# Project context

_Shared, committed memory. Keep it factual and current._

- **Product:** Landing Page Manager, a WordPress plugin. Owner: Duppins Technology.
- **Repo:** https://github.com/duppinstech/landing-page-manager (public, GPL-2.0-or-later). The Pro add-on is a separate private repo, `landing-page-manager-pro`.
- **Current release:** v1.0.9 (2026-10-17). Releases are GitHub Releases with tag `vX.Y.Z` and a `landing-page-manager.zip` asset.
- **Distribution:** live sites self-update through `includes/Update_Checker.php`. A bad release reaches clients immediately.
- **Templates:** the starters `starter-one` and `starter-two` ship in the plugin; custom templates live in `wp-content/lpmanager-templates/` (client templates come from the private `landing-page-manager-templates` repo). The active template comes from the global setting only.
- **Key dependencies:** Carbon Fields 3.6, league/color-extractor, carbon-field-password (pinned to commit 80df427).
- **Integrations:** Google Maps / Places (reviews), Brandfetch (client colors), GitHub Releases API.
- **Dev machine:** Windows 11, XAMPP PHP, Composer, and gh CLI. The repo sits in OneDrive.
- **CI:** `php -l` on 7.4/8.1/8.3. Releases are built by `release.yml` on `v*` tags. No unit tests yet (see PLAN.md).
