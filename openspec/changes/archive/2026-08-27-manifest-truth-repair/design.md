# Design: manifest-truth-repair

Store: hybrid. Upstream: proposal (Engram #328), spec
`specs/manifest-section-contract/spec.md`. Delivery: `single-pr`, ~120 lines vs a
400-line budget. No sequence diagram — per `openspec/config.yaml` `rules.design`,
this repo has no runtime call graph; the design surface here is instruction
contract and docblock truth, not code architecture.

## Technical Approach

One anchor function plus four text repairs. The manifest API
(`es_manifest_read` / `es_manifest_record` / `es_manifest_verify`) does not
change. The section list stops being prose repeated in three places and becomes
one callable fact; the three prose sites cite it instead of copying it; the one
sentence that sent the front page id into the wrong section is rewritten
word-neutrally; two standing false claims are removed and annotated.

## Architecture Decisions

### Decision: `es_manifest_sections()` returns a flat list, not a writer map

**Choice**: `return array( 'site', 'design', 'pages', 'delivery' );` — names
only. Writer status lives in the docblock, in prose, as observation.

**Alternatives considered**: `array( 'pages' => 'elementor-core:8', 'design' =>
null, … )`, so a gate could read the writer straight off the return value.

**Rationale**: who writes a section is a fact about the tree, and the later slice
`manifest-section-gate` will establish it the only honest way — by grepping for
`es_manifest_record( '<name>'` call sites. A map baked into the return value
would be this file making a claim about the rest of the repo that nothing
re-reads, which is precisely the failure class being repaired: the names were
prose in three places, one copy drifted, and a live manifest got a key that was
not a slug. The framework's own rule is *never claim what you did not read*. The
docblock may state the observation because a reader weighs prose; a return value
gets trusted.

### Decision: exactly four names, order-significant

**Choice**: `site, design, pages, delivery`, asserted with `===` against a
literal array so order and count are both pinned.

**Alternatives**: assert set membership, leaving room for a fifth name.

**Rationale**: `===` on arrays compares keys and order, so one assertion covers
"exactly these, in this order, with no fifth". The roadmap puts capabilities
inside `site`; no fifth section is planned. A subset assertion would let a fifth
name land silently — the same drift, one level up.

### Decision: placement between `es_manifest_read()` and `es_manifest_record()`

**Choice**: insert after `es-builder.php:2414` (`es_manifest_read()`'s closing
brace), before the `es_manifest_record()` docblock at `:2416`. Block order
becomes read → sections → record → verify.

**Alternatives considered**: (a) between the block header docblock `:2386-2402`
and `es_manifest_read()` — rejected, the header would silently re-attach to the
new function and leave `es_manifest_read()` undocumented; (b) above `:2386` —
rejected, a reader arriving at the manifest block would hit a function before
the block's own entry-point docblock; (c) appended after `es_manifest_verify()`
at `:2507` — rejected, the citation added at `:2398-2401` would then point 100
lines down, past all three consumers.

**Rationale**: (b) is the only placement where no existing docblock changes
owner and the diff stays one contiguous insertion, and the citation lands ten
lines from the thing it cites.

### Decision: the `design`/`delivery` gap is stated as observation, not intent

**Choice**: the docblock says these two are written by nothing and read by
nothing today, named here so the gap is countable.

**Rationale**: matches the file's voice — `es_manifest_verify()`'s docblock
(`:2458-2464`) already argues at length that a report must give observed facts
and leave the inference out. "Nothing writes this" is observed. "This will be
wired in slice 1" is a promise, and the axes spec at `docs/superpowers/specs/`
is in this change precisely because it made one.

### Decision: the orchestrator section is deleted with nothing put back

**Choice**: remove `agents/novamira-web-orchestrator.md:289-294` entirely.

**Alternatives considered**: fold the three uncovered items (token block,
approved mockup URL, connector target) into the manifest section at `:14-27`.

**Rationale**: those three are exactly the things the manifest cannot record
today, because `design` and `delivery` have no writer. Moving them up would
replace one false claim with a fresher one. See "What is lost" below.

## Component Map / Data Flow

    es_manifest_sections()          ← the declaration (new)
       │  cited by
       ├── es-builder.php:2398-2401 (block header docblock)
       └── references/knowledge.md  (routes the helper; RT_HELPER_UNROUTABLE)

    SKILL.md step 8  ──record('pages', slug=>id)──┐
    SKILL.md step 8  ──record('site',  front_page_id)──┐
                                                  ▼    ▼
                                   option `es_novamira_manifest`
                                                  │
                                   es_manifest_verify() reads BOTH:
                                     pages.data      → slug drift rows
                                     site.data.front_page_id (:2489-2491)

The defect being repaired is one crossed wire in that diagram: step 8 currently
sends `front_page_id` down the `pages` arrow, where `es_manifest_verify()` reads
it as a slug that is not a slug and emits drift, which fails `qa-review`
house-rule row 24.

## A. Shape and docblock of `es_manifest_sections()`

```php
/**
 * The four section names, in order. The list that used to be spelled out three times.
 *
 * A flat list of names and NOT a `name => writer` map, on purpose. Who writes a section is a fact
 * about the tree, and the only honest way to establish it is to grep the tree for the call. A map
 * here would be this function claiming something about the rest of the repo that nothing re-reads
 * — the exact failure this list closes: the names were prose in three places, one copy drifted,
 * and a live manifest ended up with a key that was not a slug. Never claim what you did not read.
 *
 * Observed today, and expected to change: `pages` is written by `elementor-core` step 8 (slug =>
 * id). `site` is written by the same step (`front_page_id`) and read by es_manifest_verify()
 * below, at :2489. `design` and `delivery` are written by nothing and read by nothing — declared,
 * empty, and named here so the gap is a fact a script can count instead of a line in a comment.
 */
function es_manifest_sections() {
	return array( 'site', 'design', 'pages', 'delivery' );
}
```

The docblock's `site` claim is only true once item 1 lands; the ordering in
section F puts item 1 first so this docblock is never ahead of the tree, not
even in an intermediate commit.

## B. Stopping the two prose restatements

**`es-builder.php:2398-2401`** — keep the shape sentence, drop the parenthesised
list, cite the function, and argue the removal in the file's own voice:

> Sections are namespaced per concern so two skills writing different things
> never overwrite each other's, which a flat map guarantees they eventually
> will. The names are `es_manifest_sections()`, below — spelled out here for a
> while, in a docblock nothing reads, next to two other copies that had already
> drifted from it.

**`references/knowledge.md:52-55`** — this edit is load-bearing twice: it is the
citation *and* the thing that satisfies `RT_HELPER_UNROUTABLE`, which requires
the bare token `es_manifest_sections` to appear in some `.md` (regex
`/(?<![\w])es_manifest_sections(?![\w])/` against every non-hidden `.md` in the
tree, `framework-audit.php:930`). Backtick-plus-paren matches. Replace the
parenthesised list with:

> `es_manifest_sections()` returns the section names, in order — the list, not a
> copy of it. Sections are namespaced so two skills never overwrite each other's,
> which a flat map guarantees they eventually will. Today `pages` is the only one
> `elementor-core` writes (step 8, slug → id); the front page id is a `site` key
> (`front_page_id`), which is where `es_manifest_verify()` reads it; `design` and
> `delivery` are declared and written by nothing.

This is also where the front-page detail cut from `SKILL.md:68` lands, at zero
SKILL.md word cost — `references/knowledge.md` is already reachable, so
reachability is transitive and free (`framework-audit.php:458-478`).

## C. The word-neutral `SKILL.md:68` rewrite

Counting rule is `str_word_count( strip_tags( $body ) )` after valid marker
spans are stripped (`framework-audit.php:683-690`). Digits, `→`, `—`, `…`,
backticks, `**` and `_` are not word characters, so `es_manifest_record` counts
as three words (`es`, `manifest`, `record`) and bold is free.

| | Text | Words |
|---|---|---|
| Before | `` `es_manifest_record('pages', …)` `` — slug → id, and the front page. A `` `false` `` there means the next session starts blind. | **19** |
| After | `` `es_manifest_record('pages', …)` `` — slug → id **only**. Front page id: `` `'site'` ``. A `` `false` `` means the next session starts blind. | **19** |

Net **0**. Breakdown after: `es`+`manifest`+`record`+`pages` = 4;
`slug`+`id`+`only` = 3; `Front`+`page`+`id`+`site` = 4;
`A`+`false`+`means`+`the`+`next`+`session`+`starts`+`blind` = 8.
Before: 4 + (`slug`,`id`,`and`,`the`,`front`,`page`) 6 + (`A`,`false`,`there`,
`means`,`the`,`next`,`session`,`starts`,`blind`) 9. The three `es_manifest_record`
words appear exactly once on both sides, so the net is robust to that tokenising
detail either way.

The two words bought back are `and`/`the` from the old clause and `there` from
the consequence sentence; `there` is redundant because `false` is the return of
the call named one clause earlier. A one-word-cheaper fallback exists (`Front
page: 'site'`, 18) if the measurement disagrees, at the cost of dropping the
word `id` — which is the whole point of the sentence, so use it only under
duress.

**This arithmetic is hand-computed and MUST be confirmed by
`framework-audit.php --word-report` before commit.** Expected reading:
`elementor-core` at 588 or lower, WARN count still 4.

**Flag for the implementer**: `CONTRIBUTING.md:30` states `elementor-core` "sits
at 598". The measured value is **588**. That sentence is stale by 10 words in
the dangerous direction — anyone using it to judge headroom will think there are
2 words left when there are 12. The same paragraph tells you to run
`--word-report` before quoting it. Not fixed here (out of scope, needs its own
decision alongside the stale `openspec/config.yaml` `measured_state`), but do
not trust it while doing this edit.

Also amend, in the same item: `es-builder.php:2454` (say the front page id is
read from the `site` section, not just "the `front_page` id") and `:2459-2461`
(state where the front page id *does* belong, not only where it does not).

## D. The orchestrator deletion

`agents/novamira-web-orchestrator.md:289-294` is the file's last section. Its
opening clause — "nothing in this framework persists state" — is contradicted
265 lines above by `:14-27`, which describes the option that persists exactly
those items.

Coverage check, read directly:

| Content of `:289-294` | Covered by |
|---|---|
| Read/verify before re-deriving; don't re-ask the intake | House rule `:182-186` ("Read the manifest before asking, verify it before trusting an id") + `:14-27` |
| Record each phase | House rule `:182-186` + `:14-27` ("at the end of each phase, one section per concern") |
| Site type, builder, chosen archetype/toggles, page map, front page, what was approved | `:14-27`, itemised |
| **Token block, approved mockup URL, connector target** | **not covered** |
| **The "again after the mockup is approved" checkpoint** | **not covered** |

**What is lost**: those two rows. Accepted, not backfilled. All three orphaned
items belong to `design` / `delivery`, the two sections with no writer — writing
them into `:14-27` would be issuing a fresh promise nothing keeps, which is the
defect this change repairs. They are the payload of `manifest-design-section`
and `manifest-delivery-section`.

**Pre-existing defect found while checking, reported not fixed**: `:17-18`
already overclaims — it says the manifest records "the design personality" and
"what was approved", and nothing writes `design` today. That claim is inherited
by `manifest-design-section`, which makes it true rather than repairs it.

Gate safety, verified against `framework-audit.php`:

- `RT_AGENT_NO_HOUSE_RULES` (`:837-841`): fires only on an absent or bullet-less
  `## House rules` block. That block is at `:~170-200` and is untouched.
- Marker walk (`:843`): runs over House-rules bullets only. The deleted lines
  contain no `(verifier:`-shaped line, so nothing leaves or enters the walk.
- `RT_AGENT_ROUTE_MISSING` (`:815`): fires on a backticked name that resolves to
  no `skills/<name>/` and no `agents/<name>.md`. Deletion can only remove
  candidate names, never add one — safe by construction.
- `RT_AGENT_SKILL_UNMENTIONED` (`:822-828`): a router agent must backtick every
  skill directory name at least once. The deleted block backticks exactly one
  token, `TPL-*`, which is not a skill directory. No skill loses its only
  mention. **Implementer must still re-grep** each of the 15 skill names against
  the deleted lines before committing — this is a WARN that would raise the
  count from 4 to 5.
- `RT_AGENT_CODE_BLOCK`: deletion can only remove code blocks.

Agents carry no word budget, so this is a free net reduction.

## E. The two test assertions

Into the **existing** manifest block of `tests/test-write-path.php`, inserted
after `:1126` (end of the "guarda por secciones" happy-path group, before the
"Escribir no es haber escrito" comment at `:1128`). **No new test file** — a new
one must be named on the gate line at `CONTRIBUTING.md:225` or
`RT_GATE_LINE_UNREGISTERED` FAILs.

Helpers reused, as defined in that file: `wp_fake_reset()` (`:80`),
`ok( $cond, $label )` (`:389`). `grab()` (`:401`) and `has()` (`:407`) are not
needed — neither assertion inspects printed output; the happy path emits none.

```php
/* La lista de secciones deja de ser prosa. Estaba escrita a mano en tres sitios y una copia se
   desvio, que es como acabo un `front` dentro del mapa `pages`. Una lista que solo vive en un
   comentario solo se puede leer con los ojos. */
wp_fake_reset();
ok(
	array( 'site', 'design', 'pages', 'delivery' ) === es_manifest_sections(),
	'es_manifest_sections() declara las CUATRO secciones, en orden y sin una quinta'
);

$mal = array();
$n   = 0;
foreach ( es_manifest_sections() as $s ) {
	$n++;
	wp_fake_reset();
	$b = es_manifest_read();
	if ( true !== es_manifest_record( $s, array( 'probe' => $s ) ) ) {
		$mal[] = $s;
		continue;
	}
	$b = es_manifest_read();
	if ( ! isset( $b['sections'][ $s ]['data'] ) || array( 'probe' => $s ) !== $b['sections'][ $s ]['data'] ) {
		$mal[] = $s;
	}
}
ok( 0 < $n && array() === $mal, 'y cada nombre que devuelve es una seccion que el manifiesto acepta y relee igual' );
```

Design notes on the assertions:

- Assertion 1 uses `===` on the literal array: PHP's identity operator compares
  keys **and** order, so count, order and "no fifth" are one assertion.
- Assertion 2 deliberately does **not** re-list the names — re-listing them
  would reintroduce the fourth hand-made copy this change exists to delete. It
  leans on assertion 1 for the roster and only checks round-tripping.
- `0 < $n` is the vacuous-pass guard: an empty return would make the loop body
  unreachable and `array() === $mal` trivially true. Without it, deleting the
  return value's contents would pass this assertion.
- `wp_fake_reset()` inside the loop keeps each section's round trip independent,
  so a section that only works because a previous one primed the option cannot
  hide.
- Exactly two `ok()` calls → `test-write-path.php` goes 426 → 428, total
  1193 → 1195. **The `:1172-1182` edit in item 1 must therefore be
  comment-only** — it may not add or remove an `ok()` call, or the expected
  1195 is wrong.

**Red under this harness is a fatal, not a FAIL line.** With
`es_manifest_sections()` absent, PHP raises `Error: Call to undefined function
es_manifest_sections()` at `:~1128` and the run aborts, skipping the ~300
assertions below it. That is the RED signal and it is unambiguous because the
fatal names the function. Confirm the fatal names `es_manifest_sections` — not
some other symbol — before writing the green.

## F. Ordering within the single PR

`strict_tdd: true`. Six steps, each independently verifiable:

1. **RED** — the two assertions (E). Run the gate chain: fatal on undefined
   `es_manifest_sections`. This is the red.
2. **Item 1** — `SKILL.md:68` rewrite + `es_manifest_verify()` docblock
   `:2454`/`:2459-2461` + the comment-only amendment at `:1172-1182`. Verify
   with `--word-report` **alone**, in isolation: this is the only hunk in the
   change that can move the word count, so measuring it by itself makes
   attribution exact. The test file is still red (the fatal at `:~1128` aborts
   before `:1172`), which is fine — `--word-report` does not read the tests.
3. **GREEN** — `es_manifest_sections()` + docblock (A). Run the full chain:
   1195 OK / 0 FAIL. Step 2 precedes this so the docblock's claim that step 8
   writes `site` is true at the moment it is written, not just at merge.
4. **Item 3b** — the two citations (B). Audit re-run.
5. **Item 2** — the orchestrator deletion (D). Audit re-run: 0 FAIL / 4 WARN /
   0 JUDGE across 15 skills + 2 agents.
6. **Item 4** — annotate `docs/superpowers/specs/2026-08-14-perceptual-axes-design.md:172`.
   Original sentence byte-unchanged; the annotation is adjacent and new, stating
   that the write does not exist yet and citing that only `elementor-core`
   mentions the manifest today. Full chain + `--word-report`.

Highest-risk item second, isolated, with its own measurement; lowest-risk items
last, where a regression is trivially bisectable.

## File Changes

| File | Action | Description |
|---|---|---|
| `tests/test-write-path.php` (~`:1126`) | Modify | +2 `ok()` assertions in the existing manifest block |
| `tests/test-write-path.php:1172-1182` | Modify | Comment only — name where the front page id belongs. Zero assertion delta |
| `skills/elementor-core/SKILL.md:68-69` | Modify | Word-neutral rewrite, 19 → 19 |
| `skills/elementor-core/assets/es-builder.php` (after `:2414`) | Create | `es_manifest_sections()` + docblock |
| `skills/elementor-core/assets/es-builder.php:2398-2401` | Modify | Cite the function, drop the copied list |
| `skills/elementor-core/assets/es-builder.php:2454`, `:2459-2461` | Modify | Verify docblock names `site` as the front page id's home |
| `skills/elementor-core/references/knowledge.md:52-55` | Modify | Cite the function; route the helper; absorb the front-page detail |
| `agents/novamira-web-orchestrator.md:289-294` | Delete | Contradicts `:14-27`; duplicates House rule `:182-186` |
| `docs/superpowers/specs/2026-08-14-perceptual-axes-design.md:172` | Modify | Annotate, do not delete |

## Interfaces / Contracts

```php
/** @return string[] Exactly four section names, order-significant. */
function es_manifest_sections();  // array('site','design','pages','delivery')
```

No signature, return shape or side effect of `es_manifest_read()`,
`es_manifest_record()` or `es_manifest_verify()` changes. `schema` stays `1`.

## Testing Strategy

| Layer | What | How |
|---|---|---|
| Unit | Section list is exactly four names, in order | `===` against a literal array, `tests/test-write-path.php` |
| Integration | Every declared name round-trips through the WordPress double | loop + `wp_fake_reset()` per name, `es_manifest_record` → `es_manifest_read` |
| Static | Word budget, helper routability, agent gates | `framework-audit.php`, `--word-report` |
| Prose | Citations replaced restatements | Reviewer reads the three sites; no machine check exists (see Risks) |

## Threat Matrix

N/A — no routing, shell, subprocess, VCS/PR automation, executable-file
classification or process-integration boundary. The change adds one pure
function with no arguments, no I/O and no side effects, plus text edits.

## Migration / Rollout

No migration. No schema bump, no persisted-format change. A manifest written
before, during or after this change stays valid and is read identically.

## Rollback

Per item, single hunk, no ordering dependency between reverts:

- Items 1, 2, 4 and the two citations: one-hunk text reverts.
- `es_manifest_sections()`: **verified callable by nothing.** A tree-wide grep
  for the token returns hits in exactly two files, both SDD artifacts
  (`openspec/changes/manifest-truth-repair/proposal.md`,
  `.../specs/manifest-section-contract/spec.md`). Zero `.php` occurrences, zero
  call sites in `skills/*/assets/`, zero in `tests/`. Removing the function
  breaks nothing except the two assertions added in step 1, which revert with
  it in the same hunk.
- Full revert restores byte-identical runtime behaviour.

## Risks

1. **The `RT_HELPER_UNROUTABLE` check is currently masked.** `framework-audit.php`
   scans **every** non-hidden `.md` in the tree (`:905-924`), and
   `openspec/changes/manifest-truth-repair/proposal.md` and `spec.md` already
   name `es_manifest_sections`. The WARN will therefore stay silent at step 3
   even if the `knowledge.md` citation is never written. **Verify the
   `knowledge.md` mention by grep, not by the absence of a WARN.** The SDD
   artifacts are in-flight scaffolding; `knowledge.md` is the durable route.
2. **Word budget** (Med). `RT_BODY_OVER_600` is a FAIL and the arithmetic in
   section C is hand-computed. `--word-report` after step 2 is the gate; any
   number above 588, or a WARN count other than 4, means the edit was not
   word-neutral. `CONTRIBUTING.md:30` will mislead by 10 words if consulted.
3. **`ok()` count drift** (Low). Expected 1195 depends on the `:1172-1182` edit
   being comment-only.
4. **No machine check proves a citation replaced a restatement** (Low,
   accepted). The audit can see that the helper is named somewhere; it cannot
   see that the four names stopped being copied. Reviewer-verified until
   `manifest-section-gate` ships `RT_MANIFEST_SECTION_DEAD`.
5. **Stale recorded state, reported not fixed** (pre-existing): `CONTRIBUTING.md:30`
   (598 vs 588) and `openspec/config.yaml` `measured_state` (1164 vs 1193,
   measured 2026-08-24). Both need their own decision.

## Non-goals

Wiring `design` or `delivery` to a real writer; the `schema: 1 → 2` bump; any
new `RT_` row type; any new `qa-review` house-rule row; any behaviour change to
`es_manifest_read`, `es_manifest_record` or `es_manifest_verify`; a fifth
section; repairing the two stale measurements in risk 5; the `:17-18`
overclaim in the orchestrator.

## Open Questions

None blocking.

## Verification

```bash
php skills/html-mockup/assets/gallery/_build-gallery.php
php skills/framework-audit/assets/framework-audit.php && php tests/test-container-hygiene.php && php tests/test-framework-audit.php && php tests/test-audit-signals.php && php tests/test-write-path.php
php skills/framework-audit/assets/framework-audit.php --word-report
```

Baseline on `feat/manifest-truth-repair` today: 0 FAIL / 4 WARN / 0 JUDGE across
15 skills + 2 agents; 1193 OK / 0 FAIL. Expected after: same audit line,
**1195 OK / 0 FAIL**, `elementor-core` at 588 or lower.

## Size forecast

~120 changed lines against a 400-line review budget. `single-pr`, no chaining.
Nothing in this design pushes it materially over 400.
