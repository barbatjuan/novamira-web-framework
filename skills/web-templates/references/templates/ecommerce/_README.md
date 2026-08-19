# Ecommerce templates (TPL-E-*)

Nueve arquetipos de tienda en **dos familias**. Misma mecánica en las dos: secciones FIJAS (ADN) /
TOGGLE, `design-system.md` y `toggles.md` compartidos (con toggles propios de ecommerce). El
recomendador **pregunta primero por la familia B** (ver `recommender.md` §3).

Precios siempre en **€** y carrito siempre como **icono con badge** (reglas de casa del
orquestador) — salvo donde el arquetipo declara que no hay carrito, que es `TPL-E-09`.

## Familia A — por PERFIL (`TPL-E-01..05`)

Para tiendas donde existe una referencia con precio cerrado que entra en un carrito.

| ID | Nombre | Ideal para | Hero | Foco |
|----|--------|-----------|------|------|
| TPL-E-01 | Visual Brand | Moda, decoración, lifestyle, accesorios | Slider grande | Descubrimiento + branding |
| TPL-E-02 | Catalog / Product-First | Electrónica, repuestos, ferretería, insumos | Mini, casi sin hero | Venta directa por búsqueda |
| TPL-E-03 | Brand Story | Cosmética artesanal, autor, alimentos premium | Imagen fija chica | Confianza antes que catálogo |
| TPL-E-04 | Categories-First | Hogar general, bazar, pet shop, multi-rubro | Banner | Navegación por categoría |
| TPL-E-05 | Promo / Campaign | Outlet, temporada, Black Friday, liquidación | Promo + countdown | Urgencia y descuento |

## Familia B — por UNIDAD DE CONTENIDO (`TPL-E-06..09`)

Existen porque ese supuesto se rompe: lo que se publica no es una referencia con precio cerrado.
**Cada una cambia además el mecanismo de conversión**, que es lo que de verdad las separa.

| ID | Nombre | Publica | Convierte con | Ideal para |
|----|--------|---------|---------------|-----------|
| TPL-E-06 | Talla / Prueba | La talla, y si te va a quedar | carrito, tras resolver el ajuste | Moda con ajuste, calzado, lencería, ropa técnica |
| TPL-E-07 | Lote / Peso | El lote: origen, fecha, €/kg | carrito por peso + ventana de entrega | Carnicería, pescadería, quesos, café, huerta |
| TPL-E-08 | Suscripción | El plan: cuota y cadencia | suscripción | Café, pienso, cosmética de reposición, consumibles |
| TPL-E-09 | A medida | El presupuesto | **sin carrito** — formulario de presupuesto | Cortinas, mobiliario a medida, encimeras, rotulación |

## Diferenciación real (no es el mismo template con otro color)

**Dentro de la familia A**
- **E-01** abre y cierra con la foto: el lookbook es el catálogo. Sin urgencia, sin densidad.
- **E-02** pone la rejilla apenas pasa el header y el buscador manda. Sus ausencias de ADN son
  slider, lookbook, historia de marca y testimonios extensos: nada debe empujar el producto abajo.
- **E-03** tiene testimonios e historia como **fijos**, no como toggle — si se apagan deja de ser
  este arquetipo. Catálogo curado, nunca denso.
- **E-04** es el único donde la CATEGORÍA, no el producto, es el bloque gigante, con producto en tabs.
- **E-05** es el único con **countdown**, y su barra de anuncio es ADN, no toggle.

**Dentro de la familia B**
- **E-06** pone el buscador de talla ARRIBA y las medidas en cm por prenda, no una tabla genérica
  al pie: "M" no es una medida. Enseña la prenda sobre tres cuerpos porque sobre uno solo informa
  de ese cuerpo y de ninguno más.
- **E-07** filtra por código postal ANTES del catálogo: fresco que no se puede entregar no es una
  venta, y descubrirlo en el checkout es la conversión más cara del embudo. Precio por kilo, no PVP.
- **E-08** no publica PVP sino cuota, y su bloque central es un control de **cadencia**, no un
  calendario — porque esta suscripción no termina.
- **E-09** es el **único ecommerce sin carrito y sin checkout**: el embudo acaba en presupuesto.

**Por qué la familia B no colapsa en la A.** El corte es mecánico, no de gusto: si el visitante no
puede añadir al carrito una referencia con precio cerrado, ninguna plantilla de la familia A
funciona. Vecindades a leer antes de decidir: `E-06`↔`E-01` · `E-07`↔`E-02`/`E-03` · `E-08`↔`E-02`
y ↔`TPL-C-11` (aquel plan TERMINA, éste no) · `E-09`↔`E-02` y ↔`TPL-C-01` (si es un servicio y no
un objeto fabricado).

## El cierre es diagnóstico

Cada arquetipo cierra con una banda distinta. Si el cliente pide un cierre que no es el de su
plantilla candidata (un formulario de contacto en una tienda de catálogo, un cupón en una marca de
autor), la señal es que el arquetipo elegido es otro — no que haya que añadirle una sección.
Ninguno de la familia A cierra con `COMP-LEAD-FORM`: eso es ADN de `TPL-C-01`, y en una tienda pide
un dato sin devolver nada. La excepción es `TPL-E-09`, que cierra en `COMP-QUOTE-FORM` **porque no
vende, presupuesta**.

## Componentes ecommerce reutilizables

**Familia A**: `COMP-PRODUCT-CARD`, `COMP-PRODUCT-GRID`, `COMP-PRODUCT-CAROUSEL`,
`COMP-PRODUCT-TABS`, `COMP-CATEGORY-CARD`, `COMP-CATEGORY-GRID`, `COMP-ANNOUNCEMENT`,
`COMP-PROMO-BANNER`, `COMP-BENEFITS`, `COMP-TRUST-BADGES`, `COMP-NEWSLETTER`, `COMP-ORDER-SUMMARY`,
`COMP-RELATED`, `COMP-VALUES`, `COMP-ABOUT`, `COMP-BOOKING`.

**Familia B** (estrenados por las verticales): `COMP-FIT-FINDER`, `COMP-MEASURE-TABLE`,
`COMP-FIT-GALLERY`, `COMP-RETURN-PROMISE`, `COMP-DELIVERY-WINDOW`, `COMP-BATCH-CARD`,
`COMP-WEIGHT-NOTE`, `COMP-ORIGIN-MAP`, `COMP-COLD-CHAIN`, `COMP-PLAN-PICKER`, `COMP-CADENCE`,
`COMP-FIRST-BOX`, `COMP-PAUSE-PROMISE`, `COMP-CONFIGURATOR`, `COMP-SAMPLE-REQUEST`,
`COMP-LEAD-TIME`, `COMP-QUOTE-FORM`.

Compartidos: `COMP-HEADER` / `COMP-HERO` / `COMP-FOOTER` / `COMP-TESTIMONIAL` / `COMP-CTA` /
`COMP-FAQ` / `COMP-GALLERY` / `COMP-CONTACT-DIRECT`.

## Páginas internas que arrastra una tienda

Home + `TPL-SHOP-01/02` + `TPL-PDP-01/02` + `TPL-ABOUT-01` + `TPL-CONTACT-01`, más los tres no
negociables (`TPL-LEGAL-01` ×4, `TPL-404-01`, `TPL-THANKS-01` si hay formulario). `TPL-CART-01` y
`TPL-CHECKOUT-01` existen como arquetipo de **layout**, pero quien los monta es la skill
`woocommerce`. Detalle y herencia de defaults: `recommender.md` §6 y `../pages/_README.md`.

`TPL-E-09` no arrastra Shop ni PDP ni Carrito: no hay catálogo con precio que listar. Su página
interna propia es la de trabajo entregado, que se resuelve con `TPL-PROJECT-01`.

## Estado del catálogo

Los cinco de la familia A son arquetipos **genéricos**: aportan arquitectura, y el look lo resuelve
`ux-design-system` con el ancla. Las cuatro de la familia B aportan arquitectura y mecanismo de
conversión propios, pero **todavía no tienen marca de referencia con su bloque `[data-brand]`**
como sí tienen las siete verticales corporate — eso necesita reportaje fotográfico propio por marca
(`RT_GALLERY_ONE_SHOOT` prohíbe reutilizar el de otra). Es la ampliación pendiente, y sin ella
estas cuatro no aparecen como tira en la galería.
