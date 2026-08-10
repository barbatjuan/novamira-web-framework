---
name: html-mockup
description: "Trigger: maqueta, mockup, HTML preview, prototipo, visual approval, aprobar diseño, static mockup. Materialize the resolved architecture + tokens into a static responsive HTML/CSS page set for client approval BEFORE the native builder build. Builder-agnostic, published as ONE Artifact with in-page navigation."
license: Apache-2.0
metadata:
  author: "juan"
  version: "1.2"
---

# HTML Mockup (approval preview)

Turn the resolved architecture (`web-templates`) + visual spec (`ux-design-system`) into
static, responsive HTML/CSS pages — the home AND the inner pages resolved in the page set
(Shop, Product/PDP, About, Contact…) — that the client can SEE and approve before paying the
expensive, fragile native build. Operative skill: it produces HTML/CSS (like `elementor-core`
produces `_elementor_data`), published as **ONE Artifact** for live review — the whole page set
in a single file, sharing one `:root` token block.

## Activation Contract
Run after `ux-design-system` (tokens + patterns decided) and BEFORE `elementor-core` /
`divi-core`. Its approved output is the visual contract `qa-review` checks the native build against.

## Hard Rules
- The mockup is a PREVIEW + approval artifact and the visual contract — **never** a source to
  import into the builder. Elementor/Divi builds are reproduced NATIVELY from the same spec, never
  by pasting this HTML (pasted HTML = one dead block, not editable widgets).
- **ONE Artifact for the whole page set.** Every page is a `<div class="page">` inside the same
  file; switch with in-page JS. Header, announcement bar and footer are global elements OUTSIDE
  the page containers, so they survive every switch. **Never split pages across Artifacts** and
  never link them with `target="_top"` — cross-artifact nav is dead in the sandbox. Governing
  detail: `references/mockup-guide.md` § "Multi-page preview".
- Mobile-first, responsive at the framework breakpoints (`<768 / 768–1024 / >1024`).
- Declare the design-system tokens as CSS variables in `:root` ONCE; every section references them.
  Same tokens the native build will use — single source of truth.
- Self-contained: inline `<style>`, no external CSS/JS/fonts/CDNs, no remote images (Artifact CSP).
  Use placeholder blocks (solid `--c-bg-alt` boxes, gradients, or inline SVG) — no client photos or
  final copy yet. Label it as a STRUCTURAL preview.
- Mirror `ux-design-system` rules so the native build matches: one accent color for CTAs, calm
  motion (`cubic-bezier(.22,1,.36,1)`, ~.35–.7s), two button families, one card recipe.
- Theme-aware and horizontally scroll-free; wide blocks scroll inside their own container.
- **Do not proceed to builder-core until the user approves.** Capture requested changes, iterate
  the HTML, republish.

## Execution Steps
1. Take the resolved section inventory(ies) (`web-templates`, home + page set) + tokens/patterns
   (`ux-design-system`). Pick the starting asset **by site type**: ecommerce →
   `assets/ecommerce-mockup.html`, corporate → `assets/corporate-mockup.html`. Never start a
   corporate site from the ecommerce asset (cart, shipping bar, Tienda nav are ecommerce DNA).
   Read `references/mockup-guide.md` for the shell, token block and section blueprints.
2. Build ONE responsive HTML file for the whole set: one `:root` token block, semantic
   `header/main/section/footer`, one `.page` container per page, only the sections resolved as
   kept/on, each per that archetype's per-breakpoint notes. Header/announcement/footer stay
   global and verbatim across pages. **Ecommerce path only**: prices in €, cart icon + badge.
3. Publish the file as ONE **Artifact** (title = "<brand> — maqueta", a favicon emoji, one-line
   description). Share the URL. State plainly: structural preview, no final imagery/copy, needs
   the user's visual confirmation. Start with the home page active; wire inner pages as resolved.
4. Collect approval or a change list per page. Iterate the same file → republish to the same URL.
5. On approval: freeze the file as the visual contract and hand the approved per-page section
   inventories + tokens to `elementor-core` / `divi-core`; the native build must match it.

## Output Contract
Return the single Artifact URL of the approved mockup, the section inventory per page it
represents, the toggle states baked in, and the note that `qa-review` diffs the native build
against it. On iteration, report what changed.

## References
- `assets/ecommerce-mockup.html` — **ecommerce start**: brand-neutral, one Artifact, in-page nav,
  global header/announcement/footer, 7 pages wired (home · shop · pdp · cart · checkout · about ·
  contact), prices in €, cart icon.
- `assets/corporate-mockup.html` — **corporate start**: same one-Artifact shell, no cart/commerce;
  6 pages wired (home · services index · **service detail (`TPL-SERVICE-01`)** · about · cases ·
  contact); process, cases, team, FAQ, booking/NAP. Hero carries a CTA only — the lead form sits in
  the closing band, never in the hero.
- Copy the matching asset, swap `:root` tokens + brand name + copy + placeholders, keep only the
  pages/sections the archetypes resolved.
- `references/mockup-guide.md` — governing rules: base HTML shell, `:root` token block, the
  one-Artifact multi-page contract, section blueprints (ecommerce + corporate), placeholder
  recipes, responsive rules. Pairs with `web-templates` (spec) and `ux-design-system` (look);
  hands off to `elementor-core` / `divi-core`.
