<?php
/**
 * Front-end assets for the Whiterail AI template, in load order.
 *
 * Keys become handles (lpm-whiterail-ai-<key>). Dependencies name other keys in
 * this file, or core handles such as 'jquery'. Files in css/ and js/ that are not
 * listed here are not loaded.
 *
 * Swiper (minified 5.3.0 build) only loads when the page has reviews to show in
 * the slider; main.js skips the carousel when Swiper isn't present.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lpm_wra_has_reviews = function_exists( 'carbon_get_post_meta' )
	&& '' !== trim( lpmanager_kses_rich_text( (string) carbon_get_post_meta( get_queried_object_id(), 'reviews_html' ) ) );

$lpm_wra_styles = array(
	'bootstrap'  => array( 'css/bootstrap.css' ),
	'swiper'     => array( 'css/swiper-bundle.css' ),
	'fonts'      => array( 'css/fonts.css' ),
	'header'     => array( 'css/header.css' ),
	'footer'     => array( 'css/footer.css' ),
	'main'       => array( 'css/main.css' ),
	'responsive' => array( 'css/responsive.css' ),
);
$lpm_wra_scripts = array(
	'swiper' => array( 'js/plugins/swiper.min.js' ),
	'main'   => array( 'js/main.js', array( 'jquery', 'swiper' ) ),
);

if ( ! $lpm_wra_has_reviews ) {
	unset( $lpm_wra_styles['swiper'], $lpm_wra_scripts['swiper'] );
	// Without this, WordPress would skip main.js for depending on an unregistered handle.
	$lpm_wra_scripts['main'][1] = array( 'jquery' );
}

return array(
	'styles'  => $lpm_wra_styles,
	'scripts' => $lpm_wra_scripts,
);
