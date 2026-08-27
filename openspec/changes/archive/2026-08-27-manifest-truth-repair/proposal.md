# Proposal: manifest-truth-repair

## Intent

Three statements this repo makes about persisted state are false, and one has already
corrupted a live WordPress manifest. `elementor-core/SKILL.md:68` tells the model to write
the front page into the `pages` slug→id map; `es_manifest_verify()` then reports it as page
drift and `qa-review` house-rule row 24 FAILs a build over a bug in an instruction sentence.
Repair the record before four later slices are built on top of it.

## Scope

### In Scope

1. **Split `pages` from `site`.** `pages` carries slug→id only; the front page id lives in
   `site`, which is exactly where `es_manifest_verify()` already reads it. Correct all three
   places the wrong version is recorded: the instruction, the verify docblock, the regression
   test. The `SKILL.md` edit is **word-neutral or negative** — `elementor-core` measures 588
   instruction words against a 600 FAIL ceiling, so rewrite in place, never append.
2. **Delete the orchestrator's "Carry decisions across sessions" section.** Its opener
   ("nothing in this framework persists state") contradicts the manifest described 265 lines
   above it, and its surviving content duplicates House rule `:182-186`, which already carries
   a resolving verifier marker. Agents carry no word budget: free net reduction.
3. **Add `es_manifest_sections()`** returning `site, design, pages, delivery`, docblocked with
   who writes each one *today*: `pages` → `elementor-core` step 8; `site` → the front page id,
   read by `es_manifest_verify()`; `design` and `delivery` → nothing yet, named here so the gap
   is a fact a script can read instead of a docblock claim. Point the two prose copies of the
   list at the function. Name the helper in `knowledge.md` or `RT_HELPER_UNROUTABLE` WARNs.
4. **Annotate the false axes claim**, marking it as the promise a later slice makes true.
   Annotate — do not silently delete.

### Out of Scope

- Wiring `design` or `delivery` to real writers; the `schema:1 → 2` bump
- Any new `RT_` row type; any new `qa-review` house-rule row
- Any behaviour change to `es_manifest_read` / `es_manifest_record` / `es_manifest_verify`
- Anything from the later roadmap slices

## Capabilities

### New Capabilities
- `manifest-section-contract`: the four manifest sections, machine-readable, plus which of them
  has a writer today and which is a declared gap.

### Modified Capabilities
- None. No `SKILL.md` behaviour contract changes; `gallery-bootstrap-integrity` is untouched.

## Approach

Purely subtractive plus one anchor function. No new row types, no schema change, no new test
file, no behaviour change to the manifest API.

**TDD (`strict_tdd: true`)** — two assertions added to the *existing* manifest block of
`tests/test-write-path.php` (~`:1119-1128`). No new test file, so the `CONTRIBUTING.md:225`
gate line and `RT_GATE_LINE_UNREGISTERED` stay untouched. Red (the function does not exist)
first, then green:
- `es_manifest_sections()` returns exactly the four names, in order.
- Every name it returns is a key `es_manifest_record()` accepts and `es_manifest_read()`
  round-trips.

**Decision of record, not reopened here: pre-build-gate state is backfilled at the gate.**
The manifest is a WordPress option, so on greenfield nothing before the build gate can write to
it — site type, TPL, toggles, resolved axes and the mockup approval all happen before a store
exists. The orchestrator carries them in conversation and writes `design` in one call right
after `project-context` confirms the connector. No new persistence surface. That is a later
slice; here it is context only.

**Unblocks, in this order:** `manifest-design-section` (closes open MAJOR finding 25 — the
resolved architecture, the entire product of `web-templates`, has no verifier),
`manifest-delivery-section`, `intake-and-capabilities`, and `manifest-section-gate` (the
`RT_MANIFEST_SECTION_DEAD` row, which MUST ship **last** or it lands red on main).

**Delivery:** ~120 changed lines against a 400-line review budget → `single-pr` applies, no
chaining needed.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `skills/elementor-core/SKILL.md:68` | Modified | Step 8 splits: `pages` slug→id only; front page id → `site`. Word-neutral or negative. |
| `skills/elementor-core/assets/es-builder.php:2447-2467` | Modified | `es_manifest_verify()` docblock stops recording the corrupt reading as fact. |
| `skills/elementor-core/assets/es-builder.php` (new helper) | New | `es_manifest_sections()` + docblock naming each section's writer today. |
| `skills/elementor-core/assets/es-builder.php:2398-2401` | Modified | Shape docblock cites the function instead of restating the list. |
| `skills/elementor-core/references/knowledge.md:52-55` | Modified | Same, and names the helper so it stays routable. |
| `tests/test-write-path.php:1172-1182` | Modified | Regression test asserts the corrected reading, not both. |
| `tests/test-write-path.php:~1119-1128` | Modified | Two added assertions (existing block, no new file). |
| `agents/novamira-web-orchestrator.md:289-294` | Removed | Whole "Carry decisions across sessions" section. House rules block untouched. |
| `docs/superpowers/specs/2026-08-14-perceptual-axes-design.md:172` | Modified | False claim annotated as a later slice's promise. |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Item 1 adds words and pushes `elementor-core` past 600 (`RT_BODY_OVER_600` FAILs) | Med | Rewrite in place; `--word-report` must still read ≤ 588. Any change in the WARN count means the edit was not word-neutral. |
| Deleting the agent section trips `RT_AGENT_NO_HOUSE_RULES` or the marker walk | Low | The `## House rules` block is not touched; re-run the audit and the marker walk. |
| The new helper is unreachable from any `.md` → `RT_HELPER_UNROUTABLE` WARN | Med | Name it in `references/knowledge.md`, which is already reachable. |
| **`openspec/config.yaml` `measured_state` is stale** — records 1164 OK measured 2026-08-24; the real number today is 1193 | High (already true) | **Reported, not fixed here.** Rewriting it is out of scope for this change and needs its own decision. |
| `CONTRIBUTING.md:30` quotes `elementor-core` at 598 words; the measured value is 588 | Low | Same class of staleness. Out of scope; the paragraph itself tells the reader to re-measure. |

## Rollback Plan

Every item is a **single-hunk revert** — no item depends on another landing first.

- Items 1, 2 and 4 are text reverts of one hunk each.
- Item 3: `es_manifest_sections()` is purely additive and is called by nothing that would break
  on its removal. Reverting it means dropping the function, the two docblock/reference edits
  that cite it, and the two test assertions that cover it.
- No data migration, no schema bump, no change to the persisted manifest format — a revert
  restores byte-identical runtime behaviour, and any manifest written in between stays valid.

## Dependencies

- Build the generated gallery before auditing: `php skills/html-mockup/assets/gallery/_build-gallery.php`
  (`index.html` is untracked; `RT_GALLERY_NOT_BUILT` FAILs without it).
- Baseline, measured on this branch 2026-08-27 — do not re-measure: **0 FAIL / 4 WARN / 0 JUDGE
  across 15 skills + 2 agents**; `81 + 664 + 22 + 426 = ` **1193 OK / 0 FAIL**.

## Success Criteria

- [ ] Audit line unchanged: 0 FAIL / 4 WARN / 0 JUDGE across 15 skills + 2 agents
- [ ] Documented chain reports **1195 OK / 0 FAIL** (1193 + the two new assertions)
- [ ] `--word-report` shows `elementor-core` at **588 or lower**, WARN count still 4
- [ ] No instruction, docblock or test anywhere states that the front page belongs in `pages`
- [ ] `es_manifest_sections()` exists, is named from a reachable `.md`, and round-trips through
      `es_manifest_record()` / `es_manifest_read()` for all four names
- [ ] The axes-spec claim is marked as a promise, not deleted
