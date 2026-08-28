---
name: blind-judges
description: "Trigger: juez ciego, jueces ciegos, blind judge, se parece a la anterior, todas las webs iguales, diferenciacion, misma mano, mismo estudio, objective review, judge the mockup, external judge. Two read-only judges, blind to each other and to the brief, decide whether a mockup looks like the last deliveries and whether it looks professional."
license: Apache-2.0
metadata:
  author: "juan"
  version: "1.0"
---

# Blind Judges

`qa-review` checks the build against intent. `visual-verification` looks, but is run by the actor
holding the brief and the anchor. Neither sees sameness — two identical sites pass all 31 house
rules. `framework-audit.php` names the missing evidence: "a human looking at them side by side".
These are that human's stand-in.

## Activation Contract
After `html-mockup` emits, before the build gate. That placement is the point: a "same hand"
verdict costs a re-roll there and a rebuild after. Never on a live site.

## Hard Rules
- **Blindness is a toolset, not a promise.** `blind-judge-a` and `blind-judge-b` hold Read alone
  — no shell, no browser, no search — so they cannot write, and cannot reach the source that
  declares a mockup's own axis positions in `:root` comments. Hand them image paths and nothing
  else: no brief, no client, no anchor, no toggles, no other verdict.
  (no verifier: what a launch prompt carried is decided at run time, and nothing in this repo can read it)
- **Different evidence, never the same prompt twice.** Two identical prompts on one model are one
  instrument read twice, so their agreement means nothing. A sees the corpus but not which item is
  new; B sees the current mockup and never the corpus.
  (no verifier: prompt divergence is a property of the run, not of any file this repo can read)
- **No screenshot enters the main thread.** Judging happens inside the subagent; only text returns.
  (no verifier: image reads live in the session transcript, not in this repo)
- **The orchestrator reconciles, it never breaks the tie.** It holds the brief and is the party
  being judged. A contradiction goes to the user with both verdicts verbatim.
  (no verifier: who resolved a disagreement is a property of the conversation, not of a file)
- **A verdict never triggers a patch.** "Same hand" sends the design back to `ux-design-system`.
  Art direction is the user's call, not a fix actor's.
  (no verifier: nothing inspects what happened after a verdict was reported)

## Execution Steps
1. **Capture.** `node assets/capture.mjs <page> --label <name> --out <dir>` — frozen 1280x860,
   four viewport frames plus a hero and a band shot. Judges never drive a browser: the geometry
   must be identical across deliveries or two descriptions are not comparable.
2. **Corpus.** Read `references/corpus.md`. An empty corpus skips Judge A, reported skipped.
3. **Launch `blind-judge-a` and `blind-judge-b` in parallel.** Both hold Read as their only tool,
   so blindness is enforced by the toolset rather than requested in prose. Hand each one image
   paths and nothing else. Wait for both; a partial judgment is none.
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
Both verdicts verbatim, the reconciliation row, the count swept versus skipped, and on FAIL the
tell — "all four open on a full-bleed dark hero", never "se parecen". A skipped
Judge A is reported skipped.

## References
- `references/signature-schema.md` — why the judges are shaped this way; the briefs are in `agents/`.
- `assets/capture.mjs` — the frozen capture geometry, run before either judge.
- `references/corpus.md` — the corpus, and how a delivery is recorded.
