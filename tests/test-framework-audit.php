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
$GLOBALS['fx_diagnostics']  = array();
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
/* Every printed line containing every one of $needles -- lets an assertion pin itself to ONE
   bullet among several that share the same fixture's combined output, instead of asking whether
   an ID appears ANYWHERE in it (which a different bullet could satisfy by accident). */
function fx_lines_with( $out, array $needles ) {
	return array_values(
		array_filter(
			explode( "\n", $out ),
			function ( $l ) use ( $needles ) {
				foreach ( $needles as $n ) {
					if ( false === strpos( $l, $n ) ) {
						return false;
					}
				}
				return true;
			}
		)
	);
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
/* Every PHP diagnostic any fixture's run printed, accumulated the same way IDs are. A scenario
   asserts on the substrings it cares about and ok() prints the output only when it FAILS, so a
   warning riding along in EVERY run is invisible by construction -- the marker grammar's
   affirmative form emitted "Undefined array key 1" 44 times across a suite reporting 0 FAIL. The
   audit writes to STDOUT and the gate reads STDOUT: a diagnostic there is corrupt output. */
function fx_track_diagnostics( $out ) {
	if ( preg_match_all( '/^(?:PHP )?(?:Warning|Notice|Deprecated|Fatal error|Parse error):.*/m', $out, $m ) ) {
		foreach ( $m[0] as $d ) {
			$GLOBALS['fx_diagnostics'][ preg_replace( '/ in .*$/', '', trim( $d ) ) ] = true;
		}
	}
}
/* The LEVEL of the single row a set of needles pins, from the --row-types layout's second column.
   Presence says a check FIRED; it says nothing about whether it still BLOCKS. The audit exits 1 on
   FAIL alone, so one 'FAIL' -> 'WARN' token turns a gate into a comment, and seven of this slice's
   ten blocking checks were asserted by presence only. A level is behaviour. Assert it. */
function fx_row_level( $out, array $needles ) {
	$lines = fx_lines_with( $out, $needles );
	if ( 1 !== count( $lines ) ) {
		return '<' . count( $lines ) . ' rows matched, expected exactly 1>';
	}
	return preg_match( '/^RT_[A-Z0-9_]+\s+(FAIL|WARN|JUDGE)\s/', $lines[0], $m ) ? $m[1] : '<no level column>';
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

/* Mirrors framework-audit.php's own $PERS_IDS / $PERS_FIELDS (never imported — the audit is a
   subprocess, see the file header) so a fixture catalog is built to the exact shape the parser
   expects: one "### `PERS-ID`" heading per personality, each followed by every required
   "**Field:**" bullet. */
$FX_PERS_IDS    = array(
	'PERS-EDITORIAL', 'PERS-BOLD-STARTUP', 'PERS-MINIMAL-SWISS', 'PERS-WARM-BOUTIQUE',
	'PERS-CORPORATE-TRUST', 'PERS-FASHION-EDIT', 'PERS-TECH-PRECISION', 'PERS-PERFORMANCE-ENERGY',
);
$FX_PERS_FIELDS = array( 'Fits', 'Typography', 'Color mood', 'Radius & shadow', 'Motion intensity', 'Imagery', 'Card recipe' );

/* Builds a design-personalities.md catalog: every $FX_PERS_IDS block, every $FX_PERS_FIELDS
   bullet filled in. $skip_id omits one whole personality block (for RT_PERS_ID_MISSING);
   $skip_field_pid + $skip_field omit one bullet from one block (for RT_PERS_MISSING_FIELD) — a
   caller sets one pair or the other, never both, so each scenario proves exactly one failure
   mode. */
function fx_pers_catalog( $skip_id = null, $skip_field_pid = null, $skip_field = null ) {
	global $FX_PERS_IDS, $FX_PERS_FIELDS;
	$out = "# Design personalities fixture\n\n";
	foreach ( $FX_PERS_IDS as $pid ) {
		if ( $pid === $skip_id ) {
			continue;
		}
		$out .= "### `$pid`\n";
		foreach ( $FX_PERS_FIELDS as $field ) {
			if ( $pid === $skip_field_pid && $field === $skip_field ) {
				continue;
			}
			$out .= '**' . $field . ':** fixture value.' . "\n";
		}
		$out .= "\n";
	}
	return $out;
}

/* A minimal write-capable SKILL.md: frontmatter + a passing build gate + the given "## Hard
   Rules" body, plus an optional $extra section (e.g. a custom "## Execution Steps"). $skill MUST
   be one of framework-audit.php's own hardcoded $WRITE_CAPABLE names -- that list is not
   fixture-configurable -- or none of the marker-grammar rows below this comment ever fire. */
function fx_wc_skill( $root, $skill, $hard_rules_body, $extra = '' ) {
	fx(
		$root,
		"skills/$skill/SKILL.md",
		"---\nname: $skill\ndescription: \"Trigger: fixture.\"\nlicense: MIT\nmetadata:\n  author: fixture\n  version: \"1.0\"\n---\n\n"
		. "Build gate: requires explicit **yes** before writing.\n\n"
		. "## Hard Rules\n" . $hard_rules_body . "\n" . $extra
	);
}

/* A conforming skeleton every fixture starts from: one skill (qa-review, which the audit checks
   by a HARDCODED path regardless of whether the skill exists) plus its house-rules file, an
   offline test file, a conforming ux-design-system skill carrying a conforming
   design-personalities.md (RT_PERS_CATALOG_MISSING is checked the same unconditional way as
   RT_HOUSERULES_MISSING — see framework-audit.php — so it needs the same treatment here), and
   CONTRIBUTING.md documenting every row type. Without this every fixture would carry FAILs that
   have nothing to do with what it actually tests.
   The ux-design-system SKILL.md itself is required too, not just the catalog file underneath it:
   writing references/design-personalities.md alone creates a skills/ux-design-system/ directory
   the skill-loop then walks and flags RT_NO_SKILL_MD for, since that loop is glob($root.'/skills/*')
   filtered by is_dir(), not by "has a SKILL.md" — any references/ or assets/ file dropped under a
   new skill name silently enrolls that name as a skill. */
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
		"Recommends a personality via a CAPA 2 step; see design-personalities.md for the catalog.\n"
	);
	fx( $root, 'skills/ux-design-system/references/design-personalities.md', fx_pers_catalog() );
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
		fx_track_diagnostics( $out );
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
	/* The House rule bullet is not decoration: an agent stating no House rules is now its own FAIL,
	   and this scenario asserts on the FAIL COUNT, so the fixture has to conform on every axis but
	   the one it tests. */
	"---\nname: orchestrator\n---\n\n## House rules\n- Routes to `phantom-skill` for commerce.\n  (no verifier: a fixture rule, here only so the agent states something.)\n"
);
list( $code2, $out2 ) = fx_run_ok( $audit, $r2 );
list( $f2, , ) = fx_counts( $out2 );
ok( 1 === $code2, 'exit code 1 when a routed skill is missing', $code2 );
ok( 1 === $f2, 'exactly one FAIL row reported', $f2 );
ok( has( $out2, 'routes to skill "phantom-skill" which is missing' ), 'the FAIL names the missing skill', $out2 );
/* Only an agent carrying a ROUTING MAP owes every skill a mention. This fixture has none, so the
   "unroutable" rows must stay silent: the loop runs over every agents/*.md, and while there was
   exactly one agent it silently equated "an agent" with "the router". A second agent that routes
   to nothing by design (a copywriter) collected one WARN per skill saying it was unroutable
   through the orchestrator, which it is not supposed to be. */
ok( ! has( $out2, 'unroutable through this agent' ), 'an agent with NO routing map is never told it fails to mention a skill', $out2 );
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

echo "--- but an agent that DOES carry a routing map still owes every skill a mention ---\n";
$r2b = fx_tmp_root();
fx_base( $r2b );
fx(
	$r2b,
	'agents/orchestrator.md',
	"---\nname: orchestrator\n---\n\n## Routing map\n| Need | Skill |\n|------|-------|\n| Build | `elementor-core` |\n\n"
	. "## House rules\n- Routes commerce work.\n  (no verifier: a fixture rule, here only so the agent states something.)\n"
);
list( $code2b, $out2b ) = fx_run_ok( $audit, $r2b );
ok( has( $out2b, 'unroutable through this agent' ), 'a routing-map agent that omits a skill still WARNs', $out2b );
ok( ! has( $out2b, '"elementor-core" — unroutable' ), 'and says nothing about the one it does mention', $out2b );
fx_rrmdir( $r2b );

echo "--- an agent may name a sibling AGENT without it reading as a missing skill ---\n";
/* Delegation requires naming the delegate. Before this, backticking a second agent's name was a
   FAIL for routing to a skill that does not exist -- a FAIL for doing the thing delegation needs. */
$r2c = fx_tmp_root();
fx_base( $r2c );
fx( $r2c, 'agents/sidekick.md', "---\nname: sidekick\n---\n\n## House rules\n- Does one thing.\n  (no verifier: a fixture rule, here only so the agent states something.)\n" );
fx(
	$r2c,
	'agents/orchestrator.md',
	"---\nname: orchestrator\n---\n\n## House rules\n- Delegates writing to `sidekick`, and never to `ghost-agent`.\n  (no verifier: a fixture rule, here only so the agent states something.)\n"
);
list( $code2c, $out2c ) = fx_run_ok( $audit, $r2c );
ok( ! has( $out2c, 'routes to skill "sidekick"' ), 'naming a real sibling agent is not a missing route', $out2c );
ok( has( $out2c, 'routes to skill "ghost-agent" which is missing' ), 'but a name that is neither skill nor agent still FAILs', $out2c );
fx_rrmdir( $r2c );

echo "--- and the mirror: a skill that DECLARES a gate but is not on the list ---\n";
/* $WRITE_CAPABLE is what makes the gate requirement, the marker requirement and the write checks
   apply at all, so a name dropped from it disables all three at once -- and for a skill that writes
   through the connector rather than through PHP in this repo, the content detector cannot notice.
   This was found by mutating the list and watching nothing fail. */
$r6b = fx_tmp_root();
fx_base( $r6b );
fx(
	$r6b,
	'skills/html-mockup/SKILL.md',
	"---\nname: html-mockup\ndescription: \"Trigger: fixture.\"\nlicense: MIT\nmetadata:\n  author: fixture\n  version: \"1.0\"\n---\n\n"
	. "Fixture: a skill NOT in \$WRITE_CAPABLE that nevertheless declares a blocking gate.\n\n"
	. "**Build gate — blocking.** Do not run until the user has given an explicit **yes**.\n\n"
	. "## Hard Rules\n- Something.\n  (no verifier: fixture bullet, nothing checks this.)\n"
);
list( $code6b, $out6b ) = fx_run_ok( $audit, $r6b );
$unlisted_rows = array_filter(
	explode( "\n", $out6b ),
	function ( $l ) {
		return has( $l, 'html-mockup' ) && has( $l, 'missing from $WRITE_CAPABLE' );
	}
);
ok( count( $unlisted_rows ) > 0, 'a skill declaring a build gate while off $WRITE_CAPABLE FAILs', $out6b );
fx_rrmdir( $r6b );

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

echo "--- a Hard Rule bullet with no verifier marker at all is RT_MARKER_ABSENT (D1', replaces RT_HARD_RULE_NO_VERIFIER) ---\n";
$r18 = fx_tmp_root();
fx_base( $r18 );
fx_wc_skill( $r18, 'elementor-core', "- Keep components small and focused on one job.\n" );
list( , $out18 ) = fx_run_ok( $audit, $r18 );
ok( has( $out18, 'RT_MARKER_ABSENT' ), 'a Hard Rule bullet with no verifier marker at all is RT_MARKER_ABSENT', $out18 );
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

/* --------------------------------------------- ux-design-system personality catalog fixtures
 *
 * The six checks this pairing adds all follow the row-type-registry shape above; each gets the
 * smallest tree that makes ONE check fire, matching the retrofit fixtures. Two of the six —
 * catalog-missing and hardcoded-font — also prove the conforming shape produces NO row, because
 * both call sites sit next to a sibling check (RT_PERS_MISSING_FIELD / RT_PERS_ID_MISSING share
 * the else-branch; RT_TOKENS_HARDCODED_FONT loops two literals) where an assertion that only ever
 * fires can never prove the check discriminates rather than always firing. */

echo "--- ux-design-system: missing design-personalities.md is RT_PERS_CATALOG_MISSING, and a conforming catalog clears it ---\n";
$r26 = fx_tmp_root();
fx_base( $r26 );
unlink( $r26 . '/skills/ux-design-system/references/design-personalities.md' );
list( , $out26a ) = fx_run_ok( $audit, $r26 );
ok( has( $out26a, 'RT_PERS_CATALOG_MISSING' ), 'a missing design-personalities.md is RT_PERS_CATALOG_MISSING', $out26a );

fx( $r26, 'skills/ux-design-system/references/design-personalities.md', fx_pers_catalog() );
list( , $out26b ) = fx_run_ok( $audit, $r26 );
ok( ! has( $out26b, 'RT_PERS_CATALOG_MISSING' ), 'restoring a conforming catalog clears the row', $out26b );
fx_rrmdir( $r26 );

echo "--- ux-design-system: a personality block missing a required field is RT_PERS_MISSING_FIELD ---\n";
$r27 = fx_tmp_root();
fx_base( $r27 );
fx( $r27, 'skills/ux-design-system/references/design-personalities.md', fx_pers_catalog( null, 'PERS-EDITORIAL', 'Motion intensity' ) );
list( , $out27 ) = fx_run_ok( $audit, $r27 );
ok(
	has( $out27, 'RT_PERS_MISSING_FIELD' ) && has( $out27, 'PERS-EDITORIAL' ) && has( $out27, 'Motion intensity' ),
	'PERS-EDITORIAL missing "Motion intensity" is RT_PERS_MISSING_FIELD naming both the personality and the field',
	$out27
);
fx_rrmdir( $r27 );

echo "--- ux-design-system: a declared personality ID absent from the catalog is RT_PERS_ID_MISSING ---\n";
$r28 = fx_tmp_root();
fx_base( $r28 );
fx( $r28, 'skills/ux-design-system/references/design-personalities.md', fx_pers_catalog( 'PERS-TECH-PRECISION' ) );
list( , $out28 ) = fx_run_ok( $audit, $r28 );
ok(
	has( $out28, 'RT_PERS_ID_MISSING' ) && has( $out28, 'PERS-TECH-PRECISION' ),
	'a catalog missing PERS-TECH-PRECISION entirely is RT_PERS_ID_MISSING naming it',
	$out28
);
fx_rrmdir( $r28 );

echo "--- ux-design-system: design-tokens.md hardcoding an example font pairing is RT_TOKENS_HARDCODED_FONT, and dropping it clears the row ---\n";
$r29 = fx_tmp_root();
fx_base( $r29 );
fx( $r29, 'skills/ux-design-system/references/design-tokens.md', "# Design tokens fixture\n\nHeadings use Space Grotesk; body text uses Manrope.\n" );
list( , $out29a ) = fx_run_ok( $audit, $r29 );
ok(
	has( $out29a, 'RT_TOKENS_HARDCODED_FONT' ) && has( $out29a, 'Space Grotesk' ) && has( $out29a, 'Manrope' ),
	'design-tokens.md hardcoding both example fonts raises RT_TOKENS_HARDCODED_FONT naming each',
	$out29a
);

fx( $r29, 'skills/ux-design-system/references/design-tokens.md', "# Design tokens fixture\n\nThe font pairing comes from the chosen personality; no example is hardcoded here.\n" );
list( , $out29b ) = fx_run_ok( $audit, $r29 );
ok( ! has( $out29b, 'RT_TOKENS_HARDCODED_FONT' ), 'a design-tokens.md with no hardcoded example font clears the row', $out29b );
fx_rrmdir( $r29 );

echo "--- ux-design-system: SKILL.md never mentioning design-personalities.md is RT_CATALOG_UNMENTIONED ---\n";
$r30 = fx_tmp_root();
fx_base( $r30 );
fx(
	$r30,
	'skills/ux-design-system/SKILL.md',
	"---\nname: ux-design-system\ndescription: \"Trigger: fixture skill.\"\nlicense: MIT\nmetadata:\n  author: fixture\n  version: \"1.0\"\n---\n\n"
	. "Fixture skill body. Names CAPA 2 here so this scenario isolates the catalog-pointer check alone, but never points at the catalog itself.\n"
);
list( , $out30 ) = fx_run_ok( $audit, $r30 );
ok( has( $out30, 'RT_CATALOG_UNMENTIONED' ), 'a SKILL.md never mentioning design-personalities.md is RT_CATALOG_UNMENTIONED', $out30 );
fx_rrmdir( $r30 );

echo "--- ux-design-system: SKILL.md with no CAPA 2 recommender step is RT_UXDS_NO_CAPA2_STEP ---\n";
$r31 = fx_tmp_root();
fx_base( $r31 );
fx(
	$r31,
	'skills/ux-design-system/SKILL.md',
	"---\nname: ux-design-system\ndescription: \"Trigger: fixture skill.\"\nlicense: MIT\nmetadata:\n  author: fixture\n  version: \"1.0\"\n---\n\n"
	. "Fixture skill body. Recommends a personality by pointing at design-personalities.md, but names no recommender step.\n"
);
list( , $out31 ) = fx_run_ok( $audit, $r31 );
ok( has( $out31, 'RT_UXDS_NO_CAPA2_STEP' ), 'a SKILL.md with no CAPA 2 step is RT_UXDS_NO_CAPA2_STEP', $out31 );
fx_rrmdir( $r31 );

/* ------------------------------------------------- marker grammar fixtures (B1, design D1')
 *
 * Every scenario below is scoped to a $WRITE_CAPABLE skill name (elementor-core, divi-core,
 * woocommerce, wordpress-seo, wordpress-performance) because the marker-grammar verdict rows are
 * scoped that way in framework-audit.php itself -- see fx_wc_skill(). Two mandatory fixtures
 * (r33, r34) reproduce the exact mutation that defeated the rejected slice's greedy regex: a
 * marker line followed by trailing prose that itself ends in a parenthetical.
 */

echo "--- a bullet whose PROSE merely mentions the marker mid-sentence is RT_MARKER_ABSENT, never a false marker match ---\n";
/* The exact framework-audit/SKILL.md shape this slice exists to prevent forever: the token
   appears on the bullet's continuation line, but backtick-wrapped and mid-sentence, never as the
   first character of the line. */
$r32 = fx_tmp_root();
fx_base( $r32 );
fx_wc_skill(
	$r32,
	'elementor-core',
	"- Do the thing properly and double-check the work before it ships.\n"
	. "  See the `(no verifier: reason)` convention in CONTRIBUTING.md for the escape hatch shape.\n"
);
list( , $out32 ) = fx_run_ok( $audit, $r32 );
ok( has( $out32, 'RT_MARKER_ABSENT' ), 'a bullet whose prose merely mentions the marker mid-sentence is RT_MARKER_ABSENT, not a false marker match', $out32 );
fx_rrmdir( $r32 );

echo "--- CRITICAL fixture: (no verifier: -) + trailing prose ending in a parenthetical is RT_MARKER_TRAILING_TEXT, not zero rows ---\n";
/* Reproduces the proven exploit verbatim: under the rejected slice's greedy /s regex this fixture
   produced ZERO rows because the pattern matched from the marker's "(" all the way to the LAST
   ")" in the bullet, swallowing the whole trailing sentence into the payload. */
$r33 = fx_tmp_root();
fx_base( $r33 );
fx_wc_skill(
	$r33,
	'divi-core',
	"- A real constraint the rule describes.\n"
	. "  (no verifier: -)\n"
	. "  More prose after the marker line, and it happens to end in a parenthetical (like this one).\n"
);
list( $code33, $out33 ) = fx_run_ok( $audit, $r33 );
ok( has( $out33, 'RT_MARKER_TRAILING_TEXT' ), '(no verifier: -) followed by trailing prose ending in a parenthetical is RT_MARKER_TRAILING_TEXT', $out33 );
ok( 1 === $code33, 'RT_MARKER_TRAILING_TEXT is a FAIL, exit code 1', $code33 );
fx_rrmdir( $r33 );

echo "--- CRITICAL fixture: (verifier: dunno) + trailing prose naming an unrelated es_*() is RT_MARKER_TRAILING_TEXT, existence never resolves through the prose ---\n";
$r34 = fx_tmp_root();
fx_base( $r34 );
fx_wc_skill(
	$r34,
	'woocommerce',
	"- A real constraint the rule describes.\n"
	. "  (verifier: dunno)\n"
	. "  Unrelated trailing prose that happens to name es_container_audit() by accident.\n"
);
list( , $out34 ) = fx_run_ok( $audit, $r34 );
ok( has( $out34, 'RT_MARKER_TRAILING_TEXT' ), '(verifier: dunno) followed by trailing prose naming an unrelated es_*() is RT_MARKER_TRAILING_TEXT', $out34 );
ok( ! has( $out34, 'RT_MARKER_TARGET_MISSING' ) && ! has( $out34, 'es_container_audit' ), 'the existence check never resolves through the unrelated trailing prose', $out34 );
fx_rrmdir( $r34 );

echo "--- a payload wrapped across two lines, closing at the absolute end, is accepted ---\n";
$r35 = fx_tmp_root();
fx_base( $r35 );
fx( $r35, 'skills/wordpress-performance/assets/dummy.php', "<?php\nfunction es_multiline_target() {\n\treturn true;\n}\n" );
fx_wc_skill(
	$r35,
	'wordpress-performance',
	"- A bullet whose verifier payload wraps across two physical lines in the source.\n"
	. "  (verifier: this explanation deliberately wraps across two lines and finally\n"
	. "  cites es_multiline_target() to prove multi-line payload capture works)\n"
);
list( , $out35 ) = fx_run_ok( $audit, $r35 );
ok( array() === fx_lines_with( $out35, array( 'wordpress-performance', 'RT_MARKER' ) ), 'a multi-line wrapped payload that closes at the absolute end is accepted -- no marker row', $out35 );
fx_rrmdir( $r35 );

echo "--- a payload containing the verifier's OWN call parentheses is captured whole, the balancer does not stop at the first \")\" ---\n";
$r36 = fx_tmp_root();
fx_base( $r36 );
fx( $r36, 'skills/elementor-core/assets/dummy.php', "<?php\nfunction es_test_target() {\n\treturn true;\n}\n" );
fx_wc_skill( $r36, 'elementor-core', "- A bullet citing a helper whose own call parentheses must not confuse the balancer.\n  (verifier: es_test_target())\n" );
list( , $out36 ) = fx_run_ok( $audit, $r36 );
ok( array() === fx_lines_with( $out36, array( 'elementor-core', 'RT_MARKER' ) ), '(verifier: es_test_target()) -- the balancer finds the marker\'s OWN closing paren, not the first one, so this resolves with no row', $out36 );
fx_rrmdir( $r36 );

echo "--- two markers on one bullet is RT_MARKER_MULTIPLE ---\n";
$r37 = fx_tmp_root();
fx_base( $r37 );
fx_wc_skill(
	$r37,
	'divi-core',
	"- A confused bullet carrying two markers.\n"
	. "  (verifier: first explanation of the check goes here)\n"
	. "  (verifier: second explanation should never coexist with the first)\n"
);
list( , $out37 ) = fx_run_ok( $audit, $r37 );
ok( 'FAIL' === fx_row_level( $out37, array( 'RT_MARKER_MULTIPLE' ) ), 'a bullet with two verifier markers is RT_MARKER_MULTIPLE, at FAIL level', fx_row_level( $out37, array( 'RT_MARKER_MULTIPLE' ) ) );
fx_rrmdir( $r37 );

echo "--- an unclosed marker is RT_MARKER_UNCLOSED ---\n";
$r38 = fx_tmp_root();
fx_base( $r38 );
fx_wc_skill( $r38, 'woocommerce', "- A bullet whose marker forgets to close its parenthesis.\n  (verifier: this reason never gets its closing paren\n" );
list( , $out38 ) = fx_run_ok( $audit, $r38 );
ok( 'FAIL' === fx_row_level( $out38, array( 'RT_MARKER_UNCLOSED' ) ), 'a marker whose opening paren is never closed is RT_MARKER_UNCLOSED, at FAIL level', fx_row_level( $out38, array( 'RT_MARKER_UNCLOSED' ) ) );
fx_rrmdir( $r38 );

echo "--- an empty reason on BOTH polarities is RT_MARKER_EMPTY, never RT_MARKER_UNCLOSED ---\n";
$r39 = fx_tmp_root();
fx_base( $r39 );
fx_wc_skill(
	$r39,
	'wordpress-seo',
	"- First bullet with an empty affirmative marker.\n  (verifier:)\n"
	. "- Second bullet with an empty negated marker.\n  (no verifier:)\n"
);
list( , $out39 ) = fx_run_ok( $audit, $r39 );
ok( 2 === substr_count( $out39, 'RT_MARKER_EMPTY' ), '(verifier:) and (no verifier:) both raise RT_MARKER_EMPTY -- the closing paren IS there, this is not RT_MARKER_UNCLOSED', $out39 );
ok( ! has( $out39, 'RT_MARKER_UNCLOSED' ), 'an empty-but-closed marker is never misreported as unclosed', $out39 );
ok( 'FAIL' === fx_row_level( $out39, array( 'RT_MARKER_EMPTY', 'First bullet' ) ), 'an empty marker payload is RT_MARKER_EMPTY, at FAIL level', fx_row_level( $out39, array( 'RT_MARKER_EMPTY', 'First bullet' ) ) );
fx_rrmdir( $r39 );

echo "--- wrong case ((no) Verifier:/VERIFIER:) is RT_MARKER_CASE, never silently accepted ---\n";
$r40 = fx_tmp_root();
fx_base( $r40 );
fx_wc_skill( $r40, 'elementor-core', "- A bullet using the wrong case for the marker token.\n  (NO VERIFIER: this should be flagged for case, not silently accepted)\n" );
list( , $out40 ) = fx_run_ok( $audit, $r40 );
ok( 'FAIL' === fx_row_level( $out40, array( 'RT_MARKER_CASE' ) ), 'a marker token that is not the exact lowercase literal is RT_MARKER_CASE, at FAIL level', fx_row_level( $out40, array( 'RT_MARKER_CASE' ) ) );
fx_rrmdir( $r40 );

echo "--- D1'.5: a name defined for real resolves; the same name in a docblock or a string literal does not (token_get_all, not a raw regex) ---\n";
$r41 = fx_tmp_root();
fx_base( $r41 );
fx(
	$r41,
	'skills/woocommerce/assets/tokens.php',
	"<?php\n"
	. "/**\n * Historically this called es_ghost() directly; now it delegates.\n */\n"
	. "function es_real() {\n\treturn true;\n}\n"
	. "\$s = 'function es_phantom() { return true; }';\n"
);
fx_wc_skill(
	$r41,
	'woocommerce',
	"- Shape A: cites a function really defined in this skill's assets.\n  (verifier: resolved through es_real() which is a genuine function)\n"
	. "- Shape B: cites a name that only ever appears inside a docblock comment.\n  (verifier: this claims es_ghost() checks it, but that name is only a comment)\n"
	. "- Shape C: cites a name that only ever appears inside a string literal.\n  (verifier: this claims es_phantom() checks it, but that name is only a string)\n"
);
list( , $out41 ) = fx_run_ok( $audit, $r41 );
ok( array() === fx_lines_with( $out41, array( 'Shape A', 'RT_MARKER' ) ), 'a function genuinely defined (T_FUNCTION + T_STRING) resolves, no marker row', $out41 );
ok( array() !== fx_lines_with( $out41, array( 'RT_MARKER_TARGET_MISSING', 'es_ghost()' ) ), 'a name that exists only inside a docblock comment is RT_MARKER_TARGET_MISSING, naming es_ghost()', $out41 );
ok( array() !== fx_lines_with( $out41, array( 'RT_MARKER_TARGET_MISSING', 'es_phantom()' ) ), 'a name that exists only inside a string literal is RT_MARKER_TARGET_MISSING, naming es_phantom()', $out41 );
fx_rrmdir( $r41 );

echo "--- the mislabel: (no verifier: ...) citing a step that DOES exist is RT_MARKER_MISLABEL, a JUDGE, never silently accepted ---\n";
$r42 = fx_tmp_root();
fx_base( $r42 );
fx_wc_skill(
	$r42,
	'divi-core',
	"- A bullet whose author wrongly believes nothing checks it.\n  (no verifier: covered already by step-1 in this very skill)\n",
	"## Execution Steps\n1. The first real, numbered step this marker can point at.\n2. A second step, unrelated.\n"
);
list( , $out42 ) = fx_run_ok( $audit, $r42 );
ok( array() !== fx_lines_with( $out42, array( 'RT_MARKER_MISLABEL', 'divi-core step 1' ) ), '(no verifier: ...) citing step-4-shaped text that resolves to a real step is RT_MARKER_MISLABEL, a JUDGE row', $out42 );
fx_rrmdir( $r42 );

echo "--- pivotal pair: (verifier: TODO) and (no verifier: TODO) raise the IDENTICAL FAIL row type and exit code (D1'.3 symmetry) ---\n";
/* This is the incentive fix: in the rejected slice, the affirmative marker degraded to a
   non-blocking JUDGE while the negated one FAILed for the identical placeholder payload, so the
   cheapest route past the gate was to ASSERT a verifier that does not exist. Both polarities must
   now cost the same. */
$r43 = fx_tmp_root();
fx_base( $r43 );
fx_wc_skill(
	$r43,
	'wordpress-performance',
	"- Affirmative marker with a placeholder payload.\n  (verifier: TODO)\n"
	. "- Negated marker with the identical placeholder payload.\n  (no verifier: TODO)\n"
);
list( $code43, $out43 ) = fx_run_ok( $audit, $r43 );
ok( 2 === substr_count( $out43, 'RT_MARKER_STOPWORD' ), '(verifier: TODO) and (no verifier: TODO) both raise RT_MARKER_STOPWORD -- the SAME row type', $out43 );
ok( 1 === $code43, 'both stopword placeholders FAIL -- exit code 1, never a free pass for either polarity', $code43 );
fx_rrmdir( $r43 );

echo "--- a payload under 12 characters is RT_MARKER_TOO_SHORT ---\n";
$r44 = fx_tmp_root();
fx_base( $r44 );
fx_wc_skill( $r44, 'elementor-core', "- Affirmative marker whose payload is real but too short to mean much.\n  (verifier: short)\n" );
list( , $out44 ) = fx_run_ok( $audit, $r44 );
ok( 'FAIL' === fx_row_level( $out44, array( 'RT_MARKER_TOO_SHORT' ) ), 'a payload under 12 characters is RT_MARKER_TOO_SHORT, at FAIL level', fx_row_level( $out44, array( 'RT_MARKER_TOO_SHORT' ) ) );
fx_rrmdir( $r44 );

echo "--- a payload over 40 words is RT_MARKER_OVERSIZE ---\n";
$r45 = fx_tmp_root();
fx_base( $r45 );
fx_wc_skill( $r45, 'woocommerce', "- Affirmative marker whose payload blows well past the 40-word cap.\n  (verifier: " . implode( ' ', array_fill( 0, 45, 'word' ) ) . ")\n" );
list( , $out45 ) = fx_run_ok( $audit, $r45 );
ok( 'FAIL' === fx_row_level( $out45, array( 'RT_MARKER_OVERSIZE' ) ), 'a payload over 40 words is RT_MARKER_OVERSIZE, at FAIL level', fx_row_level( $out45, array( 'RT_MARKER_OVERSIZE' ) ) );
fx_rrmdir( $r45 );

echo "--- an affirmative marker citing no locatable shape at all is RT_MARKER_PROSE_ONLY, a JUDGE ---\n";
$r46 = fx_tmp_root();
fx_base( $r46 );
fx_wc_skill( $r46, 'wordpress-seo', "- Affirmative marker whose reason is a real sentence but cites nothing checkable.\n  (verifier: the team eyeballs this manually before every release, no artifact yet)\n" );
list( , $out46 ) = fx_run_ok( $audit, $r46 );
ok( has( $out46, 'RT_MARKER_PROSE_ONLY' ), 'an affirmative marker citing no function, tests/ path, house-rule row or step is RT_MARKER_PROSE_ONLY', $out46 );
fx_rrmdir( $r46 );

echo "--- D1'.4: all four resolver shapes, when the cited target really exists, resolve with no row ---\n";
$r47 = fx_tmp_root();
fx_base( $r47 );
fx( $r47, 'skills/elementor-core/assets/shapes.php', "<?php\nfunction es_shape_ok() {\n\treturn true;\n}\n" );
fx( $r47, 'tests/shape-ok.php', "<?php\n// fixture, never executed by the audit\n" );
fx( $r47, 'skills/qa-review/references/house-rules.md', "# House Rules\n\n| 5 | A real rule | Server-side check | **auto** |\n" );
fx_wc_skill(
	$r47,
	'elementor-core',
	"- Shape 1: cites a function that is really defined.\n  (verifier: es_shape_ok() proves this)\n"
	. "- Shape 2: cites a tests/ path that really exists.\n  (verifier: see `tests/shape-ok.php` for it)\n"
	. "- Shape 3: cites a house-rules row that really exists.\n  (verifier: `qa-review` house-rule row 5 covers it)\n"
	. "- Shape 4: cites an execution step that really exists.\n  (verifier: step 1 of this skill's own Execution Steps)\n",
	"## Execution Steps\n1. The first real numbered step this marker can point at.\n"
);
list( , $out47 ) = fx_run_ok( $audit, $r47 );
ok( array() === fx_lines_with( $out47, array( 'RT_MARKER_TARGET_MISSING', 'Shape 1' ) ), 'shape 1 (existing function) resolves, no target-missing row', $out47 );
ok( array() === fx_lines_with( $out47, array( 'RT_MARKER_TARGET_MISSING', 'Shape 2' ) ), 'shape 2 (existing tests/ path) resolves, no target-missing row', $out47 );
ok( array() === fx_lines_with( $out47, array( 'RT_MARKER_TARGET_MISSING', 'Shape 3' ) ), 'shape 3 (existing house-rules row) resolves, no target-missing row', $out47 );
ok( array() === fx_lines_with( $out47, array( 'RT_MARKER_TARGET_MISSING', 'Shape 4' ) ), 'shape 4 (existing execution step) resolves, no target-missing row', $out47 );
fx_rrmdir( $r47 );

echo "--- D1'.4: all four resolver shapes, when the cited target does NOT exist, are RT_MARKER_TARGET_MISSING ---\n";
$r48 = fx_tmp_root();
fx_base( $r48 );
fx_wc_skill(
	$r48,
	'woocommerce',
	"- Shape 1: cites a function that is never defined anywhere.\n  (verifier: es_shape_missing() proves this)\n"
	. "- Shape 2: cites a tests/ path that never exists.\n  (verifier: see `tests/shape-missing.php` for it)\n"
	. "- Shape 3: cites a house-rules row that never exists.\n  (verifier: `qa-review` house-rule row 999 covers it)\n"
	. "- Shape 4: cites an execution step that never exists.\n  (verifier: step 99 of this skill's own Execution Steps)\n",
	"## Execution Steps\n1. The only real numbered step -- 99 is never one of them.\n"
);
list( , $out48 ) = fx_run_ok( $audit, $r48 );
/* Level, not presence: this is the check that makes asserting a verifier that does not exist cost
   exactly what admitting the gap costs (D1'.3), and at WARN it costs nothing. */
foreach ( array( 1, 2, 3, 4 ) as $shape ) {
	$lvl = fx_row_level( $out48, array( 'RT_MARKER_TARGET_MISSING', "Shape $shape" ) );
	ok( 'FAIL' === $lvl, "shape $shape (missing target) is RT_MARKER_TARGET_MISSING, at FAIL level", $lvl );
}
fx_rrmdir( $r48 );

echo "--- D1'.1 test (i): a valid marker's payload words are excluded from the instruction-word count, both numbers printed ---\n";
/* Expected numbers are derived by running the SAME transformation framework-audit.php runs
   (strip the exact span text, then str_word_count), not hand-counted -- markdown asterisks,
   "## " headings and the boilerplate "Build gate:" line all add real words that a hand count
   silently forgets, which is exactly how this fixture's first draft mismatched by 12-23 words. */
$r49 = fx_tmp_root();
fx_base( $r49 );
$r49_lead    = 'Marks a real rule about something.';
$r49_payload = implode( ' ', array_fill( 0, 30, 'reason' ) );
$r49_span    = '(no verifier: ' . $r49_payload . ')';
$r49_bullet  = "- $r49_lead\n  $r49_span\n";
$r49_prose   = "Build gate: requires explicit **yes** before writing.\n\n"
	. "## Notes\n" . implode( ' ', array_fill( 0, 560, 'filler' ) ) . "\n\n"
	. "## Hard Rules\n" . $r49_bullet . $r49_bullet;
$r49_marker_words = str_word_count( $r49_span . ' ' . $r49_span );
$r49_instr_words   = str_word_count( strip_tags( str_replace( $r49_span, '', $r49_prose ) ) );
ok( str_word_count( strip_tags( $r49_prose ) ) > 600, 'the fixture is over 600 words BEFORE marker exclusion (proves exclusion is load-bearing)', $r49_prose );
ok( $r49_instr_words < 600, 'the fixture is under 600 instruction words AFTER marker exclusion', $r49_instr_words );
fx(
	$r49,
	'skills/wordpress-performance/SKILL.md',
	"---\nname: wordpress-performance\ndescription: \"Trigger: fixture.\"\nlicense: MIT\nmetadata:\n  author: fixture\n  version: \"1.0\"\n---\n\n" . $r49_prose
);
list( , $out49 ) = fx_run_ok( $audit, $r49 );
ok(
	has( $out49, $r49_instr_words . ' instruction words' ) && has( $out49, '+' . $r49_marker_words . ' marker' ),
	"a valid marker's words are excluded from the instruction count; both numbers are printed ($r49_instr_words + $r49_marker_words)",
	$out49
);
ok( ! has( $out49, 'RT_BODY_OVER_600' ), 'the fixture does not FAIL the 600 ceiling once marker words are excluded', $out49 );
fx_rrmdir( $r49 );

echo "--- D1'.1 test (ii): 601 instruction words with zero markers still FAILs -- the ceiling itself is unmoved ---\n";
$r50 = fx_tmp_root();
fx_base( $r50 );
fx(
	$r50,
	'skills/wordpress-performance/SKILL.md',
	"---\nname: wordpress-performance\ndescription: \"Trigger: fixture.\"\nlicense: MIT\nmetadata:\n  author: fixture\n  version: \"1.0\"\n---\n\n"
	. "Build gate: requires explicit **yes** before writing.\n\n"
	. "## Notes\n" . implode( ' ', array_fill( 0, 601, 'filler' ) ) . "\n\n"
	. "## Hard Rules\n- A normal rule with no marker at all.\n"
);
list( , $out50 ) = fx_run_ok( $audit, $r50 );
ok( has( $out50, 'RT_BODY_OVER_600' ), '601 instruction words with no marker to exclude still FAILs RT_BODY_OVER_600', $out50 );
fx_rrmdir( $r50 );

echo "--- D1'.1 test (iv): an UNCLOSED marker's words are counted, never stripped ---\n";
/* Same self-simulation technique as r49: the expected total is str_word_count() applied to the
   EXACT prose this fixture writes, not a hand sum -- an unclosed marker has no valid span to
   strip (marker_parse() never even reaches the point of building one when closed=false), so the
   fixture's own full-body count IS the expected printed number. */
$r51        = fx_tmp_root();
$r51_filler = 595;
$r51_prose  = "Build gate: requires explicit **yes** before writing.\n\n"
	. "## Notes\n" . implode( ' ', array_fill( 0, $r51_filler, 'filler' ) ) . "\n\n"
	. "## Hard Rules\n- A rule whose marker never closes its parenthesis.\n  (no verifier: " . implode( ' ', array_fill( 0, 9, 'unclosed' ) ) . "\n";
$r51_total  = str_word_count( strip_tags( $r51_prose ) );
fx_base( $r51 );
fx(
	$r51,
	'skills/elementor-core/SKILL.md',
	"---\nname: elementor-core\ndescription: \"Trigger: fixture.\"\nlicense: MIT\nmetadata:\n  author: fixture\n  version: \"1.0\"\n---\n\n" . $r51_prose
);
list( , $out51 ) = fx_run_ok( $audit, $r51 );
ok( $r51_total > 600, 'the fixture arithmetic really does cross the 600 ceiling', $r51_total );
ok( has( $out51, $r51_total . ' instruction words' ), "an unclosed marker's words are counted toward the total, never stripped ($r51_total expected)", $out51 );
ok( has( $out51, 'RT_BODY_OVER_600' ) && has( $out51, 'RT_MARKER_UNCLOSED' ), 'the unclosed marker both FAILs on its own and inflates the instruction-word count', $out51 );
fx_rrmdir( $r51 );

echo "--- --word-report prints skill<TAB>instruction_words<TAB>marker_words and exits before the FAIL/WARN/JUDGE summary ---\n";
$r52 = fx_tmp_root();
fx_base( $r52 );
fx_wc_skill( $r52, 'divi-core', "- A rule with a clean marker.\n  (no verifier: " . implode( ' ', array_fill( 0, 10, 'reason' ) ) . ")\n" );
list( $code52, $out52 ) = fx_run( $audit, $r52, array( '--word-report' ) );
ok( 0 === $code52, '--word-report exits 0', $code52 );
ok( 1 === preg_match( '/^divi-core\t\d+\t\d+$/m', $out52 ), '--word-report prints a tab-separated skill/instruction-words/marker-words line', $out52 );
ok( ! has( $out52, 'FAIL /' ), '--word-report does not also print the FAIL/WARN/JUDGE summary line', $out52 );
fx_rrmdir( $r52 );

echo "--- D1'.9: a write-capable skill with NO \"## Hard Rules\" section at all is RT_HARD_RULES_MISSING_WRITE, a FAIL, not a WARN ---\n";
$r53 = fx_tmp_root();
fx_base( $r53 );
fx(
	$r53,
	'skills/woocommerce/SKILL.md',
	"---\nname: woocommerce\ndescription: \"Trigger: fixture.\"\nlicense: MIT\nmetadata:\n  author: fixture\n  version: \"1.0\"\n---\n\n"
	. "Build gate: requires explicit **yes** before writing.\n\nFixture skill body with no Hard Rules section at all.\n"
);
list( $code53, $out53 ) = fx_run_ok( $audit, $r53 );
ok( has( $out53, 'RT_HARD_RULES_MISSING_WRITE' ), 'a write-capable skill with no "## Hard Rules" section is RT_HARD_RULES_MISSING_WRITE', $out53 );
ok( 1 === $code53, 'RT_HARD_RULES_MISSING_WRITE is a FAIL, exit code 1', $code53 );
fx_rrmdir( $r53 );

echo "--- D1'.9: a NON-write-capable skill with no Hard Rules stays RT_NO_HARD_RULES (WARN), the write-capable FAIL does not leak onto it ---\n";
$r54 = fx_tmp_root();
fx_base( $r54 );
list( $code54n, $out54 ) = fx_run_ok( $audit, $r54 );
ok( array() !== fx_lines_with( $out54, array( 'qa-review', 'RT_NO_HARD_RULES' ) ), 'a non-write-capable skill missing "## Hard Rules" stays RT_NO_HARD_RULES', $out54 );
ok( 0 === $code54n, 'RT_NO_HARD_RULES alone does not fail the build', $code54n );
fx_rrmdir( $r54 );

echo "--- D1'.1: a marker-shaped opener line OUTSIDE \"## Hard Rules\" is RT_MARKER_OUTSIDE_RULES (WARN), not silently free ---\n";
$r55 = fx_tmp_root();
fx_base( $r55 );
fx(
	$r55,
	'skills/elementor-core/SKILL.md',
	"---\nname: elementor-core\ndescription: \"Trigger: fixture.\"\nlicense: MIT\nmetadata:\n  author: fixture\n  version: \"1.0\"\n---\n\n"
	. "Build gate: requires explicit **yes** before writing.\n\n"
	. "A stray marker-shaped line sits here, outside any Hard Rules section:\n"
	. "(no verifier: this line looks like a marker but is not inside Hard Rules)\n\n"
	. "## Hard Rules\n- A normal rule with a real marker.\n  (no verifier: this one really is inside the rules section)\n"
);
list( , $out55 ) = fx_run_ok( $audit, $r55 );
ok( has( $out55, 'RT_MARKER_OUTSIDE_RULES' ), 'a marker-shaped opener line outside "## Hard Rules" is RT_MARKER_OUTSIDE_RULES', $out55 );
fx_rrmdir( $r55 );

echo "--- a shape-2 target that climbs OUT of the audited root never counts as existing ---\n";
/* The escaping target really exists on disk, one directory above the audited root, so this fails
   the moment containment is dropped rather than passing because nothing happened to be there. */
$r56       = fx_tmp_root();
$r56_inner = $r56 . '/inner';
fx_base( $r56_inner );
fx( $r56, 'outside/escaped.php', "<?php\n// really exists, but OUTSIDE the audited root\n" );
fx( $r56_inner, 'tests/in-root.php', "<?php\n// really exists, inside the audited root\n" );
fx_wc_skill( $r56_inner, 'woocommerce', "- Escaping bullet: its target climbs out of the audited tree.\n  (verifier: covered by `tests/../../outside/escaped.php` which really is there)\n- Neighbour bullet: the identical shape, staying inside the tree.\n  (verifier: covered by `tests/in-root.php` which really is there)\n" );
list( , $out56 ) = fx_run_ok( $audit, $r56_inner );
ok( 'FAIL' === fx_row_level( $out56, array( 'RT_MARKER_TARGET_MISSING', 'Escaping bullet' ) ), 'a shape-2 target that climbs out of the audited root is RT_MARKER_TARGET_MISSING at FAIL level -- existing on the host never counts as existing in the tree', fx_row_level( $out56, array( 'RT_MARKER_TARGET_MISSING', 'Escaping bullet' ) ) );
ok( array() === fx_lines_with( $out56, array( 'RT_MARKER_TARGET_MISSING', 'Neighbour bullet' ) ), 'the non-traversing twin still resolves, so containment did not break ordinary shape-2 resolution', $out56 );
fx_rrmdir( $r56 );

echo "--- the 40-word marker cap is enforced on EVERY skill, not only the write-capable ones ---\n";
/* The strip was uniform while the cap was not, so a knowledge skill got an unmetered region the
   budget subtracted and no check read. No RT_MARKER_OVERSIZE row is expected here -- the ROW is
   still write-capable-only -- the ceiling FAIL is what must appear. */
$r57 = fx_tmp_root();
fx_base( $r57 );
$r57_span = '(no verifier: ' . implode( ' ', array_fill( 0, 700, 'hidden' ) ) . ')';
fx( $r57, 'skills/web-templates/SKILL.md', "---\nname: web-templates\ndescription: \"Trigger: fixture.\"\nlicense: MIT\nmetadata:\n  author: fixture\n  version: \"1.0\"\n---\n\n## Hard Rules\n- A knowledge-skill rule whose marker tries to swallow the whole budget.\n  $r57_span\n" );
list( , $out57 ) = fx_run_ok( $audit, $r57 );
ok( array() !== fx_lines_with( $out57, array( 'RT_BODY_OVER_600', 'web-templates' ) ), 'an over-cap marker in a NON-write-capable skill is not excluded, so its words still FAIL the 600 ceiling', $out57 );
ok( array() === fx_lines_with( $out57, array( 'RT_MARKER_OVERSIZE', 'web-templates' ) ), 'the OVERSIZE row itself stays scoped to write-capable skills -- the cap is uniform, the row is not', $out57 );
fx_rrmdir( $r57 );

echo "--- a write-capable skill that states no Hard Rules FAILs however it says nothing ---\n";
/* Three ways to state no rules, all previously free: the heading alone at EOF, the heading with
   prose under it, and rules the "- " splitter cannot see. Each was cheaper than the escape this
   design priced, so each was the route a contributor under pressure would find. */
foreach ( array(
	'bare-heading' => '',
	'prose-only'   => 'We have no rules worth writing down here.',
	'star-bullets' => "* A rule the splitter cannot see.\n* A second one.",
) as $shape => $body ) {
	$r58 = fx_tmp_root();
	fx_base( $r58 );
	fx_wc_skill( $r58, 'divi-core', $body );
	list( $code58, $out58 ) = fx_run_ok( $audit, $r58 );
	ok( 'FAIL' === fx_row_level( $out58, array( 'RT_HARD_RULES_MISSING_WRITE', 'divi-core' ) ), "a write-capable skill whose Hard Rules section is $shape is RT_HARD_RULES_MISSING_WRITE, at FAIL level", fx_row_level( $out58, array( 'RT_HARD_RULES_MISSING_WRITE', 'divi-core' ) ) );
	ok( 1 === $code58, "stating no rules as $shape blocks the gate -- exit code 1", $code58 );
	fx_rrmdir( $r58 );
}

echo "--- the mislabel carve-out: a negated marker citing an EXISTING tests/ path is not a mislabel ---\n";
/* The exemption had no fixture, so deleting it left the suite green. The shape-4 twin in the same
   run proves the branch is still live, so the carve-out cannot widen unnoticed either. */
$r59 = fx_tmp_root();
fx_base( $r59 );
fx( $r59, 'tests/carve-out.php', "<?php\n// fixture, never executed\n" );
fx_wc_skill( $r59, 'elementor-core', "- Exempt bullet: names a real tests/ path only as context for the gap.\n  (no verifier: nothing runs this yet, closest is `tests/carve-out.php`)\n- Mislabelled bullet: names a step that really exists.\n  (no verifier: covered already by step-1 in this very skill)\n", "## Execution Steps\n1. The first real numbered step this marker can point at.\n" );
list( , $out59 ) = fx_run_ok( $audit, $r59 );
ok( array() === fx_lines_with( $out59, array( 'RT_MARKER_MISLABEL', 'Exempt bullet' ) ), 'a (no verifier: ...) citing an existing tests/ path is exempt from RT_MARKER_MISLABEL', $out59 );
ok( array() !== fx_lines_with( $out59, array( 'RT_MARKER_MISLABEL', 'Mislabelled bullet' ) ), 'the same run still reports the shape-4 mislabel, so the carve-out is narrow and the branch is live', $out59 );
fx_rrmdir( $r59 );

echo "--- an agent that states no House rules is RT_AGENT_NO_HOUSE_RULES, however it says nothing ---\n";
/* The orchestrator's House rules are the defaults every build inherits, so a violation there ships
   on every site rather than one. They were the last set of rules in the repo nothing read. */
foreach ( array(
	'no-section'   => "---\nname: orchestrator\n---\n\nRoutes to `woocommerce` for commerce.\n",
	'bare-heading' => "---\nname: orchestrator\n---\n\n## House rules\n",
	'prose-only'   => "---\nname: orchestrator\n---\n\n## House rules\nWe have no defaults; decide per build.\n",
) as $shape => $agent ) {
	$r60 = fx_tmp_root();
	fx_base( $r60 );
	fx( $r60, 'agents/orchestrator.md', $agent );
	list( $code60, $out60 ) = fx_run_ok( $audit, $r60 );
	ok( 'FAIL' === fx_row_level( $out60, array( 'RT_AGENT_NO_HOUSE_RULES', 'orchestrator' ) ), "an agent whose House rules are $shape is RT_AGENT_NO_HOUSE_RULES, at FAIL level", fx_row_level( $out60, array( 'RT_AGENT_NO_HOUSE_RULES', 'orchestrator' ) ) );
	ok( 1 === $code60, "an agent stating no House rules blocks the gate as $shape -- exit code 1", $code60 );
	fx_rrmdir( $r60 );
}

echo "--- the marker grammar really runs on an agent's House rules, not only on skills ---\n";
/* The heading match is loose after "House rules" on purpose: the real orchestrator's carries a
   parenthetical, and a rule that only fires on an exact string is one an author retitles their way
   out of by accident. This fixture proves the loose form is walked, not merely matched. */
$r61 = fx_tmp_root();
fx_base( $r61 );
fx(
	$r61,
	'agents/orchestrator.md',
	"---\nname: orchestrator\n---\n\n## House rules (defaults for every build)\n"
	. "- Marked bullet, routed to `woocommerce`.\n  (no verifier: a documented gap, stated so it cannot hide.)\n"
	. "- Unmarked bullet that names no verifier at all.\n"
);
list( , $out61 ) = fx_run_ok( $audit, $r61 );
ok( array() !== fx_lines_with( $out61, array( 'RT_MARKER_ABSENT', 'orchestrator', 'Unmarked bullet' ) ), 'an unmarked House rule in an agent is RT_MARKER_ABSENT, so the grammar reaches agents', $out61 );
ok( array() === fx_lines_with( $out61, array( 'RT_MARKER_ABSENT', 'Marked bullet' ) ), 'the marked House rule beside it raises nothing, so the walk reads each bullet separately', $out61 );
ok( array() === fx_lines_with( $out61, array( 'RT_AGENT_NO_HOUSE_RULES' ) ), 'a parenthesised "## House rules (…)" heading is found, not read as a missing section', $out61 );
fx_rrmdir( $r61 );

echo "--- D1'.4 shape 5: a marker may name one of this audit's own row types ---\n";
/* The House rule "a warning nobody reads is not a warning" is enforced by RT_ERRORLOG_NO_STDOUT
   and had no honest affirmative form until this shape existed -- the only alternatives were to
   name es_warn(), which IMPLEMENTS the rule rather than checking it, or to declare a gap that is
   not real. A row type the registry does not declare must cost the same as a missing function. */
$r62 = fx_tmp_root();
fx_base( $r62 );
fx_wc_skill(
	$r62,
	'woocommerce',
	"- Real row type: cites a check this audit genuinely declares.\n  (verifier: `RT_ERRORLOG_NO_STDOUT` FAILs an error_log call with no stdout channel beside it.)\n"
	. "- Phantom row type: cites one the registry never declared.\n  (verifier: `RT_NOT_A_DECLARED_ROW_TYPE` would catch it, if it existed at all.)\n"
	. "- Unquoted row type: names one only as prose, so it cites no shape at all.\n  (verifier: RT_ERRORLOG_NO_STDOUT is the sort of thing that would catch it one day.)\n"
);
list( $code62, $out62 ) = fx_run_ok( $audit, $r62 );
ok( array() === fx_lines_with( $out62, array( 'RT_MARKER', 'Real row type' ) ), 'a marker naming a declared row type resolves, no marker row', $out62 );
ok( 'FAIL' === fx_row_level( $out62, array( 'RT_MARKER_TARGET_MISSING', 'Phantom row type' ) ), 'a marker naming an undeclared row type is RT_MARKER_TARGET_MISSING, at FAIL level', fx_row_level( $out62, array( 'RT_MARKER_TARGET_MISSING', 'Phantom row type' ) ) );
ok( 1 === $code62, 'naming a row type that does not exist blocks the gate -- exit code 1', $code62 );
ok( array() !== fx_lines_with( $out62, array( 'RT_MARKER_PROSE_ONLY', 'Unquoted row type' ) ), 'an UNQUOTED row type cites no shape: the backticks are what make it a citation rather than prose', $out62 );
fx_rrmdir( $r62 );

echo "--- a row type MENTIONED in passing never steals resolution from the shape the marker means ---\n";
/* The row-type shape shipped first and unquoted, and that was a hole big enough to drive the gate
   through: first-match wins, an ID is legal prose, so a marker naming a verifier that does NOT
   exist resolved green the moment its sentence also mentioned any row type. The target-missing FAIL
   was never reached. Both halves are pinned here -- the steal, and the negated shape-2 carve-out
   inverting into a spurious mislabel -- because a fixture that only tests row-type-alone payloads
   is exactly what let this ship. */
$r63 = fx_tmp_root();
fx_base( $r63 );
fx( $r63, 'tests/neighbour.php', "<?php\n// fixture, never executed\n" );
fx_wc_skill(
	$r63,
	'woocommerce',
	"- Missing helper, with a real row type mentioned in passing.\n  (verifier: es_never_defined_at_all() enforces it, reported the way `RT_ORPHAN_FILE` reports strays.)\n"
	. "- Missing tests path, with a real row type mentioned in passing.\n  (verifier: see `tests/does-not-exist.php`, checked alongside `RT_ORPHAN_FILE` in the same run.)\n"
	. "- Declared gap citing a neighbour file and a row type.\n  (no verifier: nothing runs this yet, closest is `tests/neighbour.php`, unlike `RT_ORPHAN_FILE`.)\n"
);
list( $code63, $out63 ) = fx_run_ok( $audit, $r63 );
ok( 'FAIL' === fx_row_level( $out63, array( 'RT_MARKER_TARGET_MISSING', 'Missing helper' ) ), 'a passing row-type mention does not rescue a marker whose named helper is missing', fx_row_level( $out63, array( 'RT_MARKER_TARGET_MISSING', 'Missing helper' ) ) );
ok( 'FAIL' === fx_row_level( $out63, array( 'RT_MARKER_TARGET_MISSING', 'Missing tests path' ) ), 'a passing row-type mention does not rescue a marker whose named tests/ path is missing', fx_row_level( $out63, array( 'RT_MARKER_TARGET_MISSING', 'Missing tests path' ) ) );
ok( array() === fx_lines_with( $out63, array( 'RT_MARKER_MISLABEL', 'Declared gap' ) ), 'the shape-2 carve-out survives a row type in the same payload -- no spurious mislabel', $out63 );
ok( 1 === $code63, 'the masked target-missing rows still block the gate -- exit code 1', $code63 );
fx_rrmdir( $r63 );

echo "--- reachability: a file nothing routes to is RT_ORPHAN_FILE, at ANY depth ---\n";
/* The old check globbed ONE level and skipped directories, so the deepest corner of the repo was
   the one corner nothing looked at. Recursion alone would have been wrong in the other direction:
   almost nothing here is cited by its full filename, so 21 real files would have been flagged.
   This tree pins the whole model in one run -- a direct pointer, a directory pointer that reaches
   only DIRECT children, a family-prefix citation, a transitive citation through a reachable index,
   and the case that makes it honest: an index nobody can reach vouches for nothing. */
$r64 = fx_tmp_root();
fx_base( $r64 );
fx(
	$r64,
	'skills/web-templates/SKILL.md',
	"---\nname: web-templates\ndescription: \"Trigger: fixture.\"\nlicense: MIT\nmetadata:\n  author: fixture\n  version: \"1.0\"\n---\n\n"
	/* "see `pages/_README.md`" is the real orchestrating shape, and it is what makes the ambiguity
	   rule load-bearing here: the skill holds TWO files named _README.md, so an unqualified mention
	   of that basename must credit NEITHER on its own. */
	/* The bare "references/" and the bare "hidden/" are deliberate: the first is the prose shape
	   that used to whitelist the whole depth-1 layer, the second is a directory-qualified mention
	   of an ambiguous basename, which credits nothing. */
	. "Read `references/atlas.md` first. Everything else lives under `references/`.\n"
	/* The BARE `_README.md` is what arms the ambiguity rule: with two files of that name, an
	   unqualified mention must credit NEITHER. Written directory-qualified, the basename handle
	   could never match anyway once citations became boundary-anchored, and the rule went
	   unverified — a guard that survives its own removal is not a guard. */
	. "Page archetypes live under `references/pages/`. Both indexes are named `_README.md`; the one that counts is the first.\n"
	. "The quick notes are in `references/atlas-q.md`.\n\n"
	. "## Hard Rules\n- A knowledge-skill rule.\n  (no verifier: a fixture rule, here only so the skill states something.)\n"
);
fx( $r64, 'skills/web-templates/references/atlas.md', "# Atlas\n\nThe deep archetype is TPL-DEEP-01.\n" );
fx( $r64, 'skills/web-templates/references/deep/TPL-DEEP-01-first.md', "# TPL-DEEP-01\n" );
fx( $r64, 'skills/web-templates/references/pages/_README.md', "# Pages\n\nThe page archetype is TPL-PAGE-01.\n" );
fx( $r64, 'skills/web-templates/references/pages/one/TPL-PAGE-01-first.md', "# TPL-PAGE-01\n" );
fx( $r64, 'skills/web-templates/references/pages/one/TPL-PAGE-99-lost.md', "# TPL-PAGE-99\n" );
fx( $r64, 'skills/web-templates/references/hidden/_README.md', "# Hidden\n\nThe hidden archetype is TPL-HIDE-01.\n" );
fx( $r64, 'skills/web-templates/references/hidden/TPL-HIDE-01-first.md', "# TPL-HIDE-01\n" );
fx( $r64, 'skills/web-templates/references/stray.md', "# Stray\n" );
fx( $r64, 'skills/web-templates/references/name.md', "# Name\n" );
fx( $r64, 'skills/web-templates/references/loose/one.md', "# One\n" );
fx( $r64, 'skills/web-templates/references/atlas-q.md', "# Atlas Q\n" );
fx( $r64, 'skills/web-templates/references/q.md', "# Q\n" );
list( , $out64 ) = fx_run_ok( $audit, $r64 );
$fx_orphan = function ( $path ) use ( $out64 ) {
	return array() !== fx_lines_with( $out64, array( 'RT_ORPHAN_FILE', $path ) );
};
ok( ! $fx_orphan( 'references/atlas.md' ), 'a file SKILL.md names directly is reachable', $out64 );
ok( ! $fx_orphan( 'references/deep/TPL-DEEP-01-first.md' ), 'a deep file cited by FAMILY prefix from a reachable file is reachable -- recursion alone would have flagged it', $out64 );
ok( ! $fx_orphan( 'references/pages/_README.md' ), 'a directory pointer reaches that directory\'s DIRECT children', $out64 );
ok( ! $fx_orphan( 'references/pages/one/TPL-PAGE-01-first.md' ), 'a file two levels below the pointer is reachable through the index that lists it', $out64 );
ok( 'WARN' === fx_row_level( $out64, array( 'RT_ORPHAN_FILE', 'references/pages/one/TPL-PAGE-99-lost.md' ) ), 'a deep file NO index lists is RT_ORPHAN_FILE, at WARN level -- the row the old one-level glob could not see', fx_row_level( $out64, array( 'RT_ORPHAN_FILE', 'references/pages/one/TPL-PAGE-99-lost.md' ) ) );
ok( $fx_orphan( 'references/hidden/_README.md' ), 'an index nobody points at is itself an orphan, even though SKILL.md names that basename for its SIBLING -- an ambiguous basename credits neither file unqualified', $out64 );
ok( $fx_orphan( 'references/hidden/TPL-HIDE-01-first.md' ), 'and it vouches for nothing: a file listed ONLY by an unreachable index stays an orphan', $out64 );
ok( $fx_orphan( 'references/stray.md' ), 'a depth-1 file nobody mentions is still an orphan -- the old check\'s one real job survives', $out64 );
/* Both of these were laundered in the first cut, and the fixture above could not see either: its
   one depth-1 file was named "stray", a word appearing in no reachable text, and both of its
   directory mentions continued into a path. Measured, the depth-1 detection rate had fallen from
   59 of 60 to 29 of 60 with the whole suite green. A guard that survives a 30-case regression is
   not a guard, so the two laundering shapes get a file each. */
ok( $fx_orphan( 'references/name.md' ), 'a common STEM is not a citation: "name" appears in every SKILL.md\'s own frontmatter, and the file is still an orphan', $out64 );
ok( $fx_orphan( 'references/loose/one.md' ), 'a bare "references/" in prose is not a pointer at the skill root, so a file under it is still an orphan', $out64 );
ok( ! $fx_orphan( 'references/atlas-q.md' ), 'the longer filename SKILL.md really cites is reachable', $out64 );
ok( $fx_orphan( 'references/q.md' ), 'a citation BEGINS where a filename begins: "atlas-q.md" does not credit "q.md"', $out64 );
fx_rrmdir( $r64 );

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

echo "--- no run of the audit above printed a PHP diagnostic on its own output ---\n";
ok( array() === $GLOBALS['fx_diagnostics'], 'not one fixture run emitted a PHP warning/notice/deprecation into the audit output the gate reads', array_keys( $GLOBALS['fx_diagnostics'] ) );

/* -------------------------------------------------------------- report */

printf( "\n%d OK / %d FAIL\n", $pass, $fail );
exit( $fail ? 1 : 0 );
