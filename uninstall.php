<?php
/**
 * Uninstall Landing Page Manager.
 *
 * Always removes plugin settings (Carbon Fields theme options stored as `_lpmanager_*`,
 * plain `lpmanager_*` options) and plugin transients.
 *
 * Landing pages, client/keyword terms and their meta are kept by default so content
 * survives a reinstall. To delete them too, add this to wp-config.php before deleting
 * the plugin:
 *
 *     define( 'LPMANAGER_UNINSTALL_DELETE_CONTENT', true );
 *
 * @package LandingPageManager
 * @author  Odell Duppins / Duppins Technology
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

/**
 * Remove plugin data for the current site.
 */
function lpmanager_uninstall_site() {
    global $wpdb;

    // Settings. Carbon Fields stores theme options as `_<field>` rows (plus `_<field>|...` for complex fields).
    // `lpmanager_pro_*` and the tracking toggles `_lpmanager_track_*` belong to the Pro add-on
    // (agreed with the Pro repo, 10-16), which removes them in its own uninstall.
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE ( option_name LIKE %s OR option_name LIKE %s ) AND option_name NOT LIKE %s AND option_name NOT LIKE %s AND option_name NOT LIKE %s",
            $wpdb->esc_like( 'lpmanager_' ) . '%',
            $wpdb->esc_like( '_lpmanager_' ) . '%',
            $wpdb->esc_like( 'lpmanager_pro_' ) . '%',
            $wpdb->esc_like( '_lpmanager_pro_' ) . '%',
            $wpdb->esc_like( '_lpmanager_track_' ) . '%'
        )
    );

    // Settings save stores plaintext copies of the API keys (for the masked-input UI) inside
    // Carbon's shared container option. Remove only our keys; other plugins may use the option.
    $container = get_option( 'carbon_fields_theme_options_container' );
    if ( is_array( $container ) ) {
        $before = count( $container );
        foreach ( array_keys( $container ) as $key ) {
            if ( 0 === strpos( (string) $key, 'lpmanager_' ) && 0 !== strpos( (string) $key, 'lpmanager_pro_' ) ) {
                unset( $container[ $key ] );
            }
        }
        if ( empty( $container ) ) {
            delete_option( 'carbon_fields_theme_options_container' );
        } elseif ( count( $container ) !== $before ) {
            update_option( 'carbon_fields_theme_options_container', $container );
        }
    }

    // Orphaned client meta from the removed per-client "Landing Page Template" field (Odell, 10-16).
    // Carbon Fields stores it as `_lpmanager_client_template` (plus `|`-suffixed rows).
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->termmeta} WHERE meta_key LIKE %s",
            $wpdb->esc_like( '_lpmanager_client_template' ) . '%'
        )
    );

    // Transients (validation notices, Google reviews locks).
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s",
            $wpdb->esc_like( '_transient_lp_missing_fields_' ) . '%',
            $wpdb->esc_like( '_transient_timeout_lp_missing_fields_' ) . '%',
            $wpdb->esc_like( '_transient_lp_kept_draft_' ) . '%',
            $wpdb->esc_like( '_transient_timeout_lp_kept_draft_' ) . '%',
            $wpdb->esc_like( '_transient_lpmanager_reviews_' ) . '%',
            $wpdb->esc_like( '_transient_timeout_lpmanager_reviews_' ) . '%'
        )
    );

    if ( defined( 'LPMANAGER_UNINSTALL_DELETE_CONTENT' ) && LPMANAGER_UNINSTALL_DELETE_CONTENT ) {
        // Direct query: get_posts( 'any' ) skips trash and auto-draft.
        $post_ids = $wpdb->get_col(
            $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s", 'landing_page' )
        );
        foreach ( $post_ids as $post_id ) {
            wp_delete_post( (int) $post_id, true );
        }

        // The plugin isn't loaded during uninstall, so register the taxonomies for wp_delete_term().
        // If one is already registered, another plugin owns that slug — leave its terms alone.
        foreach ( [ 'client', 'keyword' ] as $taxonomy ) {
            if ( taxonomy_exists( $taxonomy ) ) {
                continue;
            }
            register_taxonomy( $taxonomy, 'landing_page' );
            $term_ids = get_terms(
                [
                    'taxonomy'   => $taxonomy,
                    'hide_empty' => false,
                    'fields'     => 'ids',
                ]
            );
            if ( ! is_wp_error( $term_ids ) ) {
                foreach ( $term_ids as $term_id ) {
                    wp_delete_term( $term_id, $taxonomy );
                }
            }
        }
    }

    // Not flush_rewrite_rules(): inside switch_to_blog() it would write this request's rules into
    // other sites. Deleting the option makes each site rebuild its own on the next request.
    delete_option( 'rewrite_rules' );
}

if ( is_multisite() ) {
    foreach ( get_sites( [ 'fields' => 'ids', 'number' => 0 ] ) as $lpmanager_site_id ) {
        switch_to_blog( $lpmanager_site_id );
        lpmanager_uninstall_site();
        restore_current_blog();
    }
} else {
    lpmanager_uninstall_site();
}

// Network-wide cache of the latest GitHub release (Update_Checker).
delete_site_transient( 'lpmanager_github_release' );

// Per-user notice dismissals (user meta is shared across a network).
delete_metadata( 'user', 0, 'lpmanager_server_key_notice_dismissed', '', true );
