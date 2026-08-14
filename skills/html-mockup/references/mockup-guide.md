# Mockup Guide — HTML shell + conventions

How to render a resolved template into a self-contained, responsive HTML mockup for Artifact
publishing. The mockup is the visual contract; keep it faithful to the tokens so the native build
can reproduce it. This file is not boilerplate: the one-Artifact rule below is binding.

## Base shell

The Artifact host wraps the file in `<!doctype><head></head><body>`. Write page content only, with
one inline `<style>`. No external anything.

```html
<style>
  :root{
    /* ══ AXIS POSITIONS — the only lines you replace per project ══
       Copy the row for each RESOLVED position out of
       web-templates/references/design-system.md § "Perceptual axes — token values". Never type a
       number here and never re-derive one: these five lines are the whole visual identity, and a
       hand-picked value is how every site ends up looking the same. The values below are that
       file's `classic` scale row and `standard` density row, present only so the chain underneath
       is runnable as written. */
    --type-ratio: 1.333;  --display-lh: 1.10;  --fs-h1-max: 64;      /* scale: classic */
    --sp-scale: 1.0;                                                 /* density: standard */
    --c-bg:#ffffff; --c-bg-alt:#f4f2ee; --c-text:#1a1a1a;            /* ground */
    --elev-rest:none; --elev-hover:none;                             /* elevation: none */
    /* composition: LP-CENTERED */

    --font-primary: system-ui, sans-serif;
    --font-secondary: var(--font-primary);

    /* ── Type scale, transcribed VERBATIM from design-system.md § Scale. Every heading step hangs
          off --type-ratio and --fs-h1-max; not one heading size is written by hand, and each
          step's preferred term interpolates ITS OWN floor into ITS OWN cap across 430 → 1280 so
          the cap engages on a laptop. What used to sit on this line — `clamp(2rem,5vw,3.5rem)` —
          IS the defect the axes replaced: a 56px h1 cap on every client site, byte-identical at
          all four scale positions. --fs-h1-max is UNITLESS on purpose: calc() cannot divide a
          length by a length, so the coefficient multiplying --fluid must arrive without a unit,
          and --fs-base is the single bridge back to a length. ── */
    --fs-base: 16;
    --fluid: clamp(0px, calc((100vw - 430px) / 850), 1px);
    --n-h1: calc(var(--fs-base) * var(--type-ratio) * var(--type-ratio) * var(--type-ratio));
    --n-h2: calc(var(--fs-base) * var(--type-ratio) * var(--type-ratio));
    --n-h3: calc(var(--fs-base) * var(--type-ratio));
    --n-h1-cap: var(--fs-h1-max);
    --n-h2-cap: calc(var(--fs-h1-max) / var(--type-ratio));
    --n-h3-cap: calc(var(--fs-h1-max) / var(--type-ratio) / var(--type-ratio));
    --fs-h1: clamp(calc(var(--n-h1) / var(--fs-base) * 1rem),
                   calc(var(--n-h1) / var(--fs-base) * 1rem + (var(--n-h1-cap) - var(--n-h1)) * var(--fluid)),
                   calc(var(--n-h1-cap) * 1px));
    --fs-h2: clamp(calc(var(--n-h2) / var(--fs-base) * 1rem),
                   calc(var(--n-h2) / var(--fs-base) * 1rem + (var(--n-h2-cap) - var(--n-h2)) * var(--fluid)),
                   calc(var(--n-h2-cap) * 1px));
    --fs-h3: clamp(calc(var(--n-h3) / var(--fs-base) * 1rem),
                   calc(var(--n-h3) / var(--fs-base) * 1rem + (var(--n-h3-cap) - var(--n-h3)) * var(--fluid)),
                   calc(var(--n-h3-cap) * 1px));
    --fs-body: clamp(1rem,1.2vw,1.25rem);   /* body is NOT an axis — a plain vw term is correct */
    --fs-small:.875rem; --fs-eyebrow:.75rem; --fs-price: clamp(1.1rem,1.6vw,1.35rem);
    --fs-price-old:.95rem; --fs-button:1rem; --fs-nav:.95rem;

    --c-primary:#1a1a1a; --c-secondary:#444; --c-accent:#c8642d;
    --c-text-muted:#6b6b6b; --c-border:#e5e1d8;
    --c-success:#2e7d32; --c-error:#c62828; --c-sale:#c8322d;

    /* ── Spacing, verbatim from design-system.md § Density: ONE scale multiplied whole by the
          density position, and --sp-section carrying --sp-scale on BOTH ends of the
          interpolation — which is what makes the density axis visible at 430, 768 AND 1280
          instead of only at the extremes. Fixed rems here (`--sp-l:3rem`) flatten it. ── */
    --sp-xs: calc(0.5rem * var(--sp-scale));  --sp-s:  calc(1rem   * var(--sp-scale));
    --sp-m:  calc(1.5rem * var(--sp-scale));  --sp-l:  calc(3rem   * var(--sp-scale));
    --sp-xl: calc(5rem   * var(--sp-scale));  --sp-xxl:calc(7.5rem * var(--sp-scale));
    --n-sec:     calc(2 * var(--fs-base) * var(--sp-scale));
    --n-sec-cap: calc(7 * var(--fs-base) * var(--sp-scale));
    --sp-section: clamp(calc(var(--n-sec) / var(--fs-base) * 1rem),
                        calc(var(--n-sec) / var(--fs-base) * 1rem + (var(--n-sec-cap) - var(--n-sec)) * var(--fluid)),
                        calc(var(--n-sec-cap) * 1px));

    --container-max:1280px; --content-width:1140px;
    --pad-x-mobile:20px; --pad-x-tablet:32px; --pad-x-desktop:5%;
    --radius-card:12px; --radius-button:8px; --radius-image:8px; --radius-input:8px; --radius-container:16px;
    --ease:cubic-bezier(.22,1,.36,1);
  }
  @media (prefers-color-scheme: dark){
    :root{ --c-bg:#141414; --c-bg-alt:#1e1e1e; --c-text:#f2f2f2; --c-text-muted:#a8a8a8; --c-border:#333; }
  }
  *{box-sizing:border-box} body,figure,h1,h2,h3,p{margin:0}
  /* `overflow-wrap:anywhere`, not `break-word`: only `anywhere` shrinks a box's intrinsic
     min-content size, so a heading sized `fit-content` actually breaks its longest word instead of
     staying wider than its column. Measured on a proof mockup, `break-word` left 58px of
     horizontal scroll at the 320px reflow width (WCAG 1.4.10) untouched. */
  body{font-family:var(--font-secondary);color:var(--c-text);background:var(--c-bg);
       font-size:var(--fs-body);line-height:1.6;overflow-wrap:anywhere}
  /* h1/h2 take the display leading the scale axis fixes; h3 stays 1.25 and body 1.6. A fixed 1.15
     for every heading was the other half of the defect above. Weights per design-system.md. */
  h1,h2,h3{font-family:var(--font-primary);text-wrap:balance}
  h1,h2{line-height:var(--display-lh);font-weight:700}
  h1{font-size:var(--fs-h1)} h2{font-size:var(--fs-h2)}
  h3{font-size:var(--fs-h3);line-height:1.25;font-weight:600}
  .wrap{max-width:var(--content-width);margin-inline:auto;padding-inline:var(--pad-x-mobile)}
  /* Fluid, density-scaled, and no breakpoint by hand — that is the point of --sp-section. */
  section{padding-block:var(--sp-section)}
  .btn{display:inline-block;padding:.875rem 1.75rem;border-radius:var(--radius-button);
       font-size:var(--fs-button);font-weight:600;text-decoration:none;transition:transform .35s var(--ease),background .2s}
  .btn-primary{background:var(--c-primary);color:#fff;border:1.5px solid var(--c-primary)}
  .btn-outline{background:transparent;color:var(--c-text);border:1.5px solid var(--c-primary)}
  .btn:hover{transform:translateY(-3px)}
  /* `width` MUST be pinned alongside `aspect-ratio`. With a ratio and no width, an auto width is
     computed FROM the ratio and the available height — measured at 660px inside a 390px column,
     a 250px page overflow. Pinning width makes the ratio drive height only. */
  .ph{background:var(--c-bg-alt);border:1px dashed var(--c-border);border-radius:var(--radius-image);
      display:grid;place-items:center;color:var(--c-text-muted);font-size:var(--fs-small);
      width:100%;max-width:100%;aspect-ratio:4/3}
  .grid{display:grid;gap:var(--sp-m)}
  @media(min-width:768px){ .wrap{padding-inline:var(--pad-x-tablet)} }
  @media(min-width:1024px){ .wrap{padding-inline:var(--pad-x-desktop)} }
</style>
```

## Placeholder recipe
- Images: `<div class="ph" style="aspect-ratio:16/9">imagen</div>` — never a real client photo.
- Copy: short neutral placeholders ("Título de sección", "Categoría", "0,00 €" — euros, house
  rule). Mark clearly it's a preview.
- Product/category cards (ecommerce): `.ph` for the image + placeholder name + price token.
- Corporate: no prices unless the template resolved `COMP-PRICING`; no cart, ever.

## Section blueprints (mobile-first)
Build only the sections the template resolved as kept/on, in the template's order. Each mirrors the
per-breakpoint notes of the `TPL-*.md`.

### Shared (both site types)
- **Hero**: full-bleed block using `--c-bg-alt`; height per `TGL-HERO-HEIGHT` (`min-height:45vh`
  mobile → target vh desktop via `@media`); H1 + CTA. Slider vs fixed per `TGL-HERO-TYPE` (a static
  first-slide is fine for the mockup; note "slider" in a caption). **Never a capture form here** —
  house rule; the lead form belongs to the closing conversion band. On a split hero the second
  column is an image `.ph`, not a form card.
- **Editorial / Story**: 1 col mobile → 2 cols (`grid-template-columns:1fr 1fr`) desktop, alternating.
- **Benefits / Features** (`COMP-FEATURES`): 2×2 mobile → row of 3–4 desktop.
- **Testimonials**: 1 per view (snap row) mobile → 3 cols desktop.
- **Banner CTA** (`COMP-CTA`): full-width `--c-bg-alt` band, centered H2 + one accent button.
- **Header/Footer**: sticky header with logo + nav placeholder. The **logo always links to
  home**. Footer columns collapse to stacked on mobile. Reuse the exact same header/footer across
  every page of the set. Ecommerce ends the header with a **cart icon + count badge** (never a text
  label); corporate ends it with the primary CTA button (contacto / reserva) instead.

### Ecommerce-only
- **Category grid**: `.grid` 1 col mobile → `repeat(3–4,1fr)` desktop.
- **Product grid/carousel**: `.grid` 2 cols mobile → 4 desktop (grid); for carousel, a horizontal
  `overflow-x:auto` row with `scroll-snap`.
- **Newsletter**: stacked mobile → inline (`display:flex`) desktop.
- Prices render in **€** (house rule) — ecommerce path only.

### Corporate-only
- **Services** (`COMP-SERVICES`): card grid, 1 col mobile → 2 tablet → 3 desktop; each card = icon
  `.ph` (`aspect-ratio:1`, ~48px) + H3 + 2 lines + "Ver más" ghost link. One card recipe site-wide.
- **Lead form** (`COMP-LEAD-FORM`): stacked fields full-width mobile → 2-col field grid with the
  submit spanning both desktop; labels above inputs, `--radius-input`, one solid accent submit.
  Inert in the mockup — never wire a real endpoint. It renders in the **closing conversion band**,
  never in the hero.
- **Service detail** (`TPL-SERVICE-01`): one page per corporate service/area. H1 = the service as
  the client searches it. "What we solve" is a 1-col mobile → 2-col desktop grid of the card recipe
  without icons; scope reuses the 4-up card row; FAQ is on by default here. It ends with a **sibling
  cross-link block** — compact cards to the other services plus "see all" — which is FIXED, not a
  toggle: without links between siblings each service hangs off the home alone. Reuse the site-wide
  card recipe; do not invent a second one for this page.
- **Process / Steps** (`COMP-PROCESS`): numbered vertical list with a left rule mobile → row of
  3–4 columns desktop; big muted step number + H3 + one line.
- **Cases / Projects** (`COMP-CASES`): 1 col mobile → 2 desktop; `.ph` 16/9 + client placeholder +
  one-line result. Card opens the case page only if the page set resolved one.
- **Logos** (`COMP-LOGOS`): row of neutral `.ph` boxes (`aspect-ratio:5/2`, grayscale), 2 cols
  mobile → 5–6 desktop, low contrast so they never compete with the accent.
- **Stats** (`COMP-STATS`): 2×2 mobile → row of 3–4 desktop; big number (`--fs-h2`) + short label
  in `--c-text-muted`. Numbers are placeholders until the client confirms them.
- **Team** (`COMP-TEAM`): 2 cols mobile → 3–4 desktop; `.ph` `aspect-ratio:1` (or 3/4) + name + role.
- **Portfolio grid** (`COMP-PORTFOLIO-GRID`): the visual centerpiece of TPL-C-03 — 1 col mobile →
  2–3 desktop masonry-ish grid of `.ph` blocks, minimal chrome, caption on hover only.
- **Pricing** (`COMP-PRICING`): stacked cards mobile → 3 cols desktop, middle plan highlighted with
  the accent border (one accent only); feature list as plain `ul`.
- **FAQ** (`COMP-FAQ`): native `<details>/<summary>` accordion, full width, `--c-border` divider
  per row. No JS needed.
- **Booking** (`COMP-BOOKING`): stacked service + date/time selects mobile → inline row desktop,
  ending in the solid accent CTA. Inert placeholder, no real calendar.
- **Map / NAP** (`COMP-MAP-NAP`): map is a `.ph` block (`aspect-ratio:16/9`) — never an embedded
  iframe (Artifact CSP blocks it). Beside it (stacked mobile → 2 cols desktop) the NAP block:
  name, address, phone, hours as a small `dl`.

## Responsive rules
- Never let `body` scroll horizontally. Carousels scroll inside their own `overflow-x:auto`.
- Use relative units and `max-width:100%`. Test the three breakpoints before publishing.

## Multi-page preview: ONE Artifact, in-page switching (binding)
The whole page set ships as **one** HTML file published as **one** Artifact, with each page as a
`<div class="page">` section switched by in-page JS (`show(id)` toggling `.active`). **Never** one
mockup per page, never one Artifact per page, never `target="_top"` links between Artifacts —
cross-artifact navigation is dead in the sandbox (dead clicks, "logo doesn't return home").
Keep the **header, announcement bar and footer as global elements OUTSIDE the `.page`
containers** so they never disappear when the active page changes. The logo switches to `home`;
nav items switch pages; on ecommerce the cart icon switches pages and product cards open the PDP.
The per-page split survives only as the **handoff spec** — one section inventory per page for the
native build — never as separate mockup files or URLs.

## Container hygiene — the mockup's DOM is a blueprint

The native build reproduces this file section by section, so every wrapper `<div>` here becomes an
Elementor container there. A mockup nested five levels deep teaches the build to nest five
containers, and each level is then paid three times on the live site: a wrapper in the DOM, a block
of generated CSS, and one more click between a human and the widget they opened the editor to
change. The three rules below are builder-agnostic, and **this is where they start** — fixing them
downstream in `elementor-core` means fixing them after the client already approved the shape.

1. **The section IS the row.** A two-column band is `<section>` with `display:flex` and the two
   halves as direct children — not a section wrapping a row `<div>` wrapping the halves. Stack at
   `<768` with `flex-direction:column`.
2. **A width does not justify a `<div>`.** Wrapping an element to make it 58% wide buys a node for
   nothing; put the width on the element. In the native build this becomes `_element_custom_width`
   (`es_wide()`), and a wrapper here becomes a whole container there.
3. **A photo is an `<img>`, not a `background-image`.** Use `<img>` with `object-fit:cover` and a
   real `alt`. A CSS background needs an otherwise-empty element to live in, is invisible to
   screen readers and to Google Images, and maps to the exact container `es_photo()` exists to
   avoid.

Target depth `section > grid|row > element`. Past three levels, have a reason.
Mirror of `elementor-core/references/gotchas.md` → "Container hygiene", which carries the
measured before/after from the build where these were found.

## Handoff
On approval, list the sections present per page (in order) + the token values used. `elementor-core` /
`divi-core` reproduce this NATIVELY; `qa-review` compares the native output against this mockup.
