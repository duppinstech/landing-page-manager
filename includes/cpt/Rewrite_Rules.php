<?php
namespace LPManager\cpt;

class Rewrite_Rules {

    public static function init() {
        add_filter( 'query_vars', [ __CLASS__, 'add_query_vars' ] );
        add_action( 'init', [ __CLASS__, 'add_rewrite_rules' ] );
        add_action( 'parse_request', [ __CLASS__, 'parse_subdomain' ] );
        add_action( 'pre_get_posts', [ __CLASS__, 'handle_custom_landing_page_query' ] );
        add_action( 'wp', [ __CLASS__, 'enforce_host_client' ] );
        add_action( 'template_redirect', [ __CLASS__, 'redirect_stray_landing_url' ], 9 );
        add_filter( 'post_type_link', [ __CLASS__, 'custom_landing_page_link' ], 10, 2 );
        add_filter( 'preview_post_link', [ __CLASS__, 'custom_preview_link' ], 10, 2 );
    }

    public static function add_rewrite_rules() {
        $post_type = 'landing_page';
        $slug = get_post_type_object( $post_type )->rewrite['slug'] ?? 'landing';

        add_rewrite_rule(
            "^{$slug}/([^/]+)/?$",
            "index.php?post_type={$post_type}&name=\$matches[1]",
            'top'
        );
    }

    /**
     * Public query vars. `lp_client` is deliberately not one: only parse_subdomain() sets it, from
     * the host, so a URL can't pick the client.
     *
     * @param string[] $vars Public query vars.
     * @return string[]
     */
    public static function add_query_vars( $vars ) {
        $vars[] = 'force_lp_404';
        return $vars;
    }

    /** Client term for the request's host when it is a client subdomain, or null. */
    private static $host_client = null;

    /**
     * The client whose subdomain this request is for: the host must end in "." plus the main
     * domain, and the part before it must be a client's slug.
     *
     * @return \WP_Term|null
     */
    private static function client_for_host() {
        $host   = self::request_host();
        $suffix = '.' . self::main_host();
        if ( '.' === $suffix || ! self::ends_with( $host, $suffix ) ) return null;

        $client = get_term_by( 'slug', substr( $host, 0, -strlen( $suffix ) ), 'client' );
        return ( $client && ! is_wp_error( $client ) ) ? $client : null;
    }

    /** The request's host, lowercase, without a port. */
    private static function request_host() {
        $host = isset( $_SERVER['HTTP_HOST'] ) ? strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) ) : '';
        return (string) preg_replace( '/:\d+$/', '', $host );
    }

    /** The site's own host (from the home URL), lowercase. */
    private static function main_host() {
        return strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
    }

    /** Whether $host is longer than $suffix and ends with it. */
    private static function ends_with( $host, $suffix ) {
        return strlen( $host ) > strlen( $suffix ) && substr( $host, -strlen( $suffix ) ) === $suffix;
    }

    /** The request path when it is one level (a landing page slug), else ''. */
    private static function one_level_path() {
        $path = isset( $_SERVER['REQUEST_URI'] ) ? trim( (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ), '/' ) : '';
        return ( '' === $path || false !== strpos( $path, '/' ) ) ? '' : $path;
    }

    /**
     * The published landing page with this URL path as its slug. Published pages only: WordPress
     * doesn't keep draft slugs unique, so a lookup across all statuses could pick a draft.
     *
     * @param string $path One-level path.
     * @return \WP_Post|null
     */
    private static function published_landing_page( $path ) {
        $found = get_posts( [
            'post_type'        => 'landing_page',
            'name'             => sanitize_title_for_query( rawurlencode( urldecode( $path ) ) ),
            'post_status'      => 'publish',
            'posts_per_page'   => 1,
            'no_found_rows'    => true,
            'suppress_filters' => true,
        ] );
        return $found ? $found[0] : null;
    }

    /**
     * Route https://<client>.<main domain>/<page slug>/ to that client's published landing page.
     * Another client's page there is redirected to its own address (302, keeping the query string
     * so ad tracking survives; 302 because the page's client can change again). When there is
     * nowhere to send the visitor, the request 404s with the template's 404 page (Template_Loader).
     *
     * Previews always use the main site (see custom_preview_link()), so drafts are never routed here.
     *
     * @param \WP $wp Current request.
     */
    public static function parse_subdomain( $wp ) {
        self::$host_client = null;
        if ( is_admin() ) return;

        $client = self::client_for_host();
        if ( ! $client ) return;
        self::$host_client = $client;

        $path = self::one_level_path();
        $post = '' !== $path ? self::published_landing_page( $path ) : null;
        if ( ! $post ) return;

        // (A tax_query can't scope this: WP_Query ignores it on singular queries.)
        if ( has_term( (int) $client->term_id, 'client', $post ) ) {
            $wp->query_vars['lp_client'] = $client->slug;
            $wp->query_vars['name']      = $post->post_name;
            $wp->query_vars['post_type'] = 'landing_page';
            return;
        }

        self::redirect_to_permalink( $post, self::request_host(), $path );

        // Nowhere to send them: not found.
        $wp->query_vars = [
            'error'     => '404',
            'lp_client' => $client->slug,
            'post_type' => 'landing_page',
            'post__in'  => [ 0 ], // Match nothing.
        ];
    }

    /**
     * Before 1.0.16 a landing page's slug also served the page on the main host, www. and any
     * subdomain that is no client's (a renamed client slug, say). Keep those links working by
     * redirecting to the page's own address, but only where WordPress found nothing (a 404), so its
     * own content and routes (feeds, robots.txt, sitemaps, other plugins' endpoints) always win.
     * Runs before redirect_canonical (template_redirect, priority 10).
     */
    public static function redirect_stray_landing_url() {
        if ( self::$host_client || ! is_404() || is_admin() ) return;

        $host = self::request_host();
        $main = self::main_host();
        if ( '' === $host || '' === $main || ( $host !== $main && ! self::ends_with( $host, '.' . $main ) ) ) return;

        $path = self::one_level_path();
        $post = '' !== $path ? self::published_landing_page( $path ) : null;
        if ( $post ) {
            self::redirect_to_permalink( $post, $host, $path );
        }
    }

    /**
     * Redirect to the landing page's own address and exit, if that is on one of this site's hosts
     * and isn't the current URL.
     *
     * @param \WP_Post $post        Landing page.
     * @param string   $host        Current host.
     * @param string   $request_uri Current path, without slashes at the ends.
     */
    private static function redirect_to_permalink( $post, $host, $request_uri ) {
        $target      = (string) get_permalink( $post );
        $target_host = strtolower( (string) wp_parse_url( $target, PHP_URL_HOST ) );
        $main        = self::main_host();
        if ( $target_host !== $main && ! self::ends_with( $target_host, '.' . $main ) ) return;
        if ( $target_host === $host && trim( (string) wp_parse_url( $target, PHP_URL_PATH ), '/' ) === $request_uri ) return;

        $query = isset( $_SERVER['QUERY_STRING'] ) ? (string) wp_unslash( $_SERVER['QUERY_STRING'] ) : '';
        // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- a host of this site, checked above.
        wp_redirect( '' !== $query ? $target . ( false === strpos( $target, '?' ) ? '?' : '&' ) . $query : $target, 302, 'Landing Page Manager' );
        exit;
    }


    /**
     * A subdomain request shows published pages only.
     *
     * @param \WP_Query $query Query.
     */
    public static function handle_custom_landing_page_query( $query ) {
        if ( is_admin() || ! $query->is_main_query() ) return;

        if ( get_query_var( 'lp_client' ) && get_query_var( 'name' ) ) {
            $query->set( 'post_type', 'landing_page' );
            $query->set( 'name', get_query_var( 'name' ) );
            $query->set( 'posts_per_page', 1 );
            $query->set( 'post_status', 'publish' );
            $query->is_singular = true;
        }
    }

    /**
     * On a client subdomain, a landing page reached some other way (?p=, ?landing_page=, /landing-page/…)
     * must still belong to that client, otherwise the request 404s.
     */
    public static function enforce_host_client() {
        if ( ! self::$host_client || ! is_singular( 'landing_page' ) ) return;

        $post = get_queried_object();
        if ( $post instanceof \WP_Post && ! has_term( (int) self::$host_client->term_id, 'client', $post ) ) {
            global $wp_query;
            $wp_query->set_404();
            $wp_query->set( 'lp_client', self::$host_client->slug );
            status_header( 404 );
            nocache_headers();
        }
    }

    public static function custom_landing_page_link( $post_link, $post ) {
        // A draft has no slug yet: keep WordPress's ?post_type=…&p=… link rather than "https://client.example.com//".
        if ( $post->post_type !== 'landing_page' || '' === (string) $post->post_name ) {
            return $post_link;
        }

        $client_terms = get_the_terms( $post->ID, 'client' );
        if ( ! $client_terms || is_wp_error( $client_terms ) ) {
            return $post_link;
        }

        $client    = $client_terms[0];
        $subdomain = $client->slug;

        if ( ! $subdomain ) {
            return $post_link;
        }

        $main_domain = parse_url( home_url(), PHP_URL_HOST );
        $post_slug   = $post->post_name;
        $new_link    = 'https://' . $subdomain . '.' . $main_domain . '/' . $post_slug . '/';

        return $new_link;
    }

    /**
     * Previews open on the main site, where the editor is logged in. Login cookies usually don't
     * reach client subdomains, and there WordPress can't show a draft or check the preview nonce
     * (it answers "Sorry, you are not allowed to preview drafts."). WordPress's own preview
     * arguments (preview_id, preview_nonce) are kept, so unsaved changes show.
     *
     * @param string   $preview_link Default preview link.
     * @param \WP_Post $post         Post.
     * @return string
     */
    public static function custom_preview_link( $preview_link, $post ) {
        if ( $post->post_type !== 'landing_page' ) {
            return $preview_link;
        }

        // The permalink filter above may already have moved $preview_link to the subdomain.
        $args = [];
        wp_parse_str( (string) wp_parse_url( $preview_link, PHP_URL_QUERY ), $args );
        unset( $args['p'], $args['page_id'], $args['post_type'], $args['name'], $args['landing_page'] );
        $args = array_filter( $args, 'is_scalar' ); // rawurlencode() below takes strings only.
        $args = array_merge( [ 'post_type' => 'landing_page', 'p' => (int) $post->ID ], $args, [ 'preview' => 'true' ] );

        return add_query_arg( array_map( 'rawurlencode', $args ), home_url( '/' ) );
    }
}
