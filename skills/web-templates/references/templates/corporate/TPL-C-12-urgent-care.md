# TPL-C-12 — Urgencias / Hoy

## 1. Identidad

| Campo | Valor |
|-------|-------|
| Nombre | Urgencias / Hoy |
| Objetivo | Que alguien con dolor sepa en diez segundos si le atienden hoy y llame |
| Ideal para | Urgencias dentales, cerrajería, fontanería, asistencia informática, grúa, veterinaria 24 h |
| Ejemplos | Guardia dental, cerrajero 24 h, reparación de calderas, avería eléctrica |
| Nivel de contenido | MUY bajo. Siete secciones y ninguna es opcional por gusto |
| Protagonismo | ¿ABREN AHORA? ¿CUÁNTO ESPERO? ¿CUÁNTO CUESTA? — en ese orden y sin bajar |
| ADN | Barra de estado con el próximo hueco + triaje por síntoma + promesa de espera y precio. NO héroe, NO galería, NO equipo largo, NO formulario de contacto. |

**Por qué existe y por qué es tan corta.** Quien entra aquí tiene dolor o una puerta cerrada, está
de pie, con una mano, y no va a leer. Todos los demás arquetipos empiezan por presentarse; éste
empieza por RESPONDER. **No tiene `COMP-HERO`, y esa ausencia es la decisión de diseño más
importante del documento**: una fotografía a pantalla completa con un claim de marca, en esta
página, es medio segundo de carga y una pantalla entera gastada en algo que a esa persona no le
importa. La primera cosa bajo la cabecera es una barra que dice si están abiertos.

## 2. Wireframe (top → bottom)

```
COMP-HEADER (teléfono como elemento principal, no como dato) [fijo]
COMP-URGENT-BAR (abierto ahora · próximo hueco · llamar) [fijo · ADN]
COMP-SYMPTOM-TRIAGE (qué te pasa → qué hacemos hoy → cuánto tarda) [fijo · ADN]
COMP-WAIT-PROMISE (espera media y precio de la urgencia) [fijo · ADN]
COMP-TEAM (quién está de guardia hoy) [toggle]
COMP-MAP-NAP (dónde y cómo llegar ahora) [fijo]
COMP-FOOTER [fijo]
```

Ausencia intencional: sin héroe, sin galería, sin formulario de contacto. **Un formulario en una
página de urgencias es una promesa de respuesta que nadie va a leer a tiempo**; el control es el
teléfono y está en la cabecera, en la barra y en el pie.

## 3. Secciones

### COMP-HEADER `[fijo]`
Logo pequeño y **el teléfono como elemento de tamaño de titular**, no como una línea de datos. Sin
nav larga: tres enlaces como mucho. Reutilizable: GLOBAL (variante urgencia).

### COMP-URGENT-BAR — el estado `[fijo · ADN] · TGL-URGENT-STATE`
Objetivo: responder «¿abren ahora?» sin desplazar (navegación). Una banda con tres cosas: **estado
abierto/cerrado, próximo hueco con hora real, y el botón de llamar**. Reutilizable: SECCIÓN.
**El estado se escribe, no se pinta sólo con color.** Un punto verde no es un estado para quien no
distingue el verde del rojo, y esta banda es la única información de la página que no se puede
permitir depender del color.

### COMP-SYMPTOM-TRIAGE — triaje `[fijo · ADN] · TGL-SYMPTOM-COUNT`
Objetivo: que reconozca su caso y sepa qué pasa (decisión). Cuatro o cinco filas: **el síntoma en
las palabras del paciente**, qué se hace hoy con eso, y cuánto dura la visita. Mobile: filas
apiladas. Desktop: tres columnas.
El síntoma se escribe como se dice —«se me ha roto un diente», no «fractura coronal»— porque quien
busca a las once de la noche escribe lo primero y no lo segundo.
Reutilizable: SECCIÓN.

### COMP-WAIT-PROMISE — espera y precio `[fijo · ADN]`
Objetivo: quitar las dos dudas que quedan (confianza). **Cuánto se espera de media** y **qué cuesta
la visita de urgencia**, las dos cifras juntas y sin desplegar nada. Reutilizable: SECCIÓN.
Publicar la espera parece un riesgo y es lo contrario: quien no la ve asume lo peor y llama a otro.

### COMP-TEAM — de guardia `[toggle TGL-TEAM]`
Quién está hoy, con número de colegiado. Reutilizable: GLOBAL (variante sanitaria).

### COMP-MAP-NAP `[fijo]`
Dirección, cómo llegar y aparcamiento, como TEXTO. Sin mapa embebido. Reutilizable: GLOBAL.

### COMP-FOOTER `[fijo]`
NAP, horario de guardia, registro sanitario, legal. Reutilizable: GLOBAL.

## 4. Toggles admitidos

| Toggle | Default | Nota |
|--------|---------|------|
| `TGL-URGENT-STATE` | abierto | el build lo calcula del horario real |
| `TGL-SYMPTOM-COUNT` | 4 filas | |
| `TGL-TEAM` | on | off si la guardia rota |

**Fijos:** COMP-HEADER, COMP-URGENT-BAR, COMP-SYMPTOM-TRIAGE, COMP-WAIT-PROMISE, COMP-MAP-NAP, COMP-FOOTER.
**Ausencias de ADN:** héroe, galería, formulario de contacto, tarifa larga → sugerir TPL-C-10 o TPL-C-09.

## 5. SEO / semántica
1 `H1`, y va en la barra de estado, no en un héroe que no existe. `H2` en Triaje, Espera,
Ubicación. Schema `EmergencyService`/`Dentist` con `openingHoursSpecification` y
`availableService` por síntoma. El teléfono en `<a href="tel:">` en los tres sitios donde aparece.
`header` > `main` > `footer`.
