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
	'logo_id'            => 0,
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
	$lpm_data['logo_id']  = (int) carbon_get_term_meta( $lpm_term->term_id, 'client_logo' );
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

// Client colours override the stylesheet's defaults through custom properties. Every text colour
// is chosen with the WCAG contrast formula, so any brand colour stays readable (4.5:1 for text,
// 3:1 for a button's edge). These match the page colours in css/main.css.
$lpm_page_bg      = '#0f1720';
$lpm_page_surface = '#18222e';
$lpm_page_text    = '#f1f4f7';

$lpm_luminance = function ( $hex ) {
	$hex = ltrim( $hex, '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	$channels = array();
	foreach ( str_split( $hex, 2 ) as $lpm_part ) {
		$lpm_c      = hexdec( $lpm_part ) / 255;
		$channels[] = $lpm_c <= 0.03928 ? $lpm_c / 12.92 : pow( ( $lpm_c + 0.055 ) / 1.055, 2.4 );
	}
	return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
};
$lpm_contrast = function ( $a, $b ) use ( $lpm_luminance ) {
	$la = $lpm_luminance( $a );
	$lb = $lpm_luminance( $b );
	return ( max( $la, $lb ) + 0.05 ) / ( min( $la, $lb ) + 0.05 );
};
// Black or white, whichever reads better on the colour.
$lpm_on = function ( $hex ) use ( $lpm_contrast ) {
	return $lpm_contrast( $hex, '#000000' ) >= $lpm_contrast( $hex, '#ffffff' ) ? '#000000' : '#ffffff';
};

$lpm_data['style'] = '';
if ( $lpm_data['primary'] ) {
	$lpm_primary        = $lpm_data['primary'];
	$lpm_data['style'] .= '--lpm-primary:' . $lpm_primary . ';--lpm-on-primary:' . $lpm_on( $lpm_primary ) . ';';
	// The primary colour as text on the page and its panels (header phone, contact button), or the text colour.
	$lpm_data['style'] .= '--lpm-primary-text:' . ( min( $lpm_contrast( $lpm_primary, $lpm_page_bg ), $lpm_contrast( $lpm_primary, $lpm_page_surface ) ) >= 4.5 ? $lpm_primary : $lpm_page_text ) . ';';
	// The contact button (page colour) sits on a primary-coloured band.
	if ( $lpm_contrast( $lpm_primary, $lpm_page_bg ) < 3 ) {
		$lpm_data['style'] .= '--lpm-contact-button-border:' . $lpm_on( $lpm_primary ) . ';';
	}
	// A primary-coloured button needs a visible edge where it barely differs from the page.
	if ( min( $lpm_contrast( $lpm_primary, $lpm_page_bg ), $lpm_contrast( $lpm_primary, $lpm_page_surface ) ) < 3 ) {
		$lpm_data['style'] .= '--lpm-button-border:' . $lpm_page_text . ';';
	}
}
if ( $lpm_data['secondary'] ) {
	$lpm_accent         = $lpm_data['secondary'];
	$lpm_data['style'] .= '--lpm-accent:' . $lpm_accent . ';--lpm-on-accent:' . $lpm_on( $lpm_accent ) . ';';
	$lpm_data['style'] .= '--lpm-accent-text:' . ( min( $lpm_contrast( $lpm_accent, $lpm_page_bg ), $lpm_contrast( $lpm_accent, $lpm_page_surface ) ) >= 4.5 ? $lpm_accent : $lpm_page_text ) . ';';
	// The mobile call bar (accent colour) needs a visible edge where it barely differs from the page.
	if ( min( $lpm_contrast( $lpm_accent, $lpm_page_bg ), $lpm_contrast( $lpm_accent, $lpm_page_surface ) ) < 3 ) {
		$lpm_data['style'] .= '--lpm-callbar-border:' . $lpm_page_text . ';';
	}
}

$lpm_data['show_contact'] = '' !== $lpm_data['phone'] || '' !== trim( $lpm_data['contact_text'] ) || '' !== trim( $lpm_data['address'] );

return $lpm_data;
