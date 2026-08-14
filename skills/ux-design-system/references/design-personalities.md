# Design Personalities (CAPA 1)

8 curated visual languages. Orthogonal to the structural archetype `web-templates` resolves —
the SAME `TPL-*` can ship under any of these. `ux-design-system` CAPA 2 picks one per project
from brand signals + the client's own references (never re-asked here). Roles and the shared
spacing/breakpoint SCALE come from `web-templates/references/design-system.md` and
`references/design-tokens.md`; this file supplies the concrete VALUES within those roles.

Radius/motion ranges below stay inside the scale and curve those two files already define
(`cubic-bezier(.22,1,.36,1)`, the documented duration/lift ranges, the existing radius steps) —
a personality tunes which point on that scale it lands on, never invents new physics.

### `PERS-EDITORIAL` — Editorial

**Axes:** scale `editorial` · ground `paper` · density `generous` · composition `asymmetric` · elevation `none`

**Fits:** Heritage, prestige, a story worth slowing down for — galleries, publishers, high-end services.

**Typography:** `--font-primary` **Fraunces** (variable serif, real thick/thin contrast, optical sizing); `--font-secondary` **Inter Tight**. Both SIL OFL.

**Motion intensity:** slowest documented durations, lift capped at `-4px`. Nothing should feel quick.

**Imagery:** full-bleed or dramatically cropped photo-editorial framing; scrims sparingly.

**Card recipe:** image, hairline rule, text. No chips, no fills — the `none` elevation is the card.

### `PERS-DIRECT` — Direct

**Axes:** scale `monumental` · ground `ink` · density `compact` · composition `broken-grid` · elevation `accent-glow`

**Fits:** Brands that win by being unmistakable — studios, launches, anything that must not read as safe.

**Typography:** `--font-primary` **Archivo Expanded** at 700+; `--font-secondary` **Archivo** regular. One family, two extremes. SIL OFL.

**Motion intensity:** short durations, confident lift, accent glow rather than a neutral shadow.

**Imagery:** high-contrast, tightly cropped, often bleeding past the grid.

**Card recipe:** dark surface, accent glow border on hover instead of lift.

### `PERS-MATTER` — Matter

**Axes:** scale `classic` · ground `warm` · density `standard` · composition `strict-grid` · elevation `hairline`

**Fits:** Clients who sell a material or a made thing — stone, wood, food, furniture. The page should feel like the substance, not like software.

**Typography:** `--font-primary` **Instrument Serif** for headings; `--font-secondary` **DM Sans** for body. Both SIL OFL. The pairing is deliberately quieter than Editorial's: the photography carries the page.

**Motion intensity:** documented defaults, nothing faster. A material brand gains nothing from looking quick.

**Imagery:** the product shot straight on, warm-graded, edge to edge inside the grid. Never a lifestyle stock smile.

**Card recipe:** image at the container radius, hairline border, text below. No chips, no fills — the border is the whole chrome.

### `PERS-INSTITUTIONAL` — Institutional

**Axes:** scale `contained` · ground `cool` · density `standard` · composition `centered` · elevation `soft-shadow`

**Fits:** B2B, professional services, anything selling credibility over excitement — the archetype the abogados build belongs to.

**Typography:** `--font-primary` **Source Sans 3** semibold; `--font-secondary` **Source Sans 3** regular. One family, weight discipline instead of contrast. SIL OFL.

**Motion intensity:** unmodified defaults. This anchor earns trust by not drawing attention to its own interactions.

**Imagery:** sober photography of real work contexts; icon-led process sections.

**Card recipe:** white card, soft shadow at rest, accent icon chip, lift on hover with no colour shift.
