<?php

class Noty_CPT {
    public function __construct() {
        add_action( 'init', array( $this, 'register_post_type' ) );
        add_action( 'init', array( $this, 'register_taxonomies' ) );
        add_action( 'add_meta_boxes', array( $this, 'register_metaboxes' ) );
        add_filter( 'manage_noty_annonce_posts_columns', array( $this, 'add_admin_columns' ) );
        add_action( 'manage_noty_annonce_posts_custom_column', array( $this, 'render_admin_columns' ), 10, 2 );
    }

    public function register_post_type() {
        $labels = array(
            'name'               => 'Annonces Noty',
            'singular_name'      => 'Annonce Noty',
            'menu_name'          => 'Annonces Noty',
            'add_new'            => 'Ajouter une annonce',
            'add_new_item'       => 'Ajouter une nouvelle annonce',
            'edit_item'          => 'Modifier l\'annonce',
            'new_item'           => 'Nouvelle annonce',
            'view_item'          => 'Voir l\'annonce',
            'search_items'       => 'Rechercher des annonces',
            'not_found'          => 'Aucune annonce trouvée',
            'not_found_in_trash' => 'Aucune annonce trouvée dans la corbeille',
        );

        $args = array(
            'labels'              => $labels,
            'public'              => true,
            'has_archive'         => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'query_var'           => true,
            'rewrite'             => array( 'slug' => 'annonce-immo' ),
            'capability_type'     => 'post',
            'hierarchical'        => false,
            'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
            'menu_icon'           => 'dashicons-admin-home',
            'show_in_rest'        => true,
        );

        register_post_type( 'noty_annonce', $args );
    }

    public function register_taxonomies() {
        // Nature du bien (Maison, Appartement, etc.)
        register_taxonomy( 'noty_nature', 'noty_annonce', array(
            'label'        => 'Nature du bien',
            'rewrite'      => array( 'slug' => 'nature-bien' ),
            'hierarchical' => true,
            'show_in_rest' => true,
        ) );

        // Type de transaction (Vente, Location, etc.)
        register_taxonomy( 'noty_transaction', 'noty_annonce', array(
            'label'        => 'Type de transaction',
            'rewrite'      => array( 'slug' => 'type-transaction' ),
            'hierarchical' => true,
            'show_in_rest' => true,
        ) );

        // Ville / Commune
        register_taxonomy( 'noty_ville', 'noty_annonce', array(
            'label'        => 'Ville',
            'rewrite'      => array( 'slug' => 'ville' ),
            'hierarchical' => true,
            'show_in_rest' => true,
        ) );

        // État du bâtiment
        register_taxonomy( 'noty_etat', 'noty_annonce', array(
            'label'        => 'État du bien',
            'rewrite'      => array( 'slug' => 'etat-bien' ),
            'hierarchical' => true,
            'show_in_rest' => true,
        ) );
    }

    public function register_metaboxes() {
        add_meta_box(
            'noty_annonce_location',
            'Location / Charges',
            array( $this, 'render_location_metabox' ),
            'noty_annonce',
            'normal',
            'default'
        );

        add_meta_box(
            'noty_annonce_photos',
            'Photos',
            array( $this, 'render_photos_metabox' ),
            'noty_annonce',
            'side',
            'default'
        );

        add_meta_box(
            'noty_annonce_raw',
            'Données Noty (complet)',
            array( $this, 'render_raw_metabox' ),
            'noty_annonce',
            'normal',
            'low'
        );
    }

    public function render_location_metabox( $post ) {
        $fields = array(
            'Type de transaction'     => array( 'up_transaction_type', '_noty_transaction_type' ),
            'Loyer'                   => array( 'up_loyer', '_noty_loyer' ),
            'Périodicité du loyer'    => array( 'up_loyer_periodicite', '_noty_loyer_periodicite' ),
            'Charges incluses'        => array( 'up_charges_incluses', '_noty_charges_incluses' ),
            'Montant des charges'     => array( 'up_montant_charges', '_noty_montant_charges' ),
            'Montant état des lieux'  => array( 'up_montant_etat_lieux', '_noty_montant_etat_lieux' ),
            'Meublé'                  => array( 'up_meuble', '_noty_meuble' ),
            'Dépôt de garantie'       => array( 'up_montant_depot_garantie', '_noty_montant_depot_garantie' ),
        );

        echo '<table class="widefat striped" style="margin-top: 8px;">';
        echo '<tbody>';

        foreach ( $fields as $label => $meta_keys ) {
            $value = '';
            if ( is_array( $meta_keys ) ) {
                foreach ( $meta_keys as $k ) {
                    $tmp = get_post_meta( $post->ID, $k, true );
                    if ( $tmp !== '' && $tmp !== null ) {
                        $value = $tmp;
                        break;
                    }
                }
            } else {
                $value = get_post_meta( $post->ID, $meta_keys, true );
            }

            if ( $value === '1' ) {
                $display = 'Oui';
            } elseif ( $value === '0' ) {
                $display = 'Non';
            } elseif ( $value === '' || $value === null ) {
                $display = '—';
            } else {
                $display = (string) $value;
            }

            echo '<tr>';
            echo '<th style="width: 220px;">' . esc_html( $label ) . '</th>';
            echo '<td>' . esc_html( $display ) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
    }

    public function render_photos_metabox( $post ) {
        $photo_ids = get_post_meta( $post->ID, 'up_photo_ids', true );
        if ( ! is_array( $photo_ids ) || empty( $photo_ids ) ) {
            $photo_ids = get_post_meta( $post->ID, '_noty_photo_ids', true );
        }
        if ( ! is_array( $photo_ids ) || empty( $photo_ids ) ) {
            echo '<p>Aucune photo.</p>';
            return;
        }

        echo '<div style="display:flex;flex-wrap:wrap;gap:6px;">';
        foreach ( $photo_ids as $attachment_id ) {
            $attachment_id = (int) $attachment_id;
            if ( $attachment_id <= 0 ) {
                continue;
            }

            $thumb = wp_get_attachment_image( $attachment_id, array( 80, 80 ), true, array( 'style' => 'width:80px;height:80px;object-fit:cover;border:1px solid #ddd;' ) );
            if ( $thumb ) {
                echo $thumb;
            }
        }
        echo '</div>';
    }

    public function render_raw_metabox( $post ) {
        $raw = get_post_meta( $post->ID, 'up_raw', true );
        if ( $raw === '' || $raw === null ) {
            $raw = get_post_meta( $post->ID, '_noty_raw', true );
        }
        if ( $raw === '' || $raw === null ) {
            echo '<p>Aucune donnée brute.</p>';
            return;
        }

        $data = json_decode( $raw, true );
        if ( ! is_array( $data ) ) {
            echo '<p>Donnée brute invalide.</p>';
            return;
        }

        $flat = $this->flatten_array( $data );
        if ( empty( $flat ) ) {
            echo '<p>Aucune donnée.</p>';
            return;
        }

        echo '<table class="widefat striped" style="margin-top: 8px;">';
        echo '<tbody>';
        foreach ( $flat as $key => $value ) {
            $meta_key = $this->path_to_meta_key( $key );
            echo '<tr>';
            echo '<th style="width: 320px;"><code>' . esc_html( $key ) . '</code></th>';
            echo '<td style="width: 320px;"><code>' . esc_html( $meta_key ) . '</code></td>';
            echo '<td>' . esc_html( $this->format_display_value( $value ) ) . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    }

    private function path_to_meta_key( $path ) {
        $meta = preg_replace( '/[^a-zA-Z0-9_\-\[\]\.]/', '', (string) $path );
        $meta = str_replace( array( '.', '[', ']' ), array( '__', '_', '' ), (string) $meta );
        return 'up_' . strtolower( $meta );
    }

    private function flatten_array( $data, $prefix = '' ) {
        $result = array();

        if ( is_array( $data ) ) {
            $is_list = array_keys( $data ) === range( 0, count( $data ) - 1 );
            foreach ( $data as $k => $v ) {
                $key = $is_list ? '[' . $k . ']' : (string) $k;
                $new_prefix = $prefix === '' ? $key : $prefix . '.' . $key;

                if ( is_array( $v ) ) {
                    $nested = $this->flatten_array( $v, $new_prefix );
                    $result = array_merge( $result, $nested );
                } else {
                    $result[ $new_prefix ] = $v;
                }
            }

            return $result;
        }

        if ( $prefix !== '' ) {
            $result[ $prefix ] = $data;
        }

        return $result;
    }

    private function format_display_value( $value ) {
        if ( $value === null ) {
            return '—';
        }

        if ( $value === true || $value === '1' ) {
            return 'Oui';
        }

        if ( $value === false || $value === '0' ) {
            return 'Non';
        }

        if ( is_string( $value ) && $value === '' ) {
            return '—';
        }

        return (string) $value;
    }

    public function add_admin_columns( $columns ) {
        $new = array();

        if ( isset( $columns['cb'] ) ) {
            $new['cb'] = $columns['cb'];
        }

        $new['noty_thumb'] = 'Image';

        if ( isset( $columns['title'] ) ) {
            $new['title'] = $columns['title'];
        }

        $new['noty_nature'] = 'Nature';
        $new['noty_transaction'] = 'Transaction';
        $new['noty_ville'] = 'Ville';
        $new['noty_etat'] = 'État';

        foreach ( $columns as $key => $label ) {
            if ( isset( $new[ $key ] ) ) {
                continue;
            }

            $new[ $key ] = $label;
        }

        return $new;
    }

    public function render_admin_columns( $column, $post_id ) {
        if ( $column === 'noty_thumb' ) {
            if ( has_post_thumbnail( $post_id ) ) {
                echo get_the_post_thumbnail( $post_id, array( 60, 60 ), array( 'style' => 'width:60px;height:60px;object-fit:cover;' ) );
            } else {
                echo '—';
            }
            return;
        }

        $tax_map = array(
            'noty_nature' => 'noty_nature',
            'noty_transaction' => 'noty_transaction',
            'noty_ville' => 'noty_ville',
            'noty_etat' => 'noty_etat',
        );

        if ( isset( $tax_map[ $column ] ) ) {
            $terms = get_the_terms( $post_id, $tax_map[ $column ] );
            if ( is_wp_error( $terms ) || empty( $terms ) ) {
                echo '—';
                return;
            }

            $names = wp_list_pluck( $terms, 'name' );
            echo esc_html( implode( ', ', $names ) );
        }
    }
}
