# `STY-MATTER` — Matter

> Ported from `design-personalities.md`'s `PERS-MATTER` (style-catalog PR 4a, `tasks.md` 4a.2).
> Format only — the 8 axis positions and every prose claim below are unchanged from the source; see
> `_README.md` for what a port means and what still lives in the old file until PR 4c.

**Axes:** scale `classic` · ground `warm` · density `standard` · composition `strict-grid` ·
elevation `hairline` · accent `tinted-field` · chassis `bordered` · ornament `texture`

**Fits:** Clients who sell a material or a made thing — stone, wood, food, furniture. The page
should feel like the substance, not like software.

**Typography:** `--font-primary` **Instrument Serif** for headings; `--font-secondary` **DM Sans**
for body. Both SIL OFL. The pairing is deliberately quieter than Editorial's: the photography
carries the page. Display tracking `normal` — the `classic` h1 cap is 64px, under the ~80px where
tightening starts paying.

**Motion intensity:** documented defaults, nothing faster. A material brand gains nothing from
looking quick.

**Imagery:** the product shot straight on, warm-graded, edge to edge inside the grid. Never a
lifestyle stock smile.

**Card recipe:** image at the container radius, hairline border, text below. No chips, no fills —
the border is the whole chrome.

## Toggle precharge

| Toggle | Precharge | Why |
|---|---|---|
| `TGL-IMAGERY` | `foto` | "the product shot straight on, warm-graded" is a literal photography claim, never illustration |
| `TGL-MOTION-INTENSITY` | `default` | "documented defaults, nothing faster" is exactly the toggle's own personality-default option |
| `TGL-HERO-TYPE` | `imagen fija` | one substance-forward product shot, not a rotating carousel — "the page should feel like the substance" |
| `TGL-CTA-STRENGTH` | `medio` | sells on the material's own presence, neither hard nor apologetic |
| `TGL-CARD-STYLE` | `imagen grande` | the card recipe leads with the image at the container radius |
