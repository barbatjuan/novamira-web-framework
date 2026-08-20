# CAPA 3 — Toggles (ajuste fino modular)

Preguntas de ajuste **acotadas a lo que cada plantilla permite**. Cada respuesta prende/apaga o
intercambia un bloque modular. El agente NO pregunta lo que la plantilla ya resuelve por ADN.

Los defaults llegan precargados desde el intake de referencias (CAPA 2). El cliente confirma o cambia.

> **Este archivo es la copia, no el original.** La autoridad sobre qué toggles admite una plantilla
> es la tabla "§ 4 Toggles admitidos" de su propio `TPL-*.md`. La columna "Aplica en" de abajo está
> transcrita de esas tablas y se vuelve a transcribir cuando cambian — nunca al revés. Ya se
> desincronizó dos veces (`TGL-HERO-TYPE` listaba dos plantillas donde seis lo declaran;
> `TGL-TESTIMONIALS` listaba TPL-E-04 y TPL-E-05, que no lo declaran), así que ante una
> discrepancia gana el archivo de la plantilla, siempre.

## Catálogo de toggles — ecommerce

| ID | Pregunta al cliente | Opciones | Afecta | Aplica en |
|----|--------------------|----------|--------|-----------|
| `TGL-HERO-TYPE` | ¿Hero con slider o imagen fija? | slider / imagen fija | COMP-HERO | TPL-E-01, TPL-E-03 (fija-only, bloqueado) |
| `TGL-HERO-HEIGHT` | ¿Qué tan alto el hero? | full 60vh / medio 45vh / bajo 35vh | COMP-HERO | TPL-E-01, TPL-E-02 (bajo, tope de ADN), TPL-E-04, TPL-E-05 |
| `TGL-CARD-STYLE` | ¿Cards con imagen grande o compactas? | imagen grande / compacta con datos | COMP-PRODUCT-CARD | TPL-E-01, TPL-E-02, TPL-E-03, TPL-E-04, TPL-E-05 |
| `TGL-CARD-IMG` | ¿Las cards muestran imagen? | sí / no (solo texto+precio) | COMP-PRODUCT-CARD | TPL-E-01, TPL-E-02, TPL-E-05 |
| `TGL-ANNOUNCEMENT` | ¿Barra de anuncio arriba? | sí / no | COMP-ANNOUNCEMENT | TPL-E-01, TPL-E-02, TPL-E-04, TPL-E-05 (fijo en TPL-E-05) |
| `TGL-BENEFITS` | ¿Barra de beneficios (envíos, pagos, garantía)? | sí / no | COMP-BENEFITS | TPL-E-02, TPL-E-04 |
| `TGL-TESTIMONIALS` | ¿Testimonios / social proof? | sí / no | COMP-TESTIMONIAL | TPL-E-01 (fijo en TPL-E-03) |
| `TGL-NEWSLETTER` | ¿Captación de email en la home? | sí / no | COMP-NEWSLETTER | TPL-E-01, TPL-E-03, TPL-E-04, TPL-E-05 |
| `TGL-CATEGORIES` | ¿Índice de categorías? | sí / no | COMP-CATEGORY-CARD | TPL-E-04 (bloqueado en on: es la banda de cierre) |
| `TGL-BOOKING` | ¿Cómo reserva la cita de estilismo/medición? | form/embed / WhatsApp / teléfono | COMP-BOOKING | TPL-E-01 |
| `TGL-FAQ` | ¿Preguntas frecuentes? | sí / no | COMP-FAQ | TPL-E-02 (bloqueado en on: es ADN) |
| `TGL-TRUST` | ¿Trust badges (pagos seguros, medios)? | sí / no | COMP-TRUST-BADGES / footer | TPL-E-01, TPL-E-02, TPL-E-03, TPL-E-04, TPL-E-05 |
| `TGL-CTA-STRENGTH` | ¿Qué tan agresiva la banda de cierre? | suave / medio / fuerte | estilo de la banda de cierre | TPL-E-03, TPL-E-04, TPL-E-05 |

**Cada arquetipo ecommerce cierra con una banda propia y FIJA**, y por eso ninguna se pregunta:
TPL-E-01 con `COMP-BOOKING` (cita de estilismo/medición), TPL-E-02 con `COMP-FAQ` +
`COMP-CONTACT-DIRECT` (dudas operativas y "¿no lo encontraste?"), TPL-E-03 con `COMP-CTA` (la
palabra del taller), TPL-E-04 con `COMP-CTA` + `COMP-CATEGORY-CARD` (el índice completo) y TPL-E-05
con `COMP-CTA` (última llamada + bases). Lo único ajustable es el tono, vía `TGL-CTA-STRENGTH`
donde la plantilla lo admite.

## Catálogo de toggles — corporate

| ID | Pregunta al cliente | Opciones | Afecta | Aplica en |
|----|--------------------|----------|--------|-----------|
| `TGL-HERO-TYPE` | ¿Hero con slider o imagen fija? | slider / imagen fija | COMP-HERO | TPL-C-01, TPL-C-02, TPL-C-03, TPL-C-05 |
| `TGL-HERO-HEIGHT` | ¿Qué tan alto el hero? | full 60vh / medio 45vh / bajo 35vh | COMP-HERO | TPL-C-02, TPL-C-03, TPL-C-04, TPL-C-05 |
| `TGL-CARD-STYLE` | ¿Cards con imagen grande o compactas? | imagen grande / compacta | cards de proyecto | TPL-C-03 |
| `TGL-LEAD-FORM` | ¿Formulario en el hero o solo CTA (form al final)? | form en hero / **solo CTA** (default) | COMP-LEAD-FORM | TPL-C-01 |
| `TGL-LOGOS` | ¿Logos de clientes / credenciales? | sí / no | COMP-LOGOS | TPL-C-01, TPL-C-02, TPL-C-03 |
| `TGL-PROCESS` | ¿Bloque "cómo trabajamos" / pasos? | sí / no | COMP-PROCESS | TPL-C-01 |
| `TGL-CASES` | ¿Casos de éxito / resultados? | sí / no | COMP-CASES | TPL-C-01 |
| `TGL-STATS` | ¿Cifras / números (años, clientes)? | sí / no | COMP-STATS | TPL-C-02 (fijo) |
| `TGL-TEAM` | ¿Sección de equipo? | sí / no | COMP-TEAM | TPL-C-02 |
| `TGL-FEATURES` | ¿Detalle de "qué incluye"? | sí / no | COMP-FEATURES | TPL-C-04 |
| `TGL-PRICING` | ¿Tabla de precios / planes? | sí / no | COMP-PRICING | TPL-C-04 |
| `TGL-FAQ` | ¿Preguntas frecuentes? | sí / no | COMP-FAQ | TPL-C-04 |
| `TGL-BOOKING` | ¿Cómo reserva el cliente? | form/embed / WhatsApp / teléfono | COMP-BOOKING | TPL-C-05 |
| `TGL-MAP` | ¿Mapa + ubicación? | sí / no | COMP-MAP-NAP | TPL-C-05 (fijo) |
| `TGL-TESTIMONIALS` | ¿Testimonios / social proof? | sí / no | COMP-TESTIMONIAL | TPL-C-01, TPL-C-02, TPL-C-05 |
| `TGL-CTA-STRENGTH` | ¿Qué tan agresivo el CTA? | suave / medio / fuerte | estilo de COMP-CTA | TPL-C-01, TPL-C-02, TPL-C-03, TPL-C-04, TPL-C-05 |

## Catálogo de toggles — corporate, familia B (`TPL-C-06..12`)

Los siete arquetipos de vertical (`recommender.md` §3b, familia B) traen toggles propios porque su
unidad de contenido no existe en los otros cinco: una carta no se ajusta con los mismos mandos que
una rejilla de servicios. Varios son **de cantidad**, no de on/off — el bloque es ADN y lo único
negociable es cuántas filas publica.

| ID | Pregunta al cliente | Opciones | Afecta | Aplica en |
|----|--------------------|----------|--------|-----------|
| `TGL-MARQUEE` | ¿Cinta con lo que define la casa? | sí (default) / no | COMP-MARQUEE | TPL-C-06 |
| `TGL-MENU-PRICES` | ¿La carta lleva los precios delante? | sí (default) / no | COMP-MENU-LIST | TPL-C-06 |
| `TGL-GALLERY` | ¿Collage del local / del producto? | sí (default) / no | COMP-GALLERY | TPL-C-06, TPL-C-08 |
| `TGL-FILTER-DEPTH` | ¿Cuántos controles de filtro? | 2 / **4** (default) | COMP-SEARCH-FILTERS | TPL-C-07 |
| `TGL-STOCK-DENSITY` | ¿Cuántas unidades por fila en desktop? | 2 / **3** (default) | COMP-STOCK-GRID | TPL-C-07 |
| `TGL-FINANCE` | ¿Bloque de cuota / financiación? | sí (default) / no | COMP-FINANCE | TPL-C-07, TPL-C-08 |
| `TGL-BADGES` | ¿Sellos de garantía / confianza? | sí (default) / no | COMP-TRUST-BADGES | TPL-C-07, TPL-C-09 |
| `TGL-SPEC-ROWS` | ¿Cuántas filas compara la tabla de versiones? | **6** (default) / menos | COMP-SPEC-TABLE | TPL-C-08 |
| `TGL-PRICE-GROUPS` | ¿En cuántos grupos se ordena la tarifa? | **4** (default) / menos | COMP-PRICE-LIST | TPL-C-09 |
| `TGL-TREATMENT-COUNT` | ¿Cuántas fichas de tratamiento? | **6** (default) / menos | COMP-TREATMENT-CARDS | TPL-C-10 |
| `TGL-CASES-COUNT` | ¿Cuántos casos antes/después? | **1** (default) / más | COMP-BEFORE-AFTER | TPL-C-10 |
| `TGL-INSURANCE` | ¿Aseguradoras y financiación? | sí (default) / no | COMP-INSURANCE | TPL-C-10 |
| `TGL-PHASE-COUNT` | ¿Cuántas fases tiene el plan? | **4** (default) / otras | COMP-PHASE-TIMELINE | TPL-C-11 |
| `TGL-PRICING-PLANS` | ¿Cuántos planes con cuota? | **3** (default) / otros | COMP-PRICING | TPL-C-11 |
| `TGL-URGENT-STATE` | Estado que pinta la barra | **abierto** (default) — el build lo calcula del horario real | COMP-URGENT-BAR | TPL-C-12 |
| `TGL-SYMPTOM-COUNT` | ¿Cuántas filas de triaje? | **4** (default) / otras | COMP-SYMPTOM-TRIAGE | TPL-C-12 |
| `TGL-SEARCH-FIELDS` | ¿Cuántos filtros entran en el héroe? | **4 campos** (default) / 3 campos | COMP-SEARCH-HERO | TPL-C-13 |
| `TGL-GRID-DENSITY` | ¿Cuántas fichas por fila? | 2 col / **3 col** (default) / 4 col | COMP-PROPERTY-GRID | TPL-C-13 |
| `TGL-MAP-MODE` | ¿Cómo entra el plano? | **conmutador** (default) / sección / off | COMP-MAP-SEARCH | TPL-C-13 |

Un toggle de cantidad **no puede llegar a cero**: el bloque que gobierna es ADN del arquetipo. Una
`TPL-C-09` sin tarifa o una `TPL-C-12` sin triaje no son la misma plantilla con menos secciones,
son otro arquetipo — y la respuesta correcta es cambiar de plantilla, no vaciar ésta.

`TGL-SEARCH-FIELDS` tiene tope en cuatro y no es una preferencia estética: el quinto campo de un
buscador es el que hace abandonar, así que la opción de más no existe. `TGL-MAP-MODE` en `off` es
para carteras de una sola zona, donde un plano no discrimina nada y sólo añade un tercero que
consentir; en `conmutador` —el valor por defecto— la rejilla y el plano comparten UNA fuente de
resultados, porque dos consultas separadas es cómo el contador acaba diciendo 34 y el plano
enseñando 31.

`TGL-URGENT-STATE` es el único que el cliente **no** responde: su valor sale del horario real en el
build. Está aquí para que quede escrito de dónde viene, no para preguntarlo.

## Catálogo de toggles — ecommerce, familia B (`TPL-E-06..09`)

Las cuatro verticales de tienda (`recommender.md` §3, familia B) estrenan toggles porque su unidad
de contenido no es un producto de catálogo: es una talla, un lote, un plan o una configuración.
Igual que en corporate, varios son **de cantidad** sobre un bloque que es ADN.

| ID | Pregunta al cliente | Opciones | Afecta | Aplica en |
|----|--------------------|----------|--------|-----------|
| `TGL-MEASURE-UNITS` | ¿Medidas en centímetros o pulgadas? | **cm** (default) / in | COMP-MEASURE-TABLE | TPL-E-06 |
| `TGL-FIT-BODIES` | ¿Sobre cuántos cuerpos se enseña la prenda? | **3** (default) / 2 — nunca 1 | COMP-FIT-GALLERY | TPL-E-06 |
| `TGL-BATCH-DEPTH` | ¿Cuántos lotes se publican a la vez? | **6** (default) / otros | COMP-BATCH-CARD | TPL-E-07 |
| `TGL-ORIGIN-MAP` | ¿Mapa o foto de la explotación de origen? | sí (default) / no | COMP-ORIGIN-MAP | TPL-E-07 |
| `TGL-PLAN-COUNT` | ¿Cuántos planes de suscripción? | **3** (default) / 2 — nunca 1 | COMP-PLAN-PICKER | TPL-E-08 |
| `TGL-CADENCE-OPTIONS` | ¿Cuántas frecuencias de entrega? | **3** (default) / otras | COMP-CADENCE | TPL-E-08 |
| `TGL-CONFIG-STEPS` | ¿Cuántos pasos tiene el configurador? | **4** (default) / hasta 5 | COMP-CONFIGURATOR | TPL-E-09 |
| `TGL-SAMPLE` | ¿Se envían muestras físicas? | sí (default) / no | COMP-SAMPLE-REQUEST | TPL-E-09 |

Dos suelos que no son de estilo. `TGL-FIT-BODIES` no baja de 2 porque una prenda sobre un solo
cuerpo informa de ese cuerpo y de ninguno más, que es el problema que la sección viene a resolver.
`TGL-PLAN-COUNT` no baja de 1 porque un único plan no es una elección: es un precio, y entonces el
arquetipo correcto era otro.

`TGL-SAMPLE` en off es una decisión con consecuencia, no un ajuste: lo fabricado a medida no se
devuelve, así que sin muestra el visitante que duda del color no compra. Apagarlo solo tiene
sentido cuando el material no tiene variante visual.
## Transversales (no dependen del arquetipo)

| ID | Pregunta al cliente | Opciones | Afecta | Aplica en |
|----|--------------------|----------|--------|-----------|
| `TGL-IMAGERY` | ¿Fotografía, ilustración o tratamiento gráfico? | foto / ilustración / gráfico | imagery treatment | todas (TPL-E-01 y TPL-E-03 lo fijan en `foto`) |
| `TGL-MOTION-INTENSITY` | ¿Qué tan marcado el motion? | sutil / default (personalidad) / audaz | hover/motion deltas | todas |

Los pregunta `ux-design-system`, no CAPA 3, porque son decisiones de tratamiento visual y no
cambian el inventario de secciones. Dos plantillas los acotan en su propio doc: TPL-E-01 y TPL-E-03
fijan `TGL-IMAGERY` en `foto` (el lookbook y la historia necesitan campaña real, no ilustración).

## Catálogo de toggles — páginas internas

| ID | Pregunta al cliente | Opciones | Afecta | Aplica en |
|----|--------------------|----------|--------|-----------|
| `TGL-PDP-LAYOUT` | ¿La galería manda o manda la información? | standard / editorial | COMP-GALLERY, aire y miniaturas | TPL-PDP-01 |
| `TGL-PDP-STICKY` | ¿Info de compra pegada al scroll (desktop)? | sí / no | COMP-PRODUCT-INFO | TPL-PDP-01..03 |
| `TGL-RELATED` | ¿Productos relacionados? | sí / no | COMP-PRODUCT-CAROUSEL | TPL-PDP-01 (off por ADN en 02, 03, 04) |
| `TGL-FIT-FINDER` | ¿Traductor de tallas entre marcas? | sí / no | COMP-FIT-FINDER | TPL-E-06, TPL-PDP-02 |
| `TGL-ORIGIN` | ¿Mapa de origen de la pieza? | sí / no | COMP-ORIGIN-MAP | TPL-E-07, TPL-PDP-03 |
| `TGL-ONE-OFF` | ¿Se puede comprar suelto además de suscribirse? | sí / no | COMP-PLAN-PICKER | TPL-E-08, TPL-PDP-04 |
| `TGL-INSTALL` | ¿El plazo incluye instalación? | sí / no | COMP-LEAD-TIME | TPL-E-09, TPL-PDP-05 |
| `TGL-SHOP-FILTERS` | ¿Filtros en sidebar, barra superior o sin filtros? | sidebar / topbar / off | COMP-FILTERS/TOOLBAR | TPL-SHOP-01 |
| `TGL-SHOP-SORT` | ¿Control de orden? | sí / no | COMP-TOOLBAR | TPL-SHOP-01 |
| `TGL-ABOUT-STATS` | ¿Cifras/números en Nosotros? | sí / no | COMP-STATS | TPL-ABOUT-01 (off por ADN en 02) |
| `TGL-ABOUT-TEAM` | ¿Sección de equipo? | sí / no | COMP-TEAM | TPL-ABOUT-01, TPL-ABOUT-03 (off por ADN en 02) |
| `TGL-ABOUT-CREDS` | ¿Certificaciones y homologaciones? | sí / no | COMP-CREDENTIALS | TPL-ABOUT-02 |
| `TGL-BOOKING-TYPE` | ¿Qué se reserva? | mesa / cita / turno / visita | COMP-BOOKING | TPL-ABOUT-03, TPL-CONTACT-02 |
| `TGL-CONTACT-MAP` | ¿Mapa en Contacto? | sí / no | COMP-MAP-NAP | TPL-CONTACT-01 (fijo en 02) |
| `TGL-CONTACT-WHO` | ¿Se enseña con quién va a hablar? | sí / no | COMP-TEAM | TPL-CONTACT-01 |
| `TGL-CONTACT-FORM` | ¿Formulario en Contacto? | sí / no | COMP-CONTACT-FORM | TPL-CONTACT-02 (off por ADN) |
| `TGL-CONTACT-WHATSAPP` | ¿WhatsApp como canal? | sí / no | COMP-CONTACT-DIRECT | TPL-CONTACT-02 |
| `TGL-THANKS-DIRECT` | ¿Teléfono/WhatsApp en la página de gracias? | sí / no | COMP-CONTACT-DIRECT | TPL-THANKS-01 |
| `TGL-CART-VIEW` | ¿Carrito como drawer, página o ambos? | drawer / page / both | side-cart / cart page | TPL-CART-01 |
| `TGL-CART-CROSSSELL` | ¿"Completá tu compra" en el carrito? | sí / no | COMP-PRODUCT-CAROUSEL | TPL-CART-01 |
| `TGL-CART-SHIPBAR` | ¿Barra "te falta €X para envío gratis"? | sí / no | side-cart | TPL-CART-01 |
| `TGL-ORDER-SUMMARY` | ¿Resumen de orden sticky? | sticky / colapsable | resumen | TPL-CART-01, TPL-CHECKOUT-01 |
| `TGL-CHECKOUT-STEPS` | ¿Checkout en 1 paso o 2 pasos? | 1-step / 2-step | form checkout | TPL-CHECKOUT-01 |
| `TGL-CHECKOUT-HEADER` | ¿Header de checkout minimal o completo? | minimal / full | COMP-HEADER | TPL-CHECKOUT-01 |

| `TGL-PROJECT-GALLERY` | ¿Galería del proyecto? | sí (default) / no — apagar con menos de 3 imágenes propias | galería | TPL-PROJECT-01 |
| `TGL-PROJECT-QUOTE` | ¿Testimonio del cliente de ESTE proyecto? | sí / **no** (default) — solo si es real y autorizado | COMP-TESTIMONIAL | TPL-PROJECT-01 |
| `TGL-PROJECT-METRICS` | ¿Métricas del resultado? | sí / **no** (default) — solo con cifras verificables | métricas | TPL-PROJECT-01 |
| `TGL-PROJECT-RELATED` | ¿Proyectos relacionados? | sí (default) / no — apagar con menos de 4 proyectos | COMP-RELATED | TPL-PROJECT-01 |
| `TGL-THANKS-RELATED` | ¿Productos/servicios relacionados en gracias? | sí / **no** (default) — encender en ecommerce | COMP-RELATED | TPL-THANKS-01 |
| `TGL-404-RELATED` | ¿Destacados en la 404? | sí / **no** (default) — encender en ecommerce con catálogo amplio | COMP-RELATED | TPL-404-01 |
| `TGL-LEGAL-TOC` | ¿Índice con ancla a cada H2? | sí (default) / no — apagar en documentos cortos | índice | TPL-LEGAL-01 |
| `TGL-BLOG-FEATURED` | ¿Destacar la entrada más reciente a lo ancho? | sí (default) / no — apagar con menos de 4 entradas | destacado | TPL-BLOG-01 |
| `TGL-BLOG-CATS` | ¿Filtro por categoría? | sí / **no** (default) — encender a partir de 3 categorías con contenido | filtro | TPL-BLOG-01 |
| `TGL-POST-HERO` | ¿Imagen destacada? | sí (default) / no — apagar si no hay imagen real; una de stock resta | imagen destacada | TPL-POST-01 |
| `TGL-POST-TOC` | ¿Índice lateral? | sí / **no** (default) — solo artículos largos y solo desktop | índice lateral | TPL-POST-01 |
| `TGL-POST-AUTHOR` | ¿Ficha de autor? | sí / **no** (default) — encender si el autor aporta autoridad | autor | TPL-POST-01 |
| `TGL-POST-RELATED` | ¿Entradas relacionadas? | sí (default) / no — apagar con menos de 4 entradas | COMP-RELATED | TPL-POST-01 |

`TPL-SERVICE-01` (página de servicio/área) no estrena toggles: reutiliza `TGL-PROCESS`, `TGL-CASES`,
`TGL-FAQ`, `TGL-TESTIMONIALS`, `TGL-PRICING` y `TGL-CTA-STRENGTH`. Ojo con dos defaults propios:
`TGL-FAQ` va **on** (al revés que en `TPL-CONTACT-01`, porque ahí es ADN de SEO) y `TGL-PRICING` va
**off**. Su bloque de cross-link a las áreas hermanas es FIJO y no tiene toggle.

Compartidos entre ecommerce y corporate: `TGL-HERO-TYPE`, `TGL-HERO-HEIGHT`, `TGL-IMAGERY`,
`TGL-MOTION-INTENSITY`, `TGL-CTA-STRENGTH`, `TGL-TESTIMONIALS`, `TGL-CARD-STYLE`, `TGL-FAQ`,
`TGL-BOOKING`.

## Notas

- Un toggle marcado **FIJO** o **bloqueado** en una plantilla no se pregunta (ej: `TGL-TESTIMONIALS`
  en TPL-E-03, `TGL-FAQ` en TPL-E-02, `TGL-CATEGORIES` en TPL-E-04, `TGL-MAP` en TPL-C-05).
- Los toggles nunca rompen el ADN. No hay toggle "poné storytelling en TPL-E-02" — si el cliente
  lo pide, sugerir cambiar de plantilla, no deformar la actual.
- Un toggle que **ninguna** plantilla declara no es un ajuste, es una pregunta que nadie puede
  honrar. `TGL-FOCUS` ("¿foco de la home?") vivió así: reordenaba bloques en TPL-E-01, TPL-E-03 y
  TPL-E-04 según la columna de acá, pero TPL-E-03 nunca lo declaró y en los otros dos el bloque que
  supuestamente movía pasó a ser fijo. Se retiró del catálogo en vez de dejarlo como pregunta
  decorativa.
- **Dos nombres para la misma puerta, sin unificar todavía.** `TGL-TRUST` (ecommerce) y
  `TGL-BADGES` (corporate familia B) gobiernan los dos `COMP-TRUST-BADGES`. Se documentan por
  separado porque así los declaran sus plantillas y esa tabla manda; unificarlos es un cambio en
  siete `TPL-*.md`, no en esta lista. Mismo caso, menos grave, entre `TGL-MAP` y `TGL-CONTACT-MAP`,
  y entre `TGL-STATS`/`TGL-ABOUT-STATS` y `TGL-TEAM`/`TGL-ABOUT-TEAM`.
- **Los cinco `*-RELATED` no son un duplicado**, aunque lo parezcan. `TGL-RELATED`,
  `TGL-PROJECT-RELATED`, `TGL-POST-RELATED`, `TGL-THANKS-RELATED` y `TGL-404-RELATED` apuntan al
  mismo componente pero llevan **defaults opuestos** (on en producto, proyecto y entrada; off en
  gracias y 404), y ese default es justo la información que se perdería al fusionarlos.
- Cada `TPL-*` declara en su doc qué toggles admite y con qué default. **Esa tabla manda.**
