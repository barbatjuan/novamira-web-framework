# CAPA 2 — Recomendador

Convierte "no sé qué plantilla" en "el agente me guía". Nunca construye desde cero: bifurca por
tipo de sitio, analiza la marca, pide referencias, recomienda un `TPL-*`, confirma.

## Flujo

```
0. TIPO DE SITIO   → ecommerce | corporate
1. ANÁLISIS de marca/web
2. INTAKE de referencias
3. RECOMENDACIÓN (TPL-* + por qué)
4. CONFIRMACIÓN del usuario
5. → pasa a CAPA 3 (toggles de esa plantilla)
```

## 0. Tipo de sitio (bifurcación)

Antes de nada, resolver: ¿ecommerce o corporativa? Si `project-context` no lo deja claro,
preguntarlo (AskUserQuestion). Determina qué set de arquetipos entra:

- `ecommerce` → `templates/ecommerce/TPL-E-01..05`
- `corporate` → `templates/corporate/TPL-C-*`

El design-system y los toggles son **compartidos** entre ambos tipos.

## 1. Análisis

Con web existente o brief, extraer señales:

| Señal | Preguntas guía |
|-------|----------------|
| **Qué publica** | ¿Una carta, un inventario que rota, un solo objeto, una tarifa con importes, tratamientos, un plan de meses, disponibilidad inmediata? Es la señal que enruta la familia B de corporate (§3b), y **se pregunta antes que el objetivo**. |
| Objetivo primario | Descubrimiento, venta directa, confianza, navegación, urgencia, generación de leads. |
| Peso de la marca | ¿La marca vende sola (branding) o el producto/precio/servicio manda? |
| Catálogo (ecommerce) | ¿Pocos SKUs curados o catálogo grande? ¿Una categoría o muchas? |
| Rubro | Moda/deco, electrónica/repuestos, autor/premium, multi-categoría, outlet (ecommerce); servicios, salud, inmobiliaria, agencia (corporate). |
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

### Familia A — por PERFIL (`TPL-E-01..05`)

Para tiendas donde existe una referencia con precio cerrado que entra en un carrito.

| Perfil dominante | Recomienda | Cierra con |
|------------------|-----------|------------|
| Marca visual, pocos SKUs, foto protagonista, descubrimiento | **TPL-E-01 Visual Brand** | cita de estilismo / medición (`COMP-BOOKING`) |
| Catálogo grande, precio/producto manda, venta directa, search | **TPL-E-02 Catalog / Product-First** | dudas operativas + "¿no lo encontraste?" (`COMP-FAQ` + `COMP-CONTACT-DIRECT`) |
| Marca de autor/premium, storytelling, confianza, poco catálogo | **TPL-E-03 Brand Story** | la palabra del taller: garantía y reposición (`COMP-CTA`) |
| Muchas familias de producto, navegación por categoría | **TPL-E-04 Categories-First** | el índice completo del catálogo (`COMP-CTA` + `COMP-CATEGORY-CARD`) |
| Outlet/campaña/estacional, urgencia, descuentos protagonistas | **TPL-E-05 Promo / Campaign** | última llamada + bases y condiciones (`COMP-CTA`) |

### Familia B — por UNIDAD DE CONTENIDO (`TPL-E-06..09`)

Los cinco de arriba comparten un supuesto: existe una referencia con un precio que entra en un
carrito. Estas cuatro existen porque ese supuesto se rompe, y **cada una cambia además el mecanismo
de conversión**, que es lo que de verdad las separa. Como en corporate, se pregunta primero por
esta familia.

| Lo que la tienda publica | Señales | Convierte con | Recomienda |
|--------------------------|---------|---------------|-----------|
| Una **talla** y si te va a quedar | Moda con ajuste, calzado, lencería, ropa técnica; la devolución por talla es el coste principal | carrito, tras resolver el ajuste | **TPL-E-06 Talla / Prueba** |
| Un **lote**: origen, fecha y precio por kilo | Fresco — carnicería, pescadería, quesos, café, huerta; el producto cambia cada semana y el peso no es exacto | carrito por peso, con ventana de entrega | **TPL-E-07 Lote / Peso** |
| Un **plan** con cuota y cadencia | Café, pienso, cosmética de reposición, consumibles; se vende repetición, no una unidad | suscripción | **TPL-E-08 Suscripción** |
| Un **presupuesto** de algo que aún no existe | Cortinas, mobiliario a medida, encimeras, rotulación; el precio no se sabe hasta configurar | **sin carrito** — formulario de presupuesto | **TPL-E-09 A medida** |

### Orden de decisión (ecommerce)

1. **Preguntar primero por la familia B.** Si la tienda vende ajuste, fresco por lote, suscripción
   o fabricado a medida, gana la familia B aunque el catálogo parezca encajar en un `TPL-E-01..05`.
2. Solo si ninguna de las cuatro aplica, enrutar por perfil con la familia A.

El corte es más limpio que en corporate porque aquí es **mecánico**: si el visitante no puede
añadir al carrito una referencia con precio cerrado, ninguna plantilla de la familia A funciona.
Meter fabricado a medida en `TPL-E-02` produce el error clásico del sector — precios "desde" que no
se parecen al final, un carrito que acepta la compra y un correo posterior pidiendo medidas, que es
donde se cae la venta con el cobro ya hecho.

Vecindades a leer antes de decidir: `E-06`↔`E-01` · `E-07`↔`E-02`/`E-03` · `E-08`↔`E-02` y
↔`TPL-C-11` (aquel plan TERMINA, éste no) · `E-09`↔`E-02` y ↔`TPL-C-01` (si es un servicio y no un
objeto fabricado).

La columna de cierre no es decorativa: **las cinco cierran, y cada una con una banda distinta**.
Si el cliente pide un cierre que no es el de la plantilla candidata — un formulario de contacto en
una tienda de catálogo, un cupón en una marca de autor — eso es señal de que el arquetipo elegido
es otro, no de que haya que añadirle una banda. Ninguna de las cinco cierra con `COMP-LEAD-FORM`:
ese es el ADN de TPL-C-01 y en una tienda pide un dato sin devolver nada.

## 3b. Mapa señal → plantilla (corporate)

Los doce arquetipos corporate se reparten en dos familias que se preguntan distinto, y **se
pregunta primero por la segunda**. Ver "Orden de decisión" al final de esta sección.

### Familia A — por OBJETIVO (`TPL-C-01..05`)

Sirven cuando el negocio vende algo que el dueño describe una vez y no cambia: un servicio, una
reputación, una obra, una oferta, una puerta.

| Perfil dominante | Recomienda |
|------------------|-----------|
| Servicios B2B/profesionales, objetivo = leads, CTA/formulario protagonista | **TPL-C-01 Services / Lead-Gen** |
| Empresa establecida, salud/legal/financiero, autoridad + credenciales + cifras | **TPL-C-02 Institutional / Trust** |
| Estudio creativo/arquitectura/foto, el trabajo manda, mucho visual | **TPL-C-03 Portfolio / Showcase** |
| Una sola oferta/servicio/campaña, secuencia persuasiva a un CTA | **TPL-C-04 Landing / Single-Offer** |
| Local con reserva/turno, ubicación y horarios protagonistas | **TPL-C-05 Local / Booking** |

### Familia B — por UNIDAD DE CONTENIDO (`TPL-C-06..12`)

No son variantes de las anteriores: existen porque el negocio **publica una cosa concreta** que no
entra en una tarjeta de servicio. La pregunta que los enruta no es "¿qué objetivo tenés?" sino
**"¿qué publica este negocio, y cada cuánto cambia?"**.

| Lo que el negocio publica | Señales | Recomienda |
|---------------------------|---------|-----------|
| Una **carta** con precios, y una cocina que la firma | Restauración; la foto de la comida y el precio deciden antes que la reserva | **TPL-C-06 Mesa / Carta** |
| Un **inventario volátil** de unidades, cada una con sus datos | Concesionario, ocasión, maquinaria, náutica; el stock rota cada semana; filtrar es la primera intención | **TPL-C-07 Stock / Ocasión** |
| **Un solo objeto** caro, contado entero | Concesión oficial, lanzamiento, piso piloto; una unidad que se mira despacio, no cuarenta que se descartan | **TPL-C-08 Modelo / Lanzamiento** |
| Una **tarifa** con importes por trabajo | Taller, chapa, neumáticos, reparación técnica; el miedo del cliente es el precio desconocido | **TPL-C-09 Taller / Tarifa** |
| Una **cartera de inmuebles**, buscable por ZONA | Inmobiliaria, administrador de fincas con cartera, promotora comercializando; la primera intención es buscar, no leer | **TPL-C-13 Cartera / Búsqueda** |

**`TPL-C-13` frente a `TPL-C-07` y `TPL-C-08`.** Los tres publican unidades, y la tabla sola no
basta para separarlos. `TPL-C-07` filtra por ATRIBUTOS discretos —marca, modelo, año, kilómetros—
y una unidad es intercambiable con otra igual: dos coches del mismo modelo y año son el mismo
coche. `TPL-C-13` filtra por **ZONA**, que es geografía, y ninguna unidad es intercambiable con
otra — de ahí que lleve el plano como MODO DE BÚSQUEDA, que `TPL-C-07` no tiene. `TPL-C-08` no es
un listado en absoluto: es UNA unidad contada entera, y su mención de «piso piloto» va por ahí —
la promotora que enseña un solo piso, no la que comercializa cuarenta.
Regla corta: **¿cuántas, y se eligen por dónde están?** Cuarenta y por zona → C-13. Cuarenta y por
ficha técnica → C-07. Una → C-08.

Y una frontera que NO es de sector: una inmobiliaria publica SU cartera y el visitante mira y
contacta. No hay perfil de vendedor, ranking ni «sube tu anuncio» — eso es un portal, que es otro
negocio y no está en este catálogo. Lo que sí lleva `TPL-C-13` es captación de cartera vía
tasación, que es la agencia decidiendo qué coge, no el usuario publicando.
| **Tratamientos** con datos duros y quien los hace | Clínica dental, dermatología, fisio, podología, veterinaria; el miedo es el procedimiento, no el precio | **TPL-C-10 Clínica / Tratamientos** |
| **Un plan largo** medido en meses y cuotas | Ortodoncia, implantología, nutrición, entrenamiento, psicoterapia; un solo tratamiento de 12–18 meses | **TPL-C-11 Plan por fases** |
| **Disponibilidad AHORA** | Guardia dental, cerrajería, fontanería, grúa, veterinaria 24 h; quien entra tiene dolor o una puerta cerrada y no va a leer | **TPL-C-12 Urgencias / Hoy** |

### Orden de decisión

1. **Preguntar primero por la familia B.** Si el negocio publica una carta, un inventario, un
   objeto único, una tarifa, tratamientos, un plan por fases o disponibilidad inmediata, **gana la
   familia B**, aunque el objetivo declarado encaje con un `TPL-C-01..05`.
2. Solo si ninguna de las siete aplica, enrutar por objetivo con la familia A.

Este orden no es una preferencia: es lo que impide que arquetipos distintos colapsen en uno. Un
taller y una clínica son los dos "local con cita" y caerían los dos en `TPL-C-05`, que publica el
local y esconde justo lo que el cliente vino a saber — el precio en uno, el procedimiento en el
otro. Cada documento `TPL-C-06..12` abre con un apartado **"Por qué existe habiendo TPL-C-0X"**:
cuando dudes entre una de la familia B y su vecina de la familia A, la respuesta está ahí, en el
arquetipo, y no aquí.

Vecindades que conviene leer antes de decidir:
`C-06`↔`C-05` · `C-07`↔`C-08` · `C-08`↔`C-04` · `C-09`↔`C-05` · `C-10`↔`C-05`/`C-09` ·
`C-11`↔`C-10` · `C-12`↔`C-05`.

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
| Servicio / área | `TPL-SERVICE-01` | Una **por servicio** en cuanto la home lleve `COMP-SERVICES`. El grid de la home enlaza a algún sitio, y sin ellas se pierde toda la búsqueda comercial "`<servicio> <ciudad>`", que nunca cae en la home |
| Proyecto / caso | `TPL-PROJECT-01` | **Obligatoria** si la home es `TPL-C-03` Portfolio. Sin ella el portfolio es una galería: se ve bien y no demuestra nada |
| Blog + Entrada | `TPL-BLOG-01` + `TPL-POST-01` | Solo si hay alguien que publique, **y se pregunta antes**: tres entradas de hace dos años restan confianza en vez de sumarla |

### 6.3 Set sugerido por tipo de sitio

| Tipo de sitio | Set de páginas sugerido |
|---------------|-------------------------|
| Ecommerce | Home + **Shop/Catálogo** (`TPL-SHOP-01`) + **Ficha de producto** (`TPL-PDP-01..05`, ver 6.4) + **Nosotros** (`TPL-ABOUT-01..03`) + **Contacto** (`TPL-CONTACT-01..02`) — más el bloque 6.1. Carrito (`TPL-CART-01`) y Checkout (`TPL-CHECKOUT-01`) existen como arquetipo de LAYOUT, pero quien los monta es `woocommerce`: no entran en este set, se citan al pasar el testigo |
| Corporate | Home + **una `TPL-SERVICE-01` por servicio/área** + **Nosotros** (`TPL-ABOUT-01..03`) + **Contacto** (`TPL-CONTACT-01..02`) — más el bloque 6.1 |

Los `TPL-C-06..12` publican su unidad de contenido **en la propia home** (la carta, el stock, la
tarifa, los tratamientos, el plan, el triaje). No arrastran páginas de servicio por defecto: si la
home no lleva `COMP-SERVICES`, no hay grid que enlace a ninguna parte. Dos excepciones que sí las
piden cuando el negocio las tiene: `TPL-C-09` puede querer una `TPL-SERVICE-01` por línea de
trabajo, y `TPL-C-10` una por tratamiento con búsqueda propia. Se pregunta; no se asume.

### 6.4 Qué arquetipo le toca a cada página interna

Aquí había una línea que decía que Nosotros y Contacto **«no tienen variantes»**, y con un solo
arquetipo cada una todos los sitios del catálogo se entregaban con la misma página de Nosotros y la
misma de Contacto. Las páginas internas se eligen igual que la home: por lo que el visitante
necesita decidir, no por el sector ni por el gusto.

**Ficha de producto — se elige por la DUDA que bloquea la compra.**

| La duda que queda | Arquetipo | Home que suele acompañarla |
|-------------------|-----------|----------------------------|
| Cuál y cuántos: el producto está definido | `TPL-PDP-01` Estándar | `TPL-E-01`…`TPL-E-05` |
| Si le va a caber | `TPL-PDP-02` Talla y ajuste | `TPL-E-06` |
| Cuánto pesa, de dónde viene y qué día llega | `TPL-PDP-03` Lote y peso | `TPL-E-07` |
| A qué se compromete y cómo se sale | `TPL-PDP-04` Suscripción | `TPL-E-08` |
| Si se puede fabricar lo suyo y qué cuesta | `TPL-PDP-05` A medida | `TPL-E-09` |

La frontera se comprueba con una pregunta: **¿el precio se sabe antes de que el cliente hable?**
Sí y la pieza es siempre igual → `TPL-PDP-01`. Sí pero por kilo → `TPL-PDP-03`. Sí pero es una
cuota → `TPL-PDP-04`. No → `TPL-PDP-05`. Y si el precio se sabe pero la talla no, → `TPL-PDP-02`.

`TPL-E-01` Visual Brand y `TPL-E-03` Brand Story **no arrastran un arquetipo propio**: usan
`TPL-PDP-01` con `TGL-PDP-LAYOUT: editorial`. Hubo un `TPL-PDP-02 Editorial` y medido compartía
siete de sus ocho secciones con la estándar — el registro visual lo mueven las anclas y ese toggle,
no un esqueleto duplicado. Por lo mismo se retiró `TPL-SHOP-02 Full-width`: era
`TGL-SHOP-FILTERS: topbar` con nombre de arquetipo.

**Nosotros — se elige por lo que da confianza en ese negocio.**

| Lo que convence | Arquetipo | Ejemplos |
|-----------------|-----------|----------|
| La trayectoria: años, clientes, equipo | `TPL-ABOUT-01` La empresa | Asesoría, distribuidora, agencia |
| El oficio: cómo se hace y qué ha salido | `TPL-ABOUT-02` El oficio | Taller, cantería, imprenta, laboratorio |
| El sitio: la sala, el horario, quién atiende | `TPL-ABOUT-03` La casa | Restaurante, clínica, hotel, tienda |

**Contacto — se elige por lo que el visitante va a HACER.**

| Lo que quiere hacer | Arquetipo |
|---------------------|-----------|
| Escribir una consulta que hay que estudiar antes de contestar | `TPL-CONTACT-01` Consulta |
| Llamar, ir o reservar | `TPL-CONTACT-02` Puerta abierta |

La señal: **si la respuesta útil se da en el mismo minuto, no es una consulta, es una llamada** — y
entonces el formulario compite con el teléfono y pierde.

**Defaults coherentes con la home.** Se proponen; el usuario puede cambiar cualquiera.

- `TPL-C-05`, `TPL-C-06`, `TPL-C-10`, `TPL-C-12` (negocio con puerta) → `TPL-ABOUT-03` + `TPL-CONTACT-02`.
- `TPL-C-09`, `TPL-E-02` con taller propio → `TPL-ABOUT-02` + `TPL-CONTACT-01`.
- El resto → `TPL-ABOUT-01` + `TPL-CONTACT-01`.
- Home TPL-C-01 / TPL-C-02 (llevan `COMP-SERVICES`) → **una `TPL-SERVICE-01` por servicio o área**.
  TPL-C-04 es una oferta única y no las necesita; TPL-C-03 enlaza a páginas de proyecto, no de servicio.
- Todas heredan tokens y tono de la home. Heredar el ASPECTO no es heredar la ESTRUCTURA.

El usuario puede overridear cualquier arquetipo por página. Cada página pasa luego por sus toggles
y llega a `html-mockup`, que renderiza **el set entero en UN solo Artifact** con navegación
interna — nunca una maqueta por página ni un Artifact por página. La regla vive en
`html-mockup/SKILL.md` §Hard Rules, que es su autoridad; aquí se cita para que el recomendador no
prometa otra cosa al proponer el set.
