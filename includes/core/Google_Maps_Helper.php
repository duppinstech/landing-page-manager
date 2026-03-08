<?php
namespace LPManager\core;

if (!defined('ABSPATH')) exit;

class Google_Maps_Helper {

    public static function get_gmb_url($place_id) {
        if (empty($place_id)) {
            return '';
        }
        return 'https://www.google.com/maps/place/?q=place_id:' . urlencode($place_id);
    }

    public static function get_gmb_reviews_url($place_id) {
        return 'https://search.google.com/local/reviews?placeid=' . urlencode($place_id);
    }
}