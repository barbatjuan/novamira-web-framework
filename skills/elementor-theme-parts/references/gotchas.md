# Theme-part gotchas (confirmed)

Only entries about header/footer/Theme Builder parts live here. Page-level Elementor traps stay
in `elementor-core/references/gotchas.md`; this file was split out of it so the hardest recipe in
the repo sits with the skill that executes it rather than three sections away from it.

## Mobile 3-zone header (burger · logo · cart) — `order` won't save you
Common DOM: `[logo][cluster: nav-menu(burger) + actions(cart)]`. Wanted on mobile: burger
left, logo centered, cart right, all vertically centered. Because burger and cart live nested
together in the cluster while the logo is a separate sibling, CSS `order` can't interleave them
(different parents). Do it mobile-only (`@media(max-width:767px)`), desktop untouched:
1. Topbar row → `position:relative!important;` (positioning context).
2. Logo (heading widget) → `position:absolute; left:50%; top:50%; transform:translate(-50%,-50%);
   z-index:1; width:auto; white-space:nowrap;` → exact center over the bar.
3. Cluster → `width:100%; margin-left:0;` and its `.e-con-inner` → `justify-content:space-between;
   align-items:center;` → burger left, actions right.
4. Actions container (last inner child) → `width:auto; flex:0 0 auto; justify-content:flex-end;`
   → pins the cart to the right edge.

### `.elementor-menu-toggle` centers itself with auto margin
The burger toggle ships its own horizontal `margin:auto` that centers it and ignores
`align-items`. Force `margin:0!important` to be able to align it.

### Closed nav dropdown breaks vertical centering
A CLOSED `.elementor-nav-menu--dropdown` stays `display:block` at height 0 with a `margin-top`,
pushing the toggle off-center. Pull it out of flow only while closed, without breaking the open
state: `selector .elementor-menu-toggle[aria-expanded="false"] ~ .elementor-nav-menu--dropdown{position:absolute!important;}`
(open uses `aria-expanded="true"` + `position:fixed` full-screen and still wins).

## Verify mobile layout without a screenshot
The sandbox domain is browser-policy-blocked AND the in-app browser pane doesn't compose frames
for a screenshot — but JS measurement works. Use the in-app browser with mobile emulation +
`javascript_tool` reading `getBoundingClientRect()` (x / right / vertical-center) to confirm the
3-zone alignment numerically. Bust `post-<id>.css` with a `?v=` query to dodge cache.
