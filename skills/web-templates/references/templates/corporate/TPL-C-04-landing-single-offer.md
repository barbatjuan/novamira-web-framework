# TPL-C-04 — Landing / Single-Offer

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Landing / Single-Offer |
| Objetivo | Convertir en UNA sola oferta: secuencia persuasiva hacia un CTA |
| Ideal para | Un servicio/producto único, campañas B2B, lanzamientos, SaaS feature, infoproductos |
| Ejemplos | Landing de un curso, demo de SaaS, campaña de un servicio, lead magnet |
| Nivel de contenido | Medio (copy persuasivo estructurado) |
| Protagonismo | La OFERTA única y su conversión |
| ADN | Una oferta, un objetivo, secuencia problema→solución→beneficios→prueba→CTA, CTA repetido. NO menú amplio, NO múltiples servicios. |

## 2. Wireframe (top → bottom)

```
COMP-HEADER (logo + 1 CTA) [fijo · minimal]
COMP-HERO (propuesta única + CTA fuerte ~55vh) [fijo · ADN]
COMP-PROBLEM (dolor / por qué importa) [fijo]
COMP-SOLUTION (la oferta / beneficios) [fijo · ADN]
COMP-FEATURES (qué incluye) [toggle]
COMP-LOGOS / COMP-TESTIMONIAL (prueba) [fijo]
COMP-PRICING (planes / precio) [toggle]
COMP-FAQ [toggle]
COMP-CTA (cierre final) [fijo · ADN]
COMP-FOOTER (minimal) [fijo]
```

Un solo camino a la conversión. Ausencia intencional: sin navegación amplia, sin catálogo, sin distracciones.

## 3. Secciones

### COMP-HEADER — minimal `[fijo]`
Objetivo: sin distracción, foco en CTA (conversión). Logo + 1 botón CTA (sin menú o menú de anclas).
Mobile: logo + CTA. Desktop: igual, sticky. Reutilizable: GLOBAL (variante landing).

### COMP-HERO — propuesta única `[fijo · ADN] · TGL-HERO-HEIGHT`
Objetivo: comunicar la oferta y su valor (conversión). Headline de beneficio + subtítulo + CTA
fuerte + (opcional) imagen/mockup del producto. Mobile: 55vh, CTA full-width. Desktop: split
texto | visual. H1 único. Reutilizable: SECCIÓN.

### COMP-PROBLEM — dolor `[fijo]`
Objetivo: generar identificación (persuasión). Enunciar el problema que resuelve la oferta. Mobile:
1 columna. Desktop: texto + ilustración o 3 puntos de dolor. Reutilizable: SECCIÓN.

### COMP-SOLUTION — la oferta `[fijo · ADN]`
Objetivo: presentar la solución/beneficios (conversión). Bloques de beneficios (no features
técnicas): qué gana el usuario. Mobile: 1 columna. Desktop: 2–3 o alternado imagen/texto.
Reutilizable: SECCIÓN.

### COMP-FEATURES — qué incluye `[toggle]`
Objetivo: detalle de lo entregado (venta). Lista/grid de características. Mobile: 1 columna.
Desktop: 2–3. Reutilizable: GLOBAL.

### COMP-LOGOS / COMP-TESTIMONIAL — prueba `[fijo]`
Objetivo: reducir riesgo percibido (confianza). Logos + 1–3 testimonios fuertes. Reutilizable: GLOBAL.

### COMP-PRICING — planes `[toggle TGL-PRICING]`
Objetivo: presentar precio/planes (conversión). 1–3 planes con CTA. Mobile: 1 columna (destacado
primero). Desktop: 2–3 en fila, uno resaltado. Reutilizable: GLOBAL (`COMP-PRICING`).

### COMP-FAQ `[toggle TGL-FAQ]`
Objetivo: resolver objeciones (persuasión). Acordeón de preguntas. Mobile/Desktop: acordeón full-width.
Reutilizable: GLOBAL (`COMP-FAQ`). Elementor: Accordion/Toggle. Divi: Accordion.

### COMP-CTA — cierre `[fijo · ADN]`
Objetivo: última conversión (conversión). Banda con claim + CTA fuerte repetido. Reutilizable: GLOBAL.

### COMP-FOOTER — minimal `[fijo]`
Objetivo: legal + contacto mínimo. Sin navegación amplia. Reutilizable: GLOBAL.

## 4. Toggles admitidos

| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-HERO-HEIGHT` | 55vh | |
| `TGL-FEATURES` | on | |
| `TGL-PRICING` | on | apagar si no hay precio público |
| `TGL-FAQ` | on | |
| `TGL-STYLE` | moderno / SaaS | |
| `TGL-CTA-STRENGTH` | fuerte | ADN |

**Fijos:** COMP-HEADER (minimal), COMP-HERO, COMP-PROBLEM, COMP-SOLUTION, COMP-LOGOS/TESTIMONIAL, COMP-CTA, COMP-FOOTER.
**Ausencias de ADN:** menú amplio, múltiples servicios, portfolio, booking → sugerir TPL-C-01, TPL-C-03 o TPL-C-05.

## 5. SEO / semántica
1 `H1` (hero). `H2` en Problema, Solución, Precio, FAQ. Schema `Product`/`Offer` + `FAQPage` si hay
FAQ. `header` > `main` > `footer`. CTAs con anclas.
