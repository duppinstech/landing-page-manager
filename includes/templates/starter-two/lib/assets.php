<?php
/**
 * Front-end assets for the Starter Two template, in load order.
 *
 * Keys become handles (lpm-starter-two-<key>). The starter needs no JavaScript, and
 * 'cdn_assets' => false stops the plugin loading Google Fonts and Font Awesome: the
 * starter uses system fonts and no icons.
 *
 * @package Landing_Page_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'cdn_assets' => false,
	'styles'  => array(
		'main' => array( 'css/main.css' ),
	),
	'scripts' => array(),
);
