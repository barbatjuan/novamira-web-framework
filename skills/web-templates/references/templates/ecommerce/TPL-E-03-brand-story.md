# TPL-E-03 — Brand Story

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Brand Story |
| Objetivo | Confianza + branding: la historia y los valores venden antes que el catálogo |
| Ecommerce ideal | Cosmética artesanal, joyería, marca de autor, alimentos premium, slow fashion |
| Ejemplos | Skincare natural, orfebrería, café de especialidad, marca sustentable |
| Nivel de contenido | Alto (editorial, storytelling) |
| Protagonismo del producto | Bajo–medio — una selección curada, no un catálogo |
| Protagonismo de la marca | Muy alto |
| ADN | Hero de 1 imagen fija chica, mucho aire, valores + historia larga + testimonios FIJOS, y cierra con la palabra del taller. NO slider, NO densidad, NO urgencia, NO sistema de categorías. |

## 2. Wireframe (top → bottom)

```
COMP-HEADER              [fijo]
COMP-HERO (imagen fija ~40vh) [fijo · ADN]
COMP-VALUES              [fijo · ADN] · los compromisos de la marca
COMP-ABOUT               [fijo · ADN] · historia, misión, proceso
COMP-PRODUCT-CAROUSEL    [fijo] · selección curada, no catálogo completo
COMP-TESTIMONIAL         [fijo · ADN] · social proof
COMP-CTA                 [fijo · ADN] · banda de cierre: la palabra del taller
COMP-NEWSLETTER          [toggle] · la carta del taller
COMP-FOOTER              [fijo]
```

Mucho aire, ritmo pausado. Ausencia intencional: sin slider, sin grillas densas, sin countdown,
sin promo agresiva, y **sin bloque de categorías**: una marca de autor tiene una selección, no un
árbol de rubros — el carrusel curado ya hace ese trabajo. Sin galería de campaña: acá la imagen
viene acompañada de párrafo, no sola (eso es TPL-E-01).

## 3. Secciones

### COMP-HEADER `[fijo]`
Objetivo: navegación sobria (branding). Logo protagonista (puede ir centrado), menú reducido,
cart, account. Mobile: logo centrado, hamburguesa, cart. Desktop: menú minimal, mucho espacio.
Sticky opcional (transparente sobre hero). Reutilizable: GLOBAL.

### COMP-HERO — imagen fija `[fijo · ADN] · TGL-HERO-TYPE fija-only`
Objetivo: transmitir sensación de marca (branding). 1 imagen fija (no slider), ~40vh, título +
subtítulo + 1 CTA suave. Mucho aire alrededor. Mobile: 35vh, texto centrado. Desktop: 40vh.
H1 único acá. Toggle: `TGL-HERO-TYPE` bloqueado en imagen fija (parte del ADN). Reutilizable:
SECCIÓN. Elementor: container con background + heading. Divi: Fullwidth Header.

### COMP-VALUES — los compromisos `[fijo · ADN]`
Objetivo: declarar en qué cree la marca (confianza + branding). 3–4 pilares (natural, cruelty-free,
hecho a mano, comercio justo, materia prima trazada) ícono/número + título + una línea.
Mobile: 1–2 columnas. Desktop: fila. Reutilizable: GLOBAL (`COMP-VALUES`).

**No es `COMP-BENEFITS`.** Hasta esta versión este bloque se declaraba como "COMP-BENEFITS —
valores", y ese nombre es el que hacía que este arquetipo se leyera igual que TPL-E-01 y TPL-E-02.
`COMP-BENEFITS` es la barra operativa de una tienda — envío, pago, garantía, devolución — y es
mobiliario de catálogo. Lo de acá son creencias, no condiciones de venta: se llama `COMP-VALUES`,
que es el id que `TPL-ABOUT-01` ya usaba para exactamente esto.

### COMP-ABOUT — la historia `[fijo · ADN]`
Objetivo: contar la historia/misión/proceso (branding + confianza). Bloques imagen + texto largo,
alternados; puede incluir cita, foto del fundador, el proceso paso a paso. Mobile: 1 columna,
imagen arriba. Desktop: 2 columnas alternadas, mucho aire. CTA: outline ("Conocé más").
Reutilizable: SECCIÓN (`COMP-ABOUT`, la misma gramática que TPL-C-02 y TPL-C-03).

**Tiene id porque si no, no existe.** Antes era una fila del wireframe escrita en prosa —
"Brand Story"— y una sección sin `COMP-*` es invisible para cualquier comparación automática: este
arquetipo y TPL-E-01 medían 89% idénticos justamente porque su única diferencia real (una historia
contra un lookbook) estaba escrita como texto suelto en los dos. `RT_TPL_NO_WIREFRAME` ahora lo
impide.

### COMP-PRODUCT-CAROUSEL — selección `[fijo] · TGL-CARD-STYLE`
Objetivo: mostrar productos selectos (venta suave). 4–6 cards imagen grande. Mobile: peek + swipe.
Desktop: 3–4. Cards con imagen grande (default, coherente con lo editorial). Reutilizable: GLOBAL.
Este carrusel ES el acceso al catálogo en esta plantilla: no hay bloque de categorías arriba.

### COMP-TESTIMONIAL `[fijo · ADN]`
Objetivo: social proof, refuerza confianza (confianza). 3+ reseñas con foto/nombre, o reseñas
destacadas. Mobile: carrusel. Desktop: 3 en fila o destacada grande. Reutilizable: GLOBAL. Fijo:
en esta plantilla los testimonios son parte del ADN, no se preguntan.

### COMP-CTA — la palabra del taller `[fijo · ADN] · TGL-CTA-STRENGTH`
Objetivo: cerrar respondiendo "¿vale lo que cuesta?" (conversión calma). Banda final con la promesa
que respalda la pieza: garantía del taller, reposición o reparación de por vida, cambio sin
preguntas, y quién atiende (nombre real, no un formulario). Un CTA suave, en outline, hacia
contacto o hacia la colección. Mobile: stack centrado, mucho aire. Desktop: claim ancho + 1 botón.
Reutilizable: GLOBAL (`COMP-CTA`).

**Por qué una promesa y no una oferta.** Este arquetipo vende caro contra alternativas industriales;
su última objeción no es el precio sino el riesgo de pagarlo. Un descuento acá contradice todo lo
que la página viene diciendo desde el hero — por eso el cierre es la palabra de quien lo hizo, y no
un cupón ni un countdown, que son el ADN de TPL-E-05.

### COMP-NEWSLETTER `[toggle TGL-NEWSLETTER]`
Objetivo: captación con tono de comunidad. Copy cálido, y con una razón concreta para suscribirse
(la carta del taller, las tandas cortas, el aviso de reposición). Reutilizable: GLOBAL.

### COMP-FOOTER `[fijo]`
Objetivo: navegación + legal + redes + valores. Puede repetir un mini-claim de marca.
Mobile: apilado. Desktop: 3–4 columnas. Reutilizable: GLOBAL.

## 4. Toggles admitidos

| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-HERO-TYPE` | imagen fija | **bloqueado** (ADN) |
| `TGL-CARD-STYLE` | imagen grande | |
| `TGL-TESTIMONIALS` | on | **bloqueado** (ADN): los testimonios no se preguntan |
| `TGL-IMAGERY` | foto | |
| `TGL-CTA-STRENGTH` | suave | ADN: la banda de cierre es una promesa, no una presión |
| `TGL-NEWSLETTER` | on | |
| `TGL-TRUST` | on (footer) | |

**Fijos:** COMP-HEADER, COMP-HERO (imagen fija), COMP-VALUES, COMP-ABOUT, COMP-PRODUCT-CAROUSEL,
COMP-TESTIMONIAL, COMP-CTA (cierre), COMP-FOOTER.
**Ausencias de ADN:** slider, grilla densa de producto, countdown, promo agresiva, search
protagonista, bloque de categorías, lookbook sin texto → si el cliente los pide, sugerir TPL-E-01,
TPL-E-02 o TPL-E-05.

## 5. SEO / semántica
1 `H1` (hero). `H2` en Valores, Historia, Testimonios, y en la banda de cierre. `H3` en subtítulos
de la historia. Texto largo = bueno para SEO editorial. `header` > `main` > `footer`.
