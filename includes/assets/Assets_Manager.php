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

                // Footer cleanup: drop other plugins' footer output except allowlisted callbacks,
                // then print scripts and the plugin's own footer hook (Additional Scripts).
                $kept_footer_callbacks = self::allowlisted_footer_callbacks();
                remove_all_actions('wp_footer');
                foreach ( $kept_footer_callbacks as $kept ) {
                    add_action( 'wp_footer', $kept['function'], $kept['priority'], $kept['accepted_args'] );
                }
                add_action('wp_footer', 'wp_print_footer_scripts', 20);
                add_action('wp_footer', 'lpmanager_custom_landing_page_footer', 25);
                // wp_auth_check_html breaks on stripped landing templates (false "session expired").
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

        add_filter('wp_auth_check_load', function ($load) {
            if (function_exists('is_singular') && is_singular('landing_page')) {
                return false;
            }
            return $load;
        }, 5);

        // Hook filters for admin bar assets
        add_filter('script_loader_src', [self::class, 'makeAdminBarAssetsRelative'], 10, 2);
        add_filter('style_loader_src', [self::class, 'makeAdminBarAssetsRelative'], 10, 2);
    }

    /**
     * wp_footer callbacks that survive the landing-page footer cleanup.
     *
     * @return array<int, array{function: callable, priority: int, accepted_args: int}>
     */
    private static function allowlisted_footer_callbacks() {
        global $wp_filter;
        /**
         * Filters which wp_footer callbacks are kept on landing pages, which otherwise drop
         * other plugins' footer output. Name functions as 'function_name' and methods as
         * 'Class_Name::method_name' (closures can't be allowlisted).
         *
         * @param string[] $allowed Callback names. Default empty.
         */
        $allowed = array_map( 'strval', (array) apply_filters( 'lpmanager_allowed_footer_callbacks', [] ) );
        // These are re-added by the cleanup itself; allowlisting them would print twice.
        $allowed = array_diff( $allowed, [ 'wp_print_footer_scripts', 'lpmanager_custom_landing_page_footer', 'wp_admin_bar_render' ] );
        if ( ! $allowed || empty( $wp_filter['wp_footer'] ) || ! isset( $wp_filter['wp_footer']->callbacks ) ) {
            return [];
        }
        $kept = [];
        foreach ( $wp_filter['wp_footer']->callbacks as $priority => $callbacks ) {
            foreach ( $callbacks as $callback ) {
                $function = $callback['function'];
                if ( is_string( $function ) ) {
                    $name = $function;
                } elseif ( is_array( $function ) && 2 === count( $function ) && is_string( $function[1] ) ) {
                    $name = ( is_object( $function[0] ) ? get_class( $function[0] ) : (string) $function[0] ) . '::' . $function[1];
                } else {
                    continue;
                }
                if ( in_array( $name, $allowed, true ) ) {
                    $kept[] = [
                        'function'      => $function,
                        'priority'      => (int) $priority,
                        'accepted_args' => (int) $callback['accepted_args'],
                    ];
                }
            }
        }
        return $kept;
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
        // Swiper is not loaded from a CDN: the templates that use it ship their own copy (see lib/assets.php).
        // assets/js/reviews.js is intentionally not loaded: no template renders #reviews-container, and
        // each call was a billed Google Places request. Reviews are fetched from the editor and saved
        // into the template's reviews field instead (Reviews_Ajax).

        // jQuery is not loaded for every page: manifest templates pull it in through their script
        // dependencies (QuickTow doesn't need it). Keep it when the client's tracking code looks like
        // it uses jQuery.
        /**
         * Filters whether landing pages load jQuery even if the template doesn't need it.
         *
         * Return true for tags that need jQuery but aren't in the client's Header Tracking Code,
         * for example a jQuery-based Custom HTML tag in Google Tag Manager.
         *
         * @param bool $load    Default: whether Additional Scripts or the client's Header Tracking Code appear to use jQuery or a GTM container.
         * @param int  $post_id Landing page ID.
         */
        if ( apply_filters( 'lpmanager_load_jquery', self::client_tracking_code_uses_jquery(), get_queried_object_id() ) ) {
            wp_enqueue_script('jquery');
        }

        // --- ENQUEUE landing page local assets ---
        $includes_dir = dirname(plugin_dir_path(__FILE__));
        $includes_url = plugin_dir_url($includes_dir . '/dummy.php');
        $current_template = Template_Manager::get_current_template();

        if ( empty( $current_template ) ) {
            return;
        }

        $template_dir = $includes_dir . '/templates/' . $current_template . '/';
        $template_url = $includes_url . 'templates/' . $current_template . '/';

        if ( file_exists( $template_dir . 'lib/assets.php' ) ) {
            $this->enqueueTemplateManifest( $current_template, $template_dir, $template_url );
        } else {
            // Templates without a manifest (e.g. uploaded ones) keep the old behaviour: jQuery plus every file.
            wp_enqueue_script('jquery');
            $this->enqueueFilesInDir($template_dir . 'css/', $template_url . 'css/', 'style');
            $this->enqueueFilesInDir($template_dir . 'js/', $template_url . 'js/', 'script');
        }

        if (defined('WP_DEBUG') && WP_DEBUG) {
            global $wp_styles, $wp_scripts;
            //error_log('Enqueued Styles: ' . print_r($wp_styles->queue, true));
            //error_log('Enqueued Scripts: ' . print_r($wp_scripts->queue, true));
        }
    }

    /**
     * Whether the current page's client "Header Tracking Code" (printed in wp_head) appears to use
     * jQuery: it mentions jQuery, calls $( or $., or loads a Google Tag Manager container (gtm.js / GTM-),
     * whose Custom HTML tags often do. The gtag.js snippet alone doesn't count: it can't run such tags.
     *
     * @return bool
     */
    private static function client_tracking_code_uses_jquery() {
        // Site-wide Additional Scripts can also need jQuery (a false positive only loads jQuery).
        $code = function_exists( 'carbon_get_theme_option' ) ? (string) carbon_get_theme_option( 'lpmanager_additional_scripts' ) : '';
        $terms = get_the_terms( get_queried_object_id(), 'client' );
        if ( ! empty( $terms ) && ! is_wp_error( $terms ) && function_exists( 'carbon_get_term_meta' ) ) {
            // Operator says so explicitly ("Tracking tags need jQuery" on the client screen).
            if ( carbon_get_term_meta( $terms[0]->term_id, 'lpmanager_client_needs_jquery' ) ) {
                return true;
            }
            $code .= "\n" . (string) carbon_get_term_meta( $terms[0]->term_id, 'header_tracking_code' );
        }
        if ( '' === trim( $code ) ) {
            return false;
        }
        foreach ( [ 'jquery', '$(', '$.', 'gtm.js', 'GTM-' ] as $needle ) {
            if ( false !== stripos( $code, $needle ) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Enqueue the assets a template declares in lib/assets.php, in the order listed.
     *
     * Loading every file in css/ and js/ pulled in duplicate and unused libraries
     * (two Bootstraps, a second jQuery that replaced WordPress's own, payform, jQuery UI).
     */
    private function enqueueTemplateManifest($slug, $template_dir, $template_url) {
        $manifest = include $template_dir . 'lib/assets.php';
        if ( ! is_array( $manifest ) ) {
            return;
        }

        $prefix = 'lpm-' . sanitize_key( $slug ) . '-';
        foreach ( [ 'styles', 'scripts' ] as $group ) {
            if ( empty( $manifest[ $group ] ) || ! is_array( $manifest[ $group ] ) ) {
                continue;
            }
            // Entries whose files exist, so dependencies don't depend on the order entries are listed in.
            $available = [];
            foreach ( $manifest[ $group ] as $key => $asset ) {
                $path = isset( $asset[0] ) ? ltrim( (string) $asset[0], '/' ) : '';
                if ( '' !== $path && false === strpos( $path, '..' ) && file_exists( $template_dir . $path ) ) {
                    $available[ $key ] = true;
                }
            }
            foreach ( $manifest[ $group ] as $key => $asset ) {
                $path = isset( $asset[0] ) ? ltrim( (string) $asset[0], '/' ) : '';
                if ( '' === $path || false !== strpos( $path, '..' ) || ! file_exists( $template_dir . $path ) ) {
                    continue;
                }
                $deps = [];
                foreach ( isset( $asset[1] ) ? (array) $asset[1] : [] as $dep ) {
                    if ( ! isset( $manifest[ $group ][ $dep ] ) ) {
                        $deps[] = $dep; // Core handle such as 'jquery'.
                    } elseif ( isset( $available[ $dep ] ) ) {
                        // Another entry in this group gets its prefixed handle.
                        $deps[] = $prefix . $dep;
                    }
                    // An entry skipped above (missing file) is dropped: WordPress would otherwise
                    // silently skip this asset for depending on an unregistered handle.
                }
                $ver = @filemtime( $template_dir . $path ) ?: null;
                if ( 'styles' === $group ) {
                    wp_enqueue_style( $prefix . $key, $template_url . $path, $deps, $ver );
                } else {
                    wp_enqueue_script( $prefix . $key, $template_url . $path, $deps, $ver, true );
                }
            }
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

                $ver = @filemtime( $file->getPathname() ) ?: null;

                if ($type === 'style' && $extension === 'css') {
                    wp_enqueue_style($handle, $file_url, [], $ver);
                    //error_log("Assets_Manager: Enqueued CSS file $file_url with handle $handle");
                }

                if ($type === 'script' && $extension === 'js') {
                    wp_enqueue_script($handle, $file_url, ['jquery'], $ver, true);
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