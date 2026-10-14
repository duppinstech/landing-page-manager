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
    // `lpmanager_pro_*` belongs to the Pro add-on, which manages its own uninstall.
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE ( option_name LIKE %s OR option_name LIKE %s ) AND option_name NOT LIKE %s AND option_name NOT LIKE %s",
            $wpdb->esc_like( 'lpmanager_' ) . '%',
            $wpdb->esc_like( '_lpmanager_' ) . '%',
            $wpdb->esc_like( 'lpmanager_pro_' ) . '%',
            $wpdb->esc_like( '_lpmanager_pro_' ) . '%'
        )
    );

    // Transients (validation notices).
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
            $wpdb->esc_like( '_transient_lp_missing_fields_' ) . '%',
            $wpdb->esc_like( '_transient_timeout_lp_missing_fields_' ) . '%'
        )
    );

    if ( defined( 'LPMANAGER_UNINSTALL_DELETE_CONTENT' ) && LPMANAGER_UNINSTALL_DELETE_CONTENT ) {
        $post_ids = get_posts(
            [
                'post_type'      => 'landing_page',
                'post_status'    => 'any',
                'posts_per_page' => -1,
                'fields'         => 'ids',
            ]
        );
        foreach ( $post_ids as $post_id ) {
            wp_delete_post( $post_id, true );
        }

        // The plugin isn't loaded during uninstall, so register the taxonomies for wp_delete_term().
        foreach ( [ 'client', 'keyword' ] as $taxonomy ) {
            if ( ! taxonomy_exists( $taxonomy ) ) {
                register_taxonomy( $taxonomy, 'landing_page' );
            }
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

    flush_rewrite_rules( false );
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
