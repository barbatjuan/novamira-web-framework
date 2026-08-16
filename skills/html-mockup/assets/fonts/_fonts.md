# Font manifest

The typefaces every mockup in this skill NAMES, the bytes that make them render,
and the licence that lets this repository carry them.

## Why this file exists

Until these files landed, every mockup here named real families and shipped none
of them. `--font-primary: 'Fraunces', Georgia, 'Times New Roman', serif` renders
**Georgia** on any machine without Fraunces installed — which is every machine
that has not deliberately installed six Google families. Checked against the OS
font collection on the machine this was found on: 211 families installed, and not
one of the six.

So the EDITORIAL anchor was being judged as Georgia, DIRECT as Arial Black, and
LUXE and INSTITUTIONAL as whatever `system-ui` resolves to. Every visual verdict
anyone had reached about this framework — including "these don't feel premium" —
was a verdict on the fallback stack. No craft layer rescues the wrong typeface.

**Do not test this with `document.fonts.check()`.** It returns `true` for almost
any family name and proves nothing; that API is how the defect stayed invisible.
Ask the operating system, or measure rendered glyph metrics.

## Why embedding is not the thing the old comments refused

The four mockups used to carry a sentence saying the families were *"NAMED with
honest system fallbacks and never `@font-face`'d at a URL: the Artifact CSP blocks
external requests, and a blocked font is worse than a declared fallback."*

The premise was right and the conclusion did not follow. A `data:` URI issues no
request, so there is nothing for a CSP to block. The reasoning ruled out
`url(https://…)` and was read as ruling out `@font-face` itself.
`specs/2026-08-14-perceptual-axes-design.md` already prescribes exactly this
embedding. Those comments now say what is true, because a comment describing a
decision that has been reversed sends the next reader to re-derive it wrongly.

## Why the latin subset is enough

Google serves one woff2 per `unicode-range`. The **latin** file covers
`U+0000-00FF` plus punctuation, currency and a few marks — which is the whole of
Spanish: `á é í ó ú ñ ü ¿ ¡` all live under `U+00FF`, and Spanish is the language
every one of these mockups is written in. The `latin-ext`, `vietnamese`,
`cyrillic` and `greek` subsets are not fetched, not committed and not embedded.

## Why Archivo is two files

`Archivo Expanded` is **not a separate family**: it is Archivo at `wdth` 125.
The obvious embedding — one full-range `62..125` file under two `@font-face`
names — costs 90,104 bytes *and* has to be base64'd twice in any file that uses
both widths, because a `data:` URI is inline and cannot be shared between two
rules. Google will serve a **pinned width instance** instead, and two of those
are smaller than one shared full-range file:

| what | bytes |
|---|---|
| `Archivo:wdth,wght@62..125,400..700` (full range, would need duplicating) | 90,104 |
| `Archivo:wdth,wght@100,400..700` + `Archivo:wdth,wght@125,400..700` | 69,636 |

The `font-stretch` descriptor is what makes the expanded face render expanded: an
element asking for the default `normal` (100%) is clamped into the face's declared
range, so a face declaring `125%` renders at 125% without every rule saying so.
Measured in a browser: Archivo 1287.91px advance, Archivo Expanded 1631.81px —
**ratio 1.267**. Get this wrong and the DIRECT anchor renders as plain Archivo,
which is a different design.

## Weights are declared honestly

The `weight` column is each face's **true** range, never a convenient one.
Declaring a range wider than the font holds would suppress the browser's synthetic
bold and hide a mockup asking for a weight nobody drew.

Instrument Serif is the case in point: it is a single-weight display serif, and
`ecommerce-mockup.html` asked its headings for `font-weight: 600`. Chrome answered
with **synthetic bold** — 1.61× the ink of the 400, with 600 and 700 producing
*identical* output, which is the giveaway, since a real weight axis is graduated
and a synthesised one is on/off. Note that the advance width is unchanged, so a
width measurement detects none of this. That mockup now asks for the 400 it has.

## Licence — verified per family, not assumed

All six families are **SIL Open Font License 1.1**. That was checked rather than
believed, through three independent signals per family:

1. the family's directory in `google/fonts` upstream is `ofl/` — that repository
   segregates by licence, so the path *is* a claim (`apache/` and `ufl/` exist and
   neither holds any of these six);
2. its `METADATA.pb` there declares `license: "OFL"`;
3. the committed `OFL.txt` carries the literal header
   `SIL OPEN FONT LICENSE Version 1.1`.

OFL permits redistribution **only with the licence text accompanying the fonts**,
so each family's `OFL.txt` is committed in this directory beside its `woff2`.
This repository is public under Apache-2.0 and its `LICENSE` hands every reader
the right to redistribute what it contains — a right we can only grant over what
we actually hold. This is the same reasoning that kept Envato kits out of here.

**The files are Google's own subsets, byte-for-byte as served, and are not
re-subset here.** That is deliberate: Source Sans 3 carries the Reserved Font Name
*'Source'*, and OFL §3 restricts an RFN in **modified** versions. Redistributing
an unmodified file under its original name is what OFL §2 permits outright, so
not touching the bytes is what keeps that clause satisfied. If anyone ever
re-subsets these, that analysis has to be redone before the result is committed.

## The set

Every file below is the `latin` subset served by `fonts.googleapis.com/css2` to a
current-Chrome user agent. Fetch with an old or absent user agent and Google
serves TTF instead, which is roughly four times the size and not what these rows
describe. `sha256` is the committed file, so drift is detectable rather than
assumed.

| Family | File | Axes served | Weight | Licence | sha256 (first 16) | Bytes |
|---|---|---|---|---|---|---|
| Fraunces | `fraunces-latin.woff2` | `opsz 9..144`, `wght 400..700` | 400 700 | SIL OFL 1.1 | `7234ed860a9cc830` | 67,304 |
| Instrument Serif | `instrument-serif-latin.woff2` | none (static) | 400 | SIL OFL 1.1 | `5eb09b5ac0e28b67` | 21,032 |
| Inter Tight | `inter-tight-latin.woff2` | `wght 400..700` | 400 700 | SIL OFL 1.1 | `77fefe8ca19b9f69` | 44,872 |
| DM Sans | `dm-sans-latin.woff2` | `opsz 9..40`, `wght 400..700` | 400 700 | SIL OFL 1.1 | `ca72d2bcea8f4daa` | 62,724 |
| Source Sans 3 | `source-sans-3-latin.woff2` | `wght 400..700` | 400 700 | SIL OFL 1.1 | `7a19a7027e125257` | 28,740 |
| Archivo | `archivo-latin.woff2` | `wdth 100` (pinned), `wght 400..700` | 400 700 | SIL OFL 1.1 | `8f704806dbedeaae` | 34,928 |
| Archivo Expanded | `archivo-expanded-latin.woff2` | `wdth 125` (pinned), `wght 400..700` | 400 700 | SIL OFL 1.1 | `8ac503c4c5897b58` | 34,708 |

**294,308 bytes raw / 392,412 base64** for the whole set. No single file pays all
of it: each mockup embeds only the families it names.

### Copyright notices, as required by OFL §1

| Family | Licence file | Copyright |
|---|---|---|
| Fraunces | `fraunces-OFL.txt` | Copyright 2018 The Fraunces Project Authors (https://github.com/undercasetype/Fraunces) |
| Instrument Serif | `instrumentserif-OFL.txt` | Copyright 2022 The Instrument Serif Project Authors (https://github.com/Instrument/instrument-serif) |
| Inter Tight | `intertight-OFL.txt` | Copyright 2022 The Inter Project Authors (https://github.com/rsms/inter-tight) |
| DM Sans | `dmsans-OFL.txt` | Copyright 2014 The DM Sans Project Authors (https://github.com/googlefonts/dm-fonts) |
| Source Sans 3 | `sourcesans3-OFL.txt` | Copyright 2010-2020 Adobe (http://www.adobe.com/), with Reserved Font Name 'Source'. All Rights Reserved. Source is a trademark of Adobe in the United States and/or other countries. |
| Archivo · Archivo Expanded | `archivo-OFL.txt` | Copyright 2020 The Archivo Project Authors (https://github.com/Omnibus-Type/Archivo) |

Archivo and Archivo Expanded share one notice because they are one family.

### Source URLs

Re-fetchable, and the `vNN` is Google's asset revision — if a re-fetch returns a
different `sha256`, the upstream file moved and this table is the record of what
was actually shipped.

| File | Source |
|---|---|
| `fraunces-latin.woff2` | https://fonts.gstatic.com/s/fraunces/v38/6NU78FyLNQOQZAnv9bYEvDiIdE9Ea92uemAk_WBq8U_9v0c2Wa0KxC9TeA.woff2 |
| `instrument-serif-latin.woff2` | https://fonts.gstatic.com/s/instrumentserif/v5/jizBRFtNs2ka5fXjeivQ4LroWlx-6zUTjg.woff2 |
| `inter-tight-latin.woff2` | https://fonts.gstatic.com/s/intertight/v9/NGSwv5HMAFg6IuGlBNMjxLsH8ag.woff2 |
| `dm-sans-latin.woff2` | https://fonts.gstatic.com/s/dmsans/v17/rP2Hp2ywxg089UriCZOIHQ.woff2 |
| `source-sans-3-latin.woff2` | https://fonts.gstatic.com/s/sourcesans3/v19/nwpStKy2OAdR1K-IwhWudF-R3w8aZQ.woff2 |
| `archivo-latin.woff2` | https://fonts.gstatic.com/s/archivo/v25/k3kPo8UDI-1M0wlSV9XAw6lQkqWY8Q82sLydOxI.woff2 |
| `archivo-expanded-latin.woff2` | https://fonts.gstatic.com/s/archivo/v25/k3kPo8UDI-1M0wlSV9XAw6lQkqWY8Q8EsLydOxI.woff2 |

Licence texts came from `raw.githubusercontent.com/google/fonts/main/ofl/<dir>/OFL.txt`.

## How the bytes reach a page

`_fonts.php` holds the registry and emits the `@font-face` block.
`_embed-fonts.php` writes that block into the four static mockups between
`NM-FONTS:BEGIN` / `NM-FONTS:END` markers and is safe to re-run.
`../gallery/_build-gallery.php` calls the same helper when it generates
`index.html`. `framework-audit.php`'s `RT_MOCKUP_FONT_NOT_EMBEDDED` fails any
mockup that names a family it does not embed, so this cannot quietly come undone.
