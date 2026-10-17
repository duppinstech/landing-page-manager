# PLAN.md — action items

Living backlog for the plugin and its deployment. Update status here; log progress
in `.claude/memory/progress.md`. Priority: **P0** = before next release, **P1** = soon, **P2** = later.

Status keys: `[ ]` todo · `[~]` in progress · `[x]` done

**Standing rule (Odell, 2026-10-14):** every unchecked item outside "Decisions needed" is approved and open to work now, in priority order. Items under "Decisions needed" wait for Odell's answer.

Source tags such as _(sec, legal · DR 10-14)_ show which department review raised the item and when.
Minutes are in [`meeting-notes/`](meeting-notes/). Departments: sec, legal, fin(ance), prod(uct), mkt (marketing), sup(port/docs), ops, ux.

_Last reviewed: 2026-10-14, baseline department review (current release: v1.0.6)_

## Decisions needed (Odell)

These were forwarded by department reviews. Nothing here gets built until Odell answers, and each answer goes in `.claude/memory/decisions.md`.

- [ ] **Merge order for the template remediation.** Recommended: PR 1 = `feature/external-templates` plus the version bump, released as v1.0.10. `feature/remove-client-templates` stays unmerged until the live-site inventory confirms every site runs 1.0.10, then becomes v1.0.11. [round 2](meeting-notes/2026-10-17-1326-external-templates-rereview.md)
- [ ] **Should the 1.0.10 update block cover every template still inside the plugin, or only templates in use?** Today it covers all of them, including uploaded templates a site no longer uses, so nothing can be lost. On hosts where WordPress can't write to `wp-content`, that keeps even starter-only sites on 1.0.10 until someone copies the folders by hand. [round 2](meeting-notes/2026-10-17-1326-external-templates-rereview.md)
- [ ] **Old release zips still contain the client templates.** The assets on releases v1.0.x up to 1.0.10 include Template A, Template B and Template C, and a history rewrite does not change them. **Options:** leave them (rollback keeps working), or delete those releases or their assets (rollback to them stops working). Note: the history rewrite must not re-run the Release workflow, or every old zip is rebuilt without the templates (see the purge item). _(10-17 template remediation)_
- [ ] **Licence for the private `landing-page-manager-templates` repository.** Recipients of versions up to 1.0.10 keep their GPL rights to those templates. **Legal recommends** GPL-2.0-or-later with LICENSE and CREDITS files (`Browser.php` is GPL; Bootstrap, Swiper, Popper, WOW.js and animate.css are MIT). A proprietary licence needs attorney review (derivative-work question, the untraced `client_g` code in Template C). [2026-10-17 13:06 review](meeting-notes/2026-10-17-1306-external-templates-review.md)
- [ ] **What the history purge should remove.** Code only (the three template folders), or also client names in `PLAN.md`, `meeting-notes/`, `.claude/memory/` and `CHANGELOG.md`, followed by a GitHub Support request to purge cached PR and commit views. The repo was public with merged PRs, so assume copies exist. [2026-10-17 13:06 review](meeting-notes/2026-10-17-1306-external-templates-review.md)
- [ ] **Attorney review: ownership wording and employment terms.** The repositories sit in a WorldVia Travel Group OneDrive folder. If an employment agreement assigns work to the employer, the "owned by Odell Duppins / Duppins Technology" wording in OWNERSHIP.md and both READMEs needs checking. [2026-10-17 13:06 review](meeting-notes/2026-10-17-1306-external-templates-review.md)
- [ ] **How client templates and their redeploys are billed.** Bespoke work per client (plus upkeep), or a reusable pack (anonymised). Fixes now reach sites only by redeploying from the private repo. [2026-10-17 13:06 review](meeting-notes/2026-10-17-1306-external-templates-review.md)
- [ ] **Live-site inventory before 1.0.11.** A site on 1.0.9 or earlier installs 1.0.11 directly and loses its client templates (pages 404; reinstalling 1.0.10 recovers them). List every live install and confirm each runs 1.0.10 with no template notice. [2026-10-17 13:06 review](meeting-notes/2026-10-17-1306-external-templates-review.md)
- [ ] **When the pinned template-b template is missing.** Today the pages show a 404 with an admin notice. Alternative: fall back to Starter One (pages stay up with a different design and empty fields). Product recommends the 404. [2026-10-17 13:06 review](meeting-notes/2026-10-17-1306-external-templates-review.md)
- [ ] **Delete `wp-content/lpmanager-templates/` on uninstall?** Default: keep it (documented in `uninstall.php` and the README). Option: delete it when `LPMANAGER_UNINSTALL_DELETE_CONTENT` is set. [2026-10-17 13:06 review](meeting-notes/2026-10-17-1306-external-templates-review.md)
- [ ] **Keyboard-accessible template tiles** (now or later). The tiles are clickable `div`s and the upload input is `display:none`, so neither is reachable by keyboard. Existed before 1.0.10. [2026-10-17 13:06 review](meeting-notes/2026-10-17-1306-external-templates-review.md)

- [x] **P0 — Stop WordPress.org from offering updates for this plugin.** **Decided (Odell, 10-14): yes.** Updates come from GitHub only until the free core is listed on WordPress.org, and Pro stays the paid add-on. _(done on `fix/review-p0-batch`, 10-14)_
  - **Problem:** The header has no `Update URI:` line, and the update source defaults to `both`. Whoever publishes `landing-page-manager` on WordPress.org could push code to every site. The slug is currently unclaimed.
  - **Recommendation:** add `Update URI: https://github.com/duppinstech/landing-page-manager`, then hide or remove the `wp_org` and `both` sources. Consider claiming the slug.
  - **Bundled with it:** cache the GitHub release response in a transient (6–12 h) and add `_transient_lpmanager_%` / `_site_transient_lpmanager_%` to `uninstall.php`. Fill `requires_php`.
  - **Review:** this is an updater change, so it needs `security-reviewer`.
  - _(sec, ops, fin · DR 10-14)_
- [x] **P0 — Which phone number wins?** **Odell answered 10-14. The field mapping is being confirmed before any code changes:** the page field is labelled "Client Phone" (`client_tel`) and the client-level field "Contact Phone" (`client_phone`). **Decided (Odell, 10-16): the page number wins.** The per-page "Client Phone" overrides the client's "Contact Phone". The page field becomes optional, and one resolved number is used for display and `tel:` in every template. **Done 10-16** on `fix/owner-decisions-p0` (`lpmanager_get_landing_phone()`).
  - **Problem:** Template B shows the page's "Client Phone" but dials the client's "Contact Phone". Template A ignores the page number when the client has one. Call-tracking numbers lose attribution.
  - **Recommendation:** the page number overrides the client number everywhere. Make the page field optional, then use one resolved number for both display and `tel:`, and add help text to both fields.
  - _(prod, ux, sup · DR 10-14)_
- [x] **P0 — "Additional Scripts" setting and the footer hook.** **Decided (Odell, 10-16): wire it up for admins only.** Print it only when saved by a user with `unfiltered_html`, re-enable `lpmanager_landing_page_footer`, and add an allowlist filter. Needs `security-reviewer`. **Done 10-16** on `fix/owner-decisions-p0` (hash-verified, printed via `lpmanager_landing_page_footer`; `lpmanager_allowed_footer_callbacks` filter).
  - **Problem:** Conversion and pixel tags pasted there are saved but never printed. The `lpmanager_landing_page_footer` action never fires, and `remove_all_actions('wp_footer')` strips other plugins' tags.
  - **Recommendation:** wire it up for users with `unfiltered_html` only, re-add the footer action, and add an allowlist filter for tracking hooks. The alternative is to remove the setting.
  - **Review:** `security-reviewer`.
  - _(prod, ux, sec · DR 10-14)_
- [x] **P0 — Google reviews: storage and 4–5★ filtering (Attorney review).** **Interim decided (Odell, 10-16):** replace the 12-hour content cache with a timestamp-only lock. Permanent storage and the 4–5★ filter still wait for the attorney. **Interim done 10-16** on `fix/owner-decisions-p0`: a 10-minute timestamp lock replaces the content cache, and old cached content is deleted when it's encountered.
  - **Problem:** Reviews are stored permanently in post meta, which the Places terms forbid. They lack Google's required attributions, and are filtered to 4–5★ with no disclosure, which risks the FTC review-suppression rule.
  - **Recommendation:** render reviews live with full attribution and a "selected reviews" notice, or switch to a review-widget embed. An attorney needs to say whether disclosure is enough or the filter must go.
  - **Also put to the attorney (10-14b):** `Reviews_Ajax` now caches the Places Details response for 12 hours to control cost. Legal says Google's terms forbid caching Places content beyond place IDs, so a 12-hour cache is itself a breach. Legal's alternative keeps the cost control without caching content: a short timestamp-only lock that refuses repeat fetches.
  - _(legal, fin · DR 10-14)_ _(legal, sec, prod · DR 10-14b)_
- [x] **P0 — One global template, or a template per client?** **Decided (Odell, 10-16): per-client is a Pro feature.** Core stays global-only and exposes an `lpmanager_template_for_post` filter; Pro adds the per-client UI. Update INTENT.md goal 2 to match. **Docs done 10-16** (INTENT, OWNERSHIP). Filter added 10-16 (unknown slugs fall back to the site-wide template). The per-client UI belongs in Pro.
  - **Problem:** The WIP finishes the move to global-only, but `INTENT.md` goal 2 still says "set on the client term". An agency can't mix Template A and Client B clients on one install.
  - **Recommendation:**
    - **If global-only:** log the decision, update `INTENT.md:15`, and add a warning on the Templates tab.
    - **If per-client:** decide whether it's core or a Pro upsell. If Pro, core only exposes an `lpmanager_template_for_post` filter.
  - _(prod, fin, legal · DR 10-14)_
- [x] **P1 — Analytics: core or Pro only?** **Decided (Odell, 10-16): Pro-only.** Correct README, INTENT and business-context, and move `tracking.js` and the tracking-toggle definitions into the Pro repo. **Docs done 10-16** (INTENT, business-context; README already said Pro). Still to do: move `tracking.js` and the toggle definitions into the Pro repo. **Constraint (Pro session, 10-16):** Pro 1.0.3 reads the toggles through core's `lpmanager_tracking_options()` and the `lpmanager_track_*` option names, and its own `tracking.js` dequeues core's `lpmanager-tracking` handle. When moving them, keep that function and those option names working (or hand them to Pro in the same release), and coordinate with the Pro repo. **R10 (show interactions, heatmap and rage clicks in core's analytics page) is on hold:** it conflicts with the Pro-only decision, and the Pro session suggests it belongs in Pro's own admin pages. **Agreed with the Pro session, 10-16:** storage stays in `_lpmanager_track_*`. Pro 1.0.4 adds `lpmanager_pro_tracking_options()` and its own Settings section. Core delegates to it and hides its Conversion Tracking tab when it exists (**done 10-16**). Core's `tracking.js` was removed and then **restored** (DR 10-16c: Pro 1.0.2 still loads it from core). Delete it once every site runs Pro 1.0.3 or later. R10 was dropped: Pro shows its extra analytics on its own screens. Core's uninstall now leaves `_lpmanager_track_*` alone; Pro's opt-in uninstall removes them (10-16).
  - **Problem:** INTENT, the README and business-context say core counts views and conversions. In the code, core counts nothing without Pro. Pro's tracking JS and settings schema also live in this public repo.
  - **Recommendation:** pick one of these:
    - (a) Add a minimal core view and tel-click counter, which makes a natural upgrade prompt.
    - (b) Declare analytics Pro-only and move `tracking.js` and the toggle definitions into Pro.
  - Either way, the docs get corrected now (see P1).
  - _(mkt, prod, fin, legal · DR 10-14)_
- [x] **P1 — Pro offer: URL, price, licensing unit, update channel.** **Interim decided (Odell, 10-16):** buttons say "Pro analytics is coming soon — contact Duppins Technology for early access" and link to duppinstech.com. Price, licensing unit and EULA are still open. **Banner done 10-16** (`Admin_Dashboard`); Settings-page prompt done 10-16 as well.
  - **Problem:** Every "Get Pro Now" button links to `#`, and the README links to a private repo. No price or licensing unit is defined.
  - **Recommendation:** set a duppinstech.com Pro or contact page and the licensing model. Until then, hide the button or use "Pro analytics is coming soon — contact Duppins Technology for early access."
  - The Pro EULA should restrict only support, updates and keys, not GPL redistribution (Attorney review).
  - _(mkt, prod, sup, fin, legal · DR 10-14)_
- [x] **P1 — Client-named content in the public repo, and how client templates are packaged.** **Decided (Odell, 10-16): templates are reusable Duppins products.** Anonymize client names in labels, CSS and mockups (or move the mockups to a private repo), bill clients only for customization, and ship a neutral default.
  - **Problem:** The Template A `.html` mockups carry client names, phone numbers and "© Client E / Client D" lines. A fresh install defaults to a client-branded template.
  - **Decide:** for each template, is it reusable product (anonymize it and bill only the customization) or client-exclusive (move it to a private package and quote it separately)? Also decide whether to ship a neutral "Starter" default template, and whether to anonymize the mockups or move them to a private repo.
  - _(legal, mkt, fin · DR 10-14)_
- [x] **P1 — Tracking privacy defaults.** **Decided (Odell, 10-16): keep all trackers on by default.** No change to defaults. (Legal had recommended behavioral trackers off plus a consent filter.)
  - **Problem:** With Pro active, every behavioral tracker (heatmap, rage clicks, form interactions) defaults to on, and there's no consent hook.
  - **Recommendation:** default the behavioral trackers to off, keeping views and clicks on. Add an `lpmanager_tracking_consent` filter.
  - _(legal · DR 10-14)_
- [x] **P1 — Image and code provenance in Template C.** **Decided (Odell, 10-16): image rights are cleared; keep the default images.** Still to do: build the copyright line from the site or client name instead of the hardcoded "© Template C". **Copyright line done 10-16.**
  - **Problem:** The default hero images are hotlinked from the client's site and include a car-care brand–named photo, so their rights are unknown. The footer hardcodes "© Template C" on every client's pages. The `client_g` text domain suggests the code came from another project.
  - **Recommendation:** confirm the rights, use operator-uploaded media only, set the copyright line from the site name, and decide whether "Apply Now" becomes an HTML button.
  - _(legal, ux · DR 10-14)_
- [x] **P1 — Template A design choices.** **Decided (Odell, 10-16):** swap in a phone icon for the mobile call button, and make Template A honor the client's colours. **Done 10-16** on `fix/owner-decisions-p0`: phone icon, and client colours via `lpm_qt_brand_palette()` (darkened to keep 4.5:1 with white text).
  - **Mobile button:** the default header's three-bar "menu" icon actually dials the phone. Recommendation: swap in a phone icon.
  - **Client colors:** Template A ignores client and Brandfetch colors and uses a fixed red and navy palette. Decide whether it should honor client colors or keep the palette (and document that).
  - _(ux · DR 10-14)_
- [x] **P1 — Who pays for API usage on installs Duppins runs?** **Decided (Odell, 10-16): clients supply their own Google and Brandfetch keys and billing.**
  - **Decide:** for each Duppins-operated install, does it use the client's key or Duppins' key? Should Google, Brandfetch and hosting costs be passed through, bundled into a retainer, or covered by requiring the client's own key?
  - **Fill in:** Brandfetch plan, traffic numbers and support hours in `business-context.md` / `CLAUDE.local.md`.
  - _(fin · DR 10-14)_
- [x] **P0 — Before the next release: which live sites need hand-holding?** **Answered (Odell, 10-16): none.** No live site uses "WordPress.org only" updates or an uploaded template.
  - **Sites set to "WordPress.org only" in 1.0.6:** they will never be offered this release. Update them by uploading the zip by hand.
  - **Sites whose active template was uploaded:** they 404 the moment they update. Pause their auto-updates, or re-upload the template right after updating.
  - **Needed from Odell:** check each site you run. _(DR 10-14b)_ _(ops, sup, prod)_
- [x] **P1 — Move to Places API (New)?** **Decided (Odell, 10-16): yes, migrate.** Do it after the reviews/attorney question is settled.
  - **Problem:** The code calls the legacy Places endpoints (Find Place, Text Search, Details). Google hasn't let new Cloud projects enable those since March 2025, so new operators can't get address lookups or reviews working.
  - **Interim:** the help text now asks for the original "Places API".
  - **Migrating:** needs a field mask. Reviews would cost about $25 per 1,000 with 1,000 free a month. _(DR 10-14b)_ _(fin, sup, prod)_
- [x] **P1 — Delete the unused vendored files?** **Decided (Odell, 10-16): yes, delete them.** `swiper.js` waits one release after 10-16. **Done 10-16** on `fix/owner-decisions-p0` (19 files). **Not on the approved list, also unused:** Template B `js/plugins/bootstrap.js`, `js/plugins/popper.min.js` and `css/animate.min.css` — ask Odell before deleting.
  - **Template B and Template C:** `payform.min.js`, `jquery-migrate*.js`, `bootstrap-new.js`, the jQuery UI CSS and `sqpaymentform.css`.
  - **Template C only:** `popper.min.js`, `bootstrap.js` and the Swiper files.
  - **Also:** the dead `template-c/functions.php`.
  - **Added 10-16:** `template-b/js/plugins/swiper.js`, now unused because `swiper.min.js` replaced it. Delete it no sooner than one release after 10-16, because cached pages may still request it. _(legal, ops · DR 10-16)_
  - **Why:** none of these load any more, but they still ship in the zip and need license credits.
  - **Needed from Odell:** approval to delete files. _(DR 10-14b)_ _(prod, ux, legal)_
- [x] **P1 — Allow iframes in rich-text template fields?** **Decided (Odell, 10-16): allow iframes from a filterable host allowlist** (YouTube, Vimeo, Google Maps, plus named review-widget hosts). Needs `security-reviewer`. **Done 10-16** on `fix/owner-decisions-p0` (`lpmanager_kses_rich_text()`). Security blocked the first version (URL-parser confusion; quoting bypasses on WP 5.9–6.x). It was rebuilt: iframes are read with `WP_HTML_Tag_Processor`, checked against anchored per-provider URL patterns (`lpmanager_iframe_src_patterns`) and rebuilt from scratch. Before WP 6.2 all iframes are stripped. Add review-widget providers as patterns once you name them.
  - **Problem:** Escaping now runs Template B `section_4_text_1`, Template C `more_content` and Template B `reviews_html` through `wp_kses_post`, which strips iframes. Video, map or review-widget embeds pasted there no longer show.
  - **Decide:** allow iframes from an allowlist of hosts (product and security call), or keep stripping them. _(DR 10-14b)_ _(ops, sec)_
- [x] **P1 — Do any live Template A clients use Google Tag Manager or jQuery-based tracking tags?** _(DR 10-16: ops, fin, ux, sup)_ **Answered (Odell, 10-16): yes, some do.** Add a per-client "Tracking tags need jQuery" checkbox next to Header Tracking Code; operators tick it for those clients. **Done 10-16** on `perf/maps-lazy-load-p1-batch`. Before release, tick it for the affected Template A clients.
  - **Why:** Template A no longer loads jQuery for logged-out visitors unless the client's Header Tracking Code looks like it needs it. A jQuery tag inside a GTM container can't be detected, and would stop firing without any visible error.
  - **Recommendation:** check before the next release. For an affected site, add the `lpmanager_load_jquery` filter in a mu-plugin, or put a comment mentioning jQuery in that client's Header Tracking Code.
- [x] **P2 — Should the empty-server-key warning be dismissible?** It shows on every plugin screen until a server key is saved. **Recommendation:** keep it, but allow each user to dismiss it for 30 days. _(DR 10-16: prod, sup, ops)_ **Decided (Odell, 10-16): yes, per user for 30 days.** **Done 10-16.**
- [ ] **P2 — Template C mobile menu colours.** The panel is white with dark text. Should it follow the client's brand colours? This is a design call; contrast is fine as it is. _(DR 10-16: ux)_
- [x] **P2 — Document a "needs jQuery" marker for the client's Header Tracking Code?** Any mention of "jQuery" in that field already makes Template A load it. **Recommendation:** don't promise it as a feature. Document the `lpmanager_load_jquery` filter instead. _(DR 10-16: sup)_ **Superseded (10-16)** by the per-client jQuery checkbox.
- [ ] **P1 — Who should be able to manage clients?** _(security · 10-16)_
  - **Now:** the `client` taxonomy uses default capabilities, so Editors can add and edit clients. Since 10-16 they can no longer change the Header Tracking Code, which prints raw.
  - **Recommendation:** limit managing clients to administrators (`manage_options`), or keep Editors, since the raw-HTML field is now locked.
- [ ] **P2 — Who can run Fetch Reviews?** _(security · 10-16)_ The 10-minute lock allows up to 144 billed calls per place per day, and anyone who can edit a page for that client can trigger them. **Recommendation:** require Editor-level access (`edit_others_posts`), and optionally add a daily cap per place.
- [ ] **P1 — Client B template names and the hotlinked Template C heroes.** Both templates still carry a client's name, and the Template C default hero images load from the client's own server. **Recommendation:** rename the templates' display names (keep the slugs), as was done for Template A, or record the client's written consent. _(legal, mkt _(DR 10-16c)_)_
- [ ] **P1 — car-care brand–named default hero images in Template C, and how far the image rights reach.** Do the rights you confirmed cover use by every operator? **Recommendation:** remove the two car-care brand images from the defaults, or ask the attorney. _(legal _(DR 10-16c)_)_
- [ ] **P0 — Template A fallback colours.** Clients with no brand match are stored with the fallback `#007BFF`/`#FF4136`, so their Template A pages turn generic blue and red on update. **Recommendation:** treat that pair as "no brand colour" and keep Template A's own palette. _(prod, sup, ops _(DR 10-16c)_)_
- [ ] **P1 — API keys are per install, not per client.** "Clients supply their own keys" only works with one install per client. **Options:** one install per client on Duppins-run sites; a per-client key override in core; or Duppins' key with the cost passed through. _(fin _(DR 10-16c)_)_
- [ ] **P1 — Early-access link target.** The buttons go to the duppinstech.com home page. **Recommendation:** a contact or early-access page with UTM tags. _(fin, mkt _(DR 10-16c)_)_
- [ ] **P1 — Sites with `DISALLOW_UNFILTERED_HTML`.** Nobody, admins included, can save or approve Additional Scripts or change Header Tracking Code there. **Options:** document it (done in the CHANGELOG), or fall back to `manage_options` on single-site installs. _(ops _(DR 10-16c)_)_
- [ ] **P1 — When can core's `assets/js/tracking.js` be deleted?** Pro 1.0.2 loads it from core. The Pro session will confirm once every managed site runs Pro 1.0.3 or later. _(ops _(DR 10-16c)_)_
- [ ] **P2 — Template A accents when the client's two colours are similar.** Red accents disappear against navy. **Options:** fall back to the built-in red for accents, or accept it. _(ux _(DR 10-16c)_)_
- [ ] **P2 — Upsell copy for per-client templates on the Templates tab** ("coming in Pro, contact for early access"), if the Pro roadmap commits to it. _(mkt _(DR 10-16c)_)_
- [ ] **P2 — Pro 1.0.3 support window.** This decides when core's tracking-toggle definitions can be removed. _(prod _(DR 10-16c)_)_
- [ ] **P0 — Who can change Header Tracking Code?** On a single site, Editors have `unfiltered_html`, so they can still edit a client's Header Tracking Code, which prints raw. Additional Scripts are already admin-only, because the Settings page needs `manage_options`. **Options:** (a) also require `manage_options` for both raw-HTML fields (Editors lose the ability; needs `security-reviewer`); (b) keep it as is. The docs now say "administrators and editors on a single site". _(sup, sec · DR 10-16c re-review)_
- [ ] **P2 — Template A brand fidelity vs. contrast.** Reaching 4.5:1 for 72%-white text needs navy around 7:1, so light brands get darkened a lot (yellow becomes olive). **Option:** make text on client-navy areas fully white and relax the darkening to 4.5:1. _(ux · DR 10-16c re-review)_
- [x] **P2 — Should internal planning docs stay in the public repo?** **Decided (Odell, 10-16): keep them public.**
  - `PLAN.md`, `meeting-notes/` and `.claude/memory/` are excluded from the zip but readable on GitHub, including the legal-risk analysis.
  - **Decide:** move them to a private repo or keep them public. _(DR 10-14b)_ _(legal)_
- [x] **P2 — Smaller calls.** **Decided (Odell, 10-16): yes to all four** — (a) incomplete pages stay drafts, (b) landing pages get their own capability type, (c) uninstall deletes the orphaned `_lpmanager_client_template` meta, (d) correct the v1.0.6 release notes. **(c) and (d) done 10-16:** uninstall deletes the orphaned meta, and the v1.0.6 release notes are corrected. **(a) done 10-16:** a page being published with missing fields is kept as a draft; already-live pages are never unpublished.
  - (a) Should the validator keep incomplete pages as drafts instead of publishing them? _(prod)_
  - (b) Should landing pages get their own capability type instead of `post`? _(sec)_
  - (c) Should uninstall always delete the orphaned `_lpmanager_client_template` meta? _(ops)_
  - (d) Should the published v1.0.6 GitHub release notes be corrected (Template A variant count)? _(sup)_
  - _(DR 10-14)_

## P0 — before the next release

- [ ] **Post-release checks for v1.0.9:** on a site still running v1.0.8, open Dashboard → Updates and select Check again within a minute of opening the page. v1.0.9 should be offered. Then check the Plugins row: Settings | Help | Deactivate, and Version | By Duppins Technology | Sponsor | Visit plugin site | Support. _(security reviewer asked for the manual Check again run, 10-17)_
- [ ] **Post-release checks for v1.0.8:** on a live site, open the Help button on the Dashboard, Landing Pages list and editor, Clients, Keywords, Analytics and each Settings tab. Check that the notes match the selected Settings tab, Escape closes it, the guide link opens the right section, and the panel fits on a phone. Rewrite `marketing/guide/help-button.md`, which still describes the removed Help tab. _(Odell waived review, 10-16)_
- [ ] **Post-release checks for v1.0.7 on each live site** (the smoke test was waived): Template A colours and phone numbers (run smoke step 26's phone audit), approve Additional Scripts, check Header Tracking Code, purge page caches, and look for 404s and console errors. _(Odell waiver, 10-16)_

- [x] **Department review 10-16c fixes on `fix/owner-decisions-p0`.** **Done 10-16** (commits `384fea9` through the docs commit; see progress.md). The full list is in [the meeting notes](meeting-notes/2026-10-16-1034-owner-decisions-review.md). It covers:
  - the validator revision fix and the "Draft updated" message;
  - reviews compliance wording and the privacy text;
  - upgrade notes: colours, phone, Additional Scripts, embeds, class renames, deleted files, `DISALLOW_UNFILTERED_HTML`, and salts or site migration;
  - `reviews_html` help text, labels and the README;
  - the approval notice on every plugin screen;
  - Template A contrast targets, iframe sizing and title, and a 44px call button;
  - a jQuery check that covers Additional Scripts;
  - i18n fixes.

  _(DR 10-16c)_

- [~] **Remove or re-wire the per-client template field.**
  - **Done:** the WIP on `chore/template-field-cleanup` removes it. The README already matches.
  - **To close:** the CHANGELOG entry (below) and Odell's global-vs-per-client decision.
- [~] **Move the Template A "Style Variant" field out of core.**
  - **Done:** the WIP moves it to `template_a/lib/fields/` and keeps the `_wra_style_variant` key, so saved styles survive.
  - **To close:** the CHANGELOG entry and the smoke test (style_1/2/3, switching templates, rolling back to v1.0.6).
- [x] **CHANGELOG `[Unreleased]` for the WIP.** _(done on `fix/review-p0-batch`, 10-14)_
  - Add a "Removed" entry for the client template field and a "Changed" entry for Style variant moving to Template A → Layout.
  - Correct the 1.0.6 Template A line: it has three layout styles, not four variants.
  - The ready-to-use text is in [the appendix](meeting-notes/2026-10-14-1951-baseline-review-appendix.md).
  - _(sup, ops, prod, sec, mkt · DR 10-14)_
- [x] **CI lint gate actually fails on a syntax error.** Fixed on `fix/review-p0-batch` (10-14); lint output now goes to `$RUNNER_TEMP`, so it can't ship in the zip. **To close:** after the branch is pushed, prove it fails with a throwaway PR containing a parse error. _(DR 10-14b)_ **Closed 10-16:** throwaway PR #4 passed on 7.4/8.1/8.3 with the branch as-is (run 38848604761) and failed on all three with a parse error (run 38848679772). The PR was closed and its branch deleted.
  - **Problem:** `php -l | grep -v` with no `pipefail` exits 0, so a parse error can ship.
  - **Fix:** add `defaults: run: shell: bash` to `ci.yml` and `release.yml`, then prove it with a deliberately broken file.
  - _(ops · DR 10-14)_
- [x] **Escape all template output.** _(done on `fix/review-p0-batch`, 10-14)_
  - **Problem:** Meta is echoed raw in the templates, and Contributors and Authors can edit landing-page fields.
  - **Where:** `template-c` (`single-landing_page.php`, `404.php`, `header.php`), `template-b/single-landing_page.php:144`, and Template A's JSON-LD (`header.php:33`).
  - **Fix:** escape by context. For JSON-LD, decode and re-encode with `JSON_HEX_TAG`.
  - **Review:** `security-reviewer`.
  - _(sec · DR 10-14)_
- [x] **Explicit per-template asset loading.** _(done on `fix/review-p0-batch`, 10-14)_
  - **Problem:** `Assets_Manager::enqueueFilesInDir()` loads every file in each template's `css/` and `js/` folders. That means about 1 MB per page, two Bootstrap versions, a second jQuery that replaces WordPress's own, three Swiper versions, and unused payform and jQuery UI.
  - **Fix:** use a declared list per template (handle, path, deps), and drop the unused and duplicate files.
  - _(ux · DR 10-14)_
- [x] **Above-the-fold content must not wait for JS.** _(done on `fix/review-p0-batch`, 10-14)_
  - **Problem:** The hero H1 and CTA are hidden by `.wra-fi` and WOW until the footer JS runs.
  - **Fix:**
    - Never apply reveal classes to the hero.
    - Gate the hide under `html.js` + `prefers-reduced-motion: no-preference`.
    - Skip WOW and Swiper autoplay when the user prefers reduced motion.
  - _(ux · DR 10-14)_
- [x] **Privacy policy content.** _(done on `fix/review-p0-batch`, 10-14)_
  - Call `wp_add_privacy_policy_content()` with text covering the Google Fonts, cdnjs and jsDelivr requests, the Maps embed, and view/click tracking. The draft text is in [the appendix](meeting-notes/2026-10-14-1951-baseline-review-appendix.md).
  - _(legal · DR 10-14)_
- [x] **`.gitattributes` `export-ignore`.** _(done on `fix/review-p0-batch`, 10-14)_
  - **Problem:** If a release lacks its zip, the updater falls back to the GitHub zipball, which ignores `.distignore`.
  - **Fix:** mirror `.distignore` in `.gitattributes` (mockups, `.claude/`, `CLAUDE.md`, `PLAN.md`, `meeting-notes/` …).
  - _(legal · DR 10-14)_
- [x] **Stop per-view billed Places calls; harden `Reviews_Ajax`.** _(done on `fix/review-p0-batch`, 10-14)_
  - **Problem:** `reviews.js` loads on every page and calls Place Details with no `fields` limit and no cache.
  - **Fix:**
    - Stop loading `reviews.js` site-wide.
    - In the handler, require `landing_page` + `publish`, use `rawurlencode`, a timeout, `fields=reviews,rating`, and a per-place transient.
  - **Do not add `nopriv`** until this is done, and keep it coordinated with the reviews decision above.
  - _(fin, sec · DR 10-14)_
- [x] **Split the Google API key into browser and server keys, and explain billing.** _(done on `fix/review-p0-batch`, 10-14)_
  - **Problem:** One key is printed in page HTML and also used server-side for Places. A referrer restriction breaks the server calls, so operators leave it unrestricted.
  - **Fix:** use two settings (a browser key restricted by referrer, a server key restricted by IP). The help text should name the APIs each one needs and say who is billed. Recommend quotas and budget alerts.
  - _(fin, sup, sec · DR 10-14)_
- [x] **Warn that uploaded templates are deleted by updates.** _(done on `fix/review-p0-batch`, 10-14; the upgrade note is in the CHANGELOG, 10-14b)_
  - Add a warning on the upload card.
  - In the next release, add an admin notice when the active template folder is missing (pages would 404).
  - Add an upgrade note to the release.
  - The permanent fix is the P1 "store outside plugin folder" item.
  - _(sup · DR 10-14)_

- [x] **Smoke-test the whole unmerged stack on staging** — **waived by Odell for v1.0.7 (10-16)**; see the post-release checks item. (`chore/template-field-cleanup` → `fix/review-p0-batch` → `perf/maps-lazy-load-p1-batch` → `fix/owner-decisions-p0`), once, at the top of the stack, then merge them in order as separate PRs. Run the 15 steps in [the 10-14b appendix](meeting-notes/2026-10-14-2245-p0-batch-review.md#smoke-test) (updater, per-template assets, reduced motion, Google keys, CSV export) plus steps 16–24 in [the 10-16 appendix](meeting-notes/2026-10-16-0009-maps-p1-batch-review-appendix.md#smoke-test-steps-1624), with the step 16/22 corrections and the new step 25 from [the 10-16 re-review](meeting-notes/2026-10-16-0022-maps-p1-batch-rereview.md#corrections-to-the-0009-appendix). Test logged out: logged-in users always get jQuery. Include a touch tablet in landscape (≥921px) to check that "Services" opens on tap. Steps 25–41 for the owner-decisions batch are in [the 10-16c appendix](meeting-notes/2026-10-16-1034-owner-decisions-review-appendix.md#smoke-test-steps-2541), with corrections to steps 29, 30, 32, 35 and 41 in [the 13:00 re-review](meeting-notes/2026-10-16-1300-owner-decisions-rereview.md#smoke-test-corrections-to-steps-in-the-1034-appendix). Run the phone audit (step 26) and the `DISALLOW_UNFILTERED_HTML` check (step 31) on every live site before release. This needs a WordPress staging site, and it is the merge gate. _(DR 10-14b: ops, prod, fin · DR 10-16: ops, prod)_
- [x] **Stop loading Maps JS on every page view (template-b footer).** This is now the biggest per-view Google cost (about $7 per 1,000 loads after the free 10,000), so it moved up from P1. See the P1 entry below for the fix. _(DR 10-14b)_ _(fin)_ **Done 10-16** (`perf/maps-lazy-load-p1-batch`): Maps JS loads when the map is within 300px of the viewport, the map has an accessible region name, and setup messages show to editors only.

## P1 — deployment and quality

- [ ] **Draft-on-publish polish.**
  - Validate a page when it is scheduled, not when it goes live.
  - Keep a lasting notice when cron reverts a scheduled page.
  - Show a notice on the list screen when Quick Edit, bulk edit or REST publishes are reverted.
  - Note for Pro: re-check the post status in publish hooks.

  _(prod, ops, sec _(DR 10-16c)_)_
- [ ] **Landing pages get their own capability type** (decided 10-16 (b)). Map the defaults to today's post capabilities, then allow scoping by role. Needs `security-reviewer` and an upgrade routine that grants the new capabilities to existing roles. _(prod _(DR 10-16c)_)_
- [ ] **Migrate to Places API (New)** (decided 10-16), after the attorney answers. Redo the cost maths with the field-mask SKU, and add a per-place daily cap. _(prod, fin _(DR 10-16c)_)_
- [x] **Ship a neutral "Starter" default template** (decided 10-16: templates are reusable products). _(prod _(DR 10-16c)_)_ **Done 10-17** on `feature/external-templates`: Starter One (light) and Starter Two (dark), shared `starter_*` fields, client logo/colours/phone, no JavaScript; default for new sites (Odell, 10-17: "Starter One / Two").

- [x] **Shipped v1.0.6 on 2026-10-14.** Ongoing: **keep the `oduppinsjr` GitHub account and never create or fork a `landing-page-manager` repo under it.** (From the security review.) Sites on v1.0.5 and earlier still call the old API URL and only reach the new repo through GitHub's redirect. If a repo with that name is ever recreated there, it controls their updates, which means remote code execution on those sites.
- [ ] **Updater integrity.** Allow package URLs only from `github.com`, `codeload.github.com` and `objects.githubusercontent.com`, and verify a checksum or signature published with the release (for example a `.sha256` asset).

- [x] **CI: lint on push/PR.** `.github/workflows/ci.yml` runs `php -l` on PHP 7.4, 8.1 and 8.3. (See the P0 pipefail fix: the gate doesn't fail today.)
- [ ] **CI: PHPCS (WordPress Coding Standards).** Start non-blocking. This needs the dev-dependency decision below.
- [x] **CI: release automation.** `.github/workflows/release.yml` runs on a `v*` tag. It lints, checks that the tag matches `Version:`, builds the zip using `.distignore`, checks the zip layout, and creates the release with the zip and changelog notes. It uses the committed `vendor/`, so the shipped zip is exactly what was tested.
- [ ] **Decision needed: dev dependencies while `vendor/` is committed.** `composer require --dev phpunit` would commit PHPUnit into `vendor/`. Options: (a) CI runs `composer install` with dev dependencies and the release builds with `--no-dev`, which means no longer committing `vendor/`; (b) install the dev tools globally in CI only (`tools/` or phive).
- [ ] **Test harness.** Blocked by the decision above. Add PHPUnit with `wp-phpunit` or Brain Monkey. First targets: `Update_Checker::version_compare`, `normalize_install_source_dir`, `Landing_Page_Validator`, rewrite-rule host parsing.
- [x] **Pin Composer dependency.** `carbon-field-password` is pinned to `dev-master#80df427`, the commit already locked and vendored. Upstream has no tags.
- [x] **CHANGELOG backfill** for v1.0.1–v1.0.5.
- [x] **Add a copyright line to LICENSE.**
- [x] **Store uploaded templates outside the plugin folder.** The `lpmanager_template_for_post` fallback must check both locations (DR 10-16c). When this is done, `render_missing_template_notice()` must check both locations (DR 10-14b). (From the security review.) Every plugin update replaces `includes/templates/`, which silently deletes uploaded templates; their landing pages then fall back to a 404. Move uploads to `wp-content/lpmanager-templates/<slug>/` and have `get_templates()` and `Template_Loader` search both locations, with shipped templates taking priority. **Done 10-17** on `feature/external-templates` (`Template_Manager::get_template_dir()`/`get_template_url()`). Starter slugs always resolve to the plugin; for any other slug the custom folder wins over the plugin copy, so 1.0.10 already runs the copied templates before 1.0.11 removes the originals. A one-time step copies non-starter templates out of the plugin folder and pins `template-b` for sites that never saved the setting.
- [ ] **Remove Template A, Template B and Template C from the plugin (1.0.11).** Odell, 10-17: client templates move to the private repo `duppinstech/landing-page-manager-templates`. Release only after every live site runs 1.0.10 and shows no template notice, because an update deletes the plugin folder before new code runs. Also drop the Template C line from the privacy-policy guide (`Privacy.php`). **Prepared 10-17** on its own local branch `feature/remove-client-templates`, held back: merge only `feature/external-templates` for 1.0.10, then rebase the removal onto the 1.0.10 release commit and date its CHANGELOG block as [1.0.11] (remove the HTML comment so it doesn't land in the extracted release notes). [round 2](meeting-notes/2026-10-17-1326-external-templates-rereview.md)
- [ ] **Purge the client templates from git history** (Odell, 10-17: rewrite). After 1.0.11. Procedure ([2026-10-17 13:06 review](meeting-notes/2026-10-17-1306-external-templates-review.md)): (1) merge or abandon every other branch and worktree (`feature/github-updater-standard`, `ci/throwaway-lint-proof`), and have every session push and stop; (2) `gh workflow disable Release`, because force-pushed tags re-run it and `--clobber` would rebuild every old release zip; (3) in a `git clone --mirror`, run `git filter-repo` on the three folders (plus the docs Odell names); (4) force-push branches and **force-update** tags (never delete a tag: its release turns into a draft); (5) check that `releases/latest` still returns the newest release with `landing-page-manager.zip`, then re-enable the workflow; (6) ask GitHub Support to purge cached views and `refs/pull/*`; (7) everyone re-clones. Commit IDs quoted in PLAN and the meeting notes stop resolving.
- [x] **Review fixes for the external templates** ([2026-10-17 13:06 review](meeting-notes/2026-10-17-1306-external-templates-review.md)). Copy step off visitor requests (WP-Cron and admin pages) with a lock, shutdown cleanup, `wp_is_file_mod_allowed()` and WordPress file permissions; legacy pin before Carbon Fields registers fields; update blocked while a template exists only inside the plugin; WCAG-based starter colours, focus ring, hero priority, tap targets, Headline not required; no Google Fonts/Font Awesome for the starters (`cdn_assets`); CHANGELOG, README, Help, notices, uninstall, privacy guide and zip check updated. _2026-10-17_ Round 2 ([round 2](meeting-notes/2026-10-17-1326-external-templates-rereview.md)): no `.htaccess` (it can return errors where `Options` overrides are off; copied folders get an `index.php` instead); no database writes once nothing is pending; admin re-check for administrators only and never on AJAX; atomic `WP_Upgrader::create_lock()`; no endless WP-Cron rescheduling when file changes are disabled; an interrupted copy no longer counts as done; the guard also covers "Replace current with uploaded" and names the templates; the notice covers every template still inside the plugin; starter contrast checked against both page colours, call bar and contact button edges, eager logo, call-bar spacing only with a phone, licence notice in the starters; docs, Privacy tutorial, scaffold command and progress log updated.
- [ ] **Document how client templates are deployed** from `landing-page-manager-templates` to each live site (who, how, and how fixes such as the Template A fallback colours reach them). The README has the manual steps; automation or a schedule is Odell's call. _(prod, fin · DR 10-17)_
- [ ] **After 1.0.11: move the client-template backlog to the private repo** (Template A fallback colours and accents, Client B names and hotlinked Template C heroes, mockups, per-style items) and split CREDITS (Bootstrap, Popper, Swiper, Animate and WOW leave with the templates). _(prod, legal · DR 10-17)_
- [ ] **P2: show where each template is stored** (plugin or custom folder) on the Templates tab or in Site Health, to confirm the 1.0.10 rollout before 1.0.11. _(prod · DR 10-17)_
- [ ] **P2: `LPMANAGER_TEMPLATES_DIR`** for hosts where wp-content isn't writable (Pantheon, VIP, immutable deploys). _(ops · DR 10-17)_
- [ ] **Guard template field loading.** (From the security review.) `Template_Loader` `require_once`s `<slug>/lib/fields/landing-page-fields.php` on wp-admin requests, so a broken uploaded template can take down wp-admin. Do this after the WIP in `Template_Loader.php` is committed.
- [x] **Uninstall routine.** `uninstall.php` removes `lpmanager_*` and `_lpmanager_*` options (but not `lpmanager_pro_*`) and the validation transients, on every site in a multisite network. Content is kept unless `LPMANAGER_UNINSTALL_DELETE_CONTENT` is true. Needs a test on a WP site.
- [x] **Repo moved to `duppinstech/landing-page-manager`.** Updated the remote, the updater API URL, the Plugin URI and the docs.

### P1 — from the 2026-10-14 baseline department review

**Security and hardening**
- [ ] **Scope subdomain routing to the page's client.**
  - Require the `'.' . $main_domain` suffix in the host check.
  - Use `has_term( $subdomain, 'client', $post )`, otherwise redirect or 404.
  - Hook or remove `filter_landing_pages_query`.
  - **Review:** `security-reviewer`.
  - _(sec, prod · DR 10-14)_
- [~] **Lock down "Header Tracking Code" on clients.** **10-16:** only users with `unfiltered_html` can change it, by any route including Carbon REST, and the field is read-only for everyone else. Whether to restrict client management itself is now a question for Odell.
  - Set the taxonomy capabilities to `manage_options`.
  - Refuse or clear the field for users without `unfiltered_html`.
  - Relabel it from "(Nimbata)" to "Header tracking code (call tracking, pixels, tags)".
  - _(sec, mkt · DR 10-14)_
- [ ] **Serve third-party front-end assets locally, at pinned versions.**
  - Swiper, Font Awesome (or inline SVG) and Google Fonts. Pin Chart.js in admin and add SRI if it stays on a CDN.
  - Add per-template fonts, `preconnect` and `font-display: swap`.
  - This removes IP leaks to the CDNs and render-blocking CSS.
  - _(sec, legal, ux · DR 10-14)_
- [x] **CSV export hardening.** _(done on `fix/review-p0-batch`, 10-14)_
  - Neutralize cells starting with `= + - @ \t \r`.
  - Add `wp_nonce_url` + `check_admin_referer`.
  - Remove the duplicate `init()` call at `Landing_Page_CPT_Enhancements.php:145`, which registers every hook twice.
  - _(sec · DR 10-14)_
- [ ] **Brandfetch hardening.**
  - Validate the domain as a hostname and `rawurlencode` it.
  - Skip the call when the key is empty, and add a timeout.
  - Run `sanitize_hex_color` on the saved colors.
  - Cache results per domain.
  - _(sec, fin · DR 10-14)_
- [ ] **API key fields use the password field type.**
  - Use `carbon-field-password`, and remove the `admin.js` masking (which may save `****` over the real key) and the plaintext `*_previous` copies.
  - Include the new `lpmanager_google_server_api_key`. It's the more sensitive key and is currently shown in clear. _(DR 10-14b)_
  - _(sec, ux · DR 10-14)_

**Cost and data**
- [x] **Stop loading Maps JS on every page view (template-b footer).** Moved up to P0; see there. Done 10-16.
  - Use the Maps Embed iframe (free) or lazy-load on scroll or click.
  - Add an accessible region name.
  - Never show admin "Map unavailable" messages to visitors.
  - _(fin, ux · DR 10-14)_
- [ ] **Geocode hygiene.**
  - Skip the lookup when the name and address are unchanged.
  - Store a `_fetched` timestamp and refresh lat/lng after 30 days, per Google's caching terms. The place ID may persist.
  - _(fin, legal · DR 10-14)_
- [ ] **Remove dead code that would leak data or cost money if wired up.**
  - `Landing_Page_Map_Manager` (broken callback, duplicate geocode).
  - `get_user_geo_data()` (sends the visitor's IP over plain HTTP to geoplugin.net).
  - `Browser.php`, if it's unused.
  - The per-menu-item HEAD checks in `template-b/header.php`.
  - The template-c per-view browser geocoding.
  - Confirm the file deletions with Odell when doing this.
  - _(fin, legal, sec · DR 10-14)_

**Docs, legal text, and labels**
- [ ] **README corrections.**
  - **Client fields:** say they're required, and that the slug is the subdomain.
  - **Templates:** add Template A and say one template applies site-wide.
  - **Analytics:** mark it as Pro, to match the code.
  - **Install:** from the release zip, not `composer install`.
  - **Release:** fix the tag example.
  - **New sections:** "Subdomains" (wildcard DNS and SSL, permalink re-save), "Templates", and "External services" (Google, Brandfetch, GitHub, CDNs, with links to their terms).
  - **Author:** "Odell Duppins / Duppins Technology", plus a link to CREDITS.md.
  - **Opening line:** the new positioning line (marketing).
  - The text is in [the appendix](meeting-notes/2026-10-14-1951-baseline-review-appendix.md).
  - _(sup, legal, mkt · DR 10-14)_
- [~] **Plugin header.** Author, Author URI, Requires and the description are done (10-14b). Still to do: the `template-b/style.css` header.
  - `Author: Odell Duppins / Duppins Technology`
  - `Author URI: https://duppinstech.com`
  - `Requires at least: 5.9`
  - `Requires PHP: 7.4`
  - A real description.
  - Give `template-b/style.css` a proper header.
  - _(mkt, legal, ops · DR 10-14)_
- [ ] **`CREDITS.md` (shipped) and license headers.**
  - List every bundled third-party component and its license, as in [the appendix](meeting-notes/2026-10-14-1951-baseline-review-appendix.md).
  - Restore the MIT header on `payform.min.js`.
  - Swiper 5.3.0 (JS, `template-b/js/plugins/swiper.min.js` and `swiper.js`) and Swiper 6.0.2 (CSS, `swiper-bundle.css`): both MIT, © Vladimir Kharlampidi. _(legal · DR 10-16)_
  - Note that Carbon Fields is GPL-2.0-only, so no GPL-3.0-only or Apache-2.0 code may be added.
  - WOW.js 1.1.2 is MIT: do not upgrade past 1.1.2. Use the `graingert/WOW` fork if needed.
  - Animate.css: the bundled 2015 MIT build. Do not upgrade to 4.x, which is under the Hippocratic License 2.1 and isn't GPL-compatible. _(DR 10-14b)_
  - Before anyone adds the American Captain font files that Template C' `fonts.css` references, confirm the font's web-embedding license. _(DR 10-14b)_
  - _(legal · DR 10-14)_
- [x] **Remove client names from Template A admin labels, rendered HTML and CSS.** **Done 10-16** on `fix/owner-decisions-p0`: tokens renamed one-for-one (`layout1`, `layout2`, `layout-3`/`layout_3`) and files renamed. The only remaining client names are legacy stored-value aliases in `single-landing_page.php`, plus the `.html` mockups, which are not in the zip. Moving the mockups to a private repo is still to do (needs a destination).
  - **Labels:** "Standard (service area or services + contact column)", and "Use Heavy-duty for heavy-duty / equipment-transport footers". Add help text for the variant field.
  - **Rename** the `layout1`, `client_d` and `layout_3` slugs, classes and CSS files to neutral names, mapping the stored values.
  - _(mkt, sup, legal, prod · DR 10-14)_
- [ ] **Admin help text.**
  - **Templates tab:** say it applies to every page immediately and that each template has its own fields.
  - **Client Domain:** relabel to "Brand website", and warn that changing the client slug changes live URLs.
  - **"Client Phone":** add help text once the phone decision is made.
  - The text is in [the appendix](meeting-notes/2026-10-14-1951-baseline-review-appendix.md).
  - _(sup, prod · DR 10-14)_
- [~] **Validator messages** (raised to P0 by DR 10-16c: publishing is now blocked, so the notice must say what to fix). **Done 10-16:** labels for client, keyword and phone, and a kept-as-draft notice with a "Draft updated" message. **Still to do:** labels and tabs for template-specific required fields, and a notice that stays until the fields are fixed. Show field labels and which tab each is on, say the page was saved anyway, and keep the notice until the fields are fixed. _(prod, sup · DR 10-14)_
- [ ] **Draft preview on client subdomains.**
  - Allow draft and pending posts when `preview=true` and the user can edit the post.
  - Fall back to the default preview link when the slug is empty.
  - Verify on a WP site.
  - _(prod · DR 10-14)_
- [ ] **Template screenshots are page captures, not client logos.** Use 1200×900 hero captures, and one per Template A style or a montage. _(mkt · DR 10-14)_

**Accessibility and front end**
- [ ] **Contrast.**
  - Use the WCAG contrast formula in `get_contrast_text_color()`.
  - Compute the `.call-button` text and the mobile-menu text from their real backgrounds.
  - Template A small text should be at least 12px and at least 4.5:1.
  - Optionally, show an admin notice when a client's colors fail.
  - _(ux · DR 10-14)_
- [ ] **Images.**
  - Use `wp_get_attachment_image()` (dimensions, `srcset`, lazy loading).
  - Heroes get `fetchpriority="high"`. The template-b hero needs to become an `<img>` or be preloaded.
  - Don't print an `<img>` when there's no image.
  - _(ux · DR 10-14)_
- [ ] **Accessible names and focus.**
  - Social links get an `aria-label`, and logo `alt` text is the client name.
  - Template C "Apply Now" gets `alt="Apply now"`, and is hidden when there's no URL.
  - Visible `:focus-visible` outlines.
  - Template A FAQ buttons get `aria-expanded` and `aria-controls`.
  - _(ux · DR 10-14)_
- [x] **Warn when the server key is empty.** Show an admin notice when a browser key is set but no server key, so operators don't leave an unrestricted key in page HTML. _(DR 10-14b)_ _(fin)_ **Done 10-16.**
- [x] **Don't load jQuery on Template A.** Template A's `main.js` doesn't use it. Move `wp_enqueue_script('jquery')` into the fallback branch in `Assets_Manager` after checking that no client tracking code relies on it. _(DR 10-14b)_ _(ux)_ **Done 10-16.** Still loaded when the client's Header Tracking Code mentions jQuery.
- [x] **Load Swiper on Template B only when `reviews_html` has content,** and use a minified build. _(DR 10-14b)_ _(ux)_ **Done 10-16.** `swiper.min.js` is the official 5.3.0 build: the npm integrity hash was verified, and the vendored `swiper.js` is identical to the one in the same package.
- [x] **Fix the Template C mobile menu button.** It had no handler, so tapping it did nothing. **Done 10-16:** the button opens a panel, and "Services" in both menus lists the client's landing pages (shown only when there are more than one). The desktop dropdown is a disclosure button. _(DR 10-14b: ux · DR 10-16: prod, mkt, sup, ops, ux)_
- [ ] **Accessibility and Core Web Vitals audit** of each template on mobile, with a Lighthouse baseline. Moved from P2 to P1; the 10-14 UX report is the first pass. _(prod, ux · DR 10-14)_

## P2 — later

- [ ] **Register `post_updated_messages` for `landing_page`**, so editors see "Landing page draft updated" and "Landing page published" instead of the generic "Post …" messages. _(prod · DR 10-16c re-review)_

- [ ] **Move the Template A `.html` mockups to a private repo.** Needs a destination from Odell. _(legal, prod _(DR 10-16c)_)_
- [ ] **Remove core's tracking-toggle definitions** once Pro 1.0.3 is no longer supported. The support window is a decision for Odell. _(prod _(DR 10-16c)_)_
- [ ] **Narrow the CREDITS list now that payform is gone:** Bootstrap CSS; Bootstrap JS 4 and Popper (Template B); Swiper 5.3.0 JS and 6.0.2 CSS; Animate.css; WOW 1.1.2; Carbon Fields and the rest of `vendor/`. _(legal _(DR 10-16c)_)_

- [ ] **Map cost follow-up.** In the Core Web Vitals audit, record how far below the fold the Template B footer map sits. On short pages and tall screens it still loads on every view, and Google's crawlers can trigger it. If savings matter, add an optional "click to load map" mode or the free Maps Embed iframe. _(fin · DR 10-16)_
- [ ] **Template C mobile menu: add the Apply button to the panel.** _(prod, ux · DR 10-16)_

- [ ] **WordPress.org readiness.** `readme.txt` is empty.
  - Write it in WP.org format: stable tag, tested up to, changelog, FAQ, screenshots, and the external-services section.
  - **Blockers:** the self-updater (guideline 8), the `Update URI` header, CDN scripts, and the Get Pro banner, which can't be dismissed. A WP.org build needs Odell's decision.
  - _(mkt, legal · DR 10-14)_
  - **Feature list for the listing and README:** "Google Maps loads on scroll (lower Maps bill)" and "Loads only the scripts each template needs". _(mkt · DR 10-16)_
- [ ] **i18n pass.**
  - Regenerate `languages/landing-page-manager.pot`.
  - Wrap the remaining strings in the templates **and core**: `Client_Taxonomy`, `Landing_Page_CPT`, `_Enhancements`, `Plugin_Settings`, `admin.js`, and the dashboard menu labels.
  - Fix the `'template-b'` and `'client_g'` text domains.
  - _(sup, mkt, legal · DR 10-14)_
- [ ] **Template contract doc.** Document the required files, field registration, and helper API for new templates. `/scaffold` uses this contract.
- [ ] **Consolidate duplicated vendor assets** (bootstrap, swiper, jquery-migrate) that are copied into every template folder.
- [ ] **Upgrade routine.**
  - Store `lpmanager_version` and flush rewrites once when it changes.
  - Then fix the rewrite slug: `Rewrite_Rules` hooks before the post type exists, which builds a stray `/landing/` route. That fix needs `security-reviewer`.
  - _(ops · DR 10-14)_
- [ ] **CI builds the zip on PRs.**
  - Fail if the zip contains `.claude/`, `CLAUDE*.md`, `PLAN.md`, `meeting-notes/` or template `.html` files.
  - Add `vendor/league/color-extractor/tests` to `.distignore`.
  - _(ops · DR 10-14)_
- [ ] **Template upload: cap the uncompressed size** (zip bomb). This touches `handle_upload`, so it needs `security-reviewer`. _(sec · DR 10-14)_
- [ ] **Updater: answer "View details" from GitHub, and allow the auto-update toggle.**
  - Add a `plugins_api` handler for this slug built from the cached release, so the details modal can't show a WordPress.org listing under the same name.
  - Add a `no_update` entry when the site is current, so the Plugins screen shows the auto-update toggle.
  - **Review:** `security-reviewer`. _(DR 10-14b)_ _(sec, ops, prod)_
- [ ] **Harden CSS `url()` values in the Template C templates** (`single-landing_page.php:18,180,244`, `404.php:12`). `esc_url` doesn't stop a quote breaking out of the `url('…')` string. Not exploitable today. _(DR 10-14b)_ _(sec)_
- [ ] **Template A: load only the layout stylesheet for the page's style variant.** _(DR 10-14b)_ _(ux)_
- [ ] **Reduced motion for smooth scrolling and FAQ transitions** in the Client B templates. _(DR 10-14b)_ _(ux)_
- [ ] **CSV export uses the last applied filter, not the current dropdown values.** Relabel the button "Export CSV (current filter)", or read the dropdowns when it's clicked. Also check that keyword filtering works in the list itself, not only in the export. _(DR 10-14b)_ _(prod, sup)_
- [ ] **Admin polish.**
  - Template cards become focusable radio or `aria-pressed` buttons.
  - The upload input should be visually hidden but still keyboard-reachable.
  - Render the upload spinner.
  - Show errors in a `notice` with `role="alert"` instead of `alert()`.
  - Make the vendor-missing notice operator-friendly.
  - _(ux, sup · DR 10-14)_
- [ ] **Template metadata consistency.** Use one "Client B" spelling, and describe templates by what they contain. Remove the template-c `style.css` "theme" wording. _(mkt · DR 10-14)_
- [x] **CONTRIBUTING.md:** add the `template/` and `security/` branch prefixes. Done 10-16, together with `perf/` in `pr.md`. _(sup · DR 10-14, prod · DR 10-16)_

## Done
- [x] GitHub-release updater with WP.org toggle (v1.0.1)
- [x] Manual "check for updates" plugin-row action (v1.0.2)
- [x] Subdomain AJAX fix and normalized GitHub update install folder (v1.0.5)
- [x] SDLC and Claude Code scaffolding (2026-10-14)
- [x] v1.0.6: template upload security, CI-built release zip, uninstall, repo move, Template A template (2026-10-14)
- [x] Department reviewers, `/department-review` and `meeting-notes/` (2026-10-14)
- [x] P0 batch from the baseline review, with fixes from its department review (`fix/review-p0-batch`, 2026-10-14). The smoke test and CI proof are still open.
