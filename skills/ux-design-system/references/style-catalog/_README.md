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

## PR 4a — format and the 5 ports (this PR)

This PR changes **format, not design**: the 5 existing anchors are ported into this shape
unchanged. Any position that reads wrong belongs to a design conversation, not a silent fix here.

| `STY-*` | Ported from | Status |
|---|---|---|
| `STY-EDITORIAL` | `PERS-EDITORIAL` | Ported |
| `STY-DIRECT` | `PERS-DIRECT` | Ported |
| `STY-MATTER` | `PERS-MATTER` | Ported |
| `STY-VITRINE` | `PERS-VITRINE` | Ported |
| `STY-INSTITUTIONAL` | `PERS-INSTITUTIONAL` | Ported |

`design-personalities.md` is **not deleted by this PR** — `RT_CATALOG_UNMENTIONED` still requires
`ux-design-system/SKILL.md` to mention it, and the audit's `RT_PERS_*` rows still parse it as the
source of truth for `RT_STYLE_TOO_SIMILAR` until the rows repoint. Its content is unchanged here.

## PR 4b — 3 new styles + fonts (not in this PR)

Three more entries land in PR 4b to complete the 8, each budgeting a SIL OFL face or a re-weighted
embedded family (`Archivo`/`Archivo Expanded` is the existing pattern for the latter). Not named or
authored here — see `tasks.md` 4b.1–4b.4.

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
hardcoded.** That work is PR 4b or 4c, not this one — carried forward here so it is not lost.

## Backlog

`_backlog.md` records the movements deferred from v1, with the reason each is deferred.
