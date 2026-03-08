<?php
namespace LPManager\core;

class Landing_Page_Map_Manager {

    public static function init() {
        add_action('save_post_landing_page', 'geocode_address_and_save', 20, 3);
    }
    function geocode_address_and_save($post_id) {
        $address = carbon_get_post_meta($post_id, 'client_address');
        $api_key = carbon_get_theme_option( 'lpmanager_google_maps_api_key' );

        if (!$address || !$api_key) {
            return;
        }

        $response = wp_remote_get("https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address) . "&key=" . $api_key);

        if (is_wp_error($response)) {
            return;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);

        if ($data->status === 'OK') {
            $location = $data->results[0]->geometry->location;
            update_post_meta($post_id, 'client_address_lat', $location->lat);
            update_post_meta($post_id, 'client_address_lng', $location->lng);
        }
    }
    
}