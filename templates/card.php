<?php

if ( ! isset( $post_id ) ) {
    return;
}

$post_id = (int) $post_id;

$annonce = ( isset( $annonce ) && is_object( $annonce ) ) ? $annonce : null;
if ( ! $annonce || ! isset( $annonce->bien ) || ! is_object( $annonce->bien ) ) {
    return;
}

$bien = $annonce->bien;

?>
<div class="up-immo-card" data-postid="<?php echo $post_id; ?>" data-templates="card">

    <?php if ( has_post_thumbnail( $post_id ) ) : ?>
        
        <div class="up-immo-card__header">
            <a class="up-immo-card__header-link" href="<?php echo esc_url( $annonce->lien ); ?>"></a>
            <?php echo get_the_post_thumbnail( $post_id, 'medium', array( 'class' => 'up-immo-card__image' ) ); ?>
            <?php if ( $bien->nature !== '' ) : ?>
                <span class="up-immo-card__badge">
                    <?php echo esc_html( $bien->nature ); ?>
                </span>
                
            <?php endif; ?>
          
        </div>
 <?php else: ?>
        <div class="up-immo-card__header">
            <a class="up-immo-card__header-link" href="<?php echo esc_url( $annonce->lien ); ?>"></a>
            <img src="<?php echo esc_url( plugins_url( 'assets/images/no-image.png',"up-noty-broadcast-immo/up-noty-broadcast-immo.php" ) ); ?>" alt="<?php echo esc_attr( get_the_title( $post_id ) ); ?>" class="up-immo-card__image">
            <?php if ( $bien->nature !== '' ) : ?>
                <span class="up-immo-card__badge">
                    <?php echo esc_html( $bien->nature ); ?>
                </span>  
            <?php endif; ?>
            
        </div>

    <?php endif; ?>
    <div class="up-immo-card__content">
                <div class="up-immo-card__price-container"><span class="up-immo-card__price">
            <?php echo $bien->prix_ou_loyer !== '' ? esc_html( $bien->prix_ou_loyer ) : 'Prix sur demande'; ?>
        </span>
                <?php if ( $bien->loyer !== '' && $bien->loyer_periodicite !== '' ) : ?>
    
                <span><?php echo esc_html( ' (' . $bien->loyer_periodicite . ')' ); ?></span>
 
        <?php endif; ?>
    </div> 


        <h3 class="up-immo-card__title">
            <a class="up-immo-card__title-link" href="<?php echo esc_url( $annonce->lien ); ?>"><?php echo esc_html( $annonce->titre ); ?></a>
        </h3>

        <?php /*if ( $bien->ville !== '' ) : ?>
            <p class="up-immo-card__city">
                <span class="dashicons dashicons-location"></span>
                <?php echo esc_html( $bien->ville ); ?>
            </p>
        <?php endif; */ ?>

        <?php /* if ( $bien->transaction_string !== '' || $bien->transaction !== '' ) : ?>
            <p class="up-immo-card__transaction">
                <?php echo esc_html( $bien->transaction_string !== '' ? $bien->transaction_string : $bien->transaction ); ?>
            </p>
        <?php endif; */ ?>

      

            <span class="up-immo-card__meta">
                <?php echo esc_html( $bien->caracteristiques ); ?>
            </span>
            <a class="btn-with-arrow" href="<?php echo esc_url( $annonce->lien ); ?>" class="up-immo-card__btn">
           En savoir plus 
        </a>
        </div>

        <?php if ( $bien->charges_resume !== '' ) : ?>
            <p class="up-immo-card__charges">
                <?php echo esc_html( $bien->charges_resume ); ?>
            </p>
        <?php endif; ?>

</div>
