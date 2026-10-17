<?php
/**
 * Not-found page for the Starter One template.
 *
 * @package Landing_Page_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'lpm-starter' ); ?>>
<?php wp_body_open(); ?>
<main class="lpm-section lpm-notfound">
	<div class="lpm-wrap"><div class="lpm-prose">
		<h1><?php esc_html_e( 'Page not found', 'landing-page-manager' ); ?></h1>
		<p><?php esc_html_e( 'The page you are looking for is no longer available.', 'landing-page-manager' ); ?></p>
		<a class="lpm-button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Go to the home page', 'landing-page-manager' ); ?></a>
	</div></div>
</main>
<?php wp_footer(); ?>
</body>
</html>
