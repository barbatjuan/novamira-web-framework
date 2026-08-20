# TPL-CONTACT-02 — Contacto / Puerta abierta

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Contacto / Puerta abierta |
| Pregunta del visitante | "¿Estáis abiertos ahora, cómo llego y puedo pasar?" |
| Sirve para | Negocios con puerta, donde contactar significa ir o llamar, no escribir |
| Ejemplos | Restaurante, clínica, taller, peluquería, tienda, farmacia, gimnasio, hotel |
| ADN | Lo primero es si está abierto AHORA. Después, el teléfono, cómo se llega y cómo se reconoce la puerta. El formulario no está: sobra. |

**Por qué no hay formulario, que es la decisión entera de este arquetipo.** Quien busca el contacto
de un sitio con puerta quiere una de tres cosas: llamar, ir, o reservar. Ninguna de las tres se hace
escribiendo un mensaje y esperando. Un formulario en esta página no ayuda: **compite** con el
teléfono, y quien lo rellena a las nueve de la noche se queda esperando una respuesta que podría
haber tenido en el acto llamando al día siguiente.

Tres decisiones estructurales:

1. **El estado de ahora va primero.** No una tabla de horarios: una línea que dice "Abierto · cierra
   a las 23:30" o "Hoy cerrado · abrimos mañana a las 13:00". Es el dato más buscado de un negocio
   con puerta y casi siempre está enterrado en el pie.
2. **Cómo se reconoce la puerta.** `COMP-GALLERY` con la fachada, el portal, la entrada del
   aparcamiento. Suena menor y no lo es: mucha gente que no encuentra la entrada a la primera se va,
   y una fotografía de la fachada evita más llamadas que cualquier texto.
3. **Cierra en reserva, no en formulario.** `COMP-BOOKING` para mesa, cita o turno, con la
   antelación necesaria escrita al lado.

**Cuándo NO usar ésta.** Si la consulta hay que estudiarla antes de contestarla —un presupuesto, un
caso, un proyecto—, el teléfono no basta y la página es `TPL-CONTACT-01`.

## 2. Wireframe (top → bottom)
```
COMP-HEADER [fijo]
COMP-HERO [fijo · ADN] · la fachada o la entrada, no un icono de sobre
COMP-HOURS-BLOCK [fijo · ADN] · el estado de AHORA en primera línea, luego el horario por días
COMP-CONTACT-DIRECT [fijo · ADN] · teléfono con tap-to-call, WhatsApp, email — en ese orden
COMP-MAP-NAP [fijo · ADN] · dónde está, cómo se llega, transporte y aparcamiento
COMP-GALLERY [fijo] · cómo se reconoce la puerta: fachada, portal, entrada del parking
COMP-BOOKING [fijo · ADN] · reservar mesa, pedir cita o coger turno — el cierre
COMP-FOOTER [fijo]
```

## 3. Secciones
### COMP-HERO `[fijo · ADN]`
La fachada o la entrada, fotografiada de frente y a la luz normal. Mobile: 30vh. Desktop: 40vh. H1
único, con la ciudad dentro. Reutilizable: SECCIÓN.

### COMP-HOURS-BLOCK `[fijo · ADN]`
Primera línea: el estado de ahora, calculado, no escrito a mano. Debajo, el horario por días,
festivos y cierre por vacaciones. Reutilizable: GLOBAL. **Nota:** si el estado se calcula en el
cliente, la zona horaria se fija a la del negocio, no a la del visitante.

### COMP-CONTACT-DIRECT `[fijo · ADN]`
Teléfono primero y como **enlace `tel:`**, WhatsApp si se atiende de verdad, email después. Un
teléfono que no se puede pulsar en un móvil es un teléfono que hay que copiar a mano. Reutilizable:
GLOBAL.

### COMP-MAP-NAP `[fijo · ADN]`
Mapa, dirección, transporte y aparcamiento. El mapa embebido carga un tercero: **consentimiento
previo**, y hasta entonces una imagen con la dirección encima. Reutilizable: GLOBAL.

### COMP-GALLERY `[fijo]`
3–5 fotos de aproximación: la calle, la fachada, la puerta, la entrada del parking. Con pie.
Reutilizable: GLOBAL.

### COMP-BOOKING `[fijo · ADN]`
Reservar, pedir cita o coger turno, con la antelación necesaria y hasta cuántas personas.
Reutilizable: GLOBAL.

## 4. Toggles admitidos
| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-CONTACT-WHATSAPP` | on | apagar si nadie lo atiende — peor que no tenerlo |
| `TGL-BOOKING-TYPE` | según la home | mesa / cita / turno |
| `TGL-CONTACT-FORM` | **off** | ver ADN: el formulario compite con el teléfono y pierde |

**Fijos:** HEADER, HERO, HOURS-BLOCK, CONTACT-DIRECT, MAP-NAP, GALLERY, BOOKING, FOOTER.
**Ausencias de ADN:** formulario de consulta, "qué pasa después de enviar", equipo de atención.

## 5. SEO / semántica
1 `H1` con negocio + ciudad ("Dónde estamos · Casa Terrazza, Estella"). Schema `LocalBusiness` +
`PostalAddress` + `OpeningHoursSpecification` + `telephone` + `geo`, y `Restaurant` / `Dentist` /
el tipo que sea en vez del genérico. **NAP idéntico letra por letra** al de la home, el pie y la
ficha de Google: una abreviatura distinta de la calle en un sitio ya rompe la coincidencia.
`header` > `main` > `footer`.
