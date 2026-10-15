<?php
/**
 * Front-end assets for the Whiterail Recruits template, in load order.
 *
 * Keys become handles (lpm-whiterail-recruits-<key>). Dependencies name other keys
 * in this file, or core handles such as 'jquery'. Files in css/ and js/ that are not
 * listed here are not loaded. main.js needs Bootstrap 4 (modal, collapse), Swiper
 * and WOW.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'styles'  => array(
		'bootstrap'  => array( 'css/bootstrap.css' ),
		'animate'    => array( 'css/animate.min.css' ),
		'swiper'     => array( 'css/swiper-bundle.css' ),
		'fonts'      => array( 'css/fonts.css' ),
		'header'     => array( 'css/header.css' ),
		'footer'     => array( 'css/footer.css' ),
		'main'       => array( 'css/main.css' ),
		'responsive' => array( 'css/responsive.css' ),
	),
	'scripts' => array(
		'popper'    => array( 'js/plugins/popper.min.js' ),
		'bootstrap' => array( 'js/plugins/bootstrap.js', array( 'jquery', 'popper' ) ),
		'swiper'    => array( 'js/plugins/swiper.js' ),
		'wow'       => array( 'js/wow.min.js' ),
		'main'      => array( 'js/main.js', array( 'jquery', 'bootstrap', 'swiper', 'wow' ) ),
	),
);
