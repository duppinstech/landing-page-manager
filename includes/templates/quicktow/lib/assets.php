<?php
/**
 * Front-end assets for the QuickTow template, in load order.
 *
 * Keys become handles (lpm-quicktow-<key>) and appear in page HTML, so they stay
 * client-neutral. The layout stylesheets are scoped to their body classes, so
 * loading all three is safe.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'styles'  => array(
		'main'       => array( 'css/main.css' ),
		'style-2'    => array( 'css/charmeck-layout.css', array( 'main' ) ),
		'style-3'    => array( 'css/heavy-duty-layout.css', array( 'main' ) ),
	),
	'scripts' => array(
		'main' => array( 'js/main.js' ),
	),
);
