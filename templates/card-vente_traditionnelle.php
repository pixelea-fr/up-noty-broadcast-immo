<?php

if ( ! isset( $post_id ) ) {
    return;
}

$post_id = (int) $post_id;

$prix = get_post_meta( $post_id, 'up_prix', true );
if ( $prix === '' ) {
    $prix = get_post_meta( $post_id, '_noty_prix', true );
}
$surface = get_post_meta( $post_id, 'up_surface_habitable', true );
if ( $surface === '' ) {
    $surface = get_post_meta( $post_id, '_noty_surface_habitable', true );
}
if ( $surface === '' ) {
    $surface = get_post_meta( $post_id, 'up_surface', true );
}
if ( $surface === '' ) {
    $surface = get_post_meta( $post_id, '_noty_surface', true );
}
$pieces = get_post_meta( $post_id, 'up_nb_pieces', true );
if ( $pieces === '' ) {
    $pieces = get_post_meta( $post_id, '_noty_nb_pieces', true );
}

$villes = wp_get_post_terms( $post_id, 'noty_ville' );
$ville_name = ( ! is_wp_error( $villes ) && ! empty( $villes ) ) ? $villes[0]->name : '';

$natures = wp_get_post_terms( $post_id, 'noty_nature' );
$nature_name = ( ! is_wp_error( $natures ) && ! empty( $natures ) ) ? $natures[0]->name : '';

?>
<div class="up-immo-card up-immo-card--vente-traditionnelle">
    <?php if ( has_post_thumbnail( $post_id ) ) : ?>
        <div class="up-immo-card__thumb">
            <?php echo get_the_post_thumbnail( $post_id, 'medium', array( 'class' => 'up-immo-card__image' ) ); ?>
            <?php if ( $nature_name !== '' ) : ?>
                <span class="up-immo-card__badge">
                    <?php echo esc_html( $nature_name ); ?>
                </span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <h3 class="up-immo-card__title">
        <a class="up-immo-card__title-link" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a>
    </h3>

    <?php if ( $ville_name !== '' ) : ?>
        <p class="up-immo-card__city">
            <span class="dashicons dashicons-location"></span>
            <?php echo esc_html( $ville_name ); ?>
        </p>
    <?php endif; ?>

    <div class="up-immo-card__footer">
        <span class="up-immo-card__price">
            <?php echo $prix !== '' ? esc_html( number_format( (float) $prix, 0, ',', ' ' ) . ' €' ) : 'Prix sur demande'; ?>
        </span>
        <span class="up-immo-card__meta">
            <?php
            $parts = array();
            if ( $surface !== '' ) {
                $parts[] = $surface . ' m²';
            }
            if ( $pieces !== '' ) {
                $parts[] = $pieces . ' p.';
            }
            echo esc_html( implode( ' | ', $parts ) );
            ?>
        </span>
    </div>
</div>
