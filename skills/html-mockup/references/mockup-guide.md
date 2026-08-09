# Mockup Guide — HTML shell + conventions

How to render a resolved template into a self-contained, responsive HTML mockup for Artifact
publishing. The mockup is the visual contract; keep it faithful to the tokens so the native build
can reproduce it.

## Base shell

The Artifact host wraps the file in `<!doctype><head></head><body>`. Write page content only, with
one inline `<style>`. No external anything.

```html
<style>
  :root{
    /* ── tokens: paste the resolved values from design-system.md ── */
    --font-primary: system-ui, sans-serif;
    --font-secondary: var(--font-primary);
    --fs-h1: clamp(2rem,5vw,3.5rem); --fs-h2: clamp(1.6rem,3.5vw,2.5rem);
    --fs-h3: clamp(1.25rem,2.5vw,1.75rem); --fs-body: clamp(1rem,1.2vw,1.125rem);
    --fs-small:.875rem; --fs-eyebrow:.75rem; --fs-price: clamp(1.1rem,1.6vw,1.35rem);
    --fs-price-old:.95rem; --fs-button:1rem; --fs-nav:.95rem;
    --c-primary:#1a1a1a; --c-secondary:#444; --c-accent:#c8642d;
    --c-bg:#ffffff; --c-bg-alt:#f4f2ee; --c-text:#1a1a1a; --c-text-muted:#6b6b6b;
    --c-border:#e5e1d8; --c-success:#2e7d32; --c-error:#c62828; --c-sale:#c8322d;
    --sp-xs:.5rem; --sp-s:1rem; --sp-m:1.5rem; --sp-l:3rem; --sp-xl:5rem; --sp-xxl:7.5rem;
    --container-max:1280px; --content-width:1140px;
    --pad-x-mobile:20px; --pad-x-tablet:32px; --pad-x-desktop:5%;
    --radius-card:12px; --radius-button:8px; --radius-image:8px; --radius-input:8px; --radius-container:16px;
    --ease:cubic-bezier(.22,1,.36,1);
  }
  @media (prefers-color-scheme: dark){
    :root{ --c-bg:#141414; --c-bg-alt:#1e1e1e; --c-text:#f2f2f2; --c-text-muted:#a8a8a8; --c-border:#333; }
  }
  *{box-sizing:border-box} body,figure,h1,h2,h3,p{margin:0}
  body{font-family:var(--font-secondary);color:var(--c-text);background:var(--c-bg);line-height:1.6}
  h1,h2,h3{font-family:var(--font-primary);line-height:1.15}
  h1{font-size:var(--fs-h1)} h2{font-size:var(--fs-h2)} h3{font-size:var(--fs-h3)}
  .wrap{max-width:var(--content-width);margin-inline:auto;padding-inline:var(--pad-x-mobile)}
  section{padding-block:var(--sp-l)}
  .btn{display:inline-block;padding:.875rem 1.75rem;border-radius:var(--radius-button);
       font-size:var(--fs-button);font-weight:600;text-decoration:none;transition:transform .35s var(--ease),background .2s}
  .btn-primary{background:var(--c-primary);color:#fff;border:1.5px solid var(--c-primary)}
  .btn-outline{background:transparent;color:var(--c-text);border:1.5px solid var(--c-primary)}
  .btn:hover{transform:translateY(-3px)}
  .ph{background:var(--c-bg-alt);border:1px dashed var(--c-border);border-radius:var(--radius-image);
      display:grid;place-items:center;color:var(--c-text-muted);font-size:var(--fs-small);aspect-ratio:4/3}
  .grid{display:grid;gap:var(--sp-m)}
  @media(min-width:768px){ section{padding-block:var(--sp-xl)} .wrap{padding-inline:var(--pad-x-tablet)} }
  @media(min-width:1024px){ .wrap{padding-inline:var(--pad-x-desktop)} }
</style>
```

## Placeholder recipe
- Images: `<div class="ph" style="aspect-ratio:16/9">imagen</div>` — never a real client photo.
- Copy: short neutral placeholders ("Título de sección", "Categoría", "$ 0.000"). Mark clearly it's a preview.
- Product/category cards: `.ph` for the image + placeholder name + price token.

## Section blueprints (mobile-first)
Build only the sections the template resolved as kept/on, in the template's order. Each mirrors the
per-breakpoint notes of the `TPL-*.md`:

- **Hero**: full-bleed block using `--c-bg-alt`; height per `TGL-HERO-HEIGHT` (`min-height:45vh`
  mobile → target vh desktop via `@media`); H1 + CTA. Slider vs fixed per `TGL-HERO-TYPE` (a static
  first-slide is fine for the mockup; note "slider" in a caption).
- **Category grid**: `.grid` 1 col mobile → `repeat(3–4,1fr)` desktop.
- **Product grid/carousel**: `.grid` 2 cols mobile → 4 desktop (grid); for carousel, a horizontal
  `overflow-x:auto` row with `scroll-snap`.
- **Editorial / Story**: 1 col mobile → 2 cols (`grid-template-columns:1fr 1fr`) desktop, alternating.
- **Benefits**: 2×2 mobile → row of 3–4 desktop.
- **Testimonials**: 1 per view (snap row) mobile → 3 cols desktop.
- **Newsletter**: stacked mobile → inline (`display:flex`) desktop.
- **Header/Footer**: sticky header with logo + nav placeholder + cart. The **logo always links to
  home** (`href="/"`). Cart is an **icon** with a count badge, never a text label. Footer columns
  collapse to stacked on mobile. Reuse the exact same header/footer across every page of the set.

## Responsive rules
- Never let `body` scroll horizontally. Carousels scroll inside their own `overflow-x:auto`.
- Use relative units and `max-width:100%`. Test the three breakpoints before publishing.

## Handoff
On approval, list the sections present (in order) + the token values used. `elementor-core` /
`divi-core` reproduce this NATIVELY; `qa-review` compares the native output against this mockup.
