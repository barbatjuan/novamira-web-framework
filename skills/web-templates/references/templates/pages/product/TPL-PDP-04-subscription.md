# TPL-PDP-04 — Ficha de suscripción

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Ficha de suscripción |
| Objetivo | Que el cliente se suscriba, no que compre una vez |
| Ecommerce ideal | Café de tueste, comida de mascota, cosmética de reposición, vino, cajas de temporada, consumibles |
| Ejemplos | Café mensual, pienso cada seis semanas, cuchillas, cesta de huerta, suplementos |
| Home que la acompaña | `TPL-E-08 Suscripción / Entrega recurrente` |
| ADN | Lo que se elige es un PLAN y una cadencia, no una cantidad. Qué llega la primera vez está escrito, y poder salirse es una sección con título. NO precio unitario, NO add-to-cart, NO relacionados. |

**Por qué existe habiendo `TPL-PDP-01`, y por qué es la única ficha sin `COMP-PRODUCT-INFO`.**
La ficha estándar tiene un control de compra: variante, cantidad, añadir. Aquí ese control no
aplica, porque lo que se contrata no es una unidad sino una **cuota con una frecuencia**. Nadie
compara "12,90 €" con "12,90 € al mes cada seis semanas" del mismo modo, y dejar un botón de
"añadir al carrito" en la página empuja justo a la decisión equivocada — comprar una bolsa suelta
en vez de suscribirse. El control de compra es `COMP-PLAN-PICKER`, y sustituye al bloque de
producto en lugar de convivir con él.

De ahí las dos secciones que ninguna otra ficha tiene:

1. **La cadencia es un control, no letra pequeña.** Cambiar la frecuencia, saltarse un envío y
   pausar tienen que poder tocarse arriba, porque el miedo a acumular producto que no se consume
   es la objeción número uno y no se disuelve leyendo condiciones.
2. **La promesa de cancelación es una sección con su propio título.** No un enlace en el pie. Una
   suscripción de la que no se ve la salida no se firma, y esconderla no la hace menos visible:
   la hace más sospechosa.

Y una sección que parece obvia y casi nunca está: **qué llega exactamente la primera vez**.
"Descubre nuestro café" no es contenido de una caja; 250 g de Huila lavado, molido para filtro y
una ficha de tueste, sí.

## 2. Wireframe (top → bottom)
```
COMP-HEADER [fijo] · CTA "Empezar", sin icono de carrito
COMP-BREADCRUMB [fijo]
COMP-GALLERY [fijo] · el producto, pero mostrando la caja y su contenido
COMP-PLAN-PICKER [fijo · ADN] · qué recibes · cada cuánto · cuánto al mes — sustituye al add-to-cart
COMP-CADENCE [fijo · ADN] · cambiar frecuencia, saltar un envío, pausar
COMP-FIRST-BOX [fijo · ADN] · qué llega exactamente la primera vez, con gramos y referencias
COMP-PAUSE-PROMISE [fijo · ADN] · cancelar en un clic, sin permanencia
COMP-FAQ [toggle TGL-FAQ] · dudas de compromiso, no de producto
COMP-FOOTER [fijo]
```

## 3. Secciones
### COMP-PLAN-PICKER `[fijo · ADN]`
**Es el control de compra de esta página.** Dos o tres planes contados enteros: qué entra, cada
cuánto llega, cuánto es al mes y cuánto sale la unidad frente a comprarlo suelto. Mobile: tarjetas
apiladas con el recomendado primero. Desktop: comparación en fila. Reutilizable: GLOBAL (lo
comparte `TPL-E-08`).

### COMP-CADENCE `[fijo · ADN]`
Los tres controles que quitan el miedo: frecuencia (cada 2 / 4 / 6 semanas), saltar el próximo
envío, pausar hasta una fecha. Escritos como acciones posibles, no como condiciones. Reutilizable:
GLOBAL.

### COMP-FIRST-BOX `[fijo · ADN]`
El contenido exacto del primer envío, con cantidades y nombres reales, y la fecha en que sale.
Mobile: lista con miniaturas. Desktop: fotografía de la caja abierta + lista al lado. Reutilizable:
GLOBAL.

### COMP-PAUSE-PROMISE `[fijo · ADN]`
Sección con título: sin permanencia, cancelación desde la cuenta, qué pasa con el envío ya
preparado. Reutilizable: GLOBAL.

## 4. Toggles admitidos
| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-PLAN-COUNT` | 3 | 2 o 3 planes; con 4 nadie compara, elige el del medio por defecto |
| `TGL-ONE-OFF` | off | permitir la compra suelta además del plan — apagado, es la fuga principal |
| `TGL-FAQ` | on | dudas de compromiso |
| `TGL-RELATED` | **off** | ver ADN |

**Fijos:** HEADER, BREADCRUMB, GALLERY, PLAN-PICKER, CADENCE, FIRST-BOX, PAUSE-PROMISE, FOOTER.
**Ausencias de ADN:** `COMP-PRODUCT-INFO` con PVP y cantidad, add-to-cart, carrusel de
relacionados. Si el negocio necesita las tres, lo que vende es catálogo y la ficha es `TPL-PDP-01`.

## 5. SEO / semántica
1 `H1` (nombre de la suscripción, no del producto suelto). `H2` en Planes, Cadencia, Primera caja,
Cancelación. Schema `Product` + `Offer` con `priceSpecification` recurrente
(`billingDuration` / `billingIncrement`); publicar la cuota como si fuera un PVP es lo que produce
comparadores mostrando un precio que nadie paga. `header` > `main` > `footer`.
