<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<title><?php esc_html_e( 'Page Not Found', 'landing-page-manager' ); ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php wp_head(); ?>
	<style>
		body { font-family: sans-serif; text-align: center; padding: 50px; }
		h1 { font-size: 48px; }
		p { font-size: 18px; }
	</style>
</head>
<body>
	<h1>404</h1>
	<p><?php esc_html_e( 'Page not found.', 'landing-page-manager' ); ?></p>
	<?php wp_footer(); ?>
</body>
</html>
