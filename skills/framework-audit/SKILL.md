---
name: framework-audit
description: "Trigger: auditar el framework, framework audit, revisar las skills, skill drift, does this rule have a verifier, self-check NovaMira, before releasing a skill change. Audit the NovaMira framework itself — every rule has a verifier, every warning reaches a human, no skill points at a file that does not exist."
license: Apache-2.0
metadata:
  author: "juan"
  version: "1.0"
---

# Framework Audit (self-check)

Every other skill here verifies a BUILT SITE. This one verifies the framework. That gap is not
theoretical: "fewest containers" stayed violated through a whole build cycle while a correct
audit was already running, and `wordpress-seo`'s "one H1 per page" sat unenforced for versions
because no file anywhere checked it.

## Activation Contract
Before merging any change to `skills/**` or `agents/**`, before a release, and whenever a rule
is added to a `SKILL.md`. Read-only against the repo — it never writes to WordPress and needs no
connector. Fixing what it finds is a normal edit, done deliberately, not by this skill silently.

## Hard Rules
- **Run the script first, argue second.** `php tools/framework-audit.php` owns everything a
  machine can decide. Never re-check those by hand: two implementations of one rule drift, and
  the hand-rolled one loses.
- **JUDGE rows are not passes.** The script reports them precisely because it cannot decide them.
  Read each rule and answer honestly: is there really no verifier, or did the heuristic miss one?
- **Never silence a row by weakening the check.** A rule with no verifier gets one, or gets
  `(no verifier: <reason>)` written into it. Both are fine; a quietly relaxed threshold is not.
- **Report what you did not check.** Verified by this skill's own Output Contract: a report with
  no "not checked" section is incomplete, not clean.

## Execution Steps
1. `php tools/framework-audit.php` (add `--strict` to fail on WARN). Then
   `php tests/test-container-hygiene.php` — the audit code needs its own regression guard.
2. **FAIL** — objectively wrong (broken reference, missing build gate, absent frontmatter field,
   `error_log()` with no stdout channel, body past the ~600 ceiling). Fix before merging.
3. **WARN** — a human may have a reason. Decide and say which.
4. **JUDGE** — for each, either name the real verifier in the rule, or add `(no verifier:
   <reason>)`. CONTRIBUTING §3 requires the admission, not the absence.
5. **Drift the script cannot see** — read for it yourself: a `SKILL.md` describing behaviour the
   assets no longer have, an overview claiming a version that moved, a rule that contradicts
   another skill's rule, an archetype promised in a `_README` that was never written.

## Output Contract
Counts per level, every FAIL with its fix, every JUDGE with its resolution (verifier named, or
gap admitted and why), the drift found by reading, and **what you did not check**. A green script
run is not a green framework — it is a green half.

## References
- `tools/framework-audit.php` — the deterministic half. Add checks there, never in prose here.
- `CONTRIBUTING.md` §3 — the rules this audit enforces, and why each one exists.
