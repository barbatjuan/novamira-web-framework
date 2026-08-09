# CAPA 3 — Toggles (ajuste fino modular)

Preguntas de ajuste **acotadas a lo que cada plantilla permite**. Cada respuesta prende/apaga o
intercambia un bloque modular. El agente NO pregunta lo que la plantilla ya resuelve por ADN.

Los defaults llegan precargados desde el intake de referencias (CAPA 2). El cliente confirma o cambia.

## Catálogo de toggles

| ID | Pregunta al cliente | Opciones | Afecta | Aplica en |
|----|--------------------|----------|--------|-----------|
| `TGL-HERO-TYPE` | ¿Hero con slider o imagen fija? | slider / imagen fija | COMP-HERO | TPL-E-01, TPL-E-03 (fija-only) |
| `TGL-HERO-HEIGHT` | ¿Qué tan alto el hero? | full 60vh / medio 45vh / bajo 35vh | COMP-HERO | TPL-E-01, TPL-E-04, TPL-E-05 |
| `TGL-CARD-STYLE` | ¿Cards con imagen grande o compactas? | imagen grande / compacta con datos | COMP-PRODUCT-CARD | todas ecommerce |
| `TGL-CARD-IMG` | ¿Las cards muestran imagen? | sí / no (solo texto+precio) | COMP-PRODUCT-CARD | todas ecommerce |
| `TGL-STYLE` | ¿Estilo general? | minimalista / elegante-editorial / comercial | tokens (radius, spacing, densidad) | todas |
| `TGL-FOCUS` | ¿Foco de la home? | venta directa / CTA-branding | orden + protagonismo de bloques | TPL-E-01, TPL-E-03, TPL-E-04 |
| `TGL-CATEGORIES` | ¿Mostrar bloque de categorías? | sí / no | COMP-CATEGORY-CARD | TPL-E-01, TPL-E-02, TPL-E-05 |
| `TGL-BENEFITS` | ¿Bloque de beneficios (envíos, pagos, garantía)? | sí / no | COMP-BENEFITS | todas |
| `TGL-TESTIMONIALS` | ¿Testimonios / social proof? | sí / no | COMP-TESTIMONIAL | TPL-E-01, TPL-E-04, TPL-E-05 (fijo en TPL-E-03) |
| `TGL-NEWSLETTER` | ¿Captación de email? | sí / no | COMP-NEWSLETTER | todas |
| `TGL-ANNOUNCEMENT` | ¿Barra de anuncio arriba? | sí / no | COMP-ANNOUNCEMENT | todas (fijo en TPL-E-05) |
| `TGL-TRUST` | ¿Trust badges (pagos seguros, medios)? | sí / no | COMP-TRUST-BADGES | todas |
| `TGL-CTA-STRENGTH` | ¿Qué tan agresivo el CTA? | suave / medio / fuerte | estilo de COMP-CTA | todas |

## Catálogo de toggles — corporate

| ID | Pregunta al cliente | Opciones | Afecta | Aplica en |
|----|--------------------|----------|--------|-----------|
| `TGL-LEAD-FORM` | ¿Formulario en el hero o solo CTA (form al final)? | form en hero / solo CTA | COMP-LEAD-FORM | TPL-C-01 |
| `TGL-LOGOS` | ¿Logos de clientes / credenciales? | sí / no | COMP-LOGOS | TPL-C-01, C-02, C-03 |
| `TGL-PROCESS` | ¿Bloque "cómo trabajamos" / pasos? | sí / no | COMP-PROCESS | TPL-C-01 |
| `TGL-CASES` | ¿Casos de éxito / resultados? | sí / no | COMP-CASES | TPL-C-01 |
| `TGL-STATS` | ¿Cifras / números (años, clientes)? | sí / no | COMP-STATS | TPL-C-02 (fijo) |
| `TGL-TEAM` | ¿Sección de equipo? | sí / no | COMP-TEAM | TPL-C-02 |
| `TGL-FEATURES` | ¿Detalle de "qué incluye"? | sí / no | COMP-FEATURES | TPL-C-04 |
| `TGL-PRICING` | ¿Tabla de precios / planes? | sí / no | COMP-PRICING | TPL-C-04 |
| `TGL-FAQ` | ¿Preguntas frecuentes? | sí / no | COMP-FAQ | TPL-C-04 |
| `TGL-BOOKING` | ¿Cómo reserva el cliente? | form/embed / WhatsApp / teléfono | COMP-BOOKING | TPL-C-05 |
| `TGL-MAP` | ¿Mapa + ubicación? | sí / no | COMP-MAP-NAP | TPL-C-05 (fijo) |

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

Compartidos entre ecommerce y corporate: `TGL-HERO-TYPE`, `TGL-HERO-HEIGHT`, `TGL-STYLE`,
`TGL-CTA-STRENGTH`, `TGL-NEWSLETTER`, `TGL-TESTIMONIALS`, `TGL-TRUST`, `TGL-FAQ`, `TGL-CARD-STYLE`,
`TGL-CARD-IMG`.

## Notas

- Un toggle marcado **FIJO** en una plantilla no se pregunta (ej: `TGL-TESTIMONIALS` en TPL-E-03,
  `TGL-MAP` en TPL-C-05).
- Los toggles nunca rompen el ADN. No hay toggle "poné storytelling en TPL-E-02" — si el cliente
  lo pide, sugerir cambiar de plantilla, no deformar la actual.
- Cada `TPL-*` declara en su doc qué toggles admite y con qué default.
