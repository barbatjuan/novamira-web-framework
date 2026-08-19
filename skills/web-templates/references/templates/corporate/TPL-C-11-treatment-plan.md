# TPL-C-11 — Plan por fases

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Plan por fases |
| Objetivo | Que el usuario entienda un tratamiento LARGO y lo empiece |
| Ideal para | Ortodoncia, implantología, nutrición, entrenamiento personal, psicoterapia, coaching |
| Ejemplos | Ortodoncia invisible, plan de pérdida de peso, preparación de oposición, rehabilitación |
| Nivel de contenido | Bajo y SECUENCIAL: pocas secciones y una línea de tiempo larga |
| Protagonismo | LA LÍNEA DE TIEMPO con meses reales y el precio como cuota mensual |
| ADN | Problema + fases con meses + planes con cuota + antes/después. NO fichas de tratamiento sueltas, NO equipo, NO tarifa. |

**Por qué existe habiendo TPL-C-10.** TPL-C-10 vende una consulta: seis tratamientos, elige uno.
Aquí sólo hay UNO y dura dieciocho meses, así que la pregunta no es «cuál» sino «cuánto tiempo,
cuánto duele y cuánto al mes». Un catálogo de fichas sería la respuesta equivocada a las tres.
La línea de tiempo con meses reales es la sección que este arquetipo tiene y ningún otro: no es un
proceso de cuatro pasos genéricos —eso es `COMP-PROCESS`— sino un calendario con duraciones que el
cliente puede contrastar con su vida.

## 2. Wireframe (top → bottom)

```
COMP-HEADER (con CTA "Estudio gratuito") [fijo]
COMP-HERO-FULL (a sangre, una persona a mitad de tratamiento) [fijo · ADN]
COMP-PROBLEM (qué te molesta hoy, en sus palabras) [fijo]
COMP-PHASE-TIMELINE (fases con meses y qué pasa en cada una) [fijo · ADN]
COMP-PRICING (tres planes, cuota mensual y total) [fijo · ADN]
COMP-BEFORE-AFTER (dos fotos fechadas) [toggle]
COMP-FAQ (dolor, comer, hablar, olvidarse) [toggle]
COMP-BOOKING (estudio inicial) [fijo]
COMP-FOOTER [fijo]
```

Ausencia intencional: sin fichas de tratamiento sueltas, sin equipo, sin tarifa. Un plan largo se
vende explicando el CAMINO, no el catálogo.

## 3. Secciones

### COMP-HEADER `[fijo]`
Transparente sobre el héroe, sólido al bajar. CTA "Estudio gratuito". Reutilizable: GLOBAL.

### COMP-HERO-FULL — a sangre `[fijo · ADN] · TGL-HERO-HEIGHT`
Objetivo: identificación. Una persona A MITAD de tratamiento, no al final: quien está decidiendo se
reconoce en el proceso, no en el resultado, y una boca perfecta en la primera pantalla se lee como
publicidad. ~72vh, copy en el tercio inferior sobre velo medido. Reutilizable: SECCIÓN.

### COMP-PROBLEM — qué te molesta `[fijo]`
Objetivo: que el visitante se vea escrito (identificación). Tres o cuatro frases EN PRIMERA
PERSONA, tal y como se dicen en una consulta. Reutilizable: GLOBAL.

### COMP-PHASE-TIMELINE — el calendario `[fijo · ADN] · TGL-PHASE-COUNT`
Objetivo: convertir dieciocho meses en algo que se puede planificar (decisión). Cuatro o cinco
fases, cada una con **su duración en meses**, qué se hace y qué se nota. Mobile: vertical con la
línea a la izquierda. Desktop: horizontal con las duraciones proporcionales.
**Las duraciones se suman y el total se imprime.** Una línea de fases cuyos meses no cuadran con el
titular es la contradicción que un cliente detecta en la reunión de revisión, y siempre delante de
alguien. Reutilizable: SECCIÓN.

### COMP-PRICING — planes `[fijo · ADN] · TGL-PRICING-PLANS`
Objetivo: cerrar el "cuánto" (decisión). Dos o tres planes con **cuota mensual Y precio total**,
nunca sólo la cuota. Reutilizable: GLOBAL.
La cuota sin el total es la técnica que hace que la gente pregunte el total por teléfono
desconfiando, y la desconfianza cuesta más que la diferencia.

### COMP-BEFORE-AFTER `[toggle TGL-CASES]`
Dos fotografías fechadas del mismo caso. Reutilizable: SECCIÓN (compartida con TPL-C-10).

### COMP-FAQ `[toggle TGL-FAQ]`
Dolor, comer, hablar, y qué pasa si se olvida de ponérselo. Reutilizable: GLOBAL.

### COMP-BOOKING — estudio inicial `[fijo] · TGL-BOOKING`
Reutilizable: GLOBAL.

### COMP-FOOTER `[fijo]`
Reutilizable: GLOBAL.

## 4. Toggles admitidos

| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-HERO-HEIGHT` | 72vh | ADN: a sangre |
| `TGL-PHASE-COUNT` | 4 fases | |
| `TGL-PRICING-PLANS` | 3 planes | |
| `TGL-CASES` | on | |
| `TGL-FAQ` | on | |
| `TGL-BOOKING` | form | |

**Fijos:** COMP-HEADER, COMP-HERO-FULL, COMP-PROBLEM, COMP-PHASE-TIMELINE, COMP-PRICING, COMP-BOOKING, COMP-FOOTER.
**Ausencias de ADN:** fichas de tratamiento, equipo, tarifa → sugerir TPL-C-10.

## 5. SEO / semántica
1 `H1` (hero). `H2` en Fases, Planes, Cita. Schema `MedicalProcedure` con `followup` +
`Offer` por plan con `priceSpecification` mensual. La línea de tiempo en `<ol>` de verdad: es una
secuencia y el orden ES el contenido. `header` > `main` > `footer`.
