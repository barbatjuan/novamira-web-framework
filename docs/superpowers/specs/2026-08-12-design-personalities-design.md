# Design: Visual Design Personalities Catalog

Target repo `C:/Users/Juan/temas/novamira-web-framework`. Scope: framework skills only
(`ux-design-system`, `web-templates`, `framework-audit`). No WordPress site is touched by this
change.

## Problem

`ux-design-system` and `web-templates` currently produce ONE visual language, shared across
every architecture archetype (`web-templates/references/design-system.md`, hard rule: "ONE
shared design system across every template"). The only style variance is `TGL-STYLE`
(minimalista / elegante-editorial / comercial), which touches radius and spacing density only —
never typography, color mood, motion character, or imagery treatment.

`ux-design-system/references/design-tokens.md` names one concrete typography example ("a
distinctive geometric (e.g. Space Grotesk)" + "a clean humanist sans (e.g. Manrope)"). With no
alternative offered anywhere in the skill, that example became the de facto default on every
build. Combined with the shared-token rule, two clients in different industries with different
architecture archetypes end up visually identical: same fonts, same one-accent-color logic, same
radius scale, same hover physics. The complaint ("todo genérico, todo igual") is accurate on both
axes — structure already varies via archetypes, but *look* never does.

## Intent

Give `ux-design-system` a curated catalog of 8 visual personalities — orthogonal to the
structural archetype `web-templates` already resolves — so the same architecture (say
`TPL-E-02` catalog-first) can ship as `Tech Precision` for an electronics client or `Fashion
Edit` for an apparel client, and come out looking like different studios built them. Reframe the
skill's own voice: it should behave like a senior visual designer making a justified call per
brand, not a filler defaulting to the one example it was ever given.

## Non-goals

- No migration path for already-delivered sites. This applies to new builds only (confirmed with
  user).
- No change to `elementor-core`, `divi-core`, or `html-mockup` internals — they already consume
  `--*` custom-property values without caring where those values came from. Their output contract
  (a resolved token spec) is unchanged; only what fills it changes.
- No change to the structural archetype catalog (`TPL-E-*`, `TPL-C-*`) or the page-set model.
- Not free-form improvisation per project — catalog model, confirmed with user, because it keeps
  a quality floor and stays auditable the same way the archetype catalog is.

## Architecture

`ux-design-system` gains the same 3-layer shape `web-templates` already uses, so the framework
stays consistent with itself:

- **CAPA 1 — Personalities** (`references/design-personalities.md`, new): 8 curated visual
  languages. Each is a complete, concrete token set (not roles — actual values), keyed by a
  `PERS-*` id mirroring the `TPL-*` convention.
- **CAPA 2 — Recommender** (new steps in `ux-design-system/SKILL.md`): reuse the brand signals
  and 2-4 references `web-templates`'s own CAPA 2 already collected (never re-ask the client for
  references) plus industry/tone, map to one `PERS-*`, present the pick with rationale, confirm.
- **CAPA 3 — Toggles**: fine-tune within the chosen personality, reusing `TGL-CARD-STYLE` /
  `TGL-CARD-IMG` from `web-templates/references/toggles.md` and adding two new ones,
  `TGL-IMAGERY` and `TGL-MOTION-INTENSITY` (see below). Replaces the old 3-option `TGL-STYLE`,
  which the personality pick now subsumes.

Personality and architecture are resolved independently and combined at the end — same pattern
as combining a `TPL-*` with its toggles today, just one more independent axis.

## Component: `design-personalities.md` (CAPA 1)

New file, same authority model as `web-templates/references/design-system.md`: this file is the
single source of truth for personality VALUES. `design-tokens.md` keeps explaining ROLES only.

Each personality entry ships: font pairing (2 families max, matches the existing role contract),
color mood + one derivation note anchored to the brand logo (same derivation algorithm already in
`design-tokens.md` §"Deriving a palette from a logo" — the personality changes *dominant/contrast
mood and accent character*, not the derivation steps), radius + shadow language, motion
intensity (duration/distance deltas against the existing base curve in `motion.md`, never a new
curve), imagery treatment, card recipe variant, and a one-line "fits" note.

| ID | Name | Character | Fits |
|----|------|-----------|------|
| `PERS-EDITORIAL` | Editorial | Serif of character + clean sans, high contrast, generous whitespace, dramatic photo crops | Brands with heritage/prestige to lean on |
| `PERS-BOLD-STARTUP` | Bold Startup | Heavy geometric sans, one saturated accent, large-shadow cards, fast direct motion | Young SaaS/DTC |
| `PERS-MINIMAL-SWISS` | Minimal Swiss | Neutral grotesque, near-monochrome + minimal accent, strict grid, 0–4px radii, near-imperceptible motion | Precision, product/data-first brands |
| `PERS-WARM-BOUTIQUE` | Warm Boutique | Rounded humanist, earth/pastel palette, large radii, warm photo or illustration, soft organic motion | Artisanal/local brands |
| `PERS-CORPORATE-TRUST` | Corporate Trust | Institutional sans, blue/grey + restrained accent, medium density, sober iconography | B2B/professional services |
| `PERS-FASHION-EDIT` | Fashion Edit | Fine serif or elegant condensed sans, black/white + one seasonal accent, full-bleed uncropped photography, near-0 radii, slow reveal motion | Apparel/fashion e-commerce |
| `PERS-TECH-PRECISION` | Tech Precision | Geometric + mono for specs, native dark mode, electric accent on near-black, subtle glass/gradient panels, small radii, fast precise motion | Electronics/gadgets |
| `PERS-PERFORMANCE-ENERGY` | Performance Energy | Condensed/athletic display, diagonal section cuts, saturated accent, dynamic action photography, snappy motion | Sports/activewear |

Radius, spacing scale STEPS, and container widths stay the shared structural tokens from
`web-templates/references/design-system.md` (the scale itself doesn't change per personality —
only which step each element lands on, and shadow presence/weight). Motion always derives from
the existing `cubic-bezier(.22,1,.36,1)` base curve in `motion.md`; a personality only tunes
duration and travel distance within the ranges that file already documents, never invents new
physics.

## Component: `ux-design-system/SKILL.md` changes

- New opening framing (Hard Rules preamble): the skill acts as a senior visual designer, not a
  filler. Every typography/color/motion choice must trace to a brand signal or reference the
  client gave — "the safe default" is not an acceptable justification. No example given in this
  skill's reference files may be treated as an implicit default; if the recommender can't justify
  a `PERS-*` pick against real signals, it asks one more clarifying question instead of guessing.
- New Execution Step, before reading `design-tokens.md`: run CAPA 2 — reuse
  `web-templates`'s brand signals + references, recommend one `PERS-*`, confirm with the client.
- Existing steps (read `design-tokens.md`, `motion.md`, `layout-patterns.md`) now read as "apply
  the roles using the chosen personality's values" rather than the single implicit set.

## Component: toggles (CAPA 3)

Add to `web-templates/references/toggles.md` (shared table, same convention as existing rows):

| ID | Question | Options | Affects | Applies in |
|----|----------|---------|---------|------------|
| `TGL-IMAGERY` | ¿Fotografía, ilustración o tratamiento gráfico? | foto / ilustración / gráfico | imagery treatment | todas |
| `TGL-MOTION-INTENSITY` | ¿Qué tan marcado el motion? | sutil / default (personalidad) / audaz | hover/motion deltas | todas |

`TGL-STYLE` (minimalista / elegante-editorial / comercial) is removed — the personality pick now
carries that decision, at higher fidelity. `TGL-CARD-STYLE` and `TGL-CARD-IMG` are unchanged and
reused as-is; a personality supplies their *default* answer, the client can still override via the
existing toggle flow.

## Component: hard-rule rewrite

`web-templates/references/design-system.md`, replace:

> Tokens compartidos por TODAS las plantillas (ecommerce y corporate). Se definen una vez y se
> cambian globalmente al migrar a un cliente nuevo.

with: the token STRUCTURE (roles, scale steps, breakpoints) is shared by every archetype; token
VALUES come from the `PERS-*` chosen in `ux-design-system` CAPA 2, independent of which `TPL-*`
was chosen. Same file, same authority over names/values — it now documents the shared *shape*,
`design-personalities.md` documents the *values* the shape gets filled with per project.

`ux-design-system/references/design-tokens.md`: remove the "e.g. Space Grotesk" / "e.g. Manrope"
example line; replace with a pointer to `design-personalities.md`'s 8 concrete pairings.

## Downstream: `html-mockup`, `elementor-core`, `divi-core`

No changes. All three already consume resolved `--*` token values without embedding assumptions
about which font/palette arrives — confirmed by grep (their only Space-Grotesk/Manrope
references are inside two illustrative asset mockups, not skill logic). Their handoff contract
(`ux-design-system` → `html-mockup` → builder-core) is unchanged in shape; only the values
flowing through it now vary per project.

## Component: `framework-audit` verifier

New deterministic check in `skills/framework-audit/assets/framework-audit.php`, following this
skill's own rule ("every rule gets a verifier or an admitted gap"):

- `design-personalities.md` exists and defines all 8 `PERS-*` ids with every required field
  (font pairing, color mood, radius/shadow, motion intensity, imagery treatment, card recipe,
  fits-note) present and non-empty.
- `design-tokens.md` no longer contains a hardcoded single font-family example outside
  `design-personalities.md` (regression guard against the exact drift that caused this problem).
- `ux-design-system/SKILL.md` references the CAPA 2 recommender step and
  `design-personalities.md` in its Execution Steps / References sections.

FAIL if any of the three is false; this is deterministic, not a JUDGE row.

## Testing / verification plan

- `php skills/framework-audit/assets/framework-audit.php --strict` — new checks above must PASS.
- `php tests/test-container-hygiene.php` — unaffected, still green (no container/build-gate
  logic touched).
- Manual: run `web-templates` → `ux-design-system` end-to-end for two fabricated briefs in
  different industries with the same architecture archetype (e.g. both `TPL-E-02`) and confirm
  the resulting token specs differ meaningfully (font pair, palette mood, motion) — this is the
  actual acceptance test for "no longer generic."

## Risks / open questions

- 8 personalities is a starting catalog, not a ceiling — expect to add more (e.g. hospitality,
  wellness) as real projects surface gaps; adding one is a self-contained edit to
  `design-personalities.md` plus a `PERS-*` row, no other file changes.
- The CAPA 2 recommender making a *wrong* personality call is a judgment risk no verifier can
  catch mechanically — mitigated by requiring explicit confirmation with the client before
  handoff to `html-mockup`, same gate the architecture recommender already uses.
