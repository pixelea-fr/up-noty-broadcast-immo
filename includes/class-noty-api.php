<?php

class Noty_API {
    private $token;
    private $base_url;

    public function __construct() {
        $this->token = get_option( 'noty_api_token', '' );
        $this->base_url = get_option( 'noty_api_url', 'https://api.broadcast.test.noty.fr' );
    }

    public function get_annonces( $page = 1, $limit = 100 ) {
        $url = add_query_arg( array(
            'page'  => $page,
            'limit' => $limit,
        ), $this->base_url . '/annonces' );

        return $this->make_request( $url );
    }

    public function get_offices() {
        return $this->make_request( $this->base_url . '/offices' );
    }

    public function get_photo_url( $annonce_uuid, $photo_uuid ) {
        // Retourne l'URL directe de l'API pour le téléchargement
        return $this->base_url . "/annonces/{$annonce_uuid}/photos/{$photo_uuid}";
    }

    private function make_request( $url ) {
        if ( empty( $this->token ) ) {
            return new WP_Error( 'missing_token', 'Le jeton API Noty est manquant.' );
        }

        $response = wp_remote_get( $url, array(
            'headers' => array(
                'AUTH-TOKEN' => $this->token,
                'Accept'     => 'application/json',
            ),
            'timeout' => 30,
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );

        if ( $code !== 200 ) {
            return new WP_Error( 'api_error', "Erreur API ({$code}): " . $body );
        }

        return json_decode( $body, true );
    }
}
