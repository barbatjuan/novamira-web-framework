<?php
/**
 * Behavioural assertions for the container-hygiene helpers + audit.
 *
 * Run:  php tests/test-container-hygiene.php     (exit 0 = green)
 *
 * Why this exists: `es_container_audit()` is the thing that enforces the "fewest containers"
 * house rule on every build. Without a regression guard, the enforcer itself is unenforced —
 * which is exactly the failure mode it was written to prevent. Stubs the handful of WordPress
 * functions the builder touches, then asserts on real element trees. No WordPress needed.
 */
define( 'ABSPATH', __DIR__ );
define( 'ES_AUDIT_SILENT', true );

$GLOBALS['stub_img'] = array( 'foto' => 42 );
function get_posts( $a ) {
	if ( isset( $GLOBALS['stub_img'][ $a['name'] ] ) ) {
		$o     = new stdClass();
		$o->ID = $GLOBALS['stub_img'][ $a['name'] ];
		return array( $o );
	}
	return array();
}
function wp_get_attachment_url( $id ) { return 'https://x.test/' . $id . '.jpg'; }
/* Kept in a global so the report's TEXT is assertable. This file defines ES_AUDIT_SILENT for the
   whole process, so es_container_report()'s stdout copy is muted and error_log() is the only
   channel left — without this, the report could print anything at all and nothing would notice. */
$GLOBALS['es_log'] = tempnam( sys_get_temp_dir(), 'eslog' );
ini_set( 'error_log', $GLOBALS['es_log'] );
function log_since( $offset ) {
	clearstatcache();
	return substr( (string) @file_get_contents( $GLOBALS['es_log'] ), $offset );
}
function log_mark() {
	clearstatcache();
	return strlen( (string) @file_get_contents( $GLOBALS['es_log'] ) );
}

require_once dirname( __DIR__ ) . '/skills/elementor-core/assets/es-builder.php';

$pass = 0; $fail = 0;
function ok( $cond, $label ) {
	global $pass, $fail;
	if ( $cond ) { $pass++; echo "  OK   $label\n"; }
	else { $fail++; echo "  FAIL $label\n"; }
}
function has( array $list, $needle ) {
	foreach ( $list as $l ) { if ( false !== strpos( $l, $needle ) ) { return true; } }
	return false;
}

echo "--- es_split: la seccion ES la fila ---\n";
es_uid_reset( 't1' );
$split = es_split( array( es_h( 'A' ), es_p( 'B' ) ) );
ok( 'row' === $split['settings']['flex_direction'], 'flex_direction row en desktop' );
ok( 'column' === $split['settings']['flex_direction_mobile'], 'apila en mobile' );
ok( 2 === count( $split['elements'] ), 'los hijos son directos, sin fila intermedia' );
$a = es_container_audit( array( $split ) );
ok( 1 === $a['containers'] && 2 === $a['widgets'], 'un solo contenedor para dos columnas' );
ok( 1 === $a['max_depth'], 'profundidad 1' );
ok( ! $a['offenders'], 'sin offenders' );

echo "--- el patron viejo se detecta como offender ---\n";
es_uid_reset( 't2' );
$viejo = es_section( array( es_row( array( es_h( 'A' ), es_p( 'B' ) ) ) ) );
$a = es_container_audit( array( $viejo ) );
ok( 2 === $a['containers'], 'el patron viejo cuesta 2 contenedores' );
ok( has( $a['offenders'], 'la seccion ES la fila' ), 'seccion>fila flex marcada como offender' );

echo "--- seccion>grid sigue siendo solo fusionable, no error ---\n";
es_uid_reset( 't3' );
$grid = es_section( array( es_grid( 3, array( es_h( 'a' ), es_h( 'b' ), es_h( 'c' ) ) ) ) );
$a = es_container_audit( array( $grid ) );
ok( ! $a['offenders'], 'el idioma dominante del repo no dispara falsos positivos' );
ok( has( $a['optimizable'], 'unico hijo es un grid' ), 'reportado como fusionable' );

echo "--- es_wide: ancho sin envoltorio ---\n";
es_uid_reset( 't4' );
$w = es_wide( es_p( 'texto' ), 58 );
ok( 'initial' === $w['settings']['_element_width'], '_element_width initial' );
ok( 58 === $w['settings']['_element_custom_width']['size'], '_element_custom_width 58%' );
ok( 100 === $w['settings']['_element_custom_width_mobile']['size'], 'full width en mobile' );
$a = es_container_audit( array( es_split( array( $w, es_p( 'otro' ) ) ) ) );
ok( 1 === $a['containers'], 'dar un ancho no agrego ningun contenedor' );

echo "--- envoltorio que solo da un ancho: offender con el fix nombrado ---\n";
es_uid_reset( 't5' );
$wrap = es_c( array( 'width' => es_size( 58, '%' ) ), array( es_p( 'texto' ) ) );
$a = es_container_audit( array( $wrap ) );
ok( has( $a['offenders'], 'es_wide' ), 'el mensaje nombra es_wide()' );

echo "--- es_photo: la foto es un widget ---\n";
es_uid_reset( 't6' );
$ph = es_photo( 'foto', 400 );
ok( 'widget' === $ph['elType'] && 'image' === $ph['widgetType'], 'es un widget image' );
ok( 'cover' === $ph['settings']['object-fit'], 'object-fit cover' );
ok( ! empty( $ph['settings']['height']['size'] ), 'height presente (object-fit lo exige)' );
$a = es_container_audit( array( es_split( array( $ph, es_p( 'copy' ) ) ) ) );
ok( 1 === $a['containers'] && ! $a['offenders'], 'foto + copy = un contenedor, cero offenders' );

echo "--- contenedor vacio con imagen de fondo ---\n";
es_uid_reset( 't7' );
$bg = es_c( array( 'background_background' => 'classic', 'background_image' => es_img( 'foto' ) ), array() );
$a = es_container_audit( array( $bg ) );
ok( has( $a['offenders'], 'es_photo' ), 'el mensaje apunta a es_photo()' );

echo "--- imagen de fondo rota ya no compra coartada ---\n";
$roto = es_container_earns_its_place( array( 'background_image' => es_img( 'no-existe' ) ) );
ok( false === $roto, 'url vacia no justifica el contenedor' );
$bueno = es_container_earns_its_place( array( 'background_image' => es_img( 'foto' ) ) );
ok( true === $bueno, 'url real si lo justifica' );

echo "--- profundidad > 3 ---\n";
es_uid_reset( 't8' );
$deep = es_c( array(), array( es_c( array(), array( es_c( array(), array( es_c( array(), array( es_h( 'x' ) ) ) ) ) ) ) ) );
$a = es_container_audit( array( $deep ) );
ok( 4 === $a['max_depth'], 'profundidad 4 medida' );
ok( has( $a['offenders'], 'profundidad 4' ), 'profundidad excesiva reportada' );

echo "--- arbol legacy section>column>widget: no auditable, nunca invisible ---\n";
es_uid_reset( 't9' );
function legacy_el( $type, array $children ) {
	return array( 'elType' => $type, 'settings' => array(), 'elements' => $children );
}
$legacy = array( legacy_el( 'section', array( legacy_el( 'column', array( es_h( 'titulo' ) ) ) ) ) );
$a      = es_container_audit( $legacy );
ok( 0 === $a['containers'], 'no hay Containers que contar' );
ok( 1 === $a['widgets'], 'el widget bajo dos envoltorios legacy SI se cuenta' );
ok( isset( $a['unaudited']['section'] ) && 1 === $a['unaudited']['section']['count'], 'section queda registrado como no auditable' );
ok( isset( $a['unaudited']['column'] ) && 1 === $a['unaudited']['column']['count'], 'column queda registrado como no auditable' );
ok( '/0' === $a['unaudited']['section']['first'], 'guarda donde aparecio el primero' );
ok( 2 === $a['max_depth'], 'cada envoltorio legacy cuenta como un nivel' );
ok( ! $a['offenders'], 'un arbol importado no es culpa de quien llama: no es offender' );

echo "--- un Container culpable escondido bajo un envoltorio legacy ---\n";
es_uid_reset( 't10' );
$mixto = array( legacy_el( 'section', array( es_c( array(), array( es_p( 'solo' ) ) ) ) ) );
$a     = es_container_audit( $mixto );
ok( 1 === $a['containers'], 'el Container bajo el envoltorio legacy se ve' );
ok( has( $a['offenders'], 'no aporta nada' ), 'y su falta se reporta igual que en la superficie' );

echo "--- contenido dentro de un widget tampoco es invisible ---\n";
es_uid_reset( 't11' );
$loop             = es_w( 'loop-grid', array() );
$loop['elements'] = array( es_c( array(), array( es_p( 'dentro' ) ) ) );
$a                = es_container_audit( array( $loop ) );
ok( 1 === $a['containers'] && 2 === $a['widgets'], 'el arbol que cuelga de un widget se recorre' );
ok( has( $a['offenders'], 'no aporta nada' ), 'y sus offenders cuentan' );

echo "--- debajo de lo que no se puede juzgar, el audit no afirma nada ---\n";
es_uid_reset( 't10b' );
/* Un Container cuyo UNICO hijo es un elType que el walk no sabe juzgar. El remedio de "envoltorio
   de 1 widget" no se puede seguir aqui: el hijo no es un widget. */
$opaco = array( legacy_el( 'section', array( es_c( array(), array( legacy_el( 'column', array( es_h( 'a' ), es_h( 'b' ) ) ) ) ) ) ) );
$a     = es_container_audit( $opaco );
ok( ! $a['offenders'], 'un envoltorio cuyo unico hijo no es juzgable no recibe un remedio imposible' );
ok( 2 === $a['widgets'], 'y el arbol de abajo se sigue recorriendo entero' );
/* La profundidad se MIDE igual; lo que no se hace es cobrarsela a quien no la escribio. */
$hibrido = array( legacy_el( 'section', array( legacy_el( 'column', array( legacy_el( 'section', array( legacy_el( 'column', array( es_c( array(), array( es_h( 'a' ), es_h( 'b' ) ) ) ) ) ) ) ) ) ) ) );
$a       = es_container_audit( $hibrido );
ok( 5 === $a['max_depth'], 'la profundidad heredada se mide' );
ok( ! has( $a['offenders'], 'profundidad' ), 'pero no se cobra: 4 envoltorios importados no son una decision de anidado' );
$propio = es_c( array(), array( es_c( array(), array( es_c( array(), array( es_c( array(), array( es_h( 'x' ) ) ) ) ) ) ) ) );
ok( has( es_container_audit( array( $propio ) )['offenders'], 'profundidad 4' ), 'la profundidad que SI escribio quien llama se sigue cobrando' );
/* La seccion SI es boxed; el column de por medio no lo es nadie sabe. Si el ancho se heredara a
   traves de el, el contenedor de adentro seria un "boxed dentro de boxed" que nadie verifico. */
$boxeado = array( es_section( array( legacy_el( 'column', array( es_c( array( 'content_width' => 'boxed' ), array( es_p( 'x' ) ) ) ) ) ) ) );
ok( ! es_container_audit( $boxeado )['offenders'], 'debajo de un envoltorio opaco no se hereda un ancho boxed que nadie verifico' );

echo "--- unaudited agrega de verdad: cuenta y recuerda el PRIMERO ---\n";
$dos = array( legacy_el( 'section', array( legacy_el( 'column', array( legacy_el( 'section', array( es_h( 'x' ) ) ) ) ) ) ) );
$a   = es_container_audit( $dos );
ok( 2 === $a['unaudited']['section']['count'], 'dos section del mismo tipo suman, no se pisan' );
ok( '/0' === $a['unaudited']['section']['first'], 'y "first" guarda el mas superficial, no el ultimo visto' );

echo "--- un elemento sin elType se nombra, no se ignora ---\n";
$a = es_container_audit( array( array( 'settings' => array(), 'elements' => array() ) ) );
ok( isset( $a['unaudited']['(sin elType)'] ), 'sin elType tiene su propia entrada' );

echo "--- el remedio nombrado depende de la direccion del hijo ---\n";
es_uid_reset( 't12' );
$col = es_c( array(), array( es_c( array( 'flex_direction' => 'column' ), array( es_h( 'a' ), es_h( 'b' ) ) ) ) );
$a   = es_container_audit( array( $col ) );
ok( has( $a['offenders'], 'fusiona ambos' ), 'hijo en columna: fusionar los dos' );
ok( ! has( $a['offenders'], 'es_split()' ), 'es_split() NO se propone para un hijo en columna: cambiaria el eje' );
$rev = es_c( array(), array( es_c( array( 'flex_direction' => 'row-reverse' ), array( es_h( 'a' ), es_h( 'b' ) ) ) ) );
$a   = es_container_audit( array( $rev ) );
ok( has( $a['offenders'], 'es_split()' ), 'row-reverse sigue siendo una fila' );

echo "--- acotar un widget suelto al ancho boxed SI es un motivo ---\n";
es_uid_reset( 't13' );
$boxed = es_section( array( es_w( 'wc-archive-products', array() ) ) );
$a     = es_container_audit( array( $boxed ) );
ok( ! $a['offenders'], 'el unico modo de acotar un widget al ancho boxed no es un offender' );
$anidado = es_section( array( es_c( array( 'content_width' => 'boxed' ), array( es_p( 'x' ) ) ) ) );
$a       = es_container_audit( array( $anidado ) );
ok( has( $a['offenders'], 'acotar de nuevo no cambia el ancho' ), 'boxed dentro de boxed no aporta un segundo acotado' );
/* Un widget SI es un elemento que este walk juzga, asi que el contexto lo atraviesa: lo que cuelga
   de una plantilla de loop sigue estando dentro del ancho que acoto el contenedor de arriba. */
$enWidget             = es_w( 'loop-grid', array() );
$enWidget['elements'] = array( es_c( array( 'content_width' => 'boxed' ), array( es_p( 'x' ) ) ) );
$a                    = es_container_audit( array( es_section( array( $enWidget ) ) ) );
ok( has( $a['offenders'], 'acotar de nuevo no cambia el ancho' ), 'el ancho boxed se hereda a traves de un widget' );
ok( true === es_container_earns_its_place( array( 'content_width' => 'boxed' ), array( 'only_child' => 'widget' ) ), 'boxed + hijo widget + sin ancestro boxed: se gana el lugar' );
ok( false === es_container_earns_its_place( array( 'content_width' => 'boxed' ), array( 'only_child' => 'container' ) ), 'boxed no salva a un contenedor cuyo hijo es otro contenedor' );
ok( false === es_container_earns_its_place( array( 'content_width' => 'boxed' ), array( 'only_child' => 'widget', 'boxed_ancestor' => true ) ), 'con un ancestro boxed ya no aporta' );
ok( false === es_container_earns_its_place( array( 'content_width' => 'boxed' ) ), 'sin contexto no se regala el permiso' );

echo "--- cada remedio para un widget suelto tiene que poder hacerse ---\n";
$pad = es_container_audit( array( es_c( array( 'padding' => es_box( 40, 0, 40, 0 ) ), array( es_p( 'x' ) ) ) ) );
ok( has( $pad['offenders'], 'pasa el padding al widget' ), 'con padding, el remedio es pasarlo' );
$sin = es_container_audit( array( es_c( array(), array( es_p( 'x' ) ) ) ) );
ok( ! has( $sin['offenders'], 'padding' ), 'sin padding el mensaje ya no manda mover un padding que no existe' );

echo "--- el reporte por pagina dice lo que no pudo juzgar ---\n";
$mark = log_mark();
es_container_report( $legacy, 'heredada' );
$linea = log_since( $mark );
ok( false !== strpos( $linea, 'NO AUDITABLE' ), 'el reporte nombra la parte no auditada' );
ok( false !== strpos( $linea, 'elType "section" x1' ), 'con el elType y su conteo' );

echo "--- LIMITE DECLARADO DE ESTE COMMIT ---\n";
/* El veredicto de corrida todavia dice LIMPIO sobre un arbol que no pudo juzgar. El contrato de
   senales (-1 sin auditar / -2 no auditable) es el slice siguiente. Esto se afirma aqui a
   proposito: cuando ese slice llegue, esta assertion se pone ROJA y hay que actualizarla — que
   es exactamente lo que un comentario "pendiente" no hace. */
$GLOBALS['es_audit_runs'] = array();
es_container_report( $legacy, 'heredada' );
ok( 0 === es_audit_summary(), 'PENDIENTE (contrato de senales): hoy el veredicto sigue devolviendo 0 sobre un arbol no auditado' );

echo "--- veredicto de corrida ---\n";
$GLOBALS['es_audit_runs'] = array();
es_container_report( array( es_split( array( es_h( 'a' ), es_h( 'b' ) ) ) ), 'limpia' );
ok( 0 === es_audit_summary(), 'build limpio devuelve 0 offenders' );
es_container_report( array( es_section( array( es_row( array( es_h( 'a' ), es_h( 'b' ) ) ) ) ) ), 'sucia' );
ok( 1 === es_audit_summary(), 'build sucio devuelve el conteo' );

echo "\n$pass OK / $fail FAIL\n";
exit( $fail ? 1 : 0 );
