# TPL-PDP-02 — Product (Editorial)

## 1. Identidad
Página de producto para marcas visuales: galería grande protagonista, mucha foto, storytelling del
producto. Ideal cuando la home es TPL-E-01 Visual Brand o TPL-E-03 Brand Story. Precios en **€**.

## 2. Wireframe (top → bottom)
```
COMP-HEADER [fijo]
COMP-BREADCRUMB [toggle] · minimal, puede omitirse por estética
Bloque principal [fijo · ADN]
   ├─ COMP-GALLERY full-bleed / grande (arriba o izquierda ancha)
   └─ COMP-PRODUCT-INFO sticky (precio €, variantes, add-to-cart)
Editorial del producto [fijo · ADN] · foto grande + copy (materiales, detalle, fit)
COMP-ACCORDION [fijo] · envíos / devoluciones / cuidados
COMP-PRODUCT-CAROUSEL (completá el look) [toggle TGL-RELATED]
COMP-FOOTER [fijo]
```
ADN: la foto manda, más aire, storytelling. Ausencia: densidad de datos tipo ficha técnica arriba.

## 3. Secciones
### COMP-BREADCRUMB `[toggle]`
Puede omitirse por estética minimal; si va, discreto. Reutilizable: GLOBAL.

### Bloque principal `[fijo · ADN] · TGL-PDP-STICKY`
- **COMP-GALLERY**: imágenes grandes, full-bleed o columna ancha. Mobile: carrusel full-width con
  dots. Desktop: galería vertical scrolleable (varias fotos apiladas) o principal XL + thumbs.
- **COMP-PRODUCT-INFO**: título (H1), precio € (`--fs-price`), variantes, add-to-cart. Desktop:
  **sticky** mientras la galería scrollea. Mobile: debajo, barra add-to-cart sticky. Reutilizable: GLOBAL.

### Editorial del producto `[fijo · ADN]`
Objetivo: contar el producto (branding + venta). Foto grande + texto: materiales, confección, fit,
cómo combinarlo. Mobile: 1 columna, foto arriba. Desktop: 2 columnas alternadas. Reutilizable: SECCIÓN.

### COMP-ACCORDION `[fijo]`
Envíos / Devoluciones / Cuidados. Acordeón. Reutilizable: GLOBAL.

### COMP-PRODUCT-CAROUSEL — completá el look `[toggle TGL-RELATED]`
Cross-sell estético ("completá el look"). Cards imagen grande. Reutilizable: GLOBAL.

## 4. Toggles admitidos
| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-PDP-LAYOUT` | editorial | este arquetipo |
| `TGL-PDP-STICKY` | on (desktop) | |
| `TGL-RELATED` | on | "completá el look" |
| `TGL-CARD-STYLE` | imagen grande | |

**Fijos:** HEADER, bloque galería+info, Editorial del producto, ACCORDION, FOOTER.
**Ausencias de ADN:** ficha técnica densa arriba, layout compacto → usar TPL-PDP-01.

## 5. SEO / semántica
1 `H1` (producto). `H2` en Editorial, Relacionados. Schema `Product` + `Offer` (`priceCurrency: EUR`).
`alt` en galería (importante, mucha imagen). Cuidar LCP con la primera foto grande (ver
`wordpress-performance`). `header` > `main` > `footer`.
