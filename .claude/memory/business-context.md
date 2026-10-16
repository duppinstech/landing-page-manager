# Business context

_Shared and committed, and the repo is **public**. Keep only public-safe facts here:
published tiers and list prices, positioning, and cost drivers. Private numbers (costs,
margins, client rates, revenue) go in the "Business (private)" section of the
gitignored `CLAUDE.local.md`._

Read by `finance-reviewer`, `marketing-reviewer`, `legal-reviewer`, and `product-reviewer`.
Items marked _TBD_ are open questions for Odell. Agents must not fill them in by guessing.

## Product and positioning
- **Product:** Landing Page Manager, by Duppins Technology (https://duppinstech.com).
- **Audience:** agencies running paid search and local-service campaigns (see INTENT.md).
- **Positioning:** multi-client, client-branded landing pages on subdomains, fast and template-driven, not a page builder.

## Tiers
| Tier | What's included | Price | Distribution |
|---|---|---|---|
| Core (free, GPL) | CPT, clients and keywords, one site-wide template, subdomain routing, GitHub updater. No analytics (Odell, 10-16). | _TBD_ | GitHub Releases (WordPress.org planned, PLAN.md P2) |
| Pro add-on | All analytics (views, conversions, engagement, heatmaps, rage clicks, form analytics) and per-client template choice (separate private repo) | _TBD_ | _TBD_ |
| Templates | Reusable Duppins products shipped in core (`whiterail-*`, `quicktow`). QuickTow is anonymized in shipped code (its `.html` mockups, in git but not shipped, still carry client names; PLAN: move mockups); the Whiterail names are an open question (DR 10-16c); clients pay for customization (Odell, 10-16) | _TBD (customization quote)_ | Shipped in core |

- **Licensing model for Pro:** _TBD (per site? per agency? annual?)_
- **What triggers an upgrade:** _TBD_

## Cost drivers
- **Google Maps Platform** (Places, Geocoding, Maps JS): billed per request to **the operator's own API key**, set in Settings → API Keys. Decided 10-16: clients supply their own Google and Brandfetch keys. Caveat: keys are per install, not per client, so this holds only with one install per client unless a per-client key override is added (open question for Odell, DR 10-16c).
- **Brandfetch:** called when a client is saved. Key set in Settings. _Plan / limits TBD._
- **GitHub:** release hosting and the updater API. Free for a public repo, but rate limits apply to update checks.
- **Support time:** _TBD (hours per client per month?)_
