<?php

if (! isset($post_id)) {
    return;
}

$post_id = (int) $post_id;

$annonce = (isset($annonce) && is_object($annonce)) ? $annonce : null;
if (! $annonce || ! isset($annonce->bien) || ! is_object($annonce->bien)) {
    return;
}

$bien = $annonce->bien;

?>
<div class="up-immo-card" data-postid="<?php echo $post_id; ?>" data-templates="card">
    <?php if (has_post_thumbnail($post_id)) : ?>
        <div class="up-immo-card__header">
            <a class="up-immo-card__header-link" href="<?php echo esc_url($annonce->lien); ?>"></a>
            <?php echo get_the_post_thumbnail($post_id, 'large', array('class' => 'up-immo-card__image')); ?>
            <?php if ($bien->nature !== '') : ?>
                <span class="up-immo-card__badge">
                    <?php echo esc_html($bien->nature); ?>
                </span>

            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="up-immo-card__header">
            <a class="up-immo-card__header-link" href="<?php echo esc_url($annonce->lien); ?>"></a>
            <img src="<?php echo esc_url(plugins_url('assets/images/no-image.png', "up-noty-broadcast-immo/up-noty-broadcast-immo.php")); ?>" alt="<?php echo esc_attr(get_the_title($post_id)); ?>" class="up-immo-card__image">
            <?php if ($bien->nature !== '') : ?>
                <span class="up-immo-card__badge">
                    <?php echo esc_html($bien->nature); ?>
                </span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <div class="up-immo-card__content">
        <div class="up-immo-card__price-container"><span class="up-immo-card__price">
                <?php echo $bien->prix_ou_loyer !== '' ? esc_html($bien->prix_ou_loyer) : 'Prix sur demande'; ?>
            </span>
            <?php if ($bien->loyer !== '' && $bien->loyer_periodicite !== '') : ?>
                <span><?php echo esc_html(' (' . $bien->loyer_periodicite . ')'); ?></span>
            <?php endif; ?>
        </div>
        <h3 class="up-immo-card__title">
            <a class="up-immo-card__title-link" href="<?php echo esc_url($annonce->lien); ?>"><?php echo esc_html($annonce->titre); ?></a>
        </h3>
        <div class="p-immo-card__details">
        <?php
       foreach ( $annonce->bien->resume_details as $detail ) {
    echo '<div class="p-immo-card__detail">';
    echo '<span class="label">' . esc_html( $detail['label'] ) . ' :</span> ';
    echo '<span class="value">' . esc_html( $detail['value'] ) . '</span>';
    echo '</div>';
} ?>
</div>
        <a class="btn-with-arrow" href="<?php echo esc_url($annonce->lien); ?>" class="up-immo-card__btn">
            En savoir plus
        </a>
    </div>
</div>