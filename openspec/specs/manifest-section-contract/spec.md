# Manifest Section Contract Specification

## Purpose

The manifest declares four sections (`site`, `design`, `pages`, `delivery`),
but their list only existed as prose in three places that could drift from
`es_manifest_record()` — a drift that corrupted a live manifest.
This spec makes the list one callable fact, fixes where the front page id
lives, removes a false "no state persists" claim, and annotates — not
deletes — a false claim about an unwritten section.

**Classification.** No `SKILL.md` contract changes; the manifest API is
unchanged. Agrees with "Modified Capabilities: None"; adds
`es_manifest_sections()` as `manifest-section-contract`, like
`gallery-bootstrap-integrity`.

## Requirements

### Requirement: Manifest Section List Is Machine-Readable

`es_manifest_sections()` MUST exist in `es-builder.php` and return exactly
`array('site','design','pages','delivery')`, in order. Every name MUST
round-trip through `es_manifest_record()`/`es_manifest_read()`. Prose at
`es-builder.php:2398-2401` and `knowledge.md:52-55` MUST cite the function,
not restate it.

#### Scenario: Exact order and count
- GIVEN `es-builder.php` is loaded
- WHEN `es_manifest_sections()` is called
- THEN it returns `['site','design','pages','delivery']`, in order, no fifth name

#### Scenario: Every section round-trips
- GIVEN each name `es_manifest_sections()` returns
- WHEN `es_manifest_record($name, $data)` runs, then `es_manifest_read()`
- THEN the recorded data equals `$data`

#### Scenario: Prose cites the function
- GIVEN the prose at `es-builder.php:2398-2401` or `knowledge.md:52-55`
- WHEN a reader looks for the list
- THEN it cites `es_manifest_sections()`, not a restated list

### Requirement: Front Page Id Is Recorded In `site`, Never In `pages`

`elementor-core/SKILL.md` step 8 MUST state `pages` holds slug→id only; the
front page id goes to `site`. The edit MUST be word-neutral or negative
against the 588-word count.

#### Scenario: `pages` no longer takes the front page
- GIVEN step 8
- WHEN a reader checks what `es_manifest_record('pages', …)` receives
- THEN it lists slug→id only, no front-page mention

#### Scenario: Front page id has its own instruction
- GIVEN the same step
- WHEN looking for the front page id
- THEN it points to `es_manifest_record('site', …)`

#### Scenario: Word budget holds
- GIVEN `elementor-core` measured at 588 words
- WHEN `--word-report` runs after the edit
- THEN it reports 588 or fewer, and `RT_BODY_OVER_600` does not FAIL

### Requirement: The Framework Does Not Contradict Itself About Persisted State

`agents/novamira-web-orchestrator.md` MUST NOT claim the framework persists
no state. The "Carry decisions across sessions" section (`:289-294`) MUST be
removed; its guidance already exists at House rule `:182-186` with a
verifier marker.

#### Scenario: No standing false claim
- GIVEN the orchestrator agent file
- WHEN searched for a "no state persists" claim
- THEN none is found

#### Scenario: Guidance still lives at the House rule
- GIVEN the removed section
- WHEN someone asks where the guidance lives
- THEN House rule `:182-186` states it, verifier marker intact

#### Scenario: Audit gates unaffected
- GIVEN the agent file after removal
- WHEN `framework-audit.php` runs
- THEN `RT_AGENT_NO_HOUSE_RULES` and the verifier-marker walk pass

### Requirement: A Known-False Promise About `design` Is Marked, Not Left Standing

The claim at `docs/superpowers/specs/2026-08-14-perceptual-axes-design.md:172`
— that the resolved axis is recorded via `es_manifest_record('design', …)` —
MUST carry a visible annotation marking it unfulfilled, without deleting the
sentence.

#### Scenario: Reader is warned before trusting the claim
- GIVEN line 172
- WHEN a reader reaches the `es_manifest_record('design', …)` claim
- THEN an adjacent annotation marks it unwritten; only `elementor-core` touches the manifest

#### Scenario: Original claim is preserved
- GIVEN the file after the edit
- WHEN diffed against the prior version
- THEN the sentence is unchanged; only the annotation is new

## Out of Scope

Wiring `design`/`delivery` to real writers; the `schema: 1 → 2` bump; new
`RT_` row types; new `qa-review` house-rule rows; any manifest API
behaviour change.

## Verification

Gallery build first (`_build-gallery.php`; untracked, `RT_GALLERY_NOT_BUILT`
FAILs without it), then `CONTRIBUTING.md:225`'s chain. Baseline: 1193 OK /
0 FAIL, 0 FAIL / 4 WARN / 0 JUDGE. Expected after: 1195 OK / 0 FAIL, same
audit line. `--word-report` MUST show `elementor-core` at 588 words or lower.
