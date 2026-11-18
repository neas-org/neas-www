<?php
/**
 * Title: Cabeçalho NEAS
 * Slug: neas/header
 * Categories: header
 * Block Types: core/template-part/header
 * Inserter: no
 *
 * @package NEAS
 */
$neas_home_url = home_url( '/' );
?>
<!-- wp:group {"align":"full","className":"neas-topbar","layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull neas-topbar"><!-- wp:group {"className":"neas-topbar-row","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group neas-topbar-row"><!-- wp:paragraph {"className":"neas-topbar-text"} -->
<p class="neas-topbar-text">Rua Itaimbé, 575 – Ipsep, Recife/PE · <a href="tel:+558133394917">(81) 3339-4917</a></p>
<!-- /wp:paragraph -->

<!-- wp:social-links {"iconColor":"branco","iconColorValue":"#FFFFFF","size":"has-small-icon-size","className":"is-style-logos-only neas-social","layout":{"type":"flex","flexWrap":"nowrap"}} -->
<ul class="wp-block-social-links has-small-icon-size has-icon-color is-style-logos-only neas-social"><!-- wp:social-link {"url":"https://www.instagram.com/neautadesouza/","service":"instagram","label":"Instagram do NEAS"} /-->

<!-- wp:social-link {"url":"https://www.facebook.com/neautadesouza","service":"facebook","label":"Facebook do NEAS"} /-->

<!-- wp:social-link {"url":"https://www.youtube.com/channel/UC7bkqOgG7HSpB35_ur6pUww","service":"youtube","label":"YouTube do NEAS"} /--></ul>
<!-- /wp:social-links --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"neas-header","layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull neas-header"><!-- wp:group {"className":"neas-header-row","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group neas-header-row"><!-- wp:image {"sizeSlug":"full","linkDestination":"custom","className":"neas-logo"} -->
<figure class="wp-block-image size-full neas-logo"><a href="<?php echo esc_url( $neas_home_url ); ?>"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/logo-neas.png' ) ); ?>" alt="Núcleo Espírita Auta de Souza – página inicial"/></a></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"neas-menu-area","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"right"}} -->
<div class="wp-block-group neas-menu-area"><!-- wp:navigation {"overlayMenu":"mobile","className":"neas-menu","layout":{"type":"flex","justifyContent":"right","flexWrap":"wrap"}} -->
<!-- wp:navigation-link {"label":"A Casa","url":"<?php echo esc_url( home_url( '/neas/' ) ); ?>","kind":"custom"} /-->
<!-- wp:navigation-link {"label":"Atividades","url":"<?php echo esc_url( home_url( '/atividades-do-neas/' ) ); ?>","kind":"custom"} /-->
<!-- wp:navigation-link {"label":"Palestras","url":"<?php echo esc_url( home_url( '/palestras/' ) ); ?>","kind":"custom"} /-->
<!-- wp:navigation-link {"label":"Evangelização","url":"<?php echo esc_url( home_url( '/#evangelizacao' ) ); ?>","kind":"custom"} /-->
<!-- wp:navigation-link {"label":"Contato","url":"<?php echo esc_url( home_url( '/#visite' ) ); ?>","kind":"custom"} /-->
<!-- /wp:navigation -->

<!-- wp:buttons {"className":"neas-button-donate"} -->
<div class="wp-block-buttons neas-button-donate"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/#doe' ) ); ?>">Doe com Pix</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
