---
name: wp-code-review
description: Checklist for reviewing WordPress plugin PHP/JS changes in Landing Page Manager — security (nonces, caps, sanitize/escape), PHP 7.4 compatibility, Carbon Fields usage, hook prefixes, Pro boundary. Use when reviewing a diff, PR, or file in this repo.
---

# WordPress plugin code review

Work through the diff hunk by hunk. Report only real problems, with `file:line`.

## Security (High)
- [ ] State-changing handlers check a nonce (`check_ajax_referer`, `check_admin_referer`, REST `wp_rest`) **and** `current_user_can()`.
- [ ] Every `$_GET/$_POST/$_REQUEST/$_SERVER` read is `wp_unslash`ed and then sanitized with the right function (`sanitize_text_field`, `absint`, `sanitize_key`, `esc_url_raw`).
- [ ] Every output is escaped for its context (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`, `wp_json_encode`).
- [ ] SQL goes through `$wpdb->prepare()`.
- [ ] File operations: no user-controlled paths, no extraction into web-reachable dirs, cleanup on every exit path.
- [ ] Outbound requests use `wp_remote_*` with a timeout. A user-supplied URL is an SSRF risk.
- [ ] No hardcoded keys or tokens.

## Correctness (Medium)
- [ ] PHP 7.4 compatible (see `.claude/rules/code-style.md`).
- [ ] Carbon Fields getters are only called after `carbon_fields_register_fields` / boot.
- [ ] Hooks are registered in `init()` and wired in `Plugin::init()` in the correct order.
- [ ] Subdomain-aware URLs (`home_url` vs the current host) for AJAX and assets on landing pages.
- [ ] Rewrite rules changed means a flush on activation, never on every request.
- [ ] Updater changes keep `normalize_install_source_dir` producing a `landing-page-manager/` folder.

## Conventions (Low)
- [ ] `lpmanager_` prefix on functions, options, and hooks. Text domain is `landing-page-manager`.
- [ ] No Pro logic in core. UI is gated on `lpmanager_is_premium()`.
- [ ] Docblocks on public methods. No reformatting of untouched lines.
- [ ] CHANGELOG [Unreleased] is updated.
