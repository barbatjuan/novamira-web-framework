# Branch Verdicts — Repository Weight and Branch Hygiene Remediation

Measured after `git fetch --prune` (method note: an earlier read of these same branches, taken
from stale remote-tracking refs without a prior fetch, produced a wrong figure — see
`proposal.md` § "Correction: the branch picture was read from stale refs". Any future branch
audit MUST fetch first).

- `origin/main` at `2fd9438`, local `main` fast-forwarded to match.
- Remote branches: 8 (not 15 — `fix/review-findings` and `fix/theme-part-followups` were deleted
  upstream by someone else before this cycle).
- `origin/main` advanced 71 commits (`438cd27..2fd9438`) by **direct push, not a PR** —
  `gh pr list` tops out at PR #11 (merged 2026-08-15). Recorded as an observation
  (`CONTRIBUTING.md` `## Workflow` says this should not happen), not fixed in this change.

## Verdicts (`git branch -r --merged origin/main` / `--no-merged`, run after fetch)

| # | Branch | Verdict | Ahead / Behind `origin/main` | Action |
|---|--------|---------|-------------------------------|--------|
| 1 | `origin/feat/design-personalities-catalog` | Merged into `origin/main` | — | None — record only |
| 2 | `origin/feat/mockup-starts-from-catalog` | Merged | — | None — record only |
| 3 | `origin/feat/web-templates-mockup` | Merged | — | None — record only |
| 4 | `origin/fix/audit-truthfulness` | Merged | — | None — record only |
| 5 | `origin/fix/blockers-audit-2026-08-10` | Merged | — | None — record only |
| 6 | `origin/feat/template-gallery` | **Unmerged** | 1 ahead, 0 behind | Redundant vehicle — `feat/visual-verification` is a strict superset (`63a975b`) |
| 7 | `origin/feat/visual-verification` | **Unmerged** | 2 ahead, 0 behind | PR vehicle for this cycle — carries `63a975b` + `c01c17f` |

**No branch is deleted this cycle.** Five branches (#1–5) are confirmed merge candidates for a
future cleanup pass; `origin/feat/template-gallery` (#6) is a redundant, superseded vehicle.
Deletion is out of scope here — this file records verdicts, it does not act on them.

## Reproduction

```bash
git fetch --prune
git branch -r --merged origin/main
git branch -r --no-merged origin/main
git rev-list --left-right --count origin/main...origin/<branch>
```
