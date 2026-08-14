# TPL-C-05 — Local / Booking

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Local / Booking |
| Objetivo | Que el usuario reserve/agende o llegue al local: conversión local |
| Ideal para | Clínicas, gastronómico, peluquerías, spa, profesionales, servicios locales |
| Ejemplos | Consultorio médico, restaurante, barbería, estudio de tatuajes, gimnasio |
| Nivel de contenido | Bajo–medio (servicios + info práctica) |
| Protagonismo | La RESERVA/turno + ubicación/horarios; lo local manda |
| ADN | CTA de reserva + NAP (dirección, teléfono, horarios) + mapa protagonistas. NO portfolio grande, NO pricing complejo. |

## 2. Wireframe (top → bottom)

```
COMP-HEADER (con teléfono + CTA "Reservar") [fijo · ADN]
COMP-HERO (imagen + claim local + CTA reserva ~45vh) [fijo · ADN]
COMP-SERVICES (servicios / carta / prestaciones) [fijo]
COMP-BOOKING (bloque de reserva / turno) [fijo · ADN]
COMP-GALLERY (fotos del local / trabajos) [toggle]
COMP-TESTIMONIAL (reseñas) [toggle]
COMP-MAP-NAP (mapa + dirección + horarios) [fijo · ADN]
COMP-FOOTER (con NAP) [fijo]
```

Todo lleva a reservar o visitar. Ausencia intencional: sin portfolio extenso, sin pricing tables complejas.

## 3. Secciones

### COMP-HEADER `[fijo · ADN]`
Objetivo: contacto y reserva inmediatos (conversión). Logo, nav corto, **teléfono clickeable** y
botón **"Reservar"**. Mobile: teléfono (tap-to-call) + CTA visibles. Desktop: nav + teléfono + CTA.
Sticky. Reutilizable: GLOBAL (variante local).

### COMP-HERO — local `[fijo · ADN] · TGL-HERO-TYPE, TGL-HERO-HEIGHT`
Objetivo: identidad del local + reserva (conversión). Imagen del local + claim + CTA "Reservar /
Pedir turno". Mobile: 45vh, CTA full-width. Desktop: ~45vh. H1 único. Reutilizable: SECCIÓN.

### COMP-SERVICES — prestaciones `[fijo]`
Objetivo: qué ofrece el local (descubrimiento). Servicios/carta/prestaciones con precio orientativo
opcional. Mobile: 1 columna. Desktop: 2–3. Reutilizable: GLOBAL.

### COMP-BOOKING — reserva `[fijo · ADN] · TGL-BOOKING`
Objetivo: capturar la reserva/turno (conversión). Formulario o embed de reserva (fecha/hora/servicio)
o CTA a WhatsApp/plataforma de turnos. Mobile: full-width, simple. Desktop: form o embed centrado.
Reutilizable: GLOBAL (`COMP-BOOKING`). **Nota:** la reserva suele depender de plugin (Bookly,
Amelia, formulario, o link externo) — validar en `project-context`; si no hay, degradar a
WhatsApp/teléfono/form de contacto.

### COMP-GALLERY — fotos `[toggle]`
Objetivo: mostrar el lugar/trabajos (confianza). Grid o carrusel de fotos. Mobile: carrusel.
Desktop: grid 3–4. Reutilizable: GLOBAL (`COMP-GALLERY`).

### COMP-TESTIMONIAL — reseñas `[toggle TGL-TESTIMONIALS]`
Objetivo: reputación local (confianza). Reseñas (idealmente Google). Reutilizable: GLOBAL.

### COMP-MAP-NAP — ubicación `[fijo · ADN] · TGL-MAP`
Objetivo: que lleguen / confíen (navegación + confianza). Mapa embed + dirección + horarios +
teléfono + cómo llegar. Mobile: mapa arriba, datos abajo. Desktop: split mapa | datos. Reutilizable:
GLOBAL (`COMP-MAP-NAP`). Elementor: Google Maps widget. Divi: Map module.

### COMP-FOOTER `[fijo]`
Objetivo: NAP completo, horarios, redes, legal. Repite dirección/teléfono/horarios. Reutilizable: GLOBAL.

## 4. Toggles admitidos

| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-HERO-TYPE` | imagen fija | |
| `TGL-HERO-HEIGHT` | 45vh | |
| `TGL-BOOKING` | form/embed | o WhatsApp/teléfono si no hay plugin |
| `TGL-MAP` | on | ADN |
| `TGL-TESTIMONIALS` | on | reseñas |
| `TGL-CTA-STRENGTH` | medio | |

**Fijos:** COMP-HEADER (teléfono+CTA), COMP-HERO, COMP-SERVICES, COMP-BOOKING, COMP-MAP-NAP, COMP-FOOTER.
**Ausencias de ADN:** portfolio extenso, pricing tables complejas, secuencia de landing larga →
sugerir TPL-C-03 o TPL-C-04.

## 5. SEO / semántica
1 `H1` (hero). `H2` en Servicios, Reserva, Ubicación. Schema `LocalBusiness` + `OpeningHours` +
`PostalAddress` + `geo` (crítico para SEO local, ver `wordpress-seo`). NAP consistente en toda la
página. `header` > `main` > `footer`.
