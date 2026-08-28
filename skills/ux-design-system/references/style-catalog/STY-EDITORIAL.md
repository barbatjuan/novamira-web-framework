# `STY-EDITORIAL` — Editorial

> Ported from `design-personalities.md`'s `PERS-EDITORIAL` (style-catalog PR 4a, `tasks.md` 4a.2).
> Format only — the 8 axis positions and every prose claim below are unchanged from the source; see
> `_README.md` for what a port means and what still lives in the old file until PR 4c.

**Axes:** scale `editorial` · ground `paper` · density `generous` · composition `asymmetric` ·
elevation `none` · accent `none` · chassis `rule-divided` · ornament `rule`

**Fits:** Heritage, prestige, a story worth slowing down for — galleries, publishers, high-end
services.

**Typography:** `--font-primary` **Fraunces** (variable serif, real thick/thin contrast, optical
sizing); `--font-secondary` **Inter Tight**. Both SIL OFL. Display tracking `-0.015em` (h1–h3),
wordmark `.16em`.

**Motion intensity:** slowest documented durations, lift capped at `-4px`. Nothing should feel
quick.

**Imagery:** full-bleed or dramatically cropped photo-editorial framing; scrims sparingly.

**Card recipe:** image, hairline rule, text. No chips, no fills — the `none` elevation is the card.

## Toggle precharge

A style that precharges nothing has not been finished — this is the mechanism the proposal's root
cause 6 names (39 toggles catalogued, one moved off default across 67 strips). Every ID below is
in `web-templates/references/toggles.md`'s shared cross-template list, since `STY-*` is orthogonal
to `TPL-*` and only a toggle every template recognizes can be precharged from the style alone.

| Toggle | Precharge | Why |
|---|---|---|
| `TGL-IMAGERY` | `foto` | the Imagery line names real photo-editorial framing, never illustration |
| `TGL-MOTION-INTENSITY` | `sutil` | "nothing should feel quick" is the calm end of the range, not the personality default |
| `TGL-HERO-TYPE` | `imagen fija` | one dramatic full-bleed image serves "a story worth slowing down for"; a slider fragments it |
| `TGL-CTA-STRENGTH` | `suave` | heritage/prestige sells on restraint, not urgency |
| `TGL-CARD-STYLE` | `imagen grande` | the card recipe's own image-first, chip-free construction |
