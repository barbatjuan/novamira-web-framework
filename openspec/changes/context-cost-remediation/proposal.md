# Proposal: Repository Weight and Branch Hygiene Remediation

> **Change id note**: `context-cost-remediation` is semantically stale — the context-cost findings that named it were already remediated in a prior session and are out of scope here; the real scope is repo weight and branch hygiene. Kept deliberately, to avoid desyncing orchestrator state for no functional gain.

## Intent

`.git` is 157 MB and grows ~7 MB per gallery session because a generated 9,075,261-byte artifact (10,685 lines, 59 revisions, blobs to 8.6 MB) is version-tracked. Separately, the remote branch set has drifted and was last read from stale refs, and the repo has no CodeGraph index and no committed SDD scaffolding. Stop the growth, record an accurate branch picture, install the tooling.

## Resolved Decisions

All open questions are answered. These are decisions, not assumptions.

| # | Decision | Reasoning to preserve |
|---|----------|----------------------|
| 1 | **Gallery counts as a generated golden**, not authored risk | Nobody authored the file — `_build-gallery.php` emits it, and byte-identical reconstruction is already demonstrated (`run1 == run2`, verified in prior sessions). Counting 10,685 generated deletions as authored would record a *false measurement* of this change's review burden, which is genuinely 10–20 hand-written lines. A `size:exception` would assert "we exceeded the budget" when the authored change does not. Chosen because it is **accurate**, not because it is convenient. Retained in snapshot identity; excluded from the authored-risk count |
| 2 | **Add a documented bootstrap step** (scope addition) | A fresh clone must never be able to silently lack the gallery. See Approach §Bootstrap |
| 3 | **Verdict only — delete no branch** | Verdicts are the deliverable. `0 ahead` describes tip position, not merge status |
| 4 | **Ordinary PR — the landing-PR framing is withdrawn** | The "one landing PR for 72 unreviewable commits" answer was given under a **premise that proved false** (see below). There are not 72 unmerged commits; there are 2. A landing PR is no longer needed and would misrepresent the work. The user's standing rule is untouched: **no merge to `main` without their own visual audit** |

## Correction: the branch picture was read from stale refs

The original item 2 claimed `feat/template-gallery` held the template catalogue 72 commits outside `main`. **That was wrong**, and the error was the orchestrator's: the audit read remote-tracking refs without running `git fetch` first. Measured after `git fetch --prune`:

- `origin/main` is at `2fd9438`, **71 commits ahead** of the stale `438cd27`. It advanced by **direct push, not a PR** — `git log --first-parent origin/main` walks straight through `2fd9438 / 0fa69f6 / 9811c3a / 71119e1`, and `gh pr list` still tops out at PR #11 (merged 2026-08-15). There is no PR #12.
- **`main` already contains the whole template catalogue** — the 22 architectures, internal pages, unit/property sheets, ecommerce verticals. Nothing needs "landing".
- `origin/feat/template-gallery` is **1 commit ahead**, 0 behind: `63a975b`.
- `origin/feat/visual-verification` is **2 commits ahead**: `63a975b` + `c01c17f`. It is a **strict superset** of `feat/template-gallery`.
- Local `main` is **71 behind** `origin/main` and needs a fast-forward.
- Remote branches are now **8, not 15**. `fix/review-findings` and `fix/theme-part-followups` were deleted upstream by someone else during this session.

**Method note, and the lesson**: verdicts below come from `git branch -r --merged origin/main` run **after** a fetch. A stale ref is exactly what produced the original wrong number, so any future branch audit MUST fetch first.

### Per-branch merge verdicts (satisfies the item-2 verification requirement)

| Branch | Verdict | Action |
|--------|---------|--------|
| `origin/feat/design-personalities-catalog` | Merged into `origin/main` | None — record only |
| `origin/feat/mockup-starts-from-catalog` | Merged | None — record only |
| `origin/feat/web-templates-mockup` | Merged | None — record only |
| `origin/fix/audit-truthfulness` | Merged | None — record only |
| `origin/fix/blockers-audit-2026-08-10` | Merged | None — record only |
| `origin/feat/template-gallery` | **Unmerged**, 1 ahead | Redundant as a vehicle — superseded by `feat/visual-verification` |
| `origin/feat/visual-verification` | **Unmerged**, 2 ahead | PR vehicle |

Five branches are confirmed deletion candidates. **None is deleted this cycle** (decision 3).

## Scope

### In Scope

1. Untrack `skills/html-mockup/assets/gallery/index.html` — add to `.gitignore`, `git rm --cached` (file stays on disk). Correct `_build-gallery.php:6`, which states the output is "committed alongside this file".
2. **Bootstrap step + its verifier** (see Approach).
3. Fast-forward local `main` to `origin/main`. Open an **ordinary PR** from `feat/visual-verification` to `main` carrying `63a975b` + `c01c17f`. Record the seven branch verdicts above.
4. Run `codegraph init`; add `.codegraph/` to `.gitignore`; commit the `openspec/` scaffolding.

### Out of Scope

- **History rewrite — decided against by the user.** No `filter-repo`, no BFG, no force-push. The 157 MB stays; it stops growing. Not offered as an alternative.
- Merging the PR. The user's own visual audit gates every merge to `main`.
- Deleting any branch, including the five confirmed-merged candidates and the now-redundant `feat/template-gallery`.
- **`skills/html-mockup/assets/gallery/img/` — 5.2 MB across 100 webp files** (measured). Recorded as a **follow-up**, deliberately excluded: unlike `index.html` it is *source*, not generated output, so untracking it would delete information no generator can rebuild.
- The already-completed context-cost baseline pruning and the `visual-verification` skill itself.

## Capabilities

### New Capabilities

None — no skill behavior changes.

### Modified Capabilities

None — `openspec/specs/` holds only `.gitkeep`, and `config.yaml` `rules.archive` states the detracking is not a spec delta. The new audit row is an internal gate, not a skill contract.

## Approach

Treat the gallery as what its own generator already calls it: reproducible output. `_build-gallery.php` asserts byte-identical rebuilds (no clock, no randomness, sorted manifest), so the tracked copy carries no information the generator does not. Untrack it, keep the generator as the source of truth. CodeGraph and SDD scaffolding are additive and gitignored or inert.

### Bootstrap (decision 2)

**Placement — both files, verified against this repo's conventions:**

- `README.md` `## Install` — the post-clone landing spot. It already carries "Run one: `install.ps1` / `install.sh`" and "Re-run to update after `git pull`", so a generate step belongs beside them, and MUST be ordered *before* the test step.
- `CONTRIBUTING.md` `## Testing a change` — a contributor runs the audit chain from here, and gallery rows are only meaningful once the artifact exists.

**Verifier — warranted. Reason, in the `(verifier: ...)` spirit of CONTRIBUTING §3:**

The untracking *creates* a new silent-failure mode. Before it, a clone could not lack the gallery. After it, a clone can, and today that is invisible: framework-audit discovers gallery assets by glob, so an absent file emits **no row at all** (verified — contrast `$PROOF_MOCKUPS` at `framework-audit.php:1765`). The framework has already solved this exact problem once, and `framework-audit.php:1762–1764` states the principle in its own words: *"a missing proof file must FAIL rather than silently skip the check. 'The gate passes because the evidence is gone' is the failure mode these rows exist to make impossible."* A documented bootstrap step with no verifier would be a rule enforced by memory.

**Proposed shape** — new audit row, working name `RT_GALLERY_NOT_BUILT`, FAIL:

- Fires when `_build-gallery.php` is present in the audited root **but its `index.html` output is not**.
- **Gated on the generator's presence, deliberately.** An unconditional must-exist assertion would fire inside every temp fixture root in `tests/test-framework-audit.php`. Verified: `fx_gal()` (line 3341) writes `index.html` and optionally the manifest, and **never** the generator — so the gate leaves all existing fixtures untouched. An unconditional row would have broken large parts of the suite.
- The message MUST name the exact fix: `php skills/html-mockup/assets/gallery/_build-gallery.php`. A FAIL that names its own remedy is guidance; one that does not is noise.
- Documented as a row in the `CONTRIBUTING.md` row table, per existing convention.

**Test-file authorization (explicit, per `config.yaml` `rules.apply`)**: this proposal explicitly authorizes editing `tests/test-framework-audit.php`, which is otherwise forbidden. `strict_tdd: true` applies — the failing test lands first. **No new test file is created**, so the CONTRIBUTING `&&`-chain requirement does not bite: `tests/test-framework-audit.php` is already in the chain.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `.gitignore` | Modified | +2 entries: gallery `index.html`, `.codegraph/` |
| `skills/html-mockup/assets/gallery/index.html` | Removed from index | Untracked; file remains on disk |
| `.../gallery/_build-gallery.php` | Modified | Header comment line 6 no longer true |
| `README.md` | Modified | Bootstrap step in `## Install`, before the test step |
| `CONTRIBUTING.md` | Modified | Bootstrap step in `## Testing a change`; new row in the row table |
| `skills/framework-audit/assets/framework-audit.php` | Modified | New `RT_GALLERY_NOT_BUILT` row |
| `tests/test-framework-audit.php` | Modified | **Explicitly authorized**; RED first |
| `skills/html-mockup/SKILL.md` | **No change** | Lines 70–71 already state "Regenerate via `_build-gallery.php`; never hand-edit its `index.html`" — the documentation exists, and `rules.apply` forbids editing a SKILL.md without an explicit task |
| `openspec/` | New | `config.yaml` + scaffolding, currently untracked |
| local `main` | Fast-forward | 71 behind `origin/main` |
| `feat/visual-verification` | PR opened | 2 commits, not merged |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| **NEW** — the verifier makes a fresh clone's audit FAIL until the generator runs | High (by design) | Intentional: loud beats silent. Contained by naming the exact command in the FAIL message and ordering README so the build step precedes the test step |
| **NEW** — an unconditional must-exist row would fire in every test fixture root | Med | Neutralized by gating on generator presence; verified against `fx_gal()` at `test-framework-audit.php:3341`. A design constraint, not an optional refinement |
| **NEW** — budget margin narrows from comfortable to thin | Med | `sdd-tasks` MUST re-forecast after design; this repo's house style writes long justification comments and the audit row could land above estimate |
| Branch audits read from stale refs | **Was High, now realized once and corrected** | Already happened and produced a wrong 72-commit figure. Permanent mitigation: `git fetch --prune` before any branch verdict, recorded above as method |
| A fresh clone has no gallery | Med → Low | Now detected rather than silent. **Still unverified**: `RT_ORPHAN_FILE` behaviour for `img/` and `_gallery-images.md` once nothing references them, and the `ux-design-system/SKILL.md:43` pointer. Apply MUST run the full chain against a clean clone |
| ~~72 commits exceed any sane review~~ | **Withdrawn** | Premise false. The PR is 2 commits and is ordinarily reviewable |
| Unreviewed PR volume on `main` | Low | Observation, not a scope item: `origin/main` advanced 71 commits by direct push with no PR, contrary to `CONTRIBUTING.md` `## Workflow`. Recorded as a follow-up |
| `main` diverges while the PR waits on the visual audit | Low | 2-commit PR; rebase is cheap |

## Rollback Plan

1. **Gallery**: `git revert` the untracking commit, or `git add -f skills/html-mockup/assets/gallery/index.html` and drop the `.gitignore` entry. Because no history is rewritten, every historical blob stays reachable — rollback is total and lossless.
2. **Verifier**: revert the `framework-audit.php` row and its tests together. The row is additive and referenced by nothing else, so removal cannot orphan a caller.
3. **Bootstrap docs**: revert the README/CONTRIBUTING edits. Documentation-only, no runtime effect.
4. **PR**: close it. No merge occurred, so `main` is untouched.
5. **Local `main` fast-forward**: `git reset --hard 438cd27` restores the prior local tip. A fast-forward destroys nothing, and `origin/main` is unaffected either way.
6. **CodeGraph**: delete `.codegraph/`. Gitignored and fully derived.
7. **openspec/**: `git rm -r --cached openspec/`. No runtime consumes it.

## Dependencies

- `codegraph` CLI on PATH (unverified).
- `gh` authenticated for PR creation (verified working — `gh pr list` was read during the branch audit).
- PHP 8.2 CLI for `_build-gallery.php` and the audit chain (verified in `config.yaml`).

## Success Criteria

- [ ] `git ls-files` no longer lists the gallery `index.html`; the file still exists on disk.
- [ ] `.git` stops growing across a gallery regeneration cycle.
- [ ] Full chain from `rules.verify` passes: 1164 OK + the new tests, 0 FAIL; audit 0 FAIL / 4 WARN.
- [ ] On a clean clone with the gallery absent, the audit **FAILs with `RT_GALLERY_NOT_BUILT`** and names the regeneration command.
- [ ] After running that command on the clean clone, the full chain passes.
- [ ] Bootstrap step is discoverable in both `README.md` `## Install` and `CONTRIBUTING.md` `## Testing a change`.
- [ ] Local `main` equals `origin/main` (`2fd9438`).
- [ ] A PR from `feat/visual-verification` to `main` is open and unmerged.
- [ ] All seven branch verdicts are recorded; **no branch deleted**.
- [ ] `.codegraph/` exists and is gitignored.
- [ ] Git history unchanged — `git rev-list --count origin/main` matches its pre-change value.

## Review Budget Forecast

`review_budget_lines: 400`.

| Bucket | Lines | Counted? |
|--------|-------|----------|
| `.gitignore`, `_build-gallery.php` comment | ~10–20 | Authored |
| Bootstrap docs (README, CONTRIBUTING + row) | ~10–20 | Authored |
| `RT_GALLERY_NOT_BUILT` + tests | ~55–110 | Authored |
| `openspec/` scaffolding | ~80–150 | Authored |
| Gallery untrack | 10,685 deletions | **Generated golden — excluded** (decision 1) |
| Item 3 (fast-forward, PR, verdicts) | 0 | No file changes in this repo |

**Authored total: ~155–300 of 400.**

**Verdict: fits one PR.** The generated-golden classification is decided, so no `size:exception` is needed. Comfortable at the low estimate, **thin at the high** — the `RT_GALLERY_NOT_BUILT` row is the only volatile line item, because this repo's house style writes long justification comments. `sdd-tasks` MUST re-forecast after design and split the verifier into a second PR if the row lands above estimate. The `feat/visual-verification` PR is a separate 2-commit review and is not counted against this change's budget.
