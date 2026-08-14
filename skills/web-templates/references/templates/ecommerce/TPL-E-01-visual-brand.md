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
| ADN | Hero grande tipo slider + la foto manda. NO usa urgencia ni densidad de catálogo arriba. |

## 2. Wireframe (top → bottom)

```
COMP-ANNOUNCEMENT        [toggle]
COMP-HEADER              [fijo]
COMP-HERO (slider 60vh)  [fijo · ADN]
COMP-CATEGORY-CARD       [fijo]   · 3–4 categorías visuales
COMP-PRODUCT-CAROUSEL    [fijo]   · destacados, no grilla densa
Editorial / Lookbook     [toggle] · imagen + texto
COMP-BENEFITS            [toggle]
COMP-TESTIMONIAL         [toggle]
COMP-NEWSLETTER          [toggle]
COMP-FOOTER              [fijo]
```

El hero domina la primera pantalla. El producto aparece con estética, nunca apilado.
Ausencia intencional: sin countdown, sin promo agresiva, sin grillas densas arriba.

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

### Editorial / Lookbook `[toggle TGL-FOCUS = branding]`
Objetivo: contar la estética (branding). Imagen grande + texto + CTA, alternando lado.
Mobile: 1 columna (imagen arriba). Desktop: 2 columnas alternadas. CTA: secondary/outline.
Reutilizable: SECCIÓN específica.

### COMP-BENEFITS `[toggle TGL-BENEFITS]`
Objetivo: confianza (envíos, pagos, cambios). 3–4 ítems ícono + título + línea.
Mobile: 2×2 o carrusel. Desktop: fila 3–4. Reutilizable: GLOBAL.

### COMP-TESTIMONIAL `[toggle TGL-TESTIMONIALS]`
Objetivo: social proof (confianza). 2–3 reseñas (texto, nombre, rating).
Mobile: carrusel 1/vista. Desktop: 3 en fila o carrusel. Reutilizable: GLOBAL.

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
| `TGL-FOCUS` | branding | branding activa Editorial/Lookbook |
| `TGL-BENEFITS` | on | |
| `TGL-TESTIMONIALS` | on | |
| `TGL-NEWSLETTER` | on | |
| `TGL-TRUST` | on (footer) | |

**Fijos:** COMP-HEADER, COMP-HERO, COMP-CATEGORY-CARD, COMP-PRODUCT-CAROUSEL, COMP-FOOTER.
**Ausencias de ADN:** countdown, promo agresiva, grilla densa arriba, search protagonista →
si el cliente los pide, sugerir TPL-E-02 o TPL-E-05.

## 5. SEO / semántica
1 `H1` (slide 1). `H2` por sección. `H3` en cards. `header` > `main` > `footer`. Hero con carga
prioritaria, resto lazy.
