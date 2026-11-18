<?php
/**
 * Lecture recordings from the NEAS Instagram reels.
 *
 * The reels are fetched once a day from the Instagram Graph API (system-user
 * token of the NEAS business portfolio), cached in the database and matched to the
 * registered lectures:
 * 1. the manual "recording link" of a lecture always wins;
 * 2. otherwise the reel whose caption says "realizada no dia DD/MM/AAAA"
 *    with the lecture date, or the first reel posted after the lecture
 *    started (within NEAS_RECORDING_WINDOW);
 * 3. reels that match no lecture but have a lecture caption ("Palestra
 *    pública realizada no dia … por … sob a luz do tema …") are shown with
 *    the date, speaker and topic read from the caption.
 * Editors can hide any reel in Palestras → Instagram.
 *
 * @package NEAS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const NEAS_GRAPH_API          = 'https://graph.facebook.com/v25.0/';
const NEAS_INSTAGRAM_REELS    = 'https://www.instagram.com/neautadesouza/reels/';
const NEAS_RECORDING_WINDOW   = 129600; // 36 hours.
const NEAS_SYNC_HOOK          = 'neas_instagram_sync';
const NEAS_SYNC_TIME          = '22:30'; // After the evening lectures (Sunday ones end ~17:30).
const NEAS_OPTION_TOKEN       = 'neas_instagram_token';
const NEAS_OPTION_ACCOUNT     = 'neas_instagram_account';
const NEAS_OPTION_REELS       = 'neas_instagram_reels';
const NEAS_OPTION_HIDDEN      = 'neas_instagram_hidden';
const NEAS_OPTION_SYNC_STATUS = 'neas_instagram_sync_status';

/* -------------------------------------------------------------------------
 * Instagram API
 * ---------------------------------------------------------------------- */

/**
 * GET request to the Graph API.
 *
 * @return array|WP_Error Decoded response.
 */
function neas_graph_get( $path, $args = array() ) {
	$token = get_option( NEAS_OPTION_TOKEN );
	if ( ! $token ) {
		return new WP_Error( 'neas_no_token', 'A chave de acesso do Instagram não foi configurada.' );
	}
	$url      = ( 0 === strpos( $path, 'https://' ) ) ? $path : add_query_arg( array_merge( $args, array( 'access_token' => $token ) ), NEAS_GRAPH_API . $path );
	$response = wp_remote_get( $url, array( 'timeout' => 20 ) );
	if ( is_wp_error( $response ) ) {
		return $response;
	}
	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( ! is_array( $data ) ) {
		return new WP_Error( 'neas_bad_response', 'O Instagram devolveu uma resposta inesperada.' );
	}
	if ( isset( $data['error'] ) ) {
		$message = isset( $data['error']['message'] ) ? $data['error']['message'] : 'erro desconhecido';
		return new WP_Error( 'neas_api_error', 'Erro do Instagram: ' . $message );
	}
	return $data;
}

/**
 * Instagram account assigned to the token, as array( 'id', 'username' ).
 *
 * @return array|WP_Error
 */
function neas_discover_instagram_account() {
	$assigned = neas_graph_get( 'me/assigned_instagram_accounts' );
	if ( is_wp_error( $assigned ) ) {
		return $assigned;
	}
	if ( empty( $assigned['data'][0]['id'] ) ) {
		return new WP_Error( 'neas_no_account', 'A chave funciona, mas nenhuma conta do Instagram está ligada a ela.' );
	}
	$account = neas_graph_get( $assigned['data'][0]['id'], array( 'fields' => 'username' ) );
	if ( is_wp_error( $account ) ) {
		return $account;
	}
	return array(
		'id'       => $account['id'],
		'username' => isset( $account['username'] ) ? $account['username'] : '',
	);
}

/**
 * Fetches reels and merges them into the cache.
 *
 * @param bool $full True to walk the whole account; false for the latest page only.
 * @return int|WP_Error Number of reels in the cache.
 */
function neas_sync_reels( $full = false ) {
	$account = get_option( NEAS_OPTION_ACCOUNT );
	if ( empty( $account['id'] ) ) {
		$account = neas_discover_instagram_account();
		if ( is_wp_error( $account ) ) {
			neas_save_sync_status( $account );
			return $account;
		}
		update_option( NEAS_OPTION_ACCOUNT, $account, false );
	}

	$reels = neas_get_cached_reels();
	$page  = neas_graph_get(
		$account['id'] . '/media',
		array(
			'fields' => 'id,media_product_type,permalink,timestamp,caption',
			'limit'  => $full ? 100 : 50,
		)
	);
	for ( $pages = 1; ; $pages++ ) {
		if ( is_wp_error( $page ) ) {
			neas_save_sync_status( $page );
			return $page;
		}
		foreach ( $page['data'] as $media ) {
			if ( empty( $media['media_product_type'] ) || 'REELS' !== $media['media_product_type'] ) {
				continue;
			}
			$code = neas_instagram_code( $media['permalink'] );
			if ( ! $code ) {
				continue;
			}
			$reels[ $code ] = array(
				'code'      => $code,
				'permalink' => $media['permalink'],
				'timestamp' => strtotime( $media['timestamp'] ),
				'caption'   => isset( $media['caption'] ) ? mb_substr( $media['caption'], 0, 600 ) : '',
			);
		}
		if ( ! $full || empty( $page['paging']['next'] ) || $pages >= 30 ) {
			break;
		}
		$page = neas_graph_get( $page['paging']['next'] );
	}

	uasort(
		$reels,
		function ( $a, $b ) {
			return $b['timestamp'] - $a['timestamp'];
		}
	);
	// Stored as ASCII-only JSON: the site database does not accept emoji
	// (utf8, not utf8mb4) and would silently drop the whole value.
	$json = wp_json_encode( $reels );
	if ( ! $json || ( get_option( NEAS_OPTION_REELS ) !== $json && ! update_option( NEAS_OPTION_REELS, $json, false ) ) ) {
		$error = new WP_Error( 'neas_save_failed', 'Os vídeos foram encontrados, mas não puderam ser salvos no banco de dados.' );
		neas_save_sync_status( $error );
		return $error;
	}
	neas_save_sync_status( true );
	return count( $reels );
}

function neas_get_cached_reels() {
	$value = get_option( NEAS_OPTION_REELS, '' );
	$reels = is_string( $value ) ? json_decode( $value, true ) : $value;
	return is_array( $reels ) ? $reels : array();
}

function neas_save_sync_status( $result ) {
	update_option(
		NEAS_OPTION_SYNC_STATUS,
		array(
			'time'  => time(),
			'error' => is_wp_error( $result ) ? $result->get_error_message() : '',
		),
		false
	);
}

// Daily sync at NEAS_SYNC_TIME (site time zone) while a token is configured.
add_action( NEAS_SYNC_HOOK, 'neas_sync_reels' );
add_action(
	'init',
	function () {
		$has_token = (bool) get_option( NEAS_OPTION_TOKEN );
		if ( wp_next_scheduled( NEAS_SYNC_HOOK ) && ( ! $has_token || 'daily' !== wp_get_schedule( NEAS_SYNC_HOOK ) ) ) {
			wp_clear_scheduled_hook( NEAS_SYNC_HOOK );
		}
		if ( $has_token && ! wp_next_scheduled( NEAS_SYNC_HOOK ) ) {
			$next = new DateTime( 'today ' . NEAS_SYNC_TIME, wp_timezone() );
			if ( $next->getTimestamp() <= time() ) {
				$next->modify( '+1 day' );
			}
			wp_schedule_event( $next->getTimestamp(), 'daily', NEAS_SYNC_HOOK );
		}
	}
);

/* -------------------------------------------------------------------------
 * Matching reels to lectures
 * ---------------------------------------------------------------------- */

/**
 * Lecture details written in a reel caption:
 * "Palestra pública realizada no dia 25/10/2026 por Luiz Alberto sob a luz do tema: A coragem de ser você."
 *
 * @return array Keys 'date' (Y-m-d), 'speaker' and 'topic'; missing parts are ''.
 */
function neas_parse_reel_caption( $caption ) {
	$details = array(
		'date'    => '',
		'speaker' => '',
		'topic'   => '',
	);
	$first_line = trim( strtok( (string) $caption, "\n" ) );
	if ( preg_match( '#realizada\s+(?:no\s+dia|em)\s+(\d{1,2})/(\d{1,2})/(\d{4})#iu', $first_line, $matches ) ) {
		$details['date'] = sprintf( '%04d-%02d-%02d', $matches[3], $matches[2], $matches[1] );
	}
	if ( preg_match( '#\bpor\s+(.+?)\s*,?\s+sob\s+a\s+luz\s+do\s+tema\s*:?\s*(.+)$#iu', $first_line, $matches ) ) {
		$details['speaker'] = trim( $matches[1] );
		$details['topic']   = trim( preg_replace( '/\s+#.*$/u', '', $matches[2] ), " .\t" );
	}
	return $details;
}

/**
 * Recordings to show, most recent first, plus how each reel was used.
 *
 * @return array( 'items' => array[], 'reels' => array code => description )
 */
function neas_build_recordings() {
	$hidden    = get_option( NEAS_OPTION_HIDDEN, array() );
	$all_reels = neas_get_cached_reels();
	$reels     = array_diff_key( $all_reels, array_flip( $hidden ) );
	$used      = array();
	$items     = array();
	$usage     = array();

	foreach ( $reels as $code => $reel ) {
		$reels[ $code ]['details'] = neas_parse_reel_caption( $reel['caption'] );
	}

	foreach ( neas_query_lectures( -1, '<', 'DESC', false ) as $lecture ) {
		$code = neas_instagram_code( $lecture['recording_url'] );
		if ( ! $code ) {
			$code = neas_find_reel_for_lecture( $lecture, $reels, $used );
		}
		if ( ! $code || isset( $used[ $code ] ) ) {
			continue;
		}
		$used[ $code ]  = true;
		$usage[ $code ] = 'Palestra cadastrada: ' . $lecture['topic'];
		$items[]        = array(
			'code'    => $code,
			'date'    => $lecture['date'],
			'topic'   => $lecture['topic'],
			'speaker' => $lecture['speaker'],
		);
	}

	foreach ( $reels as $code => $reel ) {
		if ( isset( $used[ $code ] ) ) {
			continue;
		}
		$details = $reel['details'];
		if ( '' === $details['topic'] ) {
			$usage[ $code ] = '';
			continue;
		}
		$usage[ $code ] = 'Pela legenda: ' . $details['topic'];
		$items[]        = array(
			'code'    => $code,
			'date'    => $details['date'] ? $details['date'] : wp_date( 'Y-m-d', $reel['timestamp'] ),
			'topic'   => $details['topic'],
			'speaker' => $details['speaker'],
		);
	}

	usort(
		$items,
		function ( $a, $b ) {
			return strcmp( $b['date'], $a['date'] );
		}
	);
	return array(
		'items' => $items,
		'reels' => $usage,
	);
}

function neas_find_reel_for_lecture( $lecture, $reels, $used ) {
	// A caption with the lecture date is the strongest signal.
	foreach ( $reels as $code => $reel ) {
		if ( ! isset( $used[ $code ] ) && $reel['details']['date'] === $lecture['date'] ) {
			return $code;
		}
	}
	// Otherwise the first reel without a (different) caption date posted after the lecture started.
	$start = strtotime( $lecture['date'] . ' ' . $lecture['raw_time'] . ' ' . wp_timezone_string() );
	if ( ! $start ) {
		return '';
	}
	$best = '';
	foreach ( $reels as $code => $reel ) {
		if ( isset( $used[ $code ] ) || '' !== $reel['details']['date'] ) {
			continue;
		}
		if ( $reel['timestamp'] >= $start && $reel['timestamp'] <= $start + NEAS_RECORDING_WINDOW
			&& ( '' === $best || $reel['timestamp'] < $reels[ $best ]['timestamp'] ) ) {
			$best = $code;
		}
	}
	return $best;
}

/* -------------------------------------------------------------------------
 * Admin page: Palestras → Instagram
 * ---------------------------------------------------------------------- */

add_action(
	'admin_menu',
	function () {
		add_submenu_page(
			'edit.php?post_type=' . NEAS_LECTURE_POST_TYPE,
			'Gravações do Instagram',
			'Instagram',
			'manage_options',
			'neas-instagram',
			'neas_render_instagram_page'
		);
	}
);

add_action( 'admin_post_neas_instagram', 'neas_handle_instagram_form' );
function neas_handle_instagram_form() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Sem permissão.' );
	}
	check_admin_referer( 'neas_instagram' );
	$action  = isset( $_POST['neas_action'] ) ? sanitize_key( wp_unslash( $_POST['neas_action'] ) ) : '';
	$message = '';

	if ( 'save_token' === $action ) {
		$token = isset( $_POST['neas_token'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['neas_token'] ) ) ) : '';
		if ( '' !== $token ) {
			update_option( NEAS_OPTION_TOKEN, $token, false );
			delete_option( NEAS_OPTION_ACCOUNT );
			$result  = neas_sync_reels( true );
			$message = is_wp_error( $result ) ? $result->get_error_message() : 'Chave salva. ' . $result . ' vídeos encontrados.';
		}
	} elseif ( 'remove_token' === $action ) {
		delete_option( NEAS_OPTION_TOKEN );
		delete_option( NEAS_OPTION_ACCOUNT );
		$message = 'Chave removida. Os vídeos já encontrados continuam no site.';
	} elseif ( 'sync' === $action ) {
		$result  = neas_sync_reels( true );
		$message = is_wp_error( $result ) ? $result->get_error_message() : 'Pronto: ' . $result . ' vídeos encontrados.';
	} elseif ( 'toggle_hidden' === $action && isset( $_POST['neas_code'] ) ) {
		$code   = sanitize_text_field( wp_unslash( $_POST['neas_code'] ) );
		$hidden = get_option( NEAS_OPTION_HIDDEN, array() );
		if ( in_array( $code, $hidden, true ) ) {
			$hidden  = array_values( array_diff( $hidden, array( $code ) ) );
			$message = 'O vídeo voltou a aparecer no site.';
		} else {
			$hidden[] = $code;
			$message  = 'O vídeo não aparece mais no site.';
		}
		update_option( NEAS_OPTION_HIDDEN, $hidden, false );
	}

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'         => 'neas-instagram',
				'neas_message' => rawurlencode( $message ),
			),
			admin_url( 'edit.php?post_type=' . NEAS_LECTURE_POST_TYPE )
		)
	);
	exit;
}

function neas_render_instagram_page() {
	$account    = get_option( NEAS_OPTION_ACCOUNT );
	$status     = get_option( NEAS_OPTION_SYNC_STATUS );
	$has_token  = (bool) get_option( NEAS_OPTION_TOKEN );
	$hidden     = array_flip( get_option( NEAS_OPTION_HIDDEN, array() ) );
	$all_reels  = neas_get_cached_reels();
	$recordings = neas_build_recordings();
	$message    = isset( $_GET['neas_message'] ) ? sanitize_text_field( wp_unslash( $_GET['neas_message'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$form_start = '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="neas_instagram">' . wp_nonce_field( 'neas_instagram', '_wpnonce', true, false );
	?>
	<div class="wrap">
		<h1>Gravações do Instagram</h1>
		<?php if ( $message ) : ?>
			<div class="notice notice-info"><p><?php echo esc_html( $message ); ?></p></div>
		<?php endif; ?>
		<?php if ( ! empty( $status['error'] ) ) : ?>
			<div class="notice notice-error"><p><strong>A última busca falhou:</strong> <?php echo esc_html( $status['error'] ); ?></p></div>
		<?php endif; ?>

		<p>O site busca sozinho, todo dia às <?php echo esc_html( str_replace( ':', 'h', NEAS_SYNC_TIME ) ); ?>, os vídeos (reels) do Instagram do NEAS. Cada gravação aparece em <a href="<?php echo esc_url( home_url( '/palestras-anteriores/' ) ); ?>">Palestras anteriores</a> junto da palestra cadastrada no mesmo dia. Se o vídeo certo não for encontrado, cole o link dele no cadastro da palestra.</p>

		<h2>Conexão</h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">Conta</th>
				<td><?php echo ! empty( $account['username'] ) ? '@' . esc_html( $account['username'] ) : 'Não conectada'; ?></td>
			</tr>
			<tr>
				<th scope="row">Última busca</th>
				<td><?php echo ! empty( $status['time'] ) ? esc_html( wp_date( 'd/m/Y \à\s H:i', $status['time'] ) ) : 'Nunca'; ?></td>
			</tr>
		</table>
		<?php echo $form_start; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<input type="hidden" name="neas_action" value="save_token">
			<p>
				<label for="neas_token"><strong><?php echo $has_token ? 'Trocar a chave de acesso' : 'Chave de acesso'; ?></strong></label><br>
				<input type="password" id="neas_token" name="neas_token" class="regular-text" autocomplete="off" placeholder="<?php echo $has_token ? 'Chave salva — deixe em branco para manter' : 'Cole aqui a chave gerada no Meta'; ?>">
				<?php submit_button( 'Salvar chave', 'primary', 'submit', false ); ?>
			</p>
		</form>
		<?php if ( $has_token ) : ?>
			<div style="display: flex; gap: 8px;">
				<?php echo $form_start; // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<input type="hidden" name="neas_action" value="sync">
					<?php submit_button( 'Buscar vídeos agora', 'secondary', 'submit', false ); ?>
				</form>
				<?php echo $form_start; // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<input type="hidden" name="neas_action" value="remove_token">
					<?php submit_button( 'Remover chave', 'delete', 'submit', false ); ?>
				</form>
			</div>
		<?php endif; ?>

		<?php if ( $all_reels ) : ?>
			<h2>Vídeos encontrados (<?php echo (int) count( $all_reels ); ?>)</h2>
			<p>Use "Esconder" nos vídeos que não devem aparecer no site. Vídeos sem palestra associada e sem legenda de palestra já não aparecem ("—").</p>
				<table class="widefat striped">
					<thead><tr><th>Publicado em</th><th>Vídeo</th><th>Legenda</th><th>No site</th><th></th></tr></thead>
					<tbody>
					<?php foreach ( $all_reels as $code => $reel ) : ?>
						<?php
						$is_hidden = isset( $hidden[ $code ] );
						$usage     = isset( $recordings['reels'][ $code ] ) ? $recordings['reels'][ $code ] : '';
						?>
						<tr>
							<td><?php echo esc_html( wp_date( 'd/m/Y H:i', $reel['timestamp'] ) ); ?></td>
							<td><a href="<?php echo esc_url( $reel['permalink'] ); ?>" target="_blank" rel="noopener">Abrir</a></td>
							<td><?php echo esc_html( wp_trim_words( $reel['caption'], 18 ) ); ?></td>
							<td><?php echo $is_hidden ? '<em>Escondido</em>' : ( $usage ? esc_html( $usage ) : '—' ); ?></td>
							<td>
								<?php echo $form_start; // phpcs:ignore WordPress.Security.EscapeOutput ?>
									<input type="hidden" name="neas_action" value="toggle_hidden">
									<input type="hidden" name="neas_code" value="<?php echo esc_attr( $code ); ?>">
									<button type="submit" class="button button-small"><?php echo $is_hidden ? 'Mostrar' : 'Esconder'; ?></button>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
		<?php endif; ?>
	</div>
	<?php
}
