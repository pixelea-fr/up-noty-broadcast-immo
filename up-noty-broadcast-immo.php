<?php
/**
 * Plugin Name: Noty Broadcast Immo
 * Description: Affiche les annonces immobilières depuis l'API Noty Broadcast.
 * Version: 1.2.0
 * Author: GEHIN Nicolas
 * Text Domain: noty-broadcast-immo
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Définition des constantes
define( 'NOTY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'NOTY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Chargement des classes
require_once NOTY_PLUGIN_DIR . 'includes/class-noty-api.php';
require_once NOTY_PLUGIN_DIR . 'includes/class-noty-cpt.php';
require_once NOTY_PLUGIN_DIR . 'includes/class-noty-sync.php';
require_once NOTY_PLUGIN_DIR . 'includes/class-noty-admin.php';
require_once NOTY_PLUGIN_DIR . 'includes/class-noty-shortcode.php';

function noty_broadcast_register_styles() {
    $relative = 'style.css';
    $path = trailingslashit( NOTY_PLUGIN_DIR ) . $relative;
    $url = trailingslashit( NOTY_PLUGIN_URL ) . $relative;
    $ver = file_exists( $path ) ? (string) filemtime( $path ) : null;

    wp_register_style( 'up-immo-style', $url, array(), $ver );
    wp_enqueue_style( 'up-immo-style' );
}
add_action( 'wp_enqueue_scripts', 'noty_broadcast_register_styles' );

// Initialisation du plugin
function noty_broadcast_init() {
    $cpt = new Noty_CPT();
    $admin = new Noty_Admin();
    $sync = new Noty_Sync();
    $shortcode = new Noty_Shortcode();
}
add_action( 'plugins_loaded', 'noty_broadcast_init' );

// Activation du plugin : Planification de la synchronisation
register_activation_hook( __FILE__, 'noty_broadcast_activation' );
function noty_broadcast_activation() {
    if ( ! wp_next_scheduled( 'noty_sync_annonces_event' ) ) {
        wp_schedule_event( time(), 'daily', 'noty_sync_annonces_event' );
    }
}

// Désactivation du plugin : Nettoyage de la planification
register_deactivation_hook( __FILE__, 'noty_broadcast_deactivation' );
function noty_broadcast_deactivation() {
    wp_clear_scheduled_hook( 'noty_sync_annonces_event' );
}
