<?php
namespace LPManager\cpt;

class Rewrite_Rules {

    public static function init() {
        add_filter( 'query_vars', [ __CLASS__, 'add_query_vars' ] );
        add_action( 'init', [ __CLASS__, 'add_rewrite_rules' ] );
        add_action( 'parse_request', [ __CLASS__, 'parse_subdomain' ] );
        add_action( 'pre_get_posts', [ __CLASS__, 'handle_custom_landing_page_query' ] );
        add_action( 'wp', [ __CLASS__, 'enforce_host_client' ] );
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
        $host        = isset( $_SERVER['HTTP_HOST'] ) ? strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) ) : '';
        $host        = preg_replace( '/:\d+$/', '', $host );
        $main_domain = strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );

        if ( '' === $host || '' === $main_domain ) return null;

        $suffix = '.' . $main_domain;
        if ( strlen( $host ) <= strlen( $suffix ) || substr( $host, -strlen( $suffix ) ) !== $suffix ) return null;

        $client = get_term_by( 'slug', substr( $host, 0, -strlen( $suffix ) ), 'client' );
        return ( $client && ! is_wp_error( $client ) ) ? $client : null;
    }

    /**
     * Route https://<client>.<main domain>/<page slug>/ to that client's published landing page.
     * A page that belongs to another client 404s there (with the template's 404 page, see
     * Template_Loader::load_template()).
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

        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? trim( (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ), '/' ) : '';

        // Landing pages have one-level slugs; anything else is left to WordPress.
        if ( '' === $request_uri || false !== strpos( $request_uri, '/' ) ) return;

        // Published pages only: WordPress doesn't keep draft slugs unique, so a lookup across all
        // statuses could pick a draft instead of the live page.
        $slug  = sanitize_title_for_query( rawurlencode( urldecode( $request_uri ) ) );
        $found = get_posts( [
            'post_type'        => 'landing_page',
            'name'             => $slug,
            'post_status'      => 'publish',
            'posts_per_page'   => 1,
            'no_found_rows'    => true,
            'suppress_filters' => true,
        ] );
        if ( ! $found ) return;
        $post = $found[0];

        // Another client's page. (A tax_query can't do this: WP_Query ignores it on singular queries.)
        if ( ! has_term( (int) $client->term_id, 'client', $post ) ) {
            $wp->query_vars = [
                'error'     => '404',
                'lp_client' => $client->slug,
                'post_type' => 'landing_page',
                'post__in'  => [ 0 ], // Match nothing.
            ];
            return;
        }

        $wp->query_vars['lp_client'] = $client->slug;
        $wp->query_vars['name']      = $post->post_name;
        $wp->query_vars['post_type'] = 'landing_page';
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
        $args = array_merge( [ 'post_type' => 'landing_page', 'p' => (int) $post->ID ], $args, [ 'preview' => 'true' ] );

        return add_query_arg( array_map( 'rawurlencode', $args ), home_url( '/' ) );
    }
}
