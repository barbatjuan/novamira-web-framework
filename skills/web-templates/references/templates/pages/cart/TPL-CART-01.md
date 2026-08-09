# TPL-CART-01 — Carrito

## 1. Identidad
Carrito de compra. Tiene superficie de diseño real (a diferencia del checkout). Dos vistas:
**side-cart** (drawer que abre desde el icono) y **página de carrito**. Ecommerce. Precios en **€**.
Lo construye la skill `woocommerce` con widgets nativos (mini-cart / cart); acá se define layout y orden.

## 2. Wireframe

### Side-cart (drawer) `[TGL-CART-VIEW = drawer/both]`
```
Drawer derecho, overlay oscuro
  ├─ Header: "Tu carrito (n)" + cerrar
  ├─ Line items compactos: mini-foto · nombre/variante · qty stepper · precio € · quitar
  ├─ (envío gratis progress bar) [toggle]
  ├─ Subtotal €
  └─ CTA: "Finalizar compra" (primary) + "Ver carrito" (link)
```

### Página de carrito `[TGL-CART-VIEW = page/both]`
```
COMP-HEADER [fijo] · COMP-BREADCRUMB
Line items (tabla/lista) [fijo · ADN]
   imagen · nombre/variante · qty stepper · precio unit € · subtotal línea · quitar
Resumen [fijo · ADN]
   subtotal €, cupón, envío estimado, TOTAL €, CTA "Finalizar compra"
COMP-PRODUCT-CAROUSEL (completá tu compra) [toggle TGL-CART-CROSSSELL]
Estado vacío [fijo] · mensaje + CTA "Ir a la tienda"
COMP-FOOTER [fijo]
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
