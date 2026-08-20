# TPL-CONTACT-01 — Contacto / Consulta

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Contacto / Consulta |
| Pregunta del visitante | "¿Me van a contestar, cuándo, y quién?" |
| Sirve para | Negocios donde el contacto es una CONSULTA que hay que estudiar antes de responder |
| Ejemplos | Asesoría, agencia, despacho, industrial con presupuesto, distribuidor B2B, servicios técnicos |
| ADN | El formulario es el sitio al que va la página, y a su lado está lo que casi nunca se pone: qué pasa después de enviarlo y con quién se va a hablar. |

**Por qué el formulario necesita defensa.** Un formulario de contacto es una caja negra: el visitante
escribe, pulsa y no sabe si aquello llegó, quién lo va a leer ni cuándo. Esa incertidumbre es la que
hace que mucha gente prefiera llamar aunque le venga peor. Por eso este arquetipo tiene dos secciones
que la mayoría de las páginas de contacto no tienen:

1. **`COMP-PROCESS`: qué pasa cuando le das a enviar.** Tres pasos con plazo real: "lo lee una
   persona el mismo día laborable → te llamamos para entender el caso → te mandamos propuesta en
   48 h". Convierte el formulario en un principio de conversación y no en un buzón.
2. **`COMP-TEAM`: con quién vas a hablar.** Dos o tres caras con nombre y con qué llevan. Saber a
   quién le escribes cambia lo que escribes, y cambia si escribes.

**Cuándo NO usar ésta.** Si lo que el visitante necesita es ir o llamar —un restaurante, una
clínica, una tienda—, el formulario estorba y la página es `TPL-CONTACT-02`. La señal es simple:
si la respuesta útil se da en el mismo minuto, no es una consulta, es una llamada.

## 2. Wireframe (top → bottom)
```
COMP-HEADER [fijo]
COMP-PAGE-HEAD [fijo] · H1 "Contacto" + una línea de qué contestamos y en cuánto
COMP-CONTACT-FORM [fijo · ADN] · nombre, email, asunto, mensaje — corto y con labels visibles
COMP-CONTACT-DIRECT [fijo] · email, teléfono y horario de atención, al lado del formulario
COMP-PROCESS [fijo · ADN] · qué pasa después de enviar, con plazos reales
COMP-TEAM [toggle TGL-CONTACT-WHO] · con quién vas a hablar
COMP-FAQ [toggle TGL-FAQ] · lo que se pregunta antes de escribir
COMP-FOOTER [fijo]
```

## 3. Secciones
### COMP-CONTACT-FORM `[fijo · ADN]`
Capturar la consulta. Nombre, email, asunto, mensaje, enviar (primary). **Labels visibles, no
placeholders** — el placeholder desaparece al escribir y deja al usuario adivinando qué campo era.
Cada campo de más baja el envío, así que teléfono y empresa sólo si de verdad se usan. Mobile: a
todo el ancho, apilado. Desktop: formulario a un lado, datos al otro. Reutilizable: GLOBAL.
**Nota:** conecta a email/CRM; validar el plugin de formularios en `project-context`. Elementor:
Form widget. Divi: Contact Form.

### COMP-CONTACT-DIRECT `[fijo]`
Email, teléfono con tap-to-call y horario de atención. Va **junto al formulario**, no debajo del
todo: quien prefiere llamar tiene que verlo sin bajar. Reutilizable: GLOBAL.

### COMP-PROCESS `[fijo · ADN]`
3 pasos con plazo. Se cumple o se cambia el número — un plazo publicado que no se cumple hace más
daño que no publicarlo. Reutilizable: GLOBAL.

### COMP-TEAM `[toggle TGL-CONTACT-WHO]`
2–3 personas: foto, nombre, qué llevan. Caras reales. Reutilizable: GLOBAL.

## 4. Toggles admitidos
| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-CONTACT-WHO` | on | apagar si no hay caras reales que poner |
| `TGL-FAQ` | off | |
| `TGL-CONTACT-MAP` | **off** | ver ADN: si el sitio importa, la página es `TPL-CONTACT-02` |

**Fijos:** HEADER, PAGE-HEAD, CONTACT-FORM, CONTACT-DIRECT, PROCESS, FOOTER.
**Ausencias de ADN:** mapa, horario de apertura, galería del local, reserva.

## 5. SEO / semántica
1 `H1` (Contacto). Schema `Organization` + `ContactPoint` con `contactType` y `availableLanguage`.
Formulario accesible: `<label for>` real en cada campo, errores en texto y no sólo en color, y foco
visible. Página de gracias propia (`TPL-THANKS-01`) o el envío no se puede medir. `header` >
`main` > `footer`.
