---
name: visual-verification
description: "Trigger: mirar el render, revisar capturas, screenshot, verificacion visual, barrido visual, se ve mal, defectos de composicion, visual check, review the render, look at the page, responsive check by eye. Judge a rendered page by eye — inside a subagent, on a capture budget, sweeping every item, never dragging screenshots through the main thread."
license: Apache-2.0
metadata:
  author: "juan"
  version: "1.0"
---

# Visual Verification

`qa-review` measures what a machine can decide, then hands the visual sign-off to the user. That
gap is where the expensive defects lived: contrast, hierarchy and overflow all measured green
while grid columns wrapped to a second row, a type scale was a constant wearing a curve's
clothes, and muted text failed AA on every alternate section. Looking is a real check.

It was also the costliest thing ever run here. One catalogue cycle read 25 MB of PNG into the
main thread across 105 reads, held a 413k-token context for 3,759 turns, and outspent every
other check combined. The method was right. The place the image lived was wrong. A screenshot in
the parent context is not one cost — it is one cost per remaining turn.

## Activation Contract
Whenever a render must be judged by eye: after `html-mockup` emits, after a builder writes a
page, and inside `qa-review` step 3 in place of handing responsive checking to the user unseen.
Read-only — it renders, looks and reports; it writes nothing to WordPress.

## Hard Rules
- **A screenshot never enters the main thread.** Capture and judge inside a subagent; only the
  written verdict returns. The parent sees findings, never pixels.
  (no verifier: image reads live in the session transcript, not in this repo — measure them with `assets/measure-context.js`)
- **Sweep every item, never a sample.** Four strips of twenty-one were once read as a pass. A
  partial sweep is reported as partial, with the count seen and the count skipped.
  (no verifier: coverage is a property of the run, not of any file this repo can read)
- **Capture the viewport, not the full page.** One tall capture costs more than the three
  breakpoints worth having, and hides the fold it was supposed to prove.
  (no verifier: capture geometry is chosen at run time by the delegated looker)
- **A green measurement is half a verdict.** Contrast, overflow and Lighthouse cannot see
  composition, alignment, rhythm or proportion. Never report a visual pass from numbers alone.
  (verifier: `qa-review` step 4 already separates measured categories from what a model must judge)
- **Name the defect, not the impression.** "Se ve mal" is a prompt to look again, never a
  finding. A finding carries element, breakpoint, expected and observed.
  (no verifier: prose quality of a verdict is a judgement, and a checker would only launder it)

## Execution Steps
1. **Budget first.** Decide breakpoints (~430 / 768 / 1280) and how many items must be swept.
   Any pass over three captures is delegated, no exceptions.
2. **Delegate the looking.** One subagent per sweep: it renders, captures, reads its own images
   and returns text only — findings plus the counts seen and skipped.
3. **Sweep, then compare.** Walk every item against `references/render-defects.md`. Known
   defects are checked by name, not rediscovered.
4. **Separate measured from seen.** Keep numbers from `qa-review` and eye findings apart, so a
   green metric can never stand in for an unseen composition.
5. **Record what is new.** A defect that was not in the catalogue is appended to it, with the
   measurement that proves it. That file is the point of this skill.

## Output Contract
Findings as element + breakpoint + expected + observed, the count swept vs skipped, what was
measured rather than seen, and what still needs the user's eyes. A sweep that did not cover
everything is reported PARTIAL, never PASS.

## References
- `references/render-defects.md` — the catalogue: every defect found by looking, and the rule it
  produced.
- `assets/measure-context.js` — what a session actually cost, and its baseline context.
