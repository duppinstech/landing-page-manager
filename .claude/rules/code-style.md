---
paths:
  - "**/*.php"
  - "assets/**/*.js"
  - "includes/templates/**/*.js"
  - "**/*.css"
---

# Code style

## PHP
- Target **PHP 7.4**. Not allowed: `match`, enums, `readonly`, constructor promotion, `?->`, `str_contains` (unless polyfilled by WP 5.9+), union types.
- Follow the WordPress Coding Standards: spaces inside parentheses, Yoda conditions for literals, `array()` or `[]` (match the file).
- Indentation: match the file you are editing. Don't reformat lines you didn't change.
- Namespaced classes in `includes/` follow PSR-4 (`LPManager\<dir>\<Class_Name>`). Class names use `Upper_Snake`, matching the existing files.
- Module pattern: `final class` or `class` with `public static function init()` that only registers hooks, called from `Plugin::init()`.
- Global functions live in the main plugin file or a template's `lib/helper-functions.php`. Prefix them `lpmanager_` and wrap them in `function_exists` guards inside templates.
- Strings: always use the `landing-page-manager` text domain with `__()`, `esc_html__()`, and similar.
- Carbon Fields keys are prefixed `lpmanager_`. Read meta through `carbon_get_post_meta()` and options through `carbon_get_theme_option()`.

## JavaScript
- jQuery, wrapped in `(function ($) { ... })(jQuery);` or `jQuery(function ($) { ... })`.
- No build step. Write ES2017 or later only where the template already does.
- Localized data comes from `wp_localize_script`. Never inline secrets.

## CSS
- Scope template CSS under the template's body class or wrapper so it doesn't leak into wp-admin.
- Don't edit vendored libraries (`bootstrap.css`, `swiper-bundle.css`, `*.min.*`).

## Comments
- Explain *why*, not *what*. Add a docblock to every public static method.
