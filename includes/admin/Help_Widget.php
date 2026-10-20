<?php
/**
 * Class Help_Widget
 *
 * Round "Help" button fixed to the bottom-right of Landing Page Manager's own
 * admin screens. It opens a small panel with notes about the current screen
 * and a link to that screen's section of the public user guide.
 *
 * The notes are kept in the plugin so they still show if the guide page is
 * unavailable. No options, AJAX or requests: the panel is static markup.
 *
 * @author Duppins Technology
 */
namespace LPManager\admin;

class Help_Widget {

    /**
     * Public user guide. Each screen opens it at its own fragment.
     * Override with the `lpmanager_user_guide_url` filter.
     */
    const GUIDE_URL = 'https://duppinstech.com/landing-page-manager/guide/';

    /** Admin page slugs this plugin owns (the `page` query argument). */
    const PAGE_DASHBOARD = 'lp_dashboard';
    const PAGE_ANALYTICS = 'lp_analytics';
    const PAGE_TRACKING  = 'lp_tracking';
    const PAGE_SETTINGS  = 'crb_carbon_fields_container_settings.php';

    /** Screen ids of this plugin's admin pages contain one of these. */
    const SCREEN_SLUGS = [ 'lp_dashboard', 'lp_analytics', 'lp_tracking', 'crb_carbon_fields_container_settings' ];

    /** Carbon Fields opens the Settings screen on its first tab. */
    const DEFAULT_SETTINGS_TAB = 'templates';

    /** Script/style handle and file basename. */
    const HANDLE = 'lpmanager-help-widget';

    /**
     * Stable guide fragment for each screen key. The fragments are element ids
     * on the published guide page, so do not rename them.
     *
     * @var array<string, string>
     */
    private static $fragments = [
        'lp_dashboard'                 => 'dashboard',
        'landing_page-list'            => 'landing-pages',
        'landing_page-edit'            => 'landing-page-editor',
        'client-list'                  => 'clients',
        'client-edit'                  => 'client-editor',
        'keyword-list'                 => 'keywords',
        'keyword-edit'                 => 'keyword-editor',
        'lp_analytics'                 => 'analytics',
        'lp_tracking'                  => 'tracking',
        'settings-templates'           => 'settings-templates',
        'settings-conversion-tracking' => 'settings-conversion-tracking',
        'settings-api-keys'            => 'settings-api-keys',
        'settings-updates'             => 'settings-updates',
    ];

    /**
     * Register admin hooks.
     */
    public static function init() {
        if ( ! is_admin() ) {
            return;
        }
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue' ] );
        add_action( 'admin_footer', [ __CLASS__, 'render' ] );
    }

    /**
     * Enqueue the widget CSS and footer JS on this plugin's screens only.
     */
    public static function enqueue() {
        if ( '' === self::screen_key() ) {
            return;
        }
        $css = 'assets/css/' . self::HANDLE . '.css';
        $js  = 'assets/js/' . self::HANDLE . '.js';
        wp_enqueue_style( self::HANDLE, LP_MANAGER_PLUGIN_URL . $css, [], (string) filemtime( LP_MANAGER_PLUGIN_PATH . $css ) );
        wp_enqueue_script( self::HANDLE, LP_MANAGER_PLUGIN_URL . $js, [], (string) filemtime( LP_MANAGER_PLUGIN_PATH . $js ), true );
    }

    /**
     * Print the Help button and panel in the admin footer.
     *
     * On Settings, every tab gets a section and the JS shows the one for the
     * selected tab, because Carbon Fields switches tabs without reloading.
     */
    public static function render() {
        $key = self::screen_key();
        if ( '' === $key ) {
            return;
        }

        $notes    = self::screen_notes();
        $sections = [ $key => '' ]; // Screen key => Carbon Fields tab label.
        if ( 0 === strpos( $key, 'settings-' ) ) {
            $sections = [];
            foreach ( self::settings_tab_labels() as $tab => $label ) {
                $sections[ 'settings-' . $tab ] = $label;
            }
        }
        ?>
        <div class="lpmanager-help">
            <div id="lpmanager-help-panel" class="lpmanager-help__panel" role="dialog" aria-modal="false" aria-labelledby="<?php echo esc_attr( 'lpmanager-help-title-' . $key ); ?>" hidden>
                <div class="lpmanager-help__bar">
                    <button type="button" class="button lpmanager-help__close"><?php esc_html_e( 'Close', 'landing-page-manager' ); ?></button>
                </div>
                <?php foreach ( $sections as $section_key => $tab_label ) : ?>
                    <div class="lpmanager-help__section" data-lpmanager-help-tab="<?php echo esc_attr( $tab_label ); ?>"<?php echo $section_key === $key ? '' : ' hidden'; ?>>
                        <h2 id="<?php echo esc_attr( 'lpmanager-help-title-' . $section_key ); ?>" class="lpmanager-help__title">
                            <?php
                            /* translators: %s: admin screen name, e.g. "Clients". */
                            echo esc_html( sprintf( __( 'Help for %s', 'landing-page-manager' ), $notes[ $section_key ]['title'] ) );
                            ?>
                        </h2>
                        <?php foreach ( $notes[ $section_key ]['notes'] as $sentence ) : ?>
                            <p><?php echo esc_html( $sentence ); ?></p>
                        <?php endforeach; ?>
                        <p class="lpmanager-help__actions">
                            <a class="button button-primary" href="<?php echo esc_url( self::screen_guide_url( $section_key ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open the user guide (opens in a new tab)', 'landing-page-manager' ); ?></a>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="lpmanager-help__toggle" aria-expanded="false" aria-controls="lpmanager-help-panel"><?php esc_html_e( 'Help', 'landing-page-manager' ); ?></button>
        </div>
        <?php
    }

    /**
     * Base URL of the public user guide, without any hash.
     *
     * @return string
     */
    public static function guide_url() {
        /**
         * Filters the base URL of the Landing Page Manager user guide.
         * Each screen's fragment is appended after this filter runs.
         *
         * @param string $url Guide URL. Default Help_Widget::GUIDE_URL.
         */
        $url = apply_filters( 'lpmanager_user_guide_url', self::GUIDE_URL );
        if ( ! is_string( $url ) || '' === trim( $url ) ) {
            $url = self::GUIDE_URL;
        }
        $hash = strpos( $url, '#' );
        return false === $hash ? trim( $url ) : trim( substr( $url, 0, $hash ) );
    }

    /**
     * Guide URL for a screen: the base URL plus that screen's fragment.
     *
     * @param string $screen_key Screen key; defaults to the current screen.
     * @return string Empty when the screen has no guide entry.
     */
    public static function screen_guide_url( $screen_key = '' ) {
        if ( '' === $screen_key ) {
            $screen_key = self::screen_key();
        }
        if ( ! isset( self::$fragments[ $screen_key ] ) ) {
            return '';
        }
        return self::guide_url() . '#' . self::$fragments[ $screen_key ];
    }

    /**
     * Whether the current admin screen belongs to this plugin.
     *
     * @return bool
     */
    public static function is_plugin_screen() {
        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        if ( ! $screen ) {
            return false;
        }
        if ( 'landing_page' === $screen->post_type || in_array( $screen->taxonomy, [ 'client', 'keyword' ], true ) ) {
            return true;
        }
        foreach ( self::SCREEN_SLUGS as $slug ) {
            if ( false !== strpos( $screen->id, $slug ) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Key for the current screen, e.g. "landing_page-list", "lp_analytics" or
     * "settings-api-keys".
     *
     * @return string Empty when this is not a plugin screen or it has no guide entry.
     */
    public static function screen_key() {
        if ( ! self::is_plugin_screen() ) {
            return '';
        }
        $screen = get_current_screen();
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only screen detection.
        $page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
        $tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
        // phpcs:enable

        $key = '';
        if ( self::PAGE_SETTINGS === $page ) {
            $key = 'settings-' . ( isset( self::settings_tab_labels()[ $tab ] ) ? $tab : self::DEFAULT_SETTINGS_TAB );
        } elseif ( in_array( $page, [ self::PAGE_DASHBOARD, self::PAGE_ANALYTICS, self::PAGE_TRACKING ], true ) ) {
            $key = $page;
        } elseif ( in_array( $screen->taxonomy, [ 'client', 'keyword' ], true ) ) {
            $key = $screen->taxonomy . ( 'term' === $screen->base ? '-edit' : '-list' );
        } elseif ( 'landing_page' === $screen->post_type && 'edit' === $screen->base ) {
            $key = 'landing_page-list';
        } elseif ( 'landing_page' === $screen->post_type && 'post' === $screen->base ) {
            $key = 'landing_page-edit';
        }

        return isset( self::$fragments[ $key ], self::screen_notes()[ $key ] ) ? $key : '';
    }

    /**
     * Title and two or three sentences for each screen, from its real controls.
     *
     * @return array<string, array{title: string, notes: string[]}>
     */
    public static function screen_notes() {
        $is_premium = function_exists( 'lpmanager_is_premium' ) && lpmanager_is_premium();
        $tabs       = self::settings_tab_labels();

        $notes = [
            'lp_dashboard'      => [
                'title' => __( 'Dashboard', 'landing-page-manager' ),
                'notes' => [
                    __( 'This dashboard counts your landing pages and clients and lists your most recent pages.', 'landing-page-manager' ),
                    __( 'Use Add Landing Page, All Landing Pages, or Clients to go straight to a common task.', 'landing-page-manager' ),
                    __( 'Page view and conversion totals appear only when Pro tracking is active.', 'landing-page-manager' ),
                ],
            ],
            'landing_page-list' => [
                'title' => __( 'Landing Pages', 'landing-page-manager' ),
                'notes' => [
                    __( 'Each row shows the page\'s client, keywords, date, and generated Landing Page URL.', 'landing-page-manager' ),
                    __( 'Use the All Clients and All Keywords filters, then Filter, to narrow the list. Quick Edit changes a page\'s client or keyword without opening the editor.', 'landing-page-manager' ),
                    __( 'Administrators can download the filtered list with Export CSV.', 'landing-page-manager' ),
                ],
            ],
            'landing_page-edit' => [
                'title' => __( 'Landing Page editor', 'landing-page-manager' ),
                'notes' => [
                    __( 'Enter a title, then choose Client Name and Keyword under Landing Page Details.', 'landing-page-manager' ),
                    __( 'Page phone (optional) replaces the client\'s Contact Phone on this page only.', 'landing-page-manager' ),
                    __( 'The other fields come from the active template. If required fields are missing when you publish or schedule a page, it is kept as a draft, and a notice lists each missing field and its tab until you fill them in.', 'landing-page-manager' ),
                    __( 'Preview opens on the main site, where you are logged in, not on the client subdomain.', 'landing-page-manager' ),
                ],
            ],
            'client-list'       => [
                'title' => __( 'Clients', 'landing-page-manager' ),
                'notes' => [
                    __( 'Enter the client\'s Name and details, then select Add New Client.', 'landing-page-manager' ),
                    __( 'The client slug is used as its subdomain, so DNS for that subdomain must point to this site. Each landing page opens only on its own client\'s subdomain, so changing a client\'s slug changes its pages\' addresses.', 'landing-page-manager' ),
                    __( 'Select a client\'s name in the list to open its editor.', 'landing-page-manager' ),
                ],
            ],
            'client-edit'       => [
                'title' => __( 'Client editor', 'landing-page-manager' ),
                'notes' => [
                    __( 'Branding & Identity, Contact Info, Tracking & Integration, and Social Media hold the settings this client\'s landing pages use.', 'landing-page-manager' ),
                    __( 'Client Logo, Contact Address, Contact Phone, and Client Domain are required. Latitude, Longitude, Google Place ID, and Google Search URL are read-only. Latitude and Longitude are refreshed from Google every 30 days. Brand colors come from Brandfetch, or from the logo when Brandfetch has none.', 'landing-page-manager' ),
                    __( 'Select Update to save the client.', 'landing-page-manager' ),
                ],
            ],
            'keyword-list'      => [
                'title' => __( 'Keywords', 'landing-page-manager' ),
                'notes' => [
                    __( 'Keywords label the campaign or search term each landing page targets.', 'landing-page-manager' ),
                    __( 'Enter a Name and select Add New Keyword, or select a keyword in the list to edit it.', 'landing-page-manager' ),
                ],
            ],
            'keyword-edit'      => [
                'title' => __( 'Keyword editor', 'landing-page-manager' ),
                'notes' => [
                    __( 'Change this keyword\'s Name, Slug, or Description.', 'landing-page-manager' ),
                    __( 'Select Update to save the keyword.', 'landing-page-manager' ),
                ],
            ],
            'lp_analytics'      => [
                'title' => __( 'Analytics', 'landing-page-manager' ),
                'notes' => has_action( 'lpmanager_render_analytics_page' )
                    ? [
                        __( 'Use the Client and Template filters to narrow the reports. The tabs show traffic, conversions, link performance, engagement, the heatmap and rage clicks.', 'landing-page-manager' ),
                        __( 'Under By Page, select a page title to see its numbers, scroll depth, top links and heatmap grid; Edit opens the page in the editor.', 'landing-page-manager' ),
                    ]
                    : ( $is_premium
                        ? [ Admin_Dashboard::pro_has_own_screens()
                            ? __( 'This version of Landing Page Manager Pro shows analytics under Landing Pages → Pro Analytics. Update Pro to 1.0.6 or later to see them here.', 'landing-page-manager' )
                            : __( 'This version of Landing Page Manager Pro can\'t show analytics on this screen. Update Pro to 1.0.6 or later to see them here.', 'landing-page-manager' ) ]
                        : [
                            __( 'Analytics needs the Landing Page Manager Pro add-on.', 'landing-page-manager' ),
                            __( 'Without Pro, this screen shows a notice and no reports, and the plugin collects no analytics.', 'landing-page-manager' ),
                        ] ),
            ],
            'lp_tracking'       => [
                'title' => __( 'Tracking', 'landing-page-manager' ),
                'notes' => has_action( 'lpmanager_render_tracking_page' )
                    ? [
                        __( 'What is tracked turns each kind of tracking on or off; conversion tracking (phone and call-button clicks) is always on.', 'landing-page-manager' ),
                        __( 'Who is tracked can exclude internal traffic and bots, honor Global Privacy Control and Do Not Track, and wait for visitor consent. Select Save Changes to apply.', 'landing-page-manager' ),
                    ]
                    : ( $is_premium
                        ? [ Admin_Dashboard::pro_has_own_screens()
                            ? __( 'This version of Landing Page Manager Pro keeps its tracking settings under Landing Pages → Pro Settings. Update Pro to 1.0.6 or later to manage them here.', 'landing-page-manager' )
                            : __( 'This version of Landing Page Manager Pro keeps its tracking options under Settings → Conversion Tracking (and, in Pro 1.0.3, Landing Pages → Pro Settings). Update Pro to 1.0.6 or later to manage them here.', 'landing-page-manager' ) ]
                        : [
                            __( 'Conversion tracking needs the Landing Page Manager Pro add-on.', 'landing-page-manager' ),
                            __( 'Without Pro, this screen shows a notice, and the plugin tracks nothing.', 'landing-page-manager' ),
                        ] ),
            ],
            'settings-templates' => [
                'title' => sprintf( '%1$s: %2$s', __( 'Settings', 'landing-page-manager' ), $tabs['templates'] ),
                'notes' => [
                    __( 'Select one card under Landing Page Templates to set the layout for every landing page on this site, then select Save Changes.', 'landing-page-manager' ),
                    __( 'Upload New Template installs a template ZIP that contains single-landing_page.php and screenshot.png.', 'landing-page-manager' ),
                    __( 'Uploaded and custom templates are kept in wp-content/lpmanager-templates/, so plugin updates do not remove them. You can also copy a template folder there yourself.', 'landing-page-manager' ),
                ],
            ],
            'settings-api-keys' => [
                'title' => sprintf( '%1$s: %2$s', __( 'Settings', 'landing-page-manager' ), $tabs['api-keys'] ),
                'notes' => [
                    __( 'The Google Maps browser key shows maps to visitors and is visible in page source, so restrict it to this site and its client subdomains.', 'landing-page-manager' ),
                    __( 'The Google Maps server key looks up addresses, refreshes map coordinates and fetches reviews with the original Places API. The Brandfetch API key fetches client brand colors.', 'landing-page-manager' ),
                    __( 'Saved keys show as dots. To change one, clear the field, paste the new key, then select Save Changes. Leave a field untouched to keep its saved key.', 'landing-page-manager' ),
                    __( 'Additional Scripts print on landing pages only after an administrator who can post unfiltered HTML saves or approves them.', 'landing-page-manager' ),
                ],
            ],
            'settings-updates'  => [
                'title' => sprintf( '%1$s: %2$s', __( 'Settings', 'landing-page-manager' ), $tabs['updates'] ),
                'notes' => [
                    __( 'Updates come from Duppins Technology\'s official GitHub releases and are checked automatically.', 'landing-page-manager' ),
                    __( 'This tab has no update button. To check now, go to Dashboard → Updates and select Check again.', 'landing-page-manager' ),
                ],
            ],
        ];

        if ( isset( $tabs['conversion-tracking'] ) ) {
            $notes['settings-conversion-tracking'] = [
                'title' => sprintf( '%1$s: %2$s', __( 'Settings', 'landing-page-manager' ), $tabs['conversion-tracking'] ),
                'notes' => [
                    __( 'Each checkbox turns on one kind of tracking, such as page views, link clicks, scroll depth, or form interactions.', 'landing-page-manager' ),
                    __( 'Conversion Tracking ID (optional) holds an external ID such as Google Analytics. Internal conversion data is stored on your site either way.', 'landing-page-manager' ),
                ],
            ];
        }

        return $notes;
    }

    /**
     * Settings tabs present on this site, keyed by tab slug, valued by the
     * label Carbon Fields prints. Mirrors Plugin_Settings::register_settings().
     *
     * @return array<string, string>
     */
    private static function settings_tab_labels() {
        $tabs = [ 'templates' => __( 'Templates', 'landing-page-manager' ) ];
        // Only Pro before 1.0.4 keeps its tracking options in a Settings tab (see Plugin_Settings).
        if ( function_exists( 'lpmanager_is_premium' ) && lpmanager_is_premium() && ! function_exists( 'lpmanager_pro_tracking_options' ) ) {
            $tabs['conversion-tracking'] = __( 'Conversion Tracking', 'landing-page-manager' );
        }
        $tabs['api-keys'] = __( 'API Keys', 'landing-page-manager' );
        $tabs['updates']  = __( 'Updates', 'landing-page-manager' );
        return $tabs;
    }
}
