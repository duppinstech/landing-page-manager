# Changelog

All notable changes to this project are documented here.
Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) · Versioning: [SemVer](https://semver.org/).

## [Unreleased]
### Security
- The updater only installs a release asset named exactly `landing-page-manager.zip` or `landing-page-manager-<version>.zip`. Before, any zip with `landing-page-manager` in its name matched, including a Pro zip.
- Template zip upload now requires its own nonce and the `install_plugins` capability. It pre-scans archives for unsafe paths and for `.htaccess`/`.phar` files, and installs through a staging folder with a single atomic rename. Cleanup also runs after fatal errors. Previously the upload had no CSRF protection and could leave extracted PHP in `uploads/`. That old folder is now removed once on upgrade.
### Fixed
- Template upload from **Settings → Templates** works. The JS and PHP handler had drifted apart, so every upload failed.
### Changed
- The repository moved to `duppinstech/landing-page-manager`. The updater now checks releases there.
- Per-post Elementor CSS (`elementor-post-{ID}`) is now dequeued on landing pages by pattern instead of hardcoded IDs. The new filter `lpmanager_unwanted_style_patterns` controls the patterns.
- The release zip is no longer committed. The `Release` GitHub Action builds it and attaches it when a `v*` tag is pushed (`bin/build-zip.sh`, `.distignore`).
### Added
- `uninstall.php`: removes plugin settings and transients. Landing pages and terms are kept unless `LPMANAGER_UNINSTALL_DELETE_CONTENT` is defined as true.
- CI: PHP lint on PHP 7.4, 8.1 and 8.3 for every push and PR.
- SDLC and Claude Code project scaffolding: `CLAUDE.md`, `AGENTS.md`, `INTENT.md`, `PLAN.md`, `OWNERSHIP.md`, `CONTRIBUTING.md`, `SECURITY.md`, GitHub PR and issue templates, and the `.claude/` rules, commands, skills, agents, hooks, memory, and workflows.

## [1.0.5] - 2026-03-09
### Fixed
- Subdomain AJAX requests.
- GitHub update installs now always unpack to the `landing-page-manager/` folder.
### Changed
- Overhauled the core UX of the Template B template.

## [1.0.2] - 2026-03-09
### Added
- Manual "check for updates" action on the plugin row.

## [1.0.1] - 2026-03-09
### Added
- Selectable update source: GitHub, WordPress.org, or both.

## [1.0.0]
- Initial public release.

[Unreleased]: https://github.com/duppinstech/landing-page-manager/compare/v1.0.5...HEAD
[1.0.5]: https://github.com/duppinstech/landing-page-manager/releases/tag/v1.0.5
[1.0.2]: https://github.com/duppinstech/landing-page-manager/releases/tag/v1.0.2
[1.0.1]: https://github.com/duppinstech/landing-page-manager/releases/tag/v1.0.1
