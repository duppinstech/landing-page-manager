# Department re-review: external templates, round 3
**When:** 2026-10-17 13:34 EDT · **Scope:** the round-2 fixes (`37cc54b` on `feature/external-templates`) and the rebased removal (`feature/remove-client-templates`) · **Chair:** main session · **Owner:** Odell Duppins

All eight departments, in two combined passes.

## Attendance and verdicts
| Department | Verdict | Summary |
|---|---|---|
| Security | **ok** | Round-2 fixes confirmed. Low: the update guard copied without the lock, so a concurrent cron copy could block one update with an empty template list. |
| Ops / release | **ok** (1.0.10) · **block** (removal branch) | **The rebase committed merge-conflict markers in `includes/core/Privacy.php` on the removal branch** (a parse error on every request; CI lint would have stopped the release). Low: the pause transient was rewritten on every admin page; no wording for an incomplete copy. Compatibility with WP 5.9 confirmed. |
| UX | **ok** | Low: a starter's name on upload got the "replace its folder" advice; "the templates" for one template; call-bar edge checked only against the page background. |
| Product | **ok** (1.0.10) · **block** (removal branch) | Same `Privacy.php` defect. Low: an outdated line in the scaffold command. |
| Legal | **ok** (1.0.10) · fix-before-merge (removal branch) | Same defect. Low: "suggested privacy-policy text" should be "privacy-policy guide". |
| Support / docs | **ok** (1.0.10) · fix-before-merge (removal branch) | Low: hard-coded plugin and wp-content paths in messages; incomplete-folder wording; two private README lines. |
| Marketing | **ok** | No findings. |
| Finance | **ok** | No findings. |

## Outcomes
- **The 1.0.10 branch passes every department.** All remaining items were Low and are fixed in this round: guard re-reads the pending list (no empty names) and treats a folder finished by another request as copied; the pause transient is written only when missing; one shared message (`pending_templates_message()`) with singular/plural forms, paths built from the real install (`display_path()`), and a line for incomplete folders; a separate message for uploads that use a starter's name; call-bar edge against both page colours; scaffold command line replaced.
- **Removal branch:** conflict markers removed from `Privacy.php` (keeping the Starter One/Two tutorial line, dropping the Template C line), CHANGELOG says "privacy-policy guide", whole branch linted.
- **Private repo README:** Privacy note wording; where to move the old folder during a live update.
- **Process note:** the rebase's conflict list was read with `head -10`, which hid `Privacy.php`. Conflicts are now checked with `git diff --name-only --diff-filter=U` and a marker search before continuing.
- **Next:** a short check of this round's fixes only.
