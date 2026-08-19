# TPL-C-08 — Modelo / Lanzamiento

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Modelo / Lanzamiento |
| Objetivo | Que el usuario reserve una prueba de UNA unidad concreta |
| Ideal para | Concesión oficial de una marca, lanzamiento de un modelo, un producto caro y único |
| Ejemplos | Presentación de un coche, una moto, una caravana, una máquina, un piso piloto |
| Nivel de contenido | Bajo y PROFUNDO: un solo producto contado entero |
| Protagonismo | El OBJETO a pantalla completa y su ficha técnica; el precio llega tarde y a propósito |
| ADN | Un héroe de producto con tira de datos + versiones comparadas + oferta con fecha + prueba de conducción. NO inventario, NO servicios, NO equipo. |

**Por qué existe habiendo TPL-C-07.** TPL-C-07 tiene cuarenta unidades y su trabajo es
descartarlas hasta tres. Éste tiene UNA, y su trabajo es que la mires despacio. Son intenciones
opuestas: un buscador arriba aquí sería ofrecer alternativas a alguien que ya eligió. Y frente a
TPL-C-04, que es la landing de oferta única, la diferencia es el OBJETO: C-04 argumenta con
problema, solución y precio; éste argumenta con fotografía a sangre y una tabla de versiones,
porque lo que se vende se mira antes de razonarse.

## 2. Wireframe (top → bottom)

```
COMP-HEADER (transparente sobre el héroe) [fijo]
COMP-MODEL-HERO (el objeto a sangre + tira de cuatro datos) [fijo · ADN]
COMP-SPEC-TABLE (versiones en columnas, diferencias marcadas) [fijo · ADN]
COMP-GALLERY (exterior, interior, detalle) [toggle]
COMP-OFFER-STRIP (la oferta y hasta cuándo) [fijo · ADN]
COMP-FINANCE (entrada, cuota, plazo) [toggle]
COMP-BOOKING (prueba de conducción) [fijo · ADN]
COMP-FAQ (entrega, garantía, permuta) [toggle]
COMP-FOOTER [fijo]
```

El orden es el argumento: se mira, se compara la versión, se ve el plazo, se reserva la prueba.
Ausencia intencional: sin inventario, sin servicios, sin equipo, sin reseñas — una página de un
solo producto que además trae opiniones de otros productos se lee como un catálogo disfrazado.

## 3. Secciones

### COMP-HEADER `[fijo]`
Transparente y superpuesto al héroe, sólido al bajar. Logo, nav corta y CTA "Reservar prueba".
Reutilizable: GLOBAL (variante superpuesta).

### COMP-MODEL-HERO — el objeto `[fijo · ADN] · TGL-HERO-HEIGHT`
Objetivo: deseo antes que dato. Fotografía a sangre del producto, el nombre del modelo como H1 y
**una tira de exactamente cuatro cifras bajo el pliegue** — potencia, autonomía, plazas, precio
desde. Cuatro y no seis: la tira es un titular, no la ficha, y la ficha ya viene debajo.
Mobile: ~70vh, la tira en dos filas de dos. Reutilizable: SECCIÓN.

### COMP-SPEC-TABLE — versiones `[fijo · ADN] · TGL-SPEC-ROWS`
Objetivo: elegir acabado (decisión). Dos o tres versiones en columnas y las filas de
características; **lo que cambia entre versiones va marcado y lo que se repite se atenúa**, porque
una tabla en la que todo pesa igual no ayuda a decidir nada. Mobile: una versión por pantalla con
desplazamiento horizontal y la primera columna fija. Reutilizable: SECCIÓN.

### COMP-GALLERY — cómo es `[toggle TGL-GALLERY]`
Objetivo: mirar (deseo). Exterior, interior y detalle. Reutilizable: GLOBAL.

### COMP-OFFER-STRIP — la oferta `[fijo · ADN]`
Objetivo: dar un motivo para hoy (conversión). Una banda con la condición y **una fecha real de
final**. Sin cuenta atrás animada: un contador que se reinicia al recargar es una mentira que el
visitante detecta a la segunda visita. Reutilizable: SECCIÓN.

### COMP-FINANCE `[toggle TGL-FINANCE]`
Entrada, plazo, cuota, TAE y coste total. Reutilizable: SECCIÓN (compartida con TPL-C-07).

### COMP-BOOKING — prueba de conducción `[fijo · ADN] · TGL-BOOKING`
Objetivo: la única conversión de la página. Día y hora como grupos de radio visibles.
Reutilizable: GLOBAL.

### COMP-FAQ `[toggle TGL-FAQ]`
Entrega, garantía, permuta, matriculación. Reutilizable: GLOBAL.

### COMP-FOOTER `[fijo]`
Reutilizable: GLOBAL.

## 4. Toggles admitidos

| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-HERO-HEIGHT` | 78vh | ADN: a sangre |
| `TGL-SPEC-ROWS` | 6 filas | |
| `TGL-GALLERY` | on | |
| `TGL-FINANCE` | on | |
| `TGL-BOOKING` | form | o enlace a la marca |
| `TGL-FAQ` | on | |

**Fijos:** COMP-HEADER, COMP-MODEL-HERO, COMP-SPEC-TABLE, COMP-OFFER-STRIP, COMP-BOOKING, COMP-FOOTER.
**Ausencias de ADN:** inventario, servicios, equipo, reseñas → sugerir TPL-C-07 o TPL-C-01.

## 5. SEO / semántica
1 `H1` (el modelo). `H2` en Versiones, Oferta, Prueba. Schema `Product` o `Vehicle` con `offers`,
`priceValidUntil` coincidente con la fecha impresa en la banda, y `AggregateOffer` si hay versiones.
La tabla de versiones en `<table>` de verdad, con `<th scope>`: una tabla hecha de `div` es
ilegible con lector de pantalla y no se entiende fuera de su CSS. `header` > `main` > `footer`.
