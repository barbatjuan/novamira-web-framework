---
name: elementor-core
description: "Trigger: Elementor via PHP, NovaMira, es-builder, _elementor_data, deploy Elementor page, theme builder, sandbox php. Generate Elementor layouts as raw-PHP JSON and deploy them reliably. Battle-tested."
license: Apache-2.0
metadata:
  author: "juan"
  version: "1.3"
---

# Elementor Core (execution)

Build Elementor pages by writing raw PHP that emits `_elementor_data` JSON, deployed
through the NovaMira connector. Owns the helper library, the deploy pipeline, and the
Elementor failure modes. Takes its visual spec from `ux-design-system`.

## Activation Contract
Use when `project-context` reports builder = `elementor` and work runs through NovaMira
`execute-php` (not the Elementor UI).

**Build gate — blocking.** This skill writes to a live WordPress site. Do not run until the user
has given an explicit **yes** for THIS build. Reached directly instead of routed by the
orchestrator? Ask for that yes yourself before the first write and stop until you get it.
On an existing site, confirm every page/template you would overwrite by name first.

## Hard Rules
- Native Elementor / Elementor Pro widgets only. No third-party widgets. No custom JS.
  (no verifier: caught by the step-4 front-HTML fetch — an unregistered widget renders empty.)
- Custom CSS ONLY via the widget's native `custom_css` field (`selector{}`) — it always
  compiles; conditionally-enqueued assets (hover animations, swiper) do not. Verified by the
  step-4 `post-<id>.css` grep: rules that never compiled are simply absent from it.
- **Fewest containers that do the job.** One earns its place only by grouping 2+ children,
  carrying its own background/border/shadow, or changing direction at a breakpoint. Three
  helpers make the flat shape the easy one: `es_split()` (the section IS the row — never
  `es_section( es_row(...) )`), `es_wide($el,58)` (a width is not a container), `es_photo()`
  (a photo is a widget, not a `background_image`). Target depth `section → grid|row → widget`.
- **Read the audit verdict.** `es_save_page()` prints `es_container_report()` to stdout before
  writing; end every build function with `es_audit_summary()` and fix `VEREDICTO A CORREGIR`
  before deploying. `optimizable` is a judgement call, not an error. `qa-review` row 11 re-runs
  the same audit on what landed. Detail: `references/gotchas.md` → "Container hygiene".
- Deterministic IDs: `es_uid_reset('<page>')` once per page, `es_uid()` per element.
- Wrap all build logic in named functions — the sandbox auto-runs any uploaded `.php`.
  Self-verifying: top-level logic fatals the site on upload, before `execute-php` is reached.
- **Read `references/gotchas.md` before the first deploy.** Introspect widget/control names;
  never guess them (`references/knowledge.md` lists the ones that bit us).

## Execution Steps
1. Copy `assets/es-builder.php` into `wp-content/novamira-sandbox/`; swap its palette/type
   constants for the brand from `ux-design-system`. For header/footer, Theme Builder parts or ANY
   commerce template also copy `assets/es-theme-parts.example.php` as `es-theme-parts.php` — it
   defines `es_save_theme_part()`. Upload dependencies FIRST: sandbox `.php` runs on upload, so a
   missing one stops the run.
2. Write one `es_build_<page>()` per page → `es_save_page(...)`, which defaults to the
   `elementor_header_footer` template so the global header/footer survive. Header/footer +
   Theme Builder templates: model on `assets/es-theme-parts.example.php`, and read the
   "Mobile 3-zone header" recipe in `references/gotchas.md` before building a header.
   Commerce templates: see `woocommerce`.
3. Deploy with the pipeline in `references/gotchas.md` (upload multipart → require+call → clear
   `_elementor_css` + `_elementor_element_cache` + the post CSS file → regenerate kit CSS and the
   conditions cache → verify).
4. Verify server-side: fetch compiled `post-<id>.css` / front HTML, `substr_count` the expected
   selectors. State that visual confirmation needs the user.

## Output Contract
Report pages/templates built (ids), the audit verdict line, and the server-side grep counts that
prove the styles landed. Capture new failure modes into `references/gotchas.md`.

## References
- `references/gotchas.md` — deploy order, cache metas, sandbox auto-exec, name corrections.
- `references/knowledge.md` — helper API, containers/flex/grid, breakpoints, global kit, control keys.
- Assets: see step 1.
