## Setup order

Complete the hosting/DNS prerequisite first so client subdomains can reach WordPress. Then:

1. In **Plugins → Add Plugin**, upload the ZIP and activate Landing Page Manager. Screenshot: [install.jpg](screenshots/admin/install.jpg).
2. Open **Landing Pages → Clients** and create the client record. Screenshot: [clients.jpg](screenshots/admin/clients.jpg).
3. Complete the client record's branding and contact details in the client editor. Screenshot: [client-editor.jpg](screenshots/admin/client-editor.jpg).
4. Open **Landing Pages → Keywords** and add any campaign labels. Screenshot: [keywords.jpg](screenshots/admin/keywords.jpg).
5. Open **Landing Pages → Settings → Templates** and choose the site-wide design. Screenshot: [templates.jpg](screenshots/admin/templates.jpg).
6. If needed, enter Google Maps or Brandfetch keys under **Landing Pages → Settings → API Keys**. Screenshot: [api-keys.jpg](screenshots/admin/api-keys.jpg).
7. Open **Landing Pages → Landing Pages**, choose **Add New Landing Page**, and complete the shared and template fields. Screenshots: [landing-pages.jpg](screenshots/admin/landing-pages.jpg) and [landing-page-editor.jpg](screenshots/admin/landing-page-editor.jpg).
8. In **Landing Pages → Landing Pages**, open the generated **Landing Page URL** and test it on the configured subdomain. Screenshot: [landing-pages.jpg](screenshots/admin/landing-pages.jpg).

## Screen index

| Screen | Screenshot filename | What the owner does there |
| --- | --- | --- |
| Plugins → Add Plugin | `install.jpg` | Upload the plugin ZIP and install it. |
| WordPress Dashboard → Home | `dashboard-widgets.jpg` | Open the Landing Page Manager dashboard or start a Landing Page. |
| Landing Pages → Dashboard | — | Review the plugin overview and quick links. |
| Landing Pages → Dashboard → Help | `help-button.jpg` | Open contextual help and follow the public guide link. |
| Landing Pages → Clients | `clients.jpg` | Add or manage client records. |
| Client editor | `client-editor.jpg` | Review the client's name and URL slug before setting its detail tabs. |
| Client editor: Branding & Identity | — | Set Client Logo, Primary Color, and Secondary Color. |
| Client editor: Contact Info | — | Set Contact Address, Contact Phone, and Client Domain. |
| Client editor: Tracking & Integration | — | Set Header tracking code and whether tracking tags need jQuery. |
| Client editor: Social Media | — | Add profile URLs by platform. |
| Landing Pages → Keywords | `keywords.jpg` | Add and manage page keywords. |
| Landing Pages list | `landing-pages.jpg` | Open an editor, inspect the generated URL, or use Export CSV. |
| Landing Page editor | `landing-page-editor.jpg` | Set title, Client Name, optional Page phone, Keyword, and template-specific fields. |
| Landing Pages → Analytics | `analytics.jpg` | See the Analytics screen; core shows the Get Pro prompt. |
| Landing Pages → Tracking | `tracking.jpg` | See the Tracking screen; core shows the Get Pro prompt. |
| Settings → Templates | `templates.jpg` | Choose the global template or upload a template archive. |
| Settings → API Keys | `api-keys.jpg` | Configure optional Google Maps and Brandfetch keys and Additional Scripts. |
| Settings → Updates | `updates.jpg` | Read the GitHub update-source information. |

## Imported data

The plugin does not import landing-page CSV data and does not download such data. **Export CSV** on the Landing Pages list is export-only, so there are no CSV import columns or valid import row to provide. Settings → Templates can install an owner-supplied template `.zip`; the plugin does not fetch that archive from a template marketplace.
