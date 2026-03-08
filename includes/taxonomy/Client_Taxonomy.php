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
use LPManager\templates\Template_Manager;

class Client_Taxonomy {

    public static function init() {
	// Hook taxonomy registration into WordPress 'init' action
        add_action( 'init', [ __CLASS__, 'register_taxonomy' ], 0 );
        add_action( 'carbon_fields_register_fields', [ __CLASS__, 'register_fields' ] );
        //add_action( 'carbon_fields_term_meta_container_saved', 'update_client_location_data', 10, 2 );

        add_action( 'edited_client', [ __CLASS__, 'schedule_domain_sanitization' ], 10, 2 );
        add_action( 'edited_client', [ __CLASS__, 'schedule_color_extraction_on_term_save' ], 20, 2 );
        add_action( 'edited_client', [ __CLASS__, 'geocode_address_and_save' ], 20, 1 );

        add_action( 'create_client', [ __CLASS__, 'geocode_address_and_save' ], 20, 1 );
        add_action( 'create_client', function( $term_id, $tt_id ) {
            // Run your sanitization
            self::sanitize_client_domain( $term_id );

            // Run your color extraction and save
            self::extract_and_save_colors( $term_id );

        }, 10, 2 );
        
        add_filter( 'manage_edit-client_columns', [ __CLASS__, 'customize_client_columns' ] ); 
		add_filter( 'manage_client_custom_column', [ __CLASS__, 'render_client_custom_column' ], 10, 3 );
        
        add_filter( 'manage_edit-client_columns', [ __CLASS__, 'custom_columns_filter' ] );
    	//add_action( 'manage_client_custom_column', [ __CLASS__, 'custom_column_renderer' ], 10, 3 );
        
        add_filter( 'redirect_term_location', [ __CLASS__, 'redirect_client_term_location' ], 10, 2 );
        
        add_action( 'create_client', function( $term_id, $tt_id ) {
            $domain = isset($_POST['carbon_fields_compound_client_domain']) 
                ? sanitize_text_field($_POST['carbon_fields_compound_client_domain'])
                : '';
            
            self::sanitize_client_domain( $term_id );

            if ( $domain ) {
                $colors = self::resolve_client_colors( $term_id );
                carbon_set_term_meta( $term_id, 'client_primary_color', $colors['primary'] );
                carbon_set_term_meta( $term_id, 'client_secondary_color', $colors['secondary'] );
            }

            self::extract_and_save_colors( $term_id );

        }, 10, 2 );
    }

    public static function handle_brandfetch_request( $request ) {
        $domain = sanitize_text_field( $request->get_param( 'domain' ) );

        if ( empty( $domain ) ) {
            return new \WP_Error( 'missing_domain', 'Domain is required.', [ 'status' => 400 ] );
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

	public static function schedule_color_extraction_on_term_save( $term_id, $tt_id ) {
        //add_action( 'shutdown', function() use ( $term_id ) {
        //    Client_Taxonomy::extract_and_save_colors( $term_id );
        //});
        
        // Instead of scheduling, extract immediately
    	self::extract_and_save_colors( $term_id );
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
    
    public static function get_brandfetch_colors( $domain ) {
        $api_key = carbon_get_theme_option('lpmanager_brandfetch_api_key');
        $url = "https://api.brandfetch.io/v2/brands/$domain";

        $response = wp_remote_get( $url, [
            'headers' => [
                'Authorization' => "Bearer $api_key"
            ]
        ]);

        if ( is_wp_error( $response ) ) {
            return false;
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        
        if ( empty( $data['colors'] ) ) {
            return self::get_default_colors();
        }

        // Remove white (#FFFFFF, case-insensitive)
        $filtered_colors = array_filter( $data['colors'], function( $color ) {
            return strtolower( $color['hex'] ) !== '#ffffff';
        });

        // If none left after filtering, fallback to defaults
        if ( empty( $filtered_colors ) ) {
            return self::get_default_colors();
        }
        
        // Reset keys for safe indexing
        $filtered_colors = array_values( $filtered_colors );
        $primary   = $filtered_colors[0]['hex'] ?? null;
        $secondary = $filtered_colors[1]['hex'] ?? $primary;
        return [
            'primary'   => $primary,
            'secondary' => $secondary
        ];
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
        carbon_set_term_meta( $term_id, 'client_primary_color', $colors['primary'] );
        carbon_set_term_meta( $term_id, 'client_secondary_color', $colors['secondary'] );
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
	
    public static function schedule_domain_sanitization( $term_id, $tt_id ) {
        add_action( 'shutdown', function() use ( $term_id ) {
            self::sanitize_client_domain( $term_id );
        });
    }

    public static function sanitize_client_domain( $term_id ) {
        $raw_domain = carbon_get_term_meta( $term_id, 'client_domain' );
        if ( ! $raw_domain ) {
            return;
        }

        // Clean the domain
        $domain = preg_replace( '#^https?://#', '', $raw_domain ); // remove http/https
        $domain = preg_replace( '#^www\.#', '', $domain );         // remove www.
        $domain = rtrim( $domain, '/' );                           // remove trailing slash

        if ( $domain !== $raw_domain ) {
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
     * Options for client template select (slug => label). Empty = use global default.
     */
    public static function get_client_template_options() {
        $opts = [ '' => __( 'Use global default', 'landing-page-manager' ) ];
        $templates = Template_Manager::get_templates();
        foreach ( $templates as $slug => $info ) {
            $opts[ $slug ] = isset( $info['name'] ) ? $info['name'] : $slug;
        }
        return $opts;
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

            Field::make('select', 'lpmanager_client_template', __('Landing Page Template', 'landing-page-manager'))
                ->set_options( self::get_client_template_options() )
                ->set_help_text(__('Template used for all landing pages under this client. Overrides the global default.', 'landing-page-manager')),
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
                ->set_help_text('Contact phone number for the client.'),

            Field::make('text', 'client_domain', 'Client Domain')
                ->set_required(true)
                ->set_help_text('The domain for the client. Do not add https://'),
        ])

        // Tracking & Integration Tab
        ->add_tab(__('Tracking & Integration', 'landing-page-manager'), [
            Field::make('textarea', 'header_tracking_code', 'Header Tracking Code (Nimbata)')
                ->set_rows(4)
                ->set_help_text('Paste your tracking script here. It will be injected into the head of all associated landing pages.'),
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
    public static function geocode_address_and_save( $term_id ) {
        $term = get_term( $term_id, 'client' );
        if ( ! $term || is_wp_error( $term ) ) {
            return;
        }
        $company_name = $term->name;
        $address     = carbon_get_term_meta( $term_id, 'client_address' );
        $api_key     = carbon_get_theme_option( 'lpmanager_google_maps_api_key' );
        if ( ! $address || ! $api_key ) {
            return;
        }
        if ( ! self::is_valid_address( $address ) ) {
            $query        = $company_name . ' ' . $address;
            $fallback_url = 'https://www.google.com/search?q=' . urlencode( $query );
            carbon_set_term_meta( $term_id, 'client_google_search_url', $fallback_url );
            carbon_set_term_meta( $term_id, 'client_place_id', '' );
            return;
        }
        $query = urlencode( $company_name . ' ' . $address );
        $find_place_url = 'https://maps.googleapis.com/maps/api/place/findplacefromtext/json?input=' . $query . '&inputtype=textquery&fields=place_id&key=' . $api_key;
        $find_response  = wp_remote_get( $find_place_url );
        if ( is_wp_error( $find_response ) ) {
            return;
        }
        $find_data = json_decode( wp_remote_retrieve_body( $find_response ) );
        $place_id  = null;
        if ( $find_data && $find_data->status === 'OK' && ! empty( $find_data->candidates[0]->place_id ) ) {
            $place_id = $find_data->candidates[0]->place_id;
        } else {
            $text_search_url = 'https://maps.googleapis.com/maps/api/place/textsearch/json?query=' . $query . '&key=' . $api_key;
            $text_response   = wp_remote_get( $text_search_url );
            if ( is_wp_error( $text_response ) ) {
                return;
            }
            $text_data = json_decode( wp_remote_retrieve_body( $text_response ) );
            if ( $text_data && $text_data->status === 'OK' && ! empty( $text_data->results[0]->place_id ) ) {
                $place_id = $text_data->results[0]->place_id;
            } else {
                return;
            }
        }
        carbon_set_term_meta( $term_id, 'client_place_id', $place_id );
        $details_url     = 'https://maps.googleapis.com/maps/api/place/details/json?place_id=' . $place_id . '&fields=geometry&key=' . $api_key;
        $details_response = wp_remote_get( $details_url );
        if ( is_wp_error( $details_response ) ) {
            return;
        }
        $details_data = json_decode( wp_remote_retrieve_body( $details_response ) );
        if ( $details_data && $details_data->status === 'OK' && ! empty( $details_data->result->geometry->location ) ) {
            $location = $details_data->result->geometry->location;
            carbon_set_term_meta( $term_id, 'client_address_lat', $location->lat );
            carbon_set_term_meta( $term_id, 'client_address_lng', $location->lng );
        }
    }

    public static function is_valid_address( $address ) {
        // Must have at least one digit (street number) and a word (street name)
        return preg_match('/\d+/', $address) && preg_match('/[A-Za-z]+/', $address);
    }
	
}