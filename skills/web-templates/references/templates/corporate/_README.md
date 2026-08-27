# Corporate templates (TPL-C-*)

Cuatro arquetipos activos, todos por **unidad de contenido**: el negocio publica una cosa concreta
que no entra en una tarjeta de servicio genérica. `design-system.md` y `toggles.md` son compartidos
(con toggles propios de corporate). El recomendador enruta por lo que el negocio publica, no por
objetivo declarado (ver `recommender.md` §3b).

Los tres primeros llevan su marca de referencia con bloque `[data-brand]` propio en la galería —
ground, acento, par tipográfico y duotono. `TPL-C-15` todavía no: su demo de marca (`delao`,
Inmobiliaria de la O) es una decisión humana pendiente (`PR3b`).

| ID | Nombre | Publica | Ideal para | Marca demo · ancla |
|----|--------|---------|-----------|--------------------|
| TPL-C-07 | Stock / Ocasión | Un inventario volátil de unidades | Concesionarios, ocasión, maquinaria, náutica | Aranda · INSTITUTIONAL |
| TPL-C-11 | Plan por fases | Un plan largo, en meses y cuotas | Ortodoncia, implantología, nutrición, entrenamiento | Alinea · EDITORIAL |
| TPL-C-14 | Ritual / Bono | Una carta de rituales por zona, que se compra en bono | Centros de estética, cabinas, spas urbanos, depilación, uñas | Lumière · MATTER |
| TPL-C-15 | Cartera curada | Una cartera curada de inmuebles de alto valor | Inmobiliarias de lujo residencial, promotoras boutique | Inmobiliaria de la O (`delao`) · pendiente `PR3b` |

## Disposición de los once retirados (`catalog-envato-grade`)

Ninguno respaldaba una demo D1; todos se retiraron sin dejar el arquetipo huérfano en el repo
(`catalog-wrapper-integrity` Requisito 5 exige que cada uno tenga disposición explícita, no un
barrido silencioso):

| ID retirado | Nombre | Disposición |
|---|---|---|
| TPL-C-01 | Services / Lead-Gen | Retirado, sin reemplazo. Un brief de leads B2B resuelve en bespoke (`lanes.md`) hasta que la demo de abogados (fase posterior) lo cubra |
| TPL-C-02 | Institutional / Trust | Retirado, sin reemplazo. Bespoke |
| TPL-C-03 | Portfolio / Showcase | Retirado, sin reemplazo. Bespoke |
| TPL-C-04 | Landing / Single-Offer | Retirado, sin reemplazo. Bespoke |
| TPL-C-05 | Local / Booking | Retirado, sin reemplazo. Bespoke |
| TPL-C-06 | Mesa / Carta | Retirado, sin reemplazo (respaldaba Casa Terrazza, brand retirada). Bespoke |
| TPL-C-08 | Modelo / Lanzamiento | Retirado, sin reemplazo (respaldaba Auria, brand retirada). Bespoke |
| TPL-C-09 | Taller / Tarifa | Retirado, sin reemplazo (respaldaba Taller Bergara, brand retirada). Bespoke |
| TPL-C-10 | Clínica / Tratamientos | Retirado, sin reemplazo (respaldaba Clínica Arbea, brand retirada). Bespoke |
| TPL-C-12 | Urgencias / Hoy | Retirado, sin reemplazo (respaldaba Urgencia Dental, brand retirada). Bespoke |
| TPL-C-13 | Cartera / Búsqueda | **Retirado CON reemplazo nombrado**: `TPL-C-15 · Cartera curada`, que ya aterrizó en esta misma iniciativa con su propio `TGL-HERO-MODE` (`buscador-portada` \| `retrato`). `TPL-PROPERTY-01` (la ficha de inmueble) sobrevive sin cambios y se re-engancha a `TPL-C-15` |

## Diferenciación real entre los cuatro activos

- **C-07** es el único con INVENTARIO: los filtros van arriba porque filtrar es la primera intención.
- **C-11** tiene línea de tiempo con meses reales — no un `COMP-PROCESS` de cuatro pasos genéricos —
  y un final declarado: el plan TERMINA.
- **C-14** publica el **efecto al salir** junto a la duración y el precio, y cierra en BONO y no en
  cita: es el único cuya unidad de compra son varias visitas sin fecha de término. Frente a C-11: el
  plan de C-11 termina, la carta de C-14 se repite indefinidamente.
- **C-15** es el único que publica una CARTERA en vez de un servicio: no hay cita ni sesión, hay una
  vivienda que se enseña por fotografía antes que por filtro, y la conversión doble (comprador /
  propietario que quiere vender) no tiene equivalente en los otros tres.

## Componentes corporate reutilizables

**De los cuatro activos**: `COMP-SEARCH-FILTERS`, `COMP-STOCK-GRID`, `COMP-TRADE-IN`, `COMP-FINANCE`,
`COMP-PHASE-TIMELINE`, `COMP-INSURANCE`, `COMP-ZONE-SELECTOR`, `COMP-RITUAL-MENU`,
`COMP-CABIN-TOUR`, `COMP-PROTOCOL-STEPS`, `COMP-BONO-PACKS`, `COMP-SEARCH-BAND`,
`COMP-FEATURED-GRID`, `COMP-MAP-SEARCH`, `COMP-VALUATION-CTA`.

Compartidos: `COMP-HEADER` / `COMP-HERO` / `COMP-FOOTER` / `COMP-TESTIMONIAL` /
`COMP-TRUST-BADGES` / `COMP-RELATED`.

## Páginas internas

Ninguno de los cuatro arrastra una `TPL-SERVICE-01` genérica por defecto (esa herencia era de
C-01/C-02, retirados). C-07 arrastra **una `TPL-UNIT-01` por unidad en patio** (obligatoria — su
rejilla dice «ver ficha»). C-14 arrastra `TPL-SERVICES-01` + **una `TPL-SERVICE-02` por ritual**.
C-15 arrastra **una `TPL-PROPERTY-01` por inmueble en cartera** (obligatoria, mismo argumento que
C-07: su rejilla dice «ver ficha»). Los tres no negociables (`TPL-LEGAL-01` ×4, `TPL-404-01`, y
`TPL-THANKS-01` si hay formulario) entran siempre. Detalle: `recommender.md` §6.
