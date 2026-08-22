# TPL-C-14 — Ritual / Bono

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Ritual / Bono |
| Objetivo | Que la clienta elija por zona, vea cuánto dura y cuánto cuesta, y reserve — o compre el bono |
| Ideal para | Centros de estética, cabinas de belleza, spas urbanos, depilación, uñas y pestañas, estética no invasiva |
| Ejemplos | Centro de estética de barrio, cabina dentro de una peluquería, spa urbano, centro de depilación láser |
| Nivel de contenido | Medio-alto: cuatro zonas y seis u ocho rituales, cada uno con duración y precio |
| Protagonismo | EL RITUAL —qué te haces, cuánto dura, cómo sales— y LA CABINA donde ocurre |
| ADN | Selector por zona arriba + carta de rituales con duración, precio y efecto + recorrido por la cabina + el protocolo de la sesión en minutos + bonos y tarjeta regalo. NO antes/después, NO número de colegiado, NO tarifa larga. |

**Por qué existe habiendo TPL-C-05 y TPL-C-10.** `TPL-C-05` lleva a una puerta y enseña el local;
`TPL-C-10` publica un procedimiento sanitario con duración, sesiones y anestesia porque en una
consulta **el freno es el miedo**. En estética el freno es otro, y por eso el esqueleto es otro: la
clienta **ya quiere**. Lo que la para es no saber qué se le hace exactamente, cuánto tiempo se va a
quedar ahí, **cómo sale a la calle después** y cuánto cuesta si vuelve cinco veces. De ahí las tres
decisiones que no comparte con ninguno de los dos: se elige **por zona del cuerpo** antes que por
nombre comercial, cada ritual publica **el efecto al salir** además de la duración y el precio, y lo
que cierra la página **no es una cita suelta sino el bono**.

Y dos ausencias deliberadas, que son la otra mitad del argumento. **Antes/después** es el lenguaje de
`TPL-C-10` y aquí promete un resultado médico donde no lo hay: en publicidad sanitaria está regulado,
y un centro de estética que lo imita se coloca solo en un marco legal que no le corresponde.
**Número de colegiado** es una credencial que la mayoría de estas casas no tiene ni necesita; lo que
la sustituye aquí es la cabina, que se puede ver antes de entrar.

## 2. Wireframe (top → bottom)

```
COMP-HEADER (teléfono + CTA "Reservar") [fijo]
COMP-HERO-FULL (a sangre: la cabina, no un retrato de stock) [fijo]
COMP-ZONE-SELECTOR (rostro · cuerpo · manos y pies · depilación) [fijo · ADN]
COMP-RITUAL-MENU (duración · precio · cómo sales) [fijo · ADN]
COMP-CABIN-TOUR (cada frame nombra su espacio y para qué se usa) [fijo · ADN]
COMP-PROTOCOL-STEPS (la sesión contada en minutos reales) [toggle · default ON]
COMP-BONO-PACKS (bonos de sesiones + tarjeta regalo) [fijo · ADN]
COMP-BOOKING (día, hora y qué ritual) [fijo]
COMP-FOOTER (NAP + horarios) [fijo]
```

Ausencia intencional: sin antes/después, sin equipo con credencial, sin tarifa larga y sin
testimonios. Los cuatro son maneras de que otro hable por el centro; aquí hablan la carta y la
cabina, que es lo que la clienta va a comprobar el día que entre.

**Y la lista de secciones es sólo la mitad del arquetipo.** La otra mitad es la FORMA de cada una,
y hasta este documento el catálogo no la declaraba: las trece plantillas anteriores envolvían todas
sus secciones igual —banda, contenedor centrado, encabezado, lista— así que dos arquetipos con
inventarios distintos seguían leyéndose como la misma página con otra paleta. `RT_TPL_TOO_SIMILAR`
mide el inventario, que es lo que un documento puede declarar; no ve el envoltorio.
Aquí cada sección pide el suyo, y la columna de abajo es parte del contrato:

| Sección | Envoltorio | El gesto |
|---------|-----------|----------|
| `COMP-HERO-FULL` | banda con fotografía al fondo | titular a la izquierda sobre la sala, con la medida ancha para que entre en dos líneas |
| `COMP-ZONE-SELECTOR` | contenido | **retícula de filetes compartidos**, sin superficies de tarjeta |
| `COMP-RITUAL-MENU` | **banda a sangre** | la carta en un panel opaco apoyado sobre la fotografía de una sala |
| `COMP-CABIN-TOUR` | **la sección ES la fila** | texto a un lado, collage 2×2 con paspartú al otro |
| `COMP-PROTOCOL-STEPS` | contenido | contrapunto tranquilo: cuatro minutajes grandes sobre filete |
| `COMP-BONO-PACKS` | **banda a sangre** | damero: panel de tinta · panel de tinta · fotografía, y la tarjeta regalo FUERA |

Los tres envoltorios (`contenido`, `banda`, `fila`) son vocabulario compartido, no de este
arquetipo: cualquier `TPL-*` puede pedirlos. Lo propio de éste es CUÁL pide cada sección.

## 3. Secciones

### COMP-HEADER `[fijo]`
Logo, nav corta, **teléfono clickeable** y CTA "Reservar". Sticky. Reutilizable: GLOBAL.

### COMP-HERO-FULL `[fijo]`
Objetivo: sitio y promesa en una pantalla (descubrimiento). Fotografía **a sangre de la cabina**, no
un retrato de stock sonriendo: lo que se compra es entrar ahí. Claim corto y CTA a reservar.
**Nunca un formulario aquí** — la captación vive en la banda de cierre.
**Y el titular no lleva adorno.** Hubo una versión con la segunda línea entrada y un filete corto
delante; un lector la tachó con dos palabras —«muy de IA»— y tenía razón: una raya que no significa
nada, puesta donde el ojo espera una palabra. Lo que hace legible a este titular es la MEDIDA, no
un gesto: ancho suficiente para entrar en dos líneas en vez de apilarse en tres cortas.
Mobile: 1 columna, texto sobre la imagen con scrim; **el velo sube** porque el bloque de texto casi
dobla su altura al estrecharse (medido: de ~230px a 439px sobre un hero de 769) y su borde superior
se sale de la parte oscura del degradado. Desktop: texto a un tercio, imagen a sangre.
Reutilizable: SECCIÓN.

### COMP-ZONE-SELECTOR — por dónde `[fijo · ADN] · TGL-ZONE-COUNT`
Objetivo: partir el catálogo por la primera intención real (navegación). Cuatro destinos —rostro,
cuerpo, manos y pies, depilación—, cada uno con el número de rituales que contiene y la horquilla
de precio.
**Va arriba por lo mismo que los filtros de `TPL-C-07` van arriba**: nadie lee una carta entera para
encontrar lo suyo, y la clienta llega sabiendo la parte del cuerpo mucho antes que el nombre
comercial del tratamiento.
**Es TIPOGRÁFICO y no una rejilla de fotos, y esto se decidió mirando.** La primera versión daba una
fotografía a cada zona, y a dos secciones de distancia `COMP-CABIN-TOUR` volvía a enseñar esas
mismas salas: dos rejillas de cuatro imágenes casi iguales, una encima de otra, que es la manera
más rápida de que una página cara parezca una plantilla. Una zona no es un lugar que enseñar sino
un DESTINO al que ir; lo que ayuda a elegirlo es cuántos rituales hay dentro y desde cuánto, no una
foto de un brazo. La fotografía se concentra donde sí es el argumento — la cabina.
Mobile: 2×2. Desktop: fila de 4 con filete entre columnas. Reutilizable: SECCIÓN.

### COMP-RITUAL-MENU — la carta `[fijo · ADN] · TGL-RITUAL-COUNT`
Objetivo: que se pueda decidir sin llamar (decisión). Cada ritual: nombre, una línea de qué es, y
**tres datos en fila — duración, precio y cómo sales**.
**El precio va delante y no es un toggle.** Es el dato que este sector esconde detrás de
"consúltanos" y el que hace que media web sobre. La tercera columna —*cómo sales*— es la que no
tiene ningún hermano del catálogo: «la piel tirante durante una hora», «se puede maquillar encima el
mismo día», «sin sol 48 horas». Es lo que decide si la cita cabe en el jueves de esa persona, y sin
ella la duración y el precio no bastan.
Mobile: 1 columna. Desktop: 2. Reutilizable: SECCIÓN.

### COMP-CABIN-TOUR — la cabina `[fijo · ADN] · TGL-CABIN-FRAMES`
Objetivo: enseñar dónde ocurre (confianza). No es una galería del local: **cada frame lleva el nombre
del espacio y para qué se usa** —cabina de rostro, sala de depilación, zona de manos, recepción—. Un
collage sin pies de foto es decoración; con ellos es una visita previa.
**La sección ES la fila**: dos hijos directos, sin contenedor intermedio — los nombres de los
espacios a un lado y el collage al otro. Las fotos van con **paspartú ancho y sin sombra**: el marco
blanco es el gesto, la sombra contradiría la elevación `none` del ancla.
Mobile: la lista primero y el collage debajo, 2×2. Desktop: 5/7 con el collage escalonado.
Reutilizable: SECCIÓN.

### COMP-PROTOCOL-STEPS — la sesión `[toggle TGL-PROTOCOL-STEPS · default ON]`
Objetivo: quitar la incertidumbre de la primera vez (confianza). La hora contada **en minutos
reales**: diagnóstico de piel 10', limpieza 15', activo 20', masaje y frío 15'. No son cuatro pasos
genéricos de "cómo trabajamos" —eso es `COMP-PROCESS`, y vive en otros arquetipos—: son los minutos
que la clienta va a pasar tumbada, y suman exactamente la duración que la carta prometió.
Mobile: lista vertical con filete a la izquierda. Desktop: fila de 4 con el minutaje grande.
Reutilizable: SECCIÓN.

### COMP-BONO-PACKS — bonos y regalo `[fijo · ADN] · TGL-GIFT-CARD`
Objetivo: convertir una cita en cinco (conversión). **La unidad de compra real de este negocio no es
la sesión, es el bono**: cinco sesiones con su precio cerrado y su ahorro dicho en euros, más la
tarjeta regalo con importe libre. No es `COMP-PRICING` de tres planes con una columna destacada —
aquí no se elige entre niveles de servicio, se elige cuántas veces vuelves.
**Damero a sangre**: paneles de tinta y una fotografía entre ellos, de borde a borde. Los paneles
llevan la superficie inversa del ancla y **no el acento**, que está reservado a lo que se pulsa.
**La tarjeta regalo sale del damero** y va debajo en su propia tira con filete discontinuo: no es un
bono más caro ni más barato, se compra para otra persona, y como cuarta columna se leería como el
escalón bajo de una escalera que no existe.
Mobile: celdas apiladas a ancho completo. Desktop: cuatro columnas.
Reutilizable: SECCIÓN.

### COMP-BOOKING — reserva `[fijo] · TGL-BOOKING`
Día y hora como radios visibles, **más el ritual elegido** — sin él la recepción tiene que llamar
para preguntar lo único que la página ya sabía. Reutilizable: GLOBAL.

### COMP-FOOTER `[fijo]`
NAP completo, horarios reales (incluido el día de cierre), legal. Reutilizable: GLOBAL.

## 4. Toggles admitidos

| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-ZONE-COUNT` | 4 zonas | 3 si el centro no hace depilación |
| `TGL-RITUAL-COUNT` | 6 rituales | menos; nunca más de 8 en la home |
| `TGL-CABIN-FRAMES` | 4 frames | 6 si el local lo merece |
| `TGL-PROTOCOL-STEPS` | on, 4 pasos | off si ningún ritual pasa de 30' |
| `TGL-GIFT-CARD` | on | off si no se vende |
| `TGL-BOOKING` | form | o WhatsApp, o teléfono |

**Fijos:** COMP-HEADER, COMP-HERO-FULL, COMP-ZONE-SELECTOR, COMP-RITUAL-MENU, COMP-CABIN-TOUR, COMP-BONO-PACKS, COMP-BOOKING, COMP-FOOTER.
**Ausencias de ADN:** antes/después fechado, equipo con colegiado, tarifa larga, testimonios → sugerir TPL-C-10 o TPL-C-05.

## 5. SEO / semántica
1 `H1` (hero). `H2` en Zonas, Carta, Cabina, Sesión, Bonos y Reserva. Schema `BeautySalon` (o
`HealthAndBeautyBusiness`) + un `Service` por ritual con su `Offer` y su `duration`, y un `Offer`
sobre el bono con `priceSpecification`. `header` > `main` > `footer`.
**Publicidad:** un centro de estética no puede prometer resultado terapéutico ni usar el lenguaje
clínico que este arquetipo deliberadamente no le da; el copy real lo revisa el cliente, no la maqueta.
