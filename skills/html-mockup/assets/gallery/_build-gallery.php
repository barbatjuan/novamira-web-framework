<?php
/**
 * _build-gallery.php — the template gallery, GENERATED rather than written.
 *
 * Run:  php skills/html-mockup/assets/gallery/_build-gallery.php
 * Out:  skills/html-mockup/assets/gallery/index.html   (committed alongside this file)
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

// ─────────────────────────────────────────────────────────────── 4 · the four anchors
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
// optical ramp below adds a per-step offset to this token on all four anchors. Writing `normal`
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
// Piedra Valdes, cantería in Lleida — through ten architectures and four anchors. That is forty
// configurations of one identity, and it is the defect this table exists to end: a catalogue whose
// every entry is the same stonemason cannot answer "quiero la de restaurante", because none of
// them is one. The architecture varied and the identity never did.
//
// So a brand supplies exactly what an anchor deliberately does not: its own GROUND, its own
// ACCENT, its own TYPE PAIRING and its own PHOTOGRAPHS. It BORROWS scale, density, composition and
// elevation from the anchor it starts on, so the five axes keep meaning what they meant and the
// anchor stops being a variant you flip between — it becomes the preset a brand starts from.
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
$BRANDS = array(
	'terrazza' => array(
		'name'   => 'CASA TERRAZZA',
		'sector' => 'Restaurante',
		/* Ground: the room's own light. Brick, wood and candles photograph warm and DARK, and a
		   restaurant read on a phone at 21:40 is read in a dark room — a paper ground would be the
		   one surface in the whole design that argues with its own photographs. */
		'ground' => array( 'bg' => '#171310', 'alt' => '#211B16', 'text' => '#F4EBDF' ),
		/* Accent: brass. It is the metal actually in the photographs — the lamps, the cutlery, the
		   chair frames — which is the only defensible way to pick one. Measured below like any other. */
		'accent' => '#D9A441',
		'font_1' => "'Fraunces', Georgia, 'Times New Roman', serif",
		'font_2' => "'Source Sans 3', system-ui, sans-serif",
		'ink'    => array( 'sat' => 0.88, 'gamma' => 0.18 ),
	),

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

	/* AURIA · lanzamiento de un modelo. Casi negro AZULADO, frente al casi negro CÁLIDO de
	   Terrazza: los dos son oscuros y no se parecen, que es exactamente lo que un catálogo tiene
	   que poder demostrar. */
	'auria'  => array(
		'name'   => 'AURIA',
		'sector' => 'Automoción · concesión',
		'ground' => array( 'bg' => '#0A0D12', 'alt' => '#141A23', 'text' => '#EDF1F6' ),
		'accent' => '#5AA2FF',
		'font_1' => "'Archivo Expanded', 'Arial Black', system-ui, sans-serif",
		'font_2' => "'Inter Tight', system-ui, sans-serif",
		'ink'    => array( 'sat' => 0.60, 'gamma' => 0.34 ),
	),

	/* TALLER BERGARA · tarifa. Papel cálido de taller y naranja de señalización. Es el único de
	   los tres cuyo argumento es un NÚMERO y no una fotografía, así que su fondo es el más plano
	   y su acento el más ruidoso: el precio tiene que poder gritar sin ayuda de una imagen. */
	'bergara' => array(
		'name'   => 'TALLER BERGARA',
		'sector' => 'Automoción · taller',
		'ground' => array( 'bg' => '#FBF8F3', 'alt' => '#EFEAE1', 'text' => '#1B1815' ),
		'accent' => '#A63D12',
		'font_1' => "'Archivo', system-ui, sans-serif",
		'font_2' => "'DM Sans', system-ui, sans-serif",
		'ink'    => array( 'sat' => 0.80, 'gamma' => 0.16 ),
	),

	/* CLÍNICA ARBEA · odontología general. Blanco frío casi puro y verde azulado. Es el fondo más
	   CLARO de todo el catálogo a propósito: una consulta sanitaria compite con el miedo, y el miedo
	   no se calma con contraste alto ni con acentos que gritan. */
	'arbea'  => array(
		'name'   => 'CLÍNICA ARBEA',
		'sector' => 'Salud · odontología',
		'ground' => array( 'bg' => '#FBFCFD', 'alt' => '#EDF2F4', 'text' => '#0F1E24' ),
		'accent' => '#0E6E6E',
		'font_1' => "'Source Sans 3', system-ui, sans-serif",
		'font_2' => "'Inter Tight', system-ui, sans-serif",
		'ink'    => array( 'sat' => 0.58, 'gamma' => 0.10 ),
	),

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
	'urgencia' => array(
		'name'   => 'URGENCIA DENTAL',
		'sector' => 'Salud · guardia 24 h',
		'ground' => array( 'bg' => '#FFFFFF', 'alt' => '#F3F4F5', 'text' => '#131416' ),
		'accent' => '#C22217',
		'font_1' => "'Archivo', system-ui, sans-serif",
		'font_2' => "'Source Sans 3', system-ui, sans-serif",
		'ink'    => array( 'sat' => 0.74, 'gamma' => 0.22 ),
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

/* THE ACCENT'S SHARE OF THE SHADOW INK. One number for all four anchors, because the per-anchor
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
	/* TPL-C-07 · Stock / Ocasión — de su propio § "Toggles admitidos". */
	'TPL-C-07' => array(
		'TGL-FINANCE'      => array( 'ask' => '¿Simulador de cuota?', 'default' => 'sí', 'options' => array( 'sí', 'no' ) ),
		'TGL-BADGES'       => array( 'ask' => '¿Barra de garantías?', 'default' => 'sí', 'options' => array( 'sí', 'no' ) ),
		'TGL-TESTIMONIALS' => array( 'ask' => '¿Reseñas?', 'default' => 'sí', 'options' => array( 'sí', 'no' ) ),
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
	'TPL-C-10-arbea' => array(
		'tpl'          => 'TPL-C-10-arbea',
		'head_mode'    => 'tight',
		'arch'         => 'TPL-C-10',
		'brand'        => 'arbea',
		'brand_name'   => 'Clínica Arbea',
		'brand_sector' => 'Salud · odontología',
		'tpl_name'     => 'Clínica / Tratamientos',
		'site'         => 'corporate',
		'site_es'      => 'Corporativa',
		'fits'         => 'Clínicas dentales, dermatología, fisioterapia, podología, veterinaria',
		'dna'          => 'COMP-TREATMENT-CARDS · COMP-BEFORE-AFTER · COMP-TEAM',
		'wire'         => 'COMP-HEADER · COMP-HERO · COMP-TREATMENT-CARDS · COMP-BEFORE-AFTER · COMP-TEAM · COMP-INSURANCE · COMP-BOOKING · COMP-FOOTER',
		'nav'          => array( 'Tratamientos', 'Equipo', 'Coberturas' ),
		'nav_cta'      => 'Pedir cita',
		'phone'        => '945 00 00 00',
		'hero'         => array(
			'eyebrow' => 'Odontología general y estética · Vitoria-Gasteiz',
			'h1'      => 'Le contamos qué le vamos a hacer antes de hacerlo',
			'lede'    => 'Cada tratamiento con su duración, sus sesiones y si lleva anestesia. Sin presupuestos que aparecen a mitad y sin tratamientos que se alargan sin avisar.',
			'cta_1'   => 'Pedir cita',
			'cta_2'   => '945 00 00 00',
			'img'     => 'arbea-consulta',
		),
		'treatments'   => array(
			'eyebrow' => 'Tratamientos',
			'h2'      => 'Seis cosas que hacemos, con sus números',
			'note'    => 'Los precios «desde» corresponden al caso simple; la primera visita incluye radiografía y presupuesto cerrado por escrito.',
			'items'   => array(
				array( 'img' => 'arbea-material', 'h3' => 'Empaste', 'p' => 'Composite del color del diente, en una sola visita.',
					'facts' => array( '35 min', '1 sesión', 'Con anestesia', 'desde 65 €' ) ),
				array( 'img' => 'arbea-micro', 'h3' => 'Endodoncia', 'p' => 'Con microscopio: se ve el conducto en vez de buscarlo.',
					'facts' => array( '75 min', '1–2 sesiones', 'Con anestesia', 'desde 190 €' ) ),
				array( 'img' => 'arbea-radio', 'h3' => 'Implante', 'p' => 'Colocación, cicatrización y corona sobre él.',
					'facts' => array( '60 min', '3 sesiones', 'Con anestesia', 'desde 1.100 €' ) ),
				array( 'img' => 'arbea-antes', 'h3' => 'Blanqueamiento', 'p' => 'En clínica con férula para casa las dos semanas siguientes.',
					'facts' => array( '50 min', '1 sesión', 'Sin anestesia', 'desde 290 €' ) ),
				array( 'img' => 'arbea-despues', 'h3' => 'Limpieza y mantenimiento', 'p' => 'Con revisión de encías y consejo de cepillado.',
					'facts' => array( '40 min', '1 sesión', 'Sin anestesia', '59 €' ) ),
				array( 'img' => 'arbea-doctor', 'h3' => 'Primera visita', 'p' => 'Radiografía, exploración y presupuesto por escrito.',
					'facts' => array( '30 min', '1 sesión', 'Sin anestesia', 'Gratuita' ) ),
			),
		),
		'cases'        => array(
			'eyebrow' => 'Un caso',
			'h2'      => 'Antes y después, con las dos fechas',
			'lede'    => 'Rehabilitación de sector anterior: dos implantes y cuatro carillas. El paciente autorizó la publicación de las imágenes.',
			'before'  => array( 'img' => 'arbea-antes', 'label' => 'Antes', 'date' => '14 de marzo de 2025' ),
			'after'   => array( 'img' => 'arbea-despues', 'label' => 'Después', 'date' => '2 de julio de 2025' ),
			'note'    => 'Tres meses y medio, seis sesiones. Los resultados dependen de cada caso y se explican en la primera visita.',
		),
		'team'         => array(
			'eyebrow' => 'Quién le atiende',
			'h2'      => 'Tres personas, con su número de colegiado',
			'items'   => array(
				array( 'img' => 'arbea-doctor', 'name' => 'Dr. Íñigo Arbea', 'role' => 'Odontología general y endodoncia', 'lic' => 'Col. 01-0001234' ),
				array( 'img' => 'arbea-micro', 'name' => 'Dra. Leire Otxoa', 'role' => 'Implantología y cirugía', 'lic' => 'Col. 01-0004567' ),
				array( 'img' => 'arbea-radio', 'name' => 'Dr. Pablo Mendive', 'role' => 'Estética dental y prótesis', 'lic' => 'Col. 01-0007890' ),
			),
		),
		'insurance'    => array(
			'eyebrow' => 'Coberturas',
			'h2'      => 'Con qué seguros trabajamos',
			'items'   => array( 'Adeslas', 'Sanitas', 'DKV', 'Asisa', 'Mapfre Salud', 'Caser' ),
			'note'    => 'Financiación hasta 24 meses sin intereses a partir de 600 €, con estudio previo. TAE 0 % — el resto de plazos se explican por escrito antes de firmar.',
		),
		'booking'      => array(
			'eyebrow' => 'Cita',
			'h2'      => 'Elija día y hora',
			'lede'    => 'La primera visita es gratuita e incluye radiografía y presupuesto por escrito. Dura media hora.',
			'fields'  => array(
				array( 'nombre', 'Nombre', 'text' ),
				array( 'tel', 'Teléfono', 'tel' ),
			),
			'day_lbl'  => 'Día',
			'days'     => array( 'Lunes 22', 'Martes 23', 'Miércoles 24', 'Jueves 25', 'Viernes 26' ),
			'slot_lbl' => 'Hora',
			'slots'    => array( '09:30', '11:00', '12:30', '16:00', '18:00' ),
			'submit'   => 'Pedir cita',
			'small'    => 'Le confirmamos por teléfono el mismo día. Si necesita cambiarla, avise con 24 horas y no cuenta.',
		),
		'footer'       => array(
			'tag'   => 'Clínica Arbea · Calle Postas 32, 01004 Vitoria-Gasteiz · 945 00 00 00 · Reg. sanitario 01-C21-0000',
			'links' => array( 'Tratamientos', 'Equipo', 'Privacidad' ),
			'legal' => 'Clínica Arbea SL · Maqueta interna NovaMira, no publicada.',
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
	'TPL-C-12-urgencia' => array(
		'tpl'          => 'TPL-C-12-urgencia',
		'head_mode'    => 'tight',
		'arch'         => 'TPL-C-12',
		'brand'        => 'urgencia',
		'brand_name'   => 'Urgencia Dental',
		'brand_sector' => 'Salud · guardia 24 h',
		'tpl_name'     => 'Urgencias / Hoy',
		'site'         => 'corporate',
		'site_es'      => 'Corporativa',
		'fits'         => 'Urgencias dentales, cerrajería, fontanería, grúa, veterinaria 24 h',
		'dna'          => 'COMP-URGENT-BAR · COMP-SYMPTOM-TRIAGE · COMP-WAIT-PROMISE',
		'wire'         => 'COMP-HEADER · COMP-URGENT-BAR · COMP-SYMPTOM-TRIAGE · COMP-WAIT-PROMISE · COMP-TEAM · COMP-MAP-NAP · COMP-FOOTER',
		'nav'          => array( 'Qué hacemos', 'Cómo llegar' ),
		'nav_cta'      => 'Llamar ahora',
		'phone'        => '944 00 00 00',
		'urgent'       => array(
			'state'   => 'Abierto ahora',
			'h1'      => 'Guardia dental en Bilbao, hoy y sin cita',
			'next'    => 'Próximo hueco: hoy a las 19:40',
			'cta'     => 'Llamar al 944 00 00 00',
			'note'    => 'De 09:00 a 01:00 todos los días del año, festivos incluidos.',
		),
		'triage'       => array(
			'eyebrow' => 'Qué le pasa',
			'h2'      => 'Cuatro cosas que resolvemos hoy mismo',
			'items'   => array(
				array( 'Se me ha roto un diente', 'Reconstrucción provisional en la misma visita; la definitiva se cita después.', '40 min' ),
				array( 'Me duele mucho una muela', 'Radiografía, apertura y drenaje si hace falta. El dolor se va antes de que se vaya usted.', '50 min' ),
				array( 'Se me ha caído una corona o un empaste', 'Se recementa en el momento si la trae. Tráigala aunque esté partida.', '30 min' ),
				array( 'Se me ha salido un bracket y me clava', 'Se recoloca o se recorta el arco. Es lo más rápido de todo.', '20 min' ),
			),
			'note'    => 'Si ha habido un golpe con pérdida de un diente entero, llame ANTES de venir: hay media hora para reimplantarlo y le decimos qué hacer con él por teléfono.',
		),
		'wait'         => array(
			'eyebrow' => 'Espera y precio',
			'h2'      => 'Las dos cosas que nadie publica',
			'items'   => array(
				array( '22 minutos', 'de espera media en la última semana, contando fines de semana' ),
				array( '49 €', 'la visita de urgencia, con radiografía incluida' ),
			),
			'note'    => 'La visita se descuenta del tratamiento si lo hace aquí. Lo que cueste el tratamiento se lo decimos antes de empezarlo, no al terminar.',
		),
		'team'         => array(
			'eyebrow' => 'De guardia',
			'h2'      => 'Quién está hoy',
			'items'   => array(
				array( 'img' => 'urgencia-box', 'name' => 'Dra. Nerea Zubiaur', 'role' => 'Odontóloga de guardia · turno de tarde', 'lic' => 'Col. 48-0002345' ),
				array( 'img' => 'urgencia-turno', 'name' => 'Jon Aretxaga', 'role' => 'Auxiliar · recepción y radiología', 'lic' => 'Nº reg. 48-A-0678' ),
			),
		),
		'nap'          => array(
			'eyebrow' => 'Cómo llegar',
			'h2'      => 'Indautxu, Bilbao',
			'addr'    => array( 'Alameda de Urquijo 68, bajo', '48013 Bilbao · Bizkaia' ),
			'phone'   => '944 00 00 00',
			'mail'    => 'guardia@urgenciadental.example',
			'hours'   => array(
				array( 'Todos los días', '09:00 – 01:00' ),
				array( 'Festivos', '09:00 – 01:00' ),
				array( 'Fuera de ese horario', 'Llame igualmente' ),
			),
			'note'    => 'Metro Indautxu a 200 m. Portal a pie de calle, sin escalones. Hay parking público en la misma manzana.',
			'img'     => 'urgencia-box',
		),
		'footer'       => array(
			'tag'   => 'Urgencia Dental · Alameda de Urquijo 68, 48013 Bilbao · 944 00 00 00 · Reg. sanitario 48-C21-0000',
			'links' => array( 'Qué hacemos', 'Cómo llegar', 'Privacidad' ),
			'legal' => 'Urgencia Dental Bilbao SL · Maqueta interna NovaMira, no publicada.',
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
	'TPL-C-08-auria' => array(
		'tpl'          => 'TPL-C-08-auria',
		'arch'         => 'TPL-C-08',
		'brand'        => 'auria',
		'brand_name'   => 'Auria',
		'brand_sector' => 'Automoción · concesión',
		'tpl_name'     => 'Modelo / Lanzamiento',
		'site'         => 'corporate',
		'site_es'      => 'Corporativa',
		'fits'         => 'Concesión oficial, lanzamiento de un modelo, un producto caro y único',
		'dna'          => 'COMP-MODEL-HERO · COMP-SPEC-TABLE · COMP-OFFER-STRIP · COMP-BOOKING',
		'wire'         => 'COMP-HEADER · COMP-MODEL-HERO · COMP-SPEC-TABLE · COMP-GALLERY · COMP-OFFER-STRIP · COMP-FINANCE · COMP-BOOKING · COMP-FAQ · COMP-FOOTER',
		'nav'          => array( 'El modelo', 'Versiones', 'Concesión' ),
		'nav_cta'      => 'Reservar prueba',
		'phone'        => '91 000 00 00',
		'hero'         => array(
			'eyebrow' => 'Nuevo · disponible en concesión desde marzo',
			'h1'      => 'Auria GT',
			'lede'    => 'Tracción trasera, 4,1 segundos y una caja que baja de marcha cuando usted todavía lo está pensando.',
			'cta_1'   => 'Reservar prueba',
			'cta_2'   => 'Ver versiones',
			'img'     => 'auria-hero',
			'figures' => array(
				array( '385 CV', 'potencia' ),
				array( '4,1 s', '0 a 100' ),
				array( '2+2', 'plazas' ),
				array( '78.400 €', 'desde' ),
			),
		),
		'specs'        => array(
			'eyebrow' => 'Versiones',
			'h2'      => 'Tres acabados y lo que cambia entre ellos',
			'cols'    => array( 'GT', 'GT Sport', 'GT Sport +' ),
			'rows'    => array(
				array( 'Potencia',        array( '385 CV', '450 CV', '510 CV' ), true ),
				array( '0 a 100 km/h',    array( '4,1 s', '3,7 s', '3,4 s' ), true ),
				array( 'Tracción',        array( 'Trasera', 'Trasera', 'Total' ), true ),
				array( 'Llantas',         array( '19"', '20"', '20" forjadas' ), true ),
				array( 'Suspensión',      array( 'Adaptativa', 'Adaptativa', 'Adaptativa' ), false ),
				array( 'Garantía',        array( '3 años', '3 años', '3 años' ), false ),
				array( 'Precio',          array( '78.400 €', '92.900 €', '114.600 €' ), true ),
			),
			'note'    => 'Lo que cambia entre versiones va marcado; lo que se repite se atenúa. Una tabla en la que todo pesa igual no ayuda a decidir nada.',
		),
		'gallery'      => array(
			'eyebrow' => 'Por dentro',
			'h2'      => 'Tres detalles que se notan al sentarse',
			'items'   => array( 'auria-interior', 'auria-puerta', 'auria-arranque' ),
		),
		'offer'        => array(
			'eyebrow' => 'Hasta el 31 de marzo',
			'h2'      => 'Entrega en marzo con el equipamiento Sport sin coste',
			'lede'    => 'Sobre las unidades ya en tránsito. Son once y se asignan por orden de reserva.',
			'cta'     => 'Reservar prueba',
			'small'   => 'Oferta válida hasta el 31 de marzo o fin de existencias, lo que ocurra antes. Sin cuenta atrás: la fecha es la fecha.',
		),
		'finance'      => array(
			'eyebrow' => 'Financiación',
			'h2'      => 'Cuota con valor futuro garantizado',
			'rows'    => array(
				array( 'Precio del vehículo', '78.400 €' ),
				array( 'Entrada',             '23.520 €' ),
				array( 'Plazo',               '48 meses' ),
				array( 'Cuota',               '689 €/mes' ),
				array( 'TAE',                 '6,45 %' ),
				array( 'Valor futuro garantizado', '39.200 €' ),
			),
			'note'    => 'Ejemplo sobre la versión GT. Sujeto a aprobación de la entidad financiera.',
		),
		'booking'      => array(
			'eyebrow' => 'Prueba de conducción',
			'h2'      => 'Elija día y hora',
			'lede'    => 'La prueba dura cuarenta minutos y la hace usted, no un comercial de copiloto explicando cosas.',
			'fields'  => array(
				array( 'nombre', 'Nombre', 'text' ),
				array( 'tel', 'Teléfono', 'tel' ),
			),
			'day_lbl'  => 'Día',
			'days'     => array( 'Jueves 22', 'Viernes 23', 'Sábado 24', 'Lunes 26', 'Martes 27' ),
			'slot_lbl' => 'Hora',
			'slots'    => array( '10:00', '11:30', '13:00', '17:00', '18:30' ),
			'submit'   => 'Reservar prueba',
			'small'    => 'Necesita carné con más de dos años. Le confirmamos por teléfono el mismo día.',
		),
		'faq'          => array(
			'eyebrow' => 'Antes de preguntar',
			'h2'      => 'Lo que se pregunta siempre',
			'items'   => array(
				array( '¿Cuánto tarda la entrega?', 'Las unidades en tránsito se entregan en marzo. Un pedido a fábrica hoy sale a cinco meses, y esa fecha se la damos por escrito.' ),
				array( '¿Aceptan mi coche a cambio?', 'Sí. La tasación la hace el departamento de ocasión y el importe se descuenta de la entrada, no de la cuota.' ),
				array( '¿Puedo ver una unidad antes de reservar?', 'Hay una GT y una GT Sport en exposición. La prueba de conducción se hace con la GT.' ),
				array( '¿La garantía cubre uso en circuito?', 'No. Ninguna garantía de serie lo cubre, y quien le diga lo contrario le está vendiendo algo.' ),
			),
		),
		'footer'       => array(
			'tag'   => 'Auria · Concesión oficial · Avenida de Burgos 120, 28050 Madrid · 91 000 00 00',
			'links' => array( 'El modelo', 'Versiones', 'Privacidad' ),
			'legal' => 'Auria Motor SA · Maqueta interna NovaMira, no publicada.',
		),
	),

	/* ── TALLER BERGARA · TPL-C-09 · el precio es lo que NO se sabe, y ése es el miedo que hace
	   posponer la reparación. Por eso la tarifa es la segunda cosa de la página y lleva importes. */
	'TPL-C-09-bergara' => array(
		'tpl'          => 'TPL-C-09-bergara',
		'head_mode'    => 'rule',
		'arch'         => 'TPL-C-09',
		'brand'        => 'bergara',
		'brand_name'   => 'Taller Bergara',
		'brand_sector' => 'Automoción · taller',
		'tpl_name'     => 'Taller / Tarifa',
		'site'         => 'corporate',
		'site_es'      => 'Corporativa',
		'fits'         => 'Talleres mecánicos, chapa y pintura, neumáticos, reparación técnica',
		'dna'          => 'COMP-PRICE-LIST · COMP-PROCESS · COMP-BOOKING',
		'wire'         => 'COMP-HEADER · COMP-HERO · COMP-PRICE-LIST · COMP-PROCESS · COMP-BOOKING · COMP-TRUST-BADGES · COMP-FAQ · COMP-MAP-NAP · COMP-FOOTER',
		'nav'          => array( 'Tarifa', 'Cómo funciona', 'Dónde estamos' ),
		'nav_cta'      => 'Pedir cita',
		'phone'        => '943 00 00 00',
		'hero'         => array(
			'eyebrow' => 'Taller multimarca · Eibar',
			'h1'      => 'Diagnóstico 39 €, y se descuenta si repara aquí',
			'lede'    => 'La tarifa completa está debajo, con importes cerrados. Si al abrirlo sale otra cosa, le llamamos antes de tocar nada — y usted decide.',
			'cta_1'   => 'Pedir cita',
			'cta_2'   => '943 00 00 00',
			'img'     => 'bergara-nave',
		),
		'prices'       => array(
			'eyebrow' => 'Tarifa',
			'h2'      => 'Lo que cuesta, escrito',
			'note'    => 'Precios con IVA y mano de obra incluida. «Desde» siempre lleva su porqué escrito al lado: un «desde» sin explicar es peor que no publicar precio.',
			'groups'  => array(
				array(
					'h3'    => 'Mantenimiento',
					'items' => array(
						array( 'Cambio de aceite y filtro', 'Aceite sintético 5W30 y filtro de la marca. Hasta 5 litros.', '89 €' ),
						array( 'Revisión de 30 puntos', 'Con informe firmado. Incluye niveles y presiones.', '49 €' ),
						array( 'Filtro de habitáculo', 'Antipolen. Quince minutos, se hace en el momento.', '35 €' ),
					),
				),
				array(
					'h3'    => 'Frenos',
					'items' => array(
						array( 'Pastillas delanteras', 'Juego completo y purgado. Piezas de primer equipo.', '125 €' ),
						array( 'Discos y pastillas', 'Desde, porque el disco depende del diámetro del eje.', 'desde 265 €' ),
						array( 'Líquido de frenos', 'Sustitución completa con máquina de purga.', '69 €' ),
					),
				),
				array(
					'h3'    => 'Neumáticos',
					'items' => array(
						array( 'Montaje y equilibrado', 'Por rueda. Incluye válvula nueva y reciclado.', '18 €' ),
						array( 'Alineación de dirección', 'Cuatro ruedas, con ficha de ángulos antes y después.', '55 €' ),
					),
				),
				array(
					'h3'    => 'Diagnosis',
					'items' => array(
						array( 'Lectura de centralita', 'Con informe de códigos. Se descuenta si repara aquí.', '39 €' ),
						array( 'Búsqueda de avería eléctrica', 'Desde, porque se cobra por hora de banco a partir de la primera.', 'desde 60 €/h' ),
					),
				),
			),
		),
		'process'      => array(
			'eyebrow' => 'Cómo funciona',
			'h2'      => 'Cuatro pasos, y el tercero es suyo',
			'steps'   => array(
				array( 'Lo trae', 'Sin cita para diagnosis rápida; con cita si ya sabe qué hay que hacer.' ),
				array( 'Diagnosticamos', 'Lectura de centralita y revisión física. Sale un presupuesto por partidas.' ),
				array( 'Usted aprueba', 'Le llamamos con el importe. Nadie toca nada hasta que dice que sí.' ),
				array( 'Reparamos', 'Con factura desglosada: piezas por un lado, horas por otro.' ),
			),
		),
		'booking'      => array(
			'eyebrow' => 'Cita',
			'h2'      => 'Reserve hueco',
			'lede'    => 'Dos elevadores y un banco de diagnosis. Lo que no aparece aquí está ocupado, no es que no cojamos el teléfono.',
			'fields'  => array(
				array( 'matricula', 'Matrícula', 'text' ),
				array( 'tel', 'Teléfono', 'tel' ),
			),
			'day_lbl'  => 'Día',
			'days'     => array( 'Lunes 22', 'Martes 23', 'Miércoles 24', 'Jueves 25', 'Viernes 26' ),
			'slot_lbl' => 'Hora',
			'slots'    => array( '08:30', '10:00', '11:30', '15:30', '17:00' ),
			'submit'   => 'Pedir cita',
			'img'      => 'bergara-rueda',
			'small'    => 'Le confirmamos por WhatsApp. Si necesita coche de sustitución, dígalo al reservar: hay dos.',
		),
		'badges'       => array(
			array( '24 meses', 'de garantía en la mano de obra de cualquier reparación' ),
			array( 'Primer equipo', 'o equivalente homologado, y en el presupuesto pone cuál' ),
			array( 'Factura', 'con piezas y horas separadas, siempre' ),
			array( 'Piezas viejas', 'se las enseñamos y se las puede llevar' ),
		),
		'faq'          => array(
			'eyebrow' => 'Lo que preocupa',
			'h2'      => 'Preguntas que hacen todos',
			'items'   => array(
				array( '¿Y si al abrirlo sale más caro?', 'Paramos y le llamamos. El presupuesto no sube sin que usted lo apruebe, y si decide no seguir sólo paga la diagnosis.' ),
				array( '¿Pierdo la garantía del concesionario?', 'No. La ley europea permite mantener el coche en un taller independiente sin perder la garantía de fábrica, siempre con piezas homologadas y factura. Se la damos.' ),
				array( '¿Cuánto tardan?', 'El mantenimiento sale el mismo día. Una avería que necesita pieza depende del proveedor y se lo decimos al aprobar, no después.' ),
				array( '¿Puedo llevar yo la pieza?', 'Sí, pero entonces la garantía cubre nuestra mano de obra y no la pieza. Se lo ponemos por escrito para que no haya dudas luego.' ),
			),
		),
		'nap'          => array(
			'eyebrow' => 'Dónde estamos',
			'h2'      => 'Eibar, Gipuzkoa',
			'addr'    => array( 'Polígono Azitain, pabellón 6', '20600 Eibar · Gipuzkoa' ),
			'phone'   => '943 00 00 00',
			'mail'    => 'citas@tallerbergara.example',
			'hours'   => array(
				array( 'Lunes a viernes', '08:30 – 13:30 · 15:00 – 19:00' ),
				array( 'Sábado', '09:00 – 13:00 (sólo urgencias)' ),
				array( 'Domingo', 'Cerrado' ),
			),
			'note'    => 'Aparcamiento delante del pabellón. Si viene con la avería en marcha, llame antes y le dejamos hueco.',
			'img'     => 'bergara-rueda',
		),
		'footer'       => array(
			'tag'   => 'Taller Bergara · Polígono Azitain pabellón 6, 20600 Eibar · 943 00 00 00',
			'links' => array( 'Tarifa', 'Cita', 'Privacidad' ),
			'legal' => 'Taller Bergara SL · Maqueta interna NovaMira, no publicada.',
		),
	),

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
	'TPL-C-06-terrazza' => array(
		'tpl'          => 'TPL-C-06-terrazza',
		'arch'         => 'TPL-C-06',
		'brand'        => 'terrazza',
		'brand_name'   => 'Casa Terrazza',
		'brand_sector' => 'Restaurante',
		'tpl_name'     => 'Mesa / Carta',
		'site'         => 'corporate',
		'site_es'      => 'Corporativa',
		'fits'         => 'Restaurantes, asadores, bares con cocina, cualquier mesa que se reserva',
		'dna'          => 'COMP-HERO-FULL · COMP-MENU-LIST · COMP-FIGURE-QUOTE · COMP-HOURS-BLOCK',
		'wire'         => 'COMP-HEADER · COMP-HERO-FULL · COMP-MARQUEE · COMP-MENU-LIST · COMP-FIGURE-QUOTE · COMP-GALLERY · COMP-BOOKING · COMP-HOURS-BLOCK · COMP-FOOTER',
		'nav'          => array( 'Carta', 'La casa', 'Grupos' ),
		'nav_cta'      => 'Reservar mesa',
		'phone'        => '93 000 00 00',
		'hero'         => array(
			'eyebrow' => 'Cocina de brasa · Poblenou, Barcelona',
			'h1'      => 'Se cocina lo que llegó esta mañana',
			'lede'    => 'Carta corta que cambia cada lunes, encina en la brasa y sesenta referencias en bodega. Reservamos por servicio y no por turno: la mesa es suya hasta que usted se levante.',
			'cta_1'   => 'Reservar mesa',
			'cta_2'   => 'Ver la carta',
			'img'     => 'terrazza-sala',
		),
		'marquee'      => array( 'Cocina de mercado', 'Brasa de encina', 'Pan de masa madre', 'Bodega de sesenta' ),
		'menu'         => array(
			'eyebrow' => 'La carta',
			'h2'      => 'Doce platos, y el lunes son otros',
			'note'    => 'Precios con IVA. Esta es la carta del 22 al 28; el lunes cambia entera. Si algo se acaba, se acaba.',
			'groups'  => array(
				array(
					'h3'    => 'Para empezar',
					'items' => array(
						array( 'Anchoa del Cantábrico', 'Curada en casa, con mantequilla de leche cruda y pan de ayer tostado.', '4,80 € ud.' ),
						array( 'Tomate de secano', 'De Alcarràs, con albahaca y aceite de arbequina de este año.', '11,00 €' ),
						array( 'Croquetas de puchero', 'Seis. Del cocido del domingo, que es como tienen que ser.', '9,50 €' ),
					),
				),
				array(
					'h3'    => 'De la brasa',
					'items' => array(
						array( 'Presa ibérica', 'Encina y sarmiento, reposada ocho minutos. Al punto o no la servimos.', '24,00 €' ),
						array( 'Pulpo a la llama', 'Cocido dos horas, marcado treinta segundos, con patata ahumada.', '22,50 €' ),
						array( 'Alcachofas enteras', 'A la brasa con su tallo, sal en escamas y limón asado.', '14,00 €' ),
						array( 'Chuleta de vaca vieja', 'Madurada sesenta días. Mínimo para dos, se pesa en sala.', '68,00 €/kg' ),
					),
				),
				array(
					'h3'    => 'Para terminar',
					'items' => array(
						array( 'Torrija de brioche', 'Caramelizada al momento, con helado de leche merengada.', '7,50 €' ),
						array( 'Queso del Pallars', 'Tres cortes y membrillo de la casa. Pregunte cuáles hay hoy.', '9,00 €' ),
					),
				),
				array(
					'h3'    => 'Menú del mediodía',
					'items' => array(
						array( 'De martes a viernes', 'Dos platos, postre, pan y una copa de la casa. Sólo mediodía.', '19,50 €' ),
						array( 'Maridaje de tres copas', 'Lo elige quien esté en sala ese día. No hay carta de maridaje.', '16,00 €' ),
					),
				),
			),
		),
		'booking'      => array(
			'eyebrow' => 'Reservas',
			'h2'      => 'Elija día y hora',
			'lede'    => 'Veinte mesas y dos servicios. Lo que no aparece aquí es que está lleno, no que no cogemos el teléfono.',
			'fields'  => array(
				array( 'nombre', 'Nombre', 'text' ),
				array( 'tel', 'Teléfono', 'tel' ),
			),
			'day_lbl'  => 'Día',
			'days'     => array( 'Jueves 22', 'Viernes 23', 'Sábado 24', 'Domingo 25', 'Martes 27' ),
			'slot_lbl' => 'Hora',
			'slots'    => array( '13:30', '15:00', '20:00', '21:30', '22:45' ),
			'img'      => 'terrazza-mesa',
			'submit'   => 'Reservar mesa',
			'small'    => 'Le confirmamos en menos de una hora. Si al final no puede venir, avise y liberamos la mesa.',
		),
		'gallery'      => array(
			'eyebrow' => 'El sitio',
			'h2'      => 'Cómo es por dentro',
			'items'   => array( 'terrazza-mesa', 'terrazza-terraza', 'terrazza-coctel', 'terrazza-plato', 'terrazza-velas', 'terrazza-chef' ),
		),
		'figq'         => array(
			'eyebrow' => 'Quien firma',
			'quote'   => 'Cocino lo que me traen, no lo que tenía pensado. Por eso la carta cambia el lunes: si el martes llega mejor tomate que alcachofa, mando la alcachofa a casa.',
			'name'    => 'Èlia Ferrer',
			'role'    => 'Cocinera y socia · en la casa desde 2016',
			'sig'     => 'Èlia Ferrer',
			'img'     => 'terrazza-chef',
		),
		'hours'        => array(
			'eyebrow' => 'Horario y dirección',
			'h2'      => 'Abrimos de martes a domingo',
			'rows'    => array(
				array( 'Martes a jueves', '13:00 – 16:00 · 20:00 – 23:30' ),
				array( 'Viernes y sábado', '13:00 – 16:30 · 20:00 – 00:30' ),
				array( 'Domingo', '13:00 – 16:30' ),
				array( 'Lunes', 'Cerrado' ),
			),
			'addr'    => array( 'Carrer de Pujades, 118', '08005 Barcelona' ),
			'phone'   => '93 000 00 00',
			'mail'    => 'reservas@casaterrazza.example',
			'note'    => 'Metro Llacuna a cuatro minutos. La terraza abre hasta que refresca.',
		),
		'footer'       => array(
			'tag'   => 'Casa Terrazza · Carrer de Pujades 118, 08005 Barcelona · 93 000 00 00',
			'links' => array( 'Carta', 'Reservas', 'Privacidad' ),
			'legal' => 'Casa Terrazza SL · Maqueta interna NovaMira, no publicada.',
		),
	),

	'TPL-C-01' => array(
		'tpl'      => 'TPL-C-01',
		'tpl_name' => 'Services / Lead-Gen',
		/* `Ideal para`, transcribed from TPL-C-01-services-leadgen.md §1. */
		'fits'     => 'Consultoras, agencias, estudios, desarrollo, marketing, servicios profesionales',
		'site'     => 'corporate',
		'site_es'  => 'Corporativa',
		'dna'      => 'COMP-HERO · COMP-SERVICES · COMP-CTA + COMP-LEAD-FORM',
		'wire'     => 'COMP-HEADER · COMP-HERO · COMP-SERVICES · COMP-CASES · COMP-CTA + COMP-LEAD-FORM · COMP-FOOTER',
		'nav'      => array( 'Taller', 'Materiales', 'Trabajos', 'Contacto' ),
		'nav_cta'  => 'Pedir presupuesto',
		'hero'     => array(
			'eyebrow' => 'Taller de piedra natural · desde 1978',
			'h1'      => 'Cortamos piedra que dura más que el edificio',
			'lede'    => 'Mármol, granito y caliza medidos, cortados y colocados por el mismo equipo que los levanta. Sin intermediarios y sin plazos que se mueven.',
			'cta_1'   => 'Pedir presupuesto',
			'cta_2'   => 'Ver el taller',
			'img'     => 'hero-cantera',
		),
		'services' => array(
			'eyebrow' => 'Servicios',
			'h2'      => 'Lo que hacemos',
			'cards'   => array(
				array( 'img' => 'card-cantero', 'h3' => 'Encimeras y baños', 'p' => 'Medición láser en obra, corte a medida y colocación en una sola visita.' ),
				array( 'img' => 'card-patio', 'h3' => 'Fachadas y sillería', 'p' => 'Despiece, labra y montaje de piedra vista para obra nueva y rehabilitación.' ),
				array( 'img' => 'card-labra', 'h3' => 'Restauración', 'p' => 'Reposición de piezas dañadas con la misma cantera y la misma herramienta.' ),
			),
		),
		/* TPL-SERVICE-01 · the interior page. Its copy is NOT a second brand: it is the same taller
		   going one level deeper into one of the three services the home already lists, which is what
		   a service page IS. `_gallery-images.md`'s rule holds here too — no photograph this page has
		   and its home does not. */
		'service'  => array(
			'crumbs'   => array( 'Inicio', 'Servicios', 'Encimeras y baños' ),
			'eyebrow'  => 'Servicio',
			'h1'       => 'Encimeras y baños en piedra natural',
			'claim'    => 'Medimos con láser sobre la obra terminada, cortamos a medida y coloca el mismo equipo que ha cortado. Una sola pieza siempre que la pieza lo permita.',
			'cta_1'    => 'Pedir presupuesto',
			'cta_2'    => 'Ver materiales',
			'img'      => 'hero-encimera',
			'problems' => array(
				'eyebrow' => 'Qué resolvemos',
				'h2'      => 'Tres cosas que se tuercen sin un cantero',
				'items'   => array(
					array( 'La junta que aparece luego', 'Un frente de 3 metros resuelto con dos piezas enseña la junta el primer día de sol. Medimos antes de comprar el bloque.' ),
					array( 'La veta que no continúa', 'Dos piezas del mismo material no son la misma piedra. Se despiezan del mismo bloque o no se despiezan.' ),
					array( 'El fregadero que no encaja', 'El corte del seno se hace con el electrodoméstico delante, no con su ficha técnica.' ),
				),
			),
			'features' => array(
				'eyebrow' => 'Alcance',
				'h2'      => 'Qué incluye',
				'items'   => array(
					array( 'Medición láser en obra', 'Con los muebles montados y los electrodomésticos presentes.' ),
					array( 'Despiece del bloque', 'Elegimos de qué parte del bloque sale cada pieza para que la veta siga.' ),
					array( 'Corte y pulido', 'Canto recto, romo o biselado, decidido sobre muestra física.' ),
					array( 'Seno y grifería', 'Bajo encimera, enrasado o sobre encimera, con las perforaciones hechas en taller.' ),
					array( 'Colocación y sellado', 'En una sola visita, y el sellado se repasa a los seis meses.' ),
				),
			),
			'faq'      => array(
				'eyebrow' => 'Dudas',
				'h2'      => 'Lo que nos preguntan siempre',
				'items'   => array(
					array( '¿Cuánto tarda desde la medición?', 'Entre diez y quince días laborables según el material. Si la piedra está en stock, diez.' ),
					array( '¿Se puede hacer sin junta a la vista?', 'Hasta 3,4 metros sí, que es el largo útil de nuestra bancada. Por encima, la junta se coloca donde menos se ve y se decide con el cliente.' ),
					array( '¿El mármol se mancha?', 'Es calcáreo, así que sí: el limón y el vino dejan marca si se quedan. Se trata con hidrófugo y se repasa cada dos años.' ),
					array( '¿Retiráis la encimera vieja?', 'Sí, y la gestionamos en punto limpio. Va en el presupuesto, no como extra.' ),
				),
			),
			'others'   => array(
				'eyebrow' => 'Otros servicios',
				'h2'      => 'Lo demás que hacemos',
				'items'   => array(
					array( 'Fachadas y sillería', 'Despiece, labra y montaje de piedra vista para obra nueva y rehabilitación.', 'card-patio' ),
					array( 'Restauración', 'Reposición de piezas dañadas con la misma cantera y la misma herramienta.', 'card-labra' ),
				),
			),
		),
		/* COMP-LOGOS · invented studios and institutions, like `Piedra Valdés` itself. Named
		   plausibly but not after anyone real: a public repository is not the place to put a
		   fabricated client list of actual companies. */
		'logos'    => array(
			'eyebrow' => 'Confían en el taller',
			'items'   => array( 'Estudio Arnau', 'Ribera & Fills', 'Obra Nova',
				'Fundació Sant Roc', 'Vallmoll Interiors', 'Colegio de Aparejadores' ),
		),
		/* COMP-PROCESS · four steps. The archetype asks for 3–5 and for what the method is; each
		   step here names a thing that is refused as much as a thing that is done, because that is
		   what a process block is for on a lead-gen page. */
		'process'  => array(
			'eyebrow' => 'Cómo trabajamos',
			'h2'      => 'Cuatro pasos y un solo interlocutor',
			'steps'   => array(
				array( 'Medición en obra', 'Vamos al sitio con láser y plantilla. Nada se corta sobre un plano que no hemos comprobado.' ),
				array( 'Despiece y presupuesto', 'Cerrado por escrito: piezas, juntas, plazo y precio. Si algo cambia, se firma antes.' ),
				array( 'Corte y labra', 'En nuestro taller y con nuestra gente. La pieza vista no se subcontrata.' ),
				array( 'Colocación', 'El mismo equipo que la labró la monta. Quien responde es quien la hizo.' ),
			),
		),
		/* COMP-TESTIMONIAL · three quotes, each tied to a job named elsewhere on the page. */
		'quotes'   => array(
			'eyebrow' => 'Lo que dicen',
			'h2'      => 'Tres obras, tres encargantes',
			'items'   => array(
				array( '142 sillares repuestos sin cerrar el edificio al público, y cumplieron el plazo al día.',
					'Marta Vilanova', 'Gerencia · Palacio de Miraflores' ),
				array( 'Pedimos una encimera de una sola pieza de 3,4 metros. Nadie más se atrevió a medirla.',
					'Jordi Camps', 'Cocina en Vallmoll' ),
				array( 'Nos explicaron por qué la caliza que queríamos no aguantaba esa fachada. Nos ahorraron la obra dos veces.',
					'Estudio Arnau', 'Dirección facultativa' ),
			),
		),
		'cases'    => array(
			'eyebrow' => 'Obra hecha',
			'h2'      => 'Dos encargos recientes',
			'cards'   => array(
				array( 'img' => 'card-veta', 'h3' => 'Palacio de Miraflores', 'p' => '142 sillares repuestos sin cerrar el edificio al público.' ),
				array( 'img' => 'card-mueble', 'h3' => 'Cocina en Vallmoll', 'p' => 'Encimera de una sola pieza, 3,4 m sin junta visible.' ),
			),
		),
		'band'     => array(
			'eyebrow' => 'Contacto',
			'h2'      => 'Cuéntanos qué hay que cortar',
			'lede'    => 'Respondemos con un presupuesto cerrado en 48 horas laborables, con la piedra y el plazo por escrito.',
			'fields'  => array(
				array( 'name', 'Nombre', 'text' ),
				array( 'mail', 'Email', 'email' ),
				array( 'tel', 'Teléfono', 'tel' ),
			),
			'msg'     => 'Qué necesitas',
			'submit'  => 'Enviar consulta',
		),
		'footer'   => array(
			'tag'   => 'Taller de cantería · Novelda, Alicante',
			'links' => array( 'Aviso legal', 'Privacidad', 'Cookies' ),
			'legal' => '© 2026 Piedra Valdés S.L. · Ctra. de la Cantera 4, Novelda',
		),
	),

	'TPL-C-02' => array(
		'tpl'      => 'TPL-C-02',
		'tpl_name' => 'Institutional Trust',
		'site'     => 'corporate',
		'site_es'  => 'Corporativa',
		/* `Ideal para`, transcribed from TPL-C-02-institutional-trust.md §1. */
		'fits'     => 'Despachos, industria, sanidad, educación, instituciones y empresas consolidadas',
		'dna'      => 'COMP-HERO · COMP-ABOUT · COMP-STATS · COMP-SERVICES · COMP-CTA',
		'wire'     => 'COMP-HEADER · COMP-HERO · COMP-ABOUT · COMP-STATS · COMP-SERVICES · COMP-CREDENTIALS · COMP-TEAM · COMP-TESTIMONIAL · COMP-CTA · COMP-FOOTER',
		'nav'      => array( 'La casa', 'Áreas', 'Obra', 'Contacto' ),
		'nav_cta'  => 'Hablar con nosotros',
		'hero'     => array(
			'eyebrow' => 'Cantería Piedra Valdés · Lleida · desde 1978',
			'h1'      => 'Cuarenta y siete años de la misma firma en la misma piedra',
			'lede'    => 'Tres generaciones, una cantera propia y un taller que no ha cambiado de manos. Trabajamos para instituciones que necesitan que la obra siga en pie cuando ya no estemos.',
			'cta_1'   => 'Hablar con nosotros',
			'cta_2'   => 'Ver la obra',
			'img'     => 'pan-fachada',
		),
		'about'    => array(
			'eyebrow' => 'La casa',
			'h2'      => 'Quiénes somos, sin adjetivos',
			'body'    => array(
				'Piedra Valdés nace en 1978 como taller de labra para la rehabilitación de la Seu Vella. Desde 1994 explotamos cantera propia en Alcarràs, lo que nos permite responder por el material y no sólo por el corte.',
				'Somos veintiséis personas: catorce en taller, seis en obra, tres en oficina técnica y tres en cantera. No subcontratamos la pieza vista, y por eso el plazo que damos es el plazo que hay.',
			),
			'img'     => 'card-detalle',
		),
		'stats'    => array(
			'eyebrow' => 'En cifras',
			'items'   => array(
				array( '1978', 'Año de fundación' ),
				array( '312', 'Obras entregadas' ),
				array( '26', 'Personas en plantilla' ),
				array( '1', 'Cantera propia' ),
			),
		),
		'services' => array(
			'eyebrow' => 'Áreas',
			'h2'      => 'En qué trabajamos',
			'cards'   => array(
				array( 'img' => 'card-labra', 'h3' => 'Patrimonio y rehabilitación', 'p' => 'Reposición con cantera compatible, informes previos y coordinación con dirección facultativa.' ),
				array( 'img' => 'card-cantero', 'h3' => 'Obra nueva en piedra vista', 'p' => 'Despiece, labra y montaje de fachada y sillería para promotor y constructora.' ),
				array( 'img' => 'card-veta', 'h3' => 'Suministro de material', 'p' => 'Bloque y tabla de cantera propia, con ficha técnica y ensayo por partida.' ),
			),
		),
		'credentials' => array(
			'eyebrow' => 'Acreditaciones',
			'h2'      => 'Lo que nos han certificado',
			'items'   => array(
				array( 'ISO 9001', 'Gestión de calidad, recertificada en 2024' ),
				array( 'CE 2+', 'Marcado de producto para piedra natural de construcción' ),
				array( 'REA 12-…', 'Registro de Empresas Acreditadas del sector de la construcción' ),
				array( 'Gremi de Pedra', 'Miembro desde 1981' ),
			),
		),
		'team'     => array(
			'eyebrow' => 'Quién firma',
			'h2'      => 'Las personas que responden',
			'items'   => array(
				array( 'Ramon Valdés', 'Dirección · tercera generación', 'sq-manos' ),
				array( 'Aina Serra', 'Oficina técnica · despiece', 'card-detalle' ),
				array( 'Marc Puig', 'Jefe de taller', 'card-cantero' ),
			),
		),
		'quotes'   => array(
			'eyebrow' => 'Referencias',
			'h2'      => 'Quien ya ha trabajado con nosotros',
			'items'   => array(
				array( 'Cumplieron el plazo del pliego sin una sola prórroga, que en patrimonio no es lo normal.', 'Servei de Patrimoni', 'Administración autonómica' ),
				array( 'Entregaron ensayo por partida sin que se lo pidiéramos. Eso nos ahorró la discusión con la propiedad.', 'Estudio Arnau', 'Dirección facultativa' ),
				array( 'Es la única cantería de la zona que te dice que no cuando el material no aguanta.', 'Ribera & Fills', 'Constructora' ),
			),
		),
		/* COMP-CTA sobrio · NO lead form. TPL-C-02's wireframe says "COMP-CTA (contacto sobrio)" and
		   NOT COMP-LEAD-FORM, which is the one place its DNA separates hardest from TPL-C-01: this
		   archetype does not chase a lead, it opens a conversation. Rendering the form here would
		   turn two different archetypes into one. */
		'band'     => array(
			'eyebrow' => 'Contacto',
			'h2'      => 'Escríbanos y le contestamos por escrito',
			'lede'    => 'Presupuesto cerrado en 48 horas laborables, con la piedra, el plazo y el ensayo por escrito. Sin visita comercial si no la pide.',
			'cta_1'   => 'Hablar con nosotros',
			'cta_2'   => '973 00 00 00',
		),
		'footer'   => array(
			'tag'   => 'Cantería Piedra Valdés · Alcarràs, Lleida · desde 1978',
			'links' => array( 'Aviso legal', 'Privacidad', 'Acreditaciones' ),
			'legal' => 'Piedra Valdés SL · B-25000000 · Maqueta interna NovaMira, no publicada.',
		),
	),

	/* TPL-C-03 · Portfolio / Showcase. Its doc is blunt about what it refuses: "NO pricing, NO
	   stats, NO forms largos", and "bajo en texto, alto en visual". So this is the one archetype
	   here that does NOT get a stats band, does not get a lead form, and carries barely any prose.
	   What it gets instead is the grid, and the grid is the page.

	   The four reference kits all lean on counters, pricing tables and long service copy. Putting
	   any of that here because the references do it would be the exact failure the ecommerce family
	   was rebuilt to fix: five archetypes wearing one skeleton. An archetype earns its place by
	   what it REFUSES. */
	'TPL-C-03' => array(
		'tpl'      => 'TPL-C-03',
		'tpl_name' => 'Portfolio / Showcase',
		'site'     => 'corporate',
		'site_es'  => 'Corporativa',
		/* `Ideal para`, transcribed from TPL-C-03-portfolio-showcase.md §1. */
		'fits'     => 'Estudios creativos, arquitectura, fotografía, diseño, productoras, artistas',
		'dna'      => 'COMP-HERO visual · COMP-PORTFOLIO-GRID · COMP-CTA',
		'wire'     => 'COMP-HEADER · COMP-HERO · COMP-PORTFOLIO-GRID · COMP-SERVICES · COMP-ABOUT · COMP-LOGOS · COMP-CTA · COMP-FOOTER',
		'nav'      => array( 'Obra', 'Estudio', 'Contacto' ),
		'nav_cta'  => 'Encargar',
		'hero'     => array(
			'eyebrow' => 'Piedra Valdés · obra 1978—2026',
			'h1'      => 'La obra',
			'lede'    => 'Trescientas doce entregas. Estas son doce.',
			'cta_1'   => 'Encargar',
			'cta_2'   => 'Sobre el estudio',
			'img'     => 'hero-taller',
		),
		'work'     => array(
			'eyebrow' => 'Selección',
			'h2'      => 'Obra reciente',
			'items'   => array(
				array( 'Palacio de Miraflores', 'Patrimonio · 2025', 'pan-fachada' ),
				array( 'Cocina en Vallmoll', 'Interior · 2025', 'hero-encimera' ),
				array( 'Panel de veta dorada', 'Material · 2024', 'card-veta' ),
				array( 'Mueble de frente pétreo', 'Interior · 2024', 'card-mueble' ),
				array( 'Labra de capitel', 'Patrimonio · 2023', 'card-labra' ),
				array( 'Cantera de Alcarràs', 'Origen · permanente', 'hero-cantera' ),
			),
		),
		'services' => array(
			'eyebrow' => 'Qué hacemos',
			'h2'      => 'Tres cosas, bien',
			'cards'   => array(
				array( 'img' => 'card-cantero', 'h3' => 'Labra', 'p' => 'A mano y a máquina, sobre bloque propio.' ),
				array( 'img' => 'card-patio', 'h3' => 'Corte a medida', 'p' => 'Encimera, peldaño, plaqueta y despiece de fachada.' ),
				array( 'img' => 'card-detalle', 'h3' => 'Restauración', 'p' => 'Reposición con cantera compatible.' ),
			),
		),
		'about'    => array(
			'eyebrow' => 'El estudio',
			'h2'      => 'Veintiséis personas y una cantera',
			'body'    => array(
				'Trabajamos la piedra de Alcarràs desde 1978. No damos plazos que no podamos cumplir y no subcontratamos la pieza vista.',
			),
			'img'     => 'sq-manos',
		),
		'logos'    => array(
			'eyebrow' => 'Han encargado',
			'items'   => array( 'Estudio Arnau', 'Ribera & Fills', 'Obra Nova',
				'Fundació Sant Roc', 'Vallmoll Interiors', 'Servei de Patrimoni' ),
		),
		'band'     => array(
			'eyebrow' => 'Contacto',
			'h2'      => 'Cuéntenos la pieza',
			'lede'    => 'Una foto y una medida bastan para empezar.',
			'cta_1'   => 'Encargar',
			'cta_2'   => 'obra@piedravaldes.example',
		),
		'footer'   => array(
			'tag'   => 'Piedra Valdés · obra 1978—2026',
			'links' => array( 'Obra', 'Estudio', 'Contacto' ),
			'legal' => 'Piedra Valdés SL · Alcarràs, Lleida · Maqueta interna NovaMira, no publicada.',
		),
	),

	/* TPL-E-03 · Brand Story. Its doc: "la marca y el relato venden; el producto ilustra". The
	   catalogue is deliberately thin here — one carousel, no grid, no search-first header — and
	   that thinness is the archetype, not an omission. TPL-E-02 is the shop that hides its brand;
	   this is the brand that sells a shop. */
	'TPL-E-03' => array(
		'tpl'      => 'TPL-E-03',
		'tpl_name' => 'Brand Story',
		'site'     => 'ecommerce',
		'site_es'  => 'Ecommerce',
		/* `Ecommerce ideal`, transcribed from TPL-E-03-brand-story.md §1. */
		'fits'     => 'Marcas de autor, artesanía, cosmética natural, café de especialidad, moda lenta',
		'dna'      => 'COMP-HERO relato · COMP-VALUES · COMP-ABOUT · COMP-NEWSLETTER',
		'wire'     => 'COMP-HEADER · COMP-HERO · COMP-VALUES · COMP-ABOUT · COMP-PRODUCT-CAROUSEL · COMP-TESTIMONIAL · COMP-CTA · COMP-NEWSLETTER · COMP-FOOTER',
		'nav'      => array( 'La piedra', 'Catálogo', 'El taller' ),
		'cart'     => 'Cesta',
		'cart_n'   => '1',
		'hero'     => array(
			'eyebrow' => 'Alcarràs, Lleida',
			'h1'      => 'La piedra sale de un sitio, y ese sitio importa',
			'lede'    => 'Extraemos, cortamos y vendemos la misma piedra. Sin intermediario no hay teléfono al que llamar cuando algo sale mal: hay un nombre.',
			'cta_1'   => 'Ver el catálogo',
			'cta_2'   => 'Cómo trabajamos',
			'img'     => 'hero-cantera',
		),
		'values'   => array(
			'eyebrow' => 'Lo que defendemos',
			'h2'      => 'Tres cosas que no negociamos',
			'items'   => array(
				array( 'Una sola cantera', 'Todo lo que vendemos sale del mismo frente de Alcarràs. Si se acaba, se acaba: no lo sustituimos por otra piedra parecida.' ),
				array( 'Corte propio', 'No compramos tabla cortada. El despiece lo decide quien conoce el bloque.' ),
				array( 'Un nombre detrás', 'Cada pedido lleva la firma del oficial que lo cortó. Si algo falla, sabe quién es.' ),
			),
		),
		'about'    => array(
			'eyebrow' => 'El taller',
			'h2'      => 'Tres generaciones sin cambiar de manos',
			'body'    => array(
				'Ramon Valdés abrió el taller en 1978 con dos oficiales y una tronzadora de raíl. Su nieta lleva hoy la oficina técnica.',
				'Vendemos poco y despacio, porque cortamos a medida y porque una cantera no da más de lo que da.',
			),
			'img'     => 'sq-manos',
		),
		'carousel' => array(
			'eyebrow' => 'Del frente a tu casa',
			'h2'      => 'Lo que hay ahora mismo',
			'cards'   => array(
				array( 'img' => 'sq-marmol',   'h3' => 'Crema Levante',        'p' => '189 €/m²' ),
				array( 'img' => 'sq-pizarra',  'h3' => 'Gris Quintana',        'p' => '164 €/m²' ),
				array( 'img' => 'card-veta',   'h3' => 'Veta dorada',          'p' => '236 €/m²' ),
				array( 'img' => 'card-mueble', 'h3' => 'Frente de mueble',     'p' => '412 €' ),
			),
		),
		'quotes'   => array(
			'eyebrow' => 'Quien la tiene en casa',
			'h2'      => 'Tres cocinas y una fachada',
			'items'   => array(
				array( 'Nos dijeron que esperáramos tres semanas a que saliera el bloque bueno. Esperamos. Acertaron.', 'Jordi Camps', 'Vallmoll' ),
				array( 'Es la única piedra de la casa que no he tenido que explicar a nadie.', 'Marta Vilanova', 'Lleida' ),
				array( 'Me mandaron la muestra con el nombre del que la cortó escrito detrás.', 'Aina Roca', 'Barcelona' ),
			),
		),
		'band'     => array(
			'eyebrow' => 'Empezar',
			'h2'      => 'Pida una muestra física',
			'lede'    => 'Gratuita y sin compromiso. Llega en cuatro días con su ficha técnica.',
			'cta_1'   => 'Pedir muestra',
			'cta_2'   => 'Ver el catálogo',
		),
		'news'     => array(
			'eyebrow' => 'Boletín',
			'h2'      => 'Le avisamos cuando salga bloque nuevo',
			'lede'    => 'Un correo cada dos o tres meses, sólo cuando abrimos frente. Nunca ofertas.',
			'label'   => 'Su correo',
			'cta'     => 'Avisarme',
			'small'   => 'Puede darse de baja en cualquier correo. No cedemos el dato.',
		),
		'footer'   => array(
			'tag'   => 'Piedra Valdés · cantera y taller · Alcarràs, Lleida',
			'links' => array( 'Envíos', 'Devoluciones', 'Privacidad' ),
			'legal' => 'Piedra Valdés SL · Maqueta interna NovaMira, no publicada.',
		),
	),

	/* TPL-C-04 · Landing / Single Offer. One offer, one CTA, and the page repeats it. Its doc's
	   DNA line is "una sola oferta, un solo CTA repetido, sin navegación que distraiga", so the
	   header carries ONE link and the nav that every other corporate archetype prints is absent
	   on purpose — the absence is the archetype. */
	'TPL-C-04' => array(
		'tpl'      => 'TPL-C-04',
		'tpl_name' => 'Landing / Single Offer',
		'site'     => 'corporate',
		'site_es'  => 'Corporativa',
		'fits'     => 'Campañas, lanzamientos, infoproductos, servicios de entrada, captación pagada',
		'dna'      => 'COMP-HERO oferta única · COMP-PROBLEM · COMP-SOLUTION · COMP-PRICING · COMP-CTA',
		'wire'     => 'COMP-HEADER · COMP-HERO · COMP-PROBLEM · COMP-SOLUTION · COMP-FEATURES · COMP-LOGOS · COMP-PRICING · COMP-FAQ · COMP-CTA · COMP-FOOTER',
		'nav'      => array(),
		'nav_cta'  => 'Reservar medición',
		'hero'     => array(
			'eyebrow' => 'Oferta de temporada · hasta el 30 de septiembre',
			'h1'      => 'Su encimera de piedra natural, medida y colocada en quince días',
			'lede'    => 'Medición láser, corte a medida de una sola pieza y colocación por el mismo equipo. Precio cerrado antes de empezar.',
			'cta_1'   => 'Reservar medición',
			'cta_2'   => 'Ver qué incluye',
			'img'     => 'hero-encimera',
		),
		'problem'  => array(
			'eyebrow' => 'Por qué importa',
			'h2'      => 'Lo que sale mal cuando no hay cantero',
			'items'   => array(
				array( 'Plazos que se mueven', 'La cocina lleva un mes montada y la encimera sigue "en fábrica". Sin taller propio nadie sabe dónde está la pieza.' ),
				array( 'La junta que no estaba prevista', 'Se descubre el día del montaje, con los armarios puestos y sin margen para rehacer el despiece.' ),
				array( 'Nadie responde', 'Cocinista, marmolista y colocador se señalan entre ellos. Usted paga la coordinación.' ),
			),
		),
		'solution' => array(
			'eyebrow' => 'La oferta',
			'h2'      => 'Un interlocutor, un precio y una fecha',
			'lede'    => 'Nosotros extraemos, cortamos y colocamos. No hay cadena en la que perder la pieza ni a quién derivar la culpa.',
			'items'   => array(
				array( 'Quince días', 'Desde la medición hasta la colocación, si el material está en stock.' ),
				array( 'Precio cerrado', 'Por escrito antes de cortar. Si cambia el alcance, se firma otra vez.' ),
				array( 'Una sola pieza', 'Hasta 3,4 m sin junta a la vista, que es el largo útil de nuestra bancada.' ),
			),
			'img'      => 'card-veta',
		),
		'features' => array(
			'eyebrow' => 'Alcance',
			'h2'      => 'Qué incluye',
			'items'   => array(
				array( 'Medición láser en obra', 'Con muebles montados y electrodomésticos presentes.' ),
				array( 'Corte y pulido de canto', 'Recto, romo o biselado, decidido sobre muestra física.' ),
				array( 'Seno y grifería', 'Perforaciones hechas en taller, no en su cocina.' ),
				array( 'Retirada de la encimera vieja', 'Gestionada en punto limpio, incluida en el precio.' ),
				array( 'Repaso de sellado a los seis meses', 'Una visita más, sin coste.' ),
			),
		),
		'logos'    => array(
			'eyebrow' => 'Han pasado por aquí',
			'items'   => array( 'Estudio Arnau', 'Ribera & Fills', 'Obra Nova',
				'Vallmoll Interiors', 'Cuina Lleida', 'Fundació Sant Roc' ),
		),
		'pricing'  => array(
			'eyebrow' => 'Precio',
			'h2'      => 'Tres materiales, un mismo servicio',
			'note'    => 'Precios por metro cuadrado, IVA incluido, medición y colocación aparte según metros. El presupuesto cerrado se emite tras la medición.',
			'plans'   => array(
				array( 'Granito Gris Quintana', '164 €/m²', array( 'El más duro de los tres', 'No necesita hidrófugo', 'Canto recto o romo' ), 'no' ),
				array( 'Mármol Crema Levante', '189 €/m²', array( 'Veta continua garantizada', 'Hidrófugo aplicado', 'Cualquier canto' ), 'sí' ),
				array( 'Veta dorada', '236 €/m²', array( 'Pieza única por bloque', 'Despiece supervisado', 'Canto biselado incluido' ), 'no' ),
			),
		),
		'faq'      => array(
			'eyebrow' => 'Dudas',
			'h2'      => 'Antes de reservar',
			'items'   => array(
				array( '¿Qué pasa si mi cocina no está terminada?', 'No medimos. Volvemos cuando lo esté, sin coste y sin perder el turno reservado.' ),
				array( '¿La medición se paga?', 'Se cobra 60 € y se descuentan íntegros del presupuesto si acepta.' ),
				array( '¿Y si no me gusta la pieza que sale del bloque?', 'La ve antes de cortar. Si no le convence, elegimos otro bloque o le devolvemos la medición.' ),
			),
		),
		'band'     => array(
			'eyebrow' => 'Último paso',
			'h2'      => 'Reserve la medición',
			'lede'    => 'Quedan once turnos antes del 30 de septiembre. Se asignan por orden.',
			'cta_1'   => 'Reservar medición',
			'cta_2'   => '973 00 00 00',
		),
		'footer'   => array(
			'tag'   => 'Piedra Valdés · Alcarràs, Lleida',
			'links' => array( 'Condiciones de la oferta', 'Privacidad' ),
			'legal' => 'Oferta válida hasta el 30/09. Maqueta interna NovaMira, no publicada.',
		),
	),

	/* TPL-E-05 · Promo / Campaign. Urgency is the archetype, so the deadline appears four times.
	   THE DEADLINE IS A DATE IN THE HTML, NOT A COUNTDOWN. A ticking clock is the device the
	   reference kits reach for, and it renders `00:00:00` until its script runs — on a page whose
	   entire argument is "this ends soon", a timer that says zero is the worst possible failure
	   mode, and it fails that way for every crawler permanently. A date needs nothing. */
	'TPL-E-05' => array(
		'tpl'      => 'TPL-E-05',
		'tpl_name' => 'Promo / Campaign',
		'site'     => 'ecommerce',
		'site_es'  => 'Ecommerce',
		'fits'     => 'Rebajas, liquidación de stock, campañas de temporada, outlet, lanzamientos con fecha',
		'dna'      => 'COMP-ANNOUNCEMENT urgencia · COMP-HERO promo · COMP-PRODUCT-GRID ofertas · COMP-PROMO-BANNER',
		'wire'     => 'COMP-ANNOUNCEMENT · COMP-HEADER · COMP-HERO · COMP-PRODUCT-GRID · COMP-PROMO-BANNER · COMP-PRODUCT-CAROUSEL · COMP-TRUST-BADGES · COMP-CTA · COMP-NEWSLETTER · COMP-FOOTER',
		'announce' => 'Liquidación de bancada · hasta el 30 de septiembre o fin de existencias',
		'nav'      => array( 'Ofertas', 'Catálogo', 'Cortes a medida' ),
		'search'   => 'Buscar mármol, granito, pizarra…',
		'tools'    => array( 'Cuenta' ),
		'cart'     => 'Carrito',
		'cart_n'   => '3',
		'hero'     => array(
			'eyebrow' => 'Termina el 30 de septiembre',
			'h1'      => 'Restos de bancada al 40%',
			'lede'    => 'Piezas de entre 0,8 y 2,1 m² que sobran de encargos cerrados. Mismo material, mismo corte, sin la espera del bloque.',
			'cta_1'   => 'Ver los restos',
			'cta_2'   => 'Cómo funciona',
			'img'     => 'card-veta',
		),
		'prods'    => array(
			'eyebrow' => 'Ofertas',
			'h2'      => 'Lo que queda hoy',
			'cards'   => array(
				array( 'img' => 'sq-marmol',   'h3' => 'Crema Levante · 1,4 m²',  'p' => '113 €/m²' ),
				array( 'img' => 'sq-pizarra',  'h3' => 'Gris Quintana · 2,1 m²',  'p' => '98 €/m²' ),
				array( 'img' => 'card-veta',   'h3' => 'Veta dorada · 0,9 m²',    'p' => '142 €/m²' ),
				array( 'img' => 'card-mueble', 'h3' => 'Frente de mueble',        'p' => '247 €' ),
				array( 'img' => 'card-detalle','h3' => 'Plaqueta 30×15 · 3 m²',   'p' => '28 €/m²' ),
				array( 'img' => 'hero-encimera','h3' => 'Peldaño macizo 120 cm',  'p' => '83 €' ),
			),
		),
		'promos'   => array(
			array( 'Segunda pieza al 50%', 'Sobre la de menor precio, en el mismo pedido y el mismo material.', 'Ver condiciones' ),
			array( 'Corte a medida sin coste', 'En cualquier resto de más de 1 m². El despiece lo hacemos nosotros.', 'Pedir corte' ),
		),
		'carousel' => array(
			'eyebrow' => 'También rebajado',
			'h2'      => 'Última bajada de precio',
			'cards'   => array(
				array( 'img' => 'card-patio', 'h3' => 'Canto pulido a mano', 'p' => '19 €/ml' ),
				array( 'img' => 'card-labra', 'h3' => 'Bloque para labra',   'p' => '224 €' ),
				array( 'img' => 'sq-manos',   'h3' => 'Kit de sellado 1 L',  'p' => '17 €' ),
				array( 'img' => 'pan-fachada','h3' => 'Sillar de arenisca',  'p' => '67 €' ),
			),
		),
		'badges'   => array( 'Envío en 72 h a península', 'Devolución en 30 días si no se ha cortado', 'Muestra física gratuita' ),
		'band'     => array(
			'eyebrow' => 'Antes del 30 de septiembre',
			'h2'      => 'Cuando se acaba el resto, se acaba',
			'lede'    => 'No reponemos: son piezas de encargos cerrados. Lo que ve es lo que hay.',
			'cta_1'   => 'Ver los restos',
			'cta_2'   => 'Avisarme del próximo lote',
		),
		'news'     => array(
			'eyebrow' => 'Cupón',
			'h2'      => 'Diez euros en su primer pedido',
			'lede'    => 'Le mandamos el código al momento y le avisamos cuando entre lote nuevo.',
			'label'   => 'Su correo',
			'cta'     => 'Enviarme el código',
			'small'   => 'Un correo por lote, nunca más de uno al mes. Puede darse de baja en cualquiera.',
		),
		'footer'   => array(
			'tag'   => 'Piedra Valdés · liquidación de bancada · Alcarràs, Lleida',
			'links' => array( 'Condiciones', 'Envíos', 'Devoluciones' ),
			'legal' => 'Precios con IVA. Oferta hasta el 30/09 o fin de existencias. Maqueta interna NovaMira.',
		),
	),

	/* TPL-C-05 · Local / Booking. The archetype of a place you go to. Its header carries the PHONE
	   as text, its close carries the address and the hours, and both are DNA: a local business
	   whose phone number lives only inside a form is a local business you cannot ring from the car. */
	'TPL-C-05' => array(
		'tpl'      => 'TPL-C-05',
		'tpl_name' => 'Local / Booking',
		'site'     => 'corporate',
		'site_es'  => 'Corporativa',
		'fits'     => 'Restaurantes, peluquerías, clínicas, talleres, gimnasios, cualquier negocio con puerta',
		'dna'      => 'COMP-HEADER con teléfono · COMP-HERO local · COMP-BOOKING · COMP-MAP-NAP',
		'wire'     => 'COMP-HEADER · COMP-HERO · COMP-SERVICES · COMP-BOOKING · COMP-GALLERY · COMP-TESTIMONIAL · COMP-MAP-NAP · COMP-FOOTER',
		'nav'      => array( 'Showroom', 'Materiales', 'Cómo llegar' ),
		'nav_cta'  => 'Pedir cita',
		'phone'    => '973 00 00 00',
		'hero'     => array(
			'eyebrow' => 'Showroom en Alcarràs · a 12 min de Lleida',
			'h1'      => 'Venga a tocar la piedra antes de decidirla',
			'lede'    => 'Cuatrocientas tablas a la vista y un cantero que le dice cuál aguanta lo que quiere hacer. La visita dura una hora y no se paga.',
			'cta_1'   => 'Pedir cita',
			'cta_2'   => '973 00 00 00',
			'img'     => 'card-veta',
		),
		'services' => array(
			'eyebrow' => 'En la visita',
			'h2'      => 'Qué se puede hacer aquí',
			'cards'   => array(
				array( 'img' => 'sq-marmol',   'h3' => 'Ver tabla entera', 'p' => 'No una muestra de 10 cm: la tabla de la que saldría su pieza.' ),
				array( 'img' => 'card-detalle','h3' => 'Probar acabados',  'p' => 'Pulido, apomazado y envejecido sobre el mismo material.' ),
				array( 'img' => 'card-patio',  'h3' => 'Cerrar despiece',  'p' => 'Con su plano encima de la mesa y el oficial que va a cortar.' ),
			),
		),
		'booking'  => array(
			'eyebrow' => 'Cita',
			'h2'      => 'Reserve una hora',
			'lede'    => 'Atendemos de uno en uno para que el cantero esté con usted toda la visita.',
			'fields'  => array(
				array( 'nombre', 'Nombre', 'text' ),
				array( 'tel', 'Teléfono', 'tel' ),
			),
			'day_lbl' => 'Día',
			'days'    => array( 'Lunes 22', 'Martes 23', 'Miércoles 24', 'Jueves 25', 'Viernes 26' ),
			'slot_lbl'=> 'Hora',
			'slots'   => array( '09:00', '10:30', '12:00', '16:00', '17:30' ),
			'submit'  => 'Pedir cita',
			'small'   => 'Le confirmamos por teléfono el mismo día. Si no puede venir, avise y liberamos la hora.',
		),
		'gallery'  => array(
			'eyebrow' => 'El sitio',
			'h2'      => 'Cómo es por dentro',
			'items'   => array( 'card-cantero', 'card-labra', 'sq-pizarra', 'card-mueble', 'sq-manos', 'hero-taller' ),
		),
		'quotes'   => array(
			'eyebrow' => 'Reseñas',
			'h2'      => 'Lo que dicen los que vinieron',
			'items'   => array(
				array( 'Fuimos con el plano de la cocina y salimos con el despiece cerrado. Una hora bien empleada.', 'Jordi Camps', 'Vallmoll' ),
				array( 'Me enseñaron la tabla entera, no un trocito. Se ve la veta de verdad.', 'Aina Roca', 'Barcelona' ),
				array( 'Nos dijeron que la caliza que queríamos no valía para el exterior. Nos ahorraron el disgusto.', 'Marta Vilanova', 'Lleida' ),
			),
		),
		'nap'      => array(
			'eyebrow' => 'Cómo llegar',
			'h2'      => 'Alcarràs, Lleida',
			'addr'    => array( 'Ctra. de la Cantera, 4', '25180 Alcarràs · Lleida' ),
			'phone'   => '973 00 00 00',
			'mail'    => 'showroom@piedravaldes.example',
			'hours'   => array(
				array( 'Lunes a jueves', '08:00 – 13:30 · 15:00 – 18:00' ),
				array( 'Viernes', '08:00 – 14:00' ),
				array( 'Sábado y domingo', 'Cerrado' ),
			),
			'note'    => 'Aparcamiento propio. Entrada por el patio de bloques, no por la nave.',
			'img'     => 'pan-fachada',
		),
		'footer'   => array(
			'tag'   => 'Piedra Valdés · Ctra. de la Cantera 4, 25180 Alcarràs, Lleida · 973 00 00 00',
			'links' => array( 'Cómo llegar', 'Horarios', 'Privacidad' ),
			'legal' => 'Piedra Valdés SL · Maqueta interna NovaMira, no publicada.',
		),
	),

	/* TPL-E-01 · Visual / Brand-Led. The catalogue arrives through CATEGORIES and a lookbook, never
	   through a grid of SKUs: this is the shop where the reader is browsing a world, not looking up
	   a part number. TPL-E-02 puts eight products above the fold; this one puts three doors. */
	'TPL-E-01' => array(
		'tpl'      => 'TPL-E-01',
		'tpl_name' => 'Visual / Brand-Led',
		'site'     => 'ecommerce',
		'site_es'  => 'Ecommerce',
		'fits'     => 'Moda, decoración, joyería, mobiliario, cosmética, cualquier compra que entra por el ojo',
		'dna'      => 'COMP-HERO slider · COMP-CATEGORY-CARD · COMP-GALLERY lookbook · COMP-BOOKING',
		'wire'     => 'COMP-ANNOUNCEMENT · COMP-HEADER · COMP-HERO slider · COMP-CATEGORY-CARD · COMP-PRODUCT-CAROUSEL · COMP-GALLERY · COMP-TESTIMONIAL · COMP-BOOKING · COMP-NEWSLETTER · COMP-FOOTER',
		'announce' => 'Envío en 72 h · muestra física gratuita con cada pedido',
		'nav'      => array( 'Encimeras', 'Suelos', 'Baño', 'Proyectos' ),
		'search'   => 'Buscar material, acabado, formato…',
		'tools'    => array( 'Cuenta' ),
		'cart'     => 'Cesta',
		'cart_n'   => '2',
		'hero'     => array(
			'eyebrow' => 'Colección 2026',
			'h1'      => 'Piedra que se elige mirándola',
			'lede'    => 'Tres familias de material, una sola cantera y el mismo taller detrás de cada corte.',
			'cta_1'   => 'Ver la colección',
			'cta_2'   => 'Pedir muestra',
			/* El MISMO fotograma con el que arranca el slider, y no una foto elegida aparte. La
			   puerta de arriba lo exige y tiene razon: si `TGL-HERO-TYPE` cambiara tambien la
			   imagen, dejaria de ser un toggle de COMPORTAMIENTO y nadie podria leer en la barra
			   de datos que la diferencia entre dos tiras es una sola posicion movida. */
			'img'     => 'hero-cantera',
		),
		'cats'     => array(
			'eyebrow' => 'Por dónde empezar',
			'h2'      => 'Tres familias',
			'items'   => array(
				array( 'Mármoles', 'Veta viva, para interior', '18 referencias', 'sq-marmol' ),
				array( 'Granitos', 'Grano cerrado, para todo', '11 referencias', 'sq-pizarra' ),
				array( 'Piedra labrada', 'Fachada y patrimonio', '6 referencias', 'card-labra' ),
			),
		),
		'carousel' => array(
			'eyebrow' => 'Se llevan ahora',
			'h2'      => 'Lo más pedido esta temporada',
			'cards'   => array(
				array( 'img' => 'sq-marmol',   'h3' => 'Crema Levante',    'p' => '189 €/m²' ),
				array( 'img' => 'card-veta',   'h3' => 'Veta dorada',      'p' => '236 €/m²' ),
				array( 'img' => 'sq-pizarra',  'h3' => 'Gris Quintana',    'p' => '164 €/m²' ),
				array( 'img' => 'card-mueble', 'h3' => 'Frente de mueble', 'p' => '412 €' ),
			),
		),
		'gallery'  => array(
			'eyebrow' => 'Lookbook',
			'h2'      => 'Cómo queda puesta',
			'items'   => array( 'hero-encimera', 'card-mueble', 'card-veta', 'pan-fachada', 'card-detalle', 'card-patio' ),
		),
		'quotes'   => array(
			'eyebrow' => 'Quien ya la tiene',
			'h2'      => 'Tres casas',
			'items'   => array(
				array( 'Elegí por foto y acerté porque me mandaron la tabla entera antes de cortar.', 'Aina Roca', 'Barcelona' ),
				array( 'La veta sigue de una pieza a la otra. Se nota que salió del mismo bloque.', 'Jordi Camps', 'Vallmoll' ),
				array( 'Vinieron a medir con la cocina puesta y no hubo sorpresas.', 'Marta Vilanova', 'Lleida' ),
			),
		),
		'booking'  => array(
			'eyebrow' => 'Asesoramiento',
			'h2'      => 'Pida una cita de medición',
			'lede'    => 'Vamos a su obra con láser y muestras. La visita no se cobra si el pedido sale adelante.',
			'fields'  => array(
				array( 'nombre', 'Nombre', 'text' ),
				array( 'tel', 'Teléfono', 'tel' ),
			),
			'day_lbl' => 'Día',
			'days'    => array( 'Lunes 22', 'Martes 23', 'Miércoles 24', 'Jueves 25', 'Viernes 26' ),
			'slot_lbl'=> 'Franja',
			'slots'   => array( 'Mañana', 'Mediodía', 'Tarde' ),
			'submit'  => 'Pedir cita',
			'small'   => 'Le confirmamos por teléfono el mismo día.',
		),
		'news'     => array(
			'eyebrow' => 'Boletín',
			'h2'      => 'Le avisamos cuando entre material nuevo',
			'lede'    => 'Un correo por colección. Nunca ofertas.',
			'label'   => 'Su correo',
			'cta'     => 'Avisarme',
			'small'   => 'Puede darse de baja en cualquier correo.',
		),
		'footer'   => array(
			'tag'   => 'Piedra Valdés · colección 2026 · Alcarràs, Lleida',
			'links' => array( 'Envíos', 'Devoluciones', 'Privacidad' ),
			'legal' => 'Piedra Valdés SL · Maqueta interna NovaMira, no publicada.',
		),
	),

	/* TPL-E-04 · Categories First. The catalogue is WIDE, so the page routes before it sells: a
	   dense category grid first, then products organised by tab. TPL-E-02 answers "I know what I
	   want"; this one answers "I know roughly where to look". */
	'TPL-E-04' => array(
		'tpl'      => 'TPL-E-04',
		'tpl_name' => 'Categories First',
		'site'     => 'ecommerce',
		'site_es'  => 'Ecommerce',
		'fits'     => 'Catálogos amplios: ferretería, suministro industrial, papelería, repuestos, distribución',
		'dna'      => 'COMP-CATEGORY-GRID · COMP-PRODUCT-TABS · COMP-BENEFITS',
		'wire'     => 'COMP-ANNOUNCEMENT · COMP-HEADER · COMP-HERO · COMP-CATEGORY-GRID · COMP-PRODUCT-TABS · COMP-BENEFITS · COMP-CTA · COMP-CATEGORY-CARD · COMP-NEWSLETTER · COMP-FOOTER',
		'announce' => 'Más de 400 referencias en stock · corte a medida en 72 h',
		'nav'      => array( 'Catálogo', 'Formatos', 'Profesionales', 'Ayuda' ),
		'search'   => 'Referencia, material o formato…',
		'tools'    => array( 'Cuenta', 'Pedidos' ),
		'cart'     => 'Carrito',
		'cart_n'   => '5',
		'hero'     => array(
			'eyebrow' => 'Catálogo completo',
			'h1'      => 'Cuatrocientas referencias, ordenadas',
			'lede'    => 'Por material, por formato o por uso. Si sabe la referencia, búsquela arriba.',
			'cta_1'   => 'Ver el catálogo',
			'cta_2'   => 'Cuenta de profesional',
			'img'     => 'card-veta',
		),
		'grid'     => array(
			'eyebrow' => 'Por dónde empezar',
			'h2'      => 'Categorías',
			'items'   => array(
				array( 'Encimeras', '84' ), array( 'Suelos', '112' ), array( 'Fachada', '46' ),
				array( 'Baño', '38' ), array( 'Peldaños', '29' ), array( 'Plaqueta', '51' ),
				array( 'Chimeneas', '17' ), array( 'Restos de bancada', '23' ),
			),
		),
		'tabs'     => array(
			'eyebrow' => 'Novedades',
			'h2'      => 'Lo último por familia',
			'panels'  => array(
				array( 'Mármoles', array(
					array( 'img' => 'sq-marmol',   'h3' => 'Crema Levante',      'p' => '189 €/m²' ),
					array( 'img' => 'card-veta',   'h3' => 'Veta dorada',        'p' => '236 €/m²' ),
					array( 'img' => 'card-detalle','h3' => 'Blanco Macael',      'p' => '212 €/m²' ),
					array( 'img' => 'card-mueble', 'h3' => 'Travertino romano',  'p' => '178 €/m²' ),
				) ),
				array( 'Granitos', array(
					array( 'img' => 'sq-pizarra',  'h3' => 'Gris Quintana',      'p' => '164 €/m²' ),
					array( 'img' => 'card-patio',  'h3' => 'Negro Zimbabue',     'p' => '241 €/m²' ),
					array( 'img' => 'card-labra',  'h3' => 'Rosa Porriño',       'p' => '156 €/m²' ),
					array( 'img' => 'sq-manos',    'h3' => 'Blanco Cristal',     'p' => '183 €/m²' ),
				) ),
				array( 'Labrada', array(
					array( 'img' => 'pan-fachada', 'h3' => 'Sillar de arenisca', 'p' => '96 €' ),
					array( 'img' => 'card-labra',  'h3' => 'Capitel liso',       'p' => '340 €' ),
					array( 'img' => 'hero-taller', 'h3' => 'Moldura corrida',    'p' => '48 €/ml' ),
					array( 'img' => 'card-cantero','h3' => 'Peldaño macizo',     'p' => '138 €' ),
				) ),
			),
		),
		'benefits' => array( 'Corte a medida sin coste', 'Envío en 72 h a península', 'Cuenta de profesional con precios netos' ),
		'band'     => array(
			'eyebrow' => 'Profesionales',
			'h2'      => '¿Compra para obra?',
			'lede'    => 'Abrimos cuenta con precios netos, albarán por obra y pago a treinta días.',
			'cta_1'   => 'Abrir cuenta',
			'cta_2'   => 'Hablar con ventas',
		),
		'cats'     => array(
			'eyebrow' => 'También buscan',
			'h2'      => 'Tres entradas rápidas',
			'items'   => array(
				array( 'Restos de bancada', 'Piezas sueltas rebajadas', '23 referencias', 'card-veta' ),
				array( 'Formato plaqueta', '30×15 y 40×20', '51 referencias', 'card-detalle' ),
				array( 'Stock inmediato', 'Sale hoy del almacén', '96 referencias', 'sq-marmol' ),
			),
		),
		'news'     => array(
			'eyebrow' => 'Boletín',
			'h2'      => 'Entradas de stock y bajadas de precio',
			'lede'    => 'Un correo al mes con lo que ha entrado y lo que ha bajado.',
			'label'   => 'Su correo',
			'cta'     => 'Suscribirme',
			'small'   => 'Puede darse de baja en cualquier correo.',
		),
		'footer'   => array(
			'tag'   => 'Piedra Valdés · catálogo y suministro · Alcarràs, Lleida',
			'links' => array( 'Formatos', 'Envíos', 'Profesionales' ),
			'legal' => 'Precios sin IVA para cuenta profesional. Maqueta interna NovaMira.',
		),
	),

	'TPL-E-02' => array(
		'tpl'      => 'TPL-E-02',
		'tpl_name' => 'Catalog / Product-First',
		/* `Ecommerce ideal`, transcribed from TPL-E-02-catalog-product-first.md §1. */
		'fits'     => 'Electrónica, repuestos, librería, ferretería, insumos',
		'site'     => 'ecommerce',
		'site_es'  => 'Ecommerce',
		'dna'      => 'COMP-HEADER search-first · COMP-HERO mini · COMP-PRODUCT-GRID · COMP-PRODUCT-CAROUSEL · COMP-FAQ + COMP-CONTACT-DIRECT',
		'wire'     => 'COMP-ANNOUNCEMENT · COMP-HEADER · COMP-HERO mini · COMP-PRODUCT-GRID · COMP-PRODUCT-CAROUSEL · COMP-BENEFITS · COMP-FAQ · COMP-CONTACT-DIRECT · COMP-FOOTER',
		'announce' => 'Envío en 72 h a península · Corte a medida sin coste',
		'search'   => 'Buscar mármol, granito, pizarra…',
		'tools'    => array( 'Cuenta' ),
		'cart'     => 'Carrito',
		'cart_n'   => '2',
		'hero'     => array(
			'h1'   => 'Tienda',
			'lede' => 'Piedra natural cortada a medida, del bloque a tu obra.',
			'cats' => array( 'Encimeras', 'Suelos', 'Fachada', 'Chimeneas', 'Baño', 'Restos de serie' ),
			'img'  => 'pan-fachada',
		),
		'prods'    => array(
			'eyebrow' => 'Catálogo',
			'h2'      => 'Destacados',
			'cards'   => array(
				array( 'img' => 'sq-marmol', 'h3' => 'Mármol Crema Levante', 'p' => '189 €/m²' ),
				array( 'img' => 'sq-pizarra', 'h3' => 'Granito Gris Quintana', 'p' => '164 €/m²' ),
				array( 'img' => 'card-veta', 'h3' => 'Panel de veta dorada', 'p' => '236 €/m²' ),
				array( 'img' => 'card-mueble', 'h3' => 'Frente de mueble en piedra', 'p' => '412 €' ),
				array( 'img' => 'card-detalle', 'h3' => 'Plaqueta labrada 30×15', 'p' => '47 €/m²' ),
				array( 'img' => 'card-labra', 'h3' => 'Bloque para labra', 'p' => '320 €' ),
				array( 'img' => 'card-patio', 'h3' => 'Sillar de arenisca', 'p' => '96 €' ),
				array( 'img' => 'sq-manos', 'h3' => 'Canto pulido a mano', 'p' => '28 €/ml' ),
			),
		),
		/* TPL-PDP-01 · the product page for the FIRST tile of the grid on the home, so the two pages
		   are demonstrably the same shop. Its price and its finish options match that tile. */
		'pdp'      => array(
			'crumbs'  => array( 'Inicio', 'Catálogo', 'Mármol Crema Levante' ),
			'h1'      => 'Mármol Crema Levante',
			'price'   => '189 €/m²',
			'lede'    => 'Caliza marmórea de Novelda, grano fino y veta discreta. Corte a medida sobre plano o sobre medición en obra.',
			'main'    => 'sq-marmol',
			'thumbs'  => array( 'card-veta', 'card-detalle', 'card-mueble' ),
			'opt_lbl' => 'Acabado',
			'opts'    => array( 'Pulido', 'Apomazado', 'Envejecido' ),
			'qty_lbl' => 'Metros cuadrados',
			'cta'     => 'Añadir al carrito',
			'ship'    => 'Corte a medida sin coste · envío en 72 h a península',
			'acc'     => array(
				array( 'Descripción', 'Bloque de Novelda, densidad 2.690 kg/m³ y absorción por debajo del 0,4%. Apto para interior en suelo, encimera y baño. En exterior sólo en fachada ventilada.' ),
				array( 'Envíos', 'Palet completo a península en 72 h laborables. Piezas cortadas a medida, entre diez y quince días según despiece. Baleares y Canarias, consultar.' ),
				array( 'Devoluciones', 'Material de stock sin cortar, treinta días. El corte a medida no admite devolución salvo defecto, y el defecto lo peritamos nosotros en 48 h.' ),
			),
			'badges'  => array( 'Muestra física gratuita', 'Despiece incluido en el precio', 'Garantía de veta continua' ),
		),
		'carousel' => array(
			'eyebrow' => 'Cross-sell',
			'h2'      => 'Más vendidos',
			'cards'   => array(
				array( 'img' => 'card-cantero', 'h3' => 'Peldaño macizo 120 cm', 'p' => '138 €' ),
				array( 'img' => 'hero-encimera', 'h3' => 'Encimera Blanco Macael 3 m', 'p' => '890 €' ),
				array( 'img' => 'hero-cantera', 'h3' => 'Zócalo pulido 8 cm', 'p' => '19 €/ml' ),
				array( 'img' => 'hero-taller', 'h3' => 'Kit de sellado mineral 1 L', 'p' => '24 €' ),
			),
		),
		'benefits' => array(
			array( 'Envío en 72 h', 'A península, con seguimiento.' ),
			array( 'Corte a medida', 'Sin coste sobre el precio de tabla.' ),
			array( 'Pago en 3 plazos', 'Sin intereses desde 200 €.' ),
			array( 'Devolución 30 días', 'En piezas de catálogo sin cortar.' ),
		),
		/* COMP-FAQ + COMP-CONTACT-DIRECT — TPL-E-02's closing pair, both `[fijo · ADN]` in the
		   template doc, so both are rendered rather than treated as optional furniture. The
		   questions are the ones a stone buyer actually asks: plazo, medida, factura, devolución. */
		'faq'      => array(
			'eyebrow' => 'Antes de pedir',
			'h2'      => 'Lo que preguntan siempre',
			'rows'    => array(
				array( '¿Cuánto tarda una pieza cortada a medida?', 'De 5 a 9 días laborables desde que aprobás el plano de corte. El catálogo sin cortar sale en 72 h.' ),
				array( '¿Cómo mando las medidas?', 'Plano, croquis a mano o las medidas por WhatsApp. Si la obra está en Alicante o Murcia, vamos a medir sin coste.' ),
				array( '¿Hacéis factura con IVA desglosado?', 'Sí, en todos los pedidos. Para empresa, indicá el CIF en el paso de datos.' ),
				array( '¿Puedo devolver una pieza cortada?', 'No: el corte es a medida y no vuelve a stock. Las piezas de catálogo sin cortar, 30 días.' ),
				array( '¿Sirven muestras?', 'Sí, hasta 3 muestras de 10×10 gratis. La piedra natural varía de veta entre bloques.' ),
			),
		),
		'close'    => array(
			'eyebrow' => 'Mostrador',
			'h2'      => '¿No encontraste tu pieza?',
			'lede'    => 'Consultanos stock, plazo y presupuesto por pieza. Trabajamos material que no está publicado.',
			/* THE CONTROL THIS BAND NEVER HAD. TPL-C-01 closes on a form and TPL-E-02 closed on
			   three phone numbers — help, not an ask. The channels stay, because a counter still
			   answers the phone and § "COMP-CONTACT-DIRECT" is right that a catalogue closes on
			   what happens when finding failed; what was missing is that the band never ASKED for
			   anything before listing the ways to ask. One control, one line of reassurance beside
			   it, and the three channels underneath it rather than instead of it. */
			'cta'     => 'Pedir presupuesto a medida',
			'cta_sub' => 'Respuesta el mismo día laborable',
			'chans'   => array(
				array( 'Teléfono', '965 60 41 22', 'Lun a vie, 8:00–18:00' ),
				array( 'WhatsApp', '+34 600 41 22 08', 'Mandá el croquis y te presupuestamos' ),
				array( 'Email', 'mostrador@piedravaldes.es', 'Respuesta el mismo día laborable' ),
			),
		),
		'footer'   => array(
			'tag'   => 'Tienda de piedra natural · Novelda, Alicante',
			'links' => array( 'Envíos', 'Devoluciones', 'Aviso legal', 'Privacidad', 'Cookies' ),
			'legal' => '© 2026 Piedra Valdés S.L. · Ctra. de la Cantera 4, Novelda · IVA incluido',
		),
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
	} elseif ( ! in_array( $CONTENT[ $cn_k ]['head_mode'], array( 'index', 'rule', 'tight' ), true ) ) {
		fail( "template `$cn_k` asks for head mode `{$CONTENT[ $cn_k ]['head_mode']}`, which is not one"
			. ' of index / rule / tight' );
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
$BG_SLUG       = $CONTENT['TPL-C-01']['hero']['img'];
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
	/* CASA TERRAZZA GOES FIRST because the catalogue is read from the top and this is the entry
	   that answers the question the other forty cannot. PERS-EDITORIAL and not PERS-MATTER: the
	   restaurant wants the generous density and the 88px display, and `matter`'s classic scale would
	   have made a room that photographs like this read solid instead of worth the trip.

	   IT SITS ON TPL-C-06 AND NOT ON TPL-C-05, and that correction is the point of this pass. Built
	   on TPL-C-05 it was the same nine sections in the same order as the quarry with a brown palette
	   on top — the skin changed and the skeleton did not, which is the failure this repo already had
	   in August with the eight "design personalities". TPL-C-06 declares its own wireframe, and the
	   audit's `RT_TPL_TOO_SIMILAR` measures the distance instead of taking my word for it. */
	array( 'tpl' => 'TPL-C-06-terrazza', 'anchor' => 'editorial' ),
	/* Los tres de automoción. Cada uno sobre un ANCLA distinta y un ESQUELETO distinto: entre los
	   cuatro negocios de marca quedan cubiertas las cuatro anclas del sistema, que es la prueba de
	   que la variedad no depende de la paleta. */
	array( 'tpl' => 'TPL-C-07-aranda',  'anchor' => 'institutional' ),
	array( 'tpl' => 'TPL-C-08-auria',   'anchor' => 'direct' ),
	array( 'tpl' => 'TPL-C-09-bergara', 'anchor' => 'matter' ),
	/* Los tres de salud dental. */
	array( 'tpl' => 'TPL-C-10-arbea',    'anchor' => 'institutional' ),
	array( 'tpl' => 'TPL-C-11-alinea',   'anchor' => 'editorial' ),
	array( 'tpl' => 'TPL-C-12-urgencia', 'anchor' => 'direct' ),
	array( 'tpl' => 'TPL-C-01', 'anchor' => 'editorial', 'tgl' => array( 'TGL-HERO-TYPE' => 'slider' ) ),
	array( 'tpl' => 'TPL-C-01', 'anchor' => 'direct' ),
	array( 'tpl' => 'TPL-C-01', 'anchor' => 'matter' ),
	array( 'tpl' => 'TPL-C-01', 'anchor' => 'institutional' ),
	/* TPL-C-02 · the second corporate archetype. Its four anchors sit between C-01's and E-02's so
	   the catalogue reads corporate → corporate → ecommerce rather than jumping. */
	array( 'tpl' => 'TPL-C-02', 'anchor' => 'institutional' ),
	array( 'tpl' => 'TPL-C-02', 'anchor' => 'editorial' ),
	array( 'tpl' => 'TPL-C-02', 'anchor' => 'matter' ),
	array( 'tpl' => 'TPL-C-02', 'anchor' => 'direct' ),
	/* TPL-C-03 · el tercer arquetipo corporativo. */
	array( 'tpl' => 'TPL-C-03', 'anchor' => 'editorial' ),
	array( 'tpl' => 'TPL-C-03', 'anchor' => 'matter' ),
	array( 'tpl' => 'TPL-C-03', 'anchor' => 'direct' ),
	array( 'tpl' => 'TPL-C-03', 'anchor' => 'institutional' ),
	/* TPL-E-03 · el segundo arquetipo de ecommerce. */
	array( 'tpl' => 'TPL-E-03', 'anchor' => 'matter' ),
	array( 'tpl' => 'TPL-E-03', 'anchor' => 'editorial' ),
	array( 'tpl' => 'TPL-E-03', 'anchor' => 'institutional' ),
	array( 'tpl' => 'TPL-E-03', 'anchor' => 'direct' ),
	/* TPL-C-04 · la landing de oferta única. */
	array( 'tpl' => 'TPL-C-04', 'anchor' => 'direct' ),
	array( 'tpl' => 'TPL-C-04', 'anchor' => 'matter' ),
	array( 'tpl' => 'TPL-C-04', 'anchor' => 'editorial' ),
	array( 'tpl' => 'TPL-C-04', 'anchor' => 'institutional' ),
	/* TPL-E-05 · la campaña con fecha. */
	array( 'tpl' => 'TPL-E-05', 'anchor' => 'direct' ),
	array( 'tpl' => 'TPL-E-05', 'anchor' => 'matter' ),
	array( 'tpl' => 'TPL-E-05', 'anchor' => 'editorial' ),
	array( 'tpl' => 'TPL-E-05', 'anchor' => 'institutional' ),
	/* TPL-C-05 · el negocio con puerta. */
	array( 'tpl' => 'TPL-C-05', 'anchor' => 'matter' ),
	array( 'tpl' => 'TPL-C-05', 'anchor' => 'institutional' ),
	array( 'tpl' => 'TPL-C-05', 'anchor' => 'editorial' ),
	array( 'tpl' => 'TPL-C-05', 'anchor' => 'direct' ),
	/* TPL-E-01 · la tienda que entra por el ojo. */
	array( 'tpl' => 'TPL-E-01', 'anchor' => 'editorial' ),
	array( 'tpl' => 'TPL-E-01', 'anchor' => 'matter' ),
	array( 'tpl' => 'TPL-E-01', 'anchor' => 'direct' ),
	array( 'tpl' => 'TPL-E-01', 'anchor' => 'institutional' ),
	/* TPL-E-04 · el catálogo ancho. */
	array( 'tpl' => 'TPL-E-04', 'anchor' => 'institutional' ),
	array( 'tpl' => 'TPL-E-04', 'anchor' => 'direct' ),
	array( 'tpl' => 'TPL-E-04', 'anchor' => 'editorial' ),
	array( 'tpl' => 'TPL-E-04', 'anchor' => 'matter' ),
	array( 'tpl' => 'TPL-E-02', 'anchor' => 'editorial' ),
	array( 'tpl' => 'TPL-E-02', 'anchor' => 'direct' ),
	array( 'tpl' => 'TPL-E-02', 'anchor' => 'matter' ),
	array( 'tpl' => 'TPL-E-02', 'anchor' => 'institutional' ),
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
  --content-width:clamp(1140px, 68vw, 100vw);
  --pad-x-mobile:20px; --pad-x-tablet:32px;
  --radius-card:12px; --radius-button:8px; --radius-image:8px; --radius-input:8px; --radius-container:16px;
  --btn-padding:.875rem 1.75rem; --btn-border-width:1.5px;
  --ease:cubic-bezier(.22,1,.36,1);
  --fs-body:clamp(1rem, 1.2vw, 1.25rem);
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
  border-radius:999px;background:var(--c-accent);color:var(--c-on-accent);
  font-size:.7rem;font-weight:700;line-height:1}
/* COMP-HERO mini: ~20vh is the ADN, so the height is pinned here rather than by --ratio-hero.
   With an explicit height and no `aspect-ratio`, the automatic-minimum-size transfer that makes
   `min-width:0` load-bearing on `.frame` cannot fire at all. */
.mini .frame{aspect-ratio:auto;height:clamp(120px,20vh,220px)}
.mini .head{gap:var(--sp-xs)}
.cats{display:flex;gap:var(--sp-s);overflow-x:auto;scrollbar-width:none;font-size:var(--fs-nav)}
.cats::-webkit-scrollbar{display:none}
.cats a{white-space:nowrap;padding:.35rem .75rem;border:1px solid var(--c-border);
  border-radius:999px;color:var(--c-text-soft);transition:border-color var(--dur-color) var(--ease)}
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
.faqlist{border-top:1px solid var(--c-border);max-width:72ch}
.faqlist > details{border-bottom:1px solid var(--c-border)}
.faqlist summary{cursor:pointer;padding-block:var(--sp-s);font-size:var(--fs-small);
  font-weight:600;color:var(--c-text)}
.faqlist summary::marker{color:var(--c-text-muted)}   /* budget: a disclosure triangle is furniture */
.faqlist p{padding-bottom:var(--sp-s);font-size:var(--fs-small);color:var(--c-text-soft);
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
      border:1px solid var(--c-border);border-radius:999px;background:transparent;
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
.quotes blockquote::before{content:"\201C";position:absolute;right:100%;top:-.1em;
                           margin-right:.06em;color:var(--c-text-muted);
                           font-size:1.6em;line-height:1}
/* Below the tablet breakpoint there is no margin to hang into: a glyph at `right:100%` would sit
   outside the canvas and be clipped, so it comes back into the flow instead of disappearing. */
@media(max-width:767px){
  .quotes blockquote::before{position:static;display:block;margin:0 0 -.35em}
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
          border-radius:999px;padding:.3rem .8rem;white-space:nowrap;text-decoration:none;
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
     border:1px solid var(--c-border);border-radius:999px;padding:.12rem .5rem;
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
.fig dt{font-family:var(--font-primary);font-size:var(--fs-h1);line-height:1;
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
   that should stay identical start to drift. */
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

   WHAT IS NOT HERE IS THE POINT. Colour, type, and nothing else. A brand that also moved scale or
   density would be a fifth anchor wearing a business name, and the five axis chips on its own card
   would be describing a page that no longer matches them. */
$brand_css = array( '/* ══════════ THE BRANDS — ground, accent and type. Never an axis. ══════════ */' );
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
	$brand_css[] = '[data-brand="' . $b_k . '"]{'
		. '--c-bg:' . $b_gr['bg'] . ';--c-bg-alt:' . $b_gr['alt'] . ';--c-text:' . $b_gr['text'] . ';'
		. '--c-accent:' . $b_ac['hex'] . ';--c-on-accent:' . $b_ac['on'] . ';'
		. '--font-primary:' . $b_v['font_1'] . ';--font-secondary:' . $b_v['font_2'] . '}';
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
.dish .n{font-family:var(--font-primary);font-size:1.0625rem;min-width:0;order:1}
.dish .dots{order:2;flex:1 1 1.5rem;min-width:1.5rem;transform:translateY(-.32em);
  border-bottom:1px dotted color-mix(in srgb,var(--c-text) 42%,transparent)}
.dish .p{order:3;font-variant-numeric:tabular-nums;white-space:nowrap;font-weight:600}
.dish .d{order:4;flex:1 0 100%;margin:.2rem 0 0;color:var(--c-text-muted);font-size:.875rem;
  max-width:46ch}
.menu-list .foot{margin:var(--sp-l) 0 0;color:var(--c-text-muted);font-size:.875rem}

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
.figq .who span{color:var(--c-text-muted);font-size:.875rem}
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
@media(min-width:1024px){
  [data-comp="lp-asymmetric"] .booking-split .head{grid-column:c 1 / c 7}
  [data-comp="lp-asymmetric"] .booking-split .bookform{grid-column:c 1 / c 7}
  [data-comp="lp-asymmetric"] .booking-split .bookmedia{grid-column:c 8 / wide-end;
    grid-row:1 / span 2}
}

/* ══════════ THE SECTION HEAD, PER TEMPLATE ══════════
   Three treatments. `index` is what the four anchors already do — a numbered head, decorated the
   anchor's own way. The other two switch that decoration off and put a different gesture in its
   place, so two brands on one anchor no longer open every section identically. */

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
  grid-template-columns:repeat(auto-fill,minmax(min(100%,18rem),1fr))}
.trcard{display:flex;flex-direction:column;background:var(--c-bg);overflow:hidden;
  border-radius:var(--radius-card);box-shadow:var(--elev-rest)}
.trcard .frame{aspect-ratio:16/10;border-radius:0}
.trcard .frame img{width:100%;height:100%;object-fit:cover;display:block}
.trbody{display:flex;flex-direction:column;gap:.4rem;flex:1;padding:var(--sp-m)}
.trcard h3{margin:0;font-size:1.0625rem}
.trcard p{margin:0;color:var(--c-text-muted);font-size:.875rem}
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
.bashot figcaption b{font-family:var(--font-primary);font-size:1.0625rem}
.bashot figcaption span{color:var(--c-text-muted);font-size:.875rem}

.medlist{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-l);
  grid-template-columns:repeat(auto-fill,minmax(min(100%,15rem),1fr))}
.medico .frame{aspect-ratio:1/1;border-radius:var(--radius-card);overflow:hidden}
.medico .frame img{width:100%;height:100%;object-fit:cover;display:block}
.medbody{display:grid;gap:.1rem;margin-top:var(--sp-s)}
.medbody b{font-family:var(--font-primary);font-size:1.0625rem}
.medbody span{color:var(--c-text-muted);font-size:.875rem}
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
.phase{display:grid;grid-template-columns:auto minmax(0,1fr);gap:var(--sp-m);
  padding-block:var(--sp-l);border-top:1px solid var(--c-border)}
.phase:last-child{border-bottom:1px solid var(--c-border)}
.phn{font-family:var(--font-primary);font-size:clamp(1.6rem,3vw,2.6rem);line-height:1;
  color:var(--c-text-muted);font-variant-numeric:tabular-nums}
.phbody{display:grid;gap:.3rem}
.phbody h3{margin:0;font-size:var(--fs-h3);font-family:var(--font-primary)}
.phmonths{font-weight:700;font-variant-numeric:tabular-nums}
.phbody p{margin:0;color:var(--c-text-muted);max-width:58ch}

.planlist{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-l)}
@media(min-width:900px){.planlist{grid-template-columns:repeat(3,minmax(0,1fr))}}
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
  font-size:.875rem}
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
.trow b{font-family:var(--font-primary);font-size:1.125rem}
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
  grid-template-columns:repeat(auto-fill,minmax(min(100%,17rem),1fr))}
.vcard{display:flex;flex-direction:column;background:var(--c-bg);overflow:hidden;
  border-radius:var(--radius-card);box-shadow:var(--elev-rest);
  transition:box-shadow var(--dur-lift) var(--ease)}
.vcard:hover{box-shadow:var(--elev-hover)}
.vcard .frame{aspect-ratio:4/3;border-radius:0}
.vcard .frame img{width:100%;height:100%;object-fit:cover;display:block}
.vbody{display:flex;flex-direction:column;gap:.5rem;flex:1;padding:var(--sp-m)}
.vcard h3{margin:0;font-size:1.0625rem;line-height:1.25}
/* Same reasoning as `.tfacts`: five fixed facts on a fixed 3-column grid, so every vehicle card
   spends exactly two rows on them and the prices below line up across the whole listing. */
.vfacts{list-style:none;margin:0;padding:0;display:grid;
  grid-template-columns:repeat(3,minmax(0,1fr));gap:.3rem}
.vfacts li{font-size:.8125rem;color:var(--c-text-muted);border:1px solid var(--c-border);
  border-radius:var(--radius-button);padding:.08rem .45rem;text-align:center;min-width:0}
.vprice{margin:auto 0 0;display:flex;align-items:baseline;gap:.5rem;flex-wrap:wrap}
.vprice b{font-family:var(--font-primary);font-size:1.375rem;font-variant-numeric:tabular-nums}
.vprice span{color:var(--c-text-muted);font-size:.875rem}
.vcard .btn{margin-top:.25rem;align-self:flex-start}

.tiform{display:grid;gap:var(--sp-m);width:min(100%,44rem)}
@media(min-width:900px){
  .tiform{grid-template-columns:1fr 1fr auto;align-items:end}
  .tiform .small{grid-column:1 / -1}
}

/* The last row is the TOTAL and it is the only one that gets weight: every row above it is an
   input the buyer can argue with, and the total is the number this section exists to stop hiding. */
.ftable{margin:0;width:min(100%,44rem)}
.frow{display:flex;justify-content:space-between;gap:var(--sp-m);align-items:baseline;
  padding-block:calc(var(--sp-s) * .9);border-bottom:1px solid var(--c-border)}
.frow dt{color:var(--c-text-muted)}
.frow dd{margin:0;font-variant-numeric:tabular-nums}
.ftotal{border-bottom:none;border-top:2px solid var(--c-text);margin-top:.3rem}
.ftotal dt,.ftotal dd{color:var(--c-text);font-weight:700;font-size:1.125rem}

.sec.badges{border-block:1px solid var(--c-border)}
.badgelist{list-style:none;margin:0;padding:0;display:grid;gap:var(--sp-l)}
@media(min-width:768px){.badgelist{grid-template-columns:repeat(4,minmax(0,1fr))}}
.badge b{display:block;font-family:var(--font-primary);line-height:1.05;
  font-size:clamp(1.25rem,2.1vw,1.75rem)}
.badge span{display:block;margin-top:.3rem;color:var(--c-text-muted);font-size:.9375rem}

.pnote{margin:var(--sp-l) 0 0;color:var(--c-text-muted);font-size:.875rem;max-width:64ch}

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
.pwhat b{font-size:1.0625rem;line-height:1.3}
.pwhat span{color:var(--c-text-muted);font-size:.875rem}
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
.hours-big .where a{font-size:1.0625rem}
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
  [data-comp="lp-strict-grid"] .band .head{grid-column:1/7}
  [data-comp="lp-strict-grid"] .band .formwrap{grid-column:7/13}
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
  [data-comp="lp-broken-grid"] .grid-sec .head{grid-column:c 1/c 9}
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
  [data-comp="lp-broken-grid"] .band .head{grid-column:c 1/c 10}
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
  [data-comp="lp-broken-grid"] .band .formwrap{grid-column:c 6/c 13}
  [data-comp="lp-broken-grid"] .pdp .pdp-gal{grid-column:c 1/c 10}
  [data-comp="lp-broken-grid"] .pdp .pdp-buy{grid-column:c 6/c 13}
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
  [data-comp="lp-asymmetric"] .band .head{grid-column:c 1/c 7}
  [data-comp="lp-asymmetric"] .band .formwrap{grid-column:c 7/c 13}
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
		$o .= '<a href="#">' . h( $n ) . '</a>';
	}
	return $o . '</nav><a class="btn btn-primary btn-sm" href="#">' . h( $C['nav_cta'] ) . '</a>'
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
		$o .= '<a href="#">' . h( $t ) . '</a>';
	}
	return $o . '<a class="cart" href="#">' . h( $C['cart'] ) . ' <b>' . h( $C['cart_n'] ) . '</b></a>'
		. '</div></div></div></header>';
}

/** Breadcrumb — every interior page opens with one, and the home never has one. */
function crumbs_html( $trail ) {
	$o    = '<nav class="crumbs" aria-label="Migas"><div class="canvas"><ol>';
	$last = count( $trail ) - 1;
	foreach ( $trail as $i => $t ) {
		$o .= ( $i === $last )
			? '<li aria-current="page">' . h( $t ) . '</li>'
			: '<li><a href="#">' . h( $t ) . '</a></li>';
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
		. '<div class="ctas"><a class="btn btn-primary" href="#">' . h( $S['cta_1'] ) . '</a>'
		. '<a class="btn btn-outline" href="#">' . h( $S['cta_2'] ) . '</a></div></div>'
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
		. '<h2>' . h( $fq['h2'] ) . '</h2></div><div class="qas">';
	foreach ( $fq['items'] as $q ) {
		$o[] = '<details><summary>' . h( $q[0] ) . '</summary><p>' . h( $q[1] ) . '</p></details>';
	}
	$o[] = '</div></div></section>';

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
function page_pdp( $anchor_key, $C, $BRAND, $uid ) {
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
	$o[] = '<section class="sec pdp" aria-label="' . h( $P['h1'] ) . '"><div class="canvas">'
		. '<div class="pdp-gal">'
		. '<figure class="frame"><img data-img="' . h( $mn['slug'] ) . '" alt="' . h( $mn['alt'] ) . '"'
		. ' width="' . $mn['w'] . '" height="' . $mn['h'] . '"></figure>'
		. '<ul class="pdp-thumbs">' . $thumbs . '</ul>'
		. '</div>'
		. '<div class="pdp-buy">'
		. '<h1>' . h( $P['h1'] ) . '</h1>'
		. '<p class="price pdp-price">' . h( $P['price'] ) . '</p>'
		. '<p class="muted">' . h( $P['lede'] ) . '</p>'
		. '<fieldset class="opts"><legend>' . h( $P['opt_lbl'] ) . '</legend>' . $opts . '</fieldset>'
		. '<div class="field qty"><label for="' . $uid . '-qty">' . h( $P['qty_lbl'] ) . '</label>'
		. '<input id="' . $uid . '-qty" type="number" value="4" min="1"></div>'
		. '<button class="btn btn-primary" type="button">' . h( $P['cta'] ) . '</button>'
		. '<p class="small muted pdp-ship">' . h( $P['ship'] ) . '</p>'
		. '</div></div></section>';

	// COMP-ACCORDION  [fijo]
	$o[] = '<section class="sec acc grid-sec bg-alt" aria-label="Detalle"><div class="canvas"><div class="qas">';
	foreach ( $P['acc'] as $i => $a ) {
		$o[] = '<details' . ( 0 === $i ? ' open' : '' ) . '><summary>' . h( $a[0] ) . '</summary>'
			. '<p>' . h( $a[1] ) . '</p></details>';
	}
	$o[] = '</div></div></section>';

	// COMP-TRUST-BADGES  [toggle] — the tick stays neutral, same reason as the benefits bar
	$o[] = '<section class="sec bar" aria-label="Garantías"><div class="canvas"><div class="items bens">';
	foreach ( $P['badges'] as $b ) {
		$o[] = '<p class="ben"><span class="bicon" aria-hidden="true">✓</span>' . h( $b ) . '</p>';
	}
	$o[] = '</div></div></section>';

	// COMP-PRODUCT-CAROUSEL  [toggle TGL-RELATED] — the home's carousel, because it is one shop
	$cr  = $C['carousel'];
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
 * TPL-C-02 · Institutional Trust.
 *
 * Its DNA separates from TPL-C-01 in one place that matters more than all the section names: it
 * closes with COMP-CTA and NOT with COMP-LEAD-FORM. C-01 chases a lead and the form dominates its
 * close; C-02 opens a conversation. Rendering a form here would collapse two archetypes into one,
 * which is exactly the defect the ecommerce family was rebuilt to fix.
 */
function strip_institutional( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$hero = $C['hero'];
	$im   = img( $hero['img'] );
	$o    = array();

	$o[] = head_corporate( $C, $BRAND );
	$o[] = '<main>';

	// 1 · COMP-HERO — imagen fija + claim institucional  [fijo · ADN]
	$o[] = '<section class="sec hero" aria-label="Presentación"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $hero['eyebrow'] ) . '</span>'
		. '<h1>' . h( $hero['h1'] ) . '</h1>'
		. '<p class="lede muted">' . h( $hero['lede'] ) . '</p>'
		. '<div class="ctas"><a class="btn btn-primary" href="#">' . h( $hero['cta_1'] ) . '</a>'
		. '<a class="btn btn-outline" href="#">' . h( $hero['cta_2'] ) . '</a></div></div>'
		. '<div class="media"><figure class="frame"><img data-img="' . h( $im['slug'] ) . '"'
		. ' alt="' . h( $im['alt'] ) . '" width="' . $im['w'] . '" height="' . $im['h'] . '"></figure></div>'
		. '</div></section>';

	// 2 · COMP-ABOUT  [fijo]
	$ab  = $C['about'];
	$ai  = img( $ab['img'] );
	$o[] = '<section class="sec about bg-alt" aria-label="Quiénes somos"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $ab['eyebrow'] ) . '</span>'
		. '<h2>' . h( $ab['h2'] ) . '</h2>';
	foreach ( $ab['body'] as $para ) {
		$o[] = '<p class="muted">' . h( $para ) . '</p>';
	}
	$o[] = '</div><div class="media"><figure class="frame"><img data-img="' . h( $ai['slug'] ) . '"'
		. ' alt="' . h( $ai['alt'] ) . '" width="' . $ai['w'] . '" height="' . $ai['h'] . '"></figure></div>'
		. '</div></section>';

	/* 3 · COMP-STATS  [fijo · ADN]
	   THE NUMBERS ARE IN THE HTML. Two of the four reference kits ship theirs as `0+` and fill them
	   with script on scroll, so their own live demo reads "0 clients" until the animation runs — and
	   reads that way for good to anyone with JS blocked, and to every crawler. On an archetype whose
	   entire job is credibility, a credential that only exists after a script is not a credential. */
	$st  = $C['stats'];
	$o[] = '<section class="sec stats" aria-label="Cifras"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $st['eyebrow'] ) . '</span></div>'
		. '<dl class="figs">';
	foreach ( $st['items'] as $it ) {
		$o[] = '<div class="fig"><dt>' . h( $it[0] ) . '</dt><dd>' . h( $it[1] ) . '</dd></div>';
	}
	$o[] = '</dl></div></section>';

	// 4 · COMP-SERVICES — áreas / especialidades  [fijo]
	$sv  = $C['services'];
	$o[] = '<section class="sec services grid-sec bg-alt" aria-label="Áreas"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $sv['eyebrow'] ) . '</span>'
		. '<h2>' . h( $sv['h2'] ) . '</h2></div><div class="items cols-3">';
	foreach ( $sv['cards'] as $c ) {
		$o[] = card_html( $anchor_key, $c );
	}
	$o[] = '</div></div></section>';

	// 5 · COMP-CREDENTIALS  [toggle TGL-CREDENTIALS]
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-CREDENTIALS' ) ) {
		$cd  = $C['credentials'];
		$o[] = '<section class="sec creds grid-sec" aria-label="Acreditaciones"><div class="canvas">'
			. '<div class="head stack"><span class="eyebrow">' . h( $cd['eyebrow'] ) . '</span>'
			. '<h2>' . h( $cd['h2'] ) . '</h2></div><ul class="feats">';
		foreach ( $cd['items'] as $it ) {
			$o[] = '<li><b>' . h( $it[0] ) . '</b><span>' . h( $it[1] ) . '</span></li>';
		}
		$o[] = '</ul></div></section>';
	}

	// 6 · COMP-TEAM  [toggle TGL-TEAM]
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-TEAM' ) ) {
		$tm  = $C['team'];
		$o[] = '<section class="sec team grid-sec bg-alt" aria-label="Equipo"><div class="canvas">'
			. '<div class="head stack"><span class="eyebrow">' . h( $tm['eyebrow'] ) . '</span>'
			. '<h2>' . h( $tm['h2'] ) . '</h2></div><ul class="items cols-3">';
		foreach ( $tm['items'] as $it ) {
			$ti  = img( $it[2] );
			$o[] = '<li class="member"><figure class="frame sq"><img data-img="' . h( $ti['slug'] ) . '"'
				. ' alt="' . h( $ti['alt'] ) . '" width="' . $ti['w'] . '" height="' . $ti['h'] . '"></figure>'
				. '<b>' . h( $it[0] ) . '</b><span>' . h( $it[1] ) . '</span></li>';
		}
		$o[] = '</ul></div></section>';
	}

	// 7 · COMP-TESTIMONIAL  [toggle TGL-TESTIMONIALS]
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-TESTIMONIALS' ) ) {
		$qt  = $C['quotes'];
		$o[] = '<section class="sec quotes grid-sec" aria-label="Referencias"><div class="canvas">'
			. '<div class="head stack"><span class="eyebrow">' . h( $qt['eyebrow'] ) . '</span>'
			. '<h2>' . h( $qt['h2'] ) . '</h2></div><ul class="items">';
		foreach ( $qt['items'] as $q ) {
			$o[] = '<li><figure><blockquote>' . h( $q[0] ) . '</blockquote>'
				. '<figcaption><b>' . h( $q[1] ) . '</b><span>' . h( $q[2] ) . '</span></figcaption>'
				. '</figure></li>';
		}
		$o[] = '</ul></div></section>';
	}

	// 8 · COMP-CTA sobrio  [fijo] — NO lead form; see the note on this archetype's `band` content
	$b   = $C['band'];
	$o[] = '<section class="sec band closing sober" aria-label="Contacto"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $b['eyebrow'] ) . '</span>'
		. '<h2>' . h( $b['h2'] ) . '</h2><p class="muted">' . h( $b['lede'] ) . '</p>'
		. '<div class="ctas"><a class="btn btn-primary" href="#">' . h( $b['cta_1'] ) . '</a>'
		. '<a class="btn btn-outline" href="#">' . h( $b['cta_2'] ) . '</a></div></div>'
		. '</div></section>';

	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );

	return number_heads( implode( "\n", $o ) );
}

/**
 * TPL-C-03 · Portfolio / Showcase.
 *
 * The archetype doc is blunt about what it refuses — "NO pricing, NO stats, NO forms largos",
 * "bajo en texto, alto en visual" — and the refusals are the design. So there is no stats band
 * here even though the four reference kits all have one, no lead form even though TPL-C-01's DNA
 * is built on one, and barely any prose. What there is, is the grid.
 */
function strip_showcase( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$hero = $C['hero'];
	$im   = img( $hero['img'] );
	$o    = array();

	$o[] = head_corporate( $C, $BRAND );
	$o[] = '<main>';

	/* 1 · COMP-HERO visual  [fijo · ADN]
	   The photograph is the hero and the words are a caption on it — "título mínimo, sin ruido",
	   says the doc. `.hero-visual` puts the copy OVER the image instead of beside it, which is the
	   one structural difference between this archetype's hero and TPL-C-01's. */
	$o[] = '<section class="sec hero hero-visual" aria-label="Obra"><div class="media-full">'
		. '<figure class="frame"><img data-img="' . h( $im['slug'] ) . '"'
		. ' alt="' . h( $im['alt'] ) . '" width="' . $im['w'] . '" height="' . $im['h'] . '"></figure></div>'
		. '<div class="canvas"><div class="head stack">'
		. '<span class="eyebrow">' . h( $hero['eyebrow'] ) . '</span>'
		. '<h1>' . h( $hero['h1'] ) . '</h1>'
		. '<p class="lede">' . h( $hero['lede'] ) . '</p>'
		. '<div class="ctas"><a class="btn btn-primary" href="#">' . h( $hero['cta_1'] ) . '</a>'
		. '<a class="btn btn-outline" href="#">' . h( $hero['cta_2'] ) . '</a></div>'
		. '</div></div></section>';

	/* 2 · COMP-PORTFOLIO-GRID  [fijo · ADN]
	   Image large, title, category, and an overlay on hover — the doc's own recipe. The overlay is
	   a HOVER state, so the accent whitelist never sees it; and it is not the only affordance,
	   because a hover that is the sole signal is invisible on a touch screen. The caption is always
	   there and the overlay only deepens it. */
	$wk  = $C['work'];
	$o[] = '<section class="sec work grid-sec" aria-label="Obra reciente"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $wk['eyebrow'] ) . '</span>'
		. '<h2>' . h( $wk['h2'] ) . '</h2></div><ul class="works">';
	foreach ( $wk['items'] as $it ) {
		$wi  = img( $it[2] );
		$o[] = '<li class="work-item"><a href="#">'
			. '<figure class="frame"><img data-img="' . h( $wi['slug'] ) . '"'
			. ' alt="' . h( $wi['alt'] ) . '" width="' . $wi['w'] . '" height="' . $wi['h'] . '"></figure>'
			. '<span class="work-cap"><b>' . h( $it[0] ) . '</b><span>' . h( $it[1] ) . '</span></span>'
			. '</a></li>';
	}
	$o[] = '</ul></div></section>';

	// 3 · COMP-SERVICES breve  [toggle TGL-SERVICES]
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-SERVICES' ) ) {
		$sv  = $C['services'];
		$o[] = '<section class="sec services grid-sec bg-alt" aria-label="Servicios"><div class="canvas">'
			. '<div class="head stack"><span class="eyebrow">' . h( $sv['eyebrow'] ) . '</span>'
			. '<h2>' . h( $sv['h2'] ) . '</h2></div><div class="items cols-3">';
		foreach ( $sv['cards'] as $c ) {
			$o[] = card_html( $anchor_key, $c );
		}
		$o[] = '</div></div></section>';
	}

	// 4 · COMP-ABOUT  [toggle TGL-ABOUT]
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-ABOUT' ) ) {
		$ab  = $C['about'];
		$ai  = img( $ab['img'] );
		$o[] = '<section class="sec about" aria-label="El estudio"><div class="canvas">'
			. '<div class="head stack"><span class="eyebrow">' . h( $ab['eyebrow'] ) . '</span>'
			. '<h2>' . h( $ab['h2'] ) . '</h2>';
		foreach ( $ab['body'] as $para ) {
			$o[] = '<p class="muted">' . h( $para ) . '</p>';
		}
		$o[] = '</div><div class="media"><figure class="frame"><img data-img="' . h( $ai['slug'] ) . '"'
			. ' alt="' . h( $ai['alt'] ) . '" width="' . $ai['w'] . '" height="' . $ai['h'] . '"></figure></div>'
			. '</div></section>';
	}

	// 5 · COMP-LOGOS  [toggle TGL-LOGOS]
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-LOGOS' ) ) {
		$lg  = $C['logos'];
		$o[] = '<section class="sec logos bg-alt" aria-label="Clientes"><div class="canvas">'
			. '<div class="head stack"><span class="eyebrow">' . h( $lg['eyebrow'] ) . '</span></div><ul>';
		foreach ( $lg['items'] as $l ) {
			$o[] = '<li>' . h( $l ) . '</li>';
		}
		$o[] = '</ul></div></section>';
	}

	// 6 · COMP-CTA contacto directo  [fijo] — no lead form; the doc refuses long ones
	$b   = $C['band'];
	$o[] = '<section class="sec band closing sober" aria-label="Contacto"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $b['eyebrow'] ) . '</span>'
		. '<h2>' . h( $b['h2'] ) . '</h2><p class="muted">' . h( $b['lede'] ) . '</p>'
		. '<div class="ctas"><a class="btn btn-primary" href="#">' . h( $b['cta_1'] ) . '</a>'
		. '<a class="btn btn-outline" href="#">' . h( $b['cta_2'] ) . '</a></div></div>'
		. '</div></section>';

	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );

	return number_heads( implode( "\n", $o ) );
}

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
function head_shop_plain( $C, $BRAND ) {
	$o = '<header class="site-head"><div class="canvas"><div class="nav">'
		. '<span class="logo">' . h( $BRAND ) . '</span>'
		. '<nav class="mainnav" aria-label="Principal">';
	foreach ( $C['nav'] as $n ) {
		$o .= '<a href="#">' . h( $n ) . '</a>';
	}
	return $o . '</nav><a class="cart" href="#">' . h( $C['cart'] ) . ' <b>' . h( $C['cart_n'] ) . '</b></a>'
		. '</div></div></header>';
}

/**
 * TPL-E-03 · Brand Story.
 *
 * The catalogue is deliberately thin: one carousel, no grid, no search. TPL-E-02 is the shop that
 * hides its brand; this is the brand that sells a shop, and the difference has to be visible before
 * the reader has scrolled.
 */
function strip_story( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$hero = $C['hero'];
	$im   = img( $hero['img'] );
	$o    = array();

	$o[] = head_shop_plain( $C, $BRAND );
	$o[] = '<main>';

	// 1 · COMP-HERO — imagen fija ~40vh  [fijo]
	$o[] = '<section class="sec hero" aria-label="La marca"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $hero['eyebrow'] ) . '</span>'
		. '<h1>' . h( $hero['h1'] ) . '</h1>'
		. '<p class="lede muted">' . h( $hero['lede'] ) . '</p>'
		. '<div class="ctas"><a class="btn btn-primary" href="#">' . h( $hero['cta_1'] ) . '</a>'
		. '<a class="btn btn-outline" href="#">' . h( $hero['cta_2'] ) . '</a></div></div>'
		. '<div class="media"><figure class="frame"><img data-img="' . h( $im['slug'] ) . '"'
		. ' alt="' . h( $im['alt'] ) . '" width="' . $im['w'] . '" height="' . $im['h'] . '"></figure></div>'
		. '</div></section>';

	/* 2 · COMP-VALUES  [fijo · ADN]
	   Numbered, because a value that cannot be counted is a slogan. Three is the count the doc
	   names, and three is also what fits on one line without becoming a wall of virtue. */
	$vl  = $C['values'];
	$o[] = '<section class="sec values grid-sec" aria-label="Valores"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $vl['eyebrow'] ) . '</span>'
		. '<h2>' . h( $vl['h2'] ) . '</h2></div><ol class="steps">';
	foreach ( $vl['items'] as $i => $it ) {
		$o[] = '<li class="step"><span class="n">' . sprintf( '%02d', $i + 1 ) . '</span>'
			. '<h3>' . h( $it[0] ) . '</h3><p>' . h( $it[1] ) . '</p></li>';
	}
	$o[] = '</ol></div></section>';

	// 3 · COMP-ABOUT  [fijo]
	$ab  = $C['about'];
	$ai  = img( $ab['img'] );
	$o[] = '<section class="sec about bg-alt" aria-label="El taller"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $ab['eyebrow'] ) . '</span>'
		. '<h2>' . h( $ab['h2'] ) . '</h2>';
	foreach ( $ab['body'] as $para ) {
		$o[] = '<p class="muted">' . h( $para ) . '</p>';
	}
	$o[] = '</div><div class="media"><figure class="frame"><img data-img="' . h( $ai['slug'] ) . '"'
		. ' alt="' . h( $ai['alt'] ) . '" width="' . $ai['w'] . '" height="' . $ai['h'] . '"></figure></div>'
		. '</div></section>';

	// 4 · COMP-PRODUCT-CAROUSEL — the ONLY catalogue on this page
	$cr  = $C['carousel'];
	$o[] = '<section class="sec carousel grid-sec" aria-label="Catálogo"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $cr['eyebrow'] ) . '</span>'
		. '<h2>' . h( $cr['h2'] ) . '</h2></div><div class="items grid-prod cols-4">';
	foreach ( $cr['cards'] as $c ) {
		$o[] = product_html( $anchor_key, $c );
	}
	$o[] = '</div></div></section>';

	// 5 · COMP-TESTIMONIAL  [toggle TGL-TESTIMONIALS]
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

	// 6 · COMP-CTA  [fijo]
	$b   = $C['band'];
	$o[] = '<section class="sec band closing sober" aria-label="Muestra"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $b['eyebrow'] ) . '</span>'
		. '<h2>' . h( $b['h2'] ) . '</h2><p class="muted">' . h( $b['lede'] ) . '</p>'
		. '<div class="ctas"><a class="btn btn-primary" href="#">' . h( $b['cta_1'] ) . '</a>'
		. '<a class="btn btn-outline" href="#">' . h( $b['cta_2'] ) . '</a></div></div>'
		. '</div></section>';

	// 7 · COMP-NEWSLETTER  [toggle TGL-NEWSLETTER]
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-NEWSLETTER' ) ) {
		$o[] = newsletter_html( $C['news'], $uid );
	}

	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );

	return number_heads( implode( "\n", $o ) );
}

/**
 * TPL-C-04 · Landing / Single Offer.
 *
 * Its doc's DNA is "una sola oferta, un solo CTA repetido, sin navegación que distraiga", so the
 * header prints NO nav at all — `head_corporate()` already emits an empty `<nav>` when the array
 * is empty, and the absence is the archetype rather than a missing feature. The same CTA label
 * appears four times on the page on purpose; on every other archetype that would be a defect.
 */
function strip_landing( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$hero = $C['hero'];
	$im   = img( $hero['img'] );
	$o    = array();

	$o[] = head_corporate( $C, $BRAND );
	$o[] = '<main>';

	// 1 · COMP-HERO — propuesta única + CTA fuerte  [fijo · ADN]
	$o[] = '<section class="sec hero" aria-label="Oferta"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $hero['eyebrow'] ) . '</span>'
		. '<h1>' . h( $hero['h1'] ) . '</h1>'
		. '<p class="lede muted">' . h( $hero['lede'] ) . '</p>'
		. '<div class="ctas"><a class="btn btn-primary" href="#">' . h( $hero['cta_1'] ) . '</a>'
		. '<a class="btn btn-outline" href="#">' . h( $hero['cta_2'] ) . '</a></div></div>'
		. '<div class="media"><figure class="frame"><img data-img="' . h( $im['slug'] ) . '"'
		. ' alt="' . h( $im['alt'] ) . '" width="' . $im['w'] . '" height="' . $im['h'] . '"></figure></div>'
		. '</div></section>';

	// 2 · COMP-PROBLEM  [fijo · ADN] — same rails as TPL-SERVICE-01's "qué resolvemos"
	$pb  = $C['problem'];
	$o[] = '<section class="sec problems grid-sec bg-alt" aria-label="El problema"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $pb['eyebrow'] ) . '</span>'
		. '<h2>' . h( $pb['h2'] ) . '</h2></div><ul class="items cols-3">';
	foreach ( $pb['items'] as $it ) {
		$o[] = '<li class="prob"><h3>' . h( $it[0] ) . '</h3><p>' . h( $it[1] ) . '</p></li>';
	}
	$o[] = '</ul></div></section>';

	/* 3 · COMP-SOLUTION  [fijo · ADN]
	   The mirror of COMP-PROBLEM and it has to READ as the mirror: same three-count, same order,
	   each item answering the problem above it. A solution block whose items do not line up with
	   the problems is two lists that happen to be adjacent. */
	$sl  = $C['solution'];
	$si  = img( $sl['img'] );
	$o[] = '<section class="sec solution" aria-label="La solución"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $sl['eyebrow'] ) . '</span>'
		. '<h2>' . h( $sl['h2'] ) . '</h2><p class="muted">' . h( $sl['lede'] ) . '</p>'
		. '<ul class="feats">';
	foreach ( $sl['items'] as $it ) {
		$o[] = '<li><b>' . h( $it[0] ) . '</b><span>' . h( $it[1] ) . '</span></li>';
	}
	$o[] = '</ul></div><div class="media"><figure class="frame"><img data-img="' . h( $si['slug'] ) . '"'
		. ' alt="' . h( $si['alt'] ) . '" width="' . $si['w'] . '" height="' . $si['h'] . '"></figure></div>'
		. '</div></section>';

	// 4 · COMP-FEATURES  [fijo]
	$ft  = $C['features'];
	$o[] = '<section class="sec features grid-sec bg-alt" aria-label="Alcance"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $ft['eyebrow'] ) . '</span>'
		. '<h2>' . h( $ft['h2'] ) . '</h2></div><ul class="feats">';
	foreach ( $ft['items'] as $it ) {
		$o[] = '<li><b>' . h( $it[0] ) . '</b><span>' . h( $it[1] ) . '</span></li>';
	}
	$o[] = '</ul></div></section>';

	// 5 · COMP-LOGOS  [toggle TGL-LOGOS]
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-LOGOS' ) ) {
		$lg  = $C['logos'];
		$o[] = '<section class="sec logos" aria-label="Clientes"><div class="canvas">'
			. '<div class="head stack"><span class="eyebrow">' . h( $lg['eyebrow'] ) . '</span></div><ul>';
		foreach ( $lg['items'] as $l ) {
			$o[] = '<li>' . h( $l ) . '</li>';
		}
		$o[] = '</ul></div></section>';
	}

	/* 6 · COMP-PRICING  [toggle TGL-PRICING]
	   The recommended plan is marked with WEIGHT AND A WORD, never with colour alone: a plan set
	   that reads only by hue is invisible to a monochrome reader and to most colour-blind ones,
	   which is the same rule the filter chips follow. */
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-PRICING' ) ) {
		$pr  = $C['pricing'];
		$o[] = '<section class="sec pricing grid-sec" aria-label="Precio"><div class="canvas">'
			. '<div class="head stack"><span class="eyebrow">' . h( $pr['eyebrow'] ) . '</span>'
			. '<h2>' . h( $pr['h2'] ) . '</h2></div><ul class="plans">';
		foreach ( $pr['plans'] as $pl ) {
			$best = ( 'sí' === $pl[3] );
			$o[]  = '<li class="plan' . ( $best ? ' plan-best' : '' ) . '">'
				. ( $best ? '<span class="plan-tag">El más pedido</span>' : '' )
				. '<b class="plan-name">' . h( $pl[0] ) . '</b>'
				. '<span class="plan-price">' . h( $pl[1] ) . '</span><ul class="plan-list">';
			foreach ( $pl[2] as $f ) {
				$o[] = '<li>' . h( $f ) . '</li>';
			}
			$o[] = '</ul><a class="btn ' . ( $best ? 'btn-primary' : 'btn-outline' ) . '" href="#">'
				. h( $C['nav_cta'] ) . '</a></li>';
		}
		$o[] = '</ul><p class="small muted plan-note">' . h( $pr['note'] ) . '</p></div></section>';
	}

	// 7 · COMP-FAQ  [fijo]
	$fq  = $C['faq'];
	$o[] = '<section class="sec faq bg-alt" aria-label="Preguntas frecuentes"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $fq['eyebrow'] ) . '</span>'
		. '<h2>' . h( $fq['h2'] ) . '</h2></div><div class="qas">';
	foreach ( $fq['items'] as $q ) {
		$o[] = '<details><summary>' . h( $q[0] ) . '</summary><p>' . h( $q[1] ) . '</p></details>';
	}
	$o[] = '</div></div></section>';

	// 8 · COMP-CTA cierre final  [fijo · ADN]
	$b   = $C['band'];
	$o[] = '<section class="sec band closing sober" aria-label="Reservar"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $b['eyebrow'] ) . '</span>'
		. '<h2>' . h( $b['h2'] ) . '</h2><p class="muted">' . h( $b['lede'] ) . '</p>'
		. '<div class="ctas"><a class="btn btn-primary" href="#">' . h( $b['cta_1'] ) . '</a>'
		. '<a class="btn btn-outline" href="#">' . h( $b['cta_2'] ) . '</a></div></div>'
		. '</div></section>';

	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );

	return number_heads( implode( "\n", $o ) );
}

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
function strip_promo( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$hero = $C['hero'];
	$im   = img( $hero['img'] );
	$o    = array();

	$o[] = head_ecommerce( $C, $BRAND, $uid );
	$o[] = '<main>';

	// 1 · COMP-HERO promo  [fijo · ADN]
	$o[] = '<section class="sec hero" aria-label="Campaña"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $hero['eyebrow'] ) . '</span>'
		. '<h1>' . h( $hero['h1'] ) . '</h1>'
		. '<p class="lede muted">' . h( $hero['lede'] ) . '</p>'
		. '<div class="ctas"><a class="btn btn-primary" href="#">' . h( $hero['cta_1'] ) . '</a>'
		. '<a class="btn btn-outline" href="#">' . h( $hero['cta_2'] ) . '</a></div></div>'
		. '<div class="media"><figure class="frame"><img data-img="' . h( $im['slug'] ) . '"'
		. ' alt="' . h( $im['alt'] ) . '" width="' . $im['w'] . '" height="' . $im['h'] . '"></figure></div>'
		. '</div></section>';

	// 2 · COMP-PRODUCT-GRID ofertas  [fijo · ADN]
	$p   = $C['prods'];
	$o[] = '<section class="sec prods grid-sec" aria-label="Ofertas"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $p['eyebrow'] ) . '</span>'
		. '<h2>' . h( $p['h2'] ) . '</h2></div><div class="items grid-prod">';
	foreach ( $p['cards'] as $c ) {
		$o[] = product_html( $anchor_key, $c );
	}
	$o[] = '</div></div></section>';

	/* 3 · COMP-PROMO-BANNER ×2  [toggle TGL-PROMO-BANNER]
	   A band, not a card: it interrupts the catalogue instead of joining it, which is the only way
	   an offer reads as an offer and not as one more product. The accent stays off it — a whole
	   surface shouting is the closing band's job once per page, and two more would make the accent
	   a texture. */
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-PROMO-BANNER' ) ) {
		foreach ( $C['promos'] as $i => $pm ) {
			$o[] = '<section class="sec promo' . ( 0 === $i % 2 ? ' bg-alt' : '' ) . '"'
				. ' aria-label="' . h( $pm[0] ) . '"><div class="canvas">'
				. '<div class="promo-in"><b>' . h( $pm[0] ) . '</b>'
				. '<span>' . h( $pm[1] ) . '</span>'
				. '<a class="btn btn-outline btn-sm" href="#">' . h( $pm[2] ) . '</a></div>'
				. '</div></section>';
		}
	}

	// 4 · COMP-PRODUCT-CAROUSEL  [fijo]
	$cr  = $C['carousel'];
	$o[] = '<section class="sec carousel grid-sec" aria-label="Más ofertas"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $cr['eyebrow'] ) . '</span>'
		. '<h2>' . h( $cr['h2'] ) . '</h2></div><div class="items grid-prod cols-4">';
	foreach ( $cr['cards'] as $c ) {
		$o[] = product_html( $anchor_key, $c );
	}
	$o[] = '</div></div></section>';

	// 5 · COMP-TRUST-BADGES  [fijo]
	$o[] = '<section class="sec bar" aria-label="Garantías"><div class="canvas"><div class="items bens">';
	foreach ( $C['badges'] as $b ) {
		$o[] = '<p class="ben"><span class="bicon" aria-hidden="true">✓</span>' . h( $b ) . '</p>';
	}
	$o[] = '</div></div></section>';

	// 6 · COMP-CTA  [fijo]
	$b   = $C['band'];
	$o[] = '<section class="sec band closing sober" aria-label="Cierre"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $b['eyebrow'] ) . '</span>'
		. '<h2>' . h( $b['h2'] ) . '</h2><p class="muted">' . h( $b['lede'] ) . '</p>'
		. '<div class="ctas"><a class="btn btn-primary" href="#">' . h( $b['cta_1'] ) . '</a>'
		. '<a class="btn btn-outline" href="#">' . h( $b['cta_2'] ) . '</a></div></div>'
		. '</div></section>';

	// 7 · COMP-NEWSLETTER cupón  [toggle TGL-NEWSLETTER]
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-NEWSLETTER' ) ) {
		$o[] = newsletter_html( $C['news'], $uid );
	}

	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );

	return number_heads( implode( "\n", $o ) );
}

/**
 * COMP-GALLERY · declared by TPL-C-05 and TPL-E-01, so it is emitted from one place.
 *
 * Six frames, no lightbox. A lightbox is script that must load before the reader can see the
 * second photograph, and on a mockup it would be a promise about a plugin nobody has chosen yet.
 */
function gallery_html( $g ) {
	$o = '<section class="sec gallery grid-sec" aria-label="' . h( $g['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $g['eyebrow'] ) . '</span>'
		. '<h2>' . h( $g['h2'] ) . '</h2></div><ul class="shots">';
	foreach ( $g['items'] as $slug ) {
		$gi = img( $slug );
		$o .= '<li><figure class="frame sq"><img data-img="' . h( $gi['slug'] ) . '"'
			. ' alt="' . h( $gi['alt'] ) . '" width="' . $gi['w'] . '" height="' . $gi['h'] . '"></figure></li>';
	}
	return $o . '</ul></div></section>';
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
		. '<h2>' . h( $tr['h2'] ) . '</h2></div><ul class="treatcards">';
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
		. '<h2>' . h( $tm['h2'] ) . '</h2></div><ul class="medlist">';
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
		. '<h2>' . h( $pl['h2'] ) . '</h2></div><ul class="planlist">';
	foreach ( $pl['items'] as $p ) {
		$o .= '<li class="planbox">'
			. ( isset( $p['flag'] ) ? '<span class="planflag">' . h( $p['flag'] ) . '</span>' : '' )
			. '<h3>' . h( $p['name'] ) . '</h3>'
			. '<p class="planq"><b>' . h( $p['quota'] ) . '</b><span>' . h( $p['total'] ) . '</span></p>'
			. '<p class="planp">' . h( $p['p'] ) . '</p><ul class="planfeats">';
		foreach ( $p['feats'] as $f ) {
			$o .= '<li>' . h( $f ) . '</li>';
		}
		$o .= '</ul><a class="btn btn-outline btn-sm" href="#">Pedir estudio</a></li>';
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
		. '<a class="btn btn-primary btn-lg" href="#">' . h( $ug['cta'] ) . '</a>'
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
function strip_clinic( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$hero = $C['hero'];
	$im   = img( $hero['img'] );
	$o    = array();
	$o[]  = head_phone( $C, $BRAND );
	$o[]  = '<main>';
	$o[]  = '<section class="sec hero" aria-label="La clínica"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $hero['eyebrow'] ) . '</span>'
		. '<h1>' . h( $hero['h1'] ) . '</h1>'
		. '<p class="lede muted">' . h( $hero['lede'] ) . '</p>'
		. '<div class="ctas"><a class="btn btn-primary" href="#">' . h( $hero['cta_1'] ) . '</a>'
		. '<a class="btn btn-outline" href="#">' . h( $hero['cta_2'] ) . '</a></div></div>'
		. '<div class="media"><figure class="frame"><img data-img="' . h( $im['slug'] ) . '"'
		. ' alt="' . h( $im['alt'] ) . '" width="' . $im['w'] . '" height="' . $im['h'] . '"></figure></div>'
		. '</div></section>';
	$o[]  = treatment_cards_html( $C['treatments'] );
	$o[]  = before_after_html( $C['cases'], ' bg-alt' );
	$o[]  = med_team_html( $C['team'] );
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-INSURANCE' ) ) {
		$o[] = insurance_html( $C['insurance'] );
	}
	$o[]  = booking_html( $C['booking'], $uid );
	$o[]  = '</main>';
	$o[]  = footer_html( $C['footer'] );
	return implode( "\n", $o );
}

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
		. '<div class="ctas"><a class="btn btn-primary" href="#">' . h( $hero['cta_1'] ) . '</a>'
		. '<a class="btn btn-outline" href="#">' . h( $hero['cta_2'] ) . '</a></div>'
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
function strip_urgent( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$o   = array();
	$o[] = head_phone( $C, $BRAND );
	$o[] = '<main>';
	$o[] = urgent_bar_html( $C['urgent'] );
	$o[] = triage_html( $C['triage'] );
	$o[] = wait_promise_html( $C['wait'] );
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-TEAM' ) ) {
		$o[] = med_team_html( $C['team'] );
	}
	$o[] = nap_block_html( $C['nap'], ' closing' );
	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );
	return implode( "\n", $o );
}

/** COMP-HEADER with a phone: the local variant, in flow. */
function head_phone( $C, $BRAND ) {
	$o = '<header class="site-head"><div class="canvas"><div class="nav">'
		. '<span class="logo">' . h( $BRAND ) . '</span>'
		. '<nav class="mainnav" aria-label="Principal">';
	foreach ( $C['nav'] as $n ) {
		$o .= '<a href="#">' . h( $n ) . '</a>';
	}
	return $o . '</nav><a class="tel" href="#">' . h( $C['phone'] ) . '</a>'
		. '<a class="btn btn-primary btn-sm" href="#">' . h( $C['nav_cta'] ) . '</a>'
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
		. '<h2>' . h( $st['h2'] ) . '</h2></div><ul class="stockgrid">';
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
			. '<a class="btn btn-outline btn-sm" href="#">Ver ficha</a></div></li>';
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

/** COMP-TESTIMONIAL, the shared shape. */
function quotes_block_html( $qt, $extra = '' ) {
	$o = '<section class="sec quotes grid-sec' . $extra . '" aria-label="' . h( $qt['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $qt['eyebrow'] ) . '</span>'
		. '<h2>' . h( $qt['h2'] ) . '</h2></div><ul class="items">';
	foreach ( $qt['items'] as $q ) {
		$o .= '<li><figure><blockquote>' . h( $q[0] ) . '</blockquote>'
			. '<figcaption><b>' . h( $q[1] ) . '</b><span>' . h( $q[2] ) . '</span></figcaption>'
			. '</figure></li>';
	}
	return $o . '</ul></div></section>';
}

/** COMP-MAP-NAP. No embedded map: an iframe is a third-party request before any consent. */
function nap_block_html( $np, $extra = '' ) {
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
	return $o . '</dl><p class="small muted">' . h( $np['note'] ) . '</p></div>'
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
		. '<div class="ctas"><a class="btn btn-primary" href="#">' . h( $hero['cta_1'] ) . '</a>'
		. '<a class="btn btn-outline" href="#">' . h( $hero['cta_2'] ) . '</a></div></div>'
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
		. '<div class="ctas"><a class="btn btn-primary" href="#">' . h( $of['cta'] ) . '</a></div>'
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

/** COMP-FAQ, reusing the shape and the classes the other archetypes already style. */
function faq_block_html( $fq, $extra = '' ) {
	$o = '<section class="sec faq' . $extra . '" aria-label="' . h( $fq['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $fq['eyebrow'] ) . '</span>'
		. '<h2>' . h( $fq['h2'] ) . '</h2></div><div class="faqlist">';
	foreach ( $fq['items'] as $it ) {
		$o .= '<div class="qa"><h3>' . h( $it[0] ) . '</h3><p>' . h( $it[1] ) . '</p></div>';
	}
	return $o . '</div></div></section>';
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
	$o[] = nap_block_html( $C['nap'], ' closing' );
	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );
	return implode( "\n", $o );
}

/** TPL-C-08 · Modelo / Lanzamiento. */
function strip_model( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$o   = array();
	$o[] = head_over( $C, $BRAND );
	$o[] = '<main>';
	$o[] = model_hero_html( $C['hero'] );
	$o[] = spec_table_html( $C['specs'] );
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-GALLERY' ) ) {
		$o[] = gallery_html( $C['gallery'] );
	}
	$o[] = offer_strip_html( $C['offer'] );
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-FINANCE' ) ) {
		$o[] = finance_html( $C['finance'] );
	}
	$o[] = booking_html( $C['booking'], $uid );
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-FAQ' ) ) {
		$o[] = faq_block_html( $C['faq'], ' closing' );
	}
	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );
	return implode( "\n", $o );
}

/** TPL-C-09 · Taller / Tarifa. */
function strip_workshop( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$hero = $C['hero'];
	$im   = img( $hero['img'] );
	$o    = array();
	$o[]  = head_phone( $C, $BRAND );
	$o[]  = '<main>';
	$o[]  = '<section class="sec hero" aria-label="El taller"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $hero['eyebrow'] ) . '</span>'
		. '<h1>' . h( $hero['h1'] ) . '</h1>'
		. '<p class="lede muted">' . h( $hero['lede'] ) . '</p>'
		. '<div class="ctas"><a class="btn btn-primary" href="#">' . h( $hero['cta_1'] ) . '</a>'
		. '<a class="btn btn-outline" href="#">' . h( $hero['cta_2'] ) . '</a></div></div>'
		. '<div class="media"><figure class="frame"><img data-img="' . h( $im['slug'] ) . '"'
		. ' alt="' . h( $im['alt'] ) . '" width="' . $im['w'] . '" height="' . $im['h'] . '"></figure></div>'
		. '</div></section>';
	$o[]  = price_list_html( $C['prices'] );
	$o[]  = process_block_html( $C['process'], ' bg-alt' );
	$o[]  = booking_html( $C['booking'], $uid );
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-BADGES' ) ) {
		$o[] = badges_html( $C['badges'] );
	}
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-FAQ' ) ) {
		$o[] = faq_block_html( $C['faq'], ' bg-alt' );
	}
	$o[]  = nap_block_html( $C['nap'], ' closing' );
	$o[]  = '</main>';
	$o[]  = footer_html( $C['footer'] );
	return implode( "\n", $o );
}

/** COMP-HEADER floating on the hero: same contents as the local header, out of flow. */
function head_over( $C, $BRAND ) {
	$o = '<header class="site-head head-over"><div class="canvas"><div class="nav">'
		. '<span class="logo">' . h( $BRAND ) . '</span>'
		. '<nav class="mainnav" aria-label="Principal">';
	foreach ( $C['nav'] as $n ) {
		$o .= '<a href="#">' . h( $n ) . '</a>';
	}
	return $o . '</nav><a class="tel" href="#">' . h( $C['phone'] ) . '</a>'
		. '<a class="btn btn-primary btn-sm" href="#">' . h( $C['nav_cta'] ) . '</a>'
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
function gallery_masonry_html( $g ) {
	$o = '<section class="sec gallery grid-sec" aria-label="' . h( $g['h2'] ) . '"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $g['eyebrow'] ) . '</span>'
		. '<h2>' . h( $g['h2'] ) . '</h2></div><ul class="shots masonry">';
	foreach ( $g['items'] as $slug ) {
		$gi = img( $slug );
		$o .= '<li><figure class="frame"><img data-img="' . h( $gi['slug'] ) . '"'
			. ' alt="' . h( $gi['alt'] ) . '" width="' . $gi['w'] . '" height="' . $gi['h'] . '"></figure></li>';
	}
	return $o . '</ul></div></section>';
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
function strip_menu( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$hero = $C['hero'];
	$im   = img( $hero['img'] );
	$o    = array();

	// COMP-HEADER, transparent over the hero  [fijo]
	$o[] = head_over( $C, $BRAND );
	$o[] = '<main>';

	// 1 · COMP-HERO-FULL  [fijo · ADN]
	$o[] = '<section class="sec hero hero-visual hero-full" aria-label="La casa"><div class="media-full">'
		. '<figure class="frame"><img data-img="' . h( $im['slug'] ) . '"'
		. ' alt="' . h( $im['alt'] ) . '" width="' . $im['w'] . '" height="' . $im['h'] . '"></figure></div>'
		. '<div class="canvas"><div class="head stack">'
		. '<span class="eyebrow">' . h( $hero['eyebrow'] ) . '</span>'
		. '<h1>' . h( $hero['h1'] ) . '</h1>'
		. '<p class="lede">' . h( $hero['lede'] ) . '</p>'
		. '<div class="ctas"><a class="btn btn-primary" href="#">' . h( $hero['cta_1'] ) . '</a>'
		. '<a class="btn btn-outline" href="#">' . h( $hero['cta_2'] ) . '</a></div>'
		. '</div></div></section>';

	// 2 · COMP-MARQUEE  [toggle TGL-MARQUEE]
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-MARQUEE' ) ) {
		$o[] = marquee_html( $C['marquee'] );
	}

	// 3 · COMP-MENU-LIST  [fijo · ADN · toggle TGL-MENU-PRICES]
	$o[] = menu_list_html( $C['menu'], tgl_of( $tgl_rows, 'TGL-MENU-PRICES' ) );

	// 4 · COMP-FIGURE-QUOTE  [fijo · ADN]
	$o[] = figure_quote_html( $C['figq'] );

	// 5 · COMP-GALLERY, offset  [toggle TGL-GALLERY]
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-GALLERY' ) ) {
		$o[] = gallery_masonry_html( $C['gallery'] );
	}

	// 6 · COMP-BOOKING  [fijo]
	$o[] = booking_html( $C['booking'], $uid );

	// 7 · COMP-HOURS-BLOCK  [fijo · ADN] — and it is the close
	$o[] = hours_block_html( $C['hours'] );

	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );

	return implode( "\n", $o );
}

/**
 * TPL-C-05 · Local / Booking.
 *
 * The archetype of a place you go to, and two things are DNA rather than decoration: the PHONE
 * sits in the header as text, and the close carries the address and the opening hours. A local
 * business whose number lives only inside a form is one you cannot ring from the car, and hours
 * that are not on the page are a wasted trip.
 */
function strip_local( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$hero = $C['hero'];
	$im   = img( $hero['img'] );
	$o    = array();

	/* COMP-HEADER con teléfono  [fijo · ADN] — its own header, because the phone number beside the
	   CTA is the one thing this archetype's doc singles out about it. */
	$o[] = '<header class="site-head"><div class="canvas"><div class="nav">'
		. '<span class="logo">' . h( $BRAND ) . '</span>'
		. '<nav class="mainnav" aria-label="Principal">';
	foreach ( $C['nav'] as $n ) {
		$o[] = '<a href="#">' . h( $n ) . '</a>';
	}
	$o[] = '</nav><a class="tel" href="#">' . h( $C['phone'] ) . '</a>'
		. '<a class="btn btn-primary btn-sm" href="#">' . h( $C['nav_cta'] ) . '</a>'
		. '</div></div></header>';

	$o[] = '<main>';

	// 1 · COMP-HERO local  [fijo · ADN]
	$o[] = '<section class="sec hero" aria-label="El showroom"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $hero['eyebrow'] ) . '</span>'
		. '<h1>' . h( $hero['h1'] ) . '</h1>'
		. '<p class="lede muted">' . h( $hero['lede'] ) . '</p>'
		. '<div class="ctas"><a class="btn btn-primary" href="#">' . h( $hero['cta_1'] ) . '</a>'
		. '<a class="btn btn-outline" href="#">' . h( $hero['cta_2'] ) . '</a></div></div>'
		. '<div class="media"><figure class="frame"><img data-img="' . h( $im['slug'] ) . '"'
		. ' alt="' . h( $im['alt'] ) . '" width="' . $im['w'] . '" height="' . $im['h'] . '"></figure></div>'
		. '</div></section>';

	// 2 · COMP-SERVICES  [fijo]
	$sv  = $C['services'];
	$o[] = '<section class="sec services grid-sec bg-alt" aria-label="Prestaciones"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $sv['eyebrow'] ) . '</span>'
		. '<h2>' . h( $sv['h2'] ) . '</h2></div><div class="items cols-3">';
	foreach ( $sv['cards'] as $c ) {
		$o[] = card_html( $anchor_key, $c );
	}
	$o[] = '</div></div></section>';

	/* 3 · COMP-BOOKING  [fijo · ADN]
	   Day and slot are RADIO GROUPS, not a `<select>`. A select hides every option but one behind
	   a tap, and on a booking block the available times ARE the argument: five visible slots say
	   "there is room this week" and a closed dropdown says nothing at all. */
	$o[] = booking_html( $C['booking'], $uid );

	// 4 · COMP-GALLERY  [toggle TGL-GALLERY]
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-GALLERY' ) ) {
		$o[] = gallery_html( $C['gallery'] );
	}

	// 5 · COMP-TESTIMONIAL  [toggle TGL-TESTIMONIALS]
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-TESTIMONIALS' ) ) {
		$qt  = $C['quotes'];
		$o[] = '<section class="sec quotes grid-sec bg-alt" aria-label="Reseñas"><div class="canvas">'
			. '<div class="head stack"><span class="eyebrow">' . h( $qt['eyebrow'] ) . '</span>'
			. '<h2>' . h( $qt['h2'] ) . '</h2></div><ul class="items">';
		foreach ( $qt['items'] as $q ) {
			$o[] = '<li><figure><blockquote>' . h( $q[0] ) . '</blockquote>'
				. '<figcaption><b>' . h( $q[1] ) . '</b><span>' . h( $q[2] ) . '</span></figcaption>'
				. '</figure></li>';
		}
		$o[] = '</ul></div></section>';
	}

	/* 6 · COMP-MAP-NAP  [fijo · ADN]
	   NO EMBEDDED MAP. An iframe to a map provider is a remote request the Artifact CSP blocks
	   outright, and on a real build it is a third-party cookie before the visitor has consented to
	   anything. What a NAP block owes the reader is the ADDRESS, the PHONE and the HOURS as text —
	   copyable, searchable, and readable by a screen reader. The photograph stands in for the map. */
	$np  = $C['nap'];
	$ni  = img( $np['img'] );
	$o[] = '<section class="sec nap closing" aria-label="Cómo llegar"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $np['eyebrow'] ) . '</span>'
		. '<h2>' . h( $np['h2'] ) . '</h2>'
		. '<address class="nap-addr">';
	foreach ( $np['addr'] as $line ) {
		$o[] = '<span>' . h( $line ) . '</span>';
	}
	$o[] = '<a href="#">' . h( $np['phone'] ) . '</a><a href="#">' . h( $np['mail'] ) . '</a>'
		. '</address><dl class="hours">';
	foreach ( $np['hours'] as $hr ) {
		$o[] = '<div><dt>' . h( $hr[0] ) . '</dt><dd>' . h( $hr[1] ) . '</dd></div>';
	}
	$o[] = '</dl><p class="small muted">' . h( $np['note'] ) . '</p></div>'
		. '<div class="media"><figure class="frame"><img data-img="' . h( $ni['slug'] ) . '"'
		. ' alt="' . h( $ni['alt'] ) . '" width="' . $ni['w'] . '" height="' . $ni['h'] . '"></figure></div>'
		. '</div></section>';

	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );

	return number_heads( implode( "\n", $o ) );
}

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
	foreach ( $bk['fields'] as $f ) {
		$id = $uid . '-bk-' . $f[0];
		$o .= '<div class="field"><label for="' . $id . '">' . h( $f[1] ) . '</label>'
			. '<input id="' . $id . '" name="' . $f[0] . '" type="' . $f[2] . '"></div>';
	}
	foreach ( array(
		array( 'day', $bk['day_lbl'], $bk['days'] ),
		array( 'slot', $bk['slot_lbl'], $bk['slots'] ),
	) as $grp ) {
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
		$o .= '<li class="catcard"><a href="#">'
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
function strip_visual( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	global $SLIDER_FRAMES;
	$hero   = $C['hero'];
	$im     = img( $hero['img'] );
	$slider = ( 'slider' === tgl_of( $tgl_rows, 'TGL-HERO-TYPE' ) );
	$o      = array();

	$o[] = head_ecommerce( $C, $BRAND, $uid );
	$o[] = '<main>';

	// 1 · COMP-HERO slider ~60vh  [fijo · ADN] — same toggle, same copy, one changed setting
	$o[] = '<section class="sec hero' . ( $slider ? ' hero-slides' : '' ) . '" aria-label="Colección">'
		. ( $slider ? hero_slider_html( $SLIDER_FRAMES ) : '' )
		. '<div class="canvas"><div class="head stack">'
		. '<span class="eyebrow">' . h( $hero['eyebrow'] ) . '</span>'
		. '<h1>' . h( $hero['h1'] ) . '</h1>'
		. '<p class="lede muted">' . h( $hero['lede'] ) . '</p>'
		. '<div class="ctas"><a class="btn btn-primary" href="#">' . h( $hero['cta_1'] ) . '</a>'
		. '<a class="btn btn-outline" href="#">' . h( $hero['cta_2'] ) . '</a></div></div>'
		. ( $slider ? '' : '<div class="media"><figure class="frame"><img data-img="' . h( $im['slug'] ) . '"'
			. ' alt="' . h( $im['alt'] ) . '" width="' . $im['w'] . '" height="' . $im['h'] . '"></figure></div>' )
		. '</div></section>';

	// 2 · COMP-CATEGORY-CARD  [fijo · ADN]
	$o[] = category_cards_html( $anchor_key, $C['cats'] );

	// 3 · COMP-PRODUCT-CAROUSEL  [fijo]
	$cr  = $C['carousel'];
	$o[] = '<section class="sec carousel grid-sec bg-alt" aria-label="Destacados"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $cr['eyebrow'] ) . '</span>'
		. '<h2>' . h( $cr['h2'] ) . '</h2></div><div class="items grid-prod cols-4">';
	foreach ( $cr['cards'] as $c ) {
		$o[] = product_html( $anchor_key, $c );
	}
	$o[] = '</div></div></section>';

	// 4 · COMP-GALLERY lookbook  [toggle TGL-GALLERY]
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-GALLERY' ) ) {
		$o[] = gallery_html( $C['gallery'] );
	}

	// 5 · COMP-TESTIMONIAL  [fijo]
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

	// 6 · COMP-BOOKING  [fijo · ADN]
	$o[] = booking_html( $C['booking'], $uid );

	// 7 · COMP-NEWSLETTER  [toggle TGL-NEWSLETTER]
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-NEWSLETTER' ) ) {
		$o[] = newsletter_html( $C['news'], $uid );
	}

	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );

	return number_heads( implode( "\n", $o ) );
}

/**
 * TPL-E-04 · Categories First.
 *
 * The catalogue is WIDE, so the page routes before it sells. TPL-E-02 answers "I know what I want"
 * with eight products above the fold; this one answers "I know roughly where to look" with eight
 * doors and a count on each.
 */
function strip_categories( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	$hero = $C['hero'];
	$im   = img( $hero['img'] );
	$o    = array();

	$o[] = head_ecommerce( $C, $BRAND, $uid );
	$o[] = '<main>';

	// 1 · COMP-HERO banner medio  [fijo]
	$o[] = '<section class="sec hero" aria-label="Catálogo"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $hero['eyebrow'] ) . '</span>'
		. '<h1>' . h( $hero['h1'] ) . '</h1>'
		. '<p class="lede muted">' . h( $hero['lede'] ) . '</p>'
		. '<div class="ctas"><a class="btn btn-primary" href="#">' . h( $hero['cta_1'] ) . '</a>'
		. '<a class="btn btn-outline" href="#">' . h( $hero['cta_2'] ) . '</a></div></div>'
		. '<div class="media"><figure class="frame"><img data-img="' . h( $im['slug'] ) . '"'
		. ' alt="' . h( $im['alt'] ) . '" width="' . $im['w'] . '" height="' . $im['h'] . '"></figure></div>'
		. '</div></section>';

	/* 2 · COMP-CATEGORY-GRID  [fijo · ADN]
	   TEXT TILES, NOT PHOTOGRAPHS. Eight photographic doors would be eight more images to decode
	   before the reader can route, and routing is what this archetype is FOR — the picture is not
	   the argument here, the count is. COMP-CATEGORY-CARD further down does carry photographs,
	   because three doors can afford them. */
	$gr  = $C['grid'];
	$o[] = '<section class="sec catgrid grid-sec bg-alt" aria-label="Categorías"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $gr['eyebrow'] ) . '</span>'
		. '<h2>' . h( $gr['h2'] ) . '</h2></div><ul class="tiles">';
	foreach ( $gr['items'] as $it ) {
		$o[] = '<li><a href="#"><b>' . h( $it[0] ) . '</b><span>' . h( $it[1] ) . '</span></a></li>';
	}
	$o[] = '</ul></div></section>';

	/* 3 · COMP-PRODUCT-TABS  [fijo · ADN]
	   TABS WITHOUT A LINE OF JAVASCRIPT — radio inputs and a sibling selector. A tab set that needs
	   script shows one panel and three dead labels until it loads, and shows exactly that forever
	   to a crawler. The labels are `<label>` elements, so they are keyboard-reachable and announced
	   as the radio group they actually are. */
	$tb  = $C['tabs'];
	$o[] = '<section class="sec ptabs grid-sec" aria-label="Novedades"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $tb['eyebrow'] ) . '</span>'
		. '<h2>' . h( $tb['h2'] ) . '</h2></div><div class="tabset">';
	foreach ( $tb['panels'] as $i => $pn ) {
		$tid = $uid . '-tab' . $i;
		$o[] = '<input class="tabin" type="radio" id="' . $tid . '" name="' . $uid . '-tabs"'
			. ( 0 === $i ? ' checked' : '' ) . '>'
			. '<label class="tablab" for="' . $tid . '">' . h( $pn[0] ) . '</label>';
	}
	$o[] = '<div class="tabpanels">';
	foreach ( $tb['panels'] as $pn ) {
		$o[] = '<div class="tabpanel"><div class="items grid-prod cols-4">';
		foreach ( $pn[1] as $c ) {
			$o[] = product_html( $anchor_key, $c );
		}
		$o[] = '</div></div>';
	}
	$o[] = '</div></div></div></section>';

	// 4 · COMP-BENEFITS  [toggle TGL-BENEFITS]
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-BENEFITS' ) ) {
		$o[] = '<section class="sec bar" aria-label="Garantías"><div class="canvas"><div class="items bens">';
		foreach ( $C['benefits'] as $b ) {
			$o[] = '<p class="ben"><span class="bicon" aria-hidden="true">✓</span>' . h( $b ) . '</p>';
		}
		$o[] = '</div></div></section>';
	}

	// 5 · COMP-CTA  [fijo]
	$b   = $C['band'];
	$o[] = '<section class="sec band closing sober" aria-label="Profesionales"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $b['eyebrow'] ) . '</span>'
		. '<h2>' . h( $b['h2'] ) . '</h2><p class="muted">' . h( $b['lede'] ) . '</p>'
		. '<div class="ctas"><a class="btn btn-primary" href="#">' . h( $b['cta_1'] ) . '</a>'
		. '<a class="btn btn-outline" href="#">' . h( $b['cta_2'] ) . '</a></div></div>'
		. '</div></section>';

	// 6 · COMP-CATEGORY-CARD  [fijo]
	$o[] = category_cards_html( $anchor_key, $C['cats'] );

	// 7 · COMP-NEWSLETTER  [toggle TGL-NEWSLETTER]
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-NEWSLETTER' ) ) {
		$o[] = newsletter_html( $C['news'], $uid );
	}

	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );

	return number_heads( implode( "\n", $o ) );
}

function strip_corporate( $anchor_key, $C, $BRAND, $uid, $tgl_rows ) {
	global $SLIDER_FRAMES;
	$hero    = $C['hero'];
	$im      = img( $hero['img'] );
	$slider  = ( 'slider' === tgl_of( $tgl_rows, 'TGL-HERO-TYPE' ) );
	$o       = array();

	$o[] = head_corporate( $C, $BRAND );

	$o[] = '<main>';

	// 1 · COMP-HERO  [fijo · ADN] · TGL-HERO-TYPE
	//
	// The SECTION differs between the two hero types and the COPY does not — same eyebrow, same
	// h1, same lede, same two CTAs, in the same order. That is what keeps the toggle readable as a
	// toggle: a reader comparing this strip with its three siblings is looking at one changed
	// setting, not at a second design.
	$o[] = '<section class="sec hero' . ( $slider ? ' hero-slides' : '' ) . '" aria-label="Propuesta de valor">'
		. ( $slider ? hero_slider_html( $SLIDER_FRAMES ) : '' )
		. '<div class="canvas">'
		. '<div class="head stack">'
		. '<span class="eyebrow">' . h( $hero['eyebrow'] ) . '</span>'
		. '<h1>' . h( $hero['h1'] ) . '</h1>'
		. '<p class="lede muted">' . h( $hero['lede'] ) . '</p>'
		. '<div class="ctas"><a class="btn btn-primary" href="#">' . h( $hero['cta_1'] ) . '</a>'
		. '<a class="btn btn-outline" href="#">' . h( $hero['cta_2'] ) . '</a></div>'
		. '</div>'
		. ( $slider ? '' : '<div class="media"><figure class="frame"><img data-img="' . h( $im['slug'] ) . '"'
			. ' alt="' . h( $im['alt'] ) . '" width="' . $im['w'] . '" height="' . $im['h'] . '"></figure></div>' )
		. '</div></section>';

	// 2 · COMP-LOGOS  [toggle TGL-LOGOS]
	if ( 'no' !== tgl_of( $tgl_rows, 'TGL-LOGOS' ) ) {
		$lg  = $C['logos'];
		$o[] = '<section class="sec logos bg-alt" aria-label="Clientes"><div class="canvas">'
			. '<div class="head stack"><span class="eyebrow">' . h( $lg['eyebrow'] ) . '</span></div><ul>';
		foreach ( $lg['items'] as $l ) {
			$o[] = '<li>' . h( $l ) . '</li>';
		}
		$o[] = '</ul></div></section>';
	}

	// 3 · COMP-SERVICES  [fijo · ADN]
	$sv  = $C['services'];
	$o[] = '<section class="sec services grid-sec" aria-label="Servicios"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $sv['eyebrow'] ) . '</span>'
		. '<h2>' . h( $sv['h2'] ) . '</h2></div><div class="items cols-3">';
	foreach ( $sv['cards'] as $c ) {
		$o[] = card_html( $anchor_key, $c );
	}
	$o[] = '</div></div></section>';

	// 4 · COMP-PROCESS  [toggle TGL-PROCESS]
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

	// 5 · COMP-CASES  [toggle TGL-CASES, default on]
	$cs  = $C['cases'];
	$o[] = '<section class="sec cases grid-sec" aria-label="Casos"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $cs['eyebrow'] ) . '</span>'
		. '<h2>' . h( $cs['h2'] ) . '</h2></div><div class="items cols-2">';
	foreach ( $cs['cards'] as $c ) {
		$o[] = card_html( $anchor_key, $c );
	}
	$o[] = '</div></div></section>';

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

	// 7 · COMP-CTA + COMP-LEAD-FORM  [fijo · ADN] — the form exists and dominates the close
	$o[] = band_closing_html( $C['band'], $uid );

	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );

	return number_heads( implode( "\n", $o ) );
}

/** COMP-FOOTER — `[fijo]` in both archetypes, so it is emitted from one place. */
function footer_html( $f ) {
	$o = '<footer class="site-foot"><div class="canvas">'
		. '<div class="fnav"><span class="muted small">' . h( $f['tag'] ) . '</span>';
	foreach ( $f['links'] as $l ) {
		$o .= '<a href="#">' . h( $l ) . '</a>';
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
function strip_ecommerce( $anchor_key, $C, $BRAND, $uid ) {
	$hero = $C['hero'];
	$im   = img( $hero['img'] );
	$o    = array();

	// 0 · COMP-ANNOUNCEMENT + 1 · COMP-HEADER
	$o[] = head_ecommerce( $C, $BRAND, $uid );

	$o[] = '<main>';

	// 2 · COMP-HERO mini ~20vh  [fijo · ADN] — a thin banner, never a big hero
	$o[] = '<section class="sec mini" aria-label="Tienda"><div class="canvas">'
		. '<div class="head stack"><h1>' . h( $hero['h1'] ) . '</h1>'
		. '<p class="lede muted">' . h( $hero['lede'] ) . '</p></div>'
		. '<div class="media"><figure class="frame"><img data-img="' . h( $im['slug'] ) . '"'
		. ' alt="' . h( $im['alt'] ) . '" width="' . $im['w'] . '" height="' . $im['h'] . '"></figure></div>'
		. '<nav class="cats" aria-label="Categorías">';
	foreach ( $hero['cats'] as $c ) {
		$o[] = '<a href="#">' . h( $c ) . '</a>';
	}
	$o[] = '</nav></div></section>';

	// 3 · COMP-PRODUCT-GRID  [fijo · ADN] — dense, and immediately after the header
	$p   = $C['prods'];
	$o[] = '<section class="sec prods grid-sec" aria-label="Destacados"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $p['eyebrow'] ) . '</span>'
		. '<h2>' . h( $p['h2'] ) . '</h2></div><div class="items grid-prod">';
	foreach ( $p['cards'] as $c ) {
		$o[] = product_html( $anchor_key, $c );
	}
	$o[] = '</div></div></section>';

	// 4 · COMP-PRODUCT-CAROUSEL  [fijo]
	$cr  = $C['carousel'];
	$o[] = '<section class="sec carousel grid-sec bg-alt" aria-label="Más vendidos"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $cr['eyebrow'] ) . '</span>'
		. '<h2>' . h( $cr['h2'] ) . '</h2></div><div class="items rail">';
	foreach ( $cr['cards'] as $c ) {
		$o[] = product_html( $anchor_key, $c );
	}
	$o[] = '</div></div></section>';

	// 5 · COMP-BENEFITS — barra fina  [toggle TGL-BENEFITS, default on]
	$o[] = '<section class="sec bar" aria-label="Garantías"><div class="canvas"><div class="items bens">';
	foreach ( $C['benefits'] as $b ) {
		$o[] = '<div class="ben"><span class="bicon" aria-hidden="true">'
			. '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor"'
			. ' stroke-width="2" stroke-linecap="round" stroke-linejoin="round">'
			. '<path d="M20 6 9 17l-5-5"/></svg></span>'
			. '<b>' . h( $b[0] ) . '</b><span class="muted small">' . h( $b[1] ) . '</span></div>';
	}
	$o[] = '</div></div></section>';

	// 6 · COMP-FAQ — dudas operativas  [fijo · ADN]
	//
	// `<details>/<summary>`, which is `mockup-guide.md`'s recipe for COMP-FAQ and the same choice
	// the handoff block below makes: the platform's own disclosure widget, no script, and it stays
	// open-able with JS off. A panel built from divs would need state this generator cannot ship.
	$fq  = $C['faq'];
	$o[] = '<section class="sec faq" aria-label="Preguntas frecuentes"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $fq['eyebrow'] ) . '</span>'
		. '<h2>' . h( $fq['h2'] ) . '</h2></div><div class="faqlist">';
	foreach ( $fq['rows'] as $r ) {
		$o[] = '<details><summary>' . h( $r[0] ) . '</summary><p>' . h( $r[1] ) . '</p></details>';
	}
	$o[] = '</div></div></section>';

	// 7 · COMP-CONTACT-DIRECT — "¿no lo encontraste?"  [fijo · ADN] — the closing band
	//
	// The ecommerce close, and deliberately NOT the corporate one: TPL-C-01 above ends in a
	// COMP-LEAD-FORM that takes a name and answers in 48 h. A catalogue whose whole DNA is FINDING
	// closes on what happens when finding failed, at counter speed — three channels, each with the
	// one fact that makes it usable (hours, what to send, how fast the reply comes).
	$cl  = $C['close'];
	$o[] = '<section class="sec close closing" aria-label="Contacto directo"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $cl['eyebrow'] ) . '</span>'
		. '<h2>' . h( $cl['h2'] ) . '</h2>'
		. '<p class="lede muted">' . h( $cl['lede'] ) . '</p>'
		/* INSIDE `.head`, NOT BESIDE IT. `.canvas > *` is placed column by column under three of
		   the four blueprints, so a third child here would land unplaced — the same trap the note
		   on `.faqlist` records for `.band`. The control belongs to the ask, so it belongs in the
		   block that makes it. */
		. '<div class="closecta"><a class="btn btn-primary btn-close" href="#">' . h( $cl['cta'] ) . '</a>'
		. '<span class="muted small">' . h( $cl['cta_sub'] ) . '</span></div>'
		. '</div><div class="items chans">';
	foreach ( $cl['chans'] as $c ) {
		$o[] = '<div class="chan"><span class="muted small">' . h( $c[0] ) . '</span>'
			. '<b>' . h( $c[1] ) . '</b>'
			. '<span class="muted small">' . h( $c[2] ) . '</span></div>';
	}
	$o[] = '</div></div></section>';

	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );

	return number_heads( implode( "\n", $o ) );
}

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
	'TPL-C-10' => array(
		array( 'key' => 'home', 'label' => 'Clínica', 'doc' => 'TPL-C-10' ),
	),
	'TPL-C-11' => array(
		array( 'key' => 'home', 'label' => 'Plan', 'doc' => 'TPL-C-11' ),
	),
	'TPL-C-12' => array(
		array( 'key' => 'home', 'label' => 'Urgencias', 'doc' => 'TPL-C-12' ),
	),
	'TPL-C-07' => array(
		array( 'key' => 'home', 'label' => 'Stock', 'doc' => 'TPL-C-07' ),
	),
	'TPL-C-08' => array(
		array( 'key' => 'home', 'label' => 'Modelo', 'doc' => 'TPL-C-08' ),
	),
	'TPL-C-09' => array(
		array( 'key' => 'home', 'label' => 'Taller', 'doc' => 'TPL-C-09' ),
	),
	'TPL-C-06' => array(
		array( 'key' => 'home', 'label' => 'Home', 'doc' => 'TPL-C-06' ),
	),
	'TPL-C-01' => array(
		array( 'key' => 'home',     'label' => 'Home',     'doc' => 'TPL-C-01' ),
		array( 'key' => 'servicio', 'label' => 'Servicio', 'doc' => 'TPL-SERVICE-01' ),
	),
	'TPL-C-02' => array(
		array( 'key' => 'home', 'label' => 'Home', 'doc' => 'TPL-C-02' ),
	),
	'TPL-C-03' => array(
		array( 'key' => 'home', 'label' => 'Home', 'doc' => 'TPL-C-03' ),
	),
	'TPL-E-03' => array(
		array( 'key' => 'home', 'label' => 'Home', 'doc' => 'TPL-E-03' ),
	),
	'TPL-C-04' => array(
		array( 'key' => 'home', 'label' => 'Landing', 'doc' => 'TPL-C-04' ),
	),
	'TPL-E-05' => array(
		array( 'key' => 'home', 'label' => 'Campaña', 'doc' => 'TPL-E-05' ),
	),
	'TPL-C-05' => array(
		array( 'key' => 'home', 'label' => 'Home', 'doc' => 'TPL-C-05' ),
	),
	'TPL-E-01' => array(
		array( 'key' => 'home', 'label' => 'Home', 'doc' => 'TPL-E-01' ),
	),
	'TPL-E-04' => array(
		array( 'key' => 'home', 'label' => 'Home', 'doc' => 'TPL-E-04' ),
	),
	'TPL-E-02' => array(
		array( 'key' => 'home',     'label' => 'Tienda',   'doc' => 'TPL-E-02' ),
		array( 'key' => 'producto', 'label' => 'Producto', 'doc' => 'TPL-PDP-01' ),
	),
);

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
	if ( 'TPL-C-10' === $tpl && 'home' === $page_key ) {
		return strip_clinic( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-C-11' === $tpl && 'home' === $page_key ) {
		return strip_plan( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-C-12' === $tpl && 'home' === $page_key ) {
		return strip_urgent( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-C-07' === $tpl && 'home' === $page_key ) {
		return strip_stock( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-C-08' === $tpl && 'home' === $page_key ) {
		return strip_model( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-C-09' === $tpl && 'home' === $page_key ) {
		return strip_workshop( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-C-06' === $tpl && 'home' === $page_key ) {
		return strip_menu( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-C-01' === $tpl && 'home' === $page_key ) {
		return strip_corporate( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-C-01' === $tpl && 'servicio' === $page_key ) {
		return page_service( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-C-02' === $tpl && 'home' === $page_key ) {
		return strip_institutional( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-C-03' === $tpl && 'home' === $page_key ) {
		return strip_showcase( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-E-03' === $tpl && 'home' === $page_key ) {
		return strip_story( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-C-04' === $tpl && 'home' === $page_key ) {
		return strip_landing( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-E-05' === $tpl && 'home' === $page_key ) {
		return strip_promo( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-C-05' === $tpl && 'home' === $page_key ) {
		return strip_local( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-E-01' === $tpl && 'home' === $page_key ) {
		return strip_visual( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-E-04' === $tpl && 'home' === $page_key ) {
		return strip_categories( $anchor_key, $C, $BRAND, $suid, $tgl );
	}
	if ( 'TPL-E-02' === $tpl && 'home' === $page_key ) {
		return strip_ecommerce( $anchor_key, $C, $BRAND, $suid );
	}
	if ( 'TPL-E-02' === $tpl && 'producto' === $page_key ) {
		return page_pdp( $anchor_key, $C, $BRAND, $suid );
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
	$wordmark = $BRAND;
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
	'active states'    => array(),

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
