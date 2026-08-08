---
name: html-mockup
description: "Trigger: maqueta, mockup, HTML preview, prototipo, visual approval, aprobar diseño, static mockup. Materialize the resolved architecture + tokens into a static responsive HTML/CSS homepage for client approval BEFORE the native builder build. Builder-agnostic, published as an Artifact."
license: Apache-2.0
metadata:
  author: "juan"
  version: "1.0"
---

# HTML Mockup (approval preview)

Turn the resolved architecture (`web-templates`) + visual spec (`ux-design-system`) into a
static, responsive HTML/CSS homepage that the client can SEE and approve — before paying the
expensive, fragile native build. Operative skill: it produces HTML/CSS (like `elementor-core`
produces `_elementor_data`), published as an Artifact for live review.

## Activation Contract
Run after `ux-design-system` (tokens + patterns decided) and BEFORE `elementor-core` /
`divi-core`. Its approved output is the visual contract `qa-review` checks the native build against.

## Hard Rules
- The mockup is a PREVIEW + approval artifact and the visual contract — **never** a source to
  import into the builder. Elementor/Divi builds are reproduced NATIVELY from the same spec, never
  by pasting this HTML (pasted HTML = one dead block, not editable widgets).
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
1. Take the resolved section inventory (`web-templates`) + tokens/patterns (`ux-design-system`).
   Read `references/mockup-guide.md` for the base shell + token block.
2. Build ONE responsive HTML file: `:root` tokens, semantic `header/main/section/footer`, each
   section per the template's per-breakpoint notes, only the sections resolved as kept/on.
3. Publish as an **Artifact** (title = "<brand> — <TPL id> maqueta", a favicon emoji, one-line
   description). Share the URL. State plainly: structural preview, no final imagery/copy, needs
   the user's visual confirmation.
4. Collect approval or a change list. Iterate the same file → republish to the same URL.
5. On approval: freeze it as the visual contract and hand the approved section inventory + tokens
   to `elementor-core` / `divi-core`, noting the native build must match this mockup.

## Output Contract
Return the Artifact URL of the approved mockup, the section inventory it represents, the toggle
states baked in, and the note that `qa-review` diffs the native build against it. On iteration,
report what changed.

## References
- `references/mockup-guide.md` — base HTML shell, `:root` token block, section blueprints,
  placeholder recipes, responsive rules. Pairs with `web-templates` (spec) and `ux-design-system`
  (look); hands off to `elementor-core` / `divi-core`.
