<?php
/**
 * Plugin Name:       NEAS – Site
 * Description:       Cadastro de palestras públicas, próximas palestras, caixa de doação Pix, mapa e prévia do tema novo para o site do Núcleo Espírita Auta de Souza.
 * Version:           2.2.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            NEAS
 * License:           GPL-2.0-or-later
 * Text Domain:       neas-site
 *
 * @package NEAS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'NEAS_SITE_VERSION', '2.2.0' );
define( 'NEAS_SITE_URL', plugin_dir_url( __FILE__ ) );
define( 'NEAS_THEME_SLUG', 'neas-theme' );

require_once __DIR__ . '/includes/lectures.php';
require_once __DIR__ . '/includes/blocks.php';
require_once __DIR__ . '/includes/recordings.php';
require_once __DIR__ . '/includes/instagram.php';
require_once __DIR__ . '/includes/preview.php';
