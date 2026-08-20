# TPL-ABOUT-03 — Nosotros / La casa

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Nosotros / La casa |
| Pregunta del visitante | "¿Cómo es estar ahí, y cuándo puedo ir?" |
| Sirve para | Negocios con puerta, donde la visita ES la conversión |
| Ejemplos | Restaurante, hotel rural, clínica, peluquería, tienda física, bodega con visitas, gimnasio |
| ADN | La sala fotografiada, quién te atiende, el horario y cómo se llega, y la reserva como cierre. Se responde con EL SITIO. |

**Por qué existe habiendo `TPL-ABOUT-01` y `TPL-ABOUT-02`.** Las dos responden preguntas que aquí no
se hacen. A un restaurante nadie le pregunta cuántos clientes lleva ni cómo fabrica; le preguntan
cómo es el comedor, si hay terraza, a qué hora cierran y si hay que reservar. La historia de la casa
importa, pero **importa menos que la fotografía del sitio**, y por eso aquí no hay bloque de historia
larga: la que haya cabe en el hero y en los pies de foto.

Dos decisiones que la separan del resto:

1. **No hay `COMP-CTA` genérico: cierra en `COMP-BOOKING`.** Un botón de "contacto" al final de la
   página de un restaurante es tirar la conversión. La acción es reservar mesa, pedir cita o
   apuntarse a la visita, y va con su control, no con un enlace.
2. **El horario y el mapa son contenido, no pie de página.** `COMP-HOURS-BLOCK` con el estado de
   ahora —abierto, cierra en 40 min, hoy cerrado— y `COMP-MAP-NAP` con cómo llegar y dónde aparcar.
   Es la información que más se busca de un negocio con puerta y la que peor suele estar puesta.

El equipo sí está, y aquí sí es una rejilla: en un sitio que se visita, saber quién te va a atender
es parte de saber cómo es el sitio. Con caras reales — en una casa que se puede visitar, un retrato
de banco de imágenes se desmiente el día que el cliente entra por la puerta.

## 2. Wireframe (top → bottom)
```
COMP-HEADER [fijo]
COMP-HERO [fijo · ADN] · la sala, llena y con luz de la hora a la que se visita
COMP-GALLERY [fijo · ADN] · el sitio por dentro, con pies de foto que cuentan la casa
COMP-TEAM [fijo · ADN] · quién te atiende, con caras reales
COMP-HOURS-BLOCK [fijo · ADN] · horario con el estado de AHORA, no una tabla muerta
COMP-MAP-NAP [fijo · ADN] · dónde está, cómo se llega, dónde se aparca
COMP-BOOKING [fijo · ADN] · reservar, pedir cita o apuntarse — el cierre de la página
COMP-FOOTER [fijo]
```

## 3. Secciones
### COMP-HERO `[fijo · ADN]`
La sala, no el logo. Con gente si el sitio se llena y sin ella si lo que se vende es calma, pero
**a la luz de la hora a la que se visita**: un restaurante de cenas fotografiado a mediodía enseña
otro sitio. Mobile: 40vh. Desktop: 50–60vh. H1 único. Reutilizable: SECCIÓN.

### COMP-GALLERY `[fijo · ADN]`
6–10 fotos del sitio por dentro con **pie de foto real** — el pie es donde va la historia de la casa
en esta plantilla. Mobile: carrusel a todo el ancho. Desktop: mosaico. Reutilizable: GLOBAL.

### COMP-TEAM `[fijo · ADN]`
Quién atiende: foto, nombre, y qué hace en la casa. Mobile: 2 columnas. Desktop: 3–4. Reutilizable:
GLOBAL.

### COMP-HOURS-BLOCK `[fijo · ADN]`
Horario por días **con el estado de ahora** en primera línea: abierto / cierra en 40 min / hoy
cerrado. Festivos y cierre por vacaciones si los hay. Reutilizable: GLOBAL.

### COMP-MAP-NAP `[fijo · ADN]`
Mapa, dirección, teléfono con tap-to-call, transporte y aparcamiento. El mapa embebido carga un
tercero: **necesita consentimiento previo**, así que hasta aceptarlo va una imagen con la dirección
encima. Reutilizable: GLOBAL.

### COMP-BOOKING `[fijo · ADN]`
El cierre: reservar mesa, pedir cita, apuntarse a la visita. Con el dato que quita la duda al lado
(hasta cuántas personas, con cuánta antelación). Reutilizable: GLOBAL.

## 4. Toggles admitidos
| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-ABOUT-TEAM` | on | apagar sólo si de verdad no hay caras que enseñar |
| `TGL-BOOKING-TYPE` | según la home | mesa / cita / visita — hereda de `TPL-C-05` o `TPL-C-06` |

**Fijos:** HEADER, HERO, GALLERY, TEAM, HOURS-BLOCK, MAP-NAP, BOOKING, FOOTER.
**Ausencias de ADN:** bloque de historia largo, contador de cifras, sección de valores, CTA
genérico de contacto.

## 5. SEO / semántica
1 `H1` (la casa y su sitio: "El comedor de Casa Terrazza, en Estella"). `H2` en El sitio, Quién te
atiende, Horario, Cómo llegar. Schema `LocalBusiness` (o `Restaurant`, `Dentist`…) + `PostalAddress`
+ `OpeningHoursSpecification` + `geo`. NAP idéntico al de la home y al de la ficha de Google, letra
por letra. `header` > `main` > `footer`.
