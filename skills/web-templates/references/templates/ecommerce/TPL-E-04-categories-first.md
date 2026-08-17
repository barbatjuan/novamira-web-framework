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
| ADN | Bloques de categoría GIGANTES y protagonistas, producto por categoría en tabs, y cierra con el mapa completo del catálogo. NO promo protagonista, NO storytelling. |

## 2. Wireframe (top → bottom)

```
COMP-ANNOUNCEMENT        [toggle]
COMP-HEADER (mega menú)  [fijo · ADN]
COMP-HERO (banner medio ~40vh) [fijo] · banner o directo a categorías
COMP-CATEGORY-GRID       [fijo · ADN] · 4–8 bloques de categoría grandes
COMP-PRODUCT-TABS        [fijo · ADN] · productos por categoría en tabs/pestañas
COMP-BENEFITS            [toggle]
COMP-CTA                 [fijo · ADN] · banda de cierre: "¿todavía no encontraste tu sección?"
COMP-CATEGORY-CARD       [fijo · ADN] · dentro de la banda: el índice completo
COMP-NEWSLETTER          [toggle]
COMP-FOOTER              [fijo]
```

La categoría manda: abre con los ocho departamentos grandes y cierra con el índice entero.
Ausencia intencional: sin storytelling largo, sin hero protagonista, y **sin banner de promoción**
—destacar una oferta por encima de las categorías es el ADN de TPL-E-05, y acá invertiría el orden
que da nombre a la plantilla. Sin testimonios: en una tienda multi-rubro la reseña útil es la del
producto, en la PDP, no una fila genérica en la home.

## 3. Secciones

### COMP-ANNOUNCEMENT `[toggle TGL-ANNOUNCEMENT]`
Objetivo: envío/novedad. Reutilizable: GLOBAL.

### COMP-HEADER — mega menú `[fijo · ADN]`
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
GLOBAL (`COMP-CATEGORY-GRID`). Elementor: Loop Grid categorías con imagen. Divi: grid de categorías.

### COMP-PRODUCT-TABS — por categoría `[fijo · ADN] · TGL-CARD-STYLE`
Objetivo: mostrar producto dentro del contexto de categoría (venta). Tabs/pestañas: cada tab una
categoría, muestra 4–8 productos. Mobile: tabs scrolleables horizontal, 2 columnas de producto.
Desktop: tabs arriba, grilla 4. Reutilizable: SECCIÓN (usa `COMP-PRODUCT-CARD`). Elementor: Loop
Grid + tabs (o filtro). Divi: Woo Products + tabs. **Nota:** los tabs interactivos pueden requerir
configuración específica del builder; si no, degradar a carruseles por categoría.

### COMP-BENEFITS `[toggle TGL-BENEFITS]`
Objetivo: confianza operativa transversal a todos los departamentos (envío, retiro en tienda,
cambios, medios de pago). Fila de 3–4 íconos + texto. Mobile: 2×2. Reutilizable: GLOBAL.

### COMP-CTA + COMP-CATEGORY-CARD — el índice completo `[fijo · ADN]`
Objetivo: cerrar dándole salida al que no encontró su sección (navegación + conversión). Banda
final: claim corto ("¿Todavía no encontraste lo que buscabas?") y debajo el **índice completo** en
`COMP-CATEGORY-CARD` compactas — todas las categorías y subcategorías, incluidas las que no
entraron en los 4–8 bloques grandes de arriba, más los cortes transversales que el árbol no cubre
("Regalos", "Vuelta al cole", "Menos de 20 €"). Mobile: claim + grilla 2–3 de cards chicas, o
acordeón por departamento. Desktop: claim + fila de 5–6 columnas de links.
Reutilizable: GLOBAL (`COMP-CTA`, `COMP-CATEGORY-CARD` variante compacta).

**Por qué el cierre es un índice y no un formulario.** Una tienda por departamentos no tiene una
oferta única que cerrar ni una historia que contar; su conversión ES que el visitante entre a la
sección correcta, y su fracaso típico es irse porque el árbol de arriba no incluía su rubro. Por
eso la banda repite la jugada característica del arquetipo en su forma exhaustiva, en vez de pedir
un email. Comparte la forma "claim + bloque" con la banda de TPL-C-01, no el contenido: allá el
payload es un `COMP-LEAD-FORM` que captura un dato, acá es navegación que devuelve un destino.
Vale además como internal linking, que en un catálogo ancho es media estrategia de SEO.

### COMP-NEWSLETTER `[toggle TGL-NEWSLETTER]`
Objetivo: captación. Mejor si es segmentable por departamento ("avisos de la sección que te
interesa"). Reutilizable: GLOBAL.

### COMP-FOOTER `[fijo]`
Objetivo: legal, pagos, atención, medios. **Ojo:** el mapa de categorías ya está en la banda de
cierre, así que el footer NO lo repite — columnas de institucional, ayuda y legal.
Mobile: acordeón. Desktop: 4–5 columnas. Reutilizable: GLOBAL.

## 4. Toggles admitidos

| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-ANNOUNCEMENT` | on | |
| `TGL-HERO-HEIGHT` | medio ~40vh | |
| `TGL-CARD-STYLE` | imagen grande | |
| `TGL-CATEGORIES` | on | **bloqueado**: el índice de cierre es ADN, no se apaga |
| `TGL-BENEFITS` | on | |
| `TGL-NEWSLETTER` | on | |
| `TGL-CTA-STRENGTH` | medio | |
| `TGL-TRUST` | on | |

**Fijos:** COMP-HEADER (mega), COMP-HERO banner, COMP-CATEGORY-GRID, COMP-PRODUCT-TABS,
COMP-CTA + COMP-CATEGORY-CARD (cierre), COMP-FOOTER.
**Ausencias de ADN:** storytelling largo, hero protagonista tipo slider grande, banner de promoción
protagonista, testimonios en home → si el cliente los pide, sugerir TPL-E-01, TPL-E-03 o TPL-E-05.

## 5. SEO / semántica
1 `H1` (hero). `H2` por bloque (Categorías, cada tab, el índice de cierre). Links de categoría con
anchor descriptivo. `header` > `main` > `footer`. El grid de arriba y el índice de cierre son las
dos mitades del internal linking de la home: el primero da peso a los departamentos principales, el
segundo alcanza la cola larga.
