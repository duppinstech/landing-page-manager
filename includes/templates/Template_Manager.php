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

    /** Stores HOUSEKEEPING_VERSION once the one-time template housekeeping has run. */
    const HOUSEKEEPING_OPTION = 'lpmanager_templates_housekeeping';

    /** Bump to run maybe_run_housekeeping() again on every site. */
    const HOUSEKEEPING_VERSION = 1;

    public static function init() {
        add_action( 'wp_ajax_lpmanager_upload_template', [ __CLASS__, 'handle_upload' ] );
        add_action( 'admin_init', [ __CLASS__, 'remove_legacy_upload_dir' ] );
        // Before template_redirect, so the first landing page request after an update already uses the copies.
        add_action( 'init', [ __CLASS__, 'maybe_run_housekeeping' ], 1 );
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

    /** Whether the template is running from inside the plugin folder although it isn't a starter. */
    public static function is_at_risk( $slug ) {
        $slug = sanitize_title( (string) $slug );
        return ! in_array( $slug, self::STARTER_TEMPLATES, true )
            && 0 === strpos( self::get_template_dir( $slug ), self::bundled_dir() );
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
     * Once per site: copy templates out of the plugin folder before an update can delete them.
     */
    public static function maybe_run_housekeeping() {
        if ( (int) get_option( self::HOUSEKEEPING_OPTION ) >= self::HOUSEKEEPING_VERSION || get_transient( 'lpmanager_templates_housekeeping_retry' ) ) {
            return;
        }
        self::pin_legacy_default_template();
        if ( self::move_bundled_custom_templates() ) {
            update_option( self::HOUSEKEEPING_OPTION, self::HOUSEKEEPING_VERSION, true );
        } else {
            // Copying failed (for example, wp-content isn't writable): retry later, not on every request.
            set_transient( 'lpmanager_templates_housekeeping_retry', 1, HOUR_IN_SECONDS );
        }
    }

    /**
     * Before 1.0.10 the template setting defaulted to whiterail-ai. A site that has landing pages but
     * never saved the setting relied on that default, so store it, and the new default (a starter)
     * doesn't switch its pages to another design.
     */
    private static function pin_legacy_default_template() {
        // Carbon Fields stores theme options as "_<field name>".
        if ( false !== get_option( '_lpmanager_active_template' ) ) {
            return;
        }
        $has_pages = get_posts( [ 'post_type' => 'landing_page', 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids', 'no_found_rows' => true ] );
        if ( $has_pages ) {
            add_option( '_lpmanager_active_template', 'whiterail-ai' );
        }
    }

    /**
     * Copy every template in the plugin folder that isn't a starter into the custom folder, unless a
     * template with that slug is already there. Templates are copied, not moved: the plugin folder is
     * replaced on the next update anyway, and this version can still run from the originals if a copy fails.
     *
     * @return bool Whether every template is now in the custom folder.
     */
    public static function move_bundled_custom_templates() {
        $pending = [];
        foreach ( (array) glob( self::bundled_dir() . '*', GLOB_ONLYDIR ) as $dir ) {
            $slug = basename( $dir );
            if ( '' === $slug || '.' === $slug[0] || in_array( $slug, self::STARTER_TEMPLATES, true )
                || ! file_exists( $dir . '/single-landing_page.php' ) || file_exists( self::custom_dir() . $slug ) ) {
                continue;
            }
            $pending[ $slug ] = $dir;
        }
        if ( ! $pending ) {
            return true;
        }
        if ( ! self::ensure_custom_dir() ) {
            return false;
        }

        $ok = true;
        foreach ( $pending as $slug => $dir ) {
            $staging = self::custom_dir() . '.staging-' . wp_generate_password( 12, false );
            // Copy into a hidden folder and rename(), so a half-copied template is never used.
            if ( ! self::copy_dir( $dir, $staging ) || ! @rename( $staging, self::custom_dir() . $slug ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors
                self::delete_dir( $staging );
                // Another request may have copied it first.
                $ok = $ok && file_exists( self::custom_dir() . $slug . '/single-landing_page.php' );
            }
        }
        return $ok;
    }

    /** Create the custom templates folder, with an index.php so the folder can't be listed. */
    private static function ensure_custom_dir() {
        $dir = self::custom_dir();
        if ( ! wp_mkdir_p( $dir ) ) {
            return false;
        }
        if ( ! file_exists( $dir . 'index.php' ) ) {
            file_put_contents( $dir . 'index.php', "<?php\n// Silence is golden.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions
        }
        return is_writable( $dir );
    }

    /** Recursive copy with native functions, so it also works on front-end requests. */
    private static function copy_dir( $from, $to ) {
        if ( ! wp_mkdir_p( $to ) ) {
            return false;
        }
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator( $from, \FilesystemIterator::SKIP_DOTS ),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ( $items as $item ) {
            $target = $to . '/' . substr( $item->getPathname(), strlen( $from ) + 1 );
            if ( $item->isDir() ) {
                if ( ! wp_mkdir_p( $target ) ) {
                    return false;
                }
            } elseif ( ! copy( $item->getPathname(), $target ) ) {
                return false;
            }
        }
        return true;
    }

    private static function delete_dir( $dir ) {
        if ( ! is_dir( $dir ) ) {
            return;
        }
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator( $dir, \FilesystemIterator::SKIP_DOTS ),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ( $items as $item ) {
            $item->isDir() ? @rmdir( $item->getPathname() ) : @unlink( $item->getPathname() ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
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
                    __( 'Could not create or write to the %s folder.', 'landing-page-manager' ),
                    'wp-content/' . self::CUSTOM_DIR_NAME
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
            /* translators: %s: template slug */
            __( 'A template named "%s" already exists.', 'landing-page-manager' ),
            $slug
        );
        // Also refuse starter slugs and templates still inside the plugin folder.
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
