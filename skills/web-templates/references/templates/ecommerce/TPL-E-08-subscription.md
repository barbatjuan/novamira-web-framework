# TPL-E-08 — Suscripción / Entrega recurrente

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Suscripción / Entrega recurrente |
| Objetivo | Que el cliente se suscriba, no que compre una vez |
| Ecommerce ideal | Café de tueste, comida de mascota, cosmética de reposición, vino, cajas de temporada, consumibles |
| Ejemplos | Café mensual, pienso cada seis semanas, cuchillas de afeitar, cesta de huerta, suplementos |
| Nivel de contenido | Bajo y COMPARATIVO: dos o tres planes contados enteros |
| Protagonismo del producto | Medio — el producto importa, pero lo que se elige es el plan |
| Protagonismo de la marca | Medio |
| ADN | Planes con cuota mensual + control de cadencia + qué llega en la primera caja + la promesa de poder cancelar. NO rejilla de catálogo, NO countdown, NO carrusel de productos. |

**Por qué existe habiendo TPL-E-02 y TPL-C-11.** Frente a TPL-E-02, la diferencia no es el
catálogo sino **el precio**: un catálogo publica PVP y aquí se publica una cuota. Nadie compara
"12,90 €" con "12,90 € al mes cada seis semanas" del mismo modo, y una rejilla de producto empuja
justo a la decisión equivocada — comprar una bolsa suelta en vez de suscribirse.

Frente a `TPL-C-11 Plan por fases`, que también vende una cuota: aquel plan **termina** —la
ortodoncia dura dieciocho meses y luego se acaba— y por eso su bloque central es una línea de
tiempo. Éste **no termina**, y por eso su bloque central no es un calendario sino un
**control de cadencia**. La pregunta del visitante tampoco es la misma: allí es "¿cuánto tiempo y
cuánto duele?", aquí es "¿y si me sobra café?" y "¿me puedo salir?".

De ahí las dos secciones que ningún otro arquetipo tiene. **La cadencia es un control, no una
letra pequeña**: cambiar la frecuencia, saltarse un envío y pausar tienen que estar escritos
arriba, porque el miedo a acumular producto que no se consume es la objeción número uno. Y **la
promesa de cancelación es una sección con su propio título**, no un enlace en el footer: una
suscripción que esconde cómo se sale es la que no se contrata.

## 2. Wireframe (top → bottom)

```
COMP-HEADER (nav corta, CTA "Empezar") [fijo]
COMP-HERO (la promesa en cuota, no en PVP) [fijo]
COMP-PLAN-PICKER (qué recibes · cada cuánto · cuánto al mes) [fijo · ADN]
COMP-CADENCE (cambiar frecuencia, saltar un envío, pausar) [fijo · ADN]
COMP-FIRST-BOX (qué llega exactamente la primera vez) [fijo · ADN]
COMP-PAUSE-PROMISE (cancelar en un clic, sin permanencia) [fijo · ADN]
COMP-TESTIMONIAL (quién lleva tiempo suscrito) [toggle]
COMP-FAQ (dudas de compromiso, no de producto) [toggle]
COMP-FOOTER [fijo]
```

## 3. Secciones

### COMP-HEADER `[fijo]`
Logo, nav corta y CTA "Empezar". **Sin icono de carrito**: no hay compra suelta que acumular, y un
carrito vacío permanente en la cabecera es una invitación a un flujo que esta página no tiene.
Sticky. Reutilizable: GLOBAL.

### COMP-HERO — la promesa `[fijo] · TGL-HERO-HEIGHT`
Objetivo: que se entienda el modelo en una frase (descubrimiento). El H1 lleva la cuota y la
cadencia dentro —*"Café recién tostado en tu casa cada quince días, desde 14 € al mes"*—, no un
claim de marca. ~45vh. Reutilizable: SECCIÓN.

### COMP-PLAN-PICKER — los planes `[fijo · ADN] · TGL-PLAN-COUNT`
Objetivo: elegir plan (venta). Dos o tres columnas, y cada una con **qué llega**, **cada cuánto**,
**cuánto al mes** y el equivalente por unidad. Uno marcado como recomendado. Mobile: apilados, el
recomendado primero. Reutilizable: SECCIÓN.
**El equivalente por unidad va escrito.** "34 € al mes" no se puede comparar con nada; "34 € al
mes · 2 bolsas · 17 € la bolsa" sí, y es la cuenta que el visitante hará de todos modos, mejor
hecha por nosotros que a ojo.

### COMP-CADENCE — a tu ritmo `[fijo · ADN] · TGL-CADENCE-OPTIONS`
Objetivo: desactivar el miedo a acumular (decisión). Las frecuencias disponibles como opciones
visibles, y dicho con todas las letras que se puede **saltar un envío** y **cambiar la frecuencia**
sin llamar a nadie. Reutilizable: SECCIÓN.

### COMP-FIRST-BOX — la primera caja `[fijo · ADN]`
Objetivo: hacer concreto lo abstracto (confianza). Qué llega exactamente la primera vez, con foto
del contenido real y **qué día**. Reutilizable: SECCIÓN.
**Una suscripción es una promesa sobre el futuro, y eso se vende mal.** La primera caja es lo
único de toda la oferta que se puede enseñar, así que se enseña entera.

### COMP-PAUSE-PROMISE — salirse `[fijo · ADN]`
Objetivo: quitar el último freno (decisión). Tres hechos: **sin permanencia**, **se cancela desde
la cuenta** y **cuánto antes** del siguiente cobro hay que hacerlo. Reutilizable: SECCIÓN.
**Si hay permanencia, esta sección la dice igual.** Una permanencia descubierta al intentar
cancelar no retiene a nadie: produce una devolución de cargo y una reseña.

### COMP-TESTIMONIAL `[toggle TGL-TESTIMONIALS]`
De suscriptores con **cuánto tiempo llevan**, que es el único dato que prueba el modelo.
Reutilizable: GLOBAL.

### COMP-FAQ `[toggle TGL-FAQ]`
Dudas de COMPROMISO: qué pasa si me voy de viaje, puedo cambiar de producto, cuándo se cobra.
Reutilizable: GLOBAL.

### COMP-FOOTER `[fijo]`
Reutilizable: GLOBAL.

## 4. Toggles admitidos

| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-HERO-HEIGHT` | 45vh | la cuota va en el H1 |
| `TGL-PLAN-COUNT` | 3 planes | 2 si la oferta es simple; nunca 1 |
| `TGL-CADENCE-OPTIONS` | 3 frecuencias | |
| `TGL-TESTIMONIALS` | on | con antigüedad del suscriptor |
| `TGL-FAQ` | on | ADN de compromiso |
| `TGL-CTA-STRENGTH` | medio | |

**Fijos:** COMP-HEADER, COMP-HERO, COMP-PLAN-PICKER, COMP-CADENCE, COMP-FIRST-BOX,
COMP-PAUSE-PROMISE, COMP-FOOTER.
**Ausencias de ADN:** rejilla de catálogo, carrusel de producto, countdown → sugerir TPL-E-02 o
TPL-E-05. Si el plan TERMINA en una fecha, no es este arquetipo: es `TPL-C-11`.

## 5. SEO / semántica
1 `H1` (hero, con la cuota dentro). `H2` en Planes, Cadencia, Primera caja, Cancelar. Schema
`Product` + `Offer` con `priceSpecification` tipo `UnitPriceSpecification` y
`billingIncrement`/`billingDuration` para la cuota — una suscripción declarada como precio simple
se indexa como si fuera un pago único, que es exactamente el malentendido que esta página existe
para evitar. `FAQPage` en el bloque de compromiso. `header` > `main` > `footer`.
