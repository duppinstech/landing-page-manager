<?php

namespace LPManager\cpt;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Landing_Page_CPT {

    public static function init() {
        // Register CPT and taxonomies first
        add_action( 'init', [ __CLASS__, 'register_cpt' ] );

        // Register Carbon Fields fields after CR loads
        add_action( 'carbon_fields_register_fields', [ __CLASS__, 'register_fields' ] );

        // Custom admin columns
        add_filter( 'manage_edit-landing_page_columns', [ __CLASS__, 'add_columns' ] );
        add_action( 'manage_landing_page_posts_custom_column', [ __CLASS__, 'render_columns' ], 10, 2 );
	
        // Remove default taxonomy columns
        add_filter( 'manage_edit-landing_page_columns', [ __CLASS__, 'remove_default_taxonomy_columns' ], 20 );
        add_filter( 'quick_edit_show_taxonomy', [ __CLASS__, 'disable_quick_edit_taxonomies' ], 10, 3 );

        // Sortable columns and ordering
        add_filter( 'manage_edit-landing_page_sortable_columns', [ __CLASS__, 'sortable_columns' ] );
        add_action( 'pre_get_posts', [ __CLASS__, 'column_orderby' ] );
        
        // Enqueue javasript
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_quick_edit_script' ] );
        //add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_icon_picker_script' ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'google_reviews_fetch' ] );

        // Sync Carbon Fields meta → taxonomies **after Carbon saves meta**
        add_action( 'carbon_fields_post_meta_container_saved', [ __CLASS__, 'sync_meta_to_taxonomies' ], 10, 1 );

        // Quick Edit field render and save
        add_action( 'quick_edit_custom_box', [ __CLASS__, 'quick_edit_fields' ], 10, 2 );
        add_action( 'save_post', [ __CLASS__, 'save_quick_edit_fields' ], 10, 1 );
    }
	
    public static function remove_default_taxonomy_columns( $columns ) {
        unset( $columns['taxonomy-client'] );
        unset( $columns['taxonomy-keyword'] );
        return $columns;
    }

	public static function register_cpt() {
        $labels = [
            'name'                  => __( 'Landing Pages', 'landing-page-manager' ),
            'singular_name'         => __( 'Landing Page', 'landing-page-manager' ),
            'menu_name'             => __( 'Landing Pages', 'landing-page-manager' ),
            'name_admin_bar'        => __( 'Landing Page', 'landing-page-manager' ),
            'add_new'               => __( 'Add New', 'landing-page-manager' ),
            'add_new_item'          => __( 'Add New Landing Page', 'landing-page-manager' ),
            'new_item'              => __( 'New Landing Page', 'landing-page-manager' ),
            'edit_item'             => __( 'Edit Landing Page', 'landing-page-manager' ),
            'view_item'             => __( 'View Landing Page', 'landing-page-manager' ),
            'all_items'             => __( 'All Landing Pages', 'landing-page-manager' ),
            'search_items'          => __( 'Search Landing Pages', 'landing-page-manager' ),
            'parent_item_colon'     => __( 'Parent Landing Pages:', 'landing-page-manager' ),
            'not_found'             => __( 'No Landing Pages found.', 'landing-page-manager' ),
            'not_found_in_trash'    => __( 'No Landing Pages found in Trash.', 'landing-page-manager' ),
            'archives'              => __( 'Landing Page Archives', 'landing-page-manager' ),
            'insert_into_item'      => __( 'Insert into landing page', 'landing-page-manager' ),
            'uploaded_to_this_item' => __( 'Uploaded to this landing page', 'landing-page-manager' ),
            'featured_image'        => __( 'Landing Page Featured Image', 'landing-page-manager' ),
            'set_featured_image'    => __( 'Set featured image', 'landing-page-manager' ),
            'remove_featured_image' => __( 'Remove featured image', 'landing-page-manager' ),
            'use_featured_image'    => __( 'Use as featured image', 'landing-page-manager' ),
            'filter_items_list'     => __( 'Filter landing pages list', 'landing-page-manager' ),
            'items_list_navigation' => __( 'Landing pages list navigation', 'landing-page-manager' ),
            'items_list'            => __( 'Landing pages list', 'landing-page-manager' ),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'show_ui'            => true,
            'show_in_menu'       => 'lp_dashboard',
            'show_in_admin_bar'   => true,
			'publicly_queryable'  => true,
            'supports'           => [ 'title', 'editor', 'revisions' ],
            'has_archive'        => false,
            'rewrite'            => [ 'slug' => 'landing-page', 'with_front' => false ],
            'capability_type'    => 'post',
            'show_in_rest'       => true,
        ];

        register_post_type( 'landing_page', $args );
    }
	
    public static function register_fields() {
        Container::make( 'post_meta', 'Landing Page Details' )
            ->where( 'post_type', '=', 'landing_page' )
            ->add_fields( [

                Field::make( 'select', 'client', 'Client Name' )
                    ->add_options( self::get_client_options() )
                    ->set_required( true )
                    ->set_width( 50 )
                    ->set_default_value( '' ),
                
                Field::make( 'text', 'client_tel', 'Client Phone' )
                    ->set_required( true )
                    ->set_width( 50 )
                    ->set_default_value( '' ),

                Field::make( 'select', 'keyword', 'Keyword' )
                    ->add_options( self::get_keyword_options() )
                    ->set_required( true )
                    ->set_width( 50 )
                    ->set_default_value( '' ),

            ] );
    }

    public static function get_client_options() {
        $terms = get_terms( [
            'taxonomy'   => 'client',
            'hide_empty' => false,
            'number'     => 0,
        ] );

        $options = [ '' => 'Select a client...' ];

        if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
            foreach ( $terms as $term ) {
                $options[ $term->term_id ] = html_entity_decode( $term->name );
            }
        }

        return $options;
    }

    public static function get_keyword_options() {
        $terms = get_terms( [
            'taxonomy'   => 'keyword',
            'hide_empty' => false,
            'number'     => 0,
        ] );

        $options = [ '' => 'Select a keyword...' ];

        if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
            foreach ( $terms as $term ) {
                $options[ $term->term_id ] = $term->name;
            }
        }

        return $options;
    }
	
    public static function enqueue_quick_edit_script( $hook ) {
        if ( $hook !== 'edit.php' ) return;
        if ( get_current_screen()->post_type !== 'landing_page' ) return;

        wp_enqueue_script(
            'lpmanager-quick-edit',
            LP_MANAGER_PLUGIN_URL . 'assets/js/quick-edit.js',
            [ 'jquery', 'inline-edit-post' ],
            '1.0',
            true
        );
    }

    public static function enqueue_icon_picker_script( $hook ) {
        if ( $hook !== 'post.php' ) return;
        if ( get_current_screen()->post_type !== 'landing_page' ) return;

        wp_enqueue_style('iconpicker-css', 'https://cdn.jsdelivr.net/npm/@furcan/iconpicker@2.0.0/dist/css/iconpicker.min.css', [], '2.0.0');
        wp_enqueue_script('iconpicker-js', 'https://cdn.jsdelivr.net/npm/@furcan/iconpicker@2.0.0/dist/js/iconpicker.min.js', [], '2.0.0', true);
        wp_enqueue_script('lpmanager-editor', LP_MANAGER_PLUGIN_URL . 'assets/js/icon-picker.js', [ 'jquery'],'1.0', true);
    }

    public static function add_columns( $columns ) {
        $new_columns = [];

        foreach ( $columns as $key => $label ) {
            $new_columns[ $key ] = $label;

            if ( 'title' === $key ) {
                $new_columns['landing_page_url'] = __( 'Landing Page URL', 'landing-page-manager' );
                $new_columns['client'] = __( 'Client', 'landing-page-manager' );
                $new_columns['keywords'] = __( 'Keywords', 'landing-page-manager' );
            }
        }

        return $new_columns;
    }

    public static function render_columns( $column, $post_id ) {
        switch ( $column ) {
            case 'landing_page_url':
                $terms = get_the_terms( $post_id, 'client' );
                if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                    $client    = $terms[0];
                    $subdomain = $client->slug;
                    if ( $subdomain ) {
                        $main_domain = parse_url( home_url(), PHP_URL_HOST );
                        $post_slug   = get_post_field( 'post_name', $post_id );
                        $url         = 'https://' . $subdomain . '.' . $main_domain . '/' . $post_slug . '/';
                        echo '<a href="' . esc_url( $url ) . '" target="_blank">' . esc_html( $url ) . '</a>';
                    } else {
                        echo '<em>No Subdomain Assigned</em>';
                    }
                } else {
                    echo '<em>No Client Assigned</em>';
                }
                break;

            case 'client':
                $terms = get_the_terms( $post_id, 'client' );
                if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                echo '<span id="client-' . $post_id . '" data-term-id="' . esc_attr( $terms[0]->term_id ) . '">' . esc_html( $terms[0]->name ) . '</span>';
                } else {
                echo '<span id="client-' . $post_id . '" data-term-id="">No Client Assigned</span>';
                }
                break;

            case 'keywords':
                $terms = get_the_terms( $post_id, 'keyword' );
                if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                echo '<span id="keywords-' . $post_id . '" data-term-id="' . esc_attr( $terms[0]->term_id ) . '">' . esc_html( $terms[0]->name ) . '</span>';
                } else {
                echo '<span id="keywords-' . $post_id . '" data-term-id="">No Keyword Assigned</span>';
                }
                break;
        }
    }

    public static function sortable_columns( $columns ) {
        $columns['landing_page_url'] = 'landing_page_url';
        $columns['client'] = 'client';
        $columns['keywords'] = 'keywords';
        return $columns;
    }

    public static function column_orderby( $query ) {
        if ( ! is_admin() || ! $query->is_main_query() ) {
            return;
        }

        $orderby = $query->get( 'orderby' );

        if ( 'landing_page_url' === $orderby ) {
            $query->set( 'orderby', 'title' );
        }

        if ( 'client' === $orderby ) {
            // WordPress doesn’t natively sort by taxonomy name without JOIN — so fallback sort by post title
            $query->set( 'orderby', 'title' );
        }
    }


    /**
     * Save the taxonomy term from Carbon Fields association
     */
    public static function sync_meta_to_taxonomies( $post_id ) {
        self::set_taxonomy_terms( $post_id );
    }

    public static function set_taxonomy_terms( $post_id, $client_id = null, $keyword_id = null ) {
        // If values provided, use those; if not, fetch from meta
        if ( $client_id === null ) {
            $client_id = carbon_get_post_meta( $post_id, 'client' );
        }

        if ( $keyword_id === null ) {
            $keyword_id = carbon_get_post_meta( $post_id, 'keyword' );
        }

        // Set or unset client
        if ( $client_id ) {
            wp_set_object_terms( $post_id, [ (int) $client_id ], 'client', false );
        } else {
            wp_set_object_terms( $post_id, [], 'client' );
        }

        // Set or unset keyword
        if ( $keyword_id ) {
            wp_set_object_terms( $post_id, [ (int) $keyword_id ], 'keyword', false );
        } else {
            wp_set_object_terms( $post_id, [], 'keyword' );
        }
    }

    public static function quick_edit_fields( $column_name, $post_type ) {
        if ( $post_type !== 'landing_page' ) {
            return;
        }

        if ( $column_name === 'client' ) {
            $terms = get_terms( [ 'taxonomy' => 'client', 'hide_empty' => false ] );
            echo '<fieldset class="inline-edit-col-left"><div class="inline-edit-col">';
            echo '<label>';
            echo '<span class="title">Client</span>';
            echo '<select name="landing_page_client" id="landing_page_client_field">';
            echo '<option value="">— No Client —</option>';
            foreach ( $terms as $term ) {
                echo '<option value="' . esc_attr( $term->term_id ) . '">' . esc_html( $term->name ) . '</option>';
            }
            echo '</select>';
            echo '</label>';
            echo '</div></fieldset>';
        }

        if ( $column_name === 'keywords' ) {
            $terms = get_terms( [ 'taxonomy' => 'keyword', 'hide_empty' => false ] );
            echo '<fieldset class="inline-edit-col-left"><div class="inline-edit-col">';
            echo '<label>';
            echo '<span class="title">Keyword</span>';
            echo '<select name="landing_page_keyword" id="landing_page_keyword_field">';
            echo '<option value="">— No Keyword —</option>';
            foreach ( $terms as $term ) {
                echo '<option value="' . esc_attr( $term->term_id ) . '">' . esc_html( $term->name ) . '</option>';
            }
            echo '</select>';
            echo '</label>';
            echo '</div></fieldset>';
        }
    }
    
    public static function disable_quick_edit_taxonomies( $show, $taxonomy_name, $post_type ) {
        if ( $post_type === 'landing_page' && in_array( $taxonomy_name, [ 'client', 'keyword' ], true ) ) {
            return false;
        }
        return $show;
    }

    public static function save_quick_edit_fields( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( get_post_type( $post_id ) !== 'landing_page' ) {
            return;
        }
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'update-post_' . $post_id ) ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Client dropdown
        if ( isset( $_POST['landing_page_client'] ) ) {
            $client_id = intval( $_POST['landing_page_client'] );
            if ( $client_id ) {
                wp_set_object_terms( $post_id, [ $client_id ], 'client', false );
            } else {
                wp_set_object_terms( $post_id, [], 'client' );
            }
        }

        // Keyword dropdown
        if ( isset( $_POST['landing_page_keyword'] ) ) {
            $keyword_id = intval( $_POST['landing_page_keyword'] );
            if ( $keyword_id ) {
                wp_set_object_terms( $post_id, [ $keyword_id ], 'keyword', false );
            } else {
                wp_set_object_terms( $post_id, [], 'keyword' );
            }
        }
    }
    public static function google_reviews_fetch( $hook ) {
        if ( $hook !== 'post.php' && $hook !== 'post-new.php' ) {
            return;
        }
        $screen = get_current_screen();
        if ( ! $screen || $screen->post_type !== 'landing_page' ) {
            return;
        }
        wp_enqueue_script( 'lpmanager-ajax', LP_MANAGER_PLUGIN_URL . 'assets/js/reviews-fetch.js', [ 'jquery' ], '1.0', true );
        wp_localize_script( 'lpmanager-ajax', 'lpmanager_vars', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'lpmanager_nonce' ),
        ] );
    }
}