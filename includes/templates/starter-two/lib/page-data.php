<?php
/**
 * Everything the starter page prints, read once: the page's fields, the client's
 * name, logo, colours and address, and the phone number.
 *
 * Values are raw here and escaped where they're printed.
 *
 * @package Landing_Page_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lpm_post_id = get_queried_object_id();
$lpm_meta    = function ( $key ) use ( $lpm_post_id ) {
	return function_exists( 'carbon_get_post_meta' ) ? carbon_get_post_meta( $lpm_post_id, $key ) : '';
};

$lpm_data = array(
	'name'               => get_bloginfo( 'name' ),
	'logo_url'           => '',
	'address'            => '',
	'primary'            => '',
	'secondary'          => '',
	// The page's own number wins, then the client's default.
	'phone'              => function_exists( 'lpmanager_get_landing_phone' ) ? (string) lpmanager_get_landing_phone( $lpm_post_id ) : '',
	'hero_heading'       => (string) $lpm_meta( 'starter_hero_heading' ),
	'hero_text'          => (string) $lpm_meta( 'starter_hero_text' ),
	'hero_image'         => (int) $lpm_meta( 'starter_hero_image' ),
	'cta_label'          => (string) $lpm_meta( 'starter_cta_label' ),
	'cta_url'            => (string) $lpm_meta( 'starter_cta_url' ),
	'highlights_heading' => (string) $lpm_meta( 'starter_highlights_heading' ),
	'highlights'         => array(),
	'about_heading'      => (string) $lpm_meta( 'starter_about_heading' ),
	'about_text'         => (string) $lpm_meta( 'starter_about_text' ),
	'faq'                => array(),
	'contact_heading'    => (string) $lpm_meta( 'starter_contact_heading' ),
	'contact_text'       => (string) $lpm_meta( 'starter_contact_text' ),
);

$lpm_terms = get_the_terms( $lpm_post_id, 'client' );
if ( ! empty( $lpm_terms ) && ! is_wp_error( $lpm_terms ) && function_exists( 'carbon_get_term_meta' ) ) {
	$lpm_term             = $lpm_terms[0];
	$lpm_data['name']     = $lpm_term->name;
	$lpm_logo_id          = (int) carbon_get_term_meta( $lpm_term->term_id, 'client_logo' );
	$lpm_data['logo_url'] = $lpm_logo_id ? (string) wp_get_attachment_image_url( $lpm_logo_id, 'medium' ) : '';
	$lpm_data['address']  = (string) carbon_get_term_meta( $lpm_term->term_id, 'client_address' );
	$lpm_primary          = (string) sanitize_hex_color( (string) carbon_get_term_meta( $lpm_term->term_id, 'client_primary_color' ) );
	$lpm_secondary        = (string) sanitize_hex_color( (string) carbon_get_term_meta( $lpm_term->term_id, 'client_secondary_color' ) );
	// #007BFF / #FF4136 is the pair stored when no brand colours were found, so it means "no colours".
	if ( ! ( '#007bff' === strtolower( $lpm_primary ) && '#ff4136' === strtolower( $lpm_secondary ) ) ) {
		$lpm_data['primary']   = $lpm_primary;
		$lpm_data['secondary'] = $lpm_secondary;
	}
}

foreach ( (array) $lpm_meta( 'starter_highlights' ) as $lpm_row ) {
	$lpm_title = isset( $lpm_row['title'] ) ? trim( (string) $lpm_row['title'] ) : '';
	if ( '' !== $lpm_title ) {
		$lpm_data['highlights'][] = array(
			'title' => $lpm_title,
			'text'  => isset( $lpm_row['text'] ) ? trim( (string) $lpm_row['text'] ) : '',
		);
	}
}
foreach ( (array) $lpm_meta( 'starter_faq' ) as $lpm_row ) {
	$lpm_question = isset( $lpm_row['question'] ) ? trim( (string) $lpm_row['question'] ) : '';
	if ( '' !== $lpm_question ) {
		$lpm_data['faq'][] = array(
			'question' => $lpm_question,
			'answer'   => isset( $lpm_row['answer'] ) ? (string) $lpm_row['answer'] : '',
		);
	}
}

// Defaults keep a new page presentable before every field is filled in.
if ( '' === trim( $lpm_data['hero_heading'] ) ) {
	$lpm_data['hero_heading'] = get_the_title( $lpm_post_id );
}
if ( '' === trim( $lpm_data['cta_label'] ) ) {
	$lpm_data['cta_label'] = __( 'Call now', 'landing-page-manager' );
}
if ( '' === trim( $lpm_data['highlights_heading'] ) ) {
	$lpm_data['highlights_heading'] = __( 'Why choose us', 'landing-page-manager' );
}
if ( '' === trim( $lpm_data['about_heading'] ) ) {
	/* translators: %s: business name */
	$lpm_data['about_heading'] = sprintf( __( 'About %s', 'landing-page-manager' ), $lpm_data['name'] );
}
if ( '' === trim( $lpm_data['contact_heading'] ) ) {
	$lpm_data['contact_heading'] = __( 'Get in touch', 'landing-page-manager' );
}

$lpm_data['phone_digits'] = preg_replace( '/[^0-9+]/', '', $lpm_data['phone'] );
if ( '' === $lpm_data['phone_digits'] ) {
	$lpm_data['phone'] = '';
}
// The hero button calls the phone unless the page sets its own link.
if ( '' === trim( $lpm_data['cta_url'] ) && '' !== $lpm_data['phone'] ) {
	$lpm_data['cta_url'] = 'tel:' . $lpm_data['phone_digits'];
}

// Client colours override the stylesheet's defaults through custom properties.
$lpm_data['style'] = '';
if ( $lpm_data['primary'] ) {
	$lpm_data['style'] .= '--lpm-primary:' . $lpm_data['primary'] . ';';
	if ( class_exists( '\LPManager\assets\Assets_Manager' ) && method_exists( '\LPManager\assets\Assets_Manager', 'get_contrast_text_color' ) ) {
		$lpm_data['style'] .= '--lpm-on-primary:' . \LPManager\assets\Assets_Manager::get_contrast_text_color( $lpm_data['primary'] ) . ';';
	}
}
if ( $lpm_data['secondary'] ) {
	$lpm_data['style'] .= '--lpm-accent:' . $lpm_data['secondary'] . ';';
}

return $lpm_data;
