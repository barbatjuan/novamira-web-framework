# TPL-CHECKOUT-01 — Checkout (LAYOUT)

## 1. Identidad
Checkout. **Solo layout/estructura** — el checkout es 90% funcional (gateways, validación, cálculo de
envío, revisión de orden) y lo arma la skill `woocommerce` con el checkout nativo. Este arquetipo
define columnas, orden de campos y resumen; **NO** se maqueta como página "terminada" ni se le pone
lógica de pago. Ecommerce. Precios en **€**.

> Regla: no prometer un checkout "pixel-perfect" como si fuera funcional. Maquetar solo el esqueleto
> de layout y marcarlo como estructura; el comportamiento real es nativo de WooCommerce.

## 2. Wireframe
```
COMP-HEADER minimal [fijo · ADN] · logo (a home) + candado/"compra segura", SIN nav que distraiga
COMP-CHECKOUT-STEPS [toggle TGL-CHECKOUT-STEPS] · 1-step (todo en una) o 2-step (datos → pago)
COMP-CHECKOUT-FORM [fijo · ADN] · izquierda: Contacto · Envío (dirección) · Método de envío · Pago
COMP-ORDER-SUMMARY [fijo · ADN] · derecha, sticky: items, subtotal, envío, cupón, TOTAL €
COMP-TRUST-BADGES [toggle] · pago seguro, medios
COMP-CTA [fijo · ADN] · "Pagar"
COMP-FOOTER minimal [fijo]
```

## 3. Secciones
### COMP-HEADER minimal `[fijo · ADN]`
Objetivo: cero fricción/distracción (conversión). Solo logo (→ home) + señal de seguridad. Sin menú,
sin buscador. Reutilizable: variante minimal de `COMP-HEADER`.

### Form `[fijo · ADN] · TGL-CHECKOUT-STEPS`
Objetivo: completar la compra (conversión). Campos: contacto (email), envío (nombre, dirección,
ciudad, CP, país), método de envío, pago. Orden claro. Mobile: 1 columna, campos grandes, botón
"Pagar" **sticky abajo**. Desktop: columna izquierda ancha. `TGL-CHECKOUT-STEPS`: todo junto (1-step)
o acordeón datos→pago (2-step). **Funcional = WooCommerce**; acá solo el esqueleto de campos.

### COMP-ORDER-SUMMARY `[fijo · ADN] · TGL-ORDER-SUMMARY`
Objetivo: transparencia del total (confianza). Items (mini), subtotal €, envío, cupón, TOTAL €. Mobile:
**colapsable arriba** ("Ver resumen · €X"). Desktop: columna derecha **sticky**. Reutilizable: GLOBAL.

### COMP-TRUST-BADGES `[toggle TGL-TRUST]`
Pago seguro, medios aceptados, devolución. Reduce abandono. Reutilizable: GLOBAL.

### CTA "Pagar" `[fijo · ADN]`
Primary, claro, con el total. En mockup queda inerte (marcado "estructura"). Nativo: botón de pago Woo.

## 4. Toggles admitidos
| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-CHECKOUT-STEPS` | 1-step | o 2-step (datos → pago) |
| `TGL-ORDER-SUMMARY` | sticky (desktop) / colapsable (mobile) | |
| `TGL-CHECKOUT-HEADER` | minimal | ADN; full desaconsejado |
| `TGL-TRUST` | on | |

**Fijos:** header minimal, form, order summary, CTA pagar, footer minimal.
**Nota crítica:** todo el comportamiento (gateways, validación, envío, impuestos) es de `woocommerce`.
Este arquetipo es un contrato de **layout**, no de funcionalidad. No custom JS de pago.

## 5. SEO / semántica
Página `noindex`. Form accesible (labels, aria, errores claros). `header` > `main` > `footer`.
