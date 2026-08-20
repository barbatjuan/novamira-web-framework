# TPL-PDP-03 — Ficha de lote y peso

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Ficha de lote y peso |
| Objetivo | Que el cliente compre una pieza fresca sabiendo de dónde viene, cuánto pesa y qué día llega |
| Ecommerce ideal | Carnicería y pescadería online, quesos, embutido, café de tueste, bodega, huerta |
| Ejemplos | Chuletón madurado, atún de almadraba, queso de cuchara, café recién tostado, fruta de temporada |
| Home que la acompaña | `TPL-E-07 Lote / Peso` |
| ADN | El precio es por kilo y el importe se ajusta al pesar, la pieza tiene lote y fecha, y la ventana de entrega se comprueba ANTES de añadir. NO precio fijo, NO cross-sell de catálogo. |

**Por qué existe habiendo `TPL-PDP-01`.** La ficha estándar da por hecho que el producto es
idéntico a sí mismo: un cargador de móvil es el mismo cargador en marzo y en octubre. Aquí la pieza
**cambia cada semana y no es intercambiable consigo misma** — tiene un lote, una fecha y un peso
que no es exacto hasta que alguien la pone en la báscula.

Eso rompe tres supuestos que `TPL-PDP-01` no puede resolver con un toggle:

1. **El precio no es un número, es un número por kilo.** "34 €/kg · pieza de ~1,2 kg ≈ 41 €", y el
   importe final se ajusta al pesar. Un `COMP-PRODUCT-INFO` con PVP fijo miente en el 100 % de los
   pedidos, y `COMP-WEIGHT-NOTE` existe para decir cuánto puede variar antes de cobrar, no después.
2. **La entrega es parte del producto.** Fresco que llega un viernes por la tarde a una casa vacía
   es fresco perdido. `COMP-DELIVERY-WINDOW` va arriba: si no servimos ese código postal el martes,
   no hay compra que hacer, y averiguarlo en el checkout es averiguarlo tarde.
3. **La confianza es trazabilidad, no insignias.** `COMP-BATCH-CARD` da explotación o lonja, fecha
   de lote y consumo preferente. Un sello de "pago seguro" no dice nada sobre un pescado.

La ausencia también es deliberada: **no hay carrusel de relacionados**. Lo que sí hay es la cadena
de frío, porque la duda de quien compra fresco por internet no es qué más comprar sino qué le llega
y en qué estado.

## 2. Wireframe (top → bottom)
```
COMP-HEADER [fijo]
COMP-BREADCRUMB [fijo]
COMP-DELIVERY-WINDOW [fijo · ADN] · código postal → qué días entregamos ahí, ANTES de añadir
COMP-GALLERY [fijo] · la pieza concreta de este lote, no una foto de catálogo
COMP-PRODUCT-INFO [fijo · ADN] · €/kg, peso aproximado, importe estimado, add-to-cart
COMP-WEIGHT-NOTE [fijo · ADN] · por qué el importe final varía y cuánto, dicho antes de cobrar
COMP-BATCH-CARD [fijo · ADN] · origen, lote, fecha, consumo preferente
COMP-ORIGIN-MAP [toggle TGL-ORIGIN] · de qué explotación o lonja viene
COMP-COLD-CHAIN [fijo] · cómo viaja y qué hacer al recibirlo
COMP-FOOTER [fijo]
```

## 3. Secciones
### COMP-DELIVERY-WINDOW `[fijo · ADN]`
Descartar la compra imposible antes de empezarla. Campo de código postal → días de reparto en esa
zona y hora de corte. Mobile: campo ancho, resultado debajo. Desktop: en línea, sobre la galería.
Reutilizable: GLOBAL (lo comparte `TPL-E-07`).

### COMP-PRODUCT-INFO `[fijo · ADN]`
Título (H1), **precio por kilo** como cifra principal, peso aproximado de la pieza, importe
estimado en secundario, selector de tamaño de pieza si lo hay, add-to-cart. Nunca un PVP a secas.
Reutilizable: GLOBAL.

### COMP-WEIGHT-NOTE `[fijo · ADN]`
Una frase y un rango: "las piezas van de 1,1 a 1,4 kg; el cargo final se ajusta al peso real y se
te comunica antes de cobrar". Va **pegada al bloque de compra**, no en el pie. Reutilizable: GLOBAL.

### COMP-BATCH-CARD `[fijo · ADN]`
La trazabilidad de ESTA pieza: explotación o lonja, fecha de lote, consumo preferente, y quién la
preparó si aplica. Mobile: tarjeta apilada. Desktop: fila de datos. Reutilizable: GLOBAL.

### COMP-COLD-CHAIN `[fijo]`
Cómo viaja (aislante, hielo seco, transportista) y qué hacer al abrir la caja. Reutilizable: GLOBAL.

## 4. Toggles admitidos
| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-ORIGIN` | on | apagar si el origen no es un argumento de venta |
| `TGL-PDP-STICKY` | on (desktop) | |
| `TGL-RELATED` | **off** | ver ADN |
| `TGL-TRUST` | **off** | un sello de pago seguro no dice nada sobre un pescado |

**Fijos:** HEADER, BREADCRUMB, DELIVERY-WINDOW, GALLERY, PRODUCT-INFO, WEIGHT-NOTE, BATCH-CARD,
COLD-CHAIN, FOOTER.
**Ausencias de ADN:** PVP fijo, carrusel de relacionados, insignias de confianza genéricas.

## 5. SEO / semántica
1 `H1` (nombre de la pieza). `H2` en Lote, Cómo viaja. Schema `Product` + `Offer` con
`priceSpecification` por unidad de peso (`unitCode: KGM`) — un `price` a secas describe mal el
producto y peor la oferta. `header` > `main` > `footer`.
