<?php

class Noty_Admin {
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=noty_annonce',
            'Configuration Noty',
            'Configuration',
            'manage_options',
            'noty-settings',
            array( $this, 'settings_page' )
        );

        add_submenu_page(
            'edit.php?post_type=noty_annonce',
            'Mode d\'emploi',
            'Mode d\'emploi',
            'manage_options',
            'noty-shortcodes',
            array( $this, 'shortcodes_page' )
        );

        add_submenu_page(
            'edit.php?post_type=noty_annonce',
            'Import JSON',
            'Import JSON',
            'manage_options',
            'noty-import-json',
            array( $this, 'import_json_page' )
        );
    }

    public function register_settings() {
        register_setting( 'noty_settings_group', 'noty_api_token' );
        register_setting( 'noty_settings_group', 'noty_api_url' );
        register_setting( 'noty_settings_group', 'noty_debug_dump_json' );
        register_setting( 'noty_settings_group', 'noty_price_display_mode', array( $this, 'sanitize_price_display_mode' ) );
        register_setting( 'noty_settings_group', 'noty_price_display_note', array( $this, 'sanitize_price_display_note' ) );
        register_setting( 'noty_settings_group', 'noty_selected_meta_paths' );
        register_setting( 'noty_settings_group', 'noty_missing_action' );
        register_setting( 'noty_settings_group', 'noty_delete_photos' );
        register_setting( 'noty_settings_group', 'noty_duplicate_detection_mode', array( $this, 'sanitize_duplicate_detection_mode' ) );
        register_setting( 'noty_settings_group', 'noty_delete_duplicates' );
        
        register_setting( 'noty_settings_group', 'noty_fields_location_details', array( $this, 'sanitize_fields_config' ) );
        register_setting( 'noty_settings_group', 'noty_fields_location_resume', array( $this, 'sanitize_fields_config' ) );
        register_setting( 'noty_settings_group', 'noty_fields_vente_traditionnelle_details', array( $this, 'sanitize_fields_config' ) );
        register_setting( 'noty_settings_group', 'noty_fields_vente_traditionnelle_resume', array( $this, 'sanitize_fields_config' ) );
        register_setting( 'noty_settings_group', 'noty_fields_vente_viager_details', array( $this, 'sanitize_fields_config' ) );
        register_setting( 'noty_settings_group', 'noty_fields_vente_viager_resume', array( $this, 'sanitize_fields_config' ) );
    }

    public function sanitize_fields_config( $value ) {
        if ( is_string( $value ) ) {
            $value = array_map( 'trim', explode( ',', $value ) );
        }
        
        if ( ! is_array( $value ) ) {
            return array();
        }
        
        return array_values( array_filter( array_map( 'sanitize_text_field', $value ) ) );
    }

    private function render_fields_config_section() {
        $transaction_types = array(
            'location' => 'Location',
            'vente_traditionnelle' => 'Vente traditionnelle',
            'vente_viager' => 'Vente viager',
        );

        foreach ( $transaction_types as $type_key => $type_label ) {
            $this->render_transaction_fields_config( $type_key, $type_label );
        }
    }

    private function render_transaction_fields_config( $type_key, $type_label ) {
        $available_fields = Noty_Fields_Config::get_fields_for_type( $type_key );
        
        $details_option_name = 'noty_fields_' . $type_key . '_details';
        $resume_option_name = 'noty_fields_' . $type_key . '_resume';
        
        $details_config = get_option( $details_option_name );
        $resume_config = get_option( $resume_option_name );
        
        if ( ! is_array( $details_config ) || empty( $details_config ) ) {
            $details_config = Noty_Fields_Config::get_default_config( $type_key, 'details' );
        }
        if ( ! is_array( $resume_config ) || empty( $resume_config ) ) {
            $resume_config = Noty_Fields_Config::get_default_config( $type_key, 'resume' );
        }
        ?>
        <div style="border: 1px solid #ccd0d4; padding: 20px; margin-bottom: 20px; background: #fff;">
            <h3><?php echo esc_html( $type_label ); ?></h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <!-- Détails complets -->
                <div>
                    <h4>Détails complets</h4>
                    <p class="description">Glissez-déposez les champs depuis "Disponibles" vers "Sélectionnés" et ordonnez-les.</p>
                    
                    <div style="margin-bottom: 15px;">
                        <strong>Champs disponibles</strong>
                        <div id="available-details-<?php echo esc_attr( $type_key ); ?>" class="noty-fields-available" style="border: 1px solid #ddd; padding: 10px; background: #f9f9f9; min-height: 100px; max-height: 200px; overflow-y: auto;">
                            <?php foreach ( $available_fields as $field_key => $field_data ) : ?>
                                <?php if ( ! in_array( $field_key, $details_config, true ) ) : ?>
                                    <div class="noty-field-item" data-field="<?php echo esc_attr( $field_key ); ?>" draggable="true" style="padding: 8px; margin: 4px 0; background: #fff; border: 1px solid #ddd; cursor: move; border-radius: 3px;">
                                        <span class="dashicons dashicons-menu" style="color: #999;"></span>
                                        <?php echo esc_html( $field_data['label'] ); ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div>
                        <strong>Champs sélectionnés (dans l'ordre d'affichage)</strong>
                        <div id="selected-details-<?php echo esc_attr( $type_key ); ?>" class="noty-fields-selected" style="border: 1px solid #ddd; padding: 10px; background: #e8f5e9; min-height: 100px; max-height: 300px; overflow-y: auto;">
                            <?php foreach ( $details_config as $field_key ) : ?>
                                <?php if ( isset( $available_fields[ $field_key ] ) ) : ?>
                                    <div class="noty-field-item" data-field="<?php echo esc_attr( $field_key ); ?>" draggable="true" style="padding: 8px; margin: 4px 0; background: #fff; border: 1px solid #4caf50; cursor: move; border-radius: 3px;">
                                        <span class="dashicons dashicons-menu" style="color: #4caf50;"></span>
                                        <?php echo esc_html( $available_fields[ $field_key ]['label'] ); ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <input type="hidden" id="input-details-<?php echo esc_attr( $type_key ); ?>" name="<?php echo esc_attr( $details_option_name ); ?>" value="<?php echo esc_attr( implode( ',', $details_config ) ); ?>" />
                </div>
                
                <!-- Détails résumés -->
                <div>
                    <h4>Détails résumés</h4>
                    <p class="description">Glissez-déposez les champs depuis "Disponibles" vers "Sélectionnés" et ordonnez-les.</p>
                    
                    <div style="margin-bottom: 15px;">
                        <strong>Champs disponibles</strong>
                        <div id="available-resume-<?php echo esc_attr( $type_key ); ?>" class="noty-fields-available" style="border: 1px solid #ddd; padding: 10px; background: #f9f9f9; min-height: 100px; max-height: 200px; overflow-y: auto;">
                            <?php foreach ( $available_fields as $field_key => $field_data ) : ?>
                                <?php if ( ! in_array( $field_key, $resume_config, true ) ) : ?>
                                    <div class="noty-field-item" data-field="<?php echo esc_attr( $field_key ); ?>" draggable="true" style="padding: 8px; margin: 4px 0; background: #fff; border: 1px solid #ddd; cursor: move; border-radius: 3px;">
                                        <span class="dashicons dashicons-menu" style="color: #999;"></span>
                                        <?php echo esc_html( $field_data['label'] ); ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div>
                        <strong>Champs sélectionnés (dans l'ordre d'affichage)</strong>
                        <div id="selected-resume-<?php echo esc_attr( $type_key ); ?>" class="noty-fields-selected" style="border: 1px solid #ddd; padding: 10px; background: #e3f2fd; min-height: 100px; max-height: 300px; overflow-y: auto;">
                            <?php foreach ( $resume_config as $field_key ) : ?>
                                <?php if ( isset( $available_fields[ $field_key ] ) ) : ?>
                                    <div class="noty-field-item" data-field="<?php echo esc_attr( $field_key ); ?>" draggable="true" style="padding: 8px; margin: 4px 0; background: #fff; border: 1px solid #2196f3; cursor: move; border-radius: 3px;">
                                        <span class="dashicons dashicons-menu" style="color: #2196f3;"></span>
                                        <?php echo esc_html( $available_fields[ $field_key ]['label'] ); ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <input type="hidden" id="input-resume-<?php echo esc_attr( $type_key ); ?>" name="<?php echo esc_attr( $resume_option_name ); ?>" value="<?php echo esc_attr( implode( ',', $resume_config ) ); ?>" />
                </div>
            </div>
        </div>
        <?php
    }

    public function sanitize_duplicate_detection_mode( $value ) {
        $value = is_string( $value ) ? strtolower( trim( $value ) ) : 'uuid';
        return in_array( $value, array( 'uuid', 'reference', 'uuid_reference' ), true ) ? $value : 'uuid';
    }

    public function sanitize_price_display_mode( $value ) {
        $value = is_string( $value ) ? strtolower( trim( $value ) ) : 'prix';

        return in_array( $value, array( 'prix', 'prix_hni' ), true ) ? $value : 'prix';
    }

    public function sanitize_price_display_note( $value ) {
        return is_string( $value ) ? sanitize_text_field( $value ) : '';
    }

    public function settings_page() {
        // Gérer la synchronisation manuelle
        if ( isset( $_POST['noty_sync_now'] ) ) {
            check_admin_referer( 'noty_sync_action', 'noty_sync_nonce' );
            $sync = new Noty_Sync();
            $sync->sync_annonces();
            echo '<div class="updated"><p>Synchronisation terminée !</p></div>';
        }

        ?>
        <div class="wrap">
            <h1>Configuration Noty Broadcast</h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'noty_settings_group' ); ?>
                <?php do_settings_sections( 'noty_settings_group' ); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Jeton d'accès (AUTH-TOKEN)</th>
                        <td><input type="text" name="noty_api_token" value="<?php echo esc_attr( get_option( 'noty_api_token' ) ); ?>" class="regular-text" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">URL de l'API</th>
                        <td>
                            <input type="text" name="noty_api_url" value="<?php echo esc_attr( get_option( 'noty_api_url', 'https://api.broadcast.test.noty.fr' ) ); ?>" class="regular-text" />
                            <p class="description">Test : https://api.broadcast.test.noty.fr<br>Production : https://api.broadcast.noty.fr</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Debug : sauvegarde JSON</th>
                        <td>
                            <label>
                                <input type="checkbox" name="noty_debug_dump_json" value="1" <?php checked( get_option( 'noty_debug_dump_json' ), '1' ); ?> />
                                Activer la sauvegarde des réponses JSON de l'API dans le dossier uploads
                            </label>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Prix affiché</th>
                        <td>
                            <?php $price_display_mode = get_option( 'noty_price_display_mode', 'prix' ); ?>
                            <label style="display:block;margin-bottom:8px;">
                                <input type="radio" name="noty_price_display_mode" value="prix" <?php checked( $price_display_mode, 'prix' ); ?> />
                                <strong>Prix actuel</strong> - Affiche le prix affiché actuellement
                            </label>
                            <label style="display:block;margin-bottom:8px;">
                                <input type="radio" name="noty_price_display_mode" value="prix_hni" <?php checked( $price_display_mode, 'prix_hni' ); ?> />
                                <strong>Prix HNI</strong> - Affiche le prix avec les frais si disponible
                            </label>
                            <p class="description">Ce réglage s'applique aux cards et à la fiche single. Les locations continuent d'afficher le loyer.</p>
                            <div style="margin-top:12px;">
                                <label for="noty_price_display_note" style="display:block;margin-bottom:6px;">
                                    Petit message à côté du prix (optionnel)
                                </label>
                                <input type="text" id="noty_price_display_note" name="noty_price_display_note" value="<?php echo esc_attr( get_option( 'noty_price_display_note', '' ) ); ?>" class="regular-text" placeholder="HNI" />
                                <p class="description">Exemple : HNI. Ce texte sera affiché à côté du prix dans les cards et la fiche single.</p>
                            </div>
                        </td>
                    </tr>
                </table>

                <h2>Gestion des biens lors de l'import</h2>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Action sur les biens non présents</th>
                        <td>
                            <?php $missing_action = get_option( 'noty_missing_action', 'keep' ); ?>
                            <label style="display:block;margin-bottom:8px;">
                                <input type="radio" name="noty_missing_action" value="keep" <?php checked( $missing_action, 'keep' ); ?> />
                                <strong>Garder</strong> - Les biens non présents dans l'import restent publiés
                            </label>
                            <label style="display:block;margin-bottom:8px;">
                                <input type="radio" name="noty_missing_action" value="draft" <?php checked( $missing_action, 'draft' ); ?> />
                                <strong>Mettre en brouillon</strong> - Les biens non présents passent en statut brouillon
                            </label>
                            <label style="display:block;margin-bottom:8px;">
                                <input type="radio" name="noty_missing_action" value="delete" <?php checked( $missing_action, 'delete' ); ?> />
                                <strong>Supprimer</strong> - Les biens non présents sont supprimés définitivement
                            </label>
                            <p class="description">Définit ce qui arrive aux biens qui ne sont plus présents dans le fichier d'import de l'API.</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Suppression des photos</th>
                        <td>
                            <label>
                                <input type="checkbox" name="noty_delete_photos" value="1" <?php checked( get_option( 'noty_delete_photos' ), '1' ); ?> />
                                Supprimer les photos rattachées lors de la suppression d'un bien
                            </label>
                            <p class="description">Si activé, les photos de la bibliothèque de médias seront supprimées lorsqu'un bien est supprimé.</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Détection des doublons</th>
                        <td>
                            <?php $dup_mode = get_option( 'noty_duplicate_detection_mode', 'uuid' ); ?>
                            <label style="display:block;margin-bottom:8px;">
                                <input type="radio" name="noty_duplicate_detection_mode" value="uuid" <?php checked( $dup_mode, 'uuid' ); ?> />
                                <strong>UUID</strong> — Détecte les nouveaux posts par UUID uniquement (comportement par défaut)
                            </label>
                            <label style="display:block;margin-bottom:8px;">
                                <input type="radio" name="noty_duplicate_detection_mode" value="reference" <?php checked( $dup_mode, 'reference' ); ?> />
                                <strong>Référence</strong> — Détecte par référence (up_reference). Évite les doublons si l'API change l'UUID.
                            </label>
                            <label style="display:block;margin-bottom:8px;">
                                <input type="radio" name="noty_duplicate_detection_mode" value="uuid_reference" <?php checked( $dup_mode, 'uuid_reference' ); ?> />
                                <strong>UUID + Référence</strong> — Vérifie par UUID, puis par référence en fallback
                            </label>
                            <p class="description">Définit comment les annonces existantes sont détectées lors de l'import pour éviter les doublons.</p>
                            <div style="margin-top:12px;">
                                <label>
                                    <input type="checkbox" name="noty_delete_duplicates" value="1" <?php checked( get_option( 'noty_delete_duplicates' ), '1' ); ?> />
                                    Supprimer automatiquement les doublons lors du sync (garde le post le plus ancien)
                                </label>
                            </div>
                        </td>
                    </tr>
                </table>

                <h2>Configuration des champs de détails</h2>
                <p class="description">Choisissez les champs à afficher dans les détails complets et les détails résumés pour chaque type de transaction. L'ordre de sélection détermine l'ordre d'affichage.</p>
                
                <?php $this->render_fields_config_section(); ?>
                
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const containers = document.querySelectorAll('.noty-fields-available, .noty-fields-selected');
                    let draggedElement = null;

                    containers.forEach(container => {
                        container.addEventListener('dragover', function(e) {
                            e.preventDefault();
                            e.dataTransfer.dropEffect = 'move';
                            this.style.borderColor = '#2271b1';
                            this.style.borderWidth = '2px';
                        });

                        container.addEventListener('dragleave', function(e) {
                            this.style.borderColor = '#ddd';
                            this.style.borderWidth = '1px';
                        });

                        container.addEventListener('drop', function(e) {
                            e.preventDefault();
                            this.style.borderColor = '#ddd';
                            this.style.borderWidth = '1px';
                            
                            if (draggedElement && draggedElement.parentNode !== this) {
                                this.appendChild(draggedElement);
                                updateHiddenInputs();
                            }
                        });
                    });

                    document.querySelectorAll('.noty-field-item').forEach(item => {
                        item.addEventListener('dragstart', function(e) {
                            draggedElement = this;
                            e.dataTransfer.effectAllowed = 'move';
                            this.style.opacity = '0.5';
                        });

                        item.addEventListener('dragend', function(e) {
                            this.style.opacity = '1';
                            draggedElement = null;
                        });
                        
                        item.addEventListener('dragover', function(e) {
                            e.preventDefault();
                            if (this !== draggedElement && this.parentNode.classList.contains('noty-fields-selected')) {
                                const rect = this.getBoundingClientRect();
                                const midpoint = rect.top + rect.height / 2;
                                if (e.clientY < midpoint) {
                                    this.parentNode.insertBefore(draggedElement, this);
                                } else {
                                    this.parentNode.insertBefore(draggedElement, this.nextSibling);
                                }
                                updateHiddenInputs();
                            }
                        });
                    });

                    function updateHiddenInputs() {
                        document.querySelectorAll('.noty-fields-selected').forEach(selectedContainer => {
                            const containerId = selectedContainer.id;
                            const inputId = containerId.replace('selected-', 'input-');
                            const hiddenInput = document.getElementById(inputId);
                            
                            if (hiddenInput) {
                                const fields = Array.from(selectedContainer.querySelectorAll('.noty-field-item'))
                                    .map(item => item.getAttribute('data-field'))
                                    .filter(field => field);
                                hiddenInput.value = fields.join(',');
                            }
                        });
                    }
                });
                </script>

                <h2>Métas WordPress (sélection)</h2>
                <p class="description">La liste ci-dessous est alimentée automatiquement lors des imports. Coche les champs à enregistrer en métas WordPress (créées / mises à jour à chaque import).</p>

                <?php
                $discovered = get_option( 'noty_discovered_meta_paths', array() );
                $selected = get_option( 'noty_selected_meta_paths', array() );
                if ( ! is_array( $discovered ) ) {
                    $discovered = array();
                }
                if ( ! is_array( $selected ) ) {
                    $selected = array();
                }

                if ( empty( $discovered ) ) :
                    echo '<p>Aucun champ découvert pour le moment. Lance une synchronisation pour alimenter la liste.</p>';
                else :
                    ?>
                    <p style="margin-bottom:10px;">
                        <button type="button" id="select-all-metas" class="button">Tout sélectionner</button>
                        <button type="button" id="deselect-all-metas" class="button">Tout désélectionner</button>
                    </p>
                    
                    <div id="metas-checkboxes" style="max-height:360px;overflow:auto;border:1px solid #ccd0d4;background:#fff;padding:10px;">
                        <?php
                        foreach ( $discovered as $path ) {
                            if ( ! is_string( $path ) || $path === '' ) {
                                continue;
                            }

                            $is_checked = in_array( $path, $selected, true );
                            echo '<label style="display:block;margin:4px 0;">';
                            echo '<input type="checkbox" name="noty_selected_meta_paths[]" value="' . esc_attr( $path ) . '" ' . checked( $is_checked, true, false ) . ' /> ';
                            echo '<code>' . esc_html( $path ) . '</code>';
                            echo '</label>';
                        }
                        ?>
                    </div>
                    
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const selectAllBtn = document.getElementById('select-all-metas');
                        const deselectAllBtn = document.getElementById('deselect-all-metas');
                        const checkboxesContainer = document.getElementById('metas-checkboxes');
                        const checkboxes = checkboxesContainer.querySelectorAll('input[type="checkbox"]');

                        selectAllBtn.addEventListener('click', function() {
                            checkboxes.forEach(function(checkbox) {
                                checkbox.checked = true;
                            });
                        });

                        deselectAllBtn.addEventListener('click', function() {
                            checkboxes.forEach(function(checkbox) {
                                checkbox.checked = false;
                            });
                        });
                    });
                    </script>
                    <?php
                endif;
                ?>

                <?php submit_button(); ?>
            </form>

            <hr>

            <h2>Synchronisation manuelle</h2>
            <form method="post" action="">
                <?php wp_nonce_field( 'noty_sync_action', 'noty_sync_nonce' ); ?>
                <input type="hidden" name="noty_sync_now" value="1">
                <p>Cliquez sur le bouton ci-dessous pour lancer immédiatement la récupération des annonces.</p>
                <?php submit_button( 'Synchroniser maintenant', 'secondary' ); ?>
            </form>
        </div>
        <?php
    }

    public function import_json_page() {
        $uploads = wp_upload_dir();
        $dir = trailingslashit( $uploads['basedir'] ) . 'noty-debug';

        $files = array();
        if ( is_dir( $dir ) ) {
            $all_files = glob( trailingslashit( $dir ) . 'annonces-*.json' );
            if ( is_array( $all_files ) ) {
                rsort( $all_files );
                $files = $all_files;
            }
        }

        $selected_file = isset( $_GET['file'] ) ? sanitize_text_field( wp_unslash( $_GET['file'] ) ) : '';
        if ( $selected_file === '' && ! empty( $files ) ) {
            $selected_file = basename( $files[0] );
        }

        $file_path = '';
        if ( $selected_file !== '' ) {
            $candidate = trailingslashit( $dir ) . $selected_file;
            if ( file_exists( $candidate ) && is_readable( $candidate ) ) {
                $file_path = $candidate;
            }
        }

        $data = null;
        $annonces = array();
        $total_api = 0;
        $pages_api = 0;

        if ( $file_path !== '' ) {
            $raw = file_get_contents( $file_path );
            $decoded = json_decode( $raw, true );
            if ( is_array( $decoded ) ) {
                $data = $decoded;
                $annonces = isset( $decoded['results'] ) && is_array( $decoded['results'] ) ? $decoded['results'] : array();
                $total_api = isset( $decoded['total'] ) ? (int) $decoded['total'] : count( $annonces );
                $pages_api = isset( $decoded['pages'] ) ? (int) $decoded['pages'] : 1;
            }
        }

        $type_labels = array(
            'vente_traditionnelle' => 'Vente',
            'location'             => 'Location',
            'vente_viager'         => 'Viager',
        );

        $type_counts = array();
        $wp_counts = array( 'imported' => 0, 'missing' => 0 );

        foreach ( $annonces as $annonce ) {
            $type = isset( $annonce['transaction'] ) ? (string) $annonce['transaction'] : 'inconnu';
            if ( is_array( $type ) ) {
                $type = isset( $type['type'] ) ? (string) $type['type'] : 'inconnu';
            }
            if ( ! isset( $type_counts[ $type ] ) ) {
                $type_counts[ $type ] = 0;
            }
            $type_counts[ $type ]++;

            $uuid = isset( $annonce['uuid'] ) ? (string) $annonce['uuid'] : '';
            $exists = false;
            if ( $uuid !== '' ) {
                $found = get_posts( array(
                    'post_type'      => 'noty_annonce',
                    'posts_per_page' => 1,
                    'fields'         => 'ids',
                    'meta_query'     => array(
                        'relation' => 'OR',
                        array( 'key' => 'up_uuid', 'value' => $uuid ),
                        array( 'key' => '_noty_uuid', 'value' => $uuid ),
                    ),
                ) );
                $exists = ! empty( $found );
            }
            if ( $exists ) {
                $wp_counts['imported']++;
            } else {
                $wp_counts['missing']++;
            }
        }

        ?>
        <div class="wrap">
            <h1>Import JSON — Vérification</h1>

            <?php if ( empty( $files ) ) : ?>
                <div class="notice notice-warning"><p>Aucun fichier JSON trouvé dans <code>uploads/noty-debug/</code>. Activez « Debug : sauvegarde JSON » dans la configuration et lancez une synchronisation.</p></div>
            <?php else : ?>
                <form method="get" action="" style="margin:15px 0;">
                    <input type="hidden" name="post_type" value="noty_annonce">
                    <input type="hidden" name="page" value="noty-import-json">
                    <label for="noty-json-file"><strong>Fichier JSON :</strong></label>
                    <select name="file" id="noty-json-file" onchange="this.form.submit();">
                        <?php foreach ( $files as $f ) :
                            $bn = basename( $f );
                            $label = preg_replace( '/^annonces-/', '', $bn );
                            $label = preg_replace( '/\.json$/', '', $label );
                            ?>
                            <option value="<?php echo esc_attr( $bn ); ?>" <?php selected( $selected_file, $bn ); ?>><?php echo esc_html( $label ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            <?php endif; ?>

            <?php if ( $data !== null ) : ?>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;margin:20px 0;">
                    <div style="background:#fff;border:1px solid #ccd0d4;padding:15px;border-radius:4px;">
                        <div style="font-size:28px;font-weight:700;"><?php echo esc_html( count( $annonces ) ); ?></div>
                        <div style="color:#666;">Biens dans ce fichier</div>
                    </div>
                    <div style="background:#fff;border:1px solid #ccd0d4;padding:15px;border-radius:4px;">
                        <div style="font-size:28px;font-weight:700;color:#2271b1;"><?php echo esc_html( $total_api ); ?></div>
                        <div style="color:#666;">Total API<?php if ( $pages_api > 1 ) echo ' (' . esc_html( $pages_api ) . ' pages)'; ?></div>
                    </div>
                    <?php foreach ( $type_counts as $type => $count ) : ?>
                        <div style="background:#fff;border:1px solid #ccd0d4;padding:15px;border-radius:4px;">
                            <div style="font-size:28px;font-weight:700;"><?php echo esc_html( $count ); ?></div>
                            <div style="color:#666;"><?php echo esc_html( $type_labels[ $type ] ?? $type ); ?></div>
                        </div>
                    <?php endforeach; ?>
                    <div style="background:#fff;border:1px solid #ccd0d4;padding:15px;border-radius:4px;">
                        <div style="font-size:28px;font-weight:700;color:#1d7f1d;"><?php echo esc_html( $wp_counts['imported'] ); ?></div>
                        <div style="color:#666;">Importés dans WP</div>
                    </div>
                    <div style="background:#fff;border:1px solid #ccd0d4;padding:15px;border-radius:4px;">
                        <div style="font-size:28px;font-weight:700;color:<?php echo $wp_counts['missing'] > 0 ? '#b32d2e' : '#1d7f1d'; ?>;"><?php echo esc_html( $wp_counts['missing'] ); ?></div>
                        <div style="color:#666;">Manquants dans WP</div>
                    </div>
                </div>

                <?php if ( $pages_api > 1 ) : ?>
                    <div class="notice notice-warning"><p><strong>Attention :</strong> l'API retourne <?php echo esc_html( $total_api ); ?> biens sur <?php echo esc_html( $pages_api ); ?> pages, mais ce fichier ne contient que <?php echo esc_html( count( $annonces ) ); ?> biens (page 1 uniquement). Les biens des autres pages ne sont pas synchronisés.</p></div>
                <?php endif; ?>

                <table class="widefat striped" style="margin-top:15px;">
                    <thead>
                        <tr>
                            <th style="width:60px;">#</th>
                            <th style="width:140px;">Référence</th>
                            <th>Ville</th>
                            <th style="width:160px;">Type</th>
                            <th style="width:100px;text-align:right;">Prix</th>
                            <th style="width:80px;">WP</th>
                            <th>UUID</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $annonces as $i => $annonce ) :
                            $uuid = isset( $annonce['uuid'] ) ? (string) $annonce['uuid'] : '';
                            $reference = isset( $annonce['reference'] ) ? (string) $annonce['reference'] : '';
                            $ville = isset( $annonce['bien']['commune']['libelle'] ) ? (string) $annonce['bien']['commune']['libelle'] : '';
                            $type = isset( $annonce['transaction'] ) ? (string) $annonce['transaction'] : '';
                            if ( is_array( $annonce['transaction'] ?? null ) ) {
                                $type = isset( $annonce['transaction']['type'] ) ? (string) $annonce['transaction']['type'] : '';
                            }
                            $prix = isset( $annonce['prix'] ) ? (int) $annonce['prix'] : 0;
                            $loyer = isset( $annonce['loyer'] ) ? (int) $annonce['loyer'] : 0;
                            $prix_display = $prix > 0 ? number_format( $prix, 0, ',', ' ' ) . ' €' : ( $loyer > 0 ? number_format( $loyer, 0, ',', ' ' ) . ' €/mois' : '—' );

                            $exists = false;
                            $wp_post_id = 0;
                            if ( $uuid !== '' ) {
                                $found = get_posts( array(
                                    'post_type'      => 'noty_annonce',
                                    'posts_per_page' => 1,
                                    'fields'         => 'ids',
                                    'meta_query'     => array(
                                        'relation' => 'OR',
                                        array( 'key' => 'up_uuid', 'value' => $uuid ),
                                        array( 'key' => '_noty_uuid', 'value' => $uuid ),
                                    ),
                                ) );
                                if ( ! empty( $found ) ) {
                                    $exists = true;
                                    $wp_post_id = $found[0];
                                }
                            }
                        ?>
                            <tr<?php echo $exists ? '' : ' style="background:#fef7f7;"'; ?>>
                                <td><?php echo esc_html( $i + 1 ); ?></td>
                                <td><strong><?php echo esc_html( $reference ); ?></strong></td>
                                <td><?php echo esc_html( $ville ); ?></td>
                                <td><?php echo esc_html( $type_labels[ $type ] ?? $type ); ?></td>
                                <td style="text-align:right;"><?php echo esc_html( $prix_display ); ?></td>
                                <td>
                                    <?php if ( $exists ) : ?>
                                        <a href="<?php echo esc_url( get_edit_post_link( $wp_post_id ) ); ?>" style="color:#1d7f1d;font-weight:600;">✓</a>
                                    <?php else : ?>
                                        <span style="color:#b32d2e;font-weight:600;">✕</span>
                                    <?php endif; ?>
                                </td>
                                <td><code style="font-size:11px;color:#999;"><?php echo esc_html( substr( $uuid, 0, 8 ) ); ?>…</code></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php elseif ( ! empty( $files ) ) : ?>
                <div class="notice notice-error"><p>Impossible de lire le fichier JSON sélectionné.</p></div>
            <?php endif; ?>
        </div>
        <?php
    }

    public function shortcodes_page() {
        ?>
        <div class="wrap">
            <h1>Mode d'emploi</h1>

            <h2>Types de contenu et taxonomies</h2>
            
            <h3>Custom Post Types (CPT)</h3>
            <table class="widefat striped" style="max-width:920px;">
                <thead>
                    <tr>
                        <th style="width: 200px;">Nom</th>
                        <th style="width: 180px;">Slug</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Annonces Noty</strong></td>
                        <td><code>noty_annonce</code></td>
                        <td>Contenu principal pour les annonces immobilières synchronisées avec Noty</td>
                    </tr>
                </tbody>
            </table>

            <h3>Taxonomies</h3>
            <table class="widefat striped" style="max-width:920px;">
                <thead>
                    <tr>
                        <th style="width: 200px;">Nom</th>
                        <th style="width: 180px;">Slug</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Nature du bien</strong></td>
                        <td><code>noty_nature</code></td>
                        <td>Type de bien immobilier (Maison, Appartement, Studio, etc.)</td>
                    </tr>
                    <tr>
                        <td><strong>Type de transaction</strong></td>
                        <td><code>noty_transaction</code></td>
                        <td>Type de transaction (Vente, Location, Viager, etc.)</td>
                    </tr>
                    <tr>
                        <td><strong>Ville</strong></td>
                        <td><code>noty_ville</code></td>
                        <td>Ville ou commune où se situe le bien</td>
                    </tr>
                    <tr>
                        <td><strong>État du bien</strong></td>
                        <td><code>noty_etat</code></td>
                        <td>État général du bâtiment (Neuf, Bon état, Rénovation, etc.)</td>
                    </tr>
                </tbody>
            </table>

            <hr>

            <h2>Shortcodes disponibles</h2>

            <h3><code>[noty_annonces]</code></h3>
            <p>Affiche une liste d'annonces sous forme de cards.</p>
            <table class="widefat striped" style="max-width:920px;">
                <thead>
                    <tr>
                        <th>Attribut</th>
                        <th>Description</th>
                        <th>Exemple</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>limit</code></td>
                        <td>Nombre maximum d'annonces.</td>
                        <td><code>[noty_annonces limit="12"]</code></td>
                    </tr>
                    <tr>
                        <td><code>nature</code></td>
                        <td>Filtre par slug de la taxonomie <code>noty_nature</code>.</td>
                        <td><code>[noty_annonces nature="appartement"]</code></td>
                    </tr>
                    <tr>
                        <td><code>ville</code></td>
                        <td>Filtre par slug de la taxonomie <code>noty_ville</code>.</td>
                        <td><code>[noty_annonces ville="paris"]</code></td>
                    </tr>
                    <tr>
                        <td><code>transaction</code></td>
                        <td>Filtre par slug de la taxonomie <code>noty_transaction</code>.</td>
                        <td><code>[noty_annonces transaction="location"]</code></td>
                    </tr>
                </tbody>
            </table>

            <h4>Exemples</h4>
            <p><code>[noty_annonces]</code></p>
            <p><code>[noty_annonces limit="6" transaction="location"]</code></p>
            <p><code>[noty_annonces limit="9" ville="lyon" nature="maison"]</code></p>

            <hr>

            <h3><code>[noty_annonce]</code></h3>
            <p>Affiche une annonce complète (template single, avec variantes selon la transaction si disponible).</p>
            <table class="widefat striped" style="max-width:920px;">
                <thead>
                    <tr>
                        <th>Attribut</th>
                        <th>Description</th>
                        <th>Exemple</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>id</code></td>
                        <td>ID du post WordPress (CPT <code>noty_annonce</code>).</td>
                        <td><code>[noty_annonce id="123"]</code></td>
                    </tr>
                    <tr>
                        <td><code>uuid</code></td>
                        <td>UUID Noty de l'annonce (recherche sur la méta <code>up_uuid</code> puis fallback <code>_noty_uuid</code>).</td>
                        <td><code>[noty_annonce uuid="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"]</code></td>
                    </tr>
                </tbody>
            </table>

            <h4>Exemples</h4>
            <p><code>[noty_annonce]</code> (sur une page liée à une annonce)</p>
            <p><code>[noty_annonce id="123"]</code></p>
            <p><code>[noty_annonce uuid="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"]</code></p>
        </div>
        <?php
    }
}
