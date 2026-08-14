# Design Personalities Catalog Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Give `ux-design-system` a curated catalog of 8 visual personalities, orthogonal to
`web-templates`' structural archetypes, so two projects with the same architecture no longer ship
visually identical.

**Architecture:** New CAPA 1 file `design-personalities.md` (8 `PERS-*` entries, concrete
typography/color-mood/radius/motion/imagery/card values). New CAPA 2 recommender step in
`ux-design-system/SKILL.md`. CAPA 3 toggles: retire `TGL-STYLE`, add `TGL-IMAGERY` /
`TGL-MOTION-INTENSITY`. `framework-audit.php` gains 3 deterministic checks so this cannot silently
rot back to a single hardcoded default.

**Tech Stack:** Markdown skill references (Claude Code skills), PHP 8 (framework-audit.php, no
framework, plain `assert`-style script + fixture-based test harness in `tests/test-framework-audit.php`).

## Global Constraints

- New builds only — no migration path for already-delivered sites (spec Non-goals).
- No changes to `elementor-core`, `divi-core`, or `html-mockup` — output contract unchanged (spec
  Downstream section).
- Catalog model, exactly 8 personalities to start — not free-form improvisation (spec Non-goals).
- Every new Hard Rule / mechanic gets a verifier in `framework-audit.php`, per this repo's own
  `framework-audit` skill rule ("every rule has a verifier, or an admitted gap").
- SKILL.md body word budget: WARN over ~300 words, FAIL over ~600 (enforced by
  `framework-audit.php`, already true for all 11 existing skills per the 2026-08-11 audit).
- Commits: conventional commits, no AI co-author attribution.
- **Branch note (discovered at execution setup, 2026-08-12):** this plan was drafted by reading
  `framework-audit.php` off a checkout that had `fix/audit-truthfulness` (an unrelated,
  in-progress, unmerged branch) checked out. This branch (`feat/design-personalities-catalog`) was
  cut from `main`, which predates that branch's commit `1ac7992` ("give the self-audit its first
  regression harness") — so `tests/test-framework-audit.php` does not exist here yet, and
  `framework-audit.php` lacks that commit's `check_rule_markers()` / gate-self-registration
  additions. The qa-review house-rules block (this plan's insertion anchor) is untouched by that
  commit and is identical on both branches — Task 1 below is corrected to CREATE
  `tests/test-framework-audit.php` fresh, self-contained, scoped only to this change's checks,
  rather than extend a file that doesn't exist on this branch. This will produce an ordinary,
  independently-resolvable merge conflict in both files whenever `fix/audit-truthfulness` and this
  branch both land on `main` — expected, not a defect in either branch.

---

### Task 1: `framework-audit.php` checks + a fresh regression harness for the personality catalog

**Files:**
- Modify: `skills/framework-audit/assets/framework-audit.php` (insert after the qa-review
  house-rules block, i.e. after the closing `}` at the end of that section — currently ending
  `add( 'FAIL', 'qa-review', 'references/house-rules.md is missing — the house rules have no
  gate' );\n}` — and before the `/* ------------------------------------------------- offline
  suite */` comment)
- Create: `tests/test-framework-audit.php` (does NOT exist on this branch — see the plan's Global
  Constraints "Branch note" — self-contained subprocess-fixture harness, scoped only to the checks
  this task adds)
- Modify: `CONTRIBUTING.md` (its testing gate line is the static chain
  `php skills/framework-audit/assets/framework-audit.php && php tests/test-container-hygiene.php`
  — append ` && php tests/test-framework-audit.php`)

**Interfaces:**
- Produces: three new deterministic checks feeding the existing `add($level, $where, $msg)` /
  report pipeline — no new output format. Exact FAIL message substrings other tasks must satisfy:
  - `'references/design-personalities.md is missing'`
  - `'is missing personality "PERS-...'`
  - `': PERS-... is missing required field "..."'` (fields: `Fits`, `Typography`, `Color mood`,
    `Radius & shadow`, `Motion intensity`, `Imagery`, `Card recipe`)
  - `'still hardcodes "Space Grotesk"'` / `'still hardcodes "Manrope"'`
  - `'SKILL.md never mentions design-personalities.md'`
  - `'SKILL.md has no CAPA 2 recommender step'`
- Consumes: nothing new — reads the same `slurp()`, `add()`, `$root` already defined earlier in
  the file.

- [ ] **Step 1: Write the failing test file, `tests/test-framework-audit.php`**

  This file does not exist on this branch yet. Create it in full:

  ```php
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
  ```

- [ ] **Step 2: Run the test harness and confirm the new scenarios FAIL (checks don't exist yet)**

  Run: `php tests/test-framework-audit.php`
  Expected: the positive-control scenario (`$r1`) already prints `OK` — the audit doesn't inspect
  `design-personalities.md` at all yet, so a conforming fixture trivially shows 0 FAIL either way.
  The 5 defect scenarios (`$r2`-`$r6`) print `FAIL` under their `ok(has(...))` assertions — each
  checks for a specific FAIL message the audit does not produce until Step 3. The final line's
  `FAIL` count is `> 0`.

- [ ] **Step 3: Implement the 3 checks in `framework-audit.php`**

  Insert this block into `skills/framework-audit/assets/framework-audit.php`, between the
  qa-review house-rules block's closing `}` and the
  `/* ------------------------------------------------- offline suite */` comment:

  ```php
  /* --------------------------------------- ux-design-system personality catalog */

  $PERS_IDS = array(
  	'PERS-EDITORIAL', 'PERS-BOLD-STARTUP', 'PERS-MINIMAL-SWISS', 'PERS-WARM-BOUTIQUE',
  	'PERS-CORPORATE-TRUST', 'PERS-FASHION-EDIT', 'PERS-TECH-PRECISION', 'PERS-PERFORMANCE-ENERGY',
  );
  $PERS_FIELDS = array( 'Fits', 'Typography', 'Color mood', 'Radius & shadow', 'Motion intensity', 'Imagery', 'Card recipe' );

  $pers_file = $root . '/skills/ux-design-system/references/design-personalities.md';
  if ( ! file_exists( $pers_file ) ) {
  	add( 'FAIL', 'ux-design-system', 'references/design-personalities.md is missing — the personality catalog has no file' );
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
  				add( 'FAIL', 'ux-design-system', 'design-personalities.md: ' . $pid . ' is missing required field "' . $field . '"' );
  			}
  		}
  	}
  	foreach ( $PERS_IDS as $pid ) {
  		if ( ! isset( $found[ $pid ] ) ) {
  			add( 'FAIL', 'ux-design-system', 'design-personalities.md is missing personality "' . $pid . '"' );
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
  			add( 'FAIL', 'ux-design-system', 'design-tokens.md still hardcodes "' . $hardcoded . '" as an example font — move concrete pairings into design-personalities.md' );
  		}
  	}
  }

  $uxds_skill = $root . '/skills/ux-design-system/SKILL.md';
  if ( file_exists( $uxds_skill ) ) {
  	$uxds_src = slurp( $uxds_skill );
  	if ( false === strpos( $uxds_src, 'design-personalities.md' ) ) {
  		add( 'FAIL', 'ux-design-system', 'SKILL.md never mentions design-personalities.md — the personality catalog is unreachable from the skill' );
  	}
  	if ( false === strpos( $uxds_src, 'CAPA 2' ) ) {
  		add( 'FAIL', 'ux-design-system', 'SKILL.md has no CAPA 2 recommender step for picking a personality' );
  	}
  }
  ```

- [ ] **Step 4: Run the test harness again and confirm every scenario passes**

  Run: `php tests/test-framework-audit.php`
  Expected: last line reads `... OK / 0 FAIL`. All 6 scenarios print `OK`.

- [ ] **Step 5: Register the new test on `CONTRIBUTING.md`'s gate line**

  In `CONTRIBUTING.md`, find the line:

  ```
  php skills/framework-audit/assets/framework-audit.php && php tests/test-container-hygiene.php
  ```

  Replace it with:

  ```
  php skills/framework-audit/assets/framework-audit.php && php tests/test-container-hygiene.php && php tests/test-framework-audit.php
  ```

- [ ] **Step 6: Run the real audit against the actual repo — expect new FAILs (content doesn't exist yet)**

  Run: `php skills/framework-audit/assets/framework-audit.php`
  Expected: exit 1, with new FAIL rows for `ux-design-system` naming the missing
  `design-personalities.md` and the missing `CAPA 2` step — this is correct at this point in the
  plan; Tasks 2-4 clear them.

- [ ] **Step 7: Commit**

  ```bash
  git add skills/framework-audit/assets/framework-audit.php tests/test-framework-audit.php CONTRIBUTING.md
  git commit -m "test(framework-audit): verify the design-personalities catalog exists and is complete"
  ```

---

### Task 2: Write `design-personalities.md` (CAPA 1 catalog)

**Files:**
- Create: `skills/ux-design-system/references/design-personalities.md`

**Interfaces:**
- Consumes: the field-label format Task 1's checker parses (`### \`PERS-ID\` — Name` heading,
  then `**Field:**` bold labels for `Fits`, `Typography`, `Color mood`, `Radius & shadow`,
  `Motion intensity`, `Imagery`, `Card recipe`).
- Produces: `references/design-personalities.md`, pointed at by Task 4 (`SKILL.md`) and Task 5
  (`web-templates/design-system.md`).

- [ ] **Step 1: Write the file**

  Create `skills/ux-design-system/references/design-personalities.md`:

  ```markdown
  # Design Personalities (CAPA 1)

  8 curated visual languages. Orthogonal to the structural archetype `web-templates` resolves —
  the SAME `TPL-*` can ship under any of these. `ux-design-system` CAPA 2 picks one per project
  from brand signals + the client's own references (never re-asked here). Roles and the shared
  spacing/breakpoint SCALE come from `web-templates/references/design-system.md` and
  `references/design-tokens.md`; this file supplies the concrete VALUES within those roles.

  Radius/motion ranges below stay inside the scale and curve those two files already define
  (`cubic-bezier(.22,1,.36,1)`, the documented duration/lift ranges, the existing radius steps) —
  a personality tunes which point on that scale it lands on, never invents new physics.

  ### `PERS-EDITORIAL` — Editorial

  **Fits:** Brands with heritage, prestige, or a story to lean on — publishers, galleries,
  high-end services.

  **Typography:** `--font-primary` a high-contrast serif with real character (dramatic thick/thin
  strokes) for headings; `--font-secondary` a clean humanist sans for body. Large `--fs-h1`,
  tight line-height, letter-spaced eyebrow label.

  **Color mood:** dominant near-white OR near-black (pick one brand-wide, never split), contrast
  pushed to true near-black ink, accent restrained — a muted tone reads more premium here than a
  bright saturated one. Follow `design-tokens.md`'s derivation steps; keep the accent quiet.

  **Radius & shadow:** softest step of the existing scale on cards/containers; buttons/inputs
  sharper. Shadows near-absent — separation comes from whitespace and a hairline border.

  **Motion intensity:** slower than default — durations toward the top of the documented range
  (`.5s`–`.7s` as the floor), lift capped at `-4px`. Nothing should feel quick.

  **Imagery:** full-bleed or dramatically cropped photo-editorial framing. Scrims used sparingly —
  trust the photo's own contrast.

  **Card recipe:** minimal chrome — image, thin rule, text. No icon chips, no colored blocks.

  ### `PERS-BOLD-STARTUP` — Bold Startup

  **Fits:** Young SaaS, DTC brands that want to feel fast and confident.

  **Typography:** heavy geometric sans for both roles (may be one family at different weights),
  700+ weight headings, punchy.

  **Color mood:** dominant white/near-white, one HIGH-saturation accent — the loudest the
  one-accent rule allows — contrast near-black.

  **Radius & shadow:** mid-to-large radii on cards, visible soft shadow even at rest — the
  "floating card" look.

  **Motion intensity:** at or slightly above default — `.35s` color, full `-6px` lift,
  `scale(1.045)` image hover. Never stack all three intensifiers at once (`motion.md`'s own
  warning against `.35s` + `-6px` + `scale(1.06)` together still applies).

  **Imagery:** bright, high-key product/lifestyle photography or bold flat illustration; can mix.

  **Card recipe:** the existing premium feature card from `motion.md` as-is — accent circular
  icon chip, top accent bar reveal on hover.

  ### `PERS-MINIMAL-SWISS` — Minimal Swiss

  **Fits:** Precision, data, or product-first brands (tools, technical services) where restraint
  reads as competence.

  **Typography:** one neutral grotesque family for both roles, regular-to-medium weight even in
  headings — never a decorative display face.

  **Color mood:** near-monochrome — dominant/contrast do almost all the work, accent used at the
  smallest surface area the one-accent rule allows.

  **Radius & shadow:** 0–4px everywhere, no shadows at rest; hover uses a border/underline change
  instead of lift where possible.

  **Motion intensity:** near-imperceptible — shortest documented durations, `-4px` lift max, no
  image zoom on hover.

  **Imagery:** documentary/product photography shot straight-on, or none — data/typographic
  composition carries the page instead.

  **Card recipe:** hairline border only, no icon chips, no background tint — grid alignment
  carries the hierarchy.

  ### `PERS-WARM-BOUTIQUE` — Warm Boutique

  **Fits:** Artisanal, local, or hospitality-adjacent brands where "handmade" is the story.

  **Typography:** rounded humanist sans or a friendly serif for `--font-primary`, softer weight
  than Bold Startup; body slightly larger than default for easy reading.

  **Color mood:** warm dominant (cream/off-white, not pure white), earth-tone or pastel accent,
  contrast pushed to a warm near-black (brown-black, not blue-black).

  **Radius & shadow:** 12–20px across cards and containers (the softest end of the existing
  scale); soft, warm-tinted shadow, not neutral grey.

  **Motion intensity:** soft and organic — duration stretched slightly past default, gentle lift,
  no sharp snaps.

  **Imagery:** warm-toned photography or hand-drawn/watercolor illustration accents; imperfection
  reads as authenticity here, unlike Editorial's polish.

  **Card recipe:** rounded image corners matching the container radius, soft background tint
  block behind text instead of a hard border.

  ### `PERS-CORPORATE-TRUST` — Corporate Trust

  **Fits:** B2B, professional services, anything selling credibility over excitement.

  **Typography:** institutional sans, conservative, consistent weight discipline, no display
  flourishes.

  **Color mood:** dominant white, contrast a deep blue or blue-grey (not pure black), accent
  restrained and cool.

  **Radius & shadow:** medium radii (mid-scale), light shadow only on interactive elements, not
  on static content cards.

  **Motion intensity:** unmodified `motion.md` defaults — this personality earns trust by not
  drawing attention to its own interactions.

  **Imagery:** sober photography (real work contexts, not stock-smiling), icon-led service/process
  sections, minimal illustration.

  **Card recipe:** the standard premium feature card, icon chip in the cool accent, restrained
  hover (lift only, no icon color shift).

  ### `PERS-FASHION-EDIT` — Fashion Edit

  **Fits:** Apparel and fashion e-commerce competing on visual authority, not price.

  **Typography:** a fine elegant serif or a tall elegant condensed sans for `--font-primary` at a
  large size; body sans stays quiet and small.

  **Color mood:** black and white as dominant/contrast, ONE seasonal accent that can rotate per
  campaign — still only one at a time, never a second permanent color.

  **Radius & shadow:** near-0 radii throughout — square imagery, square buttons. No shadows.

  **Motion intensity:** the slowest in the catalog — reveal-style transitions, long fades, lift
  near 0, rely on opacity/scale instead.

  **Imagery:** full-bleed, uncropped or minimally cropped photography — never a tight
  product-only crop.

  **Card recipe:** image-dominant, price/name appear on hover or below with generous whitespace —
  no borders, no background.

  ### `PERS-TECH-PRECISION` — Tech Precision

  **Fits:** Electronics, gadgets, anything selling specs and precision engineering.

  **Typography:** geometric sans for `--font-primary`; a monospace family introduced specifically
  for spec tables/numbers — the one case in the catalog where a third family-ish element is
  allowed, scoped only to data, never headings/body.

  **Color mood:** native dark mode — `--c-bg` near-black, `--c-text` near-white, ONE electric
  accent (blue, green, or violet) reading as "on" against the dark field.

  **Radius & shadow:** small radii (Minimal Swiss's sharp end), but WITH an accent-tinted glow on
  hover instead of a neutral drop shadow.

  **Motion intensity:** fast and precise — shorter durations, sharp easing feel, small confident
  lift.

  **Imagery:** product photography on dark/gradient backgrounds; reuse `motion.md`'s glass recipe
  behind hero content, tinted dark.

  **Card recipe:** dark card surface, accent glow border on hover instead of lift, spec rows in
  the monospace family.

  ### `PERS-PERFORMANCE-ENERGY` — Performance Energy

  **Fits:** Sports, activewear, anything selling motion and intensity.

  **Typography:** condensed/athletic display face for `--font-primary` (tall, bold, slightly
  compressed), neutral sans for body so long copy stays readable.

  **Color mood:** dominant white or near-black (per brand), ONE highly saturated accent
  (red/orange/electric) used aggressively on CTAs.

  **Radius & shadow:** small-to-medium radii; sections use angled/diagonal dividers (clip-path or
  skewed edge) instead of the flat section-edge convention used elsewhere in the catalog — the
  one personality with a scoped exception to that convention, not a violation of it (spacing
  rhythm and section padding stay intact, only the edge shape changes).

  **Motion intensity:** fastest and snappiest in the catalog — short durations, larger lift than
  default, image zoom pushed toward the top of the documented range.

  **Imagery:** dynamic action photography with motion-blur crops, high-contrast grading.

  **Card recipe:** bold accent-colored CTA overlay on hover, sharp diagonal accent bar (echoing
  the section dividers) instead of the default straight top-bar reveal.
  ```

- [ ] **Step 2: Verify the personality-catalog FAILs are gone**

  Run: `php skills/framework-audit/assets/framework-audit.php`
  Expected: no FAIL rows mentioning `design-personalities.md is missing`, `is missing personality`,
  or `is missing required field` (rows about the missing CAPA 2 step in SKILL.md are still
  expected until Task 4).

- [ ] **Step 3: Commit**

  ```bash
  git add skills/ux-design-system/references/design-personalities.md
  git commit -m "feat(ux-design-system): add the 8-personality visual catalog"
  ```

---

### Task 3: Clean up `design-tokens.md` — drop the hardcoded font example

**Files:**
- Modify: `skills/ux-design-system/references/design-tokens.md`

**Interfaces:**
- Consumes: `design-personalities.md` (Task 2) as the new pointer target.
- Produces: a `design-tokens.md` with no `Space Grotesk` / `Manrope` substring — Task 1's
  regression check depends on this.

- [ ] **Step 1: Replace the Typography roles section**

  In `skills/ux-design-system/references/design-tokens.md`, replace:

  ```markdown
  ## Typography roles
  - `--font-primary` — headings + UI. A distinctive geometric (e.g. Space Grotesk) reads premium:
    heavy weight, tight line-height.
  - `--font-secondary` — body. A clean humanist sans (e.g. Manrope), normal weight, open
    line-height. May equal `--font-primary`. Never a third family.
  ```

  with:

  ```markdown
  ## Typography roles
  - `--font-primary` — headings + UI. Heavy weight and tight line-height read premium, but the
    family itself comes from the chosen personality (`design-personalities.md`) — never copy an
    illustrative example verbatim as a default.
  - `--font-secondary` — body. Normal weight, open line-height. May equal `--font-primary`. Never
    a third family. See `design-personalities.md` for the 8 concrete pairings.
  ```

- [ ] **Step 2: Replace the `TGL-STYLE` mention in Spacing & radii roles**

  In the same file, replace:

  ```markdown
  - Radii carry meaning: containers are the softest, then cards; buttons, inputs and images share
    the smallest step. Separate tokens are what let a brand go sharp (`TGL-STYLE` minimalista) or
    soft without touching a single module.
  ```

  with:

  ```markdown
  - Radii carry meaning: containers are the softest, then cards; buttons, inputs and images share
    the smallest step. Separate tokens are what let a personality go sharp (Minimal Swiss, Tech
    Precision) or soft (Warm Boutique) without touching a single module.
  ```

- [ ] **Step 3: Verify**

  Run: `php skills/framework-audit/assets/framework-audit.php`
  Expected: no FAIL rows mentioning `design-tokens.md still hardcodes`.

  Run: `grep -n "Space Grotesk\|Manrope\|TGL-STYLE" skills/ux-design-system/references/design-tokens.md`
  Expected: no output.

- [ ] **Step 4: Commit**

  ```bash
  git add skills/ux-design-system/references/design-tokens.md
  git commit -m "fix(ux-design-system): stop design-tokens.md defaulting every build to one font pair"
  ```

---

### Task 4: `ux-design-system/SKILL.md` — CAPA 2 step + elite-designer framing

**Files:**
- Modify: `skills/ux-design-system/SKILL.md` (full-file rewrite, same file)

**Interfaces:**
- Consumes: `design-personalities.md` (Task 2), the toggle names from Task 6
  (`TGL-IMAGERY`, `TGL-MOTION-INTENSITY`) — mentioned in prose only, no hard dependency ordering
  (this task can run before or after Task 6).
- Produces: SKILL.md containing both `design-personalities.md` and `CAPA 2` — clears Task 1's
  last remaining check.

- [ ] **Step 1: Replace the full file**

  Replace `skills/ux-design-system/SKILL.md` with:

  ```markdown
  ---
  name: ux-design-system
  description: "Trigger: premium web design, hero, layout, cards, hover effects, responsive, microinteractions, design tokens, spacing, palette. Builder-agnostic visual language for premium WordPress sites (Elementor or Divi)."
  license: Apache-2.0
  metadata:
    author: "juan"
    version: "1.2"
  ---

  # UX Design System

  The visual language, independent of the page builder. Decide HOW it should look and
  feel; `elementor-core` / `divi-core` translate these decisions into builder data.

  ## Activation Contract
  Run after `web-templates` (architecture resolved), before `html-mockup`. Use when deciding
  layout, spacing, color, hover/motion, card style, or responsive behavior. Applies to
  Elementor and Divi alike. Never hand off straight to builder-core: the mockup approval gate
  sits between this skill and any WordPress write.

  ## The 3 layers
  - **CAPA 1 — Personalities** (`references/design-personalities.md`): 8 curated visual
    languages (typography, color mood, radius/shadow, motion, imagery, card recipe). Orthogonal
    to the architecture archetype `web-templates` already resolved — same archetype, different
    personality, different studio-feel result.
  - **CAPA 2 — Recommender**: reuse the brand signals + references `web-templates` already
    collected. Never re-ask for references. Map industry/tone/audience to one `PERS-*`, present
    the pick with a one-line rationale, confirm with the client before continuing.
  - **CAPA 3 — Toggles** (`web-templates/references/toggles.md`): `TGL-IMAGERY`,
    `TGL-MOTION-INTENSITY`, plus the reused `TGL-CARD-STYLE` / `TGL-CARD-IMG`, fine-tune within
    the chosen personality's defaults.

  ## Hard Rules
  - Act like a senior visual designer, not a filler: every typography/color/motion choice must
    trace to a brand signal or a client reference. "The example in the docs" is never a
    justification — if CAPA 2 can't justify a `PERS-*` pick against real signals, ask one more
    question instead of guessing.
  - One accent color, used ONLY for CTAs / action icons / important links. Neutrals carry the rest.
  - Motion is calm: hovers use `cubic-bezier(.22,1,.36,1)`, ~.35–.7s, small moves
    (`translateY(-4…-6px)`, `scale(1.045)`), soft shadow — never a hard snap. The chosen
    personality tunes duration/distance within this range; see `references/motion.md`.
  - Consistent spacing rhythm and section padding across the whole site; audit margins as a pass.
  - Two button families only: solid accent + ghost/outline. Both need a legible hover in
    BOTH states (a ghost that turns white-on-white on hover is the classic bug).
  - Cards share ONE language site-wide (reuse a single card recipe per personality, don't
    reinvent per section).
  - Mobile-first: centered hero on small screens, real breakpoints, full-width CTAs, equal-height product cards.

  ## Execution Steps
  1. Run CAPA 2: reuse `web-templates`'s brand signals + 2-4 references, recommend one `PERS-*`
     from `references/design-personalities.md` with rationale, confirm with the client.
  2. Read `references/design-tokens.md` and fix the palette, type pair, spacing scale, radii —
     using the confirmed personality's values, never a docs example.
  3. Read `references/motion.md` for the hover/microinteraction timings and the premium card
     recipe, tuned by the personality's motion intensity.
  4. Read `references/layout-patterns.md` for hero, feature grid, banner CTA, testimonial carousel,
     glass header, mega/mobile menu, and responsive rules.
  5. Hand the chosen personality + tokens + pattern list to **`html-mockup`** as the spec to render
     for client approval. builder-core only receives it after the mockup is approved.

  ## Output Contract
  Return a short spec addressed to `html-mockup`: the `PERS-*` chosen, palette + roles, type
  pair, spacing/radii, motion timings, the list of sections/patterns to build, and per-breakpoint
  notes. No builder-specific code here.

  ## References
  - `references/design-personalities.md` — CAPA 1: the 8 personality catalog.
  - `references/design-tokens.md` — palette roles, type, spacing, radii.
  - `references/motion.md` — hover timings, premium card recipe, glass, button system.
  - `references/layout-patterns.md` — section blueprints + responsive rules.
  ```

- [ ] **Step 2: Verify**

  Run: `php skills/framework-audit/assets/framework-audit.php`
  Expected: no FAIL rows for `ux-design-system` at all. WARN for body word count is acceptable
  (all 11 existing skills already carry that WARN per the 2026-08-11 baseline audit) — a FAIL
  (over ~600 words) is not; if it fires, trim prose, don't drop content that satisfies a check.

- [ ] **Step 3: Commit**

  ```bash
  git add skills/ux-design-system/SKILL.md
  git commit -m "feat(ux-design-system): add CAPA 2 personality recommender + elite-designer framing"
  ```

---

### Task 5: `web-templates/design-system.md` — rewrite the shared-tokens hard rule

**Files:**
- Modify: `skills/web-templates/references/design-system.md`

**Interfaces:**
- Consumes: `design-personalities.md` (Task 2) as the file this now points to for concrete values.
- Produces: no new check (out of the 3 the spec scoped) — verified by read-through + the generic
  dead-reference audit check (any backticked path this file's callers point at must still exist).

- [ ] **Step 1: Replace the intro paragraphs**

  In `skills/web-templates/references/design-system.md`, replace lines 1-14:

  ```markdown
  # Sistema Global de Diseño

  Tokens compartidos por TODAS las plantillas (ecommerce y corporate). Se definen **una vez**
  y se cambian globalmente al migrar a un cliente nuevo. Diseñado mobile-first. Compatible con
  Elementor (Global Settings) y Divi (Theme Options + Global Presets), y con la skill
  `html-mockup` (variables `--*` en `:root`).

  Los valores son **defaults**. El recomendador puede ajustarlos por marca vía toggles
  (`TGL-STYLE`). Los ROLES no cambian; los valores sí.

  Este archivo es la **única autoridad** sobre NOMBRES y VALORES de token.
  `ux-design-system/references/design-tokens.md` explica los ROLES (para qué sirve cada token,
  cómo derivar la paleta de un logo) y no define valores. Ante cualquier diferencia, manda este
  archivo.
  ```

  with:

  ```markdown
  # Sistema Global de Diseño

  La ESTRUCTURA de tokens (roles, pasos de escala, breakpoints) es compartida por TODAS las
  plantillas (ecommerce y corporate) y se define **una vez**. Los VALORES concretos (tipografía,
  paleta, radios/sombras, motion) vienen de la personalidad visual (`PERS-*`) elegida en
  `ux-design-system` CAPA 2 — independiente de qué `TPL-*` se haya elegido. Diseñado mobile-first.
  Compatible con Elementor (Global Settings) y Divi (Theme Options + Global Presets), y con la
  skill `html-mockup` (variables `--*` en `:root`).

  Los valores de esta página son el fallback estructural (spacing, breakpoints, contenedores) que
  toda personalidad hereda sin tocar. Para tipografía, paleta, radios y motion CONCRETOS, ver
  `ux-design-system/references/design-personalities.md` — CAPA 2 ajusta esos valores por marca;
  los ROLES no cambian nunca.

  Este archivo es la **única autoridad** sobre NOMBRES y VALORES ESTRUCTURALES de token (spacing,
  breakpoints, contenedores). `design-personalities.md` es la única autoridad sobre los valores de
  tipografía/paleta/radios/motion por personalidad. `ux-design-system/references/design-tokens.md`
  explica los ROLES (para qué sirve cada token, cómo derivar la paleta de un logo) y no define
  valores. Ante cualquier diferencia, manda el archivo correspondiente a lo consultado.
  ```

- [ ] **Step 2: Replace the radius note**

  In the same file, replace:

  ```markdown
  Ajustable por marca vía `TGL-STYLE` (minimalista → 0–4px; soft → 12–20px).
  ```

  with:

  ```markdown
  Ajustable por marca vía la personalidad visual elegida en `ux-design-system` CAPA 2 (Minimal
  Swiss / Tech Precision → 0–4px; Warm Boutique → 12–20px). Ver `design-personalities.md`.
  ```

- [ ] **Step 3: Verify**

  Run: `php skills/framework-audit/assets/framework-audit.php`
  Expected: no new FAIL/WARN rows for `web-templates`.

  Run: `grep -n "TGL-STYLE" skills/web-templates/references/design-system.md`
  Expected: no output.

- [ ] **Step 4: Commit**

  ```bash
  git add skills/web-templates/references/design-system.md
  git commit -m "fix(web-templates): decouple token values from a single shared default"
  ```

---

### Task 6: Retire `TGL-STYLE`, add `TGL-IMAGERY` / `TGL-MOTION-INTENSITY`

**Files:**
- Modify: `skills/web-templates/references/toggles.md`
- Modify (one-line row removal each): all 13 files listed in Step 2

**Interfaces:**
- Consumes: nothing from earlier tasks.
- Produces: zero remaining `TGL-STYLE` references anywhere in `skills/` — verified by grep, not a
  framework-audit check (out of the 3 the spec scoped for the mechanical gate).

- [ ] **Step 1: Edit `skills/web-templates/references/toggles.md` — master table + prose**

  Replace the `TGL-STYLE` row:

  ```markdown
  | `TGL-STYLE` | ¿Estilo general? | minimalista / elegante-editorial / comercial | tokens (radius, spacing, densidad) | todas |
  ```

  with two rows:

  ```markdown
  | `TGL-IMAGERY` | ¿Fotografía, ilustración o tratamiento gráfico? | foto / ilustración / gráfico | imagery treatment | todas |
  | `TGL-MOTION-INTENSITY` | ¿Qué tan marcado el motion? | sutil / default (personalidad) / audaz | hover/motion deltas | todas |
  ```

  Replace the TPL-SERVICE-01 reuse sentence:

  ```markdown
  `TPL-SERVICE-01` (página de servicio/área) no estrena toggles: reutiliza `TGL-PROCESS`, `TGL-CASES`,
  `TGL-FAQ`, `TGL-TESTIMONIALS`, `TGL-PRICING`, `TGL-STYLE` y `TGL-CTA-STRENGTH`. Ojo con dos
  ```

  with:

  ```markdown
  `TPL-SERVICE-01` (página de servicio/área) no estrena toggles: reutiliza `TGL-PROCESS`, `TGL-CASES`,
  `TGL-FAQ`, `TGL-TESTIMONIALS`, `TGL-PRICING` y `TGL-CTA-STRENGTH`. Ojo con dos
  ```

  Replace the "Compartidos" footer line:

  ```markdown
  Compartidos entre ecommerce y corporate: `TGL-HERO-TYPE`, `TGL-HERO-HEIGHT`, `TGL-STYLE`,
  `TGL-CTA-STRENGTH`, `TGL-NEWSLETTER`, `TGL-TESTIMONIALS`, `TGL-TRUST`, `TGL-FAQ`, `TGL-CARD-STYLE`,
  `TGL-CARD-IMG`.
  ```

  with:

  ```markdown
  Compartidos entre ecommerce y corporate: `TGL-HERO-TYPE`, `TGL-HERO-HEIGHT`, `TGL-IMAGERY`,
  `TGL-MOTION-INTENSITY`, `TGL-CTA-STRENGTH`, `TGL-NEWSLETTER`, `TGL-TESTIMONIALS`, `TGL-TRUST`,
  `TGL-FAQ`, `TGL-CARD-STYLE`, `TGL-CARD-IMG`.
  ```

- [ ] **Step 2: Remove the `TGL-STYLE` row from each archetype's "Toggles admitidos" table**

  Each of these is a single-line removal (delete the exact line, no replacement — matches the
  existing precedent where not every "todas"-scoped toggle gets a precharged row in every
  archetype, e.g. `TGL-CATEGORIES` is already absent from `TPL-E-01`'s table today):

  | File | Line to delete |
  |------|-----------------|
  | `skills/web-templates/references/templates/corporate/TPL-C-01-services-leadgen.md` | `| \`TGL-STYLE\` | elegante / corporate | |` |
  | `skills/web-templates/references/templates/ecommerce/TPL-E-01-visual-brand.md` | `| \`TGL-STYLE\` | elegante-editorial | |` |
  | `skills/web-templates/references/templates/ecommerce/TPL-E-02-catalog-product-first.md` | `| \`TGL-STYLE\` | comercial | |` |
  | `skills/web-templates/references/templates/corporate/TPL-C-05-local-booking.md` | `| \`TGL-STYLE\` | según rubro | |` |
  | `skills/web-templates/references/templates/corporate/TPL-C-02-institutional-trust.md` | `| \`TGL-STYLE\` | corporate / sobrio | |` |
  | `skills/web-templates/references/templates/corporate/TPL-C-03-portfolio-showcase.md` | `| \`TGL-STYLE\` | minimalista / editorial | |` |
  | `skills/web-templates/references/templates/ecommerce/TPL-E-04-categories-first.md` | `| \`TGL-STYLE\` | comercial | |` |
  | `skills/web-templates/references/templates/corporate/TPL-C-04-landing-single-offer.md` | `| \`TGL-STYLE\` | moderno / SaaS | |` |
  | `skills/web-templates/references/templates/ecommerce/TPL-E-03-brand-story.md` | `| \`TGL-STYLE\` | elegante-editorial | |` |
  | `skills/web-templates/references/templates/ecommerce/TPL-E-05-promo-campaign.md` | `| \`TGL-STYLE\` | comercial | |` |
  | `skills/web-templates/references/templates/pages/about/TPL-ABOUT-01.md` | `| \`TGL-STYLE\` | hereda de la home | |` |
  | `skills/web-templates/references/templates/pages/contact/TPL-CONTACT-01.md` | `| \`TGL-STYLE\` | hereda de la home | |` |
  | `skills/web-templates/references/templates/pages/service/TPL-SERVICE-01-service-detail.md` | `| \`TGL-STYLE\` | hereda de la home | |` |

  For each row: two of the 13 files share the identical text `| \`TGL-STYLE\` | comercial | |`
  (TPL-E-02 and TPL-E-04) and two share `| \`TGL-STYLE\` | elegante-editorial | |` (TPL-E-01,
  TPL-E-03) and three share `| \`TGL-STYLE\` | hereda de la home | |` (TPL-ABOUT-01, TPL-CONTACT-01,
  TPL-SERVICE-01) — each is still unique WITHIN its own file, so a same-file string match is safe;
  do not attempt a cross-file `replace_all`.

- [ ] **Step 3: Verify no `TGL-STYLE` reference remains**

  Run: `grep -rn "TGL-STYLE" skills/`
  Expected: no output.

  Run: `php skills/framework-audit/assets/framework-audit.php`
  Expected: no new FAIL/WARN rows for `web-templates` or any `TPL-*` — this file set carries no
  dedicated framework-audit check, so a clean run here means "no broken references introduced,"
  not "content validated."

- [ ] **Step 4: Commit**

  ```bash
  git add skills/web-templates/references/toggles.md skills/web-templates/references/templates
  git commit -m "refactor(web-templates): retire TGL-STYLE, add TGL-IMAGERY and TGL-MOTION-INTENSITY"
  ```

---

### Task 7: Full verification pass

**Files:** none (verification only)

**Interfaces:**
- Consumes: everything from Tasks 1-6.
- Produces: nothing new — this is the plan's acceptance gate.

- [ ] **Step 1: Run the full automated gate**

  ```bash
  php skills/framework-audit/assets/framework-audit.php --strict
  php tests/test-framework-audit.php
  php tests/test-container-hygiene.php
  ```

  Expected: all three exit 0. `framework-audit.php --strict` must show 0 FAIL and 0 WARN for
  `ux-design-system` and `web-templates` specifically (other skills' pre-existing WARNs, e.g. the
  body-word-count WARN already present across all 11 skills, are out of this change's scope and
  may remain).

- [ ] **Step 2: Manual acceptance check — same archetype, different personality**

  Run `web-templates` → `ux-design-system` end-to-end (in a fresh conversation, or by reading
  through the two skills' Execution Steps by hand) for two fabricated briefs that both resolve to
  `TPL-E-02` (catalog-first ecommerce):

  - Brief A: "Tienda de accesorios de electrónica, catálogo grande, referencias: Nothing,
    Apple Store." Expected CAPA 2 pick: `PERS-TECH-PRECISION`.
  - Brief B: "Joyería boutique, catálogo chico, referencias: Mejuri, un estudio local."
    Expected CAPA 2 pick: `PERS-EDITORIAL` or `PERS-WARM-BOUTIQUE` (either is a defensible read of
    those references — the acceptance bar is that it is NOT `PERS-TECH-PRECISION`).

  Confirm the resulting token specs differ meaningfully (font pair, palette mood, motion
  character) between A and B despite the identical `TPL-E-02` architecture. This is the actual
  proof the "todo genérico, todo igual" complaint is addressed — the automated gate only proves
  the catalog is complete and reachable, not that it produces good results.

- [ ] **Step 3: If Step 2 surfaces a gap, fix inline and re-run Step 1**

  A gap here (e.g. the CAPA 2 recommender step in `SKILL.md` is too vague to actually
  differentiate the two briefs) is a Task 4 content issue, not a new task — fix the prose, rerun
  the automated gate, done.
