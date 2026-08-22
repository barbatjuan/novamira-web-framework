# Inner-page archetypes (páginas internas)

Mismo patrón que la home: arquetipos con secciones FIJAS / TOGGLE, `design-system.md` y `toggles.md`
compartidos. El recomendador, tras elegir la home, resuelve el **set de páginas** del sitio y aplica
un arquetipo por página. `html-mockup` renderiza el set entero en UN solo Artifact con navegación interna (manda su `SKILL.md` §Hard Rules).

Precios siempre en **€** (regla de casa del orquestador).

| Página | Arquetipos | La pregunta que separa unos de otros |
|--------|-----------|-----------|
| Product / Ficha | `TPL-PDP-01` Estándar · `TPL-PDP-02` Talla y ajuste · `TPL-PDP-03` Lote y peso · `TPL-PDP-04` Suscripción · `TPL-PDP-05` A medida | ¿qué duda bloquea la compra? cuál y cuántos · si le cabe · cuánto pesa y cuándo llega · a qué se compromete · si se puede fabricar |
| Shop / Catálogo | `TPL-SHOP-01` (filtros en sidebar, barra superior o ninguno vía `TGL-SHOP-FILTERS`) | ecommerce |
| Carrito | `TPL-CART-01` (drawer + página, una sola arquitectura) | ecommerce |
| Checkout | `TPL-CHECKOUT-01` (solo layout — funcional = `woocommerce`) | ecommerce |
| Servicios (índice) | `TPL-SERVICES-01` Índice de servicios | corporate; la página que la miga de la ficha ya prometía |
| Servicio / Área | `TPL-SERVICE-01` El servicio · `TPL-SERVICE-02` El tratamiento | ¿qué falta saber? si resuelve tu problema · qué pasa dentro de la sesión |
| Nosotros | `TPL-ABOUT-01` La empresa · `TPL-ABOUT-02` El oficio · `TPL-ABOUT-03` La casa | ¿qué da confianza aquí? la trayectoria · el oficio · el sitio |
| Contacto | `TPL-CONTACT-01` Consulta · `TPL-CONTACT-02` Puerta abierta | ¿qué va a HACER el visitante? escribir · llamar o ir |
| Proyecto / caso | `TPL-PROJECT-01` (detalle de UN trabajo) | corporate |
| Unidad de inventario (ficha) | `TPL-UNIT-01` La unidad de ocasión · `TPL-PROPERTY-01` El inmueble | ¿qué se compra? una historia (de dónde viene, cuánto ha andado) · una forma y su coste de mantenerla |
| Gracias | `TPL-THANKS-01` | ecommerce + corporate |
| 404 | `TPL-404-01` | ecommerce + corporate |
| Legales | `TPL-LEGAL-01` (los cuatro documentos) | ecommerce + corporate |
| Blog (listado) | `TPL-BLOG-01` | ecommerce + corporate |
| Entrada | `TPL-POST-01` | ecommerce + corporate |

## Resolución del set de páginas (recomendador)
- **Ecommerce** → Home + Shop + PDP + About + Contacto (Cart/Checkout los arma `woocommerce`).
- **Corporate** → Home + **`TPL-SERVICES-01`** + **una ficha por servicio/área** + About +
  Contacto. El índice entra en cuanto hay más entradas de las que la home enseña; con cinco o
  menos, la home ya ES el índice y se pregunta antes de montarlo.
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
default coherente con la home). **Los criterios de elección están en `recommender.md` § 6.4**, y son
los de la tabla de arriba: la ficha por la duda que bloquea la compra, Nosotros por lo que da
confianza, Contacto por lo que el visitante va a hacer.

**Aquí había un solo arquetipo de Nosotros y uno de Contacto, y el recomendador decía en una línea
que «no tienen variantes».** Con eso, todos los sitios del catálogo se entregaban con la misma
página de Nosotros y la misma de Contacto — y `RT_TPL_TOO_SIMILAR`, que es lo que impide que dos
arquetipos de home converjan, no miraba esta carpeta. Cuando se le apuntó, `TPL-PDP-01` y
`TPL-PDP-02` compartían SIETE de sus ocho secciones y `TPL-SHOP-01`/`TPL-SHOP-02` seis de siete: no
eran parecidos, eran la misma página. Los dos que sobraban se retiraron a un toggle y las familias
que faltaban se escribieron. Ahora la regla mide también aquí, carpeta por carpeta.

## Componentes nuevos reutilizables
`COMP-BREADCRUMB`, `COMP-GALLERY`, `COMP-PRODUCT-INFO` (precio €, variantes, qty, add-to-cart),
`COMP-ACCORDION`, `COMP-FILTERS`, `COMP-TOOLBAR` (sort/filter), `COMP-PAGINATION`, `COMP-CONTACT-FORM`,
`COMP-MAP-NAP`, `COMP-VALUES`, `COMP-SERVICE-INDEX` (el índice agrupado), `COMP-TREATMENT-FACTS`
(duración · sesiones · cabina · cómo sales), `COMP-CONTRAINDICATIONS` (cuándo NO, y qué hacer
antes) (+ los ya definidos: PRODUCT-CARD, CATEGORY-CARD, TESTIMONIAL, CTA, etc.).

## Toggles nuevos
`TGL-PDP-LAYOUT` (standard/editorial), `TGL-PDP-STICKY` (info sticky en desktop), `TGL-RELATED`
(relacionados sí/no), `TGL-SHOP-FILTERS` (sidebar/topbar/off), `TGL-SHOP-SORT`, `TGL-ABOUT-TEAM`,
`TGL-ABOUT-STATS`, `TGL-ABOUT-CREDS`, `TGL-CONTACT-MAP`, `TGL-CONTACT-WHO`, `TGL-CONTACT-FORM`,
`TGL-CONTACT-WHATSAPP`, `TGL-FIT-FINDER`, `TGL-ORIGIN`, `TGL-PLAN-COUNT`, `TGL-ONE-OFF`,
`TGL-SAMPLE`, `TGL-INSTALL`, `TGL-BOOKING-TYPE`, `TGL-SERVICES-GROUP` (por zona/por área/sin
agrupar), `TGL-SERVICES-FACTS`, `TGL-SERVICES-COMMON`, `TGL-TREATMENT-BONO`.
