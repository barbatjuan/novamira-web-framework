# House-rules checklist (the gate for the orchestrator's defaults)

The orchestrator's "House rules" (`agents/novamira-web-orchestrator.md`) had no verification
gate. This is it. Run every row. Report PASS / FAIL / UNVERIFIED per row — never blank.

How verification works here: the sandbox domain is usually browser-blocked, so checks fetch
the compiled CSS / front HTML server-side and count selectors and strings, or read WordPress
options directly. Rows marked **eyes** cannot be proven that way — ask the user and say so.
Never claim a visual result you did not see.

Run every per-page row on EVERY page in scope, not just the home.

| # | Rule | What to check | Server-side method | Verdict source |
|---|------|---------------|--------------------|----------------|
| 1 | Currency = euros | Store currency is `EUR` and the symbol rendered is `€` | Read the live option `woocommerce_currency` (and the active currency symbol). Then fetch a page with prices and count `€` vs `$` / `USD` in the price markup | **auto** |
| 2 | Cart is an icon + count badge | The header cart renders a glyph and a quantity bubble, and NO text label | Isolate the cart fragment in the header HTML (`elementor-menu-cart`). PASS needs: an icon node inside the toggle (`eicon-…` class or an inline `<svg>`) AND the quantity-indicator node. FAIL on any hit for `Carrito`, `Cesta`, `Bolsa`, `Bag`, `Cart` as visible text inside that fragment. Cross-check the widget config: `items_indicator:'bubble'`, `toggle_button_border_width:0` | **auto** |
| 3 | Theme | Elementor build on Hello Elementor; existing Astra / GeneratePress kept, not swapped; Divi build on the Divi theme | Read the active theme (`stylesheet` / `template` options). Compare against what `project-context` reported BEFORE the build — a changed value on an existing site is a FAIL | **auto** |
| 4 | Logo → home | The header logo is wrapped in an anchor whose href is the site home URL, on every page | Isolate the header fragment, take the anchor around the logo widget, compare its href to the live home URL. FAIL on `#`, empty, `javascript:`, or any non-home target. Repeat per page | **auto** |
| 5 | Exactly ONE menu | One nav-menu widget instance per page — no second nav, no duplicated item | Count nav-menu WIDGET instances in the header, not `<nav>` elements. Trap: one Elementor nav-menu widget renders TWO lists (`elementor-nav-menu--main` + `elementor-nav-menu--dropdown`); counting those gives a false FAIL. Confirm the exact widget attribute by introspecting the rendered header once, then record it in `elementor-core/references/knowledge.md`. Also dedupe the item labels/hrefs — a repeated href is a duplicated item | **auto** |
| 6 | No dead links | Every menu item resolves | Extract the hrefs from the menu markup, request each server-side (HEAD, follow redirects), flag anything not 2xx/3xx. Also flag `#` and empty hrefs | **auto** |
| 7 | Header present on every page | No page loses its header | Fetch every page in scope; the header fragment must be present in all of them. A missing header usually means a Theme Builder condition gap or a leftover template hijack — check conditions before reporting | **auto** |
| 8 | Header sticky | The header stays visible on scroll | Config only: confirm the sticky setting is stored on the header container (`sticky` = top, enabled for desktop/tablet/mobile). The sticky class is applied by JS at runtime, so the stored setting is the ONLY server-side proof — the behaviour itself is **eyes** | **auto (config) + eyes (behaviour)** |
| 9 | Header/footer reused verbatim | One global header and one global footer, byte-identical across pages | Extract the header fragment and the footer fragment from each page, normalise whitespace, hash them. All header hashes must match; all footer hashes must match. A mismatch = a per-page copy crept in. Also confirm there is ONE header template and ONE footer template with a single site-wide condition | **auto** |
| 10 | Mobile 3-zone header (burger · logo · cart) | Burger left, logo centred, cart right, all vertically centred, below 768px | Two layers. (a) Server-side: the mobile-only rules from `elementor-core/references/gotchas.md` ("Mobile 3-zone header") exist in the compiled `post-<id>.css` — count the positioning-context, absolute-centred logo, `space-between` cluster and pinned-actions rules. (b) Geometry: measure with the in-app browser under mobile emulation reading each zone's bounding rect (bust the CSS cache with a `?v=` query). Rules present but never measured = UNVERIFIED, not PASS | **auto (rules) + measured or eyes (layout)** |

| 11 | Fewest containers that do the job | No container nested "just because": none empty, none wrapping a single widget without carrying its own background/border/shadow, no section whose only child is a flex row (the section IS the row), no photo living as a container `background_image`, and nothing past depth 3 | `require_once` the sandbox's `es-builder.php` and **call `es_container_audit()`** on the `json_decode`d `_elementor_data` of each page AND each Theme Builder template in scope. Do NOT re-implement the walk here in prose or in a throwaway script: two implementations of one rule drift, and the one you hand-roll is the one that will be wrong. This runs against what actually LANDED, not what was sent — that is the whole reason it repeats a check the build already did. Report `containers / widgets / max_depth` per page and list every offender by path. PASS needs zero offenders. `optimizable` entries (a container whose only child is a grid) are reported as a **note**, never a FAIL — that call needs a human | **auto** |
| 12 | Exactly one H1 per page | Every page in scope has exactly one `<h1>`, and it describes that page | `wordpress-seo` states this as a Hard Rule but writes no checker, so it was unenforced. Fetch each page's front HTML and count `<h1` occurrences. FAIL on 0 (nothing tells a crawler what the page is) and on 2+. Also flag an H1 whose text is the site name on every page instead of the page's own subject — technically one H1, functionally none | **auto** |

| 13 | Accessibility is measured, not eyeballed | Lighthouse accessibility ≥ 90 on every page in scope, mobile | `node ../assets/lighthouse-audit.mjs <url…>` (from `qa-review/assets/`). It names the failing audits, not just the score: contrast, `image-alt`, `link-name`, `button-name`, `heading-order`, `html-has-lang`, `target-size`. Under 50 blocks. This REPLACES eyeballing step 4 — a11y was five rules with no method until this existed. Lighthouse cannot judge whether alt text is *meaningful*, only that it is present: that part stays **eyes** | **auto (+ eyes for alt quality)** |
| 14 | Best practices + SEO | Lighthouse best-practices ≥ 90 and SEO ≥ 90 | Same run. Catches console errors, mixed content, wrong image aspect ratios, a missing `<title>` or meta description, uncrawlable anchors. Under 50 blocks. Row 12 (one H1) still runs separately — Lighthouse does not check H1 count | **auto** |
| 15 | Performance is reported, never the sole blocker | Lighthouse performance score + LCP / CLS / TBT recorded for every page in scope | Same run. **Deliberately non-blocking**: mobile Elementor rarely reaches 90, and a gate that always fails is a gate everyone learns to skip. What matters is the before/after delta `wordpress-performance` owns — so record the number here and hand it over. A score from a sandbox host is not a score from the client's production host; say which one you measured | **auto (number) + judgement (verdict)** |

## Honest limits — do not dress these up

- **Row 1 is a live read, never an assumption.** No skill in this repo sets `woocommerce_currency`
  — grep confirms zero hits across every skill. A build inherits whatever the store already had,
  which on a fresh WooCommerce install is USD. So read the live value and flag USD; never infer
  euros from the fact that the design used `€`.
- **Row 8 behaviour, and row 10 geometry, are not provable from HTML/CSS text.** The stored
  setting and the CSS rules only prove intent. Report them as such.
- **Rows that depend on WooCommerce** (1, 2) do not apply to a build without WooCommerce — mark
  them N/A, not PASS.
- **Rows 13–15 need headless Chrome to REACH the URL.** Whether it reaches a NovaMira sandbox host
  is UNVERIFIED — the in-app browser is usually policy-blocked there, and headless Chrome is a
  different path that has not been tried yet. If it cannot connect, the script prints
  `UNREACHABLE` and runs nothing: report that as a tooling gap, never as a failing audit or a
  score of zero. Record the answer here the first time you find out.
- **`UNREACHABLE` is flaky on Windows — re-run before believing it.** Confirmed on the dev machine:
  the same public URL alternates between a clean run and
  `EPERM, Permission denied: …\Temp\lighthouse.<n>` with nothing changed in between, and two
  consecutive runs failed while the next succeeded. The script now uses a unique Chrome profile per
  attempt and retries three times with a backoff, which reduces it but does not eliminate it
  (antivirus holding the temp profile is the likely cause). Never report a site as unreachable off
  a single failed run.
- **A Lighthouse score is not a visual sign-off.** 100/100 on a page that looks broken is still a
  broken page. These rows add a floor, they do not replace the user's eyes.
- **The performance number is NOISY — treat one run as an estimate.** Five runs against the SAME url on the same
  machine scored 95, 74, 71, 69 and 97 — a 28-point swing from lab variance alone (LCP 2.5-5.0 s). Never report a
  performance delta from single runs on different machines or networks; take the best of three on
  the same machine, and say so. Accessibility, best-practices and SEO are near-deterministic —
  they inspect the DOM instead of timing it, which is why they are the ones that block.

## Divi

Rows 1, 3, 4, 6, 7, 9 read WordPress options or plain front HTML and are builder-agnostic —
they run unchanged on Divi.

Row 11 is builder-agnostic as a RULE but not as a METHOD: `es_container_audit()` reads
`_elementor_data`, which Divi does not have. On a Divi build, count nesting depth in the rendered
front HTML instead (`.et_pb_section` > `.et_pb_row` > `.et_pb_column` > module) and report it as
**UNVERIFIED** until the Divi storage format is confirmed and written into
`divi-core/references/gotchas.md`. Row 12 (one H1) reads plain front HTML — it runs unchanged.

Rows 2, 5, 8, 10 lean on Elementor artifacts (`elementor-menu-cart`, the nav-menu widget
attribute, the stored `sticky` control, `post-<id>.css`). The Divi equivalents are NOT confirmed
in this repo — `divi-core` is a scaffold and its own gotchas file lists its storage facts as
"verify before trusting". On a Divi build, report those rows as **UNVERIFIED**: not a PASS, not
a FAIL. Confirm the real Divi artifact names by introspection on the site, then append them to
`divi-core/references/gotchas.md` so the next run can actually check them.
