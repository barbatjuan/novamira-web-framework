---
name: ux-design-system
description: "Trigger: premium web design, hero, layout, cards, hover effects, responsive, microinteractions, design tokens, spacing, palette. Builder-agnostic visual language for premium WordPress sites (Elementor or Divi)."
license: Apache-2.0
metadata:
  author: "juan"
  version: "1.0"
---

# UX Design System

The visual language, independent of the page builder. Decide HOW it should look and
feel; `elementor-core` / `divi-core` translate these decisions into builder data.

## Activation Contract
Use when deciding layout, spacing, color, hover/motion, card style, or responsive
behavior — before the builder-core skill executes. Applies to Elementor and Divi alike.

## Hard Rules
- One accent color, used ONLY for CTAs / action icons / important links. Neutrals carry the rest.
- Motion is calm: hovers use `cubic-bezier(.22,1,.36,1)`, ~.35–.7s, small moves
  (`translateY(-4…-6px)`, `scale(1.045)`), soft shadow — never a hard snap. See `references/motion.md`.
- Consistent spacing rhythm and section padding across the whole site; audit margins as a pass.
- Two button families only: solid accent + ghost/outline. Both need a legible hover in
  BOTH states (a ghost that turns white-on-white on hover is the classic bug).
- Cards share ONE language site-wide (reuse a single card recipe, don't reinvent per section).
- Mobile-first: centered hero on small screens, real breakpoints, full-width CTAs, equal-height product cards.

## Execution Steps
1. Read `references/design-tokens.md` and fix the palette, type pair, spacing scale, radii.
2. Read `references/motion.md` for the hover/microinteraction timings and the premium card recipe.
3. Read `references/layout-patterns.md` for hero, feature grid, banner CTA, testimonial carousel,
   glass header, mega/mobile menu, and responsive rules.
4. Hand the chosen tokens + pattern list to the builder-core skill as the spec to implement.

## Output Contract
Return a short spec: palette + roles, type pair, spacing/radii, motion timings, the list of
sections/patterns to build, and per-breakpoint notes. No builder-specific code here.

## References
- `references/design-tokens.md` — palette roles, type, spacing, radii.
- `references/motion.md` — hover timings, premium card recipe, glass, button system.
- `references/layout-patterns.md` — section blueprints + responsive rules.
