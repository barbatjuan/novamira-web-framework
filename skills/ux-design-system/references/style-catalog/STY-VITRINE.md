# `STY-VITRINE` — Vitrine

> Ported from `design-personalities.md`'s `PERS-VITRINE` (style-catalog PR 4a, `tasks.md` 4a.2).
> Format only — the 8 axis positions and every prose claim below are unchanged from the source; see
> `_README.md` for what a port means and what still lives in the old file until PR 4c. The source
> anchor's own prose is Spanish, so this port keeps it Spanish rather than translating it.

**Axes:** scale `editorial` · ground `ink` · density `monumental` · composition `strict-grid` ·
elevation `soft-shadow` · accent `metallic` · chassis `soft-carded` · ornament `none`

**Fits:** Lo que se compra mirándolo de cerca y en orden — galerías, joyería, catálogos de producto
caro, showrooms, obra fotografiada. La sala a oscuras y el objeto iluminado.

**Typography:** `--font-primary` **DM Sans** at 700; `--font-secondary` **Inter Tight**. Both SIL
OFL. Ninguna de las dos es la display de otra ancla, así que el emparejamiento es nuevo aunque las
familias ya estén embebidas. Display tracking `-0.02em` (h1–h3), wordmark `.12em`.

**Motion intensity:** el zoom en el tope documentado (`.7s`) y el lift de tarjeta destacada
(`-6px`) sobre `.5s`. Es el ancla más lenta en la imagen y la más rápida en el color (`.35s`): el
objeto se acerca despacio y la interfaz responde al instante. No se acerca al `-6px` + `scale(1.06)`
+ `.35s` que `motion.md` marca como barato — el zoom se queda en `1.045` y el lift no baja de `.5s`.

**Imagery:** el objeto aislado y **iluminado contra el fondo oscuro**, con aire alrededor. Nunca a
sangre: lo que define esta ancla es el margen negro que rodea la pieza, y una foto que llega hasta
el borde lo elimina.

**Card recipe:** superficie elevada — `--c-bg-alt` sobre `--c-bg` — y la sombra guardada para el
hover. **La elevación `soft-shadow` sobre un ground `ink` es casi invisible en reposo**, y esto no
es un defecto que se disimula sino la razón de la receta: sobre negro una sombra no separa, lo que
separa es el escalón de superficie. La sombra entra al levantar la tarjeta, donde sí tiene un borde
claro contra el que leerse.

## Precarga de toggles

Un estilo que no precarga nada no está terminado — es el mecanismo que cierra la causa raíz 6 de
la propuesta (39 toggles catalogados, uno solo movido de su default en 67 franjas). Cada ID de
abajo está en la lista transversal de `web-templates/references/toggles.md`, porque `STY-*` es
ortogonal a `TPL-*` y solo un toggle que todas las plantillas reconocen puede precargarse desde el
estilo por sí solo.

| Toggle | Precarga | Por qué |
|---|---|---|
| `TGL-IMAGERY` | `foto` | "el objeto aislado e iluminado" es una fotografía real, no una ilustración |
| `TGL-MOTION-INTENSITY` | `default` | la propia ancla se mide contra el suelo barato de `motion.md` y se queda dentro del rango documentado en ambos extremos |
| `TGL-HERO-TYPE` | `imagen fija` | un objeto iluminado, nunca un slider — un carrusel rompería la sala a oscuras |
| `TGL-CTA-STRENGTH` | `suave` | registro de showroom, sin venta agresiva |
| `TGL-CARD-STYLE` | `imagen grande` | la superficie elevada necesita aire alrededor de la pieza |
