# The family rule — and the honest state of it today

**The rule.** A judge is never from the model family that produced the mockup.

**Why it is not pedantry.** A model shown output from its own family rates it higher than an
outside judge does. That is a measured effect, not a worry, and it does not go away by asking the
judge to be objective — the judge does not experience it as a preference. It lands unevenly across
the two questions this skill asks:

| Question | Exposure | Why |
|---|---|---|
| Judge B: "does this look professional?" | **High** | An absolute quality verdict on generated output is exactly the shape the bias was measured on. |
| Judge A: "same hand as these others?" | Low | A comparative, structural question. Every image in the set came from the same generator, so a shared preference cancels rather than tilts. |

So if only one judge can be moved outside the family, it is B.

## What actually satisfies the rule

A judge running on a **different vendor's** model. Not a different size in the same family — the
effect is documented at the family level, so Haiku judging Opus's output is still Claude judging
Claude. Swapping tiers removes identical-model correlation, which is worth something, but it does
not satisfy this rule and must not be reported as though it did.

## What is reachable today — measured, 2026-08-28

Nothing. Checked on this machine:

- No `OPENAI_API_KEY`, `GEMINI_API_KEY`, `GOOGLE_API_KEY`, `MISTRAL_API_KEY`, `GROQ_API_KEY`,
  `TOGETHER_API_KEY` or `OPENROUTER_API_KEY` in the environment.
- No `ollama`, `llm`, `openai` or `gemini` CLI on PATH, and no local model server.
- The subagent launcher's model parameter accepts Claude models only.

So the rule cannot be satisfied here yet. That is precisely why it is written as a rule with a
named failure state rather than left out until it becomes possible: an unmet requirement that is
recorded stays visible, and one that is omitted gets rediscovered the expensive way.

## SELF-JUDGED

When no outside family is reachable, the run is labelled **SELF-JUDGED**, and that label travels
with the verdict wherever it is reported.

- SELF-JUDGED is a third state beside PASS and FAIL, in the same way `qa-review` keeps UNVERIFIED
  apart from PASS. **It never reads as a pass.**
- A SELF-JUDGED **FAIL still counts.** The bias runs toward flattery, so a judge that condemns its
  own family's output despite it is more credible than one that praises it, not less.
- Only the professional-quality answer is degraded. Judge A's grouping stands on its own, for the
  reason in the table above — say so rather than discounting the whole run.
- Do not launder it. "The judge said it looks professional" and "a judge from the same family that
  produced it said it looks professional" are different claims, and only the second is true.

## How to satisfy it when a provider appears

1. Put the provider's key in the environment.
2. Point the judges at it and record which family answered, beside the verdict.
3. Re-run the calibration pair before trusting the first real verdict — a new family is a new
   instrument, and its readings are not interchangeable with the stored descriptions until it has
   been shown to separate the pair. See `signature-schema.md` for what calibration measured.

Step 3 is not optional. Judge B's descriptions are compared across deliveries, and a silent change
of family mid-corpus makes every comparison behind it meaningless — the same failure as changing
the capture geometry, and harder to notice.
