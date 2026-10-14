# Project context

_Shared, committed memory. Keep it factual and current._

- **Product:** Landing Page Manager, a WordPress plugin. Owner: Odell Duppins / Duppins Technology.
- **Repo:** https://github.com/duppinstech/landing-page-manager (public, GPL-2.0-or-later). The Pro add-on is a separate private repo, `landing-page-manager-pro`.
- **Current release:** v1.0.5 (2026-03-09). Releases are GitHub Releases with tag `vX.Y.Z` and a `landing-page-manager.zip` asset.
- **Distribution:** live sites self-update through `includes/Update_Checker.php`. A bad release reaches clients immediately.
- **Templates:** `template-b`, `template-c`, and `template_a`. The active template comes from the global setting only.
- **Key dependencies:** Carbon Fields 3.6, league/color-extractor, carbon-field-password (pinned to commit 80df427).
- **Integrations:** Google Maps / Places (reviews), Brandfetch (client colors), GitHub Releases API.
- **Dev machine:** Windows 11, XAMPP PHP, Composer, and gh CLI. The repo sits in OneDrive.
- **CI:** `php -l` on 7.4/8.1/8.3. Releases are built by `release.yml` on `v*` tags. No unit tests yet (see PLAN.md).
