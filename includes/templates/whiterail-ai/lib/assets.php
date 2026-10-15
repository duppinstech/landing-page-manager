<?php
/**
 * Front-end assets for the Whiterail AI template, in load order.
 *
 * Keys become handles (lpm-whiterail-ai-<key>). Dependencies name other keys in
 * this file, or core handles such as 'jquery'. Files in css/ and js/ that are not
 * listed here are not loaded.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'styles'  => array(
		'bootstrap'  => array( 'css/bootstrap.css' ),
		'swiper'     => array( 'css/swiper-bundle.css' ),
		'fonts'      => array( 'css/fonts.css' ),
		'header'     => array( 'css/header.css' ),
		'footer'     => array( 'css/footer.css' ),
		'main'       => array( 'css/main.css' ),
		'responsive' => array( 'css/responsive.css' ),
	),
	'scripts' => array(
		'swiper' => array( 'js/plugins/swiper.js' ),
		'main'   => array( 'js/main.js', array( 'jquery', 'swiper' ) ),
	),
);
