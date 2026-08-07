---
name: woocommerce
description: "Trigger: WooCommerce, shop page, product page, side cart, mini cart, checkout, my account, product archive, add to cart. Build premium WooCommerce storefront pieces via NovaMira with native widgets."
license: Apache-2.0
metadata:
  author: "juan"
  version: "1.0"
---

# WooCommerce

Build the storefront: shop archive, single product, side cart, and the account/checkout
flows. Native widgets only. Uses the active builder's core skill (`elementor-core` /
`divi-core`) to emit and deploy; this skill owns the commerce-specific structure and gotchas.

## Activation Contract
Use when `project-context` reports WooCommerce active and the task touches shop, product,
cart, checkout, or my-account. Deploy through the builder-core pipeline.

## Hard Rules
- Native commerce widgets only; no custom JS. Style the CTA to the accent color with
  `!important` (the theme's button color otherwise wins).
- Product-card grids must be equal height (see `ux-design-system/references/motion.md`).
- A leftover Theme Builder template can hijack a page and look "broken" — check for and
  disable stale single-product/archive templates before blaming layout.
- **Read `references/gotchas.md` before building templates** (Theme Builder conditions are subtle).

## Execution Steps
1. **Shop archive**: a `product-archive` Theme Builder template using the archive-products
   widget (columns, pagination in palette). Model on `assets/es-shop-template.example.php`.
2. **Single product**: a `product` Theme Builder template (breadcrumb → gallery + buy box →
   tabs → related), condition `include/product`. Model on `assets/es-product-single.example.php`.
3. **Side cart**: the native menu-cart widget in the header — `cart_type:'side-cart'`,
   `automatically_open_cart:'yes'`; full-screen on phones via scoped custom CSS; fix item-name
   contrast with `product_title_color` etc.
4. **Relabel added state / hide inline "view cart"** via the shared products CSS
   (`es_products_css()` in `elementor-core/assets/es-builder.php`).
5. Register + regenerate Theme Builder conditions, then verify server-side.

## Output Contract
Report templates built (ids), conditions registered, and the server-side checks (front HTML
uses `elementor-<id>`, gallery/tabs/related present). Note WC-native limits (e.g. single-product
add-to-cart is a form submit unless AJAX-add is enabled).

## References
- `references/knowledge.md` — widget names, template types, cart controls, products CSS.
- `references/gotchas.md` — Theme Builder condition cache, leftover templates, cart trapping.
- `assets/es-shop-template.example.php`, `assets/es-product-single.example.php`.
