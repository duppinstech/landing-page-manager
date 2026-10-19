# Department re-review: Analytics and Tracking screens, round 2
**When:** 2026-10-17 19:50 EDT · **Scope:** the round-1 fixes in `70697f6` on `feature/analytics-tracking-slots` · **Chair:** main session · **Owner:** Duppins Technology

All eight departments, one combined pass.

## Verdicts
| Department | Verdict | Summary |
|---|---|---|
| Security | **ok** | No new findings. |
| QA / Ops | **fix-before-merge** | The new `tracking.js` header and PLAN item called `tracking.js` unused, but Pro 1.0.1–1.0.2 still load it from here. The PLAN item also called `get_dashboard_data()` unused; it serves the `lpmanager/v1/dashboard-data` route, which Pro hooks. |
| Product / UX | **ok** | Low: Pro 1.0.3's Tracking text didn't mention its Pro Settings screen. Nits: an outdated comment in `Plugin_Settings`, the `pro_has_own_screens()` docblock, and an unused banner condition. |
| Support / docs | **fix-before-merge** | Same `tracking.js` point. Optional: list the new developer hooks in the changelog. |
| Legal / Privacy | **ok** | No new findings. |
| Marketing | **ok** | Wording decision already with Odell. |
| Finance | **ok** | Get Pro destination already with Odell. |

## Outcomes
- **Implemented (auto-approved):**
  - The `tracking.js` header and the PLAN P2 item are corrected; `tracking.js` and `get_dashboard_data()` are kept.
  - The Pro 1.0.3 wording now also names Pro Settings, on the screen and in the Help widget.
  - The outdated comment, the docblock and the unused banner condition are fixed.
  - The changelog lists the developer hooks.
  - `php -l` passes.
- **For Odell:** the Help widget's Tracking link opens the user guide at `#tracking`. That section needs to exist when the guide is published.
- These are text and comment corrections plus one dead condition, so the loop ends here.
