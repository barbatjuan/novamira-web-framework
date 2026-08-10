---
name: wordpress-performance
description: "Trigger: performance, lazy load, image optimization, Core Web Vitals, LCP, CLS, page speed, reduce CSS/JS. Tune a NovaMira-built WordPress site for speed without breaking the design."
license: Apache-2.0
metadata:
  author: "juan"
  version: "1.1"
---

# WordPress Performance

Make the built site fast without regressing the look. Runs after the pages exist; measure,
then fix the biggest offender first.

## Activation Contract
Use after `elementor-core` / `divi-core` have built pages, or when the user reports slowness /
poor Core Web Vitals. Read-then-tune; never redesign here.

**Build gate — blocking.** This skill writes to a live WordPress site. Do not run until the user
has given an explicit **yes** for THIS build. Reached directly instead of routed by the
orchestrator? Ask for that yes yourself before the first write and stop until you get it.
On an existing site, confirm every page/template/asset you would overwrite by name first.

## Hard Rules
- Do not add heavy plugins or custom JS to chase a metric. Prefer builder-native + host options.
- Change one lever at a time and re-measure. Keep the design intact — verify visually with the user.
- Images are almost always the #1 win.

## Execution Steps
1. **Measure** the target pages (field data if available). Identify the worst of LCP / CLS / INP.
2. **Images**: correct sizes (no oversized uploads), WebP/AVIF where supported, `loading="lazy"`
   below the fold, explicit dimensions to kill CLS, a real `srcset`. Hero image = highest priority,
   not lazy.
3. **CSS/JS weight**: enable the builder's optimized/experimental asset loading so only used
   widget assets load; avoid pulling global libraries for one effect (our hovers ride on native
   custom-CSS exactly for this reason).
4. **Fonts**: subset + `font-display:swap`; limit weights.
5. **Caching**: page cache + the builder's static CSS cache; regenerate after content changes.
6. Re-measure and report the delta.

## Output Contract
Report before/after metrics, what changed, and any tradeoff. If a fix would hurt the design,
surface it as a decision for the user rather than applying it silently.
