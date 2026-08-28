# Style Catalog Specification

## Purpose

`design-personalities.md`'s 5 anchors are replaced by a catalog of 8
`STY-*.md` entries (locked v1 size — 4 of the originally proposed 12 move
to `_backlog.md`, driven by the 7-face font budget in
`nm_font_registry()`, `_fonts.php:63-69`). This spec defines what a
catalog entry must declare and the gates that keep it distinct and
in-budget.

## Requirements

### Requirement: Exactly 8 `STY-*` Entries Ship In V1

`references/style-catalog/` MUST contain exactly 8 `STY-*.md` files plus
`_README.md` and `_backlog.md`. `design-personalities.md` MUST NOT exist
after this change; the 5 prior anchors MUST appear as entries among the 8.
Each entry MUST declare a full position for all 8 axes and its toggle
precharge list (see art-direction-ledger).

#### Scenario: Catalog is exactly 8
- GIVEN `references/style-catalog/` after this change
- WHEN its `STY-*.md` files are counted
- THEN there are exactly 8, plus `_README.md` and `_backlog.md`

#### Scenario: No automated size floor/ceiling
- GIVEN a 9th `STY-*.md` is added
- WHEN the test chain runs
- THEN no audit rule catches the count — catalog SIZE has no verifier by
  design; the load-bearing gate is distinctness (`RT_STYLE_TOO_SIMILAR`),
  not a count, because count is trivially satisfied without being true

### Requirement: Every Style Names Only An Embedded Font

Every `STY-*.md` font pairing MUST resolve to a family `nm_font_registry()`
declares, or reuse an embedded family at a different weight/stretch (as
`Archivo`/`Archivo Expanded` already do). It MUST NOT name a family absent
from the registry.

#### Scenario: Reused embedded family
- GIVEN a style pairs `Archivo` (display) with `Archivo Expanded` (body)
- WHEN `framework-audit.php` runs
- THEN `RT_MOCKUP_FONT_NOT_EMBEDDED` does not FAIL

#### Scenario: Unembedded family named
- GIVEN a style names `Canela Deck`, absent from `nm_font_registry()`
- WHEN a mockup rendered under that style is audited
- THEN `RT_MOCKUP_FONT_NOT_EMBEDDED` FAILs

### Requirement: The Full V1 Catalog Clears The Distinctness Gate

All 8 v1 entries, compared pairwise, MUST each share no more than 2 of 8
axis positions with any other entry (`RT_STYLE_TOO_SIMILAR`, defined in
`style-axes`).

#### Scenario: Full catalog passes
- GIVEN the 8 shipped v1 entries
- WHEN every pair is compared
- THEN the maximum shared-position count across all pairs is 2

#### Scenario: A late addition collides
- GIVEN entry #8 shares 4 of 8 positions with entry #3
- WHEN `framework-audit.php` runs
- THEN `RT_STYLE_TOO_SIMILAR` FAILs, and the catalog cannot ship until
  entry #8 is revised

## Out of Scope

The 4 backlog styles' motion/ornament systems (Kinetic, Cyberpunk, Y2K,
Retro, Playful, Feminine, Editorial Fashion, Experimental) — they stay in
`_backlog.md` until those systems exist. Intake, ledger, and toggle
enforcement are `art-direction-ledger`'s scope, not this one's.
