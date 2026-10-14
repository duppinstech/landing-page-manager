---
paths:
  - "tests/**"
  - "includes/**/*.php"
---

# Testing standard

There is no automated test suite yet (PLAN.md P1). Until there is, the minimum for every change is below.

## Minimum (every change)
1. `php -l` passes on every changed PHP file. The PostToolUse hook does this automatically.
2. Manual smoke test on a WordPress site:
   - Activate the plugin without notices (`WP_DEBUG` on).
   - Admin: the Landing Pages list, the edit screen, Clients, Settings tabs, and the Dashboard load without JS console errors.
   - Front end: a published landing page renders on its client subdomain, and the forms or AJAX on it work.
3. If you touched the updater, test the Check Again flow against a draft release.

## When the suite exists
- Location: `tests/unit/` (pure PHP, Brain Monkey) and `tests/integration/` (wp-phpunit).
- Naming: `Test_<Class_Name>.php`, methods `test_<behavior>_<condition>`.
- Every bug fix ships with a regression test that fails before the fix.
- Unit tests must not hit the network. Mock `wp_remote_*` with `pre_http_request`.
- Run on PHP 7.4 and the latest 8.x in CI.

## Priority test targets
`Update_Checker` (version compare, package URL, folder normalization), `Landing_Page_Validator`, `Required_Fields_Registry`, `Rewrite_Rules` host parsing, `Template_Manager::get_templates`.
