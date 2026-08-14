# Design tokens

`web-templates/references/design-system.md` is the **single authority** for token NAMES and
VALUES. This file covers only the ROLES: what each token is FOR, how to derive a value for a
brand, and when to use it. Never restate a number here — read it there. The orchestrator gets
the brand input from `project-context` (logo, palette) or asks the user.

Swap the values per brand; keep the roles.

## Palette roles

| Job | Tokens | What it is FOR |
|-----|--------|----------------|
| Dominant | `--c-bg`, `--c-bg-alt` | white / near-white background (or near-black in a dark brand). Carries most of the page; nothing competes with it. |
| Contrast | `--c-text`, `--c-primary` | near-black type and inverted dark surfaces (footer, announcement bar, solid CTA). Structure and readability. |
| Accent | `--c-accent` | ONE color. CTAs, action icons, important links, active states. Never body text, never decoration. |
| Neutrals | `--c-text-muted`, `--c-border` | meta text, dividers, tinted sections. Derived from the contrast color, desaturated. |
| States | `--c-success`, `--c-error`, `--c-sale` | stock/confirmation, errors, discount badges and offer prices. Semantic only — never reused as decoration. |

`--c-secondary` is the second action level (outline / ghost buttons). It may equal the contrast
color; it must never equal the accent, or the one-accent rule collapses.

### Deriving a palette from a logo
1. Take the logo's most saturated color → candidate **accent**. If the logo has two, the second
   becomes a hover/active shade, not a second accent.
2. **Dominant** = the lightest neutral in the brand material, or plain white.
3. **Contrast** = the darkest brand neutral pushed to near-black. Check 4.5:1 against the dominant.
4. Derive the neutrals from the contrast, not from grey: muted text ≈ 55–60% of the way toward
   the dominant, border ≈ 85%. Sampling off the contrast keeps the whole page coherent.
5. Accent hover = the accent darkened ~10%. Verify the CTA label still passes contrast on it.

Reject an accent that fails 4.5:1 with both white and near-black label text — pick a darker
variant instead of adding a second color.

## Typography roles
- `--font-primary` — headings + UI. Heavy weight and tight line-height read premium, but the
  family itself comes from the chosen personality (`design-personalities.md`) — never copy an
  illustrative example verbatim as a default.
- `--font-secondary` — body. Normal weight, open line-height. May equal `--font-primary`. Never
  a third family. See `design-personalities.md` for the 8 concrete pairings.
- Hierarchy per section: eyebrow (`--fs-eyebrow`, uppercase, letter-spaced) → heading → paragraph.
  One `--fs-h1` per page.
- The scale is fluid (`clamp()`), so sizes come from the token, never from a per-section override.

## Spacing & radii roles
- Section rhythm: the same padding tokens on every section, stepping up mobile → tablet → desktop.
  A bespoke margin anywhere breaks the rhythm.
- Grid gaps use one step of the `--sp-*` scale; tight mobile gaps use the step below. Nothing
  outside the scale.
- Radii carry meaning: containers are the softest, then cards; buttons, inputs and images share
  the smallest step. Separate tokens are what let a personality go sharp (Minimal Swiss, Tech
  Precision) or soft (Warm Boutique) without touching a single module.
- Audit margins as a dedicated pass — inconsistent spacing is the #1 tell of a cheap template.

## Imagery
- Real, high-quality photography (stock via a licensed source). Cover-fit with subtle dark
  gradient scrims over hero/CTA photos so white text stays legible.
