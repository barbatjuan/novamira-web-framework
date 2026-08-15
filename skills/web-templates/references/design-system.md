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
heading se escribe a mano. El término preferido de cada `clamp()` interpola el SUELO de ese paso
hasta su PROPIO tope entre 430px y 1280px — no es un `vw` suelto, porque un `vw` suelto es igual en
las cuatro posiciones y aplana el eje. La fórmula completa está en "Scale" al final; aquí van los
tokens ya resueltos.

| Rol | Token | Valor | Peso | Uso |
|-----|-------|-------|------|-----|
| Font principal | `--font-primary` | — | — | Headings + UI |
| Font secundaria | `--font-secondary` | — | — | Body (opcional; puede = principal) |
| Base en px | `--fs-base` | `16` **sin unidad** | — | el puente entre `1rem` y los tramos fluidos |
| Progreso fluido | `--fluid` | `clamp(0px, calc((100vw - 430px) / 850), 1px)` | — | 0 en 430, 1px en 1280 |
| Ratio de escala | `--type-ratio` | según posición del eje Scale | — | genera todos los pasos |
| Leading display | `--display-lh` | según posición del eje Scale | — | `line-height` de h1/h2 |
| Tope de h1 | `--fs-h1-max` | según posición del eje Scale, **sin unidad** | — | fija toda la cadena de topes |
| H1 | `--fs-h1` | ver "Scale": suelo `--fs-base × ratio³` → tope `--fs-h1-max` | 700 | 1 solo por página |
| H2 | `--fs-h2` | ver "Scale": suelo `--fs-base × ratio²` → tope `--fs-h1-max ÷ ratio` | 700 | título de sección |
| H3 | `--fs-h3` | ver "Scale": suelo `--fs-base × ratio` → tope `--fs-h1-max ÷ ratio²` | 600 | subtítulos |
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
| On accent | `--c-on-accent` | la tinta que va ENCIMA del acento (etiqueta del botón primario). No se elige a mano: es el que más contraste da de `--c-text` / `--c-bg`, medido. Ver "Ground" |
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

La **etiqueta** del botón sólido no es un color que se elija: es `--c-on-accent`, y sale de medir
`--c-text` y `--c-bg` contra el relleno y quedarse con el que más contraste da. Escribir un blanco
ahí es el defecto que este token existe para quitar — sobre el verde por defecto medía 3.05:1.
Regla, medidas por ground y la banda donde ninguno de los dos llega a AA: sección "Ground".

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

Padding vertical de sección: `padding-block: var(--sp-section)`, fluido y sin breakpoint a mano.
Interpola su propio suelo hasta su propio tope entre 430 y 1280, los dos multiplicados por
`--sp-scale`, así que las cuatro densidades se separan a TODOS los anchos. Fórmula en "Density".

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
| `contained` | 1.200 | 1.25 | 48 |
| `classic` | 1.333 | 1.10 | 64 |
| `editorial` | 1.500 | 0.95 | 88 |
| `monumental` | 1.618 | 0.82 | 120 |

`--fs-h1-max` is a **unitless px count**, not a length, and that is load-bearing rather than
cosmetic. The preferred term below has to multiply a token difference against `100vw`, and
`calc()` cannot divide a length by a length — so the coefficient must arrive without a unit.
`--fs-base: 16`, the px count `1rem` resolves to, is the single bridge back to a length.

Every heading step derives from the ratio by exponentiation, never by hand. `n` is the step
(h3 = 1, h2 = 2, h1 = 3): the floor is `--fs-base × ratio^n`, the cap is `--fs-h1-max ÷ ratio^(3−n)`,
and the preferred value interpolates that step's OWN floor into that step's OWN cap across
430 → 1280. One number per position still pins all three. Written out in full:

```css
--fs-base: 16;                                            /* unitless: the px count of 1rem */
--fluid: clamp(0px, calc((100vw - 430px) / 850), 1px);    /* 0 at 430px, 1px at 1280px */

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
```

`--n-h? ÷ --fs-base × 1rem` is exactly `ratio^n × 1rem`, so the floors stay rem-relative and a
reader's default font size still moves the small end. The fluid segment and the cap are px, as
every viewport-driven step necessarily is.

h1 and h2 take `line-height: var(--display-lh)`; h3 stays `1.25` and body `1.6`. `--fs-body` keeps
a plain `1.2vw` preferred term on purpose: body is NOT an axis — it reads the same at every
position — so there is no axis for a viewport-only term to flatten there.

MEASURED in a browser at a 16px root, not reasoned about:

| Position | 430 | 768 | 1280 | 1920 |
|---|---|---|---|---|
| `editorial` h1 | 54.00px | 67.52px | **88.00px** | 88.00px |
| `monumental` h1 | 67.77px | 88.54px | **120.00px** | 120.00px |

The cap engages at 1280 — a laptop — which is the entire point. The version this replaces used
`calc(3.3vw + 1rem)` as its preferred term: no `--type-ratio` in it and no `--fs-h1-max` in it, so
it was byte-identical at all four positions. Its 88px cap engaged only above ~2181px and its 120px
cap above ~3151px; on a 1280px laptop the two positions actually rendered **58.24px and 67.77px**,
a 16% gap where this table promised 88 against 120. The paragraph that used to sit here claimed
"a 68px floor and a 120px cap" as if both were reachable — the floor was, the cap was not. Before
that it was a hardcoded `clamp(2rem, 5vw, 3.5rem)`: a 56px cap on every client site.

### Density (`--sp-scale`)
| Position | `--sp-scale` |
|---|---|
| `compact` | 0.8 |
| `standard` | 1.0 |
| `generous` | 1.35 |
| `monumental` | 1.7 |

One multiplier over the whole `--sp-*` scale, so rhythm consistency survives by construction.
Section padding interpolates its own floor into its own cap over the same 430 → 1280 range, with
`--sp-scale` on BOTH ends — which is what makes the axis visible at every width:

```css
--n-sec:     calc(2 * var(--fs-base) * var(--sp-scale));   /* unitless: 2rem worth, scaled */
--n-sec-cap: calc(7 * var(--fs-base) * var(--sp-scale));   /* unitless: 7rem worth, scaled */
--sp-section: clamp(calc(var(--n-sec) / var(--fs-base) * 1rem),
                    calc(var(--n-sec) / var(--fs-base) * 1rem + (var(--n-sec-cap) - var(--n-sec)) * var(--fluid)),
                    calc(var(--n-sec-cap) * 1px));
```

`section { padding-block: var(--sp-section) }`. MEASURED at a 16px root:

| Position | 430 | 768 | 1280 | 1920 |
|---|---|---|---|---|
| `generous` (1.35) | 43.20px | 86.14px | 151.20px | 151.20px |
| `compact` (0.8) | 25.60px | 51.05px | 89.60px | 89.60px |

Every width separates, by the constant `1.35 ÷ 0.8`. The rule this replaces was
`clamp(calc(2rem * var(--sp-scale)), 6vw, calc(7rem * var(--sp-scale)))`, whose middle term had no
`--sp-scale` in it at all: between 720px and 1493px `generous` and `compact` produced the SAME
padding — measured at **46.08px at 768 and 76.80px at 1280 on both**. A density axis that is
invisible at the two widths clients actually review on is not a density axis.

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

#### The other six ground-dependent colours are DERIVED, not tabled

The three columns above are the axis INPUT. A build has more colours than that which depend on the
ground, and for a while they did not move with it: `es-builder.php` shipped `muted`, `text_soft`,
`border`, `surface_inverse` and `on_inverse` as constants sampled off a white page. Measured on the
`ink` row of the table above, against its own `--c-bg` — which is the rule this file states two
paragraphs up and enforced for `--c-text` alone: `muted` **3.70:1**, `text_soft` **2.27:1**,
`surface_inverse` **1.06:1** (the dark button was invisible on its own page) and `border`
**15.24:1**, a near-white hairline slashed across a near-black page.

They are now blended out of `--c-text` and `--c-bg` rather than tabled per position, and the reason
is coverage: this table has four rows and a client's ground is whatever their brand is. A derived
neutral is right on grounds nobody has thought of yet; a tabled one is right on four. It also
follows `design-tokens.md` step 4, which already said to derive neutrals off the contrast colour.

| Derived token | Recipe | paper | warm | cool | ink |
|---|---|---|---|---|---|
| `--c-text-soft` | text → bg, 23% | 8.49:1 | 7.55:1 | 7.71:1 | 10.50:1 |
| `--c-text-muted` | text → bg, 36.6% | 5.20:1 | 4.78:1 | 4.92:1 | 7.40:1 |
| `--c-border` | text → bg, 89% | 1.25:1 | 1.25:1 | 1.25:1 | 1.31:1 |
| `--c-surface-inverse` | text (0%) | 17.84:1 | 15.33:1 | 15.71:1 | 17.48:1 |
| `--c-on-inverse` | bg (0%) | 17.84:1 | 15.33:1 | 15.71:1 | 17.48:1 |

Every cell is a **measured contrast against that position's own `--c-bg`** — except the last row,
which is measured against `--c-surface-inverse`, since that is what that ink sits on. The two 0%
rows are the point rather than a rounding curiosity: the surface that flips the page over IS the
contrast colour, and the ink on it IS the page's ground, so on `warm` an inverted panel is dark
brown carrying cream, not dark brown carrying a white that appears nowhere else in the palette.

**These cells are evidence, not values, and that is why the position names in this table's header
are not backticked.** `RT_AXIS_VALUE_MISSING` reads any table row containing a backticked position
name and requires a token-shaped value beside it; a contrast ratio is not one, so backticking them
here would fail the audit on correct documentation. The verifier for a derived token is not a column
in this file — it is `tests/test-write-path.php`, which recomputes every ratio above on every run
against all four positions and requires body copy ≥ 4.5:1 and the inverse surface ≥ 3:1. That is
strictly stronger than a documented literal, which is only ever as true as the day it was typed.

`--c-border` is asserted as a **range** (1.05–2.5:1) rather than against WCAG 1.4.11's 3:1. It is a
divider, and it has never reached 3:1 on any ground including the white one it was drawn for. What
went wrong on `ink` was not that it was too faint but that it stopped being a hairline at all.
**Open, not closed:** the outline button's edge reads this same token, and *that* is a control at
1.25:1, so it is a real 1.4.11 gap. Fixing it means a separate control-edge token with its own
measured cell, and it is recorded here rather than hidden behind the range.

#### `--c-on-accent` — the label on the primary button is CHOSEN, not tabled

The ink that sits on the accent is whichever of the two ground extremes — `--c-text` or `--c-bg` —
measures higher against it. It is the one derived colour that cannot be tabled per ground position,
because the surface it sits on is the **brand's** accent, and the accent is not an axis.

It was a literal `#FFFFFF`, and on this framework's own accent that is **3.05:1** — below AA, on the
label of every primary button the framework emits. Pinning the other extreme instead fixes exactly
one brand and breaks the next: a navy accent needs the white label back.

| Ground | Accent | `--c-on-accent` | Measured on the accent | White would have been |
|---|---|---|---|---|
| `paper` | `#0FA968` | `#15181A` | 5.86:1 | 3.05:1 — fails AA |
| `warm` | `#0FA968` | `#241C14` | 5.51:1 | 2.78:1 — fails AA |
| `cool` | `#0FA968` | `#141C24` | 5.65:1 | 2.78:1 — fails AA |
| `ink` | `#0FA968` | `#0E1113` | 6.22:1 | 2.81:1 — fails AA |

`ink` is the row that proves the rule is a measurement: there the winner is the near-black **`--c-bg`**,
not the near-white `--c-text`, and the same code produced both. Two more, to show the span: a pale
`#F4D03F` resolves to `#15181A` at 11.84:1, a dark `#1B2A4A` resolves to `#FFFFFF` at 14.22:1.

**There is a band of accents where NEITHER extreme reaches AA, and it is geometry rather than bad
luck.** At the accent where the two candidates cross, both measure exactly `sqrt(the ground's own
contrast)`: on `paper` (17.84:1) that is **4.22:1**, so no accent in that band can clear 4.5 against
either ink — only a pure-black-on-pure-white ground (21:1 → 4.58) would close it. Ordinary brand
colours live there: `#008899` measures 4.23 / 4.22, `#1177EE` measures 4.16 / 4.29. The build paints
the better of the two and **warns naming both measurements**; the way out is to move the accent or
to set `on_accent` by hand, and both are decisions somebody has to make rather than defaults.

**Open, not closed:** the primary button hovers to `accent_hover`, the accent darkened 18.5%, and
darkening a fill *lowers* its contrast against a dark label. With the derived label that hover
measures **4.06:1 on `paper`, 3.82 warm, 3.92 cool, 4.32 ink** — all four below AA. It was below AA
before this token was derived too (white on `#0C8A55` is 4.39:1), so it is a pre-existing gap that
moved rather than one that opened. The fix is not a second on-colour: it is that `accent_hover`
darkens unconditionally, when a button whose label is dark needs its hover to go *lighter*.

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
