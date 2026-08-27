# Design: Perceptual Axes — making the personalities actually differ

Target repo `C:/Users/Juan/temas/novamira-web-framework`. Scope: framework skills only
(`ux-design-system`, `html-mockup`, `framework-audit`). No WordPress site is touched.

This is the **second attempt at the same problem**. `docs/superpowers/specs/2026-08-12-design-personalities-design.md`
created the 8-personality catalog to fix "todo genérico, todo igual". Two days later the
complaint is unchanged, so that spec's diagnosis was incomplete. This one starts from
measurement instead of from a category list.

## Problem, measured

Three sites the user names as the bar were measured through the browser (computed styles, not
opinion), alongside the framework's own shipped mockup:

| | Display size | Display `line-height` | Body | Ground | Families |
|---|---|---|---|---|---|
| `legroma` (ours) | 30px | 1.87 | 16 Roboto | pure white | Roboto + Poppins + **Jost** (leak) |
| `albatorrubia.com` | **88px** | **0.80** | 20 Montserrat | **black** | Agdasima + Montserrat |
| `dakstone.com` | 36px | 1.22, `-0.32px` | 16 DM Sans | **cream `#FFF3E3`** | Urbanist + DM Sans |
| `corporate-mockup.html` | 56px cap | 1.15 | 16–18 | pure white | **`system-ui`** |

And measured inside the framework itself:

- **The type scale is not a scale.** Step ratios are `1.400 · 1.429 · 1.556 · 1.286 · 1.167` —
  no system, numbers chosen one at a time. A designed scale has one ratio, or two deliberate
  ones (display vs text).
- **Space is not fluid.** Three `clamp()` calls in the whole mockup; `--sp-*` are fixed rems.
  The docs describe a rhythm that steps mobile → tablet → desktop; the tokens do not do it.
- **There are zero shadow tokens.** `--shadow` does not appear anywhere in the repo. Shadows are
  hardcoded in two places in the mockup.
- **`system-ui` is the shipped default family**, and no personality names a real typeface — they
  name categories ("a high-contrast serif with real character").
- The spacing discipline is genuinely good: **zero loose px** in padding/margin anywhere in the
  mockup. Everything goes through the scale.

## Why the first attempt did not land

The 8 personalities vary the **skin** and share the **skeleton**. Every one of them inherits the
same type ratio, the same spacing rhythm, the same composition, and the same card. What they
change is colour mood, radius and adjectives.

Ranked by perceptual impact, what actually makes two sites feel different:

| | Axis | Today |
|---|---|---|
| 1 | Type scale ratio + display leading | identical across all 8 |
| 2 | The ground (white / warm / cool / dark) | identical: white |
| 3 | Density (spacing rhythm) | identical |
| 4 | Composition | identical |
| 5 | Elevation strategy | not expressible — no tokens |
| 6 | Accent colour | **the only thing that varies** |

The system varies #6 and shares #1–#5. That is the whole bug. Worse, the catalog **promises
differences the token layer cannot express**: `PERS-EDITORIAL` says "shadows near-absent" and
`PERS-BOLD-STARTUP` says "visible soft shadow even at rest", with no shadow token in existence.
That is the same failure class this repo spent 2026-08-14 removing from its PHP — a rule
described where it cannot be executed.

## Intent

Make the personality a position on five **perceptual axes**, each backed by a token, with the
four anchors placed deliberately far apart. Add a dialogue so the designer asks the client
questions whose answers place the project on those axes — including positions between anchors.
Then prove it: two personalities rendered over the same content and the same structure must be
unmistakably different, and a check must fail when two anchors sit too close.

## Non-goals

- No migration of delivered sites. New builds only.
- No change to `elementor-core` / `divi-core` internals: they consume resolved `--*` values and
  do not care where those came from.
- No change to the structural archetypes (`TPL-*`) or the page-set model.
- **Not derived from the references.** The three measured sites calibrate the RANGE — evidence
  that `lh 0.80` at 88px reads confident, that a committed non-white ground reads as a decision.
  The anchors pick different points inside that range. Explicitly confirmed with the user: the
  references are references, not templates to clone.
- The accent colour stays **out** of the personality: it derives from the client's brand, per
  `design-tokens.md`'s existing derivation steps.

## The five axes

Each axis is a token set with four named positions. Values below are the contract.

### Axis 1 — Scale (`--type-ratio`, `--display-lh`, `--fs-h1-max`)

| Position | ratio | display `lh` | h1 max |
|---|---|---|---|
| `contained` | 1.200 | 1.25 | 48px |
| `classic` | 1.333 | 1.10 | 64px |
| `editorial` | 1.500 | 0.95 | 88px |
| `monumental` | 1.618 | 0.82 | 120px |

The whole type scale derives from `--type-ratio` by exponentiation from `--fs-body`, so a step is
never a hand-picked number again. Each heading token is
`clamp(<ratio^n × body>, <n·1.1vw + 1rem>, <ratio^n × body × position-cap-factor>)` where `n` is
the step (h3=1, h2=2, h1=3) and the h1 upper bound is `--fs-h1-max` exactly. Concretely at
`monumental`: `1.618³ × 16px ≈ 68px` floor, `120px` cap. At `contained`: `1.2³ × 16px ≈ 28px`
floor, `48px` cap. Body stays `1rem`–`1.25rem` across all positions: the range is what changes,
not the reading size.

Measured justification: `albatorrubia` sits at `editorial`/`monumental` (88px, 0.80). The current
mockup sits **below `contained`** (56px, 1.15) — the framework has never shipped even its
smallest position.

### Axis 2 — Ground (`--c-bg`, `--c-bg-alt`, `--c-text` derivation)

| Position | `--c-bg` | Notes |
|---|---|---|
| `paper` | pure white | the only position that is also today's default |
| `warm` | cream / ivory (`#FFF3E3`-family) | contrast pushed to a warm near-black |
| `cool` | very light blue-grey | contrast a deep blue-grey, not pure black |
| `ink` | near-black | text near-white; the accent must re-derive for contrast on dark |

Choosing `paper` is a decision and must be recorded as one. The failure today is that white is
what happens when nobody decides.

### Axis 3 — Density (`--sp-scale`)

One multiplier over the entire existing `--sp-*` scale: `compact 0.8` · `standard 1.0` ·
`generous 1.35` · `monumental 1.7`. Rhythm consistency is preserved by construction — every token
moves together — while the felt airiness changes completely. Section padding additionally becomes
fluid (`clamp()`), which is what the docs already claim and the tokens do not do.

### Axis 4 — Composition (pattern set)

`centered` · `asymmetric` (off-centre content, image bleeding one edge) · `strict-grid` ·
`broken-grid` (elements deliberately crossing the grid). Implemented as which section blueprints
`layout-patterns.md` offers, not as free-form improvisation.

### Axis 5 — Elevation (`--elev-rest`, `--elev-hover`)

`none` (whitespace only) · `hairline` (1px border, no shadow) · `soft-shadow` ·
`accent-glow`. These tokens do not exist today; creating them is what lets a personality say
"no shadows" and have it be true.

## The four anchors

Anchors are positions, not adjectives. **No two anchors may share more than one axis.**

| Anchor | Scale | Ground | Density | Composition | Elevation |
|---|---|---|---|---|---|
| `PERS-EDITORIAL` | `editorial` | `paper` | `generous` | `asymmetric` | `none` |
| `PERS-MATTER` | `classic` | `warm` | `standard` | `strict-grid` | `hairline` |
| `PERS-DIRECT` | `monumental` | `ink` | `compact` | `broken-grid` | `accent-glow` |
| `PERS-INSTITUTIONAL` | `contained` | `cool` | `standard` | `centered` | `soft-shadow` |

Pairwise shared axes: EDITORIAL/MATTER 0, EDITORIAL/DIRECT 0, EDITORIAL/INSTITUTIONAL 0,
MATTER/DIRECT 0, MATTER/INSTITUTIONAL 1 (density), DIRECT/INSTITUTIONAL 0. Maximum 1 — the
constraint holds with room to spare, which is the argument for four rather than eight: eight
anchors over five four-position axes force several pairs to sit close, and the close pairs are
the ones that look the same.

`PERS-MATTER` is named for clients who sell material (stone, wood, food). `PERS-DIRECT` is the
loud one. The eight old names are replaced, not renamed: the old catalog's failure was that its
distinctions were adjectival.

## The dialogue

`ux-design-system` asks **three to five questions in business terms**, never "which personality do
you want". They are not one-per-axis: a single business answer usually moves several axes at once,
which is why the dialogue is short. Worked example for a stone fabricator — *"should the site feel
like a material catalogue, or like a gallery of finished work?"* — catalogue moves toward
`strict-grid` + `standard` + `warm`; gallery moves toward `asymmetric` + `generous` + `paper`.

Each question is precharged with a default from the industry `project-context` / `web-templates`
already reported, and the client confirms or overrides — the existing CAPA 2/3 pattern, applied to
axes instead of to a catalog. **Every axis must end resolved**: any axis the answers did not reach
gets its own explicit question rather than a silent default, because an unasked axis falling back
to the same value on every project is precisely how the current system produces identical sites.

The resolved position is recorded into the project manifest (`es_manifest_record('design', …)`)
so a second session does not re-derive it. That path already exists and is tested.
> **Unfulfilled as of `manifest-truth-repair` (2026-08-27)**: no skill calls `es_manifest_record('design', …)` today; `design` is a manifest section with a name (`es_manifest_sections()`, `elementor-core/assets/es-builder.php`) and no writer. Read this paragraph as intent, not as current behavior.

## The verifier

New row type **`RT_PERS_TOO_SIMILAR`** in `framework-audit`: parse the anchor table from
`design-personalities.md`, compare every pair, and **FAIL** when two anchors share more than one
axis position. Also FAIL when an anchor names a position no axis defines (a typo silently
creating a fifth position).

This is the point of the whole spec made executable. Without it, "the personalities are
different" is a claim in a document, which is exactly what the 2026-08-12 spec already tried and
what this one exists to correct.

Per `CONTRIBUTING.md`, the row type must be added to `ROW_TYPES`, documented in the row-type
table, and exercised by at least one fixture in `tests/test-framework-audit.php` — the coverage
ratchet fails otherwise.

## Real typefaces

Each anchor names two real, OFL-licensed families (headings + body). They are embedded in the
mockup assets as **subsetted `woff2` base64 data URIs**, because the Artifact CSP forbids remote
fonts and a mockup showing `system-ui` while the built site shows a display face lies about the
single most important variable — and that file is the approval contract. Latin subsets run
~20–30KB per family; the mockup must stay under the Artifact's 16MB ceiling with enormous margin.

## What gets built

1. Axis tokens in `web-templates/references/design-system.md` (the naming authority) and their
   roles in `ux-design-system/references/design-tokens.md`.
2. `design-personalities.md` rewritten: four anchors as axis positions, with the real families.
3. The per-axis dialogue in `ux-design-system/SKILL.md` Execution Steps.
4. `--elev-*` tokens and fluid section padding in `motion.md` / the mockup assets.
5. `RT_PERS_TOO_SIMILAR` + its fixture.
6. **Two full mockup assets** — `PERS-EDITORIAL` and `PERS-DIRECT`, the most distant pair — over
   identical content and identical section structure.

## How it is proven

The falsifiable criterion, agreed with the user: **the same content and the same structure,
rendered under two personalities, must be unmistakably different at a glance.** The two mockups
in item 6 are that test, and they are built from one content file precisely so the difference
cannot come from the copy.

Machine-checkable alongside it: `RT_PERS_TOO_SIMILAR` for the anchor table, and the existing word
budget / marker grammar for every file touched. `elementor-core` sits at 598 of a 600-word
ceiling and `woocommerce` at 597 — neither is touched by this change, and nothing here may push
`ux-design-system` (552) past 600 either.

## Decomposition

This is two implementation cycles, not one, and the boundary is where the proof begins:

- **Phase A — the system.** Items 1–5: axis tokens, the four anchors, the dialogue, `--elev-*`
  and fluid padding, `RT_PERS_TOO_SIMILAR` and its fixture. Self-contained and verifiable on its
  own: the audit gate proves the anchors are far apart, and no mockup is needed to prove that.
- **Phase B — the proof.** Item 6: the two mockup assets over one shared content file. It
  consumes Phase A's tokens and cannot start before they exist.

Phase A gets its own plan first. Phase B is planned after Phase A lands, because building the
mockups will teach things about the tokens that no amount of specifying will.

## Risks

- **Four positions per axis may be too coarse for Composition.** Mitigated by treating the
  composition axis as a set of blueprint choices rather than a numeric value; if a fifth
  composition is needed later it is an additive change, and `RT_PERS_TOO_SIMILAR` will keep the
  anchors honest regardless.
- **The two mockups are the expensive item.** They are also the only proof the system works, so
  they are not optional; if scope must be cut, it is cut elsewhere.
