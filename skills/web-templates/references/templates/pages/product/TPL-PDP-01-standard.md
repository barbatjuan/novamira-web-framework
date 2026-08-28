# TPL-PDP-01 — Ficha estándar

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Ficha estándar |
| Objetivo | Que el cliente elija una variante y la meta en el carrito |
| Ecommerce ideal | Catálogos donde el producto es una referencia definida y siempre igual a sí misma |
| Ejemplos | Accesorios, papelería, herramienta, electrónica, menaje, recambios |
| Home que la acompaña | `TPL-E-02`, `TPL-E-04`, `TPL-E-05`, y `TPL-E-01`/`TPL-E-03` con `TGL-PDP-LAYOUT: editorial` |
| ADN | Galería + bloque de compra al lado + el detalle plegado debajo + cross-sell. La ficha que funciona cuando lo único que queda por decidir es cuál y cuántos. |

**Es la ficha por defecto y por eso es la que más hay que justificar.** Sirve cuando el producto
está definido antes de entrar: existe una referencia, tiene un precio, y la única duda que le queda
al visitante es qué variante y qué cantidad. En cuanto la duda es otra —si le va a caber, cuánto
pesa exactamente, a qué se compromete, o si se puede fabricar lo que pide— esta ficha no responde,
y hay un arquetipo hermano que sí: `TPL-PDP-02` a `TPL-PDP-05`.

**Estándar y editorial son la misma arquitectura.** Hubo un `TPL-PDP-02 Editorial` con galería a
sangre y más aire, y medido contra ésta compartía SIETE de sus ocho secciones: no era otra
arquitectura, era ésta con la foto más grande. El tamaño de la foto, el aire y el registro visual
los mueven las ocho anclas de `ux-design-system/references/style-catalog/` y el toggle `TGL-PDP-LAYOUT`, que es donde
viven las decisiones de aspecto. Un arquetipo es un ESQUELETO; si dos esqueletos coinciden, hay uno.

## 2. Wireframe (top → bottom)
```
COMP-HEADER [fijo]
COMP-BREADCRUMB [fijo]
COMP-GALLERY [fijo · ADN] · principal + miniaturas; a sangre con TGL-PDP-LAYOUT: editorial
COMP-PRODUCT-INFO [fijo · ADN] · título, precio €, variantes, cantidad, add-to-cart, envío
COMP-ACCORDION [fijo] · descripción / envíos / devoluciones / cuidados
COMP-TRUST-BADGES [toggle TGL-TRUST]
COMP-PRODUCT-CAROUSEL (relacionados) [toggle TGL-RELATED]
COMP-FOOTER [fijo]
```

## 3. Secciones
### COMP-BREADCRUMB `[fijo]`
Ubicación + navegación + SEO. Inicio / Categoría / Producto. Mobile: truncado o sólo "volver a
Categoría". Reutilizable: GLOBAL. Elementor: Breadcrumbs. Divi: módulo breadcrumb.

### COMP-GALLERY `[fijo · ADN]`
Imagen principal + miniaturas. Mobile: carrusel con dots, a todo el ancho, arriba. Desktop:
principal grande + miniaturas, zoom en hover. Con `TGL-PDP-LAYOUT: editorial` la principal sube a
columna ancha o a sangre y las miniaturas se apilan. Reutilizable: GLOBAL.

### COMP-PRODUCT-INFO `[fijo · ADN] · TGL-PDP-STICKY`
Vender el producto. Título (H1), precio `--fs-price` en €, precio anterior tachado si hay oferta,
selector de variantes, cantidad, **add-to-cart** (primary), envío y stock. Mobile: debajo de la
galería, con **barra de add-to-cart pegada** al hacer scroll. Desktop: columna derecha, sticky
opcional. Reutilizable: GLOBAL. Elementor/Divi: módulos Woo equivalentes.

### COMP-ACCORDION `[fijo]`
Detalle sin saturar. Descripción, envíos, devoluciones, cuidados. **La primera fila abierta y
ninguna otra** — regla de casa, ver `layout-patterns.md` § "Disclosure lists". Reutilizable: GLOBAL.

### COMP-TRUST-BADGES `[toggle TGL-TRUST]` · COMP-PRODUCT-CAROUSEL `[toggle TGL-RELATED]`
Fricción y cross-sell. Pago seguro, envíos, cambios; "también te puede gustar", 6–8 cards. Mobile:
peek + swipe. Desktop: 4. Reutilizables: GLOBAL.

## 4. Toggles admitidos
| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-PDP-LAYOUT` | standard | `editorial` = galería a sangre, más aire, miniaturas apiladas |
| `TGL-PDP-STICKY` | on (desktop) | info de compra pegada al scroll |
| `TGL-RELATED` | on | |
| `TGL-TRUST` | on | |
| `TGL-CARD-STYLE` | según la home | afecta a los relacionados |

**Fijos:** HEADER, BREADCRUMB, GALLERY, PRODUCT-INFO, ACCORDION, FOOTER.
**Ausencias de ADN:** tabla de medidas, lote y peso variable, planes, configurador — cada una tiene
su arquetipo, y meterlas aquí como pestaña es exactamente cómo se entierra la decisión de compra.

## 5. SEO / semántica
1 `H1` (nombre del producto). `H2` en Descripción y Relacionados. Schema `Product` + `Offer`
(`priceCurrency: EUR`, `price`, `availability`). `alt` en toda la galería. `header` > `main` >
`footer`. Ver `wordpress-seo`.
