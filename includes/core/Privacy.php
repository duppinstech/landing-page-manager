<?php
namespace LPManager\core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Suggested privacy-policy text (Settings → Privacy → Policy Guide), so operators
 * can disclose what landing pages send to third parties and what is recorded.
 *
 * Paragraphs with the `privacy-policy-tutorial` class are instructions for the
 * admin; WordPress leaves them out when the suggested text is copied. The
 * suggested text itself must be plain paragraphs.
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

        $tutorial = [
            __( 'Landing Page Manager suggests the text below. Edit it to match the templates and services your landing pages use, and have it reviewed by your own legal adviser.', 'landing-page-manager' ),
            __( 'If you use the Template C template, its default images load from client-c.example.', 'landing-page-manager' ),
        ];

        $suggested = [
            __( 'Landing pages on this site load fonts and icons from Google Fonts (Google LLC) and cdnjs (Cloudflare, Inc.). When a page shows a map, it loads Google Maps (Google LLC) as you scroll to it. Some page designs also load images or videos from other websites, such as YouTube. These providers receive your IP address and browser details when the page loads, and handle that data under their own privacy policies.', 'landing-page-manager' ),
            __( 'A landing page may also include tracking or call-tracking code added by the site operator for a specific client, such as advertising pixels or tag managers. Those services receive data under their own privacy policies.', 'landing-page-manager' ),
        ];

        // Core records nothing on its own; analytics come from the Pro add-on.
        if ( function_exists( 'lpmanager_is_premium' ) && lpmanager_is_premium()
            && function_exists( 'lpmanager_tracking_options' ) && in_array( true, lpmanager_tracking_options(), true ) ) {
            $tutorial[]  = __( 'State how long you keep landing page analytics data.', 'landing-page-manager' );
            $suggested[] = __( 'This site records landing page analytics, such as page views and clicks on links and call buttons, and depending on its settings time on page, scroll depth, aggregate mouse positions, repeated clicks and form interactions. This data is stored on this website.', 'landing-page-manager' );
        }

        $content = '';
        foreach ( $tutorial as $paragraph ) {
            $content .= '<p class="privacy-policy-tutorial">' . esc_html( $paragraph ) . '</p>';
        }
        $content .= '<strong class="privacy-policy-tutorial">' . esc_html__( 'Suggested text:', 'landing-page-manager' ) . ' </strong>';
        foreach ( $suggested as $paragraph ) {
            $content .= '<p>' . esc_html( $paragraph ) . '</p>';
        }

        /**
         * Filter the suggested privacy-policy text. The Pro add-on can add its own details,
         * such as what it stores per visitor and for how long.
         *
         * @param string $content HTML. Use class="privacy-policy-tutorial" for instructions only.
         */
        $content = apply_filters( 'lpmanager_privacy_policy_content', $content );

        wp_add_privacy_policy_content( 'Landing Page Manager', wp_kses_post( $content ) );
    }
}
