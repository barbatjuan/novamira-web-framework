# `STY-DARK-LUXURY` — Dark Luxury

> Authored in style-catalog PR 4b (`tasks.md` 4b.3) — one of the 3 new v1 entries completing the
> catalog from 5 to 8. See `_README.md` for the font-budget decision behind every new entry's
> typography and the 28-pair separation table this entry was chosen against.

**Axes:** scale `monumental` · ground `ink-warm` · density `generous` · composition `centered` ·
elevation `accent-glow` · accent `metallic` · chassis `layered` · ornament `illustration`

**Fits:** Objects that cost what they look like they cost — jewellery, spirits, private clubs, high
fashion. `STY-VITRINE` already stages one object alone in a dark room; this one stages a
*collection*, several pieces stacked in depth rather than one held up alone.

**Typography:** `--font-primary` **Fraunces** at 700; `--font-secondary` **Inter Tight** regular.
Both SIL OFL. The same variable serif `STY-EDITORIAL` carries, pushed to its heaviest weight and
reversed onto a warm-black ground instead of paper — the same optical thick/thin contrast reads as
engraved gold on lacquer here, not heritage print. A reused family in a deliberately different
register, the same precedent `DM Sans` already sets across `STY-MATTER`'s secondary and
`STY-VITRINE`'s primary. Display tracking `-0.02em` (h1–h3), wordmark `.14em`.

**Motion intensity:** glow-forward and unhurried — the anchor sits at the framework's own documented
default, the same register `STY-MATTER` and `STY-INSTITUTIONAL` already argue nothing is gained by
rushing.

**Imagery:** the collection lit and layered, several pieces stacked at different depths rather than
one object isolated against black margin — where `STY-VITRINE`'s claim is the empty space around a
single piece, this ground's claim is the depth between several.

**Card recipe:** overlapping surfaces at a fixed offset, `z-index` setting the stack; the metallic
accent runs along the leading edge of whichever card currently sits on top, never on the ones behind
it.

## Toggle precharge

| Toggle | Precharge | Why |
|---|---|---|
| `TGL-IMAGERY` | `foto` | the collection is real photography, lit and layered — never illustration |
| `TGL-MOTION-INTENSITY` | `default` | glow-forward and unhurried is the anchor's own documented default, not a bold or a subtle deviation from it |
| `TGL-HERO-TYPE` | `imagen fija` | one composed, layered shot — a slider would cut the stack apart mid-read |
| `TGL-CTA-STRENGTH` | `suave` | an object that costs what it looks like it costs sells on restraint, the same register `STY-EDITORIAL`'s own heritage claim uses |
| `TGL-CARD-STYLE` | `imagen grande` | the card recipe is the layered image itself, not a data-first compact card |
