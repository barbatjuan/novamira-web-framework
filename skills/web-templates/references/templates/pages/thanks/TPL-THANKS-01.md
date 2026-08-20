# TPL-THANKS-01 — Gracias

## 1. Identidad
Página a la que cae quien acaba de enviar un formulario o completar un pedido. Sirve para
**ecommerce y corporate**. Hereda tokens de la home. Precios (si aparecen) en **€**.

Existe por dos motivos, y el segundo es el que se olvida: confirmar al humano que su mensaje salió,
y darle a la analítica una URL propia que marcar como conversión. Un formulario que responde con un
mensaje en la misma página no se puede medir sin JavaScript extra, y lo que no se mide no se
optimiza.

## 2. Wireframe (top → bottom)
```
COMP-HEADER [fijo]
COMP-CONFIRMATION [fijo · ADN] · H1 "Gracias" + qué pasa ahora + en cuánto tiempo
COMP-CTA [fijo] · 1 salida de vuelta al sitio (nunca un callejón sin salida)
COMP-CONTACT-DIRECT [toggle TGL-THANKS-DIRECT] · teléfono/WhatsApp para los que no quieren esperar
COMP-RELATED [toggle TGL-THANKS-RELATED] · productos o servicios relacionados
COMP-FOOTER [fijo]
```

## 3. Secciones
### Confirmación `[fijo · ADN]`
Objetivo: cerrar el bucle. H1 "Gracias" + **qué pasa ahora y cuándo** — "te respondemos en menos de
24 h laborables" vale mucho más que "nos pondremos en contacto". Si el envío pudo fallar, esta
página NO es el sitio donde enterarse: eso lo cubre `wordpress-forms`, que prueba la entrega real.
Reutilizable: SECCIÓN.

### Siguiente paso `[fijo]`
Objetivo: no dejar al visitante en una hoja en blanco. Un CTA, uno solo, de vuelta a la home o al
catálogo. Reutilizable: SECCIÓN.

### COMP-CONTACT-DIRECT `[toggle TGL-THANKS-DIRECT]`
Objetivo: dar salida a quien tiene prisa. Teléfono tap-to-call + WhatsApp. Reutilizable: GLOBAL.

### COMP-RELATED `[toggle TGL-THANKS-RELATED]`
Objetivo: continuar la sesión. Grid corto. Reutilizable: GLOBAL (`COMP-RELATED`).

## 4. Toggles admitidos
| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-THANKS-DIRECT` | on | apagar si no hay atención telefónica |
| `TGL-THANKS-RELATED` | off | encender en ecommerce |

**Fijos:** HEADER, confirmación, siguiente paso, FOOTER.

## 5. SEO / semántica
1 `H1` (Gracias). **`noindex`** — esta página no debe aparecer en resultados: indexarla manda gente
a una confirmación de algo que nunca enviaron. Es la excepción a la regla de indexabilidad de la
fila 26 de `qa-review`, y hay que declararla como excepción, no dejarla ambigua. Sin schema.
`header` > `main` > `footer`.
