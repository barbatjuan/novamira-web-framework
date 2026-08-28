# Client Chassis Generation Specification

## Purpose

The client mockup path is 6 lines wide (`html-mockup/SKILL.md:41-43`): a
project starts from one of two hand-maintained files
(`corporate-mockup.html`, `ecommerce-mockup.html`) and re-points five token
lines plus the `Anchor:` marker. Everything below `:root` is byte-identical
across clients. This spec makes the client chassis generator output —
produced by `_build-gallery.php` for the resolved site type and style — and
closes the literal gap named at `mockup-guide.md:455-458`: the axis-mismatch
gate checks a label, never a value.

## Requirements

### Requirement: Client Chassis Is Generator Output, Not A Hand-Edited File

The starting HTML a client project renders from MUST be produced by invoking
`_build-gallery.php` for the resolved site type (corporate/ecommerce) and
style, not by copying and editing a static asset. `corporate-mockup.html`
and `ecommerce-mockup.html` MUST NOT exist in the repository, and no
`SKILL.md` or reference doc MUST instruct re-pointing either file.

#### Scenario: Generated chassis exists per site type
- GIVEN a project resolves site type `corporate` and a `STY-*` id
- WHEN the chassis is produced
- THEN it is generator output from `_build-gallery.php`, not a copied file

#### Scenario: Legacy chassis files are gone
- GIVEN the repository after this change
- WHEN searched for `corporate-mockup.html` or `ecommerce-mockup.html`
- THEN neither path exists, and no skill doc references either name

### Requirement: Generated Chassis Is Not Exempt From Any Mockup Rule

Chassis output from the generator MUST pass every `RT_MOCKUP_*` row exactly
as hand-authored HTML did: `RT_MOCKUP_NO_AXES`, `RT_MOCKUP_ANCHOR_UNDECLARED`,
`RT_MOCKUP_AXES_MISMATCH`, `RT_MOCKUP_DISCLOSURE_STATE`,
`RT_MOCKUP_GRID_AUTOFILL`, `RT_MOCKUP_FONT_NOT_EMBEDDED`,
`RT_MOCKUP_BLEED_FIXED_BAND`, `RT_MOCKUP_BLEED_NOT_MEDIA`. Being generator
output MUST NOT be treated as a pass by itself.

#### Scenario: Generated `<details>` block passes
- GIVEN a generated chassis with a disclosure block built from `<details>`
  that opens exactly its first row
- WHEN `framework-audit.php` runs
- THEN `RT_MOCKUP_DISCLOSURE_STATE` does not FAIL

#### Scenario: Generated chassis drops a mockup rule
- GIVEN a generated chassis whose disclosure block does not open exactly
  its first row (a generation defect, not a hand-authoring one)
- WHEN `framework-audit.php` runs
- THEN `RT_MOCKUP_DISCLOSURE_STATE` FAILs — generation is not a pass

### Requirement: Axis Mismatch Is A Value Check, Not A Label Check

`RT_MOCKUP_AXES_MISMATCH` MUST compare each declared axis's actual token
value (e.g. `--fs-h1-max`) against the value the resolved anchor/style's
table defines for that position, not merely compare the position label
string. A label that agrees with the anchor while its token value does not
MUST FAIL.

#### Scenario: Label and value agree
- GIVEN a chassis labeled `scale: contained` whose `--fs-h1-max` equals the
  `contained` position's table value
- WHEN `framework-audit.php` runs
- THEN `RT_MOCKUP_AXES_MISMATCH` does not FAIL

#### Scenario: Label agrees, value does not (today's gap)
- GIVEN a chassis labeled `scale: contained` with a hand-typed
  `--fs-h1-max: 53` that belongs to a different position
- WHEN `framework-audit.php` runs
- THEN `RT_MOCKUP_AXES_MISMATCH` FAILs, naming the disagreeing token

## Out of Scope

Deleting the two legacy files before generator output has rendered and
passed every mockup rule (Slice 1's own rollback constraint: deletion is
the last step, not the first). Section order and template selection —
unchanged by this capability.
