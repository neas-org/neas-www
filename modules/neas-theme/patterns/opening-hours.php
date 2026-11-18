<?php
/**
 * Title: Quando vir ao NEAS (horários)
 * Slug: neas/opening-hours
 * Categories: neas
 * Description: Quatro cartões com os principais horários da casa.
 *
 * @package NEAS
 */
$neas_cards = array(
	array( 'lecture', 'Palestras públicas', 'Ter, Qui e Sáb às 20h<br>Domingo às 16h30' ),
	array( 'heart', 'Atendimento fraterno e passe', 'Ter, Qui e Sáb das 19h às 20h<br>Domingo das 15h30 às 16h30' ),
	array( 'people', 'Evangelização infantojuvenil', 'Domingos – Mocidade às 16h<br>Infância às 16h30' ),
	array( 'book', 'Biblioteca e livraria', 'Abertas nos horários das palestras, com bazar e secretaria' ),
);
?>
<!-- wp:group {"align":"full","className":"neas-section","layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull neas-section"><!-- wp:heading -->
<h2 class="wp-block-heading">Quando vir ao NEAS</h2>
<!-- /wp:heading -->

<!-- wp:group {"className":"neas-cards","layout":{"type":"flex","flexWrap":"wrap","verticalAlignment":"stretch"}} -->
<div class="wp-block-group neas-cards"><?php foreach ( $neas_cards as $neas_card ) : ?><!-- wp:group {"className":"neas-card neas-ic-<?php echo esc_attr( $neas_card[0] ); ?>","layout":{"type":"default"}} -->
<div class="wp-block-group neas-card neas-ic-<?php echo esc_attr( $neas_card[0] ); ?>"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php echo esc_html( $neas_card[1] ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php echo wp_kses( $neas_card[2], array( 'br' => array() ) ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --><?php endforeach; ?></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
