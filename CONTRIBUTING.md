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
Install locally (`install.ps1` / `install.sh`), then test at the right depth:

- **Design-phase changes** (`web-templates`, `ux-design-system`, `html-mockup`) need **no
  WordPress at all** — that phase is builder-agnostic by design. Run it greenfield and read the
  Artifact.
- **Anything that writes** needs a throwaway NovaMira site. Confirm the build gate actually
  blocks: a skill reached directly, without the orchestrator, must still refuse to write until
  you say yes. A change that lets a write through unasked is a regression, however good it looks.
- Then confirm `qa-review` passes with server-side evidence, including its house-rules checklist,
  before merging. Server-side evidence means fetched CSS/HTML with counted selectors — never a
  claimed visual result.
