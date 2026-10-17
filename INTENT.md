# INTENT.md — why this plugin exists

## Problem
Agencies running paid search and local-service campaigns need many landing pages
per client — one per service, keyword, or city — each on a client-branded subdomain,
each fast and free of the host theme's bloat. Building these one-off in a page
builder is slow, inconsistent, and hard to measure.

## Intent
Landing Page Manager turns a single WordPress install into a **multi-client landing
page platform**:

1. **One content model** — a `landing_page` post type organized by `client` and `keyword`.
2. **Client identity once, reused everywhere** — logo, colors (via Brandfetch), domain
   and phone set on the client term. Core uses one site-wide template; choosing a
   template per client is a Pro feature (core exposes `lpmanager_template_for_post`).
3. **Templates, not page building** — vetted, conversion-focused templates
   with template-specific fields: two starter templates ship with the plugin, and custom
   templates are installed in `wp-content/lpmanager-templates/`.
   Templates are reusable Duppins Technology products, not client-exclusive work.
4. **Clean front end** — landing pages bypass the active theme and strip known
   page-builder assets so they load fast.
5. **Measurable (Pro)** — analytics are a Pro feature: page views, conversions,
   engagement, heatmaps, rage clicks and form analytics. Core collects nothing.
6. **Self-updating** — sites update from GitHub releases without manual uploads; the
   free core moves to WordPress.org once it is listed there (Pro stays the paid add-on).

## Users
- **Operator** (agency admin): creates clients, picks templates, publishes pages, reads analytics.
- **Visitor**: lands from an ad, must convert quickly on any device.
- **Developer** (Odell / contractors): adds templates and features without breaking live sites.

## Success looks like
- A new client landing page goes live in minutes, not days.
- Pages score well on Core Web Vitals on mobile.
- Zero hardcoded secrets; every state-changing request is nonce- and capability-checked.
- Releases are reproducible and roll out safely through the updater.

## Non-goals
- Not a general page builder or theme.
- No Pro/analytics logic in this free/core repo.
- Not multisite-specific (works on single site with subdomain rewrites).

## Ownership
Product of Odell Duppins / Duppins Technology. Bundled templates are reusable
Duppins Technology products; clients pay for customization, and source ownership
stays with Duppins Technology (see [OWNERSHIP.md](OWNERSHIP.md)).
