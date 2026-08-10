# Inner-page archetypes (páginas internas)

Mismo patrón que la home: arquetipos con secciones FIJAS / TOGGLE, `design-system.md` y `toggles.md`
compartidos. El recomendador, tras elegir la home, resuelve el **set de páginas** del sitio y aplica
un arquetipo por página. `html-mockup` renderiza cada página (una por Artifact, o multipágina).

Precios siempre en **€** (regla de casa del orquestador).

| Página | Arquetipos | Sirve para |
|--------|-----------|-----------|
| Product / PDP | `TPL-PDP-01` Standard, `TPL-PDP-02` Editorial | ecommerce |
| Shop / Catálogo | `TPL-SHOP-01` Sidebar, `TPL-SHOP-02` Full-width | ecommerce |
| Carrito | `TPL-CART-01` (side-cart + página) | ecommerce |
| Checkout | `TPL-CHECKOUT-01` (solo layout — funcional = `woocommerce`) | ecommerce |
| Servicio / Área | `TPL-SERVICE-01` (detalle de UN servicio) | corporate |
| About / Nosotros | `TPL-ABOUT-01` | ecommerce + corporate |
| Contacto | `TPL-CONTACT-01` | ecommerce + corporate |

## Resolución del set de páginas (recomendador)
- **Ecommerce** → Home + Shop + PDP + About + Contacto (Cart/Checkout los arma `woocommerce`).
- **Corporate** → Home + **una `TPL-SERVICE-01` por servicio/área** + About + Contacto.

En corporate, las páginas de servicio no son opcionales cuando la home lleva `COMP-SERVICES`: el
grid de la home enlaza a algún sitio, y ese sitio es una `TPL-SERVICE-01`. Sin ellas el sitio pierde
toda la búsqueda comercial ("<servicio> <ciudad>"), que nunca cae en la home. Ver
`service/TPL-SERVICE-01-service-detail.md` §5 antes de multiplicarlas: el arquetipo se replica,
el contenido no.
El recomendador propone el set, el usuario confirma, y por cada página elige arquetipo (o hereda el
default coherente con la home: ej. home TPL-E-01 Visual Brand → PDP TPL-PDP-02 Editorial).

## Componentes nuevos reutilizables
`COMP-BREADCRUMB`, `COMP-GALLERY`, `COMP-PRODUCT-INFO` (precio €, variantes, qty, add-to-cart),
`COMP-ACCORDION`, `COMP-FILTERS`, `COMP-TOOLBAR` (sort/filter), `COMP-PAGINATION`, `COMP-CONTACT-FORM`,
`COMP-MAP-NAP`, `COMP-VALUES` (+ los ya definidos: PRODUCT-CARD, CATEGORY-CARD, TESTIMONIAL, CTA, etc.).

## Toggles nuevos
`TGL-PDP-LAYOUT` (standard/editorial), `TGL-PDP-STICKY` (info sticky en desktop), `TGL-RELATED`
(relacionados sí/no), `TGL-SHOP-FILTERS` (sidebar/topbar/off), `TGL-SHOP-SORT`, `TGL-ABOUT-TEAM`,
`TGL-ABOUT-STATS`, `TGL-CONTACT-MAP`.
