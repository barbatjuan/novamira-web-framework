---
name: project-context
description: "Trigger: detect stack, which builder, Elementor or Divi, read project, WordPress plugins, project constraints, brand. Inspect a WordPress site via NovaMira before building anything."
license: Apache-2.0
metadata:
  author: "juan"
  version: "1.0"
---

# Project Context

Read-only reconnaissance. Determine WHAT you are building on before any skill writes
anything. Modifies nothing.

## Activation Contract
**Existing site**: run FIRST, before `web-templates` / `ux-design-system` / builder-core — the
build routes on what it reports. **New site (greenfield)**: do NOT run during the design phase
(nothing to inspect, wastes the connector round-trip); run at the **build gate** instead, once a
WordPress target + connector exist, to confirm connector/builder/theme before writing.
Re-run if the target site changes.

## Hard Rules
- Never write, create, or delete. Detection only.
- Report the builder explicitly (`elementor` | `divi` | `unknown`); the orchestrator routes on it.
- If the builder is `unknown` or ambiguous, say so — do not guess; let the orchestrator ask.

## Execution Steps (via NovaMira `execute-php`)
1. **Builder**: active plugins (`get_option('active_plugins')`) — `elementor/elementor.php`,
   `elementor-pro`, or Divi (`et_divi` theme / Divi Builder). Version from `ELEMENTOR_VERSION`.
2. **Commerce**: is `woocommerce/woocommerce.php` active? note WC version.
3. **Theme**: `wp_get_theme()` name + child theme. Recommendation for the orchestrator: on a NEW
   Elementor build default to **Hello Elementor** (minimal, no conflicts with global tokens/Theme
   Builder); if a lightweight theme is already active (Astra / GeneratePress), keep it and neutralize
   its defaults rather than swapping. Divi builds keep the Divi theme.
4. **Existing structure**: pages (`post_type=page`), which use the builder
   (`_elementor_edit_mode=builder` / Divi `_et_pb_use_builder`), Theme Builder templates
   (`elementor_library` types + `_elementor_conditions`), the active kit/global styles.
5. **Constraints**: menu (`menu-principal` etc), brand palette/logo if present, NAP
   (phone/email/address), language.
6. **Connector**: confirm the NovaMira connector UUID and that `create-upload-link` +
   `execute-php` respond (retry on transient "requires additional permissions").

## Output Contract
Return a compact block: `builder`, `builder_version`, `woocommerce` (y/n + version),
`theme`, `pages` (id · slug · builder?), `theme_templates`, `constraints`, `open_questions`.
The orchestrator uses this to route and to decide what to ask the user.

## References
- Pairs with `elementor-core` / `divi-core` (builder execution) and `ux-design-system` (look).
