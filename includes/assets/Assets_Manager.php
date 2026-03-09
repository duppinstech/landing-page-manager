<?php
namespace LPManager\assets;

Use LPManager\templates\Template_Manager;

class Assets_Manager {

    /**
     * Static init method to standardize initialization
     */
    public static function init() {
        add_action('template_redirect', function() {
            if (is_singular('landing_page')) {
                // Remove visual head output, not the enqueues
                remove_action('wp_head', 'rest_output_link_wp_head', 10);
                remove_action('wp_head', 'wp_resource_hints', 2);
                remove_action('wp_head', 'feed_links', 2);
                remove_action('wp_head', 'feed_links_extra', 3);
                remove_action('wp_head', 'rsd_link');
                remove_action('wp_head', 'wlwmanifest_link');
                remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
                remove_action('wp_head', 'locale_stylesheet');
                remove_action('wp_head', 'print_emoji_detection_script', 7);
                remove_action('wp_head', 'wp_shortlink_wp_head', 10);
                remove_action('wp_head', 'wp_generator');
                remove_action('wp_head', 'rel_canonical');

                // You could leave this if you want admin bar spacing
                // add_action('wp_head', '_admin_bar_bump_cb');

                // Footer cleanup
                remove_all_actions('wp_footer');
                add_action('wp_footer', 'wp_print_footer_scripts', 20);
                add_action('wp_footer', 'wp_auth_check_html', 100);
                if ( is_admin_bar_showing() ) {
                    add_action('wp_footer', 'wp_admin_bar_render', 1000);
                }
            }
        });

        add_action('wp_head', function() {
            if (is_singular('landing_page')) {
                $client_terms = get_the_terms(get_the_ID(), 'client');
                if (!empty($client_terms) && !is_wp_error($client_terms)) {
                    $tracking_code = carbon_get_term_meta($client_terms[0]->term_id, 'header_tracking_code');
                    if (!empty($tracking_code)) {
                        echo $tracking_code;
                    }
                }
            }
        });

        $instance = new self();
        add_action('wp_enqueue_scripts', [$instance, 'enqueueAssets'], 999);

        // Hook filters for admin bar assets
        add_filter('script_loader_src', [self::class, 'makeAdminBarAssetsRelative'], 10, 2);
        add_filter('style_loader_src', [self::class, 'makeAdminBarAssetsRelative'], 10, 2);
    }

    /**
     * Enqueue CSS and JS assets
     */
    public function enqueueAssets() {
        if (!is_singular('landing_page') || is_admin()) {
            return;
        }

        // --- DEQUEUE unwanted assets ---
        $unwanted_styles = [
            'elementor-common',
            'elementor-pro',
            'elementor-frontend',
            'elementor-post-3',
            'elementor-post-2708',
            'elementor-post-2702',
            'elementor-post-2693',
            'hello-elementor',
            'hello-elementor-theme-style',
            'hello-elementor-header-footer',
            'elementor-icons',  // sometimes enqueued separately
            'font-awesome',
            'fontawesome',
            // add any others as needed
        ];

        foreach ($unwanted_styles as $style) {
            wp_dequeue_style($style);
        }

        $unwanted_scripts = [
            'elementor-common',
            'elementor-pro-notes',
            'elementor-pro-notes-app-initiator',
            'elementor-pro-app',
            'elementor-app-loader',
            'elementor-frontend',
            // add others if needed
        ];

        foreach ($unwanted_scripts as $script) {
            wp_dequeue_script($script);
        }

        // --- ENSURE critical WordPress scripts ---
        if (is_user_logged_in()) {
            wp_enqueue_script('wp-auth-check');
        }

        global $wp_styles;
        $fa_found = false;

        foreach ( $wp_styles->queue as $handle ) {
            if ( strpos( $handle, 'fontawesome' ) !== false || strpos( $handle, 'font-awesome' ) !== false ) {
                wp_deregister_style( $handle );
            }
        }
        // Enqueue your preferred FA version
        wp_enqueue_style('my-font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', [], '5.15.4');

        // Optional: enqueue v4 compatibility shims if you need them
        wp_enqueue_style('my-font-awesome-v4-shims', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/v4-shims.min.css', ['my-font-awesome'], '5.15.4');

        // --- ENQUEUE external fonts ---
        wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700;800&family=Bebas+Neue&family=Roboto:wght@300;400;500;700&display=swap', [], null);
        wp_enqueue_style('swiper-css', 'https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css');
        wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js', [], null, true);
        wp_enqueue_script('lpmanager-reviews', LP_MANAGER_PLUGIN_URL . 'assets/js/reviews.js', ['jquery', 'swiper-js'], '1.0', true);
        wp_localize_script( 'lpmanager-reviews', 'lpmanager_vars', [
            'ajaxurl' => admin_url( 'admin-ajax.php', 'relative' ),
            'nonce'   => wp_create_nonce( 'lpmanager_nonce' ),
            'post_id' => get_the_ID(),
        ] );

        // --- ENQUEUE landing page local assets ---
        $includes_dir = dirname(plugin_dir_path(__FILE__));
        $includes_url = plugin_dir_url($includes_dir . '/dummy.php');
        $current_template = Template_Manager::get_current_template();

        if ( empty( $current_template ) ) {
            return;
        }

        $template_dir = $includes_dir . '/templates/' . $current_template . '/';
        $template_url = $includes_url . 'templates/' . $current_template . '/';

        $this->enqueueFilesInDir($template_dir . 'css/', $template_url . 'css/', 'style');
        $this->enqueueFilesInDir($template_dir . 'js/', $template_url . 'js/', 'script');

        // --- Load jQuery and UI if needed ---
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-datepicker');

        if (defined('WP_DEBUG') && WP_DEBUG) {
            global $wp_styles, $wp_scripts;
            //error_log('Enqueued Styles: ' . print_r($wp_styles->queue, true));
            //error_log('Enqueued Scripts: ' . print_r($wp_scripts->queue, true));
        }
    }

    /**
     * Loop through files in a directory and enqueue them
     */
    private function enqueueFilesInDir($dir, $url, $type = 'style') {
        if ( ! is_dir( $dir ) ) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $filename  = $file->getFilename();
                $extension = pathinfo($filename, PATHINFO_EXTENSION);
                $relative_path = str_replace($dir, '', $file->getPathname());
                $file_url = $url . $relative_path;
                $handle   = sanitize_title($filename) . '-' . md5($file->getRealPath());
                
                // Log what the function is doing
            	//error_log("Assets_Manager: Found file $filename (type: $extension) at $file_url");

                if ($type === 'style' && $extension === 'css') {
                    wp_enqueue_style($handle, $file_url);
                    //error_log("Assets_Manager: Enqueued CSS file $file_url with handle $handle");
                }

                if ($type === 'script' && $extension === 'js') {
                    wp_enqueue_script($handle, $file_url, ['jquery'], null, true);
                    //error_log("Assets_Manager: Enqueued JS file $file_url with handle $handle");
                }
            }
        }
    }

    /**
     * Convert admin bar asset URLs to relative URLs when admin bar is visible
     */
    public static function makeAdminBarAssetsRelative($src, $handle) {
        if (is_admin_bar_showing()) {
            $site_url = site_url();
            if (strpos($src, $site_url) === 0) {
                $src = str_replace($site_url, '', $src);
            }
        }
        return $src;
    }

    public static function get_contrast_text_color($hex_color) {
        $hex_color = str_replace('#', '', $hex_color);
        if (strlen($hex_color) !== 6) {
            return '#000000'; // fallback if invalid
        }

        $r = hexdec(substr($hex_color, 0, 2));
        $g = hexdec(substr($hex_color, 2, 2));
        $b = hexdec(substr($hex_color, 4, 2));

        $brightness = ($r * 299 + $g * 587 + $b * 114) / 1000;
        return ($brightness < 128) ? '#FFFFFF' : '#000000';
    }

}