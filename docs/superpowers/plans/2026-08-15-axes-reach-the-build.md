# The axes reach the WordPress build (Phase C) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development or superpowers:executing-plans. Steps use checkbox (`- [ ]`) syntax.

**Goal:** Give `es-builder.php` the token layer its own documentation already claims it has, then make the scale, density and elevation axes actually move the emitted Elementor data.

**Architecture:** One `es_tokens()` block near the top of `es-builder.php` holds every visual value. Every `es_*` helper reads from it and nothing below it types a colour, a family, a size or a shadow. Extraction lands first with today's values, proven byte-identical; derivation lands second and is allowed to move values, recorded in a table. The three sibling assets already `require_once` `es-builder.php`, so they inherit the layer.

**Tech Stack:** PHP 8, the repo's four suites, `framework-audit`.

## Global Constraints

- **Never weaken a check to make a row go green** (CONTRIBUTING.md).
- Token VALUES for axis positions come from `skills/web-templates/references/design-system.md` § "Perceptual axes — token values". Never invent a number.
- New `RT_*` needs a `ROW_TYPES` entry, a `CONTRIBUTING.md` table row, and a fixture. `COVERAGE_EXEMPT` stays `array()`.
- The working tree is CRLF (`core.autocrlf=true`); the repo stores LF. Any mutation anchor written with `\n` must be translated before use.
- `skills/elementor-core/SKILL.md` is at **598 of a 600-word FAIL ceiling** — two words of headroom. Anything added there is paid for by trimming. Measure with:
  ```bash
  php skills/framework-audit/assets/framework-audit.php . --word-report 2>/dev/null | grep elementor-core
  ```
  Column 1 is the body count that the gate reads; column 2 is marker words, which are excluded.
- Every `es_*` helper this plan touches is already covered by `tests/test-write-path.php` (253 assertions). A new warning added to a covered function **launders the assertions that share `grab()`'s stdout buffer** — if you add one, re-mutate every assertion in that function, not just yours.

## Why this exists

`skills/elementor-core/SKILL.md:54-55` instructs the operator:

> *"Copy `assets/es-builder.php` into `wp-content/novamira-sandbox/`; swap its **palette/type constants** for the brand from `ux-design-system`."*

**Those constants have never existed.** `rg 'define\(|^const '` over `es-builder.php` returns nothing — zero constants of any kind, confirmed. What an operator actually faces is 51 colour literals, 9 font strings, 12 absolute font sizes, 29 spacing tuples, 8 radii and 5 shadows, typed inline between lines 108 and 600. `#0FA968` alone is typed 11 times across 10 lines.

The drift this produces is already visible in the file: `#EAECEA`, `#E0E4E0`, `#D6DAD6` and `#CBD0CB` are four near-identical neutral borders that started as one value and came apart. And the accent glow exists twice as `rgba(15,169,104,0.55)` (line 358) and `rgba(15,169,104,0.5)` (line 244).

Meanwhile the axes are enforced from `design-system.md` all the way through the mockups by 14 row types. Then the contract sentence at `skills/html-mockup/SKILL.md:51` hands over "inventories + tokens" — the word *axes* is dropped at exactly that hop — and the builder receives an instruction pointing at constants that aren't there.

So today every NovaMira site is the same green on the same white with the same two families, no matter what the axis dialogue resolved. That is the defect.

## What is deliberately NOT in this plan

**The composition axis.** `LP-ASYMMETRIC` vs `LP-STRICT-GRID` is not a value you can look up — it is which helper you call in what arrangement. The approved mockup already carries composition and is already the frozen visual contract the build must match, so composition arrives through the mockup, not through a token. Do not invent a `'composition'` token entry.

**The Elementor global kit.** `skills/elementor-core/references/knowledge.md:246-248` advises setting global colors/typography on the kit so the whole site inherits, and nothing implements it. That remains true after this plan. It is a genuine follow-on with a real benefit — widgets the client adds later would inherit the design — but it is strictly downstream: you cannot write a kit from values you have not centralised first. Named here so it is not mistaken for an oversight.

**`divi-core`.** It has no `assets/` and no PHP at all — `SKILL.md:51-52` describes the `di_*` helper library in the future tense. There is nothing to retrofit. Greenfield, separate plan.

## File Structure

| File | Responsibility after this change |
|---|---|
| `skills/elementor-core/assets/es-builder.php` | Holds `es_tokens()` / `es_t()` / `es_fs()` / `es_sp()`; no visual literal survives in the component region. |
| `skills/elementor-theme-parts/assets/es-theme-parts.example.php` | Header/footer read the tokens instead of 37 hex literals. |
| `skills/woocommerce/assets/es-product-single.example.php` | Reads the tokens instead of 31 hex literals. |
| `skills/woocommerce/assets/es-shop-template.example.php` | Reads the tokens instead of 22 hex literals. |
| `skills/framework-audit/assets/framework-audit.php` | `RT_BUILDER_NO_TOKENS`, `RT_BUILDER_HARDCODED_TOKEN`. |
| `tests/test-write-path.php` | Token-layer behaviour tests. |
| `tests/test-framework-audit.php` | The two new row types' fixtures. |
| `skills/elementor-core/SKILL.md` | Step 2 names the real mechanism and the anchor. Paid for by trimming. |
| `skills/html-mockup/SKILL.md` | The handover sentence carries the axes across the last hop. |
| `CONTRIBUTING.md` | Two row-type rows. |

---

### Task 1: The token layer, with today's values, proven byte-identical

The whole point of this task is that **nothing changes visually**. Extraction and derivation are separated so that if the emitted data moves, you know it was Task 2 that moved it.

**Files:**
- Modify: `skills/elementor-core/assets/es-builder.php` (lines 108–600, the component region)
- Test: `tests/test-write-path.php`

**Interfaces:**
- Produces: `es_tokens( array $override = array() )`, `es_t( string $key )`. Tasks 2–5 consume these exact names.

- [ ] **Step 1: Capture the baseline BEFORE touching anything**

Write `/tmp/es-dump.php` (scratch, never committed) that requires `es-builder.php` and prints a deterministic JSON dump exercising every visual helper:

These are the verified signatures — argument ORDER is not what you would guess for two of them (`es_grid` takes `$cols` first, `es_feature_card` takes `$icon` first):

```php
<?php
require __DIR__ . '/../skills/elementor-core/assets/es-builder.php';
es_uid_reset( 'baseline' );   /* es-builder.php:27 — ids must be reproducible or the diff is noise */
$out = array(
	es_section( array() ),
	es_split( array() ),
	es_grid( 3, array() ),
	es_row( array() ),
	es_p( 'texto' ),
	es_h( 'titulo', 'h2' ),
	es_btn( 'Comprar', '#', 'primary' ),
	es_btn( 'Comprar', '#', 'dark' ),
	es_btn( 'Comprar', '#', 'outline' ),
	es_btn( 'Comprar', '#', 'outline-light' ),
	es_feature_card( 'icono', 'titulo', 'texto' ),
	es_card_hover_css(),
	es_products_css(),
	es_box( 88, 24, 88, 24 ),
);
echo json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ), "\n";
```

`es_btn()`'s four style branches live at `es-builder.php:371-403` — all four must be in the dump or the branch you miss is the one that keeps its literal.

```bash
php /tmp/es-dump.php > /tmp/baseline.json && wc -l /tmp/baseline.json
```

**A dump whose ids drift between runs is worthless as a baseline** — run it twice and `cmp` the two before you trust it.

- [ ] **Step 2: Write the token block**

Insert immediately above the component region (before `es_section`, around line 105), between explicit markers so Task 3's check has a boundary to read:

```php
/* ---------------------------------------------------------- design tokens
   This block IS what elementor-core/SKILL.md step 2 means by "swap the
   constants": ONE edit point per project, filled from the axis positions the
   ux-design-system dialogue resolved. The values below are the framework
   default, not a recommendation for any client -- a site that ships with them
   unchanged is a site nobody made a decision about.

   Nothing between es_tokens() and the END marker below types a colour, a
   family, a size or a shadow. RT_BUILDER_HARDCODED_TOKEN enforces it. */
function es_tokens( array $override = array() ) {
	static $t = null;
	if ( null === $t || $override ) {
		$t = array_merge(
			array(
				/* ground ------------------------------------------------ */
				'bg'           => '#FFFFFF',
				'bg_alt'       => '#F6F7F8',
				'text'         => '#15181A',
				'muted'        => '#6A6F6C',
				'border'       => '#E5E7E5',
				/* accent -- derives from the BRAND, never from the anchor.
				   design-tokens.md is explicit that accent is not an axis. */
				'accent'       => '#0FA968',
				'accent_hover' => '#0C8A55',
				'on_accent'    => '#FFFFFF',
				/* scale ------------------------------------------------- */
				'font_head'    => 'Space Grotesk',
				'font_body'    => 'Manrope',
				/* density ----------------------------------------------- */
				'radius'       => 10,
				/* elevation --------------------------------------------- */
				'elev_hover'   => '0 18px 40px -12px rgba(21,24,26,0.16)',
				/* motion ------------------------------------------------ */
				'ease'         => 'cubic-bezier(.22,1,.36,1)',
			),
			$override
		);
	}
	return $t;
}

function es_t( $key ) {
	$t = es_tokens();
	if ( ! array_key_exists( $key, $t ) ) {
		es_warn( 'es_t("' . $key . '") no existe en es_tokens(); revisa el nombre.' );
		return '';
	}
	return $t[ $key ];
}
```

**The key list above is a starting point, not the answer.** Build it from what you actually find in lines 108–600. Every distinct value gets a key named for its ROLE, never for its appearance — `muted`, not `grey`; a token called `green` cannot survive a client whose brand is navy.

The four drifted neutrals (`#EAECEA` 574, `#E0E4E0` 578, `#D6DAD6` 215, `#CBD0CB` 388) are the interesting case. They are four values doing one job. **Do not silently collapse them** — that changes output and breaks Step 5. Give them distinct keys for now (`border_soft`, `border_hover`, …), and report the collapse as a finding for Task 2 where value changes are allowed.

Same for the two accent glows: `rgba(15,169,104,0.55)` (358) and `rgba(15,169,104,0.5)` (244). Two keys now, one finding reported.

- [ ] **Step 3: Add the END marker**

After the last visual helper (`es_feature_card`, ~line 604), before the container-audit machinery:

```php
/* -------------------------------------------------- end of the visual layer
   Everything below is the save pipeline, the container audit, the sandbox and
   the slug machinery. No styling value belongs here, and RT_BUILDER_HARDCODED_TOKEN
   does not scan past this line -- line 1962 contains "#732" inside a Spanish
   warning string as a fake post id, and a colour regex cannot tell it apart. */
```

- [ ] **Step 4: Replace every literal in the region**

Work top to bottom. `'#0FA968'` becomes `es_t('accent')`, `'Manrope'` becomes `es_t('font_body')`, and inside the concatenated CSS strings (`es_card_hover_css`, `es_products_css`, `es_btn`, `es_feature_card`) the literals become `. es_t('accent') .` interpolations.

Watch the CSS strings specifically: they are the easiest place to leave a literal behind because it is inside a quoted blob rather than an array value.

Leave font SIZES, spacing and radii-as-numbers alone for now where they are one-off — Task 2 owns those. This task owns colours, families, shadows and easing.

- [ ] **Step 5: Prove the output is byte-identical**

```bash
php /tmp/es-dump.php > /tmp/after.json && cmp /tmp/baseline.json /tmp/after.json && echo "IDENTICO"
```

**This must print `IDENTICO`.** If it does not, the diff tells you exactly which literal you fat-fingered. Do not proceed, and do not "accept" a diff as harmless — the entire value of this task is that the transformation is provably inert.

- [ ] **Step 6: Run the suites**

```bash
for t in tests/*.php; do printf "%-34s " "$t"; php "$t" 2>&1 | tail -1; done
php skills/framework-audit/assets/framework-audit.php . 2>&1 | tail -3
```

Expected: `22 / 81 / 330 / 253` all `0 FAIL`, audit exit 0. If a write-path assertion fails, the byte-identical proof in Step 5 was wrong.

- [ ] **Step 7: Add the behaviour test**

In `tests/test-write-path.php`, following the file's existing assertion style:

```php
/* The layer is only real if overriding a token changes the emitted data. A
   token nothing reads is a token that reverts every project to one look. */
es_tokens( array( 'accent' => '#B4001F' ) );
$json = json_encode( es_btn( 'Comprar', '#', 'primary' ) );
ok( false !== strpos( $json, '#B4001F' ), 'el acento del token llega al boton primario' );
ok( false === strpos( $json, '#0FA968' ), 'el verde por defecto ya no aparece tras el override' );
```

The second assertion is the one that matters. The first passes even if the default is *also* still emitted somewhere.

Reset the static afterwards so later tests are not polluted — `es_tokens( array( 'accent' => '#0FA968' ) )`, or add a documented reset path if the file's test style has one.

- [ ] **Step 8: Mutate**

1. Change one `es_t('accent')` back to the `#0FA968` literal → the Step 7 "ya no aparece" assertion must fail. If it survives, that call site is not on any tested path; say so rather than inventing a test.
2. Make `es_t()` return the key instead of the value → many assertions must fail.
3. Delete the `array_key_exists` guard in `es_t()` → nothing may break silently; if nothing fails, the guard is unreachable and should be **deleted**, not covered. A surviving mutant is a branch nothing can distinguish, not a weak assertion.

Report `corridos / muertos / supervivientes / SIN CORRER`. A mutation anchor that never ran is neither killed nor surviving and must be reported as broken, not counted as passing.

- [ ] **Step 9: Commit**

```bash
git add skills/elementor-core/assets/es-builder.php tests/test-write-path.php
git commit -m "refactor(elementor-core): the constants SKILL.md always promised now exist"
```

---

### Task 2: Derivation — the scale, density and elevation axes start moving values

Task 1 made the values addressable. This task makes three of them *derived*, which is what turns an axis into something that can move.

Values WILL change here. That is the point, and it is why it is a separate task.

**How a value change is recorded, now that Task 1 committed a golden dump.** `tests/fixtures/emitted-golden.txt` pins what the builder emits, and `tests/test-write-path.php` re-checks it on every run. So every step below that moves a value ends with:

```bash
php tests/dump-emitted.php > tests/fixtures/emitted-golden.txt
git diff tests/fixtures/emitted-golden.txt
```

That diff **is** the "WRITE DOWN THE SHIFT" table — paste it, do not paraphrase it. Regenerating without showing the diff is how a value moves without anyone deciding it should.

**Three things Task 1 found and deliberately left here, because fixing any of them moves bytes:**

1. **The bare `ease` keyword.** `es-builder.php` types the CSS keyword `ease` 9 times on 5 lines, *inside the same rules* as the tokenised curve — `transition:transform .5s cubic-bezier(.22,1,.36,1), border-color .5s ease`. So `transform` eases on the house curve and `border-color` falls back to the browser default: two motion languages in one rule. Tokenise them onto `es_t('ease')` and record the diff.
2. **`accent_hover` is not derived.** It is a hand-picked darker green (`#0C8A55`). A navy brand gets a navy accent and a hand-picked *green-derived* hover unless someone remembers to change both. It wants a shade function, not a second literal.
3. **The drift collapses Task 1 named**: four neutral borders into one + a derived hover, the two accent glows into one, and `on_accent_short` (`#fff`) deleted in favour of `on_accent`.

**And one landmine defused in advance.** `'elev_rest' => 'none'` collides with `display:none!important` in `es_products_css`. The structural probe now declares its residual instead of demanding zero — when you add `elev_rest`, the probe goes red with the exact remedy in the message: add `'none' => array( 1, 'display:none!important en es_products_css()' )` to `$residuo` in `tests/test-write-path.php`. Declaring the residual is the correct move and keeps the check strong (a call site that keeps the literal by hand still turns it red at count 2). Do **not** loosen the assertion.

**Files:**
- Modify: `skills/elementor-core/assets/es-builder.php`
- Test: `tests/test-write-path.php`

**Interfaces:**
- Consumes: `es_tokens()`, `es_t()` from Task 1.
- Produces: `es_fs( int $step )`, `es_sp( int|float $px )`. Task 3's check and Task 4's siblings consume these.

- [ ] **Step 1: Add the derivation tokens and helpers**

Into the `es_tokens()` array, with values copied from `design-system.md`'s `classic` / `standard` / `hairline` positions — the framework default. Do not invent numbers; if a value is missing there, that is a finding to report, not a gap to fill:

```php
				/* scale */
				'fs_base'    => 16,
				'type_ratio' => 1.333,
				'display_lh' => 1.10,
				'fs_h1_max'  => 64,
				/* density */
				'sp_scale'   => 1.0,
				/* elevation */
				'elev_rest'  => 'none',
```

And below `es_t()`:

```php
/* One step on the type scale. Step 0 is body; each step up multiplies by the
   ratio. This is what the scale axis IS -- one number moves the whole
   hierarchy, instead of twelve sizes that happen to look related.
   Capped at fs_h1_max so a monumental ratio cannot run a heading off a phone. */
function es_fs( $step ) {
	$t  = es_tokens();
	$px = $t['fs_base'] * pow( $t['type_ratio'], $step );
	return round( min( $px, $t['fs_h1_max'] ), 1 );
}

/* Every spacing value in the file goes through here, so density is one
   multiplier over the whole rhythm rather than 29 numbers to re-tune. */
function es_sp( $px ) {
	return (int) round( $px * es_tokens()['sp_scale'] );
}
```

- [ ] **Step 2: Map the twelve font sizes onto steps, and WRITE DOWN THE SHIFT**

Today's sizes and their lines: `12` (309) · `16` (336) · `15` (368) · `19` (430) · `14.5` (434) · `38` (499) · `27` mobile (500) · `16` (510) · `17` (545) · `14.5` (549) · `19` (599) · `14.5` (600).

At ratio `1.333` from base `16`, the steps are `16 · 21.3 · 28.4 · 37.9 · 50.5 · 64(cap)` upward and `12 · 9` downward.

Choose the nearest step for each and put the before/after in a table in your report. Some will move a lot — `38 → 37.9` is nothing, but `14.5 → 12` or `→ 16` is a visible change, and a reviewer needs to see it stated rather than discover it.

Where a size genuinely is not on the scale and should not be, keep it as its own token entry and say why in a comment. **Do not bend a number to fit the ratio** and do not pretend a forced fit is a derivation.

- [ ] **Step 3: Route spacing through `es_sp()`**

Apply it **inside `es_box()`** rather than at the 29 call sites — one change, and no call site can forget.

**`es_box()` takes a unit:** `es_box( $t, $r, $b, $l, $unit = 'px' )` (`es-builder.php:53`). Every call today uses the `px` default — verified, zero non-px call sites across all four assets — but the parameter exists, and multiplying a `%` or `vh` by a density scale is wrong, not merely different. Guard it:

```php
	/* Density scales LENGTHS. A percentage or a viewport unit is already
	   relative to something else, and multiplying it is not a smaller gap --
	   it is a different layout. Today every call is px; the guard is here so
	   the first non-px call does not silently become a bug. */
	if ( 'px' === $unit ) {
		$t = es_sp( $t ); $r = es_sp( $r ); $b = es_sp( $b ); $l = es_sp( $l );
	}
```

Then the three gap defaults, which do not pass through `es_box()`: `48` (`es_split`, 139), `24` (`es_grid`, 258), `14` (`es_row`, 285).

- [ ] **Step 4: Introduce the rest-state elevation, which does not exist today**

There is currently **no** rest shadow anywhere in the build — only five `:hover` shadows. That is why `PERS-MATTER`'s `hairline` and `PERS-INSTITUTIONAL`'s `soft-shadow` cannot be expressed at all.

Give the card recipes a `box_shadow` at rest fed by `es_t('elev_rest')`, defaulting to `none` so Task 1's baseline behaviour is preserved for the default anchor. Take the shadow values for each position verbatim from `design-system.md`.

- [ ] **Step 5: Prove the axes move**

```php
/* The falsifiable claim of this whole effort: change the ratio, the headings
   move; change the multiplier, the rhythm moves. If these pass with the axis
   values swapped and the output does not change, the axis is decorative. */
es_tokens( array( 'type_ratio' => 1.618, 'fs_h1_max' => 120 ) );
$big = es_fs( 3 );
es_tokens( array( 'type_ratio' => 1.200, 'fs_h1_max' => 48 ) );
$small = es_fs( 3 );
ok( $big > $small, 'un ratio monumental produce un display mayor que uno contenido' );
ok( $big <= 120 && $small <= 48, 'cada posicion respeta su propio tope' );

es_tokens( array( 'sp_scale' => 1.7 ) );
$airy = es_sp( 88 );
es_tokens( array( 'sp_scale' => 0.8 ) );
$tight = es_sp( 88 );
ok( $airy > $tight, 'la densidad generosa deja mas aire que la compacta' );
ok( 150 === $airy && 70 === $tight, 'los valores derivados son los esperados, no solo el orden' );
```

The last assertion is doing real work: `$airy > $tight` also passes for a function that returns `$px * 2` and `$px`. Pin the numbers.

- [ ] **Step 6: Suites, audit, and a render check**

Run all four suites and the audit. Then, because paper checks cannot see what only exists at render time, build one page with `sp_scale => 1.7` and `type_ratio => 1.618, fs_h1_max => 120` and confirm nothing overflows or collides. If there is no local render path, say so plainly in the report rather than claiming a check you did not run.

- [ ] **Step 7: Mutate**

1. Make `es_fs()` ignore `$step` (always return `fs_base`) → Step 5's ordering assertion must fail.
2. Remove the `min()` cap → the tope assertion must fail.
3. Make `es_sp()` return `$px` unchanged → the density assertions must fail.
4. Set `elev_rest` to `none` unconditionally → the Step 4 behaviour must fail.

- [ ] **Step 8: Commit**

```bash
git add skills/elementor-core/assets/es-builder.php tests/test-write-path.php
git commit -m "feat(elementor-core): scale, density and elevation stop being decoration"
```

---

### Task 3: The check that keeps the literals out

**Files:**
- Modify: `skills/framework-audit/assets/framework-audit.php`
- Modify: `CONTRIBUTING.md`
- Test: `tests/test-framework-audit.php`

- [ ] **Step 1: Write the failing fixtures**

Four of them, using the suite's existing fixture helpers:

1. A builder asset with `es_tokens()` and no literal in the visual region → must not fire.
2. One with `#0FA968` in the visual region → must FAIL naming the literal AND its line.
3. One with no `es_tokens()` function at all → must FAIL with `RT_BUILDER_NO_TOKENS`.
4. **One with `'pagina #732 no existe'` below the END marker → must NOT fire.** This is the trap: `es-builder.php:1962` really does carry `#732` inside a Spanish warning as a fake post id, and a hex regex cannot tell it from a colour. If your fixture set omits this case, the check will FAIL the real file the moment it is switched on.

- [ ] **Step 2: Run, verify red** — neither row type is declared, so `add()` exits 3.

- [ ] **Step 3: Declare and implement**

```php
	'RT_BUILDER_NO_TOKENS'       => 'FAIL  — a builder asset has no es_tokens() block',
	'RT_BUILDER_HARDCODED_TOKEN' => 'FAIL  — a builder asset types a visual literal outside its token block',
```

Scope the literal scan to the region between the two markers Task 1 added, following the precedent `RT_MOCKUP_NO_AXES` set at `framework-audit.php:1400-1403` — it reads only the `:root` block deliberately, because *"a whole-file scan would match a USE and call it a declaration"*. Same discipline, inverted: outside the visual region a colour is inert, and `#732` proves a whole-file scan is unsafe.

**Get the region's START boundary exactly right, and add a fixture for it.** The region begins after the **closing brace of `es_t()`**, not after `es_tokens()`. Anchoring on `es_tokens()` puts the token DECLARATIONS inside the scanned region, and every one of them is a hex literal by definition — the check then reports 27 findings against a correct file and is useless. I made exactly this mistake on the first attempt at a hand-scan; the correct region is `es-builder.php:188` to the END marker, 518 lines. Write a fixture whose token block contains hex values and assert the check stays silent, or the next person re-lives it.

Detect: 3- and 6-digit hex, `rgba(`, `cubic-bezier`, and a literal on `typography_font_family`. Report the file, the line and the literal — "hay un literal" is not actionable, `es-builder.php:388 → #CBD0CB` is.

If a builder asset has no END marker, that is itself the FAIL: an unbounded region is an unscannable one.

- [ ] **Step 4: `CONTRIBUTING.md` rows**

```markdown
| `RT_BUILDER_NO_TOKENS` | FAIL | a builder asset has no `es_tokens()` block |
| `RT_BUILDER_HARDCODED_TOKEN` | FAIL | a builder asset types a visual literal outside its token block |
```

- [ ] **Step 5: Gates** — audit `0 FAIL`, four suites `0 FAIL`, `COVERAGE_EXEMPT` still `array()`.

- [ ] **Step 6: Mutate**

1. Drop `rgba(` from the detected set → fixture 2 must stop failing if you extend it with an `rgba()` literal; add that case.
2. Make the scan read the whole file instead of the region → **fixture 4 must start failing**. This mutant is the one that proves the boundary is load-bearing.
3. Drop the line number from the message → the assertion checking for a line must break.
4. Make `RT_BUILDER_NO_TOKENS` accept any file → fixture 3 must stop failing.

- [ ] **Step 7: Commit**

```bash
git add skills/framework-audit/assets/framework-audit.php CONTRIBUTING.md tests/test-framework-audit.php
git commit -m "feat(framework-audit): a literal in the builder is a project that cannot be re-skinned"
```

---

### Task 4: The siblings stop drifting

Three assets carry ~90 more hex literals for the most visible parts of a site — the header, the footer, the shop and the product page. They already `require_once` `es-builder.php` (`es-theme-parts.example.php:28`, `es-product-single.example.php:26`), so the token layer is already in scope for them; they simply do not use it.

A themed body under an un-themed header is worse than no theming at all.

**Files:**
- Modify: `skills/elementor-theme-parts/assets/es-theme-parts.example.php` (37 hex)
- Modify: `skills/woocommerce/assets/es-product-single.example.php` (31 hex)
- Modify: `skills/woocommerce/assets/es-shop-template.example.php` (22 hex)

- [ ] **Step 1: Baseline each one**

Same technique as Task 1 Step 1 — dump before, `cmp` after. These three have no derivation stage, so **byte-identical is the acceptance criterion for all three**.

**Two traps that make this proof silently vacuous. Task 1 hit both; do not re-live them.**

1. **`define( 'ABSPATH', __DIR__ );` is mandatory at the top of your dump script.** `es-builder.php:6-8` exits without it, and so do all three sibling assets by the same guard. Without it your dump is an **empty file — and `cmp` of two empty files passes.** A green proof over nothing is worse than no proof, because it is reported as a pass. Assert the dump is non-empty (`test -s`) and print its line count before you trust a single `cmp`.
2. **PHP on Windows resolves `/tmp/x` as `C:\tmp\x`; Git Bash's `/tmp` is `C:\Users\Juan\AppData\Local\Temp`.** A file PHP "wrote" is not where the shell looks, so you end up diffing a stale or missing file. Use absolute Windows paths inside PHP, or `pwd -W` to build them.

Also stub `get_posts()` and `wp_get_attachment_url()` the way the existing suites already do. Task 1 found that without them, `es_photo`, `es_card`, `es_cta_banner`, `es_eyebrow` and `es_iconbox` fall out of the dump entirely — and `es_cta_banner` alone carries four literals that nothing else covers. **A helper missing from the baseline is a helper whose literals are unprotected**, and the dump gives you no signal that it was skipped.

- [ ] **Step 2: Add the region markers to each**

Same two markers. Task 3's check must cover these three files too — extend its glob and add a fixture per file shape. If the check currently only globs `elementor-core/assets/*.php`, widening it is part of this task, not a follow-up.

- [ ] **Step 3: Replace, file by file, `cmp` after each**

Do not batch the three. One file, one `cmp`, one commit — a diff that covers three files cannot tell you which one broke.

Note the comment at `es-product-single.example.php:142`: *"Match the site green button, hover included."* That comment is the bug in miniature — a instruction to a human to keep two values in sync by hand. Once the token is shared, delete the comment; it describes a coordination problem that no longer exists.

- [ ] **Step 4: Suites and audit**

All four suites `0 FAIL`; `tests/test-write-path.php:342` requires `es-theme-parts.example.php` directly, so it exercises this path.

- [ ] **Step 5: Commit** (three commits, one per file)

```bash
git commit -m "refactor(elementor-theme-parts): header and footer read the shared tokens"
git commit -m "refactor(woocommerce): the product page reads the shared tokens"
git commit -m "refactor(woocommerce): the shop template reads the shared tokens"
```

---

### Task 5: The handoff carries the anchor across the last hop

The chain names the axes at every hop until the last one, where they vanish. Two sentences fix it, and one of them has to be paid for.

**Files:**
- Modify: `skills/html-mockup/SKILL.md:51-52`
- Modify: `skills/elementor-core/SKILL.md:54-55`

- [ ] **Step 1: `html-mockup` hands over the anchor**

Today:
> *"On approval: freeze it as the visual contract and hand inventories + tokens to `elementor-core` / `divi-core`; the build must match it."*

It must also hand the resolved axis positions — the mockup's `:root` already carries them, and the receiving skill now has somewhere to put them. `html-mockup` is at 579/600, so measure after editing.

- [ ] **Step 2: `elementor-core` step 2 names the real mechanism**

Today it says *"swap its palette/type constants"*, pointing at constants that did not exist until Task 1. Now they do — say `es_tokens()` by name, and say the values come from the anchor the `ux-design-system` dialogue resolved.

**This file is at 598 of 600.** Measure before and after:

```bash
php skills/framework-audit/assets/framework-audit.php . --word-report 2>/dev/null | grep elementor-core
```

Trim elsewhere in the file to pay for it. Under 600 is the FAIL gate; under 500 clears the WARN. Report both numbers. If you cannot get under 600 without cutting something load-bearing, **stop and report that** rather than cutting a hard rule to fit a budget.

- [ ] **Step 3: Verify the chain has no gap left**

```bash
rg -n 'PERS-|anchor|ancla|eje|axis|axes' skills/*/SKILL.md
```

Every hop from `web-templates` to `elementor-core` should name the axes or the anchor. Report the chain as a table.

- [ ] **Step 4: Gates and commit**

```bash
git add skills/html-mockup/SKILL.md skills/elementor-core/SKILL.md
git commit -m "docs: the anchor survives the hop into the build"
```

---

## Done when

- `es_tokens()` exists in all four builder assets; no visual literal survives between the markers.
- Task 1 and Task 4 produced **byte-identical** output; Task 2's value shifts are recorded in a table.
- `es_fs()` and `es_sp()` are derived, and swapping a ratio or a multiplier provably moves the emitted data.
- `elev_rest` exists, so `hairline` and `soft-shadow` are expressible for the first time.
- `RT_BUILDER_NO_TOKENS` and `RT_BUILDER_HARDCODED_TOKEN` exist with entries, rows and fixtures — including the `#732` fixture. `COVERAGE_EXEMPT` still `array()`.
- `elementor-core/SKILL.md` names `es_tokens()` and is under 600 words.
- Audit `0 FAIL`; four suites `0 FAIL`.

## The falsifiable criterion

Build the same page set twice, once with `PERS-EDITORIAL`'s positions and once with `PERS-DIRECT`'s, changing nothing but the `es_tokens()` override. If the two sites are not unmistakably different, the axes still do not reach the build and this plan failed — regardless of what the suites say.
