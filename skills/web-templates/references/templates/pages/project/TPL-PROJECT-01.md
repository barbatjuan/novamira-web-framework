# TPL-PROJECT-01 — Proyecto / caso

## 1. Identidad
El detalle de UN trabajo hecho: caso de estudio, obra, reforma, campaña. **Corporate**, y el par
natural de `TPL-C-03` Portfolio Showcase — ese grid enlaza a algún sitio, y ese sitio es esta
página. Hereda tokens de la home. Precios (si aparecen) en **€**.

Sin ella, un portfolio es una galería: se ve bonito y no demuestra nada. Lo que convence no es la
foto del resultado, es el problema que había antes.

## 2. Wireframe (top → bottom)
```
COMP-HEADER [fijo]
COMP-PAGE-HEAD [fijo] · H1 con el nombre del proyecto + cliente/sector + año
COMP-HERO-FULL [fijo] · la imagen principal del trabajo
COMP-PROJECT-FACTS [fijo] · cliente, sector, año, servicios prestados, duración
COMP-CASE-NARRATIVE [fijo · ADN] · reto → solución → resultado, en ese orden, siempre
COMP-GALLERY [toggle TGL-PROJECT-GALLERY]
COMP-TESTIMONIAL [toggle TGL-PROJECT-QUOTE] · del cliente de ESTE proyecto
COMP-STATS [toggle TGL-PROJECT-METRICS] · solo con números reales
COMP-CTA [fijo] · un servicio concreto, no "contacto"
COMP-RELATED [toggle TGL-PROJECT-RELATED] · 3 proyectos del mismo tipo
COMP-FOOTER [fijo]
```

## 3. Secciones
### Reto → solución → resultado `[fijo · ADN]`
Objetivo: demostrar criterio, no enseñar fotos. **El orden es el argumento**: sin el reto, la
solución no tiene mérito; sin el resultado, es una opinión. Si falta alguno de los tres, falta el
caso — y esa es una pregunta para el cliente, no un hueco que se rellena. Reutilizable: SECCIÓN.

### Ficha `[fijo]`
Cliente, sector, año, servicios, duración. Datos, no adjetivos. **Nombrar a un cliente exige su
permiso**: sin él, "empresa del sector X" y nada más. Reutilizable: SECCIÓN.

### Métricas `[toggle TGL-PROJECT-METRICS]`
Objetivo: prueba. Solo con **números reales y su fuente**. Un "+300%" inventado es exactamente el
hallazgo que `novamira-copywriter` tiene prohibido rellenar y devuelve como FACTS NEEDED.
Reutilizable: SECCIÓN.

### CTA `[fijo]`
A un servicio concreto: quien lee un caso de reforma de cocinas quiere reformas de cocina.
Reutilizable: SECCIÓN.

## 4. Toggles admitidos
| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-PROJECT-GALLERY` | on | apagar con menos de 3 imágenes propias |
| `TGL-PROJECT-QUOTE` | off | encender solo con testimonio real y autorizado |
| `TGL-PROJECT-METRICS` | off | encender solo con cifras verificables |
| `TGL-PROJECT-RELATED` | on | apagar con menos de 4 proyectos |

**Fijos:** HEADER, encabezado, imagen, ficha, reto/solución/resultado, CTA, FOOTER.

## 5. SEO / semántica
1 `H1` (el nombre del proyecto). Schema `CreativeWork` o `Article`, `image`, y `Review` **solo** si
el testimonio es real. Se replica el arquetipo, nunca el contenido: cinco proyectos con el mismo
texto cambiando la foto son cinco páginas casi duplicadas, y el buscador se queda con una.
`header` > `main` > `footer`.
