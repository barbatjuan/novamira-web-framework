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
| `TGL-PDP-LAYOUT` | ¿Ficha de producto estándar o editorial? | standard / editorial | arquetipo PDP | TPL-PDP-01/02 |
| `TGL-PDP-STICKY` | ¿Info de compra pegada al scroll (desktop)? | sí / no | COMP-PRODUCT-INFO | TPL-PDP-01/02 |
| `TGL-RELATED` | ¿Productos relacionados? | sí / no | COMP-PRODUCT-CAROUSEL | TPL-PDP-01/02 |
| `TGL-SHOP-FILTERS` | ¿Filtros en sidebar, barra superior o sin filtros? | sidebar / topbar / off | COMP-FILTERS/TOOLBAR | TPL-SHOP-01/02 |
| `TGL-SHOP-SORT` | ¿Control de orden? | sí / no | COMP-TOOLBAR | TPL-SHOP-01/02 |
| `TGL-ABOUT-STATS` | ¿Cifras/números en Nosotros? | sí / no | COMP-STATS | TPL-ABOUT-01 |
| `TGL-ABOUT-TEAM` | ¿Sección de equipo? | sí / no | COMP-TEAM | TPL-ABOUT-01 |
| `TGL-CONTACT-MAP` | ¿Mapa en Contacto? | sí / no | COMP-MAP-NAP | TPL-CONTACT-01 |
| `TGL-THANKS-DIRECT` | ¿Teléfono/WhatsApp en la página de gracias? | sí / no | COMP-CONTACT-DIRECT | TPL-THANKS-01 |
| `TGL-CART-VIEW` | ¿Carrito como drawer, página o ambos? | drawer / page / both | side-cart / cart page | TPL-CART-01 |
| `TGL-CART-CROSSSELL` | ¿"Completá tu compra" en el carrito? | sí / no | COMP-PRODUCT-CAROUSEL | TPL-CART-01 |
| `TGL-CART-SHIPBAR` | ¿Barra "te falta €X para envío gratis"? | sí / no | side-cart | TPL-CART-01 |
| `TGL-ORDER-SUMMARY` | ¿Resumen de orden sticky? | sticky / colapsable | resumen | TPL-CART-01, TPL-CHECKOUT-01 |
| `TGL-CHECKOUT-STEPS` | ¿Checkout en 1 paso o 2 pasos? | 1-step / 2-step | form checkout | TPL-CHECKOUT-01 |
| `TGL-CHECKOUT-HEADER` | ¿Header de checkout minimal o completo? | minimal / full | COMP-HEADER | TPL-CHECKOUT-01 |

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
- Cada `TPL-*` declara en su doc qué toggles admite y con qué default. **Esa tabla manda.**
