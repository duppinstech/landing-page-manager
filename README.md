# Landing Page Manager

Manage multi-client landing pages with subdomain routing, templates, and optional conversion tracking (with Pro).

## Description

Landing Page Manager is a WordPress plugin for creating and managing landing pages across multiple clients. It provides:

- **Landing Page post type** — Dedicated content type for landing pages
- **Client taxonomy** — Organize pages by client (with optional branding, domain, and template per client)
- **Keyword taxonomy** — Tag pages by keyword/campaign
- **Templates** — Switchable templates (e.g. template-b, template-c) with template-specific fields
- **Dashboard** — Overview, quick stats, and links to Clients, Keywords, and Analytics
- **Analytics** — Page views, conversions, link clicks, and (with Pro) engagement, heatmaps, rage clicks, and more
- **Settings** — Templates, API keys (Google Maps, Brandfetch), and (with Pro) conversion-tracking toggles

API keys (Google Maps, Brandfetch) are stored in **Settings** and are never hardcoded. All sensitive options are saved via the plugin’s settings UI only.

## Requirements

- WordPress 5.9+
- PHP 7.4+
- [Composer](https://getcomposer.org/) (for development)
- Optional: [Landing Page Manager Pro](https://github.com/duppinstech/landing-page-manager-pro) for analytics and conversion tracking

## Installation

1. Clone or download this repository into `wp-content/plugins/landing-page-manager`.
2. From the plugin directory, run `composer install` (if you have Composer) to install Carbon Fields and other dependencies.
3. In WordPress admin, go to **Plugins** and activate **Landing Page Manager**.
4. Go to **Landing Pages → Settings** to set the default template and (optionally) API keys.

If you use the Pro add-on, install and activate it in a separate plugin directory (e.g. `wp-content/plugins/landing-page-manager-pro`). Pro is maintained in its own repository.

## Usage

- **Landing Pages** — Add and edit landing pages; assign Client and Keyword terms.
- **Clients** — Add clients; optionally set logo, colors, domain, and template per client.
- **Keywords** — Add keyword/campaign terms for organizing pages.
- **Dashboard** — View counts and quick links.
- **Analytics** — View page views, conversions, and (with Pro) engagement, heatmaps, rage clicks.
- **Settings** — Templates tab: choose default template. API Keys tab: Google Maps API key, Brandfetch API key, additional scripts. Conversion Tracking tab (Pro): enable/disable tracking options. **Updates tab**: choose where to check for plugin updates—WordPress.org only, GitHub only, or both (use newest version). You can switch to “WordPress.org only” after publishing to the plugin directory, or leave “Both” to get the latest from either source.

## Updates from GitHub

In **Settings → Updates** you can set the plugin update source to **GitHub only** or **GitHub and WordPress.org (use newest version)**. When GitHub is used, the plugin checks the [GitHub releases API](https://api.github.com/repos/duppinstech/landing-page-manager/releases/latest) for a new version. Create a release with a tag (e.g. `1.0.1`) to trigger an update. For a reliable update zip, you can upload a correctly structured zip (root folder `landing-page-manager`) as a release asset and use the filter `lpmanager_github_update_package_url` to point to it; otherwise the default zipball from GitHub is used.

Recommended release flow:

1. Bump `Version` in `landing-page-manager.php` and update `CHANGELOG.md`.
2. Commit and push to `main`.
3. Push a tag `vX.Y.Z` matching the version (e.g. `git tag v1.0.6 && git push origin v1.0.6`).
4. The **Release** GitHub Action lints, checks the tag matches `Version`, builds `landing-page-manager.zip` (top-level folder `landing-page-manager/`, dev files excluded via `.distignore`), and creates the GitHub Release with the zip attached.
5. On WordPress, click **Dashboard → Updates → Check Again**.

To build the zip locally: `bin/build-zip.sh` (uses `zip`, or Python if `zip` is missing). The zip is not committed.

## Uninstalling

Deleting the plugin removes its settings. Landing pages, clients and keywords are kept. To delete those too, add `define( 'LPMANAGER_UNINSTALL_DELETE_CONTENT', true );` to `wp-config.php` before deleting.

## Development

- Run `composer install` to install dependencies.
- The Pro plugin (`landing-page-manager-pro`) is ignored by this repo’s `.gitignore`; manage it in a separate repository.

## License

GPL-2.0-or-later. See [LICENSE](LICENSE) for details.

## Author

Odell Duppins
