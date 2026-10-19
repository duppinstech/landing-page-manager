# Department review: Maps lazy-load and P1 front-end batch (`perf/maps-lazy-load-p1-batch`)
**When:** 2026-10-16 00:09 EDT · **Scope:** `git diff 24eb45e..7a8892a`, six commits stacked on `fix/review-p0-batch`. They cover the P0 Maps JS lazy-load and four P1s: no jQuery on Template A, Swiper only with reviews (minified), the Template C mobile menu, and the empty-server-key notice. · **Chair:** main session · **Owner:** Duppins Technology

## Since the last meeting
- **CI proof (P0) closed.** On throwaway draft PR #4, the branch passed lint on PHP 7.4, 8.1 and 8.3 as-is, then failed on all three with a deliberate parse error. The PR was closed and its branch deleted.
- `chore/template-field-cleanup` and `fix/review-p0-batch` were pushed to GitHub. No PRs are open and nothing went to `main`.

## Attendance and verdicts
| Department | Verdict | Findings | Summary |
|---|---|---|---|
| Security | **ok** | 3 info | Nothing new. The key is safely encoded in the inline script, and the editor-only messages are escaped. Header Tracking Code lockdown is already tracked. |
| Legal | **ok** | 5 Low | Swiper 5.3.0 is MIT with its banner kept. No Maps terms issue. Add both Swiper versions to CREDITS, and add the unused `swiper.js` to the deletion question. |
| Finance | **ok** | 6 | Saves about $7 × (1 − r) per 1,000 Template B views above the free tier, where r is the share of visitors who reach the map. No pricing change. GTM jQuery tags are a risk to attribution. |
| Product | fix-before-merge | 9 | The mobile "Services" link is dead. The smoke test must cover the whole stack. Found pre-existing Template C debug logging and an early `return`. |
| Marketing | fix-before-merge | 9 | The same dead link. CHANGELOG claims and notice copy needed tightening. Release-note draft below. |
| Support / docs | fix-before-merge | 6 | The same dead link. A jQuery upgrade note was missing. Clearer editor messages. FAQ drafted. |
| Ops / release | fix-before-merge | 7 | The jQuery check misses GTM and `$.`. CI must run on the pushed branch. A skipped manifest entry could drop its dependents. Smoke steps 16–24 added. |
| UX | fix-before-merge | 7 | The same dead link. The auto-opened info window may take focus. The 921px gap where neither header shows. A weak focus style. |

## Highlights
- **Five departments independently caught the same bug.** The new mobile menu's "Services" link pointed at `#services`, and the page has no element with that id. It's fixed properly: both menus now list the client's other landing pages. Along the way, a pre-existing problem was removed: per-view `error_log()` calls, and an early `return` that cut the Template C header short and showed "No client assigned" to visitors.
- **The jQuery change can break tracking tags without anyone seeing an error.** Detection now also catches `$.` and GTM snippets. A new `lpmanager_load_jquery` filter lets a site force jQuery on, and the upgrade notes say so. Odell is asked to confirm whether any live Template A client uses GTM or jQuery tags.
- **Security, legal and finance: ok.** No pricing change, and the new vendored Swiper build checks out (MIT, verified against npm).
- **Nothing blocks.** Every fix-before-merge item was fixed on the branch the same night under the standing rule. The merge gate is now the staging smoke test for the whole stack.

## Findings and recommendations by department

### Security
- The browser key is in the inline script whether or not the map loads. That's the same exposure as before, and it's safely encoded. **Outcome:** Declined (informational; browser keys are public by design, and the referrer restriction is the control).
- The editor-only messages could leak through a page cache that serves logged-in renders to everyone. **Outcome:** Declined (cosmetic; the text reveals nothing sensitive). Noted in the smoke test.
- The Header Tracking Code is printed raw. **Outcome:** Already tracked (P1 "Lock down Header Tracking Code").

### Legal
- Add Swiper 5.3.0 JS and 6.0.2 CSS to CREDITS. **Outcome:** Approved → PLAN (folded into the CREDITS item).
- `swiper.js` is now unused but still ships. **Outcome:** Forwarded → Odell (added to the "Delete the unused vendored files?" question).
- Privacy text: "loads Google Maps … as you scroll to it". **Outcome:** Approved → fixed.
- Lazy-loading meets the Google Maps Platform terms. Don't hide Google's attribution. **Outcome:** Noted.

### Finance
- Short pages, tall screens and crawlers still trigger a load. **Outcome:** Approved → PLAN (P2 "Map cost follow-up").
- The jQuery check misses GTM tags. **Outcome:** Approved → fixed (filter and wider detection).
- Who pays for API usage on installs Duppins runs, and how client template fixes are billed. **Outcome:** Already tracked (Odell).
- **Pricing recommendation:** no change.

### Product
- Dead mobile "Services" link. **Outcome:** Approved → fixed.
- Run the smoke test once at the top of the three-branch stack, then merge in order. **Outcome:** Approved → PLAN (the smoke-test item was renamed and extended).
- Add an `lpmanager_load_jquery` filter. **Outcome:** Approved → fixed.
- The notice link says "API Keys" but opens the default tab. **Outcome:** Approved → fixed (the link now says "Go to Settings", and the text names the tab).
- Should the server-key notice be dismissible? **Outcome:** Forwarded → Odell (P2).
- The mobile panel stays open after resizing to desktop. **Outcome:** Approved → fixed.
- Per-view `error_log()` and the early `return` in the Template C header. **Outcome:** Approved → fixed.
- Template B shows an empty Reviews section. **Outcome:** Approved → fixed.
- The `perf/` prefix isn't documented. **Outcome:** Approved → fixed (`pr.md` and CONTRIBUTING; this also closed the older CONTRIBUTING item).
- Record the map and jQuery decisions. **Outcome:** Approved → done (`decisions.md`).

### Marketing
- Dead "Services" link, and the matching CHANGELOG claim. **Outcome:** Approved → fixed.
- CHANGELOG copy: "can't be restricted", "most visitors", and the size claim with figures for pages with reviews. **Outcome:** Approved → fixed.
- Notice and map-message copy. **Outcome:** Approved → fixed.
- Add the new features to the listing and README feature list. **Outcome:** Approved → PLAN (folded into the WordPress.org readiness item).
- Don't claim "faster" or "Quality Score" until the Lighthouse baseline exists. **Outcome:** Noted (already tracked in the CWV audit).

### Support / docs
- jQuery upgrade note. **Outcome:** Approved → fixed (it documents the filter).
- Document a `<!-- needs jQuery -->` marker. **Outcome:** Forwarded → Odell (P2; the recommendation is to document the filter only).
- Dead "Services" link. **Outcome:** Approved → fixed.
- Editor map messages and the CHANGELOG jQuery wording. **Outcome:** Approved → fixed.
- The notice can't be dismissed. **Outcome:** merged into product's forwarded question.
- FAQ drafted. **Outcome:** saved in the [appendix](2026-10-16-0009-maps-p1-batch-review-appendix.md#support-faq).

### Ops / release
- The jQuery check misses `$.` and GTM. **Outcome:** Approved → fixed. Confirming live client tags is Forwarded → Odell (P1).
- CI hasn't run on this branch. **Outcome:** Approved → the branch gets pushed after these notes, and CI is proven via PR.
- The smoke test is still open, and all three branches will ship together. **Outcome:** Already tracked (the item was extended).
- Upgrade notes for the server-key warning and for purging caches. **Outcome:** Approved → fixed.
- A dependency on a skipped manifest entry made WordPress drop the dependent script. **Outcome:** Approved → fixed.
- Smoke steps 16–24. **Outcome:** saved in the [appendix](2026-10-16-0009-maps-p1-batch-review-appendix.md#smoke-test-steps-1624).
- **Rollback:** safe. No data is written.

### UX
- Dead "Services" link, and the mobile menu dropped the client's page list. **Outcome:** Approved → fixed.
- The auto-opened info window may move focus. **Outcome:** Approved → fixed (`shouldFocus: false`). Check it in the smoke test.
- Placeholder colour for the map box. **Outcome:** Approved → fixed.
- Weak mobile focus style. **Outcome:** Approved → fixed (2px outline).
- The 921px gap where neither header shows. **Outcome:** Approved → fixed.
- Should the mobile panel use the client's brand colours? **Outcome:** Forwarded → Odell (P2 design call).

## Decisions
- **Approved and fixed on the branch (5 commits):**
  - `f019944`: Template C menus and debug logging.
  - `eb4efac`: Template B map focus, placeholder, and empty Reviews.
  - `b8d8bf2`: jQuery filter, detection, and manifest dependencies.
  - `d5dccbc`: notice and privacy copy.
  - `8993159`: upgrade notes, filter docs, and the `perf/` prefix.
- **Approved → PLAN (open):** the stack-wide smoke test (P0, extended); the map cost follow-up (P2); the Apply button in the Template C mobile panel (P2); CREDITS entries for Swiper; the feature list for the WordPress.org listing.
- **Forwarded to Odell:**
  1. Do any live Template A clients use GTM or jQuery tags? (P1)
  2. Make the server-key warning dismissible? (P2)
  3. Should the Template C mobile menu use client colours? (P2)
  4. Document a "needs jQuery" marker? (P2)
  5. Add `swiper.js` to the files-to-delete question (existing item).
- **Declined:** two security informational notes. Browser keys are public by design, and the editor-message cache leak is cosmetic.
- **Waivers:** none.
