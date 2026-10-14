<?php
namespace LPManager\templates;

class Template_Manager {

    /** Nonce action for the template upload — distinct from lpmanager_nonce, which is printed on public pages. */
    const UPLOAD_NONCE = 'lpmanager_upload_template';

    public static function init() {
        add_action( 'wp_ajax_lpmanager_upload_template', [ __CLASS__, 'handle_upload' ] );
        add_action( 'admin_init', [ __CLASS__, 'remove_legacy_upload_dir' ] );
        // Template_Loader::load_template (priority 99) handles landing page template; no duplicate filter needed.
    }

    private static $current_template = 'template-b'; // or your desired fallback

    public static function set_current_template( $slug ) {
        self::$current_template = sanitize_title( $slug );
    }

    public static function get_current_template() {
        if ( empty( self::$current_template ) ) {
            return 'template-b';
        }
        return self::$current_template;
    }

    public static function get_template_path( $file ) {
    	$template_slug = self::get_current_template();
        return plugin_dir_path( __DIR__ ) . "templates/{$template_slug}/{$file}";
    }

    /**
     * Installed templates, keyed by slug. A template is a folder whose
     * single-landing_page.php carries a "Template Name:" header.
     *
     * @return array<string, array{name: string, description: string, image_url: string}>
     */
    public static function get_templates() {
        $template_dir = plugin_dir_path(__DIR__) . 'templates/';
        $template_url = plugin_dir_url(__DIR__) . 'templates/';

        $templates = [];

        foreach (glob($template_dir . '*', GLOB_ONLYDIR) as $dir) {
            $slug = basename($dir);
            // Skip in-progress upload staging dirs (Windows glob matches dot-folders).
            if ( '.' === $slug[0] ) {
                continue;
            }

            $template_file = $dir . '/single-landing_page.php';
            if ( ! file_exists( $template_file ) ) {
                continue;
            }

            // get_file_data() reads only the first 8 KB.
            $headers = get_file_data( $template_file, [ 'name' => 'Template Name', 'description' => 'Description' ] );
            if ( '' === $headers['name'] ) {
                continue;
            }

            $image = file_exists( $dir . '/screenshot.png' )
                ? $template_url . "{$slug}/screenshot.png"
                : includes_url( 'images/media/default.png' );

            $templates[ $slug ] = [
                'name'        => $headers['name'],
                'description' => $headers['description'],
                'image_url'   => $image,
            ];
        }

        return $templates;
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

        $templates_dir = plugin_dir_path( __DIR__ ) . 'templates/';
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
        if ( file_exists( $destination ) ) {
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
        foreach ( (array) glob( plugin_dir_path( __DIR__ ) . 'templates/.staging-*', GLOB_ONLYDIR ) as $dir ) {
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
