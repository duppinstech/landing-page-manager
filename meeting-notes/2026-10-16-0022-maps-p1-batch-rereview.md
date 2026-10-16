# Department re-review: Maps lazy-load and P1 batch fixes (`perf/maps-lazy-load-p1-batch`)
**When:** 2026-10-16 00:22 EDT · **Scope:** `git diff 7a8892a..7bfef21`, the fixes from the [00:09 review](2026-10-16-0009-maps-p1-batch-review.md) · **Chair:** main session · **Owner:** Odell Duppins

Only the five departments that asked for fixes re-reviewed. Security, legal and finance were ok at 00:09 and the fixes don't touch their areas. CI on draft PR #5 passed on PHP 7.4, 8.1 and 8.3.

## Attendance and verdicts
| Department | Verdict | Summary |
|---|---|---|
| Product | **ok** | All earlier findings were resolved. New: the "No client" editor message pointed to a box that the save overwrites; the jQuery check over-matched gtag.js; the Services menu showed for a single page. |
| Marketing | fix-before-merge | The copy said "other pages", but the code included the current page. Two release-note bullets were wrong. Minor copy nits. |
| Support / docs | fix-before-merge | The same "other pages" mismatch and editor message. A public "Developer hooks" README section was suggested. A FAQ was added. |
| Ops / release | fix-before-merge | **Performance regression:** `'fields' => 'ids'` cost about 2 queries per page in the Template C menu. Use the client term. Make the manifest dependency check order-independent. |
| UX | fix-before-merge | The desktop dropdown failed WCAG 1.4.13 and 4.1.2 (no Escape, no `aria-expanded`, misleading `aria-haspopup`). The current page wasn't visibly marked. There was an invalid `aria-expanded` on the `<ul>`. Empty-after-kses reviews. |

## Highlights
- **Ops caught a performance regression in the fix itself.** The new Template C menu query loaded IDs only, so every page in the list cost extra queries. It now loads full posts, which prime the caches: one query plus one term query, whatever the number of pages.
- **The desktop "Services" dropdown is now a proper disclosure button.** It has `aria-expanded`, Escape and focus-out close it, and it still opens on hover.
- **jQuery detection is tighter.** It matches GTM containers (`gtm.js`, `GTM-`) only, not the gtag.js snippet that most ad clients use, so Template A keeps its saving.
- All findings were routine and fixed the same night. **Nothing was forwarded or blocked.**

## Findings and outcomes
- **Product:**
  - "No client" message points to the Client Name field. **Approved → fixed.**
  - gtag over-match. **Approved → fixed.**
  - Services only with more than one page. **Approved → fixed.**
  - Order-dependent manifest dependencies. **Approved → fixed** (two passes).
  - Tablet tap check. **Approved → added to the smoke-test item.**
  - Garbled PLAN items. **Approved → rewritten.**
- **Marketing:**
  - "Other pages" copy. **Approved → fixed** (code and copy now agree).
  - Release-note bullets. **Approved → fixed below.**
  - Notice "those lookups" and privacy "when the map comes into view". **Approved → fixed.**
  - Client Name field vs. Clients box mismatch in Template C. **Approved → fixed** (ops' term-first change resolves it).
  - Screenshots. **Already tracked.**
- **Support:**
  - Editor message. **Approved → fixed.**
  - "Other pages" wording. **Approved → fixed.**
  - PLAN entry wording. **Approved → fixed.**
  - README "Developer hooks". **Approved → added.** Support tagged it [needs Odell], but it's in-repo documentation of existing hooks, so it's routine under the triage rules.
  - New FAQ entry. **Approved → below.**
- **Ops:**
  - Query regression. **Approved → fixed.**
  - Term vs. meta source. **Approved → fixed** (term first, meta fallback).
  - CHANGELOG wording. **Approved → fixed.**
  - Order-independent dependencies. **Approved → fixed.**
- **UX:**
  - Disclosure button pattern. **Approved → fixed.**
  - Visible `aria-current` style. **Approved → fixed.**
  - Hide Services for a single page. **Approved → fixed.**
  - Invalid `aria-expanded` on the `<ul>`. **Approved → removed.**
  - Reviews check after `wp_kses_post`. **Approved → fixed** (template and manifest).

## Corrections to the 00:09 appendix
The [00:09 appendix](2026-10-16-0009-maps-p1-batch-review-appendix.md) is kept as written. Use these in place of the items they name.

**Smoke step 16, extra check:** a Header Tracking Code that contains only the gtag.js snippet (`googletagmanager.com/gtag/js`) must **not** load jQuery. A GTM container snippet (`gtm.js`, `GTM-XXXX`) must load it.

**Smoke step 22, Template C menus.** This replaces the step in the 00:09 appendix.
- At 375px:
  - The toggle opens the panel, sets `aria-expanded="true"`, and shows the close icon.
  - "Services" lists the client's landing pages, and the current page has `aria-current` with a visible marker.
  - Tapping a link closes the panel. Escape closes it and returns focus.
  - Nothing is clipped, and the panel doesn't appear above 921px.
- At exactly 921px, one header shows.
- **Desktop "Services" button:**
  - It opens on hover, on click, and on Enter or Space, and `aria-expanded` toggles.
  - Escape closes it and returns focus to the button.
  - Tabbing past the last link closes it, and so does clicking elsewhere.
  - A touch tablet in landscape (921px or wider) opens it on tap.
- A client with zero or one published page shows no Services item, on desktop or mobile.
- With Query Monitor on, a client with 10 or more pages adds a constant number of queries for the menu.
- `debug.log` gets no "Landing Pages Debug" lines.

**Smoke step 25 (new):** a Template B page whose `reviews_html` holds only an iframe (stripped by `wp_kses_post`) shows no Reviews section and loads no Swiper.

**Release-note draft, revised:**
- **Lower Google Maps costs.** The Template B footer map loads only when a visitor scrolls near it, so visitors who never reach it don't add to your Maps bill.
- **Lighter pages.** Template A pages no longer load jQuery, and Template B skips the review slider on pages without reviews (about 300 KB uncompressed). If a Template A tracking tag uses jQuery, see the upgrade notes.
- **A working mobile menu on Template C.** It lists the client's landing pages under Services, plus the page's sections.
- **A warning when your Google browser key can't be locked down.** Admins see it until a server key is saved.

**FAQ additions and changes:**
- In the jQuery answer, use "…or contains a Google Tag Manager container snippet (`gtm.js` / `GTM-…`). The gtag.js snippet alone doesn't count."
- **Q: The Template C "Services" menu doesn't show.** A: It lists the client's published landing pages, and only appears when there are two or more. Publish more pages for that client, and check that each one has the client chosen in Client Name (Landing Page Details).

## Decisions
- **Approved and fixed on the branch:** every item above, in the commits that follow these notes.
- **Forwarded to Odell:** none new.
- **Declined:** none.
- **Gate:** no department said `block`. The merge gate is still the stack-wide staging smoke test (PLAN P0).
