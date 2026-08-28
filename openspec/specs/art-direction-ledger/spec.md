# Art Direction Ledger Specification

## Purpose

No inter-project memory exists: every anti-sameness gate compares the
catalog against itself, never against what actually shipped. This spec
extends intake (`recommender.md:41-52`) to capture the style pick, a
negative brief, and rejected colour temperature; wires the `design`
manifest section to a real writer; adds `shipped-log.md` with a 5-delivery
lookback WARN; and enforces that a generated project actually ships the
toggles its resolved style declares — no universal floor.

## Requirements

### Requirement: Intake Captures Style Pick, Negative Brief, Rejected Tone

Before builder-core begins, intake MUST record three explicit facts: the
resolved `STY-*` id, a negative brief (what was explicitly rejected), and
a rejected colour temperature.

#### Scenario: All three fields present
- GIVEN intake completes for a project
- WHEN `es_manifest_read()['design']` is inspected
- THEN it holds a non-empty style id, negative brief, and rejected tone

#### Scenario: No automated gate on the conversational step itself
- GIVEN a build proceeds to design with the style pick unresolved
- WHEN the intake conversation is reviewed
- THEN no audit rule catches an unresolved verbal intake — only the
  manifest write (below) is machine-verifiable; the conversation itself
  has no verifier

### Requirement: The `design` Manifest Section Persists The Resolved Style

`es_manifest_record('design', …)` MUST be called during style resolution
and MUST persist at minimum the resolved `STY-*` id.

#### Scenario: Manifest holds the resolved style
- GIVEN a project completes style resolution
- WHEN `es_manifest_read()` is called
- THEN `['design']` contains the resolved `STY-*` id, non-empty

### Requirement: `shipped-log.md` Records Every Delivery

`skills/ux-design-system/references/shipped-log.md` MUST gain one row per
delivered project, recording at minimum date, project id, and resolved
`STY-*`.

#### Scenario: Ledger grows by one row
- GIVEN a project is delivered
- WHEN `shipped-log.md` is read afterward
- THEN its last row matches the just-shipped project's date, id, and style

### Requirement: `RT_STYLE_REPEATS_RECENT` Warns Over The Last 5, Never Fails

A new audit rule MUST compare a newly resolved style against the last 5
rows of `shipped-log.md` and emit WARN — never FAIL — when that style
repeats within the window; it MUST stay silent outside the window and on a
fresh pick (`house-rules.md:31`: "a gate that always fails is a gate
everyone learns to skip").

#### Scenario: Fresh pick, no warning
- GIVEN the last 5 ledger rows contain no occurrence of style `STY-QUARRY`
- WHEN a project resolves to `STY-QUARRY`
- THEN `RT_STYLE_REPEATS_RECENT` stays silent

#### Scenario: Repeat within the window
- GIVEN `STY-QUARRY` shipped 2 deliveries ago (row 3 of the last 5)
- WHEN a project resolves to `STY-QUARRY` again
- THEN `RT_STYLE_REPEATS_RECENT` WARNs, and the audit still exits 0

#### Scenario: Repeat outside the window
- GIVEN `STY-QUARRY` shipped 6 deliveries ago (row 6, outside the last 5)
- WHEN a project resolves to `STY-QUARRY` again
- THEN `RT_STYLE_REPEATS_RECENT` stays silent — the window is exactly 5

### Requirement: A Generated Project Ships Its Style's Declared Precharge

For every toggle a project's resolved `STY-*.md` declares, the generated
project MUST carry that toggle at the declared value, not the toggle's own
catalog default. No universal minimum toggle count applies — the gate
checks a style's own declared list, not a fixed N (today: 1 of 39 toggles
moved off default across 67 gallery strips).

#### Scenario: All declared toggles applied
- GIVEN a style declares 6 toggles at non-default values
- WHEN the generated project's toggle state is inspected
- THEN all 6 measure at their declared, non-default values

#### Scenario: A declared toggle ships at default anyway
- GIVEN one of the style's 6 declared toggles ships at its catalog default
- WHEN a new toggle-precharge audit rule runs (named in design, not here)
- THEN it FAILs, naming the toggle and the style that declared it

#### Scenario: No floor is imposed on styles with fewer declarations
- GIVEN style A declares 2 toggles and style B declares 6
- WHEN a project resolved to style A ships with exactly its 2 applied
- THEN the audit does not FAIL for "too few" toggles — the gate is
  per-style declaration, never a universal count

## Out of Scope

`ROUTE-BESPOKE`'s own intake and ledger obligations — covered in
`bespoke-route`, though both routes write to the same `shipped-log.md`.
