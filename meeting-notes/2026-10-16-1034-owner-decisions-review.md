# Department review: owner decisions batch (`fix/owner-decisions-p0`)
**When:** 2026-10-16 10:34 EDT (started 02:03, paused by the account session limit) · **Scope:** `git diff ac03c0a^..2964472`, 19 commits implementing Odell's 10-16 Q&A decisions · **Chair:** main session · **Owner:** Duppins Technology

## Since the last meeting
- **Odell answered 20 open decisions in a Q&A** (recorded in `.claude/memory/decisions.md`, "Owner Q&A").
- **Security reviewed the raw-HTML guard and the iframe sanitizer separately, four rounds in all:**
  - The first raw-HTML version was blocked: it could be bypassed through Carbon's REST route and an unkeyed hash.
  - The next fix had an ordering bug: Carbon deletes the stored value before the save check runs.
  - The first iframe version was blocked: URL-parser confusion, plus quoting bypasses on WordPress before 7.0.
  - All of these were fixed and re-verified against WordPress 7.1.3 code. The final security verdict on those parts was ok.
- **Coordination with the Pro session:**
  - Pro's extra analytics stay on Pro's own screens (R10 dropped).
  - The tracking toggles move to Pro 1.0.4. The storage is unchanged.
  - Core's uninstall leaves `_lpmanager_track_*` alone.

## Attendance and verdicts
| Department | Verdict | Top finding |
|---|---|---|
| Security | fix-before-merge | No security defects. On WP 5.9–6.3 the revision pass could keep a complete page as a draft (functional bug). |
| Legal | fix-before-merge | The CHANGELOG claims Places-terms compliance while fetched reviews are still stored. The privacy text doesn't cover site-wide Additional Scripts. |
| Finance | **ok** | Keys are per install, not per client, so "clients pay" only holds with one install per client or a per-client override. No price change. |
| Product | fix-before-merge | All 16 decisions were implemented as decided. Template A colours and the page phone change on live pages with no upgrade note. Four decided items have no backlog entry. |
| Marketing | fix-before-merge | The same compliance claim. Clearer labels needed. Client B template names still carry a client brand. Release-note draft provided. |
| Support / docs | fix-before-merge | Five upgrade notes missing. The review-widget help text is wrong. The draft-on-publish screen says "published". |
| Ops / release | fix-before-merge | **High:** deleting `tracking.js` breaks sites still on Pro 1.0.2 (restored). The page phone changes on live pages. `DISALLOW_UNFILTERED_HTML` locks out raw-HTML fields. Smoke steps 26–41 provided. |
| UX | fix-before-merge | Semi-transparent text on client navy drops to about 3.5:1. Rebuilt iframes break `width="100%"` and overflow on phones. Missing iframe titles. A 38px call button. |

## Highlights
- **Nothing blocks.** The batch has no security defects, and the remaining work is routine fixes plus upgrade notes.
- **Ops caught a regression in this batch.** `tracking.js` was deleted on the Pro session's word that Pro 1.0.3 ships its own copy, but Pro 1.0.2 still loads core's file. It was restored right away (`384fea9`) and the Pro session was told.
- **Upgrading changes what live visitors see in two ways**, and both need checks before release:
  - Template A switches to stored client colours, which are often auto-filled with a generic blue and red.
  - Every page's old, formerly required "Client Phone" now wins.
- **Legal and marketing both rejected the same line:** "no review content is cached, as Google's terms require". Fetched reviews are still saved into the page's Reviews field.

## Findings and outcomes by department

### Security
- On WP 5.9–6.3, the revision `save_post` runs before Carbon saves the fields, so a complete page could be kept as a draft. **Approved → fix now (P0).**
- The reviews lock can be raced, and one editor can lock others out. **Already tracked** (P2 Fetch Reviews capability).
- The page is briefly published before it's reverted, so publish hooks fire. **Approved → PLAN (P2):** a note for Pro to re-check the status.

### Legal
- The CHANGELOG reviews compliance claim, and the `Reviews_Ajax` comment. **Approved → fix now (P0).**
- The privacy text must cover site-wide Additional Scripts, carry a consent tutorial line, and mention Vimeo and Google Maps embeds. **Approved → fix now (P0).**
- The Client B templates are named after a client, and the Template C heroes are hotlinked from the client's server. **Forwarded → Odell** (rename, or record consent).
- The car-care brand–named default hero images. **Forwarded → Odell** (remove them, or send to the attorney).
- Do the image rights cover use by every operator? **Forwarded → Odell.**
- The mockups still need a private home. **Already tracked.**
- Reviews storage and the 4–5★ filter. **Already tracked** (attorney).
- README Pro link, and narrowing the CREDITS list now that payform is gone. **Approved → fix now / PLAN.**

### Finance
- Per-install keys vs. per-client billing. **Forwarded → Odell:** one install per client, a per-client key override in core, or pass the cost through.
- The early-access link goes to the homepage with no UTM. **Forwarded → Odell** (URL), merged with marketing.
- business-context still shows "TBD" for the key decision. **Approved → fix now.**
- Describe the Pro feature as a "per-client template picker". **Noted** for pricing.
- **Pricing:** no change. Analytics and per-client templates argue for licensing per install or per agency.

### Product
- Template A colours change on upgrade. Upgrade note: **Approved → fix now (P0).** Whether the `#007BFF`/`#FF4136` fallback counts as "no brand colour": **Forwarded → Odell** (recommendation: yes).
- The page phone wins on upgrade. Upgrade note and audit step: **Approved → fix now (P0).**
- The "Post published" message when a page is kept as a draft. **Approved → fix now (P1).**
- Scheduled, Quick Edit and REST publishes go back to draft with no notice. **Approved → PLAN (P1).**
- Raw `client_tel` key in the notice. **Already tracked** (Validator messages, raised to P0). The `client_tel` label is mapped now.
- Unapproved-scripts notice on all plugin screens, plus a success notice. **Approved → fix now (P1).**
- Track the decided work: capability type, Places API (New), a neutral Starter template, moving the mockups, and removing core's toggles once Pro 1.0.3 is unsupported. **Approved → PLAN.**
- `lpmanager_template_for_post` must also check the future uploads folder. **Approved → PLAN** (a note on the existing item).
- `decisions.md` entries for the raw-HTML guard, the HMAC approval and the iframe rebuild. **Approved → done.**

### Marketing
- The compliance claim. **Approved → fix now** (merged with legal).
- Pro 1.0.4 lines read as if Pro is on sale. **Approved → fix now.**
- Labels: "Page phone (optional)", and Header Tracking Code without "(Nimbata)". **Approved → fix now.**
- Consistent "only administrators" wording, one canonical Pro feature list, and a softer "4.5:1" claim instead of "WCAG AA". **Approved → fix now.**
- README Pro link. **Approved → fix now.**
- Client B display names. **Forwarded → Odell** (merged with legal).
- Upsell copy for per-client templates on the Templates tab. **Forwarded → Odell.**

### Support / docs
- Upgrade notes for colours, phone, Additional Scripts (duplicate tags), embeds and review widgets, the class-rename mapping, and the deleted files used by uploaded templates. **Approved → fix now (P0).**
- `reviews_html` help text. **Approved → fix now.**
- The draft "Post published" message. **Approved → fix now** (merged with product).
- README Settings and phone lines, translators-comment placement, untranslated `Reviews_Ajax` strings, US spelling. **Approved → fix now.**
- FAQ drafted. **Saved in the [appendix](2026-10-16-1034-owner-decisions-review-appendix.md#support-faq).**

### Ops / release
- `tracking.js` deletion breaks Pro 1.0.2. **Approved → fixed** (`384fea9`). When it can be deleted: **Forwarded → Odell / Pro session.**
- `DISALLOW_UNFILTERED_HTML` locks everyone out of raw-HTML fields. CHANGELOG note: **Approved → fix now.** A `manage_options` fallback on single-site: **Forwarded → Odell.**
- Rich-text fields now filtered where 1.0.6 printed them raw. **Approved → upgrade note** (merged with support).
- Correct smoke step 25, and add steps 26–41. **Approved → [appendix](2026-10-16-1034-owner-decisions-review-appendix.md#smoke-test-steps-2541).**
- Additional Scripts that use jQuery fail on Template A. **Approved → fix now** (include them in the jQuery check).
- Salt rotation and site cloning break approval. **Approved → CHANGELOG note.**
- Scheduled pages reverted with no notice. **Approved → PLAN (P1)** (merged with product).
- Rollback side effects. **Approved → release notes.**

### UX
- Semi-transparent white on client navy, and navy text on `--red-light`. **Approved → fix now (P1):** stronger darkening targets.
- Red on dark backgrounds with a dark client secondary. **Already tracked** (Contrast). A note was added.
- Iframe `%` sizes and responsive embeds. **Approved → fix now (P1).**
- Missing iframe title. **Approved → fix now** (a fallback title per provider).
- Call-button tap target. **Approved → fix now (44px).**
- Approval notice should show the pending code. **Approved → fix now** (code preview in the notice).
- Accents vanish when the client's two colours are similar. **Forwarded → Odell** (fall back to the built-in red, or accept).

## Decisions
- **Fixed during the meeting:** `384fea9` (`tracking.js` restored).
- **Approved → fix now on this branch:** every "fix now" item above. The work starts after these notes, under the standing rule.
- **Forwarded to Odell (added to PLAN "Decisions needed"):**
  1. Client B template names and the hotlinked heroes.
  2. car-care brand images and image-rights scope.
  3. The Template A fallback colour pair.
  4. Per-install keys vs. per-client billing.
  5. The early-access URL.
  6. A `DISALLOW_UNFILTERED_HTML` fallback.
  7. When `tracking.js` can be deleted.
  8. Accent colours when the client's two colours are similar.
  9. Per-client template upsell copy.
  10. The Pro 1.0.3 support window.
- **Declined:** none.
- **Waivers:** none.
