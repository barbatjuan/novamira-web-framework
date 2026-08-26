# Template Lane Contract Specification

## Purpose

Resolves the missing architecture invariant: `recommender.md:3` today forbids
building from scratch outright, with no path for a brief that legitimately
matches no `TPL-*`. This spec defines two lanes — **catálogo** (recommend an
existing `TPL-*`) and **bespoke** (design-system primitives, no `TPL-*`
match) — plus the **promotion gate** that lets a bespoke result rarely and
strictly become a new catalogue archetype (D2).

## Requirements

### Requirement: Two-Lane Resolution

Every `web-templates` recommendation MUST resolve to exactly one of two
lanes: catálogo (a named `TPL-*` id) or bespoke (no `TPL-*` match, explicit
flag). No third lane exists.

#### Scenario: Catalogue match found
- GIVEN a brief that matches an existing `TPL-*` archetype
- WHEN `web-templates` runs its recommender flow
- THEN it returns a catálogo-lane result naming that `TPL-*` id

#### Scenario: No catalogue match
- GIVEN a brief that matches no `TPL-*` in `recommender.md`'s active set
- WHEN the recommender flow completes step 3 (RECOMENDACIÓN)
- THEN it returns a bespoke-lane result with the negative-match reasoning recorded, not a forced `TPL-*`

### Requirement: Bespoke Is an Escape Hatch, Not a Default

`recommender.md` MUST NOT let a brief reach the bespoke lane before every
`TPL-*` in the resolved type set (ecommerce/corporate) has been checked
against it. (Enforced by: no static row — `recommender.md`'s Flow order is
checked by NEW row `RT_RECOMMENDER_NO_LANE_FORK`, FAIL if the Flow section
declares no bespoke fork after step 3.)

#### Scenario: Bespoke reached without exhausting catalogue
- GIVEN `recommender.md`'s Flow section has no explicit "no match → bespoke" step
- WHEN `framework-audit.php` runs
- THEN it emits `RT_RECOMMENDER_NO_LANE_FORK`

### Requirement: Promotion Gate Is Strict

A bespoke design MAY be promoted into a new or existing `TPL-*` ONLY when it
passes every audit row a native archetype is held to: `RT_TPL_NO_WIREFRAME`,
`RT_TPL_UNROUTABLE`, `RT_TPL_TOO_SIMILAR`, and (per `catalog-wrapper-integrity`)
`RT_TPL_NO_ENVOLTORIO` / `RT_TPL_WRAPPER_DUPLICATE`. A bespoke design failing
any one of these MUST NOT be added to `recommender.md`'s active set.
(Enforced by: NEW row `RT_RECOMMENDER_PROMOTION_GATE_MISSING`, FAIL if
`recommender.md`/`web-templates/SKILL.md` names no promotion criterion at
all.)

#### Scenario: Bespoke design passes every gate
- GIVEN a bespoke design with a distinct wireframe, a unique wrapper signature, and a recommender.md entry
- WHEN `framework-audit.php` runs after promotion
- THEN no `RT_TPL_*` or `RT_RECOMMENDER_PROMOTION_GATE_MISSING` row fires

#### Scenario: Bespoke design shares a wrapper signature with an existing archetype
- GIVEN a bespoke design whose Envoltorio table duplicates a surviving `TPL-*`
- WHEN promotion is attempted
- THEN `RT_TPL_WRAPPER_DUPLICATE` FAILs and the promotion MUST be rejected

### Requirement: Lane Transparency Downstream

`ux-design-system` and `html-mockup` MUST accept catálogo-lane and
bespoke-lane input through the same interface; neither skill's dialogue may
branch on which lane produced its input.

#### Scenario: Two-lane smoke test
- GIVEN one bespoke-lane architecture and one catálogo-lane architecture
- WHEN each runs `web-templates` → `ux-design-system` → `html-mockup`
- THEN neither downstream skill asks which lane the input came from
