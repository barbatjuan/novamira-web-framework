<?php
/**
 * Example - single product template (Elementor Pro Theme Builder).
 * Native WooCommerce single-product widgets only.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once WP_CONTENT_DIR . '/novamira-sandbox/es-builder.php';
require_once WP_CONTENT_DIR . '/novamira-sandbox/es-theme-parts.php';

function es_build_product_single() {
	es_uid_reset( 'prodsingle' );

	$el = array();

	/* ---------- Breadcrumb band ---------- */
	$el[] = es_c(
		array(
			'content_width'  => 'boxed',
			'flex_direction' => 'column',
			'padding'        => es_box( 22, 24, 22, 24 ),
			'padding_mobile' => es_box( 16, 20, 16, 20 ),
			'border_border'  => 'solid',
			'border_width'   => es_box( 0, 0, 1, 0 ),
			'border_color'   => '#EAECEA',
		),
		array(
			es_w(
				'woocommerce-breadcrumb',
				array(
					'text_color'            => '#6A6F6C',
					'link_color'            => '#6A6F6C',
					'link_hover_color'      => '#0FA968',
					'typography_typography' => 'custom',
					'typography_font_family' => 'Manrope',
					'typography_font_size'  => es_size( 13.5 ),
				)
			),
		)
	);

	/* ---------- Main: gallery + buy box ---------- */
	$el[] = es_section(
		array(
			es_c(
				array(
					'content_width'    => 'full',
					'flex_direction'   => 'row',
					'flex_gap'         => array( 'unit' => 'px', 'size' => 56, 'column' => '56', 'row' => '40' ),
					'flex_wrap'        => 'wrap',
					'flex_align_items' => 'flex-start',
				),
				array(
					/* Gallery */
					es_c(
						array( 'content_width' => 'full', 'flex_direction' => 'column', 'width' => es_size( 50, '%' ), 'width_tablet' => es_size( 100, '%' ) ),
						array(
							es_w(
								'woocommerce-product-images',
								array(
									'sale_flash_show'      => 'yes',
									'image_border_radius'  => es_box( 14, 14, 14, 14 ),
									'thumbs_border_radius' => es_box( 8, 8, 8, 8 ),
									'spacing'              => es_size( 10 ),
								)
							),
						),
						true
					),
					/* Buy box */
					es_c(
						array( 'content_width' => 'full', 'flex_direction' => 'column', 'width' => es_size( 45, '%' ), 'width_tablet' => es_size( 100, '%' ) ),
						array(
							es_w(
								'woocommerce-product-title',
								array(
									'text_color'                  => '#15181A',
									'typography_typography'       => 'custom',
									'typography_font_family'      => 'Space Grotesk',
									'typography_font_size'        => es_size( 36 ),
									'typography_font_size_mobile' => es_size( 27 ),
									'typography_font_weight'      => '700',
									'typography_line_height'      => es_size( 1.12, 'em' ),
									'_margin'                     => es_box( 0, 0, 14, 0 ),
								)
							),
							es_w( 'woocommerce-product-rating', array( 'star_color' => '#0FA968', '_margin' => es_box( 0, 0, 16, 0 ) ) ),
							es_w(
								'woocommerce-product-price',
								array(
									'price_color'                 => '#15181A',
									'price_typography_typography' => 'custom',
									'price_typography_font_family' => 'Space Grotesk',
									'price_typography_font_size'  => es_size( 30 ),
									'price_typography_font_weight' => '700',
									'_margin'                     => es_box( 0, 0, 20, 0 ),
								)
							),
							es_w(
								'woocommerce-product-short-description',
								array(
									'text_color'             => '#4A4F4C',
									'typography_typography'  => 'custom',
									'typography_font_family' => 'Manrope',
									'typography_font_size'   => es_size( 15.5 ),
									'typography_line_height' => es_size( 1.65, 'em' ),
									'_margin'                => es_box( 0, 0, 26, 0 ),
								)
							),
							es_w(
								'woocommerce-product-add-to-cart',
								array(
									'button_background_color'       => '#0FA968',
									'button_hover_background_color' => '#0C8A55',
									'button_text_color'             => '#FFFFFF',
									'button_hover_text_color'       => '#FFFFFF',
									'button_border_radius'          => es_box( 8, 8, 8, 8 ),
									'quantity_border_color'         => '#E5E7E5',
									'button_typography_typography'  => 'custom',
									'button_typography_font_family' => 'Manrope',
									'button_typography_font_size'   => es_size( 15 ),
									'button_typography_font_weight' => '600',
									'_margin'                       => es_box( 0, 0, 26, 0 ),
									/* Match the site green button, hover included. */
									'custom_css'                    => 'selector .button,selector .single_add_to_cart_button{background-color:#0FA968!important;border-color:#0FA968!important;color:#fff!important;border-radius:8px!important;transition:background-color .3s ease,box-shadow .35s ease,transform .35s cubic-bezier(.22,1,.36,1)!important;}'
										. 'selector .button:hover,selector .single_add_to_cart_button:hover{background-color:#0C8A55!important;border-color:#0C8A55!important;transform:translateY(-2px);box-shadow:0 12px 26px -10px rgba(15,169,104,0.55)!important;}',
								)
							),
							/* Trust list */
							es_w(
								'icon-list',
								array(
									'icon_list'     => array(
										array( 'text' => 'Envío en 24-48 h o recogida gratis en taller', 'selected_icon' => array( 'value' => 'fas fa-truck', 'library' => 'fa-solid' ) ),
										array( 'text' => 'Garantía oficial de fabricante', 'selected_icon' => array( 'value' => 'fas fa-shield-alt', 'library' => 'fa-solid' ) ),
										array( 'text' => 'Te asesoramos si dudas de la compatibilidad', 'selected_icon' => array( 'value' => 'fas fa-headset', 'library' => 'fa-solid' ) ),
									),
									'space_between' => es_size( 12 ),
									'icon_color'    => '#0FA968',
									'icon_size'     => es_size( 15 ),
									'text_color'    => '#4A4F4C',
									'icon_typography_typography' => 'custom',
									'icon_typography_font_family' => 'Manrope',
									'icon_typography_font_size' => es_size( 14 ),
									'_padding'      => es_box( 22, 22, 22, 22 ),
									'_background_background' => 'classic',
									'_background_color' => '#F4F5F3',
									'_border_radius' => es_box( 12, 12, 12, 12 ),
									'_margin'       => es_box( 0, 0, 24, 0 ),
								)
							),
							es_w(
								'woocommerce-product-meta',
								array(
									'typography_typography'  => 'custom',
									'typography_font_family' => 'Manrope',
									'typography_font_size'   => es_size( 13.5 ),
								)
							),
						),
						true
					),
				),
				true
			),
		)
	);

	/* ---------- Product data tabs ---------- */
	$el[] = es_section(
		array(
			es_w(
				'woocommerce-product-data-tabs',
				array(
					'tabs_title_color'                  => '#6A6F6C',
					'tabs_title_color_active'           => '#15181A',
					'tabs_title_typography_typography'  => 'custom',
					'tabs_title_typography_font_family' => 'Space Grotesk',
					'tabs_title_typography_font_size'   => es_size( 15 ),
					'tabs_title_typography_font_weight' => '600',
					'border_color'                      => '#EAECEA',
					'content_color'                     => '#4A4F4C',
					'content_typography_typography'     => 'custom',
					'content_typography_font_family'    => 'Manrope',
					'content_typography_font_size'      => es_size( 15 ),
					'content_typography_line_height'    => es_size( 1.7, 'em' ),
				)
			),
		),
		array( 'bg' => '#F4F5F3' )
	);

	/* ---------- Related products ---------- */
	$el[] = es_section(
		array(
			es_c(
				array( 'content_width' => 'full', 'flex_direction' => 'column', '_margin' => es_box( 0, 0, 36, 0 ) ),
				array(
					es_eyebrow( 'También te puede interesar' ),
					es_h( 'Productos relacionados', 'h2', array( 'typography_typography' => 'custom', 'typography_font_family' => 'Space Grotesk', 'typography_font_size' => es_size( 32 ), 'typography_font_size_mobile' => es_size( 24 ), 'typography_font_weight' => '700' ) ),
				),
				true
			),
			es_w(
				'woocommerce-product-related',
				array(
					'columns'                  => 4,
					'columns_tablet'           => 3,
					'columns_mobile'           => 2,
					'posts_per_page'           => 4,
					'text_align'               => 'left',
					'product_title_color'      => '#15181A',
					'price_color'              => '#15181A',
					'button_color'             => '#FFFFFF',
					'button_background_color'  => '#0FA968',
					'button_hover_color'       => '#FFFFFF',
					'button_background_hover_color' => '#0C8A55',
					'button_border_radius'     => es_box( 6, 6, 6, 6 ),
					'custom_css'               => 'selector ul.products li.product{transition:transform .5s cubic-bezier(.22,1,.36,1),box-shadow .5s cubic-bezier(.22,1,.36,1);border-radius:12px;overflow:hidden;padding:10px;will-change:transform;}'
						. 'selector ul.products li.product .woocommerce-loop-product__link img,selector ul.products li.product img{transition:transform .7s cubic-bezier(.22,1,.36,1);border-radius:8px;will-change:transform;}'
						. 'selector ul.products li.product:hover{transform:translateY(-4px);box-shadow:0 18px 40px -12px rgba(21,24,26,0.16);}'
						. 'selector ul.products li.product:hover img{transform:scale(1.045);}'
						. 'selector ul.products li.product a.button{background-color:#0FA968!important;border-color:#0FA968!important;color:#fff!important;border-radius:6px!important;transition:background-color .3s ease,box-shadow .35s ease!important;}'
						. 'selector ul.products li.product a.button:hover{background-color:#0C8A55!important;box-shadow:0 10px 22px -8px rgba(15,169,104,0.5)!important;}'
						. 'selector ul.products{align-items:stretch;}'
						. 'selector ul.products li.product{display:flex!important;flex-direction:column;height:100%;}'
						. 'selector ul.products li.product .button{margin-top:auto;}'
						. 'selector ul.products li.product a.added_to_cart{display:none!important;}'
						. 'selector ul.products li.product a.button.added{font-size:0!important;}'
						. 'selector ul.products li.product a.button.added::after{content:"Añadido ✓"!important;font-size:13.5px!important;font-weight:600;}',
				)
			),
		)
	);

	return es_save_theme_part(
		'es-single-product',
		'Site - Product',
		'product',
		$el,
		array( 'include/product' )
	);
}
