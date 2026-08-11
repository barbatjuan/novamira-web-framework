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

$strict = in_array( '--strict', $argv, true );

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
function add( $level, $where, $msg ) {
	global $rows;
	$rows[] = array( $level, $where, $msg );
}
function slurp( $path ) {
	return str_replace( "\r\n", "\n", (string) file_get_contents( $path ) );
}

/* ---------------------------------------------------------------- skills */

$skill_dirs = array_filter( glob( $root . '/skills/*' ), 'is_dir' );
sort( $skill_dirs );

foreach ( $skill_dirs as $dir ) {
	$name = basename( $dir );
	$file = $dir . '/SKILL.md';
	if ( ! file_exists( $file ) ) {
		add( 'FAIL', $name, 'no SKILL.md' );
		continue;
	}
	$src = slurp( $file );

	/* --- frontmatter (CONTRIBUTING §2) --- */
	if ( ! preg_match( '/\A---\n(.*?)\n---\n(.*)\z/s', $src, $m ) ) {
		add( 'FAIL', $name, 'SKILL.md has no YAML frontmatter block' );
		continue;
	}
	list( , $fm, $body ) = $m;

	foreach ( array( 'name:', 'description:', 'license:', '  author:', '  version:' ) as $key ) {
		if ( false === strpos( $fm, $key ) ) {
			add( 'FAIL', $name, 'frontmatter missing "' . trim( $key ) . '"' );
		}
	}
	if ( preg_match( '/^name:\s*(\S+)/m', $fm, $nm ) && $nm[1] !== $name ) {
		add( 'FAIL', $name, 'frontmatter name "' . $nm[1] . '" does not match its directory' );
	}
	if ( false === strpos( $fm, 'Trigger:' ) ) {
		add( 'FAIL', $name, 'description carries no "Trigger:" words — the skill will not auto-activate' );
	}

	/* --- body budget (CONTRIBUTING §2: aim ~300, hard ceiling ~600) --- */
	$words = str_word_count( strip_tags( $body ) );
	if ( $words > 600 ) {
		add( 'FAIL', $name, "SKILL.md body is $words words, past the ~600 ceiling — move detail into references/" );
	} elseif ( $words > 300 ) {
		add( 'WARN', $name, "SKILL.md body is $words words, past the ~300 aim (ceiling 600)" );
	}

	/* --- build gate: the single highest-stakes property in the repo --- */
	$gate = ( false !== strpos( $src, 'Build gate' ) && false !== strpos( $src, 'explicit **yes**' ) );
	if ( in_array( $name, $WRITE_CAPABLE, true ) ) {
		if ( ! $gate ) {
			add( 'FAIL', $name, 'WRITE-CAPABLE SKILL WITH NO BLOCKING BUILD GATE — it can be reached by its own triggers and write unasked' );
		}
	} elseif ( ! $gate && preg_match( '/update_post_meta|wp_insert_post|wp_update_post|update_option/', $src ) ) {
		add( 'FAIL', $name, 'looks write-capable (calls a WordPress write) but carries no build gate, and is not in the known write-capable list' );
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
			add( 'FAIL', $name, 'points at "' . $raw . '", which does not exist' );
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
				add( 'WARN', $name, $base . ' is not mentioned by SKILL.md — dead weight, or a missing pointer' );
			}
		}
	}

	/* --- Hard Rules: does each one name what checks it? (CONTRIBUTING §3) ---
	 *
	 * Scoped to WRITE-CAPABLE skills on purpose. A knowledge skill's rule ("pick an archetype,
	 * don't assemble ad-hoc") is executed by the model reading it in context — there is no
	 * artifact to check afterwards, so demanding a verifier would report 38 rows nobody can act
	 * on, and an audit that fires on everything is one people learn to ignore. A rule in a skill
	 * that WRITES to a client's live site is different: a violation ships.
	 *
	 * Escape hatch, and the point of it: write `(no verifier: <reason>)` in the rule and it stops
	 * being reported. That is not a way to silence the audit — it is CONTRIBUTING §3's actual
	 * requirement, that an admitted gap be written down instead of left silent. */
	if ( preg_match( '/^## Hard Rules\n(.*?)(?=\n## |\z)/ms', $body, $hr ) ) {
		if ( in_array( $name, $WRITE_CAPABLE, true ) ) {
			foreach ( preg_split( '/\n(?=- )/', trim( $hr[1] ) ) as $rule ) {
				$rule = trim( $rule );
				if ( '' === $rule || '-' !== $rule[0] ) {
					continue;
				}
				if ( preg_match( '/no verifier:/i', $rule ) ) {
					continue;
				}
				$has_verifier = preg_match(
					/* "verif" not "verify": the rule that says "Verified by the step-4 grep" DOES
					   name its verifier, and an audit that cannot read its own success criterion
					   is the joke it was written to prevent. */
					'/es_[a-z_]+\(|house-rules|qa-review|audit|verdict|introspect|verif|measur|self-verifying|server-side|gotchas\.md/i',
					$rule
				);
				if ( ! $has_verifier ) {
					$short = preg_replace( '/\s+/', ' ', mb_substr( ltrim( $rule, '- ' ), 0, 84 ) );
					add( 'JUDGE', $name, 'Hard Rule names no verifier: "' . $short . '…"' );
				}
			}
		}
	} else {
		add( 'WARN', $name, 'no "## Hard Rules" section' );
	}

	/* --- warnings that reach nobody (CONTRIBUTING §3) --- */
	foreach ( glob( $dir . '/assets/*.php' ) as $php ) {
		$code  = slurp( $php );
		$lines = explode( "\n", $code );
		foreach ( $lines as $i => $line ) {
			if ( ! preg_match( '/(?<![\w>])error_log\s*\(/', $line ) ) {
				continue;
			}
			/* A docblock EXPLAINING why error_log() alone is not enough is not a call. */
			if ( preg_match( '#^\s*(\*|//|/\*|\#)#', $line ) ) {
				continue;
			}
			/* Paired with a stdout channel within a few lines either way? */
			$window = implode( "\n", array_slice( $lines, max( 0, $i - 4 ), 9 ) );
			if ( ! preg_match( '/\becho\b|es_warn\s*\(/', $window ) ) {
				add(
					'FAIL',
					$name,
					basename( $php ) . ':' . ( $i + 1 ) . ' error_log() with no stdout channel — the sandbox returns STDOUT, the PHP log is never fetched. Use es_warn()'
				);
			}
		}
	}
}

/* ------------------------------------------------------------- the agent */

foreach ( glob( $root . '/agents/*.md' ) as $agent ) {
	$name = basename( $agent, '.md' );
	$src  = slurp( $agent );

	/* "The orchestrator never gains CSS/HTML/PHP" (CONTRIBUTING §2). */
	if ( preg_match( '/```(php|css|html|js)|<\?php/i', $src ) ) {
		add( 'FAIL', $name, 'contains a code block — the agent thinks, the skills execute; move it into a skill asset' );
	}
	/* Every skill it routes to must exist. */
	preg_match_all( '/`([a-z][a-z0-9\-]{2,})`/', $src, $m );
	foreach ( array_unique( $m[1] ) as $maybe ) {
		if ( is_dir( $root . '/skills/' . $maybe ) ) {
			continue;
		}
		if ( in_array( $maybe, array_map( 'basename', $skill_dirs ), true ) ) {
			add( 'FAIL', $name, 'routes to skill "' . $maybe . '" which is missing' );
		}
	}
	foreach ( array_map( 'basename', $skill_dirs ) as $sk ) {
		if ( false === strpos( $src, '`' . $sk . '`' ) ) {
			add( 'WARN', $name, 'never mentions skill "' . $sk . '" — unroutable through the orchestrator' );
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
			add( 'FAIL', 'qa-review', 'house-rules.md:' . ( $i + 1 ) . ' row has no verdict source in its last column' );
		}
	}
} else {
	add( 'FAIL', 'qa-review', 'references/house-rules.md is missing — the house rules have no gate' );
}

/* ------------------------------------------------------------- offline suite */

if ( ! glob( $root . '/tests/*.php' ) ) {
	add( 'FAIL', 'tests', 'no offline test suite — the code that enforces the rules has nothing enforcing it' );
}

/* -------------------------------------------------------------- report */

$order = array( 'FAIL' => 0, 'WARN' => 1, 'JUDGE' => 2 );
usort(
	$rows,
	function ( $a, $b ) use ( $order ) {
		return $order[ $a[0] ] <=> $order[ $b[0] ] ?: strcmp( $a[1], $b[1] );
	}
);

$n = array( 'FAIL' => 0, 'WARN' => 0, 'JUDGE' => 0 );
foreach ( $rows as $r ) {
	$n[ $r[0] ]++;
	printf( "%-5s  %-22s  %s\n", $r[0], $r[1], $r[2] );
}
if ( ! $rows ) {
	echo "nothing to report\n";
}

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
