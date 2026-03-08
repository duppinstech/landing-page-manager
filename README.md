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
- Optional: [Landing Page Manager Pro](https://github.com/oduppins/landing-page-manager-pro) for analytics and conversion tracking

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
- **Settings** — Templates tab: choose default template. API Keys tab: Google Maps API key, Brandfetch API key, additional scripts. Conversion Tracking tab (Pro): enable/disable tracking options.

## Development

- Run `composer install` to install dependencies.
- The Pro plugin (`landing-page-manager-pro`) is ignored by this repo’s `.gitignore`; manage it in a separate repository.

## License

GPL-2.0-or-later. See [LICENSE](LICENSE) for details.

## Author

Odell Duppins
