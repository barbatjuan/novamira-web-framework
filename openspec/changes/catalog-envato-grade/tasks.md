# Tasks: catalog-envato-grade

> **Size-budget note**: the `sdd-tasks` skill caps this artifact at 530 words. This change spans
> 5 capabilities, ~23 archetype files, a 17k-line generator, and an explicit per-PR forecast +
> TDD-pairing requirement from the orchestrator's task brief. Honoring that explicit brief (files
> touched, verification command, TDD pairing, per-slice forecast, human-decision flags) for a
> change this size cannot fit in 530 words. This document exceeds the cap deliberately; the
> alternative (omitting files/verification/TDD-pairing) would silently fail the actual request.
>
> **Config note**: `openspec/config.yaml:54` states a 400-line review budget. The session
> preflight value is **800** and wins for this change. The file is intentionally left unedited —
> a later reader diffing this doc against `config.yaml` is not looking at a bug.

## Review Workload Forecast

| Field | Value |
|---|---|
| Estimated changed lines | ~500 (PR1) + ~450/-5,000 (PR2b) + ~30 (PR2c) + ~700 (pilot) + 5×~150 (archetype tables) + 2×~500 (new archetypes) + ~200 (Phase 4) |
| 800-line budget risk | **High** for PR2b only (mechanical deletion volume); Low–Medium elsewhere |
| Chained PRs recommended | Yes |
| Suggested split | PR1 → PR2a → PR2b → [PR3 pilot → PR4..PR8 survivor tables → PR9/PR10 new archetypes] → PR2c (ratchet, moved — see Sequencing) → PR11 (Phase 4) |
| Delivery strategy | auto-chain |
| Chain strategy | feature-branch-chain — tracker `feat/catalog-envato-grade` off `main @ 35a38b4`; PR1 targets the tracker, each later PR targets the immediately previous PR's branch; only the tracker merges to `main` |

```text
Decision needed before apply: Yes
Chained PRs recommended: Yes
Chain strategy: feature-branch-chain
400-line budget risk: High
```

`Decision needed: Yes` is scoped to PR2b and the three items below — `auto-chain` still proceeds
with PR1/PR2a immediately; the flagged slices pause for sign-off when reached.

**Slices needing a human decision before apply:**
| Slice | Why |
|---|---|
| PR2b | Diff exceeds 800 lines by design (see below) — needs sign-off that this is accepted as mechanical deletion, not `size:exception` (explicitly forbidden) |
| PR3b (delao brand) | Accent `#756547` must be re-verified with the generator's own `contrast()`, not hand math, before commit; new photo set needs sourcing (Freepik AI/Pikaso/Magnific per proposal Dependencies); responsive behaviour is still "pendiente de definir" and the build gate MUST reject that |
| PR5 (alinea) | Photo set 3→~7 + re-brief orthodontics→wellness coaching breaks the "zero new photography" claim — needs explicit acceptance, not silent scope creep |

**PR2b's line count is not `size:exception`.** Authored *new* lines (Registers table rewrite,
`recommender.md`/`_README.md` disposition rewrite, N/R commit-body print) are ~150–250. The
remaining ~5,000 lines are full-file deletions of archetypes `RT_TPL_NO_ENVOLTORIO` (landed in
PR2a, at WARN) has already proven carry no `Envoltorio` table and no demo brand — the same
argument the design made for `size:exception`, but delivered as a mechanical follow-on to a gate
that already fired, not as an unreviewed bulk PR. Flagging it for sign-off (not silently merging)
is the difference from `size:exception`.

## Corrections Carried Into This Checklist (verified against source, not the approved plan)

- **T-C1**: survivors = 7 (`TPL-C-07` aranda, `TPL-C-11` alinea, `TPL-C-14` lumiere, `TPL-E-06`
  corte, `TPL-E-07` bajura, `TPL-E-08` tueste, `TPL-E-09` medida), not 1. 16 die, not 22.
- **T-C2**: `tpl_envoltorio_table()` allows **at most one** catch-all row (first cell not
  `COMP-*`), mapped to `'*'`; a second catch-all is a parse FAIL. 6 of the 7 existing tables use a
  catch-all; only `TPL-C-14` names a `COMP-*` id on every row.
- **T-C3**: only `TPL-C-14` and `TPL-E-07` have an `Envoltorio` table today. `TPL-C-07`,
  `TPL-C-11`, `TPL-E-06`, `TPL-E-08`, `TPL-E-09` need theirs authored from zero — one PR each.
- **T-C4**: `specs/catalog-wrapper-integrity/spec.md`'s "Every Pre-Existing Envoltorio Table..."
  requirement wrongly lists `TPL-E-07` among "carries no demo brand" archetypes. It backs `bajura`
  (`_build-gallery.php:6148`) and survives. Fix the list to 4 members.
- **T-C5 (new, found while writing this checklist)**: `RT_GALLERY_SINGLE_PAGE_DEMO` cannot ship
  at FAIL in PR2b. `TPL-E-09` (medida) is **already** a single-page demo today — its `TPL-PDP-05`
  render died with `TPL-E-03` and is only rescued in PR8. Shipping this row at FAIL in PR2b would
  hard-block the audit on a pre-existing condition PR2b does not fix. **Ship it at WARN in PR2b,
  ratchet to FAIL alongside PR2c** (both gated on PR8 landing).
- **T-C6 (new, sequencing)**: **PR2c cannot run right after PR2b.** After PR2b, `RT_TPL_NO_ENVOLTORIO`
  still WARNs for the 5 T-C3 survivors (their tables don't exist until PR4–PR8) plus whichever of
  `TPL-C-15`/`16`/`17` haven't shipped yet. Flipping WARN→FAIL before all 10 final home archetypes
  carry a table would hard-FAIL the build on work not yet done. **PR2c is sequenced after PR10**,
  not after PR2b — see the plain "2c" label kept for traceability to the user's three-slice decision,
  physically executed last.

---

## PR1 — Phase 1 contracts (spec-only, no deletion)

Files: `skills/web-templates/SKILL.md`, `skills/web-templates/references/lanes.md` (NEW),
`skills/web-templates/references/toggles.md`, `skills/web-templates/references/recommender.md`,
`agents/novamira-web-orchestrator.md`, `skills/html-mockup/references/handoff-block.md` (NEW),
`skills/framework-audit/assets/framework-audit.php`, `tests/test-framework-audit.php`,
`CONTRIBUTING.md`, `openspec/changes/catalog-envato-grade/specs/catalog-wrapper-integrity/spec.md`.

- [x] 1.1 [RED] `tests/test-framework-audit.php`: failing test — `RT_RECOMMENDER_NO_LANE_FORK` FAILs
      when `recommender.md`'s Flow has no "no match → bespoke" step after step 3.
- [x] 1.2 [RED] failing test — `RT_RECOMMENDER_PROMOTION_GATE_MISSING` FAILs when no promotion
      criterion is named in `recommender.md`/`web-templates/SKILL.md`.
- [x] 1.3 [RED] failing test — `RT_ORCH_NO_GALLERY_STEP` FAILs when the orchestrator's routing map
      names no gallery-consultation step.
- [x] 1.4 [GREEN] implement the three rows in `framework-audit.php`; register in `ROW_TYPES`
      (`:48`) and `CONTRIBUTING.md` (else `RT_ROWTYPE_UNDOCUMENTED` fires, `:3491`).
- [x] 1.5 Write `skills/web-templates/references/lanes.md` (NEW) — two-lane resolution + promotion
      gate (`RT_TPL_NO_WIREFRAME`, `RT_TPL_UNROUTABLE`, `RT_TPL_TOO_SIMILAR`, `RT_TPL_NO_ENVOLTORIO`,
      `RT_TPL_WRAPPER_DUPLICATE`).
- [x] 1.6 Update `web-templates/SKILL.md` invariant text to point at `lanes.md`. Body stays ≤600w
      (today 559) — new prose goes in `references/`, none inline.
- [x] 1.7 `recommender.md`: add the explicit bespoke fork after step 3 (lane fork only — the
      archetype map itself is rebuilt in PR2b once survivors are final).
- [x] 1.8 `agents/novamira-web-orchestrator.md`: add the gallery-consultation step + lane fork to
      the routing map and "Order that works", before `web-templates` commits to an archetype.
- [x] 1.9 Write `skills/html-mockup/references/handoff-block.md` (NEW) — 8-field structured handoff
      (lane, anchor id, brand overrides, photo slugs, `--container-max` ref, contrast result,
      responsive behaviour, page set) per `mockup-handoff-persistence` spec.
- [x] 1.10 Fix `specs/catalog-wrapper-integrity/spec.md` Requirement 5 (T-C4): drop `TPL-E-07` from
      the "carries no demo brand" list; it backs `bajura`. List becomes 4: `TPL-C-03`, `TPL-C-05`,
      `TPL-C-06`, `TPL-E-01`.
- [x] 1.11 Verify: `php skills/framework-audit/assets/framework-audit.php && php tests/test-framework-audit.php`
      → 0 FAIL, WARN unchanged at 4 (no archetype touched yet), tests ≥1193 OK.

---

## PR2a — Envoltorio detector lands at WARN

Files: `skills/framework-audit/assets/framework-audit.php`, `tests/test-framework-audit.php`,
`CONTRIBUTING.md`.

- [x] 2a.1 [RED] fixture tests for `env_shape()`: `banda` (tested first) → `bleed`; `fila` → `row`;
      else → `contained`.
- [x] 2a.2 [RED] fixture test: a row whose first cell is not `COMP-*` is the catch-all, mapped to
      `'*'`; unlisted sections default to `contained` (matches `sec_open()`'s own default,
      `_build-gallery.php:15468`).
- [x] 2a.3 [RED] fixture test: a **second** catch-all row is a parse FAIL (own message, reuse the
      three-causes-one-row-id shape at `:1263-1279`).
- [x] 2a.4 [RED] fixture test: no `Envoltorio` table on a home `TPL-*.md` → `RT_TPL_NO_ENVOLTORIO`
      fires at **WARN** (not FAIL) for this slice.
- [x] 2a.5 [RED] acceptance test: all 7 existing tables (`TPL-C-03/05/06/13/14`, `TPL-E-01/07`)
      parse with no WARN, against `main @ 35a38b4` bytes unmodified; `TPL-C-14` named explicitly.
- [x] 2a.6 [GREEN] implement `tpl_envoltorio_table($src)` — table found by a header cell reading
      exactly `Envoltorio` (mirrors `gallery_register_count()`, `:2478`), column index from header.
- [x] 2a.7 [GREEN] implement `env_shape($raw)` and `tpl_wrapper_signature($src)` per design.
- [x] 2a.8 [GREEN] register `RT_TPL_NO_ENVOLTORIO` (WARN) in `ROW_TYPES` + `CONTRIBUTING.md`.
- [x] 2a.9 [GREEN] implement `RT_TPL_WRAPPER_DUPLICATE` at **FAIL** (no ratchet needed — it only
      compares files that already have a table; family-scoped via `$tpl_families`, `:1212`; a file
      that fired `RT_TPL_NO_ENVOLTORIO` is reported then excluded, `:1258-1261` discipline).
- [x] 2a.10 Verify: `php skills/framework-audit/assets/framework-audit.php && php tests/test-framework-audit.php`
      → 0 FAIL, WARN rises 4→20 (16 new `RT_TPL_NO_ENVOLTORIO`, expected/transitional, not a
      regression), tests ≥1193 OK.

---

## PR2b — Amputation + gallery IA (human decision required — see forecast)

Files: `framework-audit.php`, `tests/test-framework-audit.php`, `CONTRIBUTING.md`, 16 archetype
`.md` deletions under `templates/corporate/` and `templates/ecommerce/`, `recommender.md`, both
`_README.md`, `_build-gallery.php`, `_gallery-images.md`.

- [x] 2b.1 [RED] tests for `RT_GALLERY_AXIS_LEAK` (strip with no `$BRANDS` key), `RT_GALLERY_REGISTER_COUNT_MISMATCH`
      (`gallery_register_count() < count($BRANDS)`), `RT_MOCKUP_CONTAINER_FORK` (container-max
      literal ≠ `design-system.md:138`), `RT_GALLERY_ACCENT_TEXT_FAIL` (accent <4.5:1 re-measured
      against `$BRANDS` + drift check on the `4.5`/`7.0` literals at `:659-686`) — all FAIL.
      DONE: 5 new scenarios, RED verified via `git stash` of the implementation (9 FAIL), GREEN
      after restore (748 OK / 0 FAIL). Commit `d81ebcf`.
- [x] 2b.2 [RED] test for `RT_GALLERY_SINGLE_PAGE_DEMO` at **WARN** (T-C5 — medida already violates
      it; do not ship at FAIL here). DONE: included in the same RED/GREEN cycle as 2b.1, same commit.
- [x] 2b.3 [GREEN] implement all five rows; register in `ROW_TYPES` + `CONTRIBUTING.md`. DONE, commit `d81ebcf`.
      `RT_GALLERY_AXIS_LEAK`/`RT_GALLERY_REGISTER_COUNT_MISMATCH` read the BUILT gallery's own
      `data-brand` attribute rather than re-parsing `$STRIPS`/`$BRANDS` PHP source (matches this
      audit's stated "built site" scope); `RT_GALLERY_ACCENT_TEXT_FAIL` does parse `$BRANDS` from
      PHP source, since it independently re-measures rather than trusting the built output.
- [x] 2b.4 **Harvest before delete**: copy `$CONTENT['TPL-E-03']['mtm']` (`_build-gallery.php:4466`)
      to a staging key (e.g. `$CONTENT['_harvest']['tpl-e03-mtm']`) in its own commit, ahead of the
      TPL-E-03 content-block removal in 2b.6. DONE, commit `9cd1bca`, as an isolated `$HARVEST`
      global rather than the illustrative `$CONTENT['_harvest']` key: `$CONTENT` is walked by a
      validation loop requiring every entry to carry `tpl`/`arch`/`brand`/`head_mode`, and a staging
      entry nested under `$CONTENT` fails that walk on the very next build. In the 2b.6 commit, once
      `TPL-E-03` is actually deleted, the live reference is inlined into a standalone array literal
      (same bytes, no longer a pointer to a key that no longer exists).
- [x] 2b.5 Delete the 16 non-surviving `TPL-*.md` files; rewrite `recommender.md`'s active set to
      the 7 survivors; record `TPL-C-13`'s disposition explicitly (deleted, named replacement
      `TPL-C-15 · Cartera curada`, arriving PR3a) per `catalog-wrapper-integrity` Requirement 5.
      Rewrite both `templates/corporate/_README.md` and `templates/ecommerce/_README.md`
      dispositions to match. DONE, commit `c2bb1d1`.
- [x] 2b.6 Prune `_build-gallery.php`: orphaned render functions/`$CONTENT`/`$STRIPS`/`$PAGES`/
      `$BRANDS` entries for the 16 dead archetypes (TPL-E-03's block only after 2b.4's harvest);
      move the 55 axis-proof strips out of the catalogue gallery surface (`RT_GALLERY_AXIS_LEAK`
      target). DONE, commit `c2bb1d1`, via a token-aware (`token_get_all()`) deletion script, never
      line-based regex. Deleted 55 `$CONTENT` entries (16 top-level + 39 appended inner-page
      statements), 60 of 67 `$STRIPS` entries, 16 `$PAGES` entries, 5 `$BRANDS` entries, 15 of 16
      `strip_XXX()` home-render functions and their 34 dangling `render_page_inner()` dispatch
      blocks. `strip_property()`/`TPL-C-13`'s four dispatch blocks are the one deliberate exception
      — left untouched for PR3a to re-key in place, per the orchestrator's explicit citation of that
      code region. Discovered mid-task: three unconditional, build-time contrast-sweep mechanisms
      (`$BG_SLUG`, `$SLIDER_FRAMES`, `$INK_SWATCH`) hard-depend on five house images
      (`hero-cantera`, `hero-taller`, `hero-encimera`, `sq-marmol`, `sq-pizarra`) regardless of which
      archetypes exist; all five are kept alive in `_gallery-images.md` for that reason alone, not
      because any surviving archetype renders them.
- [x] 2b.7 `_gallery-images.md`: **same commit as 2b.6** — replace the 4-row house Registers table
      (`:148-159`) with a 10-row per-demo table (one per final demo, `lumiere`…`medida`, including
      placeholders for `delao`/lawyers/gyms not yet built). Delete the 15 `inmo-*` rows and retired
      brands' manifest rows. Commit body MUST print `N` (surviving manifest rows) and
      `R=10` and `ceil(N/R)`; if any `fp-` shoot exceeds the cap, diversify the shoot — do not
      retune `R`. DONE, commit `c2bb1d1`. **N=55, R=10, ceil(N/R)=6, largest shoot bucket=2** (well
      under the cap; no diversification needed). N is 55, not the ~51 estimated while writing this
      checklist, because of the five generator-dependency images discovered during 2b.6 (see above)
      that stay in the manifest with no owning archetype. A separate two-line `Row/Slug` table for
      the Material-pair requirement (`sq-marmol`/`sq-pizarra`) is kept OUTSIDE the 10-row Registers
      table so it is never counted as an eleventh demo register.
- [x] 2b.8 Regenerate: `php skills/html-mockup/assets/gallery/_build-gallery.php` (~20s). DONE —
      exit 0, ~12s, commit `c2bb1d1` (index.html itself is gitignored, not part of the diff).
- [ ] 2b.9 Full-sweep `visual-verification` (`skills/visual-verification/SKILL.md`) — every strip
      at every anchor, never a sample. **NOT DONE by sdd-apply — no browser tooling available in
      this execution context.** Owned by the orchestrator, to run separately once the regenerated
      gallery (commit `c2bb1d1`) is available.
- [x] 2b.10 Verify: `php skills/framework-audit/assets/framework-audit.php && php tests/test-framework-audit.php`
      → 0 FAIL, WARN drops to 4 (baseline) + 5 (`RT_TPL_NO_ENVOLTORIO` for the T-C3 survivors) + 1
      (`RT_GALLERY_SINGLE_PAGE_DEMO` for medida) = 10, tests ≥1193 OK. DONE, exact match:
      `0 FAIL / 10 WARN / 0 JUDGE across 15 skills + 2 agent(s)`. Full chain:
      `test-framework-audit` 738 + `test-write-path` 426 + `test-container-hygiene` 81 +
      `test-audit-signals` 22 = **1267 OK / 0 FAIL** (was 1254 after PR2a). The PR2a-era acceptance
      test for "all seven real Envoltorio tables" was updated to the two that still exist on disk
      post-amputation (`TPL-C-14`, `TPL-E-07`); the other five it named are among the 16 deleted.

---

## PR3 — Pilot: Inmobiliaria de la O

### PR3a — `TPL-C-15` archetype doc (no human decision)
Files: `templates/corporate/TPL-C-15-cartera-curada.md` (NEW), `recommender.md`, both `_README.md`.
- [x] 3a.1 Author `TPL-C-15` with `TGL-HERO-MODE` (`buscador-portada`|`retrato`, default `retrato`)
      and inherited `TGL-MAP-MODE` (default `off`); include its own `Envoltorio` table from day one
      (no catch-all-only shortcut — this is a new file, author it complete). DONE — 6 real rows
      (`COMP-HERO-CARTERA` bleed, `COMP-SEARCH-BAND` contained, `COMP-FEATURED-GRID` contained,
      `COMP-MAP-SEARCH` bleed, `COMP-VALUATION-CTA` row, `COMP-TESTIMONIAL` contained), no catch-all.
- [x] 3a.2 Re-key `recommender.md` and `corporate/_README.md` from `TPL-C-13` to `TPL-C-15` for the
      real-estate pilot. DONE for both docs (active-set tables, §3b routing table + prose, §6.2/§6.3/
      §6.4 page-dependency tables, `TPL-C-13` disposition row updated to present tense). **Deviation,
      recorded per the orchestrator's explicit scope fence for this batch ("Do NOT touch
      `_build-gallery.php`")**: `TPL-PROPERTY-01`'s render-function re-key at `_build-gallery.php`
      (the `if ('TPL-C-13' === $tpl ...)` dispatch branches) was NOT done this batch. Verified safe to
      defer — the literal `'TPL-C-13'` string is dead code right now (no `$CONTENT`/`$STRIPS` entry
      sets `tpl` to that value since PR2b's amputation), so leaving it costs nothing until PR3b wires
      the `delao` brand and can re-key it alongside the real `$BRANDS`/`$CONTENT` addition in the same
      touch of that file.
- [x] 3a.3 Verify: `php skills/framework-audit/assets/framework-audit.php && php tests/test-framework-audit.php`
      → `RT_TPL_WRAPPER_DUPLICATE` does not fire against `TPL-C-14` (shared exactly `COMP-HEADER` +
      `COMP-FOOTER` = 2 of a 15-id union, `2·2=4≤15`, better margin than the ≈4/15 forecast); 0 FAIL.
      DONE. Exact match: `0 FAIL / 10 WARN / 0 JUDGE across 15 skills + 2 agent(s)` (same 10 as
      post-PR2b — no new WARN/FAIL introduced). Full chain: `test-framework-audit` 751 +
      `test-write-path` 426 + `test-container-hygiene` 81 + `test-audit-signals` 22 =
      **1280 OK / 0 FAIL** (unchanged from pre-PR3a — no new fixture tests needed, the new file was
      verified against the real audit run, not a synthetic fixture). Adding the new file also shifted
      the gallery's own input fingerprint (`RT_GALLERY_STALE`, unrelated to the Envoltorio gate);
      regenerated `index.html` (gitignored, zero `$CONTENT`/`$STRIPS`/`$BRANDS`/`_build-gallery.php`
      change — confirmed via `git status` before and after) to clear it, staying inside the "no
      photography, no `$BRANDS` entry, no `_build-gallery.php` edit" fence.

### PR3b — delao brand (human decision required)
Files: `_build-gallery.php` (`$BRANDS`, `$CONTENT`, `$STRIPS`, `$PAGES` for `delao`),
`_gallery-images.md` (delao manifest rows + Registers row), handoff block content.
- [x] 3b.1 **Verify accent with the generator's own `contrast()`** (`_build-gallery.php:203`)
      before adding the brand block. DONE, but the launch value was WRONG and this task's own run
      of `contrast()` is what caught it: `#756547` clears the AA text-contrast gate (5.15 bg /
      4.77 alt, re-measured — ≥4.5) but FAILS a second, independent gate this task's literal text
      did not name — `ink_ends()` (§ 5a), which requires the accent-tinted shadow ink to carry a
      channel spread ≥ 20 (`fail()`: "#28251D ... spread of 11 ... a two-colour map whose dark ink
      is grey is not a two-colour map"). The whole `#8A7B5C → #7A6B4E → #756547` lineage is one
      family of *desaturated* khakis: darkening without raising saturation clears AA text-contrast
      but not ink-spread. Final: **`#8A5A2A`** (terracotta/bronze) — 5.35 bg / 4.94 alt (both
      ≥4.5) **and** shadow ink `#2F2317`, spread 24 (≥20). `#8A7B5C` (3.77/3.49) and `#7A6B4E`
      (4.73/**4.37**) both re-confirmed as known-bad controls on the AA gate, as this task
      expected. Ground `text`/`bg`/`bg-alt`: 16.17:1 / 14.95:1, both ≫7.0. `design.md`'s decision
      note updated with this correction (see its "PR3b correction" addendum).
- [x] 3b.2 Container: DONE — verification only, as written. `--container-max:1280px` is a single
      literal in the generated page shared by every brand (`_build-gallery.php`); `delao` adds no
      forked value, confirmed by `RT_MOCKUP_CONTAINER_FORK` staying silent (0 FAIL).
- [x] 3b.3 Font: DONE — `Instrument Serif` (`font_1`) + `Archivo` (`font_2`), both already in
      `skills/html-mockup/assets/fonts/`; no new `.woff2`/OFL/`_fonts.php` entry. Substitution for
      the design's Libre Caslon Display recorded as a comment on `$BRANDS['delao']` itself, same
      precedent as D3's container note.
- [x] 3b.4 DONE — the 12 `delao-*.webp` files (669 KB total) were generated and placed in
      `img/` ahead of this batch; this task's scope was adding their manifest rows (folded into
      3b.6/3b.7 below), not generation.
- [x] 3b.5 DONE — pilot responsive resolved (adopting the design handoff's own recommendation, per
      this batch's launch prompt, qualitatively: grids collapse toward fewer columns as the
      viewport narrows, the search bar stacks, the detail mosaic collapses, the nav goes mobile,
      44px touch targets). **Note on the exact breakpoints**: the launch prompt's own numbers
      (1024px / 720px) do not match the house's real, already-audited breakpoints — verified rather
      than assumed. `COMP-FEATURED-GRID` (`.stockgrid`, reused from `TPL-C-07`) drops from `--cols`
      to 2 at `max-width:900px` and to 1 at `max-width:599px`; `COMP-SEARCH-BAND`'s `.filterbar`
      stacks below `min-width:900px`; `TPL-PROPERTY-01`'s tour mosaic (`.tourlist`, pre-existing CSS,
      never rendered before this batch) collapses to 1 column below `min-width:768px`; `.rulegrid`
      (new, this batch) hides below `min-width:1024px` — decorative-only, so its own threshold does
      not need to match the others'. Reusing the house's real numbers rather than forcing new
      768/900/599 media queries to read "1024/720" keeps one set of breakpoints for the whole
      catalogue instead of a second set that only `delao` uses. 44px touch targets are the house
      `.btn`/`.field` sizing, unchanged.
      **Gap, not silently absorbed: "nav becomes a drop-down" is NOT met.** Checked the real
      `.mainnav` CSS rather than assuming: on a narrow viewport it is
      `display:flex;overflow-x:auto` — a horizontally-scrolling row, the SAME pattern all ten
      demos share, not a drop-down/hamburger. Building a real drop-down means changing `.nav`/
      `.mainnav`, chrome shared by every archetype in the catalogue, which is a catalogue-wide
      interaction change affecting nine OTHER demos' rendered chrome — not a `delao`-scoped edit,
      and outside this PR's `$BRANDS`/`$CONTENT`/`$STRIPS`/`$PAGES`-for-`delao` file list. Left
      for a dedicated follow-up (Phase 4 handoff, PR12, or its own ticket) rather than smuggled into
      one brand's PR; recorded here and in the apply-progress risk list, not silently dropped.
      No longer "pendiente de definir" in the handoff sense for the parts that ARE resolved above.
- [x] 3b.6 DONE — `$BRANDS['delao']`, `$CONTENT['TPL-C-15-delao']` (home + `nosotros` + `contacto`
      + `producto` + `propiedades`), `$STRIPS` (one entry, anchor `editorial`), `$PAGES['TPL-C-15']`
      (5 pages), manifest rows (12, slug=filename, licence `Freepik AI (Pikaso)`) all added.
      **Deviation from design.md's literal page map, recorded, not silent**: design.md named 3
      inner pages (`PROPERTY-01`/`ABOUT-01`/`CONTACT-01`, 4 pages total); this batch's launch
      prompt carried a newer decision, **D5** ("an Envato-grade demo is a complete multi-page
      site"), requiring 5 pages — home, a full portfolio listing, the property detail, Nosotros,
      Contacto. The listing page reuses `TPL-SERVICES-01`'s own "index for a home with more entries
      than fit" pattern (its own § "Por qué existe") rather than inventing a new archetype —
      `page_property_index()` is new PHP, `TPL-SERVICES-01` is not a new archetype doc. Also
      completed in this touch of `_build-gallery.php`, per PR3a's own recorded deferral: the four
      `'TPL-C-13' === $tpl` render-dispatch branches re-keyed to `'TPL-C-15'` (home now calls the
      new `strip_cartera_curada()`, not `strip_property()` — see design C2; `producto`/`contacto`
      reuse `page_property()`/`page_contact_enquiry()` verbatim, exactly as C2 promised), plus one
      new `propiedades` branch added.
- [x] 3b.7 DONE — `Inmobiliaria de la O` row in the 10-row Registers table replaced
      `*(pending — PR3b)*` with `delao-*`. **N=67** (55 pre-PR3b + 12 delao), **R=10** (unchanged —
      filling an already-declared row, not adding one), **ceil(N/R)=7**. Largest `fp-` shoot
      bucket **2** (ties: `fp-5220559/564/565/580/582/586/587` from earlier PRs, and delao's own
      `fp-5278369` — villa-alameda + atico-mar — and `fp-5278379` — nerea + leire); well under the
      cap of 7, no diversification needed. All three numbers verified by parsing the live manifest
      table with the same regex `_build-gallery.php` itself uses, not computed by hand.
- [x] 3b.8 Regenerate: DONE, `php _build-gallery.php` exit 0. **Full-sweep `visual-verification`
      NOT DONE by sdd-apply** — no browser tooling in this execution context, same limitation
      recorded for 2b.9. Owned by the orchestrator, to run over the regenerated gallery.
- [x] 3b.9 Verify: DONE. `php framework-audit.php` → **`0 FAIL / 10 WARN / 0 JUDGE across 15
      skills + 2 agent(s)`** — same 10 WARN lines as pre-PR3b (`RT_GALLERY_SINGLE_PAGE_DEMO` does
      NOT fire for `delao`, confirmed by name absent from every WARN/FAIL line; it still fires for
      `medida`, which ships 1 page). `RT_GALLERY_AXIS_LEAK`, `RT_GALLERY_REGISTER_COUNT_MISMATCH`,
      `RT_MOCKUP_CONTAINER_FORK`, `RT_GALLERY_ACCENT_TEXT_FAIL`, `RT_GALLERY_NO_MANIFEST` all
      silent for `delao`. Full chain: `test-framework-audit` 751 + `test-write-path` 426 +
      `test-container-hygiene` 81 + `test-audit-signals` 22 = **1280 OK / 0 FAIL** (unchanged from
      pre-PR3b — no new fixture tests were requested by design.md/tasks.md for this batch; the new
      brand/archetype-instance was verified against the real, already-existing audit/build binaries,
      not synthetic fixtures). Nav reachability proven by counting distinct rendered hash
      destinations for the strip in `index.html`: **5** — `#tplc15delao/editorial` (home),
      `.../propiedades`, `.../producto`, `.../nosotros`, `.../contacto` — matching all 5 declared
      pages, none unreachable.

## PR3c — Rework `delao` for design fidelity (PR3b rejected by the user)

**Rule inversion for this slice only**: fidelity to `design_handoff_inmobiliaria_de_la_o` wins over
component reuse. `TPL-C-15` and `TPL-PROPERTY-01` remain the only archetypes (no new archetype doc);
their SECTIONS are authored to match the design instead of borrowed from `TPL-C-07`/`TPL-UNIT-01`
(cars) or the clinic-shaped `page_about_company()`/`page_contact_enquiry()`.

Files: `_build-gallery.php` (bleed fix + new render functions + expanded `$CONTENT`), `CSS`,
`framework-audit.php` (new gate), `tests/test-framework-audit.php` (new fixtures),
`CONTRIBUTING.md` (new row doc).

- [x] 3c.1 [RED→GREEN] The bleed bug: `hero_cartera_html()` hand-wrote its `<section>` tag instead
      of calling `sec_open(..., 'bleed')`, so `TPL-C-15`'s own Envoltorio table said "banda a
      sangre" and the render said "contained". Fixed by routing through `sec_open()`/`sec_close()`;
      verified `.canvas`'s own grid still drives the 78vh photo/veil/4-column rule grid (`.bleedband`
      only touches `.media-full`/`.rulegrid`, both `position:absolute`, so out of grid flow).
- [x] 3c.2 [RED→GREEN] New audit row `RT_TPL_ENVOLTORIO_RENDER_MISMATCH`: for every home archetype
      with an Envoltorio table, counts DECLARED unconditional `bleed`/`row` rows (a row naming its
      own condition — "cuando el conmutador…" — is excluded) against RENDERED `bleedband`/`secrow`
      section counts on that archetype's actual home demo (`data-arch`+`data-page="home"` in the
      generated gallery). New helpers `gallery_page_segments()`/`gallery_section_shape_counts()`/
      `env_row_is_conditional()`. Failing test written FIRST (3 FAIL observed, genuine RED — gate
      code did not exist), then implemented (765 OK / 0 FAIL, GREEN). Acceptance test against REAL
      bytes: `TPL-C-14` (3 bleed + 1 row), `TPL-E-07` (1 bleed), `TPL-C-15` (1 bleed + 1 row post-fix)
      all pass; the other 5 surviving archetypes have no table yet (`RT_TPL_NO_ENVOLTORIO` owns
      that gap), so this row is silent for them. ROW_TYPES + `CONTRIBUTING.md` both updated.
- [x] 3c.3 Home `COMP-FEATURED-GRID` + listing card: new `proplux_card_html()`/`proplux_grid_html()`/
      `featured_grid_html()` replace `property_grid_html()` (Motor Aranda's `.stockgrid`/`.vcard`) at
      the two live call sites (home, and `page_property()`'s related section stays via the new
      'similar' variant). Badge flush top-left (no radius), zone as MUTED text (not accent —
      design-tokens.md's real accent row: "Never body text, never decoration"; the source design's
      own `#8A7B5C` zone would `fail()` the accent-role gate), serif title, facts row, serif price;
      listing variant adds a right-aligned monospace reference on the zone row.
- [x] 3c.4 `propiedades` page rebuilt: `split_head_html()` (1.5fr/1fr split header, shared with
      producto/nosotros/contacto), `filter_band_html()` (sticky filter bar — `top:0`, not the
      design's literal `top:78px`, since this chassis's `.site-head` is never itself sticky),
      `results_bar_html()` (count + 3 sort links, active one an `active states` accent role like
      `.mapswitch`), `property_listing_html()` (9-card `proplux` grid, ghost "cargar más" button).
      Content expanded to 9 properties (3 with delao's real photos, 6 honestly placeholder-marked —
      no new photography sourced, no real photo reused under a false name).
- [x] 3c.5 `producto` (`TPL-PROPERTY-01`) fully rewritten off `TPL-UNIT-01`'s car sections: breadcrumb
      bar with monospace ref riding it (`.refcode` reused from its rightful owner, repositioned),
      split header with a border-left price panel, mosaic gallery (`2fr 1fr`, room captions honour
      COMP-PROPERTY-TOUR's "no es COMP-GALLERY" ADN), 4-col key-data row, body (description + 10-row
      features table folding COMP-COSTS-BREAKDOWN/COMP-ENERGY-LABEL in, drawn location map, sticky
      visit panel — mortgage simulator out of scope so one block, not two), 3 similar cards.
- [x] 3c.6 `nosotros` (`TPL-ABOUT-01`) and `contacto` (`TPL-CONTACT-01`) each get a dedicated bespoke
      page function (`page_about_cartera()`, `page_contact_cartera()`) instead of the shared
      clinic/dealer-shaped `page_about_company()`/`page_contact_enquiry()`; TPL-C-15 dispatch
      re-keyed. Nosotros: split header, full-width photo band, numbered método list (COMP-VALUES'
      own "compromisos verificables" ADN), dark cifras band, 3-card team grid using the 3 REAL
      portraits with a DYNAMIC `cols_attr()` (never a hardcoded `cols-3`/`cols-4` holding the wrong
      count — the exact defect class the brief warned was already present twice elsewhere), sober
      CTA. Contacto: split header, 2×2 form grid + textarea + privacy checkbox, 3-block aside
      (Oficina/Directo/drawn map). COMP-PROCESS (`TPL-CONTACT-01` fijo·ADN) honoured via the header's
      own 24h-reply lede rather than a second visible band the source design does not draw.
- [x] 3c.7 CSS authored throughout: new `proplux-*`/`propmosaic`/`propkeydata`/`propfeat`/`proploc`/
      `mapdrawn`/`propvisit`/`propbody`/`methodsec`/`figuresband`/`teamsec`/`contactaside`/etc.
      classes, none colliding with `$CLASS_BLOCKS`'s reserved names. Map markers paint `--c-text`,
      never `--c-accent` (`map_search_html()`'s own "THE PIN IS NOT AN ACCENT MARK" precedent).
      `.sortlink[aria-current]` registered under `$ACCENT_ROLES['active states']`. One genuine
      `fail()` hit and fixed: a comment in the new CSS literally named `.refcode` before the
      "FICHAS DE INVENTARIO" ownership marker, tripping the (plain-text) collision check on its own
      prose — reworded without the literal selector.
- [x] 3c.8 Regenerate + verify: `php _build-gallery.php` exit 0; `php framework-audit.php` →
      **0 FAIL / 10 WARN / 0 JUDGE** (byte-identical WARN set to pre-PR3c); full chain
      `test-framework-audit` 765 + `test-write-path` 426 + `test-container-hygiene` 81 +
      `test-audit-signals` 22 = **1294 OK / 0 FAIL**. Nav reachability re-proven: 5 distinct hash
      destinations, matching all 5 declared pages. Rendered section sweep (before → after):
      `home: hero herocartera→+bleedband, stock grid-sec→featuredgrid`;
      `propiedades: pagehead→+splithead, +searchband filterband, stock grid-sec→proplisting`;
      `producto: unithead/ptour/unitspecs/planwrap/costs/energysec/booking/stock→propgallery/
      propfacts/propdetail/propsimilar`; `nosotros: hero/about/features/stats/team/quotes/band
      closing sober→pagehead splithead/aboutphoto/methodsec/figuresband/teamsec/aboutcta`;
      `contacto: band contactblock/process/medteam/faq→pagehead splithead/contactbody`.
      Full-sweep `visual-verification` NOT run by sdd-apply — no browser tooling in this execution
      context, same limitation recorded for 2b.9/3b.8; owned by the orchestrator.
- [ ] 3c.9 Orchestrator: full-sweep `visual-verification` over the regenerated `delao` strip at its
      one anchor, all 5 pages.

## PR3d — Reproduce the design literally (PR3c also rejected by the user — "no tiene nada que ver")

**Root cause of both PR3b and PR3c's rejection**: both were authored from the handoff's prose
README, which lists a section inventory but loses the design — the exact measurements, gradients,
texture and above all the COPY. PR3c invented an H1 ("Diecisiete propiedades, ninguna al azar")
where the artboard says "Casas que no<br>se anuncian solas", and reused this file's own generic
dark-photo-hero recipe (black vignette, white text, `TPL-C-07`'s floating rounded-shadow search
card) instead of the artboard's literal light horizontal veil and full-width inverse search band —
NovaMira's component system wearing the client's colours, in the user's own words. Fixed by reading
the seven extracted artboard HTML files (`Inicio.dc.html`, `Propiedades.dc.html`,
`Ficha Propiedad.dc.html`, `Nosotros.dc.html`, `Contacto.dc.html`, `Nav.dc.html`, `Pie.dc.html`)
directly, not the README, and reproducing their literal copy and geometry.

Files: `_build-gallery.php` only (content array rewrite, `hero_cartera_html()`, `search_band_html()`/
`filter_band_html()` + new shared `sbfield_html()`, `valuation_row_html()`, `page_cta_html()`, CSS).

- [x] 3d.1 `$CONTENT['TPL-C-15-delao']` rewritten wholesale, verbatim from the seven artboards: home
      hero (eyebrow "Marbella · Costa del Sol", H1 "Casas que no<br>se anuncian solas" as a two-line
      array, lede, stats "17/Mandatos activos" + "2,4 M€/Precio medio de cierre"), search fields
      (Zona o municipio is a text INPUT, not a select — PR3c's mistake), featured/listing/producto
      property data (Villa Alameda's real price 4.950.000 €, parcela 1.860 m², certificado "A",
      comunidad "520 €/mes", full 3-paragraph description including "sala de cine" and "apartamento
      de servicio independiente" PR3c's paraphrase dropped), Nosotros método items (item 02 was an
      entirely different practice in PR3c), valuation CTA copy, propiedades' 9-item listing (design's
      own JS array, in order, real refs MB-1042/MB-1039/MB-1031). The three real photographed slugs
      (`delao-villa-alameda`/`delao-atico-mar`/`delao-finca`) are never renamed; only the copy tied
      to each slug now matches the artboard's own property (Ático Mar de Plata/Milla de Oro, Casa
      Los Cipreses/La Zagaleta). Team keeps delao's own 3 real portraits (Nerea/Julen/Leire), not the
      design's 4 fictional names — no fourth, unphotographed portrait slot.
- [x] 3d.2 `hero_cartera_html()`: H1 rendered from a `h1_lines` two-part array joined with `<br>`
      through `h()` per part (never raw markup), the same convention this file already uses for a
      two-line address; the plain single-string `h1` stays the `aria-label`.
- [x] 3d.3 [RED→GREEN, threat-matrix: mis-scoped shared-component reuse] `sbfield_html()` new shared
      helper for `search_band_html()`/`filter_band_html()`: one field tuple now covers both a select
      (`array($key,$label,array($options))`) and a free-text input
      (`array($key,$label,'input',$placeholder)`) — the artboard's own "Zona o municipio" is always
      an input, never a select. RED observed first: rendering the old select-only path against the
      new `'input'` tuple shape emitted a broken `<select><option>Marbella, Sierra Blanca…</option>`
      instead of a real text field; GREEN after `sbfield_html()` branches on the tuple shape.
- [x] 3d.4 [RED→GREEN] Hero + search-band CSS rewritten to the artboard's literal geometry instead of
      this file's own generic dark-hero/floating-card recipes:
      - `.herocartera::after` — RED: the veil was `linear-gradient(to top, black 82%→0%)` (a vertical
        dark vignette meant for WHITE text); the artboard's own veil is
        `linear-gradient(100deg, ground 95%→6%)`, horizontal, rising from the TEXT side. Fixed with
        `color-mix(in srgb, var(--c-bg) N%, transparent)` at the artboard's own four stops.
      - `.herocartera .head` — RED: `h1`/`.lede`/`.eyebrow` were forced `color:#fff` for a veil that
        is now light; removed, so ink/muted defaults apply (matching every other section on the page).
      - `.herocartera > .canvas` — RED: `grid-template-columns:7fr 3fr` (≈2.33 ratio) against the
        artboard's own `1.45fr 1fr` (≈1.45) — nearly double the imbalance. Fixed to the literal ratio.
      - `.rulegrid span` — RED: white hairlines (`rgba(255,255,255,.16)`) invisible over the new light
        veil; the artboard paints its own rule grid `rgba(23,24,26,.09)` over its own light hero.
        Fixed to `color-mix(in srgb, var(--c-text) 9%, transparent)`, plus a `border-right` on the
        last span — the artboard's own 4th rule-grid column carries both `border-left` AND
        `border-right`, closing the grid; 4 columns need 5 lines, not 4.
      - `.statspanel` — RED: `background:rgba(0,0,0,.42)` (dark) with a white border and white text,
        against the artboard's own near-opaque LIGHT panel with ink numbers. Fixed to
        `color-mix(in srgb, var(--c-bg) 93%, transparent)` background, ink numbers, muted labels.
        Border stays NEUTRAL (`var(--c-border)`, not the artboard's own accent-toned
        `rgba(138,123,92,.55)`) — a decorative border-left claims none of `$ACCENT_ROLES`'s four
        roles and the accent-budget gate would `fail()` it on sight; same substitution
        `proplux_card_html()`'s zone label and `property_location_html()`'s map pin already made.
      - `.sbplate .filterbar` — RED: nested `.filterbar` (`TPL-C-07`'s own floating card: rounded
        corners, box-shadow, negative top margin pulling it onto a photo's bottom edge, LIGHT
        background) inside `.sbplate` (a dark inverse band) with only the label colour overridden —
        every other card property survived, invisibly, under a dark backdrop fighting its own
        light-card child. Neutralised (`background:transparent;border:0;border-radius:0;margin:0;
        width:100%;box-shadow:none`), literal unequal column ratio `1fr 1.2fr 1fr 1.1fr auto`
        (was equal `repeat(4,1fr)`), literal field padding, `border-right` hairline separators.
      - `.sbplate .filterbar select,input` — RED: base `.filterbar select` paints `var(--c-text)`
        (dark ink) — invisible on the dark inverse ground, an unreadable-values bug, not merely a
        mistint. Fixed to `var(--c-on-inverse)`.
      GREEN: `php _build-gallery.php` exit 0; rendered hero/search-band markup inspected directly
      against the artboard byte-for-byte for eyebrow/H1/lede/stats/field-order/field-type.
- [x] 3d.5 Fixed the two "Valorar mi casa" CTAs named in the launch brief:
      - `valuation_row_html()`: the artboard draws exactly ONE button here ("Solicitar valoración")
        plus a plain `tel:` link ("o llámenos"), never a second page-routed button. Rewritten to
        route the primary CTA through an EXPLICIT `ihref('contacto')` (never
        `ihref_for_label($vl['cta'])`, whose fuzzy match against TPL-C-15's five page labels fails
        and falls back to home) and render the second action as a `tel:` anchor, not a button.
      - `page_cta_html()`: now accepts OPTIONAL `cta_1_href`/`cta_2_href` overrides — every
        pre-existing caller omits them and keeps the old `ihref_for_label()` behaviour byte for byte;
        delao's `propiedades` page (whose own closing CTA is not in the artboard but is kept as
        `TPL-SERVICES-01`'s own reused "index" closing convention) supplies `cta_2_href: 'contacto'`
        for its "Valorar mi casa" button, fixing the identical fallback-to-home bug.
- [x] 3d.6 Regenerate + verify: `php _build-gallery.php` exit 0; `php framework-audit.php` →
      **0 FAIL / 10 WARN / 0 JUDGE** (byte-identical WARN set); full chain `test-framework-audit` 765
      + `test-write-path` 426 + `test-container-hygiene` 81 + `test-audit-signals` 22 =
      **1294 OK / 0 FAIL**. Nav reachability re-proven: 5 distinct hash destinations
      (`#tplc15delao/editorial`, `.../propiedades`, `.../producto`, `.../nosotros`, `.../contacto`).
      Rendered fidelity check (quoted from the real generated `index.html`): home H1
      `<h1>Casas que no<br>se anuncian solas</h1>`, eyebrow `<span class="eyebrow">Marbella · Costa
      del Sol</span>`; accent `#8A5A2A` on `b-delao` → 5.35:1 bg / 4.94:1 bg-alt (matches the launch
      brief's own quoted figures exactly). Section sweep unchanged from 3c.8 (this batch is content
      + CSS fidelity, not a section-shape change): home `hero herocartera bleedband, searchband,
      featuredgrid grid-sec, valuerow secrow, quotes grid-sec`; propiedades `pagehead splithead,
      searchband filterband, proplisting grid-sec, band closing sober`; producto `pagehead splithead
      prophead, propgallery, propfacts, propdetail grid-sec, propsimilar grid-sec`; nosotros
      `pagehead splithead, aboutphoto, methodsec grid-sec, figuresband, teamsec grid-sec, aboutcta`;
      contacto `pagehead splithead, contactbody grid-sec`.
      Full-sweep `visual-verification` NOT run by sdd-apply — no browser tooling in this execution
      context, same limitation recorded for 2b.9/3b.8/3c.9; owned by the orchestrator.
- [ ] 3d.7 Orchestrator: full-sweep `visual-verification` over the regenerated `delao` strip, all 5
      pages, checked against the actual artboard screenshots this time (not the README).

---

## PR4 — `TPL-C-07` (aranda) Envoltorio table

Files: `templates/corporate/TPL-C-07-stock-listing.md`.
- [ ] 4.1 [RED] extend the 2a.5 acceptance fixture set: `TPL-C-07` currently has **no** table →
      `RT_TPL_NO_ENVOLTORIO` WARNs (already true from PR2a; this is the regression pin).
- [ ] 4.2 Author the `Envoltorio` table against `TPL-C-07`'s own `## 2. Wireframe` `COMP-*`
      inventory (catch-all allowed, at most one).
- [ ] 4.3 Verify: `php skills/framework-audit/assets/framework-audit.php` → `RT_TPL_NO_ENVOLTORIO`
      WARN count drops by 1; `RT_TPL_WRAPPER_DUPLICATE` does not fire against any survivor. No
      gallery regeneration needed — the table is static-analysis-only, no built HTML changes.

## PR5 — `TPL-C-11` (alinea) Envoltorio table + photo re-brief (human decision required)

Files: `templates/corporate/TPL-C-11-treatment-plan.md`, `_build-gallery.php` (`$CONTENT['TPL-C-11-alinea']`),
`_gallery-images.md` (alinea manifest rows).
- [ ] 5.1 [RED] regression pin: `TPL-C-11` currently WARNs (no table).
- [ ] 5.2 Author the `Envoltorio` table.
- [ ] 5.3 **Human decision**: alinea has 3 photos, needs ~7 — source/generate ~4 more, re-brief
      copy from orthodontics to wellness coaching/entrenamiento personal (its doc already names
      "coaching"/"entrenamiento personal" as ideal-for). This breaks "zero new photography" —
      call it out, don't absorb it silently.
- [ ] 5.4 Add new manifest rows (slug=filename+licence); regenerate; full-sweep
      `visual-verification`.
- [ ] 5.5 Verify: full chain 0 FAIL; `RT_GALLERY_NO_MANIFEST` passes for the new frames.

## PR6 — `TPL-E-06` (corte) Envoltorio table

Files: `templates/ecommerce/TPL-E-06-fit-sizing.md`.
- [ ] 6.1 [RED] regression pin: `TPL-E-06` currently WARNs.
- [ ] 6.2 Author the table.
- [ ] 6.3 Verify: `php skills/framework-audit/assets/framework-audit.php` → WARN drops by 1. No
      regeneration needed (corte already renders `PDP-02`+`ABOUT-02`, unchanged).

## PR7 — `TPL-E-08` (tueste) Envoltorio table + `TPL-PDP-01` rehoming

Files: `templates/ecommerce/TPL-E-08-subscription.md`, `_build-gallery.php` (`$PAGES['TPL-E-08']`,
new page-key wiring to `TPL-PDP-01`).
- [ ] 7.1 [RED] regression pin: `TPL-E-08` currently WARNs; also add a failing test that
      `$PAGES['TPL-E-08']` has only 1 inner page today (`PDP-04`) before the addition.
- [ ] 7.2 Author the `Envoltorio` table.
- [ ] 7.3 Wire `TPL-PDP-01` onto `TPL-E-08` as a second inner page (recurring-subscription vs
      one-off single-bag purchase — the two pages differ on E-08's own separating question, per
      design decision C1, already resolved — implementation only, ≤1 new frame).
- [ ] 7.4 Regenerate + full-sweep `visual-verification` (new page renders).
- [ ] 7.5 Verify: full chain 0 FAIL; WARN drops by 1.

## PR8 — `TPL-E-09` (medida) Envoltorio table + `TPL-PDP-05` rescue (resolves T-C5)

Files: `templates/ecommerce/TPL-E-09-made-to-measure.md`, `_build-gallery.php`
(`$PAGES['TPL-E-09']`, re-skin the 2b.4 harvested `mtm` content).
- [ ] 8.1 [RED] regression pin: `TPL-E-09` currently WARNs AND is single-page (0 inner pages) —
      this is the pin for `RT_GALLERY_SINGLE_PAGE_DEMO`'s WARN state from 2b.2.
- [ ] 8.2 Author the `Envoltorio` table.
- [ ] 8.3 Re-skin the harvested `$CONTENT['_harvest']['tpl-e03-mtm']` (from 2b.4) onto medida's own
      6 `estor-*` photos as `$PAGES['TPL-E-09']`'s new `TPL-PDP-05` page.
- [ ] 8.4 Regenerate + full-sweep `visual-verification`.
- [ ] 8.5 Verify: full chain 0 FAIL; `RT_TPL_NO_ENVOLTORIO` WARN drops by 1;
      `RT_GALLERY_SINGLE_PAGE_DEMO` WARN drops to 0 (last dependency for PR2c).

## PR9 / PR10 — new archetypes: lawyers (`TPL-C-16`), gyms (`TPL-C-17`)

Files: 2 new archetype `.md` + 2 new `$BRANDS`/`$CONTENT`/`$STRIPS`/`$PAGES` entries +
`recommender.md`/`_README.md` + manifest rows, one PR per archetype-doc/brand pair (4 PRs total,
same shape as PR3a/3b).
- [ ] 9/10.1 Author each archetype with its `Envoltorio` table from day one (complete, not
      catch-all-only — new files, no legacy debt).
- [ ] 9/10.2 New brand: anchor on an existing `design-personalities.md` entry, override only
      ground/accent/type/photos (`RT_MOCKUP_AXES_MISMATCH` guard), container 1280
      (`RT_MOCKUP_CONTAINER_FORK` guard), accent re-verified with `contrast()`
      (`RT_GALLERY_ACCENT_TEXT_FAIL` guard), ≥2 pages declared (`RT_GALLERY_SINGLE_PAGE_DEMO`
      guard), own photo set with manifest rows.
- [ ] 9/10.3 Regenerate + full-sweep `visual-verification` after each brand PR.
- [ ] 9/10.4 Verify: full chain 0 FAIL for each.

---

## PR11 — Ratchet (executes the deferred "PR2c"; gated on PR4–PR10)

Files: `framework-audit.php`, `tests/test-framework-audit.php`.
- [ ] 11.1 Precondition check: run `php skills/framework-audit/assets/framework-audit.php`; confirm
      `RT_TPL_NO_ENVOLTORIO` and `RT_GALLERY_SINGLE_PAGE_DEMO` WARN counts are both **0** (all 10
      final home archetypes carry a table; medida has ≥2 pages). Do not proceed otherwise.
- [ ] 11.2 [RED→GREEN] flip both rows' severity WARN→FAIL in `framework-audit.php`; update the
      2a.4/2b.2 tests to assert FAIL instead of WARN.
- [ ] 11.3 Verify: full chain → **0 FAIL, WARN ≤ 4** (baseline only), tests ≥1193 OK.

## PR12 — Phase 4: handoff wiring + cleanup

Files: `html-mockup/SKILL.md` pointer, `references/handoff-block.md`,
`agents/novamira-web-orchestrator.md` build-gate step, `skills/qa-review/references/house-rules.md`.
- [ ] 12.1 Wire the orchestrator's build-gate step to require the 8-field handoff block before
      `es_manifest_record()` (`es-builder.php`) is called — `mockup-handoff-persistence`
      Requirements 1–3; this timing has no static verifier (Requirement 4), so `qa-review`'s
      pre-build checklist is the enforcement point — update `house-rules.md` accordingly.
- [ ] 12.2 Final full chain: `php skills/html-mockup/assets/gallery/_build-gallery.php && php skills/framework-audit/assets/framework-audit.php && php tests/test-framework-audit.php`
      → 0 FAIL, WARN ≤ 4, ≥1193 OK.
- [ ] 12.3 Final full-sweep `visual-verification` across all 10 demos, every anchor.
- [ ] 12.4 **Redeploy `~/.claude/skills`** after the tracker branch merges to `main` — it is a
      deployed copy that does not update on merge.

---

## Out of Scope (do not task, do not implement)

Mortgage simulator, real map provider, `propiedad` CPT + taxonomies, URL-reflected filters, any new
personality anchor, `TPL-CART-01`/`TPL-CHECKOUT-01`/`TPL-SHOP-01` page-set inclusion (no render
function exists — declaring them would be the lie `RT_GALLERY_SINGLE_PAGE_DEMO` blesses).
