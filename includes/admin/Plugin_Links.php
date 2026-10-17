<?php
/**
 * Class Plugin_Links
 *
 * Links on this plugin's row on the Plugins screen: Settings and Help beside
 * Deactivate, then Sponsor after the author and Support at the end of the
 * Version / By line.
 *
 * @author Odell Duppins / Duppins Technology
 */
namespace LPManager\admin;

class Plugin_Links {

    const SPONSOR_URL   = 'https://github.com/sponsors/oduppinsjr';
    const SUPPORT_EMAIL = 'support@duppinstech.com';

    /**
     * Register Plugins-screen hooks.
     */
    public static function init() {
        if ( ! is_admin() ) {
            return;
        }
        add_filter( 'plugin_action_links_' . self::basename(), [ __CLASS__, 'action_links' ] );
        add_filter( 'plugin_row_meta', [ __CLASS__, 'row_meta' ], 10, 3 );
    }

    /**
     * Settings and Help sit with Activate and Deactivate.
     *
     * @param string[] $links Existing action links.
     * @return string[]
     */
    public static function action_links( $links ) {
        $extra = [];
        if ( current_user_can( 'manage_options' ) ) {
            $extra[] = '<a href="' . esc_url( admin_url( 'admin.php?page=' . Help_Widget::PAGE_SETTINGS ) ) . '">' . esc_html__( 'Settings', 'landing-page-manager' ) . '</a>';
        }
        $extra[] = self::new_tab_link( Help_Widget::guide_url() . '#install', __( 'Help', 'landing-page-manager' ) );
        return array_merge( $extra, (array) $links );
    }

    /**
     * Sponsor follows the author. Support stays at the end of that line.
     *
     * @param string[]             $meta        Existing meta links.
     * @param string               $file        Plugin basename.
     * @param array<string,string> $plugin_data Plugin header fields.
     * @return string[]
     */
    public static function row_meta( $meta, $file, $plugin_data = [] ) {
        if ( self::basename() !== $file ) {
            return $meta;
        }
        $meta   = (array) $meta;
        $author = isset( $plugin_data['AuthorURI'] ) ? (string) $plugin_data['AuthorURI'] : '';
        $at     = count( $meta );
        if ( '' !== $author ) {
            foreach ( $meta as $index => $item ) {
                if ( is_string( $item ) && false !== strpos( $item, $author ) ) {
                    $at = $index + 1;
                    break;
                }
            }
        }
        array_splice( $meta, $at, 0, [ self::new_tab_link( self::SPONSOR_URL, __( 'Sponsor', 'landing-page-manager' ) ) ] );
        $meta[] = '<a href="' . esc_url( 'mailto:' . self::SUPPORT_EMAIL ) . '">' . esc_html__( 'Support', 'landing-page-manager' ) . '</a>';
        return $meta;
    }

    /**
     * Link that opens in a new tab and says so to screen readers.
     *
     * @param string $url   Destination.
     * @param string $label Visible text (already translated).
     * @return string
     */
    private static function new_tab_link( $url, $label ) {
        return '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $label )
            . '<span class="screen-reader-text">' . esc_html__( ' (opens in a new tab)', 'landing-page-manager' ) . '</span></a>';
    }

    /**
     * This plugin's basename, e.g. landing-page-manager/landing-page-manager.php.
     *
     * @return string
     */
    private static function basename() {
        return plugin_basename( LP_MANAGER_PLUGIN_PATH . 'landing-page-manager.php' );
    }
}
