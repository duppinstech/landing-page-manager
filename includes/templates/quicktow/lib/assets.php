<?php
/**
 * Front-end assets for the QuickTow template, in load order.
 *
 * Keys become handles (lpm-quicktow-<key>). The layout stylesheets are scoped to
 * their body classes, so loading all three is safe.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'styles'  => array(
		'main'       => array( 'css/main.css' ),
		'charmeck'   => array( 'css/charmeck-layout.css', array( 'main' ) ),
		'heavy-duty' => array( 'css/heavy-duty-layout.css', array( 'main' ) ),
	),
	'scripts' => array(
		'main' => array( 'js/main.js' ),
	),
);
