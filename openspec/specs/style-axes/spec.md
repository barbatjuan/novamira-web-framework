# Style Axes Specification

## Purpose

Today's distinctness space is 5 axes (`scale`, `ground`, `density`,
`elevation`, `composition`), shared by `axis_matches()`
(`framework-audit.php:1589-1599`). This spec adds three axes — accent
policy, chassis, ornament — bringing the total to 8, registered in
`framework-audit.php` before any style names a position on them, and widens
the anchor-distinctness gate to the same 8.

## Requirements

### Requirement: Three New Axes Are Registered Before Any Style Uses Them

`accent policy`, `chassis`, and `ornament` MUST each exist as a named
spectrum (≥2 positions) in `framework-audit.php`'s axis table before any
`STY-*` document names a position on them. This mirrors the existing
audit-before-style ordering `RT_PERS_BAD_AXIS` already enforces for the
5 original axes.

#### Scenario: Axis table has 8 axes before Slice 4
- GIVEN `framework-audit.php` after this axis registration lands
- WHEN its axis table is inspected
- THEN it lists 8 axes: the 5 existing plus accent policy, chassis, ornament

#### Scenario: A style names an undefined axis position
- GIVEN a `STY-*` document names a position on `ornament` the axis table
  does not define
- WHEN `framework-audit.php` runs
- THEN `RT_PERS_BAD_AXIS` FAILs, naming the style and the undefined position

### Requirement: The Anchor-Distinctness Gate Widens To 8 Axes And Renames

`RT_PERS_TOO_SIMILAR` MUST become `RT_STYLE_TOO_SIMILAR` and MUST compare
all 8 axis positions between any two catalog entries. It MUST FAIL when two
entries share more than 2 of the 8 positions.

#### Scenario: Two entries at the boundary
- GIVEN two `STY-*` entries sharing exactly 2 of 8 axis positions
- WHEN `framework-audit.php` runs
- THEN `RT_STYLE_TOO_SIMILAR` does not FAIL

#### Scenario: Two entries over the boundary
- GIVEN two `STY-*` entries sharing 3 of 8 axis positions
- WHEN `framework-audit.php` runs
- THEN `RT_STYLE_TOO_SIMILAR` FAILs, naming the shared positions

### Requirement: One Position-Agreement Rule Serves Every Distinctness Gate

Any two rules that judge whether two entries share an axis position
(`RT_STYLE_TOO_SIMILAR`, `RT_GALLERY_NOT_DISTINCT`, `RT_PROOF_NOT_DISTINCT`)
MUST agree on what "shares a position" means for all 8 axes — adding an
axis to the comparison MUST update every consuming rule identically, so the
three gates cannot drift out of sync with each other.

#### Scenario: New axis reaches every consumer
- GIVEN `ornament` is added to the shared position-agreement logic
- WHEN `RT_STYLE_TOO_SIMILAR`, `RT_GALLERY_NOT_DISTINCT`, and
  `RT_PROOF_NOT_DISTINCT` each run
- THEN all three judge `ornament` agreement identically

#### Scenario: No verifier for comparator forking
- GIVEN a future edit adds a second, independent position-comparison
  implementation elsewhere in the audit
- WHEN the test chain runs
- THEN no automated check catches the fork — this requirement has no
  verifier beyond code review, because detecting a semantically-duplicate
  comparator is a structural judgment, not a value a test can assert

## Out of Scope

The specific named positions each new axis will hold — that is the style
catalog's (Slice 4) job to spend. Re-baselining the ~110 pinned assertions
this widening touches (`RT_GALLERY_NOT_DISTINCT`, `RT_PROOF_NOT_DISTINCT`,
`RT_STYLE_TOO_SIMILAR`, `RT_MOCKUP_AXES_MISMATCH`, `RT_AXIS_VALUE_MISSING`)
is implementation, not a spec-level requirement.
