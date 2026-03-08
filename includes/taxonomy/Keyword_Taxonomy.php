<?php
namespace LPManager\taxonomy;

class Keyword_Taxonomy {

    public static function init() {
        add_action( 'init', [ __CLASS__, 'register_taxonomy' ], 0 );
    }

    public static function register_taxonomy() {
        $labels = [
            'name'                       => __( 'Keywords', 'landing-page-manager' ),
            'singular_name'              => __( 'Keyword', 'landing-page-manager' ),
            'search_items'               => __( 'Search Keywords', 'landing-page-manager' ),
            'popular_items'              => __( 'Popular Keywords', 'landing-page-manager' ),
            'all_items'                  => __( 'All Keywords', 'landing-page-manager' ),
            'parent_item'                => __( 'Parent Keyword', 'landing-page-manager' ),
            'parent_item_colon'          => __( 'Parent Keyword:', 'landing-page-manager' ),
            'edit_item'                  => __( 'Edit Keyword', 'landing-page-manager' ),
            'view_item'                  => __( 'View Keyword', 'landing-page-manager' ),
            'update_item'                => __( 'Update Keyword', 'landing-page-manager' ),
            'add_new_item'               => __( 'Add New Keyword', 'landing-page-manager' ),
            'new_item_name'              => __( 'New Keyword Name', 'landing-page-manager' ),
            'separate_items_with_commas' => __( 'Separate keywords with commas', 'landing-page-manager' ),
            'add_or_remove_items'        => __( 'Add or remove keywords', 'landing-page-manager' ),
            'choose_from_most_used'      => __( 'Choose from the most used keywords', 'landing-page-manager' ),
            'not_found'                  => __( 'No keywords found.', 'landing-page-manager' ),
            'no_terms'                   => __( 'No keywords', 'landing-page-manager' ),
            'menu_name'                  => __( 'Keywords', 'landing-page-manager' ),
            'items_list_navigation'      => __( 'Keywords list navigation', 'landing-page-manager' ),
            'items_list'                 => __( 'Keywords list', 'landing-page-manager' ),
            'most_used'                  => __( 'Most Used', 'landing-page-manager' ),
            'back_to_items'              => __( '← Back to Keywords', 'landing-page-manager' ),
        ];

        $args = [
            'hierarchical'          => false, // like tags
            'labels'                => $labels,
            'show_ui'               => true,
            'show_admin_column'     => true,
            'public'                => true,
            'show_in_rest'          => true, // Important if using block editor / REST API
            'query_var'             => true,
            'rewrite'               => [ 'slug' => 'keyword' ],
        ];

        register_taxonomy( 'keyword', [ 'landing_page' ], $args );
    }
}
