# TPL-PDP-02 — Ficha de talla y ajuste

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Ficha de talla y ajuste |
| Objetivo | Que el cliente compre la talla correcta a la primera |
| Ecommerce ideal | Moda con ajuste, calzado, lencería, ropa técnica y deportiva, uniformes |
| Ejemplos | Vaqueros, botas, sujetadores, ropa de running, trajes, ropa infantil por edad |
| Home que la acompaña | `TPL-E-06 Talla / Prueba` |
| ADN | El buscador de talla y las medidas en cm van ARRIBA, la prenda se enseña sobre tres cuerpos, y la devolución se cuenta con cifras antes de comprar. NO acordeón con la tabla escondida, NO carrusel de "completá el look". |

**Por qué existe habiendo `TPL-PDP-01`.** La ficha estándar da por resuelto que el producto le
sirve al comprador y sólo le queda elegir variante. Aquí la variante **es** la duda, y no es una
preferencia: es una medida. La talla es la primera causa de devolución en moda online, así que el
coste de no responderla no es una venta perdida — es una venta hecha, enviada, devuelta y
reembolsada, con dos portes pagados por el camino.

Eso mueve tres cosas de sitio, y las tres son estructurales, no de estilo:

1. **La tabla de medidas sale del acordeón.** En `TPL-PDP-01` el detalle se pliega porque es
   detalle. Aquí el detalle es la decisión: una tabla en una pestaña que nadie abre es una tabla
   que no existe. `COMP-MEASURE-TABLE` es una sección con su propio título, en centímetros y de
   ESTA prenda — "M" no es una medida, es una etiqueta que cada fábrica interpreta distinto.
2. **La galería enseña cuerpos, no producto.** `COMP-FIT-GALLERY` es la misma prenda sobre tres
   personas distintas con su talla escrita al lado. Una prenda sobre un solo cuerpo informa de ese
   cuerpo y de ninguno más, y una foto de producto sobre fondo blanco no informa de ninguno.
3. **La devolución se promete arriba y con números.** `COMP-RETURN-PROMISE` dice plazo, quién paga
   el porte y cuántos días tarda el reembolso. Es lo que convierte "no sé si me vale" en "pruebo",
   y en el pie no lo lee nadie.

La ausencia también es deliberada: **no hay cross-sell**. Empujar otra prenda a quien todavía duda
de la talla de ésta es cambiar una compra segura por dos inseguras.

## 2. Wireframe (top → bottom)
```
COMP-HEADER [fijo]
COMP-BREADCRUMB [fijo]
COMP-FIT-FINDER [fijo · ADN] · qué talla usas y en qué marca → tu talla aquí
COMP-FIT-GALLERY [fijo · ADN] · la misma prenda en tres cuerpos, con su talla escrita
COMP-PRODUCT-INFO [fijo · ADN] · precio €, selector de talla CON stock por talla, add-to-cart
COMP-MEASURE-TABLE [fijo · ADN] · medidas reales en cm de ESTA prenda, no de la marca
COMP-RETURN-PROMISE [fijo · ADN] · plazo, quién paga el porte, días hasta el reembolso
COMP-FAQ [toggle TGL-FAQ] · dudas de ajuste, no de envío
COMP-FOOTER [fijo]
```

## 3. Secciones
### COMP-FIT-FINDER `[fijo · ADN]`
Traducir una talla conocida a la de esta tienda. "¿Qué talla usas y en qué marca?" → resultado con
el margen ("te va la 42, y si la quieres holgada la 44"). Mobile: dos selectores apilados, resultado
en tarjeta. Desktop: en línea, junto a la galería. Reutilizable: GLOBAL (lo comparte `TPL-E-06`).

### COMP-FIT-GALLERY `[fijo · ADN]`
La misma prenda sobre tres cuerpos, cada uno con altura y talla escritas. Mobile: carrusel con la
talla siempre visible sobre la foto. Desktop: tres columnas. **Nunca sustituye a la foto de
producto: la acompaña.** Reutilizable: GLOBAL.

### COMP-PRODUCT-INFO `[fijo · ADN] · TGL-PDP-STICKY`
Título (H1), precio €, **selector de talla con el stock de cada una** —una talla agotada se ve
agotada, no se descubre al añadir— y add-to-cart. Mobile: barra pegada al hacer scroll.
Reutilizable: GLOBAL.

### COMP-MEASURE-TABLE `[fijo · ADN]`
Medidas en cm por talla y por prenda: pecho, cintura, largo, manga; en calzado, largo de plantilla.
Con una línea de cómo se mide. Mobile: tabla con scroll horizontal propio, nunca la página entera.
Desktop: tabla completa. Reutilizable: GLOBAL.

### COMP-RETURN-PROMISE `[fijo · ADN]`
Tres cifras y una frase: días para devolver, quién paga el porte, días hasta el reembolso. Sin
asteriscos. Reutilizable: GLOBAL.

## 4. Toggles admitidos
| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-PDP-STICKY` | on (desktop) | |
| `TGL-FIT-FINDER` | on | apagar sólo si no hay equivalencias entre marcas que ofrecer |
| `TGL-FAQ` | on | dudas de ajuste |
| `TGL-RELATED` | **off** | ver ADN: no se hace cross-sell a quien todavía duda de la talla |

**Fijos:** HEADER, BREADCRUMB, FIT-FINDER, FIT-GALLERY, PRODUCT-INFO, MEASURE-TABLE,
RETURN-PROMISE, FOOTER.
**Ausencias de ADN:** acordeón con la tabla escondida, carrusel de relacionados, galería editorial
a sangre.

## 5. SEO / semántica
1 `H1` (nombre de la prenda). `H2` en Medidas y Devoluciones. Schema `Product` + `Offer` con una
variante por talla (`size`, `availability` por variante). La tabla de medidas en `<table>` real con
`<th scope>`, no en imagen: una tabla en JPG no la lee ni un buscador ni un lector de pantalla.
`header` > `main` > `footer`.
