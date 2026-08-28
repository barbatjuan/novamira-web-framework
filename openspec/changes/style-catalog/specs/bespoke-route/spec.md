# Bespoke Route Specification

## Purpose

`ROUTE-BESPOKE` is the from-scratch escape hatch, deliberately the more
expensive route so it cannot become the new untended default corner the
way `PERS-INSTITUTIONAL` did (`mockup-guide.md:436-447`) — the same failure
mode `recommender.md:249-254` already recorded once when no archetype
meant every site shipped the same Nosotros and Contacto. Deferred to
Slice 6 so it is designed only after the catalog has real client mileage.

## Requirements

### Requirement: Zero Precharge

A project declared under `ROUTE-BESPOKE` MUST start with no toggle
precharge and no `STY-*` resolution. Every art-direction decision is
answered from scratch, none inherited from a catalog entry.

#### Scenario: No inherited defaults
- GIVEN a project declares `ROUTE-BESPOKE`
- WHEN its initial toggle/axis state is inspected
- THEN no toggle carries a precharged value and no `STY-*` id is set

### Requirement: Eight Explicit Answers And A Declared Wireframe, Enforced

`ROUTE-BESPOKE` MUST require an explicit answer for each of the 8 axes
(scale, ground, density, elevation, composition, accent policy, chassis,
ornament) plus a declared wireframe, before builder-core begins.
`RT_BESPOKE_UNDECLARED` MUST FAIL an incomplete manifest and MUST NOT FAIL
a complete one.

#### Scenario: Complete manifest passes
- GIVEN a bespoke manifest with all 8 axis answers and a declared wireframe
- WHEN `framework-audit.php` runs
- THEN `RT_BESPOKE_UNDECLARED` does not FAIL

#### Scenario: Incomplete manifest fails
- GIVEN a bespoke manifest missing the `ornament` answer
- WHEN `framework-audit.php` runs
- THEN `RT_BESPOKE_UNDECLARED` FAILs, naming the missing axis

### Requirement: No Accessibility Exemption

A bespoke build MUST pass every gate a catalog build passes: AA contrast
throughout, the AAA 7:1 ground gate, 4.5:1 accent-as-eyebrow, exactly one
`<h1>`, Lighthouse accessibility ≥ 90, touch targets, and `<details>`
semantics (`RT_MOCKUP_DISCLOSURE_STATE`). Route selection MUST NOT change
which gates run.

#### Scenario: Bespoke build passes the same chain
- GIVEN a completed bespoke build
- WHEN the standard accessibility chain runs (house-rules.md rows 12-13,
  the 7:1/4.5:1 gates, `RT_MOCKUP_DISCLOSURE_STATE`)
- THEN it passes on the same terms a catalog build would

#### Scenario: A gate is skipped for being bespoke
- GIVEN a bespoke build's Lighthouse run is omitted because "it's custom"
- WHEN the delivery chain is audited
- THEN the same gate that would block a catalog build blocks this one —
  no bespoke exemption exists anywhere in the chain

### Requirement: Ledger Registration Is Mandatory

A completed `ROUTE-BESPOKE` delivery MUST write a row to `shipped-log.md`
identical in shape to a catalog delivery's row, so the 5-row
`RT_STYLE_REPEATS_RECENT` window and future promotions both see it.

#### Scenario: Bespoke delivery appears in the ledger
- GIVEN a bespoke project is delivered
- WHEN `shipped-log.md` is read
- THEN its last row records the delivery, indistinguishable in shape from
  a catalog row

### Requirement: Promotion Feeds The Catalog, Never Bypasses It

A bespoke build's 8-axis answers MAY be promoted into a new `STY-*` entry.
A promoted entry MUST clear `RT_STYLE_TOO_SIMILAR` against the existing
catalog exactly as any other new entry would — promotion grants no
exemption from distinctness.

#### Scenario: Promoted entry clears distinctness
- GIVEN a promoted entry shares ≤2 of 8 positions with every existing entry
- WHEN `framework-audit.php` runs
- THEN `RT_STYLE_TOO_SIMILAR` does not FAIL

#### Scenario: Promoted entry collides
- GIVEN a promoted entry shares 3 of 8 positions with an existing entry
- WHEN `framework-audit.php` runs
- THEN `RT_STYLE_TOO_SIMILAR` FAILs — promotion is blocked until revised

## Out of Scope

The catalog itself (`style-catalog`), which this route feeds but does not
modify directly. Any change to the accessibility gates themselves.
