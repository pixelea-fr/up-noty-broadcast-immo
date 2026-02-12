<?php

if ( ! isset( $post_id ) ) {
    return;
}

$post_id = (int) $post_id;
$post = get_post( $post_id );
if ( ! $post ) {
    return;
}

setup_postdata( $post );

$photo_ids = get_post_meta( $post_id, 'up_photo_ids', true );
if ( ! is_array( $photo_ids ) || empty( $photo_ids ) ) {
    $photo_ids = get_post_meta( $post_id, '_noty_photo_ids', true );
}
$photo_ids = is_array( $photo_ids ) ? array_values( array_filter( array_map( 'intval', $photo_ids ) ) ) : array();

$prix = get_post_meta( $post_id, 'up_prix', true );
if ( $prix === '' ) {
    $prix = get_post_meta( $post_id, '_noty_prix', true );
}
$loyer = get_post_meta( $post_id, 'up_loyer', true );
if ( $loyer === '' ) {
    $loyer = get_post_meta( $post_id, '_noty_loyer', true );
}
$transaction_type = get_post_meta( $post_id, 'up_transaction_type', true );
if ( $transaction_type === '' ) {
    $transaction_type = get_post_meta( $post_id, '_noty_transaction_type', true );
}

$villes = wp_get_post_terms( $post_id, 'noty_ville' );
$ville_name = ( ! is_wp_error( $villes ) && ! empty( $villes ) ) ? $villes[0]->name : '';

$natures = wp_get_post_terms( $post_id, 'noty_nature' );
$nature_name = ( ! is_wp_error( $natures ) && ! empty( $natures ) ) ? $natures[0]->name : '';

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
$chambres = get_post_meta( $post_id, 'up_nb_chambres', true );
if ( $chambres === '' ) {
    $chambres = get_post_meta( $post_id, '_noty_nb_chambres', true );
}

?>
<article class="up-immo-single">
    <header class="up-immo-single__header">
        <h1 class="up-immo-single__title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h1>
        <p class="up-immo-single__subtitle">
            <?php
            $meta_parts = array();
            if ( $nature_name !== '' ) {
                $meta_parts[] = $nature_name;
            }
            if ( $ville_name !== '' ) {
                $meta_parts[] = $ville_name;
            }
            if ( $transaction_type !== '' ) {
                $meta_parts[] = $transaction_type;
            }
            echo esc_html( implode( ' · ', $meta_parts ) );
            ?>
        </p>
    </header>

    <?php if ( has_post_thumbnail( $post_id ) ) : ?>
        <div class="up-immo-single__hero">
            <?php echo get_the_post_thumbnail( $post_id, 'large', array( 'class' => 'up-immo-single__hero-image' ) ); ?>
        </div>
    <?php endif; ?>

    <?php if ( ! empty( $photo_ids ) ) : ?>
        <div class="up-immo-single__gallery">
            <?php foreach ( $photo_ids as $attachment_id ) : ?>
                <?php echo wp_get_attachment_image( $attachment_id, 'medium', true, array( 'class' => 'up-immo-single__gallery-image' ) ); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <section class="up-immo-single__highlights">
        <div class="up-immo-single__card">
            <strong>Prix / Loyer</strong><br>
            <?php
            if ( $loyer !== '' ) {
                echo esc_html( number_format( (float) $loyer, 0, ',', ' ' ) . ' €' );
            } elseif ( $prix !== '' ) {
                echo esc_html( number_format( (float) $prix, 0, ',', ' ' ) . ' €' );
            } else {
                echo 'Sur demande';
            }
            ?>
        </div>

        <div class="up-immo-single__card">
            <strong>Caractéristiques</strong><br>
            <?php
            $c = array();
            if ( $surface !== '' ) {
                $c[] = $surface . ' m²';
            }
            if ( $pieces !== '' ) {
                $c[] = $pieces . ' pièces';
            }
            if ( $chambres !== '' ) {
                $c[] = $chambres . ' chambres';
            }
            echo esc_html( $c ? implode( ' · ', $c ) : '—' );
            ?>
        </div>
    </section>

    <section class="up-immo-single__content">
        <?php echo apply_filters( 'the_content', $post->post_content ); ?>
    </section>
</article>
<?php
wp_reset_postdata();
