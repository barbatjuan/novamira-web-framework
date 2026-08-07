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

## Responsive rules
- Header as a column on mobile: top row (logo · menu · cart, aligned) + a full-width CTA row below.
- Two-column product grids need equal-height cards (see `motion.md`).
- Never let a nested container force `content_width:full`/100% and push siblings to a new row.
- Test at ~430 (phone), 768 (tablet), 1280 (desktop). Verify the header stays one row on desktop.
