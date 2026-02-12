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

$loyer = get_post_meta( $post_id, 'up_loyer', true );
if ( $loyer === '' ) {
    $loyer = get_post_meta( $post_id, '_noty_loyer', true );
}
$periodicite = get_post_meta( $post_id, 'up_loyer_periodicite', true );
if ( $periodicite === '' ) {
    $periodicite = get_post_meta( $post_id, '_noty_loyer_periodicite', true );
}
$charges_incluses = get_post_meta( $post_id, 'up_charges_incluses', true );
if ( $charges_incluses === '' ) {
    $charges_incluses = get_post_meta( $post_id, '_noty_charges_incluses', true );
}
$montant_charges = get_post_meta( $post_id, 'up_montant_charges', true );
if ( $montant_charges === '' ) {
    $montant_charges = get_post_meta( $post_id, '_noty_montant_charges', true );
}
$depot = get_post_meta( $post_id, 'up_montant_depot_garantie', true );
if ( $depot === '' ) {
    $depot = get_post_meta( $post_id, '_noty_montant_depot_garantie', true );
}
$etat_lieux = get_post_meta( $post_id, 'up_montant_etat_lieux', true );
if ( $etat_lieux === '' ) {
    $etat_lieux = get_post_meta( $post_id, '_noty_montant_etat_lieux', true );
}
$meuble = get_post_meta( $post_id, 'up_meuble', true );
if ( $meuble === '' ) {
    $meuble = get_post_meta( $post_id, '_noty_meuble', true );
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

?>
<article class="up-immo-single up-immo-single--location">
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
            $meta_parts[] = 'Location';
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
            <strong>Loyer</strong><br>
            <?php
            if ( $loyer !== '' ) {
                $suffix = $periodicite !== '' ? ' / ' . $periodicite : '';
                echo esc_html( number_format( (float) $loyer, 0, ',', ' ' ) . ' €' . $suffix );
            } else {
                echo 'Sur demande';
            }
            ?>
        </div>

        <div class="up-immo-single__card">
            <strong>Charges</strong><br>
            <?php
            $txt = array();
            if ( $charges_incluses === '1' ) {
                $txt[] = 'Incluses';
            } elseif ( $charges_incluses === '0' ) {
                $txt[] = 'Non incluses';
            }
            if ( $montant_charges !== '' ) {
                $txt[] = number_format( (float) $montant_charges, 0, ',', ' ' ) . ' €';
            }
            echo esc_html( $txt ? implode( ' · ', $txt ) : '—' );
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
            echo esc_html( $c ? implode( ' · ', $c ) : '—' );
            ?>
        </div>

        <div class="up-immo-single__card">
            <strong>Conditions</strong><br>
            <?php
            $c = array();
            if ( $depot !== '' ) {
                $c[] = 'Dépôt: ' . number_format( (float) $depot, 0, ',', ' ' ) . ' €';
            }
            if ( $etat_lieux !== '' ) {
                $c[] = 'État lieux: ' . number_format( (float) $etat_lieux, 0, ',', ' ' ) . ' €';
            }
            if ( $meuble === '1' ) {
                $c[] = 'Meublé';
            } elseif ( $meuble === '0' ) {
                $c[] = 'Non meublé';
            }
            echo esc_html( $c ? implode( ' · ', $c ) : '—' );
            ?>
        </div>
    </section>
</article>
<?php
wp_reset_postdata();
