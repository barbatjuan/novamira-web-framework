# CAPA 2 — Recomendador

Convierte "no sé qué plantilla" en "el agente me guía". Nunca construye desde cero: bifurca por
tipo de sitio, analiza la marca, pide referencias, recomienda un `TPL-*`, confirma.

## Flujo

```
0. TIPO DE SITIO   → ecommerce | corporate
1. ANÁLISIS de marca/web
2. INTAKE de referencias
3. RECOMENDACIÓN (TPL-* + por qué)
4. CONFIRMACIÓN del usuario
5. → pasa a CAPA 3 (toggles de esa plantilla)
```

## 0. Tipo de sitio (bifurcación)

Antes de nada, resolver: ¿ecommerce o corporativa? Si `project-context` no lo deja claro,
preguntarlo (AskUserQuestion). Determina qué set de arquetipos entra:

- `ecommerce` → `templates/ecommerce/TPL-E-01..05`
- `corporate` → `templates/corporate/TPL-C-*`

El design-system y los toggles son **compartidos** entre ambos tipos.

## 1. Análisis

Con web existente o brief, extraer señales:

| Señal | Preguntas guía |
|-------|----------------|
| Objetivo primario | Descubrimiento, venta directa, confianza, navegación, urgencia, generación de leads. |
| Peso de la marca | ¿La marca vende sola (branding) o el producto/precio/servicio manda? |
| Catálogo (ecommerce) | ¿Pocos SKUs curados o catálogo grande? ¿Una categoría o muchas? |
| Rubro | Moda/deco, electrónica/repuestos, autor/premium, multi-categoría, outlet (ecommerce); servicios, salud, inmobiliaria, agencia (corporate). |
| Rol de la foto | ¿Fotografía protagonista o secundaria? |
| Estacionalidad | ¿Campañas/promos permanentes o venta/actividad estable? |

## 2. Intake de referencias (obligatorio antes de recomendar)

> "Para afinar la recomendación, pasame **2–4 referencias**: sitios que te gusten, competidores,
> o imágenes/capturas del estilo que buscás. Decime qué te gusta de cada una (el hero, las cards,
> los colores, la sensación)."

De cada referencia, anotar: tipo de hero (slider / imagen fija / banner promo / sin hero),
estilo (minimalista / elegante-editorial / comercial-agresivo), cards (imagen grande / compactas),
densidad (aire / info), foco (venta / CTA / branding), paleta y tipografía percibidas.

Estas señales alimentan la recomendación **y** precargan los toggles de CAPA 3 (llegan con
defaults sugeridos, no en blanco).

## 3. Mapa señal → plantilla (ecommerce)

| Perfil dominante | Recomienda |
|------------------|-----------|
| Marca visual, pocos SKUs, foto protagonista, descubrimiento | **TPL-E-01 Visual Brand** |
| Catálogo grande, precio/producto manda, venta directa, search | **TPL-E-02 Catalog / Product-First** |
| Marca de autor/premium, storytelling, confianza, poco catálogo | **TPL-E-03 Brand Story** |
| Muchas familias de producto, navegación por categoría | **TPL-E-04 Categories-First** |
| Outlet/campaña/estacional, urgencia, descuentos protagonistas | **TPL-E-05 Promo / Campaign** |

## 3b. Mapa señal → plantilla (corporate)

| Perfil dominante | Recomienda |
|------------------|-----------|
| Servicios B2B/profesionales, objetivo = leads, CTA/formulario protagonista | **TPL-C-01 Services / Lead-Gen** |
| Empresa establecida, salud/legal/financiero, autoridad + credenciales + cifras | **TPL-C-02 Institutional / Trust** |
| Estudio creativo/arquitectura/foto, el trabajo manda, mucho visual | **TPL-C-03 Portfolio / Showcase** |
| Una sola oferta/servicio/campaña, secuencia persuasiva a un CTA | **TPL-C-04 Landing / Single-Offer** |
| Local con reserva/turno, ubicación y horarios protagonistas | **TPL-C-05 Local / Booking** |

Empates: presentar las 2 candidatas con el trade-off y dejar elegir. Nunca decidir solo un empate.

## 4. Recomendación (formato de salida)

```
Tipo de sitio: [ecommerce | corporate].
Analicé la marca: [resumen 1–2 líneas].
Referencias: [qué se extrajo].
Recomiendo: TPL-X-0N — [nombre].
Por qué: [2–3 razones ligadas a las señales].
Alternativa: TPL-X-0M si preferís [trade-off].
¿Confirmás o revisamos la alternativa?
```

## 5. Confirmación

- El usuario confirma o pide la alternativa.
- Recién con plantilla confirmada, correr CAPA 3 (`toggles.md`) filtrando solo los toggles que
  esa plantilla admite, precargados con los defaults del intake.
- Ninguna decisión estructural importante se toma sin confirmación.
