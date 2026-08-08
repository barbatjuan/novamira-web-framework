# TPL-C-01 — Services / Lead-Gen

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Services / Lead-Gen |
| Objetivo | Generar leads: capturar contacto para servicios B2B/B2C |
| Ideal para | Consultoras, agencias, estudios, desarrollo, marketing, servicios profesionales |
| Ejemplos | Agencia digital, consultora contable, estudio de arquitectura comercial, software factory |
| Nivel de contenido | Medio (servicios + prueba, poco editorial largo) |
| Protagonismo | El SERVICIO y la conversión; la marca acompaña |
| ADN | Formulario / captura de lead protagonista, CTA en toda la página, servicios claros. NO booking, NO catálogo. |

## 2. Wireframe (top → bottom)

```
COMP-HEADER (logo + nav + CTA "Contactar") [fijo]
COMP-HERO (headline + value prop + CTA/form ~50vh) [fijo · ADN]
COMP-LOGOS (clientes / confían en nosotros) [toggle]
COMP-SERVICES (grid de servicios) [fijo · ADN]
COMP-PROCESS (cómo trabajamos) [toggle]
COMP-CASES (casos de éxito / resultados) [toggle]
COMP-TESTIMONIAL [toggle]
COMP-CTA + COMP-LEAD-FORM (banda de conversión) [fijo · ADN]
COMP-FOOTER [fijo]
```

Todo empuja al contacto. Ausencia intencional: sin reserva/turnos, sin portfolio visual pesado.

## 3. Secciones

### COMP-HEADER `[fijo]`
Objetivo: navegar + CTA siempre visible (conversión). Logo, nav corto, **botón "Contactar"** destacado.
Mobile: hamburguesa + CTA visible. Desktop: nav + CTA sólido. Sticky. Reutilizable: GLOBAL.

### COMP-HERO — headline + captura `[fijo · ADN] · TGL-HERO-TYPE, TGL-LEAD-FORM`
Objetivo: proponer valor y capturar (conversión). Headline claro (qué resolvés), subtítulo, y
**CTA fuerte o mini-formulario** (nombre + email/teléfono). Mobile: 55vh, texto + CTA full-width,
form corto. Desktop: ~50vh, split (texto | form) o texto + CTA. H1 único. `TGL-LEAD-FORM` decide
form en hero vs solo CTA. Reutilizable: SECCIÓN + `COMP-LEAD-FORM`. Elementor: Form widget. Divi:
Contact Form / módulo de formulario.

### COMP-LOGOS — prueba social `[toggle TGL-LOGOS]`
Objetivo: confianza rápida (confianza). Fila de logos de clientes/partners en gris. Mobile: 2–3
por fila o carrusel. Desktop: 5–6 en fila. Reutilizable: GLOBAL.

### COMP-SERVICES — grid `[fijo · ADN] · TGL-CARD-STYLE`
Objetivo: comunicar qué ofrecés (descubrimiento + venta). 3–6 cards: ícono/imagen + título +
descripción corta + link. Mobile: 1 columna. Tablet: 2. Desktop: 3. Reutilizable: GLOBAL
(`COMP-SERVICES` card). Elementor: Loop/Icon Box grid. Divi: Blurb grid.

### COMP-PROCESS — cómo trabajamos `[toggle TGL-PROCESS]`
Objetivo: bajar fricción, mostrar método (confianza). 3–5 pasos numerados. Mobile: vertical con
línea. Desktop: fila horizontal con conectores. Reutilizable: GLOBAL.

### COMP-CASES — casos de éxito `[toggle TGL-CASES]`
Objetivo: prueba de resultados (confianza + venta). 2–3 casos con métrica/resultado + link.
Mobile: 1 columna. Desktop: 2–3. Reutilizable: GLOBAL.

### COMP-TESTIMONIAL `[toggle TGL-TESTIMONIALS]`
Objetivo: social proof (confianza). Reseñas con nombre/cargo/empresa. Reutilizable: GLOBAL.

### COMP-CTA + COMP-LEAD-FORM — banda de conversión `[fijo · ADN]`
Objetivo: cerrar el lead (conversión). Banda con claim + formulario completo (nombre, email,
teléfono, mensaje) o CTA a agenda. Mobile: stack, form full-width. Desktop: split claim | form.
Reutilizable: GLOBAL (`COMP-LEAD-FORM`). **Nota:** el form conecta a email/CRM; validar plugin
de formularios en `project-context`.

### COMP-FOOTER `[fijo]`
Objetivo: contacto, servicios, legal, redes. Incluir email/teléfono. Reutilizable: GLOBAL.

## 4. Toggles admitidos

| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-HERO-TYPE` | imagen/color fija | slider opcional |
| `TGL-LEAD-FORM` | form en hero | o solo CTA en hero + form al final |
| `TGL-LOGOS` | on | |
| `TGL-PROCESS` | on | |
| `TGL-CASES` | on | |
| `TGL-TESTIMONIALS` | on | |
| `TGL-STYLE` | elegante / corporate | |
| `TGL-CTA-STRENGTH` | fuerte | ADN |

**Fijos:** COMP-HEADER (con CTA), COMP-HERO, COMP-SERVICES, COMP-CTA+COMP-LEAD-FORM, COMP-FOOTER.
**Ausencias de ADN:** booking/turnos, mapa protagonista, portfolio visual grande → sugerir
TPL-C-05 o TPL-C-03.

## 5. SEO / semántica
1 `H1` (hero). `H2` en Servicios, Casos, Contacto. `header` > `main` > `footer`. Schema
`Organization` + `Service`. Form accesible (labels).
