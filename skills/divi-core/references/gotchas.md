# Divi gotchas — to be discovered

This file starts almost empty ON PURPOSE. Divi is not yet battle-tested here. Every time a
Divi build surprises you, add a confirmed entry below in this shape:

```
## <short title>
Symptom: <what you saw>
Cause: <verified reason>
Fix: <what worked>
Do NOT: <the trap>
```

## Known starting points (verify before trusting)
- Layouts store as `[et_pb_section]…[/et_pb_section]` shortcodes in `post_content`, gated by
  meta `_et_pb_use_builder = 'on'`. Writing raw shortcodes to `post_content` is the emit path.
- Divi caches compiled CSS per page (`et_core_page_resource`) — expect to clear it after writes,
  analogous to Elementor's `_elementor_css` / `_elementor_element_cache`.
- Theme Builder (header/footer/templates) is separate from page layouts.
- Global colors/fonts live in the Divi Theme Options / Theme Builder, analogous to the Elementor kit.

Confirm each of the above by introspection on the real site; move proven ones out of
"starting points" into real entries above.
