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

$get_meta_first = function( $keys, $default = '' ) use ( $post_id ) {
    $keys = is_array( $keys ) ? $keys : array( $keys );
    foreach ( $keys as $key ) {
        $val = get_post_meta( $post_id, $key, true );
        if ( $val !== '' && $val !== null ) {
            return $val;
        }
    }
    return $default;
};

$json_decode_if_needed = function( $value ) {
    if ( is_array( $value ) ) {
        return $value;
    }
    if ( ! is_string( $value ) || $value === '' ) {
        return null;
    }
    $decoded = json_decode( $value, true );
    return is_array( $decoded ) ? $decoded : null;
};

$raw = $get_meta_first( array( 'up_raw', '_noty_raw' ), '' );
$raw_data = $json_decode_if_needed( $raw );

$photo_ids = get_post_meta( $post_id, 'up_photo_ids', true );
if ( ! is_array( $photo_ids ) || empty( $photo_ids ) ) {
    $photo_ids = get_post_meta( $post_id, '_noty_photo_ids', true );
}
$photo_ids = is_array( $photo_ids ) ? array_values( array_filter( array_map( 'intval', $photo_ids ) ) ) : array();

$uuid = $get_meta_first( array( 'up_uuid', '_noty_uuid' ), '' );
$reference = $get_meta_first( array( 'up_reference', '_noty_reference' ), '' );
$last_sync = $get_meta_first( array( 'up_last_sync', '_noty_last_sync' ), '' );

$prix = $get_meta_first( array( 'up_prix', '_noty_prix' ), '' );
$loyer = $get_meta_first( array( 'up_loyer', '_noty_loyer' ), '' );
$loyer_periodicite = $get_meta_first( array( 'up_loyer_periodicite', '_noty_loyer_periodicite' ), '' );
$charges_incluses = $get_meta_first( array( 'up_charges_incluses', '_noty_charges_incluses' ), '' );
$montant_charges = $get_meta_first( array( 'up_montant_charges', '_noty_montant_charges' ), '' );
$montant_etat_lieux = $get_meta_first( array( 'up_montant_etat_lieux', '_noty_montant_etat_lieux' ), '' );
$meuble = $get_meta_first( array( 'up_meuble', '_noty_meuble' ), '' );
$montant_depot_garantie = $get_meta_first( array( 'up_montant_depot_garantie', '_noty_montant_depot_garantie' ), '' );

$transaction_type = $get_meta_first( array( 'up_transaction_type', '_noty_transaction_type' ), '' );
$type_honoraires = $get_meta_first( array( 'up_type_honoraires', '_noty_type_honoraires' ), '' );
$honoraires = $get_meta_first( array( 'up_honoraires', '_noty_honoraires' ), '' );
$honoraires_pourcentage = $get_meta_first( array( 'up_honoraires_pourcentage', '_noty_honoraires_pourcentage' ), '' );
$charges_copropriete = $get_meta_first( array( 'up_charges_copropriete', '_noty_charges_copropriete' ), '' );
$frais_acte = $get_meta_first( array( 'up_frais_acte', '_noty_frais_acte' ), '' );
$bouquet = $get_meta_first( array( 'up_bouquet', '_noty_bouquet' ), '' );
$bouquet_hni = $get_meta_first( array( 'up_bouquet_hni', '_noty_bouquet_hni' ), '' );
$bouquet_nv = $get_meta_first( array( 'up_bouquet_nv', '_noty_bouquet_nv' ), '' );
$rente = $get_meta_first( array( 'up_rente', '_noty_rente' ), '' );
$rente_data = $json_decode_if_needed( $rente );

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

$code_postal = $get_meta_first( array( 'up_code_postal', '_noty_code_postal' ), '' );

$dpe_classe = $get_meta_first( array( 'up_dpe_classe', '_noty_dpe_classe' ), '' );
$dpe_value = $get_meta_first( array( 'up_dpe_value', '_noty_dpe_value' ), '' );
$ges_classe = $get_meta_first( array( 'up_ges_classe', '_noty_ges_classe' ), '' );
$ges_value = $get_meta_first( array( 'up_ges_value', '_noty_ges_value' ), '' );

$office_name = '';
$office_crpcen = '';
$contact_name = '';
$contact_tel = '';
$contact_email = '';

if ( is_array( $raw_data ) ) {
    if ( isset( $raw_data['office'] ) && is_array( $raw_data['office'] ) ) {
        $office_name = (string) ( $raw_data['office']['raison_sociale'] ?? '' );
        $office_crpcen = (string) ( $raw_data['office']['crpcen'] ?? '' );
    }
    if ( isset( $raw_data['contact'] ) && is_array( $raw_data['contact'] ) ) {
        $contact_name = (string) ( $raw_data['contact']['nom'] ?? '' );
        $contact_tel = (string) ( $raw_data['contact']['telephone'] ?? '' );
        $contact_email = (string) ( $raw_data['contact']['email'] ?? '' );
    }

    if ( $transaction_type === '' && isset( $raw_data['transaction'] ) ) {
        if ( is_array( $raw_data['transaction'] ) ) {
            $transaction_type = (string) ( $raw_data['transaction']['type'] ?? '' );
        } elseif ( is_string( $raw_data['transaction'] ) ) {
            $transaction_type = (string) $raw_data['transaction'];
        }
    }

    if ( $prix === '' && array_key_exists( 'prix', $raw_data ) ) {
        $prix = $raw_data['prix'];
    }
    if ( $loyer === '' && array_key_exists( 'loyer', $raw_data ) ) {
        $loyer = $raw_data['loyer'];
    }
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

        <?php if ( $reference !== '' || $uuid !== '' || $last_sync !== '' ) : ?>
            <p class="up-immo-single__subtitle">
                <?php
                $ids = array();
                if ( $reference !== '' ) {
                    $ids[] = 'Réf. ' . $reference;
                }
                if ( $uuid !== '' ) {
                    $ids[] = 'UUID ' . $uuid;
                }
                if ( $last_sync !== '' ) {
                    $ids[] = 'Sync ' . $last_sync;
                }
                echo esc_html( implode( ' · ', $ids ) );
                ?>
            </p>
        <?php endif; ?>
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

            <?php if ( $loyer !== '' && $loyer_periodicite !== '' ) : ?>
                <br><small><?php echo esc_html( 'Périodicité : ' . $loyer_periodicite ); ?></small>
            <?php endif; ?>
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

            <?php if ( $ville_name !== '' || $code_postal !== '' ) : ?>
                <br><small><?php echo esc_html( trim( $code_postal . ' ' . $ville_name ) ); ?></small>
            <?php endif; ?>
        </div>
    </section>

    <?php if ( $dpe_classe !== '' || $ges_classe !== '' ) : ?>
        <section class="up-immo-single__highlights">
            <div class="up-immo-single__card">
                <strong>DPE</strong><br>
                <?php
                $d = array();
                if ( $dpe_classe !== '' ) {
                    $d[] = 'Classe ' . $dpe_classe;
                }
                if ( $dpe_value !== '' ) {
                    $d[] = $dpe_value;
                }
                echo esc_html( $d ? implode( ' · ', $d ) : '—' );
                ?>
            </div>
            <div class="up-immo-single__card">
                <strong>GES</strong><br>
                <?php
                $g = array();
                if ( $ges_classe !== '' ) {
                    $g[] = 'Classe ' . $ges_classe;
                }
                if ( $ges_value !== '' ) {
                    $g[] = $ges_value;
                }
                echo esc_html( $g ? implode( ' · ', $g ) : '—' );
                ?>
            </div>
        </section>
    <?php endif; ?>

    <?php
    $details = array();
    if ( $transaction_type !== '' ) {
        $details[] = array( 'Transaction', $transaction_type );
    }
    if ( $type_honoraires !== '' ) {
        $details[] = array( 'Type honoraires', $type_honoraires );
    }
    if ( $honoraires !== '' ) {
        $details[] = array( 'Honoraires', number_format( (float) $honoraires, 0, ',', ' ' ) . ' €' );
    }
    if ( $honoraires_pourcentage !== '' ) {
        $details[] = array( 'Honoraires (%)', $honoraires_pourcentage );
    }
    if ( $charges_copropriete !== '' ) {
        $details[] = array( 'Charges copropriété', number_format( (float) $charges_copropriete, 0, ',', ' ' ) . ' €' );
    }
    if ( $frais_acte !== '' ) {
        $details[] = array( 'Frais d\'acte', number_format( (float) $frais_acte, 0, ',', ' ' ) . ' €' );
    }
    if ( $bouquet !== '' ) {
        $details[] = array( 'Bouquet', number_format( (float) $bouquet, 0, ',', ' ' ) . ' €' );
    }
    if ( $bouquet_hni !== '' ) {
        $details[] = array( 'Bouquet HNI', number_format( (float) $bouquet_hni, 0, ',', ' ' ) . ' €' );
    }
    if ( $bouquet_nv !== '' ) {
        $details[] = array( 'Bouquet NV', number_format( (float) $bouquet_nv, 0, ',', ' ' ) . ' €' );
    }
    if ( is_array( $rente_data ) ) {
        $m = isset( $rente_data['montant'] ) ? $rente_data['montant'] : '';
        $p = isset( $rente_data['periodicite'] ) ? $rente_data['periodicite'] : '';
        if ( $m !== '' ) {
            $details[] = array( 'Rente', number_format( (float) $m, 0, ',', ' ' ) . ' €' . ( $p !== '' ? ' (' . $p . ')' : '' ) );
        }
    }

    if ( $loyer !== '' ) {
        if ( $charges_incluses !== '' ) {
            $details[] = array( 'Charges incluses', ( $charges_incluses === '1' || $charges_incluses === 1 || $charges_incluses === true || $charges_incluses === 'true' ) ? 'Oui' : 'Non' );
        }
        if ( $montant_charges !== '' ) {
            $details[] = array( 'Montant charges', number_format( (float) $montant_charges, 0, ',', ' ' ) . ' €' );
        }
        if ( $montant_etat_lieux !== '' ) {
            $details[] = array( 'État des lieux', number_format( (float) $montant_etat_lieux, 0, ',', ' ' ) . ' €' );
        }
        if ( $meuble !== '' ) {
            $details[] = array( 'Meublé', ( $meuble === '1' || $meuble === 1 || $meuble === true || $meuble === 'true' ) ? 'Oui' : 'Non' );
        }
        if ( $montant_depot_garantie !== '' ) {
            $details[] = array( 'Dépôt de garantie', number_format( (float) $montant_depot_garantie, 0, ',', ' ' ) . ' €' );
        }
    }
    ?>

    <?php if ( ! empty( $details ) ) : ?>
        <section class="up-immo-single__details">
            <h2>Détails</h2>
            <table class="up-immo-single__table">
                <tbody>
                    <?php foreach ( $details as $row ) : ?>
                        <tr>
                            <th><?php echo esc_html( $row[0] ); ?></th>
                            <td><?php echo esc_html( (string) $row[1] ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    <?php endif; ?>

    <?php if ( $post->post_content !== '' ) : ?>
        <section class="up-immo-single__content">
            <h2>Description</h2>
            <?php echo wpautop( wp_kses_post( $post->post_content ) ); ?>
        </section>
    <?php endif; ?>

    <?php if ( $office_name !== '' || $contact_name !== '' || $contact_tel !== '' || $contact_email !== '' ) : ?>
        <section class="up-immo-single__contact">
            <h2>Contact</h2>
            <?php if ( $office_name !== '' ) : ?>
                <p><strong><?php echo esc_html( $office_name ); ?></strong><?php echo $office_crpcen !== '' ? esc_html( ' (' . $office_crpcen . ')' ) : ''; ?></p>
            <?php endif; ?>
            <?php if ( $contact_name !== '' ) : ?>
                <p><?php echo esc_html( $contact_name ); ?></p>
            <?php endif; ?>
            <?php if ( $contact_tel !== '' ) : ?>
                <p><?php echo esc_html( $contact_tel ); ?></p>
            <?php endif; ?>
            <?php if ( $contact_email !== '' ) : ?>
                <p><a href="mailto:<?php echo esc_attr( $contact_email ); ?>"><?php echo esc_html( $contact_email ); ?></a></p>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</article>
<?php
wp_reset_postdata();
