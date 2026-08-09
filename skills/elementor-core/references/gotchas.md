# NovaMira + Elementor (raw PHP) — hard-won gotchas

Every one of these cost real debugging time. Trust them.

## Deploy pipeline (the ONLY reliable order)
1. **Upload** files via `novamira/create-upload-link` + multipart `curl -F file=@`.
   Raw PUT `--data-binary` is Forbidden by hosting. Token expires (~20 min) and
   the connector intermittently returns "requires additional permissions" — just retry.
2. `require_once` the builder files, then **call the build function explicitly**.
3. For every touched post id: `delete_post_meta(id,'_elementor_css')` +
   `delete_post_meta(id,'_elementor_element_cache')` + `@unlink(uploads/elementor/css/post-<id>.css)` +
   `\Elementor\Core\Files\CSS\Post::create(id)->update()`.
4. Regenerate kit CSS: `\Elementor\Core\Files\CSS\Post::create(get_option('elementor_active_kit'))->update()`.
5. Regenerate Theme Builder conditions cache (see woocommerce skill).
6. **Verify server-side** — fetch `post-<id>.css` / the front HTML and `substr_count` the
   expected selectors. The browser is often policy-blocked from the sandbox domain, so
   this server-side grep is the only verification available. NEVER claim it works from data alone; say it's verified server-side.

## Sandbox auto-executes uploaded .php
Any `.php` dropped in `wp-content/novamira-sandbox/` runs immediately. Top-level build
logic there crashes the site ("error crítico"). FIX: wrap ALL logic in named functions
(`es_build_home()`…), upload defines-only, then `require_once` + call the function via `execute-php`.

## Deterministic element IDs (critical)
Random IDs desync the compiled CSS from the cached HTML → styles silently don't apply.
Use the seeded counter: `es_uid()` (global `$es_id_seed,$es_id_n`) + `es_uid_reset($seed)` per page.

## `_elementor_element_cache` (the "empty page" root cause)
This meta caches rendered HTML for 24h. After API writes the front serves stale/empty
HTML until it's deleted. Always delete it in the deploy step above.

## Missing kit/global CSS after clearing cache
Clearing caches can drop `global.css` → the whole site loses styling. Always regenerate
the active kit CSS (step 4).

## Verify widget/control names before using them
Introspect instead of guessing:
```php
$w=\Elementor\Plugin::instance()->widgets_manager->get_widget_types('<name>');
array_keys($w->get_controls());          // control keys
$w->get_controls()['<ctrl>']['options']; // valid select values
```
Names that bit us: archive widget is `wc-archive-products` (not `archive-products`);
`cart_type` value is `side-cart` (not `side`); button hover bg is `button_background_hover_color`
(not `background_hover_color`).

## es_size responsive JSON
`es_size(70,'%')` serializes to `{"unit":"%","size":70,"sizes":[]}` — the `sizes` key means a
naive `substr_count('"size":70}')` misses it. Grep with a regex or without the closing brace.

## Boxed container: layout goes on `.e-con-inner`, not the container
A container WITHOUT `content_width=full` (boxed) generates an inner wrapper `.e-con-inner`
that is the real flex row. Apply `justify-content` / `align-items` to `selector>.e-con-inner`,
NOT to `selector` — on `selector` they do nothing.

## Targeting containers: `_css_classes` doesn't stick to containers
`_css_classes` renders as a class on WIDGETS but NOT on containers. To target a specific
container, use structural selectors from the parent: `selector>.e-con:first-child`,
`selector>.e-con:last-child`. Don't rely on a class you set on a container.

## Mobile 3-zone header (burger · logo · cart) — `order` won't save you
Common DOM: `[logo][cluster: nav-menu(burger) + actions(cart)]`. Wanted on mobile: burger
left, logo centered, cart right, all vertically centered. Because burger and cart live nested
together in the cluster while the logo is a separate sibling, CSS `order` can't interleave them
(different parents). Do it mobile-only (`@media(max-width:767px)`), desktop untouched:
1. Topbar row → `position:relative!important;` (positioning context).
2. Logo (heading widget) → `position:absolute; left:50%; top:50%; transform:translate(-50%,-50%);
   z-index:1; width:auto; white-space:nowrap;` → exact center over the bar.
3. Cluster → `width:100%; margin-left:0;` and its `.e-con-inner` → `justify-content:space-between;
   align-items:center;` → burger left, actions right.
4. Actions container (last inner child) → `width:auto; flex:0 0 auto; justify-content:flex-end;`
   → pins the cart to the right edge.

### `.elementor-menu-toggle` centers itself with auto margin
The burger toggle ships its own horizontal `margin:auto` that centers it and ignores
`align-items`. Force `margin:0!important` to be able to align it.

### Closed nav dropdown breaks vertical centering
A CLOSED `.elementor-nav-menu--dropdown` stays `display:block` at height 0 with a `margin-top`,
pushing the toggle off-center. Pull it out of flow only while closed, without breaking the open
state: `selector .elementor-menu-toggle[aria-expanded="false"] ~ .elementor-nav-menu--dropdown{position:absolute!important;}`
(open uses `aria-expanded="true"` + `position:fixed` full-screen and still wins).

## Verify mobile layout without a screenshot
The sandbox domain is browser-policy-blocked AND the in-app browser pane doesn't compose frames
for a screenshot — but JS measurement works. Use the in-app browser with mobile emulation +
`javascript_tool` reading `getBoundingClientRect()` (x / right / vertical-center) to confirm the
3-zone alignment numerically. Bust `post-<id>.css` with a `?v=` query to dodge cache.
