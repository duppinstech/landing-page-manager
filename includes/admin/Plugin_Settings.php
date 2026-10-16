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
        $get_pro_url = apply_filters( 'lpmanager_get_pro_url', '#' );

        $tab_templates = [
            Field::make( 'html', 'template_selector_label', __( 'Select Landing Page Template', 'landing-page-manager' ) )
                ->set_html( '<p>Select the template you want all landing pages to use. Visual previews shown below.</p>' ),
            Field::make( 'html', 'lpmanager_template_selector_and_upload', __( 'Landing Page Templates', 'landing-page-manager' ) )
                ->set_html( self::render_template_selector_and_upload() )
                ->set_help_text( __( 'Select a template or upload a new one.', 'landing-page-manager' ) ),
            Field::make( 'hidden', 'lpmanager_active_template' )
                ->set_label( false )
                ->set_default_value( 'whiterail-ai' ),
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
        } else {
            $tab_conversion = [
                Field::make( 'html', 'lpmanager_conversion_pro_locked', __( 'Unlock with Pro', 'landing-page-manager' ) )
                    ->set_html(
                        '<div class="lp-settings-pro-locked" style="background: #f0f6fc; border: 1px solid #c3c4c7; border-left: 4px solid #2271b1; padding: 16px 20px; margin: 0 0 16px 0;">' .
                        '<p style="margin: 0 0 12px 0; font-size: 14px;"><strong>' . esc_html__( 'Get Pro to unlock this section', 'landing-page-manager' ) . '</strong></p>' .
                        '<p style="margin: 0 0 14px 0; color: #50575e;">' . esc_html__( 'Conversion tracking, page views, link clicks, scroll depth, heatmaps, and more are available in Landing Page Manager Pro.', 'landing-page-manager' ) . '</p>' .
                        '<a href="' . esc_url( $get_pro_url ) . '" class="button button-primary">' . esc_html__( 'Get Pro Now', 'landing-page-manager' ) . '</a>' .
                        '</div>'
                    ),
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
            Field::make( 'textarea', 'lpmanager_additional_scripts', __( 'Additional Scripts', 'landing-page-manager' ) )
                ->set_help_text( __( 'Add any additional scripts (e.g., tracking or chat scripts) to load on all landing pages.', 'landing-page-manager' ) ),
        ];

        $tab_updates = [
            Field::make( 'html', 'lpmanager_update_source_info' )
                ->set_html(
                    '<p>' . esc_html__( 'Updates come only from Duppins Technology\'s official GitHub releases of Landing Page Manager and are checked automatically. To check now, click "Check for updates" under Landing Page Manager on the Plugins screen.', 'landing-page-manager' ) . '</p>'
                    . '<p>' . esc_html__( 'The "Plugin update source" setting from earlier versions has been removed; WordPress.org is no longer used for updates.', 'landing-page-manager' ) . '</p>'
                ),
        ];

        Container::make( 'theme_options', __( 'Settings', 'landing-page-manager' ) )
            ->set_page_parent( 'lp_dashboard' )
            ->add_tab( __( 'Templates', 'landing-page-manager' ), $tab_templates )
            ->add_tab( $is_premium ? __( 'Conversion Tracking', 'landing-page-manager' ) : __( 'Conversion Tracking (Pro)', 'landing-page-manager' ), $tab_conversion )
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
     * Warn admins when the active template's folder is gone (for example, an uploaded
     * template removed by a plugin update), because every landing page then 404s.
     */
    public static function render_missing_template_notice() {
        if ( ! current_user_can( 'manage_options' ) || ! function_exists( 'carbon_get_theme_option' ) ) {
            return;
        }
        $slug = sanitize_title( (string) carbon_get_theme_option( 'lpmanager_active_template' ) );
        // Runs on every admin screen, so check one file rather than scanning all templates.
        if ( '' === $slug || file_exists( LP_MANAGER_PLUGIN_PATH . 'includes/templates/' . $slug . '/single-landing_page.php' ) ) {
            return;
        }
        printf(
            '<div class="notice notice-error"><p>%s <a href="%s">%s</a></p></div>',
            esc_html(
                sprintf(
                    /* translators: %s: template slug */
                    __( 'Landing Page Manager: the active template "%s" is missing, so landing pages are showing a 404. It was probably removed by a plugin update. Upload it again, or choose another template.', 'landing-page-manager' ),
                    $slug
                )
            ),
            // Carbon Fields' page slug for the "Settings" theme-options container.
            esc_url( admin_url( 'admin.php?page=crb_carbon_fields_container_settings.php' ) ),
            esc_html__( 'Go to Settings → Templates', 'landing-page-manager' )
        );
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
        printf(
            '<div class="notice notice-warning"><p>%s <a href="%s">%s</a></p></div>',
            esc_html__( 'Landing Page Manager: no Google Maps server key is set, so address lookups and review fetching use the browser key. That only works while the browser key, which is visible in every landing page\'s HTML, has no website restriction. Add a server key, then restrict the browser key to your sites.', 'landing-page-manager' ),
            esc_url( admin_url( 'admin.php?page=crb_carbon_fields_container_settings.php' ) ),
            esc_html__( 'Go to Settings → API Keys', 'landing-page-manager' )
        );
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

        // Uploaded templates live inside the plugin folder until PLAN.md "Store uploaded templates
        // outside the plugin folder" lands, so a plugin update deletes them.
        $html .= '<p class="description" style="margin-top: 12px;"><strong>' . esc_html__( 'Important:', 'landing-page-manager' ) . '</strong> '
            . esc_html__( 'Uploaded templates are stored inside the plugin folder and are deleted when the plugin updates. Keep your template .zip and upload it again after each update.', 'landing-page-manager' )
            . '</p>';

        // Hidden input to track selected template
        $html .= '<input type="hidden" id="lpmanager_active_template" name="lpmanager_active_template" value="' . esc_attr($active_template) . '">';

        return $html;
    }


}