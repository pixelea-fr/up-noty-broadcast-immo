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

$annonce = ( isset( $annonce ) && is_object( $annonce ) ) ? $annonce : null;
if ( ! $annonce || ! isset( $annonce->bien ) || ! is_object( $annonce->bien ) ) {
    wp_reset_postdata();
    return;
}

$bien = $annonce->bien;
$photo_ids = ( isset( $annonce->photos ) && is_array( $annonce->photos ) ) ? $annonce->photos : array();

?>

<article class="up-immo-single">
    <header class="up-immo-single__header">
        <h1 class="up-immo-single__title"><?php echo esc_html( $annonce->titre ); ?></h1>
        <!-- <p class="up-immo-single__subtitle">
            <?php echo esc_html( $bien->subtitle ); ?>
        </p> -->

        <?php /* if ( $annonce->ids !== '' ) : ?>
            <p class="up-immo-single__subtitle">
                <?php echo esc_html( $annonce->ids ); ?>
            </p>
        <?php endif; */ ?>
    </header>
<div class="up-immo-single__body">    
    <?php /* if ( has_post_thumbnail( $post_id ) ) : ?>
        <section class="up-immo-single__hero">
            <?php echo get_the_post_thumbnail( $post_id, 'large', array( 'class' => 'up-immo-single__hero-image' ) ); ?>
        </section>
    <?php endif; */?>

    <?php if ( ! empty( $photo_ids ) ) : ?>
        <section class="up-immo-single__gallery">
            <!-- Swiper -->
            <div style="--swiper-navigation-color: #fff; --swiper-pagination-color: #fff" class="swiper swiper-main">
                <div class="swiper-wrapper">
                    <?php foreach ( $photo_ids as $attachment_id ) : ?>
                        <div class="swiper-slide">
                            <a href="<?php echo esc_url( wp_get_attachment_image_url( $attachment_id, 'full' ) ); ?>" data-fancybox="gallery">
                                <?php echo wp_get_attachment_image( $attachment_id, 'full', false, array( 'class' => 'up-immo-single__gallery-image' ) ); ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>

            <div thumbsSlider="" class="swiper swiper-thumbs">
                <div class="swiper-wrapper">
                    <?php foreach ( $photo_ids as $attachment_id ) : ?>
                        <div class="swiper-slide">
                            <?php echo wp_get_attachment_image( $attachment_id, 'medium', false, array( 'class' => 'up-immo-single__gallery-thumb' ) ); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
   

    <?php if ( ! empty( $bien->details ) ) : ?>
        <section class="up-immo-single__details">
 
            <div class="up-immo-single__table">
                <?php foreach ( $bien->details as $row ) : ?>
                    <?php
                    $meta = '';
                    $label = '';
                    $value = '';

                    if ( is_array( $row ) ) {
                        $meta = isset( $row['meta'] ) ? (string) $row['meta'] : '';
                        $label = isset( $row['label'] ) ? (string) $row['label'] : ( isset( $row[0] ) ? (string) $row[0] : '' );
                        $value = isset( $row['value'] ) ? $row['value'] : ( isset( $row[1] ) ? $row[1] : '' );
                    }

                    $value = apply_filters( 'noty_immo_meta_value', $value, $meta, $row, $bien, $annonce, $post_id );
                    if ( $meta !== '' ) {
                        $value = apply_filters( 'noty_immo_meta_value_' . $meta, $value, $row, $bien, $annonce, $post_id );
                    }
                    ?>
                    <div class="up-immo-single__table-row">
                        <div class="up-immo-single__table-cell up-immo-single__table-cell--l">
                            <span class="up-immo-single__table__label"><?php echo esc_html( $label ); ?></span>
                        </div>
                        <div class="up-immo-single__table-cell up-immo-single__table-cell--r">
                             <span class="up-immo-single__table__value" data-meta="<?php echo esc_attr( $meta ); ?>"><?php echo esc_html( (string) $value ); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
                </tbody>
            </div>
        </section>
    <?php endif; ?>

    <?php if ( $post->post_content !== '' ) : ?>
        <section class="up-immo-single__content">
            <h2>Description</h2>
            <?php echo wpautop( wp_kses_post( $post->post_content ) ); ?>
        </section>
    <?php endif; ?>

    <?php /* if ( $annonce->office->raison_sociale !== '' || $annonce->contact->nom !== '' || $annonce->contact->telephone !== '' || $annonce->contact->email !== '' ) : ?>
        <section class="up-immo-single__contact">
            <h2>Contact</h2>
            <?php if ( $annonce->office->raison_sociale !== '' ) : ?>
                <p><strong><?php echo esc_html( $annonce->office->raison_sociale ); ?></strong><?php echo $annonce->office->crpcen !== '' ? esc_html( ' (' . $annonce->office->crpcen . ')' ) : ''; ?></p>
            <?php endif; ?>
            <?php if ( $annonce->contact->nom !== '' ) : ?>
                <p><?php echo esc_html( $annonce->contact->nom ); ?></p>
            <?php endif; ?>
            <?php if ( $annonce->contact->telephone !== '' ) : ?>
                <p><?php echo esc_html( $annonce->contact->telephone ); ?></p>
            <?php endif; ?>
            <?php if ( $annonce->contact->email !== '' ) : ?>
                <p><a href="mailto:<?php echo esc_attr( $annonce->contact->email ); ?>"><?php echo esc_html( $annonce->contact->email ); ?></a></p>
            <?php endif; ?>
        </section>
    <?php endif; */?>

     <?php if ( ( isset( $bien->dpe ) && is_object( $bien->dpe ) && isset( $bien->dpe->image_url ) && $bien->dpe->image_url !== '' ) || ( isset( $bien->gse ) && is_object( $bien->gse ) && isset( $bien->gse->image_url ) && $bien->gse->image_url !== '' ) ) : ?>
        <section class="up-immo-single__dpe-gse">
            <h2 class="up-immo-single__dpe-gse__title">Diagnostiques énergétiques</h2>
            <div class="up-immo-single__dpe-gse__items">
            <?php if ( isset( $bien->dpe ) && is_object( $bien->dpe ) && isset( $bien->dpe->image_url ) && $bien->dpe->image_url !== '' ) : ?>
             <div class="up-immo-single__dpe-gse__item up-immo-single__dpe-gse__item--dpe">
                    <div class="up-immo-single__dpe-gse__title up-immo-single__dpe-gse__title--dpe">DPE</div>
                    <img class="up-immo-single__dpe-gse__img up-immo-single__dpe-gse__img--dpe" src="<?php echo esc_url( $bien->dpe->image_url ); ?>" alt="<?php echo esc_attr( 'DPE ' . strtoupper( (string) $bien->dpe->lettre ) ); ?>" loading="lazy">
                </div>
            <?php endif; ?>

            <?php if ( isset( $bien->gse ) && is_object( $bien->gse ) && isset( $bien->gse->image_url ) && $bien->gse->image_url !== '' ) : ?>
                <div class="up-immo-single__dpe-gse__item up-immo-single__dpe-gse__item--gse">
                      <div class="up-immo-single__dpe-gse__title up-immo-single__dpe-gse__title--gse">GES</div>
                    <img class="up-immo-single__dpe-gse__img up-immo-single__dpe-gse__img--gse" src="<?php echo esc_url( $bien->gse->image_url ); ?>" alt="<?php echo esc_attr( 'GES ' . strtoupper( (string) $bien->gse->lettre ) ); ?>" loading="lazy">
                </div>
            <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>
    </div>
</article>
<?php
wp_reset_postdata();
