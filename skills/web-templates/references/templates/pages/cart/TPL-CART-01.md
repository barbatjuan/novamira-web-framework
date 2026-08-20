# TPL-CART-01 — Carrito

## 1. Identidad
Carrito de compra. Tiene superficie de diseño real (a diferencia del checkout). Dos vistas:
**side-cart** (drawer que abre desde el icono) y **página de carrito**. Ecommerce. Precios en **€**.
Lo construye la skill `woocommerce` con widgets nativos (mini-cart / cart); acá se define layout y orden.

## 2. Wireframe

UN SOLO INVENTARIO, DOS VISTAS. El drawer y la página no son dos arquetipos: son la misma lista de
secciones servida en dos superficies, y quién manda es `TGL-CART-VIEW`. Iban en dos bloques
cercados y eso las rompía en dos sitios — el inventario se lee del primero, que era un dibujo del
cajón sin un solo id, así que este arquetipo declaraba una arquitectura vacía mientras su página
real quedaba fuera de toda comparación.

```
COMP-HEADER [fijo · vista página] · COMP-BREADCRUMB
COMP-CART-DRAWER [vista drawer · ADN] · panel derecho sobre overlay, cierra con Esc y con clic fuera
COMP-CART-LINES [fijo · ADN] · imagen · nombre/variante · stepper · precio unit € · subtotal · quitar
COMP-SHIPPING-BAR [toggle TGL-CART-SHIPBAR] · cuánto falta para el envío gratis
COMP-CART-TOTALS [fijo · ADN] · subtotal €, cupón, envío estimado, TOTAL €
COMP-CTA [fijo] · "Finalizar compra" (primary) + "Ver carrito" (enlace, sólo en drawer)
COMP-PRODUCT-CAROUSEL (completá tu compra) [toggle TGL-CART-CROSSSELL · sólo vista página]
COMP-EMPTY-STATE [fijo] · mensaje + CTA "Ir a la tienda"
COMP-FOOTER [fijo · vista página]
```

## 3. Secciones
### Line items `[fijo · ADN]`
Objetivo: revisar/editar la compra (conversión). Cada línea: imagen, nombre + variante (talle/color),
**qty stepper** (–/+), precio €, subtotal de línea, quitar. Mobile: card apilada (foto arriba/izq,
datos, stepper). Desktop: fila tipo tabla. Reutilizable: GLOBAL. `woocommerce`: Cart widget nativo.

### Resumen `[fijo · ADN] · TGL-ORDER-SUMMARY`
Objetivo: cerrar hacia checkout (conversión). Subtotal €, campo cupón, envío estimado, TOTAL, CTA
**"Finalizar compra"** (primary, va a checkout). Mobile: al final o **sticky abajo**. Desktop: columna
derecha, puede ser sticky. Reutilizable: GLOBAL.

### Cross-sell `[toggle TGL-CART-CROSSSELL]`
Objetivo: subir ticket (venta). "Completá tu compra" 4 cards. Reutilizable: GLOBAL (`COMP-PRODUCT-CAROUSEL`).

### Estado vacío `[fijo]`
Objetivo: recuperar (navegación). Mensaje + CTA "Ir a la tienda". No dejar un carrito vacío pelado.
Reutilizable: SECCIÓN.

## 4. Toggles admitidos
| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-CART-VIEW` | both | side-cart / page / both |
| `TGL-CART-CROSSSELL` | on | completá tu compra |
| `TGL-ORDER-SUMMARY` | sticky | resumen pegado |
| `TGL-CART-SHIPBAR` | off | barra "te falta €X para envío gratis" |

**Fijos:** line items, resumen, estado vacío.
**Nota:** el side-cart (drawer) suele ser widget específico del builder/tema — validar en `project-context`;
si no, degradar a página de carrito.

## 5. SEO / semántica
Página `noindex` (carrito no se indexa). Form/stepper accesibles (labels, aria). `header` > `main` > `footer`.
