# Tasks: Style Catalog — art direction per project

> **Size deviation, declared.** The 530-word cap is exceeded. 24 requirements / 48 scenarios / 17
> PRs under strict TDD cannot compress below this without dropping RED-test traceability — the two
> prior attempts failed partly by underspecifying delivery. Declared, not met by dropping content.

## Review Workload Forecast

| Field | Value |
|---|---|
| Estimated changed lines | ~4,600 across 17 PRs (design.md Migration/Rollout table) |
| 400-line budget risk | High in aggregate; Low per-PR after split, except 3 named exceptions |
| Chained PRs recommended | Yes |
| Suggested split | 17 PRs, 6 slices — see Work Units below |
| Delivery strategy | auto-chain |
| Chain strategy | feature-branch-chain |

```text
Decision needed before apply: No
Chained PRs recommended: Yes
Chain strategy: feature-branch-chain
400-line budget risk: High
```

`Decision needed before apply: No` — both the exception scope (PRs 1b/1c/1f, Engram #353) and the
chain strategy are already locked by prior user decisions; `sdd-apply` proceeds without re-asking.

**Note on PR count**: design.md's prose says "16 PRs" but its own Migration/Rollout table
enumerates 17 distinct ids (1a–1f, 2a–2b, 3a–3b, 4a–4c, 5a–5c, 6). This tasks artifact uses the
table's 17 — the prose count is a self-inconsistency in design.md, not a scope change.

### Chain topology (feature-branch-chain)

Tracker branch: `feat/style-catalog` (session param). PR 1a bases on the tracker; every later PR
bases on its immediate predecessor's branch; only the tracker merges to `main`. Revert order is
the exact reverse of the table below (PR 6 first, PR 1a last) — reverting an earlier slice while a
later one stands leaves a style naming an axis position the table no longer defines
(`RT_PERS_BAD_AXIS`, `framework-audit.php:95`, enforced `:1081-1084`).

### Work Units

| PR | Slice | Base branch | Est. | Budget | Focused proof command |
|---|---|---|---|---|---|
| 1a | 1 | tracker | ~200 | OK | `cmp` old/new `index.html` past line 1 (see 1a.4) |
| 1b | 1 | 1a | ~450 | **size:exception** | `fx_mockup()` corporate fixture |
| 1c | 1 | 1b | ~500 | **size:exception** | `fx_mockup()` ecommerce fixture |
| 1d | 1 | 1c | ~250 | OK | `php framework-audit.php` (chassis gates) |
| 1e | 1 | 1d | ~180 | OK | `RT_MOCKUP_AXES_MISMATCH` fixture |
| 1f | 1 | 1e | ~2000 (deletion) | **size:exception** | full chain, files absent |
| 2a | 2 | 1f | ~180 | OK | `--emit-row-types` diff |
| 2b | 2 | 2a | ~300 | OK (atomic commit) | `test-framework-audit.php` |
| 3a | 3 | 2b | ~320 | OK | `test-write-path.php` |
| 3b | 3 | 3a | ~220 | OK | `test-write-path.php` |
| 4a | 4 | 3b | ~300 | OK | `test-framework-audit.php` |
| 4b | 4 | 4a | ~280 | OK | `test-framework-audit.php` |
| 4c | 4 | 4b | ~350 | OK | full chain, file absent |
| 5a | 5 | 4c | ~200 | OK | manifest fixture (WP sandbox) |
| 5b | 5 | 5a | ~180 | OK | ledger fixtures |
| 5c | 5 | 5b | ~150 | OK | precharge fixtures |
| 6 | 6 | 5c | ~300 | OK | `RT_BESPOKE_UNDECLARED` fixtures |

Runtime harness (all PRs): `php skills/framework-audit/assets/framework-audit.php && php
tests/test-container-hygiene.php && php tests/test-framework-audit.php && php
tests/test-audit-signals.php && php tests/test-write-path.php` (`CONTRIBUTING.md:234`) — the green
check for every PR below is this full chain unless a narrower command is named.

Rollback boundary per PR: the PR's own branch is deletable without touching its predecessor, in
reverse-order only (see Chain topology).

**size:exception reasons** (must be pasted verbatim into each PR description):
- 1b/1c: irreducible page-set content (6 / 7 pages sharing one generation pass); a per-page split
  yields PRs that render nothing and pass nothing.
- 1f: ~2000-line pure deletion whose correctness is already discharged by 1a–1e proving the
  generator's output passes every mockup rule before the deletion lands.

## Slice 1 — Break the two-chassis bottleneck (`client-chassis-generation`)

- [x] 1a.1 RED: hash/`cmp` baseline of current `index.html` output.
- [x] 1a.2 Key `$css` (`_build-gallery.php:6339` region) so gallery-only chrome is excluded from
      chassis; `implode()` at `:16621` unaffected.
- [x] 1a.3 Emit chassis `:root` + shell to `assets/chassis/<site-type>.html`, gitignored.
      **Path corrected during apply** (see design.md D1 "Path correction"): the originally specified
      `assets/gallery/chassis/<site-type>.html` collided with `framework-audit.php`'s
      `RT_GALLERY_NOT_DISTINCT` walk (`#(^|/)gallery/#`, `:2683`), which FAILs any `.html` under
      `gallery/` that renders zero `<section class="strip">` — correct for the multi-strip catalog
      page, wrong for a single-site chassis, which can never have one. `assets/chassis/` is a
      sibling of `assets/gallery/`, not a child of it, so the rule never evaluates it; `.gitignore`
      moved with it. `framework-audit.php` itself is untouched — the path was wrong, not the rule.
- [x] 1a.4 GREEN: full chain green.
      **`cmp` proof, corrected.** A naked full-file `cmp` of `index.html` before/after is
      structurally impossible for any generator edit: `_gallery-fingerprint.php` deliberately hashes
      `_build-gallery.php`'s own source as an input, and line 1 of `index.html` is that fingerprint
      comment, so it changes on every edit to the generator by design — this is the fingerprint
      doing its job, not drift. **The correct invariant is: everything after line 1 is
      byte-identical, and total file length is unchanged** (sha256 hex is fixed-width). Verified:
      `tail -n +2` of both old and new `index.html`, `cmp` exit 0; both files 9,068,304 bytes.
      **Runtime harness**: `php skills/framework-audit/assets/framework-audit.php` →
      **0 FAIL / 4 WARN / 0 JUDGE**, the 4 WARNs are exactly the pre-existing word-budget ones
      (elementor-core 588, html-mockup 582, web-templates 559, woocommerce 597) — no
      `RT_GALLERY_NOT_DISTINCT`, no `RT_ORPHAN_FILE` regression. The `RT_ORPHAN_FILE` risk on the two
      new chassis files (unrelated to the path collision — `html_assets_deep()` walks the corrected
      path too) is closed by a prose pointer in `references/mockup-guide.md` § "Chassis and anchor"
      (not `SKILL.md`, which sits at 582/600 words with a standing WARN and no budget left) naming
      `assets/chassis/` and both site-type files, reachable transitively from `SKILL.md`'s existing
      `references/mockup-guide.md` pointers. `php tests/test-container-hygiene.php` 81 OK/0 FAIL;
      `php tests/test-framework-audit.php` 664 OK/0 FAIL; `php tests/test-audit-signals.php`
      22 OK/0 FAIL; `php tests/test-write-path.php` 428 OK/0 FAIL — **1195/1195, full chain green.**
- [x] 1b.1 `fx_mockup()`-based coverage in `tests/test-framework-audit.php` (4 new scenarios, 11
      assertions): the literal claim — RT_MOCKUP_NO_AXES stays silent on the generated corporate
      chassis — was verified empirically to already hold at PR 1a's close (the chassis `:root`
      inherits from `$css`, axis-complete since the shell landed), so it is not a red-before-green
      test in the literal sense; see apply-progress for the live audit run that established this.
      What genuinely had zero synthetic coverage was PR 1a's own path-correction: a chassis under
      `assets/gallery/chassis/` FAILs `RT_GALLERY_NOT_DISTINCT`, only `assets/chassis/` does not —
      proved here for the first time, paired with `RT_MOCKUP_NO_AXES`/`ANCHOR_UNDECLARED`/
      `AXES_MISMATCH` staying silent at the correct path, plus a genuine RED→GREEN pair for
      `RT_MOCKUP_DISCLOSURE_STATE` against the exact `disclosure_list_html()`-shaped FAQ markup
      1b.2 emits (broken shape FAILs, correct shape stays silent).
- [x] 1b.2 Implemented corporate chassis body markup, 6 pages (home, services, service, cases,
      about, contact), reusing `page_head_html()`/`faq_block_html()`/`disclosure_list_html()`/
      `band_closing_html()`/`number_heads()` verbatim and writing chassis-scoped equivalents of
      `head_corporate()`/`crumbs_html()`/`footer_html()`/`page_cta_html()`/`card_html()` because
      those originals route through `ihref_for_label()` (gallery-card routing context this file
      never sets) and `img()` (a real-photography manifest lookup a starting chassis has nothing to
      supply yet). Added `.ph` (mockup-guide.md's own placeholder recipe) and `.page`/`.active` +
      the `show()` switcher (mockup-guide.md's binding multi-page-preview contract) to
      `$chassis_css`, since neither existed in `$css` — the gallery never needed either.
- [x] 1b.3 GREEN: fixture passes; full chain green. **size:exception** (see reason above).
      `php skills/framework-audit/assets/framework-audit.php` → 0 FAIL / 4 WARN / 0 JUDGE (the 4
      pre-existing word-budget WARNs only). Full chain: container-hygiene 81 + framework-audit
      675 (664 + 11 new) + audit-signals 22 + write-path 428 = **1206/1206, 0 FAIL**. `index.html`
      confirmed byte-identical past line 1, same total length (9,068,304 bytes), against the
      pre-PR-1b baseline. Diff stat: 2 files changed, 422 insertions(+), 6 deletions(-) — within
      the ~450-line `size:exception` budget.
- [x] 1c.1 `fx_mockup()`-based coverage in `tests/test-framework-audit.php` (r144-r146, 6 new
      assertions): verified empirically, same discipline as 1b.1 — `html_assets_deep()` is a
      filename-agnostic recursive glob and none of `RT_GALLERY_NOT_DISTINCT`/`RT_MOCKUP_NO_AXES`/
      `RT_MOCKUP_ANCHOR_UNDECLARED`/`RT_MOCKUP_AXES_MISMATCH` read the file name, so the mechanism
      r140/r141 already locked for `corporate.html` covers `ecommerce.html` by construction —
      repeating that pair would fix no new audit behaviour. What genuinely had zero coverage:
      `assets/chassis/ecommerce.html` had no fixture of its own at all, and the PDP accordion shape
      1c.2 emits (three `<details>` under `class="qas"`) is a different shape than 1b's two-item
      `faqlist`. r144 locks correct-path silence for the sibling file (RT_GALLERY_NOT_DISTINCT/
      NO_AXES/ANCHOR_UNDECLARED/AXES_MISMATCH); r145/r146 are a genuine RED→GREEN pair for the
      three-row shape (broken: none open, FAILs; correct: first of three open, silent).
- [x] 1c.2 Implemented ecommerce chassis body markup, 7 pages (home, shop, pdp, cart, checkout,
      about, contact), reusing `page_head_html()`/`disclosure_list_html()`/`number_heads()` verbatim
      plus the corporate section's generic `chassis_ph()`/`chassis_card_html()`/`chassis_crumb_html()`
      (none are corporate-specific, per 1b's cross-PR note), and writing ecommerce-specific
      `chassis_head_ecommerce_html()`/`chassis_foot_ecommerce_html()` (cart icon + count badge
      instead of the corporate CTA) plus a new `chassis_product_html()`. Every page reuses the
      GALLERY's own `$css` vocabulary from `strip_ecommerce()`/`page_pdp()` (`.mini`/`.prods`/
      `.carousel`/`.bar`/`.bens`/`.acc`/`.pdp-gal`/`.pdp-buy`/`.opts`) rather than
      `ecommerce-mockup.html`'s own separate hand-written stylesheet, so the chassis renders styled
      with **zero new CSS** — `.ph`/`.page`/`.active` were already added unconditionally in PR 1b.
      Cart and checkout have no `strip_*`/`page_*` precedent (WooCommerce builds the functional
      versions; mockup-guide.md calls checkout "LAYOUT ONLY"), so both reuse the closing-band/
      lead-form vocabulary (`.band closing sober`, `.formwrap`, `.leadform`, `.field`,
      `.directlist`/`.dlabel`) `page_contact_enquiry()`-shaped pages already carry.
- [x] 1c.3 GREEN: fixture passes; full chain green. **size:exception** (see reason above).
      `php skills/framework-audit/assets/framework-audit.php` → 0 FAIL / 4 WARN / 0 JUDGE (the 4
      pre-existing word-budget WARNs only). Full chain: container-hygiene 81 + framework-audit
      684 (675 + 9 new) + audit-signals 22 + write-path 428 = **1215/1215, 0 FAIL**. `index.html`
      confirmed byte-identical past line 1, same total length (9,068,304 bytes), against the
      pre-PR-1c baseline. Diff stat: 2 files changed, 407 insertions(+), 2 deletions(-) — within
      the ~500-line `size:exception` budget.
- [x] 1d.1 RED: fixture — generator present, chassis output absent → assert `RT_CHASSIS_NOT_BUILT`
      FAILs (no such row exists yet). **Verified genuinely red**, not already-passing: stashed the
      PR's own `framework-audit.php` edit back to HEAD, re-ran the new r231-r234 fixtures against
      the unmodified real audit — the 3 assertions that read `RT_CHASSIS_NOT_BUILT`'s own text FAILed
      as expected (row does not exist yet); the row-absence assertions stayed green in both states,
      same shape as `RT_GALLERY_NOT_BUILT`'s own r225. Then un-stashed and confirmed GREEN.
- [x] 1d.2 Implemented `RT_CHASSIS_NOT_BUILT` mirroring `RT_GALLERY_NOT_BUILT`
      (`framework-audit.php:2604-2615`), gated on the generator's presence, checked per site type
      (`chassis/corporate.html` / `chassis/ecommerce.html`) so an interrupted build that wrote one
      and not the other still FAILs, naming only the missing one. Documented in `CONTRIBUTING.md`
      (`RT_ROWTYPE_UNDOCUMENTED`, `:89`) and added to the `ROW_TYPES` registry.
- [x] 1d.3 Extended `$anchored_required` (`:2076-2079`) to `chassis/corporate.html` and
      `chassis/ecommerce.html` (now 4 entries; PR 1f prunes back to 2 once the hand-maintained pair
      is deleted). **Empirically verified this alone regressed the real audit to 2 FAIL**
      (`RT_MOCKUP_ANCHOR_UNDECLARED` on both chassis files — the generated `:root` never declared
      `Anchor: PERS-*`, unlike the hand-authored files) before implementing the fix: `_build-gallery.php`
      now stamps `/* Anchor: PERS-INSTITUTIONAL */` into the chassis-only HTML string (never into
      `$css`/`$chassis_css`, so `index.html` cannot gain one byte) — truthful to what `:root` already
      is (`$root_anchor = $ANCHORS['institutional']` unconditionally for both site types, unchanged
      since PR 1a), not a resolved-per-site-type claim. Confirmed `RT_GALLERY_STALE` covers the
      chassis via the existing input digest **without new code**: `nm_gallery_input_manifest()`
      already hashes `_build-gallery.php`'s own source, and both artifacts are written by the same
      single invocation, so index.html can never read fresh while the chassis it was regenerated
      alongside reads stale — there is no code path where the two diverge.
- [x] 1d.4 Verified every `RT_MOCKUP_*` row against generated chassis output: NO_AXES,
      ANCHOR_UNDECLARED, AXES_MISMATCH (label-only, pre-1e) all confirmed silent by the real-repo
      0 FAIL/4 WARN run plus PR 1b/1c's r140/r141/r144 fixtures; DISCLOSURE_STATE locked by PR
      1b/1c's r142/r143/r145/r146; GRID_AUTOFILL, FONT_NOT_EMBEDDED, BLEED_FIXED_BAND,
      BLEED_NOT_MEDIA all already applied to the chassis via the glob-based walk since PR 1a/1b/1c
      and stayed silent throughout — this PR added no new markup, so none newly apply.
- [x] 1d.5 GREEN: r231-r234 fixtures pass; full chain green (see apply-progress for the verbatim
      run). `CONTRIBUTING.md`'s `RT_MOCKUP_ANCHOR_UNDECLARED`/`RT_MOCKUP_AXES_MISMATCH` rows'
      file-count prose updated from four/four to six/six to match the extended list — prose only,
      the AXES_MISMATCH mechanism itself (1e's job) is untouched.
> ### CARRIED FORWARD FROM PR 1d — a Slice 4 exit criterion, not a footnote
>
> PR 1d stamps `Anchor: PERS-INSTITUTIONAL` into BOTH generated chassis, because that is what
> their `:root` numerically is today (`--type-ratio:1.200` / `--fs-h1-max:48` / `--sp-scale:1.0`
> = `contained` + `standard`, PERS-INSTITUTIONAL's own axis line). The marker is truthful and
> the row that demanded it is right to demand it.
>
> **But a hardcoded PERS-INSTITUTIONAL chassis IS the defect this entire change exists to kill.**
> `mockup-guide.md:436-447`: *"every corporate project shipped `PERS-INSTITUTIONAL` and every
> commerce one `PERS-MATTER`, not because anyone chose them but because nobody was asked to...
> it was the tamest corner of the system, and every client site started there."*
>
> This is acceptable ONLY as an intermediate state. **Slice 4 does not close until the chassis
> anchor is resolved from the selected `STY-*`, not hardcoded**, and Slice 5's manifest writer
> persists that choice. If the change ships with the marker still pinned to one anchor, it has
> reproduced attempt 2's failure with new nouns — which is the one outcome the proposal names
> as disqualifying.

- [x] 1e.1 RED: fixture — label `scale: contained` beside `--fs-h1-max: 53` (wrong value) → confirm
      `RT_MOCKUP_AXES_MISMATCH` does NOT fire today (the gap). **Verified genuinely red**: r235
      (label agrees, `--fs-h1-max: 53`) run against the unmodified audit (`git stash` of just
      `framework-audit.php`, test fixtures kept) FAILed its 3 own assertions exactly as expected —
      `<0 rows matched, expected exactly 1>` — then `git stash pop` restored the implementation and
      the same fixture went GREEN.
- [x] 1e.2 Implemented `axis_token_props()`/`axis_token_values()` over `axis_rows_for()`
      (`framework-audit.php:1415`) and `root_token_value()` for the `:root` side; rewrote
      `RT_MOCKUP_AXES_MISMATCH` (`:2189-2219` after the new functions land at `:1483-1545`) to
      compare, per axis whose LABEL already agrees with the anchor, one representative token's
      VALUE against `design-system.md`'s own row for that position: scale's `--fs-h1-max` (the
      exact token design.md's own "Value check" paragraph names), ground's `--c-bg`, density's
      `--sp-scale`, elevation's `--elev-rest`. `--elev-hover` and scale's other two columns
      (`--type-ratio`/`--display-lh`) are read for correct column alignment but not independently
      compared — one token per axis is enough to catch a mis-typed position and keeps the checked
      surface to exactly what `fx_mockup()`'s real-value fixtures and the real repo's six
      Anchor-declaring files actually exercise; `--elev-hover` specifically rides on the same label
      as `--elev-rest` and is not independently re-pointed. A label mismatch (already FAILing)
      short-circuits the value check for that axis, so one `$mockup_wrong` entry stays per axis and
      "N of 5 axes disagree" keeps counting axes, not tokens.
      **Cross-cutting fixture fix, discovered mid-implementation, not pre-declared in this task**:
      `tests/test-framework-audit.php`'s shared `fx_ds_conforming()` (the base `design-system.md`
      EVERY `fx_base()`-rooted scenario in the suite uses) previously emitted a neutral placeholder
      value (`1`) for every axis position, correct for `RT_AXIS_VALUE_MISSING`'s "does some value
      exist" check but wrong for a VALUE-equality check — it made the new value check FAIL against
      the framework's OWN passing fixtures (`fx_mockup()`'s real production numbers never equalled
      the placeholder `1`), an 18-assertion regression across dozens of scenarios. Fixed by
      transcribing `design-system.md`'s real per-position values into `fx_ds_conforming()` (column
      shape matching `axis_token_props()`), which is the same trap this fixture's own comment
      already named once for `RT_AXIS_VALUE_MISSING`. Not a design gap — a necessary consequence of
      making the value check real anywhere it runs, including fixtures.
- [x] 1e.3 GREEN: r235 (mismatch) FAILs, naming `--fs-h1-max` `53` against `contained`'s `48`;
      r236 (label-and-value-agree, plain `fx_mockup()`) stays silent, exit 0; full chain green (see
      apply-progress for the verbatim run). Real-repo audit unchanged at 0 FAIL/4 WARN — all six
      `Anchor:`-declaring files (`corporate-mockup.html`, `ecommerce-mockup.html`, both generated
      chassis, both proof mockups) verified by hand against `design-system.md`'s real table before
      implementing, and confirmed silent by the real audit run after: no pre-existing label/value
      disagreement found in any shipped asset.
- [x] 1f.1 Confirmed full chain green with the generated chassis in place, BEFORE deleting anything:
      real-repo audit 0 FAIL/4 WARN (identical to PR 1e's close-state); `test-container-hygiene` 81,
      `test-framework-audit` 704, `test-audit-signals` 22, `test-write-path` 428 — all 0 FAIL;
      `chassis/corporate.html` (631,610 bytes) and `chassis/ecommerce.html` (629,884 bytes) present
      on disk. Recorded before touching anything else, per Slice 1's own rollback constraint.
- [x] 1f.2 Deleted `html-mockup/assets/corporate-mockup.html` (1073 lines) and
      `ecommerce-mockup.html` (721 lines) via `git rm`. Ran the real audit immediately after,
      BEFORE any of 1f.3's edits, to see what a bare deletion does on its own: `$anchored_required`
      (unpruned at that point) produced no FAIL — it only gates files the glob DISCOVERS, and a
      deleted file can never be discovered, so those two entries silently went dead rather than
      demanding a missing anchor. The real signal was a different, genuine FAIL:
      `html-mockup: points at "assets/ecommerce-mockup.html", which does not exist` /
      `...corporate-mockup.html...` — `SKILL.md`'s own References section link-checks its targets,
      confirming 1f.3 is load-bearing, not cosmetic.
- [x] 1f.3 Updated `html-mockup/SKILL.md` (Execution Steps + References — "run the generator, never
      copy" replaces the two-file copy-and-re-point instruction) and `mockup-guide.md` ("Chassis and
      anchor" section rewritten: `chassis/*.html` are what a real project starts from now, generated
      never hand-copied; the two hand-maintained originals are gone). Also resolved the remaining 21
      references across the reference surface (`framework-audit.php`, `_embed-fonts.php`,
      `_fonts.md`, `_build-gallery.php`, `_novamira-framework.md`, `novamira-web-orchestrator.md`,
      `CONTRIBUTING.md`, `_axis-proof-content.md`) — each judged prose-to-rewrite vs.
      historical-narrative-to-preserve individually; see apply-progress for the per-file
      disposition. **Trap 2, `$anchored_required`, pruned from 4 entries back to 2**
      (`chassis/corporate.html`, `chassis/ecommerce.html`) in `framework-audit.php`, with its
      surrounding comment block and CONTRIBUTING.md's two rows updated from "six files" /
      "the four starting assets" language to match. **Necessary, disclosed test-fixture fix**: this
      pruning would have broken `tests/test-framework-audit.php`'s r132 scenario (the
      `RT_MOCKUP_ANCHOR_UNDECLARED` fixture pointed at `corporate-mockup.html`, one of the two
      pruned entries) — retargeted to `chassis/corporate.html`, the fixture's actual intent
      (a required starting asset without an anchor marker FAILs) unchanged. **Trap 1, the SKILL.md
      word ceiling**: iterated three times against `--word-report` — first rewrite landed at 628
      (over 600), trimmed to 588, trimmed again to **567** — a genuine 15-word reduction from the
      582-word baseline, replacing the six-step copy-and-re-point procedure with one command.
      `_build-gallery.php`'s own source changed (comment edits), which shifted its input digest and
      correctly FAILed `RT_GALLERY_STALE` until regenerated — `_build-gallery.php` was re-run,
      restoring `index.html` to byte-identical (past line 1) with the pre-PR baseline.
- [x] 1f.4 GREEN: repo-wide search (`corporate-mockup.html` / `ecommerce-mockup.html`) confirms zero
      live/operative references — the 14 files still matching are either deliberate historical
      narrative (tense-lightly-corrected, filename kept as the recorded lesson: `framework-audit.php`
      ×2, `CONTRIBUTING.md` ×1, `_build-gallery.php` ×4, `_embed-fonts.php` ×1, `_fonts.md` ×1,
      `mockup-guide.md` ×1 confirming the retirement itself), pure non-operative comments in files
      explicitly outside this PR's given reference surface (`proof-direct-mockup.html`,
      `proof-editorial-mockup.html`), synthetic test-fixture filenames unaffected by the code change
      (`tests/test-framework-audit.php`), or SDD planning/spec record (`proposal.md`, `tasks.md`
      itself, `specs/client-chassis-generation/spec.md` — whose own acceptance criteria REQUIRE the
      two files not exist, `docs/superpowers/*`). `skills/_novamira-framework.md`,
      `agents/novamira-web-orchestrator.md`, `skills/html-mockup/SKILL.md` and
      `_axis-proof-content.md` are now fully clean (zero hits). Full chain green:
      `test-container-hygiene` 81, `test-framework-audit` 704, `test-audit-signals` 22,
      `test-write-path` 428 — **1235 OK / 0 FAIL, identical total to the pre-PR baseline** (the r132
      fixture was retargeted, not removed, so no assertion was lost). Real-repo audit: 0 FAIL / 4
      WARN (html-mockup's WARN now reads 567 words, down from 582). `index.html` confirmed
      byte-identical past line 1 against the session-start baseline, 9,068,304 bytes, both before
      regeneration (unaffected by the deletion, since the two legacy files were never generator
      inputs) and after (comment-only generator source changes, `cmp` exit 0 on both tails).
      **size:exception** (see reason above) — diff: 10 prose/code files, 78 insertions(+) / 72
      deletions(-), plus the two deleted files' 1073 + 721 = 1794 removed lines; total 1944 changed
      lines, almost all deletion as declared.

## Slice 2 — One axis registry (`style-axes`)

Reconciling design's "one atomic commit" with the table's 2a/2b split: **2a is a pure,
behavior-preserving refactor** (still 5 axes — safe on its own). **2b is the atomic unit** —
widening to 8 axes without the 5 anchors' `**Axes:**` lines updated in the same commit FAILs every
anchor instantly via `RT_PERS_BAD_AXIS`, so 2b lands as one commit, not split further.

- [x] 2a.1 RED: captured `--emit-row-types` output baseline before refactor (72 lines, sha256
      `4731b2a7...8a0f9df`).
- [x] 2a.2 Introduced `nm_axes()` (5 existing axes only) consolidating the axis list. **Line
      numbers re-verified against the checkout (all four had shifted since design.md was
      written)**: `$PERS_AXES` was at `:1017-1023` (design cited `:1016-1022`), `axis_matches()`'s
      `$labels` was at `:1659` (design cited `:1590` — its function def is at `:1658`, after PR
      1e's `axis_token_props()`/`axis_token_values()`/`root_token_value()` landed at `:1493-1548`),
      `axis_signature_of_block()`'s `$props` was at `:1622` (design cited `:1553`), and the
      `RT_MOCKUP_AXES_MISMATCH` regex alternation was at `:2175`, inside the per-mockup-file loop
      (design cited `:2096`, which is now inside the unrelated `RT_MOCKUP_DISCLOSURE_STATE` run-
      grouping logic — that row's own code grew between design and apply). `nm_axes()` returns
      `axis => array('positions', 'prop'|null, 'marker')` exactly as design.md D2 specifies.
      **Explicitly NOT touched, in scope discipline**: `axis_token_props()` (`:1493-1501`, PR 1e's
      per-token-property map, a different shape — multiple props per axis — not one of the four
      named duplicates) and `axis_declarations()` (`:956-958`, a flat 5-property list for a
      different check, RT_MOCKUP_NO_AXES/RT_QA_NO_AXIS_CHECK). Order preserved exactly per
      consumer: `$PERS_AXES` and the regex alternation keep nm_axes()'s own order (scale, ground,
      density, composition, elevation); `axis_signature_of_block()`'s `$props` derives by filtering
      out the one marker axis (composition), landing on the same scale/ground/density/elevation
      order it always had; `axis_matches()`'s `$labels` needed an explicit reorder (composition
      LAST, not fourth) since its original hand-typed order differed from `$PERS_AXES`'s — kept
      byte-for-byte to avoid reordering axis names inside any existing FAIL message.
- [x] 2a.3 GREEN: `--emit-row-types` output byte-identical (same sha256, `cmp` exit 0 — this proof
      command is unaffected by construction, since `--emit-row-types` echoes the static `ROW_TYPES`
      registry and `exit()`s before `$root`/`$PERS_AXES` are ever touched; it verifies the file
      still parses and ROW_TYPES is untouched, not the axis logic itself, which the real-repo audit
      and full chain below verify instead). Real-repo audit: 0 FAIL / 4 WARN, the same four
      (elementor-core 588, html-mockup 567, web-templates 559, woocommerce 597). Full chain:
      `test-container-hygiene` 81 + `test-framework-audit` 704 + `test-audit-signals` 22 +
      `test-write-path` 428 = **1235 OK / 0 FAIL, identical totals to baseline** — a pure refactor
      adds no assertions. `index.html`: same 9,068,304-byte length and byte-identical past line 1
      against the session baseline (`cmp` exit 0 on both tails); line 1's fingerprint itself
      differs from that baseline for a pre-existing reason unrelated to this PR — it already
      reflects PR 1f's own `_build-gallery.php` comment-edit regeneration (mtime 12:44/12:48,
      before this PR's 13:12 edit), and this PR never touches `_build-gallery.php`, so the gallery
      output could not have changed again here. Diff: 1 file, 72 insertions(+) / 10 deletions(-),
      82 changed lines total — well within the ~180-line budget.
- [x] 2b.1 RED: `fx_pers()` (`tests/test-framework-audit.php:344`) called with undefined `ornament`
      → `RT_PERS_BAD_AXIS` FAILs (new scenario `$r92b`); boundary fixture at 2/8 shared passes, 3/8
      FAILs (new scenario `$r92c`). Both genuinely unreachable before `fx_pers()` took 8 params —
      confirmed by the same red/green discipline as prior PRs.
- [x] 2b.2 **ONE COMMIT** (session-boundary note: landed across two apply sessions, both
      uncommitted — no intermediate commit exists, so the atomicity constraint holds at the
      commit boundary): widened `nm_axes()` to 8 axes (`accent`, `chassis`, `ornament` as marker
      axes) + all 5 anchor blocks' `**Axes:**` lines + threshold `>1`→`>2` (`:1169`, line shifted
      from the design-time `:1103` estimate) + renamed `RT_PERS_TOO_SIMILAR`→`RT_STYLE_TOO_SIMILAR`
      + widened `fx_pers()` to 8 params (3 new optional, default `null`, omitting that axis — same
      convention `fx_proof()` already used).
      **Two resolved blockers, applied this session** (both were genuine, both stopped a prior
      session correctly — see apply-progress for the full resolution):
      - Blocker A (`RT_AXIS_VALUE_MISSING`'s value model): the three new axes' positions are
        backticked SCREAMING-KEBAB blueprint ids (`ACC-*`, `CHS-*`, `ORN-*`), the SAME value shape
        `axis_value_kind()` already recognized for composition's `LP-*` — zero change to that
        function. `RT_AXIS_BLUEPRINT_MISSING`'s single hardcoded `layout-patterns.md` lookup
        (`:1656-1659` at design time) generalized to a per-axis lookup table (`$axis_bp_lookup`):
        composition/chassis resolve against `layout-patterns.md` (layout concerns), accent/ornament
        against `design-system.md` itself (colour/surface concerns, beside § Ground/§ Elevation).
        Real definitions written: `design-system.md` gained `### Chassis`/`### Accent`/`### Ornament`
        value tables plus `#### ACC-*`/`#### ORN-*` blueprint headings (prose in the existing
        "what is fixed, not adjectives" voice); `layout-patterns.md` gained `## Chassis blueprints`
        with all 8 `### CHS-*` headings. `none` positions (accent, ornament) are literal `none`
        cells, not blueprints — matching elevation's own existing `none` row.
      - Blocker B (the byte-identical requirement): retired for Slice 2 per this session's explicit
        instruction — Slice 2 changes the design system on purpose, so `index.html` MUST change.
        `_build-gallery.php`'s `$ANCHORS` array (the hand-maintained mirror its own `:280` comment
        names) gained `accent`/`chassis`/`ornament` per anchor; `anchor_block()` and the document
        `:root` builder both emit the 3 new `/* axis: position */` markers (bare position names,
        not blueprint ids — composition's `LP-` prefix stays the one exception). Generator re-run;
        `index.html`/`chassis/*.html` regenerated (gitignored, not part of this diff). The two hand
        proof mockups got the same 3 markers added directly (their real per-anchor values).
- [x] 2b.3 Re-baselined in the same session: `RT_GALLERY_NOT_DISTINCT`, `RT_PROOF_NOT_DISTINCT`,
      `RT_STYLE_TOO_SIMILAR`, `RT_MOCKUP_AXES_MISMATCH`, `RT_AXIS_VALUE_MISSING` all updated —
      "of 5"→"of 8" arithmetic throughout; `fx_pers()`/`fx_proof()`/`fx_gallery()`/`fx_mockup()` call
      sites widened (accent/chassis/ornament values chosen to preserve each scenario's original
      pass/FAIL intent under the new `>2`-of-8 threshold, documented per-scenario where a value was
      forced to collide on purpose); `fx_ds_conforming()`/`$FX_AXIS_POSITIONS` widened (new axes
      fall through to the same neutral-literal-`1` convention composition already used, since
      `axis_token_props()` has no entry for a marker axis and RT_MOCKUP_AXES_MISMATCH's value check
      is a no-op for one); 3 manual design-system.md-table fixtures (`RT_AXIS_VALUE_MISSING`'s
      positive control and its "two rows" scenario) gained the 17 net-new unique position rows
      (36 unique names total, 3 free via elevation/composition sharing `none`/`strict-grid`); the
      "20 pairs" assertion re-baselined to 40 (`4+4+4+4+4+7+8+5`).
- [x] 2b.4 GREEN: `$r92c`'s 2/8 fixture passes, 3/8 FAILs, confirmed. Full chain: 81 + 711 + 22 +
      428 = **1242 OK / 0 FAIL** (was 1235 at the PR 2a baseline; +7 net new assertions, all from
      the 2b.1 RED/boundary scenarios — see apply-progress for the itemised count). Real-repo audit:
      **0 FAIL / 4 WARN**, the same 4 pre-existing word-budget WARNs.
- [x] 2b.5 (No verifier — code review only, confirmed) `RT_GALLERY_NOT_DISTINCT` and
      `RT_PROOF_NOT_DISTINCT` literally share `axis_matches()` (unchanged by this PR beyond the
      per-axis label fix already landed in 2a/2b.2). `RT_STYLE_TOO_SIMILAR` does NOT call
      `axis_matches()` — it never did, pre-dating this PR — because its input shape differs
      (design-personalities.md's parsed **Axes:** text, not a compiled CSS `:root`/`[data-anchor]`
      signature); it has always carried its own inline comparator over `$axes_of`. Verified this is
      not a fork PR 2b introduced: the SAME threshold change (`>1`→`>2`) was applied to both
      mechanisms in the same commit, preserving the pre-existing "one bar, two comparators for two
      input shapes" relationship rather than letting them drift.

## Slice 3 — Colour and photographic tone (`colour-and-tone-system`)

- [x] 3a.1 **RED (free)**: extend `tests/test-write-path.php` ground whitelist (`:2327`) from
      `paper/warm/cool/ink` to 9 names, and `4 === count($suelos)` (`:2339`) to `9 ===` — FAILs
      immediately against the still-4-row `design-system.md`.
      **Verified genuinely red**: ran immediately after the edit, before touching design-system.md
      or `_build-gallery.php` — `FAIL las nueve posiciones de ground se leen de design-system.md:
      paper, warm, cool, ink` (only 4 keys parsed), 441 OK / 1 FAIL, isolated to this one assertion.
      `ink` kept its name rather than becoming `ink-neutral`: `framework-audit.php`'s `nm_axes()`
      (`ground` positions, `:1044-1048`) and all five anchors' `**Axes:**` lines in
      design-personalities.md already declare `ink`, and renaming it with those two files untouched
      would desync `RT_MOCKUP_AXES_MISMATCH`'s label against the emitted marker for zero benefit —
      that rename is Slice 4's job, not this PR's. Final 9: `paper/warm/cool/cream/earth/saturated/
      ink/ink-warm/ink-cool`.
- [x] 3a.2 RED: extend the AAA 7:1 loop (`:671-686` in the current checkout — design cited `:654-669`,
      shifted by PR 1e/2a/2b; today iterates `$BRANDS` only — verified gap: the 4 tabled house
      grounds are never body-contrast-gated) to also run over `$GROUND`'s base positions.
      Implemented as a dedicated loop right after `$GROUND`'s own literal, same `fail()` message
      shape as the brand loop. **Empirically confirmed the gap first**: the unmodified generator
      ran clean with no complaint about the 4 tabled grounds' contrast before this loop existed.
- [x] 3a.3 RED: add the `$GROUND` drift assertion (in scope per Engram #353) comparing
      `_build-gallery.php`'s `$GROUND` literal (`:238-243` at design time, unchanged in the current
      checkout) against `design-system.md`'s parsed ground table (`:310-316`), via regex extraction
      of the literal array (no `require`, since the generator has file-write side effects).
      **RED/GREEN proved by deliberate mutation, per the Definition of Done**: changed
      `ink-warm`'s documented `--c-text` from `#F7EFE2` to `#F7EFE9` in design-system.md alone,
      re-ran `test-write-path.php` → `FAIL $GROUND['ink-warm']['text'] coincide con
      design-system.md: #F7EFE2` (491 OK / 1 FAIL), then restored → 492 OK / 0 FAIL.
- [x] 3a.4 Added 5 ground rows to `design-system.md` (Ground table `:310-318` + Derived-token table
      widened from 4 to 9 position-columns, `:356-361`); recomputed every derived ratio per family:
      body ≥4.5:1, inverse surface ≥3:1, hairline 1.05–2.5:1, ground 7:1 AAA. Fixed stale "four
      rows"/"all four positions" prose in the same section (now "nine"). **Deviation, disclosed**:
      `earth` could not ship as a genuinely medium-luminance ground — proved geometrically (a
      `--c-bg` luminance of 0.20 tops out at 4.75:1/5.03:1 against pure black/white text, both
      under the 7:1 AAA floor), so `earth` sits near-white like the others, distinguished by a
      deeper, more saturated ochre hue rather than by lightness. `earth` and `saturated` also
      needed a SUBTLER `--c-bg-alt` step than the original 4 families: at the first-chosen alt
      values, `--c-text-muted` (blended toward bg-alt, the harder bar this file's own Spanish
      paragraph at `:378-387` argues for) measured 4.08:1/4.33:1 — under 4.5. Found by literally
      recomputing the doc's own "against bg-alt" convention, not just the runtime gate (which
      measures against bg and would have passed silently); re-picked both families' `bg-alt` closer
      to `bg` until the harder bar cleared too (earth 4.53:1, saturated 4.59:1), keeping the
      file's own stated invariant — "a muted that passes over bg-alt passes over bg too" — true for
      all 9 families, not just the original 4.
- [x] 3a.5 Widened `$GROUND` and `$ACCENT_BY_GROUND` to 9 families in `_build-gallery.php`; each
      accent independently clears 4.5:1 on bg and bg-alt (`:695-706` in the current checkout).
      **Went beyond the bare task text on explicit instruction** (non-negotiable per the session
      brief): `paper`/`warm`/`cool` no longer share one literal (`#8C3A1F` on 3 of 4 grounds was
      named as half the defect this PR exists to remove) — `paper` keeps it, `warm`/`cool` get
      independently-measured hexes. Stale prose making the old "one hue family for the whole
      gallery" claim fixed at 3 sites (`:456-462`, `:783-786` current line numbers, the closing-field
      comment at `:6667-6670`) since it became false.
      **Two genuine cross-PR couplings found and resolved, both real, neither touching PR 3b's ink
      mechanism**: `$ACCENT_BY_GROUND[$ground]` is `ink_ends()`'s THIRD parameter for the two
      anchors that own `warm`/`cool` (`matter`/`institutional`), so changing the accent changes
      those anchors' derived shadow ink as an INPUT effect, not a mechanism change.
      (1) The channel-spread ≥20 gate (`:938-952`, PR 3b's own gate, unmodified): a first candidate
      teal-green for `warm` (`#1F5C4D`) measured spread 17 — FAIL. Searched and replaced with
      `#0F5C1A` (forest green, spread 26).
      (2) The swatch-separation ≥10 gate (`:9492-9510`, unmodified, pixel-based — real photographs
      of `sq-marmol`/`sq-pizarra`): EVERY blue/violet candidate tried for `cool` (7 measured,
      `#1B4F7A`..`#2B6CA3`, spread 2.6–4.8) passed the other two gates but collapsed this one under
      the 10.0 bar — a hue-family effect, not a shade-tuning one. Resolved empirically (this gate
      has no offline formula — it reads real image pixels through `ink_curve()`/`ink_mean()`) by
      running the actual generator against candidate hexes directly: crimson (`#8C1A28`) is the
      hue family that clears all three gates at once (8.37:1/7.78:1 eyebrow, spread 38, swatch gap
      clears with room). Documented inline at the array entry, house idiom (LUMIÈRE/TUESTE NORTE
      pattern).
      5 new positions each get an independently-measured, non-repeating accent: `cream` `#3F4E1A`
      olive, `earth` `#3A2560` indigo, `saturated` `#8A2450` magenta-plum, `ink-warm` `#E8B93A`
      amber-gold, `ink-cool` `#4FA8E0` sky blue.
- [x] 3a.6 GREEN: whitelist/count/AAA-loop/drift assertions all pass at 9; full chain green (see
      Work Unit Evidence below).
- [x] 3b.1 RED: fixture — style A `$INK_TINT=0.30`, style B `0.60` → differing hues, both within
      `ink_quant_bound()` of `ink_ends()`'s convergence assertion. **Line numbers re-verified against
      the current checkout** (design's `:895` estimate had shifted): the convergence assertion is at
      `ink_ends()` `:966-976` (function itself `:949`). **Two things were genuinely RED**, not one:
      (a) plumbing — before 3b.4, `_build-gallery.php` had no `$INK_TINT_BY_STYLE` and both call
      sites passed the one shared `$INK_TINT`; proved red via 3 new structural assertions in
      `tests/test-write-path.php` (existence + both call sites read a per-style value), confirmed
      FAIL against a stashed pre-3b.4 generator, confirmed OK after. (b) behaviour — a genuine
      RED→GREEN pair extracting `ink_ends()`/`ink_tint()`/`ink_quant_bound()` etc. from the real
      `_build-gallery.php` source (balanced-brace extraction, no re-typed formula) and running them
      in an isolated child process (`exec()`, same isolation the existing "no se muere en silencio"
      fixture already uses, since `fail()` calls `exit(1)`). Real production values: `matter`
      (ground `warm`, accent `#0F5C1A`) at tint 0.30 → dark ink `#202E19` (spread 21); `institutional`
      (ground `cool`, accent `#8C1A28`) at tint 0.60 → dark ink `#481821` (spread 48) — genuinely
      different hues, both converged, both clear the 20-floor.
- [x] 3b.2 RED: fixture — style declares ink position `none` → no `filter:url()` emitted; convergence,
      spread, endpoint-collision never evaluated for it. **Line numbers re-verified**: the CSS
      emission loops are at `:9458-9481` (anchors) and `:9485-9504` (brands) in the current checkout
      (design's `:9342` estimate had shifted). Fixture calls the real `ink_of()` (extracted, same
      technique as 3b.1) with a grade of the literal string `'none'`: `ends` returns `NULL` (not an
      array — `ink_ends()` genuinely never runs), `sat` is the feColorMatrix identity (`'1'`), the
      table is the feComponentTransfer identity (`0 0.25 0.5 0.75 1`). Confirmed genuinely RED before
      3b.4 (all 4 assertions FAIL against the pre-implementation generator).
- [x] 3b.3 RED: fixture — channel spread of 14 (< the 20 floor) → ink gate FAILs. Real combination
      found by sweeping the accent hex space against `paper`: `#20203A` at the house default tint
      0.45 → dark ink `#242532`, spread exactly 14. Same extraction/subprocess technique; asserts
      exit code 1 and the exact `fail()` message naming "channel spread of 14". This scenario was
      ALREADY green pre-3b.4 (the spread gate itself is not new) — its value is proving `none` did
      not soften it, per 3b.4's own constraint.
- [x] 3b.4 Implemented `$INK_TINT_BY_STYLE` (shaped like `$INK_GRADE`), read per-style at the two
      call sites: the anchor loop (now `ink_of( $ink_ak, $ANCHORS, $GROUND, $ACCENT_BY_GROUND,
      $INK_GRADE, $ink_tint_v )`, current-checkout line `:1122-1124`) and the brand loop (now
      `ink_ends( $GROUND[$ink_bg], $ACCENT_BY_GROUND[$ink_bg], $ink_tint_v )`, `:1130-1141`) — both
      resolve `isset(...) ? ... : $INK_TINT_BY_STYLE['default']`, the same optional-override shape
      `$ink_bv['ink']` already had. Brands additionally gained an `'ink_tint'` override key, mirroring
      the existing `'ink'` grade override — no brand uses it yet, but the call site now supports one
      without a further edit. `none` implemented as a short-circuit inside `ink_of()` BEFORE
      `ink_ends()` is called: `ends => null`, `sat => '1'`, identity table, `gamma => 0`. **Zero new
      gate exemptions, verified per-gate**: convergence/weight/spread/collision — never reached, not
      exempted (ink_ends() not called). Split-tone ratio (`:1143-1154`->now shifted, see GREEN below)
      — passes through its OWN pre-existing zero-deviation `continue` (`$ink_ends_dev < 1e-9`), no
      new branch added. Swatch-separation (`ink_pixel()`/`ink_mean()`) — reads `sat`/`table` only,
      never `ends`, so it runs on the RAW photo mean and passes wide; not touched. The only new code
      for `none` outside `ink_of()` is a `null === $ink_o['ends']` skip in the two CSS-emission loops
      (`:9462-9464`, `:9490-9491` current lines) — output generation, not a gate.
- [x] 3b.5 Derived `soft-shadow`'s `--elev-rest`/`--elev-hover` via `color-mix(in srgb,var(--c-text)
      N%,transparent)` (4% / 16%, the exact percentages the old `rgba(0,0,0,.04)` /
      `rgba(21,24,26,.16)` literal used), same syntax `accent-glow` already established one row down.
      Replaced in both `_build-gallery.php`'s `$ELEVATION['soft-shadow']` (`:286-297` current lines)
      and `design-system.md`'s Elevation table (`:559-573` current lines, `:543` in the design-time
      estimate had shifted after PR 3a's own table growth) plus a new explanatory paragraph. Fixed a
      now-stale Spanish comment on `vitrine`'s card CSS (`:8657-8663` current lines) that had asserted
      "black on black, invisible by construction" for `soft-shadow` on the `ink` ground — no longer
      literally true once the colour follows `--c-text` instead of a fixed black. **Confirmed light
      and dark grounds diverge, real values**: `institutional` (ground `cool`, `--c-text #141C24`) →
      `color-mix(in srgb,#141C24 4%,transparent)` ≈ `rgba(20,28,36,.04)`, a genuine dark shadow;
      `vitrine` (ground `ink`, `--c-text #F4F6F7`) → `color-mix(in srgb,#F4F6F7 4%,transparent)` ≈
      `rgba(244,246,247,.04)`, a pale glow, not a shadow — the RGB channels flip from near-black to
      near-white between the two real anchors that both carry `elevation: soft-shadow` today.
- [x] 3b.6 GREEN: all ink fixtures pass (22 new assertions: 8 function-existence + 3 plumbing + 4
      (3b.1) + 4 (3b.2) + 3 (3b.3)); full chain green — see apply-progress for the verbatim run,
      including the real generator run's own measured swatch-separation numbers for `matter`/
      `institutional` at their new tints (25.1 / 16.5, bar 10.0).
- [ ] 3b.7 **DEFERRED to the orchestrator**, not attempted this session. Cross-cutting (proposal
      Success Criteria, not a spec scenario): render one archetype under 4 styles from different
      catalog groups via `visual-verification`, and the same photograph under 4 styles — histograms
      must measurably differ. Deferred per explicit session instruction: it is a visual-verification
      sweep the orchestrator runs separately, and it depends on Slice 4's catalog (8 `STY-*` entries)
      existing, which is explicitly out of scope for PR 3b.

## Slice 4 — The style catalog (`style-catalog`)

- [x] 4a.1 RED: fixture — style names `Canela Deck` (absent from `nm_font_registry()`) →
      `RT_MOCKUP_FONT_NOT_EMBEDDED` FAILs. **"Value, not novelty" RED, same discipline as 3b.1/3b.3**:
      `RT_MOCKUP_FONT_NOT_EMBEDDED` (`framework-audit.php:2538-2596`) is a generic per-mockup-file
      font-stack-vs-`@font-face` check that needs no per-family knowlege, so it already catches an
      out-of-registry family before any 4a code lands — the mechanism is pre-existing (r106 already
      proves the shape with `Fraunces`). This scenario's value is locking the SPECIFIC example
      `specs/style-catalog/spec.md`'s "Unembedded family named" scenario names (`Canela Deck`) into
      a real assertion. Added two scenarios to `tests/test-framework-audit.php` (after r112, before
      the "style-catalog PR 1b" section): `Canela Deck` named with no `@font-face` → FAILs, names
      the family; `Archivo`/`Archivo Expanded` reused at different weight/stretch (the spec's other
      scenario, "Reused embedded family") → does not FAIL. Both pass immediately (6 new assertions,
      711→717) — confirmed by direct run, not assumed.
- [x] 4a.2 Created `ux-design-system/references/style-catalog/` (`_README.md`, `_backlog.md`); ported
      the 5 existing anchors as `STY-*.md` (`STY-EDITORIAL`, `STY-DIRECT`, `STY-MATTER`,
      `STY-VITRINE`, `STY-INSTITUTIONAL`), each declaring all 8 axis positions (verbatim from
      `design-personalities.md`, format only — no position altered) + a new "Toggle precharge"
      section (2–5 toggles each, drawn from `toggles.md`'s cross-template shared list since `STY-*`
      is orthogonal to `TPL-*`, values reasoned from each anchor's own ported Fits/Motion/Imagery/
      Card-recipe prose, not arbitrary — genuinely different across styles, not a repeated default).
      `STY-VITRINE.md` keeps its ported prose in Spanish (source anchor is Spanish) and its own
      toggle table is Spanish to match, per the language contract. `_README.md` states which of the
      8 v1 entries are ported here vs. still missing (PR 4b's 3 new entries), and carries forward
      the CARRIED-FORWARD note from PR 1d verbatim in substance: both generated chassis are stamped
      `PERS-INSTITUTIONAL` hardcoded, and porting the anchor to `STY-INSTITUTIONAL` does not resolve
      that hardcode — Slice 4 does not close until it reads from the selected style. `_backlog.md`
      lists all 8 named deferred movements (Kinetic, Cyberpunk, Y2K, Retro, Playful, Feminine,
      Editorial Fashion, Experimental) each with a concrete reason grounded in what the repo
      actually lacks (motion.md's one documented curve, the ornament axis's 5 fixed positions, the
      one-accent-colour rule, `RT_PERS_BAD_AXIS`'s refusal of an undeclared position) — not a wish
      list. `RT_ORPHAN_FILE` kept silent WITHOUT touching `SKILL.md`'s word budget: a directory
      pointer (`references/style-catalog/`) was added to `design-personalities.md`'s intro prose
      instead (already reachable from `SKILL.md`, and unlike `SKILL.md` carries no word-count gate),
      closing transitively over all 7 new files per one `points_at_dir()` match. **Reversed one
      approach mid-task**: a first attempt added the pointer directly to `SKILL.md`'s own References
      section, which pushed its word count from 498 to 524 (past the ~500 `RT_BODY_OVER_500` WARN
      threshold) — a genuine new WARN, caught by re-running `--word-report` before moving on, not
      assumed safe. Reverted, re-routed through `design-personalities.md` instead.
- [x] 4a.3 GREEN: font fixtures behave as specified (both pass); full chain green. Real-repo audit:
      **0 FAIL / 4 WARN** (unchanged from baseline — elementor-core 588, html-mockup 567,
      web-templates 559, woocommerce 597; `ux-design-system` confirmed still 498 words, unchanged).
      Full chain: `test-container-hygiene` 81 + `test-framework-audit` 717 + `test-audit-signals` 22
      + `test-write-path` 514 = **1334 OK / 0 FAIL** (was 1328 at PR 3b baseline, +6 net new — the
      two 4a.1 scenarios' assertions). `php -l` clean. Diff: 9 files (2 modified, 7 new),
      331 insertions(+) / 0 deletions(-) — 31 over the ~300 estimate, not flagged `size:exception`
      in the Work Units table (budget "OK", well under the 400-line hard threshold), reported per
      the session's explicit instruction to state any overshoot and why: catalog-authoring prose
      (5 `STY-*.md` + `_README.md` + `_backlog.md`) genuinely needed slightly more room than the
      estimate to state each style's toggle precharge with a real (not decorative) rationale.
- [x] 4b.1 RED: two new `fx_mockup()` scenarios (r112d/r112e) lock PR 4b's OWN concrete font
      decisions, not a second copy of 4a's examples: `STY-TECH-SAAS` reuses `Inter Tight` as a
      PRIMARY for the first time (every prior use was secondary) paired with `Source Sans 3`;
      `STY-NEO-BRUTALIST` reuses `Archivo` (not `Archivo Expanded`) as a primary for the first time
      paired with `DM Sans`. Both confirmed NOT to FAIL. A third scenario (r112f) names `GT
      Walsheim` — a second absent family, distinct from 4a's `Canela Deck` — and confirms it FAILs
      the same way, proving the mechanism is not accidentally special-cased to one string.
      "Value, not novelty" RED, same discipline as 4a.1: `RT_MOCKUP_FONT_NOT_EMBEDDED` needs no new
      code.
- [x] 4b.2 RED: `RT_STYLE_TOO_SIMILAR` AT CATALOG SCALE (r92d), not just the 3-entry boundary r92c
      already proved — an 8-entry synthetic fixture (28 pairs) with a deliberately corrupted last
      entry sharing exactly 4/8 with the third (`scale`/`ground`/`density`/`composition` all copy
      `PERS-MATTER`) FAILs, naming "share 4"; all other 27 pairs confirmed silent by count, not
      just by the one assertion that matters. **Disclosed why this fixture is synthetic, not the
      real 8 `STY-*.md` files**: `RT_STYLE_TOO_SIMILAR` still parses `design-personalities.md`
      (unchanged since PR 4a), and `nm_axes()`'s ground positions are still stuck at the original 4
      (`paper`/`warm`/`cool`/`ink`) — using `ink-cool`/`ink-warm`/`saturated` in a fixture would
      trip `RT_PERS_BAD_AXIS` and silently exclude the anchor from comparison. See `_README.md`
      "Known gap" for the full disclosure and why it does not block this PR.
- [x] 4b.3 Authored `STY-TECH-SAAS`, `STY-DARK-LUXURY`, `STY-NEO-BRUTALIST` (8 total). Chosen from
      the proposal's remaining candidates to maximise separation from the 5 ported entries under
      the 8-axis gate; each clears ≤2/8 shared against every OTHER of the 7 entries (all 28 pairs,
      re-verified by hand, table in `_README.md`). Each spends one of PR 3a's 5 previously-unused
      ground families (`ink-cool`, `ink-warm`, `saturated` — `cream`/`earth` stay unclaimed) and
      reuses one of the 7 embedded faces in a role it has not carried before in this catalog —
      **no new SIL OFL face embedded**, decided explicitly and disclosed: verified provenance
      (source URL, sha256, copyright — `_fonts.php`'s own header requirement) needs a network fetch
      this apply session does not have, so reuse was the only path this session could execute
      honestly, not a stylistic default. Each entry declares all 8 axes, Fits/Typography/Motion/
      Imagery/Card-recipe prose, and a 5-row toggle precharge table, the same shape PR 4a's ports
      established.
- [x] 4b.4 GREEN: real-repo audit 0 FAIL/4 WARN (same 4 pre-existing word-budget WARNs); full chain
      1350 OK/0 FAIL (was 1334 at PR 4a, +16 net new — the 4b.1/4b.2 fixtures' own assertions).
      `RT_STYLE_TOO_SIMILAR` mechanism confirmed clean at n=8 scale via a GREEN companion fixture
      (r92e, same 8-entry shape as r92d with only the corrupted entry corrected): all 28 pairs
      silent. The 8 real `STY-*.md` entries' own 28-pair table (`_README.md`) is the authoritative
      claim for the real catalog — maximum shared across all 28 pairs is 2, several pairs at the
      2/8 boundary, none over. `php -l` clean on `tests/test-framework-audit.php`. Diff: 2 files
      modified (`_README.md` +137/-10, `tests/test-framework-audit.php` +156/-0) + 3 new `STY-*.md`
      files (126 lines) = 419 changed lines, 139 over the ~280 estimate, NOT `size:exception`
      (reported per instruction) — the mandatory 28-pair table plus the font-decision rationale
      table in `_README.md`, and two full RED/GREEN fixture pairs at real n=8 scale (28 pairs each,
      not a toy 2-3 entry test), needed the room.
- [x] 4c.1 Confirmed full chain green with the catalog complete BEFORE deleting
      `design-personalities.md` (1350 OK/0 FAIL, PR 4b baseline, file still present). TASK ZERO
      done first, in the same commit-ordering discipline as PR 2a/2b: `nm_axes()`'s `ground`
      positions widened from 4 (`paper`/`warm`/`cool`/`ink`) to the real 9 `design-system.md`'s own
      table carries (adds `cream`/`earth`/`saturated`/`ink-warm`/`ink-cool`), verified against
      `design-system.md:304-333` directly rather than trusted from PR 4b's own disclosure — widening
      alone re-verified 0 FAIL/4 WARN before any parser repoint landed, so `RT_PERS_BAD_AXIS` never
      saw a style name a ground position the registry did not yet define.
- [x] 4c.2 Repointed every `design-personalities.md` reader found (RT_PERS_CATALOG_MISSING/
      MISSING_FIELD/DUPLICATE_ID/BAD_AXIS, `pers_axes()`, RT_STYLE_TOO_SIMILAR, RT_CATALOG_UNMENTIONED,
      RT_MOCKUP_AXES_MISMATCH's Anchor-marker regex, RT_TOKENS_HARDCODED_FONT's message,
      `_build-gallery.php`'s startup existence-assertion and every citation comment,
      `_gallery-fingerprint.php`'s exclusion-list comment) at `references/style-catalog/STY-*.md`,
      glob-matched, id read generically (no hardcoded `PERS-`/`STY-` prefix — catalog membership is
      the glob's job). Found and fixed a genuine latent bug the repoint exposed: `pers_axes()`'s
      `**Axes:**` capture assumed one physical LINE (true for design-personalities.md's own
      unwrapped blocks) but every `STY-*.md` file wraps the Axes line across two — caught by running
      the real 8-file catalog through the repointed parser and getting 64 spurious `RT_PERS_BAD_AXIS`
      FAILs, not assumed; fixed by bounding the capture to the paragraph (next blank line or EOF)
      instead of the line. Deleted `design-personalities.md`; updated `SKILL.md` (fixed stale "Four
      anchors" at `:22`, now "Eight styles" pointing at `references/style-catalog/`, word count
      re-verified at exactly 498 both before and after — PR 4a's own 524/5th-WARN failure mode
      avoided by trimming trailing clauses rather than guessing) and `design-tokens.md` (5→8 axes:
      "Five axes" → "Eight axes", three missing axis bullets added). `layout-patterns.md` and
      `motion.md` checked and found to need NO change: their "four positions" mentions are
      `composition`/`elevation`'s own position counts (genuinely still 4 each), not the total axis
      count — confirmed by direct read, not assumed from the task line's wording.
      **THE OPEN RISK CLOSED**: both generated chassis were stamped `Anchor: PERS-INSTITUTIONAL`
      unconditionally for BOTH site types since PR 1a (`_README.md`'s own carried-forward note).
      `_build-gallery.php`'s `:root{...}` block (`root_css_for()`, extracted from inline top-level
      code into a function) is now resolved per site type from `$CHASSIS_STYLE_BY_SITE`
      (`corporate => institutional`, `ecommerce => matter` — the exact historical pair
      `mockup-guide.md:436-447` recorded, not a fresh guess), and the `Anchor:` marker is built from
      the SAME resolved key as the `:root` tokens, so the two cannot independently drift. Verified:
      `chassis/corporate.html` now declares `Anchor: STY-INSTITUTIONAL` with `data-anchor="institutional"`;
      `chassis/ecommerce.html` declares `Anchor: STY-MATTER` with `data-anchor="matter"` — two
      DIFFERENT styles, breaking the "every site starts identical" pattern. Found and fixed a real
      regression while wiring this: keying `$css['root']` broke `count($css)-1`-based in-place
      patching used twice later in the file (block 2's `%%FIELD_SELECTORS%%` substitution) — caught
      by running the generator and getting `Undefined array key 3`, not assumed safe; fixed by
      recording the plain numeric push's own index (`$root_css_ix`) instead of keying the array.
      **Explicitly still open, disclosed, not swept under the rug**: this is a static per-SITE-TYPE
      default, not a per-PROJECT resolution — `art-direction-ledger` (Slice 5) is where a real
      project's chassis gets re-pointed from an actual manifest.
- [x] 4c.3 GREEN: zero live references to the deleted file remain (verified by repo-wide grep,
      re-checked after every edit); historical citations in `docs/superpowers/`,
      `openspec/changes/archive/`, and this change's own `proposal.md`/`design.md`/`specs/` are kept
      as the recorded history they are, per the PR 1f discipline this task inherits. One reference
      could not be repointed: `skills/_novamira-framework.md:103` still names
      `design-personalities.md` in a reference table, but that file carries the user's own separate
      uncommitted edits this session was explicitly instructed not to touch — disclosed, not fixed,
      not blocking (the audit does not read that file). Full chain confirmed green WITH THE FILE
      ABSENT (not merely before deleting it): `test-container-hygiene` 81 + `test-framework-audit`
      727 + `test-audit-signals` 22 + `test-write-path` 514 = **1344 OK / 0 FAIL**. Real-repo audit:
      **0 FAIL / 4 WARN** (same 4 pre-existing word-budget WARNs — elementor-core 588, html-mockup
      567, web-templates 559, woocommerce 597). All 8 `STY-*.md` entries gated by the repointed
      `RT_STYLE_TOO_SIMILAR` directly (no longer a synthetic fixture standing in for the real files):
      all 28 pairs re-verified, maximum shared 2/8, table in apply-progress. `RT_PERS_ID_MISSING`
      retired along with `$PERS_IDS` (style-catalog spec's own "no verifier for catalog SIZE by
      design" scenario) — its two fixtures removed rather than left asserting a row that no longer
      exists; net test count is 727 (was 733 at PR 4b), the 6-assertion difference is exactly the
      retired fixtures plus one vacuous absence-check that had nothing left to assert against.
      Diff: 20 files modified + 1 deleted, 969 changed lines (496 insertions + 317 deletions across
      the 20 modified files, +156 for the deletion), 619 over the ~350 estimate — not
      `size:exception` (Work Units table lists 4c budget "OK"), reported per instruction: the true
      scope spanned two production files (framework-audit.php's parser repoint AND
      _build-gallery.php's existence-assertion fix AND its chassis per-site-type refactor, the
      latter genuinely required to close the open risk honestly rather than a marker-only patch),
      ~250 lines of test-file repointing across every `design-personalities.md`-writing fixture, and
      9 documentation files carrying live pointers to the deleted file.

## Slice 5 — Intake, persistence, ledger (`art-direction-ledger`, `manifest-section-contract`)

- [x] 5a.1 RED: fixture — `es_manifest_record('design', …)` called during resolution →
      `es_manifest_read()['design']` holds non-empty `STY-*` id (no writer call exists yet).
      **Verified genuinely red**: added the fixture (`tests/test-write-path.php`) calling
      `es_record_style_resolution()` before it existed, ran the suite — `Fatal error: Uncaught
      Error: Call to undefined function es_record_style_resolution()`, script halted at that exact
      line. Confirmed by repo-wide grep first: zero hits for `es_manifest_record\('design'` in any
      production `.php` file — only in tests and doc prose, exactly what the task claims.
- [x] 5a.2 Wire the call site — **re-verified against the checkout, drift found in the session's own
      citation**: the path is `skills/elementor-core/assets/es-builder.php` (top-level
      `elementor-core/` does not exist). `:2440-2462` is `es_manifest_record()`'s own definition,
      unchanged (design's "needs no signature change" holds) — the actual new call site is
      `es_record_style_resolution($sty_id, $negative_brief, $rejected_tone)`, added directly after
      it: validates all three fields non-empty and fails CLOSED (same contract `es_manifest_record()`
      itself already keeps) before delegating to `es_manifest_record('design', …)`.
      `es_manifest_sections()`'s own docblock (`:2417-2427`), which said "`design` ... written by
      nothing ... so the gap is countable," updated to name the new writer — leaving that line
      standing would have planted the exact false-claim defect this PR closes, one function away
      from its own fix. Documented at `elementor-core/references/knowledge.md`, deliberately NOT
      `SKILL.md`: the real audit already WARNs `elementor-core` at 588/600 words (12 from the FAIL
      ceiling), and `references/` carries no word-cap rule at all (`RT_BODY_OVER_500`/`_600` in
      `framework-audit.php` only inspects `SKILL.md`, confirmed by reading the rule) — a new
      Execution Step there risked flipping an existing WARN into a new FAIL for zero net benefit.
      `web-templates/references/recommender.md:41-52` intake (citation confirmed exact, no drift)
      extended with a new subsection asking the style pick, the negative brief, and the rejected
      colour temperature — **new ground, confirmed before writing**: zero prior hits for
      `evitar|avoid|competitor|mood|art direction` across every intake file in the repo.
- [x] 5a.3 Removed the stale annotation at
      `docs/superpowers/specs/2026-08-14-perceptual-axes-design.md` — **re-verified, drift found**:
      cited `:172`, the annotation itself actually sits at `:174` (`:172-173` is the original claim
      sentence, untouched). Deleted the blockquote entirely rather than replacing it with a new
      "fulfilled" marker — the spec's own scenario is titled "removed, not left standing," and the
      original sentence, now true, needs no annotation of any kind.
- [x] 5a.4 RED: fixture — style resolved, then re-resolved same session → `design` section
      overwrites, never appends. Two `es_record_style_resolution()` calls in one `wp_fake_reset()`
      block (`STY-EDITORIAL` then `STY-MATTER`); asserted the manifest holds ONLY `STY-MATTER` and
      its own `negative_brief`/`rejected_tone`, not a merge of both. Passes for the right reason,
      confirmed by reading `es_manifest_record()`'s body: it replaces `sections['design']` wholesale
      (`$manifest['sections'][$section] = array(...)`), never merges — no extra code needed beyond
      5a.2's writer, exactly as design.md's D5 predicted.
- [x] 5a.5 GREEN: `php tests/test-write-path.php` → **523 OK / 0 FAIL** (was 514 at PR 4c close; +9
      new assertions — the resolution round-trip, the fail-closed validation branch and its two
      companion assertions, the overwrite-not-append pair — 0 regressions in the surrounding manifest
      block or anywhere else). Full chain: `test-container-hygiene` 81 + `test-framework-audit` 727
      (unchanged — `framework-audit.php` untouched this PR) + `test-audit-signals` 22 +
      `test-write-path` 523 = **1353 OK / 0 FAIL**. Real-repo audit: **0 FAIL / 4 WARN**, the same 4
      pre-existing word-budget WARNs, unchanged word counts (`elementor-core` still exactly 588 —
      `SKILL.md` deliberately left untouched, see 5a.2). `php -l` clean on both touched PHP files.
      Diff: 5 files touched (`es-builder.php`, `knowledge.md`, `recommender.md`,
      `perceptual-axes-design.md`, `test-write-path.php`), 100 insertions / 3 deletions = 103 changed
      lines, well under the ~200 estimate.

> ### BLOCKING REQUIREMENT — chassis anchor: does NOT close in 5a, reported per instruction, not
> quietly left
>
> `_build-gallery.php`'s `$CHASSIS_STYLE_BY_SITE` (`:18120-18123`) CANNOT be made to resolve from
> `es_manifest_read()['design']` in this PR, or in any future PR without a different generator
> architecture. Verified, not assumed:
> 1. `_build-gallery.php` has ZERO WordPress bootstrap — no `get_option()`, no `wp-load.php`, no
>    `WP_CONTENT` anywhere in the file (grepped directly). It is a pure offline CLI script; there is
>    no WordPress option table for it to read `es_manifest_read()` FROM, this session or any other,
>    unless the generator itself grows a WP bootstrap — a materially different, unscoped change
>    design.md's own D1 argued explicitly against ("zero lines move" was the entire point).
> 2. It has no per-PROJECT identity: the loop that builds `$CHASSIS_STYLE_BY_SITE`'s two outputs
>    (`:18128-18166`) produces exactly ONE demo chassis per site TYPE (`corporate`, `ecommerce`),
>    never per client. There is no "project" at generation time to look up in ANY manifest, real or
>    hypothetical — this is PR 4c's own already-standing disclosure (`:18104-18119`,
>    `_README.md:180-187`), re-verified here, not new.
> 3. The REAL per-project build path (`elementor-core`/`es-builder.php`, live WP context, genuine
>    `es_manifest_read()` access) has NO chassis/anchor concept AT ALL to wire the new `design`
>    section into — confirmed by grep: zero hits for `chassis`/`CHASSIS`/`anchor_key` in
>    `es-builder.php`. Today the agent hand-fills `es_tokens()`'s override from the resolved
>    `STY-*.md` catalog entry (SKILL.md step 2) with no automatic resolution step. Building one is
>    real, unscoped, unestimated work — not covered by 5a.1-5a.5, and not sized anywhere in
>    design.md's Migration/Rollout table.
>
> **Not touched, and why not**: adding a one-line "fallback, not a choice" label to
> `$CHASSIS_STYLE_BY_SITE`'s comment WITHOUT any actual conditional manifest-read behind it would
> itself be a new false claim — the map would still be the ONLY source of truth, unconditionally,
> just wearing a different label. That is the exact defect this whole change exists to eliminate,
> reproduced in miniature. The existing PR 4c disclosure (`:18104-18119`) already states the true
> constraint as honestly as a static map can; it was left as is.
>
> What WOULD close this: a new `es_resolve_chassis_anchor()`-shaped function in `es-builder.php`
> reading `es_manifest_read()['design']['style']` with a documented, honestly-labeled fallback for
> the unresolved case, wired into a REAL per-project build's execution steps — this is follow-on
> work for a future PR, not 5a.
> ### THE SLICE 4 EXIT CRITERION WAS MIS-SPECIFIED — corrected here by the orchestrator
>
> It read: *"Slice 4 does not close until the chassis anchor is resolved from the selected
> `STY-*`, not hardcoded."* PR 5a proved that is **not achievable, and not the right target**:
>
> - `_build-gallery.php` has ZERO WordPress bootstrap — verified, no `get_option`, no
>   `wp-load`, no `ABSPATH`. It is a pure offline CLI generator with no option table to read.
> - It has no per-project identity. It emits ONE demo chassis per SITE TYPE. **There is no
>   project at generation time to resolve a style for.**
> - `es-builder.php`, which does have live manifest access, has ZERO chassis or anchor concept
>   — verified by grep. Building one is unscoped work this change never proposed.
>
> PR 5a also declined to label the static map a "fallback" without conditional logic behind
> it, on the grounds that the label would be a new false claim — the same defect this change
> exists to remove. That was the right call.
>
> **The real defect and its real closure.** `mockup-guide.md:436-447` recorded that every
> corporate site shipped `PERS-INSTITUTIONAL` *because nobody was asked*. The cure is not a
> dynamic chassis; it is that the question gets asked, the answer gets persisted, and the
> delivered mockup is checked against it. Two of the three now exist: the intake asks
> (PR 5a, including the negative brief), and `RT_MOCKUP_AXES_MISMATCH` enforces by VALUE
> (PR 1e). `es_record_style_resolution()` persists it (PR 5a).
>
> **WHAT REMAINS, AND IT IS A REAL GATE — required in PR 5b:** nothing yet FAILS when a
> project's delivered mockup still carries the chassis default and no style resolution was
> ever recorded. That silence is the actual mechanism by which the tamest corner shipped for
> months. A default that survives because nobody chose is exactly what this change exists to
> make impossible, so the gate belongs with the ledger.

- [x] 5b.1 RED: fixture — no `STY-QUARRY` in last 5 rows → `RT_STYLE_REPEATS_RECENT` silent (rule
      doesn't exist yet). **Verified genuinely red before implementing**: added `fx_ledger()` and 5
      scenarios (r150–r154) to `tests/test-framework-audit.php`, ran the suite against the
      unmodified audit — 3 of the new assertions FAILed exactly as expected (`<0 rows matched,
      expected exactly 1>` for the WARN- and FAIL-presence checks, wrong exit code for the FAIL
      case), 737 OK / 3 FAIL. This scenario's own two assertions (silence) were already true in
      that RED state — the rule not existing is one honest way to be silent — its real value lands
      with 5b.5's GREEN run below, same "value, not novelty" discipline PR 4a/4b used.
- [x] 5b.2 RED: fixture — `STY-QUARRY` at row 3 of last 5 → WARN, audit still exits 0. Genuinely
      red in the same run above: `fx_row_level($out151, ['RT_STYLE_REPEATS_RECENT', 'STY-QUARRY'])`
      matched zero rows before implementation.
- [x] 5b.3 RED: fixture — `STY-QUARRY` at row 6 (outside window) → stays silent. Same "silent
      because the rule doesn't exist yet" honesty as 5b.1; real value lands with 5b.5.
- [x] 5b.4 Created `skills/ux-design-system/references/shipped-log.md` (empty ledger — header row
      `Date | Client | Style | Ground | Accent | Scale | Chassis` + separator, zero data rows).
      Implemented `RT_STYLE_REPEATS_RECENT` (WARN, `house-rules.md:31`) over the last 5 rows via a
      new generic pipe-table parser `ledger_table_rows()` (`framework-audit.php`, mirrors
      `axis_rows_for()`'s header-token-matching resilience) — WARNs when the newest row's `Style`
      also appears among the 4 rows before it in that same 5-row window, silent otherwise. Gated on
      `file_exists($ledger_file)`, same pattern `RT_GALLERY_STALE` uses, so a fixture root without
      the file never sees the check at all.
      **Designed to dock onto `skills/blind-judges/references/corpus.md` (read, not edited, per
      instruction), not compete with it**: `shipped-log.md` states explicitly that it is the
      measured half of the same two-part memory `corpus.md` is the seen half of — same write
      moment (the orchestrator, after both verdicts land, never a judge — `corpus.md`'s own rule,
      restated here so nobody builds a third memory), same 5-row window (`corpus.md`'s own
      "Retention" section already anticipated this: "Five matches the window
      `RT_STYLE_REPEATS_RECENT` uses"), joined by `Date`+`Client` here to `Date`+`Project` there.
      `RT_ORPHAN_FILE` closed WITHOUT touching `SKILL.md` (498/500 words, no headroom — PR 4a's own
      lesson): a pointer paragraph added to `references/style-catalog/_README.md` instead, already
      transitively reachable from `SKILL.md`'s `references/style-catalog/` mention.
      **ALSO REQUIRED IN THIS PR, per the corrected criterion block immediately above 5b.1**:
      implemented `RT_STYLE_UNRESOLVED_DEFAULT` (FAIL) alongside `RT_STYLE_REPEATS_RECENT`, closing
      "nothing yet FAILS when a project's delivered mockup still carries the chassis default and no
      style resolution was ever recorded." **The honest offline signal, found by investigation, not
      assumed to exist**: the audit cannot read `es_manifest_read()` (live WP option) and cannot see
      a delivered mockup's own HTML either — `corpus.md` states plainly why none is stored in this
      repo ("client work does not belong in this repository"). The one signal the repo CAN see is
      the ledger row itself: a row whose `Chassis` names which site type a delivery started from
      but whose `Style` is blank is exactly a delivery that shipped on the untouched default with
      no resolution ever recorded — reading the SAME file `RT_STYLE_REPEATS_RECENT` reads, the same
      technique design.md D5 established for that row. FAIL, not WARN (unlike its sibling): this is
      a completeness check, not a judgment call — `mockup-guide.md:436-447`'s "not because anyone
      chose them but because nobody was asked to" is the defect this whole change exists to make
      impossible, so silence here is not acceptable the way a repeat is.
      2 new `ROW_TYPES` entries + `CONTRIBUTING.md` rows (both, `RT_ROWTYPE_UNDOCUMENTED`'s own
      gate — verified 0 FAIL after adding both).
- [x] 5b.5 GREEN: all 5 ledger fixtures pass exactly (the 3 originally specified plus the 2 for the
      additional required gate); full chain green. `tests/test-framework-audit.php`: **740 OK / 0
      FAIL** (was 727 at PR 5a close; +13 net new — 8 assertions across 5 scenarios + 5
      "subprocess launched" checks the shared harness adds per scenario). Real-repo audit: **0 FAIL
      / 4 WARN** — the same 4 pre-existing word-budget WARNs, unchanged (elementor-core 588,
      html-mockup 567, web-templates 559, woocommerce 597; `ux-design-system` confirmed still 498
      words via `--word-report`). Full chain: `test-container-hygiene` 81 + `test-framework-audit`
      740 + `test-audit-signals` 22 + `test-write-path` 523 = **1366 OK / 0 FAIL** (was 1353 at PR
      5a close). `php -l` clean on both touched PHP files. `git status`: only the 4 intended tracked
      files (`CONTRIBUTING.md`, `framework-audit.php`, `_README.md`, `test-framework-audit.php`)
      plus 1 new untracked file (`shipped-log.md`); the 5 DO-NOT-TOUCH paths (`corpus.md` and the
      rest of `skills/blind-judges/`, both `agents/blind-judge-*.md`, the user's own uncommitted
      edits to `agents/novamira-web-orchestrator.md` and `skills/_novamira-framework.md`) confirmed
      untouched — `corpus.md` does not appear in the diff at all. **Diff: 219 insertions across 4
      tracked files + 70 lines for the new `shipped-log.md` = 289 changed lines, 109 over the
      ~180-line estimate, reported per instruction**: the Work Units table's ~180 figure covered
      only `RT_STYLE_REPEATS_RECENT` and the empty ledger; it could not have anticipated
      `RT_STYLE_UNRESOLVED_DEFAULT`, which the corrected criterion block above 5b.1 added to this
      PR's scope after that estimate was written — a second FAIL-level rule with its own 2 fixtures,
      on top of the 3 originally-specified WARN fixtures, plus `shipped-log.md`'s own prose
      documenting how it docks onto `corpus.md` rather than duplicating it, which the session
      explicitly required stating in the file itself.
- [x] 5c.1 RED: fixture (`r155`) — `STY-FIX-SIX` declares 6 toggles (`fx_sty_precharge()`, new
      fixture helper), a ledger row resolved to it ships 5 at the declared value, 1 (`TGL-F`)
      absent from the `Toggles` cell entirely. **Verified genuinely red before implementing**:
      stashed `framework-audit.php` + `CONTRIBUTING.md` back to PR 5b's HEAD, reran the suite —
      744 OK / 1 FAIL, isolated to exactly the one assertion that reads the new rule's own FAIL
      row (`<0 rows matched, expected exactly 1>`); the companion exit-code assertion was already
      true pre-implementation for an unrelated reason (`STY-FIX-SIX` reuses `PERS-EDITORIAL`'s own
      axes verbatim, so `RT_STYLE_TOO_SIMILAR` already forces exit 1 — disclosed, not hidden, same
      "value not novelty" discipline PR 4a/4b/5b used), and r156's silence-only assertion was
      trivially true too (the rule not existing is one honest way to stay silent). Un-stashed,
      confirmed GREEN.
- [x] 5c.2 RED: fixture (`r156`) — `STY-FIX-TWO` declares 2, `STY-FIX-SIX-B` declares 6 (same
      catalog, no delivery ever resolves to the second), a ledger row resolved to `STY-FIX-TWO`
      ships exactly its 2 at declared value → the audit does not FAIL for "too few" against
      either a fixed count or the bigger catalog sibling. Same RED run as 5c.1 above.
- [x] 5c.3 Verified all 8 `STY-*.md`'s precharge tables (PR 4a/4b's own work): each already
      declares 5 rows with genuinely varied values across styles (motion-intensity: `default`×3,
      `sutil`×2, `audaz`×2; hero-type: `imagen fija`×6, `slider`×1; cta-strength: `suave`×3,
      `fuerte`×2, `medio`×2; card-style: `imagen grande`×3, `compacta con datos`×3) — nothing to
      complete, no edits needed to any real `STY-*.md`. Implemented `RT_STYLE_PRECHARGE_UNSHIPPED`
      (FAIL) in `framework-audit.php`: two new helpers, `style_precharge_rows()` (parses a
      `STY-*.md`'s own "## Toggle precharge" / "## Precarga de toggles" table via the existing
      `ledger_table_rows()` generic pipe-table parser — no third hand-rolled one) and
      `ledger_toggle_map()` (parses the ledger's new `Toggles` cell, `TGL-ID=value; …`); a new rule
      block reads, for every ledger row WITH a resolved `Style`, that style's own declared
      precharge list and FAILs — naming the toggle and the style — for each declared toggle the
      row's `Toggles` column does not show shipped at the declared value. **No universal floor,
      by construction**: the comparison is always against the resolved style's OWN file, never a
      fixed count or another style's list. Extended `shipped-log.md`'s row shape with the new
      `Toggles` column (documented in its own "Row shape" table and a new third bullet under
      "Three audit rules read this file") and `web-templates/references/toggles.md`'s intake
      wiring with a new paragraph naming the second precharge source (`STY-*`, alongside CAPA 2)
      and where the confirmed-or-changed value is recorded for the audit to read.
      `CONTRIBUTING.md` documents the new row (`RT_ROWTYPE_UNDOCUMENTED`'s own gate).
- [x] 5c.4 GREEN: both fixtures pass exactly (see 5c.1's verbatim RED→GREEN run); full chain green.
      `tests/test-framework-audit.php`: **745 OK / 0 FAIL** (was 740 at PR 5b close; +5 net new —
      3 assertions across 2 scenarios + 2 "subprocess launched" checks). Real-repo audit: **0 FAIL
      / 4 WARN** — the same 4 pre-existing word-budget WARNs, unchanged (elementor-core 588,
      html-mockup 567, web-templates 559 confirmed via `--word-report`, woocommerce 597). Full
      chain: `test-container-hygiene` 81 + `test-framework-audit` 745 + `test-audit-signals` 22 +
      `test-write-path` 523 = **1371 OK / 0 FAIL** (was 1366 at PR 5b close). `php -l` clean on
      both touched PHP files. `git status`: exactly 5 tracked files modified (`CONTRIBUTING.md`,
      `framework-audit.php`, `shipped-log.md`, `toggles.md`, `test-framework-audit.php`); the 5
      DO-NOT-TOUCH paths confirmed exactly as inherited (untouched by this session). **Diff: 198
      insertions / 9 deletions = 207 changed lines, 57 over the ~150-line estimate**, reported per
      instruction: `framework-audit.php`'s two new helpers plus the rule block (87 lines) and the
      test file's new `fx_sty_precharge()` fixture helper plus two full RED/GREEN scenarios with a
      6-row and a 2-row precharge table each (93 lines) needed the room, at the same
      documentation density every prior PR in this change used.

## Slice 6 — `ROUTE-BESPOKE` (`bespoke-route`)

- [ ] 6.1 RED: fixture — bespoke manifest missing `ornament` → `RT_BESPOKE_UNDECLARED` FAILs,
      naming the missing axis (no such rule exists yet).
- [ ] 6.2 RED: fixture — complete manifest (8 axes + wireframe) → `RT_BESPOKE_UNDECLARED` does not FAIL.
- [ ] 6.3 RED: fixture — promoted entry shares 3/8 with an existing entry → `RT_STYLE_TOO_SIMILAR`
      FAILs (promotion grants no exemption).
- [ ] 6.4 Implement zero-precharge intake (8 explicit axis answers + wireframe) before
      builder-core; `RT_BESPOKE_UNDECLARED`; confirm no accessibility gate is skipped (AA, AAA 7:1,
      4.5:1 eyebrow, one `<h1>`, Lighthouse ≥90, touch targets, `RT_MOCKUP_DISCLOSURE_STATE`); wire
      ledger registration and the promotion path into `STY-*`.
- [ ] 6.5 GREEN: all 3 fixtures pass exactly; full chain green — this PR is the tracker's final
      candidate for merge to `main`.

## Re-baselining tasks (explicit, per proposal/design)

- [ ] Re-baseline `RT_GALLERY_NOT_DISTINCT` (28→8-axis), `RT_PROOF_NOT_DISTINCT` (21→8-axis),
      `RT_STYLE_TOO_SIMILAR` (13, renamed), `RT_MOCKUP_AXES_MISMATCH` (9, value-check), and
      `RT_AXIS_VALUE_MISSING` (33) — all land in PR 2b, same commit as the rule they pin.
- [ ] Re-baseline total assertion count ≥ 1195 (measured baseline: container-hygiene 81 +
      framework-audit 664 + audit-signals 22 + write-path 428) after every slice; confirm
      `RT_UXDS_NO_AXIS_STEP` and `RT_QA_NO_AXIS_CHECK` still fire at the end of Slice 6.
- [ ] Confirm final audit run stays at 0 FAIL / 4 WARN (pre-existing word-budget WARNs) with no new
      WARN introduced.
