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

function es_box( $t, $r, $b, $l, $unit = 'px' ) {
	return array(
		'unit'     => $unit,
		'top'      => (string) $t,
		'right'    => (string) $r,
		'bottom'   => (string) $b,
		'left'     => (string) $l,
		'isLinked' => false,
	);
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
		return array( 'url' => '', 'id' => '' );
	}
	$cache[ $slug ] = array(
		'url' => wp_get_attachment_url( $posts[0]->ID ),
		'id'  => $posts[0]->ID,
	);
	return $cache[ $slug ];
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
 * Card hover, applied through the container's native Custom CSS field so it
 * does not depend on Elementor's conditionally-enqueued animation assets
 * (which are not registered when the layout is written via the API).
 */
function es_card_hover_css() {
	return 'selector .elementor-widget-image-box{transition:transform .5s cubic-bezier(.22,1,.36,1),box-shadow .5s cubic-bezier(.22,1,.36,1),border-color .5s ease;will-change:transform;}'
		. 'selector .elementor-widget-image-box:hover{transform:translateY(-4px);border-color:#D6DAD6;box-shadow:0 18px 40px -12px rgba(21,24,26,0.16);}'
		. 'selector .elementor-widget-image-box .elementor-image-box-img{overflow:hidden;}'
		. 'selector .elementor-widget-image-box .elementor-image-box-img img{transition:transform .7s cubic-bezier(.22,1,.36,1);will-change:transform;}'
		. 'selector .elementor-widget-image-box:hover .elementor-image-box-img img{transform:scale(1.045);}'
		. 'selector .elementor-widget-image-box .elementor-image-box-title{transition:color .4s ease;}'
		. 'selector .elementor-widget-image-box:hover .elementor-image-box-title{color:#0FA968;}';
}

/**
 * Shared CSS for any native WooCommerce products grid (archive, related, home).
 * Smooth hover lift + image zoom, green add-to-cart with hover, equal-height
 * cards (button pinned to the bottom), the redundant inline "Ver carrito" link
 * hidden, and the added-state button relabelled to "Añadido".
 */
function es_products_css() {
	return 'selector ul.products li.product{transition:transform .5s cubic-bezier(.22,1,.36,1),box-shadow .5s cubic-bezier(.22,1,.36,1);border-radius:12px;overflow:hidden;padding:10px;will-change:transform;}'
		. 'selector ul.products li.product .woocommerce-loop-product__link img,selector ul.products li.product img{transition:transform .7s cubic-bezier(.22,1,.36,1);border-radius:8px;will-change:transform;}'
		. 'selector ul.products li.product:hover{transform:translateY(-4px);box-shadow:0 18px 40px -12px rgba(21,24,26,0.16);}'
		. 'selector ul.products li.product:hover img{transform:scale(1.045);}'
		. 'selector ul.products{align-items:stretch;}'
		. 'selector ul.products li.product{display:flex!important;flex-direction:column;height:100%;}'
		. 'selector ul.products li.product .button{margin-top:auto;background-color:#0FA968!important;border-color:#0FA968!important;color:#fff!important;border-radius:6px!important;transition:background-color .3s ease,box-shadow .35s ease!important;}'
		. 'selector ul.products li.product .button:hover{background-color:#0C8A55!important;box-shadow:0 10px 22px -8px rgba(15,169,104,0.5)!important;}'
		. 'selector ul.products li.product a.added_to_cart{display:none!important;}'
		. 'selector ul.products li.product a.button.added{font-size:0!important;}'
		. 'selector ul.products li.product a.button.added::after{content:"Añadido ✓"!important;font-size:13.5px!important;font-weight:600;}';
}

/**
 * Grid container - native Elementor grid, avoids nested column containers.
 *
 * grid_rows_grid defaults to 2fr, which paints an empty second row (and a big
 * gap) whenever a grid holds a single row of content. Forcing auto rows makes
 * the grid height follow its content.
 */
function es_grid( $cols, array $children, $gap = 24, array $extra = array() ) {
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

/** Small uppercase green label above a heading. */
function es_eyebrow( $text, $color = '#0FA968' ) {
	return es_w(
		'heading',
		array(
			'title'                      => $text,
			'header_size'                => 'div',
			'title_color'                => $color,
			'typography_typography'      => 'custom',
			'typography_font_family'     => 'Manrope',
			'typography_font_size'       => es_size( 12 ),
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
		'text_color'            => '#6A6F6C',
		'typography_typography' => 'custom',
		'typography_font_family' => 'Manrope',
		'typography_font_size'  => es_size( 16 ),
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
	$trans = 'selector .elementor-button{transition:background-color .3s ease,color .3s ease,border-color .3s ease,box-shadow .35s ease,transform .35s cubic-bezier(.22,1,.36,1);}';
	$lift_green = $trans . 'selector .elementor-button:hover{transform:translateY(-2px);box-shadow:0 12px 26px -10px rgba(15,169,104,0.55);}';
	$lift_soft  = $trans . 'selector .elementor-button:hover{transform:translateY(-2px);}';

	$settings = array(
		'text'                   => $text,
		'link'                   => array( 'url' => $link, 'is_external' => '', 'nofollow' => '' ),
		'border_radius'          => es_box( 8, 8, 8, 8 ),
		'text_padding'           => es_box( 14, 26, 14, 26 ),
		'typography_typography'  => 'custom',
		'typography_font_family' => 'Manrope',
		'typography_font_size'   => es_size( 15 ),
		'typography_font_weight' => '600',
	);
	if ( 'primary' === $style ) {
		$settings['background_color']              = '#0FA968';
		$settings['button_text_color']            = '#FFFFFF';
		$settings['button_background_hover_color'] = '#0C8A55';
		$settings['hover_color']                  = '#FFFFFF';
		$settings['custom_css']                   = $lift_green;
	} elseif ( 'dark' === $style ) {
		$settings['background_color']              = '#15181A';
		$settings['button_text_color']            = '#FFFFFF';
		$settings['button_background_hover_color'] = '#0FA968';
		$settings['hover_color']                  = '#FFFFFF';
		$settings['custom_css']                   = $lift_soft;
	} elseif ( 'outline' === $style ) {
		$settings['background_color']              = 'rgba(0,0,0,0)';
		$settings['button_text_color']            = '#15181A';
		$settings['border_border']                = 'solid';
		$settings['border_width']                 = es_box( 1, 1, 1, 1 );
		$settings['border_color']                 = '#CBD0CB';
		$settings['button_background_hover_color'] = 'rgba(15,169,104,0.10)';
		$settings['hover_color']                  = '#0FA968';
		$settings['button_hover_border_color']    = '#0FA968';
		$settings['custom_css']                   = $lift_soft;
	} elseif ( 'outline-light' === $style ) {
		$settings['background_color']              = 'rgba(0,0,0,0)';
		$settings['button_text_color']            = '#FFFFFF';
		$settings['border_border']                = 'solid';
		$settings['border_width']                 = es_box( 1, 1, 1, 1 );
		$settings['border_color']                 = 'rgba(255,255,255,0.5)';
		$settings['button_background_hover_color'] = '#FFFFFF';
		$settings['hover_color']                  = '#15181A';
		$settings['button_hover_border_color']    = '#FFFFFF';
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
		'image_space'              => es_size( 20 ),
		'title_text'               => $title,
		'description_text'         => $text,
		'position'                 => 'top',
		'text_align'               => 'left',
		'title_size'               => 'h3',
		'title_color'              => '#15181A',
		'description_color'        => '#6A6F6C',
		'title_typography_typography' => 'custom',
		'title_typography_font_family' => 'Space Grotesk',
		'title_typography_font_size' => es_size( 19 ),
		'title_typography_font_weight' => '700',
		'description_typography_typography' => 'custom',
		'description_typography_font_family' => 'Manrope',
		'description_typography_font_size' => es_size( 14.5 ),
		'description_typography_line_height' => es_size( 1.6, 'em' ),
		'title_bottom_space'       => es_size( 8 ),
		'_padding'                 => es_box( 20, 20, 24, 20 ),
		'_background_background'   => 'classic',
		'_background_color'        => '#FFFFFF',
		'_border_border'           => 'solid',
		'_border_width'            => es_box( 1, 1, 1, 1 ),
		'_border_color'            => '#E5E7E5',
		'_border_radius'           => es_box( 10, 10, 10, 10 ),
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
					'border_radius'         => es_box( 14, 14, 14, 14 ),
					'overflow'              => 'hidden',
					'background_background' => 'classic',
					'background_image'      => es_img( $img_slug ),
					'background_position'   => 'center center',
					'background_size'       => 'cover',
					'background_overlay_background' => 'gradient',
					'background_overlay_color'   => 'rgba(21,24,26,0.92)',
					'background_overlay_color_b' => 'rgba(21,24,26,0.30)',
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
									'title_color'                 => '#FFFFFF',
									'typography_typography'       => 'custom',
									'typography_font_family'      => 'Space Grotesk',
									'typography_font_size'        => es_size( 38 ),
									'typography_font_size_mobile' => es_size( 27 ),
									'typography_font_weight'      => '700',
									'typography_line_height'      => es_size( 1.12, 'em' ),
									'_margin'                     => es_box( 0, 0, 16, 0 ),
								)
							),
							es_p(
								$text,
								array(
									'text_color'             => 'rgba(255,255,255,0.75)',
									'typography_font_size'   => es_size( 16 ),
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
			'primary_color'   => '#0FA968',
			'icon_space'      => es_size( 18 ),
			'icon_size'       => es_size( 20 ),
			'title_color'     => '#15181A',
			'description_color' => '#6A6F6C',
			'title_typography_typography' => 'custom',
			'title_typography_font_family' => 'Space Grotesk',
			'title_typography_font_size' => es_size( 17 ),
			'title_typography_font_weight' => '700',
			'description_typography_typography' => 'custom',
			'description_typography_font_family' => 'Manrope',
			'description_typography_font_size' => es_size( 14.5 ),
			'description_typography_line_height' => es_size( 1.55, 'em' ),
			'title_bottom_space' => es_size( 5 ),
			'_padding'        => es_box( 22, 0, 22, 0 ),
			'_border_border'  => 'solid',
			'_border_width'   => es_box( 1, 0, 0, 0 ),
			'_border_color'   => '#E5E7E5',
		)
	);
}

/**
 * Premium feature card: white card, green circular icon chip, smooth hover
 * lift with a green top-accent reveal. Shared by home ventajas and inner pages
 * so the whole site keeps one card language. Meant to sit inside es_grid().
 */
function es_feature_card( $icon, $title, $text, array $extra = array() ) {
	$defaults = array(
		'content_width'         => 'full',
		'flex_direction'        => 'column',
		'padding'               => es_box( 34, 30, 36, 30 ),
		'background_background'  => 'classic',
		'background_color'      => '#FFFFFF',
		'border_border'         => 'solid',
		'border_width'          => es_box( 1, 1, 1, 1 ),
		'border_color'          => '#EAECEA',
		'border_radius'         => es_box( 16, 16, 16, 16 ),
		'custom_css'            => 'selector{position:relative;overflow:hidden;transition:transform .5s cubic-bezier(.22,1,.36,1),box-shadow .5s cubic-bezier(.22,1,.36,1),border-color .5s ease;will-change:transform;}'
			. 'selector::before{content:"";position:absolute;top:0;left:0;right:0;height:3px;background:#0FA968;transform:scaleX(0);transform-origin:left;transition:transform .55s cubic-bezier(.22,1,.36,1);}'
			. 'selector:hover{transform:translateY(-6px);box-shadow:0 24px 50px -18px rgba(21,24,26,0.20);border-color:#E0E4E0;}'
			. 'selector:hover::before{transform:scaleX(1);}'
			. 'selector .es-feat-ico{transition:transform .5s cubic-bezier(.22,1,.36,1);}'
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
					'primary_color'   => '#0FA968',
					'secondary_color' => '#FFFFFF',
					'size'            => es_size( 20 ),
					'_css_classes'    => 'es-feat-ico',
					'_margin'         => es_box( 0, 0, 22, 0 ),
				)
			),
			es_h( $title, 'h3', array( 'typography_typography' => 'custom', 'typography_font_family' => 'Space Grotesk', 'typography_font_size' => es_size( 19 ), 'typography_font_weight' => '700', '_margin' => es_box( 0, 0, 9, 0 ) ) ),
			es_p( $text, array( 'typography_font_size' => es_size( 14.5 ), 'typography_line_height' => es_size( 1.58, 'em' ) ) ),
		),
		true
	);
}

/** Save an Elementor layout onto a page, creating the page when missing. */
function es_save_page( $slug, $title, array $elements, $tpl = 'elementor_canvas' ) {
	$page = get_page_by_path( $slug, OBJECT, 'page' );
	if ( $page ) {
		$id = $page->ID;
		wp_update_post( array( 'ID' => $id, 'post_title' => $title, 'post_status' => 'publish' ) );
	} else {
		$id = wp_insert_post(
			array(
				'post_title'   => $title,
				'post_name'    => $slug,
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => '',
			)
		);
	}
	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}

	update_post_meta( $id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $id, '_elementor_template_type', 'wp-page' );
	update_post_meta( $id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.0.0' );
	update_post_meta( $id, '_wp_page_template', $tpl );
	update_post_meta( $id, '_elementor_data', wp_slash( wp_json_encode( $elements ) ) );
	es_rebuild_css( $id );

	return $id;
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
