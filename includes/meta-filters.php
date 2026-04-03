<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function noty_immo_meta_value_default( $value, $meta, $row, $bien, $annonce, $post_id ) {
    return $value;
}
add_filter( 'noty_immo_meta_value', 'noty_immo_meta_value_default', 10, 6 );

function noty_immo_register_meta_value_filters() {
    $metas = array(
        'transaction_type',
        'type_honoraires',
        'honoraires',
        'honoraires_pourcentage',
        'charges_copropriete',
        'frais_acte',
        'bouquet',
        'bouquet_hni',
        'bouquet_nv',
        'rente',
        'charges_incluses',
        'montant_charges',
        'montant_etat_lieux',
        'meuble',
        'montant_depot_garantie',
        'surface',
        'surface_terrain',
        'pieces',
        'chambres',
        'salles_eau',
        'salles_bain',
        'niveaux',
        'ascenseur',
        'piscine',
        'loyer',
        'prix',
    );

    foreach ( $metas as $meta ) {
        add_filter(
            'noty_immo_meta_value_' . $meta,
            function( $value, $row, $bien, $annonce, $post_id ) {
                return $value;
            },
            10,
            5
        );
    }
}
add_action( 'plugins_loaded', 'noty_immo_register_meta_value_filters', 20 );

function noty_immo_meta_value_honoraires_pourcentage( $value, $row, $bien, $annonce, $post_id ) {
    if ( $value === '' || $value === null ) {
        return $value;
    }

    if ( is_string( $value ) ) {
        $trim = trim( $value );

        if ( $trim === '' ) {
            return $value;
        }

        if ( substr( $trim, -1 ) === '%' ) {
            return $trim;
        }

        if ( is_numeric( $trim ) ) {
            $num = (float) $trim;
            if ( $num > 0 && $num <= 1 ) {
                $num = $num * 100;
            }

            $formatted = rtrim( rtrim( number_format( $num, 2, '.', '' ), '0' ), '.' );
            return $formatted . '%';
        }
    }

    if ( is_numeric( $value ) ) {
        $num = (float) $value;
        if ( $num > 0 && $num <= 1 ) {
            $num = $num * 100;
        }
        $formatted = rtrim( rtrim( number_format( $num, 2, '.', '' ), '0' ), '.' );
        return $formatted . '%';
    }

    return $value;
}
add_filter( 'noty_immo_meta_value_honoraires_pourcentage', 'noty_immo_meta_value_honoraires_pourcentage', 10, 5 );

function noty_immo_meta_value_transaction_type( $value, $row, $bien, $annonce, $post_id ) {
    if ( $value === '' || $value === null ) {
        return $value;
    }

    $v = strtolower( trim( (string) $value ) );

    if ( $v === 'vente_viager' ) {
        return 'Viager';
    }
    if ( $v === 'vente_traditionnelle' ) {
        return 'Vente';
    }
    if ( $v === 'location' ) {
        return 'Location';
    }

    return $value;
}
add_filter( 'noty_immo_meta_value_transaction_type', 'noty_immo_meta_value_transaction_type', 10, 5 );

function noty_immo_meta_value_type_honoraires( $value, $row, $bien, $annonce, $post_id ) {
    if ( $value === '' || $value === null ) {
        return $value;
    }

    $v = strtolower( trim( (string) $value ) );

    if ( $v === 'charge_acquereur' ) {
        return 'Charge acquéreur';
    }
    if ( $v === 'charge_vendeur' ) {
        return 'Charge vendeur';
    }
    if ( $v === 'partage' ) {
        return 'Partage';
    }

    return $value;
}
add_filter( 'noty_immo_meta_value_type_honoraires', 'noty_immo_meta_value_type_honoraires', 10, 5 );

function noty_immo_meta_value_surface( $value, $row, $bien, $annonce, $post_id ) {
    if ( $value === '' || $value === null ) {
        return $value;
    }
    // Vérifier si "m²" est déjà présent pour éviter le double formatage
    if ( strpos( $value, 'm²' ) !== false ) {
        return $value;
    }
    return $value . ' m²';
}
add_filter( 'noty_immo_meta_value_surface', 'noty_immo_meta_value_surface', 10, 5 );

function noty_immo_meta_value_surface_terrain( $value, $row, $bien, $annonce, $post_id ) {
    if ( $value === '' || $value === null ) {
        return $value;
    }
    // Vérifier si "m²" est déjà présent pour éviter le double formatage
    if ( strpos( $value, 'm²' ) !== false ) {
        return $value;
    }
    return $value . ' m²';
}
add_filter( 'noty_immo_meta_value_surface_terrain', 'noty_immo_meta_value_surface_terrain', 10, 5 );

function noty_immo_meta_value_rente( $value, $row, $bien, $annonce, $post_id ) {
    if ( ! is_array( $value ) ) {
        return $value;
    }
    
    $m = isset( $value['montant'] ) ? $value['montant'] : '';
    $p = isset( $value['periodicite'] ) ? $value['periodicite'] : '';
    
    if ( $m === '' || $m === null ) {
        return '';
    }
    
    return number_format( (float) $m, 0, ',', ' ' ) . ' €' . ( $p !== '' ? ' (' . $p . ')' : '' );
}
add_filter( 'noty_immo_meta_value_rente', 'noty_immo_meta_value_rente', 10, 5 );
