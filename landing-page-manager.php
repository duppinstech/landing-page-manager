<?php
/**
 * Plugin Name: Landing Page Manager
 * Plugin URI: https://github.com/oduppinsjr/landing-page-manager
 * Description: Manage multi-client landing pages with subdomain routing.
 * Version: 1.0.2
 * Author: Odell Duppins
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: landing-page-manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'LP_MANAGER_PLUGIN_URL' ) ) {
    define( 'LP_MANAGER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'LP_MANAGER_PLUGIN_PATH' ) ) {
    define( 'LP_MANAGER_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

/**
 * Whether the Pro add-on is active. Uses a function only the real Pro plugin defines—
 * defining a constant in a snippet does not unlock Pro code, which lives in the Pro plugin.
 *
 * @return bool
 */
function lpmanager_is_premium() {
    return function_exists( 'lpmanager_pro_loaded' ) && lpmanager_pro_loaded();
}

/**
 * Which conversion-tracking options are enabled (Pro + Settings). Used to gate tracking and UI.
 *
 * @return array{ track_page_views: bool, track_link_clicks: bool, enable_conversion_tracking: bool, track_time_on_page: bool, track_scroll_depth: bool, track_heatmap: bool, track_rage_clicks: bool, track_outbound_clicks: bool, track_cta_visibility: bool, track_form_interactions: bool }
 */
function lpmanager_tracking_options() {
    $defaults = [
        'track_page_views'           => false,
        'track_link_clicks'          => false,
        'enable_conversion_tracking' => false,
        'track_time_on_page'         => false,
        'track_scroll_depth'         => false,
        'track_heatmap'              => false,
        'track_rage_clicks'          => false,
        'track_outbound_clicks'      => false,
        'track_cta_visibility'       => false,
        'track_form_interactions'    => false,
    ];
    if ( ! function_exists( 'lpmanager_is_premium' ) || ! lpmanager_is_premium() ) {
        return $defaults;
    }
    if ( ! function_exists( 'carbon_get_theme_option' ) ) {
        return $defaults;
    }
    return [
        'track_page_views'           => (bool) carbon_get_theme_option( 'lpmanager_track_page_views' ),
        'track_link_clicks'          => (bool) carbon_get_theme_option( 'lpmanager_track_link_clicks' ),
        'enable_conversion_tracking' => true, // Always on when Pro; individual options control what is tracked.
        'track_time_on_page'         => (bool) carbon_get_theme_option( 'lpmanager_track_time_on_page' ),
        'track_scroll_depth'         => (bool) carbon_get_theme_option( 'lpmanager_track_scroll_depth' ),
        'track_heatmap'              => (bool) carbon_get_theme_option( 'lpmanager_track_heatmap' ),
        'track_rage_clicks'          => (bool) carbon_get_theme_option( 'lpmanager_track_rage_clicks' ),
        'track_outbound_clicks'      => (bool) carbon_get_theme_option( 'lpmanager_track_outbound_clicks' ),
        'track_cta_visibility'       => (bool) carbon_get_theme_option( 'lpmanager_track_cta_visibility' ),
        'track_form_interactions'    => (bool) carbon_get_theme_option( 'lpmanager_track_form_interactions' ),
    ];
}

// Load composer autoloader first (needed for LPManager namespace classes)
if ( ! file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    add_action( 'admin_notices', function () {
        echo '<div class="notice notice-error"><p>' . esc_html__( 'Landing Page Manager: Composer autoload missing. Run composer install.', 'landing-page-manager' ) . '</p></div>';
    } );
    return;
}
require_once __DIR__ . '/vendor/autoload.php';

add_action( 'after_setup_theme', function() {
    \Carbon_Fields\Carbon_Fields::boot();
});

use LPManager\Plugin;

// Initialize plugin
add_action( 'plugins_loaded', function() {
    Plugin::init();
});

// Register activation hook (must be outside class)
register_activation_hook( __FILE__, [ Plugin::class, 'activate' ] );

// Start output buffering early for REST API requests (avoids stray output in JSON)
add_action( 'rest_api_init', function () {
    ob_start();
}, 0 );

add_action( 'rest_api_init', function () {
    register_rest_route( 'lpmanager/v1', '/dashboard-data', [
        'methods'             => 'GET',
        'callback'            => [ LPManager\admin\Admin_Dashboard::class, 'get_dashboard_data' ],
        'permission_callback' => function () {
            return current_user_can( 'manage_options' );
        },
    ] );
    register_rest_route( 'lpmanager/v1', '/get-brand-colors', [
        'methods'             => 'POST',
        'callback'            => [ 'LPManager\taxonomy\Client_Taxonomy', 'handle_brandfetch_request' ],
        'permission_callback' => function () {
            return current_user_can( 'manage_options' );
        },
    ] );
    // Analytics route is registered by the Pro plugin only.
}, 10 );

add_filter( 'rest_post_dispatch', function ( $response, $server, $request ) {
    if ( ob_get_length() ) {
        ob_end_clean();
    }
    return $response;
}, 100, 3 );

if ( ! function_exists( 'wp_doing_rest' ) ) {
    function wp_doing_rest() {
        // This is how WP core defines it
        if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
            return true;
        }
        return false;
    }
}

add_action( 'template_redirect', function () {
    if ( is_singular( 'landing_page' ) &&
        ! is_admin() &&
        ! wp_doing_ajax() &&
        ! wp_doing_rest()
    ) {
            // Remove only known unwanted plugin scripts/styles
            add_action('wp_enqueue_scripts', function() {
                $unwanted_styles = apply_filters('lpmanager_unwanted_styles', [
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
                    // Add more as needed
                ]);
                foreach ($unwanted_styles as $style) {
                    wp_dequeue_style($style);
                }
    
                $unwanted_scripts = apply_filters('lpmanager_unwanted_scripts', [
                    'elementor-common',
                    'elementor-pro-notes',
                    'elementor-pro-notes-app-initiator',
                    'elementor-pro-app',
                    'elementor-app-loader',
                    'elementor-frontend',
                    // Add more as needed
                ]);
                foreach ($unwanted_scripts as $script) {
                    wp_dequeue_script($script);
                }
            }, 100);
    
            // Remove only specific actions
            remove_action('wp_head', 'googlesitekit_print_head_scripts', 20);
            remove_action( 'wp_footer', 'googlesitekit_print_footer_scripts', 20 );
        }
} );

// Custom action for your own tracking/scripts on landing pages
function lpmanager_custom_landing_page_footer() {
    /**
     * Place your AdWords or other tracking scripts here.
     */
    // You can also use a filter here for extensibility
    do_action('lpmanager_landing_page_footer');
}

add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( 'edit-tags.php' === $hook && isset( $_GET['taxonomy'] ) && 'client' === $_GET['taxonomy'] ) {
        wp_enqueue_script( 'client-brandfetch', plugin_dir_url( __FILE__ ) . 'assets/js/client-brandfetch.js', [ 'jquery' ], '1.0', true );
        wp_localize_script( 'client-brandfetch', 'lpmanager_ajax', [
            'rest_url' => rest_url( 'lpmanager/v1/get-brand-colors' ),
            'nonce'    => wp_create_nonce( 'wp_rest' ),
        ] );
    }
} );
