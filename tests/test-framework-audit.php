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

/* Ratchet, not an escape hatch: every entry needs a written reason right in this comment, and the
   ceiling below (ok() near the bottom of this file) keeps the list from growing to fit reality
   instead of reality getting a fixture. Empty at B0 — every row type ROW_TYPES declares gets a
   real fixture that emits it; none is waived. */
const COVERAGE_EXEMPT = array();

$pass = 0;
$fail = 0;
/* Accumulates every row-type ID any fixture below actually caused the audit to print (via
   --row-types, injected by fx_run_ok() for every scenario). This is the "observed" half of the
   coverage assertion near the end of this file — see fx_track_ids(). */
$GLOBALS['fx_observed_ids'] = array();
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
/* Every row-type ID this run of the audit EMITTED, regardless of which scenario caused it.
 *
 * Anchored to the ID COLUMN — line start, then whitespace — and not to the line anywhere. That
 * anchor is the whole correctness of the coverage assertion, and it was learned the hard way: an
 * unanchored scan credited an ID as observed when it merely appeared inside another row's MESSAGE,
 * and RT_ROWTYPE_UNDOCUMENTED's message names the ID it is complaining about. So one fixture that
 * forgot a row-type bullet marked the entire registry covered, and a row type could be deleted
 * outright with the suite still green — the coverage ratchet reporting success over work nothing
 * inspected, which is the exact disease it exists to cure. A message can carry an ID-shaped token
 * (a broken-reference row echoes an audited path verbatim, and a path may be named anything);
 * only the emitter can put one at line start. */
function fx_track_ids( $out ) {
	if ( preg_match_all( '/^(RT_[A-Z0-9_]+)\s/m', $out, $m ) ) {
		foreach ( $m[1] as $id ) {
			$GLOBALS['fx_observed_ids'][ $id ] = true;
		}
	}
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

/* Every declared row-type ID, one bullet per line — read by fx_base() (and any fixture that
   writes its own CONTRIBUTING.md) so a fixture's CONTRIBUTING.md always documents every row type
   the real one does. Pulled from the audit's OWN --emit-row-types output ($declared_row_types,
   fetched once before any fixture runs), never hand-copied, so this list cannot drift from
   ROW_TYPES the way two independent implementations of one rule always eventually do. */
function fx_row_type_doc() {
	global $declared_row_types;
	$lines = '';
	foreach ( $declared_row_types as $rt_id ) {
		$lines .= "- $rt_id\n";
	}
	return "## Row types\n" . $lines;
}

/* A conforming skeleton every fixture starts from: one skill (qa-review, which the audit checks
   by a HARDCODED path regardless of whether the skill exists) plus its house-rules file, an
   offline test file, and CONTRIBUTING.md documenting every row type. Without this every fixture
   would carry FAILs that have nothing to do with what it actually tests. */
function fx_base( $root ) {
	fx( $root, 'CONTRIBUTING.md', "# Contributing\nFixture root for framework-audit tests.\n\n" . fx_row_type_doc() );
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
   a silent tooling gap can never be misread as "the audit ran and disagreed". Every call also
   requests --row-types (unless a caller already did), and every launch feeds fx_track_ids() — so
   EVERY existing and new scenario's run counts toward the coverage assertion for free, with no
   per-scenario opt-in to forget. The human-readable message text is untouched either way; the ID
   column is additive, so every has($out, '...') assertion written before this slice keeps working
   unmodified — proven by the full suite staying green after this change landed. */
function fx_run_ok( $audit, $root, array $extra_args = array() ) {
	if ( ! in_array( '--row-types', $extra_args, true ) ) {
		$extra_args[] = '--row-types';
	}
	list( $code, $out, $launched ) = fx_run( $audit, $root, $extra_args );
	ok( $launched, 'the audit subprocess actually launched', $launched ? 'true' : $out );
	if ( $launched ) {
		fx_track_ids( $out );
	}
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

/* --emit-row-types is static introspection of the script — it needs no --root and prints exit 0
   before the tree-walk even starts, so it is fetched once here rather than per-scenario. This IS
   the "declared" half of the coverage assertion: the registry the audit itself carries, not a
   second hand-typed copy in this file that could silently drift from it. */
list( , $declared_out ) = fx_run( $audit, '', array( '--emit-row-types' ) );
preg_match_all( '/\bRT_[A-Z0-9_]+\b/', $declared_out, $declared_m );
$declared_row_types = array_values( array_unique( $declared_m[0] ) );
sort( $declared_row_types );

/* -------------------------------------------------------------- scenarios */

echo "--- --row-types and the unflagged default report the identical summary line ---\n";
/* The per-row format may grow an ID column under the flag; the count line every other assertion
   in this file (and every human running the audit unflagged) reads must never move because of it. */
$r0 = fx_tmp_root();
fx_base( $r0 );
list( , $out0_flagged )   = fx_run_ok( $audit, $r0, array( '--row-types' ) );
list( , $out0_unflagged ) = fx_run( $audit, $r0, array() );
preg_match( '/\d+ FAIL \/ \d+ WARN \/ \d+ JUDGE across \d+ skills \+ \d+ agent\(s\)/', $out0_flagged, $sm_flagged );
preg_match( '/\d+ FAIL \/ \d+ WARN \/ \d+ JUDGE across \d+ skills \+ \d+ agent\(s\)/', $out0_unflagged, $sm_unflagged );
ok(
	isset( $sm_flagged[0] ) && isset( $sm_unflagged[0] ) && $sm_flagged[0] === $sm_unflagged[0],
	'--row-types and the unflagged run produce the identical counts line',
	array( 'flagged' => isset( $sm_flagged[0] ) ? $sm_flagged[0] : null, 'unflagged' => isset( $sm_unflagged[0] ) ? $sm_unflagged[0] : null )
);
fx_rrmdir( $r0 );

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

fx( $r10, 'CONTRIBUTING.md', "# Contributing\n\nRun `php tests/test-example.php` before every commit.\n\n" . fx_row_type_doc() );
list( , $out10b ) = fx_run_ok( $audit, $r10 );
$registered = array_filter(
	explode( "\n", $out10b ),
	function ( $l ) {
		return has( $l, 'gate line never runs' );
	}
);
ok( 0 === count( $registered ), 'naming it on the gate line clears the row', $out10b );
fx_rrmdir( $r10 );

/* -------------------------------------------------- row-type retrofit fixtures (B0)
 *
 * Every scenario above already exercises 7 of the 21 row types framework-audit.php could print
 * before this slice; these 13 cover the other 14 (one, r16, deliberately covers two: a skill body
 * past 600 words and a separate skill body past 300). Each is the smallest tree that makes ONE
 * check fire, so a future regression in any single check has exactly one fixture pointing at it. */

echo "--- a skill directory with no SKILL.md is RT_NO_SKILL_MD ---\n";
$r11 = fx_tmp_root();
fx_base( $r11 );
fx( $r11, 'skills/sample-empty/.keep', "placeholder, never read by the audit\n" );
list( , $out11 ) = fx_run_ok( $audit, $r11 );
ok( has( $out11, 'RT_NO_SKILL_MD' ), 'a skill directory with no SKILL.md is RT_NO_SKILL_MD', $out11 );
fx_rrmdir( $r11 );

echo "--- a SKILL.md with no YAML frontmatter block is RT_NO_FRONTMATTER ---\n";
$r12 = fx_tmp_root();
fx_base( $r12 );
fx( $r12, 'skills/sample-nofrontmatter/SKILL.md', "# No frontmatter here\n\nJust prose, no --- block.\n" );
list( , $out12 ) = fx_run_ok( $audit, $r12 );
ok( has( $out12, 'RT_NO_FRONTMATTER' ), 'a SKILL.md with no frontmatter block is RT_NO_FRONTMATTER', $out12 );
fx_rrmdir( $r12 );

echo "--- frontmatter missing a required key is RT_FRONTMATTER_MISSING_KEY ---\n";
$r13 = fx_tmp_root();
fx_base( $r13 );
fx(
	$r13,
	'skills/sample-missingkey/SKILL.md',
	"---\nname: sample-missingkey\ndescription: \"Trigger: fixture.\"\nmetadata:\n  author: fixture\n  version: \"1.0\"\n---\n\nFixture skill, license key deliberately omitted.\n"
);
list( , $out13 ) = fx_run_ok( $audit, $r13 );
ok( has( $out13, 'RT_FRONTMATTER_MISSING_KEY' ) && has( $out13, 'license' ), 'frontmatter missing "license" is RT_FRONTMATTER_MISSING_KEY', $out13 );
fx_rrmdir( $r13 );

echo "--- frontmatter name: not matching its directory is RT_NAME_MISMATCH ---\n";
$r14 = fx_tmp_root();
fx_base( $r14 );
fx(
	$r14,
	'skills/sample-namemismatch/SKILL.md',
	"---\nname: something-else\ndescription: \"Trigger: fixture.\"\nlicense: MIT\nmetadata:\n  author: fixture\n  version: \"1.0\"\n---\n\nFixture skill.\n"
);
list( , $out14 ) = fx_run_ok( $audit, $r14 );
ok( has( $out14, 'RT_NAME_MISMATCH' ), 'frontmatter name: not matching the directory is RT_NAME_MISMATCH', $out14 );
fx_rrmdir( $r14 );

echo "--- a description with no \"Trigger:\" words is RT_NO_TRIGGER ---\n";
$r15 = fx_tmp_root();
fx_base( $r15 );
fx(
	$r15,
	'skills/sample-notrigger/SKILL.md',
	"---\nname: sample-notrigger\ndescription: \"Fixture skill, no trigger words at all.\"\nlicense: MIT\nmetadata:\n  author: fixture\n  version: \"1.0\"\n---\n\nFixture skill.\n"
);
list( , $out15 ) = fx_run_ok( $audit, $r15 );
ok( has( $out15, 'RT_NO_TRIGGER' ), 'a description with no "Trigger:" words is RT_NO_TRIGGER', $out15 );
fx_rrmdir( $r15 );

echo "--- SKILL.md body word counts past 600 and past 300 are RT_BODY_OVER_600 / RT_BODY_OVER_300 ---\n";
$r16 = fx_tmp_root();
fx_base( $r16 );
fx(
	$r16,
	'skills/sample-bodyover600/SKILL.md',
	"---\nname: sample-bodyover600\ndescription: \"Trigger: fixture.\"\nlicense: MIT\nmetadata:\n  author: fixture\n  version: \"1.0\"\n---\n\n"
	. implode( ' ', array_fill( 0, 650, 'word' ) ) . "\n"
);
fx(
	$r16,
	'skills/sample-bodyover300/SKILL.md',
	"---\nname: sample-bodyover300\ndescription: \"Trigger: fixture.\"\nlicense: MIT\nmetadata:\n  author: fixture\n  version: \"1.0\"\n---\n\n"
	. implode( ' ', array_fill( 0, 350, 'word' ) ) . "\n"
);
list( , $out16 ) = fx_run_ok( $audit, $r16 );
ok( has( $out16, 'RT_BODY_OVER_600' ), 'a 650-word SKILL.md body is RT_BODY_OVER_600', $out16 );
ok( has( $out16, 'RT_BODY_OVER_300' ), 'a 350-word SKILL.md body is RT_BODY_OVER_300', $out16 );
fx_rrmdir( $r16 );

echo "--- a SKILL.md pointer to a nonexistent references/ path is RT_BROKEN_REFERENCE ---\n";
$r17 = fx_tmp_root();
fx_base( $r17 );
fx(
	$r17,
	'skills/sample-brokenref/SKILL.md',
	"---\nname: sample-brokenref\ndescription: \"Trigger: fixture.\"\nlicense: MIT\nmetadata:\n  author: fixture\n  version: \"1.0\"\n---\n\nSee `references/missing.md` for details.\n"
);
list( , $out17 ) = fx_run_ok( $audit, $r17 );
ok( has( $out17, 'RT_BROKEN_REFERENCE' ), 'a SKILL.md pointer to a nonexistent references/ path is RT_BROKEN_REFERENCE', $out17 );
fx_rrmdir( $r17 );

echo "--- a Hard Rule naming no verifier at all is RT_HARD_RULE_NO_VERIFIER ---\n";
$r18 = fx_tmp_root();
fx_base( $r18 );
fx(
	$r18,
	'skills/elementor-core/SKILL.md',
	"---\nname: elementor-core\ndescription: \"Trigger: fixture.\"\nlicense: MIT\nmetadata:\n  author: fixture\n  version: \"1.0\"\n---\n\n"
	. "Build gate: requires explicit **yes** before writing.\n\n"
	. "## Hard Rules\n- Keep components small and focused on one job.\n"
);
list( , $out18 ) = fx_run_ok( $audit, $r18 );
ok( has( $out18, 'RT_HARD_RULE_NO_VERIFIER' ), 'a Hard Rule bullet naming no verifier keyword is RT_HARD_RULE_NO_VERIFIER', $out18 );
fx_rrmdir( $r18 );

echo "--- error_log() with no paired stdout channel is RT_ERRORLOG_NO_STDOUT ---\n";
$r19 = fx_tmp_root();
fx_base( $r19 );
fx(
	$r19,
	'skills/sample-errorlog/SKILL.md',
	"---\nname: sample-errorlog\ndescription: \"Trigger: fixture.\"\nlicense: MIT\nmetadata:\n  author: fixture\n  version: \"1.0\"\n---\n\nSee `assets/broken.php`.\n"
);
fx(
	$r19,
	'skills/sample-errorlog/assets/broken.php',
	"<?php\nfunction silent_fail( \$e ) {\n\terror_log( \$e );\n\treturn null;\n}\n"
);
list( , $out19 ) = fx_run_ok( $audit, $r19 );
ok( has( $out19, 'RT_ERRORLOG_NO_STDOUT' ), 'error_log() with no paired stdout channel nearby is RT_ERRORLOG_NO_STDOUT', $out19 );
fx_rrmdir( $r19 );

echo "--- an agent markdown file with a code block is RT_AGENT_CODE_BLOCK ---\n";
$r20 = fx_tmp_root();
fx_base( $r20 );
fx( $r20, 'agents/orchestrator.md', "---\nname: orchestrator\n---\n\n## House rules\n\n```php\necho 'nope';\n```\n" );
list( , $out20 ) = fx_run_ok( $audit, $r20 );
ok( has( $out20, 'RT_AGENT_CODE_BLOCK' ), 'an agent markdown file with a code block is RT_AGENT_CODE_BLOCK', $out20 );
fx_rrmdir( $r20 );

echo "--- a house-rules.md row with no verdict source in its last column is RT_HOUSERULES_NO_VERDICT ---\n";
$r21 = fx_tmp_root();
fx_base( $r21 );
fx( $r21, 'skills/qa-review/references/house-rules.md', "# House Rules\n\n| 1 | Some rule | plain text, no bold verdict source |\n" );
list( , $out21 ) = fx_run_ok( $audit, $r21 );
ok( has( $out21, 'RT_HOUSERULES_NO_VERDICT' ), 'a house-rules.md row with no bold verdict source is RT_HOUSERULES_NO_VERDICT', $out21 );
fx_rrmdir( $r21 );

echo "--- a missing qa-review/references/house-rules.md is RT_HOUSERULES_MISSING ---\n";
$r22 = fx_tmp_root();
fx_base( $r22 );
unlink( $r22 . '/skills/qa-review/references/house-rules.md' );
list( , $out22 ) = fx_run_ok( $audit, $r22 );
ok( has( $out22, 'RT_HOUSERULES_MISSING' ), 'a missing qa-review/references/house-rules.md is RT_HOUSERULES_MISSING', $out22 );
fx_rrmdir( $r22 );

echo "--- a tree with no tests/*.php at all is RT_NO_OFFLINE_TESTS ---\n";
$r23 = fx_tmp_root();
fx( $r23, 'CONTRIBUTING.md', "# Contributing\nFixture root, deliberately no tests/*.php anywhere.\n\n" . fx_row_type_doc() );
fx(
	$r23,
	'skills/qa-review/SKILL.md',
	"---\nname: qa-review\ndescription: \"Trigger: fixture skill.\"\nlicense: MIT\nmetadata:\n  author: fixture\n  version: \"1.0\"\n---\n\n# QA Review Fixture\n\nSee `references/house-rules.md`.\n"
);
fx( $r23, 'skills/qa-review/references/house-rules.md', "# House Rules\n\nNo rows.\n" );
list( , $out23 ) = fx_run_ok( $audit, $r23 );
ok( has( $out23, 'RT_NO_OFFLINE_TESTS' ), 'a tree with no tests/*.php at all is RT_NO_OFFLINE_TESTS', $out23 );
fx_rrmdir( $r23 );

/* Mirrors r10's gate-self-registration pair exactly (D1'.7 says this check is the same shape).
   Omitting one ID from CONTRIBUTING.md's row-type table must FAIL naming that exact ID; restoring
   it must clear the row. This is also the mutation the coverage assertion below depends on never
   going stale: RT_ROWTYPE_UNDOCUMENTED is itself declared in ROW_TYPES, so IT needs a fixture too
   — the same requirement it enforces on every other row type, applied to itself. */
echo "--- a ROW_TYPES ID undocumented in CONTRIBUTING.md is RT_ROWTYPE_UNDOCUMENTED, and documenting it clears the row ---\n";
$r24 = fx_tmp_root();
fx_base( $r24 );
/* The preamble text must NOT name the omitted ID itself — strpos() cannot tell "documented in the
   table" from "mentioned in prose describing this fixture", and the first draft of this fixture
   self-sabotaged exactly that way: writing the ID's name in the description satisfied the check
   even with its bullet removed. */
$omitted_doc = str_replace( "- RT_NO_SKILL_MD\n", '', fx_row_type_doc() );
ok( false === strpos( $omitted_doc, 'RT_NO_SKILL_MD' ), 'the omitted-ID fixture doc really omits RT_NO_SKILL_MD', $omitted_doc );
fx( $r24, 'CONTRIBUTING.md', "# Contributing\nFixture root, one row type deliberately left off the table below.\n\n" . $omitted_doc );
list( , $out24a ) = fx_run_ok( $audit, $r24 );
ok( has( $out24a, 'RT_ROWTYPE_UNDOCUMENTED' ) && has( $out24a, 'RT_NO_SKILL_MD' ), 'omitting one ID from the table raises RT_ROWTYPE_UNDOCUMENTED naming it', $out24a );

fx( $r24, 'CONTRIBUTING.md', "# Contributing\nFixture root, all IDs documented.\n\n" . fx_row_type_doc() );
list( , $out24b ) = fx_run_ok( $audit, $r24 );
ok( ! has( $out24b, 'RT_ROWTYPE_UNDOCUMENTED' ), 'documenting every ID clears the row', $out24b );
fx_rrmdir( $r24 );

/* The registry's OTHER guarantee: add() refuses an ID that ROW_TYPES does not declare, loudly,
 * rather than shipping a row nothing registered. The coverage assertion above can never reach this
 * one — the guard's whole purpose is to stop the process BEFORE anything is printed, so an
 * emitted-ID census is structurally blind to it. It needs its own fixture or it is another check
 * with no verifier, which is the shape that sank two prior slices of this change.
 *
 * The IDs are literals in the audit's source, so no synthetic tree can provoke this: the fixture
 * mutates a COPY of the audit instead, and the copy lives in a temp root that gets swept like any
 * other. */
echo "--- add() refuses an unregistered row-type ID, loudly ---\n";
$r25       = fx_tmp_root();
fx_base( $r25 );
$audit_src = (string) file_get_contents( $audit );
fx( $r25, 'mutant-audit.php', str_replace( "\$rows = array();", "\$rows = array();\nadd( 'RT_NOT_IN_THE_REGISTRY', 'FAIL', 'mutation', 'this ID was never declared' );", $audit_src ) );
ok( has( (string) file_get_contents( $r25 . '/mutant-audit.php' ), 'RT_NOT_IN_THE_REGISTRY' ), 'the mutant copy really carries the unregistered call', 'injection failed' );
list( $code25, $out25 ) = fx_run_ok( $r25 . '/mutant-audit.php', $r25 );
ok( 3 === $code25, 'an unregistered row-type ID exits 3, not 0 and not 1', $code25 );
ok( has( $out25, 'RT_NOT_IN_THE_REGISTRY' ), 'the message names the offending ID', $out25 );
ok( has( $out25, 'ROW_TYPES' ), 'the message says where to register it', $out25 );
fx_rrmdir( $r25 );

/* The coverage assertion is the anti-pattern mechanism itself (design D1'.6), closing the exact
   CRITICAL shape that sank both prior slices of this change: a check added with no fixture
   exercising it. "Observed" means the ID appeared SOMEWHERE in some fixture's --row-types output
   above — fx_track_ids() harvests every scenario's run, so a row type firing only incidentally
   (never behind its own dedicated assertion) still counts, deliberately: RT_ORPHAN_FILE is real
   coverage even though no scenario above asserts on it directly. */
echo "--- fixture coverage: declared - observed - exempt must be empty ---\n";
$fx_observed = array_keys( $GLOBALS['fx_observed_ids'] );
sort( $fx_observed );
$fx_missing = array_diff( $declared_row_types, $fx_observed, COVERAGE_EXEMPT );
ok(
	array() === $fx_missing,
	'every row type ROW_TYPES declares is produced by at least one fixture above',
	array( 'declared' => count( $declared_row_types ), 'observed' => count( $fx_observed ), 'missing' => array_values( $fx_missing ) )
);
ok( count( COVERAGE_EXEMPT ) <= 3, 'the exempt list has not grown past its ratchet ceiling', COVERAGE_EXEMPT );

/* -------------------------------------------------------------- report */

printf( "\n%d OK / %d FAIL\n", $pass, $fail );
exit( $fail ? 1 : 0 );
