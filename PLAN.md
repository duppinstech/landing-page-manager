# PLAN.md — action items

Living backlog for the plugin and its deployment. Update status here; log progress
in `.claude/memory/progress.md`. Priority: **P0** = before next release, **P1** = soon, **P2** = later.

Status keys: `[ ]` todo · `[~]` in progress · `[x]` done

_Last reviewed: 2026-10-14 (current release: v1.0.5)_

## P0 — before the next release

- [ ] **Secure template zip upload** (`includes/templates/Template_Manager.php` `handle_upload`)
  - No nonce check on `admin_post_lpmanager_upload_template`: CSRF can make an admin upload PHP. Add `check_admin_referer()` and a nonce field in `Plugin_Settings`.
  - Zip is extracted into `wp-content/uploads/lpmanager_temp_upload/`, which the web server can reach. When validation fails the extracted folder is never deleted, so executable PHP is left there. Extract outside the web root (or into `get_temp_dir()`) and always clean up recursively.
  - Template slug comes from the first `*.php` filename. Sanitize it with `sanitize_key()` and use `unzip_file()` / `WP_Filesystem` so archives can't write outside the target folder (zip-slip).
  - Owner: main + `security-reviewer`.
- [ ] **Triage uncommitted WIP.** 12 modified files (analytics, validator, CPT, client taxonomy, template loader, whiterail templates) plus the untracked `includes/templates/quicktow/` template. Review, split into logical commits, and add a CHANGELOG entry.
- [ ] **Stop tracking `landing-page-manager.zip` in git.** It is a 3.5 MB binary that is rebuilt on every release and bloats history. Build it in CI and attach it to the GitHub Release instead (see P1 release automation). Add it to `.gitignore` once CI ships it.
- [ ] **Remove site-specific dequeue handles.** `landing-page-manager.php` hardcodes `elementor-post-3/2708/2702/2693`. Replace them with a pattern match (`elementor-post-*`) or move them into a filter default.

## P1 — deployment and quality

- [ ] **CI: lint on push/PR.** GitHub Action that runs `php -l` across PHP 7.4 and 8.x, plus PHPCS with the WordPress Coding Standards (start as non-blocking).
- [ ] **CI: release automation.** On a `v*` tag, run `composer install --no-dev`, build the zip with top-level folder `landing-page-manager/` (excluding `.claude/`, `.github/`, `*.md` except README, and the Pro dir), and attach it to the release.
- [ ] **Test harness.** Add PHPUnit with `wp-phpunit` or Brain Monkey. First targets: `Update_Checker::version_compare`, `normalize_install_source_dir`, `Landing_Page_Validator`, rewrite-rule host parsing.
- [ ] **Pin Composer dependency.** `pkostadinov-2create/carbon-field-password` is on `dev-master`, so builds aren't reproducible. Pin it to a commit or tag.
- [ ] **CHANGELOG backfill** for v1.0.1–v1.0.5 from git history and releases.
- [ ] **Add a copyright line to LICENSE** (`Copyright (C) 2025-2026 Odell Duppins / Duppins Technology`). Keep GPL-2.0-or-later.
- [ ] **Uninstall routine.** Add `uninstall.php` that removes `lpmanager_*` options and leaves content in place, with a documented opt-in for deleting content.

## P2 — later

- [ ] **WordPress.org readiness.** `readme.txt` is empty. Write it in WP.org format (stable tag, tested up to, changelog, FAQ, screenshots).
- [ ] **i18n pass.** Regenerate `languages/landing-page-manager.pot` and wrap remaining strings in templates.
- [ ] **Template contract doc.** Document the required files, field registration, and helper API for new templates. `/scaffold` uses this contract.
- [ ] **Consolidate duplicated vendor assets** (bootstrap, swiper, jquery-migrate) that are copied into every template folder.
- [ ] **Accessibility and Core Web Vitals audit** of each template on mobile.

## Done
- [x] GitHub-release updater with WP.org toggle (v1.0.1)
- [x] Manual "check for updates" plugin-row action (v1.0.2)
- [x] Subdomain AJAX fix and normalized GitHub update install folder (v1.0.5)
- [x] SDLC and Claude Code scaffolding (this commit)
