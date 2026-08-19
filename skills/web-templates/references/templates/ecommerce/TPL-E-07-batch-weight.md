# TPL-E-07 — Lote / Peso

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Lote / Peso |
| Objetivo | Que el cliente compre producto fresco sabiendo de dónde viene, cuánto pesa y qué día llega |
| Ecommerce ideal | Carnicería y pescadería online, quesos, embutido, café de tueste, bodega, huerta |
| Ejemplos | Chuletón madurado, atún de almadraba, queso de cuchara, café recién tostado, fruta de temporada |
| Nivel de contenido | Medio y PERECEDERO: el lote de esta semana no es el de la próxima |
| Protagonismo del producto | Alto — pero lo que se enseña es la pieza concreta, no una foto de catálogo |
| Protagonismo de la marca | Medio |
| ADN | Ventana de entrega arriba + fichas de lote con origen, fecha y precio por kg + el aviso de peso variable + cómo viaja en frío. NO countdown, NO rejilla de categorías, NO lookbook. |

**Por qué existe habiendo TPL-E-02 y TPL-E-03.** TPL-E-02 publica un catálogo grande donde el
producto es idéntico a sí mismo: un cargador de móvil es el mismo cargador en marzo y en octubre.
TPL-E-03 vende la historia de la marca antes que el producto. Aquí el producto **cambia cada
semana y no es intercambiable consigo mismo**: la pieza tiene un lote, una fecha y un peso que no
es exacto hasta que alguien la pone en la báscula.

Eso rompe tres supuestos que los otros arquetipos dan por hechos y que ninguno de sus bloques
resuelve:

1. **El precio no es un número, es un número por kilo.** La ficha dice "34 €/kg · pieza de ~1,2 kg
   ≈ 41 €", y el importe final se ajusta al pesar. Un `COMP-PRODUCT-CARD` normal con un PVP fijo
   miente en el 100 % de los pedidos.
2. **La entrega es parte del producto.** Fresco que llega un viernes por la tarde a una casa vacía
   es fresco perdido. Por eso la ventana de entrega va ARRIBA, antes que el catálogo: si no
   servimos su código postal el martes, no hay compra que hacer y averiguarlo al final del
   checkout es la peor forma de saberlo.
3. **La trazabilidad ES el argumento de venta.** Origen, lote y consumo preferente no son letra
   pequeña legal aquí; son exactamente lo que separa esta tienda del lineal del supermercado.

## 2. Wireframe (top → bottom)

```
COMP-HEADER (con carrito icono+badge) [fijo]
COMP-DELIVERY-WINDOW (código postal → qué días entregamos ahí) [fijo · ADN]
COMP-BATCH-CARD (pieza, origen, lote, consumo preferente, €/kg) [fijo · ADN]
COMP-WEIGHT-NOTE (por qué el importe final varía, y cuánto) [fijo · ADN]
COMP-ORIGIN-MAP (de qué explotación o lonja viene) [toggle]
COMP-COLD-CHAIN (cómo viaja y qué hacer al recibirlo) [fijo]
COMP-TESTIMONIAL (quién ya compró) [toggle]
COMP-NEWSLETTER (avisar cuando entre el lote nuevo) [toggle]
COMP-FOOTER [fijo]
```

## 3. Secciones

### COMP-HEADER `[fijo]`
Logo, nav corta, buscador y **carrito como icono con badge** (regla de casa). Sticky.
Reutilizable: GLOBAL.

### COMP-DELIVERY-WINDOW — cuándo llega `[fijo · ADN]`
Objetivo: descartar antes de que el visitante invierta tiempo (decisión). Un campo de código
postal y una respuesta escrita: **qué días** se entrega ahí y **hasta qué hora** hay que pedir para
el próximo reparto. Si no se sirve esa zona, se dice, y se ofrece avisar cuando se sirva. Mobile:
campo a ancho completo, respuesta debajo. Reutilizable: SECCIÓN.
**Va arriba porque es un filtro, no un dato.** Un carrito lleno que muere en el checkout al
descubrir que no hay reparto es la conversión más cara de todo el embudo: se pagó tráfico, se
ganó la decisión y se perdió por una información que costaba una línea.

### COMP-BATCH-CARD — el lote de esta semana `[fijo · ADN] · TGL-BATCH-DEPTH`
Objetivo: comprar la pieza concreta (venta). Cada tarjeta lleva la foto **de la pieza real**,
el origen con nombre propio, el número de lote, el consumo preferente, el **precio por kilo** y el
peso aproximado con su rango. Sin estrellas ni badges de "oferta". Mobile: 1 columna. Desktop: 3.
Reutilizable: SECCIÓN.
**El origen es un nombre, no una región.** "Ternera gallega" es una etiqueta; "de la ganadería
Casanova, Lugo" es trazabilidad, y es lo que justifica el precio frente al lineal.

### COMP-WEIGHT-NOTE — por qué el importe cambia `[fijo · ADN]`
Objetivo: evitar la reclamación antes de que exista (confianza). Una explicación corta de que se
cobra el peso real, **cuánto puede variar** en porcentaje, y cuándo se ajusta el cobro.
Reutilizable: SECCIÓN.
**Esto no es letra pequeña, es una sección.** Un cargo distinto del importe que el cliente aprobó
es una incidencia de atención al cliente y una disputa con el banco; explicarlo antes cuesta un
párrafo y lo evita entero.

### COMP-ORIGIN-MAP — de dónde viene `[toggle TGL-ORIGIN-MAP]`
Objetivo: convertir la trazabilidad en algo mirable (confianza). Mapa o foto de la explotación,
lonja o finca, con su nombre y a qué distancia está. Reutilizable: SECCIÓN.

### COMP-COLD-CHAIN — cómo viaja `[fijo]`
Objetivo: quitar el miedo al fresco por mensajería (confianza). Embalaje, horas de autonomía en
frío y **qué hacer nada más recibirlo**. Reutilizable: SECCIÓN.

### COMP-TESTIMONIAL `[toggle TGL-TESTIMONIALS]`
De clientes que repiten, con el producto que compraron. Reutilizable: GLOBAL.

### COMP-NEWSLETTER `[toggle TGL-NEWSLETTER]`
La promesa aquí no es "novedades y promociones": es **avisar cuando entre el lote nuevo**, que es
lo único que un cliente de fresco quiere que le escriban. Reutilizable: GLOBAL.

### COMP-FOOTER `[fijo]`
Reutilizable: GLOBAL.

## 4. Toggles admitidos

| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-BATCH-DEPTH` | 6 lotes | los de la semana en curso |
| `TGL-ORIGIN-MAP` | on | off si el origen no es un argumento |
| `TGL-TESTIMONIALS` | off | encender con reseñas reales |
| `TGL-NEWSLETTER` | on | la promesa es el aviso de lote |
| `TGL-CARD-STYLE` | imagen grande | la pieza real se mira |

**Fijos:** COMP-HEADER, COMP-DELIVERY-WINDOW, COMP-BATCH-CARD, COMP-WEIGHT-NOTE, COMP-COLD-CHAIN,
COMP-FOOTER.
**Ausencias de ADN:** countdown y urgencia, rejilla de categorías, lookbook → sugerir TPL-E-05,
TPL-E-04 o TPL-E-01.

## 5. SEO / semántica
1 `H1` (la promesa de frescura con la ciudad dentro, que es como se busca esto). `H2` en Entrega,
Lotes, Peso, Origen, Frío. Schema `Product` con `Offer` en
`priceSpecification` tipo `UnitPriceSpecification` (`referenceQuantity` en kg — el precio por kilo
es el que hay que declarar, no el estimado de la pieza), `availabilityStarts`/`availabilityEnds`
para la ventana del lote, y `deliveryTime` en `OfferShippingDetails`. El consumo preferente en
`<time datetime>`. `header` > `main` > `footer`.
