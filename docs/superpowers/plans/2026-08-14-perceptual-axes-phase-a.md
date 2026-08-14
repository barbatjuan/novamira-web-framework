# Perceptual Axes — Phase A Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the eight adjectival personalities with four anchors defined as positions on five perceptual axes, backed by tokens, and make "these are actually different" a check that fails rather than a claim in a document.

**Architecture:** `design-personalities.md` gains a machine-readable `**Axes:**` line per anchor. `framework-audit.php` parses it and fails when two anchors share more than one axis position or name a position no axis defines. Token VALUES live in `web-templates/references/design-system.md` (the existing naming authority); ROLES live in `ux-design-system/references/design-tokens.md`. `ux-design-system/SKILL.md` replaces the catalog-pick step with a short business-terms dialogue that must resolve every axis.

**Tech Stack:** PHP 8 (no framework, no Composer), plain markdown. Tests are the repo's four PHP suites run directly with `php tests/<file>.php`; the audit itself is `php skills/framework-audit/assets/framework-audit.php .`

## Global Constraints

- Spec: `docs/superpowers/specs/2026-08-14-perceptual-axes-design.md`. Phase A is spec items 1–5. Item 6 (the two mockups) is Phase B and is **out of scope here**.
- **Never weaken a check to make a row go green** (`CONTRIBUTING.md`, hard rule).
- Every new `RT_*` row type needs three things or the suite fails: an entry in `ROW_TYPES`, a row in the `CONTRIBUTING.md` row-type table (`RT_ROWTYPE_UNDOCUMENTED`), and at least one fixture that emits it (`COVERAGE_EXEMPT` is empty and must stay empty).
- `SKILL.md` word ceiling is **600 = FAIL**, aim 500 = WARN. `ux-design-system/SKILL.md` is at **552**. Measure with `php skills/framework-audit/assets/framework-audit.php . --word-report`.
- Hard Rule bullets in write-capable skills need a verifier marker; `ux-design-system` is **not** write-capable, so its bullets do not. Do not add markers there.
- `RT_TOKENS_HARDCODED_FONT` fails on the strings `Space Grotesk` or `Manrope` anywhere in `design-tokens.md`. Real family names belong in `design-personalities.md` only.
- Accent colour is **not** an axis. It derives from the brand per `design-tokens.md`'s existing steps.
- Motion is **not** an axis either: it stays a per-anchor field. The distance rule is about what a still frame shows, and `motion.md` already constrains motion to one curve and documented ranges.
- Working tree is CRLF (`core.autocrlf=true`); the repo stores LF. Mutation anchors written with `\n` must be translated — see `docs/` history if a runner is used.
- Run all four suites plus the audit before every commit.

## File Structure

| File | Responsibility after Phase A |
|---|---|
| `skills/ux-design-system/references/design-personalities.md` | The four anchors. One `**Axes:**` line each — the machine-readable contract — plus prose fields. Sole home of concrete typeface names. |
| `skills/web-templates/references/design-system.md` | Naming authority. Gains the axis position → token VALUE tables (scale, density, ground, elevation). |
| `skills/ux-design-system/references/design-tokens.md` | Roles only. Gains "what each axis is FOR"; never a number, never a font name. |
| `skills/ux-design-system/references/motion.md` | Gains the `--elev-*` token definitions; keeps the existing hover/curve rules. |
| `skills/ux-design-system/SKILL.md` | Execution Steps replace "pick a `PERS-*`" with the axis dialogue. Must stay < 600 words. |
| `skills/framework-audit/assets/framework-audit.php` | `$PERS_IDS` / `$PERS_FIELDS` updated; new `RT_PERS_TOO_SIMILAR` and `RT_PERS_BAD_AXIS`. |
| `tests/test-framework-audit.php` | Fixtures for both new row types and for the updated field list. |
| `CONTRIBUTING.md` | Two new rows in the row-type table. |

---

### Task 1: Axis registry, four anchors, and the checks that pin them

**Files:**
- Modify: `skills/framework-audit/assets/framework-audit.php:932-962` (the personality block)
- Modify: `skills/framework-audit/assets/framework-audit.php:89-94` (ROW_TYPES)
- Modify: `skills/ux-design-system/references/design-personalities.md` (full rewrite)
- Modify: `CONTRIBUTING.md` (row-type table)
- Test: `tests/test-framework-audit.php`

**Interfaces:**
- Consumes: nothing from earlier tasks.
- Produces: the axis vocabulary that Task 2 supplies values for, and the anchor IDs Task 3's dialogue resolves to. Exact position strings, which every later task must match character for character:
  - scale: `contained` | `classic` | `editorial` | `monumental`
  - ground: `paper` | `warm` | `cool` | `ink`
  - density: `compact` | `standard` | `generous` | `monumental`
  - composition: `centered` | `asymmetric` | `strict-grid` | `broken-grid`
  - elevation: `none` | `hairline` | `soft-shadow` | `accent-glow`
  - Anchors: `PERS-EDITORIAL`, `PERS-MATTER`, `PERS-DIRECT`, `PERS-INSTITUTIONAL`
  - PHP: `pers_axes( string $block ): array` returns `axis => position` parsed from one anchor block; `array()` when the `**Axes:**` line is absent or malformed.

- [ ] **Step 1: Write the failing fixtures**

Add before the `--- fixture coverage` line in `tests/test-framework-audit.php`:

```php
echo "--- las anclas de personalidad no pueden parecerse entre si ---\n";
$r80 = fx_tmp_root();
fx_base( $r80 );
/* Un ancla que comparte CUATRO ejes con otra: comparten 4 > 1, tiene que FALLAR. */
fx(
	$r80,
	'skills/ux-design-system/references/design-personalities.md',
	"# Personalities\n\n"
	. fx_pers( 'PERS-EDITORIAL', 'editorial', 'paper', 'generous', 'asymmetric', 'none' )
	. fx_pers( 'PERS-MATTER', 'classic', 'paper', 'generous', 'asymmetric', 'none' )
	. fx_pers( 'PERS-DIRECT', 'monumental', 'ink', 'compact', 'broken-grid', 'accent-glow' )
	. fx_pers( 'PERS-INSTITUTIONAL', 'contained', 'cool', 'standard', 'centered', 'soft-shadow' )
);
list( , $out80 ) = fx_run_ok( $audit, $r80 );
ok( 'FAIL' === fx_row_level( $out80, array( 'RT_PERS_TOO_SIMILAR', 'PERS-MATTER' ) ), 'dos anclas que comparten mas de un eje FALLAN', fx_row_level( $out80, array( 'RT_PERS_TOO_SIMILAR', 'PERS-MATTER' ) ) );
ok( array() !== fx_lines_with( $out80, array( 'RT_PERS_TOO_SIMILAR', '4' ) ), 'y la fila dice CUANTOS ejes comparten, que es lo accionable', $out80 );
fx_rrmdir( $r80 );

echo "--- una posicion de eje inventada no pasa en silencio ---\n";
$r81 = fx_tmp_root();
fx_base( $r81 );
fx(
	$r81,
	'skills/ux-design-system/references/design-personalities.md',
	"# Personalities\n\n"
	. fx_pers( 'PERS-EDITORIAL', 'editorial', 'paper', 'generous', 'asymmetric', 'none' )
	. fx_pers( 'PERS-MATTER', 'classic', 'warm', 'standard', 'strict-grid', 'hairline' )
	. fx_pers( 'PERS-DIRECT', 'monumental', 'ink', 'compact', 'broken-grid', 'accent-glow' )
	/* "medium" no existe en el eje de densidad: un typo crea una quinta posicion en silencio. */
	. fx_pers( 'PERS-INSTITUTIONAL', 'contained', 'cool', 'medium', 'centered', 'soft-shadow' )
);
list( , $out81 ) = fx_run_ok( $audit, $r81 );
ok( 'FAIL' === fx_row_level( $out81, array( 'RT_PERS_BAD_AXIS', 'medium' ) ), 'una posicion que ningun eje define FALLA, nombrandola', fx_row_level( $out81, array( 'RT_PERS_BAD_AXIS', 'medium' ) ) );
ok( array() === fx_lines_with( $out81, array( 'RT_PERS_TOO_SIMILAR' ) ), 'y no se cuenta como parecido: una posicion invalida no es una coincidencia', $out81 );
fx_rrmdir( $r81 );

echo "--- cuatro anclas bien separadas no levantan nada ---\n";
$r82 = fx_tmp_root();
fx_base( $r82 );
fx(
	$r82,
	'skills/ux-design-system/references/design-personalities.md',
	"# Personalities\n\n"
	. fx_pers( 'PERS-EDITORIAL', 'editorial', 'paper', 'generous', 'asymmetric', 'none' )
	. fx_pers( 'PERS-MATTER', 'classic', 'warm', 'standard', 'strict-grid', 'hairline' )
	. fx_pers( 'PERS-DIRECT', 'monumental', 'ink', 'compact', 'broken-grid', 'accent-glow' )
	. fx_pers( 'PERS-INSTITUTIONAL', 'contained', 'cool', 'standard', 'centered', 'soft-shadow' )
);
list( , $out82 ) = fx_run_ok( $audit, $r82 );
ok( array() === fx_lines_with( $out82, array( 'RT_PERS_TOO_SIMILAR' ) ), 'compartir UN eje (densidad) es legal: el limite es mas de uno', $out82 );
ok( array() === fx_lines_with( $out82, array( 'RT_PERS_BAD_AXIS' ) ), 'y todas las posiciones son validas', $out82 );
fx_rrmdir( $r82 );
```

And add this helper beside the other `fx_*` helpers (after `fx_wc_skill`):

```php
/** One anchor block in the exact shape the audit parses. */
function fx_pers( $id, $scale, $ground, $density, $composition, $elevation ) {
	return "### `$id` — Fixture\n\n"
		. "**Axes:** scale `$scale` · ground `$ground` · density `$density` · composition `$composition` · elevation `$elevation`\n\n"
		. "**Fits:** fixture.\n\n**Typography:** fixture.\n\n**Motion intensity:** fixture.\n\n"
		. "**Imagery:** fixture.\n\n**Card recipe:** fixture.\n\n";
}
```

- [ ] **Step 2: Run the fixtures to verify they fail**

```bash
php tests/test-framework-audit.php 2>&1 | grep -E "^  FAIL|OK / "
```

Expected: the three new blocks FAIL, because `RT_PERS_TOO_SIMILAR` and `RT_PERS_BAD_AXIS` are not declared and `add()` rejects any ID absent from `ROW_TYPES` with `exit(3)`. If instead the whole run dies with exit code 3, that is the same signal — the IDs do not exist yet.

- [ ] **Step 3: Declare the two row types**

In `framework-audit.php`, after the `RT_PERS_ID_MISSING` line in `ROW_TYPES`:

```php
	'RT_PERS_TOO_SIMILAR'        => 'FAIL  — two personality anchors share more than one axis position',
	'RT_PERS_BAD_AXIS'           => 'FAIL  — a personality names an axis position no axis defines',
```

- [ ] **Step 4: Replace the personality block with the axis parser**

Replace `$PERS_IDS` / `$PERS_FIELDS` and the block that follows (currently lines 932–962) with:

```php
$PERS_IDS    = array( 'PERS-EDITORIAL', 'PERS-MATTER', 'PERS-DIRECT', 'PERS-INSTITUTIONAL' );
/* Color mood and "Radius & shadow" are gone as prose fields: ground and elevation are AXES now,
   and a field that repeats an axis in adjectives is how the old catalog drifted from its own
   values. Motion stays prose on purpose — the distance rule below is about what a still frame
   shows, and motion.md already pins the curve and the ranges. */
$PERS_FIELDS = array( 'Axes', 'Fits', 'Typography', 'Motion intensity', 'Imagery', 'Card recipe' );
$PERS_AXES   = array(
	'scale'       => array( 'contained', 'classic', 'editorial', 'monumental' ),
	'ground'      => array( 'paper', 'warm', 'cool', 'ink' ),
	'density'     => array( 'compact', 'standard', 'generous', 'monumental' ),
	'composition' => array( 'centered', 'asymmetric', 'strict-grid', 'broken-grid' ),
	'elevation'   => array( 'none', 'hairline', 'soft-shadow', 'accent-glow' ),
);

/**
 * The axis positions one anchor block declares.
 *
 * Parsed from a single `**Axes:**` line so the contract is one line a human can read and a
 * machine can compare. Returns `axis => position`, omitting anything the line does not name —
 * a missing axis surfaces as RT_PERS_MISSING_FIELD or as an unnamed axis, never as a silent
 * default, because an axis nobody sets is exactly how every project lands on the same value.
 */
function pers_axes( $block ) {
	if ( ! preg_match( '/^\*\*Axes:\*\*(.+)$/m', $block, $m ) ) {
		return array();
	}
	$out = array();
	if ( preg_match_all( '/([a-z]+)\s+`([a-z-]+)`/', $m[1], $mm, PREG_SET_ORDER ) ) {
		foreach ( $mm as $pair ) {
			$out[ $pair[1] ] = $pair[2];
		}
	}

	return $out;
}

$pers_file = $root . '/skills/ux-design-system/references/design-personalities.md';
if ( ! file_exists( $pers_file ) ) {
	add( 'RT_PERS_CATALOG_MISSING', 'FAIL', 'ux-design-system', 'references/design-personalities.md is missing — the personality catalog has no file' );
} else {
	$pers_src = slurp( $pers_file );
	$blocks   = preg_split( '/(?=^### `PERS-[A-Z-]+`)/m', $pers_src );
	$found    = array();
	$axes_of  = array();
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
		$axes = pers_axes( $block );
		$ok   = true;
		foreach ( $PERS_AXES as $axis => $positions ) {
			if ( ! isset( $axes[ $axis ] ) ) {
				add( 'RT_PERS_BAD_AXIS', 'FAIL', 'ux-design-system', $pid . ' names no position for axis "' . $axis . '"' );
				$ok = false;
			} elseif ( ! in_array( $axes[ $axis ], $positions, true ) ) {
				add( 'RT_PERS_BAD_AXIS', 'FAIL', 'ux-design-system', $pid . ' places axis "' . $axis . '" at "' . $axes[ $axis ] . '", which that axis does not define' );
				$ok = false;
			}
		}
		/* Only fully valid anchors enter the comparison. An invalid position is not a coincidence,
		   and counting it would report two anchors as "too similar" over a typo. */
		if ( $ok ) {
			$axes_of[ $pid ] = $axes;
		}
	}
	$ids = array_keys( $axes_of );
	foreach ( $ids as $i => $a ) {
		foreach ( array_slice( $ids, $i + 1 ) as $b ) {
			$shared = array();
			foreach ( $PERS_AXES as $axis => $_ ) {
				if ( $axes_of[ $a ][ $axis ] === $axes_of[ $b ][ $axis ] ) {
					$shared[] = $axis . ' `' . $axes_of[ $a ][ $axis ] . '`';
				}
			}
			if ( count( $shared ) > 1 ) {
				add(
					'RT_PERS_TOO_SIMILAR',
					'FAIL',
					'ux-design-system',
					$a . ' and ' . $b . ' share ' . count( $shared ) . ' axes (' . implode( ', ', $shared )
						. ') — two anchors may share at most one, or they ship as the same site with a different accent'
				);
			}
		}
	}
	foreach ( $PERS_IDS as $pid ) {
		if ( ! isset( $found[ $pid ] ) ) {
			add( 'RT_PERS_ID_MISSING', 'FAIL', 'ux-design-system', 'design-personalities.md is missing personality "' . $pid . '"' );
		}
	}
}
```

- [ ] **Step 5: Document the two row types in CONTRIBUTING.md**

In the row-type table, after the `RT_PERS_ID_MISSING` row:

```markdown
| `RT_PERS_TOO_SIMILAR` | FAIL | two personality anchors share more than one axis position |
| `RT_PERS_BAD_AXIS` | FAIL | a personality names an axis position no axis defines |
```

- [ ] **Step 6: Run the fixtures to verify they pass**

```bash
php tests/test-framework-audit.php 2>&1 | grep -E "^  FAIL|OK / "
```

Expected: `0 FAIL`. The audit run against the real repo will now FAIL — `design-personalities.md` still holds the eight old anchors with no `**Axes:**` lines. That is expected until Step 7.

- [ ] **Step 7: Rewrite design-personalities.md with the four anchors**

Full replacement. Keep the file's existing opening paragraph about orthogonality to `TPL-*`, then these four blocks verbatim:

```markdown
### `PERS-EDITORIAL` — Editorial

**Axes:** scale `editorial` · ground `paper` · density `generous` · composition `asymmetric` · elevation `none`

**Fits:** Heritage, prestige, a story worth slowing down for — galleries, publishers, high-end services.

**Typography:** `--font-primary` **Fraunces** (variable serif, real thick/thin contrast, optical sizing); `--font-secondary` **Inter Tight**. Both SIL OFL.

**Motion intensity:** slowest documented durations, lift capped at `-4px`. Nothing should feel quick.

**Imagery:** full-bleed or dramatically cropped photo-editorial framing; scrims sparingly.

**Card recipe:** image, hairline rule, text. No chips, no fills — the `none` elevation is the card.

### `PERS-DIRECT` — Direct

**Axes:** scale `monumental` · ground `ink` · density `compact` · composition `broken-grid` · elevation `accent-glow`

**Fits:** Brands that win by being unmistakable — studios, launches, anything that must not read as safe.

**Typography:** `--font-primary` **Archivo Expanded** at 700+; `--font-secondary` **Archivo** regular. One family, two extremes. SIL OFL.

**Motion intensity:** short durations, confident lift, accent glow rather than a neutral shadow.

**Imagery:** high-contrast, tightly cropped, often bleeding past the grid.

**Card recipe:** dark surface, accent glow border on hover instead of lift.

### `PERS-MATTER` — Matter

**Axes:** scale `classic` · ground `warm` · density `standard` · composition `strict-grid` · elevation `hairline`

**Fits:** Clients who sell a material or a made thing — stone, wood, food, furniture. The page should feel like the substance, not like software.

**Typography:** `--font-primary` **Instrument Serif** for headings; `--font-secondary` **DM Sans** for body. Both SIL OFL. The pairing is deliberately quieter than Editorial's: the photography carries the page.

**Motion intensity:** documented defaults, nothing faster. A material brand gains nothing from looking quick.

**Imagery:** the product shot straight on, warm-graded, edge to edge inside the grid. Never a lifestyle stock smile.

**Card recipe:** image at the container radius, hairline border, text below. No chips, no fills — the border is the whole chrome.

### `PERS-INSTITUTIONAL` — Institutional

**Axes:** scale `contained` · ground `cool` · density `standard` · composition `centered` · elevation `soft-shadow`

**Fits:** B2B, professional services, anything selling credibility over excitement — the archetype the abogados build belongs to.

**Typography:** `--font-primary` **Source Sans 3** semibold; `--font-secondary` **Source Sans 3** regular. One family, weight discipline instead of contrast. SIL OFL.

**Motion intensity:** unmodified defaults. This anchor earns trust by not drawing attention to its own interactions.

**Imagery:** sober photography of real work contexts; icon-led process sections.

**Card recipe:** white card, soft shadow at rest, accent icon chip, lift on hover with no colour shift.
```

The four `**Axes:**` lines above are the contract Step 4's parser reads, and they are the spec's anchor table exactly. Verify before moving on that no pair shares more than one position — `PERS-MATTER` and `PERS-INSTITUTIONAL` share `density standard` and nothing else, which is the single permitted overlap.

- [ ] **Step 8: Verify the whole gate is green**

```bash
php skills/framework-audit/assets/framework-audit.php . 2>&1 | tail -3
```

Expected: `0 FAIL`. Then all four suites:

```bash
for t in tests/*.php; do printf "%-34s " "$t"; php "$t" 2>&1 | tail -1; done
```

Expected: `0 FAIL` in each.

- [ ] **Step 9: Mutate the new check**

Verify each of these breaks at least one assertion, restoring the file after each:
1. `count( $shared ) > 1` → `> 4`: the too-similar fixture must fail.
2. Drop the `if ( $ok )` guard so invalid anchors enter the comparison: the bad-axis fixture's second assertion must fail.
3. Remove `count( $shared )` from the message: the "dice CUANTOS ejes" assertion must fail.

A surviving mutant here is almost never a weak assertion — it is a branch the fixture cannot reach. Fix the branch, not the assertion.

- [ ] **Step 10: Commit**

```bash
git add skills/framework-audit/assets/framework-audit.php skills/ux-design-system/references/design-personalities.md CONTRIBUTING.md tests/test-framework-audit.php
git commit -m "feat(ux-design-system): personalities become positions, and the distance is checked"
```

---

### Task 2: Axis token values and the check that they exist

**Files:**
- Modify: `skills/web-templates/references/design-system.md` (add the value tables)
- Modify: `skills/ux-design-system/references/design-tokens.md` (add the roles)
- Modify: `skills/ux-design-system/references/motion.md` (add `--elev-*`)
- Modify: `skills/framework-audit/assets/framework-audit.php`
- Modify: `CONTRIBUTING.md`
- Test: `tests/test-framework-audit.php`

**Interfaces:**
- Consumes: the position strings from Task 1's `$PERS_AXES`.
- Produces: `--type-ratio`, `--display-lh`, `--fs-h1-max`, `--sp-scale`, `--elev-rest`, `--elev-hover` as named tokens with a value per position, which Phase B's mockups consume.

- [ ] **Step 1: Write the failing fixture**

```php
echo "--- una posicion de eje sin valor de token no sirve para nada ---\n";
$r83 = fx_tmp_root();
fx_base( $r83 );
fx( $r83, 'skills/ux-design-system/references/design-personalities.md',
	"# P\n\n"
	. fx_pers( 'PERS-EDITORIAL', 'editorial', 'paper', 'generous', 'asymmetric', 'none' )
	. fx_pers( 'PERS-MATTER', 'classic', 'warm', 'standard', 'strict-grid', 'hairline' )
	. fx_pers( 'PERS-DIRECT', 'monumental', 'ink', 'compact', 'broken-grid', 'accent-glow' )
	. fx_pers( 'PERS-INSTITUTIONAL', 'contained', 'cool', 'standard', 'centered', 'soft-shadow' ) );
/* design-system.md define TODAS menos `monumental` en densidad. */
fx( $r83, 'skills/web-templates/references/design-system.md',
	"# Tokens\n\n| position | value |\n|---|---|\n"
	. "| `contained` | x |\n| `classic` | x |\n| `editorial` | x |\n| `monumental` | x |\n"
	. "| `paper` | x |\n| `warm` | x |\n| `cool` | x |\n| `ink` | x |\n"
	. "| `compact` | x |\n| `standard` | x |\n| `generous` | x |\n"
	. "| `centered` | x |\n| `asymmetric` | x |\n| `strict-grid` | x |\n| `broken-grid` | x |\n"
	. "| `none` | x |\n| `hairline` | x |\n| `soft-shadow` | x |\n| `accent-glow` | x |\n" );
list( , $out83 ) = fx_run_ok( $audit, $r83 );
ok( array() === fx_lines_with( $out83, array( 'RT_AXIS_VALUE_MISSING' ) ), 'una posicion citada por design-system.md tiene valor', $out83 );
fx_rrmdir( $r83 );
```

Note: `monumental` appears once and covers both the scale axis and the density axis, so this fixture passes as written. To make the row fire, a second fixture drops `| \`broken-grid\` | x |`:

```php
$r84 = fx_tmp_root();
fx_base( $r84 );
fx( $r84, 'skills/ux-design-system/references/design-personalities.md',
	"# P\n\n"
	. fx_pers( 'PERS-EDITORIAL', 'editorial', 'paper', 'generous', 'asymmetric', 'none' )
	. fx_pers( 'PERS-MATTER', 'classic', 'warm', 'standard', 'strict-grid', 'hairline' )
	. fx_pers( 'PERS-DIRECT', 'monumental', 'ink', 'compact', 'broken-grid', 'accent-glow' )
	. fx_pers( 'PERS-INSTITUTIONAL', 'contained', 'cool', 'standard', 'centered', 'soft-shadow' ) );
fx( $r84, 'skills/web-templates/references/design-system.md', "# Tokens\n\n| `contained` | x |\n" );
list( , $out84 ) = fx_run_ok( $audit, $r84 );
ok( 'FAIL' === fx_row_level( $out84, array( 'RT_AXIS_VALUE_MISSING', 'broken-grid' ) ), 'una posicion sin valor en design-system.md FALLA, nombrandola', fx_row_level( $out84, array( 'RT_AXIS_VALUE_MISSING', 'broken-grid' ) ) );
fx_rrmdir( $r84 );
```

- [ ] **Step 2: Run to verify it fails**

```bash
php tests/test-framework-audit.php 2>&1 | grep -E "^  FAIL|OK / "
```

Expected: FAIL (or `exit(3)`) — `RT_AXIS_VALUE_MISSING` is not declared.

- [ ] **Step 3: Declare the row type and implement the check**

In `ROW_TYPES`:

```php
	'RT_AXIS_VALUE_MISSING'      => 'FAIL  — an axis position has no token value in design-system.md',
```

At **top level**, after the closing `}` of the `if ( ! file_exists( $pers_file ) ) … else { … }`
block — not inside the `else`. The axes exist whether or not the catalog file does, so a missing
catalog must not also silence the value check:

```php
/* A position with no value is an adjective. The old catalog was entirely adjectives, which is
   why it produced one look: nothing downstream could act on "softest step of the scale". */
$ds_file = $root . '/skills/web-templates/references/design-system.md';
	$ds_src  = file_exists( $ds_file ) ? slurp( $ds_file ) : '';
	foreach ( $PERS_AXES as $axis => $positions ) {
		foreach ( $positions as $pos ) {
			if ( false === strpos( $ds_src, '`' . $pos . '`' ) ) {
				add( 'RT_AXIS_VALUE_MISSING', 'FAIL', 'web-templates', 'design-system.md gives no value for axis "' . $axis . '" position "' . $pos . '"' );
			}
		}
	}
```

- [ ] **Step 4: Add the row to CONTRIBUTING.md**

```markdown
| `RT_AXIS_VALUE_MISSING` | FAIL | an axis position has no token value in `design-system.md` |
```

- [ ] **Step 5: Write the value tables in design-system.md**

Append a section. Values are the spec's contract — copy them exactly:

```markdown
## Perceptual axes — token values

### Scale (`--type-ratio`, `--display-lh`, `--fs-h1-max`)
| Position | `--type-ratio` | `--display-lh` | `--fs-h1-max` |
|---|---|---|---|
| `contained` | 1.200 | 1.25 | 48px |
| `classic` | 1.333 | 1.10 | 64px |
| `editorial` | 1.500 | 0.95 | 88px |
| `monumental` | 1.618 | 0.82 | 120px |

Every heading token derives from the ratio, never by hand:
`--fs-h3: clamp(calc(var(--fs-body) * var(--type-ratio)), 2.2vw + .6rem, …)`, h2 at the square,
h1 at the cube with `--fs-h1-max` as the hard cap. Body stays `1rem`–`1.25rem` at every position.

### Density (`--sp-scale`)
| Position | `--sp-scale` |
|---|---|
| `compact` | 0.8 |
| `standard` | 1.0 |
| `generous` | 1.35 |
| `monumental` | 1.7 |

One multiplier over the whole `--sp-*` scale, so rhythm consistency survives by construction.
Section padding becomes fluid: `padding-block: clamp(calc(2rem * var(--sp-scale)), 6vw, calc(7rem * var(--sp-scale)))`.

### Ground (`--c-bg`, `--c-bg-alt`, contrast derivation)
| Position | `--c-bg` | Contrast is derived toward |
|---|---|---|
| `paper` | `#FFFFFF` | neutral near-black |
| `warm` | cream/ivory, e.g. `#FFF3E3` | warm near-black (brown-black) |
| `cool` | very light blue-grey | deep blue-grey |
| `ink` | near-black | near-white text; re-derive the accent for contrast on dark |

### Elevation (`--elev-rest`, `--elev-hover`)
| Position | `--elev-rest` | `--elev-hover` |
|---|---|---|
| `none` | `none` | `none` — separation is whitespace |
| `hairline` | `0 0 0 1px var(--c-border)` | `0 0 0 1px var(--c-text)` |
| `soft-shadow` | `0 1px 2px rgba(0,0,0,.04)` | `0 18px 40px -12px rgba(21,24,26,.16)` |
| `accent-glow` | `0 0 0 1px color-mix(in srgb,var(--c-accent) 22%,transparent)` | `0 14px 34px -10px color-mix(in srgb,var(--c-accent) 40%,transparent)` |

### Composition
| Position | Blueprint set |
|---|---|
| `centered` | hero centred, symmetric grids, section headings centred |
| `asymmetric` | content off-centre at ~58%, one image bleeding an edge |
| `strict-grid` | everything on a 12-col grid, no bleeds, equal gutters |
| `broken-grid` | at least one element per section crossing the grid or overlapping a neighbour |
```

- [ ] **Step 6: Add the roles to design-tokens.md and the elevation tokens to motion.md**

In `design-tokens.md`, a new "Perceptual axes" section stating what each axis is FOR — **no numbers, no font names** (`RT_TOKENS_HARDCODED_FONT` and the "single authority" rule):

```markdown
## Perceptual axes
Five axes carry what makes two sites feel different; the accent colour is NOT one of them, it
derives from the brand. Values live in `web-templates/references/design-system.md`.
- **Scale** — the RANGE between body and display, and how tight the display leads. The single
  largest perceptual difference between two sites, and the one the framework never varied.
- **Ground** — what the page is made of. Choosing white is a decision and is recorded as one;
  white-by-default is how a site reads as a template.
- **Density** — one multiplier over the whole spacing scale, so the rhythm stays consistent while
  the airiness changes completely.
- **Composition** — which section blueprints are on offer, not free improvisation.
- **Elevation** — how separation is expressed: air, a hairline, a shadow, or an accent glow.
```

In `motion.md`, under a new heading, state that `--elev-rest` / `--elev-hover` replace the hardcoded shadow, and that the existing hover curve and durations are unchanged.

- [ ] **Step 7: Run everything**

```bash
php skills/framework-audit/assets/framework-audit.php . 2>&1 | tail -3
for t in tests/*.php; do printf "%-34s " "$t"; php "$t" 2>&1 | tail -1; done
```

Expected: `0 FAIL` everywhere.

- [ ] **Step 8: Mutate**

1. `strpos( $ds_src, '`' . $pos . '`' )` → `strpos( $ds_src, $pos )` (drop the backticks): the fixture must still pass, and if it does, ADD a fixture where a position name appears as prose but not as a token — otherwise the backticks are untested.
2. Delete the whole `foreach ( $PERS_AXES ...)` value loop: `$r84` must fail.

- [ ] **Step 9: Commit**

```bash
git add skills/web-templates/references/design-system.md skills/ux-design-system/references/design-tokens.md skills/ux-design-system/references/motion.md skills/framework-audit/assets/framework-audit.php CONTRIBUTING.md tests/test-framework-audit.php
git commit -m "feat(ux-design-system): every axis position gets a value, and a check that it has one"
```

---

### Task 3: The dialogue that resolves every axis

**Files:**
- Modify: `skills/ux-design-system/SKILL.md` (Execution Steps + the CAPA framing)
- Modify: `skills/framework-audit/assets/framework-audit.php:975-985` (`RT_UXDS_NO_CAPA2_STEP`)
- Test: `tests/test-framework-audit.php`

**Interfaces:**
- Consumes: anchor IDs and axis names from Task 1; token values from Task 2.
- Produces: nothing later in Phase A. Phase B reads the resolved axis position set.

- [ ] **Step 1: Measure the starting word count**

```bash
php skills/framework-audit/assets/framework-audit.php . --word-report | grep ux-design-system
```

Expected: `552`. The ceiling is 600 and it is a FAIL. Budget for this task: **+40 words maximum**, so text must be traded, not added.

- [ ] **Step 2: Update the existing check to the new vocabulary**

`RT_UXDS_NO_CAPA2_STEP` currently requires the literal string `CAPA 2`. The dialogue replaces it, so the check must require the new thing instead — this is a deliberate change of what is pinned, not a weakening:

```php
	if ( false === strpos( $uxds_src, 'axis' ) && false === strpos( $uxds_src, 'Axes' ) ) {
		add( 'RT_UXDS_NO_CAPA2_STEP', 'FAIL', 'ux-design-system', 'SKILL.md never mentions the axes — the personality dialogue is unreachable from the skill' );
	}
```

Update the `ROW_TYPES` description to match: `'FAIL  — ux-design-system/SKILL.md has no axis-resolving dialogue step'`. Update the `CONTRIBUTING.md` row too.

- [ ] **Step 3: Add a fixture for it**

```php
echo "--- una skill de diseno que no nombra los ejes no puede resolverlos ---\n";
$r85 = fx_tmp_root();
fx_base( $r85 );
fx( $r85, 'skills/ux-design-system/SKILL.md',
	"---\nname: ux-design-system\ndescription: \"Trigger: fixture.\"\nlicense: MIT\nmetadata:\n  author: fixture\n  version: \"1.0\"\n---\n\n"
	. "## Execution Steps\n1. Pick something.\n\n## References\n- `references/design-personalities.md`\n" );
list( , $out85 ) = fx_run_ok( $audit, $r85 );
ok( 'FAIL' === fx_row_level( $out85, array( 'RT_UXDS_NO_CAPA2_STEP' ) ), 'una SKILL.md sin los ejes FALLA', fx_row_level( $out85, array( 'RT_UXDS_NO_CAPA2_STEP' ) ) );
fx_rrmdir( $r85 );
```

- [ ] **Step 4: Rewrite the Execution Steps**

Replace steps 1 and 5 (the CAPA 2 recommender and the CAPA 3 toggles) with:

```markdown
1. Resolve the FIVE AXES with 3–5 questions in business terms, never "which personality do you
   want". Precharge each from the industry `web-templates` already reported; the client confirms
   or overrides. One answer usually moves several axes — a stone fabricator asked "material
   catalogue or gallery of finished work?" moves ground, composition and density at once. **Every
   axis must end resolved**: ask explicitly for any the answers did not reach, because an axis
   nobody sets falls to the same value on every project, which is how sites end up identical.
   Land on an anchor from `references/design-personalities.md`, or between two — both are valid.
```

Delete the CAPA 1/2/3 framing sentences in "The 3 layers" and replace with two lines naming the axes and the anchors. Trade the words; do not add them.

- [ ] **Step 5: Verify the word budget and the gate**

```bash
php skills/framework-audit/assets/framework-audit.php . --word-report | grep ux-design-system
php skills/framework-audit/assets/framework-audit.php . 2>&1 | tail -3
for t in tests/*.php; do printf "%-34s " "$t"; php "$t" 2>&1 | tail -1; done
```

Expected: word count **under 600** (under 500 is the aim and clears the WARN entirely), `0 FAIL` on the audit, `0 FAIL` on all four suites.

- [ ] **Step 6: Commit**

```bash
git add skills/ux-design-system/SKILL.md skills/framework-audit/assets/framework-audit.php CONTRIBUTING.md tests/test-framework-audit.php
git commit -m "feat(ux-design-system): the designer asks, and every axis must end resolved"
```

---

## Done when

- `php skills/framework-audit/assets/framework-audit.php .` exits 0 with no FAIL.
- All four suites report `0 FAIL`.
- `RT_PERS_TOO_SIMILAR`, `RT_PERS_BAD_AXIS` and `RT_AXIS_VALUE_MISSING` each have a fixture; `COVERAGE_EXEMPT` is still `array()`.
- `design-personalities.md` holds exactly four anchors, each with a valid `**Axes:**` line, and no pair shares more than one position.
- `ux-design-system/SKILL.md` is under 600 words.

Phase B (the two mockups over one shared content file) is planned separately, after this lands, because building them will teach things about these tokens that no amount of specifying will.
