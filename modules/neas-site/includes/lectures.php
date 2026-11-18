<?php
/**
 * "Palestra" (public lecture) post type: registration, admin form and list
 * columns, and the query used by the blocks.
 *
 * @package NEAS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const NEAS_LECTURE_POST_TYPE = 'neas_lecture';

add_action( 'init', 'neas_register_lecture_post_type' );
function neas_register_lecture_post_type() {
	register_post_type(
		NEAS_LECTURE_POST_TYPE,
		array(
			'labels'          => array(
				'name'               => 'Palestras',
				'singular_name'      => 'Palestra',
				'menu_name'          => 'Palestras',
				'add_new'            => 'Adicionar palestra',
				'add_new_item'       => 'Adicionar nova palestra',
				'edit_item'          => 'Editar palestra',
				'new_item'           => 'Nova palestra',
				'view_item'          => 'Ver palestra',
				'search_items'       => 'Buscar palestras',
				'not_found'          => 'Nenhuma palestra encontrada',
				'not_found_in_trash' => 'Nenhuma palestra na lixeira',
				'all_items'          => 'Todas as palestras',
			),
			'public'          => true,
			'show_in_rest'    => true,
			'rest_base'       => 'lectures',
			'menu_position'   => 5,
			'menu_icon'       => 'dashicons-megaphone',
			'supports'        => array( 'title', 'custom-fields' ),
			'has_archive'     => false,
			'rewrite'         => array( 'slug' => 'palestra' ),
			'capability_type' => 'post',
		)
	);

	$fields = array(
		'neas_date'          => 'Data da palestra (AAAA-MM-DD)',
		'neas_time'          => 'Horário (HH:MM)',
		'neas_speaker'       => 'Expositor(a)',
		'neas_recording_url' => 'Link da gravação no Instagram',
	);
	foreach ( $fields as $key => $description ) {
		register_post_meta(
			NEAS_LECTURE_POST_TYPE,
			$key,
			array(
				'type'              => 'string',
				'description'       => $description,
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}
}

/* -------------------------------------------------------------------------
 * Admin form (classic editor, no blocks)
 * ---------------------------------------------------------------------- */

add_filter(
	'use_block_editor_for_post_type',
	function ( $use_block_editor, $post_type ) {
		return NEAS_LECTURE_POST_TYPE === $post_type ? false : $use_block_editor;
	},
	10,
	2
);

add_filter(
	'enter_title_here',
	function ( $text, $post ) {
		return ( $post && NEAS_LECTURE_POST_TYPE === $post->post_type ) ? 'Tema da palestra' : $text;
	},
	10,
	2
);

add_action( 'add_meta_boxes', 'neas_add_lecture_meta_box' );
function neas_add_lecture_meta_box() {
	add_meta_box( 'neas_lecture_details', 'Dados da palestra', 'neas_render_lecture_meta_box', NEAS_LECTURE_POST_TYPE, 'normal', 'high' );
	// The technical "Custom Fields" box is not needed by volunteers.
	remove_meta_box( 'postcustom', NEAS_LECTURE_POST_TYPE, 'normal' );
}

function neas_render_lecture_meta_box( $post ) {
	wp_nonce_field( 'neas_save_lecture', 'neas_lecture_nonce' );
	$date    = get_post_meta( $post->ID, 'neas_date', true );
	$time    = get_post_meta( $post->ID, 'neas_time', true );
	$speaker = get_post_meta( $post->ID, 'neas_speaker', true );
	$video   = get_post_meta( $post->ID, 'neas_recording_url', true );
	if ( '' === $time ) {
		$time = '20:00';
	}
	?>
	<style>
		.neas-fields { display: grid; gap: 16px; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-top: 8px; }
		.neas-fields label { display: block; font-weight: 600; margin-bottom: 4px; }
		.neas-fields input { width: 100%; font-size: 15px; padding: 6px 8px; }
		.neas-hint { color: #50575e; margin: 12px 0 0; }
	</style>
	<div class="neas-fields">
		<p>
			<label for="neas_date">Data</label>
			<input type="date" id="neas_date" name="neas_date" value="<?php echo esc_attr( $date ); ?>" required>
		</p>
		<p>
			<label for="neas_time">Horário</label>
			<input type="time" id="neas_time" name="neas_time" value="<?php echo esc_attr( $time ); ?>">
		</p>
		<p>
			<label for="neas_speaker">Expositor(a)</label>
			<input type="text" id="neas_speaker" name="neas_speaker" value="<?php echo esc_attr( $speaker ); ?>" placeholder="Ex.: Maria José Carvalho">
		</p>
	</div>
	<p>
		<label for="neas_recording_url"><strong>Link da gravação no Instagram</strong> (opcional)</label><br>
		<input type="url" id="neas_recording_url" name="neas_recording_url" value="<?php echo esc_attr( $video ); ?>" placeholder="https://www.instagram.com/reel/..." style="width: 100%; font-size: 15px; padding: 6px 8px;">
	</p>
	<p class="neas-hint">Escreva o tema da palestra no título acima. Depois da data, a palestra sai sozinha da página inicial. Quando a gravação for publicada no Instagram, cole o link dela acima para aparecer em "Palestras anteriores".</p>
	<?php
}

add_action( 'save_post_' . NEAS_LECTURE_POST_TYPE, 'neas_save_lecture' );
function neas_save_lecture( $post_id ) {
	if ( ! isset( $_POST['neas_lecture_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['neas_lecture_nonce'] ) ), 'neas_save_lecture' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['neas_date'] ) ) {
		$date = sanitize_text_field( wp_unslash( $_POST['neas_date'] ) );
		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			update_post_meta( $post_id, 'neas_date', $date );
		}
	}
	if ( isset( $_POST['neas_time'] ) ) {
		$time = sanitize_text_field( wp_unslash( $_POST['neas_time'] ) );
		if ( preg_match( '/^\d{2}:\d{2}$/', $time ) ) {
			update_post_meta( $post_id, 'neas_time', $time );
		}
	}
	if ( isset( $_POST['neas_speaker'] ) ) {
		update_post_meta( $post_id, 'neas_speaker', sanitize_text_field( wp_unslash( $_POST['neas_speaker'] ) ) );
	}
	if ( isset( $_POST['neas_recording_url'] ) ) {
		$url = trim( sanitize_text_field( wp_unslash( $_POST['neas_recording_url'] ) ) );
		if ( '' === $url ) {
			delete_post_meta( $post_id, 'neas_recording_url' );
		} elseif ( neas_instagram_code( $url ) ) {
			update_post_meta( $post_id, 'neas_recording_url', esc_url_raw( $url ) );
		}
	}
}

/**
 * Shortcode ("DPjaD7PDRAi") of an Instagram reel or post link, or '' if it
 * is not one. Accepts instagram.com/reel/CODE/, instagram.com/p/CODE/ and
 * instagram.com/<account>/reel/CODE/.
 */
function neas_instagram_code( $url ) {
	if ( ! preg_match( '#^https?://(?:www\.)?instagram\.com/(?:[A-Za-z0-9_.]+/)?(?:reel|p|tv)/([A-Za-z0-9_-]+)#', (string) $url, $matches ) ) {
		return '';
	}
	return $matches[1];
}

/* -------------------------------------------------------------------------
 * Admin list columns
 * ---------------------------------------------------------------------- */

add_filter(
	'manage_' . NEAS_LECTURE_POST_TYPE . '_posts_columns',
	function ( $columns ) {
		$new_columns = array();
		foreach ( $columns as $key => $label ) {
			if ( 'date' === $key ) {
				continue;
			}
			$new_columns[ $key ] = ( 'title' === $key ) ? 'Tema' : $label;
			if ( 'title' === $key ) {
				$new_columns['neas_date']    = 'Data';
				$new_columns['neas_time']    = 'Horário';
				$new_columns['neas_speaker'] = 'Expositor(a)';
				$new_columns['neas_recording_url'] = 'Gravação';
			}
		}
		return $new_columns;
	}
);

add_action(
	'manage_' . NEAS_LECTURE_POST_TYPE . '_posts_custom_column',
	function ( $column, $post_id ) {
		$value = get_post_meta( $post_id, $column, true );
		if ( 'neas_date' === $column && $value ) {
			$value = neas_weekday( $value, true ) . ', ' . mysql2date( 'd/m/Y', $value );
		}
		if ( 'neas_recording_url' === $column ) {
			$value = $value ? '✓' : '';
		}
		echo esc_html( $value );
	},
	10,
	2
);

add_filter(
	'manage_edit-' . NEAS_LECTURE_POST_TYPE . '_sortable_columns',
	function ( $columns ) {
		$columns['neas_date'] = 'neas_date';
		return $columns;
	}
);

add_action(
	'pre_get_posts',
	function ( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() || NEAS_LECTURE_POST_TYPE !== $query->get( 'post_type' ) ) {
			return;
		}
		$orderby = $query->get( 'orderby' );
		if ( ! $orderby || 'neas_date' === $orderby ) {
			$query->set( 'meta_key', 'neas_date' );
			$query->set( 'orderby', 'meta_value' );
			if ( ! $query->get( 'order' ) || ! isset( $_GET['order'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$query->set( 'order', 'DESC' );
			}
		}
	}
);

/* -------------------------------------------------------------------------
 * Date helpers (Portuguese names, independent of the site locale)
 * ---------------------------------------------------------------------- */

function neas_weekday( $date, $short = false ) {
	$long_names  = array( 'Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado' );
	$short_names = array( 'Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb' );
	$timestamp   = strtotime( $date . ' 12:00:00' );
	if ( ! $timestamp ) {
		return '';
	}
	$index = (int) gmdate( 'w', $timestamp );
	return $short ? $short_names[ $index ] : $long_names[ $index ];
}

function neas_month( $date, $short = false ) {
	$long_names  = array( 'janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro' );
	$short_names = array( 'jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez' );
	$index       = (int) substr( $date, 5, 2 ) - 1;
	if ( $index < 0 || $index > 11 ) {
		return '';
	}
	return $short ? $short_names[ $index ] : $long_names[ $index ];
}

// "20:00" → "20h", "16:30" → "16h30".
function neas_short_time( $time ) {
	if ( ! preg_match( '/^(\d{2}):(\d{2})$/', $time, $matches ) ) {
		return $time;
	}
	return (int) $matches[1] . 'h' . ( '00' === $matches[2] ? '' : $matches[2] );
}

/**
 * Upcoming published lectures (today included), soonest first.
 *
 * @param int $count Maximum number of lectures; -1 for all.
 * @return array[] Lecture data ready for display.
 */
function neas_get_upcoming_lectures( $count ) {
	return neas_query_lectures( $count, '>=', 'ASC', false );
}

function neas_query_lectures( $count, $date_compare, $order, $with_recording ) {
	$meta_query = array(
		'date' => array(
			'key'     => 'neas_date',
			'value'   => current_time( 'Y-m-d' ),
			'compare' => $date_compare,
			'type'    => 'DATE',
		),
		'time' => array(
			'key'     => 'neas_time',
			'compare' => 'EXISTS',
		),
	);
	if ( $with_recording ) {
		$meta_query['recording'] = array(
			'key'     => 'neas_recording_url',
			'value'   => '',
			'compare' => '!=',
		);
	}
	$query = new WP_Query(
		array(
			'post_type'      => NEAS_LECTURE_POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => (int) $count,
			'no_found_rows'  => true,
			'meta_query'     => $meta_query, // phpcs:ignore WordPress.DB.SlowDBQuery
			'orderby'        => array(
				'date' => $order,
				'time' => $order,
			),
		)
	);
	$lectures = array();
	foreach ( $query->posts as $post ) {
		$date       = get_post_meta( $post->ID, 'neas_date', true );
		$lectures[] = array(
			'topic'         => get_the_title( $post ),
			'date'          => $date,
			'day'           => substr( $date, 8, 2 ),
			'weekday'       => neas_weekday( $date ),
			'weekday_short' => neas_weekday( $date, true ),
			'month'         => neas_month( $date ),
			'month_short'   => neas_month( $date, true ),
			'time'          => neas_short_time( get_post_meta( $post->ID, 'neas_time', true ) ),
			'raw_time'      => get_post_meta( $post->ID, 'neas_time', true ),
			'speaker'       => get_post_meta( $post->ID, 'neas_speaker', true ),
			'recording_url' => get_post_meta( $post->ID, 'neas_recording_url', true ),
		);
	}
	return $lectures;
}
