# CAPA 2 — Recomendador

Convierte "no sé qué plantilla" en "el agente me guía". Bifurca por tipo de sitio, analiza la
marca, pide referencias, recomienda un `TPL-*` — o, si ninguno aplica, resuelve en lane bespoke
(`lanes.md`). Nunca construye desde cero sin agotar antes el catálogo.

**Catálogo de ocho, no de veintitrés.** `catalog-envato-grade` retiró dieciséis arquetipos que no
respaldaban ninguna demo D1 (`TPL-C-01..06`, `C-08..10`, `C-12`, `C-13`, `TPL-E-01..05`) y sumó uno
nuevo con reemplazo nombrado (`TPL-C-15`, sucesor de `TPL-C-13`); el catálogo activo hoy son los
ocho que sí: `TPL-C-07` (Motor Aranda), `TPL-C-11` (Alinea), `TPL-C-14` (Lumière), `TPL-C-15`
(cartera curada, demo de marca pendiente de `PR3b`), `TPL-E-06` (Corte Nueve), `TPL-E-07` (Bajura),
`TPL-E-08` (Tueste Norte), `TPL-E-09` (Medida Justa). Un brief que hoy habría encajado en uno de los
dieciséis retirados **no encaja en ninguno de los ocho** — eso no es un fallo de este documento, es
agotar el catálogo honestamente y pasar a la lane bespoke (`lanes.md` § Carril bespoke), con el
razonamiento negativo registrado. Dos arquetipos nuevos más (por confirmar para abogados y
gimnasios) se suman en fases posteriores del mismo cambio; hasta entonces, un brief legal o de
fitness también resuelve en bespoke.

## Flujo

```
0. TIPO DE SITIO   → ecommerce | corporate
1. ANÁLISIS de marca/web
2. INTAKE de referencias
3. RECOMENDACIÓN (TPL-* + por qué)
3b. SIN COINCIDENCIA → lane bespoke (razonamiento negativo registrado; ver `lanes.md`)
4. CONFIRMACIÓN del usuario
5. → pasa a CAPA 3 (toggles de esa plantilla), salvo bespoke: sus secciones se declaran inline
```

Un resultado **bespoke** solo se promueve a `TPL-*` cuando pasa la misma auditoría que un
arquetipo nativo: `RT_TPL_NO_WIREFRAME`, `RT_TPL_UNROUTABLE`, `RT_TPL_TOO_SIMILAR` y, según
`catalog-wrapper-integrity`, `RT_TPL_NO_ENVOLTORIO` / `RT_TPL_WRAPPER_DUPLICATE`. Criterio de
promoción: estricto, sin excepciones — detalle completo en `lanes.md`.

## 0. Tipo de sitio (bifurcación)

Antes de nada, resolver: ¿ecommerce o corporativa? Si `project-context` no lo deja claro,
preguntarlo (AskUserQuestion). Determina qué set de arquetipos entra:

- `ecommerce` → `templates/ecommerce/TPL-E-06..09` (activos)
- `corporate` → `templates/corporate/TPL-C-07`, `TPL-C-11`, `TPL-C-14`, `TPL-C-15` (activos)

El design-system y los toggles son **compartidos** entre ambos tipos.

## 1. Análisis

Con web existente o brief, extraer señales:

| Señal | Preguntas guía |
|-------|----------------|
| **Qué publica** | ¿Un inventario que rota, una tarifa con importes, un plan de meses, un lote con origen y fecha, un plan con cuota, un presupuesto de algo a medida? Es la señal que enruta el catálogo activo, y **se pregunta antes que el objetivo**. |
| Objetivo primario | Descubrimiento, venta directa, confianza, navegación, urgencia, generación de leads. |
| Peso de la marca | ¿La marca vende sola (branding) o el producto/precio/servicio manda? |
| Catálogo (ecommerce) | ¿Pocos SKUs curados o catálogo grande? ¿Una categoría o muchas? |
| Rubro | Automoción de ocasión, salud/ortodoncia, estética (corporate); moda con ajuste, fresco por lote, suscripción, fabricado a medida (ecommerce). |
| Rol de la foto | ¿Fotografía protagonista o secundaria? |
| Estacionalidad | ¿Campañas/promos permanentes o venta/actividad estable? |

## 2. Intake de referencias (obligatorio antes de recomendar)

> "Para afinar la recomendación, pasame **2–4 referencias**: sitios que te gusten, competidores,
> o imágenes/capturas del estilo que buscás. Decime qué te gusta de cada una (el hero, las cards,
> los colores, la sensación)."

De cada referencia, anotar: tipo de hero (slider / imagen fija / banner promo / sin hero),
estilo (minimalista / elegante-editorial / comercial-agresivo), cards (imagen grande / compactas),
densidad (aire / info), foco (venta / CTA / branding), paleta y tipografía percibidas.

Estas señales alimentan la recomendación **y** precargan los toggles de CAPA 3 (llegan con
defaults sugeridos, no en blanco).

## 3. Mapa señal → plantilla (ecommerce)

Los cuatro activos comparten un supuesto: **no** existe una referencia con precio cerrado que entra
sin más en un carrito — cada uno cambia el mecanismo de conversión, que es lo que de verdad los
separa.

| Lo que la tienda publica | Señales | Convierte con | Recomienda |
|--------------------------|---------|---------------|-----------|
| Una **talla** y si te va a quedar | Moda con ajuste, calzado, lencería, ropa técnica; la devolución por talla es el coste principal | carrito, tras resolver el ajuste | **TPL-E-06 Talla / Prueba** |
| Un **lote**: origen, fecha y precio por kilo | Fresco — carnicería, pescadería, quesos, café, huerta; el producto cambia cada semana y el peso no es exacto | carrito por peso, con ventana de entrega | **TPL-E-07 Lote / Peso** |
| Un **plan** con cuota y cadencia | Café, pienso, cosmética de reposición, consumibles; se vende repetición, no una unidad | suscripción | **TPL-E-08 Suscripción** |
| Un **presupuesto** de algo que aún no existe | Cortinas, mobiliario a medida, encimeras, rotulación; el precio no se sabe hasta configurar | **sin carrito** — formulario de presupuesto | **TPL-E-09 A medida** |

**Si la tienda vende por PERFIL** — marca visual de pocos SKUs, catálogo grande por precio, marca de
autor/storytelling, navegación por categoría, o outlet/campaña estacional — ninguno de los cuatro
activos encaja: son exactamente los cinco perfiles que cubrían `TPL-E-01..05`, retirados en
`catalog-envato-grade`. Agotar primero los cuatro de arriba (ninguna referencia con precio cerrado
que entra directo en el carrito, mecanismo de conversión distinto) y, si de verdad ninguno aplica,
pasar a bespoke con el razonamiento negativo registrado — no forzar el brief dentro de `TPL-E-06..09`
solo porque son los que hay.

Vecindades a leer antes de decidir dentro del set activo: `E-08`↔`TPL-C-11` (aquel plan TERMINA,
éste no).

La columna de cierre no es decorativa: **cada uno cierra con una banda distinta**. Si el cliente
pide un cierre que no es el de la plantilla candidata, eso es señal de que el arquetipo elegido es
otro (o que el brief cae fuera del catálogo activo), no de que haya que añadirle una banda.

## 3b. Mapa señal → plantilla (corporate)

Los cuatro activos existen porque el negocio **publica una cosa concreta** que no entra en una
tarjeta de servicio genérica. La pregunta que los enruta no es "¿qué objetivo tenés?" sino **"¿qué
publica este negocio, y cada cuánto cambia?"**.

| Lo que el negocio publica | Señales | Recomienda |
|---------------------------|---------|-----------|
| Un **inventario volátil** de unidades, cada una con sus datos | Concesionario, ocasión, maquinaria, náutica; el stock rota cada semana; filtrar es la primera intención | **TPL-C-07 Stock / Ocasión** |
| **Un plan largo** medido en meses y cuotas | Ortodoncia, implantología, nutrición, entrenamiento, psicoterapia; un solo tratamiento de 12–18 meses | **TPL-C-11 Plan por fases** |
| Una **carta de rituales** que se compra en bono | Centro de estética, cabina de belleza, spa urbano, depilación, uñas; se elige por zona del cuerpo y se vuelve cinco veces | **TPL-C-14 Ritual / Bono** |
| Una **cartera curada** de inmuebles únicos, cada uno tratado como pieza | Inmobiliaria de lujo residencial, promotora boutique; la agencia CURA la cartera, no la deja rotar como un inventario | **TPL-C-15 Cartera curada** |

**Si el negocio vende por OBJETIVO** — leads B2B, autoridad institucional, portfolio creativo, una
oferta única, o un local con reserva/turno — ninguno de los cuatro activos encaja: son exactamente
los cinco objetivos que cubría `TPL-C-01..05`, retirados en `catalog-envato-grade`. Lo mismo si
publica una carta de mesa, un inventario por unidad única, una tarifa de taller, tratamientos
clínicos o disponibilidad de urgencia — `TPL-C-06`, `C-08`, `C-09`, `C-10` y `C-12` cubrían esas
cinco y también se retiraron, sin reemplazo. Una cartera de inmuebles buscable por zona ya NO cae en
bespoke: `TPL-C-13` cubría ese caso, se retiró en esta misma iniciativa, y `TPL-C-15 · Cartera
curada` es su reemplazo nombrado — activo desde ya, aunque su demo de marca (`delao`) llega en
`PR3b`. Un negocio de abogados o de gimnasios tampoco tiene hueco todavía: sus arquetipos llegan en
fases posteriores de `catalog-envato-grade`. En cualquiera de estos casos, agotar los cuatro activos
primero, registrar por qué ninguno sirve, y pasar a bespoke.

**`TPL-C-11` frente a `TPL-C-14`.** Los dos son un servicio con cita, y conviene separarlos aquí.
`TPL-C-11` vende un **plan que termina**: doce a dieciocho meses medidos en fases, con un final
declarado (ortodoncia, implantología, un programa de entrenamiento). `TPL-C-14` vende una **carta
que se repite**: la clienta vuelve, elige por zona del cuerpo cada vez, y la página cierra en un
bono de sesiones sin fecha de término. Regla corta: **¿el tratamiento tiene un final declarado?**
Sí, con fases → C-11. No, se repite indefinidamente → C-14.

**`TPL-C-15` frente a los otros tres.** Los tres publican un contenido que avanza o se repite solo
—un plan que progresa, una carta que se repite, un stock que rota cada semana—. `TPL-C-15` publica
una CARTERA casi estática (una veintena de inmuebles, no cientos) en la que lo que convence es la
fotografía antes que el filtro: no compite con los otros tres, cubre el caso en el que el negocio
vende UNIDADES únicas de alto valor que se curan, no que rotan.

### Orden de decisión

1. **Preguntar primero qué publica el negocio.** Si es un inventario que rota, un plan largo por
   fases, una carta de rituales en bono, o una cartera curada de inmuebles, el catálogo activo tiene
   una respuesta. Para cualquier otra cosa que un `TPL-C-*` cubría antes de esta fase, no hay
   arquetipo activo todavía.
2. Antes de resolver en bespoke, confirmar que de verdad ninguno de los cuatro encaja — no
   descartar por preferencia de estilo, solo por lo que el negocio publica.

Empates: presentar las 2 candidatas con el trade-off y dejar elegir. Nunca decidir solo un empate.

## 4. Recomendación (formato de salida)

```
Tipo de sitio: [ecommerce | corporate].
Analicé la marca: [resumen 1–2 líneas].
Referencias: [qué se extrajo].
Recomiendo: TPL-X-0N — [nombre].
Por qué: [2–3 razones ligadas a las señales].
Alternativa: TPL-X-0M si preferís [trade-off].
¿Confirmás o revisamos la alternativa?
```

## 5. Confirmación

- El usuario confirma o pide la alternativa.
- Recién con plantilla confirmada, correr CAPA 3 (`toggles.md`) filtrando solo los toggles que
  esa plantilla admite, precargados con los defaults del intake.
- Ninguna decisión estructural importante se toma sin confirmación.

## 6. Resolución del set de páginas (más allá de la home)

Tras confirmar la home, resolver qué **páginas internas** necesita el sitio y asignar un arquetipo a
cada una (ver `templates/pages/`). Proponer el set, confirmar, y por cada página elegir arquetipo o
heredar el default coherente con la home.

### 6.1 Lo que NO se pregunta

Tres arquetipos entran siempre, en los dos tipos de sitio, y **no se ofrecen como opción**:

| Página | Arquetipo | Por qué no se pregunta |
|--------|-----------|------------------------|
| Legales ×4 (aviso, privacidad, cookies, términos) | `TPL-LEGAL-01` | Un sitio sin legales no se entrega. El contenido lo escribe `wordpress-legal`, no este flujo |
| 404 | `TPL-404-01` | Sin una propia, el sitio devuelve la página desnuda del tema |
| Gracias | `TPL-THANKS-01` | Solo si hay formulario — pero entonces es obligatoria: sin URL propia la conversión no se puede medir |

Preguntarlas es ofrecer al cliente que diga que no a algo que igualmente hay que construir.

### 6.2 Lo que sí depende de la home

| Página | Arquetipo | Cuándo |
|--------|-----------|--------|
| Unidad de inventario | `TPL-UNIT-01` | **Obligatoria** si la home es `TPL-C-07` Stock. La rejilla de esa home dice «ver ficha»: sin la ficha, el arquetipo promete una página que no existe |
| Servicios / ritual | `TPL-SERVICES-01` + `TPL-SERVICE-02` | **Obligatoria** si la home es `TPL-C-14` Ritual/Bono. La home enseña un resumen de la carta; el índice y la ficha de cada ritual viven aquí |
| Ficha de inmueble | `TPL-PROPERTY-01` | **Obligatoria** si la home es `TPL-C-15` Cartera curada. La rejilla de esa home dice «ver ficha»: sin la ficha, el arquetipo promete una página que no existe |
| Blog + Entrada | `TPL-BLOG-01` + `TPL-POST-01` | Solo si hay alguien que publique, **y se pregunta antes**: tres entradas de hace dos años restan confianza en vez de sumarla |

`TPL-PROJECT-01` (portfolio) y una `TPL-SERVICE-01` genérica quedan sin home activa que las requiera
obligatoriamente hasta que `TPL-C-03` o `TPL-C-01`/`C-02` vuelvan a estar en catálogo.
`TPL-PROPERTY-01` ya no está en ese grupo: acompañaba a `TPL-C-13`, retirado en esta misma
iniciativa, y ahora es la ficha obligatoria de su reemplazo nombrado, `TPL-C-15` (ver
`catalog-wrapper-integrity` Requisito 5).

### 6.3 Set sugerido por tipo de sitio

| Tipo de sitio | Set de páginas sugerido |
|---------------|-------------------------|
| Ecommerce | Home + **Shop/Catálogo** (`TPL-SHOP-01`) + **Ficha de producto** (`TPL-PDP-01..05`, ver 6.4) + **Nosotros** (`TPL-ABOUT-01..03`) + **Contacto** (`TPL-CONTACT-01..02`) — más el bloque 6.1. Carrito (`TPL-CART-01`) y Checkout (`TPL-CHECKOUT-01`) existen como arquetipo de LAYOUT, pero quien los monta es `woocommerce`: no entran en este set, se citan al pasar el testigo |
| Corporate | Home + **Nosotros** (`TPL-ABOUT-01..03`) + **Contacto** (`TPL-CONTACT-01..02`) — más el bloque 6.1 |
| Corporate con inventario | Home (`TPL-C-07`) + **una ficha por unidad** (`TPL-UNIT-01`) + **Nosotros** + **Contacto** — más el bloque 6.1. Aquí las fichas no son cinco: son las que haya en patio, y las escribe quien mantiene el inventario, no quien monta la web |
| Corporate con cartera inmobiliaria | Home (`TPL-C-15`) + **una ficha por inmueble** (`TPL-PROPERTY-01`) + **Nosotros** + **Contacto** — más el bloque 6.1 |

### 6.4 Qué arquetipo le toca a cada página interna

Las páginas internas se eligen igual que la home: por lo que el visitante necesita decidir, no por
el sector ni por el gusto.

**Ficha de producto — se elige por la DUDA que bloquea la compra.**

| La duda que queda | Arquetipo | Home activa que la usa |
|-------------------|-----------|----------------------------|
| Si le va a caber | `TPL-PDP-02` Talla y ajuste | `TPL-E-06` |
| Cuánto pesa, de dónde viene y qué día llega | `TPL-PDP-03` Lote y peso | `TPL-E-07` |
| A qué se compromete y cómo se sale | `TPL-PDP-04` Suscripción | `TPL-E-08` |
| Si se puede fabricar lo suyo y qué cuesta | `TPL-PDP-05` A medida | `TPL-E-09` |

`TPL-PDP-01` (Estándar) no tiene home activa que lo use hoy — cubría `TPL-E-01..05`, retirados. Sigue
existiendo como arquetipo de ficha y vuelve a engancharse en fases posteriores del mismo cambio
(`TPL-E-08` lo suma como su ficha "una bolsa" — decisión C1 — y `TPL-E-09` hereda su contenido
`mtm` re-skinado como `TPL-PDP-05`).

**Nosotros — se elige por lo que da confianza en ese negocio.**

| Lo que convence | Arquetipo | Ejemplos |
|-----------------|-----------|----------|
| La trayectoria: años, clientes, equipo | `TPL-ABOUT-01` La empresa | Asesoría, distribuidora, agencia |
| El oficio: cómo se hace y qué ha salido | `TPL-ABOUT-02` El oficio | Taller, cantería, imprenta, laboratorio |
| El sitio: la sala, el horario, quién atiende | `TPL-ABOUT-03` La casa | Restaurante, clínica, hotel, tienda |

**Servicio — se elige por lo que al visitante le falta saber.**

| Lo que falta saber | Arquetipo | Home activa que suele acompañarla |
|--------------------|-----------|----------------------------|
| Qué pasa dentro de la sesión, y cuándo NO puedo | `TPL-SERVICE-02` El tratamiento | `TPL-C-14` |

`TPL-SERVICE-01` (El servicio, abre por el problema) no tiene home activa hoy — cubría `TPL-C-01` y
`TPL-C-02`, retirados. Por encima de las dos, `TPL-SERVICES-01` es el índice al que apunta la miga de
ambas: entra en cuanto el negocio publica más entradas de las que la home enseña.

**Ficha de inventario — se elige por QUÉ SE COMPRA.**

| Lo que decide la compra | Arquetipo | Home activa que la acompaña |
|-------------------------|-----------|----------------------|
| De dónde viene esta unidad y cuánto ha andado | `TPL-UNIT-01` La unidad de ocasión | `TPL-C-07` |
| Si confías en una vivienda única, con precio, metros y zona | `TPL-PROPERTY-01` El inmueble | `TPL-C-15` |

`TPL-PROPERTY-01` acompañaba a `TPL-C-13`, retirado en esta misma iniciativa; sobrevive sin cambios
y es la ficha de su reemplazo nombrado, `TPL-C-15` (`catalog-wrapper-integrity` Requisito 5).

Y ninguna de las dos es `TPL-PDP-01`. Una ficha de producto vende una unidad **fungible** —hay cien
iguales en el almacén— y por eso gira alrededor de elegir variante y añadir al carrito. Aquí hay
UNA. No se elige talla: se decide si te fías, y la conversión es una cita para verla.

**Contacto — se elige por lo que el visitante va a HACER.**

| Lo que quiere hacer | Arquetipo |
|---------------------|-----------|
| Escribir una consulta que hay que estudiar antes de contestar | `TPL-CONTACT-01` Consulta |
| Llamar, ir o reservar | `TPL-CONTACT-02` Puerta abierta |

La señal: **si la respuesta útil se da en el mismo minuto, no es una consulta, es una llamada** — y
entonces el formulario compite con el teléfono y pierde.

**Defaults coherentes con la home.** Se proponen; el usuario puede cambiar cualquiera.

- Home `TPL-C-14` → `TPL-ABOUT-03` + `TPL-CONTACT-02`.
- Home `TPL-C-07` → **una `TPL-UNIT-01` por unidad en patio**. No se pregunta: la rejilla de la
  home ya enlaza a ellas, y un enlace que no llega a ninguna parte no es una decisión de alcance, es
  un defecto.
- Home `TPL-C-11` → `TPL-ABOUT-01` + `TPL-CONTACT-01`.
- Home `TPL-C-14` → `TPL-SERVICES-01` agrupado **por zona** + **una `TPL-SERVICE-02` por ritual**.
  La home enseña seis; la carta entera vive en el índice, y cada ritual necesita su minutaje y sus
  contraindicaciones, que no caben en una tarjeta.
- Todas heredan tokens y tono de la home. Heredar el ASPECTO no es heredar la ESTRUCTURA.

El usuario puede overridear cualquier arquetipo por página. Cada página pasa luego por sus toggles
y llega a `html-mockup`, que renderiza **el set entero en UN solo Artifact** con navegación
interna — nunca una maqueta por página ni un Artifact por página. La regla vive en
`html-mockup/SKILL.md` §Hard Rules, que es su autoridad; aquí se cita para que el recomendador no
prometa otra cosa al proponer el set.
