# TPL-SERVICE-01 — Servicio / Área (detalle)

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Servicio / Área (detalle) |
| Objetivo | Vender UN servicio concreto y captar la consulta de quien ya sabe qué necesita |
| Ideal para | Corporate con varios servicios o áreas: despachos, consultoras, agencias, clínicas, estudios |
| Ejemplos | "Abogado laboral", "Auditoría de cuentas", "Reforma integral", "Fisioterapia deportiva" |
| Nivel de contenido | Medio (un servicio en profundidad, no un catálogo) |
| Protagonismo | EL SERVICIO. Ni la marca ni el resto del catálogo compiten aquí. |
| ADN | Un servicio, un `H1`, un CTA. Enlazado a las áreas hermanas obligatorio. NO catálogo, NO portfolio grande. |

Es la **landing de entrada de SEO**: la búsqueda comercial ("<servicio> <ciudad>") cae aquí, no
en la home. Una home no rankea para todos los servicios a la vez; una página por servicio, sí.

## 2. Wireframe (top → bottom)

```
COMP-HEADER [fijo]
COMP-BREADCRUMB (Inicio · Servicios · <este>) [fijo]
Encabezado de servicio (H1 = el servicio + claim + CTA) [fijo · ADN]
Qué resolvemos (los problemas concretos que atiende) [fijo · ADN]
COMP-FEATURES (alcance: qué incluye) [fijo]
COMP-PROCESS (cómo se lleva ESTE servicio) [toggle]
COMP-CASES (asuntos de este tipo) [toggle]
COMP-FAQ (dudas de ESTE servicio) [toggle · default ON]
COMP-TESTIMONIAL [toggle]
COMP-PRICING (planes, si el servicio los tiene) [toggle · default OFF]
Otros servicios (cross-link a las hermanas) [fijo · ADN]
COMP-CTA + COMP-LEAD-FORM [fijo · ADN]
COMP-FOOTER [fijo]
```

Todo empuja a la consulta de ESTE servicio. Ausencia intencional: catálogo completo,
portfolio visual grande, storytelling de marca (eso vive en la home y en About).

## 3. Secciones

### COMP-BREADCRUMB `[fijo]`
Objetivo: situar y enlazar hacia arriba (navegación + SEO). `Inicio · Servicios · <este>`.
Reutilizable: GLOBAL. Schema `BreadcrumbList`.

### Encabezado de servicio `[fijo · ADN]`
Objetivo: confirmar en un segundo que llegó al sitio correcto (conversión). **El `H1` es el nombre
del servicio tal como lo busca el cliente**, no "Servicios" ni el nombre comercial interno. Claim de
una línea + CTA. Mobile: 1 columna, CTA full-width. Desktop: texto a 60ch + CTA. Reutilizable: SECCIÓN.

### Qué resolvemos `[fijo · ADN]`
Objetivo: que el visitante se reconozca en un problema (descubrimiento + conversión). 4–6 situaciones
concretas, cada una título + una línea. Escrito desde el síntoma del cliente, no desde la
nomenclatura técnica del gremio. Mobile: 1 columna. Desktop: 2. Reutilizable: SECCIÓN.

### COMP-FEATURES — alcance `[fijo]`
Objetivo: definir qué incluye y qué no (confianza, evita malentendidos). 3–5 bloques.
Mobile: 1 columna. Desktop: fila. Reutilizable: GLOBAL.

### COMP-PROCESS `[toggle TGL-PROCESS]`
Objetivo: bajar fricción mostrando el método de ESTE servicio (confianza). 3–5 pasos numerados
— la numeración se justifica porque es una secuencia real. Reutilizable: GLOBAL.

### COMP-CASES `[toggle TGL-CASES]`
Objetivo: prueba de que se hace (confianza). Asuntos de este tipo. En sectores con deber de
confidencialidad (legal, salud, financiero), sin cliente identificable. Reutilizable: GLOBAL.

### COMP-FAQ `[toggle TGL-FAQ · default ON]`
Objetivo: resolver la duda previa a la consulta (info + SEO). Acordeón `<details>`. Las preguntas
son consultas de búsqueda literales de este servicio, no dudas genéricas del negocio. Es la sección
que más tráfico de cola larga aporta a esta página. Reutilizable: GLOBAL.

### COMP-PRICING `[toggle TGL-PRICING · default OFF]`
Objetivo: filtrar por presupuesto cuando el servicio es paquetizable. Apagado por default: la
mayoría de los servicios profesionales se presupuestan a medida. Reutilizable: GLOBAL.

### Otros servicios — cross-link `[fijo · ADN]`
Objetivo: recuperar al visitante que cayó en el servicio equivocado (navegación) **y construir el
cluster de enlazado interno (SEO)**. Cards compactas a las áreas hermanas + "ver todos".
Es FIJO, no toggle: sin enlazado entre hermanas cada servicio queda huérfano colgando sólo de la
home, y el cluster no se forma. Mobile: 1 columna. Desktop: 3. Reutilizable: GLOBAL.

### COMP-CTA + COMP-LEAD-FORM `[fijo · ADN]`
Objetivo: cerrar la consulta (conversión). Mismo componente que la home, con el claim referido a
ESTE servicio. **Nunca en el hero** (regla de casa del orquestador). Reutilizable: GLOBAL.

## 4. Toggles admitidos

| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-PROCESS` | on | |
| `TGL-CASES` | on | off si hay deber de confidencialidad y no hay material publicable |
| `TGL-FAQ` | **on** | al revés que en `TPL-CONTACT-01`; aquí es ADN de SEO |
| `TGL-TESTIMONIALS` | off | |
| `TGL-PRICING` | off | on sólo si el servicio es paquetizable |
| `TGL-CTA-STRENGTH` | hereda de la home | |

**Fijos:** COMP-HEADER, COMP-BREADCRUMB, encabezado, Qué resolvemos, COMP-FEATURES,
Otros servicios, COMP-CTA+COMP-LEAD-FORM, COMP-FOOTER.
**Ausencias de ADN:** catálogo completo, portfolio visual grande, storytelling de marca
→ eso vive en la home (`TPL-C-01`/`C-02`/`C-03`) o en `TPL-ABOUT-01`.

## 5. Multiplicación y contenido único

Hay **una página por servicio**, todas desde este arquetipo. El arquetipo se replica; **el contenido
no**. Clonar la página cambiando sólo el nombre del servicio produce duplicados que no rankean
ninguno. Cada instancia necesita su propio `H1`, sus propios "qué resolvemos" y su propio FAQ.

Si el sitio tiene más de ~8 servicios, resolver primero si todos merecen página propia: es preferible
cinco páginas con contenido real que quince vacías.

## 6. SEO / semántica
1 `H1` = el nombre del servicio como se busca. `H2` en Qué resolvemos, Alcance, FAQ.
Schema `Service` (+`Organization`) y `FAQPage` si el FAQ está on; `BreadcrumbList` en el breadcrumb.
URL propia y descriptiva por servicio. `header` > `main` > `footer`.
