<?php
namespace LPManager\core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Suggested privacy-policy text (Settings → Privacy → Policy Guide), so operators
 * can disclose what landing pages send to third parties and what is recorded.
 */
final class Privacy {

    /**
     * Register hooks.
     */
    public static function init() {
        add_action( 'admin_init', [ __CLASS__, 'add_policy_content' ] );
    }

    /**
     * Add the plugin's section to the privacy policy guide.
     */
    public static function add_policy_content() {
        if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
            return;
        }

        $paragraphs = [
            __( 'Landing pages on this site load fonts, icons and scripts from Google Fonts (Google LLC), cdnjs (Cloudflare, Inc.) and jsDelivr. When a page shows a map, it loads Google Maps. These providers receive your IP address and browser details when the page loads.', 'landing-page-manager' ),
            __( 'A landing page may also include tracking or call-tracking code added by the site operator for a specific client, such as advertising pixels or tag managers. Those services receive data under their own privacy policies.', 'landing-page-manager' ),
            __( 'When analytics are enabled, this site records page views and clicks on links and call buttons, and may record time on page, scroll depth, aggregate mouse positions, repeated clicks and form interactions. This data is stored on this website. [Operator: state how long you keep it.]', 'landing-page-manager' ),
        ];

        $content = '';
        foreach ( $paragraphs as $paragraph ) {
            $content .= '<p class="privacy-policy-tutorial">' . esc_html( $paragraph ) . '</p>';
        }

        /**
         * Filter the suggested privacy-policy text. The Pro add-on can append its retention details.
         *
         * @param string $content HTML paragraphs.
         */
        $content = apply_filters( 'lpmanager_privacy_policy_content', $content );

        wp_add_privacy_policy_content( 'Landing Page Manager', wp_kses_post( $content ) );
    }
}
