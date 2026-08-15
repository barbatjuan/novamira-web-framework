# The axes reach the production mockups — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development or superpowers:executing-plans. Steps use checkbox (`- [ ]`) syntax.

**Goal:** Put the five perceptual axes into the two assets every real project actually starts from, fix the reflow failures they carry, and add the check that stops a future asset shipping without them.

**Architecture:** `corporate-mockup.html` and `ecommerce-mockup.html` each declare one anchor's axis values in `:root`, marked as the value the dialogue re-points per project — the same contract their existing "swap the `:root` tokens" comment already sets. A new audit row FAILs any `html-mockup/assets/*.html` that omits the axis tokens.

**Tech Stack:** Static HTML/CSS, PHP 8, the repo's four suites.

## Global Constraints

- **Never weaken a check to make a row go green** (CONTRIBUTING.md).
- Token VALUES are copied from `skills/web-templates/references/design-system.md` § "Perceptual axes — token values". Never invent a number.
- New `RT_*` needs a `ROW_TYPES` entry, a `CONTRIBUTING.md` row, and a fixture. `COVERAGE_EXEMPT` stays `array()`.
- Mockups stay self-contained: no external CSS/JS/font/image requests, no `@font-face` with a URL.
- Zero horizontal overflow at **320 / 430 / 768 / 1024 / 1280 / 1920**, and at root **16 / 24 / 28 / 32** for the 320 case. 320px is WCAG 1.4.10's reflow width — this is the constraint both files currently fail by up to 243px.
- `html-mockup/SKILL.md` is at 579 of a 600 FAIL ceiling. Anything added there is paid for by trimming.
- Buttons centre their label on both axes (`display:inline-flex`), per the rule added in `a532df1`. Do not regress it.

## Why this exists

Measured on `a532df1`: `corporate-mockup.html` and `ecommerce-mockup.html` contain **zero** occurrences of `--type-ratio`, `--sp-scale` or `--elev-rest`. The two PROOF files carry the whole system; the two files a builder copies carry none of it. That is the same shape the axis work already removed twice — correct in the reference, stale in the file people start from — and it means the axis system currently stops before the first real project.

Both files also fail the repo's own reflow constraint: up to 159px overflow (corporate, 320px at a 32px root) and 243px (ecommerce, same), measured against the committed originals.

## File Structure

| File | Responsibility after this change |
|---|---|
| `skills/html-mockup/assets/corporate-mockup.html` | Declares `PERS-INSTITUTIONAL`'s axis values as its default; reflow-clean. |
| `skills/html-mockup/assets/ecommerce-mockup.html` | Declares `PERS-MATTER`'s axis values as its default; reflow-clean. |
| `skills/framework-audit/assets/framework-audit.php` | `RT_MOCKUP_NO_AXES`. |
| `tests/test-framework-audit.php` | Its fixtures. |
| `CONTRIBUTING.md` | The row-type row. |

---

### Task 1: The axes, and the reflow, in both production assets

**Files:**
- Modify: `skills/html-mockup/assets/corporate-mockup.html`
- Modify: `skills/html-mockup/assets/ecommerce-mockup.html`

**Interfaces:**
- Consumes: the axis token tables in `web-templates/references/design-system.md`; the `clamp()` formulas there verbatim; the blueprints in `ux-design-system/references/layout-patterns.md`.
- Produces: two `:root` blocks each declaring `--type-ratio`, `--display-lh`, `--fs-h1-max`, `--sp-scale`, `--c-bg`/`--c-bg-alt`/`--c-text`, `--elev-rest`, `--elev-hover`, and a `/* composition: LP-* */` comment. Task 2's check parses these.

- [ ] **Step 1: Pick and record the default anchor for each**

`corporate-mockup.html` → **`PERS-INSTITUTIONAL`**: scale `contained`, ground `cool`, density `standard`, composition `centered` (`LP-CENTERED`), elevation `soft-shadow`. It is the B2B/credibility asset and that anchor is the B2B one.

`ecommerce-mockup.html` → **`PERS-MATTER`**: scale `classic`, ground `warm`, density `standard`, composition `strict-grid` (`LP-STRICT-GRID`), elevation `hairline`. Commerce sells a made thing, and a product grid is what `strict-grid` is for.

These are DEFAULTS, not decisions about a client. Say so in the header comment where the existing "To re-brand for a client: swap the `:root` tokens" instruction already lives, and name which anchor each file starts from so the dialogue's output has an obvious landing place.

- [ ] **Step 2: Replace the hardcoded type scale with the axis chain**

Both files currently carry `--fs-h1: clamp(2rem,5vw,3.5rem)` and siblings — the 56px cap `design-system.md` names as the defect. Replace with the three `clamp()` formulas from `design-system.md` verbatim, driven by `--type-ratio` and `--fs-h1-max`. `h1, h2 { line-height: var(--display-lh) }`.

Do not re-derive the formulas. Copy them.

- [ ] **Step 3: Add `--sp-scale` and the elevation tokens**

Multiply the existing `--sp-*` scale by `--sp-scale`, and make section padding fluid per `design-system.md`. Replace every hardcoded `box-shadow` with `var(--elev-rest)` / `var(--elev-hover)`.

- [ ] **Step 4: Fix the reflow, measured not guessed**

Both files overflow at 320px once the root font size passes 16px. The proof files hit the same three causes and the fixes are known: a wrap guard on headings scoped to `@media (max-width:1023px)` (`overflow-wrap:anywhere` — `break-word` does NOT reduce intrinsic min-content size), `min-width:0` on any `aspect-ratio` box that is a flex or grid item, and `flex:0 1 auto; min-width:0` on the logo.

Measure `scrollWidth - clientWidth` on every page of both files at 320/430/768/1024/1280/1920, and at roots 16/24/28/32 for 320. Every cell must be 0. Put the table in the report.

- [ ] **Step 5: Verify nothing else regressed**

The button-centring rule from `a532df1` must still hold: measure each button's label ink box against its border box on both axes across both files; zero off by more than 1px. Confirm no glyph is occluded. Confirm both files still render every page they did before (6 pages corporate, 7 ecommerce).

- [ ] **Step 6: Commit**

```bash
git add skills/html-mockup/assets/corporate-mockup.html skills/html-mockup/assets/ecommerce-mockup.html
git commit -m "feat(html-mockup): the axes reach the files every project starts from"
```

---

### Task 2: The check that keeps them there

**Files:**
- Modify: `skills/framework-audit/assets/framework-audit.php`
- Modify: `CONTRIBUTING.md`
- Test: `tests/test-framework-audit.php`

- [ ] **Step 1: Write the failing fixtures**

One fixture with a mockup asset carrying every axis token (must not fire), one missing `--sp-scale` (must FAIL naming that token), one missing the composition comment (must FAIL naming it).

- [ ] **Step 2: Run, verify red** — `RT_MOCKUP_NO_AXES` is not declared yet, so `add()` exits 3.

- [ ] **Step 3: Declare and implement**

```php
	'RT_MOCKUP_NO_AXES'          => 'FAIL  — an html-mockup asset declares no perceptual-axis tokens',
```

For every `skills/html-mockup/assets/*.html`, require `--type-ratio`, `--display-lh`, `--fs-h1-max`, `--sp-scale`, `--elev-rest` and a `/* composition: LP-* */` comment, and FAIL naming each one missing. Files whose name starts with `_` are content, not mockups — skip them.

The point is not that a mockup is pretty; it is that a mockup which cannot express an axis silently reverts every project to one look, which is the defect this whole effort exists to remove.

- [ ] **Step 4: `CONTRIBUTING.md` row**

```markdown
| `RT_MOCKUP_NO_AXES` | FAIL | an `html-mockup` asset declares no perceptual-axis tokens |
```

- [ ] **Step 5: Gates** — audit `0 FAIL`, four suites `0 FAIL`.

- [ ] **Step 6: Mutate**

1. Drop one token from the required list — the fixture missing it must stop failing.
2. Make the check skip every file instead of only `_`-prefixed ones — the missing-token fixture must stop failing.
3. Drop the token name from the message — the assertion naming a specific token must break.

- [ ] **Step 7: Commit**

```bash
git add skills/framework-audit/assets/framework-audit.php CONTRIBUTING.md tests/test-framework-audit.php
git commit -m "feat(framework-audit): a mockup that cannot express an axis is a mockup that reverts every project"
```

---

## Done when

- Both production assets declare their anchor's axis values and derive type and space from them.
- `scrollWidth - clientWidth` is 0 on every page of both files at 320/430/768/1024/1280/1920, and at roots 16/24/28/32 at 320.
- Buttons still centre; no glyph occluded; every page still renders.
- `RT_MOCKUP_NO_AXES` exists with its entry, row and fixtures; `COVERAGE_EXEMPT` still `array()`.
- Audit `0 FAIL`; four suites `0 FAIL`.

## Out of scope, and named so it is not forgotten

**Phase C — the axes reaching the WordPress build.** Measured on `a532df1`: `es-builder.php` contains zero occurrences of `--type-ratio`, `--sp-scale`, `--elev-*` or `PERS-` (its only "axis" hit is the word inside a comment about `es_split()`). So the axis system still stops at the mockup: the native site built afterwards consumes none of it. That is a separate plan, and it is the last gap between "the idea works" and "a client receives a different site".
