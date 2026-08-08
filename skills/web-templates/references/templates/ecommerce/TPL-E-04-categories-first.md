# TPL-E-04 — Categories-First

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Categories-First |
| Objetivo | Navegación: el usuario elige primero una categoría; el catálogo es amplio y variado |
| Ecommerce ideal | Hogar general, tienda por departamentos, mascotas, jugueterías, bazar |
| Ejemplos | Tienda multi-rubro, pet shop, home & deco amplio, librería general |
| Nivel de contenido | Bajo–medio |
| Protagonismo del producto | Medio — secundario a la categoría |
| Protagonismo de la marca | Bajo |
| ADN | Bloques de categoría GIGANTES y protagonistas. El producto viene después, por categoría. |

## 2. Wireframe (top → bottom)

```
COMP-ANNOUNCEMENT        [toggle]
COMP-HEADER (mega menú)  [fijo]
COMP-HERO (banner medio ~40vh) [fijo] · banner o directo a categorías
COMP-CATEGORY-GRID       [fijo · ADN] · bloques de categoría grandes, protagonistas
COMP-PRODUCT-TABS        [fijo] · productos por categoría en tabs/pestañas
COMP-PROMO-BANNER        [toggle] · banner secundario intermedio
COMP-BENEFITS            [toggle]
COMP-TESTIMONIAL         [toggle]
COMP-NEWSLETTER          [toggle]
COMP-FOOTER              [fijo]
```

La categoría manda. Ausencia intencional: sin storytelling largo, sin hero protagonista.

## 3. Secciones

### COMP-ANNOUNCEMENT `[toggle TGL-ANNOUNCEMENT]`
Objetivo: envío/novedad. Reutilizable: GLOBAL.

### COMP-HEADER — mega menú `[fijo]`
Objetivo: navegar mucho catálogo variado (navegación). Logo, **mega menú por categorías**, search,
cart, account. Mobile: hamburguesa con árbol de categorías anidado, cart, search en overlay.
Desktop: mega menú desplegable con columnas de subcategorías. Sticky. Reutilizable: GLOBAL
(`COMP-HEADER`, variante mega). Elementor: Nav Menu Pro (mega). Divi: menú con submenús.

### COMP-HERO — banner medio `[fijo] · TGL-HERO-HEIGHT`
Objetivo: introducir sin robar espacio a las categorías. Banner ~40vh con 1 mensaje + CTA, o
directamente el arranque del grid de categorías. Mobile: 30vh o se saltea al grid. Desktop: 40vh.
H1 acá. Reutilizable: SECCIÓN.

### COMP-CATEGORY-GRID — bloques grandes `[fijo · ADN]`
Objetivo: que el usuario elija categoría (navegación + descubrimiento). 4–8 bloques grandes con
imagen de categoría + nombre + (opcional) nº de productos. Mobile: 1–2 columnas, cards altas.
Tablet: 2–3. Desktop: 3–4 grandes, hover con overlay. Bloque completo clickeable. Reutilizable:
GLOBAL (`COMP-CATEGORY-CARD`, variante grande). Elementor: Loop Grid categorías con imagen.
Divi: grid de categorías.

### COMP-PRODUCT-TABS — por categoría `[fijo] · TGL-CARD-STYLE`
Objetivo: mostrar producto dentro del contexto de categoría (venta). Tabs/pestañas: cada tab una
categoría, muestra 4–8 productos. Mobile: tabs scrolleables horizontal, 2 columnas de producto.
Desktop: tabs arriba, grilla 4. Reutilizable: SECCIÓN (usa `COMP-PRODUCT-CARD`). Elementor: Loop
Grid + tabs (o filtro). Divi: Woo Products + tabs. **Nota:** los tabs interactivos pueden requerir
configuración específica del builder; si no, degradar a carruseles por categoría.

### COMP-PROMO-BANNER `[toggle TGL-CATEGORIES/promo]`
Objetivo: destacar una categoría/oferta (venta). Banner ancho imagen + texto + CTA. Mobile:
full-width apilado. Desktop: banda. Reutilizable: GLOBAL.

### COMP-BENEFITS `[toggle TGL-BENEFITS]` · COMP-TESTIMONIAL `[toggle TGL-TESTIMONIALS]` · COMP-NEWSLETTER `[toggle TGL-NEWSLETTER]`
Estándar, como en las demás. Reutilizables: GLOBAL.

### COMP-FOOTER `[fijo]`
Objetivo: navegación por todas las categorías (mapa del sitio), legal, pagos. Columnas densas por
categoría. Mobile: acordeón. Desktop: 4–5 columnas. Reutilizable: GLOBAL.

## 4. Toggles admitidos

| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-ANNOUNCEMENT` | on | |
| `TGL-HERO-HEIGHT` | medio ~40vh | |
| `TGL-CARD-STYLE` | imagen grande | |
| `TGL-FOCUS` | venta directa | |
| `TGL-BENEFITS` | on | |
| `TGL-TESTIMONIALS` | off | opcional |
| `TGL-NEWSLETTER` | on | |
| `TGL-STYLE` | comercial | |
| `TGL-TRUST` | on | |

**Fijos:** COMP-HEADER (mega), COMP-HERO banner, COMP-CATEGORY-GRID, COMP-PRODUCT-TABS, COMP-FOOTER.
**Ausencias de ADN:** storytelling largo, hero protagonista tipo slider grande → si el cliente los
pide, sugerir TPL-E-01 o TPL-E-03.

## 5. SEO / semántica
1 `H1` (hero). `H2` por bloque (Categorías, cada tab). Links de categoría con anchor descriptivo.
`header` > `main` > `footer`. Grid de categorías = buen internal linking.
