# Demo Authoring Contract Specification

## Purpose

Defines the floor every one of the 10 surviving demos (lumiere, the real
estate pilot, aranda, a new lawyers brand, alinea, a new gyms brand, corte,
bajura, tueste, medida) must clear: anchor placement, re-measured accent
contrast, its own photo set, a declared responsive behaviour, an Envoltorio
table, and — per D5 — a complete multi-page site, not a lone home page.

## Requirements

### Requirement: Anchor Placement, No New Anchors

Every corporate demo MUST anchor on an existing `design-personalities.md`
entry. A brand MAY override only `ground`, `accent`, type pairing and its
own photographs; it MUST borrow `scale`, `density`, `composition` and
`elevation` unchanged from its anchor (`_build-gallery.php:445`). No demo
may introduce a new personality anchor.

#### Scenario: Brand overrides an axis beyond ground/accent/type/photos
- GIVEN a brand config overrides `density` or `composition` relative to its declared anchor
- WHEN `framework-audit.php` runs
- THEN it emits FAIL row `RT_MOCKUP_AXES_MISMATCH`

#### Scenario: Pilot overrides only ground
- GIVEN the real-estate pilot anchors on `PERS-EDITORIAL` and overrides only `ground`
- WHEN `framework-audit.php` runs
- THEN `RT_MOCKUP_AXES_MISMATCH` does not fire and `RT_PERS_TOO_SIMILAR` still passes (no new anchor was declared)

### Requirement: Container Width Is the House Token

Every demo MUST render at `--container-max: 1280px` (`design-system.md:138`).
No demo may fork a different container-max value, even when a design brief
requests one (D3 — the design reconciles to the token, not vice versa).

#### Scenario: Demo hardcodes a different container width
- GIVEN a mockup asset declares a container width literal other than `1280px`
- WHEN `framework-audit.php` runs
- THEN it emits FAIL row `RT_MOCKUP_CONTAINER_FORK`

#### Scenario: Demo uses the house token
- GIVEN a mockup asset consumes `--container-max` from `design-system.md` unmodified
- WHEN `framework-audit.php` runs
- THEN `RT_MOCKUP_CONTAINER_FORK` does not fire

### Requirement: Accent Text-Role Contrast Clears 4.5:1 by Darkening

Every demo's accent colour, wherever rendered in a text role, MUST measure
≥4.5:1 against every ground it is rendered on. Where the default accent
fails (e.g. `#8A7B5C` at 3.77:1 on paper / 4.29:1 on ink), the fix MUST be
darkening the accent, not demoting the text role to `--c-text` (D4).

#### Scenario: Accent fails 4.5:1 in a text role
- GIVEN a demo's accent renders body/antetítulo text at <4.5:1 against its declared ground
- WHEN `framework-audit.php`'s contrast sweep runs
- THEN it emits FAIL row `RT_GALLERY_ACCENT_TEXT_FAIL`

#### Scenario: Darkened accent passes on both grounds
- GIVEN a demo darkens its accent until it measures ≥4.5:1 on both `paper` and `ink` grounds it uses
- WHEN the contrast sweep runs
- THEN `RT_GALLERY_ACCENT_TEXT_FAIL` does not fire

### Requirement: Own Photo Set, Slug Equals Filename

Every demo MUST supply its own photographs (no shared/borrowed shoot across
demos), each registered in the manifest with a slug matching its filename
and a licence, per the existing manifest gate.

#### Scenario: Image with no manifest entry
- GIVEN a demo renders an image with no matching manifest row
- WHEN `framework-audit.php` runs
- THEN it emits FAIL row `RT_GALLERY_NO_MANIFEST`

### Requirement: Demo Is a Complete Multi-Page Site

An Envato-grade demo MUST declare more than one page archetype — a home
`TPL-*` plus at least one inner-page `TPL-*` it links to — even when Phase 3
delivers those pages one at a time (D5: "sitio completo aunque vayamos de a
poco"). A demo declaring only a home archetype is not yet Envato-grade.

#### Scenario: Demo declares only a home archetype
- GIVEN a brand entry names a home `TPL-*` and no inner-page `TPL-*`
- WHEN `framework-audit.php` runs
- THEN it emits FAIL row `RT_GALLERY_SINGLE_PAGE_DEMO`

#### Scenario: Demo declares home plus inner pages, delivered incrementally
- GIVEN a brand entry names a home `TPL-*` and at least one inner-page `TPL-*`, even if only the home page is built so far
- WHEN `framework-audit.php` runs
- THEN `RT_GALLERY_SINGLE_PAGE_DEMO` does not fire, because the declared page set — not the build order — is what the row checks

### Requirement: Envoltorio and Responsive Are Both Declared

Every demo MUST declare an Envoltorio table (per `catalog-wrapper-integrity`)
and an explicit responsive behaviour; "pendiente de definir" MUST NOT ship,
since `qa-review` and `visual-verification` both require it resolved.

#### Scenario: Responsive left undefined
- GIVEN a demo's handoff states responsive as "pendiente de definir"
- WHEN `qa-review`'s entry check runs
- THEN it blocks progression to `visual-verification`
