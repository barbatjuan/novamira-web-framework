---
name: web-templates
description: "Trigger: plantilla, wireframe, arquitectura de home, elegir plantilla, recomendar plantilla, ecommerce template, corporate template, site architecture, template. Choose and resolve a page architecture (which sections exist, order, hierarchy) before any visual or builder work. Site-type aware (ecommerce | corporate)."
license: Apache-2.0
metadata:
  author: "juan"
  version: "1.1"
---

# Web Templates (architecture)

Decide the ARCHITECTURE of a page — which sections exist, in what order, with what
hierarchy — before deciding how it looks (`ux-design-system`) or building it
(`elementor-core` / `divi-core`). Holds the archetypes, the recommender, and the toggles.
Not five designs with different colors: five different architectures + a layer that
picks and tunes one.

## Activation Contract
Runs before `ux-design-system`. Prerequisite is conditional:
- **Existing site**: run after `project-context` (stack, plugins, brand already detected).
- **New site (greenfield)**: run FIRST, with no WordPress and no `project-context` at all — there
  is nothing to inspect yet. `project-context` comes later, at the build gate.

Builder-agnostic. Produces a resolved architecture spec. Modifies nothing on the site.

## The 3 layers
- **CAPA 1 — Archetypes** (`references/templates/<type>/TPL-*.md`): proven skeletons. Each
  section is marked FIXED (part of the DNA) or TOGGLE (modular).
- **CAPA 2 — Recommender** (`references/recommender.md`): analyze the brand, request
  references, recommend a `TPL-*`, confirm.
- **CAPA 3 — Toggles** (`references/toggles.md`): fine-tuning limited to what the chosen
  template allows.

## Hard Rules
- Decide architecture only. No visual code, no builder data, no deploy here.
- Pick a base archetype, then adjust via toggles. Never assemble sections ad-hoc.
- A request that contradicts the archetype's DNA → recommend switching archetype, never
  deform the current one (no "add storytelling to the Catalog template").
- ONE shared design system across every template (`references/design-system.md`). Only the
  architecture changes per template; tokens are common.
- Ask the client for 2–4 references BEFORE recommending. Confirm the recommended template
  with the user before continuing.
- Mobile-first: every section carries mobile / tablet / desktop behavior.

## Execution Steps
1. Determine **site type**: `ecommerce` | `corporate`. Ask (AskUserQuestion) if unknown.
2. Read `references/recommender.md` → analyze, request references, map signals → archetype,
   present the recommendation + rationale, get confirmation.
3. Read `references/toggles.md` → run only the toggles the chosen template admits, precharged
   with defaults derived from the references.
4. Read the chosen `references/templates/<type>/TPL-*.md` → resolve the home section inventory
   (order + fixed/toggle state + toggle answers).
5. **Resolve the page set** (recommender §6): propose the inner pages the site needs
   (Shop, Product/PDP, About, Contact…), assign an archetype from `references/templates/pages/`
   to each (inherit the default coherent with the home, user can override), run each page's toggles.
6. Hand the resolved specs (home + each page) + `references/design-system.md` tokens to
   `ux-design-system`, then to `html-mockup` — one section inventory per page, rendered as ONE
   Artifact with in-page navigation (never one mockup or one Artifact per page).

## Output Contract
A resolved architecture spec: template id, site type, ordered section list with each
section's state resolved (kept/removed/swapped), the toggle answers, a pointer to the shared
tokens, and per-breakpoint notes. No visual or builder-specific code.

## References
- `references/design-system.md` — shared tokens (type, color, spacing, buttons, containers, radii).
- `references/recommender.md` — CAPA 2: analysis + reference intake + signal→template map.
- `references/toggles.md` — CAPA 3: modular toggle catalog.
- `references/templates/ecommerce/` — home archetypes TPL-E-01..05.
- `references/templates/corporate/` — home archetypes TPL-C-01..05.
- `references/templates/pages/` — inner-page archetypes: `product/` (PDP), `shop-archive/`,
  `about/`, `contact/`; see `pages/_README.md` for the page-set model.
