# Changelog

All notable changes to this project are documented here.
Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) · Versioning: [SemVer](https://semver.org/).

## [Unreleased]
### Removed
- **The "Landing Page Template" field is gone from the client screen.** It has been ignored since 1.0.6. Every landing page uses the template chosen in **Landing Pages → Settings → Templates**. Values already saved on clients stay in the database but are not used.
### Changed
- **Template A "Style variant" has moved** from the "Landing Page Details" box to the new **Layout** tab of the "Template A" box. Saved styles are kept. The field only appears when Template A is the active template.
- Template A's Style 3 label no longer names a client.

## [1.0.6] - 2026-10-14
### Security
- The updater only installs a release asset named exactly `landing-page-manager.zip` or `landing-page-manager-<version>.zip`. Before, any zip with `landing-page-manager` in its name matched, including a Pro zip.
- Template zip upload now requires its own nonce and the `install_plugins` capability. It pre-scans archives for unsafe paths and for `.htaccess`/`.phar` files, and installs through a staging folder with a single atomic rename. Cleanup also runs after fatal errors. Previously the upload had no CSRF protection and could leave extracted PHP in `uploads/`. That old folder is now removed once on upgrade.
### Fixed
- The first save of a new client now geocodes the address and extracts brand colors. These ran before Carbon Fields had saved the client's fields.
- Landing pages no longer show a false "session expired" dialog.
- Client B maps fall back to classic markers when no Map ID is set, and marker clicks work with both marker types.
- Template upload from **Settings → Templates** works. The JS and PHP handler had drifted apart, so every upload failed.
### Changed
- **The template is now chosen only by the global setting** (Settings → Templates). The per-client and per-page template choices are ignored.
- The required-field check covers client, keyword and client phone, plus each template's registered fields. Notices are escaped, and debug logging is removed.
- Landing pages no longer have the content editor. All content comes from fields.
- Template CSS and JS are versioned by file modification time, so browsers pick up changes immediately.
- Analytics heatmap previews render at a 1366px desktop width, scaled to fit, so the click grid lines up with the page.
- Template B: the review slider autoplays, the FAQ accordion is full-width, and hover colors are fixed.
- The repository moved to `duppinstech/landing-page-manager`. The updater now checks releases there.
- Per-post Elementor CSS (`elementor-post-{ID}`) is now dequeued on landing pages by pattern instead of hardcoded IDs. The new filter `lpmanager_unwanted_style_patterns` controls the patterns.
- The release zip is no longer committed. The `Release` GitHub Action builds it and attaches it when a `v*` tag is pushed (`bin/build-zip.sh`, `.distignore`).
### Added
- **Template A template** for towing and roadside services, with three layout styles (Style variant).
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

[Unreleased]: https://github.com/duppinstech/landing-page-manager/compare/v1.0.6...HEAD
[1.0.6]: https://github.com/duppinstech/landing-page-manager/compare/v1.0.5...v1.0.6
[1.0.5]: https://github.com/duppinstech/landing-page-manager/releases/tag/v1.0.5
[1.0.2]: https://github.com/duppinstech/landing-page-manager/releases/tag/v1.0.2
[1.0.1]: https://github.com/duppinstech/landing-page-manager/releases/tag/v1.0.1
