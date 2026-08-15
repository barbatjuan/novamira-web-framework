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
		'meta_ro'    => array(),   /* meta keys update/delete_post_meta accept and do not touch */
	);
	/* The builder's own per-run state lives in globals, so a fixture that forgot these would
	   inherit the previous fixture's approvals and its list of saved pages — and the assertions
	   that depend on them would pass for the wrong reason, which is the failure this whole suite
	   is about. Reset with the site, not beside it. */
	$GLOBALS['es_preflight_slugs'] = array();
	$GLOBALS['es_saved_pages']     = array();
}

/**
 * Approve slugs the way a real build does — through the preflight, whose printed block IS the
 * approval. `grab()` only keeps that block out of the output an assertion is about to read.
 */
function approve() {
	$slugs = func_get_args();
	grab(
		function () use ( $slugs ) {
			return es_overwrite_preflight( $slugs );
		}
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

/**
 * `meta_ro` reproduces a meta write that is ACCEPTED and does not land.
 *
 * Without it, three mutations survived the whole suite: a restore that reports what it attempted
 * instead of what it verified, a prune that assumes its deletes worked, and a restore that forgets
 * to slash `_elementor_data`. All three read-back branches were UNREACHABLE because the fake could
 * not fail. That is the fourth time in this branch a surviving mutant turned out to be the test
 * double's fault rather than the assertion's.
 */
function update_post_meta( $id, $key, $value ) {
	if ( in_array( $key, $GLOBALS['wp']['meta_ro'], true ) ) {
		return false;
	}
	/* The real one runs wp_unslash() on its input, which is the ONLY reason wp_slash() exists at the
	   call site: without it, the backslashes inside encoded JSON are stripped and the value lands
	   corrupted. Modelling only one half of that pair made the missing-slash mutation invisible. */
	$GLOBALS['wp']['meta'][ $id ][ $key ] = is_string( $value ) ? stripslashes( $value ) : $value;
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
	if ( in_array( $key, $GLOBALS['wp']['meta_ro'], true ) ) {
		return false;
	}
	unset( $GLOBALS['wp']['meta'][ $id ][ $key ] );
	return true;
}
function wp_set_object_terms( $id, $terms, $taxonomy ) {
	$GLOBALS['wp']['terms'][ $id ][ $taxonomy ] = $terms;
	return array();
}
/**
 * The real one ADDS SLASHES, which is the whole reason `_elementor_data` needs it: the value goes
 * through `wp_unslash()` on the way into the database. A stub returning the value untouched made
 * "did the caller slash it?" unanswerable, so forgetting the slash was undetectable.
 */
function wp_slash( $v ) {
	return is_string( $v ) ? addslashes( $v ) : $v;
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
require_once dirname( __DIR__ ) . '/skills/elementor-theme-parts/assets/es-theme-parts.example.php';

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
approve( 'contacto' );
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
approve( 'servicios' );
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
approve( 'inicio' );
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
approve( 'contacto' );
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
approve( 'contacto' );
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
approve( 'inicio' );
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
approve( 'quienes-somos' );
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
approve( 'nueva' );
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

/* Un sitio puede tener dos plantillas en una ubicacion a proposito, con condiciones distintas.
   Sin forma de decirlo, este aviso saltaba en cada build de un sitio CORRECTO, que es como un
   aviso se convierte en paisaje — el mismo argumento que el umbral de palabras que nadie cumplia. */
$r = grab(
	function () use ( $els, $ajena ) {
		$a = null;
		return es_save_theme_part( 'site-header', 'Header', 'header', $els, array( 'include/general' ), $a, array( $ajena ) );
	}
);
ok( ! has( $r['out'], 'NO es la unica' ), 'una rival RECONOCIDA por quien llama no vuelve a avisar' );

/* Pero reconocer es por ID, nunca por ubicacion: la que aparece DESPUES es un hecho nuevo. */
$GLOBALS['wp']['options']['elementor_pro_theme_builder_conditions']['header'][999] = array( 'include/general' );
$r = grab(
	function () use ( $els, $ajena ) {
		$a = null;
		return es_save_theme_part( 'site-header', 'Header', 'header', $els, array( 'include/general' ), $a, array( $ajena ) );
	}
);
ok( has( $r['out'], '#999' ), 'una rival NUEVA sigue avisando aunque otra este reconocida' );
ok( ! has( $r['out'], '#777' ), 'y no vuelve a nombrar la que ya se miro' );
ok( has( $r['out'], '1 rival' ), 'pero dice cuantas hay silenciadas: reconocida no es invisible' );

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
		. 'require ' . var_export( dirname( __DIR__ ) . '/skills/elementor-theme-parts/assets/es-theme-parts.example.php', true ) . ";\n"
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
	ok( has( $txt, 'NO SE CONSTRUYO NADA' ), 'y dice explicitamente que no se construyo nada' );
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

/* Encontrado limpiando el sandbox de un cliente REAL: `es-dlo-a11y.php` registraba
   template_redirect y envolvia cada pagina en un landmark <main> porque el tema no imprime
   ninguno. No es andamio: es la accesibilidad del sitio, viviendo en el unico directorio cuyo
   trabajo es vaciarse. La entrega lo habria borrado el dia de la entrega, en silencio y con todos
   los checks en verde. */
file_put_contents( $sb . '/es-page-otra.php', "<?php\nfunction es_build_otra() { return 1; }\n" );
/* Un hook citado SOLO en un comentario no mantiene vivo un fichero muerto: fichero aparte, para
   que la asercion pueda fallar. La primera version metia el comentario dentro del fichero que SI
   engancha, asi que decia "no se borro" por el motivo equivocado y no podia distinguir nada. */
file_put_contents( $sb . '/es-muerto.php', "<?php\n/* add_action( 'init', 'viejo' ); ya no se usa */\nfunction viejo() { return 1; }\n" );
file_put_contents( $sb . '/es-a11y.php', "<?php\nfunction wrap( \$h ) { return \$h; }\nadd_action( 'template_redirect', 'wrap', 1 );\n" );
$r    = grab( 'es_sandbox_purge' );
$left = $r['ret'];
ok( ! in_array( 'es-page-otra.php', $left, true ), 'un script de build se borra como siempre' );
ok( ! in_array( 'es-muerto.php', $left, true ), 'un hook citado solo en un COMENTARIO no mantiene vivo un fichero muerto' );
ok( in_array( 'es-a11y.php', $left, true ), 'pero uno que registra hooks NO se borra: corre en cada visita, no es andamio' );
ok( has( $r['out'], 'template_redirect' ), 'y el aviso NOMBRA el hook, que es lo que lo delata' );
ok( has( $r['out'], 'tema hijo' ), 'diciendo adonde tiene que mudarse' );
ok( array( 'template_redirect' ) === es_sandbox_runtime_hooks( $sb . '/es-a11y.php' ), 'el detector devuelve el hook, no un booleano: por eso el aviso puede nombrarlo' );
ok( array() === es_sandbox_runtime_hooks( $sb . '/no-existe.php' ), 'y un fichero que no existe no tiene hooks, no revienta' );
unlink( $sb . '/es-a11y.php' );

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
ok( has( $d[0], 'contacto' ) && has( $d[0], '#' . $c ), 'la fila da los DOS hechos: lo que el manifiesto llama y lo que el sitio dice' );
ok( ! has( $d[0], 'alguien la movio' ), 'y NO afirma quien lo hizo: eso es una causa, y esta fila solo puede observar' );

/* La misma fila, otra causa entera. Encontrado probando contra WordPress de verdad: se anoto
   `front => 2` DENTRO del mapa `pages`, que es slug->id, asi que la clave no era un slug y nadie
   habia movido nada. La version anterior de esta fila diagnosticaba "alguien la movio fuera de este
   framework" con total seguridad, y mandaba al lector a buscar una edicion que nunca existio. Un
   informe cuya regla es no afirmar lo que no leyo no puede permitirse un porque inventado. */
wp_fake_reset();
$h = wp_fake_page( 'inicio' );
es_manifest_record( 'pages', array( 'front' => $h ) );
$d = grab( 'es_manifest_verify' )['ret'];
ok( 1 === count( $d ) && has( $d[0], 'inicio' ), 'una clave que no es un slug produce la misma fila' );
ok( has( $d[0], 'nunca fuera un slug' ), 'y la fila admite ESA lectura tambien, en vez de elegir una' );

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

/* ---------------------------------------------------------------------------
 * Restaurar y podar. Un respaldo que nadie puede restaurar no es un respaldo.
 * ------------------------------------------------------------------------- */
echo "--- un respaldo se puede DESHACER, y cada pieza se comprueba ---\n";

wp_fake_reset();
$pid = wp_fake_page(
	'servicios',
	'publish',
	'Titulo VIEJO',
	'',
	array(
		'_elementor_data'   => '[{"viejo":1}]',
		'_wp_page_template' => 'plantilla-vieja.php',
	)
);
$a = null;
grab(
	function () use ( $els, &$a ) {
		return es_save_page( 'servicios', 'Titulo NUEVO', $els, 'elementor_header_footer', $a );
	}
);
ok( 'Titulo NUEVO' === get_post_field( 'post_title', $pid ), 'tras reconstruir, la pagina lleva el titulo nuevo' );
ok( 'elementor_header_footer' === get_post_meta( $pid, '_wp_page_template' ), 'y la plantilla nueva' );

$r   = grab(
	function () use ( $pid ) {
		return es_restore_page_state( $pid );
	}
);
$res = $r['ret'];
ok( '' !== $res['key'], 'restaurar sin nombrar clave coge la mas reciente' );
ok( array() === $res['failed'], 'y no falla ninguna pieza' );
ok( 'Titulo VIEJO' === get_post_field( 'post_title', $pid ), 'el titulo vuelve al viejo' );
ok( 'plantilla-vieja.php' === get_post_meta( $pid, '_wp_page_template' ), 'la plantilla tambien' );
ok( has( (string) get_post_meta( $pid, '_elementor_data' ), 'viejo' ), 'y el layout anterior' );
ok( '' === $r['out'], 'sin avisos cuando todo cuadra' );

/* Restaurar tambien pisa: tiene que dejar su propia red antes. */
ok( '' !== $res['safety'], 'la restauracion guarda el estado que ella misma va a pisar' );
$r2 = grab(
	function () use ( $pid, $res ) {
		return es_restore_page_state( $pid, $res['safety'] );
	}
);
ok( 'Titulo NUEVO' === get_post_field( 'post_title', $pid ), 'asi que deshacer el deshacer devuelve al estado nuevo' );

/* Una clave que no existe no restaura nada a medias. */
wp_fake_reset();
$pid = wp_fake_page( 'x', 'publish', 'T', '', array( '_es_page_backup_20260101-000000' => array( 'post_title' => 'V' ) ) );
$r   = grab(
	function () use ( $pid ) {
		return es_restore_page_state( $pid, '_es_page_backup_NO_EXISTE' );
	}
);
ok( array() === $r['ret']['restored'], 'una clave inexistente no restaura nada' );
ok( has( $r['out'], '_es_page_backup_20260101-000000' ), 'y el aviso lista las que SI existen' );
ok( 'T' === get_post_field( 'post_title', $pid ), 'la pagina se queda como estaba' );

wp_fake_reset();
$pid = wp_fake_page( 'y' );
$r   = grab(
	function () use ( $pid ) {
		return es_restore_page_state( $pid );
	}
);
ok( array() === $r['ret']['restored'] && has( $r['out'], 'no tiene ningun respaldo' ), 'sin respaldos, se dice y no se toca nada' );

echo "--- y no se acumulan para siempre ---\n";
wp_fake_reset();
$pid = wp_fake_page( 'z' );
foreach ( array( '20260101-000001', '20260102-000002', '20260103-000003', '20260104-000004' ) as $s ) {
	$GLOBALS['wp']['meta'][ $pid ][ '_es_page_backup_' . $s ] = array( 'post_title' => $s );
}
$p = es_prune_backups( $pid, 2 );
ok( 2 === count( $p['kept'] ), 'podar a 2 deja 2' );
ok( 2 === count( $p['deleted'] ), 'y borra los otros 2' );
ok( has( $p['kept'][1], '20260104' ), 'los que quedan son los MAS RECIENTES' );
ok( has( $p['deleted'][0], '20260101' ), 'y los borrados los mas viejos' );
ok( array() === $p['still_there'], 'sin restos' );
ok( 2 === count( es_backup_keys( array( $pid ) )[ $pid ] ), 'y al releer, quedan 2 de verdad' );

$p = es_prune_backups( $pid, 5 );
ok( array() === $p['deleted'], 'podar por encima de lo que hay no borra nada' );
$p = es_prune_backups( $pid, 0 );
ok( 1 === count( $p['kept'] ), 'y podar a 0 conserva 1: eso no seria podar, seria borrar los respaldos' );

/* Un borrado ACEPTADO que no aterriza. delete_post_meta() devuelve false al fallar Y cuando no
   habia nada, asi que la prueba es la relectura, igual que en la purga del sandbox. */
wp_fake_reset();
$pid = wp_fake_page( 'w' );
foreach ( array( '20260101-000001', '20260102-000002', '20260103-000003' ) as $s ) {
	$GLOBALS['wp']['meta'][ $pid ][ '_es_page_backup_' . $s ] = array( 'post_title' => $s );
}
$GLOBALS['wp']['meta_ro'] = array( '_es_page_backup_20260101-000001' );
$r = grab(
	function () use ( $pid ) {
		return es_prune_backups( $pid, 1 );
	}
);
ok( array( '_es_page_backup_20260101-000001' ) === $r['ret']['still_there'], 'un borrado que no aterriza se reporta como SIGUE AHI' );
ok( ! in_array( '_es_page_backup_20260101-000001', $r['ret']['deleted'], true ), 'y NO se cuenta como borrado' );
ok( in_array( '_es_page_backup_20260102-000002', $r['ret']['deleted'], true ), 'mientras el que si se borro cuenta' );
ok( has( $r['out'], 'no se pudieron borrar' ), 'y avisa' );

echo "--- restaurar informa de lo VERIFICADO, no de lo intentado ---\n";
wp_fake_reset();
$pid = wp_fake_page( 'v', 'publish', 'Titulo VIEJO', '', array( '_wp_page_template' => 'vieja.php', '_elementor_data' => '[{"a":"b"}]' ) );
approve( 'v' );
$a   = null;
grab(
	function () use ( $els, &$a ) {
		return es_save_page( 'v', 'Titulo NUEVO', $els, 'elementor_header_footer', $a );
	}
);
/* La plantilla se niega a aceptar la restauracion: la pagina queda MEZCLADA y hay que decirlo. */
$GLOBALS['wp']['meta_ro'] = array( '_wp_page_template' );
$r = grab(
	function () use ( $pid ) {
		return es_restore_page_state( $pid );
	}
);
ok( in_array( '_wp_page_template', $r['ret']['failed'], true ), 'una pieza que no aterriza sale en failed' );
ok( in_array( 'post_title', $r['ret']['restored'], true ), 'y la que si aterrizo, en restored' );
ok( has( $r['out'], 'A MEDIAS' ), 'con un aviso de restauracion parcial' );
ok( has( $r['out'], '_wp_page_template' ), 'nombrando la pieza que falta' );

/* `_elementor_data` se guarda deslizado: sin wp_slash() la relectura no coincide. */
$GLOBALS['wp']['meta_ro'] = array();
wp_fake_reset();
$pid = wp_fake_page( 'u', 'publish', 'T', '', array( '_elementor_data' => '[{"txt":"con \\\\\"comillas\\\\\""}]' ) );
$a   = null;
grab(
	function () use ( $els, &$a ) {
		return es_save_page( 'u', 'T2', $els, 'elementor_header_footer', $a );
	}
);
$r = grab(
	function () use ( $pid ) {
		return es_restore_page_state( $pid );
	}
);
ok( in_array( '_elementor_data', $r['ret']['restored'], true ), 'un layout con comillas se restaura deslizado y la relectura cuadra' );

/* ---------------------------------------------------------------------------
 * The two rules that had a helper and no runtime.
 *
 * Both existed as prose: "nobody approves a write they have not been shown" was a house rule with
 * an explicit `(no verifier: …)` marker, and `es_set_front_page()` was a tested, documented
 * function nothing in the repo ever called. Prose is enforced on the builds that read it. These
 * assertions are about the build that does not.
 * ------------------------------------------------------------------------- */

echo "--- un build con el sandbox APAGADO deja de irse en silencio ---\n";

/* `.crashed` apaga el sandbox entero, pero `execute-php` requiere el builder a mano y un require
   explicito no pasa por el cargador: el build corre igual, escribe todas las paginas y reporta
   exito sobre un sitio que sigue degradado. project-context REPORTABA el modo seguro y nada
   actuaba: reportar un bloqueante que el paso siguiente se salta es la forma que esta rama borra. */
$sb = es_sandbox_dir();
@unlink( $sb . '/.crashed' );
wp_fake_reset();
approve( 'con-sandbox-ok' );
$r = grab(
	function () use ( $els ) {
		$a = null;
		return es_save_page( 'con-sandbox-ok', 'P', $els, 'elementor_header_footer', $a );
	}
);
ok( '' === $r['out'], 'sin .crashed no dice nada: el aviso tiene que doler solo cuando hay algo que mirar' );
ok( '' === es_safe_mode_check(), 'y el veredicto viene vacio' );

file_put_contents( $sb . '/.crashed', '{"sandbox_file":"/ruta/es-roto.php","message":"undefined function"}' );
wp_fake_reset();
approve( 'con-sandbox-roto' );
$r = grab(
	function () use ( $els ) {
		$a = null;
		return es_save_page( 'con-sandbox-roto', 'P', $els, 'elementor_header_footer', $a );
	}
);
ok( $r['ret'] > 0, 'el modo seguro NO bloquea la escritura: la salida de un sandbox tumbado es ejecutar algo' );
ok( has( $r['out'], 'SANDBOX APAGADO' ), 'pero avisa, y fuerte' );
ok( has( $r['out'], 'es-roto.php' ), 'nombrando el fichero culpable, que es lo unico accionable' );
ok( 'es-roto.php' === es_safe_mode_check(), 'y devuelve el motivo, no un booleano' );

/* Una vez por peticion, al reves que la aprobacion: un aviso sin aprobar es un hecho sobre UNA
   pagina y callarse esconderia las demas; el modo seguro es un hecho sobre el SITIO, y repetirlo
   por pagina enterraria las paginas debajo. */
$out2 = '';
foreach ( array( 'a', 'b', 'c' ) as $s ) {
	approve( $s );
	$rr    = grab(
		function () use ( $els, $s ) {
			$a = null;
			return es_save_page( $s, 'P', $els, 'elementor_header_footer', $a );
		}
	);
	$out2 .= $rr['out'];
}
ok( '' === $out2, 'y no lo repite en cada pagina: es un hecho del sitio, no de la pagina' );

/* Un .crashed que no es JSON tampoco se ignora: fallar cerrado es no llamar sano a lo que no se
   pudo leer. */
@unlink( $sb . '/.crashed' );
file_put_contents( $sb . '/.crashed', 'algo se rompio y nadie escribio json' );
ok( '' !== es_safe_mode_check(), 'un .crashed ilegible sigue siendo modo seguro' );
@unlink( $sb . '/.crashed' );

echo "--- aprobacion y portada: las dos reglas que solo vivian en la prosa ---\n";

wp_fake_reset();
$r = grab(
	function () use ( $els ) {
		$a = null;
		return es_save_page( 'sin-preflight', 'Sin preflight', $els, 'elementor_header_footer', $a );
	}
);
ok( $r['ret'] > 0, 'escribir sin preflight NO bloquea: un build interrumpido tiene que poder reanudarse' );
ok( has( $r['out'], 'es_overwrite_preflight' ), 'pero avisa, nombrando la funcion que faltaba' );
ok( has( $r['out'], 'sin-preflight' ), 'y el slug que se escribio sin que nadie lo viera' );

wp_fake_reset();
$r = grab(
	function () {
		return es_approval_check( 'nadie' );
	}
);
ok( false === $r['ret'], 'es_approval_check() devuelve el veredicto, no solo lo imprime' );
approve( 'aprobado' );
ok( true === es_approval_check( 'aprobado' ), 'y un slug que paso por el bloque impreso da true' );

/* El caso que un flag por peticion no ve: se aprueban cinco y se escriben seis. */
wp_fake_reset();
approve( 'uno', 'dos', 'tres', 'cuatro', 'cinco' );
$out_aprobadas = '';
foreach ( array( 'uno', 'dos', 'tres', 'cuatro', 'cinco' ) as $slug_ok ) {
	$r              = grab(
		function () use ( $els, $slug_ok ) {
			$a = null;
			return es_save_page( $slug_ok, 'P', $els, 'elementor_header_footer', $a );
		}
	);
	$out_aprobadas .= $r['out'];
}
ok( '' === $out_aprobadas, 'las cinco aprobadas se escriben en silencio' );
$r = grab(
	function () use ( $els ) {
		$a = null;
		return es_save_page( 'seis', 'La sexta', $els, 'elementor_header_footer', $a );
	}
);
ok( has( $r['out'], 'seis' ), 'y la sexta, que nadie aprobo, avisa nombrandose — un flag por peticion ya estaria callado' );

/* La portada. */
wp_fake_reset();
ok( 'nothing-built' === es_front_page_check(), 'sin paginas guardadas no hay veredicto: "/" no es asunto de este build' );
$r = grab( 'es_front_page_check' );
ok( '' === $r['out'], 'y no dice nada, porque no hay nada que reclamar' );

wp_fake_reset();
approve( 'inicio' );
grab(
	function () use ( $els ) {
		$a = null;
		return es_save_page( 'inicio', 'Inicio', $els, 'elementor_header_footer', $a );
	}
);
$r = grab( 'es_front_page_check' );
ok( 'posts' === $r['ret'], 'con paginas guardadas y el blog en "/", el veredicto es posts' );
ok( has( $r['out'], 'es_set_front_page' ), 'y avisa nombrando la funcion que cierra el hueco' );
ok( has( $r['out'], 'BLOG' ), 'diciendo lo que el visitante ve de verdad al entrar por la raiz' );

grab(
	function () {
		return es_set_front_page( 'inicio' );
	}
);
$r = grab( 'es_front_page_check' );
ok( 'page' === $r['ret'], 'puesta la portada, el veredicto pasa a page' );
ok( '' === $r['out'], 'y se calla: un sitio correcto no tiene que oir nada' );

/* Que la comprobacion este CABLEADA, que es el hallazgo entero. Con ES_AUDIT_SILENT el veredicto
   de contenedores no imprime, asi que lo unico que puede quedar en stdout es este aviso. */
wp_fake_reset();
approve( 'servicios' );
grab(
	function () use ( $els ) {
		$a = null;
		return es_save_page( 'servicios', 'Servicios', $els, 'elementor_header_footer', $a );
	}
);
$r = grab( 'es_audit_summary' );
ok( has( $r['out'], 'BLOG' ), 'es_audit_summary() lo dice: la linea que el operador tiene orden de leer antes de desplegar' );
ok( 0 === $r['ret'], 'y su entero no cambia — los llamadores ya ramifican sobre el, un quinto significado romperia uno' );

/* Lo que se guarda se anota por el slug donde ATERRIZO, no por el que se pidio. */
wp_fake_reset();
$GLOBALS['wp']['rename_to'] = 'contacto-2';
approve( 'contacto' );
grab(
	function () use ( $els ) {
		$a = null;
		return es_save_page( 'contacto', 'Contacto', $els, 'elementor_header_footer', $a );
	}
);
ok( isset( $GLOBALS['es_saved_pages']['contacto-2'] ), 'una pagina renombrada se anota en su slug real' );
ok( ! isset( $GLOBALS['es_saved_pages']['contacto'] ), 'y NO en el que se pidio: ahi contesta otra cosa' );

wp_fake_reset();
$GLOBALS['wp']['insert_ret'] = 0;
approve( 'fantasma' );
grab(
	function () use ( $els ) {
		$a = null;
		return es_save_page( 'fantasma', 'Fantasma', $els, 'elementor_header_footer', $a );
	}
);
ok( array() === $GLOBALS['es_saved_pages'], 'una escritura rechazada no anota nada: la lista es lo que sobrevivio, no lo que se intento' );

/* ---------------------------------------------------------------------------
 * La capa de tokens.
 *
 * SKILL.md lleva desde siempre diciendole al operador que "cambie las constantes
 * de paleta y tipografia" de este fichero. Nunca hubo constantes: habia 51
 * colores, 9 familias y 5 sombras escritos a mano por el medio. Estas
 * afirmaciones son lo que convierte es_tokens() en una capa de verdad y no en un
 * array decorativo que nadie lee.
 * ------------------------------------------------------------------------- */
echo "--- los tokens llegan a los datos emitidos ---\n";

/* Todo lo que hay debajo compara contra ESTO, nunca contra un color escrito a
   mano en el test. Afirmar "#0FA968" aqui seria clavar el verde por defecto en
   dos sitios, y la tarea siguiente tiene permiso para moverlo. */
$es_defaults = es_tokens();

/* ---------------------------------------------------------------------------
 * El volcado dorado.
 *
 * Nada de lo que hay comprometido protegia la identidad byte a byte que esta
 * tarea existe para establecer: la prueba vivia en un script de usar y tirar
 * que se borro. Lo que eso cuesta esta medido, no supuesto —— con las cuatro
 * suites en verde se podia cambiar el valor por defecto del acento, poner
 * Comic Sans MS de familia de titulares, colapsar border_panel dentro de
 * border (justo el colapso que la Tarea 1 prohibe), sustituir elev_hover
 * entero y cambiar el fondo de la tarjeta de caracteristica de bg a
 * surface_inverse, y TODO seguia diciendo OK. Una tarjeta blanca que se vuelve
 * casi negra pasaba todas las comprobaciones que teniamos.
 *
 * El fichero dorado convierte "fue identico una vez" en una propiedad que se
 * vuelve a comprobar en cada ejecucion. No lo congela para siempre: el paso
 * "APUNTA EL DESPLAZAMIENTO" de la Tarea 2 es exactamente regenerarlo y
 * ensenar el diff. Un cambio que aparece en el diff es una decision; uno que
 * no aparece en ningun sitio es el fallo.
 *
 * Se ejecuta en un PROCESO APARTE a proposito: el volcado tiene que ver los
 * tokens tal y como los ve un build de verdad, no como los deja este fichero
 * despues de sobrescribirlos treinta veces mas abajo.
 * ------------------------------------------------------------------------- */
$dump_php = dirname( __DIR__ ) . '/tests/dump-emitted.php';
$oro_ruta = dirname( __DIR__ ) . '/tests/fixtures/emitted-golden.txt';
$salida   = null;
if ( function_exists( 'shell_exec' ) && is_file( $dump_php ) && is_file( $oro_ruta ) ) {
	$salida = shell_exec( escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( $dump_php ) );
}
if ( null === $salida ) {
	/* Ni verde ni rojo: no se pudo comprobar. Un "no se pudo" contado como OK
	   es la misma enfermedad que este fichero entero existe para quitar. */
	fwrite( STDERR, "ENTORNO: no se pudo ejecutar tests/dump-emitted.php (¿shell_exec deshabilitado?); el volcado dorado queda SIN COMPROBAR\n" );
	exit( 2 );
}
/* El arbol de trabajo es CRLF y el repositorio guarda LF (core.autocrlf=true),
   asi que el fichero dorado leido de disco y el volcado recien emitido no
   tienen por que traer el mismo salto de linea. Se normalizan los dos. */
$sin_cr  = function ( $s ) {
	return str_replace( "\r\n", "\n", (string) $s );
};
$salida  = $sin_cr( $salida );
$oro_txt = $sin_cr( file_get_contents( $oro_ruta ) );

/* Las tres trampas del volcado, afirmadas antes de compararlo con nada.
   Sin ABSPATH, es-builder.php:6-8 sale por la puerta y el volcado es de 0
   bytes —— y `cmp` de dos ficheros VACIOS pasa. */
ok( '' !== trim( $salida ), 'el volcado trae contenido: dos ficheros vacios tambien son identicos, y eso se reporta como aprobado' );
ok( substr_count( $salida, "\n" ) > 2000, 'y trae un volcado entero, no un fragmento: ' . substr_count( $salida, "\n" ) . ' lineas' );
ok( has( $salida, "===== FIN DEL VOLCADO =====\n" ), 'y termina en el centinela, asi que un volcado cortado no puede hacerse pasar por una coincidencia' );
ok( $oro_txt === $salida, 'lo que emite el constructor es, byte a byte, lo que dice tests/fixtures/emitted-golden.txt' );

/* ---------------------------------------------------------------------------
 * es_rgba(): el color de un token con un velo por encima.
 *
 * Siete valores metian el color de OTRO token dentro de un rgba() sin
 * tokenizar, asi que cambiar el acento no quitaba el verde: la marca se volvia
 * azul marino y el boton conservaba el halo verde. El formato es parte del
 * contrato —— este fichero lleva `0.10` y `0.5` a la vez, que NO son la misma
 * forma, y un alfa numerico reescribiria uno de los dos en silencio.
 * ------------------------------------------------------------------------- */
ok( 'rgba(15,169,104,0.55)' === es_rgba( '#0FA968', '0.55' ), 'es_rgba() escribe el triplete con el espaciado y el alfa exactos' );
ok( 'rgba(15,169,104,0.10)' === es_rgba( '#0FA968', '0.10' ), 'y respeta 0.10, que como numero seria 0.1 y moveria bytes' );
ok( 'rgba(15,169,104,0.5)' === es_rgba( '#0fa968', '0.5' ), 'y le da igual la caja del hex' );
ok( 'rgba(255,255,255,0.75)' === es_rgba( '#fff', '0.75' ), 'y entiende la forma corta de tres digitos' );
$r = grab(
	function () {
		return es_rgba( 'verde', '0.5' );
	}
);
ok( 'rgba(0,0,0,0)' === $r['ret'], 'un valor que no es hex no se convierte en un color plausible: se queda sin pintar' );
ok( has( $r['out'], 'verde' ), 'y lo dice en voz alta nombrando el valor que no supo leer' );

/* ---------------------------------------------------------------------------
 * Un unico sitio que ejercite todas las claves.
 *
 * es_card y es_cta_banner pasan por es_img(), que aqui no encuentra nada y
 * AVISA: por eso va dentro de grab(), que ademas es como el resto del fichero
 * mira lo que se imprime.
 *
 * es_uid_reset() al entrar porque los ids son un contador global: sin el, dos
 * llamadas seguidas devuelven cadenas distintas aunque no haya cambiado ni un
 * token, y la comparacion numerica de mas abajo no podria distinguir "esta
 * clave la lee alguien" de "los ids han avanzado".
 * ------------------------------------------------------------------------- */
function es_token_probe() {
	es_uid_reset( 'probe' );
	return es_card_hover_css() . es_products_css() . json_encode(
		array(
			es_eyebrow( 'etiqueta' ),
			es_p( 'texto' ),
			es_h( 'titulo' ),
			es_btn( 'Comprar', '#', 'primary' ),
			es_btn( 'Comprar', '#', 'dark' ),
			es_btn( 'Comprar', '#', 'outline' ),
			es_btn( 'Comprar', '#', 'outline-light' ),
			es_card( 'foto', 'titulo', 'texto' ),
			es_cta_banner( 'foto', 'titulo', 'texto', 'Ir', '#' ),
			es_iconbox( 'fas fa-check', 'titulo', 'texto' ),
			es_feature_card( 'fas fa-check', 'titulo', 'texto' ),
		)
	);
}

/* La capa solo es real si cambiar un token cambia lo que se emite. Un token que
   nadie lee devuelve todos los proyectos al mismo aspecto. */
es_tokens( array( 'accent' => '#B4001F' ) );
$json = json_encode( es_btn( 'Comprar', '#', 'primary' ) );
ok( has( $json, '#B4001F' ), 'el acento del token llega al boton primario' );
ok( ! has( $json, $es_defaults['accent'] ), 'el acento por defecto ya no aparece tras el override' );

/* Y la MISMA pregunta en la otra forma en la que se escribe un color. Afirmar
   solo el hex es la razon de que esto se colara: siete tokens llevaban el verde
   dentro de un rgba() —— accent_wash, elev_accent, elev_accent_cart —— y
   sobrevivian intactos a cambiar el acento. El hex desaparecia, el halo no.
   El triplete se calcula del valor por defecto, nunca se escribe a mano aqui. */
$triple_defecto = substr( es_rgba( $es_defaults['accent'], 'X' ), 5, -3 );
ok( '15,169,104' === $triple_defecto, 'el triplete de referencia sale del token, no de un numero escrito en el test' );
es_tokens( array( 'accent' => '#B4001F' ) );
$todo = grab( 'es_token_probe' );
ok( ! has( $todo['ret'], $es_defaults['accent'] ), 'cambiar el acento no deja ni una vez el hex por defecto en toda la pagina' );
ok( ! has( $todo['ret'], $triple_defecto ), 'ni una sola vez el mismo verde escrito como triplete rgba dentro de una sombra' );

/* La familia es el otro literal que se repite por todo el fichero. */
es_tokens( array( 'font_body' => 'Inter' ) );
$body = json_encode( array( es_p( 'texto' ), es_btn( 'Comprar', '#', 'outline' ) ) );
ok( has( $body, 'Inter' ), 'la familia de cuerpo sale del token, no de cada funcion' );
ok( ! has( $body, $es_defaults['font_body'] ), 'y la de por defecto ya no aparece en ningun sitio' );

/* Una clave mal escrita AVISA en vez de devolver algo plausible: un color vacio
   en Elementor se ve como "el tema decidio", no como un error. */
$r = grab(
	function () {
		return es_t( 'acento' );
	}
);
ok( '' === $r['ret'], 'una clave inexistente devuelve cadena vacia, no null ni la clave' );
ok( has( $r['out'], 'acento' ), 'y lo dice en voz alta nombrando la clave que se escribio mal' );

/* El otro lado, y el mas probable de los dos: la clave mal escrita en el
   OVERRIDE. es_t() solo vigila la LECTURA, asi que es_tokens(['acento'=>...])
   se aceptaba entero, no cambiaba nada y no avisaba de nada —— en el unico
   punto de edicion que toda esta capa existe para crear. */
$r = grab(
	function () {
		es_tokens( array( 'acento' => '#001133' ) );
		return es_t( 'accent' );
	}
);
ok( has( $r['out'], 'acento' ), 'un override con una clave que no es token avisa nombrandola' );
ok( '#001133' !== $r['ret'], 'y deja claro por que hace falta el aviso: el override no ha cambiado el acento' );

/* ---------------------------------------------------------------------------
 * La comprobacion estructural, y la razon de que exista.
 *
 * Una afirmacion por clave escrita a mano solo cubre las claves que alguien se
 * acordo de escribir, y el literal que sobrevive es siempre el del sitio en el
 * que nadie penso. Esto sustituye TODAS las claves a la vez por centinelas y
 * pregunta dos cosas de cada una: que su centinela llegue a los datos (la clave
 * se lee en algun sitio) y que su valor por defecto desaparezca (ningun sitio
 * de llamada se quedo el literal escrito a mano). Crece sola con las claves que
 * anadan las tareas siguientes.
 *
 * Los centinelas van nombrados por la clave, no numerados: anadir un token
 * renumeraba —— y por tanto ensuciaba —— todas las demas lineas del volcado
 * dorado. El `_Z` final es lo que impide que `accent` case dentro de
 * `ZTOK_accent_hover_Z`.
 * ------------------------------------------------------------------------- */
$sentinelas = array();
$numericas  = array();
$raras      = array();
foreach ( $es_defaults as $clave => $valor ) {
	if ( is_string( $valor ) ) {
		$sentinelas[ $clave ] = 'ZTOK_' . $clave . '_Z';
	} elseif ( is_int( $valor ) || is_float( $valor ) ) {
		$numericas[ $clave ] = $valor;
	} else {
		$raras[] = $clave;
	}
}
/* Un token que no es ni texto ni numero se caia por el `is_string()` de antes y
   se quedaba SIN cubrir en silencio. Aqui no se salta nada: o esta en uno de
   los dos grupos, o esto se pone rojo. */
ok( array() === $raras, 'ningun token se escapa de la comprobacion por no ser ni texto ni numero: ' . ( $raras ? implode( ', ', $raras ) : 'ninguno' ) );
ok( count( $sentinelas ) + count( $numericas ) === count( $es_defaults ), 'y los dos grupos suman las ' . count( $es_defaults ) . ' claves que hay' );

es_tokens( $sentinelas + $es_defaults );
$probe    = grab( 'es_token_probe' );
$emitido  = $probe['ret'];
$sin_leer = array();
$literal  = array();

/* Un valor de token puede coincidir, byte a byte, con gramatica CSS que no
   tiene nada que ver con el. En cuanto la Tarea 2 anada 'elev_rest' => 'none',
   `display:none!important` de es_products_css() (es-builder.php:337) ya esta en
   la salida, y la comprobacion de literales se pone ROJA sobre codigo CORRECTO.
   La salida barata para el siguiente implementador seria aflojar la afirmacion,
   que es justo lo que prohibe la Restriccion Global 1.
   La misma exposicion existe hoy para `auto`, `cover`, `center`, `solid`,
   `hidden`, `column`, `full`, `boxed`, `classic` y `custom`: todas estan ya en
   la salida sin ser el valor de ningun token.
   Asi que el residuo se DECLARA: cuantas veces aparecen esos bytes sin que
   ningun token los haya puesto, y por que. Declararlo es una decision visible;
   no declararlo es el fallo. Y se compara con ===, no con <=, para que un sitio
   de llamada que se quede el literal escrito a mano suba el contador por encima
   del residuo y esto se ponga rojo igual.
   LIMITE HONESTO: una busqueda de subcadenas no puede distinguir `display:none`
   de un 'none' escrito a mano dentro del mismo blob de CSS. Esta tabla es el
   limite real de la comprobacion, no un adorno; lo que la sostiene es que cada
   linea obliga a escribir POR QUE. */
$residuo = array(
	/* valor => array( cuantas veces, de donde salen ) */
	'none' => array( 1, 'display:none!important sobre a.added_to_cart en es_products_css(): esconde el enlace "Ver carrito" redundante, y no tiene nada que ver con elev_rest, que es la sombra en reposo' ),
);
foreach ( $sentinelas as $clave => $centinela ) {
	if ( ! has( $emitido, $centinela ) ) {
		$sin_leer[] = $clave;
	}
	$valor    = $es_defaults[ $clave ];
	$esperado = isset( $residuo[ $valor ] ) ? $residuo[ $valor ][0] : 0;
	$veces    = substr_count( $emitido, $valor );
	if ( $veces !== $esperado ) {
		$literal[] = $clave . ' (' . $veces . ' apariciones, ' . $esperado . ' declaradas)';
	}
}
/* Y una declaracion de residuo que ya no corresponde a ningun token es basura
   que solo puede tapar el proximo fallo. */
$residuo_muerto = array();
foreach ( $residuo as $valor => $porque ) {
	if ( ! in_array( $valor, $es_defaults, true ) ) {
		$residuo_muerto[] = $valor;
	}
}
ok( array() === $sin_leer, 'todas las claves de texto las lee alguien: ' . ( $sin_leer ? 'sin leer -> ' . implode( ', ', $sin_leer ) : 'ninguna sobra' ) );
ok( array() === $literal, 'ningun sitio de llamada se quedo el literal: ' . ( $literal ? 'todavia escrito a mano -> ' . implode( ', ', $literal ) . ' | si son bytes de gramatica CSS y no del token, declaralo en $residuo con el motivo' : 'ninguno' ) );
ok( array() === $residuo_muerto, 'no hay residuos declarados que ya no correspondan a ningun token: ' . ( $residuo_muerto ? implode( ', ', $residuo_muerto ) : 'ninguno' ) );

/* Las claves NUMERICAS no admiten un centinela de texto: alimentan aritmetica,
   y `16 * 1.333` no deja los bytes de ningun centinela en ningun sitio. Por eso
   el `is_string()` que habia aqui antes cubria CERO de las claves que trae la
   Tarea 2 —— anadir `radius => 10` y `sp_scale => 1.0` que no lee nadie dejaba
   la suite en verde, mientras que el equivalente de texto moria bien.
   La pregunta que importa es la misma —— ¿la lee alguien? —— y se responde
   moviendo la clave a un valor fuera de rango y exigiendo que la salida CAMBIE.
   Eso vale igual para un numero que se emite tal cual y para uno del que se
   deriva otro, que es lo que un centinela literal no aguantaria.
   Y se mueve en las DOS direcciones, que es lo que la primera version de esto
   no hacia. Solo subia a 9973, y hay una forma de token para la que subir no
   dice nada: un TOPE. `fs_h1_max` recorta por arriba, este fichero no emite
   ningun paso que llegue al tope, y subirlo a 9973 deja la salida intacta ——
   asi que una clave que SI lee es_fs() se reportaba como que no la lee nadie.
   Bajarla a 1 recorta todos los tamanos y la salida se mueve entera.
   Esto no afloja nada: la pregunta sigue siendo "¿la lee alguien?", y una clave
   que no lee nadie no mueve la salida en NINGUNA de las dos direcciones. Lo que
   cambia es que ahora la pregunta se responde bien tambien para los topes. */
es_tokens( $es_defaults );
$referencia         = grab( 'es_token_probe' );
$numericas_sin_leer = array();
foreach ( $numericas as $clave => $valor ) {
	$movio = false;
	foreach ( is_int( $valor ) ? array( 9973, 1 ) : array( 9973.0, 0.5 ) as $fuera ) {
		$movido           = $es_defaults;
		$movido[ $clave ] = $fuera;
		es_tokens( $movido );
		$m = grab( 'es_token_probe' );
		if ( $m['ret'] !== $referencia['ret'] ) {
			$movio = true;
		}
	}
	if ( ! $movio ) {
		$numericas_sin_leer[] = $clave;
	}
}
ok( array() === $numericas_sin_leer, 'mover una clave numerica fuera de rango mueve los datos emitidos: ' . ( $numericas_sin_leer ? 'no la lee nadie -> ' . implode( ', ', $numericas_sin_leer ) : count( $numericas ) . ' claves numericas comprobadas' ) );

/* Y el otro lado de lo mismo: ninguna funcion pide una clave que no existe.
   Sin esto, borrar una entrada de es_tokens() manda un color VACIO a Elementor
   —— que se ve como "asi lo quiso el tema", no como un fallo —— y las dos
   comprobaciones de arriba ni se enteran, porque la clave ya no esta en la lista
   que recorren. es_img() tambien avisa aqui (no hay imagenes en este fixture),
   asi que se mira el aviso concreto y no el buffer entero. */
ok( ! has( $probe['out'], 'es_t(' ), 'construir una pagina no pide ninguna clave inexistente' );

/* ---------------------------------------------------------------------------
 * Roles, no apariencias.
 *
 * El boton fantasma vive sobre un heroe OSCURO. Su relleno de hover leia
 * es_t('bg') —— el color de PAGINA —— y era invisible porque hoy bg y la tinta
 * inversa son los dos #FFFFFF. Un cliente con fondo crema recibia un relleno
 * crema sobre un heroe casi negro.
 *
 * Esta comprobacion no generaliza: es la pregunta concreta "¿depende este
 * elemento de un token cuyo rol no le toca?", y hay que escribirla por sitio.
 * Lo que si escala es la seccion de centinelas del volcado dorado, donde cada
 * ajuste aparece con el NOMBRE del rol que lo alimenta y una confusion de roles
 * deja de ser invisible en la revision.
 * ------------------------------------------------------------------------- */
es_tokens( array( 'bg' => '#FCF7EE' ) );
$fantasma = json_encode( es_btn( 'Ir', '#', 'outline-light' ) );
ok( ! has( $fantasma, '#FCF7EE' ), 'el boton fantasma sobre heroe oscuro no toma NADA del color de pagina' );
es_tokens( array( 'on_inverse' => '#FCF7EE' ) );
$fantasma = json_encode( es_btn( 'Ir', '#', 'outline-light' ) );
ok( has( $fantasma, '#FCF7EE' ), 'lo toma de la tinta inversa, que es el rol que le corresponde' );

/* Los tokens derivados existen para que UN punto de edicion sea de verdad uno:
   cambiar el acento tiene que mover tambien todo lo que lo lleva dentro. */
es_tokens( array( 'accent' => '#123456' ) );
ok( 'rgba(18,52,86,0.10)' === es_t( 'accent_wash' ), 'cambiar el acento mueve el tinte derivado' );
ok( '0 12px 26px -10px rgba(18,52,86,0.55)' === es_t( 'elev_accent' ), 'y el halo, conservando su geometria' );
/* El estado HOVER es la otra mitad de lo mismo, y la que faltaba: accent_hover
   era un verde oscuro elegido a mano al lado de un acento que el cliente SI
   cambia, asi que una marca azul marino recibia un boton azul y un hover verde.
   Es la enfermedad de es_rgba() un nivel mas arriba —— un punto de edicion que
   solo es uno si te acuerdas tambien del otro.
   Sin esta afirmacion el unico que cazaba una es_shade() rota era el volcado
   dorado, y un volcado se regenera; una propiedad, no. */
ok( '#0F2A46' === es_t( 'accent_hover' ), 'el hover del acento se deriva del acento: cambiarlo a azul da un azul mas oscuro, no el verde de la casa' );
es_tokens( array( 'border' => '#123456' ) );
ok( '#113150' === es_t( 'border_hover' ), 'y el filete hace lo mismo con el suyo' );
/* Y el precio de haber hecho esto derivado: CERO en la marca por defecto. El
   factor 0.815 reproduce exactamente el #0C8A55 que estaba escrito a mano, asi
   que la derivacion no movio ni un byte del verde de la casa —— solo arreglo a
   todas las demas marcas. Clavado aqui para que se note si alguien lo toca. */
es_tokens( $es_defaults );
ok( '#0C8A55' === es_t( 'accent_hover' ), 'y con la marca por defecto sale el mismo #0C8A55 de siempre: derivarlo no costo bytes' );
/* La entrada ilegible avisa en vez de devolver un color plausible, igual que
   es_rgba(): un hover vacio en Elementor se ve como "asi lo quiso el tema". */
$r = grab(
	function () {
		return es_shade( 'azul', 0.8 );
	}
);
ok( '' === $r['ret'], 'un valor que no es hex no se convierte en un color plausible al oscurecerlo' );
ok( has( $r['out'], 'azul' ), 'y lo dice en voz alta nombrando el valor que no supo leer' );
es_tokens( array( 'accent' => '#123456' ) );

/* ...y quien tenga un halo que NO es su acento tiene que poder decirlo. */
es_tokens( array( 'accent' => '#123456', 'elev_accent' => '0 1px 2px rgba(0,0,0,0.9)' ) );
ok( '0 1px 2px rgba(0,0,0,0.9)' === es_t( 'elev_accent' ), 'un override explicito de una clave derivada gana a la derivacion' );

/* ---------------------------------------------------------------------------
 * Los ejes se mueven.
 *
 * La afirmacion falsable de todo este esfuerzo: cambia el ratio y se mueve la
 * jerarquia entera; cambia el multiplicador y se mueve el ritmo entero. Si esto
 * pasara con las posiciones de eje intercambiadas y la salida no cambiase, el
 * eje seria decorativo —— que es exactamente lo que era antes de esta tarea.
 * ------------------------------------------------------------------------- */
echo "--- los ejes mueven de verdad los datos emitidos ---\n";

es_tokens( array( 'type_ratio' => 1.618, 'fs_h1_max' => 120 ) );
$monumental = es_fs( 3 );
es_tokens( array( 'type_ratio' => 1.200, 'fs_h1_max' => 48 ) );
$contenido = es_fs( 3 );
ok( $monumental > $contenido, 'un ratio monumental produce un display mayor que uno contenido' );
/* Y con los numeros clavados, porque `$a > $b` tambien lo cumple una funcion que
   devuelva `$step * 2`: eso mide el orden, no la escala. */
ok( 67.8 === $monumental && 27.6 === $contenido, 'y los pasos son los que dicta el ratio, no solo el orden: 67.8 contra 27.6' );

/* El tope, afirmado donde MUERDE. La version obvia —— `$monumental <= 120 &&
   $contenido <= 48` —— es vacua: los dos pasos valen 67.8 y 27.6, o sea que
   pasa igual de verde con el min() borrado, y el mutante que quita el tope
   sobrevive sin que nadie se entere. En la posicion `classic` el tope no entra
   hasta el paso 5 y este fichero no emite nada por encima del 3, asi que hay
   que preguntarselo a un paso que si lo alcance. */
es_tokens( array( 'type_ratio' => 1.618, 'fs_h1_max' => 120 ) );
ok( 120.0 === es_fs( 8 ), 'un paso que se sale por arriba se recorta en el tope de su propia posicion (120)' );
ok( 67.8 === es_fs( 3 ), 'y un paso que cabe NO se toca: el tope recorta, no aplana' );
es_tokens( array( 'type_ratio' => 1.200, 'fs_h1_max' => 48 ) );
ok( 48.0 === es_fs( 8 ), 'y cada posicion respeta su propio tope, no uno compartido (48)' );

es_tokens( array( 'sp_scale' => 1.7 ) );
$aireado = es_sp( 88 );
$caja_aireada = es_box( 88, 24, 88, 24 );
es_tokens( array( 'sp_scale' => 0.8 ) );
$apretado = es_sp( 88 );
ok( $aireado > $apretado, 'la densidad generosa deja mas aire que la compacta' );
ok( 150 === $aireado && 70 === $apretado, 'y los valores derivados son los esperados, no solo el orden' );
ok( '150' === $caja_aireada['top'], 'y la densidad entra DENTRO de es_box(), asi que ningun sitio de llamada puede olvidarse' );

/* Las dos guardas de es_box(), que son la diferencia entre escalar el ritmo y
   escalar cosas que no son ritmo. */
es_tokens( array( 'sp_scale' => 1.7 ) );
$pct = es_box( 5, 0, 5, 0, '%' );
ok( '5' === $pct['top'], 'un porcentaje no se multiplica por la densidad: ya es relativo a otra cosa, y multiplicarlo no es un hueco menor sino otro layout' );
$borde = es_box_unscaled( 1, 1, 1, 1 );
ok( '1' === $borde['top'], 'y una anchura de borde tampoco: a densidad 1.7 un filete de 1px se redondearia a 2px, que es un borde mas gordo, no mas aire —— y dejaria la posicion `hairline` sin poder expresarse' );

/* La sombra en reposo, que antes NO EXISTIA: solo habia cinco sombras de hover
   y nada debajo, y por eso `hairline` y `soft-shadow` no se podian expresar. */
es_tokens( array( 'elev_rest' => '0 0 0 1px #E5E7E5' ) );
$reposo = es_card_hover_css() . es_products_css() . json_encode( es_feature_card( 'fas fa-check', 'titulo', 'texto' ) );
ok( 3 === substr_count( $reposo, 'box-shadow:0 0 0 1px #E5E7E5' ), 'las tres recetas de tarjeta —— image-box, rejilla de productos y feature card —— cogen la sombra en reposo' );
es_tokens( array( 'elev_rest' => 'none' ) );
$sin_reposo = es_card_hover_css() . es_products_css() . json_encode( es_feature_card( 'fas fa-check', 'titulo', 'texto' ) );
ok( ! has( $sin_reposo, '0 0 0 1px #E5E7E5' ), 'y volver a `none` las deja planas: la sombra en reposo sale del token, no esta clavada' );

/* Cualquier override reconstruye el juego DESDE los valores por defecto, asi que
   devolverlos entero restaura todos. Esa es la via de reset, y esta afirmada
   aqui para que nadie la cambie por acumulacion sin darse cuenta. */
es_tokens( $es_defaults );
ok( $es_defaults['accent'] === es_t( 'accent' ), 'el reset devuelve el acento por defecto' );
ok( $es_defaults['font_body'] === es_t( 'font_body' ), 'y tambien la familia tocada antes: un override reconstruye desde los valores por defecto' );
ok( $es_defaults['elev_accent'] === es_t( 'elev_accent' ), 'y los derivados vuelven a derivarse del acento por defecto' );
ok( ! array_key_exists( 'acento', es_tokens() ), 'y la clave inventada de antes no se queda pegada al juego de tokens' );

echo "\n$pass OK / $fail FAIL\n";
exit( $fail ? 1 : 0 );
