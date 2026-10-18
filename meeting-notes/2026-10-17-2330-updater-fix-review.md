# Department review: updater fix (1.0.14)
**When:** 2026-10-17 23:30 EDT · **Scope:** `fix/updater-source-dir` (Update_Checker::normalize_install_source_dir, version 1.0.14) · **Chair:** main session · **Owner:** Odell Duppins

Security, release/ops, QA and support, one combined pass.

## Verdicts
| Department | Verdict | Summary |
|---|---|---|
| Security | **ok** | `$desired` is always inside the update's temp folder. Low: a folder differing only in letter case would still be deleted on case-insensitive file systems. |
| Release / ops | **ok** | A failed move silently continued with the misnamed folder; changelog links and "since 1.0.6" wording. Returning the folder with a trailing slash also fixes the zipball path, whose package check needs it. |
| QA | **ok** | Template_Manager's filter is unaffected. Asked for the bulk "Update now" path, the single path and WordPress 5.9 to be exercised; no unit-test harness exists for a regression test. |
| Support | **fix-before-merge (wording)** | Upgrade note must name the release asset `landing-page-manager.zip`, not "Source code (zip)"; menu is "Add New Plugin"; say 1.0.14 carries the 1.0.13 security fix. |

## Outcomes
- **Implemented (auto-approved):** a failed move returns a `WP_Error` instead of installing into a misnamed folder; case-only differences are treated as the same folder; the upgrade note was corrected; the changelog links were added.
- **Checked on WordPress 7.1.3 and 5.9.3 (Docker):**
  - Installing 1.0.13 from its release, then hand-installing the 1.0.14 zip.
  - "Update now" (bulk upgrader) to a correctly named package: the plugin stays active.
  - The single upgrader to a zipball-style folder: renamed and installed.
  - Bulk again.
  - As a control, 1.0.13 fails as before.
- **For Odell:** hand-install 1.0.14 on both sites (they are still on the 1.0.12 dashboard with the XSS until then). A plugin installed in a non-standard folder is renamed to `landing-page-manager` on update and comes back inactive; this was already the case before this fix. Adopting the Pro standard's keep-the-folder behaviour is a separate decision.
- **Added to PLAN:** a unit-test harness, with a mocked filesystem test for this function.
