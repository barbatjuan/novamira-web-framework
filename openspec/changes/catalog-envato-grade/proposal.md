# Proposal: catalog-envato-grade

## Intent

23 home archetypes; **16 declare no `Envoltorio` wrapper contract**, so they render as the same `section > .canvas > .head.stack` — one page repainted 23 times, invisible to `RT_TPL_TOO_SIMILAR` (which measures section *inventory* only). Compounding: the gallery is never routed by `agents/novamira-web-orchestrator.md`; there is no bespoke lane (forbidden by `recommender.md:3`); 67 strips mix 55 axis-proof strips (one shared content set) with 12 catalogue strips in one flat list. Outcome: ~10 Envato-grade demos grouped by style, a bespoke lane passing identical gates, a gallery that reads as a store.

## Scope

### In Scope

- **Two-lane output contract**: catálogo / bespoke / promoción; toggle placement; the promotion gate.
- **Gallery IA contract**: axis proof moves to `ux-design-system`; catalogue grouped estilo → enfoque; gallery + lane fork routed in the orchestrator agent.
- **Gate before amputation**: wrapper-signature audit row + "every home `TPL-*` declares an `Envoltorio` table", tests first; then delete the 22 non-Lumière home archetypes and rewrite `recommender.md` / both `_README.md`.
- **Registers replacement in the same commit** as house-content removal (see Risk R2).
- **Pilot then scale**: Inmobiliaria de la O end to end, then ~3 styles × 3–4 focus, ≥4 ecommerce (corte/bajura/tueste/medida — zero new photography).
- **Pre-gate handoff block** emitted at mockup approval.

### Out of Scope

- Application behaviour: mortgage simulator, real map provider (drags in `wordpress-legal` consent), `propiedad` CPT + taxonomies, URL-reflected filters.
- The 24 inner-page archetypes, generator chassis, `$ANCHORS`, `sec_open()`, tokens, ink grade — untouched.
- Any **new personality anchor**: forbidden, `RT_PERS_TOO_SIMILAR` FAILs on >1 shared axis.
- Refreshing stale `openspec/config.yaml` `measured_state` (see R6) — user decision.

## Capabilities

### New Capabilities

- `template-lane-contract`: the resolved-architecture invariant, three lanes, toggle home, promotion gate.
- `catalog-wrapper-integrity`: wrapper-signature + `Envoltorio`-table audit rows and their tests.
- `gallery-information-architecture`: axis proof vs catalogue split, estilo→enfoque grouping, Registers divisor semantics, orchestrator routing.
- `demo-authoring-contract`: what a demo must ship — anchor placement, re-measured contrast, own photo set (slug = filename), responsive, `Envoltorio` table, `COMP-*` wireframe.
- `mockup-handoff-persistence`: structured pre-gate handoff block.

### Modified Capabilities

- None. `gallery-bootstrap-integrity` requirements hold unchanged.

## Approach

Four sequential phases, one chained PR each.

| # | Phase | Why this order |
|---|---|---|
| 1 | Contracts, spec-only, **no deletion** | Defines the acceptance criterion every later demo must meet. New prose lands in `references/` — `RT_BODY_LENGTH` ceiling is 600w and `web-templates` is at 559, `html-mockup` at 582. |
| 2 | Raise the gate, then amputate | TDD: failing tests → gate → deletion, so the new catalogue is born under the gate. Checkpoint: clean audit with a catalogue of one. |
| 3 | Pilot, then scale | Inmobiliaria de la O proves the Claude Design → `TPL-*` route once before it is used nine more times. |
| 4 | Pre-gate persistence | `es_manifest_record()` lives in `es-builder.php`, uploaded only *after* the build gate; the choice exists as chat text until then. |

**Pilot anchor (resolved, do not re-litigate):** `PERS-EDITORIAL` with **one brand override — ground**. Scale/density/composition/elevation all match measured. The 1px hairlines are `gap:1px` over a coloured grid, a composition technique, **not** elevation. A brand legally brings its own ground/accent/type/photos and borrows the rest (`_build-gallery.php:445`).

## Affected Areas

| Area | Impact | What changes |
|---|---|---|
| `skills/web-templates/SKILL.md` + `references/` | Modified | Invariant rewritten; lanes, toggles, promotion gate in `references/` |
| `skills/web-templates/references/recommender.md` | Modified | 341 lines; §3/§3b maps rebuilt off the dead Family A/B split |
| `skills/web-templates/references/templates/**` | Removed | 22 non-Lumière home archetypes |
| `skills/framework-audit/assets/framework-audit.php` | Modified | New wrapper-signature + `Envoltorio` rows |
| `tests/test-framework-audit.php` | Modified | Failing tests first (gallery-gate tests L3868–3967 are the pattern) |
| `skills/html-mockup/assets/gallery/_build-gallery.php` | Modified | `$CONTENT`/`$STRIPS`/`$PAGES`/`$BRANDS`, orphaned render fns, IA restructure |
| `skills/html-mockup/assets/gallery/_gallery-images.md` | Modified | Retired brand rows; **new per-demo Registers table** |
| `agents/novamira-web-orchestrator.md` | Modified | Gallery step + lane fork (routing map `:68-86`, `:91-108`) |

## Open Decisions (resolve in spec/design — do not assume)

1. **Container width**: design `1440px` vs framework `--container-max: 1280px` (`design-system.md:138`). One must give.
2. **Font**: `Archivo` is in house; **Libre Caslon Display is not** (house serifs: Fraunces, Instrument Serif). Adding a woff2 + OFL + `_fonts.php` entry moves the gallery fingerprint.
3. **Accent contrast**: `#8A7B5C` = 3.77:1 on `#F6F4F0`, 4.29:1 on `#17181A` — fails 4.5:1 on **both** grounds; tertiary `.45` = 2.85:1, `.55` = 3.84:1; secondary `.62` = 4.80:1 passes. Darken for text roles, or restrict accent to non-text/large text. Re-verify with the generator's own contrast engine, which already prints every ratio and auto-demotes.
4. **Pilot photography**: the 15 `inmo-*.webp` are a Spanish urban agency; the design sells Sierra Blanca villas at 642 m² / 6 M€+. New set required.
5. **Responsive undefined** in the handoff ("pendiente de definir"); `qa-review` and `visual-verification` both require it.

## Risks

| # | Risk | Likelihood | Mitigation |
|---|---|---|---|
| R1 | Deleting 22 archetypes breaks `RT_TPL_UNROUTABLE` / recommender routing | High | Gate + tests land first; rewrite `recommender.md` in the same slice |
| R2 | Removing the house Registers table hard-FAILs `RT_GALLERY_ONE_SHOOT` on a missing divisor | High | Same-commit replacement, **one register per surviving demo** (~10 → cap 7), never per style family (3 → cap 24, too loose to catch a shared shoot) |
| R3 | Fingerprint churn (`RT_GALLERY_STALE`); deletion + new woff2 both move it | High | Regenerate in every slice; current digest `7767ab39ba5e…` |
| R4 | Ecommerce orphaned — Lumière is corporate; `TPL-PDP-*`, cart, checkout, `woocommerce` lose their demo | Med | ≥4 ecommerce demos, mapped 1:1 to PDP archetypes by the `_README.md` "pregunta que separa" column |
| R5 | Measured token blocks in the 11 dying branded docs are cheap to copy, expensive to re-derive | Med | Harvest before deleting |
| R6 | `openspec/config.yaml` `measured_state` is stale (1164 OK / 2026-08-24; truth is **1193 OK**) | Low | Flagged; user decides whether to refresh |
| R7 | Visual regression missed by sampling | Med | Sweep **every** strip at **every** anchor; the last catalogue-wide change was invalidated by checking 4 of 21 |

## Rollback Plan

Per-phase chained PRs on `feat/catalog-envato-grade` off `main @ 35a38b4`. Revert the offending PR; earlier phases stand alone (Phase 1 is spec-only, Phase 2 ends at a self-consistent catalogue of one). Regenerate the gallery after any revert to restore the fingerprint. No history rewrite (standing `rules.proposal` constraint).

## Dependencies

- Claude Design handoff: `D:\Downloads\Sitio web inmobiliario premium.zip` (`design_handoff_inmobiliaria_de_la_o/`).
- Image generation (Freepik AI / Pikaso / Magnific) for the pilot photo set.
- `~/.claude/skills` is a deployed **copy** — redeploy after merge to keep repo and install byte-identical.

## Success Criteria

- [ ] `php skills/html-mockup/assets/gallery/_build-gallery.php && php skills/framework-audit/assets/framework-audit.php && php tests/test-framework-audit.php` → **0 FAIL**, WARN ≤ 4, tests ≥ **1193 OK / 0 FAIL**.
- [ ] Every surviving home `TPL-*` declares an `Envoltorio` table and a distinct wrapper signature, enforced by a new audit row.
- [ ] `RT_GALLERY_ONE_SHOOT` passes against a per-demo Registers table, not a trivially loose divisor.
- [ ] Gallery shows catalogue only, grouped estilo → enfoque; axis proof lives in `ux-design-system`.
- [ ] **Two-lane smoke test**: one bespoke architecture runs `web-templates` → `ux-design-system` → `html-mockup` and downstream never asks which lane it came from.
- [ ] ≥4 ecommerce demos; `TPL-PDP-*`, cart, checkout and `woocommerce` all have a live consumer.
- [ ] `visual-verification` sweep over every strip at every anchor after each regeneration.
