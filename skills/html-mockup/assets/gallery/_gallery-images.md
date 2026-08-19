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
