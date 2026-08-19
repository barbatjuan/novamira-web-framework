# Corporate templates (TPL-C-*)

Doce arquetipos corporativos en **dos familias**. Misma mecánica en las dos: secciones FIJAS (ADN) /
TOGGLE, `design-system.md` y `toggles.md` compartidos (con toggles propios de corporate). El
recomendador elige por perfil, y **pregunta primero por la familia B** (ver `recommender.md` §3b).

## Familia A — por OBJETIVO (`TPL-C-01..05`)

Para negocios que venden algo que el dueño describe una vez y no cambia.

| ID | Nombre | Ideal para | Hero | Foco |
|----|--------|-----------|------|------|
| TPL-C-01 | Services / Lead-Gen | Consultoras, agencias, servicios pro | Headline + form/CTA ~50vh | Generación de leads |
| TPL-C-02 | Institutional / Trust | Empresas, salud, legal, financiero | Imagen fija + claim ~45vh | Autoridad / confianza |
| TPL-C-03 | Portfolio / Showcase | Estudios creativos, arquitectura, foto | Visual grande ~60vh | Mostrar el trabajo |
| TPL-C-04 | Landing / Single-Offer | Una oferta única, campañas, SaaS | Propuesta + CTA fuerte ~55vh | Conversión de 1 oferta |
| TPL-C-05 | Local / Booking | Clínicas, gastronómico, servicios locales | Imagen + CTA reserva ~45vh | Reserva + ubicación |

## Familia B — por UNIDAD DE CONTENIDO (`TPL-C-06..12`)

Existen porque el negocio **publica una cosa concreta** que no entra en una tarjeta de servicio.
Cada una lleva además su marca de referencia con bloque `[data-brand]` propio en la galería —
ground, acento, par tipográfico y duotono.

| ID | Nombre | Publica | Ideal para | Marca demo · ancla |
|----|--------|---------|-----------|--------------------|
| TPL-C-06 | Mesa / Carta | Una carta con precios y quien la firma | Restaurantes, asadores, bares con cocina, vinotecas | Terrazza · EDITORIAL |
| TPL-C-07 | Stock / Ocasión | Un inventario volátil de unidades | Concesionarios, ocasión, maquinaria, náutica | Aranda · INSTITUTIONAL |
| TPL-C-08 | Modelo / Lanzamiento | Un solo objeto caro, contado entero | Concesión oficial, lanzamiento, piso piloto | Auria · DIRECT |
| TPL-C-09 | Taller / Tarifa | Una tarifa con importes por trabajo | Talleres, chapa, neumáticos, reparación técnica | Bergara · MATTER |
| TPL-C-10 | Clínica / Tratamientos | Tratamientos con datos duros y quien los hace | Dental, dermatología, fisio, podología, veterinaria | Arbea · INSTITUTIONAL |
| TPL-C-11 | Plan por fases | Un plan largo, en meses y cuotas | Ortodoncia, implantología, nutrición, entrenamiento | Alinea · EDITORIAL |
| TPL-C-12 | Urgencias / Hoy | Disponibilidad AHORA | Guardia dental, cerrajería, fontanería, grúa, 24 h | Urgencia Dental · DIRECT |

## Diferenciación real (no mismo template)

**Dentro de la familia A**
- **C-01** vive del formulario/lead; **C-02** del prestigio y las cifras (stats fijos), CTA calmo.
- **C-03** es casi solo visual (portfolio grid), sin pricing ni stats ni forms largos.
- **C-04** es una secuencia lineal a UN CTA, sin menú ni catálogo.
- **C-05** es el único de la familia A con booking + mapa/NAP + horarios como ADN.

**Dentro de la familia B**
- **C-06** no tiene `COMP-SERVICES`: lo que se hace ya está en la carta. Y la reserva es la
  penúltima sección, no la tercera — la carta y la cara de quien cocina deciden antes.
- **C-07** es el único con INVENTARIO: los filtros van arriba porque filtrar es la primera intención.
- **C-08** es lo contrario de C-07: una unidad, mirada despacio. Un buscador aquí sería ofrecer
  alternativas a quien ya eligió.
- **C-09** publica el precio cerrado, que es justo lo que un taller suele esconder.
- **C-10** publica el PROCEDIMIENTO (duración, sesiones, anestesia) y el número de colegiado,
  porque en una consulta sanitaria el miedo no es el precio.
- **C-11** tiene línea de tiempo con meses reales — no un `COMP-PROCESS` de cuatro pasos genéricos.
- **C-12** **no tiene `COMP-HERO`**, y esa ausencia es su decisión de diseño principal.

**Por qué la familia B no colapsa en la A.** Un taller y una clínica son los dos "local con cita" y
caerían los dos en C-05, que enseña el local y esconde lo que el cliente vino a saber. Cada
documento de la familia B abre con **"Por qué existe habiendo TPL-C-0X"**: ante la duda, la
respuesta está ahí. Vecindades: `C-06`↔`C-05` · `C-07`↔`C-08` · `C-08`↔`C-04` · `C-09`↔`C-05` ·
`C-10`↔`C-05`/`C-09` · `C-11`↔`C-10` · `C-12`↔`C-05`.

## Componentes corporate reutilizables

**Familia A**: `COMP-SERVICES`, `COMP-LEAD-FORM`, `COMP-PROCESS`, `COMP-CASES`, `COMP-LOGOS`,
`COMP-STATS`, `COMP-TEAM`, `COMP-PORTFOLIO-GRID`, `COMP-FEATURES`, `COMP-PRICING`, `COMP-FAQ`,
`COMP-BOOKING`, `COMP-MAP-NAP`, `COMP-GALLERY`, `COMP-CTA`, `COMP-CREDENTIALS`, `COMP-PROBLEM`,
`COMP-SOLUTION`.

**Familia B** (estrenados por las verticales): `COMP-HERO-FULL`, `COMP-MARQUEE`, `COMP-MENU-LIST`,
`COMP-FIGURE-QUOTE`, `COMP-HOURS-BLOCK`, `COMP-SEARCH-FILTERS`, `COMP-STOCK-GRID`, `COMP-TRADE-IN`,
`COMP-FINANCE`, `COMP-MODEL-HERO`, `COMP-SPEC-TABLE`, `COMP-OFFER-STRIP`, `COMP-PRICE-LIST`,
`COMP-TREATMENT-CARDS`, `COMP-BEFORE-AFTER`, `COMP-INSURANCE`, `COMP-PHASE-TIMELINE`,
`COMP-URGENT-BAR`, `COMP-SYMPTOM-TRIAGE`, `COMP-WAIT-PROMISE`.

Compartidos: `COMP-HEADER` / `COMP-HERO` / `COMP-FOOTER` / `COMP-TESTIMONIAL` /
`COMP-TRUST-BADGES` / `COMP-RELATED`.

## Páginas internas

Las de la familia A con `COMP-SERVICES` (C-01, C-02) arrastran **una `TPL-SERVICE-01` por
servicio**; C-03 arrastra `TPL-PROJECT-01`. Las de la familia B publican su unidad de contenido en
la propia home y **no** arrastran páginas de servicio por defecto — con dos excepciones que se
preguntan, no se asumen: C-09 por línea de trabajo y C-10 por tratamiento. Los tres no negociables
(`TPL-LEGAL-01` ×4, `TPL-404-01`, y `TPL-THANKS-01` si hay formulario) entran siempre. Detalle:
`recommender.md` §6.
