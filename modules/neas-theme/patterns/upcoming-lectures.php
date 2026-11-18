<?php
/**
 * Title: Próximas palestras
 * Slug: neas/upcoming-lectures
 * Categories: neas
 * Description: Lista automática das próximas palestras cadastradas.
 *
 * @package NEAS
 */
?>
<!-- wp:group {"align":"full","anchor":"palestras","className":"neas-section neas-section-lectures","layout":{"type":"constrained","contentSize":"1200px"}} -->
<div id="palestras" class="wp-block-group alignfull neas-section neas-section-lectures"><!-- wp:group {"className":"neas-section-header","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"bottom"}} -->
<div class="wp-block-group neas-section-header"><!-- wp:group {"layout":{"type":"default"}} -->
<div class="wp-block-group"><!-- wp:heading -->
<h2 class="wp-block-heading">Próximas palestras</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"neas-subtitle"} -->
<p class="neas-subtitle">Palestras públicas e gratuitas, abertas a todos.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:paragraph {"className":"neas-arrow-link"} -->
<p class="neas-arrow-link"><a href="<?php echo esc_url( home_url( '/palestras/' ) ); ?>">Ver programação completa →</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:neas/upcoming-lectures {"count":6} /--></div>
<!-- /wp:group -->
