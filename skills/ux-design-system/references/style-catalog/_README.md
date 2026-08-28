# Style Catalog

`STY-*.md` replaces `design-personalities.md`. Eight entries ship in v1 — locked by the font
budget, not by taste: `nm_font_registry()` (`skills/html-mockup/assets/fonts/_fonts.php:63-69`)
embeds exactly 7 faces, and `RT_MOCKUP_FONT_NOT_EMBEDDED` FAILs any mockup that names a family
outside that list or reused at a weight/stretch it does not declare. Twelve styles were originally
proposed; four move to `_backlog.md` for that reason.

Each entry declares a full position on all 8 axes (scale, ground, density, composition, elevation,
accent, chassis, ornament — `web-templates/references/design-system.md` is the value authority for
each position) and a **toggle precharge**: which of `web-templates/references/toggles.md`'s
catalogue it moves off default, and to what. This is deliberate and per-style, not a universal
minimum count — an arbitrary N becomes a box to tick rather than a design decision. A style that
precharges nothing has not been finished.

## PR 4a — format and the 5 ports

This PR changed **format, not design**: the 5 existing anchors were ported into this shape
unchanged. Any position that reads wrong belongs to a design conversation, not a silent fix here.

| `STY-*` | Ported from | Status |
|---|---|---|
| `STY-EDITORIAL` | `PERS-EDITORIAL` | Ported |
| `STY-DIRECT` | `PERS-DIRECT` | Ported |
| `STY-MATTER` | `PERS-MATTER` | Ported |
| `STY-VITRINE` | `PERS-VITRINE` | Ported |
| `STY-INSTITUTIONAL` | `PERS-INSTITUTIONAL` | Ported |

## PR 4b — 3 new styles + fonts (this PR) — catalog is now 8/8

| `STY-*` | Status |
|---|---|
| `STY-TECH-SAAS` | Authored |
| `STY-DARK-LUXURY` | Authored |
| `STY-NEO-BRUTALIST` | Authored |

Chosen from the proposal's remaining candidates (`STY-SWISS`, `STY-QUIET-LUXURY`,
`STY-SOFT-MINIMAL`, `STY-DARK-LUXURY`, `STY-TECH-SAAS`, `STY-BRUTALIST-DARK`, `STY-NEO-BRUTALIST`,
`STY-ORGANIC`, `STY-MAXIMALIST`) to maximise separation from the 5 ported entries under the 8-axis
gate, while the font budget could still carry them. Everything not picked stays in `_backlog.md`
with the same reasoning discipline PR 4a used — most of the unpicked candidates (`STY-SWISS`,
`STY-QUIET-LUXURY`, `STY-SOFT-MINIMAL`) read as close variations on ground already covered by
`STY-EDITORIAL`/`STY-MATTER`/`STY-INSTITUTIONAL` rather than genuinely new axis territory, and
`STY-MAXIMALIST`/`STY-BRUTALIST-DARK` overlap the exact ground `STY-NEO-BRUTALIST` and
`STY-DARK-LUXURY` were chosen to cover, once one entry from each pair is picked.

### Ground families: the room PR 3a opened, spent here

`paper`, `warm`, `cool` and `ink` were the only ground positions the 5 ported entries used (`ink`
twice, `STY-DIRECT`/`STY-VITRINE`). PR 3a's other five — `cream`, `earth`, `saturated`, `ink-warm`,
`ink-cool` — sat unused until this PR: `STY-TECH-SAAS` takes `ink-cool`, `STY-DARK-LUXURY` takes
`ink-warm`, `STY-NEO-BRUTALIST` takes `saturated`. `cream` and `earth` still sit unclaimed — real
room for a future entry, not a gap this PR needed to close.

### Font decision, made explicit: reuse only, no new face embedded

Every new entry reuses one of the 7 faces `nm_font_registry()` already embeds
(`_fonts.php:63-69`), each in a role that family has not carried before in this catalog:

| `STY-*` | `--font-primary` | New role | `--font-secondary` |
|---|---|---|---|
| `STY-TECH-SAAS` | Inter Tight @ 700 | first PRIMARY use (was secondary-only: `STY-EDITORIAL`, `STY-VITRINE`) | Source Sans 3 |
| `STY-DARK-LUXURY` | Fraunces @ 700 | same family as `STY-EDITORIAL`'s primary, reversed onto a dark ground at the heaviest weight — the `DM Sans` cross-style-reuse precedent (`STY-MATTER` secondary / `STY-VITRINE` primary) extended to a third family | Inter Tight |
| `STY-NEO-BRUTALIST` | Archivo @ 700, `100%` stretch | first PRIMARY use (was secondary-only: `STY-DIRECT`) — the un-widened twin of `STY-DIRECT`'s own `Archivo Expanded` primary | DM Sans |

**Embedding a new SIL OFL face was considered and rejected for this PR, on a disclosed practical
ground**: `_fonts.php`'s own header requires every embedded face to carry verified provenance — a
real source URL, a sha256, a copyright line, all recorded in `_fonts.md` before the woff2 ships.
That verification needs a network fetch this apply session does not have. Reuse was therefore not
a stylistic default reached without considering the alternative; it was the only path this session
could execute honestly. Nothing here forecloses a future PR embedding an 8th face for a backlog
entry once that verification step is actually run.

Reuse was steered to avoid the failure the session brief named directly — "eight styles that look
alike because they share four faces" — by giving each reused family a role it has not held before
(new weight, new pairing, new register) rather than repeating an existing pair verbatim. `Inter
Tight` now touches four of eight entries in some role (two as secondary pre-existing, one new
primary, one new secondary) — the single most-reused face in the v1 catalog, and disclosed as such
rather than left to be discovered: every face in a 7-face, 16-role catalog is reused by
construction, and Inter Tight's versatility (it reads well both as a tight display face at 700 and
as a plain secondary) makes it the natural attractor, not a sign the roster is thin.

### The 8-axis separation table — all 28 pairs, max shared = 2

Positions listed in axis order: scale · ground · density · composition · elevation · accent ·
chassis · ornament.

| `STY-*` | Axes |
|---|---|
| `STY-EDITORIAL` | editorial · paper · generous · asymmetric · none · none · rule-divided · rule |
| `STY-DIRECT` | monumental · ink · compact · broken-grid · accent-glow · gradient · bare · none |
| `STY-MATTER` | classic · warm · standard · strict-grid · hairline · tinted-field · bordered · texture |
| `STY-VITRINE` | editorial · ink · monumental · strict-grid · soft-shadow · metallic · soft-carded · none |
| `STY-INSTITUTIONAL` | contained · cool · standard · centered · soft-shadow · reserved · carded · illustration |
| `STY-TECH-SAAS` | contained · ink-cool · generous · strict-grid · accent-glow · duotone · strict-grid · none |
| `STY-DARK-LUXURY` | monumental · ink-warm · generous · centered · accent-glow · metallic · layered · illustration |
| `STY-NEO-BRUTALIST` | monumental · saturated · compact · asymmetric · none · polychrome · hard-shadow · pattern |

Shared-position count for every one of the 28 pairs (re-verified by hand, axis by axis):

| Pair | Shared |
|---|---|
| EDITORIAL / DIRECT | 0 |
| EDITORIAL / MATTER | 0 |
| EDITORIAL / VITRINE | 1 (scale) |
| EDITORIAL / INSTITUTIONAL | 0 |
| EDITORIAL / TECH-SAAS | 1 (density) |
| EDITORIAL / DARK-LUXURY | 1 (density) |
| EDITORIAL / NEO-BRUTALIST | 2 (composition, elevation) |
| DIRECT / MATTER | 0 |
| DIRECT / VITRINE | 2 (ground, ornament) — the pre-existing PR 2b boundary case, unchanged |
| DIRECT / INSTITUTIONAL | 0 |
| DIRECT / TECH-SAAS | 2 (elevation, ornament) |
| DIRECT / DARK-LUXURY | 2 (scale, elevation) |
| DIRECT / NEO-BRUTALIST | 2 (scale, density) |
| MATTER / VITRINE | 1 (composition) |
| MATTER / INSTITUTIONAL | 1 (density) |
| MATTER / TECH-SAAS | 1 (composition) |
| MATTER / DARK-LUXURY | 0 |
| MATTER / NEO-BRUTALIST | 0 |
| VITRINE / INSTITUTIONAL | 1 (elevation) |
| VITRINE / TECH-SAAS | 2 (composition, ornament) |
| VITRINE / DARK-LUXURY | 1 (accent) |
| VITRINE / NEO-BRUTALIST | 0 |
| INSTITUTIONAL / TECH-SAAS | 1 (scale) |
| INSTITUTIONAL / DARK-LUXURY | 2 (composition, ornament) |
| INSTITUTIONAL / NEO-BRUTALIST | 0 |
| TECH-SAAS / DARK-LUXURY | 2 (density, elevation) |
| TECH-SAAS / NEO-BRUTALIST | 0 |
| DARK-LUXURY / NEO-BRUTALIST | 1 (scale) |

**Maximum across all 28 pairs: 2.** `RT_STYLE_TOO_SIMILAR` FAILs at >2, so the full v1 catalog
clears the gate with no pair to spare on several — the same shape `STY-DIRECT`/`STY-VITRINE` already
established at 2/8 in PR 2b. This table is the authoritative claim for the real 8 entries; the
mechanism itself is locked at n=8 scale by a separate, disclosed-synthetic fixture (see "Known gap"
below for why it cannot yet run against these files directly).

### Closed gap: `nm_axes()`'s ground positions were stale, fixed in PR 4c (Task Zero)

`framework-audit.php`'s `nm_axes()` (`:1037-1083`) listed only 4 ground positions — `paper`,
`warm`, `cool`, `ink` — never widened to the 9 `design-system.md`'s own Ground table carried since
style-catalog PR 3a. It never FAILed the real repo before PR 4c: `RT_STYLE_TOO_SIMILAR` still parsed
`design-personalities.md`, not `STY-*.md`, and `design-personalities.md`'s 5 anchors only ever used
the original 4 ground positions — flagged first in PR 4b's own apply-progress, carried forward here
so it would not be lost before the repoint that would have tripped it.

style-catalog PR 4c widened `nm_axes()`'s ground list to all 9 real positions BEFORE repointing
`RT_STYLE_TOO_SIMILAR`/`RT_PERS_BAD_AXIS` to `STY-*.md` — the same audit-before-style ordering PR 2a/
2b already established for the axis registry itself, and the exact ordering that would have FAILed
3 of these 8 entries (`STY-TECH-SAAS`, `STY-DARK-LUXURY`, `STY-NEO-BRUTALIST` — `ink-cool`,
`ink-warm`, `saturated`) had the repoint landed first. Verified by running the real audit against
all 8 `STY-*.md` files with the widened registry: `RT_PERS_BAD_AXIS` silent, `RT_STYLE_TOO_SIMILAR`
clean.

## PR 4c — `design-personalities.md` retired

Deleted after the 8-entry catalog cleared `RT_STYLE_TOO_SIMILAR` pairwise, parsed directly from
`STY-*.md` rather than the old file (`tasks.md` 4c.1–4c.3). Every `RT_PERS_*`/`RT_STYLE_TOO_SIMILAR`/
`RT_CATALOG_UNMENTIONED` rule that used to parse `design-personalities.md` now globs
`style-catalog/STY-*.md` instead; `SKILL.md`'s stale "Four anchors" line (`:22`), the 5→8-axis gap
in `design-tokens.md`, and the two hand-authored proof mockups' `Anchor:`/citation text were all
repointed in the same PR. `layout-patterns.md` and `motion.md` were checked and needed no change —
their "four positions" mentions are about `composition`/`elevation`'s own position counts (still 4
each), not the total axis count.

## The open risk this port did not close alone — closed here, partially

Both generated chassis (`assets/chassis/corporate.html`, `assets/chassis/ecommerce.html`) were
stamped `/* Anchor: PERS-INSTITUTIONAL */` unconditionally for BOTH site types from PR 1a through
PR 4b — truthful for `corporate.html`, but ALSO the value `ecommerce.html` shipped, silently, since
`:root` was built from the exact same call for both. `mockup-guide.md:436-447` recorded the same
pattern for the two hand-maintained originals this generator replaced: every corporate project
shipped `PERS-INSTITUTIONAL` and every commerce one `PERS-MATTER`, "the tamest corner of the system,
and every client site started there."

style-catalog PR 4c resolves each site type's chassis anchor from `$CHASSIS_STYLE_BY_SITE` in
`_build-gallery.php` — a static, disclosed map (`corporate => institutional`, `ecommerce => matter`,
chosen from the exact historical evidence above) — and builds BOTH the `Anchor: STY-*` marker and
the `:root` tokens from that SAME resolved key, so the two can no longer independently drift the way
a hand-typed marker string always eventually does. The two site types now resolve to two DIFFERENT
styles, which alone breaks the "every corporate site starts identical" pattern this section used to
describe. **What remains explicitly open, past PR 4c:** this is still a fixed default per site TYPE,
not a per-PROJECT resolution — there is no client, no project and no manifest at generation time for
`_build-gallery.php` to resolve against, only two demo pages. `art-direction-ledger` (Slice 5) is
where `es_manifest_record('design', …)` persists a real project's resolved style; a real project's
chassis is still expected to be re-pointed once that writer exists, exactly as
`RT_MOCKUP_AXES_MISMATCH` already polices for any hand-edit today.

## PR 5b — the ledger, `references/shipped-log.md`

`art-direction-ledger` (Slice 5) closes the open risk above one step further: every delivery now
gets a row in `references/shipped-log.md`, the measured half of the two-part memory
`skills/blind-judges/references/corpus.md` half-built. `RT_STYLE_REPEATS_RECENT` reads it to WARN
on a style repeating within the last 5 deliveries; `RT_STYLE_UNRESOLVED_DEFAULT` reads it to FAIL a
delivery that names its starting chassis with no style ever resolved for it — the offline-visible
half of the exact "nobody was asked" defect this section already documented. See
`references/shipped-log.md` itself for the row shape and who writes it.

## Backlog

`_backlog.md` records the movements deferred from v1, with the reason each is deferred.

## PR 6 — `ROUTE-BESPOKE`, the from-scratch escape hatch

`_bespoke-route.md` documents the route for a project none of the 8 entries can express: zero
precharge, a `BSP-*.md` declaration (8 axes + wireframe, `RT_BESPOKE_UNDECLARED`) before
builder-core, no accessibility exemption, mandatory ledger registration, and a promotion path back
into this directory as a new `STY-*.md` — subject to `RT_STYLE_TOO_SIMILAR` like any other entry.
