---
paths:
  - "landing-page-manager.php"
  - "includes/**/*.php"
---

# API conventions (REST, AJAX, admin-post, hooks)

## REST (`lpmanager/v1`)
- Register in `rest_api_init`. Every route has a real `permission_callback` (usually `current_user_can( 'manage_options' )`). Never use `__return_true` on routes that write.
- Validate and sanitize every argument with `args` → `sanitize_callback` / `validate_callback`.
- Return `WP_REST_Response` or `WP_Error`. Never `echo` (the bootstrap buffers and discards stray output).
- Clients send `X-WP-Nonce` built from `wp_create_nonce( 'wp_rest' )`.

## admin-ajax
- Action names are prefixed (`wp_ajax_lpmanager_*` for new actions; the existing `fetch_google_reviews` stays for compatibility).
- First line of the handler: `check_ajax_referer( 'lpmanager_nonce', 'security' )`, then the capability check.
- Respond with `wp_send_json_success()` or `wp_send_json_error()`.
- Landing pages are served on client subdomains, so build the AJAX URL for the current host. See the subdomain AJAX fix in v1.0.5 and don't regress it.

## admin-post
- `admin_post_lpmanager_*` handlers call `check_admin_referer()` **and** `current_user_can()` before reading `$_POST` or `$_FILES`.
- Finish with `wp_safe_redirect()` followed by `exit`.

## Outbound HTTP
- Use `wp_remote_get()` / `wp_remote_post()` with a timeout. Cache responses in transients (for example, Google reviews and GitHub releases).
- API keys come from settings. Never log them and never send them to the browser unless the API is a browser-key API (Google Maps JS).

## Hooks we expose
- Filters: `lpmanager_unwanted_styles`, `lpmanager_unwanted_style_patterns` (regexes, default `elementor-post-{ID}`), `lpmanager_unwanted_scripts`, `lpmanager_github_releases_api_url`, `lpmanager_github_update_package_url`, `lpmanager_privacy_policy_content`, `lpmanager_load_jquery` (force jQuery on landing pages, e.g. for GTM tags), `lpmanager_allowed_footer_callbacks` (wp_footer callbacks kept on landing pages), `lpmanager_template_for_post` (template slug per landing page; Pro uses it for per-client templates).
- Actions: `lpmanager_landing_page_footer` (fires in the landing-page footer; Additional Scripts print here).
- New hooks are prefixed `lpmanager_`, documented with a docblock, and listed here.
- Renaming or removing a public hook is a breaking change. Deprecate it first.

## Pro boundary
- Core exposes hooks and filters. Pro hooks in. Check `lpmanager_is_premium()` before showing Pro UI. Never duplicate Pro logic here.
