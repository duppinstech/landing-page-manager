<?php
/**
 * Front-end assets for the Whiterail Recruits template, in load order.
 *
 * Keys become handles (lpm-whiterail-recruits-<key>). Dependencies name other keys
 * in this file, or core handles such as 'jquery'. Files in css/ and js/ that are not
 * listed here are not loaded. main.js only needs WOW: its Swiper and Bootstrap
 * modal/collapse code checks for markup this template doesn't output, so those
 * libraries aren't loaded. Bootstrap CSS stays for the grid.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'styles'  => array(
		'bootstrap'  => array( 'css/bootstrap.css' ),
		'animate'    => array( 'css/animate.min.css' ),
		'fonts'      => array( 'css/fonts.css' ),
		'header'     => array( 'css/header.css' ),
		'footer'     => array( 'css/footer.css' ),
		'main'       => array( 'css/main.css' ),
		'responsive' => array( 'css/responsive.css' ),
	),
	'scripts' => array(
		'wow'  => array( 'js/wow.min.js' ),
		'main' => array( 'js/main.js', array( 'jquery', 'wow' ) ),
	),
);
