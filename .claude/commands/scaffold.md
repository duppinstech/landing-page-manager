---
description: Scaffold a new landing-page template or a new core module
argument-hint: "template <slug> | module <dir>/<Class_Name>"
---

Request: $ARGUMENTS

## `template <slug>`
Model it on `includes/templates/whiterail-ai/`. Create `includes/templates/<slug>/` containing:
- `single-landing_page.php`: the entry point Template_Loader resolves
- `header.php`, `footer.php`, `index.php`, `404.php`
- `style.css`: the header comment carries `Template Name`, and the author is Odell Duppins / Duppins Technology
- `screenshot.png`: a placeholder. Template_Manager requires it.
- `lib/fields/landing-page-fields.php`: Carbon Fields container for this template only, keys prefixed `lpmanager_<slug>_`
- `lib/helper-functions.php`: functions wrapped in `function_exists` guards
- `lib/assets.php`: returns the template's `styles` and `scripts` in load order (`key => [ path, deps ]`). Only listed files load. Without it, every file in `css/` and `js/` loads.
- `css/main.css`, `js/main.js`, scoped to the template wrapper

Do **not** copy the vendored bootstrap, swiper, or jquery files unless the template needs them (PLAN.md P2 tracks consolidating them).

## `module <dir>/<Class_Name>`
- Create `includes/<dir>/<Class_Name>.php` with namespace `LPManager\<dir>` and a `public static function init()` that only registers hooks.
- Register it in `includes/Plugin.php` at the correct point in the init order, and explain the placement.
- Run `composer dump-autoload` if this is a new directory.

After scaffolding, run `php -l` on the new files and list what's left for the human to fill in.
