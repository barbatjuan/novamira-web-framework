# Gallery Information Architecture Specification

## Purpose

Today 67 strips mix 55 axis-proof strips (one shared content set proving the
five personality anchors) with 12 catalogue strips in one flat list, and the
gallery is never routed by `agents/novamira-web-orchestrator.md`. This spec
separates axis proof from catalogue, groups the catalogue estilo → enfoque
around the D1 10-demo set, fixes the Registers divisor to one row per
surviving demo, and routes the gallery + lane fork in the orchestrator.

## Requirements

### Requirement: Axis Proof Lives in ux-design-system

Personality-anchor proof strips (the shared-content set proving the five
anchors, e.g. `$ANCHORS`-only strips with no `$BRANDS` key) MUST render
under `ux-design-system`'s own surface, not the `html-mockup` catalogue
gallery.

#### Scenario: Anchor-only strip found in the catalogue gallery
- GIVEN a gallery strip with no `$BRANDS` key backing it
- WHEN `framework-audit.php` runs against the built catalogue gallery
- THEN it emits FAIL row `RT_GALLERY_AXIS_LEAK`

#### Scenario: Catalogue gallery holds only branded demos
- GIVEN every strip in the catalogue gallery is backed by a `$BRANDS` entry
- WHEN `framework-audit.php` runs
- THEN `RT_GALLERY_AXIS_LEAK` does not fire

### Requirement: Catalogue Grouped Estilo → Enfoque

The catalogue gallery MUST group its 10 surviving demos first by estilo
(style family) and then by enfoque (sector focus) — never as one flat list.

#### Scenario: Ten demos rendered
- GIVEN the 10 demos (lumiere, inmobiliaria-de-la-o, aranda, new-lawyers, alinea, new-gyms, corte, bajura, tueste, medida)
- WHEN the catalogue gallery view renders
- THEN each demo appears once, nested under its estilo group and enfoque sub-group

### Requirement: Registers Divisor Is Per-Demo

`_gallery-images.md`'s Registers table MUST declare at least one register
row per surviving catalogue demo (≥10 rows), not per style family (~3 rows).
`RT_GALLERY_ONE_SHOOT` computes `cap = ceil(N / R)` from this table's row
count R; a per-demo count keeps the cap tight enough to catch a shared
shoot (`ceil(70/10)=7`), while a per-family count would not (`ceil(70/3)=24`).

#### Scenario: Register count below demo count
- GIVEN the Registers table has fewer rows than there are surviving catalogue demos
- WHEN `framework-audit.php` runs
- THEN it emits FAIL row `RT_GALLERY_REGISTER_COUNT_MISMATCH`

#### Scenario: One register per demo
- GIVEN the Registers table declares exactly one row per surviving demo
- WHEN `framework-audit.php` runs
- THEN `RT_GALLERY_REGISTER_COUNT_MISMATCH` does not fire and `RT_GALLERY_ONE_SHOOT`'s cap reflects the tighter divisor

### Requirement: Registers Table Replaced Same-Commit

The commit that removes the current house (Piedra Valdes) Registers table
MUST add its per-demo replacement in the same commit. `RT_GALLERY_ONE_SHOOT`
already FAILs on a missing divisor when no table with a literal `Register`
header cell exists.

#### Scenario: House table removed with no replacement
- GIVEN a commit deletes the house Registers table and adds no replacement
- WHEN `framework-audit.php` runs
- THEN `RT_GALLERY_ONE_SHOOT` FAILs on a missing divisor

#### Scenario: House table replaced same-commit
- GIVEN the same commit removes the house table and adds the 10-row per-demo table
- WHEN `framework-audit.php` runs
- THEN `RT_GALLERY_ONE_SHOOT` computes its cap from the new table without failing on a missing divisor

### Requirement: Orchestrator Routes the Gallery and the Lane Fork

`agents/novamira-web-orchestrator.md`'s routing map and "Order that works"
sections MUST name a gallery-consultation step and the catálogo/bespoke lane
fork before `web-templates` commits to an archetype.

#### Scenario: Routing map omits the gallery step
- GIVEN the orchestrator's routing map names no gallery-consultation entry
- WHEN `framework-audit.php` runs
- THEN it emits FAIL row `RT_ORCH_NO_GALLERY_STEP`

#### Scenario: Routing map names the gallery step and lane fork
- GIVEN the routing map and "Order that works" both name the gallery step and the lane fork ahead of `web-templates`'s recommendation
- WHEN `framework-audit.php` runs
- THEN `RT_ORCH_NO_GALLERY_STEP` does not fire
