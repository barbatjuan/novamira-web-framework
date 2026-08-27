# Design: catalog-envato-grade

## Technical Approach

The catalogue is reset around one derivable rule — **an archetype survives Phase 2 if and only if
it backs a D1 demo brand** — and every new gate is a *document parser* in `framework-audit.php`
that reuses the existing header-cell table discipline (`gallery_register_count()`,
`framework-audit.php:2478`) rather than inventing a second markdown dialect. The wrapper signature
is not a new inventory: it is the ordered `## 2. Wireframe` inventory that `tpl_wireframe_comps()`
(`:1143`) already extracts, **projected through** the `Envoltorio` table. That composition is what
closes the gap `RT_TPL_TOO_SIMILAR` (`:1289`) leaves open — inventory without shape.

Survivor set (7): `TPL-C-07` aranda · `TPL-C-11` alinea · `TPL-C-14` lumiere · `TPL-E-06` corte ·
`TPL-E-07` bajura · `TPL-E-08` tueste · `TPL-E-09` medida. Deleted (16): `TPL-C-01..06`, `C-08..10`,
`C-12`, `C-13`, `TPL-E-01..05`.

## Architecture Decisions

### Decision: Phase 2 amputates 16, not 22 — the checkpoint is a catalogue of seven

**Choice**: delete only archetypes backing no D1 brand.
**Alternatives**: the plan's blanket "22 non-Lumière homes die, catalogue of one".
**Rationale**: D1 (2026-08-26, later than the plan) keeps corte/bajura/tueste/medida *and* aranda
and alinea with their existing photography. Deleting `TPL-E-06..09` would delete the four demos D1
kept for costing zero photography; deleting `TPL-C-07`/`TPL-C-11` would force re-authoring two
archetypes that already match their sector (`TPL-C-11` §1 names "coaching" and "entrenamiento
personal"). Cost of retention: 5 `Envoltorio` tables authored in Phase 2 instead of 5 archetypes
authored in Phase 3.

### Decision: C1 — `TPL-PDP-01` moves to Tueste Norte; `medida` gets `TPL-PDP-05`

**Choice**: `$PAGES['TPL-E-08']` gains `TPL-PDP-01` ("Una bolsa" — single purchase);
`$PAGES['TPL-E-09']` gains `TPL-PDP-05`, harvesting `$CONTENT['TPL-E-03']['mtm']`
(`_build-gallery.php:4466`) re-skinned onto medida's own 6 photos.
**Alternatives**: (a) corte adopts PDP-01 — an accessory bolted onto a fit-and-sizing shop;
(b) a fifth ecommerce brand — a full photo set against D1's zero-photography premise;
(c) retire PDP-01 — the store loses the standard product page, the most common one it sells.
**Rationale**: a subscription shop that cannot sell one bag is not credible, so PDP-01 is
*business-required* by E-08's own model, not decorative. The two pages then differ along E-08's own
separating question (recurring vs one-off) instead of along a bolt-on. Cost: ≤1 new frame.

### Decision: C2 — `TPL-C-13` is deleted; `TPL-C-15 · Cartera curada` is its named replacement

**Choice**: option (iii), one new real-estate archetype, C-13 retired with an explicit rationale
(satisfies `catalog-wrapper-integrity` "Every Pre-Existing Envoltorio Table Has a Stated Phase-2
Disposition"). C-15 is born with `TGL-HERO-MODE` (`buscador-portada` | `retrato`, default
`retrato`) and inherits `TGL-MAP-MODE` (default `off`), so the urban/volume brief still routes here.
**Alternatives**: (i) the design adopts C-13's DNA; (ii) C-13's DNA is revised in place; (iii-a)
C-15 alongside a retained C-13.
**Rationale**: measured, not assumed. Deviation (b) is **not a deviation** — `COMP-MAP-SEARCH` is
`[toggle TGL-MAP-MODE]` and `TGL-MAP-MODE` documents `off` for "carteras de una sola zona"
(`TPL-C-13-property-search.md:101,146`); a 17-property Sierra Blanca portfolio is exactly that. But
with the map off, C-13's only non-default `Envoltorio` row disappears and the pilot renders an
all-`contained` page — the flat page this change exists to kill. Deviation (a) *is* real and
structural: C-13 writes "el buscador ES la portada … una imagen de fondo contenida —**no a sangre de
80vh**—" (`:82-84`) and "todo lo que se ponga por encima de él es retraso" (`:16`). The design's
78vh bleed hero contradicts the objective, not the styling, so `web-templates/SKILL.md:36-37` routes
it elsewhere rather than deforming C-13. The briefs differ at the root: C-13 serves a visitor who
arrives *filtering*, the design serves one who arrives being *persuaded*. (iii-a) was rejected
because C-13 has no `$BRANDS` entry — its five strips (`_build-gallery.php:6115-6119`) are exactly
the axis leaks `RT_GALLERY_AXIS_LEAK` removes — so retaining it keeps a demo-less archetype and an
avoidable `RT_TPL_TOO_SIMILAR` pair for zero shown value. `TPL-PROPERTY-01` survives untouched and
its render functions (`:15370`) re-key from `TPL-C-13` to `TPL-C-15`, so the detail page costs
nothing.

`RT_TPL_TOO_SIMILAR` is moot for C-15 (no real-estate sibling remains). Against `TPL-C-14`:
shared = HEADER, FOOTER, TEAM, FAQ ≈ 4 of a ≥15 union → `2·4 = 8 ≤ 15`, passes with margin.

### Decision: the pilot photo set is new slugs, not repainted ones

**Choice**: the 15 `inmo-*.webp` rows retire with `TPL-C-13`; the pilot registers a fresh slug
prefix (`delao-*`).
**Alternatives**: reuse the `inmo-*` slugs with new pixels; augment the urban set.
**Rationale**: the file name IS the WP attachment slug, so nothing may be renamed — but nothing is:
old rows are deleted, new rows added. Repainting pixels under a slug whose manifest row still
describes an unreformed ground floor is an untruthful manifest, and `RT_GALLERY_NO_MANIFEST` cannot
see it. New rows also force the `Freepik`/`Shoot`/`Licence` cells to be rewritten, which
`RT_GALLERY_ONE_SHOOT` re-derives.

### Decision: no new woff2 — Instrument Serif substitutes for Libre Caslon Display

**Choice**: pilot type pairing = `Instrument Serif` (display) + `Archivo` (text), both already in
`skills/html-mockup/assets/fonts/`.
**Alternatives**: add `libre-caslon-display-latin.woff2` + OFL + `_fonts.php` entry.
**Rationale**: `_gallery-fingerprint.php` hashes the woff2 set; Phase 2's 16 deletions already move
the digest (R3), and a font added in the same window makes a fingerprint break impossible to
attribute. `RT_MOCKUP_FONT_NOT_EMBEDDED` makes every named family load-bearing. Instrument Serif
occupies the same high-contrast display role; `Fraunces` is avoided because lumiere already holds it
(`_build-gallery.php:636`).

### Decision: contrast is measured once, in the generator; the audit checks the gate has not moved

**Choice**: pilot ground `#F6F4F0` / alt `#EFEBE4` / text `#17181A`, accent **`#8A5A2A`** (5.35 bg
· 4.94 alt, re-verified with the generator's own `contrast()`). `RT_GALLERY_ACCENT_TEXT_FAIL`
statically re-measures the `$BRANDS` block **and** asserts the literals `4.5` and `7.0` are still
present in `_build-gallery.php` — drift between the two implementations is itself a failure.
**Alternatives**: audit-only re-implementation of WCAG maths; audit checks gate presence only.
**Rationale**: the build already `fail()`s (it aborts, it does not warn), so a second contrast engine
is duplication; but the spec requires an audit row, and a row that only greps for a gate cannot name
the offending brand. The drift assertion buys both.

`#8A7B5C` (3.77 bg) and `#7A6B4E` (4.73 bg / **4.37 alt**) both stop the build. `bg-alt` is the
binding surface. The design's accent-on-dark uses (footer email, accent button) are section
treatments following the system `ink` pattern (`$ACCENT_BY_GROUND['ink'] = '#FF6A1A'`), never the
brand accent — so the "one accent cannot clear both grounds" impossibility never binds.

**PR3b correction — `#756547` also stops the build, on a SECOND gate this note did not check.**
`#756547` (5.15 bg / 4.77 alt) clears the text-contrast gate above with margin, but
`_build-gallery.php`'s house-ink derivation (`ink_ends()`, § 5a) mixes the accent into the ground's
own dark extreme at 45% and renormalizes to that extreme's luminance; the resulting shadow ink must
carry a channel spread ≥ 20 or the build `fail()`s ("a two-colour map whose dark ink is grey is not
a two-colour map" — the same regression the retired duotone caused). `#756547` produces `#28251D`,
spread 11. The whole `#8A7B5C → #7A6B4E → #756547` lineage is one family of *desaturated* khakis:
darkening without raising saturation clears text-contrast but not ink-spread, because a
low-saturation accent mixed into a near-black stays near-neutral regardless of how dark it is.
`#8A5A2A` — a terracotta/bronze, closer to Andalusian roof tile than the original stone-khaki —
raises saturation rather than just lowering luminance: shadow ink `#2F2317`, spread 24. Both gates
re-verified against the real `contrast()`/`ink_ends()` implementation before commit, not computed
separately. Discovered running the real generator, not by inspection — the build's own `fail()`
message named the exact spread and the exact hex.

### Decision: container 1280 — the design's 4-column rule grid reconciles as a ratio

**Choice**: `--container-max: 1280px` unchanged; the 1440 layout's rule grid is re-expressed as
four equal columns of the 1280 measure.
**Rationale**: D3. The grid is a proportion, not a pixel constant; only the gutter changes.

## Parser Designs (new `framework-audit.php` helpers)

```php
/* Selected by a header cell reading exactly `Envoltorio` — never by heading or position,
   the same discipline gallery_register_count() uses. Column index read from the header. */
tpl_envoltorio_table( $src ) : array<compId|'*', rawValue> | null

/* Free Spanish prose → sec_open()'s three real shapes (_build-gallery.php:15468).
   `banda` tested FIRST: "banda a sangre", "banda con fotografía al fondo" → bleed. */
env_shape( $raw ) : 'bleed' | 'row' | 'contained'
    contains 'banda' → bleed ; contains 'fila' → row ; else contained

/* Ordered wireframe inventory projected through the table. Same length as the inventory. */
tpl_wrapper_signature( $src ) : array<'bleed'|'row'|'contained'>
    foreach tpl_wireframe_comps($src)[0] as $comp:
        $shape = $table[$comp] ?? $table['*'] ?? 'contained'   // sec_open()'s own default
```

**The catch-all row.** Six of the seven compliant archetypes (`C-03`, `C-05`, `C-06`, `C-13`,
`E-01`, `E-07`) end their table with `| El resto | contenido | |` — a data row whose first cell is
**not** a `COMP-*` id. A detector demanding a `COMP-*` id in every row fails six of the seven files
the spec's own Purpose says already comply. So: a data row whose first cell matches
`/COMP-[A-Z0-9-]+/` is a per-section rule; at most **one** row that matches nothing is the
catch-all (`'*'`); a second catch-all is a FAIL under `RT_TPL_NO_ENVOLTORIO` with its own message
(the three-causes-one-row-id shape `RT_TPL_NO_WIREFRAME` already uses, `:1263-1279`). No catch-all →
default `contained`. **`TPL-C-14` passes unmodified** (6 `COMP-*` rows, no catch-all, its unnamed
sections defaulting to `contained`) — and so do the other six. *This refines the spec sentence
"whose first column names a `COMP-*` id per data row"; `sdd-tasks` must carry the amendment.*

`RT_TPL_WRAPPER_DUPLICATE` compares signatures **within a family** only, reusing `$tpl_families`
(`:1212`) for the reason `RT_TPL_TOO_SIMILAR` is family-scoped: `recommender.md` § 0 bifurcates on
site type, so a cross-family collision costs no client a choice. A file that fired
`RT_TPL_NO_ENVOLTORIO` is reported then **excluded** from the comparison (`:1258-1261` discipline) —
otherwise 16 all-`contained` signatures would drown the real finding.

Derived, never hardcoded:

| Row | Detector |
|---|---|
| `RT_GALLERY_AXIS_LEAK` | `$STRIPS[].tpl` matching `/^TPL-[A-Z]-\d+-([a-z0-9-]+)$/`; captured suffix must be a `$BRANDS` key. No suffix → leak |
| `RT_GALLERY_REGISTER_COUNT_MISMATCH` | `gallery_register_count($manifest) < count($BRANDS)` |
| `RT_GALLERY_SINGLE_PAGE_DEMO` | any brand-backed `$PAGES` key whose page list has length 1 |
| `RT_MOCKUP_CONTAINER_FORK` | container-max literal ≠ the value read from `design-system.md:138` |
| `RT_RECOMMENDER_NO_LANE_FORK` / `RT_RECOMMENDER_PROMOTION_GATE_MISSING` / `RT_ORCH_NO_GALLERY_STEP` | document-section presence, the `RT_QA_NO_AXIS_CHECK` shape |

All ten IDs must be registered in `ROW_TYPES` **and** `CONTRIBUTING.md`, or
`RT_ROWTYPE_UNDOCUMENTED` fires.

## Demo → Archetype → Page Map (all 10)

| # | Demo | Brand | Home | Inner pages | Photos |
|---|---|---|---|---|---|
| 1 | Lumière | `lumiere` | `TPL-C-14` ✔table | SERVICES-01, SERVICE-02, ABOUT-03, CONTACT-02 | 10 ✔ |
| 2 | Inmobiliaria de la O | `delao` **new** | **`TPL-C-15` new** | PROPERTY-01, ABOUT-01, CONTACT-01 | new set (`delao-*`) |
| 3 | Motor Aranda | `aranda` | `TPL-C-07` +table | UNIT-01, ABOUT-01, CONTACT-01 | 10 ✔ |
| 4 | Lawyers | new | **`TPL-C-16` new** | SERVICES-01, SERVICE-01, ABOUT-01, CONTACT-01 | new ~7 |
| 5 | Alinea (wellness coaching) | `alinea` re-briefed | `TPL-C-11` +table | SERVICE-02, ABOUT-01, CONTACT-01 | 3 → ~7 |
| 6 | Gyms | new | **`TPL-C-17` new** | SERVICES-01, ABOUT-01, CONTACT-01 | new ~7 |
| 7 | Corte Nueve | `corte` | `TPL-E-06` +table | PDP-02, ABOUT-02 | 9 ✔ |
| 8 | Bajura | `bajura` | `TPL-E-07` ✔table | PDP-03, ABOUT-02 | 7 ✔ |
| 9 | Tueste Norte | `tueste` | `TPL-E-08` +table | PDP-04, **PDP-01** | 5 (+≤1) |
| 10 | Medida Justa | `medida` | `TPL-E-09` +table | **PDP-05** | 6 ✔ |

`TPL-CART-01` / `TPL-CHECKOUT-01` / `TPL-SHOP-01` have **no render function** in
`_build-gallery.php` today. They stay out of the declared page sets: declaring a page the generator
cannot render is the lie `RT_GALLERY_SINGLE_PAGE_DEMO` would then bless. Proposal success criterion
R4 is met for `TPL-PDP-*`; cart/checkout consumers are recorded as an explicit Phase-3 stretch.

## Registers Sequencing (C4)

`gallery_register_count()` returns the **first** table carrying a literal `Register` header cell.
The 10-row per-demo replacement must therefore occupy the same position as the house table
(`_gallery-images.md:148-159`) **in the same commit** that removes it. All 10 demo names are
declared in Phase 2, before six of them exist: a larger `R` makes `cap = ceil(N/R)` *tighter*, never
looser, so declaring early is the conservative direction.

**Binding pre-commit check.** Phase 2 removes the 13 house rows (axis proof leaves with them), the
retired brands' rows and the 15 `inmo-*` rows, so `N` drops sharply while `R` rises to 10. The
deletion commit MUST compute `ceil(N/R)` and the maximum per-`fp-` shoot count and print both in the
commit body; if any shoot exceeds the cap, the shoot is diversified — the divisor is not retuned.

## Data Flow

    TPL-*.md ──2. Wireframe fence──→ tpl_wireframe_comps()  ──ordered COMP-* list──┐
        │                                                                          ├─→ tpl_wrapper_signature()
        └──Envoltorio table (header cell)──→ tpl_envoltorio_table() ─→ env_shape() ─┘        │
                                                                                             ▼
                                                           RT_TPL_NO_ENVOLTORIO / RT_TPL_WRAPPER_DUPLICATE

    $BRANDS ──→ $ACCENT_BY_GROUND ──→ contrast() ──→ fail()      (build stops, generator side)
        └──────────────────────→ framework-audit re-measure ──→ RT_GALLERY_ACCENT_TEXT_FAIL

## Phases, Files and PR Slicing (`auto-chain`, 800-line budget)

| PR | Phase | Files | Authored lines | Green at tip |
|---|---|---|---|---|
| 1 | 1 contracts | `web-templates/SKILL.md`, `web-templates/references/lanes.md` **new**, `references/toggles.md`, `references/recommender.md`, `agents/novamira-web-orchestrator.md`, `html-mockup/references/handoff-block.md` **new** | ~500 | yes |
| 2 | 2 gate + amputation | `framework-audit/assets/framework-audit.php`, `tests/test-framework-audit.php`, `CONTRIBUTING.md`, 5 survivor `Envoltorio` tables, **16 archetype deletions**, `recommender.md`, both home `_README.md`, `_build-gallery.php` prune, `_gallery-images.md` Registers + row removal, regenerate | ~450 added / ~5,000 deleted | yes |
| 3–4 | 3 pilot | 3a `TPL-C-15-curated-portfolio.md` + recommender + `_README` · 3b `delao` brand + `$CONTENT`/`$STRIPS`/`$PAGES` + manifest + regenerate | ~250 / ~450 | yes |
| 5–12 | 3 scale | one PR per demo (aranda, alinea, corte, bajura, tueste, medida) and two per new-archetype demo (lawyers, gyms) | 300–700 each | yes |
| 13 | 4 handoff | `html-mockup/SKILL.md` pointer, `references/handoff-block.md`, orchestrator build-gate step, `qa-review/references/house-rules.md` | ~200 | yes |

**PR 2 cannot be split and stay green.** `RT_TPL_NO_ENVOLTORIO` is red in every intermediate state
between "gate lands" and "16 files gone", and the spec forbids inverting that order. Its authored
*additions* are ~450 lines; the remainder is deletion of files a gate has just proven dead, which
does not carry the reviewer load the budget protects. `sdd-tasks` MUST forecast
`Decision needed before apply: Yes` for PR 2 and recommend `size:exception` for that slice only.
TDD ordering inside PR 2 is by commit: (1) rows + failing fixture tests, (2) survivor tables,
(3) amputation + recommender/`_README` rewrite + generator prune + Registers replacement,
(4) regenerate.

## Testing Strategy

| Layer | What | How |
|---|---|---|
| Unit (fixture) | `env_shape()` vocabulary; catch-all row; second catch-all; missing table | synthetic `TPL-*.md` fixtures, `tests/test-framework-audit.php` L3868–3967 pattern |
| Unit (acceptance) | **all 7 pre-existing tables pass unmodified**, `TPL-C-14` named explicitly | run the detector against `main @ 35a38b4` bytes |
| Integration | signature collision detected across a family; row excluded after `RT_TPL_NO_ENVOLTORIO` | two-fixture family dir |
| Integration | register-count, axis-leak, single-page, container-fork rows | fixtures + live tree |
| Gate | `php _build-gallery.php && php framework-audit.php && php tests/test-framework-audit.php` | 0 FAIL, WARN ≤ 4, ≥ 1193 OK |
| Visual | `visual-verification` over **every** strip at **every** anchor after each regeneration | R7 — sampling invalidated the last catalogue-wide change |

## Threat Matrix

N/A — no shell, subprocess, VCS/PR automation, executable-file classification, or process-integration
boundary is added. `framework-audit.php` reads globbed repo files only; the "routing" touched is
skill routing inside agent markdown, not request routing across a trust boundary.

## Migration / Rollout

Chained PRs on `feat/catalog-envato-grade` off `main @ 35a38b4`; revert the offending PR. Phase 1 is
spec-only; PR 2 ends at a self-consistent catalogue of seven. Regenerate the gallery after any revert
to restore the fingerprint. Redeploy `~/.claude/skills` after merge — it is a copy. Harvest the
measured token blocks of the 11 dying branded docs before PR 2 (R5).

## Open Questions

- [ ] `catalog-wrapper-integrity` says `TPL-E-07` "carries no demo brand" — it backs `bajura`
      (`_build-gallery.php:6148`). Spec text needs the correction; the disposition list drops to 4.
- [ ] Spec sentence "first column names a `COMP-*` id per data row" needs the catch-all clause.
- [ ] `alinea`'s photo set (3) must grow to ~7 and be re-briefed orthodontics → wellness coaching;
      this is the one place D1's "zero photographic cost" does not hold.
- [ ] Pilot responsive behaviour is still "pendiente de definir" — required before `qa-review`.
- [ ] `openspec/config.yaml:54` review budget says 400; the session value is 800 (not edited).
