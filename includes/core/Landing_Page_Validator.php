<?php
namespace LPManager\core;

use LPManager\core\Required_Fields_Registry;
use LPManager\templates\Template_Loader;

class Landing_Page_Validator {

    public static function init() {
        //error_log('Landing_Page_Validator::init fired');

        add_action( 'transition_post_status', [ __CLASS__, 'note_publish_transition' ], 10, 3 );
        add_action('save_post', [__CLASS__, 'validateLandingPageFields'], 20, 3);
        add_action('admin_notices', [__CLASS__, 'showLandingPageValidationNotices'], 20);
        add_filter( 'redirect_post_location', [ __CLASS__, 'kept_draft_redirect' ], 10, 2 );
    }

    /**
     * After a page was kept as a draft, show WordPress's "Draft updated" message rather than
     * "Post published".
     *
     * @param string $location Redirect URL.
     * @param int    $post_id  Post ID.
     * @return string
     */
    public static function kept_draft_redirect( $location, $post_id ) {
        if ( get_transient( "lp_kept_draft_{$post_id}" ) && 'draft' === get_post_status( $post_id ) ) {
            $location = add_query_arg( 'message', 10, remove_query_arg( 'message', $location ) );
        }
        return $location;
    }

    /** Landing pages being published in this request (not already-live pages being edited). */
    private static $being_published = [];

    /**
     * Remember landing pages moving to "publish" from another status, so the validator can keep
     * an incomplete page as a draft instead of publishing it (Odell, 10-16).
     *
     * @param string   $new_status New status.
     * @param string   $old_status Old status.
     * @param \WP_Post $post       Post.
     */
    public static function note_publish_transition( $new_status, $old_status, $post ) {
        if ( 'publish' === $new_status && 'publish' !== $old_status && $post && 'landing_page' === $post->post_type ) {
            self::$being_published[ (int) $post->ID ] = true;
        }
    }

    public static function validateLandingPageFields($post_id, $post, $update) {
        //error_log("validateLandingPageFields fired for post_id {$post_id}");

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Skip revisions. The page's own save_post pass runs after Carbon has saved this request's
        // fields; on WP < 6.4 the revision pass runs first and would see empty fields.
        if ( wp_is_post_revision( $post_id ) ) {
            return;
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
            // Keep a page that is being published (not one that is already live) as a draft.
            if ( ! empty( self::$being_published[ (int) $post_id ] ) ) {
                unset( self::$being_published[ (int) $post_id ] );
                remove_action( 'save_post', [ __CLASS__, 'validateLandingPageFields' ], 20 );
                wp_update_post( [ 'ID' => (int) $post_id, 'post_status' => 'draft' ] );
                add_action( 'save_post', [ __CLASS__, 'validateLandingPageFields' ], 20, 3 );
                set_transient( "lp_kept_draft_{$post_id}", 1, 60 );
            }
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
            if ( get_transient( "lp_kept_draft_{$post_id}" ) ) {
                delete_transient( "lp_kept_draft_{$post_id}" );
                echo '<div class="notice notice-warning"><p>' . esc_html__( 'This landing page was saved as a draft instead of being published, because required fields are missing. Fill them in, then publish again.', 'landing-page-manager' ) . '</p></div>';
            }
            echo '<div class="notice notice-error is-dismissible"><p><strong>' . esc_html__( 'Missing required fields for this template:', 'landing-page-manager' ) . '</strong></p><ul>';
            foreach ( $missing_fields as $field ) {
                $labels = [
                    'client'     => __( 'Client Name (Landing Page Details)', 'landing-page-manager' ),
                    'keyword'    => __( 'Keyword (Landing Page Details)', 'landing-page-manager' ),
                    'client_tel' => __( 'Phone: fill in this page\'s "Page phone", or the client\'s Contact Phone', 'landing-page-manager' ),
                ];
                echo '<li>' . esc_html( isset( $labels[ $field ] ) ? $labels[ $field ] : $field ) . '</li>';
            }
            echo '</ul></div>';
        }
    }

}
