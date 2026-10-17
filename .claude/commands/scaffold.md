---
description: Scaffold a new landing-page template or a new core module
argument-hint: "template <slug> | module <dir>/<Class_Name>"
---

Request: $ARGUMENTS

## `template <slug>`
Copy `includes/templates/starter-one/` to `wp-content/lpmanager-templates/<slug>/` on the site (client templates belong in the private `landing-page-manager-templates` repository, never in this one). A template contains:
- `single-landing_page.php`: the entry point Template_Loader resolves. Its header comment carries `Template Name` (the upload check reads it there) and keeps the author, copyright and licence lines
- `screenshot.png`: the tile image (1200x900). Uploads require it.
- `lib/fields/landing-page-fields.php`: Carbon Fields container for this template; rename its title
- `lib/page-data.php`: reads the page and client data once (the starters' pattern)
- `lib/assets.php`: returns `styles` and `scripts` in load order (`key => [ path, deps ]`), plus `'cdn_assets' => false` unless the template uses Google Fonts or Font Awesome. Only listed files load.
- `css/main.css` (and `js/` only if needed), optionally `404.php`
- File URLs through `Template_Manager::get_template_url()`, file paths through `__DIR__`

Load third-party libraries only if the template needs them, and list them in `lib/assets.php`.

## `module <dir>/<Class_Name>`
- Create `includes/<dir>/<Class_Name>.php` with namespace `LPManager\<dir>` and a `public static function init()` that only registers hooks.
- Register it in `includes/Plugin.php` at the correct point in the init order, and explain the placement.
- Run `composer dump-autoload` if this is a new directory.

After scaffolding, run `php -l` on the new files and list what's left for the human to fill in.
