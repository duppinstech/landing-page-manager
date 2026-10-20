<?php
/**
 * Class Client_Taxonomy
 * 
 * Handles the 'client' taxonomy for Landing Pages,
 * including term meta, admin columns, and admin UI customizations.
 */

namespace LPManager\taxonomy;

use Carbon_Fields\Container;
use Carbon_Fields\Field;
use League\ColorExtractor\Palette;
use League\ColorExtractor\Color;

class Client_Taxonomy {

    /** Term meta: hash of the client name and address the saved place ID and coordinates came from. */
    const GEOCODE_SOURCE_META = '_lpmanager_geocode_source';

    /** Term meta: when the saved latitude and longitude were last fetched from Google (Unix time). */
    const GEOCODED_AT_META = '_lpmanager_geocoded_at';

    /** Google's terms allow keeping latitude and longitude for up to 30 days; the place ID may be kept. */
    const GEOCODE_MAX_AGE = 30 * DAY_IN_SECONDS;

    /** Coordinates are refreshed from this age, so a late or busy WP-Cron run still beats GEOCODE_MAX_AGE. */
    const GEOCODE_REFRESH_AGE = 28 * DAY_IN_SECONDS;

    /** Term meta: failed refresh attempts after a client's coordinates were deleted (retried up to GEOCODE_MAX_RETRIES days). */
    const GEOCODE_RETRY_META = '_lpmanager_geocode_retries';

    /** Daily retries after a failed refresh, so a one-day Google or key problem doesn't remove a map for good. */
    const GEOCODE_MAX_RETRIES = 3;

    /** Clients handled per WP-Cron run; when a run is full, another is scheduled. */
    /** Term meta: when the daily task last tried this client (Unix time), so a failing batch isn't re-run all day. */
    const GEOCODE_ATTEMPTED_META = '_lpmanager_geocode_attempted_at';

    const GEOCODE_BATCH = 20; // Each client is one call with a 10-second timeout; keep a run well inside PHP time limits.

    /** Daily WP-Cron event that refreshes coordinates older than GEOCODE_REFRESH_AGE (or clears them). */
    const GEOCODE_REFRESH_EVENT = 'lpmanager_refresh_geocodes';

    /** Seconds to keep a Brandfetch answer per domain, so repeat saves don't make billed calls. */
    const BRANDFETCH_CACHE_TTL = WEEK_IN_SECONDS;

    public static function init() {
	// Hook taxonomy registration into WordPress 'init' action
        add_action( 'init', [ __CLASS__, 'register_taxonomy' ], 0 );
        add_action( 'carbon_fields_register_fields', [ __CLASS__, 'register_fields' ] );

        /*
         * Geocode + colors must run *after* Carbon persists term meta. Hooks like create_client /
         * edited_client at priority 20 often run before created_client @10 where Carbon saves, so
         * carbon_get_term_meta() was empty on first Add Client save.
         */
        add_action( 'carbon_fields_term_meta_container_saved', [ __CLASS__, 'after_client_term_meta_saved' ], 10, 2 );

        add_filter( 'manage_edit-client_columns', [ __CLASS__, 'customize_client_columns' ] ); 
		add_filter( 'manage_client_custom_column', [ __CLASS__, 'render_client_custom_column' ], 10, 3 );
        
        add_filter( 'manage_edit-client_columns', [ __CLASS__, 'custom_columns_filter' ] );
    	//add_action( 'manage_client_custom_column', [ __CLASS__, 'custom_column_renderer' ], 10, 3 );
        
        add_filter( 'redirect_term_location', [ __CLASS__, 'redirect_client_term_location' ], 10, 2 );

        add_action( self::GEOCODE_REFRESH_EVENT, [ __CLASS__, 'refresh_stale_geocodes' ] );
        add_action( self::GEOCODE_REFRESH_EVENT . '_more', [ __CLASS__, 'refresh_stale_geocodes' ] );
        add_action( 'init', [ __CLASS__, 'schedule_geocode_refresh' ] );
    }

    /**
     * Schedule the daily coordinate refresh. Plugin::deactivate() removes it.
     */
    public static function schedule_geocode_refresh() {
        if ( ! wp_next_scheduled( self::GEOCODE_REFRESH_EVENT ) ) {
            wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::GEOCODE_REFRESH_EVENT );
        }
    }

    /**
     * Runs when Carbon finishes saving the term meta container (add + edit). Meta is in the DB here.
     *
     * @param int                                  $term_id   Term ID.
     * @param \Carbon_Fields\Container\Container   $container Container instance (unused; all client meta is one container).
     */
    public static function after_client_term_meta_saved( $term_id, $container ) {
        $term_id = (int) $term_id;
        if ( $term_id <= 0 ) {
            return;
        }
        $term = get_term( $term_id );
        if ( ! $term || is_wp_error( $term ) || $term->taxonomy !== 'client' ) {
            return;
        }

        self::sanitize_client_domain( $term_id );
        self::geocode_address_and_save( $term_id );
        self::extract_and_save_colors( $term_id );
    }

    public static function handle_brandfetch_request( $request ) {
        $domain = self::normalize_domain( (string) $request->get_param( 'domain' ) );

        if ( '' === $domain ) {
            return new \WP_Error( 'invalid_domain', __( 'Enter a domain name such as example.com.', 'landing-page-manager' ), [ 'status' => 400 ] );
        }

        $colors = self::get_brandfetch_colors( $domain );

        return rest_ensure_response( $colors );
    }
    
    public static function resolve_client_colors( $term_id ) {
        $domain = carbon_get_term_meta( $term_id, 'client_domain' );
        $logo_id = carbon_get_term_meta( $term_id, 'client_logo' );

        if ( ! $domain && ! $logo_id ) {
            return self::get_default_colors();
        }

        if ( $domain ) {
            $colors = self::get_brandfetch_colors( $domain );
            if ( $colors ) {
                return $colors;
            }
        }

        if ( $logo_id ) {
            $image_path = get_attached_file( $logo_id );
            if ( $image_path ) {
                $colors = self::extract_primary_secondary_colors( $image_path );
                if ( $colors ) {
                    return $colors;
                }
            }
        }

        return self::get_default_colors();
    }

    public static function extract_primary_secondary_colors( $image_path ) {
        if ( ! file_exists( $image_path ) ) {
            return self::get_default_colors();
        }

        $info = getimagesize( $image_path );
        if ( $info === false ) {
            return self::get_default_colors();
        }

        $converted_temp_path = null;

        if ( $info['mime'] === 'image/webp' ) {
            $converted_temp_path = self::convert_webp_to_png_temp( $image_path );
            if ( $converted_temp_path && file_exists( $converted_temp_path ) ) {
                $image_path = $converted_temp_path;
            } else {
                return self::get_default_colors();
            }
        }

        $image_data = file_get_contents( $image_path );
        if ( ! $image_data ) {
            if ( $converted_temp_path ) {
                unlink( $converted_temp_path );
            }
            return self::get_default_colors();
        }
        
       // $image = imagecreatefromstring($image_data);
        switch ($info['mime']) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($image_path);
                break;
            case 'image/png':
                $image = imagecreatefrompng($image_path);
                break;
            case 'image/webp':
                $image = imagecreatefromwebp($image_path);
                break;
            default:
                return self::get_default_colors();
        }

        if ( ! $image ) {
            if ( $converted_temp_path ) {
                unlink( $converted_temp_path );
            }
            return self::get_default_colors();
        }

        try {
            $palette = Palette::fromGD( $image );
        } catch ( \Exception $e ) {
            imagedestroy( $image );
            if ( $converted_temp_path ) {
                unlink( $converted_temp_path );
            }
            return self::get_default_colors();
        }
        imagedestroy( $image );
        if ( $converted_temp_path ) {
            unlink( $converted_temp_path );
        }

        $topColors = $palette->getMostUsedColors( 5 );
        if ( empty( $topColors ) ) {
            return self::get_default_colors();
        }

        $primary_rgb = array_key_first($topColors);
        $primary_hex = Color::fromIntToHex($primary_rgb);

        unset($topColors[$primary_rgb]);
        $secondary_hex = !empty($topColors) ? Color::fromIntToHex(array_key_first($topColors)) : '#CCCCCC';

        return [
            'primary' => $primary_hex,
            'secondary' => $secondary_hex,
        ];
    }
    
    /**
     * A bare host name from what an operator typed ("https://www.Example.com/about" → "example.com"),
     * or '' when it isn't a valid host name.
     *
     * @param string $raw Domain as entered.
     * @return string
     */
    public static function normalize_domain( $raw ) {
        $domain = strtolower( trim( $raw ) );
        $domain = preg_replace( '#^[a-z][a-z0-9+.-]*://#', '', $domain ); // scheme
        $domain = preg_replace( '#[/?\#].*$#', '', $domain );           // path, query, fragment
        $domain = preg_replace( '#^[^@]*@#', '', $domain );              // user info
        $domain = preg_replace( '#:\d*$#', '', $domain );                // port
        $domain = preg_replace( '#^www\.#', '', $domain );
        $domain = rtrim( $domain, '.' );

        $label = '[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?';
        if ( strlen( $domain ) > 253 || ! preg_match( '/^(?:' . $label . '\.)+(?:[a-z]{2,63}|xn--[a-z0-9-]{1,59})$/', $domain ) ) {
            return '';
        }
        return $domain;
    }

    /**
     * Primary and secondary brand colors for a domain from Brandfetch, or false when there is no
     * key, the domain is invalid, the request fails or Brandfetch has no usable colors.
     * Answers are cached per domain for BRANDFETCH_CACHE_TTL.
     *
     * @param string $domain Domain.
     * @return array|false [ 'primary' => '#rrggbb', 'secondary' => '#rrggbb' ], or false.
     */
    public static function get_brandfetch_colors( $domain ) {
        $api_key = trim( (string) carbon_get_theme_option( 'lpmanager_brandfetch_api_key' ) );
        $domain  = self::normalize_domain( (string) $domain );
        if ( '' === $api_key || '' === $domain ) {
            return false;
        }

        $cache_key = 'lpmanager_brandfetch_' . md5( $domain );
        $cached    = get_transient( $cache_key );
        if ( is_array( $cached ) ) {
            return isset( $cached['primary'] ) ? $cached : false;
        }

        $response = wp_remote_get( 'https://api.brandfetch.io/v2/brands/' . rawurlencode( $domain ), [
            'timeout' => 10,
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Accept'        => 'application/json',
            ],
        ] );

        if ( is_wp_error( $response ) ) {
            return false;
        }
        $code = (int) wp_remote_retrieve_response_code( $response );
        if ( 404 === $code ) {
            // Brandfetch doesn't know this brand. Remember that, so saves don't repeat the call.
            set_transient( $cache_key, [], self::BRANDFETCH_CACHE_TTL );
            return false;
        }
        if ( 200 !== $code ) {
            return false; // Rate limit, bad key or outage: don't cache, try again next time.
        }

        $data   = json_decode( wp_remote_retrieve_body( $response ), true );
        $colors = [];
        if ( is_array( $data ) && ! empty( $data['colors'] ) && is_array( $data['colors'] ) ) {
            foreach ( $data['colors'] as $color ) {
                $hex = is_array( $color ) && isset( $color['hex'] ) ? sanitize_hex_color( (string) $color['hex'] ) : null;
                // Skip white: it is almost always the logo background, not a brand color.
                if ( $hex && ! in_array( strtolower( $hex ), [ '#ffffff', '#fff' ], true ) ) {
                    $colors[] = $hex;
                }
            }
        }

        $result = [];
        if ( $colors ) {
            $result = [
                'primary'   => $colors[0],
                'secondary' => isset( $colors[1] ) ? $colors[1] : $colors[0],
            ];
        }
        set_transient( $cache_key, $result, self::BRANDFETCH_CACHE_TTL );

        return $result ? $result : false;
    }

    public static function extract_and_save_colors( $term_id ) {
        $existing_primary   = carbon_get_term_meta( $term_id, 'client_primary_color' );
        $existing_secondary = carbon_get_term_meta( $term_id, 'client_secondary_color' );
        if ( $existing_primary && $existing_secondary ) {
            return;
        }
        $colors = self::resolve_client_colors( $term_id );
        if ( ! $colors ) {
            return;
        }
        $primary   = sanitize_hex_color( (string) $colors['primary'] );
        $secondary = sanitize_hex_color( (string) $colors['secondary'] );
        if ( ! $primary || ! $secondary ) {
            return;
        }
        carbon_set_term_meta( $term_id, 'client_primary_color', $primary );
        carbon_set_term_meta( $term_id, 'client_secondary_color', $secondary );
    }
	
    public static function convert_webp_to_png_temp($webp_path) {
        $image = imagecreatefromwebp($webp_path);
        if (!$image) {
            return false;
        }
        $temp_path = tempnam(sys_get_temp_dir(), 'img_') . '.png';
        imagepng($image, $temp_path);
        imagedestroy($image);
        return $temp_path;
    }
	
    public static function sanitize_client_domain( $term_id ) {
        $raw_domain = carbon_get_term_meta( $term_id, 'client_domain' );
        if ( ! $raw_domain ) {
            return;
        }

        // Store the bare host name. Leave anything that isn't a valid host name as entered.
        $domain = self::normalize_domain( (string) $raw_domain );

        if ( '' !== $domain && $domain !== $raw_domain ) {
            carbon_set_term_meta( $term_id, 'client_domain', $domain );
        }
    }

    public static function register_taxonomy() {
        $labels = [
            'name'                       => __( 'Clients', 'landing-page-manager' ),
            'singular_name'              => __( 'Client', 'landing-page-manager' ),
            'search_items'               => __( 'Search Clients', 'landing-page-manager' ),
            'popular_items'              => __( 'Popular Clients', 'landing-page-manager' ),
            'all_items'                  => __( 'All Clients', 'landing-page-manager' ),
            'parent_item'                => __( 'Parent Client', 'landing-page-manager' ),
            'parent_item_colon'          => __( 'Parent Client:', 'landing-page-manager' ),
            'edit_item'                  => __( 'Edit Client', 'landing-page-manager' ),
            'view_item'                  => __( 'View Client', 'landing-page-manager' ),
            'update_item'                => __( 'Update Client', 'landing-page-manager' ),
            'add_new_item'               => __( 'Add New Client', 'landing-page-manager' ),
            'new_item_name'              => __( 'New Client Name', 'landing-page-manager' ),
            'separate_items_with_commas' => __( 'Separate clients with commas', 'landing-page-manager' ),
            'add_or_remove_items'        => __( 'Add or remove clients', 'landing-page-manager' ),
            'choose_from_most_used'      => __( 'Choose from the most used clients', 'landing-page-manager' ),
            'not_found'                  => __( 'No clients found.', 'landing-page-manager' ),
            'no_terms'                   => __( 'No clients', 'landing-page-manager' ),
            'menu_name'                  => __( 'Clients', 'landing-page-manager' ),
            'items_list_navigation'      => __( 'Clients list navigation', 'landing-page-manager' ),
            'items_list'                 => __( 'Clients list', 'landing-page-manager' ),
            'most_used'                  => __( 'Most Used', 'landing-page-manager' ),
            'back_to_items'              => __( '← Back to Clients', 'landing-page-manager' ),
        ];

        $args = [
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => [ 'slug' => 'client' ],
            'show_in_menu'      => true,
    		'meta_box_cb'       => null, // <- this enables the default meta box
        ];

        register_taxonomy( 'client', [ 'landing_page' ], $args );
    }

    /**
     * "Header Tracking Code" field. It is printed raw in the head of the client's landing pages,
     * so only users allowed to post unfiltered HTML can change it (enforced on save by
     * Plugin_Settings::guard_raw_html_save); for everyone else the field is read-only.
     *
     * @return \Carbon_Fields\Field\Field
     */
    private static function header_tracking_code_field() {
        $field = Field::make( 'textarea', 'header_tracking_code', __( 'Header tracking code (call tracking, pixels, tags)', 'landing-page-manager' ) )
            ->set_rows( 4 )
            ->set_help_text( __( 'Paste your tracking script here. It is added to the head of all of this client\'s landing pages. Only users who can post unfiltered HTML (administrators and editors on a single site, super admins on multisite) can change it.', 'landing-page-manager' ) );
        if ( ! current_user_can( 'unfiltered_html' ) ) {
            $field->set_attribute( 'readOnly', 'readonly' );
        }
        return $field;
    }

    public static function register_fields() {
    // Client Taxonomy Meta Fields
    Container::make('term_meta', __('Client Details', 'landing-page-manager'))
        ->where('term_taxonomy', '=', 'client')

        // Branding & Identity Tab
        ->add_tab(__('Branding & Identity', 'landing-page-manager'), [
            Field::make('image', 'client_logo', 'Client Logo')
                ->set_required(true)
                ->set_help_text('Upload the client logo used for branding.'),

            Field::make('color', 'client_primary_color', 'Primary Color')
                ->set_help_text('Primary brand color for the client.'),

            Field::make('color', 'client_secondary_color', 'Secondary Color')
                ->set_help_text('Secondary brand color for the client.'),
        ])

        // Contact Info Tab
        ->add_tab(__('Contact Info', 'landing-page-manager'), [
            Field::make('text', 'client_address', 'Contact Address')
                ->set_required(true)
                ->set_help_text('Physical address for the client. Upon Add/Save/Update the Latitude, Longitude and Google Place ID (read-only fields below) will be populated.'),

            Field::make('text', 'client_address_lat', 'Latitude')
                ->set_width(50)
                ->set_attribute('readOnly', true),

            Field::make('text', 'client_address_lng', 'Longitude')
                ->set_width(50)
                ->set_attribute('readOnly', true),

            Field::make('text', 'client_place_id', 'Google Place ID')
                ->set_attribute('readOnly', true),
            
            Field::make('text', 'client_google_search_url', 'Google Search URL')
                ->set_attribute('readOnly', true),

            Field::make('text', 'client_phone', 'Contact Phone')
                ->set_required(true)
                ->set_help_text( __( 'Default phone number for this client\'s landing pages. A landing page\'s own "Page phone" overrides it on that page.', 'landing-page-manager' ) ),

            Field::make('text', 'client_domain', 'Client Domain')
                ->set_required(true)
                ->set_help_text('The domain for the client. Do not add https://'),
        ])

        // Tracking & Integration Tab
        ->add_tab(__('Tracking & Integration', 'landing-page-manager'), [
            self::header_tracking_code_field(),

            Field::make( 'checkbox', 'lpmanager_client_needs_jquery', __( 'Tracking tags need jQuery', 'landing-page-manager' ) )
                ->set_option_value( 'yes' )
                ->set_help_text( __( 'Tick this if this client\'s tracking or conversion tags use jQuery, for example a Custom HTML tag in Google Tag Manager. Templates that don\'t need jQuery (such as the starter templates) then load it anyway, so those tags keep working.', 'landing-page-manager' ) ),
        ])

        // Social Media Tab
        ->add_tab(__('Social Media', 'landing-page-manager'), [
            Field::make('complex', 'social_links', 'Social Links')
                ->add_fields([
                    Field::make('select', 'platform', 'Platform')
                        ->set_options([
                            'fa fa-facebook'  => 'Facebook',
                            'fa fa-youtube'   => 'YouTube',
                            'fa fa-instagram' => 'Instagram',
                            'fa fa-linkedin'  => 'LinkedIn',
                            'fa fa-twitter'   => 'Twitter',
                        ])
                        ->set_default_value('fa fa-facebook'),

                    Field::make('text', 'url', 'URL')
                        ->set_help_text('Paste the full profile URL'),
                ]),
        ]);

}

    
    public static function customize_client_columns( $columns ) {
        // Start fresh with desired columns in order
        $new_columns = [];

        // Name is always first and required
        $new_columns['cb'] = $columns['cb'] ?? ''; // Keep checkbox if exists
        $new_columns['name'] = __( 'Name', 'landing-page-manager' );

		// Add subdomain column
        $new_columns['client_slug'] = __( 'Slug', 'landing-page-manager' );
        
        // Add logo column
        $new_columns['client_logo'] = __( 'Logo', 'landing-page-manager' );

        // Add colors column
        $new_columns['client_colors'] = __( 'Colors', 'landing-page-manager' );

        // Finally, add count column at the end
        if ( isset( $columns['posts'] ) ) {
            $new_columns['posts'] = $columns['posts'];
        }

        return $new_columns;
    }
    
    public static function render_client_custom_column( $content, $column_name, $term_id ) {
        switch ( $column_name ) {
            case 'client_slug':
                $client_term = get_term( $term_id, 'client' );

                if ( is_wp_error( $client_term ) || ! $client_term ) {
                    return '<em>Error</em>';
                }

                $subdomain = $client_term->slug;

                return $subdomain ? esc_html( $subdomain ) : '<em>No subdomain</em>';

            case 'client_logo':
                $logo_id = carbon_get_term_meta( $term_id, 'client_logo' );
                if ( $logo_id ) {
                    //$image_url = wp_get_attachment_image_url( $logo_id, 'full' );
					$image_url = wp_get_attachment_url( $logo_id );
                    if ( $image_url ) {
                        return sprintf(
                            '<img src="%s" class="lpmanager-client-logo" style="max-height:50px; max-width:100px; width:auto; height:auto; display:block; margin:0 auto; object-fit:contain; float: left;" alt="Logo" />',
                            esc_url( $image_url )
                        );
                    }
                }
                return '<em>No logo</em>';

            case 'client_colors':
                $primary   = carbon_get_term_meta( $term_id, 'client_primary_color' );
                $secondary = carbon_get_term_meta( $term_id, 'client_secondary_color' );

                $primary_color_html = $primary ? sprintf(
                    '<div style="width:20px; height:20px; background:%s; border:1px solid #ccc; display:inline-block; margin-right:5px;"></div>',
                    esc_attr( $primary )
                ) : '<em>—</em>';

                $secondary_color_html = $secondary ? sprintf(
                    '<div style="width:20px; height:20px; background:%s; border:1px solid #ccc; display:inline-block;"></div>',
                    esc_attr( $secondary )
                ) : '<em>—</em>';

                return $primary_color_html . $secondary_color_html;

            default:
                return $content;
        }
    }
    
    public static function custom_columns_filter( $columns ) {
        unset( $columns['description'], $columns['slug'] );
        $columns['client_logo'] = __( 'Logo', 'landing-page-manager' );
        $columns['client_colors'] = __( 'Colors', 'landing-page-manager' );
        return $columns;
    }

    public static function custom_column_renderer( $content, $column_name, $term_id ) {
        if ( 'client_logo' === $column_name ) {
            $logo_id = carbon_get_term_meta( $term_id, 'client_logo' );
            if ( $logo_id ) {
                $logo_url = wp_get_attachment_image_url( $logo_id, [50, 50] );
                if ( $logo_url ) {
                    return '<img src="' . esc_url( $logo_url ) . '" style="max-width:50px; height:auto;">';
                }
            }
        } elseif ( 'client_colors' === $column_name ) {
            $primary = carbon_get_term_meta( $term_id, 'client_primary_color' );
            $secondary = carbon_get_term_meta( $term_id, 'client_secondary_color' );
            //error_log("Rendering client_colors for term $term_id: primary=$primary, secondary=$secondary");
            $content = '';
            if ( $primary ) {
                $content .= '<div style="width: 20px; height: 20px; background:' . esc_attr( $primary ) . '; display:inline-block; margin-right:5px; border:1px solid #000;"></div>';
            }
            if ( $secondary ) {
                $content .= '<div style="width: 20px; height: 20px; background:' . esc_attr( $secondary ) . '; display:inline-block; border:1px solid #000;"></div>';
            }
            return $content;
        }

        return $content;
    }
	
    public static function get_default_colors() {
        return [
            'primary'   => '#007BFF', // Web-safe blue
            'secondary' => '#FF4136'  // Web-safe red
        ];
    }
    
    public static function redirect_client_term_location( $location, $term_id ) {
        // Only redirect if adding a new term, not editing
        if ( isset( $_POST['action'] ) && $_POST['action'] === 'add-tag' ) {
            $client_term = get_term( $term_id, 'client' );
            if ( ! is_wp_error( $client_term ) ) {
                $location = admin_url( 'edit-tags.php?taxonomy=client&post_type=landing_page' );
            }
        }

        return $location;
    }
    /**
     * Look up the client's Google place ID and coordinates after a save.
     *
     * Skipped when the client name and address are unchanged and the coordinates are less than
     * GEOCODE_REFRESH_AGE old. When only the coordinates are old (or were deleted), they are
     * refreshed from the saved place ID (one Details call) instead of repeating the search.
     *
     * @param int $term_id Client term ID.
     */
    public static function geocode_address_and_save( $term_id ) {
        $term = get_term( $term_id, 'client' );
        if ( ! $term || is_wp_error( $term ) ) {
            return;
        }
        $company_name = $term->name;
        $address     = carbon_get_term_meta( $term_id, 'client_address' );
        $api_key     = lpmanager_google_server_api_key();
        if ( ! $address || ! $api_key ) {
            return;
        }

        $source      = md5( $company_name . '|' . $address );
        $saved_place = (string) carbon_get_term_meta( $term_id, 'client_place_id' );
        if ( '' !== $saved_place && get_term_meta( $term_id, self::GEOCODE_SOURCE_META, true ) === $source ) {
            if ( (int) get_term_meta( $term_id, self::GEOCODED_AT_META, true ) <= time() - self::GEOCODE_REFRESH_AGE ) {
                self::refresh_coordinates( $term_id, $saved_place, $api_key );
            }
            return;
        }

        if ( ! self::is_valid_address( $address ) ) {
            $query        = $company_name . ' ' . $address;
            $fallback_url = 'https://www.google.com/search?q=' . urlencode( $query );
            carbon_set_term_meta( $term_id, 'client_google_search_url', $fallback_url );
            carbon_set_term_meta( $term_id, 'client_place_id', '' );
            self::clear_coordinates( $term_id );
            return;
        }
        $query = rawurlencode( $company_name . ' ' . $address );
        $args  = [ 'timeout' => 10 ];
        $find_place_url = 'https://maps.googleapis.com/maps/api/place/findplacefromtext/json?input=' . $query . '&inputtype=textquery&fields=place_id&key=' . rawurlencode( $api_key );
        $find_response  = wp_remote_get( $find_place_url, $args );
        if ( is_wp_error( $find_response ) ) {
            return;
        }
        $find_data = json_decode( wp_remote_retrieve_body( $find_response ) );
        $place_id  = null;
        if ( $find_data && isset( $find_data->status ) && $find_data->status === 'OK' && ! empty( $find_data->candidates[0]->place_id ) ) {
            $place_id = $find_data->candidates[0]->place_id;
        } else {
            $text_search_url = 'https://maps.googleapis.com/maps/api/place/textsearch/json?query=' . $query . '&key=' . rawurlencode( $api_key );
            $text_response   = wp_remote_get( $text_search_url, $args );
            if ( is_wp_error( $text_response ) ) {
                return;
            }
            $text_data = json_decode( wp_remote_retrieve_body( $text_response ) );
            if ( $text_data && isset( $text_data->status ) && $text_data->status === 'OK' && ! empty( $text_data->results[0]->place_id ) ) {
                $place_id = $text_data->results[0]->place_id;
            } else {
                return;
            }
        }
        $place_id = sanitize_text_field( (string) $place_id );
        // Coordinates saved for an earlier name or address must not outlive it.
        self::clear_coordinates( $term_id );
        carbon_set_term_meta( $term_id, 'client_place_id', $place_id );
        if ( self::refresh_coordinates( $term_id, $place_id, $api_key ) ) {
            update_term_meta( $term_id, self::GEOCODE_SOURCE_META, $source );
        }
    }

    /**
     * Fetch the place's coordinates (Place Details, geometry only) and save them with the time.
     * If that fails, coordinates older than GEOCODE_MAX_AGE (Google's terms allow keeping them for
     * 30 days) are cleared; newer ones stay until the next attempt.
     *
     * @param int    $term_id  Client term ID.
     * @param string $place_id Google place ID.
     * @param string $api_key  Server key.
     * @return bool Whether fresh coordinates were saved.
     */
    public static function refresh_coordinates( $term_id, $place_id, $api_key ) {
        $details_url      = 'https://maps.googleapis.com/maps/api/place/details/json?place_id=' . rawurlencode( $place_id ) . '&fields=geometry&key=' . rawurlencode( $api_key );
        $details_response = wp_remote_get( $details_url, [ 'timeout' => 10 ] );
        $details_data     = is_wp_error( $details_response ) ? null : json_decode( wp_remote_retrieve_body( $details_response ) );
        if ( $details_data && isset( $details_data->status ) && $details_data->status === 'OK' && isset( $details_data->result->geometry->location->lat, $details_data->result->geometry->location->lng ) ) {
            $location = $details_data->result->geometry->location;
            carbon_set_term_meta( $term_id, 'client_address_lat', (string) (float) $location->lat );
            carbon_set_term_meta( $term_id, 'client_address_lng', (string) (float) $location->lng );
            update_term_meta( $term_id, self::GEOCODED_AT_META, time() );
            delete_term_meta( $term_id, self::GEOCODE_RETRY_META );
            return true;
        }
        if ( (int) get_term_meta( $term_id, self::GEOCODED_AT_META, true ) <= time() - self::GEOCODE_MAX_AGE ) {
            self::clear_coordinates( $term_id );
        }
        return false;
    }

    /**
     * Remove the saved coordinates and their fetch time. The place ID stays.
     *
     * @param int $term_id Client term ID.
     */
    private static function clear_coordinates( $term_id ) {
        carbon_set_term_meta( $term_id, 'client_address_lat', '' );
        carbon_set_term_meta( $term_id, 'client_address_lng', '' );
        delete_term_meta( $term_id, self::GEOCODED_AT_META );
        delete_term_meta( $term_id, self::GEOCODE_SOURCE_META );
    }

    /**
     * Daily: refresh coordinates older than GEOCODE_REFRESH_AGE (including ones saved before this
     * check existed, which have no fetch time); they are deleted once a refresh fails past
     * GEOCODE_MAX_AGE. A client whose coordinates were deleted that way is retried for up to
     * GEOCODE_MAX_RETRIES days. About one Details call per client per 28 days.
     */
    public static function refresh_stale_geocodes() {
        $base = [
            'taxonomy'   => 'client',
            'hide_empty' => false,
            'fields'     => 'ids',
            'number'     => self::GEOCODE_BATCH,
        ];
        $stale = get_terms( $base + [
            'meta_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                'relation' => 'AND',
                [
                    'key'     => '_client_address_lat',
                    'value'   => '',
                    'compare' => '!=',
                ],
                [
                    'relation' => 'OR',
                    [
                        'key'     => self::GEOCODED_AT_META,
                        'compare' => 'NOT EXISTS',
                    ],
                    [
                        'key'     => self::GEOCODED_AT_META,
                        'value'   => time() - self::GEOCODE_REFRESH_AGE,
                        'type'    => 'NUMERIC',
                        'compare' => '<=',
                    ],
                ],
                // Not tried in the last 20 hours: a batch that keeps failing must not re-run all day.
                [
                    'relation' => 'OR',
                    [
                        'key'     => self::GEOCODE_ATTEMPTED_META,
                        'compare' => 'NOT EXISTS',
                    ],
                    [
                        'key'     => self::GEOCODE_ATTEMPTED_META,
                        'value'   => time() - 20 * HOUR_IN_SECONDS,
                        'type'    => 'NUMERIC',
                        'compare' => '<=',
                    ],
                ],
            ],
        ] );
        $retry = get_terms( $base + [
            'meta_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                [
                    'key'     => self::GEOCODE_RETRY_META,
                    'compare' => 'EXISTS',
                ],
            ],
        ] );
        $stale = is_wp_error( $stale ) ? [] : array_map( 'intval', $stale );
        $retry = is_wp_error( $retry ) ? [] : array_diff( array_map( 'intval', $retry ), $stale );

        $api_key = lpmanager_google_server_api_key();
        foreach ( $stale as $term_id ) {
            update_term_meta( $term_id, self::GEOCODE_ATTEMPTED_META, time() );
            $place_id = (string) carbon_get_term_meta( $term_id, 'client_place_id' );
            if ( '' === $api_key || '' === $place_id ) {
                // Nothing to refresh from: they can only be deleted, once they reach GEOCODE_MAX_AGE.
                if ( (int) get_term_meta( $term_id, self::GEOCODED_AT_META, true ) <= time() - self::GEOCODE_MAX_AGE ) {
                    self::clear_coordinates( $term_id );
                }
                continue;
            }
            if ( ! self::refresh_coordinates( $term_id, $place_id, $api_key ) && '' === (string) carbon_get_term_meta( $term_id, 'client_address_lat' ) ) {
                update_term_meta( $term_id, self::GEOCODE_RETRY_META, 1 ); // Deleted just now: try again tomorrow.
            }
        }
        foreach ( $retry as $term_id ) {
            $place_id = (string) carbon_get_term_meta( $term_id, 'client_place_id' );
            $attempts = (int) get_term_meta( $term_id, self::GEOCODE_RETRY_META, true );
            if ( '' === $api_key || '' === $place_id || $attempts >= self::GEOCODE_MAX_RETRIES ) {
                delete_term_meta( $term_id, self::GEOCODE_RETRY_META );
                continue;
            }
            // Once a day: follow-up runs (the `_more` event) must not use up the retries in minutes.
            if ( (int) get_term_meta( $term_id, self::GEOCODE_ATTEMPTED_META, true ) > time() - 20 * HOUR_IN_SECONDS ) {
                continue;
            }
            update_term_meta( $term_id, self::GEOCODE_ATTEMPTED_META, time() );
            if ( ! self::refresh_coordinates( $term_id, $place_id, $api_key ) ) {
                update_term_meta( $term_id, self::GEOCODE_RETRY_META, $attempts + 1 );
            }
        }

        // A full batch may have left clients waiting: run again shortly instead of tomorrow.
        if ( count( $stale ) >= self::GEOCODE_BATCH && ! wp_next_scheduled( self::GEOCODE_REFRESH_EVENT . '_more' ) ) {
            wp_schedule_single_event( time() + 5 * MINUTE_IN_SECONDS, self::GEOCODE_REFRESH_EVENT . '_more' );
        }
    }

    public static function is_valid_address( $address ) {
        // Must have at least one digit (street number) and a word (street name)
        return preg_match('/\d+/', $address) && preg_match('/[A-Za-z]+/', $address);
    }
	
}