# TPL-SHOP-01 — Shop / Catálogo (Sidebar)

## 1. Identidad
Archive de productos con **filtros en sidebar**. Ideal para catálogos amplios con muchos atributos
(talle, color, precio, marca) — combina con home TPL-E-02 / TPL-E-04. Precios en **€**.

## 2. Wireframe (top → bottom)
```
COMP-HEADER [fijo]
COMP-BREADCRUMB [fijo]
COMP-PAGE-HEAD [fijo] · título de la categoría + nº de resultados + banner opcional
COMP-FILTERS [fijo · ADN · TGL-SHOP-FILTERS] · sidebar, barra superior o ninguno
COMP-TOOLBAR [fijo] · orden, nº por página y, en mobile, el botón que abre los filtros
COMP-PRODUCT-GRID [fijo · ADN] · 2 col mobile · 3 tablet · 3 con sidebar / 4 sin él
COMP-PAGINATION [fijo]
COMP-FOOTER [fijo]
```

## 3. Secciones
### COMP-BREADCRUMB `[fijo]` · Encabezado `[fijo]`
Breadcrumb + H1 de la categoría + nº de resultados. Banner de categoría opcional. Reutilizable: GLOBAL.

### COMP-FILTERS — sidebar `[fijo · ADN] · TGL-SHOP-FILTERS`
Objetivo: filtrar el catálogo (navegación). Filtros por categoría, precio (€), atributos (talle,
color), stock. Desktop: columna izquierda fija (~250px). **Mobile: se convierte en drawer** que abre
desde un botón "Filtrar" en la toolbar. Reutilizable: GLOBAL (`COMP-FILTERS`). Elementor: widgets de
filtro Woo (o plugin de filtros — indicar si es específico). Divi: módulos de filtro/plugin.

### COMP-TOOLBAR `[fijo] · TGL-SHOP-SORT`
Objetivo: ordenar y controlar (navegación). Orden (precio, novedad, nombre), nº por página, toggle de
vista, y en mobile el botón "Filtrar". Mobile: barra compacta sticky. Reutilizable: GLOBAL.

### COMP-PRODUCT-GRID `[fijo · ADN] · TGL-CARD-STYLE, TGL-CARD-IMG`
Objetivo: mostrar el catálogo (venta). Grilla de `COMP-PRODUCT-CARD` (imagen, nombre, precio €).
Mobile: 2 columnas. Tablet: 3. Desktop: 3 (con sidebar). Reutilizable: GLOBAL. Elementor: Loop Grid /
Archive Products. Divi: Woo archive / Shop module.

### COMP-PAGINATION `[fijo]`
Objetivo: recorrer resultados (navegación). Paginado o "cargar más". Mobile: botones grandes.
Reutilizable: GLOBAL. **Nota:** infinite scroll suele ser específico del builder/plugin — indicarlo.

## 4. Toggles admitidos
| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-SHOP-FILTERS` | sidebar | este arquetipo; drawer en mobile |
| `TGL-SHOP-SORT` | on | |
| `TGL-CARD-STYLE` | compacta | densidad de catálogo |
| `TGL-CARD-IMG` | sí | |

**Fijos:** HEADER, BREADCRUMB, encabezado, FILTERS, TOOLBAR, GRID, PAGINATION, FOOTER.

## 5. SEO / semántica
1 `H1` (categoría). Paginado con `rel` correcto / canonical. Schema `ItemList` opcional. `alt` en
cards. `header` > `main` > `footer`. Evitar index bloat de filtros (ver `wordpress-seo`).
