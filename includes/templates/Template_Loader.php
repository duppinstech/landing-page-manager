<?php

namespace LPManager\templates;

class Template_Loader {

    public static function init() {
        // Set template early, before CF fields registration
        add_action( 'template_redirect', [ __CLASS__, 'set_template_for_landing_page' ], 5 );

        // Load template-specific fields during Carbon Fields registration
        add_action( 'carbon_fields_register_fields', [ __CLASS__, 'load_template_fields' ] );

        // Override the template include for landing pages
        add_filter( 'template_include', [ __CLASS__, 'load_template' ], 99 );
    }

    /**
     * Get effective template for a landing page: client template > page template > global default.
     *
     * @param int $post_id Landing page post ID.
     * @return string Template slug.
     */
    public static function get_effective_template_for_page( $post_id ) {
        $terms = get_the_terms( $post_id, 'client' );
        if ( $terms && ! is_wp_error( $terms ) && ! empty( $terms ) ) {
            $client_template = function_exists( 'carbon_get_term_meta' ) ? carbon_get_term_meta( (int) $terms[0]->term_id, 'lpmanager_client_template' ) : '';
            if ( ! empty( $client_template ) ) {
                return sanitize_title( $client_template );
            }
        }
        $page_template = function_exists( 'carbon_get_post_meta' ) ? carbon_get_post_meta( $post_id, 'lpmanager_active_template' ) : '';
        if ( ! empty( $page_template ) ) {
            return sanitize_title( $page_template );
        }
        $global_template = function_exists( 'carbon_get_theme_option' ) ? carbon_get_theme_option( 'lpmanager_active_template' ) : '';
        return ! empty( $global_template ) ? sanitize_title( $global_template ) : 'template-b';
    }

    /**
     * Set active template for landing pages
     */
    public static function set_template_for_landing_page() {
        if ( is_singular( 'landing_page' ) ) {
            global $post;
            if ( $post ) {
                $template_slug = self::get_effective_template_for_page( $post->ID );
                Template_Manager::set_current_template( $template_slug );
            }
        }
    }

    /**
     * Load template-specific Carbon Fields definitions
     */
    public static function load_template_fields() {
        // Use default if nothing is set yet (for admin screens)
        $template_slug = Template_Manager::get_current_template();
        if ( empty( $template_slug ) ) {
            $template_slug = 'template-b';
        }

        $fields_file = plugin_dir_path( dirname( __FILE__ ) ) . 'templates/' . $template_slug . '/lib/fields/landing-page-fields.php';
        if ( file_exists( $fields_file ) ) {
            require_once $fields_file;
        }
    }

    /**
     * Load template file for front-end display
     */
    public static function load_template( $template ) {
        if ( is_singular( 'landing_page' ) ) {
            $template_slug = Template_Manager::get_current_template();
            if ( empty( $template_slug ) ) {
                $template_slug = 'template-b';
            }

            $template_dir  = plugin_dir_path( dirname( __FILE__ ) ) . 'templates/' . $template_slug . '/';
            $template_file = $template_dir . 'single-landing_page.php';

            return file_exists( $template_file )
                ? $template_file
                : self::get_custom_404_template();
        }

        if ( get_query_var( 'force_lp_404' ) ) {
            return self::get_custom_404_template();
        }

        return $template;
    }

    private static function get_custom_404_template() {
        $template_slug = Template_Manager::get_current_template();
        if ( empty( $template_slug ) ) {
            $template_slug = 'template-b';
        }
        $template_dir = plugin_dir_path( dirname( __FILE__ ) ) . 'templates/' . $template_slug . '/';
        $template_404 = $template_dir . '404.php';
        return file_exists( $template_404 ) ? $template_404 : get_404_template();
    }
}
