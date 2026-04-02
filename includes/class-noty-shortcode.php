<?php

class Noty_Shortcode {
    public function __construct() {
        add_shortcode( 'noty_annonces', array( $this, 'render_annonces' ) );
        add_shortcode( 'noty_annonce', array( $this, 'render_annonce' ) );
        add_filter( 'render_block', array( $this, 'render_block_shortcodes' ), 9, 2 );
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
        static $assets_enqueued = false;

        $atts = shortcode_atts( array(
            'id' => 0,
            'uuid' => '',
            'template' => 'single',
            'order-in-column' => 'false',
        ), $atts );

        // Enqueue assets only once when the 'single' template is used
        if ( ! $assets_enqueued && $atts['template'] === 'single' ) {
            // Swiper
            wp_enqueue_style( 'swiper-css', 'https://unpkg.com/swiper/swiper-bundle.min.css' );
            wp_enqueue_script( 'swiper-js', 'https://unpkg.com/swiper/swiper-bundle.min.js', array('jquery'), null, true );

            // Fancybox
            wp_enqueue_style( 'fancybox-css', 'https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0/dist/fancybox.css' );
            wp_enqueue_script( 'fancybox-js', 'https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0/dist/fancybox.umd.js', array(), null, true );

            // Custom Slider Assets
            wp_enqueue_style( 'up-immo-gallery-slider', NOTY_PLUGIN_URL . 'assets/css/gallery-slider.css', array('swiper-css', 'fancybox-css'), filemtime( NOTY_PLUGIN_DIR . 'assets/css/gallery-slider.css' ) );
            wp_enqueue_script( 'up-immo-gallery-slider', NOTY_PLUGIN_URL . 'assets/js/gallery-slider.js', array( 'swiper-js', 'fancybox-js' ), filemtime( NOTY_PLUGIN_DIR . 'assets/js/gallery-slider.js' ), true );
            
            $assets_enqueued = true;
        }

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
        
        if ( $order_in_column && $template_type !== 'card' && isset( $annonce->bien->details ) && is_array( $annonce->bien->details ) ) {
            $annonce->bien->details = Noty_Annonce::order_items_in_columns( $annonce->bien->details, 2 );
        }
        $html = $this->render_template( $template, array( 'post_id' => $post_id, 'annonce' => $annonce, 'bien' => $annonce->bien ) );

        unset( $rendering[ $render_key ] );
        return $html;
    }
}
