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
    }

    public function register_settings() {
        register_setting( 'noty_settings_group', 'noty_api_token' );
        register_setting( 'noty_settings_group', 'noty_api_url' );
        register_setting( 'noty_settings_group', 'noty_debug_dump_json' );
        register_setting( 'noty_settings_group', 'noty_selected_meta_paths' );
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
                </table>

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
                    echo '<div style="max-height:360px;overflow:auto;border:1px solid #ccd0d4;background:#fff;padding:10px;">';
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
                    echo '</div>';
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

    public function shortcodes_page() {
        ?>
        <div class="wrap">
            <h1>Mode d'emploi</h1>

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
