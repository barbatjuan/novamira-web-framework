# Tasks: Repository Weight and Branch Hygiene Remediation

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | ~35–150 authored (centre ~105); 10,685 gallery deletions + ~600 `openspec/` lines excluded (generated golden / process metadata) |
| 400-line budget risk | Low |
| Chained PRs recommended | No |
| Suggested split | Single PR |
| Delivery strategy | ask-on-risk |
| Chain strategy | pending |

Decision needed before apply: No
Chained PRs recommended: No
Chain strategy: pending
400-line budget risk: Low

### Suggested Work Units

| Unit | Goal | Likely PR | Focused test command | Runtime harness | Rollback boundary |
|------|------|-----------|----------------------|-----------------|-------------------|
| 1 | Gate, tests, untrack, docs | Single PR | `php tests/test-framework-audit.php` | `php skills/html-mockup/assets/gallery/_build-gallery.php` on a clean clone, then re-audit | Revert row/gate + test scenarios + `.gitignore`/comment edits together |
| 2 | Fast-forward, PR, CodeGraph, `openspec/` commit | Same PR (no file diff for git/gh actions) | N/A — git/gh state, not code | `gh pr view` on the opened PR | `git reset --hard 438cd27`; close PR; `git rm -r --cached openspec/`; delete `.codegraph/` |

## Phase 1: RED — Failing Tests (`tests/test-framework-audit.php`)

- [x] 1.1 Add `fx_gal_generator($root)` helper writing the `_build-gallery.php` stub, mirroring `fx_gal()` (design.md Testing Strategy).
- [x] 1.2 Scenario A: generator present, no `index.html` → assert FAIL `RT_GALLERY_NOT_BUILT`, message names the fix command, exit code `1`. (Spec: Generator present, output missing)
- [x] 1.3 Scenario B: generator + `fx_gal()` output → assert no `RT_GALLERY_NOT_BUILT` line. (Spec: Generator present, output built)
- [x] 1.4 Scenario C: neither file (`fx_base()` only) → assert no `RT_GALLERY_NOT_BUILT` line. (Spec: Generator absent)
- [x] 1.5 Run the chain; confirm Scenario A fails (RED) — row not yet registered/emitted.

**RED evidence** (`php tests/test-framework-audit.php`, 640 OK / 3 FAIL — exactly Scenario A's 3 assertions, nothing else):
```
--- the generator present without a built index.html FAILs, naming the exact fix command ---
  OK   the audit subprocess actually launched
  FAIL generator present, no index.html built -> FAIL -- actual: <0 rows matched, expected exactly 1>
  FAIL and the message names the exact fix command -- actual: framework-audit: <tmp root>\n\nRT_NO_HARD_RULES  WARN  html-mockup  no Hard Rules...\nRT_ORPHAN_FILE  WARN  html-mockup  assets/g…
  FAIL and the tree exits code 1 -- actual: 0
--- the generator present WITH a built index.html stays silent -- a built tree is not accused ---
  OK   generator + built index.html -> no RT_GALLERY_NOT_BUILT row
--- no generator at all never produces RT_GALLERY_NOT_BUILT -- the gate never fires in a bare fixture root ---
  OK   no generator, no index.html -> still no row: the gate is conditioned on the generator
```
Confirms: row not registered/emitted yet (Scenario A red for the right reason), Scenarios B/C already green (nothing to gate yet), and the generator stub incidentally raises `RT_ORPHAN_FILE` (WARN) exactly as design.md predicted.

## Phase 2: GREEN — Gate Implementation (`skills/framework-audit/assets/framework-audit.php`)

- [x] 2.1 Register `RT_GALLERY_NOT_BUILT` in `ROW_TYPES` (~:116, beside the three gallery rows).
- [x] 2.2 Insert the gate block before `$gallery_manifests_seen = array();` (:2595) per design.md's exact snippet.
- [x] 2.3 Run the full chain; confirm Scenarios A–C pass, coverage assertion (:4480) satisfied, `r200` fixture (:3348) unaffected.

**GREEN evidence** (`php tests/test-framework-audit.php`): `643 OK / 0 FAIL` (was 640 OK / 3 FAIL pre-gate). Scenario A/B/C all `OK`, coverage assertion `OK` ("every row type ROW_TYPES declares is produced by at least one fixture above"), r200 (`una galeria conforme no produce fila de galeria`) still `OK`.

Note: running the FULL 5-command chain (`test_command`) at this point — not just `test-framework-audit.php` — legitimately FAILs at step 1 (`framework-audit.php` self-auditing this repo) with `RT_ROWTYPE_UNDOCUMENTED` for the new ID, because `CONTRIBUTING.md`'s row table does not document it yet. This is expected and by design (design.md: "row + tests first (green), then untrack"; docs land in Phase 3). Full-chain 0-FAIL confirmation happens after Phase 3.

## Phase 3: Bootstrap Documentation

- [x] 3.1 `CONTRIBUTING.md`: add row-table entry for `RT_GALLERY_NOT_BUILT` after `RT_GALLERY_ONE_SHOOT`.
- [x] 3.2 `CONTRIBUTING.md` `## Testing a change`: add bootstrap step above the `&&` chain (:225); exact wording in design.md.
- [x] 3.3 `README.md` `## Install`: add bootstrap step after the install-script paragraph (:79–82), before the test step.

**Evidence**: `php skills/framework-audit/assets/framework-audit.php` self-audit on the real repo now `0 FAIL / 4 WARN / 0 JUDGE across 15 skills + 2 agent(s)` — `RT_ROWTYPE_UNDOCUMENTED` cleared (was 1 FAIL before this phase), `RT_GALLERY_NOT_BUILT` silent (real `index.html` still tracked/present at this point, gate correctly inactive). Matches proposal's documented baseline exactly.

## Phase 4: Untrack Gallery Artifact

- [x] 4.1 `.gitignore`: add anchored entries `/skills/html-mockup/assets/gallery/index.html` and `.codegraph/`.
- [x] 4.2 `.../gallery/_build-gallery.php:6`: correct comment from "committed alongside this file" to reflect untracked/generated status.
- [x] 4.3 `git rm --cached skills/html-mockup/assets/gallery/index.html`; verify `git ls-files` omits it and the file remains on disk.

**Evidence**: `git status --short` shows `D  skills/html-mockup/assets/gallery/index.html` (staged removal from index) and `M .../gallery/_build-gallery.php`; `git ls-files -- .../index.html` returns empty; `test -f .../index.html` confirms the file is still present on disk.

## Phase 5: CodeGraph & SDD Scaffolding

- [x] 5.1 Run `codegraph init` at repo root; confirm `.codegraph/` exists and is gitignored by 4.1.
- [x] 5.2 Commit `openspec/` scaffolding (`config.yaml`, `changes/context-cost-remediation/*`) — process metadata, excluded from authored-risk count.

**Evidence**: `codegraph init` → "Indexed 16 files — 465 nodes, 7,053 edges". `git check-ignore -v .codegraph/` → matched by `.gitignore:31:.codegraph/`.

## Phase 6: Branch Hygiene & PR

- [x] 6.1 `git fetch --prune`; fast-forward local `main` to `origin/main` (`2fd9438`).
- [x] 6.2 Open an ordinary PR `feat/visual-verification` → `main` (`63a975b` + `c01c17f`) via `gh pr create`; body records all seven branch verdicts from proposal.md; do not merge.

**Evidence**: `git fetch --prune` re-confirmed all seven verdicts unchanged (5 merged, `feat/template-gallery` 1 ahead/0 behind, `feat/visual-verification` 2 ahead/0 behind); `git fetch origin main:main` fast-forwarded local `main` `438cd27..2fd9438` (verified `git rev-parse main` == `git rev-parse origin/main`). Full verdict detail recorded in `openspec/changes/context-cost-remediation/branch-verdicts.md`. PR opened: https://github.com/barbatjuan/novamira-web-framework/pull/12 (`state: OPEN`, base `main`, head `feat/visual-verification`, commits `63a975b` + `c01c17f`, body states the visual-audit gate and references the verdict file). **Not merged.**

## Phase 7: Clean-Clone Verification

- [x] 7.1 On a clean clone (gallery unbuilt), run the full chain; assert both `RT_GALLERY_NOT_BUILT` and `RT_BROKEN_REFERENCE` (`ux-design-system/SKILL.md:43`) rows fire, matched by row type, not total count.
- [x] 7.2 Run the build command on that clone; re-run the chain; confirm both rows clear and the chain is 0 FAIL.
- [x] 7.3 Confirm `.git` does not grow across a gallery regeneration cycle (rebuilt `index.html` stays untracked).

**Evidence**: local clone of `feat/sdd-remediation` (`git log -1` → `95fefac`) into a scratch directory. Unbuilt state: `index.html` absent, `_build-gallery.php` present, `.git` 156M. Self-audit → `2 FAIL / 4 WARN`, matched by row type: `RT_GALLERY_NOT_BUILT` (names the fix command) and `RT_BROKEN_REFERENCE` (`ux-design-system` pointing at `html-mockup/assets/gallery/index.html`, which does not exist) — both predicted by design.md, no other row changed. Ran `php skills/html-mockup/assets/gallery/_build-gallery.php` → `index.html` regenerated, 10,685 lines. Re-ran full chain: `0 FAIL / 4 WARN` self-audit, `81/643/22/426 OK / 0 FAIL` across the four test suites — both target rows absent (`rg` for either returns nothing). Ran the generator a second time (regeneration cycle): `.git` still 156M (unchanged), `git status --short` empty (rebuilt file stays both untracked and ignored, not even listed as untracked).
