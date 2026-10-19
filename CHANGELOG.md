# Changelog

All notable changes to this project are documented here.
Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) · Versioning: [SemVer](https://semver.org/).

## [Unreleased]
### Fixed
- **A new landing page no longer opens with "Missing required fields".** The warning appeared on the blank Add New form, before anything was typed. It still appears when you save or publish a page with required fields missing, and an incomplete page still stays a draft when you publish it.

### Changed
- The copyright and author lines in the plugin's files and `LICENSE` name Duppins Technology.

## [1.0.14] - 2026-10-17
### Fixed
- **"Update now" works again.** Since 1.0.6, every update failed with "A directory could not be read" and the site stayed on its old version: the updater deleted the downloaded update because its folder was already named `landing-page-manager` (as every release zip is). If a downloaded update can't be moved into place, the update now stops with an error instead of installing into a misnamed folder.

### Upgrade notes
- **Sites on 1.0.13 or earlier: install 1.0.14 once by hand.** Download **`landing-page-manager.zip`** from the [1.0.14 release](https://github.com/duppinstech/landing-page-manager/releases/tag/v1.0.14) (not "Source code (zip)"), then go to **Plugins → Add New Plugin → Upload Plugin**, choose the file, and select **Replace current with uploaded**. Later updates install normally.
- This also brings the 1.0.13 changes, including its security fix, to sites that couldn't update to 1.0.13.

## [1.0.13] - 2026-10-17
### Added
- **Tracking menu item** under Landing Pages. With Landing Page Manager Pro 1.0.6 or later it holds Pro's tracking settings (what is tracked, whose visits are counted, consent). Without Pro it explains how to get conversion tracking.
- For developers: `lpmanager_pro_screen_slots()` names the Analytics and Tracking screens, and the `lpmanager_render_analytics_page` and `lpmanager_render_tracking_page` actions render them. Anything hooking these actions must check the user's capabilities itself.

### Changed
- **Analytics come from Landing Page Manager Pro.** The Analytics screen's dashboard (summary, charts and report tabs) now ships with Pro 1.0.6, which also adds a detail view for each landing page. Without Pro, Analytics explains how to get it, with a Get Pro button. With a Pro version before 1.0.6, Analytics and Tracking say where those features are until Pro is updated.

### Removed
- The **Conversion Tracking (Pro)** Settings tab, replaced by the Tracking menu item. (Sites running Pro before 1.0.4 still see their tracking options there.)
- The analytics dashboard's script and styles, which now ship with Pro, including an unversioned copy of Chart.js loaded from a CDN.
- The Pro banner on the other Landing Pages screens now says **Get Pro**, like the Analytics and Tracking screens, instead of "coming soon, contact for early access". It no longer appears on Analytics and Tracking, which carry their own Pro message.

### Security
- Removing the analytics dashboard script also removes a cross-site scripting issue in it: a landing page title containing quotes, written by a user who can publish landing pages, could run script on an administrator's Analytics screen. Pro 1.0.6 ships the dashboard with the fix.

### Upgrade notes
- **Update Landing Page Manager Pro to 1.0.6 at the same time.** With Pro 1.0.5 or earlier, the Analytics screen no longer shows charts: Analytics and Tracking say where Pro keeps those features until Pro is updated.

## [1.0.12] - 2026-10-17
### Removed
- The one-time step from 1.0.10 that saved the previous default template for sites that had never saved the template setting. Every site that needed it has run it. A site updating straight from 1.0.9 or earlier without having saved the setting starts on Starter One.

## [1.0.11] - 2026-10-17
### Removed
- **Template A, Template B and Template C no longer ship with the plugin.** They are client templates and are now maintained separately. Sites that ran 1.0.10 already have copies in `wp-content/lpmanager-templates/`, which this update doesn't touch.
- The privacy-policy guide no longer mentions the Template C template's images.

### Upgrade notes
- **Install 1.0.10 before this version.** 1.0.10 copies those templates out of the plugin folder and won't update while a copy is missing. A site on 1.0.9 or earlier that installs this version directly loses them, and its landing pages show a 404. To recover, download the 1.0.10 ZIP from GitHub Releases, upload it under Plugins → Add New → Upload Plugin (replace the current version), open any admin page, check that no template notice appears, then update again. Templates you uploaded yourself aren't in the 1.0.10 ZIP; upload them again.
- Sites that never saved the template setting stay on Template B, so its folder must be in `wp-content/lpmanager-templates/`.

## [1.0.10] - 2026-10-17
### Added
- **Two starter templates, Starter One (light) and Starter Two (dark).** They ship with the plugin, appear first under Settings → Templates, and are the default for new sites. Each has a hero with a call button, highlights, about, FAQ and contact sections, and uses the client's logo, colors and phone number, with text colors chosen so any brand color stays readable. They load no external fonts, icons or scripts. Copy one to build your own template.

### Changed
- **Uploaded and custom templates are kept outside the plugin folder**, in `wp-content/lpmanager-templates/`, so plugin updates no longer delete them. You can also copy a template folder there yourself.
- **Templates still inside the plugin folder are copied there automatically** (Template A, Template B, Template C, and any template you uploaded earlier), shortly after updating or when an administrator next opens an admin page. While any template is still only inside the plugin folder, an admin notice names it and **updating the plugin is blocked**, including uploading a plugin ZIP over it, so the next update can't delete it.
- The template tiles list the starter templates first, then other templates by name, then **Upload New Template**.
- The notice for a missing template now says where to put it, and the in-plugin Help no longer says that updates remove uploaded templates.
- The suggested privacy-policy text now says that *some* landing page designs load Google Fonts and cdnjs: the starter templates don't.

### Upgrade notes
- **Template A, Template B and Template C will be removed from the plugin in the next release.** This update copies them to `wp-content/lpmanager-templates/`, and your landing pages keep using them. Update to this version before any later one, open Landing Pages → Settings, and check that no template notice appears.
- Sites that never saved the template setting stay on Template B, the previous default. New sites start with Starter One.
- Back-ups and staging-to-live copies must now include `wp-content/lpmanager-templates/`. Uninstalling the plugin keeps that folder.
- **Sites that deploy the plugin without the WordPress updater** (`DISALLOW_FILE_MODS`, Composer or Git): copy every template folder except `starter-one` and `starter-two` (including any template you uploaded) from the plugin's `includes/templates/` to `wp-content/lpmanager-templates/` yourself before deploying the next version. The admin notice reminds you.
- If the copy can't run (for example, WordPress can't write to `wp-content`), automatic updates of this plugin fail, and WordPress emails a failure notice each time it retries, until the copy succeeds or you copy the folders yourself. Your pages keep working meanwhile.
- Rolling back to 1.0.9 keeps the client templates working, but 1.0.9 can't see `wp-content/lpmanager-templates/` or the starter templates: pages that use a starter or a template uploaded in 1.0.10 show a 404 until you update again.

## [1.0.9] - 2026-10-17
### Added
- **Settings, Help, Sponsor and Support links on the Plugins screen.** Settings and Help sit beside Deactivate; Sponsor follows the author, and Support (email) ends that line.

### Changed
- **Dashboard → Updates → Check again now finds a new release right away.** It skips the plugin's 6-hour release cache.
- The Plugins screen shows the author as Duppins Technology.

### Removed
- **The "Check for updates" link on the Plugins screen.** Use Dashboard → Updates → Check again instead.

## [1.0.8] - 2026-10-16
### Added
- **Help button on the plugin's admin screens.** A round **Help** button at the bottom right of the Dashboard, Landing Pages, Clients, Keywords, Analytics and each Settings tab opens short notes about that screen and a link to its section of the user guide. It doesn't appear on Posts, Pages or other screens. Developers can change the guide address with the `lpmanager_user_guide_url` filter.

## [1.0.7] - 2026-10-16
### Upgrade notes
- **Before you update: uploaded templates are deleted.** If you uploaded a template under Landing Pages → Settings → Templates, keep its .zip. Updating removes it, so upload it again straight afterwards. Until you do, landing pages show a 404 and an admin notice says so.
- **Update promptly.** Versions 1.0.6 and earlier also check WordPress.org for updates. Sites set to "WordPress.org only" in 1.0.6 won't be offered this update: download `landing-page-manager.zip` from the GitHub release and upload it under Plugins → Add New → Upload Plugin.
- **Google keys (optional, recommended):** add a **Google Maps server key** under Settings → API Keys, restricted to your server's IP. Then restrict the browser key to your site and its client subdomains (`https://example.com/*`, `https://*.example.com/*`). Save the server key **before** restricting the browser key, or client address lookups and Fetch Reviews stop working. If you ever roll back to 1.0.6, lift the browser key's restriction first, because 1.0.6 doesn't use the server key.
- **Template A:** JSON-LD that isn't valid JSON is no longer output. Check yours with Google's Rich Results Test.
- **Uploaded templates that used Swiper or a datepicker provided by the plugin, or that loaded payform, the Square payment-form CSS, jQuery Migrate, Bootstrap 4 or 5, the jQuery UI CSS, Popper or Swiper from the built-in Client B template folders,** must now include their own copy.
- **Template A: jQuery no longer loads by default.** If a tag on your Template A pages uses jQuery or `$`, for example a Custom HTML tag in Google Tag Manager, tick the new **Tracking tags need jQuery** box on that client (Clients → Tracking & Integration) before or right after updating. jQuery also still loads when the client's Header Tracking Code mentions jQuery, calls `$(` or `$.`, or contains a Google Tag Manager container snippet (`gtm.js` / `GTM-…`); the Google Ads/Analytics gtag.js snippet alone doesn't count. Developers can force it with the `lpmanager_load_jquery` filter.
- **New warning about the Google server key.** Sites with a browser key but no server key will see a warning on the plugin's screens. Add a server key under Settings → API Keys, then restrict the browser key.
- **Purge your page cache after updating**, so visitors get the new pages (with the map that loads on scroll).
- **Review each client's Header Tracking Code once after updating.** From now on only users allowed to post unfiltered HTML (administrators and editors on a single site, super admins on multisite) can change it, but code saved earlier by other users is still printed.
- Script approval relies on the security keys and salts in `wp-config.php`. If those salts change, or the site is cloned or migrated to a host with different salts, Additional Scripts stop printing until an administrator approves them again. A notice on the plugin's screens and the Dashboard offers this.
- **Phone numbers can change on live pages.** A page's own phone (now labelled "Page phone (optional)") overrides the client's Contact Phone in every template, both the number shown and the number dialled. Template A used to prefer the client's number. Before updating, check pages whose page phone differs from the client's Contact Phone, and clear the page field wherever the client's number should be used. (A developer can list those pages with smoke step 26 in `meeting-notes/2026-10-16-1034-owner-decisions-review-appendix.md`.)
- **Template A pages change to each client's colors.** Template A now uses the client's Primary and Secondary colors, which are often filled in automatically from the logo, so most Template A pages will look different after updating. Preview your Template A clients on staging. To keep the old look, set Primary to `#002868` and Secondary to `#b22234` on that client.
- **Additional Scripts start printing once an administrator approves them.** They never printed before. Before you click Approve (the notice appears on the plugin's screens), remove old tags and any tag that is already in a client's Header Tracking Code, or conversions are counted twice.
- **Embeds in rich-text fields are filtered.** In Template B's services intro and Reviews field, and in Template C's "More" content, only YouTube, Vimeo and Google Maps embeds are kept, and only on WordPress 6.2 or later. Script-based review widgets and iframes from other sites are removed, including code that 1.0.6 printed unfiltered. Check these pages after updating.
- **Custom CSS against Template A class names must be updated.** Client-named class names, handles and the layout stylesheet were renamed to neutral names: `layout1`, `layout2` and `layout-3` (the mix of `layout2` and `layout-3` is intentional), with the stylesheet now `style-2-layout.css`. Ask Duppins Technology (support@duppinstech.com) for the full old-to-new list.
- **Sites that set `DISALLOW_UNFILTERED_HTML`:** nobody, administrators included, can then save or approve Additional Scripts or change a client's Header Tracking Code. Header tracking code that is already saved keeps printing. Additional Scripts therefore never print on these sites, and no approval notice appears. Have a developer print the tags with the `lpmanager_landing_page_footer` action instead.
- **If you roll back to 1.0.6,** Additional Scripts stop printing, and Template B pages that rely only on the client's phone lose their call buttons. Custom CSS already moved to the new `--layout1`, `--layout2` and `--layout-3` class names stops matching, and Template A goes back to its built-in navy and red. Purge the page cache again.
- Apart from approving Additional Scripts, no settings re-save, permalink flush or database step is needed.

### Security
- **Only trusted users can change code that landing pages print raw.** "Additional Scripts" and each client's "Header Tracking Code" can now only be changed by users allowed to post unfiltered HTML (administrators and editors on a single site, super admins on multisite), whether through the settings screens or the REST API. Other users see the fields read-only, and their changes are discarded with a notice. Additional Scripts also print only after an administrator saves or approves them.
- **Updates now come only from GitHub Releases.** The plugin declares an `Update URI`, so WordPress no longer asks WordPress.org about it, and a different plugin published there under the same name can't be offered as an update. The "Plugin update source" setting is gone. The latest release is cached for 6 hours; "Check for updates" on the Plugins screen checks immediately. (Dashboard → Updates → Check Again can show a result up to 6 hours old.)
- **Landing page templates escape what they print**, so text entered by Contributors and Authors can't inject code. Template C headings and short text keep simple formatting (`<br>`, `<strong>`, `<em>`, `<span>`); other tags show as text. Client and company colors must be hex values such as `#1a2b3c`, otherwise the template default is used.
- **CSV export is protected.** The Export CSV link carries a security token, so a bookmarked export link now says it has expired; use the button on the Landing Pages list. Cells that start with `=`, `+`, `-` or `@` get a leading apostrophe so spreadsheet apps show them as text instead of running them as formulas.
- The editor's "Fetch Reviews" request requires permission to edit that landing page.

### Added
- Developer filter `lpmanager_template_for_post` to choose a landing page's template (core still uses one site-wide template; per-client templates come with Pro).
- **Separate Google Maps server key** (Settings → API Keys). Server-side lookups (client geocoding and Fetch Reviews) use it, so the browser key that maps need can be restricted to your site and its subdomains. If it's empty, the browser key is used as before.
- Suggested privacy-policy text for landing pages under **Settings → Privacy → Policy Guide**.
- An admin notice on the plugin's screens when a Google Maps browser key is set but no server key (each admin can hide it for 30 days). Without a server key, address lookups and Fetch Reviews use the browser key, and restricting that key to your sites breaks them.
- An admin notice, with a link to Settings, when the active template is missing (for example, an uploaded template removed by an update), which makes landing pages 404.
- The plugin declares its requirements (WordPress 5.9, PHP 7.4), so WordPress won't install it on older hosts.

### Changed
- Pages kept as a draft because required fields are missing now say "Draft updated" instead of "Post published", and the missing fields are listed by name (Client Name, Keyword, Phone).
- **Embeds fit any screen.** Video and map embeds keep percentage sizes such as `100%`, never get wider than the page, keep their shape on phones, and always have a title for screen readers ("YouTube video", "Vimeo video" or "Google map" when none was given).
- **Template A keeps text readable on any client color.** Navy areas are darkened enough for their lighter text, stat labels are larger and brighter, and the mobile call button has a 44px tap target.
- **Approving Additional Scripts is clearer.** The notice shows the pending code, appears on the plugin's screens and the WordPress Dashboard to administrators, and confirms once the scripts are live.
- **For early-access Pro users:** when Pro 1.0.4 or later is active, the tracking toggles live on Pro's Settings screen, and the free plugin's Conversion Tracking tab is hidden. The stored settings are unchanged, so nothing needs re-saving.
- **Template A no longer exposes client names** in its CSS classes, stylesheet and file names or admin labels. Styles are now named by number (for example the `--layout2` body class modifier and `style-2-layout.css`). Pages keep their saved style. If you wrote custom CSS against the old Template A class names, update it.
- **Video and map embeds are kept** in Template B's services intro and Reviews fields and in Template C's "More" content, from YouTube, Vimeo and Google Maps embed links (https only, WordPress 6.2 or later). Iframes from other sites, and styling or permissions on embeds, are removed. Developers can add providers with the `lpmanager_iframe_src_patterns` filter.
- **Incomplete landing pages stay drafts.** Publishing a page that's missing required fields now saves it as a draft and says why. Editing a page that's already live never unpublishes it.
- **Template A uses the client's colors.** The client's primary color replaces Template A's navy, and the secondary color replaces its red, on all three styles. Darker and lighter shades are derived from them, and a color too light for white text is darkened until white text on it reaches at least 4.5:1 contrast. Clients that use the default red and navy look the same as before.
- Template A's mobile call button shows a phone icon instead of three "menu" bars, because it dials the phone.
- The "Get Pro" prompts now say Pro analytics is coming soon and link to Duppins Technology for early access, instead of a dead link.
- Template C: the footer copyright line shows the year and the client's (or site's) name instead of "Template C" on every client's pages.
- Uninstall also removes the leftover per-client template setting from the removed field.
- **The Template B footer map loads only when a visitor scrolls near it.** Google bills each Maps JavaScript load, and many visitors never scroll to the footer. The map keeps its space on the page, so nothing shifts when it appears, and its info window opens without taking keyboard focus. The "Map unavailable" and "No client assigned" messages are now shown only to logged-in editors.
- **jQuery loads only for templates that use it.** Template A pages no longer load it, unless Additional Scripts or the client's Header Tracking Code mention jQuery, calls `$(` or `$.`, or contains a Google Tag Manager container snippet, or when the client's new **Tracking tags need jQuery** box is ticked. Uploaded templates still get it. New filter: `lpmanager_load_jquery`.
- **Template B loads the review slider only on pages with reviews**, and uses the minified Swiper build. That saves about 300 KB (uncompressed) on pages without reviews and about 160 KB on pages with them. Pages without reviews no longer show an empty "Reviews" heading.
- **Headlines and call buttons appear immediately** on Template A and Template C instead of fading in after scripts load. Visitors whose device is set to reduce motion get no scroll-in animations and no autoplaying sliders. The Template B review slider pauses on hover and stops on keyboard focus.
- **Landing pages load only the CSS and JavaScript their template needs.** Unused and duplicate libraries are no longer loaded: a second copy of jQuery, Bootstrap 5 alongside Bootstrap 4, jQuery Migrate 1.x, jQuery UI, payform, Popper and Swiper where unused, and a CDN copy of Swiper. Templates list their files in `lib/assets.php`. Uploaded templates without that file still load every file in their own `css/` and `js/` folders.
- Landing pages no longer load the reviews script, which requested Google reviews (a billed call) on every view by a logged-in user and wasn't shown by any template. Reviews are requested only by the editor's "Fetch Reviews" button, which asks Google for just the reviews and rating, allows one fetch per client every 10 minutes (the plugin no longer keeps its own copy of Google's response; the reviews you fetch are inserted into the page's Reviews field), and explains failures (for example a refused key or an unknown Place ID).
- **Export CSV follows the Client and Keyword filters** you applied, and is shown only to administrators.
- The template upload card warns that plugin updates delete uploaded templates.
- **Template A "Style variant" has moved** from the "Landing Page Details" box to the new **Layout** tab of the "Template A" box. Saved styles are kept. The field only appears when Template A is the active template.
- Template A's Style 3 label no longer names a client.

### Removed
- Unused bundled libraries that no template loads any more: payform, jQuery Migrate (both copies), Bootstrap 5 (`bootstrap-new.js`), the jQuery UI stylesheets and `sqpaymentform.css` from Template B and Template C, plus Popper, Bootstrap JS, Swiper and the unused `functions.php` from Template C. The release zip is smaller.
- **The "Landing Page Template" field is gone from the client screen.** It has been ignored since 1.0.6. Every landing page uses the template chosen in **Landing Pages → Settings → Templates**. Values already saved on clients stay in the database but are not used.

### Fixed
- **Additional Scripts now work.** Scripts saved under Settings → API Keys → Additional Scripts were stored but never printed. They now print at the bottom of every landing page, but only once an administrator (a super admin on multisite) has saved or approved them. **If you saved scripts there before, open Landing Pages → Settings as an administrator: a notice shows the scripts aren't approved yet, with an Approve button.**
- The `lpmanager_landing_page_footer` action now fires on landing pages. A new `lpmanager_allowed_footer_callbacks` filter keeps chosen `wp_footer` output from other plugins, which landing pages otherwise drop.
- **One phone number per page, everywhere.** A page's own **Page phone** (formerly "Client Phone"; for example a call-tracking number) now overrides the client's **Contact Phone** in every template, for both the number shown and the number dialled. Before, Template B showed the page number but dialled the client number in places, and Template A ignored the page number when the client had one. The page field is now optional; a page only needs a number if its client has none.
- The Template C mobile menu button now opens a menu: the client's landing pages under "Services" (the current page is marked), then About Us, Benefits and More About. It closes when you tap a link or press Escape. Before, the button did nothing.
- Template C: the desktop "Services" dropdown is now a button that opens on click or keyboard (Escape closes it) and announces whether it's open; it no longer jumps to the top of the page. On both desktop and mobile, "Services" only appears when the client has more than one published landing page, instead of showing "No client assigned" or "No landing pages found" to visitors. The template no longer writes debug lines to the PHP error log on every page view.
- Template C: at exactly 921px wide, neither the desktop nor the mobile header showed. One of them now always shows.
- CI and the release workflow now fail when a PHP file has a syntax error. Before, the lint step always passed.
- GitHub's source zip no longer includes development files, in case a release ever lacks its zip asset.
- Landing page list hooks (filters, Export CSV button, dashboard widget) were registered twice.

## [1.0.6] - 2026-10-14
### Security
- The updater only installs a release asset named exactly `landing-page-manager.zip` or `landing-page-manager-<version>.zip`. Before, any zip with `landing-page-manager` in its name matched, including a Pro zip.
- Template zip upload now requires its own nonce and the `install_plugins` capability. It pre-scans archives for unsafe paths and for `.htaccess`/`.phar` files, and installs through a staging folder with a single atomic rename. Cleanup also runs after fatal errors. Previously the upload had no CSRF protection and could leave extracted PHP in `uploads/`. That old folder is now removed once on upgrade.
### Fixed
- The first save of a new client now geocodes the address and extracts brand colors. These ran before Carbon Fields had saved the client's fields.
- Landing pages no longer show a false "session expired" dialog.
- Template B and Template C maps fall back to classic markers when no Map ID is set, and marker clicks work with both marker types.
- Template upload from **Settings → Templates** works. The JS and PHP handler had drifted apart, so every upload failed.
### Changed
- **The template is now chosen only by the global setting** (Settings → Templates). The per-client and per-page template choices are ignored.
- The required-field check covers client, keyword and client phone, plus each template's registered fields. Notices are escaped, and debug logging is removed.
- Landing pages no longer have the content editor. All content comes from fields.
- Template CSS and JS are versioned by file modification time, so browsers pick up changes immediately.
- Analytics heatmap previews render at a 1366px desktop width, scaled to fit, so the click grid lines up with the page.
- Template B: the review slider autoplays, the FAQ accordion is full-width, and hover colors are fixed.
- The repository moved to `duppinstech/landing-page-manager`. The updater now checks releases there.
- Per-post Elementor CSS (`elementor-post-{ID}`) is now dequeued on landing pages by pattern instead of hardcoded IDs. The new filter `lpmanager_unwanted_style_patterns` controls the patterns.
- The release zip is no longer committed. The `Release` GitHub Action builds it and attaches it when a `v*` tag is pushed (`bin/build-zip.sh`, `.distignore`).
### Added
- **Template A template** for towing and roadside services, with three layout styles (Style variant).
- `uninstall.php`: removes plugin settings and transients. Landing pages and terms are kept unless `LPMANAGER_UNINSTALL_DELETE_CONTENT` is defined as true.
- CI: PHP lint on PHP 7.4, 8.1 and 8.3 for every push and PR.
- SDLC and Claude Code project scaffolding: `CLAUDE.md`, `AGENTS.md`, `INTENT.md`, `PLAN.md`, `OWNERSHIP.md`, `CONTRIBUTING.md`, `SECURITY.md`, GitHub PR and issue templates, and the `.claude/` rules, commands, skills, agents, hooks, memory, and workflows.

## [1.0.5] - 2026-03-09
### Fixed
- Subdomain AJAX requests.
- GitHub update installs now always unpack to the `landing-page-manager/` folder.
### Changed
- Overhauled the core UX of the Template B template.

## [1.0.2] - 2026-03-09
### Added
- Manual "check for updates" action on the plugin row.

## [1.0.1] - 2026-03-09
### Added
- Selectable update source: GitHub, WordPress.org, or both.

## [1.0.0]
- Initial public release.

[Unreleased]: https://github.com/duppinstech/landing-page-manager/compare/v1.0.14...HEAD
[1.0.14]: https://github.com/duppinstech/landing-page-manager/compare/v1.0.13...v1.0.14
[1.0.13]: https://github.com/duppinstech/landing-page-manager/compare/v1.0.12...v1.0.13
[1.0.12]: https://github.com/duppinstech/landing-page-manager/compare/v1.0.11...v1.0.12
[1.0.11]: https://github.com/duppinstech/landing-page-manager/compare/v1.0.10...v1.0.11
[1.0.10]: https://github.com/duppinstech/landing-page-manager/compare/v1.0.9...v1.0.10
[1.0.9]: https://github.com/duppinstech/landing-page-manager/compare/v1.0.8...v1.0.9
[1.0.8]: https://github.com/duppinstech/landing-page-manager/compare/v1.0.7...v1.0.8
[1.0.7]: https://github.com/duppinstech/landing-page-manager/compare/v1.0.6...v1.0.7
[1.0.6]: https://github.com/duppinstech/landing-page-manager/compare/v1.0.5...v1.0.6
[1.0.5]: https://github.com/duppinstech/landing-page-manager/releases/tag/v1.0.5
[1.0.2]: https://github.com/duppinstech/landing-page-manager/releases/tag/v1.0.2
[1.0.1]: https://github.com/duppinstech/landing-page-manager/releases/tag/v1.0.1
