<?php
/**
 * Réécriture des URLs pour les archives de type de transaction
 * Remplace /type-transaction/[slug]/ par des URLs SEO-friendly
 *
 * À ajouter dans functions.php
 */

if ( ! function_exists( 'noty_immo_transaction_term_path' ) ) {
    function noty_immo_transaction_term_path( $term ) {
        $default = array(
            'location' => 'location-immobiliere',
            'vente_traditionnelle' => 'vente-immobiliere',
            'vente-traditionnelle' => 'vente-immobiliere',
            'vente_viager' => 'vente-en-viager',
            'vente-viager' => 'vente-en-viager',
        );

        $custom = get_term_meta( $term->term_id, 'noty_slug_replace', true );
        $custom = is_string( $custom ) ? trim( $custom ) : '';
        $custom = ltrim( rtrim( $custom, '/' ), '/' );

        if ( $custom !== '' ) {
            return $custom;
        }

        return isset( $default[ $term->slug ] ) ? $default[ $term->slug ] : '';
    }
}

// 1. Modifier les slugs de la taxonomie / CPT au moment de l'enregistrement
add_filter( 'register_taxonomy_args', function( $args, $taxonomy ) {

    // Remplace 'type-transaction' par le nom réel de ta taxonomie
    if ( $taxonomy === 'noty_transaction' ) {
        $args['rewrite'] = [
            'slug'         => 'immobilier', // base commune
            'with_front'   => false,
            'hierarchical' => false,
        ];
    }

    return $args;
}, 10, 2 );


// 2. Réécriture manuelle des slugs de termes
add_filter( 'term_link', function( $url, $term, $taxonomy ) {

    if ( $taxonomy !== 'noty_transaction' ) {
        return $url;
    }

    $path = noty_immo_transaction_term_path( $term );
    if ( $path !== '' ) {
        return home_url( '/' . $path . '/' );
    }

    return $url;

}, 10, 3 );


// 3. Ajouter les règles de réécriture pour que WordPress comprenne ces URLs
add_action( 'init', function() {

    $terms = get_terms( array(
        'taxonomy' => 'noty_transaction',
        'hide_empty' => false,
    ) );
    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        return;
    }

    foreach ( $terms as $term ) {
        $path = noty_immo_transaction_term_path( $term );
        if ( $path === '' ) {
            continue;
        }

        $regex_base = preg_quote( $path, '/' );

        add_rewrite_rule( $regex_base . '/?$', 'index.php?noty_transaction=' . $term->slug, 'top' );
        add_rewrite_rule( $regex_base . '/page/([0-9]+)/?$', 'index.php?noty_transaction=' . $term->slug . '&paged=$matches[1]', 'top' );
    }

}, 20 );


// 4. Redirection 301 des anciennes URLs vers les nouvelles (SEO)
add_action( 'template_redirect', function() {

    $request = $_SERVER['REQUEST_URI'];

    $redirects = [
        '/type-transaction/location'             => '/location-immobiliere/',
        '/type-transaction/location/'            => '/location-immobiliere/',
        '/type-transaction/vente_traditionnelle' => '/vente-immobiliere/',
        '/type-transaction/vente_traditionnelle/'=> '/vente-immobiliere/',
        '/type-transaction/vente-traditionnelle' => '/vente-immobiliere/',
        '/type-transaction/vente-traditionnelle/'=> '/vente-immobiliere/',
        '/type-transaction/vente_viager'         => '/vente-en-viager/',
        '/type-transaction/vente_viager/'        => '/vente-en-viager/',
        '/type-transaction/vente-viager'         => '/vente-en-viager/',
        '/type-transaction/vente-viager/'        => '/vente-en-viager/',
    ];

    // On compare sans les query strings
    $path = strtok( $request, '?' );

    if ( isset( $redirects[ $path ] ) ) {
        wp_redirect( home_url( $redirects[ $path ] ), 301 );
        exit;
    }

} );