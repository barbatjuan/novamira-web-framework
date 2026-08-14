---
name: html-mockup
description: "Trigger: maqueta, mockup, HTML preview, prototipo, visual approval, aprobar diseño, static mockup. Materialize the resolved architecture + tokens into a static responsive HTML/CSS page set for client approval BEFORE the native builder build. Builder-agnostic, published as ONE Artifact with in-page navigation."
license: Apache-2.0
metadata:
  author: "juan"
  version: "1.3"
---

# HTML Mockup (approval preview)

Turn the resolved architecture (`web-templates`) + visual spec (`ux-design-system`) into static,
responsive HTML/CSS — home AND every inner page in the resolved set — that the client can SEE and
approve before paying for the expensive, fragile native build. Published as **ONE Artifact**: the
whole page set in one file sharing one `:root` token block.

## Activation Contract
Run after `ux-design-system` (tokens + patterns decided) and BEFORE `elementor-core` /
`divi-core`. Its approved output is the visual contract `qa-review` checks the native build against.

## Hard Rules
- The mockup is the approval artifact and the visual contract — **never** a source to import into
  the builder. Builds are reproduced NATIVELY from the same spec; pasted HTML is one dead block,
  not editable widgets.
- **ONE Artifact for the whole page set.** Every page is a `<div class="page">` inside the same
  file; switch with in-page JS. Header, announcement bar and footer are global elements OUTSIDE
  the page containers, so they survive every switch. **Never split pages across Artifacts** and
  never link them with `target="_top"` — cross-artifact nav is dead in the sandbox. Governing
  detail: `references/mockup-guide.md` § "Multi-page preview".
- Mobile-first, responsive at the framework breakpoints (`<768 / 768–1024 / >1024`).
- **The mockup's DOM is a blueprint, not scaffolding.** The native build reproduces this file
  section by section, so every wrapper `<div>` here becomes a container there — nest five levels
  and you teach the build to nest five. Keep it flat: `section > grid|row > element`. Detail:
  `references/mockup-guide.md` § "Container hygiene".
- Declare the tokens as CSS variables in `:root` ONCE — the same tokens the native build uses.
- Self-contained: inline `<style>`, no external CSS/JS/fonts/CDNs, no remote images (Artifact
  CSP). Placeholder blocks only, no client photos or final copy.
- Mirror `ux-design-system` so the native build matches: one accent for CTAs, calm motion
  (`cubic-bezier(.22,1,.36,1)`, ~.35–.7s), two button families, one card recipe.
- Theme-aware and horizontally scroll-free; wide blocks scroll inside their own container.
- **Do not proceed to builder-core until the user approves.** Capture changes, iterate, republish.

## Execution Steps
1. Take the resolved section inventories (`web-templates`) + tokens (`ux-design-system`). Pick the
   starting asset **by site type** — never start a corporate site from the ecommerce one (cart,
   shipping bar and Tienda nav are ecommerce DNA). Read `references/mockup-guide.md` first.
2. Build ONE responsive file: one `:root` block, semantic `header/main/section/footer`, one
   `.page` per page, only the sections resolved as kept/on, per that archetype's breakpoint notes.
   Header/announcement/footer stay global and verbatim. **Ecommerce only**: € prices, cart badge.
3. Publish as ONE **Artifact** (title `<brand> — maqueta`, favicon emoji, one-line description).
   Share the URL; state it is a structural preview needing the user's visual confirmation.
4. Collect approval or a per-page change list. Iterate the same file → republish to the same URL.
5. On approval: freeze it as the visual contract and hand the per-page inventories + tokens to
   `elementor-core` / `divi-core`; the native build must match it.

## Output Contract
Return the Artifact URL, the section inventory per page and the toggle states baked in. On
iteration, report what changed.

## References
- `assets/ecommerce-mockup.html` — 7 pages wired (home · shop · pdp · cart · checkout · about ·
  contact), prices in €, cart icon + badge.
- `assets/corporate-mockup.html` — no commerce; 6 pages (home · services · service detail
  `TPL-SERVICE-01` · about · cases · contact). Hero carries a CTA only; the lead form sits in the
  closing band, never in the hero.
- `references/mockup-guide.md` — governing detail: HTML shell, token block, the one-Artifact
  multi-page contract, section blueprints, placeholder recipes, responsive rules.
