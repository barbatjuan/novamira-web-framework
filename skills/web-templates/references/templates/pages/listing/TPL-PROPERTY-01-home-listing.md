# TPL-PROPERTY-01 — Inmueble (ficha)

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Inmueble (ficha) |
| Objetivo | Que quien vio el inmueble en la cartera sepa cuánto cuesta DE VERDAD y pida verlo |
| Ideal para | Inmobiliarias, promotoras en comercialización, alquiler de larga temporada, locales |
| Ejemplos | "Piso 3 hab. reformado en el Casco · 214.000 €", "Ático con terraza · 41 m² de terraza" |
| Nivel de contenido | Alto y VIVO: la ficha existe mientras el inmueble esté en cartera |
| Protagonismo | EL RECORRIDO por las estancias, EL PLANO y EL COSTE REAL de entrar a vivir |
| ADN | Recorrido estancia por estancia + los seis datos de ficha + plano + desglose de coste (precio, comunidad, IBI, gastos de compra) + certificado energético + visita con la referencia dentro. NO precio "desde", NO carrito, NO perfil de vendedor. |

**Por qué existe habiendo TPL-UNIT-01.** Las dos son la ficha de una unidad de un inventario que
rota, y esa coincidencia es real. Lo que las separa es **qué se compra**. Un coche se compra por su
historia —de dónde viene, cuánto ha andado, quién lo tuvo— y por eso su sección central es el
historial. Un piso se compra por **su forma y su coste de mantenerlo**: nadie pregunta cuántos
dueños tuvo, todos preguntan cuánto paga de comunidad y si el salón da al norte. De ahí el plano,
que en un coche no significa nada, y el desglose de gastos, que en un coche es la cuota.

**Por qué existe habiendo TPL-C-13.** `TPL-C-13` es la CARTERA: buscador en la primera pantalla y
resultados en rejilla o sobre plano. Enseña precio, metros y zona porque es lo que cabe en una
tarjeta. Esta página es lo que hay detrás de la tarjeta, y su trabajo es que la visita presencial
no se gaste en descubrir algo que estaba en la web: la orientación, la planta, si hay ascensor.

**El desglose de coste es la sección que nadie publica.** Precio, comunidad al mes, IBI al año y
gastos de compra estimados —notaría, registro, ITP o IVA—, sumados. Un piso de 214.000 € cuesta
entrar en él unos 235.000, y quien descubre esos 21.000 en la notaría no vuelve a esa agencia. Es
la misma decisión que en `TPL-UNIT-01` obliga a publicar contado y cuota juntos: **el número de la
web tiene que ser el número del despacho.**

## 2. Wireframe (top → bottom)

```
COMP-HEADER [fijo]
COMP-BREADCRUMB (Inicio · Cartera · <referencia>) [fijo]
COMP-PROPERTY-TOUR (estancia por estancia, no una galería suelta) [fijo · ADN]
COMP-PROPERTY-FACTS (útiles/construidos · habitaciones · baños · planta · ascensor · año) [fijo · ADN]
COMP-FLOORPLAN (el plano, ampliable) [fijo · ADN]
COMP-COSTS-BREAKDOWN (precio + comunidad + IBI + gastos de compra) [fijo · ADN]
COMP-ENERGY-LABEL (certificado energético con su letra y su consumo) [fijo · ADN]
COMP-ZONE-MAP (el barrio: transporte, colegios, mercado) [toggle · default ON]
COMP-VISIT-REQUEST (visita con la referencia dentro) [fijo · ADN]
COMP-RELATED (de su misma zona y rango) [fijo]
COMP-FOOTER [fijo]
```

Ausencia intencional: sin carrito, sin precio "desde", sin perfil ni valoración de vendedor. Una
inmobiliaria publica SU cartera y responde ella — la corrección que `TPL-C-13` ya documenta frente
a un portal tipo marketplace.

## 3. Secciones

### COMP-HEADER `[fijo]`
Reutilizable: GLOBAL.

### COMP-BREADCRUMB `[fijo]`
`Inicio · Cartera · <referencia>`, y el eslabón del medio vuelve al buscador **con la zona que
traía**. La referencia va en el breadcrumb porque es como el usuario la nombra por teléfono.
Reutilizable: GLOBAL. Schema `BreadcrumbList`.

### COMP-PROPERTY-TOUR — el recorrido `[fijo · ADN]`
Objetivo: que se pueda recorrer la casa sin estar dentro (confianza). Las fotos **con el nombre de
la estancia y su medida**: salón 24 m², cocina 11 m², dormitorio principal 14 m².
**Por eso no es `COMP-GALLERY`.** Una galería es un carrusel de imágenes bonitas en orden
arbitrario; un recorrido tiene el orden de la puerta hacia dentro y cada foto dice qué estás
mirando y cuánto mide. La diferencia se nota en la visita: quien recorrió la ficha llega sabiendo
si su sofá cabe.
Mobile: 1 columna, pie bajo cada foto. Desktop: 2 columnas, la del salón a doble ancho.
Reutilizable: SECCIÓN.

### COMP-PROPERTY-FACTS — los seis datos `[fijo · ADN]`
Objetivo: descartar o seguir en cinco segundos (decisión). **Metros útiles y construidos** —los
dos, y esto no es opcional: publicar sólo construidos infla el piso un 15 % y es la queja número
uno del sector—, **habitaciones**, **baños**, **planta**, **ascensor** y **año de construcción**.
Mobile: lista de 6 filas. Desktop: rejilla de 6. Reutilizable: SECCIÓN.

### COMP-FLOORPLAN — el plano `[fijo · ADN]`
Objetivo: contestar la pregunta que ninguna foto contesta: cómo se reparte (decisión). El plano con
las estancias rotuladas, ampliable.
**Es FIJO y no un toggle.** Un piso sin plano obliga a la visita para saber si el segundo dormitorio
es un dormitorio o un trastero, y esa visita se la come la agencia. Si no hay plano, se dibuja: es
más barato que cuatro visitas perdidas.
Mobile: ancho completo, pellizcable. Desktop: a media caja junto a la ficha de datos.
Reutilizable: SECCIÓN.

### COMP-COSTS-BREAKDOWN — lo que cuesta de verdad `[fijo · ADN]`
Objetivo: que el coste de entrar a vivir esté en la web y no en la notaría (confianza). **Precio**,
**comunidad al mes**, **IBI al año** y **gastos de compra estimados**, con la suma escrita.
Mobile: lista con la suma destacada abajo. Desktop: tabla de dos columnas con filete en el total.
Reutilizable: SECCIÓN.
**Aviso:** los gastos de compra dependen de comunidad autónoma y de si es primera o segunda
transmisión. La maqueta pone la estructura y la etiqueta «estimado»; los números los firma la
agencia.

### COMP-ENERGY-LABEL — el certificado `[fijo · ADN]`
Objetivo: cumplir y, de paso, informar (confianza y obligación legal). La **letra** de consumo y la
de emisiones, con sus kWh/m²·año.
**Es FIJO por ley, no por diseño.** En España un anuncio de venta o alquiler tiene que publicar la
calificación energética; un toggle aquí sería un interruptor para incumplir.
Mobile: dos etiquetas apiladas. Desktop: en fila junto al desglose. Reutilizable: SECCIÓN.

### COMP-ZONE-MAP — el barrio `[toggle TGL-ZONE · default ON]`
Objetivo: vender el entorno, que en inmobiliaria es media compra (decisión). El punto en el plano y
lo que hay alrededor: transporte, colegios, mercado, salida a la autovía.
**No es `COMP-MAP-NAP`**, que dice dónde está el NEGOCIO y a qué hora abre. Aquí el negocio no
importa: importa a qué distancia está la parada. Off en obra nueva sin dirección firme.
Reutilizable: SECCIÓN.

### COMP-VISIT-REQUEST — la visita `[fijo · ADN]`
Objetivo: llenar la agenda del comercial (conversión). Día, franja y **la referencia del inmueble
ya dentro del formulario**, más la opción de visita en vídeo.
**Una solicitud sin referencia es una consulta genérica**, que es exactamente lo que la agencia no
puede atender — lo dice `TPL-C-13` en su propia identidad, y esta página es donde se cumple.
Mobile: 1 columna. Desktop: texto | formulario. Reutilizable: SECCIÓN.

### COMP-RELATED — de su zona `[fijo]`
Objetivo: que un «no» sobre este piso no sea un «no» sobre la agencia (navegación). Tres de la
misma zona y rango, más «ver toda la cartera». FIJO por la misma razón que en `TPL-UNIT-01`: en un
inventario que rota, una ficha sin salidas queda huérfana el día que se vende.
Reutilizable: GLOBAL.

### COMP-FOOTER `[fijo]`
Reutilizable: GLOBAL.

## 4. Toggles admitidos

| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-ZONE` | on | off en obra nueva sin dirección firme |
| `TGL-VIDEO-VISIT` | on | la visita en vídeo como segunda opción del formulario |
| `TGL-RELATED` | on | |

**Fijos:** COMP-HEADER, COMP-BREADCRUMB, COMP-PROPERTY-TOUR, COMP-PROPERTY-FACTS, COMP-FLOORPLAN, COMP-COSTS-BREAKDOWN, COMP-ENERGY-LABEL, COMP-VISIT-REQUEST, COMP-RELATED, COMP-FOOTER.
**Ausencias de ADN:** carrito, precio "desde", perfil de vendedor → `TPL-PDP-01` o `TPL-C-13`.

## 5. Multiplicación y contenido único
**Una por inmueble**, y como en `TPL-UNIT-01` la multiplicación es el modelo. Lo que no puede
repetirse: las fotos, el plano y el desglose. Una agencia que reutiliza el mismo texto de barrio en
las cuarenta fichas tiene cuarenta páginas compitiendo por «piso en el Casco» y ninguna gana. La
ficha **caduca**: vendido o alquilado, o se retira o se marca como tal manteniendo el enlace —
mantener comprable lo que ya no está es la llamada que quema al comercial y al cliente.

## 6. SEO / semántica
1 `H1` con tipo, habitaciones, zona y precio — es como se busca. `H2` en Recorrido, Ficha, Plano,
Coste, Energía, Zona, Visita y Parecidos. Schema `RealEstateListing` con `floorSize` en metros
útiles, `numberOfRooms`, `numberOfBathroomsTotal` y `offers`; `Residence` para el inmueble y
`RealEstateAgent` para la agencia. `header` > `main` > `footer`. **La calificación energética va en
el HTML y no sólo en una imagen**: un dato obligatorio dentro de un JPG no lo lee ni un lector de
pantalla ni un buscador.
