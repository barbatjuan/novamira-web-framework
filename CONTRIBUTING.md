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
  (The old "~180–450 tokens" was aspirational and never held. Don't measure by eye — the audit
  counts for you and FAILs past the ceiling. Be honest about the ~300 aim too: **0 of 11 skills
  meet it**, so it is currently a wish, not a budget. Either trim them or move the number; a
  threshold nothing ever meets teaches people to skip the whole report.)
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

### Row types

Every row `framework-audit.php` can print is declared in its `ROW_TYPES` registry (run
`… --emit-row-types` to list them from the source of truth) and mirrored here. An ID missing from
this table is itself a FAIL (`RT_ROWTYPE_UNDOCUMENTED`), so this table cannot silently drift from
the code — adding a check without adding its row here fails the audit on itself.

| Row type | Level | What it means |
|---|---|---|
| `RT_NO_SKILL_MD` | FAIL | a skill directory has no `SKILL.md` |
| `RT_NO_FRONTMATTER` | FAIL | `SKILL.md` has no YAML frontmatter block |
| `RT_FRONTMATTER_MISSING_KEY` | FAIL | frontmatter is missing a required key |
| `RT_NAME_MISMATCH` | FAIL | frontmatter `name:` does not match its directory |
| `RT_NO_TRIGGER` | FAIL | description carries no `Trigger:` words |
| `RT_BODY_OVER_600` | FAIL | `SKILL.md` body is past the ~600-word ceiling |
| `RT_BODY_OVER_300` | WARN | `SKILL.md` body is past the ~300-word aim |
| `RT_NO_BUILD_GATE` | FAIL | a write-capable skill has no blocking build gate |
| `RT_BROKEN_REFERENCE` | FAIL | `SKILL.md` points at a `references/`/`assets/` path that does not exist |
| `RT_ORPHAN_FILE` | WARN | a `references/`/`assets/` file is never mentioned by `SKILL.md` |
| `RT_HARD_RULE_NO_VERIFIER` | JUDGE | a Hard Rule names no verifier |
| `RT_NO_HARD_RULES` | WARN | `SKILL.md` has no `## Hard Rules` section |
| `RT_ERRORLOG_NO_STDOUT` | FAIL | an error_log call has no paired stdout channel |
| `RT_WRITE_NOT_LISTED` | FAIL | code writes to WordPress but the skill is missing from `$WRITE_CAPABLE` |
| `RT_AGENT_CODE_BLOCK` | FAIL | an agent markdown file contains a code block |
| `RT_AGENT_ROUTE_MISSING` | FAIL | an agent routes to a skill that does not exist |
| `RT_AGENT_SKILL_UNMENTIONED` | WARN | an agent never mentions an existing skill |
| `RT_HOUSERULES_NO_VERDICT` | FAIL | a `house-rules.md` row has no verdict source |
| `RT_HOUSERULES_MISSING` | FAIL | `qa-review/references/house-rules.md` is missing |
| `RT_NO_OFFLINE_TESTS` | FAIL | no offline test suite under `tests/` |
| `RT_GATE_LINE_UNREGISTERED` | FAIL | a `tests/test-*.php` file is absent from the testing gate line below |
| `RT_ROWTYPE_UNDOCUMENTED` | FAIL | a `ROW_TYPES` ID is not listed in this table |

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
php skills/framework-audit/assets/framework-audit.php && php tests/test-container-hygiene.php && php tests/test-framework-audit.php
```

The audit enforces everything on this page that a machine can decide: frontmatter, the word
budget, broken `references/` and `assets/` pointers, a write-capable skill that lost its build
gate, `error_log()` with no stdout channel, and Hard Rules in write-capable skills that name no
verifier. Its `JUDGE` rows are NOT passes — a model has to read them; that half is
`skills/framework-audit/SKILL.md`. The test suite guards the container audit itself, because the
code that enforces the rules needs something enforcing it too.

Add checks to `skills/framework-audit/assets/framework-audit.php`, never as prose in a skill.

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
