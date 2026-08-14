<?php
/**
 * Behavioural assertions for the DESTRUCTIVE write path: what the save functions say when the
 * write did NOT do what the caller asked for.
 *
 * Run:  php tests/test-write-path.php     (exit 0 = green)
 *
 * Why a third test file. `test-container-hygiene.php` stubs two read-only WordPress functions and
 * audits trees in memory; nothing there ever reaches `es_save_page()`. Proving what a REJECTED
 * write reports needs a WordPress that can be told to fail on demand, which is a different fixture
 * altogether, so it lives here rather than being bolted onto a suite about container nesting.
 *
 * Three findings from `docs/auditoria-2026-08-11.md` are under test, and all three are the same
 * disease this branch exists to remove — a run reporting success over work it never did:
 *
 *   14. The failure branch returned 0 and said NOTHING. A page WordPress refused to create left no
 *       trace on either channel: the build script carried on to the next page, the run ended with a
 *       clean audit verdict (the audit only ever sees the tree it was HANDED, never the write), and
 *       the missing page was discovered by a human loading the site.
 *   14b. Worse on the update path: `wp_update_post()`'s return value was discarded outright, so a
 *       post WordPress refused to touch still had its `_elementor_data` overwritten and still
 *       reported `updated`. The one branch that could not fail was the one being checked.
 *   15. `wp_insert_post()` does not promise you the slug you asked for. When one is taken —— by an
 *       attachment, by a post, by a reserved term —— WordPress silently appends a suffix, so asking
 *       for `contacto` yields a published page at `contacto-2` while `$action` says `created`. The
 *       page the caller believes it just built is somebody else's.
 *
 * The suite runs with ES_AUDIT_SILENT defined on purpose: it mutes the per-page container report,
 * so anything left on stdout is a WARNING, and a warning that shows up here is one the routine
 * silence switch could not suppress.
 */
define( 'ABSPATH', __DIR__ );
define( 'ES_AUDIT_SILENT', true );
if ( ! defined( 'OBJECT' ) ) {
	define( 'OBJECT', 'OBJECT' );
}

/* error_log() is the OTHER channel, and the only one still there once the operator has scrolled
   past. Redirecting it to a file both keeps this run readable and makes it assertable. */
$GLOBALS['es_log'] = tempnam( sys_get_temp_dir(), 'eswp' );
if ( false === ini_set( 'error_log', $GLOBALS['es_log'] ) ) {
	fwrite( STDERR, "ENTORNO: ini_set('error_log') fue rechazado, el canal durable no se puede leer\n" );
	exit( 2 );
}
register_shutdown_function(
	function () {
		@unlink( $GLOBALS['es_log'] );
	}
);

/* ---------------------------------------------------------------------------
 * A WordPress that can be told to fail.
 * ------------------------------------------------------------------------- */

class WP_Error {
	private $code;
	private $message;
	public function __construct( $code = '', $message = '' ) {
		$this->code    = $code;
		$this->message = $message;
	}
	public function get_error_message() {
		return $this->message;
	}
	public function get_error_code() {
		return $this->code;
	}
}
function is_wp_error( $thing ) {
	return $thing instanceof WP_Error;
}

/**
 * Reset the fake site.
 *
 * `insert_ret` / `update_ret` hold the EXACT value the corresponding WordPress function returns;
 * null means "behave normally". `rename_to` reproduces wp_unique_post_slug() handing back a slug
 * that is not the one that was asked for.
 */
function wp_fake_reset() {
	$GLOBALS['wp'] = array(
		'posts'      => array(),   /* id   => stdClass{ ID, post_status, post_name, post_title } */
		'by_slug'    => array(),   /* slug => the same object                                    */
		'meta'       => array(),   /* id   => array( key => value )                              */
		'terms'      => array(),   /* id   => array( taxonomy => terms )                         */
		'next_id'    => 100,
		'insert_ret' => null,
		'update_ret' => null,
		'rename_to'  => null,
		'options'    => array(),
		'option_ro'  => array(),   /* names update_option() accepts and silently does not write */
	);
}

function wp_fake_page( $slug, $status = 'publish', $title = 'existente' ) {
	$w   = &$GLOBALS['wp'];
	$id  = $w['next_id']++;
	$obj = (object) array( 'ID' => $id, 'post_status' => $status, 'post_name' => $slug, 'post_title' => $title );

	$w['posts'][ $id ]     = $obj;
	$w['by_slug'][ $slug ] = $obj;

	return $id;
}

function get_page_by_path( $slug, $output = OBJECT, $post_type = 'page' ) {
	$w = &$GLOBALS['wp'];
	return isset( $w['by_slug'][ $slug ] ) ? $w['by_slug'][ $slug ] : null;
}

function wp_insert_post( array $args ) {
	$w = &$GLOBALS['wp'];
	if ( null !== $w['insert_ret'] ) {
		return $w['insert_ret'];
	}
	/* The real one never guarantees post_name survives; that is finding 15. */
	$name = ( null === $w['rename_to'] ) ? $args['post_name'] : $w['rename_to'];
	$id   = $w['next_id']++;
	$obj  = (object) array(
		'ID'          => $id,
		'post_status' => isset( $args['post_status'] ) ? $args['post_status'] : 'publish',
		'post_name'   => $name,
		'post_title'  => isset( $args['post_title'] ) ? $args['post_title'] : '',
	);

	$w['posts'][ $id ]    = $obj;
	$w['by_slug'][ $name ] = $obj;

	return $id;
}

function wp_update_post( array $args ) {
	$w = &$GLOBALS['wp'];
	if ( null !== $w['update_ret'] ) {
		return $w['update_ret'];
	}
	$id = $args['ID'];
	if ( isset( $w['posts'][ $id ] ) && isset( $args['post_title'] ) ) {
		$w['posts'][ $id ]->post_title = $args['post_title'];
	}

	return $id;   /* WordPress returns the post id on success. */
}

function get_post_field( $field, $post ) {
	$w  = &$GLOBALS['wp'];
	$id = is_object( $post ) ? $post->ID : (int) $post;
	if ( ! isset( $w['posts'][ $id ] ) || ! isset( $w['posts'][ $id ]->$field ) ) {
		return '';
	}

	return $w['posts'][ $id ]->$field;
}

function update_post_meta( $id, $key, $value ) {
	$GLOBALS['wp']['meta'][ $id ][ $key ] = $value;
	return true;
}
function get_post_meta( $id, $key, $single = false ) {
	return isset( $GLOBALS['wp']['meta'][ $id ][ $key ] ) ? $GLOBALS['wp']['meta'][ $id ][ $key ] : '';
}
function delete_post_meta( $id, $key ) {
	unset( $GLOBALS['wp']['meta'][ $id ][ $key ] );
	return true;
}
function wp_set_object_terms( $id, $terms, $taxonomy ) {
	$GLOBALS['wp']['terms'][ $id ][ $taxonomy ] = $terms;
	return array();
}
function wp_slash( $v ) {
	return $v;
}
function wp_json_encode( $v ) {
	return json_encode( $v );
}
function get_option( $name, $default = false ) {
	$w = &$GLOBALS['wp'];
	return array_key_exists( $name, $w['options'] ) ? $w['options'][ $name ] : $default;
}

/**
 * `option_ro` reproduces a write that is accepted and does not land.
 *
 * The return value is deliberately NOT a reliable success signal even in real WordPress:
 * update_option() also returns false when the new value equals the old one. A caller that trusts
 * the boolean either misses a failure or invents one, which is why the only honest check is to
 * read the option back.
 */
function update_option( $name, $value ) {
	$w = &$GLOBALS['wp'];
	if ( in_array( $name, $w['option_ro'], true ) ) {
		return false;
	}
	$w['options'][ $name ] = $value;

	return true;
}
/**
 * Two callers, two meanings.
 *
 * es_save_theme_part() looks a template up by slug through get_posts(); es_img() looks an
 * attachment up the same way. Returning array() for BOTH is what let a mutation that breaks the
 * theme-part UPDATE path survive: that branch was unreachable, so nothing could be asserted about
 * it. Attachments still find nothing, because no fixture here asks for an image.
 */
function get_posts( $args ) {
	$w = &$GLOBALS['wp'];
	if ( isset( $args['post_type'] ) && 'elementor_library' === $args['post_type'] ) {
		$slug = isset( $args['name'] ) ? $args['name'] : '';
		return isset( $w['by_slug'][ $slug ] ) ? array( $w['by_slug'][ $slug ] ) : array();
	}

	return array();
}
function wp_get_attachment_url( $id ) {
	return '';
}

wp_fake_reset();

require_once dirname( __DIR__ ) . '/skills/elementor-core/assets/es-builder.php';

/* The example asset resolves its dependency by absolute sandbox path, so it needs that path to
   exist before it can be loaded at all. The shim re-requires the file already loaded above, which
   require_once resolves to the same realpath and therefore skips. */
$GLOBALS['es_sandbox'] = sys_get_temp_dir() . '/es-writepath-' . getmypid();
if ( ! @mkdir( $GLOBALS['es_sandbox'] . '/novamira-sandbox', 0777, true ) ) {
	fwrite( STDERR, "ENTORNO: no se pudo crear el sandbox temporal en " . $GLOBALS['es_sandbox'] . "\n" );
	exit( 2 );
}
file_put_contents(
	$GLOBALS['es_sandbox'] . '/novamira-sandbox/es-builder.php',
	"<?php\nrequire_once " . var_export( dirname( __DIR__ ) . '/skills/elementor-core/assets/es-builder.php', true ) . ";\n"
);
register_shutdown_function(
	function () {
		@unlink( $GLOBALS['es_sandbox'] . '/novamira-sandbox/es-builder.php' );
		@rmdir( $GLOBALS['es_sandbox'] . '/novamira-sandbox' );
		@rmdir( $GLOBALS['es_sandbox'] );
	}
);
define( 'WP_CONTENT_DIR', $GLOBALS['es_sandbox'] );
require_once dirname( __DIR__ ) . '/skills/elementor-core/assets/es-theme-parts.example.php';

/* ---------------------------------------------------------------------------
 * Harness.
 * ------------------------------------------------------------------------- */

$pass = 0;
$fail = 0;
function ok( $cond, $label ) {
	global $pass, $fail;
	if ( $cond ) {
		$pass++;
		echo "  OK   $label\n";
	} else {
		$fail++;
		echo "  FAIL $label\n";
	}
}

/** Run $fn with stdout captured, and hand back BOTH what it printed and what it returned. */
function grab( $fn ) {
	ob_start();
	$ret = $fn();
	return array( 'out' => ob_get_clean(), 'ret' => $ret );
}

function has( $haystack, $needle ) {
	return false !== strpos( $haystack, $needle );
}

function log_mark() {
	clearstatcache();
	return strlen( (string) @file_get_contents( $GLOBALS['es_log'] ) );
}
function log_since( $offset ) {
	clearstatcache();
	return substr( (string) @file_get_contents( $GLOBALS['es_log'] ), $offset );
}

/** Did ANY post end up with a layout written to it? */
function any_layout_written() {
	foreach ( $GLOBALS['wp']['meta'] as $meta ) {
		if ( isset( $meta['_elementor_data'] ) ) {
			return true;
		}
	}
	return false;
}

$els = array( es_split( array( es_h( 'a' ), es_h( 'b' ) ) ) );

echo "=== el camino de escritura ===\n";

/* ---------------------------------------------------------------------------
 * 14. Crear una pagina que WordPress rechaza.
 * ------------------------------------------------------------------------- */
echo "--- una pagina que WordPress se niega a crear deja de irse en silencio ---\n";

wp_fake_reset();
$GLOBALS['wp']['insert_ret'] = 0;
$action                      = null;
$r                           = grab(
	function () use ( $els, &$action ) {
		return es_save_page( 'contacto', 'Contacto', $els, 'elementor_header_footer', $action );
	}
);
ok( 0 === $r['ret'], 'devuelve 0 cuando la insercion falla' );
ok( 'failed' === $action, "y \$action dice 'failed'" );
ok( '' !== $r['out'], 'y NO se calla: ES_AUDIT_SILENT silencia el reporte, nunca un fallo de escritura' );
ok( has( $r['out'], 'AVISO' ), 'lo dice como aviso' );
ok( has( $r['out'], 'contacto' ), 'nombrando el slug, que es lo unico con lo que el humano puede buscar' );
ok( ! any_layout_written(), 'y no escribio ningun layout' );

wp_fake_reset();
$GLOBALS['wp']['insert_ret'] = new WP_Error( 'invalid_page_template', 'plantilla desconocida' );
$action                      = null;
$mark                        = log_mark();
$r                           = grab(
	function () use ( $els, &$action ) {
		return es_save_page( 'servicios', 'Servicios', $els, 'elementor_header_footer', $action );
	}
);
ok( 0 === $r['ret'], 'un WP_Error tambien devuelve 0' );
ok( 'failed' === $action, "y \$action tambien dice 'failed'" );
ok( has( $r['out'], 'servicios' ), 'el aviso nombra el slug' );
/* Sin esto el aviso dice "fallo" y el humano se queda sin saber POR QUE fallo, que es lo unico
   que WordPress si le habia contado a alguien. */
ok( has( $r['out'], 'plantilla desconocida' ), 'y arrastra el motivo que dio WordPress, no solo que fallo' );
/* stdout se pierde en cuanto el operador scrollea. El log durable es el unico que sigue ahi cuando
   alguien pregunta por que falta una pagina tres dias despues. */
ok( has( log_since( $mark ), 'servicios' ), 'y queda tambien en el log durable, no solo en pantalla' );

/* ---------------------------------------------------------------------------
 * 14b. Actualizar una pagina que WordPress rechaza.
 * ------------------------------------------------------------------------- */
echo "--- una actualizacion rechazada deja de pisar el diseño igual ---\n";

wp_fake_reset();
wp_fake_page( 'inicio' );
$GLOBALS['wp']['update_ret'] = new WP_Error( 'db_update_error', 'no se pudo actualizar la fila' );
$action                      = null;
$r                           = grab(
	function () use ( $els, &$action ) {
		return es_save_page( 'inicio', 'Inicio', $els, 'elementor_header_footer', $action );
	}
);
/* El retorno de wp_update_post() se descartaba entero, asi que esta era la unica rama que no podia
   fallar: WordPress se negaba a tocar el post y el layout se sobrescribia igual, reportando
   'updated'. Falla CERRADO: si no se pudo actualizar la fila, no hay nada que autorice reescribir
   su diseño. */
ok( 0 === $r['ret'], 'una actualizacion rechazada devuelve 0, no el id' );
ok( 'failed' === $action, "y \$action dice 'failed', no 'updated'" );
ok( has( $r['out'], 'inicio' ), 'el aviso nombra el slug' );
ok( has( $r['out'], 'no se pudo actualizar la fila' ), 'y el motivo de WordPress' );
ok( ! any_layout_written(), 'y sobre todo: NO sobrescribio el layout de una pagina que no pudo actualizar' );

/* ---------------------------------------------------------------------------
 * 15. Colision de slug.
 * ------------------------------------------------------------------------- */
echo "--- una colision de slug deja de reportarse como si nada ---\n";

wp_fake_reset();
$GLOBALS['wp']['rename_to'] = 'contacto-2';
$action                     = null;
$r                          = grab(
	function () use ( $els, &$action ) {
		return es_save_page( 'contacto', 'Contacto', $els, 'elementor_header_footer', $action );
	}
);
ok( $r['ret'] > 0, 'la pagina SI se creo, asi que devuelve su id' );
ok( has( $r['out'], 'contacto-2' ), 'pero avisa, nombrando el slug que WordPress asigno de verdad' );
ok( has( $r['out'], '"contacto"' ), 'y el que se habia pedido, porque la diferencia ES el hallazgo' );
ok( 'created-renamed' === $action, "y \$action deja de decir 'created' a secas: la pagina pedida no existe" );

wp_fake_reset();
$action = null;
$r      = grab(
	function () use ( $els, &$action ) {
		return es_save_page( 'contacto', 'Contacto', $els, 'elementor_header_footer', $action );
	}
);
ok( 'created' === $action, 'sin colision sigue diciendo created' );
ok( '' === $r['out'], 'y no avisa de nada: el aviso tiene que doler solo cuando hay algo que mirar' );

wp_fake_reset();
wp_fake_page( 'inicio', 'draft' );
$action = null;
$r      = grab(
	function () use ( $els, &$action ) {
		return es_save_page( 'inicio', 'Inicio', $els, 'elementor_header_footer', $action );
	}
);
ok( 'updated' === $action, 'y una actualizacion normal sigue diciendo updated' );
ok( '' === $r['out'], 'sin avisos' );
ok( any_layout_written(), 'habiendo escrito el layout de verdad' );

/* ---------------------------------------------------------------------------
 * El gemelo: es_save_theme_part() tiene la misma forma, y falla igual.
 * ------------------------------------------------------------------------- */
echo "--- el gemelo de theme parts falla del mismo modo, asi que se arregla igual ---\n";

/* Un header que no se creo es la peor de todas: no falta UNA pagina, falta el encabezado de TODAS.
   Este asset se sube al sitio y se ejecuta alli, asi que su rama de fallo es la que menos ojos
   tiene encima. */
wp_fake_reset();
$GLOBALS['wp']['insert_ret'] = 0;
$action                      = null;
$r                           = grab(
	function () use ( $els, &$action ) {
		return es_save_theme_part( 'site-header', 'Header', 'header', $els, array( 'include/general' ), $action );
	}
);
ok( 0 === $r['ret'], 'un theme part que no se pudo crear devuelve 0' );
ok( 'failed' === $action, "y \$action dice 'failed'" );
ok( has( $r['out'], 'site-header' ), 'avisando y nombrando el slug' );
ok( ! any_layout_written(), 'sin escribir layout' );

wp_fake_reset();
$GLOBALS['wp']['rename_to'] = 'site-header-2';
$action                     = null;
$r                          = grab(
	function () use ( $els, &$action ) {
		return es_save_theme_part( 'site-header', 'Header', 'header', $els, array( 'include/general' ), $action );
	}
);
ok( has( $r['out'], 'site-header-2' ), 'y una colision de slug tambien avisa aqui' );
ok( 'created-renamed' === $action, "con el mismo \$action que es_save_page()" );

/* Esta rama la destapo una mutacion, no el plan: revertir el gemelo a comprobar $id en vez del
   retorno de wp_update_post() SOBREVIVIA a la suite entera, porque el fake devolvia array() a toda
   busqueda y la rama de actualizacion era inalcanzable. Un arreglo que ningun test puede distinguir
   de su propio bug es exactamente lo que esta rama existe para eliminar. */
wp_fake_reset();
wp_fake_page( 'site-header' );
$GLOBALS['wp']['update_ret'] = new WP_Error( 'db_update_error', 'no se pudo actualizar la fila' );
$action                      = null;
$r                           = grab(
	function () use ( $els, &$action ) {
		return es_save_theme_part( 'site-header', 'Header', 'header', $els, array( 'include/general' ), $action );
	}
);
ok( 0 === $r['ret'], 'un theme part que no se pudo actualizar devuelve 0' );
ok( 'failed' === $action, "y \$action dice 'failed', no 'updated'" );
ok( has( $r['out'], 'no se pudo actualizar la fila' ), 'con el motivo de WordPress' );
ok( ! any_layout_written(), 'y NO piso el header que ya estaba puesto' );

wp_fake_reset();
wp_fake_page( 'site-header' );
$action = null;
$r      = grab(
	function () use ( $els, &$action ) {
		return es_save_theme_part( 'site-header', 'Header', 'header', $els, array( 'include/general' ), $action );
	}
);
ok( 'updated' === $action, 'y una actualizacion normal de theme part sigue diciendo updated' );
ok( any_layout_written(), 'habiendo escrito el layout de verdad' );

/* ---------------------------------------------------------------------------
 * 2 (BLOCKER). La portada.
 * ------------------------------------------------------------------------- */
echo "--- la portada se establece, y se comprueba que quedo establecida ---\n";

/* Nada en el arbol tocaba `show_on_front` ni `page_on_front` — cero coincidencias en todo el repo.
   Una home construida, guardada y auditada limpia seguia sin ser la portada del sitio: WordPress
   mostraba el blog en `/`, el build reportaba exito, y quien lo descubria era el cliente. */

wp_fake_reset();
ok( 'posts' === es_front_page()['mode'], 'un sitio recien instalado muestra entradas, no una pagina' );
ok( 0 === es_front_page()['id'], 'y no hay ningun id de portada' );

/* La mitad de la opcion puesta NO es una portada: WordPress cae de vuelta al blog. Un lector que
   solo mirara `page_on_front` daria por buena una portada que nadie ve. */
wp_fake_reset();
$hid                                     = wp_fake_page( 'inicio' );
$GLOBALS['wp']['options']['page_on_front'] = $hid;
ok( 'posts' === es_front_page()['mode'], "page_on_front solo, sin show_on_front='page', sigue siendo el blog" );

wp_fake_reset();
$hid                                       = wp_fake_page( 'inicio' );
$GLOBALS['wp']['options']['show_on_front']  = 'page';
$GLOBALS['wp']['options']['page_on_front']  = $hid;
$fp                                        = es_front_page();
ok( 'page' === $fp['mode'] && $hid === $fp['id'], 'con las dos opciones puestas, resuelve la pagina' );
ok( 'inicio' === $fp['slug'], 'y da su slug, que es como se resuelve la home sin adivinarla' );

wp_fake_reset();
$hid    = wp_fake_page( 'inicio' );
$action = null;
$r      = grab(
	function () {
		return es_set_front_page( 'inicio' );
	}
);
ok( $hid === $r['ret'], 'establecer la portada devuelve el id de la pagina' );
ok( 'page' === get_option( 'show_on_front' ), "y escribe show_on_front='page'" );
ok( $hid === (int) get_option( 'page_on_front' ), 'y page_on_front' );
ok( '' === $r['out'], 'sin avisos: en un sitio que mostraba el blog no hay nada que reclamar' );

wp_fake_reset();
$r = grab(
	function () {
		return es_set_front_page( 'inicio' );
	}
);
ok( 0 === $r['ret'], 'pedir de portada una pagina que no existe devuelve 0' );
ok( has( $r['out'], 'inicio' ), 'avisando y nombrando el slug' );
ok( ! array_key_exists( 'show_on_front', $GLOBALS['wp']['options'] ), 'y NO deja el sitio a medio configurar' );

/* El corazon del hallazgo: escribir no es haber escrito. update_option() devuelve false tambien
   cuando el valor no cambio, asi que su booleano no sirve de prueba en ninguna direccion. */
wp_fake_reset();
$hid                          = wp_fake_page( 'inicio' );
$GLOBALS['wp']['option_ro']   = array( 'page_on_front' );
$r                            = grab(
	function () {
		return es_set_front_page( 'inicio' );
	}
);
ok( 0 === $r['ret'], 'una escritura aceptada que no aterrizo devuelve 0, no el id' );
ok( '' !== $r['out'], 'y avisa: escribir la opcion no es haberla escrito' );
ok( has( $r['out'], 'inicio' ), 'nombrando la pagina que se pidio' );

/* Repuntar la portada de un sitio existente es destructivo y silencioso por naturaleza: la home
   anterior sigue publicada, solo deja de ser la que se ve al entrar. */
wp_fake_reset();
$vieja                                     = wp_fake_page( 'home-antigua' );
$nueva                                     = wp_fake_page( 'inicio' );
$GLOBALS['wp']['options']['show_on_front'] = 'page';
$GLOBALS['wp']['options']['page_on_front'] = $vieja;
$r                                         = grab(
	function () {
		return es_set_front_page( 'inicio' );
	}
);
ok( $nueva === $r['ret'], 'repuntar la portada funciona' );
ok( has( $r['out'], 'home-antigua' ), 'pero avisa nombrando la portada que se deja de mostrar' );
ok( has( $r['out'], 'inicio' ), 'y la que pasa a mostrarse' );

wp_fake_reset();
$hid                                       = wp_fake_page( 'inicio' );
$GLOBALS['wp']['options']['show_on_front'] = 'page';
$GLOBALS['wp']['options']['page_on_front'] = $hid;
$r                                         = grab(
	function () {
		return es_set_front_page( 'inicio' );
	}
);
ok( $hid === $r['ret'], 'reestablecer la MISMA portada sigue devolviendo el id' );
ok( '' === $r['out'], 'y no avisa de nada: no se dejo de mostrar ninguna pagina' );

echo "\n$pass OK / $fail FAIL\n";
exit( $fail ? 1 : 0 );
