# Workflow: code review cycle

1. **Scope.** Identify the diff (`git diff main...HEAD`, or `gh pr diff <n>`) and the PLAN.md item or issue it claims to address.
2. **Static pass.** Run `php -l` on the changed PHP files. Check PHP 7.4 compatibility.
3. **Checklist pass.** Apply the `wp-code-review` skill hunk by hunk.
4. **Security pass.** Delegate to the `security-reviewer` agent when the diff touches handlers, `$_*` superglobals, files, rewrites, or the updater.
5. **Behavior pass.** Compare the change to its intent. Does it do what the PR says, and nothing extra? Check tests or smoke notes against `testing-standard.md`.
6. **Department pass.** For a PR that is otherwise ready, run `/department-review <PR#>` so legal, finance, product, marketing, support, ops and UX also sign off.
7. **Report.** Rank findings High, Medium, Low as `file:line — problem — fix`, then give a verdict: ship, ship with fixes, or block.
8. **Re-review.** After fixes, re-check only the changed hunks and the earlier findings.
9. **Merge gate.** Lint is clean, there are no open High findings, no department verdict is `block`, and CHANGELOG is updated. Odell merges.
