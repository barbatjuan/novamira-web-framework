# TPL-E-02 — Catalog / Product-First

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Catalog / Product-First |
| Objetivo | Venta directa: el catálogo es el protagonista, mínimo storytelling |
| Ecommerce ideal | Electrónica, repuestos, librería, ferretería, insumos |
| Ejemplos | Tienda de tecnología, autopartes, papelería, farmacia online |
| Nivel de contenido | Bajo (info de producto, no editorial) |
| Protagonismo del producto | Alto — grillas densas apenas pasa el header |
| Protagonismo de la marca | Bajo |
| ADN | Producto muy arriba, search protagonista, casi sin hero. NO storytelling ni testimonios largos. |

## 2. Wireframe (top → bottom)

```
COMP-ANNOUNCEMENT        [toggle]
COMP-HEADER (search XL)  [fijo · search protagonista]
COMP-HERO (mini ~20vh)   [fijo · ADN] · banner fino o barra de categorías, NO hero grande
COMP-PRODUCT-GRID        [fijo · ADN] · destacados en grilla densa, arriba
COMP-CATEGORY-CARD       [toggle] · categorías compactas
COMP-PRODUCT-CAROUSEL    [fijo] · "Más vendidos" / "Novedades"
COMP-BENEFITS            [toggle] · envío/pago/garantía en barra fina
COMP-NEWSLETTER          [toggle]
COMP-FOOTER              [fijo]
```

El producto aparece antes de scrollear. Ausencia intencional: sin slider grande, sin lookbook,
sin historia de marca, sin testimonios extensos.

## 3. Secciones

### COMP-ANNOUNCEMENT `[toggle TGL-ANNOUNCEMENT]`
Objetivo: envío/financiación (venta). Texto corto + link. Mobile: una línea. Reutilizable: GLOBAL.

### COMP-HEADER — search protagonista `[fijo]`
Objetivo: encontrar producto rápido (navegación + venta). Logo, **search bar ancha visible**,
cart, account, acceso a categorías (mega menú). Mobile: search siempre visible (no escondida en
ícono), hamburguesa para categorías, cart. Desktop: search central ancha, mega menú de categorías.
Sticky. Reutilizable: GLOBAL (`COMP-HEADER`, variante search-first). Elementor: Search widget +
Nav Menu. Divi: módulo Search + Menu.

### COMP-HERO — mini `[fijo · ADN] · TGL-HERO-HEIGHT (bajo)`
Objetivo: no robar espacio al producto (navegación). Banner fino ~20vh o barra horizontal de
categorías/accesos rápidos. Mobile: barra de categorías scrolleable horizontal. Desktop: banner
fino con 1 CTA o accesos directos. H1 acá (ej: "Tienda"). Reutilizable: SECCIÓN. Elementor:
container fino. Divi: row simple.

### COMP-PRODUCT-GRID — destacados en grilla `[fijo · ADN] · TGL-CARD-STYLE, TGL-CARD-IMG`
Objetivo: venta inmediata. Grilla densa de 8–12 `COMP-PRODUCT-CARD` (imagen, nombre, precio,
`--fs-price`, botón/quick-add). Mobile: 2 columnas. Tablet: 3. Desktop: 4. Cards compactas por
default (más densidad). Reutilizable: GLOBAL. Elementor: Loop Grid (Woo). Divi: Woo Products grid.

### COMP-CATEGORY-CARD — compactas `[toggle TGL-CATEGORIES]`
Objetivo: acceso a familias (navegación). Cards chicas con ícono/imagen + nombre. Mobile: grilla
2–3. Desktop: fila de 5–6 compactas. Reutilizable: GLOBAL.

### COMP-PRODUCT-CAROUSEL — más vendidos `[fijo]`
Objetivo: cross-sell / novedades (venta). Carrusel 8+ cards. Mobile: peek + swipe. Desktop: 4–5
visibles. Reutilizable: GLOBAL.

### COMP-BENEFITS — barra fina `[toggle TGL-BENEFITS]`
Objetivo: confianza operativa (envío, cuotas, garantía, devolución). Fila de 3–4 íconos + texto,
compacta. Mobile: 2×2. Reutilizable: GLOBAL.

### COMP-NEWSLETTER `[toggle TGL-NEWSLETTER]`
Objetivo: captación. Compacto, fondo `--c-bg-alt`. Reutilizable: GLOBAL.

### COMP-FOOTER `[fijo]`
Objetivo: navegación amplia (mucha categoría), legal, pagos, medios. Columnas densas de links.
Mobile: acordeón. Desktop: 4–5 columnas. Reutilizable: GLOBAL.

## 4. Toggles admitidos

| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-ANNOUNCEMENT` | on | financiación/envío |
| `TGL-HERO-HEIGHT` | bajo ~20vh | el hero grande NO está disponible |
| `TGL-CARD-STYLE` | compacta | densidad = ADN |
| `TGL-CARD-IMG` | sí | |
| `TGL-CATEGORIES` | on | |
| `TGL-BENEFITS` | on | barra fina |
| `TGL-NEWSLETTER` | on | |
| `TGL-TRUST` | on | |

**Fijos:** COMP-HEADER (search-first), COMP-HERO mini, COMP-PRODUCT-GRID, COMP-PRODUCT-CAROUSEL, COMP-FOOTER.
**Ausencias de ADN:** slider grande, lookbook/editorial, historia de marca, testimonios extensos,
`TGL-FOCUS`, `TGL-TESTIMONIALS` → si el cliente los pide, sugerir TPL-E-01 o TPL-E-03.

## 5. SEO / semántica
1 `H1` (mini-hero). `H2` en cada bloque de producto/categoría. `header` > `main` > `footer`.
Grillas con imágenes lazy salvo las primeras filas (LCP).
