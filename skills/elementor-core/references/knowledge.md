# Elementor knowledge (stable)

## Helper library API (`assets/es-builder.php`)
- `es_uid()` / `es_uid_reset($seed)` — deterministic seeded element IDs.
- `es_c($settings,$children,$inner=true)` — container (elType container). `es_w($type,$settings)` — widget.
- `es_size($n,$unit='px')` — slider value. `es_box($t,$r,$b,$l)` — dimensions.
- `es_section($children,$opts)` — boxed section with responsive padding (column direction).
- `es_split($children,$opts)` — TWO-COLUMN section: the section IS the row. `row` on desktop,
  `column` at tablet/mobile, children direct. Replaces `es_section( es_row(...) )` and its
  wasted level. `$opts`: bg, gap, align, reverse, settings.
- `es_wide($el,$pct,$mobile=100)` — width ON the element (`_element_width:'initial'` +
  `_element_custom_width`) instead of a wrapper container. Works on widgets and containers.
- `es_photo($slug,$height,$extra)` — image widget with `object-fit:cover`. Use instead of a
  container `background_image`: keeps the `alt`, saves a container.
- `es_grid($cols,$children,$gap,$extra)` — grid container (rows forced to `auto`, see gotchas).
- `es_row`, `es_eyebrow`, `es_h`, `es_p`, `es_btn($text,$link,$style,$extra)`
  (styles: primary / dark / outline / outline-light), `es_card`, `es_feature_card`, `es_iconbox`.
- `es_save_page($slug,$title,$elements,$tpl,&$action)` + `es_rebuild_css($post_id)`.
  `$action` reports FOUR outcomes: `created`, `updated`, `created-renamed` (WordPress published the
  page under a DIFFERENT slug because the one you asked for was taken — the URL you expect is not
  this page), `failed` (nothing was written; the return value is `0`). Anything other than
  `created`/`updated` needs a human. All three unhappy paths also speak through `es_warn()`, so
  they reach stdout under `ES_AUDIT_SILENT` and the durable log. Do NOT treat a returned id as
  proof the page went where you asked. `es_save_theme_part()` reports the same four.
- `es_overwrite_preflight($slugs)` → `{rows[], overwrites, creates}` and PRINTS the block a human
  approves — never gated on `ES_AUDIT_SILENT`, an approval artifact is not routine output. Run it
  **before the first write**. Each row: `slug`, `id`, `action`, `status`, `is_elementor`,
  `is_front_page`, `converts`. The last two cost the most and show up the least.
- `es_backup_page_state($id,$keys)` — parks the WHOLE displaced set (layout, page template, edit
  mode, template type, version, post fields, and `post_content`) in `_es_page_backup_<Ymd-His>`.
  **Call it before the first write**: it cannot tell an old value from a new one, and it used to
  run after four of the five keys had already been overwritten, preserving what had just been
  written. Restore key by key. `es_save_page()` calls it only when updating — a page it just
  created has nothing to displace.
- `es_front_page()` → `{mode:'posts'|'page', id, slug}` — the ONE resolver for "what does `/` serve".
  Never guess the home from a slug: on an install whose front page is `/`, `/inicio/` is dead.
  `page_on_front` alone is NOT a front page; without `show_on_front='page'` WordPress renders
  the blog. `es_set_front_page($slug)` points it at a page and READS THE OPTIONS BACK, returning
  `0` and warning if they did not land — `update_option()` returns false both on failure and on an
  unchanged value, so its boolean proves nothing either way. Repointing an existing front page
  warns naming the page that stops being shown; that page stays published, it just stops being
  the one anybody lands on. **Call it once the home is saved**, and hand the id to `qa-review`
  row 16 — the options say WHICH page is the front page, never whether it is the right one.
- `es_img($slug)` — attachment lookup by slug → url+id.
- `es_container_audit($elements)` →
  `{containers,widgets,max_depth,offenders[],optimizable[],unaudited{elType:{count,first}}}`.
  `es_container_report($elements,$label)` echoes to stdout AND `error_log()`s, returns the same
  array; `es_save_page()` calls it automatically before writing.
  `es_audit_summary()` → one verdict line for the whole run. The LINE is the artifact; the int is
  for branching: `0` clean, `>0` offender count, `-1` nothing was audited (a wiring bug — it used
  to return 0, same as a pass), `-2` part of the tree uses elTypes the audit cannot judge. `-2`
  wins. Branch on the INTEGER, never on a word found in the line: your page label is interpolated
  into the line's deep-nesting suffix, so a page can put any text of its own there.
  `-1` speaks through `es_warn()`, so it reaches stdout even under `ES_AUDIT_SILENT`: silencing the
  routine report must never silence "the report never ran".
  **Call it at the end of every build function** — the per-page lines scroll past, the verdict
  is what the deploy step reads. `ES_AUDIT_SILENT` mutes the audit REPORT — the per-page lines and
  the verdict — and nothing else. It does NOT reach `es_warn()`: silencing routine output must
  never silence a warning.

## Containers, flex, grid
- Layout with flex + grid containers, not the legacy section/column. `content_width` boxed|full.
- **Fewest containers that do the job** (house rule). A container earns its place only by grouping
  2+ children, carrying its own background/border/shadow, changing direction at a breakpoint, or
  boxing a lone widget no ancestor already boxes — Elementor gives a widget no other way to sit at
  the boxed content width, so there the wrapper IS the mechanism.
  Target depth `section → grid|row → widget`. Padding alone is never a reason to exist — put it on
  the widget's `_padding`. `es_container_audit()` measures this; read its log line, and read the
  `NO AUDITABLE` block too: pre-3.6 `section`/`column` elTypes and kit imports are elements this
  audit has no opinion about. They are counted and named there rather than skipped, because a page
  built entirely of them used to measure 0 containers / 0 widgets / depth 0 and read as clean.
- Open question worth resolving on a real site: `es_section( es_grid(...) )` is this repo's dominant
  idiom and costs one level. A single grid container with `content_width:'boxed'` plus the section
  padding *should* collapse the pair into one. Plausible, NOT confirmed — the audit reports it as
  `optimizable`, never as an error. Confirm on a live build before flattening anything wholesale,
  then record the result here.
- Flex item sizing: `_flex_grow` / `_flex_shrink` / `_element_width:auto`. To keep a cluster from
  stretching, set grow/shrink 0 and DON'T set `content_width:full` on it (that forces ~100% width).
- Grid columns: `grid_columns_grid` (+ `_tablet` / `_mobile`). For a 2-col mobile grid pass
  `grid_columns_grid_mobile => {unit:fr,size:2}`.

## Breakpoints & responsive keys
- Per-device suffixes: `_tablet`, `_mobile` on most controls (`width_mobile`, `align_mobile`,
  `flex_justify_content_mobile`, `flex_wrap_mobile`, `padding_mobile`, typography sizes…).
- Visibility: `hide_desktop:'hidden-desktop'`, `hide_tablet`, `hide_mobile`.
- Button full width inside its container: `align => 'justify'` (or force `.elementor-button{width:100%}`).

## Global kit
- Kit id = `get_option('elementor_active_kit')`. Set global colors/typography/buttons there so
  the whole site inherits. Regenerate kit CSS after cache clears.

## Control names that are easy to get wrong (introspect to confirm)
- Archive products widget: `wc-archive-products` (NOT `archive-products`).
- Button hover: `button_background_hover_color`, `hover_color`, `button_hover_border_color`
  (NOT `background_hover_color`).
- Menu cart: `cart_type` = `side-cart` | `mini-cart`; `automatically_open_cart:'yes'`;
  item colors `product_title_color` / `product_price_color` / `product_quantity_color`.
- image-box: `image_size` = width slider (%), `thumbnail_size` = WP file size,
  `image_height` + `image_object_fit`.
- Introspect anything unsure:
  `array_keys(\Elementor\Plugin::instance()->widgets_manager->get_widget_types('<name>')->get_controls())`.
