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

    /**
     * Seconds to refuse repeat fetches for a place after a successful one.
     *
     * The lock stores only a timestamp; the plugin keeps no copy of Google's response. (The
     * fetched HTML is inserted into the page's Reviews field by the editor; whether that may be
     * stored is an open attorney question in PLAN.md.) This replaced a 12-hour content cache
     * (Odell, 10-16).
     */
    const LOCK_TTL = 10 * MINUTE_IN_SECONDS;

    /** Seconds to refuse repeat calls after Google rejected or failed a request. */
    const FAILURE_TTL = 10 * MINUTE_IN_SECONDS;

    public static function init() {
        add_action('wp_ajax_fetch_google_reviews', [self::class, 'fetch_google_reviews']);
    }

    public static function fetch_google_reviews() {
        check_ajax_referer('lpmanager_nonce', 'security');

        $current_post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
        if ( ! $current_post_id || 'landing_page' !== get_post_type( $current_post_id ) ) {
            wp_send_json_error( __( 'Save the landing page first, then fetch reviews.', 'landing-page-manager' ) );
        }
        // No 403 status: reviews-fetch.js only shows the message for 2xx responses.
        if ( ! current_user_can( 'edit_post', $current_post_id ) ) {
            wp_send_json_error( __( 'You are not allowed to edit this landing page.', 'landing-page-manager' ) );
        }

        $client_terms = get_the_terms($current_post_id, 'client');
        if (is_wp_error($client_terms) || empty($client_terms)) {
            wp_send_json_error( __( 'No client is assigned to this page.', 'landing-page-manager' ) );
        }

        $client_term = $client_terms[0];
        $client_term_id = $client_term->term_id;
        $place_id = (string) carbon_get_term_meta($client_term_id, 'client_place_id');
        $api_key = lpmanager_google_server_api_key();

        if ( '' === $place_id || '' === $api_key ) {
            wp_send_json_error( __( 'Add a Google Maps server key under Landing Pages → Settings → API Keys, and make sure this page\'s client has a Google Place ID.', 'landing-page-manager' ) );
        }

        $reviews = self::get_place_reviews( $place_id, $api_key );
        if ( is_wp_error( $reviews ) ) {
            wp_send_json_error( $reviews->get_error_message() );
        }
        if (empty($reviews)) {
            wp_send_json_error( __( 'Google returned no reviews for this place.', 'landing-page-manager' ) );
        }

        // Filter reviews rating >= 4
        $filtered_reviews = array_filter($reviews, function($r) {
            return isset( $r['rating'] ) && $r['rating'] >= 4;
        });

        // Limit to 10 (API only returns 5 at a time currently)
        $filtered_reviews = array_slice($filtered_reviews, 0, 10);

        if (empty($filtered_reviews)) {
            wp_send_json_error( __( 'No 4- or 5-star reviews were found for this place.', 'landing-page-manager' ) );
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
     * A place's reviews from Places Details. Nothing from the response is cached.
     *
     * Requests only the `reviews` and `rating` fields, so Google bills Places Details
     * plus Atmosphere Data and skips Contact Data. Returns an array of reviews
     * (possibly empty), or a WP_Error with a message for the editor.
     */
    private static function get_place_reviews( $place_id, $api_key ) {
        $cache_key = 'lpmanager_reviews_' . md5( $place_id );
        // Remove review content cached by earlier versions.
        delete_transient( $cache_key );

        // Cost control without keeping content: after a fetch, refuse repeats for a short while.
        $lock_key  = 'lpmanager_reviews_lock_' . md5( $place_id );
        $locked_at = (int) get_transient( $lock_key );
        if ( $locked_at ) {
            $minutes = max( 1, (int) ceil( ( $locked_at + self::LOCK_TTL - time() ) / MINUTE_IN_SECONDS ) );
            return new \WP_Error(
                'lpmanager_reviews_locked',
                sprintf(
                    /* translators: %d: minutes until reviews can be fetched again */
                    _n( 'Reviews for this client were fetched a moment ago. Try again in %d minute.', 'Reviews for this client were fetched a moment ago. Try again in %d minutes.', $minutes, 'landing-page-manager' ),
                    $minutes
                )
            );
        }

        // After a failure, refuse repeats for a while so a bad key or Place ID can't
        // be hammered into billed requests.
        $failure_key = $cache_key . '_fail';
        $failure = get_transient( $failure_key );
        if ( is_string( $failure ) && '' !== $failure ) {
            return new \WP_Error( 'lpmanager_reviews_failed', $failure );
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
        $data = ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) )
            ? null
            : json_decode( wp_remote_retrieve_body( $response ), true );
        $status = ( is_array( $data ) && isset( $data['status'] ) ) ? (string) $data['status'] : '';

        if ( ! in_array( $status, [ 'OK', 'ZERO_RESULTS' ], true ) ) {
            if ( 'REQUEST_DENIED' === $status ) {
                $message = __( 'Google refused the request. Check the Google Maps server key under Landing Pages → Settings → API Keys: it needs the Places API enabled and must not be restricted to websites (HTTP referrers).', 'landing-page-manager' );
            } elseif ( in_array( $status, [ 'NOT_FOUND', 'INVALID_REQUEST' ], true ) ) {
                $message = __( 'Google did not recognise this client\'s Place ID. Check the Place ID on the client screen.', 'landing-page-manager' );
            } elseif ( 'OVER_QUERY_LIMIT' === $status ) {
                $message = __( 'Google\'s quota for this key has been reached. Try again later, or raise the quota in the Google Cloud console.', 'landing-page-manager' );
            } else {
                $message = __( 'Could not reach Google. Try again in a few minutes.', 'landing-page-manager' );
            }
            set_transient( $failure_key, $message, self::FAILURE_TTL );
            return new \WP_Error( 'lpmanager_reviews_failed', $message );
        }

        $reviews = ( isset( $data['result']['reviews'] ) && is_array( $data['result']['reviews'] ) ) ? $data['result']['reviews'] : [];
        set_transient( $lock_key, time(), self::LOCK_TTL );
        return $reviews;
    }
}
