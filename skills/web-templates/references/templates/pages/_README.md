# Inner-page archetypes (páginas internas)

Mismo patrón que la home: arquetipos con secciones FIJAS / TOGGLE, `design-system.md` y `toggles.md`
compartidos. El recomendador, tras elegir la home, resuelve el **set de páginas** del sitio y aplica
un arquetipo por página. `html-mockup` renderiza el set entero en UN solo Artifact con navegación interna (manda su `SKILL.md` §Hard Rules).

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
| Proyecto / caso | `TPL-PROJECT-01` (detalle de UN trabajo) | corporate |
| Gracias | `TPL-THANKS-01` | ecommerce + corporate |
| 404 | `TPL-404-01` | ecommerce + corporate |
| Legales | `TPL-LEGAL-01` (los cuatro documentos) | ecommerce + corporate |
| Blog (listado) | `TPL-BLOG-01` | ecommerce + corporate |
| Entrada | `TPL-POST-01` | ecommerce + corporate |

## Resolución del set de páginas (recomendador)
- **Ecommerce** → Home + Shop + PDP + About + Contacto (Cart/Checkout los arma `woocommerce`).
- **Corporate** → Home + **una `TPL-SERVICE-01` por servicio/área** + About + Contacto.
- **Siempre, los dos tipos** → `TPL-LEGAL-01` ×4 (las escribe `wordpress-legal`), `TPL-404-01` y
  `TPL-THANKS-01` si hay formulario. No son opcionales y no se preguntan: un sitio sin legales no
  se entrega, uno sin 404 propio devuelve la página desnuda del tema, y un formulario sin página
  de gracias no se puede medir como conversión.
- **`TPL-PROJECT-01`** es obligatorio en cuanto la home lleve `TPL-C-03` Portfolio Showcase: ese
  grid enlaza a algún sitio. Sin él el portfolio es una galería, que se ve bien y no demuestra
  nada.
- **`TPL-BLOG-01` + `TPL-POST-01`** solo si hay alguien que publique, y se pregunta antes de
  montarlos: tres entradas de hace dos años restan confianza en vez de sumarla.

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
