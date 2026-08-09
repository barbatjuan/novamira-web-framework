# TPL-CONTACT-01 — Contacto

## 1. Identidad
Página de contacto: formulario + datos + mapa. Sirve para **ecommerce y corporate**. Hereda tokens
de la home. Precios (si aparecen) en **€**.

## 2. Wireframe (top → bottom)
```
COMP-HEADER [fijo]
Encabezado [fijo] · H1 "Contacto" + intro corta
Bloque principal [fijo · ADN]
   ├─ COMP-CONTACT-FORM (nombre, email, asunto, mensaje)
   └─ Datos de contacto (dirección, teléfono, email, horarios, redes)
COMP-MAP-NAP (mapa) [toggle TGL-CONTACT-MAP]
COMP-FAQ [toggle TGL-FAQ]
COMP-FOOTER [fijo]
```

## 3. Secciones
### Encabezado `[fijo]`
H1 "Contacto" + línea intro. Reutilizable: SECCIÓN.

### Bloque principal `[fijo · ADN]`
Objetivo: capturar consulta y dar datos (conversión + confianza).
- **COMP-CONTACT-FORM**: nombre, email, asunto, mensaje, enviar (primary). Mobile: full-width, stack.
  Desktop: form a un lado, datos al otro (2 columnas). Reutilizable: GLOBAL (`COMP-CONTACT-FORM`).
  **Nota:** conecta a email/CRM; validar plugin de formularios en `project-context`. Elementor: Form
  widget. Divi: Contact Form.
- **Datos**: dirección, teléfono (tap-to-call en mobile), email, horarios, redes. Reutilizable: SECCIÓN.

### COMP-MAP-NAP `[toggle TGL-CONTACT-MAP]`
Objetivo: ubicación (navegación + confianza local). Mapa embed + dirección. Mobile: mapa arriba,
datos abajo. Desktop: ancho o split. Reutilizable: GLOBAL (`COMP-MAP-NAP`). Elementor: Google Maps.
Divi: Map module.

### COMP-FAQ `[toggle TGL-FAQ]`
Objetivo: resolver dudas frecuentes (info). Acordeón. Reutilizable: GLOBAL (`COMP-FAQ`).

## 4. Toggles admitidos
| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-CONTACT-MAP` | on | apagar si es solo online sin local |
| `TGL-FAQ` | off | opcional |
| `TGL-STYLE` | hereda de la home | |

**Fijos:** HEADER, encabezado, form + datos, FOOTER.

## 5. SEO / semántica
1 `H1` (Contacto). Schema `Organization`/`LocalBusiness` + `PostalAddress` + `OpeningHours` si hay
local. Form accesible (labels). NAP consistente. `header` > `main` > `footer`.
