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
ini_set( 'error_log', tempnam( sys_get_temp_dir(), 'eslog' ) );

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

echo "--- veredicto de corrida ---\n";
$GLOBALS['es_audit_runs'] = array();
es_container_report( array( es_split( array( es_h( 'a' ), es_h( 'b' ) ) ) ), 'limpia' );
ok( 0 === es_audit_summary(), 'build limpio devuelve 0 offenders' );
es_container_report( array( es_section( array( es_row( array( es_h( 'a' ), es_h( 'b' ) ) ) ) ) ), 'sucia' );
ok( 1 === es_audit_summary(), 'build sucio devuelve el conteo' );

echo "\n$pass OK / $fail FAIL\n";
exit( $fail ? 1 : 0 );
