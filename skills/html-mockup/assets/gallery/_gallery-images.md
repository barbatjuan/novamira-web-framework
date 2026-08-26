# Gallery image manifest

The one image set every gallery strip renders, and the bridge that makes those
images survive the hop into WordPress.

## Why this file exists

`es_photo( $slug )` (`elementor-core/assets/es-builder.php`) resolves a
**WordPress attachment slug** — not a URL, not a `data:` URI. A mockup that shows
a real photograph and hands the build nothing but base64 is a promise the build
cannot keep: `es_img()` warns, the widget is written without an image, and the
client gets the grey box the mockup told them they would not get.
`agents/novamira-web-orchestrator.md` already names this failure — *"NO skill owns
image sourcing … the native build needs real assets or it ships grey boxes."*

So each row below carries the slug the operator uploads under. **The file name IS
the slug**: WordPress derives an attachment's `post_name` from the uploaded file
name, so uploading `hero-taller.webp` produces the slug `hero-taller`, which is
exactly what `es_photo( 'hero-taller' )` asks for. Rename a file and you break the
build, silently, in a way no gate here can see.

## Why one shared set

**Superseded by `catalog-envato-grade`.** The house axis-proof strips this section describes left
the catalogue gallery in Phase 2 (`RT_GALLERY_AXIS_LEAK`) along with eleven of the thirteen house
rows below; the argument still holds wherever axis proof lives now (`ux-design-system`'s own
surface), it just no longer describes anything in *this* manifest. Kept as history, not as a
current claim about the rows below — every remaining row belongs to a `$BRANDS` demo.

`_axis-proof-content.md` makes the same argument about copy: it is the constant,
so any visible difference between two renderings is the axes and nothing else.
Images are the other half of that. Every strip built on the HOUSE identity —
Piedra Valdés, forty of them — draws from the first thirteen rows, so when two of
those look different, the difference is the anchor or a **declared toggle**, never
a photograph one strip had and another did not.

**That premise stops at the house, and the day it stopped is recorded here rather
than papered over.** A shared set proves the axes; it cannot prove a CATALOGUE. A
gallery where every entry is the same stonemason answers "show me the difference
between two anchors" and cannot answer "quiero la de restaurante", which is the
question the catalogue exists for. So a BRAND — see `$BRANDS` in
`_build-gallery.php` — brings its own ground, accent, type and photographs, and
its rows are grouped under its own slug prefix below. Inside one brand the set is
still shared and the old argument still holds; ACROSS brands the photographs are
supposed to differ, because that is the thing being catalogued.

The cost of that is real and it is why brand sets are small: a brand gets one hero
and about six support frames at a tighter budget than the house rows, so thirty
brands land near 8 MB of base64 rather than past the ceiling. A brand that wants a
seventh photograph takes it out of another brand's allowance, not out of the
budget.

That second clause is not hedging, and it was added the day it stopped being
optional. `TPL-C-01 × PERS-EDITORIAL` resolves `TGL-HERO-TYPE` to `slider` and
renders three of these frames where its three siblings render one, so the older
sentence here — "the difference is the anchor, never the photograph" — became
false for that strip. The set is still shared and no strip has imagery of its
own; what changed is that CONFIGURATION is now a second declared dimension, and
a strip that resolves a toggle prints it on its own data bar beside the five
axes. The claim survives only because of that printing: an unlabelled toggle
would make this paragraph a lie the page had no way to correct.

It is also what makes the gallery fit. Unique imagery per strip is ~300 slots at
40+ KB, which is over the Artifact's 16 MB ceiling once base64 adds its third.
Shared, the whole set costs **900 KB raw / 1,200 KB base64 — 7.3% of the
ceiling**.

**Paid once because of the MECHANISM, not because the files are shared.** An
earlier version of this paragraph said the set was paid once "no matter how many
strips render it", and that was false: `<img src="data:…">` cannot dedupe, so
inline `src` attributes pay the bytes once per OCCURRENCE. Measured on the eight
phase-1 strips that comes to ~6.8 MB, and the plan's phase-2 count of forty lands
past the 16 MB ceiling — five times what the budget above claims. `_build-gallery.php`
therefore declares each slug's `data:` URI ONCE in a single map and hydrates it onto
every `<img data-img="…">` in one pass, which is the route the plan names ("una
pasada de JS"). The `<img>`, its `alt` and its slug all stay in the static DOM —
`RT_GALLERY_NO_MANIFEST`, grep and a screen reader all see `data-img="hero-taller"`,
which is strictly more legible than a base64 blob. Only the pixels need the script.

## Sourcing and licence

The house-quarry set below (`hero-cantera`, the one house row `catalog-envato-grade` kept — see
its note in the manifest) is Freepik **free-licence** stock, pulled through the connected
Freepik/Magnific MCP with `license: free`. None of the rows below are AI-generated unless their
own `Licence` cell says so explicitly (`Freepik AI (Pikaso)`) — checked per item on the
`aiGenerated` flag, because a gallery that sells craftsmanship should not illustrate it with a
synthesised craftsman.

Freepik's free licence requires **attribution where the images are published**.
The gallery is an internal tool and is not published, so nothing is owed today;
the moment a strip's imagery is reused on a live client site, that site owes the
attribution. Recorded here so the obligation travels with the files.

**Never re-source these from Envato.** This repository is public under Apache-2.0,
and its LICENSE grants every reader the right to redistribute what it contains —
a right an Envato subscription does not give us to pass on.

## Dos de `aranda-*` tampoco son fotografías, y el motivo es un dato

El reportaje de Motor Aranda son fotos reales de stock libre, y las dos tomas nuevas de la ficha de
unidad no lo son. No es descuido: es que **el stock no puede dar un cuentakilómetros que coincida
con la unidad**.

`TPL-UNIT-01` escribe en su propio contrato que el cuentakilómetros tiene que salir legible, y da la
razón: es el único dato de la ficha que el comprador puede verificar sin bajarse del sofá. La
unidad destacada tiene 48.200 km. La mejor foto libre del catálogo —un cuadro de mandos nítido y
bien iluminado— marcaba **195.478**. Publicarla habría sido escribir una regla sobre datos
verificables y romperla en el único sitio donde se ve, que es peor que no tener la regla.

Y hubo un segundo hallazgo que sólo apareció **mirando la hoja de contactos**: la toma que iba a ser
«interior desde la puerta» era otro cuadro de mandos. Dos frames distintos y el mismo encuadre. La
métrica de peso y proporción no ve eso — el paso de mirarlas existe justo para esto.

Las dos se generaron: una con la cifra pedida dentro del prompt (y verificada en pantalla, marca
48.200), y otra con la cabina entera desde la puerta del conductor. La celda `Licence` lo dice fila
por fila, que es donde tiene que decirse.

## Las siete de `inmo-*` no son fotografías (retirado)

**Sección retirada en `catalog-envato-grade` PR2b junto con las 15 filas `inmo-*` que describía.**
Existían para `TPL-C-13` (Cartera / Búsqueda), retirado en esta misma fase con reemplazo nombrado
`TPL-C-15 · Cartera curada` (`catalog-wrapper-integrity` Requisito 5). El pilar de la
inmobiliaria — incluida la decisión de generar en vez de fotografiar, y por qué — se retoma con su
propio set `delao-*` cuando `TPL-C-15` aterrice (PR3b); no hereda estas filas, cuyo slug prefix se
retira con ellas.

## Registers

**One row per surviving catalogue demo, not per content category.** `catalog-envato-grade`
Phase 2 replaced the four-register HOUSE table above — Quarry / Workshop / Finished / Material,
which counted the shared Piedra Valdés axis-proof set — with this one, because the axis-proof set
itself left the catalogue gallery (`RT_GALLERY_AXIS_LEAK`: a strip with no `$BRANDS` entry behind
it is personality-anchor proof, not a demo, and belongs under `ux-design-system`'s own surface).
Ten is the number of demos the catalogue commits to, built across this change's later phases;
declaring all ten now, before six of them exist, makes `R` larger and the per-shoot cap it derives
**tighter**, never looser — the conservative direction (`gallery-information-architecture` §
Registers Divisor Is Per-Demo).

| Register | Slugs | What it says |
|---|---|---|
| Lumière | `lumiere-*` | Belleza · centro de estética |
| Inmobiliaria de la O | *(pending — PR3b)* | Inmobiliaria · cartera curada, `TPL-C-15` |
| Motor Aranda | `aranda-*` | Automoción · ocasión |
| Lawyers | *(pending — PR9)* | Servicios legales |
| Alinea | `alinea-*` | Salud · coaching / entrenamiento personal |
| Gyms | *(pending — PR10)* | Fitness / gimnasios |
| Corte Nueve | `corte-*` | Moda · vaquero con ajuste |
| Bajura | `bajura-*` | Alimentación · pescado fresco |
| Tueste Norte | `tueste-*` | Alimentación · café por suscripción |
| Medida Justa | `estor-*` | Hogar · estores y cortinas a medida |

The count in this table is load-bearing, not decoration: `RT_GALLERY_ONE_SHOOT`
divides the set size by the number of rows here to get the per-shoot cap. Add a
register and the cap loosens; delete this table and the row fails, because a set
that declares no register structure has stated no diversity claim to measure a
shoot against. `RT_GALLERY_REGISTER_COUNT_MISMATCH` FAILs separately if this table
ever carries fewer rows than the gallery renders surviving branded demos.

**`Material`, generator-internal, is not a demo register and does not count toward the ten
above — and unlike the note further down about the three orphaned hero rows, this ONE is not
optional to reduce to a placeholder.** `_build-gallery.php` reads this row for two things, and
the second is stricter than the first: (1) `$MATERIAL_SLUGS` must be non-empty (a literal match on
a row reading exactly `Material`), or the build `fail()`s outright; (2) since the ink-exemption
itself was retired in favour of a measurement (see `_build-gallery.php`'s own `$INK_SWATCH`
comment, "THE SWATCH EXEMPTION IS RETIRED"), the build now requires **at least two** `square 1:1`
slugs in this row, so it can measure that the house ink still keeps their colours apart — a single
slug, or a slug of any other role, satisfies (1) and hard-`fail()`s on (2) with "no swatch PAIR to
measure the house ink against". No surviving demo has a `square`-role material-swatch pair today
(medida's `estor-muestras` is a `card 4:3` photo of physical fabric samples, not a colour
specification image), so `sq-marmol`/`sq-pizarra` — the original pair — are kept exactly as they
were, orphaned from every surviving archetype but load-bearing for this one generator check.
Kept OUTSIDE the ten-row table above on purpose, so `gallery_register_count()` — which reads the
FIRST table carrying a literal `Register` header cell — never counts it as an eleventh demo
register:

| Row | Slugs |
|---|---|
| Material | `sq-marmol` `sq-pizarra` |

## Shoots, and why the column is a proxy

Four registers say what the set is ABOUT. They say nothing about how many times
a shutter was pressed, and that is the axis this set actually failed on: seven of
its first fourteen images came from **one photographer's one session** — the same
bearded man in the same grey shirt, and two pairs among them (`card-patio` /
`card-labra`, `card-cantero` / `hero-taller`) were the same photograph reframed.
A register table full of "Workshop" reported that as healthy.

So every row carries a **Shoot** cell. Its value is `fp-` followed by the Freepik
id with its last three digits dropped, and `RT_GALLERY_ONE_SHOOT` RE-DERIVES it
from the Freepik cell rather than believing it. That is the whole point of
deriving instead of labelling: a hand-written shoot label can be edited until the
check goes green, which is a gate you fix by renaming rather than by fixing, and
this repository has shipped enough of those. The Freepik id cannot be edited
without also lying about the source the Licence cell stakes.

**Why the id at all.** Freepik assigns ids in upload order, so one contributor's
one upload session lands in a contiguous run. Measured on this set: the widest id
span WITHIN the known batch is 497 (`50621286` → `50621783`), and the narrowest
gap BETWEEN two different batches is 17,583 (`1033860` → `1051443`). A bucket
1,000 wide sits between those with 2× headroom under and 17× over.

**What this proxy cannot see, stated plainly:**

- A batch that straddles a multiple of 1,000 splits into two shoot keys and the
  set looks more diverse than it is. This fails QUIET, which is the bad direction.
  Nothing here detects it; only the contact sheet does.
- Two photographs of the same subject from genuinely different shoots — a
  different photographer, a different day, the same grey shirt and the same
  chisel — pass with distinct keys and still read as one picture.
- Two batches landing within 1,000 ids of each other merge into one key and the
  set looks LESS diverse than it is. That fails loud, which is the safe direction.

Which is to say: this column catches the cause that produced the defect, and it
is not a substitute for looking. **Look at them** — step 5 of "Adding one".

## The set

Every row carries its own **Licence** cell rather than leaning on the paragraph
above it. `RT_GALLERY_NO_MANIFEST` reads that cell per row, and it has to: a
blanket "all of these are free-licence" is true only until the fourteenth image
arrives from somewhere else, and it becomes false in silence — nothing about the
new row looks different from the old ones.

**Five rows below back no surviving demo and are kept anyway**, for reasons that have nothing to
do with any archetype: `hero-cantera`, `hero-taller` and `hero-encimera` are the fixed subjects of
unconditional, build-time contrast sweeps in `_build-gallery.php` (`$BG_SLUG`'s LP-BROKEN-GRID
scrim probe, `$SLIDER_FRAMES`'s slider-scrim/ink sweep, `$VIS_SLUG`'s visited-link probe) that run
on every build regardless of which archetypes exist — none of the ten surviving demos currently
resolve `TGL-HERO-TYPE` to `slider` (only the retired `TPL-C-01`/`TPL-E-01` admitted it), so these
three rows are orphaned from the CATALOGUE but not from the GENERATOR. `sq-marmol`/`sq-pizarra` are
kept for the same class of reason and are explained in full at their own `Material` row above.
Deleting any of the five moves a green build to a hard `fail()` on the very next run (measured, not
assumed, in `catalog-envato-grade` PR2b — the exact messages are "no image `hero-taller`" and
"no swatch PAIR to measure the house ink against").

| Slug | Role | Size | Weight | Freepik | Shoot | Licence | `alt` |
|---|---|---|---|---|---|---|---|
| `hero-cantera` | hero 16:9 | 1440×810 | 122 KB | 427777725 | fp-427777 | Freepik free | Vista aérea de una cantera a cielo abierto en bancadas |
| `hero-taller` | hero 16:9 | 1440×810 | 117 KB | 50621482 | fp-50621 | Freepik free | Cantero labrando un sillar a maceta y cincel en el taller |
| `hero-encimera` | hero 16:9 | 1440×810 | 114 KB | 427392968 | fp-427392 | Freepik free | Encimera de piedra natural con grifería negra en una cocina |
| `sq-marmol` | square 1:1 | 600×600 | 75 KB | 1051443 | fp-1051 | Freepik free | Superficie de mármol beige con veta natural |
| `sq-pizarra` | square 1:1 | 600×600 | 76 KB | 1033860 | fp-1033 | Freepik free | Granito gris de grano fino en placa |
| `aranda-patio` | hero 16:9 | 1440×810 | 59 KB | 5876725 | fp-5876 | Freepik free | Frontal de un coche oscuro en primer plano con otro claro desenfocado detrás |
| `aranda-v1` | card 4:3 | 720×540 | 33 KB | 423028458 | fp-423028 | Freepik free | Todoterreno azul de perfil en una carretera de otoño |
| `aranda-v2` | card 4:3 | 720×540 | 34 KB | 426304833 | fp-426304 | Freepik free | Todoterreno oscuro circulando por una calle nevada |
| `aranda-v3` | card 4:3 | 720×540 | 34 KB | 427284074 | fp-427284 | Freepik free | Berlina plateada en una autovía despejada |
| `aranda-v4` | card 4:3 | 720×540 | 32 KB | 426931288 | fp-426931 | Freepik free | Todoterreno blanco en carretera invernal al atardecer |
| `aranda-v5` | card 4:3 | 720×540 | 33 KB | 419374753 | fp-419374 | Freepik free | Berlina moderna circulando por una calle de ciudad |
| `aranda-v6` | card 4:3 | 720×540 | 33 KB | 422700761 | fp-422700 | Freepik free | Compacto gris de perfil en una vía rápida |
| `aranda-u-cuadro` | card 4:3 | 800×600 | 42 KB | 5234126956 | fp-5234126 | Freepik AI (Pikaso) | Cuadro de mandos con el cuentakilómetros marcando 48.200 km |
| `aranda-u-interior` | card 4:3 | 800×600 | 42 KB | 5234127412 | fp-5234127 | Freepik AI (Pikaso) | Interior visto desde la puerta del conductor: dos asientos de tela gris y consola |
| `aranda-u-maletero` | card 4:3 | 800×600 | 44 KB | 5234185348 | fp-5234185 | Freepik AI (Pikaso) | Maletero abierto y vacío, con el patio del concesionario detrás |
| `alinea-hero` | hero 16:9 | 1440×810 | 114 KB | 27071287 | fp-27071 | Freepik free | Joven con ortodoncia metálica sentado en el sillón dental |
| `alinea-fase` | card 4:3 | 720×540 | 34 KB | 28031013 | fp-28031 | Freepik free | Mujer joven sonriendo sentada en la clínica dental |
| `alinea-consulta` | card 4:3 | 720×540 | 34 KB | 26955735 | fp-26955 | Freepik free | Odontóloga y paciente conversando en el gabinete |
| `corte-v1` | card 4:3 | 800×600 | 38 KB | 5220559242 | fp-5220559 | Freepik AI (Pikaso) | Vaquero recto azul medio extendido sobre fondo liso, visto desde arriba |
| `corte-v2` | card 4:3 | 800×600 | 9 KB | 5220559896 | fp-5220559 | Freepik AI (Pikaso) | Vaquero pitillo negro extendido sobre fondo liso, visto desde arriba |
| `corte-v3` | card 4:3 | 800×600 | 23 KB | 5220560517 | fp-5220560 | Freepik AI (Pikaso) | Vaquero ancho de lavado claro extendido sobre fondo liso |
| `corte-v4` | card 4:3 | 800×600 | 55 KB | 5220561366 | fp-5220561 | Freepik AI (Pikaso) | Chaqueta vaquera índigo extendida sobre fondo liso |
| `corte-v5` | card 4:3 | 800×600 | 15 KB | 5220563473 | fp-5220563 | Freepik AI (Pikaso) | Pantalón chino verde oliva extendido sobre fondo liso |
| `corte-v6` | card 4:3 | 800×600 | 61 KB | 5220564170 | fp-5220564 | Freepik AI (Pikaso) | Camisa de trabajo en chambray extendida sobre fondo liso |
| `corte-cuerpo1` | card 4:3 | 800×600 | 22 KB | 5220564578 | fp-5220564 | Freepik AI (Pikaso) | El mismo vaquero recto sobre un cuerpo delgado, de cintura a tobillo |
| `corte-cuerpo2` | card 4:3 | 800×600 | 19 KB | 5220565363 | fp-5220565 | Freepik AI (Pikaso) | El mismo vaquero recto sobre un cuerpo mediano, de cintura a tobillo |
| `corte-cuerpo3` | card 4:3 | 800×600 | 22 KB | 5220565877 | fp-5220565 | Freepik AI (Pikaso) | El mismo vaquero recto sobre un cuerpo grande, de cintura a tobillo |
| `bajura-pieza` | card 4:3 | 800×600 | 105 KB | 5220580086 | fp-5220580 | Freepik AI (Pikaso) | Lubina entera sobre hielo picado en una losa de pizarra |
| `bajura-lomo` | card 4:3 | 800×600 | 118 KB | 5220580693 | fp-5220580 | Freepik AI (Pikaso) | Lomo de atún rojo cortado grueso sobre hielo picado |
| `bajura-marisco` | card 4:3 | 800×600 | 88 KB | 5220582159 | fp-5220582 | Freepik AI (Pikaso) | Langostinos crudos colocados sobre hielo picado |
| `bajura-lonja` | card 4:3 | 800×600 | 116 KB | 5220582285 | fp-5220582 | Freepik AI (Pikaso) | Nave de subasta al amanecer con cajas de pescado en hilera |
| `bajura-caja` | card 4:3 | 800×600 | 45 KB | 5220583908 | fp-5220583 | Freepik AI (Pikaso) | Caja isotérmica abierta con acumuladores de frío y una pieza envasada |
| `bajura-puerto` | panoramic 2:1 | 1200×570 | 76 KB | 5220585204 | fp-5220585 | Freepik AI (Pikaso) | Vista aérea cenital de un puerto pesquero pequeño al amanecer |
| `bajura-corte` | card 4:3 | 800×600 | 56 KB | 5220586038 | fp-5220586 | Freepik AI (Pikaso) | Manos de pescadero fileteando una pieza sobre mostrador de acero |
| `tueste-hero` | hero 16:9 | 1440×810 | 147 KB | 5220586812 | fp-5220586 | Freepik AI (Pikaso) | Grano recién tostado saliendo de la bandeja de enfriado del tostador |
| `tueste-caja` | card 4:3 | 800×600 | 52 KB | 5220587240 | fp-5220587 | Freepik AI (Pikaso) | Caja de envío abierta con una bolsa de café kraft y una ficha |
| `tueste-bolsa` | card 4:3 | 800×600 | 32 KB | 5220587983 | fp-5220587 | Freepik AI (Pikaso) | Bolsa de café kraft de pie junto a grano tostado suelto |
| `tueste-taza` | card 4:3 | 800×600 | 58 KB | 5220590270 | fp-5220590 | Freepik AI (Pikaso) | Café de filtro cayendo a una jarra de vidrio, con vapor |
| `tueste-molino` | card 4:3 | 800×600 | 46 KB | 5220589424 | fp-5220589 | Freepik AI (Pikaso) | Café recién molido en el cajón de un molinillo manual |
| `estor-hero` | hero 16:9 | 1440×810 | 36 KB | 5220845913 | fp-5220845 | Freepik AI (Pikaso) | Estor de lino claro montado en una ventana de salón, visto de frente |
| `estor-muestras` | card 4:3 | 800×600 | 42 KB | 5220846547 | fp-5220846 | Freepik AI (Pikaso) | Muestrario de tejidos de lino y algodón abierto en abanico sobre una mesa de taller |
| `estor-medicion` | card 4:3 | 800×600 | 23 KB | 5220847144 | fp-5220847 | Freepik AI (Pikaso) | Medición del hueco de una ventana con medidor láser y metro plegable |
| `estor-taller` | card 4:3 | 800×600 | 88 KB | 5220848159 | fp-5220848 | Freepik AI (Pikaso) | Rollo de tejido cortándose a medida en el banco del taller |
| `estor-cortina` | card 4:3 | 800×600 | 30 KB | 5220849652 | fp-5220849 | Freepik AI (Pikaso) | Cortina de lino con tabla montada en un dormitorio |
| `estor-oficina` | card 4:3 | 800×600 | 28 KB | 5220850130 | fp-5220850 | Freepik AI (Pikaso) | Estores anchos montados en el ventanal de una oficina, vistos desde dentro |
| `lumiere-cabina` | hero 16:9 | 1440×810 | 119 KB | 44121496 | fp-44121 | Freepik free | Camilla de estética vestida en blanco, con velas encendidas y una planta al fondo |
| `lumiere-rostro` | card 4:3 de marca | 720×540 | 31 KB | 13296110 | fp-13296 | Freepik free | Clienta tumbada con banda en el pelo durante un tratamiento facial |
| `lumiere-cuerpo` | card 4:3 de marca | 720×540 | 33 KB | 13341416 | fp-13341 | Freepik free | Manos de esteticista aplicando aceite en la pierna de una clienta tapada con toalla |
| `lumiere-manos` | card 4:3 de marca | 720×540 | 32 KB | 20268070 | fp-20268 | Freepik free | Manos de una clienta sobre la toalla junto al torno y las herramientas de manicura |
| `lumiere-depilacion` | card 4:3 de marca | 720×540 | 30 KB | 10025233 | fp-10025 | Freepik free | Esteticista con guantes extendiendo cera tibia en la pierna con una espátula |
| `lumiere-recepcion` | card 4:3 de marca | 720×540 | 33 KB | 209152350 | fp-209152 | Freepik free | Mostrador de recepción en madera y terracota con flores y lámparas colgantes |
| `lumiere-cosmetica` | card 4:3 de marca | 720×540 | 32 KB | 13983381 | fp-13983 | Freepik free | Frascos de cosmética sobre peanas rosas en un bodegón de estudio |
| `lumiere-pilar` | square 1:1 | 600×600 | 36 KB | 5227011918 | fp-5227011 | Freepik AI (Pikaso) | Retrato de esteticista veterana de pelo blanco corto, fondo cálido liso |
| `lumiere-hugo` | square 1:1 | 600×600 | 39 KB | 5227012641 | fp-5227012 | Freepik AI (Pikaso) | Retrato de masajista con barba corta, fondo cálido liso |
| `lumiere-noa` | square 1:1 | 600×600 | 39 KB | 5227013872 | fp-5227013 | Freepik AI (Pikaso) | Retrato de técnica de uñas sonriendo, fondo cálido liso |

Las veintiuna filas `corte-*`, `bajura-*` y `tueste-*` son las tres verticales de ecommerce que
el catálogo tenía escritas y no podía enseñar. Están GENERADAS, y valen para
una maqueta por lo mismo: enseñan de qué va la sección sin afirmar que existan esa tienda, ese
barco ni ese tostadero. En un sitio de cliente NINGUNA de las tres sirve — la ropa hay que
fotografiarla sobre los cuerpos reales que la venden, el pescado es el de la lonja de ese día, y
la caja es la caja que el cliente manda.

Y una nota sobre las tres de `corte-cuerpo*`: son la MISMA prenda sobre tres cuerpos distintos,
que es el dato entero de `COMP-FIT-GALLERY`. Sustituir una por otra prenda rompe la sección sin
romper nada que un gate pueda ver.

## Las siete de `lumiere-*` volvieron a ser fotografías, y dos no llegaron

**Son de archivo de Freepik con licencia libre y ninguna está generada** — `aiGenerated`
comprobado por ítem. Después de veintiocho filas seguidas de Pikaso conviene decir por qué: un
centro de estética es de los pocos negocios de este catálogo cuya escena EXISTE en el archivo
libre en cantidad, así que generar habría sido inventar lo que se podía fotografiar. Siete
identificadores en siete cubos distintos, así que `RT_GALLERY_ONE_SHOOT` las mide como siete
reportajes y no como uno.

**Presupuesto exacto: un hero y seis apoyos.** Salieron OCHO del proceso y dos se cayeron al
mirarlas en hoja de contactos —el paso 5 de «Adding one»—, que es donde este manifiesto lleva
avisando desde el principio que aparecen los defectos que ninguna medición ve:

- **Una sala con lámpara quirúrgica, camilla de papel y azulejo gris.** Fallaba dos veces a la
  vez. Por REGISTRO: es el lenguaje visual de `TPL-C-10`, y `TPL-C-14` se prohíbe a sí mismo ese
  lenguaje en su propio documento —sin antes/después, sin colegiado— porque promete acto médico
  donde no lo hay; una foto puede romper esa promesa igual que un titular. Y por ENCUADRE: era
  una camilla vacía en tres cuartos, o sea el hero otra vez, que es exactamente el defecto que
  costó un hero y tres imágenes en el set de la casa.
- **Un bodegón de spa de hotel** —biombo de bambú, orquídea, conchas, toallas enrolladas—. No
  era falso, era genérico: iba a ilustrar los BONOS, y una concha no dice bono. Se cambió por el
  bodegón de frascos sobre peanas rosas, que además fija la paleta de la marca en una imagen.

Las siete que quedan cubren registros distintos a propósito: el espacio vacío (`cabina`), tres
tratamientos en curso que además SON sus salas (`rostro`, `cuerpo`, `depilacion`), el detalle de
oficio (`manos`), la puerta (`recepcion`) y el producto (`cosmetica`).

**En un sitio de cliente ninguna de las siete vale.** La cabina es la cabina de ese centro, y la
recepción es la suya: son justo las dos fotografías que este arquetipo pide que sean reales,
porque su argumento entero es «mira dónde te lo hacemos».

**Y tres retratos que SÍ están generados**: `TPL-ABOUT-03`
lleva `COMP-TEAM` como ADN y el archivo libre no da tres caras que se lean como un mismo equipo.
La advertencia viaja con ellas sin cambiar una coma: **en la web de un centro real estas tres no
valen** — ahí van las caras de quien abre la puerta, o no va la sección.

El primer intento fue pedir tres variantes de UN encargo, y devolvió tres renderizados de la
MISMA mujer: misma cara, mismo pelo, misma túnica. Tres tarjetas de una sola persona son peores
que una, y es literalmente el defecto que este manifiesto lleva describiendo desde el apartado
de los reportajes —«el mismo hombre con la misma camisa gris»— reaparecido por la puerta del
generador en vez de por la del archivo. Un `count` alto varía el encuadre, no el sujeto: la
diversidad hay que ENCARGARLA, un encargo por persona. Los tres de ahora son tres edades, dos
géneros y tres tonos de piel, con la misma luz y la misma túnica para que sigan siendo un
equipo y no tres fotos sueltas. Y se vio mirando, no midiendo: los dos juegos pesaban lo mismo,
llevaban tres identificadores distintos y ningún cubo compartido.

`alt` is Spanish because it lands on a Spanish site, and it describes what is in
the frame rather than repeating the section heading — an `alt` that restates the
`h2` is a screen-reader user hearing the same sentence twice. It describes the
FRAME and not the card's heading, which is why `card-patio` reads "pulidora …
canto" under a services card headed *Fachadas y sillería*: the heading is the
axis-proof constant, the photograph is what the reader actually sees.

## Weights are measured, not chosen

Each file is the highest WebP quality that still fits its role's budget, found by
binary search rather than a fixed quality, because these originals differ by
roughly 8× in detail: one quality setting would blow the budget on the busy frames
and waste it on the flat ones.

The budgets themselves were unwritten until now, which made "fits its role's
budget" a procedure with an unstated parameter — reproducible only by whoever ran
it first. They are:

| Role | Budget | Reached |
|---|---|---|
| hero 16:9 | 125 KiB | q-varies, 114–122 KB |
| card 4:3 | 46 KiB | q85 / q94 on the two new frames |
| card 4:3 de marca | 34 KiB | q21–q88, y el rango ES el argumento del apartado siguiente |
| square 1:1 | 40 KiB | q85 |
| swatch 1:1 | 80 KiB | see below |
| panoramic 21:9 | 105 KiB | — |

The two material swatches carry a larger budget than the portraits, and that is a
measurement, not a preference. A full-frame stone texture is fine grain edge to
edge — the worst case WebP has. At the portrait budget `sq-marmol` floors at
43.5 KB and `sq-pizarra` at 58.4 KB, so the cap was unreachable rather than merely
expensive. The cap was wrong, not the image.

That carve-out is for the swatch, which is the PRODUCT. It does not generalise to
any texture: a stacked-slate frame considered for `card-patio` reached only q10 at
the card budget and still only q32 at the swatch budget — 78% more bytes than any
other card, for a services thumbnail. It was dropped on that measurement, not on
taste.

## Adding one

1. `stock_search` with `license: free`, then `stock_download`. Check `aiGenerated`
   per item.
2. Centre-crop to the role's ratio **before** scaling — scaling first squashes,
   cropping after throws away resolution already paid for. If centre-cropping
   ruins the frame, the IMAGE is wrong for that role; do not invent an off-centre
   rule. One candidate here lost its subject to the left edge at 1:1 and was
   moved to a 4:3 slot instead.
3. Binary-search the quality against the role budget above; do not pick a number.
4. Add its row here **with the slug**, or `RT_GALLERY_NO_MANIFEST` fails the build,
   and **with the Shoot cell**, or `RT_GALLERY_ONE_SHOOT` does.
5. Look at it. A contact sheet catches the duplicate framing that a file listing
   cannot — this set lost one hero that way after it was already converted, and
   later lost three more images to a duplicate-framing pair that every listing,
   and one perceptual hash, had reported as fine.
