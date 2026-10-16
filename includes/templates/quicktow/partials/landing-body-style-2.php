<?php
/**
 * QuickTow — Style 2 body markup (alternate layout; pairs with style-2-layout.css).
 *
 * @package Landing_Page_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cm_star = '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6z"/></svg>';
$cm_chk  = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>';
?>

<section class="hero" id="qt-hero">
	<?php if ( ! empty( $wra_hero_bg_url ) ) : ?>
	<div class="hero-img-slot">
		<img class="hero-bg-image" src="<?php echo esc_url( $wra_hero_bg_url ); ?>" alt="" />
		<div class="hero-img-fade" aria-hidden="true"></div>
	</div>
	<?php endif; ?>
	<div class="hero-inner">
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
	<div class="trust-bar">
		<div class="trust-inner">
			<?php
			$tcount = count( $wra_trust_items );
			$ti     = 0;
			foreach ( $wra_trust_items as $row ) :
				if ( empty( $row['text'] ) ) {
					continue;
				}
				++$ti;
				?>
				<?php if ( $ti > 1 ) : ?>
					<div class="trust-sep" aria-hidden="true"></div>
				<?php endif; ?>
				<div class="trust-item">
					<?php echo $cm_star; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo esc_html( $row['text'] ); ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>
</section>

<?php require __DIR__ . '/landing-body-style-2-main.php'; ?>
