# Mockup Handoff Persistence Specification

## Purpose

`es_manifest_record()` lives in `es-builder.php` and MUST only be called
after the build gate — before that point, a mockup's decisions (lane,
anchor, overrides, photo set, contrast result, page set, responsive
definition) exist only as chat text. This spec defines the structured
pre-gate handoff block and the timing guarantee that keeps it out of
persisted state until the gate is explicitly crossed.

## Requirements

### Requirement: Structured Handoff Block at Mockup Approval

At the moment a mockup is approved, `html-mockup`/`web-templates` MUST emit
a structured handoff block naming: lane (catálogo `TPL-*` id | bespoke),
anchor id, brand overrides (ground/accent/type), photo set slugs, the
`--container-max` token reference, the accent contrast re-measurement
result, the declared responsive behaviour, and the declared page set.

#### Scenario: Mockup approved with a complete handoff
- GIVEN a mockup is approved
- WHEN the approval step completes
- THEN a handoff block naming all eight fields is emitted as chat/markdown text

#### Scenario: Mockup approved with a field missing
- GIVEN a mockup is approved but its handoff omits the responsive field
- WHEN the handoff is emitted
- THEN it is incomplete, and the build gate (next requirement) MUST reject it

### Requirement: No Persistence Before the Build Gate

The handoff block's content MUST NOT be written via `es_manifest_record()`
into `es-builder.php`'s persisted state until the user explicitly confirms
the build gate. Before that confirmation, the handoff exists only as
transient chat text.

#### Scenario: Handoff emitted, gate not yet confirmed
- GIVEN a handoff block has been emitted after mockup approval
- WHEN no build-gate confirmation has occurred
- THEN `es_manifest_record()` has not been called for that handoff

#### Scenario: Build gate confirmed
- GIVEN the user explicitly confirms the build gate for an approved mockup
- WHEN the native build proceeds
- THEN `es_manifest_record()` persists the handoff's fields

### Requirement: Build Gate Rejects an Incomplete Handoff

The build gate MUST NOT proceed on a handoff missing any required field,
most notably the responsive definition (`qa-review` and
`visual-verification` both require it resolved).

#### Scenario: Gate confirmation attempted on an incomplete handoff
- GIVEN a handoff block is missing its responsive field
- WHEN the user attempts to confirm the build gate
- THEN the gate MUST NOT proceed, and the missing field MUST be resolved first

### Requirement: Handoff Timing Has No Static Verifier

No `framework-audit.php` row inspects runtime call ordering between mockup
approval and `es_manifest_record()`; this timing guarantee is a process
contract, checked operationally by `qa-review`'s pre-build checklist, not
by a static row. (No verifier: process discipline documented in
`html-mockup`/`web-templates` prose and the orchestrator's build-gate step,
verified by human/`qa-review` review of the call order.)

#### Scenario: Timing violation reviewed, not audited
- GIVEN a suspected premature `es_manifest_record()` call
- WHEN `framework-audit.php` runs
- THEN no row fires for it — the violation is only catchable by `qa-review`'s manual pre-build check
