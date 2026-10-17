<?php
/**
 * Plugin update checker: updates come only from GitHub Releases.
 *
 * The plugin header sets `Update URI`, so WordPress core (5.8+) never asks
 * WordPress.org about this plugin. The `landing-page-manager` slug is not ours on
 * WordPress.org yet, and whoever publishes it there must not be able to push code
 * to these sites. Revisit when the free core is listed on WordPress.org
 * (see .claude/memory/decisions.md, 2026-10-14).
 */
namespace LPManager;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Update_Checker {

    const GITHUB_API_URL = 'https://api.github.com/repos/duppinstech/landing-page-manager/releases/latest';
    const REPO_URL        = 'https://github.com/duppinstech/landing-page-manager';
    const PLUGIN_SLUG     = 'landing-page-manager';
    const CACHE_KEY       = 'lpmanager_github_release';
    const FORCED_KEY      = 'lpmanager_github_forced';
    // Keep in step with "Requires at least" / "Requires PHP" in landing-page-manager.php.
    const REQUIRES_WP     = '5.9';
    const REQUIRES_PHP    = '7.4';

    public static function init() {
        add_filter( 'pre_set_site_transient_update_plugins', [ __CLASS__, 'inject_update' ], 10, 2 );
        add_filter( 'upgrader_source_selection', [ __CLASS__, 'normalize_install_source_dir' ], 10, 4 );
        add_action( 'load-update-core.php', [ __CLASS__, 'maybe_force_check' ], 9 );
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
     * Latest GitHub release, cached in a site transient: 6 hours after a successful
     * fetch and 30 minutes after a failure. Unauthenticated GitHub API calls are
     * limited to 60 an hour per IP, and WordPress builds the update transient several
     * times per check. Returns [ 'version' => string, 'package' => zip URL ] or null.
     */
    private static function get_github_release() {
        $cached = get_site_transient( self::CACHE_KEY );
        if ( is_array( $cached ) && array_key_exists( 'release', $cached ) ) {
            return $cached['release'];
        }
        $release = self::fetch_github_release();
        self::cache_release( $release );
        return $release;
    }

    /**
     * Cache a fetched release (or a failed fetch, as null) for get_github_release().
     */
    private static function cache_release( $release ) {
        set_site_transient( self::CACHE_KEY, [ 'release' => $release ], $release ? 6 * HOUR_IN_SECONDS : 30 * MINUTE_IN_SECONDS );
    }

    /**
     * Dashboard → Updates → "Check again": fetch the latest release now, so a new
     * version shows up right away without a custom button. Runs before core's
     * wp_update_plugins (priority 10) and resets its last check, because core
     * otherwise skips plugins checked in the last minute.
     *
     * A failed fetch keeps the previously cached release, so a GitHub rate limit or
     * timeout can't hide a pending update. Forced fetches are limited to one a minute.
     */
    public static function maybe_force_check() {
        if ( ! self::is_forced_check() || get_site_transient( self::FORCED_KEY ) ) {
            return;
        }
        set_site_transient( self::FORCED_KEY, 1, MINUTE_IN_SECONDS );

        $release = self::fetch_github_release();
        if ( $release || ! is_array( get_site_transient( self::CACHE_KEY ) ) ) {
            self::cache_release( $release );
        }

        $current = get_site_transient( 'update_plugins' );
        if ( is_object( $current ) && isset( $current->last_checked ) ) {
            $current->last_checked = 0;
            set_site_transient( 'update_plugins', $current );
        }
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
                // Exact name only (landing-page-manager.zip or landing-page-manager-1.2.3.zip),
                // so e.g. a landing-page-manager-pro.zip asset is never installed as core.
                $pattern = '/^' . preg_quote( self::PLUGIN_SLUG, '/' ) . '(-v?\d+(\.\d+)*)?\.zip$/i';
                if ( preg_match( $pattern, (string) $asset['name'] ) ) {
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
        $obj->requires    = self::REQUIRES_WP;
        $obj->requires_php = self::REQUIRES_PHP;
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
     * Put the GitHub release (if newer) into the update transient, and drop any
     * entry for this plugin that came from somewhere else. `Update URI` already
     * stops core from asking WordPress.org; this is the second line of defense.
     */
    public static function inject_update( $transient, $action ) {
        if ( $action !== 'update_plugins' || ! is_object( $transient ) ) {
            return $transient;
        }

        $basename = self::get_plugin_basename();
        if ( isset( $transient->response[ $basename ] ) ) {
            unset( $transient->response[ $basename ] );
        }
        if ( isset( $transient->no_update[ $basename ] ) ) {
            unset( $transient->no_update[ $basename ] );
        }

        $github = self::get_github_release();
        if ( ! $github || self::version_compare( $github['version'], self::get_current_version() ) <= 0 ) {
            return $transient;
        }

        if ( ! isset( $transient->response ) || ! is_array( $transient->response ) ) {
            $transient->response = [];
        }
        $transient->response[ $basename ] = self::build_update_object( $github['version'], $github['package'] );
        return $transient;
    }

    /**
     * Whether this request is Dashboard → Updates → "Check again" by a user who can
     * update plugins. Core doesn't nonce that link either; the worst a forged
     * visit can do is one GitHub request a minute (see maybe_force_check()).
     */
    private static function is_forced_check() {
        global $pagenow;
        return is_admin()
            && 'update-core.php' === $pagenow
            && ! empty( $_GET['force-check'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only, mirrors core.
            && current_user_can( 'update_plugins' );
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
