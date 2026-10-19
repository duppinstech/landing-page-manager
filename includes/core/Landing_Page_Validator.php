<?php
namespace LPManager\core;

use LPManager\core\Required_Fields_Registry;
use LPManager\templates\Template_Loader;

class Landing_Page_Validator {

    /**
     * Post meta set when a page was kept as a draft instead of going live. It stays until the page
     * passes validation, so the notice survives cron runs, Quick Edit, bulk edit and REST saves.
     * Value: [ 'time' => int, 'target' => 'publish'|'future' ].
     */
    const KEPT_DRAFT_META = '_lpmanager_kept_draft';

    public static function init() {
        add_action( 'transition_post_status', [ __CLASS__, 'note_publish_transition' ], 10, 3 );
        add_action('save_post', [__CLASS__, 'validateLandingPageFields'], 20, 3);
        add_action('admin_notices', [__CLASS__, 'showLandingPageValidationNotices'], 20);
        add_action( 'admin_notices', [ __CLASS__, 'show_kept_draft_list_notice' ], 20 );
        add_filter( 'redirect_post_location', [ __CLASS__, 'kept_draft_redirect' ], 10, 2 );
    }

    /** Landing pages kept as drafts in this request, for the redirect message. */
    private static $kept_draft_now = [];

    /**
     * After a page was kept as a draft, show WordPress's "Draft updated" message rather than
     * "Post published".
     *
     * @param string $location Redirect URL.
     * @param int    $post_id  Post ID.
     * @return string
     */
    public static function kept_draft_redirect( $location, $post_id ) {
        if ( ! empty( self::$kept_draft_now[ (int) $post_id ] ) && 'draft' === get_post_status( $post_id ) ) {
            $location = add_query_arg( 'message', 10, remove_query_arg( 'message', $location ) );
        }
        return $location;
    }

    /** Landing pages going live in this request (published or scheduled), keyed by ID => new status. */
    private static $being_published = [];

    /**
     * Remember landing pages being published or scheduled (not already-live pages being edited),
     * so the validator can keep an incomplete page as a draft instead (owner decision, 10-16).
     * Scheduling is checked when the page is scheduled, not only when cron publishes it later.
     *
     * @param string   $new_status New status.
     * @param string   $old_status Old status.
     * @param \WP_Post $post       Post.
     */
    public static function note_publish_transition( $new_status, $old_status, $post ) {
        if ( ! $post || 'landing_page' !== $post->post_type || 'publish' === $old_status || $new_status === $old_status ) {
            return;
        }
        if ( 'publish' === $new_status || 'future' === $new_status ) {
            self::$being_published[ (int) $post->ID ] = $new_status;
        }
    }

    /**
     * Required fields this landing page is missing, as field names. Core fields first, then the
     * active template's (Required_Fields_Registry).
     *
     * @param int $post_id Landing page ID.
     * @return string[]
     */
    public static function get_missing_fields( $post_id ) {
        $missing_fields = [];

        // Core landing page metadata required across all templates.
        foreach ( [ 'client', 'keyword' ] as $field ) {
            if ( empty( carbon_get_post_meta( $post_id, $field ) ) ) {
                $missing_fields[] = $field;
            }
        }

        // A phone is required, but the page's own number is optional: the client's default
        // "Contact Phone" counts (see lpmanager_get_landing_phone()).
        if ( function_exists( 'lpmanager_get_landing_phone' ) && '' === lpmanager_get_landing_phone( $post_id ) ) {
            $missing_fields[] = 'client_tel';
        }

        $template = Template_Loader::get_effective_template_for_page( $post_id );
        if ( ! empty( $template ) ) {
            foreach ( Required_Fields_Registry::get( $template ) as $field ) {
                if ( empty( carbon_get_post_meta( $post_id, $field ) ) ) {
                    $missing_fields[] = $field;
                }
            }
        }

        return $missing_fields;
    }

    public static function validateLandingPageFields($post_id, $post, $update) {
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

        // Add New creates an empty auto-draft through save_post before anything is typed; checking
        // it would show "Missing required fields" on a blank form. Real saves are still checked.
        if ( 'auto-draft' === $post->post_status ) {
            return;
        }

        if ( wp_is_post_autosave( $post_id ) ) {
            return;
        }

        $target = isset( self::$being_published[ (int) $post_id ] ) ? self::$being_published[ (int) $post_id ] : '';
        unset( self::$being_published[ (int) $post_id ] );

        if ( empty( self::get_missing_fields( $post_id ) ) ) {
            delete_post_meta( $post_id, self::KEPT_DRAFT_META );
            return;
        }

        // Keep a page that is being published or scheduled (not one that is already live) as a draft.
        if ( '' !== $target ) {
            remove_action( 'save_post', [ __CLASS__, 'validateLandingPageFields' ], 20 );
            wp_update_post( [ 'ID' => (int) $post_id, 'post_status' => 'draft' ] );
            add_action( 'save_post', [ __CLASS__, 'validateLandingPageFields' ], 20, 3 );
            update_post_meta( $post_id, self::KEPT_DRAFT_META, [ 'time' => time(), 'target' => $target ] );
            self::$kept_draft_now[ (int) $post_id ] = true;
        }
    }

    /**
     * Label for a missing field in the notice: the template's registered label, else the Carbon
     * field's own label and the tab it is on.
     *
     * @param string $field   Field name.
     * @param int    $post_id Landing page ID.
     * @return string
     */
    private static function field_label( $field, $post_id ) {
        $core = [
            'client'     => __( 'Client Name (Landing Page Details)', 'landing-page-manager' ),
            'keyword'    => __( 'Keyword (Landing Page Details)', 'landing-page-manager' ),
            'client_tel' => __( 'Phone: fill in this page\'s "Page phone", or the client\'s Contact Phone', 'landing-page-manager' ),
        ];
        if ( isset( $core[ $field ] ) ) {
            return $core[ $field ];
        }

        $label = Required_Fields_Registry::get_label( Template_Loader::get_effective_template_for_page( $post_id ), $field );
        if ( '' !== $label ) {
            return $label;
        }

        if ( class_exists( '\Carbon_Fields\Carbon_Fields' ) ) {
            try {
                $repository = \Carbon_Fields\Carbon_Fields::resolve( 'container_repository' );
                foreach ( $repository->get_containers( 'post_meta' ) as $container ) {
                    $carbon_field = $container->get_root_field_by_name( $field );
                    if ( ! $carbon_field ) {
                        continue;
                    }
                    $where = $container->title;
                    if ( ! empty( $container->settings['tabs'] ) && is_array( $container->settings['tabs'] ) ) {
                        foreach ( $container->settings['tabs'] as $tab => $names ) {
                            if ( in_array( $carbon_field->get_name(), (array) $names, true ) ) {
                                /* translators: 1: box title, for example "Starter One", 2: tab name */
                                $where = sprintf( __( '%1$s → %2$s tab', 'landing-page-manager' ), $container->title, $tab );
                                break;
                            }
                        }
                    }
                    /* translators: 1: field label, 2: where it is, for example "Starter One → Hero tab" */
                    return sprintf( __( '%1$s (%2$s)', 'landing-page-manager' ), $carbon_field->get_label(), $where );
                }
            } catch ( \Exception $e ) {
                // Fall back to the field name below.
            }
        }

        return $field;
    }

    public static function showLandingPageValidationNotices() {
        $screen = get_current_screen();
        if ( ! $screen || 'post' !== $screen->base || $screen->post_type !== 'landing_page' ) {
            return;
        }

        $post = get_post();
        if ( ! $post || 'landing_page' !== $post->post_type || 'auto-draft' === $post->post_status ) {
            return;
        }
        $post_id = (int) $post->ID;

        // Worked out on every load, so the notice stays until the fields are filled in.
        $missing_fields = self::get_missing_fields( $post_id );
        if ( empty( $missing_fields ) ) {
            return;
        }

        $kept = get_post_meta( $post_id, self::KEPT_DRAFT_META, true );
        if ( is_array( $kept ) && 'draft' === $post->post_status ) {
            $message = ( isset( $kept['target'] ) && 'future' === $kept['target'] )
                ? __( 'This landing page was saved as a draft instead of being scheduled, because required fields are missing. Your changes were saved. Fill in the fields below, then schedule it again.', 'landing-page-manager' )
                : __( 'This landing page was saved as a draft instead of being published, because required fields are missing. Your changes were saved. Fill in the fields below, then publish again.', 'landing-page-manager' );
            echo '<div class="notice notice-warning"><p>' . esc_html( $message ) . '</p></div>';
        }

        echo '<div class="notice notice-error"><p><strong>' . esc_html__( 'Missing required fields for this template:', 'landing-page-manager' ) . '</strong></p><ul>';
        foreach ( $missing_fields as $field ) {
            echo '<li>' . esc_html( self::field_label( $field, $post_id ) ) . '</li>';
        }
        echo '</ul></div>';
    }

    /**
     * On the Landing Pages list, name the pages that were kept as drafts instead of going live,
     * for example by Quick Edit, bulk edit, a REST save or a scheduled publish run by cron.
     */
    public static function show_kept_draft_list_notice() {
        $screen = get_current_screen();
        if ( ! $screen || 'edit' !== $screen->base || 'landing_page' !== $screen->post_type ) {
            return;
        }

        $query = new \WP_Query( [
            'post_type'              => 'landing_page',
            'post_status'            => 'draft',
            'meta_key'               => self::KEPT_DRAFT_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
            'posts_per_page'         => 20,
            'orderby'                => 'modified',
            'order'                  => 'DESC',
            'no_found_rows'          => false,
            'update_post_term_cache' => false,
        ] );
        $items = [];
        foreach ( $query->posts as $post ) {
            if ( ! current_user_can( 'edit_post', $post->ID ) ) {
                continue;
            }
            $title   = '' !== $post->post_title ? $post->post_title : __( '(no title)', 'landing-page-manager' );
            $items[] = '<a href="' . esc_url( get_edit_post_link( $post->ID ) ) . '">' . esc_html( $title ) . '</a>';
        }
        if ( empty( $items ) ) {
            return;
        }

        echo '<div class="notice notice-warning"><p>'
            . esc_html( _n( 'This landing page was kept as a draft instead of going live, because required fields are missing. Open it to see which:', 'These landing pages were kept as drafts instead of going live, because required fields are missing. Open each one to see which:', count( $items ), 'landing-page-manager' ) )
            . ' ' . implode( ', ', $items ); // Each item is escaped above.
        if ( (int) $query->found_posts > count( $query->posts ) ) {
            /* translators: %d: number of further pages */
            echo ' ' . esc_html( sprintf( __( 'and %d more.', 'landing-page-manager' ), (int) $query->found_posts - count( $query->posts ) ) );
        }
        echo '</p></div>';
    }

}
