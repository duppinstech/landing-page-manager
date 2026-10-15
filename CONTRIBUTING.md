# Contributing

Thanks for helping improve Landing Page Manager. By contributing you agree that
your contributions are licensed under GPL-2.0-or-later and that the project is
owned by Odell Duppins / Duppins Technology (see [OWNERSHIP.md](OWNERSHIP.md)).

## Workflow
1. Branch from `main`: `feature/<short-name>`, `fix/<short-name>`, or `chore/<short-name>`.
2. Make focused commits. Write messages in the imperative ("Fix subdomain AJAX", not "Fixed…").
3. Lint every PHP file you touched with `php -l`.
4. Update `CHANGELOG.md` under **[Unreleased]**.
5. Open a PR using the template. Link the PLAN.md item or issue it closes.
6. Changes to request handlers, uploads, rewrites, or the updater need a security review.

## Local setup
```bash
git clone https://github.com/duppinstech/landing-page-manager.git wp-content/plugins/landing-page-manager
cd wp-content/plugins/landing-page-manager
composer install
```
Activate the plugin in WP admin, then set a default template under **Landing Pages → Settings**.

## Coding standards
- PHP 7.4 compatible. Follow the WordPress Coding Standards.
- Prefix globals with `lpmanager_`. Use the text domain `landing-page-manager`.
- Sanitize input, escape output, and check a nonce and capability on every state change.
- Detail: [.claude/rules/](.claude/rules/).

## Releases (maintainer only)
1. Bump `Version:` in `landing-page-manager.php` and move the [Unreleased] entries in `CHANGELOG.md` under the new version.
2. Commit, then push to `main`.
3. Push tag `vX.Y.Z`. The Release workflow builds the zip and publishes the GitHub Release with it attached. Don't create the release by hand first.
4. Check the release has the `landing-page-manager.zip` asset.
5. On a test site, open **Plugins** and click **Check for updates** under Landing Page Manager to confirm the update appears. (Dashboard → Updates → Check Again can use a release cached for up to 6 hours.)
