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
`elementor-core` / `divi-core` translate these decisions into builder data.

## Activation Contract
Run after `web-templates`, before `html-mockup`. Use for layout, spacing, color, hover/motion,
card style, or responsive behavior — Elementor and Divi alike. Never hand straight to
builder-core: the mockup approval gate sits between this skill and any WordPress write.

## The Design Space
- **Five axes** (`references/design-tokens.md`): scale, ground, density, composition,
  elevation — a spectrum of values, never an adjective.
- **Four anchors** (`references/design-personalities.md`): `PERS-EDITORIAL`, `PERS-MATTER`,
  `PERS-DIRECT`, `PERS-INSTITUTIONAL` — positions on all five axes at once. Land on one, or
  between two.

## Hard Rules
- Act like a senior designer, not a filler: every typography/color/motion choice traces to a
  brand signal, client reference, or resolved axis — never "the docs example." Unjustified,
  ask one more question instead of guessing.
- One accent color — ONLY CTAs, action icons, important links; neutrals carry the rest.
- Motion is calm: hovers use `cubic-bezier(.22,1,.36,1)`, ~.35–.7s, small moves
  (`translateY(-4…-6px)`, `scale(1.045)`), soft shadow, never a hard snap — the anchor tunes
  duration/distance within this range (`references/motion.md`).
- Consistent spacing rhythm and padding across the site; audit margins as a pass.
- Two button families only: solid accent + ghost/outline, each with a legible hover (no
  white-on-white ghost).
- Cards share ONE language site-wide — one card recipe per anchor, never reinvented per section.
- Mobile-first: centered hero on small screens, real breakpoints, full-width CTAs, equal-height product cards.

## Execution Steps
1. Resolve the FIVE AXES with 3–5 questions in business terms, never "which personality do you
   want". Precharge each from the industry `web-templates` already reported; the client confirms
   or overrides. One answer usually moves several axes — a stone fabricator asked "material
   catalogue or gallery of finished work?" moves ground, composition and density at once. **Every
   axis must end resolved**: ask explicitly for any the answers did not reach, because an axis
   nobody sets falls to the same value on every project, which is how sites end up identical.
   Land on an anchor from `references/design-personalities.md`, or between two — both are valid.
2. Read `references/design-tokens.md`: fix palette, type pair, spacing scale, radii from the
   resolved axes.
3. Read `references/motion.md` for hover timings and the premium card recipe, tuned by the
   resolved elevation and density.
4. Read `references/layout-patterns.md` for hero, feature grid, banner CTA, testimonial carousel,
   glass header, mega/mobile menu, and responsive rules.
5. Hand the resolved axes + tokens + pattern list to **`html-mockup`** for client approval;
   builder-core receives it only after the mockup is approved.

## Output Contract
Return a short spec: the resolved axis positions, palette + roles, type pair, spacing/radii,
motion timings, sections/patterns to build, per-breakpoint notes — no builder-specific code.

## References
- `references/design-personalities.md` — four anchors, positions on all five axes.
- `references/design-tokens.md` — palette roles, type, spacing, radii, five axes.
- `references/motion.md` — hover timings, premium card recipe, glass, button system.
- `references/layout-patterns.md` — section blueprints + responsive rules.
