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

## Notas

- Un toggle marcado **FIJO** en una plantilla no se pregunta (ej: `TGL-TESTIMONIALS` en TPL-E-03).
- Los toggles nunca rompen el ADN. No hay toggle "poné storytelling en TPL-E-02" — si el cliente
  lo pide, sugerir cambiar de plantilla, no deformar la actual.
- Cada `TPL-*` declara en su doc qué toggles admite y con qué default.
- Toggles corporate (leads, servicios, equipo, casos) se agregan al definir los TPL-C-*.
