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

/** Die loudly. A generator that limps produces a page whose defects look like design. */
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
	// Exactly SEVEN cells, first one a backticked slug and the sixth the licence
	// `RT_GALLERY_NO_MANIFEST` reads per row. The "Registers" table three headings up has three
	// cells, so it cannot be swallowed by accident.
	if ( ! preg_match(
		'/^\|\s*`([a-z0-9\-]+)`\s*\|\s*([^|]+?)\s*\|\s*(\d+)×(\d+)\s*\|\s*([^|]+?)\s*\|\s*([^|]+?)\s*\|\s*([^|]+?)\s*\|\s*([^|]+?)\s*\|\s*$/u',
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
		'alt'  => $m[8],
	);
}

if ( array() === $manifest_rows ) {
	fail( '_gallery-images.md parsed to zero rows — the table shape changed under the parser' );
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
		'track_disp'  => 'normal',
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
		'track_disp'  => 'normal',
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

$ACCENT_BY_GROUND = array(
	'paper' => '#8C3A1F',
	'warm'  => '#8C3A1F',
	'cool'  => '#8C3A1F',
	'ink'   => '#FF6A1A',
);

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

// ─────────────────────────────────────────────────────────────── 6 · the copy, one set per archetype
//
// The same discipline `_axis-proof-content.md` states for the proof pair: the copy is the
// CONSTANT. All four anchors of an archetype render these exact strings in this exact order, so
// every visible difference between them is the anchor and nothing else.

$BRAND = 'PIEDRA VALDÉS';

$CONTENT = array(
	'TPL-C-01' => array(
		'tpl'      => 'TPL-C-01',
		'tpl_name' => 'Services / Lead-Gen',
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

	'TPL-E-02' => array(
		'tpl'      => 'TPL-E-02',
		'tpl_name' => 'Catalog / Product-First',
		'site'     => 'ecommerce',
		'site_es'  => 'Ecommerce',
		'dna'      => 'COMP-HEADER search-first · COMP-HERO mini · COMP-PRODUCT-GRID · COMP-PRODUCT-CAROUSEL',
		'wire'     => 'COMP-ANNOUNCEMENT · COMP-HEADER · COMP-HERO mini · COMP-PRODUCT-GRID · COMP-PRODUCT-CAROUSEL · COMP-BENEFITS · COMP-FOOTER',
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
		'footer'   => array(
			'tag'   => 'Tienda de piedra natural · Novelda, Alicante',
			'links' => array( 'Envíos', 'Devoluciones', 'Aviso legal', 'Privacidad', 'Cookies' ),
			'legal' => '© 2026 Piedra Valdés S.L. · Ctra. de la Cantera 4, Novelda · IVA incluido',
		),
	),
);

// ─────────────────────────────────────────────────────────────── 7 · the strips
//
// One entry per `TPL-* x PERS-*` pair. Phase 1 proves the machinery; the order here is the order
// on the page, and it is fixed rather than derived so the output cannot move under a sort.

$STRIPS = array(
	array( 'tpl' => 'TPL-C-01', 'anchor' => 'editorial' ),
	array( 'tpl' => 'TPL-C-01', 'anchor' => 'direct' ),
	array( 'tpl' => 'TPL-C-01', 'anchor' => 'matter' ),
	array( 'tpl' => 'TPL-C-01', 'anchor' => 'institutional' ),
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
}

// Only the anchors and blueprints actually rendered get a CSS block. Dead CSS for an anchor no
// strip uses is the same lie as a token nothing reads.
$used_anchors = array();
$used_comps   = array();
foreach ( $STRIPS as $s ) {
	$used_anchors[ $s['anchor'] ]                          = true;
	$used_comps[ $ANCHORS[ $s['anchor'] ]['composition'] ] = true;
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
  --container-max:1280px; --content-width:1140px;
  --pad-x-mobile:20px; --pad-x-tablet:32px;
  --radius-card:12px; --radius-button:8px; --radius-image:8px; --radius-input:8px; --radius-container:16px;
  --btn-padding:.875rem 1.75rem; --btn-border-width:1.5px;
  --ease:cubic-bezier(.22,1,.36,1);
  --fs-body:clamp(1rem, 1.2vw, 1.25rem);
  --fs-small:.875rem; --fs-eyebrow:.75rem; --fs-button:1rem; --fs-nav:.95rem;
  --fs-price:clamp(1.1rem, 1.6vw, 1.35rem);
}';

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
      coefficient multiplying --fluid must arrive unitless. --fs-base is the bridge back. ══ */
:root,
[data-anchor]{
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

  /* design-system.md § derived: recipes, not literals, so they are right on a ground nobody has
     thought of yet. Percentages are the "text → bg" mixes that file measures. */
  --c-text-soft:      color-mix(in srgb, var(--c-text) 77%,   var(--c-bg));
  --c-text-muted:     color-mix(in srgb, var(--c-text) 63.4%, var(--c-bg));
  --c-border:         color-mix(in srgb, var(--c-text) 11%,   var(--c-bg));
  --c-surface-inverse: var(--c-text);
  --c-on-inverse:      var(--c-bg);
}
CSS;

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
h1,h2,h3{font-family:var(--font-primary);font-weight:700;text-wrap:balance;letter-spacing:var(--track-display)}
h1,h2{line-height:var(--display-lh)}
h1{font-size:var(--fs-h1)} h2{font-size:var(--fs-h2)}
h3{font-size:var(--fs-h3);line-height:1.25;font-weight:600}
a{color:inherit;text-decoration:none} img,svg{max-width:100%} ol,ul{list-style:none}
:focus-visible{outline:2px solid var(--c-accent);outline-offset:3px}
@media(prefers-reduced-motion:reduce){*{transition:none!important;animation:none!important}}
.sr{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap}

/* ── shared components ── */
.sec{padding-block:var(--sp-section)}
.bg-alt{background:var(--c-bg-alt)}
.muted{color:var(--c-text-muted)} .small{font-size:var(--fs-small)}
.eyebrow{display:block;font-family:var(--font-secondary);font-size:var(--fs-eyebrow);font-weight:600;
         letter-spacing:.24em;text-transform:uppercase;color:var(--c-accent)}
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
.ctas{display:flex;flex-wrap:wrap;gap:var(--sp-s)}

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
   the mid-stem `overflow-wrap` break that was rejected at desktop widths. */
.card h3{font-size:calc(var(--fs-h3) * .74);hyphens:auto}
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
.searchbar{flex:1 1 260px;min-width:0;display:flex;gap:.5rem}
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
.card.prod .body{gap:.35rem;align-items:flex-start}
.card.prod .btn{margin-top:.25rem}
/* A wide block scrolls inside its OWN container rather than pushing the page (SKILL.md). */
.rail{grid-auto-flow:column;grid-auto-columns:minmax(78%,1fr);overflow-x:auto;
      scroll-snap-type:x mandatory;padding-bottom:.5rem}
.rail > *{scroll-snap-align:start}
.bens{gap:var(--sp-s)}
.ben{display:flex;flex-direction:column;gap:.15rem;min-width:0;
     border-top:1px solid var(--c-border);padding-top:var(--sp-xs)}
.ben b{font-size:var(--fs-small)}
.bicon{color:var(--c-accent);display:block;line-height:0;margin-bottom:.15rem}
@media(min-width:600px){.bens{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(min-width:1024px){
  .bens{grid-template-columns:repeat(4,minmax(0,1fr))}
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
  [data-comp="lp-strict-grid"] .mini .head{grid-column:1/7;justify-content:center}
  [data-comp="lp-strict-grid"] .hero .media,
  [data-comp="lp-strict-grid"] .mini .media{grid-column:7/13}
  [data-comp="lp-strict-grid"] .items.cols-3{grid-template-columns:repeat(3,minmax(0,1fr))}
  [data-comp="lp-strict-grid"] .items.cols-2{grid-template-columns:repeat(2,minmax(0,1fr))}
  /* equal card heights: the rows must visibly align */
  [data-comp="lp-strict-grid"] .card{height:100%}
  [data-comp="lp-strict-grid"] .band .head{grid-column:1/7}
  [data-comp="lp-strict-grid"] .band .formwrap{grid-column:7/13}
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
  [data-comp="lp-broken-grid"] .hero .head{grid-column:c 1/full-end;grid-row:1;
    position:relative;z-index:2}
  [data-comp="lp-broken-grid"] .hero .media{grid-column:c 7/full-end;grid-row:1;
    z-index:0;align-self:stretch}
  [data-comp="lp-broken-grid"] .hero .frame{height:100%;aspect-ratio:auto;border-radius:0;
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
  [data-comp="lp-broken-grid"] .services .items,
  [data-comp="lp-broken-grid"] .cases .items{grid-column:c 1/full-end;
    grid-template-columns:4fr 5fr 3fr}
  [data-comp="lp-broken-grid"] .cases .items{grid-template-columns:5fr 7fr}
  /* "one card offset vertically by --sp-l" — on a CONTENT grid only. Applied to the catalogue it
     nudged product #2 down out of an otherwise dense 4-column row, which reads as a rendering
     bug rather than a composition choice, and TPL-E-02's density is ADN the blueprint does not
     get to spend. Caught by looking at it; no measurement would have flagged it. */
  [data-comp="lp-broken-grid"] .services .items > :nth-child(2),
  [data-comp="lp-broken-grid"] .cases .items > :nth-child(2){margin-top:var(--sp-l)}
  [data-comp="lp-broken-grid"] .band .head{grid-column:c 1/c 10}
  [data-comp="lp-broken-grid"] .band .formwrap{grid-column:c 6/full-end}
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
  [data-comp="lp-asymmetric"] .hero .media{grid-column:c 8/full-end}
  [data-comp="lp-asymmetric"] .hero .frame{border-radius:0}
  [data-comp="lp-asymmetric"] .mini .head{grid-column:c 1/c 7}
  [data-comp="lp-asymmetric"] .mini .media{grid-column:c 7/full-end}
  [data-comp="lp-asymmetric"] .mini .frame{border-radius:0}
  [data-comp="lp-asymmetric"] .mini .cats{grid-column:c 1/c 11}
  /* two columns at 7/5, never 50/50 — a 3-card grid becomes 2 + 1, which is the point */
  [data-comp="lp-asymmetric"] .services .head{grid-column:c 1/c 6}
  [data-comp="lp-asymmetric"] .services .items{grid-column:c 1/c 13;grid-template-columns:7fr 5fr}
  [data-comp="lp-asymmetric"] .cases .head{grid-column:c 1/c 6}
  [data-comp="lp-asymmetric"] .cases .items{grid-column:c 6/full-end;grid-template-columns:7fr 5fr}
  /* the catalogue grid keeps its dense equal columns — see the note in strip_ecommerce() */
  [data-comp="lp-asymmetric"] .prods .head{grid-column:c 1/c 6}
  [data-comp="lp-asymmetric"] .carousel .head{grid-column:c 1/c 6}
  [data-comp="lp-asymmetric"] .carousel .items{grid-column:c 3/full-end}
  [data-comp="lp-asymmetric"] .band .head{grid-column:c 1/c 7}
  [data-comp="lp-asymmetric"] .band .formwrap{grid-column:c 7/c 13}
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

// ═══════════════════════════════════════════════════════════════ HTML

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

/** TPL-C-01 — Services / Lead-Gen. Renders the three `[fijo · ADN]` sections plus COMP-CASES. */
function strip_corporate( $anchor_key, $C, $BRAND, $uid ) {
	$hero = $C['hero'];
	$im   = img( $hero['img'] );
	$o    = array();

	$o[] = '<header class="site-head"><div class="canvas"><div class="nav">'
		. '<span class="logo">' . h( $BRAND ) . '</span>'
		. '<nav class="mainnav" aria-label="Principal">';
	foreach ( $C['nav'] as $n ) {
		$o[] = '<a href="#">' . h( $n ) . '</a>';
	}
	$o[] = '</nav><a class="btn btn-primary btn-sm" href="#">' . h( $C['nav_cta'] ) . '</a>'
		. '</div></div></header>';

	$o[] = '<main>';

	// 1 · COMP-HERO  [fijo · ADN]
	$o[] = '<section class="sec hero" aria-label="Propuesta de valor"><div class="canvas">'
		. '<div class="head stack">'
		. '<span class="eyebrow">' . h( $hero['eyebrow'] ) . '</span>'
		. '<h1>' . h( $hero['h1'] ) . '</h1>'
		. '<p class="lede muted">' . h( $hero['lede'] ) . '</p>'
		. '<div class="ctas"><a class="btn btn-primary" href="#">' . h( $hero['cta_1'] ) . '</a>'
		. '<a class="btn btn-outline" href="#">' . h( $hero['cta_2'] ) . '</a></div>'
		. '</div>'
		. '<div class="media"><figure class="frame"><img data-img="' . h( $im['slug'] ) . '"'
		. ' alt="' . h( $im['alt'] ) . '" width="' . $im['w'] . '" height="' . $im['h'] . '"></figure></div>'
		. '</div></section>';

	// 2 · COMP-SERVICES  [fijo · ADN]
	$sv  = $C['services'];
	$o[] = '<section class="sec services grid-sec" aria-label="Servicios"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $sv['eyebrow'] ) . '</span>'
		. '<h2>' . h( $sv['h2'] ) . '</h2></div><div class="items cols-3">';
	foreach ( $sv['cards'] as $c ) {
		$o[] = card_html( $anchor_key, $c );
	}
	$o[] = '</div></div></section>';

	// 3 · COMP-CASES  [toggle TGL-CASES, default on]
	$cs  = $C['cases'];
	$o[] = '<section class="sec cases grid-sec bg-alt" aria-label="Casos"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $cs['eyebrow'] ) . '</span>'
		. '<h2>' . h( $cs['h2'] ) . '</h2></div><div class="items cols-2">';
	foreach ( $cs['cards'] as $c ) {
		$o[] = card_html( $anchor_key, $c );
	}
	$o[] = '</div></div></section>';

	// 4 · COMP-CTA + COMP-LEAD-FORM  [fijo · ADN] — the form exists and dominates the close
	$b   = $C['band'];
	$o[] = '<section class="sec band" aria-label="Contacto"><div class="canvas">'
		. '<div class="head stack"><span class="eyebrow">' . h( $b['eyebrow'] ) . '</span>'
		. '<h2>' . h( $b['h2'] ) . '</h2><p class="muted">' . h( $b['lede'] ) . '</p></div>'
		. '<div class="formwrap"><form class="leadform" onsubmit="return false">';
	foreach ( $b['fields'] as $f ) {
		$id  = $uid . '-' . $f[0];
		$o[] = '<div class="field"><label for="' . $id . '">' . h( $f[1] ) . '</label>'
			. '<input id="' . $id . '" name="' . $f[0] . '" type="' . $f[2] . '"></div>';
	}
	$o[] = '<div class="field"><label for="' . $uid . '-msg">' . h( $b['msg'] ) . '</label>'
		. '<textarea id="' . $uid . '-msg" name="msg" rows="3"></textarea></div>'
		. '<button class="btn btn-primary" type="submit">' . h( $b['submit'] ) . '</button>'
		. '</form></div></div></section>';

	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );

	return implode( "\n", $o );
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

/** One product card: the same `.card` recipe, plus the € price TPL-E-02 requires. */
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
		. '<button class="btn btn-outline btn-sm" type="button">Añadir</button>'
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

	// 0 · COMP-ANNOUNCEMENT  [toggle TGL-ANNOUNCEMENT, default on]
	$o[] = '<div class="announce"><div class="canvas"><p class="small">' . h( $C['announce'] ) . '</p></div></div>';

	// 1 · COMP-HEADER — search protagonista  [fijo]
	$o[] = '<header class="site-head"><div class="canvas"><div class="nav">'
		. '<span class="logo">' . h( $BRAND ) . '</span>'
		. '<form class="searchbar" role="search" onsubmit="return false">'
		. '<label class="sr" for="' . $uid . '-q">Buscar en la tienda</label>'
		. '<input id="' . $uid . '-q" type="search" placeholder="' . h( $C['search'] ) . '">'
		. '<button class="btn btn-primary btn-sm" type="submit">Buscar</button>'
		. '</form><div class="tools">';
	foreach ( $C['tools'] as $t ) {
		$o[] = '<a href="#">' . h( $t ) . '</a>';
	}
	$o[] = '<a class="cart" href="#">' . h( $C['cart'] ) . ' <b>' . h( $C['cart_n'] ) . '</b></a>'
		. '</div></div></div></header>';

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

	$o[] = '</main>';
	$o[] = footer_html( $C['footer'] );

	return implode( "\n", $o );
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
function handoff_text( $C, $A, $rows ) {
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
	$L[] = '';
	$L[] = 'Tipografía y motion del ancla: ux-design-system/references/design-personalities.md.';
	$L[] = '';
	$L[] = 'PRECARGA, NO DECISIÓN. Esto entra en ux-design-system paso 1 en el sitio donde hoy entra';
	$L[] = '"el sector": el diálogo sigue preguntando en términos de negocio y el cliente confirma o';
	$L[] = 'sobrescribe LOS CINCO. Un eje heredado de una tarjeta y nunca preguntado es el mismo';
	$L[] = 'default silencioso que esa regla existe para impedir.';
	return implode( "\n", $L );
}

/** The data bar: TPL id, PERS id, and all five axis positions WITH their values, as checkable data. */
function meta_html( $C, $A, $rows, $uid ) {
	$spec_id = 'spec-' . $uid;
	$o       = array();
	$o[]     = '<div class="meta"><div class="gal-wrap">'
		. '<p class="meta-pair"><b>' . h( $C['tpl'] ) . '</b> ' . h( $C['tpl_name'] )
		. ' <span class="x">×</span> <b>' . h( $A['id'] ) . '</b> ' . h( $A['name'] ) . '</p>'
		. '<p class="meta-sub">' . h( $C['site_es'] ) . ' · ADN fijo: <code>' . h( $C['dna'] ) . '</code></p>'
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
		. '<pre id="' . h( $spec_id ) . '" tabindex="0">' . h( handoff_text( $C, $A, $rows ) ) . '</pre>'
		. '</details>';

	$o[] = '</div></div>';
	return implode( "\n", $o );
}

// ── assemble ───────────────────────────────────────────────────────────────────────────────────

/** The strip's DOM id, derived in one place because the handoff assertion below has to rebuild it. */
function strip_uid( $C, $anchor ) {
	return strtolower( str_replace( '-', '', $C['tpl'] ) ) . '-' . $anchor;
}

$body    = array();
$n_strip = count( $STRIPS );

foreach ( $STRIPS as $s ) {
	$C   = $CONTENT[ $s['tpl'] ];
	$A   = $ANCHORS[ $s['anchor'] ];
	$lp  = strtolower( $COMPOSITION[ $A['composition'] ]['lp'] );
	$uid = strip_uid( $C, $s['anchor'] );

	if ( 'corporate' === $C['site'] ) {
		$inner = strip_corporate( $s['anchor'], $C, $BRAND, $uid );
	} elseif ( 'ecommerce' === $C['site'] ) {
		$inner = strip_ecommerce( $s['anchor'], $C, $BRAND, $uid );
	} else {
		fail( "no renderer for site type `{$C['site']}`" );
	}

	$body[] = '<section class="strip" id="' . h( $uid ) . '"'
		. ' data-site="' . h( $C['site'] ) . '"'
		. ' data-tpl="' . h( $C['tpl'] ) . '"'
		. ' data-pers="' . h( $s['anchor'] ) . '"'
		. ' aria-label="' . h( $C['tpl'] . ' × ' . $A['id'] ) . '">';
	$body[] = meta_html( $C, $A, axis_rows( $A, $SCALE, $DENSITY, $GROUND, $ELEVATION, $COMPOSITION ), $uid );
	// `lang` is on the sample rather than the strip: the meta bar around it is Spanish too, but
	// the sample is what carries hyphenated headings, and `hyphens:auto` needs a language to pick
	// a dictionary. Without it Chrome hyphenates nothing and the card headings overflow again.
	$body[] = '<div class="sample" lang="es" data-anchor="' . h( $s['anchor'] ) . '" data-comp="' . h( $lp ) . '">';
	$body[] = $inner;
	$body[] = '</div></section>';
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

$intro = '<header class="gal-head"><div class="gal-wrap">'
	. '<span class="eyebrow">NovaMira · uso interno</span>'
	. '<h1>Galería de plantillas</h1>'
	. '<p>Cada tira es un par <b>arquetipo × ancla</b>: el <code>TPL-*</code> decide qué secciones existen '
	. 'y en qué orden, el <code>PERS-*</code> decide cómo se ven los cinco ejes perceptuales. '
	. 'Elegir una tira precarga las dos cosas.</p>'
	. '<p class="gal-note">El copy y las fotografías son los mismos en todas las tiras del mismo arquetipo. '
	. 'Si dos tiras se distinguen, la diferencia es el ancla — no la foto ni el texto.</p>'
	. '</div></header>';

// ── the sticky filter: by site type and by anchor ──────────────────────────────────────────────
//
// Built from the strips that actually exist rather than from a hardcoded list, so a filter can
// never offer a value nothing matches — and adding a ninth strip needs no edit here.

$sites_present  = array();
$anchors_present = array();
foreach ( $STRIPS as $s ) {
	$sites_present[ $CONTENT[ $s['tpl'] ]['site'] ] = $CONTENT[ $s['tpl'] ]['site_es'];
	$anchors_present[ $s['anchor'] ]                = $ANCHORS[ $s['anchor'] ]['name'];
}

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
	. $n_strip . ' tiras</p>'
	. '</div></div>';

// Plain JS, no library. `hidden` rather than a class: it is the platform's own "not rendered and
// not in the accessibility tree", so a filtered-out strip disappears for a screen reader too
// instead of only for the eye.
$filter_js = "<script>\n"
	. "(function(){\n"
	. "  var state={site:'all',pers:'all'};\n"
	. "  var strips=[].slice.call(document.querySelectorAll('.strip'));\n"
	. "  var count=document.getElementById('gal-count');\n"
	. "  function apply(){\n"
	. "    var n=0;\n"
	. "    for(var i=0;i<strips.length;i++){\n"
	. "      var s=strips[i];\n"
	. "      var ok=(state.site==='all'||s.getAttribute('data-site')===state.site)\n"
	. "          && (state.pers==='all'||s.getAttribute('data-pers')===state.pers);\n"
	. "      s.hidden=!ok; if(ok){n++;}\n"
	. "    }\n"
	. "    count.textContent = (n===strips.length) ? n+' tiras' : n+' de '+strips.length+' tiras';\n"
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
	. '<p>' . $n_strip . ' ' . ( 1 === $n_strip ? 'tira' : 'tiras' ) . ' · '
	. count( $only_used ) . ' imágenes del set compartido · generado por <code>_build-gallery.php</code> '
	. 'desde <code>assets/gallery/img/</code> y <code>_gallery-images.md</code>.</p>'
	. '</div></footer>';

$noscript = '<noscript><div class="noscript"><div class="gal-wrap">'
	. 'Las fotografías se hidratan con una pasada de JavaScript desde un único mapa de <code>data:</code> URIs. '
	. 'Sin JS el texto, la maquetación y los ejes se ven enteros; las fotos no. '
	. 'El bloque de precarga se lee y se selecciona igual: sólo el botón «Copiar» necesita JS.'
	. '</div></div></noscript>';

$html = $head . "\n<style>\n" . implode( "\n", $css ) . "\n</style>\n\n"
	. $noscript . "\n"
	. $intro . "\n"
	. $filter . "\n\n"
	. '<main class="gal-strips">' . "\n"
	. implode( "\n", $body ) . "\n"
	. '</main>' . "\n\n"
	. $foot . "\n\n"
	. $filter_js . "\n"
	. $copy_js . "\n"
	. $script . "\n";

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
foreach ( $ACCENT as $g => $a ) {
	printf( "               accent %s on %-5s → %s bg · %s bg-alt · label %s %s\n",
		$a['hex'], $g, $a['r_bg'], $a['r_alt'], $a['on_is'], $a['r_on'] );
}
