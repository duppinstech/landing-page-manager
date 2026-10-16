# Architectural decisions

Append-only log. Format: `## YYYY-MM-DD — Title`, then Context / Decision / Consequences.

## 2026-03 — Pro add-on in a separate repository
**Context:** Analytics and conversion tracking are paid features.
**Decision:** Core exposes hooks; Pro hooks in from its own repo. `lpmanager_is_premium()` checks a function only Pro defines, so setting a constant can't unlock Pro.
**Consequences:** No Pro code in this repo. Core UI must degrade gracefully when Pro is absent.

## 2026-03 — Self-hosted updates through GitHub Releases
**Context:** The plugin isn't on WordPress.org yet, and client sites need updates.
**Decision:** `Update_Checker` reads the latest GitHub release, with a settings toggle for GitHub, WP.org, or both. Installs are normalized to the `landing-page-manager/` folder.
**Consequences:** Every tag is a production deploy, so releases need the `/deploy` checklist.

## 2026-03 — Commit vendor/
**Context:** Sites install from the release zip and have no Composer.
**Decision:** `vendor/` is committed and shipped.
**Consequences:** Never hand-edit vendor. Dependency bumps are deliberate commits.

## 2026-10-14 — Template upload is plugin-install privilege
**Decision:** Uploading a template requires `install_plugins` and its own nonce. Archives are extracted into `templates/.staging-*` and moved into place with `rename()`. Only the direct filesystem method is supported.
**Consequences:** Non-super-admins on multisite, and sites with `DISALLOW_FILE_MODS`, can't upload templates. This is intended, because templates are executable PHP.

## 2026-10-14 — Release zip is built by CI, not committed
**Decision:** `.github/workflows/release.yml` builds `landing-page-manager.zip` from the tagged commit, using the committed `vendor/` and the exclusions in `.distignore`, and creates the release with the asset attached in one step.
**Consequences:** Never create a release by hand before CI runs. A release without the asset makes sites fall back to the GitHub zipball.

## 2026-10-14 — Claude Code / SDLC scaffolding
**Decision:** Adopted CLAUDE.md, AGENTS.md, INTENT.md, PLAN.md, and the `.claude/` rules, commands, skills, agents, hooks, memory, and workflows. Hooks enforce PHP lint, block secret and vendor writes, and run phpcbf when it's available.

## 2026-10-14 — Department review gate
**Context:** Security was the only review after development. Legal (licensing, disclaimers, privacy), pricing, docs, release safety and UX were checked ad hoc or not at all.
**Decision:** Added read-only department reviewers (`legal-`, `finance-`, `product-`, `marketing-`, `support-docs-`, `release-ops-`, and `ux-reviewer`) alongside `security-reviewer`. `/department-review` runs them in parallel once development is done and produces one sign-off table. It is wired into the feature-build, bug-fix and code-review workflows, the `/deploy` preflight, the PR template, and the merge gates.
**Consequences:** Any `block` verdict stops the merge or release. Only Odell can waive High security or legal findings. Business facts the agents rely on are in `.claude/memory/business-context.md` (public-safe) and in `CLAUDE.local.md` (private numbers).

## 2026-10-14 — Approved department-review items are worked without waiting
**Decision (Odell):** Routine items that a department review approves into PLAN.md are open to work right away. Only "Decisions needed (Odell)" items wait for him. The safety boundaries (pushing to main, tags and releases, deleting files, history rewrites) still need his go-ahead.

## 2026-10-14 — Updates come only from GitHub until a WordPress.org listing exists
**Context:** The `landing-page-manager` slug is unclaimed on WordPress.org, and the plugin had no `Update URI`. Whoever published that slug could push code to every site.
**Decision (Odell):** Close the gap now. Add `Update URI` and use GitHub Releases as the only update source. Plan for WordPress.org later: the free core goes on WordPress.org, and Pro stays the paid add-on. Revisit this decision when the listing is created.

## 2026-10-16 — Footer map: lazy-loaded Maps JS, not the Embed iframe
**Decision:** The Template B footer map loads Maps JS when it comes within 300px of the viewport, not as the free Maps Embed iframe. Lazy-loading keeps the custom styling, marker and info window, and a load is billed only when a visitor gets near the footer.
**Consequences:** Short pages and tall screens still load it on every view. Revisit if Maps loads stay a top cost line (PLAN P2 "Map cost follow-up").

## 2026-10-16 — jQuery is a template dependency, not a global
**Decision:** Templates with a manifest declare `jquery` as a script dependency when they need it. Core loads jQuery itself only for templates without a manifest (uploaded templates), when the client's Header Tracking Code looks like it uses jQuery or GTM, or when the `lpmanager_load_jquery` filter returns true.
**Consequences:** Template A pages ship without jQuery for logged-out visitors. A jQuery tag inside GTM that detection can't see needs the filter. Logged-in users always get jQuery through heartbeat and wp-auth-check, so test this logged out.


## 2026-10-16 — Owner Q&A on outstanding decisions (Odell)
Answered in one Q&A session; PLAN.md "Decisions needed" items are marked with each answer.
- **Phone:** the per-page "Client Phone" overrides the client's "Contact Phone" everywhere. One resolved number is used for display and `tel:`.
- **Additional Scripts:** print them only when saved by a user with `unfiltered_html`, re-enable `lpmanager_landing_page_footer`, and add an allowlist filter.
- **Templates:** core stays global-only. Per-client template choice is a Pro feature, through an `lpmanager_template_for_post` filter.
- **Reviews (interim):** a timestamp-only lock replaces the 12-hour content cache. Storage and the 4–5★ filter wait for the attorney.
- **Analytics:** Pro-only. Fix the docs and move `tracking.js` and the toggles to Pro.
- **Pro button (interim):** "coming soon — contact Duppins Technology for early access", linking to duppinstech.com. Price and licensing are still open.
- **Client templates:** reusable Duppins products. Anonymize client names, bill only for customization, and ship a neutral default.
- **Tracking defaults:** all trackers stay on by default. Legal's recommendation (behavioral trackers off, plus a consent hook) was declined.
- **Template C images:** rights confirmed, so they stay. The copyright line will come from the site or client name.
- **Template A:** a phone icon on the mobile call button, and honor client colours.
- **API costs:** clients supply their own Google and Brandfetch keys.
- **Live sites:** none use WordPress.org-only updates or uploaded templates. Some Template A clients use GTM or jQuery tags, so a per-client "Tracking tags need jQuery" checkbox will be added.
- **Places API (New):** migrate, after the attorney question.
- **Vendored files:** delete the unused ones. `swiper.js` goes one release later.
- **Iframes:** allow them in rich-text fields from a filterable host allowlist.
- **Server-key warning:** dismissible per user for 30 days.
- **Internal docs:** stay public.
- **Smaller calls:** all yes. Incomplete pages stay drafts, landing pages get their own capability type, uninstall removes the orphaned template meta, and the v1.0.6 release notes get corrected.
- **Still open:** Template C mobile menu colours, the Pro price and licensing, and the attorney questions.

## 2026-10-16 — Raw-HTML fields need `unfiltered_html`, enforced in Carbon's save filters
**Decision:** Additional Scripts and Header Tracking Code can only be changed or deleted by users with `unfiltered_html`. This is enforced in `carbon_fields_should_delete_field_value_on_save` and `carbon_fields_should_save_field_value`, which cover the settings form, the term screens and Carbon's REST route. The stored value is captured in the delete filter, because Carbon deletes before it saves.
**Consequences:** On sites with `DISALLOW_UNFILTERED_HTML`, nobody can edit these fields. This is an open question for Odell.

## 2026-10-16 — Additional Scripts print only with a keyed approval
**Decision:** Printing requires an HMAC of the exact value, keyed with `wp_salt('auth')`. A trusted save of a new value approves it. Any other value needs an explicit Approve, which is bound to the reviewed value.
**Consequences:** Rotating the salts, or cloning a site to new salts, revokes approval until an admin approves again.

## 2026-10-16 — Rich-text iframes are rebuilt, never passed through
**Decision:** `lpmanager_kses_rich_text()` reads each iframe with `WP_HTML_Tag_Processor` and requires an anchored per-provider https pattern match (`lpmanager_iframe_src_patterns`). It then rebuilds approved iframes with fixed attributes. Before WP 6.2, and in site-kses fallbacks, all iframes are removed.


## 2026-10-16 — Release v1.0.7 without the staging smoke test (Odell)
**Decision (Odell):** "Merge and release." The stack (`chore/template-field-cleanup` → `fix/review-p0-batch` → `perf/maps-lazy-load-p1-batch` → `fix/owner-decisions-p0`) merges to `main` and ships as v1.0.7.
**Waived:**
- the staging smoke test (steps 1–41), including the per-site phone audit (step 26) and the `DISALLOW_UNFILTERED_HTML` check (step 31);
- two open P0 decisions, which ship as currently built and documented in the upgrade notes:
  - who can change Header Tracking Code: users with `unfiltered_html`, including Editors on a single site;
  - Template A's generic fallback colours, which apply as client colours.

All four batches passed department review with no `block`.
**Consequences:** Live sites receive v1.0.7 through the updater. Check live Template A pages (colours and phone numbers) and Additional Scripts approval right after the update.

## 2026-10-16 — Release v1.0.8 (Help button) without department review (Odell)
**Context:** v1.0.8 adds only the Help button on the plugin's admin screens. Open P0s in PLAN.md (Template A fallback colours, who can change Header Tracking Code, v1.0.7 post-release checks) concern behaviour already live in v1.0.7 and are not changed by it.
**Decision:** Odell asked to commit, push and release with no review. Department review was not run, and the open P0s were treated as waived for this release. The contextual Help tab (never committed) was dropped in favour of the button.
**Consequences:** The Help button has not been checked in a live wp-admin. Check it on a live site after the update. The open P0s still apply to the next release.
