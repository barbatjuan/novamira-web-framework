<?php
/**
 * Regression harness for framework-audit.php itself — the audit's own audit.
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
/* Every root fx_tmp_root() creates is swept here too, so a fatal error or interrupt mid-scenario
   never orphans a temp directory — teardown no longer depends on linear fallthrough alone. */
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
/* Stringifies an assertion's actual value for a FAIL line, so "exit code 0 on a conforming
   fixture" failing says what the exit code actually WAS, not just that it wasn't 0. */
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

/* A temp root with a space in its name — proves the subprocess argument quoting survives the
   one Windows/escapeshellarg edge case that silently breaks naive `exec()` calls. */
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
	/* is_link() BEFORE is_dir(): both is_dir() and scandir() follow symlinks, so recursing on
	   is_dir() alone would walk through a symlink into a directory this fixture never created —
	   a predictable-name-under-sys_get_temp_dir() TOCTOU shape. A symlink is unlinked, never
	   traversed. */
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

/* A conforming skeleton every fixture starts from: one skill (qa-review, which the audit checks
   by a HARDCODED path regardless of whether the skill exists) plus its house-rules file, an
   offline test file, and CONTRIBUTING.md. Without this every fixture would carry three FAILs
   that have nothing to do with what it actually tests. */
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
}

/* Runs the real audit as a subprocess against $root. Returns [exit_code, combined_output,
   launched]. $launched distinguishes two different bugs that used to collapse into the same
   fx_counts() sentinel: a SITE defect (the audit ran and printed something unparseable) versus a
   TOOLING gap (the audit never ran at all — no CLI PHP binary under a non-CLI SAPI, exec()
   disabled, or the shell failed to launch the command). $php_binary is injectable so this
   distinction is itself testable — see the launch-failure scenario below. */
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
/* Every scenario except the launch-failure one below calls this instead of fx_run() directly, so
   a silent tooling gap can never be misread as "the audit ran and disagreed". */
function fx_run_ok( $audit, $root, array $extra_args = array() ) {
	list( $code, $out, $launched ) = fx_run( $audit, $root, $extra_args );
	ok( $launched, 'the audit subprocess actually launched', $launched ? 'true' : $out );
	return array( $code, $out );
}
/* Parses the summary line printed by the printf() in framework-audit.php's report section (see
   the matching comment there) -- the two sides of this contract live in different files and must
   be kept in sync by hand. Callers only reach this after fx_run_ok()/fx_run() has already
   confirmed $launched === true, so the (-1,-1,-1) sentinel here means only "ran, but printed
   something this regex cannot parse" -- never "never ran" (see fx_run() above). */
function fx_counts( $out ) {
	if ( preg_match( '/(\d+) FAIL \/ (\d+) WARN \/ (\d+) JUDGE/', $out, $m ) ) {
		return array( (int) $m[1], (int) $m[2], (int) $m[3] );
	}
	return array( -1, -1, -1 );
}

/* -------------------------------------------------------------- scenarios */

echo "--- positive control: a fully conforming fixture is 0 FAIL ---\n";
$r1 = fx_tmp_root();
ok( false !== strpos( $r1, ' ' ), 'the fixture temp path itself contains a space', $r1 );
fx_base( $r1 );
list( $code1, $out1 ) = fx_run_ok( $audit, $r1 );
list( $f1, , ) = fx_counts( $out1 );
ok( 0 === $code1, 'exit code 0 on a conforming fixture', $code1 );
ok( 0 === $f1, 'zero FAIL rows reported', $f1 );
fx_rrmdir( $r1 );

echo "--- routing to a missing skill FAILs (was a silent tautology) ---\n";
$r2 = fx_tmp_root();
fx_base( $r2 );
fx(
	$r2,
	'agents/orchestrator.md',
	"---\nname: orchestrator\n---\n\n## House rules\n\nRoutes to `phantom-skill` for commerce.\n"
);
list( $code2, $out2 ) = fx_run_ok( $audit, $r2 );
list( $f2, , ) = fx_counts( $out2 );
ok( 1 === $code2, 'exit code 1 when a routed skill is missing', $code2 );
ok( 1 === $f2, 'exactly one FAIL row reported', $f2 );
ok( has( $out2, 'routes to skill "phantom-skill" which is missing' ), 'the FAIL names the missing skill', $out2 );
fx_rrmdir( $r2 );

echo "--- write-capability is detected from assets/*.php content, not SKILL.md prose ---\n";
$r3 = fx_tmp_root();
fx_base( $r3 );
fx(
	$r3,
	'skills/sample-writer/SKILL.md',
	"---\nname: sample-writer\ndescription: \"Trigger: fixture.\"\nlicense: MIT\nmetadata:\n  author: fixture\n  version: \"1.0\"\n---\n\n" .
	"Build gate: requires explicit **yes** before writing (fixture claims a gate it should not have).\n"
);
fx(
	$r3,
	'skills/sample-writer/assets/tool.php',
	"<?php\nfunction do_write( \$id ) {\n\tupdate_post_meta( \$id, 'x', 'y' );\n}\n"
);
fx(
	$r3,
	'skills/sample-mentioner/SKILL.md',
	"---\nname: sample-mentioner\ndescription: \"Trigger: fixture.\"\nlicense: MIT\nmetadata:\n  author: fixture\n  version: \"1.0\"\n---\n\nFixture skill, no writes.\n"
);
fx(
	$r3,
	'skills/sample-mentioner/assets/note.php',
	"<?php\n/**\n * Historically this asset called update_post_meta( \$id, 'x', 'y' ) directly; now it delegates.\n */\nfunction noop() { return true; }\n"
);
list( $code3, $out3 ) = fx_run_ok( $audit, $r3 );
ok( has( $out3, 'sample-writer' ) && has( $out3, 'writes to WordPress (tool.php:3)' ), 'unlisted skill with a real assets/*.php write call is flagged, with file:line' );
ok( has( $out3, 'not in the write-capable list' ), 'the FAIL explains the skill is missing from $WRITE_CAPABLE' );
$mentioner_lines = array_filter(
	explode( "\n", $out3 ),
	function ( $l ) {
		return false !== strpos( $l, 'sample-mentioner' );
	}
);
$mentioner_flagged = false;
foreach ( $mentioner_lines as $l ) {
	if ( false !== strpos( $l, 'writes to WordPress' ) ) {
		$mentioner_flagged = true;
	}
}
ok( ! $mentioner_flagged, 'a comment-only mention of the same token produces no write-capability row' );
fx_rrmdir( $r3 );

echo "--- the audit does not self-flag through its own detection-token literal ---\n";
/* Pairs with the positive case above: together they pin the call-shape rule (\s*\() that stands
   between this file's own detection-regex literal and self-flagging when the assets glob reaches
   skills/framework-audit/assets/framework-audit.php in a real repo scan. */
$r4 = fx_tmp_root();
fx_base( $r4 );
fx(
	$r4,
	'skills/sample-selfcheck/SKILL.md',
	"---\nname: sample-selfcheck\ndescription: \"Trigger: fixture.\"\nlicense: MIT\nmetadata:\n  author: fixture\n  version: \"1.0\"\n---\n\nFixture skill, no writes.\n"
);
fx(
	$r4,
	'skills/sample-selfcheck/assets/detector.php',
	<<<EOT
<?php
/* Same token list as framework-audit.php's own literal: "|"-separated, never followed by "(". */
\$pattern = '/\b(update_post_meta|wp_insert_post|wp_update_post|update_option|es_save_page|es_save_theme_part)\s*\(/';
function noop() { return \$pattern; }
EOT
);
list( $code4, $out4 ) = fx_run_ok( $audit, $r4 );
$selfcheck_rows = array_filter(
	explode( "\n", $out4 ),
	function ( $l ) {
		return has( $l, 'sample-selfcheck' ) && has( $l, 'writes to WordPress' );
	}
);
ok( 0 === count( $selfcheck_rows ), 'a token list written the same way the audit writes its own regex literal produces no write-capability row', $out4 );
fx_rrmdir( $r4 );

echo "--- write-capability from SKILL.md PROSE alone is never flagged ---\n";
/* The comment-only-mention assertion above pins the call-shape rule INSIDE assets/*.php; it never
   tested this other half of the same claim. A regression that resurrects a
   strpos($src, 'update_post_meta') check on SKILL.md text -- the exact bug this slice fixed --
   would pass every existing assertion undetected. */
$r5 = fx_tmp_root();
fx_base( $r5 );
fx(
	$r5,
	'skills/sample-prose-writer/SKILL.md',
	"---\nname: sample-prose-writer\ndescription: \"Trigger: fixture.\"\nlicense: MIT\nmetadata:\n  author: fixture\n  version: \"1.0\"\n---\n\n" .
	"Discusses update_post_meta() and wp_insert_post() at length; nothing under assets/ calls either.\n"
);
fx( $r5, 'skills/sample-prose-writer/assets/reader.php', "<?php\nfunction read_only() { return true; }\n" );
list( $code5, $out5 ) = fx_run_ok( $audit, $r5 );
$prose_rows = array_filter(
	explode( "\n", $out5 ),
	function ( $l ) {
		return has( $l, 'sample-prose-writer' ) && has( $l, 'writes to WordPress' );
	}
);
ok( 0 === count( $prose_rows ), 'a SKILL.md whose PROSE names write tokens, with no calling assets, produces no write-capability row', $out5 );
fx_rrmdir( $r5 );

echo "--- a \$WRITE_CAPABLE skill with no PHP assets still needs a build gate ---\n";
/* divi-core, wordpress-seo and wordpress-performance ship no assets/*.php at all, so the
   content-based detector above can never catch them missing a gate -- this in_array($WRITE_CAPABLE)
   branch is the ONLY enforcement path for those three. */
$r6 = fx_tmp_root();
fx_base( $r6 );
fx(
	$r6,
	'skills/wordpress-seo/SKILL.md',
	"---\nname: wordpress-seo\ndescription: \"Trigger: fixture.\"\nlicense: MIT\nmetadata:\n  author: fixture\n  version: \"1.0\"\n---\n\nFixture write-capable skill: no PHP assets, no build gate.\n"
);
list( $code6, $out6 ) = fx_run_ok( $audit, $r6 );
$gate_missing_rows = array_filter(
	explode( "\n", $out6 ),
	function ( $l ) {
		return has( $l, 'wordpress-seo' ) && has( $l, 'WRITE-CAPABLE SKILL WITH NO BLOCKING BUILD GATE' );
	}
);
ok( count( $gate_missing_rows ) > 0, 'a $WRITE_CAPABLE skill with no assets/*.php and no gate text still FAILs', $out6 );
fx_rrmdir( $r6 );

$r7 = fx_tmp_root();
fx_base( $r7 );
fx(
	$r7,
	'skills/wordpress-seo/SKILL.md',
	"---\nname: wordpress-seo\ndescription: \"Trigger: fixture.\"\nlicense: MIT\nmetadata:\n  author: fixture\n  version: \"1.0\"\n---\n\nBuild gate: requires explicit **yes** before writing.\n"
);
list( $code7, $out7 ) = fx_run_ok( $audit, $r7 );
$gate_present_rows = array_filter(
	explode( "\n", $out7 ),
	function ( $l ) {
		return has( $l, 'wordpress-seo' ) && has( $l, 'NO BLOCKING BUILD GATE' );
	}
);
ok( 0 === count( $gate_present_rows ), 'the same skill WITH gate text produces no such row', $out7 );
fx_rrmdir( $r7 );

echo "--- missing CONTRIBUTING.md is refused as an install directory ---\n";
$r8 = fx_tmp_root();
mkdir( $r8 . '/skills', 0777, true );
list( $code8, $out8 ) = fx_run_ok( $audit, $r8 );
ok( 2 === $code8, 'exit code 2 when CONTRIBUTING.md is absent', $code8 );
ok( has( $out8, 'CONTRIBUTING.md' ), 'the message names CONTRIBUTING.md', $out8 );
fx_rrmdir( $r8 );

echo "--- --strict promotes a WARN-only run to exit 1 ---\n";
$r9 = fx_tmp_root();
fx_base( $r9 );
list( $code9n, $out9n ) = fx_run_ok( $audit, $r9 );
list( $code9s, ) = fx_run_ok( $audit, $r9, array( '--strict' ) );
list( $f9, $w9, ) = fx_counts( $out9n );
ok( 0 === $f9 && $w9 > 0, 'the base fixture carries a real WARN and no FAIL', json_encode( array( $f9, $w9 ) ) );
ok( 0 === $code9n, 'exit code 0 without --strict despite the WARN', $code9n );
ok( 1 === $code9s, '--strict turns that WARN into exit 1', $code9s );
fx_rrmdir( $r9 );

echo "--- fx_run() distinguishes a launch failure from a parse failure ---\n";
list( $codeL, $msgL, $launchedL ) = fx_run( $audit, '/does/not/matter', array(), '' );
ok( false === $launchedL, 'launched=false when no CLI PHP binary is configured', var_export( $launchedL, true ) );
ok( null === $codeL, 'a launch failure reports no exit code', $codeL );
ok( has( $msgL, 'PHP_BINARY' ), 'the message names PHP_BINARY as the reason', $msgL );
$parse_counts = fx_counts( 'summary line missing from this output' );
ok( array( -1, -1, -1 ) === $parse_counts, 'fx_counts() keeps a distinct sentinel for "ran, unparseable" vs a launch failure', json_encode( $parse_counts ) );

/* The gate self-registration check is the one verifier this change adds, and nothing here
   exercised it: every fixture wrote tests/dummy.php, which the `test-*.php` glob deliberately
   misses, so the loop body never ran once and deleting the whole block left this suite green at
   32 OK. A gate reporting success on work nothing inspected is the exact disease this change
   exists to cure — finding it inside the cure is why both branches are pinned here. */
echo "--- a tests/test-*.php absent from the gate line FAILs, and joining the line clears it ---\n";
$r10 = fx_tmp_root();
fx_base( $r10 );
fx( $r10, 'tests/test-example.php', "<?php\n// fixture test file, never executed by the audit\n" );

list( , $out10a ) = fx_run_ok( $audit, $r10 );
$unregistered = array_filter(
	explode( "\n", $out10a ),
	function ( $l ) {
		return has( $l, 'gate line never runs' ) && has( $l, 'test-example.php' );
	}
);
ok( 1 === count( $unregistered ), 'an unregistered tests/test-*.php raises exactly one gate-line FAIL', $out10a );

fx( $r10, 'CONTRIBUTING.md', "# Contributing\n\nRun `php tests/test-example.php` before every commit.\n" );
list( , $out10b ) = fx_run_ok( $audit, $r10 );
$registered = array_filter(
	explode( "\n", $out10b ),
	function ( $l ) {
		return has( $l, 'gate line never runs' );
	}
);
ok( 0 === count( $registered ), 'naming it on the gate line clears the row', $out10b );
fx_rrmdir( $r10 );

/* -------------------------------------------------------------- report */

printf( "\n%d OK / %d FAIL\n", $pass, $fail );
exit( $fail ? 1 : 0 );
