<?php
/**
 * Plugin update checker: optional updates from GitHub and/or WordPress.org.
 * Controlled by Settings → Updates → "Plugin update source".
 */
namespace LPManager;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Update_Checker {

    const GITHUB_API_URL = 'https://api.github.com/repos/oduppinsjr/landing-page-manager/releases/latest';
    const REPO_URL        = 'https://github.com/oduppinsjr/landing-page-manager';
    const PLUGIN_SLUG     = 'landing-page-manager';

    public static function init() {
        add_filter( 'pre_set_site_transient_update_plugins', [ __CLASS__, 'inject_update' ], 10, 2 );
        add_filter( 'plugin_action_links_' . self::get_plugin_basename(), [ __CLASS__, 'add_plugin_action_links' ] );
        add_filter( 'upgrader_source_selection', [ __CLASS__, 'normalize_install_source_dir' ], 10, 4 );
        add_action( 'admin_init', [ __CLASS__, 'handle_manual_update_check' ] );
        add_action( 'admin_notices', [ __CLASS__, 'render_manual_update_notice' ] );
    }

    /**
     * Get the chosen update source: 'wp_org' | 'github' | 'both'.
     */
    private static function get_update_source() {
        if ( ! function_exists( 'carbon_get_theme_option' ) ) {
            return 'both';
        }
        $source = carbon_get_theme_option( 'lpmanager_update_source' );
        return in_array( $source, [ 'wp_org', 'github', 'both' ], true ) ? $source : 'both';
    }

    /**
     * Plugin basename (e.g. landing-page-manager/landing-page-manager.php).
     */
    private static function get_plugin_basename() {
        return plugin_basename( LP_MANAGER_PLUGIN_PATH . 'landing-page-manager.php' );
    }

    /**
     * Current plugin version from main file header.
     */
    private static function get_current_version() {
        static $version = null;
        if ( $version !== null ) {
            return $version;
        }
        if ( ! function_exists( 'get_plugin_data' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $data = get_plugin_data( LP_MANAGER_PLUGIN_PATH . 'landing-page-manager.php', false, false );
        $version = isset( $data['Version'] ) ? $data['Version'] : '0';
        return $version;
    }

    /**
     * Fetch latest release from GitHub. Returns [ 'version' => string, 'package' => zip URL ] or null.
     */
    private static function fetch_github_release() {
        $url = apply_filters( 'lpmanager_github_releases_api_url', self::GITHUB_API_URL );
        $response = wp_remote_get( $url, [
            'timeout' => 10,
            'headers' => [
                'Accept'     => 'application/vnd.github.v3+json',
                'User-Agent' => 'Landing-Page-Manager-Updater',
            ],
        ] );
        if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
            return null;
        }
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );
        if ( ! $data || empty( $data['tag_name'] ) ) {
            return null;
        }
        $version = ltrim( (string) $data['tag_name'], 'v' );
        if ( ! preg_match( '/^\d+\.\d+(\.\d+)?/', $version ) ) {
            return null;
        }
        $zip = self::resolve_package_url( $data );
        // Allow overriding the package URL (e.g. to a Release asset zip with correct folder structure).
        $zip = apply_filters( 'lpmanager_github_update_package_url', $zip, $version, $data );
        if ( ! $zip ) {
            return null;
        }
        return [ 'version' => $version, 'package' => $zip ];
    }

    /**
     * Prefer a release asset zip (correct folder structure), fallback to GitHub zipball.
     */
    private static function resolve_package_url( array $release_data ) {
        if ( ! empty( $release_data['assets'] ) && is_array( $release_data['assets'] ) ) {
            foreach ( $release_data['assets'] as $asset ) {
                if ( empty( $asset['name'] ) || empty( $asset['browser_download_url'] ) ) {
                    continue;
                }
                $name = strtolower( (string) $asset['name'] );
                $is_zip = substr( $name, -4 ) === '.zip';
                $looks_like_plugin_zip = strpos( $name, self::PLUGIN_SLUG ) !== false;
                if ( $is_zip && $looks_like_plugin_zip ) {
                    return (string) $asset['browser_download_url'];
                }
            }
        }
        return isset( $release_data['zipball_url'] ) ? (string) $release_data['zipball_url'] : '';
    }

    /**
     * Build the update object WordPress expects for a plugin.
     */
    private static function build_update_object( $new_version, $package_url ) {
        $obj = new \stdClass();
        $obj->slug        = self::PLUGIN_SLUG;
        $obj->plugin      = self::get_plugin_basename();
        $obj->new_version = $new_version;
        $obj->package     = $package_url;
        $obj->url         = self::REPO_URL;
        $obj->id          = '0';
        $obj->icons       = [];
        $obj->banners     = [];
        $obj->banners_rtl = [];
        $obj->tested      = '';
        $obj->requires_php = '';
        return $obj;
    }

    /**
     * Compare two version strings. Returns 1 if $a > $b, -1 if $a < $b, 0 if equal.
     */
    private static function version_compare( $a, $b ) {
        $cmp = version_compare( $a, $b );
        return $cmp > 0 ? 1 : ( $cmp < 0 ? -1 : 0 );
    }

    /**
     * Inject our plugin update into the transient when source is GitHub or Both.
     */
    public static function inject_update( $transient, $action ) {
        if ( $action !== 'update_plugins' || ! is_object( $transient ) ) {
            return $transient;
        }
        $source = self::get_update_source();
        if ( $source === 'wp_org' ) {
            return $transient;
        }

        $current = self::get_current_version();
        $basename = self::get_plugin_basename();

        $github = self::fetch_github_release();
        if ( ! $github || self::version_compare( $github['version'], $current ) <= 0 ) {
            return $transient;
        }

        if ( $source === 'github' ) {
            if ( ! isset( $transient->response ) ) {
                $transient->response = [];
            }
            $transient->response[ $basename ] = self::build_update_object( $github['version'], $github['package'] );
            return $transient;
        }

        if ( $source === 'both' ) {
            $wp_org_version = null;
            if ( isset( $transient->response[ $basename ] ) && is_object( $transient->response[ $basename ] ) ) {
                $wp_org_version = isset( $transient->response[ $basename ]->new_version )
                    ? $transient->response[ $basename ]->new_version
                    : null;
            }
            $use_github = ! $wp_org_version || self::version_compare( $github['version'], $wp_org_version ) >= 0;
            if ( $use_github ) {
                if ( ! isset( $transient->response ) ) {
                    $transient->response = [];
                }
                $transient->response[ $basename ] = self::build_update_object( $github['version'], $github['package'] );
            }
            return $transient;
        }

        return $transient;
    }

    /**
     * Add "Check for updates" action link on this plugin row.
     */
    public static function add_plugin_action_links( $links ) {
        if ( ! current_user_can( 'update_plugins' ) ) {
            return $links;
        }
        $url = wp_nonce_url(
            add_query_arg(
                [ 'lpmanager_check_updates' => '1' ],
                admin_url( 'plugins.php' )
            ),
            'lpmanager_check_updates'
        );
        array_unshift(
            $links,
            '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Check for updates', 'landing-page-manager' ) . '</a>'
        );
        return $links;
    }

    /**
     * Handle manual update check trigger from plugin action link.
     */
    public static function handle_manual_update_check() {
        if ( ! is_admin() || ! current_user_can( 'update_plugins' ) ) {
            return;
        }
        if ( empty( $_GET['lpmanager_check_updates'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return;
        }
        check_admin_referer( 'lpmanager_check_updates' );

        if ( ! function_exists( 'wp_update_plugins' ) ) {
            require_once ABSPATH . 'wp-includes/update.php';
        }

        delete_site_transient( 'update_plugins' );
        wp_update_plugins();

        $redirect = admin_url( 'plugins.php' );
        $redirect = add_query_arg( [ 'lpmanager_updates_checked' => '1' ], $redirect );
        wp_safe_redirect( $redirect );
        exit;
    }

    /**
     * Admin notice after manual update check.
     */
    public static function render_manual_update_notice() {
        if ( ! is_admin() || empty( $_GET['lpmanager_updates_checked'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return;
        }
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Landing Page Manager update check completed. Review available updates below.', 'landing-page-manager' ) . '</p></div>';
    }

    /**
     * Normalize GitHub zipball extraction folder to plugin slug.
     * Prevents installs into repo/hash-style directories.
     */
    public static function normalize_install_source_dir( $source, $remote_source, $upgrader, $hook_extra ) {
        if ( empty( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== self::get_plugin_basename() ) {
            return $source;
        }
        $desired = trailingslashit( $remote_source ) . self::PLUGIN_SLUG;
        if ( wp_normalize_path( $source ) === wp_normalize_path( $desired ) ) {
            return $source;
        }
        global $wp_filesystem;
        if ( ! $wp_filesystem || ! $wp_filesystem->exists( $source ) ) {
            return $source;
        }
        if ( $wp_filesystem->exists( $desired ) ) {
            $wp_filesystem->delete( $desired, true );
        }
        $moved = $wp_filesystem->move( $source, $desired );
        return $moved ? $desired : $source;
    }
}
