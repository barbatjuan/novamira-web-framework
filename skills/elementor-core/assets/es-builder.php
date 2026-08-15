<?php
/**
 * Elementor page builder helpers (NovaMira raw-PHP).
 * Native Elementor / Elementor Pro widgets only. No custom CSS, no third-party widgets.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Deterministic element ids.
 *
 * Elementor keys its generated CSS on element ids, so random ids desync the
 * stylesheet from cached HTML on every rebuild. A seeded counter keeps ids
 * stable across regenerations of the same layout.
 */
function es_uid() {
	global $es_id_seed, $es_id_n;
	if ( ! isset( $es_id_seed ) ) {
		$es_id_seed = 'es';
	}
	$es_id_n = isset( $es_id_n ) ? $es_id_n + 1 : 1;
	return substr( md5( $es_id_seed . '-' . $es_id_n ), 0, 7 );
}

/** Start a new stable id sequence for one page or template. */
function es_uid_reset( $seed ) {
	global $es_id_seed, $es_id_n;
	$es_id_seed = $seed;
	$es_id_n    = 0;
}

function es_c( array $settings, array $children = array(), $inner = false ) {
	return array(
		'id'       => es_uid(),
		'elType'   => 'container',
		'settings' => $settings,
		'elements' => $children,
		'isInner'  => $inner,
	);
}

function es_w( $type, array $settings ) {
	return array(
		'id'         => es_uid(),
		'elType'     => 'widget',
		'widgetType' => $type,
		'settings'   => $settings,
		'elements'   => array(),
	);
}

/**
 * A four-sided box value, with the density axis applied ONCE, here.
 *
 * The multiplier lives inside this function and not at the 29 call sites for
 * one reason: a call site can forget, and the one that forgets is the one that
 * stops moving when the axis moves. Two guards keep it from scaling things that
 * are not rhythm:
 *
 *  - $unit. Density scales LENGTHS. A percentage or a viewport unit is already
 *    relative to something else, and multiplying it is not a smaller gap -- it
 *    is a different layout. Every call in all four assets is px today (checked,
 *    not assumed); the guard is here so the first non-px call does not silently
 *    become a bug.
 *  - $scale. A border WIDTH and a border RADIUS ride in this same shape and are
 *    NOT rhythm. At sp_scale 1.7 a 1px hairline rounds to 2px -- that is a
 *    heavier border, not more air, and it makes the `hairline` elevation
 *    position unexpressible -- and a 16px radius becomes 27px, which is a
 *    different shape language. Those call sites say so with es_box_unscaled().
 */
function es_box( $t, $r, $b, $l, $unit = 'px', $scale = true ) {
	if ( $scale && 'px' === $unit ) {
		$t = es_sp( $t );
		$r = es_sp( $r );
		$b = es_sp( $b );
		$l = es_sp( $l );
	}
	return array(
		'unit'     => $unit,
		'top'      => (string) $t,
		'right'    => (string) $r,
		'bottom'   => (string) $b,
		'left'     => (string) $l,
		'isLinked' => false,
	);
}

/** A box the density axis must NOT touch: border widths and border radii. */
function es_box_unscaled( $t, $r, $b, $l ) {
	return es_box( $t, $r, $b, $l, 'px', false );
}

function es_size( $n, $unit = 'px' ) {
	return array( 'unit' => $unit, 'size' => $n, 'sizes' => array() );
}

function es_img( $slug ) {
	static $cache = array();
	if ( isset( $cache[ $slug ] ) ) {
		return $cache[ $slug ];
	}
	$posts = get_posts(
		array(
			'post_type'      => 'attachment',
			'name'           => $slug,
			'posts_per_page' => 1,
			'post_status'    => 'inherit',
		)
	);
	if ( empty( $posts ) ) {
		/* This used to return the empty shape and say NOTHING, which is how a mistyped slug shipped
		   an image widget with no <img> in it while every check stayed green. Two channels, because
		   the mistake happens HERE and is discovered LATER: es_warn() now, and `es_missing`, which
		   rides along in the settings so es_container_walk() can name the slug when it audits the
		   tree — including the re-audit qa-review runs against what actually landed. Elementor
		   reads only `url` and `id`, so the extra key is inert to it and legible to us.
		   Cached like a hit so a slug used in ten places warns once, not ten times. */
		$cache[ $slug ] = array( 'url' => '', 'id' => '', 'es_missing' => $slug );
		es_warn( 'no existe ninguna imagen con el slug "' . $slug . '". El widget se va a construir SIN imagen. Sube el archivo o corrige el slug antes de desplegar.' );
		return $cache[ $slug ];
	}
	$cache[ $slug ] = array(
		'url' => wp_get_attachment_url( $posts[0]->ID ),
		'id'  => $posts[0]->ID,
	);
	return $cache[ $slug ];
}

/**
 * One token's colour, at an alpha, as an rgba() string.
 *
 * A token whose value hides another token's colour is not a token. Overriding
 * the accent used to leave the old green inside every glow -- `accent` went
 * navy and `elev_accent` still carried `rgba(15,169,104,0.55)`, so the client
 * got a navy button with a green halo, and the ONE edit point was not one.
 * The seven accent/ink glows and the two white veils are derived through here
 * so that overriding the source colour really does move all of them.
 *
 * $alpha is TEXT on purpose. `0.10` and `0.1` are the same float and different
 * bytes, and this file carries both shapes on purpose-by-accident: the outline
 * wash is `0.10`, the cart glow is `0.5`. Formatting a number would silently
 * rewrite one of them, and rewriting emitted bytes is the one thing the
 * extraction task may not do.
 */
function es_rgba( $hex, $alpha ) {
	$h = ltrim( (string) $hex, '#' );
	if ( 3 === strlen( $h ) ) {
		$h = $h[0] . $h[0] . $h[1] . $h[1] . $h[2] . $h[2];
	}
	if ( 6 !== strlen( $h ) || ! ctype_xdigit( $h ) ) {
		/* Loud and visibly unpainted beats silently plausible: a wrong colour
		   in a shadow reads as "the theme decided that", a missing one does
		   not, and the warning names the value that could not be read. */
		es_warn( 'es_rgba() no sabe leer "' . $hex . '" como color hex, asi que el efecto que lo usa se queda SIN pintar. Escribe el token como #RGB o #RRGGBB.' );
		return 'rgba(0,0,0,0)';
	}
	return 'rgba(' . hexdec( substr( $h, 0, 2 ) ) . ',' . hexdec( substr( $h, 2, 2 ) ) . ',' . hexdec( substr( $h, 4, 2 ) ) . ',' . $alpha . ')';
}

/**
 * One token's colour, darkened by a factor, as a hex string.
 *
 * The hover of a colour is not a second colour to remember. `accent_hover` was
 * a hand-picked darker GREEN sitting next to an accent a client is expected to
 * change -- so a navy brand got a navy button and a green hover, which is the
 * rgba bug from es_rgba() one level up: an edit point that is only one edit
 * point if you also remember the other one.
 *
 * Multiplying every channel by the same factor is the ordinary "darken": it
 * keeps the hue and the relative channel spacing and works on any brand,
 * including a dark one, where a fixed subtraction would flatten to black.
 */
function es_shade( $hex, $factor ) {
	$h = ltrim( (string) $hex, '#' );
	if ( 3 === strlen( $h ) ) {
		$h = $h[0] . $h[0] . $h[1] . $h[1] . $h[2] . $h[2];
	}
	if ( 6 !== strlen( $h ) || ! ctype_xdigit( $h ) ) {
		/* Same reasoning as es_rgba(): loud and visibly unpainted beats
		   silently plausible, and the warning names the value it could not read. */
		es_warn( 'es_shade() no sabe leer "' . $hex . '" como color hex, asi que el estado derivado que lo usa se queda SIN pintar. Escribe el token como #RGB o #RRGGBB.' );
		return '';
	}
	$out = '#';
	for ( $i = 0; $i < 3; $i++ ) {
		$c    = (int) round( hexdec( substr( $h, $i * 2, 2 ) ) * $factor );
		$out .= sprintf( '%02X', max( 0, min( 255, $c ) ) );
	}
	return $out;
}

/**
 * Tokens that are another token's colour, darkened.
 *
 * array( source token, factor ). Same table shape as es_token_recipes() and for
 * the same reason: the key list, the derivation and the unknown-key guard read
 * ONE table and cannot drift apart.
 *
 * The two factors are different on purpose, because the two jobs are. Pressing
 * a button has to be FELT, so the accent drops ~18%; a hairline nudging on
 * hover is a hint, so the border drops ~6.5%. One factor for both would either
 * make the border look broken or make the button look asleep.
 */
function es_token_shades() {
	return array(
		/* 0.815 is not a fitted curiosity: it is the factor at which the
		   framework's own accent reproduces its hand-picked hover #0C8A55
		   exactly, so making this derived costs zero emitted bytes on the
		   default brand while every other brand finally gets a hover of its
		   OWN colour. */
		'accent_hover' => array( 'accent', 0.815 ),
		'border_hover' => array( 'border', 0.935 ),
	);
}

/**
 * Tokens that are another token's colour with a veil over it.
 *
 * array( prefix, source token, alpha ). Kept as data rather than inline so the
 * key list, the derivation and the unknown-key guard all read the SAME table
 * and cannot drift apart.
 *
 * Only the colour is derived; the geometry (`0 18px 40px -12px `) stays a
 * literal because it is a distance, not a colour, and distances belong to the
 * density axis that Task 2 owns.
 */
function es_token_recipes() {
	return array(
		'muted_on_inverse'  => array( '', 'on_inverse', '0.75' ),
		'border_on_inverse' => array( '', 'on_inverse', '0.5' ),
		'accent_wash'       => array( '', 'accent', '0.10' ),
		'scrim_from'        => array( '', 'surface_inverse', '0.92' ),
		'scrim_to'          => array( '', 'surface_inverse', '0.30' ),
		/* ONE neutral lift and ONE accent glow. Both existed twice, and neither
		   pair had a stated reason to differ -- `elev_hover_panel` was the same
		   shadow 6px further out at 0.20, `elev_accent_cart` the same glow 2px
		   tighter at 0.5. Where two values disagreed, the survivor is the one
		   design-system.md actually documents: `0 18px 40px -12px rgba(21,24,26,.16)`
		   is verbatim its `soft-shadow` --elev-hover. The other two were nobody's
		   decision, which is why nothing was keeping them in step. */
		'elev_hover'        => array( '0 18px 40px -12px ', 'text', '0.16' ),
		'elev_accent'       => array( '0 12px 26px -10px ', 'accent', '0.55' ),
	);
}

/* ---------------------------------------------------------- design tokens
   This block IS what elementor-core/SKILL.md step 2 means by "swap the
   constants": ONE edit point per project, filled from the axis positions the
   ux-design-system dialogue resolved. The values below are the framework
   default, not a recommendation for any client -- a site that ships with them
   unchanged is a site nobody made a decision about.

   No colour, family, shadow, easing curve, font size or spacing value between
   here and the END marker below is typed by hand. The bare CSS keyword `ease`
   used to be the exception -- typed 9 times on 5 lines INSIDE the same rules as
   the tokenised curve, so `transform` eased on cubic-bezier(.22,1,.36,1) while
   `border-color` fell back to the browser default, two motion languages in one
   rule -- and it is gone: every duration in this file now names es_t('ease').

   ONE thing is still not true of that region: RT_BUILDER_HARDCODED_TOKEN does
   not exist yet -- it is Task 3. Until it lands, NOTHING mechanical stops the
   next literal. The golden dump in tests/ is what catches a value that moves;
   it does not catch a literal that happens to equal the token it replaced.

   Keys are named for the ROLE the value plays, never for what it looks like:
   `muted`, not `grey`; `surface_inverse`, not `surface_dark`. A token called
   `green` cannot survive a client whose brand is navy, and a token called
   `dark` cannot survive one whose inverse surface is cream -- and renaming it
   afterwards means touching every call site again.

   Nothing below is a duplicate of anything else below. The six that were --
   five neutral borders doing one job, two accent glows differing in geometry
   AND alpha, two neutral lifts, and one white written `#fff` in a CSS blob and
   `#FFFFFF` everywhere else -- were collapsed here, and the values that moved
   are recorded in the golden dump's diff. Drift is not a naming problem: two
   keys for one job are two things to remember to change together, and the
   whole point of this block is that there is only one. */
function es_tokens( array $override = array() ) {
	static $t = null;
	if ( null === $t || $override ) {
		$base = array(
			/* ground ------------------------------------------------ */
			'bg'                 => '#FFFFFF', /* the light surface a card sits on */
			'surface_inverse'    => '#15181A', /* the surface that flips the page over: the `dark` button, the CTA scrim */
			'transparent'        => 'rgba(0,0,0,0)', /* an explicit no-fill; Elementor needs the value, not an absent key */
			/* ink --------------------------------------------------- */
			'text'               => '#15181A',
			'muted'              => '#6A6F6C',
			'on_accent'          => '#FFFFFF', /* text/glyph sitting ON the accent */
			'on_inverse'         => '#FFFFFF', /* text sitting ON surface_inverse or on the CTA scrim */
			'muted_on_inverse'   => null, /* derived: on_inverse at 0.75 */
			/* borders ----------------------------------------------- */
			/* ONE hairline and its hover. This was five keys -- a rest/hover
			   pair for the image-box card, a second pair two shades off for
			   the feature card, and a darker edge for the outline button --
			   five values for one job, none of them a decision anybody
			   recorded. The outline button's edge is the one that lightens
			   most (#CBD0CB -> #E5E7E5); a control edge wanting more contrast
			   than a divider is a real argument, but it is an accessibility
			   argument, and #CBD0CB was 1.5:1 on white, nowhere near the 3:1
			   WCAG 1.4.11 asks of a control. It was not buying the contrast
			   its darkness implied, so nothing is lost by collapsing it. */
			'border'             => '#E5E7E5',
			'border_hover'       => null, /* derived: border darkened 6.5% */
			'border_on_inverse'  => null, /* derived: on_inverse at 0.5 */
			/* accent -- derives from the BRAND, never from the anchor.
			   design-tokens.md is explicit that accent is not an axis. */
			'accent'             => '#0FA968',
			'accent_hover'       => null, /* derived: accent darkened 18.5% */
			'accent_wash'        => null, /* derived: accent at 0.10 -- the faint tint an outline control fills with */
			/* scrim over the CTA banner photo, so the copy stays legible */
			'scrim_from'         => null, /* derived: surface_inverse at 0.92 */
			'scrim_to'           => null, /* derived: surface_inverse at 0.30 */
			/* scale ------------------------------------------------- */
			'font_head'          => 'Space Grotesk',
			'font_body'          => 'Manrope',
			/* The four numbers that ARE the scale axis, at the `classic`
			   position from design-system.md's "Perceptual axes" table. Every
			   heading and body size in this file is es_fs(step) off these; no
			   size is typed. */
			'fs_base'            => 16,
			'type_ratio'         => 1.333,
			'display_lh'         => 1.10,
			'fs_h1_max'          => 64,
			/* The one size deliberately OFF the ratio. design-system.md pins
			   --fs-small at a flat 0.875rem rather than deriving it, because
			   card meta text is not a step in the heading hierarchy: it is the
			   floor under body, and a ratio that pushes headings apart must not
			   also push meta text into unreadability. Nearest-step would have
			   sent it to 16 -- the same size as body -- erasing the distinction
			   it exists to make. An honest exception, not a bent derivation.
			   It does NOT track fs_base; a project that moves the base should
			   move this too. */
			'fs_small'           => 14,
			/* Same reasoning, and design-system.md pins it the same way
			   (--fs-eyebrow: 0.75rem, flat). This one was ON the scale for a
			   while at step -1, and the render numbers showed why that is
			   wrong: a step BELOW body shrinks as the ratio grows, so the
			   monumental position -- the one meant to make headings shout --
			   was quietly taking this uppercase, letter-spaced label down to
			   9.9px. The scale axis pushes the hierarchy APART; it must not
			   push the small end into unreadability. */
			'fs_eyebrow'         => 12,
			/* density ----------------------------------------------- */
			/* `standard`. Applied inside es_box() and the three gap defaults,
			   so one multiplier moves the whole rhythm. */
			'sp_scale'           => 1.0,
			/* elevation --------------------------------------------- */
			/* The rest state, which did not exist at all before: this file had
			   five :hover shadows and nothing underneath them, which is why
			   `hairline` and `soft-shadow` could not be expressed. `none` is
			   the `none` position from design-system.md and renders exactly as
			   the absence it replaces. Swap it for `0 0 0 1px #E5E7E5`
			   (hairline) or `0 1px 2px rgba(0,0,0,.04)` (soft-shadow) and every
			   card recipe picks it up. */
			'elev_rest'          => 'none',
			'elev_hover'         => null, /* derived: text at 0.16 */
			'elev_accent'        => null, /* derived: accent at 0.55 */
			/* motion ------------------------------------------------ */
			'ease'               => 'cubic-bezier(.22,1,.36,1)',
		);

		/* A mistyped override key used to be accepted in silence: es_tokens()
		   merged `acento` in, nothing read it, the accent stayed green and no
		   channel said a word. es_t()'s guard only catches typos on the READ
		   side, and this is the one edit point the whole token layer exists to
		   create -- a typo here is the likeliest operator error there is. */
		foreach ( $override as $clave => $ignorado ) {
			if ( ! array_key_exists( $clave, $base ) ) {
				es_warn( 'la clave "' . $clave . '" no es un token: el override se ha aceptado y NO cambia nada. Revisa el nombre contra la lista de es_tokens.' );
			}
		}

		$t = array_merge( $base, $override );
		/* Shades run BEFORE the veils: a shade produces a hex, a veil consumes
		   one, so this order is what lets a future glow be built on a hover. */
		foreach ( es_token_shades() as $clave => $receta ) {
			/* Same escape hatch as the veils below: a brand whose hover is not
			   a darker version of its accent must be able to say so. */
			if ( ! array_key_exists( $clave, $override ) ) {
				$t[ $clave ] = es_shade( $t[ $receta[0] ], $receta[1] );
			}
		}
		foreach ( es_token_recipes() as $clave => $receta ) {
			/* An explicit override of a derived key still wins: a brand whose
			   glow is not its accent must be able to say so. */
			if ( ! array_key_exists( $clave, $override ) ) {
				$t[ $clave ] = $receta[0] . es_rgba( $t[ $receta[1] ], $receta[2] );
			}
		}
		foreach ( $t as $clave => $valor ) {
			if ( null === $valor ) {
				es_warn( 'el token "' . $clave . '" quedo sin valor: hay un hueco declarado arriba sin receta en es_token_recipes(), o al reves.' );
			}
		}
	}
	return $t;
}

function es_t( $key ) {
	$t = es_tokens();
	if ( ! array_key_exists( $key, $t ) ) {
		es_warn( 'es_t("' . $key . '") no existe en es_tokens(); revisa el nombre.' );
		return '';
	}
	return $t[ $key ];
}

/**
 * One step on the type scale, in px.
 *
 * Step 0 is body; each step up multiplies by the ratio, each step down divides.
 * This is what the scale axis IS -- ONE number moves the whole hierarchy,
 * instead of twelve sizes that happen to look related and drift apart the first
 * time someone nudges one of them. (They had: two card titles declared with the
 * same `title_size => h3` were shipping at 19px and 17px.)
 *
 * Capped at fs_h1_max so a monumental ratio cannot run a heading off a phone.
 * At the `classic` position the cap does not engage until step 5, and this file
 * emits nothing above step 3 -- so today it is insurance, not a live value, and
 * the suite proves it engages rather than assuming it.
 */
function es_fs( $step ) {
	$t  = es_tokens();
	$px = $t['fs_base'] * pow( $t['type_ratio'], $step );
	return round( min( $px, $t['fs_h1_max'] ), 1 );
}

/**
 * One length on the density axis, in px.
 *
 * Every spacing value in the file goes through here -- via es_box() and the
 * three gap defaults -- so density is one multiplier over the whole rhythm
 * rather than 29 numbers to re-tune by hand and 29 chances to miss one.
 */
function es_sp( $px ) {
	return (int) round( $px * es_tokens()['sp_scale'] );
}

/**
 * Full-width section wrapper with boxed inner content.
 */
function es_section( array $children, array $opts = array() ) {
	$settings = array(
		'content_width'    => 'boxed',
		'flex_direction'   => 'column',
		'flex_gap'         => array( 'unit' => 'px', 'size' => 0, 'column' => '0', 'row' => '0' ),
		'padding'          => es_box( 88, 24, 88, 24 ),
		'padding_tablet'   => es_box( 72, 24, 72, 24 ),
		'padding_mobile'   => es_box( 56, 20, 56, 20 ),
	);
	if ( ! empty( $opts['bg'] ) ) {
		$settings['background_background'] = 'classic';
		$settings['background_color']      = $opts['bg'];
	}
	if ( ! empty( $opts['settings'] ) ) {
		$settings = array_merge( $settings, $opts['settings'] );
	}
	return es_c( $settings, $children );
}

/**
 * Two-column section — THE SECTION IS THE ROW.
 *
 * The reflex for a split layout is `es_section( es_row( array( $left, $right ) ) )`, which
 * costs a whole container level whose only job is "be a flex row". The section can be that
 * row itself: `flex_direction:row` on the section, `column` at tablet/mobile, and the two
 * halves as DIRECT children. Same result, one level less, one less click in the editor.
 *
 * Confirmed on a live build (de la O Abogados, contacto: 8 containers/depth 4 -> 4/depth 2).
 *
 * A boxed container puts its flex on the generated `.e-con-inner`; the NATIVE flex controls
 * know that and target it correctly. Only hand-written `custom_css` has to say
 * `selector>.e-con-inner` (see references/gotchas.md).
 *
 * $opts: bg, gap, align (flex_align_items), reverse (stack mobile in reverse), settings.
 */
function es_split( array $children, array $opts = array() ) {
	/* The three flex gaps are the only spacing that does not travel through
	   es_box(), so density has to be applied to them by hand -- here, at the
	   default, and not at any call site. */
	$gap      = es_sp( isset( $opts['gap'] ) ? (int) $opts['gap'] : 48 );
	$settings = array(
		'content_width'         => 'boxed',
		'flex_direction'        => 'row',
		'flex_direction_tablet' => empty( $opts['reverse'] ) ? 'column' : 'column-reverse',
		'flex_direction_mobile' => empty( $opts['reverse'] ) ? 'column' : 'column-reverse',
		'flex_align_items'      => isset( $opts['align'] ) ? $opts['align'] : 'center',
		'flex_gap'              => array( 'unit' => 'px', 'size' => $gap, 'column' => (string) $gap, 'row' => (string) $gap ),
		'padding'               => es_box( 88, 24, 88, 24 ),
		'padding_tablet'        => es_box( 72, 24, 72, 24 ),
		'padding_mobile'        => es_box( 56, 20, 56, 20 ),
	);
	if ( ! empty( $opts['bg'] ) ) {
		$settings['background_background'] = 'classic';
		$settings['background_color']      = $opts['bg'];
	}
	if ( ! empty( $opts['settings'] ) ) {
		$settings = array_merge( $settings, $opts['settings'] );
	}
	return es_c( $settings, $children );
}

/**
 * Give ONE element a width without wrapping it in a container.
 *
 * A width is not a layout. Wrapping a widget in a container just to make it 58% wide buys a
 * <div>, a CSS block and an editor level for something the element itself can carry:
 * `_element_width:'initial'` unlocks `_element_custom_width`. Works on widgets and on
 * containers alike — both read the same `_element_*` keys.
 *
 * Defaults to full width at mobile, which is what you want ~always; pass $mobile to override.
 */
function es_wide( array $el, $pct, $mobile = 100, $unit = '%' ) {
	$el['settings']['_element_width']        = 'initial';
	$el['settings']['_element_custom_width'] = es_size( $pct, $unit );
	if ( null !== $mobile ) {
		$el['settings']['_element_width_mobile']        = 'initial';
		$el['settings']['_element_custom_width_mobile'] = es_size( $mobile, '%' );
	}
	return $el;
}

/**
 * A photo is a WIDGET, not a container background.
 *
 * `background_image` on a container costs twice: it needs a container that exists only to hold
 * the picture (usually an EMPTY one, which the audit flags), and the image ships with no `alt`,
 * so it is invisible to screen readers and to Google Images. The native image widget with a
 * fixed height + `object-fit:cover` crops identically AND keeps the alt text.
 *
 * Control keys confirmed on the de la O build. If a future Elementor renames them, introspect
 * (`references/gotchas.md` -> "Verify widget/control names") rather than guessing.
 * `object-fit` is hyphenated on purpose — that IS the control id — and Elementor only honours
 * it while `height` has a value.
 */
function es_photo( $img_slug, $height = 420, array $extra = array() ) {
	$settings = array(
		'image'          => es_img( $img_slug ),
		'image_size'     => 'large',
		'width'          => es_size( 100, '%' ),
		'height'         => es_size( $height ),
		'height_mobile'  => es_size( (int) round( $height * 0.72 ) ),
		'object-fit'     => 'cover',
		'object-position' => 'center center',
	);
	$settings = array_merge( $settings, $extra );
	return es_w( 'image', $settings );
}

/**
 * Card hover, applied through the container's native Custom CSS field so it
 * does not depend on Elementor's conditionally-enqueued animation assets
 * (which are not registered when the layout is written via the API).
 */
function es_card_hover_css() {
	$ease = es_t( 'ease' );
	return 'selector .elementor-widget-image-box{transition:transform .5s ' . $ease . ',box-shadow .5s ' . $ease . ',border-color .5s ' . $ease . ';box-shadow:' . es_t( 'elev_rest' ) . ';will-change:transform;}'
		. 'selector .elementor-widget-image-box:hover{transform:translateY(-4px);border-color:' . es_t( 'border_hover' ) . ';box-shadow:' . es_t( 'elev_hover' ) . ';}'
		. 'selector .elementor-widget-image-box .elementor-image-box-img{overflow:hidden;}'
		. 'selector .elementor-widget-image-box .elementor-image-box-img img{transition:transform .7s ' . $ease . ';will-change:transform;}'
		. 'selector .elementor-widget-image-box:hover .elementor-image-box-img img{transform:scale(1.045);}'
		. 'selector .elementor-widget-image-box .elementor-image-box-title{transition:color .4s ' . $ease . ';}'
		. 'selector .elementor-widget-image-box:hover .elementor-image-box-title{color:' . es_t( 'accent' ) . ';}';
}

/**
 * Shared CSS for any native WooCommerce products grid (archive, related, home).
 * Smooth hover lift + image zoom, green add-to-cart with hover, equal-height
 * cards (button pinned to the bottom), the redundant inline "Ver carrito" link
 * hidden, and the added-state button relabelled to "Añadido".
 *
 * This is the single source of truth for the products grid — every consumer must
 * call it instead of pasting a copy, because hand-copied duplicates drift (the
 * archive and related-products templates had already diverged from each other).
 * Grid-specific extras that genuinely belong to one template only (archive
 * pagination, for instance) ride in through `$extra_css` so the shared rules stay
 * shared and the difference stays visible at the call site.
 */
function es_products_css( $extra_css = '' ) {
	$ease = es_t( 'ease' );
	return 'selector ul.products li.product{transition:transform .5s ' . $ease . ',box-shadow .5s ' . $ease . ';box-shadow:' . es_t( 'elev_rest' ) . ';border-radius:12px;overflow:hidden;padding:10px;will-change:transform;}'
		. 'selector ul.products li.product .woocommerce-loop-product__link img,selector ul.products li.product img{transition:transform .7s ' . $ease . ';border-radius:8px;will-change:transform;}'
		. 'selector ul.products li.product:hover{transform:translateY(-4px);box-shadow:' . es_t( 'elev_hover' ) . ';}'
		. 'selector ul.products li.product:hover img{transform:scale(1.045);}'
		. 'selector ul.products{align-items:stretch;}'
		. 'selector ul.products li.product{display:flex!important;flex-direction:column;height:100%;}'
		. 'selector ul.products li.product .button{margin-top:auto;background-color:' . es_t( 'accent' ) . '!important;border-color:' . es_t( 'accent' ) . '!important;color:' . es_t( 'on_accent' ) . '!important;border-radius:6px!important;transition:background-color .3s ' . $ease . ',box-shadow .35s ' . $ease . '!important;}'
		. 'selector ul.products li.product .button:hover{background-color:' . es_t( 'accent_hover' ) . '!important;box-shadow:' . es_t( 'elev_accent' ) . '!important;}'
		. 'selector ul.products li.product a.added_to_cart{display:none!important;}'
		. 'selector ul.products li.product a.button.added{font-size:0!important;}'
		. 'selector ul.products li.product a.button.added::after{content:"Añadido ✓"!important;font-size:13.5px!important;font-weight:600;}'
		. $extra_css;
}

/**
 * Grid container - native Elementor grid, avoids nested column containers.
 *
 * grid_rows_grid defaults to 2fr, which paints an empty second row (and a big
 * gap) whenever a grid holds a single row of content. Forcing auto rows makes
 * the grid height follow its content.
 */
function es_grid( $cols, array $children, $gap = 24, array $extra = array() ) {
	$gap      = es_sp( $gap );   /* ver es_split(): la densidad se aplica aqui, no en la llamada */
	$settings = array(
		'container_type'          => 'grid',
		'content_width'           => 'full',
		'grid_columns_grid'       => array( 'unit' => 'fr', 'size' => $cols ),
		'grid_columns_grid_tablet' => array( 'unit' => 'fr', 'size' => min( 2, $cols ) ),
		'grid_columns_grid_mobile' => array( 'unit' => 'fr', 'size' => 1 ),
		'grid_rows_grid'          => array( 'unit' => 'custom', 'size' => 'auto' ),
		'grid_rows_grid_tablet'   => array( 'unit' => 'custom', 'size' => 'auto' ),
		'grid_rows_grid_mobile'   => array( 'unit' => 'custom', 'size' => 'auto' ),
		'grid_gap'                => array(
			'unit'   => 'px',
			'column' => (string) $gap,
			'row'    => (string) $gap,
			'isLinked' => true,
		),
		'_flex_grow'              => 0,
		'_flex_shrink'            => 0,
		'custom_css'              => es_card_hover_css(),
	);
	$settings = array_merge( $settings, $extra );
	return es_c( $settings, $children, true );
}

/**
 * Horizontal row (buttons, inline items).
 */
function es_row( array $children, $gap = 14, array $extra = array() ) {
	$gap      = es_sp( $gap );   /* ver es_split(): la densidad se aplica aqui, no en la llamada */
	$settings = array(
		'content_width'  => 'full',
		'flex_direction' => 'row',
		'flex_wrap'      => 'wrap',
		'flex_gap'       => array( 'unit' => 'px', 'size' => $gap, 'column' => (string) $gap, 'row' => (string) $gap ),
		'flex_align_items' => 'flex-start',
		'_flex_grow'     => 0,
		'_flex_shrink'   => 0,
	);
	$settings = array_merge( $settings, $extra );
	return es_c( $settings, $children, true );
}

/**
 * Small uppercase label above a heading, in the accent colour.
 *
 * $color defaults to null rather than to the accent itself because PHP cannot
 * call a function in a parameter default; null means "whatever the tokens say".
 */
function es_eyebrow( $text, $color = null ) {
	$color = ( null === $color ) ? es_t( 'accent' ) : $color;
	return es_w(
		'heading',
		array(
			'title'                      => $text,
			'header_size'                => 'div',
			'title_color'                => $color,
			'typography_typography'      => 'custom',
			'typography_font_family'     => es_t( 'font_body' ),
			'typography_font_size'       => es_size( es_t( 'fs_eyebrow' ) ),
			'typography_font_weight'     => '700',
			'typography_text_transform'  => 'uppercase',
			'typography_letter_spacing'  => es_size( 1.6 ),
			'_margin'                    => es_box( 0, 0, 14, 0 ),
		)
	);
}

/** Section heading. */
function es_h( $text, $tag = 'h2', array $extra = array() ) {
	$settings = array(
		'title'       => $text,
		'header_size' => $tag,
		'_margin'     => es_box( 0, 0, 16, 0 ),
	);
	$settings = array_merge( $settings, $extra );
	return es_w( 'heading', $settings );
}

/** Body paragraph. */
function es_p( $html, array $extra = array() ) {
	$settings = array(
		'editor'                => '<p>' . $html . '</p>',
		'text_color'            => es_t( 'muted' ),
		'typography_typography' => 'custom',
		'typography_font_family' => es_t( 'font_body' ),
		'typography_font_size'  => es_size( es_fs( 0 ) ),
		'typography_line_height' => es_size( 1.65, 'em' ),
		'_margin'               => es_box( 0, 0, 0, 0 ),
	);
	$settings = array_merge( $settings, $extra );
	return es_w( 'text-editor', $settings );
}

/** Button. */
/**
 * Site-wide button system. Two families, one hover language:
 *   'primary'       -> solid green, lifts + green glow on hover.
 *   'outline'       -> ghost on light bg, fills faint green + turns green on hover.
 *   'outline-light' -> ghost on dark bg (heroes), fills white on hover.
 *   'dark'          -> solid near-black (legacy, kept for dark CTAs).
 * NOTE: the Button widget hover keys are button_background_hover_color and
 * button_hover_border_color (NOT background_hover_color). Using the wrong key is
 * why hovers silently did nothing. Transitions + lift ride on native custom_css
 * so they never depend on conditionally-enqueued hover assets.
 */
function es_btn( $text, $link, $style = 'primary', array $extra = array() ) {
	$ease  = es_t( 'ease' );
	$trans = 'selector .elementor-button{transition:background-color .3s ' . $ease . ',color .3s ' . $ease . ',border-color .3s ' . $ease . ',box-shadow .35s ' . $ease . ',transform .35s ' . $ease . ';}';
	$lift_green = $trans . 'selector .elementor-button:hover{transform:translateY(-2px);box-shadow:' . es_t( 'elev_accent' ) . ';}';
	$lift_soft  = $trans . 'selector .elementor-button:hover{transform:translateY(-2px);}';

	$settings = array(
		'text'                   => $text,
		'link'                   => array( 'url' => $link, 'is_external' => '', 'nofollow' => '' ),
		'border_radius'          => es_box_unscaled( 8, 8, 8, 8 ),
		'text_padding'           => es_box( 14, 26, 14, 26 ),
		'typography_typography'  => 'custom',
		'typography_font_family' => es_t( 'font_body' ),
		'typography_font_size'   => es_size( es_fs( 0 ) ),
		'typography_font_weight' => '600',
	);
	if ( 'primary' === $style ) {
		$settings['background_color']              = es_t( 'accent' );
		$settings['button_text_color']            = es_t( 'on_accent' );
		$settings['button_background_hover_color'] = es_t( 'accent_hover' );
		$settings['hover_color']                  = es_t( 'on_accent' );
		$settings['custom_css']                   = $lift_green;
	} elseif ( 'dark' === $style ) {
		$settings['background_color']              = es_t( 'surface_inverse' );
		$settings['button_text_color']            = es_t( 'on_inverse' );
		$settings['button_background_hover_color'] = es_t( 'accent' );
		$settings['hover_color']                  = es_t( 'on_accent' );
		$settings['custom_css']                   = $lift_soft;
	} elseif ( 'outline' === $style ) {
		$settings['background_color']              = es_t( 'transparent' );
		$settings['button_text_color']            = es_t( 'text' );
		$settings['border_border']                = 'solid';
		$settings['border_width']                 = es_box_unscaled( 1, 1, 1, 1 );
		$settings['border_color']                 = es_t( 'border' );
		$settings['button_background_hover_color'] = es_t( 'accent_wash' );
		$settings['hover_color']                  = es_t( 'accent' );
		$settings['button_hover_border_color']    = es_t( 'accent' );
		$settings['custom_css']                   = $lift_soft;
	} elseif ( 'outline-light' === $style ) {
		$settings['background_color']              = es_t( 'transparent' );
		$settings['button_text_color']            = es_t( 'on_inverse' );
		$settings['border_border']                = 'solid';
		$settings['border_width']                 = es_box_unscaled( 1, 1, 1, 1 );
		$settings['border_color']                 = es_t( 'border_on_inverse' );
		/* The fill is the SOLID version of the ink this button already lives
		   in: border_on_inverse is on_inverse at 0.5, so hovering to on_inverse
		   at 1.0 is the same ink turned up. It used to read es_t('bg') -- the
		   PAGE surface -- which was byte-identical only because both are
		   #FFFFFF today, and would have filled a cream-page client's ghost
		   button with cream on top of a near-black hero. */
		$settings['button_background_hover_color'] = es_t( 'on_inverse' );
		$settings['hover_color']                  = es_t( 'text' );
		$settings['button_hover_border_color']    = es_t( 'on_inverse' );
		$settings['custom_css']                   = $lift_soft;
	}
	$settings = array_merge( $settings, $extra );
	return es_w( 'button', $settings );
}

/**
 * Service / product card on the native image-box widget.
 * image_size is the width slider; thumbnail_size picks the WP file size.
 */
function es_card( $img_slug, $title, $text, $link = '', array $extra = array() ) {
	$settings = array(
		'image'                    => es_img( $img_slug ),
		'thumbnail_size'           => 'large',
		'image_size'               => es_size( 100, '%' ),
		'image_height'             => es_size( 190 ),
		'image_object_fit'         => 'cover',
		'image_border_radius'      => es_size( 8 ),
		'image_space'              => es_size( es_sp( 20 ) ),
		'title_text'               => $title,
		'description_text'         => $text,
		'position'                 => 'top',
		'text_align'               => 'left',
		'title_size'               => 'h3',
		'title_color'              => es_t( 'text' ),
		'description_color'        => es_t( 'muted' ),
		'title_typography_typography' => 'custom',
		'title_typography_font_family' => es_t( 'font_head' ),
		'title_typography_font_size' => es_size( es_fs( 1 ) ),
		'title_typography_font_weight' => '700',
		'description_typography_typography' => 'custom',
		'description_typography_font_family' => es_t( 'font_body' ),
		'description_typography_font_size' => es_size( es_t( 'fs_small' ) ),
		'description_typography_line_height' => es_size( 1.6, 'em' ),
		'title_bottom_space'       => es_size( es_sp( 8 ) ),
		'_padding'                 => es_box( 20, 20, 24, 20 ),
		'_background_background'   => 'classic',
		'_background_color'        => es_t( 'bg' ),
		'_border_border'           => 'solid',
		'_border_width'            => es_box_unscaled( 1, 1, 1, 1 ),
		'_border_color'            => es_t( 'border' ),
		'_border_radius'           => es_box_unscaled( 10, 10, 10, 10 ),
		/* Hover handled by the parent grid's Custom CSS (es_card_hover_css). */
	);
	if ( $link ) {
		$settings['link'] = array( 'url' => $link, 'is_external' => '', 'nofollow' => '' );
	}
	$settings = array_merge( $settings, $extra );
	return es_w( 'image-box', $settings );
}

/**
 * Rounded CTA banner: full-bleed photo, dark scrim, copy and button on the left.
 * Sits inside a normal section so it keeps the page's boxed width.
 */
function es_cta_banner( $img_slug, $title, $text, $btn_text, $btn_link, $bg = '' ) {
	return es_section(
		array(
			es_c(
				array(
					'content_width'         => 'full',
					'flex_direction'        => 'column',
					'flex_justify_content'  => 'center',
					'min_height'            => es_size( 400 ),
					'min_height_mobile'     => es_size( 340 ),
					'padding'               => es_box( 64, 64, 64, 64 ),
					'padding_mobile'        => es_box( 36, 28, 36, 28 ),
					'border_radius'         => es_box_unscaled( 14, 14, 14, 14 ),
					'overflow'              => 'hidden',
					'background_background' => 'classic',
					'background_image'      => es_img( $img_slug ),
					'background_position'   => 'center center',
					'background_size'       => 'cover',
					'background_overlay_background' => 'gradient',
					'background_overlay_color'   => es_t( 'scrim_from' ),
					'background_overlay_color_b' => es_t( 'scrim_to' ),
					'background_overlay_gradient_type'  => 'linear',
					'background_overlay_gradient_angle' => es_size( 90, 'deg' ),
					'background_overlay_color_stop'     => es_size( 10, '%' ),
					'background_overlay_color_b_stop'   => es_size( 95, '%' ),
				),
				array(
					es_c(
						array(
							'content_width'  => 'full',
							'flex_direction' => 'column',
							'width'          => es_size( 56, '%' ),
							'width_tablet'   => es_size( 100, '%' ),
						),
						array(
							es_h(
								$title,
								'h2',
								array(
									'title_color'                 => es_t( 'on_inverse' ),
									'typography_typography'       => 'custom',
									'typography_font_family'      => es_t( 'font_head' ),
									'typography_font_size'        => es_size( es_fs( 3 ) ),
									'typography_font_size_mobile' => es_size( es_fs( 2 ) ),
									'typography_font_weight'      => '700',
									'typography_line_height'      => es_size( es_t( 'display_lh' ), 'em' ),
									'_margin'                     => es_box( 0, 0, 16, 0 ),
								)
							),
							es_p(
								$text,
								array(
									'text_color'             => es_t( 'muted_on_inverse' ),
									'typography_font_size'   => es_size( es_fs( 0 ) ),
									'typography_line_height' => es_size( 1.65, 'em' ),
									'_margin'                => es_box( 0, 0, 30, 0 ),
								)
							),
							es_btn( $btn_text, $btn_link, 'primary', array( '_element_width' => 'auto' ) ),
						),
						true
					),
				),
				true
			),
		),
		$bg ? array( 'bg' => $bg ) : array()
	);
}

/** Advantage row built on the native icon-box widget. */
function es_iconbox( $icon, $title, $text ) {
	return es_w(
		'icon-box',
		array(
			'selected_icon'   => array( 'value' => $icon, 'library' => 'fa-solid' ),
			'title_text'      => $title,
			'description_text' => $text,
			'position'        => 'inline-start',
			'text_align'      => 'left',
			'title_size'      => 'h3',
			'primary_color'   => es_t( 'accent' ),
			'icon_space'      => es_size( es_sp( 18 ) ),
			'icon_size'       => es_size( 20 ),
			'title_color'     => es_t( 'text' ),
			'description_color' => es_t( 'muted' ),
			'title_typography_typography' => 'custom',
			'title_typography_font_family' => es_t( 'font_head' ),
			'title_typography_font_size' => es_size( es_fs( 1 ) ),
			'title_typography_font_weight' => '700',
			'description_typography_typography' => 'custom',
			'description_typography_font_family' => es_t( 'font_body' ),
			'description_typography_font_size' => es_size( es_t( 'fs_small' ) ),
			'description_typography_line_height' => es_size( 1.55, 'em' ),
			'title_bottom_space' => es_size( es_sp( 5 ) ),
			'_padding'        => es_box( 22, 0, 22, 0 ),
			'_border_border'  => 'solid',
			'_border_width'   => es_box_unscaled( 1, 0, 0, 0 ),
			'_border_color'   => es_t( 'border' ),
		)
	);
}

/**
 * Premium feature card: white card, green circular icon chip, smooth hover
 * lift with a green top-accent reveal. Shared by home ventajas and inner pages
 * so the whole site keeps one card language. Meant to sit inside es_grid().
 */
function es_feature_card( $icon, $title, $text, array $extra = array() ) {
	$ease     = es_t( 'ease' );
	$defaults = array(
		'content_width'         => 'full',
		'flex_direction'        => 'column',
		'padding'               => es_box( 34, 30, 36, 30 ),
		'background_background'  => 'classic',
		'background_color'      => es_t( 'bg' ),
		'border_border'         => 'solid',
		'border_width'          => es_box_unscaled( 1, 1, 1, 1 ),
		'border_color'          => es_t( 'border' ),
		'border_radius'         => es_box_unscaled( 16, 16, 16, 16 ),
		'custom_css'            => 'selector{position:relative;overflow:hidden;transition:transform .5s ' . $ease . ',box-shadow .5s ' . $ease . ',border-color .5s ' . $ease . ';box-shadow:' . es_t( 'elev_rest' ) . ';will-change:transform;}'
			. 'selector::before{content:"";position:absolute;top:0;left:0;right:0;height:3px;background:' . es_t( 'accent' ) . ';transform:scaleX(0);transform-origin:left;transition:transform .55s ' . $ease . ';}'
			. 'selector:hover{transform:translateY(-6px);box-shadow:' . es_t( 'elev_hover' ) . ';border-color:' . es_t( 'border_hover' ) . ';}'
			. 'selector:hover::before{transform:scaleX(1);}'
			. 'selector .es-feat-ico{transition:transform .5s ' . $ease . ';}'
			. 'selector:hover .es-feat-ico{transform:translateY(-3px);}',
	);
	return es_c(
		array_merge( $defaults, $extra ),
		array(
			es_w(
				'icon',
				array(
					'selected_icon'   => array( 'value' => $icon, 'library' => 'fa-solid' ),
					'view'            => 'stacked',
					'shape'           => 'circle',
					'primary_color'   => es_t( 'accent' ),
					'secondary_color' => es_t( 'on_accent' ),
					'size'            => es_size( 20 ),
					'_css_classes'    => 'es-feat-ico',
					'_margin'         => es_box( 0, 0, 22, 0 ),
				)
			),
			es_h( $title, 'h3', array( 'typography_typography' => 'custom', 'typography_font_family' => es_t( 'font_head' ), 'typography_font_size' => es_size( es_fs( 1 ) ), 'typography_font_weight' => '700', '_margin' => es_box( 0, 0, 9, 0 ) ) ),
			es_p( $text, array( 'typography_font_size' => es_size( es_t( 'fs_small' ) ), 'typography_line_height' => es_size( 1.58, 'em' ) ) ),
		),
		true
	);
}

/* -------------------------------------------------- end of the visual layer
   Everything below is the save pipeline, the container audit, the sandbox and
   the slug machinery. No styling value belongs here, and RT_BUILDER_HARDCODED_TOKEN
   does not scan past this line.

   That boundary is a RESERVATION, not a response to this file as it stands: every
   post id below is concatenated (`'#' . $id`), never typed, so a hex scan of the
   current machinery finds nothing. It is drawn because the day someone does type
   a "#732" into a warning, a colour regex cannot tell it from a colour -- and the
   cheap time to draw a boundary is before it is load-bearing. The START boundary
   is the one carrying weight today: without it the token declarations themselves
   read as 21 hardcoded literals.

   What the region above now holds, and what it deliberately still does not:
     - Every colour, family, shadow, easing curve, font size and spacing length
       reads a token. The bare `ease` keyword is gone.
     - Lengths written INSIDE the CSS blobs are not on the density axis:
       `border-radius:12px`, `padding:10px`, `font-size:13.5px` and the motion
       distances (`translateY(-4px)`, `scale(1.045)`) are still literals. They
       are a real gap, not an oversight -- reported, not silently left.
     - Body leading drifted the way the borders did: 1.65 / 1.60 / 1.58 / 1.55
       across four helpers for one job. design-system.md says body is 1.6 flat
       and is NOT an axis, so collapsing it belongs to whoever owns that value,
       not to the axis task. `display_lh`, which IS an axis, is tokenised. */

/**
 * Audit the container tree before it is written.
 *
 * Every extra container level is paid three times: one more wrapper <div> in the DOM, one
 * more block of generated CSS, and one more thing a human has to click through in the
 * Elementor editor to reach the widget they actually want. Nesting that buys nothing is the
 * fastest way a generated layout becomes one nobody wants to maintain.
 *
 * Reports, never blocks, and separates three severities on purpose:
 *
 *   offenders   — wrong with no argument. An empty container; a container wrapping a single
 *                 WIDGET while carrying no background/border/shadow/boxed width of its own;
 *                 anything nested past depth 3.
 *   optimizable — exactly one shape: a container whose only child is a GRID. A container child
 *                 is not automatically this — a flex ROW child and a COLUMN child are both
 *                 offenders, because both collapse. The grid pair is kept OUT of `offenders`
 *                 deliberately: `es_section( es_grid(...) )` is this repo's own dominant idiom,
 *                 and an audit that screams on every normal build is one people learn to
 *                 ignore. Merging that pair into a single boxed grid container is plausible
 *                 but NOT yet confirmed on a live site — verify before doing it wholesale.
 *   unaudited   — an elType this audit has no opinion about: pre-3.6 `section`/`column`, a kit
 *                 import, a future element. It used to fall off the walk entirely, so a whole
 *                 legacy page measured 0 containers / 0 widgets / depth 0 and read as a clean
 *                 build. Silence about a tree is not a verdict on it.
 *
 * `unaudited` is deliberately a map `elType => {count, first}` rather than the `string[]` the
 * other two use: an imported kit page carries hundreds of legacy elements and would otherwise
 * bury the rows a human can act on. It is NOT an offender — the caller cannot fix an import by
 * rewriting an `es_*()` call — and never blocks, because `es_save_page()` reports mid-write.
 *
 * That last sentence is a rule with teeth, and it runs one level further than it looks. BELOW an
 * element this audit cannot judge, it makes no contextual claim either: depth accumulated above a
 * legacy wrapper is measured but never charged as an offender, an inherited boxed width is not
 * assumed, and a container whose ONLY child is an unjudgeable element is not judged at all. What a
 * container does wrong on its own — empty, or wrapping a lone widget for nothing — is still its
 * caller's to fix wherever it sits. The line is between a container's own defect, which the caller
 * wrote, and its context, which an import handed it.
 *
 * @return array{containers:int,widgets:int,max_depth:int,offenders:string[],optimizable:string[],unaudited:array<string,array{count:int,first:string}>}
 */
function es_container_audit( array $elements ) {
	$out = array( 'containers' => 0, 'widgets' => 0, 'max_depth' => 0, 'offenders' => array(), 'optimizable' => array(), 'unaudited' => array() );
	es_container_walk( $elements, 0, '', $out );
	return $out;
}

/**
 * `$anc` is what the ancestors say, and both keys exist to stop this audit claiming more than it
 * knows. `boxed` — an ancestor already constrains the width to the boxed content width, the
 * context `es_container_earns_its_place()` needs to tell a wrapper that DOES something from one
 * repeating what its parent already did. `opaque` — an ancestor is an elType this walk cannot
 * judge, so nothing derived from the path above is trustworthy down here.
 */
function es_container_walk( array $els, $depth, $path, array &$out, array $anc = array() ) {
	$boxed  = ! empty( $anc['boxed'] );
	$opaque = ! empty( $anc['opaque'] );
	foreach ( $els as $i => $el ) {
		$type     = isset( $el['elType'] ) ? $el['elType'] : '';
		$here     = $path . '/' . $i;
		$kids     = ( isset( $el['elements'] ) && is_array( $el['elements'] ) ) ? $el['elements'] : array();
		$settings = ( isset( $el['settings'] ) && is_array( $el['settings'] ) ) ? $el['settings'] : array();
		$kid_anc  = $anc;
		/* ABOVE the dispatch: the only background_image here sits on a CONTAINER, so checking it in
		   the widget branch was dead on arrival. isset(), not !empty(): the payload IS the slug. */
		foreach ( array( 'image', 'background_image' ) as $k ) {
			if ( isset( $settings[ $k ]['es_missing'] ) ) { $out['offenders'][] = $here . ' ' . ( '' === $type ? '(sin elType)' : $type ) . ' sin imagen: el slug "' . $settings[ $k ]['es_missing'] . '" no existe, va a renderizar vacio'; }
		}
		/* Also above the dispatch: a setting written on the wrong element type saves fine and never
		   applies — visible in the source, absent on screen, so it gets re-added and re-wondered-at. */
		foreach ( es_key_offenders( $type, $settings, isset( $el['widgetType'] ) ? $el['widgetType'] : '' ) as $why ) {
			$out['offenders'][] = $here . ' ' . ( '' === $type ? '(sin elType)' : $type ) . ' ' . $why;
		}
		if ( 'container' === $type ) {
			$out['containers']++;
			$d = $depth + 1;
			if ( ! $kids ) {
				$out['offenders'][] = empty( $settings['background_image']['url'] )
					? $here . ' contenedor vacio'
					: $here . ' contenedor vacio que solo sostiene una imagen de fondo: usa es_photo() (widget image + object-fit) y gana el alt';
			} elseif ( 1 === count( $kids ) ) {
				$only   = isset( $kids[0]['elType'] ) ? $kids[0]['elType'] : '?';
				$kidset = isset( $kids[0]['settings'] ) && is_array( $kids[0]['settings'] ) ? $kids[0]['settings'] : array();
				/* The child's elType and the ancestors' width are context the settings alone cannot
				   carry, and the predicate needs both — hence the read before the call. */
				$ctx = array( 'only_child' => $only, 'boxed_ancestor' => $boxed );
				/* Three elType families, and the third one is why this is a whitelist and not
				   `'container' !== $only`. That negation sent every elType the walk had just filed
				   under `unaudited` into the lone-WIDGET remedy, so an import shaped
				   `container > column > widget` was told "usa el widget directo" about a child that
				   is not a widget — un-followable advice, counted as an offender, printed on the
				   same line as NO AUDITABLE. An unjudgeable only child means the wrapper cannot be
				   judged either: it is already recorded where it belongs, and nothing is said here. */
				if ( ! es_container_earns_its_place( $settings, $ctx ) ) {
					if ( 'widget' === $only ) {
						$out['offenders'][] = $here . es_lone_widget_remedy( $settings, $boxed );
					} elseif ( 'container' === $only ) {
						if ( isset( $kidset['container_type'] ) && 'grid' === $kidset['container_type'] ) {
							/* section > grid: this repo's own idiom. Mergeable in theory, a human decides. */
							$out['optimizable'][] = $here . ' contenedor cuyo unico hijo es un grid: candidato a fusionar';
						} elseif ( in_array( isset( $kidset['flex_direction'] ) ? $kidset['flex_direction'] : '', array( 'row', 'row-reverse' ), true ) ) {
							/* A flex ROW child is the one es_split() actually collapses: the section becomes
							   the row. Naming that remedy for a child stacking in a COLUMN was advice that
							   could not be followed — es_split() would have changed the layout's axis. Only
							   the desktop value is read on purpose: es_split() sets the tablet/mobile
							   variants itself, so no breakpoint value can change WHICH remedy applies. */
							$out['offenders'][] = $here . ' contenedor cuyo unico hijo es una fila flex: la seccion ES la fila, usa es_split()';
						} else {
							$out['offenders'][] = $here . ' contenedor cuyo unico hijo es otro contenedor en columna: fusiona ambos, el hijo no aporta un eje distinto';
						}
					}
					/* Any other elType falls through saying nothing: the child is already recorded
					   under `unaudited`, and a wrapper around something this walk cannot judge
					   cannot be judged either. */
				}
			}
			/* Depth is MEASURED across legacy levels below and reported in max_depth either way.
			   It is only CHARGED here when every level above was one this walk judged — four
			   imported wrappers are not a nesting decision the caller made. */
			if ( $d > 3 && ! $opaque ) {
				$out['offenders'][] = $here . ' anidado a profundidad ' . $d . ' (max recomendado 3)';
			}
			$kid_anc['boxed'] = $boxed || ( isset( $settings['content_width'] ) && 'boxed' === $settings['content_width'] );
		} elseif ( 'widget' === $type ) {
			$out['widgets']++;
			$d = $depth;                                    /* a widget is content, not a wrapper level */
		} else {
			$k = ( '' === $type ) ? '(sin elType)' : $type;
			if ( ! isset( $out['unaudited'][ $k ] ) ) {
				$out['unaudited'][ $k ] = array( 'count' => 0, 'first' => $here );
			}
			$out['unaudited'][ $k ]['count']++;
			$d       = $depth + 1;                          /* a legacy wrapper IS a level, judged or not */
			$kid_anc = array( 'opaque' => true );           /* below it, inherit nothing — not even boxed */
		}
		if ( $d > $out['max_depth'] ) {
			$out['max_depth'] = $d;
		}
		/* HOISTED out of the container branch. It used to live inside it, so anything under a
		   legacy wrapper — or inside a widget that carries its own elements, like a loop
		   template — was never walked at all. */
		es_container_walk( $kids, $d, $here, $out, $kid_anc );
	}
}

/**
 * Name the remedy for a container whose only child is a widget.
 *
 * Every branch has to be something the caller can DO. "pasa el padding al widget" was printed
 * unconditionally, including for wrappers carrying no padding to pass.
 */
function es_lone_widget_remedy( array $s, $boxed_ancestor ) {
	if ( ! empty( $s['width'] ) ) {
		return ' envoltorio que solo da un ancho: usa es_wide($widget, N) en vez de un contenedor';
	}
	if ( $boxed_ancestor && isset( $s['content_width'] ) && 'boxed' === $s['content_width'] ) {
		return ' envoltorio boxed dentro de otro boxed: acotar de nuevo no cambia el ancho, borra este';
	}
	if ( ! empty( $s['padding'] ) ) {
		return ' envoltorio de 1 widget sin fondo/borde/sombra: pasa el padding al widget';
	}
	return ' envoltorio de 1 widget que no aporta nada: usa el widget directo';
}

/**
 * A container with a single child is only justified if it does something nothing else can.
 *
 * `$ctx` carries what the settings alone cannot say: `only_child` is that child's elType, and
 * `boxed_ancestor` is true when some ancestor already constrains the width. Optional, so every
 * existing caller keeps working and the predicate stays usable on a bare settings array.
 */
function es_container_earns_its_place( array $s, array $ctx = array() ) {
	/* es_img() returns array('url'=>'','id'=>'') when the slug is missing, and a non-empty
	   array is truthy — so a BROKEN image lookup used to buy the container an alibi. */
	if ( ! empty( $s['background_image']['url'] ) ) {
		return true;
	}
	foreach ( array( 'background_background', 'border_border', 'border_radius', 'box_shadow_box_shadow_type', 'sticky' ) as $k ) {
		if ( ! empty( $s[ $k ] ) ) {
			return true;
		}
	}
	/* Changing direction or column count at a breakpoint is a real reason to exist. */
	foreach ( $s as $k => $v ) {
		if ( ! empty( $v ) && preg_match( '/^(flex_direction|grid_columns_grid|content_width)_(tablet|mobile)$/', $k ) ) {
			return true;
		}
	}
	/* Constraining a lone widget to the boxed content width. Elementor gives a widget no way to
	   do this itself, so here the wrapper IS the mechanism and "use the widget directly" was
	   advice that would have changed the layout.
	   All three conditions are load-bearing. The child must be a WIDGET: a container child gets
	   its own boxed setting, and passing this to `es_section( es_row(...) )` would silence the
	   offender that names es_split(). No boxed ANCESTOR: a second boxing inside the first
	   changes nothing. And `content_width` must be present and 'boxed' EXPLICITLY, because
	   Elementor's runtime default is already boxed, so an absent key is not a decision.
	   `padding` is deliberately NOT a pass and must not become one: padding on a wrapper is the
	   canonical thing that belongs on the widget, which is the offender's own remedy. It only
	   sharpens which message es_lone_widget_remedy() prints. */
	if ( 'widget' === ( isset( $ctx['only_child'] ) ? $ctx['only_child'] : '' )
		&& empty( $ctx['boxed_ancestor'] )
		&& isset( $s['content_width'] ) && 'boxed' === $s['content_width'] ) {
		return true;
	}
	return false;
}

/**
 * Say something out loud, once, through BOTH channels.
 *
 * The sandbox returns STDOUT from `execute-php`; `error_log()` goes to the server's PHP log,
 * which in practice nobody ever fetches. Every warning in this framework used to take only the
 * second road — including "this template will NOT appear on the front end", which is about the
 * loudest thing the system can have to say. Route every warning through here so a silent
 * failure becomes impossible by construction.
 *
 * ES_AUDIT_SILENT does NOT reach here, and the gate that used to is gone. That constant mutes the
 * audit REPORT — the routine per-page lines someone silences to keep stdout parseable for another
 * consumer. A warning is the opposite kind of message: it only exists because something went wrong
 * and nobody asked. Muting both with one switch meant "this template will NOT appear on the front
 * end" could be silenced as a side effect of wanting tidy output, leaving it only on the road the
 * docblock above already explains nobody travels.
 */
function es_warn( $msg ) {
	error_log( 'NovaMira: ' . str_replace( "\n", ' | ', $msg ) );
	echo 'NovaMira AVISO: ' . $msg . "\n";
}

/**
 * Report the audit where a human will actually read it.
 *
 * This used to only call error_log(), and that is precisely why a build shipped with empty and
 * redundant containers anyway: the offenders were written to the server's PHP log, which nobody
 * fetches. The sandbox returns STDOUT from `execute-php`, so echoing is the difference between a
 * rule that is measured and a rule that is seen. error_log() stays as the durable copy.
 *
 * Define ES_AUDIT_SILENT before the build if stdout must stay clean for some other consumer.
 */
function es_container_report( array $elements, $label = '' ) {
	global $es_audit_runs;

	$a   = es_container_audit( $elements );
	$msg = sprintf(
		'NovaMira contenedores%s: %d contenedores / %d widgets, profundidad max %d',
		$label ? ' [' . $label . ']' : '',
		$a['containers'],
		$a['widgets'],
		$a['max_depth']
	);
	if ( $a['offenders'] ) {
		$msg .= "\n  A CORREGIR (" . count( $a['offenders'] ) . "):\n    " . implode( "\n    ", $a['offenders'] );
	}
	if ( $a['optimizable'] ) {
		$msg .= "\n  fusionables (" . count( $a['optimizable'] ) . ", decide un humano):\n    " . implode( "\n    ", $a['optimizable'] );
	}
	if ( $a['unaudited'] ) {
		$bits  = array();
		$total = 0;
		foreach ( $a['unaudited'] as $k => $u ) {
			$total += $u['count'];
			$bits[] = 'elType "' . $k . '" x' . $u['count'] . ' (primero en ' . $u['first'] . ')';
		}
		$msg .= "\n  NO AUDITABLE (" . $total . ", esta parte del arbol no fue juzgada):\n    " . implode( "\n    ", $bits );
	}

	error_log( str_replace( "\n", ' | ', $msg ) );
	if ( ! defined( 'ES_AUDIT_SILENT' ) ) {
		echo $msg . "\n";
	}

	if ( ! isset( $es_audit_runs ) || ! is_array( $es_audit_runs ) ) {
		$es_audit_runs = array();
	}
	$es_audit_runs[ $label ? $label : count( $es_audit_runs ) ] = $a;

	return $a;
}

/**
 * One verdict line for the whole build.
 *
 * Call it at the END of the build function. Per-page lines scroll past; this is the line the
 * deploy step reads to decide whether the layout is shippable.
 *
 * THE LINE IS THE PRIMARY ARTIFACT; the integer is a convenience for a caller that wants to
 * branch. It used to be one integer covering two different worlds:
 *
 *    0  — audited, clean.
 *   >0  — audited, N offenders to fix.
 *   -1  — NOTHING was audited: es_container_report() never ran. It used to return 0, so a build
 *         that forgot to call the audit reported what a passing build reports. It speaks through
 *         es_warn(), not the verdict writer: it warns about the audit, it does not judge a tree.
 *   -2  — audited, but part of the tree is elTypes this audit cannot judge. Zero offenders over a
 *         tree nobody judged is not a pass either.
 *
 * `0 === clean` is preserved deliberately: callers already branch on it. The two failures get
 * NEGATIVE sentinels so no existing `if ( es_audit_summary() )` silently starts treating them as
 * success, and -2 wins over an offender count, because you cannot ask someone to fix what was
 * never judged.
 *
 * The line NAMES its verdict; the INTEGER carries it. Branch on the integer, never on a word found
 * in the line: the caller's page label is interpolated into the deep-nesting suffix, so a page can
 * put any text of its own in there — including the word a deploy gate might be looking for.
 */
function es_audit_summary() {
	global $es_audit_runs;

	/* Before the verdict, and outside all four of its branches: whether `/` serves the blog is a
	   fact about the SITE, not about the tree, so it is true of a clean build and of SIN AUDITAR
	   alike. It stays out of the return value on purpose — callers already branch on that integer,
	   and a fifth meaning would change what an existing `if ( es_audit_summary() )` decides. */
	es_front_page_check();

	if ( ! isset( $es_audit_runs ) || ! is_array( $es_audit_runs ) || ! $es_audit_runs ) {
		/* es_warn(), NO es_audit_verdict(): el escritor lo volvia callable, y era la unica linea
		   que este archivo SIEMPRE habia impreso. */
		es_warn( 'auditoria VEREDICTO SIN AUDITAR: ninguna pagina paso por es_container_report(). O falta cablear el audit en la funcion de build, o se llamo al resumen antes de guardar nada, o TODAS las paginas fallaron al guardarse. No hay ningun arbol detras de este numero.' );
		return -1;
	}
	$off  = 0;
	$opt  = 0;
	$un   = 0;
	$deep = array();
	foreach ( $es_audit_runs as $page => $a ) {
		$off += count( $a['offenders'] );
		$opt += count( $a['optimizable'] );
		if ( ! empty( $a['unaudited'] ) ) {
			foreach ( $a['unaudited'] as $u ) {
				$un += $u['count'];   /* the COUNT, not one per elType: an import is hundreds */
			}
		}
		if ( $a['max_depth'] > 3 ) {
			$deep[] = $page . '(' . $a['max_depth'] . ')';
		}
	}
	$tail = sprintf(
		'%d paginas, %d a corregir, %d fusionables%s',
		count( $es_audit_runs ),
		$off,
		$opt,
		$deep ? ', profundidad >3 en ' . implode( ', ', $deep ) : ''
	);
	if ( $un ) {
		return es_audit_verdict(
			'NO AUDITABLE: ' . $tail . ', y ' . $un . ' elementos con un elType que este audit no sabe juzgar (section/column heredados, o un kit importado) — parte de este arbol no fue juzgada',
			-2
		);
	}
	if ( $off ) {
		return es_audit_verdict( 'A CORREGIR: ' . $tail, $off );
	}
	return es_audit_verdict( 'LIMPIO: ' . $tail, 0 );
}

/** One writer for the three verdicts ABOUT a tree, so no branch forgets the silence rule or the log. */
function es_audit_verdict( $rest, $code ) {
	$line = 'NovaMira auditoria VEREDICTO ' . $rest;
	error_log( $line );
	if ( ! defined( 'ES_AUDIT_SILENT' ) ) {
		echo $line . "\n";
	}
	return $code;
}

/**
 * Save an Elementor layout onto a page, creating the page when missing.
 *
 * This docblock used to sit 375 lines up the file, immediately followed by ANOTHER docblock, so it
 * documented nothing at all while the comment inside this function pointed at it by name.
 *
 * `$tpl` defaults to `elementor_header_footer` (Elementor Full Width): full-bleed
 * content that KEEPS the theme / Theme Builder header and footer. Do not switch the
 * default to `elementor_canvas` — Canvas renders neither, so every page built with it
 * silently loses the global header, breaking the "header on every page" house rule.
 * Pass `elementor_canvas` explicitly for the rare page that must have no chrome
 * (a standalone landing, a coming-soon splash).
 *
 * Overwriting an existing page is destructive and irreversible on its own: writing
 * `_elementor_data` through the meta API replaces the whole layout and leaves no
 * revision behind. Every overwrite therefore parks the displaced state in a timestamped
 * backup key first (see es_backup_page_state) — the layout AND the page template, the
 * edit mode, the template type, the version, and the post fields. Overwriting a page
 * that was not built with Elementor also warns: its `post_content` survives in the
 * database and in the backup, but stops being what the visitor sees.
 *
 * The existing `post_status` is preserved too. Forcing `publish` here used to push a
 * client's draft live as a side effect of rebuilding its layout; only pages this
 * function creates are published.
 *
 * `$action` is an out-parameter, passed by reference rather than returned because callers rely on
 * the return value being the page id. It reports FOUR outcomes, not two:
 *
 *   'created'         — the page did not exist and now does, at the slug that was asked for.
 *   'updated'         — an existing page was rewritten in place.
 *   'created-renamed' — the page was created, but NOT where you asked. See below.
 *   'failed'          — nothing was written. The return value is 0.
 *
 * Branch on `$action`, and treat anything that is not 'created' or 'updated' as needing a human.
 */
function es_save_page( $slug, $title, array $elements, $tpl = 'elementor_header_footer', &$action = null ) {
	es_safe_mode_check();
	es_approval_check( $slug );
	$page = es_page_by_slug( $slug );
	if ( $page ) {
		$id     = $page->ID;
		$action = 'updated';
		/* BEFORE wp_update_post(), not after. The backup used to sit further down, past this call,
		   so `post_title` and `post_status` were saved as the values this very call had just
		   written -- the meta half of that same bug was fixed earlier and this half survived it,
		   because a page rebuilt under its own name displaces a title identical to the new one and
		   nothing looks wrong. Caught by an end-to-end run on a live site where the title DID
		   change: the backup recorded the new one. Meta keys are still untouched at this point, so
		   moving the whole block up keeps them correct too. */
		es_backup_page_state(
			$id,
			array( '_elementor_data', '_wp_page_template', '_elementor_edit_mode', '_elementor_template_type', '_elementor_version' )
		);
		/* The most destructive overwrite in the repertoire, and the one the old backup covered
		   LEAST: with no `_elementor_data` to copy it returned '' and said nothing, while the
		   post_content that WAS the page stopped rendering for good. */
		$body = trim( (string) get_post_field( 'post_content', $id ) );
		if ( 'builder' !== get_post_meta( $id, '_elementor_edit_mode', true ) && '' !== $body ) {
			es_warn(
				'"' . $slug . '" (#' . $id . ') no era una pagina de Elementor y va a serlo. Su contenido actual ('
				. strlen( $body ) . ' caracteres del editor clasico o de bloques) deja de renderizarse: sigue en la base de '
				. 'datos y en el respaldo, pero el visitante ya no lo ve. Si no era la intencion, para aqui.'
			);
		}
		/* post_status intentionally mirrors what is already there - see docblock above.
		   The return value is KEPT: discarding it made this the one branch that could not fail, so
		   a post WordPress refused to touch still had its layout overwritten and still reported
		   'updated' - a write reporting success over work it did not do. */
		$wrote = wp_update_post( array( 'ID' => $id, 'post_title' => $title, 'post_status' => $page->post_status ) );
	} else {
		$action = 'created';
		$id     = wp_insert_post(
			array(
				'post_title'   => $title,
				'post_name'    => $slug,
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => '',
			)
		);
		$wrote  = $id;
	}
	if ( is_wp_error( $wrote ) || ! $wrote ) {
		/* This branch used to return 0 and say NOTHING, so a page that never got built left no
		   trace on either channel and the run still ended on a clean audit verdict - the audit only
		   ever sees the tree it was HANDED, never the write. Fails CLOSED on the update path: if
		   WordPress would not update the row, nothing authorises rewriting its design. */
		es_warn(
			'WordPress rechazo ' . ( 'updated' === $action ? 'actualizar' : 'crear' ) . ' la pagina "' . $slug . '"'
			. ( is_wp_error( $wrote ) ? ': ' . $wrote->get_error_message() : '' )
			. '. NO se escribio ningun diseño. Esa pagina no existe o quedo como estaba; el resto del build sigue.'
		);
		$action = 'failed';
		return 0;
	}

	if ( 'created' === $action ) {
		/* wp_insert_post() does not promise the slug you asked for. When one is taken - by an
		   attachment, by a post, by a reserved term - wp_unique_post_slug() appends a suffix and
		   returns happily, so asking for "contacto" published a page at "contacto-2" while $action
		   said 'created'. The page the caller believes it just built is somebody else's. */
		$real = get_post_field( 'post_name', $id );
		if ( '' !== $real && $real !== $slug ) {
			es_warn(
				'se pidio la pagina "' . $slug . '" y WordPress la creo en "' . $real . '" (#' . $id . '), porque ese slug ya estaba ocupado '
				. '(otra entrada, un adjunto o un termino reservado). La URL que esperabas NO apunta a esta pagina. '
				. 'Libera el slug y renombrala, o cambia el slug en el build.'
			);
			$action = 'created-renamed';
		}
	}

	update_post_meta( $id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $id, '_elementor_template_type', 'wp-page' );
	update_post_meta( $id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.0.0' );
	update_post_meta( $id, '_wp_page_template', $tpl );
	es_container_report( $elements, $slug );
	update_post_meta( $id, '_elementor_data', wp_slash( wp_json_encode( $elements ) ) );
	es_rebuild_css( $id );

	/* What this run actually WROTE, keyed by the slug it actually landed on — not the one that was
	   asked for. `created-renamed` is the whole reason for the distinction: recording the requested
	   slug would put a page in the manifest at a URL that answers with somebody else's. Read by
	   es_front_page_check(), and the honest source for es_manifest_record( 'pages', … ). */
	global $es_saved_pages;
	if ( ! isset( $es_saved_pages ) || ! is_array( $es_saved_pages ) ) {
		$es_saved_pages = array();
	}
	$landed = (string) get_post_field( 'post_name', $id );
	$es_saved_pages[ '' !== $landed ? $landed : $slug ] = (int) $id;

	return $id;
}

/**
 * Find a PAGE by slug — and only a page.
 *
 * `get_page_by_path( $slug, OBJECT, 'page' )` does not do what its third argument says. WordPress
 * folds attachments into that lookup, so a slug held by a media item comes back as a normal
 * result, with `post_type` = `attachment`. MEASURED on a live install, not reasoned: an attachment
 * at `nvm-solo-adjunto` was returned for a `'page'` lookup, and a page created for that same slug
 * was silently renamed `nvm-solo-adjunto-2`.
 *
 * Left unfiltered, every caller here treated that attachment as an existing page: es_save_page()
 * would take the UPDATE branch, rename the media item, write `_elementor_data` onto it and report
 * `updated` — no page created, a broken attachment, and every check green. The preflight would
 * list it as a page about to be overwritten, and the manifest would verify against it.
 *
 * So: one lookup, one type check, one place to be wrong. Callers that need "is ANYTHING holding
 * this slug" — which is a different question, because WordPress suffixes against the whole slug
 * space — must ask get_page_by_path() directly and say what they found.
 */
function es_page_by_slug( $slug ) {
	$found = get_page_by_path( $slug, OBJECT, 'page' );

	return ( $found && isset( $found->post_type ) && 'page' === $found->post_type ) ? $found : null;
}

/**
 * What is the site's front page RIGHT NOW?
 *
 * The ONE resolver. Nothing in this library may guess the home from a slug: on an install whose
 * front page is `/`, `/inicio/` is a dead link, and on an install still showing the blog there is
 * no home page at all. Both facts are only knowable from these two options.
 *
 * Two options, not one. `page_on_front` alone is NOT a front page: WordPress renders the blog
 * unless `show_on_front` is also `'page'`, so a reader that checked only the id would report a
 * front page nobody sees. Half the setting is the same as none of it.
 *
 * Returns `array( 'mode' => 'posts'|'page', 'id' => int, 'slug' => string )`.
 */
function es_front_page() {
	$mode = get_option( 'show_on_front' );
	$id   = (int) get_option( 'page_on_front' );
	if ( 'page' !== $mode || ! $id ) {
		return array(
			'mode' => 'posts',
			'id'   => 0,
			'slug' => '',
		);
	}

	return array(
		'mode' => 'page',
		'id'   => $id,
		'slug' => (string) get_post_field( 'post_name', $id ),
	);
}

/**
 * Point the site's front page at a page this build made, and PROVE it landed.
 *
 * Nothing in this framework used to touch `show_on_front` or `page_on_front` — zero occurrences
 * across the whole repository. So a home page could be built, saved, audited clean and handed over
 * while WordPress went on serving the blog at `/`: every automated check green, and the person who
 * found out was the client. That is this branch's thesis with a URL attached.
 *
 * The options are READ BACK rather than trusted. `update_option()` returns false both when the
 * write fails and when the value simply did not change, so its boolean cannot distinguish success
 * from failure in either direction; the only honest proof is asking the site what it now believes.
 *
 * Repointing an existing front page warns on purpose, naming the page that stops being shown. It
 * is not an error — it is the destructive part of the operation, and it is invisible otherwise:
 * the old home stays published, it just stops being the one anybody lands on.
 *
 * Returns the page id, or 0 when the front page is not what was asked for.
 */
function es_set_front_page( $slug ) {
	$page = es_page_by_slug( $slug );
	if ( ! $page ) {
		es_warn(
			'no existe ninguna pagina con el slug "' . $slug . '", asi que la portada NO se cambio. '
			. 'El sitio sigue mostrando lo que mostraba. Construye y guarda esa pagina antes de fijarla como portada.'
		);
		return 0;
	}
	$before = es_front_page();

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $page->ID );

	$after = es_front_page();
	if ( 'page' !== $after['mode'] || (int) $page->ID !== $after['id'] ) {
		es_warn(
			'se pidio poner "' . $slug . '" (#' . $page->ID . ') como portada, pero al releer las opciones el sitio '
			. ( 'posts' === $after['mode'] ? 'sigue mostrando las entradas' : 'muestra la pagina #' . $after['id'] ) . '. '
			. 'La escritura se acepto y no quedo: revisa permisos, un plugin que filtre la opcion, o una cache de opciones.'
		);
		return 0;
	}
	if ( $before['id'] && $before['id'] !== (int) $page->ID ) {
		es_warn(
			'la portada del sitio era "' . ( '' !== $before['slug'] ? $before['slug'] : '#' . $before['id'] ) . '" y ahora es "' . $slug . '". '
			. 'La anterior sigue publicada, solo deja de ser la que se ve al entrar. Si no era la intencion, esto es lo que hay que revertir.'
		);
	}

	return (int) $page->ID;
}

/**
 * Park EVERYTHING an overwrite is about to displace, in one timestamped backup meta key.
 *
 * Elementor stores a layout as one blob of post meta, so rewriting it through the API destroys
 * the previous design outright: no revision, no diff, nothing to roll back to. Copying the old
 * state aside first is the cheapest thing that makes an accidental overwrite recoverable.
 *
 * This used to copy `_elementor_data` and nothing else, while the caller also rewrote the page
 * template, the edit mode, the template type and the version -- so restoring the layout onto a
 * template that had silently changed did NOT put the page back the way it was. The docblock said
 * "the previous layout", which was true and far narrower than the damage. It now takes the exact
 * key list the caller is about to write, plus the post fields `wp_update_post()` touches, and
 * `post_content`, which is not displaced but STOPS BEING RENDERED the moment a classic page
 * becomes an Elementor one.
 *
 * Must be called BEFORE the first write. It cannot tell an old value from a new one.
 *
 * Backup key: `_es_page_backup_<Ymd-His>` (UTC), holding an array keyed by what it saved. Restore
 * by hand, key by key: meta keys go back through `update_post_meta()` (`_elementor_data` needs
 * `wp_slash()`), post fields through `wp_update_post()`. The leading underscore keeps backups out
 * of the custom-fields UI; they are never pruned, so a long-lived page accumulates one per
 * rebuild on purpose.
 *
 * Returns the key written, or '' when there was genuinely nothing to preserve.
 */
/**
 * Put a page back the way a backup found it — and prove each piece landed.
 *
 * The backups were handed over as a list of keys and a sentence telling a human to restore them by
 * hand, key by key, remembering that `_elementor_data` needs `wp_slash()` and that the CSS has to
 * be regenerated afterwards. That is a recovery procedure nobody executes correctly at the moment
 * they need it, which is the moment something already went wrong. A backup nobody can restore is
 * not a backup.
 *
 * Restoring is itself destructive, so it BACKS UP FIRST: the state it is about to overwrite gets
 * its own timestamped key, and a restore aimed at the wrong page or the wrong moment is recoverable
 * exactly like the write that caused it. Yes, that means restoring twice returns you to where you
 * started rather than stranding you.
 *
 * Every piece is READ BACK. `update_post_meta()` and `update_option()` share the same useless
 * boolean — false on failure and false on an unchanged value — and `wp_update_post()` can be
 * filtered out from under you. So this reports what it VERIFIED, never what it attempted, and a
 * partial restore says which parts are missing instead of returning a cheerful true.
 *
 * `$key` empty picks the NEWEST backup, which is the one a human means when they say "undo that".
 *
 * Returns `array( 'key' => string, 'restored' => array, 'failed' => array, 'safety' => string )`,
 * or an empty `restored` with a warning when there is nothing to restore.
 */
function es_restore_page_state( $post_id, $key = '' ) {
	$post_id = (int) $post_id;
	$all     = es_backup_keys( array( $post_id ) );
	$keys    = isset( $all[ $post_id ] ) ? $all[ $post_id ] : array();

	if ( ! $keys ) {
		es_warn( 'la pagina #' . $post_id . ' no tiene ningun respaldo guardado, asi que no hay nada que restaurar.' );
		return array(
			'key'      => '',
			'restored' => array(),
			'failed'   => array(),
			'safety'   => '',
		);
	}
	if ( '' === $key ) {
		$key = end( $keys );   /* es_backup_keys() sorts, so the last one is the newest */
	}
	if ( ! in_array( $key, $keys, true ) ) {
		es_warn(
			'la pagina #' . $post_id . ' no tiene ningun respaldo llamado "' . $key . '". Los que tiene son: '
			. implode( ', ', $keys ) . '. No se restauro nada.'
		);
		return array(
			'key'      => '',
			'restored' => array(),
			'failed'   => array(),
			'safety'   => '',
		);
	}

	$state = get_post_meta( $post_id, $key, true );
	if ( ! is_array( $state ) || ! $state ) {
		es_warn( 'el respaldo "' . $key . '" de la pagina #' . $post_id . ' esta vacio o no tiene la forma esperada. No se restauro nada.' );
		return array(
			'key'      => '',
			'restored' => array(),
			'failed'   => array(),
			'safety'   => '',
		);
	}

	/* Restoring overwrites. Park the CURRENT state first, using the same key list this backup
	   holds, so undoing an undo is possible. */
	$meta_keys = array();
	foreach ( array_keys( $state ) as $k ) {
		if ( 0 === strpos( (string) $k, '_' ) ) {
			$meta_keys[] = $k;
		}
	}
	$safety = es_backup_page_state( $post_id, $meta_keys );

	$restored = array();
	$failed   = array();
	$fields   = array();

	foreach ( $state as $k => $value ) {
		if ( 0 === strpos( (string) $k, '_' ) ) {
			/* `_elementor_data` is stored slashed; everything else round-trips as it is. */
			update_post_meta( $post_id, $k, '_elementor_data' === $k ? wp_slash( $value ) : $value );
			continue;
		}
		$fields[ $k ] = $value;
	}
	if ( $fields ) {
		$fields['ID'] = $post_id;
		wp_update_post( $fields );
	}

	/* The read-back. Nothing above is trusted. */
	foreach ( $state as $k => $value ) {
		$now = ( 0 === strpos( (string) $k, '_' ) )
			? get_post_meta( $post_id, $k, true )
			: get_post_field( $k, $post_id );
		if ( (string) $now === (string) $value ) {
			$restored[] = $k;
		} else {
			$failed[] = $k;
		}
	}

	es_rebuild_css( $post_id );

	if ( $failed ) {
		es_warn(
			'la restauracion de la pagina #' . $post_id . ' desde "' . $key . '" quedo A MEDIAS: '
			. implode( ', ', $failed ) . ' no coincide al releerlo. La pagina esta ahora en un estado mezclado, '
			. ( '' !== $safety ? 'y el estado previo quedo en "' . $safety . '".' : 'y no se pudo guardar el estado previo.' )
		);
	}

	return array(
		'key'      => $key,
		'restored' => $restored,
		'failed'   => $failed,
		'safety'   => $safety,
	);
}

/**
 * Keep the newest N backups of a page and delete the rest.
 *
 * Backups are never pruned by design — a long-lived page accumulates one per rebuild, and each one
 * now holds the whole displaced state rather than a single blob, so they are bigger than they used
 * to be. That is the right default (losing the one you needed costs more than the rows), but it
 * cannot be the ONLY option or the meta table becomes the thing that breaks.
 *
 * Deletes oldest-first and READS BACK: `delete_post_meta()` returns false both when it failed and
 * when there was nothing there, so the proof is the re-read, exactly as with the sandbox purge.
 *
 * Returns `array( 'kept' => array, 'deleted' => array, 'still_there' => array )`.
 */
function es_prune_backups( $post_id, $keep = 5 ) {
	$post_id = (int) $post_id;
	$keep    = max( 1, (int) $keep );   /* keeping zero is not pruning, it is deleting the backups */
	$all     = es_backup_keys( array( $post_id ) );
	$keys    = isset( $all[ $post_id ] ) ? $all[ $post_id ] : array();

	if ( count( $keys ) <= $keep ) {
		return array(
			'kept'        => $keys,
			'deleted'     => array(),
			'still_there' => array(),
		);
	}
	$drop = array_slice( $keys, 0, count( $keys ) - $keep );   /* sorted: oldest first */
	$kept = array_slice( $keys, count( $keys ) - $keep );

	foreach ( $drop as $k ) {
		delete_post_meta( $post_id, $k );
	}

	$after = es_backup_keys( array( $post_id ) );
	$now   = isset( $after[ $post_id ] ) ? $after[ $post_id ] : array();
	$stuck = array_values( array_intersect( $drop, $now ) );

	if ( $stuck ) {
		es_warn(
			'no se pudieron borrar ' . count( $stuck ) . ' respaldos de la pagina #' . $post_id . ': '
			. implode( ', ', $stuck ) . '. Siguen ocupando sitio en la tabla de meta.'
		);
	}

	return array(
		'kept'        => $kept,
		'deleted'     => array_values( array_diff( $drop, $now ) ),
		'still_there' => $stuck,
	);
}

function es_backup_page_state( $post_id, array $meta_keys ) {
	$state = array();
	foreach ( $meta_keys as $key ) {
		$value = get_post_meta( $post_id, $key, true );
		if ( '' !== $value && array() !== $value && '[]' !== $value ) {
			$state[ $key ] = $value;
		}
	}
	foreach ( array( 'post_title', 'post_status', 'post_content' ) as $field ) {
		$value = (string) get_post_field( $field, $post_id );
		if ( '' !== $value ) {
			$state[ $field ] = $value;
		}
	}
	if ( ! $state ) {
		return '';
	}
	$key = '_es_page_backup_' . gmdate( 'Ymd-His' );
	update_post_meta( $post_id, $key, $state );

	return $key;
}

/**
 * Settings written on the wrong element type, which Elementor accepts and then ignores.
 *
 * Elementor names the same visual control differently depending on where it lives: a CONTAINER
 * takes `padding`, a WIDGET takes `_padding`, because on a widget those are wrapper ("advanced")
 * controls and carry an underscore. Write the container form on a widget and the JSON saves, the
 * editor opens, the page renders — and the padding is simply not there. Nothing errors. It is the
 * quietest failure in the whole library: the setting is visible in the source and absent on screen,
 * so it gets re-added, re-saved and re-wondered-at.
 *
 * MEASURED on Elementor 4.2.2, not reasoned. A page was built with paired elements and its CSS
 * regenerated through `\Elementor\Core\Files\CSS\Post`, then read back:
 *
 *   widget with `_padding: 33px`     -> `.elementor-element-wbajo001{padding:33px 33px 33px 33px;}`
 *   widget with `padding: 44px`      -> NO rule at all; "44px" appears nowhere in the file
 *   container with `padding: 55px`   -> `--padding-top:55px; …`
 *   container with `_padding: 66px`  -> NO rule; "66px" appears nowhere
 *   widget with `flex_direction:row` -> NO rule at all
 *
 * So the wrong form is not merely ignored at render time: it never reaches the stylesheet. Nothing
 * warns, nothing errors, and the JSON keeps the setting forever.
 *
 * The key list is deliberately SHORT. `width` is the reason for that caution: it is a container
 * layout key AND a genuine control on several widgets, so flagging it would invent offenders on
 * correct code — mutation proves it, by breaking three existing assertions. A check that cries wolf
 * is one people learn to skip, which is the failure this repo exists to remove, so when in doubt a
 * key stays off the list and the gap is real rather than papered over.
 *
 * Reports, never blocks, through the same offender channel as everything else in the walk.
 *
 * `$widget_type` is the widget's own name, and it exists because the first version of this check
 * invented offenders on TEN widget types. Introspecting all 128 that expose controls showed
 * `padding` is a real dimensions control on three of them and `background_background` a real choose
 * control on seven — so the rule "on a widget it must carry the underscore" was telling their
 * authors to break working code. See es_owns_control().
 */
function es_key_offenders( $type, array $settings, $widget_type = '' ) {
	/* Container-only layout keys: a widget has no flex box of its own to configure. Re-measured
	   across all 128 widget types that expose controls on Elementor 4.2.2 + Pro 4.2.1: not one of
	   these five is a real control on any of them, in either spelling. */
	$container_only = array( 'content_width', 'flex_direction', 'flex_gap', 'flex_justify_content', 'flex_align_items' );
	/* Wrapper controls, spelled bare on a container and underscored on a widget. */
	$wrapper = array( 'padding', 'margin', 'background_background' );

	$out = array();
	if ( 'widget' === $type ) {
		foreach ( $container_only as $key ) {
			if ( isset( $settings[ $key ] ) ) {
				$out[] = 'lleva "' . $key . '", que es una clave de CONTENEDOR: Elementor la guarda y no la aplica. Ponla en el contenedor padre';
			}
		}
		foreach ( $wrapper as $key ) {
			if ( isset( $settings[ $key ] ) && ! isset( $settings[ '_' . $key ] ) && ! es_owns_control( $widget_type, $key ) ) {
				$out[] = 'lleva "' . $key . '" sin guion bajo: en un widget esa clave es "_' . $key . '" y sin el no hace nada';
			}
		}
	} elseif ( 'container' === $type ) {
		foreach ( $wrapper as $key ) {
			if ( isset( $settings[ '_' . $key ] ) && ! isset( $settings[ $key ] ) ) {
				$out[] = 'lleva "_' . $key . '" con guion bajo: en un contenedor esa clave es "' . $key . '" y con el no hace nada';
			}
		}
	}

	return $out;
}

/**
 * Does this widget type own `$key` as a control of its own?
 *
 * The wrapper rule — "a container takes `padding`, a widget takes `_padding`" — is true of the
 * wrapper controls every widget inherits, and FALSE wherever a widget defines a control of its own
 * under the bare name. Measured by walking all 128 widget types that expose controls on Elementor
 * 4.2.2 + Pro 4.2.1 and asking each one, not by reading documentation: `padding` is a real
 * dimensions control on three, `background_background` a real choose control on seven. Without this
 * the checker invented an offender on ten widget types and told the author to "fix" code that was
 * already right, and an invented offender costs more than a missed one — the same reasoning that
 * keeps `width` off the list entirely, since ten widgets own that one for real.
 *
 * Asks ELEMENTOR when Elementor is there, because a hardcoded roster goes stale on the next release
 * and goes stale silently. The measured list below is the fallback for the offline suite, where
 * there is no Elementor to ask; it is short, dated and derived, never guessed.
 *
 * A type Elementor does not recognise returns no controls, and that is treated as "does not own it"
 * — failing towards reporting. An unregistered widget renders empty anyway, and the walk already
 * has its own row for that. An element with no `widgetType` reaches here as `''` and takes the same
 * path: MEASURED on 4.2.2, `get_widget_types('')` returns NULL and `get_widget_types(null)` returns
 * all 130, so the empty string is safe and only a null argument would be dangerous — the call site
 * passes `''`, never null. An early return for `''` was written first and removed: it changed no
 * outcome, so it was a branch nothing could test, which is how a check quietly stops checking.
 */
function es_owns_control( $widget_type, $key ) {
	static $live = array();
	if ( class_exists( '\Elementor\Plugin' ) ) {
		if ( ! isset( $live[ $widget_type ] ) ) {
			$w                    = \Elementor\Plugin::instance()->widgets_manager->get_widget_types( $widget_type );
			$live[ $widget_type ] = ( $w && method_exists( $w, 'get_controls' ) ) ? (array) $w->get_controls() : array();
		}

		/* Elementor is HERE, so Elementor is the answer — including when it answers "no such widget",
		   which used to fall through to the list below. That fall-through was the only path on which
		   a version-pinned roster could still decide anything on a live site, and a roster whose
		   staleness is guarded by a sentence in a docblock is the exact failure this file spent the
		   day removing. An unregistered type owns no controls; it renders empty, and the walk has
		   its own row for that. */
		return isset( $live[ $widget_type ][ $key ] );
	}
	/* Reached ONLY when Elementor is absent entirely — the offline suite, or a tree audited outside
	   WordPress. MEASURED on Elementor 4.2.2 / Pro 4.2.1; re-run the introspection when either
	   moves. It can no longer go stale behind a live site's back, because a live site never asks it. */
	$measured = array(
		'padding'               => array( 'nested-tabs', 'call-to-action', 'table-of-contents' ),
		'background_background' => array( 'button', 'archive-posts', 'loop-grid', 'off-canvas', 'posts', 'paypal-button', 'stripe-button' ),
	);

	return isset( $measured[ $key ] ) && in_array( $widget_type, $measured[ $key ], true );
}

/**
 * The project manifest: what this framework knows about THIS site, between sessions.
 *
 * Nothing persisted anything. Every session re-derived the builder, the page ids, the slugs and
 * what had already been approved by asking again or by guessing, which is how the same page gets
 * rebuilt twice and how a second session overwrites what a first one agreed not to touch.
 *
 * It lives in a WordPress option and NOT in a file next to this library, for one reason: the
 * library is uploaded to a sandbox that the delivery phase deletes. State that dies with the
 * sandbox is not state. The option travels with the site, which is the only thing both sessions
 * are looking at.
 *
 * Shape: `array( 'schema' => 1, 'updated' => 'Ymd-His', 'sections' => array( name => array(
 * 'at' => 'Ymd-His', 'data' => array( … ) ) ) )`. Sections are namespaced per concern (`site`,
 * `design`, `pages`, `delivery`) so two skills writing different things never overwrite each
 * other's, which a flat map guarantees they eventually will.
 */
function es_manifest_read() {
	$raw = get_option( 'es_novamira_manifest' );
	if ( ! is_array( $raw ) || ! isset( $raw['sections'] ) || ! is_array( $raw['sections'] ) ) {
		return array(
			'schema'   => 1,
			'updated'  => '',
			'sections' => array(),
		);
	}

	return $raw;
}

/**
 * Record one section, stamped, and READ IT BACK.
 *
 * Merges into the named section only — never the whole manifest — and returns false when the
 * write did not land. `update_option()` returns false both on failure and on an unchanged value,
 * so its boolean is not evidence in either direction; the re-read is.
 */
function es_manifest_record( $section, array $data ) {
	$manifest = es_manifest_read();
	$stamp    = gmdate( 'Ymd-His' );

	$manifest['schema']               = 1;
	$manifest['updated']              = $stamp;
	$manifest['sections'][ $section ] = array(
		'at'   => $stamp,
		'data' => $data,
	);
	update_option( 'es_novamira_manifest', $manifest );

	$back = es_manifest_read();
	if ( ! isset( $back['sections'][ $section ]['data'] ) || $back['sections'][ $section ]['data'] !== $data ) {
		es_warn(
			'el manifiesto NO se guardo: la seccion "' . $section . '" no esta o no coincide al releerla. '
			. 'La proxima sesion va a empezar sin saber nada de esta. Revisa permisos o una cache de opciones.'
		);
		return false;
	}

	return true;
}

/**
 * Check the manifest against the site it claims to describe.
 *
 * A manifest nobody checks is a memory that lies. Between two sessions a page can be deleted,
 * renamed by hand, replaced by a plugin import, or repointed as the front page — and a second
 * session trusting the recorded ids would write into whatever now sits there.
 *
 * Reads the `pages` section (`slug => post_id`) and the `front_page` id, and reports DRIFT rather
 * than repairing it: repairing would mean guessing which of the two truths is the intended one,
 * and the whole point is that only a human knows.
 *
 * The lines state what was OBSERVED and never why. An earlier version of the first one said
 * "somebody moved it outside this framework", which is a cause, and a live test proved it wrong the
 * first time it fired: the manifest had a key that was not a slug at all (`front` written into the
 * `pages` map, where the front page does not belong), and nobody had moved anything. A report whose
 * whole rule is "never claim what you did not read" cannot afford a confident wrong diagnosis in
 * its own rows — the reader who believes it goes hunting for an edit that never happened. So the
 * row gives the two facts side by side and leaves the inference where it belongs.
 *
 * Returns a list of human-readable drift lines, empty when the manifest still matches.
 */
function es_manifest_verify() {
	$manifest = es_manifest_read();
	$pages    = isset( $manifest['sections']['pages']['data'] ) ? (array) $manifest['sections']['pages']['data'] : array();
	$drift    = array();

	foreach ( $pages as $slug => $id ) {
		$id   = (int) $id;
		$page = es_page_by_slug( $slug );
		if ( ! $page ) {
			$real = (string) get_post_field( 'post_name', $id );
			$drift[] = '' !== $real
				? 'el manifiesto llama "' . $slug . '" a la pagina #' . $id . ', y el slug vivo de esa pagina es "' . $real
					. '". Los dos hechos, sin conclusion: puede que la renombraran fuera de este framework, o que lo anotado nunca fuera un slug'
				: 'la pagina "' . $slug . '" (#' . $id . ') ya no existe: borrada o en la papelera desde la ultima sesion';
			continue;
		}
		if ( (int) $page->ID !== $id ) {
			$drift[] = '"' . $slug . '" ahora es la pagina #' . $page->ID . ', no la #' . $id . ' que dice el manifiesto: la de antes se borro y se creo otra en su lugar';
		}
	}

	$recorded_front = isset( $manifest['sections']['site']['data']['front_page_id'] )
		? (int) $manifest['sections']['site']['data']['front_page_id']
		: null;
	if ( null !== $recorded_front ) {
		$live = es_front_page();
		if ( $live['id'] !== $recorded_front ) {
			$drift[] = 'la portada era la #' . $recorded_front . ' y ahora ' . ( $live['id'] ? 'es la #' . $live['id'] : 'el sitio muestra el blog' );
		}
	}

	if ( $drift ) {
		es_warn(
			'el manifiesto ya no describe este sitio (' . count( $drift ) . '): ' . implode( ' | ', $drift )
			. '. NO se corrige solo a proposito: solo un humano sabe cual de las dos versiones es la buena.'
		);
	}

	return $drift;
}

/**
 * Where the sandbox lives. One definition, so nothing hand-builds this path.
 *
 * Every `.php` dropped in here EXECUTES on upload — that is how this framework runs at all, and
 * it is also why leaving files behind matters: they stay executable on the client's site forever,
 * reachable by anyone who guesses the URL, long after anybody remembers writing them.
 */
function es_sandbox_dir() {
	return ( defined( 'WP_CONTENT_DIR' ) ? WP_CONTENT_DIR : '' ) . '/novamira-sandbox';
}

/**
 * Is the sandbox actually RUNNING, or has it switched itself off?
 *
 * Read from the Novamira loader's own source, not guessed. It globs `*.php` in the sandbox and
 * `require_once`s every one of them on EVERY request — not "on upload", which is what this repo's
 * gotchas used to say. But before that it does:
 *
 *     $is_safe_mode = file_exists( $crashed_file );
 *     if ( $is_safe_mode ) { return; }
 *
 * So a single `.crashed` file disables the WHOLE sandbox, silently. The only notice is an
 * admin_notices banner, which needs a logged-in manager looking at wp-admin — an agent working
 * through the connector never sees it.
 *
 * Measured on two live sites: one carrying `.crashed` since a fatal, where NO `es_*` function was
 * defined at all, and one without it, where the library loaded normally. On the first, every build
 * this framework performs would die on "undefined function" with nothing explaining why.
 *
 * The catch worth stating: when the sandbox IS in safe mode, this function is not loaded either, so
 * it cannot be the thing that warns you. `project-context` reads the same file directly, before any
 * of this library exists. This one covers the case where the library is running and the crash
 * happened afterwards — and the delivery phase, which must not hand over a site whose sandbox is
 * quietly off.
 *
 * Returns `array( 'safe_mode' => bool, 'reason' => string|null, 'files' => array )`.
 */
function es_sandbox_state() {
	$dir     = es_sandbox_dir();
	$crashed = $dir . '/.crashed';
	$reason  = null;

	if ( file_exists( $crashed ) ) {
		$raw = (string) @file_get_contents( $crashed );
		$rec = json_decode( $raw, true );
		if ( is_array( $rec ) ) {
			$reason = ( isset( $rec['sandbox_file'] ) ? basename( (string) $rec['sandbox_file'] ) . ': ' : '' )
				. ( isset( $rec['message'] ) ? substr( (string) $rec['message'], 0, 200 ) : 'sin mensaje' );
		} else {
			$reason = '' !== $raw ? substr( $raw, 0, 200 ) : 'el fichero .crashed esta vacio';
		}
		es_warn(
			'el sandbox esta en MODO SEGURO: existe ' . $dir . '/.crashed, asi que el cargador de Novamira NO carga '
			. 'NINGUN fichero del sandbox. Causa registrada: ' . $reason . '. Nada de lo que subas se va a ejecutar. '
			. 'Arregla o borra el fichero culpable ANTES de borrar .crashed — borrarlo sin mas vuelve a cargarlo y a tumbar el sitio otra vez.'
		);
	}

	return array(
		'safe_mode' => null !== $reason,
		'reason'    => $reason,
		'files'     => es_sandbox_report(),
	);
}

/**
 * What is still sitting in the sandbox?
 *
 * Returns a sorted list of filenames, `array()` when empty or absent. This is the READ that makes
 * "I cleaned up" checkable: the delivery phase must show what remains, not assert it is nothing.
 */
function es_sandbox_report() {
	$dir = es_sandbox_dir();
	if ( ! is_dir( $dir ) ) {
		return array();
	}
	$left = array();
	foreach ( (array) scandir( $dir ) as $entry ) {
		if ( '.' === $entry || '..' === $entry ) {
			continue;
		}
		$left[] = $entry;
	}
	sort( $left );

	return $left;
}

/**
 * Delete the build scripts from the sandbox, then READ IT BACK and report what survived.
 *
 * The audit's one security finding was that this directory is never cleaned. Everything this
 * framework uploads is executable PHP on a live site: helper libraries, page builders, whatever
 * was pasted in to debug something at 2am. None of it is needed once the pages exist.
 *
 * The return value is what is STILL THERE after the attempt, never what was deleted, because a
 * delete that silently failed on a permissions error and a delete that worked look identical from
 * the return of unlink() alone. An empty array is the only proof of an empty sandbox.
 *
 * Scoped hard on purpose: only regular files DIRECTLY inside the sandbox, only when realpath()
 * still resolves inside it, and only the extensions this framework uploads. It never recurses,
 * never follows a link out, and never removes the directory itself.
 */
function es_sandbox_purge() {
	$dir  = es_sandbox_dir();
	$real = realpath( $dir );
	if ( ! $real || ! is_dir( $real ) ) {
		return array();
	}
	foreach ( es_sandbox_report() as $entry ) {
		$path = $real . DIRECTORY_SEPARATOR . $entry;
		$rp   = realpath( $path );
		if ( ! $rp || 0 !== strpos( $rp, $real . DIRECTORY_SEPARATOR ) || ! is_file( $rp ) ) {
			continue;   /* a link pointing out, or a subdirectory: not ours to touch */
		}
		$ext = strtolower( pathinfo( $rp, PATHINFO_EXTENSION ) );
		if ( ! in_array( $ext, array( 'php', 'log', 'txt', 'json' ), true ) ) {
			continue;
		}
		if ( es_sandbox_runtime_hooks( $rp ) ) {
			continue;   /* not scaffolding: it runs on every visit. See below. */
		}
		@unlink( $rp );
	}
	clearstatcache();
	$left = es_sandbox_report();
	if ( $left ) {
		$live = array();
		foreach ( $left as $entry ) {
			$hooks = es_sandbox_runtime_hooks( $real . DIRECTORY_SEPARATOR . $entry );
			if ( $hooks ) {
				$live[] = $entry . ' (' . implode( ', ', $hooks ) . ')';
			}
		}
		es_warn(
			'el sandbox NO quedo vacio: siguen ahi ' . implode( ', ', $left ) . '. Todo .php que quede en '
			. 'wp-content/novamira-sandbox/ se ejecuta y es alcanzable por URL en el sitio del cliente. '
			. 'Borralos a mano antes de entregar.'
			. ( $live
				? ' OJO, esto NO se borro a proposito porque registra hooks de WordPress y por tanto corre en cada '
					. 'visita, no es andamio de build: ' . implode( '; ', $live ) . '. Borrarlo cambia el sitio. '
					. 'Muevelo al tema hijo y borralo de aqui despues, nunca antes.'
				: '' )
		);
	}

	return $left;
}

/**
 * Does this sandbox file register WordPress hooks — that is, does it RUN on every visit?
 *
 * The purge deletes what this framework uploads, and until now "what this framework uploads" was
 * assumed to be build scaffolding: helper libraries and page builders, all of them useless once
 * the pages exist. Found on a real client site while cleaning one: `es-dlo-a11y.php` registered
 * `template_redirect` and wrapped every page in a `<main>` landmark, because the theme prints
 * none. It is not scaffolding, it is the site's accessibility, living in the one directory whose
 * job is to empty itself — and the delivery phase would have deleted it on hand-off day, silently,
 * with every check green. That is this branch's thesis with a screen reader attached.
 *
 * So a file that hooks joins the list the purge already refuses to touch, next to subdirectories
 * and unknown extensions: it still BLOCKS delivery, it just needs a human. The right fix is always
 * to move it into the child theme — this framework may not write PHP outside the sandbox — and
 * delete it here afterwards, never before.
 *
 * Detected by reading the source, not by loading it: loading is what the sandbox already does on
 * every request and re-running it here would be a side effect inside a report. Comment lines are
 * skipped, the same rule the audit uses, so a docblock explaining a hook that was removed does not
 * keep a dead file alive forever.
 *
 * Returns the hook names found, so the warning can NAME them; an empty array means safe to delete.
 */
function es_sandbox_runtime_hooks( $path ) {
	$src = is_file( $path ) ? (string) @file_get_contents( $path ) : '';
	if ( '' === $src ) {
		return array();
	}
	$found = array();
	foreach ( explode( "\n", str_replace( "\r\n", "\n", $src ) ) as $line ) {
		if ( preg_match( '#^\s*(\*|//|/\*|\#)#', $line ) ) {
			continue;
		}
		if ( preg_match_all( '/(?<![\w>$])(add_action|add_filter|register_activation_hook|register_shutdown_function)\s*\(\s*[\'"]([^\'"]+)/', $line, $m ) ) {
			foreach ( $m[2] as $hook ) {
				$found[ $hook ] = true;
			}
		}
	}

	return array_keys( $found );
}

/**
 * Every backup key this framework has parked on a set of posts.
 *
 * Handing these over is the difference between "there is a backup" and "here is how to restore
 * it". They are never pruned, so a long-lived page has one per rebuild, newest last.
 *
 * Returns `array( post_id => array( key, … ) )`, omitting posts with none.
 */
function es_backup_keys( array $post_ids ) {
	$out = array();
	foreach ( $post_ids as $id ) {
		$keys = array();
		foreach ( (array) get_post_meta( $id ) as $key => $value ) {
			if ( 0 === strpos( (string) $key, '_es_page_backup_' ) ) {
				$keys[] = $key;
			}
		}
		if ( $keys ) {
			sort( $keys );
			$out[ (int) $id ] = $keys;
		}
	}

	return $out;
}

/**
 * Does WordPress currently allow this site to be indexed?
 *
 * `blog_public` = 0 is the "discourage search engines" switch. Staging sites are built with it on
 * and nobody remembers to turn it off, so the site is delivered looking perfect and stays invisible
 * for weeks. It is one option and it decides whether any of the SEO work matters.
 *
 * Scope, stated rather than implied: this reads THAT OPTION and nothing else. It reports whether a
 * PHYSICAL robots.txt exists next to WordPress, because that file overrides the virtual one, but it
 * does NOT parse it — deciding what a robots file permits means honouring user-agent groups,
 * wildcards and Allow precedence, and a half-parser here would be a confident wrong answer. A
 * virtual robots.txt (WordPress's own, or a plugin's) is invisible from disk entirely. Fetching
 * `/robots.txt` over HTTP and reading it is `qa-review`'s job, not this function's.
 *
 * Returns `array( 'indexable' => bool, 'blog_public' => mixed, 'robots_file' => string|null )`,
 * where `robots_file` is the file's contents when one exists on disk.
 */
function es_indexing_state() {
	$blog_public = get_option( 'blog_public' );
	$robots      = null;
	$path        = ( defined( 'ABSPATH' ) ? ABSPATH : '' ) . 'robots.txt';
	if ( '' !== $path && file_exists( $path ) ) {
		$robots = (string) file_get_contents( $path );
	}

	return array(
		'indexable'   => ( '0' !== (string) $blog_public && '' !== (string) $blog_public ),
		'blog_public' => $blog_public,
		'robots_file' => $robots,
	);
}

/**
 * Move a page from one slug to another, and record where the old URL went.
 *
 * A rebuild that "renames" a page did neither of the two things a rename needs. Building the new
 * page at the new slug leaves the OLD page published and indexed: Google now has both, they
 * compete, and the stale one often wins because it has the history. Changing the slug in place
 * instead makes every existing inbound link — search results, the client's own printed material,
 * another site's link — 404 with nothing to follow.
 *
 * So this moves the page rather than duplicating it, VERIFIES the slug actually moved, and stores
 * the old→new pair in the `es_slug_redirects` option.
 *
 * Read this next part before trusting it: **nothing in this framework serves that option.** There
 * is no mu-plugin, no `template_redirect` hook, no rewrite rule — a grep for every redirect helper
 * across this repo returns nothing, which is exactly why the audit called slug hygiene missing.
 * The map is the record a redirect plugin or a snippet can be pointed at, and `qa-review` row 17
 * checks the old URLs against it. Until something reads it, the old URL still 404s, and this
 * function says so out loud on every successful move rather than letting a stored map read as a
 * working redirect. A half-measure that announces itself is worth having; one that does not is
 * the failure this whole branch exists to remove.
 *
 * Returns the page id on a completed move, 0 otherwise.
 */
function es_migrate_slug( $from, $to ) {
	if ( $from === $to ) {
		return 0;
	}
	$page = es_page_by_slug( $from );
	if ( ! $page ) {
		/* Not an error, but not silence either: a migration that finds nothing is almost always a
		   typo in the OLD slug, and a quiet no-op passes for a completed move. */
		es_warn(
			'no hay ninguna pagina en "' . $from . '", asi que no se movio nada. Si esperabas moverla, revisa el slug de origen: '
			. 'un slug mal escrito aqui no falla, simplemente no hace nada.'
		);
		return 0;
	}
	/* Deliberately NOT es_page_by_slug(): the question here is "is ANYTHING holding this slug",
	   which is a different one. WordPress makes a slug unique against the whole space, so an
	   attachment is as much of a blocker as a page — measured on a live install, where a page
	   asking for a slug an attachment held came back renamed with a "-2" suffix. The type is named
	   in the warning because "la pagina #732" for a media item sends the reader hunting in the
	   wrong list. */
	$taken = get_page_by_path( $to, OBJECT, 'page' );
	if ( $taken && (int) $taken->ID !== (int) $page->ID ) {
		$que = isset( $taken->post_type ) && 'page' !== $taken->post_type ? $taken->post_type : 'pagina';
		es_warn(
			'"' . $to . '" ya lo ocupa ' . ( 'pagina' === $que ? 'la pagina' : 'un elemento de tipo "' . $que . '"' ) . ' #' . $taken->ID
			. ', asi que "' . $from . '" (#' . $page->ID . ') NO se movio. '
			. 'Mover encima habria dejado dos cosas peleando por la misma URL y WordPress renombrando una de ellas a lo que le pareciera. '
			. 'Decide cual sobrevive y borra o mueve la otra a mano.'
		);
		return 0;
	}
	$wrote = wp_update_post(
		array(
			'ID'        => $page->ID,
			'post_name' => $to,
		)
	);
	$real = get_post_field( 'post_name', $page->ID );
	if ( is_wp_error( $wrote ) || ! $wrote || $real !== $to ) {
		es_warn(
			'no se pudo mover "' . $from . '" (#' . $page->ID . ') a "' . $to . '"'
			. ( is_wp_error( $wrote ) ? ': ' . $wrote->get_error_message() : '' )
			. '. El slug sigue siendo "' . $real . '". No se registro ninguna redireccion.'
		);
		return 0;
	}

	$map = get_option( 'es_slug_redirects' );
	if ( ! is_array( $map ) ) {
		$map = array();
	}
	$map[ $from ] = $to;
	update_option( 'es_slug_redirects', $map );

	$check = get_option( 'es_slug_redirects' );
	if ( ! is_array( $check ) || ! isset( $check[ $from ] ) || $check[ $from ] !== $to ) {
		es_warn(
			'la pagina se movio a "' . $to . '" pero el mapa de redirecciones no quedo escrito, asi que ni siquiera hay registro de '
			. 'que "' . $from . '" existio. Apuntalo a mano antes de que se pierda.'
		);
		return (int) $page->ID;
	}

	/* On every successful move, without exception. The day something serves the map, this is the
	   line that has to change, and it is easier to find than a silence. */
	es_warn(
		'"' . $from . '" se movio a "' . $to . '" y quedo anotado en la opcion es_slug_redirects. AVISO: nada en este framework '
		. 'SIRVE ese mapa todavia, asi que /' . $from . '/ sigue devolviendo 404 para quien llegue desde Google o desde un enlace viejo. '
		. 'Este framework NO PUEDE cerrarlo solo: no le esta permitido escribir .php fuera del sandbox, y el sandbox se vacia al '
		. 'entregar. Lo cierra una persona, de dos maneras: un plugin de redirecciones, o el mu-plugin de 15 lineas que lee esta '
		. 'misma opcion, copiado tal cual de elementor-core/references/knowledge.md ("Servir es_slug_redirects"). '
		. 'Comprobalo despues con la fila 17 de qa-review, que hasta entonces FALLA a proposito.'
	);

	return (int) $page->ID;
}

/**
 * Cross the slugs a build is about to write against what is already on the site.
 *
 * Nothing did this. The build discovered an existing page by trying to overwrite it, which means
 * the first time anybody learned that `/inicio` already belonged to somebody was after it had
 * stopped belonging to them. This is the report a human approves BEFORE the connector is handed
 * a single write.
 *
 * It prints as well as returning, and its printing is NOT gated on `ES_AUDIT_SILENT`: that switch
 * mutes the routine container report, and an approval artifact is not routine output.
 *
 * Returns `array( 'rows' => [...], 'overwrites' => int, 'creates' => int )`. Each row carries
 * `slug`, `id`, `action` (`create`|`overwrite`), `status`, `is_elementor`, `is_front_page` and
 * `converts` -- the last two being the ones that cost the most and show up the least.
 */
function es_overwrite_preflight( array $slugs ) {
	$front = es_front_page();
	$rows  = array();
	$over  = 0;
	$make  = 0;

	foreach ( $slugs as $slug ) {
		$page = es_page_by_slug( $slug );
		if ( ! $page ) {
			$rows[] = array(
				'slug'          => $slug,
				'id'            => 0,
				'action'        => 'create',
				'status'        => '',
				'is_elementor'  => false,
				'is_front_page' => false,
				'converts'      => false,
			);
			$make++;
			continue;
		}
		$is_elementor = 'builder' === get_post_meta( $page->ID, '_elementor_edit_mode', true );
		$body         = trim( (string) get_post_field( 'post_content', $page->ID ) );
		$rows[]       = array(
			'slug'          => $slug,
			'id'            => (int) $page->ID,
			'action'        => 'overwrite',
			'status'        => $page->post_status,
			'is_elementor'  => $is_elementor,
			'is_front_page' => ( 'page' === $front['mode'] && $front['id'] === (int) $page->ID ),
			'converts'      => ( ! $is_elementor && '' !== $body ),
		);
		$over++;
	}

	$out = 'NovaMira preflight de escritura: ' . count( $rows ) . ' slugs — ' . $over . ' se pisan, ' . $make . ' se crean';
	foreach ( $rows as $row ) {
		if ( 'create' === $row['action'] ) {
			$out .= "\n  CREA       " . $row['slug'];
			continue;
		}
		$out .= "\n  " . ( $row['converts'] ? 'CONVIERTE ' : 'PISA      ' ) . $row['slug']
			. ' #' . $row['id'] . ' ' . $row['status']
			. ' ' . ( $row['is_elementor'] ? 'Elementor' : 'clasica' )
			. ( $row['is_front_page'] ? '  [PORTADA — es lo que ve el visitante al entrar]' : '' )
			. ( $row['converts'] ? '  [su contenido actual deja de renderizarse]' : '' );
	}
	if ( ! $over ) {
		$out .= "\n  nada que pisar: ninguno de estos slugs existe todavia";
	}
	error_log( str_replace( "\n", ' | ', $out ) );
	echo $out . "\n";

	/* Recorded AFTER the block is printed, never before. The approval artifact is the text a human
	   read, so a preflight that dies partway through approves nothing — the same reason every write
	   in this file is read back instead of trusted to its return value. */
	global $es_preflight_slugs;
	if ( ! isset( $es_preflight_slugs ) || ! is_array( $es_preflight_slugs ) ) {
		$es_preflight_slugs = array();
	}
	foreach ( $rows as $row ) {
		$es_preflight_slugs[] = $row['slug'];
	}

	return array(
		'rows'       => $rows,
		'overwrites' => $over,
		'creates'    => $make,
	);
}

/**
 * Is this build writing into a site whose sandbox is switched off?
 *
 * `.crashed` disables the WHOLE sandbox: the loader returns before its `require_once` loop, so not
 * one file in that directory runs on its own. A build survives that anyway, because `execute-php`
 * requires the builder explicitly and an explicit require does not go through the loader — so
 * every page can be written, audited and reported as done while the site sits in a degraded state
 * nobody resolved. `project-context` step 8 REPORTED safe mode and nothing acted on it; reporting a
 * blocker that the next step walks straight past is the shape this branch keeps removing.
 *
 * Once per request here, unlike the per-slug approval check, and the difference is the point: an
 * unapproved write is a fact about ONE page, so silence after the first would hide the rest. Safe
 * mode is one fact about the SITE, and repeating it per page would bury the pages under it.
 *
 * Warns rather than refuses, for the same reason nothing else in this file refuses: the way out of
 * a crashed sandbox is to run something, and a guard that blocks writes blocks the repair too.
 * What it must never do is stay quiet — `.crashed` is invisible from the connector, its only other
 * notice is a wp-admin banner, and an agent working through MCP never sees one.
 *
 * Returns the reason when safe mode is on, `''` otherwise, so a caller can read the verdict without
 * parsing stdout.
 */
function es_safe_mode_check() {
	static $said = false;
	$crashed = es_sandbox_dir() . '/.crashed';
	if ( ! file_exists( $crashed ) ) {
		return '';
	}
	$raw    = trim( (string) @file_get_contents( $crashed ) );
	$rec    = json_decode( $raw, true );
	$reason = is_array( $rec )
		? ( isset( $rec['sandbox_file'] ) ? basename( (string) $rec['sandbox_file'] ) : 'fichero sin nombrar' )
		: ( '' !== $raw ? substr( $raw, 0, 120 ) : 'el fichero .crashed esta vacio' );
	if ( ! $said ) {
		$said = true;
		es_warn(
			'ESTE BUILD ESTA ESCRIBIENDO CON EL SANDBOX APAGADO. Existe .crashed (' . $reason . '), asi que el cargador '
			. 'de Novamira no ejecuta NINGUN fichero del sandbox por su cuenta: lo que se construya hoy depende de que '
			. 'alguien vuelva a requerir estos ficheros a mano, y el fallo que dejo el sitio asi sigue sin arreglarse. '
			. 'Arregla o borra el fichero culpable ANTES de quitar .crashed — quitarlo sin mas vuelve a cargarlo y a tumbar el sitio.'
		);
	}

	return $reason;
}

/**
 * Was THIS slug in a block somebody was shown?
 *
 * `es_overwrite_preflight()` prints the approval artifact, and until now printing it was the whole
 * mechanism. A build that never called it wrote exactly as before, and a build that preflighted
 * five slugs and then wrote six left the sixth one invisible — the page nobody approved is
 * precisely the page nobody knew about. The house rule existed; the runtime did not. That is the
 * same shape as the front page nothing set, and the reason this branch exists.
 *
 * Per slug, not once per request, deliberately: a per-request flag falls silent after the first
 * warning, and the write it would then hide is the unapproved one. Each unapproved write is its
 * own fact and says its own name.
 *
 * It WARNS and does not block. A build interrupted mid-flight — which the connector's ~20-minute
 * token makes routine — has to be resumable without re-approving the pages that already landed,
 * and refusing the write here would make the recovery path the one that cannot run. The backup
 * still happens either way; what is missing is the approval, and approval comes before the write
 * or it is not approval.
 *
 * Returns the verdict so a caller — and a test — can read it without parsing stdout.
 */
function es_approval_check( $slug ) {
	global $es_preflight_slugs;

	if ( isset( $es_preflight_slugs ) && is_array( $es_preflight_slugs ) && in_array( $slug, $es_preflight_slugs, true ) ) {
		return true;
	}
	es_warn(
		'se va a escribir "' . $slug . '" sin haberlo pasado por es_overwrite_preflight(). Nadie ha visto el bloque que '
		. 'dice si esa pagina ya existe, si es la portada, o si su contenido actual deja de renderizarse. El respaldo se '
		. 'hace igual, pero la aprobacion va ANTES de la escritura: despues ya no es una aprobacion, es un aviso.'
	);

	return false;
}

/**
 * Did a build that made pages leave WordPress serving the blog at `/`?
 *
 * `es_set_front_page()` was written, tested and documented, and nothing called it — so a build
 * could still finish with every check green and the client's front page untouched. A helper
 * nothing invokes is the same failure as a check that inspects nothing, one level up. This is
 * where it becomes visible, because `es_audit_summary()` is the one line the operator is told to
 * read before deploying.
 *
 * Fires only when this run SAVED pages and `/` still serves the blog. It deliberately does not
 * judge WHICH page is the front page on a site that already has one: the options say which, never
 * whether it is the right one, and an audit that complains about every correct existing site is an
 * audit people learn to scroll past.
 *
 * Returns `'nothing-built'`, `'page'` or `'posts'` — the verdict, not the fact that it ran.
 */
function es_front_page_check() {
	global $es_saved_pages;

	if ( ! isset( $es_saved_pages ) || ! is_array( $es_saved_pages ) || ! $es_saved_pages ) {
		return 'nothing-built';
	}
	if ( 'page' === es_front_page()['mode'] ) {
		return 'page';
	}
	es_warn(
		'este build guardo ' . count( $es_saved_pages ) . ' pagina(s) y WordPress sigue sirviendo el BLOG en "/". Ninguna '
		. 'de ellas es la portada, asi que quien entre por la raiz no vera nada de lo que se acaba de construir. Llama a '
		. 'es_set_front_page("<slug>") con la home y relee lo que devuelve.'
	);

	return 'posts';
}

/**
 * Rebuild one post's Elementor stylesheet with a fresh cache-busting version.
 *
 * Elementor also stores the rendered markup in `_elementor_element_cache` for
 * 24h. Writing `_elementor_data` directly does not invalidate it, so the front
 * end keeps serving the previous (or empty) HTML until that meta is dropped.
 */
function es_rebuild_css( $post_id ) {
	delete_post_meta( $post_id, '_elementor_element_cache' );

	if ( ! class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
		return;
	}
	$uploads = wp_upload_dir();
	$file    = $uploads['basedir'] . '/elementor/css/post-' . $post_id . '.css';
	if ( file_exists( $file ) ) {
		unlink( $file );
	}
	delete_post_meta( $post_id, '_elementor_css' );
	\Elementor\Core\Files\CSS\Post::create( $post_id )->update();
}

/**
 * Regenerate the Theme Builder conditions cache.
 *
 * Writing `_elementor_conditions` post-meta does NOT register a template: at runtime
 * Elementor Pro reads the cached option `elementor_pro_theme_builder_conditions`
 * (`{location:{post_id:[conds]}}`) and never the meta. A template saved without this
 * step exists in the library and simply never renders on the front end.
 *
 * Use `get_cache()->regenerate()` and not the conditions manager's `save_conditions()`,
 * which throws "Cannot unset string offsets".
 *
 * Every hop is guarded so a site without Elementor Pro degrades to a logged no-op
 * instead of a fatal. Returns true only when the cache was actually rebuilt.
 */
function es_rebuild_theme_conditions() {
	if ( ! class_exists( '\ElementorPro\Modules\ThemeBuilder\Module' )
		|| ! method_exists( '\ElementorPro\Modules\ThemeBuilder\Module', 'instance' ) ) {
		return false;
	}
	$module = \ElementorPro\Modules\ThemeBuilder\Module::instance();
	if ( ! $module || ! method_exists( $module, 'get_conditions_manager' ) ) {
		return false;
	}
	$manager = $module->get_conditions_manager();
	if ( ! $manager || ! method_exists( $manager, 'get_cache' ) ) {
		return false;
	}
	$cache = $manager->get_cache();
	if ( ! $cache || ! method_exists( $cache, 'regenerate' ) ) {
		return false;
	}
	$cache->regenerate();

	return true;
}

/**
 * Is a template present in the Theme Builder conditions cache?
 *
 * Regenerating is not proof: the gotcha is explicit that you must VERIFY the option
 * contains your template afterwards, because a condition string the runtime does not
 * recognise is dropped silently and the template stays invisible.
 *
 * Present is not the same as RENDERING, and this function cannot tell you the difference.
 * Elementor resolves one template per location; when two are registered there, one of them
 * loses, and this returns true for the loser exactly as it does for the winner. Ask
 * es_theme_location_rivals() before reading a `true` here as "the header is on the site".
 */
function es_theme_conditions_registered( $post_id ) {
	$cache = get_option( 'elementor_pro_theme_builder_conditions' );
	if ( ! is_array( $cache ) ) {
		return false;
	}
	foreach ( $cache as $templates ) {
		if ( ! is_array( $templates ) ) {
			continue;
		}
		if ( array_key_exists( (int) $post_id, $templates ) || array_key_exists( (string) $post_id, $templates ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Which OTHER templates are registered at the same locations as this one?
 *
 * The registration check answers "is my template in the cache" and stops there, which reports
 * green on the one failure an operator cannot see: another published template already claiming
 * `header`. Elementor picks one per location, so a header that is correctly saved, correctly
 * conditioned and correctly cached can still never render — and every automated check said yes.
 * That is a leftover from a previous agency, a theme's bundled template, or the last build of
 * this very site, and the site looks unchanged while the report says it worked.
 *
 * Returns `array( location => array( other_template_id, … ) )`, empty when nothing competes.
 * It names rivals, it does not pick a winner: the resolution order is Elementor Pro's and is not
 * knowable from this option, so the honest output is "these compete, go look".
 */
function es_theme_location_rivals( $post_id ) {
	$cache = get_option( 'elementor_pro_theme_builder_conditions' );
	if ( ! is_array( $cache ) ) {
		return array();
	}
	$rivals = array();
	foreach ( $cache as $location => $templates ) {
		if ( ! is_array( $templates ) ) {
			continue;
		}
		if ( ! array_key_exists( (int) $post_id, $templates ) && ! array_key_exists( (string) $post_id, $templates ) ) {
			continue;
		}
		foreach ( array_keys( $templates ) as $other ) {
			if ( (int) $other !== (int) $post_id ) {
				$rivals[ $location ][] = (int) $other;
			}
		}
	}

	return $rivals;
}
