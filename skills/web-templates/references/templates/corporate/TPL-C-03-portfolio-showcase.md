# TPL-C-03 — Portfolio / Showcase

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Portfolio / Showcase |
| Objetivo | Mostrar el trabajo: el proyecto/obra vende |
| Ideal para | Estudios creativos, arquitectura, fotografía, diseño, productoras, artistas |
| Ejemplos | Estudio de diseño, arquitecto, fotógrafo, agencia creativa, portfolio personal |
| Nivel de contenido | Bajo en texto, alto en visual |
| Protagonismo | El TRABAJO (proyectos/obras); la marca es la curaduría |
| ADN | Grid de proyectos protagonista, hero visual grande, mucho aire, mínimo texto. NO pricing, NO stats, NO forms largos. |

## 2. Wireframe (top → bottom)

```
COMP-HEADER (minimal) [fijo]
COMP-HERO (visual grande ~60vh / fullscreen) [fijo · ADN]
COMP-PORTFOLIO-GRID (proyectos) [fijo · ADN]
COMP-SERVICES (qué hacemos, breve) [toggle]
COMP-ABOUT (sobre el estudio) [toggle]
COMP-LOGOS (clientes) [toggle]
COMP-CTA (contacto directo) [fijo]
COMP-FOOTER (minimal) [fijo]
```

El trabajo manda. Ausencia intencional: sin pricing, sin stats, sin formularios largos, sin urgencia.

**El envoltorio de cada sección es parte del contrato.** `RT_TPL_TOO_SIMILAR` mide el INVENTARIO
—lo único que un documento sabía declarar— y no ve la FORMA. Medido en la galería: veintidós de las
veintitrés arquitecturas no dejaban que ningún elemento tocara el borde de la pantalla, así que dos
arquetipos con inventarios distintos seguían leyéndose como la misma página con otra paleta. Los
tres envoltorios (`contenido`, `banda`, `fila`) son vocabulario compartido; lo propio de cada
arquetipo es CUÁL pide cada sección.

| Sección | Envoltorio | Por qué |
|---------|-----------|---------|
| `COMP-PORTFOLIO-GRID` | **banda** | Este arquetipo existe para enseñar trabajo terminado: es lo único que hace y es su prueba entera. Seis obras dentro de la misma columna en la que va el FAQ decían «folleto» mientras el inventario decía «portfolio». La rejilla escapa de la medida de contenido; el encabezado y los pies de cada obra se quedan en su columna, porque el texto nunca sangra. |
| El resto | contenido | |

## 3. Secciones

### COMP-HEADER — minimal `[fijo]`
Objetivo: navegación discreta (navegación). Logo + nav corto (Trabajo, Estudio, Contacto). Mobile:
hamburguesa. Desktop: nav minimal, puede ir transparente sobre el hero. Reutilizable: GLOBAL.

### COMP-HERO — visual `[fijo · ADN] · TGL-HERO-TYPE, TGL-HERO-HEIGHT`
Objetivo: impacto visual inmediato (branding). Imagen/video grande, título mínimo, sin ruido.
Mobile: 50vh. Desktop: 60vh o fullscreen. H1 corto. `TGL-HERO-TYPE` permite slider de obras.
Reutilizable: SECCIÓN.

### COMP-PORTFOLIO-GRID — proyectos `[fijo · ADN] · TGL-CARD-STYLE`
Objetivo: mostrar el trabajo (descubrimiento + venta). Grid de proyectos con imagen grande +
título + categoría, hover con overlay. Mobile: 1 columna. Tablet: 2. Desktop: 2–3 (o masonry).
Card → página de proyecto. Reutilizable: GLOBAL (`COMP-PORTFOLIO-GRID`). Elementor: Loop/Portfolio
Grid. Divi: Portfolio / Filterable Portfolio.

### COMP-SERVICES — breve `[toggle TGL-PROCESS/services]`
Objetivo: contexto de qué ofrecen (descubrimiento). Lista corta de capacidades. Mobile: 1 columna.
Desktop: 2–3. Reutilizable: GLOBAL.

### COMP-ABOUT — sobre el estudio `[toggle]`
Objetivo: identidad/curaduría (branding). Texto breve + imagen. Reutilizable: SECCIÓN.

### COMP-LOGOS — clientes `[toggle TGL-LOGOS]`
Objetivo: credibilidad (confianza). Fila de logos. Reutilizable: GLOBAL.

### COMP-CTA — contacto `[fijo]`
Objetivo: iniciar conversación (conversión). Claim + email/botón "Trabajemos juntos". Sobrio.
Reutilizable: GLOBAL.

### COMP-FOOTER — minimal `[fijo]`
Objetivo: contacto + redes (Instagram/Behance importantes). Minimal. Reutilizable: GLOBAL.

## 4. Toggles admitidos

| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-HERO-TYPE` | imagen fija | slider de obras opcional |
| `TGL-HERO-HEIGHT` | 60vh | |
| `TGL-CARD-STYLE` | imagen grande | ADN visual |
| `TGL-LOGOS` | on | |
| `TGL-CTA-STRENGTH` | suave | |

**Fijos:** COMP-HEADER, COMP-HERO, COMP-PORTFOLIO-GRID, COMP-CTA, COMP-FOOTER.
**Ausencias de ADN:** pricing, stats, formularios largos, urgencia → sugerir TPL-C-01, TPL-C-02 o TPL-C-04.

## 5. SEO / semántica
1 `H1` (hero). `H2` en Trabajo, Estudio. Cada proyecto con su propia página + `alt` en imágenes.
Schema `CreativeWork`/`Organization`. `header` > `main` > `footer`.
