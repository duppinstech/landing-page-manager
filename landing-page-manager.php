<?php
/**
 * Plugin Name: Landing Page Manager
 * Plugin URI: https://github.com/duppinstech/landing-page-manager
 * Description: Launch fast, client-branded landing pages on subdomains for every service, keyword and city.
 * Version: 1.0.11
 * Requires at least: 5.9
 * Requires PHP: 7.4
 * Author: Duppins Technology
 * Author URI: https://duppinstech.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: landing-page-manager
 * Update URI: https://github.com/duppinstech/landing-page-manager
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
 * Google API key for server-side calls (Places, Geocoding).
 *
 * The browser key (`lpmanager_google_maps_api_key`) is printed in page HTML for the
 * Maps JavaScript API, so it should be restricted to the site's URLs, and a key
 * restricted that way can't be used from the server. Sites that haven't set a
 * separate server key fall back to the browser key, as before.
 *
 * @return string
 */
function lpmanager_google_server_api_key() {
    if ( ! function_exists( 'carbon_get_theme_option' ) ) {
        return '';
    }
    $key = trim( (string) carbon_get_theme_option( 'lpmanager_google_server_api_key' ) );
    if ( '' === $key ) {
        $key = trim( (string) carbon_get_theme_option( 'lpmanager_google_maps_api_key' ) );
    }
    return $key;
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
    // Pro 1.0.4+ owns the tracking toggles (same wp_options rows, its own Settings section).
    if ( function_exists( 'lpmanager_pro_tracking_options' ) ) {
        $pro = (array) lpmanager_pro_tracking_options();
        foreach ( $defaults as $key => $unused ) {
            $defaults[ $key ] = ! empty( $pro[ $key ] );
        }
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

/**
 * The phone number a landing page shows and dials.
 *
 * The page's own "Page phone" (meta key client_tel; e.g. a call-tracking number for that page) wins; otherwise the
 * client's default "Contact Phone" is used. Every template uses this for both display and tel: links.
 *
 * @param int $post_id Landing page ID. Defaults to the current post.
 * @return string Phone number as entered (may be empty).
 */
function lpmanager_get_landing_phone( $post_id = 0 ) {
    $post_id = $post_id ? (int) $post_id : (int) get_the_ID();
    if ( ! $post_id || ! function_exists( 'carbon_get_post_meta' ) ) {
        return '';
    }
    $page_phone = trim( (string) carbon_get_post_meta( $post_id, 'client_tel' ) );
    if ( '' !== $page_phone ) {
        return $page_phone;
    }
    $terms = get_the_terms( $post_id, 'client' );
    if ( empty( $terms ) || is_wp_error( $terms ) ) {
        return '';
    }
    return trim( (string) carbon_get_term_meta( $terms[0]->term_id, 'client_phone' ) );
}

/**
 * Filter rich-text template content like post content, but keep embedded video and map iframes
 * from approved providers (Odell, 10-16).
 *
 * Iframes are never passed through as written. Each one is read with WordPress's HTML parser
 * (which takes the first `src`, decoded, as browsers do), its src is checked against strict
 * per-provider URL patterns, and approved iframes are rebuilt from scratch with a fixed set of
 * clean attributes. Everything else goes through wp_kses_post(), which removes iframes.
 * Without the HTML API (WordPress before 6.2), all iframes are removed.
 *
 * @param string $html Field content.
 * @return string Safe HTML.
 */
function lpmanager_kses_rich_text( $html ) {
    $html = (string) $html;
    // Post rules without iframes, whatever other plugins add to them.
    $no_iframes = wp_kses_allowed_html( 'post' );
    unset( $no_iframes['iframe'] );
    if ( false === stripos( $html, 'iframe' ) || ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
        return wp_kses( $html, $no_iframes );
    }

    /**
     * Filters the URL patterns an iframe src must fully match to be kept in rich-text fields.
     *
     * @param string[] $patterns PCRE patterns. Each must start with `#^https://` and end with `$#`,
     *                           or it is ignored.
     */
    $patterns = (array) apply_filters(
        'lpmanager_iframe_src_patterns',
        [
            'youtube'     => '#^https://www\.youtube(?:-nocookie)?\.com/embed/[A-Za-z0-9_-]+(?:\?[A-Za-z0-9_=&%.-]*)?$#',
            'vimeo'       => '#^https://player\.vimeo\.com/video/[0-9]+(?:\?[A-Za-z0-9_=&%.-]*)?$#',
            'google_maps' => '#^https://www\.google\.com/maps/embed(?:/v1/[a-z]+)?\?[A-Za-z0-9_=&%.!:,+-]*$#',
        ]
    );
    $allow_tokens = [ 'accelerometer', 'autoplay', 'clipboard-write', 'encrypted-media', 'gyroscope', 'picture-in-picture', 'web-share', 'fullscreen' ];

    // 1. Read every iframe; mark the approved ones with an index into $embeds.
    $embeds    = [];
    $processor = new WP_HTML_Tag_Processor( $html );
    while ( $processor->next_tag( [ 'tag_name' => 'IFRAME' ] ) ) {
        $src = $processor->get_attribute( 'src' );
        $src = is_string( $src ) ? $src : '';
        $ok  = '' !== $src && ! preg_match( '#[\x00-\x20\\\\@]#', $src );
        $provider = '';
        if ( $ok ) {
            $ok = false;
            foreach ( $patterns as $key => $pattern ) {
                // Only anchored https patterns count, so a loose pattern from a filter can't widen the check.
                if ( is_string( $pattern ) && 0 === strpos( $pattern, '#^https://' ) && '$#' === substr( $pattern, -2 ) && preg_match( $pattern, $src ) ) {
                    $ok       = true;
                    $provider = (string) $key;
                    break;
                }
            }
        }
        if ( ! $ok ) {
            $processor->remove_attribute( 'data-lpm-embed' ); // No smuggled markers.
            continue;
        }
        $allow = [];
        $raw   = $processor->get_attribute( 'allow' );
        foreach ( is_string( $raw ) ? explode( ';', $raw ) : [] as $token ) {
            $token = strtolower( trim( $token ) );
            if ( in_array( $token, $allow_tokens, true ) ) {
                $allow[] = $token;
            }
        }
        // Sizes: plain pixels (capped) or a percentage such as width="100%"; anything else is dropped.
        $size = function ( $value ) {
            if ( ! is_string( $value ) || ! preg_match( '#^\s*([0-9]{1,4})\s*(%?)\s*$#', $value, $m ) ) {
                return '';
            }
            if ( 0 === (int) $m[1] ) {
                return ''; // A zero size would hide the embed.
            }
            return '%' === $m[2] ? min( 100, (int) $m[1] ) . '%' : (string) min( 2000, (int) $m[1] );
        };
        $title = $processor->get_attribute( 'title' );
        $title = is_string( $title ) ? sanitize_text_field( $title ) : '';
        if ( '' === $title ) {
            // Every iframe needs an accessible name; most pasted embed code has none.
            $fallback = [
                'youtube'     => __( 'YouTube video', 'landing-page-manager' ),
                'vimeo'       => __( 'Vimeo video', 'landing-page-manager' ),
                'google_maps' => __( 'Google map', 'landing-page-manager' ),
            ];
            $title = isset( $fallback[ $provider ] ) ? $fallback[ $provider ] : __( 'Embedded content', 'landing-page-manager' );
        }
        $embeds[] = [
            'src'             => $src,
            'width'           => $size( $processor->get_attribute( 'width' ) ),
            'height'          => $size( $processor->get_attribute( 'height' ) ),
            'title'           => $title,
            'allow'           => array_unique( $allow ),
            'allowfullscreen' => null !== $processor->get_attribute( 'allowfullscreen' ),
        ];
        $processor->set_attribute( 'data-lpm-embed', (string) ( count( $embeds ) - 1 ) );
    }
    $html = $processor->get_updated_html();

    // 2. Standard post filtering; iframes survive only as bare markers.
    $allowed           = $no_iframes;
    $allowed['iframe'] = [ 'data-lpm-embed' => true ];
    $html              = wp_kses( $html, $allowed );

    // 3. Drop original closing tags (rebuilt iframes get their own, so an unclosed iframe can't
    //    swallow the rest of the page), then rebuild approved iframes and drop every other one.
    //    After kses an iframe tag can only carry the marker, so these patterns see exactly that.
    $html = preg_replace( '#</iframe\s*>#i', '', (string) $html );
    $html = preg_replace_callback(
        '#<iframe\b[^>]*>#i',
        function ( $m ) use ( $embeds ) {
            if ( ! preg_match( '#data-lpm-embed=["\']?([0-9]+)#i', $m[0], $idx ) || ! isset( $embeds[ (int) $idx[1] ] ) ) {
                return '';
            }
            $e   = $embeds[ (int) $idx[1] ];
            $src = esc_url( $e['src'], [ 'https' ] );
            if ( '' === $src ) {
                return '';
            }
            // Responsive: never wider than its container; pixel sizes keep their aspect ratio.
            $style = 'max-width:100%;';
            // preg_match, not ctype_digit: the ctype extension can be missing on some hosts.
            if ( preg_match( '/^[0-9]+$/', $e['width'] ) && preg_match( '/^[0-9]+$/', $e['height'] ) ) {
                $style .= 'height:auto;aspect-ratio:' . (int) $e['width'] . '/' . (int) $e['height'] . ';';
            }
            $out = '<iframe class="lpm-embed" src="' . $src . '"';
            $out .= '' !== $e['width'] ? ' width="' . esc_attr( $e['width'] ) . '"' : '';
            $out .= '' !== $e['height'] ? ' height="' . esc_attr( $e['height'] ) . '"' : '';
            $out .= ' style="' . esc_attr( $style ) . '"';
            $out .= ' title="' . esc_attr( $e['title'] ) . '"';
            $out .= $e['allow'] ? ' allow="' . esc_attr( implode( '; ', $e['allow'] ) ) . '"' : '';
            $out .= $e['allowfullscreen'] ? ' allowfullscreen' : '';
            return $out . ' loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>';
        },
        (string) $html
    );
    return (string) $html;
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

                // Per-post Elementor CSS handles (elementor-post-{ID}) differ on every site, so match by pattern.
                $unwanted_style_patterns = apply_filters('lpmanager_unwanted_style_patterns', [
                    '/^elementor-post-\d+$/',
                ]);
                foreach (wp_styles()->queue as $handle) {
                    foreach ($unwanted_style_patterns as $pattern) {
                        if (preg_match($pattern, $handle)) {
                            wp_dequeue_style($handle);
                            break;
                        }
                    }
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

/**
 * Landing-page footer hook, attached to wp_footer on landing pages (see Assets_Manager).
 * Settings → API Keys → Additional Scripts prints here; add your own output with
 * add_action( 'lpmanager_landing_page_footer', ... ).
 */
function lpmanager_custom_landing_page_footer() {
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
