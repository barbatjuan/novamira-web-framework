# Handoff Block — structured mockup approval

Emitted at the moment a mockup is approved (Execution Steps § 4), before the build gate. It exists
so a build-gate confirmation has something explicit to confirm against, rather than approving
"the vibe" of a mockup and letting decisions live only as chat scrollback. Per
`mockup-handoff-persistence`, this block is `chat/markdown text only` until the user explicitly
confirms the build gate — `es_manifest_record()` MUST NOT be called for it before that point.

## The 8 fields

| # | Field | What it names |
|---|-------|---------------|
| 1 | Lane | `TPL-*` id (catalog lane) or `bespoke` (`web-templates/references/lanes.md`) |
| 2 | Anchor | the `PERS-*` id the mockup is pointed at (`design-personalities.md`) |
| 3 | Brand overrides | ground / accent / type pairing — the ONLY axes a brand may override |
| 4 | Photo set | the manifest slugs actually rendered (own shoot, one row per slug) |
| 5 | Container | the `--container-max` token reference (never a hardcoded literal) |
| 6 | Contrast result | the accent's re-measured contrast ratio against every ground it renders text on |
| 7 | Responsive | the declared mobile/tablet/desktop behaviour — never "pendiente de definir" |
| 8 | Page set | every page archetype the demo declares, home + inner pages, even if only the home is built so far |

## Template

```
Lane: [TPL-* id | bespoke]
Anchor: PERS-*
Brand overrides: ground=… accent=… type=…
Photo set: [slug-1, slug-2, …]
Container: var(--container-max)
Contrast result: accent vs ground = X.XX:1 (…and against ground-alt if used)
Responsive: [explicit behaviour per breakpoint]
Page set: [home TPL-*, inner-page TPL-*, …]
```

## Timing and the build gate

- Emit the block as soon as the mockup is approved — never before, since earlier decisions may
  still change.
- Any field missing — most often Responsive, since it is the one most often deferred — means the
  handoff is incomplete. The build gate MUST NOT proceed until every field resolves.
- No `framework-audit.php` row inspects the CALL ORDER between mockup approval and
  `es_manifest_record()`; this is a process contract, not a static check, and `qa-review`'s
  pre-build checklist is the only thing that catches a premature call.
