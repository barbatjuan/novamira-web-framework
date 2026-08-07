---
name: divi-core
description: "Trigger: Divi builder, Divi theme, Divi via PHP, build Divi page, Divi theme builder, et_pb. Generate and deploy Divi layouts via NovaMira. SCAFFOLD — validate each step and record gotchas as you learn them."
license: Apache-2.0
metadata:
  author: "juan"
  version: "0.1"
---

# Divi Core (execution) — scaffold

Parallel of `elementor-core` for sites where `project-context` reports builder = `divi`.
The visual spec still comes from `ux-design-system`; only the emit/deploy mechanics differ.

> STATUS: scaffold, NOT battle-tested like Elementor. Verify every step on the real site and
> append confirmed findings to `references/gotchas.md`. Do not present unverified Divi behavior
> as proven — say "unverified" to the orchestrator.

## Activation Contract
Use only when the active builder is Divi. Otherwise route to `elementor-core`.

## Hard Rules
- Native Divi modules only. No custom JS. Custom CSS via the module's built-in Custom CSS
  fields or the page/section advanced CSS — scoped, never a global stylesheet.
- Wrap all build logic in named functions; the NovaMira sandbox auto-runs any uploaded `.php`.
- Verify server-side (fetch compiled HTML/CSS, grep). The sandbox domain is usually
  browser-blocked; state that visual confirmation needs the user.

## Execution Steps (validate each)
1. **Detect storage**: Divi layouts live as `[et_pb_*]` shortcodes in `post_content` (meta
   `_et_pb_use_builder = 'on'`, `_et_pb_page_layout`). Confirm on the real post before writing.
2. **Emit**: build the shortcode tree in PHP (section → row → column → module) and write it to
   `post_content` via `wp_update_post`. Keep a helper library mirroring `es_*` (`di_section`,
   `di_row`, `di_module`) under `assets/`.
3. **Header/footer & templates**: use the Divi Theme Builder
   (`et_theme_builder_*` — templates + body/header/footer layouts + assignment rules). Confirm
   the exact option/post-type names by introspection before relying on them.
4. **Deploy**: update the post, clear Divi's static CSS cache
   (`et_core_page_resource_*` / Divi > cache), and verify.

## Output Contract
Report what was built, what was VERIFIED vs assumed, and every new Divi fact discovered
(append to `references/gotchas.md`). Flag unverified assumptions explicitly.

## References
- `references/gotchas.md` — Divi findings (starts near-empty; grow it).
- Mirror the concepts in `elementor-core/references/knowledge.md`, translated to Divi.
