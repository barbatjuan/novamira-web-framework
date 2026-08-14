# Perceptual Axes — Phase B Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Prove the axis system produces visibly different sites, by rendering ONE content set under TWO anchors and making "they differ" a check rather than a claim.

**Architecture:** One shared content file holds every string. Two mockup assets consume it, each pinning a different anchor's axis token values from `web-templates/references/design-system.md` and implementing its `LP-*` blueprint from `ux-design-system/references/layout-patterns.md`. A new audit check parses both `:root` blocks and FAILs unless they differ on at least four of the five axes.

**Tech Stack:** Static HTML + CSS (no build, no JS beyond page switching), PHP 8 for the check, the repo's four PHP suites.

## Global Constraints

- Spec: `docs/superpowers/specs/2026-08-14-perceptual-axes-design.md`, item 6. Phase A (items 1–5) is merged at `0f5591b`.
- **Never weaken a check to make a row go green** (CONTRIBUTING.md).
- New `RT_*` needs a `ROW_TYPES` entry, a `CONTRIBUTING.md` table row, and a fixture. `COVERAGE_EXEMPT` stays `array()`.
- Token VALUES are copied from `web-templates/references/design-system.md` exactly. Do not invent a number; if a value is missing there, that is a finding to report, not a gap to fill.
- Mockups are **self-contained**: inline `<style>`, no external CSS/JS/font/image requests (the Artifact CSP forbids them). Placeholder blocks only.
- Theme-aware is NOT required for these two: each pins one ground on purpose. That is the point of the ground axis.
- `skills/html-mockup/SKILL.md` is at **564 of a 600-word FAIL ceiling** — 36 words of headroom. Any pointer added there must be paid for by trimming.
- Every file under `assets/` must be reachable from its `SKILL.md` or it is `RT_ORPHAN_FILE`.
- **Real typefaces are named, not embedded.** Downloading binary font files is out of scope for this framework; the `font-family` stacks name the anchor's real families first with an honest fallback, and the embedding procedure is documented as a human step. Do not fake it with a `@font-face` pointing at a URL — the CSP blocks it and a broken request is worse than an honest fallback.

## File Structure

| File | Responsibility |
|---|---|
| `skills/html-mockup/assets/_axis-proof-content.md` | Every string both mockups render. The single source of copy, so no visual difference can come from the words. |
| `skills/html-mockup/assets/proof-editorial-mockup.html` | `PERS-EDITORIAL`: scale `editorial`, ground `paper`, density `generous`, composition `asymmetric` (`LP-ASYMMETRIC`), elevation `none`. |
| `skills/html-mockup/assets/proof-direct-mockup.html` | `PERS-DIRECT`: scale `monumental`, ground `ink`, density `compact`, composition `broken-grid` (`LP-BROKEN-GRID`), elevation `accent-glow`. |
| `skills/framework-audit/assets/framework-audit.php` | New `RT_PROOF_NOT_DISTINCT`. |
| `tests/test-framework-audit.php` | Its fixtures. |
| `skills/html-mockup/SKILL.md` | Points at the two proof assets and the content file. |
| `CONTRIBUTING.md` | The new row-type row. |

---

### Task 1: The shared content file and the two mockups

**Files:**
- Create: `skills/html-mockup/assets/_axis-proof-content.md`
- Create: `skills/html-mockup/assets/proof-editorial-mockup.html`
- Create: `skills/html-mockup/assets/proof-direct-mockup.html`
- Modify: `skills/html-mockup/SKILL.md`

**Interfaces:**
- Consumes: the axis token tables in `web-templates/references/design-system.md` ("Perceptual axes — token values") and the blueprints in `ux-design-system/references/layout-patterns.md`.
- Produces: two `:root` blocks each declaring `--type-ratio`, `--display-lh`, `--fs-h1-max`, `--sp-scale`, `--c-bg`, `--c-bg-alt`, `--c-text`, `--elev-rest`, `--elev-hover`, and a `/* composition: LP-* */` comment. Task 2's check parses exactly these.

- [ ] **Step 1: Write the content file**

`_axis-proof-content.md` holds the copy for a fictional client — a stone fabricator, chosen because it is the real client profile this framework serves and because it is the opposite of the SaaS aesthetic the references warned about. Sections, in order: hero (eyebrow, H1, subcopy, two CTA labels), three value items (title + line each), a four-item work grid (title + material each), a process of three numbered steps, one testimonial, closing CTA band, footer.

Every string appears ONCE here. Both mockups render these exact strings. This is what makes the comparison honest: any visible difference is the axes, never the words.

- [ ] **Step 2: Build `proof-editorial-mockup.html`**

`:root` copies these EXACT values from `design-system.md`:
```css
--type-ratio: 1.500;  --display-lh: 0.95;  --fs-h1-max: 88px;   /* scale: editorial */
--sp-scale: 1.35;                                               /* density: generous */
--c-bg: #FFFFFF; --c-bg-alt: #F6F7F8; --c-text: #15181A;        /* ground: paper */
--elev-rest: none; --elev-hover: none;                          /* elevation: none */
/* composition: LP-ASYMMETRIC */
```
Heading sizes use the three `clamp()` formulas from `design-system.md` verbatim — do not hardcode a px size. `h1, h2 { line-height: var(--display-lh) }`. Section padding is
`clamp(calc(2rem * var(--sp-scale)), 6vw, calc(7rem * var(--sp-scale)))`.

Layout follows `LP-ASYMMETRIC` exactly: 12-column grid, text on columns 1–7, hero copy at ~58% with ONE image bleeding the right viewport edge via `margin-right: calc(50% - 50vw)`, section headings left-aligned, exactly one bleed per section always on the same edge, two-column grids at 7/5 or 5/7 alternating, never 50/50.

`font-family` names the anchor's real families first: `'Fraunces', Georgia, 'Times New Roman', serif` for headings and `'Inter Tight', system-ui, sans-serif` for body.

- [ ] **Step 3: Build `proof-direct-mockup.html`**

Same content file's strings, same section order. `:root`:
```css
--type-ratio: 1.618;  --display-lh: 0.82;  --fs-h1-max: 120px;  /* scale: monumental */
--sp-scale: 0.8;                                                /* density: compact */
--c-bg: #0E1113; --c-bg-alt: #171B1E; --c-text: #F4F6F7;        /* ground: ink */
--elev-rest: 0 0 0 1px color-mix(in srgb,var(--c-accent) 22%,transparent);
--elev-hover: 0 14px 34px -10px color-mix(in srgb,var(--c-accent) 40%,transparent);
/* composition: LP-BROKEN-GRID */
```
The accent MUST be re-derived to clear 4.5:1 against `#0E1113` — `design-system.md` says an accent that passed on `paper` usually fails on `ink`. State the measured ratio in a comment.

Layout follows `LP-BROKEN-GRID`: oversized H1 crossing the container's right edge with the image behind it and offset, at least one element per section crossing a column line or overlapping its neighbour by `--sp-m`, at least one image bleeding two edges, overlaps via `z-index` and never via negative margins that collapse on small screens, uneven columns (7/5, 4/8) with one card offset vertically by `--sp-l`.

`font-family`: `'Archivo Expanded', 'Arial Black', system-ui, sans-serif` for headings, `'Archivo', system-ui, sans-serif` for body.

- [ ] **Step 4: Point `SKILL.md` at all three files, within budget**

`html-mockup/SKILL.md` is at 564 of 600. Add the three pointers to `## References` and pay for them by trimming prose elsewhere in that file. Measure before and after:
```bash
php skills/framework-audit/assets/framework-audit.php . --word-report 2>/dev/null | grep html-mockup
```
Must end under 600; under 500 clears the WARN.

- [ ] **Step 5: Verify both render and the gates pass**

Open each file in the browser pane and confirm: no horizontal scroll at 1280 / 768 / 430, the H1 is the size the token says (88px cap vs 120px cap), and the two pages are unmistakably different at a glance. Then:
```bash
php skills/framework-audit/assets/framework-audit.php . 2>&1 | tail -3
for t in tests/*.php; do printf "%-34s " "$t"; php "$t" 2>&1 | tail -1; done
```

- [ ] **Step 6: Commit**

```bash
git add skills/html-mockup/assets/_axis-proof-content.md skills/html-mockup/assets/proof-editorial-mockup.html skills/html-mockup/assets/proof-direct-mockup.html skills/html-mockup/SKILL.md
git commit -m "feat(html-mockup): one content set, two anchors, visibly different"
```

---

### Task 2: Make the proof a gate

**Files:**
- Modify: `skills/framework-audit/assets/framework-audit.php`
- Modify: `CONTRIBUTING.md`
- Test: `tests/test-framework-audit.php`

**Interfaces:**
- Consumes: the two `:root` blocks Task 1 produced.
- Produces: `RT_PROOF_NOT_DISTINCT`.

- [ ] **Step 1: Write the failing fixture**

A fixture with two proof mockups whose `:root` blocks differ on only THREE axes must FAIL; one differing on four or five must not. Build them with a helper that emits a minimal HTML file carrying just a `:root` block, so the fixture is about the axis values and nothing else.

- [ ] **Step 2: Run it, verify it fails**

Expected: `exit(3)` or FAIL — the row type is not declared yet.

- [ ] **Step 3: Declare and implement**

```php
	'RT_PROOF_NOT_DISTINCT'      => 'FAIL  — the two proof mockups do not differ on enough axes',
```

Parse each proof mockup's `:root` for the five axis signatures — scale (`--type-ratio`), ground (`--c-bg`), density (`--sp-scale`), elevation (`--elev-rest`), composition (the `/* composition: LP-* */` comment) — and FAIL when fewer than four differ. Report WHICH axes match, because "not different enough" is not actionable and "both use `--sp-scale: 1.0`" is.

A missing proof file is also a FAIL: the proof cannot be optional, or it becomes a claim again.

- [ ] **Step 4: Document in CONTRIBUTING.md**

```markdown
| `RT_PROOF_NOT_DISTINCT` | FAIL | the two proof mockups do not differ on enough axes |
```

- [ ] **Step 5: Run the gates**

Audit `0 FAIL`, all four suites `0 FAIL`.

- [ ] **Step 6: Mutate**

1. Lower the threshold from four to one: the three-axis fixture must stop failing → its assertion must break.
2. Delete the missing-file FAIL: a fixture with one proof file absent must stop failing.
3. Drop the "which axes match" detail from the message: the assertion naming a specific axis must break.

- [ ] **Step 7: Commit**

```bash
git add skills/framework-audit/assets/framework-audit.php CONTRIBUTING.md tests/test-framework-audit.php
git commit -m "feat(framework-audit): the proof that two anchors differ is a gate, not a claim"
```

---

## Done when

- Both proof mockups exist, render without horizontal scroll at 430 / 768 / 1280, and render the same strings from `_axis-proof-content.md`.
- `RT_PROOF_NOT_DISTINCT` exists with its `ROW_TYPES` entry, `CONTRIBUTING.md` row and fixtures; `COVERAGE_EXEMPT` is still `array()`.
- Audit `0 FAIL`; all four suites `0 FAIL`; `html-mockup/SKILL.md` under 600 words.
- The falsifiable criterion is met by inspection: same content, two anchors, unmistakably different.

## Known limitation, stated rather than hidden

The real typefaces are **named, not embedded**. This framework does not download binary font files, so the mockups declare `'Fraunces'` / `'Archivo Expanded'` with honest fallbacks and the subsetting-and-embedding procedure is documented as a human step. There is an upside worth keeping: if the two pages are unmistakably different while BOTH fall back to system fonts, the difference is coming from scale, ground, density, composition and elevation — which is a stronger proof of the axis system than one where the typefaces did the work.
