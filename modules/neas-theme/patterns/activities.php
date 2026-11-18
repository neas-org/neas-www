<?php
/**
 * Title: Atividades da casa
 * Slug: neas/activities
 * Categories: neas
 * Description: Cartões com estudos, atendimento, obras assistenciais e cursos.
 *
 * @package NEAS
 */
$neas_groups = array(
	'Estudos da Doutrina'     => array(
		array( 'Introdução ao Estudo (IEDE)', 'Sáb · 16h' ),
		array( 'Estudo Sistematizado (ESDE)', 'Sex · 19h30' ),
		array( 'Estudo Aprofundado (EADE)', '1º e 3º Sáb · 14h' ),
		array( 'Estudo do Evangelho', 'Qua · 19h30' ),
	),
	'Atendimento espiritual'  => array(
		array( 'Atendimento fraterno', 'Ter, Qui, Sáb · 19h' ),
		array( 'Passe individual', 'Ter, Qui, Sáb · 19h' ),
		array( 'Passe de coluna', 'Ter · 19h' ),
		array( 'Evangelho no Lar (Grupo Emmanuel)', 'Sex · 19h30' ),
	),
	'Obras assistenciais'     => array(
		array( 'Campanha da Fraternidade Auta de Souza', 'Dom · 8h30' ),
		array( 'Atendimento às famílias', 'Dom · 8h' ),
		array( 'Enxoval para gestantes', '2ª Sex · 14h30' ),
		array( 'Samaritanas', 'Sex · 14h30' ),
	),
	'Cursos e arte'           => array(
		array( 'Escolinha de Arte', 'Ter e Qua · 14h' ),
		array( 'Corte e Costura', 'Qui · 14h' ),
		array( 'Esperanto', 'Sáb · 9h' ),
		array( 'Coral Canto Livre', 'Qui · 17h30' ),
	),
);
?>
<!-- wp:group {"align":"full","anchor":"atividades","className":"neas-section neas-bg-light","layout":{"type":"constrained","contentSize":"1200px"}} -->
<div id="atividades" class="wp-block-group alignfull neas-section neas-bg-light"><!-- wp:heading -->
<h2 class="wp-block-heading">Atividades da casa</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"neas-subtitle"} -->
<p class="neas-subtitle">Estudos, assistência e cursos durante toda a semana. Todas as atividades são gratuitas.</p>
<!-- /wp:paragraph -->

<!-- wp:group {"className":"neas-cards neas-cards-wide","layout":{"type":"flex","flexWrap":"wrap","verticalAlignment":"stretch"}} -->
<div class="wp-block-group neas-cards neas-cards-wide"><?php foreach ( $neas_groups as $neas_title => $neas_items ) : ?><!-- wp:group {"className":"neas-card neas-card-white","layout":{"type":"default"}} -->
<div class="wp-block-group neas-card neas-card-white"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php echo esc_html( $neas_title ); ?></h3>
<!-- /wp:heading -->

<!-- wp:table {"hasFixedLayout":false,"className":"neas-table"} -->
<figure class="wp-block-table neas-table"><table><tbody><?php foreach ( $neas_items as $neas_item ) : ?><tr><td><?php echo esc_html( $neas_item[0] ); ?></td><td><?php echo esc_html( $neas_item[1] ); ?></td></tr><?php endforeach; ?></tbody></table></figure>
<!-- /wp:table --></div>
<!-- /wp:group --><?php endforeach; ?></div>
<!-- /wp:group -->

<!-- wp:paragraph {"className":"neas-arrow-link"} -->
<p class="neas-arrow-link"><a href="<?php echo esc_url( home_url( '/atividades-do-neas/' ) ); ?>">Ver todas as atividades →</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
