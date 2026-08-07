# WooCommerce + Elementor Theme Builder — gotchas

## Leftover template hijack (looks "broken")
A single-product page that renders blank/ugly is often a STALE Theme Builder template from
a previous build overriding the loop — not a layout bug. Find product-type templates
(`_elementor_template_type` in `elementor_library`) and disable the old one: set to draft +
clear `_elementor_conditions` + regenerate the conditions cache. Products already had images.

## Theme Builder conditions cache (writing meta is not enough)
`_elementor_conditions` post-meta alone does NOT register a template. The runtime reads the
cache option `elementor_pro_theme_builder_conditions` (`{location:{post_id:[conds]}}`). After
setting meta, run
`\ElementorPro\Modules\ThemeBuilder\Module::instance()->get_conditions_manager()->get_cache()->regenerate()`
then VERIFY the option contains your location. The manager's `save_conditions()` throws
"Cannot unset string offsets" — do not use it; regenerate + verify instead.

## Document types & conditions
- Single product: document type `product`, location `single`, condition string `include/product`.
- Product archive: type `product-archive`, location `archive`, conditions
  `include/product_archive/shop_page` | `/product_cat` | `/product_search`
  (the generic `include/general` does NOT work for archives).

## cart_type value
`cart_type` must be `side-cart` (full-height right drawer) or `mini-cart`. `side` is invalid
and renders a broken small box.

## Side cart trapped in the header
The drawer is `position:fixed`; a header with `backdrop-filter` becomes its containing block
and traps it inside the header box. Move the header glass to a `::before` layer (see
`ux-design-system/references/motion.md`). Also `transform`/`filter`/`perspective`/`will-change`
on any ancestor causes the same trap.

## Added-state + inline "view cart"
After AJAX add, the button gets class `.added`. Relabel with CSS (no JS):
`a.button.added{font-size:0} a.button.added::after{content:"Añadido ✓";font-size:13.5px}`.
Hide the redundant inline link: `a.added_to_cart{display:none!important}`.
