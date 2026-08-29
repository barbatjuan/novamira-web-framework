# WordPress Orchestrator

Modular system for building **premium WordPress sites**, reusable across
projects (workshop, clinic, real-estate, ecommerce…) by swapping brand config and adding
domain skills. The orchestrator and bases stay the same. Two builder paths exist —
**Elementor is proven; Divi is an unvalidated scaffold** (see "Use" below).

**Principle: the agent thinks, the skills execute.** A tiny orchestrator agent decides
*which* skill runs, in what order, with what context. It holds no CSS/HTML/PHP — those live
in skills and their `assets/`.

## What's inside
```
agents/wordpress-orchestrator.md      # tiny router (thinks, asks, routes)
skills/
  _wordpress-orchestrator-framework.md # architecture overview
  project-context/                    # detect builder (elementor|divi), plugins, brand
  web-templates/                      # page ARCHITECTURE: home archetypes (ecommerce|corporate) + inner pages (PDP, shop, about, contact) + recommender + toggles
  ux-design-system/                   # builder-agnostic visual language (tokens, motion, layout)
  html-mockup/                        # static HTML preview for client approval before the native build
  elementor-core/                     # Elementor execution — battle-tested (+ es-builder.php)
  divi-core/                          # Divi execution — scaffold, unvalidated, no assets yet
  elementor-theme-parts/              # header, footer, Theme Builder parts (Elementor Pro)
  woocommerce/                        # shop / product / side cart / templates (Elementor path only)
  wordpress-forms/                    # contact/lead forms + a PROVEN delivery, not a rendered form
  wordpress-legal/                    # legal pages from the client's real data + a banner that blocks
  wordpress-performance/  wordpress-seo/  qa-review/
```

Alongside the orchestrator, `agents/wordpress-copywriter.md` is a subagent that writes the real
copy in its own context window. It is reached by explicit delegation only and never touches
WordPress.

## Build flow
The agent's **first question is "new site or existing site?"** — it decides whether
WordPress is inspected at all.

**New site (greenfield)** — nothing is written to WordPress until the gate:
```
web-templates → ux-design-system → html-mockup → [BUILD GATE] → project-context
→ elementor-core | divi-core → woocommerce → performance / seo → qa-review
```
**Existing site** — inspect first, then route on what was actually found:
```
project-context → web-templates → ux-design-system → html-mockup → [BUILD GATE]
→ elementor-core | divi-core → woocommerce → performance / seo → qa-review
```
`web-templates` picks a `TPL-*` archetype by site type, asks you for references and resolves
toggles; `ux-design-system` fixes tokens and motion; `html-mockup` renders a static preview
you approve; builder-core reproduces it natively; `qa-review` diffs the native build against
the approved mockup. The design phase is builder-agnostic and needs no WordPress.

**The build gate is a hard stop.** After the mockup is approved and before ANY write to
WordPress, the agent stops and asks for an explicit yes for that build — expect it to block
there. No mockup approval + no explicit yes → no native build. On an existing site it also
confirms each page overwrite by name. The mockup itself is the approval gate and the visual
contract; it is never imported into the builder.

Three kinds of skill: **knowledge** (`web-templates`, `ux-design-system` — decide, touch
nothing), **read-only** (`project-context`, `qa-review` — inspect and report, never write),
and **operative** (`html-mockup` produces an Artifact; the rest write to the live site behind
the gate). Only `elementor-core` and `woocommerce` currently have both a
`references/knowledge.md` and a `references/gotchas.md`; `divi-core` has gotchas only, and
`project-context` / `qa-review` / `wordpress-performance` / `wordpress-seo` / `wordpress-forms` /
`wordpress-legal` have no
`references/` yet. See `skills/_wordpress-orchestrator-framework.md` for the full map.

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
The copy overwrites in place — it does **not** delete files that were removed upstream, so a
skill deleted from this repo keeps living in your `~/.claude/` until you remove it by hand.
(Or symlink the folders into `~/.claude/` if you prefer live edits and exact mirroring.)

## Use
In Claude Code, ask the orchestrator to drive a build:
> "Use the **wordpress-orchestrator** to redesign the home and shop of this WordPress site."

It asks whether the site is new or existing first. On an **existing** site it runs
`project-context` up front (detects Elementor vs Divi, WooCommerce, theme, brand) and routes
on what it reports. On a **new** site it skips straight to the builder-agnostic design phase
and runs `project-context` later, at the build gate, once there is a connected WordPress
target to inspect. It asks you anything ambiguous, stops at the build gate for your explicit
yes, then builds and verifies server-side. You can also invoke a skill directly by name
(e.g. `elementor-core`, `woocommerce`) — the operative skills enforce the build gate
themselves.

Requirements: a connected **NovaMira** MCP connector for the target site (the connector UUID
is per-site; give it to the agent) — needed only from the build gate onward, not for the
design phase.

**Elementor is the proven path.** Divi is a scaffold: `divi-core` is v0.2, has no `assets/`
and no helper library, and no Divi build has been validated end-to-end on a real site.
`woocommerce` and `qa-review` are likewise Elementor-only in practice today. Treat any Divi
step as unverified and record what you learn in `divi-core/references/gotchas.md`.

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
