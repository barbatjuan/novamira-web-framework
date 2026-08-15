<?php
/**
 * Deterministic dump of everything the visual layer emits.
 *
 * Run:  php tests/dump-emitted.php > tests/fixtures/emitted-golden.txt
 *
 * WHY THIS FILE EXISTS. Task 1 of the axes plan claims the token extraction is
 * byte-identical. That claim lived in a scratch script that was deleted, so
 * nothing committed protected it, and the review proved what that costs: with
 * the four suites green you could change the accent's default, swap the heading
 * family for Comic Sans, collapse two drifted border tokens into one, replace a
 * whole shadow, and turn a white feature card near-black — and every check
 * still said OK. A golden dump turns "it was byte-identical once" into a
 * property the suite re-checks on every run.
 *
 * This dump is NOT frozen for ever. Task 2 is allowed — required — to move
 * values. Its "WRITE DOWN THE SHIFT" step is exactly: regenerate this file and
 * show the diff. A change that appears in the diff is a decision; a change that
 * does not appear anywhere is the defect.
 *
 * THREE TRAPS, all of them proven live on this machine, all defended here:
 *
 *  1. `define( 'ABSPATH', __DIR__ )` is mandatory. es-builder.php:6-8 exits
 *     without it, the dump is 0 bytes — and `cmp` of two EMPTY files passes.
 *     A green proof over nothing is worse than no proof. The guard below turns
 *     that into a loud exit(2) instead of a silent pass.
 *  2. PHP on Windows resolves `/tmp/x` to `C:\tmp\x` while Git Bash's `/tmp`
 *     is somewhere else entirely, so a file PHP "wrote" is not where the shell
 *     looks. Nothing here writes a file: it echoes to STDOUT and the shell
 *     redirects.
 *  3. get_posts()/wp_get_attachment_url() must be stubbed or es_img() finds
 *     nothing, and es_photo / es_card / es_cta_banner fall out of coverage
 *     while the dump gives no signal that it skipped them. es_cta_banner alone
 *     carries four token reads nothing else exercises.
 *
 * And the dump ends with a sentinel line, so a truncated dump cannot pass for
 * a match.
 */

define( 'ABSPATH', __DIR__ );
define( 'ES_AUDIT_SILENT', true );

/* Trap 3. A hit, not a miss: a miss makes es_img() warn (noise in the dump)
   AND strips the image helpers out of coverage. Values are constants so the
   dump stays deterministic across runs and machines. */
function get_posts( $args ) {
	$p     = new stdClass();
	$p->ID = 4242;
	return array( $p );
}
function wp_get_attachment_url( $id ) {
	return 'https://example.invalid/wp-content/uploads/es-golden-' . $id . '.jpg';
}

require_once dirname( __DIR__ ) . '/skills/elementor-core/assets/es-builder.php';

/* Trap 1, made loud. If the require above hit the ABSPATH guard and exited,
   we never get here at all; if it loaded something that is not the builder,
   this catches it before an empty dump can masquerade as a pass. */
if ( ! function_exists( 'es_tokens' ) || ! function_exists( 'es_feature_card' ) ) {
	fwrite( STDERR, "ENTORNO: es-builder.php no se cargo (¿falta ABSPATH?). El volcado seria VACIO.\n" );
	exit( 2 );
}

/**
 * Every visual helper, every branch that carries a token of its own.
 *
 * es_btn's four style branches are all here on purpose: the branch you leave
 * out is the branch that keeps its hand-written literal, and no check would
 * ever look at it.
 */
function es_dump_visuals() {
	return array(
		'es_box'            => es_box( 88, 24, 88, 24 ),
		'es_box_pct'        => es_box( 5, 0, 5, 0, '%' ),
		'es_size'           => es_size( 19 ),
		'es_size_em'        => es_size( 1.65, 'em' ),
		'es_card_hover_css' => es_card_hover_css(),
		'es_products_css'   => es_products_css( 'selector .woocommerce-pagination{margin-top:32px;}' ),
		'es_eyebrow'        => es_eyebrow( 'etiqueta' ),
		'es_h1'             => es_h( 'titular', 'h1' ),
		'es_h2'             => es_h( 'titulo' ),
		'es_h3'             => es_h( 'subtitulo', 'h3' ),
		'es_p'              => es_p( 'texto de cuerpo' ),
		'es_btn_primary'    => es_btn( 'Comprar', '#', 'primary' ),
		'es_btn_dark'       => es_btn( 'Comprar', '#', 'dark' ),
		'es_btn_outline'    => es_btn( 'Comprar', '#', 'outline' ),
		'es_btn_outline_lt' => es_btn( 'Comprar', '#', 'outline-light' ),
		'es_photo'          => es_photo( 'foto' ),
		'es_card'           => es_card( 'foto', 'titulo', 'texto' ),
		'es_card_linked'    => es_card( 'foto', 'titulo', 'texto', '/destino' ),
		'es_iconbox'        => es_iconbox( 'fas fa-check', 'titulo', 'texto' ),
		'es_feature_card'   => es_feature_card( 'fas fa-check', 'titulo', 'texto' ),
		'es_cta_banner'     => es_cta_banner( 'foto', 'titulo', 'texto', 'Ir', '#' ),
		'es_row'            => es_row( array( es_p( 'a' ), es_p( 'b' ) ) ),
		'es_grid'           => es_grid( 3, array( es_p( 'a' ) ) ),
		'es_split'          => es_split( array( es_p( 'a' ), es_p( 'b' ) ) ),
		'es_split_rev'      => es_split( array( es_p( 'a' ) ), array( 'reverse' => true, 'gap' => 64 ) ),
		'es_section'        => es_section( array( es_p( 'a' ) ) ),
		'es_wide'           => es_wide( es_p( 'a' ), 58 ),
	);
}

/**
 * The sentinel override set.
 *
 * Named after the key rather than numbered, so adding a token does not
 * renumber — and therefore does not churn — every other line of the golden.
 * The trailing `_Z` is what keeps `accent` from matching inside
 * `ZTOK_accent_hover_Z`.
 *
 * A numeric token cannot take a text sentinel without breaking the arithmetic
 * that reads it, so numbers get an out-of-range NUMBER instead. There are no
 * numeric tokens today; Task 2 adds them, and this arrives before they do so
 * the first one is covered on the day it lands rather than a task later.
 */
function es_dump_sentinels() {
	$out = array();
	$n   = 0;
	foreach ( es_tokens() as $clave => $valor ) {
		if ( is_string( $valor ) ) {
			$out[ $clave ] = 'ZTOK_' . $clave . '_Z';
		} elseif ( is_int( $valor ) ) {
			$out[ $clave ] = 9973 + ( ++$n );
		} elseif ( is_float( $valor ) ) {
			$out[ $clave ] = 9973.0 + ( ++$n );
		} else {
			fwrite( STDERR, "ENTORNO: el token '$clave' no es texto ni numero; el volcado no sabe sustituirlo.\n" );
			exit( 2 );
		}
	}
	return $out;
}

function es_dump_section( $titulo, array $override ) {
	es_uid_reset( 'golden' );  /* es-builder.php:27 — ids must be reproducible or the diff is noise */
	es_tokens( $override );
	echo "===== $titulo =====\n";
	echo json_encode( es_dump_visuals(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ), "\n";
}

/* Section 1: what a build actually emits. This is the byte-identity contract. */
$defaults = es_tokens();
es_dump_section( 'VALORES POR DEFECTO', $defaults );

/* Section 2: the same page with every token replaced by a sentinel named after
   its key. Any hand-written literal that survived the extraction shows up here
   as the literal instead of the sentinel — which is the one thing section 1
   structurally cannot see, because there a token and a literal are the same
   bytes. */
es_dump_section( 'TOKENS SUSTITUIDOS POR CENTINELAS', es_dump_sentinels() );

es_tokens( $defaults );

echo "===== FIN DEL VOLCADO =====\n";
