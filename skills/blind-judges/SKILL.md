---
name: blind-judges
description: "Trigger: juez ciego, jueces ciegos, blind judge, se parece a la anterior, todas las webs iguales, diferenciacion, misma mano, mismo estudio, objective review, judge the mockup, external judge. Two read-only judges, blind to each other and to the brief, decide whether a mockup looks like the last deliveries and whether it looks professional."
license: Apache-2.0
metadata:
  author: "juan"
  version: "1.1"
---

# Blind Judges

`qa-review` checks against intent; `visual-verification` is run by the actor holding the brief.
Neither sees sameness — two identical sites pass all 31 house rules. `framework-audit.php` names
the missing evidence: "a human looking at them side by side". These are that stand-in.

## Hard Rules
- **Blindness is a toolset, not a promise.** `blind-judge-a` and `blind-judge-b` hold Read alone
  — no shell, no browser, no search — so they cannot write, and cannot reach the source that
  declares a mockup's axis positions in `:root` comments. Hand them image paths, nothing else.
  (no verifier: what a launch prompt carried is decided at run time, and nothing in this repo can read it)
- **Never the family that made the mockup.** A model shown its own family's output rates it higher.
  That is measured, and lands hardest on "does this look professional". With no outside family
  reachable the verdict is SELF-JUDGED — never counted as a pass.
  (no verifier: which family answered is chosen when the judge is launched, and no file here can read that)
- **Different evidence, never the same prompt twice.** Two identical prompts on one model are one
  instrument read twice. A sees the corpus but not which is new; B sees the mockup, never the corpus.
  (no verifier: prompt divergence is a property of the run, not of any file this repo can read)
- **No screenshot enters the main thread.** Judging happens inside the subagent; only text returns.
  (no verifier: image reads live in the session transcript, not in this repo)
- **The orchestrator reconciles, it never breaks the tie.** It holds the brief and is the party
  being judged; a contradiction goes to the user verbatim.
  (no verifier: who resolved a disagreement is a property of the conversation, not of a file)
- **A verdict never triggers a patch.** "Same hand" sends the design back to `ux-design-system` —
  art direction is the user's call.
  (no verifier: nothing inspects what happened after a verdict was reported)

## Activation Contract
After `html-mockup` emits, before the build gate: a "same hand" verdict costs a re-roll there and
a rebuild after. Never on a live site.

## Execution Steps
1. **Capture.** `node assets/capture.mjs <page> --label <name> --out <dir>` — frozen 1280x860, one
   frame per screen, plus hero, band and tail. Judges never drive a browser; identical geometry
   makes two descriptions comparable.
2. **Choose the family** — outside the one that made the mockup, or declare SELF-JUDGED up front
   (`references/judge-independence.md`). Read `references/corpus.md`; an empty corpus skips Judge A.
3. **Launch `blind-judge-a` and `blind-judge-b` in parallel.** Hand each image paths and nothing
   else. Wait for both; a partial judgment is none.
4. **A** gets corpus shots plus the current one, unlabeled and shuffled. **B** gets the current
   page only. Both briefs live in the agent files.
5. **Reconcile** B's description against the stored ones, then against A's grouping:

| Judge A | Judge B vs stored | Result |
|---|---|---|
| different hand | distinct | PASS |
| same hand | matches a recent delivery | FAIL — name the tell |
| the two disagree | — | ESCALATE to the user |

6. **Record.** The orchestrator appends B's description and three thumbnails.

## Output Contract
Both verdicts verbatim, the judge family and whether the run was SELF-JUDGED, the reconciliation
row, the count swept versus skipped, and on FAIL the tell in concrete terms. A skipped Judge A
reads as skipped; SELF-JUDGED never reads as PASS.

## References
- `references/judge-independence.md` — the family rule and what SELF-JUDGED costs.
- `references/signature-schema.md` — why the judges are shaped this way; briefs in `agents/`.
- `assets/capture.mjs` — the frozen capture geometry.
- `references/corpus.md` — the corpus, and how a delivery is recorded.
