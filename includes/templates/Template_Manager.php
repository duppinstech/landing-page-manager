<?php
namespace LPManager\templates;

class Template_Manager {

    public static function init() {
        add_action( 'wp_ajax_lpmanager_upload_template', [ __CLASS__, 'handle_upload' ] );
        // Template_Loader::load_template (priority 99) handles landing page template; no duplicate filter needed.
    }
	
    private static $current_template = 'whiterail-ai'; // or your desired fallback

    public static function set_current_template( $slug ) {
        self::$current_template = sanitize_title( $slug );
    }

    public static function get_current_template() {
        if ( empty( self::$current_template ) ) {
            return 'whiterail-ai';
        }
        return self::$current_template;
    }
    
    public static function get_template_path( $file ) {
    	$template_slug = self::get_current_template();
        return plugin_dir_path( __DIR__ ) . "templates/{$template_slug}/{$file}";
    }
    
    public static function get_templates() {
        $template_dir = plugin_dir_path(__DIR__) . 'templates/';
        $template_url = plugin_dir_url(__DIR__) . 'templates/';

        $templates = [];

        foreach (glob($template_dir . '*', GLOB_ONLYDIR) as $dir) {
            $slug = basename($dir);

            // Find PHP file with Template Name header
            $template_file = null;
            foreach (glob($dir . '/*.php') as $php_file) {
                $contents = file_get_contents($php_file);
                if (preg_match('/Template Name:\s*(.+)/i', $contents)) {
                    $template_file = $php_file;
                    break;
                }
            }

            if ($template_file) {
                $contents = file_get_contents($template_file);
                preg_match('/Template Name:\s*(.+)/i', $contents, $name_match);
                preg_match('/Description:\s*(.+)/i', $contents, $desc_match);

                $name = isset($name_match[1]) ? trim($name_match[1]) : ucfirst(str_replace('-', ' ', $slug));
                $desc = isset($desc_match[1]) ? trim($desc_match[1]) : '';

                $screenshot_path = $dir . '/screenshot.png';
                $image = file_exists( $dir . '/screenshot.png' )
                    ? $template_url . "{$slug}/screenshot.png"
                    : includes_url( 'images/media/default.png' );

                $templates[ $slug ] = [
                    'name'        => $name,
                    'description'=> $desc,
                    'image_url'   => $image,
                ];
            }
        }

        return $templates;
    }

    /**
     * AJAX: install a template from an uploaded .zip (Settings → Templates).
     *
     * Templates contain executable PHP, so this is treated like installing a plugin:
     * nonce + install_plugins capability (which also honors DISALLOW_FILE_MODS),
     * extraction outside the web root via WP's unzip_file() (rejects ../ paths),
     * and cleanup on every exit path.
     */
    public static function handle_upload() {
        check_ajax_referer( 'lpmanager_nonce', 'nonce' );

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

        require_once ABSPATH . 'wp-admin/includes/file.php';
        if ( ! WP_Filesystem() ) {
            wp_send_json_error( __( 'Could not access the filesystem.', 'landing-page-manager' ), 500 );
        }
        global $wp_filesystem;

        $work_dir = trailingslashit( get_temp_dir() ) . 'lpmanager-template-' . wp_generate_password( 12, false );
        $cleanup  = function () use ( $wp_filesystem, $work_dir ) {
            $wp_filesystem->delete( $work_dir, true );
        };

        $unzipped = unzip_file( $file['tmp_name'], $work_dir );
        if ( is_wp_error( $unzipped ) ) {
            $cleanup();
            wp_send_json_error( __( 'Could not extract the zip file.', 'landing-page-manager' ), 400 );
        }

        // Accept either files at the zip root or a single top-level folder.
        $source  = $work_dir;
        $entries = array_values( array_diff( (array) scandir( $work_dir ), [ '.', '..', '__MACOSX' ] ) );
        if ( 1 === count( $entries ) && is_dir( $work_dir . '/' . $entries[0] ) ) {
            $source = $work_dir . '/' . $entries[0];
            $slug   = sanitize_key( $entries[0] );
        } else {
            $slug = sanitize_key( pathinfo( $name, PATHINFO_FILENAME ) );
        }

        $entry_file = $source . '/single-landing_page.php';
        if ( ! file_exists( $entry_file ) || ! file_exists( $source . '/screenshot.png' ) ) {
            $cleanup();
            wp_send_json_error( __( 'Template must contain single-landing_page.php and screenshot.png.', 'landing-page-manager' ), 400 );
        }
        if ( ! preg_match( '/Template Name:\s*\S/i', (string) file_get_contents( $entry_file ) ) ) {
            $cleanup();
            wp_send_json_error( __( 'single-landing_page.php is missing a "Template Name:" header.', 'landing-page-manager' ), 400 );
        }

        if ( '' === $slug ) {
            $cleanup();
            wp_send_json_error( __( 'Could not determine a valid template name.', 'landing-page-manager' ), 400 );
        }

        $destination = plugin_dir_path( __DIR__ ) . 'templates/' . $slug;
        if ( file_exists( $destination ) ) {
            $cleanup();
            /* translators: %s: template slug */
            wp_send_json_error( sprintf( __( 'A template named "%s" already exists.', 'landing-page-manager' ), $slug ), 409 );
        }

        $wp_filesystem->mkdir( $destination );
        $copied = copy_dir( $source, $destination );
        $cleanup();
        if ( is_wp_error( $copied ) ) {
            $wp_filesystem->delete( $destination, true );
            wp_send_json_error( __( 'Could not install the template.', 'landing-page-manager' ), 500 );
        }

        wp_send_json_success( [ 'slug' => $slug ] );
    }
}
