<?php
namespace LPManager\admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;
use LPManager\templates\Template_Manager;

class Plugin_Settings {
    public static function init() {
        add_action( 'carbon_fields_register_fields', [ __CLASS__, 'register_settings' ] );
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_styles']);
        add_action( 'admin_notices', [ __CLASS__, 'render_missing_template_notice' ] );
        add_action( 'admin_notices', [ __CLASS__, 'render_empty_server_key_notice' ] );
        add_action( 'admin_post_lpmanager_dismiss_server_key_notice', [ __CLASS__, 'handle_dismiss_server_key_notice' ] );
        add_filter( 'carbon_fields_should_delete_field_value_on_save', [ __CLASS__, 'guard_raw_html_delete' ], 10, 2 );
        add_filter( 'carbon_fields_should_save_field_value', [ __CLASS__, 'guard_raw_html_save' ], 10, 3 );
        add_action( 'admin_post_lpmanager_approve_additional_scripts', [ __CLASS__, 'handle_approve_additional_scripts' ] );
        add_action( 'admin_notices', [ __CLASS__, 'render_additional_scripts_notice' ] );
        add_action( 'lpmanager_landing_page_footer', [ __CLASS__, 'print_additional_scripts' ] );
		add_action('carbon_fields_theme_options_container_saved', function() {
            $fields = [
                'lpmanager_google_maps_api_key',
                'lpmanager_brandfetch_api_key'
            ];
            foreach ($fields as $field) {
                $value = carbon_get_theme_option($field);
                // If the value is all asterisks or matches the masked pattern, revert to previous value
                if ($value && preg_match('/^\\*+.{0,6}$/', $value)) {
                    // Get the previous value from the database directly
                    $option = get_option('carbon_fields_theme_options_container');
                    if (isset($option[$field . '_previous'])) {
                        $real_value = $option[$field . '_previous'];
                        carbon_set_theme_option($field, $real_value);
                    } else {
                        // If no previous value, just delete
                        carbon_set_theme_option($field, '');
                    }
                } else {
                    // Save the current value as previous for next time
                    $option = get_option('carbon_fields_theme_options_container');
                    $option[$field . '_previous'] = $value;
                    update_option('carbon_fields_theme_options_container', $option);
                }
            }
        });
    }

	public static function enqueue_admin_styles( $hook ) {
        // Load only on plugin settings / Carbon theme options page under lp_dashboard
		if ( strpos( $hook, 'lp_dashboard' ) === false && strpos( $hook, 'crb_carbon_fields' ) === false ) {
            return;
        }

        wp_enqueue_style(
            'lpmanager-admin-styles',
            LP_MANAGER_PLUGIN_URL . 'assets/css/admin.css',
            [],
            '1.0.0'
        );
        
        wp_enqueue_script(
        	'lpmanager-admin-js', 
            LP_MANAGER_PLUGIN_URL . 'assets/js/admin.js', 
            ['jquery'], 
            '1.0', 
            true
        );
        
        wp_localize_script('lpmanager-admin-js', 'lpmanager_vars', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('lpmanager_nonce'),
            'upload_nonce' => wp_create_nonce( \LPManager\templates\Template_Manager::UPLOAD_NONCE ),
        ]);
    }
    
    public static function register_settings() {
        $templates = Template_Manager::get_templates();

        // Define the base URL for template images
        $template_url = plugin_dir_url(__DIR__ . '/../assets/templates/previews/');

        if (empty($templates)) {
            $templates['none'] = [
                'name' => 'No templates found',
                'description' => '',
                'image_url' => $template_url . 'default-preview.png',
            ];
        }

        // Prepare options array for radio_image field (HTML strings)
        $template_options = [];
        foreach ($templates as $slug => $info) {
            $template_options[$slug] = '<div style="display:flex; flex-direction: column; align-items:center; margin-bottom: 15px;">
                <strong style="margin-bottom: 6px; font-size: 16px;">' . esc_html($info['name']) . '</strong>
                <img src="' . esc_url($info['image_url']) . '" alt="' . esc_attr($info['name']) . '" style="width:150px; height:auto; border:1px solid #ccc; margin-bottom: 6px;" />
                <p style="text-align:center; font-size: 12px; color: #666; margin: 0;">' . esc_html($info['description']) . '</p>
            </div>';
        }

        $is_premium = function_exists( 'lpmanager_is_premium' ) && lpmanager_is_premium();

        $tab_templates = [
            Field::make( 'html', 'template_selector_label', __( 'Select Landing Page Template', 'landing-page-manager' ) )
                ->set_html( '<p>Select the template you want all landing pages to use. Visual previews shown below.</p>' ),
            Field::make( 'html', 'lpmanager_template_selector_and_upload', __( 'Landing Page Templates', 'landing-page-manager' ) )
                ->set_html( self::render_template_selector_and_upload() )
                ->set_help_text( __( 'Select a template or upload a new one.', 'landing-page-manager' ) ),
            Field::make( 'hidden', 'lpmanager_active_template' )
                ->set_label( false )
                ->set_default_value( Template_Manager::DEFAULT_TEMPLATE ),
        ];

        if ( $is_premium ) {
            $tab_conversion = [
                Field::make( 'html', 'lpmanager_tracking_intro', __( 'Tracking options', 'landing-page-manager' ) )
                    ->set_html( self::render_tracking_toggles_and_intro() ),
                Field::make( 'checkbox', 'lpmanager_track_page_views', __( 'Track page views', 'landing-page-manager' ) )
                    ->set_default_value( true )
                    ->set_help_text( __( 'Count each time a landing page is loaded. Powers Total Page Views and visit counts.', 'landing-page-manager' ) ),
                Field::make( 'checkbox', 'lpmanager_track_link_clicks', __( 'Track link clicks (CTR)', 'landing-page-manager' ) )
                    ->set_default_value( true )
                    ->set_help_text( __( 'Track clicks on links and buttons for click-through rate and link performance.', 'landing-page-manager' ) ),
                Field::make( 'text', 'lpmanager_conversion_tracking_id', __( 'Conversion Tracking ID (optional)', 'landing-page-manager' ) )
                    ->set_help_text( __( 'Optional: external tracking ID (e.g. Google Analytics, Facebook Pixel). Internal conversion data is stored on your site regardless.', 'landing-page-manager' ) ),
                Field::make( 'separator', 'behavior_tracking_separator', __( 'Behavior & engagement', 'landing-page-manager' ) ),
                Field::make( 'checkbox', 'lpmanager_track_time_on_page', __( 'Track time on page / abandonment', 'landing-page-manager' ) )
                    ->set_default_value( true )
                    ->set_help_text( __( 'Record how long visitors stay. Helps identify page abandonment and engagement.', 'landing-page-manager' ) ),
                Field::make( 'checkbox', 'lpmanager_track_scroll_depth', __( 'Track scroll depth', 'landing-page-manager' ) )
                    ->set_default_value( true )
                    ->set_help_text( __( 'Record how far visitors scroll (25%, 50%, 75%, 100%). See if users reach key sections.', 'landing-page-manager' ) ),
                Field::make( 'checkbox', 'lpmanager_track_heatmap', __( 'Track mouse position (heatmap)', 'landing-page-manager' ) )
                    ->set_default_value( true )
                    ->set_help_text( __( 'Record mouse movement in aggregate to see where visitors look. Similar to Hotjar heatmaps.', 'landing-page-manager' ) ),
                Field::make( 'checkbox', 'lpmanager_track_rage_clicks', __( 'Track rage / repeated clicks', 'landing-page-manager' ) )
                    ->set_default_value( true )
                    ->set_help_text( __( 'Detect repeated clicks in the same area (frustration). Find broken or confusing elements.', 'landing-page-manager' ) ),
                Field::make( 'checkbox', 'lpmanager_track_outbound_clicks', __( 'Track outbound link clicks', 'landing-page-manager' ) )
                    ->set_default_value( true )
                    ->set_help_text( __( 'Track clicks that leave your site. Separate from internal link clicks.', 'landing-page-manager' ) ),
                Field::make( 'checkbox', 'lpmanager_track_cta_visibility', __( 'Track CTA visibility', 'landing-page-manager' ) )
                    ->set_default_value( true )
                    ->set_help_text( __( 'Record when call-to-action buttons enter the viewport. Measure CTA exposure.', 'landing-page-manager' ) ),
                Field::make( 'checkbox', 'lpmanager_track_form_interactions', __( 'Track form interactions', 'landing-page-manager' ) )
                    ->set_default_value( true )
                    ->set_help_text( __( 'Track form focus, blur, and submit attempts. See form engagement and drop-off.', 'landing-page-manager' ) ),
            ];
        }

        $tab_api = [
            Field::make( 'html', 'lpmanager_google_keys_intro' )
                ->set_html(
                    '<p>' . esc_html__( 'Google bills usage to the billing account linked to the Google Cloud project that owns each key, and use of these keys is subject to the Google Maps Platform Terms of Service. In the Google Cloud console, set API quotas for the project and a budget alert on its billing account.', 'landing-page-manager' ) . '</p>'
                ),
            Field::make( 'text', 'lpmanager_google_maps_api_key', __( 'Google Maps browser key', 'landing-page-manager' ) )
                ->set_help_text( __( 'Used in visitors\' browsers to show maps (Maps JavaScript API). It is visible in page source, so in Google Cloud restrict it to websites (HTTP referrers) covering this site and every client subdomain, for example https://example.com/* and https://*.example.com/*, and to the Maps JavaScript API. Save a Google Maps server key below before you restrict this key.', 'landing-page-manager' ) ),
            Field::make( 'text', 'lpmanager_google_server_api_key', __( 'Google Maps server key', 'landing-page-manager' ) )
                ->set_help_text( __( 'Used by this site\'s server to look up client addresses and fetch reviews. It needs the Places API (the original "Places API", not "Places API (New)"). Restrict it to your server\'s IP address and to that API. If left empty, the browser key is used, which only works while the browser key has no website restriction.', 'landing-page-manager' ) ),
            Field::make( 'text', 'lpmanager_brandfetch_api_key', __( 'Brandfetch API key', 'landing-page-manager' ) )
                ->set_help_text( __( 'Used to fetch client brand colors. Usage is billed to the Brandfetch account that owns this key.', 'landing-page-manager' ) ),
            self::additional_scripts_field(),
        ];

        $tab_updates = [
            Field::make( 'html', 'lpmanager_update_source_info' )
                ->set_html(
                    '<p>' . esc_html__( 'Updates come only from Duppins Technology\'s official GitHub releases of Landing Page Manager and are checked automatically. To check now, go to Dashboard → Updates and click "Check again".', 'landing-page-manager' ) . '</p>'
                    . '<p>' . esc_html__( 'The "Plugin update source" setting from earlier versions has been removed; WordPress.org is no longer used for updates.', 'landing-page-manager' ) . '</p>'
                ),
        ];

        $container = Container::make( 'theme_options', __( 'Settings', 'landing-page-manager' ) )
            ->set_page_parent( 'lp_dashboard' )
            ->add_tab( __( 'Templates', 'landing-page-manager' ), $tab_templates );
        // Pro 1.0.4+ shows the tracking toggles on its own Settings screen (same stored options).
        // Only Pro before 1.0.4 keeps its tracking options here; later Pro versions have their own Tracking
        // screen, and without Pro the Tracking menu item explains how to get it.
        if ( $is_premium && ! function_exists( 'lpmanager_pro_tracking_options' ) ) {
            $container->add_tab( __( 'Conversion Tracking', 'landing-page-manager' ), $tab_conversion );
        }
        $container
            ->add_tab( __( 'API Keys', 'landing-page-manager' ), $tab_api )
            ->add_tab( __( 'Updates', 'landing-page-manager' ), $tab_updates );
    }

    public static function render_tracking_toggles_and_intro() {
        $intro = '<p>' . esc_html__( 'Choose what to track on landing pages. All data is stored on your site; no external services are required.', 'landing-page-manager' ) . '</p>';
        $intro .= '<p class="lp-tracking-toggles" style="margin-bottom: 14px;">';
        $intro .= '<button type="button" class="button lp-tracking-enable-all" style="margin-right: 8px;">' . esc_html__( 'Enable all', 'landing-page-manager' ) . '</button>';
        $intro .= '<button type="button" class="button lp-tracking-disable-all">' . esc_html__( 'Disable all', 'landing-page-manager' ) . '</button>';
        $intro .= '</p>';
        $intro .= '<script>
(function() {
    var toggles = document.querySelector(".lp-tracking-toggles");
    if (!toggles) return;
    var form = toggles.closest("form");
    if (!form) return;
    var selector = "input[type=checkbox][name*=\"lpmanager_track_\"]";
    function allCheckboxes() { return form.querySelectorAll(selector); }
    toggles.querySelector(".lp-tracking-enable-all").addEventListener("click", function() {
        allCheckboxes().forEach(function(cb) { cb.checked = true; });
    });
    toggles.querySelector(".lp-tracking-disable-all").addEventListener("click", function() {
        allCheckboxes().forEach(function(cb) { cb.checked = false; });
    });
})();
</script>';
        return $intro;
    }

    /**
     * Warn admins when the active template's folder is gone, because every landing page then
     * 404s, and when any template other than the starters still exists only inside the plugin
     * folder, where the next plugin update would delete it (updating is blocked until then).
     */
    public static function render_missing_template_notice() {
        if ( ! current_user_can( 'manage_options' ) || ! function_exists( 'carbon_get_theme_option' ) ) {
            return;
        }
        $notices = [];
        $slug    = sanitize_title( (string) carbon_get_theme_option( 'lpmanager_active_template' ) );
        // Runs on every admin screen: one file check plus one scan of the plugin's template folders.
        if ( '' !== $slug && ! Template_Manager::template_exists( $slug ) ) {
            $notices[] = [
                'notice-error',
                sprintf(
                    /* translators: 1: template slug, 2: folder path */
                    __( 'Landing Page Manager: the active template "%1$s" is missing, so landing pages are showing a 404. Put its folder in %2$s (as %2$s%1$s/), upload it again, or choose another template.', 'landing-page-manager' ),
                    $slug,
                    Template_Manager::display_path( Template_Manager::custom_dir() )
                ),
            ];
        }
        $pending = array_keys( Template_Manager::pending_templates() );
        if ( $pending ) {
            $notices[] = [ 'notice-warning', 'Landing Page Manager: ' . Template_Manager::pending_templates_message( $pending ) ];
        }
        foreach ( $notices as $notice ) {
            printf(
                '<div class="notice %s"><p>%s <a href="%s">%s</a></p></div>',
                esc_attr( $notice[0] ),
                esc_html( $notice[1] ),
                // Carbon Fields' page slug for the "Settings" theme-options container.
                esc_url( admin_url( 'admin.php?page=crb_carbon_fields_container_settings.php' ) ),
                esc_html__( 'Go to Settings → Templates', 'landing-page-manager' )
            );
        }
    }

    /**
     * Warn admins on the plugin's screens when a browser key is set but no server key.
     * The server then falls back to the browser key, which only works while that key
     * (visible in every page's HTML) has no website restriction.
     */
    public static function render_empty_server_key_notice() {
        if ( ! current_user_can( 'manage_options' ) || ! function_exists( 'carbon_get_theme_option' ) ) {
            return;
        }
        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        if ( ! $screen ) {
            return;
        }
        $on_plugin_screen = 'landing_page' === $screen->post_type
            || 'client' === $screen->taxonomy
            || false !== strpos( $screen->id, 'lp_dashboard' )
            || false !== strpos( $screen->id, 'crb_carbon_fields_container_settings' );
        if ( ! $on_plugin_screen ) {
            return;
        }
        $browser_key = trim( (string) carbon_get_theme_option( 'lpmanager_google_maps_api_key' ) );
        $server_key  = trim( (string) carbon_get_theme_option( 'lpmanager_google_server_api_key' ) );
        if ( '' === $browser_key || '' !== $server_key ) {
            return;
        }
        if ( (int) get_user_meta( get_current_user_id(), self::SERVER_KEY_NOTICE_DISMISSED, true ) > time() ) {
            return; // Dismissed by this user for 30 days.
        }
        $dismiss_url = wp_nonce_url( admin_url( 'admin-post.php?action=lpmanager_dismiss_server_key_notice' ), 'lpmanager_dismiss_server_key_notice' );
        printf(
            '<div class="notice notice-warning"><p>%s <a href="%s">%s</a> &middot; <a href="%s">%s</a></p></div>',
            esc_html__( 'Landing Page Manager: add a Google Maps server key on the API Keys tab. Right now address lookups and Fetch Reviews use your browser key, which appears in every landing page\'s HTML and can\'t be restricted to your sites without breaking those lookups.', 'landing-page-manager' ),
            esc_url( admin_url( 'admin.php?page=crb_carbon_fields_container_settings.php' ) ),
            esc_html__( 'Go to Settings', 'landing-page-manager' ),
            esc_url( $dismiss_url ),
            esc_html__( 'Hide for 30 days', 'landing-page-manager' )
        );
    }

    /** User meta: time until which the empty-server-key notice is hidden for that user. */
    const SERVER_KEY_NOTICE_DISMISSED = 'lpmanager_server_key_notice_dismissed';

    /**
     * admin-post: hide the empty-server-key notice for the current user for 30 days (Odell, 10-16).
     */
    public static function handle_dismiss_server_key_notice() {
        check_admin_referer( 'lpmanager_dismiss_server_key_notice' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You are not allowed to do that.', 'landing-page-manager' ), 403 );
        }
        update_user_meta( get_current_user_id(), self::SERVER_KEY_NOTICE_DISMISSED, time() + 30 * DAY_IN_SECONDS );
        wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url() );
        exit;
    }

    /** Option holding a keyed hash (HMAC) of the approved Additional Scripts value. */
    const ADDITIONAL_SCRIPTS_HASH = 'lpmanager_additional_scripts_hash';

    /** Fields whose values are printed raw, so only users with unfiltered_html may change them. */
    const RAW_HTML_FIELDS = [ 'lpmanager_additional_scripts', 'header_tracking_code' ];

    /** Stored raw-HTML values captured before Carbon deletes them during a save, keyed by field object. */
    private static $raw_html_before_save = [];

    /**
     * "Additional Scripts" field. Read-only for users who may not post unfiltered HTML
     * (for example site admins on multisite), because the value is printed raw.
     *
     * @return \Carbon_Fields\Field\Field
     */
    private static function additional_scripts_field() {
        $field = Field::make( 'textarea', 'lpmanager_additional_scripts', __( 'Additional Scripts', 'landing-page-manager' ) )
            ->set_help_text( __( 'Tracking, conversion or chat scripts printed at the bottom of every landing page, once an administrator has saved or approved them. Only administrators (super admins on multisite) can change this.', 'landing-page-manager' ) );
        if ( ! current_user_can( 'unfiltered_html' ) ) {
            $field->set_attribute( 'readOnly', 'readonly' );
        }
        return $field;
    }

    /**
     * Keyed hash of an Additional Scripts value. Keyed with the site's auth salt so it can't be
     * forged by someone who can write options but doesn't know wp-config secrets.
     *
     * @param string $code Script value.
     * @return string
     */
    private static function additional_scripts_hmac( $code ) {
        return hash_hmac( 'sha256', (string) $code, wp_salt( 'auth' ) );
    }

    /**
     * Whether the stored Additional Scripts value is approved for printing.
     *
     * @param string $code Stored value.
     * @return bool
     */
    private static function additional_scripts_approved( $code ) {
        return '' !== trim( (string) $code )
            && hash_equals( (string) get_option( self::ADDITIONAL_SCRIPTS_HASH ), self::additional_scripts_hmac( $code ) );
    }

    /**
     * Stored raw value of a raw-HTML field, read straight from the database.
     *
     * @param \Carbon_Fields\Field\Field $field Field being saved.
     * @return string
     */
    private static function stored_raw_html_value( $field ) {
        if ( 'lpmanager_additional_scripts' === $field->get_base_name() ) {
            return (string) get_option( '_lpmanager_additional_scripts', '' );
        }
        $datastore = $field->get_datastore();
        $term_id   = ( $datastore && method_exists( $datastore, 'get_object_id' ) ) ? (int) $datastore->get_object_id() : 0;
        return $term_id ? (string) get_term_meta( $term_id, '_header_tracking_code', true ) : '';
    }

    /**
     * carbon_fields_should_delete_field_value_on_save: never let a user without unfiltered_html
     * clear a raw-HTML field (Carbon deletes before it saves). Covers the settings form, client
     * screens and Carbon's REST API, which all go through Field::save().
     *
     * @param bool                       $delete Default true.
     * @param \Carbon_Fields\Field\Field $field  Field being saved.
     * @return bool
     */
    public static function guard_raw_html_delete( $delete, $field ) {
        if ( ! in_array( $field->get_base_name(), self::RAW_HTML_FIELDS, true ) ) {
            return $delete;
        }
        // Field::save() deletes before the save filter runs, so remember the stored value now.
        self::$raw_html_before_save[ spl_object_hash( $field ) ] = self::stored_raw_html_value( $field );
        if ( ! current_user_can( 'unfiltered_html' ) ) {
            return false;
        }
        return $delete;
    }

    /**
     * carbon_fields_should_save_field_value: only users with unfiltered_html can change a raw-HTML
     * field. For Additional Scripts, a trusted save of a new value also approves it for printing.
     *
     * @param bool                       $save  Default true.
     * @param mixed                      $value Submitted value.
     * @param \Carbon_Fields\Field\Field $field Field being saved.
     * @return bool
     */
    public static function guard_raw_html_save( $save, $value, $field ) {
        $name = $field->get_base_name();
        if ( ! in_array( $name, self::RAW_HTML_FIELDS, true ) ) {
            return $save;
        }
        $value  = (string) $value;
        $key    = spl_object_hash( $field );
        // The value stored before this save (captured in guard_raw_html_delete, because Carbon has
        // already deleted it by now). Fall back to the database if delete was skipped.
        $stored = array_key_exists( $key, self::$raw_html_before_save ) ? self::$raw_html_before_save[ $key ] : self::stored_raw_html_value( $field );
        unset( self::$raw_html_before_save[ $key ] );

        if ( ! current_user_can( 'unfiltered_html' ) ) {
            if ( $value !== $stored ) {
                set_transient( 'lpmanager_raw_html_rejected_' . get_current_user_id(), $name, MINUTE_IN_SECONDS );
            }
            return false; // Keep whatever is stored.
        }

        if ( 'lpmanager_additional_scripts' === $name ) {
            if ( '' === trim( $value ) ) {
                delete_option( self::ADDITIONAL_SCRIPTS_HASH );
            } elseif ( $value !== $stored || self::additional_scripts_approved( $stored ) ) {
                // A new value typed by this trusted user, or the already-approved one.
                update_option( self::ADDITIONAL_SCRIPTS_HASH, self::additional_scripts_hmac( $value ), false );
            }
            // Otherwise the stored value arrived some other way: it stays unapproved until a
            // trusted user approves it explicitly (render_additional_scripts_notice).
        }
        return $save;
    }

    /**
     * Notices: a rejected raw-HTML change, and an Additional Scripts value awaiting approval.
     */
    public static function render_additional_scripts_notice() {
        $key      = 'lpmanager_raw_html_rejected_' . get_current_user_id();
        $rejected = get_transient( $key );
        if ( $rejected ) {
            delete_transient( $key );
            $label = ( 'header_tracking_code' === $rejected ) ? __( 'Header Tracking Code', 'landing-page-manager' ) : __( 'Additional Scripts', 'landing-page-manager' );
            $message = sprintf(
                /* translators: %s: field name */
                __( 'Landing Page Manager: your %s change was not saved. Only administrators (super admins on multisite) can change it.', 'landing-page-manager' ),
                $label
            );
            echo '<div class="notice notice-error"><p>' . esc_html( $message ) . '</p></div>';
        }

        $approved_key = 'lpmanager_additional_scripts_approved_' . get_current_user_id();
        if ( get_transient( $approved_key ) ) {
            delete_transient( $approved_key );
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Landing Page Manager: Additional Scripts approved. They now print on landing pages (purge your page cache).', 'landing-page-manager' ) . '</p></div>';
        }

        // Same capabilities as the approve handler, so only users who can approve see the code.
        if ( ! current_user_can( 'unfiltered_html' ) || ! current_user_can( 'manage_options' ) || ! function_exists( 'carbon_get_theme_option' ) ) {
            return;
        }
        $code = (string) get_option( '_lpmanager_additional_scripts', '' );
        if ( '' === trim( $code ) || self::additional_scripts_approved( $code ) ) {
            return;
        }
        // Shown on the plugin's screens and the WP Dashboard, so a lost approval (salt rotation,
        // site migration) is noticed even by admins who don't open Settings.
        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        if ( ! $screen ) {
            return;
        }
        $relevant = 'dashboard' === $screen->id
            || 'landing_page' === $screen->post_type
            || 'client' === $screen->taxonomy
            || false !== strpos( $screen->id, 'lp_dashboard' )
            || false !== strpos( $screen->id, 'crb_carbon_fields_container_settings' );
        if ( ! $relevant ) {
            return;
        }
        // Bind the approval to the value being shown, so a swap before the click isn't approved.
        $approve_url = wp_nonce_url(
            add_query_arg(
                [
                    'action' => 'lpmanager_approve_additional_scripts',
                    'h'      => self::additional_scripts_hmac( $code ),
                ],
                admin_url( 'admin-post.php' )
            ),
            'lpmanager_approve_additional_scripts'
        );
        printf(
            '<div class="notice notice-warning"><p>%s</p><pre tabindex="0" aria-label="%s" style="max-height:12em;overflow:auto;background:#f6f7f7;padding:8px;white-space:pre-wrap;">%s</pre><p><a class="button button-primary" href="%s">%s</a> <a href="%s">%s</a></p></div>',
            esc_html__( 'Landing Page Manager: these Additional Scripts are not printed on landing pages until an administrator approves them. This is expected after updating to this version, after a site migration or a change of security keys, or when the code was saved another way. Before approving, remove any tag that is already in a client\'s Header Tracking Code, or conversions are counted twice. Approve only code you trust.', 'landing-page-manager' ),
            esc_attr__( 'Additional Scripts code', 'landing-page-manager' ),
            esc_html( $code ),
            esc_url( $approve_url ),
            esc_html__( 'Approve Additional Scripts', 'landing-page-manager' ),
            esc_url( admin_url( 'admin.php?page=crb_carbon_fields_container_settings.php' ) ),
            esc_html__( 'Edit in Settings → API Keys', 'landing-page-manager' )
        );
    }

    /**
     * admin-post: approve the stored Additional Scripts value (explicit review by a trusted user).
     */
    public static function handle_approve_additional_scripts() {
        check_admin_referer( 'lpmanager_approve_additional_scripts' );
        if ( ! current_user_can( 'unfiltered_html' ) || ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You are not allowed to approve scripts.', 'landing-page-manager' ), 403 );
        }
        $code     = (string) get_option( '_lpmanager_additional_scripts', '' );
        $reviewed = isset( $_GET['h'] ) ? sanitize_text_field( wp_unslash( $_GET['h'] ) ) : '';
        if ( '' !== trim( $code ) && '' !== $reviewed && hash_equals( self::additional_scripts_hmac( $code ), $reviewed ) ) {
            update_option( self::ADDITIONAL_SCRIPTS_HASH, self::additional_scripts_hmac( $code ), false );
            set_transient( 'lpmanager_additional_scripts_approved_' . get_current_user_id(), 1, MINUTE_IN_SECONDS );
        }
        // If the value changed since it was shown, nothing is approved; the notice shows the new value.
        wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'admin.php?page=crb_carbon_fields_container_settings.php' ) );
        exit;
    }

    /**
     * Print Additional Scripts at the bottom of landing pages (lpmanager_landing_page_footer),
     * but only the exact value an administrator saved or approved.
     */
    public static function print_additional_scripts() {
        if ( ! is_singular( 'landing_page' ) || ! function_exists( 'carbon_get_theme_option' ) ) {
            return;
        }
        $code = (string) carbon_get_theme_option( 'lpmanager_additional_scripts' );
        if ( ! self::additional_scripts_approved( $code ) ) {
            return;
        }
        echo "\n" . $code . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- raw by design; only an administrator-approved value is printed.
    }

    public static function render_template_selector_and_upload() {
        $templates = Template_Manager::get_templates();
        //$active_template = get_option('lpmanager_active_template');
        $active_template = carbon_get_theme_option('lpmanager_active_template');

        $html = '<h2 style="margin-bottom: 10px;">Landing Page Templates</h2>';
        $html .= '<div class="lpmanager-template-grid">';

        if (empty($templates)) {
            $html .= '<div class="lpmanager-template-card empty">';
            $html .= '<p>No templates found.</p>';
            $html .= '<img src="' . esc_url(includes_url('images/media/default.png')) . '" style="max-width: 150px; opacity: 0.3;">';
            $html .= '</div>';
        } else {
            foreach ($templates as $slug => $info) {
                $selected_class = ($slug === $active_template) ? ' selected' : '';
                $html .= '<div class="lpmanager-template-card' . esc_attr($selected_class) . '" data-template="' . esc_attr($slug) . '">';
                $html .= '<strong>' . esc_html($info['name']) . '</strong>';
                $html .= '<img src="' . esc_url($info['image_url']) . '" alt="' . esc_attr($info['name']) . '">';
                $html .= '<p>' . esc_html($info['description']) . '</p>';
                $html .= '</div>';
            }
        }

        // Upload box
        $html .= '<label class="lpmanager-template-upload">';
        $html .= '<strong>Upload New Template</strong>';
        $html .= '<input type="file" id="lpmanager_template_upload" name="lpmanager_template_upload" style="display:none;" accept=".zip">';
        $html .= '</label>';

        $html .= '</div>';

        $html .= '<p class="description" style="margin-top: 12px;">'
            . esc_html(
                sprintf(
                    /* translators: %s: folder path */
                    __( 'Uploaded and custom templates are kept in %s, outside the plugin folder, so plugin updates don\'t remove them. You can also copy a template folder there yourself.', 'landing-page-manager' ),
                    Template_Manager::display_path( Template_Manager::custom_dir() )
                )
            )
            . '</p>';

        // Hidden input to track selected template
        $html .= '<input type="hidden" id="lpmanager_active_template" name="lpmanager_active_template" value="' . esc_attr($active_template) . '">';

        return $html;
    }


}