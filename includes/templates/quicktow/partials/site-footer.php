<?php
/**
 * QuickTow — site footer markup.
 *
 * @package Landing_Page_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wra_ft_bottom_right = isset( $wra_ft_bottom_right ) ? (string) $wra_ft_bottom_right : '';
$qt_ft_split_bottom  = isset( $wra_style_variant ) && in_array( $wra_style_variant, array( 'layout1', 'layout_3' ), true ) && '' !== trim( wp_strip_all_tags( $wra_ft_bottom_right ) );

$qt_layout2_ft  = isset( $wra_style_variant ) && 'layout2' === $wra_style_variant;
$qt_heavy_footer = $qt_layout2_ft && isset( $wra_ft_layout ) && 'heavy' === $wra_ft_layout;

if ( $qt_layout2_ft ) {
	$ft_logo = isset( $wra_header_logo_text ) ? $wra_header_logo_text : '';
	$ft_mod  = $qt_heavy_footer ? ' qt-layout2-ft--heavy' : '';
	?>
<footer class="qt-layout2-ft<?php echo esc_attr( $ft_mod ); ?>">
	<?php if ( $qt_heavy_footer ) : ?>
	<div class="footer-grid footer-grid--heavy">
		<div class="footer-brand wra-fi">
			<div class="logo-text">
				<?php
				if ( strpos( $ft_logo, '|' ) !== false ) {
					$fp = explode( '|', $ft_logo, 2 );
					echo esc_html( trim( $fp[0] ) ) . ' <span>' . esc_html( trim( $fp[1] ) ) . '</span>';
				} else {
					$fp = explode( ' ', $ft_logo, 2 );
					if ( count( $fp ) > 1 ) {
						echo esc_html( $fp[0] ) . ' <span>' . esc_html( $fp[1] ) . '</span>';
					} else {
						echo esc_html( $ft_logo );
					}
				}
				?>
			</div>
			<?php if ( ! empty( $wra_ft_tagline ) ) : ?>
				<p><?php echo esc_html( $wra_ft_tagline ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $client_phone ) && ! empty( $tel_digits ) ) : ?>
				<a class="footer-phone-link" href="tel:<?php echo esc_attr( $tel_digits ); ?>"><?php echo esc_html( $client_phone ); ?></a>
			<?php endif; ?>
			<?php if ( ! empty( $wra_ft_address_note ) ) : ?>
				<address><?php echo wp_kses_post( wpautop( $wra_ft_address_note ) ); ?></address>
			<?php endif; ?>
			<?php if ( ! empty( $wra_ft_hours ) ) : ?>
				<?php foreach ( $wra_ft_hours as $row ) : ?>
					<?php if ( ! empty( $row['day'] ) ) : ?>
						<span class="footer-hours-row"><?php echo esc_html( $row['day'] ); ?>: <?php echo esc_html( $row['hours'] ); ?></span>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		<?php if ( ! empty( $wra_ft_services ) ) : ?>
		<div class="footer-col wra-fi">
			<h4><?php esc_html_e( 'Services', 'landing-page-manager' ); ?></h4>
			<ul class="city-list">
				<?php foreach ( $wra_ft_services as $link ) : ?>
					<?php if ( ! empty( $link['label'] ) ) : ?>
						<li><a href="<?php echo esc_url( $link['url'] ?: '#' ); ?>"><?php echo esc_html( $link['label'] ); ?></a></li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php endif; ?>
		<?php if ( ! empty( $wra_ft_area_links ) ) : ?>
		<div class="footer-col wra-fi">
			<h4><?php esc_html_e( 'Service area', 'landing-page-manager' ); ?></h4>
			<ul class="city-list">
				<?php foreach ( $wra_ft_area_links as $link ) : ?>
					<?php if ( ! empty( $link['label'] ) ) : ?>
						<li><a href="<?php echo esc_url( ! empty( $link['url'] ) ? $link['url'] : '#' ); ?>"><?php echo esc_html( $link['label'] ); ?></a></li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php endif; ?>
	</div>
	<?php if ( ! empty( $wra_ft_cities ) ) : ?>
	<div class="footer-cities wra-fi"><?php echo wp_kses_post( wpautop( $wra_ft_cities ) ); ?></div>
	<?php endif; ?>
	<?php else : ?>
	<div class="footer-grid">
		<div class="footer-brand wra-fi">
			<div class="logo-text">
				<?php
				if ( strpos( $ft_logo, '|' ) !== false ) {
					$fp = explode( '|', $ft_logo, 2 );
					echo esc_html( trim( $fp[0] ) ) . ' <span>' . esc_html( trim( $fp[1] ) ) . '</span>';
				} else {
					$fp = explode( ' ', $ft_logo, 2 );
					if ( count( $fp ) > 1 ) {
						echo esc_html( $fp[0] ) . ' <span>' . esc_html( $fp[1] ) . '</span>';
					} else {
						echo esc_html( $ft_logo );
					}
				}
				?>
			</div>
			<?php if ( ! empty( $wra_ft_tagline ) ) : ?>
				<p><?php echo esc_html( $wra_ft_tagline ); ?></p>
			<?php elseif ( ! empty( $wra_ft_address_note ) ) : ?>
				<p><?php echo wp_kses_post( wp_strip_all_tags( $wra_ft_address_note ) ); ?></p>
			<?php endif; ?>
		</div>
		<?php if ( ! empty( $wra_ft_cities ) ) : ?>
		<div class="footer-col wra-fi">
			<h4><?php esc_html_e( 'Service area', 'landing-page-manager' ); ?></h4>
			<div class="city-list"><?php echo wp_kses_post( $wra_ft_cities ); ?></div>
		</div>
		<?php elseif ( ! empty( $wra_ft_services ) ) : ?>
		<div class="footer-col wra-fi">
			<h4><?php esc_html_e( 'Services', 'landing-page-manager' ); ?></h4>
			<ul class="city-list">
				<?php foreach ( $wra_ft_services as $link ) : ?>
					<?php if ( ! empty( $link['label'] ) ) : ?>
						<li><a href="<?php echo esc_url( $link['url'] ?: '#' ); ?>"><?php echo esc_html( $link['label'] ); ?></a></li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php endif; ?>
		<div class="footer-col footer-contact wra-fi">
			<h4><?php esc_html_e( 'Contact', 'landing-page-manager' ); ?></h4>
			<?php if ( ! empty( $client_phone ) && ! empty( $tel_digits ) ) : ?>
				<a href="tel:<?php echo esc_attr( $tel_digits ); ?>"><?php echo esc_html( $client_phone ); ?></a>
			<?php endif; ?>
			<?php if ( ! empty( $wra_ft_address_note ) ) : ?>
				<?php echo wp_kses_post( wpautop( $wra_ft_address_note ) ); ?>
			<?php endif; ?>
			<?php if ( ! empty( $wra_ft_hours ) ) : ?>
				<?php foreach ( $wra_ft_hours as $row ) : ?>
					<?php if ( ! empty( $row['day'] ) ) : ?>
						<span class="footer-hours-row"><?php echo esc_html( $row['day'] ); ?>: <?php echo esc_html( $row['hours'] ); ?></span>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
			<?php if ( ! empty( $wra_ft_nav ) ) : ?>
				<?php foreach ( $wra_ft_nav as $link ) : ?>
					<?php if ( ! empty( $link['label'] ) ) : ?>
						<a href="<?php echo esc_url( $link['url'] ?: '#' ); ?>"><?php echo esc_html( $link['label'] ); ?></a>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
	<?php endif; ?>
	<div class="footer-bottom">
		<?php if ( ! empty( $wra_ft_bottom ) ) : ?>
			<?php echo wp_kses_post( $wra_ft_bottom ); ?>
		<?php else : ?>
			&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $ft_logo ); ?>
		<?php endif; ?>
	</div>
</footer>
	<?php
	return;
}

if ( isset( $wra_style_variant ) && 'layout_3' === $wra_style_variant ) {
	$ft_logo = isset( $wra_header_logo_text ) ? $wra_header_logo_text : '';
	?>
<footer class="qt-layout-3-ft">
	<div class="footer-grid footer-grid--heavy">
		<div class="footer-brand wra-fi">
			<div class="logo-text">
				<?php
				if ( strpos( $ft_logo, '|' ) !== false ) {
					$fp = explode( '|', $ft_logo, 2 );
					echo esc_html( trim( $fp[0] ) ) . ' <span>' . esc_html( trim( $fp[1] ) ) . '</span>';
				} else {
					$fp = explode( ' ', $ft_logo, 2 );
					if ( count( $fp ) > 1 ) {
						echo esc_html( $fp[0] ) . ' <span>' . esc_html( $fp[1] ) . '</span>';
					} else {
						echo esc_html( $ft_logo );
					}
				}
				?>
			</div>
			<?php if ( ! empty( $wra_ft_tagline ) ) : ?>
				<p><?php echo esc_html( $wra_ft_tagline ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $client_phone ) && ! empty( $tel_digits ) ) : ?>
				<a class="footer-phone-link" href="tel:<?php echo esc_attr( $tel_digits ); ?>"><?php echo esc_html( $client_phone ); ?></a>
			<?php endif; ?>
			<?php if ( ! empty( $wra_ft_address_note ) ) : ?>
				<address><?php echo wp_kses_post( wpautop( $wra_ft_address_note ) ); ?></address>
			<?php endif; ?>
			<?php if ( ! empty( $wra_ft_hours ) ) : ?>
				<?php foreach ( $wra_ft_hours as $row ) : ?>
					<?php if ( ! empty( $row['day'] ) ) : ?>
						<span class="footer-hours-row"><?php echo esc_html( $row['day'] ); ?>: <?php echo esc_html( $row['hours'] ); ?></span>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		<?php if ( ! empty( $wra_ft_services ) ) : ?>
		<div class="footer-col wra-fi">
			<h4><?php esc_html_e( 'Services', 'landing-page-manager' ); ?></h4>
			<ul class="city-list">
				<?php foreach ( $wra_ft_services as $link ) : ?>
					<?php if ( ! empty( $link['label'] ) ) : ?>
						<li><a href="<?php echo esc_url( $link['url'] ?: '#' ); ?>"><?php echo esc_html( $link['label'] ); ?></a></li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php endif; ?>
		<?php if ( ! empty( $wra_ft_area_links ) ) : ?>
		<div class="footer-col wra-fi">
			<h4><?php esc_html_e( 'Service area', 'landing-page-manager' ); ?></h4>
			<ul class="city-list">
				<?php foreach ( $wra_ft_area_links as $link ) : ?>
					<?php if ( ! empty( $link['label'] ) ) : ?>
						<li><a href="<?php echo esc_url( ! empty( $link['url'] ) ? $link['url'] : '#' ); ?>"><?php echo esc_html( $link['label'] ); ?></a></li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php endif; ?>
	</div>
	<?php if ( ! empty( $wra_ft_cities ) ) : ?>
	<div class="footer-cities wra-fi"><?php echo wp_kses_post( wpautop( $wra_ft_cities ) ); ?></div>
	<?php endif; ?>
	<?php if ( $qt_ft_split_bottom ) : ?>
	<div class="footer-bottom footer-bottom--split">
		<div class="footer-bottom-col footer-bottom-col--left">
			<?php if ( ! empty( $wra_ft_bottom ) ) : ?>
				<?php echo wp_kses_post( $wra_ft_bottom ); ?>
			<?php else : ?>
				&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $ft_logo ); ?>
			<?php endif; ?>
		</div>
		<div class="footer-bottom-col footer-bottom-col--right">
			<?php echo wp_kses_post( $wra_ft_bottom_right ); ?>
		</div>
	</div>
	<?php else : ?>
	<div class="footer-bottom">
		<?php if ( ! empty( $wra_ft_bottom ) ) : ?>
			<?php echo wp_kses_post( $wra_ft_bottom ); ?>
		<?php else : ?>
			&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $ft_logo ); ?>
		<?php endif; ?>
	</div>
	<?php endif; ?>
</footer>
	<?php
	return;
}
?>
<footer id="footer" class="footer--<?php echo esc_attr( isset( $wra_style_variant ) ? $wra_style_variant : 'layout1' ); ?>">
	<div class="container">
		<div class="ft-grid">
			<div>
				<div class="logo-wrap">
					<?php if ( ! empty( $wra_use_logo_badge ) && isset( $wra_logo_badge_text ) && '' !== $wra_logo_badge_text ) : ?>
						<div class="logo-icon logo-icon--text-badge" aria-hidden="true"><span class="logo-badge-unskew"><?php echo esc_html( $wra_logo_badge_text ); ?></span></div>
					<?php elseif ( ! empty( $client_logo_url ) ) : ?>
						<div class="logo-icon"><img src="<?php echo esc_url( $client_logo_url ); ?>" alt="<?php echo esc_attr( $wra_header_logo_text ); ?>" /></div>
					<?php else : ?>
						<div class="logo-icon" aria-hidden="true">🚛</div>
					<?php endif; ?>
					<div>
						<span class="logo-name" style="font-size:28px"><?php echo esc_html( $wra_header_logo_text ); ?></span>
						<?php if ( ! empty( $wra_ft_tagline ) ) : ?>
							<span class="logo-tag"><?php echo esc_html( $wra_ft_tagline ); ?></span>
						<?php endif; ?>
					</div>
				</div>
				<address>
					<?php if ( ! empty( $wra_ft_address_note ) ) : ?>
						<?php echo wp_kses_post( wpautop( $wra_ft_address_note ) ); ?>
					<?php endif; ?>
					<?php if ( ! empty( $client_phone ) && ! empty( $tel_digits ) ) : ?>
						<a href="tel:<?php echo esc_attr( $tel_digits ); ?>"><?php echo esc_html( $client_phone ); ?></a><br>
					<?php endif; ?>
				</address>
			</div>
			<?php if ( ! empty( $wra_ft_services ) ) : ?>
			<div class="ft-col">
				<h4><?php esc_html_e( 'Services', 'landing-page-manager' ); ?></h4>
				<ul class="ft-links">
					<?php foreach ( $wra_ft_services as $link ) : ?>
						<?php if ( ! empty( $link['label'] ) ) : ?>
							<li><a href="<?php echo esc_url( $link['url'] ?: '#' ); ?>"><?php echo esc_html( $link['label'] ); ?></a></li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php endif; ?>
			<div class="ft-col">
				<?php if ( ! empty( $wra_ft_hours ) ) : ?>
					<h4><?php esc_html_e( 'Hours', 'landing-page-manager' ); ?></h4>
					<ul class="ft-hours">
						<?php foreach ( $wra_ft_hours as $row ) : ?>
							<?php if ( ! empty( $row['day'] ) ) : ?>
								<li><span><?php echo esc_html( $row['day'] ); ?></span><span class="open"><?php echo esc_html( $row['hours'] ); ?></span></li>
							<?php endif; ?>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
				<?php if ( ! empty( $wra_ft_nav ) ) : ?>
					<br>
					<h4><?php esc_html_e( 'Navigate', 'landing-page-manager' ); ?></h4>
					<ul class="ft-links">
						<?php foreach ( $wra_ft_nav as $link ) : ?>
							<?php if ( ! empty( $link['label'] ) ) : ?>
								<li><a href="<?php echo esc_url( $link['url'] ?: '#' ); ?>"><?php echo esc_html( $link['label'] ); ?></a></li>
							<?php endif; ?>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</div>
		<?php if ( ! empty( $wra_ft_cities ) ) : ?>
			<div class="ft-cities">
				<?php echo wp_kses_post( wpautop( $wra_ft_cities ) ); ?>
			</div>
		<?php endif; ?>
		<?php if ( $qt_ft_split_bottom ) : ?>
		<div class="ft-bottom ft-bottom--split">
			<div class="ft-bottom-col ft-bottom-col--left">
				<?php if ( ! empty( $wra_ft_bottom ) ) : ?>
					<?php echo wp_kses_post( $wra_ft_bottom ); ?>
				<?php else : ?>
					&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $wra_header_logo_text ); ?>
				<?php endif; ?>
			</div>
			<div class="ft-bottom-col ft-bottom-col--right">
				<?php echo wp_kses_post( $wra_ft_bottom_right ); ?>
			</div>
		</div>
		<?php else : ?>
		<div class="ft-bottom">
			<?php if ( ! empty( $wra_ft_bottom ) ) : ?>
				<?php echo wp_kses_post( $wra_ft_bottom ); ?>
			<?php else : ?>
				&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $wra_header_logo_text ); ?>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	</div>
</footer>
