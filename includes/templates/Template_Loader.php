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
     * Core uses the global template setting. Choosing a template per client or page is a
     * Pro feature, through the lpmanager_template_for_post filter (Odell, 10-16).
     *
     * @param int $post_id Landing page post ID.
     * @return string Template slug.
     */
    public static function get_effective_template_for_page( $post_id ) {
        $global = self::get_global_default_template_slug();
        /**
         * Filters the template slug used for a landing page.
         *
         * @param string $slug    Template slug. Default: the site-wide template from Settings → Templates.
         * @param int    $post_id Landing page ID.
         */
        $slug = sanitize_title( (string) apply_filters( 'lpmanager_template_for_post', $global, (int) $post_id ) );
        // Ignore a filtered slug whose template isn't installed, rather than 404ing the page.
        if ( '' === $slug || ! Template_Manager::template_exists( $slug ) ) {
            return $global;
        }
        return $slug;
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
        return Template_Manager::DEFAULT_TEMPLATE;
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
            $template_slug = Template_Manager::DEFAULT_TEMPLATE;
        }
        Template_Manager::set_current_template( $template_slug );

        $template_dir = Template_Manager::get_template_dir( $template_slug );
        if ( '' !== $template_dir && file_exists( $template_dir . 'lib/fields/landing-page-fields.php' ) ) {
            require_once $template_dir . 'lib/fields/landing-page-fields.php';
        }
    }

    /**
     * Load template file for front-end display
     */
    public static function load_template( $template ) {
        if ( is_singular( 'landing_page' ) ) {
            $template_slug = Template_Manager::get_current_template();
            $template_dir = Template_Manager::get_template_dir( $template_slug );

            return '' !== $template_dir
                ? $template_dir . 'single-landing_page.php'
                : self::get_custom_404_template();
        }

        if ( get_query_var( 'force_lp_404' ) ) {
            return self::get_custom_404_template();
        }

        return $template;
    }

    private static function get_custom_404_template() {
        $template_dir = Template_Manager::get_template_dir();
        return '' !== $template_dir && file_exists( $template_dir . '404.php' ) ? $template_dir . '404.php' : get_404_template();
    }
}
