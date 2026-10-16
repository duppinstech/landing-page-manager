# Changelog

All notable changes to this project are documented here.
Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) · Versioning: [SemVer](https://semver.org/).

## [Unreleased]
### Upgrade notes
- **Before you update: uploaded templates are deleted.** If you uploaded a template under Landing Pages → Settings → Templates, keep its .zip. Updating removes it, so upload it again straight afterwards. Until you do, landing pages show a 404 and an admin notice says so.
- **Update promptly.** Versions 1.0.6 and earlier also check WordPress.org for updates. Sites set to "WordPress.org only" in 1.0.6 won't be offered this update: download `landing-page-manager.zip` from the GitHub release and upload it under Plugins → Add New → Upload Plugin.
- **Google keys (optional, recommended):** add a **Google Maps server key** under Settings → API Keys, restricted to your server's IP. Then restrict the browser key to your site and its client subdomains (`https://example.com/*`, `https://*.example.com/*`). Save the server key **before** restricting the browser key, or client address lookups and Fetch Reviews stop working. If you ever roll back to 1.0.6, lift the browser key's restriction first, because 1.0.6 doesn't use the server key.
- **Template A:** JSON-LD that isn't valid JSON is no longer output. Check yours with Google's Rich Results Test.
- **Uploaded templates that used Swiper or a datepicker provided by the plugin** must now include their own copy.
- **Template A: jQuery no longer loads by default.** If a tag on your Template A pages uses jQuery or `$`, for example a Custom HTML tag in Google Tag Manager, tick the new **Tracking tags need jQuery** box on that client (Clients → Tracking & Integration) before or right after updating. jQuery also still loads when the client's Header Tracking Code mentions jQuery, calls `$(` or `$.`, or contains a Google Tag Manager container snippet (`gtm.js` / `GTM-…`); the Google Ads/Analytics gtag.js snippet alone doesn't count. Developers can force it with the `lpmanager_load_jquery` filter.
- **New warning about the Google server key.** Sites with a browser key but no server key will see a warning on the plugin's screens. Add a server key under Settings → API Keys, then restrict the browser key.
- **Purge your page cache after updating**, so visitors get the new pages (with the map that loads on scroll).
- No settings re-save, permalink flush or database step is needed.

### Security
- **Updates now come only from GitHub Releases.** The plugin declares an `Update URI`, so WordPress no longer asks WordPress.org about it, and a different plugin published there under the same name can't be offered as an update. The "Plugin update source" setting is gone. The latest release is cached for 6 hours; "Check for updates" on the Plugins screen checks immediately. (Dashboard → Updates → Check Again can show a result up to 6 hours old.)
- **Landing page templates escape what they print**, so text entered by Contributors and Authors can't inject code. Template C headings and short text keep simple formatting (`<br>`, `<strong>`, `<em>`, `<span>`); other tags show as text. Client and company colors must be hex values such as `#1a2b3c`, otherwise the template default is used.
- **CSV export is protected.** The Export CSV link carries a security token, so a bookmarked export link now says it has expired; use the button on the Landing Pages list. Cells that start with `=`, `+`, `-` or `@` get a leading apostrophe so spreadsheet apps show them as text instead of running them as formulas.
- The editor's "Fetch Reviews" request requires permission to edit that landing page.

### Added
- **Separate Google Maps server key** (Settings → API Keys). Server-side lookups (client geocoding and Fetch Reviews) use it, so the browser key that maps need can be restricted to your site and its subdomains. If it's empty, the browser key is used as before.
- Suggested privacy-policy text for landing pages under **Settings → Privacy → Policy Guide**.
- An admin notice on the plugin's screens when a Google Maps browser key is set but no server key. Without a server key, address lookups and Fetch Reviews use the browser key, and restricting that key to your sites breaks them.
- An admin notice, with a link to Settings, when the active template is missing (for example, an uploaded template removed by an update), which makes landing pages 404.
- The plugin declares its requirements (WordPress 5.9, PHP 7.4), so WordPress won't install it on older hosts.

### Changed
- **The Template B footer map loads only when a visitor scrolls near it.** Google bills each Maps JavaScript load, and many visitors never scroll to the footer. The map keeps its space on the page, so nothing shifts when it appears, and its info window opens without taking keyboard focus. The "Map unavailable" and "No client assigned" messages are now shown only to logged-in editors.
- **jQuery loads only for templates that use it.** Template A pages no longer load it, unless the client's Header Tracking Code mentions jQuery, calls `$(` or `$.`, or contains a Google Tag Manager container snippet, or when the client's new **Tracking tags need jQuery** box is ticked. Uploaded templates still get it. New filter: `lpmanager_load_jquery`.
- **Template B loads the review slider only on pages with reviews**, and uses the minified Swiper build. That saves about 300 KB (uncompressed) on pages without reviews and about 160 KB on pages with them. Pages without reviews no longer show an empty "Reviews" heading.
- **Headlines and call buttons appear immediately** on Template A and Template C instead of fading in after scripts load. Visitors whose device is set to reduce motion get no scroll-in animations and no autoplaying sliders. The Template B review slider pauses on hover and stops on keyboard focus.
- **Landing pages load only the CSS and JavaScript their template needs.** Unused and duplicate libraries are no longer loaded: a second copy of jQuery, Bootstrap 5 alongside Bootstrap 4, jQuery Migrate 1.x, jQuery UI, payform, Popper and Swiper where unused, and a CDN copy of Swiper. Templates list their files in `lib/assets.php`. Uploaded templates without that file still load every file in their own `css/` and `js/` folders.
- Landing pages no longer load the reviews script, which requested Google reviews (a billed call) on every view by a logged-in user and wasn't shown by any template. Reviews are requested only by the editor's "Fetch Reviews" button, which asks Google for just the reviews and rating, allows one fetch per client every 10 minutes (no review content is cached, as Google's Places terms require), and explains failures (for example a refused key or an unknown Place ID).
- **Export CSV follows the Client and Keyword filters** you applied, and is shown only to administrators.
- The template upload card warns that plugin updates delete uploaded templates.
- **Template A "Style variant" has moved** from the "Landing Page Details" box to the new **Layout** tab of the "Template A" box. Saved styles are kept. The field only appears when Template A is the active template.
- Template A's Style 3 label no longer names a client.

### Removed
- **The "Landing Page Template" field is gone from the client screen.** It has been ignored since 1.0.6. Every landing page uses the template chosen in **Landing Pages → Settings → Templates**. Values already saved on clients stay in the database but are not used.

### Fixed
- **One phone number per page, everywhere.** A page's own **Client Phone** (for example a call-tracking number) now overrides the client's **Contact Phone** in every template, for both the number shown and the number dialled. Before, Template B showed the page number but dialled the client number in places, and Template A ignored the page number when the client had one. The page field is now optional; a page only needs a number if its client has none.
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
- Client B maps fall back to classic markers when no Map ID is set, and marker clicks work with both marker types.
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

[Unreleased]: https://github.com/duppinstech/landing-page-manager/compare/v1.0.6...HEAD
[1.0.6]: https://github.com/duppinstech/landing-page-manager/compare/v1.0.5...v1.0.6
[1.0.5]: https://github.com/duppinstech/landing-page-manager/releases/tag/v1.0.5
[1.0.2]: https://github.com/duppinstech/landing-page-manager/releases/tag/v1.0.2
[1.0.1]: https://github.com/duppinstech/landing-page-manager/releases/tag/v1.0.1
