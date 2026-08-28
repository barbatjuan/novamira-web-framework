# Colour And Tone System Specification

## Purpose

Every photograph shares one tonal envelope because `ink_tint()`
(`_build-gallery.php:829-850`) re-pins the derived shadow ink to the
neutral endpoint's own luminance using one shared `$INK_TINT = 0.45`
(`:784`), and three of four grounds share one accent `#8C3A1F`
(`:642-647`). This spec replaces the 4-row ground table with 9 families,
gives each its own accent, makes `$INK_TINT` and the ink position
per-style (including a `none` position), and derives `soft-shadow` from
`--c-text` instead of a fixed literal.

## Requirements

### Requirement: Nine Ground Families, Each Clearing The AAA Floor

The ground axis MUST offer 9 named positions. Each MUST clear
`contrast(text, bg) ≥ 7.0` and `contrast(text, bg-alt) ≥ 7.0` — the same
gate `_build-gallery.php:654-669` already enforces per brand, extended to
run over all 9 families.

#### Scenario: A family clears AAA
- GIVEN a ground family whose text/bg pair measures 7.2:1
- WHEN the ground-onboarding gate runs
- THEN it does not FAIL

#### Scenario: A family's derived token misses AA
- GIVEN a ground family whose derived `--c-text-muted` (blended 36.6%
  toward `--c-bg-alt`, `design-system.md:335-394`) measures 4.1:1 against
  its own `--c-bg-alt`
- WHEN `tests/test-write-path.php` recomputes the ratio for that family
- THEN it FAILs — body copy must clear 4.5:1, extended from 4 families to 9

### Requirement: Every Ground Family Has Its Own Accent

`$ACCENT_BY_GROUND` MUST assign each of the 9 families its own accent hex;
no family may hold a value only because none was assigned. Each accent
MUST clear 4.5:1 against both its family's `--c-bg` and `--c-bg-alt`
(`_build-gallery.php:677-688`).

#### Scenario: Independently-chosen accents all clear AA
- GIVEN 9 families with 9 independently assigned accent hexes
- WHEN the accent-eyebrow gate runs
- THEN every accent clears 4.5:1 on both its family's surfaces

#### Scenario: An accent misses the eyebrow bar
- GIVEN a family's accent measuring 3.9:1 against its `--c-bg-alt`
- WHEN the gate runs
- THEN it FAILs, naming the family and the measured ratio

### Requirement: Ink Tint Is Per-Style, Weight Still Re-Pins To Luminance

Each `STY-*` MUST declare its own `$INK_TINT`, not the shared `0.45`
constant. The derived shadow ink MUST still converge on its neutral
endpoint's own luminance within `ink_quant_bound()` (`:895`) and MUST clear
a channel spread of ≥20 (`:925`). A style MAY declare ink position `none`.

#### Scenario: Two styles, two hues, same weight discipline
- GIVEN style A declares `$INK_TINT = 0.30` and style B declares `0.60`
- WHEN each derives its shadow ink
- THEN the two shadow inks differ in hue and both still land within
  `ink_quant_bound()` of their own neutral endpoint's luminance

#### Scenario: Channel spread falls under the floor
- GIVEN a style's derived shadow ink measures a channel spread of 14
  (max − min across R/G/B)
- WHEN the ink gate runs
- THEN it FAILs — under 20 is a neutral, and a two-colour map whose dark
  ink is grey is not a two-colour map

#### Scenario: A style opts out of tinted ink
- GIVEN a style declares ink position `none`
- WHEN its photography renders
- THEN no tinted-duotone treatment applies, and every gate untouched by
  ink (contrast, spread, convergence) is not evaluated for that style

### Requirement: `soft-shadow` Is Derived From `--c-text`

The `soft-shadow` elevation position's `--elev-rest`/`--elev-hover` values
MUST be computed from the resolved `--c-text` of the active ground family,
not the fixed `rgba(0,0,0,.04)` literal `design-system.md:538-544`
currently tables.

#### Scenario: Light and dark grounds diverge
- GIVEN a light-ground family and a dark-ground family both using
  `soft-shadow`
- WHEN each derives its shadow rgba
- THEN the two measurably differ, each computed from its own `--c-text`

#### Scenario: No perceptual regression check exists yet
- GIVEN `soft-shadow` on a dark-ground family
- WHEN it is checked for visual indistinguishability from the old fixed
  literal
- THEN no automated verifier exists for this today — a perceptual
  difference assertion is new work this spec requires but does not name;
  design/tasks assigns it

## Out of Scope

Which 9 families are named and their literal hex values — a design-time
decision. Motion or ornament treatments the backlog styles need.
