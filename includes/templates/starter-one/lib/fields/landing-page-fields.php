<?php
/**
 * Carbon Fields for the Starter One template.
 *
 * Starter One and Starter Two use the same field names, so a page keeps its content
 * when the site switches between them.
 *
 * @package Landing_Page_Manager
 */

use Carbon_Fields\Container;
use Carbon_Fields\Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

Container::make( 'post_meta', __( 'Starter One', 'landing-page-manager' ) )
	->show_on_post_type( 'landing_page' )
	->add_tab(
		__( 'Hero', 'landing-page-manager' ),
		array(
			Field::make( 'text', 'starter_hero_heading', __( 'Headline', 'landing-page-manager' ) )
				->set_help_text( __( 'Main headline. Uses the page title when empty.', 'landing-page-manager' ) ),
			Field::make( 'textarea', 'starter_hero_text', __( 'Introduction', 'landing-page-manager' ) )
				->set_rows( 3 ),
			Field::make( 'image', 'starter_hero_image', __( 'Hero image', 'landing-page-manager' ) ),
			Field::make( 'text', 'starter_cta_label', __( 'Button text', 'landing-page-manager' ) )
				->set_default_value( __( 'Call now', 'landing-page-manager' ) ),
			Field::make( 'text', 'starter_cta_url', __( 'Button link', 'landing-page-manager' ) )
				->set_help_text( __( 'Leave empty to call the page or client phone number.', 'landing-page-manager' ) ),
		)
	)
	->add_tab(
		__( 'Highlights', 'landing-page-manager' ),
		array(
			Field::make( 'text', 'starter_highlights_heading', __( 'Section heading', 'landing-page-manager' ) )
				->set_help_text( __( 'Default: Why choose us', 'landing-page-manager' ) ),
			Field::make( 'complex', 'starter_highlights', __( 'Highlights', 'landing-page-manager' ) )
				->set_layout( 'tabbed-vertical' )
				->add_fields(
					array(
						Field::make( 'text', 'title', __( 'Title', 'landing-page-manager' ) ),
						Field::make( 'textarea', 'text', __( 'Text', 'landing-page-manager' ) )->set_rows( 2 ),
					)
				)
				->set_header_template( '<%- title %>' ),
		)
	)
	->add_tab(
		__( 'About', 'landing-page-manager' ),
		array(
			Field::make( 'text', 'starter_about_heading', __( 'Section heading', 'landing-page-manager' ) )
				->set_help_text( __( 'Default: About and the client name', 'landing-page-manager' ) ),
			Field::make( 'rich_text', 'starter_about_text', __( 'About text', 'landing-page-manager' ) ),
		)
	)
	->add_tab(
		__( 'FAQ', 'landing-page-manager' ),
		array(
			Field::make( 'complex', 'starter_faq', __( 'Questions', 'landing-page-manager' ) )
				->set_layout( 'tabbed-vertical' )
				->add_fields(
					array(
						Field::make( 'text', 'question', __( 'Question', 'landing-page-manager' ) ),
						Field::make( 'textarea', 'answer', __( 'Answer', 'landing-page-manager' ) )->set_rows( 3 ),
					)
				)
				->set_header_template( '<%- question %>' ),
		)
	)
	->add_tab(
		__( 'Contact', 'landing-page-manager' ),
		array(
			Field::make( 'text', 'starter_contact_heading', __( 'Section heading', 'landing-page-manager' ) )
				->set_help_text( __( 'Default: Get in touch', 'landing-page-manager' ) ),
			Field::make( 'textarea', 'starter_contact_text', __( 'Text', 'landing-page-manager' ) )
				->set_rows( 2 ),
		)
	);
