# Commits & pull requests

## Branches
`feature/<name>`, `fix/<name>`, `chore/<name>`, `template/<name>`, `security/<name>`, all branched from `main`.

## Commits
- Use the imperative mood and keep the subject to 72 characters or less, matching history: "Fix subdomain AJAX and normalize GitHub update installs."
- One logical change per commit. Don't bundle unrelated WIP.
- Stage files explicitly (`git add <paths>`). Never use `git add -A` while there is unrelated work in the tree.
- Don't commit `landing-page-manager.zip` changes unless the commit is a release commit.

## PR body
Use `.github/pull_request_template.md`: summary, type, checklist, test notes. Link the PLAN.md item. Use the `pr-description` skill to draft it.

## Merge gates
- Lint is clean.
- The security review is done for handler, upload, rewrite, and updater changes.
- `CHANGELOG.md` has an [Unreleased] entry.
- The version bump and tag happen only in a dedicated release PR or commit (see `/deploy`).
