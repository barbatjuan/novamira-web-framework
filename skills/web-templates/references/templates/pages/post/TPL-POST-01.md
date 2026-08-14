# TPL-POST-01 — Entrada del blog

## 1. Identidad
El artículo. Sirve para **ecommerce y corporate**. Hereda tokens de la home. Precios (si aparecen)
en **€**.

Es, casi siempre, la página por la que entra alguien que no conocía el sitio. Todo lo que la home
da por sabido —quién es este negocio, qué vende, por qué creerle— aquí hay que darlo otra vez.

## 2. Wireframe (top → bottom)
```
COMP-HEADER [fijo]
Encabezado [fijo] · H1 + fecha + autor + tiempo de lectura
Imagen destacada [toggle TGL-POST-HERO]
Cuerpo [fijo · ADN] · UNA columna, medida de lectura limitada
Índice lateral [toggle TGL-POST-TOC] · solo en artículos largos y solo en desktop
CTA contextual [fijo] · qué hacer ahora, ligado al tema del artículo
Autor [toggle TGL-POST-AUTHOR]
COMP-RELATED [toggle TGL-POST-RELATED] · 3 entradas del mismo tema
COMP-FOOTER [fijo]
```

## 3. Secciones
### Cuerpo `[fijo · ADN]`
Objetivo: que se lea. Una columna, medida ~65-75 caracteres, H2/H3 con jerarquía real (no saltar de
H2 a H4), imágenes con `alt` escrito para quien no las ve. Nada de anuncios ni bloques intercalados
que rompan la lectura. Reutilizable: SECCIÓN.

### CTA contextual `[fijo]`
Objetivo: convertir la visita en algo. **Ligado al tema**, no un banner genérico: quien lee sobre un
servicio quiere ese servicio, no la home. Un CTA, uno solo. Reutilizable: SECCIÓN.

### Encabezado / Imagen / Índice / Autor / Relacionadas `[fijo + toggles]`
Fecha visible siempre. El índice lateral solo con más de ~6 H2, y nunca en mobile, donde ocupa la
pantalla entera antes del primer párrafo. Reutilizable: GLOBAL (`COMP-RELATED`), SECCIÓN el resto.

## 4. Toggles admitidos
| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-POST-HERO` | on | apagar si no hay imagen real; una de stock genérica resta |
| `TGL-POST-TOC` | off | encender en artículos largos, desktop |
| `TGL-POST-AUTHOR` | off | encender si el autor aporta autoridad |
| `TGL-POST-RELATED` | on | apagar con menos de 4 entradas |

**Fijos:** HEADER, encabezado, cuerpo, CTA, FOOTER.

## 5. SEO / semántica
1 `H1` (el título del artículo, **nunca** el nombre del sitio — es el error que convierte todas las
entradas en la misma página a ojos de un buscador). `article` con `datePublished` y
`dateModified`. Schema `Article`/`BlogPosting` + `author`. Meta description propia por entrada.
Imágenes con `alt` real. `header` > `main` > `footer`.
