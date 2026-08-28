# Design: Style Catalog — art direction per project

> **Size deviation, declared.** The 800-word cap is exceeded (~1,600). Six architectural questions
> had to be resolved concretely with verified file:line seams, and the two prior attempts failed by
> leaving exactly this level unstated. Declared rather than met by dropping content.

## Technical Approach

`_build-gallery.php` is a straight-line program: it fills `$css` (`:6339`), assembles `$html`
(`:16621-16633`), runs ~600 lines of gates over the assembled string, then writes (`:17207`). The
client chassis becomes **a second artifact emitted by the same run from the same in-memory tables**
— not a mode flag, not an extracted module. Axes become **one registry** four consumers derive from.
Colour and tone vary through the ground family's own endpoints, which is the lever that never moved.

## Architecture Decisions

### D1 · The chassis seam (Slice 1)

**Choice**: one run, N artifacts. `_build-gallery.php` keeps writing `index.html` and additionally
writes `chassis/<site-type>.html` from the same `$css` and the same axis/colour tables, with one
`:root` instead of the gallery's per-`[data-anchor]` blocks. `$css` becomes a **keyed** array so
gallery-only chrome (`.gal-top`, `.strip`, `.axis`) is excluded from the chassis document;
`implode()` at `:16621` is unaffected. Output lands at `assets/chassis/`, a sibling of
`assets/gallery/`, not a child of it — gitignored like `index.html` (`.gitignore`).

> **Path correction (PR 1a, applied during apply, not at design time).** This decision originally
> named `gallery/chassis/<site-type>.html`. That path is wrong and was caught by the live audit
> regressing to 2 FAIL / 2 extra WARN: `framework-audit.php`'s `RT_GALLERY_NOT_DISTINCT` walk
> (`#(^|/)gallery/#`, verified at `:2683`) FAILs any `.html` under a `gallery/` path that renders
> zero `<section class="strip">`, and a single-site chassis can never have one. The gate is correct
> for the multi-strip catalog page it was written to protect; the chassis simply does not belong
> under `gallery/` at all. **Do not move the chassis output back under `assets/gallery/` — that
> reintroduces this exact regression.** `html_assets_deep()` (`framework-audit.php:1878-1879`) walks
> `skills/html-mockup/assets` recursively regardless of the subdirectory, so every `RT_MOCKUP_*` rule
> still applies to the chassis at its corrected location — a chassis is a mockup, just not a gallery
> entry, and no rule was weakened to accommodate the move.

| Option | Tradeoff | Verdict |
|---|---|---|
| `--mode=chassis` flag branching the script | Branches the file's most load-bearing decision (`:root` vs `[data-anchor]`, argued at `:6370-6404`) and forces ~600 lines of gallery gates behind `if (mode)` guards — where gates quietly stop firing | Rejected |
| Extract shared CSS/tokens into an includable module both consume | Correct in the abstract; costs thousands of moved lines (`$css` heredocs at `:6799`–`:10422`) and risks byte-drift on the flagship artifact | Rejected |
| Codegen the two `.html` files | Same hand-maintained artifact with extra steps; the 6-line path survives | Rejected |
| **One run, N artifacts** | The file grows; two programs share a namespace | **Chosen** |

**Rationale**: zero lines move, so `cmp` of `index.html` before/after is the whole regression proof —
the generator already guarantees byte-identical reruns (`:14-17`). It also extends the file's own
stated reason for existing ("the shared half exists once, in this file") rather than contradicting it.

**Consequences**: `$anchored_required` (`:2076-2079`) repoints to the generated paths; a new
`RT_CHASSIS_NOT_BUILT` mirrors `RT_GALLERY_NOT_BUILT` (`:2604-2615`); `nm_gallery_input_digest()`
already covers the new outputs' inputs, so `RT_GALLERY_STALE` extends free. Every new row id needs a
`CONTRIBUTING.md` row in the same commit (`RT_ROWTYPE_UNDOCUMENTED`, `:89`).

**Value check** (`RT_MOCKUP_AXES_MISMATCH`): `axis_rows_for($ds_src, $pos)` already parses
`design-system.md`'s position rows for `RT_AXIS_VALUE_MISSING` (`:1498`). A new
`axis_token_values()` over the same rows gives position → token value; the gate compares the `:root`'s
actual `--fs-h1-max` against it instead of the label (today's gap, admitted at `:1935-1939`).

### D2 · One axis registry (Slice 2)

Four hardcoded copies of the axis list exist today and must agree: `$PERS_AXES` (`:1016-1022`),
`axis_matches()`'s `$labels` (`:1590`), `axis_signature_of_block()`'s `$props` (`:1549-1553`), and
the `RT_MOCKUP_AXES_MISMATCH` regex alternation (`:2096`).

**Choice**: a single `nm_axes()` returning `axis => array('positions', 'prop'|null, 'marker')`; the
four consumers derive from it. The three new axes are **marker axes** (`/* chassis: bleed */`), like
composition — they need no custom property, and the marker parser already exists.

**Ordering**: Slice 2 is **one atomic commit**. Widening the registry alone FAILs instantly via
`RT_PERS_BAD_AXIS` ("names no position for axis …", `:1081`), so registry + the 5 anchors' `**Axes:**`
lines + the `>1`→`>2` threshold (`:1103`) + the rename + fixtures land together. `fx_pers()`
(`test-framework-audit.php:300`) gains three parameters; every caller re-baselines in that commit.

**Exit criterion**: assign the 24 new positions so no anchor pair exceeds 2 of 8. Adding 3 axes can
push a pair from 1/5 to 4/8 if positions collide.

### D3 · Nine ground families (Slice 3)

**Shape**: the `design-system.md` table stays the parsed source of truth (`:310-316`); `$GROUND`
(`_build-gallery.php:238-243`) keeps transcribing it, per the existing convention.

**Where the recomputation lives — a test, not a promise**: `tests/test-write-path.php` already reads
the ground rows out of `design-system.md`, whitelisted at `:2327` (`paper/warm/cool/ink`), asserts
`4 === count($suelos)` at `:2339`, and recomputes every derived ratio per family at `:2356-2380`
(body ≥4.5:1, inverse surface ≥3:1, hairline 1.05–2.5:1). **The RED test is that edit**: extend the
whitelist to 9 names and the count to 9; it fails until the doc grows 5 rows, then recomputes.

**Gap found and closed here**: the generator's 7:1 AAA gate runs over `$BRANDS` only (`:654-669`) —
the four tabled grounds are never body-contrast-gated. Slice 3 runs the same loop over `$GROUND`.
The 4.5:1 eyebrow gate (`:677-688`) already covers both, via `$ACCENT_BY_GROUND`.

`soft-shadow` moves from the literal `0 1px 2px rgba(0,0,0,.04)` (`design-system.md:543`,
`_build-gallery.php:258`) to a `color-mix` off `--c-text`, matching how `accent-glow` is already
written (`:263-266`) — so this follows an existing pattern rather than inventing one.

### D4 · Per-style tone and the `none` position (Slice 3)

`ink_ends($gr, $accent, $tint)` (`:877`) and `ink_of(…, $tint)` (`:991`) **already take the tint as a
parameter**. Per-style tint is a change at two call sites (`:1011`, `:1021`) plus a
`$INK_TINT_BY_STYLE` table shaped exactly like the existing per-anchor `$INK_GRADE` (`:798-806`);
`$INK_TINT = 0.45` (`:784`) becomes its default.

**The luminance re-pin stays.** It is what stops lifted blacks (`:906-919`). The envelope varies
through five levers, of which only two ever moved: (a) the ground family's own extremes → the neutral
endpoint (`:880`), (b) `gamma`, (c) `sat`, (d) tint weight → hue, (e) `none`. Nine families make (a)
real. That is the honest answer to "the envelope cannot vary": it varies through the endpoints, and
the endpoints were the thing held constant.

**`none` = an identity grade, not a null.** `sat = 1`, identity `tableValues`, `ends => null`, and no
`filter:url()` emitted (`:9342`). Every existing gate then handles it on its own terms, with **no new
exemptions**:

| Gate | Behaviour under `none` |
|---|---|
| convergence `:895`, spread ≥20 `:925`, endpoint-collision `:937` | `ink_ends()` is never called — not evaluated, exactly as the spec's `none` scenario states |
| split-tone ratio `:1053-1065` | Passes via its own existing guard `if ($ink_ends_dev < 1e-9) continue;` (`:1061`) |
| swatch separation ≥10 `:9406-9436` | Runs on raw means and passes wide: raw chroma 38.4 vs 0.0 (`:9390-9391`) against a 10.0 bar |
| ink CSS emit `:9339-9347`, receipt `:17238-17242` | Emit nothing / print `none`; an identity filter is compositing cost for zero effect |

Verified: nothing asserts that a used anchor must carry an ink filter, so `none` needs no gate change.

### D5 · Ledger mechanics (Slice 5)

`shipped-log.md` is an append-only markdown table, newest last:
`| date | project | STY-* | route | ground family |`.

**Who appends**: the delivery step, as an explicit instruction — nothing in this framework writes
markdown into its own repo automatically, and pretending otherwise is the false-promise defect the
`manifest-truth-repair` lineage exists to prevent.

**`RT_STYLE_REPEATS_RECENT` reads the ledger, not a project.** The audit runs offline over the repo
and cannot see `es_manifest_read()` (a WordPress option in a sandbox). So: take the last 5 data rows;
WARN if the **last** row's `STY-*` also appears in the 4 before it. Rejected: reading the project
manifest — unreachable offline. This satisfies all three spec scenarios, including row 6 staying
silent because it falls outside the 5-row slice.

`es_manifest_record('design', …)` (`es-builder.php:2440-2462`) needs no signature change — `design`
is already a declared section (`:2429-2431`) and the writer already re-reads to prove the write landed.

## Data Flow

    design-system.md (9 ground rows, axis token values)
        │ transcribed              │ parsed
        ▼                          ▼
    _build-gallery.php ────────► framework-audit.php (nm_axes() → 4 consumers)
        │ $css (keyed) + tables                    │
        ├──► index.html            (gallery)       │ walks both
        └──► chassis/*.html (client, sibling of gallery/) ◄────┘
                  │ resolved STY-*
                  ▼
        es_manifest_record('design') ──► shipped-log.md ──► RT_STYLE_REPEATS_RECENT (WARN)

## File Changes

| File | Action | Description |
|---|---|---|
| `.../gallery/_build-gallery.php` | Modify | Key `$css`; chassis `:root` + page bodies + gates + writes; `$GROUND` 9 rows; `$ACCENT_BY_GROUND` 9; AAA loop over `$GROUND`; `$INK_TINT_BY_STYLE`; `none`; `soft-shadow`; catalog path at `:48` |
| `framework-audit/assets/framework-audit.php` | Modify | `nm_axes()`; 3 axes; `RT_STYLE_TOO_SIMILAR` (>2/8); value-check `AXES_MISMATCH`; `RT_CHASSIS_NOT_BUILT`, `RT_STYLE_REPEATS_RECENT`, toggle-precharge row, `RT_BESPOKE_UNDECLARED` |
| `tests/test-framework-audit.php` | Modify | `fx_pers()` 8 axes; fixtures for every new/renamed row |
| `tests/test-write-path.php` | Modify | Ground whitelist `:2327` 4→9; count `:2339`; **RED test first** |
| `web-templates/references/design-system.md` | Modify | 5 ground rows; 3 axis tables; derived-ratio table 9 cols; `soft-shadow` derived |
| `ux-design-system/references/style-catalog/STY-*.md`, `_README.md`, `_backlog.md` | Create | 8 v1 entries |
| `ux-design-system/references/design-personalities.md` | Delete | Last step of Slice 4 |
| `ux-design-system/references/shipped-log.md` | Create | The ledger |
| `html-mockup/assets/{corporate,ecommerce}-mockup.html` | Delete | **Last step of Slice 1** |
| `html-mockup/SKILL.md`, `references/mockup-guide.md` | Modify | Step 1 (`:40-43`) and References (`:57-60`): run the generator, never copy |
| `CONTRIBUTING.md`, `.gitignore` | Modify | Row-type docs (`RT_ROWTYPE_UNDOCUMENTED`); ignore `assets/chassis/` (sibling of `assets/gallery/`, see D1 path correction) |
| `web-templates/references/recommender.md`, `toggles.md`; `elementor-core/assets/es-builder.php` | Modify | Intake `:41-52`; precharge; `design` writer call site |

## Testing Strategy — the RED test per slice

| Slice | Failing test written first |
|---|---|
| 1a–1c | `cmp` of `index.html` before/after (byte-identity), then a chassis fixture through `fx_mockup()` asserting `RT_MOCKUP_NO_AXES` silent |
| 1d | `fx_gal_generator()` present + chassis absent → `RT_CHASSIS_NOT_BUILT` FAILs |
| 1e | Fixture: label `scale: contained` beside `--fs-h1-max: 53` → `RT_MOCKUP_AXES_MISMATCH` FAILs |
| 2a | `--emit-row-types` output identical after registry unification |
| 2b | `fx_pers()` with an undefined `ornament` → `RT_PERS_BAD_AXIS`; pair at 2/8 passes, 3/8 FAILs |
| 3a | `test-write-path.php` whitelist extended to 9 → FAILs on 4 rows |
| 3b | Two styles, tints 0.30/0.60 → differing hues, both within `ink_quant_bound()`; a `none` style leaves every ink gate silent |
| 4 | `RT_STYLE_TOO_SIMILAR` over all 8 entries; `RT_MOCKUP_FONT_NOT_EMBEDDED` on an unregistered family |
| 5 | Ledger fixture: repeat at row 3 of 5 → WARN + exit 0; row 6 → silent |
| 6 | Bespoke manifest missing `ornament` → `RT_BESPOKE_UNDECLARED` FAILs |

## Threat Matrix

N/A — no routing, shell, subprocess, VCS/PR automation, executable-file classification, or
process-integration boundary. The generator's existing `exec('node --check')` (`:16705-16712`) is
untouched by this change.

## Migration / Rollout

17 PRs, `auto-chain` with `chain_strategy: feature-branch-chain`, reverted in reverse PR
order. Estimates (additions + deletions):

| PR | Scope | Est. | Budget |
|---|---|---|---|
| 1a | `$css` keying + chassis `:root` + assembly | ~200 | OK |
| 1b / 1c | Chassis page bodies, corporate (6) / ecommerce (7) | ~450 / ~500 | **over** |
| 1d | Chassis gates + `RT_CHASSIS_NOT_BUILT` + fixtures | ~250 | OK |
| 1e | `AXES_MISMATCH` label→value | ~180 | OK |
| 1f | Delete the two chassis + retire the re-point prose | ~2000 (deletion) | **over** |
| 2a / 2b | Registry unification / 3 axes + threshold + rename | ~180 / ~300 | OK |
| 3a / 3b | 9 grounds + contrast tests / ink tint + `none` + shadow | ~300 / ~220 | OK |
| 4a / 4b / 4c | Catalog format + 5 ported / 3 new + fonts / delete old | ~300 / ~280 / ~350 | OK |
| 5a / 5b / 5c | Manifest writer + intake / ledger + WARN / precharge | ~200 / ~180 / ~150 | OK |
| 6 | `ROUTE-BESPOKE` | ~300 | OK |

**Three PRs cannot fit 400 lines and no further split helps.** 1b/1c are irreducible page-set content
(6 and 7 pages of markup); splitting per page produces PRs that render nothing and pass nothing.
1f is a pure deletion whose review is "does the generated chassis pass every rule the deleted files
passed" — already proven by 1d/1e. These three need an explicit `size:exception` decision before
apply; `sdd-tasks` must surface it rather than discover it.

## Open Questions

- [x] **DECIDED 2026-08-28.** `size:exception` APPROVED for exactly PRs 1b, 1c and 1f, and for
      no others; every other PR holds the 400-line budget. Each of the three carries its written
      reason. Recorded in Engram #353.
- [x] **DECIDED 2026-08-28 — IN SCOPE.** `$GROUND` (`:238-243`) transcribes `design-system.md`
      and nothing gates the drift. The ~20-line assertion in `test-write-path.php` comparing the
      literal against the parsed table lands in Slice 3. Ruled in by the orchestrator: this change
      takes ground families from 4 to 9, so the drift becomes more likely, not less.
- [ ] Fonts: 8 styles against 7 embedded faces (`_fonts.php:63-69`). How many styles re-weight an
      embedded family versus need a new SIL OFL face is a Slice-4 gate, unresolved here.
- [ ] Renaming `_build-gallery.php` (it now emits more than a gallery) would break `$gal_gen`
      (`:2604`), the `.gitignore` anchor and the fingerprint module name. Recommend: do not bundle it.
