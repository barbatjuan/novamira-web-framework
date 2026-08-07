# WooCommerce widget knowledge (Elementor Pro)

## Widgets (native)
- Archive loop: `wc-archive-products` (columns / columns_tablet / columns_mobile, rows,
  paginate, product_title_color, price_color, button_background_color, pagination_color…).
- Arbitrary products: `woocommerce-products` (columns, rows, paginate, query_orderby='date',
  query_order='desc') — use on the home to show REAL products that link to `/producto/…`,
  not decorative cards.
- Single product: `woocommerce-breadcrumb`, `woocommerce-product-images`,
  `woocommerce-product-title`, `woocommerce-product-rating`, `woocommerce-product-price`,
  `woocommerce-product-short-description`, `woocommerce-product-add-to-cart`,
  `woocommerce-product-meta`, `woocommerce-product-data-tabs`, `woocommerce-product-related`.
- Header cart: `woocommerce-menu-cart`. Cart/checkout/account:
  `woocommerce-cart`, `woocommerce-checkout-page`, `woocommerce-my-account`.

## Menu cart controls
`cart_type:'side-cart'`, `side_cart_alignment:'right'`, `automatically_open_cart:'yes'`,
`automatically_update_cart:'yes'`, `items_indicator:'bubble'` (+ `items_indicator_background_color`),
`toggle_button_icon_color`, `toggle_button_border_width` (0 → icon only),
`product_title_color` / `product_price_color` / `product_quantity_color` / `subtotal_color`.
Full-screen on phones: `@media(max-width:767px){selector .elementor-menu-cart__container{width:100vw!important}}`.

## Shared products CSS
`es_products_css()` in `elementor-core/assets/es-builder.php` gives hover lift+zoom, accent
button, equal-height cards, hidden inline "view cart", and the added-state relabel. Reuse it
for archive, related, and home product grids. Add pagination-palette CSS on the archive only.

## Demo catalog
Simple products via `WC_Product_Simple` (idempotent by title), assign a category image as the
thumbnail so galleries are never blank. Mark fictional testimonials as design-only.
