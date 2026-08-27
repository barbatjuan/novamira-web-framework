# TPL-C-15 — Cartera curada

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Cartera curada |
| Objetivo | Que quien busca una vivienda de alto valor confíe en la agencia antes de comparar cuarenta fichas, y pida ver UNA propiedad concreta o una tasación |
| Ideal para | Inmobiliarias de lujo residencial, promotoras boutique, agencias con cartera reducida y curada (villas, áticos, fincas de autor, obra nueva de gama alta) |
| Ejemplos | Agencia de villas en Marbella / Costa del Sol, boutique inmobiliaria de una sola zona prime, promotora de áticos de autor |
| Nivel de contenido | Medio-alto y CURADO, no volátil: una veintena de inmuebles únicos, cada uno tratado como pieza, no un stock que rota cada semana |
| Protagonismo | LA PROPIEDAD como objeto deseable —manda la fotografía— y LA AGENCIA como curadora de confianza; el buscador existe pero no es la portada |
| ADN | Hero de persuasión con retícula de marca + buscador de cuatro campos debajo, nunca encima + selección curada con precio, m² y zona en cada ficha + CTA de captación del propietario + prueba social. NO buscador-como-portada por defecto, NO carrito, NO contador de resultados en la portada |

**Por qué es un arquetipo nuevo, y no `TPL-C-13` con otra piel.** `TPL-C-13 · Cartera / Búsqueda`
(retirado en esta misma iniciativa) compartía con este arquetipo seis puntos de ADN: buscador de
hasta cuatro campos, cartera en rejilla, ficha con precio/m²/zona, solicitud de visita que viaja con
la referencia del inmueble, CTA de captación del propietario —que aquella plantilla llamaba
`COMP-VALUATION-CTA` y este diseño rotula «VALORAR MI CASA»— y ningún carrito. Con ese parecido, la
pregunta obligada es por qué no basta con darle a `TPL-C-13` una tabla `Envoltorio` y una demo nueva.

Uno de los dos puntos en que diverge NO es una divergencia real. `COMP-MAP-SEARCH` es un conmutador
con un estado `off` que `TPL-C-13` ya documentaba —«en carteras de una sola zona, donde el plano no
discrimina nada»— y una cartera de diecisiete propiedades en Sierra Blanca es exactamente esa
cartera de una sola zona.

El otro punto SÍ es estructural. `TPL-C-13` lo escribe en su propio documento: «el buscador ES la
portada… una imagen de fondo contenida —no a sangre de 80vh— con el formulario encima» y «todo lo
que se ponga por encima de él es retraso». El diseño de origen abre con un héroe a sangre de 78vh y
baja el buscador a una banda propia, separada, debajo. Eso no es una variación de matiz sobre el
mismo objetivo: es el objetivo contrario. Forzar `TPL-C-13` a esa composición es deformarlo, y
`web-templates/SKILL.md` existe precisamente para enrutar a otro sitio en vez de deformar uno que ya
funciona.

La causa de fondo: `TPL-C-13` sirve a quien llega FILTRANDO —sus propias fotografías eran un
adosado sin reformar, una planta baja, un estudio, un piso de tres habitaciones: volumen urbano
donde filtrar por zona y precio es la primera intención real. `TPL-C-15` sirve a quien llega siendo
CONVENCIDO —diecisiete villas y áticos de 642 m² y 6 M€+ en Sierra Blanca, donde la fotografía vende
antes de que el usuario toque un filtro. Son dos maneras de resolver «cartera de inmuebles» tan
distintas como `TPL-C-11` y `TPL-C-14` resuelven «servicio con cita» de maneras distintas: el eje
que las separa no es el sector, es la ACTITUD con la que se llega.

`TPL-C-15` nace con `TGL-HERO-MODE` (`buscador-portada` | `retrato`, por defecto `retrato`)
precisamente para no perder la ruta de `TPL-C-13`: un brief de volumen urbano que hoy encajaría en
el arquetipo retirado elige `buscador-portada` y recupera esa composición exacta, sin que este
documento tenga que declarar dos arquetipos. `TGL-MAP-MODE` se hereda con el mismo vocabulario, sólo
que aquí el valor por defecto es `off` en vez de `conmutador`. `TPL-PROPERTY-01`, la ficha de
inmueble, sobrevive sin cambios y es la pareja de este arquetipo tal como lo era de `TPL-C-13`.

## 2. Wireframe (top → bottom)

```
COMP-HEADER (teléfono + CTA secundario "Valorar mi casa") [fijo]
COMP-HERO-CARTERA (persuasión: la propiedad, no el buscador) [fijo · ADN] · TGL-HERO-MODE
COMP-SEARCH-BAND (operación · zona · tipo · precio, máximo cuatro campos) [fijo · ADN]
COMP-FEATURED-GRID (selección curada, no la cartera entera) [fijo · ADN] · TGL-FEATURED-COUNT
COMP-MAP-SEARCH (los mismos resultados sobre plano) [toggle TGL-MAP-MODE · default off]
COMP-VALUATION-CTA (¿vendes? captación del propietario) [fijo · ADN]
COMP-TESTIMONIAL (valoración agregada + citas) [toggle TGL-REVIEWS · default on]
COMP-FOOTER (NAP + número de registro) [fijo]
```

Ausencia intencional: sin equipo en portada (vive en `TPL-ABOUT-*`, donde SÍ hace falta poner cara a
la agencia), sin solicitud de visita en portada (vive en la ficha `TPL-PROPERTY-01`, donde la
referencia del inmueble ya llega rellena), sin contador de resultados tipo «34 inmuebles» (esta
portada enseña TRES fichas curadas, no la cartera entera — un contador junto a tres tarjetas no
informa, presume; pertenece a la página de listado completo, fuera del alcance de este piloto), y
sin carrito de ninguna clase.

**Y la lista de secciones es sólo la mitad del arquetipo.** La otra mitad es la FORMA de cada una, y
es precisamente lo que a `TPL-C-13` le faltaba declarar cuando esta iniciativa empezó a medir el
catálogo: sus veintidós vecinas envolvían cada sección igual —contenedor centrado, encabezado,
lista— y dos arquetipos con inventarios distintos se leían como la misma página con otra paleta.
Aquí, como en `TPL-C-14`, cada sección pide la suya, y la columna de abajo es parte del contrato:

| Sección | Envoltorio | Por qué |
|---------|-----------|---------|
| `COMP-HERO-CARTERA` | banda a sangre (en `retrato`, su valor por defecto) | La fotografía llega a los bordes de la ventana con la retícula de cuatro columnas superpuesta — es la persuasión que a `TPL-C-13` le costaba: una casa que se vende por cómo se ve, no por cuántos filtros tiene delante |
| `COMP-SEARCH-BAND` | contenido — bloque de superficie inversa del ancla, del ancho del contenedor de página, sin tocar el borde de la ventana | A diferencia de una sección a sangre, este bloque comparte ancho con el resto del contenido: es una herramienta debajo de la persuasión, no la persuasión misma |
| `COMP-FEATURED-GRID` | contenido | Rejilla de tarjetas dentro del contenedor de página, igual que `COMP-STOCK-GRID` en `TPL-C-07`: una selección se lee, no se navega a sangre |
| `COMP-MAP-SEARCH` | banda a sangre cuando el conmutador está en `on` | Heredado tal cual de `TPL-C-13`: un modo de búsqueda metido en una columna de contenido se lee como una captura de pantalla, no como un mapa que se recorre |
| `COMP-VALUATION-CTA` | la sección ES la fila | Panel de captación y fotografía como hijos directos, sin contenedor intermedio, separados por el mismo `gap:1px` que actúa de filete en el resto de rejillas del diseño de origen — el mismo damero que `TPL-C-14` usa en `COMP-BONO-PACKS`, aplicado aquí a la otra conversión |
| `COMP-TESTIMONIAL` | contenido | Cabecera y citas dentro del contenedor de página, con un filete superior que abre la sección; la prueba social no necesita sangre para funcionar |

## 3. Secciones

### COMP-HEADER `[fijo]`
Logo en dos líneas (nombre en serif + línea de posicionamiento en versalitas de acento) + nav de
tres enlaces + teléfono clickeable + botón secundario **"Valorar mi casa"**. El botón de captación es
secundario y no primario: comparte cabecera con el teléfono pero nunca compite con él en peso —la
misma regla que `TPL-C-13` ya declaraba para su propio "Vender tu casa". Sticky. Reutilizable:
GLOBAL.

### COMP-HERO-CARTERA — la casa antes que el buscador `[fijo · ADN] · TGL-HERO-MODE`
Objetivo: sitio y promesa en una pantalla (descubrimiento), y aquí la promesa es una fotografía, no
un formulario. En `retrato` (por defecto): `min-height:78vh`, contenido alineado abajo. Capas en
orden: fotografía a sangre → velo en degradado que sube de transparente a opaco hacia el lado del
texto → retícula de cuatro columnas superpuesta (`z-index:2`, `pointer-events:none`, sólo filetes,
decoración y no contenedor) → contenido. El contenido reparte antetítulo + H1 a dos líneas + párrafo
a un lado, y al otro un panel de cifras (dos datos, valor grande + etiqueta) sobre un fondo casi
opaco con filete NEUTRO a la izquierda (blanco translúcido, no `--c-accent`: un filete decorativo
no es CTA, icono de acción, enlace importante ni estado activo, y el generador lo `fail()`ea si
pinta con acento fuera de esos cuatro roles) — la casa se afirma con números que la agencia puede
sostener, no con un claim vacío.
**Nunca lleva el formulario encima en este modo**: eso es lo que distinguía a `TPL-C-13`, y aquí
sigue vivo como el otro extremo del toggle.
`TGL-HERO-MODE = buscador-portada`: la fotografía deja de ser a sangre —contenida, sin la retícula
superpuesta— y el buscador (`COMP-SEARCH-BAND`) se dibuja ENCIMA de ella, centrado, exactamente como
`TPL-C-13` lo declaraba. En este modo `COMP-SEARCH-BAND` no se repite como sección aparte más abajo.
Mobile: pendiente de definir a nivel de píxel (el handoff marca el responsive global como «pendiente
de definir»); recomendación mínima: apilar en una columna, ocultar la retícula (deja de leerse a ese
ancho) y bajar el panel de cifras debajo del texto. Reutilizable: SECCIÓN.

### COMP-SEARCH-BAND — la herramienta, no la portada `[fijo · ADN]`
Objetivo: que la primera acción posible sea buscar (intención), sin que eso implique que el buscador
ES la portada. Bloque de superficie inversa del ancla, del ancho del contenedor, con cuatro campos
en fila —operación, zona o municipio, tipo, precio— cada uno con etiqueta corta y control
transparente, separados por un filete vertical, y un botón de acento al final.
**Cuatro campos como máximo, herencia directa de `TPL-C-13`**: el quinto es el que hace que se
abandone. Reutilizable: SECCIÓN.
Mobile: campos apilados a ancho completo, botón al final.

### COMP-FEATURED-GRID — selección, no cartera entera `[fijo · ADN] · TGL-FEATURED-COUNT`
Objetivo: dar prueba de que la cartera tiene lo que se busca sin enseñarla entera (persuasión antes
que comparación). Cabecera de sección con antetítulo, H2 y un enlace de salida ("ver la cartera
completa") con filete inferior. Rejilla de tarjetas: foto en proporción vertical con un badge de
estado pegado a la esquina (sin radio, sin sombra), zona en acento, título en serif, fila de
metadatos (m², habitaciones, baños) y precio en serif.
**No lleva contador de resultados.** `TPL-C-13` sí lo llevaba porque su rejilla ERA la cartera
entera; aquí la rejilla es una muestra, y poner "3 de 17" junto a tres tarjetas no informa, presume.
Reutilizable: SECCIÓN.
Mobile: 1 columna. Desktop: 3.

### COMP-MAP-SEARCH — heredado, apagado por defecto `[toggle TGL-MAP-MODE · default off]`
Objetivo: buscar por dónde está y no por cómo se llama (geografía) — idéntico a `TPL-C-13`, del que
se hereda función y vocabulario sin cambiar una línea de comportamiento, sólo el valor por defecto.
Los mismos resultados de `COMP-FEATURED-GRID` (o de la cartera completa, cuando exista esa página)
sobre un plano, con chincheta y precio dentro de cada una. Se enciende cuando la cartera cubre varias
zonas que de verdad discriminan; en un piloto de una sola zona —Sierra Blanca, en el diseño de
origen— se queda apagado. Reutilizable: SECCIÓN.

### COMP-VALUATION-CTA — la otra conversión `[fijo · ADN]`
Objetivo: que el propietario pida ver qué puede sacar por su casa (conversión, la otra), sin robarle
la portada al comprador — el mismo argumento que ya usaba `TPL-C-13`, que la ponía al final por eso.
Panel de superficie inversa del ancla con antetítulo, H2, párrafo y botón de acento ("Valorar mi
casa") más un enlace secundario, junto a una fotografía de al menos 420px de alto.
**A diferencia de `TPL-C-13`, que embebía un formulario corto de tasación en la propia banda**, aquí
el botón lleva al formulario de Contacto con el motivo "Valoración de mi propiedad" ya
preseleccionado: el diseño de origen no repite el patrón de formulario embebido en esta sección, y
añadir uno que el handoff no pide sería inventar un campo. **Nunca promete un precio en la página** —
la tasación es presencial. Reutilizable: SECCIÓN.
Mobile: panel y fotografía apilados.

### COMP-TESTIMONIAL — prueba social cuantificada `[toggle TGL-REVIEWS · default on]`
Objetivo: que la cifra de confianza llegue antes que la decisión de escribir (confianza) — sección
sin precedente en `TPL-C-13`, que no la llevaba, y que aquí se añade porque un comprador de 6 M€+
pide más prueba que un comprador de un piso de tres habitaciones. Cabecera con H2 a dos líneas y la
valoración agregada ("4,9 / 5 · 68 reseñas verificadas"); tres citas en serif con autor y el tipo de
operación que cerraron. Reutilizable: SECCIÓN.
Mobile: citas apiladas.

### COMP-FOOTER `[fijo]`
Cuatro columnas: marca + descripción + sello de registro en acento; dos columnas de enlaces; datos
de contacto (dirección, email, teléfono). Barra inferior con copyright y enlaces legales.
**Hereda de `TPL-C-13` el número de registro de agente inmobiliario** donde la comunidad autónoma lo
exija — sigue siendo ley, no diseño. Reutilizable: GLOBAL.

## 4. Toggles admitidos

| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-HERO-MODE` | `retrato` | `buscador-portada` recupera la composición de `TPL-C-13` (imagen contenida, formulario encima) para el brief de volumen urbano que este arquetipo también puede cubrir |
| `TGL-FEATURED-COUNT` | 3 tarjetas | hasta 6 si la cartera es lo bastante grande para no repetir zona en dos tarjetas seguidas |
| `TGL-MAP-MODE` | `off` | `conmutador` / `sección` / `off`, mismo vocabulario que `TPL-C-13` — aquí por defecto `off`: una cartera de una sola zona no gana nada con un plano que no discrimina nada |
| `TGL-REVIEWS` | on | off si la agencia no tiene aún reseñas verificadas que mostrar — un contador en cero resta más de lo que suma |

**Fijos:** COMP-HEADER, COMP-SEARCH-BAND, COMP-FEATURED-GRID, COMP-VALUATION-CTA, COMP-FOOTER.
**Ausencias de ADN:** buscador-como-portada por defecto, contador total de resultados en la portada,
equipo en portada, visita concertada en portada → viven en `TGL-HERO-MODE=buscador-portada`, en la
ficha (`TPL-PROPERTY-01`), o en Nosotros/Contacto según corresponda.

## 5. SEO / semántica
1 `H1` (la promesa de la cartera —Marbella / Costa del Sol, villas y áticos—, no el nombre de la
agencia). `H2` en Buscador, Selección destacada, Valoración de la propiedad y Reseñas. Schema
`RealEstateAgent` a nivel de organización, con `areaServed` fijando la zona geográfica de la cartera,
y `AggregateRating` sobre la valoración agregada de las reseñas. Cada ficha de inmueble
(`TPL-PROPERTY-01`) declara su propio `Offer` con `price`/`priceCurrency` sobre el tipo de inmueble
que corresponda (`House`, `Apartment`…) — esta home no repite ese detalle, sólo lo enlaza.
`header` > `main` > `footer`.
