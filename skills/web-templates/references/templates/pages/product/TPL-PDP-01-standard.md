# TPL-PDP-01 — Product (Standard)

## 1. Identidad
Página de producto clásica y eficiente: galería a un lado, info y compra al otro. Foco en
conversión y claridad. Ideal para catálogos amplios (combina con home TPL-E-02 / TPL-E-04).
Precios en **€**.

## 2. Wireframe (top → bottom)
```
COMP-HEADER [fijo]
COMP-BREADCRUMB [fijo]
Bloque principal [fijo · ADN]
   ├─ COMP-GALLERY (izquierda / arriba en mobile)
   └─ COMP-PRODUCT-INFO (derecha): título, precio €, variantes, qty, add-to-cart, envío
COMP-ACCORDION [fijo] · descripción / envíos / devoluciones
COMP-TRUST-BADGES [toggle]
COMP-PRODUCT-CAROUSEL (relacionados) [toggle TGL-RELATED]
COMP-FOOTER [fijo]
```

## 3. Secciones
### COMP-BREADCRUMB `[fijo]`
Objetivo: ubicación + navegación + SEO. Inicio / Categoría / Producto. Mobile: truncado o solo
"volver a Categoría". Reutilizable: GLOBAL. Elementor: Breadcrumbs widget. Divi: módulo breadcrumb.

### Bloque principal `[fijo · ADN] · TGL-PDP-STICKY`
Objetivo: mostrar y vender el producto (venta).
- **COMP-GALLERY**: imagen principal + miniaturas. Mobile: carrusel swipe con dots, full-width,
  arriba. Desktop: principal grande + miniaturas verticales/horizontales, zoom en hover.
- **COMP-PRODUCT-INFO**: título (H1), precio `--fs-price` en €, precio anterior tachado si oferta,
  selector de variantes (talle/color), cantidad, **add-to-cart** (primary), info de envío/stock.
  Mobile: debajo de la galería; **barra add-to-cart sticky** al hacer scroll. Desktop: columna
  derecha, opcional sticky (`TGL-PDP-STICKY`). Reutilizable: GLOBAL (`COMP-PRODUCT-INFO`).
- Elementor: Woo Product widgets (Product Images, Title, Price, Add to Cart, Short Description).
  Divi: Woo modules equivalentes.

### COMP-ACCORDION `[fijo]`
Objetivo: detalle sin saturar (info). Tabs/acordeón: Descripción, Envíos, Devoluciones, Cuidados.
Mobile/Desktop: acordeón full-width. Reutilizable: GLOBAL (`COMP-ACCORDION`). Elementor: Accordion.
Divi: Accordion / Woo Tabs.

### COMP-TRUST-BADGES `[toggle TGL-TRUST]`
Objetivo: reducir fricción (confianza). Pago seguro, envíos, cambios. Reutilizable: GLOBAL.

### COMP-PRODUCT-CAROUSEL — relacionados `[toggle TGL-RELATED]`
Objetivo: cross-sell (venta). "También te puede gustar" 6–8 cards. Mobile: peek+swipe. Desktop: 4.
Reutilizable: GLOBAL.

## 4. Toggles admitidos
| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-PDP-LAYOUT` | standard | este arquetipo |
| `TGL-PDP-STICKY` | on (desktop) | info pegada al scroll |
| `TGL-RELATED` | on | |
| `TGL-TRUST` | on | |
| `TGL-CARD-STYLE` | según home | relacionados |

**Fijos:** HEADER, BREADCRUMB, bloque galería+info, ACCORDION, FOOTER.

## 5. SEO / semántica
1 `H1` (nombre del producto). `H2` en Descripción, Relacionados. Schema `Product` + `Offer`
(`priceCurrency: EUR`, `price`, `availability`). `alt` en todas las imágenes de galería.
`header` > `main` > `footer`. Ver `wordpress-seo`.
