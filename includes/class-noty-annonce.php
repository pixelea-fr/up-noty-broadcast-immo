<?php

class Noty_Annonce {
    public $id = 0;
    public $titre = '';
    public $lien = '';

    public $uuid = '';
    public $reference = '';
    public $last_sync = '';

    public $photos = array();

    public $raw = null;

    public $ids = '';

    public $bien;

    public $office;

    public $contact;

    public function __construct() {
        $this->bien = new Noty_Bien();
        $this->office = (object) array(
            'raison_sociale' => '',
            'crpcen' => '',
        );
        $this->contact = (object) array(
            'nom' => '',
            'telephone' => '',
            'email' => '',
        );
    }

    public static function from_post_id( $post_id ) {
        $post_id = (int) $post_id;
        if ( $post_id <= 0 ) {
            return null;
        }

        $self = new self();
        $self->id = $post_id;
        $self->titre = (string) get_the_title( $post_id );
      
        $self->lien = (string) get_permalink( $post_id );

        $self->uuid = (string) self::get_meta_first( $post_id, array( 'up_uuid', '_noty_uuid' ), '' );
        $self->reference = (string) self::get_meta_first( $post_id, array( 'up_reference', '_noty_reference' ), '' );
        $self->last_sync = (string) self::get_meta_first( $post_id, array( 'up_last_sync', '_noty_last_sync' ), '' );

        $raw = self::get_meta_first( $post_id, array( 'up_raw', '_noty_raw' ), '' );
        $raw_data = self::json_decode_if_needed( $raw );
        $self->raw = $raw_data;

        $photo_ids = get_post_meta( $post_id, 'up_photo_ids', true );
        if ( ! is_array( $photo_ids ) || empty( $photo_ids ) ) {
            $photo_ids = get_post_meta( $post_id, '_noty_photo_ids', true );
        }
        $self->photos = is_array( $photo_ids ) ? array_values( array_filter( array_map( 'intval', $photo_ids ) ) ) : array();

        $self->bien->nature = self::get_term_name( $post_id, 'noty_nature' );
        $self->bien->ville = self::get_term_name( $post_id, 'noty_ville' );
        $self->bien->transaction = self::get_term_name( $post_id, 'noty_transaction' );

        $self->bien->code_postal = (string) self::get_meta_first( $post_id, array( 'up_code_postal', '_noty_code_postal' ), '' );

        $self->bien->surface = (string) self::get_meta_first( $post_id, array( 'up_surface_habitable', '_noty_surface_habitable', 'up_surface', '_noty_surface' ), '' );
        $self->bien->pieces = (string) self::get_meta_first( $post_id, array( 'up_pieces', '_noty_pieces' ), '' );
        $self->bien->chambres = (string) self::get_meta_first( $post_id, array( 'up_chambres', '_noty_chambres' ), '' );
        $self->bien->surface_terrain = (string) self::get_meta_first( $post_id, array( 'up_surface_terrain', '_noty_surface_terrain' ), '' );
        $self->bien->salles_eau = (string) self::get_meta_first( $post_id, array( 'up_salles_eau', '_noty_salles_eau' ), '' );
        $self->bien->salles_bain = (string) self::get_meta_first( $post_id, array( 'up_salles_bain', '_noty_salles_bain' ), '' );
        $self->bien->niveaux = (string) self::get_meta_first( $post_id, array( 'up_niveaux', '_noty_niveaux' ), '' );
        $self->bien->ascenseur = self::format_bool_oui_non( self::get_meta_first( $post_id, array( 'up_ascenseur', '_noty_ascenseur' ), '' ) );
        $self->bien->piscine = self::format_bool_oui_non( self::get_meta_first( $post_id, array( 'up_piscine', '_noty_piscine' ), '' ) );

        $self->bien->surface_string = $self->bien->surface !== '' ? ( $self->bien->surface . ' m²' ) : '';

        $prix_num = self::get_meta_first( $post_id, array( 'up_prix', '_noty_prix' ), '' );
        $prix_hni_num = self::get_meta_first( $post_id, array( 'up_prix_hni', '_noty_prix_hni' ), '' );
        $prix_nv_num = self::get_meta_first( $post_id, array( 'up_prix_nv', '_noty_prix_nv' ), '' );
        $loyer_num = self::get_meta_first( $post_id, array( 'up_loyer', '_noty_loyer' ), '' );

        $self->bien->prix = self::format_eur( $prix_num );
        $self->bien->prix_hni = self::format_eur( $prix_hni_num );
        $self->bien->prix_nv = self::format_eur( $prix_nv_num );
        $self->bien->loyer = self::format_eur( $loyer_num );
        $self->bien->loyer_periodicite = (string) self::get_meta_first( $post_id, array( 'up_loyer_periodicite', '_noty_loyer_periodicite' ), '' );

        $self->bien->charges_incluses = self::format_bool_oui_non( self::get_meta_first( $post_id, array( 'up_charges_incluses', '_noty_charges_incluses' ), '' ) );
        $self->bien->montant_charges = self::format_eur( self::get_meta_first( $post_id, array( 'up_montant_charges', '_noty_montant_charges' ), '' ) );
        $self->bien->montant_etat_lieux = self::format_eur( self::get_meta_first( $post_id, array( 'up_montant_etat_lieux', '_noty_montant_etat_lieux' ), '' ) );
        $self->bien->meuble = self::format_bool_oui_non( self::get_meta_first( $post_id, array( 'up_meuble', '_noty_meuble' ), '' ) );
        $self->bien->montant_depot_garantie = self::format_eur( self::get_meta_first( $post_id, array( 'up_montant_depot_garantie', '_noty_montant_depot_garantie' ), '' ) );

        $self->bien->transaction_type = (string) self::get_meta_first( $post_id, array( 'up_transaction_type', '_noty_transaction_type' ), '' );

        $self->bien->type_honoraires = (string) self::get_meta_first( $post_id, array( 'up_type_honoraires', '_noty_type_honoraires' ), '' );
        $self->bien->honoraires = self::format_eur( self::get_meta_first( $post_id, array( 'up_honoraires', '_noty_honoraires' ), '' ) );
        $self->bien->honoraires_pourcentage = (string) self::get_meta_first( $post_id, array( 'up_honoraires_pourcentage', '_noty_honoraires_pourcentage' ), '' );
        $self->bien->charges_copropriete = self::format_eur( self::get_meta_first( $post_id, array( 'up_charges_copropriete', '_noty_charges_copropriete' ), '' ) );
        $self->bien->frais_acte = self::format_eur( self::get_meta_first( $post_id, array( 'up_frais_acte', '_noty_frais_acte' ), '' ) );

        $self->bien->bouquet = self::format_eur( self::get_meta_first( $post_id, array( 'up_bouquet', '_noty_bouquet' ), '' ) );
        $self->bien->bouquet_hni = self::format_eur( self::get_meta_first( $post_id, array( 'up_bouquet_hni', '_noty_bouquet_hni' ), '' ) );
        $self->bien->bouquet_nv = self::format_eur( self::get_meta_first( $post_id, array( 'up_bouquet_nv', '_noty_bouquet_nv' ), '' ) );
        $self->bien->rente = self::json_decode_if_needed( self::get_meta_first( $post_id, array( 'up_rente', '_noty_rente' ), '' ) );

        $self->bien->dpe_classe = (string) self::get_meta_first( $post_id, array( 'up_dpe_classe', '_noty_dpe_classe' ), '' );
        $self->bien->dpe_value = (string) self::get_meta_first( $post_id, array( 'up_dpe_value', '_noty_dpe_value' ), '' );
        $self->bien->ges_classe = (string) self::get_meta_first( $post_id, array( 'up_ges_classe', '_noty_ges_classe' ), '' );
        $self->bien->ges_value = (string) self::get_meta_first( $post_id, array( 'up_ges_value', '_noty_ges_value' ), '' );

        $dpe_classe = strtolower( trim( (string) $self->bien->dpe_classe ) );
        $ges_classe = strtolower( trim( (string) $self->bien->ges_classe ) );

        $dpe_value = trim( (string) $self->bien->dpe_value );
        $ges_value = trim( (string) $self->bien->ges_value );

        if ( $dpe_classe !== '' ) {
            $self->bien->dpe = self::build_outils_immo_label(
                'dpe',
                $dpe_classe,
                $dpe_value,
                array(
                    'valeurges' => $ges_value,
                )
            );
        }

        if ( $ges_classe !== '' ) {
            $self->bien->ges = self::build_outils_immo_label( 'ges', $ges_classe, $ges_value );
        }

        $self->bien->gse = $self->bien->ges;

        if ( is_array( $raw_data ) ) {
            if ( $self->bien->transaction_type === '' && isset( $raw_data['transaction'] ) ) {
                if ( is_array( $raw_data['transaction'] ) ) {
                    $self->bien->transaction_type = (string) ( $raw_data['transaction']['type'] ?? '' );
                } elseif ( is_string( $raw_data['transaction'] ) ) {
                    $self->bien->transaction_type = (string) $raw_data['transaction'];
                }
            }

            if ( $self->bien->prix === '' && array_key_exists( 'prix', $raw_data ) ) {
                $self->bien->prix = self::format_eur( $raw_data['prix'] );
            }
            if ( $self->bien->prix_hni === '' ) {
                $raw_prix_hni = '';
                if ( isset( $raw_data['transaction'] ) && is_array( $raw_data['transaction'] ) ) {
                    $raw_prix_hni = $raw_data['transaction']['prix_hni'] ?? ( $raw_data['transaction']['prixHni'] ?? '' );
                }
                if ( $raw_prix_hni === '' && array_key_exists( 'prix_hni', $raw_data ) ) {
                    $raw_prix_hni = $raw_data['prix_hni'];
                }
                if ( $raw_prix_hni !== '' && $raw_prix_hni !== null ) {
                    $self->bien->prix_hni = self::format_eur( $raw_prix_hni );
                }
            }
            if ( $self->bien->prix_nv === '' ) {
                $raw_prix_nv = '';
                if ( isset( $raw_data['transaction'] ) && is_array( $raw_data['transaction'] ) ) {
                    $raw_prix_nv = $raw_data['transaction']['prix_nv'] ?? ( $raw_data['transaction']['prixNv'] ?? '' );
                }
                if ( $raw_prix_nv === '' && array_key_exists( 'prix_nv', $raw_data ) ) {
                    $raw_prix_nv = $raw_data['prix_nv'];
                }
                if ( $raw_prix_nv !== '' && $raw_prix_nv !== null ) {
                    $self->bien->prix_nv = self::format_eur( $raw_prix_nv );
                }
            }
            if ( $self->bien->loyer === '' && array_key_exists( 'loyer', $raw_data ) ) {
                $self->bien->loyer = self::format_eur( $raw_data['loyer'] );
            }

            if ( isset( $raw_data['office'] ) && is_array( $raw_data['office'] ) ) {
                $self->office->raison_sociale = (string) ( $raw_data['office']['raison_sociale'] ?? '' );
                $self->office->crpcen = (string) ( $raw_data['office']['crpcen'] ?? '' );
            }
            if ( isset( $raw_data['contact'] ) && is_array( $raw_data['contact'] ) ) {
                $self->contact->nom = (string) ( $raw_data['contact']['nom'] ?? '' );
                $self->contact->telephone = (string) ( $raw_data['contact']['telephone'] ?? '' );
                $self->contact->email = (string) ( $raw_data['contact']['email'] ?? '' );
            }
        }

        $self->bien->transaction_string = '';
        if ( $self->bien->transaction_type === 'location' ) {
            $self->bien->transaction_string = 'à louer';
        } elseif ( $self->bien->transaction_type === 'vente_traditionnelle' ) {
            $self->bien->transaction_string = 'à vendre';
        } elseif ( $self->bien->transaction_type === 'vente_viager' ) {
            $self->bien->transaction_string = 'à vendre (en viager)';
        }

        $price_mode = self::get_price_display_mode();
        $self->bien->prix_ou_loyer = self::resolve_display_price( $self->bien, $price_mode );
        $self->bien->prix_ou_loyer_note = (string) get_option( 'noty_price_display_note', '' );
        $self->bien->prix_ou_loyer_note_tooltip = self::build_price_note_tooltip( $self->bien );

        $self->bien->localisation = trim( $self->bien->code_postal . ' ' . $self->bien->ville );

        $car = array();
        if ( $self->bien->surface !== '' ) {
            $car[] = $self->bien->surface . ' m²';
        }
        if ( $self->bien->pieces !== '' ) {
            $car[] = $self->bien->pieces . ' pièces';
        }
        if ( $self->bien->chambres !== '' ) {
            $car[] = $self->bien->chambres . ' chambres';
        }
        $self->bien->caracteristiques = $car ? implode( ' · ', $car ) : '—';

        $subtitle = array();
        if ( $self->bien->nature !== '' ) {
            $subtitle[] = $self->bien->nature;
        }
        if ( $self->bien->ville !== '' ) {
            $subtitle[] = $self->bien->ville;
        }
        if ( $self->bien->transaction_type !== '' ) {
            $subtitle[] = $self->bien->transaction_type;
        }
        $self->bien->subtitle = $subtitle ? implode( ' · ', $subtitle ) : '';

        $ids = array();
        if ( $self->reference !== '' ) {
            $ids[] = 'Réf. ' . $self->reference;
        }
        if ( $self->uuid !== '' ) {
            $ids[] = 'UUID ' . $self->uuid;
        }
        if ( $self->last_sync !== '' ) {
            $ids[] = 'Sync ' . $self->last_sync;
        }
        $self->ids = $ids ? implode( ' · ', $ids ) : '';

        $charges = array();
        if ( $self->bien->charges_incluses === 'Oui' ) {
            $charges[] = 'Charges incluses';
        } elseif ( $self->bien->charges_incluses === 'Non' ) {
            $charges[] = 'Charges non incluses';
        }
        if ( $self->bien->montant_charges !== '' ) {
            $charges[] = 'Charges: ' . $self->bien->montant_charges;
        }
        $self->bien->charges_resume = $charges ? implode( ' · ', $charges ) : '';

        $self->bien->details = self::build_details( $self->bien );
        $self->bien->resume_details = self::build_resume_details( $self->bien );
        //$self->titre = ucfirst( $self->bien->nature ) . " " . $self->bien->surface_string . " " . $self->bien->transaction_string . " à " . $self->bien->ville;
        $self->titre = ucfirst( $self->bien->nature ) . " à " . $self->bien->ville;
        return $self;
    }

    private static function build_outils_immo_label( $type, $lettre, $valeur, $extra_args = array() ) {
        $type = (string) $type;
        $lettre = (string) $lettre;
        $valeur = (string) $valeur;
        $extra_args = is_array( $extra_args ) ? $extra_args : array();

        $modele = '2021';
        if ( $valeur === '' || ! is_numeric( $valeur ) ) {
            $modele = 'light';
        }

        $args = array(
            'type' => $type,
            'modele' => $modele,
            'lettre' => $lettre,
            'valeur' => $modele === 'light' ? '' : (string) (int) $valeur,
        );

        if ( $type === 'dpe' && $modele === '2021' ) {
            if ( isset( $extra_args['valeurges'] ) && $extra_args['valeurges'] !== '' && is_numeric( $extra_args['valeurges'] ) ) {
                $args['valeurges'] = (string) (int) $extra_args['valeurges'];
            }
        }

        $url = add_query_arg( $args, 'https://www.outils.immo/outils-immo.php' );

        return (object) array(
            'type' => $type,
            'modele' => $modele,
            'lettre' => $lettre,
            'valeur' => $valeur,
            'image_url' => esc_url_raw( $url ),
        );
    }

    public static function order_items_in_columns( $items, $columns = 2 ) {
        if ( ! is_array( $items ) ) {
            return array();
        }

        $columns = (int) $columns;
        if ( $columns <= 1 ) {
            return array_values( $items );
        }

        $items = array_values( $items );
        $n = count( $items );
        if ( $n === 0 ) {
            return array();
        }

        $rows = (int) ceil( $n / $columns );
        $ordered = array();

        for ( $r = 0; $r < $rows; $r++ ) {
            for ( $c = 0; $c < $columns; $c++ ) {
                $i = $r + ( $c * $rows );
                if ( isset( $items[ $i ] ) ) {
                    $ordered[] = $items[ $i ];
                }
            }
        }

        return $ordered;
    }

    private static function add_detail( &$details, $meta, $label, $value ) {
        if ( $value === '' || $value === null ) {
            return;
        }
        $details[] = array(
            'meta'  => (string) $meta,
            'label' => (string) $label,
            'value' => $value,
        );
    }

    private static function build_common_details( Noty_Bien $bien ) {
        $common = array();
        
        self::add_detail( $common, 'surface', 'Surface', $bien->surface !== '' ? $bien->surface . ' m²' : '' );
        self::add_detail( $common, 'pieces', 'Nombre de pièces', $bien->pieces );
        self::add_detail( $common, 'chambres', 'Nombre de chambres', $bien->chambres );
        
        return $common;
    }

    private static function build_resume_details( Noty_Bien $bien ) {
        $transaction_type = strtolower( trim( (string) $bien->transaction_type ) );
        return self::build_details_from_config( $bien, $transaction_type, 'resume' );
    }

    private static function build_resume_details_location( Noty_Bien $bien ) {
        $resume = array();

       // self::add_detail( $resume, 'loyer', 'Loyer', $bien->loyer );
        self::add_detail( $resume, 'charges_incluses', 'Charges incluses', $bien->charges_incluses );
        self::add_detail( $resume, 'surface', 'Surface', $bien->surface !== '' ? $bien->surface . ' m²' : '' );
        self::add_detail( $resume, 'pieces', 'Pièces', $bien->pieces );

        return $resume;
    }

    private static function build_resume_details_vente_traditionnelle( Noty_Bien $bien ) {
        $resume = array();

       // self::add_detail( $resume, 'prix', 'Prix', $bien->prix );
        self::add_detail( $resume, 'surface', 'Surface', $bien->surface !== '' ? $bien->surface . ' m²' : '' );
        self::add_detail( $resume, 'pieces', 'Pièces', $bien->pieces );
        self::add_detail( $resume, 'honoraires', 'Honoraires', $bien->honoraires );

        return $resume;
    }

    private static function build_resume_details_vente_viager( Noty_Bien $bien ) {
        $resume = array();

        self::add_detail( $resume, 'bouquet', 'Bouquet', $bien->bouquet );
        
        if ( is_array( $bien->rente ) ) {
            $m = isset( $bien->rente['montant'] ) ? $bien->rente['montant'] : '';
            $p = isset( $bien->rente['periodicite'] ) ? $bien->rente['periodicite'] : '';
            if ( $m !== '' ) {
                self::add_detail( $resume, 'rente', 'Rente', number_format( (float) $m, 0, ',', ' ' ) . ' €' . ( $p !== '' ? ' (' . $p . ')' : '' ) );
            }
        }
        
        self::add_detail( $resume, 'surface', 'Surface', $bien->surface !== '' ? $bien->surface . ' m²' : '' );
        self::add_detail( $resume, 'pieces', 'Pièces', $bien->pieces );

        return $resume;
    }

    private static function build_details( Noty_Bien $bien ) {
        $transaction_type = strtolower( trim( (string) $bien->transaction_type ) );
        return self::build_details_from_config( $bien, $transaction_type, 'details' );
    }

    private static function build_details_from_config( Noty_Bien $bien, $transaction_type, $context = 'details' ) {
        if ( $transaction_type === '' ) {
            $transaction_type = 'vente_traditionnelle';
        }

        $option_name = 'noty_fields_' . $transaction_type . '_' . $context;
        $config = get_option( $option_name );
        
        if ( ! is_array( $config ) || empty( $config ) ) {
            $config = Noty_Fields_Config::get_default_config( $transaction_type, $context );
        }

        $available_fields = Noty_Fields_Config::get_fields_for_type( $transaction_type );
        $details = array();

        foreach ( $config as $field_key ) {
            if ( ! isset( $available_fields[ $field_key ] ) ) {
                continue;
            }

            $field_config = $available_fields[ $field_key ];
            $label = $field_config['label'];
            $value = self::get_field_value( $bien, $field_key, $field_config );

            self::add_detail( $details, $field_key, $label, $value );
        }

        return $details;
    }

    private static function get_field_value( Noty_Bien $bien, $field_key, $field_config ) {
        $getter = isset( $field_config['getter'] ) ? $field_config['getter'] : $field_key;
        $value = isset( $bien->$getter ) ? $bien->$getter : '';
        
        if ( has_filter( 'noty_immo_meta_value_' . $field_key ) ) {
            $value = apply_filters( 'noty_immo_meta_value_' . $field_key, $value, null, $bien, null, null );
        }
        
        return $value;
    }

    private static function build_details_location( Noty_Bien $bien ) {
        $details = array();

        self::add_detail( $details, 'transaction_type', 'Transaction', $bien->transaction_type );
        self::add_detail( $details, 'type_honoraires', 'Type honoraires', $bien->type_honoraires );
        self::add_detail( $details, 'honoraires', 'Honoraires', $bien->honoraires );
        self::add_detail( $details, 'honoraires_pourcentage', 'Honoraires (%)', $bien->honoraires_pourcentage );
        
        self::add_detail( $details, 'loyer', 'Loyer', $bien->loyer );
        self::add_detail( $details, 'charges_incluses', 'Charges incluses', $bien->charges_incluses );
        self::add_detail( $details, 'montant_charges', 'Montant charges', $bien->montant_charges );
        self::add_detail( $details, 'montant_etat_lieux', 'État des lieux', $bien->montant_etat_lieux );
        self::add_detail( $details, 'meuble', 'Meublé', $bien->meuble );
        self::add_detail( $details, 'montant_depot_garantie', 'Dépôt de garantie', $bien->montant_depot_garantie );
        
        $details = array_merge( $details, self::build_common_details( $bien ) );

        return $details;
    }

    private static function build_details_vente_traditionnelle( Noty_Bien $bien ) {
        $details = array();

        self::add_detail( $details, 'transaction_type', 'Transaction', $bien->transaction_type );
        self::add_detail( $details, 'type_honoraires', 'Type honoraires', $bien->type_honoraires );
        self::add_detail( $details, 'honoraires', 'Honoraires', $bien->honoraires );
        self::add_detail( $details, 'honoraires_pourcentage', 'Honoraires (%)', $bien->honoraires_pourcentage );
        self::add_detail( $details, 'charges_copropriete', 'Charges copropriété', $bien->charges_copropriete );
        self::add_detail( $details, 'frais_acte', 'Frais d\'acte', $bien->frais_acte );
        
        $details = array_merge( $details, self::build_common_details( $bien ) );

        return $details;
    }

    private static function build_details_vente_viager( Noty_Bien $bien ) {
        $details = array();

        self::add_detail( $details, 'transaction_type', 'Transaction', $bien->transaction_type );
        self::add_detail( $details, 'type_honoraires', 'Type honoraires', $bien->type_honoraires );
        self::add_detail( $details, 'honoraires', 'Honoraires', $bien->honoraires );
        self::add_detail( $details, 'honoraires_pourcentage', 'Honoraires (%)', $bien->honoraires_pourcentage );
        self::add_detail( $details, 'charges_copropriete', 'Charges copropriété', $bien->charges_copropriete );
        self::add_detail( $details, 'frais_acte', 'Frais d\'acte', $bien->frais_acte );
        
        self::add_detail( $details, 'bouquet', 'Bouquet', $bien->bouquet );
        self::add_detail( $details, 'bouquet_hni', 'Bouquet HNI', $bien->bouquet_hni );
        self::add_detail( $details, 'bouquet_nv', 'Bouquet NV', $bien->bouquet_nv );

        if ( is_array( $bien->rente ) ) {
            $m = isset( $bien->rente['montant'] ) ? $bien->rente['montant'] : '';
            $p = isset( $bien->rente['periodicite'] ) ? $bien->rente['periodicite'] : '';
            if ( $m !== '' ) {
                self::add_detail( $details, 'rente', 'Rente', number_format( (float) $m, 0, ',', ' ' ) . ' €' . ( $p !== '' ? ' (' . $p . ')' : '' ) );
            }
        }
        
        $details = array_merge( $details, self::build_common_details( $bien ) );

        return $details;
    }

    private static function get_meta_first( $post_id, $keys, $default = '' ) {
        $keys = is_array( $keys ) ? $keys : array( $keys );
        foreach ( $keys as $key ) {
            $val = get_post_meta( $post_id, $key, true );
            if ( $val !== '' && $val !== null ) {
                return $val;
            }
        }
        return $default;
    }

    private static function json_decode_if_needed( $value ) {
        if ( is_array( $value ) ) {
            return $value;
        }
        if ( ! is_string( $value ) || $value === '' ) {
            return null;
        }
        $decoded = json_decode( $value, true );
        return is_array( $decoded ) ? $decoded : null;
    }

    private static function get_term_name( $post_id, $taxonomy ) {
        $terms = wp_get_post_terms( $post_id, $taxonomy );
        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return '';
        }
        return (string) $terms[0]->name;
    }

    private static function get_price_display_mode() {
        $mode = (string) get_option( 'noty_price_display_mode', 'prix' );
        return in_array( $mode, array( 'prix', 'prix_hni' ), true ) ? $mode : 'prix';
    }

    private static function build_price_note_tooltip( Noty_Bien $bien ) {
        $type_honoraires = trim( (string) $bien->type_honoraires );
        $honoraires_pourcentage = trim( (string) $bien->honoraires_pourcentage );

        if ( $type_honoraires === '' && $honoraires_pourcentage === '' ) {
            return '';
        }

        $parts = array();
        if ( $type_honoraires !== '' ) {
            $type_honoraires_normalized = strtolower( remove_accents( $type_honoraires ) );

            if ( strpos( $type_honoraires_normalized, 'acquereur' ) !== false ) {
                $parts[] = 'Honoraires à la charge de l\'acquéreur inclus dans le prix';
            } elseif ( strpos( $type_honoraires_normalized, 'vendeur' ) !== false ) {
                $parts[] = 'Honoraires inclus dans le prix';
            } else {
                $parts[] = 'Type honoraires : ' . $type_honoraires;
            }
        }
        if ( $honoraires_pourcentage !== '' ) {
            $percentage = $honoraires_pourcentage;
            if ( strpos( $percentage, '%' ) === false ) {
                $percentage .= ' %';
            }
            $parts[] = 'Montant des Honoraires: ' . $percentage;
        }

        return implode( "\n", $parts );
    }

    private static function resolve_display_price( Noty_Bien $bien, $price_mode ) {
        if ( strtolower( trim( (string) $bien->transaction_type ) ) === 'location' ) {
            return $bien->loyer !== '' ? $bien->loyer : $bien->prix;
        }

        if ( $price_mode === 'prix_hni' ) {
            if ( $bien->prix_hni !== '' ) {
                return $bien->prix_hni;
            }

            return $bien->prix !== '' ? $bien->prix : $bien->prix_nv;
        }

        if ( $bien->prix !== '' ) {
            return $bien->prix;
        }

        return $bien->prix_nv !== '' ? $bien->prix_nv : $bien->prix_hni;
    }

    private static function format_eur( $value ) {
        if ( $value === '' || $value === null ) {
            return '';
        }
        return number_format( (float) $value, 0, ',', ' ' ) . ' €';
    }

    private static function format_bool_oui_non( $value ) {
        if ( $value === '' || $value === null ) {
            return '';
        }
        return ( $value === '1' || $value === 1 || $value === true || $value === 'true' ) ? 'Oui' : 'Non';
    }
}
