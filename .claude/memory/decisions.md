# Architectural decisions

Append-only log. Format: `## YYYY-MM-DD — Title`, then Context / Decision / Consequences.

## 2026-03 — Pro add-on in a separate repository
**Context:** Analytics and conversion tracking are paid features.
**Decision:** Core exposes hooks; Pro hooks in from its own repo. `lpmanager_is_premium()` checks a function only Pro defines, so setting a constant can't unlock Pro.
**Consequences:** No Pro code in this repo. Core UI must degrade gracefully when Pro is absent.

## 2026-03 — Self-hosted updates through GitHub Releases
**Context:** The plugin isn't on WordPress.org yet, and client sites need updates.
**Decision:** `Update_Checker` reads the latest GitHub release, with a settings toggle for GitHub, WP.org, or both. Installs are normalized to the `landing-page-manager/` folder.
**Consequences:** Every tag is a production deploy, so releases need the `/deploy` checklist.

## 2026-03 — Commit vendor/
**Context:** Sites install from the release zip and have no Composer.
**Decision:** `vendor/` is committed and shipped.
**Consequences:** Never hand-edit vendor. Dependency bumps are deliberate commits.

## 2026-10-14 — Template upload is plugin-install privilege
**Decision:** Uploading a template requires `install_plugins` and its own nonce. Archives are extracted into `templates/.staging-*` and moved into place with `rename()`. Only the direct filesystem method is supported.
**Consequences:** Non-super-admins on multisite, and sites with `DISALLOW_FILE_MODS`, can't upload templates. This is intended, because templates are executable PHP.

## 2026-10-14 — Release zip is built by CI, not committed
**Decision:** `.github/workflows/release.yml` builds `landing-page-manager.zip` from the tagged commit, using the committed `vendor/` and the exclusions in `.distignore`, and creates the release with the asset attached in one step.
**Consequences:** Never create a release by hand before CI runs. A release without the asset makes sites fall back to the GitHub zipball.

## 2026-10-14 — Claude Code / SDLC scaffolding
**Decision:** Adopted CLAUDE.md, AGENTS.md, INTENT.md, PLAN.md, and the `.claude/` rules, commands, skills, agents, hooks, memory, and workflows. Hooks enforce PHP lint, block secret and vendor writes, and run phpcbf when it's available.
