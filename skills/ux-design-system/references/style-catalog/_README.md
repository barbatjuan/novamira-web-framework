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

### Known gap: `nm_axes()`'s ground positions are stale, found this session

`framework-audit.php`'s `nm_axes()` (`:1037-1083`) still lists only 4 ground positions — `paper`,
`warm`, `cool`, `ink` — never widened to the 9 `design-system.md`'s own Ground table has carried
since style-catalog PR 3a. This does **not** FAIL the real repo today: `RT_STYLE_TOO_SIMILAR` still
parses `design-personalities.md`, not `STY-*.md` (unchanged since PR 4a — see "PR 4c" below), and
`design-personalities.md`'s 5 anchors only ever used the original 4 ground positions.

It **will** FAIL the moment a future PR repoints `RT_STYLE_TOO_SIMILAR`/`RT_PERS_BAD_AXIS` to read
`STY-*.md` instead: 3 of these 8 entries (`STY-TECH-SAAS`, `STY-DARK-LUXURY`, `STY-NEO-BRUTALIST`)
name a ground position (`ink-cool`, `ink-warm`, `saturated`) `nm_axes()` does not yet recognise, and
`RT_PERS_BAD_AXIS` would FAIL all three and silently exclude them from every distinctness gate.
Widening `nm_axes()`'s ground list to the 9 real positions is not itemised in any remaining
`tasks.md` entry (Slice 4's 4c.2 lists `SKILL.md`/`design-tokens.md`/`layout-patterns.md`/
`motion.md`, not `framework-audit.php` itself) — flagged here, the same way PR 4a carried forward
the chassis-anchor hardcode, so it is not lost before whichever PR performs that repoint.

## PR 4c — retiring `design-personalities.md` (not in this PR)

`design-personalities.md` is deleted only after the 8-entry catalog is complete and passes
`RT_STYLE_TOO_SIMILAR` pairwise (`tasks.md` 4c.1–4c.3). `SKILL.md`'s stale "Four anchors" line
(`:22`) and the 5→8-axis references in `design-tokens.md`/`layout-patterns.md`/`motion.md` are
fixed in that PR too, not this one.

## The open risk this port does not close

Both generated chassis (`assets/chassis/corporate.html`, `assets/chassis/ecommerce.html`) are
stamped `/* Anchor: PERS-INSTITUTIONAL */` (PR 1d). The marker is truthful — their `:root`
genuinely resolves to `contained` + `standard`, `PERS-INSTITUTIONAL`'s own axis line — but **a
chassis pinned to one anchor is the defect this entire change exists to kill**
(`mockup-guide.md:436-447`: every corporate project shipped `PERS-INSTITUTIONAL` because nobody was
asked). Porting `PERS-INSTITUTIONAL` to `STY-INSTITUTIONAL` in this PR does not touch that
hardcode — the stamp still names the old id, not a resolved-per-project choice.

**Slice 4 does not close until the chassis anchor is resolved from the selected `STY-*` rather than
hardcoded.** PR 4b (this PR) did not touch it either — it is not itemised in `tasks.md` 4b.1–4b.4 or
4c.1–4c.3, so it remains open past PR 4c too, carried forward here again so it is not lost.

## Backlog

`_backlog.md` records the movements deferred from v1, with the reason each is deferred.
