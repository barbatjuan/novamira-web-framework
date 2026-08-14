---
name: html-mockup
description: "Trigger: maqueta, mockup, HTML preview, prototipo, visual approval, aprobar diseño, static mockup. Materialize the resolved architecture + tokens into a static responsive HTML/CSS page set for client approval BEFORE the native builder build. Builder-agnostic, published as ONE Artifact with in-page navigation."
license: Apache-2.0
metadata:
  author: "juan"
  version: "1.4"
---

# HTML Mockup (approval preview)

Turn the resolved architecture (`web-templates`) + visual spec (`ux-design-system`) into static,
responsive HTML/CSS the client can approve before paying for the fragile native build — home and
every inner page, in **ONE Artifact** sharing one `:root` token block.

## Activation Contract
Run after `ux-design-system` (tokens + patterns decided), BEFORE `elementor-core` / `divi-core`.
Its approved output is the visual contract `qa-review` checks the build against.

## Hard Rules
- The mockup is the approval artifact and visual contract — **never** a source to import. The
  build is reproduced NATIVELY from the same spec; pasted HTML is one dead block, not widgets.
- **ONE Artifact for the whole page set.** Every page is a `<div class="page">` in the same file,
  switched with in-page JS; header, announcement bar and footer sit OUTSIDE them so they survive
  every switch. Never split pages across Artifacts nor link them with `target="_top"` —
  cross-artifact nav is dead in the sandbox. Detail: `references/mockup-guide.md` § "Multi-page
  preview".
- Mobile-first at the framework breakpoints (`<768 / 768–1024 / >1024`); no horizontal scroll at
  any of them, and wide blocks scroll inside their own container.
- Theme-aware, unless the file pins one ground on purpose.
- **The DOM is a blueprint, not scaffolding.** Every wrapper `<div>` here becomes a container in
  the native build. Keep it flat: `section > grid|row > element`. Detail:
  `references/mockup-guide.md` § "Container hygiene".
- Declare the tokens as CSS variables in `:root` ONCE — the same tokens the native build uses.
- Self-contained: inline `<style>`, no external CSS/JS/fonts/CDNs, no remote images (Artifact
  CSP). Placeholder blocks only, no client photos or final copy.
- Mirror `ux-design-system`: one accent for CTAs, calm motion (`cubic-bezier(.22,1,.36,1)`,
  ~.35–.7s), two button families, one card recipe.
- **Do not proceed to builder-core until the user approves.** Capture changes, iterate, republish.

## Execution Steps
1. Take the resolved section inventories + tokens. Pick the starting asset **by site type** —
   never start a corporate site from the ecommerce one (cart, shipping bar and Tienda nav are
   ecommerce DNA). Read `references/mockup-guide.md` first.
2. Build ONE responsive file: one `:root` block, semantic `header/main/section/footer`, one
   `.page` per page, only the sections resolved as kept/on. Header/announcement/footer stay
   global. **Ecommerce only**: € prices, cart badge.
3. Publish as ONE **Artifact** (title `<brand> — maqueta`, favicon emoji, one-line description).
   Share the URL as a structural preview needing the user's visual confirmation.
4. Collect approval or a per-page change list. Iterate the same file → republish to the same URL.
5. On approval: freeze it as the visual contract and hand inventories + tokens to
   `elementor-core` / `divi-core`; the build must match it.

## Output Contract
Return the Artifact URL, the per-page section inventory and the toggle states baked in. On
iteration, report what changed.

## References
- `assets/ecommerce-mockup.html` — 7 pages (home · shop · pdp · cart · checkout · about ·
  contact), € prices, cart badge.
- `assets/corporate-mockup.html` — no commerce; 6 pages (home · services · service detail
  `TPL-SERVICE-01` · about · cases · contact). Hero carries a CTA only; the lead form lives in
  the closing band.
- `references/mockup-guide.md` — governing detail: HTML shell, token block, the multi-page
  contract, section blueprints, placeholder recipes, responsive rules.
- `assets/_axis-proof-content.md` — one copy set, rendered by both proof files below: the
  constant that makes the axis comparison honest.
- `assets/proof-editorial-mockup.html` — `PERS-EDITORIAL` over that copy: paper ground, editorial
  scale, `LP-ASYMMETRIC`, no elevation.
- `assets/proof-direct-mockup.html` — `PERS-DIRECT` over the SAME copy: ink ground, monumental
  scale, `LP-BROKEN-GRID`, accent glow. Same strings (`RT_PROOF_COPY_DIFFERS` gates it); every
  difference is the anchor — five axes plus the typography and detail each file lists in its head.
