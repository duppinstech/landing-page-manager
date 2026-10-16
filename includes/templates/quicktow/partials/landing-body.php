<?php
/**
 * QuickTow — main sections (hero through FAQ).
 *
 * @package Landing_Page_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Style 2 / 3 use alternate markup; not achievable with CSS-only on Style 1 DOM.
if ( isset( $wra_style_variant ) && 'layout2' === $wra_style_variant ) {
	require __DIR__ . '/landing-body-style-2.php';
	return;
}
if ( isset( $wra_style_variant ) && 'layout_3' === $wra_style_variant ) {
	require __DIR__ . '/landing-body-style-3.php';
	return;
}
?>

<section id="hero" class="hero--<?php echo esc_attr( isset( $wra_style_variant ) ? $wra_style_variant : 'layout1' ); ?>">
	<div class="stripe" aria-hidden="true"></div>
	<div class="hero-img-slot">
		<?php if ( ! empty( $wra_hero_bg_url ) ) : ?>
			<img class="hero-bg-image" src="<?php echo esc_url( $wra_hero_bg_url ); ?>" alt="" />
		<?php else : ?>
			<div class="ph" style="height:100%;">
				<span class="ph-icon" aria-hidden="true">📷</span>
				<span><?php esc_html_e( 'Upload a hero image in the Hero tab.', 'landing-page-manager' ); ?></span>
			</div>
		<?php endif; ?>
	</div>
	<div class="hero-fade hero-fade--<?php echo esc_attr( isset( $wra_style_variant ) ? $wra_style_variant : 'layout1' ); ?>" aria-hidden="true"></div>
	<div class="hero-content">
		<?php if ( ! empty( $wra_hero_eyebrow ) ) : ?>
			<div class="hero-eyebrow"><?php echo esc_html( $wra_hero_eyebrow ); ?></div>
		<?php endif; ?>
		<?php if ( ! empty( $wra_hero_h1 ) ) : ?>
			<h1 class="hero-title"><?php echo nl2br( esc_html( $wra_hero_h1 ) ); ?></h1>
		<?php endif; ?>
		<?php if ( ! empty( $wra_hero_sub ) ) : ?>
			<p class="hero-sub"><?php echo esc_html( $wra_hero_sub ); ?></p>
		<?php endif; ?>
		<?php if ( $wra_show_hero_badge && ( ! empty( $wra_hero_badge_price ) || ! empty( $wra_hero_badge_sub ) ) ) : ?>
			<div class="wra-price-badge">
				<span class="wra-badge-dot" aria-hidden="true"></span>
				<?php if ( ! empty( $wra_hero_badge_price ) ) : ?>
					<span class="wra-badge-price"><?php echo wp_kses_post( $wra_hero_badge_price ); ?></span>
				<?php endif; ?>
				<?php if ( ! empty( $wra_hero_badge_sub ) ) : ?>
					<span class="wra-badge-text"><?php echo esc_html( $wra_hero_badge_sub ); ?></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<?php if ( ! empty( $wra_hero_support ) ) : ?>
			<p class="hero-support"><?php echo esc_html( $wra_hero_support ); ?></p>
		<?php endif; ?>
		<?php if ( ! empty( $client_phone ) && ! empty( $tel_digits ) ) : ?>
			<div class="hero-ctas">
				<a href="tel:<?php echo esc_attr( $tel_digits ); ?>" class="btn btn-red btn-lg"><span><?php echo esc_html( trim( $wra_hero_primary_btn_prefix ) ); ?> <?php echo esc_html( $client_phone ); ?></span></a>
				<?php if ( ! empty( $wra_hero_secondary_cta_text ) ) : ?>
					<a href="<?php echo esc_url( $wra_hero_secondary_cta_url ); ?>" class="btn btn-outline"><span><?php echo esc_html( $wra_hero_secondary_cta_text ); ?></span></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php if ( ! empty( $wra_trust_items ) ) : ?>
	<div class="trust-bar">
		<div class="trust-bar-inner">
			<?php foreach ( $wra_trust_items as $row ) : ?>
				<?php if ( ! empty( $row['text'] ) ) : ?>
					<span class="ti"><?php echo esc_html( $row['text'] ); ?></span>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>
</section>

<?php if ( $wra_show_urgency && ! empty( $wra_urgency_text ) ) : ?>
<div class="urgency">
	<p><?php echo wp_kses_post( $wra_urgency_text ); ?></p>
</div>
<?php endif; ?>

<?php if ( $wra_show_pricing_strip && ( ! empty( $wra_pricing_pill_price ) || ! empty( $wra_pricing_strip_cta ) ) ) : ?>
<div class="wra-pricing-strip">
	<div class="wra-pricing-strip-inner">
		<?php if ( ! empty( $wra_pricing_pill_price ) || ! empty( $wra_pricing_pill_label ) ) : ?>
		<div class="wra-pricing-pill">
			<?php if ( ! empty( $wra_pricing_pill_price ) ) : ?>
				<div class="wra-pill-price"><?php echo esc_html( $wra_pricing_pill_price ); ?></div>
			<?php endif; ?>
			<?php if ( ! empty( $wra_pricing_pill_label ) ) : ?>
				<div class="wra-pill-label"><?php echo nl2br( esc_html( $wra_pricing_pill_label ) ); ?></div>
			<?php endif; ?>
		</div>
		<?php endif; ?>
		<?php if ( ! empty( $wra_pricing_strip_cta ) ) : ?>
			<div class="wra-pricing-strip-cta"><?php echo wp_kses_post( $wra_pricing_strip_cta ); ?></div>
		<?php endif; ?>
	</div>
</div>
<?php endif; ?>

<section id="near-me">
	<div class="container">
		<div class="two-col">
			<div>
				<?php if ( ! empty( $wra_nm_label ) ) : ?>
					<span class="section-label"><?php echo esc_html( $wra_nm_label ); ?></span>
				<?php endif; ?>
				<?php if ( ! empty( $wra_nm_h2 ) ) : ?>
					<h2 class="section-title"><?php echo esc_html( $wra_nm_h2 ); ?></h2>
				<?php endif; ?>
				<div class="accent-line"></div>
				<?php if ( ! empty( $wra_nm_body ) ) : ?>
					<div class="body-copy"><?php echo wp_kses_post( wpautop( $wra_nm_body ) ); ?></div>
				<?php endif; ?>
				<?php if ( ! empty( $wra_nm_cta_text ) && ! empty( $tel_digits ) ) : ?>
					<br><a href="tel:<?php echo esc_attr( $tel_digits ); ?>" class="btn btn-red"><span><?php echo esc_html( $wra_nm_cta_text ); ?></span></a>
				<?php endif; ?>
			</div>
			<div>
				<?php if ( ! empty( $wra_nm_image_url ) ) : ?>
					<div class="ph r43" style="padding:0;border:none;background:transparent;">
						<img src="<?php echo esc_url( $wra_nm_image_url ); ?>" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:6px;" />
					</div>
				<?php else : ?>
					<div class="ph r43"><span class="ph-icon" aria-hidden="true">📸</span><span><?php esc_html_e( 'Optional image', 'landing-page-manager' ); ?></span></div>
				<?php endif; ?>
				<?php if ( ! empty( $wra_qf_title ) || ! empty( $wra_qf_items ) ) : ?>
				<div class="qf-card">
					<?php if ( ! empty( $wra_qf_title ) ) : ?>
						<h4><?php echo esc_html( $wra_qf_title ); ?></h4>
					<?php endif; ?>
					<ul class="qf-list">
						<?php foreach ( $wra_qf_items as $row ) : ?>
							<?php if ( ! empty( $row['line'] ) ) : ?>
								<li><?php echo esc_html( $row['line'] ); ?></li>
							<?php endif; ?>
						<?php endforeach; ?>
					</ul>
				</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>

<section id="services">
	<div class="container">
		<div class="sec-header wra-fi">
			<?php if ( ! empty( $wra_sv_label ) ) : ?>
				<span class="section-label"><?php echo esc_html( $wra_sv_label ); ?></span>
			<?php endif; ?>
			<?php if ( ! empty( $wra_sv_h2 ) ) : ?>
				<h2 class="section-title"><?php echo esc_html( $wra_sv_h2 ); ?></h2>
			<?php endif; ?>
			<div class="accent-line c"></div>
			<?php if ( ! empty( $wra_sv_sub ) ) : ?>
				<p class="sec-sub"><?php echo esc_html( $wra_sv_sub ); ?></p>
			<?php endif; ?>
		</div>
		<div class="svc-grid">
			<?php foreach ( $wra_sv_cards as $card ) : ?>
				<?php if ( ! empty( $card['title'] ) || ! empty( $card['text'] ) ) : ?>
				<div class="svc-card wra-fi">
					<?php if ( ! empty( $card['icon'] ) ) : ?>
						<span class="svc-icon" aria-hidden="true"><?php echo lpm_quicktow_render_service_icon( $card['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<?php endif; ?>
					<?php if ( ! empty( $card['title'] ) ) : ?>
						<h3 class="card-title"><?php echo esc_html( $card['title'] ); ?></h3>
					<?php endif; ?>
					<?php if ( ! empty( $card['text'] ) ) : ?>
						<p><?php echo esc_html( $card['text'] ); ?></p>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php if ( ! empty( $wra_st_items ) ) : ?>
<section id="stats">
	<div class="container">
		<?php if ( ! empty( $wra_st_label ) || ! empty( $wra_st_h2 ) ) : ?>
		<div style="text-align:center" class="wra-fi">
			<?php if ( ! empty( $wra_st_label ) ) : ?>
				<span class="section-label" style="color:rgba(255,255,255,.4)"><?php echo esc_html( $wra_st_label ); ?></span>
			<?php endif; ?>
			<?php if ( ! empty( $wra_st_h2 ) ) : ?>
				<h2 class="section-title" style="color:#fff"><?php echo esc_html( $wra_st_h2 ); ?></h2>
			<?php endif; ?>
			<div class="accent-line c"></div>
		</div>
		<?php endif; ?>
		<div class="stats-grid">
			<?php foreach ( $wra_st_items as $st ) : ?>
				<?php if ( ! empty( $st['num'] ) || ! empty( $st['lbl'] ) ) : ?>
				<div class="stat-box wra-fi">
					<?php if ( ! empty( $st['num'] ) ) : ?>
						<span class="stat-num"><?php echo wp_kses_post( $st['num'] ); ?></span>
					<?php endif; ?>
					<?php if ( ! empty( $st['lbl'] ) ) : ?>
						<span class="stat-lbl"><?php echo esc_html( $st['lbl'] ); ?></span>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if ( $wra_show_feature ) : ?>
<section id="roadside">
	<div class="container">
		<div class="two-col flip">
			<div>
				<?php if ( ! empty( $wra_feat_label ) ) : ?>
					<span class="section-label"><?php echo esc_html( $wra_feat_label ); ?></span>
				<?php endif; ?>
				<?php if ( ! empty( $wra_feat_h2 ) ) : ?>
					<h2 class="section-title"><?php echo esc_html( $wra_feat_h2 ); ?></h2>
				<?php endif; ?>
				<div class="accent-line"></div>
				<?php if ( ! empty( $wra_feat_body ) ) : ?>
					<div class="body-copy"><?php echo wp_kses_post( wpautop( $wra_feat_body ) ); ?></div>
				<?php endif; ?>
				<?php if ( ! empty( $wra_feat_card_title ) || ! empty( $wra_feat_checklist ) ) : ?>
				<div class="cap-card">
					<?php if ( ! empty( $wra_feat_card_title ) ) : ?>
						<h4><?php echo esc_html( $wra_feat_card_title ); ?></h4>
					<?php endif; ?>
					<ul class="chk">
						<?php foreach ( $wra_feat_checklist as $row ) : ?>
							<?php if ( ! empty( $row['item'] ) ) : ?>
								<li><?php echo esc_html( $row['item'] ); ?></li>
							<?php endif; ?>
						<?php endforeach; ?>
					</ul>
				</div>
				<?php endif; ?>
				<?php if ( ! empty( $wra_feat_cta_text ) && ! empty( $tel_digits ) ) : ?>
					<br><a href="tel:<?php echo esc_attr( $tel_digits ); ?>" class="btn btn-red"><span><?php echo esc_html( $wra_feat_cta_text ); ?></span></a>
				<?php endif; ?>
			</div>
			<div>
				<?php if ( ! empty( $wra_feat_image_url ) ) : ?>
					<div class="ph r43" style="padding:0;border:none;background:transparent;">
						<img src="<?php echo esc_url( $wra_feat_image_url ); ?>" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:6px;" />
					</div>
				<?php else : ?>
					<div class="ph r43"><span class="ph-icon" aria-hidden="true">📷</span></div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>

<section id="cta-banner">
	<div class="stripe" aria-hidden="true"></div>
	<div class="container" style="position:relative;z-index:2;text-align:center">
		<?php if ( ! empty( $wra_cta_label ) ) : ?>
			<span class="section-label" style="color:rgba(255,255,255,.55)"><?php echo esc_html( $wra_cta_label ); ?></span>
		<?php endif; ?>
		<?php if ( ! empty( $wra_cta_h2 ) ) : ?>
			<h2 class="section-title" style="color:#fff"><?php echo esc_html( $wra_cta_h2 ); ?></h2>
		<?php endif; ?>
		<?php if ( ! empty( $wra_cta_sub ) ) : ?>
			<p class="cta-sub"><?php echo esc_html( $wra_cta_sub ); ?></p>
		<?php endif; ?>
		<?php if ( ! empty( $client_phone ) && ! empty( $tel_digits ) ) : ?>
			<a href="tel:<?php echo esc_attr( $tel_digits ); ?>" class="cta-phone bebas"><?php echo esc_html( $client_phone ); ?></a>
			<br>
			<?php if ( ! empty( $wra_cta_btn_text ) ) : ?>
				<a href="tel:<?php echo esc_attr( $tel_digits ); ?>" class="btn btn-white btn-lg"><span><?php echo esc_html( $wra_cta_btn_text ); ?></span></a>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</section>

<section id="area">
	<div class="container">
		<div class="sec-header wra-fi">
			<?php if ( ! empty( $wra_area_label ) ) : ?>
				<span class="section-label"><?php echo esc_html( $wra_area_label ); ?></span>
			<?php endif; ?>
			<?php if ( ! empty( $wra_area_h2 ) ) : ?>
				<h2 class="section-title"><?php echo esc_html( $wra_area_h2 ); ?></h2>
			<?php endif; ?>
			<div class="accent-line c"></div>
			<?php if ( ! empty( $wra_area_sub ) ) : ?>
				<p class="sec-sub"><?php echo esc_html( $wra_area_sub ); ?></p>
			<?php endif; ?>
		</div>
		<div class="area-grid">
			<?php foreach ( $wra_area_regions as $reg ) : ?>
				<?php if ( ! empty( $reg['name'] ) ) : ?>
				<div class="area-card wra-fi">
					<span class="area-name"><?php echo esc_html( $reg['name'] ); ?></span>
					<div class="pills">
						<?php
						$pills_raw = isset( $reg['pills'] ) ? $reg['pills'] : '';
						$lines = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $pills_raw ) ) );
						foreach ( $lines as $p ) {
							echo '<span class="pill">' . esc_html( $p ) . '</span>';
						}
						?>
					</div>
				</div>
				<?php endif; ?>
			<?php endforeach; ?>
			<?php if ( ! empty( $wra_area_callout_title ) || ! empty( $wra_area_callout_text ) ) : ?>
			<div class="area-card callout wra-fi">
				<?php if ( ! empty( $wra_area_callout_title ) ) : ?>
					<span class="area-name"><?php echo esc_html( $wra_area_callout_title ); ?></span>
				<?php endif; ?>
				<?php if ( ! empty( $wra_area_callout_text ) ) : ?>
					<p><?php echo esc_html( $wra_area_callout_text ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $client_phone ) && ! empty( $tel_digits ) ) : ?>
					<a href="tel:<?php echo esc_attr( $tel_digits ); ?>" class="btn btn-red" style="margin-top:8px"><span style="font-size:15px"><?php echo esc_html( $client_phone ); ?></span></a>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</div>
		<?php if ( ! empty( $wra_area_hwy_title ) || ! empty( $wra_area_hwy_text ) ) : ?>
		<div class="hwy-strip wra-fi">
			<?php if ( ! empty( $wra_area_hwy_title ) ) : ?>
				<h4><?php echo esc_html( $wra_area_hwy_title ); ?></h4>
			<?php endif; ?>
			<?php if ( ! empty( $wra_area_hwy_text ) ) : ?>
				<?php
				$hwy_raw = trim( (string) $wra_area_hwy_text );
				// One flowing line: avoid wpautop/textarea line breaks becoming stacked paragraphs.
				$hwy_raw = preg_replace( '/<\/p>\s*<p[^>]*>/i', ' ', $hwy_raw );
				$hwy_raw = preg_replace( '/<br\s*\/?>/i', ' ', $hwy_raw );
				$hwy_raw = str_replace( array( '<p>', '</p>' ), ' ', $hwy_raw );
				$hwy_raw = preg_replace( '/[\r\n\t]+/', ' ', $hwy_raw );
				$hwy_raw = preg_replace( '/\s{2,}/u', ' ', trim( $hwy_raw ) );
				?>
				<div class="hwy-list"><?php echo wp_kses_post( $hwy_raw ); ?></div>
			<?php endif; ?>
		</div>
		<?php endif; ?>
		<?php if ( ! empty( $wra_area_closing ) ) : ?>
			<p class="area-close"><?php echo wp_kses_post( $wra_area_closing ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php if ( $wra_show_faq && ! empty( $wra_faq_items ) ) : ?>
<section id="faq">
	<div class="container">
		<div class="sec-header wra-fi">
			<?php if ( ! empty( $wra_faq_h2 ) ) : ?>
				<h2 class="section-title"><?php echo esc_html( $wra_faq_h2 ); ?></h2>
			<?php endif; ?>
			<div class="accent-line c"></div>
		</div>
		<div class="faq-list">
			<?php foreach ( $wra_faq_items as $faq ) : ?>
				<?php if ( empty( $faq['q'] ) ) { continue; } ?>
			<div class="wra-faq-item">
				<button type="button" class="faq-q wra-faq-q">
					<span class="faq-qt"><?php echo esc_html( $faq['q'] ); ?></span>
					<span class="faq-chev" aria-hidden="true">+</span>
				</button>
				<div class="faq-ans">
					<?php if ( ! empty( $faq['a'] ) ) : ?>
						<p><?php echo esc_html( $faq['a'] ); ?></p>
					<?php endif; ?>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>
