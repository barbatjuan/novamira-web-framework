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
