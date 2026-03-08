<?php
/**
 * Class Admin_Dashboard
 * 
 * Handles the 'client' taxonomy for Landing Pages,
 * including term meta, admin columns, and admin UI customizations.
 */
namespace LPManager\admin;

class Admin_Dashboard {

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'register_menu_page' ] );
        add_filter( 'parent_file', [ __CLASS__, 'keep_parent_menu_open_on_taxonomy' ] );
        add_filter( 'submenu_file', [ __CLASS__, 'highlight_taxonomy_submenu' ], 10, 2 );
        
		// Add dashboard widget on WP Dashboard screen
        add_action('wp_dashboard_setup', [ __CLASS__, 'add_dashboard_widgets' ]);

        // Add submenu for plugin dashboard
        add_action( 'admin_menu', [ __CLASS__, 'register_dashboard_page' ] );
        add_action( 'admin_menu', [ __CLASS__, 'add_dashboard_submenu' ] );
        
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_admin_assets' ] );
        add_action( 'admin_notices', [ __CLASS__, 'maybe_render_pro_banner' ], 5 );
    }

    /**
     * Show "Get Pro" banner at top of Landing Page Manager admin pages when not premium.
     */
    public static function maybe_render_pro_banner() {
        if ( function_exists( 'lpmanager_is_premium' ) && lpmanager_is_premium() ) {
            return;
        }
        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        if ( ! $screen ) {
            return;
        }
        $on_lp_pages = ( strpos( $screen->id, 'lp_dashboard' ) !== false || strpos( $screen->id, 'lp_analytics' ) !== false || strpos( $screen->id, 'crb_carbon_fields' ) !== false || ( isset( $screen->parent_base ) && $screen->parent_base === 'lp_dashboard' ) );
        if ( ! $on_lp_pages ) {
            return;
        }
        $get_pro_url = apply_filters( 'lpmanager_get_pro_url', '#' );
        ?>
        <div class="notice lp-pro-banner" style="margin: 0 0 20px 0; padding: 12px 20px; border-left-color: #2271b1; background: #f0f6fc; border-left-width: 4px;">
            <p style="margin: 0; display: flex; align-items: center; flex-wrap: wrap; gap: 12px;">
                <strong><?php esc_html_e( 'Get more features with Landing Page Manager Pro', 'landing-page-manager' ); ?></strong>
                <span style="color: #50575e;">— <?php esc_html_e( 'Analytics, conversion tracking, click-through rates, and behavior insights.', 'landing-page-manager' ); ?></span>
                <a href="<?php echo esc_url( $get_pro_url ); ?>" class="button button-primary" style="margin-left: auto;"><?php esc_html_e( 'Get Pro Now', 'landing-page-manager' ); ?></a>
            </p>
        </div>
        <?php
    }
	
    public static function enqueue_assets() {
        wp_enqueue_script(
            'lpmanager-dashboard',
            plugin_dir_url(__FILE__) . '../assets/js/dashboard.js',
            ['chart-js-handle'], // make sure Chart.js is a dependency
            '1.0.0',
            true
        );

        // Pass the REST API URL to the JS script
        wp_localize_script('lpmanager-dashboard', 'lpmanagerDashboard', [
            'apiUrl' => rest_url('lpmanager/v1/dashboard-data'),
            'nonce'  => wp_create_nonce('wp_rest'),
        ]);
    }
    
    public static function enqueue_admin_assets( $hook ) {
        $is_dashboard = ( strpos( $hook, 'lp_dashboard' ) !== false && strpos( $hook, 'lp_analytics' ) === false );
        $is_analytics = ( strpos( $hook, 'lp_analytics' ) !== false );

        wp_enqueue_style(
            'lp-dashboard-css',
            LP_MANAGER_PLUGIN_URL . 'assets/css/dashboard.css',
            [],
            '2.1.0'
        );

        if ( ! lpmanager_is_premium() && ( $is_dashboard || $is_analytics || strpos( $hook, 'crb_carbon_fields' ) !== false ) ) {
            wp_add_inline_style( 'lp-dashboard-css', '
                .toplevel_page_lp_dashboard .wp-submenu a[href*="page=lp_analytics"]::after { content: " — Get Pro"; color: #d63638; font-weight: 600; font-size: 11px; }
            ' );
        }

        if ( $is_analytics ) {
            wp_enqueue_style(
                'lp-analytics-css',
                LP_MANAGER_PLUGIN_URL . 'assets/css/analytics.css',
                [ 'lp-dashboard-css' ],
                '2.1.0'
            );
            if ( lpmanager_is_premium() ) {
                wp_enqueue_script( 'chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', [], '4.4.1', true );
                wp_enqueue_script(
                    'lp-analytics-js',
                    LP_MANAGER_PLUGIN_URL . 'assets/js/analytics.js',
                    [ 'chartjs' ],
                    '2.1.0',
                    true
                );
                wp_localize_script( 'lp-analytics-js', 'lpmanagerAnalytics', [
                    'apiUrl'        => rest_url( 'lpmanager/v1/analytics-data' ),
                    'nonce'         => wp_create_nonce( 'wp_rest' ),
                    'clientId'      => isset( $_GET['client_id'] ) ? absint( $_GET['client_id'] ) : 0,
                    'templateFilter'=> isset( $_GET['template'] ) ? sanitize_text_field( wp_unslash( $_GET['template'] ) ) : '',
                    'editBase'       => admin_url( 'post.php?post=REPLACE_ID&action=edit' ),
                    'adminUrl'       => admin_url( 'admin.php' ),
                ] );
            }
        }
    }
    
    public static function get_dashboard_data( $request ) {
        // Landing page counts
        $counts = wp_count_posts( 'landing_page' );
        $data['landing_pages'] = [
            'publish' => (int) $counts->publish,
            'draft'   => (int) $counts->draft,
            'pending' => (int) $counts->pending
        ];

        // Client taxonomy count (used outside charts)
        $clients_count = wp_count_terms( 'client', [ 'hide_empty' => false ] );
        $data['clients'] = is_wp_error( $clients_count ) ? 0 : (int) $clients_count;

        // Keywords taxonomy counts
        $terms = get_terms( [ 'taxonomy' => 'keyword', 'hide_empty' => false ] );
        $data['keywords'] = [];
        if ( is_wp_error( $terms ) ) {
            $terms = [];
        }
        foreach ( $terms as $term ) {
            $data['keywords'][] = [
                'label' => $term->name,
                'count' => $term->count
            ];
        }

        // Get all landing pages
        $pages = get_posts( [
            'post_type'      => 'landing_page',
            'posts_per_page' => -1
        ] );

        // Base data only; Pro plugin adds visits, conversions, summary via filter.
        $data['visits_per_page'] = [];
        $data['conversions_per_page'] = [];
        $data['keyword_count'] = count( $terms );
        $data['daily_visits'] = [];
        $data['daily_conversions'] = [];

        $data = apply_filters( 'lpmanager_dashboard_data', $data );

        return new \WP_REST_Response( $data, 200 );
    }

    public static function register_menu_page() {
        add_menu_page(
            'Landing Pages',
            'Landing Pages',
            'manage_options',
    		'lp_dashboard',
            '',  // No callback, CPT listing handles output
            'dashicons-media-document',
    		21
        );
		
        // Submenu: Dashboard (required for top-level menu to show submenu)
        add_submenu_page(
            'lp_dashboard',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'lp_dashboard',
            [ __CLASS__, 'render_dashboard_page' ]
        );
        
        // Submenu: Landing Pages CPT listing
        add_submenu_page(
            'lp_dashboard',
            'Landing Pages',
            'Landing Pages',
            'edit_posts',
            'edit.php?post_type=landing_page'
        );

        // Submenu: Clients taxonomy
        add_submenu_page(
            'lp_dashboard',
            'Clients',
            'Clients',
            'manage_options',
            'edit-tags.php?taxonomy=client&post_type=landing_page'
        );
        
        // Submenu: Keyword taxonomy
        add_submenu_page(
            'lp_dashboard',
            'Keywords',
            'Keywords',
            'manage_options',
            'edit-tags.php?taxonomy=keyword&post_type=landing_page'
        );

        // Submenu: Analytics (all users; Pro unlocks data; free shows CTA and "Get Pro" in menu)
        add_submenu_page(
            'lp_dashboard',
            __( 'Analytics', 'landing-page-manager' ),
            __( 'Analytics', 'landing-page-manager' ),
            'manage_options',
            'lp_analytics',
            [ __CLASS__, 'render_analytics_page' ]
        );
    }

    /**
     * Keep the Landing Pages parent menu expanded when on Clients or Keywords taxonomy screens.
     */
    public static function keep_parent_menu_open_on_taxonomy( $parent_file ) {
        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        if ( ! $screen ) {
            return $parent_file;
        }
        if ( $screen->id === 'edit-client' || $screen->id === 'edit-keyword' ) {
            return 'lp_dashboard';
        }
        return $parent_file;
    }

    /**
     * Highlight the correct submenu (Clients or Keywords) when on that taxonomy screen.
     */
    public static function highlight_taxonomy_submenu( $submenu_file, $parent_file ) {
        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        if ( ! $screen || $parent_file !== 'lp_dashboard' ) {
            return $submenu_file;
        }
        if ( $screen->id === 'edit-client' ) {
            return 'edit-tags.php?taxonomy=client&post_type=landing_page';
        }
        if ( $screen->id === 'edit-keyword' ) {
            return 'edit-tags.php?taxonomy=keyword&post_type=landing_page';
        }
        return $submenu_file;
    }
	
    public static function add_dashboard_widgets() {
        wp_add_dashboard_widget(
            'lp_dashboard_widget',
            __( 'Landing Page Manager Overview', 'landing-page-manager' ),
            [ __CLASS__, 'render_dashboard_widget' ]
        );
    }

    public static function render_dashboard_widget() {
        $counts = wp_count_posts( 'landing_page' );
        $total  = (int) ( $counts->publish ?? 0 ) + (int) ( $counts->draft ?? 0 ) + (int) ( $counts->pending ?? 0 );
        $clients = wp_count_terms( 'client', [ 'hide_empty' => false ] );
        $clients = is_wp_error( $clients ) ? 0 : (int) $clients;
        $dashboard_url = admin_url( 'admin.php?page=lp_dashboard' );
        ?>
        <p><?php echo esc_html( sprintf( __( 'You have %d landing pages and %d clients.', 'landing-page-manager' ), $total, $clients ) ); ?></p>
        <p>
            <a href="<?php echo esc_url( $dashboard_url ); ?>" class="button button-primary"><?php esc_html_e( 'Open Dashboard', 'landing-page-manager' ); ?></a>
            <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=landing_page' ) ); ?>" class="button"><?php esc_html_e( 'Add Landing Page', 'landing-page-manager' ); ?></a>
        </p>
        <?php
    }
    
    public static function register_dashboard_page() {
        // Redundant: Dashboard is already added as first submenu under lp_dashboard in register_menu_page().
        // This hook is kept for backwards compatibility but targets non-existent parent; safe to no-op or remove.
    }
    
    public static function render_settings_page() {
	    echo '<div class="wrap">';
	    echo '<h1>' . esc_html__( 'Landing Page Manager Settings', 'landing-page-manager' ) . '</h1>';
	    echo '<p>Use the settings below to configure global plugin behavior.</p>';
	    do_action( 'carbon_fields_container_lp_dashboard' ); // optionally trigger a custom hook if needed
	    echo '</div>';

    }
    
    public static function add_dashboard_submenu() {
        add_submenu_page(
            'edit.php?post_type=landing_page', // Parent slug (this attaches it to the Landing Pages menu)
            __( 'Dashboard', 'landing-page-manager' ), // Page title
            __( 'Dashboard', 'landing-page-manager' ), // Menu label
            'manage_options', // Capability
            'lp_dashboard', // Menu slug
            [ __CLASS__, 'render_dashboard_page' ] // Callback function
        );
    }

    public static function render_dashboard_page() {
        $landing_pages_count = wp_count_posts( 'landing_page' );
        $clients_count       = wp_count_terms( 'client', [ 'hide_empty' => false ] );
        $total_landing_pages = (int) ( $landing_pages_count->publish ?? 0 ) + (int) ( $landing_pages_count->draft ?? 0 ) + (int) ( $landing_pages_count->pending ?? 0 );
        $clients_count       = is_wp_error( $clients_count ) ? 0 : (int) $clients_count;
        $is_premium          = lpmanager_is_premium();

        $tracking = function_exists( 'lpmanager_tracking_options' ) ? lpmanager_tracking_options() : [];
        $total_views = 0;
        $total_conversions = 0;
        $total_clicks = 0;
        if ( $is_premium ) {
            $pages = get_posts( [ 'post_type' => 'landing_page', 'posts_per_page' => -1, 'post_status' => 'any' ] );
            foreach ( $pages as $p ) {
                if ( ! empty( $tracking['track_page_views'] ) ) {
                    $total_views += (int) get_post_meta( $p->ID, '_lpmanager_page_views', true );
                }
                if ( ! empty( $tracking['enable_conversion_tracking'] ) ) {
                    $total_conversions += (int) get_post_meta( $p->ID, '_lpmanager_conversions', true );
                }
                if ( ! empty( $tracking['track_link_clicks'] ) ) {
                    $total_clicks += (int) get_post_meta( $p->ID, '_lpmanager_total_clicks', true );
                }
            }
        }
        $ctr_conv = $total_views > 0 ? round( ( $total_conversions / $total_views ) * 100, 1 ) : 0;

        $recent_pages = get_posts( [
            'post_type'      => 'landing_page',
            'posts_per_page' => 5,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ] );

        $clients = get_terms( [ 'taxonomy' => 'client', 'hide_empty' => false ] );
        if ( is_wp_error( $clients ) ) {
            $clients = [];
        }
        $analytics_url = admin_url( 'admin.php?page=lp_analytics' );
        ?>
        <div class="wrap lp-dashboard lp-dashboard--overview">
            <header class="lp-dashboard__header">
                <h1 class="lp-dashboard__title"><?php esc_html_e( 'Landing Page Manager', 'landing-page-manager' ); ?></h1>
                <p class="lp-dashboard__subtitle"><?php esc_html_e( 'High-level overview of your landing pages and Google Ads campaigns.', 'landing-page-manager' ); ?></p>
            </header>

            <div class="lp-dashboard__actions">
                <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=landing_page' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Add Landing Page', 'landing-page-manager' ); ?></a>
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=landing_page' ) ); ?>" class="button"><?php esc_html_e( 'All Landing Pages', 'landing-page-manager' ); ?></a>
                <a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=client&post_type=landing_page' ) ); ?>" class="button"><?php esc_html_e( 'Clients', 'landing-page-manager' ); ?></a>
                <?php if ( $is_premium ) : ?>
                    <a href="<?php echo esc_url( $analytics_url ); ?>" class="button button-secondary lp-button--highlight"><?php esc_html_e( 'View Analytics', 'landing-page-manager' ); ?></a>
                <?php else : ?>
                    <a href="<?php echo esc_url( $analytics_url ); ?>" class="button"><?php esc_html_e( 'View Analytics (Pro)', 'landing-page-manager' ); ?></a>
                <?php endif; ?>
            </div>

            <div class="lp-stats lp-stats--hero">
                <div class="lp-stat-card lp-stat-card--pages">
                    <h3 class="lp-stat-card__label"><?php esc_html_e( 'Landing Pages', 'landing-page-manager' ); ?></h3>
                    <p class="lp-stat-card__value"><?php echo esc_html( (string) $total_landing_pages ); ?></p>
                </div>
                <div class="lp-stat-card lp-stat-card--clients">
                    <h3 class="lp-stat-card__label"><?php esc_html_e( 'Clients', 'landing-page-manager' ); ?></h3>
                    <p class="lp-stat-card__value"><?php echo esc_html( (string) $clients_count ); ?></p>
                </div>
                <?php
                $show_views = $is_premium && ! empty( $tracking['track_page_views'] );
                $show_conversions = $is_premium && ! empty( $tracking['enable_conversion_tracking'] );
                $show_clicks = $is_premium && ! empty( $tracking['track_link_clicks'] );
                $show_ctr = $show_conversions && $total_views > 0;
                ?>
                <?php if ( $show_views ) : ?>
                    <div class="lp-stat-card lp-stat-card--views">
                        <h3 class="lp-stat-card__label"><?php esc_html_e( 'Total Page Views', 'landing-page-manager' ); ?></h3>
                        <p class="lp-stat-card__value"><?php echo esc_html( number_format_i18n( $total_views ) ); ?></p>
                    </div>
                <?php endif; ?>
                <?php if ( $show_conversions ) : ?>
                    <div class="lp-stat-card lp-stat-card--conversions">
                        <h3 class="lp-stat-card__label"><?php esc_html_e( 'Conversions (e.g. calls)', 'landing-page-manager' ); ?></h3>
                        <p class="lp-stat-card__value"><?php echo esc_html( number_format_i18n( $total_conversions ) ); ?></p>
                    </div>
                <?php endif; ?>
                <?php if ( $show_clicks ) : ?>
                    <div class="lp-stat-card lp-stat-card--clicks">
                        <h3 class="lp-stat-card__label"><?php esc_html_e( 'Link Clicks', 'landing-page-manager' ); ?></h3>
                        <p class="lp-stat-card__value"><?php echo esc_html( number_format_i18n( $total_clicks ) ); ?></p>
                    </div>
                <?php endif; ?>
                <?php if ( $show_ctr ) : ?>
                    <div class="lp-stat-card lp-stat-card--ctr">
                        <h3 class="lp-stat-card__label"><?php esc_html_e( 'Conversion rate (CTR)', 'landing-page-manager' ); ?></h3>
                        <p class="lp-stat-card__value"><?php echo esc_html( $ctr_conv . '%' ); ?></p>
                        <p class="lp-stat-card__hint"><?php esc_html_e( 'Visitors who clicked call / converted', 'landing-page-manager' ); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ( ! empty( $clients ) ) : ?>
                <section class="lp-section lp-section--clients">
                    <h2 class="lp-section__title"><?php esc_html_e( 'Clients', 'landing-page-manager' ); ?></h2>
                    <div class="lp-client-cards">
                        <?php foreach ( $clients as $c ) :
                            $pages_for_client = get_posts( [
                                'post_type'      => 'landing_page',
                                'posts_per_page' => -1,
                                'post_status'    => 'publish',
                                'tax_query'      => [ [ 'taxonomy' => 'client', 'field' => 'term_id', 'terms' => $c->term_id ] ],
                            ] );
                            $client_views = 0;
                            $client_conversions = 0;
                            $client_clicks = 0;
                            if ( $is_premium ) {
                                foreach ( $pages_for_client as $p ) {
                                    if ( ! empty( $tracking['track_page_views'] ) ) {
                                        $client_views += (int) get_post_meta( $p->ID, '_lpmanager_page_views', true );
                                    }
                                    if ( ! empty( $tracking['enable_conversion_tracking'] ) ) {
                                        $client_conversions += (int) get_post_meta( $p->ID, '_lpmanager_conversions', true );
                                    }
                                    if ( ! empty( $tracking['track_link_clicks'] ) ) {
                                        $client_clicks += (int) get_post_meta( $p->ID, '_lpmanager_total_clicks', true );
                                    }
                                }
                            }
                            $client_ctr = $client_views > 0 ? round( ( $client_conversions / $client_views ) * 100, 1 ) : 0;
                            $client_show_stats = $is_premium && ( ! empty( $tracking['track_page_views'] ) || ! empty( $tracking['enable_conversion_tracking'] ) || ! empty( $tracking['track_link_clicks'] ) );
                            $client_url = add_query_arg( 'client_id', $c->term_id, $analytics_url );
                            ?>
                            <a href="<?php echo esc_url( $client_url ); ?>" class="lp-client-card">
                                <h3 class="lp-client-card__name"><?php echo esc_html( $c->name ); ?></h3>
                                <p class="lp-client-card__meta"><?php echo esc_html( sprintf( _n( '%d landing page', '%d landing pages', count( $pages_for_client ), 'landing-page-manager' ), count( $pages_for_client ) ) ); ?></p>
                                <?php if ( $client_show_stats ) : ?>
                                    <div class="lp-client-card__stats">
                                        <?php if ( ! empty( $tracking['track_page_views'] ) ) : ?>
                                            <span><?php echo esc_html( number_format_i18n( $client_views ) ); ?> <?php esc_html_e( 'views', 'landing-page-manager' ); ?></span>
                                        <?php endif; ?>
                                        <?php if ( ! empty( $tracking['enable_conversion_tracking'] ) ) : ?>
                                            <span><?php echo esc_html( number_format_i18n( $client_conversions ) ); ?> <?php esc_html_e( 'conversions', 'landing-page-manager' ); ?></span>
                                            <span><?php echo esc_html( $client_ctr ); ?>% <?php esc_html_e( 'rate', 'landing-page-manager' ); ?></span>
                                        <?php endif; ?>
                                        <?php if ( ! empty( $tracking['track_link_clicks'] ) ) : ?>
                                            <span><?php echo esc_html( number_format_i18n( $client_clicks ) ); ?> <?php esc_html_e( 'clicks', 'landing-page-manager' ); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="lp-client-card__action"><?php esc_html_e( 'View analytics →', 'landing-page-manager' ); ?></span>
                                <?php elseif ( $is_premium ) : ?>
                                    <span class="lp-client-card__action"><?php esc_html_e( 'View analytics →', 'landing-page-manager' ); ?></span>
                                <?php else : ?>
                                    <span class="lp-client-card__action"><?php esc_html_e( 'View analytics (Pro) →', 'landing-page-manager' ); ?></span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <section class="lp-section lp-section--recent">
                <h2 class="lp-section__title"><?php esc_html_e( 'Recent Landing Pages', 'landing-page-manager' ); ?></h2>
                <?php if ( empty( $recent_pages ) ) : ?>
                    <p class="lp-empty"><?php esc_html_e( 'No landing pages yet.', 'landing-page-manager' ); ?></p>
                <?php else : ?>
                    <ul class="lp-recent-list">
                        <?php foreach ( $recent_pages as $page ) :
                            $edit_link = get_edit_post_link( $page->ID );
                            ?>
                            <li>
                                <a href="<?php echo esc_url( $edit_link ); ?>"><?php echo esc_html( get_the_title( $page ) ); ?></a>
                                <span class="lp-recent-list__status"><?php echo esc_html( get_post_status( $page ) ); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>
        </div>
        <?php
    }

    public static function render_analytics_page() {
        if ( ! lpmanager_is_premium() ) {
            ?>
            <div class="wrap lp-analytics lp-analytics--upgrade">
                <div class="lp-upgrade-cta">
                    <h1 class="lp-upgrade-cta__title"><?php esc_html_e( 'Analytics', 'landing-page-manager' ); ?></h1>
                    <p class="lp-upgrade-cta__message"><?php esc_html_e( 'Analytics and conversion tracking are part of Landing Page Manager Pro. Upgrade to see page views, link clicks, conversion rates, and campaign performance.', 'landing-page-manager' ); ?></p>
                    <p class="lp-upgrade-cta__hint"><?php esc_html_e( 'Install and activate the Pro add-on to unlock this feature.', 'landing-page-manager' ); ?></p>
                </div>
            </div>
            <?php
            return;
        }
        $client_id = isset( $_GET['client_id'] ) ? absint( $_GET['client_id'] ) : 0;
        $clients = get_terms( [ 'taxonomy' => 'client', 'hide_empty' => false ] );
        if ( is_wp_error( $clients ) ) $clients = [];
        ?>
        <div class="wrap lp-analytics" id="lp-analytics-root">
            <header class="lp-analytics__header">
                <h1 class="lp-analytics__title"><?php esc_html_e( 'Analytics', 'landing-page-manager' ); ?></h1>
                <p class="lp-analytics__subtitle"><?php esc_html_e( 'Traffic, click-through rates, and campaign performance.', 'landing-page-manager' ); ?></p>
                <div class="lp-analytics__filters">
                    <?php if ( ! empty( $clients ) ) : ?>
                        <div class="lp-analytics__filter">
                            <label for="lp-client-filter"><?php esc_html_e( 'Client', 'landing-page-manager' ); ?></label>
                            <select id="lp-client-filter" class="lp-select">
                                <option value="0"><?php esc_html_e( 'All clients', 'landing-page-manager' ); ?></option>
                                <?php foreach ( $clients as $c ) : ?>
                                    <option value="<?php echo esc_attr( $c->term_id ); ?>" <?php selected( $client_id, $c->term_id ); ?>><?php echo esc_html( $c->name ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <div class="lp-analytics__filter">
                        <label for="lp-template-filter"><?php esc_html_e( 'Template', 'landing-page-manager' ); ?></label>
                        <select id="lp-template-filter" class="lp-select">
                            <option value=""><?php esc_html_e( 'All templates', 'landing-page-manager' ); ?></option>
                        </select>
                    </div>
                </div>
            </header>

            <div class="lp-tabs" role="tablist">
                <button type="button" class="lp-tabs__tab lp-tabs__tab--active" role="tab" id="tab-overview" aria-selected="true" aria-controls="panel-overview" data-tab="overview"><?php esc_html_e( 'Overview', 'landing-page-manager' ); ?></button>
                <button type="button" class="lp-tabs__tab" role="tab" id="tab-clients" aria-selected="false" aria-controls="panel-clients" data-tab="clients"><?php esc_html_e( 'By Client', 'landing-page-manager' ); ?></button>
                <button type="button" class="lp-tabs__tab" role="tab" id="tab-pages" aria-selected="false" aria-controls="panel-pages" data-tab="pages"><?php esc_html_e( 'By Page', 'landing-page-manager' ); ?></button>
                <button type="button" class="lp-tabs__tab" role="tab" id="tab-template" aria-selected="false" aria-controls="panel-template" data-tab="template"><?php esc_html_e( 'By Template', 'landing-page-manager' ); ?></button>
                <button type="button" class="lp-tabs__tab" role="tab" id="tab-links" aria-selected="false" aria-controls="panel-links" data-tab="links"><?php esc_html_e( 'Link Performance', 'landing-page-manager' ); ?></button>
                <button type="button" class="lp-tabs__tab" role="tab" id="tab-engagement" aria-selected="false" aria-controls="panel-engagement" data-tab="engagement"><?php esc_html_e( 'Engagement', 'landing-page-manager' ); ?></button>
                <button type="button" class="lp-tabs__tab" role="tab" id="tab-heatmap" aria-selected="false" aria-controls="panel-heatmap" data-tab="heatmap"><?php esc_html_e( 'Heatmap', 'landing-page-manager' ); ?></button>
                <button type="button" class="lp-tabs__tab" role="tab" id="tab-rage" aria-selected="false" aria-controls="panel-rage" data-tab="rage"><?php esc_html_e( 'Rage Clicks', 'landing-page-manager' ); ?></button>
            </div>

            <div id="panel-overview" class="lp-tab-panel lp-tab-panel--active" role="tabpanel" aria-labelledby="tab-overview">
                <div class="lp-stats lp-stats--hero lp-analytics-summary" id="lp-summary-cards"></div>
                <div class="lp-charts lp-charts--overview">
                    <div class="lp-chart-card"><canvas id="lp-chart-daily-visits"></canvas></div>
                    <div class="lp-chart-card"><canvas id="lp-chart-daily-conversions"></canvas></div>
                </div>
            </div>
            <div id="panel-clients" class="lp-tab-panel" role="tabpanel" aria-labelledby="tab-clients" hidden>
                <div class="lp-table-wrapper">
                    <table class="lp-table" id="lp-table-clients">
                        <thead><tr><th><?php esc_html_e( 'Client', 'landing-page-manager' ); ?></th><th><?php esc_html_e( 'Pages', 'landing-page-manager' ); ?></th><th><?php esc_html_e( 'Views', 'landing-page-manager' ); ?></th><th><?php esc_html_e( 'Conversions', 'landing-page-manager' ); ?></th><th><?php esc_html_e( 'Clicks', 'landing-page-manager' ); ?></th><th><?php esc_html_e( 'Conv. rate', 'landing-page-manager' ); ?></th></tr></thead>
                        <tbody id="lp-tbody-clients"></tbody>
                    </table>
                </div>
            </div>
            <div id="panel-pages" class="lp-tab-panel" role="tabpanel" aria-labelledby="tab-pages" hidden>
                <div class="lp-table-wrapper">
                    <table class="lp-table" id="lp-table-pages">
                        <thead><tr><th><?php esc_html_e( 'Page', 'landing-page-manager' ); ?></th><th><?php esc_html_e( 'Client', 'landing-page-manager' ); ?></th><th><?php esc_html_e( 'Template', 'landing-page-manager' ); ?></th><th><?php esc_html_e( 'Views', 'landing-page-manager' ); ?></th><th><?php esc_html_e( 'Conversions', 'landing-page-manager' ); ?></th><th><?php esc_html_e( 'Clicks', 'landing-page-manager' ); ?></th><th><?php esc_html_e( 'Conv. rate', 'landing-page-manager' ); ?></th></tr></thead>
                        <tbody id="lp-tbody-pages"></tbody>
                    </table>
                </div>
            </div>
            <div id="panel-template" class="lp-tab-panel" role="tabpanel" aria-labelledby="tab-template" hidden>
                <div class="lp-table-wrapper">
                    <table class="lp-table" id="lp-table-template">
                        <thead><tr><th><?php esc_html_e( 'Template', 'landing-page-manager' ); ?></th><th><?php esc_html_e( 'Pages', 'landing-page-manager' ); ?></th><th><?php esc_html_e( 'Views', 'landing-page-manager' ); ?></th><th><?php esc_html_e( 'Conversions', 'landing-page-manager' ); ?></th><th><?php esc_html_e( 'Clicks', 'landing-page-manager' ); ?></th><th><?php esc_html_e( 'Conv. rate', 'landing-page-manager' ); ?></th></tr></thead>
                        <tbody id="lp-tbody-template"></tbody>
                    </table>
                </div>
            </div>
            <div id="panel-links" class="lp-tab-panel" role="tabpanel" aria-labelledby="tab-links" hidden>
                <div id="lp-link-performance"></div>
            </div>
            <div id="panel-engagement" class="lp-tab-panel" role="tabpanel" aria-labelledby="tab-engagement" hidden>
                <div id="lp-engagement-content"></div>
            </div>
            <div id="panel-heatmap" class="lp-tab-panel" role="tabpanel" aria-labelledby="tab-heatmap" hidden>
                <div id="lp-heatmap-content"></div>
            </div>
            <div id="panel-rage" class="lp-tab-panel" role="tabpanel" aria-labelledby="tab-rage" hidden>
                <div id="lp-rage-content"></div>
            </div>

            <div class="lp-analytics-loading" id="lp-analytics-loading"><?php esc_html_e( 'Loading…', 'landing-page-manager' ); ?></div>
            <div class="lp-analytics-error" id="lp-analytics-error" hidden></div>
        </div>
        <?php
    }

}
