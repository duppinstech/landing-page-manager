<?php
/*
Template Name: Starter One
Description: A light starter layout: hero with call button, highlights, about, FAQ and contact. Copy it to build your own template.
*/

/**
 * Starter One landing page.
 *
 * Starter templates are a base to copy. To make your own, copy this folder to
 * wp-content/lpmanager-templates/<your-slug>/, change "Template Name" above, and edit freely.
 *
 * @package Landing_Page_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lpm_starter = require __DIR__ . '/lib/page-data.php';
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'lpm-starter' ); ?> style="<?php echo esc_attr( $lpm_starter['style'] ); ?>">
<?php wp_body_open(); ?>
<a class="lpm-skip" href="#lpm-main"><?php esc_html_e( 'Skip to content', 'landing-page-manager' ); ?></a>

<header class="lpm-header">
	<div class="lpm-wrap lpm-header__inner">
		<span class="lpm-brand">
			<?php if ( $lpm_starter['logo_url'] ) : ?>
				<img src="<?php echo esc_url( $lpm_starter['logo_url'] ); ?>" alt="<?php echo esc_attr( $lpm_starter['name'] ); ?>" />
			<?php else : ?>
				<?php echo esc_html( $lpm_starter['name'] ); ?>
			<?php endif; ?>
		</span>
		<?php if ( $lpm_starter['phone'] ) : ?>
			<a class="lpm-header__phone" href="<?php echo esc_url( 'tel:' . $lpm_starter['phone_digits'] ); ?>"><?php echo esc_html( $lpm_starter['phone'] ); ?></a>
		<?php endif; ?>
	</div>
</header>

<main id="lpm-main">
	<section class="lpm-hero">
		<div class="lpm-wrap lpm-hero__inner<?php echo $lpm_starter['hero_image'] ? ' lpm-hero__inner--media' : ''; ?>">
			<div class="lpm-hero__text">
				<h1><?php echo esc_html( $lpm_starter['hero_heading'] ); ?></h1>
				<?php if ( $lpm_starter['hero_text'] ) : ?>
					<p class="lpm-hero__lead"><?php echo esc_html( $lpm_starter['hero_text'] ); ?></p>
				<?php endif; ?>
				<?php if ( $lpm_starter['cta_url'] ) : ?>
					<a class="lpm-button" href="<?php echo esc_url( $lpm_starter['cta_url'] ); ?>"><?php echo esc_html( $lpm_starter['cta_label'] ); ?></a>
				<?php endif; ?>
			</div>
			<?php if ( $lpm_starter['hero_image'] ) : ?>
				<div class="lpm-hero__media"><?php echo wp_get_attachment_image( $lpm_starter['hero_image'], 'large', false, array( 'loading' => 'eager' ) ); ?></div>
			<?php endif; ?>
		</div>
	</section>

	<?php if ( $lpm_starter['highlights'] ) : ?>
		<section class="lpm-section lpm-highlights" aria-labelledby="lpm-highlights-heading">
			<div class="lpm-wrap">
				<h2 id="lpm-highlights-heading" class="lpm-section__title"><?php echo esc_html( $lpm_starter['highlights_heading'] ); ?></h2>
				<ul class="lpm-cards">
					<?php foreach ( $lpm_starter['highlights'] as $lpm_item ) : ?>
						<li class="lpm-card">
							<h3><?php echo esc_html( $lpm_item['title'] ); ?></h3>
							<?php if ( '' !== $lpm_item['text'] ) : ?>
								<p><?php echo esc_html( $lpm_item['text'] ); ?></p>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $lpm_starter['about_text'] ) : ?>
		<section class="lpm-section lpm-about" aria-labelledby="lpm-about-heading">
			<div class="lpm-wrap"><div class="lpm-prose">
				<h2 id="lpm-about-heading" class="lpm-section__title"><?php echo esc_html( $lpm_starter['about_heading'] ); ?></h2>
				<?php echo wp_kses_post( wpautop( $lpm_starter['about_text'] ) ); ?>
			</div></div>
		</section>
	<?php endif; ?>

	<?php if ( $lpm_starter['faq'] ) : ?>
		<section class="lpm-section lpm-faq" aria-labelledby="lpm-faq-heading">
			<div class="lpm-wrap"><div class="lpm-prose">
				<h2 id="lpm-faq-heading" class="lpm-section__title"><?php esc_html_e( 'Frequently asked questions', 'landing-page-manager' ); ?></h2>
				<?php foreach ( $lpm_starter['faq'] as $lpm_item ) : ?>
					<details class="lpm-faq__item">
						<summary><?php echo esc_html( $lpm_item['question'] ); ?></summary>
						<?php echo wp_kses_post( wpautop( $lpm_item['answer'] ) ); ?>
					</details>
				<?php endforeach; ?>
			</div></div>
		</section>
	<?php endif; ?>

	<section class="lpm-section lpm-contact" aria-labelledby="lpm-contact-heading">
		<div class="lpm-wrap lpm-contact__inner">
			<h2 id="lpm-contact-heading" class="lpm-section__title"><?php echo esc_html( $lpm_starter['contact_heading'] ); ?></h2>
			<?php if ( $lpm_starter['contact_text'] ) : ?>
				<p><?php echo esc_html( $lpm_starter['contact_text'] ); ?></p>
			<?php endif; ?>
			<?php if ( $lpm_starter['phone'] ) : ?>
				<a class="lpm-button" href="<?php echo esc_url( 'tel:' . $lpm_starter['phone_digits'] ); ?>">
					<?php
					/* translators: %s: phone number */
					echo esc_html( sprintf( __( 'Call %s', 'landing-page-manager' ), $lpm_starter['phone'] ) );
					?>
				</a>
			<?php endif; ?>
			<?php if ( $lpm_starter['address'] ) : ?>
				<p class="lpm-contact__address"><?php echo esc_html( $lpm_starter['address'] ); ?></p>
			<?php endif; ?>
		</div>
	</section>
</main>

<footer class="lpm-footer">
	<div class="lpm-wrap">
		<p>&copy; <?php echo esc_html( date_i18n( 'Y' ) . ' ' . $lpm_starter['name'] ); ?>. <?php esc_html_e( 'All rights reserved.', 'landing-page-manager' ); ?></p>
	</div>
</footer>

<?php if ( $lpm_starter['phone'] ) : ?>
	<a class="lpm-callbar" href="<?php echo esc_url( 'tel:' . $lpm_starter['phone_digits'] ); ?>">
		<?php
		/* translators: %s: phone number */
		echo esc_html( sprintf( __( 'Call %s', 'landing-page-manager' ), $lpm_starter['phone'] ) );
		?>
	</a>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
