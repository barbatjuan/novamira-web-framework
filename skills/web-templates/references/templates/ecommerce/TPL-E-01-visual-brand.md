# TPL-E-01 — Visual Brand

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Visual Brand |
| Objetivo | Descubrimiento + branding a través de fotografía protagonista |
| Ecommerce ideal | Moda, decoración, lifestyle, accesorios |
| Ejemplos | Marca de ropa, tienda de deco, joyería de diseño, óptica fashion |
| Nivel de contenido | Medio (visual, poco texto) |
| Protagonismo del producto | Medio — mostrado con estética, no en grillas densas |
| Protagonismo de la marca | Alto |
| ADN | Hero grande tipo slider + lookbook shoppable: la foto abre y la foto cierra. NO usa urgencia, densidad de catálogo ni storytelling largo. |

## 2. Wireframe (top → bottom)

```
COMP-ANNOUNCEMENT        [toggle]
COMP-HEADER              [fijo]
COMP-HERO (slider 60vh)  [fijo · ADN]
COMP-CATEGORY-CARD       [fijo]   · 3–4 colecciones visuales
COMP-PRODUCT-CAROUSEL    [fijo]   · destacados, no grilla densa
COMP-GALLERY (lookbook)  [fijo · ADN] · campaña shoppable, casi sin texto
COMP-TESTIMONIAL         [toggle]
COMP-BOOKING (cita de estilismo / medición) [fijo · ADN] · banda de cierre
COMP-NEWSLETTER          [toggle]
COMP-FOOTER              [fijo]
```

El hero domina la primera pantalla. El producto aparece con estética, nunca apilado.
Ausencia intencional: sin countdown, sin promo agresiva, sin grillas densas arriba, sin barra de
beneficios operativos (envío/pago/cambios) — eso es mobiliario de catálogo y rompe el ritmo
editorial; la confianza operativa vive en el footer vía `TGL-TRUST`. Sin bloques largos de
imagen+texto alternado: eso es storytelling y es el ADN de TPL-E-03.

**El envoltorio de cada sección es parte del contrato.** `RT_TPL_TOO_SIMILAR` mide el INVENTARIO
—lo único que un documento sabía declarar— y no ve la FORMA. Medido en la galería: veintidós de las
veintitrés arquitecturas no dejaban que ningún elemento tocara el borde de la pantalla, así que dos
arquetipos con inventarios distintos seguían leyéndose como la misma página con otra paleta. Los
tres envoltorios (`contenido`, `banda`, `fila`) son vocabulario compartido; lo propio de cada
arquetipo es CUÁL pide cada sección.

| Sección | Envoltorio | Por qué |
|---------|-----------|---------|
| `COMP-GALLERY` | **banda** | Aquí el lookbook ES el catálogo: se entra a la tienda mirando, no filtrando. Seis frames sin pie, a sangre. |
| El resto | contenido | |

## 3. Secciones

### COMP-ANNOUNCEMENT `[toggle TGL-ANNOUNCEMENT]`
Objetivo: envío gratis / novedad (branding suave). Componentes: texto corto + link opcional.
Mobile: una línea, sin link secundario. Desktop: texto completo centrado. Reutilizable: GLOBAL.
Elementor: barra en Theme Builder header. Divi: módulo texto fijado en Theme Builder.

### COMP-HEADER `[fijo]`
Objetivo: navegación + carrito/cuenta/búsqueda. Componentes: logo, menú, search, cart, account.
Mobile: hamburguesa, cart visible, search en overlay, sticky. Desktop: menú horizontal, sticky
al scroll. CTA: ícono carrito. Reutilizable: GLOBAL (`COMP-HEADER`). Elementor/Divi: Theme Builder > Header.

### COMP-HERO — slider `[fijo · ADN] · TGL-HERO-TYPE, TGL-HERO-HEIGHT`
Objetivo: impacto visual, entrada a la marca. Componentes: 2–4 slides (imagen full-bleed, título,
CTA). Mobile: 45vh, imagen recortada al centro, overlay legible, 1 CTA, swipe, dots (sin flechas).
Desktop: 60vh, flechas + dots, autoplay lento (6–8s) con pausa en hover. H1 único en slide 1.
CTA: primary dentro del hero. Toggle: slider↔imagen fija, altura. Elementor: Slides/Loop + overlay.
Divi: Fullwidth Slider.

### COMP-CATEGORY-CARD `[fijo]`
Objetivo: guiar a colecciones (navegación + descubrimiento). 3–4 cards imagen + nombre + link.
Mobile: 1–2 columnas. Tablet: 2. Desktop: 3–4, hover zoom suave. Card completa clickeable.
Reutilizable: GLOBAL. Elementor: Loop Grid categorías. Divi: Portfolio / Woo categorías.

### COMP-PRODUCT-CAROUSEL — destacados `[fijo] · TGL-CARD-STYLE, TGL-CARD-IMG`
Objetivo: mostrar producto sin romper estética (venta suave). Carrusel 6–8 `COMP-PRODUCT-CARD`.
Mobile: 1.2 cards visibles (peek), swipe. Tablet: 2.5. Desktop: 4, flechas, hover 2ª imagen/quick-add.
Reutilizable: GLOBAL. Elementor: Loop Carousel. Divi: Woo Products en carousel.

### COMP-GALLERY — lookbook shoppable `[fijo · ADN] · TGL-IMAGERY`
Objetivo: vender el look completo, no la pieza suelta (descubrimiento + branding). 4–8 imágenes de
campaña a tamaño grande, cada una enlazando a los productos que aparecen en ella; texto mínimo
(nombre de la campaña y poco más). Mobile: carrusel full-width con swipe, 1 imagen por vista.
Tablet: 2 columnas. Desktop: grid 2–3 o masonry, hover revelando los productos de la foto.
Reutilizable: GLOBAL (`COMP-GALLERY`). Elementor: Gallery / Loop Grid con enlace a colección.
Divi: Gallery / Portfolio.

**Es una galería, no un editorial.** La versión anterior de este bloque era "imagen grande + texto
+ CTA, alternando lado", que es exactamente la gramática de `COMP-ABOUT` en TPL-E-03 — storytelling
prestado. Con "Nivel de contenido: Medio (visual, poco texto)" declarado arriba, lo que le
corresponde a este arquetipo es la foto sin párrafo alrededor. Si el cliente quiere contar la
historia larga, el arquetipo es TPL-E-03, no este con más texto.

### COMP-TESTIMONIAL `[toggle TGL-TESTIMONIALS]`
Objetivo: social proof (confianza). 2–3 reseñas (texto, nombre, rating).
Mobile: carrusel 1/vista. Desktop: 3 en fila o carrusel. Reutilizable: GLOBAL.

### COMP-BOOKING — cita de estilismo / medición `[fijo · ADN] · TGL-BOOKING`
Objetivo: cerrar quitando el riesgo de que NO ENCAJE (conversión). Banda final con claim + reserva
de una cita: asesoría de estilismo, prueba en tienda, medición a domicilio para deco, ajuste de
montura en óptica. Formulario/embed de agenda, o CTA a WhatsApp si no hay plugin. Mobile:
full-width, 1 servicio + 1 fecha, CTA full-width. Desktop: claim | agenda en dos columnas.
Reutilizable: GLOBAL (`COMP-BOOKING`). **Nota:** depende de plugin (Bookly, Amelia, formulario o
link externo) — validar en `project-context`; si no hay, degradar a WhatsApp/teléfono.

**Por qué esta banda y no un formulario de contacto.** En moda, deco y óptica la última objeción no
es "no confío", es "no sé si me va a quedar / si entra en el ambiente / si me favorece". La
fotografía creó el deseo arriba; el cierre retira el riesgo de talle y medida, que es el único
servicio que una marca visual puede ofrecer y un catálogo no. Un `COMP-LEAD-FORM` corporativo pide
un dato y no devuelve nada — acá el cliente reserva algo que la tienda efectivamente presta.

### COMP-NEWSLETTER `[toggle TGL-NEWSLETTER]`
Objetivo: captación de email. Título + input + botón, fondo `--c-bg-alt`.
Mobile: stack vertical. Desktop: inline. CTA: primary. Reutilizable: GLOBAL.

### COMP-FOOTER `[fijo]`
Objetivo: navegación secundaria, legal, redes, pagos. Columnas + redes + medios de pago + copyright.
Mobile: acordeón/apilado. Desktop: 3–4 columnas. Reutilizable: GLOBAL. Theme Builder > Footer.

## 4. Toggles admitidos

| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-ANNOUNCEMENT` | on | |
| `TGL-HERO-TYPE` | slider | ADN sugiere slider; permite imagen fija |
| `TGL-HERO-HEIGHT` | 60vh (45vh mobile) | |
| `TGL-CARD-STYLE` | imagen grande | |
| `TGL-CARD-IMG` | sí | |
| `TGL-IMAGERY` | foto | el lookbook no admite ilustración: es campaña real |
| `TGL-TESTIMONIALS` | on | |
| `TGL-BOOKING` | form/embed | o WhatsApp/teléfono si no hay plugin |
| `TGL-NEWSLETTER` | on | |
| `TGL-TRUST` | on (footer) | acá viven envíos/pagos/cambios, no en una banda |

**Fijos:** COMP-HEADER, COMP-HERO, COMP-CATEGORY-CARD, COMP-PRODUCT-CAROUSEL, COMP-GALLERY
(lookbook), COMP-BOOKING (cierre), COMP-FOOTER.
**Ausencias de ADN:** countdown, promo agresiva, grilla densa arriba, search protagonista, barra de
beneficios operativos, storytelling largo → si el cliente los pide, sugerir TPL-E-02, TPL-E-03 o
TPL-E-05.

## 5. SEO / semántica
1 `H1` (slide 1). `H2` por sección. `H3` en cards. `header` > `main` > `footer`. Hero con carga
prioritaria, resto lazy. El lookbook necesita `alt` descriptivo por imagen: es contenido, no adorno.
