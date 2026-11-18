<?php
/**
 * Fallback for [instagram-feed] while the Smash Balloon plugin is not
 * installed: visitors see nothing, editors see a reminder.
 *
 * @package NEAS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'init',
	function () {
		if ( shortcode_exists( 'instagram-feed' ) ) {
			return;
		}
		add_shortcode(
			'instagram-feed',
			function () {
				if ( ! current_user_can( 'edit_posts' ) ) {
					return '';
				}
				return '<p class="neas-instagram-placeholder">As publicações do Instagram aparecerão aqui depois que o plugin <strong>Smash Balloon Instagram Feed</strong> for instalado e conectado à conta. (Este aviso só aparece para quem está logado.)</p>';
			}
		);
	},
	99
);
