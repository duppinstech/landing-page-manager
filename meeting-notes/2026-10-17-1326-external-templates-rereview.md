# Department re-review: external templates, round 2 (`feature/external-templates`)
**When:** 2026-10-17 13:26 EDT · **Scope:** the round-1 fixes in `0209ef5`, and the doc and workflow parts of the removal commit (now on `feature/remove-client-templates`) · **Chair:** main session · **Owner:** Duppins Technology

All eight departments re-reviewed, in three combined passes.

## Attendance and verdicts
| Department | Verdict | Summary |
|---|---|---|
| Security | **ok** | Round 1's Medium is fixed. Low: logged-out `admin-ajax`/`admin-post` still ran the admin re-check; the `add_option` lock isn't atomic; "Replace current with uploaded" skipped the update guard. |
| Ops / release | fix-before-merge | **The `.htaccess` (`Options -Indexes`) returns errors on hosts that disallow `Options` overrides**; 2–3 database writes on every admin request after the copy; endless WP-Cron rescheduling when file changes are disabled; an empty or partial copy counted as done. Merge order needs splitting (for Odell). |
| UX | fix-before-merge | Round 1 resolved. Medium: Starter Two's About links were checked only against the page background, not the lighter panel. Low: call bar and contact button edges with dark client colours, lazy logo, call-bar spacing without a phone. |
| Product | fix-before-merge | Round 1 resolved. Medium: the branch ended with the 1.0.11 removal, so merging it would ship 1.0.11 early. Low: the guard didn't name the templates; the notice covered only the active template; README uninstall wording; scaffold command; `cdn_assets` undocumented. |
| Legal | fix-before-merge | Round 1 resolved. Medium: the README said derivatives need a "GPL-compatible" licence; GPL-2.0 requires the GPL itself. Low: no licence notice in the starters; a Privacy tutorial line for starter-only sites. |
| Support / docs | fix-before-merge | Round 1 resolved. Medium: uninstall wording; the CHANGELOG overstated the notice; no path for sites that deploy without the WordPress updater; the private README described the old copy timing. Several wording Lows. |
| Marketing | **ok** | Claims hold (WCAG text colours, no external assets). Low: a contact address for the class map; a "why it matters" phrase. |
| Finance | **ok** | No cost change. **For Odell:** the update block also covers unused templates, so starter-only sites on unwritable hosts stay on 1.0.10 until someone copies the folders. |

## Highlights
- No `block`. Every round-1 item was confirmed resolved.
- **Release caught a real outage risk in a round-1 fix:** the `.htaccess` could make every copied template's CSS, JS and images return HTTP 500 on some Apache setups. It was removed; copied folders get an `index.php` instead.
- **The removal is now on its own branch** (`feature/remove-client-templates`), so `feature/external-templates` can be merged and released as 1.0.10 alone.

## Outcomes
- **Approved, fixed in this round:**
  - Copy step: `pending_templates()` first, so nothing is written once done; atomic `WP_Upgrader::create_lock()`; admin re-check only for administrators and never on AJAX; a day's pause when file changes are disabled; an interrupted copy is never overwritten and keeps the template pending; try/catch around folder walks; `.htaccess` removed, `index.php` in copied folders.
  - Guard: also on `upgrader_source_selection` ("Replace current with uploaded", `wp plugin install --force`), and the error names the templates.
  - Notice: every template still inside the plugin, with a variant for sites that don't allow file changes; no nested-folder wording.
  - Starters: contrast against both page colours; call bar and contact button edges; eager logo; call-bar spacing only with a phone; copyright, author and GPL notice in `single-landing_page.php`.
  - Docs: README (uninstall, `cdn_assets`, GPL sentence, why the folder matters), CHANGELOG (notice wording, guard on uploads, non-WordPress deploys, auto-update emails, rollback to 1.0.9, contact address), Privacy tutorial line, upload "already installed" message, scaffold command, progress log, private repo README.
- **For Odell (Decisions needed):** merge order (PR for 1.0.10 alone, removal held); whether the update block should cover only templates in use.
- **Re-review:** next round checks only this round's fixes.
