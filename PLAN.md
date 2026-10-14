# PLAN.md — action items

Living backlog for the plugin and its deployment. Update status here; log progress
in `.claude/memory/progress.md`. Priority: **P0** = before next release, **P1** = soon, **P2** = later.

Status keys: `[ ]` todo · `[~]` in progress · `[x]` done

_Last reviewed: 2026-10-14 (current release: v1.0.5; next: v1.0.6)_

## P0 — before the next release

- [x] **Secure template zip upload.** Done on `security/template-upload`: dedicated nonce plus the `install_plugins` capability, archive pre-scan, a staging folder with an atomic rename, shutdown and stale-folder cleanup, and removal of the legacy `uploads/` folder. The `security-reviewer` verdict was ok. This also fixed the upload itself, which had never worked because the JS and PHP had drifted apart. Needs a manual test on a WP site before release.
- [ ] **Triage uncommitted WIP.** 12 modified files (analytics, validator, CPT, client taxonomy, template loader, whiterail templates) plus the untracked `includes/templates/quicktow/` template. Review, split into logical commits, and add a CHANGELOG entry.
- [x] **Stop tracking `landing-page-manager.zip` in git.** It is untracked and gitignored now. `bin/build-zip.sh` and the Release workflow build it. Older copies stay in history; rewriting history to purge them is optional and not planned.
- [x] **Remove site-specific dequeue handles.** `elementor-post-{ID}` is now matched by pattern, using the new filter `lpmanager_unwanted_style_patterns`.

## P1 — deployment and quality

- [x] **CI: lint on push/PR.** `.github/workflows/ci.yml` runs `php -l` on PHP 7.4, 8.1 and 8.3.
- [ ] **CI: PHPCS (WordPress Coding Standards).** Start non-blocking. This needs the dev-dependency decision below.
- [x] **CI: release automation.** `.github/workflows/release.yml` runs on a `v*` tag. It lints, checks that the tag matches `Version:`, builds the zip using `.distignore`, checks the zip layout, and creates the release with the zip and changelog notes. It uses the committed `vendor/`, so the shipped zip is exactly what was tested.
- [ ] **Decision needed: dev dependencies while `vendor/` is committed.** `composer require --dev phpunit` would commit PHPUnit into `vendor/`. Options: (a) CI runs `composer install` with dev dependencies and the release builds with `--no-dev`, which means no longer committing `vendor/`; (b) install the dev tools globally in CI only (`tools/` or phive).
- [ ] **Test harness.** Blocked by the decision above. Add PHPUnit with `wp-phpunit` or Brain Monkey. First targets: `Update_Checker::version_compare`, `normalize_install_source_dir`, `Landing_Page_Validator`, rewrite-rule host parsing.
- [x] **Pin Composer dependency.** `carbon-field-password` is pinned to `dev-master#80df427`, the commit already locked and vendored. Upstream has no tags.
- [x] **CHANGELOG backfill** for v1.0.1–v1.0.5.
- [x] **Add a copyright line to LICENSE.**
- [ ] **Store uploaded templates outside the plugin folder.** (From the security review.) Every plugin update replaces `includes/templates/`, which silently deletes uploaded templates; their landing pages then fall back to a 404. Move uploads to `wp-content/lpmanager-templates/<slug>/` and have `get_templates()` and `Template_Loader` search both locations, with shipped templates taking priority.
- [ ] **Guard template field loading.** (From the security review.) `Template_Loader` `require_once`s `<slug>/lib/fields/landing-page-fields.php` on wp-admin requests, so a broken uploaded template can take down wp-admin. Do this after the WIP in `Template_Loader.php` is committed.
- [x] **Uninstall routine.** `uninstall.php` removes `lpmanager_*` and `_lpmanager_*` options (but not `lpmanager_pro_*`) and the validation transients, on every site in a multisite network. Content is kept unless `LPMANAGER_UNINSTALL_DELETE_CONTENT` is true. Needs a test on a WP site.
- [x] **Repo moved to `duppinstech/landing-page-manager`.** Updated the remote, the updater API URL, the Plugin URI and the docs.

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
- [x] SDLC and Claude Code scaffolding (2026-10-14)
