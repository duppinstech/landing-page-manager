<?php
/**
 * QuickTow — document head + top header.
 *
 * Expects variables set by single-landing_page.php: $client_*, $wra_*, $tel_digits, $wra_schema_json_ld (global).
 *
 * @package Landing_Page_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wra_schema_json_ld;

// Re-encode the stored JSON-LD so it can never close the <script> tag; drop it if it isn't valid JSON.
$qt_schema_json = '';
if ( is_string( $wra_schema_json_ld ) && '' !== trim( $wra_schema_json_ld ) ) {
	$qt_schema_decoded = json_decode( $wra_schema_json_ld, true );
	if ( is_array( $qt_schema_decoded ) ) {
		$qt_schema_json = (string) wp_json_encode( $qt_schema_decoded, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	}
}

$style_variant = isset( $wra_style_variant ) ? $wra_style_variant : 'james';
$qt_fixed_palette = in_array( $style_variant, array( 'james', 'charmeck', 'james_heavy' ), true );
$qt_body_classes  = array( 'quicktow' );
if ( 'james_heavy' === $style_variant ) {
	$qt_body_classes[] = 'quicktow--james-heavy';
} else {
	$qt_body_classes[] = 'quicktow--' . sanitize_html_class( $style_variant );
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta name="format-detection" content="telephone=no" />
	<script>document.documentElement.className += ' qt-js';</script>
	<?php wp_head(); ?>
	<?php if ( '' !== $qt_schema_json ) : ?>
	<script type="application/ld+json">
	<?php echo $qt_schema_json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Re-encoded above with JSON_HEX_TAG, so it cannot contain "<". ?>
	</script>
	<?php endif; ?>
	<?php if ( $qt_fixed_palette ) : ?>
	<style id="qt-charmeck-palette">
		/* James / CharMeck canonical tokens (matches static HTML samples; overrides client brand on Style 1–3). */
		:root .quicktow.quicktow--james,
		:root .quicktow.quicktow--charmeck,
		:root .quicktow.quicktow--james-heavy {
			--red: #B22234;
			--red-dark: #8B1A28;
			--red-light: #f9eaec;
			--navy: #002868;
			--navy-dark: #001A45;
			--navy-deep: #0A0C1E;
			--dark: #0A0C1E;
			--dark2: #0F0D26;
			--ink: #1a1a2e;
			--ink-mid: #3d3d55;
			--ink-light: #6b6b85;
			--white: #ffffff;
			--gray-light: #f7f8fc;
			--body-dark: #3d3d55;
			--strong-dark: #1a1a2e;
			--border: #e0e3ef;
			--text-light: rgba(255, 255, 255, 0.78);
		}
	</style>
	<?php endif; ?>
	<?php
	// Client colours (Odell, 10-16): after the built-in palette so they win; shades are derived and
	// darkened where needed so white text stays readable (see lpm_qt_brand_palette()).
	$qt_brand_palette = function_exists( 'lpm_qt_brand_palette' )
		? lpm_qt_brand_palette( isset( $client_primary ) ? $client_primary : '', isset( $client_secondary ) ? $client_secondary : '' )
		: array();
	if ( $qt_brand_palette ) :
		?>
	<style id="qt-brand-vars">
		:root .quicktow,
		:root .quicktow.quicktow--james,
		:root .quicktow.quicktow--charmeck,
		:root .quicktow.quicktow--james-heavy {
			<?php foreach ( $qt_brand_palette as $qt_var => $qt_colour ) : ?>
			<?php echo esc_html( $qt_var ); ?>: <?php echo esc_html( $qt_colour ); ?>;
			<?php endforeach; ?>
		}
	</style>
	<?php endif; ?>
</head>
<body <?php body_class( $qt_body_classes ); ?> data-qt-style="<?php echo esc_attr( $style_variant ); ?>">
<?php wp_body_open(); ?>

<?php
$layout = isset( $wra_header_layout ) ? $wra_header_layout : 'standard';
$logo_text = isset( $wra_header_logo_text ) ? $wra_header_logo_text : $client_name;
$use_logo_badge = ! empty( $wra_use_logo_badge );
$logo_badge   = isset( $wra_logo_badge_text ) ? $wra_logo_badge_text : '';
$tagline = isset( $wra_header_tagline ) ? $wra_header_tagline : '';
$phone_label = isset( $wra_header_phone_label ) ? $wra_header_phone_label : '';
$phone_svg = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.45 2 2 0 0 1 3.59 1.27h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.91a16 16 0 0 0 5.5 5.5l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>';
?>

<?php if ( 'james_heavy' === $style_variant ) : ?>
	<?php
	$heavy_badge = $use_logo_badge && '' !== $logo_badge ? $logo_badge : '';
	if ( '' === $heavy_badge && is_string( $logo_text ) && '' !== trim( $logo_text ) ) {
		$wds = preg_split( '/\s+/', trim( $logo_text ) );
		if ( count( $wds ) >= 2 ) {
			$heavy_badge = strtoupper( mb_substr( $wds[0], 0, 1, 'UTF-8' ) . mb_substr( $wds[1], 0, 1, 'UTF-8' ) );
		} else {
			$heavy_badge = strtoupper( mb_substr( $wds[0], 0, 2, 'UTF-8' ) );
		}
	}
	if ( '' === $heavy_badge ) {
		$heavy_badge = 'JT';
	}
	$heavy_name_html = '';
	if ( is_string( $logo_text ) && '' !== trim( $logo_text ) ) {
		if ( strpos( $logo_text, '|' ) !== false ) {
			$hp = explode( '|', $logo_text, 2 );
			$heavy_name_html = esc_html( trim( $hp[0] ) ) . ' <em>' . esc_html( trim( $hp[1] ) ) . '</em>';
		} else {
			$hp = preg_split( '/\s+/', trim( $logo_text ), 2 );
			if ( count( $hp ) === 2 ) {
				$heavy_name_html = esc_html( $hp[0] ) . ' <em>' . esc_html( $hp[1] ) . '</em>';
			} else {
				$heavy_name_html = esc_html( $logo_text );
			}
		}
	}
	$heavy_phone_label = '' !== $phone_label ? $phone_label : __( '24/7 Emergency Dispatch', 'landing-page-manager' );
	?>
<header class="qt-heavy-header" role="banner" aria-label="<?php esc_attr_e( 'Primary', 'landing-page-manager' ); ?>">
	<div class="qt-heavy-header-inner">
		<a class="qt-heavy-logo" href="<?php echo esc_url( get_permalink() ); ?>">
			<span class="qt-heavy-badge" aria-hidden="true"><span class="qt-heavy-badge-unskew"><?php echo esc_html( $heavy_badge ); ?></span></span>
			<span class="qt-heavy-wordmark">
				<span class="qt-heavy-name"><?php echo wp_kses_post( $heavy_name_html ); ?></span>
				<?php if ( $tagline ) : ?>
					<span class="qt-heavy-tagline"><?php echo esc_html( $tagline ); ?></span>
				<?php endif; ?>
			</span>
		</a>
		<?php if ( ! empty( $client_phone ) && ! empty( $tel_digits ) ) : ?>
		<div class="qt-heavy-header-right">
			<div class="qt-heavy-phone-block">
				<span class="qt-heavy-phone-label"><?php echo esc_html( $heavy_phone_label ); ?></span>
				<a class="qt-heavy-phone-num" href="tel:<?php echo esc_attr( $tel_digits ); ?>"><?php echo esc_html( $client_phone ); ?></a>
			</div>
			<div class="qt-heavy-call-btn">
				<div class="btn-skew-wrap"><a href="tel:<?php echo esc_attr( $tel_digits ); ?>"><?php esc_html_e( 'Call Now', 'landing-page-manager' ); ?></a></div>
			</div>
		</div>
		<?php endif; ?>
	</div>
</header>
<?php elseif ( 'charmeck' === $style_variant ) : ?>
<header class="site-header" role="banner" aria-label="<?php esc_attr_e( 'Primary', 'landing-page-manager' ); ?>">
	<a class="header-logo" href="<?php echo esc_url( get_permalink() ); ?>">
		<?php
		if ( strpos( $logo_text, '|' ) !== false ) {
			$parts = explode( '|', $logo_text, 2 );
			echo esc_html( trim( $parts[0] ) ) . ' <span>' . esc_html( trim( $parts[1] ) ) . '</span>';
		} else {
			$parts = explode( ' ', $logo_text, 2 );
			if ( count( $parts ) > 1 ) {
				echo esc_html( $parts[0] ) . ' <span>' . esc_html( $parts[1] ) . '</span>';
			} else {
				echo esc_html( $logo_text );
			}
		}
		?>
	</a>
	<?php if ( ! empty( $client_phone ) && ! empty( $tel_digits ) ) : ?>
	<a class="header-phone" href="tel:<?php echo esc_attr( $tel_digits ); ?>">
		<?php echo $phone_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php echo esc_html( $client_phone ); ?>
	</a>
	<a class="hamburger" href="tel:<?php echo esc_attr( $tel_digits ); ?>" aria-label="<?php esc_attr_e( 'Call us', 'landing-page-manager' ); ?>">
		<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.45 2 2 0 0 1 3.59 1.27h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.91a16 16 0 0 0 5.5 5.5l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
	</a>
	<?php endif; ?>
</header>
<?php elseif ( 'minimal' === $layout ) : ?>
<header class="wra-site-header--minimal wra-site-header--<?php echo esc_attr( $style_variant ); ?>" aria-label="<?php esc_attr_e( 'Primary', 'landing-page-manager' ); ?>">
	<a class="wra-min-logo" href="<?php echo esc_url( get_permalink() ); ?>">
		<?php
		if ( strpos( $logo_text, '|' ) !== false ) {
			$parts = explode( '|', $logo_text, 2 );
			echo esc_html( trim( $parts[0] ) ) . ' <span>' . esc_html( trim( $parts[1] ) ) . '</span>';
		} else {
			$parts = explode( ' ', $logo_text, 2 );
			if ( count( $parts ) > 1 ) {
				echo esc_html( $parts[0] ) . ' <span>' . esc_html( $parts[1] ) . '</span>';
			} else {
				echo esc_html( $logo_text );
			}
		}
		?>
	</a>
	<?php if ( ! empty( $client_phone ) && ! empty( $tel_digits ) ) : ?>
	<a class="wra-min-phone" href="tel:<?php echo esc_attr( $tel_digits ); ?>">
		<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.45 2 2 0 0 1 3.59 1.27h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.91a16 16 0 0 0 5.5 5.5l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
		<?php echo esc_html( $client_phone ); ?>
	</a>
	<a class="wra-hamburger" href="tel:<?php echo esc_attr( $tel_digits ); ?>" aria-label="<?php esc_attr_e( 'Call', 'landing-page-manager' ); ?>">
		<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.45 2 2 0 0 1 3.59 1.27h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.91a16 16 0 0 0 5.5 5.5l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
	</a>
	<?php endif; ?>
</header>
<?php else : ?>
<header id="hdr" class="hdr--<?php echo esc_attr( $style_variant ); ?>" role="banner">
	<div class="hdr-inner">
		<a href="<?php echo esc_url( get_permalink() ); ?>" class="logo-wrap">
			<?php if ( $use_logo_badge ) : ?>
				<span class="logo-icon logo-icon--text-badge" aria-hidden="true"><span class="logo-badge-unskew"><?php echo esc_html( $logo_badge ); ?></span></span>
			<?php elseif ( ! empty( $client_logo_url ) ) : ?>
				<span class="logo-icon"><img src="<?php echo esc_url( $client_logo_url ); ?>" alt="<?php echo esc_attr( $logo_text ); ?>" /></span>
			<?php else : ?>
				<span class="logo-icon" aria-hidden="true">🚛</span>
			<?php endif; ?>
			<span>
				<span class="logo-name"><?php echo esc_html( $logo_text ); ?></span>
				<?php if ( $tagline ) : ?>
					<span class="logo-tag"><?php echo esc_html( $tagline ); ?></span>
				<?php endif; ?>
			</span>
		</a>
		<div class="hdr-right">
			<?php if ( ! empty( $client_phone ) && ! empty( $tel_digits ) ) : ?>
				<a href="tel:<?php echo esc_attr( $tel_digits ); ?>" class="hdr-phone bebas"><?php echo esc_html( $client_phone ); ?></a>
				<a href="tel:<?php echo esc_attr( $tel_digits ); ?>" class="btn btn-navy hdr-cta"><span><?php esc_html_e( 'Call Now', 'landing-page-manager' ); ?></span></a>
				<a href="tel:<?php echo esc_attr( $tel_digits ); ?>" class="ham ham--call" id="ham" aria-label="<?php esc_attr_e( 'Call us', 'landing-page-manager' ); ?>">
					<?php echo $phone_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG defined above. ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</header>
<?php endif; ?>
