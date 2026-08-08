# TPL-E-05 — Promo / Campaign

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Promo / Campaign |
| Objetivo | Urgencia + venta agresiva: la oferta/campaña es la protagonista |
| Ecommerce ideal | Outlet, liquidaciones, temporada, Black Friday, Hot Sale, lanzamientos |
| Ejemplos | Landing de campaña, outlet permanente, tienda estacional |
| Nivel de contenido | Bajo (todo apunta a la conversión) |
| Protagonismo del producto | Alto — ofertas visibles de inmediato |
| Protagonismo de la marca | Bajo |
| ADN | Hero promo con countdown + CTA fuerte, urgencia en toda la página. Única plantilla con countdown. |

## 2. Wireframe (top → bottom)

```
COMP-ANNOUNCEMENT (urgencia) [fijo · ADN] · cupón / cuenta regresiva / envío
COMP-HEADER              [fijo]
COMP-HERO (promo ~50vh + countdown) [fijo · ADN]
COMP-PRODUCT-GRID (ofertas) [fijo · ADN] · productos en descuento con badge
COMP-PROMO-BANNER (x2–3)  [fijo] · bloques de promoción secundarios
COMP-PRODUCT-CAROUSEL (más ofertas) [toggle]
COMP-CATEGORY-CARD       [toggle] · "comprá por descuento %"
COMP-TRUST-BADGES        [toggle] · pago seguro (reduce fricción)
COMP-NEWSLETTER (cupón)  [toggle]
COMP-FOOTER              [fijo]
```

Todo grita conversión. Ausencia intencional: sin storytelling, sin editorial lento, sin aire.

## 3. Secciones

### COMP-ANNOUNCEMENT — urgencia `[fijo · ADN]`
Objetivo: instalar urgencia desde arriba (venta). Cupón, "envío gratis hoy", o mini countdown.
Fondo `--c-sale`. Mobile: una línea condensada. Desktop: con código de cupón. Reutilizable: GLOBAL.
Fijo: en esta plantilla siempre va (no se pregunta).

### COMP-HEADER `[fijo]`
Objetivo: navegación mínima, foco en carrito (venta). Logo, menú corto, search, **cart destacado**
(con contador). Mobile: cart prominente, hamburguesa. Desktop: menú reducido, cart con total.
Sticky. Reutilizable: GLOBAL.

### COMP-HERO — promo + countdown `[fijo · ADN] · TGL-HERO-HEIGHT`
Objetivo: comunicar la oferta principal y crear urgencia (venta). Banner ~50vh: título de campaña
grande (ej: "-50% TODO"), subtítulo, **cuenta regresiva**, CTA fuerte ("Comprar ahora"). Mobile:
45vh, countdown legible, CTA full-width. Desktop: 50vh, countdown grande. H1 acá. Reutilizable:
SECCIÓN. **Nota:** el countdown suele necesitar widget específico (Elementor: Countdown widget;
Divi: módulo Countdown Timer) — introspectar antes de construir.

### COMP-PRODUCT-GRID — ofertas `[fijo · ADN] · TGL-CARD-STYLE, TGL-CARD-IMG`
Objetivo: venta inmediata de productos en descuento (venta). Grilla de `COMP-PRODUCT-CARD` con
**badge de descuento** (`--c-sale`), precio anterior tachado (`--fs-price-old`) + precio oferta.
Mobile: 2 columnas. Desktop: 3–4. Reutilizable: GLOBAL (card variante sale). Elementor: Loop Grid
Woo filtrando on-sale. Divi: Woo Products on-sale.

### COMP-PROMO-BANNER — bloques promo `[fijo]`
Objetivo: empujar promociones específicas (venta). 2–3 banners (ej: "2x1", "hasta -70%", "solo
hoy") imagen + texto + CTA. Mobile: apilados full-width. Desktop: 2–3 en fila o banda ancha.
Reutilizable: GLOBAL (`COMP-PROMO-BANNER`).

### COMP-PRODUCT-CAROUSEL — más ofertas `[toggle]`
Objetivo: seguir mostrando descuentos (venta). Carrusel de ofertas. Reutilizable: GLOBAL.

### COMP-CATEGORY-CARD — por descuento `[toggle TGL-CATEGORIES]`
Objetivo: navegar por nivel de descuento (venta). Cards "hasta -30/50/70%". Reutilizable: GLOBAL.

### COMP-TRUST-BADGES `[toggle TGL-TRUST]`
Objetivo: reducir fricción en compra impulsiva (confianza). Pago seguro, medios, devolución.
Fila compacta. Reutilizable: GLOBAL.

### COMP-NEWSLETTER — cupón `[toggle TGL-NEWSLETTER]`
Objetivo: captar con incentivo ("10% en tu primera compra"). CTA fuerte. Reutilizable: GLOBAL.

### COMP-FOOTER `[fijo]`
Objetivo: legal (importante en promos: bases y condiciones), pagos, links. Mobile: apilado.
Desktop: 3–4 columnas + link a "términos de la promoción". Reutilizable: GLOBAL.

## 4. Toggles admitidos

| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-ANNOUNCEMENT` | on | **fijo/ADN**, no se apaga |
| `TGL-HERO-HEIGHT` | 50vh (45vh mobile) | |
| `TGL-CARD-STYLE` | imagen grande con badge | |
| `TGL-CARD-IMG` | sí | |
| `TGL-CATEGORIES` | off | opcional (por descuento) |
| `TGL-TRUST` | on | reduce fricción |
| `TGL-NEWSLETTER` | on | con cupón |
| `TGL-CTA-STRENGTH` | fuerte | ADN |
| `TGL-STYLE` | comercial | |

**Fijos:** COMP-ANNOUNCEMENT (urgencia), COMP-HEADER, COMP-HERO (promo+countdown),
COMP-PRODUCT-GRID (ofertas), COMP-PROMO-BANNER, COMP-FOOTER.
**Ausencias de ADN:** storytelling, editorial, lookbook, mucho aire → si el cliente los pide,
sugerir TPL-E-01 o TPL-E-03.

## 5. SEO / semántica
1 `H1` (campaña). `H2` por bloque de promo. Cuidar que el countdown no bloquee LCP. Marcar precios
con schema `Offer`/`priceValidUntil` (ver `wordpress-seo`). `header` > `main` > `footer`.
