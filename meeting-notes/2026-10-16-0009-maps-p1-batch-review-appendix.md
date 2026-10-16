# Appendix: Maps lazy-load and P1 front-end batch review (2026-10-16)

Ready-to-use text drafted by the departments. Linked from PLAN.md and the [meeting notes](2026-10-16-0009-maps-p1-batch-review.md).

## Smoke test steps 16–24
These add to the 15 steps in the [10-14b smoke test](2026-10-14-2245-p0-batch-review.md#smoke-test). Run them once at the top of the stack (`perf/maps-lazy-load-p1-batch`). Steps marked "logged out" use a private window, because logged-in users always get jQuery through heartbeat and wp-auth-check.

16. **jQuery (logged out).**
    - On Template A styles 1, 2 and 3, `typeof jQuery === 'undefined'`, there are no console errors, and the FAQ, CTA and reveal all work.
    - Put `<script>jQuery(function(){console.log('ok')})</script>` in that client's Header Tracking Code. jQuery now loads in the head before the tag, and "ok" is logged.
    - Repeat with `$.post(...)`, and with a GTM container snippet. Both should load jQuery.
    - Remove the tag and add `add_filter( 'lpmanager_load_jquery', '__return_true' );` in a mu-plugin. jQuery loads.
17. **Logged in.** jQuery is present on all three templates, the admin bar works, and there's no false "session expired" message.
18. **Template B with an empty `reviews_html`.**
    - No requests for `swiper.min.js` or `swiper-bundle.css`.
    - `main.js` loads, and there are no console errors.
    - No Reviews section or heading is shown.
19. **Template B with reviews.**
    - `swiper.min.js` loads and `swiper.js` doesn't.
    - The slider works, pauses on hover and stops on focus.
20. **Maps lazy-load (logged out).**
    - On load, there's no `maps.googleapis.com/maps/api/js` request.
    - Scroll to about 300px above the footer: exactly one request, and the map and info window render.
    - Tabbing past the map does **not** move focus into the info window.
    - The 400px box (placeholder colour) doesn't shift the layout (Lighthouse CLS).
    - A short page whose footer is visible on load requests Maps at once.
    - A client with no lat/lng shows no message to visitors, and the editor message to editors.
    - The map region has an accessible name.
21. **Server-key notice.**
    - With a browser key and no server key, it shows on Landing Pages, Clients, the Dashboard and Settings.
    - It doesn't show on Posts or Plugins, or to non-admins.
    - It disappears once a server key is saved, and "Go to Settings" opens Settings.
22. **Template C menus.**
    - At 375px, the toggle opens the menu, sets `aria-expanded="true"`, and switches to the close icon.
    - "Services" lists the client's other landing pages, and the current page has `aria-current`.
    - A link tap navigates and closes the menu. Escape closes it and returns focus to the button.
    - The menu isn't clipped and doesn't appear above 921px.
    - At exactly 921px, one header shows.
    - On desktop, the Services dropdown opens on hover and on keyboard Tab, and clicking "Services" doesn't jump to the top.
    - A client with no other pages shows no Services item.
    - `debug.log` gets no "Landing Pages Debug" lines.
23. **Cache.** With a page-cache plugin active, purge after the upgrade and confirm the new HTML is served, with no Maps request on load.
24. **Rollback (extends step 14).** After going back to 1.0.6, jQuery and Swiper load as before, the map loads on page load, and nothing fatals.

## Release-note draft (marketing)
- **Lower Google Maps costs.** The Template B footer map only loads when a visitor scrolls near it, so you aren't billed for visitors who never reach it.
- **Lighter pages.** Template A pages no longer load jQuery, and Template B pages without reviews skip the review slider (about 300 KB uncompressed).
- **A working mobile menu on Template C.** It lists the client's other pages under Services, plus the page's sections.
- **A heads-up before a key gets exposed.** Admins see a warning when only the Google browser key is set.

## Support FAQ
- **Q: The map in my Template B footer disappeared or shows nothing.**
  A: The map now loads only when a visitor scrolls near the footer, which cuts Google billing, so some speed-test screenshots won't show it. If it never appears, log in and view the page: editors see the reason. Usual causes: the client has no Contact Address saved (save the client to look up its coordinates), the browser key is empty, or the key's website restriction leaves out `https://*.yourdomain.com/*`.
- **Q: My tracking or GTM tag stopped working on Template A ("jQuery is not defined").**
  A: Template A no longer loads jQuery by default. It loads when the client's Header Tracking Code mentions jQuery, calls `$(` or `$.`, or loads Google Tag Manager. A developer can force it with `add_filter( 'lpmanager_load_jquery', '__return_true' );`.
- **Q: Why do I see "add a Google Maps server key"?**
  A: Address lookups and Fetch Reviews fall back to your browser key, which is visible in page HTML and has to stay unrestricted for that to work. Create a server key restricted to your server's IP and save it under Settings → API Keys. Then restrict the browser key to your site and its subdomains. Do it in that order.
