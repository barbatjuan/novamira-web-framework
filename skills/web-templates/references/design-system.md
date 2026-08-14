# Sistema Global de Diseño

La ESTRUCTURA de tokens (roles, pasos de escala, breakpoints) es compartida por TODAS las
plantillas (ecommerce y corporate) y se define **una vez**. Los VALORES concretos (tipografía,
paleta, radios/sombras, motion) vienen de la personalidad visual (`PERS-*`) elegida en
`ux-design-system` CAPA 2 — independiente de qué `TPL-*` se haya elegido. Diseñado mobile-first.
Compatible con Elementor (Global Settings) y Divi (Theme Options + Global Presets), y con la
skill `html-mockup` (variables `--*` en `:root`).

Los valores de esta página son el fallback estructural (spacing, breakpoints, contenedores) que
toda personalidad hereda sin tocar. Para tipografía, paleta, radios y motion CONCRETOS, ver
`ux-design-system/references/design-personalities.md` — CAPA 2 ajusta esos valores por marca;
los ROLES no cambian nunca.

Este archivo es la **única autoridad** sobre NOMBRES y VALORES ESTRUCTURALES de token (spacing,
breakpoints, contenedores). `design-personalities.md` es la única autoridad sobre los valores de
tipografía/paleta/radios/motion por personalidad. `ux-design-system/references/design-tokens.md`
explica los ROLES (para qué sirve cada token, cómo derivar la paleta de un logo) y no define
valores. Ante cualquier diferencia, manda el archivo correspondiente a lo consultado.

## Breakpoints (mobile-first)

| Nombre | Rango | Uso |
|--------|-------|-----|
| mobile | `< 768px` | base — se diseña primero |
| tablet | `768–1024px` | ajustes de columnas |
| desktop | `> 1024px` | layout completo |

Se escribe el CSS base para mobile y se sube con `min-width`. Nunca al revés.

## Tipografía

Dos familias máximo. Escala fluida con `clamp()`.

| Rol | Token | Valor | Peso | Uso |
|-----|-------|-------|------|-----|
| Font principal | `--font-primary` | — | — | Headings + UI |
| Font secundaria | `--font-secondary` | — | — | Body (opcional; puede = principal) |
| H1 | `--fs-h1` | `clamp(2rem, 5vw, 3.5rem)` | 700 | 1 solo por página |
| H2 | `--fs-h2` | `clamp(1.6rem, 3.5vw, 2.5rem)` | 700 | título de sección |
| H3 | `--fs-h3` | `clamp(1.25rem, 2.5vw, 1.75rem)` | 600 | subtítulos |
| Body | `--fs-body` | `clamp(1rem, 1.2vw, 1.125rem)` | 400 | párrafos |
| Small | `--fs-small` | `0.875rem` | 400 | notas, meta |
| Eyebrow/label | `--fs-eyebrow` | `0.75rem` | 600 uppercase, tracking | etiqueta sobre título |
| Precio | `--fs-price` | `clamp(1.1rem, 1.6vw, 1.35rem)` | 700 | precio actual (ecommerce) |
| Precio anterior | `--fs-price-old` | `0.95rem` | 400 line-through, muted | precio tachado |
| Botón | `--fs-button` | `1rem` | 600 | CTAs |
| Navegación | `--fs-nav` | `0.95rem` | 500 | menú |

`line-height`: headings `1.15`, body `1.6`.

## Colores

| Rol | Token | Nota |
|-----|-------|------|
| Primary | `--c-primary` | color de marca / CTA principal |
| Secondary | `--c-secondary` | acciones secundarias |
| Accent | `--c-accent` | detalles, hover, highlights (uno solo, para CTAs) |
| Background | `--c-bg` | fondo base |
| Background secondary | `--c-bg-alt` | secciones alternadas |
| Text | `--c-text` | texto principal |
| Text muted | `--c-text-muted` | texto secundario, meta |
| Border | `--c-border` | bordes, divisores |
| Success | `--c-success` | stock, confirmación |
| Error | `--c-error` | errores, sin stock |
| Sale / promo | `--c-sale` | badges de descuento, precio oferta (ecommerce) |

Cambiar estos tokens = re-brandear todo el sitio.

## Botones

| Variante | Clase | Uso |
|----------|-------|-----|
| Primary | `.btn-primary` | CTA principal (fondo `--c-primary`) |
| Secondary | `.btn-secondary` | acción secundaria |
| Outline | `.btn-outline` | terciaria (borde, fondo transparente) |

Estados: `:hover` (oscurecer 8–10% o accent), `:active` (scale 0.98), `:disabled` (opacity 0.5).
Dos familias de botón solamente (alineado con `ux-design-system`): sólido + ghost/outline;
ambos con hover legible en los dos estados.

```
--btn-padding: 0.875rem 1.75rem;
--btn-radius: var(--radius-button);
--btn-font: var(--fs-button);
--btn-transition: all 0.2s ease;
--btn-border-width: 1.5px;
```

## Spacing

Escala única. Prohibido inventar márgenes sueltos.

| Token | Valor | Uso |
|-------|-------|-----|
| `--sp-xs` | `0.5rem` (8px) | gaps internos |
| `--sp-s` | `1rem` (16px) | entre elementos |
| `--sp-m` | `1.5rem` (24px) | entre bloques |
| `--sp-l` | `3rem` (48px) | padding sección mobile |
| `--sp-xl` | `5rem` (80px) | padding sección desktop |
| `--sp-xxl` | `7.5rem` (120px) | separaciones grandes desktop |

Padding vertical de sección: `--sp-l` mobile → `--sp-xl`/`--sp-xxl` desktop.

## Contenedores

| Token | Valor | Nota |
|-------|-------|------|
| `--container-max` | `1280px` | ancho máximo |
| `--content-width` | `1140px` | ancho de contenido |
| `--pad-x-desktop` | `5%` | padding horizontal desktop |
| `--pad-x-tablet` | `32px` | padding horizontal tablet |
| `--pad-x-mobile` | `20px` | padding horizontal mobile |

## Border radius

| Elemento | Token | Default |
|----------|-------|---------|
| Cards | `--radius-card` | `12px` |
| Buttons | `--radius-button` | `8px` |
| Images | `--radius-image` | `8px` |
| Inputs | `--radius-input` | `8px` |
| Containers | `--radius-container` | `16px` |

Ajustable por marca vía la personalidad visual elegida en `ux-design-system` CAPA 2 (Minimal
Swiss / Tech Precision → 0–4px; Warm Boutique → 12–20px). Ver `design-personalities.md`.

## Notas de implementación

**html-mockup:** declara todos los `--*` en `:root` una sola vez; cada sección los referencia.
**Elementor:** tokens → Site Settings > Global Colors + Global Fonts; el resto → Global Custom
CSS con las `--*` en `:root`. **Divi:** colores/fuentes → Theme Customizer + Global Presets;
`--*` → Theme Options > Custom CSS.

Definir las variables `--*` en `:root` una sola vez = cambiar branding sin tocar módulos. Es la
clave de la reutilización, y hace que maqueta HTML y build nativo compartan el mismo origen.

## Perceptual axes — token values

### Scale (`--type-ratio`, `--display-lh`, `--fs-h1-max`)
| Position | `--type-ratio` | `--display-lh` | `--fs-h1-max` |
|---|---|---|---|
| `contained` | 1.200 | 1.25 | 48px |
| `classic` | 1.333 | 1.10 | 64px |
| `editorial` | 1.500 | 0.95 | 88px |
| `monumental` | 1.618 | 0.82 | 120px |

Every heading token derives from the ratio, never by hand:
`--fs-h3: clamp(calc(var(--fs-body) * var(--type-ratio)), 2.2vw + .6rem, …)`, h2 at the square,
h1 at the cube with `--fs-h1-max` as the hard cap. Body stays `1rem`–`1.25rem` at every position.

### Density (`--sp-scale`)
| Position | `--sp-scale` |
|---|---|
| `compact` | 0.8 |
| `standard` | 1.0 |
| `generous` | 1.35 |
| `monumental` | 1.7 |

One multiplier over the whole `--sp-*` scale, so rhythm consistency survives by construction.
Section padding becomes fluid: `padding-block: clamp(calc(2rem * var(--sp-scale)), 6vw, calc(7rem * var(--sp-scale)))`.

### Ground (`--c-bg`, `--c-bg-alt`, contrast derivation)
| Position | `--c-bg` | Contrast is derived toward |
|---|---|---|
| `paper` | `#FFFFFF` | neutral near-black |
| `warm` | cream/ivory, e.g. `#FFF3E3` | warm near-black (brown-black) |
| `cool` | very light blue-grey | deep blue-grey |
| `ink` | near-black | near-white text; re-derive the accent for contrast on dark |

### Elevation (`--elev-rest`, `--elev-hover`)
| Position | `--elev-rest` | `--elev-hover` |
|---|---|---|
| `none` | `none` | `none` — separation is whitespace |
| `hairline` | `0 0 0 1px var(--c-border)` | `0 0 0 1px var(--c-text)` |
| `soft-shadow` | `0 1px 2px rgba(0,0,0,.04)` | `0 18px 40px -12px rgba(21,24,26,.16)` |
| `accent-glow` | `0 0 0 1px color-mix(in srgb,var(--c-accent) 22%,transparent)` | `0 14px 34px -10px color-mix(in srgb,var(--c-accent) 40%,transparent)` |

### Composition
| Position | Blueprint set |
|---|---|
| `centered` | hero centred, symmetric grids, section headings centred |
| `asymmetric` | content off-centre at ~58%, one image bleeding an edge |
| `strict-grid` | everything on a 12-col grid, no bleeds, equal gutters |
| `broken-grid` | at least one element per section crossing the grid or overlapping a neighbour |
