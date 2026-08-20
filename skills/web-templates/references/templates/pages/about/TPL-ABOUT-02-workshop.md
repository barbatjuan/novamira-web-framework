# TPL-ABOUT-02 — Nosotros / El oficio

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Nosotros / El oficio |
| Pregunta del visitante | "¿Sabéis hacer esto de verdad?" |
| Sirve para | Corporate y ecommerce donde lo que se compra es una ejecución, no una marca |
| Ejemplos | Taller mecánico, cantería, carpintería, tapicería, imprenta, reformas, laboratorio dental |
| ADN | El proceso paso a paso con fotografía del taller, los trabajos hechos, y quién firma el trabajo con nombre. Se responde con OFICIO, no con trayectoria. |

**Por qué existe habiendo `TPL-ABOUT-01`.** Aquella responde "¿sois sólidos?" con años, cifras y una
rejilla de retratos. Un taller no gana esa conversación: puede llevar tres años y ser el mejor de la
provincia, o cuarenta y hacerlo mal. Lo que convence de un oficio es **ver cómo se hace y ver lo que
ha salido**, y ninguna de las dos cosas cabe en una sección de valores.

Por eso aquí no hay rejilla de equipo. **No es un olvido: es la decisión del arquetipo.** Un taller
de cuatro personas con cuatro retratos de estudio parece una consultora pequeña; el mismo taller con
una fotografía de las manos trabajando y una frase firmada por quien las tiene, parece lo que es.
`COMP-FIGURE-QUOTE` da la cara —una o dos personas, con nombre y con lo que hacen— y hace el trabajo
que la rejilla hacía mal.

Tampoco hay cifras. La prueba es `COMP-CASES`: trabajos concretos, con lo que tenían de difícil.
"Doce años de experiencia" lo escribe cualquiera; "una encimera de 3,20 m en una sola pieza por una
escalera de caracol" no.

## 2. Wireframe (top → bottom)
```
COMP-HEADER [fijo]
COMP-HERO [fijo] · el taller trabajando, no el logo sobre fondo blanco
COMP-PROCESS [fijo · ADN] · el trabajo paso a paso, con foto de cada paso
COMP-GALLERY [fijo · ADN] · el taller y las herramientas: dónde y con qué se hace
COMP-FIGURE-QUOTE [fijo · ADN] · quién firma el trabajo, con nombre y con lo que hace
COMP-CASES [fijo · ADN] · trabajos hechos, con lo que tenían de difícil
COMP-CREDENTIALS [toggle TGL-ABOUT-CREDS] · certificaciones, homologaciones, gremio
COMP-CTA [fijo] · pedir presupuesto o ver el trabajo
COMP-FOOTER [fijo]
```

## 3. Secciones
### COMP-PROCESS `[fijo · ADN]`
Cómo se hace, en 4–6 pasos numerados, **cada uno con su fotografía**. Los pasos son los reales, con
sus tiempos: "medición en obra (1 día) → despiece (2 días) → corte → pulido → montaje". Mobile: pasos
apilados, foto arriba. Desktop: alternando lado. Reutilizable: GLOBAL.

### COMP-GALLERY `[fijo · ADN]`
El sitio y las herramientas. No es un porfolio —eso es `COMP-CASES`— sino la prueba de que existe un
taller: la máquina, el banco, el material esperando. Mobile: carrusel. Desktop: mosaico.
Reutilizable: GLOBAL.

### COMP-FIGURE-QUOTE `[fijo · ADN]`
Una persona, su nombre, lo que hace, y una frase suya sobre cómo trabaja. Fotografía de trabajo, no
de estudio. Como mucho dos. Reutilizable: GLOBAL.

### COMP-CASES `[fijo · ADN]`
3–5 trabajos con foto, una línea de qué era y una de qué tenía de difícil. Es la sustituta de las
cifras. Reutilizable: GLOBAL.

### COMP-CREDENTIALS `[toggle TGL-ABOUT-CREDS]`
Homologaciones, certificados, pertenencia a gremio, seguro de responsabilidad. Sólo los que se
pueden verificar. Reutilizable: GLOBAL.

## 4. Toggles admitidos
| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-ABOUT-CREDS` | on | apagar si no hay nada verificable que enseñar |
| `TGL-ABOUT-TEAM` | **off** | ver ADN: aquí la cara la da `COMP-FIGURE-QUOTE`, no una rejilla |
| `TGL-ABOUT-STATS` | **off** | ver ADN: la prueba son los trabajos, no los años |

**Fijos:** HEADER, HERO, PROCESS, GALLERY, FIGURE-QUOTE, CASES, CTA, FOOTER.
**Ausencias de ADN:** rejilla de equipo, contador de cifras, sección de valores. Un taller que
necesita las tres es una empresa, y su página es `TPL-ABOUT-01`.

## 5. SEO / semántica
1 `H1` (el oficio, no "Nosotros" a secas: "Cantería en Navarra desde el taller de Zizur" posiciona,
"Nosotros" no). `H2` en Proceso, El taller, Trabajos. Schema `Organization` + `AboutPage`, y
`LocalBusiness` si el taller recibe visitas. `alt` en todas las fotos de proceso — son contenido, no
decoración. `header` > `main` > `footer`.
