# Gallery image manifest

The one image set every gallery strip renders, and the bridge that makes those
images survive the hop into WordPress.

## Why this file exists

`es_photo( $slug )` (`elementor-core/assets/es-builder.php`) resolves a
**WordPress attachment slug** — not a URL, not a `data:` URI. A mockup that shows
a real photograph and hands the build nothing but base64 is a promise the build
cannot keep: `es_img()` warns, the widget is written without an image, and the
client gets the grey box the mockup told them they would not get.
`agents/wordpress-orchestrator.md` already names this failure — *"NO skill owns
image sourcing … the native build needs real assets or it ships grey boxes."*

So each row below carries the slug the operator uploads under. **The file name IS
the slug**: WordPress derives an attachment's `post_name` from the uploaded file
name, so uploading `hero-taller.webp` produces the slug `hero-taller`, which is
exactly what `es_photo( 'hero-taller' )` asks for. Rename a file and you break the
build, silently, in a way no gate here can see.

## Why one shared set

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

All thirteen are Freepik **free-licence** stock, pulled through the connected
Freepik/Magnific MCP with `license: free`. None is AI-generated — checked per item
on the `aiGenerated` flag, because a gallery that sells craftsmanship should not
illustrate it with a synthesised craftsman.

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

## Las siete de `inmo-*` no son fotografías

**Están GENERADAS, y la columna `Licence` lo dice en vez de dejarlas pasar por lo que no son.**
El resto de este manifiesto son fotografías de archivo de Freepik; éstas salieron de **Freepik
Pikaso**, que es el generador de la misma casa y entrena sobre la biblioteca propia de Freepik,
así que llevan su licencia comercial. Esa es la razón de que estén aquí y no otra: la regla del
repo es *imágenes sólo Freepik libre*, y ésta es la parte de Freepik que sabe dibujar un piso.

**Por qué hicieron falta.** `TPL-C-13` publica una cartera de inmuebles y en las 45 filas
anteriores no hay una sola vivienda: lo más cercano son una cantera y seis coches de ocasión.
Meter un coche en una ficha de piso es el defecto que el § 5 del propio arquetipo prohíbe, y
reutilizar el reportaje de Piedra Valdés habría cargado la cota de `RT_GALLERY_ONE_SHOOT` sin
ganar nada. Durante un commit la tira fue con placeholder marcado, que era la respuesta honesta
mientras no hubiera imágenes.

**El `Shoot` se deriva igual que el de las demás** — el identificador sin sus tres últimos
dígitos — y lleva el mismo prefijo `fp-`. Se intentó `pk-` para que la procedencia se leyera de un
vistazo y la regla lo rechazó con razón: el `Shoot` es una CLAVE DE AGRUPACIÓN y la procedencia va
en `Licence`, así que meter las dos cosas en una celda habría roto la derivación a cambio de nada.
Cada una
es una generación independiente con su propio encargo, así que no forman un reportaje en el
sentido que `RT_GALLERY_ONE_SHOOT` persigue: siete escenas distintas, no siete encuadres de la
misma tarde. La cota sigue midiéndolas de todos modos, que es como debe ser — la regla no se fía
de la prosa de este párrafo y hace bien.

**Las caras llegaron después, y con una advertencia que viaja con ellas.** Durante un commit
`COMP-TEAM` fue con placeholder por un motivo que sigue siendo verdad: un retrato inventado en la
web de una agencia rompe una promesa concreta —«éste es quien te abre la puerta»—. En la GALERÍA,
que es una herramienta interna y no la web de nadie, no hay promesa que romper y un hueco a la
derecha del último retrato se lee como una tarjeta que falta. **En un sitio de cliente estas tres
no valen**: ahí van las caras reales o no va la sección.

## Registers

Four, deliberately. A single register would have shipped forty strips of the same
man in the same grey shirt, which reads as exactly the cheap template the gallery
exists to disprove.

| Register | Slugs | What it says |
|---|---|---|
| Quarry | `hero-cantera` | Where the material comes from |
| Workshop | `hero-taller` `card-cantero` `card-patio` `card-labra` `card-detalle` `sq-manos` | The work itself |
| Finished | `hero-encimera` `card-veta` `card-mueble` `pan-fachada` | What the client buys |
| Material | `sq-marmol` `sq-pizarra` | The swatch |

The count in this table is load-bearing, not decoration: `RT_GALLERY_ONE_SHOOT`
divides the set size by the number of rows here to get the per-shoot cap. Add a
register and the cap loosens; delete this table and the row fails, because a set
that declares no register structure has stated no diversity claim to measure a
shoot against.

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

| Slug | Role | Size | Weight | Freepik | Shoot | Licence | `alt` |
|---|---|---|---|---|---|---|---|
| `hero-taller` | hero 16:9 | 1440×810 | 117 KB | 50621482 | fp-50621 | Freepik free | Cantero labrando un sillar a maceta y cincel en el taller |
| `hero-cantera` | hero 16:9 | 1440×810 | 122 KB | 427777725 | fp-427777 | Freepik free | Vista aérea de una cantera a cielo abierto en bancadas |
| `hero-encimera` | hero 16:9 | 1440×810 | 114 KB | 427392968 | fp-427392 | Freepik free | Encimera de piedra natural con grifería negra en una cocina |
| `card-cantero` | card 4:3 | 800×600 | 46 KB | 24492177 | fp-24492 | Freepik free | Operario cortando una placa de piedra en una tronzadora de raíl |
| `card-patio` | card 4:3 | 800×600 | 38 KB | 22698676 | fp-22698 | Freepik free | Pulidora de disco repasando el canto de una placa clara |
| `card-labra` | card 4:3 | 800×600 | 44 KB | 50621286 | fp-50621 | Freepik free | Labra de un bloque con herramienta neumática |
| `card-detalle` | card 4:3 | 800×600 | 44 KB | 50621416 | fp-50621 | Freepik free | Detalle del cincel abriendo la superficie de la piedra |
| `card-veta` | card 4:3 | 800×600 | 43 KB | 425368036 | fp-425368 | Freepik free | Panel de piedra con veta dorada junto a un frente de madera |
| `card-mueble` | card 4:3 | 800×600 | 39 KB | 427487926 | fp-427487 | Freepik free | Mueble con frente de piedra y cajones abiertos |
| `sq-manos` | square 1:1 | 600×600 | 39 KB | 3762376 | fp-3762 | Freepik free | Manos enguantadas tronzando un bloque de piedra con radial |
| `sq-marmol` | square 1:1 | 600×600 | 75 KB | 1051443 | fp-1051 | Freepik free | Superficie de mármol beige con veta natural |
| `sq-pizarra` | square 1:1 | 600×600 | 76 KB | 1033860 | fp-1033 | Freepik free | Granito gris de grano fino en placa |
| `pan-fachada` | panoramic 21:9 | 1440×617 | 102 KB | 410749048 | fp-410749 | Freepik free | Fachada barroca en piedra labrada de Lecce |
| `terrazza-sala` | hero 16:9 | 1440×810 | 114 KB | 7734844 | fp-7734 | Freepik free | Comedor de ladrillo visto con mesas corridas de madera y lámparas colgantes |
| `terrazza-mesa` | card 4:3 | 720×540 | 34 KB | 11541171 | fp-11541 | Freepik free | Mesa larga vestida para una cena, con velas y sillas doradas |
| `terrazza-plato` | card 4:3 | 720×540 | 33 KB | 5449221 | fp-5449 | Freepik free | Plato de carne con salsa de cerezas servido en una mesa oscura |
| `terrazza-chef` | card 4:3 | 720×540 | 32 KB | 13273102 | fp-13273 | Freepik free | Cocinero terminando un plato con una cuchara en la cocina |
| `terrazza-velas` | card 4:3 | 720×540 | 34 KB | 5697815 | fp-5697 | Freepik free | Velas encendidas sobre una repisa de madera del comedor |
| `terrazza-coctel` | card 4:3 | 720×540 | 34 KB | 3421110 | fp-3421 | Freepik free | Rincón del comedor con pared de ladrillo y tablas de madera colgadas |
| `terrazza-terraza` | card 4:3 | 720×540 | 34 KB | 3742308 | fp-3742 | Freepik free | Terraza de restaurante en la calle con mesas y sillas de mimbre |
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
| `auria-hero` | hero 16:9 | 1440×810 | 81 KB | 427093861 | fp-427093 | Freepik free | Deportivo oscuro de frente en una carretera de campo con el fondo barrido |
| `auria-interior` | card 4:3 | 720×540 | 33 KB | 13337874 | fp-13337 | Freepik free | Interior de piel marrón y negra con salpicadero y consola |
| `auria-puerta` | card 4:3 | 720×540 | 32 KB | 11678381 | fp-11678 | Freepik free | Puerta abierta de un coche con tapicería clara bajo luz de garaje |
| `auria-arranque` | card 4:3 | 720×540 | 33 KB | 13554714 | fp-13554 | Freepik free | Detalle del botón de arranque en una consola central negra |
| `bergara-nave` | card 4:3 | 720×540 | 34 KB | 12750560 | fp-12750 | Freepik free | Mecánico apretando una pieza bajo un coche elevado en el taller |
| `bergara-rueda` | card 4:3 | 720×540 | 33 KB | 10001443 | fp-10001 | Freepik free | Operario sujetando un neumático en el taller de cambio |
| `arbea-consulta` | hero 16:9 | 1440×810 | 116 KB | 17437967 | fp-17437 | Freepik free | Gabinete dental con sillón moderno y equipo junto a un ventanal |
| `arbea-material` | card 4:3 | 720×540 | 32 KB | 18264637 | fp-18264 | Freepik free | Instrumental dental estéril dispuesto sobre la bandeja |
| `arbea-micro` | card 4:3 | 720×540 | 34 KB | 416006272 | fp-416006 | Freepik free | Odontóloga trabajando con microscopio dental sobre una paciente |
| `arbea-radio` | card 4:3 | 720×540 | 34 KB | 26766940 | fp-26766 | Freepik free | Profesional mostrando una radiografía dental en una tableta |
| `arbea-doctor` | card 4:3 | 720×540 | 32 KB | 17296182 | fp-17296 | Freepik free | Retrato de odontólogo sonriendo en su gabinete |
| `arbea-antes` | card 4:3 | 720×540 | 31 KB | 8404196 | fp-8404 | Freepik free | Paciente mostrando la dentadura en consulta |
| `arbea-despues` | card 4:3 | 720×540 | 33 KB | 8896977 | fp-8896 | Freepik free | Paciente comprobando su dentadura en un espejo de mano |
| `alinea-hero` | hero 16:9 | 1440×810 | 114 KB | 27071287 | fp-27071 | Freepik free | Joven con ortodoncia metálica sentado en el sillón dental |
| `alinea-fase` | card 4:3 | 720×540 | 34 KB | 28031013 | fp-28031 | Freepik free | Mujer joven sonriendo sentada en la clínica dental |
| `alinea-consulta` | card 4:3 | 720×540 | 34 KB | 26955735 | fp-26955 | Freepik free | Odontóloga y paciente conversando en el gabinete |
| `urgencia-box` | card 4:3 | 720×540 | 32 KB | 22511062 | fp-22511 | Freepik free | Odontóloga tratando a un paciente con dolor en el box de urgencias |
| `urgencia-turno` | card 4:3 | 720×540 | 34 KB | 15933809 | fp-15933 | Freepik free | Auxiliar de clínica dental de turno mirando a cámara |
| `inmo-calle` | panoramic 21:9 | 1440×617 | 132 KB | 5215694971 | fp-5215694 | Freepik AI (Pikaso) | Calle residencial de manzana cerrada con balcones de forja, al atardecer |
| `inmo-reformado` | card 4:3 | 800×600 | 30 KB | 5215695836 | fp-5215695 | Freepik AI (Pikaso) | Salón reformado con parqué de roble y ventanal al balcón |
| `inmo-atico` | card 4:3 | 800×600 | 50 KB | 5215697660 | fp-5215697 | Freepik AI (Pikaso) | Terraza de ático con solado claro y barandilla de vidrio sobre los tejados |
| `inmo-bajo` | card 4:3 | 800×600 | 34 KB | 5215697234 | fp-5215697 | Freepik AI (Pikaso) | Bajo sin reformar con salida a un patio interior enlosado |
| `inmo-piedra` | card 4:3 | 800×600 | 56 KB | 5215699667 | fp-5215699 | Freepik AI (Pikaso) | Estancia con muro de piedra vista, vigas de madera y ventana pequeña |
| `inmo-adosado` | card 4:3 | 800×600 | 57 KB | 5215700160 | fp-5215700 | Freepik AI (Pikaso) | Fachada de adosado con garaje doble y seto recortado |
| `inmo-estudio` | card 4:3 | 800×600 | 32 KB | 5215701894 | fp-5215701 | Freepik AI (Pikaso) | Estudio de una sola pieza con cocina en un lateral |
| `inmo-nerea` | square 1:1 | 600×600 | 18 KB | 5216694377 | fp-5216694 | Freepik AI (Pikaso) | Retrato de agente inmobiliaria, fondo gris liso |
| `inmo-julen` | square 1:1 | 600×600 | 17 KB | 5216695166 | fp-5216695 | Freepik AI (Pikaso) | Retrato de agente inmobiliario, fondo gris liso |
| `inmo-leire` | square 1:1 | 600×600 | 20 KB | 5216696534 | fp-5216696 | Freepik AI (Pikaso) | Retrato de agente inmobiliaria veterana, fondo gris liso |
| `inmo-plano` | panoramic 2:1 | 1200×570 | 119 KB | 5218258085 | fp-5218258 | Freepik AI (Pikaso) | Vista aérea cenital de un distrito urbano europeo con manzanas, un parque y un río |
| `inmo-p-cocina` | card 4:3 | 800×600 | 42 KB | 5234118861 | fp-5234118 | Freepik AI (Pikaso) | Cocina con muebles blancos, encimera de piedra clara y ventana a un patio |
| `inmo-p-dormitorio` | card 4:3 | 800×600 | 41 KB | 5234118808 | fp-5234118 | Freepik AI (Pikaso) | Dormitorio principal con cama vestida en blanco y armario empotrado |
| `inmo-p-bano` | card 4:3 | 800×600 | 44 KB | 5234119696 | fp-5234119 | Freepik AI (Pikaso) | Baño con ducha de obra, mampara de vidrio y lavabo suspendido |
| `inmo-p-plano` | plan 3:2 | 1200×800 | 71 KB | 5234119490 | fp-5234119 | Freepik AI (Pikaso) | Plano de planta de un piso de tres dormitorios, línea negra sobre blanco, con cotas |
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
el catálogo tenía escritas y no podía enseñar. Están GENERADAS, como las `inmo-*`, y valen para
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

**Y tres retratos que SÍ están generados**, por lo mismo que los de `inmo-*`: `TPL-ABOUT-03`
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
