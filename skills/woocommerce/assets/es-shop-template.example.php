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
		/* Both channels on purpose: es_warn() lives in es-builder.php, which is exactly the
		   file that may be missing here, and error_log() alone is not "loudly" — the sandbox
		   returns STDOUT, so a log-only warning is a build that silently does nothing. */
		$es_msg = 'NovaMira: ' . basename( __FILE__ ) . ' requires ' . $es_dep . ' in novamira-sandbox/. Upload it first. NOTHING WAS BUILT.';
		error_log( $es_msg );
		echo $es_msg . "\n";
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
					   conditionally-enqueued animation assets. The grid rules come from the
					   shared es_products_css() helper rather than a local copy - hand-copied
					   duplicates drift, and this one already had. Only the pagination, which
					   exists on the archive and nowhere else, is passed in as an extra. */
					'custom_css'               => es_products_css(
						/* Pagination in palette (was inheriting a pink link color) */
						'selector .woocommerce-pagination .page-numbers,selector .elementor-pagination .page-numbers{color:#6A6F6C!important;border-color:#E5E7E5!important;border-radius:8px!important;transition:color .25s ease,background-color .25s ease,border-color .25s ease;}'
						. 'selector .woocommerce-pagination a.page-numbers:hover,selector .elementor-pagination a.page-numbers:hover{color:#0FA968!important;border-color:#0FA968!important;}'
						. 'selector .woocommerce-pagination .page-numbers.current,selector .elementor-pagination .page-numbers.current{color:#FFFFFF!important;background-color:#0FA968!important;border-color:#0FA968!important;}'
					),
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

	/*
	 * Archive conditions must name a sub-location: the runtime accepts
	 * include/product_archive/shop_page | /product_cat | /product_search, and the bare
	 * parent form does not register the template at all. This is the main shop page.
	 */
	return es_save_theme_part(
		'es-shop-archive',
		'Site - Product archive',
		'product-archive',
		$el,
		array( 'include/product_archive/shop_page' )
	);
}
