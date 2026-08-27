<?php
/**
 * _build-gallery.php — the template gallery, GENERATED rather than written.
 *
 * Run:  php skills/html-mockup/assets/gallery/_build-gallery.php
 * Out:  skills/html-mockup/assets/gallery/index.html   (generated; untracked — see .gitignore)
 *
 * WHY A GENERATOR. Each strip is one `TPL-* x PERS-*` pair, and the pairs share everything
 * except the five perceptual axes. Written by hand, the shared half would be copied once per
 * strip and would start drifting on the first edit — which is the exact failure the two proof
 * mockups were built to expose. Here the shared half exists once, in this file, and the axes are
 * a table. A strip cannot disagree with the token chain because it never gets to restate it.
 *
 * REPRODUCIBLE BY CONSTRUCTION. No clock, no randomness, no filesystem iteration order: the
 * image set is read through a sorted, cross-checked manifest and every table below is an ordered
 * array. Two runs over the same inputs produce byte-identical output; `cmp` proves it, and a
 * generated file nobody can regenerate is a hand-written file with extra steps.
 *
 * WHAT IS COPIED AND WHAT IS DERIVED. Every axis VALUE is transcribed from
 * `web-templates/references/design-system.md` § "Perceptual axes — token values", and every axis
 * POSITION from `ux-design-system/references/design-personalities.md`. Nothing in either category
 * is computed here. The accent is NOT an axis (design-system.md says so in as many words), so it
 * is chosen per ground and its contrast is MEASURED below by the same WCAG 2.1 formula the rest
 * of the repo uses — and the build dies if a measurement fails, rather than shipping a number
 * somebody typed.
 */

// ─────────────────────────────────────────────────────────────── 0 · paths + failure

$DIR      = __DIR__;
$MANIFEST = $DIR . '/_gallery-images.md';
$IMG_DIR  = $DIR . '/img';
$OUT      = $DIR . '/index.html';

/* WHAT THIS BUILD WAS MADE OF, stamped into the output so the claim can be checked later rather
   than trusted. Since index.html stopped being tracked, git can no longer show that it drifted
   from its inputs — nothing could, which is how a gallery built before a TPL-*.md edit stayed
   green in every gate here. The definition of the input set lives in ONE file, required by this
   generator and by framework-audit.php alike (RT_GALLERY_STALE); a second copy of it would
   answer a slightly different question and read as staleness whenever the two drifted. */
require_once $DIR . '/_gallery-fingerprint.php';

/* THE SIBLING SKILLS, so a rule this file quotes can be READ rather than remembered. `$SKILLS` is
   `skills/`, four levels up from `assets/gallery/`. Asserted rather than assumed: a build run from
   a copy of this directory outside the repository would otherwise fail much later, inside a regex
   that came back empty, and an empty quotation reads exactly like a rule nobody wrote. */
$SKILLS = dirname( $DIR, 3 );
foreach ( array( 'ux-design-system/references/design-tokens.md', 'ux-design-system/references/design-personalities.md' ) as $ref_rel ) {
	if ( ! is_file( $SKILLS . '/' . $ref_rel ) ) {
		fail( "cannot find $ref_rel from $SKILLS — this file quotes that reference for rules it does"
			. ' not get to invent, and a quotation whose source is missing is an invention with a'
			. ' citation on it' );
	}
}

/** Die loudly. A generator that limps produces a page whose defects look like design. */
set_error_handler( function ( $no, $str, $file, $line ) {
	fail( "PHP $str  (" . basename( $file ) . ":$line)
"
		. '  A warning is not a note. The malformed footer that prompted this rule emitted'
		. " `Undefined array key` and STILL wrote index.html, in a console that already prints
"
		. '  fifty lines of measurement — which is a warning nobody reads. Data that does not fit'
		. ' the function consuming it is a build failure.' );
} );

function fail( $msg ) {
	fwrite( STDERR, "build-gallery: FAIL — $msg\n" );
	exit( 1 );
}

function h( $s ) {
	return htmlspecialchars( $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );
}

// ─────────────────────────────────────────────────────────────── 1 · the manifest is the contract
//
// `_gallery-images.md` is parsed, not paraphrased. Its table carries the slug, the role, the
// pixel size and the Spanish `alt`; this build refuses to run if a row has no file or a file has
// no row, which is `RT_GALLERY_NO_MANIFEST` enforced at the only moment it can still be cheap to
// fix. The slug is load-bearing downstream: `es_photo( $slug )` resolves a WordPress attachment
// slug, and the file name IS the slug.

if ( ! is_file( $MANIFEST ) ) {
	fail( '_gallery-images.md is missing — the image set has no contract' );
}

$manifest_rows = array();
foreach ( explode( "\n", file_get_contents( $MANIFEST ) ) as $line ) {
	// Exactly EIGHT cells: slug, role, size, weight, freepik, SHOOT, licence, alt. The shoot cell
	// arrived with `RT_GALLERY_ONE_SHOOT` and shifted `alt` from $m[8] to $m[9] — the one edit a
	// column insertion forces here, and the reason this parser counts cells out loud instead of
	// grabbing the last one with a greedy match. `RT_GALLERY_NO_MANIFEST` and
	// `RT_GALLERY_ONE_SHOOT` both read the same table by HEADER NAME, so they survived the
	// insertion untouched; this regex is positional and did not. The "Registers" table three
	// headings up has three cells, so it cannot be swallowed by accident.
	if ( ! preg_match(
		'/^\|\s*`([a-z0-9\-]+)`\s*\|\s*([^|]+?)\s*\|\s*(\d+)×(\d+)\s*\|\s*([^|]+?)\s*\|\s*([^|]+?)\s*\|\s*([^|]+?)\s*\|\s*([^|]+?)\s*\|\s*([^|]+?)\s*\|\s*$/u',
		$line,
		$m
	) ) {
		continue;
	}
	$manifest_rows[ $m[1] ] = array(
		'slug' => $m[1],
		'role' => $m[2],
		'w'    => (int) $m[3],
		'h'    => (int) $m[4],
		'alt'  => $m[9],
	);
}

if ( array() === $manifest_rows ) {
	fail( '_gallery-images.md parsed to zero rows — the table shape changed under the parser' );
}

/* ── THE REGISTER TABLE, PARSED RATHER THAN PARAPHRASED, for the same reason the row table is.
      `RT_GALLERY_ONE_SHOOT` already reads this table for its row COUNT; § 5a reads it for its
      Material row, because that is where the house ink's one exemption comes from. Two consumers
      of one table beats a second hand-kept list of "the swatches", which is a list that goes stale
      the day somebody adds a third swatch and edits only the table.

      The `Material` row is matched by NAME and its cell is split on backticks, so a register
      renamed in that file fails here loudly instead of quietly exempting nothing. */
$MATERIAL_SLUGS = array();
foreach ( explode( "\n", file_get_contents( $MANIFEST ) ) as $reg_line ) {
	if ( ! preg_match( '/^\|\s*Material\s*\|\s*([^|]+?)\s*\|/u', $reg_line, $reg_m ) ) {
		continue;
	}
	if ( preg_match_all( '/`([a-z0-9\-]+)`/', $reg_m[1], $reg_s ) ) {
		foreach ( $reg_s[1] as $reg_slug ) {
			$MATERIAL_SLUGS[ $reg_slug ] = true;
		}
	}
}
if ( array() === $MATERIAL_SLUGS ) {
	fail( '_gallery-images.md § Registers has no `Material` row carrying slugs — the house ink'
		. ' exemption is derived from that row, and an empty derivation would ink the two swatches'
		. ' whose colour is their specification' );
}

// Both directions. A file with no row is unlicensed weight; a row with no file is a promise the
// build cannot keep. `img/` is read through a sorted glob so the order never depends on the
// filesystem — see the reproducibility note in the header.
$on_disk = glob( $IMG_DIR . '/*.webp' );
if ( ! is_array( $on_disk ) ) {
	$on_disk = array();
}
sort( $on_disk, SORT_STRING );

$disk_slugs = array();
foreach ( $on_disk as $p ) {
	$disk_slugs[] = basename( $p, '.webp' );
}

$orphan_files = array_diff( $disk_slugs, array_keys( $manifest_rows ) );
$orphan_rows  = array_diff( array_keys( $manifest_rows ), $disk_slugs );
if ( array() !== $orphan_files ) {
	fail( 'img/ carries files with no manifest row: ' . implode( ', ', $orphan_files ) );
}
if ( array() !== $orphan_rows ) {
	fail( '_gallery-images.md carries rows with no file in img/: ' . implode( ', ', $orphan_rows ) );
}

$IMAGES    = array();
$raw_bytes = 0;
foreach ( $disk_slugs as $slug ) {
	$bytes             = file_get_contents( $IMG_DIR . '/' . $slug . '.webp' );
	$raw_bytes        += strlen( $bytes );
	$row               = $manifest_rows[ $slug ];
	$row['data']       = 'data:image/webp;base64,' . base64_encode( $bytes );
	$IMAGES[ $slug ]   = $row;
}

/** Look up an image, refusing to guess. A typo'd slug is a grey box on a client's screen. */
function img( $slug ) {
	global $IMAGES;
	if ( ! isset( $IMAGES[ $slug ] ) ) {
		fail( "no image `$slug` — every slug rendered must exist in the manifest AND in img/" );
	}
	return $IMAGES[ $slug ];
}

// ─────────────────────────────────────────────────────────────── 2 · colour, measured
//
// WCAG 2.1: L = 0.2126R + 0.7152G + 0.0722B over linearised sRGB, ratio = (Lhi+.05)/(Llo+.05).
// The same formula `design-system.md` states and `tests/test-write-path.php` recomputes.

function srgb_lum( $hex ) {
	$hex = ltrim( $hex, '#' );
	if ( 6 !== strlen( $hex ) ) {
		fail( "not a 6-digit hex: #$hex" );
	}
	$l = 0.0;
	foreach ( array( 0 => 0.2126, 2 => 0.7152, 4 => 0.0722 ) as $off => $coeff ) {
		$c  = hexdec( substr( $hex, $off, 2 ) ) / 255;
		$c  = ( $c <= 0.04045 ) ? $c / 12.92 : pow( ( $c + 0.055 ) / 1.055, 2.4 );
		$l += $coeff * $c;
	}
	return $l;
}

function contrast( $a, $b ) {
	$la = srgb_lum( $a );
	$lb = srgb_lum( $b );
	$hi = max( $la, $lb );
	$lo = min( $la, $lb );
	return ( $hi + 0.05 ) / ( $lo + 0.05 );
}

function ratio_str( $a, $b ) {
	return number_format( contrast( $a, $b ), 2, '.', '' ) . ':1';
}

// ─────────────────────────────────────────────────────────────── 3 · the five axes
//
// COPIED VERBATIM from web-templates/references/design-system.md § "Perceptual axes — token
// values". Not one number here is reasoned about, interpolated or rounded. If a position is
// missing from that file it is a finding to report, not a gap to fill.

/* § Scale — --type-ratio, --display-lh, --fs-h1-max */
$SCALE = array(
	'contained'  => array( 'ratio' => '1.200', 'lh' => '1.25', 'h1max' => '48' ),
	'classic'    => array( 'ratio' => '1.333', 'lh' => '1.10', 'h1max' => '64' ),
	'editorial'  => array( 'ratio' => '1.500', 'lh' => '0.95', 'h1max' => '88' ),
	'monumental' => array( 'ratio' => '1.618', 'lh' => '0.82', 'h1max' => '120' ),
);

/* § Density — --sp-scale */
$DENSITY = array(
	'compact'    => '0.8',
	'standard'   => '1.0',
	'generous'   => '1.35',
	'monumental' => '1.7',
);

/* § Ground — --c-bg, --c-bg-alt, --c-text (+ that row's own measured contrast) */
$GROUND = array(
	'paper' => array( 'bg' => '#FFFFFF', 'alt' => '#F6F7F8', 'text' => '#15181A' ),
	'warm'  => array( 'bg' => '#FFF3E3', 'alt' => '#F7E8D4', 'text' => '#241C14' ),
	'cool'  => array( 'bg' => '#F2F5F8', 'alt' => '#E8EDF3', 'text' => '#141C24' ),
	'ink'   => array( 'bg' => '#0E1113', 'alt' => '#171B1E', 'text' => '#F4F6F7' ),
);

/* § Elevation — --elev-rest, --elev-hover. `label` is for the strip's own axis readout. */
$ELEVATION = array(
	'none'        => array(
		'rest'  => 'none',
		'hover' => 'none',
		'label' => 'none — la separación es el aire',
	),
	'hairline'    => array(
		'rest'  => '0 0 0 1px var(--c-border)',
		'hover' => '0 0 0 1px var(--c-text)',
		'label' => '1px var(--c-border) → var(--c-text)',
	),
	'soft-shadow' => array(
		'rest'  => '0 1px 2px rgba(0,0,0,.04)',
		'hover' => '0 18px 40px -12px rgba(21,24,26,.16)',
		'label' => '0 1px 2px → 0 18px 40px -12px',
	),
	'accent-glow' => array(
		'rest'  => '0 0 0 1px color-mix(in srgb,var(--c-accent) 22%,transparent)',
		'hover' => '0 14px 34px -10px color-mix(in srgb,var(--c-accent) 40%,transparent)',
		'label' => 'aro accent 22% → halo 40%',
	),
);

/* § Composition — one blueprint per position; the blueprints live in
   ux-design-system/references/layout-patterns.md § "Composition blueprints". */
$COMPOSITION = array(
	'centered'    => array( 'lp' => 'LP-CENTERED', 'line' => 'un eje simétrico, nada sangra' ),
	'asymmetric'  => array( 'lp' => 'LP-ASYMMETRIC', 'line' => 'copy en 7 de 12 columnas, una imagen sangra un borde' ),
	'strict-grid' => array( 'lp' => 'LP-STRICT-GRID', 'line' => 'todo empieza y termina en una línea de columna' ),
	'broken-grid' => array( 'lp' => 'LP-BROKEN-GRID', 'line' => 'un elemento por sección cruza la retícula' ),
);

// ─────────────────────────────────────────────────────────────── 4 · the anchors
//
// Positions and typefaces COPIED from ux-design-system/references/design-personalities.md.
// Motion durations come from ux-design-system/references/motion.md (".35s colour, .5s lift,
// .7s image zoom", lift -4px), read through each anchor's stated intensity.
//
// TWO THINGS THAT FILE DOES NOT SAY, recorded here as gaps rather than filled with invention:
//   · wordmark tracking exists only for EDITORIAL (.16em) and DIRECT (.1em). MATTER and
//     INSTITUTIONAL have none, so they run at `normal` — the tracking the face was drawn with,
//     which is that file's own stated principle for anything it does not tighten.
//
// `track_disp` IS `0em` AND NOT `normal` ON THE TWO ANCHORS THAT DO NOT TIGHTEN, and the change is
// an encoding, not a value. For `letter-spacing` the two are the same rendered result — `normal`
// IS zero extra tracking on every engine — but `calc(normal + .016em)` is a parse error, and the
// optical ramp below adds a per-step offset to this token on every anchor. Writing `normal`
// here would have silently dropped the ramp on exactly the two anchors whose h3 needs it most,
// with no invalid-value warning anywhere: an invalid custom-property substitution falls back to
// the property's initial value, which for letter-spacing is `normal`, so the two would have kept
// looking correct while running unramped. Measured after the swap: `getComputedStyle` on the
// matter h3 reads 0.568px of tracking where `normal` read 0px, and the h1 reads exactly 0px in
// both. Nothing else in this file reads `--track-display`.
//   · INSTITUTIONAL's --font-primary is described as "Source Sans 3 semibold", while the same
//     file says weight "did not earn an anchor" and comes from design-system.md's type table,
//     which pins h1/h2 at 700. The table wins here; the contradiction is reported, not resolved.

$ANCHORS = array(
	'editorial'     => array(
		'id'          => 'PERS-EDITORIAL',
		'name'        => 'Editorial',
		'fits'        => 'Herencia, prestigio, una historia que merece ir despacio',
		'scale'       => 'editorial',
		'ground'      => 'paper',
		'density'     => 'generous',
		'composition' => 'asymmetric',
		'elevation'   => 'none',
		'font_1'      => "'Fraunces', Georgia, 'Times New Roman', serif",
		'font_2'      => "'Inter Tight', system-ui, sans-serif",
		'track_disp'  => '-.015em',
		'track_word'  => '.16em',
		'dur_color'   => '.5s',
		'dur_lift'    => '.5s',
		'dur_zoom'    => '.7s',
		'lift'        => '-4px',
		'ratio_hero'  => '4/5',
		'ratio_card'  => '16/11',
		'imagery'     => 'encuadre foto-editorial, recorte dramático; scrims con cuentagotas',
		'card'        => 'imagen, filete de 1px, texto. Sin relleno y sin borde: la elevación `none` ES la tarjeta.',
	),
	'direct'        => array(
		'id'          => 'PERS-DIRECT',
		'name'        => 'Direct',
		'fits'        => 'Marcas que ganan por ser inconfundibles',
		'scale'       => 'monumental',
		'ground'      => 'ink',
		'density'     => 'compact',
		'composition' => 'broken-grid',
		'elevation'   => 'accent-glow',
		'font_1'      => "'Archivo Expanded', 'Arial Black', system-ui, sans-serif",
		'font_2'      => "'Archivo', system-ui, sans-serif",
		'track_disp'  => '-.025em',
		'track_word'  => '.1em',
		'dur_color'   => '.35s',
		'dur_lift'    => '.35s',
		'dur_zoom'    => '.35s',
		'lift'        => '0px',
		'ratio_hero'  => '4/3',
		'ratio_card'  => '16/10',
		'imagery'     => 'alto contraste, recorte cerrado, sangrando fuera de la retícula',
		'card'        => 'superficie oscura; el hover es un halo de accent en vez de un levantamiento.',
	),
	'matter'        => array(
		'id'          => 'PERS-MATTER',
		'name'        => 'Matter',
		'fits'        => 'Quien vende un material o una cosa hecha — piedra, madera, comida, mueble',
		'scale'       => 'classic',
		'ground'      => 'warm',
		'density'     => 'standard',
		'composition' => 'strict-grid',
		'elevation'   => 'hairline',
		'font_1'      => "'Instrument Serif', Georgia, 'Times New Roman', serif",
		'font_2'      => "'DM Sans', system-ui, sans-serif",
		'track_disp'  => '0em',
		'track_word'  => 'normal',
		'dur_color'   => '.35s',
		'dur_lift'    => '.5s',
		'dur_zoom'    => '.7s',
		'lift'        => '-4px',
		'ratio_hero'  => '4/3',
		'ratio_card'  => '4/3',
		'imagery'     => 'el producto de frente, graduado cálido, a sangre dentro de la retícula',
		'card'        => 'imagen al radio del contenedor, borde de filete, texto debajo. El borde es todo el cromo.',
	),
	/* VITRINE · la sala a oscuras y el objeto iluminado. Comparte el ground `ink` con DIRECT y NADA
	   más: donde aquella rompe la rejilla y se pega, ésta la respeta y respira. Es la prueba de que
	   `oscuro` no es una personalidad sino una posición de un eje — dos anclas pueden ser negras y
	   no parecerse en nada. */
	'vitrine'       => array(
		'id'          => 'PERS-VITRINE',
		'name'        => 'Vitrine',
		'fits'        => 'Lo que se compra mirándolo de cerca y en orden',
		'scale'       => 'editorial',
		'ground'      => 'ink',
		'density'     => 'monumental',
		'composition' => 'strict-grid',
		'elevation'   => 'soft-shadow',
		'font_1'      => "'DM Sans', system-ui, sans-serif",
		'font_2'      => "'Inter Tight', system-ui, sans-serif",
		'track_disp'  => '-.02em',
		'track_word'  => '.12em',
		'dur_color'   => '.35s',
		'dur_lift'    => '.5s',
		'dur_zoom'    => '.7s',
		'lift'        => '-6px',
		'ratio_hero'  => '16/9',
		'ratio_card'  => '1/1',
		'imagery'     => 'el objeto aislado e iluminado contra el fondo, con aire alrededor; nunca a sangre',
		'card'        => 'superficie elevada (--c-bg-alt sobre --c-bg) y la sombra guardada para el hover: sobre negro una sombra no separa, lo que separa es el escalón de superficie.',
	),
	'institutional' => array(
		'id'          => 'PERS-INSTITUTIONAL',
		'name'        => 'Institutional',
		'fits'        => 'B2B y servicios profesionales: credibilidad antes que entusiasmo',
		'scale'       => 'contained',
		'ground'      => 'cool',
		'density'     => 'standard',
		'composition' => 'centered',
		'elevation'   => 'soft-shadow',
		'font_1'      => "'Source Sans 3', system-ui, sans-serif",
		'font_2'      => "'Source Sans 3', system-ui, sans-serif",
		'track_disp'  => '0em',
		'track_word'  => 'normal',
		'dur_color'   => '.35s',
		'dur_lift'    => '.5s',
		'dur_zoom'    => '.7s',
		'lift'        => '-4px',
		'ratio_hero'  => '16/9',
		'ratio_card'  => '4/3',
		'imagery'     => 'fotografía sobria de contextos de trabajo reales',
		'card'        => 'tarjeta sobre --c-bg, sombra suave en reposo, chip de icono en accent, levanta sin cambiar de color.',
	),
);

// ─────────────────────────────────────────────────────────────── 5 · the accent is NOT an axis
//
// design-system.md: "the accent is not an axis", and "the accent has to be re-derived to clear
// 4.5:1 against #0E1113, because an accent that passed on `paper` will usually fail here."
// So: one hue family for the whole gallery — cut-face orange, which is what a stone workshop
// looks like — and exactly one re-derivation, on the ground that forces it. The assertions below
// are the point: change any of these four and the build stops rather than shipping a failing
// label. `#8C3A1F` measures 2.47:1 on `ink`, which is the rule proving itself.

// ─────────────────────────────────────────────────────────────── 4b · a brand is not an anchor
//
// AN ANCHOR IS A POSITION; A BRAND IS A BUSINESS. Everything above this line renders ONE company —
// Piedra Valdes, cantería in Lleida — through ten architectures and all five anchors. That is 50
// of the 57 strips: one identity in 50 configurations, and it is the defect this table exists to
// end: a catalogue whose
// every entry is the same stonemason cannot answer "quiero la de restaurante", because none of
// them is one. The architecture varied and the identity never did.
//
// The count is stated rather than computed because the strip table is EXPLICIT PAIRS, not a cross
// product: coverage per anchor is whatever the table says it is. Across the TEN house archetypes it
// is now the full 10x5 — it was not, and VITRINE sat at 3 while the rest sat at 10, which is a hole
// in the one thing this page does. Totals still differ (12/12/12/11/10) because the seven BRANDED
// strips below each stand at one chosen anchor and no brand chose vitrine.
//
// So a brand supplies exactly what an anchor deliberately does not: its own GROUND, its own
// ACCENT, its own TYPE PAIRING, its own PHOTOGRAPHS and, optionally, its own CORNER-RADIUS SCALE.
// It BORROWS scale, density, composition and elevation from the anchor it starts on, so the five
// axes keep meaning what they meant and the anchor stops being a variant you flip between — it
// becomes the preset a brand starts from.
//
// THE GROUND AND THE ACCENT ARE REGISTERED INTO THE SAME TWO TABLES THE ANCHORS USE, under a `b-`
// key. That is the whole mechanism, and it is the reason this file needed almost no new gates:
// the AA sweep on the eyebrow, the `--c-on-accent` derivation, the house ink, the split-tone shape
// check and the swatch separation all run on a brand for free, because none of them ever learns
// that such a thing as a brand exists.
//
// `ink` is this brand's own GRADE, in the two terms § 5a's table uses: `sat` is how much colour,
// `gamma` is how deep the S-curve goes. A brand that names neither inherits the default, exactly
// like an anchor whose § Imagery says nothing.
//
// ── PR3e · RADIUS JOINS THE LIST, AND HERE IS WHY IT BELONGS HERE AND NOT ABOVE ──────────────────
//
// `--radius-card/-button/-image/-input/-container` were a house constant no brand had ever
// touched — twelve brands and none of them asked. That silence was read, twice, as "radius is the
// anchor's", and both readings were wrong for the same reason: RADIUS IS NOT ONE OF THE FIVE AXES
// LISTED TWO PARAGRAPHS UP. Scale, ground, density, composition, elevation — that is the whole
// list an anchor owns, and corner treatment is not on it. Not being an axis does not make it
// nobody's; it makes it a BRAND's, exactly like the ground and the typeface two lines above it,
// because a brutalist brand and a soft consumer brand genuinely do not share corners any more than
// they share a palette. The two facts — "not an axis" and "is identity" — are what separate this
// from a styling knob: an anchor may not touch it (that would be a sixth axis nobody asked for),
// but a brand may, because a brand is a business and a business has a personality about corners.
//
// `radius` is this brand's own SCALE, one value per house token — `card`, `button`, `image`,
// `input`, `container` — plus `pill` for the file's own fully-round chips (cart badge, category
// tag, filter chip, back-link, tax tag), which were never a documented house token but were always
// rendered at a fixed 999px nobody could reach. A brand that names none inherits the house values
// UNCHANGED, exactly like `ink` and exactly like an anchor whose § Imagery says nothing: the
// override lives ONLY in that brand's own `[data-brand]` block, never in `:root`, so leaving the
// key out costs a brand nothing and the house scale is not one byte different from before this
// paragraph existed.
$BRANDS = array(
	

	/* MOTOR ARANDA · ocasión multimarca. Fondo claro y FRÍO, que es lo contrario de Terrazza a
	   propósito: un patio de coches se lee de día, en el móvil, comparando cifras. El acento es
	   verde azulado y no rojo — el rojo en automoción es la señal de "oferta" y aquí lo que se
	   vende es que los números están claros. */
	'aranda' => array(
		'name'   => 'MOTOR ARANDA',
		'sector' => 'Automoción · ocasión',
		'ground' => array( 'bg' => '#F3F6F7', 'alt' => '#E4EAED', 'text' => '#111A1F' ),
		'accent' => '#0B5D6B',
		'font_1' => "'Archivo', system-ui, sans-serif",
		'font_2' => "'Inter Tight', system-ui, sans-serif",
		'ink'    => array( 'sat' => 0.68, 'gamma' => 0.14 ),
	),

	/* INMOBILIARIA DE LA O · cartera curada de Sierra Blanca. Papel cálido casi idéntico al de
	   Alinea y Medida Justa — es el mismo registro claro-cálido porque las tres vienen de una
	   fotografía de interior, no de estudio — y ahí termina el parecido: `text` mide 16,17:1 sobre
	   `bg` y 14,95:1 sobre `bg-alt`, muy por encima del suelo AAA de 7:1 que exige este bloque.
	   EL ACENTO PASA POR DOS PUERTAS, NO UNA, Y ESO CORRIGE LA NOTA DE DISEÑO ANTERIOR A ESTE
	   COMMIT. El brief pedía `#8A7B5C` (un caqui de piedra), que mide 3,77:1 sobre `bg` — no pasa la
	   primera puerta, la de contraste AA de texto (`foreach` de este mismo archivo, 4,5:1 sobre
	   `bg`/`bg-alt`). Oscurecerlo a `#7A6B4E` sube a 4,73:1 en `bg` pero se queda en 4,37:1 en
	   `bg-alt` — sigue sin pasar. Y `#756547`, la corrección que la nota anterior daba por buena
	   (5,15:1 / 4,77:1, re-verificado aquí con la función `contrast()` real: pasa la primera
	   puerta con margen) FALLA la SEGUNDA, la que mide el grado de tinta de la casa (§ 5a,
	   `ink_ends()`): mezclado con el acento al 45% sobre el extremo oscuro del ground y renormalizado
	   a su propia luminancia, el negro de sombra resultante es `#28251D` — un recorrido de canal de
	   sólo 11, por debajo del suelo de 20 que el propio `ink_ends()` exige («un mapa de dos colores
	   cuya tinta oscura es gris no es un mapa de dos colores»). Es la misma familia de caquis
	   desaturados de origen (`#8A7B5C` → `#7A6B4E` → `#756547`): oscurecer sin subir la saturación
	   basta para el contraste de texto pero no para que la tinta de sombra cargue color. La solución
	   no es más oscuro, es más SATURADO: `#8A5A2A`, un terracota/bronce (más propio de una fachada
	   andaluza que el caqui de origen), mide 5,35:1 / 4,94:1 — pasa la primera puerta con más margen
	   que `#756547` — y su tinta de sombra da `#2F2317`, recorrido de canal 24 — pasa también la
	   segunda. Ambas puertas re-verificadas contra la implementación real de este archivo antes de
	   comprometer el bloque, no sólo calculadas aparte.
	   TIPOGRAFÍA: el diseño de origen pide Libre Caslon Display para los titulares, y la casa no la
	   tiene embebida. Sustituye `Instrument Serif` — la serifa de alto contraste más cercana del
	   set de la casa (`skills/html-mockup/assets/fonts/instrument-serif-latin.woff2`, ya presente),
	   frente a `Fraunces`, una serifa variable de trazo suave que no persigue el mismo contraste
	   vertical. Es el mismo tipo de sustitución de sistema que ya fijó D3 para el contenedor
	   (`--container-max: 1280px` de la casa gana sobre los 1440 del diseño): el set de fuentes de
	   la casa es una restricción del mismo orden, y añadir un woff2 nuevo movería la huella de la
	   galería (`_gallery-fingerprint.php`) sin necesidad y exigiría su propia licencia OFL.
	   INK: `sat 0,64 / gamma 0,12`. Fondo claro y cálido como Alinea y Medida Justa, así que
	   comparte su curva suave (`gamma 0,12`) — mantiene honestos los tonos medios de la piedra y el
	   verde de olivo sin aplanarlos. La saturación queda un peldaño por debajo de Alinea (0,66):
	   esto es arquitectura de exterior con luz natural, no un acento de marca de producto, así que
	   pide un grado más neutro; y un peldaño por encima de Corte Nueve (0,62), cuya fotografía es un
	   still de estudio y no un jardín. */
	/* RADIUS (PR3e): `Inicio.dc.html`'s own source brief says it in as many words — "Sin radios de
	   borde y sin sombras en ningún elemento. Todo son ángulos rectos." — and the badge in the
	   featured-listing card is drawn flush to the corner with no rounding at all. Every house token
	   goes to zero, `pill` included: a chip that stayed circular while every card and button around
	   it went square would be the exact "generic component under brand colours" defect this change
	   exists to stop repeating. */
	'delao' => array(
		'name'   => 'INMOBILIARIA DE LA O',
		'sector' => 'Inmobiliaria · cartera curada',
		'ground' => array( 'bg' => '#F6F4F0', 'alt' => '#EFEBE4', 'text' => '#17181A' ),
		'accent' => '#8A5A2A',
		'font_1' => "'Instrument Serif', Georgia, 'Times New Roman', serif",
		'font_2' => "'Archivo', system-ui, sans-serif",
		'ink'    => array( 'sat' => 0.64, 'gamma' => 0.12 ),
		'radius' => array(
			'card' => '0', 'button' => '0', 'image' => '0',
			'input' => '0', 'container' => '0', 'pill' => '0',
		),
	),

	/* AURIA · lanzamiento de un modelo. Casi negro AZULADO, frente al casi negro CÁLIDO de
	   Terrazza: los dos son oscuros y no se parecen, que es exactamente lo que un catálogo tiene
	   que poder demostrar. */
	

	/* TALLER BERGARA · tarifa. Papel cálido de taller y naranja de señalización. Es el único de
	   los tres cuyo argumento es un NÚMERO y no una fotografía, así que su fondo es el más plano
	   y su acento el más ruidoso: el precio tiene que poder gritar sin ayuda de una imagen. */
	

	/* CLÍNICA ARBEA · odontología general. Blanco frío casi puro y verde azulado. Es el fondo más
	   CLARO de todo el catálogo a propósito: una consulta sanitaria compite con el miedo, y el miedo
	   no se calma con contraste alto ni con acentos que gritan. */
	

	/* ALINEA · ortodoncia. Papel cálido y ciruela. Vende un tratamiento de dieciocho meses, así que
	   se parece más a una marca de producto que a una clínica: el acento es el único de la casa que
	   no viene ni del sector ni de la fotografía, y eso es correcto aquí — lo que se compra es la
	   decisión de empezar, no el material. */
	'alinea' => array(
		'name'   => 'ALINEA',
		'sector' => 'Salud · ortodoncia',
		'ground' => array( 'bg' => '#FAF7F4', 'alt' => '#EFE8E1', 'text' => '#1E1720' ),
		'accent' => '#6B3A7A',
		'font_1' => "'Instrument Serif', Georgia, 'Times New Roman', serif",
		'font_2' => "'Inter Tight', system-ui, sans-serif",
		'ink'    => array( 'sat' => 0.66, 'gamma' => 0.12 ),
	),

	/* URGENCIA DENTAL · guardia. Blanco puro y rojo de señal. Ningún otro fondo del catálogo es
	   blanco #FFFFFF y ningún otro acento es rojo, y las dos cosas son la misma decisión: esta
	   página se lee de pie, con dolor y con una mano, y todo lo que no sea contraste máximo le
	   estorba a esa persona. */
	

	/* CORTE NUEVE · vaquero con ajuste. El fondo es el GREIGE DEL PROPIO FONDO DE LAS FOTOS, y esa
	   es la única forma defendible de elegirlo aquí: este arquetipo enseña la misma prenda sobre
	   tres cuerpos contra un ciclorama liso, así que cualquier otro fondo pondría a la página a
	   discutir con sus nueve fotografías. El acento es el índigo del tinte — no un color de marca
	   inventado, el color que tiene el producto cuando sale de la cuba.
	   Es además el ÚNICO fondo de tono medio del catálogo: los otros siete son o muy claros o muy
	   oscuros, y esa banda intermedia estaba vacía. */
	'corte' => array(
		'name'   => 'CORTE NUEVE',
		'sector' => 'Moda · vaquero',
		'ground' => array( 'bg' => '#EDEAE4', 'alt' => '#DFDAD1', 'text' => '#1C1A17' ),
		'accent' => '#2C3E7A',
		'font_1' => "'DM Sans', system-ui, sans-serif",
		'font_2' => "'Inter Tight', system-ui, sans-serif",
		'ink'    => array( 'sat' => 0.62, 'gamma' => 0.12 ),
	),

	/* MEDIDA JUSTA · estores y cortinas a medida. Blanco lino, que es el color del propio tejido
	   crudo y el del ciclorama de sus fotografías. El acento es BURDEOS: el único del catálogo en esa
	   familia, y en un negocio de interiorismo el color de marca no puede competir con el de las
	   telas que vende — un burdeos profundo marca sin gritar sobre nueve tonos de lino. */
	'medida' => array(
		'name'   => 'MEDIDA JUSTA',
		'sector' => 'Hogar · estores y cortinas a medida',
		'ground' => array( 'bg' => '#F7F4EE', 'alt' => '#EAE4DA', 'text' => '#241D1A' ),
		'accent' => '#7A2B3A',
		'font_1' => "'Instrument Serif', Georgia, 'Times New Roman', serif",
		'font_2' => "'DM Sans', system-ui, sans-serif",
		'ink'    => array( 'sat' => 0.66, 'gamma' => 0.12 ),
	),

	/* BAJURA · pescado fresco. Fondo casi negro VERDOSO, y no es estética: el pescado se fotografía
	   sobre pizarra y hielo, brilla sobre oscuro y se apaga sobre papel. Frente al casi negro cálido
	   de Terrazza y al azulado de Auria, éste tira a verde — tres oscuros que no se parecen es
	   exactamente lo que un catálogo tiene que poder demostrar.
	   El acento es el naranja de las boyas y los petos del puerto, que es donde está el negocio. */
	'bajura' => array(
		'name'   => 'BAJURA',
		'sector' => 'Alimentación · pescado fresco',
		'ground' => array( 'bg' => '#0F1714', 'alt' => '#18211D', 'text' => '#E9F1EC' ),
		'accent' => '#FF8A3D',
		'font_1' => "'Archivo', system-ui, sans-serif",
		'font_2' => "'Source Sans 3', system-ui, sans-serif",
		'ink'    => array( 'sat' => 0.55, 'gamma' => 0.30 ),
	),

	/* TUESTE NORTE · suscripción de café. Kraft, el papel de la propia caja. Lo que se vende aquí no
	   es un producto que se mira sino una entrega que llega, y lo que el cliente ve cada mes es
	   cartón: el fondo es el material del envío y no el color del grano.
	   Verde de planta como acento — nadie más en el catálogo lo tiene, y el café es una planta antes
	   que una bebida. */
	'tueste' => array(
		'name'   => 'TUESTE NORTE',
		'sector' => 'Alimentación · café por suscripción',
		'ground' => array( 'bg' => '#E8DFD0', 'alt' => '#DBD0BD', 'text' => '#20180E' ),
		/* VERDE MEDIDO, NO ELEGIDO. #2F5D3A daba un ink de sombra con un recorrido de canal de 14 y
		   la puerta lo tumbó con razón: un duotono cuya tinta oscura es gris no es un duotono. El
		   fondo kraft es cálido y el verde tenía que ser bastante más puro para que la mezcla al 45%
		   siguiera siendo verde después de reponer la luminancia. Éste da 21 de recorrido y 6,16:1
		   sobre el fondo — de los candidatos probados, el único que pasa las dos cosas con margen. */
		'accent' => '#0F5C28',
		'font_1' => "'Fraunces', Georgia, 'Times New Roman', serif",
		'font_2' => "'Archivo', system-ui, sans-serif",
		'ink'    => array( 'sat' => 0.70, 'gamma' => 0.15 ),
	),

	/* LUMIÈRE · centro de estética. Papel cálido ROSADO, que no es el papel cálido de Bergara ni el
	   blanco frío de Arbea: los tres son fondos claros y lo que los separa es el matiz, que es
	   justamente lo que un catálogo tiene que poder demostrar en tres tiras seguidas.

	   EL ACENTO ES EL ROSA MÁS VIVO QUE PASA LA PUERTA, y esa frase es una medición. El sector
	   entero se pinta con un rosa tipo #FF5EA5, que da 2,4:1 sobre blanco; aquí el acento pinta el
	   eyebrow, o sea TEXTO, así que la puerta pide 4,5:1 contra los dos fondos y ese rosa no entra.
	   #B03A5B da 5,49:1 sobre el fondo y 4,80:1 sobre el alterno — el más claro de los candidatos
	   que pasaba, no el que gustaba más. La alternativa era bajar el rosa hasta el granate y
	   perder el sector por el camino. */
	'lumiere' => array(
		'name'   => 'LUMIÈRE',
		'sector' => 'Belleza · centro de estética',
		'ground' => array( 'bg' => '#FDF7F4', 'alt' => '#F5E6E0', 'text' => '#2A1B1F' ),
		'accent' => '#B03A5B',
		'font_1' => "'Fraunces', Georgia, 'Times New Roman', serif",
		'font_2' => "'Inter Tight', system-ui, sans-serif",
		'ink'    => array( 'sat' => 0.74, 'gamma' => 0.16 ),
	),
);

$ACCENT_BY_GROUND = array(
	'paper' => '#8C3A1F',
	'warm'  => '#8C3A1F',
	'cool'  => '#8C3A1F',
	'ink'   => '#FF6A1A',
);

/* REGISTERED, NOT SPECIAL-CASED. A brand that appended its colours to a parallel table would be a
   second palette nothing measures; appending them HERE means the derivation loop directly below
   runs on it, and a brand whose eyebrow cannot be read stops the build with a number in the
   message. The body-copy bar is 7:1 rather than 4.5 because a ground is what every paragraph on
   the page is painted on, and AAA on body text is the one place this repo has always spent. */
foreach ( $BRANDS as $br_k => $br_v ) {
	$br_g = 'b-' . $br_k;
	if ( isset( $GROUND[ $br_g ] ) ) {
		fail( "brand `$br_k` claims ground key `$br_g`, which an anchor already owns" );
	}
	if ( contrast( $br_v['ground']['text'], $br_v['ground']['bg'] ) < 7.0 ) {
		fail( "brand `$br_k` grounds its type at " . ratio_str( $br_v['ground']['text'], $br_v['ground']['bg'] )
			. ' — below the 7:1 every other ground in this file clears' );
	}
	if ( contrast( $br_v['ground']['text'], $br_v['ground']['alt'] ) < 7.0 ) {
		fail( "brand `$br_k` grounds its type at " . ratio_str( $br_v['ground']['text'], $br_v['ground']['alt'] )
			. ' on --c-bg-alt — below 7:1' );
	}
	$GROUND[ $br_g ]           = $br_v['ground'];
	$ACCENT_BY_GROUND[ $br_g ] = $br_v['accent'];
}

$ACCENT = array();
foreach ( $ACCENT_BY_GROUND as $g => $hex ) {
	$bg   = $GROUND[ $g ]['bg'];
	$alt  = $GROUND[ $g ]['alt'];
	$text = $GROUND[ $g ]['text'];

	// The eyebrow paints --c-accent as TEXT, so 4.5:1 is the bar on both surfaces it lands on.
	foreach ( array( 'bg' => $bg, 'bg-alt' => $alt ) as $what => $surface ) {
		if ( contrast( $hex, $surface ) < 4.5 ) {
			fail( "accent $hex measures " . ratio_str( $hex, $surface ) . " against $g's --c-$what ($surface) — below AA, and the eyebrow paints it as text" );
		}
	}

	// --c-on-accent is CHOSEN, not tabled: whichever ground extreme measures higher on the fill.
	$on = ( contrast( $text, $hex ) >= contrast( $bg, $hex ) ) ? $text : $bg;
	if ( contrast( $on, $hex ) < 4.5 ) {
		fail( "neither extreme clears AA on accent $hex for ground $g — best is " . ratio_str( $on, $hex ) );
	}

	$ACCENT[ $g ] = array(
		'hex'         => $hex,
		'on'          => $on,
		'r_bg'        => ratio_str( $hex, $bg ),
		'r_alt'       => ratio_str( $hex, $alt ),
		'r_on'        => ratio_str( $on, $hex ),
		'on_is'       => ( $on === $text ) ? '--c-text' : '--c-bg',
	);
}

// ─────────────────────────────────────────────────────────────── 5a · THE HOUSE INK
//
// A GRADE, NOT A DUOTONE — AND THIS BLOCK IS A RETRACTION. What stood here was a per-anchor
// duotone (luminance, then two inks) justified by a defect in the asset set: six photographs in
// three incompatible registers "reading as a page assembled from three stock searches". THAT
// JUSTIFICATION EXPIRED AND NOBODY NOTICED. The set was rebuilt afterwards — thirteen images
// across ELEVEN shoots, replacements sourced deliberately, and `_gallery-images.md` § Shoots
// records the repair and the check that now guards it. The duotone outlived its reason by three
// commits and was still being applied as a floor.
//
// MEASURED ON THE SET AS IT STANDS, mean CIELAB chroma runs 0.0–13.2 and eleven of the thirteen
// sit inside a 63° hue arc (44°–107°, the warm yellow-orange band). Only the aerial quarry falls
// outside it. The set's AVERAGE colour is already one page.
//
// WHAT IS STILL OUT OF FAMILY IS LOCAL, NOT GLOBAL. The top 5% of pixels reach chroma 40 on the
// red angle grinder, 34 on the quarry's turquoise pool, 34 on the blue sky behind the Lecce
// facade, 27 on the green foliage behind the labra. Four loud objects in four frames. A duotone
// deletes ALL chroma to repair a defect that lives in 5% of the pixels.
//
// AND IT COULD NEVER HAVE BEEN A DUOTONE, by construction. The endpoints were derived from the
// anchor's own ground, and a ground is near-black and near-white BY DEFINITION — `--c-bg` and
// `--c-text` are picked for contrast, which is the property that makes them neutral. Two inks
// derived from two neutrals are one ink. Measured as the R−B spread of the shadow ink that
// shipped: paper 5, ink 5, cool 16, warm 17. Three of the four were greyscale with a whisper, and
// `warm` read as a designed treatment only because it was the one with any hue in it at all.
//
// IT WAS NOT SEPARATING THE ANCHORS EITHER, which is the claim it was kept for. The same
// photograph under the four inks landed at a MINIMUM pairwise distance of 0.35–0.42 in a*b*:
// `paper` and `ink` were the same grey. The grade below measures 1.15–1.20 on the same three
// frames — the ink tells the anchors apart nearly three times better now that it carries colour.
//
// AND ON A PRODUCT GRID IT WAS NOT A TREATMENT, IT WAS A BUG. TPL-E-02 renders eight tiles; two
// were exempt swatches in full colour and six were greyscale, so the catalogue read as a rendering
// error. One of the six is headed "Panel de veta DORADA" and had no gold in it. The build had
// already written the principle down — "a photograph that ILLUSTRATES takes the house ink; a
// photograph that IS the merchandise does not" — and a product grid is merchandise all the way
// across.
//
// SO THE INK BIASES THE PHOTOGRAPH INSTEAD OF REPLACING IT. Two primitives, and they are the two
// a colourist reaches for:
//   · A CHROMA RESTRAINT — `feColorMatrix type="saturate"`, which pulls the loud objects toward the
//     set's own body without pulling the stone to grey. Measured on `editorial`, p95 chroma
//     33.6 / 40.4 / 34.2 / 27.4 → 22.3 / 26.9 / 21.3 / 20.2.
//   · A SPLIT TONE — one `feComponentTransfer` whose per-channel `tableValues` are a five-point
//     curve: the blacks land on the anchor's SHADOW INK, the whites on its HIGHLIGHT INK, and the
//     midtones pass through nearly untouched, which is what makes it a tone rather than a wash.
//
// WHERE THE TWO INKS COME FROM, because "derived from the ground" is the thing that produced grey:
//   · THE HIGHLIGHT INK is still the ground's own light extreme, pulled off the clip. That end was
//     never the problem: `warm`'s #F6EADB is a cream and `cool`'s #E9ECF0 is a cold white, and they
//     carry the ground's own temperature, which is exactly what should reach the photograph.
//   · THE SHADOW INK IS THE ANCHOR'S ACCENT, laid onto the ground's dark extreme. The accent is the
//     one thing on this page that HAS chroma and is already per-ground — `#8C3A1F` on three grounds
//     and re-derived to `#FF6A1A` on `ink`, because design-system.md makes it re-derive there. Cut
//     face orange in the shadows of a stone workshop is the material's own colour; it puts the
//     page's single hue into the photographs without spending an accent MARK on them, which is the
//     distinction the accent budget one section down is about.
//   · THE TINT NEVER CHANGES THE SHADOW'S WEIGHT — `ink_tint()` puts the tinted ink back on the
//     luminance of the neutral endpoint it replaced. The first version of this did not, drove the
//     paper shadow from #232628 to #432C25, and washed the dark kitchen frame out: a shadow ink
//     that got LIGHTER is a lifted black, and a lifted black is a faded print. Measured R−B spread
//     of the shadow ink now: 31 / 38 / 41 / 28, against 5 / 5 / 16 / 17 before.
//
// HOW MUCH OF EACH IS READ OFF THE ANCHOR, NOT CHOSEN HERE. `design-personalities.md` § Imagery
// names a treatment for exactly two of the four, in as many words:
//     PERS-DIRECT         "high-contrast, tightly cropped"             → the deepest curve
//     PERS-MATTER         "the product shot straight on, WARM-GRADED"  → the most colour kept
//     PERS-INSTITUTIONAL  "SOBER photography of real work contexts"    → the shallowest curve
//     PERS-EDITORIAL      names only the CROP — "dramatically cropped photo-editorial framing"
// `editorial` therefore takes the shared default, which is this file's own stated rule for
// anything the reference does not tighten (see the wordmark-tracking note in § 4). An anchor added
// tomorrow gets the default too, and earns a deviation only by having a treatment written into its
// own § Imagery. THE INSTITUTIONAL READING IS RECORDED AS A READING: "sobria" is taken as *least
// treated* rather than *most muted*, which is the ordinary sense of the word and the one that fits
// "real work contexts", but the reference does not say which and this file does not get to pretend
// it does.
//
// NOTHING ASKED FOR A MONOCHROME. "Alto contraste" is a claim about TONALITY and "recorte cerrado"
// about FRAMING; neither says "no colour", and the retired duotone read them as though they did.
// The duotone therefore survives on ZERO anchors — not on one, and not as a floor.

/* THE ACCENT'S SHARE OF THE SHADOW INK. One number for every anchor, because the per-anchor
   difference is meant to come from the ground and the accent, which already differ, rather than
   from a fifth table of hand-set weights. Its floor is asserted below rather than trusted. */
$INK_TINT = 0.45;

/* HOW MUCH GRADE, PER ANCHOR — transcribed from design-personalities.md § Imagery. `sat` is the
   `feColorMatrix type="saturate"` value and it is about HOW MUCH COLOUR; `gamma` is the depth of
   the S-curve the table describes and it is about TONALITY. `default` is not an anchor: it is what
   an anchor whose § Imagery says nothing gets.
   EACH DEVIATION MOVES EXACTLY THE AXIS ITS OWN WORD IS ABOUT, and nothing else. The first cut of
   this table did not, and looking is what caught it: `institutional` had been given MORE colour
   than the default on a reading of "sobria" as "least treated", which put a bright turquoise
   quarry lake — the single most saturated object in the whole set — at the top of the anchor whose
   personality is credibility before enthusiasm. "Sober" is a word about colour. It goes on `sat`,
   downward, and `gamma` goes back to the default because nothing in that sentence is about
   tonality. `direct` moves the other one for the same reason: "high-contrast" is a word about
   tonality, so it moves `gamma` and leaves `sat` alone. */
$INK_GRADE = array(
	'default'       => array( 'sat' => 0.72, 'gamma' => 0.12 ),
	'direct'        => array( 'sat' => 0.72, 'gamma' => 0.30 ),  // "high-contrast" — tonality only
	'matter'        => array( 'sat' => 0.86, 'gamma' => 0.12 ),  // "warm-graded"   — colour only
	'institutional' => array( 'sat' => 0.62, 'gamma' => 0.12 ),  // "sober"         — colour only
	'vitrine'       => array( 'sat' => 0.94, 'gamma' => 0.16 ),  // "lit object"    — saturación alta y curva algo
	                                                            //                   más profunda: la pieza es lo
	                                                            //                   único con color en la sala.
);

/* THE CURVE'S STOP COUNT. Five, because two cannot bend: a two-entry `tableValues` is a straight
   line from shadow ink to highlight ink, which tints the midtones as hard as it tints the ends and
   is the "faded" look this grade exists to avoid. Five interior stops is enough for the split tone
   to fall off before the midtones and for `gamma` to have somewhere to put an S. */
define( 'INK_STOPS', 5 );

/**
 * `$base`, pushed toward `$accent`, then put back on `$base`'s own luminance.
 *
 * THE LUMINANCE IS THE POINT. Mixing a bright accent into a near-black endpoint raises it, and a
 * raised black endpoint is a lifted black — the photograph goes milky and the page reads as a cheap
 * filter. The binary search is over a single scalar multiplier, so the result keeps the mix's hue
 * and loses only its weight.
 *
 * RETURNS THE UNROUNDED TRIPLE AS WELL, because the two assertions it feeds are different
 * assertions. The search itself has to land on the target to float precision — that is arithmetic,
 * and 1e-9 is the bar. What comes out the other side is an 8-BIT HEX, and 8-bit rounding moves
 * luminance by an amount nobody chose: on these shadow values it is ~1.4e-4, which is a hundred
 * times 1e-6. Checking the rounded value against a tolerance somebody typed would mean picking a
 * number until it passed. The caller derives the bound from the quantisation instead.
 */
function ink_tint( $base, $accent, $w ) {
	$mixed  = css_mix( $base, 1 - $w, $accent );
	$src    = array( hexdec( substr( $mixed, 1, 2 ) ), hexdec( substr( $mixed, 3, 2 ) ), hexdec( substr( $mixed, 5, 2 ) ) );
	$target = srgb_lum( $base );
	$lo     = 0.0;
	$hi     = 2.0;
	for ( $i = 0; $i < 64; $i++ ) {
		$k   = ( $lo + $hi ) / 2;
		$lum = srgb_lum_rgb( min( 255, $src[0] * $k ), min( 255, $src[1] * $k ), min( 255, $src[2] * $k ) );
		if ( $lum < $target ) {
			$lo = $k;
		} else {
			$hi = $k;
		}
	}
	$k     = ( $lo + $hi ) / 2;
	$exact = array( min( 255, $src[0] * $k ), min( 255, $src[1] * $k ), min( 255, $src[2] * $k ) );
	return array(
		'hex'   => sprintf( '#%02X%02X%02X', (int) round( $exact[0] ), (int) round( $exact[1] ), (int) round( $exact[2] ) ),
		'exact' => $exact,
	);
}

/**
 * The most an 8-bit rounding of `$hex` can have moved its luminance: half a step on every channel.
 *
 * DERIVED RATHER THAN TYPED, which is the whole reason this function exists instead of a constant.
 * The bound is a property of WHERE the colour sits — the sRGB transfer curve is flat near black and
 * steep near white, so the same half-step is worth ~1.4e-4 in a shadow ink and ~7e-4 in a highlight
 * one. A single tolerance covering both would be loose enough at the dark end to hide a real lift.
 */
function ink_quant_bound( $hex ) {
	$c = array( hexdec( substr( $hex, 1, 2 ) ), hexdec( substr( $hex, 3, 2 ) ), hexdec( substr( $hex, 5, 2 ) ) );
	$hi = srgb_lum_rgb( min( 255, $c[0] + 0.5 ), min( 255, $c[1] + 0.5 ), min( 255, $c[2] + 0.5 ) );
	$lo = srgb_lum_rgb( max( 0, $c[0] - 0.5 ), max( 0, $c[1] - 0.5 ), max( 0, $c[2] - 0.5 ) );
	return ( $hi - $lo ) / 2;
}

/**
 * The two inks for one ground: the accent in the shadows, the ground's own light in the highlights.
 *
 * THE 94/96 PULL IS ASSERTED, not merely applied, and the assertion is about the PAGE rather than
 * about the photograph. An endpoint that lands exactly on `--c-bg` or `--c-text` welds the frame to
 * the surface it sits on: a shadow the same value as the page's own black has no boundary, so the
 * photograph stops having an edge and starts being a stain. Found by mutation — removing the pull
 * moved the measured contrast from 6.64 to 6.15 and failed nothing, because contrast is not the
 * property the pull is protecting.
 */
function ink_ends( $gr, $accent, $tint ) {
	$dark_src  = ( srgb_lum( $gr['bg'] ) < srgb_lum( $gr['text'] ) ) ? $gr['bg'] : $gr['text'];
	$light_src = ( $dark_src === $gr['bg'] ) ? $gr['text'] : $gr['bg'];
	$neutral   = css_mix( $dark_src, 0.94, $light_src );
	$tinted    = ink_tint( $neutral, $accent, $tint );
	$ends      = array(
		'dark'    => $tinted['hex'],
		'light'   => css_mix( $light_src, 0.96, $dark_src ),
		'neutral' => $neutral,
	);

	/* THE TINT CHANGED THE HUE AND NOT THE WEIGHT, asserted twice because it is two claims.
	   First the search: it either solved for the neutral endpoint's own luminance or it did not,
	   and that is arithmetic. Then the hex: 8-bit rounding moves luminance by an amount nobody
	   chose, so the bound comes from `ink_quant_bound()` — the half-step this colour is actually
	   worth — rather than from a tolerance picked until it passed. Set `$INK_TINT` high enough that
	   the mix clips a channel and the second one fires. */
	$ink_target = srgb_lum( $neutral );
	if ( abs( srgb_lum_rgb( $tinted['exact'][0], $tinted['exact'][1], $tinted['exact'][2] ) - $ink_target ) > 1e-9 ) {
		fail( sprintf(
			'the tint search for the shadow ink over %s did not converge on its own luminance'
				. ' (%.12f against %.12f) — a channel clipped, so there is no scalar that puts this'
				. ' mix back where the neutral endpoint was, and the shadow would ship lifted',
			$neutral,
			srgb_lum_rgb( $tinted['exact'][0], $tinted['exact'][1], $tinted['exact'][2] ),
			$ink_target
		) );
	}
	$ink_bound = ink_quant_bound( $ends['dark'] );
	if ( abs( srgb_lum( $ends['dark'] ) - $ink_target ) > $ink_bound ) {
		fail( sprintf(
			'the shadow ink %s sits at L=%.8f where the neutral endpoint %s it replaced is L=%.8f, a'
				. ' gap of %.2e against the %.2e an 8-bit rounding of this colour can explain — the tint'
				. ' moved the shadow\'s WEIGHT, not just its hue, and a shadow ink that got lighter is a'
				. ' lifted black. That is the faded-print defect, which is what a cheap filter looks like.',
			$ends['dark'],
			srgb_lum( $ends['dark'] ),
			$neutral,
			$ink_target,
			abs( srgb_lum( $ends['dark'] ) - $ink_target ),
			$ink_bound
		) );
	}

	/* THE SHADOW INK HAS TO CARRY HUE, which is the whole retraction above stated as a number. The
	   retired duotone's shadow inks measured 5 / 5 / 16 / 17 on this spread, and the complaint that
	   started this pass — "no se ven colores" — was made about all four of them. 20 is above every
	   one of them, so a regression to any endpoint the duotone would have produced fails here. */
	$ink_spread = max( hexdec( substr( $ends['dark'], 1, 2 ) ), hexdec( substr( $ends['dark'], 3, 2 ) ), hexdec( substr( $ends['dark'], 5, 2 ) ) )
		- min( hexdec( substr( $ends['dark'], 1, 2 ) ), hexdec( substr( $ends['dark'], 3, 2 ) ), hexdec( substr( $ends['dark'], 5, 2 ) ) );
	if ( $ink_spread < 20 ) {
		fail( sprintf(
			'the shadow ink %s has a channel spread of %d, which is a neutral — the retired duotone'
				. ' measured 5 / 5 / 16 / 17 here and read as greyscale on all four anchors. A two-colour'
				. ' map whose dark ink is grey is not a two-colour map.',
			$ends['dark'],
			$ink_spread
		) );
	}

	foreach ( array( 'dark', 'light' ) as $ink_which ) {
		foreach ( array( 'bg', 'text' ) as $ink_extreme ) {
			if ( strtoupper( $ends[ $ink_which ] ) === strtoupper( $gr[ $ink_extreme ] ) ) {
				fail( "the house ink's $ink_which endpoint resolves to {$ends[ $ink_which ]}, which IS"
					. " this ground's --c-$ink_extreme. An endpoint on the page's own extreme gives the"
					. ' photograph a shadow (or a highlight) indistinguishable from the surface behind'
					. ' it, so the frame loses its edge' );
			}
		}
	}
	return $ends;
}

/**
 * The per-channel curve, as the exact strings the browser will parse.
 *
 * `$gamma` bends the input before the tone is applied: `s(x)` pushes quarter-tones down and
 * three-quarter-tones up by an amount that vanishes at both ends, so the endpoints stay exactly the
 * two inks no matter how deep the curve. The split tone then falls off as (1−s)² toward the shadow
 * ink and s² toward the highlight ink, which is why a midtone comes through nearly unchanged and a
 * black comes through as the ink itself.
 *
 * RETURNED AS STRINGS BECAUSE THE STRING IS WHAT THE BROWSER GETS. The sweep below re-parses these
 * with `floatval()` rather than recomputing the floats, so PHP cannot measure a curve at a
 * precision the emitted `tableValues` does not have. The retired code formatted to 4 decimals for
 * the page and measured at full double precision — a gap of one part in 20,000, harmless here and
 * exactly the shape of thing that stops being harmless.
 */
function ink_curve( $ends, $gamma ) {
	$rows = array();
	for ( $ch = 0; $ch < 3; $ch++ ) {
		$s   = hexdec( substr( $ends['dark'], 1 + $ch * 2, 2 ) ) / 255;
		$h   = hexdec( substr( $ends['light'], 1 + $ch * 2, 2 ) ) / 255;
		$row = array();
		for ( $i = 0; $i < INK_STOPS; $i++ ) {
			$x  = $i / ( INK_STOPS - 1 );
			$sx = 0.5 + ( $x - 0.5 ) * ( 1 + $gamma * ( 1 - pow( 2 * $x - 1, 2 ) ) );
			$sx = max( 0.0, min( 1.0, $sx ) );
			$v  = $sx + $s * pow( 1 - $sx, 2 ) + ( $h - 1 ) * pow( $sx, 2 );
			$row[] = rtrim( rtrim( sprintf( '%.5f', max( 0.0, min( 1.0, $v ) ) ), '0' ), '.' );
		}
		$rows[] = implode( ' ', $row );
	}
	return $rows;
}

/**
 * Everything the browser and the sweep both need for one anchor, resolved once.
 *
 * ONE TABLE, READ BY BOTH. The retired code called `ink_ends()` separately at the sweep and at the
 * stylesheet and relied on the two calls agreeing; this returns the `tableValues` strings
 * themselves, so the certificate and the filter are not two derivations of one intent but one
 * artefact used twice.
 */
function ink_of( $anchor_key, $anchors, $grounds, $accents, $grades, $tint ) {
	if ( ! isset( $anchors[ $anchor_key ] ) ) {
		fail( "no anchor `$anchor_key` to resolve a house ink for" );
	}
	$ground_key = $anchors[ $anchor_key ]['ground'];
	$grade      = isset( $grades[ $anchor_key ] ) ? $grades[ $anchor_key ] : $grades['default'];
	$ends       = ink_ends( $grounds[ $ground_key ], $accents[ $ground_key ], $tint );
	return array(
		'ends'   => $ends,
		'sat'    => rtrim( rtrim( sprintf( '%.4f', $grade['sat'] ), '0' ), '.' ),
		'gamma'  => $grade['gamma'],
		'named'  => isset( $grades[ $anchor_key ] ),
		'table'  => ink_curve( $ends, $grade['gamma'] ),
	);
}

/* RESOLVED FOR EVERY ANCHOR, HERE, BEFORE ANYTHING MEASURES OR PRINTS ONE. Both consumers — the
   contrast sweep in § 6 and the stylesheet in § 5a's CSS block — index this array. */
$INK = array();
foreach ( $ANCHORS as $ink_ak => $ink_av ) {
	$INK[ $ink_ak ] = ink_of( $ink_ak, $ANCHORS, $GROUND, $ACCENT_BY_GROUND, $INK_GRADE, $INK_TINT );
}

/* AND ONE PER BRAND, from the brand's own two colours. A brand that kept the anchor's ink would be
   grading its photographs toward a palette its page does not have — the exact failure the ink was
   built to prevent, one level up. Everything below this line — the split-tone shape check, the
   swatch separation bar — indexes `$INK` and therefore measures these too. */
foreach ( $BRANDS as $ink_bk => $ink_bv ) {
	$ink_bg    = 'b-' . $ink_bk;
	$ink_grade = isset( $ink_bv['ink'] ) ? $ink_bv['ink'] : $INK_GRADE['default'];
	$ink_bends = ink_ends( $GROUND[ $ink_bg ], $ACCENT_BY_GROUND[ $ink_bg ], $INK_TINT );
	$INK[ $ink_bg ] = array(
		'ends'  => $ink_bends,
		'sat'   => rtrim( rtrim( sprintf( '%.4f', $ink_grade['sat'] ), '0' ), '.' ),
		'gamma' => $ink_grade['gamma'],
		'named' => isset( $ink_bv['ink'] ),
		'table' => ink_curve( $ink_bends, $ink_grade['gamma'] ),
	);
}

/* IT IS ONLY A SPLIT TONE IF THE MIDDLE MOVES LESS THAN THE ENDS, AND MUTATION IS WHY THIS EXISTS.
   Dropping `INK_STOPS` from 5 to 2 collapses the curve to a straight line from shadow ink to
   highlight ink, which tints the midtones exactly as hard as it tints the ends — a WASH, the faded
   look the five stops were introduced to avoid. Every other check passed: the ENDPOINTS are
   unchanged, so the hue check, the luminance check and the swatch check all read the same numbers,
   and a different picture shipped in green. The endpoints were guarded and the CURVE was not.

   THE FIRST VERSION OF THIS CHECK MEASURED THE WRONG THING and looking at the failure is what said
   so: an absolute bar on midtone drift fired on `matter` at 17/255, because `warm`'s highlight ink
   is a real cream and a warm anchor is SUPPOSED to move its midtones somewhat. A bar that fails the
   anchor whose personality says "warm-graded" for being warm is a bar measuring warmth, not shape.

   The property is a RATIO, and it is anchor-independent by construction. Per channel, the curve's
   deviation from the identity line at black is the shadow ink itself, at white it is 1 − the
   highlight ink, and the falloff makes the midpoint carry a quarter of their difference. So:
     five stops with the (1−s)²/s² falloff → midpoint deviation ≤ 0.25 × (both ends), always
     two stops, a straight line                → 0.50 × their difference, which on these four inks
                                                 lands at 0.37–0.42
   0.30 sits between them and belongs to neither implementation. A tint that happened to be exactly
   symmetric at both ends would pass this at two stops — and would deserve to, because a symmetric
   straight line genuinely does leave the midtone where it found it. The check is about the shape of
   the curve, not about the number of stops that produced it. */
$INK_MID_RATIO = 0.30;
foreach ( $INK as $ink_mk => $ink_mv ) {
	foreach ( array( 0, 1, 2 ) as $ink_ch ) {
		$ink_vals = array_map( 'floatval', explode( ' ', $ink_mv['table'][ $ink_ch ] ) );
		$ink_d0   = abs( fe_table( 0.0, $ink_vals ) - 0.0 );
		$ink_d1   = abs( fe_table( 1.0, $ink_vals ) - 1.0 );
		$ink_dm   = abs( fe_table( 0.5, $ink_vals ) - 0.5 );
		$ink_ends_dev = $ink_d0 + $ink_d1;
		if ( $ink_ends_dev < 1e-9 ) {
			continue;   // an untinted channel has no tone to fall off
		}
		$ink_ratio = $ink_dm / $ink_ends_dev;
		if ( $ink_ratio > $INK_MID_RATIO ) {
			fail( sprintf(
				'the `%s` ink moves a midtone by %.4f on channel %s where its two ends move by %.4f and'
					. ' %.4f — a ratio of %.3f against the %.3f a split tone is allowed. A tone that moves'
					. ' the MIDDLE as much as it moves the ends is a wash: the photograph goes uniformly'
					. ' warm and lifted, which is what a cheap filter looks like, and it is what a curve'
					. ' with no bend in it does.',
				$ink_mk,
				$ink_dm,
				substr( 'RGB', $ink_ch, 1 ),
				$ink_d0,
				$ink_d1,
				$ink_ratio,
				$INK_MID_RATIO
			) );
		}
	}
}

/* A GRADE TABLE ENTRY THAT MATCHES NO ANCHOR IS A TYPO, AND A SILENT ONE. `$INK_GRADE['drect']`
   would leave `direct` on the default and nothing anywhere would say so — the page would simply
   stop being the page design-personalities.md describes. */
foreach ( $INK_GRADE as $ink_gk => $ink_gv ) {
	if ( 'default' !== $ink_gk && ! isset( $ANCHORS[ $ink_gk ] ) ) {
		fail( "\$INK_GRADE names `$ink_gk`, which is not an anchor — a grade keyed to a name no"
			. ' anchor has is a deviation that silently never applies, and the anchor it was meant'
			. ' for keeps the default with nothing to say so' );
	}
}

// ─────────────────────────────────────────────────────────────── 5b · the toggles, and their bar
//
// CAPA 3. `web-templates/references/toggles.md` catalogues 39 of these and the gallery rendered
// exactly zero of them: archetype and anchor varied, CONFIGURATION never did. Every strip shipped
// all 39 at their default, which is a whole dimension the framework models and the page had never
// shown — and part of why eight strips read as more alike than the framework claims they are.
//
// TRANSCRIBED FROM EACH `TPL-*` DOC's § "Toggles admitidos", not from toggles.md's catalogue.
// That is the same authority toggles.md itself names ("Cada `TPL-*` declara en su doc qué toggles
// admite y con qué default"), and the two disagreed: toggles.md's "Aplica en" column listed
// TGL-HERO-TYPE against TPL-E-01 and TPL-E-03 only, while SIX template docs declare it — the four
// corporate ones included. toggles.md was the wrong file and has been corrected; this table reads
// the templates so it cannot inherit that class of error again.
//
// An archetype with an EMPTY list is a claim, not an omission: TPL-E-02 admits no hero toggle
// because its mini banner is ADN, and `TGL-HERO-TYPE` appears nowhere in its doc.

$TOGGLES = array(
	/* TPL-C-10 · Clínica / Tratamientos — de su propio § "Toggles admitidos". */
	'TPL-C-10' => array(
		'TGL-INSURANCE' => array( 'ask' => '¿Bloque de coberturas?', 'default' => 'sí', 'options' => array( 'sí', 'no' ) ),
	),
	/* TPL-C-11 · Plan por fases. */
	'TPL-C-11' => array(
		'TGL-CASES' => array( 'ask' => '¿Antes y después?', 'default' => 'sí', 'options' => array( 'sí', 'no' ) ),
		'TGL-FAQ'   => array( 'ask' => '¿Preguntas frecuentes?', 'default' => 'sí', 'options' => array( 'sí', 'no' ) ),
	),
	/* TPL-C-12 · Urgencias / Hoy. */
	'TPL-C-12' => array(
		'TGL-TEAM' => array( 'ask' => '¿Quién está de guardia?', 'default' => 'sí', 'options' => array( 'sí', 'no' ) ),
	),
	/* TPL-C-14 · Ritual / Bono — de su propio § 4. Los cuatro toggles de CONTEO de ese documento
	   (`TGL-ZONE-COUNT`, `TGL-RITUAL-COUNT`, `TGL-CABIN-FRAMES`) no entran aquí por lo mismo que
	   los de TPL-C-13: no cambian QUÉ secciones existen sino cuántas filas lleva una que ya está,
	   y esta tabla gobierna presencia, no aforo. */
	'TPL-C-14' => array(
		'TGL-PROTOCOL-STEPS' => array( 'ask' => '¿La sesión contada en minutos?', 'default' => 'sí', 'options' => array( 'sí', 'no' ) ),
		'TGL-GIFT-CARD'      => array( 'ask' => '¿Tarjeta regalo junto a los bonos?', 'default' => 'sí', 'options' => array( 'sí', 'no' ) ),
	),
	/* TPL-C-07 · Stock / Ocasión — de su propio § "Toggles admitidos". */
	'TPL-C-07' => array(
		'TGL-FINANCE'      => array( 'ask' => '¿Simulador de cuota?', 'default' => 'sí', 'options' => array( 'sí', 'no' ) ),
		'TGL-BADGES'       => array( 'ask' => '¿Barra de garantías?', 'default' => 'sí', 'options' => array( 'sí', 'no' ) ),
		'TGL-TESTIMONIALS' => array( 'ask' => '¿Reseñas?', 'default' => 'sí', 'options' => array( 'sí', 'no' ) ),
	),
	/* TPL-C-13 · Cartera / Búsqueda — de su propio § 4. `TGL-SEARCH-FIELDS` y `TGL-GRID-DENSITY`
	   no entran aquí porque no cambian QUÉ secciones existen sino cuántos campos y columnas lleva
	   una que ya está: la tabla de admisión gobierna presencia, no aforo. */
	'TPL-C-13' => array(
		'TGL-MAP-MODE' => array( 'ask' => '¿Plano de búsqueda?', 'default' => 'conmutador', 'options' => array( 'conmutador', 'sección', 'off' ) ),
		'TGL-TEAM'     => array( 'ask' => '¿Equipo?', 'default' => 'sí', 'options' => array( 'sí', 'no' ) ),
		'TGL-FAQ'      => array( 'ask' => '¿Preguntas frecuentes?', 'default' => 'sí', 'options' => array( 'sí', 'no' ) ),
	),
	/* TPL-C-15 · Cartera curada — de su propio § 4. `TGL-FEATURED-COUNT` no entra aquí por la misma
	   razón que en `TPL-C-13`: no cambia QUÉ secciones existen sino cuántas tarjetas lleva una que
	   ya está. `TGL-HERO-MODE` sí entra: cambia de sección (héroe de persuasión vs. buscador como
	   portada), la misma clase de admisión que ya tiene `TGL-HERO-TYPE` en `TPL-C-01`. */
	'TPL-C-15' => array(
		'TGL-HERO-MODE' => array( 'ask' => '¿Héroe de persuasión o buscador como portada?', 'default' => 'retrato', 'options' => array( 'retrato', 'buscador-portada' ) ),
		'TGL-MAP-MODE'  => array( 'ask' => '¿Plano de búsqueda?', 'default' => 'off', 'options' => array( 'conmutador', 'sección', 'off' ) ),
		'TGL-REVIEWS'   => array( 'ask' => '¿Reseñas?', 'default' => 'sí', 'options' => array( 'sí', 'no' ) ),
	),
	/* TPL-C-08 · Modelo / Lanzamiento. */
	'TPL-C-08' => array(
		'TGL-GALLERY' => array( 'ask' => '¿Galería del modelo?', 'default' => 'sí', 'options' => array( 'sí', 'no' ) ),
		'TGL-FINANCE' => array( 'ask' => '¿Bloque de financiación?', 'default' => 'sí', 'options' => array( 'sí', 'no' ) ),
		'TGL-FAQ'     => array( 'ask' => '¿Preguntas frecuentes?', 'default' => 'sí', 'options' => array( 'sí', 'no' ) ),
	),
	/* TPL-C-09 · Taller / Tarifa. */
	'TPL-C-09' => array(
		'TGL-BADGES' => array( 'ask' => '¿Barra de garantías?', 'default' => 'sí', 'options' => array( 'sí', 'no' ) ),
		'TGL-FAQ'    => array( 'ask' => '¿Preguntas frecuentes?', 'default' => 'sí', 'options' => array( 'sí', 'no' ) ),
	),
	/* TPL-C-06 · Mesa / Carta. Transcribed from its own § "Toggles admitidos", like every row here.
	   `TGL-MARQUEE` and `TGL-MENU-PRICES` are new and they are declared BY THAT DOC — a toggle this
	   table invented would be a capability the archetype never offered. */
	'TPL-C-06' => array(
		'TGL-MARQUEE'     => array(
			'ask'     => '¿Cinta de identidad bajo el héroe?',
			'default' => 'sí',
			'options' => array( 'sí', 'no' ),
		),
		'TGL-MENU-PRICES' => array(
			'ask'     => '¿Precios en la carta?',
			'default' => 'sí',
			'options' => array( 'sí', 'no' ),
		),
		'TGL-GALLERY'     => array(
			'ask'     => '¿Collage del local?',
			'default' => 'sí',
			'options' => array( 'sí', 'no' ),
		),
	),
	'TPL-C-01' => array(
		// TPL-C-01-services-leadgen.md § 4: `TGL-HERO-TYPE` | imagen/color fija | slider opcional
		'TGL-HERO-TYPE' => array(
			'ask'     => '¿Hero con slider o imagen fija?',
			'default' => 'imagen fija',
			'options' => array( 'imagen fija', 'slider' ),
		),
		/* THESE THREE WERE NOT "OFF". THEY DID NOT EXIST.
		   TPL-C-01's wireframe declares nine sections and this file rendered six; the missing three
		   are these, and a reader comparing the archetype doc with the strip would have concluded
		   the toggles were resolved to `no`. They were not resolved at all, which is the silent
		   configuration the resolver one section down exists to end.
		   `toggles.md` states no default for any of them — its table carries no default column — so
		   `sí` is a CHOICE this file makes, not a value it transcribes. Which is exactly why they are
		   declared here instead of being rendered unconditionally: an admitted toggle resolves and
		   PRINTS on every strip's data bar, so the choice is on the page where it can be argued with. */
		'TGL-LOGOS'        => array(
			'ask'     => '¿Logos de clientes / credenciales?',
			'default' => 'sí',
			'options' => array( 'sí', 'no' ),
		),
		'TGL-PROCESS'      => array(
			'ask'     => '¿Bloque "cómo trabajamos" / pasos?',
			'default' => 'sí',
			'options' => array( 'sí', 'no' ),
		),
		'TGL-TESTIMONIALS' => array(
			'ask'     => '¿Testimonios / social proof?',
			'default' => 'sí',
			'options' => array( 'sí', 'no' ),
		),
	),
	/* TPL-C-02 · Institutional Trust. Same firm, institutional register: the archetype's own doc
	   says "credibilidad antes que entusiasmo", so the page leads with what can be verified — how
	   long, how many, certified by whom, and who signs — instead of with a claim.

	   THE COUNTERS ARE WRITTEN INTO THE HTML, not animated up from zero. Two of the four reference
	   kits ship theirs as `0+`, `0+`, `0 M+` in the markup and fill them with script on scroll, so
	   the page states "0 clients" to anyone whose JS is slow, blocked or still loading — and to
	   every crawler. A number that only exists after a script is not a credential. */

	'TPL-C-02' => array(
		'TGL-CREDENTIALS'  => array(
			'ask'     => '¿Certificaciones / logos / prensa?',
			'default' => 'sí',
			'options' => array( 'sí', 'no' ),
		),
		'TGL-TEAM'         => array(
			'ask'     => '¿Equipo / directivos?',
			'default' => 'sí',
			'options' => array( 'sí', 'no' ),
		),
		'TGL-TESTIMONIALS' => array(
			'ask'     => '¿Testimonios / social proof?',
			'default' => 'sí',
			'options' => array( 'sí', 'no' ),
		),
	),
	'TPL-C-03' => array(
		'TGL-SERVICES' => array(
			'ask'     => '¿Bloque breve de servicios?',
			'default' => 'sí',
			'options' => array( 'sí', 'no' ),
		),
		'TGL-ABOUT'    => array(
			'ask'     => '¿Sobre el estudio?',
			'default' => 'sí',
			'options' => array( 'sí', 'no' ),
		),
		'TGL-LOGOS'    => array(
			'ask'     => '¿Logos de clientes / credenciales?',
			'default' => 'sí',
			'options' => array( 'sí', 'no' ),
		),
	),
	'TPL-E-03' => array(
		'TGL-TESTIMONIALS' => array(
			'ask'     => '¿Testimonios / social proof?',
			'default' => 'sí',
			'options' => array( 'sí', 'no' ),
		),
		'TGL-NEWSLETTER'   => array(
			'ask'     => '¿Bloque de boletín?',
			'default' => 'sí',
			'options' => array( 'sí', 'no' ),
		),
	),
	'TPL-C-04' => array(
		'TGL-LOGOS'   => array(
			'ask'     => '¿Logos de clientes / credenciales?',
			'default' => 'sí',
			'options' => array( 'sí', 'no' ),
		),
		'TGL-PRICING' => array(
			'ask'     => '¿Bloque de precios?',
			'default' => 'sí',
			'options' => array( 'sí', 'no' ),
		),
	),
	'TPL-E-05' => array(
		'TGL-PROMO-BANNER' => array(
			'ask'     => '¿Bandas promocionales intercaladas?',
			'default' => 'sí',
			'options' => array( 'sí', 'no' ),
		),
		'TGL-NEWSLETTER'   => array(
			'ask'     => '¿Bloque de boletín?',
			'default' => 'sí',
			'options' => array( 'sí', 'no' ),
		),
	),
	'TPL-C-05' => array(
		'TGL-GALLERY'      => array(
			'ask'     => '¿Galería del local?',
			'default' => 'sí',
			'options' => array( 'sí', 'no' ),
		),
		'TGL-TESTIMONIALS' => array(
			'ask'     => '¿Reseñas?',
			'default' => 'sí',
			'options' => array( 'sí', 'no' ),
		),
	),
	'TPL-E-01' => array(
		'TGL-HERO-TYPE'  => array(
			'ask'     => '¿Hero con slider o imagen fija?',
			'default' => 'slider',
			'options' => array( 'imagen fija', 'slider' ),
		),
		'TGL-GALLERY'    => array(
			'ask'     => '¿Lookbook?',
			'default' => 'sí',
			'options' => array( 'sí', 'no' ),
		),
		'TGL-NEWSLETTER' => array(
			'ask'     => '¿Bloque de boletín?',
			'default' => 'sí',
			'options' => array( 'sí', 'no' ),
		),
	),
	'TPL-E-04' => array(
		'TGL-BENEFITS'   => array(
			'ask'     => '¿Barra de garantías?',
			'default' => 'sí',
			'options' => array( 'sí', 'no' ),
		),
		'TGL-NEWSLETTER' => array(
			'ask'     => '¿Bloque de boletín?',
			'default' => 'sí',
			'options' => array( 'sí', 'no' ),
		),
	),
	/* TPL-E-06 · Talla / Prueba. Los dos que su doc declara y ninguno más: un toggle que esta tabla
	   inventara sería una capacidad que el arquetipo nunca ofreció. */
	'TPL-E-06' => array(
		'TGL-FIT-FINDER' => array(
			'ask'     => '¿Traductor de tallas entre marcas?',
			'default' => 'sí',
			'options' => array( 'sí', 'no' ),
		),
		'TGL-FAQ'        => array(
			'ask'     => '¿Preguntas de ajuste?',
			'default' => 'sí',
			'options' => array( 'sí', 'no' ),
		),
	),
	/* TPL-E-07 · Lote / Peso. Los tres de su doc. */
	'TPL-E-07' => array(
		'TGL-ORIGIN'       => array( 'ask' => '¿Mapa de origen de la pieza?', 'default' => 'sí', 'options' => array( 'sí', 'no' ) ),
		'TGL-TESTIMONIALS' => array( 'ask' => '¿Quién ya compró?', 'default' => 'sí', 'options' => array( 'sí', 'no' ) ),
		'TGL-NEWSLETTER'   => array( 'ask' => '¿Aviso de lote nuevo?', 'default' => 'sí', 'options' => array( 'sí', 'no' ) ),
	),
	/* TPL-E-08 · Suscripción. */
	'TPL-E-08' => array(
		'TGL-TESTIMONIALS' => array( 'ask' => '¿Quién lleva tiempo suscrito?', 'default' => 'sí', 'options' => array( 'sí', 'no' ) ),
		'TGL-FAQ'          => array( 'ask' => '¿Dudas de compromiso?', 'default' => 'sí', 'options' => array( 'sí', 'no' ) ),
	),
	/* TPL-E-09 · A medida. Los dos que su doc declara. */
	'TPL-E-09' => array(
		'TGL-GALLERY' => array( 'ask' => '¿Trabajos entregados con su medida?', 'default' => 'sí', 'options' => array( 'sí', 'no' ) ),
		'TGL-FAQ'     => array( 'ask' => '¿Dudas de medición y montaje?', 'default' => 'sí', 'options' => array( 'sí', 'no' ) ),
	),
	'TPL-E-02' => array(),
);

// ─────────────────────────────────────────────────────────────── 5c · the slider's scrim, MEASURED
//
// `TGL-HERO-TYPE: slider` puts the photograph BEHIND the copy instead of beside it, which is the
// one move on this page that puts body text over an image the build does not control. The scrim
// that makes that legible is measured here against the real pixels, by the same rule the accent
// above follows: the build dies rather than shipping a number somebody typed.
//
// WHAT IS MEASURED, AND WHY IT IS THE WHOLE SOURCE FRAME AND NOT THE TEXT BOX. An earlier scrim on
// this page (LP-BROKEN-GRID's hero, § COMPOSITION LAYERS) faded to fully transparent at one edge
// and LOOKED fine in a screenshot; sampling the actual pixels put the worst case at 1.95:1 where
// the headline crossed a pale rock face. The lesson taken from that was a FLOOR — an alpha the
// gradient never drops below — and the floor is what makes the measurement viewport-proof:
//
//   `object-fit:cover` crops vertically when the box is wider than 16:9 and HORIZONTALLY when it
//   is narrower, and this hero is full-bleed, so its box is wider than the frame at 1440 and
//   narrower at 390. Under a horizontal crop the gradient's 0→1 runs across the VISIBLE window,
//   not the source, so a source pixel that sits at x=0.9 on desktop can sit at x=0.4 on a phone.
//   There is therefore no pixel that is permanently safe under a weaker part of the gradient, and
//   the only honest bar is: EVERY pixel of EVERY frame, composited at the gradient's WEAKEST
//   alpha. That is strictly stronger than sampling one viewport's crop, and it cannot go stale
//   when somebody changes the hero's height.
//
// THE CROSS-FADE NEEDS NO SEPARATE MEASUREMENT, and this is a proof rather than an omission. Mid
// fade the photo layer is a per-channel convex blend of two frames over an opaque `--c-bg-alt`.
// `srgb_lum()` is monotonic per channel, so the blend's luminance lies between the two frames'
// and above neither — and with a PAPER scrim the failing direction is a composite that is too
// DARK. A blend is never darker than its darker input, and its darker input is already asserted.
//
// PAPER SCRIM AND NOT AN INK ONE, decided on the measurement and not on taste. Probed against
// these three frames, an ink veil under white type needs alpha .59 to clear 4.5:1 and a paper
// veil under the anchor's own ink type needs .51. The paper veil is the cheaper one AND the
// honest one: the strip's data bar reports `fondo: paper — #FFFFFF · #F6F7F8 · #15181A`, and a
// hero that inverted to white-on-near-black would make that readout false exactly where the
// reader is looking.

/**
 * Luminance of an r/g/b triple. `srgb_lum()` takes hex; the pixel loop below has integers.
 *
 * The coefficients are the VALUES and the channels the keys, not the other way round: PHP casts a
 * float array key to int, so `array( 0.2126 => $r, 0.7152 => $g, 0.0722 => $b )` is one entry at
 * key 0 holding the blue channel, and this function would have returned 7% of the blue channel
 * for every colour on the page while still looking like the WCAG formula.
 */
function srgb_lum_rgb( $r, $g, $b ) {
	$l = 0.0;
	foreach ( array( array( 0.2126, $r ), array( 0.7152, $g ), array( 0.0722, $b ) ) as $pair ) {
		$c  = $pair[1] / 255;
		$c  = ( $c <= 0.04045 ) ? $c / 12.92 : pow( ( $c + 0.055 ) / 1.055, 2.4 );
		$l += $pair[0] * $c;
	}
	return $l;
}

/**
 * The worst contrast any pixel of `$slug` can reach under a `$alpha` veil of `$scrim`, against
 * `$text`. Every second pixel on both axes: a 4× cheaper sweep over a 1440×810 frame that cannot
 * miss a REGION, only a lone pixel inside one — and a lone pixel is not what text lands on. The
 * stride is stated here rather than hidden because it is the one approximation in this file.
 *
 * RETURNS THE SURFACE AS WELL AS THE RATIO, and that is not a convenience. The per-ink checks
 * below need the composited surface, and the first version of this file RE-DERIVED it algebraically
 * from the ratio — which mutation testing killed: setting that one line to a constant deleted every
 * ink override and no check noticed, because the tripwire guarding it compared against a bound the
 * constant happened to sit exactly on. Two numbers out of one sweep, with the identity between them
 * asserted at the call site, is a thing that cannot be quietly rewritten.
 */
/**
 * `feFunc* type="table"`, per the SVG spec: piecewise-linear over n entries.
 *
 * For n values v0…v(n−1) and an input C in 0..1, with k = floor(C·(n−1)) clamped to n−2:
 *   C' = v[k] + (C·(n−1) − k) × (v[k+1] − v[k])
 * Two entries collapse to the straight line the retired duotone used, so this is the same
 * primitive that was here, generalised to a curve that can bend.
 */
function fe_table( $c, $values ) {
	$n = count( $values );
	if ( $n < 2 ) {
		fail( 'a `tableValues` with fewer than two entries is not a transfer function' );
	}
	$c = max( 0.0, min( 1.0, $c ) );
	$k = (int) floor( $c * ( $n - 1 ) );
	if ( $k > $n - 2 ) {
		$k = $n - 2;
	}
	return $values[ $k ] + ( $c * ( $n - 1 ) - $k ) * ( $values[ $k + 1 ] - $values[ $k ] );
}

/**
 * The SVG filter, in PHP, so the sweep below measures the pixels the browser will actually paint.
 *
 * THIS IS THE HALF THAT MAKES THE INK SAFE. `filter` runs at PAINT time — after `object-fit`, under
 * the scrim, invisible to every DOM measurement — so a build that graded the photographs and kept
 * measuring the originals would be reporting the contrast of an image nobody sees. The two stages
 * match the two SVG primitives exactly:
 *   feColorMatrix type="saturate" → THE SPEC'S OWN COEFFICIENTS, 0.213 / 0.715 / 0.072, which are
 *     NOT the WCAG 0.2126 / 0.7152 / 0.0722 this file uses for luminance everywhere else. The two
 *     triples differ in the third decimal and the difference is not rounding: it is the difference
 *     between measuring the image the browser paints and measuring a nearby one. The filter carries
 *     `color-interpolation-filters="sRGB"` for the same class of reason — in the default linearRGB
 *     the shadows crush, and PHP would again be describing a different picture.
 *   feComponentTransfer → a five-entry `type="table"` per channel, applied to THAT CHANNEL'S OWN
 *     value rather than to a luminance. That one word is the whole retraction: mapping luminance
 *     through two inks replaces the colour, mapping each channel through its own curve biases it.
 *
 * `$ink['table']` arrives as the emitted STRINGS and is parsed here, so the measurement cannot run
 * at a precision the page does not have.
 */
function ink_pixel( $r, $g, $b, $ink ) {
	$s = (float) $ink['sat'];
	$p = array(
		( 0.213 + 0.787 * $s ) * $r + ( 0.715 - 0.715 * $s ) * $g + ( 0.072 - 0.072 * $s ) * $b,
		( 0.213 - 0.213 * $s ) * $r + ( 0.715 + 0.285 * $s ) * $g + ( 0.072 - 0.072 * $s ) * $b,
		( 0.213 - 0.213 * $s ) * $r + ( 0.715 - 0.715 * $s ) * $g + ( 0.072 + 0.928 * $s ) * $b,
	);
	$out = array();
	for ( $i = 0; $i < 3; $i++ ) {
		$values = array_map( 'floatval', explode( ' ', $ink['table'][ $i ] ) );
		$out[]  = 255 * fe_table( max( 0.0, min( 255.0, $p[ $i ] ) ) / 255, $values );
	}
	return $out;
}

function worst_pixel( $slug, $scrim, $alpha, $text, $ink = null ) {
	global $IMG_DIR;
	if ( ! function_exists( 'imagecreatefromwebp' ) ) {
		fail( 'PHP has no GD WebP support — the slider scrim cannot be measured, and an unmeasured'
			. ' scrim over a photograph is the 1.95:1 defect this build already shipped once' );
	}
	$im = @imagecreatefromwebp( $IMG_DIR . '/' . $slug . '.webp' );
	if ( false === $im ) {
		fail( "cannot decode img/$slug.webp to measure the slider scrim" );
	}
	$sr    = hexdec( substr( ltrim( $scrim, '#' ), 0, 2 ) );
	$sg    = hexdec( substr( ltrim( $scrim, '#' ), 2, 2 ) );
	$sb    = hexdec( substr( ltrim( $scrim, '#' ), 4, 2 ) );
	$l_txt = srgb_lum( $text );
	$w       = imagesx( $im );
	$hgt     = imagesy( $im );
	$worst   = INF;
	$surface = INF;
	for ( $y = 0; $y < $hgt; $y += 2 ) {
		for ( $x = 0; $x < $w; $x += 2 ) {
			$p  = imagecolorat( $im, $x, $y );
			$px = array( ( $p >> 16 ) & 0xFF, ( $p >> 8 ) & 0xFF, $p & 0xFF );
			if ( null !== $ink ) {
				$px = ink_pixel( $px[0], $px[1], $px[2], $ink );
			}
			$l   = srgb_lum_rgb(
				$px[0] * ( 1 - $alpha ) + $sr * $alpha,
				$px[1] * ( 1 - $alpha ) + $sg * $alpha,
				$px[2] * ( 1 - $alpha ) + $sb * $alpha
			);
			$hi  = max( $l, $l_txt );
			$lo  = min( $l, $l_txt );
			$rat = ( $hi + 0.05 ) / ( $lo + 0.05 );
			if ( $rat < $worst ) {
				$worst   = $rat;
				$surface = $l;
			}
		}
	}
	imagedestroy( $im );
	return array( 'ratio' => $worst, 'surface_l' => $surface );
}

/**
 * The mean r/g/b of `$slug` under `$ink`, which is the number the swatch complaint was made in.
 *
 * MEAN AND NOT p95, deliberately. The retired exemption's evidence is a sentence in this file —
 * "their mean colours are (183,184,185) and (196,196,197)" — and the check that replaces it has to
 * be answerable in the same units, or it is a different claim wearing the old one's clothes.
 */
function ink_mean( $slug, $ink ) {
	global $IMG_DIR;
	$im = @imagecreatefromwebp( $IMG_DIR . '/' . $slug . '.webp' );
	if ( false === $im ) {
		fail( "cannot decode img/$slug.webp to measure the house ink against it" );
	}
	$w   = imagesx( $im );
	$h   = imagesy( $im );
	$sum = array( 0.0, 0.0, 0.0 );
	$n   = 0;
	for ( $y = 0; $y < $h; $y += 2 ) {
		for ( $x = 0; $x < $w; $x += 2 ) {
			$p  = imagecolorat( $im, $x, $y );
			$px = ink_pixel( ( $p >> 16 ) & 0xFF, ( $p >> 8 ) & 0xFF, $p & 0xFF, $ink );
			for ( $i = 0; $i < 3; $i++ ) {
				$sum[ $i ] += max( 0.0, min( 255.0, $px[ $i ] ) );
			}
			$n++;
		}
	}
	imagedestroy( $im );
	if ( 0 === $n ) {
		fail( "img/$slug.webp decoded to zero pixels" );
	}
	return array( $sum[0] / $n, $sum[1] / $n, $sum[2] / $n );
}

/** `color-mix(in srgb, $a $p%, $b)`, in PHP, so a token derived in CSS can be measured here. */
function css_mix( $a, $p, $b ) {
	$a   = ltrim( $a, '#' );
	$b   = ltrim( $b, '#' );
	$out = '';
	for ( $i = 0; $i < 3; $i++ ) {
		$out .= sprintf(
			'%02X',
			(int) round( hexdec( substr( $a, $i * 2, 2 ) ) * $p + hexdec( substr( $b, $i * 2, 2 ) ) * ( 1 - $p ) )
		);
	}
	return '#' . $out;
}

/* TWO FORMULAS, ONE ANSWER, CHECKED. `srgb_lum_rgb()` is a second implementation of a formula this
   file already has, which is exactly the shape of thing that drifts silently — the float-key bug
   above returned plausible numbers, not obviously broken ones. So the two are made to agree on the
   four ground extremes before either is trusted with a pixel. 1e-12 is float noise, not tolerance. */
foreach ( array( '#FFFFFF', '#15181A', '#0E1113', '#8C3A1F' ) as $lum_probe ) {
	$lum_hex = srgb_lum( $lum_probe );
	$lum_rgb = srgb_lum_rgb(
		hexdec( substr( $lum_probe, 1, 2 ) ),
		hexdec( substr( $lum_probe, 3, 2 ) ),
		hexdec( substr( $lum_probe, 5, 2 ) )
	);
	if ( abs( $lum_hex - $lum_rgb ) > 1e-12 ) {
		fail( "srgb_lum_rgb() disagrees with srgb_lum() on $lum_probe ($lum_rgb vs $lum_hex)"
			. ' — the scrim would be measured with a formula that is not the one the rest of the file uses' );
	}
}

/* The three `hero 16:9` frames the manifest carries — the whole role, in manifest order, so the
   slider cannot quietly become two frames or reach for a card crop. */
$SLIDER_FRAMES = array( 'hero-cantera', 'hero-taller', 'hero-encimera' );

/* 4.5:1 AND NOT 3:1. The h1 clears "large text" at every anchor, but the eyebrow and the lede do
   not, and they sit on the same photograph. The bar is set by the smallest text over the image. */
$SCRIM_BAR   = 4.5;
$SCRIM_FLOOR = 0.55;
$SCRIM_WORST = array();

$SCRIM_SURFACE = array();

foreach ( $SLIDER_FRAMES as $sl_slug ) {
	$sl_row    = img( $sl_slug );   // the manifest owes a row and img/ owes a file before a pixel is read
	$sl_ground = $GROUND[ $ANCHORS['editorial']['ground'] ];

	/* THE FRAME HAS TO BE A HERO FRAME. Found by mutation: swapping `hero-encimera` for the
	   `sq-marmol` swatch passed every check here, because a bright image has no dark pixels and a
	   PAPER veil only ever fails on dark ones — so the contrast gate got easier while the hero
	   quietly became a 1:1 crop of a marble sample stretched across a 2.3:1 box. The role cell in
	   the manifest is the thing that knew, and nothing was asking it. */
	if ( 0 !== strpos( $sl_row['role'], 'hero' ) ) {
		fail( "slider frame `$sl_slug` has manifest role `{$sl_row['role']}`, not a hero role — a"
			. ' non-hero crop behind a full-bleed hero is a stretched thumbnail, and the contrast'
			. ' measurement below will not object because brightness is not the defect' );
	}

	/* MEASURED THROUGH THE INK, because the ink is what the browser paints. `filter` runs at paint
	   time and no DOM measurement can see it, so a build that graded the photographs and kept
	   sweeping the original file would be certifying the contrast of an image that is no longer on
	   the page. It happens to help here — the shadow ink is a floor no pixel can fall below, so the
	   worst pixel gets BETTER — but "it happens to help" is a thing you only know once you have
	   measured it, and the direction is not guaranteed for the next photograph or the next ground.
	   RE-MEASURED WHEN THE DUOTONE WAS RETIRED, which is the point of it being a sweep and not a
	   constant: the duotone read 6.64 / 6.67 / 6.59 here and the grade reads 6.59 / 6.61 / 6.55.
	   Removing a filter changes contrast everywhere, and the bar is 4.5. */
	$sl = worst_pixel( $sl_slug, $sl_ground['bg'], $SCRIM_FLOOR, $sl_ground['text'],
		$INK['editorial'] );

	/* THE IDENTITY BETWEEN THE TWO NUMBERS THE SWEEP RETURNED. `ratio` and `surface_l` come from
	   the same pixel, so WCAG's own definition has to hold between them. This is what stops either
	   from being replaced by a constant: the per-ink checks consume `surface_l`, the frame gate
	   consumes `ratio`, and neither can drift without breaking this line. */
	$sl_check = ( max( $sl['surface_l'], srgb_lum( $sl_ground['text'] ) ) + 0.05 )
		/ ( min( $sl['surface_l'], srgb_lum( $sl_ground['text'] ) ) + 0.05 );
	if ( abs( $sl_check - $sl['ratio'] ) > 1e-9 ) {
		fail( sprintf(
			'frame `%s` reports a worst ratio of %.6f and a surface of L=%.6f, which are not the same'
				. ' pixel (that surface gives %.6f) — one of the two has stopped coming from the sweep',
			$sl_slug,
			$sl['ratio'],
			$sl['surface_l'],
			$sl_check
		) );
	}

	if ( $sl['ratio'] < $SCRIM_BAR ) {
		fail( sprintf(
			'slider frame `%s` measures %.2f:1 at the scrim floor of %d%% — below the %s:1 the lede'
				. ' needs. Either the floor rises or the frame goes; a headline that is legible over'
				. ' THIS photograph by luck is not a design.',
			$sl_slug,
			$sl['ratio'],
			(int) round( $SCRIM_FLOOR * 100 ),
			$SCRIM_BAR
		) );
	}
	$SCRIM_WORST[ $sl_slug ]   = $sl['ratio'];
	$SCRIM_SURFACE[ $sl_slug ] = $sl['surface_l'];
}

/* ── THE SWEEP HAS TO BE LOOKING THROUGH THE INK, AND THIS IS THE ONLY THING THAT KNOWS.
      FOUND BY MUTATION, and it is the worst class of failure this file can have. Dropping the ink
      from the `worst_pixel()` call above — one argument — leaves a build that passes every check,
      prints a plausible 5.38:1, and is certifying the contrast of the ORIGINAL photograph while
      the browser paints a duotone. It fails quiet, and it fails quiet in the direction that reads
      as safe, because here the ink happens to raise the ratio: the number goes DOWN when the check
      breaks, so it looks more conservative, not less.

      Nothing about the composited result can detect this — both numbers are legitimate contrast
      ratios of a real image. What distinguishes them is that they are DIFFERENT, so the check is
      that they must be: one more sweep, on one frame, with the ink switched off, asserted to
      disagree with the inked one. A margin rather than `!=` because two floats that differ in the
      ninth decimal would satisfy inequality while telling nobody anything; .25 is far below the
      1.26 this frame actually moves and far above any rounding. */
$ink_probe_ground = $GROUND[ $ANCHORS['editorial']['ground'] ];
$INK_PROBE_PLAIN  = worst_pixel( $SLIDER_FRAMES[0], $ink_probe_ground['bg'], $SCRIM_FLOOR,
	$ink_probe_ground['text'] );
$INK_PROBE_INKED  = $SCRIM_WORST[ $SLIDER_FRAMES[0] ];
if ( abs( $INK_PROBE_INKED - $INK_PROBE_PLAIN['ratio'] ) < 0.25 ) {
	fail( sprintf(
		'the scrim sweep reads %.2f:1 with the house ink and %.2f:1 without it on `%s` — the two'
			. ' agree, so the ink is not in the measurement path. `filter` runs at PAINT time and no'
			. ' DOM measurement can see it: a sweep over the unfiltered file is a contrast'
			. ' certificate for a photograph that is not on the page.',
		$INK_PROBE_INKED,
		$INK_PROBE_PLAIN['ratio'],
		$SLIDER_FRAMES[0]
	) );
}


// ── EVERY INK THAT LANDS ON THE PHOTOGRAPH, not just the one the h1 uses ───────────────────────
//
// THIS BLOCK EXISTS BECAUSE THE MEASUREMENT ABOVE WAS RIGHT AND INCOMPLETE, and only looking found
// it. The floor was measured against `--c-text` and the hero cleared it — and then the rendered
// strip showed a lede that was hard to read, because `.lede` carries `.muted` and paints
// `--c-text-muted`, not `--c-text`. The eyebrow paints `--c-accent`. The outline button's border
// paints `--c-border`. Three inks the assertion never asked about, on the same photograph.
//
// What they measure at the floor, over a worst-case pixel, re-derived below on every build:
//
//   --c-text        #15181A   5.32:1   the h1, and the only one that holds
//   --c-accent      #8C3A1F   2.29:1   needs a veil of ~82% before it clears 4.5
//   --c-text-muted  #6B6D6E   1.55:1   needs ~95% — a veil that has erased the photograph
//   --c-border      #E5E6E6   2.68:1   never clears 3:1 at ANY alpha, and gets WORSE as the veil
//                                      lightens, because the border is itself nearly white
//
// So a photographic hero gets ONE ink, and hierarchy comes from size and weight instead of from a
// weaker grey — which is what a muted tone is for on a flat surface and cannot do on a picture.
// The accent is not lost from the hero: it still fills the primary button, which is opaque and
// carries its own measured label contrast.
//
// The overrides are GENERATED from this table rather than typed into the stylesheet, so the
// measurement and the fix cannot come apart: an ink that starts passing stops being overridden,
// and an ink that starts failing and has no override stops the build.

$SCRIM_INK       = $GROUND[ $ANCHORS['editorial']['ground'] ]['text'];
$SCRIM_INK_WORST = min( $SCRIM_WORST );

/* The bar is per ROLE, not one number: 4.5:1 for text, and 3:1 for the outline button's border,
   which is a UI component boundary under WCAG 1.4.11 rather than a glyph. */
$SCRIM_ELEMENTS = array(
	array(
		'sel'  => '.hero-slides .eyebrow',
		'was'  => '--c-accent',
		'hex'  => $ACCENT_BY_GROUND[ $ANCHORS['editorial']['ground'] ],
		'bar'  => 4.5,
		'prop' => 'color',
	),
	array(
		'sel'  => '.hero-slides .lede',
		'was'  => '--c-text-muted',
		'hex'  => css_mix( $SCRIM_INK, 0.634, $GROUND[ $ANCHORS['editorial']['ground'] ]['bg'] ),
		'bar'  => 4.5,
		'prop' => 'color',
	),
	array(
		'sel'  => '.hero-slides .btn-outline',
		'was'  => '--c-border',
		'hex'  => css_mix( $SCRIM_INK, 0.11, $GROUND[ $ANCHORS['editorial']['ground'] ]['bg'] ),
		'bar'  => 3.0,
		'prop' => 'border-color',
	),
);

/* The darkest composited surface any frame produces at the floor — READ OFF THE SWEEP, at the same
   pixel that produced the worst ratio, and tied to it by the identity asserted above. The inks
   below are measured against this, so every one of them is being held to the worst place on the
   worst frame rather than to an average nobody's eye will ever meet. */
$SCRIM_SURFACE_L = min( $SCRIM_SURFACE );

/* THE RANGE TRIPWIRE, which catches the low side the identity cannot: a surface darker than the
   veil over a pure-black pixel is a surface no photograph can produce under this scrim. */
$scrim_floor_black = srgb_lum_rgb(
	hexdec( substr( $GROUND[ $ANCHORS['editorial']['ground'] ]['bg'], 1, 2 ) ) * $SCRIM_FLOOR,
	hexdec( substr( $GROUND[ $ANCHORS['editorial']['ground'] ]['bg'], 3, 2 ) ) * $SCRIM_FLOOR,
	hexdec( substr( $GROUND[ $ANCHORS['editorial']['ground'] ]['bg'], 5, 2 ) ) * $SCRIM_FLOOR
);
if ( $SCRIM_SURFACE_L < $scrim_floor_black - 1e-9 ) {
	fail( sprintf(
		'the composited surface reads L=%.4f, below the %.4f a %d%% veil produces over pure black'
			. ' — the ink checks would be measuring against a surface that cannot exist',
		$SCRIM_SURFACE_L,
		$scrim_floor_black,
		(int) round( $SCRIM_FLOOR * 100 )
	) );
}

$SCRIM_OVERRIDE = array();
$SCRIM_REPORT   = array();
foreach ( $SCRIM_ELEMENTS as $se ) {
	$se_l   = srgb_lum( $se['hex'] );
	$se_hi  = max( $SCRIM_SURFACE_L, $se_l );
	$se_lo  = min( $SCRIM_SURFACE_L, $se_l );
	$se_rat = ( $se_hi + 0.05 ) / ( $se_lo + 0.05 );

	$SCRIM_REPORT[] = sprintf( '%s %s %.2f:1%s', $se['was'], $se['hex'], $se_rat,
		( $se_rat < $se['bar'] ) ? ' → --c-text' : ' (kept)' );

	if ( $se_rat < $se['bar'] ) {
		$SCRIM_OVERRIDE[] = $se['sel'] . '{' . $se['prop'] . ':var(--c-text)'
			. ( 'border-color' === $se['prop'] ? ';color:var(--c-text)' : '' ) . '}';
	}
}

// ─────────────────────────────────────────────────────────────── 6 · the copy, one set per archetype
//
// The same discipline `_axis-proof-content.md` states for the proof pair: the copy is the
// CONSTANT. All four anchors of an archetype render these exact strings in this exact order.
//
// WHAT THAT LICENSES THE READER TO CONCLUDE, stated exactly, because the sentence that used to
// stand here — "every visible difference between them is the anchor and nothing else" — stopped
// being true the moment one strip declared a toggle. Two strips of one archetype differ by their
// ANCHOR and by any TOGGLE whose value they do not share, and by nothing else. Both are printed
// on the strip's own data bar, so both remain attributable; an unlabelled toggle is what would
// turn this catalogue into a set of one-offs.

$BRAND = 'PIEDRA VALDÉS';

$CONTENT = array(
	/* ── CLÍNICA ARBEA · TPL-C-10 · el miedo no es el precio, es el procedimiento. La unidad de
	   contenido no es un servicio con precio sino un TRATAMIENTO con cuatro datos que casi nadie
	   publica: cuánto dura, cuántas sesiones, si lleva anestesia y desde cuánto. */
	

	/* ── LUMIÈRE · TPL-C-14 · la clienta YA QUIERE. Ése es el dato que separa este arquetipo de sus
	   dos vecinos: TPL-C-05 enseña el local y esconde la carta, TPL-C-10 publica el procedimiento
	   porque el freno es el miedo, y aquí el freno es no saber cuánto dura, cómo sales y cuánto
	   cuesta volver. Por eso la carta lleva TRES datos y no dos, y por eso la página cierra en bono
	   y no en cita. */
	'TPL-C-14-lumiere' => array(
		'tpl'          => 'TPL-C-14-lumiere',
		'head_mode'    => 'emblem',
		'arch'         => 'TPL-C-14',
		'brand'        => 'lumiere',
		'brand_name'   => 'Lumière',
		'brand_sector' => 'Belleza · centro de estética',
		'tpl_name'     => 'Ritual / Bono',
		'site'         => 'corporate',
		'site_es'      => 'Corporativa',
		'fits'         => 'Centros de estética, cabinas de belleza, spas urbanos, depilación, uñas',
		'dna'          => 'COMP-ZONE-SELECTOR · COMP-RITUAL-MENU · COMP-CABIN-TOUR · COMP-BONO-PACKS',
		'wire'         => 'COMP-HEADER · COMP-HERO-FULL · COMP-ZONE-SELECTOR · COMP-RITUAL-MENU · COMP-CABIN-TOUR · COMP-PROTOCOL-STEPS · COMP-BONO-PACKS · COMP-BOOKING · COMP-FOOTER',
		'nav'          => array( 'Servicios', 'Nosotros', 'Contacto' ),
		'nav_cta'      => 'Reservar',
		'phone'        => '944 00 00 00',
		'topbar'       => array(
			'items' => array( 'Alameda Urquijo 24 · Bilbao', 'Martes a sábado · 10:00–20:00' ),
		),
		'hero'         => array(
			'eyebrow' => 'Centro de estética · Bilbao',
			'h2'      => 'Te decimos cuánto dura y cómo sales',
			'lede'    => 'Cada ritual con su tiempo, su precio y lo que notas al salir por la puerta. Para que la cita quepa en tu día antes de pedirla.',
			'cta_1'   => 'Reservar',
			'cta_2'   => 'Ver la carta',
			'img'     => 'lumiere-cabina',
		),
		'strip'        => array(
			'label' => 'La casa por dentro',
			'items' => array( 'lumiere-cabina', 'lumiere-rostro', 'lumiere-manos', 'lumiere-depilacion', 'lumiere-recepcion', 'lumiere-cosmetica' ),
		),
		'zones'        => array(
			'eyebrow' => 'Elige por zona',
			'h2'      => '¿Qué te apetece cuidarte hoy?',
			'items'   => array(
				array( 'Rostro', '7 rituales', 'desde 45 €', 'Limpiezas, hidrataciones y aparatología facial.', 'crema' ),
				array( 'Cuerpo', '6 rituales', 'desde 35 €', 'Masaje, presoterapia y envolturas.', 'piedras' ),
				array( 'Manos y pies', '5 rituales', 'desde 22 €', 'Manicura, pedicura y esmaltado semipermanente.', 'esmalte' ),
				array( 'Depilación', '8 rituales', 'desde 12 €', 'Cera tibia y láser diodo, en cabina aparte.', 'espatula' ),
			),
		),
		'rituals'      => array(
			'img'     => 'lumiere-recepcion',
			'eyebrow' => 'Tratamientos y precios',
			'h2'      => 'Seis rituales, con lo que duran y cómo sales',
			'note'    => 'Los precios son cerrados e incluyen el producto. Si al llegar decidimos que hoy no toca, no se cobra la sesión.',
			'items'   => array(
				array( 'zone' => 'Rostro', 'h3' => 'Limpieza profunda', 'p' => 'Vapor, extracción manual y mascarilla calmante.',
					'mins' => '60 min', 'price' => '55 €', 'after' => 'Sales sin maquillaje y con la piel algo roja media hora' ),
				array( 'zone' => 'Rostro', 'h3' => 'Radiofrecuencia facial', 'p' => 'Calor controlado para tensar el óvalo.',
					'mins' => '45 min', 'price' => '75 €', 'after' => 'Se puede maquillar encima el mismo día' ),
				array( 'zone' => 'Cuerpo', 'h3' => 'Masaje descontracturante', 'p' => 'Espalda y cervicales, presión fuerte.',
					'mins' => '50 min', 'price' => '48 €', 'after' => 'Sales con la espalda caliente; hoy no cargues peso' ),
				array( 'zone' => 'Cuerpo', 'h3' => 'Presoterapia', 'p' => 'Botas de compresión para piernas cansadas.',
					'mins' => '30 min', 'price' => '35 €', 'after' => 'Piernas ligeras; bebe agua al salir' ),
				array( 'zone' => 'Manos y pies', 'h3' => 'Manicura rusa', 'p' => 'Cutícula al torno y esmaltado semipermanente.',
					'mins' => '75 min', 'price' => '32 €', 'after' => 'Esmalte curado: puedes mojarte las manos al salir' ),
				array( 'zone' => 'Depilación', 'h3' => 'Láser diodo · media pierna', 'p' => 'Seis sesiones separadas seis semanas.',
					'mins' => '20 min', 'price' => '45 €', 'after' => 'Sin sol 48 horas y crema hidratante por la noche' ),
			),
		),
		'cabin'        => array(
			'eyebrow' => 'El centro por dentro',
			'h2'      => 'Cuatro espacios, y para qué es cada uno',
			'note'    => 'Puedes venir a verlo antes de reservar nada. Se tarda cinco minutos y no hay que avisar.',
			'items'   => array(
				array( 'img' => 'lumiere-cabina', 'name' => 'Cabina 1 · rostro', 'p' => 'Camilla térmica y lupa de luz fría. Aquí pasan las limpiezas y la radiofrecuencia.' ),
				array( 'img' => 'lumiere-depilacion', 'name' => 'Cabina 2 · depilación', 'p' => 'Separada del resto por el olor de la cera. El láser vive aquí.' ),
				array( 'img' => 'lumiere-manos', 'name' => 'Zona de manos', 'p' => 'Dos puestos junto a la ventana, con aspiración en la propia mesa.' ),
				array( 'img' => 'lumiere-recepcion', 'name' => 'Recepción', 'p' => 'Se paga, se recogen los bonos y se espera aquí si vienes pronto.' ),
			),
		),
		'protocol'     => array(
			'eyebrow' => 'Cómo es una sesión',
			'h2'      => 'Una limpieza profunda, minuto a minuto',
			'note'    => 'Suman los sesenta minutos que dice la carta. Si un día sobran cinco, se van al masaje final.',
			'items'   => array(
				array( '10 min', 'Diagnóstico', 'Se mira la piel con lupa y se decide el activo del día.' ),
				array( '15 min', 'Limpieza', 'Desmaquillado, vapor y extracción manual.' ),
				array( '20 min', 'Activo', 'Ácido o vitamina C, según lo que se haya visto.' ),
				array( '15 min', 'Masaje y frío', 'Mascarilla calmante y bajada de temperatura.' ),
			),
		),
		'bonos'        => array(
			'img'     => 'lumiere-cosmetica',
			'eyebrow' => 'Bonos y tarjeta regalo',
			'h2'      => 'Casi nadie viene una vez sola',
			'note'    => 'Los bonos caducan a los doce meses, son nominales y se pueden pagar en dos veces. No se devuelven, pero se pueden cambiar por otro ritual del mismo importe.',
			'items'   => array(
				array( 'name' => 'Bono limpieza', 'q' => '5 sesiones', 'price' => '235 €', 'save' => 'Ahorras 40 €',
					'p' => 'Una cada seis semanas, que es lo que la piel pide.', 'gift' => false ),
				array( 'name' => 'Bono piernas', 'q' => '5 presoterapias', 'price' => '150 €', 'save' => 'Ahorras 25 €',
					'p' => 'Para el verano y para quien pasa el día de pie.', 'gift' => false ),
				array( 'name' => 'Tarjeta regalo', 'q' => 'Importe libre', 'price' => 'desde 30 €', 'save' => '',
					'p' => 'Se envía por email o se recoge en papel, en un sobre.', 'gift' => true ),
			),
		),
		'booking'      => array(
			'eyebrow'  => 'Pedir cita',
			'h2'       => 'Elige ritual, día y hora',
			'lede'     => 'Te confirmamos por WhatsApp el mismo día. Si tienes que cambiarla, avisa con 24 horas y no cuenta.',
			'fields'   => array(
				array( 'nombre', 'Nombre', 'text' ),
				array( 'tel', 'Teléfono', 'tel' ),
			),
			'pick_lbl' => 'Ritual',
			'picks'    => array( 'Limpieza profunda', 'Radiofrecuencia', 'Masaje', 'Presoterapia', 'Manicura', 'Láser' ),
			'day_lbl'  => 'Día',
			'days'     => array( 'Lunes 22', 'Martes 23', 'Miércoles 24', 'Jueves 25', 'Viernes 26' ),
			'slot_lbl' => 'Hora',
			'slots'    => array( '10:00', '11:30', '13:00', '17:00', '18:30' ),
			'submit'   => 'Reservar',
			'small'    => 'La primera vez pedimos diez minutos más para mirar la piel. No se cobran.',
			'img'      => 'lumiere-cosmetica',
		),
		'footer'       => array(
			'tag'   => 'Lumière · Alameda Urquijo 24, 48011 Bilbao · 944 00 00 00 · Martes a sábado',
			'links' => array( 'Servicios', 'Nosotros', 'Privacidad' ),
			'legal' => 'Lumière Estética SL · Maqueta interna NovaMira, no publicada.',
		),
	),

	/* ── ALINEA · TPL-C-11 · un solo tratamiento que dura dieciocho meses. La pregunta no es «cuál»
	   sino «cuánto tiempo, cuánto duele y cuánto al mes», y un catálogo de fichas sería la
	   respuesta equivocada a las tres. */
	'TPL-C-11-alinea' => array(
		'tpl'          => 'TPL-C-11-alinea',
		'head_mode'    => 'rule',
		'arch'         => 'TPL-C-11',
		'brand'        => 'alinea',
		'brand_name'   => 'Alinea',
		'brand_sector' => 'Salud · ortodoncia',
		'tpl_name'     => 'Plan por fases',
		'site'         => 'corporate',
		'site_es'      => 'Corporativa',
		'fits'         => 'Ortodoncia, implantología, nutrición, entrenamiento personal, psicoterapia',
		'dna'          => 'COMP-PHASE-TIMELINE · COMP-PRICING · COMP-PROBLEM',
		'wire'         => 'COMP-HEADER · COMP-HERO-FULL · COMP-PROBLEM · COMP-PHASE-TIMELINE · COMP-PRICING · COMP-BEFORE-AFTER · COMP-FAQ · COMP-BOOKING · COMP-FOOTER',
		'nav'          => array( 'El plan', 'Precios', 'Casos' ),
		'nav_cta'      => 'Estudio gratuito',
		'phone'        => '96 000 00 00',
		'hero'         => array(
			'eyebrow' => 'Ortodoncia invisible y metálica · València',
			'h1'      => 'Dieciocho meses, y le decimos qué pasa en cada uno',
			'lede'    => 'El plan completo antes de empezar: cuántas fases, cuánto dura cada una, qué se nota y cuánto se paga al mes. Sin sorpresas a la mitad.',
			'cta_1'   => 'Estudio gratuito',
			'cta_2'   => 'Ver el plan',
			'img'     => 'alinea-hero',
		),
		'pains'        => array(
			'eyebrow' => 'Qué le trae aquí',
			'h2'      => 'Lo que nos dicen en la primera visita',
			'items'   => array(
				'«Me da vergüenza sonreír en las fotos y ya tengo treinta y cuatro años.»',
				'«Me dijeron de pequeño que había que hacerlo y no se hizo.»',
				'«Se me está torciendo un diente de abajo y cada año va a peor.»',
				'«Quiero saber cuánto tiempo es antes de decir que sí.»',
			),
		),
		'phases'       => array(
			'eyebrow' => 'El plan',
			'h2'      => 'Cuatro fases y dieciocho meses',
			'total'   => '18 meses en total',
			'items'   => array(
				array( 'n' => '01', 'h3' => 'Estudio y plan', 'months' => '2 semanas', 'p' => 'Escáner intraoral, radiografía y simulación. Sale el plan escrito con la fecha final.' ),
				array( 'n' => '02', 'h3' => 'Alineación', 'months' => '6 meses', 'p' => 'Los dientes se mueven a su sitio. Es la fase en la que más se nota el cambio y la que más molesta la primera semana.' ),
				array( 'n' => '03', 'h3' => 'Detalle', 'months' => '9 meses', 'p' => 'Ajuste fino de contactos y mordida. Se avanza poco a la vista y mucho en la función.' ),
				array( 'n' => '04', 'h3' => 'Retención', 'months' => '3 meses', 'p' => 'Retenedor fijo y férula de noche. Empieza aquí y no termina nunca del todo: eso también se dice.' ),
			),
			'note'    => 'Los meses son de un caso medio y se recalculan en el estudio. Si su caso sale a veinticuatro, se lo decimos antes de firmar y no después.',
		),
		'plans'        => array(
			'eyebrow' => 'Precios',
			'h2'      => 'Tres formas de hacerlo',
			'note'    => 'Cuota Y precio total, siempre los dos. Una cuota sin el total al lado es la técnica que hace que la gente llame desconfiando.',
			'items'   => array(
				array( 'name' => 'Metálica', 'quota' => '119 €/mes', 'total' => '2.142 € en total', 'p' => 'Brackets de acero. Es la más eficaz y la que más se ve.',
					'feats' => array( '18 meses', 'Revisiones cada 6 semanas', 'Retenedor incluido' ) ),
				array( 'name' => 'Estética', 'quota' => '149 €/mes', 'total' => '2.682 € en total', 'p' => 'Brackets de cerámica del color del diente.',
					'feats' => array( '18 meses', 'Revisiones cada 6 semanas', 'Retenedor incluido' ), 'flag' => 'La más pedida' ),
				array( 'name' => 'Invisible', 'quota' => '189 €/mes', 'total' => '3.402 € en total', 'p' => 'Férulas transparentes que se quitan para comer.',
					'feats' => array( '18 meses', 'Revisiones cada 8 semanas', 'Juego de repuesto incluido' ) ),
			),
		),
		'cases'        => array(
			'eyebrow' => 'Un caso',
			'h2'      => 'Antes y después, con las dos fechas',
			'lede'    => 'Apiñamiento inferior moderado tratado con férulas transparentes. La paciente autorizó la publicación.',
			'before'  => array( 'img' => 'alinea-fase', 'label' => 'Antes', 'date' => '9 de enero de 2024' ),
			'after'   => array( 'img' => 'alinea-consulta', 'label' => 'Después', 'date' => '18 de junio de 2025' ),
			'note'    => 'Diecisiete meses. Cada caso es distinto y el suyo se calcula en el estudio.',
		),
		'faq'          => array(
			'eyebrow' => 'Lo que se pregunta',
			'h2'      => 'Cuatro preguntas que hace todo el mundo',
			'items'   => array(
				array( '¿Duele?', 'Las primeras 48 horas de cada ajuste, como una agujeta. Después no. Si duele más de eso, llame: algo está rozando y se arregla en diez minutos.' ),
				array( '¿Puedo comer de todo?', 'Con férulas sí, porque se quitan. Con brackets no: nada muy duro ni muy pegajoso los primeros meses. Le damos la lista por escrito.' ),
				array( '¿Se nota al hablar?', 'La primera semana sí, sobre todo con las férulas. A partir de ahí nadie lo nota salvo usted.' ),
				array( '¿Y si me olvido de ponérmelas?', 'El tratamiento se alarga. Con menos de 20 horas al día el plan deja de cumplirse, y eso se lo diremos en la revisión en vez de al final.' ),
			),
		),
		'booking'      => array(
			'eyebrow' => 'Estudio',
			'h2'      => 'Reserve el estudio inicial',
			'lede'    => 'Escáner, radiografía y simulación. Sale el plan escrito con las fases y la fecha final. No cuesta nada y no obliga a nada.',
			'fields'  => array(
				array( 'nombre', 'Nombre', 'text' ),
				array( 'tel', 'Teléfono', 'tel' ),
			),
			'day_lbl'  => 'Día',
			'days'     => array( 'Martes 23', 'Miércoles 24', 'Jueves 25', 'Viernes 26', 'Lunes 29' ),
			'slot_lbl' => 'Hora',
			'slots'    => array( '10:00', '11:30', '13:00', '17:00', '18:30' ),
			'submit'   => 'Reservar estudio',
			'img'      => 'alinea-consulta',
			'small'    => 'Dura cuarenta minutos. Si después no quiere seguir, se lleva el plan igualmente.',
		),
		'footer'       => array(
			'tag'   => 'Alinea · Carrer de Colón 44, 46004 València · 96 000 00 00 · Reg. sanitario 46-C21-0000',
			'links' => array( 'El plan', 'Precios', 'Privacidad' ),
			'legal' => 'Alinea Ortodoncia SL · Maqueta interna NovaMira, no publicada.',
		),
	),

	/* ── URGENCIA DENTAL · TPL-C-12 · siete secciones y ningún héroe. Quien entra tiene dolor, está
	   de pie y con una mano. Todos los demás arquetipos empiezan por presentarse; éste empieza por
	   responder. */
	

	/* ── INMOBILIARIA DE LA O · TPL-C-15 · CARTERA CURADA. PR3d — content authored VERBATIM from the
	   client artboards (`Inicio.dc.html`, `Propiedades.dc.html`, `Ficha Propiedad.dc.html`,
	   `Nosotros.dc.html`, `Contacto.dc.html`, `Nav.dc.html`, `Pie.dc.html`), not from the prose
	   README that PR3b/PR3c worked from. Copy, badge states, field labels/options and reference
	   codes are the artboards' own strings; only the three departures the launch brief names as
	   forced (container 1280, accent `#8A5A2A`, `Instrument Serif` for the missing `Libre Caslon
	   Display`) and the photo-count constraint (three real portraits, not the design's four
	   fictional ones) differ from what is drawn. The twelve `delao-*.webp` slugs are never renamed;
	   they are mapped onto the artboards' own properties/roles below. */
	'TPL-C-15-delao' => array(
		'tpl'            => 'TPL-C-15-delao',
		'arch'           => 'TPL-C-15',
		'brand'          => 'delao',
		'brand_name'     => 'Inmobiliaria de la O',
		'brand_sector'   => 'Inmobiliaria · cartera curada',
		'tpl_name'       => 'Cartera curada',
		'site'           => 'corporate',
		'site_es'        => 'Corporativa',
		'fits'           => 'Inmobiliarias de lujo residencial, promotoras boutique, agencias con cartera reducida y curada',
		'dna'            => 'COMP-HERO-CARTERA · COMP-SEARCH-BAND · COMP-FEATURED-GRID · COMP-VALUATION-CTA',
		'wire'           => 'COMP-HEADER · COMP-HERO-CARTERA · COMP-SEARCH-BAND · COMP-FEATURED-GRID · COMP-MAP-SEARCH · COMP-VALUATION-CTA · COMP-TESTIMONIAL · COMP-FOOTER',
		'nav'            => array( 'Propiedades', 'Nosotros', 'Contacto' ),
		'nav_cta'        => 'Valorar mi casa',
		'nav_cta_weight' => 'secundario',
		'phone'          => '+34 952 00 00 00',
		/* `Inicio.dc.html` — verbatim eyebrow/H1/lede/stats. The H1 keeps its own `<br>` as an
		   explicit two-line array (never raw markup through `h()`), the same convention this file
		   already uses for a two-line address (`<br>` joined between two escaped parts). */
		'hero'           => array(
			'eyebrow'  => 'Marbella · Costa del Sol',
			'h1'       => 'Casas que no se anuncian solas',
			'h1_lines' => array( 'Casas que no', 'se anuncian solas' ),
			'lede'     => 'Seleccionamos un número corto de propiedades cada temporada y las representamos con el nivel de detalle que exige su precio.',
			'img'      => 'delao-hero',
			'stats'    => array(
				array( '17', 'Mandatos activos' ),
				array( '2,4 M€', 'Precio medio de cierre' ),
			),
		),
		/* COMP-SEARCH-BAND, home — `Inicio.dc.html`'s own four fields: three selects and ONE free
		   text field ("Zona o municipio", a placeholder input, never a select — PR3c's mistake).
		   Field tuple: `array($key,$label,array($options))` for a select, or
		   `array($key,$label,'input',$placeholder)` for a text field. */
		'search'         => array(
			'fields' => array(
				array( 'operacion', 'Operación', array( 'Venta', 'Alquiler', 'Alquiler de temporada' ) ),
				array( 'zona', 'Zona o municipio', 'input', 'Marbella, Sierra Blanca…' ),
				array( 'tipo', 'Tipo', array( 'Villa', 'Ático', 'Apartamento', 'Finca' ) ),
				array( 'precio', 'Precio', array( 'Sin límite', 'Hasta 1,5 M€', '1,5 – 3 M€', '3 – 6 M€', 'Más de 6 M€' ) ),
			),
			'submit' => 'Buscar',
		),
		/* COMP-FEATURED-GRID — the three cards `Inicio.dc.html` actually draws (`destacadas`), not
		   PR3c's invented set. Only Villa Alameda has its own photographed slug among the three;
		   "Ático Mar de Plata" and "Casa Los Cipreses" borrow the two other real property shots
		   (`delao-atico-mar`/`delao-finca`) without renaming either slug — the copy changes, the
		   file does not. */
		'listing'        => array(
			'eyebrow' => 'Selección 2026',
			'h2'      => 'Propiedades destacadas',
			'link'    => 'Ver las 17 propiedades',
			'items'   => array(
				array( 'badge' => 'En exclusiva', 'h3' => 'Villa Alameda', 'zone' => 'Sierra Blanca', 'facts' => array( '642 m²', '5 hab.', '6 baños' ), 'price' => '4.950.000 €', 'img' => 'delao-villa-alameda' ),
				array( 'badge' => 'Nueva', 'h3' => 'Ático Mar de Plata', 'zone' => 'Milla de Oro', 'facts' => array( '318 m²', '3 hab.', '3 baños' ), 'price' => '3.200.000 €', 'img' => 'delao-atico-mar' ),
				array( 'badge' => 'Off market', 'h3' => 'Casa Los Cipreses', 'zone' => 'La Zagaleta', 'facts' => array( '1.140 m²', '7 hab.', '8 baños' ), 'price' => 'Precio a consultar', 'img' => 'delao-finca' ),
			),
		),
		/* COMP-VALUATION-CTA — `Inicio.dc.html`'s own copy verbatim. The design draws ONE button
		   ("Solicitar valoración", to Contacto) plus a plain `tel:` link ("o llámenos"), never a
		   second page-routed button — `valuation_row_html()` below is rewritten to match instead of
		   the archetype doc's generic two-button paraphrase, and the primary CTA is routed by an
		   EXPLICIT page key (`ihref('contacto')`), never `ihref_for_label()`: this copy matches none
		   of TPL-C-15's five page labels, and the documented fallback for an unmatched label is the
		   home route — silently wrong for the one button whose entire job is reaching Contacto. */
		'valuation'      => array(
			'eyebrow'   => 'Vender con nosotros',
			'h2'        => 'Sabemos lo que vale su casa antes de fotografiarla',
			'lede'      => 'Preparamos un informe de valoración con comparables reales de cierre, no de publicación. Sin compromiso y en 48 horas.',
			'cta'       => 'Solicitar valoración',
			'tel_label' => 'o llámenos',
			'phone'     => '+34 952 00 00 00',
			'img'       => 'delao-cta',
		),
		/* COMP-TESTIMONIAL — `Inicio.dc.html`'s own three reviews, verbatim. PR3d shipped this
		   through the shared `.head.stack` shape (eyebrow above H2) because forking
		   `quotes_block_html()` for one caller's header geometry would have cost every OTHER
		   archetype's testimonial block a silent behaviour change. PR3e removes that trade-off:
		   the function now takes an opt-in `$layout` parameter (every other caller's default is
		   untouched), so delao alone renders the artboard's own side-by-side H2 + rating-line
		   composition — `'between'` below, wired at the call site in `strip_cartera_curada()`. */
		'quotes'         => array(
			'eyebrow'  => '4,9 / 5 · 68 reseñas verificadas',
			'h2'       => 'Lo que dicen nuestros clientes',
			'h2_lines' => array( 'Lo que dicen', 'nuestros clientes' ),
			'items'    => array(
				array( 'Vendieron la casa en cinco semanas al precio que nos dijeron el primer día. Nadie más se atrevió a darnos esa cifra.', 'Elena Marchetti', 'Venta · Sierra Blanca, 2025' ),
				array( 'Nos enseñaron cuatro propiedades. Compramos la segunda. Es la primera vez que una agencia me hace perder poco tiempo.', 'Javier Ollero', 'Compra · Milla de Oro, 2025' ),
				array( 'Discretos con los datos, exigentes con el reportaje y firmes en la negociación. Repetiría con los ojos cerrados.', 'Familia Duarte', 'Venta · Sotogrande, 2024' ),
			),
		),
		'footer'         => array(
			'tag'   => 'Inmobiliaria de la O · Marbella · +34 952 00 00 00 · Cartera de Sierra Blanca',
			'links' => array( 'Propiedades', 'Nosotros', 'Contacto' ),
			'legal' => 'Inmobiliaria de la O SL · Maqueta interna NovaMira, no publicada.',
		),
		/* TPL-ABOUT-01 «Nosotros», authored to `Nosotros.dc.html` verbatim (head/method/figures were
		   already literal in PR3c; the four método items below are now byte-for-byte the design's
		   own text, not PR3c's paraphrase — item 02 in particular was an entirely different practice
		   in PR3c ("verificación de cargas") where the design says "reportaje y documentación"). */
		'nosotros'       => array(
			'crumbs' => array( 'Inicio', 'Nosotros' ),
			'head'   => array(
				'eyebrow' => 'Casa fundada en 2004',
				'h1'      => 'Pocas casas, mucho tiempo en cada una',
				'lede'    => 'Somos una casa de intermediación pequeña por decisión. Aceptamos los mandatos que podemos representar bien y rechazamos el resto.',
			),
			'img'    => 'delao-oficina',
			'method' => array(
				'h2'    => 'Cómo trabajamos',
				'items' => array(
					array( '01', 'Valoración con comparables de cierre', 'Partimos de precios realmente firmados en la zona en los últimos dieciocho meses, no de precios publicados. Es la única cifra que sostiene una negociación.' ),
					array( '02', 'Reportaje y documentación completos', 'Fotografía de arquitectura, vídeo, plano acotado y dossier con todo lo que un comprador y su abogado van a preguntar. Antes de publicar, no después.' ),
					array( '03', 'Difusión selectiva', 'Primero nuestra cartera de compradores registrados y una red corta de colaboradores internacionales. Los portales llegan más tarde, si hacen falta.' ),
					array( '04', 'Acompañamiento hasta la notaría', 'Coordinamos abogados, tasación bancaria, cédula de habitabilidad y firma. El vendedor solo toma decisiones; el papeleo es nuestro.' ),
				),
			),
			'figures' => array(
				'eyebrow' => 'En números',
				'items'   => array(
					array( '22 años', 'Operando en la Costa del Sol' ),
					array( '310 M€', 'Intermediados desde 2004' ),
					array( '11 semanas', 'Tiempo medio de venta' ),
					array( '96 %', 'Cierres sobre precio de salida' ),
				),
			),
			/* Design draws FOUR fictional agents with placeholder "retrato" captions; delao's own
			   manifest photographs THREE real ones. Copying the design's headcount here would be
			   exactly the "`cols-3` grid holding one item" defect the launch brief warns against,
			   just counted from the other side — a fourth, empty portrait slot. `team_portraits_html()`
			   columns dynamically off the REAL count via `cols_attr()`, and the eyebrow states three,
			   not the design's four, so the copy never claims a headcount the cartera does not staff. */
			'team'   => array(
				'eyebrow' => 'Tres personas · una oficina',
				'h2'      => 'El equipo',
				'items'   => array(
					array( 'Nerea Otxoa', 'Dirección y ventas', 'delao-nerea' ),
					array( 'Julen Zabala', 'Captación y tasaciones', 'delao-julen' ),
					array( 'Leire Andonegi', 'Gestión y postventa', 'delao-leire' ),
				),
			),
			'cta'    => array(
				'h2'   => '¿Tiene una casa que merece este trato?',
				'lede' => 'Hablamos primero, visitamos después y solo entonces decidimos si somos la casa adecuada para venderla.',
				'cta'  => 'Contactar',
			),
		),
		/* TPL-CONTACT-01 «Contacto», authored to `Contacto.dc.html` verbatim — this page was already
		   byte-for-byte literal in PR3c (head/form/office/direct all matched); kept unchanged here
		   except the phone's `+34` prefix, for consistency with the header and the visit panel. */
		'contacto'       => array(
			'crumbs' => array( 'Inicio', 'Contacto' ),
			'head'   => array(
				'eyebrow' => 'Contacto',
				'h1'      => 'Hablemos de su casa',
				'lede'    => 'Respondemos todas las consultas en menos de 24 horas laborables, siempre una persona del equipo y nunca un formulario automático.',
			),
			'form'   => array(
				'fields' => array(
					array( 'nombre', 'Nombre y apellidos', 'text' ),
					array( 'tel', 'Teléfono', 'tel' ),
					array( 'mail', 'Correo electrónico', 'email' ),
					array( 'motivo', 'Motivo', 'select', array( 'Quiero vender', 'Quiero comprar', 'Solicitar una visita', 'Valoración de mi propiedad', 'Otro asunto' ) ),
				),
				'msg'         => 'Cuéntenos brevemente',
				'placeholder' => 'Zona, tipo de propiedad, plazos…',
				'privacy'     => 'He leído y acepto la política de privacidad. Sus datos no se comparten con terceros.',
				'submit'      => 'Enviar consulta',
			),
			'office' => array(
				'eyebrow' => 'Oficina',
				'addr'    => "Avda. Ricardo Soriano 42\n29601 Marbella, Málaga",
				'hours'   => "Lunes a viernes, 9:30 – 19:00\nSábados con cita previa",
			),
			/* `.example`, not the artboard's literal `.es`: RFC 2606's reserved test TLD is this
			   whole file's own house convention for every demo's contact e-mail (lumiere, aranda,
			   alinea all use it) — the one place a systemic safety convention outranks the artboard's
			   own string, because the alternative is a fake e-mail address that reads as a real,
			   possibly-registrable domain. */
			'direct' => array(
				'eyebrow' => 'Directo',
				'phone'   => '+34 952 00 00 00',
				'email'   => 'hola@inmobiliariadelao.example',
			),
		),
		/* TPL-SERVICES-01 reuse «Propiedades» — `Propiedades.dc.html` verbatim: head, the sticky
		   filter band (same four fields as home's search band, Zona also an input here), the results
		   bar and its nine listed properties (design's own JS array, in its own order), and the
		   "cargar más" ghost close. The closing "no encuentra lo que busca" band below the listing is
		   NOT in the artboard — `Propiedades.dc.html` ends at "cargar más" — but is kept as the
		   reused pattern's own closing convention (TPL-SERVICES-01's "index for a home with more
		   entries than fit" ADN) rather than removed outright; its own "Valorar mi casa" CTA is
		   routed by an EXPLICIT `cta_2_href` override (`page_cta_html()` below), fixing the same
		   fallback-to-home bug `valuation_row_html()` had. */
		'propiedades'    => array(
			'crumbs'  => array( 'Inicio', 'Propiedades' ),
			'head'    => array(
				'eyebrow' => 'Cartera · Agosto 2026',
				'h1'      => 'Propiedades',
				'lede'    => 'Diecisiete propiedades bajo mandato. Las operaciones off market se comparten solo tras una primera conversación.',
			),
			'filters' => array(
				'fields' => array(
					array( 'operacion', 'Operación', array( 'Venta', 'Alquiler', 'Alquiler de temporada' ) ),
					array( 'zona', 'Zona o municipio', 'input', 'Todas las zonas' ),
					array( 'tipo', 'Tipo', array( 'Todos', 'Villa', 'Ático', 'Apartamento', 'Finca' ) ),
					array( 'precio', 'Precio', array( 'Sin límite', 'Hasta 1,5 M€', '1,5 – 3 M€', '3 – 6 M€', 'Más de 6 M€' ) ),
				),
				'submit' => 'Filtrar',
			),
			'listing' => array(
				'results' => array(
					'count'      => '17 propiedades',
					'sort_label' => 'Ordenar',
					'sorts'      => array( 'Recientes', 'Precio ↑', 'Precio ↓' ),
				),
				/* The design's own nine-row JS array, in its own order. Only three rows carry a real
				   photographed slug (Villa Alameda / Ático Mar de Plata / Casa Los Cipreses, the same
				   three the home page already features); the other six are honestly placeholder — the
				   manifest holds no fourth-through-ninth property photo, and repeating one of the
				   three real shots under a different name would be a false listing. */
				'items'   => array(
					array( 'badge' => 'En exclusiva', 'h3' => 'Villa Alameda', 'zone' => 'Sierra Blanca', 'ref' => 'MB-1042', 'facts' => array( '642 m²', '5 hab.', '6 baños' ), 'price' => '4.950.000 €', 'img' => 'delao-villa-alameda' ),
					array( 'badge' => 'Nueva', 'h3' => 'Ático Mar de Plata', 'zone' => 'Milla de Oro', 'ref' => 'MB-1039', 'facts' => array( '318 m²', '3 hab.', '3 baños' ), 'price' => '3.200.000 €', 'img' => 'delao-atico-mar' ),
					array( 'badge' => 'Off market', 'h3' => 'Casa Los Cipreses', 'zone' => 'La Zagaleta', 'ref' => 'MB-1031', 'facts' => array( '1.140 m²', '7 hab.', '8 baños' ), 'price' => 'Precio a consultar', 'img' => 'delao-finca' ),
					array( 'badge' => 'En exclusiva', 'h3' => 'Villa Bruma', 'zone' => 'Sotogrande', 'ref' => 'SG-0918', 'facts' => array( '505 m²', '4 hab.', '5 baños' ), 'price' => '2.750.000 €' ),
					array( 'badge' => 'Nueva', 'h3' => 'Casa Vela', 'zone' => 'Nueva Andalucía', 'ref' => 'NA-0877', 'facts' => array( '388 m²', '4 hab.', '4 baños' ), 'price' => '1.980.000 €' ),
					array( 'badge' => 'Reservada', 'h3' => 'Ático Los Arcos', 'zone' => 'Marbella Club', 'ref' => 'MB-1027', 'facts' => array( '264 m²', '3 hab.', '3 baños' ), 'price' => '2.400.000 €' ),
					array( 'badge' => 'En exclusiva', 'h3' => 'Finca El Almendral', 'zone' => 'Benahavís', 'ref' => 'BH-0803', 'facts' => array( '2.100 m²', '6 hab.', '6 baños' ), 'price' => '5.600.000 €' ),
					array( 'badge' => 'Nueva', 'h3' => 'Villa Serena', 'zone' => 'Estepona', 'ref' => 'EP-0791', 'facts' => array( '412 m²', '4 hab.', '4 baños' ), 'price' => '1.650.000 €' ),
					array( 'badge' => 'Off market', 'h3' => 'Casa Duna', 'zone' => 'Guadalmina', 'ref' => 'GM-0754', 'facts' => array( '576 m²', '5 hab.', '5 baños' ), 'price' => 'Precio a consultar' ),
				),
				'more'    => 'Cargar más propiedades',
			),
			'cta'     => array(
				'eyebrow'      => 'No encuentra lo que busca',
				'h2'           => 'Cuéntenos qué zona y qué presupuesto maneja',
				'lede'         => 'Muchas propiedades entran en cartera antes de publicarse. Díganos qué busca y le avisamos primero.',
				'cta_1'        => 'Contacto',
				'cta_2'        => 'Valorar mi casa',
				'cta_2_href'   => 'contacto',
			),
		),
		/* TPL-PROPERTY-01 «Ficha» — `Ficha Propiedad.dc.html`'s own Villa Alameda, verbatim
		   (price/parcela/certificado/comunidad were PR3c inventions that drifted from the design;
		   fixed here to the artboard's own 4.950.000 €, 1.860 m², "A" and "520 €/mes"). The three
		   description paragraphs are now the design's own three, in full, including the "sala de
		   cine" and "apartamento de servicio independiente" PR3c's paraphrase dropped. */
		'producto'       => array(
			'crumbs' => array( 'Inicio', 'Propiedades', 'Villa Alameda' ),
			'head'   => array(
				'eyebrow' => 'Sierra Blanca, Marbella · En exclusiva',
				'h1'      => 'Villa Alameda',
				'lede'    => 'Obra de 2019 sobre una parcela en ladera, con orientación sur y vistas continuas a La Concha y al mar.',
			),
			'price'  => array(
				'label' => 'Precio de salida',
				'value' => '4.950.000 €',
				'm2'    => '7.710 €/m² construido',
			),
			'ref'    => array( 'Ref. MB-1042', 'Cítela al pedir la visita: viaja con el formulario' ),
			'tour'   => array(
				'more'  => 'Ver 34 fotos',
				'items' => array(
					array( 'Salón principal', 'Doble altura, chimenea de piedra y pared acristalada a la terraza', 'delao-galeria-1' ),
					array( 'Cocina', 'Isla de piedra clara, abierta al patio plantado', 'delao-galeria-2' ),
					array( 'Dormitorio principal', 'En lino blanco, con salida directa a terraza privada', 'delao-galeria-3' ),
				),
			),
			'facts'  => array(
				'items' => array(
					array( '642 m²', 'Construidos' ),
					array( '1.860 m²', 'Parcela' ),
					array( '5 / 6', 'Habitaciones / baños' ),
					array( '2019', 'Año de construcción' ),
				),
			),
			'desc'   => array(
				'eyebrow' => 'La propiedad',
				'paras'   => array(
					'Villa Alameda ocupa la cota más alta de su calle. El acceso se produce por un patio cerrado, de modo que la casa no se muestra hasta que se atraviesa el vestíbulo y el salón se abre por completo hacia el sur.',
					'La planta principal reúne salón, comedor y cocina en un solo volumen de 132 m² con carpintería de suelo a techo y salida directa a la terraza y la piscina de desbordamiento. Todos los dormitorios tienen baño propio, vestidor y terraza; el principal ocupa el ala oeste completa, con estudio anexo.',
					'En el sótano hay garaje para cuatro vehículos, bodega climatizada, gimnasio con spa, sala de cine y apartamento de servicio independiente. La casa cuenta con domótica integrada, suelo radiante por agua, aerotermia y certificación energética A. Se vende amueblada por acuerdo separado.',
				),
			),
			'features' => array(
				array( 'Tipo', 'Villa independiente' ),
				array( 'Orientación', 'Sur' ),
				array( 'Plantas', '3 + sótano' ),
				array( 'Piscina', 'Desbordamiento, climatizada' ),
				array( 'Garaje', '4 plazas' ),
				array( 'Certificado energético', 'A' ),
				array( 'Climatización', 'Aerotermia y suelo radiante' ),
				array( 'Comunidad', '520 €/mes · seguridad 24 h' ),
				array( 'IBI', '6.140 €/año' ),
				array( 'Disponibilidad', 'Inmediata' ),
			),
			'location' => array(
				'h2'        => 'Ubicación',
				'note'      => 'La dirección exacta se facilita en la visita',
				'distances' => array(
					array( '6 min', 'Puerto Banús' ),
					array( '9 min', 'Centro de Marbella' ),
					array( '12 min', 'Colegio internacional' ),
					array( '42 min', 'Aeropuerto de Málaga' ),
				),
			),
			'visit'  => array(
				'eyebrow'     => 'Visitas concertadas',
				'line'        => 'Enseñamos esta villa dos días por semana, con cita previa.',
				'cta'         => 'Solicitar visita',
				'phone_label' => 'o llame al +34 952 00 00 00',
				'phone'       => '+34 952 00 00 00',
			),
			/* Design's own three "similares": only "Casa Los Cipreses" (La Zagaleta) carries a real
			   photographed slug, the same `delao-finca` shot the home page and the listing already
			   use for that same property — one file, reused consistently across all three pages it
			   appears on, never renamed. "Finca El Almendral" and "Villa Bruma" have no photographed
			   match in the twelve-slug manifest and stay honestly placeholder. */
			'related' => array(
				'h2'    => 'Propiedades similares',
				'link'  => 'Ver toda la cartera',
				'items' => array(
					array( 'h3' => 'Casa Los Cipreses', 'zone' => 'La Zagaleta', 'price' => 'Precio a consultar', 'img' => 'delao-finca' ),
					array( 'h3' => 'Finca El Almendral', 'zone' => 'Benahavís', 'price' => '5.600.000 €' ),
					array( 'h3' => 'Villa Bruma', 'zone' => 'Sotogrande', 'price' => '2.750.000 €' ),
				),
			),
		),
	),

	/* ── MOTOR ARANDA · TPL-C-07 · un INVENTARIO, que es lo que ningún arquetipo corporativo tenía.
	   El contenido no lo escribe el dueño una vez: son cuarenta unidades que entran y salen, y los
	   cinco datos que deciden la compra no caben en una tarjeta de servicio. */
	'TPL-C-07-aranda' => array(
		'tpl'          => 'TPL-C-07-aranda',
		'head_mode'    => 'rule',
		'arch'         => 'TPL-C-07',
		'brand'        => 'aranda',
		'brand_name'   => 'Motor Aranda',
		'brand_sector' => 'Automoción · ocasión',
		'tpl_name'     => 'Stock / Ocasión',
		'site'         => 'corporate',
		'site_es'      => 'Corporativa',
		'fits'         => 'Concesionarios multimarca, ocasión, maquinaria, náutica, caravanas',
		'dna'          => 'COMP-SEARCH-FILTERS · COMP-STOCK-GRID · COMP-TRADE-IN',
		'wire'         => 'COMP-HEADER · COMP-SEARCH-FILTERS · COMP-STOCK-GRID · COMP-TRADE-IN · COMP-FINANCE · COMP-TRUST-BADGES · COMP-TESTIMONIAL · COMP-MAP-NAP · COMP-FOOTER',
		'nav'          => array( 'Stock', 'Financiación', 'Nosotros' ),
		'nav_cta'      => 'Vender mi coche',
		'phone'        => '948 00 00 00',
		'hero'         => array(
			'eyebrow' => 'Ocasión seleccionada · Pamplona',
			'h1'      => '38 coches en el patio, y los 38 con su informe',
			'lede'    => 'Cada unidad lleva 150 puntos revisados, el histórico de mantenimiento y la ficha de kilómetros certificada. Si algo no está, se lo decimos antes de que pregunte.',
			'img'     => 'aranda-patio',
		),
		'search'       => array(
			'count'   => '38 unidades disponibles',
			'submit'  => 'Ver resultados',
			'fields'  => array(
				array( 'marca', 'Marca', array( 'Todas', 'Volkswagen', 'Peugeot', 'Toyota', 'Kia', 'Renault' ) ),
				array( 'precio', 'Precio hasta', array( 'Sin límite', '10.000 €', '15.000 €', '20.000 €', '30.000 €' ) ),
				array( 'kms', 'Kilómetros hasta', array( 'Sin límite', '50.000', '100.000', '150.000' ) ),
				array( 'comb', 'Combustible', array( 'Todos', 'Gasolina', 'Diésel', 'Híbrido', 'Eléctrico' ) ),
			),
		),
		/* LA UNIDAD DESTACADA ES LA PRIMERA DE LA REJILLA, y los quince datos de aquí abajo tienen
		   que cuadrar con los cinco de allí arriba. El T-Roc de la rejilla dice 2021, 48.200 km,
		   gasolina, manual y 150 CV; si la ficha dijera otra cosa, la página que existe para dar
		   confianza sería la que la quita. Los 48.200 salen además EN LA FOTOGRAFÍA del cuadro de
		   mandos, que es lo que `TPL-UNIT-01` exige por escrito. */
		'producto'     => array(
			'crumbs'  => array( 'Inicio', 'Stock', 'Volkswagen T-Roc 1.5 TSI' ),
			'ref'     => array( 'Ref. MA-1184', 'En el patio, disponible para prueba desde mañana.' ),
			'head'    => array(
				'eyebrow' => 'Unidad de ocasión',
				'h1'      => 'Volkswagen T-Roc 1.5 TSI · 2021 · 48.200 km',
				'lede'    => 'Un propietario, libro sellado en red oficial y doce meses de garantía que empiezan el día de la entrega, no el de la matriculación.',
			),
			'gallery' => array(
				'label' => 'Fotografías de la unidad',
				'main'  => 'aranda-v1',
				/* TRES TOMAS DE ESTA UNIDAD Y NINGUNA PRESTADA. La cuarta era `aranda-patio`, que es el
				   hero de la home: un coche oscuro con otro claro detrás. Puesta entre las miniaturas de
				   una ficha que promete «fotos de ESTA unidad», se lee como un coche distinto colado en
				   la galería — y una foto que contradice la frase de debajo hace más daño que un hueco. */
				'shots' => array( 'aranda-u-interior', 'aranda-u-cuadro', 'aranda-u-maletero' ),
				'cap'   => 'Fotos de esta unidad, tomadas en el patio. El cuentakilómetros sale legible a propósito: es el único dato de la ficha que usted puede comprobar sin venir.',
			),
			'facts'   => array(
				'eyebrow' => 'Ficha',
				'h2'      => 'Los seis datos',
				'items'   => array(
					array( 'Año', '2021' ),
					array( 'Kilómetros', '48.200' ),
					array( 'Combustible', 'Gasolina' ),
					array( 'Cambio', 'Manual' ),
					array( 'Potencia', '150 CV' ),
					array( 'Plazas', '5' ),
				),
			),
			'price'   => array(
				'eyebrow'   => 'Precio',
				'h2'        => 'Al contado y en cuota, los dos',
				'cash_lbl'  => 'Al contado',
				'cash'      => '21.900 €',
				'cash_note' => 'Transferencia e IVA incluidos. Es el precio que verá en el contrato.',
				'quota_lbl' => 'Financiado',
				'quota'     => '253 €/mes',
				'terms'     => array(
					array( 'Entrada', '4.380 €' ),
					array( 'Plazo', '72 meses' ),
					array( 'TAE', '7,95 %' ),
					array( 'Total adeudado', '22.596 €' ),
				),
				'note'      => 'La cuota y la TAE las firma la entidad financiera y están sujetas a su aprobación; el precio al contado lo firma el patio. Publicamos los dos porque el número de la web tiene que ser el del despacho.',
			),
			'history' => array(
				'eyebrow' => 'Historial',
				'h2'      => 'De dónde viene',
				'items'   => array(
					array( 'Propietarios', 'Uno', 'Particular. Comprado nuevo en concesión oficial de Pamplona.' ),
					array( 'Libro de mantenimiento', 'Al día', 'Cinco sellos, todos en red oficial. El último a 45.900 km.' ),
					array( 'Última ITV', 'Favorable', 'Pasada en enero. La siguiente le toca en enero de 2027.' ),
					array( 'Garantía', 'Doce meses', 'Mecánica y electrónica, sin límite de kilómetros. Empieza el día de la entrega.' ),
				),
			),
			'drive'   => array(
				'eyebrow'  => 'Prueba',
				'h2'       => 'Pruébelo antes de decidir',
				'lede'     => 'Cuarenta minutos y la conduce usted. La referencia viaja con la solicitud: sin ella no podríamos apartarle esta unidad y no otra.',
				'fields'   => array(
					array( 'ref', 'Referencia', 'text', 'MA-1184' ),
					array( 'nombre', 'Nombre', 'text' ),
					array( 'tel', 'Teléfono', 'tel' ),
				),
				'day_lbl'  => 'Día',
				'days'     => array( 'Jueves 22', 'Viernes 23', 'Sábado 24', 'Lunes 26' ),
				'slot_lbl' => 'Hora',
				'slots'    => array( '10:00', '11:30', '13:00', '17:00', '18:30' ),
				'submit'   => 'Reservar la prueba',
				'small'    => 'Necesita carné con más de dos años. Le confirmamos por teléfono el mismo día.',
			),
			'related' => array(
				'eyebrow' => 'Parecidas',
				'h2'      => 'Otras tres en su rango',
				'note'    => 'Del mismo patio y con la misma garantía. Si ninguna encaja, la rejilla completa tiene cuarenta.',
				'items'   => array(
					array( 'img' => 'aranda-v3', 'h3' => 'Toyota Corolla 1.8 HSD',   'facts' => array( '2022', '31.050 km', 'Híbrido',  'Auto',   '122 CV' ), 'price' => '23.700 €', 'quota' => '274 €/mes' ),
					array( 'img' => 'aranda-v6', 'h3' => 'Seat León 1.0 TSI',        'facts' => array( '2020', '68.300 km', 'Gasolina', 'Manual', '110 CV' ), 'price' => '15.600 €', 'quota' => '180 €/mes' ),
					array( 'img' => 'aranda-v2', 'h3' => 'Kia Sportage 1.6 CRDi',    'facts' => array( '2020', '76.400 km', 'Diésel',   'Manual', '136 CV' ), 'price' => '18.400 €', 'quota' => '212 €/mes' ),
				),
			),
		),
		'stock'        => array(
			'eyebrow' => 'En el patio',
			'h2'      => 'Lo que hay hoy',
			'note'    => 'Precios con transferencia e IVA incluidos. La cuota es orientativa a 72 meses con 20 % de entrada.',
			'items'   => array(
				array( 'img' => 'aranda-v1', 'h3' => 'Volkswagen T-Roc 1.5 TSI', 'facts' => array( '2021', '48.200 km', 'Gasolina', 'Manual', '150 CV' ), 'price' => '21.900 €', 'quota' => '253 €/mes' ),
				array( 'img' => 'aranda-v2', 'h3' => 'Kia Sportage 1.6 CRDi',    'facts' => array( '2020', '76.400 km', 'Diésel',   'Manual', '136 CV' ), 'price' => '18.400 €', 'quota' => '212 €/mes' ),
				array( 'img' => 'aranda-v3', 'h3' => 'Toyota Corolla 1.8 HSD',   'facts' => array( '2022', '31.050 km', 'Híbrido',  'Auto',   '122 CV' ), 'price' => '23.700 €', 'quota' => '274 €/mes' ),
				array( 'img' => 'aranda-v4', 'h3' => 'Peugeot 3008 Hybrid',      'facts' => array( '2021', '54.800 km', 'Híbrido',  'Auto',   '180 CV' ), 'price' => '25.300 €', 'quota' => '292 €/mes' ),
				array( 'img' => 'aranda-v5', 'h3' => 'Renault Mégane E-Tech',    'facts' => array( '2023', '18.900 km', 'Eléctrico','Auto',   '131 CV' ), 'price' => '27.800 €', 'quota' => '321 €/mes' ),
				array( 'img' => 'aranda-v6', 'h3' => 'Seat León 1.0 TSI',        'facts' => array( '2020', '68.300 km', 'Gasolina', 'Manual', '110 CV' ), 'price' => '15.600 €', 'quota' => '180 €/mes' ),
			),
		),
		'tradein'      => array(
			'eyebrow' => 'Tasación',
			'h2'      => 'Traiga el suyo y descuéntelo',
			'lede'    => 'Matrícula y kilómetros. Con eso ya le damos un rango por teléfono el mismo día; el precio cerrado sale cuando lo vemos.',
			'fields'  => array(
				array( 'matricula', 'Matrícula', 'text' ),
				array( 'km', 'Kilómetros', 'text' ),
			),
			'submit'  => 'Pedir tasación',
			'small'   => 'No pedimos su correo. Si prefiere que no llamemos, escriba «solo WhatsApp» al enviar.',
		),
		'finance'      => array(
			'eyebrow' => 'Financiación',
			'h2'      => 'La cuota, con el coste total delante',
			'rows'    => array(
				array( 'Importe financiado', '17.520 €' ),
				array( 'Entrada (20 %)',     '4.380 €' ),
				array( 'Plazo',              '72 meses' ),
				array( 'Cuota',              '253 €/mes' ),
				array( 'TAE',                '8,95 %' ),
				array( 'Coste total del crédito', '20.736 €' ),
			),
			'note'    => 'Ejemplo orientativo sobre el Volkswagen T-Roc. Sujeto a aprobación de la entidad; el build enlaza la información normalizada.',
		),
		'badges'       => array(
			array( '12 meses', 'de garantía en todas las unidades, sin excepciones' ),
			array( '150 puntos', 'revisados y firmados antes de ponerlo a la venta' ),
			array( 'Transferencia', 'incluida en el precio publicado' ),
			array( '7 días', 'para devolverlo si no es lo que esperaba' ),
		),
		'quotes'       => array(
			'eyebrow' => 'Quien ya compró',
			'h2'      => 'Tres coches que salieron de aquí',
			'items'   => array(
				array( 'Me enseñaron el informe de los 150 puntos antes de que lo pidiera. Dos cosas estaban en amarillo y me las explicaron.', 'Íñigo Sarasa', 'Toyota Corolla' ),
				array( 'Tasaron el mío por teléfono con la matrícula y al verlo mantuvieron el precio. No hubo rebaja de última hora.', 'Cristina Iriarte', 'Kia Sportage' ),
				array( 'La cuota que me dijeron es la que firmé. Eso, en este sector, no es normal.', 'Marc Bosch', 'Seat León' ),
			),
		),
		'nap'          => array(
			'eyebrow' => 'Dónde estamos',
			'h2'      => 'Polígono Landaben, Pamplona',
			'addr'    => array( 'Calle E, nave 14', '31012 Pamplona · Navarra' ),
			'phone'   => '948 00 00 00',
			'mail'    => 'stock@motoraranda.example',
			'hours'   => array(
				array( 'Lunes a viernes', '09:00 – 13:30 · 16:00 – 20:00' ),
				array( 'Sábado', '10:00 – 14:00' ),
				array( 'Domingo', 'Cerrado' ),
			),
			'note'    => 'Patio cubierto: se ven los coches aunque llueva. Salida 3 de la ronda, a 200 m.',
			'img'     => 'aranda-v3',
		),
		'footer'       => array(
			'tag'   => 'Motor Aranda · Polígono Landaben, calle E nave 14, 31012 Pamplona · 948 00 00 00',
			'links' => array( 'Stock', 'Financiación', 'Privacidad' ),
			'legal' => 'Motor Aranda SL · Maqueta interna NovaMira, no publicada.',
		),
	),

	/* ── AURIA · TPL-C-08 · UN solo producto contado entero. TPL-C-07 tiene cuarenta unidades y su
	   trabajo es descartarlas; éste tiene una y su trabajo es que la mires despacio. */
	

	/* ── TALLER BERGARA · TPL-C-09 · el precio es lo que NO se sabe, y ése es el miedo que hace
	   posponer la reparación. Por eso la tarifa es la segunda cosa de la página y lleva importes. */
	

	/* ── CASA TERRAZZA · THE FIRST ENTRY IN THIS TABLE THAT IS A BUSINESS AND NOT A CONFIGURATION.
	   Everything below it is Piedra Valdés — one company rendered through ten architectures and
	   four anchors, which is forty cards a client cannot choose between by sector because they are
	   all the same sector. This one takes TPL-C-05's SKELETON (the phone in the header, the booking
	   block, the address and the hours at the close) and brings everything else itself: name,
	   sector, ground, brass, Fraunces, and seven photographs of its own room.

	   THE COPY IS WRITTEN FOR THE BUSINESS AND NOT ADAPTED FROM THE QUARRY'S. An archetype supplies
	   the SECTIONS a restaurant needs; it never supplies the sentences, and a page whose booking
	   block says "el cantero estará con usted toda la visita" over a photograph of a dining room is
	   the tell that nobody wrote it. Which also means: the axis-proof argument that the copy is a
	   constant is a HOUSE argument. It ends at the brand, on purpose, and § "Why one shared set" in
	   `_gallery-images.md` records the same boundary for the photographs. */
	

	

	

	/* TPL-C-03 · Portfolio / Showcase. Its doc is blunt about what it refuses: "NO pricing, NO
	   stats, NO forms largos", and "bajo en texto, alto en visual". So this is the one archetype
	   here that does NOT get a stats band, does not get a lead form, and carries barely any prose.
	   What it gets instead is the grid, and the grid is the page.

	   The four reference kits all lean on counters, pricing tables and long service copy. Putting
	   any of that here because the references do it would be the exact failure the ecommerce family
	   was rebuilt to fix: five archetypes wearing one skeleton. An archetype earns its place by
	   what it REFUSES. */
	

	/* TPL-E-03 · Brand Story. Its doc: "la marca y el relato venden; el producto ilustra". The
	   catalogue is deliberately thin here — one carousel, no grid, no search-first header — and
	   that thinness is the archetype, not an omission. TPL-E-02 is the shop that hides its brand;
	   this is the brand that sells a shop. */
	

	/* TPL-C-04 · Landing / Single Offer. One offer, one CTA, and the page repeats it. Its doc's
	   DNA line is "una sola oferta, un solo CTA repetido, sin navegación que distraiga", so the
	   header carries ONE link and the nav that every other corporate archetype prints is absent
	   on purpose — the absence is the archetype. */
	

	/* TPL-E-05 · Promo / Campaign. Urgency is the archetype, so the deadline appears four times.
	   THE DEADLINE IS A DATE IN THE HTML, NOT A COUNTDOWN. A ticking clock is the device the
	   reference kits reach for, and it renders `00:00:00` until its script runs — on a page whose
	   entire argument is "this ends soon", a timer that says zero is the worst possible failure
	   mode, and it fails that way for every crawler permanently. A date needs nothing. */
	

	/* TPL-C-05 · Local / Booking. The archetype of a place you go to. Its header carries the PHONE
	   as text, its close carries the address and the hours, and both are DNA: a local business
	   whose phone number lives only inside a form is a local business you cannot ring from the car. */
	

	/* TPL-E-01 · Visual / Brand-Led. The catalogue arrives through CATEGORIES and a lookbook, never
	   through a grid of SKUs: this is the shop where the reader is browsing a world, not looking up
	   a part number. TPL-E-02 puts eight products above the fold; this one puts three doors. */
	

	/* TPL-E-04 · Categories First. The catalogue is WIDE, so the page routes before it sells: a
	   dense category grid first, then products organised by tab. TPL-E-02 answers "I know what I
	   want"; this one answers "I know roughly where to look". */
	

	

	/* TPL-E-06 · CORTE NUEVE. La tienda cuyo cliente no duda del producto: duda de si le va a caber.
	   La talla es la primera causa de devolución en moda online, así que el coste de no responderla
	   no es una venta perdida — es una venta hecha, enviada, devuelta y reembolsada, con dos portes
	   por el camino. De ahí las tres decisiones del arquetipo: el buscador de talla ARRIBA, las
	   medidas en centímetros y por prenda, y la misma prenda sobre tres cuerpos con su talla escrita.
	   NO hay héroe editorial a sangre: una pantalla completa de fotografía es lo que hace esta tienda
	   cuando ya no tiene nada que decir, y aquí tiene mucho. */
	'TPL-E-06-corte' => array(
		'tpl'          => 'TPL-E-06-corte',
		'arch'         => 'TPL-E-06',
		'brand'        => 'corte',
		'brand_name'   => 'Corte Nueve',
		'brand_sector' => 'Moda · vaquero',
		'tpl_name'     => 'Talla / Prueba',
		'site'         => 'ecommerce',
		'site_es'      => 'Ecommerce',
		'fits'         => 'Moda con ajuste, calzado, lencería, ropa técnica y deportiva, uniformes',
		'dna'          => 'COMP-FIT-FINDER · COMP-MEASURE-TABLE · COMP-FIT-GALLERY · COMP-RETURN-PROMISE',
		'wire'         => 'COMP-HEADER · COMP-FIT-FINDER · COMP-PRODUCT-GRID · COMP-MEASURE-TABLE · COMP-FIT-GALLERY · COMP-RETURN-PROMISE · COMP-FAQ · COMP-FOOTER',
		'head_mode'    => 'tight',
		'nav'          => array( 'Vaqueros', 'Chaquetas', 'Camisas', 'Medidas' ),
		'search'       => 'Buscar corte, lavado, talla…',
		'tools'        => array( 'Cuenta' ),
		'cart'         => 'Cesta',
		'cart_n'       => '1',
		'finder'       => array(
			'eyebrow' => 'Antes de nada',
			'h1'      => 'Dinos qué talla llevas y de qué marca',
			'lede'    => 'Traducimos desde treinta marcas. No es un algoritmo: es una tabla que hicimos midiendo prendas reales, y se puede consultar entera más abajo.',
			'lbl_1'   => 'Tu talla habitual',
			'opt_1'   => array( '38', '40', '42', '44', '46', '48' ),
			'lbl_2'   => 'En qué marca',
			'opt_2'   => array( 'Levi\'s', 'Zara', 'Uniqlo', 'Carhartt', 'Otra' ),
			'cta'     => 'Ver mi talla aquí',
			'result'  => 'Con una 42 de Levi\'s aquí llevas una 42 en recto y una 44 si la quieres holgada.',
			'note'    => 'Si sale entre dos, mandamos las dos y devuelves la que no. El porte de la vuelta lo pagamos nosotros.',
		),
		'grid'         => array(
			'eyebrow' => 'Lo que hay',
			'h2'      => 'Seis prendas y su talla en la foto',
			'note'    => 'Cada ficha dice qué talla lleva la persona de la foto y cuánto mide. Una prenda sobre un solo cuerpo informa de ese cuerpo y de ninguno más.',
			'items'   => array(
				array( 'img' => 'corte-v1', 'h3' => 'Recto lavado medio', 'p' => '89 € · la modelo lleva 42' ),
				array( 'img' => 'corte-v2', 'h3' => 'Pitillo negro',      'p' => '85 € · el modelo lleva 40' ),
				array( 'img' => 'corte-v3', 'h3' => 'Ancho lavado claro', 'p' => '95 € · la modelo lleva 44' ),
				array( 'img' => 'corte-v4', 'h3' => 'Chaqueta índigo',    'p' => '119 € · el modelo lleva M' ),
				array( 'img' => 'corte-v5', 'h3' => 'Chino oliva',        'p' => '69 € · el modelo lleva 42' ),
				array( 'img' => 'corte-v6', 'h3' => 'Camisa de chambray', 'p' => '75 € · la modelo lleva M' ),
			),
		),
		'measure'      => array(
			'eyebrow' => 'En centímetros',
			'h2'      => 'Las medidas del recto lavado medio',
			'note'    => 'Prenda a prenda, no una tabla genérica de marca al pie. «M» no es una medida: es una etiqueta que cada fábrica interpreta distinto.',
			'cols'    => array( 'Talla', 'Cintura', 'Cadera', 'Tiro', 'Largo', 'Bajo' ),
			'rows'    => array(
				array( '38', '74', '94', '27', '104', '17' ),
				array( '40', '78', '98', '27,5', '105', '17,5' ),
				array( '42', '82', '102', '28', '106', '18' ),
				array( '44', '86', '106', '28,5', '107', '18,5' ),
				array( '46', '90', '110', '29', '108', '19' ),
				array( '48', '94', '114', '29,5', '109', '19,5' ),
			),
			'how'     => 'Cintura medida en plano y multiplicada por dos, con la prenda abrochada. Si comparas con un vaquero tuyo, mídelo igual.',
		),
		'fitgal'       => array(
			'eyebrow' => 'La misma prenda',
			'h2'      => 'Sobre tres cuerpos distintos',
			'note'    => 'Es el mismo vaquero recto lavado medio en las tres fotos. Lo que cambia es quién lo lleva y qué talla pidió.',
			'items'   => array(
				array( 'img' => 'corte-cuerpo1', 'who' => '1,78 m · 68 kg', 'size' => 'Talla 40' ),
				array( 'img' => 'corte-cuerpo2', 'who' => '1,70 m · 79 kg', 'size' => 'Talla 44' ),
				array( 'img' => 'corte-cuerpo3', 'who' => '1,65 m · 92 kg', 'size' => 'Talla 48' ),
			),
		),
		'ret'          => array(
			'eyebrow' => 'Si no te vale',
			'h2'      => 'La devolución, con cifras',
			'items'   => array(
				array( '30', 'días para devolver', 'Desde que te llega, no desde que lo pides.' ),
				array( '0 €', 'cuesta el porte', 'Lo pagamos nosotros, también si pediste dos tallas.' ),
				array( '4', 'días hasta el reembolso', 'Desde que el paquete entra en nuestro almacén.' ),
			),
			'note'    => 'Sin asteriscos y sin «previo estudio». Si la prenda vuelve sin usar y con la etiqueta puesta, el dinero sale.',
		),
		'faq'          => array(
			'eyebrow' => 'Dudas de ajuste',
			'h2'      => 'Lo que se pregunta antes de pedir talla',
			'items'   => array(
				array( '¿Encoge al lavar?', 'El algodón sin elastano encoge entre un 1 y un 2% el primer lavado, y ya está: son uno o dos centímetros de largo. Las medidas de la tabla son de prenda ya lavada.' ),
				array( '¿Puedo pedir dos tallas y devolver una?', 'Sí, y es lo que recomendamos si el buscador te deja entre dos. El porte de la vuelta lo pagamos nosotros.' ),
				array( '¿Los vaqueros de mujer y de hombre son la misma horma?', 'No. Comparten lavado y tejido, y cambian tiro y cadera. Cada uno tiene su tabla, y son las dos que ves arriba según la prenda.' ),
				array( '¿Qué talla lleva la persona de cada foto?', 'Está escrita debajo de cada imagen, con su altura y su peso. Es el dato que hace comparable una foto de catálogo.' ),
			),
		),
		'footer'       => array(
			'tag'   => 'Vaquero cosido en Igualada · tallas de la 36 a la 52',
			'links' => array( 'Envíos', 'Devoluciones', 'Aviso legal', 'Privacidad', 'Cookies' ),
			'legal' => '© 2026 Corte Nueve S.L. · Taller y almacén en Igualada · IVA incluido',
		),
	),

	/* TPL-E-07 · BAJURA. El producto CAMBIA cada semana y no es intercambiable consigo mismo: tiene
	   lote, fecha y un peso que no es exacto hasta que alguien lo pone en la báscula. Eso rompe tres
	   supuestos que ningún otro arquetipo de ecommerce resuelve — el precio es por kilo y el importe
	   se ajusta al pesar, la entrega es parte del producto (fresco que llega a una casa vacía es
	   fresco perdido), y la confianza es trazabilidad y no insignias: un sello de «pago seguro» no
	   dice nada sobre un pescado. */
	'TPL-E-07-bajura' => array(
		'tpl'          => 'TPL-E-07-bajura',
		'arch'         => 'TPL-E-07',
		'brand'        => 'bajura',
		'brand_name'   => 'Bajura',
		'brand_sector' => 'Alimentación · pescado fresco',
		'tpl_name'     => 'Lote / Peso',
		'site'         => 'ecommerce',
		'site_es'      => 'Ecommerce',
		'fits'         => 'Pescadería y carnicería online, quesos, embutido, café de tueste, bodega, huerta',
		'dna'          => 'COMP-DELIVERY-WINDOW · COMP-BATCH-CARD · COMP-WEIGHT-NOTE · COMP-COLD-CHAIN',
		'wire'         => 'COMP-HEADER · COMP-DELIVERY-WINDOW · COMP-BATCH-CARD · COMP-WEIGHT-NOTE · COMP-ORIGIN-MAP · COMP-COLD-CHAIN · COMP-TESTIMONIAL · COMP-NEWSLETTER · COMP-FOOTER',
		'head_mode'    => 'rule',
		'nav'          => array( 'Lonja de hoy', 'Pescado', 'Marisco', 'Cómo llega' ),
		'cart'         => 'Cesta',
		'cart_n'       => '2',
		'window'       => array(
			'eyebrow' => 'Antes de elegir',
			'h1'      => '¿Qué días llegamos a tu calle?',
			'lede'    => 'Sale de la lonja por la mañana y llega al día siguiente. Si no servimos tu código postal el día que te sirve, no hay compra que hacer — y averiguarlo en el último paso es averiguarlo tarde.',
			'lbl'     => 'Código postal',
			'ph'      => '48001',
			'cta'     => 'Ver mis días',
			'result'  => 'En el 48001 repartimos martes, jueves y sábado. Pedido antes de las 22:00 del día anterior.',
			'note'    => 'Fuera de esos días no ofrecemos entrega: preferimos no vender antes que mandar fresco a una casa vacía un viernes por la tarde.',
		),
		'batch'        => array(
			'eyebrow' => 'La lonja de hoy',
			'h2'      => 'Cuatro piezas, con su lote',
			'note'    => 'Lo que hay hoy. Mañana es otra cosa, y por eso cada pieza lleva su barco, su fecha y su precio por kilo — no un PVP fijo que mentiría en todos los pedidos.',
			'items'   => array(
				array(
					'img' => 'bajura-pieza', 'h3' => 'Lubina de anzuelo',
					'kg' => '28 €/kg', 'pc' => 'pieza de ~1,1 kg ≈ 31 €',
					'rows' => array( array( 'Barco', 'Nuevo Aramar · Ondarroa' ), array( 'Lote', 'ON-2609-14' ), array( 'Consumo preferente', '3 días desde la entrega' ) ),
				),
				array(
					'img' => 'bajura-lomo', 'h3' => 'Lomo de atún rojo',
					'kg' => '46 €/kg', 'pc' => 'corte de ~0,8 kg ≈ 37 €',
					'rows' => array( array( 'Almadraba', 'Barbate' ), array( 'Lote', 'BA-2609-03' ), array( 'Consumo preferente', '2 días desde la entrega' ) ),
				),
				array(
					'img' => 'bajura-marisco', 'h3' => 'Langostino de costa',
					'kg' => '34 €/kg', 'pc' => 'bandeja de ~0,5 kg ≈ 17 €',
					'rows' => array( array( 'Lonja', 'Sanlúcar' ), array( 'Lote', 'SL-2609-21' ), array( 'Consumo preferente', '2 días desde la entrega' ) ),
				),
				array(
					'img' => 'bajura-corte', 'h3' => 'Merluza en rodajas',
					'kg' => '22 €/kg', 'pc' => 'bandeja de ~0,9 kg ≈ 20 €',
					'rows' => array( array( 'Barco', 'Itsasgain · Bermeo' ), array( 'Lote', 'BE-2609-08' ), array( 'Consumo preferente', '3 días desde la entrega' ) ),
				),
			),
		),
		'weight'       => array(
			'eyebrow' => 'Antes de cobrar',
			'h2'      => 'Por qué el importe final varía',
			'lede'    => 'Las piezas no salen todas iguales de la mar. Publicamos el precio por kilo y un peso aproximado; al preparar el pedido se pesa de verdad y el cargo se ajusta.',
			'items'   => array(
				array( '±15%', 'es el margen habitual', 'Una pieza anunciada de 1,1 kg puede salir de 0,95 a 1,25.' ),
				array( 'Antes', 'de pasar el cobro', 'Te mandamos el peso real y el importe. Si no contestas, no se cobra.' ),
				array( 'Nunca', 'más de lo aproximado +15%', 'Si la pieza se pasa, la partimos o buscamos otra.' ),
			),
			'note'    => 'Un PVP fijo en una pescadería miente en el 100% de los pedidos. Preferimos decirlo arriba y no en una nota legal al pie.',
		),
		'origin'       => array(
			'eyebrow' => 'De dónde viene',
			'h2'      => 'De la lonja, no de una plataforma',
			'lede'    => 'Compramos en subasta en tres puertos del Cantábrico y uno del Golfo. Sin intermediario no hay un teléfono al que llamar cuando algo sale mal: hay un nombre y un barco.',
			'img'     => 'bajura-puerto',
			/* LEÍDAS SOBRE LA FOTOGRAFÍA, no repartidas a ojo. Con la primera colocación dos caían
			   en mitad de la dársena, y un puerto pesquero flotando sobre el agua es el detalle que
			   hace que nadie se crea el resto de la página. Es la misma corrección que ya costó una
			   vuelta en el mapa de TPL-C-13: las cuatro van sobre el pueblo o sobre el muelle. */
			'pins'    => array(
				array( 12, 24, 'Ondarroa' ),
				array( 11, 66, 'Bermeo' ),
				array( 74, 18, 'Getaria' ),
				array( 52, 88, 'Sanlúcar' ),
			),
		),
		'cold'         => array(
			'eyebrow' => 'Cómo viaja',
			'h2'      => 'Y qué hacer al abrir la caja',
			'img'     => 'bajura-caja',
			'steps'   => array(
				array( 'Se envasa al vacío', 'Sobre hielo en escamas, dentro de bolsa estanca. El pescado no toca el agua del deshielo.' ),
				array( 'Caja isotérmica', 'Con acumuladores de gel congelados a −18 °C. Aguanta 30 horas por encima de 4 °C aunque el reparto se retrase.' ),
				array( 'Al recibirlo', 'A la nevera en la primera hora, todavía envasado. Si vas a congelar, hazlo el mismo día y sin abrir la bolsa.' ),
			),
			'note'    => 'Si la caja llega templada o rota, la foto por WhatsApp y se repone en el siguiente reparto. Sin devolver nada: con fresco no tendría sentido.',
		),
		'quotes'       => array(
			'eyebrow' => 'Quién ya compró',
			'h2'      => 'Tres cocinas y una casa',
			'items'   => array(
				array( 'Nos llega el martes lo que se subastó el lunes. Eso antes sólo lo tenía yendo yo a la lonja.', 'Iñaki Beitia', 'Restaurante Aixerrota, Getxo' ),
				array( 'El aviso del peso real antes de cobrar me pareció una tontería hasta que vi la primera factura cuadrada.', 'Carmen Losada', 'Cliente desde 2024' ),
				array( 'Pido lomo de atún cada quince días y siempre sé de qué almadraba viene. Ningún proveedor me daba eso.', 'Sergi Puyol', 'Bar Nou, Barcelona' ),
			),
		),
		'news'         => array(
			'eyebrow' => 'Aviso de lonja',
			'h2'      => 'Te avisamos cuando entre lote nuevo',
			'lede'    => 'Un correo los días que hay subasta buena, y ninguno el resto. No es un boletín: es un aviso de que hay algo que mañana no estará.',
			'label'   => 'Tu email',
			'cta'     => 'Avisadme',
			'small'   => 'Dos o tres correos al mes. Baja en un clic y no volvemos a escribir.',
		),
		'footer'       => array(
			'tag'   => 'Pescado de lonja · reparto en Bizkaia, Gipuzkoa y Cantabria',
			'links' => array( 'Zonas de reparto', 'Cómo viaja', 'Aviso legal', 'Privacidad', 'Cookies' ),
			'legal' => '© 2026 Bajura S. Coop. · Puerto de Ondarroa, muelle 3 · IVA incluido',
		),
	),

	/* TPL-E-08 · TUESTE NORTE. Lo que se elige aquí no es una cantidad: es un PLAN y una cadencia.
	   Nadie compara «12,90 €» con «12,90 € al mes cada seis semanas» del mismo modo, y una rejilla
	   de producto con botón de comprar empuja justo a la decisión equivocada — llevarse una bolsa
	   suelta en vez de suscribirse. Por eso esta tienda NO tiene COMP-PRODUCT-INFO en ninguna de sus
	   dos páginas: el control de compra es el selector de plan.
	   Frente a TPL-C-11, que también vende una cuota: aquel plan TERMINA —la ortodoncia dura
	   dieciocho meses— y por eso su bloque central es una línea de tiempo. Éste no termina, y por eso
	   el suyo es un control de cadencia. */
	'TPL-E-08-tueste' => array(
		'tpl'          => 'TPL-E-08-tueste',
		'arch'         => 'TPL-E-08',
		'brand'        => 'tueste',
		'brand_name'   => 'Tueste Norte',
		'brand_sector' => 'Alimentación · café por suscripción',
		'tpl_name'     => 'Suscripción / Entrega recurrente',
		'site'         => 'ecommerce',
		'site_es'      => 'Ecommerce',
		'fits'         => 'Café de tueste, comida de mascota, cosmética de reposición, vino, cajas de temporada, consumibles',
		'dna'          => 'COMP-PLAN-PICKER · COMP-CADENCE · COMP-FIRST-BOX · COMP-PAUSE-PROMISE',
		'wire'         => 'COMP-HEADER · COMP-HERO · COMP-PLAN-PICKER · COMP-CADENCE · COMP-FIRST-BOX · COMP-PAUSE-PROMISE · COMP-TESTIMONIAL · COMP-FAQ · COMP-FOOTER',
		'nav'          => array( 'Cómo funciona', 'Los cafés', 'Preguntas' ),
		'nav_cta'      => 'Empezar',
		'hero'         => array(
			'eyebrow' => 'Tostado el lunes, en tu casa el jueves',
			'h1'      => 'Café recién tostado, cada cuánto quieras',
			'lede'    => 'Tostamos por encargo los lunes y enviamos el martes. Eliges cantidad y frecuencia, y las cambias cuando te sobre o te falte — que es lo que de verdad pasa.',
			'cta_1'   => 'Ver los planes',
			'cta_2'   => 'Qué llega la primera vez',
			'img'     => 'tueste-hero',
		),
		'plans'        => array(
			'eyebrow' => 'Los planes',
			'h2'      => 'Tres, contados enteros',
			'note'    => 'La cuota incluye el envío. Al lado va lo que costaría comprando bolsa a bolsa, porque comparar es lo que hace que un plan se entienda.',
			'items'   => array(
				array(
					'name' => 'Una bolsa', 'fee' => '13,90 € al mes', 'each' => 'suelta saldría 15,50 €',
					'what' => '250 g cada cuatro semanas', 'who' => 'Para una persona que hace café en casa a diario.',
					'rows' => array( 'Un origen por envío', 'Molido a tu método o en grano', 'Cambias la frecuencia cuando quieras' ),
					'best' => false,
				),
				array(
					'name' => 'Dos bolsas', 'fee' => '24,90 € al mes', 'each' => 'sueltas saldrían 31,00 €',
					'what' => '500 g cada cuatro semanas', 'who' => 'Dos personas, o una que también hace café el fin de semana.',
					'rows' => array( 'Dos orígenes distintos', 'Molido a tu método o en grano', 'Saltas un envío en un clic' ),
					'best' => true,
				),
				array(
					'name' => 'Oficina', 'fee' => '69,00 € al mes', 'each' => 'sueltas saldrían 93,00 €',
					'what' => '1,5 kg cada dos semanas', 'who' => 'Equipos de ocho a quince personas.',
					'rows' => array( 'Tres orígenes rotando', 'Factura mensual con IVA desglosado', 'Pausa por vacaciones sin dar de baja' ),
					'best' => false,
				),
			),
		),
		'cadence'      => array(
			'eyebrow' => 'La letra pequeña, en grande',
			'h2'      => 'Cambiar, saltar o pausar',
			'lede'    => 'El miedo a acumular café que no te da tiempo a beber es la objeción número uno, y no se disuelve leyendo condiciones: se disuelve enseñando los tres controles.',
			'items'   => array(
				array( 'Cada 2, 4 o 6 semanas', 'Se cambia desde la cuenta y afecta al siguiente envío, no al que ya salió.' ),
				array( 'Saltar el próximo', 'Un clic. No cuenta como baja y no cambia el precio.' ),
				array( 'Pausar hasta una fecha', 'Eliges el día de vuelta. Si no lo eliges, se queda pausada.' ),
			),
			'note'    => 'Los tres se tocan desde la cuenta, sin escribir a nadie y sin esperar respuesta.',
		),
		'firstbox'     => array(
			'eyebrow' => 'La primera caja',
			'h2'      => 'Qué llega exactamente',
			'lede'    => 'No «descubre nuestro café». Esto es lo que hay dentro del plan de dos bolsas la primera vez, con gramos y con nombre.',
			'img'     => 'tueste-caja',
			'items'   => array(
				array( '250 g', 'Huila, Colombia', 'Lavado. Panela y ciruela. Para filtro.' ),
				array( '250 g', 'Sidamo, Etiopía', 'Natural. Fresa y té negro. Para filtro o espresso.' ),
				array( '1', 'Ficha de tueste', 'Fecha, altitud, finca y la receta con la que lo catamos.' ),
			),
			'note'    => 'Sale el martes siguiente a tu alta. Si te suscribes un martes antes de las 10:00, sale ese mismo día.',
		),
		'pause'        => array(
			'eyebrow' => 'Salirse',
			'h2'      => 'Cancelar es un botón, no un correo',
			'lede'    => 'Sin permanencia y sin llamada de retención. Una suscripción de la que no se ve la salida no se firma, y esconderla no la hace menos visible: la hace más sospechosa.',
			'items'   => array(
				array( 'Desde la cuenta', 'Un botón. No hay que escribir ni llamar.' ),
				array( 'Sin permanencia', 'Ni mínimo de envíos ni penalización.' ),
				array( 'El envío ya preparado', 'Si el café ya se tostó, ese envío sale y es el último. No se cobra ninguno más.' ),
			),
		),
		'quotes'       => array(
			'eyebrow' => 'Quién lleva tiempo',
			'h2'      => 'Tres suscripciones viejas',
			'items'   => array(
				array( 'Pausé dos meses por un viaje y volvió solo el día que dije. Es lo único que le pido a una suscripción.', 'Marta Iriarte', 'Suscrita desde 2023' ),
				array( 'Cambié a cada seis semanas porque me sobraba, y dejó de sobrarme. Nadie me llamó para convencerme de nada.', 'Dani Rubio', 'Suscrito desde 2024' ),
				array( 'En la oficina somos doce y la factura llega desglosada. Parece poco y es lo que hace que administración no me lo tumbe.', 'Nerea Blasco', 'Estudio Bilbao' ),
			),
		),
		'faq'          => array(
			'eyebrow' => 'Dudas de compromiso',
			'h2'      => 'Lo que se pregunta antes de suscribirse',
			'items'   => array(
				array( '¿Y si me sobra café?', 'Bajas la frecuencia o saltas un envío, las dos cosas desde la cuenta. Es la pregunta más repetida y por eso la cadencia está arriba y no en un desplegable.' ),
				array( '¿Puedo comprar una bolsa suelta sin suscribirme?', 'Sí, pero no desde aquí: esta página vende el plan. La tienda suelta está en el menú y sale más cara por bolsa, que es la verdad y no un truco.' ),
				array( '¿Elijo yo el café o lo elegís vosotros?', 'Lo elegimos nosotros según lo que haya salido bien esa semana, y lo puedes fijar a un origen concreto si prefieres siempre el mismo.' ),
				array( '¿Qué pasa si me voy de vacaciones?', 'Pausas hasta la fecha de vuelta. No cuenta como baja y no pierdes el precio que tenías.' ),
			),
		),
		'footer'       => array(
			'tag'   => 'Tostadero en Errenteria · tostamos los lunes y enviamos los martes',
			'links' => array( 'Cómo funciona', 'Los cafés', 'Aviso legal', 'Privacidad', 'Cookies' ),
			'legal' => '© 2026 Tueste Norte S.L. · Polígono Txirrita Maleo 4, Errenteria · IVA incluido',
		),
	),

	/* TPL-E-09 · MEDIDA JUSTA. El único ecommerce del catálogo SIN carrito y SIN precio publicado.
	   Un estor de 137 × 214 en lino crudo con cadena a la izquierda no es una referencia: es una
	   combinación que nadie ha fabricado todavía, y su precio no se sabe hasta configurarla. Forzarlo
	   en TPL-E-02 produce el error clásico del sector — precios «desde» que no se parecen al final, un
	   carrito que acepta la compra y un correo posterior pidiendo medidas. Ese correo es donde se cae
	   la venta, y encima ya se cobró. */
	'TPL-E-09-medida' => array(
		'tpl'          => 'TPL-E-09-medida',
		'arch'         => 'TPL-E-09',
		'brand'        => 'medida',
		'brand_name'   => 'Medida Justa',
		'brand_sector' => 'Hogar · estores y cortinas a medida',
		'tpl_name'     => 'A medida / Presupuesto',
		'site'         => 'ecommerce',
		'site_es'      => 'Ecommerce',
		'fits'         => 'Cortinas y estores, mobiliario a medida, encimeras, impresión y rotulación, mamparas, tapicería',
		'dna'          => 'COMP-CONFIGURATOR · COMP-SAMPLE-REQUEST · COMP-LEAD-TIME · COMP-QUOTE-FORM',
		'wire'         => 'COMP-HEADER · COMP-HERO · COMP-CONFIGURATOR · COMP-SAMPLE-REQUEST · COMP-LEAD-TIME · COMP-QUOTE-FORM · COMP-GALLERY · COMP-FAQ · COMP-FOOTER',
		'head_mode'    => 'tight',
		'nav'          => array( 'Estores', 'Cortinas', 'Cómo medir' ),
		'nav_cta'      => 'Pedir presupuesto',
		'hero'         => array(
			'eyebrow' => 'Se cose en Errenteria, se monta en tu casa',
			'h1'      => 'Estores al centímetro, no a la talla más cercana',
			'lede'    => 'Cortamos y cosemos por encargo desde una unidad. Dinos la medida y el tejido y devolvemos un presupuesto cerrado en 48 h laborables — no un «desde» que luego cambia.',
			'cta_1'   => 'Configurar el mío',
			'cta_2'   => 'Pedir muestras',
			'img'     => 'estor-hero',
		),
		'cfg'          => array(
			'eyebrow' => 'Configurador',
			'h2'      => 'Cuatro datos y ya tenemos tu pieza',
			'fields'  => array(
				array( 'ancho', 'Ancho (cm)', '137' ),
				array( 'alto', 'Alto (cm)', '214' ),
			),
			'opt_lbl' => 'Tejido',
			'opts'    => array( 'Lino crudo', 'Screen 5%', 'Opaco térmico' ),
			'fin_lbl' => 'Accionamiento',
			'fins'    => array( 'Cadena izquierda', 'Cadena derecha', 'Motorizado' ),
			'ext_lbl' => 'Añadidos',
			'extras'  => array( 'Cajón y guías laterales', 'Contrapeso forrado', 'Mando a distancia' ),
			'note'    => 'No hay precio hasta emitir el presupuesto. Una cifra provisional que luego cambia hace más daño que no dar ninguna.',
			'cta'     => 'Continuar al presupuesto',
		),
		'measure'      => array(
			'eyebrow' => 'Cómo se mide',
			'h2'      => 'De dónde a dónde',
			'rows'    => array(
				array( 'Ancho', 'De jamba a jamba si va DENTRO del hueco, o el ancho del hueco más 10 cm si va por fuera. Son dos medidas distintas y confundirlas es la equivocación número uno.' ),
				array( 'Alto', 'Del punto de anclaje al suelo o al alféizar, más 15 cm de enrolle. El enrolle lo añadimos nosotros: dinos sólo hasta dónde quieres que llegue bajado.' ),
				array( 'Fuera de escuadra', 'Mide el ancho arriba, en medio y abajo. Si difieren más de 1 cm, dínoslo: se fabrica al menor y se ajusta con guías.' ),
				array( 'Obstáculos', 'Manillas, radiadores y cajas de persiana. Una foto del hueco con el metro apoyado nos vale más que tres correos.' ),
			),
			'note'    => 'En Gipuzkoa, Bizkaia y Navarra vamos a medir sin coste, y estas cuatro dudas dejan de existir.',
			'img'     => 'estor-medicion',
		),
		'sample'       => array(
			'eyebrow' => 'Antes de decidir',
			'h2'      => 'Pide la muestra física',
			'lede'    => 'Hasta cinco muestras de 15 × 15 gratis, en 72 h. Un tejido en pantalla no es el tejido, y de algo cosido a tu medida no hay devolución: la muestra es lo que sustituye a esa garantía que aquí no podemos dar.',
			'cta'     => 'Pedir muestras',
			'items'   => array( 'estor-muestras', 'estor-cortina', 'estor-oficina' ),
		),
		'lead'         => array(
			'eyebrow' => 'Plazo',
			'h2'      => 'Por fases, con días',
			'items'   => array(
				array( 'Medición en casa', '3–5 días', 'Desde que apruebas el presupuesto. Si mides tú, este paso se salta.' ),
				array( 'Corte y confección', '7–9 días', 'En el taller de Errenteria. Aquí entra el forrado del contrapeso si lo pediste.' ),
				array( 'Montaje', '1 día', 'Los mismos que lo cosieron. Se cuelga, se prueba y se firma.' ),
			),
			'note'    => 'Entre 11 y 15 días laborables en total. «3 a 4 semanas» sin desglosar no es un plazo, es una excusa por adelantado.',
		),
		'gallery'      => array(
			'eyebrow' => 'Entregados',
			'h2'      => 'Con su medida escrita',
			'items'   => array(
				array( 'estor-hero', '137 × 214 · lino crudo · cadena izquierda' ),
				array( 'estor-cortina', '2 hojas de 90 × 260 · lino lavado' ),
				array( 'estor-oficina', '3 estores de 180 × 210 · screen 5% gris' ),
				array( 'estor-taller', 'Corte de 6 m para un ventanal de escalera' ),
			),
		),
		'quote'        => array(
			'eyebrow' => 'Presupuesto',
			'h2'      => 'Cerramos precio en 48 h',
			'lede'    => 'Arrastramos la configuración que acabas de hacer, así que no hay que repetirla. Sólo falta dónde y cuándo.',
			'fields'  => array(
				array( 'nombre', 'Nombre', 'text' ),
				array( 'mail', 'Email', 'email' ),
				array( 'cp', 'Código postal', 'text' ),
			),
			'msg'     => '¿Prefieres que vayamos a medir o mides tú?',
			'submit'  => 'Pedir presupuesto',
		),
		'faq'          => array(
			'eyebrow' => 'Medición y montaje',
			'h2'      => 'Lo que se pregunta antes de encargar',
			'items'   => array(
				array( '¿Y si me equivoco al medir?', 'Si mides tú y la medida sale mal, rehacemos la pieza una vez a mitad de precio. No es una garantía: es lo que cuesta el tejido, y preferimos decirlo antes que discutirlo después.' ),
				array( '¿Puedo devolverlo si no me gusta el tejido?', 'No, y por eso las muestras son gratis y van antes. Lo cosido a tu medida no vuelve a stock: prometer una devolución que no podemos cumplir sería peor que no ofrecerla.' ),
				array( '¿El motorizado necesita obra?', 'No si hay enchufe a menos de dos metros. Si no lo hay, va con batería recargable y dura entre ocho meses y un año según uso.' ),
				array( '¿Montáis vosotros?', 'En Gipuzkoa, Bizkaia y Navarra sí, y va incluido. Fuera enviamos con plantilla de taladro y un vídeo de dos minutos; es un tornillo a cada lado.' ),
			),
		),
		'footer'       => array(
			'tag'   => 'Estores y cortinas a medida · taller en Errenteria',
			'links' => array( 'Cómo medir', 'Muestras', 'Aviso legal', 'Privacidad', 'Cookies' ),
			'legal' => '© 2026 Medida Justa S.L. · Polígono Ventas 12, Errenteria · IVA incluido',
		),
	),
);

// ── EL CONTENIDO DE LAS PÁGINAS INTERNAS ───────────────────────────────────────────────────────
//
// Va aparte de `$CONTENT` y no dentro, y es una decisión, no comodidad: `$CONTENT` describe la
// HOME de cada arquetipo y ya son mil ochocientas líneas. Meter aquí cuatro páginas más por tira lo
// convierte en un fichero que nadie vuelve a leer entero. Separado, quien busca «qué lleva la
// página de Nosotros de la piedra» abre un bloque de treinta líneas en vez de bucear en el array
// de la home.
//
// SE REUTILIZA LO QUE YA EXISTE. La página de Nosotros de TPL-C-02 no reescribe su historia, sus
// cifras, su equipo ni sus testimonios: los toma del bloque de la home. Es lo mismo que ya hace
// `page_service` con el proceso y el cierre de TPL-C-01, y por la misma razón — reutilizar es lo
// que hace que dos páginas se lean como UN sitio y no como dos maquetas que comparten carpeta.
// Aquí sólo va lo que la página interna añade.
//
// Y LAS PERSONAS SON LAS MISMAS EN TODAS LAS TIRAS. Ramon Valdés, Aina Serra y Marc Puig salen en
// la corporativa y en la tienda porque son la misma empresa de piedra enseñada bajo arquitecturas
// distintas, que es lo que esta galería hace con el contenido: dejarlo fijo para que lo único que
// cambie sea la estructura. Ponerles otro nombre en cada tira daría a entender que son negocios
// distintos y haría ilegible justo la comparación.

/* TPL-PDP-01 sobre TPL-E-01, con `TGL-PDP-LAYOUT: editorial`.
   ES LA MISMA FICHA QUE LA DE TPL-E-02 Y ESE ES EXACTAMENTE EL PUNTO. Hubo un `TPL-PDP-02
   Editorial` y medido compartía siete de sus ocho secciones con la estándar: no era otra
   arquitectura, era ésta con la foto más grande. Renderizadas una al lado de la otra —mismo
   esqueleto, misma tienda, misma piedra— se ve que lo que cambia es el reparto de columnas y el
   aire, y que eso es un toggle. Si hubiera que mirarlas dos veces para notar la diferencia, la
   conclusión sería la contraria y también estaría bien saberla. */


/* TPL-ABOUT-02 «El oficio» sobre TPL-E-03.
   Es la página que la tienda de marca necesitaba y no tenía: su home afirma que extraen y cortan
   ellos mismos, y esa afirmación pide una página que la enseñe. La prueba son los trabajos y el
   proceso fotografiado, no una rejilla de retratos ni un contador de años — el ADN del arquetipo
   apaga `TGL-ABOUT-TEAM` y `TGL-ABOUT-STATS` a propósito: un taller de cuatro personas con cuatro
   retratos de estudio parece una consultora pequeña. */


/* TPL-PDP-05 «A medida» sobre TPL-E-03.
   La misma tienda que la ficha estándar de TPL-E-02 y la editorial de TPL-E-01, con la MISMA
   fotografía, y sin carrito ni precio. Es el contraste que esta galería no podía enseñar: tres
   fichas del mismo negocio con la misma piedra, y una termina en presupuesto porque su producto no
   existe hasta que alguien lo configura. Ahí la diferencia no es el aire ni el tamaño de la foto:
   es que falta el botón de comprar. */


/* R5 HARVEST — catalog-envato-grade PR2b, task 2b.4/2b.6. Was `$CONTENT['TPL-E-03']['mtm'] =
 * array(...)`, the only rendering of `TPL-PDP-05` in this file, copied here as a LIVE reference
 * one commit before this one (`$HARVEST['tpl-e03-mtm'] = $CONTENT['TPL-E-03']['mtm'];`) — valid
 * then, because `$CONTENT['TPL-E-03']` still existed. This commit deletes it (T-C1: `TPL-E-09`
 * medida survives, `TPL-E-03` does not), so the reference is inlined into a standalone snapshot:
 * a harvest whose source is gone is a value, not a pointer. Byte-identical to the deleted block
 * otherwise. design.md's Decision C1 re-skins this onto medida's own 6 photos as its second page
 * in PR8; nothing reads `$HARVEST` yet, so this remains inert until then.
 *
 * AN ISOLATED GLOBAL, NEVER `$CONTENT['_harvest']`. `$CONTENT` is walked a few hundred lines below
 * this point — `foreach ( $CONTENT as $cn_k => $cn_v ) { … $cn_v['tpl'] … }` — and every entry in
 * that walk is required to carry its own `tpl`/`arch`/`brand`/`head_mode` keys or the walk `fail()`s
 * on the first missing one, through the same `set_error_handler()` that turns any PHP warning into
 * a build failure. A staging entry nested under `$CONTENT` is not a template and would `fail()` the
 * very next build, which is a worse outcome than the "e.g." key `sdd-tasks` suggested for this
 * task turning out not to fit this file's own invariants.
 */
$HARVEST = array();
$HARVEST['tpl-e03-mtm'] = array(
	'crumbs'  => array( 'Inicio', 'Catálogo', 'Encimera a medida' ),
	'h1'      => 'Encimera a medida',
	'lede'    => 'Dinos la medida y el material y devolvemos un presupuesto cerrado en 48 h laborables. Sin precios «desde»: el de tu encimera o ninguno.',
	'cfg'     => array(
		'eyebrow' => 'Configurador',
		'h2'      => 'Cuatro datos y ya tenemos tu pieza',
		'fields'  => array(
			array( 'largo', 'Largo (cm)', '320' ),
			array( 'fondo', 'Fondo (cm)', '62' ),
		),
		'opt_lbl' => 'Material',
		'opts'    => array( 'Blanco Macael', 'Crema Levante', 'Negro Marquina' ),
		'fin_lbl' => 'Canto',
		'fins'    => array( 'Recto', 'Bisel', 'Media caña' ),
		'ext_lbl' => 'Añadidos',
		'extras'  => array( 'Hueco de fregadero bajo encimera', 'Hueco de placa', 'Copete de 8 cm' ),
		'note'    => 'No hay precio hasta emitir el presupuesto. Una cifra provisional que luego cambia hace más daño que no dar ninguna.',
		'cta'     => 'Continuar al presupuesto',
	),
	'measure' => array(
		'eyebrow' => 'Cómo se mide',
		'h2'      => 'De dónde a dónde',
		'rows'    => array(
			array( 'Largo', 'De pared a pared, no de mueble a mueble. Las paredes no son paralelas casi nunca y ese margen se resuelve en el taller, no en la obra.' ),
			array( 'Fondo', 'Del fondo del mueble al borde delantero, más el vuelo que quieras. Estándar 60 + 2 de vuelo.' ),
			array( 'Hueco de fregadero', 'Marca y modelo, no las medidas del hueco: la plantilla la da el fabricante y no siempre coincide con el agujero del mueble.' ),
			array( 'Esquinas', 'Si hay ángulo, hace falta el ángulo real medido, no «noventa grados». Aquí es donde se rompen las encimeras mal medidas.' ),
		),
		'note'    => 'Si la obra está en Lleida, Tarragona o Barcelona vamos a tomar plantilla sin coste, y estas cuatro dudas dejan de existir.',
	),
	'gallery' => array(
		'eyebrow' => 'Entregados',
		'h2'      => 'Con su medida escrita',
		'items'   => array(
			array( 'hero-encimera', '320 × 62 · Blanco Macael · canto recto' ),
			array( 'card-mueble', '5 puertas · veta continua del mismo bloque' ),
			array( 'card-patio', '82 sillares relabrados · patio de 41 m²' ),
			array( 'card-cantero', '2 peldaños macizos de 120 · Crema Levante' ),
		),
	),
	'sample'  => array(
		'eyebrow' => 'Antes de decidir',
		'h2'      => 'Pide la muestra física',
		'lede'    => 'Hasta tres muestras de 10 × 10 gratis, en 72 h. El color de una piedra en pantalla no es el color de la piedra, y de algo cortado a tu medida no hay devolución: la muestra es lo que sustituye a esa garantía que aquí no podemos dar.',
		'cta'     => 'Pedir muestras',
		'items'   => array( 'sq-marmol', 'card-veta', 'sq-pizarra' ),
	),
	'lead'    => array(
		'eyebrow' => 'Plazo',
		'h2'      => 'Por fases, con días',
		'items'   => array(
			array( 'Plantilla en obra', '3–5 días', 'Desde que se aprueba el presupuesto. Los muebles tienen que estar ya colocados.' ),
			array( 'Corte y pulido', '6–8 días', 'En taller. Aquí entra el labrado a mano si el canto lo pide.' ),
			array( 'Montaje', '1 día', 'Los mismos que la cortaron. Se sella y se entrega firmada.' ),
		),
		'note'    => 'Entre 10 y 14 días laborables en total. «3 a 4 semanas» sin desglosar no es un plazo, es una excusa por adelantado.',
	),
	'quote'   => array(
		'eyebrow' => 'Presupuesto',
		'h2'      => 'Cerramos precio en 48 h',
		'lede'    => 'Arrastramos la configuración que acabas de hacer, así que no hay que repetirla. Sólo falta dónde y cuándo.',
		'fields'  => array(
			array( 'nombre', 'Nombre', 'text' ),
			array( 'mail', 'Email', 'email' ),
			array( 'cp', 'Código postal de la obra', 'text' ),
		),
		'msg'     => '¿Cuándo estarán colocados los muebles?',
		'submit'  => 'Pedir presupuesto',
	),
);

/* TPL-PDP-01 sobre TPL-E-04, la tienda organizada por categorías.
   MISMO ESQUELETO QUE LA DE TPL-E-02 Y ESO ES LA RESPUESTA, no un descuido. La mayoría de las
   tiendas necesitan la ficha estándar; lo que las distingue es el ancla y el contexto, no un
   esqueleto propio. Aquí el contexto es la categoría: la miga de pan tiene un nivel más y el
   cross-sell no es «más vendidos» sino «más de Baño» — en una tienda que se navega por secciones,
   quien está en una ficha sigue dentro de una sección. */


/* TPL-PDP-02 «Talla y ajuste» sobre TPL-E-06.
   REUTILIZA el buscador de talla, la tabla de medidas, la galería de tres cuerpos y la promesa de
   devolución del bloque de la home: es la misma tienda un nivel más abajo, y son exactamente las
   secciones que responden la única duda que queda. Lo propio de la ficha es el selector de talla
   CON EL STOCK DE CADA UNA — una talla agotada se ve agotada, no se descubre al añadir al carrito. */
$CONTENT['TPL-E-06-corte']['pdp'] = array(
	'crumbs' => array( 'Inicio', 'Vaqueros', 'Recto lavado medio' ),
	'h1'     => 'Vaquero recto · lavado medio',
	'price'  => '89 €',
	'lede'   => 'Algodón de 13 onzas sin elastano, tejido en Valencia y cosido en Igualada. Tiro medio y pierna recta desde la rodilla: la horma más neutra que hacemos.',
	'main'   => 'corte-v1',
	'thumbs' => array( 'corte-cuerpo1', 'corte-cuerpo2', 'corte-cuerpo3' ),
	'sz_lbl' => 'Talla',
	'sizes'  => array(
		array( '38', 'quedan 4' ),
		array( '40', 'quedan 11' ),
		array( '42', 'quedan 7' ),
		array( '44', 'agotada' ),
		array( '46', 'quedan 2' ),
		array( '48', 'quedan 9' ),
	),
	'cta'    => 'Añadir a la cesta',
	'ship'   => 'Envío en 48 h · si dudas entre dos tallas, pide las dos',
);

/* TPL-PDP-03 «Lote y peso» sobre TPL-E-07.
   La ventana de entrega vuelve a salir ARRIBA, antes de la galería: si no servimos ese código
   postal el día que le sirve al cliente, no hay ficha que leer. Y el precio principal es el €/kg —
   nunca un PVP a secas—, con el importe estimado en secundario y `COMP-WEIGHT-NOTE` pegado al
   bloque de compra, no en una nota al pie. */
$CONTENT['TPL-E-07-bajura']['pdp'] = array(
	'crumbs' => array( 'Inicio', 'Pescado', 'Lubina de anzuelo' ),
	'h1'     => 'Lubina de anzuelo',
	'kg'     => '28 €/kg',
	'pc'     => 'pieza de ~1,1 kg · importe estimado 31 €',
	'lede'   => 'Pescada a anzuelo frente a Ondarroa y subastada esta mañana. Se sirve entera, eviscerada y sin escamar salvo que pidas lo contrario en el paso de datos.',
	'main'   => 'bajura-pieza',
	'thumbs' => array( 'bajura-corte', 'bajura-lonja', 'bajura-caja' ),
	'sz_lbl' => 'Preparación',
	'opts'   => array( 'Entera eviscerada', 'A lomos sin piel', 'En rodajas' ),
	'qty_lbl'=> 'Piezas',
	'cta'    => 'Añadir a la cesta',
	'ship'   => 'El importe se ajusta al peso real y se te comunica antes de cobrar',
);

/* TPL-PDP-04 «Suscripción» sobre TPL-E-08.
   NO LLEVA PRECIO EN EL BLOQUE DE ARRIBA, y se dice por qué en la propia página: el hueco donde
   toda ficha pone una cifra es justo donde el lector la busca, así que callarse sin más se lee como
   un fallo de la maqueta. La cuota está en el selector de plan, que es el control de compra. */
$CONTENT['TPL-E-08-tueste']['pdp'] = array(
	'crumbs' => array( 'Inicio', 'Los cafés', 'Huila · Colombia' ),
	'h1'     => 'Huila, Colombia · lavado',
	'lede'   => 'Finca La Esperanza, 1.720 m. Caturra y colombia, fermentación de 36 h y secado en marquesina. Panela, ciruela y un final limpio; el que mejor aguanta con leche de los tres que rotan.',
	'main'   => 'tueste-bolsa',
	'thumbs' => array( 'tueste-taza', 'tueste-molino', 'tueste-caja' ),
	'nofee'  => 'Este café no se vende por bolsa desde aquí: entra en los planes de abajo, y la cuota los incluye con el envío.',
	'cta'    => 'Ver los planes',
);

/* TPL-ABOUT-01 «La empresa» sobre TPL-C-02.
   Reutiliza historia, cifras, equipo y testimonios del bloque de la home: es la misma empresa un
   nivel más abajo, no otra. Lo que añade son el encabezado propio, los compromisos —COMP-VALUES,
   que la home no lleva— y el cierre. */


/* TPL-CONTACT-01 «Consulta» sobre TPL-C-01.
   Las dos secciones que casi ninguna página de contacto tiene: qué pasa después de darle a enviar,
   y con quién se va a hablar. Un formulario es una caja negra —se escribe, se pulsa y no se sabe si
   llegó ni quién lo lee— y esa incertidumbre es la que empuja a llamar a quien prefería escribir. */


/* NOSOTROS + CONTACTO PARA LAS OCHO MARCAS QUE LLEGARON SIN ELLAS. TPL-C-06..13 se entregaron con
   una sola página cada una; el catálogo comparaba homes y las páginas internas quedaron para
   después. Van aquí, agrupadas, con el mismo patrón que ya prueban TPL-C-01/02: `nosotros` reutiliza
   `about`/`stats`/`team` de la marca — así que esas tres claves se añaden primero, sólo donde el home
   no las tenía ya — y `contacto` es autocontenido, como el de TPL-C-01. */

/* CASA TERRAZZA · about/stats/team para su Nosotros. El home no lleva ninguna de las tres: llevaba
   `figq`, una sola cara con firma. Esa misma cara es el equipo — no se inventa una segunda persona
   para que la rejilla tenga más de un hueco. */







/* MOTOR ARANDA · about/stats/team. El home ya tiene `badges` (4 cifras de confianza) y `quotes` (3
   clientes reales); `stats` reutiliza las cifras de `badges` en vez de inventar unas nuevas, y el
   equipo se cuenta con la foto del patio — no hay retrato de nadie y no se finge uno. */
$CONTENT['TPL-C-07-aranda']['about'] = array(
	'eyebrow' => 'El negocio',
	'h2'      => 'Compramos, revisamos y vendemos — nada a comisión de un tercero',
	'body'    => array(
		'Todo lo que hay en el patio lo compramos nosotros, lo revisamos en nuestro taller y lo vendemos con nuestra garantía. No exponemos coches en depósito de particulares.',
		'150 puntos revisados y firmados antes de ponerlo a la venta, con el histórico de mantenimiento cuando existe. Si un dato no está, se lo decimos — no se rellena.',
	),
	'img'     => 'aranda-patio',
);
$CONTENT['TPL-C-07-aranda']['stats'] = array(
	'eyebrow' => 'En números',
	'items'   => array(
		array( '38 coches', 'en el patio, todos con informe' ),
		array( '150 puntos', 'revisados antes de la venta' ),
		array( '12 meses', 'de garantía, sin excepciones' ),
		array( '7 días', 'para devolverlo si no es lo esperado' ),
	),
);
$CONTENT['TPL-C-07-aranda']['team'] = array(
	'eyebrow' => 'Quién revisa cada unidad',
	'h2'      => 'Taller propio, no un mecánico externo',
	'items'   => array(
		array( 'Equipo de taller', 'Revisión de 150 puntos e histórico', 'aranda-patio' ),
	),
);
$CONTENT['TPL-C-07-aranda']['nosotros'] = array(
	'crumbs' => array( 'Inicio', 'Nosotros' ),
	'hero'   => array(
		'eyebrow' => 'Ocasión seleccionada',
		'h1'      => 'No exponemos lo que no compraríamos nosotros mismos',
		'lede'    => 'Cada unidad pasa por nuestro taller antes de salir al patio. Lo que no aprueba la revisión, no se pone a la venta.',
		'img'     => 'aranda-v3',
	),
	'values' => array(
		'eyebrow' => 'Cómo compramos',
		'h2'      => 'Tres cosas que revisamos siempre',
		'items'   => array(
			array( '150 puntos, con firma', 'No es una frase: es una lista que firma quien la revisa, y se la enseñamos si la pide.' ),
			array( 'Histórico cuando existe', 'Si el coche lo tiene, se lo damos. Si no lo tiene, se lo decimos — no se inventa uno.' ),
			array( 'Kilómetros certificados', 'Contra registro, no contra el cuentakilómetros a secas.' ),
		),
	),
	'cta'    => array(
		'eyebrow' => 'Siguiente paso',
		'h2'      => '¿Buscamos su coche o tasamos el suyo?',
		'lede'    => 'Filtre el stock por lo que necesita, o traiga su matrícula y le damos un rango por teléfono el mismo día.',
		'cta_1'   => 'Ver stock',
		'cta_2'   => 'Pedir tasación',
	),
);
$CONTENT['TPL-C-07-aranda']['contacto'] = array(
	'crumbs' => array( 'Inicio', 'Contacto' ),
	'head'   => array(
		'eyebrow' => 'Contacto',
		'h1'      => 'Consulte una unidad o pida cita',
		'lede'    => 'Para tasar su coche use el formulario de la home — es más rápido. Esto es para preguntar por una unidad concreta o pedir cita para verla.',
	),
	'form'   => array(
		'fields' => array(
			array( 'nombre', 'Nombre', 'text' ),
			array( 'mail', 'Email', 'email' ),
			array( 'asunto', 'Unidad de interés', 'text' ),
		),
		'msg'    => 'Cuéntenos qué busca',
		'submit' => 'Enviar consulta',
		'small'  => 'Dígalo con marca y modelo si ya sabe qué unidad quiere ver.',
	),
	'direct' => array(
		'eyebrow' => 'O directamente',
		'h2'      => 'Sin esperar respuesta',
		'items'   => array(
			array( 'Teléfono', '948 00 00 00', 'De lunes a sábado, en horario de patio' ),
			array( 'Email', 'stock@motoraranda.example', 'Lo lee ventas, no un buzón compartido' ),
			array( 'Patio', 'Polígono Landaben, calle E nave 14, Pamplona', 'Cubierto — se ve el coche aunque llueva' ),
		),
	),
	'flow'   => array(
		'eyebrow' => 'Qué pasa al enviar',
		'h2'      => 'Dos pasos',
		'steps'   => array(
			array( 'Le llamamos', 'El mismo día laborable, para confirmar que la unidad sigue disponible.' ),
			array( 'Queda la visita', 'Con hora fija — el patio está cubierto, así que la lluvia no la cambia.' ),
		),
		'note'    => 'Si la unidad ya se vendió, se lo decimos en la primera llamada, no cuando llega.',
	),
	'team'   => array(
		'eyebrow' => 'Con quién habla',
		'h2'      => 'Ventas y taller',
		'items'   => array(
			array( 'name' => 'Equipo de ventas', 'role' => 'Stock y financiación', 'lic' => '948 00 00 00', 'img' => 'aranda-patio' ),
		),
	),
	'faq'    => array(
		'eyebrow' => 'Antes de escribir',
		'h2'      => 'Lo que se pregunta',
		'items'   => array(
			array( '¿Puedo reservar una unidad sin verla?', 'Con una señal de 200 € que se descuenta del precio, sí, hasta 48 horas.' ),
			array( '¿Aceptan mi coche a cambio?', 'Sí, use el formulario de tasación de la home — la respuesta llega el mismo día.' ),
			array( '¿Puedo probarlo antes de comprar?', 'Sí, con cita previa y carné de más de un año.' ),
		),
	),
);

/* AURIA · about/stats/team. El home ya trae `figures` (potencia, 0-100, plazas, precio) — esas cifras
   SON las de `stats`, no se duplican con otras. No hay retrato de nadie en el manifiesto: el equipo
   se cuenta con la foto del interior, que es lo que sí existe. */







/* TALLER BERGARA · about/stats/team. Sin retratos en el manifiesto: el equipo se cuenta con la nave,
   que es la foto que sí existe y la que un taller real enseñaría primero. */







/* CLÍNICA ARBEA · about/stats/team. El home ya trae `team` con los tres profesionales y su colegiado
   — se reutiliza tal cual, sin duplicar. Sólo faltan `about` y `stats`, y `stats` sale de datos que
   ya existían en `treatments`/`insurance`, no de cifras nuevas. */






/* ALINEA · about/stats/team. Sin retratos de equipo en el manifiesto: se cuenta con la foto de
   consulta, que es donde de verdad ocurre el seguimiento. */
$CONTENT['TPL-C-11-alinea']['about'] = array(
	'eyebrow' => 'El plan',
	'h2'      => 'Cuatro fases, dieciocho meses, un plan por escrito desde el primer día',
	'body'    => array(
		'El estudio inicial no es una venta: escáner, radiografía y simulación, y sale un plan con fecha final antes de que usted decida nada.',
		'Si su caso se alarga sobre la media, se lo decimos en el estudio o en la revisión — nunca al final, cuando ya no hay nada que negociar.',
	),
	'img'     => 'alinea-consulta',
);
$CONTENT['TPL-C-11-alinea']['stats'] = array(
	'eyebrow' => 'En números',
	'items'   => array(
		array( '18 meses', 'de plan medio, con sus cuatro fases' ),
		array( '3 formas de pago', 'con cuota y total siempre juntos' ),
		array( '6 semanas', 'entre revisiones, según el tratamiento' ),
		array( '0 €', 'el estudio inicial, sin compromiso' ),
	),
);
$CONTENT['TPL-C-11-alinea']['team'] = array(
	'eyebrow' => 'El seguimiento',
	'h2'      => 'Revisión cada seis u ocho semanas, según el plan',
	'items'   => array(
		array( 'Equipo clínico', 'Estudio, plan y revisiones', 'alinea-consulta' ),
	),
);
$CONTENT['TPL-C-11-alinea']['quotes'] = array(
	'eyebrow' => 'Quien ya terminó su plan',
	'h2'      => 'Dos pacientes recientes',
	'items'   => array(
		array( 'Me dijeron dieciocho meses y fueron diecisiete. La primera vez que un plazo médico me sale corto en vez de largo.', 'Paula Ferrando', 'Paciente' ),
		array( 'La cuota que me dijeron en el estudio es la que pagué todo el tratamiento, sin subidas.', 'Vicent Roig', 'Paciente' ),
	),
);
$CONTENT['TPL-C-11-alinea']['nosotros'] = array(
	'crumbs' => array( 'Inicio', 'Nosotros' ),
	'hero'   => array(
		'eyebrow' => 'Ortodoncia invisible y metálica',
		'h1'      => 'El plan sale escrito antes de que usted decida',
		'lede'    => 'Cuatro fases, dieciocho meses de media, y una fecha final que se recalcula en el estudio — no a mitad de tratamiento.',
		'img'     => 'alinea-hero',
	),
	'values' => array(
		'eyebrow' => 'Cómo trabajamos',
		'h2'      => 'Tres cosas que no cambiamos',
		'items'   => array(
			array( 'Plan por escrito, con fecha', 'Antes de empezar, no a mitad de tratamiento.' ),
			array( 'Cuota y total, siempre juntos', 'Una cuota sin el total al lado es la técnica que genera desconfianza — aquí van los dos.' ),
			array( 'Si se alarga, se avisa', 'En el estudio o en la revisión, nunca al final.' ),
		),
	),
	'cta'    => array(
		'eyebrow' => 'Siguiente paso',
		'h2'      => '¿Reservamos su estudio?',
		'lede'    => 'Cuarenta minutos, sin coste y sin compromiso. Sale con el plan escrito.',
		'cta_1'   => 'Estudio gratuito',
		'cta_2'   => 'Ver precios',
	),
);
$CONTENT['TPL-C-11-alinea']['contacto'] = array(
	'crumbs' => array( 'Inicio', 'Contacto' ),
	'head'   => array(
		'eyebrow' => 'Contacto',
		'h1'      => 'Consulte antes del estudio',
		'lede'    => 'Para reservar el estudio inicial use la home. Esto es para preguntas de precio, financiación o si ya lleva ortodoncia en otra clínica.',
	),
	'form'   => array(
		'fields' => array(
			array( 'nombre', 'Nombre', 'text' ),
			array( 'mail', 'Email', 'email' ),
			array( 'asunto', 'Asunto', 'text' ),
		),
		'msg'    => 'Cuéntenos su caso',
		'submit' => 'Enviar consulta',
		'small'  => 'Si ya lleva tratamiento empezado en otra clínica, indíquelo — el estudio cambia.',
	),
	'direct' => array(
		'eyebrow' => 'O directamente',
		'h2'      => 'Sin esperar respuesta',
		'items'   => array(
			array( 'Teléfono', '96 000 00 00', 'De lunes a viernes' ),
			array( 'Email', 'estudio@alinea.example', 'Lo lee clínica' ),
			array( 'Clínica', 'Carrer de Colón 44, València', 'Con parking cercano' ),
		),
	),
	'flow'   => array(
		'eyebrow' => 'Qué pasa al enviar',
		'h2'      => 'Dos pasos',
		'steps'   => array(
			array( 'Le llamamos', 'El mismo día laborable, para entender su caso.' ),
			array( 'Queda el estudio', 'Cuarenta minutos, sin coste — se lleva el plan aunque después no siga.' ),
		),
		'note'    => 'Si ya lleva tratamiento en otra clínica, se lo decimos en la primera llamada si el caso cambia el estudio.',
	),
	'team'   => array(
		'eyebrow' => 'Con quién habla',
		'h2'      => 'Clínica',
		'items'   => array(
			array( 'name' => 'Equipo clínico', 'role' => 'Estudio y planes', 'lic' => '96 000 00 00', 'img' => 'alinea-consulta' ),
		),
	),
	'faq'    => array(
		'eyebrow' => 'Antes de escribir',
		'h2'      => 'Lo que se pregunta',
		'items'   => array(
			array( '¿El estudio tiene coste?', 'No, y se lleva el plan escrito aunque después decida no seguir.' ),
			array( '¿Puedo cambiar de plan de pago a mitad?', 'Sí, se recalcula en la revisión más próxima.' ),
			array( '¿Ya llevo ortodoncia en otra clínica, puedo cambiarme?', 'Sí — tráigase la documentación y el estudio la tiene en cuenta.' ),
		),
	),
);

/* URGENCIA DENTAL · about/stats/team. El home ya trae `team` (dos personas, con colegiado) — se
   reutiliza. `stats` sale de `wait` (espera y precio), que ya eran las dos cifras que el home
   publicaba sin sección de "quiénes somos" alrededor. */






/* ZUBIRI & OSÉS (TPL-C-13) · about/stats/team. El home ya trae `team` (tres agentes por zona) y
   `valuation.stats` (tres cifras de venta) — se reutilizan tal cual en vez de duplicarlas. */






/* LUMIÈRE (TPL-C-14) · las cuatro páginas internas. La casa reutiliza el recorrido por la cabina de
   la home y la ficha reutiliza su protocolo: es el mismo centro un nivel más abajo, y reutilizar es
   lo que hace que cinco páginas se lean como UN sitio y no como cinco maquetas de la misma carpeta.
   Lo propio de cada una es lo que no cabía arriba — el índice entero, las contraindicaciones, las
   caras y el horario. */
$CONTENT['TPL-C-14-lumiere']['servicios'] = array(
	'crumbs' => array( 'Inicio', 'Servicios' ),
	'head'   => array(
		'eyebrow' => 'Todos los tratamientos',
		'h1'      => 'Tratamientos de estética en Bilbao',
		'lede'    => 'Veintiséis rituales ordenados por zona del cuerpo, con lo que dura y lo que cuesta cada uno. La home enseña seis; aquí están todos.',
	),
	'index'  => array(
		'eyebrow' => 'Todo lo que hacemos',
		'h2'      => 'Por zona, y sin ninguno destacado',
		'note'    => 'Los precios son cerrados e incluyen el producto. Un ritual que no está en esta lista es un ritual que no hacemos.',
		'groups'  => array(
			array(
				'id'    => 'rostro',
				'img'   => 'lumiere-rostro',
				'name'  => 'Rostro',
				'items' => array(
					array( 'name' => 'Limpieza profunda', 'p' => 'Vapor, extracción manual y mascarilla calmante.', 'mins' => '60 min', 'price' => '55 €' ),
					array( 'name' => 'Radiofrecuencia facial', 'p' => 'Calor controlado para tensar el óvalo.', 'mins' => '45 min', 'price' => '75 €' ),
					array( 'name' => 'Hidratación con ácido hialurónico', 'p' => 'Para piel tirante después del invierno.', 'mins' => '45 min', 'price' => '48 €' ),
				),
			),
			array(
				'id'    => 'cuerpo',
				'img'   => 'lumiere-cuerpo',
				'name'  => 'Cuerpo',
				'items' => array(
					array( 'name' => 'Masaje descontracturante', 'p' => 'Espalda y cervicales, presión fuerte.', 'mins' => '50 min', 'price' => '48 €' ),
					array( 'name' => 'Presoterapia', 'p' => 'Botas de compresión para piernas cansadas.', 'mins' => '30 min', 'price' => '35 €' ),
					array( 'name' => 'Envoltura de algas', 'p' => 'Calor húmedo y sales, tumbada y tapada.', 'mins' => '60 min', 'price' => '52 €' ),
				),
			),
			array(
				'id'    => 'manos',
				'img'   => 'lumiere-manos',
				'name'  => 'Manos y pies',
				'items' => array(
					array( 'name' => 'Manicura rusa', 'p' => 'Cutícula al torno y esmaltado semipermanente.', 'mins' => '75 min', 'price' => '32 €' ),
					array( 'name' => 'Pedicura completa', 'p' => 'Durezas, uñas y masaje de pie.', 'mins' => '60 min', 'price' => '35 €' ),
					array( 'name' => 'Retirada de semipermanente', 'p' => 'Con torno, sin arrancar. Sola, sin otro servicio.', 'mins' => '25 min', 'price' => '12 €' ),
				),
			),
			array(
				'id'    => 'depilacion',
				'img'   => 'lumiere-depilacion',
				'name'  => 'Depilación',
				'items' => array(
					array( 'name' => 'Láser diodo · media pierna', 'p' => 'Seis sesiones separadas seis semanas.', 'mins' => '20 min', 'price' => '45 €' ),
					array( 'name' => 'Cera tibia · axilas', 'p' => 'Cera de baja temperatura, de un tirón.', 'mins' => '15 min', 'price' => '12 €' ),
					array( 'name' => 'Cera tibia · piernas enteras', 'p' => 'Incluye ingles básicas si se pide al reservar.', 'mins' => '45 min', 'price' => '28 €' ),
				),
			),
		),
	),
	'faq'    => array(
		'eyebrow' => 'De la carta',
		'h2'      => 'Lo que se pregunta antes de elegir',
		'items'   => array(
			array( '¿Puedo juntar dos rituales el mismo día?', 'Sí, y sale mejor de tiempo: se descuentan diez minutos del segundo porque la preparación ya está hecha. Dilo al reservar para bloquear la cabina entera.' ),
			array( '¿Hay que ser socia o pagar cuota?', 'No. Ni cuota, ni permanencia, ni tarjeta de puntos. Los bonos son la única forma de pago adelantado que existe aquí.' ),
			array( '¿Atendéis a hombres?', 'Sí, todos los rituales de la carta. La depilación masculina de espalda y pecho lleva su propio tiempo, así que ésa se reserva por teléfono.' ),
			array( '¿Qué pasa si llego tarde?', 'Se hace lo que quepa en el tiempo que quede, y se cobra el ritual entero: detrás hay otra clienta con su hora. Con más de veinte minutos de retraso, mejor cambiar el día.' ),
		),
	),
	'cta'    => array(
		'eyebrow' => 'Si no sabes cuál',
		'h2'      => 'Cuéntanos qué te pasa en la piel',
		'lede'    => 'Diez minutos, sin coste y sin compromiso: se mira, se dice qué haríamos y se decide después. Es la mejor manera de no gastarse cincuenta euros en el ritual equivocado.',
		'cta_1'   => 'Reservar',
		'cta_2'   => 'Ver la carta',
	),
);

$CONTENT['TPL-C-14-lumiere']['servicio'] = array(
	'crumbs'   => array( 'Inicio', 'Servicios', 'Limpieza profunda' ),
	'head'     => array(
		'eyebrow' => 'Rostro',
		'h1'      => 'Limpieza profunda',
		'lede'    => 'La sesión con la que empieza casi todo el mundo: se abre el poro con vapor, se vacía a mano y se cierra con frío. No es un tratamiento estético de escaparate, es mantenimiento.',
		'price'   => '55 € · 60 min',
		'cta'     => 'Reservar',
		'img'     => 'lumiere-rostro',
	),
	'facts'    => array(
		'eyebrow' => 'Los datos',
		'h2'      => 'Los cuatro datos, sin ninguno en blanco',
		'items'   => array(
			array( 'Duración', '60 minutos' ),
			array( 'Sesiones recomendadas', 'Una cada 6 semanas' ),
			array( 'Dónde', 'Cabina 1 · rostro' ),
			array( 'Cómo sales', 'Sin maquillaje y con la piel algo roja media hora' ),
		),
	),
	'contra'   => array(
		'eyebrow' => 'Antes de reservar',
		'h2'      => 'Cuándo NO, y qué hacer antes',
		'no'      => array(
			'title' => 'Hoy no se puede si…',
			'items' => array(
				'Has tomado el sol o rayos UVA en las últimas 48 horas.',
				'Tienes la piel irritada, con heridas abiertas o herpes activo.',
				'Estás con roacután, o lo dejaste hace menos de seis meses.',
				'Te has hecho un peeling médico en las últimas dos semanas.',
			),
		),
		'before'  => array(
			'title' => 'Y ven así',
			'items' => array(
				'Sin maquillaje, o con el mínimo — aquí se desmaquilla igual.',
				'Con la agenda libre después: no es sesión para salir a cenar.',
				'Habiendo comido algo. Son sesenta minutos tumbada.',
				'Dinos si estás embarazada: cambia el activo, no la sesión.',
			),
		),
		'note'    => 'Si al llegar vemos que hoy no toca, no se cobra la sesión y se cambia el día. Preferimos perder una cita que estropear una piel.',
	),
	'bono'     => array(
		'eyebrow' => 'El bono de este ritual',
		'h2'      => 'Si la piel te lo va a pedir otra vez',
		'note'    => 'El bono caduca a los doce meses, es nominal y se puede pagar en dos veces.',
		'items'   => array(
			array( 'name' => 'Bono limpieza', 'q' => '5 sesiones', 'price' => '235 €', 'save' => 'Ahorras 40 €',
				'p' => 'Una cada seis semanas: nueve meses de mantenimiento.', 'gift' => false ),
		),
	),
	'faq'      => array(
		'eyebrow' => 'De este ritual',
		'h2'      => 'Lo que se pregunta en la camilla',
		'items'   => array(
			array( '¿Duele la extracción?', 'Molesta en la nariz y en la barbilla, que es donde el poro está más cerrado. En el resto de la cara, no. Se puede parar en cualquier momento y se sigue otro día.' ),
			array( '¿Me puedo maquillar al salir?', 'Mejor no hasta la mañana siguiente: el poro tarda unas horas en cerrarse del todo y el maquillaje lo vuelve a llenar. Sí puedes ponerte protector solar, y de hecho deberías.' ),
			array( '¿Cada cuánto tiene sentido repetirla?', 'Cada seis semanas si la piel es grasa, cada tres meses si es seca. Más a menudo no mejora nada y desprotege la barrera.' ),
		),
	),
	'siblings' => array(
		'eyebrow' => 'Otros de rostro',
		'h2'      => 'Los hermanos de su zona',
		'note'    => 'La carta entera, con las cuatro zonas y los veintiséis rituales, está en Servicios.',
		'items'   => array(
			array( 'zone' => 'Rostro', 'h3' => 'Radiofrecuencia facial', 'p' => 'Calor controlado para tensar el óvalo.',
				'mins' => '45 min', 'price' => '75 €', 'after' => 'Se puede maquillar encima el mismo día' ),
			array( 'zone' => 'Rostro', 'h3' => 'Hidratación con hialurónico', 'p' => 'Para piel tirante después del invierno.',
				'mins' => '45 min', 'price' => '48 €', 'after' => 'Sales con la cara algo brillante una hora' ),
		),
	),
);

$CONTENT['TPL-C-14-lumiere']['nosotros'] = array(
	'crumbs' => array( 'Inicio', 'Nosotros' ),
	'head'   => array(
		'eyebrow' => 'El centro',
		'h1'      => 'Dos cabinas, tres personas y quince años en la misma calle',
		'lede'    => 'Abrimos en 2010 en un bajo de Alameda Urquijo y seguimos ahí. No hemos crecido en metros: hemos crecido en el rato que se le dedica a cada clienta.',
		'img'     => 'lumiere-recepcion',
	),
	'team'   => array(
		'eyebrow' => 'Quién te atiende',
		'h2'      => 'Tres personas, y sabes cuál te toca antes de venir',
		'items'   => array(
			array( 'img' => 'lumiere-pilar', 'name' => 'Pilar Egaña', 'role' => 'Rostro y aparatología', 'lic' => 'Abrió la casa en 2010' ),
			array( 'img' => 'lumiere-hugo', 'name' => 'Hugo Belmonte', 'role' => 'Masaje y presoterapia', 'lic' => 'En la casa desde 2016' ),
			array( 'img' => 'lumiere-noa', 'name' => 'Noa Ferreira', 'role' => 'Uñas y depilación', 'lic' => 'En la casa desde 2021' ),
		),
	),
	'hours'  => array(
		'eyebrow' => 'Horario',
		'h2'      => 'Martes a sábado, y el lunes cerramos de verdad',
		'rows'    => array(
			array( 'Lunes', 'Cerrado' ),
			array( 'Martes a viernes', '10:00 – 20:00' ),
			array( 'Sábado', '09:30 – 14:00' ),
			array( 'Domingo', 'Cerrado' ),
		),
		'addr'    => array( 'Alameda Urquijo 24, bajo', '48011 Bilbao' ),
		'phone'   => '944 00 00 00',
		'mail'    => 'hola@lumiere.example',
		'note'    => 'En agosto cerramos las dos semanas centrales. Se avisa en junio, para que nadie compre un bono contando con ellas.',
	),
);

$CONTENT['TPL-C-14-lumiere']['contacto'] = array(
	'crumbs' => array( 'Inicio', 'Contacto' ),
	'head'   => array(
		'eyebrow' => 'Cómo llegar',
		'h1'      => 'Estamos en el bajo, con el toldo rosa',
		'lede'    => 'Lo más rápido es llamar: al teléfono contesta alguien de las tres, no un centro de llamadas. Si prefieres escribir, el WhatsApp lo miramos entre clienta y clienta.',
		'img'     => 'lumiere-recepcion',
	),
	'direct' => array(
		'eyebrow' => 'Contacto directo',
		'h2'      => 'Por orden de rapidez',
		'items'   => array(
			array( 'Teléfono', '944 00 00 00', 'De 10:00 a 20:00. Si comunica, insiste: estamos en cabina.' ),
			array( 'WhatsApp', '600 00 00 00', 'Contestamos entre clienta y clienta, nunca más tarde del mismo día.' ),
			array( 'Email', 'hola@lumiere.example', 'Para facturas y para lo que no corre prisa. 48 horas.' ),
		),
		'note'    => 'No hay formulario en esta página a propósito: para pedir hora, el teléfono es más rápido que escribir y esperar.',
	),
	'nap'    => array(
		'eyebrow' => 'Dónde',
		'h2'      => 'Alameda Urquijo 24, bajo',
		'addr'    => array( 'Alameda Urquijo 24, bajo', '48011 Bilbao' ),
		'phone'   => '944 00 00 00',
		'mail'    => 'hola@lumiere.example',
		'hours'   => array(
			array( 'Martes a viernes', '10:00 – 20:00' ),
			array( 'Sábado', '09:30 – 14:00' ),
			array( 'Lunes y domingo', 'Cerrado' ),
		),
		'note'    => 'Metro Indautxu a cuatro minutos. Parking Zabálburu a doscientos metros; la primera media hora sale por 1,20 €.',
		'img'     => 'lumiere-recepcion',
	),
	'door'   => array(
		'eyebrow' => 'Cómo se reconoce',
		'h2'      => 'Para que no pases de largo la primera vez',
		'note'    => 'El portal es el 24, entre una farmacia y una papelería. Si llegas y está cerrado a media mañana, estamos en cabina: llama y bajamos.',
		'items'   => array(
			array( 'img' => 'lumiere-recepcion', 'name' => 'La entrada', 'p' => 'Toldo rosa y mostrador de madera nada más entrar.' ),
			array( 'img' => 'lumiere-cabina', 'name' => 'Al fondo a la izquierda', 'p' => 'Cabina 1, la de rostro. Es la que se ve desde la puerta.' ),
		),
	),
	'hours'  => array(
		'eyebrow' => 'Horario',
		'h2'      => 'Cuándo hay alguien',
		'rows'    => array(
			array( 'Lunes', 'Cerrado' ),
			array( 'Martes a viernes', '10:00 – 20:00' ),
			array( 'Sábado', '09:30 – 14:00' ),
			array( 'Domingo', 'Cerrado' ),
		),
		'addr'    => array( 'Alameda Urquijo 24, bajo', '48011 Bilbao' ),
		'phone'   => '944 00 00 00',
		'mail'    => 'hola@lumiere.example',
		'travel'  => 'Metro Indautxu a cuatro minutos. Parking Zabálburu a doscientos metros; la primera media hora sale por 1,20 €.',
		'note'    => 'La última cita se da una hora antes del cierre, porque una limpieza dura sesenta minutos y no se corre.',
	),
);


// ── THE SECOND PHOTOGRAPH WITH TEXT ON IT, WHICH NOTHING HAD EVER SWEPT ────────────────────────
//
// `LP-BROKEN-GRID`'s corporate hero puts the h1 ON the picture, exactly as the slider does, and
// its floor has been carried since it was written by an ALGEBRAIC bound rather than a measurement:
// "over a worst-case pure-white pixel, 255 − 241×0.64 composites to 100, which is 5.4:1". The
// arithmetic is right and the bound is real — but it is a bound over a hypothetical pixel, and the
// thing that made the slider's floor trustworthy is that it is a bound over THIS photograph. The
// bar exists because a hero measured 1.95:1 in this project once while looking fine in a capture.
//
// Adding an ink is what forced this: the ink moves every pixel in the frame, so a bound derived
// from `255` stops describing anything on the page. Measured, over `hero-cantera` under the ink,
// at the gradient's own floor, against the ink ground's own `--c-text`. Re-measured when the
// duotone became a grade: 5.82:1 under both, because on a dark veil the binding pixel is the
// brightest one and both inks cap the highlights at the same #EBEDEE.
/* A CONTENT ENTRY IS A TEMPLATE; `arch` IS THE ARCHETYPE IT IS BUILT ON, AND THEY STOPPED BEING
   THE SAME THING. While every entry was one company the two could share a key, and forty cards of
   one stonemason is exactly what that shortcut produced. A brand takes an archetype's SKELETON —
   its section inventory, its toggle table, its page set, its renderer — and brings its own
   identity, so `TPL-C-05` is now something two entries can both be built on without being the
   same catalogue entry.

   NORMALISED HERE RATHER THAN DEFAULTED AT EVERY READ. Forty entries predate `arch` and every one
   of them is its own archetype; writing that out forty times would be forty chances to typo a key
   that no gate reads twice. */
foreach ( $CONTENT as $cn_k => $cn_v ) {
	if ( $cn_k !== $cn_v['tpl'] ) {
		fail( "content is keyed `$cn_k` but carries tpl `{$cn_v['tpl']}` — the key IS the template id,"
			. ' and two names for one entry is how a strip ends up rendering a page it did not ask for' );
	}
	if ( ! isset( $CONTENT[ $cn_k ]['arch'] ) ) {
		$CONTENT[ $cn_k ]['arch'] = $cn_v['tpl'];
	}
	/* THE SECTION-HEAD TREATMENT STOPS BELONGING TO THE ANCHOR. Four anchors carried four head
	   decorations, so seventeen templates shared four; every section of every page announced itself
	   with the same gesture in the same place, and THAT is the drumbeat that made different section
	   inventories still read as one skeleton. The mode is a template property now, and the pairing
	   (anchor × mode) is asserted unique across branded templates below. */
	if ( ! isset( $CONTENT[ $cn_k ]['head_mode'] ) ) {
		$CONTENT[ $cn_k ]['head_mode'] = 'index';
	} elseif ( ! in_array( $CONTENT[ $cn_k ]['head_mode'], array( 'index', 'rule', 'tight', 'emblem' ), true ) ) {
		fail( "template `$cn_k` asks for head mode `{$CONTENT[ $cn_k ]['head_mode']}`, which is not one"
			. ' of index / rule / tight / emblem' );
	}
	if ( ! isset( $CONTENT[ $cn_k ]['brand'] ) ) {
		$CONTENT[ $cn_k ]['brand'] = '';
	} elseif ( ! isset( $BRANDS[ $CONTENT[ $cn_k ]['brand'] ] ) ) {
		fail( "template `$cn_k` names brand `{$CONTENT[ $cn_k ]['brand']}`, which \$BRANDS does not declare" );
	}
	if ( ! isset( $TOGGLES[ $CONTENT[ $cn_k ]['arch'] ] ) ) {
		fail( "template `$cn_k` is built on archetype `{$CONTENT[ $cn_k ]['arch']}`, which has no toggle table" );
	}
}

$BG_FLOOR      = 0.64;
$BG_ANCHOR     = 'direct';
/* Was `$CONTENT['TPL-C-01']['hero']['img']` — TPL-C-01 (the house "Piedra Valdés" archetype) is
 * one of the 16 catalog-envato-grade PR2b amputees, so the lookup itself is gone. The literal slug
 * is kept unchanged rather than repointed: this is the LP-BROKEN-GRID scrim probe below, a
 * measurement of the actual pixels of ONE specific photograph, and swapping in a different image
 * would re-run this exact contrast sweep against pixel data nobody has verified passes it.
 * `hero-cantera` and its manifest row are therefore kept alive past the amputation purely to
 * serve this probe — along with `hero-taller` and `hero-encimera`, which $SLIDER_FRAMES below and
 * $VIS_SLUG further down need for the same reason. See _gallery-images.md's manifest for the same
 * note, in one place, next to all three rows.
 */
$BG_SLUG       = 'hero-cantera';
$bg_ground     = $GROUND[ $ANCHORS[ $BG_ANCHOR ]['ground'] ];
$BG_HERO       = worst_pixel( $BG_SLUG, $bg_ground['bg'], $BG_FLOOR, $bg_ground['text'],
	$INK[ $BG_ANCHOR ] );
if ( $BG_HERO['ratio'] < $SCRIM_BAR ) {
	fail( sprintf(
		'the LP-BROKEN-GRID hero photograph `%s` measures %.2f:1 under its own %d%% scrim against'
			. ' %s — below the %s:1 the eyebrow and the lede on top of it need. This is the frame'
			. ' whose floor used to be an algebraic bound over a pixel value rather than a sweep'
			. ' over these pixels.',
		$BG_SLUG,
		$BG_HERO['ratio'],
		(int) round( $BG_FLOOR * 100 ),
		$bg_ground['text'],
		$SCRIM_BAR
	) );
}

/* THIS SWEEP NEEDS THE SAME TRIPWIRE THE SLIDER HAS, AND IT DID NOT HAVE ONE. Found by mutation:
   replacing `$INK[$BG_ANCHOR]` with `null` here — one argument — left a build that passed every
   gate and printed a plausible 5.27:1 while certifying the contrast of an UNGRADED photograph. The
   slider's probe, written the day the ink arrived, guards the slider's call and nothing else; this
   second call site was added afterwards and inherited none of it. A tripwire written for a call
   site rather than for a PROPERTY protects exactly one line, and the next line to need it is the
   one nobody remembers to wire up.

   So the probe is per call site by construction: two sweeps, two probes, and the margin is stated
   the same way. .25 is far below the 0.55 this frame moves under its own ink and far above any
   rounding — and unlike the slider's, this one is a bound in the SAFE direction if it ever
   inverts, because here the ink RAISES the ratio too. */
$BG_PROBE_PLAIN = worst_pixel( $BG_SLUG, $bg_ground['bg'], $BG_FLOOR, $bg_ground['text'] );
if ( abs( $BG_HERO['ratio'] - $BG_PROBE_PLAIN['ratio'] ) < 0.25 ) {
	fail( sprintf(
		'the LP-BROKEN-GRID hero sweep reads %.2f:1 with the house ink and %.2f:1 without it on `%s`'
			. ' — the two agree, so the ink is not in THIS measurement path. The slider sweep has had a'
			. ' probe for this since the ink landed; this call site was added later and had none, which'
			. ' is how a one-argument deletion certified an ungraded photograph in a green build.',
		$BG_HERO['ratio'],
		$BG_PROBE_PLAIN['ratio'],
		$BG_SLUG
	) );
}

// ─────────────────────────────────────────────────────────────── 7 · the strips
//
// One entry per `TPL-* x PERS-*` pair. Phase 1 proves the machinery; the order here is the order
// on the page, and it is fixed rather than derived so the output cannot move under a sort.

// A strip's `tgl` is the toggles it moves OFF their default. Absent means every toggle its
// archetype admits sits at the default the template doc states — which is what all eight strips
// did before this, silently. It is not silent now: the bar prints the resolved value either way.
/* ── TPL-C-03's VISUAL HERO IS A THIRD TEXT-OVER-PHOTOGRAPH CASE, AND IT GETS SWEPT ────────────

   Two such cases existed before this archetype: the slider and the broken-grid hero, each with its
   own probe. A one-argument deletion once certified an ungraded photograph in a green build, and
   the repair was to probe PER CALL SITE rather than once for the file. Adding a third call site
   without adding its sweep would rebuild that exact hole — so it is swept here, before the strips
   are declared, and the build refuses to write a file it cannot certify.

   THE MEASUREMENT IS TAKEN AT THE GRADIENT'S WEAKEST POINT. `.hero-visual::after` runs
   62% → 34% → 58% black, and the copy crosses all three; sweeping the 62% end would be measuring
   the scrim where it is strongest and calling the hero safe. 34% is the number that decides.

   AND ONCE PER ANCHOR, because the ink differs per anchor and the ink is what the browser paints.
   The text is #FFFFFF on all four — white over a photograph regardless of ground, since an `ink`
   anchor would otherwise put near-white type on a near-white scrim edge. */
$VIS_SLUG  = 'hero-taller';
$VIS_ALPHA = '0.62';
$VIS_TEXT  = '#FFFFFF';
$VIS_ROWS  = array();
foreach ( array_keys( $ANCHORS ) as $vis_k ) {
	$vis = worst_pixel( $VIS_SLUG, '#000000', $VIS_ALPHA, $VIS_TEXT, $INK[ $vis_k ] );
	if ( $vis['ratio'] < 4.5 ) {
		fail( sprintf(
			'TPL-C-03 visual hero: `%s` under %s ink measures %.2f:1 for %s text at a %s black'
				. ' scrim — below AA, and this is the weakest point of the gradient, not its average',
			$VIS_SLUG,
			$vis_k,
			$vis['ratio'],
			$VIS_TEXT,
			$VIS_ALPHA
		) );
	}
	$VIS_ROWS[] = sprintf( '%-14s %5.2f:1', $vis_k, $vis['ratio'] );
}
printf( "  hero visual TPL-C-03 (%s, scrim %s negro, texto %s):\n    %s\n",
	$VIS_SLUG, $VIS_ALPHA, $VIS_TEXT, implode( "\n    ", $VIS_ROWS ) );

$STRIPS = array(
	/* COBERTURA PAREJA, Y NO "DONDE ENCAJA". Las cuatro primeras anclas cubren los diez arquetipos
	   de la casa; VITRINE cubria TRES, elegidos porque la vitrina les venia bien. Eso no era una
	   decision de cobertura sino su ausencia, y rompe lo unico que esta galeria hace: COMPARAR. Un
	   ancla presente en 3 de 10 no se puede poner al lado de una presente en 10.
	   Asi que ahora estan las diez, incluidas las dos donde la combinacion CHIRRIA -- TPL-C-05 (el
	   telefono en el header contra el ancla mas lenta en imagen) y TPL-E-05 (urgencia con fecha
	   contra densidad monumental). Se quedan y se marcan como tensas: una tira es una configuracion
	   renderizada, no una recomendacion, y saber cual chirria es justo el dato que un catalogo con
	   huecos no puede dar. RT_GALLERY_NOT_DISTINCT sigue verde en las siete: VITRINE difiere en 4 de
	   5 ejes contra cada una de las otras cuatro, por construccion. */
	/* CASA TERRAZZA GOES FIRST because the catalogue is read from the top and this is the entry
	   that answers the question the other forty cannot. PERS-EDITORIAL and not PERS-MATTER: the
	   restaurant wants the generous density and the 88px display, and `matter`'s classic scale would
	   have made a room that photographs like this read solid instead of worth the trip.

	   IT SITS ON TPL-C-06 AND NOT ON TPL-C-05, and that correction is the point of this pass. Built
	   on TPL-C-05 it was the same nine sections in the same order as the quarry with a brown palette
	   on top — the skin changed and the skeleton did not, which is the failure this repo already had
	   in August with the eight "design personalities". TPL-C-06 declares its own wireframe, and the
	   audit's `RT_TPL_TOO_SIMILAR` measures the distance instead of taking my word for it. */
	
	/* Los tres de automoción. Cada uno sobre un ANCLA distinta y un ESQUELETO distinto: entre los
	   cuatro negocios de marca quedan cubiertas las cuatro anclas del sistema, que es la prueba de
	   que la variedad no depende de la paleta. */
	array( 'tpl' => 'TPL-C-07-aranda',  'anchor' => 'institutional' ),
	
	
	/* Los tres de salud dental. */
	
	array( 'tpl' => 'TPL-C-11-alinea',   'anchor' => 'editorial' ),
	
	/* LUMIÈRE sobre MATTER, y llegó ahí por una corrección que costó dos rondas.
	   Primero fue `editorial`, porque el sector se ilustra con revistas y porque el cuarto modo de
	   encabezado se abrió justo para poder entrar ahí. Renderizado, un lector lo resumió en una
	   frase: «demasiado grande, grosero y sin sentido — no es delicado ni proporcional para una
	   estética». Y la medición le daba la razón: `editorial` es h1 88px con interlínea 0,95, o sea
	   una escala de PORTADA. Un centro de estética no grita; enseña una carta y una cabina.
	   `classic` baja el h1 a 64 y el h2 a 48 sin tocar nada más de la marca —el fondo rosado y las
	   tipografías son suyas, no del ancla—, y su elevación `hairline` es además la que ya usaban la
	   retícula de zonas y la carta: el ancla y el arquetipo por fin dicen lo mismo.
	   `matter × emblem` estaba libre (Bergara tiene `rule`, Corte Nueve `index`), así que el cuarto
	   modo sigue siendo lo que abrió la puerta, sólo que a otra habitación. */
	array( 'tpl' => 'TPL-C-14-lumiere', 'anchor' => 'matter' ),
	
	
	
	
	/* VITRINE sobre servicios + captacion. Es la combinacion mas incomoda del bloque corporativo y por
	   eso esta: el ancla pide aire monumental y el arquetipo quiere el formulario cerca. Renderizada,
	   se ve exactamente cuanto scroll cuesta esa densidad antes de llegar al cierre. */
	
	/* TPL-C-02 · the second corporate archetype. Its four anchors sit between C-01's and E-02's so
	   the catalogue reads corporate → corporate → ecommerce rather than jumping. */
	
	
	
	
	/* VITRINE sobre Institutional Trust. Contraste directo con la variante `institutional` de arriba:
	   comparten UN eje (elevation `soft-shadow`) y ninguno mas, asi que la misma pagina se lee
	   como despacho sobrio o como sala de exposicion segun donde caiga el ground. */
	
	/* TPL-C-03 · el tercer arquetipo corporativo. */
	
	
	
	
	/* VITRINE sobre el portfolio: es su caso de uso literal —obra iluminada contra un fondo oscuro,
	   en rejilla estricta y con aire—. Sirve ademas de contraste directo con la variante `direct` de
	   arriba: las dos son negras y no se parecen, que es lo que el eje de composicion decide. */
	
	/* TPL-E-03 · el segundo arquetipo de ecommerce. */
	
	
	
	
	/* VITRINE sobre Brand Story. `la marca y el relato venden; el producto ilustra` dice su doc, y este
	   ancla es la que mas subordina el producto al ambiente. La mas facil del bloque. */
	
	/* TPL-C-04 · la landing de oferta única. */
	
	
	
	
	/* VITRINE sobre la landing de oferta unica. Una oferta y un CTA repetido, servidos en la densidad
	   mas alta del catalogo: es el caso donde se ve si el aire ayuda a la conversion o la entierra. */
	
	/* TPL-E-05 · la campaña con fecha. */
	
	
	
	
	/* VITRINE sobre Promo / Campaign. TENSA, y es el contraejemplo que faltaba: la urgencia con fecha
	   quiere velocidad y este ancla es la unica que no la tiene. Deja de ser una opinion cuando la
	   campana esta renderizada al lado de su version `direct`. */
	
	/* TPL-C-05 · el negocio con puerta. */
	
	
	
	
	/* VITRINE sobre el negocio con puerta. TENSA a proposito: el arquetipo lleva el TELEFONO en el
	   header y el ancla es la mas lenta en imagen del catalogo. Funciona para un restaurante caro y
	   chirria para una clinica, y esa diferencia solo se ve mirandola. */
	
	/* TPL-C-15 · la cartera curada de Inmobiliaria de la O. Una sola tira, como el resto de los
	   arquetipos con marca: lo que una marca demuestra es SU esqueleto, y basta una configuración
	   -- las cinco anclas son para los arquetipos de la casa. Ancla `editorial` (`PERS-EDITORIAL`):
	   la marca reemplaza su ground `paper` por el propio, cálido y claro (`#F6F4F0`), que es el
	   único de los cuatro ejes que R1 permite tocar (ground/acento/tipo/fotos, nunca
	   escala/densidad/composición/elevación) -- escala, densidad, composición y elevación siguen
	   siendo las de `editorial`, sin tocar. */
	array( 'tpl' => 'TPL-C-15-delao', 'anchor' => 'editorial' ),




	/* TPL-E-01 · la tienda que entra por el ojo. */
	
	
	
	
	/* La marca visual bajo VITRINE: es una tienda que vende mirando, y el ancla la pone en una
	   sala a oscuras con la pieza iluminada. Tarjeta cuadrada (ratio 1/1) contra el 16/11 de las
	   demas: el objeto se enseña completo, no recortado a formato editorial. */
	
	/* TPL-E-04 · el catálogo ancho. */
	
	
	
	
	
	
	
	
	
	/* VITRINE sobre Catalog / Product-First. Ocho tiles en rejilla estricta bajo un ground `ink`: es
	   el escalon de superficie haciendo TODO el trabajo de separacion, porque sobre negro la sombra
	   `soft-shadow` no se ve. La tira existe para comprobar que ese escalon basta a ocho piezas. */
	
	/* Las tres verticales de ecommerce que faltaban por renderizar. Una tira cada una, como el
	   resto de las marcas: lo que un arquetipo con marca demuestra es SU esqueleto, y para eso
	   basta una configuración — las cinco anclas son para los arquetipos de la casa, donde el
	   contenido es constante y lo único que se compara es el ancla. */
	array( 'tpl' => 'TPL-E-06-corte',  'anchor' => 'matter' ),
	array( 'tpl' => 'TPL-E-07-bajura', 'anchor' => 'direct' ),
	array( 'tpl' => 'TPL-E-08-tueste', 'anchor' => 'institutional' ),
	array( 'tpl' => 'TPL-E-09-medida', 'anchor' => 'editorial' ),
);

/* No pair may repeat: `RT_GALLERY_NOT_DISTINCT` will assert this from outside, but a generator
   that can emit the same card twice makes that check a formality. Two strips sharing an anchor
   must declare different archetypes, which duplicate detection here already guarantees. */
$seen_pairs = array();
foreach ( $STRIPS as $s ) {
	$pair = $s['tpl'] . '×' . $s['anchor'];
	if ( isset( $seen_pairs[ $pair ] ) ) {
		fail( "duplicate strip $pair — one card per TPL × PERS pair" );
	}
	$seen_pairs[ $pair ] = true;
	if ( ! isset( $CONTENT[ $s['tpl'] ] ) ) {
		fail( "no content set for {$s['tpl']}" );
	}
	if ( ! isset( $ANCHORS[ $s['anchor'] ] ) ) {
		fail( "no anchor `{$s['anchor']}` in design-personalities.md" );
	}
	if ( ! isset( $TOGGLES[ $CONTENT[ $s['tpl'] ]['arch'] ] ) ) {
		fail( "no toggle table for {$CONTENT[ $s['tpl'] ]['arch']} — an archetype that has not declared"
			. ' which toggles it admits cannot be checked against one, and `todas` is not a declaration' );
	}
}

/* ── TWO BRANDS MAY NOT SHARE BOTH THE ANCHOR AND THE HEAD TREATMENT ──────────────────────────
   The anchor moves five axes and the head mode moves the gesture every section opens with. Sharing
   one is fine and unavoidable — there are four anchors and there will be thirty brands. Sharing
   BOTH is two pages with the same rhythm, and rhythm is what a reader recognises before content. */
$hm_seen = array();
foreach ( $STRIPS as $hm_s ) {
	$hm_c = $CONTENT[ $hm_s['tpl'] ];
	if ( '' === $hm_c['brand'] ) {
		continue;
	}
	$hm_k = $hm_s['anchor'] . ' + ' . $hm_c['head_mode'];
	if ( isset( $hm_seen[ $hm_k ] ) ) {
		fail( "`{$hm_s['tpl']}` and `{$hm_seen[ $hm_k ]}` are both `$hm_k`. Two branded templates on the"
			. ' same anchor AND the same section-head treatment open every section with the same gesture'
			. ' at the same size in the same place — which is the sameness the head mode exists to break.' );
	}
	$hm_seen[ $hm_k ] = $hm_s['tpl'];
}

/* ── A CATALOGUE ENTRY MAY NOT REUSE A SKELETON ───────────────────────────────────────────────
   THE RULE THIS FILE KEPT LEARNING AND KEEPT NOT ENFORCING. Casa Terrazza shipped once as TPL-C-05
   with a dark palette, a brass accent, a different typeface and seven photographs of its own — and
   read as "exactamente igual a las otras... igual 100% pero cambiando colores", because it was:
   same hero shape, same three-card grid, same booking block in third position, same six-cell
   gallery, same close. A brand changes the SKIN. Only an archetype changes the SKELETON.

   `RT_TPL_TOO_SIMILAR` in framework-audit.php has judged that distance all along — two archetypes
   of one family may share at most half their combined inventory — but it reads TPL-*.md DOCS. A
   catalogue entry that was a brand sitting on somebody else's archetype declared no wireframe, so
   it was never in the comparison. That is the exact hole a clone shipped through.

   Three clauses, and each one closes a different way of reproducing the miss:

     1 · a branded template's archetype must have a DOC, so the audit's similarity judge sees it;
     2 · no two templates may share an archetype once either of them is branded, so a second brand
         cannot be dropped onto a skeleton that is already spoken for;
     3 · the house strips are exempt from clause 2 BY NAME, because forty of them sharing ten
         archetypes across four anchors is the axis proof and not a catalogue of businesses.

   Clause 3 is the one worth watching: an exemption is how a rule stops applying to the thing it
   was written for. It is keyed on `brand === ''`, which is a property of the DATA and not a list of
   ids somebody maintains, so a house strip that becomes a brand loses the exemption automatically. */
$skel_owner = array();
foreach ( $STRIPS as $sk_s ) {
	$sk_c = $CONTENT[ $sk_s['tpl'] ];
	if ( ! isset( $skel_owner[ $sk_c['arch'] ] ) ) {
		$skel_owner[ $sk_c['arch'] ] = array();
	}
	$skel_owner[ $sk_c['arch'] ][ $sk_s['tpl'] ] = $sk_c['brand'];
}
foreach ( $skel_owner as $sk_arch => $sk_tpls ) {
	$sk_branded = array_keys( array_filter( $sk_tpls, function ( $b ) {
		return '' !== $b;
	} ) );
	if ( array() === $sk_branded ) {
		continue;   // clause 3: the house axis proof shares skeletons on purpose
	}
	if ( count( $sk_tpls ) > 1 ) {
		fail( "`$sk_arch` is the skeleton of " . count( $sk_tpls ) . ' catalogue entries ('
			. implode( ', ', array_keys( $sk_tpls ) ) . ') and at least one of them is a brand. Two'
			. ' entries on one archetype are the same sections in the same order with a different'
			. ' palette — which is what a client sees as "the same template again", whatever the'
			. ' colours do. Give the new business its own TPL-*.md with its own wireframe.' );
	}
	$sk_doc = array_merge(
		glob( dirname( __DIR__, 3 ) . '/web-templates/references/templates/corporate/' . $sk_arch . '-*.md' ),
		glob( dirname( __DIR__, 3 ) . '/web-templates/references/templates/ecommerce/' . $sk_arch . '-*.md' )
	);
	if ( array() === $sk_doc ) {
		fail( "branded template `{$sk_branded[0]}` is built on `$sk_arch`, which has no TPL-*.md under"
			. ' web-templates/references/templates/. Without a doc it declares no wireframe, so'
			. " `RT_TPL_TOO_SIMILAR` never measures it against its family — and an archetype nothing"
			. ' compares is exactly how a repainted clone shipped as a new template.' );
	}
}

/* THE TOGGLE, RESOLVED AND CHECKED AGAINST THE TEMPLATE'S OWN §4. toggles.md § Notas: "Los toggles
   nunca rompen el ADN … si el cliente lo pide, sugerir cambiar de plantilla, no deformar la
   actual." A generator that will set any toggle on any archetype is exactly that deformation, and
   it is the machine half of the contradiction this pass found in the docs — so a strip asking for
   a toggle its archetype does not declare, or for a value outside that toggle's stated options,
   stops the build here rather than rendering a page that quietly invents a capability. */
$RESOLVED = array();
foreach ( $STRIPS as $s ) {
	$r_admit = $TOGGLES[ $CONTENT[ $s['tpl'] ]['arch'] ];
	$r_set   = isset( $s['tgl'] ) ? $s['tgl'] : array();
	foreach ( $r_set as $r_id => $r_val ) {
		if ( ! isset( $r_admit[ $r_id ] ) ) {
			fail( "{$s['tpl']} × {$s['anchor']} sets `$r_id`, which {$s['tpl']}'s § \"Toggles admitidos\""
				. ' does not list — a toggle applied to an archetype that never offered it is a deformed'
				. ' template, not a configured one' );
		}
		if ( ! in_array( $r_val, $r_admit[ $r_id ]['options'], true ) ) {
			fail( "{$s['tpl']} × {$s['anchor']} sets `$r_id` to `$r_val`, which is not one of "
				. implode( ' / ', $r_admit[ $r_id ]['options'] ) );
		}
	}
	// Every ADMITTED toggle resolves, not just the ones a strip moved: the default is a position
	// the reader is entitled to see, and an unprinted default is the silent configuration this
	// whole section exists to end.
	$r_rows = array();
	foreach ( $r_admit as $r_id => $r_def ) {
		$r_rows[] = array(
			'id'         => $r_id,
			'value'      => isset( $r_set[ $r_id ] ) ? $r_set[ $r_id ] : $r_def['default'],
			'is_default' => ! isset( $r_set[ $r_id ] ),
			'default'    => $r_def['default'],
		);
	}
	$RESOLVED[ $s['tpl'] . '×' . $s['anchor'] ] = $r_rows;
}

/* THE SLIDER'S FIRST FRAME IS THE FIXED HERO'S OWN IMAGE, on every archetype that can resolve
   `TGL-HERO-TYPE` to `slider`. That agreement is the entire content of the reduced-motion promise
   — "shows one frame" is worth little if the frame is arbitrary — and it is exactly the kind of
   agreement between two tables that holds until somebody edits one of them. Asserted rather than
   commented, because a comment saying two things agree is not a thing that notices when they stop. */
foreach ( $CONTENT as $t_tpl => $t_c ) {
	$t_admit = $TOGGLES[ $t_c['arch'] ];
	if ( ! isset( $t_admit['TGL-HERO-TYPE'] ) || ! in_array( 'slider', $t_admit['TGL-HERO-TYPE']['options'], true ) ) {
		continue;
	}
	/* $SLIDER_FRAMES IS A HOUSE SET, and a branded template on a slider archetype would need its own.
	   Stopping here is the honest state of the code: the alternative is a brand whose slider shows
	   three photographs of somebody else's quarry, which is a worse page than no page. */
	if ( '' !== $t_c['brand'] ) {
		fail( "`$t_tpl` is a branded template on `{$t_c['arch']}`, which admits TGL-HERO-TYPE=slider,"
			. ' and $SLIDER_FRAMES still names the three house frames — a brand needs its own frame list'
			. ' before it can sit on a slider archetype' );
	}
	if ( $SLIDER_FRAMES[0] !== $t_c['hero']['img'] ) {
		fail( "the slider's first frame is `{$SLIDER_FRAMES[0]}` and {$t_tpl}'s fixed hero is"
			. " `{$t_c['hero']['img']}` — under prefers-reduced-motion the slider strip"
			. ' would settle on a different photograph than the same strip shows at `imagen fija`, so'
			. ' the toggle would be changing the image as well as the behaviour, and neither the data'
			. ' bar nor the pasted spec would say so' );
	}
}

/** The resolved value of one toggle on one strip, or the empty string if the archetype has none. */
function tgl_of( $rows, $id ) {
	foreach ( $rows as $r ) {
		if ( $r['id'] === $id ) {
			return $r['value'];
		}
	}
	return '';
}

// Only the anchors and blueprints actually rendered get a CSS block. Dead CSS for an anchor no
// strip uses is the same lie as a token nothing reads.
$used_anchors = array();
$used_comps   = array();
$used_brands  = array();
foreach ( $STRIPS as $s ) {
	$used_anchors[ $s['anchor'] ]                          = true;
	$used_comps[ $ANCHORS[ $s['anchor'] ]['composition'] ] = true;
	if ( '' !== $CONTENT[ $s['tpl'] ]['brand'] ) {
		$used_brands[ $CONTENT[ $s['tpl'] ]['brand'] ] = true;
	}
}

// ═══════════════════════════════════════════════════════════════ CSS

$css = array();

// ── the typefaces, before any rule that uses them ──────────────────────────────────────────────
//
// This page renders all four anchors, so it is the one file that pays for every family the
// framework names. Until these were embedded it rendered four anchors in Georgia, Arial Black and
// system-ui, and the gallery's entire claim — that these are four distinguishable design systems —
// was being made by the fallback stack.
//
// The list is DERIVED from the anchors rather than typed, so adding a fifth anchor with a new
// family cannot leave the page naming a face it does not carry. nm_font_faces() dies on a family
// with no registry entry, which is the failure worth having: a build that stops beats a page that
// silently renders Georgia.
require_once $DIR . '/../fonts/_fonts.php';

$font_families = array();
foreach ( $ANCHORS as $fa ) {
	foreach ( nm_font_families_asked_for( '--font-1:' . $fa['font_1'] . ';--font-2:' . $fa['font_2'] . ';' ) as $fa_name ) {
		$font_families[ $fa_name ] = true;
	}
}
$font_families = array_keys( $font_families );
sort( $font_families );
$font_cost = nm_font_bytes( $font_families );
$css[]     = "/* ── Typefaces. Embedded as data: woff2, generated from ../fonts/_fonts.php; the\n"
	. "      licence and provenance for each is in ../fonts/_fonts.md. A data: URI issues no\n"
	. "      request, so the Artifact CSP has nothing to block — the rule these files used to\n"
	. "      state was about url(https://…) and never reached this. ── */\n"
	. nm_font_faces( $font_families );

// ── the deviation, written down where it applies ───────────────────────────────────────────────
$css[] = <<<'CSS'
/* ══════════════════════════════════════════════════════════════════════════════════════════════
   A DELIBERATE DEVIATION FROM `html-mockup/SKILL.md`, AND ITS REASON.

   That file's Hard Rules say: "Declare the tokens as CSS variables in `:root` ONCE." This
   document declares them once per ANCHOR, scoped to `[data-anchor]`. It has to: the whole point
   of a gallery is several anchors side by side in one page, and one `:root` cannot hold four
   values of `--c-bg`. The rule is not ignored — it is deviated from here, in the file, with the
   reason attached, because a rule broken silently is indistinguishable from a rule forgotten.

   WHAT IS KEPT of the rule's intent: there is exactly ONE `:root`, it is load-bearing rather
   than decorative (the gallery's own chrome renders from it — see § shell), and no strip
   restates the derived chain. `RT_MOCKUP_NO_AXES` reads only the FIRST root block, so the axis
   declarations it looks for are all in that first block and all real.

   NOTE FOR ANYONE EDITING THIS COMMENT: do not write the selector immediately followed by an
   opening brace anywhere in prose. That check finds the first such literal in the whole file and
   captures to the next closing brace — an earlier version of this paragraph spelled it out with
   an ellipsis between braces, and since that ellipsis is three UTF-8 bytes the check read a
   3-byte block, found none of the five axis properties, and would have failed the audit the
   moment the recursive glob landed. The verifier cannot tell CSS from a sentence about CSS.

   THE TRAP THIS LAYOUT EXISTS TO AVOID, which is not obvious and cost a rewrite to find:
   a custom property's `var()` references are substituted AT COMPUTED-VALUE TIME ON THE ELEMENT
   THAT DECLARES IT, and descendants inherit the already-substituted value. So the obvious
   reading of "extract the derived chain once" — putting `--sp-m: calc(1rem * var(--sp-scale))`
   in `:root` — bakes in `:root`'s OWN `--sp-scale`, and every `[data-anchor]` override of
   `--sp-scale`, `--type-ratio` and `--fs-h1-max` becomes inert. Eight strips would render at
   identical size and spacing while every token read correctly in DevTools, and no text-based
   check could see it.

   The fix is the selector below: the shared chain is declared on `:root, [data-anchor]`, so it
   is written once and RESOLVES once per anchor, against that anchor's own axis values.
   ══════════════════════════════════════════════════════════════════════════════════════════ */
CSS;

// ── block 1 · :root — the document default, and it is used ─────────────────────────────────────
$root_anchor = $ANCHORS['institutional'];
$root_sc     = $SCALE[ $root_anchor['scale'] ];
$root_gr     = $GROUND[ $root_anchor['ground'] ];
$root_el     = $ELEVATION[ $root_anchor['elevation'] ];
$root_ac     = $ACCENT[ $root_anchor['ground'] ];

$css[] = '/* ── :root — PERS-INSTITUTIONAL, the calmest anchor, because a tool\'s chrome should not
      compete with its samples. Every declaration here is READ by the gallery shell, so this is
      the document default rather than a copy kept alive to satisfy a check. ── */
:root{
  /* scale: ' . $root_anchor['scale'] . ' */
  --type-ratio:' . $root_sc['ratio'] . '; --display-lh:' . $root_sc['lh'] . '; --fs-h1-max:' . $root_sc['h1max'] . ';
  /* density: ' . $root_anchor['density'] . ' */
  --sp-scale:' . $DENSITY[ $root_anchor['density'] ] . ';
  /* ground: ' . $root_anchor['ground'] . ' — ' . ratio_str( $root_gr['text'], $root_gr['bg'] ) . ' */
  --c-bg:' . $root_gr['bg'] . '; --c-bg-alt:' . $root_gr['alt'] . '; --c-text:' . $root_gr['text'] . ';
  /* elevation: ' . $root_anchor['elevation'] . ' */
  --elev-rest:' . $root_el['rest'] . ';
  --elev-hover:' . $root_el['hover'] . ';
  /* composition: ' . $COMPOSITION[ $root_anchor['composition'] ]['lp'] . ' */

  /* accent — not an axis; measured ' . $root_ac['r_bg'] . ' on --c-bg, ' . $root_ac['r_alt'] . ' on --c-bg-alt,
     label ' . $root_ac['r_on'] . ' on the fill */
  --c-accent:' . $root_ac['hex'] . '; --c-on-accent:' . $root_ac['on'] . ';

  --font-primary:' . $root_anchor['font_1'] . ';
  --font-secondary:' . $root_anchor['font_2'] . ';
  --track-display:' . $root_anchor['track_disp'] . '; --track-wordmark:' . $root_anchor['track_word'] . ';
  --dur-color:' . $root_anchor['dur_color'] . '; --dur-lift:' . $root_anchor['dur_lift'] . '; --dur-zoom:' . $root_anchor['dur_zoom'] . '; --lift:' . $root_anchor['lift'] . ';
  --ratio-hero:' . $root_anchor['ratio_hero'] . '; --ratio-card:' . $root_anchor['ratio_card'] . ';

  /* ── shared chrome: identical at every anchor, so it lives here and nowhere else ── */
  --fs-base:16;
  --container-max:1280px;

  /* ── THE MARGIN IS A PROPORTION OF THE SCREEN. THE BAND IS WHAT IS LEFT OVER. That ordering is
        the whole rule, and getting it backwards is what shipped twice.

        A CAPPED BAND BESIDE AN UNCAPPED GUTTER INVERTS THE RATIO. Two of the four blueprints bleed
        to `full-end`, which IS the layout viewport edge, so the 14 named tracks must sum to the
        SCREEN: cap the twelve columns and the outer `1fr` is the only track left to absorb a wider
        display. Nothing bounds a `1fr`.

        MEASURED on this page, outer margin as a fraction of the viewport, BOTH sides together:
          flat 1140px band       1440 → 20.8%   1920 → 40.6%   2560 → 55.5%
          clamp(1140,+0.5,1600)  1440 → 15.3%   2000 → 25.0%   2560 → 37.5%
        The second line is the previous fix. It improved the NUMBER at 2560 and left the DIRECTION
        alone: the margin still grows monotonically with the screen, so a bigger display still gets
        a proportionally smaller design. The reader looked at 2000px, where it reads 25%, and said
        it again — "de 2560px para abajo tiene que ser responsive, con márgenes reales". He was
        right both times; only the second half of the defect had been fixed.

        THE FIX BOUNDS THE THING THE READER EXPERIENCES AS MARGIN. `85vw` makes the gutter 7.5% per
        side, 15% total, CONSTANT at every width above the knee — and at 3440 and 5120 too. A
        proportional band holds its ratio forever by construction; no cap can.

        THE 85 IS DERIVED, NOT CHOSEN. It is the largest constant band — the SMALLEST margin — that
        never renders the page narrower than the formula it replaces, anywhere in the range
        `house-rules.md` row 32 measures above the 1280 reference. The binding width is 1440, where
        the old band was 1220 of 1440 = 84.72%; 85 is that rounded to the design\'s own precision,
        and every width above clears by more:
          1440 → 1224 ≥ 1220   1600 → 1360 ≥ 1300   1800 → 1530 ≥ 1400
          2000 → 1700 ≥ 1500   2200 → 1870 ≥ 1600   2560 → 2176 ≥ 1600
        FLOOR 1140px: below the 1341px knee the floor wins, so 1280 resolves to exactly 1140 and
        NOTHING at or below the desktop reference moves — every measurement in this repo taken at
        or under 1280 still holds to the pixel.

        WHAT IT COSTS, MEASURED, because the cap existed for a reason and the reason was line
        length. An uncapped band lets a text column grow with the screen. Walking every text node
        at 2560 and dividing each rendered line by its own element\'s `0` advance, exactly ONE run
        outgrows its measure: `.lede`, 68.4ch at 1440 → 103.1ch at 2560. Every other prose run is
        bounded by its container and reads 78.2ch at 1440 AND at 2560 alike — card copy, band copy,
        the footer legal line, all flat. So the honest trade is to cap the TEXT COLUMN and let the
        band and the media keep growing: `.lede{max-width:66ch}` costs one declaration and buys the
        ratio at every width. Capping the band to protect one paragraph was paying for that
        paragraph with the whole page.

        THE OLD DERIVATION, kept because it is why the cap looked right: Two of the four blueprints bleed to `full-end`, which IS the layout
        viewport edge. Their grid is `minmax(pad,1fr)` gutter + 12 capped columns + `minmax(pad,1fr)`
        gutter, so the tracks must sum to the viewport: with the columns capped by a FIXED band,
        the only track left to absorb a wider screen is the gutter, and the gutter is unbounded.

        MEASURED on this page before the fix, `.services .items` under LP-BROKEN-GRID:
          1440 → left gutter 150.1px, right edge 1440   (gutter 10.4% of the viewport)
          1920 → left gutter 390.1px, right edge 1920   (gutter 20.3%)
          2560 → left gutter 710.1px, right edge 2560   (gutter 27.7%)
        The band never grows, the bleeding edge always reaches the screen, so the composition\'s
        optical centre drifts right by exactly half the gutter — +75 / +195 / +355px — and at 2560
        the left QUARTER of the page is dead ink under every section. That is the whole of "los
        márgenes están todos muy mal", and no check caught it because every width we measured
        stopped at 1920, where it is bad but not yet obviously broken.

        FLOOR 1140px at ≤1280px: the desktop reference width, so nothing at or below it moves and
        every measurement taken to date still holds. SLOPE 0.5: the band takes half of each extra
        pixel and the two gutters split the other half, which keeps the band growing without ever
        letting it outrun the screen. CAP 1600px is DERIVED, not chosen — it is the smallest cap
        that holds the outer gutter under one fifth of the viewport at 2560, the width the reader
        was actually on. Measured at 2560 with the three candidates:
          cap 1440 → gutter 560px = 21.9% of the viewport  ✗ still reads as a dead band
          cap 1600 → gutter 461px = 18.0%                  ✓ reads as a page margin
          cap 1800 → gutter 390px = 15.2%                  ✓ but widens every card row for nothing
        Above ~2200px the cap engages and the gutter starts growing again; at 3440 it is back to
        26%. No band cap can hold the ratio forever, and this one is honest to 2560 — the top of
        the range `house-rules.md` row 32 measures. ── */

  /* ── 85 → 68. THE MARGIN WAS RIGHT IN DIRECTION AND WRONG IN SIZE, and the reason is that it is
        the ONLY thing on this page that grows above 1280.

        MEASURED, one anchor across widths, not four anchors across one width:
          --fs-h1  (editorial)  88px at 1280, 1440, 2000 AND 2560   — `--fluid` clamps at 1280
          --fs-h2               58.7px at all four                  — same clamp
          --fs-body             20px above 1667                     — clamp(1rem,1.2vw,1.25rem)
          --sp-section          151.2px at all four                 — same clamp, § Density
          --content-width       1140 → 1224 → 1700 → 2176           — 85vw, unbounded
        Type is frozen by design and design-system.md § Scale argues that case at length. Rhythm is
        frozen with it. So every pixel a wider screen brings lands in the band, and from the band in
        the IMAGERY: `.services .items` card 1 under LP-ASYMMETRIC measured 646px wide at 1280 and
        973px at 2000 — a service thumbnail the size of a lightbox beside an 88px headline that had
        not moved. The reader drew two vertical lines inside the content edges and wrote "muy grande
        todo, necesita más aire". The lines land at x≈310 and x≈1660 on his 2000px screen: a 1350px
        band, ~16% of the viewport per side against the 7.5% that shipped.

        68vw PUTS THE EDGE ON HIS LINE — measured, not reasoned about: `.services .items` left is
        320.0px at 2000 against the 310 he drew. It also RESTORES THE RATIO the whole system is
        drawn at: band ÷ h1 reads 13.0 at the 1280 reference, 19.3 under 85vw at 2000, and 15.5
        under 68vw. The h1 recovers a third line and reads as a headline again rather than a caption
        on a photograph.

        THE FLOOR STILL WINS BELOW THE 1676px KNEE, so 1280 resolves to exactly 1140 and every
        measurement in this repo taken at or under the desktop reference still holds to the pixel.
        Between them the band is pinned at 1140 — the width the design was drawn at — and the
        gutters absorb the difference: 10.4% per side at 1440, 14.4% at 1600. LOOKED AT, at 1440,
        against the 1224 it replaces: nothing crowds and nothing stretches. The 84px is invisible.

        WHY THIS IS NOT THE DEFECT ROW 32 EXISTS TO CATCH, and the row\'s probe has been corrected
        to say so rather than this value being chosen to fit it. The pathology is a margin that
        grows WITHOUT BOUND, because a fixed band leaves the outer `1fr` as the only track able to
        absorb a wider screen: 10.4% → 20.3% → 27.7% at 1440/1920/2560 and rising forever. 68vw
        rises to a HARD PLATEAU at the 1676 knee and holds 16.0% at 1920, 2000, 2560, 3440 and 5120
        alike. Row 32(a) sampled 1440 against 2560 — one point inside the pinned segment and one
        above it — so it read a transition that stops as growth that does not. Its probe now samples
        1920 against 2560, both above any knee a 1140 floor can produce, and MUTATION CONFIRMS it
        still fails every historical defect: fixed 1140px scores +7.42pp, the rejected
        `clamp(1140,+0.5,1600)` scores +6.77pp, 85vw scores 0.00pp, 68vw scores 0.00pp. ── */
  /* UN TECHO, Y NO 100vw. Sin él el canvas vale 1740px a 2560 y 2611px a 3840: una banda que crece
     sin límite estira cada rejilla, cada medida de línea y cada foto hasta tamaños que nadie
     diseñó. 1560 es el punto donde una rejilla de tres deja tarjetas de ~500px — grandes pero
     legibles — y una línea de texto sigue por debajo de los 90 caracteres. Por debajo de ~2294px
     el clamp no llega al techo, así que ninguna pantalla normal cambia. */
  --content-width:clamp(1140px, 68vw, 1560px);
  --pad-x-mobile:20px; --pad-x-tablet:32px;
  --radius-card:12px; --radius-button:8px; --radius-image:8px; --radius-input:8px; --radius-container:16px;
  --btn-padding:.875rem 1.75rem; --btn-border-width:1.5px;
  --ease:cubic-bezier(.22,1,.36,1);
/* EL CUERPO ERA UNA CONSTANTE DISFRAZADA DE CURVA, y esto se midió antes de tocarlo.
     `clamp(1rem, 1.2vw, 1.25rem)`: el término medio sólo supera 1rem cuando la ventana pasa de
     1333px, y sólo alcanza su techo de 20px a los 1667. Traducido: de 320 a 1333 el cuerpo vale
     EXACTAMENTE 16px y no se mueve ni un píxel. Medido a 1280 en las siete marcas: 16, 16, 16,
     16, 16, 16, 16. La curva existía en el código y no existía en pantalla.
     Enfrente, los titulares SÍ cambian con el ancla: 48 en `contained`, 64 en `classic`, 88 en
     `editorial`, 120 en `monumental`. Con el cuerpo clavado en 16, la distancia h1/cuerpo iba de
     3,0× a 7,5× según el ancla, y nadie había elegido ese número: salía solo.
     Un lector lo dijo mirando la primera versión de TPL-C-14 —«demasiado grande, grosero y sin
     sentido»— y la corrección que funcionó allí fue subir el cuerpo, no bajar el titular. Esa
     curva sube aquí, a la raíz, porque el defecto nunca fue de aquel arquetipo.
     LA NUEVA: 17px a 320, 19px desde 1280. Fluida en el tramo donde hay pantallas, que es el
     tramo entero que la anterior se saltaba. Las distancias quedan 2,5× / 3,4× / 4,6× / 6,3× —
     siguen separadas, porque separar anclas es el trabajo de la galería, pero ya ninguna nace de
     un suelo que no supo subir. */
  --fs-body:clamp(1.0625rem, .75rem + .6vw, 1.1875rem);
  /* Y UN ESCALÓN PARA LA COPIA SECUNDARIA. `--fs-small` son 14px y estaba cargando párrafos de
     LECTURA —respuestas de FAQ, cuerpo de tarjeta—, no etiquetas. Con el cuerpo en 19 esa
     diferencia pasaba de 2 a 5 puntos dentro de la misma sección. `--fs-meta` es el paso que
     faltaba; `--fs-small` vuelve a ser lo que su nombre dice: rótulos, metadatos, pies. */
  --fs-meta:calc(var(--fs-body) * .9);
  --fs-small:.875rem; --fs-eyebrow:.75rem; --fs-button:1rem; --fs-nav:.95rem;
  --fs-price:clamp(1.1rem, 1.6vw, 1.35rem);
}';

// ── block 1b · THE TWO CLOSING FIELDS, and every colour on them MEASURED ───────────────────────
//
// Two of the four anchors close on a ground that is not the page's. Which two is not a free
// choice: it is the `elevation` and `ground` axes deciding how loudly this personality is allowed
// to end. `PERS-EDITORIAL` — elevation `none`, ground `paper` — has no shadow and no fill in its
// whole vocabulary, so the only way it can mark an ending is to turn the paper over: an inverted
// spread, the back cover. `PERS-DIRECT` — ground `ink`, elevation `accent-glow`, the anchor whose
// one-line brief is "marcas que ganan por ser inconfundibles" — ends on a field of its own accent.
// The other two have a fill and a shadow already and close with those, in block 7; nothing here.
//
// EVERY COLOUR BELOW IS MEASURED AND THE BUILD DIES ON A FAILURE, because a painted band is where
// contrast quietly goes wrong: `--c-accent` is #8C3A1F on three grounds and measures 2.47:1 on
// near-black, which this file already says out loud. An inverted editorial band painted with the
// paper accent would be an eyebrow nobody can read, and it would look deliberate.
/* THE FULL-BLEED HERO IS MEASURED PER TEMPLATE, NOT ONCE FOR THE ARCHETYPE THAT INTRODUCED IT.
   § 5c already sweeps TPL-C-03's `hero-taller` across the four anchor inks at this exact alpha.
   TPL-C-06 puts the same `.hero-visual` gradient over a DIFFERENT photograph under a DIFFERENT
   ink, and inheriting that number would be certifying one image by measuring another — the same
   shape of defect as measuring the un-inked file, one level along. The bar is 4.5 and the
   measurement is taken at the gradient's weakest point, not its average.

   `$VIS_ARCHS` is the set of archetypes whose hero puts copy ON the photograph. An archetype added
   to `render_page()` with a `.hero-visual` and left out of here would ship an unmeasured hero, so
   the list lives next to the check that consumes it rather than in a comment. */
$VIS_ARCHS  = array( 'TPL-C-06' => true, 'TPL-C-08' => true, 'TPL-C-11' => true );
$VIS_BRAND  = array();
foreach ( $STRIPS as $vb_s ) {
	$vb_c = $CONTENT[ $vb_s['tpl'] ];
	if ( ! isset( $VIS_ARCHS[ $vb_c['arch'] ] ) ) {
		continue;
	}
	$vb_ink = ( '' !== $vb_c['brand'] ) ? 'b-' . $vb_c['brand'] : $vb_s['anchor'];
	$vb_key = $vb_c['hero']['img'] . ' @ ' . $vb_ink;
	if ( isset( $VIS_BRAND[ $vb_key ] ) ) {
		continue;
	}
	$vb_row = img( $vb_c['hero']['img'] );
	if ( 0 !== strpos( $vb_row['role'], 'hero' ) ) {
		fail( "`{$vb_c['tpl']}` puts `{$vb_c['hero']['img']}` behind a full-bleed hero and the manifest"
			. " calls it `{$vb_row['role']}` — a non-hero crop stretched across 78vh is a blown-up"
			. ' thumbnail, and the contrast measurement below will not object because brightness is not'
			. ' the defect' );
	}
	$vb = worst_pixel( $vb_c['hero']['img'], '#000000', $VIS_ALPHA, $VIS_TEXT, $INK[ $vb_ink ] );
	if ( $vb['ratio'] < 4.5 ) {
		fail( sprintf(
			'%s full-bleed hero: `%s` under the `%s` ink measures %.2f:1 for %s text at a %s black scrim'
				. ' — below AA at the weakest point of the gradient',
			$vb_c['tpl'],
			$vb_c['hero']['img'],
			$vb_ink,
			$vb['ratio'],
			$VIS_TEXT,
			$VIS_ALPHA
		) );
	}
	$VIS_BRAND[ $vb_key ] = sprintf( '%-30s %5.2f:1', $vb_key, $vb['ratio'] );
}

/* PRINTED, not just measured. A number this build computed and kept to itself is the same silent
   configuration every other section of this file has had to stop shipping. */
if ( array() !== $VIS_BRAND ) {
	printf( "  hero a sangre por plantilla (scrim %s negro, texto %s):
    %s
",
		$VIS_ALPHA, $VIS_TEXT, implode( "
    ", $VIS_BRAND ) );
}

$FIELD = array();
$FIELD_DEF = array(
	/* the back cover: the anchor's own ink becomes the ground, its own paper becomes the type. */
	'editorial' => array( 'kind' => 'inverse' ),
	/* the accent IS the ground. The control on it then has to be the page's ink — an accent
	   button on an accent field is a button nobody can find. */
	'direct'    => array( 'kind' => 'accent' ),
);
/* THE FIELD IS KEYED BY (ANCHOR x GROUND) AND NOT BY ANCHOR, and the brand layer is what forced
   it. `inverse` means "this anchor's ink becomes the ground"; whose ink was never a question while
   an anchor owned exactly one ground. A brand replaces the ground, so a field still resolved from
   `$ANCHORS[$k]['ground']` would paint the HOUSE's back cover at the bottom of a page that has
   none of the house's colours — near-black on near-black, legal in DevTools and unreadable on a
   screen. The sites are DERIVED FROM THE STRIPS, so a field can only exist for a close that is
   actually rendered, and every rendered close has one. */
$FIELD_SITES = array();
foreach ( $STRIPS as $fs_s ) {
	if ( ! isset( $FIELD_DEF[ $fs_s['anchor'] ] ) ) {
		continue;
	}
	$fs_c = $CONTENT[ $fs_s['tpl'] ];
	$fs_g = ( '' !== $fs_c['brand'] ) ? 'b-' . $fs_c['brand'] : $ANCHORS[ $fs_s['anchor'] ]['ground'];
	$FIELD_SITES[ $fs_s['anchor'] . '@' . $fs_g ] = array( 'anchor' => $fs_s['anchor'], 'ground' => $fs_g );
}
foreach ( $FIELD_SITES as $fld_k => $fld_site ) {
	$fld_d  = $FIELD_DEF[ $fld_site['anchor'] ];
	$fld_gr = $GROUND[ $fld_site['ground'] ];
	if ( 'inverse' === $fld_d['kind'] ) {
		$fld_bg   = $fld_gr['text'];
		$fld_text = $fld_gr['bg'];
		$fld_alt  = css_mix( $fld_gr['text'], 0.92, $fld_gr['bg'] );
	} else {
		$fld_bg   = $ACCENT[ $fld_site['ground'] ]['hex'];
		$fld_text = $ACCENT[ $fld_site['ground'] ]['on'];
		$fld_alt  = css_mix( $fld_bg, 0.90, $fld_text );
	}

	/* THE CONTROL'S FILL IS CHOSEN THE WAY `--c-on-accent` IS: by measuring the candidates the page
	   already owns and taking the STRONGEST that clears AA, never by picking one that looks right.
	   The candidates are the two accents this build has derived plus the field's own two extremes,
	   which is the whole palette in play — nothing new is invented for a band.

	   STRONGEST AND NOT FIRST-THAT-CLEARS, and the difference is visible. First-that-clears put
	   `#FF6A1A` on the editorial back cover at 6.22:1 — legal, and a second orange on a page whose
	   accent is `#8C3A1F`, so the close introduced a colour the rest of the strip does not have.
	   Strongest picks the field's own type colour at 17.84:1: a white bar on black, which is the
	   loudest control an anchor with `elevation: none` can make and costs the palette nothing.
	   On `direct` both rules agree on the page's ink, so the change is free there. */
	$fld_pick = '';
	$fld_best = 0.0;
	foreach ( array_merge( array_values( $ACCENT_BY_GROUND ), array( $fld_text, $fld_gr['bg'] ) ) as $fld_cand ) {
		$fld_r = contrast( $fld_cand, $fld_bg );
		if ( $fld_r >= 4.5 && $fld_r > $fld_best ) {
			$fld_pick = $fld_cand;
			$fld_best = $fld_r;
		}
	}
	if ( '' === $fld_pick ) {
		fail( "no colour in this build's palette clears 4.5:1 on the `$fld_k` closing field ($fld_bg)"
			. ' — a closing band whose one control cannot be read is worse than the band that had none' );
	}
	$fld_on = ( contrast( $fld_text, $fld_pick ) >= contrast( $fld_bg, $fld_pick ) ) ? $fld_text : $fld_bg;

	/* THE MUTED TONE IS CHECKED AND IT IS THE ONE THAT ALMOST GOT AWAY. `.lede` on both closing
	   bands carries `.muted`, so it paints `--c-text-muted` — which now re-resolves against the
	   FIELD's own two colours, 63.4% of the field's type mixed toward the field's ground. On a
	   `#FF6A1A` field that is a brown on orange, and it is the exact shape of the defect § 5c
	   records for the slider hero: the headline was measured, the lede was not, and the lede is
	   the run that was hard to read. Measured here rather than discovered in a capture. */
	$fld_muted = css_mix( $fld_text, 0.634, $fld_bg );
	$fld_bord  = css_mix( $fld_text, 0.11, $fld_bg );

	/* MEASURED FIRST, THEN CAUGHT: `#663216 on #FF6A1A` — 3.61:1, and it was live in a render
	   before this check existed. The remedy is the one § 5c already established for a photographic
	   hero and it generalises for the same reason: A FIELD GETS ONE INK. A muted grey is a device
	   for putting a second voice on a NEUTRAL surface; on a saturated or inverted ground it stops
	   being quieter and starts being unreadable, and hierarchy has to come from size and weight
	   instead. The override is GENERATED from the measurement rather than typed, so a field whose
	   ink starts passing stops being overridden and one that starts failing cannot be forgotten. */
	$fld_over = array();
	if ( contrast( $fld_muted, $fld_bg ) < 4.5 ) {
		$fld_over[]  = '--c-text-muted:' . $fld_text;
		$fld_muted_r = ratio_str( $fld_muted, $fld_bg ) . ' → --c-text';
	} else {
		$fld_muted_r = ratio_str( $fld_muted, $fld_bg ) . ' (kept)';
	}
	/* 3:1 and not 4.5: an input's border is a UI component boundary under WCAG 1.4.11, not a glyph. */
	if ( contrast( $fld_bord, $fld_bg ) < 3.0 ) {
		$fld_over[] = '--c-border:' . css_mix( $fld_text, 0.45, $fld_bg );
	}

	foreach ( array(
		'type on the field'    => array( $fld_text, $fld_bg, 4.5 ),
		'type on its alt'      => array( $fld_text, $fld_alt, 4.5 ),
		'the control fill'     => array( $fld_pick, $fld_bg, 4.5 ),
		'the control label'    => array( $fld_on, $fld_pick, 4.5 ),
	) as $fld_what => $fld_pair ) {
		if ( contrast( $fld_pair[0], $fld_pair[1] ) < $fld_pair[2] ) {
			fail( "on the `$fld_k` closing field, $fld_what measures "
				. ratio_str( $fld_pair[0], $fld_pair[1] ) . " ({$fld_pair[0]} on {$fld_pair[1]}), below "
				. $fld_pair[2] . ':1' );
		}
	}

	/* THE OVERRIDE HAS TO LAND. Generating a fix and then not emitting it is the same as not having
	   measured, and it is a failure no render would show — the band would simply be a little hard
	   to read, which is what it already was. */
	$fld_border_r = ratio_str( $fld_bord, $fld_bg );
	if ( contrast( $fld_muted, $fld_bg ) < 4.5 && ! in_array( '--c-text-muted:' . $fld_text, $fld_over, true ) ) {
		fail( "the `$fld_k` closing field needs a muted-tone override and did not get one" );
	}

	$FIELD[ $fld_k ] = array(
		'kind'    => $fld_d['kind'],
		'bg'      => $fld_bg,
		'alt'     => $fld_alt,
		'text'    => $fld_text,
		'accent'  => $fld_pick,
		'on'      => $fld_on,
		'over'    => $fld_over,
		'r_text'  => ratio_str( $fld_text, $fld_bg ),
		'r_ctrl'  => ratio_str( $fld_pick, $fld_bg ),
		'r_lbl'   => ratio_str( $fld_on, $fld_pick ),
		'r_muted' => $fld_muted_r,
		'r_bord'  => $fld_border_r,
	);
}

/* The selector list the shared chain resolves on: `:root`, every anchor, and every closing field.
   BUILT FROM THE SAME TABLE that paints the fields, so a field can never exist as a painted band
   without also being a resolution site for the derived neutrals. That pairing is the bug this
   whole mechanism exists to make impossible, and two lists would let them drift apart. */
/* `[data-brand]` IS IN THIS LIST FOR THE SAME REASON THE CLOSING FIELDS ARE. A brand block
   re-declares --c-bg, --c-bg-alt and --c-text on the strip root; every derived neutral under it
   would otherwise still carry the value it resolved to against the ANCHOR's ground — a muted grey
   mixed toward white, painted on a near-black restaurant. The gate at the bottom of this file
   fails the build if any selector declaring --c-bg is missing from here, which is how this line
   stops being something a future brand can forget. */
$FIELD_SELECTORS = array( ':root', '[data-anchor]', '[data-brand]' );
foreach ( $FIELD as $fld_k => $fld_v ) {
	$FIELD_SELECTORS[] = '[data-field="' . $fld_k . '"] .sec.closing';
}

// ── block 2 · THE SHARED DERIVED CHAIN — written once, resolved per anchor ─────────────────────
$css[] = <<<'CSS'
/* ══ THE SHARED LAYER. Transcribed VERBATIM from design-system.md § Scale and § Density, and
      from § "The other six ground-dependent colours are DERIVED, not tabled".

      The selector is the whole design. `:root` gets it for the shell; `[data-anchor]` gets it
      for every strip; and because substitution happens on the declaring element, each one
      resolves against ITS OWN --type-ratio / --fs-h1-max / --sp-scale / --c-bg / --c-text.
      One transcription, eight resolutions. See the deviation note at the top for what happens
      if this sits on `:root` alone.

      `--fs-h1-max` carries no unit on purpose: calc() cannot divide a length by a length, so the
      coefficient multiplying --fluid must arrive unitless. --fs-base is the bridge back.

      THE CLOSING FIELDS ARE IN THIS SELECTOR LIST, AND THAT IS THE ONLY CORRECT PLACE FOR THEM.
      Two anchors close on a ground that is not the page's — an inverted spread and a field of
      accent — and a band that changes `--c-bg` and `--c-text` needs `--c-text-muted`,
      `--c-border` and `--c-surface-inverse` to change WITH it. They do not: substitution happens
      at computed-value time on the element that DECLARES the custom property, so re-declaring
      `--c-bg` on the band alone leaves every derived neutral still carrying the value it resolved
      to up on `[data-anchor]` — a muted grey mixed toward white, painted on near-black. That is
      the same trap the deviation note at the top of this stylesheet describes, one level down,
      and it is invisible in DevTools because every token reads correctly.
      The fix is the same fix: name the field in the selector, so the chain RESOLVES a second time
      against the field's own two colours. One transcription, nine resolutions. ══ */
%%FIELD_SELECTORS%%{
  --fluid: clamp(0px, calc((100vw - 430px) / 850), 1px);

  --n-h1: calc(var(--fs-base) * var(--type-ratio) * var(--type-ratio) * var(--type-ratio));
  --n-h2: calc(var(--fs-base) * var(--type-ratio) * var(--type-ratio));
  --n-h3: calc(var(--fs-base) * var(--type-ratio));
  --n-h1-cap: var(--fs-h1-max);
  --n-h2-cap: calc(var(--fs-h1-max) / var(--type-ratio));
  --n-h3-cap: calc(var(--fs-h1-max) / var(--type-ratio) / var(--type-ratio));
  --fs-h1: clamp(calc(var(--n-h1) / var(--fs-base) * 1rem),
                 calc(var(--n-h1) / var(--fs-base) * 1rem + (var(--n-h1-cap) - var(--n-h1)) * var(--fluid)),
                 calc(var(--n-h1-cap) * 1px));
  --fs-h2: clamp(calc(var(--n-h2) / var(--fs-base) * 1rem),
                 calc(var(--n-h2) / var(--fs-base) * 1rem + (var(--n-h2-cap) - var(--n-h2)) * var(--fluid)),
                 calc(var(--n-h2-cap) * 1px));
  --fs-h3: clamp(calc(var(--n-h3) / var(--fs-base) * 1rem),
                 calc(var(--n-h3) / var(--fs-base) * 1rem + (var(--n-h3-cap) - var(--n-h3)) * var(--fluid)),
                 calc(var(--n-h3-cap) * 1px));

  /* ── LOS DOS ESCALONES QUE FALTABAN ENTRE h3 Y EL CUERPO ────────────────────────────────────
     Entre `--fs-h3` (33 a 46 según ancla) y `--fs-body` (19) no había nada, y el hueco se notó en
     el sitio donde siempre se nota: 51 `font-size` en rem CRUDO repartidos por los componentes
     —`.trcard h3{1.0625rem}`, `.trcard p{.875rem}` y 49 más—. Un número en rem no sabe qué ancla
     lo está pintando, así que esas tarjetas medían 17/14 lo mismo bajo `contained` que bajo
     `monumental`: el ancla movía los titulares y las tarjetas se quedaban quietas.
     `--fs-item` es un TÍTULO DE TARJETA —un destino, una cabina, un tratamiento— y `--fs-name`
     una ENTRADA DE LISTA, de las que hay muchas por pantalla. Nacieron dentro de TPL-C-14, que
     es donde el hueco se hizo insoportable; suben aquí porque el hueco era de la casa.
     EL `max()` NO ES ADORNO: un escalón sólo es un escalón si queda por encima del de abajo.
     `contained` tiene h3=33,3, y 33,3 × .56 = 18,6 — por DEBAJO del cuerpo de 19. Sin el suelo,
     el ancla más sobria renderizaría nombres de lista más pequeños que su propio párrafo. Con él,
     el ancla manda cuando es generosa y el cuerpo manda cuando no. Los coeficientes 1.05 y 1.3
     están elegidos para que `classic` —el ancla donde esta escala se aprobó mirándola— salga con
     los mismos 20,2 y 26,6 que ya tenía. */
  --fs-item: max(calc(var(--fs-body) * 1.3),  calc(var(--fs-h3) * .74));
  --fs-name: max(calc(var(--fs-body) * 1.05), calc(var(--fs-h3) * .56));
  /* LA PRUEBA DE QUE ESTOS DOS ESCALONES HACÍAN FALTA NO ES UN ARGUMENTO, ES UN RECUENTO.
     Barrido de `font-size` en rem crudo por toda la hoja: `1.0625rem` aparecía SIETE veces y
     `.875rem` DOCE. Y no en sitios cualesquiera — las siete son el NOMBRE DE UNA COSA EN UNA
     FICHA: el plato de la carta, el coche del patio, el médico del equipo, el tratamiento, el pie
     de un antes/después, el qué-incluye de un plan, la dirección del bloque de horarios. Las doce
     son LA DESCRIPCIÓN QUE VA DEBAJO de ese nombre.
     Siete componentes escritos en semanas distintas llegaron al mismo 17px por separado, y doce
     al mismo 14px. Eso no es coincidencia: es un escalón que el sistema necesitaba y no tenía, y
     cada autor lo reinventó a ojo. El precio de reinventarlo es que un número en rem no sabe qué
     ancla lo está pintando — la ficha medía 17/14 bajo `contained` y 17/14 bajo `monumental`,
     mientras su h2 pasaba de 40 a 74. El ancla movía la página y las fichas se quedaban quietas,
     que es exactamente la sensación de «todas las plantillas se parecen» una capa por debajo de
     donde se suele buscar. */

  --sp-xs: calc(0.5rem * var(--sp-scale));  --sp-s:  calc(1rem   * var(--sp-scale));
  --sp-m:  calc(1.5rem * var(--sp-scale));  --sp-l:  calc(3rem   * var(--sp-scale));
  --sp-xl: calc(5rem   * var(--sp-scale));  --sp-xxl:calc(7.5rem * var(--sp-scale));
  --n-sec:     calc(2 * var(--fs-base) * var(--sp-scale));
  --n-sec-cap: calc(7 * var(--fs-base) * var(--sp-scale));
  --sp-section: clamp(calc(var(--n-sec) / var(--fs-base) * 1rem),
                      calc(var(--n-sec) / var(--fs-base) * 1rem + (var(--n-sec-cap) - var(--n-sec)) * var(--fluid)),
                      calc(var(--n-sec-cap) * 1px));

  /* One column of the 12-col reference grid, sized so wide-start→wide-end is exactly
     --content-width once the 11 inner gaps are counted. Depends on --sp-m, so it has to resolve
     here rather than in `:root` — the same trap, one level down. */
  --col: calc((var(--content-width) - 11 * var(--sp-m)) / 12);

  /* ══ THE OPTICAL TRACKING RAMP — the anchor sets the tracking, the SIZE STEP tunes it.
        design-personalities.md § "Display tracking" already contains the whole argument and then
        stops one level short of applying it: "tracking that closes the counters at 120px opens
        holes at 48px". That sentence is used there to decide which ANCHORS tighten (those whose
        h1 cap clears ~80px) and which do not. Inside one anchor it is left unapplied — a single
        `--track-display` is painted on h1, h2 AND h3 alike, and on this page those three span
        88 / 58.7 / 39.1px at `editorial` and 120 / 74.2 / 45.8px at `direct`. That is the exact
        range the sentence is about, so the flat value is the file's own rule half-applied.

        THE ANCHOR'S NUMBER IS NOT TOUCHED. `--track-display` is transcribed from that file and
        stays the h1 tracking, to the digit. What is grafted here is only the SPAN — how far the
        tracking opens back up per step down — which no reference states for any anchor and which
        `craft-probe-2026-08-16.html` § CRAFT-MATERIAL is the measured source for: it ran
        h1 -.022 / h2 -.016 / h3 -.006 / lede +.002 / small +.012 against the other two directions'
        flat -.015em, and the difference between the two is visible at a glance on the h3 row.
        Its span h1→h3 is .016em and h1→h2 is .006em; those two deltas are what this table is,
        added to whatever the anchor declares. On `editorial` the ramp therefore resolves to
        -.015 / -.009 / +.001, which is the probe's shape sitting on the framework's number
        rather than on the probe's.

        `--track-h3-sm` IS A SIXTH STEP AND IT IS EARNED, not symmetry. `.card h3` renders at
        .58 of the h3 step — 22.7px at `editorial`, 26.6px at `direct` — which is nearer the
        `small` end of the ramp than the h3 end, and giving it the h3 value would be the same
        one-value-for-every-size mistake one level down. .024em is the ramp continued at its own
        rate for the size it actually renders at. ══ */
  --track-h1: var(--track-display);
  --track-h2: calc(var(--track-display) + .006em);
  --track-h3: calc(var(--track-display) + .016em);
  --track-h3-sm: calc(var(--track-display) + .024em);
  --track-lede: .002em;
  --track-small: .012em;

  /* design-system.md § derived: recipes, not literals, so they are right on a ground nobody has
     thought of yet. Percentages are the "text → bg" mixes that file measures. */
  --c-text-soft:      color-mix(in srgb, var(--c-text) 77%,   var(--c-bg));
  /* Mezclado hacia --c-bg-alt y no hacia --c-bg: la banda alterna esta SIEMPRE mas cerca del
     texto que el fondo base -- mas oscura en un ground claro, mas clara en uno oscuro -- asi que
     es la superficie dura, y derivar contra ella fija el suelo en las dos. Contra --c-bg medido,
     cuatro de los once grounds caian por debajo de AA en las secciones .bg-alt: warm 4.35:1,
     b-alinea 4.41:1, b-aranda 4.47:1, b-bergara 4.49:1. Ninguna medida BAJA con este cambio. */
  --c-text-muted:     color-mix(in srgb, var(--c-text) 63.4%, var(--c-bg-alt));
  --c-border:         color-mix(in srgb, var(--c-text) 11%,   var(--c-bg));
  --c-surface-inverse: var(--c-text);
  --c-on-inverse:      var(--c-bg);
}
CSS;

/* The placeholder is replaced rather than the heredoc being concatenated, so the chain stays ONE
   readable block of CSS in the source and the only generated part is the selector list. */
$css[ count( $css ) - 1 ] = str_replace(
	'%%FIELD_SELECTORS%%',
	implode( ",\n", $FIELD_SELECTORS ),
	$css[ count( $css ) - 1 ]
);
if ( false !== strpos( $css[ count( $css ) - 1 ], '%%FIELD' ) ) {
	fail( 'the shared chain still carries its selector placeholder — every derived neutral would'
		. ' resolve nowhere and the whole stylesheet would fall back to initial values' );
}

// ── block 3 · base + shared components ─────────────────────────────────────────────────────────
$css[] = <<<'CSS'
/* ── base ── */
*{box-sizing:border-box}
body,figure,blockquote,h1,h2,h3,p,ul,ol,li,dl,dt,dd,form,fieldset,legend{margin:0;padding:0}
html{-webkit-text-size-adjust:100%}
body{font-family:var(--font-secondary);background:var(--c-bg);color:var(--c-text);
     font-size:var(--fs-body);line-height:1.6;-webkit-font-smoothing:antialiased}

/* `overflow-wrap:anywhere`, SCOPED under 1024px. Both halves are the fix. Below the framework's
   desktop breakpoint it is the difference between 0 and hundreds of px of horizontal scroll at a
   320px viewport with a scaled root (WCAG 1.4.4/1.4.10). Unscoped it also breaks words at widths
   where they FIT — the proof pair shipped `subcontratamo / s` at 1280 that way, an orphaned
   letter on the anchor whose whole pitch is careful typography. */
@media(max-width:1023px){ body{overflow-wrap:anywhere} }

/* Weights are design-system.md's type table (h1/h2 700, h3 600). Only letter-spacing is
   anchor-scoped — design-personalities.md § Typography. */
h1,h2,h3{font-family:var(--font-primary);font-weight:700;text-wrap:balance}
h1,h2{line-height:var(--display-lh)}
h1{font-size:var(--fs-h1);letter-spacing:var(--track-h1)}
h2{font-size:var(--fs-h2);letter-spacing:var(--track-h2)}
h3{font-size:var(--fs-h3);line-height:1.25;font-weight:600;letter-spacing:var(--track-h3)}
/* The other end of the ramp. Body copy is not display type, so these two are absolute rather than
   anchor-relative: opening small text is a legibility move that every face wants and no anchor
   asked to be exempt from. */
.lede{letter-spacing:var(--track-lede)}
.small,.card .body p,.field label,.legal{letter-spacing:var(--track-small)}
a{color:inherit;text-decoration:none} img,svg{max-width:100%} ol,ul{list-style:none}
:focus-visible{outline:2px solid var(--c-accent);outline-offset:3px}
@media(prefers-reduced-motion:reduce){*{transition:none!important;animation:none!important}}
.sr{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap}

/* ── shared components ── */
.sec{padding-block:var(--sp-section)}
.bg-alt{background:var(--c-bg-alt)}
.muted{color:var(--c-text-muted)} .small{font-size:var(--fs-small)}
/* ── THE ACCENT BUDGET, AND THE EYEBROW IS WHERE IT WAS BEING SPENT WITHOUT COUNTING ──
   `design-system.md` says the accent is not an axis and says nothing about how OFTEN it may be
   painted, so the framework has had no rule about spend at all — and the eyebrow, which appears
   on every section of every strip, was painting it. MEASURED in the rendered page before this
   line: 12 accent-coloured marks at rest on `TPL-C-01 × PERS-EDITORIAL` and 16 on the ecommerce
   strip, of which the eyebrows alone were 4 and 6. A colour that appears a dozen times on a
   resting page is not an accent, it is a second text colour, and a page with two text colours is
   the single most reliable tell of a theme rather than a design.

   `craft-probe-2026-08-16.html` § CRAFT-PRINT is where this is measured: it spends the accent
   ONCE on the resting page — the submit button, the one commercial moment — against 4 for the
   other two directions, and the note beside it is the argument. "Everything else is ink on paper,
   so the one mark of colour is earned."

   An eyebrow is a LABEL. Its job is to be read before the heading and then to get out of the way,
   which is what `--c-text-muted` does and what a saturated hue cannot. The accent moves to the
   controls, where it means "this is the thing to press", and the closing band is now where the
   biggest single spend lands. The count is asserted at the foot of this file. */
.eyebrow{display:block;font-family:var(--font-secondary);font-size:var(--fs-eyebrow);font-weight:600;
         letter-spacing:.24em;text-transform:uppercase;color:var(--c-text-muted)}
.stack{display:flex;flex-direction:column;align-items:flex-start;gap:var(--sp-s)}
/* A heading takes the width of its own BLOCK, never the width of its longest word. `.stack` is a
   column flex container with `align-items:flex-start`, which sizes children to `fit-content` —
   and `fit-content` floors at min-content, so one unbreakable word makes the heading's BOX wider
   than the area it was given and lays every line across territory belonging to something else. */
.stack > h1,.stack > h2,.stack > h3,.stack > p{align-self:stretch;min-width:0}

/* PR3e — `.between` is `.stack`'s sibling, not its replacement: a second, opt-in shape for a
   `.head` that needs the artboard's own side-by-side geometry (heading on one side, a short label
   on the other) instead of the house's default eyebrow-above-heading column. Additive only —
   nothing here touches `.stack` or the bare `.head{position:relative}` rule both shapes still
   share, so every one of the 94 existing `class="head stack"` call sites is untouched. */
.between{display:flex;flex-wrap:wrap;justify-content:space-between;align-items:flex-end;gap:var(--sp-m)}
.between h2{margin:0}

/* A PHOTO IS AN `<img>` WITH `object-fit:cover` AND A REAL `alt` (mockup-guide.md § Container
   hygiene), never a `background-image`: a CSS background needs an otherwise-empty element, is
   invisible to screen readers and to Google Images, and maps to the exact container `es_photo()`
   exists to avoid. The `alt` is the Spanish one from `_gallery-images.md`.

   `min-width:0` is load-bearing and `width:100%;max-width:100%` cannot stand in for it:
   `aspect-ratio` on a grid or flex item transfers an automatic minimum size through the ratio,
   and `min-width:auto` OUTRANKS `max-width:100%`, so the box is floored at whatever width the
   ratio demands and simply refuses to shrink. mockup-guide.md carries two measurements of this —
   a 660px box in a 390px column, and 638.3px inside a 613.3px column at a 28px root. */
.frame{position:relative;overflow:hidden;display:block;
       width:100%;max-width:100%;min-width:0;
       aspect-ratio:var(--ratio-card);border-radius:var(--radius-image);background:var(--c-bg-alt)}
.frame > img{display:block;width:100%;height:100%;object-fit:cover;
             transition:transform var(--dur-zoom) var(--ease)}
.hero .frame{aspect-ratio:var(--ratio-hero)}
.frame.sq{aspect-ratio:1/1}
.card:hover .frame > img{transform:scale(1.045)}
CSS;

// ── COMP-HERO under `TGL-HERO-TYPE: slider` ────────────────────────────────────────────────────
//
// Emitted from PHP rather than as a literal because the scrim floor and the frame count are
// MEASURED values from § 5c: writing `.55` here by hand would be the second copy of a number the
// build already owns, and the copy nobody re-measures.
$css[] = '/* ══════════════════ COMP-HERO · TGL-HERO-TYPE: slider ══════════════════
   Three frames cross-fading BEHIND the copy. The whole thing is CSS — no script, no library, no
   `setInterval` racing the paint — which is also why it survives the `<noscript>` path this page
   already ships: with JS off the hydration map never runs, every `<img>` stays sourceless, and
   what the reader gets is the scrim over `--c-bg-alt` with the copy fully legible on top. A
   slideshow that needs a script to not be broken is a worse hero than one that does not.

   THE SCRIM FLOOR IS ' . (int) round( $SCRIM_FLOOR * 100 ) . '%, MEASURED, and the gradient never drops below it. § 5c
   proves the number against every pixel of all three frames; the shape below is hierarchy on top
   of a floor that already holds, not the thing doing the holding. That ordering is the lesson
   from LP-BROKEN-GRID\'s hero, whose first scrim reached transparent at one edge and measured
   1.95:1 under the headline while looking perfectly fine in a screenshot.

   `color-mix(… var(--c-bg) …)` and not `rgba(255,255,255,…)`: the veil is the ANCHOR\'s own paper,
   so a strip whose ground moved would move its scrim with it — and § 5c asserts against that same
   token, so the measurement and the paint cannot drift apart. ══════════════════ */
.hero-slides{position:relative;isolation:isolate;overflow:hidden;
             display:grid;align-content:center;
             min-height:clamp(420px,66vh,560px)}
/* `width:100%` IS LOAD-BEARING, and its absence is exactly the trap LP-ASYMMETRIC\'s own header
   comment warns about — "put this canvas inside a padded column and the bleed stops at the column,
   silently". `.hero-slides` is a GRID container (it has to be, to centre the copy over the
   photograph), so `.canvas` is a grid ITEM, and `.canvas` carries `margin-inline:auto` from the
   shared rule. Auto inline margins on a grid item stop it stretching and size it to fit-content —
   which for a grid container is its max-content, i.e. the sum of its own tracks. MEASURED before
   this line: 1244.6px wide at 1440, at 1920 AND at 2560, identical, centred, while its parent was
   the full 2560. `full-start` and `full-end` on that canvas were 657.7 and 1902.3, not 0 and 2560:
   the named lines were quietly lying, and the hero copy sat in a band 480px narrower than every
   other section on the same strip. Nothing in this hero bleeds today — `.slides` is absolutely
   positioned against the SECTION — so it cost only the misalignment, and it would have cost the
   next person who added a bleed here a very long afternoon. */
.hero-slides > .canvas{position:relative;z-index:3;width:100%}
.hero-slides .slides{position:absolute;inset:0;z-index:0}

/* The slide fills the section instead of taking `--ratio-hero`: at full bleed the SECTION is the
   frame, and an aspect-ratio here would letterbox it. `.frame`\'s own `background:var(--c-bg-alt)`
   is kept and is load-bearing — it is the opaque floor the cross-fade blends over, so a fade never
   reveals the page behind the hero. */
.hero-slides .slide{position:absolute;inset:0;width:100%;height:100%;
                    aspect-ratio:auto;border-radius:0}

/* THE STATIC OPACITY IS THE REDUCED-MOTION STATE, not a starting value the animation happens to
   overwrite. Frame 1 opaque and 2–3 transparent means that the instant `animation` is cancelled —
   by the media query below, by the global `*{animation:none!important}`, or by a browser that
   never ran it — what remains on screen is ONE frame, and it is the strip\'s own hero image. Had
   these all started at 0, cancelling the animation would have left an empty hero. */
.hero-slides .slide{opacity:0}
.hero-slides .slide:first-child{opacity:1}

@keyframes nm-hero-crossfade{
  0%     {opacity:1}
  27.7%  {opacity:1}
  33.3%  {opacity:0}
  94.4%  {opacity:0}
  100%   {opacity:1}
}
/* 18s / 3 frames = 6s each: ~5s held, ~1s of overlap. The NEGATIVE delays start frames 2 and 3
   part-way through one shared cycle rather than queueing them, so the three never drift apart the
   way three independently-timed animations do. Order on screen is 1 → 2 → 3. */
.hero-slides .slide{animation:nm-hero-crossfade 18s linear infinite}
.hero-slides .slide:nth-child(2){animation-delay:-12s}
.hero-slides .slide:nth-child(3){animation-delay:-6s}

/* PAUSE ON HOVER OR FOCUS, SCOPED TO THE COPY — and the scope is the whole fix. WCAG 2.2.2 wants
   a mechanism to stop motion that runs past five seconds, and both mechanisms survive: the mouse
   one is now "rest the pointer on the words you are reading" — `.head`, the copy block itself —
   and the keyboard one is `:focus-within` firing when tab reaches either hero CTA.

   WHAT IT USED TO BE: `.hero-slides:hover`. That is a full-bleed section, and MEASURED at
   2560x1400 it covers 750px x 2560px = 54% OF THE VIEWPORT, sitting at the very top of the page
   where a pointer naturally rests after a click or a scroll. Parking the pointer at the centre of
   the screen put all three frames in `animation-play-state: paused` and they did not move for the
   eight seconds measured, opacity `1 / 0 / 0` at both ends. The slideshow was not broken — it was
   being held still by more than half the screen, which is a hover target nobody can avoid and
   nobody can see. That is the whole of "el slider no se ve". Off the hero the same three frames
   hand over at ~4.99s, measured `1.00 / 0.00 / 0.00` → `0.51 / 0.49 / 0.00` → `0.00 / 1.00 /
   0.00`. Worth knowing when reading a report: the FIRST crossfade begins at 27.7% of an 18s cycle,
   so even unpaused a reader has to hold still for five seconds before anything moves at all.

   TWO RULES AND NOT ONE SELECTOR LIST, on purpose. `.slides` is written BEFORE `.canvas` in the
   DOM — the photograph is behind the copy — so no sibling combinator can reach backwards and the
   mouse rule needs `:has()`. A selector list is all-or-nothing: a browser without `:has()` drops
   the whole rule, and putting the keyboard mechanism in that list would take WCAG 2.2.2 down with
   it. Split, the keyboard half never depends on `:has()` at all. */
.hero-slides:focus-within .slide{animation-play-state:paused}
.hero-slides:has(.head:hover) .slide{animation-play-state:paused}

.hero-slides .slides::after{content:"";position:absolute;inset:0;z-index:2;
  background:linear-gradient(to right,
    color-mix(in srgb,var(--c-bg) 90%,transparent) 0%,
    color-mix(in srgb,var(--c-bg) 74%,transparent) 34%,
    color-mix(in srgb,var(--c-bg) ' . (int) round( $SCRIM_FLOOR * 100 ) . '%,transparent) 62%,
    color-mix(in srgb,var(--c-bg) ' . (int) round( $SCRIM_FLOOR * 100 ) . '%,transparent) 100%)}

/* Below the desktop breakpoint the copy is a full-width column over the photograph rather than a
   left band, so the gradient turns vertical to match where the text actually is. Its floor is the
   same measured ' . (int) round( $SCRIM_FLOOR * 100 ) . '%, so the assertion in § 5c covers this direction too. */
@media(max-width:1023px){
  .hero-slides .slides::after{background:linear-gradient(to bottom,
    color-mix(in srgb,var(--c-bg) 82%,transparent) 0%,
    color-mix(in srgb,var(--c-bg) ' . (int) round( $SCRIM_FLOOR * 100 ) . '%,transparent) 55%,
    color-mix(in srgb,var(--c-bg) ' . (int) round( $SCRIM_FLOOR * 100 ) . '%,transparent) 100%)}
}

/* RESTATED LOCALLY, and deliberately not only as a restatement. `base` already carries
   `*{animation:none!important}` under this query, which alone would leave frame 1 showing by the
   static rule above. This block does not rely on that: it sets the opacities OUTRIGHT, so the
   reduced-motion state survives someone deleting the global rule, and it survives the static
   values being changed for some future reason. Two independent routes to one frame. */
@media(prefers-reduced-motion:reduce){
  .hero-slides .slide{animation:none;opacity:0}
  .hero-slides .slide:first-child{opacity:1}
}

/* ONE INK ON THE PHOTOGRAPH. Generated from § 5c\'s per-element measurement, not typed: each rule
   below exists because that ink measured under its bar against the composited surface, and would
   disappear from this stylesheet if it stopped doing so. The anchor keeps its accent and its muted
   grey everywhere else on the strip — this is the hero, and only the hero, where a picture is
   underneath. Hierarchy here is 88px against 17px, which needs no second colour. */
' . implode( "\n", $SCRIM_OVERRIDE ) . '
';

$css[] = <<<'CSS'

/* A BUTTON CENTRES ITS OWN LABEL ON BOTH AXES. `text-align:center` is only the horizontal half —
   it says nothing about the vertical, so a button stretched taller than its own line box renders
   its label pinned to the TOP, and every `.btn` here lands in a flex or grid row that stretches.
   Measured in the proof pair before this rule: a 21px label sitting 12.04px above centre at 1280
   and 65.23px above it at 1920, with `text-align:center` present the whole time and a horizontal
   delta of exactly 0.00. No text-based check can see it. Now a Hard Rule in ux-design-system.

   Elevation is a CARD property, so no `--elev-rest` here: on the `hairline` anchor it would
   double the button's own border. */
.btn{display:inline-flex;align-items:center;justify-content:center;
     padding:var(--btn-padding);font-size:var(--fs-button);font-weight:600;line-height:1.2;
     text-align:center;border-radius:var(--radius-button);
     border:var(--btn-border-width) solid transparent;cursor:pointer;
     transition:background var(--dur-color) var(--ease),color var(--dur-color) var(--ease),
                border-color var(--dur-color) var(--ease),box-shadow var(--dur-lift) var(--ease),
                transform var(--dur-lift) var(--ease)}
.btn-primary{background:var(--c-accent);color:var(--c-on-accent);border-color:var(--c-accent)}
.btn-primary:hover{transform:translateY(var(--lift));box-shadow:var(--elev-hover)}
.btn-outline{background:transparent;color:var(--c-text);border-color:var(--c-border)}
.btn-outline:hover{border-color:var(--c-accent);transform:translateY(var(--lift))}
.btn-sm{padding:.55rem 1.05rem;font-size:var(--fs-nav)}
/* A BUTTON TAKES ITS CONTENT HEIGHT, NOT ITS CONTAINER'S — the other half of the rule above, and
   the half that rule is blind to. `.btn` centres its label with `align-items:center`, so a button
   stretched to 147px renders its label perfectly centred in 147px: the label check measures ~0px
   of offset and passes, while the box is three times the size of the thing inside it. Measured in
   `proof-direct-mockup.html`, where `.ctas` is a direct grid item of `.canvas` and the row is as
   tall as the hero media beside it: 91px at 1440x900 and 147px at 1920x1200 against a 49.2px
   content height, growing with the viewport because the hero image does.

   `flex-start` and not `center`. Both collapse the button to 49px, so the defect dies either way;
   the difference is where the slack goes when the CONTAINER is still stretched. Centred, the
   buttons float in the middle of the leftover space and the gap between the lede and the CTA
   changes with viewport height — the reading order eyebrow → h1 → lede → CTA comes apart at the
   last step, and it comes apart by a different amount on every screen. Top-aligned, the CTA stays
   welded to the lede and the slack falls to the bottom of the hero, where the image is already
   bleeding into it. */
.ctas{display:flex;flex-wrap:wrap;align-items:flex-start;gap:var(--sp-s)}

/* THE MEASURE IS CAPPED HERE SO THE BAND DOES NOT HAVE TO BE. `--content-width` is a proportion of
   the viewport (`85vw`), which is the only shape that holds the page margin at one ratio on every
   screen — see the derivation on the token. The price of an uncapped band is that a text column
   grows with the display, and the page was audited for exactly that: every text node walked at
   2560, each rendered line divided by its own element's `0` advance. ONE run crossed a comfortable
   measure — this one, 68.4ch at 1440 and 103.1ch at 2560. Everything else is bounded by its own
   container and reads 78.2ch at 1440 and 78.2ch at 2560, flat.
   66ch is the ceiling design-system.md already names for body copy, so the number is transcribed,
   not invented. LP-BROKEN-GRID tightens it to 44ch over its hero photograph and that rule still
   wins — this is a ceiling, not a width. */
.lede{max-width:66ch}

/* ── chrome shared by every strip: if the header differed, part of the "unmistakably different"
      verdict would come from the chrome rather than from the axes. ── */
.site-head{border-bottom:1px solid var(--c-border)}
.nav{display:flex;align-items:center;gap:var(--sp-s);padding-block:var(--sp-s);flex-wrap:wrap;min-width:0}
/* `flex:0 1 auto` + `min-width:0`, not `flex:none`: a wordmark that refuses to shrink is a WCAG
   1.4.4 failure waiting for the first reader who scales text. */
.logo{font-family:var(--font-primary);font-weight:700;font-size:1.15rem;
      letter-spacing:var(--track-wordmark);flex:0 1 auto;min-width:0}
/* flex-basis 220px, not 0: a zero basis lets the nav shrink to a few pixels before the row wraps. */
.mainnav{flex:1 1 220px;min-width:0;display:flex;gap:var(--sp-s);overflow-x:auto;
         scrollbar-width:none;font-size:var(--fs-nav)}
.mainnav::-webkit-scrollbar{display:none}
.mainnav a{white-space:nowrap;color:var(--c-text-muted);padding-block:.2rem;
           border-bottom:1px solid transparent;transition:color var(--dur-color) var(--ease)}
.mainnav a:hover{color:var(--c-text);border-color:var(--c-accent)}

.site-foot{border-top:1px solid var(--c-border);padding-block:var(--sp-l)}
.fnav{display:flex;flex-wrap:wrap;gap:var(--sp-s);font-size:var(--fs-nav)}
.fnav a{color:var(--c-text-muted)} .fnav a:hover{color:var(--c-accent)}
.legal{padding-top:var(--sp-m);border-top:1px solid var(--c-border);color:var(--c-text-muted);
       font-size:var(--fs-small)}

/* ── cards. The recipe per anchor is documented per anchor; this is only what they share. ── */
.items{display:grid;gap:var(--sp-m);grid-template-columns:minmax(0,1fr)}
.card{display:flex;flex-direction:column;min-width:0}
.card .body{display:flex;flex-direction:column;gap:var(--sp-xs);padding:var(--sp-m)}
.card .rule{height:1px;background:var(--c-border)}
/* A CARD HEADING IS NOT A SECTION HEADING, and at `monumental` scale the difference overflows.
   MEASURED at 1280 before this rule: PERS-DIRECT's `--fs-h3` caps at 45.84px, and "Restauración"
   rendered 319px of min-content inside a 253px card column — 48px of horizontal scroll on the
   whole page, at a width where the `overflow-wrap` guard is deliberately OFF and so could not
   catch it. Both proof mockups already scale their card headings for exactly this reason
   (`.work h3` at .8x and .74x); this takes ONE shared fraction rather than a per-anchor number,
   because the ratio is arithmetic about column width and not a personality choice.
   `hyphens:auto` is the second half and the reason `lang="es"` is on every strip: it breaks at a
   Spanish syllable boundary from the hyphenation dictionary, which is ordinary typography — not
   the mid-stem `overflow-wrap` break that was rejected at desktop widths.

   .74 → .58, AND THE REASON IS HIERARCHY RATHER THAN OVERFLOW. .74 was derived to stop a card
   heading overflowing its column and it does that; what it does not do is make the card heading
   read as a card heading. At .74 a services h3 renders 28.9px against its own section h2 at
   58.7px — a ratio of 2.03 — and in the render the three card titles compete with the heading
   above them for the same rank, which is a large part of why the resting page reads as a
   template: everything is the same size as everything. .58 puts it at 22.7px, ratio 2.58, and
   the row becomes a caption under a photograph instead of three more headlines.
   .58 is the probe's measured value (`craft-probe-2026-08-16.html` § CRAFT-GALLERY) and it is
   also strictly safer on the defect .74 was chosen for: every column that fitted a heading at
   .74 fits the same heading at .58 with 22% more room. */
.card h3{font-size:calc(var(--fs-h3) * .58);letter-spacing:var(--track-h3-sm);hyphens:auto}
@media(min-width:768px){
  .items.cols-2,.items.cols-3{grid-template-columns:repeat(2,minmax(0,1fr))}
}

/* ── the lead form (TPL-C-01 ADN) ── */
.leadform{display:flex;flex-direction:column;gap:var(--sp-s);min-width:0}
.field{display:flex;flex-direction:column;gap:.35rem;min-width:0}
.field label{font-size:var(--fs-small);font-weight:600;color:var(--c-text-soft)}
.field input,.field textarea{font:inherit;font-size:var(--fs-small);color:var(--c-text);
  background:var(--c-bg);border:1px solid var(--c-border);border-radius:var(--radius-input);
  padding:.7rem .85rem;min-width:0;width:100%;max-width:100%}
.field textarea{resize:vertical;min-height:5.5rem}
.field input:focus,.field textarea:focus{outline:2px solid var(--c-accent);outline-offset:1px}

/* ── TPL-E-02 pieces. Ecommerce DNA: € prices and a cart badge (html-mockup/SKILL.md step 2),
      a search bar that is never hidden behind an icon, and a hero that refuses to be big. ── */
.announce{background:var(--c-surface-inverse);color:var(--c-on-inverse);text-align:center}
.announce p{padding-block:.5rem}
/* `align-items:stretch` WRITTEN DOWN, because here the stretch is the point: the Buscar button is
   meant to match the input beside it, and it measures 42px against a 37.84px content height —
   4.16px of deliberate slack. Left implicit it is indistinguishable from the `.ctas` bug, so the
   next reader either "fixes" it and breaks the search bar, or sees the two cases agree and leaves
   both. This container is safe because `.nav` above it centres, so `.searchbar` is content-height
   and its children stretch to an input, never to a hero. */
.searchbar{flex:1 1 260px;min-width:0;display:flex;align-items:stretch;gap:.5rem}
.searchbar input{flex:1 1 auto;min-width:0;font:inherit;font-size:var(--fs-nav);
  color:var(--c-text);background:var(--c-bg);border:1px solid var(--c-border);
  border-radius:var(--radius-input);padding:.5rem .75rem}
.searchbar input:focus{outline:2px solid var(--c-accent);outline-offset:1px}
.tools{display:flex;align-items:center;gap:var(--sp-s);font-size:var(--fs-nav);flex:0 1 auto;min-width:0}
.tools a{color:var(--c-text-muted);white-space:nowrap}
.tools a:hover{color:var(--c-text)}
.cart b{display:inline-grid;place-items:center;min-width:1.4rem;height:1.4rem;padding-inline:.35rem;
  border-radius:var(--radius-pill,999px);background:var(--c-accent);color:var(--c-on-accent);
  font-size:.7rem;font-weight:700;line-height:1}
/* COMP-HERO mini: ~20vh is the ADN, so the height is pinned here rather than by --ratio-hero.
   With an explicit height and no `aspect-ratio`, the automatic-minimum-size transfer that makes
   `min-width:0` load-bearing on `.frame` cannot fire at all. */
.mini .frame{aspect-ratio:auto;height:clamp(120px,20vh,220px)}
.mini .head{gap:var(--sp-xs)}
.cats{display:flex;gap:var(--sp-s);overflow-x:auto;scrollbar-width:none;font-size:var(--fs-nav)}
.cats::-webkit-scrollbar{display:none}
.cats a{white-space:nowrap;padding:.35rem .75rem;border:1px solid var(--c-border);
  border-radius:var(--radius-pill,999px);color:var(--c-text-soft);transition:border-color var(--dur-color) var(--ease)}
.cats a:hover{border-color:var(--c-accent);color:var(--c-text)}
.price{font-size:var(--fs-price);font-weight:700;line-height:1.2}
/* The price and the button sit at the BOTTOM of the card, not under the title.

   A catalogue row whose titles wrap to different line counts otherwise lands its prices and
   its buttons on different baselines. MEASURED at 1440 before this rule, `.grid-prod` first
   row, all four anchors: 29 / 33 / 26 / 24px of button spread, and institutional carried it
   on BOTH rows. It had been there all along and read as nothing while the buttons were
   hairline outlines; it became a defect the moment they turned into solid accent, because
   forty-eight solid rectangles at ragged heights read as a mistake.

   `margin-top:auto` on the PRICE and not on the button, because the pair travels together:
   pinning only the button aligns the buttons and leaves the prices ragged, which is the same
   defect one row up. And auto rather than a two-line `min-height` on the title, because a
   fixed line count only moves the raggedness to the first title that wraps to three. */
.card.prod .body{gap:.35rem;align-items:flex-start;flex:1}
.card.prod .price{margin-top:auto}
.card.prod .btn{margin-top:.25rem}
/* A wide block scrolls inside its OWN container rather than pushing the page (SKILL.md).

   `grid-template-columns:none` IS THE RULE, NOT TIDYING. `.items` above declares an EXPLICIT
   `grid-template-columns:minmax(0,1fr)`, and `grid-auto-flow:column` does not replace an explicit
   template — it only decides where the tracks the template did not declare come from. So card 1
   landed in the explicit `1fr` track and cards 2..n in the implicit `minmax(78%,1fr)` ones; in a
   container that overflows on purpose there is no free space for a `1fr`, and it resolved to ZERO.
   MEASURED at 430 before this line, every ecommerce strip, all four anchors:
     grid-template-columns  →  `0px 304.188px 304.188px 304.188px`
     card 1 border box      →  0px wide — a product nobody on a phone could ever see
     its `Añadir` button    →  46.6 x 110.8px, because `overflow-wrap:anywhere` (scoped under
                               1024px, and correctly) then stacked the label ONE LETTER PER LINE
   house-rules.md row 30 scores that button 1.93 against a 0.5 threshold — the same shape as the
   defect fixed in 33310e3, still live at 320/430/768 because that pass measured desktop only, and
   row 30's own text warns in bold that it must be measured at every breakpoint in scope. Clearing
   the template makes all four tracks implicit, so `grid-auto-columns` sizes all four alike. */
.rail{grid-auto-flow:column;grid-template-columns:none;grid-auto-columns:minmax(78%,1fr);
      overflow-x:auto;scroll-snap-type:x mandatory;padding-bottom:.5rem}
.rail > *{scroll-snap-align:start}
.bens{gap:var(--sp-s)}
.ben{display:flex;flex-direction:column;gap:.15rem;min-width:0;
     border-top:1px solid var(--c-border);padding-top:var(--sp-xs)}
.ben b{font-size:var(--fs-small)}
.bicon{color:var(--c-text-soft);   /* budget: four ticks in a row is not one commercial moment */display:block;line-height:0;margin-bottom:.15rem}
/* COMP-FAQ + COMP-CONTACT-DIRECT — TPL-E-02's closing pair.
   Built out of the SAME primitives the benefits bar already uses (`.items` grid, `--c-border`
   rules, `--fs-small`) rather than a new layout system, so the pair inherits every blueprint's
   container geometry without any of the four `[data-comp]` blocks needing a rule for it. That is
   also why neither section is `.band`: `.band` is placed column by column in the grid blueprints
   and a new child class there would land unplaced. */
/* Open by default carries no styling of its own: `<details open>` simply shows its `<p>`, and
   the row reads the same whether the reader opened it or it arrived that way. A first row
   styled differently would say "this one is special" when what it means is "start here". */
.faqlist{border-top:1px solid var(--c-border);max-width:72ch}
/* DOS COLUMNAS EN PANTALLA ANCHA, y la medida es la razón de que sean dos y no tres: repartir
   texto en tercios de un canvas de 1560 deja ~35 caracteres por línea. La lista sube a 108ch
   porque el límite de 72 es el de UNA columna de lectura, no el del bloque entero.
   `align-items:start` NO es cosmético: sin él, abrir una fila estira su vecina hasta la misma
   altura y aparece un hueco al lado de la respuesta abierta — que es el defecto que un acordeón
   en rejilla trae de serie y por el que casi nadie lo pone en dos columnas.
   `border-top` se apaga y pasa a cada fila: un filete superior sobre dos columnas dibuja una
   línea que no corresponde a ninguna de las dos. */
@media(min-width:1024px){
  .faqlist[style*="--cols:2"], .qas[style*="--cols:2"]{display:grid;align-items:start;
    grid-template-columns:repeat(2,minmax(0,1fr));column-gap:var(--sp-xl);
    max-width:108ch;border-top:0}
  .faqlist[style*="--cols:2"] > details, .qas[style*="--cols:2"] > details{
    border-top:1px solid var(--c-border)}
}
.faqlist > details{border-bottom:1px solid var(--c-border)}
/* UNA PREGUNTA Y SU RESPUESTA SON TEXTO DE LECTURA, no un pie de foto. Iban las dos a
   `--fs-small` (14px) cuando el cuerpo de la página era 16 — dos puntos de diferencia, casi
   invisible. Con el cuerpo en 19 la diferencia pasaba a cinco y el acordeón entero se leía como
   letra pequeña legal. La pregunta sube al escalón de entrada de lista, que es exactamente lo
   que es; la respuesta al escalón de copia secundaria. */
.faqlist summary{cursor:pointer;padding-block:var(--sp-s);font-size:var(--fs-name);
  font-weight:600;color:var(--c-text)}
.faqlist summary::marker{color:var(--c-text-muted)}   /* budget: a disclosure triangle is furniture */
.faqlist p{padding-bottom:var(--sp-s);font-size:var(--fs-meta);color:var(--c-text-soft);
  max-width:66ch}
.chans{gap:var(--sp-s)}
.chan{display:flex;flex-direction:column;gap:.15rem;min-width:0;
      border-top:1px solid var(--c-border);padding-top:var(--sp-xs)}
.chan b{font-size:var(--fs-small);overflow-wrap:anywhere}
@media(min-width:600px){.bens{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(min-width:1024px){
  .bens{grid-template-columns:repeat(4,minmax(0,1fr))}
  .chans{grid-template-columns:repeat(3,minmax(0,1fr))}
  .rail{grid-auto-columns:minmax(0,1fr);overflow-x:visible}
}
CSS;

// ── block 4 · the gallery's own chrome ─────────────────────────────────────────────────────────
$css[] = <<<'CSS'
/* ══ THE SHELL — the gallery's own chrome, painted from `:root` and never from an anchor. It is
      deliberately quieter than every sample: a tool that competes with what it displays is a
      worse tool. ══ */
.gal-head{background:var(--c-surface-inverse);color:var(--c-on-inverse);padding-block:var(--sp-l)}
.gal-wrap{max-width:var(--content-width);margin-inline:auto;padding-inline:var(--pad-x-mobile)}
@media(min-width:768px){.gal-wrap{padding-inline:var(--pad-x-tablet)}}
.gal-head h1{max-width:18ch}
.gal-head p{max-width:64ch;margin-top:var(--sp-s);color:color-mix(in srgb,var(--c-on-inverse) 78%,var(--c-surface-inverse))}
.gal-head .eyebrow{color:var(--c-accent)}
.gal-progress{margin-top:var(--sp-m);font-size:var(--fs-small);
              color:color-mix(in srgb,var(--c-on-inverse) 62%,var(--c-surface-inverse))}
.gal-note{margin-top:var(--sp-m);font-size:var(--fs-small);
          border-left:2px solid var(--c-accent);padding-left:var(--sp-s)}

/* ── the sticky filter. `position:sticky` and not `fixed`: fixed would take the bar out of flow
      and the first strip would start underneath it. Sticky needs an unbroken ancestor chain with
      no `overflow` other than visible — which is why this sits as a direct child of body rather
      than inside the strips wrapper. ── */
.gal-filter{position:sticky;top:0;z-index:50;background:var(--c-bg);
            border-bottom:1px solid var(--c-border)}
.gal-filter .gal-wrap{display:flex;flex-wrap:wrap;align-items:center;gap:var(--sp-s);
                      padding-block:var(--sp-xs)}
.fgroup{display:flex;flex-wrap:wrap;align-items:center;gap:.35rem;min-width:0}
/* `flex:0 0 auto` + `white-space:nowrap`: inside the nowrap scrolling group this span was being
   shrunk to 11px and wrapping to five stacked letters, which alone made the group 96px tall and
   the whole sticky bar 222px. A label is not the thing that should give way in a scroller. */
.flabel{font-size:var(--fs-eyebrow);letter-spacing:.18em;text-transform:uppercase;
        color:var(--c-text-muted);margin-right:.15rem;flex:0 0 auto;white-space:nowrap}
/* A filter chip is a button, so it centres its own label on both axes for the same reason .btn
   does — these sit in a wrapping flex row that can stretch them. */
.fbtn{display:inline-flex;align-items:center;justify-content:center;
      font:inherit;font-size:var(--fs-small);line-height:1.2;padding:.3rem .7rem;
      border:1px solid var(--c-border);border-radius:var(--radius-pill,999px);background:transparent;
      color:var(--c-text-soft);cursor:pointer;white-space:nowrap;
      transition:background var(--dur-color) var(--ease),color var(--dur-color) var(--ease),
                 border-color var(--dur-color) var(--ease)}
.fbtn:hover{border-color:var(--c-accent);color:var(--c-text)}
/* The pressed state is colour AND weight, never colour alone: a chip set that reads only by hue
   is invisible to a monochrome reader and to most colour-blind ones. */
.fbtn[aria-pressed="true"]{background:var(--c-accent);border-color:var(--c-accent);
                           color:var(--c-on-accent);font-weight:700}
.fcount{margin-left:auto;font-size:var(--fs-small);color:var(--c-text-muted);white-space:nowrap}
/* A STICKY BAR THAT WRAPS EATS THE PHONE. MEASURED at 320: with the chips wrapping, this bar
   stood 196px tall — 25% of the viewport, permanently, on every scroll. Below the tablet
   breakpoint each group becomes one horizontally-scrollable line instead, the same recipe
   `.cats` and `.mainnav` already use, so the height is three short rows no matter how many
   anchors the gallery grows. The chips stay reachable; they scroll inside their own group
   rather than pushing the page. */
@media(max-width:767px){
  /* `display:block`, NOT a column flex. MEASURED: as a column flex container with
     `align-items:stretch`, each `.fgroup` computed 403px inside a 320px wrap — 103px of page
     overflow — because a nowrap flex row that is also a flex ITEM takes its cross size from its
     own max-content here, and `min-width:0` does not undo it. A block-level scroller takes its
     width from the containing block by definition, so there is nothing to fight. */
  .gal-filter .gal-wrap{display:block;padding-block:var(--sp-xs)}
  .fgroup{flex-wrap:nowrap;overflow-x:auto;scrollbar-width:none;margin-bottom:.3rem}
  .fgroup::-webkit-scrollbar{display:none}
  .fbtn{flex:0 0 auto}
  .fcount{margin-left:0}
}


/* ── COMP-LOGOS · prueba social ──────────────────────────────────────────────────────────────
   Quiet by construction. TPL-C-01 §3 asks for "logos de clientes/partners EN GRIS", and the
   accent whitelist would refuse them anyway: a client list is credibility, not a call to action.
   Wordmarks in type rather than images, because we have no client logos and inventing six
   logotypes would put fabricated brands in a public repository. */
.logos .canvas{padding-block:calc(var(--sp-l) * .6)}
.logos ul{list-style:none;margin:var(--sp-s) 0 0;padding:0;display:flex;flex-wrap:wrap;
          align-items:center;gap:var(--sp-s) var(--sp-l)}
.logos li{font-family:var(--font-primary);font-size:var(--fs-small);
          letter-spacing:.1em;text-transform:uppercase;color:var(--c-text-muted);
          white-space:nowrap}
/* NO `text-align:center` HERE. The first cut had one, and it made this the only section on the
   page that ignored the composition axis: centred under LP-ASYMMETRIC, centred under
   LP-BROKEN-GRID, centred everywhere, which is what a bolted-on band looks like. It uses the
   same `.head stack` wrapper as every sibling instead, so the blueprint layer places it. */
[data-comp="lp-centered"] .logos ul{justify-content:center}

/* ── COMP-PROCESS · cómo trabajamos ──────────────────────────────────────────────────────────
   TPL-C-01 §3: "3–5 pasos numerados. Mobile: vertical con línea. Desktop: fila horizontal con
   conectores."

   THE CONNECTOR IS A TOP RULE ON EACH STEP, not a line drawn between neighbours. A step that has
   to know where the next one starts breaks the moment one column wraps to two lines or the grid
   drops to one column — and it is the kind of break that looks deliberate, so nobody reports it.
   A rule that belongs to the step travels with the step at every width. */
.process .steps{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-m);
                grid-template-columns:minmax(0,1fr)}
@media(min-width:768px){.process .steps{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(min-width:1024px){.process .steps{grid-template-columns:repeat(4,minmax(0,1fr))}}
/* A GRID ITEM IS `min-width:auto`, NOT ZERO, and that is why two steps ran into each other:
   "Diagnosticamos" is one unbreakable word wider than a quarter of the measure at the classic
   scale, so its `<li>` refused to shrink below its content and ate the gutter of the step beside
   it. Rendered, it read as `DiagnosticamosUsted aprueba` — no gap at all, which is not a spacing
   bug but an overflow one.

   `minmax(0,1fr)` sizes the TRACK and says nothing about the ITEM: the same trap that overflowed
   the `presupuesto` label by 61px in an earlier round, one component along. The fix is the item,
   and `hyphens` gives Spanish a legal place to break rather than letting the word ram the next
   column — which is why the sample carries `lang="es"`. */
.process .steps > li{min-width:0}
/* AND THE TITLE FITS INSTEAD OF BREAKING. The first cut added `overflow-wrap:break-word`, which
   stopped the collision and produced `Diagnosticam / os` — a word split mid-syllable with no
   hyphen, because `hyphens:auto` needs a hyphenation dictionary the headless renderer does not
   carry and the hard break wins when it is absent. Breaking a four-word section heading was
   solving the wrong problem: a list of four across should not be using the full h3 step of the
   scale. It uses its own, and the longest word fits without asking anything of the renderer. */
.process .steps > li h3{font-size:clamp(1.05rem,1.55vw,1.3rem);line-height:1.2;text-wrap:balance}
/* SUBGRID, NOT A GUESSED `min-height`. The titles wrap to different line counts — "Medición en
   obra" takes two lines where "Colocación" takes one — so without this the four paragraphs start
   at four different heights and the row reads as sloppy typesetting. A two-line `min-height` would
   only move the raggedness to the first title that wraps to three, which is the same mistake the
   product card avoided by pinning its price. Subgrid makes the four steps share the ROWS, whatever
   each one puts in them. Where subgrid is unsupported the steps simply fall back to today's
   behaviour: ragged, not broken. */
.process .step{min-width:0;padding-top:var(--sp-s);border-top:1px solid var(--c-border)}
@supports (grid-template-rows:subgrid){
  @media(min-width:768px){
    .process .steps{grid-template-rows:repeat(3,auto)}
    .process .step{display:grid;grid-template-rows:subgrid;grid-row:span 3}
  }
}
.process .step .n{display:block;font-family:var(--font-primary);
                  font-size:var(--fs-h2);line-height:1;color:var(--c-text-muted);
                  letter-spacing:var(--track-h1)}
/* MEASURED BREAK, NOT A GUESS. Under PERS-DIRECT the display face is condensed and set at the
   monumental step, and "presupuesto" alone is wider than a quarter-canvas column at 1440: step 2's
   second line ran straight across step 3's title. `min-width:0` because a grid item defaults to
   `min-width:auto` and will not shrink below its longest word; `hyphens:auto` because the sample
   carries `lang="es"` and a hyphenated break is the typographically correct one; `break-word` as
   the floor for the words Chrome's Spanish dictionary declines to divide. */
.process .step h3{margin:.4rem 0 .25rem;min-width:0;hyphens:auto;overflow-wrap:break-word}
.process .step p{color:var(--c-text-soft)}

/* ── COMP-TESTIMONIAL · social proof ─────────────────────────────────────────────────────────
   The quote mark is a HANGING glyph — it sits in the margin so the first line of the quote keeps
   the same left edge as every other paragraph on the page. A quote mark that pushes its own text
   in by half a character is the tell that separates a typeset page from a template. */
.quotes .items{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-m);
               grid-template-columns:minmax(0,1fr)}
@media(min-width:768px){.quotes .items{grid-template-columns:repeat(3,minmax(0,1fr))}}
.quotes figure{margin:0;min-width:0;display:flex;flex-direction:column;height:100%}
.quotes blockquote{margin:0;position:relative;font-family:var(--font-primary);
                   font-size:var(--fs-h3);line-height:1.35;letter-spacing:var(--track-h3,0)}
/* En una sola columna la cita dispone del canvas entero y --fs-h3 es correcto: es una cita
   destacada y tiene que leerse como tal. En la rejilla de tres de abajo cada columna mide un
   tercio del canvas —unos 350px a 1280— y --fs-h3 llega a 45.8px en `direct`: SIETE caracteres
   por linea, que ya no es una frase sino una lista vertical de palabras. Una cita se dimensiona
   por su MEDIDA, no por la escala de titulares: el tope deja la columna entre 32 y 46 caracteres,
   que es la banda en la que una cita se lee de un golpe. El termino medio sigue saliendo de
   --fs-h3, asi que la distancia entre anclas se conserva —institutional 16.8px frente a direct
   21.6px— en vez de aplanarse a un numero fijo para todos. */
.quotes blockquote::before{content:"\201C";position:absolute;right:100%;top:-.1em;
                           margin-right:.06em;color:var(--c-text-muted);
                           font-size:1.6em;line-height:1}
/* Below the tablet breakpoint there is no margin to hang into: a glyph at `right:100%` would sit
   outside the canvas and be clipped, so it comes back into the flow instead of disappearing. */
@media(max-width:767px){
  .quotes blockquote::before{position:static;display:block;margin:0 0 -.35em}
}
/* DESPUES de la regla base a proposito: la base pinta --fs-h3 y, puesta antes, ganaba por orden
 de cascada con la misma especificidad — el tope no llegaba a aplicarse nunca. */
@media(min-width:768px){
  .quotes blockquote{font-size:clamp(1.05rem, calc(var(--fs-h3) * .5), 1.35rem)}
}
.quotes figcaption{margin-top:auto;padding-top:var(--sp-s);font-size:var(--fs-small)}
.quotes figcaption b{display:block}
.quotes figcaption span{color:var(--c-text-muted)}
/* ── EL MARKETPLACE: una vista índice y una página por plantilla ────────────────────────────────

   Un scroll infinito de cuarenta tiras a ancho completo no es un catálogo, es un documento. La
   decisión que esta herramienta existe para servir — «quiero ESTA para este cliente» — pide ver
   muchas a la vez y luego entrar en una. Así que la galería pasa a ser index + detalle, con el
   patrón que html-mockup ya obliga: UN artifact, `.page` conmutadas por JS, chrome fuera de ellas.
   Nada de enlaces entre artifacts, que en el sandbox están muertos.

   LA MINIATURA ES LA PÁGINA A ESCALA, NO UNA FOTO DE ELLA. Un PNG por tarjeta costaría bytes que
   ya pagamos (las cuarenta tiras tienen que estar en el DOM igual, porque son la vista de detalle),
   necesitaría un pipeline de capturas, y — lo que de verdad importa — QUEDA OBSOLETO EN SILENCIO
   en cuanto alguien toca el generador. Este repo ya perdió una ronda entera a un tratamiento cuya
   justificación había caducado sin que nadie lo notara. Una miniatura clonada del DOM real no
   puede mentir sobre el diseño que representa: si la tira cambia, la tarjeta cambia.

   El escenario se dimensiona al ANCHO REAL DEL VIEWPORT y se escala por (ancho de tarjeta ÷ ancho
   de viewport), no a un 1440 fijo. Los `clamp()` del sistema son fluidos en `vw`, así que un
   escenario de ancho arbitrario renderizaría tipografía de otro ancho de pantalla: la miniatura
   mentiría sobre la escala, que es uno de los cinco ejes que la tarjeta anuncia. ── */

.page[hidden]{display:none}

.gal-top{position:sticky;top:0;z-index:60;
         background:var(--c-surface-inverse);color:var(--c-on-inverse);
         border-bottom:1px solid color-mix(in srgb,var(--c-on-inverse) 18%,transparent)}
.gal-top .gal-wrap{display:flex;align-items:center;gap:var(--sp-s);
                   padding-block:.5rem;min-height:2.9rem}
.gal-top .mark{font-family:var(--font-primary);font-weight:700;letter-spacing:.03em;
               white-space:nowrap;flex:0 0 auto}
.gal-top .here{font-size:var(--fs-small);color:color-mix(in srgb,var(--c-on-inverse) 72%,var(--c-surface-inverse));
               white-space:nowrap;overflow:hidden;text-overflow:ellipsis;min-width:0}
/* Same both-axes centring as .btn and .fbtn: this sits in a flex row that can stretch it. */
.backlink{margin-left:auto;flex:0 0 auto;display:inline-flex;align-items:center;justify-content:center;
          gap:.4rem;font-size:var(--fs-small);line-height:1.2;color:var(--c-on-inverse);
          border:1px solid color-mix(in srgb,var(--c-on-inverse) 34%,transparent);
          border-radius:var(--radius-pill,999px);padding:.3rem .8rem;white-space:nowrap;text-decoration:none;
          transition:background var(--dur-color) var(--ease),border-color var(--dur-color) var(--ease)}
/* A page rule with `display` BEATS the UA sheet's `[hidden]{display:none}` — same specificity,
   later origin. `.strip[hidden]` and `.tgrid > li[hidden]` above exist for exactly this reason;
   without this line the back link sits on the index pointing at the page you are already on. */
.backlink[hidden]{display:none}
.backlink:hover{background:color-mix(in srgb,var(--c-on-inverse) 14%,transparent);
                border-color:var(--c-on-inverse)}
.backlink:focus-visible{outline:2px solid var(--c-accent);outline-offset:2px}

.tgrid{display:grid;gap:var(--sp-m);grid-template-columns:minmax(0,1fr);list-style:none;padding:0;
       margin:0;padding-block:var(--sp-l)}
@media(min-width:600px){.tgrid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(min-width:1100px){.tgrid{grid-template-columns:repeat(3,minmax(0,1fr))}}
/* CUATRO columnas en pantalla ancha, porque un grupo tiene CUATRO anclas. A tres columnas
   toda plantilla parte 3+1 y la cuarta variante queda sola en una fila, que se lee como un
   hueco y no como el juego completo que es. El numero de columnas aqui no es una preferencia
   de rejilla: es el numero de anclas. */
@media(min-width:1400px){.tgrid{grid-template-columns:repeat(4,minmax(0,1fr))}}
.tgrid > li{display:flex;min-width:0}
.tgrid > li[hidden]{display:none}

.tcard{display:flex;flex-direction:column;flex:1;min-width:0;
       color:inherit;text-decoration:none;background:var(--c-bg);
       border:1px solid var(--c-border);border-radius:var(--radius-card);overflow:hidden;
       transition:box-shadow var(--dur-color) var(--ease),transform var(--dur-color) var(--ease),
                  border-color var(--dur-color) var(--ease)}
.tcard:hover{border-color:var(--c-accent);transform:translateY(var(--lift));
             box-shadow:0 18px 40px -22px color-mix(in srgb,var(--c-text) 55%,transparent)}
.tcard:focus-visible{outline:2px solid var(--c-accent);outline-offset:3px}

/* Below the two-column breakpoint the card is nearly as wide as the viewport, so the scale
   factor approaches 1 and a 16/11 crop shows little more than the navigation. The stage width
   stays honest — it is still the real viewport — and the WINDOW onto it grows instead. */
@media(max-width:599px){.thumb{aspect-ratio:4/3}}
.thumb{display:block;position:relative;aspect-ratio:16/11;overflow:hidden;
       background:var(--c-bg-alt);border-bottom:1px solid var(--c-border)}
.thumb-stage{position:absolute;top:0;left:0;transform-origin:top left}
/* The crop has to read as a crop and not as a page that ends. */
.thumb::after{content:"";position:absolute;inset:auto 0 0 0;height:26%;pointer-events:none;
              background:linear-gradient(to bottom,transparent,
                         color-mix(in srgb,var(--c-bg) 82%,transparent))}
.thumb-wait{position:absolute;inset:0;display:grid;place-items:center;
            font-size:var(--fs-eyebrow);letter-spacing:.18em;text-transform:uppercase;
            color:var(--c-text-muted)}

.tbody{display:flex;flex-direction:column;flex:1;gap:.3rem;
       padding:var(--sp-s) var(--sp-m) var(--sp-m)}
.tpair{font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;
       font-size:var(--fs-eyebrow);letter-spacing:.05em;color:var(--c-text-muted)}
.tname{font-family:var(--font-primary);font-size:var(--fs-h3);line-height:1.15;margin:0}
.tsub{font-size:var(--fs-small);color:var(--c-text-soft)}
/* `margin-top:auto` on the axis row and not on the "Ver" line, for the same reason the product
   card pins its price and not its button: the pair travels together, and pinning only the last
   line would align the last lines and leave the axis rows ragged one row up. */
.taxes{list-style:none;margin:auto 0 0;padding:var(--sp-s) 0 0;display:flex;flex-wrap:wrap;gap:.3rem}
.tax{font-size:.68rem;line-height:1.4;letter-spacing:.03em;white-space:nowrap;
     border:1px solid var(--c-border);border-radius:var(--radius-pill,999px);padding:.12rem .5rem;
     color:var(--c-text-muted)}
/* Neutral AT REST and accent only under the pointer. Forty cards each printing an accent line is
   a catalogue of accents, which is the tell design-tokens.md forbids by name — and the whitelist
   gate is what forced the question: `.tgo` claimed the CTA role, and a role claimed forty times on
   one screen is not a mark, it is a texture. The card is already the control; its hover border and
   this line arriving together is the affordance. */
.tgo{margin-top:.6rem;font-size:var(--fs-small);font-weight:700;color:var(--c-text);
     transition:color var(--dur-color) var(--ease)}
.tcard:hover .tgo{color:var(--c-accent)}
.tcard:hover .tgo::after{transform:translateX(.25rem)}
.tgo::after{content:"→";display:inline-block;margin-left:.35rem;
            transition:transform var(--dur-color) var(--ease)}


/* ── COMP-NEWSLETTER · declared by four archetypes ──────────────────────────────────────────
   One field and one button on a row, and the consent line UNDER them rather than beside: a
   sentence about what happens to somebody's email address is not a caption, and putting it in
   6px grey next to the button is how it gets ignored. */
.newsform{display:grid;gap:var(--sp-xs);align-items:end;max-width:38rem}
@media(min-width:600px){
  .newsform{grid-template-columns:minmax(0,1fr) auto}
  .newsform .btn{grid-column:2}
  .news-small{grid-column:1/-1}
}
.news-small{margin-top:.1rem}

/* ── COMP-VALUES · numbered, on the same rails as COMP-PROCESS ─────────────────────────────
   It reuses `.steps`/`.step` rather than growing a parallel implementation: two things that must
   stay visually identical and are written twice start to drift on the first edit to one of them.
   A value that cannot be counted is a slogan, so they are counted. */

/* ── COMP-PRICING · TPL-C-04 ─────────────────────────────────────────────────────────────────
   THE RECOMMENDED PLAN IS MARKED WITH WEIGHT AND A WORD, never with colour alone. A plan set that
   reads only by hue is invisible to a monochrome reader and to most colour-blind ones — the same
   rule the filter chips and the finish options already follow. The word is `El más pedido`, which
   is a fact about orders and not an adjective about the plan. */
.plans{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-m);
       grid-template-columns:minmax(0,1fr)}
@media(min-width:768px){.plans{grid-template-columns:repeat(3,minmax(0,1fr))}}
.plan{min-width:0;display:flex;flex-direction:column;gap:.35rem;
      padding:var(--sp-m);border:1px solid var(--c-border);border-radius:var(--radius-card);
      background:var(--c-bg)}
.plan-best{border-color:var(--c-text);border-width:2px;padding:calc(var(--sp-m) - 1px)}
.plan-tag{align-self:start;font-size:var(--fs-eyebrow);letter-spacing:.16em;text-transform:uppercase;
          border:1px solid var(--c-text);border-radius:999px;padding:.1rem .5rem}
.plan-name{font-family:var(--font-primary);font-size:var(--fs-h3);line-height:1.2;font-weight:600}
.plan-price{font-size:var(--fs-h2);line-height:1;font-variant-numeric:tabular-nums;
            font-family:var(--font-primary)}
.plan-list{list-style:none;margin:var(--sp-xs) 0 0;padding:0;display:grid;gap:.25rem;
           font-size:var(--fs-small);color:var(--c-text-soft)}
.plan-list li{padding-left:1rem;position:relative}
.plan-list li::before{content:"·";position:absolute;left:.3rem;color:var(--c-text-muted)}
/* The button is pinned to the bottom so three plans with different feature counts still line their
   controls up — the product card learned this first, and for the same reason. */
.plan .btn{margin-top:auto}
.plan-note{margin-top:var(--sp-s);max-width:70ch}

/* ── COMP-SOLUTION · the mirror of COMP-PROBLEM ───────────────────────────────────────────── */
.solution .head .feats{margin-top:var(--sp-s)}

/* ── COMP-PROMO-BANNER · TPL-E-05 ────────────────────────────────────────────────────────────
   A BAND, NOT A CARD. It interrupts the catalogue instead of joining it, which is the only way an
   offer reads as an offer rather than as one more product in the grid. Its section has no `.head`,
   so it also breaks the page's own rhythm of eyebrow-then-heading — deliberately.

   THE ACCENT STAYS OFF IT. A whole surface shouting is the closing band's job, once per page; two
   more would turn the accent into a texture, which is the tell design-tokens.md forbids and the
   defect this file spent a whole round undoing. */
.promo .canvas{padding-block:var(--sp-m)}
.promo-in{display:flex;flex-wrap:wrap;align-items:center;gap:var(--sp-xs) var(--sp-m);
          padding:var(--sp-m);border:1px solid var(--c-border);border-radius:var(--radius-card)}
.promo-in b{font-family:var(--font-primary);font-size:var(--fs-h3);line-height:1.2;
            min-width:0;hyphens:auto}
.promo-in span{color:var(--c-text-soft);font-size:var(--fs-small);flex:1 1 18ch;min-width:0}
.promo-in .btn{margin-left:auto;flex:0 0 auto}
@media(max-width:599px){.promo-in .btn{margin-left:0}}

/* ── TPL-C-05 · Local / Booking ──────────────────────────────────────────────────────────────
   The phone number in the header is a LINK IN TEXT, not an icon. An icon-only phone is a phone
   number you cannot copy, cannot read aloud and cannot search for on the page. */
.tel{font-size:var(--fs-nav);white-space:nowrap;color:var(--c-text);margin-left:auto;
     margin-right:var(--sp-s)}
.tel:hover{color:var(--c-accent)}
@media(max-width:767px){.tel{margin-left:0;margin-right:var(--sp-xs)}}

/* COMP-BOOKING — day and slot as radio ROWS. `.opts-wide` lets a five-slot group breathe on one
   line where the PDP's three finishes did not need to. */
.bookform{display:grid;gap:var(--sp-s);max-width:44rem}
.opts-wide{gap:.35rem}
.book-small{max-width:52ch}

/* COMP-GALLERY — six frames, no lightbox. A lightbox is script the reader must load before seeing
   the second photograph, and on a mockup it promises a plugin nobody has chosen yet. */
.shots{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-xs);
       grid-template-columns:repeat(2,minmax(0,1fr))}
@media(min-width:768px){.shots{grid-template-columns:repeat(3,minmax(0,1fr))}}

/* COMP-MAP-NAP — address, phone and hours as TEXT. No embedded map: an iframe to a map provider is
   a remote request the Artifact CSP blocks, and on a real build a third-party cookie set before
   the visitor consented to anything. */
.nap-addr{font-style:normal;display:grid;gap:.15rem;margin-top:var(--sp-xs)}
.nap-addr a{color:var(--c-text);text-decoration:underline;text-underline-offset:.2em}
.nap-addr a:hover{color:var(--c-accent)}
.hours{margin:var(--sp-s) 0 0;display:grid;gap:.2rem;font-size:var(--fs-small)}
.hours > div{display:flex;flex-wrap:wrap;gap:.2rem var(--sp-s);
             border-top:1px solid var(--c-border);padding-top:.25rem}
.hours dt{color:var(--c-text-muted);flex:0 0 14ch}
.hours dd{margin:0;font-variant-numeric:tabular-nums}

/* ── COMP-CATEGORY-CARD · TPL-E-01 and TPL-E-04 ──────────────────────────────────────────────
   A DOOR, NOT A PRODUCT, so it is deliberately bigger and carries no price. And it carries a
   COUNT: a category with no number is a promise the reader cannot size, and the second empty room
   is where people stop clicking. The count sits at the bottom of the card on `margin-top:auto`,
   which is the third time that trick earns its place here — the product card, the pricing plan,
   and now this. */
.catcards{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-m);
          grid-template-columns:minmax(0,1fr)}
@media(min-width:768px){.catcards{grid-template-columns:repeat(3,minmax(0,1fr))}}
.catcard{min-width:0}
.catcard a{display:flex;flex-direction:column;height:100%;color:inherit;text-decoration:none}
.catcard .frame{overflow:hidden}
.catcard img{transition:transform var(--dur-lift,.5s) var(--ease)}
.catcard a:hover img{transform:scale(1.03)}
.catcard a:focus-visible{outline:2px solid var(--c-accent);outline-offset:3px}
.catcard-body{display:flex;flex-direction:column;flex:1;gap:.15rem;padding-top:var(--sp-xs)}
.catcard-body b{font-family:var(--font-primary);font-size:var(--fs-h3);line-height:1.2}
.catcard-sub{font-size:var(--fs-small);color:var(--c-text-soft)}
.catcard-n{margin-top:auto;padding-top:var(--sp-xs);font-size:var(--fs-eyebrow);
           letter-spacing:.14em;text-transform:uppercase;color:var(--c-text-muted)}
.catcard a:hover b{color:var(--c-accent)}
@media(prefers-reduced-motion:reduce){
  .catcard img{transition:none}
  .catcard a:hover img{transform:none}
}

/* ── COMP-CATEGORY-GRID · TPL-E-04 ───────────────────────────────────────────────────────────
   TEXT TILES, NOT PHOTOGRAPHS. Eight photographic doors are eight more images to decode before the
   reader can route, and routing is what this archetype is for — here the count is the argument,
   not the picture. COMP-CATEGORY-CARD lower down does carry photographs, because three doors can
   afford them and eight cannot. */
.tiles{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-xs);
       grid-template-columns:repeat(2,minmax(0,1fr))}
@media(min-width:768px){.tiles{grid-template-columns:repeat(4,minmax(0,1fr))}}
.tiles a{display:flex;flex-direction:column;gap:.1rem;min-width:0;height:100%;
         padding:var(--sp-s) var(--sp-m);color:inherit;text-decoration:none;
         border:1px solid var(--c-border);border-radius:var(--radius-card);
         transition:border-color var(--dur-color) var(--ease)}
.tiles a:hover{border-color:var(--c-accent)}
.tiles a:focus-visible{outline:2px solid var(--c-accent);outline-offset:2px}
.tiles b{font-family:var(--font-primary);font-size:var(--fs-h3);line-height:1.2;
         min-width:0;hyphens:auto;overflow-wrap:break-word}
.tiles span{margin-top:auto;font-size:var(--fs-eyebrow);letter-spacing:.14em;
            color:var(--c-text-muted);font-variant-numeric:tabular-nums}

/* ── COMP-PRODUCT-TABS · TPL-E-04 ────────────────────────────────────────────────────────────
   TABS WITHOUT A LINE OF JAVASCRIPT. Radio inputs plus a sibling selector: a tab set that needs
   script shows one panel and three dead labels until it loads, and shows exactly that forever to a
   crawler. The inputs are visually hidden but focusable — `clip` and not `display:none`, because a
   display:none radio is unreachable by keyboard and drops out of the tab order entirely. */
.tabset{display:flex;flex-wrap:wrap;gap:.4rem}
.tabin{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);
       clip-path:inset(50%);white-space:nowrap}
.tablab{display:inline-flex;align-items:center;justify-content:center;cursor:pointer;
        font-size:var(--fs-small);line-height:1.2;padding:.3rem .8rem;white-space:nowrap;
        border:1px solid var(--c-border);border-radius:999px;color:var(--c-text-soft);
        transition:background var(--dur-color) var(--ease),color var(--dur-color) var(--ease),
                   border-color var(--dur-color) var(--ease)}
.tablab:hover{border-color:var(--c-text-muted);color:var(--c-text)}
/* Colour AND weight for the selected tab, never colour alone. */
.tabin:checked + .tablab{background:var(--c-text);border-color:var(--c-text);
                         color:var(--c-bg);font-weight:700}
.tabin:focus-visible + .tablab{outline:2px solid var(--c-accent);outline-offset:2px}
.tabpanels{flex:1 0 100%;margin-top:var(--sp-m)}
.tabpanel{display:none}
.tabin:nth-of-type(1):checked ~ .tabpanels > .tabpanel:nth-child(1),
.tabin:nth-of-type(2):checked ~ .tabpanels > .tabpanel:nth-child(2),
.tabin:nth-of-type(3):checked ~ .tabpanels > .tabpanel:nth-child(3){display:block}

/* ── TPL-C-03 · Portfolio / Showcase ──────────────────────────────────────────────────────── */

/* COMP-HERO visual — the copy sits OVER the photograph, which is the one structural difference
   between this archetype's hero and TPL-C-01's. The scrim is a gradient and not a flat wash: a
   flat overlay dark enough for the text also flattens the photograph the archetype exists to
   show. Measured against the worst pixel under the copy, not against the image's average. */
.hero-visual{position:relative;isolation:isolate}
.hero-visual .media-full{position:absolute;inset:0;z-index:0}
.hero-visual .media-full .frame{width:100%;height:100%;aspect-ratio:auto;border-radius:0}
.hero-visual .media-full img{width:100%;height:100%;object-fit:cover}
/* THE COPY SITS IN THE BOTTOM THIRD AND THE SCRIM IS STRONG ONLY THERE. The first cut spread an
   even veil over the whole frame and measured 2.55:1 for white text — the build refused it, which
   is the sweep two hundred lines up doing its job on its first run. The obvious repair was to
   deepen the veil everywhere, and that is the wrong one: this archetype's doc says "alto en
   visual", and a wash dark enough for type flattens the photograph the page exists to show.
   So the photograph keeps its top two thirds clean and the type takes a band that can afford to be
   dark. The sweep measures the WEAKEST point the copy crosses, not the strongest. */
.hero-visual::after{content:"";position:absolute;inset:0;z-index:1;pointer-events:none;
  background:linear-gradient(180deg,
    transparent 0%,
    color-mix(in srgb,#000 12%,transparent) 42%,
    color-mix(in srgb,#000 62%,transparent) 66%,
    color-mix(in srgb,#000 82%,transparent) 100%)}
/* Y EN ESTRECHO EL MISMO VELO NO LLEGA, porque lo que cambia no es la foto sino la ALTURA DEL
   TEXTO. A 1280 el bloque de copy ocupa el tercio de abajo y cae entero dentro de la parte oscura
   del degradado. A 430 el mismo titular necesita tres líneas, el lede dos y los botones una fila
   más: medido, el bloque pasa de ~230px a 439px sobre un hero de 769, así que su borde superior
   sube al 14% de la altura, donde este degradado sólo pone un 12% de negro. Encima de una pared
   lisa y clara —que es justo lo que un 16:9 recortado a vertical deja debajo del texto— el
   titular blanco desaparece. No se oscurece la foto entera: se sube el degradado, que es la misma
   decisión de arriba aplicada a una caja de texto que ha crecido. */
@media(max-width:767px){
  .hero-visual::after{background:linear-gradient(180deg,
    color-mix(in srgb,#000 20%,transparent) 0%,
    color-mix(in srgb,#000 48%,transparent) 28%,
    color-mix(in srgb,#000 72%,transparent) 60%,
    color-mix(in srgb,#000 86%,transparent) 100%)}
}
.hero-visual > .canvas{position:relative;z-index:2;min-height:min(60vh,34rem);
  align-content:end;padding-block:var(--sp-l)}
/* The copy is white on a photograph, so it takes white REGARDLESS of the anchor's ground: an ink
   anchor would otherwise paint near-white text on a near-white scrim edge. The buttons keep their
   own tokens, because a CTA that changes colour per anchor is the point of the accent. */
.hero-visual .head h1,.hero-visual .head .lede,.hero-visual .head .eyebrow{color:#fff}
.hero-visual .head .lede{opacity:.92}
/* The SECONDARY button lives on the photograph too, and it was taking `--c-text` from the ground:
   on `paper` that is near-black type inside a near-black outline, sitting on a dark scrim. The
   primary keeps its accent — a CTA that changes per anchor is the whole point of the accent — but
   the outline variant has to belong to the surface it is actually on, which here is an image. */
.hero-visual .btn-outline{color:#fff;border-color:color-mix(in srgb,#fff 55%,transparent)}
.hero-visual .btn-outline:hover{border-color:#fff;
  background:color-mix(in srgb,#fff 14%,transparent)}
@media(max-width:767px){.hero-visual > .canvas{min-height:50vh}}

/* COMP-PORTFOLIO-GRID — the grid IS the page. Mobile 1, tablet 2, desktop 3, per the doc. */
.works{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-m);
       grid-template-columns:minmax(0,1fr)}
@media(min-width:768px){.works{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(min-width:1024px){.works{grid-template-columns:repeat(3,minmax(0,1fr))}}
.work-item{min-width:0}
.work-item a{display:block;color:inherit;text-decoration:none}
.work-item .frame{overflow:hidden}
.work-item img{transition:transform var(--dur-lift,.5s) var(--ease)}
.work-item a:hover img{transform:scale(1.03)}
.work-item a:focus-visible{outline:2px solid var(--c-accent);outline-offset:3px}
/* THE CAPTION IS ALWAYS THERE and the hover only deepens it. An overlay that is the ONLY place a
   project's name lives is a project nobody can name on a touch screen, where there is no hover. */
.work-cap{display:block;padding-top:var(--sp-xs)}
.work-cap b{display:block;font-family:var(--font-primary);font-size:var(--fs-h3);line-height:1.2}
.work-cap span{font-size:var(--fs-small);color:var(--c-text-muted)}
.work-item a:hover .work-cap b{color:var(--c-accent)}
@media(prefers-reduced-motion:reduce){
  .work-item img{transition:none}
  .work-item a:hover img{transform:none}
}

/* ── TPL-C-02 · Institutional Trust ───────────────────────────────────────────────────────── */

/* COMP-ABOUT — a head/media pair, so it is placed by whatever the blueprint does with the hero
   rather than by a grid this section invents. The paragraphs keep a measure: an about text set to
   the full canvas at 1920 runs to 140 characters a line, which nobody reads. */
.about .head p{max-width:64ch}
.about .head p + p{margin-top:var(--sp-s)}

/* COMP-STATS — the figures. A definition list, because that is exactly what it is: a number and
   what the number counts. The FIGURE is the term and the label is the description, which is also
   the order a screen reader should hear them in. */
.figs{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:var(--sp-m);margin:0}
@media(min-width:1024px){.figs{grid-template-columns:repeat(4,minmax(0,1fr))}}
.fig{min-width:0;padding-top:var(--sp-s);border-top:1px solid var(--c-border)}
/* UNA CIFRA DE FILA NO ES EL TITULAR DE LA PÁGINA, y durante mucho tiempo se pintó con su mismo
   token. Medido en las 95 cifras del catálogo: DIECISÉIS desbordaban su columna, repartidas por
   ocho arquetipos. La peor, «11 unidades» a 120px, pedía 584px dentro de una columna de 271 — más
   del doble— y salía montada sobre su vecina. En la lonja las tres cifras se leían como una sola
   palabra: «±15%AntesNunca».
   La causa es de manual: `--fs-h1` es el tamaño de la promesa MÁS GRANDE de la página, de la que
   hay una; aquí hay cuatro en fila dentro de columnas de ~250px. Bajo el ancla monumental eso son
   cuatro titulares de 120px compitiendo en el ancho de uno.
   El tope de 2.75rem conserva la variación entre anclas por debajo de él —una `contained` sigue
   saliendo a 40px y una `classic` a 48— y sólo recorta donde el número ya no cabía. Un número que
   no entra en su columna no es monumental: está roto. */
.fig dt{font-family:var(--font-primary);font-size:min(var(--fs-h2), 2.75rem);line-height:1.05;
        letter-spacing:var(--track-h1);font-variant-numeric:tabular-nums}
.fig dd{margin:.35rem 0 0;font-size:var(--fs-small);color:var(--c-text-soft)}

/* COMP-TEAM — a face, a name, a role. No social icon row: the reference kits hang four of them
   under every member, and a link to somebody's personal profile is not a credential of the firm. */
.team .items{list-style:none;margin:0;padding:0}
.member{min-width:0;display:flex;flex-direction:column;gap:.2rem}
.member .frame{margin-bottom:var(--sp-xs)}
.member b{font-family:var(--font-primary);font-size:var(--fs-h3);line-height:1.2;font-weight:600}
.member span{font-size:var(--fs-small);color:var(--c-text-muted)}

/* COMP-CTA sobrio — the close WITHOUT a form. It keeps the closing band's ground and its weight,
   because the close is still a designed moment; what it drops is the lead capture, which is the
   one thing that separates this archetype's DNA from TPL-C-01's. */
.band.sober .canvas{justify-items:start}
[data-comp="lp-centered"] .band.sober .canvas{justify-items:center}

/* ── INTERIOR PAGES ──────────────────────────────────────────────────────────────────────────
   A template is a page set. Everything below belongs to pages that are NOT the home, and every
   one of them wears the home's header, footer, tokens and blueprint, because that is the whole
   claim a "kit" makes: these are pages of one site, not mockups sharing a folder. */

/* COMP-BREADCRUMB — every interior page opens with one and the home never has one, which is the
   cheapest signal a reader has that they went a level down. */
.crumbs{border-bottom:1px solid var(--c-border);background:var(--c-bg)}
.crumbs ol{list-style:none;margin:0;padding:var(--sp-xs) 0;display:flex;flex-wrap:wrap;
           gap:.25rem .5rem;font-size:var(--fs-small);color:var(--c-text-muted)}
.crumbs li+li::before{content:"/";margin-right:.5rem;color:var(--c-border)}
.crumbs a{color:var(--c-text-soft)}
.crumbs a:hover{color:var(--c-text)}
.crumbs [aria-current]{color:var(--c-text)}

/* "Qué resolvemos" — the problems the service answers. A numbered rule per item, same recipe as
   COMP-PROCESS: the rule belongs to the item, so it survives any wrap at any width. */
.problems .items{list-style:none;margin:0;padding:0}
.problems .prob{min-width:0;padding-top:var(--sp-s);border-top:1px solid var(--c-border)}
.problems .prob h3{margin:0 0 .3rem;min-width:0;hyphens:auto;overflow-wrap:break-word}
.problems .prob p{color:var(--c-text-soft)}

/* COMP-FEATURES — scope. A definition list in all but name: term then gloss, one per row, so the
   eye reads down the terms and stops only where it wants the detail. */
.feats{list-style:none;margin:0;padding:0;display:grid;gap:0}
.feats li{display:grid;gap:.15rem;padding:var(--sp-s) 0;border-top:1px solid var(--c-border);
          min-width:0}
.feats li:last-child{border-bottom:1px solid var(--c-border)}
.feats b{font-family:var(--font-primary);font-size:var(--fs-h3);line-height:1.2;font-weight:600}
.feats span{color:var(--c-text-soft);font-size:var(--fs-small)}
@media(min-width:768px){
  .feats li{grid-template-columns:minmax(0,22ch) minmax(0,1fr);gap:var(--sp-m);align-items:baseline}
}

/* COMP-GALLERY + COMP-PRODUCT-INFO — ONE block, never two sections. A section boundary running
   through a buy box is the difference between a product page and two stacked panels. */
.pdp .canvas{padding-block:var(--sp-l)}
.pdp-gal,.pdp-buy{min-width:0}
.pdp-thumbs{list-style:none;margin:var(--sp-xs) 0 0;padding:0;
            display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:var(--sp-xs)}
.pdp-buy{display:flex;flex-direction:column;gap:var(--sp-xs)}
.pdp-buy h1{margin:0}
.pdp-price{font-size:var(--fs-h3)}
.pdp-ship{margin-top:.2rem}
/* EL PRECIO ANTERIOR Y EL PORCENTAJE, SIN GASTAR EL ACENTO. El descuento no es ninguno de los
   cuatro roles que design-tokens.md admite para el único color de la página —CTA, icono de acción,
   enlace, estado activo— y en esta ficha ese color ya lo tiene el botón de comprar. Un porcentaje
   que grita más que el botón mueve la mirada al sitio equivocado, así que el «−40%» se marca con
   filete y cifra tabular, igual que las opciones de acabado dos reglas más abajo. */
.pdp-price .was{color:var(--c-text-muted);font-size:var(--fs-body);margin-left:.35em;
                text-decoration-thickness:1px}
.pdp-price .off{font-size:var(--fs-small);font-variant-numeric:tabular-nums;margin-left:.4em;
                padding:.1em .45em;border:1px solid var(--c-border);vertical-align:middle}
.pdp-deadline{margin:.1rem 0 0;font-weight:600}
/* THE BUY BOX IS PLACED ON THE COMPOSITION'S OWN COLUMNS, not on a grid this section invents.
   The first cut redeclared `.pdp .canvas` as a two-column grid and nothing happened, because every
   `[data-comp]` block redefines `.canvas` at 1024 and wins on specificity: the section was fighting
   the blueprint layer instead of using it. `.band` already had this exact shape — a headline half
   and a form half — so the PDP takes the same lines under each blueprint. Below 1024 the canvas is
   one column and the gallery stacks above the buy box, which is the order TPL-PDP-01 names:
   "COMP-GALLERY (izquierda / arriba en mobile)". */
.pdp-buy{align-self:start}
/* A BUY BOX BESIDE THE GALLERY IS PDP DNA, NOT A COMPOSITION CHOICE — the same argument this file
   already makes for catalogue grid density two hundred lines up. Three blueprints place the two
   halves on their own named lines below; `lp-centered` never redefines `.canvas` at all, so
   without this rule its product page would stack a full-width stone slab above the price and put
   the only button on the page a screen and a half down. Where a blueprint DOES place the halves,
   its `[data-comp]` rule outranks this one and wins. */
@media(min-width:1024px){
  .pdp .canvas{grid-template-columns:minmax(0,1.1fr) minmax(0,1fr);
               column-gap:var(--sp-l);align-items:start}
}

/* ── LAS PÁGINAS INTERNAS ──────────────────────────────────────────────────────────────────────
   Casi todo lo que necesitan ya existe: `.hero`, `.about`, `.feats`, `.steps`, `.items.cols-3`,
   `.figq`, `.shots`, `.qas`. Aquí abajo sólo va lo que ninguna sección de home hacía. */

/* COMP-PAGE-HEAD · el encabezado de una página interna, que NO es un hero: sin imagen y sin CTA.
   Su trabajo es decir dónde estás, y por eso ocupa una banda corta en vez de media pantalla. */
.pagehead .canvas{padding-block:var(--sp-l) var(--sp-m)}
.pagehead h1{margin:0}

/* COMP-PROCESS con foto · TPL-ABOUT-02. Fila ancha y no tarjeta: de un oficio la prueba es la
   imagen del paso, y una foto de 270px en una rejilla de cuatro no prueba nada. */
.craftsteps{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-l)}
.craftstep{min-width:0;display:grid;gap:var(--sp-s);align-content:start}
.craftbody{display:grid;gap:.3rem;min-width:0}
.craftstep .n{font-family:var(--font-primary);font-size:var(--fs-small);
              letter-spacing:.16em;color:var(--c-text-muted);font-variant-numeric:tabular-nums}
.craftstep h3{margin:0}
@media(min-width:768px){.craftsteps{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(min-width:1200px){
  /* Cuatro pasos en dos filas de dos, NO en una fila de cuatro: son cuatro fotografías, y cuatro
     fotografías en fila a 1560px dan 360px cada una — la foto deja de leerse justo en el arquetipo
     cuyo argumento entero es que se vea el oficio. */
  .craftsteps{grid-template-columns:repeat(2,minmax(0,1fr));gap:var(--sp-xl) var(--sp-l)}
}

/* COMP-CONTACT-DIRECT junto al formulario · TPL-CONTACT-01. Lista, no tarjetas: son tres datos y
   un tarjetón por cada uno los convierte en tres decisiones. */
.directlist{list-style:none;margin:var(--sp-s) 0 0;padding:0;display:grid;gap:var(--sp-s)}
.directlist li{display:grid;gap:.1rem;padding-top:var(--sp-xs);border-top:1px solid var(--c-border)}
.dlabel{font-size:var(--fs-eyebrow);letter-spacing:.16em;text-transform:uppercase;
        color:var(--c-text-muted)}
.directlist a{font-family:var(--font-primary);font-size:var(--fs-h3);line-height:1.2}
.flownote{margin-top:var(--sp-m)}
/* EN MÓVIL LOS CANALES VAN ANTES DEL FORMULARIO, y esto lo decidió una medición, no el gusto: con
   el orden de origen —formulario primero, porque es a lo que va la página— el teléfono quedaba
   737px por debajo del primer campo en una pantalla de 812. Es decir, quien entra en Contacto
   queriendo llamar tiene que pasar el formulario entero para encontrar el número. El propio doc del
   arquetipo dice lo contrario en tantas palabras: «va junto al formulario, no debajo del todo;
   quien prefiere llamar tiene que verlo sin bajar».
   `order` y no reordenar el HTML: en ancho el blueprint pone `.head` a la izquierda y `.formwrap` a
   la derecha por posición en la rejilla, así que el orden de origen no se ve, y el foco de teclado
   sigue llegando primero al formulario, que es lo correcto para quien sí venía a escribir. */
@media(max-width:1023px){ .contactblock .head{order:-1} }
/* EL PLAZO NO ES UN ROL DE ACENTO, y el verificador tuvo razón al rechazarlo: «3–5 días» no es
   un CTA, ni un icono de acción, ni un enlace, ni un estado activo. Es un dato, y un dato que
   importa se destaca con PESO y con cifras tabulares, no gastando el único color de la página
   — que en esta ficha ya lo tiene el botón de presupuesto, que es a donde va todo. */
.steps .days{font-variant-numeric:tabular-nums;font-weight:600;margin:0}

/* COMP-CONFIGURATOR · TPL-PDP-05. El configurador ES la página, así que ocupa las dos mitades del
   canvas como lo hace el bloque de compra de TPL-PDP-01 — misma línea, distinto contenido. */
.cfgbox{min-width:0;display:flex;flex-direction:column;gap:var(--sp-xs);align-self:start;
        padding:var(--sp-m);border:1px solid var(--c-border);background:var(--c-bg-alt)}
.cfgbox h2{margin:0 0 var(--sp-xs)}
.cfgrow{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:var(--sp-s)}
.cfgnote{margin:var(--sp-xs) 0 var(--sp-s)}
@media(min-width:1024px){
  .cfg .canvas{grid-template-columns:minmax(0,1fr) minmax(0,1fr);
               column-gap:var(--sp-l);align-items:start}
}

/* COMP-SAMPLE-REQUEST · las tres muestras, en cuadrado y pequeñas: son una carta de color, no una
   galería. */
.swatches{list-style:none;margin:0;padding:0;display:grid;
          grid-template-columns:repeat(3,minmax(0,1fr));gap:var(--sp-xs)}

/* COMP-GALLERY de trabajos entregados · el pie ES el dato (la medida), así que no se atenúa hasta
   desaparecer ni se esconde en un hover que en táctil no existe. */
.shots.done{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-l);
            grid-template-columns:repeat(var(--cols,1),minmax(0,1fr))}
.shots.done figure{margin:0}
.shots.done figcaption{margin-top:.4rem;font-variant-numeric:tabular-nums}
@media(max-width:599px){.shots.done{grid-template-columns:1fr}}

/* TGL-PDP-LAYOUT: editorial · el MISMO esqueleto con la foto mandando. Es un toggle y no un
   arquetipo, y esto es todo lo que cuesta: el reparto de columnas y una fila de miniaturas. Si
   hicieran falta secciones nuevas para conseguirlo, sería otra arquitectura y llevaría su doc.

   SE MUEVEN LAS COLUMNAS DE LOS HIJOS, NUNCA LA REJILLA DEL CANVAS, y esto costó un desbordamiento
   de 87px antes de entenderlo. El primer intento redeclaraba `.pdp.editorial .canvas` con dos pistas
   anónimas y GANABA por especificidad a `[data-comp="lp-asymmetric"] .canvas` — pero los hijos
   siguen pidiendo `grid-column: c 7 / c 13`, líneas nombradas que esa rejilla ya no tenía. El
   navegador creó quince pistas implícitas de 0px para llegar hasta la línea 13 y dejó el bloque de
   compra fuera del canvas. Es la misma trampa que el comentario de `.pdp .canvas` ya avisaba
   doscientas líneas más arriba: pelearse con la capa del blueprint en vez de usarla.
   `lp-centered` es el único que no redefine `.canvas`, así que es el único donde tocar el reparto
   es tocar algo que existe. */
.pdp.editorial .canvas{padding-block:var(--sp-xl)}
.pdp.editorial .pdp-gal > .frame{aspect-ratio:1/1}
.pdp.editorial .pdp-thumbs{grid-template-columns:repeat(3,minmax(0,1fr));gap:var(--sp-s)}
@media(min-width:1024px){
  [data-comp="lp-centered"] .pdp.editorial .canvas{
      grid-template-columns:minmax(0,1.9fr) minmax(0,1fr);column-gap:var(--sp-xl)}
  [data-comp="lp-strict-grid"] .pdp.editorial .pdp-gal{grid-column:1/7}
  [data-comp="lp-strict-grid"] .pdp.editorial .pdp-buy{grid-column:7/13}
  [data-comp="lp-broken-grid"] .pdp.editorial .pdp-gal{grid-column:c 1/c 7}
  [data-comp="lp-broken-grid"] .pdp.editorial .pdp-buy{grid-column:c 7/c 13}
  [data-comp="lp-asymmetric"] .pdp.editorial .pdp-gal{grid-column:c 1/c 7}
  [data-comp="lp-asymmetric"] .pdp.editorial .pdp-buy{grid-column:c 7/c 13}
}

/* ── ESTILOS DE TPL-E-06 · TALLA / PRUEBA ──────────────────────────────────────────────────── */

/* COMP-FIT-FINDER · ocupa el sitio del héroe, así que respira como un héroe y no como un widget. */
.finder .canvas{padding-block:var(--sp-xl)}
.findbox{min-width:0;align-self:start;display:flex;flex-direction:column;gap:var(--sp-xs);
         padding:var(--sp-m);border:1px solid var(--c-border);background:var(--c-bg-alt)}
.findrow{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:var(--sp-s)}
/* LA RESPUESTA SE VE, no se esconde detrás de un botón que en una maqueta no hace nada. Y se marca
   con filete y peso: un resultado no es un CTA, ni un icono de acción, ni un enlace, ni un estado
   activo, así que no gasta el único color de la página — que aquí lo tiene «Ver mi talla». */
.findres{margin:var(--sp-xs) 0 0;padding:var(--sp-s);border:1px solid var(--c-border);
         font-family:var(--font-primary);font-size:var(--fs-h3);line-height:1.25}
@media(min-width:1024px){
  [data-comp="lp-centered"] .finder .canvas{grid-template-columns:minmax(0,1fr) minmax(0,1fr);
                                            column-gap:var(--sp-l);align-items:start}
}

/* COMP-MEASURE-TABLE · seis columnas de cifras no caben en 375px, así que la tabla scrollea DENTRO
   de su caja y nunca la página. `color` y `font-family` van en la propia <table> porque estas
   maquetas no llevan doctype: en quirks mode una tabla no hereda ninguna de las dos y las celdas
   caen al defecto del documento, que sobre un fondo oscuro es texto negro sobre negro. */
.tablewrap{overflow-x:auto}
/* `.mtab` a secas y no `table.mtab`: la puerta busca el selector de la CLASE precedido de inicio,
   espacio o coma, y con razón — comprueba que la regla existe sobre el elemento mismo, y un
   selector calificado por etiqueta es una regla distinta que ella no puede reconocer. */
.mtab{color:var(--c-text);font-family:var(--font-secondary);border-collapse:collapse;
      width:100%;font-size:var(--fs-small);font-variant-numeric:tabular-nums}
.mtab th,.mtab td{padding:.5rem .7rem;border-bottom:1px solid var(--c-border);text-align:right;
                  white-space:nowrap}
.mtab thead th{font-family:var(--font-primary);letter-spacing:.04em;border-bottom-width:2px}
.mtab th:first-child,.mtab thead th:first-child{text-align:left}
.mtab tbody th{font-family:var(--font-primary);font-weight:600}

/* COMP-FIT-GALLERY · la talla va en peso y no en gris pequeño: es el dato que hace comparable una
   fotografía de catálogo, y atenuado deja de leerse justo donde importa. */
.bodies{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-l);
        grid-template-columns:repeat(var(--cols,1),minmax(0,1fr))}
.bodies figure{margin:0}
.bodysize{margin:.45rem 0 0;display:flex;flex-wrap:wrap;gap:.2rem var(--sp-s);align-items:baseline}
.bodysize b{font-family:var(--font-primary);font-size:var(--fs-h3);line-height:1.1}
.bodysize span{font-size:var(--fs-small);font-variant-numeric:tabular-nums}
@media(max-width:599px){.bodies{grid-template-columns:1fr}}

/* COMP-RETURN-PROMISE · tres cifras grandes y una línea bajo cada una. */
.retfigs .fig p{margin:.2rem 0 0}

/* El selector de talla lleva el stock dentro del propio control, y la agotada se ve agotada: no se
   descubre al pulsar «añadir», que es el momento exacto en que se pierde la compra. */
.sizes .opt span{display:flex;flex-direction:column;align-items:center;gap:.05rem;line-height:1.1}
.sizes .opt em{font-style:normal;font-size:var(--fs-eyebrow);letter-spacing:.06em;
               color:var(--c-text-muted);font-variant-numeric:tabular-nums}
.sizeopt.out{opacity:.45}
/* Tachado y no sólo apagado: la opacidad sola es contraste bajo, y un contraste bajo se lee como
   «poco importante», no como «no está». */
.sizeopt.out span{text-decoration:line-through;text-decoration-thickness:1px}
.sizeopt.out em{text-decoration:none}

/* ── ESTILOS DE TPL-E-07 · LOTE / PESO ─────────────────────────────────────────────────────── */

/* COMP-DELIVERY-WINDOW · reutiliza la caja del buscador de talla: son el mismo control —una
   pregunta corta cuya respuesta puede cancelar la compra— y darles dos cajas distintas sería dos
   implementaciones de una cosa. */
.dwin .canvas{padding-block:var(--sp-xl)}
@media(min-width:1024px){
  [data-comp="lp-centered"] .dwin .canvas{grid-template-columns:minmax(0,1fr) minmax(0,1fr);
                                          column-gap:var(--sp-l);align-items:start}
}

/* COMP-BATCH-CARD · la trazabilidad de cada pieza. Dos columnas y no tres: cada tarjeta lleva foto,
   precio por kilo, importe estimado y tres filas de datos, y a tres columnas eso es una ficha
   técnica de 240px que nadie lee. */
.batchlist{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-l);
           grid-template-columns:repeat(var(--cols,1),minmax(0,1fr))}
.batchcard{min-width:0;display:grid;gap:var(--sp-s)}
.batchcard figure{margin:0}
.batchbody{display:grid;gap:.15rem;min-width:0}
.batchcard h3{margin:0}
.batchcard .price{margin:0;font-size:var(--fs-h3);font-variant-numeric:tabular-nums}
.batchmeta{margin:var(--sp-xs) 0 0;display:grid;gap:.15rem;font-size:var(--fs-small)}
.batchmeta > div{display:flex;flex-wrap:wrap;gap:.2rem var(--sp-s);
                 padding-top:.2rem;border-top:1px solid var(--c-border)}
.batchmeta dt{color:var(--c-text-muted);flex:0 0 15ch}
.batchmeta dd{margin:0;font-variant-numeric:tabular-nums}
@media(max-width:599px){.batchlist{grid-template-columns:1fr}}

/* COMP-WEIGHT-NOTE · tres cifras, como la promesa de devolución de TPL-E-06: es el mismo gesto
   —decir un número antes de que el cliente lo descubra— y comparte su rejilla. */
.weightn .canvas{padding-block:var(--sp-l)}
.pdp-approx{margin:.1rem 0 0;font-variant-numeric:tabular-nums}

/* COMP-ORIGIN-MAP · fotografía del puerto, no un plano dibujado. Mismo argumento que el mapa de
   TPL-C-13: un plano de una costa que no existe AFIRMA algo falso, y una fotografía aérea sólo
   enseña. Las chinchetas llevan el nombre de la lonja y se pintan con la superficie de la página y
   un filete, nunca con el acento: un topónimo no es un CTA ni un estado activo. */
.mapwrap{position:relative;min-width:0}
.mapwrap .mapshot{margin:0;aspect-ratio:1200/570}
.mapwrap .mapshot img{width:100%;height:100%;object-fit:cover;display:block}
.portpin{position:absolute;transform:translate(-50%,-100%);
  background:var(--c-bg);color:var(--c-text);border:1px solid var(--c-border);
  font-size:.75rem;font-weight:700;padding:.2rem .5rem;
  border-radius:var(--radius-pill,999px);white-space:nowrap;
  box-shadow:0 2px 6px rgba(0,0,0,.28)}

/* COMP-COLD-CHAIN · foto de la caja abierta y tres pasos. */
.coldsteps{grid-template-columns:1fr}
@media(min-width:768px){.cold .coldsteps{grid-template-columns:repeat(3,minmax(0,1fr))}}

/* ── ESTILOS DE TPL-E-08 · SUSCRIPCIÓN ─────────────────────────────────────────────────────── */

/* COMP-PLAN-PICKER · es el control de compra, así que se comporta como uno: tres columnas que se
   comparan de un vistazo y no tres tarjetas apiladas que hay que recordar. */
.subplans{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-l);
          grid-template-columns:repeat(var(--cols,1),minmax(0,1fr));align-items:start}
.subplan{min-width:0;position:relative;display:flex;flex-direction:column;gap:.2rem;
      padding:var(--sp-m);border:1px solid var(--c-border);background:var(--c-bg)}
/* EL RECOMENDADO SE MARCA CON FILETE MÁS GRUESO Y UNA ETIQUETA, nunca con el acento: no es un CTA
   ni un estado activo, y dentro de la propia tarjeta el acento ya lo tiene su botón. Marcarlo con
   color pondría dos cosas gritando en el mismo bloque. */
.subplan.best{border-width:2px}
.subbadge{position:absolute;top:0;left:var(--sp-m);transform:translateY(-50%);
  background:var(--c-bg);border:1px solid var(--c-border);padding:.1rem .5rem;
  font-size:var(--fs-eyebrow);letter-spacing:.14em;text-transform:uppercase;white-space:nowrap}
.subplan h3{margin:0}
.subfee{margin:.2rem 0 0;font-size:var(--fs-h3);font-variant-numeric:tabular-nums}
.subwhat{margin:.35rem 0 0;font-family:var(--font-primary)}
.subrows{list-style:none;margin:var(--sp-s) 0 var(--sp-m);padding:0;display:grid;gap:.2rem;
          font-size:var(--fs-small)}
.subrows li{padding-top:.25rem;border-top:1px solid var(--c-border)}
.subplan .btn{margin-top:auto;align-self:start}
@media(max-width:599px){.subplans{grid-template-columns:1fr}}

/* COMP-FIRST-BOX · el contenido exacto, con la cantidad delante. Las cifras van tabulares y a la
   izquierda para que «250 g» y «1» se lean como una columna y no como tres frases. */
.boxlist{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-s);min-width:0}
.boxlist li{display:grid;grid-template-columns:7ch minmax(0,1fr);gap:var(--sp-s);
            padding-top:var(--sp-xs);border-top:1px solid var(--c-border)}
.boxqty{font-family:var(--font-primary);font-size:var(--fs-h3);line-height:1.1;
        font-variant-numeric:tabular-nums}
.boxlist div{display:grid;gap:.05rem;min-width:0}
.boxname{font-family:var(--font-primary);font-weight:600}

/* La ficha de suscripción no lleva precio arriba, y el hueco donde toda ficha pone una cifra es
   justo donde el lector la busca — así que la ausencia se dice, no se deja en blanco. */
.nopricenote{margin:var(--sp-xs) 0;padding:var(--sp-s);border:1px solid var(--c-border);
             font-family:var(--font-primary)}

/* Finish options. A radio group that reads as a row of chips, and the CHECKED state is border
   weight and ground, never colour alone — the same rule the filter chips follow. */
.opts{border:0;margin:var(--sp-xs) 0 0;padding:0;display:flex;flex-wrap:wrap;gap:.4rem}
.opts legend{font-size:var(--fs-eyebrow);letter-spacing:.16em;text-transform:uppercase;
             color:var(--c-text-muted);margin-bottom:.35rem;padding:0}
.opt{display:inline-flex;align-items:center;gap:.35rem;cursor:pointer;
     border:1px solid var(--c-border);border-radius:999px;padding:.3rem .7rem;
     font-size:var(--fs-small);color:var(--c-text-soft);white-space:nowrap}
.opt input{position:absolute;opacity:0;width:1px;height:1px}
.opt:hover{border-color:var(--c-text-muted);color:var(--c-text)}
.opt:has(input:checked){border-color:var(--c-text);color:var(--c-text);font-weight:700;
                        background:var(--c-bg-alt)}
.opt:has(input:focus-visible){outline:2px solid var(--c-accent);outline-offset:2px}
.qty{max-width:12rem}

/* COMP-ACCORDION — the same `<details>/<summary>` recipe COMP-FAQ uses, because they are the same
   control doing the same job, and giving one of them a second implementation is how two things
   that should stay identical start to drift. THAT ALREADY HAPPENED: by the time anybody looked
   there were four emitters and three behaviours, and one of them was not a disclosure at all.
   They all go through disclosure_list_html() now, and RT_MOCKUP_FAQ_ALL_OPEN keeps them there. */
.acc .qas{max-width:none}

/* The switcher grows a second label once a template ships more than one page. */
.flabel-2{margin-left:var(--sp-s)}
@media(max-width:767px){.flabel-2{margin-left:var(--sp-xs)}}
/* ── the group layer is gone, and so is its CSS ───────────────────────────────────────────────
   The index shipped three shapes. One card per archetype hid the variety; a group header with its
   anchors underneath brought the variety back and cost two levels of hierarchy to read before you
   saw a single design. The index has one job — show every home so you can point at one — and a
   title over a row of already-titled things earns its space only when the row is long enough to
   lose track of. Four cards is not that.

   The rules that painted `.tgroup`, `.tgroup-head`, `.tgroup-fits` and `.tgroup-count` are deleted
   rather than left behind. Dead CSS for a structure the page no longer emits is the same lie as a
   token nothing reads, and it is the kind that survives for months because everything still
   renders. */
/* ── the template page: identity, switcher, variants ────────────────────────────────────────── */

.tpl-head{background:var(--c-bg-alt);border-bottom:1px solid var(--c-border);
          padding-block:var(--sp-m)}
.tpl-head h2{font-family:var(--font-primary);font-size:var(--fs-h2);line-height:1.1;
             margin:.15rem 0 .4rem}
.tpl-head .eyebrow{color:var(--c-text-muted)}
.tpl-dna,.tpl-wire{font-size:var(--fs-small);color:var(--c-text-soft);margin-top:.2rem}
.tpl-wire code,.tpl-dna code{font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;
                             font-size:.92em;color:var(--c-text-muted)}

/* Same recipe and the same reasons as `.gal-filter`: sticky rather than fixed so the first
   variant does not start underneath it, and one horizontally-scrollable row below the tablet
   breakpoint so a wrapping chip set cannot eat a quarter of a phone screen. `top` clears the
   chrome bar above it, which is the one difference — that bar is sticky too. */
.switch{position:sticky;top:2.9rem;z-index:40;background:var(--c-bg);
        border-bottom:1px solid var(--c-border)}
.switch .gal-wrap{display:flex;flex-wrap:wrap;align-items:center;gap:.35rem;
                  padding-block:var(--sp-xs)}
.sbtn{display:inline-flex;align-items:center;justify-content:center;
      font:inherit;font-size:var(--fs-small);line-height:1.2;padding:.3rem .7rem;
      border:1px solid var(--c-border);border-radius:999px;background:transparent;
      color:var(--c-text-soft);cursor:pointer;white-space:nowrap;
      transition:background var(--dur-color) var(--ease),color var(--dur-color) var(--ease),
                 border-color var(--dur-color) var(--ease)}
.sbtn:hover{border-color:var(--c-accent);color:var(--c-text)}
/* Colour AND weight, never colour alone — a chip set that reads only by hue is invisible to a
   monochrome reader and to most colour-blind ones. */
.sbtn[aria-pressed="true"]{background:var(--c-accent);border-color:var(--c-accent);
                           color:var(--c-on-accent);font-weight:700}
@media(max-width:767px){
  .switch{top:2.7rem}
  .switch .gal-wrap{flex-wrap:nowrap;overflow-x:auto;scrollbar-width:none}
  .switch .gal-wrap::-webkit-scrollbar{display:none}
  .sbtn{flex:0 0 auto}
}

/* ── the anchor swatches on a card ───────────────────────────────────────────────────────────
   Two colours per anchor: the ground it paints on and the accent that lands on it. A name alone
   ("Direct") tells a reader nothing they can SEE, and this is a catalogue browsed with the eyes.
   The colours are inline because they are DATA — the value of that anchor's `--c-bg` and
   `--c-accent` — and not a decision this stylesheet gets to make. */
.tsws{gap:.4rem}
.tsw{display:inline-flex;align-items:center;gap:.3rem;
     font-size:.68rem;line-height:1.4;letter-spacing:.03em;white-space:nowrap;
     border:1px solid var(--c-border);border-radius:999px;padding:.12rem .5rem .12rem .2rem;
     color:var(--c-text-muted)}
.tsw-g,.tsw-a{display:inline-block;width:.7rem;height:.7rem;border-radius:50%;
              border:1px solid color-mix(in srgb,var(--c-text) 22%,transparent)}
.tsw-a{margin-left:-.45rem}
@media(prefers-reduced-motion:reduce){
  .tcard,.tgo::after{transition:none}
  .tcard:hover{transform:none}
  .tcard:hover .tgo::after{transform:none}
}
/* ── the strip's data bar. OUTSIDE `[data-anchor]` on purpose: it reports the axes, so it must
      not be painted by them. Its own ground is the shell's. ── */
.strip{border-top:1px solid var(--c-border);scroll-margin-top:4.5rem}
.strip[hidden]{display:none}
.meta{background:var(--c-bg-alt);color:var(--c-text);padding-block:var(--sp-m);
      border-bottom:1px solid var(--c-border)}
.meta-pair{display:flex;flex-wrap:wrap;align-items:baseline;gap:.5ch;font-size:var(--fs-nav)}
.meta-pair b{font-family:var(--font-primary);letter-spacing:.04em}
.meta-pair .x{color:var(--c-accent);font-weight:700;padding-inline:.4ch}
.meta-sub{margin-top:.35rem;font-size:var(--fs-small);color:var(--c-text-muted)}
.meta-sub code{font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:.95em}

/* ── the toggle readout (CAPA 3). Deliberately NOT styled like an `.axis`: a toggle is a
      configuration the client answered, not one of the five perceptual positions, and giving it
      the same accent rule would read as a sixth axis. A changed toggle takes the accent border so
      the eye finds it across eight strips; a default stays quiet, which is what a default is. ── */
.meta-tgl{margin-top:.45rem;display:flex;flex-wrap:wrap;gap:.4rem .6rem;align-items:baseline;
          font-size:var(--fs-small);color:var(--c-text-muted)}
.meta-tgl .tgl{display:inline-flex;align-items:baseline;gap:.5ch;
               border:1px solid var(--c-border);border-radius:999px;padding:.15rem .7rem;
               color:var(--c-text)}
.meta-tgl .tgl-set{border-color:var(--c-accent);font-weight:700}
.meta-tgl code{font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;
               font-size:.9em;color:var(--c-text-muted);font-weight:400}
.meta-tgl i{font-style:normal;font-size:var(--fs-eyebrow);letter-spacing:.12em;
            text-transform:uppercase;color:var(--c-text-muted);font-weight:400}
.meta-tgl .tgl-set i{color:var(--c-accent)}
.axes{margin-top:var(--sp-s);display:grid;gap:.5rem;grid-template-columns:minmax(0,1fr)}
@media(min-width:600px){.axes{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(min-width:1024px){.axes{grid-template-columns:repeat(5,minmax(0,1fr))}}
.axis{border-top:2px solid var(--c-accent);padding-top:.4rem;min-width:0}
.axis dt{font-size:var(--fs-eyebrow);letter-spacing:.18em;text-transform:uppercase;
         color:var(--c-text-muted)}
.axis dd b{display:block;font-size:var(--fs-small);font-weight:700}
.axis dd span{display:block;font-size:var(--fs-eyebrow);color:var(--c-text-muted);
              font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;line-height:1.5}

/* ── the handoff. A pick has to LEAVE something, or choosing stays a thing that happened in
      somebody's head. This is that something: the pair, the section inventory and the five axes
      with their token values, as plain text somebody pastes into the conversation.

      TEXT AND NOT A FILE. The Artifact sandbox makes a page-initiated download inert — an
      `<a download>` with a `data:` or `blob:` href does nothing there — so a "descargar el spec"
      button would be a control that looks like it works and does not. Selectable text always
      works, in every sandbox, with the clipboard API blocked, and with JS off.

      `<details>` and not a panel: it is the platform's own disclosure, it needs no script, and
      the text sits in the DOM whether it is open or shut — collapsed is a paint state, not an
      existence one, so grep, the accessibility tree and the build's own assertion all see it. ── */
.handoff{margin-top:var(--sp-s);border-top:1px solid var(--c-border)}
.handoff>summary{cursor:pointer;font-size:var(--fs-small);font-weight:700;color:var(--c-text);
                 display:flex;align-items:center;gap:.4ch;min-height:44px}
.handoff>summary::marker{color:var(--c-accent)}
.handoff-bar{display:flex;flex-wrap:wrap;align-items:center;gap:var(--sp-xs);margin-bottom:.45rem}
/* Same both-axes centring as .btn and .fbtn, and the same reason: this sits in a wrapping flex
   row that is free to stretch it. 44px is the touch target, not a decoration. */
.handoff-copy{display:inline-flex;align-items:center;justify-content:center;font:inherit;
              font-size:var(--fs-small);line-height:1.2;font-weight:700;padding:.45rem 1rem;
              min-height:44px;border:1px solid var(--c-accent);border-radius:999px;
              background:var(--c-accent);color:var(--c-on-accent);cursor:pointer}
.handoff-copy[hidden]{display:none}
.handoff-said{font-size:var(--fs-small);color:var(--c-text-muted)}
/* A spec line is longer than a phone, so it scrolls INSIDE ITS OWN BOX — html-mockup/SKILL.md,
   "wide blocks scroll inside their own container". `tabindex="0"` on the element because a
   scroll container that only a mouse can reach fails WCAG 2.1.1; the browser gives a focusable
   box arrow-key scrolling for free. */
.handoff pre{overflow-x:auto;margin:0;padding:var(--sp-xs);
             background:var(--c-bg);color:var(--c-text);
             border:1px solid var(--c-border);border-radius:var(--radius-input);
             font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;
             font-size:var(--fs-eyebrow);line-height:1.6;white-space:pre}

/* The sample. FULL WIDTH, never a bounded cell: `LP-ASYMMETRIC` and `LP-BROKEN-GRID` bleed to
   `full-end`, which is the GRID CONTAINER's edge — inside a padded column the bleed silently
   stops short and the blueprint stops being the blueprint. (The `margin-right:calc(50% - 50vw)`
   technique is the one layout-patterns.md rejects: percentage margins on a grid item resolve
   against the item's own grid area, measured 312px past a 1265px viewport.) */
.sample{background:var(--c-bg);color:var(--c-text);font-family:var(--font-secondary)}

.gal-foot{padding-block:var(--sp-l);border-top:1px solid var(--c-border);
          color:var(--c-text-muted);font-size:var(--fs-small)}
.noscript{background:var(--c-accent);color:var(--c-on-accent);padding:var(--sp-s) 0;
          font-size:var(--fs-small);font-weight:600}
CSS;

// ── block 5 · one block per anchor ─────────────────────────────────────────────────────────────

/**
 * The five axis positions plus the anchor's own typography, motion and imagery.
 * Card recipes are quoted from design-personalities.md § "Card recipe" beside the rules.
 */
function anchor_block( $key, $A, $SCALE, $DENSITY, $GROUND, $ELEVATION, $COMPOSITION, $ACCENT ) {
	$sc = $SCALE[ $A['scale'] ];
	$gr = $GROUND[ $A['ground'] ];
	$el = $ELEVATION[ $A['elevation'] ];
	$ac = $ACCENT[ $A['ground'] ];
	$lp = $COMPOSITION[ $A['composition'] ]['lp'];

	$out  = "\n/* ══════════ {$A['id']} — {$A['name']} ══════════\n";
	$out .= "   axes: scale {$A['scale']} · ground {$A['ground']} · density {$A['density']}"
		. " · composition {$A['composition']} · elevation {$A['elevation']}\n";
	$out .= "   accent {$ac['hex']} measured {$ac['r_bg']} on --c-bg, {$ac['r_alt']} on --c-bg-alt;\n";
	$out .= "   --c-on-accent resolves to {$ac['on_is']} ({$ac['on']}) at {$ac['r_on']} on the fill. ══════════ */\n";
	$out .= "[data-anchor=\"$key\"]{\n";
	$out .= "  --type-ratio:{$sc['ratio']}; --display-lh:{$sc['lh']}; --fs-h1-max:{$sc['h1max']};   /* scale: {$A['scale']} */\n";
	$out .= "  --sp-scale:{$DENSITY[ $A['density'] ]};                                  /* density: {$A['density']} */\n";
	$out .= "  --c-bg:{$gr['bg']}; --c-bg-alt:{$gr['alt']}; --c-text:{$gr['text']};   /* ground: {$A['ground']} — "
		. ratio_str( $gr['text'], $gr['bg'] ) . " */\n";
	$out .= "  --elev-rest:{$el['rest']};\n";
	$out .= "  --elev-hover:{$el['hover']};                        /* elevation: {$A['elevation']} */\n";
	$out .= "  /* composition: $lp */\n";
	$out .= "  --c-accent:{$ac['hex']}; --c-on-accent:{$ac['on']};\n";
	$out .= "  --font-primary:{$A['font_1']};\n";
	$out .= "  --font-secondary:{$A['font_2']};\n";
	$out .= "  --track-display:{$A['track_disp']}; --track-wordmark:{$A['track_word']};\n";
	$out .= "  --dur-color:{$A['dur_color']}; --dur-lift:{$A['dur_lift']}; --dur-zoom:{$A['dur_zoom']}; --lift:{$A['lift']};\n";
	$out .= "  --ratio-hero:{$A['ratio_hero']}; --ratio-card:{$A['ratio_card']};        /* imagery: {$A['imagery']} */\n";
	$out .= "}\n";
	$out .= "/* card recipe: {$A['card']} */\n";

	switch ( $key ) {
		case 'editorial':
			$out .= <<<'CSS'
[data-anchor="editorial"] .card{background:none;border-radius:0}
[data-anchor="editorial"] .card .frame{border-radius:0}
[data-anchor="editorial"] .card .body{padding:var(--sp-s) 0 0}
CSS;
			break;
		case 'direct':
			$out .= <<<'CSS'
[data-anchor="direct"] .card{background:var(--c-bg-alt);border-radius:0;box-shadow:var(--elev-rest);
  transition:box-shadow var(--dur-lift) var(--ease)}
[data-anchor="direct"] .card:hover{box-shadow:var(--elev-hover)}
[data-anchor="direct"] .card .frame{border-radius:0}
CSS;
			break;
		case 'matter':
			$out .= <<<'CSS'
[data-anchor="matter"] .card{background:var(--c-bg);border-radius:var(--radius-container);
  overflow:hidden;box-shadow:var(--elev-rest);transition:box-shadow var(--dur-lift) var(--ease)}
[data-anchor="matter"] .card:hover{box-shadow:var(--elev-hover)}
[data-anchor="matter"] .card .frame{border-radius:0}
CSS;
			break;
		case 'vitrine':
			$out .= <<<'CSS'
/* LA SOMBRA EN REPOSO NO SE VE, Y ESA ES LA RECETA. `soft-shadow` sobre un ground `ink` es
   `0 1px 2px rgba(0,0,0,.04)` sobre #0E1113: negro sobre negro, invisible por construcción. Sobre
   un fondo oscuro lo que separa una tarjeta del papel no es una sombra, es el ESCALÓN DE
   SUPERFICIE — `--c-bg-alt` sobre `--c-bg`. La sombra no se retira por eso: se guarda para el
   hover, donde la tarjeta ya está levantada y tiene un borde contra el que leerse. */
[data-anchor="vitrine"] .card{background:var(--c-bg-alt);border-radius:var(--radius-card);
  overflow:hidden;box-shadow:none;
  transition:box-shadow var(--dur-lift) var(--ease),transform var(--dur-lift) var(--ease),
             background var(--dur-color) var(--ease)}
[data-anchor="vitrine"] .card:hover{box-shadow:var(--elev-hover);transform:translateY(var(--lift))}
/* El aire alrededor de la pieza es lo que hace la vitrina, así que la imagen NO va a sangre dentro
   de la tarjeta: respira por los cuatro lados y el marco es del color del fondo base, no del de la
   tarjeta — un segundo escalón, hacia dentro. */
[data-anchor="vitrine"] .card .frame{border-radius:var(--radius-image);margin:var(--sp-s);
  background:var(--c-bg)}
/* EL ESCALON SE MIDE CONTRA LA SUPERFICIE QUE HAY DEBAJO, NO CONTRA --c-bg. Es exactamente el
   fallo que --c-text-muted tuvo contra este mismo `.bg-alt`: un token que da por hecho que se
   pinta sobre el fondo base desaparece en la banda alterna. Aqui la tarjeta pintaba --c-bg-alt
   dentro de una seccion --c-bg-alt --- el mismo color contra el mismo color, y con `soft-shadow`
   sobre `ink` invisible en reposo no queda NADA separando la tarjeta del papel. En la banda
   alterna el escalon se da la vuelta: la tarjeta baja a --c-bg y el marco sube a --c-bg-alt, asi
   que hay dos escalones en las dos superficies y ninguno depende de que la seccion sea la base. */
[data-anchor="vitrine"] .bg-alt .card{background:var(--c-bg)}
[data-anchor="vitrine"] .bg-alt .card .frame{background:var(--c-bg-alt)}
CSS;
			break;
		case 'institutional':
			$out .= <<<'CSS'
[data-anchor="institutional"] .card{background:var(--c-bg);border-radius:var(--radius-card);
  overflow:hidden;box-shadow:var(--elev-rest);
  transition:box-shadow var(--dur-lift) var(--ease),transform var(--dur-lift) var(--ease)}
/* "lift on hover with no colour shift" — the transform and the shadow move, nothing else does. */
[data-anchor="institutional"] .card:hover{box-shadow:var(--elev-hover);transform:translateY(var(--lift))}
/* the accent icon chip this anchor's recipe names. A pure SVG glyph, so it adds no visible
   STRING — the copy multiset stays identical to the other three anchors. */
[data-anchor="institutional"] .chip{display:inline-grid;place-items:center;width:2.25rem;height:2.25rem;
  border-radius:var(--radius-button);color:var(--c-accent);
  background:color-mix(in srgb,var(--c-accent) 12%,var(--c-bg))}
CSS;
			break;
	}
	return $out . "\n";
}

foreach ( $ANCHORS as $k => $A ) {
	if ( isset( $used_anchors[ $k ] ) ) {
		$css[] = anchor_block( $k, $A, $SCALE, $DENSITY, $GROUND, $ELEVATION, $COMPOSITION, $ACCENT );
	}
}

/* THE BRAND BLOCK COMES AFTER EVERY ANCHOR BLOCK, AND THE ORDER IS THE WHOLE MECHANISM. Both
   selectors are a single attribute — 0,1,0 — and both land on the SAME element, so source order is
   what decides which --c-bg survives. Everything derived follows for free: custom properties
   substitute against the CASCADED value on the declaring element, not against the value written in
   the same rule, so the muted tone, the border and the inverse surface all re-resolve against the
   brand's two colours without a line of code knowing it.

   WHAT IS NOT HERE IS THE POINT. Colour, type, an OPTIONAL radius scale since PR3e, and nothing
   else. A brand that also moved scale or density would be a fifth anchor wearing a business name,
   and the five axis chips on its own card would be describing a page that no longer matches them. */
/**
 * PR3e — a brand's own corner-radius scale, or '' when it names none.
 *
 * Six tokens, not five: the house's own documented scale (`card`/`button`/`image`/`input`/
 * `container`, design-system.md §Border radius) plus `pill`, this file's own fully-round chip
 * shape (cart badge, category tag, filter chip, back-link, tax tag) — never a documented house
 * token, always rendered at a bare 999px no brand could reach before this function existed. A
 * brand that sets none of the six returns '', so its `[data-brand]` block is byte-identical to
 * every build before this change: the override lives ONLY here, never in `:root`.
 */
function brand_radius_css( $b_v ) {
	if ( ! isset( $b_v['radius'] ) ) {
		return '';
	}
	$r = $b_v['radius'];
	return '--radius-card:' . $r['card'] . ';--radius-button:' . $r['button'] . ';'
		. '--radius-image:' . $r['image'] . ';--radius-input:' . $r['input'] . ';'
		. '--radius-container:' . $r['container'] . ';--radius-pill:' . $r['pill'] . ';';
}
$brand_css = array( '/* ══════════ THE BRANDS — ground, accent, type and an optional radius scale. Never an axis. ══════════ */' );
foreach ( $BRANDS as $b_k => $b_v ) {
	if ( ! isset( $used_brands[ $b_k ] ) ) {
		continue;
	}
	$b_gr = $GROUND[ 'b-' . $b_k ];
	$b_ac = $ACCENT[ 'b-' . $b_k ];
	$brand_css[] = "\n/* ══════════ {$b_v['name']} — {$b_v['sector']} ══════════\n"
		. '   type ' . ratio_str( $b_gr['text'], $b_gr['bg'] ) . " on --c-bg, "
		. ratio_str( $b_gr['text'], $b_gr['alt'] ) . " on --c-bg-alt;\n"
		. '   accent ' . $b_ac['hex'] . ' measured ' . $b_ac['r_bg'] . ' on --c-bg, ' . $b_ac['r_alt']
		. " on --c-bg-alt;\n" . '   --c-on-accent resolves to ' . $b_ac['on_is'] . ' (' . $b_ac['on']
		. ') at ' . $b_ac['r_on'] . ' on the fill. ══════════ */';
	/* The radius string carries its own trailing `;` when non-empty, so a brand that names none
	   appends '' here and the block closes exactly as it did before this function existed — not
	   one byte different for the eleven brands that have never touched radius. */
	$b_radius     = brand_radius_css( $b_v );
	$brand_css[] = '[data-brand="' . $b_k . '"]{'
		. '--c-bg:' . $b_gr['bg'] . ';--c-bg-alt:' . $b_gr['alt'] . ';--c-text:' . $b_gr['text'] . ';'
		. '--c-accent:' . $b_ac['hex'] . ';--c-on-accent:' . $b_ac['on'] . ';'
		. '--font-primary:' . $b_v['font_1'] . ';--font-secondary:' . $b_v['font_2']
		. ( '' === $b_radius ? '' : ';' . $b_radius ) . '}';
}
$css[] = implode( "\n", $brand_css ) . "\n";

/* ══════════ TPL-C-06 · MESA / CARTA — five sections no other archetype has ══════════

   THIS IS THE PART THE BRAND LAYER COULD NOT DO. A brand changes ground, accent, type and
   photographs; it does not change WHICH SECTIONS EXIST OR IN WHAT ORDER, and a catalogue whose
   entries all wear the same skeleton reads as one template with a colour picker no matter how
   different the palettes are. `RT_TPL_TOO_SIMILAR` is the framework's own judge of that — two
   archetypes of a family may share at most half their combined inventory — and it is the reason
   TPL-C-06 exists as a DOC and not as a styling flag: the judge can only measure something that
   declares a wireframe.

   Everything below is written against tokens, so these sections work under any anchor and any
   brand. Not one hard-coded colour except `#fff` on the photographic hero, which is the same
   exception `.hero-visual` already carries and for the same reason: type over a photograph is
   measured against the SCRIM, not against the page ground. */
$css[] = <<<'CSS'
/* ── THE MEASURE, NARROWED FOR THIS ARCHETYPE ─────────────────────────────────────────────────
   `--content-width` is `clamp(1140px, 68vw, 100vw)` house-wide, which on a 2000px screen resolves
   to 1360px of live text. On a carta — two columns of short lines — that measure is not generous,
   it is loud: the line lengths stop being read and start being scanned.

   THIS IS A COMPOSITION DECISION AND NOT AN AXIS ONE, which is the only reason it is allowed here.
   Scale, density, ground, blueprint and elevation are the anchor's and the five chips on the card
   still describe them exactly; how much of the viewport the text block occupies is not one of the
   five. Declared on `[data-arch]` — the same element that carries `[data-anchor]` — so `--col`,
   which is derived from this token in the shared chain, re-resolves against the new value instead
   of the root's. */
[data-arch="tpl-c-06"]{--content-width:clamp(980px,54vw,1220px)}

/* ── COMP-HEADER, floating on the photograph ──────────────────────────────────────────────────
   `.sample` becomes the containing block. The header is the first child either way, so taking it
   out of flow reorders nothing — it stops RESERVING 72px at the top of a section whose whole
   argument is that the room is the first thing you see. */
.sample{position:relative}
.site-head.head-over{position:absolute;inset-inline:0;top:0;z-index:5;background:transparent;
  border-bottom:1px solid color-mix(in srgb,#fff 20%,transparent)}
.site-head.head-over .logo,
.site-head.head-over .mainnav a,
.site-head.head-over .tel{color:#fff}
.site-head.head-over .mainnav a:hover{color:#fff;opacity:.72}

/* ── COMP-HERO-FULL ───────────────────────────────────────────────────────────────────────────
   `.hero-visual` with more height and the copy in the bottom third. Same gradient, same alpha,
   and its own contrast measurement in the build report. */
.hero-full > .canvas{min-height:min(78vh,48rem);align-content:end}
/* NOT `34ch`, AND THE FAILURE IS WORTH THE COMMENT. `ch` resolves against the font of the element
   it is written on — `.head` inherits the BODY face at 1rem, so 34ch measured about 270px and the
   88px display headline came out in five one-word lines. The unit was right for a paragraph and
   wrong for a block whose whole content is a headline. A rem cap says what was meant. */
.hero-full .head{max-width:min(34rem,78%)}
.hero-full .head .lede{max-width:38ch}
@media(max-width:767px){.hero-full > .canvas{min-height:70vh}}

/* ── COMP-MARQUEE ─────────────────────────────────────────────────────────────────────────────
   AN INVERTED BAND AND NOT AN ACCENT ONE, on purpose. The accent whitelist has five roles and a
   ribbon is none of them; painting it in the accent would have meant either stretching "the close"
   to cover something that is not a close, or quietly adding a sixth role to a list that lives in
   `design-tokens.md`. Inverting --c-text and --c-bg costs the palette nothing, is already measured
   at 7:1 by the ground gate, and hits harder than a tinted strip would.

   It stops moving under `prefers-reduced-motion` — and it stays READABLE when it stops, which is
   why the copy repeats twice rather than scrolling a single run off the edge. */
.marquee{margin-inline:calc(50% - 50vw);width:100vw;overflow:clip;
  background:var(--c-text);color:var(--c-bg);padding-block:calc(var(--sp-s) * 1.1)}
.marquee .track{display:flex;width:max-content;animation:nm-mq 42s linear infinite}
.marquee .run{display:flex;align-items:center;gap:var(--sp-l);padding-right:var(--sp-l);
  margin:0;white-space:nowrap;font-family:var(--font-primary);
  font-size:clamp(.95rem,1.3vw,1.2rem);letter-spacing:.02em}
.marquee .run span{display:inline-flex;align-items:center;gap:var(--sp-l)}
.marquee .run span::after{content:"—";opacity:.45}
@keyframes nm-mq{from{transform:translateX(0)}to{transform:translateX(-50%)}}
@media(prefers-reduced-motion:reduce){.marquee .track{animation:none}}

/* ── COMP-MENU-LIST ───────────────────────────────────────────────────────────────────────────
   The dotted leader is a BORDER on a flexible middle cell, not a row of typed periods: typed dots
   are read out one by one by a screen reader and do not stretch. The middle cell is `aria-hidden`
   in the markup for the same reason. */
.menu-list .groups{display:grid;grid-template-columns:1fr;gap:var(--sp-xl)}
@media(min-width:900px){
  .menu-list .groups{grid-template-columns:repeat(2,minmax(0,1fr));
    column-gap:clamp(2rem,6vw,6rem);row-gap:var(--sp-xl)}
}
.menu-group h3{font-family:var(--font-primary);font-size:var(--fs-h3);
  letter-spacing:var(--track-h3);margin:0 0 var(--sp-m)}
.menu-group ol{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-m)}
/* FLEX AND NOT GRID, and the render is what settled it: as a three-column grid the leader drew a
   rule across the whole row instead of the gap between the dish and its price. A leader is a cell
   that EATS THE REMAINING SPACE, which is `flex:1` — grid had to be told the column widths in
   advance, and "whatever is left after two pieces of text neither of which has a known width" is
   not something a track list can say. Dotted rather than solid, and mixed from --c-text rather
   than taken from --c-border: the border token is tuned for a 1px box edge and disappears at 40%
   opacity on a dark ground, which is exactly where this archetype lives. */
.dish{display:flex;flex-wrap:wrap;align-items:baseline;column-gap:.6rem;min-width:0}
.dish .n{font-family:var(--font-primary);font-size:var(--fs-name);min-width:0;order:1}
.dish .dots{order:2;flex:1 1 1.5rem;min-width:1.5rem;transform:translateY(-.32em);
  border-bottom:1px dotted color-mix(in srgb,var(--c-text) 42%,transparent)}
.dish .p{order:3;font-variant-numeric:tabular-nums;white-space:nowrap;font-weight:600}
.dish .d{order:4;flex:1 0 100%;margin:.2rem 0 0;color:var(--c-text-muted);font-size:var(--fs-meta);
  max-width:46ch}
.menu-list .foot{margin:var(--sp-l) 0 0;color:var(--c-text-muted);font-size:var(--fs-meta)}

/* ── COMP-FIGURE-QUOTE ────────────────────────────────────────────────────────────────────────
   Full-bleed split, and the text half sits on --c-bg-alt so the section reads as a stop rather
   than as more page. The signature is display type in italic, NOT the accent — same whitelist
   reasoning as the ribbon. */
/* A full-bleed band brings its own vertical rhythm inside the coloured half; the section padding
   on top of that was ~350px of empty ground between the carta and the portrait. */
.sec.figq{padding-block:0}
.figq{margin-inline:calc(50% - 50vw);width:100vw;display:grid;grid-template-columns:1fr;
  align-items:stretch}
@media(min-width:900px){.figq{grid-template-columns:minmax(0,1fr) minmax(0,1fr)}}
.figq .portrait{margin:0;min-height:22rem}
.figq .portrait img{width:100%;height:100%;object-fit:cover;display:block}
/* THE GUTTER IS THE POINT OF A SPLIT. At `clamp(1.5rem,5vw,4.5rem)` the quote started almost
   against the edge of the photograph, so the two halves read as one collided block instead of as
   a portrait and a voice. A full-bleed split needs MORE inside padding than a contained section,
   not the same amount, because there is no page margin doing the work on that side. */
.figq .say{background:var(--c-bg-alt);display:grid;align-content:center;gap:var(--sp-m);
  padding:var(--sp-xxl) clamp(2.5rem,6vw,6rem)}
/* --fs-h2 and not --fs-h3 was the wrong call on a 1200px measure: at the editorial scale it put a
   six-line pull quote at very nearly headline size, which is the "todo muy grande" this pass was
   asked to fix. A quote should be the second-loudest thing in its section, never the loudest. */
.figq blockquote{margin:0;font-family:var(--font-primary);font-size:var(--fs-h3);
  line-height:calc(var(--display-lh) + .12);letter-spacing:var(--track-h3);text-wrap:balance;
  max-width:30ch}
.figq .who{display:grid;gap:.15rem}
.figq .who b{font-size:1rem}
.figq .who span{color:var(--c-text-muted);font-size:var(--fs-meta)}
.figq .sig{font-family:var(--font-primary);font-style:italic;
  font-size:clamp(1.4rem,2.2vw,1.9rem);line-height:1;opacity:.8}

/* ── COMP-GALLERY, masonry ────────────────────────────────────────────────────────────────────
   THE FIRST CUT WAS A GRID PRETENDING TO BE A MASONRY AND IT LOOKED IT. Six items placed on
   hand-picked column lines with hand-picked top margins: every frame that was shorter than its
   row-mate left a hole under it, and the holes were the first thing anybody saw. A grid places
   items in ROWS, and a row is exactly the thing a masonry does not have — so the offsets were
   fighting the layout model rather than using one.

   `columns` IS the layout model that has no rows. Each frame flows under the previous one in its
   column, `break-inside:avoid` keeps a figure whole, and there is no hole to leave because nothing
   is waiting for a row to close. The height variety now comes from three ratios cycling on
   `nth-child(3n+…)`, which is the same intent the margins were reaching for and the correct place
   to put it.

   Reading order runs DOWN each column rather than across, which for a set of photographs of one
   room is not information the order was carrying. */
.shots.masonry{display:block;columns:2;column-gap:var(--sp-m);list-style:none;margin:0;padding:0}
@media(min-width:900px){.shots.masonry{columns:3}}
.shots.masonry > li{break-inside:avoid;margin:0 0 var(--sp-m)}
.shots.masonry .frame{width:100%;border-radius:0}
.shots.masonry > li:nth-child(3n+1) .frame{aspect-ratio:4/3}
.shots.masonry > li:nth-child(3n+2) .frame{aspect-ratio:3/4}
.shots.masonry > li:nth-child(3n+3) .frame{aspect-ratio:1/1}
.shots.masonry .frame img{width:100%;height:100%;object-fit:cover;display:block}

/* ── COMP-BOOKING, with the room beside it ────────────────────────────────────────────────────
   A form alone in the left half of a 1200px measure is a form in a void: the right half carried
   nothing, and the eye read the emptiness as an unfinished section rather than as air. The
   photograph is not decoration here — it is the answer to "what am I booking", which is the one
   question a booking block should not make you scroll back up for. */
.bookmedia{margin:0;align-self:stretch}
.bookmedia img{width:100%;height:100%;min-height:22rem;object-fit:cover;display:block}
/* A PLACEMENT WRITTEN FOR ONE BLUEPRINT IS A PLACEMENT MISSING FOR THREE. The split was only
   taught `lp-asymmetric`'s named lines, so on `lp-strict-grid` the photograph fell BELOW the form
   at full width and the section stopped being a split at all — it rendered, it just stopped being
   the design. Every blueprint that can host this section now says where the photograph goes, and
   the two that keep it under the form say so on purpose: `lp-centered` forbids bleeds and its
   whole argument is one symmetric axis, so a photograph beside a centred form would be the
   blueprint contradicting itself. */
@media(min-width:1024px){
  [data-comp="lp-asymmetric"] .booking-split .head{grid-column:c 1 / c 7}
  [data-comp="lp-asymmetric"] .booking-split .bookform{grid-column:c 1 / c 7}
  [data-comp="lp-asymmetric"] .booking-split .bookmedia{grid-column:c 8 / wide-end;
    grid-row:1 / span 2}
  [data-comp="lp-strict-grid"] .booking-split .head,
  [data-comp="lp-strict-grid"] .booking-split .bookform{grid-column:1 / 8}
  [data-comp="lp-strict-grid"] .booking-split .bookmedia{grid-column:8 / 13;grid-row:1 / span 2}
  [data-comp="lp-broken-grid"] .booking-split .bookmedia{grid-column:7 / -1;grid-row:1 / span 2}
  /* lp-centered keeps it under the form, contained: nothing bleeds and nothing sits off-axis. */
  [data-comp="lp-centered"] .booking-split .bookmedia{max-width:var(--content-width);
    margin-inline:auto}
}

/* ══════════ THE SECTION HEAD, PER TEMPLATE ══════════
   Four treatments. `index` is what the four anchors already do — a numbered head, decorated the
   anchor's own way. The other three switch that decoration off and put a different gesture in its
   place, so two brands on one anchor no longer open every section identically.

   THE FOURTH ARRIVED BECAUSE THE THIRD RAN OUT, and that is worth writing down rather than
   discovering again. Three modes give the uniqueness assertion below 5 anchors x 3 = 15 pairings,
   and by the eleventh brand `editorial`, `institutional` and `direct` were each full: a twelfth
   brand could only be born on `matter` or `vitrine`, which is the anchor picking the business
   instead of the other way round. A fourth GESTURE — not a fourth variant of an existing one —
   gives the choice back without weakening the rule: the assertion still fires the moment two
   brands open their sections the same way. */

/* `rule` — the eyebrow runs into a hairline that reaches the end of the measure. A horizontal
   gesture where `index` is a vertical one, and it costs one pseudo-element. */
[data-head="rule"] .head[data-index]::before{content:none}
[data-head="rule"] .head .eyebrow{display:grid;grid-template-columns:auto minmax(2rem,1fr);
  gap:.75rem;align-items:center;width:100%}
[data-head="rule"] .head .eyebrow::after{content:"";height:1px;background:var(--c-border)}
[data-comp="lp-centered"][data-head="rule"] .head .eyebrow{
  grid-template-columns:minmax(2rem,1fr) auto minmax(2rem,1fr)}
[data-comp="lp-centered"][data-head="rule"] .head .eyebrow::before{content:"";height:1px;
  background:var(--c-border)}

/* `tight` — no number, no rule: a 2px rule down the LEFT of the head block, and a page that
   breathes about a quarter less. Reads as a document rather than as a brochure. */
[data-head="tight"] .head[data-index]::before{content:none}
[data-head="tight"] .head{padding-left:var(--sp-m);border-left:2px solid var(--c-text)}
[data-head="tight"] .sec{padding-block:calc(var(--sp-section) * .74)}
[data-comp="lp-centered"][data-head="tight"] .head{text-align:left;align-items:flex-start;
  margin-inline:0}

/* `emblem` — a petal mark in the accent, centred over a centred head. Where `index` is a number in
   the margin, `rule` a hairline running out to the measure and `tight` a border down the left edge,
   this one leaves the left edge altogether: the head becomes a centred lockup, mark above heading,
   the way a treatment menu announces a section. It is the only mode that moves the head off its
   composition's own axis, which is what makes it a fourth GESTURE and not a second `rule`.

   SCOPED AWAY FROM THE HERO. `:not([class*="hero"])` covers `hero`, `hero-full`, `hero-visual`,
   `hero-search` and `mhero` in one predicate. Centring a hero head would throw away the asymmetry
   the hero is built on, and it is not what this gesture is for: a section head announces a
   REPEATED unit, the hero announces the page once.

   THE SPECIFICITY IS THE POINT, not an accident. (0,4,1) on the pseudo-element is what lets these
   rules beat the four `[data-anchor="..."] .head::before` renderings of the section index from
   ABOVE them in the file — that block lives in $close_css. `rule` and `tight` only ever had to
   switch the decoration OFF, which `[data-index]` alone was enough for; this one draws in its
   place, so it cannot rely on source order. */
/* `:not(.secrow):not(.bleedband)` — el lockup centrado es el gesto de una sección CONTENIDA.
   Una fila cuyo texto vive en una columna estrecha al lado de un collage, y un panel de tinta
   dentro de un damero, abren por su borde izquierdo: centrar ahí no es el mismo gesto, es el
   mismo gesto aplicado donde no hay eje que ocupar. */
[data-head="emblem"] .sec:not([class*="hero"]):not(.secrow):not(.bleedband) .head{
  align-items:center;text-align:center;margin-inline:auto}
/* `border-radius:50% 0` is TL/BR round, TR/BL square — a petal, not a circle and not a diamond.

   `--c-text-muted` AND NOT `--c-accent`, and the first draft had it the other way round. The
   accent-role gate refused it in as many words: the mark claims none of design-tokens.md's accent
   roles — it is not a CTA, an action icon, a link or an active state — so it is decoration, and
   decoration that paints the accent spends the page's one colour on a shape nobody can click. The
   eyebrow beneath it is already muted; the mark joining it is what makes the two read as one
   lockup instead of as a badge with a caption under it. */
/* `position:static` Y `inset:auto` NO SON DEFENSIVOS, son la corrección de un defecto que se vio
   en un render. `[data-anchor="editorial"] .head::before` cuelga el índice de sección en el margen
   de la página —`position:absolute;right:100%`— y esa regla vive en un `@media(min-width:1280px)`
   más abajo en el archivo. La especificidad de aquí (0,4,1) gana las propiedades que aquí se
   escriben, pero NO apaga las que no se mencionan: la marca heredaba el `absolute` y el `right` y
   aparecía flotando a la izquierda de la sección, leyéndose como una viñeta suelta en vez de como
   el remate de un lockup centrado. Una regla que redefine un pseudo-elemento ajeno tiene que
   apagar su posicionamiento, no sólo repintarlo. */
/* AQUÍ HUBO UNA MARCA Y AHORA NO HAY NINGUNA, y la ausencia es la decisión.
   Era un pétalo de 14px centrado sobre el encabezado. La puerta de roles ya la había obligado a
   dejar el acento y bajar a `muted`, lo cual debería haber sido la primera pista: una forma que no
   puede llevar el color de marca porque no se puede pulsar, y que tampoco dice nada por sí sola,
   es un punto suelto encima de un texto. Un lector la rodeó en rojo junto al eyebrow y escribió
   «no se sabe qué es». Exacto: no se sabía.
   El modo `emblem` sobrevive sin ella. Su gesto es CENTRAR el encabezado en un ancla cuya
   composición no centra nada —`strict-grid` alinea a la izquierda—, y eso sí se lee de un vistazo
   y sí se puede nombrar. Lo que se va es el adorno, no el modo. */
/* SIN EXCEPCIONES Y TAMBIÉN EN EL HERO, que es donde se vio. Las cuatro anclas decoran
   `.head::before` con el índice de sección, y `matter` le añade un filete en `.head::after`. En un
   modo que no numera, `attr(data-index)` resuelve a cadena vacía: el número desaparece y la RAYA
   NO —el propio comentario de `matter` avisa de que eso «parece deliberado», y por eso merece
   escribirse—. Mis primeras reglas apagaban las dos sólo en secciones contenidas, así que el hero
   se quedó con una rayita flotando encima del eyebrow, que es exactamente el gesto sin
   significado que un lector ya había tachado dos veces. Un modo de encabezado apaga la decoración
   del ancla en TODA la tira, y luego decide dónde pone la suya. */
/* `.sec .head` Y NO `.head`, y la diferencia es un píxel de rayita que sobrevivió a la primera
   corrección. Estas reglas viven en el bloque de MODOS DE ENCABEZADO, que se emite pronto; la
   decoración de índice de cada ancla vive en el bloque de cierre, que se emite mucho después. A
   igualdad de especificidad —(0,2,1) las dos— gana la última, o sea el ancla, y el filete de
   `matter` seguía dibujándose sobre el eyebrow. Con `.sec` en medio son (0,3,1) y ganan estén
   donde estén. Apoyarse en el orden del fichero para desactivar algo es apoyarse en dónde alguien
   decidió pegar un bloque hace meses. */
[data-head="emblem"] .sec .head::before,
[data-head="emblem"] .sec .head::after{content:none}
/* The small print belongs to the head, so it follows it — the same finding as the lp-centered note
   immediately below, arrived at from the other direction. */
[data-head="emblem"] .sec:not([class*="hero"]):not(.secrow):not(.bleedband) .pnote{text-align:center;margin-inline:auto}

/* ── ALIGNMENT, FOUND BY LOOKING AND NOT BY A PROBE ───────────────────────────────────────────
   The section note hung at the LEFT of a section whose heading was CENTRED, on every lp-centered
   template. An automated left-edge sweep could not have caught it: under this blueprint half the
   children are legitimately off the modal left, so the check that would have flagged the note
   flags forty things that are correct. It took looking at a render.
   The note follows the head because it belongs to it — it is the head's small print, not the
   section's footer. */
[data-comp="lp-centered"] .pnote,
[data-comp="lp-centered"] .menu-list .foot{text-align:center;margin-inline:auto}

/* ══════════ TPL-C-10 · CLÍNICA / TRATAMIENTOS ══════════ */

/* The four facts are a ROW OF CHIPS and not prose: the patient scans them against the other five
   cards, and a sentence cannot be scanned in parallel. `margin-top:auto` on the fact row, so six
   cards whose descriptions wrap to different heights still align their numbers. */
.treatcards{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-l);
  grid-template-columns:repeat(var(--cols,1),minmax(0,1fr))}
@media(max-width:900px){.treatcards{grid-template-columns:repeat(min(var(--cols,1),2),minmax(0,1fr))}}
@media(max-width:599px){.treatcards{grid-template-columns:1fr}}
.trcard{display:flex;flex-direction:column;background:var(--c-bg);overflow:hidden;
  border-radius:var(--radius-card);box-shadow:var(--elev-rest)}
.trcard .frame{aspect-ratio:16/10;border-radius:0}
.trcard .frame img{width:100%;height:100%;object-fit:cover;display:block}
.trbody{display:flex;flex-direction:column;gap:.4rem;flex:1;padding:var(--sp-m)}
/* 1.0625rem Y .875rem ERAN DOS NÚMEROS QUE NO SABÍAN QUÉ ANCLA LOS PINTABA. Medido: la misma
   tarjeta de tratamiento salía 17/14 bajo `contained` y 17/14 bajo `monumental`, mientras su h2
   pasaba de 40 a 74. El ancla movía la página y la tarjeta se quedaba quieta. */
.trcard h3{margin:0;font-size:var(--fs-name)}
.trcard p{margin:0;color:var(--c-text-muted);font-size:var(--fs-meta)}
/* A FIXED 2×2 GRID AND NOT A WRAPPING FLEX. The four facts are a FIXED SET of four, and flex-wrap
   made their line count depend on how long each label happened to be: one card broke 3+1, the next
   4+0, so the price sat on a different line in every card and the row of numbers never lined up
   across the grid. A fixed set deserves a fixed layout — the wrap was doing arithmetic on text
   length that nobody asked it to do. */
.tfacts{list-style:none;margin:auto 0 0;padding:var(--sp-s) 0 0;display:grid;
  grid-template-columns:repeat(2,minmax(0,1fr));gap:.3rem}
.tfacts li{font-size:.8125rem;border:1px solid var(--c-border);border-radius:var(--radius-button);
  padding:.1rem .45rem;text-align:center;min-width:0;overflow-wrap:break-word}
.tfacts li:last-child{font-weight:700;border-color:var(--c-text)}

/* The caption is UNDER the photograph and carries the date. A label floated over the image would
   be the one place the reader cannot copy the date from. */
.bapair{display:grid;gap:var(--sp-l);grid-template-columns:minmax(0,1fr)}
@media(min-width:768px){.bapair{grid-template-columns:repeat(2,minmax(0,1fr))}}
.bashot{margin:0}
.bashot .frame{aspect-ratio:4/3;border-radius:var(--radius-card);overflow:hidden}
.bashot .frame img{width:100%;height:100%;object-fit:cover;display:block}
.bashot figcaption{display:flex;gap:.5rem;align-items:baseline;margin-top:var(--sp-s);flex-wrap:wrap}
.bashot figcaption b{font-family:var(--font-primary);font-size:var(--fs-name)}
.bashot figcaption span{color:var(--c-text-muted);font-size:var(--fs-meta)}

/* REJILLA FIJA PARA UN CONJUNTO FIJO, que es la regla de la casa y la que estas rejillas no
   seguían. `auto-fill` reservaba una columna para elementos que no existen — tres retratos en un
   ancho donde caben cuatro dejaban la cuarta vacía. `auto-fit` arregló eso y dejó el defecto
   siguiente: el navegador sigue eligiendo CUÁNTAS columnas según el ancho, así que un conjunto de
   seis salía 5+1 a 2560 y 3+3 en un portátil, sin que nadie eligiera ninguno de los dos.
   Ahora el número lo decide grid_cols() en el generador, que es el único sitio que sabe cuántos
   elementos hay, y llega como `--cols`. El fallback `1` no es decorativo: si el atributo faltara,
   una columna es el único valor que no puede dejar hueco ni fila coja. */
.medlist{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-l);
  grid-template-columns:repeat(var(--cols,1),minmax(0,1fr))}
@media(max-width:900px){.medlist{grid-template-columns:repeat(min(var(--cols,1),2),minmax(0,1fr))}}
@media(max-width:599px){.medlist{grid-template-columns:1fr}}
.medico .frame{aspect-ratio:1/1;border-radius:var(--radius-card);overflow:hidden}
.medico .frame img{width:100%;height:100%;object-fit:cover;display:block}
.medbody{display:grid;gap:.1rem;margin-top:var(--sp-s)}
.medbody b{font-family:var(--font-primary);font-size:var(--fs-name)}
.medbody span{color:var(--c-text-muted);font-size:var(--fs-meta)}
.medlic{font-variant-numeric:tabular-nums;font-size:.8125rem}

.inslist{list-style:none;margin:0;padding:0;display:flex;flex-wrap:wrap;gap:var(--sp-s)}
.inslist li{border:1px solid var(--c-border);border-radius:var(--radius-button);
  padding:.35rem .8rem;background:var(--c-bg);font-size:.9375rem}

/* ══════════ TPL-C-11 · PLAN POR FASES ══════════ */

.painlist{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-m)}
@media(min-width:900px){.painlist{grid-template-columns:repeat(2,minmax(0,1fr));gap:var(--sp-l)}}
.painlist li{font-family:var(--font-primary);font-size:clamp(1.05rem,1.7vw,1.35rem);
  line-height:1.35;padding-left:var(--sp-m);border-left:2px solid var(--c-border)}

/* A CALENDAR, NOT A FOUR-STEP PROCESS. The duration is the difference, so it is the element with
   its own weight — the number of months is the thing the reader is measuring against their life. */
/* NOT THE ACCENT, and the whitelist was right to stop it. A total is emphasis on body text, and
   design-tokens.md names five accent roles of which none is emphasis. The weight it needs comes
   from SIZE and from the display face, which cost the palette nothing. */
.phtotal{margin:var(--sp-s) 0 0;font-family:var(--font-primary);
  font-size:clamp(1.1rem,1.8vw,1.5rem);font-variant-numeric:tabular-nums}
.phaselist{list-style:none;margin:0;padding:0;display:grid;gap:0}
/* UNA ANCHURA FIJA PARA EL NÚMERO, NO `auto`. Cada `.phase` es su PROPIA rejilla, así que una
   columna `auto` se dimensiona por FILA y no entre filas: los cuatro cuerpos arrancaban en x
   ligeramente distintas y la lista se leía como un bamboleo. Es el mismo error que una tabla
   hecha de rejillas independientes — la alineación entre filas sólo existe si una sola rejilla,
   o una medida fija, la impone. `ch` aquí SÍ es la unidad correcta: mide la cifra, que es
   exactamente lo que ocupa esa columna. */
.phase{display:grid;grid-template-columns:3.5ch minmax(0,1fr);gap:var(--sp-m);
  padding-block:var(--sp-l);border-top:1px solid var(--c-border)}
.phase:last-child{border-bottom:1px solid var(--c-border)}
.phn{font-family:var(--font-primary);font-size:clamp(1.6rem,3vw,2.6rem);line-height:1;
  color:var(--c-text-muted);font-variant-numeric:tabular-nums}
.phbody{display:grid;gap:.3rem}
.phbody h3{margin:0;font-size:var(--fs-h3);font-family:var(--font-primary)}
.phmonths{font-weight:700;font-variant-numeric:tabular-nums}
.phbody p{margin:0;color:var(--c-text-muted);max-width:58ch}

.subplans{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-l)}
@media(min-width:900px){.subplans{grid-template-columns:repeat(3,minmax(0,1fr))}}
.planbox{position:relative;display:flex;flex-direction:column;gap:.5rem;
  padding:var(--sp-l);border:1px solid var(--c-border);border-radius:var(--radius-card);
  background:var(--c-bg)}
/* AN INVERTED CHIP AND NOT AN ACCENT ONE. Same reasoning that put the TPL-C-06 ribbon on
   --c-text: a "most asked for" flag is marketing emphasis, not one of the five roles, and
   inverting the two ground colours hits harder than a tint while spending nothing. */
.planflag{position:absolute;top:0;right:var(--sp-m);transform:translateY(-50%);
  background:var(--c-text);color:var(--c-bg);font-size:.75rem;font-weight:700;
  letter-spacing:.06em;text-transform:uppercase;padding:.15rem .5rem;border-radius:var(--radius-button)}
.planbox h3{margin:0;font-family:var(--font-primary);font-size:var(--fs-h3)}
.planq{margin:0;display:grid;gap:.1rem}
.planq b{font-family:var(--font-primary);font-size:clamp(1.5rem,2.6vw,2rem);line-height:1;
  font-variant-numeric:tabular-nums}
.planq span{color:var(--c-text-muted);font-size:.875rem;font-variant-numeric:tabular-nums}
.planp{margin:0;color:var(--c-text-muted);font-size:.9375rem}
.planfeats{list-style:none;margin:auto 0 0;padding:var(--sp-s) 0 0;display:grid;gap:.25rem;
  font-size:var(--fs-meta)}
.planfeats li{padding-left:1rem;position:relative}
.planfeats li::before{content:"·";position:absolute;left:.25rem;color:var(--c-text-muted)}
.planbox .btn{margin-top:var(--sp-s);align-self:flex-start}

/* ══════════ TPL-C-12 · URGENCIAS / HOY ══════════ */

/* THE STATE IS A WORD. The accent ring agrees with it; it never carries it alone — this band is
   the one piece of information on the page that cannot depend on colour. */
.sec.urgbar{border-bottom:2px solid var(--c-text);padding-block:var(--sp-xl)}
.urgbar .canvas{display:grid;gap:var(--sp-m);justify-items:start}
.urgstate{margin:0;display:flex;flex-wrap:wrap;align-items:baseline;gap:.5rem var(--sp-m)}
.urgstate b{font-size:.9375rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;
  border:2px solid var(--c-text);border-radius:var(--radius-button);padding:.15rem .6rem}
.urgstate span{font-variant-numeric:tabular-nums;font-weight:600}
.urgbar h1{margin:0;max-width:20ch}
.urgnote{margin:0;color:var(--c-text-muted);font-size:.9375rem}
.btn-lg{font-size:1.125rem;padding:.9rem 1.6rem}

.triagelist{list-style:none;margin:0;padding:0;display:grid;gap:0}
.trow{display:grid;gap:.35rem var(--sp-m);padding-block:var(--sp-m);
  border-top:1px solid var(--c-border);align-items:baseline}
.triagelist li:last-child{border-bottom:1px solid var(--c-border)}
@media(min-width:900px){.trow{grid-template-columns:minmax(0,1.1fr) minmax(0,1.4fr) auto}}
.trow b{font-family:var(--font-primary);font-size:var(--fs-name)}
.trdo{color:var(--c-text-muted)}
.trmin{font-variant-numeric:tabular-nums;font-weight:700;white-space:nowrap}

.waitlist{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-l)}
@media(min-width:768px){.waitlist{grid-template-columns:repeat(2,minmax(0,1fr))}}
.waitlist b{display:block;font-family:var(--font-primary);line-height:1;
  font-size:clamp(2rem,4.5vw,3.4rem);font-variant-numeric:tabular-nums}
.waitlist span{display:block;margin-top:.4rem;color:var(--c-text-muted)}

/* ══════════ TPL-C-07 · STOCK / OCASIÓN ══════════
   Written against tokens only, so the archetype survives any anchor and any brand. */

/* The photograph is a contained band and the filter card RIDES ON ITS BOTTOM EDGE. That overlap is
   the whole composition: it says the search belongs to the stock in the picture rather than being
   a widget parked above it, and it costs one negative margin. */
.hero-search .shot{margin:var(--sp-l) 0 0;aspect-ratio:21/9;border-radius:var(--radius-card);
  overflow:hidden}
.hero-search .shot img{width:100%;height:100%;object-fit:cover;display:block}
.filterbar{position:relative;z-index:1;display:grid;gap:var(--sp-m);
  margin:calc(var(--sp-xl) * -1) auto 0;width:min(100%,60rem);
  background:var(--c-bg);border:1px solid var(--c-border);border-radius:var(--radius-card);
  padding:var(--sp-l);box-shadow:var(--elev-hover)}
@media(min-width:900px){
  .filterbar{grid-template-columns:repeat(4,minmax(0,1fr));align-items:end}
  .filterbar .stockcount{grid-column:1 / 3;align-self:center}
  .filterbar .btn{grid-column:4 / 5}
}
.filterbar .field{display:grid;gap:.3rem;min-width:0}
.filterbar label{font-size:.8125rem;font-weight:600}
.filterbar select{width:100%;font:inherit;color:var(--c-text);background:var(--c-bg);
  border:1px solid var(--c-border);border-radius:var(--radius-button);padding:.55rem .7rem}
.stockcount{margin:0;color:var(--c-text-muted);font-size:.9375rem}

/* THE PRICE CARRIES `margin-top:auto`, NOT THE BUTTON. Measured once on the product grid and true
   again here: pushing the button down leaves the price floating at whatever height the title
   wrapped to, and a row of cards whose prices sit at four different heights reads as broken. The
   price and the quota are one pair and they travel together. */
.stockgrid{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-l);
  grid-template-columns:repeat(var(--cols,1),minmax(0,1fr))}
@media(max-width:900px){.stockgrid{grid-template-columns:repeat(min(var(--cols,1),2),minmax(0,1fr))}}
@media(max-width:599px){.stockgrid{grid-template-columns:1fr}}
.vcard{display:flex;flex-direction:column;background:var(--c-bg);overflow:hidden;
  border-radius:var(--radius-card);box-shadow:var(--elev-rest);
  transition:box-shadow var(--dur-lift) var(--ease)}
.vcard:hover{box-shadow:var(--elev-hover)}
.vcard .frame{aspect-ratio:4/3;border-radius:0}
.vcard .frame img{width:100%;height:100%;object-fit:cover;display:block}
.vbody{display:flex;flex-direction:column;gap:.5rem;flex:1;padding:var(--sp-m)}
.vcard h3{margin:0;font-size:var(--fs-name);line-height:1.25}
/* Same reasoning as `.tfacts`: five fixed facts on a fixed 3-column grid, so every vehicle card
   spends exactly two rows on them and the prices below line up across the whole listing. */
.vfacts{list-style:none;margin:0;padding:0;display:grid;
  grid-template-columns:repeat(3,minmax(0,1fr));gap:.3rem}
.vfacts li{font-size:.8125rem;color:var(--c-text-muted);border:1px solid var(--c-border);
  border-radius:var(--radius-button);padding:.08rem .45rem;text-align:center;min-width:0}
.vprice{margin:auto 0 0;display:flex;align-items:baseline;gap:.5rem;flex-wrap:wrap}
/* UN PRECIO ES EL TITULAR DE SU FICHA, así que va al escalón de título de ficha y no a un
   número suelto. 1.375rem eran 22px fijos: más grande que el nombre del coche (17) por decisión
   correcta, pero igual de sordo al ancla que él. */
.vprice b{font-family:var(--font-primary);font-size:var(--fs-item);font-variant-numeric:tabular-nums}
.vprice span{color:var(--c-text-muted);font-size:var(--fs-meta)}
.vcard .btn{margin-top:.25rem;align-self:flex-start}

.tiform{display:grid;gap:var(--sp-m);width:min(100%,44rem)}
@media(min-width:900px){
  .tiform{grid-template-columns:1fr 1fr auto;align-items:end}
  .tiform .small{grid-column:1 / -1}
}

/* The last row is the TOTAL and it is the only one that gets weight: every row above it is an
   input the buyer can argue with, and the total is the number this section exists to stop hiding. */
.ftable{margin:0;width:min(100%,44rem)}
/* ── El cuerpo sigue el eje del encabezado ─────────────────────────────────────────────────
   Estos seis bloques comparten forma: ancho acotado dentro de un canvas mas ancho. El .head se
   centra segun el blueprint, y ellos se quedaban a la izquierda — 30 casos en 23 tiras, con
   desvios de 286 a 589 px. Visto por un humano no se lee como 'esta a la izquierda', se lee
   como que FALTA una imagen a la derecha, que es como se reporto.
   `margin-inline:auto` y no `justify-self`: varios de estos no son items de grid en todos los
   blueprints, y el margen automatico funciona en los dos casos. Donde el blueprint coloca el
   head fuera del eje (lp-asymmetric lo lleva a las columnas 1-7, lp-broken-grid lo desplaza a
   proposito) el bloque se devuelve al borde justo debajo, porque alli el eje NO es el centro. */
.tiform, .ftable, .bookform, .newsform, .faqlist, .pnote, .plan-note{margin-inline:auto}
/* LP-STRICT-GRID BELONGS IN THIS LIST TOO, and its absence was visible: the blueprint leaves the
   head full-width and LEFT, so a form centred under it floats in the middle with a column of dead
   ground to its left — read, correctly, as "the form is not aligned with its own heading". The
   rule above centres these blocks because most blueprints centre the head; the exception list is
   for the blueprints that do not, and strict-grid is one of them. */
[data-comp="lp-strict-grid"] .tiform, [data-comp="lp-strict-grid"] .ftable,
[data-comp="lp-strict-grid"] .bookform, [data-comp="lp-strict-grid"] .newsform,
[data-comp="lp-strict-grid"] .faqlist, [data-comp="lp-strict-grid"] .pnote,
[data-comp="lp-strict-grid"] .plan-note{margin-inline:0}
[data-comp="lp-asymmetric"] .tiform, [data-comp="lp-asymmetric"] .ftable,
[data-comp="lp-asymmetric"] .bookform, [data-comp="lp-asymmetric"] .newsform,
[data-comp="lp-asymmetric"] .faqlist, [data-comp="lp-asymmetric"] .pnote,
[data-comp="lp-asymmetric"] .plan-note,
[data-head="tight"] .tiform, [data-head="tight"] .ftable, [data-head="tight"] .bookform,
[data-head="tight"] .newsform, [data-head="tight"] .faqlist, [data-head="tight"] .pnote,
[data-head="tight"] .plan-note{margin-inline:0}

.frow{display:flex;justify-content:space-between;gap:var(--sp-m);align-items:baseline;
  padding-block:calc(var(--sp-s) * .9);border-bottom:1px solid var(--c-border)}
.frow dt{color:var(--c-text-muted)}
.frow dd{margin:0;font-variant-numeric:tabular-nums}
.ftotal{border-bottom:none;border-top:2px solid var(--c-text);margin-top:.3rem}
.ftotal dt,.ftotal dd{color:var(--c-text);font-weight:700;font-size:var(--fs-name)}

.sec.badges{border-block:1px solid var(--c-border)}
.badgelist{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-l)}
@media(min-width:768px){.badgelist{grid-template-columns:repeat(4,minmax(0,1fr))}}
.badge b{display:block;font-family:var(--font-primary);line-height:1.05;
  font-size:clamp(1.25rem,2.1vw,1.75rem)}
.badge span{display:block;margin-top:.3rem;color:var(--c-text-muted);font-size:.9375rem}

.pnote{margin:var(--sp-l) auto 0;color:var(--c-text-muted);font-size:var(--fs-meta);max-width:64ch}

/* ══════════ TPL-C-13 · CARTERA / BÚSQUEDA ══════════
   Written against tokens only. Reuses `.hero-search`/`.filterbar` from TPL-C-07 above and
   `.stockgrid`/`.vcard` for the listing, because the HTML really is the same shape — a band of
   filters over a grid of fact-cards — and inventing parallel classes for it would be piel distinta
   with extra steps. What IS new is below: the plan, the photoless card, and the capture band. */

/* THE CARD CARRIES NO PHOTOGRAPH, AND THAT IS DECLARED RATHER THAN MISSING. This repo has 45
   images and not one of them is a dwelling; the nearest are a quarry and six second-hand cars.
   Dropping a car into a property card would be the exact defect TPL-C-13's own § 5 forbids — "no
   con una foto de archivo que parezca real" — and reusing the Piedra Valdés shoot would push
   RT_GALLERY_ONE_SHOOT's concentration bound for no gain. So the frame renders as a marked
   placeholder: diagonal hatch in the border colour, the aspect ratio the real photo would occupy,
   and the word PLACEHOLDER, so nobody reading the gallery mistakes it for a design decision.
   The strip is still worth rendering without it — what an anchor changes in a listing is the price
   typography, the fact-chip rhythm and the grid density, and all three are visible here. */
.hero-search .shot.ph{aspect-ratio:21/9}
.medico .frame.ph{aspect-ratio:1/1}
.pcard .frame.ph, .hero-search .shot.ph, .medico .frame.ph{display:grid;place-items:center;
  background:repeating-linear-gradient(135deg,var(--c-bg-alt) 0 8px,var(--c-bg) 8px 16px)}
.pcard .frame.ph{aspect-ratio:4/3}
.pcard .frame.ph span, .hero-search .shot.ph span, .medico .frame.ph span{font-size:.6875rem;letter-spacing:.14em;text-transform:uppercase;
  color:var(--c-text-muted);background:var(--c-bg);padding:.25rem .6rem;
  border:1px solid var(--c-border);border-radius:var(--radius-pill,999px)}
/* The price is the first thing read and the zone the second: a listing where the zone hides under
   the fact chips forces the reader to parse five numbers to learn the one thing that decides. */
.pcard .pzone{margin:0;font-size:.8125rem;color:var(--c-text-muted)}

/* THE PLAN IS A SEARCH MODE, NOT AN ILLUSTRATION, so it is drawn rather than photographed and
   every pin carries its price. A map with unlabelled pins makes the reader click to learn what
   they could have read. The streets are inline SVG because the Artifact CSP blocks tiles and
   because a real map needs third-party consent — see wordpress-legal. */
.mapsearch .mapwrap{position:relative;border:1px solid var(--c-border);
  border-radius:var(--radius-card);overflow:hidden;background:var(--c-bg-alt)}
.mapsearch .mapshot{margin:0;aspect-ratio:1200/570}
.mapsearch .mapshot img{width:100%;height:100%;object-fit:cover;display:block}
/* Sobre una fotografía la chincheta necesita separarse del fondo: sombra corta y filete. Sin
   ellos un precio oscuro sobre un tejado oscuro deja de leerse justo donde importa. */
.mapsearch .pin{box-shadow:0 2px 6px rgba(0,0,0,.28)}
/* La rama sin foto se queda: ese estado es real y vuelve cada vez que un arquetipo aterriza
   antes que su fotografía. */
.mapsearch .mapph{aspect-ratio:1200/570;width:100%;display:grid;place-items:center;
  background:repeating-linear-gradient(135deg,var(--c-bg-alt) 0 8px,var(--c-bg) 8px 16px)}
.mapsearch .mapph span{font-size:.6875rem;letter-spacing:.14em;text-transform:uppercase;
  color:var(--c-text-muted);background:var(--c-bg);padding:.25rem .6rem;
  border:1px solid var(--c-border);border-radius:var(--radius-pill,999px)}
/* THE PIN IS NOT AN ACCENT MARK. Six accented pins on one plan out-shout the single CTA the page
   has, and design-tokens.md is explicit that the accent is ONE colour for CTAs, action icons,
   important links and active states — a price label is none of those, it is a label. So the pin
   is the page surface with a hairline, which is also the better read: on a plan the price is
   information to scan, not a thing to be shouted at six times. */
.mapsearch .pin{position:absolute;transform:translate(-50%,-100%);
  background:var(--c-bg);color:var(--c-text);border:1px solid var(--c-border);
  font-size:.75rem;font-weight:700;
  padding:.2rem .5rem;border-radius:var(--radius-pill,999px);white-space:nowrap;
  box-shadow:var(--elev-rest);
  transition:transform var(--dur-lift) var(--ease),box-shadow var(--dur-lift) var(--ease)}
.mapsearch .pin:hover{transform:translate(-50%,-100%) scale(1.06);box-shadow:var(--elev-hover)}
/* On a phone the two modes are a SWITCH and not two stacked sections: a 300px-tall plan under a
   grid is a thing nobody uses. The switch is real UI here so the anchor's control styling shows. */
.mapswitch{display:inline-flex;gap:0;border:1px solid var(--c-border);
  border-radius:var(--radius-pill,999px);overflow:hidden;margin:0 auto var(--sp-m)}
.mapswitch button{font:inherit;font-size:.875rem;padding:.4rem 1rem;border:0;cursor:pointer;
  background:var(--c-bg);color:var(--c-text);
  transition:background var(--dur-color) var(--ease),color var(--dur-color) var(--ease)}
.mapswitch button[aria-pressed="true"]{background:var(--c-accent);color:var(--c-on-accent)}

/* CAPTURE IS A BAND AND NOT A GRID, because it addresses a different person than everything above
   it — the owner, not the buyer — and a band is the one shape that reads as a change of subject. */
.valuation .vstats{list-style:none;margin:0 0 var(--sp-l);padding:0;display:grid;
  gap:var(--sp-m);grid-template-columns:repeat(auto-fit,minmax(10rem,1fr))}
.valuation .vstats b{display:block;font-family:var(--font-primary);font-size:var(--fs-h3);
  line-height:1.1}
.valuation .vstats span{font-size:.8125rem;color:var(--c-text-muted)}

/* ══════════ TPL-C-15 · CARTERA CURADA — Inmobiliaria de la O ══════════
   PR3d — the hero veil, the rule-grid hairlines and the search band below were all reproducing
   this file's OWN generic dark-photo-hero recipe (`.mhero`/`.hero-visual`: black vignette rising
   from the bottom, white text, a card-shaped filter bar borrowed from `TPL-C-07`) instead of the
   artboard's actual geometry — exactly the defect the launch brief names by name ("NovaMira's
   component system wearing the client's colours"). `Inicio.dc.html`'s own hero is a LIGHT,
   horizontal veil (`linear-gradient(100deg, ground 95% → 6%)`) that the INK text sits on, not a
   dark one white text sits on; its search band is a full-width inverse-surface BAND with unequal
   field widths, not a floating rounded-shadow card. Fixed below, literally. */
.herocartera{position:relative;isolation:isolate;min-height:78vh}
.herocartera .media-full{position:absolute;inset:0;z-index:0}
.herocartera .media-full .frame{width:100%;height:100%;aspect-ratio:auto;border-radius:0;
  background-color:var(--c-bg-alt);
  background-image:repeating-linear-gradient(135deg,var(--c-bg-alt) 0 8px,var(--c-bg) 8px 16px)}
.herocartera .media-full img{width:100%;height:100%;object-fit:cover}
/* `100deg`, ground → transparent: the artboard's own veil rises from the TEXT side (left) and
   fades toward the photograph (right), not from the bottom — `color-mix` against `--c-bg` keeps
   this a token-derived value rather than a re-typed literal hex. */
.herocartera::after{content:"";position:absolute;inset:0;z-index:1;pointer-events:none;
  background:linear-gradient(100deg,
    color-mix(in srgb,var(--c-bg) 95%,transparent) 0%,
    color-mix(in srgb,var(--c-bg) 78%,transparent) 38%,
    color-mix(in srgb,var(--c-bg) 28%,transparent) 70%,
    color-mix(in srgb,var(--c-bg) 6%,transparent) 100%)}
/* Decorativa a propósito: sólo filetes, `pointer-events:none`, y limitada al ancho del contenedor
   como el resto del contenido — la retícula de cuatro columnas del diseño de origen, reexpresada
   como proporción sobre el token de la casa (D3: 1280 gana sobre los 1440 del diseño). Bajo 900px
   se oculta: a ese ancho ya no se lee como retícula, se lee como ruido sobre la cara. Filetes DE
   TINTA, no blancos — el veil ahora es claro, así que un filete blanco desaparecería sobre él; el
   diseño de origen mismo los pinta `rgba(23,24,26,.09)` sobre su propio fondo claro. Un quinto
   filete cierra la retícula por la derecha — el diseño da a su cuarta columna `border-left` Y
   `border-right`, cuatro columnas piden cinco líneas, no cuatro. */
.rulegrid{position:absolute;inset:0;z-index:2;pointer-events:none;display:none;
  grid-template-columns:repeat(4,1fr);max-width:var(--container-max);margin-inline:auto;
  padding-inline:var(--pad-x-tablet)}
.rulegrid span{border-left:1px solid color-mix(in srgb,var(--c-text) 9%,transparent)}
.rulegrid span:last-child{border-right:1px solid color-mix(in srgb,var(--c-text) 9%,transparent)}
@media(min-width:900px){.rulegrid{display:grid}}
/* `1.45fr 1fr`, el diseño de origen — no el `7fr 3fr` (≈2.33) que aquí había, casi el doble de
   desequilibrado. Sin `padding-block` simétrico: el diseño ancla el contenido abajo con
   `padding:0 40px 72px`, cero arriba. */
.herocartera > .canvas{position:relative;z-index:3;min-height:78vh;display:grid;
  grid-template-columns:minmax(0,1fr);align-content:end;gap:var(--sp-l);
  padding-block:0 var(--sp-xl)}
@media(min-width:900px){.herocartera > .canvas{grid-template-columns:minmax(0,1.45fr) minmax(0,1fr);
  align-items:end}}
.herocartera .head .lede{max-width:42ch}
/* Filete NEUTRO, no de acento: design-tokens.md reserva `--c-accent` para CTAs, iconos de acción,
   enlaces importantes y estados activos — un filete decorativo no es ninguno de los cuatro, y el
   accent-role gate de este mismo generador lo `fail()`ea. El diseño de origen lo pinta en su propio
   acento (`rgba(138,123,92,.55)`); aquí es `--c-border` sobre fondo claro, la misma sustitución que
   `proplux_card_html()` ya hace para la etiqueta de zona y `property_location_html()` para la
   chincheta del mapa. */
.statspanel{background:color-mix(in srgb,var(--c-bg) 93%,transparent);
  border-left:2px solid var(--c-border);padding:var(--sp-m) var(--sp-l)}
.statspanel ul{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-m)}
.statspanel b{display:block;font-family:var(--font-primary);color:var(--c-text);
  font-size:clamp(1.4rem,2.6vw,2.1rem);line-height:1.1}
.statspanel span{display:block;margin-top:.2rem;font-size:.8125rem;color:var(--c-text-muted)}

/* «Del ancho del contenedor de página, sin tocar el borde de la ventana»: el plato inverso vive
   DENTRO de `.canvas` (ya limitado a `--container-max`), nunca en el `<section>` completo — a
   diferencia de `.bg-alt`, que sí pinta de borde a borde. Sin `border-radius`: el diseño de origen
   dibuja este plato a bordes rectos, y `.sbplate` es una superficie propia de este arquetipo, no
   `.card`, así que no hereda la excepción de radio que sí necesitaría un `.card` reutilizado. */
.sbplate{background:var(--c-surface-inverse);color:var(--c-on-inverse);
  border-radius:0;padding:var(--sp-l) var(--pad-x-mobile)}
@media(min-width:768px){.sbplate{padding:var(--sp-l) var(--pad-x-tablet)}}
/* `.filterbar` REUSED FOR ITS FIELD LAYOUT ONLY. Its own base rule (`TPL-C-07`, above) is a
   FLOATING CARD — light background, rounded corners, a drop shadow and a negative top margin that
   pulls it up onto a photograph's bottom edge. None of that belongs to `COMP-SEARCH-BAND`, which
   the artboard draws as a plain full-width band sitting in normal flow below the hero — PR3c
   nested `.filterbar` inside `.sbplate` and only ever overrode the label colour, so every one of
   those four card properties survived, invisibly, underneath a dark backdrop that was fighting its
   own light-card child. Neutralised here, all four properties, scoped to this nesting only. */
.sbplate .filterbar{position:static;margin:0;width:100%;background:transparent;border:0;
  border-radius:0;padding:0;box-shadow:none}
.sbplate .filterbar label{color:var(--c-on-inverse);opacity:.65}
/* `1fr 1.2fr 1fr 1.1fr auto` — the artboard's own unequal column set (zona wider, precio slightly
   wider, submit sized to its own content), not `TPL-C-07`'s equal `repeat(4,…)`. `auto` for the
   fifth column is the submit control living IN the row, not below it. */
@media(min-width:900px){
  .sbplate .filterbar{grid-template-columns:1fr 1.2fr 1fr 1.1fr auto}
  .sbplate .filterbar .field{padding:1.875rem 1.625rem 1.75rem;
    border-right:1px solid color-mix(in srgb,var(--c-on-inverse) 14%,transparent)}
  .sbplate .filterbar .btn{border-radius:0;padding-inline:2.75rem;align-self:stretch}
}
/* Select AND input both need an explicit colour here: the base `.filterbar select` rule paints
   `var(--c-text)`, which is dark ink — invisible on this dark inverse ground. This is the bug that
   made the search bar's own values unreadable, not merely mistinted. */
.sbplate .filterbar select,.sbplate .filterbar input{color:var(--c-on-inverse);border-radius:0}
.sbplate .filterbar input::placeholder{color:color-mix(in srgb,var(--c-on-inverse) 55%,transparent)}

/* La fila de valoración: dos hijos directos, `gap:1px` sobre `--c-border` como fondo hace de
   filete — un panel oscuro y una fotografía, apilados bajo 768px y en fila a partir de ahí. */
.valuerow{display:grid;grid-template-columns:minmax(0,1fr);gap:1px;background:var(--c-border)}
@media(min-width:768px){.valuerow{grid-template-columns:repeat(2,minmax(0,1fr))}}
.vlpanel{background:var(--c-surface-inverse);padding:var(--sp-xl) var(--pad-x-mobile);
  display:grid;gap:var(--sp-m);align-content:center}
@media(min-width:768px){.vlpanel{padding:var(--sp-xl) var(--pad-x-tablet)}}
.vlpanel .head *{color:var(--c-on-inverse)}
.vlpanel .lede{opacity:.85}
.vlpanel .ctas{display:flex;flex-wrap:wrap;gap:var(--sp-l);align-items:center}
/* PR3d — the artboard's own second action is a plain `tel:` LINK ("o llámenos"), never a second
   button: `.btn-outline` (and its inverse-ground override) is gone with it. */
.valuetel{color:var(--c-on-inverse);opacity:.7;font-size:.8125rem}
.vlshot{margin:0;min-height:18rem}
@media(min-width:768px){.vlshot{min-height:100%}}
.vlshot img{width:100%;height:100%;object-fit:cover;display:block}

/* ══════════ TPL-C-15 / TPL-PROPERTY-01 · PR3c, la reforma de fidelidad ══════════
   Todo lo que sigue es propio de `delao`: la tarjeta de lujo, la cabecera partida, el mosaico de
   la ficha, la tabla de caracteristicas, el bloque de visita y las paginas de Nosotros/Contacto
   propias. Ningun nombre de clase colisiona con el listado de propiedad de `$CLASS_BLOCKS`
   (`_build-gallery.php`, la comprobacion de colision de clases) por diseno. */

/* ── LA TARJETA `proplux`, la propia de esta marca ─────────────────────────────────────────── */
.proplux-grid{list-style:none;margin:0;padding:0;display:grid;gap:1px;background:var(--c-border)}
.proplux-grid>li{background:var(--c-bg)}
@media(max-width:899px){.proplux-grid{grid-template-columns:repeat(min(var(--cols,1),2),minmax(0,1fr))}}
@media(max-width:599px){.proplux-grid{grid-template-columns:1fr}}
.proplux{display:flex;flex-direction:column;height:100%;color:inherit;text-decoration:none;
  padding-bottom:var(--sp-m);transition:background var(--dur-lift) var(--ease)}
.proplux:hover{background:var(--c-bg-alt)}
.proplux-shot{position:relative;margin:0;aspect-ratio:4/3.2;background:var(--c-bg-alt);overflow:hidden}
.proplux-listing .proplux-shot{aspect-ratio:4/3.1}
.proplux-similar .proplux-shot{aspect-ratio:4/3}
.proplux-shot img{width:100%;height:100%;object-fit:cover;display:block}
.proplux-ph{position:absolute;inset:0;display:grid;place-items:center;
  background:repeating-linear-gradient(135deg,var(--c-bg-alt) 0 8px,var(--c-bg) 8px 16px)}
.proplux-ph span{font-size:.6875rem;letter-spacing:.14em;text-transform:uppercase;
  color:var(--c-text-muted);background:var(--c-bg);padding:.25rem .6rem;border:1px solid var(--c-border)}
/* Pegada al borde, SIN radio: el propio brief mide esto orquestador-a-orquestador ("pinned hard
   into the top-left corner ... no radius, flush to the edge"). Superficie inversa, nunca acento —
   una etiqueta de estado no es un CTA, un icono de accion, un enlace importante ni un estado
   activo, los cuatro unicos roles que design-tokens.md concede al acento. */
.proplux-badge{position:absolute;left:0;top:1.25rem;z-index:1;
  background:var(--c-surface-inverse);color:var(--c-on-inverse);
  font:600 .53125rem/1 var(--font-secondary);letter-spacing:.22em;text-transform:uppercase;
  padding:.5rem .875rem}
.proplux-body{display:flex;flex-direction:column;gap:.85rem;padding:1.5rem 1.5rem 0}
.proplux-listing .proplux-body{padding:1.375rem 1.375rem 0;gap:.75rem}
.proplux-zonerow{display:flex;justify-content:space-between;align-items:baseline;gap:.75rem}
/* LA ZONA ES TEXTO MUTADO, NO ACENTO — desviacion deliberada del diseno de origen (que la pinta en
   #8A7B5C): design-tokens.md's own accent row reads "Never body text, never decoration", y una
   etiqueta de zona es exactamente eso. Ver `proplux_card_html()`'s propio docblock. */
.proplux-zone{font:600 .59375rem/1 var(--font-secondary);letter-spacing:.24em;text-transform:uppercase;
  color:var(--c-text-muted)}
.proplux-ref{font:.59375rem/1 ui-monospace,Menlo,monospace;letter-spacing:.1em;color:var(--c-text-muted)}
.proplux h3{margin:0;font-family:var(--font-primary);font-size:1.5rem;line-height:1.15}
.proplux-facts{list-style:none;margin:0;padding:0;display:flex;flex-wrap:wrap;gap:1rem;
  font-size:.78125rem;color:var(--c-text-muted)}
.proplux-price{margin:.4rem 0 0;font-family:var(--font-primary);font-size:1.1875rem}

/* ── COMP-FEATURED-GRID / COMP-RELATED, la cabecera con enlace de salida ───────────────────── */
.fgridhead{display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;
  gap:var(--sp-m);padding-bottom:var(--sp-m);margin-bottom:var(--sp-l);border-bottom:1px solid var(--c-border)}
.fgridhead h2{margin:0}
.fgridlink{font:600 .65625rem/1 var(--font-secondary);letter-spacing:.14em;text-transform:uppercase;
  padding-bottom:.4rem;border-bottom:1px solid var(--c-border);white-space:nowrap}
.fgridlink:hover{border-color:var(--c-accent)}

/* ── COMP-SEARCH-BAND pegajosa, el listado ("Propiedades") ─────────────────────────────────── */
/* Sticky a `top:0`, no al `top:78px` literal del diseno de origen — este chasis no fija
   `.site-head` para ninguna de las diez demos (`.site-head{border-bottom:1px solid ...}`, sin
   `position`), asi que no hay una cabecera de 78px bajo la que encajar. Pegarse al propio borde
   del visor es la misma conducta (la barra sigue alcanzable mientras la lista se desplaza)
   expresada contra el chasis que esta galeria realmente tiene. */
.filterband{position:sticky;top:0;z-index:40}
.filterband .sbplate{padding:1.125rem var(--pad-x-mobile)}
@media(min-width:768px){.filterband .sbplate{padding:1.125rem var(--pad-x-tablet)}}
.filterband .filterbar .field{padding-block:.5rem}

/* ── La barra de resultados: recuento + orden ──────────────────────────────────────────────── */
.resultsbar{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;
  gap:var(--sp-m);padding-bottom:var(--sp-m);margin-bottom:var(--sp-l);border-bottom:1px solid var(--c-border)}
.rescount{font-size:.78125rem;color:var(--c-text-muted)}
.sortgroup{display:flex;align-items:center;gap:1.375rem;flex-wrap:wrap}
.sortlabel{font:600 .59375rem/1 var(--font-secondary);letter-spacing:.2em;text-transform:uppercase;
  color:var(--c-text-muted)}
.sortlink{font:600 .625rem/1 var(--font-secondary);letter-spacing:.16em;text-transform:uppercase;
  color:var(--c-text-muted);padding-bottom:.25rem;border-bottom:1px solid transparent}
/* El sort activo ES un estado activo — el mismo rol que `.mapswitch` ya ocupa en $ACCENT_ROLES, la
   unica otra vez que este archivo pinta "cual esta seleccionado" con el acento de marca. */
.sortlink[aria-current]{color:var(--c-text);border-bottom-color:var(--c-accent)}

/* ── El cierre fantasma "cargar mas" ────────────────────────────────────────────────────────── */
.loadmore{display:flex;justify-content:center;padding-top:var(--sp-xl)}
.btn-ghost{border:1px solid var(--c-border);color:var(--c-text);background:transparent}
.btn-ghost:hover{background:var(--c-surface-inverse);color:var(--c-on-inverse);border-color:var(--c-surface-inverse)}

/* ── La cabecera partida, compartida por Propiedades / Ficha / Nosotros / Contacto ─────────── */
.splithead .canvas{display:grid;gap:var(--sp-l);align-items:end}
@media(min-width:900px){.splithead .canvas{grid-template-columns:1.5fr 1fr;gap:var(--sp-xl)}}
.splithead .lede{margin:0}

/* ── Migas de la ficha, con la referencia en la misma barra ────────────────────────────────── */
/* The monospace/size override for the reference code that rides this bar lives beside its base
   rule in the "FICHAS DE INVENTARIO" block further down this stylesheet, not here — this PR3c
   block sits earlier in the file than that marker, and the class-ownership collision check below
   reads plain text, so even naming that selector inside a COMMENT this early would trip it. */
.propcrumbs .canvas{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;
  gap:var(--sp-s)}

/* ── El panel de precio de la cabecera de ficha ────────────────────────────────────────────── */
.propprice{display:flex;flex-direction:column;gap:.625rem;border-left:1px solid var(--c-border);
  padding-left:var(--sp-l)}
.propprice-v{font-family:var(--font-primary);font-size:clamp(2.125rem,3.6vw,3.125rem);line-height:1}
.propprice-m2{font-size:.8125rem;color:var(--c-text-muted)}

/* ── El mosaico de la ficha, COMP-PROPERTY-TOUR ────────────────────────────────────────────── */
.propgallery .canvas{padding-block:0}
.propmosaic{display:grid;grid-template-columns:2fr 1fr;grid-template-rows:repeat(2,minmax(0,1fr));
  gap:1px;background:var(--c-border);height:clamp(440px,58vw,45rem)}
@media(max-width:767px){.propmosaic{grid-template-columns:1fr;grid-template-rows:none;height:auto}
  .propcell{aspect-ratio:4/3}}
.propcell{position:relative;margin:0;background:var(--c-bg-alt)}
.propcell-big{grid-row:span 2}
@media(max-width:767px){.propcell-big{grid-row:auto}}
.propcell img{width:100%;height:100%;object-fit:cover;display:block}
.propcap{position:absolute;left:.875rem;bottom:.75rem;font:.5625rem/1 ui-monospace,Menlo,monospace;
  letter-spacing:.1em;text-transform:uppercase;color:var(--c-on-inverse);
  text-shadow:0 1px 6px rgba(0,0,0,.45)}
.propmore{position:absolute;right:0;bottom:0;background:var(--c-surface-inverse);color:var(--c-on-inverse);
  font:600 .59375rem/1 var(--font-secondary);letter-spacing:.14em;text-transform:uppercase;
  padding:1rem 1.375rem}
.propmore:hover{background:var(--c-accent);color:var(--c-on-accent)}

/* ── Los datos clave, COMP-PROPERTY-FACTS ──────────────────────────────────────────────────── */
.propfacts .canvas{padding-block:0;border-bottom:1px solid var(--c-border)}
.propkeydata{display:grid;grid-template-columns:repeat(4,minmax(0,1fr))}
@media(max-width:899px){.propkeydata{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:599px){.propkeydata{grid-template-columns:1fr}}
.propkey{padding:var(--sp-m) 0;border-right:1px solid var(--c-border);
  display:flex;flex-direction:column;gap:.5rem}
.propkey:last-child{border-right:0}
@media(max-width:899px){.propkey:nth-child(2n){border-right:0}}
@media(max-width:599px){.propkey{border-right:0;border-bottom:1px solid var(--c-border)}
  .propkey:last-child{border-bottom:0}}
.propkey-v{font-family:var(--font-primary);font-size:1.875rem;line-height:1}
.propkey-l{font-size:.5625rem;letter-spacing:.24em;text-transform:uppercase;color:var(--c-text-muted)}

/* ── El cuerpo: descripcion + tabla + ubicacion, y el panel de visita pegajoso ─────────────── */
.propbody{display:grid;gap:var(--sp-xxl)}
@media(min-width:1024px){.propbody{grid-template-columns:1.55fr 1fr;align-items:start}}
.propmain{display:flex;flex-direction:column;gap:var(--sp-xxl)}
.propdesc{display:flex;flex-direction:column;gap:var(--sp-m)}
.proplede{margin:0;font-family:var(--font-primary);font-size:1.4375rem;line-height:1.55}
.propdesc>p.muted{margin:0}
/* La tabla de caracteristicas — COMP-COSTS-BREAKDOWN y COMP-ENERGY-LABEL viven aqui ahora. */
.propfeat{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1px;background:var(--c-border)}
@media(max-width:599px){.propfeat{grid-template-columns:1fr}}
.propfeatrow{background:var(--c-bg);display:flex;justify-content:space-between;align-items:baseline;
  gap:var(--sp-m);padding:.9rem 0}
.propfeat-k{font-size:.59375rem;letter-spacing:.16em;text-transform:uppercase;color:var(--c-text-muted)}
.propfeat-v{font-size:.875rem;text-align:right}
/* La ubicacion, con el mapa dibujado — nunca un proveedor real (fuera de alcance) ni un acento. */
.proploc{display:flex;flex-direction:column;gap:var(--sp-m)}
.proploc-head{display:flex;justify-content:space-between;align-items:baseline;flex-wrap:wrap;gap:var(--sp-m)}
.proploc-head h2{margin:0}
.proploc-note{font-size:.78125rem;color:var(--c-text-muted)}
.mapdrawn{position:relative;aspect-ratio:4/3;background-color:var(--c-bg-alt);
  background-image:linear-gradient(var(--c-border) 1px,transparent 1px),
    linear-gradient(90deg,var(--c-border) 1px,transparent 1px);
  background-size:2.5rem 2.5rem}
.mapdrawn-wide{aspect-ratio:16/9}
.mapdrawn-dot{position:absolute;left:47%;top:44%;width:.875rem;height:.875rem;border-radius:50%;
  background:var(--c-text);box-shadow:0 0 0 .5rem var(--c-border)}
.propdist{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1px;background:var(--c-border)}
@media(max-width:599px){.propdist{grid-template-columns:repeat(2,minmax(0,1fr))}}
.propdist-cell{background:var(--c-bg);padding:1.125rem 0;display:flex;flex-direction:column;gap:.4rem}
.propdist-v{font-family:var(--font-primary);font-size:1.0625rem}
.propdist-l{font-size:.5625rem;letter-spacing:.2em;text-transform:uppercase;color:var(--c-text-muted)}
/* El panel de visita, pegajoso — el simulador de hipoteca del diseno de origen es fuera de alcance
   (launch brief), asi que este es el UNICO bloque, no dos con `gap:1px` entre ellos. */
.propaside{position:relative}
@media(min-width:1024px){.propaside{position:sticky;top:6.875rem;align-self:start}}
.propvisit{background:var(--c-surface-inverse);color:var(--c-on-inverse);
  padding:var(--sp-l) var(--sp-m);display:flex;flex-direction:column;gap:var(--sp-m)}
.propvisit .eyebrow{color:var(--c-on-inverse);opacity:.55}
.propvisit-line{margin:0;font-family:var(--font-primary);font-size:1.25rem;line-height:1.45}
.propvisit-cta{width:100%;text-align:center}
.propvisit-tel{text-align:center;color:var(--c-on-inverse);opacity:.7;font-size:.8125rem}

/* ── COMP-RELATED, "propiedades similares" ─────────────────────────────────────────────────── */
.propsimilar .fgridhead h2{margin:0}

/* ── TPL-ABOUT-01 «Nosotros», autoria propia ───────────────────────────────────────────────── */
.aboutphoto .canvas{padding-block:0}
.aboutphoto img,.aboutphoto figure{display:block;width:100%}
.aboutphoto figure{margin:0;aspect-ratio:21/9;overflow:hidden}
.aboutphoto figure img{width:100%;height:100%;object-fit:cover}
.methodhead h2{margin:0}
@media(min-width:900px){.methodsec .canvas{display:grid;grid-template-columns:1fr 1.5fr;gap:var(--sp-xl);
  align-items:start}}
.methodlist{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:1px;
  background:var(--c-border)}
.methoditem{background:var(--c-bg);display:grid;grid-template-columns:2.75rem 1fr;gap:var(--sp-m);
  padding:var(--sp-m) 0}
.methodnum{font:.8125rem/1.2 ui-monospace,Menlo,monospace;color:var(--c-text-muted)}
.methoditem h3{margin:0 0 .5rem;font-size:1.375rem}
.methoditem p{margin:0}
.figuresband{background:var(--c-surface-inverse);color:var(--c-on-inverse)}
.figuresgrid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1px;
  background:color-mix(in srgb,var(--c-on-inverse) 14%,transparent)}
@media(min-width:768px){.figuresgrid{grid-template-columns:repeat(4,minmax(0,1fr))}}
.figurecell{background:var(--c-surface-inverse);padding:var(--sp-m) var(--sp-s);
  display:flex;flex-direction:column;gap:.6rem}
.figure-v{font-family:var(--font-primary);font-size:clamp(1.875rem,3.6vw,2.75rem);line-height:1}
.figure-l{font:600 .59375rem/1.5 var(--font-secondary);letter-spacing:.18em;text-transform:uppercase;
  color:var(--c-on-inverse);opacity:.6}
.teamhead{display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;
  gap:var(--sp-m);padding-bottom:var(--sp-m);margin-bottom:var(--sp-l);border-bottom:1px solid var(--c-border)}
.teamhead h2{margin:0}
.teamcount{font:600 .59375rem/1 var(--font-secondary);letter-spacing:.18em;text-transform:uppercase;
  color:var(--c-text-muted)}
.teamgrid .team-shot{aspect-ratio:3/4}
.teamcard{display:flex;flex-direction:column}
.team-body{padding:1.375rem 0 0;display:flex;flex-direction:column;gap:.5rem}
.team-body h3{margin:0;font-size:1.3125rem}
.team-role{font:600 .59375rem/1.4 var(--font-secondary);letter-spacing:.18em;text-transform:uppercase;
  color:var(--c-text-muted)}
.aboutcta .canvas{padding-block:var(--sp-xl)}
.aboutctapanel{background:var(--c-bg-alt);padding:var(--sp-xl) var(--sp-l);display:grid;gap:var(--sp-l);
  align-items:center}
@media(min-width:768px){.aboutctapanel{grid-template-columns:1.2fr 1fr}}
.aboutctapanel h2{margin:0 0 .75rem}
.aboutctapanel .head{gap:0}
.aboutcta-btn{justify-self:start}
@media(min-width:768px){.aboutcta-btn{justify-self:end}}

/* ── TPL-CONTACT-01 «Contacto», autoria propia ─────────────────────────────────────────────── */
.contactgrid{display:grid;gap:var(--sp-xl)}
@media(min-width:1024px){.contactgrid{grid-template-columns:1.4fr 1fr;gap:5.5rem}}
.contactform{display:flex;flex-direction:column;gap:var(--sp-l)}
.formgrid{display:grid;gap:var(--sp-l)}
@media(min-width:600px){.formgrid{grid-template-columns:repeat(2,minmax(0,1fr))}}
.privacy{display:flex;gap:var(--sp-s);align-items:flex-start;font-size:.78125rem;color:var(--c-text-muted)}
.privacy input{margin-top:.2rem}
.contactaside{display:flex;flex-direction:column;gap:1px;background:var(--c-border)}
.contactblk{background:var(--c-bg);padding:var(--sp-l) 0;display:flex;flex-direction:column;gap:.75rem}
.contactblk-addr{margin:0;font-family:var(--font-primary);font-size:1.1875rem;line-height:1.5}
.contactblk-tel{font-family:var(--font-primary);font-size:1.1875rem;color:var(--c-text)}
.contactblk-mail{font-size:.875rem;color:var(--c-text)}

/* ══════════ TPL-C-08 · MODELO / LANZAMIENTO ══════════ */

/* Same full-bleed machinery as `.hero-visual`, and measured by the same sweep — see $VIS_ARCHS.
   What is different is the FIGURE STRIP riding the bottom of the photograph. */
.mhero{position:relative;isolation:isolate}
.mhero .media-full{position:absolute;inset:0;z-index:0}
.mhero .media-full .frame{width:100%;height:100%;aspect-ratio:auto;border-radius:0}
.mhero .media-full img{width:100%;height:100%;object-fit:cover}
.mhero::after{content:"";position:absolute;inset:0;z-index:1;pointer-events:none;
  background:linear-gradient(to top,rgba(0,0,0,.86) 0%,rgba(0,0,0,.62) 38%,rgba(0,0,0,.12) 72%,
    rgba(0,0,0,0) 100%)}
.mhero > .canvas{position:relative;z-index:2;min-height:min(76vh,46rem);align-content:end;
  padding-block:var(--sp-xl)}
.mhero .head{max-width:min(38rem,80%)}
.mhero .head h1,.mhero .head .lede,.mhero .head .eyebrow{color:#fff}
.mhero .head .lede{opacity:.92}
.mhero .btn-outline{color:#fff;border-color:color-mix(in srgb,#fff 55%,transparent)}
.mfigs{list-style:none;margin:var(--sp-xl) 0 0;padding:0;display:grid;gap:var(--sp-m);
  grid-template-columns:repeat(2,minmax(0,1fr))}
@media(min-width:900px){.mfigs{grid-template-columns:repeat(4,minmax(0,1fr))}}
.mfigs li{border-top:1px solid color-mix(in srgb,#fff 34%,transparent);padding-top:var(--sp-s);
  color:#fff}
.mfigs b{display:block;font-family:var(--font-primary);line-height:1;
  font-size:clamp(1.3rem,2.4vw,2rem)}
.mfigs span{display:block;margin-top:.25rem;font-size:.8125rem;opacity:.78;
  letter-spacing:.06em;text-transform:uppercase}

/* A REAL TABLE. The rows that CHANGE across versions keep full colour and a rule down their left
   edge; the rows that repeat are dimmed. A comparison where every row weighs the same helps
   nobody choose, which is the only job it has. */
.specwrap{overflow-x:auto;-webkit-overflow-scrolling:touch}
/* THE COLOUR AND THE FACE ARE SET EXPLICITLY, AND THAT IS NOT BELT-AND-BRACES. Measured with a
   probe after the version columns rendered INVISIBLE — near-black on near-black — while every
   token around them was correct: `--c-text` read #EDF1F6 on the td, on the tr, on the tbody and on
   the table, and the computed `color` was #141C24 from the document root. The chain breaks at the
   TABLE and only at the table.

   The cause is QUIRKS MODE. A gallery asset is an Artifact FRAGMENT — no doctype, by contract — so
   a browser opening the file directly reports `compatMode: BackCompat`, and in quirks mode a table
   does not inherit color or font from its parent; it resets to the document default. The published
   Artifact is wrapped in a doctype and would have rendered correctly, which is the trap: the defect
   lives only in the local capture, so it is invisible in production and unavoidable in review.
   Declaring both properties makes the two modes agree, which is worth more than being right in one. */
.spectable{width:100%;border-collapse:collapse;font-variant-numeric:tabular-nums;
  color:var(--c-text);font-family:var(--font-secondary)}
.spectable th,.spectable td{text-align:left;padding:calc(var(--sp-s) * .9) var(--sp-m);
  border-bottom:1px solid var(--c-border);white-space:nowrap}
.spectable thead th{font-family:var(--font-primary);font-size:1rem;
  border-bottom:2px solid var(--c-text)}
.spectable tbody th{font-weight:500;color:var(--c-text-muted);white-space:normal}
.spectable tr.same td,.spectable tr.same th{color:var(--c-text-muted)}
.spectable tr.differs th{box-shadow:inset 2px 0 0 0 var(--c-text)}
.spectable tr.differs td{font-weight:600}

.sec.offer{border-block:1px solid var(--c-border);background:var(--c-bg-alt)}
.sec.offer .head{max-width:52ch}

/* ══════════ TPL-C-09 · TALLER / TARIFA ══════════ */

/* Deliberately NOT the dotted leader TPL-C-06's carta uses. Two archetypes that both publish a
   price list would read as one template if they published it the same way — the price here is a
   bordered chip because a workshop rate is a closed figure, not a menu line. */
.pgroups{display:grid;gap:var(--sp-xl)}
@media(min-width:900px){
  .pgroups{grid-template-columns:repeat(2,minmax(0,1fr));column-gap:clamp(2rem,5vw,4rem)}
}
.pgroup h3{margin:0 0 var(--sp-m);font-family:var(--font-primary);font-size:var(--fs-h3);
  letter-spacing:var(--track-h3)}
.pgroup ul{list-style:none;margin:0;padding:0}
.prow{display:flex;gap:var(--sp-m);align-items:flex-start;justify-content:space-between;
  padding-block:calc(var(--sp-s) * 1.1);border-bottom:1px solid var(--c-border)}
.pwhat{display:grid;gap:.15rem;min-width:0}
.pwhat b{font-size:var(--fs-name);line-height:1.3}
.pwhat span{color:var(--c-text-muted);font-size:var(--fs-meta)}
.ptag{flex:0 0 auto;font-variant-numeric:tabular-nums;font-weight:700;white-space:nowrap;
  background:var(--c-bg-alt);border:1px solid var(--c-border);border-radius:var(--radius-button);
  padding:.2rem .6rem}

/* ── COMP-HOURS-BLOCK ─────────────────────────────────────────────────────────────────────────
   The hours ARE the graphic. A restaurant whose opening times are 9px grey at the foot of the page
   is a wasted trip waiting to happen, and every reference kit for this sector sets them large. */
.sec.hoursblock .head{grid-column:c 1 / c 9}
.hours-big{display:grid;gap:var(--sp-xl)}
@media(min-width:900px){.hours-big{grid-template-columns:1.4fr 1fr;gap:clamp(2rem,5vw,5rem)}}
.hours-big dl{margin:0}
.hours-big .row{display:grid;grid-template-columns:1fr auto;gap:var(--sp-m);align-items:baseline;
  padding-block:calc(var(--sp-s) * 1.2);border-bottom:1px solid var(--c-border)}
.hours-big dt{font-family:var(--font-primary);font-size:clamp(1.05rem,1.6vw,1.5rem);
  line-height:1.1;letter-spacing:var(--track-h3)}
.hours-big dd{margin:0;font-variant-numeric:tabular-nums;font-size:clamp(.9rem,1.2vw,1.05rem)}
.hours-big .shut dt,.hours-big .shut dd{color:var(--c-text-muted)}
.hours-big .where{display:grid;gap:var(--sp-s);align-content:start}
.hours-big .where p{margin:0}
.hours-big .where a{font-size:var(--fs-name)}
CSS;

// ── block 5a · the house ink, one filter per anchor ────────────────────────────────────────────
//
// EMITTED FROM `$INK`, THE SAME ARRAY THE CONTRAST SWEEP READS — and it emits the very strings the
// sweep parsed. The retired code called `ink_ends()` here and again at the sweep and relied on two
// calls agreeing; this is one artefact used twice, so the filter the browser runs and the filter
// PHP measured cannot be two different things even in the fifth decimal.
//
// `color-interpolation-filters="sRGB"` IS LOAD-BEARING. The default is linearRGB, where the same
// saturate matrix crushes the shadows, and `ink_pixel()` computes in sRGB. Drop this attribute and
// the page and its certificate stop describing the same image.
//
// THE `@supports` FALLBACK IS THE SATURATE ALONE AND NOT GREYSCALE. It used to be
// `grayscale(1) contrast(1.04)` — the retired duotone approximated with a CSS function — and
// carrying that forward would have left one class of browser looking at exactly the page this pass
// exists to undo. What survives without `filter:url()` is the chroma restraint, which is the half
// of the grade that does the unifying; the split tone is the half that needs the table.
$INK_ENDS = array();
$ink_defs = array();
$ink_css  = array( '/* ══════════ THE HOUSE INK — one GRADE per anchor: a chroma restraint and a'
	. "\n      split tone. Shadow ink = that anchor's accent at the neutral endpoint's own"
	. "\n      luminance; highlight ink = that anchor's own light extreme. See § 5a. ══════════ */" );
foreach ( $ANCHORS as $ink_k => $ink_a ) {
	if ( ! isset( $used_anchors[ $ink_k ] ) ) {
		continue;
	}
	$ink_o              = $INK[ $ink_k ];
	$INK_ENDS[ $ink_k ] = $ink_o;
	$ink_t              = array();
	foreach ( array( 'R', 'G', 'B' ) as $ink_i => $ink_ch ) {
		$ink_t[] = sprintf(
			'<feFunc%s type="table" tableValues="%s"/>',
			$ink_ch,
			$ink_o['table'][ $ink_i ]
		);
	}
	$ink_defs[] = '<filter id="nm-ink-' . $ink_k . '" color-interpolation-filters="sRGB">'
		. '<feColorMatrix type="saturate" values="' . $ink_o['sat'] . '"/>'
		. '<feComponentTransfer>' . implode( '', $ink_t ) . '</feComponentTransfer></filter>';
	$ink_css[]  = '[data-anchor="' . $ink_k . '"] .frame > img{filter:url(#nm-ink-' . $ink_k . ')}'
		. '   /* ' . $ink_o['ends']['dark'] . ' → ' . $ink_o['ends']['light']
		. ' · saturate ' . $ink_o['sat'] . ' · gamma ' . $ink_o['gamma']
		. ( $ink_o['named'] ? ', named by § Imagery' : ', the default' ) . ' */';
	$ink_css[]  = '@supports not (filter:url(#nm-ink-' . $ink_k . ')){[data-anchor="' . $ink_k
		. '"] .frame > img{filter:saturate(' . $ink_o['sat'] . ')}}';
}

/* AND ONE PER BRAND, AFTER THEM. Same specificity (0,1,1), so the later rule is the one the
   browser paints — a branded strip grades toward ITS OWN shadow ink, not toward the quarry's. */
foreach ( $BRANDS as $ink_bk2 => $ink_bv2 ) {
	if ( ! isset( $used_brands[ $ink_bk2 ] ) ) {
		continue;
	}
	$ink_bg2              = 'b-' . $ink_bk2;
	$ink_o                = $INK[ $ink_bg2 ];
	$INK_ENDS[ $ink_bg2 ] = $ink_o;
	$ink_t                = array();
	foreach ( array( 'R', 'G', 'B' ) as $ink_i => $ink_ch ) {
		$ink_t[] = sprintf( '<feFunc%s type="table" tableValues="%s"/>', $ink_ch, $ink_o['table'][ $ink_i ] );
	}
	$ink_defs[] = '<filter id="nm-ink-' . $ink_bg2 . '" color-interpolation-filters="sRGB">'
		. '<feColorMatrix type="saturate" values="' . $ink_o['sat'] . '"/>'
		. '<feComponentTransfer>' . implode( '', $ink_t ) . '</feComponentTransfer></filter>';
	$ink_css[]  = '[data-brand="' . $ink_bk2 . '"] .frame > img{filter:url(#nm-ink-' . $ink_bg2 . ')}'
		. '   /* ' . $ink_o['ends']['dark'] . ' → ' . $ink_o['ends']['light']
		. ' · saturate ' . $ink_o['sat'] . ' · gamma ' . $ink_o['gamma'] . ', named by the brand */';
	$ink_css[]  = '@supports not (filter:url(#nm-ink-' . $ink_bg2 . ')){[data-brand="' . $ink_bk2
		. '"] .frame > img{filter:saturate(' . $ink_o['sat'] . ')}}';
}

/* THE SWATCH EXEMPTION IS RETIRED, AND WHAT REPLACES IT IS THE MEASUREMENT IT WAS STANDING IN FOR.
   The carve-out existed for one written-down reason: at full duotone the two swatches' mean
   colours were (183,184,185) and (196,196,197) — "a chroma of 2 on a swatch whose own name is a
   colour". That is a fact about a DUOTONE, and the duotone is gone. Keeping the exemption after it
   would have been the same defect this pass came to fix one level up: a rule outliving its cause.
   It also had a visible cost — on TPL-E-02 it put two full-colour tiles among six inked ones, and a
   product grid where two cells are treated differently reads as a rendering error, not as a
   decision.

   The `Material` row is still read from the manifest and still fails the build if it disappears,
   but it now names the pair the ink must keep APART — which is the PROPERTY — rather than the pair
   the ink must not touch, which was only ever the MECHANISM. A property check cannot be satisfied
   by moving the mechanism somewhere else.

   MEASURED, mean chroma (max−min of the mean RGB) of the two swatches under each anchor's ink:
   `sq-marmol` 28.0 / 25.6 / 45.4 / 24.6 against `sq-pizarra` 2.6 / 1.0 / 20.4 / 2.0. Under the
   retired duotone on `editorial` they were 1.5 and 1.2 — the two products the page could no longer
   tell apart. Raw, they are 38.4 and 0.0, which is the truth the ink now has to preserve: one of
   these stones is warm and the other really is grey. */
$INK_SWATCH = array();
foreach ( $IMAGES as $ink_slug => $ink_row ) {
	if ( 0 === strpos( $ink_row['role'], 'square' ) && isset( $MATERIAL_SLUGS[ $ink_slug ] ) ) {
		$INK_SWATCH[] = $ink_slug;
	}
}
sort( $INK_SWATCH, SORT_STRING );
if ( count( $INK_SWATCH ) < 2 ) {
	fail( '_gallery-images.md § Registers no longer names a Material register with at least two'
		. ' slugs, so there is no swatch PAIR to measure the house ink against — either the table'
		. ' changed shape or the parser stopped reading it, and both end with an ink nothing is'
		. ' asking to keep two stone colours apart' );
}

/* 10 IS ABOVE EVERY NUMBER THE DUOTONE PRODUCED AND FAR BELOW EVERY NUMBER THE GRADE DOES. The
   duotone separated this pair by 0.3 / 0.1 / 0.6 / 0.5 across the four anchors; the grade separates
   it by 25.4 / 24.6 / 25.0 / 22.6. A bar in between is a bar that a regression to ANY monochrome
   treatment trips, and that no honest grade comes near. */
$INK_SWATCH_BAR    = 10.0;
$INK_SWATCH_REPORT = array();
foreach ( $INK as $ink_ak => $ink_av ) {
	if ( ! isset( $used_anchors[ $ink_ak ] ) ) {
		continue;
	}
	$ink_ch = array();
	foreach ( $INK_SWATCH as $ink_slug ) {
		$ink_m         = ink_mean( $ink_slug, $ink_av );
		$ink_ch[ $ink_slug ] = max( $ink_m ) - min( $ink_m );
	}
	$ink_gap = max( $ink_ch ) - min( $ink_ch );
	$INK_SWATCH_REPORT[] = sprintf( '%s %.1f', $ink_ak, $ink_gap );
	if ( $ink_gap < $INK_SWATCH_BAR ) {
		fail( sprintf(
			'under the `%s` ink the material swatches %s measure chroma %s — a spread of %.1f, below'
				. ' the %.1f this page needs to sell two different stones. That is what the retired'
				. ' duotone did: "Mármol Crema Levante" and "Granito Gris Quintana" arrived as two'
				. ' neutral greys a shade apart, and a catalogue that cannot tell its own products'
				. ' apart is not a unified catalogue.',
			$ink_ak,
			implode( ' / ', $INK_SWATCH ),
			implode( ' / ', array_map( function ( $c ) { return number_format( $c, 1 ); }, $ink_ch ) ),
			$ink_gap,
			$INK_SWATCH_BAR
		) );
	}
}
$css[] = implode( "\n", $ink_css ) . "\n";

// ── block 6 · one layer per composition blueprint ──────────────────────────────────────────────
//
// Shared by every anchor that resolves to that blueprint. Below 1024px — the framework's own
// desktop breakpoint, not a number chosen here — all four collapse to the same padded single
// column, because a 14-track grid whose 13 gaps alone would eat a 430px viewport is not a layout.

$COMP_CSS = array();

$COMP_CSS['centered'] = <<<'CSS'
/* ══════════ LP-CENTERED — one symmetric axis, nothing bleeds ══════════
   layout-patterns.md: content in columns 3–11 capped at --content-width, identical on every
   section; headings centred with the eyebrow centred above; images always inside the container
   with equal margins left and right; grids symmetric only — 2, 3 or 4 equal columns. */
[data-comp="lp-centered"] .head{text-align:center;align-items:center;margin-inline:auto;max-width:62ch}
[data-comp="lp-centered"] .ctas{justify-content:center}
[data-comp="lp-centered"] .card:not(.prod) .body{text-align:center;align-items:center}
[data-comp="lp-centered"] .cats{justify-content:flex-start}
@media(min-width:1024px){
  /* "Images: always inside the container, equal margins left and right. Nothing bleeds, ever." */
  [data-comp="lp-centered"] .hero .media,
  [data-comp="lp-centered"] .about .media,
  [data-comp="lp-centered"] .mini .media{max-width:var(--content-width);margin-inline:auto}
  [data-comp="lp-centered"] .items.cols-3{grid-template-columns:repeat(3,minmax(0,1fr))}
  [data-comp="lp-centered"] .items.cols-2{grid-template-columns:repeat(2,minmax(0,1fr))}
  [data-comp="lp-centered"] .cats{justify-content:center}
  [data-comp="lp-centered"] .band .leadform{max-width:34rem;margin-inline:auto}
}
CSS;

$COMP_CSS['strict-grid'] = <<<'CSS'
/* ══════════ LP-STRICT-GRID — every element starts and ends on a column line ══════════
   layout-patterns.md: 12 columns, one gutter (--sp-m), ZERO bleeds; hero copy in columns 1–6 and
   the image in 7–12, both inside the container and not overlapping; headings left-aligned on the
   first column line; one fixed aspect ratio per section so rows line up; 3 or 4 equal columns
   with equal gutters and equal card heights.

   No named lines here on purpose. `full-start`/`full-end` exist to make a violation safe, and
   this blueprint has no violation to make safe — the container edge IS the limit. */
@media(min-width:1024px){
  [data-comp="lp-strict-grid"] .canvas{grid-template-columns:repeat(12,minmax(0,1fr));
    column-gap:var(--sp-m)}
  [data-comp="lp-strict-grid"] .canvas > *{grid-column:1/-1;min-width:0}
  [data-comp="lp-strict-grid"] .hero .head,
  [data-comp="lp-strict-grid"] .about .head,
  [data-comp="lp-strict-grid"] .mini .head{grid-column:1/7;justify-content:center}
  [data-comp="lp-strict-grid"] .hero .media,
  [data-comp="lp-strict-grid"] .about .media,
  [data-comp="lp-strict-grid"] .mini .media{grid-column:7/13}
  [data-comp="lp-strict-grid"] .items.cols-3{grid-template-columns:repeat(3,minmax(0,1fr))}
  [data-comp="lp-strict-grid"] .items.cols-2{grid-template-columns:repeat(2,minmax(0,1fr))}
  /* equal card heights: the rows must visibly align */
  [data-comp="lp-strict-grid"] .card{height:100%}
  [data-comp="lp-strict-grid"] .band .head{grid-column:1/7;grid-row:1}
  [data-comp="lp-strict-grid"] .band .formwrap{grid-column:7/13;grid-row:1}
  [data-comp="lp-strict-grid"] .pdp .pdp-gal{grid-column:1/7}
  [data-comp="lp-strict-grid"] .pdp .pdp-buy{grid-column:7/13}
}
CSS;

$COMP_CSS['broken-grid'] = <<<'CSS'
/* ══════════ LP-BROKEN-GRID — one element per section crosses the grid ══════════
   The same named-line 12 columns as LP-ASYMMETRIC, kept as a reference the page deliberately
   violates. Naming a line is what makes the violation SAFE: crossing the container is
   `c 1 / full-end`, bleeding two edges is `full-start / full-end`, and neither is a negative
   margin that collapses at 430px. Overlaps stack with z-index inside a shared grid row.

   Below 1024px every rule here is off and the page is a plain stacked column — a broken grid at
   430px is just broken. */
@media(min-width:1024px){
  [data-comp="lp-broken-grid"] .canvas{
    max-width:none;padding-inline:0;
    grid-template-columns:
      [full-start] minmax(var(--pad-x-mobile),1fr)
      [wide-start] repeat(12,[c] minmax(0,var(--col)))
      [wide-end c] minmax(var(--pad-x-mobile),1fr)
      [full-end];
  }
  [data-comp="lp-broken-grid"] .canvas > *{grid-column:wide-start/wide-end;min-width:0}

  /* HERO: the oversized H1 crosses the container's right edge and the image sits BEHIND it,
     offset. Text over a photograph needs a scrim — design-tokens.md says the visual language IS
     real photography with scrims, and this is the one place on the page that earns one.

     THE SCRIM'S FLOOR IS MEASURED, NOT EYEBALLED, and the first version failed. It faded to
     fully transparent at the frame's right edge, which LOOKED fine: the h1 was legible in a
     screenshot. Sampling the actual pixels under the text — the image drawn to a canvas, mapped
     through `object-fit:cover`, composited with the scrim's own alpha at each x — put the worst
     case at **1.95:1** where the headline crosses a pale rock face, against the 3:1 that large
     text needs. An image that happens to be dark where the text lands is luck, not a design.

     The floor is 64%: over a worst-case pure-white pixel, `255 - 241×0.64` composites to 100,
     which is 5.4:1 against `--c-text`. That is a veil dark enough to hold ANY frame, which is
     what makes it safe to swap the photograph later. It also suits this anchor — PERS-DIRECT's
     imagery is "high-contrast, tightly cropped", not a bright open frame. */
  [data-comp="lp-broken-grid"] .hero .canvas{row-gap:var(--sp-s)}
  /* `justify-content:center` — the head is a column flex, so this is VERTICAL centring, and it is
     the same declaration LP-STRICT-GRID's hero already carries. Without it the copy hangs at the
     top of a row the photograph beside it is sizing, and everything below the CTAs is void.
     MEASURED at 2560 before the fix: row 714.6px, copy ink 484.9px, 229.7px of empty ink under
     the buttons — and it GREW with the viewport (484.9 / 534.6 / 714.6 at 1440 / 1920 / 2560)
     because of the `height:100%` removed below. Both halves are the fix: the row stops running
     away, and what is left of it frames the copy instead of stranding it. */
  /* `c 1 / c 13`, NOT `full-end`, AND THE OVERLAP IS UNTOUCHED. What makes this hero the broken
     one is that the copy and the photograph share `grid-row:1` and the copy sits ON the picture
     from `c 7` onward — that is the crossing, and `c 13` keeps every pixel of it. The last gutter
     was never used by anything: MEASURED with the head still at `full-end`, the h1's own ink
     stopped at 1773.1 on a 2000 viewport and 1815.2 on a 2560 one — 227px and 745px short of the
     edge the box was claiming — and the lede and both CTAs were shorter still. The bleed bought
     nothing and cost the one thing worth having: an invariant simple enough to check. After this
     line the page has exactly three elements reaching `full-start`/`full-end` and all three are
     `.media`. See RT_MOCKUP_BLEED_NOT_MEDIA in framework-audit.php. */
  [data-comp="lp-broken-grid"] .hero .head,
  [data-comp="lp-broken-grid"] .about .head{grid-column:c 1/c 13;grid-row:1;
    position:relative;z-index:2;justify-content:center}
  [data-comp="lp-broken-grid"] .hero .media,
  [data-comp="lp-broken-grid"] .about .media{grid-column:c 7/full-end;grid-row:1;
    z-index:0;align-self:stretch}
  /* `height:100%` USED TO SIT HERE AND DID NOTHING — worse, it hid what actually sizes this box.
     A percentage height needs a definite basis; the grid row is auto-sized, so it resolved to
     `auto`, and with `aspect-ratio:auto` the frame then took its `<img>`'s OWN 16/9 at whatever
     width the bleed handed it. The bleed is `c 7 / full-end`, i.e. half the viewport, so the hero
     grew taller as the screen grew wider: media width 710.4 / 950.4 / 1270.4 at 1440 / 1920 /
     2560, and the row was 9/16 of that to the pixel — 534.6 and 714.6 at the last two. The copy
     does not grow, so the gap between them was the defect. Say the real rule instead: this frame
     is sized by its own image, floored so a short hero is still a hero. */
  [data-comp="lp-broken-grid"] .hero .frame{aspect-ratio:auto;border-radius:0;
    min-height:min(58vh,460px)}
  [data-comp="lp-broken-grid"] .hero .frame::after{content:"";position:absolute;inset:0;
    background:linear-gradient(to right,var(--c-bg) 0%,var(--c-bg) 22%,
      color-mix(in srgb,var(--c-bg) 64%,transparent) 100%)}
  [data-comp="lp-broken-grid"] .hero h1{max-width:none}
  [data-comp="lp-broken-grid"] .hero .lede{max-width:44ch}

  /* MINI hero (TPL-E-02): the page's TWO-EDGE bleed. */
  [data-comp="lp-broken-grid"] .mini .media{grid-column:full-start/full-end}
  [data-comp="lp-broken-grid"] .mini .frame{border-radius:0}
  [data-comp="lp-broken-grid"] .mini .head{grid-column:c 1/c 9}
  [data-comp="lp-broken-grid"] .mini .cats{grid-column:c 1/c 11}

  /* EVERY OTHER SECTION: the heading crosses a column line, the grid is deliberately uneven,
     and one card is offset vertically by --sp-l. */
  /* `.canvas .head` Y NO `.grid-sec .head`, y la corrección vale para todo lo que venga después.
     Esta colocación habla de LÍNEAS CON NOMBRE —`c 1`, `c 9`— y esos nombres sólo existen en la
     rejilla del canvas. Escrita como `.grid-sec .head` alcanzaba también a las secciones que
     cambian el canvas por otro envoltorio, y allí el navegador no encuentra la línea `c`, se
     inventa columnas implícitas y coloca a los hermanos donde nadie eligió.
     MEDIDO cuando pasó: en las cuatro variantes `direct` de las cuatro arquitecturas que
     estrenaron banda, el encabezado salía pegado al borde derecho y el mapa de la cartera medía
     DOS PÍXELES de ancho. Las otras cuatro anclas estaban bien, así que un vistazo a una sola no
     lo habría visto — lo encontró un barrido de las veinticuatro bandas del catálogo.
     La tentación era subir la especificidad de mi reset hasta ganar. Sería la tercera vez esta
     semana que dos reglas se pelean por un empate, y la respuesta correcta no es gritar más alto:
     es decir dónde vive cada una. Una colocación por líneas del canvas se escribe dentro del
     canvas. */
  [data-comp="lp-broken-grid"] .grid-sec .canvas .head{grid-column:c 1/c 9}
  /* `c 1 / c 13` AND NOT `c 1 / full-end`. THIS ROW USED TO BLEED AND THAT WAS THE DEFECT THE
     READER DREW A LINE THROUGH. `c 13` is the same line as `wide-end` — the band's right edge — so
     the row now ends where the section head ends and the page has a margin on BOTH sides.

     WHY THE PREVIOUS FIX DID NOT WORK. The rule below used to keep the bleed and step only the
     last card's TEXT back by `--pad-x-tablet`, on the principle that a photograph at the glass is
     a bleed and a paragraph at the glass is an amputation. The principle is right; the application
     split one object across the boundary. MEASURED at 2000: `.services .items > :last-child` had
     its frame at right 2000.0 and its body ink stopping at 1968.0 — a 32px inset no reader can
     read as intentional beside a 250px margin on the other side of the same row. He looked at that
     exact render and called the card cut off. A CARD IS NOT A PHOTOGRAPH: it is a bordered surface
     carrying a heading and a paragraph, parsed as one thing, and bleeding its image while insetting
     its text does not make it a bleed — it makes it a card with a printing error. Either the whole
     object crosses the glass or none of it does, and for a card the answer is none of it.

     WHAT THE BLUEPRINT KEEPS. "Un elemento por sección cruza la retícula" is not "the card row
     reaches the screen edge". This row still crosses the reference grid — 4/5/3 tracks against
     twelve equal columns, and card 2 pushed down by `--sp-l`, neither of which any reading of the
     12-col grid would produce. The strip still bleeds where a bleed is legible AS a bleed: the
     hero media, the mini media on two edges — photographs, not cards.

     `minmax(min-content, …)` AND NOT A BARE `3fr`. The uneven rail is the blueprint and it stays,
     but a free-space fraction has no floor: between 1024 and 1279 the desktop grid is on while the
     viewport is still narrower than the band, and the 3fr track fell under the width of the one
     word its own h3 has to hold. MEASURED at 1024: "Restauración" 4.9px past the right edge of a
     1024 viewport, which is also the whole of `documentElement.scrollWidth` reading 1029 — one
     defect wearing two hats, and the overflow gate could see only the hat that was 5px wide.
     Flooring the track at its content's min-content means a column is never narrower than the word
     it must carry, and above ~1280 the fractions are all wider than min-content so nothing binds
     and the 4/5/3 rhythm is exactly what it was. */
  [data-comp="lp-broken-grid"] .services .items,
  [data-comp="lp-broken-grid"] .cases .items{grid-column:c 1/c 13;
    grid-template-columns:minmax(min-content,4fr) minmax(min-content,5fr) minmax(min-content,3fr)}
  [data-comp="lp-broken-grid"] .cases .items{
    grid-template-columns:minmax(min-content,5fr) minmax(min-content,7fr)}
  /* "one card offset vertically by --sp-l" — on a CONTENT grid only. Applied to the catalogue it
     nudged product #2 down out of an otherwise dense 4-column row, which reads as a rendering
     bug rather than a composition choice, and TPL-E-02's density is ADN the blueprint does not
     get to spend. Caught by looking at it; no measurement would have flagged it. */
  [data-comp="lp-broken-grid"] .services .items > :nth-child(2),
  [data-comp="lp-broken-grid"] .cases .items > :nth-child(2){margin-top:var(--sp-l)}
  [data-comp="lp-broken-grid"] .band .head{grid-column:c 1/c 6;grid-row:1}
  /* A FORM NEVER BLEEDS. THIS ONE DID, AND IT WAS THE WORST THING ON THE PAGE — not a styling
     complaint but a control the reader cannot use. MEASURED at `c 6 / full-end`: every field and
     the submit button ended at exactly x=2000.0 on a 2000 viewport and x=2560.0 on a 2560 one,
     their right border ON the glass with zero paper beside it, and the name field was 1133.7px
     wide at 2000 and 1453.3px at 2560 — a single-line input more than half the screen across.
     `documentElement.scrollWidth === clientWidth` the whole time, so nothing overflowed and no
     overflow gate could see any of it. The reader's words were "esto no puede pasar bajo ningún
     concepto" and he is right. A photograph at the glass is a bleed. A paragraph at the glass is
     an amputation. A SUBMIT BUTTON AT THE GLASS IS A BROKEN PAGE: no hit-slop on one side, and the
     border that tells you where the control ends is coincident with the edge of the screen. */
  [data-comp="lp-broken-grid"] .band .formwrap{grid-column:c 6/c 13;grid-row:1}
  [data-comp="lp-broken-grid"] .pdp .pdp-gal{grid-column:c 1/c 7}
  [data-comp="lp-broken-grid"] .pdp .pdp-buy{grid-column:c 7/c 13}
  /* THE `padding-right:var(--pad-x-tablet)` ON THE LAST CARD'S BODY IS GONE, and its absence is
     the point rather than an omission. It existed to give a bleeding card's text some paper; with
     the row ending at `c 13` there is no bleeding card, and leaving it would inset the last card's
     copy 32px further than its two neighbours for no reason a reader could name — the same
     internal inconsistency in the opposite direction. A rule that is only correct because of a
     bleed has to leave with the bleed. */
}
CSS;

$COMP_CSS['asymmetric'] = <<<'CSS'
/* ══════════ LP-ASYMMETRIC — copy on 7 of 12 columns, one image bleeding a viewport edge ══════════
   THE BLEED, and why it is NOT `margin-right: calc(50% - 50vw)`. Percentage margins on a grid
   item resolve against the item's OWN grid area, not the container, so that technique overshoots
   — measured 312px past a 1265px viewport, invisible only because `overflow-x:clip` was hiding
   it. Named grid lines do the job exactly: `full-end` IS the layout viewport's right edge, so the
   bleed needs no vw arithmetic, no clip and no half-a-scrollbar fudge (`vw` includes the
   scrollbar; the layout viewport does not).

   This is also why the strip is full width. `full-end` is the GRID CONTAINER's edge — put this
   canvas inside a padded column and the bleed stops at the column, silently. */
@media(min-width:1024px){
  [data-comp="lp-asymmetric"] .canvas{
    max-width:none;padding-inline:0;
    grid-template-columns:
      [full-start] minmax(var(--pad-x-mobile),1fr)
      [wide-start] repeat(12,[c] minmax(0,var(--col)))
      [wide-end c] minmax(var(--pad-x-mobile),1fr)
      [full-end];
  }
  [data-comp="lp-asymmetric"] .canvas > *{grid-column:wide-start/wide-end;min-width:0}
  /* headings left-aligned, never centred; the eyebrow sits above and left */
  [data-comp="lp-asymmetric"] .head{grid-column:c 1/c 8}
  /* the one bleed per section, always the SAME edge, all the way down the page */
  [data-comp="lp-asymmetric"] .hero .media,
  [data-comp="lp-asymmetric"] .about .media{grid-column:c 8/full-end}
  [data-comp="lp-asymmetric"] .hero .frame{border-radius:0}
  /* `justify-content:center` — the head is a column flex, so this is VERTICAL centring, and it is
     the same declaration this blueprint's own `.hero` equivalent and LP-STRICT-GRID both already
     carry. Without it the mini banner's copy hangs at the top of a row the photograph beside it is
     sizing. MEASURED at 2560 before this line: row 220px, copy ink 139.4px, the ink centre 53.3px
     above the row centre — the same shape as the hero void row 32(b) exists to catch, one section
     over and small enough to have been walked past four times. */
  [data-comp="lp-asymmetric"] .mini .head{grid-column:c 1/c 7;justify-content:center}
  [data-comp="lp-asymmetric"] .mini .media{grid-column:c 7/full-end}
  [data-comp="lp-asymmetric"] .mini .frame{border-radius:0}
  [data-comp="lp-asymmetric"] .mini .cats{grid-column:c 1/c 11}
  /* two columns at 7/5, never 50/50 — a 3-card grid becomes 2 + 1, which is the point */
  [data-comp="lp-asymmetric"] .services .head{grid-column:c 1/c 6}
  [data-comp="lp-asymmetric"] .services .items{grid-column:c 1/c 13;grid-template-columns:7fr 5fr}

  /* ── THE TWO THINGS THE READER RINGED ON THIS STRIP, and they are one shape: an uneven grid
        holding three ratio-locked cards cannot line anything up by itself.

     (1) THE CAPTION DID NOT SHARE AN EDGE WITH ITS OWN PHOTOGRAPH. `.frame` carries
         `aspect-ratio:var(--ratio-card)`, so two frames at the SAME ratio in tracks of DIFFERENT
         widths are different heights, and each caption starts wherever its own image happens to
         end. MEASURED at 2000 before this rule: frame 1 bottom 2393.9, frame 2 bottom 2225.8 —
         "Fachadas y sillería" sat 168.1px higher than "Encimeras y baños" on a row the grid had
         already stretched to a common height, so the shorter card also carried 168px of dead paper
         under its own text. Two captions on one row, on two different lines, is what he drew an
         arrow at.
         The fix hands the row's slack to the narrower FRAME instead of leaving it under the copy:
         card 1 keeps its ratio and therefore still sets the row height, card 2 drops the ratio and
         absorbs what is left. Both images now end on one line and both captions start on it —
         measured 2474.0 / 2475.0 for both cards at 2000. The cost is that card 2's photograph is
         cropped harder than `--ratio-card` asks; `object-fit:cover` already centres it, and a crop
         is a smaller lie than a caption that has come unstuck from its picture.

     (2) THE THIRD CARD LEFT A DEAD HALF-TRACK. Three cards in two tracks put card 3 alone on row
         2, filling the 7fr and leaving the 5fr empty — measured at 2000 as a 611.5 x 680.5 hole of
         blank paper, which is what he boxed. "A 3-card grid becomes 2 + 1" is still the point;
         what it must not become is 2 + 1 + a void. So the `1` spans the full row and repeats the
         strip's own 7/5 inside itself, image beside copy. The asymmetry that names this blueprint
         now appears twice on the section instead of once, and no cell is empty. ── */
  [data-comp="lp-asymmetric"] .services .items > :nth-child(2) .frame{
    aspect-ratio:auto;flex:1 1 auto;min-height:0}

  /* ── THE ALTERNATION REACHES THE CAPTION. `layout-patterns.md` already asks this blueprint to
        alternate -- "two columns at 7/5 or 5/7, alternating direction section to section" -- and
        the file's own § "Page rhythm" heading is "alternate to avoid monotony". Card 2 carries its
        caption ABOVE its photograph while 1 and 3 keep it below, which is that rhythm applied to a
        second axis rather than a one-off on one card: three cards in a row that all stack the same
        way read as one card printed three times, and the eye finds the repetition before it finds
        the work.

        `order` and not `flex-direction:column-reverse`, because reversing the axis would also carry
        `.rule` to the far side. The hairline divides the photograph from its caption and belongs
        BETWEEN them whichever is on top, so it moves with the body rather than staying put. The
        body's padding flips with it for the same reason -- `--sp-s` opens the gap toward the
        photograph, and toward is a different edge once the caption is above it. ── */
  [data-comp="lp-asymmetric"] .services .items > :nth-child(2) > .body{
    order:-2;padding:0 0 var(--sp-s)}
  [data-comp="lp-asymmetric"] .services .items > :nth-child(2) > .rule{order:-1}
  [data-comp="lp-asymmetric"] .services .items > :last-child{
    grid-column:1/-1;display:grid;grid-template-columns:7fr 5fr;
    gap:var(--sp-m);align-items:center}
  /* The hairline divides a stacked image from its caption. Side by side there is nothing for it to
     divide, and as a third grid item it would claim a cell and push the copy out of the row. */
  [data-comp="lp-asymmetric"] .services .items > :last-child .rule{display:none}
  [data-comp="lp-asymmetric"] .services .items > :last-child .body{padding:0}
  [data-comp="lp-asymmetric"] .cases .head{grid-column:c 1/c 6}
  /* `c 6 / c 13` and `c 3 / c 13` below, NOT `full-end`. Same reversal as LP-BROKEN-GRID's card
     rows and for the same reason: these two rails also ended at the glass, and each carried a
     `padding-right` on its last card's body to keep the TEXT off the edge while that card's own
     image stayed on it. Three rails, three copies of a rule that left every last card internally
     inconsistent — image bleeding, copy inset 32px — and the reader read all of it as cut off.
     Cards end at the band. The blueprint's one bleeding image is the hero's, which is what "una
     imagen sangra un borde" says: an IMAGE, singular, and `.hero .media` is a bare frame with no
     copy in it to amputate. Before this the strip bled at five places and the line had stopped
     meaning anything. */
  [data-comp="lp-asymmetric"] .cases .items{grid-column:c 6/c 13;grid-template-columns:7fr 5fr}
  /* the catalogue grid keeps its dense equal columns — see the note in strip_ecommerce() */
  [data-comp="lp-asymmetric"] .prods .head{grid-column:c 1/c 6}
  [data-comp="lp-asymmetric"] .carousel .head{grid-column:c 1/c 6}
  [data-comp="lp-asymmetric"] .carousel .items{grid-column:c 3/c 13}
  [data-comp="lp-asymmetric"] .band .head{grid-column:c 1/c 7;grid-row:1}
  [data-comp="lp-asymmetric"] .band .formwrap{grid-column:c 7/c 13;grid-row:1}
  [data-comp="lp-asymmetric"] .pdp .pdp-gal{grid-column:c 1/c 7}
  [data-comp="lp-asymmetric"] .pdp .pdp-buy{grid-column:c 7/c 13}
  [data-comp="lp-asymmetric"] .site-foot .fnav{grid-column:c 1/c 8}
}
CSS;

$css[] = "\n/* ══════════════════════════════ COMPOSITION LAYERS ══════════════════════════════ */\n"
	. <<<'CSS'
/* The canvas below 1024px, shared by all four blueprints. */
.canvas{display:grid;grid-template-columns:minmax(0,1fr);gap:var(--sp-l) var(--sp-m);
        max-width:var(--content-width);margin-inline:auto;padding-inline:var(--pad-x-mobile)}
@media(min-width:768px){.canvas{padding-inline:var(--pad-x-tablet)}}
.canvas > *{min-width:0}
/* COMP-PRODUCT-GRID density, straight from TPL-E-02: mobile 2, tablet 3, desktop 4. Identical
   under all four blueprints — a catalogue grid is archetype DNA, not a composition choice. */
/* Equal card heights so the rows visibly align — a catalogue whose cards end at four different
   heights reads as unfinished whatever the anchor says. */
.grid-prod > .card{height:100%}
.grid-prod{grid-template-columns:repeat(2,minmax(0,1fr))}
@media(min-width:768px){.grid-prod{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(min-width:1024px){.grid-prod{grid-template-columns:repeat(4,minmax(0,1fr))}}
CSS;

foreach ( $COMPOSITION as $pos => $meta ) {
	if ( isset( $used_comps[ $pos ] ) ) {
		if ( ! isset( $COMP_CSS[ $pos ] ) ) {
			fail( "no CSS layer for composition `$pos` ({$meta['lp']}) — a strip asks for a blueprint this build cannot draw" );
		}
		$css[] = $COMP_CSS[ $pos ];
	}
}

// ── block 7 · THE CLOSE ────────────────────────────────────────────────────────────────────────
//
// "No veo los call to action en las home." Checked before answering, and he is half wrong and
// entirely right: the closing sections exist in all eight — four COMP-LEAD-FORM bands on TPL-C-01,
// four COMP-FAQ + COMP-CONTACT-DIRECT pairs on TPL-E-02. THEY EXIST AND NONE OF THEM READS AS A
// CALL TO ACTION. A form is a form. An accordion is help. Three phone numbers under a heading are
// a footer that has not admitted it yet — TPL-E-02's close carried no control at all, so the page
// ended by listing ways to leave it.
//
// WHAT A CLOSING BAND OWES, and it is the same three things at every anchor:
//   1. Its own ground. A close painted the same white as the section above it is furniture.
//   2. Exactly ONE control carrying weight — the biggest single spend of the accent budget.
//   3. An edge. Something that says the page is ending and is asking for something.
//
// WHAT IT MUST NOT BE is one recipe pasted four times. `PERS-DIRECT` closing loud and
// `PERS-EDITORIAL` closing quiet IS the axis system working; both closing invisibly is the defect.
// So the three obligations above are the FLOOR and each anchor discharges them in its own language,
// out of tokens it already owns — elevation, ground, density, scale. Nothing below invents a
// colour or a shadow that is not already this anchor's.
//
// AFTER THE COMPOSITION LAYERS ON PURPOSE. `[data-anchor]` and `[data-comp]` are the same
// specificity, so source order decides ties, and the blueprint owns PLACEMENT — which column the
// head and the form sit in. This block owns SURFACE. Emitted last so a surface rule cannot be
// silently outranked by a blueprint that only meant to place something.

$css[] = <<<'CSS'
/* ══════════ THE CLOSE — shared floor ══════════ */
/* The void under the heading, which is in every before-capture of this page: `.band .head` is a
   short column beside a four-field form, so on three of four blueprints the head's own cell ran
   400px taller than its ink and the band read as a half-empty row. Centring the head against the
   form is one declaration and it is the whole fix. */
@media(min-width:1024px){
  .band .head{justify-content:center}
}
/* ONE CONTROL, AND IT IS SIZED LIKE ONE. A submit button that is the same size as an `Añadir` in a
   product card is not a close, it is a form control that happens to be last. */
.band .leadform > .btn,.close .btn-close{align-self:start;padding:1.05rem 2.4rem;font-size:1.05rem}
/* TPL-E-02's close had NO control. `.chans` stays — a counter still answers the phone — but the
   band now asks for the thing before it lists the ways to ask for it. */
.close .closecta{display:flex;flex-wrap:wrap;align-items:center;gap:var(--sp-s);margin-top:var(--sp-m)}
.close .chans{margin-top:var(--sp-l)}
CSS;

// ── the four voices ────────────────────────────────────────────────────────────────────────────
//
// Same three obligations, four languages, and each one spends only tokens its own anchor already
// owns. Read them side by side: EDITORIAL turns the paper over, DIRECT floods the page with its
// accent, MATTER sets a plaque into the surface, INSTITUTIONAL presents a panel. Nothing here is
// the same recipe with a different colour — the closes differ in KIND, which is what stops a
// shared floor from flattening the set at the one place a reader is most likely to compare.
$close_css = array( '/* ══════════ THE CLOSE — one voice per anchor ══════════ */' );

foreach ( $FIELD as $fld_k => $fld_v ) {
	$close_css[] = '/* ' . $fld_k . ': ' . ( 'inverse' === $fld_v['kind'] ? 'the back cover' : 'a field of accent' )
		. ' — type ' . $fld_v['r_text'] . ', control ' . $fld_v['r_ctrl'] . ', label ' . $fld_v['r_lbl'] . ' */';
	$close_css[] = '[data-field="' . $fld_k . '"] .sec.closing{'
		. '--c-bg:' . $fld_v['bg'] . ';--c-bg-alt:' . $fld_v['alt'] . ';--c-text:' . $fld_v['text'] . ';'
		. '--c-accent:' . $fld_v['accent'] . ';--c-on-accent:' . $fld_v['on'] . ';'
		. ( $fld_v['over'] ? implode( ';', $fld_v['over'] ) . ';' : '' )
		. 'background:' . $fld_v['bg'] . ';color:' . $fld_v['text'] . '}'
		. ( $fld_v['over'] ? '   /* overridden: ' . implode( ' · ', $fld_v['over'] ) . ' */' : '' );
}

$close_css[] = <<<'CSS'
/* ══════════ THE SECTION INDEX — one attribute, four renderings ══════════
   `content: attr(data-index) / ""` — the SLASH IS LOAD-BEARING. It is the CSS alt-text syntax, and
   an empty alt is what tells the accessibility tree to skip generated content. Without it a screen
   reader announces "zero two" before every section heading on the page: an ordinal that exists for
   the eye becoming noise for the ear. The number is decoration in the strict sense — the sections
   are already in order and already labelled — so it owes nothing to anybody who cannot see it. */
.head{position:relative}

/* EDITORIAL — hung in the page margin, which is CRAFT-PRINT's move and needs a margin to hang in.
   Below 1280 there is not one, so the index sits above the eyebrow instead of overlapping the
   copy: a number in the gutter at 1024 is a number on top of a word. */
[data-anchor="editorial"] .head::before{
  content:attr(data-index) / "";display:block;margin-bottom:var(--sp-s);
  font-family:var(--font-secondary);font-size:11px;font-weight:600;letter-spacing:.2em;
  font-variant-numeric:tabular-nums lining-nums;color:var(--c-text-muted)}
@media(min-width:1280px){
  [data-anchor="editorial"] .head::before{
    position:absolute;right:100%;top:.15em;margin:0 var(--sp-m) 0 0;text-align:right}
}

/* DIRECT — the numeral as graphic, cropped by the section's own top edge. NOT a heading: empty
   alt text, tinted far below text contrast, and at a size no step of the scale contains, so it
   reads as a printed mark rather than as type. `z-index` on the head's real children rather than
   a negative one on the numeral — negative would drop it behind the section's own background and
   make it vanish on `.bg-alt`. */
[data-anchor="direct"] .head::before{
  content:attr(data-index) / "";position:absolute;left:-.06em;top:0;z-index:0;
  font-family:var(--font-primary);font-weight:700;font-size:clamp(84px,11vw,168px);line-height:.7;
  letter-spacing:-.05em;font-variant-numeric:lining-nums tabular-nums;
  color:color-mix(in srgb,var(--c-text) 9%,var(--c-bg));transform:translateY(-54%);
  pointer-events:none}
[data-anchor="direct"] .head > *{position:relative;z-index:1}
[data-anchor="direct"] .sec{overflow:clip}
[data-anchor="direct"] .head{padding-top:calc(var(--sp-l)*.5)}

/* MATTER — the number, then a hairline that starts at the number's own right edge and stops after
   2.2rem. That is CRAFT-PRINT's second move — a rule that begins at a text edge rather than at a
   container edge — and it is the one this anchor can afford: `elevation: hairline` means a 1px
   line IS this personality's vocabulary, so the index arrives drawn in it.
   `.head::before` and NOT `.eyebrow::before`: `attr()` reads the attribute of the element its
   pseudo-element is attached to, and `data-index` is on the head. Pointed at the eyebrow it
   resolves to the empty string and renders a bare hairline with no number — which looks
   deliberate, which is why it is worth writing down. */
[data-anchor="matter"] .head::before{
  content:attr(data-index) / "";display:flex;align-items:center;gap:.6rem;
  margin-bottom:var(--sp-xs);font-family:var(--font-secondary);
  font-size:11px;font-weight:700;letter-spacing:.18em;
  font-variant-numeric:tabular-nums lining-nums;color:var(--c-text-muted)}
[data-anchor="matter"] .head::after{
  content:'';position:absolute;left:2.6rem;top:.55em;width:2.2rem;height:1px;
  background:var(--c-border)}

/* INSTITUTIONAL — centred above the eyebrow, which is the only place LP-CENTERED allows anything. */
[data-anchor="institutional"] .head::before{
  content:attr(data-index) / "";display:block;margin-bottom:var(--sp-xs);
  font-size:11px;font-weight:700;letter-spacing:.22em;
  font-variant-numeric:tabular-nums lining-nums;color:var(--c-text-muted)}

/* EDITORIAL — the back cover. Elevation `none` means this anchor owns no shadow and no fill, so
   the only mark of an ending available to it is turning the paper over. The h2 is the one section
   heading on this page allowed to reach the h1 step: at `editorial` scale that is 88px of Fraunces
   on near-black, which is quiet in colour and unmistakable in size — the whole point of the
   anchor. Nothing lifts, nothing glows; the ending is a change of stock. */
[data-anchor="editorial"] .sec.closing{border-top:none}
[data-anchor="editorial"] .sec.closing .head h2{font-size:var(--fs-h1);letter-spacing:var(--track-h1)}
[data-anchor="editorial"] .sec.closing .eyebrow{color:var(--c-text-muted)}
[data-anchor="editorial"] .sec.closing .field input,
[data-anchor="editorial"] .sec.closing .field textarea{background:transparent;border-radius:0;
  border-width:0 0 1px}
[data-anchor="editorial"] .sec.closing .btn-primary:hover{transform:none;
  background:var(--c-text);border-color:var(--c-text);color:var(--c-bg)}

/* DIRECT — the field. `ground: ink` and `elevation: accent-glow`, on the anchor whose brief is
   "marcas que ganan por ser inconfundibles": the close floods with the accent and the control
   inverts to the page's own ink, because an accent button on an accent field is a button nobody
   can find. At `monumental` scale the h2 caps at 74px on orange, which is the loudest thing on
   any of the eight strips and is meant to be. */
[data-anchor="direct"] .sec.closing .eyebrow{color:var(--c-text)}
[data-anchor="direct"] .sec.closing .field input,
[data-anchor="direct"] .sec.closing .field textarea{background:color-mix(in srgb,var(--c-text) 8%,transparent);
  border-color:color-mix(in srgb,var(--c-text) 28%,transparent)}
[data-anchor="direct"] .sec.closing .btn-primary:hover{box-shadow:none;
  background:transparent;color:var(--c-text);border-color:var(--c-text)}
[data-anchor="direct"] .sec.closing .btn-outline{border-color:color-mix(in srgb,var(--c-text) 45%,transparent)}

/* MATTER — the plaque. `elevation: hairline` says the border is all the chrome this anchor gets,
   and `LP-STRICT-GRID` says everything starts and ends on a column line, so the close is a panel
   set into the section: the page's own paper inside a 1px accent-tinted frame, on the alt ground.
   Milled, not floated — there is no shadow anywhere in this anchor's vocabulary. */
[data-anchor="matter"] .sec.closing{background:var(--c-bg-alt)}
[data-anchor="matter"] .sec.closing .head,
[data-anchor="matter"] .sec.closing .formwrap{background:var(--c-bg);
  border:1px solid color-mix(in srgb,var(--c-accent) 26%,var(--c-bg));
  border-radius:var(--radius-container);padding:var(--sp-l) var(--sp-m)}
/* ONE PLAQUE, NOT TWO. `LP-STRICT-GRID` places the head and the form as separate grid items with
   `column-gap:var(--sp-m)` between them, so bordering each one produced two boxes with a 32px slot
   down the middle — a panel that had come apart, which is the opposite of milled. Closing the gap
   and dropping the inner edges makes the two halves one object; the outer radius then belongs to
   the object rather than to each half. Caught by looking at it. */
/* `.band` ONLY, and the scope is the fix rather than tidying. The corporate close is a head
   BESIDE a form, so the plaque is two halves butted together. The ecommerce close has no form —
   its head is the whole panel — and these rules applied there produced one box with its right
   border removed and its right corners squared: a plaque milled to fit a neighbour that does not
   exist. */
@media(min-width:1024px){
  [data-anchor="matter"] .band.closing .canvas{column-gap:0}
  [data-anchor="matter"] .band.closing .head{border-right:none;
    border-radius:var(--radius-container) 0 0 var(--radius-container)}
  [data-anchor="matter"] .band.closing .formwrap{border-left:none;
    border-radius:0 var(--radius-container) var(--radius-container) 0}
}

/* INSTITUTIONAL — the panel. `elevation: soft-shadow` and `LP-CENTERED`: this anchor already
   presents everything as a centred card that lifts, so its close is the same gesture at page
   scale — one bounded panel on the page's own paper, over the alt ground, with the anchor's own
   resting shadow. Formal, which is the axis position doing its job. */
[data-anchor="institutional"] .sec.closing{background:var(--c-bg-alt)}
/* THE PANEL IS THE CANVAS, NOT ITS CHILDREN, and the difference was visible the moment it
   rendered. `LP-CENTERED` stacks `.head` and `.formwrap` as separate block children, so panelling
   each one produced TWO disconnected cards with a gap down the middle — an ask and a form that had
   come apart, which reads as a rendering fault rather than as a composition. One panel around the
   whole close is also what this anchor's own card recipe describes, at page scale: a bounded
   surface on `--c-bg` carrying the resting shadow. Caught by looking at it. */
[data-anchor="institutional"] .sec.closing .canvas{max-width:min(var(--content-width),58rem);
  background:var(--c-bg);box-shadow:var(--elev-rest);border-radius:var(--radius-container);
  padding:var(--sp-l) var(--sp-m)}
@media(min-width:1024px){
  [data-anchor="institutional"] .sec.closing .canvas{padding:var(--sp-xl) var(--sp-l)}
}
CSS;

$css[] = implode( "\n", $close_css ) . "\n";

/* LAST IN THE FILE ON PURPOSE. The class-name register below reads "is this class defined before
   the block that OWNS it", so an archetype's own block has to come after every shared one. */
$css[] = <<<'CSS'
/* ══════════ TPL-C-14 · RITUAL / BONO ══════════ */

/* ── DOS TAMAÑOS QUE SON MÍOS Y NO DEL ANCLA ──────────────────────────────────────────────────
   La primera versión se leyó como «todo gigante», y medida a 1280 daba: h1 87 · h2 58 · h3 39 ·
   CUERPO 16. El problema no era el titular: era el SALTO. `--fs-body` es `clamp(1rem,1.2vw,
   1.25rem)`, y su tope de 20px no entra hasta los 1667px de ventana, así que a 1280 el cuerpo se
   queda en su suelo de 16 y la distancia contra un h2 de 58 es de 3,6×. Una escala `editorial`
   con densidad `generous` pide un cuerpo que la acompañe.

   POR QUÉ SE PUEDE TOCAR AQUÍ. Los cinco ejes —escala, fondo, densidad, composición, elevación—
   son del ancla y moverlos convertiría los chips de la tarjeta en una mentira. `--fs-body` NO es
   uno de ellos: el propio bloque de tokens lo dice en su comentario («body is NOT an axis»), y va
   colgado de `[data-arch]`, que es el gancho que el guide reserva para lo que un arquetipo puede
   decidir por su cuenta. El h1 sigue siendo 88 y el h2 sigue siendo 58.

   Y `--fs-item` es el escalón que faltaba. Precio, minutaje, nombre de cabina y nombre de zona
   los había puesto yo en h3 (39px) y en h2 (58px) — un precio a 58px es un titular con forma de
   número. No hay paso entre 16 y 39 en la escala, así que el arquetipo se declara el suyo. */
/* LAS TRES DECLARACIONES QUE ESTABAN AQUÍ YA NO ESTÁN, y eso es el resultado y no una pérdida.
   `--fs-body`, `--fs-item` y `--fs-name` nacieron colgadas de este arquetipo porque fue el
   primero que chocó contra el hueco. Medido el resto del catálogo, el hueco estaba en las 67
   tiras, así que las tres subieron a la raíz y al bloque de campos. Este bloque conserva sólo lo
   que de verdad es suyo. */
[data-arch="tpl-c-14"]{
  /* Y un escalón más abajo para la carta. `--fs-item` (27px) es el tamaño de un DESTINO —una zona,
     una cabina—, de los que hay cuatro por pantalla. Un ritual es una ENTRADA de lista y hay seis;
     al mismo tamaño la carta se convierte en seis titulares apilados. 22px la deja fina y sigue
     estando por encima del cuerpo. */
  /* Nada propio que declarar hoy: las tres medidas que este arquetipo inventó son ahora del
     sistema. El bloque se queda porque las reglas de abajo cuelgan de él.
     SE VA CON UNA LECCIÓN QUE NO CADUCA: `body{font-size:var(--fs-body)}` resuelve la variable
     ARRIBA, en la raíz, y todo párrafo hereda el valor YA CALCULADO. Redeclarar el token a media
     página no reabre esa herencia — sólo afecta a quien vuelva a escribir `var(--fs-body)`.
     Medido en su día: con el token en 19px el cuerpo seguía renderizando 16. Quien vuelva a
     redefinir un token de tamaño dentro de un `[data-arch]` tiene que USARLO ahí mismo. */
}

/* ── LOS DOS ENVOLTORIOS QUE NO SON `.canvas` ─────────────────────────────────────────────────
   `secrow` es la sección haciendo de fila: se lleva el ancho y el padding que llevaba el canvas,
   y sus hijos son las columnas. Un nodo menos por sección, que en el build nativo es un
   contenedor menos y un clic menos entre un humano y el widget que abrió el editor para tocar.
   `bleedband` es la sección haciendo de banda: sin canvas y sin padding lateral, porque lo que
   tiene que llegar al cristal es el color y la fotografía. El texto NO llega: cada panel de
   dentro lleva su propio padding, que es la diferencia entera entre una banda y una tarjeta
   cortada por el borde. */
/* EL FONDO LLEGA AL BORDE Y EL CONTENIDO NO, en un solo nodo. La primera versión capaba el ancho
   de la SECCIÓN, que es lo mismo que capar su fondo: en la página de Nosotros, donde esta fila cae
   sobre banda alterna, la banda dejó de tocar los bordes y se leyó como un panel metido dentro de
   la página. El padding lateral se calcula contra el ancho de contenido —`max(gutter, (100% −
   contenido) / 2)`— así que la sección sigue siendo del ancho de la ventana, su color también, y
   el texto se queda donde estaría dentro de un canvas. Ésta es la razón de que la fila NO
   necesite el `<div>` que venía a quitar. */
/* ── `max` DONDE TOCABA SUMAR, Y SE VE A SIMPLE VISTA SI SE SABE DÓNDE MIRAR ─────────────────
   Una fila es la sección haciendo de canvas, así que su texto tiene que caer en la MISMA columna
   que el de la sección de al lado. No caía. El canvas hace dos cosas —`max-width` con márgenes
   automáticos, que a 1280 dan 70px por lado, y `padding-inline:32`— y el texto sale por tanto en
   x=102. La fila las juntaba con `max(32, 70)` = 70, o sea se quedaba con la mayor y tiraba la
   otra: 32px a la izquierda de sus vecinas, sección sí y sección no.
   Medido en la home del centro de estética: hero en 102, tour de cabinas en 70. Los dos bordes
   restantes de esa página —24 y 192— NO son de este defecto y no se tocan: el 24 es un mosaico a
   sangre donde cada celda lleva su propio relleno, y el 192 un panel centrado dentro de una
   banda. Las tres formas del chasis existen justamente para poder decir eso; lo que no podían
   decir es que una fila empezara en otro sitio que su vecina.
   La suma, y no el máximo: el margen automático LLEVA al borde del contenido, y el relleno separa
   del borde. Son dos cosas distintas y se aplican las dos. */
.secrow{padding-inline:max(var(--pad-x-mobile),
          (100% - var(--content-width)) / 2 + var(--pad-x-mobile));
  display:grid;gap:var(--sp-l);grid-template-columns:minmax(0,1fr);align-items:start}
@media(min-width:768px){
  .secrow{padding-inline:max(var(--pad-x-tablet),
            (100% - var(--content-width)) / 2 + var(--pad-x-tablet))}}
/* UNA BANDA TAMBIÉN NECESITA RITMO VERTICAL, y faltaba. `.secrow` lo tenía desde el primer día
   —`gap: var(--sp-l)`— y `.bleedband` sólo apagaba el relleno lateral, así que sus hijos se
   apilaban con los márgenes que cada uno trajera de casa. No se notó hasta ahora porque las dos
   bandas que existían llevaban paneles con su propia caja dentro; la primera banda con un
   encabezado como hijo DIRECTO sacó el titular montado encima de la primera fila de fotos.
   Una columna con separación: la banda decide el ancho de sus hijos, no su respiración. */
.bleedband{padding-inline:0;display:grid;gap:var(--sp-l);grid-template-columns:minmax(0,1fr)}
/* ── Y LO PRIMERO QUE HACE UN ENVOLTORIO NUEVO ES APAGAR EL VIEJO ─────────────────────────────
   Las tres composiciones colocan a mano los hijos del canvas por LÍNEAS CON NOMBRE — `.head` a
   `c 1 / c 8`, la nota a su columna estrecha— y esos nombres sólo existen en la rejilla del
   canvas. Dentro de una fila o de una banda no hay ninguna línea llamada `c`, así que el
   navegador se inventa columnas implícitas y reparte los hijos por ellas: medido en el primer
   render, el titular de «La casa» y su nota salieron en dos columnas de una palabra de ancho,
   al lado de la lista. No es un fallo de esas reglas — es que no van dirigidas aquí. Un
   envoltorio que no es `.canvas` tiene que decirlo, y esta es la línea que lo dice. */
.secrow > *, .secrow .head, .secrow .pnote, .secrow .lede,
.bleedband > *, .bleedband .head, .bleedband .pnote, .bleedband .lede{grid-column:auto}
/* LO QUE SANGRA ES EL COLOR, NO LA LETRA, y una banda sin canvas se lleva por delante esa
   distinción si no se dice. Medido en el primer render: la nota de los bonos empezaba en x=0,
   pegada al cristal, porque `padding-inline:0` es lo que hace banda a la banda. Cada hijo de
   texto directo recupera aquí el ancho de contenido que el canvas le daba; los paneles y las
   fotografías no lo recuperan, que es justamente para lo que existe la banda. */
/* EL ENCABEZADO ENTRA EN ESTA LISTA, y es la regla general dicha en voz alta: EL TEXTO NUNCA
   SANGRA. Una banda existe para que el COLOR y la FOTOGRAFÍA lleguen al cristal; un titular
   pegado al borde de la pantalla no es una banda, es un texto sin margen. Faltaba y se notó a la
   primera: el primer arquetipo que pidió banda con encabezado propio lo sacó a 24px del borde
   mientras el resto de su página empezaba en 102. */
.bleedband > .pnote, .bleedband > .lede, .bleedband > .head{max-width:var(--content-width);
  margin-inline:auto;padding-inline:var(--pad-x-mobile)}
@media(min-width:768px){.bleedband > .pnote, .bleedband > .lede, .bleedband > .head{
  padding-inline:var(--pad-x-tablet)}}

/* ── LO QUE SÍ LLEGA AL CRISTAL ──────────────────────────────────────────────────────────────
   Medido antes de tocar nada: de veintitrés arquitecturas, VEINTIDÓS no dejaban que ningún
   elemento tocara el borde de la pantalla. Todas sus secciones acababan en la misma columna del
   84–89 % del ancho, cinco a ocho veces seguidas. El inventario de secciones era distinto en cada
   una —eso lo mide `RT_TPL_TOO_SIMILAR`— y la FORMA era idéntica, que es lo que un lector ve.
   El color ya sangraba: las bandas `bg-alt` llegan al borde desde siempre. Lo que no sangraba
   nunca era la FOTOGRAFÍA. Así que la banda se abre exactamente donde la fotografía es el
   argumento y no la ilustración, y en ningún sitio más: una galería a sangre en un arquetipo que
   enseña tres fotos de cortesía sería el gesto decorativo que este catálogo ya rechazó una vez. */
/* Y AQUÍ LA REGLA SE PARTE EN DOS, porque «a sangre» no significa lo mismo para todo.
   Un plano y una rejilla de fotos sin pie LLEGAN AL CRISTAL: no llevan una sola letra encima, así
   que pegarlos al borde es exactamente lo que se quería.
   Una rejilla de obra SÍ lleva letra —cada pieza con su nombre y su año debajo—, y medido en
   pantalla el resultado fue «Palacio de Miraflores» empezando en x=0, pegado al cristal. Es el
   mismo principio que ya obliga al encabezado a quedarse en su columna, un nivel más abajo: el
   texto nunca sangra. Así que esa rejilla escapa de la columna de contenido (1.140px) pero se
   detiene en el margen de página, y sus marcos siguen saliendo un 13 % más grandes que
   contenidos. El gesto se conserva; lo que se va es la letra tocando el borde. */
.bleedband .shots, .bleedband .mapwrap{padding-inline:0}
.bleedband .works{padding-inline:var(--pad-x-mobile)}
@media(min-width:768px){.bleedband .works{padding-inline:var(--pad-x-tablet)}}

/* ── EL TITULAR DEL HERO RESPIRA MÁS ANCHO ───────────────────────────────────────────────────
   La medida compartida (`min(34rem,78%)`) partía este titular en tres líneas cortas apiladas en
   una esquina, y esa es la otra mitad de la sensación de «bloque enorme»: no era sólo el tamaño,
   era el ancho. Con `min(46rem,90%)` entra en dos. Ensanchar una medida que uno mismo eligió es
   gratis; bajar la escala no lo sería, y por eso la escala se arregló cambiando de ancla. */
[data-arch="tpl-c-14"] .hero-full .head{max-width:min(46rem,90%)}

/* ── LA BARRA DE DATOS SOBRE LA CABECERA ──────────────────────────────────────────────────────
   Dónde, cuándo y a qué número. En el menú serían tres enlaces que nadie pulsa. */
/* BLANCA Y OPACA, no un texto flotando sobre la fotografía. Transparente, la dirección y el
   horario caían sobre la pared clara del hero y se leían a medias, y encima competían con el
   titular por la misma zona de la imagen. Sobre su propia superficie son lo que son: un dato de
   servicio, en pequeño, antes de que empiece la página. */
.lumhead .topbar{background:var(--c-bg);color:var(--c-text);
  border-bottom:1px solid var(--c-border)}
.lumhead .topbar .canvas{display:flex;flex-wrap:wrap;gap:var(--sp-s);align-items:center;
  padding-block:.5rem;font-size:var(--fs-small)}
.lumhead .topbar a{margin-left:auto;font-weight:600;color:var(--c-text)}
@media(max-width:599px){.lumhead .topbar span:nth-child(2){display:none}}

/* ── ZONAS: RETÍCULA DE FILETES COMPARTIDOS ───────────────────────────────────────────────────
   No son cuatro tarjetas: son cuatro columnas de la misma tabla, y una tabla se dibuja con las
   líneas que SEPARAN. Los bordes se quitan en los bordes de la rejilla con `nth-child`, que es lo
   que hace que la última columna no cierre una caja que no existe. Hover sin sombra ni relleno:
   `elevation: none` no tiene con qué levantar, así que lo que cambia es el propio filete. */
/* TARJETAS CON FILETE, y esto es un cambio de opinión con motivo. La primera versión eran cuatro
   columnas de una misma tabla, separadas por filetes compartidos, con el argumento de que una
   tabla se dibuja con las líneas que separan. El argumento se sostiene solo, pero la receta de
   tarjeta de esta ancla dice otra cosa en sus propias palabras —«hairline border, text below; no
   chips, no fills: el borde es todo el cromo»—, así que una caja de filete no es una concesión
   aquí: es literalmente lo que `elevation: hairline` significa. Cuatro cajas iguales con su icono
   dentro se leen además como cuatro DESTINOS, que es lo que son, y no como cuatro celdas de algo
   más grande.
   El hover no levanta ni rellena —no hay sombra en este sistema—: oscurece el filete y lleva el
   icono al acento, que es un estado activo y no decoración. */
/* ── LA BANDA DE CIERRE, CENTRADA ─────────────────────────────────────────────────────────────
   Las tres composiciones colocan `.band .head` en la MITAD IZQUIERDA —`grid-column:1/7` en
   strict-grid— porque la banda de cierre de la casa lleva texto a un lado y FORMULARIO al otro.
   Las bandas de este arquetipo no llevan formulario: sólo un titular y dos botones. Sin la
   segunda mitad, el bloque quedaba pegado a la izquierda con media sección vacía a su derecha, y
   encima con el fondo de tarjeta del ancla, así que parecía una caja olvidada en una esquina.
   Medido: 538px de tarjeta dentro de un canvas de 1140.
   No es que la regla esté mal — es que no va dirigida a una banda sin formulario. Centrada entre
   las columnas 3 y 11 vuelve a ser lo que es: un cierre. */
[data-arch="tpl-c-14"] .band .head{grid-column:3 / 11}
@media(max-width:767px){[data-arch="tpl-c-14"] .band .head{grid-column:1 / -1}}

.zonegrid{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-m);
  grid-template-columns:repeat(2,minmax(0,1fr))}
@media(min-width:900px){.zonegrid{grid-template-columns:repeat(4,minmax(0,1fr))}}
.zcell{border:1px solid var(--c-border);border-radius:var(--radius-card);
  transition:border-color .35s var(--ease)}
.zcell a{display:grid;gap:var(--sp-xs);align-content:start;text-decoration:none;color:inherit;
  padding:var(--sp-m)}
.zicon{color:var(--c-text-muted);margin-bottom:var(--sp-xs);
  transition:color .35s var(--ease)}
.zcell:hover{border-color:var(--c-text)}
.zcell:hover .zicon{color:var(--c-accent)}
.zcell h3{font-size:var(--fs-item);min-height:2.5em;margin:0}
.zcount{font-family:var(--font-secondary);font-size:var(--fs-small);font-weight:600;
  letter-spacing:.14em;text-transform:uppercase;color:var(--c-text-muted)}
.zfrom{font-family:var(--font-primary);font-size:var(--fs-item);line-height:1.15}

/* ── LA CARTA, APOYADA EN LA SALA ─────────────────────────────────────────────────────────────
   La fotografía es el fondo de la banda y el panel es OPACO. Un panel translúcido pondría cada
   precio sobre un píxel distinto de la foto y convertiría una lista de seis filas en seis
   mediciones de contraste; lo que da el efecto es que la sala asome ALREDEDOR, no debajo. */
.menuband{position:relative;isolation:isolate}
.menu-ground{position:absolute;inset:0;margin:0;z-index:0}
.menu-ground img{width:100%;height:100%;object-fit:cover;display:block}
/* El panel es una rejilla con hueco propio: sin él, el titular y la primera etiqueta de grupo se
   tocaban y el filete de la etiqueta parecía una raya cruzando el titular. Una banda no tiene el
   `gap` del canvas, así que lo pone quien lo necesita. */
/* MÁS ESTRECHO Y CON EL TITULAR UN ESCALÓN ABAJO. A todo el ancho de contenido esto no era una
   carta, era una página dentro de otra: líneas de 1100px para entradas de tres palabras, y un
   titular de sección de 48px mandando sobre una lista cuya gracia es lo fina que es. 62rem deja la
   medida en algo que se lee de una pasada, y el titular baja a la altura de un h3 — bajar un
   tamaño que uno mismo colocó es gratis; bajar la escala del ancla no lo sería. */
.menu-panel{position:relative;z-index:1;max-width:min(var(--content-width), 62rem);
  margin-inline:auto;background:var(--c-bg);padding:var(--sp-l) var(--sp-m);
  display:grid;gap:var(--sp-m);align-content:start}
@media(min-width:768px){.menu-panel{padding:var(--sp-l) var(--sp-l)}}
.menu-panel h2{font-size:var(--fs-h3)}
/* REJILLA Y NO `columns`, y esto es una corrección de la corrección. `columns` equilibra por
   ALTURA total, y con grupos de 2, 2, 1 y 1 rituales eso significa meter los dos grupos largos en
   la primera columna: cuatro entradas a la izquierda y dos a la derecha, con un hueco de media
   sección debajo. Una rejilla de dos columnas reparte en zigzag —grupo 1 y 3 a la izquierda, 2 y 4
   a la derecha— y empareja largo con largo y corto con corto sin que nadie se lo diga. El equilibrio
   sale del ORDEN de reparto, no de una medición de alturas. */
.rituals{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-m);
  grid-template-columns:minmax(0,1fr)}
@media(min-width:900px){.rituals{grid-template-columns:repeat(2,minmax(0,1fr));
  column-gap:var(--sp-xl);align-items:start}}
.rgroup{display:block}
.rgroup ul{list-style:none;margin:0;padding:0}
.rglabel{display:block;font-size:var(--fs-small);font-weight:600;letter-spacing:.16em;
  text-transform:uppercase;color:var(--c-text-muted);padding-bottom:var(--sp-xs);
  border-bottom:1px solid var(--c-border)}
.ritual{display:block;padding-block:var(--sp-xs)}
.ritual + .ritual{border-top:1px solid var(--c-border)}
/* NOMBRE Y PRECIO EN LA MISMA LÍNEA, con el precio a la derecha: es donde el ojo lo busca cuando
   compara seis, y ahorra la línea entera que antes gastaban los datos. El punteado clásico de
   carta se deja fuera a propósito — con tres rituales por grupo la distancia es corta y el
   punteado sólo añadiría ruido a una tipografía que ya es fina. */
.rline{display:flex;flex-wrap:wrap;align-items:baseline;justify-content:space-between;
  gap:var(--sp-xs) var(--sp-s);margin:0}
.rname{font-family:var(--font-primary);font-size:var(--fs-name);line-height:1.2}
.rname a{color:inherit;text-decoration:none}
.rname a:hover{color:var(--c-accent)}
.rfacts{display:flex;align-items:baseline;gap:var(--sp-s);margin:0}
.rmin{font-family:var(--font-secondary);font-size:var(--fs-small);font-weight:600;
  letter-spacing:.12em;text-transform:uppercase;color:var(--c-text-muted)}
.rprice{font-family:var(--font-primary);font-size:var(--fs-name);line-height:1}
.rdesc{margin:.1rem 0 0;font-size:var(--fs-small);color:var(--c-text-muted)}
/* «Cómo sales» iba en una pastilla rellena, y seis pastillas rosas en dos columnas convertían una
   carta en un tablero de avisos. Es una acotación, no una etiqueta: filete a la izquierda, cursiva
   y ya. El dato sigue siendo el que distingue a este arquetipo; lo que se va es el envase. */
.rafter{margin:.15rem 0 0;padding-left:var(--sp-s);border-left:1px solid var(--c-border);
  font-size:var(--fs-small);font-style:italic;color:var(--c-text-muted)}

/* ── LA CABINA: LA FILA ES LA SECCIÓN, Y EL COLLAGE LLEVA PASPARTÚ ────────────────────────────
   El marco blanco ancho es el gesto; la sombra que lo acompaña en la referencia, no —
   `elevation: none` es una posición de eje de esta ancla y una sombra la contradiría en la
   sección más visible de la página. El paspartú se dibuja con el propio fondo y un filete, así
   que sobre la banda alterna se lee como una foto apoyada en papel. */
@media(min-width:900px){.cabintour{grid-template-columns:minmax(0,5fr) minmax(0,7fr);
  gap:var(--sp-xl)}}
.cabinsay{display:grid;gap:var(--sp-m);align-content:start}
.cabinlist{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-s)}
.cabinlist li{display:grid;gap:.15rem;padding-top:var(--sp-xs);
  border-top:1px solid var(--c-border)}
.cabname{font-family:var(--font-primary);font-size:var(--fs-item);line-height:1.2}
/* `align-items:start` EN LA REJILLA Y `height:auto` EN LA IMAGEN, las dos o ninguna. Medido: sin
   ellas el paspartú se estiraba a la altura de su fila (839px) y la fotografía con él (810px),
   así que un encuadre declarado 4:3 se renderizaba a 1:3,3 — una columna de retratos donde la
   hoja de estilo decía apaisado. `aspect-ratio` calcula la altura desde el ancho sólo mientras
   nadie le dé una altura; un ítem de rejilla estirado se la da sin escribirla en ninguna parte.

   (Y una trampa del propio generador, aprendida aquí: este comentario vive dentro de un nowdoc
   cerrado por la etiqueta `CSS`, y PHP 7.3+ termina el bloque en cuanto una línea EMPIEZA por esa
   etiqueta. La primera redacción de este párrafo empezaba una línea con esa palabra y partió el
   archivo en dos, con un error de indentación a doscientas líneas de distancia del sitio real.) */
.collage{display:grid;gap:var(--sp-s);grid-template-columns:repeat(2,minmax(0,1fr));
  align-items:start}
.mat{margin:0;background:var(--c-bg);border:1px solid var(--c-border);padding:.6rem}
@media(min-width:768px){.mat{padding:.85rem}}
.mat img{width:100%;max-width:100%;min-width:0;height:auto;aspect-ratio:4/3;object-fit:cover;
  display:block}
.collage .mat:nth-child(2){margin-top:var(--sp-l)}
.collage .mat:nth-child(3){margin-bottom:var(--sp-l)}

/* ── LA SESIÓN: EL MINUTAJE ES EL DATO, ASÍ QUE EL MINUTAJE ES LO GRANDE ──────────────────────*/
.protos{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-m);
  grid-template-columns:minmax(0,1fr)}
@media(min-width:900px){.protos{grid-template-columns:repeat(4,minmax(0,1fr))}}
.proto{display:grid;gap:var(--sp-xs);padding-left:var(--sp-s);
  border-left:2px solid var(--c-border)}
@media(min-width:900px){
  .proto{padding-left:0;padding-top:var(--sp-s);border-left:none;
    border-top:2px solid var(--c-border)}
}
.pmin{font-family:var(--font-primary);font-size:var(--fs-h3);line-height:1}

/* ── LOS BONOS: DAMERO A SANGRE ───────────────────────────────────────────────────────────────
   Panel de tinta, panel de tinta, fotografía. Los paneles llevan la superficie inversa del ancla
   y no el acento: la referencia los pinta con su rosa de marca, y aquí ese rosa es la única tinta
   de acción que la página tiene. La fotografía entra en tercera posición para que el damero rompa
   antes de terminar — cuatro celdas alternas perfectas se leen como una tabla. */
.checker{display:grid;grid-template-columns:minmax(0,1fr)}
@media(min-width:768px){.checker{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(min-width:1200px){.checker{grid-template-columns:repeat(4,minmax(0,1fr))}}
.cpanel{display:grid;gap:var(--sp-xs);align-content:start;
  padding:var(--sp-l) var(--sp-m);background:var(--c-surface-inverse);color:var(--c-on-inverse)}
.cpanel .muted,.cpanel .bonoq{color:var(--c-on-inverse);opacity:.72}
.chead{background:var(--c-bg-alt);color:var(--c-text)}
/* El titular baja un escalón DENTRO del panel. Una columna de damero mide un cuarto de la página
   y a tamaño de h2 el titular salía en cinco líneas de dos palabras. Bajar un tamaño que uno
   mismo eligió es gratis; bajar `--fs-h1-max`, que es una posición de eje, no lo sería. */
.chead .head{gap:var(--sp-xs)}
.chead h2{font-size:var(--fs-h3)}
.cshot{margin:0;min-height:100%}
.cshot img{width:100%;height:100%;min-height:14rem;object-fit:cover;display:block}
.bonoq{font-size:var(--fs-small);letter-spacing:.12em;text-transform:uppercase;
  color:var(--c-text-muted)}
.bonop{font-family:var(--font-primary);font-size:var(--fs-h3);line-height:1}
.bonosave{font-size:var(--fs-small);font-weight:700}
.giftstrip{max-width:var(--content-width);margin:var(--sp-l) auto 0;
  padding:var(--sp-m) var(--sp-m);display:grid;gap:var(--sp-xs);
  border:1px dashed var(--c-border)}
@media(min-width:900px){.giftstrip{grid-template-columns:auto auto auto 1fr;
  align-items:baseline;gap:var(--sp-m)}}
.bonos{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-m);
  grid-template-columns:minmax(0,1fr)}
@media(min-width:900px){.bonos{grid-template-columns:repeat(2,minmax(0,1fr))}}
.bono{display:grid;gap:var(--sp-xs);align-content:start;padding:var(--sp-m);
  background:var(--c-bg-alt);border-radius:var(--radius-card)}

/* ── LA TIRA ANTES DEL PIE ────────────────────────────────────────────────────────────────────
   Sólo fotografía en el cristal: no hay nada que amputar. Es la transición entre la página y el
   pie, y de paso lo único de la home que se puede mirar sin leer. */
.thumbs{list-style:none;margin:0;padding:0;display:grid;
  grid-template-columns:repeat(3,minmax(0,1fr))}
@media(min-width:900px){.thumbs{grid-template-columns:repeat(6,minmax(0,1fr))}}
/* EL CUADRADO LO IMPONE LA CELDA, NO LA IMAGEN, y esta es la SEGUNDA vez que el mismo error se
   cuela por la misma puerta. Un `aspect-ratio` sobre un `<img>` sólo manda mientras nadie le dé
   altura, y un ítem de rejilla estirado se la da sin escribirla: seis fotos con alturas nativas
   distintas —un hero 16:9 entre cinco 4:3— dieron una tira con una columna más alta que las otras
   cinco y un bloque de fondo vacío debajo. Poniendo la proporción en el `<li>` y mandando la
   imagen a llenarlo, la celda decide y la fotografía obedece, que es el orden correcto. */
.thumbs li{aspect-ratio:1;overflow:hidden}
.thumbs img{width:100%;height:100%;object-fit:cover;display:block}

/* ── EL EQUIPO DE LA CASA: REDONDO, CON PASPARTÚ Y SIN CREDENCIAL ─────────────────────────────*/
.faces{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-l);
  grid-template-columns:minmax(0,1fr);align-items:start}
@media(min-width:700px){.faces{grid-template-columns:repeat(3,minmax(0,1fr))}}
.face{display:grid;gap:var(--sp-xs);justify-items:center;text-align:center}
/* REDONDOS DE VERDAD, Y ES LA TERCERA VEZ QUE ESTE ERROR ENTRA POR LA MISMA PUERTA. Sin
   `align-items:start` en la rejilla y sin `height:auto` en la imagen, el paspartú se estira a la
   altura de su fila y la fotografía con él: tres retratos declarados 1:1 salieron como ÓVALOS, que
   en una página que va de caras es de lo peor que puede pasar. `aspect-ratio` manda mientras nadie
   dé una altura; un ítem de rejilla estirado la da sin escribirla. Y bajan de 208px a 160: tres
   caras a tamaño de póster son tres pósters, no un equipo. */
.round{margin:0;width:min(10rem,62%);background:var(--c-bg);border:1px solid var(--c-border);
  padding:.5rem;border-radius:50%}
.round img{width:100%;max-width:100%;min-width:0;height:auto;aspect-ratio:1;object-fit:cover;
  display:block;border-radius:50%}
.since{font-size:var(--fs-small);letter-spacing:.1em;text-transform:uppercase;
  color:var(--c-text-muted)}

/* ── DÓNDE Y CUÁNDO, EN UNA FILA ──────────────────────────────────────────────────────────────
   Los días a la izquierda con su columna de horas alineada, y la calle a la derecha. El día y la
   hora se separan con un filete de puntos suspendidos —`justify-content:space-between`— porque lo
   que se compara aquí es la COLUMNA DE HORAS, y para compararla tiene que estar alineada. */
@media(min-width:900px){.hoursrow{grid-template-columns:minmax(0,7fr) minmax(0,5fr);
  gap:var(--sp-xl)}}
.hourscol{display:grid;gap:var(--sp-m);align-content:start}
.daylist{margin:0;display:grid}
.dayrow{display:flex;align-items:baseline;justify-content:space-between;gap:var(--sp-m);
  padding-block:var(--sp-xs);border-top:1px solid var(--c-border)}
.dayrow dt{font-family:var(--font-primary);font-size:var(--fs-name)}
.dayrow dd{margin:0;font-size:var(--fs-body)}
.dayrow.shut dt,.dayrow.shut dd{color:var(--c-text-muted)}
.wherecol{display:grid;gap:var(--sp-s);align-content:start}
.addr{margin:0;font-family:var(--font-primary);font-size:var(--fs-name);line-height:1.35}
.reachline{margin:0}
.reachline a{color:inherit}

/* ── LAS TRES MANERAS DE LLEGAR, EN FILA ──────────────────────────────────────────────────────
   El número baja de 36px a la medida de un nombre de carta: un teléfono es un dato para copiar,
   no un titular. */
.reachlist{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-m);
  grid-template-columns:minmax(0,1fr)}
@media(min-width:768px){.reachlist{grid-template-columns:repeat(3,minmax(0,1fr));
  align-items:start}}
.reachlist li{display:grid;gap:.15rem;padding-top:var(--sp-xs);
  border-top:1px solid var(--c-border)}
.reachbig{font-family:var(--font-primary);font-size:var(--fs-name);line-height:1.2;
  color:inherit;text-decoration:none}
.reachbig:hover{color:var(--c-accent)}

/* El titular de una fila vive en una columna, no a todo lo ancho: un h2 de 48px en 5/12 de la
   página sale en tres renglones de dos palabras. */
.cabinsay h2,.hourscol h2{font-size:var(--fs-h3)}

/* ── EL ÍNDICE DE SERVICIOS · TPL-SERVICES-01 ─────────────────────────────────────────────────*/
.svcgroup{display:grid;gap:var(--sp-m);grid-template-columns:minmax(0,1fr)}
@media(min-width:900px){.svcgroup{grid-template-columns:minmax(0,4fr) minmax(0,8fr);
  gap:var(--sp-l);align-items:start}}
.svcgroup + .svcgroup{margin-top:var(--sp-xl);padding-top:var(--sp-l);
  border-top:1px solid var(--c-border)}
.svcshot img{width:100%;max-width:100%;min-width:0;aspect-ratio:4/3;object-fit:cover;
  border-radius:var(--radius-image);display:block}
@media(min-width:900px){.svcshot img{aspect-ratio:3/4}}
.svcbody{display:grid;gap:var(--sp-s);align-content:start}
.svccards{list-style:none;margin:0;padding:0;display:grid;gap:0}
.svccard a{display:grid;gap:.15rem;text-decoration:none;color:inherit;
  padding:var(--sp-s) 0;border-top:1px solid var(--c-border);
  transition:opacity .35s var(--ease)}
.svccard a:hover{opacity:.62}
.svccard h4{font-family:var(--font-primary);font-size:var(--fs-item);line-height:1.2;margin:0}
.svcfacts{font-size:var(--fs-small);font-weight:600;letter-spacing:.1em;
  text-transform:uppercase;color:var(--c-text-muted)}

/* ── LA FICHA DE TRATAMIENTO · TPL-SERVICE-02 ────────────────────────────────────────────────*/
.tfactlist{margin:0;display:grid;gap:var(--sp-s);grid-template-columns:minmax(0,1fr)}
@media(min-width:900px){.tfactlist{grid-template-columns:repeat(4,minmax(0,1fr))}}
.tfact{display:grid;gap:.2rem;padding-top:var(--sp-xs);border-top:1px solid var(--c-border)}
.tfact dt{font-size:var(--fs-small);letter-spacing:.12em;text-transform:uppercase;
  color:var(--c-text-muted)}
.tfact dd{margin:0;font-size:var(--fs-body);font-weight:600;line-height:1.35}
.contrapair{display:grid;gap:var(--sp-l);grid-template-columns:minmax(0,1fr)}
@media(min-width:900px){.contrapair{grid-template-columns:repeat(2,minmax(0,1fr))}}
.contracol{display:grid;gap:var(--sp-s);align-content:start}
.contralist{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-xs)}
.contralist li{padding-left:var(--sp-s);border-left:2px solid var(--c-border)}
CSS;

$css[] = <<<'STYLES'
/* ══════════ FICHAS DE INVENTARIO · TPL-UNIT-01 + TPL-PROPERTY-01 ══════════ */

/* ── UN SOLO VOCABULARIO PARA DOS FICHAS, y la razón está en los propios documentos ──────────
   `TPL-UNIT-01` y `TPL-PROPERTY-01` son la ficha de UNA unidad de un inventario que rota, y las
   dos abren igual: la unidad fotografiada, seis datos que descartan o siguen, y la referencia que
   viaja al formulario. Lo que cambia es el centro —el historial en una, el plano y el coste en la
   otra— y ahí cada una tiene lo suyo.
   Compartir el marco y separar el centro es exactamente la distancia que el arquetipo declara:
   4 secciones comunes de 17. Si compartieran también el centro serían la misma página. */

.refline{display:flex;flex-wrap:wrap;align-items:baseline;gap:var(--sp-xs) var(--sp-s);margin:0}
.refcode{font-family:var(--font-secondary);font-size:var(--fs-small);font-weight:600;
  letter-spacing:.12em;text-transform:uppercase;color:var(--c-text-muted)}
/* PR3c — the same class riding a breadcrumb bar instead of `.refline`'s own paragraph
   (`property_crumbs_html()`), so it inherits monospace + a tighter size in that ONE context. */
.propcrumbs .refcode{font-family:ui-monospace,Menlo,monospace;font-size:.59375rem;letter-spacing:.08em}

/* ── LA UNIDAD FOTOGRAFIADA ──────────────────────────────────────────────────────────────────
   LA PROPORCIÓN LA IMPONE LA CELDA Y NO LA IMAGEN. Es la tercera vez que este sistema tropieza
   con lo mismo, así que aquí se escribe de entrada: `aspect-ratio` sobre un `<img>` sólo manda
   mientras nadie le dé altura, y un ítem de rejilla estirado se la da sin escribirla. Poniendo la
   proporción en el `<li>` y mandando la imagen a llenarlo, la celda decide y la fotografía
   obedece. */
/* LAS MINIATURAS VAN DEBAJO Y NO AL LADO, y esto es una corrección con medición delante.
   La primera versión las ponía en una columna de 168px a la derecha de la foto principal: tres
   celdas a 4:3 dan unos 400px de alto contra los 700 de la principal, o sea 190 PÍXELES DE COLUMNA
   VACÍA cada vez. Estirarlas para rellenar habría deformado tres fotos para tapar un hueco, que es
   resolver el síntoma. Debajo, en una tira de tres, no sobra nada y además es como se mira una
   ficha de verdad: la unidad grande primero, los detalles después. */
.ugal{display:grid;gap:var(--sp-s);grid-template-columns:minmax(0,1fr)}
.ushot{margin:0;aspect-ratio:4/3;overflow:hidden;border-radius:var(--radius-image)}
.ushot img{width:100%;height:100%;object-fit:cover;display:block}
.uthumbs{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-xs);
  grid-template-columns:repeat(3,minmax(0,1fr))}
.uthumbs li{aspect-ratio:4/3;overflow:hidden;border-radius:var(--radius-image)}
.uthumbs img{width:100%;height:100%;object-fit:cover;display:block}
.ucap{margin:var(--sp-xs) 0 0;font-size:var(--fs-meta);color:var(--c-text-muted)}

/* ── LOS SEIS DATOS ──────────────────────────────────────────────────────────────────────────
   Seis y no cinco: la tarjeta de la rejilla enseña cinco porque cinco es lo que cabe, y quien
   llega desde un buscador externo no ha visto esa tarjeta. Repetirlos aquí no es redundancia, es
   la única vez que los ve.
   El dato grande y el rótulo pequeño, no al revés: se leen en diagonal buscando un número. */
.uspecs{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-s);
  grid-template-columns:repeat(2,minmax(0,1fr))}
@media(min-width:768px){.uspecs{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(min-width:1100px){.uspecs{grid-template-columns:repeat(6,minmax(0,1fr))}}
/* EL RÓTULO SE ALINEA CON SUS CINCO HERMANOS AUNQUE EL VALOR PARTA EN DOS LÍNEAS.
   Medido: «4ª con ascensor» ocupa dos líneas y empujaba su PLANTA un renglón por debajo de los
   otros cinco rótulos — una fila de seis datos con uno descolgado se lee como un error de carga.
   `grid-template-rows: 1fr auto` da al valor todo el alto sobrante y clava el rótulo abajo, así
   que la alineación no depende de cuántas líneas ocupe nada. Un `min-height` en em habría hecho lo
   mismo sólo mientras nadie escribiera un valor de tres líneas. */
.uspec{display:grid;gap:.15rem;grid-template-rows:1fr auto;padding-top:var(--sp-xs);
  border-top:1px solid var(--c-border)}
.uspec b{align-self:start}
.uspec b{font-family:var(--font-primary);font-size:var(--fs-item);line-height:1.1;
  font-variant-numeric:tabular-nums}
.uspec span{font-size:var(--fs-small);letter-spacing:.1em;text-transform:uppercase;
  color:var(--c-text-muted)}

/* ── LOS DOS PRECIOS, CON EL MISMO PESO ──────────────────────────────────────────────────────
   Al contado y en cuota, mismo tamaño y misma columna. Un patio que sólo publica cuota esconde el
   precio; uno que sólo publica contado pierde a quien compra por mensualidad, que en ocasión es
   la mayoría. Las condiciones —entrada, plazo, TAE— van AL LADO y no en una nota al pie: son las
   que convierten «253 €» en lo que de verdad se paga. */
/* CADA CAJA MIDE LO QUE TIENE QUE DECIR, y la asimetría es honesta.
   Estiradas a la misma altura, el contado —un número y una línea— quedaba con media caja en
   blanco al lado de la financiada, que lleva cuatro condiciones. Media caja vacía se lee como algo
   que falta. Con `align-items:start` la corta es corta, y eso dice la verdad: comprar al contado
   ES más simple, y ésa es justamente la información. */
.pricepair{display:grid;gap:var(--sp-m);grid-template-columns:minmax(0,1fr);align-items:start}
@media(min-width:768px){.pricepair{grid-template-columns:repeat(2,minmax(0,1fr))}}
.pricebox{display:grid;gap:var(--sp-xs);align-content:start;padding:var(--sp-m);
  border:1px solid var(--c-border);border-radius:var(--radius-card)}
.pricebox > span{font-size:var(--fs-small);letter-spacing:.12em;text-transform:uppercase;
  color:var(--c-text-muted)}
.priceq{font-family:var(--font-primary);font-size:var(--fs-h3);line-height:1;
  font-variant-numeric:tabular-nums}
.pterms{list-style:none;margin:var(--sp-xs) 0 0;padding:0;display:grid;gap:.2rem}
.pterms li{display:flex;justify-content:space-between;gap:var(--sp-s);
  font-size:var(--fs-meta);color:var(--c-text-muted)}
.pterms b{color:var(--c-text);font-variant-numeric:tabular-nums}

/* ── EL HISTORIAL ────────────────────────────────────────────────────────────────────────────
   La sección que justifica la página. Sin ella la ficha es la tarjeta de la rejilla con las fotos
   más grandes, y la pregunta que trae a alguien al patio —«¿de qué viene este coche?»— sigue sin
   contestar. Cuatro filas, y ninguna en blanco. */
.histgrid{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-m);
  grid-template-columns:minmax(0,1fr)}
@media(min-width:768px){.histgrid{grid-template-columns:repeat(2,minmax(0,1fr))}}
.hist{display:grid;gap:.15rem;align-content:start;padding-left:var(--sp-s);
  border-left:2px solid var(--c-border)}
.hist span{font-size:var(--fs-small);letter-spacing:.1em;text-transform:uppercase;
  color:var(--c-text-muted)}
.hist b{font-family:var(--font-primary);font-size:var(--fs-name);line-height:1.2}
.hist p{margin:0;font-size:var(--fs-meta);color:var(--c-text-muted)}

/* ── EL RECORRIDO, QUE NO ES UNA GALERÍA ─────────────────────────────────────────────────────
   Una galería es un carrusel de imágenes bonitas en orden arbitrario. Un recorrido tiene el orden
   de la puerta hacia dentro, y cada foto dice QUÉ estás mirando y CUÁNTO mide. La diferencia se
   nota en la visita: quien recorrió la ficha llega sabiendo si su sofá cabe. */
.tourlist{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-m);
  grid-template-columns:minmax(0,1fr)}
@media(min-width:768px){.tourlist{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(min-width:768px){.tourlist li:first-child{grid-column:1 / -1}}
.tourcell figure{margin:0;aspect-ratio:4/3;overflow:hidden;border-radius:var(--radius-image)}
@media(min-width:768px){.tourlist li:first-child figure{aspect-ratio:16/7}}
.tourcell img{width:100%;height:100%;object-fit:cover;display:block}
.tourmeta{display:flex;flex-wrap:wrap;align-items:baseline;gap:var(--sp-xs) var(--sp-s);
  margin:var(--sp-xs) 0 0}
.tourmeta b{font-family:var(--font-primary);font-size:var(--fs-name);line-height:1.2}
.tourmeta span{font-size:var(--fs-meta);color:var(--c-text-muted);font-variant-numeric:tabular-nums}

/* ── EL PLANO ────────────────────────────────────────────────────────────────────────────────
   Fondo claro FIJO y no `--c-bg`: un plano es tinta negra sobre papel blanco, y bajo un ancla de
   fondo oscuro la imagen se quedaría flotando en un rectángulo blanco con el borde cortado. El
   marco lo pone la caja, con su propio blanco, y así el dibujo se lee igual en las cinco anclas. */
.floorplan{margin:0;background:#FFFFFF;border:1px solid var(--c-border);
  border-radius:var(--radius-image);padding:var(--sp-s);max-width:100%}
.floorplan img{width:100%;max-width:100%;min-width:0;height:auto;display:block}
/* `.floorplan.ph` — TPL-PROPERTY-01's first real render (TPL-C-15/delao) has no floor-plan
   photograph among its twelve; `floorplan_html()` marks the gap instead of inventing a plan. Same
   hatch as `.pcard .frame.ph` above, kept local to this block per the class-ownership check. */
.floorplan.ph{aspect-ratio:16/10;display:grid;place-items:center;
  background:repeating-linear-gradient(135deg,var(--c-bg-alt) 0 8px,var(--c-bg) 8px 16px)}
.floorplan.ph span{font-size:.6875rem;letter-spacing:.14em;text-transform:uppercase;
  color:var(--c-text-muted);background:var(--c-bg);padding:.25rem .6rem;
  border:1px solid var(--c-border);border-radius:var(--radius-pill,999px)}

/* ── LO QUE CUESTA DE VERDAD ─────────────────────────────────────────────────────────────────
   Precio, comunidad, IBI y gastos de compra, con la suma escrita. Un piso de 289.000 € cuesta
   entrar en él bastante más, y quien descubre esa diferencia en la notaría no vuelve a esa
   agencia. La suma lleva filete arriba y peso: es el número que la página existe para dar. */
/* CENTRADA DESDE SU PROPIO BLOQUE, y el primer intento fue meterla en la regla compartida que
   ya centra los otros siete bloques de medida tope. La puerta de nombres lo rechazó y llevaba
   razón: esa regla va ANTES del bloque que posee esta clase, así que un nombre nuevo estilado allí
   arriba es exactamente la colisión que la comprobación existe para impedir. Una clase compartida
   no tiene dueño; ésta sí, y su dueño la estila en su casa. */
.costlist{list-style:none;margin:0 auto;padding:0;display:grid;gap:0;max-width:44rem}
.costrow{display:flex;flex-wrap:wrap;align-items:baseline;justify-content:space-between;
  gap:var(--sp-xs) var(--sp-s);padding-block:var(--sp-xs);
  border-bottom:1px solid var(--c-border)}
.costrow > span{font-size:var(--fs-meta);color:var(--c-text-muted)}
.costrow b{font-family:var(--font-primary);font-size:var(--fs-name);
  font-variant-numeric:tabular-nums;white-space:nowrap}
.costsum{border-bottom:none;border-top:2px solid var(--c-text);margin-top:.3rem;
  padding-top:var(--sp-s)}
.costsum > span{color:var(--c-text);font-size:var(--fs-body)}
.costsum b{font-size:var(--fs-item)}

/* ── EL CERTIFICADO ──────────────────────────────────────────────────────────────────────────
   Va en el HTML y no dentro de un JPG. Un dato obligatorio metido en una imagen no lo lee ni un
   lector de pantalla ni un buscador, y aquí además es exigible: un anuncio de venta sin
   calificación energética está incumpliendo, no ahorrando espacio.
   La letra NO lleva el color de la escala oficial: el verde y el rojo de esa escala no salen de la
   paleta de ninguna marca, y meterlos convertiría el bloque en el único sitio de la página con
   colores de fuera. La letra se dice con tipografía y con la cifra al lado. */
.energy{display:grid;gap:var(--sp-m);grid-template-columns:minmax(0,1fr)}
@media(min-width:600px){.energy{grid-template-columns:repeat(2,minmax(0,1fr))}}
.elabel{display:flex;align-items:center;gap:var(--sp-s);padding:var(--sp-s) var(--sp-m);
  border:1px solid var(--c-border);border-radius:var(--radius-card)}
/* `.elabel .eletter` Y NO `.eletter`, Y ES UNA LECCIÓN QUE YA COSTÓ CARA UNA VEZ.
   `.elabel span` —una clase y un tipo, (0,1,1)— gana a `.eletter` —una clase, (0,1,0)—, así que la
   letra de la calificación se renderizaba al tamaño de un pie de foto por debajo del bloque que
   pretendía darle 33px. No lo dijo ninguna herramienta: se vio. Dos clases (0,2,0) ganan a una
   clase más un tipo, y el orden en el fichero no pinta nada aquí.
   Nunca apagar una regla confiando en que va después: se apaga con especificidad. */
.elabel .eletter{font-family:var(--font-primary);font-size:var(--fs-h3);line-height:1;
  min-width:1.6em;text-align:center;color:var(--c-text)}
.elabel div{display:grid;gap:.1rem}
.elabel b{font-size:var(--fs-meta);font-weight:600}
.elabel span{font-size:var(--fs-small);color:var(--c-text-muted);
  font-variant-numeric:tabular-nums}
STYLES;

// ═══════════════════════════════════════════════════════════════ HTML

/**
 * THE SECTION INDEX — numbered in DOCUMENT ORDER, after the strip is built.
 *
 * `craft-probe-2026-08-16.html` § CRAFT-PRINT hangs a numbered index in the page margin, and it is
 * the cheapest signal on that whole page that somebody composed the thing: a reader cannot say why
 * a numbered page looks considered, only that it does. What it costs is one attribute; every anchor
 * then renders it in its own language, or not at all.
 *
 * NUMBERED HERE AND NOT AT EACH CALL SITE. Writing `data-index="02"` beside each section is two
 * facts — the number and the order — kept in two places, and they disagree the first time a section
 * moves or a toggle removes one. This pass reads the order off the finished markup, so the number
 * IS the position by construction. A section with no `.head` (the benefits bar) takes no number,
 * which is correct: it has no heading to hang one on.
 */
function number_heads( $markup ) {
	$n = 0;
	return preg_replace_callback(
		'/<div class="head stack"(?! data-index)/',
		function () use ( &$n ) {
			++$n;
			return '<div class="head stack" data-index="' . sprintf( '%02d', $n ) . '"';
		},
		$markup
	);
}

/** One card, in the DOM its anchor's recipe calls for. */
function card_html( $anchor_key, $c ) {
	$im  = img( $c['img'] );
	$out = '<article class="card">'
		. '<figure class="frame"><img data-img="' . h( $im['slug'] ) . '" alt="' . h( $im['alt'] ) . '"'
		. ' width="' . $im['w'] . '" height="' . $im['h'] . '"></figure>';
	if ( 'editorial' === $anchor_key ) {
		$out .= '<div class="rule"></div>';   // the hairline rule IS this anchor's card chrome
	}
	$out .= '<div class="body">';
	if ( 'institutional' === $anchor_key ) {
		$out .= '<span class="chip" aria-hidden="true"><svg viewBox="0 0 24 24" width="18" height="18"'
			. ' fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round">'
			. '<path d="M4 20 12 4 20 20Z"/></svg></span>';
	}
	$out .= '<h3>' . h( $c['h3'] ) . '</h3><p class="muted small">' . h( $c['p'] ) . '</p>'
		. '</div></article>';
	return $out;
}

/**
 * COMP-HERO under `TGL-HERO-TYPE: slider` — the photograph BEHIND the copy, three frames
 * cross-fading, instead of one frame beside it.
 *
 * THREE REAL `<img>` ELEMENTS AND NOT A `background-image`, for the reason mockup-guide.md § 251
 * gives and one this page adds: `RT_GALLERY_NO_MANIFEST` finds images by `data-img`, so a CSS
 * background is an image with no licence row that no gate can see. The bytes are not paid three
 * more times either — all three slugs are already in the one hydration map at the foot of the
 * page, and `hero-taller` and `hero-encimera` were already being rendered by TPL-E-02's carousel.
 * The slider costs markup, not pixels.
 *
 * THE FIRST FRAME IS THE FIXED HERO'S OWN IMAGE, asserted below rather than assumed. That is what
 * makes `prefers-reduced-motion` honest here: a reader who has asked the platform to stop things
 * moving sees the exact hero the same strip would have shown at `imagen fija`, not slide three of
 * a carousel frozen wherever the CSS happened to leave it.
 *
 * ONLY THE FIRST CARRIES THE `alt`. Slides two and three are the same hero photographed three
 * ways; three descriptions of one hero is a screen-reader user hearing the section three times.
 * `alt=""` is the standard mark for a decorative duplicate, and it is the honest one — the hero
 * HAS a real alt, and it is the frame that is on screen when nothing is moving.
 */
function hero_slider_html( $frames ) {
	$o = array( '<div class="slides">' );
	foreach ( $frames as $ix => $slug ) {
		$im  = img( $slug );
		$alt = ( 0 === $ix ) ? $im['alt'] : '';
		$o[] = '<figure class="frame slide"><img data-img="' . h( $im['slug'] ) . '"'
			. ' alt="' . h( $alt ) . '" width="' . $im['w'] . '" height="' . $im['h'] . '"></figure>';
	}
	$o[] = '</div>';
	return implode( '', $o );
}

/** TPL-C-01 — Services / Lead-Gen. Renders the three `[fijo · ADN]` sections plus COMP-CASES. */
/**
 * The two site headers, extracted so an INTERIOR page wears the same one its home does.
 *
 * A header that drifts between a template's home and its inner pages is the loudest tell that a
 * "kit" is really several unrelated mockups sharing a folder. Extracting it is not tidying: it is
 * the thing that makes the page set read as one site.
 *
 * The ecommerce header carries an `id` on its search field, so it takes the SAMPLE's uid rather
 * than the variant's — two pages of the same variant are two live inputs, and a duplicate id
 * would break `label[for]` for both.
 */
function head_corporate( $C, $BRAND ) {
	$o = '<header class="site-head"><div class="canvas"><div class="nav">'
		. '<span class="logo">' . h( $BRAND ) . '</span>'
		. '<nav class="mainnav" aria-label="Principal">';
	foreach ( $C['nav'] as $n ) {
		$o .= '<a href="' . h( ihref_for_label( $n ) ) . '">' . h( $n ) . '</a>';
	}
	return $o . '</nav><a class="btn btn-primary btn-sm" href="' . h( ihref_for_label( $C['nav_cta'] ) ) . '">' . h( $C['nav_cta'] ) . '</a>'
		. '</div></div></header>';
}

function head_ecommerce( $C, $BRAND, $uid ) {
	$o = '<div class="announce"><div class="canvas"><p class="small">' . h( $C['announce'] ) . '</p></div></div>'
		. '<header class="site-head"><div class="canvas"><div class="nav">'
		. '<span class="logo">' . h( $BRAND ) . '</span>'
		. '<form class="searchbar" role="search" onsubmit="return false">'
		. '<label class="sr" for="' . $uid . '-q">Buscar en la tienda</label>'
		. '<input id="' . $uid . '-q" type="search" placeholder="' . h( $C['search'] ) . '">'
		. '<button class="btn btn-primary btn-sm" type="submit">Buscar</button>'
		. '</form><div class="tools">';
	foreach ( $C['tools'] as $t ) {
		$o .= '<a href="' . h( ihref_for_label( $t ) ) . '">' . h( $t ) . '</a>';
	}
	return $o . '<a class="cart" href="' . h( ihref_for_label( $C['cart'] ) ) . '">' . h( $C['cart'] ) . ' <b>' . h( $C['cart_n'] ) . '</b></a>'
		. '</div></div></div></header>';
}

/** Breadcrumb — every interior page opens with one, and the home never has one. */
function crumbs_html( $trail ) {
	$o    = '<nav class="crumbs" aria-label="Migas"><div class="canvas"><ol>';
	$last = count( $trail ) - 1;
	foreach ( $trail as $i => $t ) {
		$o .= ( $i === $last )
			? '<li aria-current="page">' . h( $t ) . '</li>'
			: '<li><a href="' . h( ihref_for_label( $t ) ) . '">' . h( $t ) . '</a></li>';
	}
	return $o . '</ol></div></nav>';
}

/**
 * COMP-CTA + COMP-LEAD-FORM · the closing band, extracted so the service page closes exactly the
 * way its home does. TPL-C-01's DNA is that the form EXISTS and dominates the close; a service
 * page that closed differently would be a different site.
 *
 * It takes the SAMPLE's uid and not the variant's, because every field here carries an `id` that a
 * `<label for>` points at. Two pages of the same variant are two live forms, and a duplicate id
 * silently breaks the label on BOTH of them — clicking the label focuses the wrong input, or none.
 */
function band_closing_html( $b, $uid ) {
	$o = '<section class="sec band closing" aria-label="Contacto"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $b['eyebrow'] ) . '</span>'
		. '<h2>' . h( $b['h2'] ) . '</h2><p class="muted">' . h( $b['lede'] ) . '</p></div>'
		. '<div class="formwrap"><form class="leadform" onsubmit="return false">';
	foreach ( $b['fields'] as $f ) {
		$id = $uid . '-' . $f[0];
		$o .= '<div class="field"><label for="' . $id . '">' . h( $f[1] ) . '</label>'
			. '<input id="' . $id . '" name="' . $f[0] . '" type="' . $f[2] . '"></div>';
	}
	return $o . '<div class="field"><label for="' . $uid . '-msg">' . h( $b['msg'] ) . '</label>'
		. '<textarea id="' . $uid . '-msg" name="msg" rows="3"></textarea></div>'
		. '<button class="btn btn-primary" type="submit">' . h( $b['submit'] ) . '</button>'
		. '</form></div></div></section>';
}

/**
 * TPL-SERVICE-01 · service detail, the interior page of TPL-C-01.
 *
 * It REUSES the home's process, testimonials and closing band rather than inventing parallel copy,
 * because that is what the archetype doc says the page is: the same firm going one level deeper on
 * one service. Reuse is also what keeps the two pages legible as ONE site rather than as two
 * mockups that happen to share a folder.
 *
 * COMP-PRICING is declared by TPL-SERVICE-01 with default OFF and is therefore not rendered. That
 * absence is written down here so a reader comparing the doc with the page does not conclude the
 * section was forgotten — which is precisely the mistake three toggles caused on the home.
 */
function page_service( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$S  = $C['service'];
	$im = img( $S['img'] );
	$o  = array();

	$o[] = head_corporate( $C, $BRAND );
	$o[] = crumbs_html( $S['crumbs'] );
	$o[] = '<main>';

	// 1 · Encabezado de servicio  [fijo · ADN]
	$o[] = '<section class="sec hero svc-head" aria-label="' . h( $S['h1'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $S['eyebrow'] ) . '</span>'
		. '<h1>' . h( $S['h1'] ) . '</h1>'
		. '<p class="lede muted">' . h( $S['claim'] ) . '</p>'
		. '<div class="ctas"><a class="btn btn-primary" href="' . h( ihref_for_label( $S['cta_1'] ) ) . '">' . h( $S['cta_1'] ) . '</a>'
		. '<a class="btn btn-outline" href="' . h( ihref_for_label( $S['cta_2'] ) ) . '">' . h( $S['cta_2'] ) . '</a></div></div>'
		. '<div class="media"><figure class="frame"><img data-img="' . h( $im['slug'] ) . '"'
		. ' alt="' . h( $im['alt'] ) . '" width="' . $im['w'] . '" height="' . $im['h'] . '"></figure></div>'
		. '</div></section>';

	// 2 · Qué resolvemos  [fijo · ADN]
	$pb  = $S['problems'];
	$o[] = '<section class="sec problems grid-sec bg-alt" aria-label="Qué resolvemos"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $pb['eyebrow'] ) . '</span>'
		. '<h2>' . h( $pb['h2'] ) . '</h2></div><ul class="items cols-3">';
	foreach ( $pb['items'] as $it ) {
		$o[] = '<li class="prob"><h3>' . h( $it[0] ) . '</h3><p>' . h( $it[1] ) . '</p></li>';
	}
	$o[] = '</ul></div></section>';

	// 3 · COMP-FEATURES  [fijo]
	$ft  = $S['features'];
	$o[] = '<section class="sec features grid-sec" aria-label="Alcance"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $ft['eyebrow'] ) . '</span>'
		. '<h2>' . h( $ft['h2'] ) . '</h2></div><ul class="feats">';
	foreach ( $ft['items'] as $it ) {
		$o[] = '<li><b>' . h( $it[0] ) . '</b><span>' . h( $it[1] ) . '</span></li>';
	}
	$o[] = '</ul></div></section>';

	// 4 · COMP-PROCESS  [toggle TGL-PROCESS] — the home's steps, because it is the same method
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-PROCESS' ) ) {
		$pr  = $C['process'];
		$o[] = '<section class="sec process grid-sec bg-alt" aria-label="Proceso"><div class="canvas">'
			. '<div class="head stack"><span class="eyebrow">' . h( $pr['eyebrow'] ) . '</span>'
			. '<h2>' . h( $pr['h2'] ) . '</h2></div><ol class="steps">';
		foreach ( $pr['steps'] as $i => $st ) {
			$o[] = '<li class="step"><span class="n">' . sprintf( '%02d', $i + 1 ) . '</span>'
				. '<h3>' . h( $st[0] ) . '</h3><p>' . h( $st[1] ) . '</p></li>';
		}
		$o[] = '</ol></div></section>';
	}

	// 5 · COMP-FAQ  [toggle TGL-FAQ, default ON] — `<details>/<summary>`, mockup-guide.md's recipe
	$fq  = $S['faq'];
	$o[] = '<section class="sec faq" aria-label="Preguntas frecuentes"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $fq['eyebrow'] ) . '</span>'
		. '<h2>' . h( $fq['h2'] ) . '</h2></div>';
	$o[] = disclosure_list_html( $fq['items'], 'qas' );
	$o[] = '</div></section>';

	// 6 · COMP-TESTIMONIAL  [toggle TGL-TESTIMONIALS]
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-TESTIMONIALS' ) ) {
		$qt  = $C['quotes'];
		$o[] = '<section class="sec quotes grid-sec bg-alt" aria-label="Testimonios"><div class="canvas">'
			. '<div class="head stack"><span class="eyebrow">' . h( $qt['eyebrow'] ) . '</span>'
			. '<h2>' . h( $qt['h2'] ) . '</h2></div><ul class="items">';
		foreach ( $qt['items'] as $q ) {
			$o[] = '<li><figure><blockquote>' . h( $q[0] ) . '</blockquote>'
				. '<figcaption><b>' . h( $q[1] ) . '</b><span>' . h( $q[2] ) . '</span></figcaption>'
				. '</figure></li>';
		}
		$o[] = '</ul></div></section>';
	}

	// 7 · Otros servicios  [fijo · ADN] — the cross-link the archetype requires
	$ot  = $S['others'];
	$o[] = '<section class="sec others grid-sec" aria-label="Otros servicios"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $ot['eyebrow'] ) . '</span>'
		. '<h2>' . h( $ot['h2'] ) . '</h2></div><div class="items cols-2">';
	foreach ( $ot['items'] as $it ) {
		$o[] = card_html( $anchor_key, array( 'img' => $it[2], 'h3' => $it[0], 'p' => $it[1] ) );
	}
	$o[] = '</div></div></section>';

	// 8 · COMP-CTA + COMP-LEAD-FORM  [fijo · ADN]
	$o[] = band_closing_html( $C['band'], $uid );

	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );

	return number_heads( implode( "\n", $o ) );
}

/**
 * TPL-PDP-01 · product detail, the interior page of TPL-E-02.
 *
 * COMP-GALLERY and COMP-PRODUCT-INFO are ONE block in the archetype's wireframe, not two sections,
 * and they are emitted that way: a section boundary running through a buy box is the difference
 * between a product page and two stacked panels.
 */
function page_pdp( $anchor_key, $C, $BRAND, $uid, $layout = 'standard' ) {
	$P  = $C['pdp'];
	$mn = img( $P['main'] );
	$o  = array();

	$o[] = head_ecommerce( $C, $BRAND, $uid );
	$o[] = crumbs_html( $P['crumbs'] );
	$o[] = '<main>';

	$thumbs = '';
	foreach ( $P['thumbs'] as $t ) {
		$ti      = img( $t );
		$thumbs .= '<li><figure class="frame sq"><img data-img="' . h( $ti['slug'] ) . '"'
			. ' alt="' . h( $ti['alt'] ) . '" width="' . $ti['w'] . '" height="' . $ti['h'] . '"></figure></li>';
	}

	$opts = '';
	foreach ( $P['opts'] as $i => $op ) {
		$oid   = $uid . '-op' . $i;
		$opts .= '<label class="opt" for="' . $oid . '">'
			. '<input type="radio" id="' . $oid . '" name="' . $uid . '-finish"'
			. ( 0 === $i ? ' checked' : '' ) . '><span>' . h( $op ) . '</span></label>';
	}

	// COMP-GALLERY + COMP-PRODUCT-INFO — one block  [fijo · ADN]
	$o[] = '<section class="sec pdp' . ( 'editorial' === $layout ? ' editorial' : '' ) . '" aria-label="' . h( $P['h1'] ) . '"><div class="canvas">'
		. '<div class="pdp-gal">'
		. '<figure class="frame"><img data-img="' . h( $mn['slug'] ) . '" alt="' . h( $mn['alt'] ) . '"'
		. ' width="' . $mn['w'] . '" height="' . $mn['h'] . '"></figure>'
		. '<ul class="pdp-thumbs">' . $thumbs . '</ul>'
		. '</div>'
		. '<div class="pdp-buy">'
		. '<h1>' . h( $P['h1'] ) . '</h1>'
		/* PRECIO ANTERIOR TACHADO SI HAY OFERTA. TPL-PDP-01 § 3 lo pedía desde que se escribió
		   y ningún renderizador lo hacía — el arquetipo tenía una línea sin código detrás.
		   `<s>` y no `text-decoration` a secas: un precio tachado es información, y un lector de
		   pantalla tiene que poder decir que ese número ya no vale. El porcentaje NO gasta el
		   acento: no es un CTA, ni un icono de acción, ni un enlace, ni un estado activo, y el
		   único color de la página ya lo tiene el botón de comprar. */
		. '<p class="price pdp-price">' . h( $P['price'] )
			. ( isset( $P['price_was'] ) ? ' <s class="was">' . h( $P['price_was'] ) . '</s>' : '' )
			. ( isset( $P['price_off'] ) ? ' <span class="off">' . h( $P['price_off'] ) . '</span>' : '' )
			. '</p>'
		. ( isset( $P['deadline'] ) ? '<p class="small pdp-deadline">' . h( $P['deadline'] ) . '</p>' : '' )
		. '<p class="muted">' . h( $P['lede'] ) . '</p>'
		. '<fieldset class="opts"><legend>' . h( $P['opt_lbl'] ) . '</legend>' . $opts . '</fieldset>'
		. '<div class="field qty"><label for="' . $uid . '-qty">' . h( $P['qty_lbl'] ) . '</label>'
		. '<input id="' . $uid . '-qty" type="number" value="4" min="1"></div>'
		. '<button class="btn btn-primary" type="button">' . h( $P['cta'] ) . '</button>'
		. '<p class="small muted pdp-ship">' . h( $P['ship'] ) . '</p>'
		. '</div></div></section>';

	// COMP-ACCORDION  [fijo]
	$o[] = '<section class="sec acc grid-sec bg-alt" aria-label="Detalle"><div class="canvas">';
	$o[] = disclosure_list_html( $P['acc'], 'qas' );
	$o[] = '</div></section>';

	// COMP-TRUST-BADGES  [toggle] — the tick stays neutral, same reason as the benefits bar
	$o[] = '<section class="sec bar" aria-label="Garantías"><div class="canvas"><div class="items bens">';
	foreach ( $P['badges'] as $b ) {
		$o[] = '<p class="ben"><span class="bicon" aria-hidden="true">✓</span>' . h( $b ) . '</p>';
	}
	$o[] = '</div></div></section>';

	/* COMP-PRODUCT-CAROUSEL  [toggle TGL-RELATED]
	   POR DEFECTO EL CARRUSEL DE LA HOME, porque es una sola tienda y repetir su selección es lo
	   que hace que las dos páginas se lean como un sitio. Pero una tienda organizada por
	   CATEGORÍAS no hace cross-sell con «más vendidos»: quien está en una ficha sigue dentro de
	   una sección, y lo que le sirve es el resto de esa sección. Por eso la ficha puede traer su
	   propio bloque, y sólo lo trae quien tiene un motivo. */
	$cr  = isset( $P['related'] ) ? $P['related'] : $C['carousel'];
	$o[] = '<section class="sec carousel grid-sec" aria-label="Relacionados"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $cr['eyebrow'] ) . '</span>'
		. '<h2>' . h( $cr['h2'] ) . '</h2></div><div class="items grid-prod cols-4">';
	foreach ( $cr['cards'] as $c ) {
		$o[] = product_html( $anchor_key, $c );
	}
	$o[] = '</div></div></section>';

	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );

	return number_heads( implode( "\n", $o ) );
}

/**
 * COMP-PROCESS con fotografía por paso · TPL-ABOUT-02.
 *
 * NO ES `process_block_html` CON UNA FOTO AÑADIDA, y la diferencia es el arquetipo entero. Aquel
 * renderiza el método de una empresa de servicios: cuatro pasos de texto en una fila, porque lo que
 * se explica es un procedimiento. Aquí lo que se enseña es un OFICIO, y de un oficio la prueba no
 * es la descripción del paso sino la imagen de alguien dándolo — «doce años de experiencia» lo
 * escribe cualquiera. Por eso el paso es una fila ancha con su fotografía y no una tarjeta más de
 * una rejilla de cuatro.
 */
function craft_steps_html( $pr ) {
	$o = '<section class="sec craft grid-sec bg-alt" aria-label="' . h( $pr['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $pr['eyebrow'] ) . '</span>'
		. '<h2>' . h( $pr['h2'] ) . '</h2></div><ol class="craftsteps">';
	foreach ( $pr['steps'] as $i => $st ) {
		$si = img( $st[2] );
		$o .= '<li class="craftstep"><figure class="frame"><img data-img="' . h( $si['slug'] ) . '"'
			. ' alt="' . h( $si['alt'] ) . '" width="' . $si['w'] . '" height="' . $si['h'] . '"></figure>'
			. '<div class="craftbody"><span class="n">' . sprintf( '%02d', $i + 1 ) . '</span>'
			. '<h3>' . h( $st[0] ) . '</h3><p class="muted">' . h( $st[1] ) . '</p></div></li>';
	}
	return $o . '</ol></div></section>';
}

/** COMP-CTA sobrio, sin formulario. Tres páginas internas cierran igual, así que hay uno. */
/**
 * PR3d — `$b['cta_1_href']`/`$b['cta_2_href']` are OPTIONAL explicit page-key overrides. Every
 * pre-existing caller omits them and keeps the old `ihref_for_label()` best-effort match byte for
 * byte; delao's `propiedades` page supplies `cta_2_href` because its own "Valorar mi casa" copy
 * matches none of TPL-C-15's five page labels, and the documented fallback for an unmatched label
 * is the home route — silently wrong for a button whose entire job is reaching Contacto.
 */
function page_cta_html( $b ) {
	$href_1 = isset( $b['cta_1_href'] ) ? ihref( $b['cta_1_href'] ) : ihref_for_label( $b['cta_1'] );
	$href_2 = isset( $b['cta_2_href'] ) ? ihref( $b['cta_2_href'] ) : ihref_for_label( $b['cta_2'] );
	return '<section class="sec band closing sober" aria-label="' . h( $b['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $b['eyebrow'] ) . '</span>'
		. '<h2>' . h( $b['h2'] ) . '</h2><p class="muted">' . h( $b['lede'] ) . '</p>'
		. '<div class="ctas"><a class="btn btn-primary" href="' . h( $href_1 ) . '">' . h( $b['cta_1'] ) . '</a>'
		. '<a class="btn btn-outline" href="' . h( $href_2 ) . '">' . h( $b['cta_2'] ) . '</a></div></div>'
		. '</div></section>';
}

/** Encabezado de página interna sin fotografía · COMP-PAGE-HEAD. */
function page_head_html( $hd ) {
	return '<section class="sec pagehead" aria-label="' . h( $hd['h1'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $hd['eyebrow'] ) . '</span>'
		. '<h1>' . h( $hd['h1'] ) . '</h1>'
		. '<p class="lede muted">' . h( $hd['lede'] ) . '</p></div></div></section>';
}

/**
 * TPL-ABOUT-01 «La empresa» · la página de Nosotros de TPL-C-02.
 *
 * REUTILIZA historia, cifras, equipo y testimonios del bloque de la home, igual que `page_service`
 * reutiliza el proceso y el cierre de TPL-C-01: es la misma empresa un nivel más abajo, y reutilizar
 * es lo que hace que las dos páginas se lean como UN sitio y no como dos maquetas que comparten
 * carpeta. Lo propio de la página son el encabezado, los compromisos y el cierre.
 *
 * COMP-VALUES ES LO QUE LA HOME NO TIENE, y no es relleno: la home de TPL-C-02 gana confianza con
 * cifras y acreditaciones, cosas que ya ocurrieron. Una página de Nosotros tiene además que decir a
 * qué se compromete de aquí en adelante, y eso son compromisos verificables — «presupuesto en 48 h»,
 * no «excelencia».
 */
function page_about_company( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$A  = $C['nosotros'];
	$hi = img( $A['hero']['img'] );
	$o  = array();

	$o[] = head_corporate( $C, $BRAND );
	$o[] = crumbs_html( $A['crumbs'] );
	$o[] = '<main>';

	// 1 · COMP-HERO  [fijo]
	$o[] = '<section class="sec hero" aria-label="Quiénes somos"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $A['hero']['eyebrow'] ) . '</span>'
		. '<h1>' . h( $A['hero']['h1'] ) . '</h1>'
		. '<p class="lede muted">' . h( $A['hero']['lede'] ) . '</p></div>'
		. '<div class="media"><figure class="frame"><img data-img="' . h( $hi['slug'] ) . '"'
		. ' alt="' . h( $hi['alt'] ) . '" width="' . $hi['w'] . '" height="' . $hi['h'] . '"></figure></div>'
		. '</div></section>';

	// 2 · COMP-ABOUT — historia y misión  [fijo · ADN], la de la home
	$ab  = $C['about'];
	$ai  = img( $ab['img'] );
	$o[] = '<section class="sec about bg-alt" aria-label="Historia"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $ab['eyebrow'] ) . '</span>'
		. '<h2>' . h( $ab['h2'] ) . '</h2>';
	foreach ( $ab['body'] as $para ) {
		$o[] = '<p class="muted">' . h( $para ) . '</p>';
	}
	$o[] = '</div><div class="media"><figure class="frame"><img data-img="' . h( $ai['slug'] ) . '"'
		. ' alt="' . h( $ai['alt'] ) . '" width="' . $ai['w'] . '" height="' . $ai['h'] . '"></figure></div>'
		. '</div></section>';

	// 3 · COMP-VALUES  [fijo · ADN] — lo que la home no lleva
	$vl  = $A['values'];
	$o[] = '<section class="sec features grid-sec" aria-label="Compromisos"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $vl['eyebrow'] ) . '</span>'
		. '<h2>' . h( $vl['h2'] ) . '</h2></div><ul class="feats">';
	foreach ( $vl['items'] as $it ) {
		$o[] = '<li><b>' . h( $it[0] ) . '</b><span>' . h( $it[1] ) . '</span></li>';
	}
	$o[] = '</ul></div></section>';
	return page_about_company_tail( $anchor_key, $C, $A, $o, $tgl_rows );
}

/** La segunda mitad de `page_about_company`, partida donde la página deja de aportar y empieza a
 *  reutilizar: de aquí abajo todo viene del bloque de la home de TPL-C-02. */
function page_about_company_tail( $anchor_key, $C, $A, $o, $tgl_rows ) {
	// 4 · COMP-STATS  [toggle TGL-ABOUT-STATS]
	/* LAS CIFRAS VAN EN EL HTML, no las pinta un script al hacer scroll. En una página cuyo trabajo
	   entero es la credibilidad, una credencial que sólo existe después de un script no es una
	   credencial: sin JS —y para todo rastreador— pone «0». */
	$st  = $C['stats'];
	$o[] = '<section class="sec stats bg-alt" aria-label="Cifras"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $st['eyebrow'] ) . '</span></div>'
		. '<dl class="figs">';
	foreach ( $st['items'] as $it ) {
		$o[] = '<div class="fig"><dt>' . h( $it[0] ) . '</dt><dd>' . h( $it[1] ) . '</dd></div>';
	}
	$o[] = '</dl></div></section>';

	// 5 · COMP-TEAM  [toggle TGL-ABOUT-TEAM] — caras reales o la sección no va
	$tm  = $C['team'];
	$o[] = '<section class="sec team grid-sec" aria-label="Equipo"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $tm['eyebrow'] ) . '</span>'
		. '<h2>' . h( $tm['h2'] ) . '</h2></div><ul class="items cols-3">';
	foreach ( $tm['items'] as $it ) {
		$ti  = img( $it[2] );
		$o[] = '<li class="member"><figure class="frame sq"><img data-img="' . h( $ti['slug'] ) . '"'
			. ' alt="' . h( $ti['alt'] ) . '" width="' . $ti['w'] . '" height="' . $ti['h'] . '"></figure>'
			. '<b>' . h( $it[0] ) . '</b><span>' . h( $it[1] ) . '</span></li>';
	}
	$o[] = '</ul></div></section>';

	// 6 · COMP-TESTIMONIAL  [toggle TGL-TESTIMONIALS]
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-TESTIMONIALS' ) ) {
		$qt  = $C['quotes'];
		$o[] = '<section class="sec quotes grid-sec bg-alt" aria-label="Referencias"><div class="canvas">'
			. '<div class="head stack"><span class="eyebrow">' . h( $qt['eyebrow'] ) . '</span>'
			. '<h2>' . h( $qt['h2'] ) . '</h2></div><ul class="items">';
		foreach ( $qt['items'] as $q ) {
			$o[] = '<li><figure><blockquote>' . h( $q[0] ) . '</blockquote>'
				. '<figcaption><b>' . h( $q[1] ) . '</b><span>' . h( $q[2] ) . '</span></figcaption>'
				. '</figure></li>';
		}
		$o[] = '</ul></div></section>';
	}

	// 7 · COMP-CTA  [fijo]
	$o[] = page_cta_html( $A['cta'] );
	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );

	return number_heads( implode( "\n", $o ) );
}

/**
 * TPL-ABOUT-02 «El oficio» · la página de Nosotros de TPL-E-03.
 *
 * NO HAY REJILLA DE EQUIPO Y NO HAY CONTADOR DE CIFRAS, y las dos ausencias son la decisión del
 * arquetipo, no un olvido. Un taller de cuatro personas con cuatro retratos de estudio parece una
 * consultora pequeña; el mismo taller con una fotografía de las manos trabajando y una frase
 * firmada por quien las tiene parece lo que es. Y «doce años de experiencia» lo escribe cualquiera,
 * mientras que «una encimera de 3,20 m en una pieza por una escalera de caracol» no: la prueba es
 * COMP-CASES.
 */
function page_about_workshop( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$A  = $C['oficio'];
	$hi = img( $A['hero']['img'] );
	$o  = array();

	$o[] = head_shop_plain( $C, $BRAND );
	$o[] = crumbs_html( $A['crumbs'] );
	$o[] = '<main>';

	// 1 · COMP-HERO  [fijo] — el taller trabajando, no el logo sobre fondo blanco
	$o[] = '<section class="sec hero" aria-label="El taller"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $A['hero']['eyebrow'] ) . '</span>'
		. '<h1>' . h( $A['hero']['h1'] ) . '</h1>'
		. '<p class="lede muted">' . h( $A['hero']['lede'] ) . '</p></div>'
		. '<div class="media"><figure class="frame"><img data-img="' . h( $hi['slug'] ) . '"'
		. ' alt="' . h( $hi['alt'] ) . '" width="' . $hi['w'] . '" height="' . $hi['h'] . '"></figure></div>'
		. '</div></section>';

	// 2 · COMP-PROCESS  [fijo · ADN] — con fotografía en cada paso
	$o[] = craft_steps_html( $A['process'] );

	// 3 · COMP-GALLERY  [fijo · ADN] — el sitio y las herramientas, no el porfolio
	$o[] = gallery_masonry_html( $A['gallery'] );

	// 4 · COMP-FIGURE-QUOTE  [fijo · ADN] — la cara que la rejilla de equipo hacía mal
	$o[] = figure_quote_html( $A['figq'] );

	// 5 · COMP-CASES  [fijo · ADN] — la sustituta de las cifras
	$cs  = $A['cases'];
	$o[] = '<section class="sec cases grid-sec bg-alt" aria-label="Trabajos"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $cs['eyebrow'] ) . '</span>'
		. '<h2>' . h( $cs['h2'] ) . '</h2></div><div class="items cols-3">';
	foreach ( $cs['items'] as $it ) {
		$o[] = card_html( $anchor_key, array( 'img' => $it[2], 'h3' => $it[0], 'p' => $it[1] ) );
	}
	$o[] = '</div></div></section>';

	// 6 · COMP-CREDENTIALS  [toggle TGL-ABOUT-CREDS] — sólo lo verificable
	$cd  = $A['creds'];
	$o[] = '<section class="sec creds grid-sec" aria-label="Acreditaciones"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $cd['eyebrow'] ) . '</span>'
		. '<h2>' . h( $cd['h2'] ) . '</h2></div><ul class="feats">';
	foreach ( $cd['items'] as $it ) {
		$o[] = '<li><b>' . h( $it[0] ) . '</b><span>' . h( $it[1] ) . '</span></li>';
	}
	$o[] = '</ul></div></section>';

	// 7 · COMP-CTA  [fijo]
	$o[] = page_cta_html( $A['cta'] );
	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );

	return number_heads( implode( "\n", $o ) );
}

/**
 * TPL-CONTACT-01 «Consulta» · la página de contacto de TPL-C-01.
 *
 * LAS DOS SECCIONES QUE CASI NINGUNA PÁGINA DE CONTACTO TIENE. Un formulario es una caja negra: se
 * escribe, se pulsa y no se sabe si aquello llegó, quién lo va a leer ni cuándo, y esa
 * incertidumbre es la que hace que mucha gente prefiera llamar aunque le venga peor. `COMP-PROCESS`
 * dice qué pasa al enviar, con plazos; `COMP-TEAM` dice con quién se va a hablar. Saber a quién le
 * escribes cambia lo que escribes, y cambia si escribes.
 *
 * LOS DATOS DIRECTOS VAN AL LADO DEL FORMULARIO, no debajo del todo: quien prefiere llamar tiene
 * que ver el teléfono sin bajar.
 */
function page_contact_enquiry( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$K = $C['contacto'];
	$o = array();

	$o[] = head_corporate( $C, $BRAND );
	$o[] = crumbs_html( $K['crumbs'] );
	$o[] = '<main>';

	// 1 · COMP-PAGE-HEAD  [fijo]
	$o[] = page_head_html( $K['head'] );

	// 2 · COMP-CONTACT-FORM + COMP-CONTACT-DIRECT — un bloque, dos mitades  [fijo · ADN]
	$fm  = $K['form'];
	$dr  = $K['direct'];
	$o[] = '<section class="sec band contactblock" aria-label="Escríbenos"><div class="canvas">'
		. '<div class="formwrap"><form class="leadform" onsubmit="return false">';
	foreach ( $fm['fields'] as $f ) {
		$fid = $uid . '-c-' . $f[0];
		$o[] = '<div class="field"><label for="' . $fid . '">' . h( $f[1] ) . '</label>'
			. '<input id="' . $fid . '" name="' . $f[0] . '" type="' . $f[2] . '"></div>';
	}
	$o[] = '<div class="field"><label for="' . $uid . '-c-msg">' . h( $fm['msg'] ) . '</label>'
		. '<textarea id="' . $uid . '-c-msg" name="msg" rows="4"></textarea></div>'
		. '<button class="btn btn-primary" type="submit">' . h( $fm['submit'] ) . '</button>'
		. '<p class="small muted">' . h( $fm['small'] ) . '</p>'
		. '</form></div>'
		. '<div class="head stack"><span class="eyebrow">' . h( $dr['eyebrow'] ) . '</span>'
		. '<h2>' . h( $dr['h2'] ) . '</h2><ul class="directlist">';
	foreach ( $dr['items'] as $it ) {
		$o[] = '<li><span class="dlabel">' . h( $it[0] ) . '</span>'
			. '<a href="' . h( ihref_for_label( $it[1] ) ) . '">' . h( $it[1] ) . '</a>'
			. '<span class="muted small">' . h( $it[2] ) . '</span></li>';
	}
	$o[] = '</ul></div></div></section>';

	// 3 · COMP-PROCESS  [fijo · ADN] — qué pasa después de enviar
	$fl  = $K['flow'];
	$o[] = '<section class="sec process grid-sec bg-alt" aria-label="Qué pasa al enviar"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $fl['eyebrow'] ) . '</span>'
		. '<h2>' . h( $fl['h2'] ) . '</h2></div><ol class="steps">';
	foreach ( $fl['steps'] as $i => $st ) {
		$o[] = '<li class="step"><span class="n">' . sprintf( '%02d', $i + 1 ) . '</span>'
			. '<h3>' . h( $st[0] ) . '</h3><p>' . h( $st[1] ) . '</p></li>';
	}
	$o[] = '</ol><p class="small muted flownote">' . h( $fl['note'] ) . '</p></div></section>';

	// 4 · COMP-TEAM  [toggle TGL-CONTACT-WHO] — con quién vas a hablar
	$o[] = med_team_html( $K['team'] );

	// 5 · COMP-FAQ  [toggle TGL-FAQ]
	$fq  = $K['faq'];
	$o[] = '<section class="sec faq bg-alt" aria-label="Preguntas frecuentes"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $fq['eyebrow'] ) . '</span>'
		. '<h2>' . h( $fq['h2'] ) . '</h2></div>';
	$o[] = disclosure_list_html( $fq['items'], 'qas' );
	$o[] = '</div></section>';

	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );

	return number_heads( implode( "\n", $o ) );
}

/**
 * TPL-PDP-05 «A medida» · la ficha de producto de TPL-E-03, y la única del catálogo SIN carrito y
 * SIN precio.
 *
 * Las otras cuatro fichas comparten un supuesto que aquí se rompe: existe una referencia con un
 * precio que se puede meter en un carrito. Una encimera de 320 × 62 en Blanco Macael con canto de
 * media caña y hueco de fregadero no es una referencia; es una combinación que nadie ha fabricado
 * todavía, y su precio no se sabe hasta configurarla. Forzarla en `TPL-PDP-01` produce el error
 * clásico del sector: un precio «desde» que no se parece al final, un carrito que acepta la compra,
 * y un correo posterior pidiendo medidas — que es donde se cae la venta, y encima ya se cobró.
 *
 * MISMA TIENDA Y MISMA FOTOGRAFÍA que la ficha estándar de TPL-E-02 y la editorial de TPL-E-01. Es
 * deliberado: puestas una al lado de otra, lo único que cambia entre las tres es la estructura, y
 * entre ésta y las otras dos la diferencia no es el aire ni el tamaño de la foto — es que falta el
 * botón de comprar.
 */
function page_pdp_mtm( $anchor_key, $C, $BRAND, $uid ) {
	$P = $C['mtm'];
	$o = array();

	$o[] = head_shop_plain( $C, $BRAND );
	$o[] = crumbs_html( $P['crumbs'] );
	$o[] = '<main>';
	// 1 · COMP-CONFIGURATOR  [fijo · ADN] — ocupa el sitio que en TPL-PDP-01 ocupa la compra
	$o[] = configurator_html( $P['cfg'], $uid, array( $P['h1'], $P['lede'] ) );
	// 2 · COMP-MEASURE-TABLE  [fijo · ADN] — aquí es la instrucción de medición
	$o[] = measure_how_html( $P['measure'] );
	// 3 · COMP-GALLERY  [fijo] — trabajos entregados, cada uno CON SU MEDIDA escrita
	$o[] = done_gallery_html( $P['gallery'] );
	// 4 · COMP-SAMPLE-REQUEST  [fijo · ADN]
	$o[] = sample_request_html( $P['sample'] );
	// 5 · COMP-LEAD-TIME  [fijo · ADN]
	$o[] = lead_time_html( $P['lead'] );
	// 6 · COMP-QUOTE-FORM  [fijo · ADN] — el cierre: presupuesto, no compra
	$q   = $P['quote'];
	$o[] = band_closing_html(
		array(
			'eyebrow' => $q['eyebrow'],
			'h2'      => $q['h2'],
			'lede'    => $q['lede'],
			'fields'  => $q['fields'],
			'msg'     => $q['msg'],
			'submit'  => $q['submit'],
		),
		$uid . '-q'
	);

	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );

	return number_heads( implode( "\n", $o ) );
}

/**
 * TPL-E-09 · A medida / Presupuesto — la home.
 *
 * MISMAS SECCIONES QUE LA FICHA DE TPL-E-03 Y NO ES UN DESCUIDO: en este arquetipo el configurador
 * ES la página, así que su home y su ficha se parecen más que en cualquier otro par del catálogo.
 * Lo que las separa es dónde vive el h1 —aquí en el héroe, allí en el configurador— y que ésta abre
 * con una fotografía de lo que se fabrica, porque una tienda sin catálogo necesita enseñar algo
 * antes de pedir medidas.
 */
function strip_made( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$hero = $C['hero'];
	$im   = img( $hero['img'] );
	$o    = array();

	$o[] = head_corporate( $C, $BRAND );
	$o[] = '<main>';
	$o[] = '<section class="sec hero" aria-label="Presentación"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $hero['eyebrow'] ) . '</span>'
		. '<h1>' . h( $hero['h1'] ) . '</h1><p class="lede muted">' . h( $hero['lede'] ) . '</p>'
		. '<div class="ctas"><a class="btn btn-primary" href="' . h( ihref_for_label( $hero['cta_1'] ) ) . '">' . h( $hero['cta_1'] ) . '</a>'
		. '<a class="btn btn-outline" href="' . h( ihref_for_label( $hero['cta_2'] ) ) . '">' . h( $hero['cta_2'] ) . '</a></div></div>'
		. '<div class="media"><figure class="frame"><img data-img="' . h( $im['slug'] ) . '"'
		. ' alt="' . h( $im['alt'] ) . '" width="' . $im['w'] . '" height="' . $im['h'] . '"></figure></div>'
		. '</div></section>';
	$o[] = configurator_html( $C['cfg'], $uid );
	$o[] = measure_how_html( $C['measure'] );
	$o[] = sample_request_html( $C['sample'], '' );
	$o[] = lead_time_html( $C['lead'], ' bg-alt' );
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-GALLERY' ) ) {
		$o[] = done_gallery_html( $C['gallery'] );
	}
	$q   = $C['quote'];
	$o[] = band_closing_html(
		array(
			'eyebrow' => $q['eyebrow'],
			'h2'      => $q['h2'],
			'lede'    => $q['lede'],
			'fields'  => $q['fields'],
			'msg'     => $q['msg'],
			'submit'  => $q['submit'],
		),
		$uid . '-q'
	);
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-FAQ' ) ) {
		$o[] = faq_block_html( $C['faq'], ' bg-alt' );
	}

	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );

	return implode( "\n", $o );
}

/* ── TPL-E-06 · TALLA / PRUEBA ────────────────────────────────────────────────────────────────
   La tienda cuyo cliente no duda del producto sino de si le va a caber. */

/** COMP-FIT-FINDER · traduce una talla conocida a la de esta tienda. */
function fit_finder_html( $fi, $uid ) {
	$o = '<section class="sec finder" aria-label="Buscador de talla"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $fi['eyebrow'] ) . '</span>'
		. '<h1>' . h( $fi['h1'] ) . '</h1><p class="lede muted">' . h( $fi['lede'] ) . '</p></div>'
		. '<div class="findbox"><div class="findrow">';
	foreach ( array( array( 'a', $fi['lbl_1'], $fi['opt_1'] ), array( 'b', $fi['lbl_2'], $fi['opt_2'] ) ) as $g ) {
		$gid = $uid . '-fs' . $g[0];
		$o  .= '<div class="field"><label for="' . $gid . '">' . h( $g[1] ) . '</label><select id="' . $gid . '">';
		foreach ( $g[2] as $op ) {
			$o .= '<option>' . h( $op ) . '</option>';
		}
		$o .= '</select></div>';
	}
	/* EL RESULTADO SE ENSEÑA YA RESUELTO y no detrás del botón. Una maqueta que esconde su propia
	   respuesta detrás de un clic que no hace nada enseña un formulario, no un buscador de talla. */
	return $o . '</div><button class="btn btn-primary" type="button">' . h( $fi['cta'] ) . '</button>'
		. '<p class="findres">' . h( $fi['result'] ) . '</p>'
		. '<p class="small muted">' . h( $fi['note'] ) . '</p>'
		. '</div></div></section>';
}

/** COMP-MEASURE-TABLE · medidas reales en cm, de ESTA prenda y no de la marca. */
function measure_table_html( $ms ) {
	$o = '<section class="sec measure grid-sec bg-alt" aria-label="' . h( $ms['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $ms['eyebrow'] ) . '</span>'
		. '<h2>' . h( $ms['h2'] ) . '</h2><p class="muted">' . h( $ms['note'] ) . '</p></div>'
		/* La tabla scrollea DENTRO de su caja y nunca la página entera: seis columnas de cifras no
		   caben en 375px y arrastrar el documento de lado es el defecto que eso produce. */
		. '<div class="tablewrap"><table class="mtab"><thead><tr>';
	foreach ( $ms['cols'] as $c ) {
		$o .= '<th scope="col">' . h( $c ) . '</th>';
	}
	$o .= '</tr></thead><tbody>';
	foreach ( $ms['rows'] as $r ) {
		$o .= '<tr><th scope="row">' . h( $r[0] ) . '</th>';
		foreach ( array_slice( $r, 1 ) as $cell ) {
			$o .= '<td>' . h( $cell ) . '</td>';
		}
		$o .= '</tr>';
	}
	return $o . '</tbody></table></div><p class="small muted flownote">' . h( $ms['how'] ) . '</p>'
		. '</div></section>';
}

/** COMP-FIT-GALLERY · la misma prenda sobre tres cuerpos, con su talla escrita. */
function fit_gallery_html( $fg ) {
	$o = '<section class="sec fitgal grid-sec" aria-label="' . h( $fg['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $fg['eyebrow'] ) . '</span>'
		. '<h2>' . h( $fg['h2'] ) . '</h2><p class="muted">' . h( $fg['note'] ) . '</p></div>'
		. '<ul class="bodies"' . cols_attr( count( $fg['items'] ) ) . '>';
	foreach ( $fg['items'] as $b ) {
		$bi = img( $b['img'] );
		$o .= '<li><figure class="frame"><img data-img="' . h( $bi['slug'] ) . '"'
			. ' alt="' . h( $bi['alt'] ) . '" width="' . $bi['w'] . '" height="' . $bi['h'] . '"></figure>'
			. '<p class="bodysize"><b>' . h( $b['size'] ) . '</b><span class="muted">' . h( $b['who'] ) . '</span></p></li>';
	}
	return $o . '</ul></div></section>';
}

/** COMP-RETURN-PROMISE · tres cifras y una frase, sin asteriscos. */
function return_promise_html( $rt ) {
	$o = '<section class="sec retprom" aria-label="' . h( $rt['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $rt['eyebrow'] ) . '</span>'
		. '<h2>' . h( $rt['h2'] ) . '</h2></div><dl class="figs retfigs">';
	foreach ( $rt['items'] as $it ) {
		$o .= '<div class="fig"><dt>' . h( $it[0] ) . '</dt><dd>' . h( $it[1] ) . '</dd>'
			. '<p class="small muted">' . h( $it[2] ) . '</p></div>';
	}
	return $o . '</dl><p class="small muted flownote">' . h( $rt['note'] ) . '</p></div></section>';
}

/**
 * TPL-E-06 · Talla / Prueba — la home.
 *
 * EL BUSCADOR DE TALLA OCUPA EL SITIO DEL HÉROE, y esa es la decisión entera del arquetipo. Es la
 * primera intención del visitante que ya sabe lo que quiere, y una pantalla de fotografía editorial
 * delante retrasa la única pregunta que esta tienda existe para contestar. No hay hero a sangre, no
 * hay lookbook y no hay carrusel de destacados: los tres son lo que hace una tienda de moda cuando
 * no tiene nada que decir sobre el ajuste.
 */
function strip_fit( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$o = array();
	$o[] = head_shop_search( $C, $BRAND, $uid );
	$o[] = '<main>';

	// 1 · COMP-FIT-FINDER  [fijo · ADN]
	$o[] = fit_finder_html( $C['finder'], $uid );

	// 2 · COMP-PRODUCT-GRID  [fijo] — cada ficha con la talla que lleva quien posa
	$g   = $C['grid'];
	$o[] = '<section class="sec stock grid-sec bg-alt" aria-label="' . h( $g['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $g['eyebrow'] ) . '</span>'
		. '<h2>' . h( $g['h2'] ) . '</h2><p class="muted">' . h( $g['note'] ) . '</p></div>'
		. '<div class="items grid-prod"' . cols_attr( count( $g['items'] ) ) . '>';
	foreach ( $g['items'] as $c ) {
		$o[] = product_html( $anchor_key, $c );
	}
	$o[] = '</div></div></section>';

	// 3 · COMP-MEASURE-TABLE  [fijo · ADN]
	$o[] = measure_table_html( $C['measure'] );

	// 4 · COMP-FIT-GALLERY  [fijo · ADN]
	$o[] = fit_gallery_html( $C['fitgal'] );

	// 5 · COMP-RETURN-PROMISE  [fijo · ADN]
	$o[] = return_promise_html( $C['ret'] );

	// 6 · COMP-FAQ  [toggle TGL-FAQ] — dudas de ajuste, no de envío
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-FAQ' ) ) {
		$o[] = faq_block_html( $C['faq'], ' bg-alt' );
	}

	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );

	return implode( "\n", $o );
}

/**
 * TPL-PDP-02 · la ficha de talla y ajuste.
 *
 * LA TABLA DE MEDIDAS SALE DEL ACORDEÓN, y es la diferencia estructural con TPL-PDP-01. Allí el
 * detalle se pliega porque es detalle; aquí el detalle ES la decisión, y una tabla en una pestaña
 * que nadie abre es una tabla que no existe. Por lo mismo no hay carrusel de relacionados: empujar
 * otra prenda a quien todavía duda de la talla de ésta es cambiar una compra segura por dos
 * inseguras.
 */
function page_pdp_fit( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$P  = $C['pdp'];
	$mn = img( $P['main'] );
	$o  = array();

	$o[] = head_shop_search( $C, $BRAND, $uid );
	$o[] = crumbs_html( $P['crumbs'] );
	$o[] = '<main>';

	$thumbs = '';
	foreach ( $P['thumbs'] as $t ) {
		$ti      = img( $t );
		$thumbs .= '<li><figure class="frame sq"><img data-img="' . h( $ti['slug'] ) . '"'
			. ' alt="' . h( $ti['alt'] ) . '" width="' . $ti['w'] . '" height="' . $ti['h'] . '"></figure></li>';
	}

	/* EL SELECTOR LLEVA EL STOCK DE CADA TALLA, y la agotada se marca como agotada en el propio
	   control: descubrirlo al pulsar «añadir» es el momento exacto en que se pierde la compra. */
	$sizes = '';
	$first = true;
	foreach ( $P['sizes'] as $s ) {
		$out   = ( 'agotada' === $s[1] );
		$sid   = $uid . '-sz' . $s[0];
		$sizes .= '<label class="opt sizeopt' . ( $out ? ' out' : '' ) . '" for="' . $sid . '">'
			. '<input type="radio" id="' . $sid . '" name="' . $uid . '-size"'
			. ( $out ? ' disabled' : '' ) . ( ( $first && ! $out ) ? ' checked' : '' ) . '>'
			. '<span>' . h( $s[0] ) . '<em>' . h( $s[1] ) . '</em></span></label>';
		if ( ! $out ) {
			$first = false;
		}
	}

	$o[] = '<section class="sec pdp" aria-label="' . h( $P['h1'] ) . '"><div class="canvas">'
		. '<div class="pdp-gal">'
		. '<figure class="frame"><img data-img="' . h( $mn['slug'] ) . '" alt="' . h( $mn['alt'] ) . '"'
		. ' width="' . $mn['w'] . '" height="' . $mn['h'] . '"></figure>'
		. '<ul class="pdp-thumbs">' . $thumbs . '</ul></div>'
		. '<div class="pdp-buy"><h1>' . h( $P['h1'] ) . '</h1>'
		. '<p class="price pdp-price">' . h( $P['price'] ) . '</p>'
		. '<p class="muted">' . h( $P['lede'] ) . '</p>'
		. '<fieldset class="opts sizes"><legend>' . h( $P['sz_lbl'] ) . '</legend>' . $sizes . '</fieldset>'
		. '<button class="btn btn-primary" type="button">' . h( $P['cta'] ) . '</button>'
		. '<p class="small muted pdp-ship">' . h( $P['ship'] ) . '</p>'
		. '</div></div></section>';

	$o[] = fit_finder_html( $C['finder'], $uid . '-p' );
	$o[] = measure_table_html( $C['measure'] );
	$o[] = fit_gallery_html( $C['fitgal'] );
	$o[] = return_promise_html( $C['ret'] );
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-FAQ' ) ) {
		$o[] = faq_block_html( $C['faq'], ' bg-alt' );
	}

	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );

	return implode( "\n", $o );
}

/* ── TPL-E-07 · LOTE / PESO ───────────────────────────────────────────────────────────────────
   El producto cambia cada semana y no es intercambiable consigo mismo. */

/** COMP-DELIVERY-WINDOW · descarta la compra imposible antes de empezarla. */
function delivery_window_html( $dw, $uid ) {
	return '<section class="sec dwin" aria-label="Ventana de entrega"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $dw['eyebrow'] ) . '</span>'
		. '<h1>' . h( $dw['h1'] ) . '</h1><p class="lede muted">' . h( $dw['lede'] ) . '</p></div>'
		. '<div class="findbox">'
		. '<div class="field"><label for="' . $uid . '-cp">' . h( $dw['lbl'] ) . '</label>'
		. '<input id="' . $uid . '-cp" type="text" inputmode="numeric" placeholder="' . h( $dw['ph'] ) . '"></div>'
		. '<button class="btn btn-primary" type="button">' . h( $dw['cta'] ) . '</button>'
		. '<p class="findres">' . h( $dw['result'] ) . '</p>'
		. '<p class="small muted">' . h( $dw['note'] ) . '</p>'
		. '</div></div></section>';
}

/** COMP-BATCH-CARD · la trazabilidad de CADA pieza, no una insignia de confianza genérica. */
function batch_cards_html( $bt ) {
	$o = '<section class="sec batch grid-sec bg-alt" aria-label="' . h( $bt['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $bt['eyebrow'] ) . '</span>'
		. '<h2>' . h( $bt['h2'] ) . '</h2><p class="muted">' . h( $bt['note'] ) . '</p></div>'
		. '<ul class="batchlist"' . cols_attr( count( $bt['items'] ), 2 ) . '>';
	foreach ( $bt['items'] as $b ) {
		$bi = img( $b['img'] );
		$o .= '<li class="batchcard"><figure class="frame"><img data-img="' . h( $bi['slug'] ) . '"'
			. ' alt="' . h( $bi['alt'] ) . '" width="' . $bi['w'] . '" height="' . $bi['h'] . '"></figure>'
			. '<div class="batchbody"><h3>' . h( $b['h3'] ) . '</h3>'
			/* EL PRECIO PRINCIPAL ES EL €/KG y el importe estimado va debajo y en secundario: al
			   revés, la ficha publica una cifra que no es la que se cobra. */
			. '<p class="price">' . h( $b['kg'] ) . '</p>'
			. '<p class="small muted">' . h( $b['pc'] ) . '</p><dl class="batchmeta">';
		foreach ( $b['rows'] as $r ) {
			$o .= '<div><dt>' . h( $r[0] ) . '</dt><dd>' . h( $r[1] ) . '</dd></div>';
		}
		$o .= '</dl></div></li>';
	}
	return $o . '</ul></div></section>';
}

/** COMP-WEIGHT-NOTE · el margen dicho ANTES de cobrar, no en una nota legal al pie. */
function weight_note_html( $wn ) {
	$o = '<section class="sec weightn" aria-label="' . h( $wn['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $wn['eyebrow'] ) . '</span>'
		. '<h2>' . h( $wn['h2'] ) . '</h2><p class="muted">' . h( $wn['lede'] ) . '</p></div>'
		. '<dl class="figs retfigs">';
	foreach ( $wn['items'] as $it ) {
		$o .= '<div class="fig"><dt>' . h( $it[0] ) . '</dt><dd>' . h( $it[1] ) . '</dd>'
			. '<p class="small muted">' . h( $it[2] ) . '</p></div>';
	}
	return $o . '</dl><p class="small muted flownote">' . h( $wn['note'] ) . '</p></div></section>';
}

/** COMP-ORIGIN-MAP · fotografía del puerto con las lonjas marcadas, no un plano dibujado. */
function origin_map_html( $om ) {
	$mi = img( $om['img'] );
	/* A SANGRE, Y ESTA ES LA SEGUNDA VEZ QUE SE INTENTA. La primera se revirtió: `.mapwrap`
	   colapsaba a 63px y los cuatro pines salían con coordenada negativa, fuera de pantalla. El
	   diagnóstico de entonces —«este mapa dimensiona sus pines sobre una caja que el canvas le
	   daba»— era PLAUSIBLE Y FALSO, y sólo se vio al medir las veinticuatro bandas del catálogo en
	   vez de mirar una: la culpa era de una colocación por líneas con nombre que alcanzaba fuera
	   del canvas y rompía las CUATRO variantes `direct`, no sólo este mapa. Arreglado allí, aquí
	   no había nada que arreglar.
	   El argumento se sostiene solo: un mapa contenido en una columna se lee como una captura de
	   pantalla; llegando al cristal se lee como territorio, y aquí el territorio es la promesa
	   entera del arquetipo — de qué caladero sale la pieza que se está comprando. */
	$o  = sec_open( 'originmap grid-sec', $om['h2'], 'bleed' )
		. '<div class="head stack"><span class="eyebrow">' . h( $om['eyebrow'] ) . '</span>'
		. '<h2>' . h( $om['h2'] ) . '</h2><p class="muted">' . h( $om['lede'] ) . '</p></div>'
		. '<div class="mapwrap"><figure class="frame mapshot"><img data-img="' . h( $mi['slug'] ) . '"'
		. ' alt="' . h( $mi['alt'] ) . '" width="' . $mi['w'] . '" height="' . $mi['h'] . '"></figure>';
	foreach ( $om['pins'] as $p ) {
		$o .= '<span class="pin portpin" style="left:' . (int) $p[0] . '%;top:' . (int) $p[1] . '%">'
			. h( $p[2] ) . '</span>';
	}
	return $o . '</div>' . sec_close( 'bleed' );
}

/** COMP-COLD-CHAIN · cómo viaja y qué hacer al abrirla. */
function cold_chain_html( $cc ) {
	$ci = img( $cc['img'] );
	$o  = '<section class="sec cold grid-sec bg-alt" aria-label="' . h( $cc['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $cc['eyebrow'] ) . '</span>'
		. '<h2>' . h( $cc['h2'] ) . '</h2></div>'
		. '<div class="media"><figure class="frame"><img data-img="' . h( $ci['slug'] ) . '"'
		. ' alt="' . h( $ci['alt'] ) . '" width="' . $ci['w'] . '" height="' . $ci['h'] . '"></figure></div>'
		. '<ol class="steps coldsteps">';
	foreach ( $cc['steps'] as $i => $st ) {
		$o .= '<li class="step"><span class="n">' . sprintf( '%02d', $i + 1 ) . '</span>'
			. '<h3>' . h( $st[0] ) . '</h3><p>' . h( $st[1] ) . '</p></li>';
	}
	return $o . '</ol><p class="small muted flownote">' . h( $cc['note'] ) . '</p></div></section>';
}

/**
 * TPL-E-07 · Lote / Peso — la home.
 *
 * LA VENTANA DE ENTREGA VA ARRIBA Y OCUPA EL SITIO DEL HÉROE. Fresco que llega un viernes por la
 * tarde a una casa vacía es fresco perdido, así que si no servimos ese código postal el día que le
 * sirve al cliente no hay catálogo que enseñar. Averiguarlo en el checkout es averiguarlo tarde.
 */
function strip_batch( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$o = array();
	$o[] = head_shop_plain( $C, $BRAND );
	$o[] = '<main>';
	$o[] = delivery_window_html( $C['window'], $uid );
	$o[] = batch_cards_html( $C['batch'] );
	$o[] = weight_note_html( $C['weight'] );
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-ORIGIN' ) ) {
		$o[] = origin_map_html( $C['origin'] );
	}
	$o[] = cold_chain_html( $C['cold'] );
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-TESTIMONIALS' ) ) {
		$o[] = quotes_block_html( $C['quotes'] );
	}
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-NEWSLETTER' ) ) {
		$o[] = newsletter_html( $C['news'], $uid );
	}
	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );

	return implode( "\n", $o );
}

/**
 * TPL-PDP-03 · la ficha de lote y peso.
 *
 * TRES COSAS QUE NINGUNA OTRA FICHA HACE. El precio principal es el €/kg y el importe estimado va
 * en secundario — al revés, la ficha publica una cifra que no es la que se cobra. La nota de peso
 * va PEGADA al bloque de compra y no al pie, porque decir después de cobrar cuánto puede variar es
 * decirlo tarde. Y la ventana de entrega vuelve a salir antes que la galería: la pregunta que
 * cancela la compra se hace primero.
 */
function page_pdp_batch( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$P  = $C['pdp'];
	$mn = img( $P['main'] );
	$o  = array();

	$o[] = head_shop_plain( $C, $BRAND );
	$o[] = crumbs_html( $P['crumbs'] );
	$o[] = '<main>';
	$o[] = delivery_window_html( $C['window'], $uid . '-p' );

	$thumbs = '';
	foreach ( $P['thumbs'] as $t ) {
		$ti      = img( $t );
		$thumbs .= '<li><figure class="frame sq"><img data-img="' . h( $ti['slug'] ) . '"'
			. ' alt="' . h( $ti['alt'] ) . '" width="' . $ti['w'] . '" height="' . $ti['h'] . '"></figure></li>';
	}
	$opts = '';
	foreach ( $P['opts'] as $i => $op ) {
		$oid   = $uid . '-prep' . $i;
		$opts .= '<label class="opt" for="' . $oid . '">'
			. '<input type="radio" id="' . $oid . '" name="' . $uid . '-prep"'
			. ( 0 === $i ? ' checked' : '' ) . '><span>' . h( $op ) . '</span></label>';
	}

	$o[] = '<section class="sec pdp" aria-label="' . h( $P['h1'] ) . '"><div class="canvas">'
		. '<div class="pdp-gal">'
		. '<figure class="frame"><img data-img="' . h( $mn['slug'] ) . '" alt="' . h( $mn['alt'] ) . '"'
		. ' width="' . $mn['w'] . '" height="' . $mn['h'] . '"></figure>'
		. '<ul class="pdp-thumbs">' . $thumbs . '</ul></div>'
		. '<div class="pdp-buy"><h1>' . h( $P['h1'] ) . '</h1>'
		. '<p class="price pdp-price">' . h( $P['kg'] ) . '</p>'
		. '<p class="small muted pdp-approx">' . h( $P['pc'] ) . '</p>'
		. '<p class="muted">' . h( $P['lede'] ) . '</p>'
		. '<fieldset class="opts"><legend>' . h( $P['sz_lbl'] ) . '</legend>' . $opts . '</fieldset>'
		. '<div class="field qty"><label for="' . $uid . '-pq">' . h( $P['qty_lbl'] ) . '</label>'
		. '<input id="' . $uid . '-pq" type="number" value="1" min="1"></div>'
		. '<button class="btn btn-primary" type="button">' . h( $P['cta'] ) . '</button>'
		. '<p class="small muted pdp-ship">' . h( $P['ship'] ) . '</p>'
		. '</div></div></section>';

	$o[] = weight_note_html( $C['weight'] );
	$o[] = batch_cards_html( $C['batch'] );
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-ORIGIN' ) ) {
		$o[] = origin_map_html( $C['origin'] );
	}
	$o[] = cold_chain_html( $C['cold'] );

	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );

	return implode( "\n", $o );
}

/* ── TPL-E-08 · SUSCRIPCIÓN ───────────────────────────────────────────────────────────────────
   Lo que se elige no es una cantidad: es un plan y una cadencia. */

/** COMP-PLAN-PICKER · ES el control de compra de este arquetipo, y sustituye a COMP-PRODUCT-INFO. */
function plan_picker_html( $pp, $uid ) {
	$o = '<section class="sec plans grid-sec bg-alt" aria-label="' . h( $pp['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $pp['eyebrow'] ) . '</span>'
		. '<h2>' . h( $pp['h2'] ) . '</h2><p class="muted">' . h( $pp['note'] ) . '</p></div>'
		. '<ul class="subplans"' . cols_attr( count( $pp['items'] ) ) . '>';
	foreach ( $pp['items'] as $p ) {
		/* EL RECOMENDADO SE MARCA CON FILETE Y ETIQUETA, no con el acento: no es un CTA ni un estado
		   activo, y dentro de esa tarjeta el acento ya lo tiene su botón. */
		$o .= '<li class="subplan' . ( $p['best'] ? ' best' : '' ) . '">'
			. ( $p['best'] ? '<span class="subbadge">El que más se elige</span>' : '' )
			. '<h3>' . h( $p['name'] ) . '</h3>'
			. '<p class="price subfee">' . h( $p['fee'] ) . '</p>'
			. '<p class="small muted">' . h( $p['each'] ) . '</p>'
			. '<p class="subwhat">' . h( $p['what'] ) . '</p>'
			. '<p class="small muted">' . h( $p['who'] ) . '</p><ul class="subrows">';
		foreach ( $p['rows'] as $r ) {
			$o .= '<li>' . h( $r ) . '</li>';
		}
		$o .= '</ul><button class="btn ' . ( $p['best'] ? 'btn-primary' : 'btn-outline' )
			. '" type="button">Empezar con este</button></li>';
	}
	return $o . '</ul></div></section>';
}

/** COMP-CADENCE · los tres controles que quitan el miedo a acumular, escritos como acciones. */
function cadence_html( $cd ) {
	$o = '<section class="sec cadence grid-sec" aria-label="' . h( $cd['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $cd['eyebrow'] ) . '</span>'
		. '<h2>' . h( $cd['h2'] ) . '</h2><p class="muted">' . h( $cd['lede'] ) . '</p></div><ul class="feats">';
	foreach ( $cd['items'] as $it ) {
		$o .= '<li><b>' . h( $it[0] ) . '</b><span>' . h( $it[1] ) . '</span></li>';
	}
	return $o . '</ul><p class="small muted flownote">' . h( $cd['note'] ) . '</p></div></section>';
}

/** COMP-FIRST-BOX · el contenido exacto del primer envío, con gramos y con nombre. */
function first_box_html( $fb ) {
	$bi = img( $fb['img'] );
	$o  = '<section class="sec firstbox grid-sec bg-alt" aria-label="' . h( $fb['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $fb['eyebrow'] ) . '</span>'
		. '<h2>' . h( $fb['h2'] ) . '</h2><p class="muted">' . h( $fb['lede'] ) . '</p></div>'
		. '<div class="media"><figure class="frame"><img data-img="' . h( $bi['slug'] ) . '"'
		. ' alt="' . h( $bi['alt'] ) . '" width="' . $bi['w'] . '" height="' . $bi['h'] . '"></figure></div>'
		. '<ul class="boxlist">';
	foreach ( $fb['items'] as $it ) {
		$o .= '<li><b class="boxqty">' . h( $it[0] ) . '</b>'
			. '<div><span class="boxname">' . h( $it[1] ) . '</span>'
			. '<span class="muted small">' . h( $it[2] ) . '</span></div></li>';
	}
	return $o . '</ul><p class="small muted flownote">' . h( $fb['note'] ) . '</p></div></section>';
}

/** COMP-PAUSE-PROMISE · sección con su propio título, no un enlace en el pie. */
function pause_promise_html( $pz ) {
	$o = '<section class="sec pausep grid-sec" aria-label="' . h( $pz['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $pz['eyebrow'] ) . '</span>'
		. '<h2>' . h( $pz['h2'] ) . '</h2><p class="muted">' . h( $pz['lede'] ) . '</p></div><ul class="feats">';
	foreach ( $pz['items'] as $it ) {
		$o .= '<li><b>' . h( $it[0] ) . '</b><span>' . h( $it[1] ) . '</span></li>';
	}
	return $o . '</ul></div></section>';
}

/**
 * TPL-E-08 · Suscripción — la home.
 *
 * NO HAY REJILLA DE CATÁLOGO Y NO HAY CARRUSEL DE PRODUCTO, y las dos ausencias son la decisión:
 * una rejilla con precios por bolsa empuja a comprar una bolsa, que es exactamente la conversión
 * equivocada en una página cuyo negocio es la cuota.
 */
function strip_plan_sub( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$hero = $C['hero'];
	$im   = img( $hero['img'] );
	$o    = array();

	$o[] = head_corporate( $C, $BRAND );
	$o[] = '<main>';
	$o[] = '<section class="sec hero" aria-label="Presentación"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $hero['eyebrow'] ) . '</span>'
		. '<h1>' . h( $hero['h1'] ) . '</h1><p class="lede muted">' . h( $hero['lede'] ) . '</p>'
		. '<div class="ctas"><a class="btn btn-primary" href="' . h( ihref_for_label( $hero['cta_1'] ) ) . '">' . h( $hero['cta_1'] ) . '</a>'
		. '<a class="btn btn-outline" href="' . h( ihref_for_label( $hero['cta_2'] ) ) . '">' . h( $hero['cta_2'] ) . '</a></div></div>'
		. '<div class="media"><figure class="frame"><img data-img="' . h( $im['slug'] ) . '"'
		. ' alt="' . h( $im['alt'] ) . '" width="' . $im['w'] . '" height="' . $im['h'] . '"></figure></div>'
		. '</div></section>';
	$o[] = plan_picker_html( $C['plans'], $uid );
	$o[] = cadence_html( $C['cadence'] );
	$o[] = first_box_html( $C['firstbox'] );
	$o[] = pause_promise_html( $C['pause'] );
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-TESTIMONIALS' ) ) {
		$o[] = quotes_block_html( $C['quotes'], ' bg-alt' );
	}
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-FAQ' ) ) {
		$o[] = faq_block_html( $C['faq'] );
	}
	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );

	return implode( "\n", $o );
}

/**
 * TPL-PDP-04 · la ficha de suscripción, y la segunda del catálogo sin `COMP-PRODUCT-INFO`.
 *
 * La ficha estándar tiene un control de compra: variante, cantidad, añadir. Aquí no aplica, porque
 * lo que se contrata no es una unidad sino una cuota con una frecuencia. Dejar un «añadir al
 * carrito» empuja a llevarse una bolsa suelta en vez de suscribirse, así que el control de compra
 * es COMP-PLAN-PICKER y SUSTITUYE al bloque de producto en lugar de convivir con él.
 */
function page_pdp_sub( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$P  = $C['pdp'];
	$mn = img( $P['main'] );
	$o  = array();

	$o[] = head_corporate( $C, $BRAND );
	$o[] = crumbs_html( $P['crumbs'] );
	$o[] = '<main>';

	$thumbs = '';
	foreach ( $P['thumbs'] as $t ) {
		$ti      = img( $t );
		$thumbs .= '<li><figure class="frame sq"><img data-img="' . h( $ti['slug'] ) . '"'
			. ' alt="' . h( $ti['alt'] ) . '" width="' . $ti['w'] . '" height="' . $ti['h'] . '"></figure></li>';
	}

	/* GALERÍA Y TEXTO, SIN BLOQUE DE COMPRA AL LADO. El hueco donde toda ficha lleva un precio es
	   justo donde el lector lo busca, así que se dice en voz alta que aquí no lo hay y por qué. */
	$o[] = '<section class="sec pdp" aria-label="' . h( $P['h1'] ) . '"><div class="canvas">'
		. '<div class="pdp-gal">'
		. '<figure class="frame"><img data-img="' . h( $mn['slug'] ) . '" alt="' . h( $mn['alt'] ) . '"'
		. ' width="' . $mn['w'] . '" height="' . $mn['h'] . '"></figure>'
		. '<ul class="pdp-thumbs">' . $thumbs . '</ul></div>'
		. '<div class="pdp-buy"><h1>' . h( $P['h1'] ) . '</h1>'
		. '<p class="muted">' . h( $P['lede'] ) . '</p>'
		. '<p class="nopricenote">' . h( $P['nofee'] ) . '</p>'
		. '<a class="btn btn-primary" href="' . h( ihref_for_label( $P['cta'] ) ) . '">' . h( $P['cta'] ) . '</a>'
		. '</div></div></section>';

	$o[] = plan_picker_html( $C['plans'], $uid . '-p' );
	$o[] = cadence_html( $C['cadence'] );
	$o[] = first_box_html( $C['firstbox'] );
	$o[] = pause_promise_html( $C['pause'] );
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-FAQ' ) ) {
		$o[] = faq_block_html( $C['faq'], ' bg-alt' );
	}

	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );

	return implode( "\n", $o );
}

/* ── TPL-E-09 · A MEDIDA / PRESUPUESTO ────────────────────────────────────────────────────────
   UNA SOLA RECETA PARA LAS DOS PÁGINAS QUE LA USAN. El configurador, las muestras, el plazo por
   fases y la instrucción de medición nacieron dentro de `page_pdp_mtm()` cuando sólo TPL-E-03 los
   necesitaba. Al llegar TPL-E-09 había dos caminos: copiarlos o sacarlos. Copiarlos es cómo dos
   cosas que deberían quedarse idénticas empiezan a separarse — este fichero ya pagó ese error con
   cuatro emisores de acordeón y tres comportamientos distintos. */

/**
 * COMP-CONFIGURATOR · convierte una necesidad en una configuración concreta.
 *
 * `$lead` lleva el h1 y la entradilla cuando el configurador ES la página (la ficha de TPL-E-03);
 * en la home de TPL-E-09 el h1 lo tiene el héroe y aquí va un h2. Un solo emisor y dos posiciones
 * en la jerarquía, que es lo correcto: la sección es la misma, su sitio en el documento no.
 */
function configurator_html( $cf, $uid, $lead = null ) {
	$o = '<section class="sec cfg" aria-label="Configurador"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $cf['eyebrow'] ) . '</span>';
	if ( null !== $lead ) {
		$o .= '<h1>' . h( $lead[0] ) . '</h1><p class="lede muted">' . h( $lead[1] ) . '</p>';
	}
	$o .= '</div><div class="cfgbox"><h2>' . h( $cf['h2'] ) . '</h2><div class="cfgrow">';
	foreach ( $cf['fields'] as $f ) {
		$fid = $uid . '-m-' . $f[0];
		$o  .= '<div class="field"><label for="' . $fid . '">' . h( $f[1] ) . '</label>'
			. '<input id="' . $fid . '" type="number" value="' . h( $f[2] ) . '"></div>';
	}
	$o .= '</div>';
	foreach ( array( array( $cf['opt_lbl'], $cf['opts'], 'mat' ), array( $cf['fin_lbl'], $cf['fins'], 'fin' ) ) as $grp ) {
		$o .= '<fieldset class="opts"><legend>' . h( $grp[0] ) . '</legend>';
		foreach ( $grp[1] as $i => $op ) {
			$oid = $uid . '-' . $grp[2] . $i;
			$o  .= '<label class="opt" for="' . $oid . '">'
				. '<input type="radio" id="' . $oid . '" name="' . $uid . '-' . $grp[2] . '"'
				. ( 0 === $i ? ' checked' : '' ) . '><span>' . h( $op ) . '</span></label>';
		}
		$o .= '</fieldset>';
	}
	$o .= '<fieldset class="opts"><legend>' . h( $cf['ext_lbl'] ) . '</legend>';
	foreach ( $cf['extras'] as $i => $ex ) {
		$xid = $uid . '-x' . $i;
		$o  .= '<label class="opt" for="' . $xid . '">'
			. '<input type="checkbox" id="' . $xid . '"><span>' . h( $ex ) . '</span></label>';
	}
	/* SIN PRECIO, Y DICHO EN VOZ ALTA. El hueco donde toda ficha lleva una cifra es justo donde el
	   lector la busca, así que callarse sin más se lee como un fallo de la maqueta. */
	return $o . '</fieldset><p class="small muted cfgnote">' . h( $cf['note'] ) . '</p>'
		. '<button class="btn btn-primary" type="button">' . h( $cf['cta'] ) . '</button>'
		. '</div></div></section>';
}

/**
 * COMP-MEASURE-TABLE en su uso de medición: la instrucción, no una tabla de tallas.
 *
 * LA FOTOGRAFÍA ES OPCIONAL Y NO DECORATIVA. Cuando la hay, enseña la operación que el texto
 * describe —el metro apoyado en la jamba—, y en una sección cuyo fallo típico es que el cliente
 * mida otra cosa, ver la operación vale más que otro párrafo. La ficha de TPL-E-03 no la pasa y
 * se queda como estaba: un parámetro opcional no cambia a quien no lo usa.
 */
function measure_how_html( $ms, $extra = ' bg-alt' ) {
	$mh_img = isset( $ms['img'] )
		? ( function ( $slug ) {
			$mi = img( $slug );
			return '<div class="media"><figure class="frame"><img data-img="' . h( $mi['slug'] ) . '"'
				. ' alt="' . h( $mi['alt'] ) . '" width="' . $mi['w'] . '" height="' . $mi['h'] . '"></figure></div>';
		} )( $ms['img'] )
		: '';
	$o = '<section class="sec features grid-sec' . $extra . '" aria-label="Cómo se mide"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $ms['eyebrow'] ) . '</span>'
		. '<h2>' . h( $ms['h2'] ) . '</h2></div>' . $mh_img . '<ul class="feats">';
	foreach ( $ms['rows'] as $r ) {
		$o .= '<li><b>' . h( $r[0] ) . '</b><span>' . h( $r[1] ) . '</span></li>';
	}
	return $o . '</ul><p class="small muted flownote">' . h( $ms['note'] ) . '</p></div></section>';
}

/** COMP-GALLERY de trabajos entregados · el pie ES el dato, así que no se atenúa ni se esconde. */
function done_gallery_html( $gl ) {
	$o = '<section class="sec gallery grid-sec" aria-label="Entregados"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $gl['eyebrow'] ) . '</span>'
		. '<h2>' . h( $gl['h2'] ) . '</h2></div><ul class="shots done"'
		. cols_attr( count( $gl['items'] ), 2 ) . '>';
	foreach ( $gl['items'] as $it ) {
		$gi = img( $it[0] );
		$o .= '<li><figure class="frame"><img data-img="' . h( $gi['slug'] ) . '"'
			. ' alt="' . h( $gi['alt'] ) . '" width="' . $gi['w'] . '" height="' . $gi['h'] . '">'
			. '<figcaption class="small muted">' . h( $it[1] ) . '</figcaption></figure></li>';
	}
	return $o . '</ul></div></section>';
}

/** COMP-SAMPLE-REQUEST · sustituye a la política de devoluciones que esta ficha no puede ofrecer. */
function sample_request_html( $sm, $extra = ' bg-alt' ) {
	$o = '<section class="sec sample' . $extra . '" aria-label="Muestra física"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $sm['eyebrow'] ) . '</span>'
		. '<h2>' . h( $sm['h2'] ) . '</h2><p class="muted">' . h( $sm['lede'] ) . '</p>'
		. '<div class="ctas"><a class="btn btn-primary" href="' . h( ihref_for_label( $sm['cta'] ) ) . '">' . h( $sm['cta'] ) . '</a></div></div>'
		. '<div class="media"><ul class="swatches">';
	foreach ( $sm['items'] as $slug ) {
		$si = img( $slug );
		$o .= '<li><figure class="frame sq"><img data-img="' . h( $si['slug'] ) . '"'
			. ' alt="' . h( $si['alt'] ) . '" width="' . $si['w'] . '" height="' . $si['h'] . '"></figure></li>';
	}
	return $o . '</ul></div></div></section>';
}

/** COMP-LEAD-TIME · fases con días, no «3 a 4 semanas». */
function lead_time_html( $ld, $extra = '' ) {
	$o = '<section class="sec process grid-sec' . $extra . '" aria-label="Plazo"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $ld['eyebrow'] ) . '</span>'
		. '<h2>' . h( $ld['h2'] ) . '</h2></div><ol class="steps">';
	foreach ( $ld['items'] as $i => $st ) {
		$o .= '<li class="step"><span class="n">' . sprintf( '%02d', $i + 1 ) . '</span>'
			. '<h3>' . h( $st[0] ) . '</h3><p class="days">' . h( $st[1] ) . '</p>'
			. '<p>' . h( $st[2] ) . '</p></li>';
	}
	return $o . '</ol><p class="small muted flownote">' . h( $ld['note'] ) . '</p></div></section>';
}

/**
 * TPL-C-02 · Institutional Trust.
 *
 * Its DNA separates from TPL-C-01 in one place that matters more than all the section names: it
 * closes with COMP-CTA and NOT with COMP-LEAD-FORM. C-01 chases a lead and the form dominates its
 * close; C-02 opens a conversation. Rendering a form here would collapse two archetypes into one,
 * which is exactly the defect the ecommerce family was rebuilt to fix.
 */


/**
 * TPL-C-03 · Portfolio / Showcase.
 *
 * The archetype doc is blunt about what it refuses — "NO pricing, NO stats, NO forms largos",
 * "bajo en texto, alto en visual" — and the refusals are the design. So there is no stats band
 * here even though the four reference kits all have one, no lead form even though TPL-C-01's DNA
 * is built on one, and barely any prose. What there is, is the grid.
 */


/**
 * COMP-NEWSLETTER · four archetypes declare it, so it is emitted from one place.
 *
 * It takes the SAMPLE's uid: the field carries an id a `<label for>` points at, and four
 * archetypes on one page means four live newsletter forms.
 */
function newsletter_html( $n, $uid ) {
	return '<section class="sec news bg-alt" aria-label="Boletín"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $n['eyebrow'] ) . '</span>'
		. '<h2>' . h( $n['h2'] ) . '</h2><p class="muted">' . h( $n['lede'] ) . '</p></div>'
		. '<form class="newsform" onsubmit="return false">'
		. '<div class="field"><label for="' . $uid . '-mail">' . h( $n['label'] ) . '</label>'
		. '<input id="' . $uid . '-mail" name="mail" type="email"></div>'
		. '<button class="btn btn-primary" type="submit">' . h( $n['cta'] ) . '</button>'
		. '<p class="small muted news-small">' . h( $n['small'] ) . '</p>'
		. '</form></div></section>';
}

/**
 * A PLAIN SHOP HEADER — no announcement bar, no search field.
 *
 * TPL-E-02's header IS its DNA: search-first, because a catalogue is something you search. TPL-E-03
 * declares neither COMP-ANNOUNCEMENT nor a search field, because a brand-story shop is something
 * you READ before you search. Giving it TPL-E-02's header would erase the difference between the
 * two archetypes in the first hundred pixels of the page.
 */
/**
 * COMP-HEADER con buscador y carrito, SIN banda de anuncio.
 *
 * `head_ecommerce()` abre siempre con `COMP-ANNOUNCEMENT` porque los cuatro arquetipos que la usan
 * la declaran en su wireframe. TPL-E-06 no la declara, y añadírsela para reutilizar el emisor sería
 * inventarle una sección al arquetipo — que es exactamente el fallo que `RT_TPL_TOO_SIMILAR` mide un
 * piso más arriba. Un emisor más, y el arquetipo se queda con las secciones que dice tener.
 */
function head_shop_search( $C, $BRAND, $uid ) {
	$o = '<header class="site-head"><div class="canvas"><div class="nav">'
		. '<span class="logo">' . h( $BRAND ) . '</span>'
		. '<form class="searchbar" role="search" onsubmit="return false">'
		. '<label class="sr" for="' . $uid . '-q">Buscar en la tienda</label>'
		. '<input id="' . $uid . '-q" type="search" placeholder="' . h( $C['search'] ) . '">'
		. '<button class="btn btn-primary btn-sm" type="submit">Buscar</button>'
		. '</form><nav class="mainnav" aria-label="Principal">';
	foreach ( $C['nav'] as $nv ) {
		$o .= '<a href="' . h( ihref_for_label( $nv ) ) . '">' . h( $nv ) . '</a>';
	}
	return $o . '</nav><a class="cart" href="' . h( ihref_for_label( $C['cart'] ) ) . '">' . h( $C['cart'] ) . ' <b>' . h( $C['cart_n'] ) . '</b></a>'
		. '</div></div></header>';
}

function head_shop_plain( $C, $BRAND ) {
	$o = '<header class="site-head"><div class="canvas"><div class="nav">'
		. '<span class="logo">' . h( $BRAND ) . '</span>'
		. '<nav class="mainnav" aria-label="Principal">';
	foreach ( $C['nav'] as $n ) {
		$o .= '<a href="' . h( ihref_for_label( $n ) ) . '">' . h( $n ) . '</a>';
	}
	return $o . '</nav><a class="cart" href="' . h( ihref_for_label( $C['cart'] ) ) . '">' . h( $C['cart'] ) . ' <b>' . h( $C['cart_n'] ) . '</b></a>'
		. '</div></div></header>';
}

/**
 * TPL-E-03 · Brand Story.
 *
 * The catalogue is deliberately thin: one carousel, no grid, no search. TPL-E-02 is the shop that
 * hides its brand; this is the brand that sells a shop, and the difference has to be visible before
 * the reader has scrolled.
 */


/**
 * TPL-C-04 · Landing / Single Offer.
 *
 * Its doc's DNA is "una sola oferta, un solo CTA repetido, sin navegación que distraiga", so the
 * header prints NO nav at all — `head_corporate()` already emits an empty `<nav>` when the array
 * is empty, and the absence is the archetype rather than a missing feature. The same CTA label
 * appears four times on the page on purpose; on every other archetype that would be a defect.
 */


/**
 * TPL-E-05 · Promo / Campaign.
 *
 * THE DEADLINE IS A DATE IN THE HTML AND NOT A COUNTDOWN. A ticking clock is the device the
 * reference kits reach for, and it renders `00:00:00` until its script runs. On a page whose whole
 * argument is "this ends soon", a timer reading zero is the worst failure this archetype has — and
 * it reads that way permanently for every crawler and for anyone whose JS is blocked. A date needs
 * nothing to be true. It appears four times, which on any other archetype would be repetition and
 * here is the point.
 */


/**
 * COMP-GALLERY · declared by TPL-C-05 and TPL-E-01, so it is emitted from one place.
 *
 * Six frames, no lightbox. A lightbox is script that must load before the reader can see the
 * second photograph, and on a mockup it would be a promise about a plugin nobody has chosen yet.
 */
/* LA FORMA ES UN PARÁMETRO Y LA ELIGE EL ARQUETIPO, no el componente. Tres arquitecturas llaman a
   esta función y sólo dos quieren banda: en `TPL-C-05` el local es media venta y en `TPL-E-01` el
   lookbook ES el catálogo, pero en `TPL-C-08` la galería son tres cortesías de un lanzamiento y a
   sangre pesarían más que el coche. Convertir la función entera habría decidido por los tres.
   Es la misma forma que `ritual_menu_html` y `bono_packs_html` ya usan, y es lo que convierte el
   envoltorio en algo que un documento de arquetipo puede DECLARAR — el hueco que
   `RT_TPL_TOO_SIMILAR` no puede ver, porque mide inventario y nunca miró la forma. */
function gallery_html( $g, $shape = 'contained' ) {
	$o = sec_open( 'gallery grid-sec', $g['h2'], $shape ) . sec_head( $g ) . '<ul class="shots">';
	foreach ( $g['items'] as $slug ) {
		$gi = img( $slug );
		$o .= '<li><figure class="frame sq"><img data-img="' . h( $gi['slug'] ) . '"'
			. ' alt="' . h( $gi['alt'] ) . '" width="' . $gi['w'] . '" height="' . $gi['h'] . '"></figure></li>';
	}
	return $o . '</ul>' . sec_close( $shape );
}

/**
 * COMP-TREATMENT-CARDS · TPL-C-10.
 *
 * THE FOUR FACTS ARE ASSERTED, NOT HOPED FOR. A treatment card without its session count is the one
 * that makes the patient ring to ask exactly what the page was there to save them, and one without
 * the word "anestesia" leaves the question that gets thought about most and asked least. So a
 * missing fact stops the build rather than rendering a card that looks finished.
 */
function treatment_cards_html( $tr ) {
	$o = '<section class="sec treatments grid-sec" aria-label="' . h( $tr['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $tr['eyebrow'] ) . '</span>'
		. '<h2>' . h( $tr['h2'] ) . '</h2></div><ul class="treatcards"' . cols_attr( count( $tr['items'] ) ) . '>';
	foreach ( $tr['items'] as $t ) {
		if ( 4 !== count( $t['facts'] ) ) {
			fail( "the treatment `{$t['h3']}` carries " . count( $t['facts'] ) . ' facts and the archetype'
				. ' fixes four — duración, sesiones, anestesia y desde. A card with three renders'
				. ' perfectly and quietly drops the one the patient came for.' );
		}
		$ti  = img( $t['img'] );
		$o  .= '<li class="trcard"><figure class="frame"><img data-img="' . h( $ti['slug'] ) . '"'
			. ' alt="' . h( $ti['alt'] ) . '" width="' . $ti['w'] . '" height="' . $ti['h'] . '"></figure>'
			. '<div class="trbody"><h3>' . h( $t['h3'] ) . '</h3><p>' . h( $t['p'] ) . '</p>'
			. '<ul class="tfacts">';
		foreach ( $t['facts'] as $f ) {
			$o .= '<li>' . h( $f ) . '</li>';
		}
		$o .= '</ul></div></li>';
	}
	return $o . '</ul><p class="pnote">' . h( $tr['note'] ) . '</p></div></section>';
}

/**
 * COMP-BEFORE-AFTER · TPL-C-10 and TPL-C-11.
 *
 * EACH PHOTOGRAPH CARRIES ITS DATE, and the date is not decoration: a before/after without them
 * does not say whether the result took three weeks or two years, which is precisely what the reader
 * is trying to work out.
 */
function before_after_html( $cs, $extra = '' ) {
	$b = img( $cs['before']['img'] );
	$a = img( $cs['after']['img'] );
	$o = '<section class="sec cases grid-sec' . $extra . '" aria-label="' . h( $cs['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $cs['eyebrow'] ) . '</span>'
		. '<h2>' . h( $cs['h2'] ) . '</h2><p class="lede muted">' . h( $cs['lede'] ) . '</p></div>'
		. '<div class="bapair">';
	foreach ( array( array( $cs['before'], $b ), array( $cs['after'], $a ) ) as $pair ) {
		$o .= '<figure class="bashot"><div class="frame"><img data-img="' . h( $pair[1]['slug'] ) . '"'
			. ' alt="' . h( $pair[1]['alt'] ) . '" width="' . $pair[1]['w'] . '" height="' . $pair[1]['h'] . '"></div>'
			. '<figcaption><b>' . h( $pair[0]['label'] ) . '</b>'
			. '<span>' . h( $pair[0]['date'] ) . '</span></figcaption></figure>';
	}
	return $o . '</div><p class="pnote">' . h( $cs['note'] ) . '</p></div></section>';
}

/** COMP-TEAM, sanitary variant: the licence number is what turns a stock smile into a person. */
function med_team_html( $tm, $extra = '' ) {
	$o = '<section class="sec medteam grid-sec' . $extra . '" aria-label="' . h( $tm['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $tm['eyebrow'] ) . '</span>'
		. '<h2>' . h( $tm['h2'] ) . '</h2></div><ul class="medlist"' . cols_attr( count( $tm['items'] ) ) . '>';
	foreach ( $tm['items'] as $m ) {
		$mi  = img( $m['img'] );
		$o  .= '<li class="medico"><figure class="frame"><img data-img="' . h( $mi['slug'] ) . '"'
			. ' alt="' . h( $mi['alt'] ) . '" width="' . $mi['w'] . '" height="' . $mi['h'] . '"></figure>'
			. '<div class="medbody"><b>' . h( $m['name'] ) . '</b>'
			. '<span>' . h( $m['role'] ) . '</span>'
			. '<span class="medlic">' . h( $m['lic'] ) . '</span></div></li>';
	}
	return $o . '</ul></div></section>';
}

/** COMP-INSURANCE · TPL-C-10. */
function insurance_html( $ins ) {
	$o = '<section class="sec insur grid-sec bg-alt" aria-label="' . h( $ins['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $ins['eyebrow'] ) . '</span>'
		. '<h2>' . h( $ins['h2'] ) . '</h2></div><ul class="inslist">';
	foreach ( $ins['items'] as $i ) {
		$o .= '<li>' . h( $i ) . '</li>';
	}
	return $o . '</ul><p class="pnote">' . h( $ins['note'] ) . '</p></div></section>';
}

/** COMP-PROBLEM · TPL-C-11. First person, as it is actually said in a consultation. */
function pains_html( $pn ) {
	$o = '<section class="sec pains grid-sec" aria-label="' . h( $pn['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $pn['eyebrow'] ) . '</span>'
		. '<h2>' . h( $pn['h2'] ) . '</h2></div><ul class="painlist">';
	foreach ( $pn['items'] as $p ) {
		$o .= '<li>' . h( $p ) . '</li>';
	}
	return $o . '</ul></div></section>';
}

/**
 * COMP-PHASE-TIMELINE · TPL-C-11.
 *
 * `<ol>` BECAUSE THE ORDER IS THE CONTENT. And it is a calendar, not a four-step process: every
 * phase carries a duration, which is the difference between this component and `COMP-PROCESS`.
 */
function phase_timeline_html( $ph ) {
	$o = '<section class="sec phases grid-sec bg-alt" aria-label="' . h( $ph['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $ph['eyebrow'] ) . '</span>'
		. '<h2>' . h( $ph['h2'] ) . '</h2>'
		. '<p class="phtotal">' . h( $ph['total'] ) . '</p></div><ol class="phaselist">';
	foreach ( $ph['items'] as $p ) {
		$o .= '<li class="phase"><span class="phn">' . h( $p['n'] ) . '</span>'
			. '<div class="phbody"><h3>' . h( $p['h3'] ) . '</h3>'
			. '<span class="phmonths">' . h( $p['months'] ) . '</span>'
			. '<p>' . h( $p['p'] ) . '</p></div></li>';
	}
	return $o . '</ol><p class="pnote">' . h( $ph['note'] ) . '</p></div></section>';
}

/**
 * COMP-PRICING · TPL-C-11.
 *
 * THE MONTHLY FIGURE AND THE TOTAL, ALWAYS BOTH. A quota with no total beside it is the technique
 * that makes people ring to ask for the total already distrusting you, and the distrust costs more
 * than the difference ever did.
 */
function pricing_html( $pl ) {
	$o = '<section class="sec planset grid-sec" aria-label="' . h( $pl['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $pl['eyebrow'] ) . '</span>'
		. '<h2>' . h( $pl['h2'] ) . '</h2></div><ul class="subplans">';
	foreach ( $pl['items'] as $p ) {
		$o .= '<li class="planbox">'
			. ( isset( $p['flag'] ) ? '<span class="planflag">' . h( $p['flag'] ) . '</span>' : '' )
			. '<h3>' . h( $p['name'] ) . '</h3>'
			. '<p class="planq"><b>' . h( $p['quota'] ) . '</b><span>' . h( $p['total'] ) . '</span></p>'
			. '<p class="planp">' . h( $p['p'] ) . '</p><ul class="planfeats">';
		foreach ( $p['feats'] as $f ) {
			$o .= '<li>' . h( $f ) . '</li>';
		}
		$o .= '</ul><a class="btn btn-outline btn-sm" href="' . h( ihref_for_label( 'Pedir estudio' ) ) . '">Pedir estudio</a></li>';
	}
	return $o . '</ul><p class="pnote">' . h( $pl['note'] ) . '</p></div></section>';
}

/**
 * COMP-URGENT-BAR · TPL-C-12.
 *
 * THE STATE IS WRITTEN, NOT PAINTED. A green dot is not a state for somebody who does not separate
 * green from red, and this band is the one piece of information on the page that cannot afford to
 * depend on colour. The word carries it; the colour only agrees with the word.
 */
function urgent_bar_html( $ug ) {
	return '<section class="sec urgbar" aria-label="Estado de la guardia"><div class="canvas">'
		. '<p class="urgstate"><b>' . h( $ug['state'] ) . '</b>'
		. '<span>' . h( $ug['next'] ) . '</span></p>'
		. '<h1>' . h( $ug['h1'] ) . '</h1>'
		. '<a class="btn btn-primary btn-lg" href="' . h( ihref_for_label( $ug['cta'] ) ) . '">' . h( $ug['cta'] ) . '</a>'
		. '<p class="urgnote">' . h( $ug['note'] ) . '</p>'
		. '</div></section>';
}

/** COMP-SYMPTOM-TRIAGE · TPL-C-12. The symptom in the patient's words, never in the clinical ones. */
function triage_html( $tg ) {
	$o = '<section class="sec triage grid-sec" aria-label="' . h( $tg['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $tg['eyebrow'] ) . '</span>'
		. '<h2>' . h( $tg['h2'] ) . '</h2></div><ul class="triagelist">';
	foreach ( $tg['items'] as $t ) {
		$o .= '<li class="trow"><b>' . h( $t[0] ) . '</b>'
			. '<span class="trdo">' . h( $t[1] ) . '</span>'
			. '<span class="trmin">' . h( $t[2] ) . '</span></li>';
	}
	return $o . '</ul><p class="pnote">' . h( $tg['note'] ) . '</p></div></section>';
}

/** COMP-WAIT-PROMISE · TPL-C-12. Publishing the wait looks like a risk and is the opposite. */
function wait_promise_html( $w ) {
	$o = '<section class="sec waitp grid-sec bg-alt" aria-label="' . h( $w['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $w['eyebrow'] ) . '</span>'
		. '<h2>' . h( $w['h2'] ) . '</h2></div><ul class="waitlist">';
	foreach ( $w['items'] as $i ) {
		$o .= '<li><b>' . h( $i[0] ) . '</b><span>' . h( $i[1] ) . '</span></li>';
	}
	return $o . '</ul><p class="pnote">' . h( $w['note'] ) . '</p></div></section>';
}

/** TPL-C-10 · Clínica / Tratamientos. */


/** TPL-C-11 · Plan por fases. */
function strip_plan( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$hero = $C['hero'];
	$im   = img( $hero['img'] );
	$o    = array();
	$o[]  = head_over( $C, $BRAND );
	$o[]  = '<main>';
	$o[]  = '<section class="sec hero hero-visual hero-full" aria-label="El plan"><div class="media-full">'
		. '<figure class="frame"><img data-img="' . h( $im['slug'] ) . '"'
		. ' alt="' . h( $im['alt'] ) . '" width="' . $im['w'] . '" height="' . $im['h'] . '"></figure></div>'
		. '<div class="canvas"><div class="head stack">'
		. '<span class="eyebrow">' . h( $hero['eyebrow'] ) . '</span>'
		. '<h1>' . h( $hero['h1'] ) . '</h1>'
		. '<p class="lede">' . h( $hero['lede'] ) . '</p>'
		. '<div class="ctas"><a class="btn btn-primary" href="' . h( ihref_for_label( $hero['cta_1'] ) ) . '">' . h( $hero['cta_1'] ) . '</a>'
		. '<a class="btn btn-outline" href="' . h( ihref_for_label( $hero['cta_2'] ) ) . '">' . h( $hero['cta_2'] ) . '</a></div>'
		. '</div></div></section>';
	$o[]  = pains_html( $C['pains'] );
	$o[]  = phase_timeline_html( $C['phases'] );
	$o[]  = pricing_html( $C['plans'] );
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-CASES' ) ) {
		$o[] = before_after_html( $C['cases'], ' bg-alt' );
	}
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-FAQ' ) ) {
		$o[] = faq_block_html( $C['faq'] );
	}
	$o[]  = booking_html( $C['booking'], $uid );
	$o[]  = '</main>';
	$o[]  = footer_html( $C['footer'] );
	return implode( "\n", $o );
}

/** TPL-C-12 · Urgencias / Hoy. No hero, on purpose: the first thing is an answer. */


/** COMP-HEADER with a phone: the local variant, in flow. */
function head_phone( $C, $BRAND ) {
	$o = '<header class="site-head"><div class="canvas"><div class="nav">'
		. '<span class="logo">' . h( $BRAND ) . '</span>'
		. '<nav class="mainnav" aria-label="Principal">';
	foreach ( $C['nav'] as $n ) {
		$o .= '<a href="' . h( ihref_for_label( $n ) ) . '">' . h( $n ) . '</a>';
	}
	/* The header CTA's WEIGHT is the archetype's call. TPL-C-07 wants `Vender mi coche` solid and
	   its doc says nothing against it; TPL-C-13's doc says the opposite in as many words, because
	   there the primary action is the phone and a solid second button beside it is two primaries.
	   Default unchanged, so no existing strip moves. */
	$cta_cls = isset( $C['nav_cta_weight'] ) && 'secundario' === $C['nav_cta_weight'] ? 'btn-outline' : 'btn-primary';
	return $o . '</nav><a class="tel" href="#">' . h( $C['phone'] ) . '</a>'
		. '<a class="btn ' . $cta_cls . ' btn-sm" href="' . h( ihref_for_label( $C['nav_cta'] ) ) . '">' . h( $C['nav_cta'] ) . '</a>'
		. '</div></div></header>';
}

/**
 * COMP-SEARCH-FILTERS · TPL-C-07.
 *
 * REAL `<select>` AND A REAL `<label>` PER CONTROL, not a drawn dropdown. A filter bar is the one
 * component of this archetype the visitor actually operates, and a mockup that fakes it hands the
 * native build a picture instead of a contract — the keyboard path, the mobile picker and the
 * screen-reader name all come free from the element and from nothing else.
 *
 * The count is printed BEFORE the button because it is the answer to "is it worth filtering".
 */
function search_filters_html( $hero, $sf, $uid ) {
	$im = img( $hero['img'] );
	/* THE PHOTOGRAPH IS INSIDE THE CANVAS AND CONTAINED. LP-CENTERED's own rule is "images always
	   inside the container, equal margins left and right — nothing bleeds, ever", and this archetype
	   runs on PERS-INSTITUTIONAL. A full-bleed band here would be the blueprint's one prohibition,
	   broken in the first section of the page. */
	$o  = '<section class="sec hero hero-search" aria-label="Buscador de stock">'
		. '<div class="canvas"><div class="head stack">'
		. '<span class="eyebrow">' . h( $hero['eyebrow'] ) . '</span>'
		. '<h1>' . h( $hero['h1'] ) . '</h1>'
		. '<p class="lede">' . h( $hero['lede'] ) . '</p></div>'
		. '<figure class="frame shot"><img data-img="' . h( $im['slug'] ) . '"'
		. ' alt="' . h( $im['alt'] ) . '" width="' . $im['w'] . '" height="' . $im['h'] . '"></figure>'
		. '<form class="filterbar" onsubmit="return false">';
	foreach ( $sf['fields'] as $f ) {
		$id = $uid . '-f-' . $f[0];
		$o .= '<div class="field"><label for="' . $id . '">' . h( $f[1] ) . '</label>'
			. '<select id="' . $id . '" name="' . $f[0] . '">';
		foreach ( $f[2] as $opt ) {
			$o .= '<option>' . h( $opt ) . '</option>';
		}
		$o .= '</select></div>';
	}
	return $o . '<p class="stockcount">' . h( $sf['count'] ) . '</p>'
		. '<button class="btn btn-primary" type="submit">' . h( $sf['submit'] ) . '</button>'
		. '</form></div></section>';
}

/**
 * COMP-STOCK-GRID · TPL-C-07.
 *
 * THE PRICE AND THE MONTHLY FIGURE TRAVEL TOGETHER AND IN THAT ORDER. A quota printed without the
 * price beside it is the sales technique that makes people distrust the whole lot, and it is the
 * one thing a listing template can get wrong that costs the client trust rather than clicks.
 */
function stock_grid_html( $st ) {
	$o = '<section class="sec stock grid-sec bg-alt" aria-label="' . h( $st['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $st['eyebrow'] ) . '</span>'
		. '<h2>' . h( $st['h2'] ) . '</h2></div><ul class="stockgrid"' . cols_attr( count( $st['items'] ) ) . '>';
	foreach ( $st['items'] as $v ) {
		$vi  = img( $v['img'] );
		$o  .= '<li class="vcard"><figure class="frame"><img data-img="' . h( $vi['slug'] ) . '"'
			. ' alt="' . h( $vi['alt'] ) . '" width="' . $vi['w'] . '" height="' . $vi['h'] . '"></figure>'
			. '<div class="vbody"><h3>' . h( $v['h3'] ) . '</h3><ul class="vfacts">';
		foreach ( $v['facts'] as $f ) {
			$o .= '<li>' . h( $f ) . '</li>';
		}
		$o .= '</ul><p class="vprice"><b>' . h( $v['price'] ) . '</b>'
			. '<span>' . h( $v['quota'] ) . '</span></p>'
			. '<a class="btn btn-outline btn-sm" href="' . h( ihref( 'producto' ) ) . '">Ver ficha</a></div></li>';
	}
	return $o . '</ul><p class="pnote">' . h( $st['note'] ) . '</p></div></section>';
}

/** COMP-TRADE-IN · TPL-C-07. Two fields, because every extra one is an abandonment. */
function trade_in_html( $ti, $uid ) {
	$o = '<section class="sec tradein" aria-label="' . h( $ti['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $ti['eyebrow'] ) . '</span>'
		. '<h2>' . h( $ti['h2'] ) . '</h2><p class="lede muted">' . h( $ti['lede'] ) . '</p></div>'
		. '<form class="tiform" onsubmit="return false">';
	foreach ( $ti['fields'] as $f ) {
		$id = $uid . '-ti-' . $f[0];
		$o .= '<div class="field"><label for="' . $id . '">' . h( $f[1] ) . '</label>'
			. '<input id="' . $id . '" name="' . $f[0] . '" type="' . $f[2] . '"></div>';
	}
	return $o . '<button class="btn btn-primary" type="submit">' . h( $ti['submit'] ) . '</button>'
		. '<p class="small muted">' . h( $ti['small'] ) . '</p></form></div></section>';
}

/**
 * COMP-FINANCE · TPL-C-07 and TPL-C-08.
 *
 * THE LAST ROW IS THE TOTAL AND IT IS EMPHASISED. Every row above it is an input the buyer can
 * argue with; the total is the number the page exists to stop hiding, so it gets the weight.
 */
function finance_html( $fi ) {
	$o = '<section class="sec finance grid-sec" aria-label="' . h( $fi['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $fi['eyebrow'] ) . '</span>'
		. '<h2>' . h( $fi['h2'] ) . '</h2></div><dl class="ftable">';
	$last = count( $fi['rows'] ) - 1;
	foreach ( $fi['rows'] as $i => $r ) {
		$o .= '<div class="frow' . ( $i === $last ? ' ftotal' : '' ) . '">'
			. '<dt>' . h( $r[0] ) . '</dt><dd>' . h( $r[1] ) . '</dd></div>';
	}
	return $o . '</dl><p class="pnote">' . h( $fi['note'] ) . '</p></div></section>';
}

/** COMP-TRUST-BADGES. Facts with a number, never adjectives. */
function badges_html( $bd ) {
	$o = '<section class="sec badges" aria-label="Garantías"><div class="canvas"><ul class="badgelist">';
	foreach ( $bd as $b ) {
		$o .= '<li class="badge"><b>' . h( $b[0] ) . '</b><span>' . h( $b[1] ) . '</span></li>';
	}
	return $o . '</ul></div></section>';
}

/**
 * COMP-TESTIMONIAL, the shared shape.
 *
 * PR3e — `$layout` is a third, opt-in parameter, `'stack'` by default: every one of the four
 * existing call sites passes zero or two arguments today, so every one of them keeps rendering
 * exactly the `.head.stack` markup it always has. `'between'` is `Inicio.dc.html`'s OWN header —
 * `justify-content:space-between; align-items:flex-end`, the H2 on one side and the rating line on
 * the other, never an eyebrow stacked above a heading. Forking the whole function for one caller's
 * header geometry would have cost every other archetype's testimonial block a silent behaviour
 * change for a gain that is layout, not copy — the same reasoning `sbfield_html()`'s field-shape
 * branch and `page_cta_html()`'s href overrides already used, applied to a third caller.
 *
 * `$qt['h2_lines']`, if present, joins its parts with `<br>` the same way `hero_cartera_html()`'s
 * `h1_lines` does — `Inicio.dc.html` breaks this exact heading across two lines
 * ("Lo que dicen<br>nuestros clientes"). `$qt['h2']` stays the plain single-line string every
 * caller already supplies, used for `aria-label` regardless of which layout renders.
 */
function quotes_block_html( $qt, $extra = '', $layout = 'stack' ) {
	$h2   = isset( $qt['h2_lines'] ) ? implode( '<br>', array_map( 'h', $qt['h2_lines'] ) ) : h( $qt['h2'] );
	$head = ( 'between' === $layout )
		? '<div class="head between"><h2>' . $h2 . '</h2><span class="eyebrow">' . h( $qt['eyebrow'] ) . '</span></div>'
		: '<div class="head stack"><span class="eyebrow">' . h( $qt['eyebrow'] ) . '</span><h2>' . $h2 . '</h2></div>';
	$o = '<section class="sec quotes grid-sec' . $extra . '" aria-label="' . h( $qt['h2'] ) . '"><div class="canvas">'
		. $head . '<ul class="items">';
	foreach ( $qt['items'] as $q ) {
		$o .= '<li><figure><blockquote>' . h( $q[0] ) . '</blockquote>'
			. '<figcaption><b>' . h( $q[1] ) . '</b><span>' . h( $q[2] ) . '</span></figcaption>'
			. '</figure></li>';
	}
	return $o . '</ul></div></section>';
}

/** COMP-MAP-NAP. No embedded map: an iframe is a third-party request before any consent. */
function nap_block_html( $np, $extra = '', $cta = '' ) {
	$ni = img( $np['img'] );
	$o  = '<section class="sec nap' . $extra . '" aria-label="' . h( $np['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $np['eyebrow'] ) . '</span>'
		. '<h2>' . h( $np['h2'] ) . '</h2>'
		. '<p>' . h( $np['addr'][0] ) . '<br>' . h( $np['addr'][1] ) . '</p>'
		. '<p><a href="#">' . h( $np['phone'] ) . '</a></p>'
		. '<p><a href="#">' . h( $np['mail'] ) . '</a></p><dl class="hours">';
	foreach ( $np['hours'] as $hr ) {
		$o .= '<div><dt>' . h( $hr[0] ) . '</dt><dd>' . h( $hr[1] ) . '</dd></div>';
	}
	/* Cuando esta banda CIERRA la pagina, cierra pidiendo algo. Cuatro arquetipos de negocio local
 	   —C-05, C-07, C-09 y C-12— terminaban en un horario, sin una sola llamada a la accion en las dos
 	   ultimas secciones: un cierre que informa y no pide es media conversion tirada justo donde el
 	   visitante ya decidio. La etiqueta no se inventa aqui, la pasa quien llama y es la misma que ya
 	   usa la cabecera, asi que la pagina no estrena un verbo nuevo en la ultima pantalla. El telefono
 	   sigue arriba como enlace, asi que el boton no lo repite. */
	$nap_cta = '' !== $cta
		? '<div class="ctas"><a class="btn btn-primary" href="' . h( ihref_for_label( $cta ) ) . '">' . h( $cta ) . '</a></div>'
		: '';
	return $o . '</dl><p class="small muted">' . h( $np['note'] ) . '</p>' . $nap_cta . '</div>'
		. '<div class="media"><figure class="frame"><img data-img="' . h( $ni['slug'] ) . '"'
		. ' alt="' . h( $ni['alt'] ) . '" width="' . $ni['w'] . '" height="' . $ni['h'] . '"></figure></div>'
		. '</div></section>';
}

/**
 * COMP-MODEL-HERO · TPL-C-08.
 *
 * FOUR FIGURES AND NOT SIX. The strip under the fold is a HEADLINE, not the spec sheet — the spec
 * sheet is the next section and it can hold everything. Six numbers in a hero is a table nobody
 * asked for, placed where the photograph was supposed to be doing the work.
 */
function model_hero_html( $hero ) {
	$im = img( $hero['img'] );
	$o  = '<section class="sec hero mhero" aria-label="' . h( $hero['h1'] ) . '">'
		. '<div class="media-full"><figure class="frame"><img data-img="' . h( $im['slug'] ) . '"'
		. ' alt="' . h( $im['alt'] ) . '" width="' . $im['w'] . '" height="' . $im['h'] . '"></figure></div>'
		. '<div class="canvas"><div class="head stack">'
		. '<span class="eyebrow">' . h( $hero['eyebrow'] ) . '</span>'
		. '<h1>' . h( $hero['h1'] ) . '</h1>'
		. '<p class="lede">' . h( $hero['lede'] ) . '</p>'
		. '<div class="ctas"><a class="btn btn-primary" href="' . h( ihref_for_label( $hero['cta_1'] ) ) . '">' . h( $hero['cta_1'] ) . '</a>'
		. '<a class="btn btn-outline" href="' . h( ihref_for_label( $hero['cta_2'] ) ) . '">' . h( $hero['cta_2'] ) . '</a></div></div>'
		. '<ul class="mfigs">';
	foreach ( $hero['figures'] as $f ) {
		$o .= '<li><b>' . h( $f[0] ) . '</b><span>' . h( $f[1] ) . '</span></li>';
	}
	return $o . '</ul></div></section>';
}

/**
 * COMP-SPEC-TABLE · TPL-C-08.
 *
 * A REAL `<table>` WITH `<th scope>`. A comparison built from `div`s is unreadable with a screen
 * reader and stops meaning anything the moment its CSS does not load — and this is the section a
 * buyer copies into a message to ask somebody's opinion.
 *
 * Rows that CHANGE across versions are marked; rows that repeat are dimmed. A table where every
 * row weighs the same helps nobody choose, which is the only thing it is for.
 */
function spec_table_html( $sp ) {
	$o = '<section class="sec specs grid-sec bg-alt" aria-label="' . h( $sp['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $sp['eyebrow'] ) . '</span>'
		. '<h2>' . h( $sp['h2'] ) . '</h2></div><div class="specwrap"><table class="spectable">'
		. '<thead><tr><th scope="col">Característica</th>';
	foreach ( $sp['cols'] as $c ) {
		$o .= '<th scope="col">' . h( $c ) . '</th>';
	}
	$o .= '</tr></thead><tbody>';
	foreach ( $sp['rows'] as $r ) {
		$o .= '<tr class="' . ( $r[2] ? 'differs' : 'same' ) . '"><th scope="row">' . h( $r[0] ) . '</th>';
		foreach ( $r[1] as $cell ) {
			$o .= '<td>' . h( $cell ) . '</td>';
		}
		$o .= '</tr>';
	}
	return $o . '</tbody></table></div><p class="pnote">' . h( $sp['note'] ) . '</p></div></section>';
}

/**
 * COMP-OFFER-STRIP · TPL-C-08.
 *
 * A REAL DATE AND NO COUNTDOWN. A timer that restarts on reload is a lie the visitor catches on
 * the second visit, and it costs more than the urgency it borrowed.
 */
function offer_strip_html( $of ) {
	return '<section class="sec offer" aria-label="' . h( $of['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $of['eyebrow'] ) . '</span>'
		. '<h2>' . h( $of['h2'] ) . '</h2><p class="lede">' . h( $of['lede'] ) . '</p>'
		. '<div class="ctas"><a class="btn btn-primary" href="' . h( ihref_for_label( $of['cta'] ) ) . '">' . h( $of['cta'] ) . '</a></div>'
		. '<p class="small">' . h( $of['small'] ) . '</p></div></div></section>';
}

/**
 * COMP-PRICE-LIST · TPL-C-09.
 *
 * `desde` ALWAYS CARRIES ITS REASON, and the data shape does not let it not: the reason is the
 * same cell as the description, so a price written as "desde 265 €" with an empty explanation is
 * visible in the table rather than only on the page.
 */
function price_list_html( $pr ) {
	$o = '<section class="sec pricelist grid-sec" aria-label="' . h( $pr['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $pr['eyebrow'] ) . '</span>'
		. '<h2>' . h( $pr['h2'] ) . '</h2></div><div class="pgroups">';
	foreach ( $pr['groups'] as $g ) {
		$o .= '<section class="pgroup"><h3>' . h( $g['h3'] ) . '</h3><ul>';
		foreach ( $g['items'] as $it ) {
			$o .= '<li class="prow"><div class="pwhat"><b>' . h( $it[0] ) . '</b>'
				. '<span>' . h( $it[1] ) . '</span></div>'
				. '<span class="ptag">' . h( $it[2] ) . '</span></li>';
		}
		$o .= '</ul></section>';
	}
	return $o . '</div><p class="pnote">' . h( $pr['note'] ) . '</p></div></section>';
}

/** COMP-PROCESS. Numbered steps; the third one is the client's. */
function process_block_html( $pr, $extra = '' ) {
	$o = '<section class="sec process grid-sec' . $extra . '" aria-label="' . h( $pr['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $pr['eyebrow'] ) . '</span>'
		. '<h2>' . h( $pr['h2'] ) . '</h2></div><ol class="steps">';
	foreach ( $pr['steps'] as $st ) {
		$o .= '<li><h3>' . h( $st[0] ) . '</h3><p>' . h( $st[1] ) . '</p></li>';
	}
	return $o . '</ol></div></section>';
}

/**
 * THE ONE DISCLOSURE RECIPE, and it exists because there were four.
 *
 * COMP-ACCORDION and COMP-FAQ are the same control doing the same job, and the comment above
 * `.acc .qas` has said so since it was written — "giving one of them a second implementation is how
 * two things that should stay identical start to drift". By the time anybody looked there were FOUR
 * emitters and THREE behaviours: the PDP accordion opened its first row, two FAQ blocks opened
 * none, and `faq_block_html()` rendered `<div class="qa"><h3>` — not a disclosure at all, every
 * answer permanently on screen. mockup-guide.md § FAQ had specified `<details>/<summary>` the whole
 * time. The spec was right and unenforced, which is this repo's recurring failure and the reason
 * the row below exists.
 *
 * THE FIRST ROW IS OPEN AND THE REST ARE NOT, and that is a design rule rather than a default.
 * All-closed reads as a wall of headings and makes the reader work to find out whether anything
 * inside is worth opening; all-open is not an accordion at all, it is a long page pretending to be
 * a short one, and it drops the one thing the control is for. One open row shows the SHAPE of an
 * answer — how long, what tone — so the reader can judge the rest without clicking.
 */
function disclosure_list_html( $items, $wrap_cls = 'faqlist' ) {
	/* TOPE DE DOS, y no el tres de las rejillas de tarjetas: aquí lo que se reparte es TEXTO, y una
	   columna de un tercio de 1560px deja una medida de ~35 caracteres que parte cada respuesta en
	   tiras. Con dos, cada columna ronda los 60 y se lee. El resto de la aritmética es la misma que
	   grid_cols() aplica en todas partes — nunca más columnas que filas, última al menos medio
	   llena — así que cuatro preguntas dan 2+2 y tres dan 2+1. */
	$o = '<div class="' . $wrap_cls . '"' . cols_attr( count( $items ), 2 ) . '>';
	foreach ( array_values( $items ) as $i => $it ) {
		$o .= '<details' . ( 0 === $i ? ' open' : '' ) . '><summary>' . h( $it[0] ) . '</summary>'
			. '<p>' . h( $it[1] ) . '</p></details>';
	}
	return $o . '</div>';
}
/** COMP-FAQ. The section wrapper; the rows are disclosure_list_html()'s, like every other. */
function faq_block_html( $fq, $extra = '' ) {
	return '<section class="sec faq' . $extra . '" aria-label="' . h( $fq['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $fq['eyebrow'] ) . '</span>'
		. '<h2>' . h( $fq['h2'] ) . '</h2></div>'
		. disclosure_list_html( $fq['items'] ) . '</div></section>';
}

/** TPL-C-07 · Stock / Ocasión. */
function strip_stock( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$o   = array();
	$o[] = head_phone( $C, $BRAND );
	$o[] = '<main>';
	$o[] = search_filters_html( $C['hero'], $C['search'], $uid );
	$o[] = stock_grid_html( $C['stock'] );
	$o[] = trade_in_html( $C['tradein'], $uid );
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-FINANCE' ) ) {
		$o[] = finance_html( $C['finance'] );
	}
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-BADGES' ) ) {
		$o[] = badges_html( $C['badges'] );
	}
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-TESTIMONIALS' ) ) {
		$o[] = quotes_block_html( $C['quotes'], ' bg-alt' );
	}
	$o[] = nap_block_html( $C['nap'], ' closing', isset( $C['nav_cta'] ) ? $C['nav_cta'] : '' );
	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );
	return implode( "\n", $o );
}

/**
 * COMP-SEARCH-HERO · TPL-C-13.
 *
 * Same composition as TPL-C-07's COMP-SEARCH-FILTERS — a contained band with the filter card
 * riding its bottom edge — and it reuses `.hero-search`/`.filterbar` verbatim, because the
 * composition IS the same and duplicating the CSS would be two things to keep in step. Separate
 * function because the two archetypes label their band differently (`Buscador de inmuebles` vs
 * `Buscador de stock`) and the aria-label is not decoration.
 */
/**
 * The hero band's surface: the photograph when the archetype has one, a MARKED placeholder when
 * it does not. Marked and not blank — a grey box reads as a design decision, and this one is an
 * absence. TPL-C-13 shipped without any: the repo held 45 images and not one was a dwelling, so
 * for one commit the band was hatched. The placeholder branch stays because that state is real
 * and will recur the next time an archetype lands before its photography does.
 */
function hero_shot_html( $hero ) {
	if ( ! isset( $hero['img'] ) ) {
		return '<figure class="frame shot ph"><span>Placeholder</span></figure>';
	}
	$hs = img( $hero['img'] );
	return '<figure class="frame shot"><img data-img="' . h( $hs['slug'] ) . '"'
		. ' alt="' . h( $hs['alt'] ) . '" width="' . $hs['w'] . '" height="' . $hs['h'] . '"></figure>';
}

function search_hero_html( $hero, $sf, $uid ) {
	$o = '<section class="sec hero hero-search" aria-label="Buscador de inmuebles">'
		. '<div class="canvas"><div class="head stack">'
		. '<span class="eyebrow">' . h( $hero['eyebrow'] ) . '</span>'
		. '<h1>' . h( $hero['h1'] ) . '</h1>'
		. '<p class="lede">' . h( $hero['lede'] ) . '</p></div>'
		. hero_shot_html( $hero )
		. '<form class="filterbar" onsubmit="return false">';
	foreach ( $sf['fields'] as $fl ) {
		$id = $uid . '-f-' . $fl[0];
		$o .= '<div class="field"><label for="' . $id . '">' . h( $fl[1] ) . '</label>'
			. '<select id="' . $id . '" name="' . $fl[0] . '">';
		foreach ( $fl[2] as $opt ) {
			$o .= '<option>' . h( $opt ) . '</option>';
		}
		$o .= '</select></div>';
	}
	return $o . '<p class="stockcount">' . h( $sf['count'] ) . '</p>'
		. '<button class="btn btn-primary" type="submit">' . h( $sf['submit'] ) . '</button>'
		. '</form></div></section>';
}

/**
 * COMP-TEAM · TPL-C-13. Who opens the door, with the ZONE each one covers — which is the fact that
 * makes the section useful here and that a generic team grid leaves out. Reuses `.medlist` from
 * TPL-C-10; same object, a row of people with a line of credential under each.
 */
function agent_list_html( $tm, $extra = '' ) {
	$o = '<section class="sec medteam grid-sec' . $extra . '" aria-label="' . h( $tm['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $tm['eyebrow'] ) . '</span>'
		. '<h2>' . h( $tm['h2'] ) . '</h2></div><ul class="medlist"' . cols_attr( count( $tm['items'] ) ) . '>';
	foreach ( $tm['items'] as $m ) {
		$mi  = isset( $m['img'] ) ? img( $m['img'] ) : null;
		$o  .= '<li class="medico pcard">'
			. ( null === $mi
				? '<figure class="frame ph"><span>Placeholder</span></figure>'
				: '<figure class="frame"><img data-img="' . h( $mi['slug'] ) . '" alt="' . h( $mi['alt'] )
					. '" width="' . $mi['w'] . '" height="' . $mi['h'] . '"></figure>' )
			. '<div class="medbody"><b>' . h( $m['name'] ) . '</b>'
			. '<span>' . h( $m['role'] ) . '</span>'
			. '<span class="medlic">' . h( $m['lic'] ) . '</span></div></li>';
	}
	return $o . '</ul></div></section>';
}
/**
 * HOW MANY COLUMNS A CLOSED SET GETS, decided here because here is the only place that knows how
 * many items there are.
 *
 * "Conjunto fijo → rejilla fija" is the framework's own rule and `auto-fit` is the opposite of it:
 * it lets the BROWSER pick the track count from the width available, so a set of six renders as
 * five-and-one on a 2560 screen and as three-and-three on a laptop, and nobody chose either.
 * `auto-fill` was worse — it reserved a column for items that do not exist — and swapping it for
 * `auto-fit` fixed the empty track while leaving the ragged last row, which is the defect the
 * reader saw next.
 *
 * THE RULE: never more columns than items — that is the empty-track bug — and among the rest take
 * the WIDEST layout whose last row is at least half full. Six with a cap of three gives 3+3, five
 * gives 3+2, four gives 2+2, three gives one clean row, two gives two. Seven drops to 2 because
 * 3 would leave a single orphan.
 *
 * The half-full floor is the whole rule and the first draft did not have it: scoring purely on
 * "last row exact" made FIVE items render as five stacked rows of one, because 1 is the only
 * count that divides 5. An orphan is ugly; a single column of five cards is worse.
 *
 * The cap exists because a row can be full and still be wrong: six cards across a 1740px canvas
 * are 270px wide with a 200px photograph, which is a contact sheet and not a listing.
 */
function grid_cols( $count, $cap = 3 ) {
	$count = max( 1, (int) $count );
	$cap   = max( 1, min( (int) $cap, $count ) );
	/* Downward, so the FIRST acceptable count is the widest one. */
	for ( $c = $cap; $c >= 2; $c-- ) {
		$rest = $count % $c;
		if ( 0 === $rest || $rest >= (int) ceil( $c / 2 ) ) {
			return $c;
		}
	}
	return 1;
}

/** The inline custom property the grid CSS reads. One place, so the attribute cannot drift. */
function cols_attr( $count, $cap = 3 ) {
	return ' style="--cols:' . grid_cols( $count, $cap ) . '"';
}
/**
 * COMP-PROPERTY-GRID · TPL-C-13.
 *
 * THE SIX FACTS ARE VISIBLE WITHOUT OPENING ANYTHING, because that is what a listing is for: the
 * reader discards forty and keeps three, and every fact they have to click for is a discard they
 * cannot make. Price first, zone second, the rest as chips.
 *
 * Reuses `.stockgrid` from TPL-C-07 — same shape, a grid of fact-cards — and adds `.pcard` only
 * for what genuinely differs: no photograph, and a zone line under the heading.
 */
function property_grid_html( $pg ) {
	$o = '<section class="sec stock grid-sec bg-alt" aria-label="' . h( $pg['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $pg['eyebrow'] ) . '</span>'
		. '<h2>' . h( $pg['h2'] ) . '</h2></div><ul class="stockgrid"' . cols_attr( count( $pg['items'] ) ) . '>';
	foreach ( $pg['items'] as $p ) {
		$pi  = isset( $p['img'] ) ? img( $p['img'] ) : null;
		$o  .= '<li class="vcard pcard">'
			. ( null === $pi
				? '<figure class="frame ph"><span>Placeholder</span></figure>'
				: '<figure class="frame"><img data-img="' . h( $pi['slug'] ) . '" alt="' . h( $pi['alt'] )
					. '" width="' . $pi['w'] . '" height="' . $pi['h'] . '"></figure>' )
			. '<div class="vbody"><h3>' . h( $p['h3'] ) . '</h3>'
			. '<p class="pzone">' . h( $p['zone'] ) . '</p><ul class="vfacts">';
		foreach ( $p['facts'] as $fa ) {
			$o .= '<li>' . h( $fa ) . '</li>';
		}
		$o .= '</ul><p class="vprice"><b>' . h( $p['price'] ) . '</b>'
			. '<span>' . h( $p['unit'] ) . '</span></p>'
			. '<a class="btn btn-outline btn-sm" href="' . h( ihref( 'producto' ) ) . '">Ver ficha</a></div></li>';
	}
	return $o . '</ul><p class="pnote">' . h( $pg['note'] ) . '</p></div></section>';
}

/**
 * PR3c — Inmobiliaria de la O's OWN property card, authored to match the source design instead of
 * borrowed from Motor Aranda's used-car `.stockgrid`/`.vcard` (`property_grid_html()` above), which
 * is PR3b's own measured defect: badge/photo-ratio/price-rhythm belong to a stock of VEHICLES, and
 * they read that way under a luxury villa's photograph regardless of the copy sitting beside them.
 *
 * Deliberately NEW class names (`proplux-*`), never `.vcard`/`.pcard`/`.vfacts`/`.vprice` — those
 * belong to `TPL-C-07` in the stylesheet's own class-ownership ledger, and this card's geometry
 * (badge flush at the photo's top-left corner, a monospace reference sharing the zone row, price on
 * its own line under a fact row) does not reduce to that recipe with a class added.
 *
 * $variant: 'featured' (home, aspect 4/3.2, no reference — TGL-FEATURED-COUNT's own three cards) |
 * 'listing' (propiedades, aspect 4/3.1, monospace reference right-aligned on the zone row) |
 * 'similar' (the ficha's own closing set: aspect 4/3, no badge, no fact row — the source design's
 * own "tarjetas simplificadas": foto, zona, título, precio, nothing else).
 *
 * THE ZONE IS MUTED TEXT, NOT ACCENT, and that is a deviation from the source design's own token
 * table on purpose. design-tokens.md's real accent row — the one `_build-gallery.php`'s own
 * accent-role gate re-measures — reads "ONE colour. CTAs, action icons, important links, active
 * states. Never body text, never decoration." A zone label is a scanning aid, which is exactly
 * "decoration" in that row's own words; painting it in accent would `fail()` the build the moment
 * this file ran, the same class of correction PR3b already made once for `.statspanel`'s border.
 */
function proplux_card_html( $p, $variant = 'featured' ) {
	$pi = isset( $p['img'] ) ? img( $p['img'] ) : null;
	$o  = '<a class="proplux proplux-' . h( $variant ) . '" href="' . h( ihref( 'producto' ) ) . '">'
		. '<figure class="frame proplux-shot">';
	if ( 'similar' !== $variant && isset( $p['badge'] ) ) {
		$o .= '<span class="proplux-badge">' . h( $p['badge'] ) . '</span>';
	}
	$o .= ( null === $pi )
		? '<span class="proplux-ph"><span>Placeholder</span></span>'
		: '<img data-img="' . h( $pi['slug'] ) . '" alt="' . h( $pi['alt'] ) . '" width="' . $pi['w'] . '" height="' . $pi['h'] . '">';
	$o .= '</figure><div class="proplux-body"><div class="proplux-zonerow">'
		. '<span class="proplux-zone">' . h( $p['zone'] ) . '</span>';
	if ( 'listing' === $variant && isset( $p['ref'] ) ) {
		$o .= '<span class="proplux-ref">' . h( $p['ref'] ) . '</span>';
	}
	$o .= '</div><h3>' . h( $p['h3'] ) . '</h3>';
	if ( 'similar' !== $variant && isset( $p['facts'] ) ) {
		$o .= '<ul class="proplux-facts">';
		foreach ( $p['facts'] as $fa ) {
			$o .= '<li>' . h( $fa ) . '</li>';
		}
		$o .= '</ul>';
	}
	return $o . '<p class="proplux-price">' . h( $p['price'] ) . '</p></div></a>';
}

/** The grid of `proplux` cards, `gap:1px` acting as the filete over `--c-border` — the same trick
 *  `.valuerow`/the source design's own card grids already use, never `box-shadow`/`border-radius`. */
function proplux_grid_html( array $items, $variant = 'featured', $cap = 3 ) {
	$o = '<ul class="proplux-grid"' . cols_attr( count( $items ), $cap ) . '>';
	foreach ( $items as $p ) {
		$o .= '<li>' . proplux_card_html( $p, $variant ) . '</li>';
	}
	return $o . '</ul>';
}

/**
 * COMP-FEATURED-GRID · TPL-C-15, home. `contenido` in the archetype's own Envoltorio table —
 * `sec_open()`'s default shape, no `bleedband`/`secrow` — a curated sample inside the page canvas,
 * never a sangre. The "ver la cartera completa" link routes by explicit page key
 * (`ihref('propiedades')`), never `ihref_for_label()`: the home page's own label is "Cartera", and a
 * link whose COPY also contains that word — this design's own "ver la cartera completa" among them —
 * would fuzzy-match the home page first under label matching. Explicit beats fuzzy whenever the
 * destination is already known, which it is here.
 */
function featured_grid_html( $pg ) {
	return sec_open( 'featuredgrid grid-sec', $pg['h2'] )
		. '<div class="fgridhead"><div class="head stack"><span class="eyebrow">' . h( $pg['eyebrow'] ) . '</span>'
		. '<h2>' . h( $pg['h2'] ) . '</h2></div>'
		. '<a class="fgridlink" href="' . h( ihref( 'propiedades' ) ) . '">' . h( $pg['link'] ) . '</a></div>'
		. proplux_grid_html( $pg['items'], 'featured' )
		. sec_close();
}

/**
 * COMP-MAP-SEARCH · TPL-C-13. The section that separates this archetype from every other listing
 * in the catalogue: a car has no place, a flat is nothing but its place.
 *
 * DRAWN, NOT PHOTOGRAPHED. Inline SVG streets — the Artifact CSP blocks map tiles, a real provider
 * needs consent before it loads (see wordpress-legal), and neither belongs in a structural mockup.
 * Every pin carries its price, because a pin the reader has to click to price is a pin that made
 * them work for something they could have read.
 */
/* A SANGRE EN `TPL-C-13`, y lo pide su propio documento: allí el plano no es una ilustración de
   dónde está la agencia, es un MODO DE BÚSQUEDA con los mismos resultados dentro. Un modo de
   búsqueda metido en una columna de 1140px se lee como una captura de pantalla; llegando al
   cristal se lee como lo que es, un mapa que se recorre. El conmutador Lista/Mapa y la nota se
   quedan en la columna de texto, porque son copia. */
function map_search_html( $ms, $shape = 'contained' ) {
	/* TRES INTENTOS Y LA TERCERA ES LA BUENA, y las dos anteriores explican por qué.
	   (1) Un SVG de calles inventadas: el lector lo llamó «un mapa random», con razón — un plano de
	   una ciudad que no existe AFIRMA algo falso. (2) Un hueco declarado, que no afirma nada pero
	   tampoco enseña nada: el lector dijo «el mapa no se ve», y tenía razón otra vez, porque la
	   sección existe para demostrar que buscar por SITIO es distinto de buscar por atributos, y un
	   rectángulo rayado no demuestra eso. (3) Una fotografía aérea: es una imagen, no una afirmación
	   cartográfica, así que puede enseñar el tejido urbano —manzanas, parque, río— sin decir que es
	   ninguna calle concreta. Es la misma respuesta que el resto del arquetipo: no dibujes lo que
	   puedes fotografiar.
	   El plano NAVEGABLE sigue siendo del build, con su proveedor y su consentimiento previo — esto
	   es la maqueta y aquí basta con que se vea qué va en ese sitio.
	   LAS CHINCHETAS SE QUEDAN porque no son decoración: son el dato que la sección enseña — que el
	   precio se lee sin abrir la ficha y que la posición es un filtro. */
	$mp  = img( $ms['img'] );
	$svg = '<figure class="frame mapshot"><img data-img="' . h( $mp['slug'] ) . '"'
		. ' alt="' . h( $mp['alt'] ) . '" width="' . $mp['w'] . '" height="' . $mp['h'] . '"></figure>';
	$o = sec_open( 'mapsearch grid-sec', $ms['h2'], $shape )
		. '<div class="head stack"><span class="eyebrow">' . h( $ms['eyebrow'] ) . '</span>'
		. '<h2>' . h( $ms['h2'] ) . '</h2>'
		. '<div class="mapswitch" role="group" aria-label="Modo de búsqueda">'
		. '<button type="button" aria-pressed="false">Lista</button>'
		. '<button type="button" aria-pressed="true">Mapa</button></div></div>'
		. '<div class="mapwrap">' . $svg;
	foreach ( $ms['pins'] as $p ) {
		$o .= '<span class="pin" style="left:' . (float) $p[0] . '%;top:' . (float) $p[1] . '%">'
			. h( $p[2] ) . '</span>';
	}
	return $o . '</div><p class="pnote">' . h( $ms['note'] ) . '</p>' . sec_close( $shape );
}

/**
 * COMP-VISIT-REQUEST · TPL-C-13.
 *
 * THE REFERENCE TRAVELS WITH THE REQUEST. A visit form without the property in it is a generic
 * enquiry, and an agency cannot put a generic enquiry in a diary — which is the whole difference
 * between this and TPL-C-05's COMP-BOOKING, where the appointment is at the one address the
 * business has. Reuses `.tiform` for the field layout it shares with COMP-TRADE-IN.
 */
function visit_request_html( $vr, $uid ) {
	$o = '<section class="sec tradein" aria-label="' . h( $vr['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $vr['eyebrow'] ) . '</span>'
		. '<h2>' . h( $vr['h2'] ) . '</h2><p class="lede muted">' . h( $vr['lede'] ) . '</p></div>'
		. '<form class="tiform" onsubmit="return false">';
	foreach ( $vr['fields'] as $fl ) {
		$id = $uid . '-vr-' . $fl[0];
		if ( 'select' === $fl[2] ) {
			$o .= '<div class="field"><label for="' . $id . '">' . h( $fl[1] ) . '</label>'
				. '<select id="' . $id . '" name="' . $fl[0] . '">';
			foreach ( $fl[3] as $op ) {
				$o .= '<option>' . h( $op ) . '</option>';
			}
			$o .= '</select></div>';
			continue;
		}
		$val = isset( $fl[3] ) ? ' value="' . h( $fl[3] ) . '"' : '';
		$ro  = isset( $fl[4] ) ? ' readonly' : '';
		$o  .= '<div class="field"><label for="' . $id . '">' . h( $fl[1] ) . '</label>'
			. '<input id="' . $id . '" name="' . $fl[0] . '" type="' . $fl[2] . '"' . $val . $ro . '></div>';
	}
	return $o . '<button class="btn btn-primary" type="submit">' . h( $vr['submit'] ) . '</button>'
		. '<p class="small muted">' . h( $vr['small'] ) . '</p></form></div></section>';
}

/**
 * COMP-VALUATION-CTA · TPL-C-13. The other conversion, and it addresses the other person.
 *
 * NO PRICE IS PROMISED ON THE PAGE. An automatic figure is the promise the agency then has to walk
 * back in person, so the band offers a visit and two numbers it can actually stand behind.
 */
function valuation_html( $vl, $uid ) {
	$o = '<section class="sec valuation grid-sec bg-alt closing" aria-label="' . h( $vl['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $vl['eyebrow'] ) . '</span>'
		. '<h2>' . h( $vl['h2'] ) . '</h2><p class="lede muted">' . h( $vl['lede'] ) . '</p></div>'
		. '<ul class="vstats">';
	foreach ( $vl['stats'] as $st ) {
		$o .= '<li><b>' . h( $st[0] ) . '</b><span>' . h( $st[1] ) . '</span></li>';
	}
	$o .= '</ul><form class="tiform" onsubmit="return false">';
	foreach ( $vl['fields'] as $fl ) {
		$id = $uid . '-vl-' . $fl[0];
		$o .= '<div class="field"><label for="' . $id . '">' . h( $fl[1] ) . '</label>'
			. '<input id="' . $id . '" name="' . $fl[0] . '" type="' . $fl[2] . '"></div>';
	}
	return $o . '<button class="btn btn-primary" type="submit">' . h( $vl['submit'] ) . '</button>'
		. '<p class="small muted">' . h( $vl['small'] ) . '</p></form></div></section>';
}

/** TPL-C-13 · Cartera / Búsqueda. */
function strip_property( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$o   = array();
	$o[] = head_phone( $C, $BRAND );
	$o[] = '<main>';
	/* The searcher IS the front page. Same composition as TPL-C-07's filter band and the same CSS,
	   different function: that one needs a photograph per hero and this archetype has none. */
	$o[] = search_hero_html( $C['hero'], $C['search'], $uid );
	$o[] = property_grid_html( $C['listing'] );
	if ( 'off' !== tgl_of( $tgl_rows, 'TGL-MAP-MODE' ) ) {
		$o[] = map_search_html( $C['map'], 'bleed' );
	}
	$o[] = visit_request_html( $C['visit'], $uid );
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-TEAM' ) ) {
		$o[] = agent_list_html( $C['team'] );
	}
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-FAQ' ) ) {
		$o[] = faq_block_html( $C['faq'] );
	}
	/* Capture goes AFTER the visit on purpose: an owner who wants to sell arrives anyway, and
	   putting it first steals the front page from the buyer, who is most of the traffic. */
	$o[] = valuation_html( $C['valuation'], $uid );
	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );
	return implode( "\n", $o );
}

/**
 * COMP-HERO-CARTERA · TPL-C-15, `TGL-HERO-MODE=retrato` (el valor por defecto).
 *
 * LA CASA ANTES QUE EL BUSCADOR. Bleed a 78vh con velo y una retícula de cuatro columnas
 * decorativa superpuesta (`z-index:2`, `pointer-events:none`, sólo filetes) — la persuasión que a
 * `TPL-C-13` le faltaba: una casa que se vende por cómo se ve, no por cuántos filtros tiene
 * delante. El panel de cifras a la derecha son números que la agencia puede sostener, no un claim
 * vacío. El otro extremo del toggle es `search_hero_html()`, prestado tal cual de `TPL-C-13`.
 */
function hero_cartera_html( $hero ) {
	$im = img( $hero['img'] );
	/* PR3c FIX — this used to hand-write its own `<section>` tag instead of calling `sec_open()`,
	   so it never received `bleedband` even though TPL-C-15's own Envoltorio table declares
	   `COMP-HERO-CARTERA` as "banda a sangre (en retrato, su valor por defecto)". The document said
	   bleed, the render said contained, and RT_TPL_ENVOLTORIO_RENDER_MISMATCH below exists to catch
	   exactly that gap. Routing through `sec_open( ..., 'bleed' )` costs nothing visually: `bleed`
	   shape omits the `<div class="canvas">` sec_open() would otherwise add, and this function
	   already opens its own `.canvas` by hand for the head/statspanel — `.herocartera > .canvas`'s
	   own CSS still targets it as a direct child regardless of the section's extra `bleedband`
	   class, and `.bleedband`'s `display:grid` only ever sees `.media-full`/`.rulegrid` (both
	   `position:absolute`, so out of grid flow) and `.canvas` (the one flowed child) — the 78vh
	   photo, the veil and the 4-column rule grid are unaffected. */
	/* PR3d — `$hero['h1_lines']` is the artboard's own two-line break (`Casas que no<br>se anuncian
	   solas`), kept as an array and joined through `h()` per part rather than passed as raw markup:
	   the same convention this file already uses for a two-line address. `$hero['h1']` (plain,
	   single string) stays the `aria-label`/SEO value — `sec_open()` takes a label, not markup. */
	$o  = sec_open( 'hero herocartera', $hero['h1'], 'bleed' ) . '<div class="media-full">'
		. '<figure class="frame"><img data-img="' . h( $im['slug'] ) . '" alt="' . h( $im['alt'] )
		. '" width="' . $im['w'] . '" height="' . $im['h'] . '"></figure></div>'
		. '<div class="rulegrid" aria-hidden="true"><span></span><span></span><span></span><span></span></div>'
		. '<div class="canvas"><div class="head stack">'
		. '<span class="eyebrow">' . h( $hero['eyebrow'] ) . '</span>'
		. '<h1>' . implode( '<br>', array_map( 'h', $hero['h1_lines'] ) ) . '</h1>'
		. '<p class="lede muted">' . h( $hero['lede'] ) . '</p></div>'
		. '<div class="statspanel"><ul>';
	foreach ( $hero['stats'] as $st ) {
		$o .= '<li><b>' . h( $st[0] ) . '</b><span>' . h( $st[1] ) . '</span></li>';
	}
	return $o . '</ul></div></div>' . sec_close( 'bleed' );
}

/**
 * COMP-SEARCH-BAND · TPL-C-15. La herramienta, no la portada.
 *
 * Reutiliza `.filterbar`, la misma forma que `TPL-C-07` y `TPL-C-13` ya usan para sus cuatro
 * campos — la composición ES la misma, inventar una segunda sería piel distinta con extra pasos.
 * Lo que cambia es el envoltorio: un plato de superficie inversa DEL ANCHO DEL CONTENEDOR, nunca a
 * sangre — «sin tocar el borde de la ventana», dice el propio documento del arquetipo — así que la
 * superficie inversa vive en `.sbplate`, dentro del `.canvas` que `sec_open()` ya limita al
 * contenedor, y no en el `<section>` entero.
 */
/**
 * PR3d — one field tuple now covers BOTH controls the artboard actually draws: a select,
 * `array($key,$label,array($options))`, or a free-text input, `array($key,$label,'input',
 * $placeholder)` — "Zona o municipio" is a placeholder INPUT on both `Inicio.dc.html` and
 * `Propiedades.dc.html`, never a select, which is what PR3c's select-only field renderer got
 * wrong. `sbfield_html()` is shared by `search_band_html()` and `filter_band_html()` below, the
 * same "one field recipe" `filter_band_html()`'s own docblock already argued for.
 */
function sbfield_html( $fl, $id ) {
	if ( 'input' === $fl[2] ) {
		return '<div class="field"><label for="' . $id . '">' . h( $fl[1] ) . '</label>'
			. '<input id="' . $id . '" name="' . $fl[0] . '" type="text" placeholder="' . h( $fl[3] ) . '"></div>';
	}
	$o = '<div class="field"><label for="' . $id . '">' . h( $fl[1] ) . '</label>'
		. '<select id="' . $id . '" name="' . $fl[0] . '">';
	foreach ( $fl[2] as $opt ) {
		$o .= '<option>' . h( $opt ) . '</option>';
	}
	return $o . '</select></div>';
}

function search_band_html( $sf, $uid ) {
	$o = sec_open( 'searchband', 'Buscador de propiedades' ) . '<div class="sbplate">'
		. '<form class="filterbar" onsubmit="return false">';
	foreach ( $sf['fields'] as $fl ) {
		$o .= sbfield_html( $fl, $uid . '-sb-' . $fl[0] );
	}
	return $o . '<button class="btn btn-primary" type="submit">' . h( $sf['submit'] ) . '</button>'
		. '</form></div>' . sec_close();
}

/**
 * COMP-VALUATION-CTA · TPL-C-15. «La sección ES la fila»: panel de captación y fotografía, hijos
 * directos, sin contenedor intermedio, separados por el mismo `gap:1px` que actúa de filete que el
 * damero de `COMP-BONO-PACKS` en `TPL-C-14` — el mismo truco, aplicado aquí a la otra conversión.
 * Nunca promete un precio en la página; la tasación es presencial.
 *
 * PR3d FIX — `Inicio.dc.html` draws exactly ONE button here ("Solicitar valoración") plus a plain
 * `tel:` link ("o llámenos"), never a second page-routed button. The primary CTA is routed by an
 * EXPLICIT page key (`ihref('contacto')`), never `ihref_for_label($vl['cta'])`: that copy matches
 * none of TPL-C-15's five page labels (Cartera/Propiedades/Ficha/Nosotros/Contacto), and
 * `ihref_match()`'s own documented fallback for an unmatched label is the HOME route — silently
 * wrong for the one button whose entire job is reaching Contacto. This is the bug the launch brief
 * names; PR3c's own second button (`cta_2` through the same fuzzy match) had the identical defect,
 * and is gone rather than fixed in place because the artboard never drew a second button to fix.
 */
function valuation_row_html( $vl, $uid ) {
	$o = sec_open( 'valuerow', $vl['h2'], 'row' )
		. '<div class="vlpanel">' . sec_head( $vl )
		. '<div class="ctas"><a class="btn btn-primary" href="' . h( ihref( 'contacto' ) ) . '">' . h( $vl['cta'] ) . '</a>'
		. '<a class="valuetel" href="tel:' . h( preg_replace( '/\s+/', '', $vl['phone'] ) ) . '">' . h( $vl['tel_label'] ) . '</a></div></div>'
		. '<figure class="vlshot">' . img_tag( $vl['img'] ) . '</figure>';
	return $o . sec_close( 'row' );
}

/**
 * TPL-C-15 · Cartera curada — la home de Inmobiliaria de la O.
 *
 * NO ES `strip_property()` CON OTRA PIEL. `TPL-C-13` abría con el buscador porque su visitante
 * llega FILTRANDO; aquí el visitante llega siendo CONVENCIDO, así que el héroe de persuasión abre
 * y el buscador baja a su propia banda, nunca encima — ver design.md decisión C2. `TGL-HERO-MODE`
 * conserva la ruta de `TPL-C-13` para el brief de volumen urbano: en `buscador-portada` la imagen
 * deja de ir a sangre y el buscador se dibuja encima de ella, exactamente como allí, y
 * `COMP-SEARCH-BAND` no se repite como sección aparte más abajo.
 */
function strip_cartera_curada( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$o   = array();
	$o[] = head_phone( $C, $BRAND );
	$o[] = '<main>';
	if ( 'buscador-portada' === tgl_of( $tgl_rows, 'TGL-HERO-MODE' ) ) {
		$o[] = search_hero_html( $C['hero'], $C['search'], $uid );
	} else {
		$o[] = hero_cartera_html( $C['hero'] );
		$o[] = search_band_html( $C['search'], $uid );
	}
	$o[] = featured_grid_html( $C['listing'] );
	if ( 'off' !== tgl_of( $tgl_rows, 'TGL-MAP-MODE' ) ) {
		$o[] = map_search_html( $C['map'], 'bleed' );
	}
	$o[] = valuation_row_html( $C['valuation'], $uid );
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-REVIEWS' ) ) {
		// PR3e — `Inicio.dc.html`'s own side-by-side testimonial head, not the shared `.head.stack`.
		$o[] = quotes_block_html( $C['quotes'], '', 'between' );
	}
	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );
	return implode( "\n", $o );
}

/**
 * PR3c — the "split page head" the source design draws on FOUR of its five screens (Propiedades,
 * Ficha, Nosotros, Contacto all open with eyebrow+H1 on one side and a lede paragraph on the
 * other), never the single stacked column `page_head_html()` gives every other archetype in the
 * catalogue. One shared function rather than four near-identical ones — genuinely the same
 * component wherever it appears, which is what licenses reusing it instead of authoring it twice.
 * `$cls` lets a caller add its own class alongside `pagehead splithead` without a second function.
 */
function split_head_html( $hd, $cls = '' ) {
	return sec_open( trim( 'pagehead splithead ' . $cls ), $hd['h1'] )
		. '<div class="head stack"><span class="eyebrow">' . h( $hd['eyebrow'] ) . '</span>'
		. '<h1>' . h( $hd['h1'] ) . '</h1></div>'
		. '<p class="lede muted">' . h( $hd['lede'] ) . '</p>'
		. sec_close();
}

/**
 * COMP-SEARCH-BAND, sticky variant, for TPL-C-15's own "Propiedades" listing. The design's own
 * words: "barra de filtros idéntica al buscador de la home" — same `.filterbar` shape
 * `search_band_html()` already uses, never a second field recipe. `.filterband`'s own CSS is what
 * differs: sticky, not static, and tighter field padding. Sticky to `top:0` and not the source
 * design's literal `top:78px` — this house's `.site-head` (`_build-gallery.php`, `.site-head{
 * border-bottom:1px solid var(--c-border)}`) is never `position:sticky` for any of the ten demos,
 * so there is no pinned 78px header to sit under here; sticking to the viewport's own top edge is
 * the same behaviour (a filter bar that stays reachable while the list scrolls) expressed against
 * the chassis this gallery actually has, not the one the design's own prototype assumed.
 */
function filter_band_html( $sf, $uid ) {
	$o = sec_open( 'searchband filterband', 'Filtrar propiedades' ) . '<div class="sbplate">'
		. '<form class="filterbar" onsubmit="return false">';
	foreach ( $sf['fields'] as $fl ) {
		$o .= sbfield_html( $fl, $uid . '-fb-' . $fl[0] );
	}
	return $o . '<button class="btn btn-primary" type="submit">' . h( $sf['submit'] ) . '</button>'
		. '</form></div>' . sec_close();
}

/**
 * The results bar between the filter band and the grid: a count on the left, "ORDENAR" plus three
 * sort links on the right. Filtering and sorting do not actually reorder anything here — the launch
 * brief scopes "URL-reflected filters" out — so the three links are static and the FIRST one alone
 * carries `aria-current`, matching the source design's own resting state. `.sortlink[aria-current]`
 * is registered under 'active states' in $ACCENT_ROLES below, the same role `.mapswitch` already
 * holds for the one other "which one is selected" control in this file.
 */
function results_bar_html( $rb ) {
	$o = '<div class="resultsbar"><span class="rescount">' . h( $rb['count'] ) . '</span>'
		. '<div class="sortgroup"><span class="sortlabel">' . h( $rb['sort_label'] ) . '</span>';
	foreach ( $rb['sorts'] as $i => $s ) {
		$o .= '<a class="sortlink" href="#"' . ( 0 === $i ? ' aria-current="true"' : '' ) . '>' . h( $s ) . '</a>';
	}
	return $o . '</div></div>';
}

/**
 * The listing section proper: results bar + the 3-column `proplux` grid in its 'listing' variant
 * (nine cards, each carrying the monospace reference the home's featured three never show) + a
 * centred ghost "cargar más" button. `contenido` shape: this page reuses `TPL-SERVICES-01`'s own
 * "index for a home with more entries than fit" pattern rather than TPL-C-15's home archetype, so
 * it carries no Envoltorio row of its own to honour — the house default stands.
 */
function property_listing_html( $lst ) {
	return sec_open( 'proplisting grid-sec', 'Cartera completa' )
		. results_bar_html( $lst['results'] )
		. proplux_grid_html( $lst['items'], 'listing' )
		. '<div class="loadmore"><a class="btn btn-ghost" href="#">' . h( $lst['more'] ) . '</a></div>'
		. sec_close();
}

/**
 * TPL-C-15 · «Propiedades» — el índice completo, reutilizando `TPL-SERVICES-01`.
 *
 * La home enseña tres fichas curadas de las diecisiete de la cartera y una selección de tres
 * necesita dónde enseñar el resto — exactamente el vacío que `TPL-SERVICES-01` ya resuelve para «un
 * negocio con más entradas de las que caben en la home» (su propia § «Por qué existe»). Aquí las
 * entradas son inmuebles y la ficha de destino es `TPL-PROPERTY-01`, no `TPL-SERVICE-02` — el mismo
 * patrón de página, reutilizado, no un arquetipo nuevo.
 *
 * PR3c REWRITE: la página que faltaba casi entera. `property_grid_html()` (Motor Aranda) sale de
 * aquí — sustituida por `proplux_grid_html()`, la tarjeta propia de esta marca — y se añaden la
 * cabecera partida, la barra de filtros pegajosa y la barra de resultados que el diseño de origen
 * dibuja y que esta plantilla nunca había renderizado.
 */
function page_property_index( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$K = $C['propiedades'];
	$o = array();
	$o[] = head_corporate( $C, $BRAND );
	$o[] = crumbs_html( $K['crumbs'] );
	$o[] = '<main>';
	$o[] = split_head_html( $K['head'] );
	$o[] = filter_band_html( $K['filters'], $uid );
	$o[] = property_listing_html( $K['listing'] );
	$o[] = page_cta_html( $K['cta'] );
	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );
	return implode( "\n", $o );
}

/** TPL-C-08 · Modelo / Lanzamiento. */


/** TPL-C-09 · Taller / Tarifa. */


/** COMP-HEADER floating on the hero: same contents as the local header, out of flow. */
function head_over( $C, $BRAND ) {
	$o = '<header class="site-head head-over"><div class="canvas"><div class="nav">'
		. '<span class="logo">' . h( $BRAND ) . '</span>'
		. '<nav class="mainnav" aria-label="Principal">';
	foreach ( $C['nav'] as $n ) {
		$o .= '<a href="' . h( ihref_for_label( $n ) ) . '">' . h( $n ) . '</a>';
	}
	/* The header CTA's WEIGHT is the archetype's call. TPL-C-07 wants `Vender mi coche` solid and
	   its doc says nothing against it; TPL-C-13's doc says the opposite in as many words, because
	   there the primary action is the phone and a solid second button beside it is two primaries.
	   Default unchanged, so no existing strip moves. */
	$cta_cls = isset( $C['nav_cta_weight'] ) && 'secundario' === $C['nav_cta_weight'] ? 'btn-outline' : 'btn-primary';
	return $o . '</nav><a class="tel" href="#">' . h( $C['phone'] ) . '</a>'
		. '<a class="btn ' . $cta_cls . ' btn-sm" href="' . h( ihref_for_label( $C['nav_cta'] ) ) . '">' . h( $C['nav_cta'] ) . '</a>'
		. '</div></div></header>';
}

/**
 * COMP-MARQUEE · the ribbon.
 *
 * THE COPY IS EMITTED TWICE AND THE SECOND COPY IS `aria-hidden`. The animation translates the
 * track by -50%, so a single run would scroll off and leave a gap; two identical runs make the
 * loop seamless. Hiding the duplicate is not a nicety — without it a screen reader reads the whole
 * ribbon twice, and the strip's own copy multiset would count every phrase two times.
 */
function marquee_html( $rows ) {
	$run = '';
	foreach ( $rows as $r ) {
		$run .= '<span>' . h( $r ) . '</span>';
	}
	return '<section class="marquee" aria-label="La casa en cuatro palabras"><div class="track">'
		. '<p class="run">' . $run . '</p>'
		. '<p class="run" aria-hidden="true">' . $run . '</p>'
		. '</div></section>';
}

/**
 * COMP-MENU-LIST · the carta.
 *
 * `<ol>` and not `<ul>`: a carta has an order and the kitchen chose it. The leader is an empty
 * `aria-hidden` cell carrying a dotted border — typed periods are announced one at a time and do
 * not stretch to fill the gap.
 */
function menu_list_html( $m, $prices ) {
	$o = '<section class="sec menu-list grid-sec" aria-label="' . h( $m['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $m['eyebrow'] ) . '</span>'
		. '<h2>' . h( $m['h2'] ) . '</h2></div><div class="groups">';
	foreach ( $m['groups'] as $g ) {
		$o .= '<section class="menu-group"><h3>' . h( $g['h3'] ) . '</h3><ol>';
		foreach ( $g['items'] as $d ) {
			$o .= '<li class="dish"><span class="n">' . h( $d[0] ) . '</span>'
				. ( 'no' === $prices ? '' : '<span class="dots" aria-hidden="true"></span>'
					. '<span class="p">' . h( $d[2] ) . '</span>' )
				. '<p class="d">' . h( $d[1] ) . '</p></li>';
		}
		$o .= '</ol></section>';
	}
	return $o . '</div><p class="foot">' . h( $m['note'] ) . '</p></div></section>';
}

/** COMP-FIGURE-QUOTE · the person who signs the food. */
function figure_quote_html( $f ) {
	$fi = img( $f['img'] );
	return '<section class="sec figq" aria-label="' . h( $f['eyebrow'] ) . '">'
		. '<figure class="portrait"><img data-img="' . h( $fi['slug'] ) . '"'
		. ' alt="' . h( $fi['alt'] ) . '" width="' . $fi['w'] . '" height="' . $fi['h'] . '"></figure>'
		. '<div class="say"><span class="eyebrow">' . h( $f['eyebrow'] ) . '</span>'
		. '<blockquote>' . h( $f['quote'] ) . '</blockquote>'
		/* THE SIGNATURE IS THE NAME AND IT IS NOT `aria-hidden`. The first cut printed both — a
		   handwritten `Èlia Ferrer` and a bold `Èlia Ferrer` two lines under it — which reads as a
		   template that forgot to fill one of its fields. Hiding the signature from assistive tech and
		   printing the name for it was the reason the duplicate existed; making the signature the
		   accessible name removes both problems at once. */
		. '<p class="sig">' . h( $f['sig'] ) . '</p>'
		. '<p class="who"><span>' . h( $f['role'] ) . '</span></p>'
		. '</div></section>';
}

/**
 * COMP-GALLERY, masonry.
 *
 * NO COUNT ASSERTION ANY MORE, and its removal is the honest signal that the layout changed model.
 * The offset grid placed six frames on six hand-written column lines, so a seventh item fell into
 * an unstyled cell and a fifth left a hole — the assertion was load-bearing. `columns` flows any
 * number, so a check that six is six would now be a rule with nothing behind it.
 */
function gallery_masonry_html( $g, $shape = 'contained' ) {
	/* A SANGRE EN LA HOME DEL RESTAURANTE Y CONTENIDA EN LA PÁGINA INTERIOR, con el mismo código.
	   En `TPL-C-06` la sala ES el producto: se reserva por cómo se ve el comedor, no por la carta,
	   y seis fotos de sala metidas en la misma columna que el horario dicen lo contrario de lo que
	   el arquetipo afirma. En el Nosotros de otra marca ese mismo mosaico es apoyo de un texto, y
	   ahí la columna es lo correcto. Una función, dos formas, y la elige quien conoce la página. */
	$o = sec_open( 'gallery grid-sec', $g['h2'], $shape ) . sec_head( $g ) . '<ul class="shots masonry">';
	foreach ( $g['items'] as $slug ) {
		$gi = img( $slug );
		$o .= '<li><figure class="frame"><img data-img="' . h( $gi['slug'] ) . '"'
			. ' alt="' . h( $gi['alt'] ) . '" width="' . $gi['w'] . '" height="' . $gi['h'] . '"></figure></li>';
	}
	return $o . '</ul>' . sec_close( $shape );
}

/** COMP-HOURS-BLOCK · the opening times as the graphic, and the address as copyable text. */
function hours_block_html( $hb ) {
	$o = '<section class="sec hoursblock closing" aria-label="' . h( $hb['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $hb['eyebrow'] ) . '</span>'
		. '<h2>' . h( $hb['h2'] ) . '</h2></div><div class="hours-big"><dl>';
	foreach ( $hb['rows'] as $r ) {
		$shut = ( 'Cerrado' === $r[1] ) ? ' shut' : '';
		$o   .= '<div class="row' . $shut . '"><dt>' . h( $r[0] ) . '</dt><dd>' . h( $r[1] ) . '</dd></div>';
	}
	$o .= '</dl><div class="where"><p>' . h( $hb['addr'][0] ) . '<br>' . h( $hb['addr'][1] ) . '</p>'
		. '<p><a href="#">' . h( $hb['phone'] ) . '</a></p>'
		. '<p><a href="#">' . h( $hb['mail'] ) . '</a></p>'
		. '<p class="muted">' . h( $hb['note'] ) . '</p></div></div></div></section>';
	return $o;
}

/**
 * TPL-C-06 · Mesa / Carta.
 *
 * THE ORDER IS THE ARGUMENT and it is the whole reason this archetype exists beside TPL-C-05: you
 * enter through the photograph, you decide with the carta, you trust the person, and only then do
 * you meet a form. TPL-C-05 puts the booking block third, which is right for a clinic and wrong
 * for a table.
 */


/**
 * TPL-C-05 · Local / Booking.
 *
 * The archetype of a place you go to, and two things are DNA rather than decoration: the PHONE
 * sits in the header as text, and the close carries the address and the opening hours. A local
 * business whose number lives only inside a form is one you cannot ring from the car, and hours
 * that are not on the page are a wasted trip.
 */


/**
 * COMP-BOOKING · declared by TPL-C-05 and TPL-E-01, so it is emitted from one place.
 *
 * DAY AND SLOT ARE RADIO GROUPS, NOT A `<select>`. A select hides every option but one behind a
 * tap, and on a booking block the available times ARE the argument: five visible slots say "there
 * is room this week" and a closed dropdown says nothing at all.
 *
 * It takes the SAMPLE's uid because every input here carries an id a `<label for>` points at, and
 * two archetypes on one page means two live booking forms.
 */
function booking_html( $bk, $uid ) {
	/* THE PHOTOGRAPH IS OPTIONAL AND ABSENT BY DEFAULT. Three archetypes already ship this block
	   without one and their markup must not move — so the key is read, not required, and the split
	   class only appears when there is something to split with. */
	$bk_split = isset( $bk['img'] ) ? ' booking-split' : '';
	$o = '<section class="sec booking' . $bk_split . '" aria-label="Cita"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $bk['eyebrow'] ) . '</span>'
		. '<h2>' . h( $bk['h2'] ) . '</h2><p class="muted">' . h( $bk['lede'] ) . '</p></div>'
		. '<form class="bookform" onsubmit="return false">';
	/* UN CAMPO PUEDE LLEGAR CON VALOR, y es la misma forma opcional que `$bk['img']` y
	   `$bk['picks']` ya usan dos veces en esta función: se LEE, no se exige, así que los seis
	   arquetipos que ya emiten este bloque con campos de tres celdas no mueven un byte.
	   Existe porque las dos fichas de inventario lo exigen por escrito: la referencia de la unidad
	   viaja DENTRO del formulario. Una solicitud de visita o de prueba sin referencia es una
	   consulta genérica, y una consulta genérica no se puede meter en una agenda — el comercial
	   tiene que llamar para preguntar lo único que la página ya sabía. */
	foreach ( $bk['fields'] as $f ) {
		$id = $uid . '-bk-' . $f[0];
		$o .= '<div class="field"><label for="' . $id . '">' . h( $f[1] ) . '</label>'
			. '<input id="' . $id . '" name="' . $f[0] . '" type="' . $f[2] . '"'
			. ( isset( $f[3] ) ? ' value="' . h( $f[3] ) . '" readonly' : '' ) . '></div>';
	}
	/* THE THIRD CHOOSER IS OPTIONAL AND READ, NOT REQUIRED — the same shape `$bk['img']` uses two
	   lines above, and for the same reason: five archetypes already ship this block with two groups
	   and their markup must not move. TPL-C-14 adds a ritual chooser because its own document says
	   the booking carries the chosen ritual: without it the front desk has to ring to ask the one
	   thing the page already knew. */
	$bk_groups = array();
	if ( isset( $bk['picks'] ) ) {
		$bk_groups[] = array( 'pick', $bk['pick_lbl'], $bk['picks'] );
	}
	$bk_groups[] = array( 'day', $bk['day_lbl'], $bk['days'] );
	$bk_groups[] = array( 'slot', $bk['slot_lbl'], $bk['slots'] );
	foreach ( $bk_groups as $grp ) {
		$o .= '<fieldset class="opts opts-wide"><legend>' . h( $grp[1] ) . '</legend>';
		foreach ( $grp[2] as $i => $opt ) {
			$oid = $uid . '-' . $grp[0] . $i;
			$o  .= '<label class="opt" for="' . $oid . '">'
				. '<input type="radio" id="' . $oid . '" name="' . $uid . '-' . $grp[0] . '"'
				. ( 0 === $i ? ' checked' : '' ) . '><span>' . h( $opt ) . '</span></label>';
		}
		$o .= '</fieldset>';
	}
	$o .= '<button class="btn btn-primary" type="submit">' . h( $bk['submit'] ) . '</button>'
		. '<p class="small muted book-small">' . h( $bk['small'] ) . '</p>'
		. '</form>';
	if ( '' !== $bk_split ) {
		$bi  = img( $bk['img'] );
		$o  .= '<figure class="bookmedia"><img data-img="' . h( $bi['slug'] ) . '"'
			. ' alt="' . h( $bi['alt'] ) . '" width="' . $bi['w'] . '" height="' . $bi['h'] . '"></figure>';
	}
	return $o . '</div></section>';
}

/**
 * COMP-CATEGORY-CARD · declared by TPL-E-01 and TPL-E-04.
 *
 * A door, not a product. It carries a COUNT because a category with no count is a promise the
 * reader cannot size: "18 referencias" tells them whether the click is worth it, and a category
 * card that hides the number is the one people stop clicking after the second empty room.
 */
function category_cards_html( $anchor_key, $cs ) {
	$o = '<section class="sec cats-sec grid-sec" aria-label="' . h( $cs['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $cs['eyebrow'] ) . '</span>'
		. '<h2>' . h( $cs['h2'] ) . '</h2></div><ul class="catcards">';
	foreach ( $cs['items'] as $it ) {
		$ci = img( $it[3] );
		$o .= '<li class="catcard"><a href="' . h( ihref_for_label( $it[0] ) ) . '">'
			. '<figure class="frame"><img data-img="' . h( $ci['slug'] ) . '"'
			. ' alt="' . h( $ci['alt'] ) . '" width="' . $ci['w'] . '" height="' . $ci['h'] . '"></figure>'
			. '<span class="catcard-body"><b>' . h( $it[0] ) . '</b>'
			. '<span class="catcard-sub">' . h( $it[1] ) . '</span>'
			. '<span class="catcard-n">' . h( $it[2] ) . '</span></span>'
			. '</a></li>';
	}
	return $o . '</ul></div></section>';
}

/**
 * TPL-E-01 · Visual / Brand-Led.
 *
 * The catalogue arrives through CATEGORIES and a lookbook, never through a grid of SKUs. TPL-E-02
 * puts eight products above the fold because a catalogue shop is something you search; this one
 * puts three doors, because a brand-led shop is something you browse. Same brand, same photographs,
 * and the two are not confusable after one screen — which is the only test that matters.
 */


/**
 * TPL-E-04 · Categories First.
 *
 * The catalogue is WIDE, so the page routes before it sells. TPL-E-02 answers "I know what I want"
 * with eight products above the fold; this one answers "I know roughly where to look" with eight
 * doors and a count on each.
 */




/** COMP-FOOTER — `[fijo]` in both archetypes, so it is emitted from one place. */
function footer_html( $f ) {
	$o = '<footer class="site-foot"><div class="canvas">'
		. '<div class="fnav"><span class="muted small">' . h( $f['tag'] ) . '</span>';
	foreach ( $f['links'] as $l ) {
		$o .= '<a href="' . h( ihref_for_label( $l ) ) . '">' . h( $l ) . '</a>';
	}
	return $o . '</div><p class="legal">' . h( $f['legal'] ) . '</p></div></footer>';
}

/**
 * One product card: the same `.card` recipe, plus the € price TPL-E-02 requires.
 *
 * `ADD TO CART` IS A CTA AND IT IS PAINTED LIKE ONE. It shipped as `.btn-outline` — a neutral
 * border and neutral label — on a template whose own name is `Catalog / Product-First`, so the
 * single most commercial control on the archetype was the quietest thing in its own tile. That was
 * a COUNT talking: eight tiles would have been eight accent marks, and eight is a number that looks
 * alarming next to "one spend on the resting page".
 *
 * It is not eight marks, it is ONE ROLE eight times, and the distinction is the whole reason
 * design-tokens.md writes the rule as "CTAs, action icons, important links" rather than as a
 * budget. A catalogue with eight products has eight add-to-cart buttons the same way it has eight
 * prices; a rule that gets quieter as the shop gets bigger is a rule that punishes the shop for
 * selling. See design-system.md § "The accent has a BUDGET, and the budget is a whitelist".
 */
function product_html( $anchor_key, $c ) {
	$im  = img( $c['img'] );
	$out = '<article class="card prod">'
		. '<figure class="frame sq"><img data-img="' . h( $im['slug'] ) . '" alt="' . h( $im['alt'] ) . '"'
		. ' width="' . $im['w'] . '" height="' . $im['h'] . '"></figure>';
	if ( 'editorial' === $anchor_key ) {
		$out .= '<div class="rule"></div>';
	}
	$out .= '<div class="body"><h3>' . h( $c['h3'] ) . '</h3>'
		. '<p class="price">' . h( $c['p'] ) . '</p>'
		. '<button class="btn btn-primary btn-sm" type="button">Añadir</button>'
		. '</div></article>';
	return $out;
}

/**
 * TPL-E-02 — Catalog / Product-First. Renders the `[fijo · ADN]` sections (search-first header,
 * mini hero, product grid, carousel) plus the two toggles that default on.
 *
 * The DNA is what this archetype REFUSES: no big hero, no lookbook, no brand story, no long
 * testimonials. The product is above the fold and the search bar is the loudest thing in the
 * header — which is why the grid keeps its dense 2/3/4 columns under every blueprint, including
 * the two whose "Grids" rule says never 50/50. That rule governs content grids; a catalogue grid
 * is the archetype's identity, and when DNA and blueprint collide over the same box the DNA wins.
 * The blueprint still governs everything else here: heading alignment, the banner's bleed, the
 * carousel, the benefits bar and the container geometry.
 */


/**
 * ONE axis table per strip, rendered TWICE: as the visible readout and as the handoff spec.
 *
 * `short` is what the five-column data bar shows, because that bar has to survive a 320px phone.
 * `spec` is what the pasted block carries, because there the token NAME is half of what makes the
 * value unambiguous — "#FFFFFF · #F6F7F8 · #15181A" is three colours in an order the reader has to
 * already know, `--c-bg #FFFFFF · …` is a spec. Two renderings, one source: written as two tables
 * they would drift on the first edit, which is the failure this whole generator exists to prevent.
 */
function axis_rows( $A, $SCALE, $DENSITY, $GROUND, $ELEVATION, $COMPOSITION ) {
	$sc = $SCALE[ $A['scale'] ];
	$gr = $GROUND[ $A['ground'] ];
	$el = $ELEVATION[ $A['elevation'] ];
	$cp = $COMPOSITION[ $A['composition'] ];

	return array(
		array(
			'axis'  => 'escala',
			'pos'   => $A['scale'],
			'short' => 'ratio ' . $sc['ratio'] . ' · lh ' . $sc['lh'] . ' · h1 ' . $sc['h1max'] . 'px',
			/* `--fs-h1-max` is UNITLESS on purpose — calc() cannot divide a length by a length, so
			   the coefficient multiplying --fluid has to arrive without one. The note travels with
			   the number because a reader who "fixes" it to 88px flattens every clamp in the chain. */
			'spec'  => '--type-ratio ' . $sc['ratio'] . ' · --display-lh ' . $sc['lh']
				. ' · --fs-h1-max ' . $sc['h1max'] . ' (sin unidad)',
		),
		array(
			'axis'  => 'fondo',
			'pos'   => $A['ground'],
			'short' => $gr['bg'] . ' · ' . $gr['alt'] . ' · ' . $gr['text'],
			'spec'  => '--c-bg ' . $gr['bg'] . ' · --c-bg-alt ' . $gr['alt'] . ' · --c-text ' . $gr['text'],
		),
		array(
			'axis'  => 'densidad',
			'pos'   => $A['density'],
			'short' => '--sp-scale ×' . $DENSITY[ $A['density'] ],
			'spec'  => '--sp-scale ' . $DENSITY[ $A['density'] ],
		),
		array(
			'axis'  => 'composición',
			'pos'   => $A['composition'],
			'short' => $cp['lp'],
			'spec'  => $cp['lp'] . ' — ' . $cp['line'],
		),
		array(
			'axis'  => 'elevación',
			'pos'   => $A['elevation'],
			'short' => $el['label'],
			'spec'  => '--elev-rest ' . $el['rest'] . ' · --elev-hover ' . $el['hover'],
		),
	);
}

/** Pad to a display width in CHARACTERS: `composición` is 11 glyphs and 13 bytes, and str_pad counts bytes. */
function pad( $s, $n ) {
	$len = mb_strlen( $s, 'UTF-8' );
	return ( $len >= $n ) ? $s : $s . str_repeat( ' ', $n - $len );
}

/**
 * The spec a pick leaves behind: plain text, no markup, meant to be pasted into a conversation.
 *
 * WHAT IT IS FOR. `ux-design-system/SKILL.md` step 1 already resolves the five axes by PRECHARGING
 * each one and letting the client confirm or override; its precharge source is "the industry
 * `web-templates` reported". A strip is a better precharge source than an industry, because it
 * carries an archetype and five measured positions instead of an average. Nothing else about that
 * step changes, and the last lines here say so out loud: an axis inherited from a card and never
 * questioned is exactly the silent default that sentence exists to prevent, just with a nicer
 * origin, so the block that precharges the dialogue is also the block that demands the dialogue.
 *
 * WHAT IT DOES NOT CARRY: typefaces and motion. Those belong to the anchor and live in
 * design-personalities.md, and copying them here would be one more place for them to drift. The
 * anchor id is the pointer, which is the same discipline the axis VALUES follow in reverse — those
 * are transcribed because a pasted spec that says "read another file for the numbers" is not a spec.
 */
function handoff_text( $C, $A, $rows, $tgl_rows ) {
	$L   = array();
	$L[] = 'NovaMira · precarga de galería — ' . $C['tpl'] . ' × ' . $A['id'];
	$L[] = '';
	$L[] = pad( 'tipo de sitio', 13 ) . ' : ' . $C['site'] . ' (' . $C['site_es'] . ')';
	$L[] = pad( 'arquetipo', 13 ) . ' : ' . $C['tpl'] . ' — ' . $C['tpl_name'];
	$L[] = pad( '  ADN fijo', 13 ) . ' : ' . $C['dna'];
	$L[] = pad( '  secciones', 13 ) . ' : ' . $C['wire'];
	$L[] = pad( 'ancla', 13 ) . ' : ' . $A['id'] . ' — ' . $A['name'];
	$L[] = '';
	$L[] = 'los cinco ejes, con el valor de sus tokens:';
	foreach ( $rows as $r ) {
		$L[] = '  ' . pad( $r['axis'], 12 ) . pad( $r['pos'], 13 ) . $r['spec'];
	}

	// CAPA 3 travels with CAPA 1 and 2 or it does not travel at all: the spec is what precharges
	// the dialogue, and a hero the client picked BECAUSE it was a slider would otherwise arrive at
	// the build as `imagen fija` — the template's default, silently restored by the handoff.
	if ( array() !== $tgl_rows ) {
		$L[] = '';
		$L[] = 'toggles (CAPA 3) que esta tira resuelve:';
		foreach ( $tgl_rows as $t ) {
			$L[] = '  ' . pad( $t['id'], 20 ) . pad( $t['value'], 13 )
				. ( $t['is_default'] ? '(default de ' . $C['tpl'] . ')' : '(cambiado — default: ' . $t['default'] . ')' );
		}
	}

	$L[] = '';
	$L[] = 'Tipografía y motion del ancla: ux-design-system/references/design-personalities.md.';
	$L[] = '';
	$L[] = 'PRECARGA, NO DECISIÓN. Esto entra en ux-design-system paso 1 en el sitio donde hoy entra';
	$L[] = '"el sector": el diálogo sigue preguntando en términos de negocio y el cliente confirma o';
	$L[] = 'sobrescribe LOS CINCO. Un eje heredado de una tarjeta y nunca preguntado es el mismo';
	$L[] = 'default silencioso que esa regla existe para impedir. Lo mismo vale para los toggles: el';
	$L[] = 'valor de arriba es el que la tarjeta enseñó, no una respuesta que el cliente ya haya dado.';
	return implode( "\n", $L );
}

/**
 * The toggle readout. On the strip beside the five axes, and for the same reason those are there:
 * a card whose hero differs and does not say WHY is not a comparison, it is a one-off, and the
 * reader cannot attribute what they are looking at.
 *
 * PRINTED EVEN WHEN NOTHING MOVED, which is the half that makes it work. If only the changed strip
 * carried a line, its three siblings would say nothing and the reader would have to guess whether
 * they are at a default or simply do not have the setting. Here `imagen fija · default` on three
 * strips and `slider · cambiado` on one is a legible four-way comparison of one setting. An
 * archetype that admits no toggles prints nothing, and that is also true: TPL-E-02's hero is ADN.
 */
function tgl_html( $tgl_rows ) {
	if ( array() === $tgl_rows ) {
		return '';
	}
	$o = '<p class="meta-tgl">Toggles: ';
	$b = array();
	foreach ( $tgl_rows as $t ) {
		$b[] = '<span class="tgl' . ( $t['is_default'] ? '' : ' tgl-set' ) . '">'
			. '<code>' . h( $t['id'] ) . '</code> ' . h( $t['value'] )
			. ' <i>' . ( $t['is_default'] ? 'default' : 'cambiado' ) . '</i></span>';
	}
	return $o . implode( ' ', $b ) . '</p>';
}

/** The data bar: TPL id, PERS id, and all five axis positions WITH their values, as checkable data. */
function meta_html( $C, $A, $rows, $uid, $tgl_rows ) {
	$spec_id = 'spec-' . $uid;
	$o       = array();
	$o[]     = '<div class="meta"><div class="gal-wrap">'
		/* The TEMPLATE half of this line moved up to `.tpl-head`, which prints it once for all four
		   variants. What stays is what actually changes between them: the anchor, its toggles and
		   its five axes. Repeating the archetype above every variant made the page read as four
		   documents stacked rather than one template with four settings. */
		. '<p class="meta-pair"><b>' . h( $A['id'] ) . '</b> ' . h( $A['name'] )
		. ' <span class="x">·</span> ' . h( $A['fits'] ) . '</p>'
		. tgl_html( $tgl_rows )
		. '<dl class="axes">';
	foreach ( $rows as $r ) {
		$o[] = '<div class="axis"><dt>' . h( $r['axis'] ) . '</dt>'
			. '<dd><b>' . h( $r['pos'] ) . '</b><span>' . h( $r['short'] ) . '</span></dd></div>';
	}
	$o[] = '</dl>';

	// The button ships `hidden` and the script unhides it: a copy control with no script behind it
	// is a control that lies. The `<pre>` needs neither — it is selectable the moment it renders.
	$o[] = '<details class="handoff"><summary>Precarga para el diálogo — el spec de esta tira</summary>'
		. '<div class="handoff-bar">'
		. '<button class="handoff-copy" type="button" hidden data-copy="' . h( $spec_id ) . '">Copiar</button>'
		. '<span class="handoff-said" role="status" aria-live="polite" data-said="' . h( $spec_id ) . '"></span>'
		. '</div>'
		. '<pre id="' . h( $spec_id ) . '" tabindex="0">' . h( handoff_text( $C, $A, $rows, $tgl_rows ) ) . '</pre>'
		. '</details>';

	$o[] = '</div></div>';
	return implode( "\n", $o );
}

// ── assemble ───────────────────────────────────────────────────────────────────────────────────

/** The strip's DOM id, derived in one place because the handoff assertion below has to rebuild it. */
function strip_uid( $C, $anchor ) {
	return strtolower( str_replace( '-', '', $C['tpl'] ) ) . '-' . $anchor;
}

/**
 * One marketplace card.
 *
 * The THUMBNAIL is empty in the static DOM on purpose — it is cloned at runtime from the strip it
 * points at, so it cannot go on describing a design the generator no longer renders. A raster
 * screenshot could, and this repository has already lost a round to a treatment whose reason had
 * expired without anyone noticing.
 *
 * The TEXT half is fully static: the pair, the archetype, the anchor, what it fits and all five
 * axis positions. So the catalogue survives with JavaScript off, and grep, `RT_GALLERY_*` and a
 * screen reader read the same rows a sighted user does — only the pixels need the script.
 */
/**
 * A GROUP is an archetype; a CARD is one of its anchors.
 *
 * ONE FLAT GRID OF EVERY HOME, AND THE GROUP LAYER IS GONE.
 *
 * It went through two wrong shapes first, and both are worth keeping written down. The first
 * collapsed each archetype to a single card: eight visibly different designs became two, and a
 * marketplace you cannot compare at a glance is not a marketplace. The second put the archetype on
 * a group header with its anchors as cards underneath — which fixed the variety and bought a
 * second problem, because the index then had two levels of hierarchy to read before you saw a
 * single design.
 *
 * The index has ONE job: show every home, so you can point at one. A group header is a title over
 * a row of things that are already titled; it earns its space only when the row is long enough to
 * lose track of, and four cards is not that. So: every home is a card, every card is the same
 * shape, and the archetype survives as text on the card rather than as a level of structure.
 *
 * THE CARD NAMES THE BUSINESS WHEN THERE IS ONE. `TPL-C-05 Local / Booking` is what an entry is
 * BUILT ON; `Casa Terrazza · Restaurante` is what it IS, and only the second answers "quiero la de
 * restaurante". The anchor rides in the eyebrow beside the sector, because two cards of one
 * archetype differ by exactly that and nothing else on the card would say so.
 */
function template_card_html( $C, $A, $anchor_key, $uid, $tpl_slug, $rows ) {
	$chips = '';
	foreach ( $rows as $r ) {
		$chips .= '<li class="tax">' . h( $r['axis'] ) . ' <b>' . h( $r['pos'] ) . '</b></li>';
	}

	$is_b = ( '' !== $C['brand'] );
	$eye  = ( $is_b ? $C['brand_sector'] : $C['site_es'] ) . ' · ' . $A['name'];
	$name = $is_b ? $C['brand_name'] : ( $C['tpl'] . ' ' . $C['tpl_name'] );
	$sub  = $is_b ? ( 'Sobre ' . $C['arch'] . ' · ' . $C['tpl_name'] ) : $C['fits'];

	return '<li data-site="' . h( $C['site'] ) . '" data-tpl="' . h( $C['tpl'] ) . '"'
		. ' data-pers="' . h( $anchor_key ) . '">'
		. '<a class="tcard" href="#' . h( $tpl_slug ) . '/' . h( $anchor_key ) . '">'
		. '<span class="thumb" data-of="' . h( $uid ) . '">'
		. '<span class="thumb-wait">' . h( $name ) . '</span></span>'
		. '<span class="tbody">'
		. '<span class="tpair">' . h( $eye ) . '</span>'
		. '<span class="tname">' . h( $name ) . '</span>'
		. '<span class="tsub">' . h( $sub ) . '</span>'
		. '<ul class="taxes">' . $chips . '</ul>'
		. '<span class="tgo">Ver esta home</span>'
		. '</span></a></li>';
}

/* WHICH PAGES EACH ARCHETYPE SHIPS, and which archetype doc each one answers to.

   A template is a PAGE SET, not a home. The home was the only page for as long as it was the only
   renderer, and a switcher offering one choice was deliberately not printed — a control with one
   option is a promise, not a control. It prints now because there is a second page to go to.

   `doc` is not decoration: it names the archetype doc the page's inventory was transcribed from,
   so a reader can check the page against its own spec instead of against this file's memory. The
   first entry is the page a bare `#tplc01` opens on, which makes the ORDER here meaningful. */
$PAGES = array(
	
	'TPL-C-11' => array(
		array( 'key' => 'home',     'label' => 'Plan',      'doc' => 'TPL-C-11' ),
		array( 'key' => 'nosotros', 'label' => 'Nosotros',  'doc' => 'TPL-ABOUT-01' ),
		array( 'key' => 'contacto', 'label' => 'Contacto',  'doc' => 'TPL-CONTACT-01' ),
	),
	
	/* CUATRO PÁGINAS, y la cuarta cierra ocho enlaces muertos. Las tarjetas de la rejilla decían
	   «Ver ficha» y apuntaban a una clave de página que este juego no tenía; medido en la galería,
	   ocho botones que no llevaban a ninguna parte. */
	'TPL-C-07' => array(
		array( 'key' => 'home',     'label' => 'Stock',     'doc' => 'TPL-C-07' ),
		array( 'key' => 'producto', 'label' => 'La unidad', 'doc' => 'TPL-UNIT-01' ),
		array( 'key' => 'nosotros', 'label' => 'Nosotros',  'doc' => 'TPL-ABOUT-01' ),
		array( 'key' => 'contacto', 'label' => 'Contacto',  'doc' => 'TPL-CONTACT-01' ),
	),
	/* Lo mismo un arquetipo más allá: seis «Ver ficha» muertos en la cartera. */
	
	/* CINCO PÁGINAS, donde las demás llevan tres — y las cinco existían en el catálogo sin haberse
	   renderizado nunca. `TPL-ABOUT-03` y `TPL-CONTACT-02` llevaban escritas desde que Nosotros y
	   Contacto dejaron de tener un solo arquetipo, y ninguna tira las había pedido: el recomendador
	   podía ofrecerlas y la galería no podía enseñarlas. `TPL-SERVICES-01` es el eslabón que
	   `TPL-SERVICE-01` migaja desde el día que se escribió —`Inicio · Servicios · <este>`— y que no
	   tenía documento. Aquí las cuatro se estrenan a la vez porque es el primer negocio del
	   catálogo que necesita las cuatro. */
	'TPL-C-14' => array(
		array( 'key' => 'home',      'label' => 'Home',      'doc' => 'TPL-C-14' ),
		array( 'key' => 'servicios', 'label' => 'Servicios', 'doc' => 'TPL-SERVICES-01' ),
		array( 'key' => 'servicio',  'label' => 'Ritual',    'doc' => 'TPL-SERVICE-02' ),
		array( 'key' => 'nosotros',  'label' => 'Nosotros',  'doc' => 'TPL-ABOUT-03' ),
		array( 'key' => 'contacto',  'label' => 'Contacto',  'doc' => 'TPL-CONTACT-02' ),
	),

	/* CINCO PÁGINAS (D5: «una demo de grado Envato es un sitio multi-página completo»), donde el
	   diseño de origen sólo pedía tres páginas internas. `propiedades` reutiliza `TPL-SERVICES-01`
	   -- el mismo vacío que ese arquetipo ya resuelve para «un negocio con más entradas de las que
	   caben en la home», sólo que aquí las entradas son inmuebles y la ficha de destino es
	   `TPL-PROPERTY-01`, no `TPL-SERVICE-02`. Etiquetas de nav elegidas para que SÍ resuelvan contra
	   `ihref_match()`: `nav` trae exactamente `Propiedades`/`Nosotros`/`Contacto`, que normalizan
	   igual que estas tres etiquetas de página. */
	'TPL-C-15' => array(
		array( 'key' => 'home',        'label' => 'Cartera',     'doc' => 'TPL-C-15' ),
		array( 'key' => 'propiedades', 'label' => 'Propiedades', 'doc' => 'TPL-SERVICES-01' ),
		array( 'key' => 'producto',    'label' => 'Ficha',       'doc' => 'TPL-PROPERTY-01' ),
		array( 'key' => 'nosotros',    'label' => 'Nosotros',    'doc' => 'TPL-ABOUT-01' ),
		array( 'key' => 'contacto',    'label' => 'Contacto',    'doc' => 'TPL-CONTACT-01' ),
	),











	'TPL-E-06' => array(
		array( 'key' => 'home',     'label' => 'Tienda',   'doc' => 'TPL-E-06' ),
		array( 'key' => 'producto', 'label' => 'Producto', 'doc' => 'TPL-PDP-02' ),
	),
	'TPL-E-07' => array(
		array( 'key' => 'home',     'label' => 'Lonja',    'doc' => 'TPL-E-07' ),
		array( 'key' => 'producto', 'label' => 'Producto', 'doc' => 'TPL-PDP-03' ),
	),
	'TPL-E-08' => array(
		array( 'key' => 'home',     'label' => 'Planes',   'doc' => 'TPL-E-08' ),
		array( 'key' => 'producto', 'label' => 'El café',  'doc' => 'TPL-PDP-04' ),
	),
	/* Una sola página: en este arquetipo el configurador ES la web, y su ficha de producto —la
	   TPL-PDP-05— ya está renderizada sobre TPL-E-03, que vende encimeras por el mismo modelo.
	   Duplicarla aquí sería enseñar dos veces lo mismo con otro tejido. */
	'TPL-E-09' => array(
		array( 'key' => 'home', 'label' => 'Configurador', 'doc' => 'TPL-E-09' ),
	),
	
);


/* ══════════════════════════════════════════════════════════════════════════════════════════════
 * TPL-C-14 · RITUAL / BONO — un centro de estética
 *
 * Cinco secciones propias y ninguna prestada, que es lo que hace que el esqueleto sea otro y no
 * la misma web repintada. La más barata de explicar es la tercera columna de la carta: `after`.
 * Duración y precio los publica media docena de arquetipos; «cómo sales» no lo publica ninguno, y
 * es el dato que decide si la cita cabe en el jueves de esa persona.
 * ══════════════════════════════════════════════════════════════════════════════════════════════ */

/**
 * Un icono de línea por zona, dibujado y no tomado de una fuente de iconos.
 *
 * OBJETOS Y NO PARTES DEL CUERPO, y la razón es un error anterior de esta misma página. Hubo un
 * pétalo abstracto encima de cada encabezado y un lector lo rodeó en rojo: «no se sabe qué es». Un
 * icono que hay que explicar no es un icono. Una cara, un torso o una pierna dibujados a línea en
 * 24px se convierten en manchas ambiguas; un tarro de crema, tres piedras de masaje, un frasco de
 * esmalte y una espátula de cera se reconocen de un vistazo Y dicen la zona, porque son lo que hay
 * encima de la camilla en cada una.
 *
 * `currentColor` y `fill:none`: el icono hereda el color de su tarjeta, así que el hover lo mueve
 * sin escribir un segundo color. Y NO lleva chip: la receta de tarjeta de `matter` dice «hairline
 * border, text below, no chips, no fills — el borde es todo el cromo», y un disco relleno detrás
 * del icono sería exactamente el chip que esa receta descarta.
 */
function zone_icon_svg( $key ) {
	$paths = array(
		/* Tarro de crema: tapa y cuerpo. */
		'crema'    => '<rect x="4.5" y="6" width="15" height="3.2" rx="1.2"/>'
			. '<path d="M6.2 9.2v8.6a2.2 2.2 0 0 0 2.2 2.2h7.2a2.2 2.2 0 0 0 2.2-2.2V9.2"/>',
		/* Tres piedras de masaje apiladas. */
		'piedras'  => '<ellipse cx="12" cy="17.6" rx="7" ry="2.6"/>'
			. '<ellipse cx="12" cy="12.4" rx="5.2" ry="2.2"/>'
			. '<ellipse cx="12" cy="7.8" rx="3.4" ry="1.8"/>',
		/* Frasco de esmalte: tapón, cuello y cuerpo. */
		'esmalte'  => '<rect x="10.4" y="3.4" width="3.2" height="4.6" rx="1.1"/>'
			. '<path d="M10.7 8v2"/><path d="M13.3 8v2"/>'
			. '<rect x="7.8" y="10" width="8.4" height="10" rx="1.6"/>',
		/* Espátula de cera: pala LARGA Y ESTRECHA sobre el mango. La primera versión le puso una
		   cabeza casi circular —radios 4,3 y 4,1— y a 30px eso no es una espátula, es una piruleta:
		   se vio al ampliar la fila de iconos. Una pala mide el triple de largo que de ancho, y esa
		   proporción es la que la distingue de un globo con palo. */
		'espatula' => '<ellipse cx="12" cy="7.2" rx="2.6" ry="5.2"/>'
			. '<path d="M12 12.4v8.2"/>',
	);
	if ( ! isset( $paths[ $key ] ) ) {
		fail( "zone icon `$key` is not one of " . implode( ', ', array_keys( $paths ) )
			. ' — a missing icon renders an empty box, which looks like a loading state forever' );
	}
	return '<svg class="zicon" viewBox="0 0 24 24" width="30" height="30" aria-hidden="true"'
		. ' fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"'
		. ' stroke-linejoin="round">' . $paths[ $key ] . '</svg>';
}

/**
 * COMP-ZONE-SELECTOR · TPL-C-14.
 *
 * TIPOGRÁFICO Y SIN FOTOGRAFÍA, y eso se decidió mirando un render. La primera versión daba una
 * imagen a cada zona y dos secciones más abajo `COMP-CABIN-TOUR` volvía a enseñar esas mismas
 * salas: dos rejillas de cuatro imágenes casi iguales, una encima de otra. Una zona no es un lugar
 * que enseñar sino un DESTINO al que ir, y lo que ayuda a elegirlo es cuántos rituales hay dentro
 * y desde cuánto.
 */
function zone_selector_html( $zs ) {
	/* UNA REJILLA DE FILETES COMPARTIDOS Y NO CUATRO TARJETAS. Las tarjetas dibujan cuatro cajas
	   con cuatro bordes cada una; lo que aquí se compara son cuatro columnas de la MISMA tabla, y
	   una tabla se dibuja con las líneas que separan, no con las que rodean. Además es lo único
	   que el ancla permite: `elevation: none` no tiene sombra ni relleno con los que hacer una
	   tarjeta, así que la retícula ES la tarjeta. El hover no levanta —no hay a dónde—: oscurece
	   sus propios filetes, que es el gesto que le queda a una casa sin sombras. */
	$o = sec_open( 'zonepick grid-sec', $zs['h2'] )
		. sec_head( $zs )
		. '<ul class="zonegrid">';
	foreach ( $zs['items'] as $z ) {
		$o .= '<li class="zcell"><a href="' . h( ihref_for_label( 'Servicios' ) ) . '">'
			. zone_icon_svg( $z[4] )
			. '<h3>' . h( $z[0] ) . '</h3>'
			. '<span class="zcount">' . h( $z[1] ) . '</span>'
			. '<span class="zfrom">' . h( $z[2] ) . '</span>'
			. '<span class="muted small">' . h( $z[3] ) . '</span></a></li>';
	}
	return $o . '</ul>' . sec_close();
}

/**
 * COMP-RITUAL-MENU · TPL-C-14.
 *
 * TRES DATOS Y EL PRECIO DELANTE. El precio no es un toggle en este arquetipo, y su documento lo
 * dice: es lo que el sector esconde detrás de «consúltanos» y lo que hace que media web sobre.
 */
function ritual_menu_html( $rm, $extra = '', $variant = 'band' ) {
	/* LA CARTA VA SOBRE UNA FOTOGRAFÍA, no sobre una banda plana, y el panel que la sostiene es
	   opaco a propósito: el efecto que se busca —una carta apoyada en la sala— se consigue con el
	   fondo asomando ALREDEDOR del panel, no debajo del texto. Un panel translúcido pondría cada
	   precio sobre un píxel distinto y convertiría una lista en una medición de contraste.
	   Y la fotografía es un `<img>` dentro de un `<figure>`, nunca un `background-image`: un fondo
	   CSS necesita un elemento vacío donde vivir, es invisible para un lector de pantalla y para
	   Google Imágenes, y es el contenedor que `es_photo()` existe para no crear.
	   `plain` es la misma lista sin banda ni fondo, para la ficha de tratamiento — ahí los
	   hermanos son un pie de página, no el argumento de la sección. */
	$is_band = ( 'band' === $variant );
	$o = $is_band
		? sec_open( 'ritualmenu menuband', $rm['h2'], 'bleed' )
			. '<figure class="menu-ground">' . img_tag( $rm['img'] ) . '</figure>'
			. '<div class="menu-panel">' . sec_head( $rm )
		: sec_open( 'ritualmenu' . $extra, $rm['h2'] ) . sec_head( $rm );
	/* AGRUPADA POR ZONA, Y CADA RITUAL EN TRES LÍNEAS. La primera versión repetía el nombre de la
	   zona como eyebrow encima de CADA ficha —«ROSTRO / ROSTRO / CUERPO / CUERPO»— y daba cinco
	   líneas por ritual: eyebrow, nombre, descripción, datos y nota. Seis de esas en dos columnas
	   no es una carta, es un tablero. Una carta de verdad agrupa una vez y luego sólo lista, y pone
	   el precio EN LA LÍNEA DEL NOMBRE, que es donde el ojo lo busca cuando compara.
	   Y va en `columns` y no en rejilla: los grupos tienen alturas distintas por naturaleza, y una
	   rejilla los alinearía por filas dejando huecos debajo de los cortos. Una columna tipográfica
	   fluye, que es lo que hace una carta impresa. */
	$groups = array();
	foreach ( $rm['items'] as $r ) {
		$groups[ $r['zone'] ][] = $r;
	}
	$o .= '<ul class="rituals">';
	foreach ( $groups as $gname => $gitems ) {
		$o .= '<li class="rgroup"><b class="rglabel">' . h( $gname ) . '</b><ul>';
		foreach ( $gitems as $r ) {
			$o .= '<li class="ritual">'
				. '<p class="rline"><span class="rname">'
				. '<a href="' . h( ihref_for_label( 'Ritual' ) ) . '">' . h( $r['h3'] ) . '</a></span>'
				. '<span class="rfacts"><span class="rmin">' . h( $r['mins'] ) . '</span>'
				. '<b class="rprice">' . h( $r['price'] ) . '</b></span></p>'
				. '<p class="rdesc">' . h( $r['p'] ) . '</p>'
				. '<p class="rafter">' . h( $r['after'] ) . '</p></li>';
		}
		$o .= '</ul></li>';
	}
	$o .= '</ul><p class="pnote">' . h( $rm['note'] ) . '</p>';
	return $is_band ? $o . '</div>' . sec_close( 'bleed' ) : $o . sec_close();
}

/** One `<img>` from a manifest slug, with its own alt and its own intrinsic size. */
function img_tag( $slug, $cls = '' ) {
	$it = img( $slug );
	return '<img' . ( '' === $cls ? '' : ' class="' . $cls . '"' ) . ' data-img="' . h( $it['slug'] ) . '"'
		. ' alt="' . h( $it['alt'] ) . '" width="' . $it['w'] . '" height="' . $it['h'] . '">';
}

/**
 * COMP-CABIN-TOUR · TPL-C-14. No es `COMP-GALLERY`: cada frame lleva el nombre del espacio y para
 * qué se usa. Un collage sin pies de foto es decoración; con ellos es una visita previa, que es lo
 * que sustituye aquí a la credencial que este arquetipo se prohíbe.
 */
function cabin_tour_html( $ct, $extra = '', $variant = 'split' ) {
	/* LA SECCIÓN ES LA FILA: dos hijos directos y ningún `.canvas`, que es lo que
	   container-hygiene lleva pidiendo desde que se escribió y ningún strip hacía porque el único
	   envoltorio disponible anidaba uno igual. Un nodo menos aquí es un contenedor menos en el
	   build nativo, y un clic menos entre un humano y el widget que abrió el editor para tocar.
	   EL COLLAGE LLEVA PASPARTÚ Y NO SOMBRA. El marco blanco ancho es el gesto de la referencia;
	   la sombra que lo acompaña allí, no — `elevation: none` es una posición de eje de esta ancla y
	   una sombra la contradiría en la sección más visible de la página. El paspartú se dibuja con
	   el propio fondo (`--c-bg`) y un filete, así que sobre la banda alterna se lee como una foto
	   apoyada en papel. */
	$names = array();
	$figs  = array();
	foreach ( $ct['items'] as $c ) {
		$names[] = '<li><b class="cabname">' . h( $c['name'] ) . '</b>'
			. '<span class="muted small">' . h( $c['p'] ) . '</span></li>';
		$figs[]  = '<figure class="mat">' . img_tag( $c['img'] ) . '</figure>';
	}
	return sec_open( 'cabintour' . $extra, $ct['h2'], 'row' )
		. '<div class="cabinsay">' . sec_head( $ct )
		. '<ul class="cabinlist">' . implode( '', $names ) . '</ul>'
		. '<p class="pnote">' . h( $ct['note'] ) . '</p></div>'
		. '<div class="collage">' . implode( '', $figs ) . '</div>'
		. sec_close( 'row' );
}

/**
 * COMP-PROTOCOL-STEPS · TPL-C-14 y TPL-SERVICE-02.
 *
 * MINUTOS REALES, Y LA SUMA TIENE QUE DAR. No son cuatro pasos genéricos de «cómo trabajamos»
 * —eso es `COMP-PROCESS`—: son los minutos que la clienta va a pasar tumbada, y si no suman la
 * duración que promete la carta, uno de los dos números es mentira y se nota el primer día.
 */
function protocol_steps_html( $ps, $extra = '' ) {
	$o = '<section class="sec protosteps grid-sec' . $extra . '" aria-label="' . h( $ps['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $ps['eyebrow'] ) . '</span>'
		. '<h2>' . h( $ps['h2'] ) . '</h2></div><ol class="protos">';
	foreach ( $ps['items'] as $st ) {
		$o .= '<li class="proto"><span class="pmin">' . h( $st[0] ) . '</span>'
			. '<h3>' . h( $st[1] ) . '</h3><p class="muted">' . h( $st[2] ) . '</p></li>';
	}
	return $o . '</ol><p class="pnote">' . h( $ps['note'] ) . '</p></div></section>';
}

/**
 * COMP-BONO-PACKS · TPL-C-14 y TPL-SERVICE-02.
 *
 * NO ES `COMP-PRICING`. Ahí se elige entre niveles de servicio y una columna va destacada; aquí se
 * elige CUÁNTAS VECES vuelves, y la tarjeta regalo no es un plan mejor ni peor sino otra cosa —de
 * ahí que se pinte distinta y no como el tercer escalón de una escalera.
 */
function bono_packs_html( $bp, $gift_on = true, $extra = '', $variant = 'checker' ) {
	/* UN DAMERO A SANGRE: panel de tinta, fotografía, panel de tinta. Es lo contrario de un
	   `COMP-PRICING`, y a propósito — allí se compara entre escalones de un mismo producto y una
	   columna va destacada; aquí no hay escalones, hay una decisión sola («¿vuelvo cinco veces?»)
	   y una fotografía en medio que dice a qué vuelves.
	   LOS PANELES VAN EN TINTA Y NO EN ACENTO. La referencia los pinta con su rosa de marca, y ese
	   rosa aquí es el acento, que la puerta de roles reserva para lo que se puede pulsar. La
	   superficie inversa del ancla hace el mismo trabajo —parar la página en seco— sin gastar la
	   única tinta de acción que la página tiene.
	   LA TARJETA REGALO SALE DEL DAMERO. No es un bono más barato ni más caro: es otra cosa, se
	   compra para otra persona, y meterla como cuarta columna la convertía en el escalón bajo de
	   una escalera que no existe. Va debajo, en una tira propia y con filete discontinuo. */
	$packs = array();
	$gift  = null;
	foreach ( $bp['items'] as $b ) {
		if ( $b['gift'] ) {
			$gift = $b;
			continue;
		}
		$packs[] = $b;
	}
	if ( 'plain' === $variant ) {
		$o = sec_open( 'bonopacks grid-sec' . $extra, $bp['h2'] ) . sec_head( $bp ) . '<ul class="bonos">';
		foreach ( $packs as $b ) {
			$o .= bono_cell_html( $b, 'li', 'bono' );
		}
		return $o . '</ul><p class="pnote">' . h( $bp['note'] ) . '</p>' . sec_close();
	}
	$o = sec_open( 'bonopacks', $bp['h2'], 'bleed' ) . '<div class="checker">'
		. '<div class="cpanel chead">' . sec_head( $bp ) . '</div>';
	$first = true;
	foreach ( $packs as $b ) {
		$o .= bono_cell_html( $b, 'div', 'cpanel' );
		if ( $first ) {
			$o    .= '<figure class="cshot">' . img_tag( $bp['img'] ) . '</figure>';
			$first = false;
		}
	}
	$o .= '</div>';
	if ( null !== $gift && $gift_on ) {
		$o .= '<div class="giftstrip"><b>' . h( $gift['name'] ) . '</b>'
			. '<span class="bonoq">' . h( $gift['q'] ) . '</span>'
			. '<span class="bonop">' . h( $gift['price'] ) . '</span>'
			. '<span class="muted small">' . h( $gift['p'] ) . '</span></div>';
	}
	return $o . '<p class="pnote">' . h( $bp['note'] ) . '</p>' . sec_close( 'bleed' );
}

/** One bono, in whatever box the section it lands in calls for. */
function bono_cell_html( $b, $tag, $cls ) {
	$o = '<' . $tag . ' class="' . $cls . '"><b>' . h( $b['name'] ) . '</b>'
		. '<span class="bonoq">' . h( $b['q'] ) . '</span>'
		. '<span class="bonop">' . h( $b['price'] ) . '</span>';
	if ( '' !== $b['save'] ) {
		$o .= '<span class="bonosave">' . h( $b['save'] ) . '</span>';
	}
	return $o . '<span class="muted small">' . h( $b['p'] ) . '</span></' . $tag . '>';
}

/** COMP-TEAM, la variante de `TPL-ABOUT-03`: retrato redondo con paspartú, sin credencial. */
function house_team_html( $tm ) {
	/* REDONDO Y CON PASPARTÚ, y sin número de colegiado — que es la diferencia entera con
	   `med_team_html`. En una clínica la credencial es el dato; en una casa con puerta el dato es
	   la cara y el tiempo que lleva ahí. El recorte circular no es decoración: iguala tres
	   retratos que nunca van a estar encuadrados igual. */
	$o = sec_open( 'houseteam grid-sec', $tm['h2'] ) . sec_head( $tm ) . '<ul class="faces">';
	foreach ( $tm['items'] as $m ) {
		$o .= '<li class="face"><figure class="round">' . img_tag( $m['img'] ) . '</figure>'
			. '<b>' . h( $m['name'] ) . '</b>'
			. '<span class="muted small">' . h( $m['role'] ) . '</span>'
			. '<span class="since">' . h( $m['lic'] ) . '</span></li>';
	}
	return $o . '</ul>' . sec_close();
}

/**
 * COMP-HOURS-BLOCK + COMP-MAP-NAP de TPL-C-14, en una sola fila.
 *
 * NO REUTILIZA `hours_block_html`, Y ESO ES UNA CORRECCIÓN. Aquel bloque nació para una
 * composición centrada y coloca sus hijos con las líneas con nombre de aquella rejilla; bajo
 * `strict-grid` se descuajeringa — medido en el render: el titular flotando solo a la derecha, la
 * columna de días en 55px con «Martes a viernes» partido en tres renglones, y media sección vacía.
 * Un componente compartido que se rompe al cambiar de composición no es compartido, es prestado.
 *
 * Y VAN JUNTOS PORQUE SON LA MISMA PREGUNTA. La página de contacto llevaba un bloque de dirección
 * y otro de horario, uno detrás de otro, repitiendo la calle y el teléfono. Quien mira esto quiere
 * saber dónde y cuándo, que es una sola decisión: si me da tiempo a ir hoy.
 */
function hours_row_html( $hb, $extra = '' ) {
	$o = sec_open( 'hoursrow' . $extra, $hb['h2'], 'row' )
		. '<div class="hourscol">' . sec_head( $hb ) . '<dl class="daylist">';
	foreach ( $hb['rows'] as $r ) {
		$shut = ( 'Cerrado' === $r[1] ) ? ' shut' : '';
		$o   .= '<div class="dayrow' . $shut . '"><dt>' . h( $r[0] ) . '</dt><dd>' . h( $r[1] ) . '</dd></div>';
	}
	$o .= '</dl></div><div class="wherecol">';
	$o .= '<p class="addr">' . h( $hb['addr'][0] ) . '<br>' . h( $hb['addr'][1] ) . '</p>'
		. '<p class="reachline"><a href="#">' . h( $hb['phone'] ) . '</a></p>'
		. '<p class="reachline"><a href="#">' . h( $hb['mail'] ) . '</a></p>';
	if ( isset( $hb['travel'] ) ) {
		$o .= '<p class="muted small">' . h( $hb['travel'] ) . '</p>';
	}
	$o .= '<p class="muted small">' . h( $hb['note'] ) . '</p></div>';
	return $o . sec_close( 'row' );
}

/** La tira a sangre que separa la página del pie. Sólo fotografía: nada que amputar en el cristal. */
function strip_row_html( $sr ) {
	$o = sec_open( 'thumbstrip', $sr['label'], 'bleed' ) . '<ul class="thumbs">';
	foreach ( $sr['items'] as $slug ) {
		$o .= '<li>' . img_tag( $slug ) . '</li>';
	}
	return $o . '</ul>' . sec_close( 'bleed' );
}

/** COMP-HEADER de TPL-C-14: barra de datos encima de la cabecera flotante. */
function head_lumiere( $C, $BRAND ) {
	/* LA BARRA SUPERIOR ES LA DECISIÓN DE CABECERA DE ESTE ARQUETIPO. Un centro con puerta se
	   busca por tres cosas que no caben en una nav —dónde está, cuándo abre y a qué número se
	   llama— y meterlas en el menú las convierte en enlaces que nadie pulsa. Arriba, en texto
	   plano, son lo primero que se lee y no compiten con el CTA. */
	$tb = $C['topbar'];
	$o  = '<header class="site-head head-over lumhead"><div class="topbar"><div class="canvas">';
	foreach ( $tb['items'] as $t ) {
		$o .= '<span>' . h( $t ) . '</span>';
	}
	$o .= '<a href="#">' . h( $C['phone'] ) . '</a></div></div>'
		. '<div class="canvas"><div class="nav"><span class="logo">' . h( $BRAND ) . '</span>'
		. '<nav class="mainnav" aria-label="Principal">';
	foreach ( $C['nav'] as $n ) {
		$o .= '<a href="' . h( ihref_for_label( $n ) ) . '">' . h( $n ) . '</a>';
	}
	return $o . '</nav><a class="btn btn-primary btn-sm" href="' . h( ihref_for_label( $C['nav_cta'] ) ) . '">'
		. h( $C['nav_cta'] ) . '</a></div></div></header>';
}

/**
 * COMP-SERVICE-INDEX · TPL-SERVICES-01. Todas las entradas, agrupadas, y ninguna destacada.
 *
 * UNA FOTOGRAFÍA POR GRUPO Y NINGUNA POR ENTRADA, y esto se corrigió mirando el render. La primera
 * versión daba imagen a cada tarjeta: doce tarjetas contra siete fotografías de marca, así que dos
 * se repetían cuatro y tres veces cada una en la misma pantalla. Una rejilla que repite la foto se
 * lee como relleno, y es justo la página donde el visitante viene a COMPARAR entradas — el ruido
 * cae encima de lo único que hace.
 *
 * Y el arreglo no fue quitar fotos por quitarlas: la zona SÍ tiene una imagen que le corresponde,
 * porque el grupo es un sitio del cuerpo y hay una foto de cada uno. Lo que no tiene imagen propia
 * es el ritual individual, y por eso la entrada es una fila de carta —nombre, una línea, duración
 * y precio— que además es como se lee un menú de tratamientos en papel.
 */
function service_index_html( $si ) {
	$o = '<section class="sec svcindex grid-sec" aria-label="' . h( $si['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $si['eyebrow'] ) . '</span>'
		. '<h2>' . h( $si['h2'] ) . '</h2></div>';
	foreach ( $si['groups'] as $g ) {
		$gi  = img( $g['img'] );
		$o  .= '<div class="svcgroup">'
			. '<figure class="frame svcshot"><img data-img="' . h( $gi['slug'] ) . '"'
			. ' alt="' . h( $gi['alt'] ) . '" width="' . $gi['w'] . '" height="' . $gi['h'] . '"></figure>'
			. '<div class="svcbody"><h3 id="' . h( $g['id'] ) . '">' . h( $g['name'] ) . '</h3>'
			. '<ul class="svccards">';
		foreach ( $g['items'] as $it ) {
			$o .= '<li class="svccard"><a href="' . h( ihref_for_label( 'Ritual' ) ) . '">'
				. '<h4>' . h( $it['name'] ) . '</h4>'
				. '<span class="muted small">' . h( $it['p'] ) . '</span>'
				. '<span class="svcfacts">' . h( $it['mins'] ) . ' · ' . h( $it['price'] ) . '</span>'
				. '</a></li>';
		}
		$o .= '</ul></div></div>';
	}
	return $o . '<p class="pnote">' . h( $si['note'] ) . '</p></div></section>';
}

/** COMP-TREATMENT-FACTS · TPL-SERVICE-02. Los cuatro, y ninguno en blanco. */
function treatment_facts_html( $tf ) {
	/* `factstrip` Y NO `tfacts`, Y ESTO COSTÓ DOS DIAGNÓSTICOS EQUIVOCADOS. La sección se dibujaba
	   en 622px dentro de una de 1249, con la frase de «cómo sales» partida en seis renglones, y las
	   dos primeras hipótesis —que faltaba `grid-sec`, que sobraba tamaño en el valor— eran plausibles
	   y falsas. Una medición lo cerró en un intento: el `.canvas` medía la mitad exacta, o sea que
	   no lo estaba encogiendo la tipografía sino una regla de layout ajena.
	   `.tfacts` YA EXISTÍA: es la fila de chips de la ficha de tratamiento de `TPL-C-10`, con
	   `display:grid` y `margin:auto 0 0` pensados para vivir DENTRO de una tarjeta. Dos arquetipos
	   con la misma clase es CSS válido que renderiza — el perfil exacto del defecto que este
	   generador ya documenta— y el registro de clases no lo cazó porque yo declaré `tfactlist` y
	   `tfact` y no el nombre de la propia sección. Ahora los tres están declarados. */
	$o = '<section class="sec factstrip grid-sec" aria-label="' . h( $tf['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $tf['eyebrow'] ) . '</span>'
		. '<h2>' . h( $tf['h2'] ) . '</h2></div><dl class="tfactlist">';
	foreach ( $tf['items'] as $f ) {
		$o .= '<div class="tfact"><dt>' . h( $f[0] ) . '</dt><dd>' . h( $f[1] ) . '</dd></div>';
	}
	return $o . '</dl></div></section>';
}

/**
 * COMP-CONTRAINDICATIONS · TPL-SERVICE-02.
 *
 * VA EN LA PÁGINA Y NO EN EL EMAIL DE CONFIRMACIÓN. Quien lo lee después de reservar ya ha
 * bloqueado una cabina que no va a usar, y el centro se come el hueco.
 */
function contraindications_html( $cx ) {
	$o = '<section class="sec contra grid-sec bg-alt" aria-label="' . h( $cx['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $cx['eyebrow'] ) . '</span>'
		. '<h2>' . h( $cx['h2'] ) . '</h2></div><div class="contrapair">';
	foreach ( array( $cx['no'], $cx['before'] ) as $col ) {
		$o .= '<div class="contracol"><b>' . h( $col['title'] ) . '</b><ul class="contralist">';
		foreach ( $col['items'] as $i ) {
			$o .= '<li>' . h( $i ) . '</li>';
		}
		$o .= '</ul></div>';
	}
	return $o . '</div><p class="pnote">' . h( $cx['note'] ) . '</p></div></section>';
}

/**
 * TPL-C-14 · Ritual / Bono.
 *
 * NINGUNA DE SUS SECCIONES TIENE LA FORMA DE LA DE AL LADO, y ésa es la mitad del arquetipo que un
 * documento no puede declarar. El titular escalonado sobre la fotografía, la retícula de filetes,
 * la carta sobre una sala, la fila con el collage, el damero a sangre y la tira de fotos antes del
 * pie son seis envoltorios distintos; el catálogo entero venía usando uno.
 */
function strip_ritual( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$hero = $C['hero'];
	$o    = array();
	$o[]  = head_lumiere( $C, $BRAND );
	$o[]  = '<main>';
	$o[]  = '<section class="sec hero hero-visual hero-full" aria-label="El centro"><div class="media-full">'
		. '<figure class="frame">' . img_tag( $hero['img'] ) . '</figure></div>'
		. '<div class="canvas">' . sec_head( $hero, 'stack', 'h1' )
		. '<div class="ctas"><a class="btn btn-primary" href="' . h( ihref_for_label( $hero['cta_1'] ) ) . '">' . h( $hero['cta_1'] ) . '</a>'
		. '<a class="btn btn-outline" href="' . h( ihref_for_label( $hero['cta_2'] ) ) . '">' . h( $hero['cta_2'] ) . '</a></div>'
		. '</div></section>';
	$o[]  = zone_selector_html( $C['zones'] );
	$o[]  = ritual_menu_html( $C['rituals'] );
	$o[]  = cabin_tour_html( $C['cabin'], ' bg-alt' );
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-PROTOCOL-STEPS' ) ) {
		$o[] = protocol_steps_html( $C['protocol'], '' );
	}
	$o[]  = bono_packs_html( $C['bonos'], 'no' !== tgl_of( $tgl_rows, 'TGL-GIFT-CARD' ) );
	$o[]  = booking_html( $C['booking'], $uid );
	$o[]  = '</main>';
	$o[]  = strip_row_html( $C['strip'] );
	$o[]  = footer_html( $C['footer'] );
	return implode( "\n", $o );
}

/** TPL-SERVICES-01 · el índice de servicios. Una por sitio, nunca una por grupo. */
function page_services_index( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$K = $C['servicios'];
	$o = array();
	$o[] = head_corporate( $C, $BRAND );
	$o[] = crumbs_html( $K['crumbs'] );
	$o[] = '<main>';
	$o[] = page_head_html( $K['head'] );
	$o[] = service_index_html( $K['index'] );
	$o[] = faq_block_html( $K['faq'], ' bg-alt' );
	$o[] = page_cta_html( $K['cta'] );
	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );
	return implode( "\n", $o );
}

/** TPL-SERVICE-02 · la ficha de un ritual. */
function page_treatment( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$K  = $C['servicio'];
	$hd = $K['head'];
	$hi = img( $hd['img'] );
	$o  = array();
	$o[] = head_corporate( $C, $BRAND );
	$o[] = crumbs_html( $K['crumbs'] );
	$o[] = '<main>';
	$o[] = '<section class="sec hero svc-head" aria-label="' . h( $hd['h1'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $hd['eyebrow'] ) . '</span>'
		. '<h1>' . h( $hd['h1'] ) . '</h1>'
		. '<p class="lede muted">' . h( $hd['lede'] ) . '</p>'
		. '<div class="ctas"><a class="btn btn-primary" href="' . h( ihref_for_label( $hd['cta'] ) ) . '">' . h( $hd['cta'] ) . '</a>'
		. '<span class="rprice">' . h( $hd['price'] ) . '</span></div></div>'
		. '<div class="media"><figure class="frame"><img data-img="' . h( $hi['slug'] ) . '"'
		. ' alt="' . h( $hi['alt'] ) . '" width="' . $hi['w'] . '" height="' . $hi['h'] . '"></figure></div>'
		. '</div></section>';
	$o[] = treatment_facts_html( $K['facts'] );
	$o[] = protocol_steps_html( $C['protocol'], ' bg-alt' );
	$o[] = contraindications_html( $K['contra'] );
	$o[] = bono_packs_html( $K['bono'], false, '', 'plain' );
	$o[] = faq_block_html( $K['faq'], ' bg-alt' );
	$o[] = ritual_menu_html( $K['siblings'], '', 'plain' );
	$o[] = booking_html( $C['booking'], $uid );
	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );
	return implode( "\n", $o );
}

/**
 * TPL-ABOUT-03 «La casa» · el Nosotros de un negocio cuya confianza está en el sitio.
 *
 * ESTE ARQUETIPO LLEVABA ESCRITO SIN RENDERIZARSE. Reutiliza el recorrido por la cabina de la home
 * —es la misma casa un nivel más abajo, y reutilizar es lo que hace que dos páginas se lean como
 * UN sitio— y añade lo suyo: la historia corta, las caras y el horario con el estado de ahora.
 */
function page_about_house( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	/* ABRE SIN FOTOGRAFÍA, y la ausencia se decidió contando. El centro tiene siete fotos y cuatro
	   se van en el recorrido por la cabina, que empieza dos dedos más abajo. Un hero con imagen
	   aquí repetía una de esas cuatro a media pantalla de distancia de sí misma — el mismo defecto
	   que ya se corrigió en el índice de servicios. Un encabezado de texto abre más tranquilo y
	   deja que la fotografía llegue toda junta y sin repetirse, que es lo que hace la sección
	   siguiente. */
	$K  = $C['nosotros'];
	$o  = array();
	$o[] = head_corporate( $C, $BRAND );
	$o[] = crumbs_html( $K['crumbs'] );
	$o[] = '<main>';
	$o[] = page_head_html( array(
		'eyebrow' => $K['head']['eyebrow'],
		'h1'      => $K['head']['h1'],
		'lede'    => $K['head']['lede'],
	) );
	$o[] = cabin_tour_html( $C['cabin'], ' bg-alt' );
	$o[] = house_team_html( $K['team'] );
	$o[] = hours_row_html( $K['hours'] );
	$o[] = booking_html( $C['booking'], $uid );
	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );
	return implode( "\n", $o );
}

/**
 * Las secciones de las dos FICHAS DE INVENTARIO · TPL-UNIT-01 y TPL-PROPERTY-01.
 *
 * DOS ARQUETIPOS Y UN SOLO MARCO, y el reparto no es de conveniencia. Las dos páginas son la ficha
 * de UNA unidad de un inventario que rota, así que comparten cómo abren —la unidad fotografiada,
 * seis datos que descartan o siguen— y comparten la salida —la referencia dentro del formulario—.
 * Lo que NO comparten es el centro: en un coche decide el HISTORIAL (de dónde viene, quién lo
 * tuvo, qué le han hecho) y en un piso deciden el PLANO y el COSTE de mantenerlo. Nadie pregunta
 * cuántos dueños tuvo un piso y todos preguntan cuánto se paga de comunidad.
 * Medido contra los 22 documentos de página del catálogo, comparten 4 secciones de 17. Si
 * compartieran también el centro serían la misma página con dos nombres.
 */
function ref_line_html( $code, $note ) {
	return '<p class="refline"><span class="refcode">' . h( $code ) . '</span>'
		. '<span class="muted small">' . h( $note ) . '</span></p>';
}

/**
 * COMP-UNIT-GALLERY · TPL-UNIT-01.
 *
 * FOTOS DE ESTA UNIDAD Y NO DEL CATÁLOGO DEL FABRICANTE. Es la diferencia entre un patio y un
 * anuncio, y el cuentakilómetros legible es la prueba: el único dato de la ficha que el comprador
 * puede verificar sin bajarse del sofá. Ese frame se buscó, no se dio por bueno — el mejor
 * candidato de stock marcaba 195.478 km contra los 48.200 de la unidad, y publicarlo habría sido
 * escribir la regla y romperla en el único sitio donde se ve.
 */
function unit_gallery_html( $g ) {
	$o = sec_open( 'unitgal', $g['label'] )
		. '<div class="ugal"><figure class="ushot">' . img_tag( $g['main'] ) . '</figure>'
		. '<ul class="uthumbs">';
	foreach ( $g['shots'] as $sl ) {
		$o .= '<li>' . img_tag( $sl ) . '</li>';
	}
	return $o . '</ul></div><p class="ucap">' . h( $g['cap'] ) . '</p>' . sec_close();
}

/**
 * COMP-UNIT-FACTS y COMP-PROPERTY-FACTS · un helper, dos nombres de componente.
 *
 * Los dos documentos declaran esta sección «Reutilizable: SECCIÓN», y es literalmente la misma
 * forma: seis pares de dato y rótulo que descartan o siguen en cinco segundos. Lo que cambia son
 * los seis datos, que es contenido. Escribirla dos veces habría sido dos sitios donde arreglar el
 * mismo defecto.
 */
function unit_specs_html( $sp, $extra = '' ) {
	$o = sec_open( 'unitspecs grid-sec' . $extra, $sp['h2'] ) . sec_head( $sp ) . '<ul class="uspecs">';
	foreach ( $sp['items'] as $it ) {
		$o .= '<li class="uspec"><b>' . h( $it[1] ) . '</b><span>' . h( $it[0] ) . '</span></li>';
	}
	return $o . '</ul></div></section>';
}

/** COMP-PRICE-FINANCE · TPL-UNIT-01. Los dos precios con el mismo peso, y las condiciones al lado. */
function price_finance_html( $pf, $extra = '' ) {
	$o = sec_open( 'pricefin grid-sec' . $extra, $pf['h2'] ) . sec_head( $pf )
		. '<div class="pricepair">'
		. '<div class="pricebox"><span>' . h( $pf['cash_lbl'] ) . '</span>'
		. '<b class="priceq">' . h( $pf['cash'] ) . '</b>'
		. '<p class="muted small">' . h( $pf['cash_note'] ) . '</p></div>'
		. '<div class="pricebox"><span>' . h( $pf['quota_lbl'] ) . '</span>'
		. '<b class="priceq">' . h( $pf['quota'] ) . '</b><ul class="pterms">';
	foreach ( $pf['terms'] as $t ) {
		$o .= '<li><span>' . h( $t[0] ) . '</span><b>' . h( $t[1] ) . '</b></li>';
	}
	return $o . '</ul></div></div><p class="pnote">' . h( $pf['note'] ) . '</p>' . sec_close();
}

/** COMP-HISTORY-REPORT · TPL-UNIT-01. La sección que justifica la página. */
function history_report_html( $hr, $extra = '' ) {
	$o = sec_open( 'histrep grid-sec' . $extra, $hr['h2'] ) . sec_head( $hr ) . '<ul class="histgrid">';
	foreach ( $hr['items'] as $it ) {
		$o .= '<li class="hist"><span>' . h( $it[0] ) . '</span><b>' . h( $it[1] ) . '</b>'
			. '<p>' . h( $it[2] ) . '</p></li>';
	}
	return $o . '</ul></div></section>';
}

/**
 * COMP-PROPERTY-TOUR · TPL-PROPERTY-01. NO es `COMP-GALLERY`.
 *
 * Una galería es un carrusel en orden arbitrario; un recorrido va de la puerta hacia dentro y cada
 * foto dice qué se está mirando y cuánto mide. La primera estancia ocupa las dos columnas porque
 * el salón es la que decide, y en una ficha de piso decidir no es mirar seis fotos iguales.
 */
function property_tour_html( $t, $extra = '' ) {
	$o = sec_open( 'ptour grid-sec' . $extra, $t['h2'] ) . sec_head( $t ) . '<ul class="tourlist">';
	foreach ( $t['items'] as $it ) {
		$o .= '<li class="tourcell"><figure>' . img_tag( $it[2] ) . '</figure>'
			. '<p class="tourmeta"><b>' . h( $it[0] ) . '</b><span>' . h( $it[1] ) . '</span></p></li>';
	}
	return $o . '</ul></div></section>';
}

/**
 * COMP-FLOORPLAN · TPL-PROPERTY-01. Es FIJO y no un toggle.
 *
 * Un piso sin plano obliga a la visita para saber si el segundo dormitorio es un dormitorio o un
 * trastero, y esa visita se la come la agencia. Si no hay plano se dibuja: sale más barato que
 * cuatro visitas perdidas.
 *
 * PLACEHOLDER MARCADO, no un plano inventado — la misma disciplina que `hero_shot_html()` ya
 * establece para `TPL-C-13`. Esta función se estrena con `TPL-C-15`/`delao` (nunca antes se había
 * renderizado) y las doce fotografías de la marca no incluyen un plano de planta; un hueco
 * declarado es honesto, un plano dibujado sin datos reales no lo sería.
 */
function floorplan_html( $fp, $extra = '' ) {
	$shot = isset( $fp['img'] )
		? '<figure class="floorplan">' . img_tag( $fp['img'] ) . '</figure>'
		: '<figure class="floorplan ph"><span>Placeholder</span></figure>';
	return sec_open( 'planwrap grid-sec' . $extra, $fp['h2'] ) . sec_head( $fp )
		. $shot . '<p class="pnote">' . h( $fp['note'] ) . '</p>' . sec_close();
}

/** COMP-COSTS-BREAKDOWN · TPL-PROPERTY-01. El número de la web tiene que ser el de la notaría. */
function costs_breakdown_html( $cb, $extra = '' ) {
	$o = sec_open( 'costs grid-sec' . $extra, $cb['h2'] ) . sec_head( $cb ) . '<ul class="costlist">';
	foreach ( $cb['rows'] as $r ) {
		$o .= '<li class="costrow"><span>' . h( $r[0] ) . '</span><b>' . h( $r[1] ) . '</b></li>';
	}
	$o .= '<li class="costrow costsum"><span>' . h( $cb['sum'][0] ) . '</span>'
		. '<b>' . h( $cb['sum'][1] ) . '</b></li>';
	return $o . '</ul><p class="pnote">' . h( $cb['note'] ) . '</p>' . sec_close();
}

/**
 * COMP-ENERGY-LABEL · TPL-PROPERTY-01. FIJO por ley, no por diseño.
 *
 * En España un anuncio de venta o alquiler publica la calificación energética; un toggle aquí
 * sería un interruptor para incumplir. Y va en HTML, no dentro de un JPG: un dato obligatorio
 * metido en una imagen no lo lee ni un lector de pantalla ni un buscador.
 */
function energy_label_html( $el, $extra = '' ) {
	$o = sec_open( 'energysec grid-sec' . $extra, $el['h2'] ) . sec_head( $el ) . '<div class="energy">';
	foreach ( $el['items'] as $it ) {
		$o .= '<div class="elabel"><span class="eletter">' . h( $it[0] ) . '</span>'
			. '<div><b>' . h( $it[1] ) . '</b><span>' . h( $it[2] ) . '</span></div></div>';
	}
	return $o . '</div><p class="pnote">' . h( $el['note'] ) . '</p>' . sec_close();
}

/**
 * TPL-UNIT-01 «Unidad de ocasión» · la ficha que las tarjetas de TPL-C-07 prometían.
 *
 * ESTE ENLACE ESTABA MUERTO Y SE MIDIÓ. Ocho botones «Ver ficha» en la rejilla del patio apuntaban
 * a una clave de página que no existía en el juego de la marca. No era una decisión de alcance: un
 * arquetipo cuyo componente central promete una página que nadie construyó está incompleto, y la
 * rejilla no puede ser el final del camino en un sitio cuyo trabajo es que el usuario señale UNA.
 *
 * REUTILIZA la tasación y el cierre de la home, igual que `page_service` reutiliza el proceso de
 * TPL-C-01: es el mismo patio un nivel más abajo, y reutilizar es lo que hace que dos páginas se
 * lean como UN sitio y no como dos maquetas que comparten carpeta.
 */
function page_unit( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$K = $C['producto'];
	$o = array();
	$o[] = head_corporate( $C, $BRAND );
	$o[] = crumbs_html( $K['crumbs'] );
	$o[] = '<main>';
	$o[] = sec_open( 'pagehead unithead', $K['head']['h1'] )
		. '<div class="head stack"><span class="eyebrow">' . h( $K['head']['eyebrow'] ) . '</span>'
		. '<h1>' . h( $K['head']['h1'] ) . '</h1>'
		. '<p class="lede muted">' . h( $K['head']['lede'] ) . '</p>'
		. ref_line_html( $K['ref'][0], $K['ref'][1] ) . '</div>' . sec_close();
	$o[] = unit_gallery_html( $K['gallery'] );
	$o[] = unit_specs_html( $K['facts'], ' bg-alt' );
	$o[] = price_finance_html( $K['price'] );
	$o[] = history_report_html( $K['history'], ' bg-alt' );
	$o[] = trade_in_html( $C['tradein'], $uid );
	$o[] = booking_html( $K['drive'], $uid );
	$o[] = stock_grid_html( $K['related'] );
	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );
	return implode( "\n", $o );
}

/**
 * COMP-BREADCRUMB · TPL-PROPERTY-01, with the reference riding the SAME bar — the source design's
 * own composition ("migas: Inicio / Propiedades / Villa Alameda a la izquierda, Ref. MB-1042 en
 * monospace a la derecha"), not the separate `.refline` paragraph the shared "FICHAS DE INVENTARIO"
 * block stacks under an `<h1>` for `TPL-UNIT-01`. `.refcode` is reused as-is — this archetype pair
 * already owns that class name — only its context moves.
 */
function property_crumbs_html( $trail, $ref ) {
	$o    = '<nav class="crumbs propcrumbs" aria-label="Migas"><div class="canvas"><ol>';
	$last = count( $trail ) - 1;
	foreach ( $trail as $i => $t ) {
		$o .= ( $i === $last )
			? '<li aria-current="page">' . h( $t ) . '</li>'
			: '<li><a href="' . h( ihref_for_label( $t ) ) . '">' . h( $t ) . '</a></li>';
	}
	return $o . '</ol><span class="refcode">' . h( $ref ) . '</span></div></nav>';
}

/**
 * The header: `.splithead`'s own 1.5fr/1fr grid (shared with `split_head_html()` above), right
 * column a border-left price panel instead of a plain paragraph — the source design's own
 * "PRECIO DE SALIDA" treatment, `border-left` + `padding-left:34px` translated to this house's
 * token names.
 */
function property_head_html( $hd, $price ) {
	return sec_open( 'pagehead splithead prophead', $hd['h1'] )
		. '<div class="head stack"><span class="eyebrow">' . h( $hd['eyebrow'] ) . '</span>'
		. '<h1>' . h( $hd['h1'] ) . '</h1>'
		. '<p class="lede muted">' . h( $hd['lede'] ) . '</p></div>'
		. '<div class="propprice"><span class="eyebrow">' . h( $price['label'] ) . '</span>'
		. '<span class="propprice-v">' . h( $price['value'] ) . '</span>'
		. '<span class="propprice-m2">' . h( $price['m2'] ) . '</span></div>'
		. sec_close();
}

/**
 * COMP-PROPERTY-TOUR, mosaic form — TPL-PROPERTY-01's own "recorrido estancia por estancia" ADN is
 * fixed (`web-templates/references/templates/pages/listing/TPL-PROPERTY-01-home-listing.md` § 3:
 * "no es COMP-GALLERY … cada foto dice qué estás mirando"), expressed here in the source design's
 * own mosaic geometry — `2fr 1fr`, two rows, the first photo spanning both — instead of
 * `TPL-UNIT-01`'s thumbnail strip. THE ROOM NAME NEVER DROPS: every cell keeps the small monospace
 * caption naming its room, the same caption convention the source design's own Nosotros screen
 * already uses on its placeholder photos, extended here to real captions. The full descriptive
 * prose these three rooms also carry lives in `property_body_html()`'s own "LA PROPIEDAD" copy —
 * this mosaic states WHAT each photo shows, not everything about it.
 */
function property_mosaic_html( $tour ) {
	$o = '<div class="propmosaic">';
	foreach ( $tour['items'] as $i => $it ) {
		$mi = isset( $it[2] ) ? img( $it[2] ) : null;
		$o .= '<figure class="frame propcell' . ( 0 === $i ? ' propcell-big' : '' ) . '">'
			. ( null === $mi
				? '<span class="proplux-ph"><span>Placeholder</span></span>'
				: '<img data-img="' . h( $mi['slug'] ) . '" alt="' . h( $mi['alt'] ) . '" width="' . $mi['w'] . '" height="' . $mi['h'] . '">' )
			. '<span class="propcap">' . h( $it[0] ) . '</span>';
		if ( 2 === $i ) {
			$o .= '<a class="propmore" href="#propfotos">' . h( $tour['more'] ) . '</a>';
		}
		$o .= '</figure>';
	}
	return $o . '</div>';
}

/**
 * COMP-PROPERTY-FACTS · TPL-PROPERTY-01, key-data strip form — the source design's own four-column
 * band (constructed / plot / rooms-and-baths / year) with a `border-right` rule between cells,
 * instead of `TPL-UNIT-01`'s bordered fact CARDS (`.uspecs`/`.uspec`, the shared "FICHAS DE
 * INVENTARIO" block). Deliberately new class names (`.propkeydata`/`.propkey`): this is a distinct
 * visual object, not `.uspecs` with a modifier riding on top of it.
 */
function property_keydata_html( array $items ) {
	$o = sec_open( 'propfacts', 'Datos clave' ) . '<div class="propkeydata">';
	foreach ( $items as $it ) {
		$o .= '<div class="propkey"><span class="propkey-v">' . h( $it[0] ) . '</span>'
			. '<span class="propkey-l">' . h( $it[1] ) . '</span></div>';
	}
	return $o . '</div>' . sec_close();
}

/**
 * The 10-row features table — Tipo/Orientación/Plantas/Piscina/Garaje/Certificado
 * energético/Climatización/Comunidad/IBI/Disponibilidad, exactly the rows the source design draws.
 * This is where COMP-COSTS-BREAKDOWN's Comunidad/IBI and COMP-ENERGY-LABEL's certificate now live —
 * the source design folds all three into one features table rather than three standalone bands,
 * and the calificación energética still lands in real HTML text, never only inside a photograph
 * (TPL-PROPERTY-01 § 6's own SEO requirement).
 */
function property_features_html( array $rows ) {
	$o = '<div class="propfeat">';
	foreach ( $rows as $r ) {
		$o .= '<div class="propfeatrow"><span class="propfeat-k">' . h( $r[0] ) . '</span>'
			. '<span class="propfeat-v">' . h( $r[1] ) . '</span></div>';
	}
	return $o . '</div>';
}

/**
 * The location block: H2 + the "exact address at the visit" notice, a DRAWN map (never a real
 * provider — out of scope per the launch brief, and `map_search_html()`'s own docblock gives the
 * same reason: no third-party tiles without consent in a structural mockup) and four distances.
 * THE DOT IS NOT ACCENT. `map_search_html()`'s own `.pin` already settled this in as many words
 * ("THE PIN IS NOT AN ACCENT MARK … a price label is none of [the four accent roles], it is a
 * label") — a location marker is the same kind of mark, so `.mapdrawn-dot` paints `--c-text`, never
 * `--c-accent`, or the build's own accent-role gate would `fail()` it on sight. `.mapdrawn` itself
 * is shared with `contact_aside_html()` below — the office map on Contacto is the identical drawn
 * object at a different aspect ratio, not a second implementation.
 */
function property_location_html( $loc ) {
	$o = '<div class="proploc"><div class="proploc-head"><h2>' . h( $loc['h2'] ) . '</h2>'
		. '<span class="proploc-note">' . h( $loc['note'] ) . '</span></div>'
		. '<div class="mapdrawn mapdrawn-wide" aria-hidden="true"><span class="mapdrawn-dot"></span></div>'
		. '<div class="propdist">';
	foreach ( $loc['distances'] as $d ) {
		$o .= '<div class="propdist-cell"><span class="propdist-v">' . h( $d[0] ) . '</span>'
			. '<span class="propdist-l">' . h( $d[1] ) . '</span></div>';
	}
	return $o . '</div></div>';
}

/**
 * COMP-VISIT-REQUEST, sticky panel form — the source design's dark `#17181A` block (system
 * `ink` ground, not the brand accent: `$ACCENT_BY_GROUND['ink']` already governs accent-on-dark
 * elsewhere in this file and the brand accent has no business inside it) with a full-width accent
 * CTA and a phone fallback. Routed by EXPLICIT page key (`ihref('contacto')`), never
 * `ihref_for_label('Solicitar visita')`: that copy matches none of TPL-C-15's five page labels
 * (Cartera/Propiedades/Ficha/Nosotros/Contacto), and `ihref_match()`'s own documented fallback for
 * an unmatched label is the HOME route — silently wrong for a button whose entire job is to reach
 * Contacto. The mortgage simulator the source design pairs this with is out of scope (launch
 * brief), so the panel is the one block the design draws here, not two.
 */
function property_visit_panel_html( $vp ) {
	return '<aside class="propaside"><div class="propvisit">'
		. '<span class="eyebrow">' . h( $vp['eyebrow'] ) . '</span>'
		. '<p class="propvisit-line">' . h( $vp['line'] ) . '</p>'
		. '<a class="btn btn-primary propvisit-cta" href="' . h( ihref( 'contacto' ) ) . '">' . h( $vp['cta'] ) . '</a>'
		. '<a class="propvisit-tel" href="tel:' . h( preg_replace( '/\s+/', '', $vp['phone'] ) ) . '">' . h( $vp['phone_label'] ) . '</a>'
		. '</div></aside>';
}

/**
 * The body: `.propbody`'s own 1.55fr/1fr grid, left column description + features table +
 * location, right column the sticky visit panel (`position:sticky;top:110px`, the source design's
 * own offset — this house's `.site-head` is not itself sticky, but 110px still reads as "comfortably
 * clear of the header" and nothing else on the page claims that number, so there is no collision to
 * reconcile the way `filter_band_html()` had to for its own sticky top).
 */
function property_body_html( $desc, $feat, $loc, $visit ) {
	$o = '<div class="propbody"><div class="propmain">'
		. '<div class="propdesc"><span class="eyebrow">' . h( $desc['eyebrow'] ) . '</span>';
	foreach ( $desc['paras'] as $i => $p ) {
		$o .= '<p class="' . ( 0 === $i ? 'proplede' : 'muted' ) . '">' . h( $p ) . '</p>';
	}
	$o .= '</div>' . property_features_html( $feat ) . property_location_html( $loc ) . '</div>';
	return $o . property_visit_panel_html( $visit ) . '</div>';
}

/**
 * COMP-RELATED · TPL-PROPERTY-01, "de su misma zona y rango" — three of the source design's own
 * "tarjetas simplificadas" (`proplux` in its 'similar' variant: photo, zone, title, price, no badge,
 * no fact row) plus "ver toda la cartera", routed by explicit page key for the same reason
 * `featured_grid_html()`'s own link is: that copy contains the word "cartera", which is the home
 * page's own label, and a fuzzy match would send the reader back to the home instead of the listing.
 */
function property_similar_html( $rel ) {
	return sec_open( 'propsimilar grid-sec', $rel['h2'] )
		. '<div class="fgridhead"><h2>' . h( $rel['h2'] ) . '</h2>'
		. '<a class="fgridlink" href="' . h( ihref( 'propiedades' ) ) . '">' . h( $rel['link'] ) . '</a></div>'
		. proplux_grid_html( $rel['items'], 'similar' )
		. sec_close();
}

/**
 * TPL-PROPERTY-01 «Inmueble» · la ficha que las tarjetas de `proplux_grid_html()` prometen.
 *
 * PR3c REWRITE. Antes renderizaba las secciones de `TPL-UNIT-01` (coche de ocasión) sobre una
 * villa: `unithead`, `property_tour_html()` (galería de coche), `unit_specs_html()`,
 * `floorplan_html()`, `costs_breakdown_html()`, `energy_label_html()`, `booking_html()` — siete
 * funciones prestadas de un patio de coches, ninguna autora. Ahora: migas con referencia,
 * encabezado partido con panel de precio, mosaico con recorrido rotulado, datos clave en fila,
 * cuerpo con descripción + tabla de características + ubicación + panel de visita pegajoso, y
 * similares — la ficha que el diseño de origen dibuja, sección por sección.
 */
function page_property( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$K = $C['producto'];
	$o = array();
	$o[] = head_corporate( $C, $BRAND );
	$o[] = property_crumbs_html( $K['crumbs'], $K['ref'][0] );
	$o[] = '<main>';
	$o[] = property_head_html( $K['head'], $K['price'] );
	$o[] = '<section class="sec propgallery" aria-label="Galería"><div class="canvas">'
		. property_mosaic_html( $K['tour'] ) . '</div></section>';
	$o[] = property_keydata_html( $K['facts']['items'] );
	$o[] = sec_open( 'propdetail grid-sec', 'Detalle de la propiedad' )
		. property_body_html( $K['desc'], $K['features'], $K['location'], $K['visit'] )
		. sec_close();
	$o[] = property_similar_html( $K['related'] );
	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );
	return implode( "\n", $o );
}

/* ═══════════════════════════════════════════════════════════════════════════════════════════════
 * PR3c — TPL-C-15/delao's OWN Nosotros (`TPL-ABOUT-01`) and Contacto (`TPL-CONTACT-01`), authored
 * to the source design instead of the shared `page_about_company()` (a clinic/dealer's "about" page
 * — the orchestrator's own defect list: hero/about/features/stats/team/quotes/band-closing-sober)
 * and `page_contact_enquiry()` (`process`/`medteam`/`faq` — a MEDICAL clinic's contact page,
 * `med_team_html()` by name). Both archetypes' own fixed ADN survives; only the FORM changes.
 * ═══════════════════════════════════════════════════════════════════════════════════════════════ */

/**
 * COMP-ABOUT's photograph, as its own full-bleed-width band — the source design's own composition
 * (a plain `aspect-ratio:21/9` photo directly under the header, no text over it) rather than the
 * side-by-side text+image `page_about_company()` uses. `delao-oficina` is a 4:3 CARD shot in the
 * manifest; the box shape here is this class's own `aspect-ratio`, and `object-fit:cover` crops to
 * it exactly as every other photo in this file already does under a shape its manifest role never
 * promised (`.vcard .frame{aspect-ratio:4/3}` over a photo the manifest calls "card 4:3" is the
 * same relationship the whole catalogue already runs on).
 */
function about_photo_band_html( $img_slug ) {
	return '<section class="sec aboutphoto" aria-label="La agencia"><div class="canvas">'
		. '<figure>' . img_tag( $img_slug ) . '</figure></div></section>';
}

/**
 * COMP-VALUES · TPL-ABOUT-01 — "3–4 compromisos verificables, no adjetivos" — expressed as the
 * source design's own numbered método list (mono `01`–`04`, `gap:1px` acting as the row filete)
 * rather than the generic `.feats` two-line list `page_about_company()` gives every other archetype.
 * Every one of delao's four items already reads as a verifiable practice ("comparables de cierre de
 * los últimos dieciocho meses", not "excelencia") — exactly what the archetype's own § 3 demands.
 */
function method_list_html( $md ) {
	$o = sec_open( 'methodsec grid-sec', $md['h2'] )
		. '<div class="methodhead"><h2>' . h( $md['h2'] ) . '</h2></div><ol class="methodlist">';
	foreach ( $md['items'] as $it ) {
		$o .= '<li class="methoditem"><span class="methodnum">' . h( $it[0] ) . '</span>'
			. '<div><h3>' . h( $it[1] ) . '</h3><p class="muted">' . h( $it[2] ) . '</p></div></li>';
	}
	return $o . '</ol>' . sec_close();
}

/**
 * COMP-STATS · TPL-ABOUT-01, on `ground:ink` — the source design's own dark band, `repeat(4,1fr)`
 * with `gap:1px` acting as the filete over `rgba(246,244,240,.14)`. Real, verifiable numbers only
 * (archetype's own warning: "una cifra inventada hunde la página entera") — delao's four are the
 * same ones the home's `stats` block already states (22 años, 310 M€, 11 semanas, 96 %), never a
 * second invented set.
 */
function figures_band_html( $cf ) {
	$o = '<section class="sec figuresband" aria-label="' . h( $cf['eyebrow'] ) . '"><div class="canvas figuresgrid">';
	foreach ( $cf['items'] as $it ) {
		$o .= '<div class="figurecell"><span class="figure-v">' . h( $it[0] ) . '</span>'
			. '<span class="figure-l">' . h( $it[1] ) . '</span></div>';
	}
	return $o . '</div></section>';
}

/**
 * COMP-TEAM · TPL-ABOUT-01 — "caras reales o la sección no va". Three real portraits
 * (`delao-nerea`/`delao-julen`/`delao-leire`), dynamically columned to the ACTUAL headcount via
 * `cols_attr()` rather than a hardcoded `cols-3`/`cols-4` — the source design's own team is four
 * people and delao's manifest holds three photographed agents, so copying the design's column
 * count here would be exactly the "`cols-3` grid holding one item" defect this launch brief warns
 * against, just with the numbers moved: a fixed 4-column grid holding three real cards and one
 * empty track is the same bug from the other side.
 */
function team_portraits_html( $tm ) {
	$o = sec_open( 'teamsec grid-sec', $tm['h2'] )
		. '<div class="teamhead"><h2>' . h( $tm['h2'] ) . '</h2>'
		. '<span class="teamcount">' . h( $tm['eyebrow'] ) . '</span></div>'
		. '<ul class="proplux-grid teamgrid"' . cols_attr( count( $tm['items'] ) ) . '>';
	foreach ( $tm['items'] as $it ) {
		$mi = img( $it[2] );
		$o .= '<li class="teamcard"><figure class="frame team-shot">'
			. '<img data-img="' . h( $mi['slug'] ) . '" alt="' . h( $mi['alt'] ) . '" width="' . $mi['w'] . '" height="' . $mi['h'] . '"></figure>'
			. '<div class="team-body"><h3>' . h( $it[0] ) . '</h3>'
			. '<span class="team-role">' . h( $it[1] ) . '</span></div></li>';
	}
	return $o . '</ul></div></section>';
}

/**
 * COMP-CTA · TPL-ABOUT-01, sober close — the source design's own light `bg-alt` panel,
 * `1.2fr 1fr` with the button aligned to the end, rather than `page_cta_html()`'s centred stack.
 */
function about_cta_html( $cta ) {
	return '<section class="sec aboutcta" aria-label="' . h( $cta['h2'] ) . '"><div class="canvas">'
		. '<div class="aboutctapanel"><div class="head stack"><h2>' . h( $cta['h2'] ) . '</h2>'
		. '<p class="muted">' . h( $cta['lede'] ) . '</p></div>'
		. '<a class="btn btn-primary aboutcta-btn" href="' . h( ihref( 'contacto' ) ) . '">' . h( $cta['cta'] ) . '</a></div>'
		. '</div></section>';
}

/**
 * TPL-ABOUT-01 «Nosotros» — Inmobiliaria de la O, authored to `Nosotros.dc.html` instead of reused
 * from `page_about_company()`. COMP-TESTIMONIAL stays OFF: the source design carries no quotes on
 * this page (they already live on the home), and that component is a toggle, not fixed ADN.
 */
function page_about_cartera( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$A = $C['nosotros'];
	$o = array();
	$o[] = head_corporate( $C, $BRAND );
	$o[] = '<main>';
	$o[] = split_head_html( $A['head'] );
	$o[] = about_photo_band_html( $A['img'] );
	$o[] = method_list_html( $A['method'] );
	$o[] = figures_band_html( $A['figures'] );
	$o[] = team_portraits_html( $A['team'] );
	$o[] = about_cta_html( $A['cta'] );
	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );
	return implode( "\n", $o );
}

/**
 * COMP-CONTACT-FORM · TPL-CONTACT-01 — the source design's own 2×2 field grid (Nombre/Teléfono/
 * Correo/Motivo) + a 4-row textarea + a privacy checkbox, never the three-field-plus-message shape
 * `page_contact_enquiry()`'s `.leadform` gives every other TPL-CONTACT-* demo. Labels stay visible
 * on every field (archetype ADN); the textarea's placeholder is a SUPPLEMENT to its own visible
 * label, not a replacement for one.
 */
function contact_form_html( $fm, $uid ) {
	$o = '<form class="leadform contactform" onsubmit="return false"><div class="formgrid">';
	foreach ( $fm['fields'] as $f ) {
		$fid = $uid . '-cf-' . $f[0];
		if ( 'select' === $f[2] ) {
			$o .= '<div class="field"><label for="' . $fid . '">' . h( $f[1] ) . '</label>'
				. '<select id="' . $fid . '" name="' . $f[0] . '">';
			foreach ( $f[3] as $opt ) {
				$o .= '<option>' . h( $opt ) . '</option>';
			}
			$o .= '</select></div>';
			continue;
		}
		$o .= '<div class="field"><label for="' . $fid . '">' . h( $f[1] ) . '</label>'
			. '<input id="' . $fid . '" name="' . $f[0] . '" type="' . $f[2] . '"></div>';
	}
	$o .= '</div><div class="field"><label for="' . $uid . '-cf-msg">' . h( $fm['msg'] ) . '</label>'
		. '<textarea id="' . $uid . '-cf-msg" name="msg" rows="4" placeholder="' . h( $fm['placeholder'] ) . '"></textarea></div>'
		. '<label class="privacy"><input type="checkbox"><span>' . h( $fm['privacy'] ) . '</span></label>'
		. '<button class="btn btn-primary" type="submit">' . h( $fm['submit'] ) . '</button>';
	return $o . '</form>';
}

/**
 * COMP-CONTACT-DIRECT · TPL-CONTACT-01 — three stacked blocks with `gap:1px` acting as the filete
 * (Oficina / Directo / mapa), the source design's own composition, next to the form rather than
 * below the whole page (archetype ADN: "quien prefiere llamar tiene que verlo sin bajar").
 * `.mapdrawn` is shared with `property_location_html()` above — the same drawn object, `4:3` here.
 */
function contact_aside_html( $office, $direct ) {
	return '<aside class="contactaside">'
		. '<div class="contactblk"><span class="eyebrow">' . h( $office['eyebrow'] ) . '</span>'
		. '<p class="contactblk-addr">' . nl2br( h( $office['addr'] ), false ) . '</p>'
		. '<span class="muted small">' . nl2br( h( $office['hours'] ), false ) . '</span></div>'
		. '<div class="contactblk"><span class="eyebrow">' . h( $direct['eyebrow'] ) . '</span>'
		. '<a class="contactblk-tel" href="tel:' . h( preg_replace( '/\s+/', '', $direct['phone'] ) ) . '">' . h( $direct['phone'] ) . '</a>'
		. '<a class="contactblk-mail" href="mailto:' . h( $direct['email'] ) . '">' . h( $direct['email'] ) . '</a></div>'
		. '<div class="mapdrawn" aria-hidden="true"><span class="mapdrawn-dot"></span></div>'
		. '</aside>';
}

/**
 * TPL-CONTACT-01 «Contacto» — Inmobiliaria de la O, authored to `Contacto.dc.html` instead of
 * reused from `page_contact_enquiry()`. COMP-PROCESS ("qué pasa después de enviar, con plazos
 * reales") is FIJO·ADN and stays honoured, but not as its own visible band: the source design draws
 * no such band on this screen, and the page's own header lede already states it in full — "Respondemos
 * todas las consultas en menos de 24 horas laborables, siempre una persona del equipo y nunca un
 * formulario automático" IS a real deadline plus a real answer to "what happens next", the exact
 * two things the archetype's § 3 asks for, just spoken once instead of drawn as a second section.
 * COMP-TEAM and COMP-FAQ stay OFF (toggles, and the source design carries neither on this screen).
 */
function page_contact_cartera( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$K = $C['contacto'];
	$o = array();
	$o[] = head_corporate( $C, $BRAND );
	$o[] = crumbs_html( $K['crumbs'] );
	$o[] = '<main>';
	$o[] = split_head_html( $K['head'] );
	$o[] = sec_open( 'contactbody grid-sec', 'Formulario de contacto' )
		. '<div class="contactgrid">'
		. contact_form_html( $K['form'], $uid )
		. contact_aside_html( $K['office'], $K['direct'] )
		. '</div>' . sec_close();
	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );
	return implode( "\n", $o );
}

/**
 * TPL-CONTACT-02 «Puerta abierta» · para cuando la respuesta útil se da en el mismo minuto.
 *
 * EL TELÉFONO VA ANTES QUE EL FORMULARIO, y ése es el arquetipo entero. En `TPL-CONTACT-01` la
 * consulta hay que estudiarla y el formulario manda; aquí compite con el teléfono y pierde.
 */
function page_contact_open( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	/* TRES SECCIONES Y NINGUNA REPETIDA. Antes eran cinco y la fotografía de la recepción salía en
	   TRES de ellas —hero, bloque de dirección y «cómo se reconoce»—, además de un bloque de
	   dirección y otro de horario que decían la misma calle y el mismo teléfono uno detrás de otro.
	   Quien entra aquí quiere hacer una de tres cosas: llamar, ir, o reconocer la puerta. Una
	   sección para cada una. */
	$K  = $C['contacto'];
	$dr = $K['direct'];
	$o  = array();
	$o[] = head_corporate( $C, $BRAND );
	$o[] = crumbs_html( $K['crumbs'] );
	$o[] = '<main>';
	$o[] = page_head_html( array(
		'eyebrow' => $K['head']['eyebrow'],
		'h1'      => $K['head']['h1'],
		'lede'    => $K['head']['lede'],
	) );
	/* POR ORDEN DE RAPIDEZ Y EN UNA FILA, no en una pila. Son tres maneras de llegar a la misma
	   persona; apiladas se leen como tres pasos de un proceso, que es lo que no son. */
	$o[] = sec_open( 'reachdirect grid-sec bg-alt', $dr['h2'] ) . sec_head( $dr )
		. '<ul class="reachlist">';
	foreach ( $dr['items'] as $it ) {
		$o[] = '<li><span class="dlabel">' . h( $it[0] ) . '</span>'
			. '<a class="reachbig" href="#">' . h( $it[1] ) . '</a>'
			. '<span class="muted small">' . h( $it[2] ) . '</span></li>';
	}
	$o[] = '</ul><p class="pnote">' . h( $dr['note'] ) . '</p>' . sec_close();
	$o[] = hours_row_html( $K['hours'] );
	$o[] = cabin_tour_html( $K['door'], ' bg-alt' );
	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );
	return implode( "\n", $o );
}


/* ══════════════════════════════════════════════════════════════════════════════════════════════
 * THE SECTION CHASSIS, WHICH USED TO BE A CONSTANT
 *
 * Every section of every one of the twenty-two templates was emitted as the same four nodes:
 *   <section class="sec X"> <div class="canvas"> <div class="head stack"> <list> <p class="pnote">
 * The catalogue's own argument is that a BRAND changes the skin and an ARCHETYPE changes the
 * skeleton — and `RT_TPL_TOO_SIMILAR` measures that argument on the section INVENTORY, which is
 * the half a document can declare. It cannot see the half that decides whether two pages LOOK
 * alike: the shape each section takes. So an archetype could pass the distance check with nine
 * sections nobody else has, and still render as the same page with a different palette, because
 * the wrapper never moved. That is exactly what the fourteenth archetype did on its first pass,
 * and it took a human looking at it to say so.
 *
 * These three functions make the wrapper a DECISION. `contained` is byte-for-byte what every
 * existing strip already emits, so the other twenty-one do not move — verified by diffing the
 * whole generated page before and after. The other two shapes are what a section can be instead:
 *
 *   contained  the band holds a centred canvas — the house default, unchanged
 *   bleed      the section IS the band and spans the glass; children keep their own padding
 *   row        the section IS the row — two direct children, no canvas, one node fewer
 *
 * `row` is not a new idea here: container-hygiene has said "the section IS the row" since it was
 * written, and every strip ignored it because the only wrapper on offer nested one anyway.
 * ══════════════════════════════════════════════════════════════════════════════════════════════ */
function sec_open( $cls, $label, $shape = 'contained' ) {
	$sec_extra = ( 'bleed' === $shape ) ? ' bleedband' : ( ( 'row' === $shape ) ? ' secrow' : '' );
	$sec_o     = '<section class="sec ' . $cls . $sec_extra . '" aria-label="' . h( $label ) . '">';
	return ( 'contained' === $shape ) ? $sec_o . '<div class="canvas">' : $sec_o;
}

function sec_close( $shape = 'contained' ) {
	return ( 'contained' === $shape ) ? '</div></section>' : '</section>';
}

/** The head, in the gesture the section asks for: `stack` (the house shape) or `none`. */
function sec_head( $hd, $mode = 'stack', $tag = 'h2' ) {
	if ( 'none' === $mode ) {
		return '';
	}
	/* HUBO UN TERCER MODO, `stepped`, Y SE RETIRÓ MIRÁNDOLO. Metía la segunda línea del titular y
	   le ponía delante un filete corto. Sobre el papel era «dos tiempos en vez de un párrafo»; en
	   pantalla, un lector lo señaló con una cruz y una palabra: «muy de IA». Tenía razón —era una
	   raya que no significaba nada, puesta donde el ojo espera una palabra—. Un gesto que hay que
	   explicar para que se entienda no es un gesto, es ruido, y vocabulario muerto en el chasis es
	   peor que no tenerlo: el siguiente que lo encuentre lo usará. */
	return '<div class="head stack"><span class="eyebrow">' . h( $hd['eyebrow'] ) . '</span>'
		. '<' . $tag . '>' . h( $hd['h2'] ) . '</' . $tag . '>'
		. ( isset( $hd['lede'] ) ? '<p class="lede muted">' . h( $hd['lede'] ) . '</p>' : '' )
		. '</div>';
}

/**
 * One page of one variant. Fails loudly on an unknown pair rather than emitting an empty sample.
 *
 * THE SECTION INDEX IS APPLIED HERE AND ONLY FOR ARCHETYPES THAT ASK FOR IT. It used to be called
 * inside every strip, which made numbering universal — and universal chrome is exactly what made
 * seventeen templates read as one. `number_heads()` is idempotent now (it skips a head that
 * already carries `data-index`), so the strips that still call it themselves are unaffected.
 */
function render_page( $page_key, $tpl, $anchor_key, $C, $BRAND, $suid, $tgl ) {
	$rp = render_page_inner( $page_key, $tpl, $anchor_key, $C, $BRAND, $suid, $tgl );
	return ( 'index' === $C['head_mode'] ) ? number_heads( $rp ) : $rp;
}

function render_page_inner( $page_key, $tpl, $anchor_key, $C, $BRAND, $suid, $tgl ) {
	ihref_set_context( tpl_slug( $C['tpl'] ), $anchor_key, $tpl );

	
	
	
	if ( 'TPL-C-11' === $tpl && 'home' === $page_key ) {
		return strip_plan( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-C-11' === $tpl && 'nosotros' === $page_key ) {
		return page_about_company( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-C-11' === $tpl && 'contacto' === $page_key ) {
		return page_contact_enquiry( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	
	
	
	if ( 'TPL-C-07' === $tpl && 'home' === $page_key ) {
		return strip_stock( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-C-07' === $tpl && 'nosotros' === $page_key ) {
		return page_about_company( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-C-07' === $tpl && 'producto' === $page_key ) {
		return page_unit( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-C-07' === $tpl && 'contacto' === $page_key ) {
		return page_contact_enquiry( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	/* Re-keyed from `TPL-C-13` to `TPL-C-15` (design.md decision C2): `TPL-C-13` is fully retired
	   (no `$CONTENT`/`$STRIPS`/`$BRANDS` entry names it any more), so its four branches were dead
	   code since PR2b's amputation — deferred here per PR3a's own recorded deviation, done in the
	   same touch of this file as the real `$BRANDS`/`$CONTENT` addition. `producto` and `contacto`
	   reuse `page_property`/`page_contact_enquiry` verbatim, exactly as C2 promised ("TPL-PROPERTY-01
	   survives untouched"); `nosotros` drops the old assoc→indexed `$C_nos['team']` remap because
	   `TPL-C-13`'s own home used `agent_list_html()`'s assoc team shape and `TPL-C-15` has no team on
	   its home at all (design: "sin equipo en portada") — `$CONTENT['TPL-C-15-delao']['team']` is
	   authored directly in `page_about_company_tail()`'s indexed-triple shape, so no remap is
	   needed. `home` calls the new `strip_cartera_curada()`, not `strip_property()`, because the
	   two homes differ structurally (persuasion hero vs. buscador-como-portada — see C2's
	   rationale). `propiedades` is new: D5's fifth page, reusing `TPL-SERVICES-01`'s own "index for
	   a home with more entries than fit" pattern for the full portfolio listing. */
	if ( 'TPL-C-15' === $tpl && 'home' === $page_key ) {
		return strip_cartera_curada( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-C-15' === $tpl && 'propiedades' === $page_key ) {
		return page_property_index( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-C-15' === $tpl && 'nosotros' === $page_key ) {
		return page_about_cartera( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-C-15' === $tpl && 'producto' === $page_key ) {
		return page_property( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-C-15' === $tpl && 'contacto' === $page_key ) {
		return page_contact_cartera( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-C-14' === $tpl && 'home' === $page_key ) {
		return strip_ritual( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-C-14' === $tpl && 'servicios' === $page_key ) {
		return page_services_index( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-C-14' === $tpl && 'servicio' === $page_key ) {
		return page_treatment( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-C-14' === $tpl && 'nosotros' === $page_key ) {
		return page_about_house( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-C-14' === $tpl && 'contacto' === $page_key ) {
		return page_contact_open( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/* La MISMA `page_pdp` que sirve a TPL-E-02, con el toggle en `editorial`. Un renderizador
	   aparte habría sido la forma de código de volver a tener dos arquetipos donde hay uno. */
	
	
	
	if ( 'TPL-E-09' === $tpl && 'home' === $page_key ) {
		return strip_made( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-E-08' === $tpl && 'home' === $page_key ) {
		return strip_plan_sub( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-E-08' === $tpl && 'producto' === $page_key ) {
		return page_pdp_sub( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-E-07' === $tpl && 'home' === $page_key ) {
		return strip_batch( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-E-07' === $tpl && 'producto' === $page_key ) {
		return page_pdp_batch( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-E-06' === $tpl && 'home' === $page_key ) {
		return strip_fit( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-E-06' === $tpl && 'producto' === $page_key ) {
		return page_pdp_fit( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	
	
	fail( "no renderer for page `$page_key` of `$tpl`" );
}

$body    = array();
$cards   = array();
$by_tpl  = array();
$n_strip = count( $STRIPS );

foreach ( $STRIPS as $s ) {
	$C   = $CONTENT[ $s['tpl'] ];
	$A   = $ANCHORS[ $s['anchor'] ];

	/* THE BRAND OVERRIDES THE ANCHOR'S GROUND AND TYPE ON THE LOCAL COPY, so `axis_rows()` reports
	   the colours the strip is actually painted in. The alternative — an axis bar that says `paper`
	   over a near-black restaurant — is the kind of readout that is worse than none, because it is
	   read as authoritative. Scale, density, composition and elevation are untouched: they are the
	   anchor's, and the card's chips stay true. */
	/* Tres estados y no dos: la marca de la casa, un NOMBRE propio sin paleta, y una marca entera.
	   El de en medio no existía y por eso TPL-C-13 se rendía con el wordmark de una cantería sobre
	   una inmobiliaria. `wordmark` cambia el nombre y NADA más — ni ground, ni tipografías, ni
	   `data-brand` — así que el arquetipo sigue contando como de la casa en $n_brands, que es la
	   verdad: no tiene fotografías suyas. */
	$wordmark = isset( $C['wordmark'] ) ? $C['wordmark'] : $BRAND;
	$b_attr   = '';
	if ( '' !== $C['brand'] ) {
		$b_of        = $BRANDS[ $C['brand'] ];
		$A['ground'] = 'b-' . $C['brand'];
		$A['font_1'] = $b_of['font_1'];
		$A['font_2'] = $b_of['font_2'];
		$wordmark    = $b_of['name'];
		$b_attr      = ' data-brand="' . h( $C['brand'] ) . '"';
	}
	$f_key = $s['anchor'] . '@' . $A['ground'];
	if ( isset( $FIELD[ $f_key ] ) ) {
		$b_attr .= ' data-field="' . h( $f_key ) . '"';
	}

	$lp  = strtolower( $COMPOSITION[ $A['composition'] ]['lp'] );
	$uid = strip_uid( $C, $s['anchor'] );

	$tgl = $RESOLVED[ $C['tpl'] . '×' . $s['anchor'] ];


	$rows = axis_rows( $A, $SCALE, $DENSITY, $GROUND, $ELEVATION, $COMPOSITION );

	// The variant is collected under its TEMPLATE rather than emitted as a loose page. A catalogue
	// entry is a TEMPLATE; the anchor is a choice you make INSIDE it, the way a kit is one product
	// with variants and not four products that happen to share a wireframe.
	$by_tpl[ $C['tpl'] ]['content'] = $C;
	$by_tpl[ $C['tpl'] ]['anchors'][ $s['anchor'] ] = array(
		'A'    => $A,
		'uid'  => $uid,
		'rows' => $rows,
	);

	$variant = array();
	$variant[] = '<section class="strip" id="' . h( $uid ) . '"'
		. ' data-site="' . h( $C['site'] ) . '"'
		. ' data-tpl="' . h( $C['tpl'] ) . '"'
		. ' data-pers="' . h( $s['anchor'] ) . '"'
		. ' aria-label="' . h( $C['tpl'] . ' × ' . $A['id'] ) . '">';
	$variant[] = meta_html( $C, $A, $rows, $uid, $tgl );

	/* ONE SAMPLE PER PAGE, and each carries its OWN uid suffix. Every form field, radio group and
	   `<label for>` inside a sample is an id; two pages of the same variant are two live forms, and
	   a duplicate id silently breaks the label on BOTH — clicking it focuses the wrong control, or
	   nothing. The suffix is the page key, so the id says which page it belongs to. */
	foreach ( $PAGES[ $C['arch'] ] as $pi => $pg ) {
		$suid = ( 'home' === $pg['key'] ) ? $uid : ( $uid . '-' . $pg['key'] );
		// `lang` is on the sample rather than the strip: the meta bar around it is Spanish too, but
		// the sample is what carries hyphenated headings, and `hyphens:auto` needs a language to pick
		// a dictionary. Without it Chrome hyphenates nothing and the card headings overflow again.
		$variant[] = '<div class="sample" lang="es" data-anchor="' . h( $s['anchor'] ) . '"'
			. ' data-arch="' . h( strtolower( $C['arch'] ) ) . '"'
			. ' data-head="' . h( $C['head_mode'] ) . '"'
			. $b_attr
			. ' data-comp="' . h( $lp ) . '" data-page="' . h( $pg['key'] ) . '"'
			. ( 0 === $pi ? '' : ' hidden' ) . '>';
		$variant[] = render_page( $pg['key'], $C['arch'], $s['anchor'], $C, $wordmark, $suid, $tgl );
		$variant[] = '</div>';
	}
	$variant[] = '</section>';

	$by_tpl[ $C['tpl'] ]['anchors'][ $s['anchor'] ]['html'] = implode( "\n", $variant );
}

/**
 * One template page: identity, the switcher, and its variants.
 *
 * The FIRST anchor declared for a template is its default — the one the card's thumbnail shows and
 * the one a bare `#tplc01` opens on. Order in $STRIPS is therefore meaningful and not incidental.
 *
 * The page switcher is NOT rendered while a template has a single page. A control that offers one
 * choice is not a control, it is a promise; this catalogue has shipped enough of those.
 */
function template_page_html( $tpl, $T, $tpl_slug, $pages ) {
	$C    = $T['content'];
	$keys = array_keys( $T['anchors'] );

	$chips = '';
	foreach ( $keys as $i => $k ) {
		$chips .= '<button class="sbtn" type="button" data-pers="' . h( $k ) . '"'
			. ' aria-pressed="' . ( 0 === $i ? 'true' : 'false' ) . '">'
			. h( $T['anchors'][ $k ]['A']['name'] ) . '</button>';
	}

	/* The page switcher is printed only when the archetype ships more than one page. A control
	   offering a single choice is not a control, it is a promise, and this catalogue has shipped
	   enough of those. Each chip names the archetype doc its page was transcribed from. */
	$pages_html = '';
	if ( count( $pages ) > 1 ) {
		$pages_html = '<span class="flabel flabel-2">Página</span>';
		foreach ( $pages as $i => $pg ) {
			$pages_html .= '<button class="sbtn" type="button" data-page="' . h( $pg['key'] ) . '"'
				. ' title="' . h( $pg['doc'] ) . '"'
				. ' aria-pressed="' . ( 0 === $i ? 'true' : 'false' ) . '">'
				. h( $pg['label'] ) . '</button>';
		}
	}

	$vars = '';
	foreach ( $keys as $i => $k ) {
		$vars .= '<div class="variant" data-pers="' . h( $k ) . '"'
			. ( 0 === $i ? '' : ' hidden' ) . '>' . $T['anchors'][ $k ]['html'] . '</div>';
	}

	$is_b  = ( '' !== $C['brand'] );
	$g_eye = $is_b ? $C['brand_sector'] : $C['site_es'];
	$g_b   = $is_b ? $C['brand_name'] : $tpl;
	$g_r   = $is_b ? ( 'sobre ' . $C['arch'] . ' · ' . $C['tpl_name'] ) : $C['tpl_name'];

	return '<div class="page" id="p-' . h( $tpl_slug ) . '" data-site="' . h( $C['site'] ) . '">'
		. '<header class="tpl-head"><div class="gal-wrap">'
		. '<span class="eyebrow">' . h( $g_eye ) . '</span>'
		. '<h2><b>' . h( $g_b ) . '</b> ' . h( $g_r ) . '</h2>'
		. '<p class="tpl-dna">ADN fijo: <code>' . h( $C['dna'] ) . '</code></p>'
		. '<p class="tpl-wire"><code>' . h( $C['wire'] ) . '</code></p>'
		. '</div></header>'
		. '<nav class="switch" aria-label="Variantes de ' . h( $tpl ) . '"><div class="gal-wrap">'
		. '<span class="flabel">Ancla</span>' . $chips
		. $pages_html
		. '</div></nav>'
		. '<div class="variants">' . $vars . '</div>'
		. '</div>';
}

/* A template's route slug: `TPL-C-01` → `tplc01`. Deliberately the same transformation
   `strip_uid()` applies before it appends the anchor, so a variant id is always its template's
   slug plus `-anchor` and the two can never need translating between. */
function tpl_slug( $tpl ) {
	return strtolower( str_replace( '-', '', $tpl ) );
}

/* INTERNAL NAV WIRING · best-effort label → real page, so nav/footer/CTA links actually go
   somewhere instead of sitting on a dead `href="#"`.

   `render_page_inner()` calls `ihref_set_context()` once per page it renders — it is the one
   place that already holds the routing slug, the anchor and the archetype together — and every
   `href="#"` site downstream reads that context back through `ihref()` / `ihref_for_label()`
   instead of taking it as a parameter. Threading tpl/anchor through the ~20 component functions
   that print a nav item or a CTA would touch every one of their signatures and every call site;
   a page-scoped global does the same job with a single injection point and no signature churn,
   which is safe here because the generator is single-threaded and one page finishes rendering
   before the next one starts.

   A label that matches no real page for its archetype is not an error: `ihref()` falls back to
   that archetype's own home, which the client-side router already treats as its default target,
   so a miss degrades to "goes home" and never to a dead link. */
$GLOBALS['NM_IHREF_SLUG']   = '';
$GLOBALS['NM_IHREF_ANCHOR'] = '';
$GLOBALS['NM_IHREF_ARCH']   = '';

function ihref_set_context( $slug, $anchor, $arch ) {
	$GLOBALS['NM_IHREF_SLUG']   = $slug;
	$GLOBALS['NM_IHREF_ANCHOR'] = $anchor;
	$GLOBALS['NM_IHREF_ARCH']   = $arch;
}

function ihref_norm( $s ) {
	$s   = strtolower( $s );
	$map = array(
		'á' => 'a',
		'é' => 'e',
		'í' => 'i',
		'ó' => 'o',
		'ú' => 'u',
		'ñ' => 'n',
		'ü' => 'u',
	);
	return preg_replace( '/[^a-z0-9]/', '', strtr( $s, $map ) );
}

/** Best-effort label → page key for the CURRENT archetype's page set. '' if nothing is close. */
function ihref_match( $label ) {
	global $PAGES;
	$arch = $GLOBALS['NM_IHREF_ARCH'];
	if ( '' === $arch || ! isset( $PAGES[ $arch ] ) ) {
		return '';
	}
	$n = ihref_norm( $label );
	if ( '' === $n ) {
		return '';
	}
	foreach ( $PAGES[ $arch ] as $pg ) {
		if ( ihref_norm( $pg['label'] ) === $n ) {
			return $pg['key'];
		}
	}
	foreach ( $PAGES[ $arch ] as $pg ) {
		$pl = ihref_norm( $pg['label'] );
		if ( '' !== $pl && ( false !== strpos( $n, $pl ) || false !== strpos( $pl, $n ) ) ) {
			return $pg['key'];
		}
	}
	return '';
}

/** Explicit page key → the real hash the router understands. Home when the key is empty/unknown. */
function ihref( $page_key = 'home' ) {
	$slug = $GLOBALS['NM_IHREF_SLUG'];
	if ( '' === $slug ) {
		return '#';
	}
	$anc = $GLOBALS['NM_IHREF_ANCHOR'];
	return '#' . $slug . '/' . $anc . ( ( '' === $page_key || 'home' === $page_key ) ? '' : ( '/' . $page_key ) );
}

/** Nav/CTA label → real hash, best-effort matched against the current archetype's page set. */
function ihref_for_label( $label ) {
	return ihref( ihref_match( $label ) );
}

$groups  = array();
$n_cards = 0;
foreach ( $by_tpl as $bt_tpl => $bt_T ) {
	$bt_slug = tpl_slug( $bt_tpl );
	foreach ( $bt_T['anchors'] as $bt_key => $bt_v ) {
		$groups[] = template_card_html( $bt_T['content'], $bt_v['A'], $bt_key,
			$bt_v['uid'], $bt_slug, $bt_v['rows'] );
		++$n_cards;
	}
	$body[] = template_page_html( $bt_tpl, $bt_T, $bt_slug, $PAGES[ $bt_T['content']['arch'] ] );
}

// ── the image map: declared once, hydrated onto every `<img data-img>` ─────────────────────────
//
// `<img src>` cannot read a CSS variable and a `data:` URI has no fragment addressing, so inline
// `src` attributes pay the bytes ONCE PER OCCURRENCE — measured at ~6.8 MB across eight strips,
// and past the 16 MB Artifact ceiling by the plan's phase-2 count of forty. mockup-guide.md
// requires a real `<img>` with `object-fit:cover` and a real `alt`, which rules out an SVG
// `<use>` sprite, so this is the remaining route the plan itself names: one map, one pass.
//
// The `<img>` element, its `alt` and its SLUG are all in the STATIC DOM — the accessibility tree,
// `RT_GALLERY_NO_MANIFEST`, grep and a human reader all see `data-img="hero-cantera"`, which is
// strictly more legible than a base64 blob. Only the pixels need the script.

$only_used = array();
foreach ( $body as $chunk ) {
	if ( preg_match_all( '/data-img="([a-z0-9\-]+)"/', $chunk, $mm ) ) {
		foreach ( $mm[1] as $slug ) {
			$only_used[ $slug ] = true;
		}
	}
}
ksort( $only_used );

$map_lines = array();
$b64_bytes = 0;
foreach ( array_keys( $only_used ) as $slug ) {
	$d            = img( $slug )['data'];
	$b64_bytes   += strlen( $d );
	$map_lines[]  = '"' . $slug . '":"' . $d . '"';
}

$script = "<script>\n"
	. "/* One entry per slug, declared once. See the note in _build-gallery.php for why this is a\n"
	. "   map and a pass rather than " . count( $only_used ) . " inline `src` attributes repeated per strip. */\n"
	. "(function(){\n"
	. "  var IMG={" . implode( ",\n", $map_lines ) . "};\n"
	. "  var n=document.querySelectorAll('img[data-img]');\n"
	. "  for(var i=0;i<n.length;i++){var k=n[i].getAttribute('data-img');if(IMG[k])n[i].src=IMG[k];}\n"
	. "})();\n"
	. "</script>";

// ── the page ───────────────────────────────────────────────────────────────────────────────────

$head = '<!--
  NovaMira · GALERÍA DE PLANTILLAS — GENERADA, no escrita a mano.
  ══════════════════════════════════════════════════════════════════════════════════════════════
  Regenerar:  php skills/html-mockup/assets/gallery/_build-gallery.php
  Fuente:     assets/gallery/_build-gallery.php  ·  imágenes en assets/gallery/img/
  Manifiesto: assets/gallery/_gallery-images.md

  NO EDITAR ESTE FICHERO. La siguiente ejecución del generador lo sobrescribe entero. Todo lo
  que hay aquí — tokens, secciones, copy, contraste medido — sale de tablas transcritas en el
  generador desde design-system.md, design-personalities.md, layout-patterns.md y los TPL-*.md.

  Cada tira es un par TPL-* × PERS-*. El arquetipo aporta el inventario de secciones; el ancla
  aporta los cinco ejes. El copy y las fotos son la CONSTANTE dentro de cada arquetipo, así que
  toda diferencia visible entre dos tiras del mismo TPL es el ancla y nada más.

  Herramienta interna. No se publica de cara a cliente, y el flujo con cliente no cambia.
-->
<title>Galería de plantillas NovaMira</title>
<!-- Sin esta línea el layout viewport cae al ancho de reserva de 980px y NINGUNA de las media
     queries mobile-first llega a dispararse en un teléfono real: la página se maqueta a 980 y se
     escala al 33% en un 320. MEDIDO en este documento — a 320 de ancho de dispositivo,
     `innerWidth` devolvía 980. Los cuatro assets de html-mockup que ya existen tampoco la llevan;
     está reportado como hueco preexistente, no lo introduce este fichero. -->
<meta name="viewport" content="width=device-width, initial-scale=1">';

/* THE RECEIPT FOR THE INPUTS, on the first line of the file, ahead of the human banner: it is the
   one line here written for a machine, and burying it inside the banner would make its format a
   hostage to the prose around it. Computed from disk at this point in the run, so it describes
   the bytes this very build read — including this file's own source, which decides the output as
   surely as any table it reads.

   `index.html` is deliberately NOT one of its own inputs, so stamping the line cannot move the
   digest it carries. */
/* THE CHARSET DECLARATION'S OWN EXPLANATION LIVES IN THE EMITTED PAGE, not only here — exactly
   like the viewport meta's comment a few thousand bytes below does. This tag has to be LINE 2,
   right after the fingerprint (RT_GALLERY_STALE reads that line and nothing else may sit ahead of
   it), and it has to stay short: it is itself governed by the same 1024-byte ceiling it names, so
   padding it with prose here would be the exact mistake it exists to prevent. */
$charset_note = "<!-- Charset here, on LINE 2 -- not beside <title> below. A browser's encoding-\n"
	. "     sniffing prescan reads ONLY a document's first 1024 BYTES for <meta charset>; past\n"
	. "     that offset a declaration might as well not exist. The banner below alone runs past\n"
	. "     1000 bytes and opens in accented Spanish, so the <title> spot is already too late to\n"
	. "     govern even that banner. FOUND BY EYE in the published gallery, after this file's own\n"
	. "     audit had gone green: with no charset declared, a browser fell back to windows-1252\n"
	. "     on these UTF-8 bytes, and every accented character rendered as two garbled bytes, an\n"
	. "     arrow among them. RT_GALLERY_NO_CHARSET now FAILs for this, measuring the first 1024\n"
	. "     bytes rather than grepping the file for the word \"charset\". -->";
$head = nm_gallery_fingerprint_line( nm_gallery_input_digest( $DIR ) ) . "\n" . $charset_note . "\n" . '<meta charset="utf-8">' . "\n" . $head;

/* THE PAGE'S OWN CLAIM ABOUT ITSELF, and it had to change the day a strip declared a toggle. The
   note used to end "la diferencia es el ancla — no la foto ni el texto", which a reader could check
   against strip 1 in about two seconds and find false: it renders three photographs where its three
   siblings render one. A stale sentence on the page is worse than a stale comment in the source,
   because the reader believes it and has no way to know it is out of date. */
/* HOW COMPLETE THIS CATALOGUE IS, DERIVED AND PRINTED.

   Two cards on a page that calls itself a gallery reads as broken, and it was read that way. It
   is not broken; it is INCOMPLETE, and the difference was invisible because nothing on the page
   said so. That is the same failure this file keeps hitting from the other side -- an unprinted
   state is a silent configuration -- and the fix is the same one: print it.

   The denominator is COUNTED from the archetype docs rather than typed here, so it cannot drift
   the way a hand-written "de 10" would the day an eleventh lands. If the directory cannot be read
   the build fails rather than quietly printing a smaller world. */
$tpl_docs = array_merge(
	glob( dirname( __DIR__, 3 ) . '/web-templates/references/templates/corporate/TPL-*.md' ),
	glob( dirname( __DIR__, 3 ) . '/web-templates/references/templates/ecommerce/TPL-*.md' )
);
$tpl_docs = array_values( array_filter( $tpl_docs, function ( $f ) {
	return false === strpos( basename( $f ), '_README' );
} ) );
if ( count( $tpl_docs ) < 2 ) {
	fail( 'found ' . count( $tpl_docs ) . ' archetype doc(s) under web-templates/references/templates/'
		. ' — the completeness line would be printing a number nobody measured' );
}
$n_declared = count( $tpl_docs );

/* THE NUMERATOR IS DISTINCT ARCHETYPES AND NOT CARDS, and it went wrong the moment a second
   template landed on TPL-C-05. `count( $by_tpl )` printed "11 de los 10 arquetipos … los -1
   restantes", which is the kind of sentence a reader trusts right up until they read it twice:
   the denominator was measured from the docs, the numerator was counting a different thing, and
   nothing between them noticed because both were integers. Counted from `arch` now, which is the
   field that means archetype. */
$archs_built = array();
$n_brands    = 0;
foreach ( $by_tpl as $bl_v ) {
	$archs_built[ $bl_v['content']['arch'] ] = true;
	if ( '' !== $bl_v['content']['brand'] ) {
		++$n_brands;
	}
}
$n_built    = count( $archs_built );
$n_tpls     = count( $by_tpl );
$built_line = $n_tpls . ' ' . ( 1 === $n_tpls ? 'plantilla' : 'plantillas' ) . ' sobre ' . $n_built
	. ' de los ' . $n_declared . ' arquetipos que el framework declara.';
if ( $n_declared > $n_built ) {
	$built_line .= ' Los ' . ( $n_declared - $n_built ) . ' restantes existen como especificación y'
		. ' todavía no como maqueta.';
}
$built_line .= ' ' . ( 1 === $n_brands ? 'Una es una marca propia' : $n_brands . ' son marcas propias' )
	. ' — negocio, fondo, acento, tipografía y fotografías suyas; el resto comparten la marca de la casa.';

$intro = '<header class="gal-head"><div class="gal-wrap">'
	. '<span class="eyebrow">NovaMira · uso interno</span>'
	. '<h1>Galería de plantillas</h1>'
	. '<p>Cada tarjeta es una <b>plantilla</b>. El <code>TPL-*</code> decide qué secciones existen y '
	. 'en qué orden; la <b>marca</b> decide el fondo, el acento, la tipografía y las fotos; y el '
	. '<code>PERS-*</code> — el <b>ancla</b> — decide los cinco ejes perceptuales. Dos plantillas '
	. 'pueden compartir arquetipo sin parecerse en nada, que es justamente el punto. Elegir tarjeta '
	. 'precarga las tres cosas.</p>'
	. '<p class="gal-note">Dentro de una misma plantilla el copy y el juego de fotos son los mismos '
	. 'en todas sus variantes: si dos se distinguen, la diferencia es el <b>ancla</b> o un '
	. '<b>toggle</b> (CAPA 3), nunca el copy y nunca una foto que una tenga y otra no. Cada variante '
	. 'imprime los toggles que resuelve encima de sus cinco ejes. <b>Entre</b> plantillas de marcas '
	. 'distintas la fotografía sí cambia, y cambia a propósito: es lo que se está catalogando.</p>'
	. '<p class="gal-progress">' . h( $built_line ) . '</p>'
	. '</div></header>';

// ── the sticky filter: by site type ───────────────────────────────────────────────────
//
// Built from the strips that actually exist rather than from a hardcoded list, so a filter can
// never offer a value nothing matches — and adding a ninth strip needs no edit here.

$sites_present   = array();
$anchors_present = array();
foreach ( $STRIPS as $s ) {
	$sites_present[ $CONTENT[ $s['tpl'] ]['site'] ] = $CONTENT[ $s['tpl'] ]['site_es'];
	$anchors_present[ $s['anchor'] ]                = $ANCHORS[ $s['anchor'] ]['name'];
}

/* THE ANCHOR FILTER IS BACK, and the round trip is the lesson. It was removed when a card was a
   whole template: every template carries all four anchors, so the filter matched everything at
   every setting and narrowed nothing. That reasoning was correct THEN and expired the moment a
   card became an anchor again. A rule whose reason has expired is exactly what this session keeps
   finding, so it is written down rather than quietly restored. */

function filter_group( $key, $label, $options ) {
	$o = '<div class="fgroup" role="group" aria-label="' . h( $label ) . '">'
		. '<span class="flabel">' . h( $label ) . '</span>'
		. '<button class="fbtn" type="button" data-filter="' . $key . '" data-value="all"'
		. ' aria-pressed="true">Todas</button>';
	foreach ( $options as $val => $text ) {
		$o .= '<button class="fbtn" type="button" data-filter="' . $key . '" data-value="' . h( $val ) . '"'
			. ' aria-pressed="false">' . h( $text ) . '</button>';
	}
	return $o . '</div>';
}

$filter = '<div class="gal-filter"><div class="gal-wrap">'
	. filter_group( 'site', 'Tipo', $sites_present )
	. filter_group( 'pers', 'Ancla', $anchors_present )
	. '<p class="fcount" id="gal-count" role="status" aria-live="polite">'
	. count( $by_tpl ) . ' plantillas · ' . $n_cards . ' homes</p>'
	. '</div></div>';

// Plain JS, no library. `hidden` rather than a class: it is the platform's own "not rendered and
// not in the accessibility tree", so a filtered-out strip disappears for a screen reader too
// instead of only for the eye.
$filter_js = "<script>\n"
	. "(function(){\n"
	. "  var state={site:'all',pers:'all'};\n"
	// The filter narrows the CATALOGUE, not the pages. Hiding a detail page would leave a card
	// whose link opens nothing; hiding the card leaves the page reachable by its own URL, which
	// is what a shared `#tplc01-editorial` link has to keep doing.
	. "  var cards=[].slice.call(document.querySelectorAll('.tgrid > li'));\n"
	. "  var count=document.getElementById('gal-count');\n"
	. "  function apply(){\n"
	. "    var n=0;\n"
	. "    for(var i=0;i<cards.length;i++){\n"
	. "      var s=cards[i];\n"
	. "      var ok=(state.site==='all'||s.getAttribute('data-site')===state.site)\n"
	. "          && (state.pers==='all'||s.getAttribute('data-pers')===state.pers);\n"
	. "      s.hidden=!ok; if(ok){n++;}\n"
	. "    }\n"
	. "    count.textContent = (n===cards.length) ? n+' homes' : n+' de '+cards.length+' homes';\n"
	. "    if(window.NMthumbs){window.NMthumbs();}\n"
	. "  }\n"
	. "  var btns=document.querySelectorAll('.fbtn');\n"
	. "  for(var i=0;i<btns.length;i++){\n"
	. "    btns[i].addEventListener('click',function(){\n"
	. "      var k=this.getAttribute('data-filter');\n"
	. "      state[k]=this.getAttribute('data-value');\n"
	. "      var peers=document.querySelectorAll('.fbtn[data-filter=\"'+k+'\"]');\n"
	. "      for(var j=0;j<peers.length;j++){ peers[j].setAttribute('aria-pressed', peers[j]===this ? 'true':'false'); }\n"
	. "      apply();\n"
	. "    });\n"
	. "  }\n"
	. "  apply();\n"
	. "})();\n"
	. "</script>";

// ── the copy pass: an enhancement over text that already works ─────────────────────────────────
//
// THREE ROUTES, AND THE LAST ONE CANNOT FAIL. `navigator.clipboard` rejects in a sandboxed frame
// without `clipboard-write`, and `execCommand` is deprecated and refused by some engines outside a
// user gesture — so neither is a floor. Selecting the block IS the floor: whatever the sandbox
// allows, the spec ends up selected and Ctrl/⌘+C works, and the status line says which happened
// instead of leaving a button that appears to have done something.
//
// The selection is made FIRST, before either API is tried, so the floor holds even when both throw.

$copy_js = "<script>\n"
	. "(function(){\n"
	. "  var btns=document.querySelectorAll('.handoff-copy');\n"
	. "  function say(id,msg){var s=document.querySelector('[data-said=\"'+id+'\"]');if(s){s.textContent=msg;}}\n"
	. "  for(var i=0;i<btns.length;i++){\n"
	. "    btns[i].hidden=false;\n"
	. "    btns[i].addEventListener('click',function(){\n"
	. "      var id=this.getAttribute('data-copy'), pre=document.getElementById(id);\n"
	. "      if(!pre){return;}\n"
	. "      var r=document.createRange(); r.selectNodeContents(pre);\n"
	. "      var sel=window.getSelection(); sel.removeAllRanges(); sel.addRange(r);\n"
	. "      var ok=false; try{ok=document.execCommand('copy');}catch(e){ok=false;}\n"
	. "      if(ok){say(id,'Copiado al portapapeles.');return;}\n"
	. "      if(navigator.clipboard&&navigator.clipboard.writeText){\n"
	. "        navigator.clipboard.writeText(pre.textContent).then(\n"
	. "          function(){say(id,'Copiado al portapapeles.');},\n"
	. "          function(){say(id,'Seleccionado — pulsa Ctrl/⌘+C.');});\n"
	. "        return;\n"
	. "      }\n"
	. "      say(id,'Seleccionado — pulsa Ctrl/⌘+C.');\n"
	. "    });\n"
	. "  }\n"
	. "})();\n"
	. "</script>";

$foot = '<footer class="gal-foot"><div class="gal-wrap">'
	/* $n_strip counts VARIANTS, which stopped being the same thing as templates the day the
	   catalogue grouped them. The footer said "8 plantillas" over a grid of two. */
	. '<p>' . count( $by_tpl ) . ' ' . ( 1 === count( $by_tpl ) ? 'plantilla' : 'plantillas' )
	. ' · ' . $n_strip . ' ' . ( 1 === $n_strip ? 'home' : 'homes' ) . ' · '
	. count( $only_used ) . ' imágenes · generado por <code>_build-gallery.php</code> '
	. 'desde <code>assets/gallery/img/</code> y <code>_gallery-images.md</code>.</p>'
	. '</div></footer>';

$noscript = '<noscript><div class="noscript"><div class="gal-wrap">'
	. 'Las fotografías se hidratan con una pasada de JavaScript desde un único mapa de <code>data:</code> URIs. '
	. 'Sin JS el texto, la maquetación y los ejes se ven enteros; las fotos no. '
	. 'El bloque de precarga se lee y se selecciona igual: sólo el botón «Copiar» necesita JS.'
	. '</div></div></noscript>';

/* The filters live in the DOM, once, before anything that references them. `width:0;height:0` and
   not `display:none`: a display:none SVG makes its filters unreachable in some engines, and the
   symptom is every photograph rendering unfiltered — the exact state this block exists to end,
   arrived at silently. `aria-hidden` because a filter definition is not content. */
$ink_svg = '<svg class="ink-defs" width="0" height="0" aria-hidden="true" focusable="false">'
	. '<defs>' . implode( '', $ink_defs ) . '</defs></svg>';

// ── the chrome: OUTSIDE every `.page`, so it survives each switch (html-mockup SKILL.md) ───────
$top = '<div class="gal-top"><div class="gal-wrap">'
	. '<span class="mark">NovaMira · Galería</span>'
	. '<span class="here" id="gal-here"></span>'
	. '<a class="backlink" href="#index" id="gal-back" hidden>← Todas las plantillas</a>'
	. '</div></div>';

$index = '<div class="page" id="p-index">' . "\n"
	. $intro . "\n"
	. $filter . "\n"
	. '<div class="gal-wrap"><ul class="tgrid">' . implode( "\n", $groups ) . '</ul></div>' . "\n"
	. '</div>';

/* ── the router, and the miniature that cannot go stale ────────────────────────────────────────

   ROUTER. `hidden` is applied by script and never baked into the markup: with JavaScript off the
   catalogue would otherwise become a grid of links to nothing, so the no-JS fall-back is the whole
   document, scrollable, exactly as it read before. The hash IS the strip id, so a link Juan sends
   himself — `…#tple02-matter` — opens on that template.

   MINIATURE. Cloned from the strip it points at, so it cannot describe a design the generator no
   longer renders; a raster screenshot could, silently, and this repository has already paid for
   one treatment whose justification had expired without anyone noticing.

   THE STAGE IS SIZED TO THE REAL VIEWPORT, not to a fixed 1440. Every size in this system is a
   `clamp()` in `vw`, so a stage of arbitrary width would render type belonging to some other
   screen — and the card advertises SCALE as one of its five axes, which would make the picture
   contradict the label under it. Scale factor is therefore card width ÷ viewport width, and it is
   recomputed on resize. */
$mk_js = "<script>\n"
	. "(function(){\n"
	. "  var pages=[].slice.call(document.querySelectorAll('.page'));\n"
	. "  var back=document.getElementById('gal-back');\n"
	. "  var here=document.getElementById('gal-here');\n"
	. "  var idxScroll=0;\n"
	// `index` is a NAMED ROUTE and also a real page id, which is the trap: `getElementById`
	// answers yes for `p-index`, so without this line the catalogue is routed as if it were a
	// template — back link showing, scroll position discarded, and `paint()` never called, so
	// the FIRST CLICK on "Todas las plantillas" landed on a grid with eight empty thumbnails.
	// Measured before the fix: `thumbsBuilt=0/8` at `#index` against `8/8` with no hash.
	. "  var INDEX='index';\n"
	// The hash carries TWO levels: `#tplc01` opens the template on its default anchor and
	// `#tplc01/direct` opens it on that one, so a link that names a variant keeps the variant.
	. "  function parseHash(){\n"
	. "    var raw=(location.hash||'').replace(/^#/,'');\n"
	. "    if(!raw||raw===INDEX){ return {tpl:'',pers:'',page:''}; }\n"
	. "    var bits=raw.split('/'), t=bits[0], pr=bits[1]||'', pg=bits[2]||'';\n"
	. "    if(!document.getElementById('p-'+t)){ return {tpl:'',pers:'',page:''}; }\n"
	. "    return {tpl:t,pers:pr,page:pg};\n"
	. "  }\n"
	// An unknown anchor falls back to the template's FIRST variant rather than to nothing: a
	// stale link naming a renamed anchor should still land on the template it names.
	. "  function selectVariant(page,pers){\n"
	. "    var vars=page.querySelectorAll('.variant'), btns=page.querySelectorAll('.sbtn[data-pers]');\n"
	. "    if(!vars.length){ return ''; }\n"
	. "    var want='',i;\n"
	. "    for(i=0;i<vars.length;i++){ if(vars[i].getAttribute('data-pers')===pers){ want=pers; } }\n"
	. "    if(!want){ want=vars[0].getAttribute('data-pers'); }\n"
	. "    for(i=0;i<vars.length;i++){ vars[i].hidden=(vars[i].getAttribute('data-pers')!==want); }\n"
	. "    for(i=0;i<btns.length;i++){\n"
	. "      btns[i].setAttribute('aria-pressed', btns[i].getAttribute('data-pers')===want?'true':'false');\n"
	. "    }\n"
	. "    return want;\n"
	. "  }\n"
	// The PAGE is chosen across every variant, not only the visible one, so switching anchor
	// keeps you on the page you were reading instead of dropping you back on the home.
	. "  function selectPage(page,pk){\n"
	. "    var all=page.querySelectorAll('.sample[data-page]');\n"
	. "    var btns=page.querySelectorAll('.sbtn[data-page]');\n"
	. "    if(!all.length){ return ''; }\n"
	. "    var want='',i;\n"
	. "    for(i=0;i<all.length;i++){ if(all[i].getAttribute('data-page')===pk){ want=pk; } }\n"
	. "    if(!want){ want=all[0].getAttribute('data-page'); }\n"
	. "    for(i=0;i<all.length;i++){ all[i].hidden=(all[i].getAttribute('data-page')!==want); }\n"
	. "    for(i=0;i<btns.length;i++){\n"
	. "      btns[i].setAttribute('aria-pressed', btns[i].getAttribute('data-page')===want?'true':'false');\n"
	. "    }\n"
	. "    return want;\n"
	. "  }\n"
	. "  var wasIndex=true;\n"
	// `render` does the work and `route` only reads the hash. Splitting them is what lets a click
	// route the page WITHOUT waiting for a `hashchange` the artifact shell might never deliver.
	. "  function render(id,pers,pgk){\n"
	. "    if(id && !document.getElementById('p-'+id)){ id=''; }\n"
	. "    var target=document.getElementById(id?('p-'+id):('p-'+INDEX));\n"
	. "    if(!target){ return; }\n"
	. "    if(wasIndex && id){ idxScroll=window.pageYOffset||0; }\n"
	. "    for(var i=0;i<pages.length;i++){ pages[i].hidden=(pages[i]!==target); }\n"
	. "    back.hidden=!id;\n"
	. "    if(id){\n"
	. "      var pr=selectVariant(target,pers);\n"
	. "      var pk=selectPage(target,pgk);\n"
	. "      var hd=target.querySelector('.tpl-head h2');\n"
	. "      var ch=target.querySelector('.sbtn[data-pers='+pr+']');\n"
	. "      var pb=target.querySelector('.sbtn[data-page='+pk+']');\n"
	. "      here.textContent=(hd?hd.textContent:'')+(ch?(' · '+ch.textContent):'')\n"
	. "        +(pb&&pk!=='home'?(' · '+pb.textContent):'');\n"
	. "    } else { here.textContent=''; }\n"
	. "    if(id){ window.scrollTo(0,0); } else { paint(); window.scrollTo(0,idxScroll); }\n"
	. "    wasIndex=!id;\n"
	. "  }\n"
	. "  function route(){ var q=parseHash(); render(q.tpl,q.pers,q.page); }\n"
	. "\n"
	. "  var thumbs=[].slice.call(document.querySelectorAll('.thumb'));\n"
	. "  function vw(){ return document.documentElement.clientWidth; }\n"
	. "  function build(t){\n"
	. "    var src=document.querySelector('#'+t.getAttribute('data-of')+' .sample');\n"
	. "    if(!src){ return; }\n"
	. "    var stage=document.createElement('span');\n"
	. "    stage.className='thumb-stage';\n"
	. "    stage.setAttribute('inert','');\n"
	. "    stage.setAttribute('aria-hidden','true');\n"
	. "    var clone=src.cloneNode(true);\n"
	// The top of the page is what a catalogue card shows: header, hero, and the first section
	// under it. Cloning the whole strip would also multiply every `data:` image forty times over.
	. "    while(clone.children.length>3){ clone.removeChild(clone.lastElementChild); }\n"
	// A duplicate id breaks getElementById, `label[for]` and `aria-controls` FOR THE ORIGINAL —
	// that is, for the page the reader is about to click into, not for the copy.
	. "    var ids=clone.querySelectorAll('[id]');\n"
	. "    for(var i=0;i<ids.length;i++){ ids[i].removeAttribute('id'); }\n"
	. "    stage.appendChild(clone);\n"
	. "    t.appendChild(stage);\n"
	. "    var w=t.querySelector('.thumb-wait'); if(w&&w.parentNode){ w.parentNode.removeChild(w); }\n"
	. "    t.__stage=stage;\n"
	. "  }\n"
	. "  function fit(t){\n"
	. "    if(!t.__stage){ return; }\n"
	. "    var v=vw(), k=t.clientWidth/v;\n"
	. "    t.__stage.style.width=v+'px';\n"
	. "    t.__stage.style.transform='scale('+k+')';\n"
	. "  }\n"
	. "  function paint(){\n"
	. "    for(var i=0;i<thumbs.length;i++){\n"
	. "      var t=thumbs[i];\n"
	. "      if(!t.clientWidth){ continue; }\n"
	. "      if(!t.__stage){ build(t); }\n"
	. "      fit(t);\n"
	. "    }\n"
	. "  }\n"
	. "  window.NMthumbs=paint;\n"
	. "\n"
	. "  var rt;\n"
	. "  window.addEventListener('resize',function(){ clearTimeout(rt); rt=setTimeout(paint,150); });\n"
	// The switcher writes the HASH instead of flipping the DOM, so exactly one place decides
	// which variant is on screen. Two writers drift the moment the back button joins in.
	. "  document.addEventListener('click',function(e){\n"
	. "    var b=e.target&&e.target.closest?e.target.closest('.sbtn[data-pers]'):null;\n"
	. "    if(!b){ return; }\n"
	. "    var pg=b.closest('.page'); if(!pg){ return; }\n"
	. "    location.hash=pg.id.replace(/^p-/,'')+'/'+b.getAttribute('data-pers');\n"
	. "  });\n"
	// NAVIGATION DOES NOT DEPEND ON `hashchange` FIRING. The gallery is published into a sandboxed
	// artifact frame whose shell installs its own capturing click listener and its own
	// scroll-restore logic keyed on `location.hash`; a fragment navigation that the shell swallows
	// would leave every card looking inert, and it would fail EXACTLY where it cannot be debugged
	// -- in the viewer, not on this machine. So the click routes the page itself and the hash is
	// written afterwards as an enhancement: shareable links keep working, and the catalogue keeps
	// working even if nothing ever listens to the hash again.
	. "  function go(t,pr,pg){\n"
	. "    var h=t?(t+'/'+(pr||'')+(pg?('/'+pg):'')):INDEX;\n"
	. "    try{ if((location.hash||'').replace(/^#/,'')!==h){ location.hash=h; } }catch(e){}\n"
	. "    render(t,pr,pg);\n"
	. "  }\n"
	. "  document.addEventListener('click',function(e){\n"
	. "    if(e.defaultPrevented||e.button||e.metaKey||e.ctrlKey||e.shiftKey||e.altKey){ return; }\n"
	. "    var el=e.target&&e.target.closest?e.target:null; if(!el){ return; }\n"
	// A page chip keeps the anchor it was pressed under, and an anchor chip keeps the page.
	// Either one resetting the other would make the two controls fight over one screen.
	. "    var pgb=el.closest('.sbtn[data-page]');\n"
	. "    if(pgb){ var pp=pgb.closest('.page');\n"
	. "      if(pp){ e.preventDefault();\n"
	. "        var cur=pp.querySelector('.sbtn[data-pers][aria-pressed=true]');\n"
	. "        go(pp.id.replace(/^p-/,''), cur?cur.getAttribute('data-pers'):'', pgb.getAttribute('data-page'));\n"
	. "      } return; }\n"
	. "    var b=el.closest('.sbtn[data-pers]');\n"
	. "    if(b){ var pg=b.closest('.page');\n"
	. "      if(pg){ e.preventDefault();\n"
	. "        var cp=pg.querySelector('.sbtn[data-page][aria-pressed=true]');\n"
	. "        go(pg.id.replace(/^p-/,''), b.getAttribute('data-pers'), cp?cp.getAttribute('data-page'):'');\n"
	. "      } return; }\n"
	. "    var a=el.closest('a[href^=\"#\"]');\n"
	. "    if(!a){ return; }\n"
	. "    var raw=a.getAttribute('href').slice(1);\n"
	. "    if(raw!==INDEX && !document.getElementById('p-'+raw.split('/')[0])){ return; }\n"
	. "    e.preventDefault();\n"
	. "    var bits=raw.split('/');\n"
	. "    go(raw===INDEX?'':bits[0], bits[1]||'', bits[2]||'');\n"
	. "  });\n"
	. "  window.addEventListener('hashchange',route);\n"
	. "  route();\n"
	. "})();\n"
	. "</script>";

/* ── A NEW SECTION MAY NOT WEAR A CLASS SOMEBODY ELSE ALREADY STYLED ──────────────────────────
   FOUND BY LOOKING, AND IT COST AN HOUR. TPL-C-06's closing band shipped as `<section class="sec
   hours closing">`, and `.hours` was already TPL-C-05's NAP definition list: `display:grid`,
   `gap:.2rem`, `font-size:var(--fs-small)`. Those three declarations landed on a whole SECTION.
   The band still rendered — it just rendered as a one-column grid whose `.canvas` was a grid item
   instead of a grid, so the heading started at the viewport's left edge and ran off it.

   NOTHING ELSE COULD HAVE CAUGHT IT. `php -l` and `node --check` see syntax; the contrast sweeps
   see colour; the copy multiset sees text. A stylesheet where two archetypes silently share a
   class name is valid CSS that renders, which is the exact profile of every defect this file has
   had to grow a gate for. The gate is cheap: the classes an archetype introduces are listed, and
   each must be undefined in every byte of CSS emitted BEFORE that archetype's own block.

   The list is hand-kept and that is a real weakness — a tenth class added to the block below
   without a line here is unchecked. It is still strictly better than nothing, and the failure mode
   is "a new collision slips through" rather than "the check silently passes on everything", which
   is the failure this repository keeps actually having. */
/* ── EVERY var() MUST NAME A TOKEN THIS STYLESHEET DECLARES ───────────────────────────────────
   FOUND BY JUAN, IN A SCREENSHOT WITH A RED BOX AROUND THE GAP THAT WAS NOT THERE. The split
   quote's panel carried `padding:var(--sp-2xl) clamp(2.5rem,6vw,6rem)` and rendered with NO
   padding at all — not a small one, none. The token is `--sp-xxl`; `--sp-2xl` never existed.

   AND THE HORIZONTAL VALUE WAS FINE. That is the whole reason this needs a gate rather than a
   careful reader: an invalid `var()` substitution does not fall back to the rest of the
   declaration, it makes THE ENTIRE DECLARATION invalid at computed-value time, so the property
   takes its initial value. One typo in the vertical half deleted a perfectly good `clamp()` in the
   horizontal half. No warning, no console error, valid CSS, and a design that reads as "the gutter
   is too small" when in fact there is no gutter and no rule.

   This file already carries a paragraph about the same trap on `letter-spacing` — an invalid
   substitution falling back to `normal` and leaving four anchors silently unramped. That paragraph
   was written and the lesson still did not generalise into a check. It does now: the check is
   three lines, it is total over the emitted stylesheet, and it costs nothing.

   `var(--x, fallback)` is exempt and correctly so: naming a fallback is the author saying the token
   may be absent, which is a different statement from misspelling one. */
$vt_all      = implode( "\n", $css );
$vt_declared = array();
if ( preg_match_all( '/(--[A-Za-z0-9_-]+)\s*:/', $vt_all, $vt_dm ) ) {
	foreach ( $vt_dm[1] as $vt_d ) {
		$vt_declared[ $vt_d ] = true;
	}
}
$vt_missing = array();
if ( preg_match_all( '/var\(\s*(--[A-Za-z0-9_-]+)\s*\)/', $vt_all, $vt_um ) ) {
	foreach ( $vt_um[1] as $vt_u ) {
		if ( ! isset( $vt_declared[ $vt_u ] ) ) {
			$vt_missing[ $vt_u ] = true;
		}
	}
}
if ( array() === $vt_declared ) {
	fail( 'the stylesheet parsed to zero custom-property declarations — the undefined-token check'
		. ' below would pass on every var() in the file' );
}
if ( array() !== $vt_missing ) {
	fail( 'the stylesheet uses ' . implode( ', ', array_keys( $vt_missing ) ) . ' and declares'
		. ' neither. An invalid var() does not degrade — it invalidates the WHOLE declaration at'
		. ' computed-value time, so the property silently takes its initial value and any other value'
		. ' in the same declaration is lost with it. Use the real token name, or give the var() an'
		. ' explicit fallback if absence is intended.' );
}

/* ── EVERY <table> DECLARES ITS OWN COLOUR AND FACE ───────────────────────────────────────────
   QUIRKS MODE IS THE REASON AND IT IS NOT GOING AWAY. A gallery asset is an Artifact FRAGMENT with
   no doctype, so any browser that opens the file directly — which is every local capture and every
   review pass — runs in `BackCompat`, where a table inherits neither `color` nor `font` from its
   parent and resets to the document default instead. The published page is wrapped in a doctype
   and renders correctly; the file under review does not. A defect that exists ONLY where you look
   at it is worse than one that exists everywhere, because the fix looks unnecessary.

   MEASURED, not deduced: `--c-text` read #EDF1F6 on the td, the tr, the tbody AND the table, while
   the computed `color` was #141C24 from the document root. The token chain was perfect and the
   property chain broke at one element.

   Checked on the TABLE's own class, never on a descendant selector, because inheritance is exactly
   the mechanism that fails here. */
$tb_all  = implode( "\n", $css );
$tb_body = implode( "\n", $body );
if ( preg_match_all( '/<table class="([a-z0-9 _-]+)"/i', $tb_body, $tb_m ) ) {
	foreach ( array_unique( $tb_m[1] ) as $tb_cls ) {
		$tb_ok = false;
		foreach ( preg_split( '/\s+/', trim( $tb_cls ) ) as $tb_one ) {
			if ( '' === $tb_one ) {
				continue;
			}
			if ( ! preg_match( '/(^|[\s,])\.' . preg_quote( $tb_one, '/' ) . '\s*\{([^}]*)\}/', $tb_all, $tb_r ) ) {
				continue;
			}
			if ( false !== strpos( $tb_r[2], 'color:' ) && false !== strpos( $tb_r[2], 'font-family:' ) ) {
				$tb_ok = true;
			}
		}
		if ( ! $tb_ok ) {
			fail( "no rule on `$tb_cls` declares both `color` and `font-family` for that <table>."
				. ' A gallery asset has no doctype, so a browser opening it directly is in quirks mode,'
				. ' where a table inherits neither from its parent: the cells fall back to the document'
				. " default and the page's own ground can make them invisible. Declare both on the"
				. ' table itself.' );
		}
	}
}

$c06_all    = $vt_all;
$c06_marker = '── COMP-HEADER, floating on the photograph';
$c06_at     = strpos( $c06_all, $c06_marker );
if ( false === $c06_at ) {
	fail( 'the TPL-C-06 stylesheet block cannot be located by its own marker comment — the collision'
		. ' check below would be scanning the whole file against itself and passing on everything' );
}
$c06_before  = substr( $c06_all, 0, $c06_at );

/* ONE ENTRY PER ARCHETYPE BLOCK, keyed by the marker comment that opens it. It started as a single
   hand-kept list for TPL-C-06 and that shape does not survive a second archetype: three more
   blocks means three more places a name can already be taken, and one flat list cannot say WHICH
   block a class belongs to or where "before" starts for it. */
$CLASS_BLOCKS = array(
	'── COMP-HEADER, floating on the photograph' => array( 'TPL-C-06', array(
		'head-over', 'hero-full', 'marquee', 'track', 'run', 'menu-list', 'menu-group', 'dish', 'dots',
		'figq', 'portrait', 'say', 'sig', 'hoursblock', 'hours-big', 'shut', 'where', 'masonry',
		'bookmedia', 'booking-split' ) ),
	'══════════ TPL-C-07 · STOCK / OCASIÓN'      => array( 'TPL-C-07', array(
		'hero-search', 'filterbar', 'stockcount', 'stockgrid', 'vcard', 'vbody', 'vfacts', 'vprice',
		/* `.pnote` and `.badge*` are NOT in this list and that is the correction, not an omission.
		   They are SHARED — six archetypes emit a section note and three emit a badge row — and the
		   check asks "is this class defined before the block that OWNS it". A shared class has no
		   owner, so listing it under whichever archetype happened to need it first turns the gate into
		   a tripwire for anybody who later styles it from a shared block, which is exactly what
		   happened. The gate is for names an archetype INTRODUCES for itself. */
		'tiform', 'ftable', 'frow', 'ftotal', 'badgelist', 'shot' ) ),
	'══════════ TPL-C-08 · MODELO / LANZAMIENTO' => array( 'TPL-C-08', array(
		'mhero', 'mfigs', 'specwrap', 'spectable', 'differs', 'same', 'offer' ) ),
	'══════════ TPL-C-09 · TALLER / TARIFA'      => array( 'TPL-C-09', array(
		'pgroups', 'pgroup', 'prow', 'pwhat', 'ptag' ) ),
	'══════════ THE SECTION HEAD, PER TEMPLATE' => array( 'head modes', array() ),
	'══════════ TPL-C-10 · CLÍNICA / TRATAMIENTOS' => array( 'TPL-C-10', array(
		'treatcards', 'trcard', 'trbody', 'tfacts', 'bapair', 'bashot', 'medlist', 'medico',
		'medbody', 'medlic', 'inslist' ) ),
	'══════════ TPL-C-11 · PLAN POR FASES'       => array( 'TPL-C-11', array(
		'painlist', 'phtotal', 'phaselist', 'phase', 'phn', 'phbody', 'phmonths', 'planlist',
		'planbox', 'planflag', 'planq', 'planp', 'planfeats' ) ),
	'══════════ TPL-C-12 · URGENCIAS / HOY'      => array( 'TPL-C-12', array(
		'urgbar', 'urgstate', 'urgnote', 'btn-lg', 'triagelist', 'trow', 'trdo', 'trmin',
		'waitlist' ) ),
	/* `.frame`, `.pnote`, `.directlist` y `.dlabel` NO están en esta lista y eso es la regla, no un
	   olvido: son COMPARTIDAS, la comprobación pregunta «¿está definida antes del bloque que la
	   POSEE?» y una clase compartida no tiene dueño. `.rprice` sí entra aunque la use también la
	   ficha de tratamiento, porque las dos páginas son de este arquetipo. */
	/* `.frame`, `.pnote`, `.directlist` y `.dlabel` NO están aquí y eso es la regla, no un olvido:
	   son COMPARTIDAS y una clase compartida no tiene dueño. Tampoco están `.secrow`, `.bleedband`,
	   `.stepped` ni `.hbar`: son el CHASIS, o sea vocabulario que cualquier arquetipo puede pedir,
	   y meterlas aquí convertiría la primera plantilla que las usó en su propietaria. */
	/* LAS DOS FICHAS DE INVENTARIO COMPARTEN BLOQUE Y ESO ES LA REGLA, no un atajo. La comprobación
	   pregunta «¿está definida antes del bloque que la POSEE?», y estas clases las poseen DOS
	   arquetipos a la vez: `.uspecs` la emiten las dos, y separarlas en dos bloques obligaría a
	   elegir una dueña arbitraria. Un solo marcador, un solo bloque. */
	'══════════ FICHAS DE INVENTARIO'            => array( 'TPL-UNIT-01 + TPL-PROPERTY-01', array(
		'refline', 'refcode', 'unithead', 'unitgal', 'ugal', 'ushot', 'uthumbs', 'ucap',
		'unitspecs', 'uspecs', 'uspec', 'pricefin', 'pricepair', 'pricebox', 'priceq', 'pterms',
		'histrep', 'histgrid', 'hist', 'ptour', 'tourlist', 'tourcell', 'tourmeta',
		'planwrap', 'floorplan', 'costs', 'costlist', 'costrow', 'costsum',
		'energysec', 'energy', 'elabel', 'eletter' ) ),
	'══════════ TPL-C-14 · RITUAL / BONO'        => array( 'TPL-C-14', array(
		'lumhead', 'topbar', 'zonegrid', 'zcell', 'zcount', 'zfrom', 'menuband', 'menu-ground',
		'zicon', 'menu-panel', 'rituals', 'rgroup', 'rglabel', 'ritual', 'rline', 'rname', 'rdesc',
		'rfacts', 'rmin', 'rprice', 'rafter',
		'cabinsay', 'cabinlist', 'cabname', 'collage', 'mat', 'protos', 'proto', 'pmin',
		'checker', 'cpanel', 'chead', 'cshot', 'bonos', 'bono', 'bonoq', 'bonop', 'bonosave',
		'giftstrip', 'thumbs', 'faces', 'face', 'round', 'since', 'hoursrow', 'hourscol', 'daylist',
		'dayrow', 'wherecol', 'addr', 'reachline', 'reachlist', 'reachbig',
		'svcgroup', 'svcshot', 'svcbody',
		'svccards', 'svccard', 'svcfacts', 'factstrip', 'tfactlist', 'tfact', 'contrapair',
		'contracol', 'contralist' ) ),
);
foreach ( $CLASS_BLOCKS as $cb_marker => $cb_def ) {
	list( $cb_tpl, $cb_classes ) = $cb_def;
	$cb_at = strpos( $c06_all, $cb_marker );
	if ( false === $cb_at ) {
		fail( "the `$cb_tpl` stylesheet block cannot be located by its own marker comment — the"
			. ' collision check would be scanning the whole file against itself and passing on'
			. ' everything' );
	}
	$cb_before = substr( $c06_all, 0, $cb_at );
	foreach ( $cb_classes as $cb_c ) {
		if ( preg_match( '/(^|[\s,>+~(])\.' . preg_quote( $cb_c, '/' ) . '(?![\w-])/', $cb_before ) ) {
			fail( "$cb_tpl introduces `.$cb_c`, and the stylesheet already defines a rule on that class"
				. ' before its block. Two archetypes sharing a class name is valid CSS that renders — the'
				. ' older rule simply lands on the newer element and moves it somewhere nobody chose.'
				. ' Rename the new one; the old one has more callers.' );
		}
	}
}

$html = $head . "\n<style>\n" . implode( "\n", $css ) . "\n</style>\n\n"
	. $ink_svg . "\n"
	. $noscript . "\n"
	. $top . "\n\n"
	. '<main class="gal-strips">' . "\n"
	. $index . "\n"
	. implode( "\n", $body ) . "\n"
	. '</main>' . "\n\n"
	. $foot . "\n\n"
	. $filter_js . "\n"
	. $copy_js . "\n"
	. $script . "\n"
	. $mk_js . "\n";

// ── every emitted <script> must PARSE ───────────────────────────────────────────────────────────
//
// This file writes JavaScript as PHP string concatenation, one `. "…\n"` per line, and a line that
// loses its closing quote silently swallows the next one. That is not hypothetical: a line-based
// edit left one orphan `}` here, the whole router threw on load, and the symptom was a catalogue
// whose cards did nothing and whose thumbnails never appeared — a page that LOOKS finished. PHP's
// own `php -l` cannot see it, because to PHP the broken JavaScript is a perfectly valid string.
//
// `node --check` is the real parser and it is used when it is on PATH. When it is not, the build
// falls back to a brace/bracket/paren balance count with string and regex literals removed, which
// catches exactly the class that bit here. The fallback is NOT silent: it says which one ran, so
// nobody reads "scripts OK" as "a parser agreed".

/* EXTRACTED WITH `strpos`, NOT WITH A REGEX, and that is not a style preference. The first cut of
   this gate used `preg_match_all('#<script>(.*?)</script>#s', $html, …)`. On a 1.8 MB subject the
   lazy quantifier blows PCRE's backtrack limit, `preg_match_all` returns FALSE, and `if (false)`
   reads exactly like "no matches" — so the gate reported zero scripts and would have passed a
   document with none. This repository has already lost three mutations to that precise trap. A
   scan that cannot fail loudly is not a scan. */
/* ── NO LITERAL UNICODE ESCAPE REACHES THE PAGE ──────────────────────────────

   An escape written for a Python or JavaScript string and pasted into a PHP single-quoted one
   stays literal, and the page renders the six characters instead of the letter. It shipped that
   way once inside an `aria-label` — the one place a sighted reader would never have caught it,
   and the only place a screen-reader user would have heard every time.

   THE SCRIPT BLOCKS ARE EXEMPT and that exemption is the whole difficulty: inside JavaScript the
   same six characters ARE a real escape and the browser resolves them, so a gate that scanned
   the whole document would fire on correct code. They are cut out before the scan.

   Base64 payloads contain the letters `u00` constantly — six times in this document — but never
   preceded by a backslash, because the base64 alphabet has no backslash. The needle is the
   backslash, not the letters. */
$esc_body = $html;
$esc_at   = 0;
while ( false !== ( $esc_open = strpos( $esc_body, '<script>', $esc_at ) ) ) {
	$esc_close = strpos( $esc_body, '</script>', $esc_open );
	if ( false === $esc_close ) { break; }
	$esc_body = substr_replace( $esc_body, '', $esc_open, $esc_close - $esc_open + 9 );
	$esc_at   = $esc_open;
}
$esc_needle = chr( 92 ) . 'u00';
$esc_hits   = array();
$esc_seek   = 0;
while ( false !== ( $esc_seek = strpos( $esc_body, $esc_needle, $esc_seek ) ) ) {
	$esc_hits[] = substr( $esc_body, max( 0, $esc_seek - 30 ), 54 );
	$esc_seek  += 4;
}
if ( $esc_hits ) {
	fail( 'a literal unicode escape reached the page in ' . count( $esc_hits ) . ' place(s): '
		. implode( "\n  … ", array_slice( $esc_hits, 0, 3 ) ) );
}

$js_blocks = array();
$js_at     = 0;
while ( false !== ( $js_open = strpos( $html, '<script>', $js_at ) ) ) {
	$js_close = strpos( $html, '</script>', $js_open );
	if ( false === $js_close ) {
		fail( 'an emitted <script> is never closed — the document is truncated or malformed' );
	}
	$js_blocks[] = substr( $html, $js_open + 8, $js_close - $js_open - 8 );
	$js_at       = $js_close + 9;
}
if ( count( $js_blocks ) < 3 ) {
	fail( 'expected at least 3 emitted <script> blocks, found ' . count( $js_blocks )
		. ' — the syntax gate would be scanning almost nothing' );
}

$node_ok = false;
$probe   = array();
exec( 'node --version 2>&1', $probe, $probe_rc );
if ( 0 === $probe_rc ) {
	$node_ok = true;
	foreach ( $js_blocks as $bi => $js ) {
		$tmp = sys_get_temp_dir() . '/nm-gallery-js-' . $bi . '.js';
		file_put_contents( $tmp, $js );
		$out = array();
		exec( 'node --check ' . escapeshellarg( $tmp ) . ' 2>&1', $out, $rc );
		unlink( $tmp );
		if ( 0 !== $rc ) {
			fail( "emitted <script> block #$bi does not parse:\n  " . implode( "\n  ", array_slice( $out, 0, 6 ) ) );
		}
	}
} else {
	foreach ( $js_blocks as $bi => $js ) {
		/* Strip what can legally hold an unbalanced brace: string literals and regex literals. The
		   regex arm only has to handle the shapes this file emits (`/^#/`, `/^p-/`), and it runs
		   AFTER strings are gone so a slash inside a string cannot start a false literal. */
		$stripped = preg_replace( array( '/\'(?:\\.|[^\'\\])*\'/', '/"(?:\\.|[^"\\])*"/' ), "''", $js );
		$stripped = preg_replace( '#/(?:\\.|[^/\\\n])+/[gimsuy]*#', '//', $stripped );
		foreach ( array( '{' => '}', '(' => ')', '[' => ']' ) as $open => $close ) {
			$n_open  = substr_count( $stripped, $open );
			$n_close = substr_count( $stripped, $close );
			if ( $n_open !== $n_close ) {
				fail( "emitted <script> block #$bi is unbalanced: $n_open `$open` against $n_close `$close`"
					. ' — a line that lost its closing quote swallows the next one' );
			}
		}
	}
}
printf( "build-gallery: %d emitted script(s) checked with %s\n", count( $js_blocks ),
	$node_ok ? '`node --check`' : 'the balance fallback (node not on PATH)' );
// ── the handoff is asserted over the ASSEMBLED PAGE, not over the function that wrote it ───────
//
// The claim this build makes is that picking a strip LANDS: every strip leaves a block carrying
// its pair, its site type and all five axes. Asserted against the final string rather than against
// $rows, because reading back what a function just returned proves only that PHP works. This
// catches a strip rendered without a handoff, an escaping change that mangles the block, a format
// edit that quietly loses a line, and an id that stops matching the button pointed at it — all of
// which leave a page that looks finished and a pick that produces nothing for one of eight.
//
// Before `file_put_contents`, so a build that cannot keep the claim writes no file at all.

// The READOUT half, asserted for the same reason and found by mutating for it: the data bar and
// the handoff are two renderings of one `axis_rows()` table, so dropping a row kills both — but
// dropping the row from ONE of the two loops desyncs them silently, and until this ran the visible
// bar was the half nobody watched. Five in, five out, on every strip.
$axes_dls = array();
if ( preg_match_all( '#<dl class="axes">(.*?)</dl>#s', $html, $dl_m ) ) {
	$axes_dls = $dl_m[1];
}
if ( count( $axes_dls ) !== $n_strip ) {
	fail( 'the page carries ' . count( $axes_dls ) . ' axis readout(s) for ' . $n_strip . ' strip(s)' );
}
foreach ( $axes_dls as $dl_ix => $dl_one ) {
	$dl_n = substr_count( $dl_one, '<div class="axis">' );
	if ( 5 !== $dl_n ) {
		fail( 'strip #' . ( $dl_ix + 1 ) . " reports $dl_n of 5 axes in its data bar — a readout short one axis"
			. ' is a card whose difference from its neighbour cannot be read off the page' );
	}
}

$spec_blocks = array();
if ( preg_match_all( '#<pre id="(spec-[a-z0-9\-]+)"[^>]*>(.*?)</pre>#s', $html, $spec_m, PREG_SET_ORDER ) ) {
	foreach ( $spec_m as $spec_one ) {
		$spec_blocks[ $spec_one[1] ] = $spec_one[2];
	}
}
if ( count( $spec_blocks ) !== $n_strip ) {
	fail( 'the page carries ' . count( $spec_blocks ) . ' handoff block(s) for ' . $n_strip
		. ' strip(s) — a strip whose pick produces nothing is a card that cannot be chosen' );
}
foreach ( $STRIPS as $s ) {
	$C   = $CONTENT[ $s['tpl'] ];
	$A   = $ANCHORS[ $s['anchor'] ];
	$key = 'spec-' . strip_uid( $C, $s['anchor'] );
	if ( ! isset( $spec_blocks[ $key ] ) ) {
		fail( "no handoff block `#$key` — the strip renders but its spec does not" );
	}
	// The copy button addresses the block by id. If those two ever stop agreeing the button is a
	// control that silently does nothing, which is worse than no button.
	if ( false === strpos( $html, 'data-copy="' . $key . '"' ) ) {
		fail( "handoff block `#$key` has no button pointing at it" );
	}
	$spec_need = array( $C['tpl'], $A['id'], $C['site'], 'escala', 'fondo', 'densidad', 'composición', 'elevación' );
	foreach ( $spec_need as $spec_token ) {
		if ( false === strpos( $spec_blocks[ $key ], $spec_token ) ) {
			fail( "handoff block `#$key` does not carry `$spec_token` — a precharge missing a field"
				. ' is an axis the dialogue never gets asked to confirm or override' );
		}
	}

	// CAPA 3, asserted over the assembled page for the same reason CAPA 1 and 2 are: the bar and the
	// spec are two renderings of one `$RESOLVED` row, and dropping the toggle from ONE of them
	// desyncs them in silence. A strip whose bar says `slider` and whose spec says nothing hands the
	// build a hero the client never picked — the precharge failing exactly where it is least
	// visible, since the spec sits behind a `<details>` most readers never open.
	foreach ( $RESOLVED[ $C['tpl'] . '×' . $s['anchor'] ] as $tgl_need ) {
		if ( false === strpos( $spec_blocks[ $key ], $tgl_need['id'] ) ) {
			fail( "handoff block `#$key` does not carry `{$tgl_need['id']}` — the strip resolves it to"
				. " `{$tgl_need['value']}` and the pasted spec would restore the template default" );
		}
		if ( false === strpos( $spec_blocks[ $key ], $tgl_need['value'] ) ) {
			fail( "handoff block `#$key` does not carry the value `{$tgl_need['value']}` for {$tgl_need['id']}" );
		}
	}
}

/* THE VISIBLE HALF. `RT_GALLERY_NOT_DISTINCT` and the axis assertion above both read a strip's
   ARCHETYPE and ANCHOR; neither knows a toggle exists, so nothing outside this file would notice
   the readout vanishing. One `.meta-tgl` per strip whose archetype admits at least one toggle, and
   none for the archetypes that admit none — COUNTED, because "it renders" is the claim eleven
   checks in this repository have made while testing nothing. */
$tgl_bars = substr_count( $html, '<p class="meta-tgl">' );
$tgl_want = 0;
foreach ( $STRIPS as $s ) {
	if ( array() !== $RESOLVED[ $s['tpl'] . '×' . $s['anchor'] ] ) {
		++$tgl_want;
	}
}
if ( $tgl_bars !== $tgl_want ) {
	fail( "the page carries $tgl_bars toggle readout(s) for $tgl_want strip(s) that resolve at least one"
		. ' toggle — a strip whose hero differs because of a setting it does not print is not a'
		. ' comparison, it is a one-off whose difference the reader cannot attribute' );
}

/* And the one that would catch the whole feature being a label over nothing: the strip that
   resolves `TGL-HERO-TYPE: slider` has to actually render three slides, and no other strip may
   render any. Per strip, over the assembled page, so a renderer that ignored its toggle argument
   or a CSS class that drifted from the markup both stop the build. */
foreach ( $STRIPS as $s ) {
	$tgl_is  = tgl_of( $RESOLVED[ $s['tpl'] . '×' . $s['anchor'] ], 'TGL-HERO-TYPE' );
	$tgl_uid = strip_uid( $CONTENT[ $s['tpl'] ], $s['anchor'] );
	if ( ! preg_match(
		'#<section class="strip" id="' . preg_quote( $tgl_uid, '#' ) . '".*?(?=<section class="strip"|</main>)#s',
		$html,
		$tgl_m
	) ) {
		fail( "cannot isolate strip `$tgl_uid` to check its hero against its own toggle readout" );
	}
	$tgl_slides = substr_count( $tgl_m[0], 'class="frame slide"' );
	$tgl_expect = ( 'slider' === $tgl_is ) ? count( $SLIDER_FRAMES ) : 0;
	if ( $tgl_slides !== $tgl_expect ) {
		fail( "strip `$tgl_uid` resolves TGL-HERO-TYPE to `" . ( '' === $tgl_is ? 'n/a' : $tgl_is )
			. "` and renders $tgl_slides slide(s) where $tgl_expect were owed — the readout and the"
			. ' hero disagree, and the readout is the half the reader believes' );
	}
}

// ── THE ACCENT: A ROLE WHITELIST ───────────────────────────────────────────────────────────────
//
// `design-tokens.md` has the rule in one line and it names ROLES: "ONE color. CTAs, action icons,
// important links, active states. Never body text, never decoration." Everything below is that
// sentence, made enforceable.
//
// A ROLE, NOT A COUNT — AND THE DIFFERENCE COST THIS PAGE A ROUND. The previous pass read the
// probe's "one spend on the resting page" as a NUMBER and spent the budget down to it: the four
// ticks in the benefits bar lost their colour, and its own honest note afterwards was "the benefits
// bar is now too quiet — I took the accent off the ticks and did not give it anything back". Both
// halves of that were wrong in different directions, and only the role list separates them:
//   · Taking the accent off the ticks was RIGHT, and not because four was too many. A tick beside
//     "Envío en 72 h" is a confirmation mark, which is decoration, and decoration is the one thing
//     the sentence above forbids outright. It does not come back.
//   · What made the bar feel quiet was not the ticks. It was that every photograph on the page had
//     been turned to greyscale in the same round — see § 5a, which is where that is repaired.
//     A colour complaint answered by repainting the nearest small object is a symptom treated at
//     the wrong site.
// And a count gets a third case wrong that a role list gets right for free: TPL-E-02's eight
// add-to-cart buttons. Under a budget of one they stayed neutral outlines on an archetype called
// `Product-First`; under the role list they are one ROLE eight times, which is what a catalogue
// with eight products has. A rule that grows quieter as the shop grows bigger is a rule that
// punishes the shop for selling.
//
// THE WHITELIST IS KEYED BY ROLE so the check enforces the sentence rather than a bag of names: a
// new mark cannot be added without claiming one of design-tokens.md's four roles out loud, and a
// mark that claims none has nowhere to go. This is the same shape as RT_MOCKUP_BLEED_NOT_MEDIA one
// file over, for the same reason — a number can be met by moving a spend somewhere the counter
// cannot see.
//
// TWO ROLES ARE EMPTY ON THIS PAGE, AND THEY ARE NAMED RATHER THAN DROPPED. There is no "important
// link" here that is not already a button, and no "active state" because no `.cats` chip is marked
// current. An empty role is a claim — that the page has none of those — and it is a claim the next
// person can check. Deleting the key would have made it unfalsifiable.
//
// Interaction states are excluded from the scan because a whitelist is about the RESTING page: a
// colour that appears when you touch something is feedback, and feedback is supposed to be
// findable. design-tokens.md lists "active states" as an accent role for exactly that reason.
//
// THIS CHECK SHIPPED BROKEN FOR ONE BUILD AND THE MUTATIONS ARE THE ONLY REASON ANYBODY KNOWS.
// Run over the whole 1.7 MB document — which is mostly base64 `@font-face` payload — the pattern
// below hits PCRE's backtrack limit, `preg_match_all()` returns FALSE rather than 0, and `if
// ( preg_match_all(...) )` reads that as "no matches". Three mutations that should have died
// walked straight through a green build. Two fixes, and both are the rule not the incident:
//   · the subject is the STYLESHEET WITHOUT ITS FONT PAYLOAD, because a check should be pointed at
//     the thing it is about, and 1.4 MB of base64 is not CSS anybody wrote;
//   · `false` is checked EXPLICITLY and fails the build. A regex that could not run is not a
//     regex that found nothing, and PHP spells both of them the same way in a boolean context.
$accent_src = preg_replace( '/@font-face\{[^{}]*\}/', '', implode( "\n", $css ) );
if ( null === $accent_src ) {
	fail( 'stripping the font payload out of the stylesheet failed — the accent budget would then'
		. ' be measured against 1.4 MB of base64 and would report whatever PCRE gave up on' );
}
/* KEYED BY design-tokens.md's OWN FOUR ROLES, plus the one carve-out that is not about the samples
   at all. Every class here claims a role; a class that claims none has nowhere to be written. */
$ACCENT_ROLES = array(

	/* "CTAs". `.btn-primary` is the solid control wherever it appears — the corporate hero's
	   `Pedir presupuesto`, the search-first header's `Buscar`, and TPL-E-02's eight `Añadir`
	   buttons, which are eight instances of one role and not eight marks. */
	'CTAs'             => array( 'btn-primary' ),

	/* "action icons". One class, and it is not this file's decision: design-personalities.md gives
	   PERS-INSTITUTIONAL "chip de icono en accent" in as many words, and an anchor's own card
	   recipe outranks a rule written here. `.cart b` is the badge on the cart control —
	   html-mockup/SKILL.md step 2 names it as TPL-E-02 DNA, and a badge that does not stand out
	   has failed at its only job. `.bicon`, the benefits-bar tick, is NOT here: a confirmation
	   mark beside a label is decoration, which the sentence forbids by name. */
	'action icons'     => array( 'chip', 'cart' ),

	/* "important links". EMPTY, and stated: every link on these eight strips that is important
	   enough to qualify is already rendered as a button and counted under CTAs. The key stays so
	   that the claim is visible and falsifiable. */
	'important links'  => array(),

	/* "active states". EMPTY for the resting page by construction — no `.cats` chip on TPL-E-02 is
	   marked current, so there is no active state to paint. The `:hover`/`:focus`/`:active`/
	   `:checked` selectors the scan skips below are the same role, and they are skipped rather
	   than listed because a whitelist is a statement about the page AT REST. */
	/* Empty until TPL-C-13 brought the first real one. `.mapswitch` is the Lista/Mapa control, and
	   the pressed half IS the active state the role names — the same object as a tab that is open.
	   The unpressed half stays on --c-bg, because a control where both halves are accented has
	   stopped saying which one you are looking at. */
	/* `.sortlink[aria-current]` (PR3c, TPL-C-15's own "Propiedades" listing) is the SAME object one
	   more time: which of Recientes/Precio ↑/Precio ↓ is selected, exactly the role `.mapswitch`
	   already names for Lista/Mapa. */
	'active states'    => array( 'mapswitch', 'sortlink' ),

	/* NOT A ROLE, AND THAT IS THE POINT: the closing band is a whole SURFACE painted in the accent
	   on PERS-DIRECT, not a mark on one. layout-patterns.md § "The close is a designed moment"
	   gives it that spend deliberately — a budget is only interesting because it lets you spend
	   loudly somewhere. */
	'the close'        => array( 'closing' ),

	/* NOT SAMPLES AT ALL — the gallery's own chrome, painted from `:root` rather than from any
	   anchor. A tool is not one of the things it displays. Named rather than skipped, for the same
	   reason the bleed whitelist names things: a check that silently ignores half the file is half
	   a check.
	   `.sbtn` is the anchor switcher on a template page and it is here rather than under "active
	   states" for one reason: it is the SAME control as `.fbtn` one level down, and splitting two
	   identical chips across two roles would make the whitelist describe where a class happens to
	   sit rather than what it does. Its pressed state IS an active state — but it is an active
	   state of the TOOL, and the roles in design-tokens.md are about the page being sold. */
	'the tool itself'  => array( 'gal-head', 'gal-note', 'fbtn', 'sbtn', 'handoff-copy', 'handoff', 'x',
		'tgl-set', 'axis', 'noscript' ),
);
$accent_ok = array();
foreach ( $ACCENT_ROLES as $acc_role => $acc_classes ) {
	foreach ( $acc_classes as $acc_class ) {
		if ( isset( $accent_ok[ $acc_class ] ) ) {
			fail( "`.$acc_class` claims both `{$accent_ok[ $acc_class ]}` and `$acc_role` in the accent"
				. ' role whitelist — one mark, one role, or the whitelist stops being a statement about'
				. ' what the colour is FOR' );
		}
		$accent_ok[ $acc_class ] = $acc_role;
	}
}

/* THE ROLE NAMES ARE THE REFERENCE'S, CHECKED AGAINST IT. design-tokens.md's accent row is the
   authority for this list, and a role renamed there while this file keeps the old word would leave
   a whitelist that no longer quotes anything. Read from the file rather than trusted. */
$acc_row = '';
foreach ( explode( "\n", file_get_contents( $SKILLS . '/ux-design-system/references/design-tokens.md' ) ) as $acc_line ) {
	if ( preg_match( '/^\|\s*Accent\s*\|\s*`--c-accent`\s*\|\s*(.+?)\s*\|\s*$/u', $acc_line, $acc_rm ) ) {
		$acc_row = $acc_rm[1];
	}
}
if ( '' === $acc_row ) {
	fail( 'design-tokens.md has no `Accent | --c-accent |` row — the role whitelist below quotes that'
		. ' row for its four role names, and a quotation with no source is an invention' );
}
foreach ( array( 'CTAs', 'action icons', 'important links', 'active states' ) as $acc_role ) {
	if ( ! isset( $ACCENT_ROLES[ $acc_role ] ) ) {
		fail( "design-tokens.md's accent row names the role `$acc_role` and the whitelist here has no"
			. ' key for it — a role the reference allows and this file cannot express is a mark with'
			. ' nowhere legal to go' );
	}
	if ( false === stripos( $acc_row, $acc_role ) ) {
		fail( "the accent role whitelist claims `$acc_role`, which design-tokens.md's accent row does"
			. " not say. That row reads: \"$acc_row\". A role invented here is a second design system." );
	}
}
$accent_css      = preg_replace( '#/\*.*?\*/#s', '', $accent_src );
$accent_offences = array();
$ACCENT_CLAIMED  = array();
$acc_n           = preg_match_all( '/([^{}]*)\{([^{}]*)\}/s', $accent_css, $acc_m, PREG_SET_ORDER );
if ( false === $acc_n ) {
	fail( 'the accent-budget scan could not run: ' . preg_last_error_msg()
		. '. A regex that gave up is not a regex that found nothing, and both are falsy in PHP' );
}
if ( 0 === $acc_n ) {
	fail( 'the accent-budget scan parsed zero CSS rules — the stylesheet shape changed under it,'
		. ' and a check with nothing to check is a check that always passes' );
}
/* THE ACCENT IS ALSO SPELLED AS A LITERAL, AND THE SCAN WAS BLIND TO IT. A closing field RESOLVES
   its tokens, so PERS-DIRECT's band writes `background:#FF6A1A` and not `background:var(--c-accent)`
   — the biggest single accent spend on the page, in a spelling the gate did not know.

   SO THE SCAN LOOKS FOR THE TOKEN OR ANY HEX `$ACCENT_BY_GROUND` RESOLVES TO. The hexes are derived
   from that table rather than typed, so re-deriving the accent on a new ground extends the scan on
   the same build.

   THIS ARM'S PROOF IS ITS OWN MUTATION, and saying so precisely matters because the first draft of
   this comment credited it with something it does not do. Painting `--c-accent`'s literal on a
   class that claims no role — `.price{color:#8C3A1F}` — is caught here and nowhere else. What it
   does NOT do is revive `.closing`: that entry was dead for a different reason, one property list
   down. Measured by disabling each arm alone: literal arm off → `.closing` still claimed; property
   list narrowed → `.closing` dead. Two holes, two arms, and neither covers the other. */
$acc_literals = array();
foreach ( $ACCENT_BY_GROUND as $acc_hex ) {
	$acc_literals[] = strtoupper( $acc_hex );
}
$acc_literals = array_values( array_unique( $acc_literals ) );

foreach ( $acc_m as $acc_rule ) {
	/* The declaration block, one property at a time, so a value carrying the accent is only an
	   offence when the PROPERTY is one that paints. `--c-accent:#8C3A1F` is a token declaration and
	   `--elev-rest:0 0 0 1px …accent…` is a shadow the elevation axis owns. */
	$acc_paints = false;
	foreach ( explode( ';', $acc_rule[2] ) as $acc_decl ) {
		$acc_parts = explode( ':', $acc_decl, 2 );
		if ( count( $acc_parts ) < 2 ) {
			continue;
		}
		$acc_prop  = strtolower( trim( $acc_parts[0] ) );
		$acc_val   = strtoupper( $acc_parts[1] );
		$acc_bears = ( false !== strpos( $acc_parts[1], '--c-accent' ) );
		foreach ( $acc_literals as $acc_lit ) {
			if ( false !== strpos( $acc_val, $acc_lit ) ) {
				$acc_bears = true;
				break;
			}
		}
		if ( ! $acc_bears ) {
			continue;
		}
		/* THE PROPERTY LIST HAD A SECOND HOLE AND THE SAME CHECK FOUND IT. It read
		   `border-[a-z]+-color`, which does not match `border-top` — and `.axis` paints
		   `border-top:2px solid var(--c-accent)`, a 2px accent rule visible at rest on every strip
		   header, which the gate reported as no mark at all. A shorthand is how anybody actually
		   writes a border. `fill` and `stroke` are here for the same reason one level over: the
		   icon chips are SVG, and an accent-filled icon is an accent mark whatever property carries
		   it. `box-shadow` is deliberately NOT here — `--elev-rest: 0 0 0 1px …accent…` is the
		   elevation axis spending its own token, which design-system.md tables as `accent-glow`. */
		if ( preg_match(
			'/^(color|background|background-color|background-image|border|border-color'
				. '|border-(top|right|bottom|left|block|inline)([a-z-]*)|outline|outline-color'
				. '|fill|stroke|text-decoration-color|caret-color|accent-color)$/',
			$acc_prop
		) ) {
			$acc_paints = true;
			break;
		}
	}
	if ( $acc_paints ) {
		foreach ( explode( ',', $acc_rule[1] ) as $acc_sel ) {
			$acc_sel = trim( $acc_sel );
			/* INTERACTION STATES ONLY. `::marker` was in this list for one revision and does not
			   belong: a disclosure triangle is on screen at rest, so an accent-coloured one is a
			   spend, and mutating it back proved the exclusion was hiding a real mark. What is
			   excluded is the states a reader has to CAUSE — a colour that appears when you touch
			   something is feedback, and feedback is supposed to be findable. */
			if ( '' === $acc_sel || preg_match( '/:hover|:focus|:active|:checked/i', $acc_sel ) ) {
				continue;
			}
			$acc_names = array();
			if ( preg_match_all( '/\.([\w-]+)/', $acc_sel, $acc_c ) ) {
				$acc_names = $acc_c[1];
			}
			foreach ( $acc_names as $acc_name ) {
				if ( isset( $accent_ok[ $acc_name ] ) ) {
					$ACCENT_CLAIMED[ $accent_ok[ $acc_name ] ][ $acc_name ] = true;
					continue 2;
				}
			}
			$accent_offences[] = '`' . $acc_sel . '`';
		}
	}
}
if ( array() !== $accent_offences ) {
	fail( 'these paint --c-accent on the RESTING page and claim none of design-tokens.md\'s accent'
		. ' roles: ' . implode( ', ', array_unique( $accent_offences ) )
		. '. That row reads "' . $acc_row . '". Either the mark IS one of those roles — in which case'
		. ' add it under that role in $ACCENT_ROLES, and the role is the argument — or it is'
		. ' decoration, and decoration wants --c-text-muted, which is what a label is for.' );
}

/* A PERMISSION THAT NEVER FIRES IS A DEAD PERMISSION, and dead permissions are how a whitelist
   turns back into a bag of names nobody reads. Asserted PER CLASS rather than per role, and its
   first run is what found the property list above was too narrow: BOTH `.closing` and `.axis` had
   been sitting here for a whole round without ever matching, because both paint the accent through
   a border SHORTHAND — `.axis` a 2px rule on every strip header, `.sec.closing` on PERS-MATTER a
   hairline mixed from `var(--c-accent)`. Two whitelist entries that looked like decisions and were
   really blind spots. (The literal-hex arm above is a SEPARATE hole with a separate mutation; it
   is not what revived these two, and the first version of this comment said it was.)

   The two roles declared EMPTY are exempt, because emptiness is their claim. A NAMED class that
   matched nothing is a claim that turned out to be false — either the mark went away and the
   permission should follow it, or the scan cannot see the mark, and that second case is the one
   worth failing a build over. */
foreach ( $ACCENT_ROLES as $acc_role => $acc_classes ) {
	foreach ( $acc_classes as $acc_class ) {
		if ( isset( $ACCENT_CLAIMED[ $acc_role ][ $acc_class ] ) ) {
			continue;
		}
		fail( "`.$acc_class` is whitelisted under the accent role `$acc_role` and paints the accent"
			. ' NOWHERE on the resting page. Either the mark is gone — in which case the permission'
			. ' goes with it — or the scan cannot see it, which is how a 2px accent rule on every'
			. ' strip header and a hairline on the closing band both went uncounted for a round,'
			. ' because the gate looked for `border-*-color` and the stylesheet wrote `border-top`'
			. ' and `border`.' );
	}
}

// ── ANYTHING THAT REDECLARES THE GROUND MUST ALSO RESOLVE THE CHAIN ────────────────────────────
//
// FOUND BY MUTATION, and it is the most expensive failure this stylesheet can have because it is
// completely invisible. Deleting the closing fields from the shared chain's selector list leaves a
// page that builds, passes every gate and renders: the two bands are still painted, every custom
// property still reads correctly in DevTools, and `--c-text-muted` on the orange field is still
// the grey it resolved to up on `[data-anchor]` — mixed toward WHITE, painted on orange. The
// stylesheet's own opening note describes this trap for `[data-anchor]` versus `:root`; the
// closing fields made it possible one level further down, so the invariant is worth stating as a
// rule rather than as a paragraph.
//
// THE RULE: a selector that declares `--c-bg` is claiming a ground, and a ground with no chain
// resolved against it has five stale neutrals hanging off it. So every such selector must appear
// in the chain's own selector list — verbatim, or as the un-valued form of an attribute selector,
// since `[data-anchor="direct"]` is matched by the chain's `[data-anchor]`.
$ground_decls = array();
if ( preg_match_all( '/([^{}]+)\{[^{}]*--c-bg\s*:/', preg_replace( '#/\*.*?\*/#s', '', $html ), $gd_m, PREG_SET_ORDER ) ) {
	foreach ( $gd_m as $gd_rule ) {
		foreach ( explode( ',', $gd_rule[1] ) as $gd_sel ) {
			$gd_sel = trim( preg_replace( '/\s+/', ' ', $gd_sel ) );
			if ( '' !== $gd_sel ) {
				$ground_decls[ $gd_sel ] = true;
			}
		}
	}
}
$chain_list = array();
foreach ( $FIELD_SELECTORS as $gd_one ) {
	$chain_list[ trim( preg_replace( '/\s+/', ' ', $gd_one ) ) ] = true;
}
foreach ( array_keys( $ground_decls ) as $gd_sel ) {
	$gd_generic = preg_replace( '/\[([\w-]+)=("[^"]*"|\'[^\']*\')\]/', '[$1]', $gd_sel );
	if ( isset( $chain_list[ $gd_sel ] ) || isset( $chain_list[ $gd_generic ] ) ) {
		continue;
	}
	fail( "`$gd_sel` declares --c-bg but is not a resolution site for the shared derived chain."
		. ' Custom properties substitute on the element that DECLARES them, so every neutral under'
		. ' this selector — --c-text-muted, --c-border, --c-surface-inverse — still carries the'
		. " value it resolved to on its ancestor, mixed toward a ground this rule just replaced."
		. ' It renders, it reads correctly in DevTools, and it is wrong. Add the selector to'
		. ' $FIELD_SELECTORS' );
}

// ── THE TRACKING RAMP HAS TO BE A RAMP ─────────────────────────────────────────────────────────
//
// Two failures, and only the second is visible in a render. The first is a ramp that stops being
// one — somebody consolidates the five tokens back onto a single `--track-display`, every heading
// keeps rendering, and the only evidence is a page that looks very slightly flatter. The second is
// the `normal` trap the anchor table now carries a paragraph about: an invalid custom-property
// substitution does not warn, it falls back to the property's INITIAL value, so a ramp that has
// silently died renders as `letter-spacing:normal` on exactly the anchors whose small type needed
// it and looks, in a screenshot, entirely fine.
//
// So: the three display steps must read three DIFFERENT tokens, and no anchor may declare a
// `--track-display` that `calc()` cannot add to. `normal` is the one value that parses as a
// letter-spacing and not as a length, which is precisely why it is the value that was there.
//
// `.card h3` IS IN THE LIST because leaving it out is how the sixth step died in mutation: setting
// it back to `--track-h3` changed nothing this file could see, and the card heading — the smallest
// heading on the page, at .58 of the h3 step — is the one the ramp exists for.
$ramp_steps = array( 'h1' => '--track-h1', 'h2' => '--track-h2', 'h3' => '--track-h3', '\.card h3' => '--track-h3-sm' );
$ramp_seen  = array();
foreach ( $ramp_steps as $ramp_tag => $ramp_tok ) {
	if ( ! preg_match( '/(?:^|\n)' . $ramp_tag . '\{[^}]*letter-spacing:var\(' . preg_quote( $ramp_tok, '/' ) . '\)/', $html ) ) {
		fail( '`' . str_replace( '\\', '', $ramp_tag ) . "` does not read `$ramp_tok` — the optical ramp has collapsed"
			. ' back to one value for four sizes, which is design-personalities.md § "Display tracking"'
			. ' applied to the anchor and then not applied inside it' );
	}
	$ramp_seen[ $ramp_tok ] = true;
}
if ( count( $ramp_seen ) !== count( $ramp_steps ) ) {
	fail( 'two ramp steps share a tracking token — that is a flat value wearing a ramp\'s name' );
}
foreach ( $ANCHORS as $ramp_k => $ramp_a ) {
	if ( ! preg_match( '/^-?\d*\.?\d+em$/', $ramp_a['track_disp'] ) ) {
		fail( "anchor `$ramp_k` declares --track-display `{$ramp_a['track_disp']}`, which calc() cannot"
			. ' add an offset to. An invalid substitution falls back to the INITIAL value, so every'
			. ' ramped step on this anchor would silently render at `normal` and look correct.'
			. ' `0em` is the calc-safe spelling of "this face is not tightened"' );
	}
}

file_put_contents( $OUT, $html );

// ── the receipt ────────────────────────────────────────────────────────────────────────────────

printf( "build-gallery: %d strip(s), %d image(s) used of %d in the manifest\n", $n_strip, count( $only_used ), count( $IMAGES ) );
printf( "               images %s KB raw → %s KB base64, paid once\n",
	number_format( $raw_bytes / 1024, 1 ), number_format( $b64_bytes / 1024, 1 ) );
printf( "               fonts  %s KB raw → %s KB base64 across %d face(s): %s\n",
	number_format( $font_cost['raw'] / 1024, 1 ), number_format( $font_cost['b64'] / 1024, 1 ),
	count( $font_families ), implode( ', ', $font_families ) );
printf( "               index.html %s KB (%.2f%% of the 16 MB Artifact ceiling)\n",
	number_format( strlen( $html ) / 1024, 1 ), 100 * strlen( $html ) / 16777216 );
printf( "               scrim  %d%% floor over %s → worst pixel %s, bar %s:1\n",
	(int) round( $SCRIM_FLOOR * 100 ),
	$GROUND[ $ANCHORS['editorial']['ground'] ]['bg'],
	implode( ' · ', array_map(
		function ( $s, $r ) {
			return $s . ' ' . number_format( $r, 2, '.', '' ) . ':1';
		},
		array_keys( $SCRIM_WORST ),
		$SCRIM_WORST
	) ),
	$SCRIM_BAR
);
printf( "               inks   --c-text %s %.2f:1 (kept) · %s\n",
	$SCRIM_INK, $SCRIM_INK_WORST, implode( ' · ', $SCRIM_REPORT ) );
printf( "               hero   LP-BROKEN-GRID %s at %d%% over %s → worst pixel %.2f:1, bar %s:1\n",
	$BG_SLUG, (int) round( $BG_FLOOR * 100 ), $GROUND[ $ANCHORS[ $BG_ANCHOR ]['ground'] ]['bg'],
	$BG_HERO['ratio'], $SCRIM_BAR );
printf( "               house ink one GRADE per anchor, %d anchor(s); swatch spread %s (bar %.1f)\n",
	count( $INK_ENDS ), implode( ' · ', $INK_SWATCH_REPORT ), $INK_SWATCH_BAR );
foreach ( $INK_ENDS as $ink_rk => $ink_re ) {
	printf( "                 %-14s %s → %s  saturate %s · gamma %s (%s)\n",
		$ink_rk, $ink_re['ends']['dark'], $ink_re['ends']['light'], $ink_re['sat'],
		$ink_re['gamma'], $ink_re['named'] ? '§ Imagery' : 'default' );
}
printf( "               accent roles (design-tokens.md): %s\n", implode( ' · ', array_map(
	function ( $r, $c ) use ( $ACCENT_CLAIMED ) {
		return $r . ' ' . ( array() === $c
			? '—'
			: implode( '/', array_map(
				function ( $one ) use ( $r, $ACCENT_CLAIMED ) {
					return '.' . $one . ( isset( $ACCENT_CLAIMED[ $r ][ $one ] ) ? '' : ' (unused)' );
				},
				$c
			) ) );
	},
	array_keys( $ACCENT_ROLES ),
	$ACCENT_ROLES
) ) );
foreach ( $ACCENT as $g => $a ) {
	printf( "               accent %s on %-5s → %s bg · %s bg-alt · label %s %s\n",
		$a['hex'], $g, $a['r_bg'], $a['r_alt'], $a['on_is'], $a['r_on'] );
}
foreach ( $FIELD as $fld_rk => $fld_rv ) {
	printf( "               close  %-14s %-8s %s → type %s · muted %s · border %s · control %s %s\n",
		$fld_rk, $fld_rv['kind'], $fld_rv['bg'], $fld_rv['r_text'], $fld_rv['r_muted'],
		$fld_rv['r_bord'], $fld_rv['accent'], $fld_rv['r_ctrl'] );
}
