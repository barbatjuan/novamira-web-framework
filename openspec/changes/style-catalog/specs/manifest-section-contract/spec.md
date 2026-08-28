# Delta for Manifest Section Contract

## MODIFIED Requirements

### Requirement: The `design` Manifest Section Persists The Resolved Style, Not Merely An Annotated Claim

`es_manifest_record('design', …)` MUST be called during a project's style
resolution (`art-direction-ledger`'s intake, this change's Slice 5) and
MUST persist at minimum the resolved `STY-*` id. The claim at
`docs/superpowers/specs/2026-08-14-perceptual-axes-design.md:172` — that
the resolved axis is recorded via `es_manifest_record('design', …)` — is no
longer a known-false promise requiring an annotation: it MUST be true. Any
annotation from the prior requirement marking the claim unfulfilled MUST be
removed once the writer lands — a fulfilled claim still carrying an
"unfulfilled" annotation is a new false statement, in the opposite
direction from the one this spec originally closed.

(Previously: "A Known-False Promise About `design` Is Marked, Not Left
Standing" — the requirement was to annotate the false claim without
implementing a writer, because no writer existed yet and annotating a
known gap beats leaving it silently false. This is superseded, not
regressed: Slice 5 of the `style-catalog` change wires the real writer this
same spec's own Out of Scope line named as future work — "Wiring
`design`/`delivery` to real writers" is no longer out of scope for
`design`; `delivery` remains unwritten and stays out of scope. Making a
known-false claim true is strictly stronger than annotating it as false;
nothing this spec previously guaranteed is weakened.)

#### Scenario: Manifest holds the resolved style
- GIVEN a project completes style resolution
- WHEN `es_manifest_read()` is called
- THEN `['design']` contains the resolved `STY-*` id, non-empty

#### Scenario: The stale annotation is removed, not left standing
- GIVEN the annotation from the superseded requirement still sits at
  `perceptual-axes-design.md:172` after the writer lands
- WHEN a reader reaches the `es_manifest_record('design', …)` claim
- THEN no "unfulfilled" annotation remains — the claim is true and needs
  no warning; leaving a false "unfulfilled" marker on a true claim would
  recreate the exact defect this spec exists to prevent, in reverse

#### Scenario: Re-resolution overwrites, not appends
- GIVEN a project's style is resolved once, then re-resolved later in the
  same session (a design change mid-build)
- WHEN `es_manifest_record('design', …)` runs the second time
- THEN it overwrites the section — `design` holds exactly one resolved
  style, never a history; delivery history is `shipped-log.md`'s job

## Note: Out Of Scope Line Is Also Superseded

The main spec's `## Out of Scope` section names "Wiring `design`/`delivery`
to real writers" as future work. This delta narrows that line to
`delivery` only — `design` is no longer future work once this requirement
lands. `sdd-archive` MUST update that prose line alongside merging this
requirement, or the main spec will contradict its own newly-fulfilled
requirement.
