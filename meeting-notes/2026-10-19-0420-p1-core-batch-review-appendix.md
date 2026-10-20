# Appendix: P1 core batch review (2026-10-19 04:20)

Ready-to-use text from the [department review](2026-10-19-0420-p1-core-batch-review.md).

## Release-note highlights (marketing)
- Incomplete pages stay as drafts, whether you publish or schedule them. The editor names each missing field and its tab, and the notice stays until the fields are filled in.
- A landing page opens only on its own client's subdomain; old addresses redirect to it.
- Preview works for drafts and published pages alike.
- Fewer repeat API calls: Brandfetch colours are reused for a week, and addresses are looked up again only when the client's name or address changes. When Brandfetch has no colours for a client, they come from the logo.
- API keys on Settings → API Keys show as dots, and password managers leave them alone.

Don't describe the keys as "encrypted" or "securely stored": they are hidden on screen and stored as before.

## Smoke test (ops)
On staging, PHP 7.4 and WP 5.9 if possible, then current versions.

1. Start on 1.0.15 with saved Google browser, server and Brandfetch keys, and at least two clients with coordinates. Record the `carbon_fields_theme_options_container` option and the clients' latitude and longitude.
2. Open Settings → API Keys in a second tab, then update through Dashboard → Updates.
3. Save the old tab (it shows the asterisk mask). All three keys must be unchanged in the database.
4. Reload Settings. The keys show as dots, the browser doesn't autofill them, and each help text names the saved key's last four characters. The container option has no `lpmanager_*` keys left, or is gone.
5. `wp cron event list` shows `lpmanager_refresh_geocodes`. Run `wp cron event run lpmanager_refresh_geocodes`:
   - With a valid server key, `_lpmanager_geocoded_at` is set.
   - With the server key emptied and a referrer-restricted browser key, legacy coordinates are deleted and `_lpmanager_geocode_retries` is set. After three more runs the retry meta is gone.
6. Routing, logged out (needs wildcard DNS on staging, or `curl -H "Host: …"`):
   - `a.<domain>/<A page>/` returns 200.
   - `a.<domain>/<B page>/?utm=x` returns 302 to `b.<domain>/<B page>/?utm=x`.
   - `<domain>/<A page>/`, `www.<domain>/<A page>/` and `<old-slug>.<domain>/<A page>/` return 302 to `a.<domain>/<A page>/`.
   - A main-site page whose slug matches a landing page still shows the main-site page.
   - `a.<domain>/?p=<B id>` and `?landing_page=<B slug>` return 404.
   - `evil<domain>` is not routed.
7. If staging's home is `www.example.com`, check that `a.www.example.com/<page>/` routes.
8. Preview a new draft, a published page with unsaved changes, and a scheduled page. Each opens on the main site and shows the unsaved edits.
9. Publish, then schedule, an incomplete page. It stays a draft, and the notice lists each field's label and tab and is still there after a reload. Quick Edit-publish another incomplete page: the list screen names it. Fill in the fields and publish: the notice and the meta are gone.
10. Break the active custom template's `lib/fields/landing-page-fields.php` (call an undefined function). wp-admin still loads and shows the error notice.
11. Save a client with an unchanged name and address: no Places calls are made (Query Monitor or the debug log). With no Brandfetch key, logo colours are used. A domain entered as `https://www.Example.com/x` is stored as `example.com`.
12. Purge page caches, then repeat step 6 through the CDN or cache.
13. Deactivate: both geocode events are gone. Reactivate: the daily event is scheduled again on the next request.
14. Uninstall on a two-site network: the new metas, transients, events and the client latitude and longitude are gone on both sites. `lpmanager_pro_*` and `_lpmanager_track_*` are untouched, and place IDs are kept.
15. Rollback: reinstall the 1.0.15 zip, re-enter the browser and Brandfetch keys, and save Settings once. Pages and kept drafts are intact.
16. Check the error log at every step under PHP 7.4.

## Support FAQ macros
1. **"My landing page link redirects (or shows page not found) since the update."** A page now opens only on its own client's subdomain, and other addresses redirect there. Open the page, check Client Name under Landing Page Details, and use the Landing Page URL shown in the list in your ads. If you renamed the client's slug, update the ads to the new subdomain. Then purge your page cache.
2. **"Preview opens on my main domain, not the client's subdomain. Is that a bug?"** No. Login cookies don't reach client subdomains, so previews there showed the live page or "not allowed to preview drafts". Previews now open on the main site with the same template and content. Once the page is published, check it at its Landing Page URL.
3. **"My page went back to Draft, and a box lists missing fields and won't go away."** Pages with empty required fields are kept as drafts when published or scheduled, so a half-finished page never goes live. Fill in each listed field (the notice names the tab), then publish or schedule again. The notice and the list entry clear on the next save. If you don't need the page, trash it.
