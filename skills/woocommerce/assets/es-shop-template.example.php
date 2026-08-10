<?php
/**
 * Example - product archive template (Elementor Pro Theme Builder).
 * Uses the native archive-products widget so WooCommerce owns the loop.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/*
 * Dependencies. Any .php dropped in novamira-sandbox/ executes on upload, so a bare
 * require_once on a missing file fatals before execute-php is ever called — taking the
 * site down. Fail loudly but safely instead: report what is missing and stop.
 * es-theme-parts.php is es-theme-parts.example.php with the .example suffix stripped.
 */
foreach ( array( 'es-builder.php', 'es-theme-parts.php' ) as $es_dep ) {
	$es_dep_path = WP_CONTENT_DIR . '/novamira-sandbox/' . $es_dep;
	if ( ! file_exists( $es_dep_path ) ) {
		error_log( 'NovaMira: ' . basename( __FILE__ ) . ' requires ' . $es_dep . ' in novamira-sandbox/. Upload it first.' );
		return;
	}
	require_once $es_dep_path;
}

function es_build_shop_template() {
	es_uid_reset( 'shoparchive' );

	$el = array();

	/* Page head */
	$el[] = es_c(
		array(
			'content_width'  => 'boxed',
			'flex_direction' => 'column',
			'padding'        => es_box( 76, 24, 60, 24 ),
			'padding_mobile' => es_box( 48, 20, 40, 20 ),
			'border_border'  => 'solid',
			'border_width'   => es_box( 0, 0, 1, 0 ),
			'border_color'   => '#E5E7E5',
		),
		array(
			es_eyebrow( 'Tienda online' ),
			es_h(
				'Recambios y accesorios de confianza.',
				'h1',
				array(
					'typography_typography'       => 'custom',
					'typography_font_family'      => 'Space Grotesk',
					'typography_font_size'        => es_size( 48 ),
					'typography_font_size_mobile' => es_size( 31 ),
					'typography_font_weight'      => '700',
					'typography_line_height'      => es_size( 1.1, 'em' ),
					'_margin'                     => es_box( 0, 0, 16, 0 ),
				)
			),
			es_p(
				'Aceites, frenos, baterías, iluminación, limpieza, accesorios, neumáticos y herramientas. Recogida en taller sin coste o envío a domicilio.',
				array( 'typography_font_size' => es_size( 17 ), 'width' => es_size( 62, '%' ), 'width_tablet' => es_size( 100, '%' ) )
			),
		)
	);

	/* Product grid */
	$el[] = es_section(
		array(
			es_w(
				'wc-archive-products',
				array(
					'columns'                  => 4,
					'columns_tablet'           => 3,
					'columns_mobile'           => 2,
					'rows'                     => 5,
					'paginate'                 => 'yes',
					'allow_order'              => 'yes',
					'show_result_count'        => 'yes',
					/* Card */
					'row_gap'                  => es_size( 28 ),
					'column_gap'               => es_size( 24 ),
					'text_align'               => 'left',
					/* Title */
					'product_title_color'      => '#15181A',
					'product_title_typography_typography' => 'custom',
					'product_title_typography_font_family' => 'Manrope',
					'product_title_typography_font_size' => es_size( 15 ),
					'product_title_typography_font_weight' => '600',
					'product_title_typography_line_height' => es_size( 1.4, 'em' ),
					/* Price */
					'price_color'              => '#15181A',
					'price_typography_typography' => 'custom',
					'price_typography_font_family' => 'Space Grotesk',
					'price_typography_font_size' => es_size( 18 ),
					'price_typography_font_weight' => '700',
					/* Button */
					'button_color'             => '#FFFFFF',
					'button_background_color'  => '#0FA968',
					'button_hover_color'       => '#FFFFFF',
					'button_background_hover_color' => '#0C8A55',
					'button_border_radius'     => es_box( 6, 6, 6, 6 ),
					'button_padding'           => es_box( 11, 18, 11, 18 ),
					'button_typography_typography' => 'custom',
					'button_typography_font_family' => 'Manrope',
					'button_typography_font_size' => es_size( 13.5 ),
					'button_typography_font_weight' => '600',
					/* Image hover zoom */
					'image_border_radius'      => es_box( 8, 8, 8, 8 ),
					/* Hover via native Custom CSS so it does not rely on
					   conditionally-enqueued animation assets. */
					'custom_css'               => 'selector ul.products li.product{transition:transform .5s cubic-bezier(.22,1,.36,1),box-shadow .5s cubic-bezier(.22,1,.36,1);border-radius:12px;overflow:hidden;padding:10px;will-change:transform;}'
						. 'selector ul.products li.product .woocommerce-loop-product__link img,selector ul.products li.product img{transition:transform .7s cubic-bezier(.22,1,.36,1);border-radius:8px;will-change:transform;}'
						. 'selector ul.products li.product:hover{transform:translateY(-4px);box-shadow:0 18px 40px -12px rgba(21,24,26,0.16);}'
						. 'selector ul.products li.product:hover img{transform:scale(1.045);}'
						. 'selector ul.products li.product a.button{background-color:#0FA968!important;border-color:#0FA968!important;color:#fff!important;border-radius:6px!important;transition:background-color .3s ease,box-shadow .35s ease!important;}'
						. 'selector ul.products li.product a.button:hover{background-color:#0C8A55!important;box-shadow:0 10px 22px -8px rgba(15,169,104,0.5)!important;}'
						. 'selector ul.products{align-items:stretch;}'
						. 'selector ul.products li.product{display:flex!important;flex-direction:column;height:100%;}'
						. 'selector ul.products li.product .button{margin-top:auto;}'
						/* Hide the inline "Ver carrito" link; the side cart covers it */
						. 'selector ul.products li.product a.added_to_cart{display:none!important;}'
						. 'selector ul.products li.product a.button.added{font-size:0!important;}'
						. 'selector ul.products li.product a.button.added::after{content:"Añadido ✓"!important;font-size:13.5px!important;font-weight:600;}'
						/* Pagination in palette (was inheriting a pink link color) */
						. 'selector .woocommerce-pagination .page-numbers,selector .elementor-pagination .page-numbers{color:#6A6F6C!important;border-color:#E5E7E5!important;border-radius:8px!important;transition:color .25s ease,background-color .25s ease,border-color .25s ease;}'
						. 'selector .woocommerce-pagination a.page-numbers:hover,selector .elementor-pagination a.page-numbers:hover{color:#0FA968!important;border-color:#0FA968!important;}'
						. 'selector .woocommerce-pagination .page-numbers.current,selector .elementor-pagination .page-numbers.current{color:#FFFFFF!important;background-color:#0FA968!important;border-color:#0FA968!important;}',
					/* Pagination */
					'pagination_color'         => '#6A6F6C',
					'pagination_color_hover'   => '#0FA968',
					'pagination_color_active'  => '#15181A',
					'pagination_typography_typography' => 'custom',
					'pagination_typography_font_family' => 'Manrope',
					'pagination_typography_font_size' => es_size( 14 ),
				)
			),
		)
	);

	/* Trust band */
	$ventajas = array(
		array( 'fas fa-store', 'Recogida en taller', 'Sin coste y disponible el mismo día.' ),
		array( 'fas fa-truck', 'Envío a domicilio', 'Entrega en 24-48 h en península.' ),
		array( 'fas fa-shield-alt', 'Garantía de fabricante', 'Todos los recambios con garantía oficial.' ),
		array( 'fas fa-headset', 'Te asesoramos', 'Consúltanos si dudas de la compatibilidad.' ),
	);
	$vcards = array();
	foreach ( $ventajas as $v ) {
		$vcards[] = es_w(
			'icon-box',
			array(
				'selected_icon'    => array( 'value' => $v[0], 'library' => 'fa-solid' ),
				'title_text'       => $v[1],
				'description_text' => $v[2],
				'position'         => 'block-start',
				'text_align'       => 'left',
				'title_size'       => 'h3',
				'primary_color'    => '#0FA968',
				'icon_size'        => es_size( 20 ),
				'icon_space'       => es_size( 16 ),
				'title_color'      => '#15181A',
				'description_color' => '#6A6F6C',
				'title_typography_typography' => 'custom',
				'title_typography_font_family' => 'Space Grotesk',
				'title_typography_font_size' => es_size( 17 ),
				'title_typography_font_weight' => '700',
				'description_typography_typography' => 'custom',
				'description_typography_font_family' => 'Manrope',
				'description_typography_font_size' => es_size( 14 ),
				'description_typography_line_height' => es_size( 1.55, 'em' ),
				'_padding'         => es_box( 28, 24, 30, 24 ),
			)
		);
	}
	$el[] = es_section(
		array( es_grid( 4, $vcards, 1, array( 'background_background' => 'classic', 'background_color' => '#E5E7E5' ) ) ),
		array( 'bg' => '#F4F5F3' )
	);

	return es_save_theme_part(
		'es-shop-archive',
		'Site - Product archive',
		'product-archive',
		$el,
		array( 'include/product_archive' )
	);
}
