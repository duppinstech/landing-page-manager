# Department re-review: owner decisions batch fixes (`fix/owner-decisions-p0`)
**When:** 2026-10-16 13:00 EDT · **Scope:** `git diff 4b1f6e9..251332c` plus `384fea9`: the fixes from the [10:34 review](2026-10-16-1034-owner-decisions-review.md) · **Chair:** main session · **Owner:** Odell Duppins

Seven departments re-reviewed. Finance was already ok at 10:34, and its area didn't change.

## Attendance and verdicts
| Department | Verdict | Summary |
|---|---|---|
| Security | **ok** | Every fix was correct. Low: the approval notice also showed to single-site Editors, who can't approve. Fixed in `251332c`. |
| Legal | **ok** | The reviews wording, Reviews_Ajax comment, privacy text and README are all accurate. Low: business-context overstated the anonymization. Fixed. |
| Product | **ok** | All eight items resolved. Validator messages are only partly done, and the "Post draft updated" wording is generic. Both went to PLAN. |
| Marketing | fix-before-merge | Copy only: the README Pro list, the jQuery note doesn't mention Additional Scripts, this round's changes are missing from the CHANGELOG, notice copy, capitalization, and "optional" appears twice. |
| Support / docs | fix-before-merge | **On a single site, Editors have `unfiltered_html`, so they can still edit Header Tracking Code. The docs said "only administrators."** Also: the `DISALLOW_UNFILTERED_HTML` consequence, an old phone label, the deleted-files list, a phone-audit hint, and next steps in Reviews_Ajax messages. |
| Ops / release | **ok** | Low: a stale Draft-updated swap after a Quick Edit; a `0` size; `ctype_digit` without the ctype extension; a 60s uninstall leftover; the jQuery docs; rollback CSS. Corrections to smoke steps 29, 32, 35 and 41. |
| UX | **ok** | Low: `0` sizes, and the `<pre>` preview can't be focused in Safari. **For Odell:** light brands get darkened a lot. |

## Highlights
- No blocks, and no High finding that code can fix. Security, legal, product, ops and UX said ok.
- **Support found a real gap between the docs and the code:** Editors can still change Header Tracking Code on a single site. The docs now describe that behaviour accurately. Whether to restrict the field to administrators goes to Odell (option (a) in PLAN).
- All the routine follow-ups were fixed the same day.

## Outcomes
- **Approved, fixed:**
  - Notice capability (`251332c`), and business-context wording.
  - `0` sizes ignored, `ctype_digit` replaced, the Draft-updated swap only applies to real drafts, a focusable labelled code preview, fuller notice copy, the uninstall transient, and the doubled "optional" removed.
  - CHANGELOG: jQuery triggers include Additional Scripts, this round's Changed entries, accurate "who can change raw HTML" wording, the `DISALLOW_UNFILTERED_HTML` consequence, the Page phone label, the full deleted-files list, a phone-audit hint, and rollback CSS.
  - README: the Pro list and the jQuery hook text.
  - Reviews_Ajax next steps, and the filter docblock.
- **Approved → PLAN:**
  - Validator messages marked `[~]`, with the remaining work listed (P0).
  - `post_updated_messages` for landing pages (P2).
- **Forwarded → Odell:**
  - **Who can change Header Tracking Code: administrators only, or administrators and editors?** (P0)
  - QuickTow brand fidelity vs. contrast. (P2)
- **Declined:**
  - Capitalization of "Header Tracking Code": the label was kept lower-case to match the new descriptive label. Notices use the field's proper name.
  - Older-Safari `aspect-ratio` fallback: accepted as-is, because the share is tiny and the `height` attribute still applies.
  - Marketing's CHANGELOG class-table concern (legal Low): kept, because operators need the mapping.

## Smoke-test corrections (to steps in the [10:34 appendix](2026-10-16-1034-owner-decisions-review-appendix.md#smoke-test-steps-2541))
- **29:** The notice shows on the plugin's screens, client screens and the WP Dashboard, and only to administrators (`unfiltered_html` plus `manage_options`). It includes a code preview and an "Edit in Settings" link. Approving from the Dashboard returns there with an "approved" notice.
- **30:** On a single site, Editors **can** edit Header Tracking Code (they have `unfiltered_html`). It is read-only for site admins on multisite and for Authors. Re-test if Odell chooses option (a).
- **32:** Additional Scripts with jQuery or GTM markers load jQuery, even unapproved and on pages with no client. jQuery appears in the head, before the footer scripts.
- **35:** Rebuilt iframes carry `class="lpm-embed"` and `style="max-width:100%;"`. Pixel sizes also get `height:auto;aspect-ratio:W/H`. `width="0"` is dropped. A fallback title is used when none is given.
- **41:** Also removed: the `lpmanager_additional_scripts_approved_*` transients.
