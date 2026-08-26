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
- [ ] 3a.1 Author `TPL-C-15` with `TGL-HERO-MODE` (`buscador-portada`|`retrato`, default `retrato`)
      and inherited `TGL-MAP-MODE` (default `off`); include its own `Envoltorio` table from day one
      (no catch-all-only shortcut — this is a new file, author it complete).
- [ ] 3a.2 Re-key `recommender.md` and `corporate/_README.md` from `TPL-C-13` to `TPL-C-15` for the
      real-estate pilot; `TPL-PROPERTY-01`'s render functions re-key at `_build-gallery.php:15370`
      and its `if ('TPL-C-13' === $tpl ...)` branches (`:15572-15590`) switch to `TPL-C-15`.
- [ ] 3a.3 Verify: `php skills/framework-audit/assets/framework-audit.php && php tests/test-framework-audit.php`
      → `RT_TPL_WRAPPER_DUPLICATE` does not fire against `TPL-C-14` (shared ≈4 of ≥15 union,
      `2·4=8≤15`); 0 FAIL.

### PR3b — delao brand (human decision required)
Files: `_build-gallery.php` (`$BRANDS`, `$CONTENT`, `$STRIPS`, `$PAGES` for `delao`),
`_gallery-images.md` (delao manifest rows + Registers row), handoff block content.
- [ ] 3b.1 **Verify accent with the generator's own `contrast()`** (`_build-gallery.php:203`)
      before adding the brand block: ground `#F6F4F0` / alt `#EFEBE4` / text `#17181A`, accent
      `#756547`. Confirm ≥4.5 on both `bg`/`bg-alt` and ≥7.0 text-on-ground/alt. `#8A7B5C` and
      `#7A6B4E` are known-bad controls — running them first proves the check actually gates.
- [ ] 3b.2 Container: consume `--container-max` from `design-system.md:138` unmodified (1280,
      D3) — no forked value.
- [ ] 3b.3 Font: confirm `Instrument Serif` + `Archivo` (both already in
      `skills/html-mockup/assets/fonts/`) are used; no new `.woff2`/OFL/`_fonts.php` entry —
      resolved by design, this is verification only.
- [ ] 3b.4 Source/generate the `delao-*` photo set (new slug prefix; the 15 `inmo-*` rows already
      retired in 2b.7) — human decision: asset sourcing via Freepik AI/Pikaso/Magnific per
      proposal Dependencies.
- [ ] 3b.5 Resolve the pilot's responsive behaviour (currently "pendiente de definir"); the
      `mockup-handoff-persistence` Build Gate MUST reject the handoff otherwise.
- [ ] 3b.6 Add `$BRANDS['delao']`, `$CONTENT`/`$STRIPS`/`$PAGES` (home `TPL-C-15`, `PROPERTY-01`,
      `ABOUT-01`, `CONTACT-01`), manifest rows with slug=filename + licence.
- [ ] 3b.7 Add delao's Registers row to the 10-row table from 2b.7 (was a placeholder).
- [ ] 3b.8 Regenerate + full-sweep `visual-verification`.
- [ ] 3b.9 Verify: full chain 0 FAIL; `RT_GALLERY_AXIS_LEAK`, `RT_GALLERY_REGISTER_COUNT_MISMATCH`,
      `RT_MOCKUP_CONTAINER_FORK`, `RT_GALLERY_ACCENT_TEXT_FAIL`, `RT_GALLERY_SINGLE_PAGE_DEMO`,
      `RT_GALLERY_NO_MANIFEST` all pass for `delao`.

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
