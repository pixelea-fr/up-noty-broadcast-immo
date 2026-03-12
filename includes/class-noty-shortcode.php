<?php

class Noty_Shortcode {
    public function __construct() {
        add_shortcode( 'noty_annonces', array( $this, 'render_annonces' ) );
        add_shortcode( 'noty_annonce', array( $this, 'render_annonce' ) );
        add_filter( 'render_block', array( $this, 'render_block_shortcodes' ), 9, 2 );
    }

    private function format_eur( $value ) {
        if ( $value === '' || $value === null ) {
            return '';
        }
        return number_format( (float) $value, 0, ',', ' ' ) . ' €';
    }

    private function format_bool_oui_non( $value ) {
        if ( $value === '' || $value === null ) {
            return '';
        }
        return ( $value === '1' || $value === 1 || $value === true || $value === 'true' ) ? 'Oui' : 'Non';
    }

    private function get_meta_first( $post_id, $keys, $default = '' ) {
        $keys = is_array( $keys ) ? $keys : array( $keys );
        foreach ( $keys as $key ) {
            $val = get_post_meta( $post_id, $key, true );
            if ( $val !== '' && $val !== null ) {
                return $val;
            }
        }
        return $default;
    }

    private function json_decode_if_needed( $value ) {
        if ( is_array( $value ) ) {
            return $value;
        }
        if ( ! is_string( $value ) || $value === '' ) {
            return null;
        }
        $decoded = json_decode( $value, true );
        return is_array( $decoded ) ? $decoded : null;
    }

    private function get_term_name( $post_id, $taxonomy ) {
        $terms = wp_get_post_terms( $post_id, $taxonomy );
        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return '';
        }
        return (string) $terms[0]->name;
    }

    private function build_annonce_data( $post_id ) {
        $post_id = (int) $post_id;
        if ( $post_id <= 0 ) {
            return array();
        }

        $raw = $this->get_meta_first( $post_id, array( 'up_raw', '_noty_raw' ), '' );
        $raw_data = $this->json_decode_if_needed( $raw );

        $photo_ids = get_post_meta( $post_id, 'up_photo_ids', true );
        if ( ! is_array( $photo_ids ) || empty( $photo_ids ) ) {
            $photo_ids = get_post_meta( $post_id, '_noty_photo_ids', true );
        }
        $photo_ids = is_array( $photo_ids ) ? array_values( array_filter( array_map( 'intval', $photo_ids ) ) ) : array();

        $data = array(
            'post_id' => $post_id,
            'title' => get_the_title( $post_id ),
            'permalink' => get_permalink( $post_id ),
            'uuid' => $this->get_meta_first( $post_id, array( 'up_uuid', '_noty_uuid' ), '' ),
            'reference' => $this->get_meta_first( $post_id, array( 'up_reference', '_noty_reference' ), '' ),
            'last_sync' => $this->get_meta_first( $post_id, array( 'up_last_sync', '_noty_last_sync' ), '' ),
            'photo_ids' => $photo_ids,
            'prix' => $this->get_meta_first( $post_id, array( 'up_prix', '_noty_prix' ), '' ),
            'loyer' => $this->get_meta_first( $post_id, array( 'up_loyer', '_noty_loyer' ), '' ),
            'loyer_periodicite' => $this->get_meta_first( $post_id, array( 'up_loyer_periodicite', '_noty_loyer_periodicite' ), '' ),
            'charges_incluses' => $this->get_meta_first( $post_id, array( 'up_charges_incluses', '_noty_charges_incluses' ), '' ),
            'montant_charges' => $this->get_meta_first( $post_id, array( 'up_montant_charges', '_noty_montant_charges' ), '' ),
            'montant_etat_lieux' => $this->get_meta_first( $post_id, array( 'up_montant_etat_lieux', '_noty_montant_etat_lieux' ), '' ),
            'meuble' => $this->get_meta_first( $post_id, array( 'up_meuble', '_noty_meuble' ), '' ),
            'montant_depot_garantie' => $this->get_meta_first( $post_id, array( 'up_montant_depot_garantie', '_noty_montant_depot_garantie' ), '' ),
            'transaction_type' => $this->get_meta_first( $post_id, array( 'up_transaction_type', '_noty_transaction_type' ), '' ),
            'type_honoraires' => $this->get_meta_first( $post_id, array( 'up_type_honoraires', '_noty_type_honoraires' ), '' ),
            'honoraires' => $this->get_meta_first( $post_id, array( 'up_honoraires', '_noty_honoraires' ), '' ),
            'honoraires_pourcentage' => $this->get_meta_first( $post_id, array( 'up_honoraires_pourcentage', '_noty_honoraires_pourcentage' ), '' ),
            'charges_copropriete' => $this->get_meta_first( $post_id, array( 'up_charges_copropriete', '_noty_charges_copropriete' ), '' ),
            'frais_acte' => $this->get_meta_first( $post_id, array( 'up_frais_acte', '_noty_frais_acte' ), '' ),
            'bouquet' => $this->get_meta_first( $post_id, array( 'up_bouquet', '_noty_bouquet' ), '' ),
            'bouquet_hni' => $this->get_meta_first( $post_id, array( 'up_bouquet_hni', '_noty_bouquet_hni' ), '' ),
            'bouquet_nv' => $this->get_meta_first( $post_id, array( 'up_bouquet_nv', '_noty_bouquet_nv' ), '' ),
            'rente' => $this->json_decode_if_needed( $this->get_meta_first( $post_id, array( 'up_rente', '_noty_rente' ), '' ) ),
            'surface' => '',
            'pieces' => $this->get_meta_first( $post_id, array( 'up_nb_pieces', '_noty_nb_pieces' ), '' ),
            'chambres' => $this->get_meta_first( $post_id, array( 'up_nb_chambres', '_noty_nb_chambres' ), '' ),
            'code_postal' => $this->get_meta_first( $post_id, array( 'up_code_postal', '_noty_code_postal' ), '' ),
            'dpe_classe' => $this->get_meta_first( $post_id, array( 'up_dpe_classe', '_noty_dpe_classe' ), '' ),
            'dpe_value' => $this->get_meta_first( $post_id, array( 'up_dpe_value', '_noty_dpe_value' ), '' ),
            'ges_classe' => $this->get_meta_first( $post_id, array( 'up_ges_classe', '_noty_ges_classe' ), '' ),
            'ges_value' => $this->get_meta_first( $post_id, array( 'up_ges_value', '_noty_ges_value' ), '' ),
            'ville_name' => $this->get_term_name( $post_id, 'noty_ville' ),
            'nature_name' => $this->get_term_name( $post_id, 'noty_nature' ),
            'transaction_name' => $this->get_term_name( $post_id, 'noty_transaction' ),
            'raw' => $raw_data,
            'office_name' => '',
            'office_crpcen' => '',
            'contact_name' => '',
            'contact_tel' => '',
            'contact_email' => '',
        );

        $surface = $this->get_meta_first( $post_id, array( 'up_surface_habitable', '_noty_surface_habitable', 'up_surface', '_noty_surface' ), '' );
        $data['surface'] = $surface;

        if ( is_array( $raw_data ) ) {
            if ( $data['transaction_type'] === '' && isset( $raw_data['transaction'] ) ) {
                if ( is_array( $raw_data['transaction'] ) ) {
                    $data['transaction_type'] = (string) ( $raw_data['transaction']['type'] ?? '' );
                } elseif ( is_string( $raw_data['transaction'] ) ) {
                    $data['transaction_type'] = (string) $raw_data['transaction'];
                }
            }
            if ( $data['prix'] === '' && array_key_exists( 'prix', $raw_data ) ) {
                $data['prix'] = $raw_data['prix'];
            }
            if ( $data['loyer'] === '' && array_key_exists( 'loyer', $raw_data ) ) {
                $data['loyer'] = $raw_data['loyer'];
            }

            if ( isset( $raw_data['office'] ) && is_array( $raw_data['office'] ) ) {
                $data['office_name'] = (string) ( $raw_data['office']['raison_sociale'] ?? '' );
                $data['office_crpcen'] = (string) ( $raw_data['office']['crpcen'] ?? '' );
            }
            if ( isset( $raw_data['contact'] ) && is_array( $raw_data['contact'] ) ) {
                $data['contact_name'] = (string) ( $raw_data['contact']['nom'] ?? '' );
                $data['contact_tel'] = (string) ( $raw_data['contact']['telephone'] ?? '' );
                $data['contact_email'] = (string) ( $raw_data['contact']['email'] ?? '' );
            }
        }

        return $data;
    }

    private function build_annonce_view( $data ) {
        $data = is_array( $data ) ? $data : array();

        $annonce = (object) array(
            'id' => (int) ( $data['post_id'] ?? 0 ),
            'titre' => (string) ( $data['title'] ?? '' ),
            'lien' => (string) ( $data['permalink'] ?? '' ),
            'uuid' => (string) ( $data['uuid'] ?? '' ),
            'reference' => (string) ( $data['reference'] ?? '' ),
            'last_sync' => (string) ( $data['last_sync'] ?? '' ),
            'photos' => (array) ( $data['photo_ids'] ?? array() ),
            'raw' => $data['raw'] ?? null,
        );

        $prix_num = $data['prix'] ?? '';
        $loyer_num = $data['loyer'] ?? '';

        $annonce->bien = (object) array(
            'nature' => (string) ( $data['nature_name'] ?? '' ),
            'ville' => (string) ( $data['ville_name'] ?? '' ),
            'code_postal' => (string) ( $data['code_postal'] ?? '' ),
            'transaction' => (string) ( $data['transaction_name'] ?? '' ),
            'transaction_type' => (string) ( $data['transaction_type'] ?? '' ),
            'surface' => (string) ( $data['surface'] ?? '' ),
            'pieces' => (string) ( $data['pieces'] ?? '' ),
            'chambres' => (string) ( $data['chambres'] ?? '' ),
            'prix' => $this->format_eur( $prix_num ),
            'loyer' => $this->format_eur( $loyer_num ),
            'loyer_periodicite' => (string) ( $data['loyer_periodicite'] ?? '' ),
            'charges_incluses' => $this->format_bool_oui_non( $data['charges_incluses'] ?? '' ),
            'montant_charges' => $this->format_eur( $data['montant_charges'] ?? '' ),
            'montant_etat_lieux' => $this->format_eur( $data['montant_etat_lieux'] ?? '' ),
            'meuble' => $this->format_bool_oui_non( $data['meuble'] ?? '' ),
            'montant_depot_garantie' => $this->format_eur( $data['montant_depot_garantie'] ?? '' ),
            'type_honoraires' => (string) ( $data['type_honoraires'] ?? '' ),
            'honoraires' => $this->format_eur( $data['honoraires'] ?? '' ),
            'honoraires_pourcentage' => (string) ( $data['honoraires_pourcentage'] ?? '' ),
            'charges_copropriete' => $this->format_eur( $data['charges_copropriete'] ?? '' ),
            'frais_acte' => $this->format_eur( $data['frais_acte'] ?? '' ),
            'bouquet' => $this->format_eur( $data['bouquet'] ?? '' ),
            'bouquet_hni' => $this->format_eur( $data['bouquet_hni'] ?? '' ),
            'bouquet_nv' => $this->format_eur( $data['bouquet_nv'] ?? '' ),
            'rente' => $data['rente'] ?? null,
            'dpe_classe' => (string) ( $data['dpe_classe'] ?? '' ),
            'dpe_value' => (string) ( $data['dpe_value'] ?? '' ),
            'ges_classe' => (string) ( $data['ges_classe'] ?? '' ),
            'ges_value' => (string) ( $data['ges_value'] ?? '' ),
        );

        $annonce->bien->prix_ou_loyer = '';
        if ( is_string( $annonce->bien->loyer ) && $annonce->bien->loyer !== '' ) {
            $annonce->bien->prix_ou_loyer = $annonce->bien->loyer;
        } elseif ( is_string( $annonce->bien->prix ) && $annonce->bien->prix !== '' ) {
            $annonce->bien->prix_ou_loyer = $annonce->bien->prix;
        }

        $loc = trim( $annonce->bien->code_postal . ' ' . $annonce->bien->ville );
        $annonce->bien->localisation = $loc;

        $car = array();
        if ( $annonce->bien->surface !== '' ) {
            $car[] = $annonce->bien->surface . ' m²';
        }
        if ( $annonce->bien->pieces !== '' ) {
            $car[] = $annonce->bien->pieces . ' pièces';
        }
        if ( $annonce->bien->chambres !== '' ) {
            $car[] = $annonce->bien->chambres . ' chambres';
        }
        $annonce->bien->caracteristiques = $car ? implode( ' · ', $car ) : '—';

        $subtitle = array();
        if ( $annonce->bien->nature !== '' ) {
            $subtitle[] = $annonce->bien->nature;
        }
        if ( $annonce->bien->ville !== '' ) {
            $subtitle[] = $annonce->bien->ville;
        }
        if ( $annonce->bien->transaction_type !== '' ) {
            $subtitle[] = $annonce->bien->transaction_type;
        }
        $annonce->bien->subtitle = $subtitle ? implode( ' · ', $subtitle ) : '';

        $ids = array();
        if ( $annonce->reference !== '' ) {
            $ids[] = 'Réf. ' . $annonce->reference;
        }
        if ( $annonce->uuid !== '' ) {
            $ids[] = 'UUID ' . $annonce->uuid;
        }
        if ( $annonce->last_sync !== '' ) {
            $ids[] = 'Sync ' . $annonce->last_sync;
        }
        $annonce->ids = $ids ? implode( ' · ', $ids ) : '';

        $charges = array();
        if ( $annonce->bien->charges_incluses === 'Oui' ) {
            $charges[] = 'Charges incluses';
        } elseif ( $annonce->bien->charges_incluses === 'Non' ) {
            $charges[] = 'Charges non incluses';
        }
        if ( $annonce->bien->montant_charges !== '' ) {
            $charges[] = 'Charges: ' . $annonce->bien->montant_charges;
        }
        $annonce->bien->charges_resume = $charges ? implode( ' · ', $charges ) : '';

        $details = array();
        if ( $annonce->bien->transaction_type !== '' ) {
            $details[] = array( 'Transaction', $annonce->bien->transaction_type );
        }
        if ( $annonce->bien->type_honoraires !== '' ) {
            $details[] = array( 'Type honoraires', $annonce->bien->type_honoraires );
        }
        if ( $annonce->bien->honoraires !== '' ) {
            $details[] = array( 'Honoraires', $annonce->bien->honoraires );
        }
        if ( $annonce->bien->honoraires_pourcentage !== '' ) {
            $details[] = array( 'Honoraires (%)', $annonce->bien->honoraires_pourcentage );
        }
        if ( $annonce->bien->charges_copropriete !== '' ) {
            $details[] = array( 'Charges copropriété', $annonce->bien->charges_copropriete );
        }
        if ( $annonce->bien->frais_acte !== '' ) {
            $details[] = array( 'Frais d\'acte', $annonce->bien->frais_acte );
        }
        if ( $annonce->bien->bouquet !== '' ) {
            $details[] = array( 'Bouquet', $annonce->bien->bouquet );
        }
        if ( $annonce->bien->bouquet_hni !== '' ) {
            $details[] = array( 'Bouquet HNI', $annonce->bien->bouquet_hni );
        }
        if ( $annonce->bien->bouquet_nv !== '' ) {
            $details[] = array( 'Bouquet NV', $annonce->bien->bouquet_nv );
        }

        if ( is_array( $annonce->bien->rente ) ) {
            $m = isset( $annonce->bien->rente['montant'] ) ? $annonce->bien->rente['montant'] : '';
            $p = isset( $annonce->bien->rente['periodicite'] ) ? $annonce->bien->rente['periodicite'] : '';
            if ( $m !== '' ) {
                $details[] = array( 'Rente', number_format( (float) $m, 0, ',', ' ' ) . ' €' . ( $p !== '' ? ' (' . $p . ')' : '' ) );
            }
        }

        if ( $annonce->bien->loyer !== '' ) {
            if ( $annonce->bien->charges_incluses !== '' ) {
                $details[] = array( 'Charges incluses', $annonce->bien->charges_incluses );
            }
            if ( $annonce->bien->montant_charges !== '' ) {
                $details[] = array( 'Montant charges', $annonce->bien->montant_charges );
            }
            if ( $annonce->bien->montant_etat_lieux !== '' ) {
                $details[] = array( 'État des lieux', $annonce->bien->montant_etat_lieux );
            }
            if ( $annonce->bien->meuble !== '' ) {
                $details[] = array( 'Meublé', $annonce->bien->meuble );
            }
            if ( $annonce->bien->montant_depot_garantie !== '' ) {
                $details[] = array( 'Dépôt de garantie', $annonce->bien->montant_depot_garantie );
            }
        }

        $annonce->bien->details = $details;

        $annonce->office = (object) array(
            'raison_sociale' => (string) ( $data['office_name'] ?? '' ),
            'crpcen' => (string) ( $data['office_crpcen'] ?? '' ),
        );

        $annonce->contact = (object) array(
            'nom' => (string) ( $data['contact_name'] ?? '' ),
            'telephone' => (string) ( $data['contact_tel'] ?? '' ),
            'email' => (string) ( $data['contact_email'] ?? '' ),
        );

        return $annonce;
    }

    public function render_block_shortcodes( $block_content, $block ) {
        if ( ! is_string( $block_content ) || $block_content === '' ) {
            return $block_content;
        }

        if ( strpos( $block_content, '[noty_annonce' ) === false && strpos( $block_content, '[noty_annonces' ) === false ) {
            return $block_content;
        }

        return do_shortcode( $block_content );
    }

    private function locate_template( $relative_path ) {
        $path = trailingslashit( NOTY_PLUGIN_DIR ) . 'templates/' . ltrim( (string) $relative_path, '/' );
        if ( file_exists( $path ) ) {
            return $path;
        }

        return '';
    }

    private function render_template( $relative_path, $vars = array() ) {
        $path = $this->locate_template( $relative_path );
        if ( $path === '' ) {
            return '';
        }

        if ( is_array( $vars ) ) {
            extract( $vars, EXTR_SKIP );
        }

        ob_start();
        include $path;
        return ob_get_clean();
    }

    private function get_transaction_slug( $post_id ) {
        $terms = wp_get_post_terms( $post_id, 'noty_transaction' );
        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return '';
        }

        return (string) $terms[0]->slug;
    }

    private function get_template_for_card( $post_id ) {
        return 'card.php';
    }

    private function get_template_for_single( $post_id ) {
        return 'single.php';
    }

    public function render_annonces( $atts ) {
        $atts = shortcode_atts( array(
            'limit' => 12,
            'nature' => '',
            'ville' => '',
            'transaction' => '',
        ), $atts );

        $args = array(
            'post_type'      => 'noty_annonce',
            'posts_per_page' => $atts['limit'],
            'orderby'        => 'date',
            'order'          => 'DESC',
        );

        $tax_query = array();

        if ( ! empty( $atts['nature'] ) ) {
            $tax_query[] = array(
                'taxonomy' => 'noty_nature',
                'field'    => 'slug',
                'terms'    => $atts['nature'],
            );
        }

        if ( ! empty( $atts['ville'] ) ) {
            $tax_query[] = array(
                'taxonomy' => 'noty_ville',
                'field'    => 'slug',
                'terms'    => $atts['ville'],
            );
        }

        if ( ! empty( $atts['transaction'] ) ) {
            $tax_query[] = array(
                'taxonomy' => 'noty_transaction',
                'field'    => 'slug',
                'terms'    => $atts['transaction'],
            );
        }

        if ( ! empty( $tax_query ) ) {
            $args['tax_query'] = $tax_query;
        }

        $query = new WP_Query( $args );
        
        ob_start();
        
        if ( $query->have_posts() ) {
            echo '<div class="up-immo-annonces">';
            echo '<div class="up-immo-annonces__items">';
            while ( $query->have_posts() ) {
                $query->the_post();
                $post_id = get_the_ID();
                $template = $this->get_template_for_card( $post_id );
                $annonce = Noty_Annonce::from_post_id( $post_id );
                if ( ! $annonce instanceof Noty_Annonce ) {
                    continue;
                }
                echo $this->render_template( $template, array( 'post_id' => $post_id, 'annonce' => $annonce, 'bien' => $annonce->bien ) );
            }
            echo '</div>';
            echo '</div>';
            wp_reset_postdata();
        } else {
            echo '<p class="up-immo-annonces__empty">Aucune annonce disponible pour le moment.</p>';
        }

        return ob_get_clean();
    }

    public function render_annonce( $atts ) {
        static $rendering = array();

        $atts = shortcode_atts( array(
            'id' => 0,
            'uuid' => '',
            'template' => 'single',
            'order-in-column' => 'false',
        ), $atts );

        $post_id = (int) $atts['id'];

        // Si UUID fourni, chercher par UUID
        if ( $post_id <= 0 && is_string( $atts['uuid'] ) && $atts['uuid'] !== '' ) {
            $posts = get_posts( array(
                'post_type' => 'noty_annonce',
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key' => 'up_uuid',
                        'value' => $atts['uuid'],
                    ),
                    array(
                        'key' => '_noty_uuid',
                        'value' => $atts['uuid'],
                    ),
                ),
                'posts_per_page' => 1,
            ) );
            if ( ! empty( $posts ) ) {
                $post_id = (int) $posts[0]->ID;
            }
        }

        // Si toujours pas d'ID, essayer de récupérer depuis le contexte de la boucle
        if ( $post_id <= 0 ) {
            // Méthode 1: get_the_ID() (fonctionne dans les query loops standard)
            $post_id = get_the_ID();

            // Méthode 2: depuis get_post() (global post)
            if ( ! $post_id ) {
                $current = get_post();
                if ( $current instanceof WP_Post ) {
                    $post_id = (int) $current->ID;
                }
            }

            // Méthode 3: depuis le post global (fallback)
            if ( ! $post_id && isset( $GLOBALS['post'] ) && is_object( $GLOBALS['post'] ) ) {
                $post_id = (int) $GLOBALS['post']->ID;
            }

            // Méthode 4: depuis wp_query current post
            if ( ! $post_id && isset( $GLOBALS['wp_query'] ) && isset( $GLOBALS['wp_query']->post ) && is_object( $GLOBALS['wp_query']->post ) ) {
                $post_id = (int) $GLOBALS['wp_query']->post->ID;
            }

            // Méthode 5: depuis wp_query->posts[current_post] (utile dans certains rendus de Query Loop)
            if ( ! $post_id && isset( $GLOBALS['wp_query'] ) && $GLOBALS['wp_query'] instanceof WP_Query ) {
                $idx = (int) $GLOBALS['wp_query']->current_post;
                if ( isset( $GLOBALS['wp_query']->posts[ $idx ] ) && $GLOBALS['wp_query']->posts[ $idx ] instanceof WP_Post ) {
                    $post_id = (int) $GLOBALS['wp_query']->posts[ $idx ]->ID;
                }
            }
        }

        if ( ! $post_id ) {
            return '';
        }

        $render_key = (string) (int) $post_id;
        if ( isset( $rendering[ $render_key ] ) ) {
            return '';
        }
        $rendering[ $render_key ] = true;

        $template_type = sanitize_text_field( $atts['template'] );
        if ( $template_type === 'card' ) {
            $template = $this->get_template_for_card( $post_id );
        } else {
            $template = $this->get_template_for_single( $post_id );
        }
        $annonce = Noty_Annonce::from_post_id( $post_id );
        if ( ! $annonce instanceof Noty_Annonce ) {
            unset( $rendering[ $render_key ] );
            return '';
        }

        $order_in_column = sanitize_text_field( $atts['order-in-column'] );
        $order_in_column = in_array( strtolower( $order_in_column ), array( '1', 'true', 'yes', 'on' ), true );
        if ( $order_in_column && $template_type !== 'card' && isset( $annonce->bien ) && is_object( $annonce->bien ) ) {
            $annonce->bien->details = Noty_Annonce::order_items_in_columns( $annonce->bien->details, 2 );
        }
        $html = $this->render_template( $template, array( 'post_id' => $post_id, 'annonce' => $annonce, 'bien' => $annonce->bien ) );

        unset( $rendering[ $render_key ] );
        return $html;
    }
}
