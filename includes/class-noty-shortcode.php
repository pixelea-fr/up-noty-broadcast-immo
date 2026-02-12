<?php

class Noty_Shortcode {
    public function __construct() {
        add_shortcode( 'noty_annonces', array( $this, 'render_annonces' ) );
    }

    public function render_annonces( $atts ) {
        $atts = shortcode_atts( array(
            'limit' => 12,
            'nature' => '',
            'ville' => '',
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

        if ( ! empty( $tax_query ) ) {
            $args['tax_query'] = $tax_query;
        }

        $query = new WP_Query( $args );
        
        ob_start();
        
        if ( $query->have_posts() ) {
            echo '<div class="noty-annonces-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">';
            while ( $query->have_posts() ) {
                $query->the_post();
                $post_id = get_the_ID();
                $prix = get_post_meta( $post_id, '_noty_prix', true );
                $surface = get_post_meta( $post_id, '_noty_surface_habitable', true );
                $pieces = get_post_meta( $post_id, '_noty_nb_pieces', true );
                
                // Récupérer les termes des taxonomies
                $villes = wp_get_post_terms( $post_id, 'noty_ville' );
                $ville_name = ! empty( $villes ) ? $villes[0]->name : '';
                
                $natures = wp_get_post_terms( $post_id, 'noty_nature' );
                $nature_name = ! empty( $natures ) ? $natures[0]->name : '';
                ?>
                <div class="noty-annonce-card" style="border: 1px solid #ddd; padding: 15px; border-radius: 8px; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="noty-thumbnail" style="margin-bottom: 10px; position: relative;">
                            <?php the_post_thumbnail( 'medium', array( 'style' => 'width: 100%; height: 200px; object-fit: cover; border-radius: 4px;' ) ); ?>
                            <span style="position: absolute; top: 10px; left: 10px; background: rgba(0,0,0,0.6); color: #fff; padding: 2px 8px; border-radius: 3px; font-size: 0.8em;">
                                <?php echo esc_html( $nature_name ); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <h3 style="margin: 0 0 10px 0; font-size: 1.1em; line-height: 1.3; height: 2.6em; overflow: hidden;">
                        <a href="<?php the_permalink(); ?>" style="text-decoration: none; color: #333;"><?php the_title(); ?></a>
                    </h3>
                    
                    <p style="color: #666; margin: 0 0 5px 0; font-weight: bold;">
                        <span class="dashicons dashicons-location" style="font-size: 16px; vertical-align: middle;"></span>
                        <?php echo esc_html( $ville_name ); ?>
                    </p>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px; padding-top: 10px; border-top: 1px solid #eee;">
                        <span style="font-size: 1.2em; color: #e67e22; font-weight: bold;">
                            <?php echo $prix ? number_format( (float)$prix, 0, ',', ' ' ) . ' €' : 'Prix sur demande'; ?>
                        </span>
                        <span style="font-size: 0.9em; color: #7f8c8d;">
                            <?php 
                            if ( $surface ) echo esc_html( $surface ) . ' m²';
                            if ( $surface && $pieces ) echo ' | ';
                            if ( $pieces ) echo esc_html( $pieces ) . ' p.';
                            ?>
                        </span>
                    </div>
                </div>
                <?php
            }
            echo '</div>';
            wp_reset_postdata();
        } else {
            echo '<p>Aucune annonce disponible pour le moment.</p>';
        }

        return ob_get_clean();
    }
}
