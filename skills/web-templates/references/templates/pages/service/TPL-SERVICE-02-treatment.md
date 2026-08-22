# TPL-SERVICE-02 — Tratamiento (ficha)

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Tratamiento (ficha) |
| Objetivo | Que quien ya eligió el tratamiento sepa qué le pasa dentro, cuándo NO puede, y reserve |
| Ideal para | Centros de estética, cabinas, spas, depilación, uñas y pestañas, fisioterapia sin acto médico |
| Ejemplos | "Limpieza profunda", "Radiofrecuencia facial", "Depilación láser axilas", "Manicura rusa" |
| Nivel de contenido | Medio: un tratamiento contado en minutos, no un catálogo |
| Protagonismo | LA SESIÓN — qué pasa, cuánto dura, cómo sales — y CUÁNDO NO |
| ADN | Protocolo en minutos + ficha de datos (duración, sesiones recomendadas, cabina, cómo sales) + contraindicaciones + el bono al que pertenece. NO storytelling de marca, NO antes/después, NO catálogo. |

**Por qué existe habiendo TPL-SERVICE-01.** `TPL-SERVICE-01` es una landing comercial: abre con el
problema que resuelve, sigue con el alcance y cierra en formulario, y funciona porque un servicio
profesional se contrata **antes** de saber cómo se ejecuta. Un tratamiento es lo contrario: se
compra sabiendo el nombre y lo que falta es **qué pasa dentro de esa hora**. Ninguno de los huecos
de `TPL-SERVICE-01` sirve para el minutaje de una sesión, para la cabina en la que ocurre, ni para
la lista de cuándo NO se puede — y esa última no es un detalle de diseño: es la que evita que
alguien se presente embarazada, con la piel quemada por el sol o con un antibiótico fotosensible.

**Y NO lleva antes/después.** Es el lenguaje de `TPL-C-10`; en estética sin acto médico promete un
resultado clínico que nadie puede firmar, y la publicidad sanitaria lo tiene regulado. Lo que
sustituye a la prueba aquí es la **precisión**: los minutos reales y el estado en el que sales.

## 2. Wireframe (top → bottom)

```
COMP-HEADER [fijo]
COMP-BREADCRUMB (Inicio · Servicios · <este>) [fijo]
COMP-PAGE-HEAD [fijo · ADN] · H1 = el tratamiento como se busca + CTA reservar
COMP-TREATMENT-FACTS (duración · sesiones · cabina · cómo sales) [fijo · ADN]
COMP-PROTOCOL-STEPS (la sesión, minuto a minuto) [fijo · ADN]
COMP-CONTRAINDICATIONS (cuándo NO, y qué hacer antes) [fijo · ADN]
COMP-BONO-PACKS (el bono al que pertenece este ritual) [toggle · default ON]
COMP-FAQ (dudas de ESTE tratamiento) [toggle · default ON]
COMP-RELATED (los hermanos de su zona + ver la carta) [fijo]
COMP-BOOKING [fijo]
COMP-FOOTER [fijo]
```

Ausencia intencional: sin antes/después, sin catálogo completo, sin historia de marca. La carta
entera está a un clic en `COMP-RELATED`; repetirla aquí es ofrecer alternativas a quien ya eligió,
que es el defecto que `TPL-C-08` documenta desde el otro lado.

## 3. Secciones

### COMP-HEADER `[fijo]`
Reutilizable: GLOBAL.

### COMP-BREADCRUMB `[fijo]`
`Inicio · Servicios · <este>`, y el eslabón del medio apunta a `TPL-SERVICES-01`.
Reutilizable: GLOBAL. Schema `BreadcrumbList`.

### COMP-PAGE-HEAD `[fijo · ADN]`
Objetivo: confirmar que es esto y dejar reservar ya (conversión). `H1` con el nombre **como se
busca**, una línea de para qué es, el precio y el CTA. Una fotografía del tratamiento en curso, no
un retrato.
Mobile: 1 columna, imagen debajo. Desktop: dos tercios de texto, un tercio de imagen.
Reutilizable: GLOBAL.

### COMP-TREATMENT-FACTS — la ficha `[fijo · ADN]`
Objetivo: los cuatro datos que deciden si cabe en tu semana (decisión). **Duración**, **sesiones
recomendadas**, **en qué cabina** y **cómo sales**. Los cuatro, siempre, sin ninguno en blanco: una
ficha sin "cómo sales" es la llamada que la página iba a ahorrar, y una sin el número de sesiones
convierte un precio de 45 € en un compromiso de 225 € que nadie avisó.
Mobile: lista de 4 filas. Desktop: fila de 4 chips con el dato grande.
Reutilizable: SECCIÓN.

### COMP-PROTOCOL-STEPS — la sesión `[fijo · ADN]`
Objetivo: quitar la incertidumbre de la primera vez (confianza). Los pasos **con sus minutos
reales**, y la suma tiene que dar la duración de la ficha de arriba. Si no da, uno de los dos
números es mentira y se nota.
Mobile: lista vertical con filete a la izquierda. Desktop: fila de 4–5 con el minutaje grande.
Reutilizable: SECCIÓN (compartida con `TPL-C-14`).

### COMP-CONTRAINDICATIONS — cuándo NO `[fijo · ADN]`
Objetivo: que nadie venga a una sesión que no se le puede hacer (confianza, y responsabilidad).
Dos listas cortas y explícitas: **cuándo no se puede** —embarazo, sol reciente, piel irritada,
medicación fotosensible, según el tratamiento— y **qué hacer antes** —venir sin maquillaje, no
depilarse 48 h, comer algo—.
**Va en la página y no en el email de confirmación.** Quien lo lee después de reservar ya ha
bloqueado una cabina que no va a usar, y el centro se come el hueco.
Mobile: 1 columna, dos bloques. Desktop: 2 columnas. Reutilizable: SECCIÓN.
**Aviso:** el contenido real lo firma el centro. La maqueta pone la estructura, no la lista.

### COMP-BONO-PACKS `[toggle TGL-TREATMENT-BONO · default ON]`
Objetivo: convertir una sesión en cinco (conversión). El bono **de este ritual** con su precio
cerrado y su ahorro en euros. Off cuando el tratamiento no se repite.
Reutilizable: SECCIÓN (compartida con `TPL-C-14`).

### COMP-FAQ `[toggle TGL-FAQ · default ON]`
Dudas de ESTE tratamiento. `<details>`, **la primera abierta y ninguna más**. Reutilizable: GLOBAL.

### COMP-RELATED — los hermanos `[fijo]`
Objetivo: no dejar la ficha colgando de la home (navegación). Los otros rituales **de su misma
zona** más "ver la carta entera". Es FIJO y no un toggle, por la misma razón que en
`TPL-SERVICE-01`: sin enlaces entre hermanas cada ficha cuelga sola. Misma receta de tarjeta que el
resto del sitio. Reutilizable: GLOBAL.

### COMP-BOOKING `[fijo] · TGL-BOOKING`
Con **el ritual ya elegido** — es esta página, la recepción no debería tener que preguntarlo.
Reutilizable: GLOBAL.

### COMP-FOOTER `[fijo]`
Reutilizable: GLOBAL.

## 4. Toggles admitidos

| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-TREATMENT-BONO` | on | off si el tratamiento no se repite |
| `TGL-FAQ` | on | |
| `TGL-BOOKING` | form | o WhatsApp, o teléfono |

**Fijos:** COMP-HEADER, COMP-BREADCRUMB, COMP-PAGE-HEAD, COMP-TREATMENT-FACTS, COMP-PROTOCOL-STEPS, COMP-CONTRAINDICATIONS, COMP-RELATED, COMP-BOOKING, COMP-FOOTER.
**Ausencias de ADN:** antes/después, catálogo completo, historia de marca → `TPL-C-10`, `TPL-SERVICES-01` o `TPL-ABOUT-03`.

## 5. Multiplicación y contenido único
**Una por tratamiento**, y el arquetipo se replica pero el contenido no. Doce fichas con el mismo
protocolo cambiado de nombre son doce páginas que compiten entre ellas por la misma búsqueda y
ninguna gana. Lo que tiene que ser distinto en cada una, sí o sí: los minutos del protocolo, la
lista de contraindicaciones y la línea de "cómo sales". Si en dos fichas esos tres coinciden, **son
el mismo tratamiento con dos nombres comerciales** y hay que fundirlas o diferenciarlas de verdad.

## 6. SEO / semántica
1 `H1` (page head). `H2` en Ficha, Sesión, Cuándo no, Bono, FAQ y Reserva. Schema `Service` con
`provider` al `BeautySalon` de la home, `offers` con el precio, y `FAQPage` si la sección está on.
`header` > `main` > `footer`. **Publicidad:** ningún claim terapéutico; el copy real lo firma el
centro, no la maqueta.
