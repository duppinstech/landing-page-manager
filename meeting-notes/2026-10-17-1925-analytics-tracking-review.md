# Department review: Analytics and Tracking screens from Pro
**When:** 2026-10-17 19:25 EDT · **Scope:** `feature/analytics-tracking-slots` (Analytics and Tracking menu items filled by Pro 1.0.6+, get-Pro messages without Pro, dashboard assets moved to Pro), checked with Pro's `feature/one-analytics-screen` · **Chair:** main session · **Owner:** Odell Duppins

All eight departments, one combined pass.

## Verdicts
| Department | Verdict | Summary |
|---|---|---|
| Security | **ok** | Both screens check `manage_options`; output escaped; no new input. Anything hooking the render actions must check capabilities itself (Pro does). |
| QA / Ops | **fix-before-merge** | Pro before 1.0.4 was told to look under Pro Analytics / Pro Settings, which it doesn't have. Pro 1.0.4–1.0.5 lose the charts until they update: needs an upgrade note. |
| Product / UX | **fix-before-merge** | Without Pro, the "coming soon" banner landed inside the new Pro box, giving two clashing messages. Button text included "(opens in a new tab)" visibly. Leftover unused dashboard code noted. |
| Support / docs | **fix-before-merge** | README still said Settings has conversion-tracking toggles; CLAUDE.md and a `tracking.js` comment still named the Conversion Tracking tab. |
| Legal / Privacy | **ok** | One fewer outside request (no Chart.js here any more); nothing collected. |
| Marketing | **fix-before-merge (owner)** | "Get Pro" on these screens vs "coming soon, contact for early access" elsewhere. |
| Finance | **ok** | The Get Pro link leads to a site with no way to buy yet. |

## Outcomes
- **Implemented (auto-approved):**
  - The banner no longer shows on Analytics and Tracking.
  - Messages for an active Pro now depend on its version: Pro 1.0.4–1.0.5 are pointed to Pro Analytics / Pro Settings, and earlier Pro to Settings → Conversion Tracking (`Admin_Dashboard::pro_has_own_screens()`). The Help widget uses the same wording.
  - The button reads "Get Pro", with "(opens in a new tab)" for screen readers only.
  - README, CLAUDE.md and the `tracking.js` comment are updated.
  - The changelog has an upgrade note: update Pro to 1.0.6 at the same time.
- **Checked on the test site:** without Pro, each screen shows one message with the Get Pro button, while the Dashboard banner still shows. With simulated Pro before 1.0.4 and Pro 1.0.4–1.0.5, each screen gives the right "update Pro" text. Pro's integration suite passes against this branch and against 1.0.12.
- **Added to PLAN (P2):** remove the unused dashboard code.
- **For Odell:** wording on these screens vs the banner, and where Get Pro leads (see Decisions needed). Release order: Pro 1.0.6 before or together with 1.0.13.
- The re-review follows in the next meeting.
