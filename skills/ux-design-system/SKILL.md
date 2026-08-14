---
name: ux-design-system
description: "Trigger: premium web design, hero, layout, cards, hover effects, responsive, microinteractions, design tokens, spacing, palette. Builder-agnostic visual language for premium WordPress sites (Elementor or Divi)."
license: Apache-2.0
metadata:
  author: "juan"
  version: "1.2"
---

# UX Design System

The visual language, independent of the page builder. Decide HOW it should look and
feel; `elementor-core` / `divi-core` translate these decisions into builder data.

## Activation Contract
Run after `web-templates` (architecture resolved), before `html-mockup`. Use when deciding
layout, spacing, color, hover/motion, card style, or responsive behavior. Applies to
Elementor and Divi alike. Never hand off straight to builder-core: the mockup approval gate
sits between this skill and any WordPress write.

## The 3 layers
- **CAPA 1 — Personalities** (`references/design-personalities.md`): 8 curated visual
  languages (typography, color mood, radius/shadow, motion, imagery, card recipe). Orthogonal
  to the architecture archetype `web-templates` already resolved — same archetype, different
  personality, different studio-feel result.
- **CAPA 2 — Recommender**: reuse the brand signals + references `web-templates` already
  collected. Never re-ask for references. Map industry/tone/audience to one `PERS-*`, present
  the pick with a one-line rationale, confirm with the client before continuing.
- **CAPA 3 — Toggles** (`web-templates/references/toggles.md`): `TGL-IMAGERY`,
  `TGL-MOTION-INTENSITY`, plus the reused `TGL-CARD-STYLE` / `TGL-CARD-IMG`, fine-tune within
  the chosen personality's defaults.

## Hard Rules
- Act like a senior visual designer, not a filler: every typography/color/motion choice must
  trace to a brand signal or a client reference. "The example in the docs" is never a
  justification — if CAPA 2 can't justify a `PERS-*` pick against real signals, ask one more
  question instead of guessing.
- One accent color, used ONLY for CTAs / action icons / important links. Neutrals carry the rest.
- Motion is calm: hovers use `cubic-bezier(.22,1,.36,1)`, ~.35–.7s, small moves
  (`translateY(-4…-6px)`, `scale(1.045)`), soft shadow — never a hard snap. The chosen
  personality tunes duration/distance within this range; see `references/motion.md`.
- Consistent spacing rhythm and section padding across the whole site; audit margins as a pass.
- Two button families only: solid accent + ghost/outline. Both need a legible hover in
  BOTH states (a ghost that turns white-on-white on hover is the classic bug).
- Cards share ONE language site-wide (reuse a single card recipe per personality, don't
  reinvent per section).
- Mobile-first: centered hero on small screens, real breakpoints, full-width CTAs, equal-height product cards.

## Execution Steps
1. Run CAPA 2: reuse `web-templates`'s brand signals + 2-4 references, recommend one `PERS-*`
   from `references/design-personalities.md` with rationale, confirm with the client.
2. Read `references/design-tokens.md` and fix the palette, type pair, spacing scale, radii —
   using the confirmed personality's values, never a docs example.
3. Read `references/motion.md` for the hover/microinteraction timings and the premium card
   recipe, tuned by the personality's motion intensity.
4. Read `references/layout-patterns.md` for hero, feature grid, banner CTA, testimonial carousel,
   glass header, mega/mobile menu, and responsive rules.
5. Run CAPA 3: ask `TGL-IMAGERY` and `TGL-MOTION-INTENSITY` from `web-templates/references/toggles.md`,
   precharged with the confirmed personality's Imagery and Motion intensity values as defaults —
   the client confirms or overrides.
6. Hand the chosen personality + tokens + pattern list to **`html-mockup`** as the spec to render
   for client approval. builder-core only receives it after the mockup is approved.

## Output Contract
Return a short spec addressed to `html-mockup`: the `PERS-*` chosen, palette + roles, type
pair, spacing/radii, motion timings, the list of sections/patterns to build, and per-breakpoint
notes. No builder-specific code here.

## References
- `references/design-personalities.md` — CAPA 1: the 8 personality catalog.
- `references/design-tokens.md` — palette roles, type, spacing, radii.
- `references/motion.md` — hover timings, premium card recipe, glass, button system.
- `references/layout-patterns.md` — section blueprints + responsive rules.
