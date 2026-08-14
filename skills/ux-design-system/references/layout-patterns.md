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
- Grid: 12 columns; text blocks occupy columns 1–7 (~58%), the remaining 5 are air or image.
- Hero: copy left at ~58% width, ONE image bleeding the right viewport edge
  (`margin-right: calc(50% - 50vw)`).
- Section headings: left-aligned, never centred; the eyebrow sits above and left.
- Images: exactly one bleed per section, always on the same edge down the whole page.
- Grids: two columns at 7/5 or 5/7, alternating direction section to section. Never 50/50.

### `LP-STRICT-GRID`
- Grid: 12 columns, one gutter (`--sp-m`), zero bleeds — every element starts and ends on a column line.
- Hero: copy in columns 1–6, image in columns 7–12, both inside the container, no overlap.
- Section headings: left-aligned on the first column line, sharing the grid with the content below.
- Images: one fixed aspect ratio per section (all 4:3 or all 1:1) so rows line up across the grid.
- Grids: 3 or 4 equal columns, equal gutters, equal card heights. Rows must visibly align.

### `LP-BROKEN-GRID`
- Grid: 12 columns kept as a reference the page deliberately violates.
- Hero: oversized H1 crossing the container's right edge; the image sits BEHIND it, offset.
- Every section: at least one element crossing a column line or overlapping its neighbour by ~`--sp-m`.
- Images: at least one per page bleeding two edges; overlaps stack with `z-index`, never with
  negative margins that collapse on small screens.
- Grids: deliberately uneven columns (7/5, 4/8), with one card offset vertically by `--sp-l`.
- Mobile: every overlap collapses to a single stacked column — a broken grid at 430px is just broken.

## Responsive rules
- Header as a column on mobile: top row (logo · menu · cart, aligned) + a full-width CTA row below.
- Two-column product grids need equal-height cards (see `motion.md`).
- Never let a nested container force `content_width:full`/100% and push siblings to a new row.
- Test at ~430 (phone), 768 (tablet), 1280 (desktop). Verify the header stays one row on desktop.
