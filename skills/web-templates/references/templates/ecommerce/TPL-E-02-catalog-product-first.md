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
| ADN | Producto muy arriba, search protagonista, casi sin hero, y cierra resolviendo lo que la búsqueda no resolvió. NO storytelling ni testimonios largos. |

## 2. Wireframe (top → bottom)

```
COMP-ANNOUNCEMENT        [toggle]
COMP-HEADER (search XL)  [fijo · ADN · search protagonista]
COMP-HERO (mini ~20vh)   [fijo · ADN] · banner fino + barra de categorías, NO hero grande
COMP-PRODUCT-GRID        [fijo · ADN] · destacados en grilla densa, arriba
COMP-PRODUCT-CAROUSEL    [fijo] · "Más vendidos" / "Novedades"
COMP-BENEFITS            [toggle] · envío/pago/garantía en barra fina
COMP-FAQ                 [fijo · ADN] · plazos, cortes, factura, devolución
COMP-CONTACT-DIRECT      [fijo · ADN] · banda de cierre: "¿no lo encontraste?"
COMP-FOOTER              [fijo]
```

El producto aparece antes de scrollear. Ausencia intencional: sin slider grande, sin lookbook,
sin historia de marca, sin testimonios extensos, y **sin bloque de categorías propio**: el mega
menú del header y la barra del mini-hero ya son el acceso a familias, repetirlo abajo es mobiliario
duplicado. **Sin banda de newsletter**: ver la nota en `COMP-CONTACT-DIRECT`.

## 3. Secciones

### COMP-ANNOUNCEMENT `[toggle TGL-ANNOUNCEMENT]`
Objetivo: envío/financiación (venta). Texto corto + link. Mobile: una línea. Reutilizable: GLOBAL.

### COMP-HEADER — search protagonista `[fijo · ADN]`
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

**Este bloque ES el acceso a categorías de la plantilla.** Junto con el mega menú del header cubre
la navegación por familias, y por eso no hay un `COMP-CATEGORY-CARD` más abajo: la versión anterior
lo tenía y era el mismo control dibujado dos veces.

### COMP-PRODUCT-GRID — destacados en grilla `[fijo · ADN] · TGL-CARD-STYLE, TGL-CARD-IMG`
Objetivo: venta inmediata. Grilla densa de 8–12 `COMP-PRODUCT-CARD` (imagen, nombre, precio,
`--fs-price`, botón/quick-add). Mobile: 2 columnas. Tablet: 3. Desktop: 4. Cards compactas por
default (más densidad). Reutilizable: GLOBAL. Elementor: Loop Grid (Woo). Divi: Woo Products grid.

### COMP-PRODUCT-CAROUSEL — más vendidos `[fijo]`
Objetivo: cross-sell / novedades (venta). Carrusel 8+ cards. Mobile: peek + swipe. Desktop: 4–5
visibles. Reutilizable: GLOBAL.

### COMP-BENEFITS — barra fina `[toggle TGL-BENEFITS]`
Objetivo: confianza operativa (envío, cuotas, garantía, devolución). Fila de 3–4 íconos + texto,
compacta. Mobile: 2×2. Reutilizable: GLOBAL.

### COMP-FAQ — dudas operativas `[fijo · ADN] · TGL-FAQ bloqueado en on`
Objetivo: resolver las objeciones que frenan un pedido técnico (venta + confianza). Acordeón de
5–8 preguntas reales del rubro: plazos de entrega, corte/medida a pedido, compatibilidad, factura A,
garantía del fabricante, devolución de piezas sin usar, retiro en depósito. Mobile/Desktop:
acordeón full-width, `<details>/<summary>`, divisor `--c-border` por fila, sin JS.
Reutilizable: GLOBAL (`COMP-FAQ`). Elementor: Accordion/Toggle. Divi: Accordion.

### COMP-CONTACT-DIRECT — "¿no lo encontraste?" `[fijo · ADN]`
Objetivo: recuperar la búsqueda fallida (conversión). Banda final: claim corto + teléfono
clickeable + WhatsApp + email de mostrador, con el horario de atención al lado. Pide consultar
stock, plazo y presupuesto por pieza. Mobile: canales apilados, tap-to-call primero. Desktop:
claim | fila de canales. Reutilizable: GLOBAL (`COMP-CONTACT-DIRECT`).

**Por qué este cierre y no un formulario ni un newsletter.** El ADN de arriba es *encontrar*: search
ancha, grilla densa, mega menú. El cierre honesto de una plantilla así es lo que pasa cuando
encontrar falló — el comprador de repuestos e insumos no quiere contar su historia, quiere saber si
hay stock y cuándo llega. Un `COMP-LEAD-FORM` corporativo pide datos y responde en 48 h, que es la
velocidad equivocada para este rubro. Y la banda de newsletter compite por el mismo lugar sin nada
que ofrecer: con "Nivel de contenido: Bajo" y "Protagonismo de la marca: Bajo" declarados arriba,
esta tienda no tiene qué mandar por mail que el catálogo no diga ya. El email se capta en el footer
y en el flujo de pedido, no como banda de home.

### COMP-FOOTER `[fijo]`
Objetivo: navegación amplia (mucha categoría), legal, pagos, medios, captación de email.
Columnas densas de links. Mobile: acordeón. Desktop: 4–5 columnas. Reutilizable: GLOBAL.

## 4. Toggles admitidos

| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-ANNOUNCEMENT` | on | financiación/envío |
| `TGL-HERO-HEIGHT` | bajo ~20vh | el hero grande NO está disponible |
| `TGL-CARD-STYLE` | compacta | densidad = ADN |
| `TGL-CARD-IMG` | sí | |
| `TGL-BENEFITS` | on | barra fina |
| `TGL-FAQ` | on | **bloqueado** (ADN): las dudas operativas no se preguntan |
| `TGL-TRUST` | on | |

**Fijos:** COMP-HEADER (search-first), COMP-HERO mini, COMP-PRODUCT-GRID, COMP-PRODUCT-CAROUSEL,
COMP-FAQ, COMP-CONTACT-DIRECT (cierre), COMP-FOOTER.
**Ausencias de ADN:** slider grande, lookbook/editorial, historia de marca, testimonios extensos,
bloque de categorías propio, banda de newsletter, `TGL-TESTIMONIALS` → si el cliente los pide,
sugerir TPL-E-01, TPL-E-03 o TPL-E-04.

## 5. SEO / semántica
1 `H1` (mini-hero). `H2` en cada bloque de producto y en FAQ/contacto. `header` > `main` > `footer`.
Grillas con imágenes lazy salvo las primeras filas (LCP). El acordeón admite schema `FAQPage`
(ver `wordpress-seo`) y los datos de contacto, `LocalBusiness` si hay mostrador físico.
