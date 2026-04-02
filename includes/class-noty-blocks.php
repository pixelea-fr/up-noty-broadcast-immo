<?php

class Noty_Blocks {
    public function __construct() {
        add_action( 'init', array( $this, 'register_dynamic_blocks' ) );
    }

    public function register_dynamic_blocks() {
        // Bloc pour le template card
        register_block_type( 'noty-broadcast-immo/card', [
            'render_callback' => function( $attributes, $content, $block ) {
                $post_id = $block->context['postId'] ?? get_the_ID();
                if ( ! $post_id ) {
                    return '';
                }
                
                return '<div class="wp-block-noty-broadcast-immo-card">' . do_shortcode( '[noty_annonce id="' . $post_id . '" template="card"]' ) . '</div>';
            },
            'uses_context' => ['postId', 'postType'],
        ] );

        // Bloc pour le template single
        register_block_type( 'noty-broadcast-immo/single', [
            'render_callback' => function( $attributes, $content, $block ) {
                $post_id = $block->context['postId'] ?? get_the_ID();
                if ( ! $post_id ) {
                    return '';
                }
                
                return '<div class="wp-block-noty-broadcast-immo-single">' . do_shortcode( '[noty_annonce id="' . $post_id . '" template="single"]' ) . '</div>';
            },
            'uses_context' => ['postId', 'postType'],
        ] );
    }
}
