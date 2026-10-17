<?php
namespace LPManager\templates;

class Template_Manager {

    /** Nonce action for the template upload — distinct from lpmanager_nonce, which is printed on public pages. */
    const UPLOAD_NONCE = 'lpmanager_upload_template';

    /** Templates that ship with the plugin, in the order their tiles appear. Their slugs are reserved. */
    const STARTER_TEMPLATES = [ 'starter-one', 'starter-two' ];

    /** Template for new sites, and the fallback when none is set. */
    const DEFAULT_TEMPLATE = 'starter-one';

    /**
     * Folder under wp-content for custom and uploaded templates. Plugin updates replace the
     * plugin folder, so templates kept here survive them.
     */
    const CUSTOM_DIR_NAME = 'lpmanager-templates';

    /** Set once every template that was inside the plugin folder has a copy in the custom folder. */
    const COPIED_OPTION = 'lpmanager_templates_copied';

    /** Set once pin_legacy_default_template() has run. */
    const PIN_OPTION = 'lpmanager_templates_default_checked';

    /** WP-Cron event that copies templates out of the plugin folder. */
    const COPY_EVENT = 'lpmanager_copy_templates';

    public static function init() {
        add_action( 'wp_ajax_lpmanager_upload_template', [ __CLASS__, 'handle_upload' ] );
        add_action( 'admin_init', [ __CLASS__, 'remove_legacy_upload_dir' ] );
        // Before Carbon Fields registers fields (init, priority 0), so even the first request after an
        // update registers the fields of the template it renders.
        add_action( 'init', [ __CLASS__, 'pin_legacy_default_template' ], -1 );
        // Templates are copied in WP-Cron and on admin pages, never while a visitor waits. Until then,
        // get_template_dir() keeps using the copy inside the plugin folder.
        add_action( 'init', [ __CLASS__, 'maybe_schedule_copy' ] );
        add_action( self::COPY_EVENT, [ __CLASS__, 'copy_pending_templates' ] );
        // Admin pages re-check, so a restored site or another server gets its copies too.
        add_action( 'admin_init', [ __CLASS__, 'copy_pending_templates_on_admin' ] );
        add_filter( 'upgrader_pre_install', [ __CLASS__, 'block_update_while_templates_at_risk' ], 10, 2 );
        // Uploading a plugin ZIP with "Replace current with uploaded" (or wp plugin install --force) skips
        // upgrader_pre_install's plugin check; this runs before the old folder is cleared. After the
        // updater's own folder-name fix (priority 10).
        add_filter( 'upgrader_source_selection', [ __CLASS__, 'block_replace_while_templates_at_risk' ], 20, 3 );
        // Template_Loader::load_template (priority 99) handles landing page template; no duplicate filter needed.
    }

    private static $current_template = self::DEFAULT_TEMPLATE;

    public static function set_current_template( $slug ) {
        self::$current_template = sanitize_title( $slug );
    }

    public static function get_current_template() {
        if ( empty( self::$current_template ) ) {
            return self::DEFAULT_TEMPLATE;
        }
        return self::$current_template;
    }

    /** Folder of the templates that ship with the plugin, with a trailing slash. */
    public static function bundled_dir() {
        return plugin_dir_path( __DIR__ ) . 'templates/';
    }

    /** Folder of custom and uploaded templates (wp-content/lpmanager-templates/), with a trailing slash. */
    public static function custom_dir() {
        return trailingslashit( WP_CONTENT_DIR ) . self::CUSTOM_DIR_NAME . '/';
    }

    /**
     * Folder of an installed template, with a trailing slash, or '' when it isn't installed.
     *
     * Starter templates always come from the plugin. Any other template comes from the custom
     * folder, or from the plugin folder while it hasn't been copied out yet (sites updating from
     * 1.0.9 or earlier, where every template lived inside the plugin).
     *
     * @param string|null $slug Template slug. Default: the current template.
     * @return string
     */
    public static function get_template_dir( $slug = null ) {
        $slug = sanitize_title( null === $slug ? self::get_current_template() : (string) $slug );
        if ( '' === $slug ) {
            return '';
        }
        $roots = in_array( $slug, self::STARTER_TEMPLATES, true )
            ? [ self::bundled_dir() ]
            : [ self::custom_dir(), self::bundled_dir() ];
        foreach ( $roots as $root ) {
            if ( file_exists( $root . $slug . '/single-landing_page.php' ) ) {
                return $root . $slug . '/';
            }
        }
        return '';
    }

    /**
     * URL of a file in a template, for stylesheets, scripts and images.
     *
     * @param string      $file Path inside the template folder, e.g. 'css/main.css'. Default: the folder itself.
     * @param string|null $slug Template slug. Default: the current template.
     * @return string '' when the template isn't installed.
     */
    public static function get_template_url( $file = '', $slug = null ) {
        $dir = self::get_template_dir( $slug );
        if ( '' === $dir ) {
            return '';
        }
        $custom = self::custom_dir();
        $base   = 0 === strpos( $dir, $custom )
            ? trailingslashit( content_url( self::CUSTOM_DIR_NAME ) ) . substr( $dir, strlen( $custom ) )
            : plugin_dir_url( __DIR__ ) . 'templates/' . substr( $dir, strlen( self::bundled_dir() ) );
        return $base . ltrim( (string) $file, '/' );
    }

    /** Whether a template is installed in either location. */
    public static function template_exists( $slug ) {
        return '' !== self::get_template_dir( $slug );
    }

    public static function get_template_path( $file ) {
        $dir = self::get_template_dir();
        // A missing template keeps the old path, so callers' file_exists() checks still fail cleanly.
        return ( '' !== $dir ? $dir : self::bundled_dir() . self::get_current_template() . '/' ) . $file;
    }

    /**
     * Installed templates, keyed by slug: the starter templates first, then custom and uploaded
     * templates by name. A template is a folder whose single-landing_page.php carries a
     * "Template Name:" header.
     *
     * @return array<string, array{name: string, description: string, image_url: string}>
     */
    public static function get_templates() {
        $slugs = [];
        foreach ( [ self::custom_dir(), self::bundled_dir() ] as $root ) {
            foreach ( (array) glob( $root . '*', GLOB_ONLYDIR ) as $dir ) {
                $slug = basename( $dir );
                // Skip in-progress upload staging dirs (Windows glob matches dot-folders).
                if ( '' !== $slug && '.' !== $slug[0] ) {
                    $slugs[ $slug ] = true;
                }
            }
        }

        $starters = [];
        $others   = [];
        foreach ( array_keys( $slugs ) as $slug ) {
            $dir = self::get_template_dir( $slug );
            if ( '' === $dir ) {
                continue;
            }

            // get_file_data() reads only the first 8 KB.
            $headers = get_file_data( $dir . 'single-landing_page.php', [ 'name' => 'Template Name', 'description' => 'Description' ] );
            if ( '' === $headers['name'] ) {
                continue;
            }

            $info = [
                'name'        => $headers['name'],
                'description' => $headers['description'],
                'image_url'   => file_exists( $dir . 'screenshot.png' )
                    ? self::get_template_url( 'screenshot.png', $slug )
                    : includes_url( 'images/media/default.png' ),
            ];
            if ( in_array( $slug, self::STARTER_TEMPLATES, true ) ) {
                $starters[ $slug ] = $info;
            } else {
                $others[ $slug ] = $info;
            }
        }

        uksort( $starters, function ( $a, $b ) {
            return array_search( $a, self::STARTER_TEMPLATES, true ) - array_search( $b, self::STARTER_TEMPLATES, true );
        } );
        uasort( $others, function ( $a, $b ) {
            return strnatcasecmp( $a['name'], $b['name'] );
        } );

        return $starters + $others;
    }

    /**
     * Once per site: before 1.0.10 the template setting defaulted to whiterail-ai. A site that has
     * landing pages but never saved the setting relied on that default, so store it, and the new
     * default (a starter) doesn't switch its pages to another design.
     */
    public static function pin_legacy_default_template() {
        if ( get_option( self::PIN_OPTION ) ) {
            return;
        }
        // Carbon Fields stores theme options as "_<field name>". Post types aren't registered this
        // early, so check the posts table directly.
        global $wpdb;
        if ( false === get_option( '_lpmanager_active_template' )
            && $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s LIMIT 1", 'landing_page' ) ) ) {
            add_option( '_lpmanager_active_template', 'whiterail-ai' );
        }
        update_option( self::PIN_OPTION, 1, true );
    }

    /** Queue the copy in WP-Cron until it has succeeded once. */
    public static function maybe_schedule_copy() {
        if ( get_option( self::COPIED_OPTION ) || get_transient( 'lpmanager_templates_copy_retry' ) || wp_next_scheduled( self::COPY_EVENT ) ) {
            return;
        }
        wp_schedule_single_event( time(), self::COPY_EVENT );
    }

    /** Admin-page re-check: administrators only, and never on AJAX requests (heartbeat, logged-out admin-ajax). */
    public static function copy_pending_templates_on_admin() {
        if ( wp_doing_ajax() || ! current_user_can( 'activate_plugins' ) ) {
            return;
        }
        self::copy_pending_templates();
    }

    /**
     * Copy templates out of the plugin folder before an update can delete them (WP-Cron and admin pages).
     * Once nothing is pending, this costs one folder scan and no database writes.
     */
    public static function copy_pending_templates() {
        $copied = ! self::pending_templates();
        if ( ! $copied ) {
            if ( ! wp_is_file_mod_allowed( 'lpmanager_copy_templates' ) ) {
                // These sites deploy the plugin another way; the admin notice says to copy the folders by hand.
                if ( ! get_transient( 'lpmanager_templates_copy_retry' ) ) {
                    set_transient( 'lpmanager_templates_copy_retry', 1, DAY_IN_SECONDS );
                }
                return;
            }
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            // Atomic lock (a ten-minute timeout covers a request that dies mid-copy).
            if ( ! \WP_Upgrader::create_lock( 'lpmanager_templates_copy', 10 * MINUTE_IN_SECONDS ) ) {
                return;
            }
            $copied = self::move_bundled_custom_templates();
            \WP_Upgrader::release_lock( 'lpmanager_templates_copy' );
            if ( ! $copied ) {
                // For example, wp-content isn't writable: WP-Cron tries again in an hour, admin pages on every load.
                if ( ! get_transient( 'lpmanager_templates_copy_retry' ) ) {
                    set_transient( 'lpmanager_templates_copy_retry', 1, HOUR_IN_SECONDS );
                }
            }
        }
        // Write only when the state changes.
        if ( (int) get_option( self::COPIED_OPTION ) !== (int) $copied ) {
            update_option( self::COPIED_OPTION, (int) $copied, true );
        }
    }

    /**
     * Templates other than the starters that exist only inside the plugin folder, keyed by slug.
     * A custom-folder copy without single-landing_page.php (an interrupted copy) doesn't count.
     *
     * @return array<string, string> Slug => folder inside the plugin.
     */
    public static function pending_templates() {
        $pending = [];
        foreach ( (array) glob( self::bundled_dir() . '*', GLOB_ONLYDIR ) as $dir ) {
            $slug = basename( $dir );
            if ( '' === $slug || '.' === $slug[0] || in_array( $slug, self::STARTER_TEMPLATES, true )
                || ! file_exists( $dir . '/single-landing_page.php' )
                || file_exists( self::custom_dir() . $slug . '/single-landing_page.php' ) ) {
                continue;
            }
            $pending[ $slug ] = $dir;
        }
        return $pending;
    }

    /**
     * Stop a plugin update (manual or automatic) while a template exists only inside the plugin
     * folder, because the update deletes that folder.
     *
     * @param bool|\WP_Error $response   Installation response.
     * @param array          $hook_extra Extra arguments passed to hooked filters.
     * @return bool|\WP_Error
     */
    public static function block_update_while_templates_at_risk( $response, $hook_extra ) {
        if ( is_wp_error( $response ) || empty( $hook_extra['plugin'] )
            || plugin_basename( LP_MANAGER_PLUGIN_PATH . 'landing-page-manager.php' ) !== $hook_extra['plugin'] ) {
            return $response;
        }
        $error = self::templates_at_risk_error();
        return $error ? $error : $response;
    }

    /**
     * The same check for a ZIP uploaded over this plugin ("Replace current with uploaded").
     *
     * @param string|\WP_Error $source        Folder of the unpacked package.
     * @param string           $remote_source Folder the package was unpacked into.
     * @param \WP_Upgrader     $upgrader      Upgrader instance.
     * @return string|\WP_Error
     */
    public static function block_replace_while_templates_at_risk( $source, $remote_source, $upgrader ) {
        if ( is_wp_error( $source ) || ! ( $upgrader instanceof \Plugin_Upgrader )
            || basename( untrailingslashit( (string) $source ) ) !== basename( untrailingslashit( LP_MANAGER_PLUGIN_PATH ) ) ) {
            return $source;
        }
        $error = self::templates_at_risk_error();
        return $error ? $error : $source;
    }

    /** Try the copy now; if templates are still only inside the plugin, an error naming them. */
    private static function templates_at_risk_error() {
        if ( ! self::pending_templates() || self::move_bundled_custom_templates() ) {
            return null;
        }
        // Another request may have finished the copy meanwhile.
        $pending = array_keys( self::pending_templates() );
        if ( ! $pending ) {
            return null;
        }
        return new \WP_Error(
            'lpmanager_templates_not_copied',
            __( 'Landing Page Manager was not updated.', 'landing-page-manager' ) . ' ' . self::pending_templates_message( $pending )
        );
    }

    /**
     * What to do about templates that exist only inside the plugin folder (admin notice and update error).
     *
     * @param string[] $slugs Pending template slugs.
     * @return string
     */
    public static function pending_templates_message( array $slugs ) {
        $from = self::display_path( self::bundled_dir() );
        $to   = self::display_path( self::custom_dir() );
        if ( ! wp_is_file_mod_allowed( 'lpmanager_copy_templates' ) ) {
            /* translators: 1: template folder names, 2: source folder, 3: destination folder */
            $message = _n(
                'The template %1$s is still stored only inside the plugin folder. This site does not allow file changes from WordPress, so copy it from %2$s into %3$s before you deploy the next plugin version, or that deploy deletes it. If a folder with that name is already in %3$s but incomplete, finish copying it or delete it.',
                'The templates %1$s are still stored only inside the plugin folder. This site does not allow file changes from WordPress, so copy them from %2$s into %3$s before you deploy the next plugin version, or that deploy deletes them. If a folder with one of those names is already in %3$s but incomplete, finish copying it or delete it.',
                count( $slugs ),
                'landing-page-manager'
            );
        } else {
            /* translators: 1: template folder names, 2: source folder, 3: destination folder */
            $message = _n(
                'The template %1$s is still stored only inside the plugin folder, where a plugin update would delete it, so updating the plugin is blocked. Make wp-content writable and reload this page, or copy it from %2$s into %3$s yourself. If a folder with that name is already in %3$s but incomplete, finish copying it or delete it.',
                'The templates %1$s are still stored only inside the plugin folder, where a plugin update would delete them, so updating the plugin is blocked. Make wp-content writable and reload this page, or copy them from %2$s into %3$s yourself. If a folder with one of those names is already in %3$s but incomplete, finish copying it or delete it.',
                count( $slugs ),
                'landing-page-manager'
            );
        }
        return sprintf( $message, wp_sprintf_l( '%l', $slugs ), $from, $to );
    }

    /** A folder path as admins see it: relative to the WordPress root when it is inside it. */
    public static function display_path( $path ) {
        $root = wp_normalize_path( untrailingslashit( ABSPATH ) ) . '/';
        $path = wp_normalize_path( $path );
        return 0 === strpos( $path, $root ) ? substr( $path, strlen( $root ) ) : $path;
    }

    /**
     * Copy every pending template (see pending_templates()) into the custom folder. Templates are
     * copied, not moved: the plugin folder is replaced on the next update anyway, and this version can
     * still run from the originals if a copy fails.
     *
     * @return bool Whether every template is now in the custom folder.
     */
    public static function move_bundled_custom_templates() {
        $pending = self::pending_templates();
        if ( ! $pending ) {
            return true;
        }
        if ( ! self::ensure_custom_dir() ) {
            return false;
        }

        $ok = true;
        foreach ( $pending as $slug => $dir ) {
            // A folder without single-landing_page.php is an interrupted copy, perhaps one made by hand:
            // never overwrite it. The admin notice lists the template until someone finishes it.
            if ( file_exists( self::custom_dir() . $slug ) ) {
                // Unless another request finished it in the meantime.
                $ok = $ok && file_exists( self::custom_dir() . $slug . '/single-landing_page.php' );
                continue;
            }
            $staging = self::custom_dir() . '.staging-' . wp_generate_password( 12, false );
            // A fatal error or timeout mid-copy must not leave a partial PHP tree behind.
            register_shutdown_function( [ __CLASS__, 'delete_dir' ], $staging );
            // Copy into a hidden folder and rename(), so a half-copied template is never used.
            if ( ! self::copy_dir( $dir, $staging ) || ! @rename( $staging, self::custom_dir() . $slug ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors
                self::delete_dir( $staging );
                // Another request may have copied it first.
                $ok = $ok && file_exists( self::custom_dir() . $slug . '/single-landing_page.php' );
            }
        }
        return $ok;
    }

    /** Create the custom templates folder, with an index.php so it can't be listed. */
    private static function ensure_custom_dir() {
        $dir = self::custom_dir();
        if ( ! wp_mkdir_p( $dir ) || ! wp_is_writable( $dir ) ) {
            return false;
        }
        self::write_index_file( $dir );
        return true;
    }

    /**
     * Put an empty index.php in a folder, so servers that list folders show nothing. (An .htaccess
     * with "Options -Indexes" would return errors on hosts that don't allow Options overrides.)
     */
    private static function write_index_file( $dir ) {
        $file = trailingslashit( $dir ) . 'index.php';
        if ( ! file_exists( $file ) && false !== @file_put_contents( $file, "<?php\n// Silence is golden.\n" ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions, WordPress.PHP.NoSilencedErrors
            @chmod( $file, self::file_mode() ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
        }
    }

    /** Permissions WordPress uses for files it writes (FS_CHMOD_FILE). */
    private static function file_mode() {
        return defined( 'FS_CHMOD_FILE' ) ? FS_CHMOD_FILE : ( ( fileperms( ABSPATH . 'index.php' ) & 0777 ) | 0644 );
    }

    /** Recursive copy with native functions, so it also works without filesystem credentials (WP-Cron). */
    private static function copy_dir( $from, $to ) {
        if ( ! wp_mkdir_p( $to ) ) {
            return false;
        }
        try {
            $items = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator( $from, \FilesystemIterator::SKIP_DOTS ),
                \RecursiveIteratorIterator::SELF_FIRST
            );
            $dirs = [ $to ];
            foreach ( $items as $item ) {
                $target = $to . '/' . substr( $item->getPathname(), strlen( $from ) + 1 );
                if ( $item->isDir() ) {
                    if ( ! wp_mkdir_p( $target ) ) {
                        return false;
                    }
                    $dirs[] = $target;
                } elseif ( ! @copy( $item->getPathname(), $target ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors -- reported through the return value and the admin notice.
                    return false;
                } else {
                    @chmod( $target, self::file_mode() ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
                }
            }
        } catch ( \Exception $e ) {
            // An unreadable folder must not break every admin page; the copy is retried later.
            return false;
        }
        foreach ( $dirs as $dir ) {
            self::write_index_file( $dir );
        }
        return true;
    }

    /** Delete a folder tree. Public only so register_shutdown_function() can call it. */
    public static function delete_dir( $dir ) {
        if ( ! is_dir( $dir ) ) {
            return;
        }
        try {
            $items = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator( $dir, \FilesystemIterator::SKIP_DOTS ),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ( $items as $item ) {
                $item->isDir() ? @rmdir( $item->getPathname() ) : @unlink( $item->getPathname() ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
            }
        } catch ( \Exception $e ) {
            return;
        }
        @rmdir( $dir ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
    }

    /**
     * AJAX: install a template from an uploaded .zip (Settings → Templates).
     *
     * Templates contain executable PHP, so this is treated like installing a plugin:
     * dedicated nonce + install_plugins capability (which also honors DISALLOW_FILE_MODS
     * and limits multisite to super admins). The archive is pre-scanned, extracted into
     * a hidden staging folder next to its destination, and moved into place with a
     * single rename() so a half-copied template is never live. Cleanup also runs on
     * shutdown so fatals and timeouts don't leave extracted PHP behind.
     */
    public static function handle_upload() {
        check_ajax_referer( self::UPLOAD_NONCE, 'nonce' );

        if ( ! current_user_can( 'install_plugins' ) ) {
            wp_send_json_error( __( 'You do not have permission to install templates.', 'landing-page-manager' ), 403 );
        }

        if ( ! isset( $_FILES['template_zip'] ) || UPLOAD_ERR_OK !== $_FILES['template_zip']['error'] ) {
            wp_send_json_error( __( 'No file was uploaded.', 'landing-page-manager' ), 400 );
        }

        $file = $_FILES['template_zip'];
        $name = sanitize_file_name( wp_unslash( $file['name'] ) );
        if ( 'zip' !== strtolower( pathinfo( $name, PATHINFO_EXTENSION ) ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
            wp_send_json_error( __( 'Please upload a .zip file.', 'landing-page-manager' ), 400 );
        }

        $problem = self::scan_archive( $file['tmp_name'] );
        if ( $problem ) {
            wp_send_json_error( $problem, 400 );
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        // Native checks and rename() below assume direct access, like the rest of this plugin's file handling.
        if ( 'direct' !== get_filesystem_method() || ! WP_Filesystem() ) {
            wp_send_json_error( __( 'Template upload requires direct filesystem access on this server.', 'landing-page-manager' ), 500 );
        }

        // Uploads go to wp-content/lpmanager-templates/, which plugin updates don't touch.
        if ( ! self::ensure_custom_dir() ) {
            wp_send_json_error(
                sprintf(
                    /* translators: %s: folder path, e.g. wp-content/lpmanager-templates */
                    __( 'Could not create or write to the %s folder. Ask your host to make wp-content writable, or copy the template folder there yourself.', 'landing-page-manager' ),
                    self::display_path( self::custom_dir() )
                ),
                500
            );
        }
        $templates_dir = self::custom_dir();
        $staging       = $templates_dir . '.staging-' . wp_generate_password( 12, false );
        $cleanup       = function () use ( $staging ) {
            global $wp_filesystem;
            if ( $wp_filesystem && file_exists( $staging ) ) {
                $wp_filesystem->delete( $staging, true );
            }
        };
        register_shutdown_function( $cleanup );

        // Force ZipArchive so extraction uses the same parser scan_archive() vetted.
        add_filter( 'unzip_file_use_ziparchive', '__return_true', PHP_INT_MAX );
        $unzipped = unzip_file( $file['tmp_name'], $staging );
        remove_filter( 'unzip_file_use_ziparchive', '__return_true', PHP_INT_MAX );
        if ( is_wp_error( $unzipped ) ) {
            $cleanup();
            wp_send_json_error( __( 'Could not extract the zip file.', 'landing-page-manager' ), 400 );
        }

        // Accept either files at the zip root or a single top-level folder.
        $source  = $staging;
        $entries = array_values( array_diff( (array) scandir( $staging ), [ '.', '..', '__MACOSX' ] ) );
        if ( 1 === count( $entries ) && is_dir( $staging . '/' . $entries[0] ) ) {
            $source = $staging . '/' . $entries[0];
            $slug   = sanitize_key( $entries[0] );
        } else {
            $slug = sanitize_key( pathinfo( $name, PATHINFO_FILENAME ) );
        }

        $entry_file = $source . '/single-landing_page.php';
        if ( ! file_exists( $entry_file ) || ! file_exists( $source . '/screenshot.png' ) ) {
            $cleanup();
            wp_send_json_error( __( 'Template must contain single-landing_page.php and screenshot.png.', 'landing-page-manager' ), 400 );
        }
        $headers = get_file_data( $entry_file, [ 'name' => 'Template Name' ] );
        if ( '' === $headers['name'] ) {
            $cleanup();
            wp_send_json_error( __( 'single-landing_page.php is missing a "Template Name:" header.', 'landing-page-manager' ), 400 );
        }

        if ( '' === $slug || '.' === $slug[0] ) {
            $cleanup();
            wp_send_json_error( __( 'Could not determine a valid template name.', 'landing-page-manager' ), 400 );
        }

        $destination = $templates_dir . $slug;
        $exists_msg  = sprintf(
            /* translators: 1: template slug, 2: folder path */
            __( 'A template named "%1$s" is already installed. To update it, replace its folder in %2$s.', 'landing-page-manager' ),
            $slug,
            self::display_path( self::custom_dir() )
        );
        if ( in_array( $slug, self::STARTER_TEMPLATES, true ) ) {
            $cleanup();
            wp_send_json_error(
                sprintf(
                    /* translators: %s: template slug */
                    __( 'The name "%s" belongs to a starter template. Rename your template\'s folder and upload it again.', 'landing-page-manager' ),
                    $slug
                ),
                409
            );
        }
        // Also refuse templates still inside the plugin folder.
        if ( file_exists( $destination ) || self::template_exists( $slug ) ) {
            $cleanup();
            wp_send_json_error( $exists_msg, 409 );
        }

        // rename() is the atomic step: it fails if a concurrent upload claimed the slug first.
        if ( ! @rename( $source, $destination ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors
            $cleanup();
            wp_send_json_error( file_exists( $destination ) ? $exists_msg : __( 'Could not install the template.', 'landing-page-manager' ), file_exists( $destination ) ? 409 : 500 );
        }

        $cleanup();
        wp_send_json_success( [ 'slug' => $slug ] );
    }

    /**
     * Reject archives with entries WordPress's validate_file() doesn't catch
     * (backslash traversal on Windows hosts, absolute paths) or server-config files.
     *
     * @param string $zip_path Path to the uploaded archive.
     * @return string Error message, or '' when the archive is acceptable.
     */
    private static function scan_archive( $zip_path ) {
        if ( ! class_exists( 'ZipArchive' ) ) {
            return __( 'The PHP zip extension is required to upload templates.', 'landing-page-manager' );
        }

        $zip = new \ZipArchive();
        if ( true !== $zip->open( $zip_path ) ) {
            return __( 'Could not read the zip file.', 'landing-page-manager' );
        }

        $blocked = array_filter( [ '.htaccess', '.user.ini', 'php.ini', 'web.config', strtolower( (string) ini_get( 'user_ini.filename' ) ) ] );
        $problem = '';
        for ( $i = 0; $i < $zip->numFiles; $i++ ) {
            $entry = (string) $zip->getNameIndex( $i );
            $base  = strtolower( basename( $entry ) );
            $ext   = strtolower( pathinfo( $entry, PATHINFO_EXTENSION ) );

            if ( false !== strpos( $entry, '\\' ) || '/' === substr( $entry, 0, 1 ) || preg_match( '#(^|/)\.\.(/|$)#', $entry ) || preg_match( '/^[a-z]:/i', $entry ) ) {
                $problem = __( 'The zip contains unsafe file paths.', 'landing-page-manager' );
                break;
            }
            if ( in_array( $base, $blocked, true ) || in_array( $ext, [ 'phar', 'phtml' ], true ) ) {
                /* translators: %s: file name inside the zip */
                $problem = sprintf( __( 'The zip contains a disallowed file: %s', 'landing-page-manager' ), $entry );
                break;
            }
        }
        $zip->close();

        return $problem;
    }

    /**
     * Admin housekeeping for extracted PHP that must not linger:
     * - staging dirs orphaned when a worker was killed mid-upload (shutdown cleanup never ran);
     * - versions before 1.0.6 extracted into uploads/lpmanager_temp_upload/ — removed once.
     */
    public static function remove_legacy_upload_dir() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $stale = [];
        // Uploads staged in the plugin folder before 1.0.10, in the custom folder since.
        $staging_dirs = array_merge(
            (array) glob( self::bundled_dir() . '.staging-*', GLOB_ONLYDIR ),
            (array) glob( self::custom_dir() . '.staging-*', GLOB_ONLYDIR )
        );
        foreach ( $staging_dirs as $dir ) {
            if ( filemtime( $dir ) < time() - HOUR_IN_SECONDS ) {
                $stale[] = $dir;
            }
        }

        $legacy       = wp_upload_dir()['basedir'] . '/lpmanager_temp_upload';
        $check_legacy = ! get_option( 'lpmanager_legacy_upload_dir_removed' );
        if ( ! $stale && ! ( $check_legacy && file_exists( $legacy ) ) ) {
            if ( $check_legacy ) {
                update_option( 'lpmanager_legacy_upload_dir_removed', 1, false );
            }
            return;
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        if ( ! WP_Filesystem() ) {
            return; // Try again on a later request.
        }
        global $wp_filesystem;

        foreach ( $stale as $dir ) {
            $wp_filesystem->delete( $dir, true );
        }
        // Only mark done once the folder is actually gone, so a failed delete is retried.
        if ( $check_legacy && ( ! file_exists( $legacy ) || $wp_filesystem->delete( $legacy, true ) ) ) {
            update_option( 'lpmanager_legacy_upload_dir_removed', 1, false );
        }
    }
}
