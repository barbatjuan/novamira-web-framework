---
name: qa-review
description: "Trigger: verify, QA, review before handoff, did it work, check the build, responsive check, accessibility check. Verify a NovaMira build against intent before the orchestrator reports done."
license: Apache-2.0
metadata:
  author: "juan"
  version: "1.0"
---

# QA Review

The gate before hand-off. Confirm the change actually landed and matches intent. Evidence
before assertions — never claim "done" without a check.

## Activation Contract
Run after any build/deploy skill and before the orchestrator reports completion. Also on
"does it work?" / "verify".

## Hard Rules
- No success claim without evidence. If a check couldn't run, say so — don't assume PASS.
- The sandbox domain is usually browser-blocked; verify SERVER-SIDE (fetch compiled CSS/HTML,
  `substr_count` the expected selectors). State plainly that the final visual sign-off is the user's.
- Report failures with the actual output, not a summary that hides them.

## Execution Steps
1. **Applied?** Fetch the target `post-<id>.css` and front HTML; count the selectors/values the
   change was supposed to add (hover transforms, `100vw`, accent `!important`, template
   `elementor-<id>`, etc.). Zero counts = not applied → investigate cache/conditions, don't report done.
2. **Responsive**: check the per-device rules exist (mobile centering, 2-col grids, header one row
   on desktop, full-width mobile CTA). Ask the user to eyeball ~430 / 768 / 1280 since you can't see it.
3. **Accessibility quick pass**: one H1, alt text on images, color contrast on text/buttons
   (ghost buttons legible in BOTH states), focus states, tap targets ≥ ~44px.
4. **Regression**: confirm nothing adjacent broke (header not wrapping, no leftover template
   hijack, kit/global CSS intact).

## Output Contract
Return a short checklist with PASS/FAIL + the evidence (grep counts) for each item, the list
of things only the user can confirm visually, and any follow-ups. If anything failed, the
orchestrator must NOT report done.
