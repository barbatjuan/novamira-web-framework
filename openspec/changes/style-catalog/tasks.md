# Tasks: Style Catalog — art direction per project

> **Size deviation, declared.** The 530-word cap is exceeded. 24 requirements / 48 scenarios / 17
> PRs under strict TDD cannot compress below this without dropping RED-test traceability — the two
> prior attempts failed partly by underspecifying delivery. Declared, not met by dropping content.

## Review Workload Forecast

| Field | Value |
|---|---|
| Estimated changed lines | ~4,600 across 17 PRs (design.md Migration/Rollout table) |
| 400-line budget risk | High in aggregate; Low per-PR after split, except 3 named exceptions |
| Chained PRs recommended | Yes |
| Suggested split | 17 PRs, 6 slices — see Work Units below |
| Delivery strategy | auto-chain |
| Chain strategy | feature-branch-chain |

```text
Decision needed before apply: No
Chained PRs recommended: Yes
Chain strategy: feature-branch-chain
400-line budget risk: High
```

`Decision needed before apply: No` — both the exception scope (PRs 1b/1c/1f, Engram #353) and the
chain strategy are already locked by prior user decisions; `sdd-apply` proceeds without re-asking.

**Note on PR count**: design.md's prose says "16 PRs" but its own Migration/Rollout table
enumerates 17 distinct ids (1a–1f, 2a–2b, 3a–3b, 4a–4c, 5a–5c, 6). This tasks artifact uses the
table's 17 — the prose count is a self-inconsistency in design.md, not a scope change.

### Chain topology (feature-branch-chain)

Tracker branch: `feat/style-catalog` (session param). PR 1a bases on the tracker; every later PR
bases on its immediate predecessor's branch; only the tracker merges to `main`. Revert order is
the exact reverse of the table below (PR 6 first, PR 1a last) — reverting an earlier slice while a
later one stands leaves a style naming an axis position the table no longer defines
(`RT_PERS_BAD_AXIS`, `framework-audit.php:95`, enforced `:1081-1084`).

### Work Units

| PR | Slice | Base branch | Est. | Budget | Focused proof command |
|---|---|---|---|---|---|
| 1a | 1 | tracker | ~200 | OK | `cmp` old/new `index.html` past line 1 (see 1a.4) |
| 1b | 1 | 1a | ~450 | **size:exception** | `fx_mockup()` corporate fixture |
| 1c | 1 | 1b | ~500 | **size:exception** | `fx_mockup()` ecommerce fixture |
| 1d | 1 | 1c | ~250 | OK | `php framework-audit.php` (chassis gates) |
| 1e | 1 | 1d | ~180 | OK | `RT_MOCKUP_AXES_MISMATCH` fixture |
| 1f | 1 | 1e | ~2000 (deletion) | **size:exception** | full chain, files absent |
| 2a | 2 | 1f | ~180 | OK | `--emit-row-types` diff |
| 2b | 2 | 2a | ~300 | OK (atomic commit) | `test-framework-audit.php` |
| 3a | 3 | 2b | ~320 | OK | `test-write-path.php` |
| 3b | 3 | 3a | ~220 | OK | `test-write-path.php` |
| 4a | 4 | 3b | ~300 | OK | `test-framework-audit.php` |
| 4b | 4 | 4a | ~280 | OK | `test-framework-audit.php` |
| 4c | 4 | 4b | ~350 | OK | full chain, file absent |
| 5a | 5 | 4c | ~200 | OK | manifest fixture (WP sandbox) |
| 5b | 5 | 5a | ~180 | OK | ledger fixtures |
| 5c | 5 | 5b | ~150 | OK | precharge fixtures |
| 6 | 6 | 5c | ~300 | OK | `RT_BESPOKE_UNDECLARED` fixtures |

Runtime harness (all PRs): `php skills/framework-audit/assets/framework-audit.php && php
tests/test-container-hygiene.php && php tests/test-framework-audit.php && php
tests/test-audit-signals.php && php tests/test-write-path.php` (`CONTRIBUTING.md:234`) — the green
check for every PR below is this full chain unless a narrower command is named.

Rollback boundary per PR: the PR's own branch is deletable without touching its predecessor, in
reverse-order only (see Chain topology).

**size:exception reasons** (must be pasted verbatim into each PR description):
- 1b/1c: irreducible page-set content (6 / 7 pages sharing one generation pass); a per-page split
  yields PRs that render nothing and pass nothing.
- 1f: ~2000-line pure deletion whose correctness is already discharged by 1a–1e proving the
  generator's output passes every mockup rule before the deletion lands.

## Slice 1 — Break the two-chassis bottleneck (`client-chassis-generation`)

- [x] 1a.1 RED: hash/`cmp` baseline of current `index.html` output.
- [x] 1a.2 Key `$css` (`_build-gallery.php:6339` region) so gallery-only chrome is excluded from
      chassis; `implode()` at `:16621` unaffected.
- [x] 1a.3 Emit chassis `:root` + shell to `assets/chassis/<site-type>.html`, gitignored.
      **Path corrected during apply** (see design.md D1 "Path correction"): the originally specified
      `assets/gallery/chassis/<site-type>.html` collided with `framework-audit.php`'s
      `RT_GALLERY_NOT_DISTINCT` walk (`#(^|/)gallery/#`, `:2683`), which FAILs any `.html` under
      `gallery/` that renders zero `<section class="strip">` — correct for the multi-strip catalog
      page, wrong for a single-site chassis, which can never have one. `assets/chassis/` is a
      sibling of `assets/gallery/`, not a child of it, so the rule never evaluates it; `.gitignore`
      moved with it. `framework-audit.php` itself is untouched — the path was wrong, not the rule.
- [x] 1a.4 GREEN: full chain green.
      **`cmp` proof, corrected.** A naked full-file `cmp` of `index.html` before/after is
      structurally impossible for any generator edit: `_gallery-fingerprint.php` deliberately hashes
      `_build-gallery.php`'s own source as an input, and line 1 of `index.html` is that fingerprint
      comment, so it changes on every edit to the generator by design — this is the fingerprint
      doing its job, not drift. **The correct invariant is: everything after line 1 is
      byte-identical, and total file length is unchanged** (sha256 hex is fixed-width). Verified:
      `tail -n +2` of both old and new `index.html`, `cmp` exit 0; both files 9,068,304 bytes.
      **Runtime harness**: `php skills/framework-audit/assets/framework-audit.php` →
      **0 FAIL / 4 WARN / 0 JUDGE**, the 4 WARNs are exactly the pre-existing word-budget ones
      (elementor-core 588, html-mockup 582, web-templates 559, woocommerce 597) — no
      `RT_GALLERY_NOT_DISTINCT`, no `RT_ORPHAN_FILE` regression. The `RT_ORPHAN_FILE` risk on the two
      new chassis files (unrelated to the path collision — `html_assets_deep()` walks the corrected
      path too) is closed by a prose pointer in `references/mockup-guide.md` § "Chassis and anchor"
      (not `SKILL.md`, which sits at 582/600 words with a standing WARN and no budget left) naming
      `assets/chassis/` and both site-type files, reachable transitively from `SKILL.md`'s existing
      `references/mockup-guide.md` pointers. `php tests/test-container-hygiene.php` 81 OK/0 FAIL;
      `php tests/test-framework-audit.php` 664 OK/0 FAIL; `php tests/test-audit-signals.php`
      22 OK/0 FAIL; `php tests/test-write-path.php` 428 OK/0 FAIL — **1195/1195, full chain green.**
- [ ] 1b.1 RED: `fx_mockup()` fixture asserting `RT_MOCKUP_NO_AXES` stays silent on generated
      corporate chassis (fails until body markup exists).
- [ ] 1b.2 Implement corporate chassis body markup, 6 pages, from the shared in-memory tables.
- [ ] 1b.3 GREEN: fixture passes; full chain green. **size:exception** (see reason above).
- [ ] 1c.1 RED: same fixture pattern for ecommerce chassis.
- [ ] 1c.2 Implement ecommerce chassis body markup, 7 pages.
- [ ] 1c.3 GREEN: fixture passes; full chain green. **size:exception** (see reason above).
- [ ] 1d.1 RED: fixture — generator present, chassis output absent → assert `RT_CHASSIS_NOT_BUILT`
      FAILs (no such row exists yet).
- [ ] 1d.2 Implement `RT_CHASSIS_NOT_BUILT` mirroring `RT_GALLERY_NOT_BUILT`
      (`framework-audit.php:2604-2615`); document the row in `CONTRIBUTING.md` (`RT_ROWTYPE_UNDOCUMENTED`, `:89`).
- [ ] 1d.3 Extend `$anchored_required` (`:2076-2079`) to the new chassis paths; confirm
      `RT_GALLERY_STALE` covers them via the existing input digest.
- [ ] 1d.4 Verify every `RT_MOCKUP_*` row against generated chassis output (NO_AXES,
      ANCHOR_UNDECLARED, DISCLOSURE_STATE, GRID_AUTOFILL, FONT_NOT_EMBEDDED, BLEED_FIXED_BAND,
      BLEED_NOT_MEDIA).
- [ ] 1d.5 GREEN: fixture passes; full chain green.
- [ ] 1e.1 RED: fixture — label `scale: contained` beside `--fs-h1-max: 53` (wrong value) → confirm
      `RT_MOCKUP_AXES_MISMATCH` does NOT fire today (the gap).
- [ ] 1e.2 Implement `axis_token_values()` over `axis_rows_for()` (`:1498`); rewrite
      `RT_MOCKUP_AXES_MISMATCH` (region `:2090-2122`) to compare token value, not label.
- [ ] 1e.3 GREEN: mismatch fixture FAILs; label-and-value-agree fixture stays silent; full chain green.
- [ ] 1f.1 Confirm full chain is green with generator chassis in place, BEFORE deleting anything
      (Slice 1's own rollback constraint: deletion is the last step).
- [ ] 1f.2 Delete `html-mockup/assets/corporate-mockup.html`, `ecommerce-mockup.html`.
- [ ] 1f.3 Update `html-mockup/SKILL.md` and `mockup-guide.md` to run the generator, never copy.
- [ ] 1f.4 GREEN: repo-wide search confirms zero references to deleted files; full chain green.
      **size:exception** (see reason above).

## Slice 2 — One axis registry (`style-axes`)

Reconciling design's "one atomic commit" with the table's 2a/2b split: **2a is a pure,
behavior-preserving refactor** (still 5 axes — safe on its own). **2b is the atomic unit** —
widening to 8 axes without the 5 anchors' `**Axes:**` lines updated in the same commit FAILs every
anchor instantly via `RT_PERS_BAD_AXIS`, so 2b lands as one commit, not split further.

- [ ] 2a.1 RED: capture `--emit-row-types` output baseline before refactor.
- [ ] 2a.2 Introduce `nm_axes()` (5 existing axes only) consolidating `$PERS_AXES` (`:1016-1022`),
      `axis_matches()`'s `$labels` (`:1590`), `axis_signature_of_block()`'s `$props` (`:1553`), and
      the `RT_MOCKUP_AXES_MISMATCH` regex alternation (`:2096`) into one source.
- [ ] 2a.3 GREEN: `--emit-row-types` output byte-identical; full chain green (pure refactor).
- [ ] 2b.1 RED: `fx_pers()` (`tests/test-framework-audit.php:300`) called with undefined `ornament`
      → assert `RT_PERS_BAD_AXIS` FAILs; boundary fixture at 2/8 shared expected to pass, 3/8
      expected to FAIL — both unreachable until `fx_pers()` takes 8 params.
- [ ] 2b.2 **ONE COMMIT**: widen `nm_axes()` to 8 axes (accent policy, chassis, ornament as marker
      axes) + update all 5 anchor blocks' `**Axes:**` lines + change threshold `>1`→`>2` at `:1103`
      + rename `RT_PERS_TOO_SIMILAR`→`RT_STYLE_TOO_SIMILAR` + widen `fx_pers()` to 8 params. Do not
      split across commits.
- [ ] 2b.3 Re-baseline in the same commit: `RT_GALLERY_NOT_DISTINCT` (28), `RT_PROOF_NOT_DISTINCT`
      (21), `RT_STYLE_TOO_SIMILAR` (13), `RT_MOCKUP_AXES_MISMATCH` (9), `RT_AXIS_VALUE_MISSING` (33).
- [ ] 2b.4 GREEN: 2/8 fixture passes, 3/8 FAILs; full chain green with re-baselined totals.
- [ ] 2b.5 (No verifier — code review only) Confirm `RT_STYLE_TOO_SIMILAR`,
      `RT_GALLERY_NOT_DISTINCT`, `RT_PROOF_NOT_DISTINCT` all call the same shared comparator; no
      independent fork introduced.

## Slice 3 — Colour and photographic tone (`colour-and-tone-system`)

- [ ] 3a.1 **RED (free)**: extend `tests/test-write-path.php` ground whitelist (`:2327`) from
      `paper/warm/cool/ink` to 9 names, and `4 === count($suelos)` (`:2339`) to `9 ===` — FAILs
      immediately against the still-4-row `design-system.md`.
- [ ] 3a.2 RED: extend the AAA 7:1 loop (`:654-669`, today iterates `$BRANDS` only — verified gap:
      the 4 tabled house grounds are never body-contrast-gated) to also run over `$GROUND`'s 4 base
      positions.
- [ ] 3a.3 RED: add the ~20-line `$GROUND` drift assertion (in scope per Engram #353) comparing
      `_build-gallery.php`'s `$GROUND` literal (`:238-243`) against `design-system.md`'s parsed
      ground table (`:304-316`).
- [ ] 3a.4 Add 5 ground rows to `design-system.md`; recompute every derived ratio (`:335-394`) per
      family: body ≥4.5:1, inverse surface ≥3:1, hairline 1.05–2.5:1, ground 7:1.
- [ ] 3a.5 Widen `$GROUND` and `$ACCENT_BY_GROUND` to 9 families in `_build-gallery.php`; each
      accent clears 4.5:1 on bg and bg-alt (`:677-688`).
- [ ] 3a.6 GREEN: whitelist/count/AAA-loop/drift assertions all pass at 9; full chain green.
- [ ] 3b.1 RED: fixture — style A `$INK_TINT=0.30`, style B `0.60` → differing hues, both within
      `ink_quant_bound()` of `ink_ends()`'s convergence assertion (`:895`).
- [ ] 3b.2 RED: fixture — style declares ink position `none` → no `filter:url()` emitted (`:9342`);
      convergence (`:895`), spread (`:925`), endpoint-collision (`:937`) never evaluated for it.
- [ ] 3b.3 RED: fixture — channel spread of 14 (< the 20 floor at `:925`) → ink gate FAILs.
- [ ] 3b.4 Implement `$INK_TINT_BY_STYLE` (shaped like `$INK_GRADE`, `:798-806`); read per-style at
      the two existing call sites `ink_of()` (`:1011`) and the brand loop (`:1021`) — tint is
      already a parameter of `ink_ends()` (`:877`) and `ink_of()` (`:991`); implement `none` as an
      identity grade with zero new gate exemptions.
- [ ] 3b.5 Derive `soft-shadow` `--elev-rest`/`--elev-hover` via `color-mix` off `--c-text`,
      replacing the fixed literal (`design-system.md:543`); confirm light/dark grounds diverge.
- [ ] 3b.6 GREEN: all ink fixtures pass; full chain green.
- [ ] 3b.7 Cross-cutting (proposal Success Criteria, not a spec scenario): render one archetype
      under 4 styles from different catalog groups via `visual-verification`, and the same
      photograph under 4 styles — histograms must measurably differ.

## Slice 4 — The style catalog (`style-catalog`)

- [ ] 4a.1 RED: fixture — style names `Canela Deck` (absent from `nm_font_registry()`) →
      `RT_MOCKUP_FONT_NOT_EMBEDDED` FAILs.
- [ ] 4a.2 Create `ux-design-system/references/style-catalog/` (`_README.md`, `_backlog.md`); port
      the 5 existing anchors as `STY-*.md`, each declaring all 8 axis positions + toggle precharge.
- [ ] 4a.3 GREEN: font fixture behaves as specified; full chain green.
- [ ] 4b.1 RED: fixture — reused embedded family (`Archivo`/`Archivo Expanded` pattern) →
      `RT_MOCKUP_FONT_NOT_EMBEDDED` does not FAIL.
- [ ] 4b.2 RED: fixture — 8-entry catalog, entry #8 shares 4/8 with entry #3 →
      `RT_STYLE_TOO_SIMILAR` FAILs.
- [ ] 4b.3 Author 3 new `STY-*.md` entries (8 total); each clears ≤2/8 shared against every other
      entry; budget SIL OFL faces / re-weighted embedded families per style.
- [ ] 4b.4 GREEN: full 8-entry catalog passes `RT_STYLE_TOO_SIMILAR` pairwise at max 2/8; fonts
      pass; full chain green.
- [ ] 4c.1 Confirm full chain green with the catalog complete, BEFORE deleting
      `design-personalities.md` (same deletion-last discipline as Slice 1).
- [ ] 4c.2 Delete `design-personalities.md`; update `SKILL.md` (fix stale "Four anchors" at `:22`),
      `design-tokens.md`, `layout-patterns.md`, `motion.md` for 5→8 axes.
- [ ] 4c.3 GREEN: zero references to the deleted file remain; full chain green.

## Slice 5 — Intake, persistence, ledger (`art-direction-ledger`, `manifest-section-contract`)

- [ ] 5a.1 RED: fixture — `es_manifest_record('design', …)` called during resolution →
      `es_manifest_read()['design']` holds non-empty `STY-*` id (no writer call exists yet).
- [ ] 5a.2 Wire the call site (`es-builder.php:2440-2462`, section declared `:2429-2431`); extend
      `recommender.md:41-52` intake for style pick, negative brief, rejected colour temperature.
- [ ] 5a.3 Remove the stale "unfulfilled" annotation at
      `docs/superpowers/specs/2026-08-14-perceptual-axes-design.md:172` now the claim is true.
- [ ] 5a.4 RED: fixture — style resolved, then re-resolved same session → `design` section
      overwrites, never appends.
- [ ] 5a.5 GREEN: manifest fixtures pass; full chain green.
- [ ] 5b.1 RED: fixture — no `STY-QUARRY` in last 5 rows → `RT_STYLE_REPEATS_RECENT` silent (rule
      doesn't exist yet).
- [ ] 5b.2 RED: fixture — `STY-QUARRY` at row 3 of last 5 → WARN, audit still exits 0.
- [ ] 5b.3 RED: fixture — `STY-QUARRY` at row 6 (outside window) → stays silent.
- [ ] 5b.4 Create `shipped-log.md` (empty ledger); implement `RT_STYLE_REPEATS_RECENT` over the
      last 5 rows, WARN-only (`house-rules.md:31`).
- [ ] 5b.5 GREEN: all 3 ledger fixtures pass exactly; full chain green.
- [ ] 5c.1 RED: fixture — style declares 6 toggles, project ships only 5 at declared value → new
      precharge rule FAILs, naming the toggle and style.
- [ ] 5c.2 RED: fixture — style A declares 2, style B declares 6, project on A ships exactly 2 →
      audit does not FAIL for "too few" (no universal floor).
- [ ] 5c.3 Each `STY-*.md` declares its precharge list; implement the per-style precharge rule;
      extend `web-templates/references/toggles.md` intake wiring.
- [ ] 5c.4 GREEN: both precharge fixtures pass exactly; full chain green.

## Slice 6 — `ROUTE-BESPOKE` (`bespoke-route`)

- [ ] 6.1 RED: fixture — bespoke manifest missing `ornament` → `RT_BESPOKE_UNDECLARED` FAILs,
      naming the missing axis (no such rule exists yet).
- [ ] 6.2 RED: fixture — complete manifest (8 axes + wireframe) → `RT_BESPOKE_UNDECLARED` does not FAIL.
- [ ] 6.3 RED: fixture — promoted entry shares 3/8 with an existing entry → `RT_STYLE_TOO_SIMILAR`
      FAILs (promotion grants no exemption).
- [ ] 6.4 Implement zero-precharge intake (8 explicit axis answers + wireframe) before
      builder-core; `RT_BESPOKE_UNDECLARED`; confirm no accessibility gate is skipped (AA, AAA 7:1,
      4.5:1 eyebrow, one `<h1>`, Lighthouse ≥90, touch targets, `RT_MOCKUP_DISCLOSURE_STATE`); wire
      ledger registration and the promotion path into `STY-*`.
- [ ] 6.5 GREEN: all 3 fixtures pass exactly; full chain green — this PR is the tracker's final
      candidate for merge to `main`.

## Re-baselining tasks (explicit, per proposal/design)

- [ ] Re-baseline `RT_GALLERY_NOT_DISTINCT` (28→8-axis), `RT_PROOF_NOT_DISTINCT` (21→8-axis),
      `RT_STYLE_TOO_SIMILAR` (13, renamed), `RT_MOCKUP_AXES_MISMATCH` (9, value-check), and
      `RT_AXIS_VALUE_MISSING` (33) — all land in PR 2b, same commit as the rule they pin.
- [ ] Re-baseline total assertion count ≥ 1195 (measured baseline: container-hygiene 81 +
      framework-audit 664 + audit-signals 22 + write-path 428) after every slice; confirm
      `RT_UXDS_NO_AXIS_STEP` and `RT_QA_NO_AXIS_CHECK` still fire at the end of Slice 6.
- [ ] Confirm final audit run stays at 0 FAIL / 4 WARN (pre-existing word-budget WARNs) with no new
      WARN introduced.
