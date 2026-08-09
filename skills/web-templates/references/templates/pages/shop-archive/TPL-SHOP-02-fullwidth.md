# TPL-SHOP-02 — Shop / Catálogo (Full-width)

## 1. Identidad
Archive de productos **full-width**, sin sidebar: filtros en barra superior, grilla ancha. Ideal para
marcas visuales con catálogo curado y pocos atributos — combina con home TPL-E-01 / TPL-E-03.
Precios en **€**.

## 2. Wireframe (top → bottom)
```
COMP-HEADER [fijo]
COMP-BREADCRUMB [toggle]
Encabezado de archive [fijo] · H1 + intro corta + nº resultados
COMP-TOOLBAR (filtros + orden en barra horizontal) [fijo · ADN]
COMP-PRODUCT-GRID (ancha) [fijo · ADN]
COMP-PAGINATION [fijo]
COMP-FOOTER [fijo]
```
ADN: sin sidebar, grilla protagonista, estética limpia. Ausencia: filtros complejos en columna.

## 3. Secciones
### Encabezado `[fijo]`
H1 de categoría + intro editorial corta + nº resultados. Centrado, con aire. Reutilizable: SECCIÓN.

### COMP-TOOLBAR — filtros horizontales `[fijo · ADN] · TGL-SHOP-FILTERS(topbar), TGL-SHOP-SORT`
Objetivo: filtrar/ordenar sin robar ancho (navegación). Chips/dropdowns horizontales (categoría,
precio €, orden). Mobile: scroll horizontal de chips + drawer para filtros extra. Desktop: fila de
filtros + orden a la derecha. Sticky opcional. Reutilizable: GLOBAL (`COMP-TOOLBAR`).

### COMP-PRODUCT-GRID — ancha `[fijo · ADN] · TGL-CARD-STYLE`
Grilla a todo el ancho. Mobile: 2 columnas. Tablet: 3. Desktop: **4** (sin sidebar). Cards imagen
grande por default (estética). Reutilizable: GLOBAL. Elementor: Loop Grid full-width. Divi: Shop full.

### COMP-PAGINATION `[fijo]`
Paginado o "cargar más". Reutilizable: GLOBAL.

## 4. Toggles admitidos
| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-SHOP-FILTERS` | topbar | este arquetipo (sin sidebar) |
| `TGL-SHOP-SORT` | on | |
| `TGL-CARD-STYLE` | imagen grande | |

**Fijos:** HEADER, encabezado, TOOLBAR, GRID, PAGINATION, FOOTER.
**Ausencias de ADN:** sidebar de filtros complejos → usar TPL-SHOP-01.

## 5. SEO / semántica
1 `H1` (categoría). Igual que TPL-SHOP-01: canonical/paginado, `alt`, evitar index bloat de filtros.
`header` > `main` > `footer`.
