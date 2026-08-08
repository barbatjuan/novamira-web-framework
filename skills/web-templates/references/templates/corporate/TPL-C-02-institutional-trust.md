# TPL-C-02 — Institutional / Trust

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Institutional / Trust |
| Objetivo | Transmitir autoridad y confianza institucional |
| Ideal para | Empresas establecidas, salud, legal, financiero, industria, educación |
| Ejemplos | Clínica, estudio jurídico, banco/fintech, universidad, corporación industrial |
| Nivel de contenido | Medio–alto (institucional, credenciales) |
| Protagonismo | La INSTITUCIÓN: trayectoria, cifras, credenciales |
| ADN | Sobrio, autoridad, stats/números y credenciales protagonistas. CTA calmo, NO urgencia, NO agresivo. |

## 2. Wireframe (top → bottom)

```
COMP-HEADER (sobrio) [fijo]
COMP-HERO (imagen fija + claim institucional ~45vh) [fijo · ADN]
COMP-ABOUT (quiénes somos / misión) [fijo]
COMP-STATS (cifras: años, clientes, proyectos) [fijo · ADN]
COMP-SERVICES (áreas / especialidades) [fijo]
COMP-CREDENTIALS (certificaciones / logos / prensa) [toggle]
COMP-TEAM (equipo / directivos) [toggle]
COMP-TESTIMONIAL [toggle]
COMP-CTA (contacto sobrio) [fijo]
COMP-FOOTER [fijo]
```

Ritmo sobrio, mucha credibilidad. Ausencia intencional: sin countdown, sin promo, sin CTA agresivo.

## 3. Secciones

### COMP-HEADER `[fijo]`
Objetivo: navegación institucional (navegación). Logo, nav completo (áreas, nosotros, contacto),
teléfono. Mobile: hamburguesa + teléfono. Desktop: nav amplio, look sobrio. Sticky. Reutilizable: GLOBAL.

### COMP-HERO — imagen fija `[fijo · ADN] · TGL-HERO-TYPE fija, TGL-HERO-HEIGHT`
Objetivo: transmitir solidez (branding + confianza). Imagen fija sobria + claim institucional +
CTA calmo ("Conocenos" / "Contactar"). Mobile: 40vh. Desktop: 45vh. H1 único. Reutilizable: SECCIÓN.

### COMP-ABOUT — quiénes somos `[fijo]`
Objetivo: contexto y misión (confianza). Texto institucional + imagen. Mobile: 1 columna. Desktop:
2 columnas. Reutilizable: SECCIÓN.

### COMP-STATS — cifras `[fijo · ADN] · TGL-STATS`
Objetivo: prueba de trayectoria (confianza). 3–4 números grandes (años, clientes, proyectos, país).
Mobile: 2×2. Desktop: fila de 4, con contador animado suave. Reutilizable: GLOBAL (`COMP-STATS`).
Elementor: Counter widget. Divi: Number Counter.

### COMP-SERVICES — áreas `[fijo]`
Objetivo: mostrar especialidades (descubrimiento). 3–6 áreas ícono + título + descripción.
Mobile: 1. Desktop: 3. Reutilizable: GLOBAL.

### COMP-CREDENTIALS `[toggle TGL-LOGOS]`
Objetivo: autoridad (confianza). Certificaciones, premios, logos de prensa/partners. Fila en gris.
Reutilizable: GLOBAL.

### COMP-TEAM — equipo `[toggle TGL-TEAM]`
Objetivo: cara humana, credibilidad (confianza). Cards foto + nombre + cargo. Mobile: 2 columnas.
Desktop: 3–4. Reutilizable: GLOBAL (`COMP-TEAM`).

### COMP-TESTIMONIAL `[toggle TGL-TESTIMONIALS]`
Objetivo: social proof institucional. Reutilizable: GLOBAL.

### COMP-CTA — contacto sobrio `[fijo]`
Objetivo: canal de contacto (conversión calma). Banda con claim + botón "Contactar" o teléfono.
No formulario agresivo. Reutilizable: GLOBAL.

### COMP-FOOTER `[fijo]`
Objetivo: contacto, áreas, legal, ubicación. Reutilizable: GLOBAL.

## 4. Toggles admitidos

| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-HERO-TYPE` | imagen fija | slider desaconsejado |
| `TGL-HERO-HEIGHT` | 45vh | |
| `TGL-STATS` | on | ADN |
| `TGL-LOGOS` | on | credenciales |
| `TGL-TEAM` | on | |
| `TGL-TESTIMONIALS` | on | |
| `TGL-STYLE` | corporate / sobrio | |
| `TGL-CTA-STRENGTH` | suave | ADN |

**Fijos:** COMP-HEADER, COMP-HERO, COMP-ABOUT, COMP-STATS, COMP-SERVICES, COMP-CTA, COMP-FOOTER.
**Ausencias de ADN:** urgencia, promo, formulario agresivo en hero → sugerir TPL-C-01 o TPL-C-04.

## 5. SEO / semántica
1 `H1` (hero). `H2` en Nosotros, Áreas, Equipo. Schema `Organization` + `LocalBusiness` si aplica.
`header` > `main` > `footer`.
