<?php
/**
 * NEAS theme functions.
 *
 * @package NEAS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'after_setup_theme',
	function () {
		add_editor_style( 'style.css' );
	}
);

add_action(
	'wp_enqueue_scripts',
	function () {
		$theme = wp_get_theme();
		wp_enqueue_style( 'neas-theme', get_stylesheet_uri(), array(), $theme->get( 'Version' ) );
	}
);

add_action(
	'init',
	function () {
		register_block_pattern_category( 'neas', array( 'label' => 'NEAS' ) );
	}
);
