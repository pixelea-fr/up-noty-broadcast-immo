<?php

class Noty_Sync {
    private $api;

    public function __construct() {
        $this->api = new Noty_API();
        add_action( 'noty_sync_annonces_event', array( $this, 'sync_annonces' ) );
    }

    public function sync_annonces() {
        $data = $this->api->get_annonces();
        if ( is_wp_error( $data ) ) {
            error_log( 'Noty Sync Error: ' . $data->get_error_message() );
            return;
        }

        $this->maybe_dump_json( $data, 'annonces' );

        $annonces = isset( $data['results'] ) ? $data['results'] : [];
        $imported_uuids = array();
        
        foreach ( $annonces as $annonce ) {
            $post_id = $this->process_annonce( $annonce );
            if ( $post_id && isset( $annonce['uuid'] ) ) {
                $imported_uuids[] = $annonce['uuid'];
            }
        }
        
        $this->handle_missing_annonces( $imported_uuids );
    }

    private function maybe_dump_json( $data, $prefix ) {
        if ( get_option( 'noty_debug_dump_json' ) !== '1' ) {
            return;
        }

        $uploads = wp_upload_dir();
        if ( empty( $uploads['basedir'] ) ) {
            return;
        }

        $dir = trailingslashit( $uploads['basedir'] ) . 'noty-debug';
        if ( ! file_exists( $dir ) ) {
            wp_mkdir_p( $dir );
        }

        $filename = sprintf(
            '%s-%s.json',
            sanitize_file_name( (string) $prefix ),
            gmdate( 'Ymd-His' )
        );

        $path = trailingslashit( $dir ) . $filename;
        $json = wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
        if ( is_string( $json ) ) {
            file_put_contents( $path, $json );
        }
    }

    private function process_annonce( $annonce ) {
        $uuid = $annonce['uuid'];

        $this->update_discovered_meta_paths( $annonce );
        
        $existing_posts = get_posts( array(
            'post_type'  => 'noty_annonce',
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key'   => 'up_uuid',
                    'value' => $uuid,
                ),
                array(
                    'key'   => '_noty_uuid',
                    'value' => $uuid,
                ),
            ),
            'posts_per_page' => 1,
        ) );

        $post_data = array(
            'post_title'   => $this->generate_title( $annonce ),
            'post_content' => $annonce['description'] ?? '',
            'post_status'  => 'publish',
            'post_type'    => 'noty_annonce',
        );

        if ( ! empty( $existing_posts ) ) {
            $post_id = $existing_posts[0]->ID;
            $post_data['ID'] = $post_id;
            wp_update_post( $post_data );
        } else {
            $post_id = wp_insert_post( $post_data );
        }

        if ( is_wp_error( $post_id ) ) return false;

        // 1. Enregistrement des Taxonomies
        $this->update_taxonomies( $post_id, $annonce );

        // 2. Enregistrement des Métadonnées (Meta)
        $this->update_metas( $post_id, $annonce );

        $this->update_configured_metas( $post_id, $annonce );

        // 3. Synchronisation des Photos
        $this->sync_photos( $post_id, $annonce );

        return $post_id;
    }

    private function update_discovered_meta_paths( $annonce ) {
        $paths = $this->flatten_paths( $annonce );
        if ( empty( $paths ) ) {
            return;
        }

        $existing = get_option( 'noty_discovered_meta_paths', array() );
        if ( ! is_array( $existing ) ) {
            $existing = array();
        }

        $merged = array_values( array_unique( array_merge( $existing, $paths ) ) );
        sort( $merged );

        update_option( 'noty_discovered_meta_paths', $merged, false );
    }

    private function flatten_paths( $data, $prefix = '' ) {
        $result = array();

        if ( is_array( $data ) ) {
            $is_list = array_keys( $data ) === range( 0, count( $data ) - 1 );
            foreach ( $data as $k => $v ) {
                $key = $is_list ? '[' . $k . ']' : (string) $k;
                $new_prefix = $prefix === '' ? $key : $prefix . '.' . $key;

                if ( is_array( $v ) ) {
                    $result = array_merge( $result, $this->flatten_paths( $v, $new_prefix ) );
                } else {
                    $result[] = $new_prefix;
                }
            }

            return $result;
        }

        if ( $prefix !== '' ) {
            $result[] = $prefix;
        }

        return $result;
    }

    private function update_configured_metas( $post_id, $annonce ) {
        $selected = get_option( 'noty_selected_meta_paths', array() );
        if ( ! is_array( $selected ) || empty( $selected ) ) {
            return;
        }

        foreach ( $selected as $path ) {
            if ( ! is_string( $path ) || $path === '' ) {
                continue;
            }

            $value = $this->get_value_by_path( $annonce, $path );
            $meta_key = $this->path_to_meta_key( $path );
            $this->update_meta_value( $post_id, $meta_key, $value );

            $legacy_key = $this->legacy_path_to_meta_key( $path );
            if ( is_string( $legacy_key ) && $legacy_key !== '' ) {
                $legacy_value = get_post_meta( $post_id, $legacy_key, true );
                $current_value = get_post_meta( $post_id, $meta_key, true );
                if ( ( $current_value === '' || $current_value === null ) && ( $legacy_value !== '' && $legacy_value !== null ) ) {
                    update_post_meta( $post_id, $meta_key, $legacy_value );
                }
            }
        }
    }

    private function path_to_meta_key( $path ) {
        $meta = preg_replace( '/[^a-zA-Z0-9_\-\[\]\.]/', '', $path );
        $meta = str_replace( array( '.', '[', ']' ), array( '__', '_', '' ), (string) $meta );
        return 'up_' . strtolower( $meta );
    }

    private function legacy_path_to_meta_key( $path ) {
        $meta = preg_replace( '/[^a-zA-Z0-9_\-\[\]\.]/', '', $path );
        $meta = str_replace( array( '.', '[', ']' ), array( '__', '_', '' ), (string) $meta );
        return '_noty_custom_' . strtolower( $meta );
    }

    private function get_value_by_path( $data, $path ) {
        $tokens = $this->tokenize_path( $path );
        $current = $data;

        foreach ( $tokens as $token ) {
            if ( is_array( $current ) && array_key_exists( $token, $current ) ) {
                $current = $current[ $token ];
                continue;
            }

            if ( is_array( $current ) && is_numeric( $token ) ) {
                $idx = (int) $token;
                if ( array_key_exists( $idx, $current ) ) {
                    $current = $current[ $idx ];
                    continue;
                }
            }

            return null;
        }

        return $current;
    }

    private function tokenize_path( $path ) {
        $tokens = array();
        $parts = explode( '.', (string) $path );
        foreach ( $parts as $part ) {
            if ( $part === '' ) {
                continue;
            }

            if ( preg_match_all( '/([^\[]+)|\[([^\]]+)\]/', $part, $matches, PREG_SET_ORDER ) ) {
                foreach ( $matches as $m ) {
                    if ( isset( $m[1] ) && $m[1] !== '' ) {
                        $tokens[] = $m[1];
                    } elseif ( isset( $m[2] ) && $m[2] !== '' ) {
                        $tokens[] = $m[2];
                    }
                }
            } else {
                $tokens[] = $part;
            }
        }

        return $tokens;
    }

    private function update_taxonomies( $post_id, $annonce ) {
        // Nature
        if ( ! empty( $annonce['bien']['nature'] ) ) {
            wp_set_object_terms( $post_id, (string) $annonce['bien']['nature'], 'noty_nature' );
        }

        // Transaction
        if ( isset( $annonce['transaction'] ) ) {
            $transaction_type = '';
            if ( is_array( $annonce['transaction'] ) && ! empty( $annonce['transaction']['type'] ) ) {
                $transaction_type = (string) $annonce['transaction']['type'];
            } elseif ( is_string( $annonce['transaction'] ) ) {
                $transaction_type = $annonce['transaction'];
            }

            if ( $transaction_type !== '' ) {
                wp_set_object_terms( $post_id, $transaction_type, 'noty_transaction' );
            }
        }

        // Ville
        if ( ! empty( $annonce['bien']['commune']['libelle'] ) ) {
            wp_set_object_terms( $post_id, (string) $annonce['bien']['commune']['libelle'], 'noty_ville' );
        }

        // État
        if ( ! empty( $annonce['bien']['etat'] ) ) {
            wp_set_object_terms( $post_id, (string) $annonce['bien']['etat'], 'noty_etat' );
        }
    }

    private function update_metas( $post_id, $annonce ) {
        $this->update_meta_value( $post_id, 'up_raw', $annonce );

        update_post_meta( $post_id, 'up_uuid', $annonce['uuid'] );
        update_post_meta( $post_id, 'up_reference', $annonce['reference'] ?? '' );

        $this->update_meta_value( $post_id, 'up_transaction', $annonce['transaction'] ?? '' );
        $this->update_meta_value( $post_id, 'up_loyer', $annonce['loyer'] ?? '' );
        $this->update_meta_value( $post_id, 'up_loyer_periodicite', $annonce['loyer_periodicite'] ?? '' );
        $this->update_meta_value( $post_id, 'up_charges_incluses', $annonce['charges_incluses'] ?? '' );
        $this->update_meta_value( $post_id, 'up_montant_charges', $annonce['montant_charges'] ?? '' );
        $this->update_meta_value( $post_id, 'up_montant_etat_lieux', $annonce['montant_etat_lieux'] ?? '' );
        $this->update_meta_value( $post_id, 'up_meuble', $annonce['meuble'] ?? '' );
        $this->update_meta_value( $post_id, 'up_montant_depot_garantie', $annonce['montant_depot_garantie'] ?? '' );
        
        $bien = $annonce['bien'] ?? [];
        $this->update_meta_value( $post_id, 'up_nature', $bien['nature'] ?? '' );
        $this->update_meta_value( $post_id, 'up_surface', $bien['surface'] ?? '' );
        $this->update_meta_value( $post_id, 'up_surface_habitable', $bien['surface_habitable'] ?? '' );
        $this->update_meta_value( $post_id, 'up_nb_pieces', $bien['nb_pieces'] ?? '' );
        $this->update_meta_value( $post_id, 'up_nb_chambres', $bien['nb_chambres'] ?? '' );
        $this->update_meta_value( $post_id, 'up_accessibilite', $bien['accessibilite'] ?? [] );
        $this->update_meta_value( $post_id, 'up_situation_locative', $bien['situation_locative'] ?? [] );
        $this->update_meta_value( $post_id, 'up_emplacement', $bien['emplacement'] ?? [] );
        
        $commune = $bien['commune'] ?? [];
        $this->update_meta_value( $post_id, 'up_ville', $commune['libelle'] ?? '' );
        $this->update_meta_value( $post_id, 'up_code_postal', $commune['code_postal'] ?? '' );
        $this->update_meta_value( $post_id, 'up_code_insee', $commune['code_insee'] ?? '' );
        
        $transaction = $annonce['transaction'] ?? [];
        if ( is_array( $transaction ) ) {
            $this->update_meta_value( $post_id, 'up_prix', $transaction['prix'] ?? '' );
            $this->update_meta_value( $post_id, 'up_transaction_type', $transaction['type'] ?? '' );
        } else {
            $this->update_meta_value( $post_id, 'up_prix', $annonce['prix'] ?? '' );
            $this->update_meta_value( $post_id, 'up_transaction_type', (string) $transaction );
        }
        
        // DPE / GES
        $perf = $bien['performance_energetique'] ?? [];
        $this->update_meta_value( $post_id, 'up_dpe_classe', $perf['dpe_classe'] ?? '' );
        $this->update_meta_value( $post_id, 'up_dpe_value', $perf['dpe_value'] ?? '' );
        $this->update_meta_value( $post_id, 'up_ges_classe', $perf['ges_classe'] ?? '' );
        $this->update_meta_value( $post_id, 'up_ges_value', $perf['ges_value'] ?? '' );

        update_post_meta( $post_id, 'up_last_sync', current_time( 'mysql' ) );
    }

    private function update_meta_value( $post_id, $meta_key, $value ) {
        if ( is_array( $value ) || is_object( $value ) ) {
            $encoded = wp_json_encode( $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
            update_post_meta( $post_id, $meta_key, $encoded === false ? '' : $encoded );
            return;
        }

        if ( is_bool( $value ) ) {
            update_post_meta( $post_id, $meta_key, $value ? '1' : '0' );
            return;
        }

        update_post_meta( $post_id, $meta_key, $value );
    }

    private function sync_photos( $post_id, $annonce ) {
        $photos = $annonce['bien']['photos'] ?? [];
        if ( empty( $photos ) ) return;

        usort( $photos, function( $a, $b ) {
            return ( $a['rank'] ?? 0 ) <=> ( $b['rank'] ?? 0 );
        } );

        $token = get_option( 'noty_api_token', '' );

        $attachment_ids = array();
        
        foreach ( $photos as $index => $photo ) {
            $photo_uuid = $photo['uuid'];
            
            $existing_media = get_posts( array(
                'post_type'   => 'attachment',
                'meta_query'  => array(
                    'relation' => 'OR',
                    array(
                        'key'   => 'up_photo_uuid',
                        'value' => $photo_uuid,
                    ),
                    array(
                        'key'   => '_noty_photo_uuid',
                        'value' => $photo_uuid,
                    ),
                ),
                'posts_per_page' => 1,
            ) );

            if ( ! empty( $existing_media ) ) {
                $attachment_id = $existing_media[0]->ID;
            } else {
                // Utiliser l'URL signée (href) si disponible, sinon construire l'URL API
                $photo_url = ! empty( $photo['href'] ) ? $photo['href'] : $this->api->get_photo_url( $annonce['uuid'], $photo_uuid );
                
                $args = array(
                    'headers' => array( 'AUTH-TOKEN' => $token ),
                    'timeout' => 60,
                    'sslverify' => false // Parfois nécessaire pour les environnements de test
                );
                
                $response = wp_remote_get( $photo_url, $args );
                if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
                    error_log( "Noty Photo Download Error: " . ( is_wp_error( $response ) ? $response->get_error_message() : "HTTP " . wp_remote_retrieve_response_code( $response ) ) );
                    continue;
                }
                
                $image_contents = wp_remote_retrieve_body( $response );

                $content_type = wp_remote_retrieve_header( $response, 'content-type' );
                $content_type = is_string( $content_type ) ? strtolower( trim( explode( ';', $content_type )[0] ) ) : '';
                $ext = 'jpg';
                if ( $content_type === 'image/png' ) {
                    $ext = 'png';
                } elseif ( $content_type === 'image/webp' ) {
                    $ext = 'webp';
                } elseif ( $content_type === 'image/gif' ) {
                    $ext = 'gif';
                }

                $filename = $photo_uuid . '.' . $ext;
                $upload = wp_upload_bits( $filename, null, $image_contents );
                
                if ( ! $upload['error'] ) {
                    $wp_filetype = wp_check_filetype( $upload['file'], null );
                    $attachment = array(
                        'post_mime_type' => $wp_filetype['type'],
                        'post_title'     => sanitize_file_name( $filename ),
                        'post_content'   => '',
                        'post_status'    => 'inherit'
                    );
                    
                    $attachment_id = wp_insert_attachment( $attachment, $upload['file'], $post_id );
                    if ( ! is_wp_error( $attachment_id ) ) {
                        require_once( ABSPATH . 'wp-admin/includes/image.php' );
                        $attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
                        wp_update_attachment_metadata( $attachment_id, $attachment_data );
                        update_post_meta( $attachment_id, 'up_photo_uuid', $photo_uuid );
                    }
                }
            }

            if ( isset( $attachment_id ) && ! is_wp_error( $attachment_id ) ) {
                $attachment_ids[] = (int) $attachment_id;

                if ( $index === 0 ) {
                    set_post_thumbnail( $post_id, $attachment_id );
                }
            }
        }

        if ( ! empty( $attachment_ids ) ) {
            update_post_meta( $post_id, 'up_photo_ids', array_values( array_unique( $attachment_ids ) ) );
        }
    }

    private function handle_missing_annonces( $imported_uuids ) {
        $missing_action = get_option( 'noty_missing_action', 'keep' );
        
        if ( $missing_action === 'keep' ) {
            return;
        }
        
        $all_annonces = get_posts( array(
            'post_type'      => 'noty_annonce',
            'posts_per_page' => -1,
            'post_status'    => array( 'publish', 'draft' ),
            'fields'         => 'ids',
        ) );
        
        foreach ( $all_annonces as $post_id ) {
            $uuid = get_post_meta( $post_id, 'up_uuid', true );
            if ( ! $uuid ) {
                $uuid = get_post_meta( $post_id, '_noty_uuid', true );
            }
            
            if ( $uuid && ! in_array( $uuid, $imported_uuids, true ) ) {
                if ( $missing_action === 'draft' ) {
                    wp_update_post( array(
                        'ID'          => $post_id,
                        'post_status' => 'draft',
                    ) );
                } elseif ( $missing_action === 'delete' ) {
                    $this->delete_annonce_with_photos( $post_id );
                }
            }
        }
    }

    private function delete_annonce_with_photos( $post_id ) {
        $delete_photos = get_option( 'noty_delete_photos' ) === '1';
        
        if ( $delete_photos ) {
            $photo_ids = get_post_meta( $post_id, 'up_photo_ids', true );
            
            if ( is_array( $photo_ids ) && ! empty( $photo_ids ) ) {
                foreach ( $photo_ids as $attachment_id ) {
                    wp_delete_attachment( $attachment_id, true );
                }
            }
            
            $thumbnail_id = get_post_thumbnail_id( $post_id );
            if ( $thumbnail_id ) {
                wp_delete_attachment( $thumbnail_id, true );
            }
        }
        
        wp_delete_post( $post_id, true );
    }

    private function generate_title( $annonce ) {
        $nature = $annonce['bien']['nature'] ?? 'Bien immobilier';
        $ville = $annonce['bien']['commune']['libelle'] ?? '';
        return ucfirst( $nature ) . ( $ville ? ' à ' . $ville : '' ) . ' - ' . ( $annonce['reference'] ?? $annonce['uuid'] );
    }
}
