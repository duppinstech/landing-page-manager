---
description: Review the current diff (or a given path/branch) against project rules
argument-hint: "[path | branch | PR#]"
---

Review target: $ARGUMENTS (if empty, use `git diff` plus `git diff --staged`).

1. Load `.claude/rules/code-style.md`, `.claude/rules/api-conventions.md`, and `.claude/rules/testing-standard.md`.
2. Follow the `wp-code-review` skill checklist.
3. If the diff touches request handlers, `$_GET/$_POST/$_FILES`, uploads, `Rewrite_Rules`, or `Update_Checker`, delegate to the `security-reviewer` agent and merge its findings in.
4. Run `php -l` on every changed PHP file.
5. Report findings ranked by severity (High / Medium / Low) as `file:line — problem — fix`. Finish with a one-line verdict: ship, ship with fixes, or block.

Do not edit files. This command is review-only.
