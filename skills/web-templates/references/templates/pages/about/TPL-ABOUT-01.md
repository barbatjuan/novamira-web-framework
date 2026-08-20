# TPL-ABOUT-01 — Nosotros / La empresa

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Nosotros / La empresa |
| Pregunta del visitante | "¿Sois sólidos? ¿Puedo confiaros esto?" |
| Sirve para | Ecommerce y corporate con estructura de empresa: varias personas, varios años, clientes que se pueden nombrar |
| Ejemplos | Asesoría, distribuidora, agencia, clínica con plantilla, fabricante |
| ADN | Historia y misión, los compromisos, las cifras, las caras y la prueba. Se responde con TRAYECTORIA. |

**Es la de por defecto, y por eso hay que decir cuándo NO.** Funciona cuando lo que da confianza es
la organización: que existe hace años, que somos varios, que hay clientes que lo dicen. Si lo que da
confianza es el **oficio** —cómo se hace, con qué, quién lo hace con las manos— la página es
`TPL-ABOUT-02` y esta sale sobrando. Si lo que da confianza es el **sitio** —la sala, la puerta, el
horario— es `TPL-ABOUT-03`.

Un aviso que vale más que la plantilla: **una cifra inventada hunde la página entera.** "Más de 500
clientes satisfechos" sin nada detrás es la frase que más se repite en este sector y la que menos se
cree. Si no hay números reales, `TGL-ABOUT-STATS` se apaga y no pasa nada; lo que no se puede es
rellenarlo. Lo mismo con el equipo: retratos de banco de imágenes en la sección de equipo es la
mentira más fácil de pillar que existe en una web.

## 2. Wireframe (top → bottom)
```
COMP-HEADER [fijo]
COMP-HERO [fijo] · imagen fija + claim, ~40vh (35vh en mobile)
COMP-ABOUT [fijo · ADN] · historia y misión: el porqué, con hitos si los hay
COMP-VALUES [fijo · ADN] · 3–4 compromisos, no adjetivos
COMP-STATS [toggle TGL-ABOUT-STATS] · años, clientes, envíos — sólo con números reales
COMP-TEAM [toggle TGL-ABOUT-TEAM] · caras reales o la sección no va
COMP-TESTIMONIAL [toggle TGL-TESTIMONIALS]
COMP-CTA [fijo] · contacto o ir a la tienda
COMP-FOOTER [fijo]
```

## 3. Secciones
### COMP-HERO `[fijo]`
Imagen fija + claim ("Quiénes somos"). Mobile: 35vh. Desktop: 40vh. H1 único. Reutilizable: SECCIÓN.

### COMP-ABOUT `[fijo · ADN]`
Contar el porqué: branding + confianza. Texto + imagen, con línea de hitos si la trayectoria da para
ella. Mobile: 1 columna. Desktop: 2 columnas o timeline. Reutilizable: SECCIÓN.

### COMP-VALUES `[fijo · ADN]`
Propuesta de valor. 3–4 pilares: icono + título + una línea. **Compromisos verificables, no
adjetivos** — "respondemos en 24 h laborables" vale, "excelencia" no. Mobile: 1–2 columnas. Desktop:
fila. Reutilizable: GLOBAL (reutiliza `COMP-BENEFITS`).

### COMP-STATS `[toggle TGL-ABOUT-STATS]`
Cifras. Mobile: 2×2. Desktop: fila de 4, contador suave. Reutilizable: GLOBAL.

### COMP-TEAM `[toggle TGL-ABOUT-TEAM]`
Foto + nombre + rol. Mobile: 2 columnas. Desktop: 3–4. Reutilizable: GLOBAL.

### COMP-TESTIMONIAL `[toggle]` · COMP-CTA `[fijo]`
Prueba social y cierre. Reutilizables: GLOBAL.

## 4. Toggles admitidos
| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-ABOUT-STATS` | on | apagar si no hay números reales — ver el aviso de § 1 |
| `TGL-ABOUT-TEAM` | on | apagar si no hay equipo público o no hay fotos reales |
| `TGL-TESTIMONIALS` | off | |

**Fijos:** HEADER, HERO, ABOUT, VALUES, CTA, FOOTER.
**Ausencias de ADN:** proceso de fabricación, galería del local, horario — son `TPL-ABOUT-02` y
`TPL-ABOUT-03`.

## 5. SEO / semántica
1 `H1` (Nosotros). `H2` en Historia, Compromisos, Equipo. Schema `Organization` (+ `AboutPage`).
`header` > `main` > `footer`.
