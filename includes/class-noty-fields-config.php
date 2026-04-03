<?php

class Noty_Fields_Config {
    
    public static function get_available_fields() {
        return array(
            'transaction_type' => array(
                'label' => 'Transaction',
                'types' => array( 'location', 'vente_traditionnelle', 'vente_viager' ),
                'getter' => 'transaction_type',
            ),
            'type_honoraires' => array(
                'label' => 'Type honoraires',
                'types' => array( 'location', 'vente_traditionnelle', 'vente_viager' ),
                'getter' => 'type_honoraires',
            ),
            'honoraires' => array(
                'label' => 'Honoraires',
                'types' => array( 'location', 'vente_traditionnelle', 'vente_viager' ),
                'getter' => 'honoraires',
            ),
            'honoraires_pourcentage' => array(
                'label' => 'Honoraires (%)',
                'types' => array( 'location', 'vente_traditionnelle', 'vente_viager' ),
                'getter' => 'honoraires_pourcentage',
            ),
            'loyer' => array(
                'label' => 'Loyer',
                'types' => array( 'location' ),
                'getter' => 'loyer',
            ),
            'charges_incluses' => array(
                'label' => 'Charges incluses',
                'types' => array( 'location' ),
                'getter' => 'charges_incluses',
            ),
            'montant_charges' => array(
                'label' => 'Montant charges',
                'types' => array( 'location' ),
                'getter' => 'montant_charges',
            ),
            'montant_etat_lieux' => array(
                'label' => 'État des lieux',
                'types' => array( 'location' ),
                'getter' => 'montant_etat_lieux',
            ),
            'meuble' => array(
                'label' => 'Meublé',
                'types' => array( 'location' ),
                'getter' => 'meuble',
            ),
            'montant_depot_garantie' => array(
                'label' => 'Dépôt de garantie',
                'types' => array( 'location' ),
                'getter' => 'montant_depot_garantie',
            ),
            'charges_copropriete' => array(
                'label' => 'Charges copropriété',
                'types' => array( 'vente_traditionnelle', 'vente_viager' ),
                'getter' => 'charges_copropriete',
            ),
            'frais_acte' => array(
                'label' => 'Frais d\'acte',
                'types' => array( 'vente_traditionnelle', 'vente_viager' ),
                'getter' => 'frais_acte',
            ),
            'bouquet' => array(
                'label' => 'Bouquet',
                'types' => array( 'vente_viager' ),
                'getter' => 'bouquet',
            ),
            'bouquet_hni' => array(
                'label' => 'Bouquet HNI',
                'types' => array( 'vente_viager' ),
                'getter' => 'bouquet_hni',
            ),
            'bouquet_nv' => array(
                'label' => 'Bouquet NV',
                'types' => array( 'vente_viager' ),
                'getter' => 'bouquet_nv',
            ),
            'rente' => array(
                'label' => 'Rente',
                'types' => array( 'vente_viager' ),
                'getter' => 'rente',
            ),
            'surface' => array(
                'label' => 'Surface',
                'types' => array( 'location', 'vente_traditionnelle', 'vente_viager' ),
                'getter' => 'surface',
            ),
            'pieces' => array(
                'label' => 'Nombre de pièces',
                'types' => array( 'location', 'vente_traditionnelle', 'vente_viager' ),
                'getter' => 'pieces',
            ),
            'chambres' => array(
                'label' => 'Nombre de chambres',
                'types' => array( 'location', 'vente_traditionnelle', 'vente_viager' ),
                'getter' => 'chambres',
            ),
            'salles_eau' => array(
                'label' => 'Nombre de salles d\'eau',
                'types' => array( 'location', 'vente_traditionnelle', 'vente_viager' ),
                'getter' => 'salles_eau',
            ),
            'salles_bain' => array(
                'label' => 'Nombre de salles de bain',
                'types' => array( 'location', 'vente_traditionnelle', 'vente_viager' ),
                'getter' => 'salles_bain',
            ),
            'niveaux' => array(
                'label' => 'Nombre de niveaux',
                'types' => array( 'location', 'vente_traditionnelle', 'vente_viager' ),
                'getter' => 'niveaux',
            ),
            'ascenseur' => array(
                'label' => 'Ascenseur',
                'types' => array( 'location', 'vente_traditionnelle', 'vente_viager' ),
                'getter' => 'ascenseur',
            ),
            'piscine' => array(
                'label' => 'Piscine',
                'types' => array( 'location', 'vente_traditionnelle', 'vente_viager' ),
                'getter' => 'piscine',
            ),
        );
    }

    public static function get_fields_for_type( $transaction_type ) {
        $all_fields = self::get_available_fields();
        $type_fields = array();

        foreach ( $all_fields as $key => $field ) {
            if ( in_array( $transaction_type, $field['types'], true ) ) {
                $type_fields[ $key ] = $field;
            }
        }

        return $type_fields;
    }

    public static function get_default_config( $transaction_type, $context = 'details' ) {
        $defaults = array(
            'location' => array(
                'details' => array( 'transaction_type', 'type_honoraires', 'honoraires', 'honoraires_pourcentage', 'loyer', 'charges_incluses', 'montant_charges', 'montant_etat_lieux', 'meuble', 'montant_depot_garantie', 'surface', 'pieces', 'chambres' ),
                'resume' => array( 'charges_incluses', 'surface', 'pieces' ),
            ),
            'vente_traditionnelle' => array(
                'details' => array( 'transaction_type', 'type_honoraires', 'honoraires', 'honoraires_pourcentage', 'charges_copropriete', 'frais_acte', 'surface', 'pieces', 'chambres' ),
                'resume' => array( 'surface', 'pieces', 'honoraires' ),
            ),
            'vente_viager' => array(
                'details' => array( 'transaction_type', 'type_honoraires', 'honoraires', 'honoraires_pourcentage', 'charges_copropriete', 'frais_acte', 'bouquet', 'bouquet_hni', 'bouquet_nv', 'rente', 'surface', 'pieces', 'chambres' ),
                'resume' => array( 'bouquet', 'rente', 'surface', 'pieces' ),
            ),
        );

        if ( isset( $defaults[ $transaction_type ][ $context ] ) ) {
            return $defaults[ $transaction_type ][ $context ];
        }

        return array();
    }
}
