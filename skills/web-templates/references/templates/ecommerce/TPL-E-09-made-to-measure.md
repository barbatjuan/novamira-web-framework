# TPL-E-09 — A medida / Presupuesto

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | A medida / Presupuesto |
| Objetivo | Que el cliente pida presupuesto de algo que todavía no existe |
| Ecommerce ideal | Cortinas y estores, mobiliario a medida, encimeras, impresión y rotulación, mamparas, tapicería |
| Ejemplos | Estor enrollable por centímetros, armario a medida, encimera de cocina, vinilo para escaparate |
| Nivel de contenido | Bajo y CONFIGURABLE: pocas secciones y muchas combinaciones |
| Protagonismo del producto | Alto — pero el producto es una configuración, no una referencia |
| Protagonismo de la marca | Medio |
| ADN | Configurador con medidas y acabados + muestra física + plazo por fases + formulario de PRESUPUESTO. **No hay carrito y no hay checkout.** NO catálogo, NO PVP, NO countdown. |

**Por qué existe, y por qué es el único ecommerce sin carrito.** Los cinco arquetipos originales y
los tres anteriores comparten un supuesto que aquí se rompe: **existe una referencia con un precio
que se puede meter en un carrito**. Un estor de 137 × 214 cm en lino crudo con cadena a la
izquierda no es una referencia; es una combinación que nadie ha fabricado todavía, y su precio no
se sabe hasta configurarla.

Forzar esto en `TPL-E-02` produce el error clásico del sector: un catálogo con precios "desde"
que no se parecen al precio final, un carrito que acepta la compra y un correo posterior pidiendo
medidas. Ese correo es donde se cae la venta, y encima ya se cobró.

Así que aquí el embudo termina en **presupuesto**, no en pago, y eso cambia tres cosas de raíz.
El configurador **es** la página, no una pestaña de la ficha. La **muestra física** existe como
sección propia, porque un color de tejido en pantalla no es el color del tejido y una devolución
de algo fabricado a medida no existe. Y el **plazo va por fases** —medición, fabricación,
instalación— porque "3 a 4 semanas" sin desglosar no es un plazo, es una excusa por adelantado.

Nota de encaje: si además hay visita técnica y el precio depende de ella, sigue siendo este
arquetipo. Si lo que se vende es un servicio y no un objeto fabricado, es `TPL-C-01`.

## 2. Wireframe (top → bottom)

```
COMP-HEADER (sin carrito; CTA "Pedir presupuesto") [fijo]
COMP-HERO (qué se fabrica y en cuánto tiempo) [fijo]
COMP-CONFIGURATOR (medidas, material, acabado, herrajes) [fijo · ADN]
COMP-SAMPLE-REQUEST (muestra física antes de decidir) [fijo · ADN]
COMP-LEAD-TIME (plazo por fases, con días) [fijo · ADN]
COMP-QUOTE-FORM (presupuesto, no compra) [fijo · ADN]
COMP-GALLERY (trabajos entregados, con su medida) [toggle]
COMP-FAQ (dudas de medición y montaje) [toggle]
COMP-FOOTER [fijo]
```

## 3. Secciones

### COMP-HEADER `[fijo]`
Logo, nav corta, teléfono clickeable y CTA **"Pedir presupuesto"**. **Sin icono de carrito**: no
hay nada que añadir, y un carrito que no se usa es una promesa de compra inmediata que la página
no puede cumplir. Sticky. Reutilizable: GLOBAL.

### COMP-HERO — qué fabricamos `[fijo] · TGL-HERO-HEIGHT`
Objetivo: situar en una frase (descubrimiento). Qué se fabrica, a medida de qué, y **en cuánto
tiempo**. ~45vh, con una fotografía de trabajo terminado, no de campaña. Reutilizable: SECCIÓN.

### COMP-CONFIGURATOR — tu medida `[fijo · ADN] · TGL-CONFIG-STEPS`
Objetivo: convertir una idea en algo presupuestable (decisión). Pasos cortos —medidas, material,
acabado, herrajes— con **precio estimado que se actualiza** y el aviso de que es estimación hasta
la medición. Mobile: un paso por pantalla, con progreso visible. Reutilizable: SECCIÓN.
**El estimado se enseña aunque no sea el definitivo.** Un configurador que pide cuatro decisiones
y no devuelve ninguna cifra hasta que dejas el teléfono se abandona en el paso dos: el visitante
está intentando saber si esto entra en su presupuesto, y esa es una pregunta legítima que merece
respuesta antes del formulario.

### COMP-SAMPLE-REQUEST — la muestra `[fijo · ADN] · TGL-SAMPLE`
Objetivo: resolver el color y el tacto (confianza). Envío de muestras físicas, cuántas, si son
gratis y en cuántos días llegan. Reutilizable: SECCIÓN.
**Es la sección que sostiene todo el modelo.** Lo fabricado a medida no se devuelve, así que el
visitante que duda del color no compra; una muestra de 3 € evita un pedido de 600 € que no se
hace, y además abre un contacto con quien ya decidió casi todo.

### COMP-LEAD-TIME — cuánto tarda `[fijo · ADN]`
Objetivo: hacer creíble el plazo (confianza). Fases con sus días: **medición**, **fabricación**,
**instalación**. Reutilizable: SECCIÓN.

### COMP-QUOTE-FORM — presupuesto `[fijo · ADN]`
Objetivo: cerrar (conversión). Recoge la configuración ya hecha —no la vuelve a preguntar—,
más contacto, código postal y una foto opcional del hueco. Dice **en cuánto tiempo se responde**
y con **consentimiento RGPD explícito** (lo monta `wordpress-forms`). Reutilizable: SECCIÓN.
**Nunca en el hero, siempre al final** (regla de casa del orquestador): un formulario antes del
configurador pide datos a quien todavía no sabe si puede pagarlo.

### COMP-GALLERY — trabajos entregados `[toggle TGL-GALLERY]`
Objetivo: prueba (confianza). Cada foto con **la medida y el material** escritos, que es lo que la
convierte en referencia y no en decoración. Reutilizable: GLOBAL.

### COMP-FAQ `[toggle TGL-FAQ]`
Dudas de MEDICIÓN y MONTAJE: quién mide, qué pasa si me equivoco al medir, si se instala.
Reutilizable: GLOBAL.

### COMP-FOOTER `[fijo]`
Reutilizable: GLOBAL.

## 4. Toggles admitidos

| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-HERO-HEIGHT` | 45vh | |
| `TGL-CONFIG-STEPS` | 4 pasos | nunca más de 5 |
| `TGL-SAMPLE` | on | off solo si el material no tiene variante visual |
| `TGL-GALLERY` | on | cada foto con su medida |
| `TGL-FAQ` | on | ADN de medición |
| `TGL-CTA-STRENGTH` | suave | se pide presupuesto, no se cierra una venta |

**Fijos:** COMP-HEADER, COMP-HERO, COMP-CONFIGURATOR, COMP-SAMPLE-REQUEST, COMP-LEAD-TIME,
COMP-QUOTE-FORM, COMP-FOOTER.
**Ausencias de ADN:** carrito, checkout, catálogo con PVP, countdown → si hay referencias con
precio cerrado, el arquetipo es TPL-E-02. Si se vende un servicio y no un objeto fabricado, es
TPL-C-01.

## 5. SEO / semántica
1 `H1` (hero, con el producto y "a medida" dentro, que es como se busca). `H2` en Configurador,
Muestras, Plazos, Presupuesto. Schema `Product` con `Offer` en
`priceSpecification` marcada como estimación y `PriceSpecification.minPrice`/`maxPrice` en vez de
un `price` cerrado — declarar un precio exacto que el presupuesto luego desmiente es una
discrepancia que Merchant Center penaliza y que además defrauda a quien llegó por ella. `Service`
para la instalación si se ofrece. El configurador tiene que funcionar y ser indexable **sin JS**
en su forma mínima: si el único camino al presupuesto es un widget, la página no tiene contenido
que posicionar. `header` > `main` > `footer`.
