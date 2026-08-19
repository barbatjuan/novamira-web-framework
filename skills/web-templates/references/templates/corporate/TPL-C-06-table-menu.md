# TPL-C-06 — Mesa / Carta

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Mesa / Carta |
| Objetivo | Que el usuario decida venir a comer: la carta y el sitio convencen, la reserva cierra |
| Ideal para | Restaurantes, asadores, bares con cocina, vinotecas, cafés de especialidad |
| Ejemplos | Restaurante de mercado, parrilla, taberna, brunch, coctelería con cocina |
| Nivel de contenido | Medio (carta larga + una voz que la firme) |
| Protagonismo | La FOTOGRAFÍA a sangre y la CARTA con precios; la reserva va después, no antes |
| ADN | Héroe a sangre con el claim encima + carta con precios + una persona que la firma + horario en grande. NO rejilla de servicios, NO mapa embebido, NO reseñas como sección propia. |

**Por qué existe habiendo TPL-C-05.** TPL-C-05 es el negocio con puerta: te dice qué se hace ahí,
te enseña el local y te pone a reservar. Sirve para una clínica, una peluquería y un taller, y por
eso *no* sirve del todo para una mesa. Un restaurante se decide por dos cosas que TPL-C-05 no tiene
en su inventario: **la carta con los precios delante** y **la cara de quien cocina**. Y las decide
antes de mirar el formulario — de ahí que aquí la reserva sea la penúltima sección y no la tercera.

La ausencia es igual de deliberada: aquí **no hay `COMP-SERVICES`**. Una rejilla de tres tarjetas
titulada "qué hacemos" en un restaurante es relleno, porque lo que se hace ya está en la carta.

## 2. Wireframe (top → bottom)

```
COMP-HEADER (transparente sobre el héroe, teléfono + "Reservar") [fijo]
COMP-HERO-FULL (foto a sangre, claim encima, 78vh) [fijo · ADN]
COMP-MARQUEE (cinta con lo que define la casa) [toggle]
COMP-MENU-LIST (la carta: plato, descripción, precio) [fijo · ADN]
COMP-FIGURE-QUOTE (quien cocina: retrato a sangre + cita + firma) [fijo · ADN]
COMP-GALLERY (collage desplazado del local) [toggle]
COMP-BOOKING (reserva) [fijo]
COMP-HOURS-BLOCK (horario y dirección en grande) [fijo · ADN]
COMP-FOOTER (con NAP) [fijo]
```

El orden ES el argumento: se entra por la foto, se decide con la carta, se confía con la persona y
se reserva al final. Ausencia intencional: sin rejilla de servicios, sin mapa embebido, sin
reseñas como sección propia — la reseña, si la hay, vive dentro de `COMP-FIGURE-QUOTE`.

## 3. Secciones

### COMP-HEADER `[fijo]`
Objetivo: reservar o llamar sin bajar (conversión). Logo, nav corta, **teléfono clickeable** y
botón **"Reservar"**. **Transparente y superpuesto al héroe**, no una barra sólida encima de él:
la primera pantalla es la fotografía y una barra opaca le come 72px. Al hacer scroll se vuelve
sólida. Mobile: teléfono (tap-to-call) + CTA visibles. Reutilizable: GLOBAL (variante local).

### COMP-HERO-FULL — a sangre `[fijo · ADN] · TGL-HERO-HEIGHT`
Objetivo: que el sitio se vea antes de que se lea nada (deseo). Fotografía a sangre de borde a
borde, el claim **encima** de ella y no al lado, apoyado en el tercio inferior con un velo que sólo
carga donde hay texto. ~78vh en escritorio, ~70vh en móvil. H1 único. Reutilizable: SECCIÓN.
**Nota:** el velo se mide contra el peor píxel de la zona de texto, nunca se elige a ojo.

### COMP-MARQUEE — cinta `[toggle TGL-MARQUEE]`
Objetivo: decir en cuatro palabras lo que la casa no negocia (identidad). Una banda estrecha con
tres o cuatro afirmaciones cortas repetidas, separadas por un signo. Desplazamiento lento y
continuo; **se detiene bajo `prefers-reduced-motion`** y entonces se lee como una banda estática.
Nunca lleva enlaces: es un rótulo, no una navegación. Reutilizable: SECCIÓN.

### COMP-MENU-LIST — la carta `[fijo · ADN] · TGL-MENU-PRICES`
Objetivo: que el precio no sea una sorpresa (decisión). Dos o tres grupos (entrantes, brasa,
postres) y dentro de cada uno una lista: **nombre del plato, una línea de descripción, y el precio
alineado a la derecha con un filete que los une**. Sin fotos por plato. Mobile: 1 columna.
Desktop: 2 columnas de grupos. Reutilizable: SECCIÓN.
**Por qué sin fotos:** una carta con foto por plato es una carta de menú del día; el sitio que
quiere que confíes en la cocina enseña el nombre y el precio y deja la fotografía para la sala.

### COMP-FIGURE-QUOTE — quien firma `[fijo · ADN]`
Objetivo: poner una persona detrás de la comida (confianza). Media pantalla de retrato a sangre y
media de texto: una frase larga en tamaño de titular, el nombre, el papel que ocupa y una firma.
El bloque de texto va sobre el acento o sobre el fondo alterno, nunca sobre el fondo base — es un
alto en el camino. Mobile: retrato arriba, texto debajo. Reutilizable: SECCIÓN.

### COMP-GALLERY — el local `[toggle TGL-GALLERY]`
Objetivo: enseñar la sala (deseo). Collage **desplazado**: las piezas no comparten línea de base
ni tamaño. Mobile: carrusel. Desktop: composición de 5–6 con dos alturas. Reutilizable: GLOBAL
(`COMP-GALLERY`, variante desplazada).

### COMP-BOOKING — reserva `[fijo] · TGL-BOOKING`
Objetivo: cerrar (conversión). Día y hora como grupos de radio visibles, no un desplegable: las
horas libres SON el argumento. Reutilizable: GLOBAL (`COMP-BOOKING`). **Nota:** depende de plugin
(Bookly, Amelia, formulario o enlace externo) — validar en `project-context`; si no hay, degradar a
WhatsApp/teléfono.

### COMP-HOURS-BLOCK — horario en grande `[fijo · ADN]`
Objetivo: que nadie venga un lunes (navegación). El horario compuesto **como pieza gráfica**: los
días y las franjas en tamaño de titular, no en una tabla de 9px al pie. Dirección y teléfono al
lado, en texto copiable. Sin mapa embebido — un iframe de mapa es una petición a un tercero antes
de que nadie haya consentido nada. Reutilizable: SECCIÓN.

### COMP-FOOTER `[fijo]`
Objetivo: NAP completo, redes, legal. Repite dirección, teléfono y horario. Reutilizable: GLOBAL.

## 4. Toggles admitidos

| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-HERO-HEIGHT` | 78vh | ADN: a sangre |
| `TGL-MARQUEE` | on | cinta de identidad |
| `TGL-MENU-PRICES` | on | off sólo si la casa no publica precios |
| `TGL-GALLERY` | on | collage del local |
| `TGL-BOOKING` | form/embed | o WhatsApp/teléfono si no hay plugin |
| `TGL-CTA-STRENGTH` | medio | |

**Fijos:** COMP-HEADER, COMP-HERO-FULL, COMP-MENU-LIST, COMP-FIGURE-QUOTE, COMP-BOOKING,
COMP-HOURS-BLOCK, COMP-FOOTER.
**Ausencias de ADN:** rejilla de servicios, mapa embebido, reseñas como sección propia, pricing
tables → si el cliente las pide, sugerir TPL-C-05, no deformar esta.

## 5. SEO / semántica
1 `H1` (héroe). `H2` en Carta, Quien firma, Reserva y Horario. Schema `Restaurant` +
`hasMenu`/`Menu` + `OpeningHoursSpecification` + `PostalAddress` + `geo`. La carta en HTML de
verdad y **nunca como PDF ni como imagen**: un PDF no se indexa como carta, no se lee con lector de
pantalla y no se puede copiar un plato en una búsqueda. `header` > `main` > `footer`.
