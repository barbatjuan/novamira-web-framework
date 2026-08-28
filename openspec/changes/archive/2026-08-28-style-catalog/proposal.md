# Proposal: Style Catalog — art direction per project

## Intent

Every site the framework produces reads as the same site with a different logo, **including its
colour temperature**. This is measured, not felt. Hex census over `skills/**` (178 distinct
colours, 983 occurrences): darks `L≤22` are **64% blue** (231/363), lights `L≥88` are **43% blue**
(178/416), mid accents **52% orange** (106/204). The four most-repeated hexes — `#0e1113`,
`#171b1e`, `#f4f6f7`, `#15181a` — are one cool blue-grey at four lightnesses.

Six root causes (evidence in Engram `#347`):

| # | Cause | Anchor |
|---|---|---|
| 1 | Client path never touches the generator; it is 6 lines wide | `html-mockup/SKILL.md:41-43` |
| 2 | Every photo shares one tonal envelope — ink is re-pinned to the neutral's own luminance | `_build-gallery.php:829-850`, `:877-948`, `$INK_TINT=0.45` `:784` |
| 3 | Two gates delete the accent where it is loudest; 3 of 4 grounds share `#8C3A1F` | `:1775-1788`, `:16909-17123`, `design-system.md:422-425`, `:642-647` |
| 4 | The chassis around each section never varies; below 1024px one layout | `render-defects.md:50-58`, `:9442-9444` |
| 5 | No inter-project memory; `RT_MOCKUP_AXES_MISMATCH` checks the label, not the value | `mockup-guide.md:455-458` |
| 6 | 39 toggles catalogued, 1 moved off default across 67 strips | `_build-gallery.php:1097-1100` |

### Why this is not attempt 2 with new nouns

Attempt 1 (`docs/superpowers/specs/2026-08-12-design-personalities-design.md`) varied the skin.
Attempt 2 (`2026-08-14-perceptual-axes-design.md:6`) diagnosed that, built five perceptual axes,
and fixed it — **inside the gallery**. The complaint survived because the path a client site
actually takes never reaches the gallery: it copies one of two hand-maintained HTML chassis and
re-points five token lines. `mockup-guide.md:436-447` already recorded the consequence — every
corporate site shipped `PERS-INSTITUTIONAL`, every commerce one `PERS-MATTER`, *"the tamest corner
of the system, and every client site started there."*

Both prior attempts began with a catalog. **Here the catalog is Slice 4, not Slice 1.** Slice 1 is
the delivery path. If this change ships only a bigger catalog, it has failed the same way twice
before and is failing again.

## Scope

### In Scope — six slices, in order

1. **Break the two-chassis bottleneck.** Generate the client chassis from `_build-gallery.php`;
   delete `corporate-mockup.html` and `ecommerce-mockup.html`. Retire the "re-point 6 lines"
   instruction. Turn `RT_MOCKUP_AXES_MISMATCH` into a **value** check.
2. **Three new axes** — accent policy, chassis, ornament — registered in `framework-audit.php`
   **before** any style names them. `RT_PERS_TOO_SIMILAR` → `RT_STYLE_TOO_SIMILAR`: max 2 of 8
   shared positions.
3. **Colour and photographic tone.** 9 ground families replace 4 literal rows; per-family accent;
   per-style `$INK_TINT` and luminance re-pin plus a `none` ink position; `soft-shadow` derived
   from `--c-text`.
4. **The style catalog.** `STY-*.md` replaces `design-personalities.md`; the 5 anchors become
   catalog entries. **Eight styles ship in v1**, separated at the 8-axis gate; the remaining four
   from the proposed table move to `_backlog.md` alongside the movements that need motion/ornament
   systems. Eight is a font-budget decision: only 7 faces are embedded today.
5. **Intake, persistence, ledger.** Extend `recommender.md:41-52` with style pick, negative brief,
   and rejected colour temperature. Wire the `design` manifest writer. Add `shipped-log.md` +
   `RT_STYLE_REPEATS_RECENT` (**WARN, not FAIL** — `house-rules.md:31`: *"a gate that always fails
   is a gate everyone learns to skip"*). The ledger window is the **last 5** deliveries.
   **Each `STY-*` declares which toggles it precharges and to what value** — no universal minimum
   count, because an arbitrary N becomes a box to tick rather than a design decision.

6. **`ROUTE-BESPOKE`** — the from-scratch route, deferred so the catalog proves itself on real
   client work first. Deliberately the *more expensive* route: zero precharge, eight explicit
   answers, declared wireframe, every a11y gate, ledger registration, and a promotion path back
   into the catalog. Enforced by `RT_BESPOKE_UNDECLARED`.

### Out of Scope

- **Section order per archetype stays fixed.** Reordering was tried and withdrawn for stated
  reasons (`toggles.md:207-211`); archetype DNA is argument order.
- **Templates `TPL-*` stay.** Architecture and look are orthogonal selectors.
- **No accessibility or legal floor moves** — AA contrast, the AAA 7:1 ground gate
  (`_build-gallery.php:654-669`), 4.5:1 accent-as-eyebrow (`:677-688`), one `<h1>`, Lighthouse ≥90,
  touch targets, `<details>` semantics.
- Migration of delivered sites; `elementor-core`/`divi-core` internals; the motion/ornament systems
  the backlog movements need (Kinetic, Cyberpunk, Y2K, Retro, Playful, Feminine, Editorial Fashion,
  Experimental).

## Capabilities

### New Capabilities
- `client-chassis-generation`: the client mockup chassis is generator output, not a hand-maintained
  file; axis labels are value-checked.
- `style-axes`: accent policy, chassis and ornament as spectra with named positions, plus the
  8-axis separation gate.
- `colour-and-tone-system`: ground families, per-family accent, per-style photographic tonal
  envelope, derived elevation.
- `style-catalog`: the `STY-*` catalog (8 in v1) with the anchors as entries.
- `bespoke-route`: the `ROUTE-BESPOKE` contract and its promotion path (Slice 6).
- `art-direction-ledger`: intake questions, `design` manifest persistence, `shipped-log.md`, and
  the recent-repeat advisory.

### Modified Capabilities
- `manifest-section-contract`: `design` gains a real writer. The requirement *"A Known-False
  Promise About `design` Is Marked, Not Left Standing"* is superseded, and that spec's explicit
  out-of-scope line (*"Wiring `design`/`delivery` to real writers"*) is what Slice 5 delivers.

## Approach

Order is the argument. The sameness is **load-bearing**: ~110 assertions in
`tests/test-framework-audit.php` and 26 audit rules pin the current numbers, and
`RT_PERS_BAD_AXIS` (`framework-audit.php:95`, enforced `:1081-1084`) FAILs any style naming a
position the axis table does not define. Therefore **the audit change lands before the style that
needs it**, every time. `axis_matches()` (`:1589-1599`) is shared by `RT_PROOF_NOT_DISTINCT` and
`RT_GALLERY_NOT_DISTINCT`, so the shared axis list is edited once and both are re-baselined
together.

Slice 1 first because it is the only slice on the path real client sites take; slices 2–3 supply
the vocabulary and the colour range; slice 4 spends it; slice 5 makes it stick across projects.
Slice 6 is deliberately last: `ROUTE-BESPOKE` is the largest single risk here, and designing an
escape hatch before the catalog has been used on real client work is designing it blind.

## Affected Areas

| Area | Impact | Description |
|---|---|---|
| `skills/html-mockup/assets/corporate-mockup.html`, `ecommerce-mockup.html` | Removed | Replaced by generator output |
| `skills/html-mockup/assets/gallery/_build-gallery.php` | Modified | Axis tables, `$ACCENT_BY_GROUND`, `$INK_TINT`, `ink_ends`/`ink_tint`, `sec_open`, scrim override, `$ACCENT_ROLES` |
| `skills/framework-audit/assets/framework-audit.php` + `tests/test-framework-audit.php` | Modified | New/renamed row types; re-baselined fixtures |
| `skills/ux-design-system/references/design-personalities.md` | Removed | → `references/style-catalog/STY-*.md` + `_README.md` + `_backlog.md` |
| `skills/web-templates/references/design-system.md` | Modified | §Ground `:304-316`, §Elevation `:538-544`, §accent whitelist `:411-467`, §Radius `:184-194` |
| `skills/ux-design-system/references/shipped-log.md` | New | The delivery ledger |
| `skills/html-mockup/SKILL.md`, `references/mockup-guide.md` | Modified | Retire the 6-line re-point |
| `skills/web-templates/references/recommender.md`, `toggles.md` | Modified | Intake + toggle precharge |
| `skills/ux-design-system/SKILL.md`, `design-tokens.md`, `layout-patterns.md`, `motion.md` | Modified | 5→8 axes; fix stale "Four anchors" at `SKILL.md:22` |
| `skills/qa-review/references/house-rules.md`, `agents/novamira-web-orchestrator.md`, `assets/fonts/_fonts.php`, `CONTRIBUTING.md` | Modified | Style-conditional gates, new faces, docs |

## Risks

| Risk | Likelihood | Mitigation |
|---|---|---|
| **Font budget.** Only 7 faces are embedded (`_fonts.php:63-69`); `RT_MOCKUP_FONT_NOT_EMBEDDED` FAILs any other family. 12 styles want display faces | **High** | Budget ~6 new SIL OFL faces; styles without one reuse an embedded family at another weight/width (`Archivo`/`Archivo Expanded` already prove the pattern). Treat the face list as a Slice-4 gate, not an afterthought |
| **Contrast regression surface.** 9 ground families × every derived ratio in `design-system.md:335-394`, plus the 7:1 and 4.5:1 gates | **High** | Recompute all ratios per family as a Slice-3 exit criterion; hue anchor moves, floors do not; no ground family ships until it clears |
| **Test re-baselining.** ~110 assertions and 26 rules pin the current numbers | **High** | Audit-before-style ordering; re-baseline `RT_GALLERY_NOT_DISTINCT` (28), `RT_PROOF_NOT_DISTINCT` (21), `RT_PERS_TOO_SIMILAR` (13), `RT_MOCKUP_AXES_MISMATCH` (9), `RT_AXIS_VALUE_MISSING` (33) in the same commit as the rule they belong to |
| **`ROUTE-BESPOKE` becomes the new untended default corner** (deferred to Slice 6 for this reason), exactly as `PERS-INSTITUTIONAL` did (`mockup-guide.md:436-447`) — and the repo already ran the cheap version: `recommender.md:249-254`, where no archetype meant every site shipped the same Nosotros and the same Contacto | **Med-High** | Deliberately the more expensive route: zero precharge, 8 explicit answers, declared wireframe, `RT_BESPOKE_UNDECLARED` FAIL, ledger registration, and a promotion path that feeds the catalog instead of bypassing it |
| **Slice 1 deletes two shipped assets** before the generator has proven it can replace them | Med | Generator output must render and pass every mockup rule *before* the deletion commit; deletion is the last step of Slice 1, not the first |
| **Scope.** 12+ files, 6 slices, well past the 400-line review budget | High | `delivery_strategy: auto-chain` — one PR per slice, each independently verifiable |
| Advisory `RT_STYLE_REPEATS_RECENT` gets ignored | Med | Accepted tradeoff; WARN is deliberate per `house-rules.md:31` |

## Rollback Plan

Branch-scoped: `feat/style-catalog` off `feat/manifest-truth-repair`, one PR per slice. Revert **in
reverse slice order** — reverting an earlier slice while a later one stands would leave a style
naming an axis position the table no longer defines, which `RT_PERS_BAD_AXIS` FAILs. Slice 1's
deletions restore from git. Full abandonment = delete the branch; `main` is untouched.

## Dependencies

- The manifest writer landed in `feat/manifest-truth-repair` (Slice 5 depends on it) — the reason
  this branch is not based on `main @ 35a38b4`.
- SIL OFL typefaces sourced and subsetted to `woff2` before Slice 4 — one per v1 style that needs
  a display face it cannot get by re-weighting an already-embedded family.

## Success Criteria

Measured baseline in this checkout, 2026-08-28: **1195 OK / 0 FAIL** across the four test files
(`test-container-hygiene` 81, `test-framework-audit` 664, `test-audit-signals` 22,
`test-write-path` 428 — the documented chain at `CONTRIBUTING.md:234`);
audit **0 FAIL / 4 WARN** (pre-existing word-budget WARNs).

- [ ] `php skills/framework-audit/assets/framework-audit.php` — 0 FAIL; no new WARN.
- [ ] Full chain green after re-baselining; total assertions ≥ 1195; `RT_UXDS_NO_AXIS_STEP` and
      `RT_QA_NO_AXIS_CHECK` still fire.
- [ ] `RT_STYLE_TOO_SIMILAR` passes over the 8-style v1 catalog at **≤2 of 8** shared positions.
      Stated plainly: today `RT_PERS_TOO_SIMILAR` FAILs at `count($shared) > 1` of 5
      (`framework-audit.php:1103`), so 4 of 5 axes must differ — 80%. The new gate forces 6 of 8 —
      75%. Absolute separation rises; proportional separation relaxes slightly. This is a
      consequence of widening the axis set, not a tightening, and is recorded as such.
- [ ] Every ratio in `design-system.md:335-394` recomputed against all **9** ground families: body
      ≥4.5:1, inverse surface ≥3:1, ground 7:1, accent-as-eyebrow 4.5:1. **Zero new AA failures.**
- [ ] Every ink still clears channel spread ≥20 (`_build-gallery.php:925`), convergence (`:895`),
      split-tone ratio (`:1053`).
- [ ] **Attempt 2's own acceptance test** (`2026-08-14-perceptual-axes-design.md:210-215`): *"the
      same content and the same structure, rendered under two personalities, must be unmistakably
      different at a glance."* Render one archetype under 4 styles from different catalog groups,
      judged via `visual-verification`.
- [ ] **Tonal-envelope histogram test** — the user's literal complaint. Same photograph under 4
      styles; measured histograms must **differ**. Today they are identical by construction
      (`ink_tint()` re-pins to the neutral's own luminance, `:829-850`).
- [ ] `RT_STYLE_REPEATS_RECENT` WARNs against the last **5** ledger rows and stays silent on a
      fresh pick.
- [ ] *(Slice 6)* `RT_BESPOKE_UNDECLARED` FAILs an incomplete bespoke manifest and passes a complete one; the
      promoted `STY-*` clears `RT_STYLE_TOO_SIMILAR`.
- [ ] Zero hand-maintained client chassis remain; no skill references a deleted asset.
- [ ] Toggles: a generated project ships every toggle its resolved `STY-*` declares, at the
      declared value, and no project ships all-defaults (today: 1 of 39 moved across 67 strips).
