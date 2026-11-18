<?php
/**
 * Preview of the new theme for logged-in editors only.
 *
 * Open the site with ?neas_preview=1 to turn it on and ?neas_preview=0 to
 * turn it off (remembered in a cookie). Visitors are never affected.
 *
 * @package NEAS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const NEAS_PREVIEW_PARAM     = 'neas_preview';
const NEAS_PREVIEW_COOKIE    = 'neas_preview';
const NEAS_PREVIEW_HOME_SLUG = 'inicio';

function neas_is_preview_active() {
	static $active = null;
	if ( null !== $active ) {
		return $active;
	}
	if ( ! function_exists( 'wp_get_current_user' ) || ! did_action( 'plugins_loaded' ) ) {
		return false; // Too early to know who the user is; do not cache.
	}
	$active = false;
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || wp_doing_ajax() ) {
		return $active;
	}
	$request = isset( $_GET[ NEAS_PREVIEW_PARAM ] ) ? sanitize_text_field( wp_unslash( $_GET[ NEAS_PREVIEW_PARAM ] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification
	if ( null !== $request ) {
		$enable = ( '1' === $request );
		if ( ! headers_sent() ) {
			setcookie( NEAS_PREVIEW_COOKIE, $enable ? '1' : '', $enable ? time() + WEEK_IN_SECONDS : time() - HOUR_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true );
		}
		$_COOKIE[ NEAS_PREVIEW_COOKIE ] = $enable ? '1' : '';
	}
	if ( empty( $_COOKIE[ NEAS_PREVIEW_COOKIE ] ) || ! is_user_logged_in() || ! current_user_can( 'edit_pages' ) ) {
		return $active;
	}
	$active = neas_is_theme_installed_but_inactive();
	return $active;
}

function neas_is_theme_installed_but_inactive() {
	return wp_get_theme( NEAS_THEME_SLUG )->exists() && NEAS_THEME_SLUG !== get_option( 'stylesheet' );
}

function neas_get_page_id( $slug ) {
	$page = get_page_by_path( $slug, OBJECT, 'page' );
	return $page ? (int) $page->ID : 0;
}

foreach ( array( 'template', 'stylesheet' ) as $neas_theme_filter ) {
	add_filter(
		$neas_theme_filter,
		function ( $theme ) {
			return neas_is_preview_active() ? NEAS_THEME_SLUG : $theme;
		}
	);
}

// The new home page (still private) replaces the old one.
add_filter(
	'pre_option_page_on_front',
	function ( $value ) {
		if ( neas_is_preview_active() ) {
			$page_id = neas_get_page_id( NEAS_PREVIEW_HOME_SLUG );
			if ( $page_id ) {
				return $page_id;
			}
		}
		return $value;
	}
);
add_filter(
	'pre_option_show_on_front',
	function ( $value ) {
		return ( neas_is_preview_active() && neas_get_page_id( NEAS_PREVIEW_HOME_SLUG ) ) ? 'page' : $value;
	}
);

// Hide the "Privado:" prefix on private page titles.
add_filter(
	'private_title_format',
	function ( $format ) {
		return neas_is_preview_active() ? '%s' : $format;
	}
);

// The old lectures address leads to the new page.
add_action(
	'template_redirect',
	function () {
		if ( ! neas_is_preview_active() || ! is_404() ) {
			return;
		}
		$path = trim( (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '', PHP_URL_PATH ), '/' );
		if ( 'paletras-publicas' === $path ) {
			wp_safe_redirect( home_url( '/palestras/' ) );
			exit;
		}
	}
);

// Admin bar button to enter and leave the preview.
add_action(
	'admin_bar_menu',
	function ( $admin_bar ) {
		if ( ! current_user_can( 'edit_pages' ) || ! neas_is_theme_installed_but_inactive() ) {
			return;
		}
		$active = neas_is_preview_active();
		$admin_bar->add_node(
			array(
				'id'    => 'neas-preview',
				'title' => $active ? 'Sair da prévia do tema novo' : 'Ver o site com o tema novo',
				'href'  => home_url( '/?' . NEAS_PREVIEW_PARAM . '=' . ( $active ? '0' : '1' ) ),
				'meta'  => array( 'class' => $active ? 'neas-preview-active' : '' ),
			)
		);
	},
	100
);

add_action(
	'wp_head',
	function () {
		if ( ! neas_is_preview_active() ) {
			return;
		}
		echo '<style>#wpadminbar .neas-preview-active > .ab-item{background:#F6C324!important;color:#1F2340!important;font-weight:700}</style>';
	}
);
