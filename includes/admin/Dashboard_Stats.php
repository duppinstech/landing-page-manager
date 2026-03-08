<?php
namespace LPManager\admin;

class Dashboard_Stats {
    public static function register_rest_routes() {
        register_rest_route( 'lpmanager/v1', '/stats', [
            'methods'             => 'GET',
            'callback'            => [ __CLASS__, 'get_dashboard_stats' ],
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
        ] );
    }

    public static function get_dashboard_stats() {
        // Landing Page post counts by status
        $landing_page_counts = wp_count_posts( 'landing_page' );

        // Client term count
        $client_count = wp_count_terms( 'client', [ 'hide_empty' => false ] );

        // Custom: count by keyword taxonomy if you’ve added it
        $keyword_counts = [];
        $keywords = get_terms([
            'taxonomy'   => 'keyword',
            'hide_empty' => false,
        ]);

        foreach ( $keywords as $term ) {
            $keyword_counts[] = [
                'label' => $term->name,
                'count' => $term->count,
            ];
        }

        // Return JSON data
        return [
            'landing_pages' => $landing_page_counts,
            'clients'       => $client_count,
            'keywords'      => $keyword_counts,
        ];
    }

}