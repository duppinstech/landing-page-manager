## Hero

# Landing Page Manager

Manage landing pages for multiple clients from one WordPress site. Assign each page to a client and keyword, choose one site-wide template, and publish a generated HTTPS link using the client's slug as its subdomain; DNS and server routing still need to be configured for your site.

Product page: https://github.com/duppinstech/landing-page-manager

## How an owner sets it up

Before setup, configure DNS and hosting so the client subdomains reach this WordPress site. After installing and activating Landing Page Manager:

1. In **Plugins → Add Plugin**, upload the plugin ZIP and activate Landing Page Manager. [Screenshot: Install](screenshots/admin/install.jpg)
2. In **Landing Pages → Clients**, add the client record. [Screenshot: Clients](screenshots/admin/clients.jpg)
3. In the client editor, complete the branding and contact fields, including **Client Domain** and **Contact Phone**. [Screenshot: Client editor](screenshots/admin/client-editor.jpg)
4. In **Landing Pages → Keywords**, add the campaign or topic labels you plan to use. [Screenshot: Keywords](screenshots/admin/keywords.jpg)
5. In **Landing Pages → Settings → Templates**, choose the template used across the site. [Screenshot: Templates](screenshots/admin/templates.jpg)
6. If the template needs maps, reviews, or Brandfetch colors, enter the relevant keys in **Landing Pages → Settings → API Keys**. [Screenshot: API Keys](screenshots/admin/api-keys.jpg)
7. Open **Landing Pages → Landing Pages**, then choose **Add New Landing Page**. [Screenshot: Landing Pages](screenshots/admin/landing-pages.jpg)
8. In the editor, set the title and **Client Name**, optionally set **Page phone (optional)** and **Keyword**, complete the selected template's fields, and publish. [Screenshot: Landing Page editor](screenshots/admin/landing-page-editor.jpg)

## How it works

1. A site owner creates a Client record. Its WordPress slug is used as the client subdomain in generated page links.
2. The owner creates a Landing Page, assigns a client and optionally a keyword, and fills the fields for the active template.
3. The selected template is global: it is the one template used by Landing Pages on this site.
4. When the page is published, the plugin generates an HTTPS URL from the client slug, the site's domain, and the page slug.
5. The web host and DNS must route that subdomain to WordPress; the plugin does not provision DNS or server settings.

## What you get

- A dedicated **Landing Pages** post type with an overview dashboard, a page list, and CSV export.
- **Clients** and **Keywords** taxonomies for organizing pages.
- Three bundled templates: Template A, Template B, and Template C, selected site-wide in Settings → Templates.
- A page-level **Page phone (optional)** field that overrides the client's **Contact Phone** for that page.
- Optional Google Maps, Google reviews, and Brandfetch color lookups when their keys are configured. Third-party usage is billed to the account that owns each key.
- GitHub release updates. The **Updates** tab explains the update source; update checks are started from the WordPress Plugins screen.
- A Pro analytics menu entry, with analytics and conversion tracking marked as coming soon in the core interface.

## Plans

| Plan | Availability | Price | Checkout |
| --- | --- | --- | --- |
| Landing Page Manager core | Available under GPL-2.0-or-later | No price is listed in the repository | No checkout URL is published |
| Landing Page Manager Pro | Analytics add-on is marked “coming soon”; early access is offered | No public price is listed | No checkout URL is published |

The core code does not enforce plan-based limits for the number of landing pages, clients, or keywords. The Pro button is an early-access contact link, not a checkout link.

## Short FAQ

**Does the plugin configure subdomain DNS?** No. Configure DNS and the web server separately so requests reach WordPress.

**Can each client use a different template?** Not in core. The template selected in Settings → Templates is site-wide.

**Does core include analytics?** No. The Analytics screen and Conversion Tracking (Pro) prompt identify those features as Pro and coming soon.

**Does it import landing-page CSV files?** No. The Landing Pages list has an **Export CSV** action; there is no CSV import screen.

**Where do support requests go?** Email support@duppinstech.com or call (240) 397-8221. The plugin's **Contact for early access** button is for Pro enquiries and points to https://duppinstech.com/.
