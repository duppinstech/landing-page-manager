<?php
namespace LPManager\ajax;

if ( ! defined('ABSPATH') ) exit;

/**
 * "Fetch Reviews" button on the landing page editor (assets/js/reviews-fetch.js).
 *
 * Admin-only by design: every call is a billed Google Places Details request on the
 * operator's key. Never register a wp_ajax_nopriv_ handler for this.
 */
final class Reviews_Ajax {

    /** Seconds to reuse a place's reviews before calling Google again. */
    const CACHE_TTL = 12 * HOUR_IN_SECONDS;

    public static function init() {
        add_action('wp_ajax_fetch_google_reviews', [self::class, 'fetch_google_reviews']);
    }

    public static function fetch_google_reviews() {
        check_ajax_referer('lpmanager_nonce', 'security');

        $current_post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
        if ( ! $current_post_id || 'landing_page' !== get_post_type( $current_post_id ) ) {
            wp_send_json_error('Missing post ID.');
        }
        if ( ! current_user_can( 'edit_post', $current_post_id ) ) {
            wp_send_json_error( 'You are not allowed to edit this landing page.', 403 );
        }

        $client_terms = get_the_terms($current_post_id, 'client');
        if (is_wp_error($client_terms) || empty($client_terms)) {
            wp_send_json_error('No client assigned to this page.');
        }

        $client_term = $client_terms[0];
        $client_term_id = $client_term->term_id;
        $place_id = (string) carbon_get_term_meta($client_term_id, 'client_place_id');
        $api_key = lpmanager_google_server_api_key();

        if ( '' === $place_id || '' === $api_key ) {
            wp_send_json_error('Missing API key or Place ID.');
        }

        $reviews = self::get_place_reviews( $place_id, $api_key );
        if ( null === $reviews ) {
            wp_send_json_error('Failed to contact Google Places API.');
        }
        if (empty($reviews)) {
            wp_send_json_error('No reviews found.');
        }

        // Filter reviews rating >= 4
        $filtered_reviews = array_filter($reviews, function($r) {
            return isset( $r['rating'] ) && $r['rating'] >= 4;
        });

        // Limit to 10 (API only returns 5 at a time currently)
        $filtered_reviews = array_slice($filtered_reviews, 0, 10);

        if (empty($filtered_reviews)) {
            wp_send_json_error('No positive reviews found.');
        }

        ob_start();
        ?>
        <div class="swiper-container reviews-swiper">
            <div class="swiper-wrapper">
                <?php foreach ($filtered_reviews as $review) : ?>
                    <div class="swiper-slide" style="box-sizing: border-box; padding: 10px;">
                        <p><?php echo esc_html( str_repeat('⭐', intval($review['rating'])) ); ?></p>
                        <p>"<?php echo esc_html( isset( $review['text'] ) ? $review['text'] : '' ); ?>"</p>
                        <p><strong>- <?php echo esc_html( isset( $review['author_name'] ) ? $review['author_name'] : '' ); ?></strong></p>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
        <?php
        $output = ob_get_clean();

        wp_send_json_success($output);
    }

    /**
     * A place's reviews from Places Details, cached per place.
     *
     * Requests only the `reviews` and `rating` fields, so Google bills the
     * Atmosphere SKU alone instead of every field. Returns an array of reviews
     * (possibly empty), or null if Google couldn't be reached.
     */
    private static function get_place_reviews( $place_id, $api_key ) {
        $cache_key = 'lpmanager_reviews_' . md5( $place_id );
        $cached = get_transient( $cache_key );
        if ( is_array( $cached ) ) {
            return $cached;
        }

        $url = add_query_arg(
            [
                'place_id' => rawurlencode( $place_id ),
                'fields'   => 'reviews,rating',
                'key'      => rawurlencode( $api_key ),
            ],
            'https://maps.googleapis.com/maps/api/place/details/json'
        );
        $response = wp_remote_get( $url, [ 'timeout' => 10 ] );
        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            return null;
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( ! is_array( $data ) || ! isset( $data['status'] ) || ! in_array( $data['status'], [ 'OK', 'ZERO_RESULTS' ], true ) ) {
            return null;
        }

        $reviews = ( isset( $data['result']['reviews'] ) && is_array( $data['result']['reviews'] ) ) ? $data['result']['reviews'] : [];
        set_transient( $cache_key, $reviews, self::CACHE_TTL );
        return $reviews;
    }
}
