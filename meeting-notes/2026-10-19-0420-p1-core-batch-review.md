# Department review: P1 core batch
**When:** 2026-10-19 04:20 EDT · **Scope:** `fix/p1-core-batch` vs `main` (5 commits, plus the fixes below, uncommitted at review time) · **Chair:** main session · **Owner:** Duppins Technology

Four departments (legal, support, ops, UX) stopped partway on an account usage limit and were resumed later the same morning. All eight finished.

## Attendance and verdicts
| Department | Verdict | Findings | Summary |
|---|---|---|---|
| Security | ok | 2 Low | Third pass. All earlier findings fixed; two Lows (list-notice count, preview args) fixed here. |
| Legal | fix-before-merge | 2 Medium, 4 Low | Uninstall must delete Google coordinates; CHANGELOG "Privacy" entry restated. Brandfetch colour storage goes to the attorney. |
| Finance | ok | 2 Medium, 4 Low | Net reduction in Google and Brandfetch calls; new refresh is bounded. A failed refresh shouldn't remove a map for good. |
| Product | fix-before-merge | 2 Medium, 5 Low | Upgrade note for coordinate deletion; show which API key is saved. |
| Marketing | fix-before-merge | 3 Medium, 5 Low | CHANGELOG claims broader than the code (subdomains, broken templates). |
| Support | fix-before-merge | 2 High, 6 Medium, 6 Low | Upgrade notes for subdomain scoping and coordinate refresh; stale Help widget and guide text. |
| Ops | fix-before-merge | 1 High, 2 Medium, 4 Low | Main-domain and stale-subdomain URLs that worked by accident would 404; rollback note. |
| UX | ok | 1 Medium, 7 Low | Wrong-host visitors hit a dead-end 404; notice tone; screen-reader text. |

## Highlights
- **Old landing-page addresses now redirect instead of 404.** 1.0.15 served any landing page on any of the site's hosts by accident. 1.0.16 serves a page only on its own client's subdomain and 302-redirects the others there with the query string kept. This default is built; Odell is asked to confirm it.
- **Coordinates:** refreshed every 28 days, deleted only when a refresh fails past 30 days, then retried for three days; always deleted on uninstall. Odell should check the Google Maps server key on both live sites before updating.
- **Brandfetch colours are stored for good**, which Brandfetch's terms may not allow. This predates the branch, and the question goes to the attorney.
- All other findings were routine and were fixed on the branch (wording, upgrade notes, help text, notice tone, accessibility, retry logic).

## Findings and recommendations by department
### Security
- The list notice's "and N more" counted drafts the user can't edit → count only the user's own drafts without `edit_others_posts`. **Outcome:** Approved, fixed on the branch.
- An array-valued preview argument from another plugin could throw on PHP 8 → drop non-scalar arguments. **Outcome:** Approved, fixed on the branch.
- Search, feeds and the zip-bomb cap. **Outcome:** Already tracked (P2).

### Legal
- Uninstall kept Google latitude and longitude with nothing left to refresh them → always delete them (the place ID stays). **Outcome:** Approved, fixed on the branch.
- The `### Privacy` CHANGELOG heading and its statement of Google's terms → move it under Changed, saying "at the time of writing". **Outcome:** Approved, fixed on the branch.
- Refreshing exactly at 30 days, once a day, in batches can overshoot → refresh at 28 days, keep deleting at 30, and re-run full batches. **Outcome:** Approved, fixed on the branch.
- Coordinates aren't refreshed while the plugin is deactivated → document it. **Outcome:** Approved, documented in the CHANGELOG.
- Brandfetch colours are saved permanently without a refresh (section 5.1) → attorney review; options are a 30-day refresh, operator-confirmed suggestions, or logo colours only. **Outcome:** Forwarded → Odell.
- An optional privacy-tutorial line about client data sent to Google and Brandfetch. **Outcome:** Approved → PLAN (P2).

### Finance
- Net effect: fewer billed calls (unchanged saves make none; Brandfetch is skipped without a key and cached). The new refresh is about one Details call per client per month and bounded per run. No pricing change.
- One failed refresh deleted coordinates for good → retry for three days. **Outcome:** Approved, fixed on the branch.
- Live ads pointing at the wrong subdomain would hit a 404 → check ad URLs before release, and add an upgrade note. **Outcome:** Approved (note written, and the redirect removes the 404); the check is in the P0 pre-release item for Odell.
- Coordinates are looked up even for templates that never read them; no-match lookups repeat; Brandfetch 429s aren't cached. **Outcome:** Approved → PLAN (P2).

### Product
- Coordinates that can't be refreshed are deleted, and footer maps vanish → upgrade note, server-key help text, and a pre-release key check. **Outcome:** Approved, fixed on the branch (the check is in the P0 item).
- Password fields don't say which key is saved → help text names the last four characters and says to clear the field before pasting. **Outcome:** Approved, fixed on the branch.
- A red error on every unfinished draft; no "still live" wording → a warning while building, an error after a refused publish, and a "stays published" heading. **Outcome:** Approved, fixed on the branch.
- The list banner can't be dismissed; template fatals can still lock out wp-admin; refresh failures are silent. **Outcome:** Approved → PLAN (P2).
- PLAN cleanups (stale "Still to do" text, superseded preview bullets) and a README note that `lp_client` isn't set during previews. **Outcome:** Approved, done.

### Marketing
- "A client's subdomain shows only that client's pages" overstates it (search and feeds aren't scoped yet) → reworded around the page's own address. **Outcome:** Approved, fixed on the branch.
- "No longer takes down wp-admin" is too absolute → "Most errors…", with the exception stated. **Outcome:** Approved, fixed on the branch.
- Notice wording ("saved" twice, "for this template" over core fields), and upgrade-note clarity. **Outcome:** Approved, fixed on the branch.
- Never call the keys "encrypted" or "secure storage". **Outcome:** Approved; the CHANGELOG says "hidden on screen; stored as before".
- Screenshots, `website.md` and the WordPress.org feature list. **Outcome:** Approved → PLAN (P2). Updating duppinstech.com is Odell's.
- Release-note draft: in the appendix.

### Support
- Upgrade notes for subdomain scoping (and cache purge), coordinate refresh and deletion, and the notice on live pages. **Outcome:** Approved, fixed on the branch.
- Stale Help widget notes (editor, clients, client editor, API keys) and guide pages (editor, API keys, client editor, list). **Outcome:** Approved, fixed on the branch.
- The colour change doesn't touch existing clients; plural handling for "and N more"; the template error should mention redeploying. **Outcome:** Approved, fixed on the branch.
- Whether deleting coordinates on the live sites is acceptable. **Outcome:** Settled by legal's terms reading plus the retry; the server-key check is in the P0 item.
- FAQ macros: in the appendix.

### Ops
- URLs that served pages by accident in 1.0.15 (the main domain, `www.`, renamed client slugs) would 404 → redirect them to the page's own address, but on the main host only where WordPress returns a 404, so its own routes (feeds, robots.txt, sitemaps) always win. **Outcome:** Approved as a compatibility fix and built (security re-review requested); confirmation forwarded → Odell.
- First refresh deletes coordinates on any one failure and never retries → three-day retry. **Outcome:** Approved, fixed on the branch.
- Rollback to 1.0.15 would wipe two keys on the first Settings save → rollback note. **Outcome:** Approved, fixed on the branch.
- Page caches keep old responses → purge note. **Outcome:** Approved, fixed on the branch.
- Batch of 50 calls × 10 s may outlast PHP limits → batch of 20 with follow-up runs. **Outcome:** Approved, fixed on the branch.
- Network deactivation clears one site's event. **Outcome:** Approved → PLAN (P2).
- CI hasn't run because the branch isn't pushed. **Outcome:** Merge gate, in the P0 item.
- Smoke test: in the appendix.

### UX
- A wrong-host visitor hits a dead-end 404 → redirect, or a branded 404. **Outcome:** Approved; the redirect is built (see ops). A branded 404 is declined for now: with the redirect, only pages with nowhere to go 404.
- Notice tone, screen-reader "(opens in a new tab)", the arrow in field locations, and the unused meta cache in the list query. **Outcome:** Approved, fixed on the branch.
- A Show/Hide button for keys, and a Chrome "suggest strong password" check; the Brandfetch error shown in the client editor; the list banner's age limit. **Outcome:** Approved → PLAN (P2).

## Decisions
- **Approved into PLAN.md:** a P0 pre-release check for Odell (server key, Client column and ad URLs, access logs, cache purge, CI); P2s for refresh-failure notice, list-banner dismissal, template-fatal safe mode, coordinates only for templates that need them, no-match memory and Brandfetch 429 cache, key Show/Hide and Chrome check, Brandfetch error in the client editor, network deactivation, privacy tutorial line, and marketing assets.
- **Forwarded to Odell:**
  - Brandfetch colours are stored for good. Ask the attorney, then choose a 30-day refresh, operator-confirmed suggestions, or logo colours only.
  - Confirm that old landing-page addresses redirect (built) rather than 404.
- **Declined:** a branded 404 page (UX), superseded by the redirect; it can come back if Odell chooses 404s.
- **Gate:** no department blocked. The fix-before-merge items are fixed on the branch.

## Security re-review of the fixes
- Round 1 on the fixes: **fix-before-merge**, two Mediums. Redirecting on the main host during request parsing could take over WordPress's own one-level routes (feeds, robots.txt, sitemaps) when someone publishes a landing page with that slug; and a failing batch could re-run the coordinate refresh every few minutes. Both fixed: main-host redirects now run only on WordPress 404s (`template_redirect`, before `redirect_canonical`), and each client's refresh attempt is stamped and skipped for 20 hours.
- Round 2: **ok**. Its one Low (retries could be used up by follow-up runs) is fixed too: retries are also once per 20 hours. A note about one-level routes on client subdomains was added to the existing P2 client-hosts item.
- Still before a PR: push the branch and confirm CI (7.4/8.1/8.3).
