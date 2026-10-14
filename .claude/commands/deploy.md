---
description: Cut a plugin release (version bump, changelog, tag, GitHub Release, zip)
argument-hint: "<version e.g. 1.0.6>"
---

Release version: $ARGUMENTS

This ships to live sites through the GitHub updater. **Confirm with Odell before every step that pushes or tags.**

1. **Preflight**
   - `git status` must show a clean tree on `main`, up to date with `origin/main`. Stop if not.
   - No open P0 items in PLAN.md, or Odell explicitly waives them.
   - Lint every PHP file: `find includes -name '*.php' -print0 | xargs -0 -n1 php -l` plus `php -l landing-page-manager.php`.
2. **Bump**
   - Set ` * Version: $ARGUMENTS` in `landing-page-manager.php`.
   - In `CHANGELOG.md`, move [Unreleased] to `## [$ARGUMENTS] - <today>` and update the compare links.
   - Commit: `Release v$ARGUMENTS`.
3. **Build the zip** (top-level folder must be exactly `landing-page-manager/`):
   - Stage a copy that excludes `.git/`, `.github/`, `.claude/`, `CLAUDE*.md`, `AGENTS.md`, `PLAN.md`, `INTENT.md`, `.mcp.json`, `landing-page-manager-pro/`, and any existing zip.
   - Zip it with PowerShell `Compress-Archive` or `7z`. Verify with `unzip -l` that every path starts with `landing-page-manager/`.
4. **Publish** (ask first)
   - `git push origin main`
   - `git tag v$ARGUMENTS && git push origin v$ARGUMENTS`
   - `gh release create v$ARGUMENTS landing-page-manager.zip --title "Landing Page Manager v$ARGUMENTS" --notes-file <changelog section>`
5. **Verify**
   - `gh release view v$ARGUMENTS` shows the asset.
   - On a staging site, **Dashboard → Updates → Check Again** offers $ARGUMENTS and installs into the correct folder.
6. Update `.claude/memory/progress.md` and PLAN.md.
