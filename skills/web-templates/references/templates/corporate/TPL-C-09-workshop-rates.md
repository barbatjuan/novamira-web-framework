# TPL-C-09 — Taller / Tarifa

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Taller / Tarifa |
| Objetivo | Que el usuario deje el vehículo sabiendo lo que va a pagar |
| Ideal para | Talleres mecánicos, chapa y pintura, neumáticos, reparación técnica, servicio a domicilio |
| Ejemplos | Taller multimarca, electricista, reparación de electrodomésticos, informática |
| Nivel de contenido | Medio: una tarifa larga y un proceso corto |
| Protagonismo | El PRECIO CERRADO por trabajo y el proceso que explica por qué es cerrado |
| ADN | Tarifa con precio por trabajo + proceso de tres pasos + cita + garantía. NO galería, NO equipo, NO stock. |

**Por qué existe habiendo TPL-C-05.** TPL-C-05 es el negocio con puerta: te enseña el local, te
cuenta qué se hace y te pone a reservar. Vale para una peluquería porque ahí el precio se sabe. En
un taller el precio es exactamente lo que NO se sabe, y ése es el miedo que hace que la gente
posponga la reparación. Así que aquí la tarifa no es una sección más: es la segunda cosa de la
página, va con importes reales, y el proceso existe para explicar qué pasa cuando el trabajo se
sale de la tarifa. Un taller que enseña fotos bonitas del local y esconde los precios está
respondiendo a una pregunta que nadie hizo.

## 2. Wireframe (top → bottom)

```
COMP-HEADER (con teléfono + CTA "Pedir cita") [fijo]
COMP-HERO (claim de precio, no de marca) [fijo]
COMP-PRICE-LIST (tarifa por trabajo, con importe) [fijo · ADN]
COMP-PROCESS (traes · diagnosticamos · apruebas · reparamos) [fijo · ADN]
COMP-BOOKING (cita con día y hora) [fijo]
COMP-TRUST-BADGES (garantía, piezas, factura) [toggle]
COMP-FAQ (qué pasa si sale más caro) [toggle]
COMP-MAP-NAP [fijo]
COMP-FOOTER [fijo]
```

Ausencia intencional: sin galería, sin equipo, sin stock. El héroe NO lleva fotografía de portada
a sangre: lleva una cifra.

## 3. Secciones

### COMP-HEADER `[fijo]`
Logo, nav corta, **teléfono clickeable** y CTA "Pedir cita". Sticky. Reutilizable: GLOBAL.

### COMP-HERO — el claim es un precio `[fijo] · TGL-HERO-HEIGHT`
Objetivo: quitar el miedo en la primera pantalla (confianza). El H1 lleva la promesa de precio —
*"Diagnóstico 39 €, y se descuenta si reparas aquí"*— y al lado una fotografía contenida, no a
sangre. Mobile: ~45vh. Reutilizable: SECCIÓN. **Deliberadamente sobrio:** un taller que abre con
una fotografía épica a pantalla completa está gastando la primera pantalla en algo que el cliente
no está decidiendo.

### COMP-PRICE-LIST — la tarifa `[fijo · ADN] · TGL-PRICE-GROUPS`
Objetivo: que el precio deje de ser una llamada (decisión). Grupos —mantenimiento, frenos,
neumáticos, diagnosis— y dentro de cada uno el trabajo, qué incluye en una línea, y **el importe
cerrado o el "desde" con el motivo escrito al lado**. Sin fotos por línea. Mobile: 1 columna.
Desktop: 2. Reutilizable: SECCIÓN.
**"Desde" siempre lleva su porqué.** Un "desde 49 €" sin explicar de qué depende es peor que no
publicar precio: el visitante asume lo peor y además pierde la confianza que la tarifa venía a dar.

### COMP-PROCESS — cómo funciona `[fijo · ADN]`
Objetivo: explicar el precio cerrado (confianza). Cuatro pasos numerados, y el tercero es
**"apruebas"**: la promesa de que nadie toca nada sin llamar antes. Reutilizable: GLOBAL.

### COMP-BOOKING — cita `[fijo] · TGL-BOOKING`
Día y hora como radios visibles + matrícula. Reutilizable: GLOBAL.

### COMP-TRUST-BADGES `[toggle TGL-BADGES]`
Meses de garantía, origen de las piezas, factura con desglose. Hechos, no adjetivos.
Reutilizable: GLOBAL.

### COMP-FAQ `[toggle TGL-FAQ]`
La primera pregunta es siempre *"¿y si al abrirlo sale más caro?"*. Reutilizable: GLOBAL.

### COMP-MAP-NAP `[fijo]`
Dirección, horario y teléfono como texto. Reutilizable: GLOBAL.

### COMP-FOOTER `[fijo]`
Reutilizable: GLOBAL.

## 4. Toggles admitidos

| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-HERO-HEIGHT` | 45vh | ADN: contenido, no a sangre |
| `TGL-PRICE-GROUPS` | 4 grupos | |
| `TGL-BOOKING` | form | o WhatsApp |
| `TGL-BADGES` | on | |
| `TGL-FAQ` | on | |

**Fijos:** COMP-HEADER, COMP-HERO, COMP-PRICE-LIST, COMP-PROCESS, COMP-BOOKING, COMP-MAP-NAP, COMP-FOOTER.
**Ausencias de ADN:** galería, equipo, inventario → sugerir TPL-C-05 o TPL-C-07.

## 5. SEO / semántica
1 `H1` (hero, con la cifra dentro). `H2` en Tarifa, Cómo funciona, Cita, Ubicación. Schema
`AutoRepair`/`LocalBusiness` + `OfferCatalog` con un `Offer` por trabajo y su `price`, +
`OpeningHoursSpecification`. La tarifa en HTML y nunca en PDF: un PDF de precios no se indexa, no
se lee con lector de pantalla y envejece sin que nadie lo note. `header` > `main` > `footer`.
