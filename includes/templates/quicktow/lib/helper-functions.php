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

if ( ! function_exists( 'lpm_qt_mix' ) ) {
	/**
	 * Mix a hex colour toward another (0 = unchanged, 1 = the other colour).
	 *
	 * @param string $hex    #rrggbb.
	 * @param string $toward #rrggbb.
	 * @param float  $amount 0..1.
	 * @return string #rrggbb.
	 */
	function lpm_qt_mix( $hex, $toward, $amount ) {
		$a = array_map( 'hexdec', str_split( ltrim( $hex, '#' ), 2 ) );
		$b = array_map( 'hexdec', str_split( ltrim( $toward, '#' ), 2 ) );
		$out = '#';
		for ( $i = 0; $i < 3; $i++ ) {
			$out .= sprintf( '%02x', (int) round( $a[ $i ] + ( $b[ $i ] - $a[ $i ] ) * $amount ) );
		}
		return $out;
	}
}

if ( ! function_exists( 'lpm_qt_contrast_with_white' ) ) {
	/**
	 * WCAG contrast ratio between a hex colour and white.
	 *
	 * @param string $hex #rrggbb.
	 * @return float
	 */
	function lpm_qt_contrast_with_white( $hex ) {
		$lum = 0.0;
		foreach ( array( 0.2126, 0.7152, 0.0722 ) as $i => $weight ) {
			$c    = hexdec( substr( ltrim( $hex, '#' ), $i * 2, 2 ) ) / 255;
			$c    = ( $c <= 0.03928 ) ? $c / 12.92 : pow( ( $c + 0.055 ) / 1.055, 2.4 );
			$lum += $weight * $c;
		}
		return 1.05 / ( $lum + 0.05 );
	}
}

if ( ! function_exists( 'lpm_qt_contrast' ) ) {
	/**
	 * WCAG contrast ratio between two hex colours.
	 *
	 * @param string $a #rrggbb.
	 * @param string $b #rrggbb.
	 * @return float
	 */
	function lpm_qt_contrast( $a, $b ) {
		$la = 1.05 / lpm_qt_contrast_with_white( $a ) - 0.05; // Relative luminance.
		$lb = 1.05 / lpm_qt_contrast_with_white( $b ) - 0.05;
		return ( max( $la, $lb ) + 0.05 ) / ( min( $la, $lb ) + 0.05 );
	}
}

if ( ! function_exists( 'lpm_qt_brand_palette' ) ) {
	/**
	 * CSS custom properties that apply the client's colours to QuickTow.
	 *
	 * QuickTow puts white text on its red and navy, so a client colour too light for that is
	 * darkened until white text on it reaches 4.5:1 (navy further, for its semi-transparent text). Returns an empty array when the client
	 * uses the default red and navy, so the built-in palette applies unchanged.
	 *
	 * @param string $primary   Client primary colour (#rrggbb), used where QuickTow uses navy.
	 * @param string $secondary Client secondary colour (#rrggbb), used where QuickTow uses red.
	 * @return array<string, string> Custom property => colour.
	 */
	function lpm_qt_brand_palette( $primary, $secondary ) {
		$primary   = sanitize_hex_color( (string) $primary );
		$secondary = sanitize_hex_color( (string) $secondary );
		if ( ! $primary || ! $secondary ) {
			return array();
		}
		$expand = function ( $hex ) {
			$hex = strtolower( ltrim( $hex, '#' ) );
			return '#' . ( 3 === strlen( $hex ) ? $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2] : $hex );
		};
		$primary   = $expand( $primary );
		$secondary = $expand( $secondary );
		if ( '#002868' === $primary && '#b22234' === $secondary ) {
			return array();
		}
		$readable = function ( $hex ) {
			for ( $i = 0; $i < 30 && lpm_qt_contrast_with_white( $hex ) < 4.5; $i++ ) {
				$hex = lpm_qt_mix( $hex, '#000000', 0.06 );
			}
			return $hex;
		};
		// Navy also carries semi-transparent white text (about 72% white) and is used as text on the
		// light tint, so it must be darker: 72% white on it reaches 4.5:1, and it reaches 5.3:1 on white.
		$readable_navy = function ( $hex ) {
			for ( $i = 0; $i < 40 && ( lpm_qt_contrast_with_white( $hex ) < 5.3 || lpm_qt_contrast( lpm_qt_mix( $hex, '#ffffff', 0.72 ), $hex ) < 4.5 ); $i++ ) {
				$hex = lpm_qt_mix( $hex, '#000000', 0.06 );
			}
			return $hex;
		};
		$red  = $readable( $secondary );
		$navy = $readable_navy( $primary );
		return array(
			'--red'       => $red,
			'--red-dark'  => lpm_qt_mix( $red, '#000000', 0.22 ),
			'--red-light' => lpm_qt_mix( $red, '#ffffff', 0.94 ),
			'--navy'      => $navy,
			'--navy-dark' => lpm_qt_mix( $navy, '#000000', 0.35 ),
		);
	}
}
