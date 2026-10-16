<?php
namespace LPManager\core;

use LPManager\core\Required_Fields_Registry;
use LPManager\templates\Template_Loader;

class Landing_Page_Validator {

    public static function init() {
        //error_log('Landing_Page_Validator::init fired');

        add_action('save_post', [__CLASS__, 'validateLandingPageFields'], 20, 3);
        add_action('admin_notices', [__CLASS__, 'showLandingPageValidationNotices'], 20);
    }

    public static function validateLandingPageFields($post_id, $post, $update) {
        //error_log("validateLandingPageFields fired for post_id {$post_id}");

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Redirect revision post IDs to parent post ID
        if (wp_is_post_revision($post_id)) {
            $parent_post_id = wp_is_post_revision($post_id);
            $post = get_post($parent_post_id);
            if (!$post) {
                return;
            }
            $post_id = $parent_post_id;
        }

        if ( $post->post_type !== 'landing_page' ) {
            return;
        }

        if ( wp_is_post_autosave( $post_id ) ) {
            return;
        }

        $missing_fields = [];

        // Core landing page metadata required across all templates.
        $core_required_fields = [
            'client'     => 'client',
            'keyword'    => 'keyword',
        ];
        foreach ( $core_required_fields as $field_key => $field_label ) {
            $value = carbon_get_post_meta( $post_id, $field_key );
            if ( empty( $value ) ) {
                $missing_fields[] = $field_label;
            }
        }

        // A phone is required, but the page's own number is optional: the client's default
        // "Contact Phone" counts (see lpmanager_get_landing_phone()).
        if ( function_exists( 'lpmanager_get_landing_phone' ) && '' === lpmanager_get_landing_phone( $post_id ) ) {
            $missing_fields[] = 'client_tel';
        }

        // Template-specific required fields.
        $template = Template_Loader::get_effective_template_for_page( $post_id );
        if ( ! empty( $template ) ) {
            $required_fields = Required_Fields_Registry::get( $template );
            foreach ( $required_fields as $field ) {
                $value = carbon_get_post_meta( $post_id, $field );
                if ( empty( $value ) ) {
                    $missing_fields[] = $field;
                }
            }
        }

        if ( ! empty( $missing_fields ) ) {
            set_transient( "lp_missing_fields_{$post_id}", $missing_fields, 60 );
        } else {
            delete_transient( "lp_missing_fields_{$post_id}" );
        }
    }

    public static function showLandingPageValidationNotices() {
        $screen = get_current_screen();
        if ( ! $screen || $screen->post_type !== 'landing_page' ) {
            return;
        }

        // Try to get post ID from screen or request
        $post_id = 0;

        if (isset($_GET['post'])) {
            $post_id = intval($_GET['post']);
        } elseif (isset($_POST['post_ID'])) {
            $post_id = intval($_POST['post_ID']);
        }

        if (!$post_id) {
            global $post;
            if ($post) {
                $post_id = $post->ID;
            }
        }

        if ( ! $post_id ) {
            return;
        }

        $missing_fields = get_transient( "lp_missing_fields_{$post_id}" );

        if ( $missing_fields ) {
            echo '<div class="notice notice-error is-dismissible"><p><strong>' . esc_html__( 'Missing required fields for this template:', 'landing-page-manager' ) . '</strong></p><ul>';
            foreach ( $missing_fields as $field ) {
                echo '<li>' . esc_html( $field ) . '</li>';
            }
            echo '</ul></div>';
        }
    }

}
