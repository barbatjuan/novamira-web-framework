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

**Fits:** Brands with heritage, prestige, or a story to lean on — publishers, galleries,
high-end services.

**Typography:** `--font-primary` a high-contrast serif with real character (dramatic thick/thin
strokes) for headings; `--font-secondary` a clean humanist sans for body. Large `--fs-h1`,
tight line-height, letter-spaced eyebrow label.

**Color mood:** dominant near-white OR near-black (pick one brand-wide, never split), contrast
pushed to true near-black ink, accent restrained — a muted tone reads more premium here than a
bright saturated one. Follow `design-tokens.md`'s derivation steps; keep the accent quiet.

**Radius & shadow:** softest step of the existing scale on cards/containers; buttons/inputs
sharper. Shadows near-absent — separation comes from whitespace and a hairline border.

**Motion intensity:** slower than default — durations toward the top of the documented range
(`.5s`–`.7s` as the floor), lift capped at `-4px`. Nothing should feel quick.

**Imagery:** full-bleed or dramatically cropped photo-editorial framing. Scrims used sparingly —
trust the photo's own contrast.

**Card recipe:** minimal chrome — image, thin rule, text. No icon chips, no colored blocks.

### `PERS-BOLD-STARTUP` — Bold Startup

**Fits:** Young SaaS, DTC brands that want to feel fast and confident.

**Typography:** heavy geometric sans for both roles (may be one family at different weights),
700+ weight headings, punchy.

**Color mood:** dominant white/near-white, one HIGH-saturation accent — the loudest the
one-accent rule allows — contrast near-black.

**Radius & shadow:** mid-to-large radii on cards, visible soft shadow even at rest — the
"floating card" look.

**Motion intensity:** at or slightly above default — `.35s` color, full `-6px` lift,
`scale(1.045)` image hover. Never stack all three intensifiers at once (`motion.md`'s own
warning against `.35s` + `-6px` + `scale(1.06)` together still applies).

**Imagery:** bright, high-key product/lifestyle photography or bold flat illustration; can mix.

**Card recipe:** the existing premium feature card from `motion.md` as-is — accent circular
icon chip, top accent bar reveal on hover.

### `PERS-MINIMAL-SWISS` — Minimal Swiss

**Fits:** Precision, data, or product-first brands (tools, technical services) where restraint
reads as competence.

**Typography:** one neutral grotesque family for both roles, regular-to-medium weight even in
headings — never a decorative display face.

**Color mood:** near-monochrome — dominant/contrast do almost all the work, accent used at the
smallest surface area the one-accent rule allows.

**Radius & shadow:** 0–4px everywhere, no shadows at rest; hover uses a border/underline change
instead of lift where possible.

**Motion intensity:** near-imperceptible — shortest documented durations, `-4px` lift max, no
image zoom on hover.

**Imagery:** documentary/product photography shot straight-on, or none — data/typographic
composition carries the page instead.

**Card recipe:** hairline border only, no icon chips, no background tint — grid alignment
carries the hierarchy.

### `PERS-WARM-BOUTIQUE` — Warm Boutique

**Fits:** Artisanal, local, or hospitality-adjacent brands where "handmade" is the story.

**Typography:** rounded humanist sans or a friendly serif for `--font-primary`, softer weight
than Bold Startup; body slightly larger than default for easy reading.

**Color mood:** warm dominant (cream/off-white, not pure white), earth-tone or pastel accent,
contrast pushed to a warm near-black (brown-black, not blue-black).

**Radius & shadow:** 12–20px across cards and containers (the softest end of the existing
scale); soft, warm-tinted shadow, not neutral grey.

**Motion intensity:** soft and organic — duration stretched slightly past default, gentle lift,
no sharp snaps.

**Imagery:** warm-toned photography or hand-drawn/watercolor illustration accents; imperfection
reads as authenticity here, unlike Editorial's polish.

**Card recipe:** rounded image corners matching the container radius, soft background tint
block behind text instead of a hard border.

### `PERS-CORPORATE-TRUST` — Corporate Trust

**Fits:** B2B, professional services, anything selling credibility over excitement.

**Typography:** institutional sans, conservative, consistent weight discipline, no display
flourishes.

**Color mood:** dominant white, contrast a deep blue or blue-grey (not pure black), accent
restrained and cool.

**Radius & shadow:** medium radii (mid-scale), light shadow only on interactive elements, not
on static content cards.

**Motion intensity:** unmodified `motion.md` defaults — this personality earns trust by not
drawing attention to its own interactions.

**Imagery:** sober photography (real work contexts, not stock-smiling), icon-led service/process
sections, minimal illustration.

**Card recipe:** the standard premium feature card, icon chip in the cool accent, restrained
hover (lift only, no icon color shift).

### `PERS-FASHION-EDIT` — Fashion Edit

**Fits:** Apparel and fashion e-commerce competing on visual authority, not price.

**Typography:** a fine elegant serif or a tall elegant condensed sans for `--font-primary` at a
large size; body sans stays quiet and small.

**Color mood:** black and white as dominant/contrast, ONE seasonal accent that can rotate per
campaign — still only one at a time, never a second permanent color.

**Radius & shadow:** near-0 radii throughout — square imagery, square buttons. No shadows.

**Motion intensity:** the slowest in the catalog — reveal-style transitions, long fades, lift
near 0, rely on opacity/scale instead.

**Imagery:** full-bleed, uncropped or minimally cropped photography — never a tight
product-only crop.

**Card recipe:** image-dominant, price/name appear on hover or below with generous whitespace —
no borders, no background.

### `PERS-TECH-PRECISION` — Tech Precision

**Fits:** Electronics, gadgets, anything selling specs and precision engineering.

**Typography:** geometric sans for `--font-primary`; a monospace family introduced specifically
for spec tables/numbers — the one case in the catalog where a third family-ish element is
allowed, scoped only to data, never headings/body.

**Color mood:** native dark mode — `--c-bg` near-black, `--c-text` near-white, ONE electric
accent (blue, green, or violet) reading as "on" against the dark field.

**Radius & shadow:** small radii (Minimal Swiss's sharp end), but WITH an accent-tinted glow on
hover instead of a neutral drop shadow.

**Motion intensity:** fast and precise — shorter durations, sharp easing feel, small confident
lift.

**Imagery:** product photography on dark/gradient backgrounds; reuse `motion.md`'s glass recipe
behind hero content, tinted dark.

**Card recipe:** dark card surface, accent glow border on hover instead of lift, spec rows in
the monospace family.

### `PERS-PERFORMANCE-ENERGY` — Performance Energy

**Fits:** Sports, activewear, anything selling motion and intensity.

**Typography:** condensed/athletic display face for `--font-primary` (tall, bold, slightly
compressed), neutral sans for body so long copy stays readable.

**Color mood:** dominant white or near-black (per brand), ONE highly saturated accent
(red/orange/electric) used aggressively on CTAs.

**Radius & shadow:** small-to-medium radii; sections use angled/diagonal dividers (clip-path or
skewed edge) instead of the flat section-edge convention used elsewhere in the catalog — the
one personality with a scoped exception to that convention, not a violation of it (spacing
rhythm and section padding stay intact, only the edge shape changes).

**Motion intensity:** fastest and snappiest in the catalog — short durations, larger lift than
default, image zoom pushed toward the top of the documented range.

**Imagery:** dynamic action photography with motion-blur crops, high-contrast grading.

**Card recipe:** bold accent-colored CTA overlay on hover, sharp diagonal accent bar (echoing
the section dividers) instead of the default straight top-bar reveal.
