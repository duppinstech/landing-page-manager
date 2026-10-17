# Appendix: drafted text from the 2026-10-14 baseline review

This is ready-to-use wording that the departments drafted, so the approved PLAN.md items can be done without re-running the review.
Text that depends on one of Odell's open decisions is marked **(pending decision)**.

## CHANGELOG `[Unreleased]` for the WIP _(support)_
```markdown
## [Unreleased]
### Removed
- **The "Landing Page Template" field is gone from the client screen.** It has been ignored since 1.0.6. Every landing page uses the template chosen in **Landing Pages → Settings → Templates**. Values already saved on clients stay in the database but are not used.
### Changed
- **Template A "Style variant" has moved** from the "Landing Page Details" box to the new **Layout** tab of the "Template A" box. Saved styles are kept. The field only appears when Template A is the active template.
- Template A's Style 3 label no longer names a client.
```
Correction for 1.0.6 line 28: `**Template A template** for towing and roadside services, with three layout styles (Style variant).`

## Upgrade note for the next release _(support)_
> - **If you uploaded a template under Settings → Templates, keep its .zip before updating.** Updating replaces the plugin folder and deletes uploaded templates. Upload it again after the update.
> - The "Landing Page Template" field has been removed from client screens. It has had no effect since 1.0.6.
> - Template A sites: "Style variant" is now in the **Template A → Layout** tab. Your saved styles are unchanged.

## Release-note highlights for v1.0.6 / next _(marketing)_
- New **Template A** template for towing and roadside services, with three layouts.
- Uploading a template from Settings → Templates now works, and it's locked down.
- New clients get their brand colors and map location on first save.
- Visitors no longer see a false "session expired" popup.
- Updates install only the official release package.
- Deleting the plugin removes its settings but keeps your pages, clients and keywords.

## Admin help text _(support, product, marketing)_
- **Templates tab:** "This template is used by every landing page on the site, and the change applies immediately. Each template has its own content fields. Content entered for one template is kept but not shown under another, so switching back restores it."
- **Template upload card:** "Uploaded templates are stored inside the plugin folder and are deleted when the plugin updates. Keep your template .zip and upload it again after each update."
- **Missing-template notice:** "Landing Page Manager: the active template "%s" is missing, so landing pages are showing a 404. It was probably removed by a plugin update. Upload it again under Landing Pages → Settings → Templates, or choose another template."
- **Client Domain:** "The client's main website (e.g. example.com), used for Home links in the page header and footer. Do not add https://. This does not control the landing page URL; that comes from the client slug."
- **Google key(s):** "Enable Maps JavaScript API, Geocoding API and Places API. Usage is billed to your Google Cloud account; set quotas and budget alerts." Once the key is split, the browser key text should also say "Restrict this key to your site's URLs", and the server key text "Restrict this key to your server's IP."
- **Validator heading:** `esc_html__( 'This landing page was saved, but these required fields are empty. Fill them in and click Update, or the page may look incomplete:', 'landing-page-manager' )`
- **Template A labels:**
  - Standard footer option: "Standard (service area or services, plus a contact column)".
  - Heavy-duty footer note: "Use Heavy-duty with Style 3 for heavy-duty or equipment-transport pages."
  - Variant help text: "Changes the hero, trust bar and section layout. Style 3 pairs with the Heavy-duty footer (Footer tab)."
- **Header tracking code label:** "Header tracking code (call tracking, pixels, tags)".
- **Client Phone (pending the phone decision):** "Number shown and dialed on this page (e.g. a call-tracking number). Leave empty to use the client's Contact Phone."
- **Export denied:** `wp_die( esc_html__( 'You need administrator access to export landing pages.', 'landing-page-manager' ) )`
- **Vendor missing:** "Landing Page Manager is missing its bundled libraries (the vendor folder). Delete the plugin and reinstall it from landing-page-manager.zip on the GitHub Releases page."
- **Get Pro, until there's a URL (pending decision):** "Pro analytics is coming soon — contact Duppins Technology for early access."

## README replacements _(support, marketing, legal)_
- **Opening line:** "Launch fast, client-branded landing pages on subdomains for every service, keyword and city. Built for agencies running paid search and local-service campaigns. Not a page builder."
- **Client taxonomy:** "One client per brand, with its logo, colors, address, phone and website. The client's slug becomes its subdomain."
- **Templates:** "One site-wide template (Template B, Template C, Template A), each with its own fields. Chosen in Settings → Templates."
- **Analytics:** "**Analytics** (Pro): page views, conversions, link clicks, engagement, heatmaps and rage clicks. Without Pro, this screen shows an upgrade prompt." This matches the code today. Revisit it after the analytics decision.
- **Install:** "Download `landing-page-manager.zip` from the latest GitHub Release and upload it under Plugins → Add New → Upload Plugin. `composer install` is only needed for development."
- **Clients:** "Add a client with its logo, address, phone and website (all required). Colors, Place ID and coordinates are filled in automatically on save."
- **Subdomains (new section):** "Each landing page is served at `https://<client-slug>.<your-site-domain>/<page-slug>/`. You need a wildcard DNS record (`*.your-site-domain`) pointing to this site and a wildcard SSL certificate. If pages 404 after setup, go to Settings → Permalinks and click Save."
- **External services (new section):** for each service, say what it receives and when, and link to its terms and privacy policy:
  - **Google Maps, Places and Geocoding:** the client address and place ID (admin), and the visitor's IP (front end).
  - **Brandfetch:** the client domain (admin).
  - **GitHub API:** the update check.
  - **Google Fonts, cdnjs and jsDelivr:** the visitor's IP, until the assets are served locally.
- **Author:** "Odell Duppins / Duppins Technology (https://duppinstech.com)". Also add: "See CREDITS.md for bundled third-party components."

## Plugin header _(marketing, legal, ops)_
```
 * Description: Launch fast, client-branded landing pages on subdomains for every service, keyword and city.
 * Author: Odell Duppins / Duppins Technology
 * Author URI: https://duppinstech.com
 * Requires at least: 5.9
 * Requires PHP: 7.4
```
`Update URI:` is pending Odell's decision. It's an updater change.

## Privacy policy text, for `wp_add_privacy_policy_content` _(legal)_
> Landing pages on this site load fonts and scripts from Google Fonts (Google LLC), cdnjs (Cloudflare, Inc.) and jsDelivr, and may display a Google Map. These providers receive your IP address and browser details. When analytics are enabled, this site records page views and clicks on links and call buttons, and may record time on page, scroll depth, aggregate mouse position, repeated clicks and form interactions. This data is stored on this website. [Operator: add retention period.]

Once assets are served locally, drop the CDN sentence.

## CREDITS.md contents _(legal)_
| Component | License |
|---|---|
| Carbon Fields | GPL-2.0-only |
| carbon-field-password | GPL-2.0+ |
| league/color-extractor | MIT |
| Bootstrap 4.3.1, 4.5.0, 5.3.3 | MIT |
| Popper | MIT |
| jQuery 3.7.1 (misnamed `jquery-migrate-new.min.js`) | MIT |
| jQuery Migrate 1.4.1 | MIT |
| jQuery UI 1.12.1 | MIT |
| Swiper 5.3.0 and 6.0.2 | MIT |
| Animate.css | MIT |
| WOW.js 1.1.2 (do not upgrade; use the `graingert/WOW` fork) | MIT |
| payform | MIT |
| Browser.php (Chris Schuld) | GPL-2.0+ |
| Font Awesome Free | icons CC BY 4.0, fonts OFL, code MIT |
| Chart.js | MIT |

Add this note to CREDITS.md: "Carbon Fields is GPL-2.0-only, so the distributed package is GPL-2.0. Do not add GPL-3.0-only or Apache-2.0 code."
Restore the payform header: `/*! payform | (c) 2014 Stripe (jquery.payment), (c) 2015 Jonathan D. Johnson | MIT */`

## Reviews notice (pending the attorney review) _(legal)_
> Showing selected 4- and 5-star reviews from Google. See all reviews on Google Maps.

## FAQ and support macros _(support)_
- **Can each client use a different template?** Not currently. One template applies to every landing page (Settings → Templates). The answer depends on the template decision.
- **I changed the template and my pages are blank.** Each template has its own fields. Your old content is still saved, and switching back restores it.
- **Where is Template A's Style variant?** Edit the page, open the "Template A" box, and go to the Layout tab. It only appears when Template A is the active template.
- **My landing page 404s or shows an SSL error.** Check four things:
  1. The page has a client assigned.
  2. A wildcard DNS record and a wildcard SSL certificate cover `*.your-domain`.
  3. The URL is `https://<client-slug>.<your-domain>/<page-slug>/`.
  4. You've re-saved Settings → Permalinks.
- **My uploaded template disappeared after an update.** Upload the .zip again and keep a copy. A permanent fix is planned.

## Cost math, using illustrative traffic and Google list prices _(finance)_
- **Place Details with no `fields` limit:** about $25 per 1,000 calls. At 50k uncached views a month that's about $1,157/month. With a 24-hour cache per place (20 clients) it's about $0, within the free tier.
- **Maps JS:** $7 per 1,000 loads after 10k free loads a month per billing account. At 50k loads a month that's about $280/month. The Maps Embed API is free.
- **Client save:** at most about $0.049 per save.
- **Brandfetch:** unknown until the plan is recorded.

## Manual smoke test for the WIP _(ops)_
Run this on a staging site with WP 5.9 and PHP 7.4, and `WP_DEBUG` + `WP_DEBUG_LOG` on.

1. Install the v1.0.6 zip and set the global template to Template A.
2. Create two clients. Give one of them a per-client template value.
3. Create Template A pages saved as style_1, style_2 and style_3, plus a page on a client subdomain.
4. Build the branch zip (`bin/build-zip.sh`) and upload it with "Replace current with uploaded".
5. Check that the debug log is clean and that the Landing Pages list, the editor, Clients, all four Settings tabs and the Dashboard load without JS console errors.
6. On the client screen, check that the template field is gone and that the other client fields still save.
7. Open each Template A page in the editor:
   - The Layout tab shows the saved style.
   - The core box no longer has Style Variant.
   - A new page publishes with style_1.
8. On the front end, each subdomain page renders its own variant.
9. Switch to Template B. The Template A box disappears, and pages render with no fatal error or 404.
10. Switch back to Template A and check that every page kept its style.
11. On the main domain, check that a normal page whose slug matches a landing page slug is still served.
12. Click Plugins → "Check again". There should be no fatal error. Time the page load.
13. Reinstall the v1.0.6 zip. The client template field shows its old value, and all pages render.
14. Delete the plugin on a throwaway copy:
    - `lpmanager_*` options are gone.
    - `lpmanager_pro_*` options and the content remain.
    - Permalinks still resolve.
