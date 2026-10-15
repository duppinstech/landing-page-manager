---
description: Cut a plugin release (version bump, changelog, tag, GitHub Release, zip)
argument-hint: "<version e.g. 1.0.6>"
---

Release version: $ARGUMENTS

This ships to live sites through the GitHub updater. **Confirm with Odell before every step that pushes or tags.**

1. **Preflight**
   - `git status` must show a clean tree on `main`, up to date with `origin/main`. Stop if not.
   - No open P0 items in PLAN.md, or Odell explicitly waives them.
   - Run `/department-review v$ARGUMENTS` on everything since the last tag. Any `block` stops the release. Use the marketing release-note draft and the ops smoke test in steps 2 and 5.
   - Lint every PHP file: `find includes -name '*.php' -print0 | xargs -0 -n1 php -l` plus `php -l landing-page-manager.php`.
2. **Bump**
   - Set ` * Version: $ARGUMENTS` in `landing-page-manager.php`.
   - In `CHANGELOG.md`, move [Unreleased] to `## [$ARGUMENTS] - <today>` and update the compare links.
   - Commit: `Release v$ARGUMENTS`.
3. **Dry-run the zip locally** (optional): `bin/build-zip.sh` and check that `unzip -Z1 landing-page-manager.zip` paths all start with `landing-page-manager/`. Don't commit the zip.
4. **Publish** (ask first)
   - `git push origin main`
   - `git tag v$ARGUMENTS && git push origin v$ARGUMENTS`
   - The `Release` workflow (`.github/workflows/release.yml`) lints, checks that the tag matches `Version:`, builds the zip, and creates the release with the zip and changelog notes. Watch it with `gh run watch`.
   - Don't run `gh release create` by hand. A release without the asset makes sites fall back to the raw zipball.
5. **Verify**
   - `gh release view v$ARGUMENTS` shows the asset.
   - On a staging site, **Dashboard → Updates → Check Again** offers $ARGUMENTS and installs into the correct folder.
6. Update `.claude/memory/progress.md` and PLAN.md.
