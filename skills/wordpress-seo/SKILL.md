---
name: wordpress-seo
description: "Trigger: SEO, meta title, meta description, H1/H2 hierarchy, schema, structured data, sitemap, Open Graph, alt text. On-page SEO for a NovaMira-built WordPress site."
license: Apache-2.0
metadata:
  author: "juan"
  version: "1.0"
---

# WordPress SEO

On-page SEO for the built pages. Content-first and honest; no manipulation.

## Activation Contract
Use after pages exist, or when the user asks for SEO. Works with whatever SEO plugin
`project-context` found (Yoast / Rank Math) or WordPress defaults.

## Hard Rules
- One H1 per page; logical H2/H3 outline that matches the visible structure.
- Titles/descriptions are unique, human, and describe the page — not keyword stuffing.
- Every meaningful image has real alt text. Fictional/demo content stays labeled as such.
- Don't invent facts (addresses, reviews, credentials) to fill schema.

## Execution Steps
1. **Per page**: unique `<title>` (~50–60 chars) and meta description (~150–160), set via the
   SEO plugin's post meta.
2. **Headings**: verify exactly one H1 and a sensible heading tree (the builder controls the tags).
3. **Schema**: JSON-LD appropriate to the business — `LocalBusiness`/`AutoRepair`, `Product`
   (WooCommerce usually emits this), `FAQPage` for FAQ sections, `BreadcrumbList`.
4. **Social**: Open Graph title/description/image per key page.
5. **Sitemap + indexing**: ensure an XML sitemap exists and is submitted; noindex utility pages.
6. Verify the emitted tags server-side (fetch HTML, check `<title>`, meta, JSON-LD).

## Output Contract
Report per-page titles/descriptions set, heading check result, schema types added, and the
sitemap status. Flag any page where honest content is missing rather than fabricating it.
