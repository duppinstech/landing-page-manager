<?php
namespace LPManager\cpt;

class Landing_Page_CPT_Enhancements {

    public static function init() {
        add_action( 'restrict_manage_posts', [ __CLASS__, 'add_client_filter_and_export_button' ] );
        add_filter( 'parse_query', [ __CLASS__, 'filter_by_client' ] );
        add_action( 'admin_post_export_landing_pages', [ __CLASS__, 'export_landing_pages_csv' ] );
        add_action( 'wp_dashboard_setup', [ __CLASS__, 'add_dashboard_widget' ] );
    }

    public static function add_client_filter_and_export_button() {
        global $typenow;
        if ( $typenow !== 'landing_page' ) {
            return;
        }

        // Client taxonomy filter dropdown
        $taxonomy = 'client';
        $selected = isset( $_GET[$taxonomy] ) ? sanitize_text_field( $_GET[$taxonomy] ) : '';
        wp_dropdown_categories( [
            'show_option_all' => 'All Clients',
            'taxonomy'        => $taxonomy,
            'name'            => $taxonomy,
            'orderby'         => 'name',
            'selected'        => $selected,
            'hierarchical'    => true,
            'depth'           => 1,
            'show_count'      => true,
            'hide_empty'      => false,
        ] );
        
        // Keywords taxonomy filter dropdown
        $taxonomy = 'keyword';
        $selected = isset( $_GET[$taxonomy] ) ? sanitize_text_field( $_GET[$taxonomy] ) : '';
        wp_dropdown_categories( [
            'show_option_all' => 'All Keywords',
            'taxonomy'        => $taxonomy,
            'name'            => $taxonomy,
            'orderby'         => 'name',
            'selected'        => $selected,
            'hierarchical'    => true,
            'depth'           => 1,
            'show_count'      => true,
            'hide_empty'      => false,
        ] );        

        // Export button
        $export_url = admin_url( 'admin-post.php?action=export_landing_pages' );
        echo '<a href="' . esc_url( $export_url ) . '" class="button" id="lp-export-csv-button" style="margin-left: 10px;">Export CSV</a>';
    }

    public static function filter_by_client( $query ) {
        global $pagenow;
        $post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : '';

        if ( $pagenow === 'edit.php' && $post_type === 'landing_page' && ! empty( $_GET['client'] ) ) {
            $query->query_vars['tax_query'] = [
                [
                    'taxonomy' => 'client',
                    'field'    => 'id',
                    'terms'    => sanitize_text_field( $_GET['client'] )
                ]
            ];
        }
    }

    public static function export_landing_pages_csv() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized request' );
        }

        header( 'Content-Type: text/csv' );
        header( 'Content-Disposition: attachment;filename=landing-pages-export.csv' );

        $output = fopen( 'php://output', 'w' );
        fputcsv( $output, [ 'Title', 'Landing Page URL', 'Client', 'Keywords', 'Publish Date' ] );

        // Match filters in current view (e.g., client or keyword filters)
        $args = [
            'post_type'      => 'landing_page',
            'posts_per_page' => -1,
        ];

        // Optional: Apply client filter if present
        if ( ! empty( $_GET['client'] ) ) {
            $args['tax_query'][] = [
                'taxonomy' => 'client',
                'field'    => 'id',
                'terms'    => sanitize_text_field( $_GET['client'] ),
            ];
        }

        // Optional: Apply keyword filter if present
        if ( ! empty( $_GET['keyword'] ) ) {
            $args['tax_query'][] = [
                'taxonomy' => 'keyword',
                'field'    => 'id',
                'terms'    => sanitize_text_field( $_GET['keyword'] ),
            ];
        }

        $posts = get_posts( $args );

        foreach ( $posts as $post ) {
            $url = get_permalink( $post->ID );
            $client_terms = get_the_terms( $post->ID, 'client' );
            $keyword_terms = get_the_terms( $post->ID, 'keyword' );

            $client = ( $client_terms && ! is_wp_error( $client_terms ) ) ? $client_terms[0]->name : 'None';
            $keywords = ( $keyword_terms && ! is_wp_error( $keyword_terms ) )
                ? implode( ', ', wp_list_pluck( $keyword_terms, 'name' ) )
                : 'None';

            fputcsv( $output, [
                $post->post_title,
                $url,
                $client,
                $keywords,
                get_the_date( 'Y-m-d', $post ),
            ]);
        }

        fclose( $output );
        exit;
    }

    public static function add_dashboard_widget() {
        wp_add_dashboard_widget( 'lpmanager_dashboard_widget', 'Landing Page Manager Stats', [ __CLASS__, 'render_dashboard_widget' ] );
    }

    public static function render_dashboard_widget() {
        $landing_pages = wp_count_posts( 'landing_page' )->publish;
        $clients       = wp_count_terms( 'client' );

        echo '<ul>';
        echo '<li><strong>Total Landing Pages:</strong> ' . esc_html( $landing_pages ) . '</li>';
        echo '<li><strong>Total Clients:</strong> ' . esc_html( $clients ) . '</li>';
        echo '</ul>';
    }
    
}

Landing_Page_CPT_Enhancements::init();
