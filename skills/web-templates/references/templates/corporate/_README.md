# Corporate templates (TPL-C-*)

Cinco arquetipos corporativos. Misma mecánica que ecommerce: secciones FIJAS (ADN) / TOGGLE,
`design-system.md` y `toggles.md` compartidos (con toggles propios de corporate). El recomendador
elige por perfil (ver `recommender.md` §3b).

| ID | Nombre | Ideal para | Hero | Foco |
|----|--------|-----------|------|------|
| TPL-C-01 | Services / Lead-Gen | Consultoras, agencias, servicios pro | Headline + form/CTA ~50vh | Generación de leads |
| TPL-C-02 | Institutional / Trust | Empresas, salud, legal, financiero | Imagen fija + claim ~45vh | Autoridad / confianza |
| TPL-C-03 | Portfolio / Showcase | Estudios creativos, arquitectura, foto | Visual grande ~60vh | Mostrar el trabajo |
| TPL-C-04 | Landing / Single-Offer | Una oferta única, campañas, SaaS | Propuesta + CTA fuerte ~55vh | Conversión de 1 oferta |
| TPL-C-05 | Local / Booking | Clínicas, gastronómico, servicios locales | Imagen + CTA reserva ~45vh | Reserva + ubicación |

## Diferenciación real (no mismo template)
- **C-01** vive del formulario/lead; **C-02** del prestigio y las cifras (stats fijos), CTA calmo.
- **C-03** es casi solo visual (portfolio grid), sin pricing ni stats ni forms largos.
- **C-04** es una secuencia lineal a UN CTA, sin menú ni catálogo.
- **C-05** es el único con booking + mapa/NAP + horarios como ADN.

## Componentes corporate reutilizables
`COMP-SERVICES`, `COMP-LEAD-FORM`, `COMP-PROCESS`, `COMP-CASES`, `COMP-LOGOS`, `COMP-STATS`,
`COMP-TEAM`, `COMP-PORTFOLIO-GRID`, `COMP-FEATURES`, `COMP-PRICING`, `COMP-FAQ`, `COMP-BOOKING`,
`COMP-MAP-NAP`, `COMP-GALLERY`, `COMP-CTA` (+ los compartidos `COMP-HEADER`/`COMP-HERO`/`COMP-FOOTER`/`COMP-TESTIMONIAL`).
