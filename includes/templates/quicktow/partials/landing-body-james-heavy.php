<?php
/**
 * QuickTow — Style 3 (James Towing heavy-duty reference): split hero (solid left, image right),
 * checkmark trust bar, then CharMeck-style sections inside .quicktow--charmeck wrapper.
 *
 * @package Landing_Page_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cm_chk = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>';
?>

<section class="hero hero--james-heavy on-dark" id="qt-hero">
	<div class="hero-bg" aria-hidden="true"></div>
	<div class="hero-bg-pattern" aria-hidden="true"></div>
	<div class="hero-img-slot">
		<?php if ( ! empty( $wra_hero_bg_url ) ) : ?>
			<img class="hero-bg-image" src="<?php echo esc_url( $wra_hero_bg_url ); ?>" alt="" />
		<?php else : ?>
			<div class="hero-img-ph" aria-hidden="true">
				<span class="hero-img-ph-icon">🚛</span>
				<span><?php esc_html_e( 'Upload a hero image in the Hero tab.', 'landing-page-manager' ); ?></span>
			</div>
		<?php endif; ?>
	</div>
	<div class="hero-gradient" aria-hidden="true"></div>
	<div class="hero-inner hero-inner--james-heavy">
		<?php if ( ! empty( $wra_hero_eyebrow ) ) : ?>
			<div class="hero-eyebrow"><?php echo esc_html( $wra_hero_eyebrow ); ?></div>
		<?php endif; ?>
		<?php if ( ! empty( $wra_hero_h1 ) ) : ?>
			<h1><?php echo wp_kses_post( $wra_hero_h1 ); ?></h1>
		<?php endif; ?>
		<?php if ( ! empty( $wra_hero_sub ) ) : ?>
			<p class="hero-sub"><?php echo esc_html( $wra_hero_sub ); ?></p>
		<?php endif; ?>
		<?php if ( $wra_show_hero_badge && ( ! empty( $wra_hero_badge_price ) || ! empty( $wra_hero_badge_sub ) ) ) : ?>
			<div class="price-badge">
				<span class="badge-dot" aria-hidden="true"></span>
				<?php if ( ! empty( $wra_hero_badge_price ) ) : ?>
					<span class="badge-price"><?php echo wp_kses_post( $wra_hero_badge_price ); ?></span>
				<?php endif; ?>
				<?php if ( ! empty( $wra_hero_badge_sub ) ) : ?>
					<span class="badge-text"><?php echo esc_html( $wra_hero_badge_sub ); ?></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<?php if ( ! empty( $wra_hero_support ) ) : ?>
			<p class="hero-support"><?php echo esc_html( $wra_hero_support ); ?></p>
		<?php endif; ?>
		<?php if ( ! empty( $client_phone ) && ! empty( $tel_digits ) ) : ?>
			<div class="hero-ctas">
				<div class="btn-primary">
					<div class="btn-skew-wrap"><a href="tel:<?php echo esc_attr( $tel_digits ); ?>"><?php echo esc_html( trim( $wra_hero_primary_btn_prefix ) . ' ' . $client_phone ); ?></a></div>
				</div>
				<?php if ( ! empty( $wra_hero_secondary_cta_text ) ) : ?>
					<div class="btn-secondary">
						<div class="btn-skew-wrap"><a href="<?php echo esc_url( $wra_hero_secondary_cta_url ); ?>"><?php echo esc_html( $wra_hero_secondary_cta_text ); ?></a></div>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( ! empty( $wra_trust_items ) ) : ?>
	<div class="trust-bar trust-bar--james-heavy">
		<div class="trust-bar-inner">
			<?php foreach ( $wra_trust_items as $row ) : ?>
				<?php if ( empty( $row['text'] ) ) { continue; } ?>
				<span class="trust-item">
					<em class="trust-check" aria-hidden="true">✔</em>
					<?php echo esc_html( $row['text'] ); ?>
				</span>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>
</section>

<div class="quicktow--charmeck qt-charmeck-sections">
<?php require __DIR__ . '/landing-body-charmeck-main.php'; ?>
</div>
