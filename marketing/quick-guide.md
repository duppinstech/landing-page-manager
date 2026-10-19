Landing Page Manager manages multi-client landing pages in WordPress with client and keyword taxonomies, one site-wide template, and generated subdomain links. DNS and web-server routing are configured separately.

## Screens that matter first

1. **Landing Pages → Clients** — create the client and set its identity, contact details, domain, and optional integrations.
2. **Landing Pages → Keywords** — add optional campaign or topic labels.
3. **Landing Pages → Settings → Templates** — choose Template A, Template B, or Template C for the whole site.
4. **Landing Pages → Settings → API Keys** — enter keys only for integrations the active template/site uses.
5. **Landing Pages → Landing Pages → Add New Landing Page** — select the client and keyword, fill the template fields, then publish.

## Fields and shortcodes

Core does not register a shortcode or template-token syntax. The shared Landing Page fields are **Client Name**, **Page phone (optional)**, and **Keyword**. A page phone overrides that client's **Contact Phone**.

Template fields vary. Template A has **Style variant**, **Header & branding**, **Hero**, **Trust bar & strips**, **Services grid**, **Service area**, and other section tabs. Template B uses **Hero**, **Trust Bar**, **Opening**, **Reviews**, **Icon Boxes**, **Service Area**, **Roads & Highways**, **FAQ**, and **Closing**. Template C uses **Hero**, **Company**, **Benefits**, **More**, and **Apply Now**.

## After publish

Confirm the page is published, has the intended client assigned, and displays the expected generated HTTPS URL in the Landing Pages list. Open that URL and check the rendered page, phone link, images, and any map or review integration. The domain's DNS and server must direct the subdomain to this WordPress site.

## When a run fails

The plugin has no logs screen. Check the site's PHP or web-server logs; check `wp-content/debug.log` only if WordPress debug logging is configured. Contact support@duppinstech.com or (240) 397-8221 after reviewing the logs. The plugin's **Contact for early access** link points to https://duppinstech.com/ and is for Pro enquiries.
