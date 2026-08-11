# Contributing

The point of this repo is to get better every project. Two rules keep it healthy.

## 1. Gotchas are the gold
When a build surprises you, capture it in the right `skills/<skill>/references/gotchas.md`
in this exact shape:

```
## <short title>
Symptom: <what you saw>
Cause: <verified reason>
Fix: <what worked>
Do NOT: <the trap>
```

Only add CONFIRMED findings. "Probably" belongs in a PR discussion, not in gotchas.

## 2. Keep the shape
- `SKILL.md` body stays concise: **aim ~300 words, hard ceiling ~600**. Detail → `references/`.
  Code → `assets/`. The body is what loads on activation, so every word is a tax on every run.
  (The old "~180–450 tokens" was aspirational and never held — half the skills were already past
  it. Measure with `wc -w`, not by eye. `html-mockup` and `web-templates` are over the ceiling
  today and should shed detail into `references/` next time they are touched.)
- Frontmatter needs `name`, `description` (with trigger words first), `license`,
  `metadata.author`, `metadata.version`.
- The orchestrator never gains CSS/HTML/PHP. Execution lives in skills.
- Builder-agnostic knowledge goes in `ux-design-system`; builder-specific execution in
  `elementor-core` / `divi-core`.

## 3. Every rule names its verifier, and every warning reaches a human

The "fewest containers" rule survived a whole build cycle being violated even though a correct
audit was already running. Two causes, both cheap to prevent, both easy to repeat in the next
skill. Treat these as review questions on any PR that adds a rule:

- **A rule with no verifier is a wish.** When you add a Hard Rule to a `SKILL.md`, say in the
  same breath WHAT CHECKS IT: a helper that makes the violation hard to express, a check in the
  write path, or a row in `qa-review/references/house-rules.md` with a server-side method. If the
  honest answer is "nothing checks it", write that down — an admitted gap gets fixed, a silent
  one does not. (`wordpress-seo`'s "one H1 per page" sat unenforced this way until it became
  house-rule row 12.)
- **A warning only in `error_log()` is a warning nobody reads.** The sandbox returns STDOUT;
  the server's PHP log is never fetched. Route every warning through `es_warn()` (or echo
  alongside `error_log()` where the helper library may not be loaded). This is not cosmetic:
  "this template will NOT appear on the front end" and "the header is being built WITHOUT its
  navigation" were both log-only, which means both could ship unnoticed.
- **Don't re-implement a check in prose.** `qa-review` calls `es_container_audit()` rather than
  describing the walk again. Two implementations of one rule drift, and the hand-rolled one loses.
- **Prefer a helper over a rule.** `es_section()` hardcoded `flex_direction:column`, so building
  two columns REQUIRED the extra container the rule forbade. When the library makes the wrong
  shape the easy one, no amount of documentation wins. Fix the library first (`es_split()`,
  `es_wide()`, `es_photo()`), then write the rule.

## Workflow
```
git checkout -b <type>/<short-name>     # gotcha/side-cart-trap, feat/build-home, fix/…
# edit
git add -A
git commit -m "<type>: <summary>"       # conventional commits, no AI attribution
git push -u origin <branch>
# open a PR
```

## Versioning
Bump `metadata.version` in a skill's frontmatter when its contract changes. `divi-core`
stays < 1.0 until its path is validated end-to-end on a real site.

## Testing a change
First, offline — no WordPress, no connector, both run in a second:

```bash
php tools/framework-audit.php && php tests/test-container-hygiene.php
```

The audit enforces everything on this page that a machine can decide: frontmatter, the word
budget, broken `references/` and `assets/` pointers, a write-capable skill that lost its build
gate, `error_log()` with no stdout channel, and Hard Rules in write-capable skills that name no
verifier. Its `JUDGE` rows are NOT passes — a model has to read them; that half is
`skills/framework-audit/SKILL.md`. The test suite guards the container audit itself, because the
code that enforces the rules needs something enforcing it too.

Add checks to `tools/framework-audit.php`, never as prose in a skill.

Then install locally (`install.ps1` / `install.sh`) and test at the right depth:

- **Design-phase changes** (`web-templates`, `ux-design-system`, `html-mockup`) need **no
  WordPress at all** — that phase is builder-agnostic by design. Run it greenfield and read the
  Artifact.
- **Anything that writes** needs a throwaway NovaMira site. Confirm the build gate actually
  blocks: a skill reached directly, without the orchestrator, must still refuse to write until
  you say yes. A change that lets a write through unasked is a regression, however good it looks.
- Then confirm `qa-review` passes with server-side evidence, including its house-rules checklist,
  before merging. Server-side evidence means fetched CSS/HTML with counted selectors — never a
  claimed visual result.
