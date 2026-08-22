# TPL-SERVICES-01 — Índice de servicios

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Índice de servicios |
| Objetivo | Enseñar TODO lo que se hace, agrupado como el cliente lo busca, y mandar a cada ficha |
| Ideal para | Cualquier corporate con más servicios de los que caben en la home |
| Ejemplos | "Servicios", "Tratamientos", "Carta", "Áreas de práctica", "Qué hacemos" |
| Nivel de contenido | Alto en anchura, bajo en profundidad: muchas entradas, dos líneas cada una |
| Protagonismo | LA LISTA COMPLETA y su agrupación. Ni la marca ni un servicio concreto compiten aquí. |
| ADN | Un índice agrupado + una tarjeta por servicio que enlaza a SU ficha. NO storytelling de marca, NO un servicio destacado sobre los demás, NO precios largos. |

**Por qué existe.** `TPL-SERVICE-01` abre con la miga `Inicio · Servicios · <este>` desde el día que
se escribió, y esa página intermedia **no existía en el catálogo**: el enlace del medio no llevaba a
ningún arquetipo. La home enseña seis entradas como mucho —lo que su toggle de conteo permita— y un
negocio con dieciocho tratamientos o nueve áreas de práctica no tiene dónde ponerlas. Sin esta
página el sitio pierde la búsqueda de categoría ("centro de estética <ciudad>", "abogados
<materia>") y, peor, obliga a la home a elegir qué se esconde.

**Es un ÍNDICE, no una home segunda.** La tentación —y el defecto que ya se ha visto— es rellenarla
con las secciones de confianza de la home: equipo, testimonios, cifras, historia. Quien llega aquí
ya decidió que le interesa el negocio y está buscando **cuál**. Todo lo que no ayude a elegir entre
entradas es scroll entre ella y la ficha que iba a abrir.

## 2. Wireframe (top → bottom)

```
COMP-HEADER [fijo]
COMP-BREADCRUMB (Inicio · Servicios) [fijo]
COMP-PAGE-HEAD [fijo · ADN] · H1 = lo que el cliente busca, no "Nuestros servicios"
COMP-SERVICE-INDEX (todas las entradas, agrupadas) [fijo · ADN]
COMP-FEATURES (qué incluye trabajar aquí, común a todas) [toggle · default OFF]
COMP-FAQ (dudas que son de la CARTA, no de un servicio) [toggle · default ON]
COMP-CTA + COMP-LEAD-FORM [fijo]
COMP-FOOTER [fijo]
```

Ausencia intencional: equipo, testimonios, cifras e historia de marca. Todo eso vive en la home y en
Nosotros; aquí es distancia entre el visitante y la ficha que venía a abrir.

## 3. Secciones

### COMP-HEADER `[fijo]`
Reutilizable: GLOBAL, idéntico al de la home.

### COMP-BREADCRUMB `[fijo]`
`Inicio · Servicios`. Reutilizable: GLOBAL. Schema `BreadcrumbList`.

### COMP-PAGE-HEAD `[fijo · ADN]`
Objetivo: decir de qué va la lista y **cómo está ordenada** (navegación). El `H1` es el término por
el que se busca —"Tratamientos de estética", "Áreas de práctica"— y no el genérico "Nuestros
servicios", que no lo busca nadie. Debajo, una línea que nombra el criterio de agrupación, porque un
índice cuyo orden no se explica se lee como una lista desordenada.
Mobile: 1 columna. Desktop: encabezado con la medida capada a 66ch. Reutilizable: GLOBAL.

### COMP-SERVICE-INDEX — el índice `[fijo · ADN] · TGL-SERVICES-GROUP, TGL-SERVICES-FACTS`
Objetivo: que se pueda comparar y elegir sin abrir nada (decisión). **Todas** las entradas, sin
paginar y sin destacar ninguna. Cada tarjeta: imagen, nombre, dos líneas y el enlace a su ficha; y,
si el negocio publica un dato que decide, **hasta tres hechos por tarjeta** heredados de la unidad de
contenido de la home —duración y precio en un centro de estética, plazo en un taller, nada en un
despacho—.
**La agrupación es la decisión de esta página.** `TGL-SERVICES-GROUP` la resuelve: por zona (cuerpo,
rostro…), por área (materia, especialidad) o sin agrupar cuando hay menos de seis entradas. Cada
grupo lleva su `H2` y su ancla, para que el índice de zonas de la home pueda apuntar aquí en vez de
a un scroll a ciegas.
**Una sola receta de tarjeta en todo el sitio.** La de la home y la de aquí son la misma; inventar
una segunda es cómo un sitio empieza a tener dos diseños.
Mobile: 1 columna. Tablet: 2. Desktop: 3. Reutilizable: SECCIÓN.

### COMP-FEATURES `[toggle TGL-SERVICES-COMMON · default OFF]`
Objetivo: lo que es verdad para TODAS las entradas y por tanto no cabe en ninguna (confianza).
Garantía, material, forma de pagar. **Default OFF**: en la mayoría de los negocios esto es relleno,
y encendido sin contenido real empuja el índice bajo el pliegue.
Mobile: 2×2. Desktop: fila de 3–4. Reutilizable: SECCIÓN.

### COMP-FAQ `[toggle TGL-FAQ · default ON]`
Objetivo: resolver lo que impide elegir (decisión). Dudas **de la carta** —cómo se reserva, si se
puede cambiar, si hay que venir antes— y no de un servicio concreto: ésas van en su ficha.
`<details>`, **la primera fila abierta y ninguna más**. Reutilizable: GLOBAL.

### COMP-CTA + COMP-LEAD-FORM `[fijo]`
Objetivo: recoger a quien no supo elegir (conversión). Quien llega al final del índice sin abrir una
ficha necesita que alguien le diga cuál; el formulario pregunta eso y no "cuéntanos tu proyecto".
Reutilizable: GLOBAL.

### COMP-FOOTER `[fijo]`
Reutilizable: GLOBAL.

## 4. Toggles admitidos

| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-SERVICES-GROUP` | por área | por zona / por área / sin agrupar (<6 entradas) |
| `TGL-SERVICES-FACTS` | on | off si el negocio no publica un dato que decida |
| `TGL-SERVICES-COMMON` | off | on solo si hay algo verdadero para todas |
| `TGL-FAQ` | on | |

**Fijos:** COMP-HEADER, COMP-BREADCRUMB, COMP-PAGE-HEAD, COMP-SERVICE-INDEX, COMP-CTA, COMP-LEAD-FORM, COMP-FOOTER.
**Ausencias de ADN:** equipo, testimonios, cifras, historia → eso es la home o `TPL-ABOUT-0X`.

## 5. Multiplicación y contenido único
Esta página es **una por sitio**, nunca una por grupo: un índice partido en cuatro páginas es cuatro
páginas compitiendo por la misma búsqueda. Los grupos son anclas dentro de ella.

## 6. SEO / semántica
1 `H1` (page head). Un `H2` por grupo, con `id` para el ancla. Cada tarjeta enlaza a su
`TPL-SERVICE-01` o `TPL-SERVICE-02` con el nombre del servicio como texto del enlace — nunca "ver
más", que es el mismo texto en dieciocho enlaces distintos. Schema `ItemList` con un `Service` por
entrada. `header` > `main` > `footer`.
