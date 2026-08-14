# TPL-ABOUT-01 — About / Nosotros

## 1. Identidad
Página institucional de marca/empresa: historia, valores, equipo, prueba. Sirve para **ecommerce y
corporate**. Hereda tokens y tono de la home. Precios (si aparecen) en **€**.

## 2. Wireframe (top → bottom)
```
COMP-HEADER [fijo]
COMP-HERO (imagen fija + claim ~40vh) [fijo]
Historia / Misión [fijo · ADN] · texto + imagen
COMP-VALUES (pilares/valores) [fijo]
COMP-STATS (cifras) [toggle TGL-ABOUT-STATS]
COMP-TEAM (equipo) [toggle TGL-ABOUT-TEAM]
COMP-TESTIMONIAL [toggle]
COMP-CTA (contacto / ir a la tienda) [fijo]
COMP-FOOTER [fijo]
```

## 3. Secciones
### COMP-HERO `[fijo]`
Imagen fija + claim ("Quiénes somos"). Mobile: 35vh. Desktop: 40vh. H1 único. Reutilizable: SECCIÓN.

### Historia / Misión `[fijo · ADN]`
Objetivo: contar el porqué (branding + confianza). Texto + imagen, puede incluir hitos/timeline.
Mobile: 1 columna. Desktop: 2 columnas o timeline. Reutilizable: SECCIÓN.

### COMP-VALUES `[fijo]`
Objetivo: propuesta de valor (confianza). 3–4 pilares ícono + título + línea. Mobile: 1–2 col.
Desktop: fila. Reutilizable: GLOBAL (`COMP-VALUES` / reutiliza `COMP-BENEFITS`).

### COMP-STATS `[toggle TGL-ABOUT-STATS]`
Cifras (años, clientes, envíos). Mobile: 2×2. Desktop: fila de 4, contador suave. Reutilizable: GLOBAL.

### COMP-TEAM `[toggle TGL-ABOUT-TEAM]`
Cards foto + nombre + rol. Mobile: 2 col. Desktop: 3–4. Reutilizable: GLOBAL (`COMP-TEAM`).

### COMP-TESTIMONIAL `[toggle]` · COMP-CTA `[fijo]`
Social proof + cierre (ir a tienda / contacto). Reutilizables: GLOBAL.

## 4. Toggles admitidos
| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-ABOUT-STATS` | on | |
| `TGL-ABOUT-TEAM` | on | apagar si no hay equipo público |
| `TGL-TESTIMONIALS` | off | opcional |

**Fijos:** HEADER, HERO, Historia, VALUES, CTA, FOOTER.

## 5. SEO / semántica
1 `H1` (Nosotros). `H2` en Historia, Valores, Equipo. Schema `Organization` (+`AboutPage`).
`header` > `main` > `footer`.
