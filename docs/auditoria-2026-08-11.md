# Auditoría del framework — 2026-08-11

Foto fechada del estado del repositorio en `18fe00b`. **No es documentación viva**: describe lo que
se encontró ese día. Cuando un hallazgo se cierre, se anota aquí con el commit que lo cerró; el
documento no se reescribe.

Método: 24 agentes en paralelo — ocho lectores sobre distintas porciones del repositorio, tres
análisis de flujo, once lotes de refutación adversaria, un crítico de completitud y una síntesis.
**97 hallazgos confirmados** tras refutación, 11 refutados. 2 BLOCKER, 37 MAJOR, 37 MINOR, 4 IDEA.

---

## Veredicto

**No estaba listo para producción.** Diseño sólido y con un motor real detrás, pero entregaba
sitios rotos y los reportaba como limpios mientras lo hacía.

Lo más peligroso no era un fallo, era **el mismo fallo en cinco sitios**:

> Todas las verificaciones automáticas podían reportar éxito sobre trabajo que nunca inspeccionaron.

1. `es_img()` devuelve `array( 'url' => '', 'id' => '' )` ante un slug inexistente y no avisa, así
   que una foto mal escrita entrega un widget sin `<img>` y todas las verificaciones siguen verdes.
2. `es_container_walk()` no tiene rama `else` y su llamada recursiva vive dentro de la rama
   `container`, de modo que una página heredada (`section > column > widget`) audita como
   `0 contenedores / 0 widgets` y `es_audit_summary()` imprime `VEREDICTO LIMPIO` sobre un árbol
   que nunca recorrió.
3. La comprobación «¿esta regla nombra un verificador?» de `framework-audit.php` era una búsqueda
   de subcadena sobre un vocabulario. De cinco reglas sin verificador inyectadas a propósito,
   cuatro pasaron. El titular `0 FAIL / 11 WARN / 0 JUDGE` medía dialecto de la casa, no cobertura.
4. El FAIL «enruta a una skill que falta» del mismo script era **código muerto inalcanzable**.
5. Nada invocaba `qa-review`. Ningún contrato de salida de las cinco skills con capacidad de
   escritura la nombra, y el README documenta la invocación directa: en ese camino las quince
   filas de reglas de casa nunca se ejecutan.

Un framework cuyas líneas de veredicto no son fiables es **peor** que uno sin veredictos, porque el
agente aprende a confiar en ellas.

Segundo problema, más barato de arreglar: **el camino destructivo está poco instrumentado.** La
rama de fallo de `es_save_page()` regresa en silencio, su búsqueda de slug puede crear una página
publicada duplicada y reportar `created`, la portada del sitio nunca se establece ni se comprueba,
y la única copia de seguridad cubre una clave de metadatos de una sola entrada mientras su propia
documentación afirma proteger «cada sobrescritura».

La capa de diseño (`web-templates`, `ux-design-system`, `html-mockup`) es la parte más sana del
repositorio. `divi-core` es un andamio, correctamente etiquetado. La disciplina de higiene de
contenedores es buen trabajo: lo que había que arreglar era **el auditor**, no la idea.

---

## Hallazgos bloqueantes y mayores

| # | Sev | Título | Fichero | Arreglo | Est. |
|---|-----|--------|---------|---------|------|
| 1 | BLOCKER | `es_img()` entrega una imagen vacía y no avisa a nadie | `skills/elementor-core/assets/es-builder.php` | `es_warn()` en el fallo, cachear el fallo, sumarlo al veredicto | S |
| 2 | BLOCKER | Nada establece ni verifica la portada de WordPress | `es-builder.php` | Añadir `front_page_id` a `project-context`; resolver la home por `get_option('page_on_front')` | M |
| 3 | MAJOR | La auditoría de contenedores imprime `LIMPIO` en cualquier árbol no-contenedor | `es-builder.php` | Rama `else` que cuente y recurse; negar `LIMPIO` si no se auditó nada | S |
| 4 | MAJOR | Nada delega en `qa-review`; quince filas omitidas en el camino directo | `agents/novamira-web-orchestrator.md` | Línea de traspaso en cada contrato de salida + FAIL de `framework-audit` por su ausencia | M |
| 5 | MAJOR | La comprobación de verificador pasa por vocabulario | `skills/framework-audit/assets/framework-audit.php` | Eliminar la expresión regular; exigir marcador explícito `(verifier: …)` / `(no verifier: …)` | M |
| 6 | MAJOR | Las reglas de casa del agente nunca se analizan | `framework-audit.php` | Analizar `## House rules` en el bucle de agentes; añadir fila para el formulario en el hero | M |
| 7 | MAJOR | «Enruta a una skill que falta» es código muerto | `framework-audit.php` | Invertir la comprobación + prueba de regresión | S |
| 8 | MAJOR | La capacidad de escritura se detecta leyendo prosa, no código | `framework-audit.php` | Detectar desde `assets/*.php` exigiendo forma de llamada | M |
| 9 | MAJOR | Los tres artefactos de referencia del repositorio fallan su propia auditoría | `skills/*/assets/` | Ponerlos en verde y aseverar cero infractores en `tests/` | M |
| 10 | MAJOR | La rama infractora nunca lee `flex_direction` | `es-builder.php` | Condicionar el consejo de `es_split()` a que el hijo sea fila | S |
| 11 | MAJOR | `earns_its_place()` ignora `content_width`/`padding` | `es-builder.php` | Eximir un contenedor de un solo widget que aporta ancho o relleno | S |
| 12 | MAJOR | `es-product-single.example.php` usa el patrón que su propia skill prohíbe | `skills/woocommerce/assets/` | Reconstruir con `es_split()` + `es_wide()` | S |
| 13 | MAJOR | La fila 9 falla en todo sitio multipágina correcto | `skills/qa-review/references/house-rules.md` | Normalizar `current-*` y `aria-current` antes de comparar | S |
| 14 | MAJOR | La rama de fallo de `es_save_page()` es silenciosa | `es-builder.php` | `es_warn()` nombrando el slug antes de regresar | S |
| 15 | MAJOR | Una colisión de slug crea una página duplicada y reporta `created` | `es-builder.php` | Comparar el slug real con el pedido y avisar ante discrepancia | S |
| 16 | MAJOR | Sin higiene de slugs ni redirecciones: la página vieja sigue publicada e indexada | `es-builder.php` | `es_migrate_slug()` + fila de verificación para huérfanas vivas | M |
| 17 | MAJOR | La copia de seguridad es más estrecha de lo que promete | `es-builder.php` | Respaldar el conjunto completo desplazado; avisar al convertir una página no-Elementor | M |
| 18 | MAJOR | «Cambia sus constantes de paleta»: el fichero no tiene ninguna | `skills/elementor-core/SKILL.md` | Extraer constantes reales, incluidos los `rgba` dentro de `custom_css` | M |
| 19 | MAJOR | `es_wide()` no escribe claves de tableta; `es_split()` apila en tableta | `es-builder.php` | Reflejar el bloque móvil en `_tablet` y aseverarlo | S |
| 20 | MAJOR | En proyecto nuevo, `project-context` corre **después** de aprobar la maqueta | `agents/novamira-web-orchestrator.md` | Preguntar capacidades en la toma de requisitos | M |
| 21 | MAJOR | Elementor Pro y el soporte de contenedores son requisitos que nadie comprueba | `skills/project-context/SKILL.md` | Añadirlos al contrato y bloquear la escritura si faltan | M |
| 22 | MAJOR | Formularios: tres plantillas delegan en un paso que no existe | `web-templates/…/TPL-C-01`, `TPL-C-05`, `TPL-CONTACT-01` | Construir `wordpress-forms` | L |
| 23 | MAJOR | El plugin de SEO nunca se detecta, aunque la skill afirme que sí | `skills/wordpress-seo/SKILL.md` | Añadir `seo_plugin` al contrato de `project-context` | M |
| 24 | MAJOR | Tres ficheros prometen que `qa-review` compara con la maqueta; `qa-review` no la menciona | `skills/html-mockup/` | Construir la comparación o retirar la promesa | M |
| 25 | MAJOR | La arquitectura resuelta, producto entero de `web-templates`, no tiene verificador | `skills/web-templates/SKILL.md` | Persistirla y comparar inventario y orden contra lo construido | L |
| 26 | MAJOR | Siete secciones marcadas como alternables con un identificador inexistente | plantillas varias | Darles identificador real o fijarlas | M |
| 27 | MAJOR | `es_theme_conditions_registered()` da verde ante el secuestro de plantilla | `es-builder.php` | Avisar de cualquier otra plantilla publicada en la misma ubicación | S |
| 28 | MAJOR | `es-theme-parts.example.php` aborta con **cero** salida si falta su dependencia | `skills/elementor-core/assets/` | Copiar la guarda de los ficheros de comercio | S |
| 29 | MAJOR | El parámetro de salida que permite confirmar cada sobrescritura no se usa | `es-theme-parts.example.php` | Devolverlo o imprimir la acción | M |
| 30 | MAJOR | Ningún paso previo cruza el inventario de páginas con los slugs a escribir | orquestador | `es_overwrite_preflight()` cuya salida es lo que el usuario aprueba | M |
| 31 | MAJOR | La ruta temporal de Lighthouse son los primeros bytes de la URL | `skills/qa-review/assets/lighthouse-audit.mjs` | Derivarla de la URL completa más el identificador de proceso | S |
| 32 | MAJOR | Páginas legales y consentimiento de cookies sin responsable | `es-theme-parts.example.php` | Convertirlas en enlaces reales; construir `wordpress-legal` | L |
| 33 | MAJOR | No hay fase de entrega: los `.php` autoejecutables quedan en producción | orquestador | Fase bloqueante que borre el sandbox y confirme que está vacío | S |
| 34 | MAJOR | La indexabilidad del sitio nunca se lee | `skills/wordpress-seo/SKILL.md` | Leer `blog_public` y añadir fila automática | S |
| 35 | MAJOR | `layout-patterns.md` prescribe el orden contrario a la regla 10 | `skills/ux-design-system/references/` | Reescribirlo y señalar la fuente que manda | S |
| 36 | MAJOR | La aprobación no puede mostrar la tipografía que se entrega | `skills/html-mockup/references/mockup-guide.md` | Exigir un bloque visible que nombre las familias reales | M |
| 37 | MAJOR | La rejilla de portafolio enlaza a un arquetipo de página que no existe | `TPL-C-03` | Añadir `TPL-PROJECT-01` | M |
| 38 | MAJOR | Secciones fijas que necesitan capacidades del constructor sin degradación | `TPL-E-01` | Añadir requisitos y degradación obligatoria | M |
| 39 | MAJOR | La puerta de construcción vive solo en markdown | orquestador y `es-builder.php` | Comprobación de consentimiento en la propia función de guardado | M |

Los 37 hallazgos menores y las 4 ideas están en el informe completo de la sesión; los más
repetidos son la ausencia de verificador para 404, `robots.txt`, mapa del sitio, favicon,
consentimiento y entrega real de formularios.

---

## Qué construir, por daño evitado

1. **Instrumentar el camino de escritura.** Avisos en `es_img()` y en el fallo de `es_save_page()`,
   comprobación previa de sobrescritura, relectura tras escribir. **Ni skill ni agente**: tiene que
   ejecutarse en el mismo proceso PHP que tiene el identificador y el array. Una skill solo puede
   *describirlo*, que es precisamente la clase de fallo que este repositorio combate.
2. **Arreglar el auditor, luego poner los ejemplos en verde, luego añadir la aserción.** En ese
   orden: corregir primero el auditor permite *verificar* el arreglo de los ejemplos en vez de
   afirmarlo.
3. **`wordpress-forms`, como skill.** Detección de capacidad, ayudante de formulario con
   destinatario y consentimiento, y una prueba de entrega real. Skill y no agente porque debe
   correr en el hilo que ya tiene el conector, los identificadores de página y el token.
4. **`novamira-copywriter`, como subagente.** Redactar es un trabajo de salida larga cuyo contexto
   ideal es el opuesto al del hilo de construcción. La ventana nueva es la ventaja, y debe
   invocarse por delegación explícita: una skill que se dispare con «texto» durante un despliegue
   es un riesgo.
5. **`wordpress-legal`, como skill.** Aviso legal, privacidad, cookies y condiciones con los datos
   fiscales reales del cliente, sin inventarlos, más el banner de consentimiento.
6. **Fase de entrega bloqueante** en el orquestador: borrar el sandbox, confirmar que quedó vacío,
   entregar las claves de copia de seguridad y declarar el estado de indexación.
7. **Persistir el estado** en un manifiesto del proyecto. Es requisito previo de cuatro hallazgos
   distintos.
8. **Filas nuevas de reglas de casa**, todas automáticas: entrega real del formulario, 404 con
   estilo, `robots.txt`, mapa del sitio, favicon, banner de consentimiento, indexabilidad y color
   de acento coincidente con el aprobado.
9. **Arquetipos que faltan**, por frecuencia: proyecto, legales, blog y entrada, gracias, 404.
10. **Comprobador de claves de widget** que avise cuando una clave de contenedor aparece en un
    widget.

---

## Qué NO se comprobó

Esta sección importa tanto como las anteriores.

- **Nada se ejecutó contra un WordPress vivo.** Toda afirmación sobre el comportamiento de
  Elementor procede de las notas del propio repositorio y de conocimiento general, no de medición.
  El PHP que sí se ejecutó corrió contra sustitutos escritos a mano, no contra WordPress.
- **El canal del sandbox no se probó.** Nadie verificó que un `.php` depositado en
  `wp-content/novamira-sandbox/` se ejecute solo, ni qué devuelve realmente por salida estándar.
- **El alcance de Chrome sin interfaz al dominio del sandbox sigue sin verificar.**
- **Los hechos de almacenamiento de `divi-core` siguen sin confirmar.**
- **La activación real de las skills por palabra clave nunca se observó.** Es la mayor suposición
  no probada del informe, porque todo el comportamiento del framework depende de ella.
- **Sin revisión de seguridad** más allá de constatar que el directorio del sandbox nunca se
  limpia.
- **Sin medición de rendimiento ni accesibilidad** de un sitio producido.
- **Sin ruta multiidioma.**

---

## Estado

| Hallazgo | Estado |
|----------|--------|
| 7, 8 | **Cerrados** por `1ac7992`, con el primer arnés de regresión que `framework-audit.php` ha tenido |
| 5 | **Cerrado** por `048fdf1` + `3017d90` + `3bfc649` + `8b72cdb`: la regex de vocabulario borrada, marcador `(verifier: …)` / `(no verifier: …)` exigido en las cinco skills con capacidad de escritura, y las 23 reglas migradas |
| 6 | **Cerrado** por `344e5fa`: las `## House rules` del orquestador se analizan por primera vez, con sus 11 marcadores |
| 3, 10, 11 | **Cerrados** por `5d946e7` + `b0ff90e`: el recorrido entra en los árboles heredados y nombra lo que no supo juzgar; el remedio lee la dirección del hijo antes de proponerlo; `earns_its_place()` reconoce el ancho boxed. `es_audit_summary()` devuelve cuatro valores, así que «no se auditó nada» dejó de parecerse a «limpio» |
| varios MINOR | **Cerrados** en el mismo programa: cobertura de archivos huérfanos a cualquier profundidad (`9886ef4`), y el acoplamiento de `ES_AUDIT_SILENT` con `es_warn()`, que silenciaba avisos junto con el reporte de rutina (`b0ff90e`) |
| 1 | **Cerrado** por `1c01901`: `es_img()` avisa al no encontrar el slug y deja una miga `es_missing` que el recorrido convierte en offender, así que el fallo sobrevive hasta `_elementor_data` y la fila 11 de `qa-review` lo vuelve a ver |
| 14, 15 | **Cerrados** por `c0d88c9`: la rama de fallo de `es_save_page()` avisa nombrando el slug y el motivo de WordPress; el slug real se relee tras crear y una discrepancia avisa nombrando ambos, con `$action` en `created-renamed`. El mismo commit cierra un tercer defecto de la misma clase que salió al escribir la fixture: el retorno de `wp_update_post()` se descartaba, así que la rama de actualización no podía fallar y pisaba el diseño de una página que WordPress se había negado a tocar. `es_save_theme_part()` recibe los mismos dos arreglos |
| 2 | **Cerrado** por `af1e6ba`: `es_front_page()` resuelve qué sirve `/` leyendo las DOS opciones —`page_on_front` sola no es una portada—, y `es_set_front_page()` la establece y **relee las opciones**, porque `update_option()` devuelve `false` tanto al fallar como al no cambiar nada. Repuntar una portada existente avisa nombrando la página que deja de mostrarse. La regla no existía en ninguna parte: ahora está en las House rules del orquestador, en la fila 16 de `qa-review` (que además pide `/` de verdad, porque una opción es intención y una respuesta es prueba) y en `project-context`, que reporta `front_page_id` |
| Resto | Abiertos. El cambio 1 de ocho, `audit-truthfulness`, está cerrado; del 2 quedan los hallazgos 16, 17, 27, 28 y 30 —los dos BLOCKER ya están cerrados |

Esta tabla llegó tarde y con un error: durante siete commits nadie la actualizó, y su primera
versión daba el hallazgo 5 por cerrado en `1ac7992` cuando la regex de vocabulario siguió viva
hasta `048fdf1`. Un registro de cierres que nadie verifica es la misma clase de fallo que este
informe describe, cometida por el informe. Y volvió a pasar de inmediato: la fila del hallazgo 1
faltaba, porque la tabla se escribió un commit antes de que se cerrara. Nada obliga a actualizarla
—no hay verificador para esto, y la única defensa es acordarse—, así que conviene leerla sabiendo
que puede ir por detrás de la rama.

El commit que cerró esos tres hallazgos encontró uno nuevo durante su propia revisión: la
comprobación de auto-registro que ese mismo commit añadía era el único verificador nuevo que su
arnés no ejercitaba, así que borrarla entera dejaba la suite en verde. Se corrigió antes de
entrar. Vale la pena registrarlo porque es la enfermedad de este informe reproducida dentro de su
propia cura, y porque la encontró la revisión, no el autor.
