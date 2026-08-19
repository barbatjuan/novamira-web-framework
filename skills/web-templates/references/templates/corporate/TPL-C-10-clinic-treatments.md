# TPL-C-10 — Clínica / Tratamientos

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Clínica / Tratamientos |
| Objetivo | Que el paciente entienda qué le van a hacer, quién y cuánto cuesta, y pida cita |
| Ideal para | Clínicas dentales, dermatología, fisioterapia, podología, oftalmología, veterinaria |
| Ejemplos | Dental generalista, clínica del pie, centro de fisioterapia, óptica con consulta |
| Nivel de contenido | Medio: seis u ocho tratamientos contados con datos, no con adjetivos |
| Protagonismo | EL TRATAMIENTO con sus datos duros y LA PERSONA que lo hace |
| ADN | Fichas de tratamiento con duración, sesiones y anestesia + antes/después fechado + equipo con número de colegiado + aseguradoras. NO galería del local, NO tarifa larga, NO portfolio. |

**Por qué existe habiendo TPL-C-05 y TPL-C-09.** TPL-C-05 lleva a una puerta y TPL-C-09 publica una
tarifa; los dos sirven cuando lo que se compra es un servicio conocido. En una consulta sanitaria
el paciente no sabe qué le van a hacer, y **el miedo no es el precio: es el procedimiento**.
Por eso la unidad de contenido aquí no es un servicio con precio sino un TRATAMIENTO con cuatro
datos que nadie publica —cuánto dura, cuántas sesiones, si lleva anestesia y desde cuánto— y por
eso la sección de equipo lleva número de colegiado, que es el dato que convierte una foto sonriente
en una persona verificable.

## 2. Wireframe (top → bottom)

```
COMP-HEADER (con teléfono + CTA "Pedir cita") [fijo]
COMP-HERO (claim de confianza, fotografía contenida) [fijo]
COMP-TREATMENT-CARDS (duración · sesiones · anestesia · desde) [fijo · ADN]
COMP-BEFORE-AFTER (dos fotos, cada una con su fecha) [fijo · ADN]
COMP-TEAM (quién te atiende, con número de colegiado) [fijo · ADN]
COMP-INSURANCE (aseguradoras aceptadas y financiación) [toggle]
COMP-BOOKING (cita con día y hora) [fijo]
COMP-FOOTER (con NAP) [fijo]
```

Ausencia intencional: sin galería del local, sin tarifa larga, sin portfolio. El local importa
menos que el procedimiento, y una clínica que enseña su recepción antes que sus tratamientos está
respondiendo a una pregunta que el paciente no hizo.

## 3. Secciones

### COMP-HEADER `[fijo]`
Logo, nav corta, **teléfono clickeable** y CTA "Pedir cita". Sticky. Reutilizable: GLOBAL.

### COMP-HERO `[fijo] · TGL-HERO-HEIGHT`
Objetivo: confianza en la primera pantalla. Claim sobre lo que la clínica hace distinto, y una
fotografía CONTENIDA, no a sangre: una consulta a pantalla completa intimida más de lo que
tranquiliza. ~45vh. Reutilizable: SECCIÓN.

### COMP-TREATMENT-CARDS — los tratamientos `[fijo · ADN] · TGL-TREATMENT-COUNT`
Objetivo: quitar el miedo al procedimiento (decisión). Cada ficha: nombre, una línea de qué es, y
**cuatro datos en una fila** — duración de la sesión, número de sesiones, si lleva anestesia y el
precio «desde». Mobile: 1 columna. Desktop: 2 o 3. Reutilizable: SECCIÓN.
**Los cuatro datos son obligatorios y ninguno se puede dejar en blanco.** Una ficha sin el número
de sesiones es la que hace que el paciente llame para preguntar justo lo que la página iba a
ahorrarle, y una sin la palabra «anestesia» deja la pregunta que más se piensa y menos se hace.

### COMP-BEFORE-AFTER — antes y después `[fijo · ADN] · TGL-CASES-COUNT`
Objetivo: prueba (confianza). Dos fotografías del MISMO caso, **cada una con su fecha** y con el
tratamiento que hubo en medio. Mobile: apiladas. Desktop: pareadas.
**La fecha no es decoración.** Un antes/después sin fechas no dice si el resultado tardó tres
semanas o dos años, que es exactamente lo que el paciente está intentando averiguar.
Reutilizable: SECCIÓN. **Aviso legal:** en publicidad sanitaria las imágenes de resultados están
reguladas; el build tiene que llevar el consentimiento y el descargo que la comunidad autónoma
exija.

### COMP-TEAM — quién te atiende `[fijo · ADN]`
Objetivo: poner cara y credencial (confianza). Retrato, nombre, especialidad y **número de
colegiado**. Reutilizable: GLOBAL (variante sanitaria).
El colegiado es el dato que separa un equipo verificable de una foto de stock, y es público: quien
quiera comprobarlo puede.

### COMP-INSURANCE — coberturas `[toggle TGL-INSURANCE]`
Objetivo: resolver "¿me lo cubre el seguro?" antes de la llamada (decisión). Lista de aseguradoras
aceptadas y, si la hay, la financiación con su TAE. Reutilizable: SECCIÓN.

### COMP-BOOKING — cita `[fijo] · TGL-BOOKING`
Día y hora como radios visibles. Reutilizable: GLOBAL.

### COMP-FOOTER `[fijo]`
NAP completo, horarios, número de registro sanitario, legal. Reutilizable: GLOBAL.

## 4. Toggles admitidos

| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-HERO-HEIGHT` | 45vh | ADN: contenido, no a sangre |
| `TGL-TREATMENT-COUNT` | 6 fichas | |
| `TGL-CASES-COUNT` | 1 caso | |
| `TGL-INSURANCE` | on | off si no hay concierto |
| `TGL-BOOKING` | form | o teléfono |

**Fijos:** COMP-HEADER, COMP-HERO, COMP-TREATMENT-CARDS, COMP-BEFORE-AFTER, COMP-TEAM, COMP-BOOKING, COMP-FOOTER.
**Ausencias de ADN:** galería del local, tarifa larga, portfolio → sugerir TPL-C-05 o TPL-C-09.

## 5. SEO / semántica
1 `H1` (hero). `H2` en Tratamientos, Casos, Equipo, Cita. Schema `Dentist`/`MedicalClinic` +
`MedicalProcedure` por tratamiento + `Physician` por miembro del equipo con `identifier` para el
colegiado. `header` > `main` > `footer`. **Publicidad sanitaria:** los antes/después y cualquier
claim de resultado están regulados; el copy real lo revisa el cliente, no la maqueta.
