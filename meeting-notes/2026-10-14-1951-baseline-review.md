# Department review: baseline (initial reports)
**When:** 2026-10-14 19:51 EDT · **Scope:** the whole plugin, `main` at v1.0.6 (00d196f) plus the uncommitted WIP on `chore/template-field-cleanup` (per-client template field removed, Template A Style Variant moved into the template) · **Chair:** main session · **Owner:** Odell Duppins

First meeting of the department reviewers. Each department reviewed the whole product, not just a diff, so the backlog would start from a full baseline.
Drafted text (CHANGELOG, help text, README, privacy text, credits): [appendix](2026-10-14-1951-baseline-review-appendix.md).

## Attendance and verdicts
| Department | Verdict | Findings | Summary |
|---|---|---|---|
| Security | fix-before-merge | 10 | 2 High: an update-source hijack risk (needs Odell) and unescaped template output. The WIP diff itself is safe. |
| Legal | fix-before-merge | 15 | Google reviews stored and filtered against the Places terms (Attorney review), no privacy-policy text, IP leaks to CDNs, client content in the public repo. WOW.js is MIT, so it's fine. |
| Finance | fix-before-merge | 12 | API costs land on the operator's key, but one public key plus uncached per-view Places calls create billing risk. Prices are undefined, so packaging comes first. |
| Product | fix-before-merge | 12 | Conversion tags never fire, call buttons dial the wrong number, and global-only templates conflict with INTENT. The WIP completes both P0 items. |
| Marketing | fix-before-merge | 11 | The docs promise free analytics that core doesn't have, "Get Pro" goes nowhere, and template previews are logos. |
| Support / docs | fix-before-merge | 12 | `[Unreleased]` is empty, README drift, uploaded templates are wiped on update, and no subdomain setup docs. |
| Ops / release | fix-before-merge | 8 | The CI lint gate can't fail. The WordPress.org slug is unclaimed and there's no `Update URI`. The release zip is clean. |
| UX | fix-before-merge | 15 | Every file in a template folder loads (about 1 MB, duplicate jQuery and Bootstrap), the hero is hidden until JS runs, and contrast fails. |

The verdicts cover the baseline. Every department said the WIP diff on its own is low-risk. It can merge once the CHANGELOG entry is added and the smoke test passes.

## Highlights
- **Four departments raised the same five issues** (phone numbers, Additional Scripts never printing, analytics claims, the dead Get Pro button, client names in public content). Those are the clearest signals.
- **Two security and release risks need Odell before the next release:** there's no `Update URI:` header, so a WordPress.org plugin using the unclaimed slug could push code to every site. And template output isn't escaped. The second is approved as P0; the first is an updater change, so it was forwarded.
- **The CI lint gate is broken:** a syntax error passes CI. Ops reproduced this. Approved as P0.
- **Google reviews are a legal and cost exposure:** permanent storage, 4–5★ filtering with no disclosure, missing attribution, and uncached billed calls. Forwarded for attorney review. The cost and hardening part is approved.
- **Revenue can't be assessed yet:** Pro has no price, URL, licensing unit or update channel. Forwarded.

## Findings and recommendations by department

### Security
- **No `Update URI` header, and the update source defaults to "both":** a WordPress.org plugin with the same slug could replace this one. → Add the header and hide the wp_org/both sources. **Outcome:** Forwarded → Odell (P0, updater change).
- **Template meta output is not escaped** (in all three templates). Contributors can edit landing-page fields. → Escape by context. **Outcome:** Approved → PLAN (P0).
- Client "Header Tracking Code" is editable without `unfiltered_html`. → Restrict capabilities. **Outcome:** Approved → PLAN (P1).
- CDN scripts are loaded unpinned and without SRI. → Pin versions or serve locally. **Outcome:** Approved → PLAN (P1, merged with the legal and UX local-assets item).
- `Reviews_Ajax` has no status check, caching, encoding or timeout. → Harden it before ever adding `nopriv`. **Outcome:** Approved → PLAN (P0, merged with finance).
- CSV export: formula injection, no nonce, hooks registered twice. **Outcome:** Approved → PLAN (P1).
- The subdomain isn't tied to the page's client, and the host check has no dot boundary. **Outcome:** Approved → PLAN (P1, merged with product).
- Brandfetch: domain not validated, empty key sent, no hex sanitizing. **Outcome:** Approved → PLAN (P1).
- API keys are plain text fields with plaintext `_previous` copies. **Outcome:** Approved → PLAN (P1, merged with UX).
- "Additional Scripts" is never printed, and `Landing_Page_Map_Manager` is broken. **Outcome:** Forwarded → Odell (wire up or remove), and the dead code was approved for removal.
- Minor: no size limit on template uploads (zip bomb). **Outcome:** Approved → PLAN (P2).

### Legal
- **Google reviews are stored permanently, filtered to 4–5★, and missing attribution.** → Render them live with attribution and a disclosure, or use a widget. **Outcome:** Forwarded → Odell (P0, Attorney review on the FTC rule).
- No `wp_add_privacy_policy_content`. **Outcome:** Approved → PLAN (P0).
- Visitor IPs are sent to Google Fonts, cdnjs and jsDelivr without consent. → Bundle these locally. **Outcome:** Approved → PLAN (P1).
- Pro behavioral trackers default to on, and there's no consent hook. **Outcome:** Forwarded → Odell (P1).
- Client-named mockups (names, phone numbers, "© client") are in the public repo. **Outcome:** Forwarded → Odell (P1).
- The zipball fallback would ship the mockups and `CLAUDE.md`. → Add `.gitattributes export-ignore`. **Outcome:** Approved → PLAN (P0).
- Template A admin labels name clients. **Outcome:** Approved → PLAN (P1, merged).
- Template C default images are hotlinked and include a car-care brand photo. **Outcome:** Forwarded → Odell (P1).
- Template C footer copyright is hardcoded, and the `client_g` text domain suggests the code came from another project. **Outcome:** Forwarded → Odell (P1).
- No `CREDITS.md`, and payform has lost its MIT header. **Outcome:** Approved → PLAN (P1).
- No README "External services" section. **Outcome:** Approved → PLAN (P1, in the README item).
- Coordinates are kept forever, but Google's terms allow at most 30 days. **Outcome:** Approved → PLAN (P1, merged with the finance geocode item).
- Unused `get_user_geo_data()` would send visitor IPs over HTTP. **Outcome:** Approved → PLAN (P1, dead-code item).
- Blockers to a WordPress.org listing (updater, CDNs). **Outcome:** Forwarded → Odell (P2, folded into WP.org readiness).
- Pro tracking code is in the public core repo. **Outcome:** Forwarded → Odell (part of the analytics decision). The Pro EULA needs attorney review.
- **WOW.js license:** v1.1.2 is MIT, so this was a false alarm. **Outcome:** Declined, no action. Never upgrade past 1.1.2. Removed from the legal-reviewer's list of items to recheck.

### Finance
- **One Google key is public and also used server-side for Places**, so operators leave it unrestricted, and it can be abused at $32 per 1,000 requests. → Split it into browser and server keys, and explain billing. **Outcome:** Approved → PLAN (P0).
- **Uncached Place Details on every view** (about $25 per 1,000 views if ever exposed to visitors). **Outcome:** Approved → PLAN (P0, merged with security).
- Maps JS loads on every view (about $7 per 1,000 after the free tier). → Use the Embed API or lazy-load it. **Outcome:** Approved → PLAN (P1, merged with UX).
- Who pays for API costs on installs Duppins runs. **Outcome:** Forwarded → Odell (P1).
- Analytics are promised in core but don't exist there. **Outcome:** Forwarded → Odell (P1, merged).
- Client templates ship as free GPL code: decide product vs. exclusive. **Outcome:** Forwarded → Odell (P1, merged).
- Per-client templates as a possible Pro upsell. **Outcome:** Forwarded → Odell (part of the P0 template decision).
- Pro has no price, unit or channel. **Outcome:** Forwarded → Odell (P1).
- Updater: no caching and the rate-limit risk. **Outcome:** Already tracked plus forwarded (bundled with the Update URI decision).
- Re-geocoding on every save, Brandfetch with no cache, dead per-view cost code. **Outcome:** Approved → PLAN (P1, merged).
- **Pricing recommendation:** revisit, starting with packaging. No prices exist yet, so the agent didn't propose any.

### Product
- **Global-only templates conflict with INTENT goal 2.** **Outcome:** Forwarded → Odell (P0).
- **Conversion tags never fire** (Additional Scripts, the footer hook, and the `wp_footer` strip). **Outcome:** Forwarded → Odell (P0). Product rated it auto, UX and security said Odell decides, so the more cautious call was taken.
- **Phone shown is not the phone dialed.** **Outcome:** Forwarded → Odell (P0, merged with UX and support).
- Cross-client subdomain access and slug collisions. **Outcome:** Approved → PLAN (P1, merged).
- Drafts can't be previewed on subdomains. **Outcome:** Approved → PLAN (P1).
- WIP is complete but has no CHANGELOG entry or decision record. **Outcome:** Approved → PLAN (P0). Both P0 items are marked `[~]`.
- Client names in Template A's rendered HTML, and its variant labels. **Outcome:** Approved → PLAN (P1, merged).
- Validator lists raw keys. **Outcome:** Approved → PLAN (P1). Keeping incomplete pages as drafts was forwarded (P2).
- Get Pro goes to `#`. **Outcome:** Forwarded → Odell (P1, merged).
- Analytics: core vs. Pro. **Outcome:** Forwarded → Odell (P1, merged).
- Client Domain label is misleading, and changing a client's slug breaks URLs silently. **Outcome:** Approved → PLAN (P1).
- Raise the accessibility and Core Web Vitals audit from P2 to P1. **Outcome:** Already tracked, reprioritized to P1.

### Marketing
- Free analytics claimed in four docs. **Outcome:** Forwarded → Odell. The README is corrected to "Analytics (Pro)" in the meantime (approved, P1).
- Get Pro dead links. **Outcome:** Forwarded → Odell (P1).
- Template previews are logos. **Outcome:** Approved → PLAN (P1).
- CHANGELOG oversells Template A (it lists 4 variants; there are 3). **Outcome:** Approved → PLAN (P0, in the CHANGELOG item).
- New installs default to a client-branded template, and a neutral "Starter" template was suggested. **Outcome:** Forwarded → Odell (P1, merged).
- Client names in Template A labels. **Outcome:** Approved → PLAN (P1, merged).
- README lacks a story and Template A. **Outcome:** Approved → PLAN (P1, README item).
- WordPress.org listing blockers. **Outcome:** Forwarded → Odell (P2).
- Thin plugin header. **Outcome:** Approved → PLAN (P1).
- "Nimbata" label. **Outcome:** Approved → PLAN (P1, merged into the tracking-code item).
- Naming and metadata inconsistencies. **Outcome:** Approved → PLAN (P2).
- **Story:** the Template A template for towing and roadside, plus safer uploads and updates. The release-note draft is in the appendix.

### Support / docs
- `[Unreleased]` is empty. **Outcome:** Approved → PLAN (P0).
- **Uploaded templates are deleted on update** (pages then 404). → Add an upload warning, an admin notice and an upgrade note. **Outcome:** Approved → PLAN (P0). The permanent fix is already tracked (P1).
- Templates tab doesn't say that switching blanks other templates' fields. **Outcome:** Approved → PLAN (P1).
- Phone field help text and behavior. **Outcome:** Forwarded → Odell (merged into the phone decision).
- Validator messages. **Outcome:** Approved → PLAN (P1, merged).
- README drift in seven places. **Outcome:** Approved → PLAN (P1).
- No subdomain setup docs, and the Client Domain label is misleading. **Outcome:** Approved → PLAN (P1, merged).
- Get Pro dead link. **Outcome:** Forwarded → Odell (merged).
- Template A labels and CHANGELOG variant count. **Outcome:** Approved → PLAN (merged).
- Google key help text doesn't list the APIs it needs. **Outcome:** Approved → PLAN (P0, merged into the key split).
- Core i18n gaps and the wrong text domain. **Outcome:** Already tracked (i18n pass), scope extended.
- Developer-facing vendor notice and missing upload spinner. **Outcome:** Approved → PLAN (P2, admin polish).
- CONTRIBUTING branch prefixes. **Outcome:** Approved → PLAN (P2).
- Correcting the published v1.0.6 release notes. **Outcome:** Forwarded → Odell (P2, external-facing).

### Ops / release
- **CI lint gate can't fail** (no `pipefail`). Reproduced. **Outcome:** Approved → PLAN (P0).
- WordPress.org slug unclaimed, and no `Update URI`. **Outcome:** Forwarded → Odell (P0, merged with security).
- GitHub API isn't cached, so update checks slow wp-admin and can hit rate limits. **Outcome:** Forwarded → Odell (bundled with the updater decision).
- No upgrade routine or rewrite flush, and a stray `/landing/` route. **Outcome:** Approved → PLAN (P2).
- `[Unreleased]` is empty. **Outcome:** Approved → PLAN (P0, merged).
- Orphaned `_lpmanager_client_template` meta. **Outcome:** Forwarded → Odell (P2). No migration is needed.
- Missing `Requires` headers. **Outcome:** Approved → PLAN (P1, plugin header item).
- Zip only checked at tag time. **Outcome:** Approved → PLAN (P2).
- **Confirmed fine:** lint is clean on all PHP, no syntax needs PHP 8, the WIP needs no migration, and the v1.0.6 zip layout is correct. The 14-step smoke test is in the appendix.

### UX
- **Every file in each template's `css/` and `js/` folders loads** (about 1 MB, two Bootstraps, a second jQuery, three Swipers). **Outcome:** Approved → PLAN (P0).
- **Phone shown is not the phone dialed.** **Outcome:** Forwarded → Odell (P0, merged).
- **Hero is hidden until JS runs, and reduced-motion is ignored.** **Outcome:** Approved → PLAN (P0).
- Additional Scripts setting and footer hook. **Outcome:** Forwarded → Odell (P0, merged).
- Contrast: the brightness cutoff isn't WCAG-based, the call button is hardcoded white, and Template A uses tiny low-contrast text. **Outcome:** Approved → PLAN (P1).
- Template C "Apply Now" is an image with empty alt text, hotlinked. **Outcome:** Alt text and empty-URL handling Approved → PLAN (P1). Hosting the image and turning it into an HTML button were Forwarded → Odell (image rights).
- Render-blocking fonts and CSS. **Outcome:** Approved → PLAN (P1, merged into local assets).
- Images have no dimensions, lazy loading or priority hints. **Outcome:** Approved → PLAN (P1).
- Maps loads eagerly and shows admin messages to the public. **Outcome:** Approved → PLAN (P1, merged).
- Link names and logo alt text. **Outcome:** Approved → PLAN (P1).
- Focus outlines removed, and FAQ state isn't exposed. **Outcome:** Approved → PLAN (P1).
- API-key masking may save asterisks over the real key. **Outcome:** Approved → PLAN (P1, merged). Needs testing on a WordPress site.
- Admin template cards and upload aren't keyboard-accessible. **Outcome:** Approved → PLAN (P2).
- Template A hamburger icon dials the phone. **Outcome:** Forwarded → Odell (P1, layout the client approved).
- Template A ignores client colors. **Outcome:** Forwarded → Odell (P1).

## Decisions
- **Approved into PLAN.md (36 new items after merging duplicates, plus 4 existing items updated):**
  - **10 P0:** CHANGELOG entry, CI pipefail, template escaping, explicit asset loading, hero without JS, privacy text, `.gitattributes`, Places cost and hardening, key split, uploaded-template warning.
  - **20 P1:** security hardening (6), cost and data (3), docs, legal text and labels (8), front-end accessibility (3).
  - **6 P2:** upgrade routine, CI zip check, upload size cap, admin polish, metadata consistency, CONTRIBUTING.
  - **Existing items updated:**
    - The two WIP items are now marked `[~]`.
    - The accessibility and Core Web Vitals audit moved from P2 to P1.
    - The WordPress.org readiness and i18n items now cover more.
- **Forwarded to Odell (13 questions, under "Decisions needed" in PLAN.md):**
  1. Should we add `Update URI` and drop the WordPress.org update source?
  2. Which phone number wins?
  3. Should Additional Scripts be wired up or removed?
  4. Google reviews: storage and filtering (Attorney review).
  5. Global or per-client templates?
  6. Analytics in core or Pro only?
  7. What is the Pro offer: URL, price, unit and channel?
  8. Client-named content and how client templates are packaged.
  9. Tracking privacy defaults.
  10. Template C image and code provenance.
  11. Template A design choices.
  12. API cost pass-through.
  13. Four smaller calls (P2).
- **Declined:** WOW.js license concern. It was a false alarm, because v1.1.2 is MIT.
- **Process change made after this meeting:** WOW.js was removed from the legal-reviewer's list of items to recheck.
