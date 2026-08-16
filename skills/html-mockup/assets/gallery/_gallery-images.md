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
Images are the other half of that. Every strip in the gallery draws from these
fourteen, so when two strips look different, the difference is the anchor —
never the photograph.

It is also what makes the gallery fit. Unique imagery per strip is ~300 slots at
40+ KB, which is over the Artifact's 16 MB ceiling once base64 adds its third.
Shared, the whole set costs **1,022 KB raw / 1,362 KB base64 — 8.3% of the
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

All fourteen are Freepik **free-licence** stock, pulled through the connected
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

Three, deliberately. A single register would have shipped forty strips of the same
man in the same grey shirt, which reads as exactly the cheap template the gallery
exists to disprove.

| Register | Slugs | What it says |
|---|---|---|
| Quarry | `hero-cantera` | Where the material comes from |
| Workshop | `hero-taller` `hero-banco` `card-cantero` `card-patio` `card-labra` `card-detalle` `sq-manos` | The work itself |
| Finished | `hero-encimera` `card-veta` `card-mueble` `pan-fachada` | What the client buys |
| Material | `sq-marmol` `sq-pizarra` | The swatch |

## The set

Every row carries its own **Licence** cell rather than leaning on the paragraph
above it. `RT_GALLERY_NO_MANIFEST` reads that cell per row, and it has to: a
blanket "all of these are free-licence" is true only until the fifteenth image
arrives from somewhere else, and it becomes false in silence — nothing about the
new row looks different from the old ones.

| Slug | Role | Size | Weight | Freepik | Licence | `alt` |
|---|---|---|---|---|---|---|
| `hero-taller` | hero 16:9 | 1440×810 | 117 KB | 50621482 | Freepik free | Cantero labrando un sillar a maceta y cincel en el taller |
| `hero-banco` | hero 16:9 | 1440×810 | 119 KB | 50621783 | Freepik free | Cantero puliendo una pieza de piedra entre el polvo del corte |
| `hero-cantera` | hero 16:9 | 1440×810 | 122 KB | 427777725 | Freepik free | Vista aérea de una cantera a cielo abierto en bancadas |
| `hero-encimera` | hero 16:9 | 1440×810 | 114 KB | 427392968 | Freepik free | Encimera de piedra natural con grifería negra en una cocina |
| `card-cantero` | card 4:3 | 800×600 | 45 KB | 50621544 | Freepik free | Cantero golpeando el cincel sobre un bloque de arenisca |
| `card-patio` | card 4:3 | 800×600 | 43 KB | 50621352 | Freepik free | Cantero trabajando una pieza en el patio del taller |
| `card-labra` | card 4:3 | 800×600 | 44 KB | 50621286 | Freepik free | Labra de un bloque con herramienta neumática |
| `card-detalle` | card 4:3 | 800×600 | 44 KB | 50621416 | Freepik free | Detalle del cincel abriendo la superficie de la piedra |
| `card-veta` | card 4:3 | 800×600 | 43 KB | 425368036 | Freepik free | Panel de piedra con veta dorada junto a un frente de madera |
| `card-mueble` | card 4:3 | 800×600 | 39 KB | 427487926 | Freepik free | Mueble con frente de piedra y cajones abiertos |
| `sq-manos` | square 1:1 | 600×600 | 38 KB | 50621643 | Freepik free | Manos de cantero puliendo el canto de una pieza |
| `sq-marmol` | square 1:1 | 600×600 | 75 KB | 1051443 | Freepik free | Superficie de mármol beige con veta natural |
| `sq-pizarra` | square 1:1 | 600×600 | 76 KB | 1033860 | Freepik free | Granito gris de grano fino en placa |
| `pan-fachada` | panoramic 21:9 | 1440×617 | 102 KB | 410749048 | Freepik free | Fachada barroca en piedra labrada de Lecce |

`alt` is Spanish because it lands on a Spanish site, and it describes what is in
the frame rather than repeating the section heading — an `alt` that restates the
`h2` is a screen-reader user hearing the same sentence twice.

## Weights are measured, not chosen

Each file is the highest WebP quality that still fits its role's budget, found by
binary search rather than a fixed quality, because these originals differ by
roughly 8× in detail: one quality setting would blow the budget on the busy frames
and waste it on the flat ones.

The two material swatches carry a larger budget than the portraits, and that is a
measurement, not a preference. A full-frame stone texture is fine grain edge to
edge — the worst case WebP has. At the portrait budget `sq-marmol` floors at
43.5 KB and `sq-pizarra` at 58.4 KB, so the cap was unreachable rather than merely
expensive. The cap was wrong, not the image.

## Adding one

1. `stock_search` with `license: free`, then `stock_download`.
2. Centre-crop to the role's ratio **before** scaling — scaling first squashes,
   cropping after throws away resolution already paid for.
3. Binary-search the quality against the role budget; do not pick a number.
4. Add its row here **with the slug**, or `RT_GALLERY_NO_MANIFEST` fails the build.
5. Look at it. A contact sheet catches the duplicate framing that a file listing
   cannot — this set lost one hero that way, after it was already converted.
