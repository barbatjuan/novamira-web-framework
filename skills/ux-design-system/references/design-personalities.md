# Design Personalities

Four anchors, each a position on five perceptual axes — scale, ground, density, composition,
elevation. Orthogonal to the structural archetype `web-templates` resolves — the SAME `TPL-*`
can ship under any of these. `ux-design-system`'s Execution Steps resolve every axis with the
client and land on one of these anchors, or between two. This file is the authority on WHICH
POSITION each anchor takes and on the concrete typeface names; the VALUE behind each position
lives in `web-templates/references/design-system.md`, and `references/design-tokens.md` explains
what each axis is FOR without naming a number.

No two anchors may share more than one axis position: the audit's `RT_PERS_TOO_SIMILAR` check
FAILS the build on it, but the rule belongs here too — two anchors that agree on two or more axes
are the same site with a different accent color, not two personalities.

Motion ranges below stay inside the curve those two files already define
(`cubic-bezier(.22,1,.36,1)`, the documented duration/lift ranges) — a personality tunes where on
that curve it lands, never invents new physics.

**Display tracking** is stated per anchor for the same reason the typeface is: it is a property of
that face at that size, not of an axis. It tightens only where the Scale axis puts the h1 cap past
~80px, because tracking that closes the counters at 120px opens holes at 48px; below that the faces
run at the tracking they were drawn with. The two proof mockups shipped `-0.015em` and `-0.025em`
with nothing behind them, which is what put this paragraph here — a difference with no home is
indistinguishable from an accident. Weight, `text-wrap`, button radius, footer direction and the
composition breakpoint were the other five differences those files carried; none of them earned an
anchor, so they are identical in both. Weights and radius come from
`web-templates/references/design-system.md` (type table, radius table); the breakpoint is the
framework's own `>1024`.

#### The tracking is a RAMP, not a value — and the paragraph above is why

Read that paragraph again: *"tracking that closes the counters at 120px opens holes at 48px."* It
is used there to decide which **anchors** tighten, by their h1 cap. It was never applied **inside**
an anchor, and inside an anchor is where the same span lives: at `editorial` the three display
steps render 88 / 58.7 / 39.1px, at `direct` 120 / 74.2 / 45.8px. One `--track-display` painted on
h1, h2 and h3 alike is this file's own rule half-applied — the h3 gets tracking chosen for a
headline three times its size.

**The anchor's number does not move.** It is the **h1** tracking. What the ramp adds is the span:
how far the tracking opens back up per step down. `craft-probe-2026-08-16.html` § `CRAFT-MATERIAL`
is the measured source — it ran `h1 -.022 / h2 -.016 / h3 -.006 / lede +.002 / small +.012` against
the other two directions' flat `-.015em`, and the h3 row is where the difference is visible without
measuring anything. Its span is what generalises; its absolute values are one anchor's.

| Step | Value | Why |
|---|---|---|
| `--track-h1` | `var(--track-display)` | the anchor's own number, unchanged |
| `--track-h2` | `calc(var(--track-display) + .006em)` | one step down |
| `--track-h3` | `calc(var(--track-display) + .016em)` | two steps down |
| `--track-h3-sm` | `calc(var(--track-display) + .024em)` | a card heading renders at `.58` of the h3 step — nearer the small end of the ramp than the h3 end |
| `--track-lede` | `.002em` | absolute: body copy is not display type, and no anchor asked to be exempt from legible small text |
| `--track-small` | `.012em` | absolute, same reason |

On `PERS-EDITORIAL` that resolves to `-.015 / -.009 / +.001` — the probe's shape sitting on this
file's number rather than on the probe's.

**Write `0em`, never `normal`, for an anchor that does not tighten.** They render identically, and
`calc(normal + .016em)` is a parse error. An invalid custom-property substitution does not warn: it
falls back to the property's **initial** value, which for `letter-spacing` is `normal` — so the ramp
dies silently on exactly the two anchors whose small type needed it most, and every heading still
renders, and a screenshot looks correct. `PERS-MATTER` and `PERS-INSTITUTIONAL` are the two anchors
this applies to.

### `PERS-EDITORIAL` — Editorial

**Axes:** scale `editorial` · ground `paper` · density `generous` · composition `asymmetric` · elevation `none`

**Fits:** Heritage, prestige, a story worth slowing down for — galleries, publishers, high-end services.

**Typography:** `--font-primary` **Fraunces** (variable serif, real thick/thin contrast, optical sizing); `--font-secondary` **Inter Tight**. Both SIL OFL. Display tracking `-0.015em` (h1–h3), wordmark `.16em`.

**Motion intensity:** slowest documented durations, lift capped at `-4px`. Nothing should feel quick.

**Imagery:** full-bleed or dramatically cropped photo-editorial framing; scrims sparingly.

**Card recipe:** image, hairline rule, text. No chips, no fills — the `none` elevation is the card.

### `PERS-DIRECT` — Direct

**Axes:** scale `monumental` · ground `ink` · density `compact` · composition `broken-grid` · elevation `accent-glow`

**Fits:** Brands that win by being unmistakable — studios, launches, anything that must not read as safe.

**Typography:** `--font-primary` **Archivo Expanded** at 700; `--font-secondary` **Archivo** regular. One family, two extremes. SIL OFL. Display tracking `-0.025em` (h1–h3), wordmark `.1em`.

**Motion intensity:** short durations, confident lift, accent glow rather than a neutral shadow.

**Imagery:** high-contrast, tightly cropped, often bleeding past the grid.

**Card recipe:** dark surface, accent glow border on hover instead of lift.

### `PERS-MATTER` — Matter

**Axes:** scale `classic` · ground `warm` · density `standard` · composition `strict-grid` · elevation `hairline`

**Fits:** Clients who sell a material or a made thing — stone, wood, food, furniture. The page should feel like the substance, not like software.

**Typography:** `--font-primary` **Instrument Serif** for headings; `--font-secondary` **DM Sans** for body. Both SIL OFL. The pairing is deliberately quieter than Editorial's: the photography carries the page. Display tracking `normal` — the `classic` h1 cap is 64px, under the ~80px where tightening starts paying.

**Motion intensity:** documented defaults, nothing faster. A material brand gains nothing from looking quick.

**Imagery:** the product shot straight on, warm-graded, edge to edge inside the grid. Never a lifestyle stock smile.

**Card recipe:** image at the container radius, hairline border, text below. No chips, no fills — the border is the whole chrome.

### `PERS-INSTITUTIONAL` — Institutional

**Axes:** scale `contained` · ground `cool` · density `standard` · composition `centered` · elevation `soft-shadow`

**Fits:** B2B, professional services, anything selling credibility over excitement — the archetype the abogados build belongs to.

**Typography:** `--font-primary` **Source Sans 3** semibold; `--font-secondary` **Source Sans 3** regular. One family, weight discipline instead of contrast. SIL OFL. Display tracking `normal` — the `contained` h1 cap is 48px, well under the ~80px where tightening starts paying.

**Motion intensity:** unmodified defaults. This anchor earns trust by not drawing attention to its own interactions.

**Imagery:** sober photography of real work contexts; icon-led process sections.

**Card recipe:** white card, soft shadow at rest, accent icon chip, lift on hover with no colour shift.
