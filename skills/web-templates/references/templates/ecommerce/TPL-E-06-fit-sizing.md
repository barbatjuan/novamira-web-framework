# TPL-E-06 — Talla / Prueba

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Talla / Prueba |
| Objetivo | Que el cliente compre convencido de que le va a quedar, y no "a ver si suena la flauta" |
| Ecommerce ideal | Moda con ajuste, calzado, lencería, ropa técnica y deportiva, uniformes |
| Ejemplos | Vaqueros, botas, sujetadores, ropa de running, trajes, ropa infantil por edad |
| Nivel de contenido | Medio, y casi todo NUMÉRICO: medidas, no adjetivos |
| Protagonismo del producto | Alto — pero mostrado sobre cuerpos distintos, no sobre uno solo |
| Protagonismo de la marca | Bajo–medio |
| ADN | Buscador de talla arriba + medidas reales en cm por prenda + la misma prenda en tres cuerpos + la política de devolución con cifras. NO hero editorial, NO lookbook, NO countdown. |

**Por qué existe habiendo TPL-E-01.** TPL-E-01 vende con la fotografía: el lookbook abre y el
lookbook cierra, y funciona cuando lo que se compra es el estilo. Aquí lo que se compra es una
prenda que **tiene que caber**, y esa es una pregunta que ninguna fotografía responde. El dato duro
del sector es que la talla es la primera causa de devolución en moda online, así que el coste de no
responderla no es una venta perdida: es una venta hecha, enviada, devuelta y reembolsada, pagando
dos portes por el camino.

De ahí las tres decisiones que definen el arquetipo. **El buscador de talla va ARRIBA**, no en una
pestaña de la ficha, porque es la primera intención del visitante que ya sabe lo que quiere. **Las
medidas van en centímetros y por prenda**, no una tabla genérica de marca al pie: "M" no es una
medida, es una etiqueta que cada fábrica interpreta distinto. Y **la misma prenda aparece sobre
tres cuerpos con sus tallas escritas**, porque una prenda sobre un solo cuerpo informa de ese
cuerpo y de ninguno más.

La ausencia también es deliberada: **no hay hero editorial a sangre**. Una pantalla completa de
campaña antes del buscador es media pantalla gastada en alguien que ya decidió comprar y solo
necesita saber su talla.

## 2. Wireframe (top → bottom)

```
COMP-HEADER (con buscador y carrito icono+badge) [fijo]
COMP-FIT-FINDER (qué talla usas y en qué marca → tu talla aquí) [fijo · ADN]
COMP-PRODUCT-GRID (ficha con la talla que lleva la modelo) [fijo]
COMP-MEASURE-TABLE (medidas reales en cm, prenda a prenda) [fijo · ADN]
COMP-FIT-GALLERY (la misma prenda en tres cuerpos, con su talla) [fijo · ADN]
COMP-RETURN-PROMISE (plazo, quién paga el porte, cuántos días tarda) [fijo · ADN]
COMP-FAQ (dudas de ajuste, no de envío) [toggle]
COMP-FOOTER [fijo]
```

## 3. Secciones

### COMP-HEADER `[fijo]`
Logo, nav corta, buscador y **carrito como icono con badge** (regla de casa). Sticky.
Reutilizable: GLOBAL.

### COMP-FIT-FINDER — tu talla aquí `[fijo · ADN]`
Objetivo: convertir la talla en un dato, no en una apuesta (decisión). Dos campos y nada más:
**qué talla usas** y **en qué marca**. Devuelve la talla equivalente en esta tienda, escrita, con
la advertencia si la prenda calza distinto. Mobile: los dos campos apilados, resultado debajo.
Reutilizable: SECCIÓN.
**Dos campos, no un cuestionario.** Un formulario de altura, peso, contorno y preferencia de ajuste
convierte peor que la tabla que pretende sustituir: el visitante que no sabe su contorno de cadera
abandona ahí, y el que lo sabe ya se habría medido igual.

### COMP-PRODUCT-GRID — el catálogo `[fijo]`
Objetivo: elegir prenda (descubrimiento). Ficha con foto, precio en €, y **la talla que lleva la
persona de la foto** escrita en la tarjeta, no escondida en el detalle. Mobile: 2 columnas.
Desktop: 4. Reutilizable: GLOBAL.

### COMP-MEASURE-TABLE — las medidas `[fijo · ADN] · TGL-MEASURE-UNITS`
Objetivo: que la talla se pueda comprobar con una cinta métrica (confianza). Una fila por talla y
una columna por medida relevante A ESA PRENDA — un vaquero lleva cintura, cadera y largo de
entrepierna; una camisa lleva pecho, hombro y manga. **Medidas de la prenda, no del cuerpo**, y
dicho en la propia tabla, porque son dos números distintos y confundirlos es la mitad de los
errores. Mobile: la tabla scrollea dentro de su contenedor, la página nunca. Reutilizable: SECCIÓN.

### COMP-FIT-GALLERY — cómo queda `[fijo · ADN] · TGL-FIT-BODIES`
Objetivo: que el visitante se reconozca (confianza). La misma prenda en tres personas de
complexión distinta, cada foto con **la altura y la talla que lleva** escritas al lado. Sin
retoque de silueta. Mobile: carrusel de una en una. Reutilizable: SECCIÓN.
**Tres cuerpos es el mínimo que informa.** Con dos la lectura es "la delgada y la otra"; con tres
hay un rango, que es lo que el visitante está buscando para situarse.

### COMP-RETURN-PROMISE — si no te queda `[fijo · ADN]`
Objetivo: quitar el último freno (decisión). Tres cifras y ninguna letra pequeña: **cuántos días**
hay para devolver, **quién paga** el porte, y **en cuántos días** se devuelve el dinero.
Reutilizable: SECCIÓN.
**Si el porte de devolución lo paga el cliente, se dice aquí igual.** Esconderlo hasta el checkout
no evita la fricción, la traslada al momento en que más caro sale perderla.

### COMP-FAQ `[toggle TGL-FAQ]`
Solo dudas de AJUSTE: encoge al lavar, calza grande, entre dos tallas cuál. Los envíos van al
footer. Reutilizable: GLOBAL.

### COMP-FOOTER `[fijo]`
Reutilizable: GLOBAL.

## 4. Toggles admitidos

| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-MEASURE-UNITS` | cm | pulgadas solo si el mercado principal las usa |
| `TGL-FIT-BODIES` | 3 cuerpos | nunca menos de 2 |
| `TGL-CARD-STYLE` | imagen grande | |
| `TGL-FAQ` | on | ADN de ajuste |
| `TGL-CTA-STRENGTH` | medio | |

**Fijos:** COMP-HEADER, COMP-FIT-FINDER, COMP-PRODUCT-GRID, COMP-MEASURE-TABLE, COMP-FIT-GALLERY,
COMP-RETURN-PROMISE, COMP-FOOTER.
**Ausencias de ADN:** lookbook editorial, countdown, rejilla de categorías → sugerir TPL-E-01,
TPL-E-05 o TPL-E-04.

## 5. SEO / semántica
1 `H1` (hero del buscador de talla). `H2` en Catálogo, Medidas, Cómo queda, Devoluciones. Schema
`Product` con `size` en cada `Offer` y `MerchantReturnPolicy` con `merchantReturnDays` y
`returnFees` — los dos números que `COMP-RETURN-PROMISE` publica, y que Google muestra en la ficha
de resultados. La tabla de medidas en `<table>` real con `<th scope>`, nunca como imagen: una tabla
de tallas en JPG no se indexa, no se lee con lector de pantalla y no se puede ampliar en un móvil.
`header` > `main` > `footer`.
