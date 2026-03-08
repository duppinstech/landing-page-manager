<?php
namespace LPManager\templates;

class Template_Manager {

    public static function init() {
        add_action( 'admin_post_lpmanager_upload_template', [ __CLASS__, 'handle_upload' ] );
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

    public static function handle_upload() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        if (!isset($_FILES['lpmanager_template_upload']) || $_FILES['lpmanager_template_upload']['error'] !== UPLOAD_ERR_OK) {
            wp_redirect(add_query_arg('upload_error', 'true', wp_get_referer()));
            exit;
        }

        $file = $_FILES['lpmanager_template_upload'];

        // Validate file extension is zip
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (strtolower($ext) !== 'zip') {
            wp_redirect(add_query_arg('upload_error', 'invalid_format', wp_get_referer()));
            exit;
        }

        // Move uploaded file to a temporary location
        $tmp_dir = wp_upload_dir()['basedir'] . '/lpmanager_temp_upload';
        if (!file_exists($tmp_dir)) {
            wp_mkdir_p($tmp_dir);
        }
        $tmp_zip_path = $tmp_dir . '/' . basename($file['name']);
        move_uploaded_file($file['tmp_name'], $tmp_zip_path);

        // Extract and validate
        $zip = new ZipArchive;
        if ($zip->open($tmp_zip_path) === TRUE) {
            $extract_path = $tmp_dir . '/extracted_' . time();
            mkdir($extract_path);

            $zip->extractTo($extract_path);
            $zip->close();

            // Validate extracted folder - e.g. check for a PHP template file
            $template_php_files = glob($extract_path . '/*.php');
            $screenshot_png = glob($extract_path . '/screenshot.png');

            if (empty($template_php_files) || empty($screenshot_png)) {
                // Cleanup
                unlink($tmp_zip_path);
                // delete extracted folder recursively here (implement a helper function)
                // Redirect with error
                wp_redirect(add_query_arg('upload_error', 'missing_files', wp_get_referer()));
                exit;
            }

            // Further validate PHP template content (basic check for '<?php')
            $php_content = file_get_contents($template_php_files[0]);
            if (strpos($php_content, '<?php') === false) {
                // Cleanup
                unlink($tmp_zip_path);
                // delete extracted folder recursively
                wp_redirect(add_query_arg('upload_error', 'invalid_template', wp_get_referer()));
                exit;
            }

            // Move extracted folder to plugin templates directory
            $plugin_templates_dir = plugin_dir_path(__DIR__) . 'templates/';
            $template_slug = basename($template_php_files[0], '.php'); // or extract from zip folder name

            $final_template_path = $plugin_templates_dir . $template_slug;

            if (file_exists($final_template_path)) {
                // Avoid overwrite, maybe add suffix or reject upload
                wp_redirect(add_query_arg('upload_error', 'already_exists', wp_get_referer()));
                exit;
            }

            rename($extract_path, $final_template_path);

            // Cleanup uploaded zip
            unlink($tmp_zip_path);

            wp_redirect(add_query_arg('upload_success', 'true', wp_get_referer()));
            exit;
        } else {
            wp_redirect(add_query_arg('upload_error', 'unzip_failed', wp_get_referer()));
            exit;
        }
    }
}