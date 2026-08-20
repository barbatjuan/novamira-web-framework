# TPL-PDP-05 — Ficha a medida / presupuesto

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Ficha a medida / presupuesto |
| Objetivo | Que el cliente configure lo que necesita y pida presupuesto de algo que todavía no existe |
| Ecommerce ideal | Cortinas y estores, mobiliario a medida, encimeras, impresión y rotulación, mamparas, tapicería |
| Ejemplos | Estor enrollable por centímetros, armario a medida, encimera de cocina, vinilo para escaparate |
| Home que la acompaña | `TPL-E-09 A medida / Presupuesto` |
| ADN | El configurador ES la página. Hay muestra física, plazo por fases y formulario de PRESUPUESTO. **No hay carrito y no hay precio.** |

**Por qué existe, y por qué es la única ficha sin add-to-cart ni precio.** Las cuatro hermanas
comparten un supuesto que aquí se rompe: **existe una referencia con un precio que se puede meter
en un carrito**. Un estor de 137 × 214 cm en lino crudo con cadena a la izquierda no es una
referencia; es una combinación que nadie ha fabricado todavía, y su precio no se sabe hasta
configurarla.

Forzar esto en `TPL-PDP-01` produce el error clásico del sector: un precio "desde" que no se
parece al final, un carrito que acepta la compra y un correo posterior pidiendo medidas. Ese correo
es donde se cae la venta, y encima ya se cobró.

Así que el embudo termina en **presupuesto**, no en pago, y eso cambia tres cosas de raíz:

1. **El configurador es la página, no una pestaña.** `COMP-CONFIGURATOR` ocupa el sitio que en
   `TPL-PDP-01` ocupan galería y bloque de compra juntos, y `COMP-MEASURE-TABLE` está aquí para
   explicar **cómo se mide** —de dónde a dónde, con hueco o sin él— porque una medida mal tomada
   es una pieza mal fabricada que nadie puede devolver.
2. **La muestra física es una sección propia.** Un color de tejido en pantalla no es el color del
   tejido, y de algo fabricado a medida no hay devolución. La muestra es lo que sustituye a la
   política de devoluciones que esta ficha no puede ofrecer.
3. **El plazo va por fases.** Medición, fabricación, instalación, con días en cada una. "3 a 4
   semanas" sin desglosar no es un plazo: es una excusa por adelantado.

## 2. Wireframe (top → bottom)
```
COMP-HEADER [fijo] · sin carrito; CTA "Pedir presupuesto"
COMP-BREADCRUMB [fijo]
COMP-CONFIGURATOR [fijo · ADN] · medidas, material, acabado, herrajes — ocupa el sitio de la compra
COMP-MEASURE-TABLE [fijo · ADN] · cómo se mide: de dónde a dónde, con hueco o sin él
COMP-GALLERY [fijo] · trabajos entregados, cada uno con su medida escrita
COMP-SAMPLE-REQUEST [fijo · ADN] · muestra física antes de decidir
COMP-LEAD-TIME [fijo · ADN] · plazo por fases, con días: medición · fabricación · instalación
COMP-QUOTE-FORM [fijo · ADN] · presupuesto, no compra
COMP-FOOTER [fijo]
```

## 3. Secciones
### COMP-CONFIGURATOR `[fijo · ADN]`
Convertir una necesidad en una configuración concreta. Medidas en cm con mínimos y máximos reales,
material, acabado, herrajes, lado de accionamiento. Cada paso muestra lo que lleva elegido.
**Sin precio total hasta que el presupuesto se emite** — una cifra provisional que luego cambia
hace más daño que no dar ninguna. Mobile: un paso por pantalla con resumen pegado abajo. Desktop:
formulario a un lado, resumen al otro. Reutilizable: GLOBAL (lo comparte `TPL-E-09`).

### COMP-MEASURE-TABLE `[fijo · ADN]`
Aquí no es una tabla de tallas: es la instrucción de medición, con dibujo y los dos o tres casos
que se equivocan siempre. Reutilizable: GLOBAL, con este uso documentado aparte del de `TPL-PDP-02`.

### COMP-SAMPLE-REQUEST `[fijo · ADN]`
Pedir muestra física: cuántas, si son gratis, cuánto tardan. Formulario corto y propio, separado
del de presupuesto — quien pide muestra todavía no pide precio. Reutilizable: GLOBAL.

### COMP-LEAD-TIME `[fijo · ADN]`
Fases con días: medición, fabricación, instalación. Con la fecha estimada de la primera fase si se
puede calcular. Reutilizable: GLOBAL.

### COMP-QUOTE-FORM `[fijo · ADN]`
El cierre. Arrastra la configuración ya hecha —no se vuelve a pedir— más contacto, código postal y
cuándo le viene bien la medición. Reutilizable: GLOBAL.

## 4. Toggles admitidos
| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-SAMPLE` | on | apagar sólo si no se envían muestras |
| `TGL-INSTALL` | on | incluir la fase de instalación en el plazo |
| `TGL-GALLERY` | on | trabajos entregados con su medida |

**Fijos:** HEADER, BREADCRUMB, CONFIGURATOR, MEASURE-TABLE, GALLERY, SAMPLE-REQUEST, LEAD-TIME,
QUOTE-FORM, FOOTER.
**Ausencias de ADN:** `COMP-PRODUCT-INFO`, add-to-cart, precio publicado, carrusel de relacionados,
acordeón de devoluciones — de lo fabricado a medida no hay devolución, y prometerla es peor que
callarla.

## 5. SEO / semántica
1 `H1` (el producto configurable). `H2` en Cómo medir, Muestra, Plazo, Presupuesto. Schema
`Product` **sin `Offer` con precio**: la oferta no existe hasta emitir el presupuesto, y publicar
un `price` inventado para "salir en el comparador" es exactamente el error que este arquetipo
evita. `Service` + `areaServed` si hay medición e instalación a domicilio. `header` > `main` >
`footer`.
