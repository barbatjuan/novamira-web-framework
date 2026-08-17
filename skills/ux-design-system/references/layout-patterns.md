# Layout patterns + responsive

Section blueprints proven to read as premium. Builder-agnostic; the builder-core skill
implements them with native widgets/modules only.

## Page rhythm (alternate to avoid monotony)
Hero (dark photo + scrim) → advantages (light, feature cards) → services (grey tint grid) →
featured products (light) → process (dark) → testimonials (light) → banner CTA → final CTA (dark).
Alternate light / grey / dark backgrounds so adjacent sections never blur together.

## Sections
- **Hero**: full-bleed photo + gradient overlay, eyebrow + H1 + subcopy at ~58% width, two
  buttons (solid + ghost), a thin counter row. Mobile: center everything, counters centered.
- **Feature grid**: 3×2 premium feature cards (see `motion.md`). Distinct from image cards.
- **Services / catalog**: image cards in a grid, one card recipe, hover zoom.
- **Banner CTA**: rounded full-bleed photo, dark gradient, copy + button on the left.
- **Testimonials**: native carousel with infinite loop + autoplay + pause-on-hover (no custom JS).
- **Process**: numbered steps on a dark section.

## Header / navigation
- Sticky, glass (frost on `::before`, see `motion.md`). Logo left; nav + phone + cart + CTA right,
  one row on desktop.
- Desktop nav underline: accent line fading in on hover/active — scope to `@media(min-width:1025px)`
  so it never leaks into the mobile panel as odd bars.
- Mobile menu: modern, not a bare burger. A full-screen overlay on open reads best; scope the
  overlay CSS to the open state (`[aria-expanded="true"]`) so it isn't stuck open when closed.

## Composition blueprints

The composition axis resolves to exactly ONE of these four, and
`web-templates/references/design-system.md` maps each axis position to the blueprint id below.
They are the reason two anchors over identical content do not render the same page, so each one
fixes three things a sentence never could: how many columns, where the content sits inside them,
and what an image is allowed to do. Apply the chosen blueprint to EVERY section — mixing two is
how a page goes back to reading as a template. Everything else on this page (the section recipes
above, the header, motion) is shared and does not change with the position.

### `LP-CENTERED`
- Grid: 12 columns; content in columns 3–11, capped at `--content-width`, identical on every section.
- Hero: eyebrow, H1 and subcopy on one centred axis; both buttons centred beneath, side by side.
- Section headings: centred, eyebrow centred above them.
- Images: always inside the container, equal margins left and right. Nothing bleeds, ever.
- Grids: symmetric only — 2, 3 or 4 equal columns; an odd last row centres its remainder.

### `LP-ASYMMETRIC`
- Grid: 12 columns declared with named lines so an edge is a line, not a margin:
  `[full-start] minmax(pad,1fr) [wide-start] repeat(12,[c] minmax(0,var(--col))) [wide-end c] minmax(pad,1fr) [full-end]`.
- **`--content-width` must be a PROPORTION of the viewport for this grid to hold, and getting
  that backwards has shipped twice.** The tracks have to sum to the viewport, so capping the 12
  columns leaves the `1fr` gutter as the only track that can absorb a wider screen — and nothing
  bounds a `1fr`. A fixed `1140px` band measured 150 / 390 / **710px** of gutter at 1440 / 1920 /
  2560; the fluid-but-capped band that replaced it still grew, 15.3% → 25.0% → 37.5% of total
  margin at 1440 / 2000 / 2560, because a cap only changes how fast the margin runs away, never
  that it does. `clamp(1140px, 85vw, 100vw)` holds it flat at 15% at every width above 1341px.
  `design-system.md` § Contenedores carries the value, its derivation and what it costs.
- Hero: copy left at ~58% width, ONE image bleeding the right viewport edge —
  `grid-column: c 8 / full-end`. **Not** `margin-right: calc(50% - 50vw)`: percentage margins on a
  grid item resolve against that item's own grid area, not the container, so the bleed overshoots
  (measured 312px past a 1265px viewport) and `overflow-x:clip` then hides the damage.
- Section headings: left-aligned, never centred; the eyebrow sits above and left.
- Images: exactly one bleed per section, always on the same edge down the whole page.
- Grids: two columns at 7/5 or 5/7, alternating direction section to section. Never 50/50.
- **ONLY MEDIA MAY REACH `full-start` / `full-end`.** A photograph touching the screen edge is a
  bleed. A paragraph touching it is an amputation. A CARD touching it is sliced — a card is a
  bordered surface carrying a heading and a paragraph, and the reader parses it as one object, so
  bleeding its image while insetting its copy does not make it a bleed, it makes it a card with a
  printing error (measured: frame right 2000.0, body ink 1968.0, and the reader called it cut off
  across two rounds of fixes). A FORM CONTROL touching it is a broken page: no hit-slop on one
  side, the border that says where the control ends coincident with the edge of the screen, and a
  name field 1453.3px wide at 2560. Card rows, copy blocks and forms end at the band — `c 13`,
  which is the same line as `wide-end`. `framework-audit.php`'s `RT_MOCKUP_BLEED_NOT_MEDIA` decides
  this from the selector's subject; `documentElement.scrollWidth === clientWidth` throughout every
  case above, so no overflow gate can see any of it.
  A rail that ends at `full-end` keeps the bleed on its images and steps its last card's TEXT back
  by the page padding. Measured on the gallery at 2560: the last case study's title sat at right
  `2560.0` on a 2560 viewport with `scrollWidth === clientWidth`, so nothing overflowed, no
  overflow gate could see it, and the reader read it as cut off — which it was, of paper.

### `LP-STRICT-GRID`
- Grid: 12 columns, one gutter (`--sp-m`), zero bleeds — every element starts and ends on a column line.
- Hero: copy in columns 1–6, image in columns 7–12, both inside the container, no overlap.
- Section headings: left-aligned on the first column line, sharing the grid with the content below.
- Images: one fixed aspect ratio per section (all 4:3 or all 1:1) so rows line up across the grid.
- Grids: 3 or 4 equal columns, equal gutters, equal card heights. Rows must visibly align.

### `LP-BROKEN-GRID`
- Grid: the same named-line 12 columns as `LP-ASYMMETRIC` — including its fluid `--content-width`,
  for the same reason — kept as a reference the page deliberately violates. Crossing the container
  is `grid-column: c 1 / full-end`; bleeding two edges is `full-start / full-end`. Naming a line is
  what makes the violation safe.
- Hero: oversized H1 crossing the container's right edge; the image sits BEHIND it, offset. **The
  copy is vertically centred in the hero row** (`justify-content:center` on the column-flex head),
  because the bleeding image sizes that row and the copy does not: measured, the row grew 484.9 →
  534.6 → 714.6px across 1440 / 1920 / 2560 while the copy ink stayed 399.5px, and everything
  under the CTAs was void. Do not give that frame `height:100%` and think it fills the row — the
  row is auto-sized, a percentage height has no definite basis, and it silently computes to `auto`.
- Every section: at least one element crossing a column line or overlapping its neighbour by ~`--sp-m`.
- Images: at least one per page bleeding two edges; overlaps stack with `z-index` in a shared grid
  row, never with negative margins that collapse on small screens.
- Grids: deliberately uneven columns (7/5, 4/8), with one card offset vertically by `--sp-l`.
- Mobile: every overlap collapses to a single stacked column — a broken grid at 430px is just broken.

## Responsive rules
- Header as a column on mobile: top row (logo · menu · cart, aligned) + a full-width CTA row below.
- Two-column product grids need equal-height cards (see `motion.md`).
- Never let a nested container force `content_width:full`/100% and push siblings to a new row.
- Test at 320, ~430 (phone), 768 (tablet), 1024, 1280 (desktop), 1440, 1920 **and 2560**. Verify
  the header stays one row on desktop. **2560 is not optional and it is not a luxury**: stopping at
  1280 is why a fixed `--content-width` beside a viewport-edge bleed shipped, and stopping at 1920
  is why it shipped twice. Every defect in this file's measured notes is invisible at 1280 and
  obvious at 2560. 1024 belongs on the list too — it is the first width where the desktop grid is
  on while the viewport is still narrower than the band, which is where an uneven `fr` rail falls
  under the width of the word it has to carry (fix: `minmax(min-content, <n>fr)`, not a bare `fr`).
