# Dos carriles + puerta de promoción

`recommender.md` § Flujo bifurca en el paso 3b a uno de dos carriles. No hay un tercero: toda
recomendación de `web-templates` resuelve en **catálogo** o en **bespoke**.

## Carril catálogo

Un `TPL-*` existente encaja con el brief. CAPA 2 recomienda el id, CAPA 3 corre solo los toggles
que esa plantilla admite (precargados desde el intake), y el archivo del arquetipo resuelve el
inventario de secciones. Es el camino normal: se ofrece siempre primero, y solo se abandona cuando
**ningún** `TPL-*` del set resuelto (ecommerce o corporate) encaja — nunca por preferencia, nunca
antes de agotar el catálogo.

## Carril bespoke

Ningún `TPL-*` encaja. Es la vía de escape, no el camino principal (decisión D2): se llega aquí
solo tras revisar el set completo y registrar el razonamiento negativo (por qué cada candidato no
sirve), nunca como salida rápida ante un catálogo que no se miró entero.

Bespoke **salta CAPA 3**: no hay plantilla cuyos toggles admitir, así que las secciones se declaran
inline, con sus propios toggles ad-hoc por sección (no del catálogo de `toggles.md`). El resultado
bespoke **no es un `TPL-*`** y nunca entra al catálogo del repo: no se guarda bajo
`references/templates/`, no aparece en `recommender.md` ni en ningún `_README.md`, y por eso
`RT_TPL_UNROUTABLE` no lo alcanza — esa fila vigila los `TPL-*.md` que sí viven en el repo, no un
diseño de un solo proyecto.

## Contrato de salida (ambos carriles)

Catálogo o bespoke, `web-templates` siempre entrega lo mismo a `ux-design-system` → `html-mockup`:
una **arquitectura resuelta** — secciones nombradas en orden, cada una con su id `COMP-*` y su
Envoltorio declarado (`bleed` | `row` | `contained`, ver `catalog-wrapper-integrity`), más el
wireframe. Ningún dato visual o de builder. Ninguna de las dos skills posteriores pregunta de qué
carril vino el input: la interfaz es una sola (`template-lane-contract` § Lane Transparency
Downstream).

## Puerta de promoción (estricta)

Un resultado bespoke **puede** promoverse a `TPL-*` nuevo o existente, pero solo si pasa la misma
auditoría que cualquier arquetipo nativo — nada de excepción por ser reciente:

| Fila | Qué exige |
|---|---|
| `RT_TPL_NO_WIREFRAME` | wireframe completo, cada fila con id `COMP-*` |
| `RT_TPL_UNROUTABLE` | recomendable de verdad: nombrado en `recommender.md` y en el `_README.md` de su familia |
| `RT_TPL_TOO_SIMILAR` | no comparte más de la mitad del inventario con un `TPL-*` de su misma familia |
| `RT_TPL_NO_ENVOLTORIO` | tabla `Envoltorio` completa (`catalog-wrapper-integrity`) |
| `RT_TPL_WRAPPER_DUPLICATE` | firma de envoltorio (secuencia de shapes normalizados) distinta a la de cualquier archetype de su familia |

Fallar una sola de las cinco impide la promoción. No hay `size:exception` para esto: es la
decisión D2 — bespoke es escape hatch, no la vía para ampliar el catálogo sin pasar el gate.
