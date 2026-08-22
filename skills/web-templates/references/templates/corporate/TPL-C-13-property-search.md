# TPL-C-13 — Cartera / Búsqueda

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Cartera / Búsqueda |
| Objetivo | Que el usuario encuentre UN inmueble concreto de la cartera y pida verlo |
| Ideal para | Inmobiliarias, administradores de fincas con cartera propia, promotoras con obra en venta |
| Ejemplos | Agencia de barrio, inmobiliaria de costa, alquiler de larga temporada, obra nueva en comercialización |
| Nivel de contenido | Alto y VIVO — la cartera cambia sola; el resto de la web casi no cambia |
| Protagonismo | El BUSCADOR en la primera pantalla y la FICHA con precio, metros y zona. La agencia va al final o no va |
| ADN | Buscador en el héroe + cartera en rejilla + los mismos resultados sobre plano + solicitud de visita con la referencia dentro + captación de cartera. NO carrito, NO precio "desde", NO rejilla de servicios. |

**Nadie entra a una inmobiliaria a leer sobre la inmobiliaria.** Entra a buscar piso. Por eso el
buscador no es una sección: es la primera pantalla, y todo lo que se ponga por encima de él es
retraso. La sección "quiénes somos" existe, pero va después de que el usuario haya visto que la
cartera tiene lo que busca — antes no se la ha ganado.

**Una sola cara, y esto es una corrección deliberada.** Un portal tipo marketplace deja publicar a
cualquiera y rankea a los vendedores. Una inmobiliaria **no**: publica SU cartera, y el visitante
mira y contacta. No hay perfil de vendedor, no hay ranking, no hay "sube tu anuncio". Lo que sí hay
es `COMP-VALUATION-CTA`, que es otra cosa — el propietario pide una tasación y la agencia decide si
coge el inmueble. Captación, no publicación.

**Por qué existe habiendo TPL-C-07.** `TPL-C-07` también es un listado con filtros, y esa
coincidencia es real. Lo que las separa es el EJE por el que se busca. Un coche se filtra por
atributos discretos —marca, modelo, año, kilómetros— y un coche de un modelo es intercambiable con
otro del mismo modelo. Un inmueble es único y se filtra por **zona**, que es geografía: por eso aquí
el plano es un MODO DE BÚSQUEDA con los mismos resultados dentro, y en `TPL-C-07` no hay ninguno.
Las conversiones también divergen: allí la financiación y la tasación del usado, aquí la visita
presencial a una dirección concreta.

**Por qué no es `TPL-C-05`.** `TPL-C-05` reserva una cita en UN sitio: el negocio tiene una puerta y
la cita es en ella. Aquí la visita es a UNA propiedad de muchas, y el formulario no vale si no lleva
la referencia del inmueble dentro — una solicitud de visita sin referencia es una consulta genérica,
que es exactamente lo que la agencia no puede atender.

## 2. Wireframe (top → bottom)

```
COMP-HEADER (teléfono + "Vender tu casa") [fijo]
COMP-SEARCH-HERO (operación · zona · precio · habitaciones, sobre la primera pantalla) [fijo · ADN]
COMP-PROPERTY-GRID (la cartera, con los filtros aún a la vista) [fijo · ADN]
COMP-MAP-SEARCH (los MISMOS resultados sobre plano) [toggle · ADN]
COMP-VISIT-REQUEST (solicitud de visita con la referencia dentro) [fijo · ADN]
COMP-TEAM (quién te abre la puerta) [toggle]
COMP-VALUATION-CTA (¿vendes? tasación) [fijo · ADN]
COMP-FAQ (gastos, arras, nota simple) [toggle]
COMP-FOOTER (con NAP y número de registro) [fijo]
```

El orden ES el argumento: se busca, se ve la cartera, se comprueba dónde cae en el plano, y sólo
entonces se pide la visita. La captación va DESPUÉS de la visita a propósito — un propietario que
quiere vender llega igual, y ponerla antes le roba la primera pantalla a quien viene a comprar, que
es el 90% del tráfico.

Ausencia intencional: sin `COMP-SERVICES` (una rejilla de "qué hacemos" en una inmobiliaria es
relleno: lo que hace ya está en la cartera), sin `COMP-PRICING` (el precio es de cada inmueble, no
de la agencia) y sin carrito de ninguna clase.

**El envoltorio de cada sección es parte del contrato.** `RT_TPL_TOO_SIMILAR` mide el INVENTARIO
—lo único que un documento sabía declarar— y no ve la FORMA. Medido en la galería: veintidós de las
veintitrés arquitecturas no dejaban que ningún elemento tocara el borde de la pantalla, así que dos
arquetipos con inventarios distintos seguían leyéndose como la misma página con otra paleta. Los
tres envoltorios (`contenido`, `banda`, `fila`) son vocabulario compartido; lo propio de cada
arquetipo es CUÁL pide cada sección.

| Sección | Envoltorio | Por qué |
|---------|-----------|---------|
| `COMP-MAP-SEARCH` | **banda** | La identidad de este arquetipo ya dice que el plano es un MODO DE BÚSQUEDA con los mismos resultados dentro, no una ilustración de dónde está la agencia. Un modo de búsqueda dentro de una columna se lee como una captura de pantalla; llegando al cristal se lee como un mapa que se recorre. El conmutador Lista/Mapa y la nota se quedan en la columna de texto. |
| El resto | contenido | |

## 3. Secciones

### COMP-HEADER `[fijo]`
Objetivo: llamar sin bajar, y dar salida al propietario (conversión doble). Logo, nav corta,
**teléfono clickeable** y un botón secundario **"Vender tu casa"** que baja a `COMP-VALUATION-CTA`.
El botón de venta es secundario y no primario: comparte barra con el teléfono pero nunca compite con
él en peso. Mobile: teléfono visible, venta dentro del menú. Reutilizable: GLOBAL.

### COMP-SEARCH-HERO — el buscador ES la portada `[fijo · ADN] · TGL-SEARCH-FIELDS`
Objetivo: que la primera acción posible sea buscar (intención). Una imagen de fondo contenida —no a
sangre de 80vh— con el formulario **encima y centrado**: operación (comprar / alquilar) como
selector visible y no desplegable, zona, precio máximo y habitaciones. Cuatro campos como máximo:
el quinto es el que hace que se abandone. El H1 va encima del formulario y es corto, porque nadie lo
lee. Sin resultados aún: al enviar, la página baja a `COMP-PROPERTY-GRID` con los filtros aplicados.
Reutilizable: SECCIÓN.
**Nota:** el fondo lleva velo sólo donde cae el formulario, y se mide contra el peor píxel de esa
zona, nunca a ojo.

### COMP-PROPERTY-GRID — la cartera `[fijo · ADN] · TGL-GRID-DENSITY`
Objetivo: que el usuario descarte rápido y se quede con dos o tres (comparación). Rejilla de fichas,
cada una con **foto, precio, metros, habitaciones, baños, planta y zona** — los seis datos por los
que se descarta, todos visibles sin abrir. Los filtros de `COMP-SEARCH-HERO` siguen accesibles
arriba, fijos al hacer scroll: cambiar un filtro no debe obligar a volver a la portada. Contador de
resultados visible ("34 inmuebles"), porque un listado sin total no deja saber si merece la pena
seguir. Estado vacío con salida: si no hay resultados, se ofrece ampliar la zona o el precio, nunca
una página en blanco. Reutilizable: SECCIÓN.

### COMP-MAP-SEARCH — el plano como modo de búsqueda `[toggle TGL-MAP-MODE · ADN]`
Objetivo: buscar por dónde está y no por cómo se llama (geografía). **Los mismos resultados que la
rejilla**, sobre un plano, con chincheta por inmueble y el precio dentro de la chincheta. Mover el
plano refiltra; los dos modos comparten estado, así que volver a la rejilla conserva lo que el plano
dejó. Esta sección es lo que distingue a este arquetipo de cualquier otro listado del catálogo: un
coche no tiene sitio, un piso no es otra cosa que su sitio.
En móvil es un **conmutador** (Lista / Mapa) y no dos secciones apiladas: un plano de 300px de alto
bajo una rejilla no lo usa nadie. Reutilizable: SECCIÓN.
**Nota:** el plano necesita consentimiento previo si lo sirve un tercero — ver `wordpress-legal`.

### COMP-VISIT-REQUEST — la visita, con la referencia dentro `[fijo · ADN]`
Objetivo: convertir sobre UN inmueble concreto (conversión). Nombre, teléfono, franja horaria y
**la referencia del inmueble ya rellenada** cuando se llega desde una ficha. Sin referencia el
formulario sigue existiendo, pero pide entonces la zona y el presupuesto: una solicitud de visita
sin objeto es una consulta genérica, y una agencia no puede agendarla. Consentimiento RGPD explícito
y enlace a la política — ver `wordpress-forms`. Reutilizable: SECCIÓN.

### COMP-TEAM — quién abre la puerta `[toggle TGL-TEAM]`
Objetivo: poner cara a quien va a acompañar la visita (confianza). Retratos reales, nombre, zona de
la que se ocupa cada uno y teléfono directo. Sin cargos inventados. Se apaga en agencias de una
persona, donde una rejilla de un solo retrato se lee como una plantilla a medio llenar.
Reutilizable: SECCIÓN.

### COMP-VALUATION-CTA — captación de cartera `[fijo · ADN]`
Objetivo: que el propietario pida una tasación (conversión, la otra). Una banda ancha, no una
rejilla: un titular directo, dos o tres datos que la agencia sí puede sostener (tiempo medio de
venta, número de operaciones en la zona) y un formulario corto — dirección y teléfono. **Nunca
promete un precio en la web**: la tasación es presencial, y anunciar una cifra automática es la
promesa que la agencia luego tiene que desdecir. Reutilizable: SECCIÓN.

### COMP-FAQ — lo que se pregunta antes de firmar `[toggle TGL-FAQ]`
Objetivo: quitar del medio las dudas que frenan la visita (fricción). Gastos de compraventa, arras,
nota simple, quién paga qué. Acordeón, una abierta por defecto. Reutilizable: SECCIÓN.

### COMP-FOOTER `[fijo]`
Objetivo: cerrar con lo que la ley pide y lo que el usuario busca al final (cierre). NAP completo,
horario, **número de registro de agente inmobiliario** donde la comunidad autónoma lo exija, y
enlaces legales. Reutilizable: GLOBAL.

## 4. Toggles

| Toggle | Opciones | Por defecto | Qué cambia |
|--------|----------|-------------|------------|
| `TGL-SEARCH-FIELDS` | 3 campos / 4 campos | 4 campos | Cuántos filtros entran en el héroe. El quinto campo es el que hace abandonar |
| `TGL-GRID-DENSITY` | 2 col / 3 col / 4 col | 3 col | Cuántas fichas por fila en escritorio. 4 sólo con foto apaisada y carteras grandes |
| `TGL-MAP-MODE` | conmutador / sección / off | conmutador | Cómo entra el plano. `off` en carteras de una sola zona, donde el plano no discrimina nada |
| `TGL-TEAM` | on / off | on | Retratos del equipo. Off en agencias de una persona |
| `TGL-FAQ` | on / off | on | Dudas de compraventa |

## 5. Notas de implementación

- La cartera es CONTENIDO VIVO: se alimenta de un CPT (`propiedad`) con taxonomías de zona y
  operación, nunca de secciones escritas a mano. El resto de la home casi no cambia; la cartera
  cambia sola, y esa asimetría es la razón de que el buscador vaya delante.
- El estado de los filtros vive en la URL. Un resultado que no se puede enviar por WhatsApp no
  sirve en este sector, donde la decisión se toma entre dos personas.
- El plano y la rejilla comparten UNA fuente de resultados. Dos consultas separadas es cómo el
  contador acaba diciendo 34 y el plano enseñando 31.
- Imágenes: cada inmueble necesita foto propia. Reutilizar la foto de otro inmueble como relleno es
  la práctica que este arquetipo no admite, y en un mockup se resuelve con placeholder declarado, no
  con una foto de archivo que parezca real.
