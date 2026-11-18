<?php
/**
 * Dynamic blocks (rendered on the server, also inside the editor through
 * ServerSideRender): next lecture, upcoming lectures, full schedule, Pix and map.
 *
 * @package NEAS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const NEAS_PIX_KEY         = '09790155000143';
const NEAS_PIX_KEY_DISPLAY = '09.790.155/0001-43';
const NEAS_ADDRESS         = 'Rua Itaimbé, 575, Ipsep, Recife - PE';

add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_style( 'neas-site' );
	}
);

add_action( 'init', 'neas_register_blocks', 20 );
function neas_register_blocks() {
	wp_register_style( 'neas-site', NEAS_SITE_URL . 'assets/style.css', array(), NEAS_SITE_VERSION );
	wp_register_script( 'neas-pix', NEAS_SITE_URL . 'assets/pix.js', array(), NEAS_SITE_VERSION, true );
	wp_register_script(
		'neas-site-blocks',
		NEAS_SITE_URL . 'assets/blocks.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render' ),
		NEAS_SITE_VERSION,
		true
	);

	$common = array(
		'api_version'   => 3,
		'category'      => 'widgets',
		'editor_script' => 'neas-site-blocks',
		'style'         => 'neas-site',
		'supports'      => array( 'html' => false ),
	);

	register_block_type(
		'neas/next-lecture',
		array_merge(
			$common,
			array(
				'title'           => 'Próxima palestra (cartão)',
				'description'     => 'Mostra automaticamente a próxima palestra cadastrada.',
				'icon'            => 'megaphone',
				'render_callback' => 'neas_render_next_lecture',
			)
		)
	);

	register_block_type(
		'neas/upcoming-lectures',
		array_merge(
			$common,
			array(
				'title'           => 'Próximas palestras (lista)',
				'description'     => 'Lista automática das próximas palestras cadastradas.',
				'icon'            => 'list-view',
				'attributes'      => array(
					'count' => array(
						'type'    => 'number',
						'default' => 6,
					),
				),
				'render_callback' => 'neas_render_upcoming_lectures',
			)
		)
	);

	register_block_type(
		'neas/lecture-schedule',
		array_merge(
			$common,
			array(
				'title'           => 'Programação completa de palestras',
				'description'     => 'Todas as próximas palestras, separadas por mês.',
				'icon'            => 'calendar-alt',
				'render_callback' => 'neas_render_lecture_schedule',
			)
		)
	);

	register_block_type(
		'neas/past-lectures',
		array_merge(
			$common,
			array(
				'title'           => 'Palestras anteriores (gravações)',
				'description'     => 'Palestras que já aconteceram e têm gravação, com o vídeo do Instagram.',
				'icon'            => 'video-alt3',
				'attributes'      => array(
					'count' => array(
						'type'    => 'number',
						'default' => 24,
					),
				),
				'render_callback' => 'neas_render_past_lectures',
			)
		)
	);

	register_block_type(
		'neas/pix',
		array_merge(
			$common,
			array(
				'title'           => 'Doação Pix',
				'description'     => 'QR Code, chave Pix e botão para copiar a chave.',
				'icon'            => 'heart',
				'view_script'     => 'neas-pix',
				'attributes'      => array(
					'key'     => array(
						'type'    => 'string',
						'default' => NEAS_PIX_KEY,
					),
					'display' => array(
						'type'    => 'string',
						'default' => NEAS_PIX_KEY_DISPLAY,
					),
					'qr'      => array(
						'type'    => 'string',
						'default' => '',
					),
				),
				'render_callback' => 'neas_render_pix',
			)
		)
	);

	register_block_type(
		'neas/map',
		array_merge(
			$common,
			array(
				'title'           => 'Mapa (Google Maps)',
				'description'     => 'Mapa com o endereço da casa.',
				'icon'            => 'location',
				'attributes'      => array(
					'address' => array(
						'type'    => 'string',
						'default' => NEAS_ADDRESS,
					),
				),
				'render_callback' => 'neas_render_map',
			)
		)
	);
}

/* -------------------------------------------------------------------------
 * Render callbacks
 * ---------------------------------------------------------------------- */

function neas_render_next_lecture() {
	$lectures = neas_get_upcoming_lectures( 1 );
	ob_start();
	echo '<div class="neas-next">';
	echo '<p class="neas-eyebrow">Próxima palestra pública</p>';
	if ( empty( $lectures ) ) {
		echo '<p class="neas-next-empty">A programação do próximo mês será publicada em breve.</p>';
	} else {
		$lecture = $lectures[0];
		echo '<div class="neas-next-body">';
		echo '<div class="neas-date-card"><span class="neas-date-weekday">' . esc_html( $lecture['weekday'] ) . '</span><span class="neas-date-day">' . esc_html( $lecture['day'] ) . '</span><span class="neas-date-month">' . esc_html( $lecture['month_short'] . ' · ' . $lecture['time'] ) . '</span></div>';
		echo '<div class="neas-next-text"><h3 class="neas-next-topic">' . esc_html( $lecture['topic'] ) . '</h3>';
		if ( $lecture['speaker'] ) {
			echo '<p class="neas-next-speaker">com ' . esc_html( $lecture['speaker'] ) . '</p>';
		}
		echo '</div></div>';
	}
	echo '<div class="neas-next-footer"><span><strong>Ter, Qui e Sáb</strong> às 20h</span><span><strong>Domingo</strong> às 16h30</span></div>';
	echo '<p class="neas-next-note">Entrada gratuita, aberta a todos.</p>';
	echo '</div>';
	return ob_get_clean();
}

function neas_render_upcoming_lectures( $attributes ) {
	$count = isset( $attributes['count'] ) ? (int) $attributes['count'] : 6;
	return neas_render_lecture_list( neas_get_upcoming_lectures( $count ), false );
}

function neas_render_lecture_schedule() {
	return neas_render_lecture_list( neas_get_upcoming_lectures( -1 ), true );
}

function neas_render_lecture_list( $lectures, $group_by_month ) {
	if ( empty( $lectures ) ) {
		return '<p class="neas-list-empty">A programação de palestras será publicada em breve. Acompanhe também pelo nosso Instagram.</p>';
	}
	$html          = '';
	$current_month = '';
	$list_open     = false;
	foreach ( $lectures as $lecture ) {
		$month_key = substr( $lecture['date'], 0, 7 );
		if ( $group_by_month && $month_key !== $current_month ) {
			if ( $list_open ) {
				$html .= '</ul>';
			}
			$html         .= '<h3 class="neas-list-month">' . esc_html( ucfirst( $lecture['month'] ) . ' de ' . substr( $lecture['date'], 0, 4 ) ) . '</h3><ul class="neas-list">';
			$current_month = $month_key;
			$list_open     = true;
		} elseif ( ! $list_open ) {
			$html     .= '<ul class="neas-list">';
			$list_open = true;
		}
		$html .= '<li class="neas-list-item">';
		$html .= '<div class="neas-list-date"><span class="neas-list-day">' . esc_html( $lecture['day'] ) . '</span><span class="neas-list-weekday">' . esc_html( $lecture['weekday_short'] . ' · ' . $lecture['time'] ) . '</span></div>';
		$html .= '<div class="neas-list-topic">' . esc_html( $lecture['topic'] ) . '</div>';
		$html .= '<div class="neas-list-speaker">' . esc_html( $lecture['speaker'] ) . '</div>';
		$html .= '</li>';
	}
	if ( $list_open ) {
		$html .= '</ul>';
	}
	return '<div class="neas-lectures">' . $html . '</div>';
}

function neas_render_past_lectures( $attributes ) {
	$count    = isset( $attributes['count'] ) ? (int) $attributes['count'] : 24;
	$lectures = neas_get_recorded_lectures( $count );
	if ( empty( $lectures ) ) {
		return '<p class="neas-list-empty">As gravações das palestras aparecerão aqui em breve. Enquanto isso, assista no <a href="https://www.instagram.com/neautadesouza/reels/">nosso Instagram</a>.</p>';
	}
	$html = '';
	foreach ( $lectures as $lecture ) {
		$embed_url = neas_instagram_embed_url( $lecture['recording_url'] );
		if ( ! $embed_url ) {
			continue;
		}
		$date  = (int) $lecture['day'] . ' de ' . $lecture['month'] . ' de ' . substr( $lecture['date'], 0, 4 );
		$html .= '<article class="neas-recording">';
		$html .= '<div class="neas-recording-video"><iframe src="' . esc_url( $embed_url ) . '" title="' . esc_attr( 'Gravação: ' . $lecture['topic'] ) . '" loading="lazy" allowfullscreen scrolling="no"></iframe></div>';
		$html .= '<p class="neas-recording-date">' . esc_html( $date ) . '</p>';
		$html .= '<h3 class="neas-recording-topic">' . esc_html( $lecture['topic'] ) . '</h3>';
		if ( $lecture['speaker'] ) {
			$html .= '<p class="neas-recording-speaker">com ' . esc_html( $lecture['speaker'] ) . '</p>';
		}
		$html .= '</article>';
	}
	return '<div class="neas-recordings">' . $html . '</div>';
}

function neas_render_pix( $attributes ) {
	$key     = ! empty( $attributes['key'] ) ? $attributes['key'] : NEAS_PIX_KEY;
	$display = ! empty( $attributes['display'] ) ? $attributes['display'] : NEAS_PIX_KEY_DISPLAY;
	$qr      = ! empty( $attributes['qr'] ) ? $attributes['qr'] : NEAS_SITE_URL . 'assets/pix-qr.png';
	ob_start();
	?>
	<div class="neas-pix">
		<img class="neas-pix-qr" src="<?php echo esc_url( $qr ); ?>" alt="QR Code Pix para doação ao NEAS" width="160" height="160">
		<div class="neas-pix-details">
			<span class="neas-pix-label">Chave Pix (CNPJ)</span>
			<span class="neas-pix-key"><?php echo esc_html( $display ); ?></span>
			<button type="button" class="neas-pix-button" data-key="<?php echo esc_attr( $key ); ?>">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="11" height="11" rx="2"></rect><path d="M5 15V5a1 1 0 0 1 1-1h9"></path></svg>
				<span class="neas-pix-text">Copiar chave Pix</span>
			</button>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

function neas_render_map( $attributes ) {
	$address = ! empty( $attributes['address'] ) ? $attributes['address'] : NEAS_ADDRESS;
	$src     = 'https://maps.google.com/maps?q=' . rawurlencode( $address ) . '&z=16&output=embed';
	return '<div class="neas-map"><iframe title="' . esc_attr( 'Mapa: ' . $address ) . '" src="' . esc_url( $src ) . '" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>';
}
