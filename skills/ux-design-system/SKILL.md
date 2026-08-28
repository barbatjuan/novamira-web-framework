---
name: ux-design-system
description: "Trigger: premium web design, hero, layout, cards, hover effects, responsive, microinteractions, design tokens, spacing, palette. Builder-agnostic visual language for premium WordPress sites (Elementor or Divi)."
license: Apache-2.0
metadata:
  author: "juan"
  version: "1.2"
---

# UX Design System

The visual language, independent of the page builder: HOW it looks and feels.
`elementor-core` / `divi-core` translate these into builder data.

## Activation Contract
Run after `web-templates`, before `html-mockup`. Never hand straight to builder-core:
the mockup approval gate sits between this skill and any WordPress write.

## The Design Space
- **Eight axes** (`references/design-tokens.md`): scale, ground, density, composition,
  elevation, accent, chassis, ornament — a spectrum of values, never an adjective.
- **Eight styles** (`references/style-catalog/`): full positions on all eight axes plus a
  toggle precharge. Land on the closest.

## Hard Rules
- Every typography/color/motion choice traces to a brand signal, client reference, or resolved
  axis — never "the docs example." Unjustified, ask one more question.
- One accent color — ONLY CTAs, action icons, important links; neutrals carry the rest.
- Motion is calm: hovers use `cubic-bezier(.22,1,.36,1)`, ~.35–.7s, small moves
  (`translateY(-4…-6px)`, `scale(1.045)`), soft shadow, never a hard snap; the anchor tunes
  duration/distance within this range.
- Consistent spacing rhythm and padding across the site; audit margins as a pass.
- Two button families only: solid accent + ghost/outline, each with a legible hover (no
  white-on-white ghost). Buttons centre their label on BOTH axes — `inline-flex` +
  `align-items:center` + `justify-content:center`; `text-align:center` alone pins it to the
  top of any stretched button.
- Cards share ONE language site-wide — one card recipe per anchor, never reinvented per section.
- Mobile-first: centered hero on small screens, real breakpoints, full-width CTAs, equal-height product cards.

## Execution Steps
1. Resolve the EIGHT AXES with 3–5 questions in business terms, never "which style do you
   want". Precharge each from the industry `web-templates` reported, or from the spec of a strip
   picked in `html-mockup/assets/gallery/index.html`, which precharges archetype and all eight;
   the client confirms or overrides. One answer usually moves several axes: "material catalogue
   or gallery of finished work?" moves ground, composition and density at once. **Every axis must
   end resolved**: ask explicitly for any the answers did not reach — an axis nobody sets, or one
   inherited from a card and never questioned, falls to the same value on every project, which is
   how sites end up identical.
2. Ask `TGL-IMAGERY` and `TGL-MOTION-INTENSITY` (`web-templates/references/toggles.md`),
   precharged from the anchor's Imagery/Motion intensity; the client confirms or overrides.
3. Read `references/design-tokens.md`: fix palette, type pair, spacing scale, radii from the
   resolved axes.
4. Read `references/motion.md`: hover timings and premium card recipe, tuned by elevation and
   density.
5. Read `references/layout-patterns.md` for the section blueprints and responsive rules.
6. Hand the resolved axes + tokens + pattern list to **`html-mockup`** for approval — builder-core
   receives it only afterward.

## Output Contract
Return a short spec: resolved axis positions, palette + roles, type pair, spacing/radii, motion
timings, sections/patterns, per-breakpoint notes — no builder-specific code.

## References
- `references/style-catalog/` — eight styles, positions on all eight axes.
- `references/design-tokens.md` — palette roles, type, spacing, radii, eight axes.
- `references/motion.md` — hover timings, premium card recipe, glass, button system.
- `references/layout-patterns.md` — section blueprints + responsive rules.
