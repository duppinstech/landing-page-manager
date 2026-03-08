<?php
namespace LPManager\cpt;

class Rewrite_Rules {

    public static function init() {
        add_filter( 'query_vars', [ __CLASS__, 'add_query_vars' ] );
        add_action( 'init', [ __CLASS__, 'add_rewrite_rules' ] );
        add_action( 'parse_request', [ __CLASS__, 'parse_subdomain' ] );
        add_action( 'pre_get_posts', [ __CLASS__, 'handle_custom_landing_page_query' ] );
        add_filter( 'post_type_link', [ __CLASS__, 'custom_landing_page_link' ], 10, 2 );
        add_filter( 'preview_post_link', [ __CLASS__, 'custom_preview_link' ], 10, 2 );
    }

    public static function add_rewrite_rules() {
        $post_type = 'landing_page';
        $slug = get_post_type_object( $post_type )->rewrite['slug'] ?? 'landing';

        add_rewrite_rule(
            "^{$slug}/([^/]+)/?$",
            "index.php?post_type={$post_type}&name=\$matches[1]",
            'top'
        );
    }

    public static function add_query_vars( $vars ) {
        $vars[] = 'lp_client';
        $vars[] = 'force_lp_404';
        return $vars;
    }

    public static function parse_subdomain( $wp ) {
        if ( is_admin() ) return;

        $host        = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
        $main_domain = parse_url( home_url(), PHP_URL_HOST );

        if ( empty( $host ) || empty( $main_domain ) ) return;

        if ( substr( $host, -strlen( $main_domain ) ) === $main_domain ) {
            $subdomain   = trim( str_replace( '.' . $main_domain, '', $host ) );
            $request_uri = trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );

            // If no slug is present, don't process
            if ( empty( $request_uri ) ) return;

            // Check if slug is a valid landing_page post before proceeding
            $post = get_page_by_path( $request_uri, OBJECT, 'landing_page' );
            if ( ! $post ) return;

            $wp->query_vars['lp_client'] = sanitize_text_field( $subdomain );
            $wp->query_vars['name']      = sanitize_title( $request_uri );
            $wp->query_vars['post_type'] = 'landing_page';
        }
    }


    public static function handle_custom_landing_page_query( $query ) {
        if ( is_admin() || ! $query->is_main_query() ) return;

        if ( get_query_var( 'lp_client' ) && get_query_var( 'name' ) ) {
            $query->set( 'post_type', 'landing_page' );
            $query->set( 'name', get_query_var( 'name' ) );
            $query->set( 'posts_per_page', 1 );
            $query->set( 'post_status', 'publish' );
            $query->is_singular = true;
        }
    }

    public static function custom_landing_page_link( $post_link, $post ) {
        if ( $post->post_type !== 'landing_page' ) {
            return $post_link;
        }

        $client_terms = get_the_terms( $post->ID, 'client' );
        if ( ! $client_terms || is_wp_error( $client_terms ) ) {
            return $post_link;
        }

        $client    = $client_terms[0];
        $subdomain = $client->slug;

        if ( ! $subdomain ) {
            return $post_link;
        }

        $main_domain = parse_url( home_url(), PHP_URL_HOST );
        $post_slug   = $post->post_name;
        $new_link    = 'https://' . $subdomain . '.' . $main_domain . '/' . $post_slug . '/';

        return $new_link;
    }

    public static function custom_preview_link( $preview_link, $post ) {
        if ( $post->post_type !== 'landing_page' ) {
            return $preview_link;
        }

        $client_terms = get_the_terms( $post->ID, 'client' );
        if ( ! $client_terms || is_wp_error( $client_terms ) ) {
            return $preview_link;
        }

        $client    = $client_terms[0];
        $subdomain = $client->slug;

        if ( ! $subdomain ) {
            return $preview_link;
        }

        $main_domain = parse_url( home_url(), PHP_URL_HOST );
        $post_slug   = $post->post_name;
        $new_link    = 'https://' . $subdomain . '.' . $main_domain . '/' . $post_slug . '/?preview=true';

        return $new_link;
    }

    public static function filter_landing_pages_query( $query ) {
        if ( is_admin() || ! $query->is_main_query() ) return;

        $client = get_query_var( 'lp_client' );
        if ( $client && $query->get( 'post_type' ) === 'landing_page' ) {
            $query->set( 'tax_query', [
                [
                    'taxonomy' => 'client',
                    'field'    => 'slug',
                    'terms'    => $client,
                ]
            ] );
            $query->set( 'post_status', 'publish' );
        }
    }
}
