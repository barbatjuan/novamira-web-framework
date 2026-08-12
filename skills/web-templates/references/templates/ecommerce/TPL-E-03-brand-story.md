# TPL-E-03 — Brand Story

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Brand Story |
| Objetivo | Confianza + branding: la historia y los valores venden antes que el catálogo |
| Ecommerce ideal | Cosmética artesanal, joyería, marca de autor, alimentos premium, slow fashion |
| Ejemplos | Skincare natural, orfebrería, café de especialidad, marca sustentable |
| Nivel de contenido | Alto (editorial, storytelling) |
| Protagonismo del producto | Bajo–medio — productos selectos, curados |
| Protagonismo de la marca | Muy alto |
| ADN | Hero de 1 imagen fija chica, mucho aire, historia + testimonios FIJOS. NO slider, NO densidad, NO urgencia. |

## 2. Wireframe (top → bottom)

```
COMP-HEADER              [fijo]
COMP-HERO (imagen fija ~40vh) [fijo · ADN]
COMP-BENEFITS (valores)  [fijo] · beneficios/valores de marca
Brand Story              [fijo · ADN] · historia, misión, proceso
COMP-CATEGORY-CARD       [toggle] · pocas categorías curadas
COMP-PRODUCT-CAROUSEL    [fijo] · selección, no catálogo completo
COMP-TESTIMONIAL         [fijo · ADN] · social proof
COMP-NEWSLETTER          [toggle]
COMP-FOOTER              [fijo]
```

Mucho aire, ritmo pausado. Ausencia intencional: sin slider, sin grillas densas, sin countdown,
sin promo agresiva.

## 3. Secciones

### COMP-HEADER `[fijo]`
Objetivo: navegación sobria (branding). Logo protagonista (puede ir centrado), menú reducido,
cart, account. Mobile: logo centrado, hamburguesa, cart. Desktop: menú minimal, mucho espacio.
Sticky opcional (transparente sobre hero). Reutilizable: GLOBAL.

### COMP-HERO — imagen fija `[fijo · ADN] · TGL-HERO-TYPE fija-only`
Objetivo: transmitir sensación de marca (branding). 1 imagen fija (no slider), ~40vh, título +
subtítulo + 1 CTA suave. Mucho aire alrededor. Mobile: 35vh, texto centrado. Desktop: 40vh.
H1 único acá. Toggle: `TGL-HERO-TYPE` bloqueado en imagen fija (parte del ADN). Reutilizable:
SECCIÓN. Elementor: container con background + heading. Divi: Fullwidth Header.

### COMP-BENEFITS — valores `[fijo]`
Objetivo: comunicar propuesta de valor (confianza). 3–4 pilares (ej: natural, cruelty-free, hecho
a mano) ícono + título + línea. Mobile: 1–2 columnas. Desktop: fila. Reutilizable: GLOBAL.

### Brand Story `[fijo · ADN]`
Objetivo: contar la historia/misión/proceso (branding + confianza). Bloques imagen + texto largo,
alternados; puede incluir cita, foto del fundador, proceso. Mobile: 1 columna, imagen arriba.
Desktop: 2 columnas alternadas, mucho aire. CTA: outline ("Conocé más"). Reutilizable: SECCIÓN.

### COMP-CATEGORY-CARD — curadas `[toggle TGL-CATEGORIES]`
Objetivo: guiar a colecciones selectas (navegación). 2–3 cards grandes, editoriales. Mobile: 1
columna. Desktop: 2–3. Reutilizable: GLOBAL.

### COMP-PRODUCT-CAROUSEL — selección `[fijo] · TGL-CARD-STYLE`
Objetivo: mostrar productos selectos (venta suave). 4–6 cards imagen grande. Mobile: peek + swipe.
Desktop: 3–4. Cards con imagen grande (default, coherente con lo editorial). Reutilizable: GLOBAL.

### COMP-TESTIMONIAL `[fijo · ADN]`
Objetivo: social proof, refuerza confianza (confianza). 3+ reseñas con foto/nombre, o reseñas
destacadas. Mobile: carrusel. Desktop: 3 en fila o destacada grande. Reutilizable: GLOBAL. Fijo:
en esta plantilla los testimonios son parte del ADN, no se preguntan.

### COMP-NEWSLETTER `[toggle TGL-NEWSLETTER]`
Objetivo: captación con tono de comunidad. Copy cálido. Reutilizable: GLOBAL.

### COMP-FOOTER `[fijo]`
Objetivo: navegación + legal + redes + valores. Puede repetir un mini-claim de marca.
Mobile: apilado. Desktop: 3–4 columnas. Reutilizable: GLOBAL.

## 4. Toggles admitidos

| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-HERO-TYPE` | imagen fija | **bloqueado** (ADN) |
| `TGL-CATEGORIES` | on | pocas, curadas |
| `TGL-CARD-STYLE` | imagen grande | |
| `TGL-NEWSLETTER` | on | |
| `TGL-TRUST` | on (footer) | |

**Fijos:** COMP-HEADER, COMP-HERO (imagen fija), COMP-BENEFITS (valores), Brand Story,
COMP-PRODUCT-CAROUSEL, COMP-TESTIMONIAL, COMP-FOOTER.
**Ausencias de ADN:** slider, grilla densa de producto, countdown, promo agresiva, search
protagonista → si el cliente los pide, sugerir TPL-E-01, TPL-E-02 o TPL-E-05.

## 5. SEO / semántica
1 `H1` (hero). `H2` en Historia, Testimonios, etc. `H3` en subtítulos de la historia. Texto largo
= bueno para SEO editorial. `header` > `main` > `footer`.
