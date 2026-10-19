# Landing Page Manager

Manage multi-client landing pages with subdomain routing, templates, and optional conversion tracking (with Pro).

## Description

Landing Page Manager is a WordPress plugin for creating and managing landing pages across multiple clients. It provides:

- **Landing Page post type** — Dedicated content type for landing pages
- **Client taxonomy** — One client per brand, with its logo, colors, address, phone and website. The client's slug becomes its subdomain. A landing page's own "Page phone" overrides the client's phone on that page.
- **Keyword taxonomy** — Tag pages by keyword/campaign
- **Templates** — Pick the site's template in Settings → Templates; each template brings its own page fields. Starter One (light) and Starter Two (dark) ship with the plugin, and you can add your own in `wp-content/lpmanager-templates/`, where plugin updates never remove them (see [Custom templates](#custom-templates))
- **Dashboard** — Overview, quick stats, and links to Clients, Keywords, and Analytics
- **Analytics** (Pro) — Page views, conversions, click tracking, heatmaps, rage clicks and form analytics, with a detail view for each landing page. Landing Page Manager Pro 1.0.6+ fills this screen; without Pro it shows how to get Pro.
- **Tracking** (Pro) — What is tracked and whose visits are counted. Landing Page Manager Pro 1.0.6+ fills this screen; without Pro it shows how to get conversion tracking.
- **Settings** — Templates and API keys (Google Maps browser key, Google Maps server key, Brandfetch). With Pro before 1.0.4, also its conversion-tracking toggles

API keys are stored in **Settings** and are never hardcoded. Google and Brandfetch bill usage to the account that owns each key, so set quotas and budget alerts. All sensitive options are saved via the plugin’s settings UI only.

## Requirements

- WordPress 5.9+
- PHP 7.4+
- [Composer](https://getcomposer.org/) (for development)
- Optional: [Landing Page Manager Pro](https://duppinstech.com/) for analytics and conversion tracking.

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
- **Analytics** (Pro) — View page views, conversions, engagement, heatmaps, rage clicks; under By Page, select a page for its detail view.
- **Tracking** (Pro) — Choose what is tracked and whose visits are counted.
- **Settings** — Templates tab: choose default template. API Keys tab: Google Maps browser key (restrict it to your site and `https://*.yourdomain/*` for client subdomains), Google Maps server key (restrict it by IP; it needs the original Places API), Brandfetch API key, and Additional Scripts (printed at the bottom of every landing page; only administrators can change them, and they print only after an administrator saves or approves them). **Updates tab**: shows where updates come from (GitHub Releases). Uploaded templates are stored in `wp-content/lpmanager-templates/`, which plugin updates don't touch.

## Custom templates

Landing Page Manager looks for templates in two places:

- **Starter templates** (Starter One and Starter Two) ship inside the plugin, in `includes/templates/`. Their folder names (`starter-one`, `starter-two`) are reserved. Updates replace them, so don't edit them in place.
- **Custom templates** live in `wp-content/lpmanager-templates/<slug>/`. Plugin updates replace the plugin folder but never touch this folder, so templates kept here survive updates.

To add a custom template, either upload a .zip under **Settings → Templates → Upload New Template**, or copy the template folder into `wp-content/lpmanager-templates/` yourself (SFTP, your host's file manager, or a deploy from a private repository). A template folder needs:

- `single-landing_page.php` with a `Template Name:` header (and optionally `Description:`),
- `screenshot.png` (1200×900 works well) for its tile,
- optionally `lib/fields/landing-page-fields.php` (its Carbon Fields), `lib/assets.php` (its stylesheets and scripts), and `404.php`.

By default the plugin also loads Google Fonts and Font Awesome from their CDNs on every landing page. A template that doesn't use them should return `'cdn_assets' => false` from `lib/assets.php`, as the starters do, so visitors' browsers don't contact those services.

The easiest start is to copy `wp-content/plugins/landing-page-manager/includes/templates/starter-one/` to `wp-content/lpmanager-templates/my-template/`, then change the `Template Name` header in `single-landing_page.php` and the field box title in `lib/fields/landing-page-fields.php`. The starter templates are licensed under GPL-2.0-or-later, like the plugin. A template built from one is a derivative work; if you distribute it, you must license it under the GPL, version 2 or later. In a template, use `\LPManager\templates\Template_Manager::get_template_url( 'css/file.css' )` for file URLs and `__DIR__` for file paths, never paths inside the plugin folder.

## Updates from GitHub

Updates come only from this repository's [GitHub Releases](https://github.com/duppinstech/landing-page-manager/releases). The plugin header sets `Update URI: https://github.com/duppinstech/landing-page-manager`, so WordPress never asks WordPress.org about this plugin, and a different plugin published there under the same name can't be offered as an update. The plugin checks the [GitHub releases API](https://api.github.com/repos/duppinstech/landing-page-manager/releases/latest) and caches the latest release for 6 hours (30 minutes after a failed check). It installs the `landing-page-manager.zip` asset attached to the release, and falls back to GitHub's source zip (with development files excluded) if the asset is missing.

Recommended release flow:

1. Bump `Version` in `landing-page-manager.php` and update `CHANGELOG.md`.
2. Commit and push to `main`.
3. Push a tag `vX.Y.Z` matching the version (e.g. `git tag v1.0.7 && git push origin v1.0.7`).
4. The **Release** GitHub Action lints, checks the tag matches `Version`, builds `landing-page-manager.zip` (top-level folder `landing-page-manager/`, dev files excluded via `.distignore`), and creates the GitHub Release with the zip attached.
5. On WordPress, open **Dashboard → Updates** and click **Check again**. That skips the plugin's 6-hour release cache, so the new version shows up straight away.

To build the zip locally: `bin/build-zip.sh` (uses `zip`, or Python if `zip` is missing). The zip is not committed.

## Uninstalling

Deleting the plugin removes its settings. Landing pages, clients and keywords are kept; to delete them too, add `define( 'LPMANAGER_UNINSTALL_DELETE_CONTENT', true );` to `wp-config.php` before deleting. Templates in `wp-content/lpmanager-templates/` are always kept, even with that setting; delete that folder yourself if you no longer need it.

## Developer hooks

- `lpmanager_load_jquery` (filter, `bool $load, int $post_id`): return `true` to load jQuery on landing pages whose template doesn't need it. It also loads automatically when Additional Scripts or a client's Header Tracking Code mention jQuery or a Google Tag Manager container. Operators can force it per client with the **Tracking tags need jQuery** box.
- `lpmanager_unwanted_styles`, `lpmanager_unwanted_style_patterns`, `lpmanager_unwanted_scripts` (filters): styles and scripts removed from landing pages.
- `lpmanager_template_for_post` (filter, `string $slug, int $post_id`): the template a landing page uses. Defaults to the site-wide template; an unknown slug falls back to it.
- `lpmanager_iframe_src_patterns` (filter, `string[]`): anchored PCRE patterns an iframe `src` must fully match to be kept in rich-text template fields. Defaults cover YouTube `/embed/`, Vimeo `/video/` and Google Maps `/maps/embed` (https only). Kept iframes are rebuilt with a fixed set of attributes.
- `lpmanager_landing_page_footer` (action): output in the landing page footer. Additional Scripts print here.
- `lpmanager_allowed_footer_callbacks` (filter, `string[]`): other plugins' `wp_footer` callbacks to keep on landing pages, which otherwise drop them. Use `'function_name'` or `'Class_Name::method'`.

## Development

- Run `composer install` to install dependencies.
- The Pro plugin (`landing-page-manager-pro`) is ignored by this repo’s `.gitignore`; manage it in a separate repository.

## License

GPL-2.0-or-later. See [LICENSE](LICENSE) for details.

## Author

Duppins Technology (https://duppinstech.com)
