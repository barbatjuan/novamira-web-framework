# Sistema Global de Diseño

La ESTRUCTURA de tokens (roles, pasos de escala, breakpoints) es compartida por TODAS las
plantillas (ecommerce y corporate) y se define **una vez**. Lo que cambia entre proyectos es la
POSICIÓN en los cinco ejes perceptuales — escala, ground, densidad, composición, elevación — que
`ux-design-system` resuelve con el cliente, independiente de qué `TPL-*` se haya elegido. Diseñado
mobile-first. Compatible con Elementor (Global Settings) y Divi (Theme Options + Global Presets),
y con la skill `html-mockup` (variables `--*` en `:root`).

Este archivo es la **autoridad sobre NOMBRES y VALORES de token**, incluidos los valores de cada
posición de eje (`--type-ratio`, `--display-lh`, `--fs-h1-max`, `--sp-scale`, `--c-bg`,
`--c-bg-alt`, `--c-text`, `--elev-rest`, `--elev-hover`): ver "Perceptual axes — token values" al
final. `design-personalities.md` es la autoridad sobre QUÉ POSICIÓN toma cada ancla en cada eje y
sobre los nombres concretos de tipografía; no define valores de token.
`ux-design-system/references/motion.md` manda sobre la curva y los rangos de duración/lift.
`ux-design-system/references/design-tokens.md` explica los ROLES (para qué sirve cada token, cómo
derivar la paleta de un logo) y no define ni valores ni nombres de fuente. Ante cualquier
diferencia, manda el archivo correspondiente a lo consultado.

Los pasos de tipografía y de spacing de abajo **no son números elegidos a mano**: derivan de
`--type-ratio` y `--sp-scale`, que fija la posición de eje. Cambiar la posición mueve la escala
entera de una vez — que es exactamente lo que antes no pasaba, cuando `--fs-h1` estaba clavado en
un tope de 56px y todos los clientes recibían la misma tipografía.

## Breakpoints (mobile-first)

| Nombre | Rango | Uso |
|--------|-------|-----|
| mobile | `< 768px` | base — se diseña primero |
| tablet | `768–1024px` | ajustes de columnas |
| desktop | `> 1024px` | layout completo |

Se escribe el CSS base para mobile y se sube con `min-width`. Nunca al revés.

## Tipografía

Dos familias máximo. Escala fluida con `clamp()`, **derivada de `--type-ratio`**: ningún paso de
heading se escribe a mano. La fórmula completa, paso por paso, está en "Scale" al final; aquí van
los tokens ya resueltos.

| Rol | Token | Valor | Peso | Uso |
|-----|-------|-------|------|-----|
| Font principal | `--font-primary` | — | — | Headings + UI |
| Font secundaria | `--font-secondary` | — | — | Body (opcional; puede = principal) |
| Ratio de escala | `--type-ratio` | según posición del eje Scale | — | genera todos los pasos |
| Leading display | `--display-lh` | según posición del eje Scale | — | `line-height` de h1/h2 |
| Tope de h1 | `--fs-h1-max` | según posición del eje Scale | — | cota superior del `clamp()` de h1 |
| H1 | `--fs-h1` | `clamp(calc(var(--fs-body) * var(--type-ratio) * var(--type-ratio) * var(--type-ratio)), calc(3.3vw + 1rem), var(--fs-h1-max))` | 700 | 1 solo por página |
| H2 | `--fs-h2` | `clamp(calc(var(--fs-body) * var(--type-ratio) * var(--type-ratio)), calc(2.2vw + 1rem), calc(var(--fs-h1-max) / var(--type-ratio)))` | 700 | título de sección |
| H3 | `--fs-h3` | `clamp(calc(var(--fs-body) * var(--type-ratio)), calc(1.1vw + 1rem), calc(var(--fs-h1-max) / var(--type-ratio) / var(--type-ratio)))` | 600 | subtítulos |
| Body | `--fs-body` | `clamp(1rem, 1.2vw, 1.25rem)` | 400 | párrafos |
| Small | `--fs-small` | `0.875rem` | 400 | notas, meta |
| Eyebrow/label | `--fs-eyebrow` | `0.75rem` | 600 uppercase, tracking | etiqueta sobre título |
| Precio | `--fs-price` | `clamp(1.1rem, 1.6vw, 1.35rem)` | 700 | precio actual (ecommerce) |
| Precio anterior | `--fs-price-old` | `0.95rem` | 400 line-through, muted | precio tachado |
| Botón | `--fs-button` | `1rem` | 600 | CTAs |
| Navegación | `--fs-nav` | `0.95rem` | 500 | menú |

`line-height`: h1/h2 usan `var(--display-lh)` (lo fija la posición del eje Scale), h3 `1.25`, body
`1.6`. Un `1.15` fijo para todos los headings era la otra mitad del defecto: el leading del display
es lo que separa un titular contenido de uno monumental, y no puede ser una constante.

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

Escala única, multiplicada entera por `--sp-scale` (la posición del eje de densidad). Prohibido
inventar márgenes sueltos.

| Token | Valor | Uso |
|-------|-------|-----|
| `--sp-xs` | `calc(0.5rem * var(--sp-scale))` | gaps internos |
| `--sp-s` | `calc(1rem * var(--sp-scale))` | entre elementos |
| `--sp-m` | `calc(1.5rem * var(--sp-scale))` | entre bloques |
| `--sp-l` | `calc(3rem * var(--sp-scale))` | padding sección mobile |
| `--sp-xl` | `calc(5rem * var(--sp-scale))` | padding sección desktop |
| `--sp-xxl` | `calc(7.5rem * var(--sp-scale))` | separaciones grandes desktop |

Con `--sp-scale: 1` los pasos valen 8 / 16 / 24 / 48 / 80 / 120px — los mismos rems fijos que este
archivo declaraba antes de que la densidad fuera un eje. La multiplicación va sobre la escala
entera, nunca sobre un token suelto: así el ritmo sobrevive por construcción y lo único que cambia
es la sensación de aire.

Padding vertical de sección: fluido, sin breakpoint a mano —
`padding-block: clamp(calc(2rem * var(--sp-scale)), 6vw, calc(7rem * var(--sp-scale)))`.

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

Estos son los defaults. El ancla resuelta puede moverlos cuando su **Card recipe** en
`design-personalities.md` lo pide (p. ej. `PERS-MATTER` pone la imagen al radio del contenedor);
donde la receta calla, manda la tabla. Los NOMBRES de token no cambian nunca.

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

Every heading step derives from the ratio by exponentiation, never by hand. `n` is the step
(h3 = 1, h2 = 2, h1 = 3): the floor is `--fs-body × ratio^n`, the preferred value is
`n × 1.1vw + 1rem`, and the whole cap chain hangs off `--fs-h1-max`, so one number per position
pins all three. Written out in full, with no step left to the reader:

```css
--fs-h1: clamp(calc(var(--fs-body) * var(--type-ratio) * var(--type-ratio) * var(--type-ratio)),
               calc(3.3vw + 1rem),
               var(--fs-h1-max));
--fs-h2: clamp(calc(var(--fs-body) * var(--type-ratio) * var(--type-ratio)),
               calc(2.2vw + 1rem),
               calc(var(--fs-h1-max) / var(--type-ratio)));
--fs-h3: clamp(calc(var(--fs-body) * var(--type-ratio)),
               calc(1.1vw + 1rem),
               calc(var(--fs-h1-max) / var(--type-ratio) / var(--type-ratio)));
```

h1 and h2 take `line-height: var(--display-lh)`; h3 stays `1.25` and body `1.6`. Body itself stays
`1rem`–`1.25rem` at every position: what changes is the RANGE, not the reading size.

At `monumental` and a 16px body that is a 68px floor and a 120px cap for h1 (`1.618³ × 16 ≈ 68`);
at `contained`, a 28px floor and a 48px cap (`1.2³ × 16 ≈ 28`). The defect this replaces was a
hardcoded `clamp(2rem, 5vw, 3.5rem)` — a 56px cap on every client site, below even `contained`.

### Density (`--sp-scale`)
| Position | `--sp-scale` |
|---|---|
| `compact` | 0.8 |
| `standard` | 1.0 |
| `generous` | 1.35 |
| `monumental` | 1.7 |

One multiplier over the whole `--sp-*` scale, so rhythm consistency survives by construction.
Section padding becomes fluid: `padding-block: clamp(calc(2rem * var(--sp-scale)), 6vw, calc(7rem * var(--sp-scale)))`.

### Ground (`--c-bg`, `--c-bg-alt`, `--c-text`)
Every cell is a literal. `--c-text` is derived exactly as `design-tokens.md` step 3 specifies — the
darkest brand neutral pushed toward near-black, or toward near-white on a dark ground — and each
pair was contrast-checked against its OWN `--c-bg`, not against white. "very light blue-grey" and
"near-black" were the two positions that shipped as adjectives; a builder cannot paint an adjective.

| Position | `--c-bg` | `--c-bg-alt` | `--c-text` | Measured contrast |
|---|---|---|---|---|
| `paper` | `#FFFFFF` | `#F6F7F8` | `#15181A` | 17.8:1 |
| `warm` | `#FFF3E3` | `#F7E8D4` | `#241C14` | 15.3:1 |
| `cool` | `#F2F5F8` | `#E8EDF3` | `#141C24` | 15.7:1 |
| `ink` | `#0E1113` | `#171B1E` | `#F4F6F7` | 17.5:1 |

`ink` inverts the derivation, and that is the one that bites: the accent has to be re-derived to
clear 4.5:1 against `#0E1113`, because an accent that passed on `paper` will usually fail here.

### Elevation (`--elev-rest`, `--elev-hover`)
| Position | `--elev-rest` | `--elev-hover` |
|---|---|---|
| `none` | `none` | `none` — separation is whitespace |
| `hairline` | `0 0 0 1px var(--c-border)` | `0 0 0 1px var(--c-text)` |
| `soft-shadow` | `0 1px 2px rgba(0,0,0,.04)` | `0 18px 40px -12px rgba(21,24,26,.16)` |
| `accent-glow` | `0 0 0 1px color-mix(in srgb,var(--c-accent) 22%,transparent)` | `0 14px 34px -10px color-mix(in srgb,var(--c-accent) 40%,transparent)` |

### Composition (one blueprint per position)
The only axis whose value is a layout rule rather than a number, so each position names a blueprint
instead. The blueprints are defined in `ux-design-system/references/layout-patterns.md`, where each
one fixes column count, where the content sits, and what an image may do — enough specificity that
two anchors over identical content render as visibly different pages. Four prose sentences, which
is what this table held before, were not: nothing downstream could act on them.

| Position | Blueprint | In one line |
|---|---|---|
| `centered` | `LP-CENTERED` | one symmetric axis, nothing bleeds |
| `asymmetric` | `LP-ASYMMETRIC` | copy on 7 of 12 columns, one image bleeding a viewport edge |
| `strict-grid` | `LP-STRICT-GRID` | every element starts and ends on a column line |
| `broken-grid` | `LP-BROKEN-GRID` | one element per section crosses the grid or overlaps a neighbour |
