<?php
/**
 * Regression harness for framework-audit.php's design-personalities catalog checks.
 *
 * Run: php tests/test-framework-audit.php     (exit 0 = green)
 *
 * Why a subprocess: framework-audit.php is a top-level script that reads $argv and calls exit()
 * directly. Including it would kill this test process on its first fixture, and the exit code is
 * half of what this file needs to prove. Every fixture is a synthetic tree written to a fresh
 * temp directory — never the real repo — so a broken fixture can never mark real skills.
 */

$root_dir = dirname( __DIR__ );
$audit    = $root_dir . '/skills/framework-audit/assets/framework-audit.php';

$pass = 0;
$fail = 0;
$GLOBALS['fx_roots'] = array();
register_shutdown_function(
	function () {
		foreach ( $GLOBALS['fx_roots'] as $dir ) {
			fx_rrmdir( $dir );
		}
	}
);

function ok( $cond, $label, $actual = null ) {
	global $pass, $fail;
	if ( $cond ) {
		$pass++;
		echo "  OK   $label\n";
	} else {
		$fail++;
		$suffix = ( null !== $actual ) ? ' -- actual: ' . fx_repr( $actual ) : '';
		echo "  FAIL $label$suffix\n";
	}
}
function fx_repr( $v ) {
	if ( null === $v ) {
		return 'null';
	}
	if ( is_bool( $v ) ) {
		return $v ? 'true' : 'false';
	}
	if ( is_array( $v ) ) {
		return json_encode( $v );
	}
	$s = (string) $v;
	return ( strlen( $s ) > 300 ) ? substr( $s, 0, 300 ) . '…' : $s;
}
function has( $haystack, $needle ) {
	return false !== strpos( $haystack, $needle );
}

/* -------------------------------------------------------------- fixture plumbing */

function fx_tmp_root() {
	$dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/nm audit fx ' . uniqid( '', true );
	if ( ! mkdir( $dir, 0777, true ) && ! is_dir( $dir ) ) {
		fwrite( STDERR, "fx_tmp_root(): mkdir() failed for \"$dir\" -- cannot create a fixture root, aborting.\n" );
		exit( 1 );
	}
	$GLOBALS['fx_roots'][] = $dir;
	return $dir;
}
function fx( $root, $relpath, $content ) {
	$path = $root . '/' . $relpath;
	$dir  = dirname( $path );
	if ( ! is_dir( $dir ) ) {
		mkdir( $dir, 0777, true );
	}
	file_put_contents( $path, $content );
}
function fx_rrmdir( $dir ) {
	if ( is_link( $dir ) ) {
		unlink( $dir );
		return;
	}
	if ( ! is_dir( $dir ) ) {
		return;
	}
	foreach ( scandir( $dir ) as $f ) {
		if ( '.' === $f || '..' === $f ) {
			continue;
		}
		$p = $dir . '/' . $f;
		if ( is_link( $p ) ) {
			unlink( $p );
		} elseif ( is_dir( $p ) ) {
			fx_rrmdir( $p );
		} else {
			unlink( $p );
		}
	}
	rmdir( $dir );
}

/* A conforming skeleton every fixture starts from: CONTRIBUTING.md, one offline test file, the
   qa-review skill + house-rules.md (framework-audit.php checks it by a hardcoded path regardless
   of whether the skill exists), and a fully conforming ux-design-system skill (personality
   catalog, clean design-tokens.md, SKILL.md pointing at both). Without this every fixture would
   carry FAILs unrelated to what it actually tests. */
function fx_base( $root ) {
	fx( $root, 'CONTRIBUTING.md', "# Contributing\nFixture root for framework-audit tests.\n" );
	fx( $root, 'tests/dummy.php', "<?php\n// fixture placeholder, never executed by the audit\n" );
	fx(
		$root,
		'skills/qa-review/SKILL.md',
		"---\n" .
		"name: qa-review\n" .
		"description: \"Trigger: fixture skill.\"\n" .
		"license: MIT\n" .
		"metadata:\n" .
		"  author: fixture\n" .
		"  version: \"1.0\"\n" .
		"---\n\n" .
		"# QA Review Fixture\n\nSee `references/house-rules.md`.\n"
	);
	fx( $root, 'skills/qa-review/references/house-rules.md', "# House Rules\n\nNo rows.\n" );
	fx(
		$root,
		'skills/ux-design-system/SKILL.md',
		"---\n" .
		"name: ux-design-system\n" .
		"description: \"Trigger: fixture skill.\"\n" .
		"license: MIT\n" .
		"metadata:\n" .
		"  author: fixture\n" .
		"  version: \"1.0\"\n" .
		"---\n\n" .
		"# UX Design System Fixture\n\n" .
		"CAPA 1 personalities live in `references/design-personalities.md`; roles stay in\n" .
		"`references/design-tokens.md`. CAPA 2 recommends one personality and confirms it.\n"
	);
	fx(
		$root,
		'skills/ux-design-system/references/design-tokens.md',
		"# Design tokens fixture\n\nSee design-personalities.md for concrete pairings.\n"
	);
	fx( $root, 'skills/ux-design-system/references/design-personalities.md', fx_pers_catalog() );
}

/* One conforming PERS-* block: heading + all 7 required bold-labeled fields. */
function fx_pers_block( $id, $name ) {
	return "### `$id` — $name\n\n" .
		"**Fits:** fixture.\n\n" .
		"**Typography:** fixture.\n\n" .
		"**Color mood:** fixture.\n\n" .
		"**Radius & shadow:** fixture.\n\n" .
		"**Motion intensity:** fixture.\n\n" .
		"**Imagery:** fixture.\n\n" .
		"**Card recipe:** fixture.\n\n";
}
/* Builds a full 8-personality catalog. Pass $skip_id to omit one entirely, or
   $skip_field_for + $skip_field to knock out one required field on one entry — the two knobs
   a defect-fixture needs, without duplicating the whole catalog per scenario. */
function fx_pers_catalog( $skip_field_for = null, $skip_field = null, $skip_id = null ) {
	$ids = array(
		'PERS-EDITORIAL'          => 'Editorial',
		'PERS-BOLD-STARTUP'       => 'Bold Startup',
		'PERS-MINIMAL-SWISS'      => 'Minimal Swiss',
		'PERS-WARM-BOUTIQUE'      => 'Warm Boutique',
		'PERS-CORPORATE-TRUST'    => 'Corporate Trust',
		'PERS-FASHION-EDIT'       => 'Fashion Edit',
		'PERS-TECH-PRECISION'     => 'Tech Precision',
		'PERS-PERFORMANCE-ENERGY' => 'Performance Energy',
	);
	$out = "# Design Personalities Fixture\n\n";
	foreach ( $ids as $id => $name ) {
		if ( $id === $skip_id ) {
			continue;
		}
		$block = fx_pers_block( $id, $name );
		if ( $id === $skip_field_for && $skip_field ) {
			$block = str_replace( '**' . $skip_field . ':**', '**Removed-' . $skip_field . ':**', $block );
		}
		$out .= $block;
	}
	return $out;
}

/* Runs the real audit as a subprocess against $root. Returns [exit_code, combined_output,
   launched]. */
function fx_run( $audit, $root, array $extra_args = array(), $php_binary = null ) {
	$php_binary = ( null === $php_binary ) ? PHP_BINARY : $php_binary;
	if ( '' === $php_binary ) {
		return array( null, 'launch failure: PHP_BINARY is empty -- no CLI PHP binary is available (non-CLI SAPI?)', false );
	}
	if ( ! function_exists( 'exec' ) ) {
		return array( null, 'launch failure: exec() is unavailable -- disabled via disable_functions', false );
	}
	$parts = array( escapeshellarg( $php_binary ), escapeshellarg( $audit ), '--root=' . escapeshellarg( $root ) );
	foreach ( $extra_args as $arg ) {
		$parts[] = escapeshellarg( $arg );
	}
	$last = exec( implode( ' ', $parts ) . ' 2>&1', $out, $code );
	if ( false === $last && array() === $out ) {
		return array( null, 'launch failure: exec() returned false -- the shell never ran the command', false );
	}
	return array( $code, implode( "\n", $out ), true );
}
function fx_run_ok( $audit, $root, array $extra_args = array() ) {
	list( $code, $out, $launched ) = fx_run( $audit, $root, $extra_args );
	ok( $launched, 'the audit subprocess actually launched', $launched ? 'true' : $out );
	return array( $code, $out );
}
function fx_counts( $out ) {
	if ( preg_match( '/(\d+) FAIL \/ (\d+) WARN \/ (\d+) JUDGE/', $out, $m ) ) {
		return array( (int) $m[1], (int) $m[2], (int) $m[3] );
	}
	return array( -1, -1, -1 );
}

/* -------------------------------------------------------------- scenarios */

echo "--- positive control: a fully conforming ux-design-system fixture is 0 FAIL ---\n";
$r1 = fx_tmp_root();
fx_base( $r1 );
list( $code1, $out1 ) = fx_run_ok( $audit, $r1 );
list( $f1, , ) = fx_counts( $out1 );
ok( 0 === $code1, 'exit code 0 on a conforming fixture', $code1 );
ok( 0 === $f1, 'zero FAIL rows reported', $out1 );
fx_rrmdir( $r1 );

echo "--- ux-design-system: missing design-personalities.md FAILs ---\n";
$r2 = fx_tmp_root();
fx_base( $r2 );
unlink( $r2 . '/skills/ux-design-system/references/design-personalities.md' );
list( $code2, $out2 ) = fx_run_ok( $audit, $r2 );
ok( has( $out2, 'references/design-personalities.md is missing' ), 'a missing catalog file FAILs with the right message', $out2 );
fx_rrmdir( $r2 );

echo "--- ux-design-system: catalog missing a required personality FAILs ---\n";
$r3 = fx_tmp_root();
fx_base( $r3 );
fx( $r3, 'skills/ux-design-system/references/design-personalities.md', fx_pers_catalog( null, null, 'PERS-TECH-PRECISION' ) );
list( $code3, $out3 ) = fx_run_ok( $audit, $r3 );
ok( has( $out3, 'is missing personality "PERS-TECH-PRECISION"' ), 'a missing PERS id is named in the FAIL', $out3 );
fx_rrmdir( $r3 );

echo "--- ux-design-system: a personality missing a required field FAILs ---\n";
$r4 = fx_tmp_root();
fx_base( $r4 );
fx( $r4, 'skills/ux-design-system/references/design-personalities.md', fx_pers_catalog( 'PERS-EDITORIAL', 'Motion intensity' ) );
list( $code4, $out4 ) = fx_run_ok( $audit, $r4 );
ok( has( $out4, 'PERS-EDITORIAL is missing required field "Motion intensity"' ), 'a personality missing one field is named precisely', $out4 );
fx_rrmdir( $r4 );

echo "--- ux-design-system: design-tokens.md hardcoding a single font example FAILs ---\n";
$r5 = fx_tmp_root();
fx_base( $r5 );
fx( $r5, 'skills/ux-design-system/references/design-tokens.md', "# Design tokens fixture\n\nUse Space Grotesk for headings.\n" );
list( $code5, $out5 ) = fx_run_ok( $audit, $r5 );
ok( has( $out5, 'still hardcodes "Space Grotesk"' ), 'a hardcoded font example FAILs', $out5 );
fx_rrmdir( $r5 );

echo "--- ux-design-system: SKILL.md missing the catalog pointer or CAPA 2 step FAILs ---\n";
$r6 = fx_tmp_root();
fx_base( $r6 );
fx(
	$r6,
	'skills/ux-design-system/SKILL.md',
	"---\nname: ux-design-system\ndescription: \"Trigger: fixture skill.\"\nlicense: MIT\nmetadata:\n  author: fixture\n  version: \"1.0\"\n---\n\nFixture skill, no personality step.\n"
);
list( $code6, $out6 ) = fx_run_ok( $audit, $r6 );
ok( has( $out6, 'SKILL.md never mentions design-personalities.md' ), 'no pointer to the catalog FAILs', $out6 );
ok( has( $out6, 'SKILL.md has no CAPA 2 recommender step' ), 'no CAPA 2 step FAILs', $out6 );
fx_rrmdir( $r6 );

/* -------------------------------------------------------------- report */

printf( "\n%d OK / %d FAIL\n", $pass, $fail );
exit( $fail ? 1 : 0 );
