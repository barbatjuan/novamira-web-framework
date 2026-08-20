# TPL-BLOG-01 — Listado del blog

## 1. Identidad
El índice de artículos. Sirve para **ecommerce y corporate**. Hereda tokens de la home. Precios (si
aparecen) en **€**.

Es el arquetipo que más se pide y menos se sostiene: un blog sin quien escriba se queda con tres
entradas de hace dos años, y eso resta confianza en vez de sumarla. Antes de encenderlo, pregunta
quién publica y cada cuánto. Si no hay respuesta, no lo montes.

## 2. Wireframe (top → bottom)
```
COMP-HEADER [fijo]
COMP-PAGE-HEAD [fijo] · H1 + una línea de para quién es
COMP-FEATURED-POST [toggle TGL-BLOG-FEATURED] · la entrada más reciente, a lo ancho
COMP-POST-GRID [fijo · ADN] · imagen, título, fecha, extracto, tiempo de lectura
COMP-FILTERS (por categoría) [toggle TGL-BLOG-CATS]
COMP-PAGINATION [fijo] · nunca scroll infinito sin enlaces reales
COMP-NEWSLETTER [toggle TGL-NEWSLETTER]
COMP-FOOTER [fijo]
```

## 3. Secciones
### Listado `[fijo · ADN]`
Objetivo: que se vea de qué va esto sin entrar. Tarjetas con imagen, título (enlace), fecha,
extracto de 2 líneas y tiempo de lectura. Mobile: una columna. Desktop: 2-3.
**La fecha va visible**: ocultarla para disimular que no se publica desde hace un año engaña al
lector y no al buscador. Reutilizable: SECCIÓN.

### Paginación `[fijo]`
Objetivo: que las entradas viejas sean alcanzables por un crawler. Enlaces `?paged=` reales; el
scroll infinito puede acompañar, nunca sustituir. Reutilizable: SECCIÓN.

### Destacado / Categorías / Newsletter `[toggles]`
Reutilizables: GLOBAL (`COMP-NEWSLETTER`), SECCIÓN los otros dos.

## 4. Toggles admitidos
| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-BLOG-FEATURED` | on | apagar con menos de 4 entradas |
| `TGL-BLOG-CATS` | off | encender a partir de 3 categorías con contenido |
| `TGL-NEWSLETTER` | off | solo con envío real detrás |

**Fijos:** HEADER, encabezado, listado, paginación, FOOTER.

## 5. SEO / semántica
1 `H1` (el nombre del blog, no el de la última entrada). Cada tarjeta es un `article` con su título
en `H2`. Schema `Blog` + `ItemList`. Paginación con `rel=prev/next` o enlaces rastreables. Índice de
categorías indexable solo si tiene contenido propio; si no, `noindex` para no llenar el índice de
páginas casi vacías. `header` > `main` > `footer`.
