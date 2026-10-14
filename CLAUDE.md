# CLAUDE.md — Landing Page Manager

Loaded every session. Keep under 200 lines; push detail into `.claude/rules/`.

## What this is
A WordPress plugin for managing multi-client landing pages with subdomain
routing, switchable templates, and (via the separate Pro add-on) conversion
analytics. Owned by **Odell Duppins / Duppins Technology** — see
[OWNERSHIP.md](OWNERSHIP.md). Why it exists: [INTENT.md](INTENT.md).
Open action items: [PLAN.md](PLAN.md). Agent roster: [AGENTS.md](AGENTS.md).

## Tech stack
- PHP 7.4+ (must stay 7.4-compatible: no enums, readonly, match, named-arg-only APIs)
- WordPress 5.9+
- Composer, PSR-4 autoload `LPManager\` → `includes/`
- Carbon Fields 3.x for all meta/options (`carbon_get_post_meta`, `carbon_get_theme_option`)
- jQuery-based admin/front JS in `assets/js/` and per-template `js/`
- No build step. No test suite yet (see PLAN.md).

## Architecture
- `landing-page-manager.php` — bootstrap: constants, autoload guard, REST routes, asset dequeue on landing pages, `lpmanager_is_premium()`, `lpmanager_tracking_options()`.
- `includes/Plugin.php` — `Plugin::init()` wires every module in order. Order matters: taxonomies → CPT → assets/validator → templates → ajax → admin → updater.
- `includes/cpt/` — `landing_page` CPT, list-table enhancements, subdomain rewrite rules.
- `includes/taxonomy/` — `client` (branding, domain, template, Brandfetch) and `keyword`.
- `includes/templates/` — `Template_Manager` (registry + zip upload), `Template_Loader` (template_include at priority 99), and one folder per template (`template-b`, `template-c`, `template_a`). Each template has `single-landing_page.php`, `screenshot.png`, `lib/fields/landing-page-fields.php`, `lib/helper-functions.php`.
- `includes/admin/` — dashboard, stats, settings (tabs: Templates, API Keys, Conversion Tracking, Updates).
- `includes/core/` — validator, required-field registry, Google Maps helpers.
- `includes/Update_Checker.php` — GitHub-release updater; normalizes zipball folder name.
- Pro add-on lives in a **separate repo** (`landing-page-manager-pro`, gitignored here). Never put Pro logic in this repo; gate with `lpmanager_is_premium()`.

## Commands
```bash
composer install                      # deps (vendor/ is committed — release zip needs it)
php -l path/to/file.php               # lint a file (also run by the PostToolUse hook)
find includes -name '*.php' -not -path '*/vendor/*' -print0 | xargs -0 -n1 php -l   # lint all
```
Local PHP: `C:\xampp\php\php.exe`. GitHub CLI `gh` is installed and authenticated.

## Conventions (summary — full rules in `.claude/rules/`)
- WordPress Coding Standards; 4-space indent in PHP files that already use it; match the file.
- Prefix everything global: functions `lpmanager_`, options/meta `lpmanager_`, hooks `lpmanager_`, text domain `landing-page-manager`.
- Sanitize on input, escape on output, nonce + capability on every state change.
- New modules: a class with `public static function init()` registered in `Plugin::init()`.
- Template-specific fields belong in that template's `lib/fields/`, not in core.

## Hard rules
- **Never** hardcode API keys (Google Maps, Brandfetch, etc.). Keys live in Settings only.
- **Never** edit `vendor/` by hand. Change `composer.json` and run `composer install`.
- **Never** commit `.env`, `*.pem`, `CLAUDE.local.md`, `.claude/settings.local.json`.
- **Never** bump `Version:` or tag a release without being asked — releases ship to live sites via the GitHub updater.
- Do not commit someone else's uncommitted WIP alongside your change; stage only your files.
- Copyright/author lines: Odell Duppins / Duppins Technology. Client names only describe who work is *for*.

## Safety boundaries
- Ask before: pushing to `main`, creating tags/releases, deleting files, rewriting history, changing the updater.
- Changes to `Update_Checker.php`, `Rewrite_Rules.php`, or `Template_Manager::handle_upload` require the `security-reviewer` agent.

## Memory
Durable project context lives in `.claude/memory/` — read `project-context.md` at the
start of non-trivial work, append to `decisions.md` when an architectural choice is
made, and update `progress.md` when a PLAN.md item moves.
