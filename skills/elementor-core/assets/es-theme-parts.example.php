<?php
/**
 * Elorrieta Sport - global header and footer (Elementor Pro Theme Builder).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once WP_CONTENT_DIR . '/novamira-sandbox/es-builder.php';

/**
 * Create or update a theme template and apply a global display condition.
 */
function es_save_theme_part( $slug, $title, $type, array $elements, array $conditions ) {
	$existing = get_posts(
		array(
			'post_type'      => 'elementor_library',
			'name'           => $slug,
			'posts_per_page' => 1,
			'post_status'    => 'any',
		)
	);
	if ( $existing ) {
		$id = $existing[0]->ID;
		wp_update_post( array( 'ID' => $id, 'post_title' => $title, 'post_status' => 'publish' ) );
	} else {
		$id = wp_insert_post(
			array(
				'post_title'  => $title,
				'post_name'   => $slug,
				'post_type'   => 'elementor_library',
				'post_status' => 'publish',
			)
		);
	}
	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}

	wp_set_object_terms( $id, $type, 'elementor_library_type' );
	update_post_meta( $id, '_elementor_template_type', $type );
	update_post_meta( $id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.0.0' );
	update_post_meta( $id, '_elementor_data', wp_slash( wp_json_encode( $elements ) ) );
	update_post_meta( $id, '_elementor_conditions', $conditions );
	es_rebuild_css( $id );

	return $id;
}

/* =====================================================================
 * HEADER
 * ===================================================================== */
function es_foot_col( $title, array $links ) {
	$children = array(
		es_w(
			'heading',
			array(
				'title'                     => $title,
				'header_size'               => 'div',
				'title_color'               => 'rgba(255,255,255,0.42)',
				'typography_typography'     => 'custom',
				'typography_font_family'    => 'Manrope',
				'typography_font_size'      => es_size( 11.5 ),
				'typography_font_weight'    => '700',
				'typography_text_transform' => 'uppercase',
				'typography_letter_spacing' => es_size( 1.5 ),
				'_margin'                   => es_box( 0, 0, 18, 0 ),
			)
		),
	);
	$items = array();
	foreach ( $links as $label => $url ) {
		$items[] = array(
			'text' => $label,
			'link' => array( 'url' => $url, 'is_external' => '', 'nofollow' => '' ),
		);
	}
	$children[] = es_w(
		'icon-list',
		array(
			'icon_list'             => $items,
			'view'                  => 'traditional',
			'space_between'         => es_size( 11 ),
			'text_color'            => 'rgba(255,255,255,0.76)',
			'text_color_hover'      => '#0FA968',
			'icon_color'            => 'rgba(255,255,255,0)',
			'icon_size'             => es_size( 0 ),
			'text_indent'           => es_size( 0 ),
			'icon_typography_typography' => 'custom',
			'icon_typography_font_family' => 'Manrope',
			'icon_typography_font_size' => es_size( 14.5 ),
		)
	);
	return es_c(
		array( 'content_width' => 'full', 'flex_direction' => 'column' ),
		$children,
		true
	);
}

function es_build_theme_parts() {
	es_uid_reset( 'header' );
	
	$header = array(
		es_c(
			array(
				'content_width'         => 'boxed',
				'flex_direction'        => 'column',
				'flex_gap'              => array( 'unit' => 'px', 'size' => 0, 'column' => '0', 'row' => '0' ),
				'padding'               => es_box( 16, 24, 16, 24 ),
				'border_border'         => 'solid',
				'border_width'          => es_box( 0, 0, 1, 0 ),
				'border_color'          => 'rgba(21,24,26,0.08)',
				/* Glass lives on a ::before layer, NOT the container. backdrop-filter
				   on the container itself creates a containing block that traps the
				   fixed side-cart inside the header box; the pseudo-element keeps the
				   frosted look while the cart is free to cover the viewport. */
				'custom_css'            => 'selector{position:relative;}'
					. 'selector::before{content:"";position:absolute;inset:0;z-index:-1;background:rgba(255,255,255,0.72);backdrop-filter:saturate(180%) blur(16px);-webkit-backdrop-filter:saturate(180%) blur(16px);}',
				'sticky'                => 'top',
				'sticky_on'             => array( 'desktop', 'tablet', 'mobile' ),
				'sticky_effects_offset' => 0,
				'z_index'               => 99,
			),
			array(
				/* Top bar: logo (left) + cluster (right), always one row. */
				es_c(
					array(
						'flex_direction'       => 'row',
						'flex_align_items'     => 'center',
						'flex_justify_content' => 'space-between',
						'flex_wrap'            => 'nowrap',
						'flex_gap'             => array( 'unit' => 'px', 'size' => 20, 'column' => '20', 'row' => '12' ),
					),
					array(
				/* Logo */
				es_w(
					'heading',
					array(
						'title'                     => 'ELORRIETA<span style="color:#0FA968">SPORT</span>',
						'header_size'               => 'div',
						'link'                      => array( 'url' => home_url( '/' ) ),
						'title_color'               => '#15181A',
						'typography_typography'     => 'custom',
						'typography_font_family'    => 'Space Grotesk',
						'typography_font_size'      => es_size( 20 ),
						'typography_font_weight'    => '700',
						'typography_letter_spacing' => es_size( -0.4 ),
						'_element_width'            => 'auto',
					)
				),
				/* Right cluster: nav + phone + cart + CTA, right-aligned as one unit. */
				es_c(
					array(
						'flex_direction'   => 'row',
						'flex_align_items' => 'center',
						'flex_wrap'        => 'nowrap',
						'flex_gap'         => array( 'unit' => 'px', 'size' => 12, 'column' => '12', 'row' => '10' ),
						'_element_width'   => 'auto',
						'_flex_grow'       => 0,
						'_flex_shrink'     => 0,
						/* Mobile: fill the row and push the cart to the far right,
						   leaving the burger toward the centre. */
						'custom_css'       => '@media(max-width:767px){selector{flex-grow:1!important;justify-content:space-between!important;margin-left:18px;}}',
					),
					array(
				/* Menu */
				es_w(
					'nav-menu',
					array(
						'menu'                  => 'menu-principal',
						'layout'                => 'horizontal',
						'submenu_icon'          => array( 'value' => 'fas fa-caret-down', 'library' => 'fa-solid' ),
						'align_items'           => 'center',
						'pointer'               => 'underline',
						'animation_line'        => 'fade',
						'color_menu_item'       => '#6A6F6C',
						'color_menu_item_hover' => '#15181A',
						'color_menu_item_active' => '#15181A',
						'pointer_color_menu_item_hover' => '#0FA968',
						'pointer_color_menu_item_active' => '#0FA968',
						'menu_typography_typography' => 'custom',
						'menu_typography_font_family' => 'Manrope',
						'menu_typography_font_size' => es_size( 14.5 ),
						'menu_typography_font_weight' => '500',
						'padding_horizontal_menu_item' => es_size( 13 ),
						'padding_vertical_menu_item' => es_size( 8 ),
						'_element_width'        => 'auto',
						/* ---- Mobile / tablet: modern full-width panel ---- */
						'dropdown'              => 'tablet',
						'toggle'                => 'burger',
						'color_dropdown_item'   => '#15181A',
						'background_color_dropdown_item' => '#FFFFFF',
						'color_dropdown_item_hover' => '#0FA968',
						'background_color_dropdown_item_hover' => '#F4F5F3',
						'color_dropdown_item_active' => '#0FA968',
						'background_color_dropdown_item_active' => 'rgba(15,169,104,0.08)',
						'dropdown_typography_typography' => 'custom',
						'dropdown_typography_font_family' => 'Space Grotesk',
						'dropdown_typography_font_size' => es_size( 17 ),
						'dropdown_typography_font_weight' => '600',
						'padding_horizontal_dropdown_item' => es_size( 22 ),
						'padding_vertical_dropdown_item' => es_size( 17 ),
						'dropdown_divider_border' => 'solid',
						'dropdown_divider_width' => es_size( 1 ),
						'dropdown_divider_color' => '#EEF0EE',
						'dropdown_border_border' => 'solid',
						'dropdown_border_width'  => es_box( 1, 1, 1, 1 ),
						'dropdown_border_color'  => '#EAECEA',
						'dropdown_border_radius' => es_box( 14, 14, 14, 14 ),
						'dropdown_top_distance'  => es_size( 10 ),
						/* ---- Toggle: rounded square, not a bare burger ---- */
						'toggle_size'           => es_size( 19 ),
						'toggle_color'          => '#15181A',
						'toggle_background_color' => 'rgba(15,169,104,0.07)',
						'toggle_color_hover'    => '#FFFFFF',
						'toggle_background_color_hover' => '#0FA968',
						'toggle_border_width'   => es_box( 0, 0, 0, 0 ),
						'toggle_border_radius'  => es_box( 10, 10, 10, 10 ),
						/* Green underline only on desktop; in the mobile panel it read
						   as odd green bars, so it is scoped out below. */
						'custom_css'            => 'selector .elementor-nav-menu--dropdown{overflow:hidden;box-shadow:0 24px 50px -18px rgba(21,24,26,0.22);}'
							. '@media(max-width:1024px){selector .elementor-menu-toggle{width:46px;height:46px;position:relative;z-index:100;}'
							. 'selector .elementor-menu-toggle[aria-expanded="true"] ~ .elementor-nav-menu--dropdown{position:fixed!important;top:0!important;left:0!important;right:0!important;width:100vw!important;max-width:100vw!important;height:100vh!important;max-height:100vh!important;border:0!important;border-radius:0!important;box-shadow:none!important;background:#FFFFFF!important;display:flex!important;flex-direction:column!important;justify-content:center!important;padding:104px 22px 44px!important;overflow-y:auto!important;z-index:99!important;}'
							. 'selector .elementor-menu-toggle[aria-expanded="true"] ~ .elementor-nav-menu--dropdown .elementor-nav-menu{width:100%;max-width:440px;margin:0 auto;}'
							. 'selector .elementor-menu-toggle[aria-expanded="true"] ~ .elementor-nav-menu--dropdown .elementor-item{text-align:center!important;font-size:24px!important;padding:16px!important;border-radius:12px!important;}}'
							. '@media(min-width:1025px){selector .elementor-item{position:relative;}'
							. 'selector .elementor-item::after{content:"";position:absolute;left:13px;right:13px;bottom:2px;height:2px;background:#0FA968;opacity:0;transform:translateY(2px);transition:opacity .28s ease,transform .28s ease;}'
							. 'selector .elementor-item:hover::after,selector .current-menu-item .elementor-item::after,selector .elementor-item-active::after{opacity:1;transform:translateY(0);}}',
					)
				),
				/* Actions */
				es_c(
					array(
						'content_width'    => 'full',
						'flex_direction'   => 'row',
						'flex_align_items' => 'center',
						'flex_gap'         => array( 'unit' => 'px', 'size' => 10, 'column' => '10', 'row' => '10' ),
						'width'            => es_size( 'auto', 'custom' ),
						'_element_width'   => 'auto',
					),
					array(
						es_btn(
							'000 000 000',
							'tel:0000000000',
							'outline',
							array(
								'selected_icon'        => array( 'value' => 'fas fa-phone-alt', 'library' => 'fa-solid' ),
								'icon_align'           => 'left',
								'icon_indent'          => es_size( 7 ),
								'text_padding'         => es_box( 10, 16, 10, 16 ),
								'typography_font_size' => es_size( 14 ),
								'hide_mobile'          => 'hidden-mobile',
							)
						),
						/* Side cart: opens automatically when a product is added. */
						es_w(
							'woocommerce-menu-cart',
							array(
								'icon'                     => 'cart-medium',
								'items_indicator'          => 'bubble',
								'show_subtotal'            => '',
								'cart_type'                => 'side-cart',
								'side_cart_alignment'      => 'right',
								'automatically_open_cart'  => 'yes',
								'automatically_update_cart' => 'yes',
								'close_cart_button_show'   => 'yes',
								'view_cart_button_show'    => 'yes',
								'toggle_button_icon_color' => '#15181A',
								'toggle_button_background_color' => 'rgba(0,0,0,0)',
								'toggle_button_border_color' => 'rgba(0,0,0,0)',
								'toggle_button_border_width' => es_box( 0, 0, 0, 0 ),
								'toggle_button_hover_icon_color' => '#0FA968',
								'toggle_icon_size'         => es_size( 22 ),
								'toggle_button_padding'    => es_box( 4, 4, 4, 4 ),
								/* Just the icon, no square frame. */
								'custom_css'               => 'selector .elementor-menu-cart__toggle .elementor-button{border:0!important;background:transparent!important;box-shadow:none!important;}'
									. '@media(max-width:767px){selector .elementor-menu-cart__container{width:100vw!important;max-width:100vw!important;}}',
								'items_indicator_text_color' => '#FFFFFF',
								'items_indicator_background_color' => '#0FA968',
								'view_cart_button_background_color' => '#15181A',
								'view_cart_button_text_color' => '#FFFFFF',
								/* Cart item contrast (product name was inheriting a pink link color) */
								'product_title_color'      => '#15181A',
								'product_title_hover_color' => '#0FA968',
								'product_price_color'      => '#15181A',
								'product_quantity_color'   => '#6A6F6C',
								'subtotal_color'           => '#15181A',
								'_element_width'           => 'auto',
							)
						),
						es_btn(
							'Pedir cita',
							'/contacto/',
							'primary',
							array(
								'text_padding'         => es_box( 11, 20, 11, 20 ),
								'typography_font_size' => es_size( 14 ),
								'hide_mobile'          => 'hidden-mobile',
							)
						),
					),
					true
				),
				)
			),
				)
			),
				/* Pedir cita on mobile: its own centered row at 70% width. */
				es_c(
					array(
						'content_width'        => 'full',
						'flex_direction'       => 'row',
						'flex_justify_content' => 'center',
						'width_mobile'         => es_size( 92, '%' ),
						'hide_desktop'         => 'hidden-desktop',
						'hide_tablet'          => 'hidden-tablet',
						'_margin_mobile'       => es_box( 12, 0, 0, 0 ),
						'custom_css'           => '@media(max-width:767px){selector{margin-left:auto!important;margin-right:auto!important;}'
								. 'selector .elementor-widget-button{width:100%!important;}'
								. 'selector .elementor-button{width:100%!important;justify-content:center!important;}}',
					),
					array(
						es_btn(
							'Pedir cita',
							'/contacto/',
							'primary',
							array(
								'align'        => 'justify',
								'text_padding' => es_box( 13, 20, 13, 20 ),
							)
						),
					),
					true
				),
			)
		)
	);

	$header_id = es_save_theme_part(
		'es-header',
		'Elorrieta - Cabecera',
		'header',
		$header,
		array( 'include/general' )
	);
	
	/* =====================================================================
	 * FOOTER
	 * ===================================================================== */
	
	es_uid_reset( 'footer' );
	
	$footer = array(
		es_c(
			array(
				'content_width'         => 'boxed',
				'flex_direction'        => 'column',
				'padding'               => es_box( 76, 24, 32, 24 ),
				'padding_mobile'        => es_box( 56, 20, 28, 20 ),
				'background_background' => 'classic',
				'background_color'      => '#15181A',
			),
			array(
				/* Columns */
				es_grid(
					4,
					array(
						es_c(
							array( 'content_width' => 'full', 'flex_direction' => 'column' ),
							array(
								es_w(
									'heading',
									array(
										'title'                  => 'ELORRIETA<span style="color:#0FA968">SPORT</span>',
										'header_size'            => 'div',
										'title_color'            => '#FFFFFF',
										'typography_typography'  => 'custom',
										'typography_font_family' => 'Space Grotesk',
										'typography_font_size'   => es_size( 21 ),
										'typography_font_weight' => '700',
										'_margin'                => es_box( 0, 0, 16, 0 ),
									)
								),
								es_p(
									'Taller mecánico de coches y motos. Cercanía, transparencia y presupuesto cerrado en cada reparación.',
									array(
										'text_color'           => 'rgba(255,255,255,0.52)',
										'typography_font_size' => es_size( 14 ),
										'_margin'              => es_box( 0, 0, 22, 0 ),
									)
								),
								es_w(
									'social-icons',
									array(
										'social_icon_list' => array(
											array( 'social_icon' => array( 'value' => 'fab fa-instagram', 'library' => 'fa-brands' ), 'link' => array( 'url' => '#' ) ),
											array( 'social_icon' => array( 'value' => 'fab fa-facebook-f', 'library' => 'fa-brands' ), 'link' => array( 'url' => '#' ) ),
											array( 'social_icon' => array( 'value' => 'fab fa-tiktok', 'library' => 'fa-brands' ), 'link' => array( 'url' => '#' ) ),
										),
										'shape'            => 'rounded',
										'icon_size'        => es_size( 15 ),
										'icon_padding'     => es_size( 11 ),
										'icon_spacing'     => es_size( 9 ),
										'icon_color'       => 'custom',
										'icon_primary_color' => 'rgba(255,255,255,0.08)',
										'icon_secondary_color' => '#FFFFFF',
										'hover_primary_color' => '#0FA968',
										'hover_secondary_color' => '#FFFFFF',
									)
								),
							),
							true
						),
						es_foot_col(
							'Sitio',
							array(
								'Inicio'        => '/inicio/',
								'Quiénes somos' => '/quienes-somos/',
								'Servicios'     => '/servicios/',
								'Tienda online' => '/tienda/',
							)
						),
						es_foot_col(
							'Servicios',
							array(
								'Mecánica general' => '/servicios/',
								'Diagnosis'        => '/servicios/',
								'Pre ITV'          => '/servicios/',
								'Ruedas'           => '/servicios/',
							)
						),
						es_foot_col(
							'Contacto',
							array(
								'000 000 000'               => 'tel:0000000000',
								'hello@example.com'  => 'mailto:hello@example.com',
								'Cómo llegar'               => '/encuentranos/',
								'Pedir cita'                => '/contacto/',
							)
						),
					),
					48,
					array( '_margin' => es_box( 0, 0, 48, 0 ) )
				),
				/* Bottom bar */
				es_c(
					array(
						'content_width'        => 'full',
						'flex_direction'       => 'row',
						'flex_justify_content' => 'space-between',
						'flex_align_items'     => 'center',
						'flex_wrap'            => 'wrap',
						'flex_gap'             => array( 'unit' => 'px', 'size' => 14, 'column' => '14', 'row' => '10' ),
						'padding'              => es_box( 28, 0, 0, 0 ),
						'border_border'        => 'solid',
						'border_width'         => es_box( 1, 0, 0, 0 ),
						'border_color'         => 'rgba(255,255,255,0.11)',
					),
					array(
						es_p(
							'© ' . gmdate( 'Y' ) . ' Elorrieta Sport. Todos los derechos reservados.',
							array( 'text_color' => 'rgba(255,255,255,0.42)', 'typography_font_size' => es_size( 13 ), '_element_width' => 'auto' )
						),
						es_p(
							'Aviso legal · Privacidad · Cookies',
							array( 'text_color' => 'rgba(255,255,255,0.42)', 'typography_font_size' => es_size( 13 ), '_element_width' => 'auto' )
						),
					),
					true
				),
			)
		),
	);
	
	$footer_id = es_save_theme_part(
		'es-footer',
		'Elorrieta - Pie de página',
		'footer',
		$footer,
		array( 'include/general' )
	);

	return array( 'header' => $header_id, 'footer' => $footer_id );
}
