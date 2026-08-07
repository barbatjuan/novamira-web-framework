# NovaMira Web Framework — overview

A modular system for building premium WordPress sites via NovaMira. Reusable across
projects (Elorrieta, a clinic, a real-estate site, an ecommerce) by swapping brand config
and adding domain skills — the orchestrator and bases stay the same.

## Principle
**The agent thinks. The skills execute.** The orchestrator agent
(`agents/novamira-web-orchestrator.md`) only decides which skill runs, in what order, with
what context. It holds no CSS/HTML/PHP. Those live in skills and their `assets/`.

## Two kinds of skill
- **Knowledge** (teach, modify nothing): `ux-design-system`, and the `references/knowledge.md`
  inside each core skill. Living documentation.
- **Operative** (do work, deploy): `elementor-core`, `divi-core`, `woocommerce`,
  `wordpress-performance`, `wordpress-seo`, `project-context`, `qa-review`.

## Knowledge vs gotchas
Each core skill splits stable knowledge (`references/knowledge.md`) from hard-won traps
(`references/gotchas.md`). Gotchas are gold — grow them every time something surprises you.

## Builder-agnostic vs builder-specific
- Agnostic (both Elementor & Divi): `ux-design-system` (palette, spacing, motion, cards,
  responsive), `project-context`, `woocommerce` patterns, performance, seo, qa.
- Specific: `elementor-core` (battle-tested) and `divi-core` (scaffold). `project-context`
  detects the builder; the orchestrator routes and asks the user when unsure.

## Assets (library, not skill)
Reusable code lives under a skill's `assets/`: `elementor-core/assets/es-builder.php`
(helpers), `es-theme-parts.example.php`, and the `woocommerce/assets/*.example.php` templates.
Skills reference assets by path — they don't paste code inline.

## Flow
`project-context` → `ux-design-system` (decide the look) → `elementor-core` | `divi-core`
(execute) → `woocommerce` (if commerce) → `wordpress-performance` / `wordpress-seo` →
`qa-review` → hand off.

## Extending per project
Add domain-specific operative skills next to these (e.g. `build-home`, `build-product-page`,
`real-estate-listings`, `clinic-booking`). Reuse the same orchestrator, bases, and assets.
Keep new gotchas in the relevant `references/gotchas.md`.
