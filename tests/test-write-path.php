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

function wp_fake_page( $slug, $status = 'publish', $title = 'existente', $content = '', array $meta = array(), $type = 'page' ) {
	$w   = &$GLOBALS['wp'];
	$id  = $w['next_id']++;
	$obj = (object) array(
		'ID'           => $id,
		'post_status'  => $status,
		'post_name'    => $slug,
		'post_title'   => $title,
		'post_content' => $content,
		'post_type'    => $type,
	);

	$w['posts'][ $id ]     = $obj;
	$w['by_slug'][ $slug ] = $obj;
	if ( $meta ) {
		$w['meta'][ $id ] = $meta;
	}

	return $id;
}

/** The one backup key this run wrote for $id, or '' — the tests never hard-code a timestamp. */
function backup_of( $id ) {
	foreach ( array_keys( isset( $GLOBALS['wp']['meta'][ $id ] ) ? $GLOBALS['wp']['meta'][ $id ] : array() ) as $k ) {
		if ( 0 === strpos( $k, '_es_page_backup_' ) ) {
			return $GLOBALS['wp']['meta'][ $id ][ $k ];
		}
	}

	return '';
}

/**
 * The real one IGNORES its post_type argument for attachments.
 *
 * MEASURED on a live install: an attachment at a given slug is returned for a `'page'` lookup,
 * with `post_type` = `attachment`. The fake used to return only what tests put in `by_slug` with
 * no type at all, which made the whole defect class unreachable — every caller here treated the
 * result as a page, and no assertion could tell the difference.
 */
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
		'post_type'   => isset( $args['post_type'] ) ? $args['post_type'] : 'page',
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
	if ( isset( $w['posts'][ $id ] ) ) {
		if ( isset( $args['post_title'] ) ) {
			$w['posts'][ $id ]->post_title = $args['post_title'];
		}
		/* post_name has to MOVE, in the object and in the slug index. Without this the fake would
		   accept a rename and keep answering with the old slug, so es_migrate_slug()'s read-back
		   check could never be exercised — the branch would be untestable rather than untested. */
		if ( isset( $args['post_name'] ) && $args['post_name'] !== $w['posts'][ $id ]->post_name ) {
			/* `rename_to` applies here too: wp_unique_post_slug() runs on UPDATE as well as on
			   insert, and the slug space includes attachments and posts, which get_page_by_path()
			   never sees. So a destination no PAGE holds can still come back suffixed, and the
			   update reports success either way. */
			$name = ( null === $w['rename_to'] ) ? $args['post_name'] : $w['rename_to'];
			unset( $w['by_slug'][ $w['posts'][ $id ]->post_name ] );
			$w['posts'][ $id ]->post_name = $name;
			$w['by_slug'][ $name ]        = $w['posts'][ $id ];
		}
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
/**
 * Real get_post_meta() returns the WHOLE meta map when called with no key, which is how
 * es_backup_keys() enumerates backup keys it cannot guess the timestamps of. A stub that required
 * a key would have made that function unreachable rather than untested.
 */
function get_post_meta( $id, $key = null, $single = false ) {
	$all = isset( $GLOBALS['wp']['meta'][ $id ] ) ? $GLOBALS['wp']['meta'][ $id ] : array();
	if ( null === $key ) {
		return $all;
	}

	return isset( $all[ $key ] ) ? $all[ $key ] : '';
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
		@unlink( $GLOBALS['es_sandbox'] . '/pro-stub.php' );
		@rmdir( $GLOBALS['es_sandbox'] . '/novamira-sandbox' );
		@rmdir( $GLOBALS['es_sandbox'] );
	}
);
define( 'WP_CONTENT_DIR', $GLOBALS['es_sandbox'] );
require_once dirname( __DIR__ ) . '/skills/elementor-core/assets/es-theme-parts.example.php';

/* A stand-in for Elementor Pro, in its own file because a namespace declaration cannot share a
   file with non-namespaced code. Without it es_rebuild_theme_conditions() returns false at its
   first guard and the registration/rival branches are UNREACHABLE — which is how a check that
   reports green on a template hijack went untested. instance() returns null until $es_pro is set,
   so every assertion written before this existed keeps the world it was written against. */
file_put_contents(
	$GLOBALS['es_sandbox'] . '/pro-stub.php',
	'<?php' . "\n"
	. 'namespace ElementorPro\Modules\ThemeBuilder;' . "\n"
	. 'class Cache { public function regenerate() { $GLOBALS["wp"]["regenerated"] = true; } }' . "\n"
	. 'class ConditionsManager { public function get_cache() { return new Cache(); } }' . "\n"
	. 'class Module {' . "\n"
	. '  public static function instance() { return empty( $GLOBALS["es_pro"] ) ? null : new self(); }' . "\n"
	. '  public function get_conditions_manager() { return new ConditionsManager(); }' . "\n"
	. '}' . "\n"
);
require_once $GLOBALS['es_sandbox'] . '/pro-stub.php';

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

/* ---------------------------------------------------------------------------
 * 17. La copia de seguridad prometia mas de lo que guardaba.
 * ------------------------------------------------------------------------- */
echo "--- el respaldo cubre todo lo que la escritura desplaza, no solo el layout ---\n";

/* El docblock decia que "cada sobrescritura aparca el layout anterior". Cierto y estrecho:
   es_save_page() tambien pisa _wp_page_template, y restaurar el layout sobre una plantilla
   cambiada no devuelve la pagina a como estaba. */
wp_fake_reset();
$pid = wp_fake_page(
	'servicios',
	'publish',
	'Servicios',
	'',
	array(
		'_elementor_data'      => '[{"viejo":1}]',
		'_elementor_edit_mode' => 'builder',
		'_wp_page_template'    => 'plantilla-del-tema.php',
	)
);
/* El titulo NUEVO es distinto del viejo A PROPOSITO. La version anterior de esta assertion usaba el
   mismo en los dos lados, asi que no podia fallar: el respaldo guardaba el titulo YA PISADO por
   wp_update_post() y la comprobacion pasaba igual. Lo destapo una corrida end-to-end contra un sitio
   real, donde el titulo si cambiaba. Un test que no puede distinguir el arreglo de su propio bug es
   exactamente lo que esta rama existe para eliminar. */
$r = grab(
	function () use ( $els ) {
		$a = null;
		return es_save_page( 'servicios', 'Servicios v2', $els, 'elementor_header_footer', $a );
	}
);
$bk = backup_of( $pid );
ok( is_array( $bk ), 'el respaldo es un conjunto, no un unico blob' );
ok( isset( $bk['_elementor_data'] ) && has( $bk['_elementor_data'], 'viejo' ), 'con el layout anterior' );
ok( isset( $bk['_wp_page_template'] ) && 'plantilla-del-tema.php' === $bk['_wp_page_template'], 'Y la plantilla anterior, que la escritura tambien pisa' );
ok( isset( $bk['post_title'] ) && 'Servicios' === $bk['post_title'], 'y el titulo VIEJO, no el que wp_update_post acaba de escribir' );
ok( 'Servicios v2' === get_post_field( 'post_title', $pid ), 'mientras la pagina si lleva el nuevo' );
ok( isset( $bk['post_status'] ) && 'publish' === $bk['post_status'], 'y el estado' );
ok( 'elementor_header_footer' === get_post_meta( $pid, '_wp_page_template' ), 'la escritura si cambio la plantilla, que es lo que hace falta respaldar' );

/* Convertir una pagina clasica es la sobrescritura mas destructiva del repertorio y era la unica
   que NO dejaba respaldo: sin _elementor_data que copiar, el respaldo antiguo devolvia '' y se iba
   en silencio, mientras el post_content que ERA la pagina dejaba de renderizarse para siempre. */
wp_fake_reset();
$pid = wp_fake_page( 'quienes-somos', 'publish', 'Quienes somos', '<p>Texto que lleva ahi ocho anios.</p>' );
$r   = grab(
	function () use ( $els ) {
		$a = null;
		return es_save_page( 'quienes-somos', 'Quienes somos', $els, 'elementor_header_footer', $a );
	}
);
ok( '' !== $r['out'], 'convertir una pagina que no era de Elementor avisa' );
ok( has( $r['out'], 'quienes-somos' ), 'nombrando el slug' );
$bk = backup_of( $pid );
ok( is_array( $bk ), 'y deja respaldo aunque no hubiera layout que copiar' );
ok( isset( $bk['post_content'] ) && has( $bk['post_content'], 'ocho anios' ), 'con el contenido clasico que deja de renderizarse' );

/* Una pagina nueva y vacia no tiene nada que perder: respaldarla es ruido, y un respaldo por
   reconstruccion en una pagina que nunca tuvo nada llena la tabla de meta sin motivo. */
wp_fake_reset();
$r = grab(
	function () use ( $els ) {
		$a = null;
		return es_save_page( 'nueva', 'Nueva', $els, 'elementor_header_footer', $a );
	}
);
ok( '' === $r['out'], 'crear una pagina nueva no avisa de ninguna conversion' );
$ninguno = true;
foreach ( array_keys( $GLOBALS['wp']['meta'] ) as $any ) {
	if ( '' !== backup_of( $any ) ) {
		$ninguno = false;
	}
}
ok( $ninguno, 'ni deja respaldo en ninguna parte: no habia nada que desplazar' );

/* ---------------------------------------------------------------------------
 * 30. Nadie cruzaba el inventario con los slugs a escribir.
 * ------------------------------------------------------------------------- */
echo "--- se puede saber QUE se va a pisar antes de pisarlo ---\n";

wp_fake_reset();
$home  = wp_fake_page( 'inicio', 'publish', 'Inicio', '', array( '_elementor_data' => '[1]', '_elementor_edit_mode' => 'builder' ) );
$clas  = wp_fake_page( 'quienes-somos', 'publish', 'Quienes somos', '<p>clasica</p>' );
$borr  = wp_fake_page( 'servicios', 'draft', 'Servicios' );
$GLOBALS['wp']['options']['show_on_front'] = 'page';
$GLOBALS['wp']['options']['page_on_front'] = $home;

$r = grab(
	function () {
		return es_overwrite_preflight( array( 'inicio', 'quienes-somos', 'servicios', 'contacto' ) );
	}
);
$p = $r['ret'];
ok( is_array( $p ) && isset( $p['rows'] ), 'el preflight devuelve filas' );
ok( 4 === count( $p['rows'] ), 'una por slug pedido, incluida la que no existe' );
ok( 3 === $p['overwrites'] && 1 === $p['creates'], 'y separa lo que se pisa de lo que se crea' );

$by = array();
foreach ( $p['rows'] as $row ) {
	$by[ $row['slug'] ] = $row;
}
ok( 'overwrite' === $by['inicio']['action'] && $home === $by['inicio']['id'], 'una pagina existente sale como sobrescritura, con su id' );
ok( true === $by['inicio']['is_front_page'], 'y marca cual es la PORTADA: pisarla es lo que ve el cliente al entrar' );
ok( true === $by['inicio']['is_elementor'], 'dice si ya era de Elementor' );
ok( false === $by['quienes-somos']['is_elementor'], 'y si no lo era' );
ok( true === $by['quienes-somos']['converts'], 'porque eso es una CONVERSION, no una reconstruccion' );
ok( 'draft' === $by['servicios']['status'], 'da el estado, para no publicar un borrador sin querer' );
ok( 'create' === $by['contacto']['action'] && 0 === $by['contacto']['id'], 'y lo que no existe se va a crear' );

ok( has( $r['out'], 'inicio' ), 'lo imprime, porque esto es lo que el usuario aprueba' );
ok( has( $r['out'], 'PORTADA' ), 'destacando la portada' );
ok( has( $r['out'], 'quienes-somos' ), 'y la conversion' );

wp_fake_reset();
$r = grab(
	function () {
		return es_overwrite_preflight( array( 'contacto' ) );
	}
);
ok( 0 === $r['ret']['overwrites'], 'un sitio vacio no pisa nada' );
ok( '' !== $r['out'], 'y aun asi informa: "no se pisa nada" tambien es una respuesta que hay que ver' );

/* ---------------------------------------------------------------------------
 * 16. Higiene de slugs: mover, no duplicar; y decir que la redireccion no existe.
 * ------------------------------------------------------------------------- */
echo "--- un slug que cambia mueve la pagina, y la URL vieja queda anotada ---\n";

wp_fake_reset();
$pid = wp_fake_page( 'servicios-web', 'publish', 'Servicios' );
$r   = grab(
	function () {
		return es_migrate_slug( 'servicios-web', 'servicios' );
	}
);
ok( $pid === $r['ret'], 'mueve la MISMA pagina, no crea una segunda' );
ok( 'servicios' === get_post_field( 'post_name', $pid ), 'y el slug quedo cambiado de verdad' );
$mapa = get_option( 'es_slug_redirects' );
ok( is_array( $mapa ) && isset( $mapa['servicios-web'] ) && 'servicios' === $mapa['servicios-web'], 'anotando de donde a donde' );
/* La parte incomoda, y la que hace que esto no sea una mentira: nada sirve ese mapa. */
ok( has( $r['out'], '404' ), 'y AVISA de que la URL vieja sigue devolviendo 404' );
ok( has( $r['out'], 'es_slug_redirects' ), 'nombrando la opcion donde quedo el registro' );

wp_fake_reset();
$r = grab(
	function () {
		return es_migrate_slug( 'no-existe', 'servicios' );
	}
);
ok( 0 === $r['ret'], 'mover algo que no existe no mueve nada' );
ok( has( $r['out'], 'no-existe' ), 'y avisa, porque un slug de origen mal escrito no falla: no hace nada' );

/* Mover encima de una pagina ocupada dejaria dos peleando por la misma URL y WordPress renombrando
   una a lo que le pareciera — la colision del hallazgo 15 otra vez, por la puerta de atras. */
wp_fake_reset();
$viejo = wp_fake_page( 'servicios-web' );
$otro  = wp_fake_page( 'servicios' );
$r     = grab(
	function () {
		return es_migrate_slug( 'servicios-web', 'servicios' );
	}
);
ok( 0 === $r['ret'], 'mover a un slug ocupado no mueve nada' );
ok( 'servicios-web' === get_post_field( 'post_name', $viejo ), 'la pagina se queda donde estaba' );
ok( has( $r['out'], '#' . $otro ), 'y el aviso nombra a quien ocupa el destino' );
ok( ! is_array( get_option( 'es_slug_redirects' ) ), 'sin anotar una redireccion que no ocurrio' );

wp_fake_reset();
wp_fake_page( 'servicios' );
$r = grab(
	function () {
		return es_migrate_slug( 'servicios', 'servicios' );
	}
);
ok( 0 === $r['ret'] && '' === $r['out'], 'mover un slug a si mismo no hace nada y no dice nada' );

/* Igual que en es_save_page: escribir no es haber escrito. */
wp_fake_reset();
$pid                         = wp_fake_page( 'servicios-web' );
$GLOBALS['wp']['update_ret'] = new WP_Error( 'db', 'la fila no se pudo tocar' );
$r                           = grab(
	function () {
		return es_migrate_slug( 'servicios-web', 'servicios' );
	}
);
ok( 0 === $r['ret'], 'un movimiento rechazado devuelve 0' );
ok( has( $r['out'], 'la fila no se pudo tocar' ), 'con el motivo de WordPress' );
ok( ! is_array( get_option( 'es_slug_redirects' ) ), 'y no anota una redireccion hacia una pagina que no se movio' );

/* Este bloque lo destapo una mutacion: quitar la relectura del slug SOBREVIVIA a la suite entera.
   El caso que faltaba es el hallazgo 15 por la puerta de atras — wp_unique_post_slug() corre
   tambien al ACTUALIZAR, y el espacio de slugs incluye adjuntos y entradas, que get_page_by_path()
   no ve. Asi que un destino que ninguna PAGINA ocupa puede volver sufijado, y la actualizacion
   reporta exito igual. Anotar la redireccion ahi apuntaria a una URL que no existe. */
wp_fake_reset();
$pid                        = wp_fake_page( 'servicios-web' );
$GLOBALS['wp']['rename_to'] = 'servicios-2';
$r                          = grab(
	function () {
		return es_migrate_slug( 'servicios-web', 'servicios' );
	}
);
ok( 0 === $r['ret'], 'si WordPress sufija el destino, el movimiento NO cuenta como hecho' );
ok( has( $r['out'], 'servicios-2' ), 'y el aviso nombra el slug que quedo de verdad' );
ok( ! is_array( get_option( 'es_slug_redirects' ) ), 'sin anotar una redireccion hacia una URL que no existe' );

/* ---------------------------------------------------------------------------
 * 27. Registrado no es lo mismo que visible.
 * ------------------------------------------------------------------------- */
echo "--- una plantilla registrada que compite por su ubicacion deja de dar verde ---\n";

$GLOBALS['es_pro'] = true;   /* a partir de aqui existe Elementor Pro: la rama es alcanzable */

wp_fake_reset();
$mio = wp_fake_page( 'site-header' );
$GLOBALS['wp']['options']['elementor_pro_theme_builder_conditions'] = array( 'header' => array( $mio => array( 'include/general' ) ) );
$r = grab(
	function () use ( $els ) {
		$a = null;
		return es_save_theme_part( 'site-header', 'Header', 'header', $els, array( 'include/general' ), $a );
	}
);
ok( array() === es_theme_location_rivals( $mio ), 'sin rivales, la lista viene vacia' );
ok( ! has( $r['out'], 'NO es la unica' ), 'y no avisa de nada' );

/* Lo que el informe llama secuestro de plantilla: una plantilla ajena —de la agencia anterior, del
   tema, o del build de ayer— ya reclamaba `header`. La nuestra se guarda, se condiciona, se
   cachea, la comprobacion de registro dice que si, y el sitio sigue enseñando la otra. */
wp_fake_reset();
$mio   = wp_fake_page( 'site-header' );
$ajena = 777;
$GLOBALS['wp']['options']['elementor_pro_theme_builder_conditions'] = array(
	'header' => array(
		$ajena => array( 'include/general' ),
		$mio   => array( 'include/general' ),
	),
);
ok( true === es_theme_conditions_registered( $mio ), 'la comprobacion de registro sigue diciendo que si, y tiene razon' );
$riv = es_theme_location_rivals( $mio );
ok( isset( $riv['header'] ) && array( 777 ) === $riv['header'], 'pero ahora se puede preguntar quien mas esta en esa ubicacion' );
$r = grab(
	function () use ( $els ) {
		$a = null;
		return es_save_theme_part( 'site-header', 'Header', 'header', $els, array( 'include/general' ), $a );
	}
);
ok( has( $r['out'], 'NO es la unica' ), 'y el guardado avisa: registrado no es visible' );
ok( has( $r['out'], '#777' ), 'nombrando la rival por id, que es con lo que se la busca' );
ok( has( $r['out'], 'header' ), 'y la ubicacion en disputa' );

/* Una plantilla en OTRA ubicacion no compite: avisar de ella seria ruido, y un aviso que salta
   siempre es un aviso que se aprende a ignorar. */
wp_fake_reset();
$mio = wp_fake_page( 'site-header' );
$GLOBALS['wp']['options']['elementor_pro_theme_builder_conditions'] = array(
	'header' => array( $mio => array( 'include/general' ) ),
	'footer' => array( 888 => array( 'include/general' ) ),
);
ok( array() === es_theme_location_rivals( $mio ), 'una plantilla en otra ubicacion no es rival' );

$GLOBALS['es_pro'] = false;

/* ---------------------------------------------------------------------------
 * 28. El fichero abortaba con CERO salida.
 * ------------------------------------------------------------------------- */
echo "--- si falta la dependencia, se dice; no se muere en silencio ---\n";

/* No se puede probar dentro de este proceso: el fichero ya esta cargado y su guarda corre al
   cargarse. Se prueba en uno nuevo, con un WP_CONTENT_DIR vacio, que es exactamente el caso real
   —subir los theme parts antes que el builder—. Antes de esta guarda eso era un fatal con la
   salida vacia, indistinguible de un build que corrio y no hizo nada. */
if ( ! function_exists( 'exec' ) ) {
	ok( false, 'ENTORNO, no el cambio: exec() esta deshabilitado, la guarda no se pudo verificar aqui' );
} else {
	$vacio = sys_get_temp_dir() . '/es-nodep-' . getmypid();
	@mkdir( $vacio, 0777, true );
	$probe = $vacio . '/probe.php';
	file_put_contents(
		$probe,
		'<?php' . "\n"
		. "define( 'ABSPATH', __DIR__ );\n"
		. 'define( ' . var_export( 'WP_CONTENT_DIR', true ) . ', ' . var_export( $vacio, true ) . " );\n"
		. 'require ' . var_export( dirname( __DIR__ ) . '/skills/elementor-core/assets/es-theme-parts.example.php', true ) . ";\n"
		. "echo \"SOBREVIVIO\\n\";\n"
	);
	$out  = array();
	$code = -1;
	$ran  = exec( escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( $probe ) . ' 2>&1', $out, $code );
	$txt  = implode( "\n", $out );
	@unlink( $probe );
	@rmdir( $vacio );
	ok( false !== $ran && -1 !== $code, 'el proceso de prueba se pudo lanzar' );
	ok( '' !== trim( $txt ), 'falta la dependencia y la salida NO esta vacia' );
	ok( has( $txt, 'es-builder.php' ), 'nombra el fichero que falta' );
	ok( has( $txt, 'NOTHING WAS BUILT' ), 'y dice explicitamente que no se construyo nada' );
	ok( has( $txt, 'SOBREVIVIO' ), 'y devuelve el control en vez de fatalar, que es lo que tumbaba el sitio al subirlo' );
	ok( ! has( $txt, 'Fatal error' ), 'sin fatal' );
}

/* ---------------------------------------------------------------------------
 * Entrega: el sandbox se vacia, y "vacio" se comprueba leyendo.
 * ------------------------------------------------------------------------- */
echo "--- la entrega borra el sandbox y RELEE para saber si quedo vacio ---\n";

/* El unico hallazgo de seguridad del informe: este directorio no se limpia nunca. Todo .php que
   queda ahi sigue siendo ejecutable y alcanzable por URL en el sitio del cliente, para siempre. */
$sb = es_sandbox_dir();
foreach ( array( 'es-page-home.php', 'debug.log', 'notas.txt' ) as $f ) {
	file_put_contents( $sb . '/' . $f, 'x' );
}
$antes = es_sandbox_report();
ok( in_array( 'es-page-home.php', $antes, true ), 'el informe ve lo que hay antes de borrar' );
ok( count( $antes ) >= 4, 'incluyendo el shim que este propio test dejo ahi' );

$r    = grab( 'es_sandbox_purge' );
$left = $r['ret'];
ok( array() === $left, 'tras purgar, la lista de lo que queda viene vacia' );
ok( array() === es_sandbox_report(), 'y releer el directorio lo confirma' );
ok( '' === $r['out'], 'sin avisos, porque no quedo nada' );

/* Un borrado que falla en silencio y uno que funciona son indistinguibles por el retorno de
   unlink(). Por eso lo que se devuelve es lo que SIGUE ahi, no lo que se borro. */
mkdir( $sb . '/subdir' );
file_put_contents( $sb . '/subdir/dentro.php', 'x' );
file_put_contents( $sb . '/imagen.png', 'x' );
$r    = grab( 'es_sandbox_purge' );
$left = $r['ret'];
ok( in_array( 'imagen.png', $left, true ), 'una extension que este framework no sube no se toca' );
ok( in_array( 'subdir', $left, true ), 'ni se entra en subdirectorios' );
ok( file_exists( $sb . '/subdir/dentro.php' ), 'asi que un .php dentro de un subdirectorio sobrevive' );
ok( '' !== $r['out'], 'y AVISA de que el sandbox no quedo vacio' );
ok( has( $r['out'], 'imagen.png' ), 'nombrando lo que queda' );

unlink( $sb . '/subdir/dentro.php' );
rmdir( $sb . '/subdir' );
unlink( $sb . '/imagen.png' );

echo "--- las claves de respaldo se entregan, no se prometen ---\n";
wp_fake_reset();
$p1 = wp_fake_page( 'inicio' );
$p2 = wp_fake_page( 'contacto' );
$p3 = wp_fake_page( 'nueva' );
$GLOBALS['wp']['meta'][ $p1 ]['_es_page_backup_20260101-101010'] = array( 'post_title' => 'a' );
$GLOBALS['wp']['meta'][ $p1 ]['_es_page_backup_20260102-101010'] = array( 'post_title' => 'b' );
$GLOBALS['wp']['meta'][ $p1 ]['_elementor_data']                 = '[]';
$GLOBALS['wp']['meta'][ $p2 ]['_es_page_backup_20260103-101010'] = array( 'post_title' => 'c' );

$k = es_backup_keys( array( $p1, $p2, $p3 ) );
ok( isset( $k[ $p1 ] ) && 2 === count( $k[ $p1 ] ), 'una pagina reconstruida dos veces entrega sus dos claves' );
ok( $k[ $p1 ][0] < $k[ $p1 ][1], 'ordenadas, asi que la ultima es la mas reciente' );
ok( isset( $k[ $p2 ] ) && 1 === count( $k[ $p2 ] ), 'y cada pagina las suyas' );
ok( ! isset( $k[ $p3 ] ), 'una pagina sin respaldos no aparece: una lista vacia no es una entrega' );
ok( ! in_array( '_elementor_data', $k[ $p1 ], true ), 'y no cuela meta que no es un respaldo' );

echo "--- el estado de indexacion se declara, no se supone ---\n";
wp_fake_reset();
$GLOBALS['wp']['options']['blog_public'] = '0';
$st                                      = es_indexing_state();
ok( false === $st['indexable'], "blog_public=0 es 'disuadir a los buscadores': el sitio NO es indexable" );
ok( '0' === $st['blog_public'], 'y se devuelve el valor crudo, no solo el veredicto' );

wp_fake_reset();
$GLOBALS['wp']['options']['blog_public'] = '1';
ok( true === es_indexing_state()['indexable'], 'blog_public=1 si lo es' );

wp_fake_reset();
$st = es_indexing_state();
ok( false === $st['indexable'], 'sin la opcion puesta se falla CERRADO: no se declara indexable lo que no se leyo' );
ok( null === $st['robots_file'], 'y sin robots.txt fisico se dice null, no se inventa un permiso' );

/* ---------------------------------------------------------------------------
 * Manifiesto: estado entre sesiones que se puede CONTRASTAR.
 * ------------------------------------------------------------------------- */
echo "--- el manifiesto guarda por secciones, y se relee ---\n";

wp_fake_reset();
ok( array() === es_manifest_read()['sections'], 'sin manifiesto, se devuelve la forma vacia y no null' );

ok( true === es_manifest_record( 'site', array( 'builder' => 'elementor' ) ), 'guardar una seccion devuelve true' );
ok( true === es_manifest_record( 'design', array( 'personality' => 'PERS-EDITORIAL' ) ), 'y otra tambien' );
$m = es_manifest_read();
ok( 'elementor' === $m['sections']['site']['data']['builder'], 'la primera seccion sigue ahi' );
ok( 'PERS-EDITORIAL' === $m['sections']['design']['data']['personality'], 'y la segunda no la piso' );
ok( '' !== $m['sections']['site']['at'], 'cada seccion lleva su propia marca de tiempo' );

/* Escribir no es haber escrito, igual que en la portada y en el camino de escritura. */
wp_fake_reset();
$GLOBALS['wp']['option_ro'] = array( 'es_novamira_manifest' );
$r                          = grab(
	function () {
		return es_manifest_record( 'site', array( 'builder' => 'divi' ) );
	}
);
ok( false === $r['ret'], 'una escritura aceptada que no aterriza devuelve false' );
ok( has( $r['out'], 'manifiesto' ), 'y avisa' );
ok( has( $r['out'], 'sin saber nada' ), 'diciendo lo que se pierde: la proxima sesion empieza a ciegas' );

echo "--- y se puede contrastar contra el sitio que dice describir ---\n";

wp_fake_reset();
$h = wp_fake_page( 'inicio' );
$c = wp_fake_page( 'contacto' );
$GLOBALS['wp']['options']['show_on_front'] = 'page';
$GLOBALS['wp']['options']['page_on_front'] = $h;
es_manifest_record( 'pages', array( 'inicio' => $h, 'contacto' => $c ) );
es_manifest_record( 'site', array( 'front_page_id' => $h ) );
$r = grab( 'es_manifest_verify' );
ok( array() === $r['ret'], 'un manifiesto que coincide no reporta nada' );
ok( '' === $r['out'], 'ni avisa' );

/* Una pagina borrada a mano entre sesiones. */
wp_fake_reset();
$h = wp_fake_page( 'inicio' );
es_manifest_record( 'pages', array( 'inicio' => $h, 'contacto' => 999 ) );
$r = grab( 'es_manifest_verify' );
ok( 1 === count( $r['ret'] ), 'una pagina que ya no existe es una desviacion' );
ok( has( $r['ret'][0], 'ya no existe' ), 'nombrada como tal' );
ok( has( $r['ret'][0], 'contacto' ), 'con su slug' );
ok( has( $r['out'], 'solo un humano' ), 'y el aviso dice por que NO se corrige solo' );

/* Renombrada fuera del framework: el id sigue vivo, el slug ya no es el que era. */
wp_fake_reset();
$c = wp_fake_page( 'contacto-nuevo' );
es_manifest_record( 'pages', array( 'contacto' => $c ) );
$d = grab( 'es_manifest_verify' )['ret'];
ok( 1 === count( $d ) && has( $d[0], 'contacto-nuevo' ), 'una pagina movida a mano se detecta por el slug real' );
ok( has( $d[0], 'fuera de este framework' ), 'y se dice que el movimiento vino de fuera' );

/* Borrada y recreada: mismo slug, otro id. Es la peor, porque todo "parece" bien. */
wp_fake_reset();
$otro = wp_fake_page( 'inicio' );
es_manifest_record( 'pages', array( 'inicio' => 555 ) );
$d = grab( 'es_manifest_verify' )['ret'];
ok( 1 === count( $d ) && has( $d[0], '#' . $otro ), 'mismo slug con otro id tambien es desviacion' );
ok( has( $d[0], '#555' ), 'nombrando el id que el manifiesto creia' );

/* La portada repuntada entre sesiones. */
wp_fake_reset();
$h = wp_fake_page( 'inicio' );
$o = wp_fake_page( 'otra' );
$GLOBALS['wp']['options']['show_on_front'] = 'page';
$GLOBALS['wp']['options']['page_on_front'] = $o;
es_manifest_record( 'site', array( 'front_page_id' => $h ) );
$d = grab( 'es_manifest_verify' )['ret'];
ok( 1 === count( $d ) && has( $d[0], 'portada' ), 'una portada repuntada se detecta' );

wp_fake_reset();
$h = wp_fake_page( 'inicio' );
es_manifest_record( 'site', array( 'front_page_id' => $h ) );
$d = grab( 'es_manifest_verify' )['ret'];
ok( 1 === count( $d ) && has( $d[0], 'el blog' ), 'y volver a mostrar el blog tambien, que es lo que nadie mira' );

/* ---------------------------------------------------------------------------
 * Un ADJUNTO ocupando el slug. Encontrado probando contra WordPress de verdad.
 * ------------------------------------------------------------------------- */
echo "--- un adjunto con el slug de una pagina deja de pasar por pagina ---\n";

/* `get_page_by_path($slug, OBJECT, 'page')` NO hace lo que dice su tercer argumento: WordPress mete
   los adjuntos en esa busqueda. Medido en una instalacion viva — un adjunto en "nvm-solo-adjunto"
   volvio de una consulta de tipo 'page', y una pagina pidiendo ese mismo slug quedo renombrada
   "-2". Sin filtrar, es_save_page() entraba en la rama de ACTUALIZAR, renombraba el adjunto, le
   escribia _elementor_data encima y reportaba 'updated': ninguna pagina creada, un adjunto roto, y
   todos los checks en verde. */
wp_fake_reset();
$adj = wp_fake_page( 'contacto', 'inherit', 'foto.jpg', '', array(), 'attachment' );
ok( null === es_page_by_slug( 'contacto' ), 'un adjunto NO es una pagina, aunque get_page_by_path lo devuelva' );
ok( null !== get_page_by_path( 'contacto', OBJECT, 'page' ), 'y el doble reproduce que la funcion de WordPress SI lo devuelve' );

$action = null;
$r      = grab(
	function () use ( $els, &$action ) {
		return es_save_page( 'contacto', 'Contacto', $els, 'elementor_header_footer', $action );
	}
);
ok( 'updated' !== $action, "no se ACTUALIZA un adjunto creyendo que es una pagina" );
ok( 'created' === $action || 'created-renamed' === $action, 'se crea una pagina de verdad' );
ok( $r['ret'] !== $adj, 'y el id devuelto no es el del adjunto' );
ok( ! isset( $GLOBALS['wp']['meta'][ $adj ]['_elementor_data'] ), 'el adjunto NO recibio un layout encima' );

/* El preflight lo listaba como una pagina a punto de ser pisada. */
wp_fake_reset();
wp_fake_page( 'contacto', 'inherit', 'foto.jpg', '', array(), 'attachment' );
$p = grab(
	function () {
		return es_overwrite_preflight( array( 'contacto' ) );
	}
)['ret'];
ok( 0 === $p['overwrites'] && 1 === $p['creates'], 'el preflight no cuenta un adjunto como pagina a sobrescribir' );

/* Y el manifiesto verificaba contra el. */
wp_fake_reset();
$adj = wp_fake_page( 'contacto', 'inherit', 'foto.jpg', '', array(), 'attachment' );
es_manifest_record( 'pages', array( 'contacto' => 999 ) );
$d = grab( 'es_manifest_verify' )['ret'];
ok( 1 === count( $d ), 'el manifiesto reporta desviacion: la pagina no existe' );
ok( ! has( $d[0], '#' . $adj ), 'sin confundirla con el adjunto que ocupa ese slug' );

/* Mover a un slug que ocupa un adjunto SI se bloquea: WordPress sufija contra todo el espacio de
   slugs, asi que un adjunto estorba igual que una pagina. Lo que cambia es como se nombra. */
wp_fake_reset();
wp_fake_page( 'servicios-web' );
$adj = wp_fake_page( 'servicios', 'inherit', 'foto.jpg', '', array(), 'attachment' );
$r   = grab(
	function () {
		return es_migrate_slug( 'servicios-web', 'servicios' );
	}
);
ok( 0 === $r['ret'], 'un adjunto en el destino tambien bloquea el movimiento' );
ok( has( $r['out'], 'attachment' ), 'y el aviso dice QUE lo ocupa, no lo llama pagina' );

/* ---------------------------------------------------------------------------
 * Modo seguro del sandbox. Leido del cargador de Novamira, no supuesto.
 * ------------------------------------------------------------------------- */
echo "--- un .crashed apaga el sandbox entero, y eso deja de ser invisible ---\n";

$sb = es_sandbox_dir();

$st = es_sandbox_state();
ok( false === $st['safe_mode'], 'sin .crashed, el sandbox esta operativo' );
ok( null === $st['reason'], 'y no hay causa que reportar' );

/* El cargador hace `if (file_exists($crashed_file)) { return; }` ANTES de recorrer los ficheros:
   uno solo apaga los diez. El unico aviso es un banner de wp-admin, que un agente por conector no
   ve nunca. Medido en un sitio vivo que llevaba en modo seguro desde un fatal: ni una sola funcion
   es_* estaba definida, asi que cualquier build habria muerto con "undefined function". */
file_put_contents( $sb . '/.crashed', json_encode( array( 'sandbox_file' => '/ruta/es-theme-parts.php', 'message' => 'Uncaught Error: Call to undefined function is_user_logged_in()' ) ) );
$r  = grab( 'es_sandbox_state' );
$st = $r['ret'];
ok( true === $st['safe_mode'], 'con .crashed, se reporta MODO SEGURO' );
ok( has( $st['reason'], 'es-theme-parts.php' ), 'nombrando el fichero que lo provoco' );
ok( has( $st['reason'], 'is_user_logged_in' ), 'y el error registrado' );
ok( '' !== $r['out'], 'y AVISA: nada de lo que subas se va a ejecutar' );
ok( has( $r['out'], 'MODO SEGURO' ), 'diciendo que es modo seguro' );
/* El aviso tiene que desaconsejar el atajo evidente, que vuelve a tumbar el sitio. */
ok( has( $r['out'], 'ANTES de borrar' ), 'y avisa de no borrar .crashed sin arreglar la causa' );

/* Un .crashed ilegible sigue siendo modo seguro: el cargador solo mira que EXISTA. */
file_put_contents( $sb . '/.crashed', 'no es json' );
$st = grab( 'es_sandbox_state' )['ret'];
ok( true === $st['safe_mode'], 'un .crashed que no es json sigue siendo modo seguro' );
ok( has( $st['reason'], 'no es json' ), 'y se reporta su contenido crudo' );

file_put_contents( $sb . '/.crashed', '' );
$st = grab( 'es_sandbox_state' )['ret'];
ok( true === $st['safe_mode'], 'y uno VACIO tambien: el cargador solo comprueba que exista' );

unlink( $sb . '/.crashed' );
ok( false === es_sandbox_state()['safe_mode'], 'borrado el fichero, vuelve a estar operativo' );

echo "\n$pass OK / $fail FAIL\n";
exit( $fail ? 1 : 0 );
