<?php
namespace LPManager\ajax;

if ( ! defined('ABSPATH') ) exit;

final class Reviews_Ajax {

    public static function init() {
        add_action('wp_ajax_fetch_google_reviews', [self::class, 'fetch_google_reviews']);
    }

    public static function fetch_google_reviews() {
        check_ajax_referer('lpmanager_nonce', 'security');

        $current_post_id = intval($_POST['post_id'] ?? 0);
        if (!$current_post_id) {
            wp_send_json_error('Missing post ID.');
        }

        $client_terms = get_the_terms($current_post_id, 'client');
        if (is_wp_error($client_terms) || empty($client_terms)) {
            wp_send_json_error('No client assigned to this page.');
        }

        $client_term = $client_terms[0];
        $client_term_id = $client_term->term_id;
        $place_id = carbon_get_term_meta($client_term_id, 'client_place_id');
        $api_key = carbon_get_theme_option('lpmanager_google_maps_api_key');

        if (!$place_id || !$api_key) {
            wp_send_json_error('Missing API key or Place ID.');
        }

        $url = "https://maps.googleapis.com/maps/api/place/details/json?place_id=$place_id&key=$api_key";
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            wp_send_json_error('Failed to contact Google Places API.');
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data['result']['reviews'])) {
            wp_send_json_error('No reviews found.');
        }

        // Filter reviews rating >= 4
        $filtered_reviews = array_filter($data['result']['reviews'], function($r) {
            return $r['rating'] >= 4;
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
                        <p><?php echo str_repeat('⭐', intval($review['rating'])); ?></p>
                        <p>"<?php echo esc_html($review['text']); ?>"</p>
                        <p><strong>- <?php echo esc_html($review['author_name']); ?></strong></p>
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
}
