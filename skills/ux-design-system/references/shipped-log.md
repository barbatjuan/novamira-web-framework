# Shipped log — the measured half

This is the other half of a memory `skills/blind-judges/references/corpus.md` already half-built.
Two files, one event, one write moment, never a third.

| | Measures | Written by | Read offline by |
|---|---|---|---|
| `corpus.md` | what a delivery LOOKED like — three screenshots plus Judge B's eight perceptual attributes | the orchestrator, after both verdicts land | Judge A only, over `corpus/` |
| `shipped-log.md` (here) | what a delivery WAS, structurally — the resolved `STY-*` id, the axes it shipped with, and the toggles its style precharged | the orchestrator, after both verdicts land — same moment | `RT_STYLE_REPEATS_RECENT`, `RT_STYLE_UNRESOLVED_DEFAULT`, `RT_STYLE_PRECHARGE_UNSHIPPED` |

**The judges never write here either.** Both judges are read-only over `corpus/` — `corpus.md`'s
own rule. Neither one opens this file at all. The orchestrator is the only writer, for both files,
in the same pass, so nothing a judge saw can be altered by the same write that judged it, and the
measured half can never silently drift from the seen half.

**Why two files and not one.** `corpus.md` holds perceptual JPEGs a screenshot tool captured;
`shipped-log.md` holds the structural facts an offline audit can parse without opening an image.
They describe the same delivery from two vantage points, joined by `Date` + `Client` here matching
`Date` + `Project` there — read side by side, never merged by hand.

## Why this file exists at all

`framework-audit.php` runs offline, over this repo, never against a live WordPress site. It cannot
call `es_manifest_read()` — a WordPress option, reachable only from inside a real build — and it
cannot open a delivered mockup either: `corpus.md` already states why none is stored here ("client
work does not belong in this repository"). This table is the one artifact the offline audit CAN
see that says, for a given delivery, whether a style was ever actually resolved.

## Row shape

One row per delivery, **appended, newest last** — never reordered, never edited in place. A
re-delivery to the same client gets its own new row rather than overwriting the old one, because
history is this file's whole job (contrast `es_manifest_record('design', …)`, which overwrites the
live `design` section on purpose — that section only needs to say what is true now).

| Column | Holds |
|---|---|
| Date | `yyyy-mm-dd`, same day as the matching `corpus.md` entry |
| Client | the project id — same string as `corpus.md`'s `Project` column |
| Style | the resolved `STY-*` id, or blank if none was ever recorded |
| Ground | the resolved ground family (one of the nine `design-system.md` positions) |
| Accent | a coarse hue bucket ("warm", "cool", "olive", …), not the literal hex — the same grain as `corpus.md`'s own `Accent` attribute |
| Scale | the resolved scale position |
| Chassis | which starting chassis this delivery built from: `corporate` or `ecommerce` |
| Toggles | `TGL-ID=value; TGL-ID2=value2; …` — every toggle the resolved `STY-*` precharges (`web-templates/references/toggles.md`), at the value it actually shipped with, `;`-joined. Only the style's OWN declared list, never the full 39-toggle catalogue — a style declaring 2 writes 2 |
| Route | `bespoke` for a `ROUTE-BESPOKE` delivery (style-catalog PR 6), blank for a catalog delivery. The one cell that tells a deliberate zero-precharge build apart from an unresolved default — both leave `Style` blank |

## Who writes a row, and when

The orchestrator, after both judge verdicts land — never a judge, never mid-build. Identical
discipline to `corpus.md`'s own index, in the same pass, so the two histories cannot drift apart.

## Three audit rules read this file

- **`RT_STYLE_REPEATS_RECENT` (WARN, `house-rules.md:31`)** — takes the last 5 rows; WARNs if the
  newest row's `Style` also appears in the 4 before it. Five matches the window `corpus.md`'s Judge
  A is shown, on purpose (`corpus.md` "Retention"), so the measured half and the seen half never
  disagree about what "recent" means. A repeat is a judgment call, so this only advises; a repeat
  outside the 5-row window is invisible, exactly like an entry Judge A is never shown.
- **`RT_STYLE_UNRESOLVED_DEFAULT` (FAIL)** — FAILs any row whose `Chassis` is filled but whose
  `Style` is blank: a delivery that named which chassis it started from with no style ever resolved
  for it. That is the offline-visible shadow of the exact defect `mockup-guide.md:436-447`
  recorded — every corporate site shipped `PERS-INSTITUTIONAL` "not because anyone chose them but
  because nobody was asked to." A blank `Style` here means the intake's answer either never
  happened or never reached this ledger; either way, the project shipped on the untouched default.
  **Exception (style-catalog PR 6):** a row whose `Route` reads `bespoke` never trips this row even
  with `Style` blank — `ROUTE-BESPOKE`'s own first requirement is zero precharge, so a resolved
  `STY-*` id is never expected there; its own gate is `RT_BESPOKE_UNDECLARED` before build, not
  this one after delivery.
- **`RT_STYLE_PRECHARGE_UNSHIPPED` (FAIL)** — for a row WITH a resolved `Style`, reads that
  `STY-*.md`'s own "Toggle precharge" table and FAILs, naming the toggle and the style, for every
  declared toggle this row's `Toggles` column does not show shipped at the declared value. Root
  cause 6 (proposal): the demo gallery varied structure and look but moved exactly one toggle off
  default across 67 strips — "configuration never did." **No universal floor, on purpose**: the
  check is always against the RESOLVED style's own declared list, never a fixed count — a style
  declaring 2 toggles is exactly as satisfied by 2 shipped as a style declaring 6 is by 6.

## Empty ledger

No delivery has been recorded yet.

| Date | Client | Style | Ground | Accent | Scale | Chassis | Toggles | Route |
|---|---|---|---|---|---|---|---|---|
