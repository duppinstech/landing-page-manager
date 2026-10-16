<?php
/**
 * QuickTow — Style 2 sections from urgency strip through FAQ (shared by Style 2 and Style 3 inner wrapper).
 *
 * Expects $cm_chk (SVG string), all $wra_* and $client_* from single-landing_page.php.
 *
 * @package Landing_Page_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php if ( $wra_show_urgency && ! empty( $wra_urgency_text ) ) : ?>
<div class="urgency-strip">
	<p><?php echo wp_kses_post( $wra_urgency_text ); ?></p>
</div>
<?php endif; ?>

<?php if ( $wra_show_pricing_strip && ( ! empty( $wra_pricing_pill_price ) || ! empty( $wra_pricing_strip_cta ) ) ) : ?>
<div class="pricing-strip wra-pricing-strip">
	<div class="pricing-strip-inner wra-pricing-strip-inner">
		<?php if ( ! empty( $wra_pricing_pill_price ) || ! empty( $wra_pricing_pill_label ) ) : ?>
		<div class="pricing-pill wra-pricing-pill">
			<?php if ( ! empty( $wra_pricing_pill_price ) ) : ?>
				<div class="pill-price wra-pill-price"><?php echo esc_html( $wra_pricing_pill_price ); ?></div>
			<?php endif; ?>
			<?php if ( ! empty( $wra_pricing_pill_label ) ) : ?>
				<div class="pill-label wra-pill-label"><?php echo nl2br( esc_html( $wra_pricing_pill_label ) ); ?></div>
			<?php endif; ?>
		</div>
		<?php endif; ?>
		<?php if ( ! empty( $wra_pricing_strip_cta ) ) : ?>
			<div class="pricing-strip-cta wra-pricing-strip-cta"><?php echo wp_kses_post( $wra_pricing_strip_cta ); ?></div>
		<?php endif; ?>
	</div>
</div>
<?php endif; ?>

<section class="near-me" id="near-me">
	<div class="container near-me-grid">
		<div class="near-me-body wra-fi">
			<?php if ( ! empty( $wra_nm_label ) ) : ?>
				<div class="section-label"><?php echo esc_html( $wra_nm_label ); ?></div>
			<?php endif; ?>
			<?php if ( ! empty( $wra_nm_h2 ) ) : ?>
				<h2 class="section-title"><?php echo esc_html( $wra_nm_h2 ); ?></h2>
			<?php endif; ?>
			<?php if ( ! empty( $wra_nm_body ) ) : ?>
				<div class="near-me-body-copy"><?php echo wp_kses_post( wpautop( $wra_nm_body ) ); ?></div>
			<?php endif; ?>
			<?php if ( ! empty( $wra_nm_cta_text ) && ! empty( $tel_digits ) ) : ?>
				<div class="btn-primary cm-inline-cta">
					<div class="btn-skew-wrap"><a href="tel:<?php echo esc_attr( $tel_digits ); ?>"><?php echo esc_html( $wra_nm_cta_text ); ?></a></div>
				</div>
			<?php endif; ?>
		</div>
		<div class="wra-fi">
			<?php if ( ! empty( $wra_nm_image_url ) ) : ?>
				<div class="cm-near-img">
					<img src="<?php echo esc_url( $wra_nm_image_url ); ?>" alt="" />
				</div>
			<?php endif; ?>
			<?php if ( ! empty( $wra_qf_title ) || ! empty( $wra_qf_items ) ) : ?>
			<div class="facts-card">
				<?php if ( ! empty( $wra_qf_title ) ) : ?>
					<h3><?php echo esc_html( $wra_qf_title ); ?></h3>
				<?php endif; ?>
				<?php foreach ( $wra_qf_items as $row ) : ?>
					<?php if ( ! empty( $row['line'] ) ) : ?>
					<div class="fact-row">
						<?php echo $cm_chk; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php echo esc_html( $row['line'] ); ?>
					</div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		</div>
	</div>
</section>

<section class="services-section" id="services">
	<div class="container">
		<div class="cm-sec-head wra-fi">
			<?php if ( ! empty( $wra_sv_label ) ) : ?>
				<div class="section-label"><?php echo esc_html( $wra_sv_label ); ?></div>
			<?php endif; ?>
			<?php if ( ! empty( $wra_sv_h2 ) ) : ?>
				<h2 class="section-title"><?php echo esc_html( $wra_sv_h2 ); ?></h2>
			<?php endif; ?>
			<?php if ( ! empty( $wra_sv_sub ) ) : ?>
				<p class="cm-sec-sub"><?php echo esc_html( $wra_sv_sub ); ?></p>
			<?php endif; ?>
		</div>
		<div class="services-grid">
			<?php foreach ( $wra_sv_cards as $card ) : ?>
				<?php if ( ! empty( $card['title'] ) || ! empty( $card['text'] ) ) : ?>
				<div class="service-card wra-fi">
					<?php if ( ! empty( $card['icon'] ) ) : ?>
						<div class="icon" aria-hidden="true"><?php echo lpm_quicktow_render_service_icon( $card['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					<?php endif; ?>
					<?php if ( ! empty( $card['title'] ) ) : ?>
						<h3><?php echo esc_html( $card['title'] ); ?></h3>
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
<section class="stats-section" id="stats">
	<div class="container">
		<?php if ( ! empty( $wra_st_label ) || ! empty( $wra_st_h2 ) ) : ?>
		<div class="cm-stats-head wra-fi">
			<?php if ( ! empty( $wra_st_label ) ) : ?>
				<div class="section-label cm-on-dark"><?php echo esc_html( $wra_st_label ); ?></div>
			<?php endif; ?>
			<?php if ( ! empty( $wra_st_h2 ) ) : ?>
				<h2 class="section-title-light"><?php echo esc_html( $wra_st_h2 ); ?></h2>
			<?php endif; ?>
		</div>
		<?php endif; ?>
		<div class="stats-grid">
			<?php foreach ( $wra_st_items as $st ) : ?>
				<?php if ( ! empty( $st['num'] ) || ! empty( $st['lbl'] ) ) : ?>
				<div class="stat-box wra-fi">
					<?php if ( ! empty( $st['num'] ) ) : ?>
						<div class="stat-number"><?php echo wp_kses_post( $st['num'] ); ?></div>
					<?php endif; ?>
					<?php if ( ! empty( $st['lbl'] ) ) : ?>
						<div class="stat-label"><?php echo esc_html( $st['lbl'] ); ?></div>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if ( $wra_show_feature ) : ?>
<section class="complex-section" id="roadside">
	<div class="container complex-grid<?php echo ( isset( $wra_style_variant ) && 'layout_3' === $wra_style_variant ) ? ' complex-grid--layout-3' : ''; ?>">
		<?php if ( isset( $wra_style_variant ) && 'layout_3' === $wra_style_variant ) : ?>
		<div class="complex-visual-col wra-fi">
			<?php if ( ! empty( $wra_feat_image_url ) ) : ?>
				<div class="cm-feat-img cm-feat-img--lead">
					<img src="<?php echo esc_url( $wra_feat_image_url ); ?>" alt="" />
				</div>
			<?php endif; ?>
			<?php if ( ! empty( $wra_feat_card_title ) || ! empty( $wra_feat_checklist ) ) : ?>
			<div class="checklist-card">
				<?php if ( ! empty( $wra_feat_card_title ) ) : ?>
					<h3><?php echo esc_html( $wra_feat_card_title ); ?></h3>
				<?php endif; ?>
				<?php foreach ( $wra_feat_checklist as $row ) : ?>
					<?php if ( ! empty( $row['item'] ) ) : ?>
					<div class="check-item">
						<?php echo $cm_chk; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php echo esc_html( $row['item'] ); ?>
					</div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		</div>
		<div class="complex-body wra-fi">
			<?php if ( ! empty( $wra_feat_label ) ) : ?>
				<div class="section-label"><?php echo esc_html( $wra_feat_label ); ?></div>
			<?php endif; ?>
			<?php if ( ! empty( $wra_feat_h2 ) ) : ?>
				<h2 class="section-title"><?php echo esc_html( $wra_feat_h2 ); ?></h2>
			<?php endif; ?>
			<?php if ( ! empty( $wra_feat_body ) ) : ?>
				<?php echo wp_kses_post( wpautop( $wra_feat_body ) ); ?>
			<?php endif; ?>
			<?php if ( ! empty( $wra_feat_cta_text ) && ! empty( $tel_digits ) ) : ?>
				<div class="btn-primary cm-feat-cta">
					<div class="btn-skew-wrap"><a href="tel:<?php echo esc_attr( $tel_digits ); ?>"><?php echo esc_html( $wra_feat_cta_text ); ?></a></div>
				</div>
			<?php endif; ?>
		</div>
		<?php else : ?>
		<div class="complex-body wra-fi">
			<?php if ( ! empty( $wra_feat_label ) ) : ?>
				<div class="section-label"><?php echo esc_html( $wra_feat_label ); ?></div>
			<?php endif; ?>
			<?php if ( ! empty( $wra_feat_h2 ) ) : ?>
				<h2 class="section-title"><?php echo esc_html( $wra_feat_h2 ); ?></h2>
			<?php endif; ?>
			<?php if ( ! empty( $wra_feat_body ) ) : ?>
				<?php echo wp_kses_post( wpautop( $wra_feat_body ) ); ?>
			<?php endif; ?>
			<?php if ( ! empty( $wra_feat_image_url ) ) : ?>
				<div class="cm-feat-img">
					<img src="<?php echo esc_url( $wra_feat_image_url ); ?>" alt="" />
				</div>
			<?php endif; ?>
		</div>
		<div class="wra-fi">
			<?php if ( ! empty( $wra_feat_card_title ) || ! empty( $wra_feat_checklist ) ) : ?>
			<div class="checklist-card">
				<?php if ( ! empty( $wra_feat_card_title ) ) : ?>
					<h3><?php echo esc_html( $wra_feat_card_title ); ?></h3>
				<?php endif; ?>
				<?php foreach ( $wra_feat_checklist as $row ) : ?>
					<?php if ( ! empty( $row['item'] ) ) : ?>
					<div class="check-item">
						<?php echo $cm_chk; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php echo esc_html( $row['item'] ); ?>
					</div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	</div>
</section>
<?php endif; ?>

<?php if ( ! empty( $client_phone ) && ! empty( $tel_digits ) ) : ?>
<div class="cta-banner wra-fi">
	<?php if ( ! empty( $wra_cta_label ) ) : ?>
		<p class="cm-cta-label"><?php echo esc_html( $wra_cta_label ); ?></p>
	<?php endif; ?>
	<?php if ( ! empty( $wra_cta_h2 ) ) : ?>
		<h2><?php echo esc_html( $wra_cta_h2 ); ?></h2>
	<?php endif; ?>
	<?php if ( ! empty( $wra_cta_sub ) ) : ?>
		<p class="cm-cta-sub"><?php echo esc_html( $wra_cta_sub ); ?></p>
	<?php endif; ?>
	<a class="cta-phone-display" href="tel:<?php echo esc_attr( $tel_digits ); ?>"><?php echo esc_html( $client_phone ); ?></a>
	<?php if ( ! empty( $wra_cta_btn_text ) ) : ?>
	<div class="btn-primary">
		<div class="btn-skew-wrap"><a href="tel:<?php echo esc_attr( $tel_digits ); ?>"><?php echo esc_html( $wra_cta_btn_text ); ?></a></div>
	</div>
	<?php endif; ?>
</div>
<?php endif; ?>

<section class="service-area" id="area">
	<div class="container">
		<div class="wra-fi">
			<?php if ( ! empty( $wra_area_label ) ) : ?>
				<div class="section-label"><?php echo esc_html( $wra_area_label ); ?></div>
			<?php endif; ?>
			<?php if ( ! empty( $wra_area_h2 ) ) : ?>
				<h2 class="section-title"><?php echo esc_html( $wra_area_h2 ); ?></h2>
			<?php endif; ?>
			<?php if ( ! empty( $wra_area_sub ) ) : ?>
				<p class="cm-area-intro"><?php echo esc_html( $wra_area_sub ); ?></p>
			<?php endif; ?>
		</div>
		<div class="area-grid">
			<?php foreach ( $wra_area_regions as $reg ) : ?>
				<?php if ( ! empty( $reg['name'] ) ) : ?>
				<div class="area-card wra-fi">
					<h4><?php echo esc_html( $reg['name'] ); ?></h4>
					<div class="pill-group">
						<?php
						$pills_raw = isset( $reg['pills'] ) ? $reg['pills'] : '';
						$lines     = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $pills_raw ) ) );
						foreach ( $lines as $p ) {
							echo '<span class="pill">' . esc_html( $p ) . '</span>';
						}
						?>
					</div>
				</div>
				<?php endif; ?>
			<?php endforeach; ?>
			<?php if ( ! empty( $wra_area_callout_title ) || ! empty( $wra_area_callout_text ) ) : ?>
			<div class="area-card cm-area-callout wra-fi">
				<?php if ( ! empty( $wra_area_callout_title ) ) : ?>
					<h4><?php echo esc_html( $wra_area_callout_title ); ?></h4>
				<?php endif; ?>
				<?php if ( ! empty( $wra_area_callout_text ) ) : ?>
					<p><?php echo esc_html( $wra_area_callout_text ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $client_phone ) && ! empty( $tel_digits ) ) : ?>
					<div class="btn-primary cm-area-cta">
						<div class="btn-skew-wrap"><a href="tel:<?php echo esc_attr( $tel_digits ); ?>"><?php echo esc_html( $client_phone ); ?></a></div>
					</div>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</div>
		<?php if ( ! empty( $wra_area_hwy_title ) || ! empty( $wra_area_hwy_text ) ) : ?>
		<div class="highway-strip wra-fi">
			<?php if ( ! empty( $wra_area_hwy_title ) ) : ?>
				<span><?php echo esc_html( $wra_area_hwy_title ); ?></span>
			<?php endif; ?>
			<?php
			if ( ! empty( $wra_area_hwy_text ) ) {
				$hwy_lines = preg_split( '/\r\n|\r|\n/', $wra_area_hwy_text );
				foreach ( $hwy_lines as $line ) {
					$badge = trim( wp_strip_all_tags( $line ) );
					if ( '' !== $badge ) {
						echo '<div class="highway-badge">' . esc_html( $badge ) . '</div>';
					}
				}
			}
			?>
		</div>
		<?php endif; ?>
		<?php if ( ! empty( $wra_area_closing ) ) : ?>
			<p class="area-closing wra-fi"><?php echo wp_kses_post( $wra_area_closing ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php if ( $wra_show_faq && ! empty( $wra_faq_items ) ) : ?>
<section class="faq-section" id="faq">
	<div class="container">
		<div class="cm-faq-head wra-fi">
			<?php if ( ! empty( $wra_faq_h2 ) ) : ?>
				<h2 class="section-title"><?php echo esc_html( $wra_faq_h2 ); ?></h2>
			<?php endif; ?>
		</div>
		<div class="faq-list">
			<?php foreach ( $wra_faq_items as $faq ) : ?>
				<?php if ( empty( $faq['q'] ) ) { continue; } ?>
			<div class="faq-item wra-faq-item">
				<button type="button" class="faq-q wra-faq-q">
					<span class="faq-qt"><?php echo esc_html( $faq['q'] ); ?></span>
					<span class="faq-icon" aria-hidden="true">+</span>
				</button>
				<div class="faq-ans">
					<?php if ( ! empty( $faq['a'] ) ) : ?>
						<div class="faq-a"><?php echo wp_kses_post( wpautop( $faq['a'] ) ); ?></div>
					<?php endif; ?>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>
