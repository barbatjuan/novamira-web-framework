# Catalog Wrapper Integrity Specification

## Purpose

23 home `TPL-*.md` archetypes exist today; 16 declare no wrapper contract at
all and render identically. **7 already carry one** — `TPL-C-03`, `TPL-C-05`,
`TPL-C-06`, `TPL-C-13`, `TPL-C-14` (Lumière, the reference implementation),
`TPL-E-01`, `TPL-E-07` — as a three-column table (`| Sección | Envoltorio |
… |`) embedded inside `## 2. Wireframe`, with **no dedicated heading**. This
spec formalizes that existing convention as a gate: every surviving home
archetype MUST carry the table, its values MUST normalize to a closed shape
vocabulary, and no two archetypes MUST share the same normalized shape
sequence. The gate MUST pass against `TPL-C-14` unmodified — a detector that
fails its own reference implementation is not a gate.

## Requirements

### Requirement: Every Home Archetype Declares an Envoltorio Table

Every home `TPL-*.md` MUST contain a markdown table with a header cell that
is, case-insensitively and trimmed, exactly `Envoltorio`, whose first column
names a `COMP-*` id per data row — mirroring how `gallery_register_count()`
(`framework-audit.php:2478-2498`) already finds a table by a literal header
cell, never by heading or position.

#### Scenario: Archetype with no Envoltorio table
- GIVEN a home `TPL-*.md` with no table carrying a header cell `Envoltorio`
- WHEN `framework-audit.php` runs
- THEN it emits FAIL row `RT_TPL_NO_ENVOLTORIO`

#### Scenario: TPL-C-14 (Lumière) passes unmodified
- GIVEN `TPL-C-14-ritual-bono.md` as it exists today at `main @ 35a38b4`, with its `| Sección | Envoltorio | El gesto |` table under `## 2. Wireframe`
- WHEN `framework-audit.php` runs
- THEN `RT_TPL_NO_ENVOLTORIO` does NOT fire for that file — this is the detector's own acceptance test

### Requirement: Wrapper Values Normalize to a Closed Shape Vocabulary

`Envoltorio` column values are free Spanish prose (`contenido`, `banda a
sangre`, `banda con fotografía al fondo`, `la sección ES la fila`, …), not a
closed enum. Each value MUST normalize to one of the three shapes
`sec_open($cls, $label, $shape)` already accepts (`_build-gallery.php:15468`):
a value containing `banda` → `bleed`; a value containing `fila` → `row`;
everything else (including `contenido`) → `contained`. Signature comparison
MUST use these normalized shapes, never the raw string.

#### Scenario: Two phrasings normalize to the same shape
- GIVEN one row reads `banda a sangre` and another reads `banda con fotografía al fondo`
- WHEN the normalizer runs
- THEN both resolve to `bleed`

### Requirement: Wrapper Signatures Are Distinct

No two home `TPL-*.md` files MAY declare the identical ORDERED sequence of
normalized shapes across their `Envoltorio` rows. This closes the gap
`RT_TPL_TOO_SIMILAR` leaves open, since that row diffs section inventory,
never markup nesting.

#### Scenario: Two archetypes share a normalized shape sequence
- GIVEN two home `TPL-*.md` files whose Envoltorio rows normalize to the same ordered sequence (e.g. `bleed, contained, bleed, row, contained`)
- WHEN `framework-audit.php` runs
- THEN it emits FAIL row `RT_TPL_WRAPPER_DUPLICATE` naming both files

#### Scenario: Raw strings differ, normalized sequences match — still a FAIL
- GIVEN two archetypes phrase their bleed rows differently but land on the identical shape sequence otherwise
- WHEN `framework-audit.php` runs
- THEN `RT_TPL_WRAPPER_DUPLICATE` still fires, because comparison is on normalized shapes, not raw text

### Requirement: Gate Precedes Amputation (TDD Ordering)

The commit adding `RT_TPL_NO_ENVOLTORIO`, the normalizer, and
`RT_TPL_WRAPPER_DUPLICATE` to `framework-audit.php`, with failing tests in
`test-framework-audit.php` proving the rows detect the 16-archetype
no-table collision, MUST land before any commit deletes a non-Lumière home
archetype.

#### Scenario: Tests exist and fail against current state
- GIVEN the pre-deletion tree, with 16 archetypes declaring no Envoltorio table
- WHEN the new test cases run
- THEN they FAIL, proving the collision the rows are meant to catch

### Requirement: Every Pre-Existing Envoltorio Table Has a Stated Phase-2 Disposition

Of the 7 archetypes that already carry the table, 4 (`TPL-C-03`, `TPL-C-05`,
`TPL-C-06`, `TPL-E-01`) carry no demo brand (`corporate/_README.md`) and fall
inside the Phase 2 deletion scope — their tables are moot once removed.
`TPL-E-07` is NOT in this list: it backs `bajura` (`_build-gallery.php:6148`)
and survives Phase 2 with its table intact. `TPL-C-13` is the exception:
`corporate/_README.md:37` marks it explicitly unbranded, `recommender.md:241`
already pairs it with `TPL-PROPERTY-01` into a 4-page real-estate set (Home +
ficha + Nosotros + Contacto), and it already satisfies this spec's
wrapper-signature contract. Phase 2's deletion commit MUST explicitly record
`TPL-C-13`'s disposition (retained as the real-estate pilot's home archetype,
or deleted with a named replacement) — it MUST NOT be swept silently into the
same pass as the four brand-specific archetypes with no reuse case.

#### Scenario: TPL-C-13 disposition undocumented
- GIVEN a Phase 2 deletion commit removes `TPL-C-13` with no recorded rationale and no replacement named in `recommender.md`
- WHEN a reviewer checks the commit against this requirement
- THEN the commit fails review — `TPL-C-13`'s disposition MUST be explicit either way

#### Scenario: TPL-C-13 retained for the pilot
- GIVEN Phase 2 retains `TPL-C-13` and `recommender.md` continues naming it for the real-estate pilot
- WHEN `framework-audit.php` runs
- THEN `RT_TPL_UNROUTABLE` does not fire for it, and its Envoltorio table continues to satisfy `RT_TPL_NO_ENVOLTORIO` / `RT_TPL_WRAPPER_DUPLICATE`
