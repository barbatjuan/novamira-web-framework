# TPL-C-07 — Stock / Ocasión

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Stock / Ocasión |
| Objetivo | Que el usuario encuentre una unidad concreta y pregunte por ella |
| Ideal para | Concesionarios multimarca, vehículo de ocasión, maquinaria, náutica, caravanas |
| Ejemplos | Compraventa de coches, seminuevos, tractores, motos, embarcaciones |
| Nivel de contenido | Alto y VOLÁTIL: el stock cambia cada semana |
| Protagonismo | El BUSCADOR y la rejilla de unidades; todo lo demás va debajo |
| ADN | Filtros arriba + fichas con kilómetros, año y precio + tasación + cuota. NO carta de servicios, NO equipo, NO portfolio. |

**Por qué existe.** Ningún arquetipo corporativo tenía un INVENTARIO. TPL-C-01 vende servicios,
TPL-C-03 enseña obra terminada, TPL-C-05 lleva a una puerta — y las tres presentan contenido que el
dueño escribe una vez. Aquí el contenido son cuarenta unidades que entran y salen, cada una con
cinco datos que deciden la compra (precio, año, kilómetros, combustible, cambio) y ninguno de los
cuales cabe en una tarjeta de servicio. El buscador va ARRIBA y no en una página aparte porque
filtrar es la primera intención del visitante, no la segunda.

## 2. Wireframe (top → bottom)

```
COMP-HEADER (con teléfono + CTA "Vender mi coche") [fijo]
COMP-SEARCH-FILTERS (marca, precio, kilómetros, combustible) [fijo · ADN]
COMP-STOCK-GRID (fichas de unidad con sus cinco datos) [fijo · ADN]
COMP-TRADE-IN (tasa el tuyo, entra como entrada) [fijo · ADN]
COMP-FINANCE (cuota, entrada, plazo, TAE) [toggle]
COMP-TRUST-BADGES (garantía, revisión, transferencia incluida) [toggle]
COMP-TESTIMONIAL (quién ya compró) [toggle]
COMP-MAP-NAP (dónde está el patio y cuándo abre) [fijo]
COMP-FOOTER [fijo]
```

Ausencia intencional: sin carta de servicios, sin equipo, sin portfolio. Quien entra aquí no
quiere saber quién eres, quiere saber si tienes el coche.

## 3. Secciones

### COMP-HEADER `[fijo]`
Logo, nav corta, **teléfono clickeable** y un CTA que NO es "contacto": es **"Vender mi coche"**,
porque la mitad del negocio entra por ahí y el que quiere comprar ya va a bajar. Sticky.
Reutilizable: GLOBAL (variante local).

### COMP-SEARCH-FILTERS — el buscador `[fijo · ADN] · TGL-FILTER-DEPTH`
Objetivo: reducir cuarenta unidades a tres (descubrimiento). Cuatro controles como máximo —
marca, precio máximo, kilómetros máximos, combustible — y un contador de resultados que se ve
antes de pulsar nada. Mobile: apilado, el contador pegado al botón. Desktop: en una fila.
Reutilizable: SECCIÓN. **Los filtros son `<select>` y `<input>` de verdad**, no un desplegable
dibujado: en la maqueta se navega con teclado igual que en el build.

### COMP-STOCK-GRID — las unidades `[fijo · ADN] · TGL-STOCK-DENSITY`
Objetivo: comparar de un vistazo (decisión). Ficha con foto, marca y modelo, y **los cinco datos
en una fila de etiquetas**: año, kilómetros, combustible, cambio, potencia. El precio en grande y,
debajo, la cuota orientativa. Mobile: 1 columna. Desktop: 3. Reutilizable: SECCIÓN.
**El precio y la cuota viajan juntos y en ese orden.** Una cuota sin precio al lado es la técnica
de venta que hace que la gente desconfíe del patio entero.

### COMP-TRADE-IN — tasación `[fijo · ADN]`
Objetivo: capturar al que viene a vender (conversión). Matrícula y kilómetros, nada más — cada
campo extra en un formulario de tasación es un abandono, y el resto se pregunta por teléfono.
Reutilizable: SECCIÓN.

### COMP-FINANCE — la cuota `[toggle TGL-FINANCE]`
Objetivo: convertir un precio en una decisión mensual (decisión). Entrada, plazo y cuota
resultante, con **TAE y coste total visibles sin desplegar nada**. Reutilizable: SECCIÓN.
**Nota legal:** cifra orientativa; el build tiene que enlazar la información normalizada.

### COMP-TRUST-BADGES — garantías `[toggle TGL-BADGES]`
Objetivo: quitar el miedo del ocasión (confianza). Tres o cuatro hechos comprobables —meses de
garantía, puntos revisados, transferencia incluida—, nunca adjetivos. Reutilizable: GLOBAL.

### COMP-TESTIMONIAL `[toggle TGL-TESTIMONIALS]`
Objetivo: reputación (confianza). Reseñas con el modelo comprado al lado del nombre.
Reutilizable: GLOBAL.

### COMP-MAP-NAP `[fijo]`
Objetivo: que lleguen al patio. Dirección, horario y teléfono como TEXTO. Sin mapa embebido — un
iframe de un tercero es una petición externa antes de que nadie haya consentido. Reutilizable:
GLOBAL.

### COMP-FOOTER `[fijo]`
NAP completo, horarios, legal. Reutilizable: GLOBAL.

## 4. Toggles admitidos

| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-FILTER-DEPTH` | 4 controles | 2 si el stock es pequeño |
| `TGL-STOCK-DENSITY` | 3 columnas | 2 si las fotos son buenas |
| `TGL-FINANCE` | on | off si no hay financiera |
| `TGL-BADGES` | on | |
| `TGL-TESTIMONIALS` | on | |
| `TGL-CTA-STRENGTH` | alto | |

**Fijos:** COMP-HEADER, COMP-SEARCH-FILTERS, COMP-STOCK-GRID, COMP-TRADE-IN, COMP-MAP-NAP, COMP-FOOTER.
**Ausencias de ADN:** carta de servicios, equipo, portfolio → sugerir TPL-C-01 o TPL-C-09.

## 5. SEO / semántica
1 `H1` (el buscador nombra el stock, no el negocio). `H2` en Unidades, Tasación, Financiación,
Ubicación. Schema `AutoDealer` + un `Vehicle`/`Product` por ficha con `offers`, `mileageFromOdometer`
y `vehicleTransmission`. La rejilla en HTML de verdad — un stock que sólo existe dentro de un
script no se indexa y no se comparte. `header` > `main` > `footer`.
