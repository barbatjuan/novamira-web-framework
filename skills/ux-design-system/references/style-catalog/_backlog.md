# Style Catalog — Backlog

Movements deferred from v1, with the reason each is deferred. A backlog without reasons is a wish
list, so every row states what is actually missing, not just that the entry is not ready.

For most of these the reason is the same shape: the motion or ornament system the movement needs
does not exist in this framework yet. `references/motion.md` documents one calm hover curve
(`cubic-bezier(.22,1,.36,1)`, `~.35–.7s`, `translateY(-4…-6px)`, `scale(1.045)`) and no other. The
ornament axis (`design-system.md` § "Ornament") defines exactly 5 positions — `none`, `rule`,
`texture`, `pattern`, `illustration` — and none of them cover a loud, saturated, or animated mark.

| Movement | Deferred because |
|---|---|
| Kinetic | needs a fast, loud motion system — scroll-triggered animation, parallax — outside the one calm curve `motion.md` documents; adding it is a motion-system change, not a style. |
| Cyberpunk | needs a neon/glow ornament position and a saturated multi-accent treatment; the ornament axis has no such position, and `ux-design-system`'s Hard Rules cap the whole framework at ONE accent colour. |
| Y2K | needs a chrome/gradient-mesh ornament and a non-monotone type system this catalog's axes do not model. |
| Retro | needs a grain/halftone print-texture treatment closer to a paper-stock simulation than `ORN-TEXTURE`'s low-contrast surface grain. |
| Playful | needs a bouncy/elastic (spring) easing curve outside the documented calm `cubic-bezier(.22,1,.36,1)`. |
| Feminine | is a named vibe, not a set of axis positions — exactly the "a position with no value is an adjective" failure `design-personalities.md`'s own opening rule warns against. Belongs in the catalog only once it is resolved into concrete positions on all 8 axes, like every other entry here. |
| Editorial Fashion | needs a large-format, video/cinemagraph-forward imagery system beyond the static photo-editorial framing the Imagery line models today. |
| Experimental | names no fixed axis position by definition, which is what `RT_PERS_BAD_AXIS` (and its widened `RT_STYLE_*` siblings) exist to refuse. An undecided-by-design style is `ROUTE-BESPOKE`'s job (Slice 6), not a catalog entry. |

None of these are rejected outright — each is a real system this framework does not have yet.
Promoting one means building that system first, then authoring a `STY-*.md` the same way the 5
ported entries and PR 4b's 3 new ones are authored, and clearing `RT_STYLE_TOO_SIMILAR` against the
full catalog like any other entry.
