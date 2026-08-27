<?php
/**
 * Regression harness for the brand contract's PR3e addition: an OPTIONAL per-brand corner-radius
 * scale, emitted only in that brand's own `[data-brand]` block.
 *
 * Run: php tests/test-gallery-brand-radius.php     (exit 0 = green)
 *
 * Why a real build, and why this lives in its OWN file rather than inside
 * `tests/test-framework-audit.php`. Every scenario in that file is a SYNTHETIC fixture tree in a
 * fresh temp directory, on purpose ("never the real repo — so a broken fixture can never mark real
 * skills"), because it tests framework-audit.php's own STATIC-TEXT parsing. What this file tests is
 * different in kind: whether `_build-gallery.php`'s own `[data-brand]` CSS-emission loop, running
 * for real against the real `$BRANDS` table, puts a brand's declared radius scale where it belongs
 * and nowhere else. There is no separately extractable pure function to unit-test in isolation —
 * `_build-gallery.php` is a top-level script that reads the manifest, measures contrast and writes
 * `index.html` the moment it is loaded (see `fail()`'s `exit(1)`, same reason
 * `tests/test-framework-audit.php` subprocesses `framework-audit.php` instead of including it) — so
 * the only faithful way to prove the real emission is correct is to run the real build and read its
 * real, gitignored `index.html`. PR3d already established real-build inspection as the source of
 * truth for CSS-level correctness no static gate covers; this formalises that same inspection into
 * `ok()` assertions instead of leaving it as prose in an apply-progress artifact.
 *
 * What is under test, at minimum (the PR3e brief's own words):
 *   1. A brand that declares no `radius` renders the house values — proven by `aranda` (a survivor
 *      demo brand that has never set `radius`) carrying NO `--radius-` declaration at all in its
 *      own `[data-brand="aranda"]` block, and by `:root` still carrying exactly the five original
 *      house values, byte for byte.
 *   2. A brand that declares one gets its own — proven by `delao`'s `[data-brand="delao"]` block
 *      carrying all six `--radius-*` tokens (the five house tokens plus `pill`) at `0`.
 *   3. The override is SCOPED, not global — proven by the same `aranda` assertion in (1): if the
 *      mechanism leaked into `:root` or into every brand indiscriminately, aranda would show
 *      delao's radius values too, and it does not.
 *   4. The five previously hard-coded `border-radius:999px` sites named in the PR3e brief
 *      (`.cart b`, `.cats a`, `.fbtn`, `.backlink`, `.tax`) now read `var(--radius-pill,999px)`,
 *      so a brand's radius override can actually reach them — proven directly against the PHP
 *      SOURCE (no build needed for this one). The count of genuinely untouched
 *      `border-radius:999px` sites is asserted too, so that number is a tracked regression rather
 *      than an implicit assumption: `.plan-tag`, `.tablab`, `.opt`, `.sbtn`, `.tsw`,
 *      `.meta-tgl .tgl` and `.handoff-copy` are the gallery TOOL's own chrome (plan pricing tag,
 *      style-switch tabs, finish-option chips, anchor-switch buttons, toggle labels, the handoff
 *      summary button) — never part of a rendered brand sample — and are out of this PR's scope;
 *      reaching them is a separate, reported finding, not silently fixed here.
 */

$root_dir = dirname( __DIR__ );
$gallery  = $root_dir . '/skills/html-mockup/assets/gallery/_build-gallery.php';
$out_html = $root_dir . '/skills/html-mockup/assets/gallery/index.html';

$pass = 0;
$fail = 0;
function ok( $cond, $label, $actual = null ) {
	global $pass, $fail;
	if ( $cond ) {
		$pass++;
		echo "  OK   $label\n";
		return;
	}
	$fail++;
	echo "  FAIL $label\n";
	if ( null !== $actual ) {
		echo '       actual: ' . ( is_string( $actual ) ? substr( $actual, 0, 400 ) : var_export( $actual, true ) ) . "\n";
	}
}

/** Every declaration block a `[data-brand="X"]` selector opens, or null if it opens none. */
function brand_block( $src, $brand ) {
	$re = '/\[data-brand\s*=\s*"' . preg_quote( $brand, '/' ) . '"\]\s*\{([^{}]*)\}/';
	return preg_match( $re, $src, $m ) ? $m[1] : null;
}

/**
 * The FIRST `:root{...}` block — the house document default.
 *
 * NOT a `[^{}]*` brace-count regex: this specific rule's body is a several-hundred-line CSS
 * declaration list interleaved with prose comments (the container-width and body-size rationale),
 * and at least one of those comments contains a literal `{` — the exact trap
 * `gallery_anchor_block()`'s own docblock in framework-audit.php names from the other side ("a
 * verifier cannot tell CSS from a sentence about CSS"). A non-nested-brace regex simply fails to
 * match at all here (0 matches, confirmed against the real build). Line-anchored instead: this
 * file's own generator always closes a top-level rule with a bare `}` starting its own line
 * (confirmed against the real build too), so the boundary is the first such line after `:root{`,
 * never the first stray brace inside a comment.
 */
function root_block( $src ) {
	$start = strpos( $src, ':root{' );
	if ( false === $start ) {
		return null;
	}
	if ( ! preg_match( '/\n\}/', $src, $m, PREG_OFFSET_CAPTURE, $start ) ) {
		return null;
	}
	$close = $m[0][1];
	return substr( $src, $start + 6, $close - ( $start + 6 ) );
}

// ── (4) source-level: the five named hard-coded pill sites are now token-based ──────────────────
if ( ! is_file( $gallery ) ) {
	fwrite( STDERR, "ENTORNO: $gallery no existe\n" );
	exit( 2 );
}
$src = file_get_contents( $gallery );

$named_selectors = array(
	'.cart b'     => '/\.cart b\{[^}]*border-radius:var\(--radius-pill,999px\)/',
	'.cats a'     => '/\.cats a\{[^}]*border-radius:var\(--radius-pill,999px\)/',
	'.fbtn'       => '/\.fbtn\{[^}]*border-radius:var\(--radius-pill,999px\)/s',
	'.backlink'   => '/\.backlink\{[^}]*border-radius:var\(--radius-pill,999px\)/s',
	'.tax'        => '/\.tax\{[^}]*border-radius:var\(--radius-pill,999px\)/s',
);
foreach ( $named_selectors as $sel => $re ) {
	ok( 1 === preg_match( $re, $src ), "$sel now reads var(--radius-pill,999px), not a bare 999px" );
}

$still_bare = preg_match_all( '/border-radius:999px/', $src );
ok(
	7 === $still_bare,
	'exactly 7 bare border-radius:999px sites remain — the gallery tool\'s own chrome' .
		' (.plan-tag/.tablab/.opt/.sbtn/.tsw/.meta-tgl .tgl/.handoff-copy), never a rendered brand' .
		' sample, tracked here so a future fix (or an accidental new one) is not silent',
	$still_bare
);

// ── (1)+(2)+(3) real build: run it, then read what it actually rendered ─────────────────────────
$php_binary = ( '' !== PHP_BINARY ) ? PHP_BINARY : null;
if ( null === $php_binary || ! function_exists( 'exec' ) ) {
	fwrite( STDERR, "ENTORNO: sin PHP_BINARY/exec() no se puede lanzar el build real\n" );
	exit( 2 );
}
$cmd = implode( ' ', array( escapeshellarg( $php_binary ), escapeshellarg( $gallery ), '2>&1' ) );
exec( $cmd, $build_out, $build_code );
ok( 0 === $build_code, 'the real gallery builder exits 0 after the PR3e radius contract change', implode( "\n", $build_out ) );

if ( 0 !== $build_code ) {
	echo "\n$pass passed, $fail failed\n";
	exit( $fail > 0 ? 1 : 0 );
}

if ( ! is_file( $out_html ) ) {
	fwrite( STDERR, "ENTORNO: el build reportó éxito pero $out_html no existe\n" );
	exit( 2 );
}
$html = file_get_contents( $out_html );

$root = root_block( $html );
ok( null !== $root, 'the built index.html carries a :root block' );
ok(
	null !== $root && false !== strpos( $root, '--radius-card:12px' )
		&& false !== strpos( $root, '--radius-button:8px' )
		&& false !== strpos( $root, '--radius-image:8px' )
		&& false !== strpos( $root, '--radius-input:8px' )
		&& false !== strpos( $root, '--radius-container:16px' ),
	':root still carries the five original house radius values, byte for byte — PR3e touches no :root declaration',
	$root
);
ok(
	null !== $root && false === strpos( $root, '--radius-pill' ),
	':root does NOT declare --radius-pill — the pill token stays reachable only through a brand override or its own 999px fallback, never a new :root default',
	$root
);

$aranda = brand_block( $html, 'aranda' );
ok( null !== $aranda, 'the build renders a [data-brand="aranda"] block (aranda is a surviving demo brand)' );
ok(
	null !== $aranda && false === strpos( $aranda, '--radius' ),
	'aranda names no radius key, so its own [data-brand="aranda"] block carries NO --radius- declaration at all — the house :root value silently wins, and delao\'s override does not leak here',
	$aranda
);

$delao = brand_block( $html, 'delao' );
ok( null !== $delao, 'the build renders a [data-brand="delao"] block' );
foreach ( array( 'card', 'button', 'image', 'input', 'container', 'pill' ) as $tok ) {
	ok(
		null !== $delao && false !== strpos( $delao, "--radius-$tok:0;" ),
		"delao's own [data-brand=\"delao\"] block declares --radius-$tok:0 (Inicio.dc.html: \"Todo son ángulos rectos\")",
		$delao
	);
}

echo "\n$pass passed, $fail failed\n";
exit( $fail > 0 ? 1 : 0 );
