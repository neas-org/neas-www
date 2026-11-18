<?php
/**
 * Title: Página de palestras anteriores (gravações)
 * Slug: neas/past-lectures-page
 * Categories: neas
 * Block Types: core/post-content
 * Post Types: page
 * Description: Gravações das palestras que já aconteceram, com o vídeo do Instagram.
 *
 * @package NEAS
 */
?>
<!-- wp:paragraph {"className":"neas-text-large"} -->
<p class="neas-text-large">Perdeu alguma palestra ou quer assistir de novo? Aqui estão as gravações das palestras públicas do NEAS.</p>
<!-- /wp:paragraph -->

<!-- wp:neas/past-lectures /-->

<!-- wp:paragraph {"className":"neas-arrow-link"} -->
<p class="neas-arrow-link"><a href="https://www.instagram.com/neautadesouza/reels/">Ver todos os vídeos no Instagram →</a>   <a href="<?php echo esc_url( home_url( '/palestras/' ) ); ?>">Próximas palestras →</a></p>
<!-- /wp:paragraph -->
