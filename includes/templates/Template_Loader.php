<?php

namespace LPManager\templates;

class Template_Loader {

    public static function init() {
        // Set template early, before CF fields registration
        add_action( 'template_redirect', [ __CLASS__, 'set_template_for_landing_page' ], 5 );

        // After theme options (priority 10) so carbon_get_theme_option( 'lpmanager_active_template' ) works on Add New.
        add_action( 'carbon_fields_register_fields', [ __CLASS__, 'load_template_fields' ], 20 );

        // Override the template include for landing pages
        add_filter( 'template_include', [ __CLASS__, 'load_template' ], 99 );
    }

    /**
     * Get effective template for a landing page.
     *
     * This project uses the global template setting as the single source of truth.
     *
     * @param int $post_id Landing page post ID.
     * @return string Template slug.
     */
    public static function get_effective_template_for_page( $post_id ) {
        return self::get_global_default_template_slug();
    }

    /**
     * Global default template from plugin settings (Carbon theme options).
     *
     * @return string Template slug.
     */
    public static function get_global_default_template_slug() {
        if ( function_exists( 'carbon_get_theme_option' ) ) {
            $global_template = carbon_get_theme_option( 'lpmanager_active_template' );
            if ( ! empty( $global_template ) ) {
                return sanitize_title( $global_template );
            }
        }
        return 'whiterail-ai';
    }

    /**
     * Post ID when editing in wp-admin (post screen).
     *
     * @return int
     */
    public static function get_admin_edit_post_id() {
        if ( ! is_admin() ) {
            return 0;
        }
        if ( isset( $_GET['post'] ) ) {
            $id = (int) $_GET['post'];
            if ( $id > 0 ) {
                return $id;
            }
        }
        if ( isset( $_POST['post_ID'] ) ) {
            $id = (int) $_POST['post_ID'];
            if ( $id > 0 ) {
                return $id;
            }
        }
        return 0;
    }

    /**
     * Which template's Carbon fields should register for the current admin request.
     *
     * @return string Template slug.
     */
    public static function get_template_slug_for_admin_fields() {
        $post_id = self::get_admin_edit_post_id();
        if ( $post_id > 0 && get_post_type( $post_id ) === 'landing_page' ) {
            $slug = self::get_effective_template_for_page( $post_id );
        } else {
            // Add New Landing Page: no post ID yet — fields match site default until first save.
            $slug = self::get_global_default_template_slug();
        }

        return (string) apply_filters( 'lpmanager_template_slug_for_admin_fields', $slug, $post_id );
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
        $template_slug = self::get_template_slug_for_admin_fields();
        if ( empty( $template_slug ) ) {
            $template_slug = 'whiterail-ai';
        }
        Template_Manager::set_current_template( $template_slug );

        $templates_root = plugin_dir_path( dirname( __FILE__ ) ) . 'templates/';
        $pattern        = $templates_root . '*/lib/fields/landing-page-fields.php';
        $candidates     = glob( $pattern ) ?: array();

        foreach ( $candidates as $fields_file ) {
            $dir_slug = basename( dirname( dirname( dirname( $fields_file ) ) ) );
            if ( sanitize_title( $dir_slug ) !== $template_slug ) {
                continue;
            }
            if ( file_exists( $fields_file ) ) {
                require_once $fields_file;
            }
            return;
        }

        // Fallback: direct path if glob failed (some hosts disable glob).
        $fields_file = $templates_root . $template_slug . '/lib/fields/landing-page-fields.php';
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
                $template_slug = 'whiterail-ai';
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
            $template_slug = 'whiterail-ai';
        }
        $template_dir = plugin_dir_path( dirname( __FILE__ ) ) . 'templates/' . $template_slug . '/';
        $template_404 = $template_dir . '404.php';
        return file_exists( $template_404 ) ? $template_404 : get_404_template();
    }
}
