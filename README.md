# NovaMira Web Framework

Modular system for building **premium WordPress sites via NovaMira** (Elementor or Divi),
reusable across projects (workshop, clinic, real-estate, ecommerce…) by swapping brand
config and adding domain skills. The orchestrator and bases stay the same.

**Principle: the agent thinks, the skills execute.** A tiny orchestrator agent decides
*which* skill runs, in what order, with what context. It holds no CSS/HTML/PHP — those live
in skills and their `assets/`.

## What's inside
```
agents/novamira-web-orchestrator.md   # tiny router (thinks, asks, routes)
skills/
  _novamira-framework.md              # architecture overview
  project-context/                    # detect builder (elementor|divi), plugins, brand
  ux-design-system/                   # builder-agnostic visual language (tokens, motion, layout)
  elementor-core/                     # Elementor execution — battle-tested (+ es-builder.php)
  divi-core/                          # Divi execution — scaffold, grow the gotchas
  woocommerce/                        # shop / product / side cart / templates
  wordpress-performance/  wordpress-seo/  qa-review/
```
Two kinds of skill: **knowledge** (teach, modify nothing) and **operative** (deploy). Each
core skill splits stable `references/knowledge.md` from hard-won `references/gotchas.md`.

## Install
Skills and agents load from `~/.claude/` (user scope). Run one:

**Windows (PowerShell):**
```powershell
./install.ps1
```
**macOS / Linux:**
```bash
./install.sh
```
Both copy `agents/` and `skills/` into `~/.claude/`. Re-run to update after `git pull`.
(Or symlink the folders into `~/.claude/` if you prefer live edits.)

## Use
In Claude Code, ask the orchestrator to drive a build:
> "Use the **novamira-web-orchestrator** to redesign the home and shop of this WordPress site."

It runs `project-context` first (detects Elementor vs Divi, WooCommerce, brand), asks you
anything ambiguous, then routes to the right skills and verifies server-side. You can also
invoke a skill directly by name (e.g. `elementor-core`, `woocommerce`).

Requirements: a connected **NovaMira** MCP connector for the target site (the connector UUID
is per-site; give it to the agent). Elementor path is proven; Divi path is a scaffold.

## Contributing (grow the gotchas)
The `references/gotchas.md` files are the real value. When something surprises you on a
build, add a confirmed entry (symptom → cause → fix → "do NOT"). Keep `SKILL.md` bodies
concise (~180–450 tokens); put detail in `references/` and code in `assets/`.

1. `git checkout -b gotcha/<short-name>`
2. Edit the relevant skill / reference / asset.
3. `git commit` (conventional commits) and open a PR.

## Per-project extension
Add domain operative skills next to these (`build-home`, `build-product-page`,
`real-estate-listings`, `clinic-booking`…). Reuse the same orchestrator, bases, and assets.

## License
Apache-2.0 (skills). Adapt the palette/brand tokens freely.
