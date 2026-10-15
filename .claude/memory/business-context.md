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
| Core (free, GPL) | CPT, clients and keywords, templates, subdomain routing, basic views and conversions, GitHub updater | _TBD_ | GitHub Releases (WordPress.org planned, PLAN.md P2) |
| Pro add-on | Engagement, heatmaps, rage clicks, form analytics (separate private repo) | _TBD_ | _TBD_ |
| Client templates | Custom templates built *for* a client and licensed to them (`whiterail-*`, `quicktow`) | _TBD (custom quote?)_ | Shipped in core |

- **Licensing model for Pro:** _TBD (per site? per agency? annual?)_
- **What triggers an upgrade:** _TBD_

## Cost drivers
- **Google Maps Platform** (Places, Geocoding, Maps JS): billed per request to **the operator's own API key**, set in Settings → API Keys. _Confirm this applies to every client._
- **Brandfetch:** called when a client is saved. Key set in Settings. _Plan / limits TBD._
- **GitHub:** release hosting and the updater API. Free for a public repo, but rate limits apply to update checks.
- **Support time:** _TBD (hours per client per month?)_
