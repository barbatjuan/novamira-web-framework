# TPL-LEGAL-01 — Páginas legales

## 1. Identidad
Un solo arquetipo para las cuatro: aviso legal, privacidad, cookies y condiciones. Sirve para
**ecommerce y corporate**. Hereda tokens de la home.

El contenido lo escribe `wordpress-legal` con los datos fiscales REALES del cliente. Este arquetipo
solo dice cómo se lee un texto largo; no inventa ni una línea de él, y una página legal a la que le
falta un dato **no se publica** — una que nombra a la entidad equivocada parece hecha, y por eso
nadie la vuelve a leer.

## 2. Wireframe (top → bottom)
```
COMP-HEADER [fijo]
Encabezado [fijo] · H1 con el nombre exacto del documento + fecha de última actualización
Índice [toggle TGL-LEGAL-TOC] · ancla a cada H2, útil a partir de ~6 secciones
Cuerpo [fijo · ADN] · texto largo, UNA columna, medida de lectura limitada
Contacto del responsable [fijo] · a quién se escribe para ejercer derechos
COMP-FOOTER [fijo]
```

## 3. Secciones
### Encabezado `[fijo]`
H1 con el nombre del documento tal cual ("Política de privacidad", no "Privacidad") + **fecha de
última actualización**, que es lo que convierte el documento en verificable. Reutilizable: SECCIÓN.

### Índice `[toggle TGL-LEGAL-TOC]`
Anclas a los H2. Reutilizable: SECCIÓN.

### Cuerpo `[fijo · ADN]`
Objetivo: que se pueda leer de verdad. **Una columna**, medida ~65-75 caracteres, jerarquía H2/H3
real, listas donde el texto enumera. Nada de dos columnas ni de acordeones que escondan cláusulas:
un texto legal plegado por defecto es un texto que no se mostró. Reutilizable: SECCIÓN.

### Contacto del responsable `[fijo]`
Dirección de contacto para derechos de acceso, rectificación y supresión. Con los datos reales o no
se publica. Reutilizable: SECCIÓN.

## 4. Toggles admitidos
| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-LEGAL-TOC` | on | apagar en documentos cortos |

**Fijos:** HEADER, encabezado, cuerpo, contacto, FOOTER.

## 5. SEO / semántica
1 `H1` por documento, y **títulos distintos entre las cuatro páginas** — es el sitio donde más fácil
se duplica un `<title>`. Indexables (no llevan `noindex`): son parte de la identidad pública del
negocio. **Enlazadas desde el pie de TODAS las páginas**, que es lo que comprueba la fila 20 de
`qa-review` — una página legal a la que no se llega es un fichero, no cumplimiento. Sin schema.
`header` > `main` > `footer`.
