# Landing Page Manager

Manage multi-client landing pages with subdomain routing, templates, and optional conversion tracking (with Pro).

## Description

Landing Page Manager is a WordPress plugin for creating and managing landing pages across multiple clients. It provides:

- **Landing Page post type** — Dedicated content type for landing pages
- **Client taxonomy** — One client per brand, with its logo, colors, address, phone and website. The client's slug becomes its subdomain.
- **Keyword taxonomy** — Tag pages by keyword/campaign
- **Templates** — One site-wide template (Whiterail AI, Whiterail Recruits, QuickTow), each with its own fields, chosen in Settings → Templates
- **Dashboard** — Overview, quick stats, and links to Clients, Keywords, and Analytics
- **Analytics** (Pro) — Page views, conversions, link clicks, engagement, heatmaps, rage clicks. Without Pro, this screen shows an upgrade prompt.
- **Settings** — Templates, API keys (Google Maps browser key, Google Maps server key, Brandfetch), and (with Pro) conversion-tracking toggles

API keys are stored in **Settings** and are never hardcoded. Google and Brandfetch bill usage to the account that owns each key, so set quotas and budget alerts. All sensitive options are saved via the plugin’s settings UI only.

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
- **Clients** — Add clients; optionally set logo, colors, and domain. The landing page template is chosen globally in **Settings → Templates**.
- **Keywords** — Add keyword/campaign terms for organizing pages.
- **Dashboard** — View counts and quick links.
- **Analytics** (Pro) — View page views, conversions, engagement, heatmaps, rage clicks.
- **Settings** — Templates tab: choose default template. API Keys tab: Google Maps browser key (restrict it to your site and `https://*.yourdomain/*` for client subdomains), Google Maps server key (restrict it by IP; it needs the original Places API), Brandfetch API key, additional scripts. Conversion Tracking tab (Pro): enable/disable tracking options. **Updates tab**: shows where updates come from (GitHub Releases). Uploaded templates are stored inside the plugin folder and are deleted by plugin updates; keep the .zip and upload it again after each update.

## Updates from GitHub

Updates come only from this repository's [GitHub Releases](https://github.com/duppinstech/landing-page-manager/releases). The plugin header sets `Update URI: https://github.com/duppinstech/landing-page-manager`, so WordPress never asks WordPress.org about this plugin, and a different plugin published there under the same name can't be offered as an update. The plugin checks the [GitHub releases API](https://api.github.com/repos/duppinstech/landing-page-manager/releases/latest) and caches the latest release for 6 hours (30 minutes after a failed check). It installs the `landing-page-manager.zip` asset attached to the release, and falls back to GitHub's source zip (with development files excluded) if the asset is missing.

Recommended release flow:

1. Bump `Version` in `landing-page-manager.php` and update `CHANGELOG.md`.
2. Commit and push to `main`.
3. Push a tag `vX.Y.Z` matching the version (e.g. `git tag v1.0.7 && git push origin v1.0.7`).
4. The **Release** GitHub Action lints, checks the tag matches `Version`, builds `landing-page-manager.zip` (top-level folder `landing-page-manager/`, dev files excluded via `.distignore`), and creates the GitHub Release with the zip attached.
5. On WordPress, open **Plugins** and click **Check for updates** under Landing Page Manager. (Dashboard → Updates → Check Again can show a result up to 6 hours old.)

To build the zip locally: `bin/build-zip.sh` (uses `zip`, or Python if `zip` is missing). The zip is not committed.

## Uninstalling

Deleting the plugin removes its settings. Landing pages, clients and keywords are kept. To delete those too, add `define( 'LPMANAGER_UNINSTALL_DELETE_CONTENT', true );` to `wp-config.php` before deleting.

## Developer hooks

- `lpmanager_load_jquery` (filter, `bool $load, int $post_id`): return `true` to load jQuery on landing pages whose template doesn't need it. Operators can do the same per client with the **Tracking tags need jQuery** box.
- `lpmanager_unwanted_styles`, `lpmanager_unwanted_style_patterns`, `lpmanager_unwanted_scripts` (filters): styles and scripts removed from landing pages.
- `lpmanager_template_for_post` (filter, `string $slug, int $post_id`): the template a landing page uses. Defaults to the site-wide template; an unknown slug falls back to it.
- `lpmanager_iframe_hosts` (filter, `string[]`): hosts whose iframes are allowed in rich-text template fields. Default: YouTube, Vimeo and Google Maps.
- `lpmanager_landing_page_footer` (action): output in the landing page footer. Additional Scripts print here.
- `lpmanager_allowed_footer_callbacks` (filter, `string[]`): other plugins' `wp_footer` callbacks to keep on landing pages, which otherwise drop them. Use `'function_name'` or `'Class_Name::method'`.

## Development

- Run `composer install` to install dependencies.
- The Pro plugin (`landing-page-manager-pro`) is ignored by this repo’s `.gitignore`; manage it in a separate repository.

## License

GPL-2.0-or-later. See [LICENSE](LICENSE) for details.

## Author

Odell Duppins
