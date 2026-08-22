# TPL-UNIT-01 — Unidad de ocasión (ficha)

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Unidad de ocasión (ficha) |
| Objetivo | Que quien ya vio la unidad en la rejilla sepa si ESA vale la pena y venga a probarla |
| Ideal para | Vehículo de ocasión, maquinaria, náutica, caravanas, agrícola de segunda mano |
| Ejemplos | "Volkswagen T-Roc 1.5 TSI 2021 · 48.200 km", "Tractor Deutz 2016 · 3.100 h" |
| Nivel de contenido | Alto y EFÍMERO: la ficha muere el día que la unidad se vende |
| Protagonismo | LA UNIDAD CONCRETA — su historia, su precio y su cuota — no el modelo |
| ADN | Fotos de ESTA unidad + los seis datos de ficha + precio al contado Y cuota + historial verificable (propietarios, libro, ITV, garantía) + tasación del tuyo como entrada + reserva de prueba con la referencia dentro. NO variantes, NO carrito, NO "desde". |

**Por qué existe habiendo TPL-PDP-01.** Una ficha de producto vende una unidad **fungible**: hay
cien iguales en el almacén, y por eso su página gira alrededor de elegir variante y añadir al
carrito. Aquí hay UNA, con 48.200 km que nadie más tiene, un arañazo en el paragolpes trasero y un
libro de mantenimiento que existe o no existe. No se elige talla: se decide si te fías. Por eso el
componente central no es el selector de variante sino el **historial**, y la conversión no es un
carrito sino una cita para verlo con tus ojos.

**Por qué existe habiendo TPL-C-07.** `TPL-C-07` es el INVENTARIO: filtros arriba y cuarenta
unidades en rejilla, y su trabajo termina cuando el usuario señala una. Esta página empieza ahí. La
rejilla enseña cinco datos por unidad porque cinco es lo que cabe en una tarjeta; la decisión de
comprar un usado necesita quince, y catorce de ellos no caben en ningún sitio de la home.

**El precio se publica DOS VECES y eso es deliberado.** Al contado y en cuota, en la misma sección
y con la misma jerarquía. Un patio que sólo publica cuota esconde el precio, y uno que sólo publica
contado pierde al que compra por mensualidad — que en ocasión es la mayoría. Publicar los dos
juntos, con entrada, plazo y TAE al lado, es lo que evita que el número de la web y el número del
despacho sean distintos.

## 2. Wireframe (top → bottom)

```
COMP-HEADER (con teléfono del patio) [fijo]
COMP-BREADCRUMB (Inicio · Stock · <esta unidad>) [fijo]
COMP-UNIT-GALLERY (fotos de ESTA unidad, los golpes incluidos) [fijo · ADN]
COMP-UNIT-FACTS (año · km · combustible · cambio · potencia · plazas) [fijo · ADN]
COMP-PRICE-FINANCE (contado y cuota, con entrada, plazo y TAE) [fijo · ADN]
COMP-HISTORY-REPORT (propietarios · libro · última ITV · garantía que lleva) [fijo · ADN]
COMP-TRADE-IN (tu coche como entrada de ESTE) [toggle · default ON]
COMP-TEST-DRIVE (reservar la prueba, con la referencia dentro) [fijo · ADN]
COMP-RELATED (parecidas del patio) [fijo]
COMP-FOOTER [fijo]
```

Ausencia intencional: sin selector de variante, sin carrito, sin precio "desde", sin catálogo de
marcas. Quien llega aquí ya eligió; ofrecerle alternativas antes de la prueba es devolverlo a la
rejilla de la que acaba de salir.

## 3. Secciones

### COMP-HEADER `[fijo]`
El teléfono del patio, visible. En ocasión la llamada gana al formulario. Reutilizable: GLOBAL.

### COMP-BREADCRUMB `[fijo]`
`Inicio · Stock · <esta unidad>`, y el eslabón del medio vuelve a la rejilla **con los filtros que
traía**. Reutilizable: GLOBAL. Schema `BreadcrumbList`.

### COMP-UNIT-GALLERY — la unidad `[fijo · ADN]`
Objetivo: que la foto sea de ESTE coche y no del catálogo del fabricante (confianza). Cuatro o
cinco tomas: tres cuartos delantero, interior desde la puerta, salpicadero con el cuentakilómetros
**legible**, y maletero o detalle.
**El cuentakilómetros en foto no es un capricho.** Es el único dato de la ficha que el comprador
puede verificar sin bajarse del sofá, y publicarlo separa a un patio de un anuncio.
Y si la unidad tiene un golpe, va fotografiado. Un desperfecto que aparece en el patio y no en la
web cuesta la venta entera y la reseña.
Mobile: una principal + tira de miniaturas. Desktop: principal grande + miniaturas en columna.
Reutilizable: SECCIÓN.

### COMP-UNIT-FACTS — los seis datos `[fijo · ADN]`
Objetivo: cerrar de un vistazo si esta unidad entra en lo que buscabas (decisión). **Año**,
**kilómetros**, **combustible**, **cambio**, **potencia** y **plazas**. Los seis, siempre. Es el
mismo juego que la tarjeta de la rejilla más potencia y plazas, y se repite a propósito: quien
llega desde un buscador externo no ha visto la tarjeta.
Mobile: lista de 6 filas. Desktop: rejilla de 6 con el dato grande y el rótulo pequeño.
Reutilizable: SECCIÓN.

### COMP-PRICE-FINANCE — los dos precios `[fijo · ADN]`
Objetivo: que el número de la web sea el número del despacho (conversión y confianza). Precio al
contado y cuota mensual con **entrada, plazo y TAE** escritos al lado, no en una nota al pie.
Mobile: contado arriba, cuota debajo, condiciones en lista. Desktop: dos columnas del mismo peso.
Reutilizable: SECCIÓN (comparte la caja de cuota con `COMP-FINANCE` de `TPL-C-07`, con la cifra de
ESTA unidad dentro).
**Aviso:** la TAE y las condiciones las firma la financiera. La maqueta pone los huecos.

### COMP-HISTORY-REPORT — el historial `[fijo · ADN]`
Objetivo: sustituir la desconfianza por hechos comprobables (confianza). **Número de propietarios**,
**libro de mantenimiento** (sí/no y dónde se selló), **última ITV** con fecha y resultado, y
**garantía que lleva** con sus meses.
**Ésta es la sección que justifica la página.** Sin ella la ficha es la tarjeta de la rejilla con
las fotos más grandes, y la pregunta que trae a alguien al patio —«¿de qué viene este coche?»—
sigue sin respuesta.
Mobile: 4 filas con etiqueta y valor. Desktop: 2×2 con filete. Reutilizable: SECCIÓN.

### COMP-TRADE-IN `[toggle TGL-TRADE-IN · default ON]`
Objetivo: convertir «no me llega» en una entrada (conversión). El tuyo tasado **como entrada de
ésta**, no como una tasación suelta: la diferencia es que el resultado se resta del precio que el
usuario está mirando. Off en maquinaria y náutica, donde la permuta es excepcional.
Reutilizable: SECCIÓN (compartida con `TPL-C-07`).

### COMP-TEST-DRIVE — la prueba `[fijo · ADN]`
Objetivo: sacar al usuario de la pantalla y meterlo en el coche (conversión). Día, franja y **la
referencia de la unidad ya dentro del formulario**.
**Una solicitud de prueba sin referencia es una consulta genérica**, y el patio no puede reservar
un coche que no sabe cuál es — el mismo defecto que `TPL-C-13` documenta para la visita a un piso.
Mobile: 1 columna. Desktop: texto | formulario. Reutilizable: SECCIÓN.

### COMP-RELATED — parecidas `[fijo]`
Objetivo: no dejar la ficha colgando (navegación). Tres unidades del patio en su mismo rango de
precio, más «ver todo el stock». Es FIJO: en un inventario que rota, una ficha sin salidas se queda
huérfana el día que la unidad se vende. Misma receta de tarjeta que la rejilla.
Reutilizable: GLOBAL.

### COMP-FOOTER `[fijo]`
Reutilizable: GLOBAL.

## 4. Toggles admitidos

| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-TRADE-IN` | on | off en maquinaria y náutica |
| `TGL-UNIT-FINANCE` | on | off si el patio no financia; entonces sólo contado |
| `TGL-RELATED` | on | |

**Fijos:** COMP-HEADER, COMP-BREADCRUMB, COMP-UNIT-GALLERY, COMP-UNIT-FACTS, COMP-PRICE-FINANCE, COMP-HISTORY-REPORT, COMP-TEST-DRIVE, COMP-RELATED, COMP-FOOTER.
**Ausencias de ADN:** variantes, carrito, precio "desde", catálogo de marcas → `TPL-PDP-01` o `TPL-C-07`.

## 5. Multiplicación y contenido único
**Una por unidad**, y aquí la multiplicación no es un riesgo sino el modelo: cuarenta unidades son
cuarenta fichas. Lo que **no** puede repetirse son las fotos y el historial. Dos fichas con las
mismas fotos del catálogo del fabricante y el mismo «un propietario, libro al día» son dos anuncios
que nadie se cree, y el buscador las trata como contenido duplicado con razón. La ficha **caduca**:
cuando la unidad se vende, o se retira con un 410 o se marca como vendida y mantiene el enlace.
Dejarla viva y comprable es la reseña de una estrella.

## 6. SEO / semántica
1 `H1` con marca, modelo, versión, año y kilómetros — es como se busca. `H2` en Ficha, Precio,
Historial, Tasación, Prueba y Parecidas. Schema `Vehicle` con `mileageFromOdometer`,
`vehicleTransmission`, `fuelType`, `numberOfPreviousOwners` y `offers` con el precio al contado;
`seller` al `AutoDealer` de la home. `header` > `main` > `footer`. **La cuota no va en `offers`**:
el precio de la oferta es el contado, y publicar la mensualidad como precio es lo que la normativa
de crédito al consumo persigue.
