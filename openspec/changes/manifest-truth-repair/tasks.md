# Tasks: manifest-truth-repair

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | ~120 (one function + docblock, 4 text edits, 2 test assertions) |
| 400-line budget risk | Low |
| Chained PRs recommended | No |
| Suggested split | Single PR |
| Delivery strategy | single-pr |
| Chain strategy | pending (not needed — well under budget) |

Decision needed before apply: No
Chained PRs recommended: No
Chain strategy: pending
400-line budget risk: Low

### Suggested Work Units

| Unit | Goal | Likely PR | Focused test command | Runtime harness | Rollback boundary |
|------|------|-----------|----------------------|-----------------|-------------------|
| 1 | All four scope items, RED→GREEN | PR 1 | `php tests/test-write-path.php` | `php skills/framework-audit/assets/framework-audit.php --word-report` | Each item is a one-hunk text/function revert; no schema bump, no shared state |

## Phase 1: RED — Failing Tests First (R1)
- [x] 1.1 In `tests/test-write-path.php`, insert after line 1126 (before the `/* Escribir no es haber escrito... */` comment at 1128): (a) `ok( array('site','design','pages','delivery') === es_manifest_sections(), '…' )`; (b) loop `es_manifest_sections()`, `wp_fake_reset()` per name, round-trip `es_manifest_record($s, array('probe'=>$s))` → `es_manifest_read()`, collect mismatches; `ok( 0 < $n && array() === $mal, '…' )`. Reuse `ok()`/`wp_fake_reset()`; no new file.
- [x] 1.2 Run `php tests/test-write-path.php` alone. Confirm a FATAL naming `es_manifest_sections` as undefined (not a counted FAIL). Do not proceed until observed.

## Phase 2: Front-page relocation, word-neutral (R2)
- [x] 2.1 Rewrite `skills/elementor-core/SKILL.md:68-69` step 8 to: ``8. `es_manifest_record('pages', …)` — slug → id **only**. Front page id: `'site'`. A `false` there means the next session starts blind.``
- [x] 2.2 Amend `es-builder.php:2454` and `:2458-2461` (verify docblock) to name `site` as the front-page id's home, matching the read at `:2489-2495`.
- [x] 2.3 Comment-only edit at `tests/test-write-path.php:1172-1182` aligning prose with step 8. Add/remove zero `ok()` calls — 1195 depends on this.
- [x] 2.4 Run `php skills/framework-audit/assets/framework-audit.php --word-report`; confirm `elementor-core` ≤ 588 words. Full chain still red — expected.

## Phase 3: Implement `es_manifest_sections()` (R1)
- [x] 3.1 Add the function to `es-builder.php` after `es_manifest_read()`'s closing brace (`:2414`), before the `es_manifest_record()` docblock (`:2416`). Returns exactly `array('site','design','pages','delivery')`.
- [x] 3.2 Docblock: flat list, not a writer map (writer status is established by grep, not the return value); note `pages`/`site` written by step 8, `design`/`delivery` written by nothing.
- [x] 3.3 Run the full `CONTRIBUTING.md:225` chain; expect `1195 OK / 0 FAIL`, audit line unchanged (`0 FAIL / 4 WARN / 0 JUDGE`, 15 skills + 2 agents).

## Phase 4: Citations replace restatements (R1)
- [x] 4.1 Edit `es-builder.php:2398-2401`: drop the parenthesised name list, cite `es_manifest_sections()`.
- [x] 4.2 Edit `skills/elementor-core/references/knowledge.md:52-55` to cite `es_manifest_sections()`.
- [x] 4.3 Grep-verify: `rg -n "es_manifest_sections" skills/elementor-core/references/knowledge.md` matches. Do NOT infer success from an absent `RT_HELPER_UNROUTABLE` WARN — `proposal.md`/`spec.md` already mask it.
- [x] 4.4 Re-run full chain; still `1195 OK / 0 FAIL`, audit line unchanged.

## Phase 5: Orchestrator truth repair (R3)
- [x] 5.1 Delete `agents/novamira-web-orchestrator.md:289-294` ("Carry decisions across sessions", last section).
- [x] 5.2 Re-grep all 15 project skill names (divi-core, elementor-core, elementor-theme-parts, framework-audit, html-mockup, project-context, qa-review, ux-design-system, visual-verification, web-templates, woocommerce, wordpress-forms, wordpress-legal, wordpress-performance, wordpress-seo) against the POST-deletion file — each must still have ≥1 mention elsewhere.
- [x] 5.3 Run full chain; confirm audit line still `0 FAIL / 4 WARN / 0 JUDGE` (not 5 — `RT_AGENT_SKILL_UNMENTIONED` must not fire).

## Phase 6: Axes-spec annotation (R4)
- [x] 6.1 Add a visible annotation adjacent to `docs/superpowers/specs/2026-08-14-perceptual-axes-design.md:172` marking the `es_manifest_record('design', …)` claim unfulfilled — no writer exists today. Do not delete/edit the original sentence.
- [x] 6.2 `git diff` on that file shows only an added line; line 172 byte-unchanged.

## Phase 7: Final verification + carry-forward notes
- [x] 7.1 Build gallery first: `php skills/html-mockup/assets/gallery/_build-gallery.php` (untracked; `RT_GALLERY_NOT_BUILT` else FAILs).
- [x] 7.2 Run `CONTRIBUTING.md:225` chain end to end; confirm `1195 OK / 0 FAIL`, `0 FAIL / 4 WARN / 0 JUDGE` across 15 skills + 2 agents.
- [x] 7.3 Run `--word-report`; confirm `elementor-core` ≤ 588.
- [x] 7.4 Report, do not fix: `CONTRIBUTING.md:30` says elementor-core "598" (real 588); `openspec/config.yaml:36` `measured_state` says "1164 OK" (real 1193 pre-/1195 post-change).
- [x] 7.5 Report, do not fix: `agents/novamira-web-orchestrator.md:17-18` already claims the manifest records "the design personality"/"what was approved" — nothing writes `design`. Follow-up for `manifest-design-section`.
