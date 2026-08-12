<?php
/**
 * NovaMira framework self-audit — the checks that need no judgement.
 *
 * Run:  php skills/framework-audit/assets/framework-audit.php           (exit 0 = no FAILs)
 *         …/framework-audit.php --strict                                (WARNs also fail)
 *         …/framework-audit.php --root=/path/to/checkout                (audit another tree)
 *
 * Lives in the skill's assets/ rather than a repo-level tools/ for a boring but load-bearing
 * reason: install.ps1 copies only skills/ and agents/, so a script anywhere else does not travel
 * with the skill that tells you to run it.
 *
 * It audits a CHECKOUT, never an install. Pointing it at ~/.claude was tried and cut: an install
 * holds every skill from every source, so it judged 34 unrelated skills against this repo's
 * CONTRIBUTING and produced 690 warnings about files nobody here owns. An audit that fires on
 * things you cannot fix is one people learn to ignore. Verifying an install still matters — that
 * is a diff against the repo, which is a different tool.
 *
 * Everything this framework verifies is about a BUILT SITE. Nothing verified the framework
 * itself, which is how "fewest containers" stayed violated through a whole build cycle and how
 * "one H1 per page" sat in wordpress-seo for versions with no checker anywhere. This closes that
 * loop for the mechanical half.
 *
 * Deliberately split by what a script can actually decide:
 *   FAIL  — objectively wrong. Broken reference, missing build gate, absent frontmatter field.
 *   WARN  — smells wrong, a human may have a reason. Over the word budget, orphaned file.
 *   JUDGE — a heuristic fired and a MODEL has to read it. Reported, never counted as a pass.
 *
 * A script that guesses at JUDGE rows and prints PASS is worse than no script, because it
 * launders an unknown into a green tick. skills/framework-audit/SKILL.md owns that half.
 */

$strict           = in_array( '--strict', $argv, true );
$show_row_types   = in_array( '--row-types', $argv, true );
$show_word_report = in_array( '--word-report', $argv, true );

/* ---------------------------------------------------------- row-type registry (D1'.6)
 *
 * Every add() call site below is named here. Two guarantees this buys, both proven by mutation
 * in tests/test-framework-audit.php rather than asserted in prose:
 *   1. add() rejects any ID absent from this list — exit(3), loud, not a silent skip. A call site
 *      cannot drift from the registry by typo or omission.
 *   2. The regression harness accumulates every ID it OBSERVES (via --row-types, below) across all
 *      its fixtures and diffs that set against this registry. A check with no fixture, or a fixture
 *      whose emitting branch gets deleted, turns the harness red — the exact CRITICAL that sank two
 *      prior slices of this change, closed by mechanism instead of discipline.
 */
const ROW_TYPES = array(
	'RT_NO_SKILL_MD'             => 'FAIL  — a skill directory has no SKILL.md',
	'RT_NO_FRONTMATTER'          => 'FAIL  — SKILL.md has no YAML frontmatter block',
	'RT_FRONTMATTER_MISSING_KEY' => 'FAIL  — frontmatter is missing a required key',
	'RT_NAME_MISMATCH'           => 'FAIL  — frontmatter name: does not match its directory',
	'RT_NO_TRIGGER'              => 'FAIL  — description carries no "Trigger:" words',
	'RT_BODY_OVER_600'           => 'FAIL  — SKILL.md body is past the ~600-word ceiling',
	'RT_BODY_OVER_300'           => 'WARN  — SKILL.md body is past the ~300-word aim',
	'RT_NO_BUILD_GATE'           => 'FAIL  — a write-capable skill has no blocking build gate',
	'RT_BROKEN_REFERENCE'        => 'FAIL  — SKILL.md points at a references/assets path that does not exist',
	'RT_ORPHAN_FILE'             => 'WARN  — a references/ or assets/ file is never mentioned by SKILL.md',
	'RT_NO_HARD_RULES'           => 'WARN  — SKILL.md has no "## Hard Rules" section',
	'RT_HARD_RULES_MISSING_WRITE' => 'FAIL  — a write-capable skill has no "## Hard Rules" section',
	/* D1' verifier-marker grammar (B1) — replaces the old vocabulary-substring
	   RT_HARD_RULE_NO_VERIFIER, which prose could satisfy by accident. See marker_parse(). */
	'RT_MARKER_ABSENT'           => 'JUDGE — a Hard Rule bullet names no verifier marker',
	'RT_MARKER_MULTIPLE'         => 'FAIL  — a Hard Rule bullet carries two or more verifier markers',
	'RT_MARKER_CASE'             => 'FAIL  — a verifier marker token is not the exact lowercase literal',
	'RT_MARKER_UNCLOSED'         => 'FAIL  — a verifier marker\'s opening paren is never closed',
	'RT_MARKER_TRAILING_TEXT'    => 'FAIL  — text follows a verifier marker\'s closing paren',
	'RT_MARKER_EMPTY'            => 'FAIL  — a verifier marker\'s payload is empty',
	'RT_MARKER_STOPWORD'         => 'FAIL  — a verifier marker\'s payload is a stop-word placeholder',
	'RT_MARKER_TOO_SHORT'        => 'FAIL  — a verifier marker\'s payload is under 12 characters',
	'RT_MARKER_OVERSIZE'         => 'FAIL  — a verifier marker\'s payload is over the 40-word cap',
	'RT_MARKER_TARGET_MISSING'   => 'FAIL  — a "(verifier: …)" marker names a target that does not exist',
	'RT_MARKER_MISLABEL'         => 'JUDGE — a "(no verifier: …)" marker names a target that DOES exist',
	'RT_MARKER_PROSE_ONLY'       => 'JUDGE — a "(verifier: …)" marker names no locatable target',
	'RT_MARKER_OUTSIDE_RULES'    => 'WARN  — a verifier-marker-shaped line sits outside "## Hard Rules"',
	'RT_ERRORLOG_NO_STDOUT'      => 'FAIL  — an error_log call has no paired stdout channel',
	'RT_WRITE_NOT_LISTED'        => 'FAIL  — code writes to WordPress but the skill is missing from $WRITE_CAPABLE',
	'RT_AGENT_CODE_BLOCK'        => 'FAIL  — an agent markdown file contains a code block',
	'RT_AGENT_ROUTE_MISSING'     => 'FAIL  — an agent routes to a skill that does not exist',
	'RT_AGENT_SKILL_UNMENTIONED' => 'WARN  — an agent never mentions an existing skill',
	'RT_HOUSERULES_NO_VERDICT'   => 'FAIL  — a house-rules.md row has no verdict source',
	'RT_HOUSERULES_MISSING'      => 'FAIL  — qa-review/references/house-rules.md is missing',
	'RT_NO_OFFLINE_TESTS'        => 'FAIL  — no offline test suite under tests/',
	'RT_GATE_LINE_UNREGISTERED'  => 'FAIL  — a tests/test-*.php file is absent from the CONTRIBUTING.md gate line',
	'RT_ROWTYPE_UNDOCUMENTED'    => 'FAIL  — a ROW_TYPES ID is not listed in CONTRIBUTING.md',
	'RT_PERS_CATALOG_MISSING'    => 'FAIL  — ux-design-system/references/design-personalities.md is missing',
	'RT_PERS_MISSING_FIELD'      => 'FAIL  — a personality block in design-personalities.md is missing a required field',
	'RT_PERS_ID_MISSING'         => 'FAIL  — a required personality ID is absent from design-personalities.md',
	'RT_TOKENS_HARDCODED_FONT'   => 'FAIL  — design-tokens.md still hardcodes an example font pairing',
	'RT_CATALOG_UNMENTIONED'     => 'FAIL  — ux-design-system/SKILL.md never mentions design-personalities.md',
	'RT_UXDS_NO_CAPA2_STEP'      => 'FAIL  — ux-design-system/SKILL.md has no CAPA 2 personality-recommender step',
);

/* --emit-row-types is static introspection of the script, not of an audited tree: it needs no
   --root and no CONTRIBUTING.md guard, so it is handled before either. */
if ( in_array( '--emit-row-types', $argv, true ) ) {
	foreach ( ROW_TYPES as $rt_id => $rt_desc ) {
		echo $rt_id . "\t" . $rt_desc . "\n";
	}
	exit( 0 );
}

/* Root = the tree that holds skills/ and agents/. Walk up rather than hardcoding a depth, so the
   same file works from the repo checkout and from an install. --root wins when given. */
$root = '';
foreach ( $argv as $a ) {
	if ( 0 === strpos( $a, '--root=' ) ) {
		$root = rtrim( substr( $a, 7 ), '/\\' );
	}
}
if ( '' === $root ) {
	$probe = __DIR__;
	for ( $i = 0; $i < 5; $i++ ) {
		if ( is_dir( $probe . '/skills' ) && is_dir( $probe . '/agents' ) ) {
			$root = $probe;
			break;
		}
		$probe = dirname( $probe );
	}
}
if ( '' === $root || ! is_dir( $root . '/skills' ) ) {
	fwrite( STDERR, "framework-audit: no skills/ + agents/ tree found. Pass --root=<checkout>.\n" );
	exit( 2 );
}
/* CONTRIBUTING.md is what distinguishes a checkout from an install directory. Refuse the latter
   rather than judging unrelated skills by this repo's rules — see the header. */
if ( ! file_exists( $root . '/CONTRIBUTING.md' ) ) {
	fwrite(
		STDERR,
		"framework-audit: \"$root\" has no CONTRIBUTING.md, so it is an install directory, not a\n"
		. "NovaMira checkout. This audits the repo. To check an install, diff it against the repo.\n"
	);
	exit( 2 );
}
echo 'framework-audit: ' . $root . "\n\n";

/* Skills that write to a live WordPress site. The canonical list is the orchestrator's
   "the build gate is also enforced skill-side" paragraph; it is repeated here so the script
   can check it, and cross-checked below so a NEW write-capable skill cannot slip past. */
$WRITE_CAPABLE = array( 'elementor-core', 'divi-core', 'woocommerce', 'wordpress-seo', 'wordpress-performance' );

$rows = array();
/* $id is a row-type ID from ROW_TYPES — see the block above. An ID this registry does not
   recognise is not a row, not a warning: it is a bug in the audit itself, so it stops the audit,
   loudly, rather than shipping a row nothing declared. */
function add( $id, $level, $where, $msg ) {
	global $rows;
	if ( ! array_key_exists( $id, ROW_TYPES ) ) {
		fwrite( STDERR, "framework-audit: add() called with unregistered row-type ID \"$id\" — register it in ROW_TYPES first.\n" );
		exit( 3 );
	}
	$rows[] = array( $id, $level, $where, $msg );
}
function slurp( $path ) {
	return str_replace( "\r\n", "\n", (string) file_get_contents( $path ) );
}

/* ---------------------------------------------------- verifier-marker grammar (D1', slice B1)
 *
 * Marker shape: own-line, terminal, case-sensitive — `(verifier: …)` or `(no verifier: …)`. The
 * token must OPEN the line; its closing `)` must be the LAST character of the bullet. Parsed from
 * a line array plus a paren-depth balance, never a regex over the whole bullet: a greedy
 * dot-matches-newline regex anchored at end-of-bullet accepts ANY bullet whose LAST character is
 * ")" and absorbs all intervening prose into the payload — proven twice by mutation on the
 * rejected first attempt at this slice (ending a sentence in a parenthetical is a constant habit
 * in these files). Depth counting finds the marker's OWN closing paren; anything after it is
 * trailing prose, not payload.
 */
function marker_parse( $rule ) {
	$lines   = explode( "\n", rtrim( $rule ) );
	$open    = array();
	$ci_only = 0;
	foreach ( $lines as $i => $l ) {
		if ( preg_match( '/^[ \t]*\((no[ \t]+)?verifier:/', $l, $m ) ) {
			/* isset(), not a bare cast: the "no " group is optional AND last, so on the AFFIRMATIVE
			   marker PHP drops it from $m entirely instead of filling it with "". The cast got the
			   right answer and raised "Undefined array key 1" on STDOUT doing it, once per marker,
			   into the same channel the gate and --word-report print. */
			$open[] = array( $i, isset( $m[1] ) && '' !== trim( $m[1] ) );
		} elseif ( preg_match( '/^[ \t]*\((no[ \t]+)?verifier:/i', $l ) ) {
			++$ci_only;
		}
	}
	if ( 0 === count( $open ) && $ci_only > 0 ) {
		return array( 'n' => 0, 'case_mismatch' => true );
	}
	if ( 1 !== count( $open ) ) {
		return array( 'n' => count( $open ) );
	}
	list( $li, $negated ) = $open[0];
	$tail  = implode( "\n", array_slice( $lines, $li ) );
	$start = strpos( $tail, '(' );
	$depth = 0;
	$close = -1;
	for ( $p = $start, $len = strlen( $tail ); $p < $len; $p++ ) {
		if ( '(' === $tail[ $p ] ) {
			++$depth;
		} elseif ( ')' === $tail[ $p ] && 0 === --$depth ) {
			$close = $p;
			break;
		}
	}
	$span = ( -1 === $close ) ? '' : substr( $tail, $start, $close - $start + 1 );
	preg_match( '/^\((?:no[ \t]+)?verifier:[ \t]*(.*)\)$/s', $span, $pm );
	return array(
		'n'        => 1,
		'negated'  => $negated,
		'span'     => $span,
		'closed'   => ( -1 !== $close ),
		'terminal' => ( $close === strlen( $tail ) - 1 ),
		'payload'  => isset( $pm[1] ) ? trim( $pm[1] ) : '',
	);
}

/* A short, single-line excerpt of a bullet for a row message — mirrors the truncation the old
   vocabulary check used, so messages stay scannable in a terminal. */
function marker_short( $rule ) {
	return preg_replace( '/\s+/', ' ', mb_substr( ltrim( $rule, '- ' ), 0, 84 ) );
}

/* D1'.5: function names via token_get_all(), never a regex over the raw file text. A name that
   appears only in a comment (T_COMMENT/T_DOC_COMMENT) or a string literal
   (T_CONSTANT_ENCAPSED_STRING) can never be T_FUNCTION's next T_STRING, so it is structurally
   unreachable here — the exact hole the rejected slice's regex collector had. */
function collect_function_names( $root ) {
	$names = array();
	foreach ( glob( $root . '/skills/*/assets/*.php' ) as $php ) {
		$expect = false;
		foreach ( token_get_all( slurp( $php ) ) as $tok ) {
			if ( ! is_array( $tok ) ) {
				if ( $expect && '&' !== $tok ) {
					$expect = false;
				}
				continue;
			}
			list( $id, $text ) = $tok;
			if ( T_FUNCTION === $id ) {
				$expect = true;
				continue;
			}
			if ( ! $expect ) {
				continue;
			}
			if ( T_WHITESPACE === $id || T_COMMENT === $id || T_DOC_COMMENT === $id ) {
				continue;
			}
			if ( T_STRING === $id ) {
				$names[ $text ] = true;
			}
			$expect = false;
		}
	}
	return $names;
}

/* D1'.4 shape 3: reuses the exact house-rules row shape the walk further below already carries
   ("row N" in the leading table column), so there is exactly one place that decides what a
   "house-rule row" looks like. */
function collect_house_rule_rows( $root ) {
	$rows = array();
	$file = $root . '/skills/qa-review/references/house-rules.md';
	if ( file_exists( $file ) ) {
		foreach ( explode( "\n", slurp( $file ) ) as $line ) {
			if ( preg_match( '/^\|\s*(\d+)\s*\|/', $line, $m ) ) {
				$rows[ (int) $m[1] ] = true;
			}
		}
	}
	return $rows;
}

/* D1'.4 shape 4: the numbered list under a skill's OWN "## Execution Steps" heading. [^\n]*
   tolerates a decorative tail — divi-core writes "## Execution Steps (validate each)". */
function collect_skill_steps( array $skill_dirs ) {
	$steps = array();
	foreach ( $skill_dirs as $dir ) {
		$name = basename( $dir );
		$file = $dir . '/SKILL.md';
		if ( ! file_exists( $file ) ) {
			continue;
		}
		if ( preg_match( '/^## Execution Steps\b[^\n]*\n(.*?)(?=\n## |\z)/ms', slurp( $file ), $m ) ) {
			preg_match_all( '/^(\d+)\.\s/m', $m[1], $sm );
			$steps[ $name ] = array_map( 'intval', $sm[1] );
		}
	}
	return $steps;
}

/* D1'.4: the four resolver shapes, tried in order. Returns null when the payload cites none of
   them — verdict RT_MARKER_PROSE_ONLY for an affirmative marker, silently accepted for a negated
   one (a gap explanation is prose by nature). Resolution itself is polarity-blind; the caller
   decides what a resolved/unresolved/absent result means for each polarity. */
function marker_resolve( $payload, $root, array $fn_names, array $house_rows, array $skill_steps, $own_skill ) {
	if ( preg_match( '/\bes_[a-z_]+\(/', $payload, $m ) ) {
		$fn = rtrim( $m[0], '(' );
		return array( 'shape' => 1, 'exists' => isset( $fn_names[ $fn ] ), 'target' => $fn . '()' );
	}
	if ( preg_match( '#`(tests/[\w\-./]+)`#', $payload, $m ) ) {
		/* The capture admits "." and "/", so it admits "..". Concatenated onto $root unchecked, a
		   climbing path probes the HOST filesystem: the FAIL is then satisfied by a file this
		   repository does not contain, and the same commit passes on one machine and fails on
		   another. A target that leaves the audited tree never counts as existing. */
		$inside = false === strpos( '/' . $m[1] . '/', '/../' );
		return array( 'shape' => 2, 'exists' => $inside && file_exists( $root . '/' . $m[1] ), 'target' => $m[1] );
	}
	if ( preg_match( '/(?:`qa-review`|house-rule)[^\d]{0,24}row\s+(\d+)/i', $payload, $m ) ) {
		return array( 'shape' => 3, 'exists' => isset( $house_rows[ (int) $m[1] ] ), 'target' => 'house-rules row ' . $m[1] );
	}
	if ( preg_match( '/(?:`([a-z0-9\-]+)`\s*)?\bstep-?\s*(\d+)\b/i', $payload, $m ) ) {
		$skill = ( isset( $m[1] ) && '' !== $m[1] ) ? $m[1] : $own_skill;
		$list  = isset( $skill_steps[ $skill ] ) ? $skill_steps[ $skill ] : array();
		return array( 'shape' => 4, 'exists' => in_array( (int) $m[2], $list, true ), 'target' => "$skill step " . $m[2] );
	}
	return null;
}

/* ---------------------------------------------------------------- skills */

$skill_dirs = array_filter( glob( $root . '/skills/*' ), 'is_dir' );
sort( $skill_dirs );

/* D1'.4/.5 collectors: walked once, handed to every skill's marker_resolve() call below — the
   tree does not change mid-audit, so there is no reason to rescan it per bullet. */
$FN_NAMES    = collect_function_names( $root );
$HOUSE_ROWS  = collect_house_rule_rows( $root );
$SKILL_STEPS = collect_skill_steps( $skill_dirs );
$word_report = array();

foreach ( $skill_dirs as $dir ) {
	$name = basename( $dir );
	$file = $dir . '/SKILL.md';
	if ( ! file_exists( $file ) ) {
		add( 'RT_NO_SKILL_MD', 'FAIL', $name, 'no SKILL.md' );
		continue;
	}
	$src = slurp( $file );

	/* --- frontmatter (CONTRIBUTING §2) --- */
	if ( ! preg_match( '/\A---\n(.*?)\n---\n(.*)\z/s', $src, $m ) ) {
		add( 'RT_NO_FRONTMATTER', 'FAIL', $name, 'SKILL.md has no YAML frontmatter block' );
		continue;
	}
	list( , $fm, $body ) = $m;

	foreach ( array( 'name:', 'description:', 'license:', '  author:', '  version:' ) as $key ) {
		if ( false === strpos( $fm, $key ) ) {
			add( 'RT_FRONTMATTER_MISSING_KEY', 'FAIL', $name, 'frontmatter missing "' . trim( $key ) . '"' );
		}
	}
	if ( preg_match( '/^name:\s*(\S+)/m', $fm, $nm ) && $nm[1] !== $name ) {
		add( 'RT_NAME_MISMATCH', 'FAIL', $name, 'frontmatter name "' . $nm[1] . '" does not match its directory' );
	}
	if ( false === strpos( $fm, 'Trigger:' ) ) {
		add( 'RT_NO_TRIGGER', 'FAIL', $name, 'description carries no "Trigger:" words — the skill will not auto-activate' );
	}

	/* --- Hard Rules markers (D1'.1-.5, .9): parsed BEFORE the body word budget, because a
	 * structurally valid marker span is excluded from the word count below.
	 *
	 * The grammar verdict rows stay scoped to WRITE-CAPABLE skills, exactly like the vocabulary
	 * check this replaces: a knowledge skill's rule is executed by the model reading it in
	 * context, there is no artifact to check afterward, and demanding a verifier there would
	 * report rows nobody can act on. A rule in a skill that WRITES to a client's live site is
	 * different: a violation ships. Word-budget stripping of a structurally valid span, however,
	 * applies to every skill uniformly — a marker is provenance, not an instruction, wherever it
	 * legitimately appears.
	 */
	$is_write_capable = in_array( $name, $WRITE_CAPABLE, true );
	$hard_rules_block = null;
	if ( preg_match( '/^## Hard Rules\n(.*?)(?=\n## |\z)/ms', $body, $hr ) ) {
		$hard_rules_block = $hr[1];
	}
	/* The verdict is driven by the BULLETS actually parsed, never by the heading: the lazy capture
	   above matches the empty string happily, so testing the block for null let a write-capable
	   skill past the FAIL by typing "## Hard Rules" and stopping — free, where the escape this
	   design priced costs a written reason. Rules the splitter cannot see (a "* " bullet) count as
	   absent for the same reason: fail closed, and say so. */
	$rules = array();
	foreach ( preg_split( '/\n(?=- )/', trim( (string) $hard_rules_block ) ) as $rule ) {
		$rule = trim( $rule );
		if ( '' !== $rule && '-' === $rule[0] ) {
			$rules[] = $rule;
		}
	}
	if ( array() === $rules ) {
		$how = 'no "## Hard Rules" section, or one with no "- " bullets under it';
		if ( $is_write_capable ) {
			add( 'RT_HARD_RULES_MISSING_WRITE', 'FAIL', $name, 'WRITE-CAPABLE skill states no Hard Rules — ' . $how );
		} else {
			add( 'RT_NO_HARD_RULES', 'WARN', $name, 'no Hard Rules — ' . $how );
		}
	}

	$valid_spans = array();
	if ( array() !== $rules ) {
		foreach ( $rules as $rule ) {
			$mp = marker_parse( $rule );
			if ( ! empty( $mp['case_mismatch'] ) ) {
				if ( $is_write_capable ) {
					add( 'RT_MARKER_CASE', 'FAIL', $name, marker_short( $rule ) . '… — marker token is not the exact lowercase "(verifier:"/"(no verifier:" literal' );
				}
				continue;
			}
			if ( 0 === $mp['n'] ) {
				if ( $is_write_capable ) {
					add( 'RT_MARKER_ABSENT', 'JUDGE', $name, 'Hard Rule names no verifier marker: "' . marker_short( $rule ) . '…"' );
				}
				continue;
			}
			if ( $mp['n'] >= 2 ) {
				if ( $is_write_capable ) {
					add( 'RT_MARKER_MULTIPLE', 'FAIL', $name, marker_short( $rule ) . '… — carries ' . $mp['n'] . ' verifier markers, exactly one is allowed' );
				}
				continue;
			}
			if ( ! $mp['closed'] ) {
				if ( $is_write_capable ) {
					add( 'RT_MARKER_UNCLOSED', 'FAIL', $name, marker_short( $rule ) . '… — verifier marker is never closed' );
				}
				continue;
			}
			if ( ! $mp['terminal'] ) {
				if ( $is_write_capable ) {
					add( 'RT_MARKER_TRAILING_TEXT', 'FAIL', $name, marker_short( $rule ) . '… — text follows the closing ")": the marker must be the last thing in the rule' );
				}
				continue;
			}
			/* Structurally valid (closed && terminal) regardless of what its payload says below —
			   free from the word budget either way (D1'.1), but only WITHIN the 40-word cap, which
			   is evaluated here, ABOVE the write-capable gate. The strip is what makes a marker
			   free, so a cap running only for write-capable skills is not a cap: it left the
			   knowledge skills an unmetered region the budget subtracts and no check reads. The
			   ROW stays scoped; the CAP does not. */
			$payload   = $mp['payload'];
			$too_large = str_word_count( $payload ) > 40;
			if ( ! $too_large ) {
				$valid_spans[] = $mp['span'];
			}
			if ( ! $is_write_capable ) {
				continue;
			}
			if ( '' === $payload ) {
				add( 'RT_MARKER_EMPTY', 'FAIL', $name, marker_short( $rule ) . '… — verifier marker payload is empty' );
				continue;
			}
			if ( in_array( mb_strtolower( $payload ), array( 'n/a', 'none', 'todo', 'tbd', 'pendiente', '-', 'x', '?', 'dunno' ), true ) ) {
				add( 'RT_MARKER_STOPWORD', 'FAIL', $name, marker_short( $rule ) . '… — verifier marker payload "' . $payload . '" is a placeholder, not a reason' );
				continue;
			}
			if ( mb_strlen( $payload ) < 12 ) {
				add( 'RT_MARKER_TOO_SHORT', 'FAIL', $name, marker_short( $rule ) . '… — verifier marker payload is under 12 characters' );
				continue;
			}
			if ( $too_large ) {
				add( 'RT_MARKER_OVERSIZE', 'FAIL', $name, marker_short( $rule ) . '… — verifier marker payload is ' . str_word_count( $payload ) . ' words, past the 40-word cap' );
				continue;
			}
			$resolved = marker_resolve( $payload, $root, $FN_NAMES, $HOUSE_ROWS, $SKILL_STEPS, $name );
			if ( $mp['negated'] ) {
				/* Shape 2 is exempt on purpose: "(no verifier: nothing runs this yet, closest is
				   `tests/test-foo.php`)" cites a NEIGHBOURING file as context for the gap — it does
				   not claim that file checks the rule. The other three shapes name the checker
				   itself, so if the checker exists the marker is simply mislabelled. */
				if ( $resolved && $resolved['exists'] && 2 !== $resolved['shape'] ) {
					add( 'RT_MARKER_MISLABEL', 'JUDGE', $name, marker_short( $rule ) . '… — "(no verifier: …)" names "' . $resolved['target'] . '", which DOES exist; use "(verifier: …)" instead' );
				}
			} elseif ( ! $resolved ) {
				add( 'RT_MARKER_PROSE_ONLY', 'JUDGE', $name, marker_short( $rule ) . '… — verifier marker names no locatable function, tests/ path, house-rule row or step' );
			} elseif ( ! $resolved['exists'] ) {
				add( 'RT_MARKER_TARGET_MISSING', 'FAIL', $name, marker_short( $rule ) . '… — verifier marker names "' . $resolved['target'] . '", which does not exist' );
			}
		}
	}

	/* A marker-shaped OPENER line outside "## Hard Rules" is not free: markers are provenance for
	   the rule they document, not a general licence, so a line shaped like one anywhere else in
	   the body is WARN'd and stays counted toward the budget below. */
	$rest_of_body = ( null !== $hard_rules_block ) ? str_replace( $hard_rules_block, '', $body ) : $body;
	foreach ( explode( "\n", $rest_of_body ) as $ln ) {
		if ( preg_match( '/^[ \t]*\((no[ \t]+)?verifier:/', $ln ) ) {
			add( 'RT_MARKER_OUTSIDE_RULES', 'WARN', $name, 'verifier-marker-shaped line outside "## Hard Rules": "' . trim( $ln ) . '"' );
		}
	}

	/* --- body budget (CONTRIBUTING §2: aim ~300, hard ceiling ~600) ---
	   Structurally valid marker spans are excluded (D1'.1): a marker documents what CHECKS a
	   rule, it is provenance for the audit and the reviewer, not an instruction the model
	   executes, so excluding it makes the measurement more accurate, not more lenient. */
	$budget_body = $body;
	foreach ( $valid_spans as $span ) {
		$budget_body = str_replace( $span, '', $budget_body );
	}
	$marker_words         = str_word_count( implode( ' ', $valid_spans ) );
	$words                = str_word_count( strip_tags( $budget_body ) );
	$word_report[ $name ] = array( $words, $marker_words );
	if ( $words > 600 ) {
		add( 'RT_BODY_OVER_600', 'FAIL', $name, "SKILL.md body is $words instruction words (+$marker_words marker), past the ~600 ceiling — move detail into references/" );
	} elseif ( $words > 300 ) {
		add( 'RT_BODY_OVER_300', 'WARN', $name, "SKILL.md body is $words instruction words (+$marker_words marker), past the ~300 aim (ceiling 600)" );
	}

	/* --- build gate: the single highest-stakes property in the repo --- */
	$gate = ( false !== strpos( $src, 'Build gate' ) && false !== strpos( $src, 'explicit **yes**' ) );
	if ( in_array( $name, $WRITE_CAPABLE, true ) ) {
		if ( ! $gate ) {
			add( 'RT_NO_BUILD_GATE', 'FAIL', $name, 'WRITE-CAPABLE SKILL WITH NO BLOCKING BUILD GATE — it can be reached by its own triggers and write unasked' );
		}
	}

	/* --- every path it points at must exist --- */
	preg_match_all( '#`([a-z0-9\-]+/)?(references|assets)/[\w\-./]+\.(md|php|html|mjs|js|json)`#i', $src, $refs, PREG_SET_ORDER );
	$seen = array();
	foreach ( $refs as $r ) {
		$raw = trim( $r[0], '`' );
		if ( isset( $seen[ $raw ] ) ) {
			continue;
		}
		$seen[ $raw ] = true;
		$target = $r[1] ? $root . '/skills/' . $raw : $dir . '/' . $raw;
		if ( ! file_exists( $target ) ) {
			add( 'RT_BROKEN_REFERENCE', 'FAIL', $name, 'points at "' . $raw . '", which does not exist' );
		}
	}

	/* --- files nobody points at --- */
	foreach ( array( 'references', 'assets' ) as $sub ) {
		foreach ( glob( $dir . '/' . $sub . '/*' ) as $f ) {
			if ( is_dir( $f ) ) {
				continue;
			}
			$base = $sub . '/' . basename( $f );
			if ( false === strpos( $src, basename( $f ) ) ) {
				add( 'RT_ORPHAN_FILE', 'WARN', $name, $base . ' is not mentioned by SKILL.md — dead weight, or a missing pointer' );
			}
		}
	}

	/* --- warnings that reach nobody (CONTRIBUTING §3) + write-capability from real code --- */
	$write_capable_hit = '';
	foreach ( glob( $dir . '/assets/*.php' ) as $php ) {
		$code  = slurp( $php );
		$lines = explode( "\n", $code );
		foreach ( $lines as $i => $line ) {
			/* A comment that only NAMES a token is not a call — shared by both checks below, so a
			 * note explaining what a function used to do never fires either one. This docblock is
			 * itself proof: every continuation line opens with " * " precisely so a comment about
			 * detection logic can never be mistaken for the code it describes. */
			$is_comment = preg_match( '#^\s*(\*|//|/\*|\#)#', $line );

			if ( ! $is_comment && preg_match( '/(?<![\w>])error_log\s*\(/', $line ) ) {
				/* Paired with a stdout channel within a few lines either way? */
				$window = implode( "\n", array_slice( $lines, max( 0, $i - 4 ), 9 ) );
				if ( ! preg_match( '/\becho\b|es_warn\s*\(/', $window ) ) {
					add(
						'RT_ERRORLOG_NO_STDOUT',
						'FAIL',
						$name,
						basename( $php ) . ':' . ( $i + 1 ) . ' error_log() with no stdout channel — the sandbox returns STDOUT, the PHP log is never fetched. Use es_warn()'
					);
				}
			}

			/* Write-capability, from what the code actually DOES, not what SKILL.md says about
			 * it. `\s*\(` is the load-bearing part: this file's own detection-regex literal below
			 * contains each token followed by "|" or ")", never "(", so it cannot self-flag. */
			if ( ! $is_comment && ! $write_capable_hit
				&& preg_match( '/\b(update_post_meta|wp_insert_post|wp_update_post|update_option|es_save_page|es_save_theme_part)\s*\(/', $line )
			) {
				$write_capable_hit = basename( $php ) . ':' . ( $i + 1 );
			}
		}
	}
	if ( $write_capable_hit && ! in_array( $name, $WRITE_CAPABLE, true ) ) {
		add( 'RT_WRITE_NOT_LISTED', 'FAIL', $name, 'writes to WordPress (' . $write_capable_hit . ') but is not in the write-capable list' );
	}
}

/* --word-report is B1's deliverable to B2: B2's acceptance test is that these two columns are
   byte-identical before and after its migration (markers are excluded, and the migration is a
   pure line addition, so the number is invariant by construction). Exits before the normal
   FAIL/WARN/JUDGE report and the agent/qa-review/tests walk below -- this is introspection of the
   skill tree already walked above, not another kind of audit run. */
if ( $show_word_report ) {
	foreach ( $word_report as $rep_name => $rep ) {
		printf( "%s\t%d\t%d\n", $rep_name, $rep[0], $rep[1] );
	}
	exit( 0 );
}

/* ------------------------------------------------------------- the agent */

foreach ( glob( $root . '/agents/*.md' ) as $agent ) {
	$name = basename( $agent, '.md' );
	$src  = slurp( $agent );

	/* "The orchestrator never gains CSS/HTML/PHP" (CONTRIBUTING §2). */
	if ( preg_match( '/```(php|css|html|js)|<\?php/i', $src ) ) {
		add( 'RT_AGENT_CODE_BLOCK', 'FAIL', $name, 'contains a code block — the agent thinks, the skills execute; move it into a skill asset' );
	}
	/* Every skill it routes to must exist. Was a tautology: it only FAILed when $maybe was ALSO
	   in the set of dirs that exist, which the `is_dir()` check right above already guarantees
	   false for. Inverted so a missing routing target is actually reported. */
	preg_match_all( '/`([a-z][a-z0-9\-]{2,})`/', $src, $m );
	foreach ( array_unique( $m[1] ) as $maybe ) {
		if ( is_dir( $root . '/skills/' . $maybe ) ) {
			continue;
		}
		add( 'RT_AGENT_ROUTE_MISSING', 'FAIL', $name, 'routes to skill "' . $maybe . '" which is missing' );
	}
	foreach ( array_map( 'basename', $skill_dirs ) as $sk ) {
		if ( false === strpos( $src, '`' . $sk . '`' ) ) {
			add( 'RT_AGENT_SKILL_UNMENTIONED', 'WARN', $name, 'never mentions skill "' . $sk . '" — unroutable through the orchestrator' );
		}
	}
}

/* ------------------------------------------------- qa-review house rules */

$hr_file = $root . '/skills/qa-review/references/house-rules.md';
if ( file_exists( $hr_file ) ) {
	foreach ( explode( "\n", slurp( $hr_file ) ) as $i => $line ) {
		if ( ! preg_match( '/^\|\s*\d+\s*\|/', $line ) ) {
			continue;
		}
		if ( ! preg_match( '/\|\s*\*\*[^|]*(auto|eyes|measured|manual)[^|]*\*\*\s*\|\s*$/i', rtrim( $line ) ) ) {
			add( 'RT_HOUSERULES_NO_VERDICT', 'FAIL', 'qa-review', 'house-rules.md:' . ( $i + 1 ) . ' row has no verdict source in its last column' );
		}
	}
} else {
	add( 'RT_HOUSERULES_MISSING', 'FAIL', 'qa-review', 'references/house-rules.md is missing — the house rules have no gate' );
}

/* --------------------------------------- ux-design-system personality catalog */

$PERS_IDS = array(
	'PERS-EDITORIAL', 'PERS-BOLD-STARTUP', 'PERS-MINIMAL-SWISS', 'PERS-WARM-BOUTIQUE',
	'PERS-CORPORATE-TRUST', 'PERS-FASHION-EDIT', 'PERS-TECH-PRECISION', 'PERS-PERFORMANCE-ENERGY',
);
$PERS_FIELDS = array( 'Fits', 'Typography', 'Color mood', 'Radius & shadow', 'Motion intensity', 'Imagery', 'Card recipe' );

$pers_file = $root . '/skills/ux-design-system/references/design-personalities.md';
if ( ! file_exists( $pers_file ) ) {
	add( 'RT_PERS_CATALOG_MISSING', 'FAIL', 'ux-design-system', 'references/design-personalities.md is missing — the personality catalog has no file' );
} else {
	$pers_src = slurp( $pers_file );
	$blocks   = preg_split( '/(?=^### `PERS-[A-Z-]+`)/m', $pers_src );
	$found    = array();
	foreach ( $blocks as $block ) {
		if ( ! preg_match( '/^### `(PERS-[A-Z-]+)`/', $block, $hm ) ) {
			continue;
		}
		$pid           = $hm[1];
		$found[ $pid ] = true;
		foreach ( $PERS_FIELDS as $field ) {
			if ( false === strpos( $block, '**' . $field . ':**' ) ) {
				add( 'RT_PERS_MISSING_FIELD', 'FAIL', 'ux-design-system', 'design-personalities.md: ' . $pid . ' is missing required field "' . $field . '"' );
			}
		}
	}
	foreach ( $PERS_IDS as $pid ) {
		if ( ! isset( $found[ $pid ] ) ) {
			add( 'RT_PERS_ID_MISSING', 'FAIL', 'ux-design-system', 'design-personalities.md is missing personality "' . $pid . '"' );
		}
	}
}

/* Regression guard: the exact drift that made every build converge on the same look — a single
   named font example with no alternative anywhere in the skill. */
$dt_file = $root . '/skills/ux-design-system/references/design-tokens.md';
if ( file_exists( $dt_file ) ) {
	$dt_src = slurp( $dt_file );
	foreach ( array( 'Space Grotesk', 'Manrope' ) as $hardcoded ) {
		if ( false !== strpos( $dt_src, $hardcoded ) ) {
			add( 'RT_TOKENS_HARDCODED_FONT', 'FAIL', 'ux-design-system', 'design-tokens.md still hardcodes "' . $hardcoded . '" as an example font — move concrete pairings into design-personalities.md' );
		}
	}
}

$uxds_skill = $root . '/skills/ux-design-system/SKILL.md';
if ( file_exists( $uxds_skill ) ) {
	$uxds_src = slurp( $uxds_skill );
	if ( false === strpos( $uxds_src, 'design-personalities.md' ) ) {
		add( 'RT_CATALOG_UNMENTIONED', 'FAIL', 'ux-design-system', 'SKILL.md never mentions design-personalities.md — the personality catalog is unreachable from the skill' );
	}
	if ( false === strpos( $uxds_src, 'CAPA 2' ) ) {
		add( 'RT_UXDS_NO_CAPA2_STEP', 'FAIL', 'ux-design-system', 'SKILL.md has no CAPA 2 recommender step for picking a personality' );
	}
}

/* ------------------------------------------------------------- offline suite */

if ( ! glob( $root . '/tests/*.php' ) ) {
	add( 'RT_NO_OFFLINE_TESTS', 'FAIL', 'tests', 'no offline test suite — the code that enforces the rules has nothing enforcing it' );
}

/* ------------------------------------------------- gate self-registration */

/* CONTRIBUTING.md's testing gate is a static, hand-typed && chain, not a glob — the exact hole
 * that lets a brand-new tests/test-*.php file silently never run. This turns that discipline
 * into a verifier: every tests/test-*.php must be named on the gate line, or it FAILs. */
$contributing_file = $root . '/CONTRIBUTING.md';
if ( file_exists( $contributing_file ) ) {
	$contrib_src = slurp( $contributing_file );
	foreach ( glob( $root . '/tests/test-*.php' ) as $t ) {
		$base = basename( $t );
		if ( false === strpos( $contrib_src, 'tests/' . $base ) ) {
			add( 'RT_GATE_LINE_UNREGISTERED', 'FAIL', 'CONTRIBUTING.md', 'gate line never runs "' . $base . '" — a new test file must join the && chain or it silently never runs' );
		}
	}

	/* ------------------------------------------------ row-type doc-sync (D1'.7)
	 *
	 * Mirrors the gate-self-registration check right above: a new FAIL/WARN/JUDGE mode cannot ship
	 * without a line in CONTRIBUTING.md naming it. Reuses the same ROW_TYPES registry the coverage
	 * assertion in tests/test-framework-audit.php reads via --emit-row-types, so there is exactly
	 * one place a row type is declared, never two that can drift apart. */
	foreach ( ROW_TYPES as $rt_id => $rt_desc ) {
		if ( false === strpos( $contrib_src, $rt_id ) ) {
			add( 'RT_ROWTYPE_UNDOCUMENTED', 'FAIL', 'CONTRIBUTING.md', 'row type "' . $rt_id . '" is declared in ROW_TYPES but never documented in CONTRIBUTING.md' );
		}
	}
}

/* -------------------------------------------------------------- report */

/* $r is now [ id, level, where, msg ] — id threaded through for --row-types, ignored otherwise. */
$order = array( 'FAIL' => 0, 'WARN' => 1, 'JUDGE' => 2 );
usort(
	$rows,
	function ( $a, $b ) use ( $order ) {
		return $order[ $a[1] ] <=> $order[ $b[1] ] ?: strcmp( $a[2], $b[2] );
	}
);

$n = array( 'FAIL' => 0, 'WARN' => 0, 'JUDGE' => 0 );
foreach ( $rows as $r ) {
	list( $rt_id, $level, $where, $msg ) = $r;
	$n[ $level ]++;
	if ( $show_row_types ) {
		printf( "%-28s  %-5s  %-22s  %s\n", $rt_id, $level, $where, $msg );
	} else {
		printf( "%-5s  %-22s  %s\n", $level, $where, $msg );
	}
}
if ( ! $rows ) {
	echo "nothing to report\n";
}

/* Stdout contract, and it has a reader: `fx_counts()` in tests/test-framework-audit.php parses
   this exact line with /(\d+) FAIL \/ (\d+) WARN \/ (\d+) JUDGE/. Reword the format without
   updating that regex and every assertion keyed off these counts stops testing anything —
   quietly, which is the failure this whole file exists to catch. */
printf(
	"\n%d FAIL / %d WARN / %d JUDGE across %d skills + %d agent(s)\n",
	$n['FAIL'],
	$n['WARN'],
	$n['JUDGE'],
	count( $skill_dirs ),
	count( glob( $root . '/agents/*.md' ) )
);
if ( $n['JUDGE'] ) {
	echo "JUDGE rows are NOT passes. A model has to read each one and decide whether the rule\n"
		. "really has no verifier or the heuristic simply missed it — see skills/framework-audit/SKILL.md.\n";
}

exit( ( $n['FAIL'] || ( $strict && $n['WARN'] ) ) ? 1 : 0 );
