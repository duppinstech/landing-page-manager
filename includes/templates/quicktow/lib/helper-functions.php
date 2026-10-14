<?php
/**
 * QuickTow template helpers.
 *
 * @package Landing_Page_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Digits only for tel: href.
 *
 * @param string $phone Raw phone string.
 * @return string
 */
function lpm_wra_phone_digits( $phone ) {
	return preg_replace( '/\D+/', '', (string) $phone );
}

/**
 * Allowed tags for inline SVG in service card "icon" field.
 *
 * @return array<string, array<string, bool>>
 */
function lpm_quicktow_service_icon_svg_kses() {
	return array(
		'svg'      => array(
			'class'           => true,
			'width'           => true,
			'height'          => true,
			'viewbox'         => true,
			'viewBox'         => true,
			'fill'            => true,
			'xmlns'           => true,
			'stroke'          => true,
			'stroke-width'    => true,
			'stroke-linecap'  => true,
			'stroke-linejoin' => true,
			'aria-hidden'     => true,
			'role'            => true,
			'focusable'       => true,
			'opacity'         => true,
		),
		'path'     => array(
			'd'               => true,
			'fill'            => true,
			'stroke'          => true,
			'stroke-width'    => true,
			'stroke-linecap'  => true,
			'stroke-linejoin' => true,
			'transform'       => true,
			'opacity'         => true,
		),
		'line'     => array(
			'x1' => true, 'x2' => true, 'y1' => true, 'y2' => true,
			'stroke' => true, 'stroke-width' => true,
		),
		'circle'   => array(
			'cx' => true, 'cy' => true, 'r' => true,
			'fill' => true, 'stroke' => true, 'stroke-width' => true,
		),
		'ellipse'  => array(
			'cx' => true, 'cy' => true, 'rx' => true, 'ry' => true,
			'fill' => true, 'stroke' => true, 'stroke-width' => true,
		),
		'rect'     => array(
			'x' => true, 'y' => true, 'width' => true, 'height' => true,
			'rx' => true, 'ry' => true, 'fill' => true, 'stroke' => true, 'transform' => true,
		),
		'polyline' => array( 'points' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true ),
		'polygon'  => array( 'points' => true, 'fill' => true, 'stroke' => true ),
		'g'        => array( 'fill' => true, 'stroke' => true, 'transform' => true, 'class' => true ),
		'title'    => array(),
		'desc'     => array(),
	);
}

/**
 * Service card icon: emoji / short text, or full inline &lt;svg&gt;…&lt;/svg&gt; markup.
 *
 * @param string $raw Field value.
 * @return string Safe HTML (emoji escaped as text, or sanitized SVG).
 */
function lpm_quicktow_render_service_icon( $raw ) {
	$raw = trim( (string) $raw );
	if ( '' === $raw ) {
		return '';
	}
	if ( preg_match( '/<\s*svg[\s>]/i', $raw ) ) {
		return wp_kses( $raw, lpm_quicktow_service_icon_svg_kses() );
	}
	return esc_html( $raw );
}
