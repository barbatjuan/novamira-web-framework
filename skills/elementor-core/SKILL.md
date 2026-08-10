---
name: elementor-core
description: "Trigger: Elementor via PHP, NovaMira, es-builder, _elementor_data, deploy Elementor page, theme builder, sandbox php. Generate Elementor layouts as raw-PHP JSON and deploy them reliably. Battle-tested."
license: Apache-2.0
metadata:
  author: "juan"
  version: "1.0"
---

# Elementor Core (execution)

Build Elementor pages by writing raw PHP that emits `_elementor_data` JSON, deployed
through the NovaMira connector. Owns the helper library, the deploy pipeline, and the
Elementor failure modes. Takes its visual spec from `ux-design-system`.

## Activation Contract
Use when `project-context` reports builder = `elementor` and work runs through NovaMira
`execute-php` / `create-upload-link` (not the Elementor UI).

## Hard Rules
- Native Elementor / Elementor Pro widgets only. No third-party widgets. No custom JS.
- Custom CSS ONLY via the widget's native `custom_css` field (`selector{}`) — it always
  compiles; conditionally-enqueued assets (hover animations, swiper) do not.
- Deterministic IDs: `es_uid_reset('<page>')` once per page, `es_uid()` per element.
- Wrap all build logic in named functions — the sandbox auto-runs any uploaded `.php`.
- **Read `references/gotchas.md` before the first deploy.** Introspect widget/control names;
  never guess them (`references/knowledge.md` lists the ones that bit us).

## Execution Steps
1. Copy `assets/es-builder.php` into `wp-content/novamira-sandbox/` as the shared library;
   swap the palette/type constants at the top for the brand from `ux-design-system`.
   Building header/footer, Theme Builder parts, or ANY commerce template? Also copy
   `assets/es-theme-parts.example.php` as `es-theme-parts.php` (drop `.example`) — it defines
   `es_save_theme_part()`, which those files require. Upload dependencies BEFORE the file that
   needs them: sandbox `.php` executes on upload, so a missing dependency stops the run.
2. Write one `es_build_<page>()` per page → `es_save_page(...)`, which defaults to the
   `elementor_header_footer` template so the global header/footer survive. Header/footer +
   Theme Builder templates: model on `assets/es-theme-parts.example.php`, and read the
   "Mobile 3-zone header" recipe in `references/gotchas.md` before building a header.
   Commerce templates: see `woocommerce`.
3. Deploy with the pipeline in `references/gotchas.md`
   (upload multipart → require+call → clear `_elementor_css` + `_elementor_element_cache` +
   post CSS file → regenerate kit CSS → regenerate conditions cache → verify).
4. Verify server-side: fetch compiled `post-<id>.css` / front HTML, `substr_count` the expected
   selectors. State that visual confirmation needs the user.

## Output Contract
Report pages/templates built (ids), the server-side grep counts that prove the styles landed,
and any control names introspected. Capture new failure modes into `references/gotchas.md`.

## References
- `references/gotchas.md` — deploy order, cache metas, sandbox auto-exec, name corrections.
- `references/knowledge.md` — helper API, containers/flex/grid, breakpoints, global kit, control keys.
- `assets/es-builder.php` — helper library. `assets/es-theme-parts.example.php` — header/footer.
