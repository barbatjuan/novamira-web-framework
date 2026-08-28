# `STY-TECH-SAAS` — Tech SaaS

> Authored in style-catalog PR 4b (`tasks.md` 4b.3) — one of the 3 new v1 entries completing the
> catalog from 5 to 8. See `_README.md` for the font-budget decision behind every new entry's
> typography and the 28-pair separation table this entry was chosen against.

**Axes:** scale `contained` · ground `ink-cool` · density `generous` · composition `strict-grid` ·
elevation `accent-glow` · accent `duotone` · chassis `strict-grid` · ornament `none`

**Fits:** Product-led software — dashboards, developer tools, platforms selling capability rather
than craft. The interface is the pitch; nothing else has to perform.

**Typography:** `--font-primary` **Inter Tight** at 700; `--font-secondary` **Source Sans 3**
regular. Both SIL OFL. Inter Tight has only ever carried a secondary role in this catalog
(`STY-EDITORIAL`, `STY-VITRINE`) — pushed to its heaviest weight here as a PRIMARY for the first
time, it reads closer to interface chrome than a display face, which is the point: a reused family,
a genuinely new role. Display tracking `-0.01em` (h1–h3), wordmark `.08em` — tighter than every
ported anchor; a product wordmark, not a heritage one.

**Motion intensity:** short, glow-forward transitions at the calm end of the documented range —
nothing lifts, things illuminate. The `accent-glow` elevation carries the whole motion story on its
own.

**Imagery:** real photography — the team, the hardware, the room the product ships from —
duotone-graded in the accent plus the ground's own dark neutral. Never a raw UI screenshot standing
in for a photograph, and never a decorative lifestyle shot with nothing to do with the product.

**Card recipe:** no fill, no border — spacing and grid alignment alone separate blocks, every edge
landing on a fixed line. The glow arrives only on hover, from elevation, never from the chassis
itself: composition and chassis are both grid-locked here, but for two different reasons — the page
is a grid, and so is every block inside it.

## Toggle precharge

| Toggle | Precharge | Why |
|---|---|---|
| `TGL-IMAGERY` | `foto` | the Imagery line is a literal claim about real photography, duotone-graded — never illustration or a screenshot standing in for one |
| `TGL-MOTION-INTENSITY` | `sutil` | the glow already carries the motion story; a bold hover on top of it reads busy, not confident |
| `TGL-HERO-TYPE` | `imagen fija` | one graded photograph, not a slider — "the interface is the pitch" doesn't need three frames to make its case |
| `TGL-HERO-HEIGHT` | `bajo 35vh` | a product hero leads with the headline and the proof below it, not a full-bleed banner |
| `TGL-CARD-STYLE` | `compacta con datos` | the card recipe is a grid-locked block with no image-first construction |
