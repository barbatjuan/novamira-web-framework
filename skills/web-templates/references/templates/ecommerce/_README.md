# Ecommerce templates (TPL-E-*)

Cuatro arquetipos activos, todos por **unidad de contenido**: lo que la tienda publica no es una
referencia con precio cerrado que entra sin más en un carrito. `design-system.md` y `toggles.md` son
compartidos (con toggles propios de ecommerce). El recomendador enruta por lo que se publica, no por
perfil de marca (ver `recommender.md` §3).

Precios siempre en **€** y carrito siempre como **icono con badge** (reglas de casa del
orquestador) — salvo donde el arquetipo declara que no hay carrito, que es `TPL-E-09`.

Los cuatro llevan su marca de referencia con bloque `[data-brand]` propio en la galería — ground,
acento, par tipográfico y duotono.

| ID | Nombre | Publica | Convierte con | Ideal para | Marca demo · ancla |
|----|--------|---------|---------------|-----------|--------------------|
| TPL-E-06 | Talla / Prueba | La talla, y si te va a quedar | carrito, tras resolver el ajuste | Moda con ajuste, calzado, lencería, ropa técnica | Corte Nueve · MATTER |
| TPL-E-07 | Lote / Peso | El lote: origen, fecha, €/kg | carrito por peso + ventana de entrega | Carnicería, pescadería, quesos, café, huerta | Bajura · DIRECT |
| TPL-E-08 | Suscripción | El plan: cuota y cadencia | suscripción | Café, pienso, cosmética de reposición, consumibles | Tueste Norte · INSTITUTIONAL |
| TPL-E-09 | A medida | El presupuesto | **sin carrito** — formulario de presupuesto | Cortinas, mobiliario a medida, encimeras, rotulación | Medida Justa · EDITORIAL |

## Disposición de los cinco retirados (`catalog-envato-grade`)

Ninguno respaldaba una demo D1; todos se retiraron sin dejar el arquetipo huérfano en el repo
(`catalog-wrapper-integrity` Requisito 5):

| ID retirado | Nombre | Disposición |
|---|---|---|
| TPL-E-01 | Visual Brand | Retirado, sin reemplazo. Bespoke (`lanes.md`) hasta que un perfil de marca visual vuelva a tener demo |
| TPL-E-02 | Catalog / Product-First | Retirado, sin reemplazo. Bespoke |
| TPL-E-03 | Brand Story | Retirado, sin reemplazo. Su ficha `TPL-PDP-05` (contenido `mtm`) se rescató antes de la retirada y se re-engancha, re-skinada, a `TPL-E-09` en una fase posterior (decisión C1) |
| TPL-E-04 | Categories-First | Retirado, sin reemplazo. Bespoke |
| TPL-E-05 | Promo / Campaign | Retirado, sin reemplazo. Bespoke |

## Diferenciación real entre los cuatro activos

- **E-06** pone el buscador de talla ARRIBA y las medidas en cm por prenda, no una tabla genérica
  al pie: "M" no es una medida. Enseña la prenda sobre tres cuerpos porque sobre uno solo informa
  de ese cuerpo y de ninguno más.
- **E-07** filtra por código postal ANTES del catálogo: fresco que no se puede entregar no es una
  venta, y descubrirlo en el checkout es la conversión más cara del embudo. Precio por kilo, no PVP.
- **E-08** no publica PVP sino cuota, y su bloque central es un control de **cadencia**, no un
  calendario — porque esta suscripción no termina.
- **E-09** es el **único ecommerce sin carrito y sin checkout**: el embudo acaba en presupuesto.

El corte entre los cuatro es mecánico, no de gusto. Vecindades a leer antes de decidir:
`E-08`↔`TPL-C-11` (aquel plan TERMINA, éste no).

## El cierre es diagnóstico

Cada arquetipo cierra con una banda distinta. Si el cliente pide un cierre que no es el de su
plantilla candidata, la señal es que el arquetipo elegido es otro — no que haya que añadirle una
sección. `TPL-E-09` cierra en `COMP-QUOTE-FORM` **porque no vende, presupuesta**.

## Componentes ecommerce reutilizables

**De los cuatro activos**: `COMP-FIT-FINDER`, `COMP-MEASURE-TABLE`, `COMP-FIT-GALLERY`,
`COMP-RETURN-PROMISE`, `COMP-DELIVERY-WINDOW`, `COMP-BATCH-CARD`, `COMP-WEIGHT-NOTE`,
`COMP-ORIGIN-MAP`, `COMP-COLD-CHAIN`, `COMP-PLAN-PICKER`, `COMP-CADENCE`, `COMP-FIRST-BOX`,
`COMP-PAUSE-PROMISE`, `COMP-CONFIGURATOR`, `COMP-SAMPLE-REQUEST`, `COMP-LEAD-TIME`,
`COMP-QUOTE-FORM`.

Compartidos: `COMP-HEADER` / `COMP-HERO` / `COMP-FOOTER` / `COMP-TESTIMONIAL` / `COMP-CTA` /
`COMP-FAQ` / `COMP-GALLERY` / `COMP-CONTACT-DIRECT`.

## Páginas internas que arrastra una tienda activa

E-06/E-07/E-08 arrastran su propia ficha de producto (`TPL-PDP-02`/`03`/`04`) + `TPL-ABOUT-01` +
`TPL-CONTACT-01`, más los tres no negociables (`TPL-LEGAL-01` ×4, `TPL-404-01`, `TPL-THANKS-01` si
hay formulario). `TPL-E-08` suma además `TPL-PDP-01` (Estándar) como ficha "una bolsa" — decisión C1,
fase posterior. `TPL-CART-01` y `TPL-CHECKOUT-01` existen como arquetipo de **layout**, pero quien
los monta es la skill `woocommerce`. Detalle y herencia de defaults: `recommender.md` §6 y
`../pages/_README.md`.

`TPL-E-09` no arrastra Shop ni Carrito: no hay catálogo con precio que listar. Su ficha propia es
`TPL-PDP-05`, rescatada de `TPL-E-03` y re-skinada sobre sus propias 6 fotos (fase posterior).
