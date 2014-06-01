<?php
/**
 * The template for displaying Archive pages.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package Sell Media
 * @since 0.1
 */
get_header();
global $wp_query;
$settings = sell_media_get_plugin_options();
?>
    <div id="sell-media-archive" class="sell-media">
        <div id="content" role="main">
            <header class="page-header">
                <h1 class="page-title">
                    <?php
                    $taxonomy = get_query_var( 'taxonomy' );
                    $term = get_term_by( 'slug', get_query_var( 'term' ), $taxonomy );
                    $term_parent = get_term( $term->parent, $taxonomy );

                    if ( $taxonomy && ! empty( $term ) ) :
                        echo '<ul class="sell-media-breadcrumbs">';
                        echo '<li><a href="'. site_url( 'collections' ). '" title="' . __( 'Collections', 'sell_media' ) . '">' . __( 'Collections', 'sell_media' ) . '</a> <span class="raquo">&raquo;</span> </li>';
                        sell_media_taxonomy_breadcrumb();
                        echo '</ul>';
                    elseif ( is_post_type_archive( 'sell_media_item' ) ) :
                        $obj = get_post_type_object( 'sell_media_item' );
                        echo ucfirst( $obj->rewrite['slug'] );
                    else :
                        _e( 'Archive', 'sell_media' );
                    endif;
                    ?>
                </h1>
            </header>

            <div id="sell-media-grid-container" class="sell-media-grid-container">

                <?php

                /*
                 * Retrieves all the terms from the taxonomy collection
                 * http://codex.wordpress.org/Function_Reference/get_categories
                 */

                if ( get_term_children( $term->term_id, $taxonomy ) ) :

                    $args = array(
                        'type'          => 'sell_media_item',
                        'orderby'       => 'name',
                        'order'         => 'ASC',
                        'taxonomy'      => $taxonomy,
                        'parent'        => $term->term_id,
                        'hide_empty'    => 0
                    );
                    $categories = get_categories( $args );

                    /*
                     * Pulls the first post from each individual collection
                     */
                    foreach( $categories as $category ) {
                        $args = array(
                            'posts_per_page'            => 1,
                            'post_type'                 => 'sell_media_item',
                            'collection'                => $category->slug,
                            'no_found_rows'             => true,
                            'update_post_meta_cache'    => false,
                            'update_post_term_cache'    => false
                        );
                        $the_query = new WP_Query( $args );
                        $i = 0;
                        // The Loop
                        while ( $the_query->have_posts() ) : $the_query->the_post(); $i++; ?>
                            <div class="sell-media-grid<?php if ( $i %3 == 0 ) echo ' end'; ?>">
                            <div class="item-inner">
                                <?php
                                    $collection_attachment_id = sell_media_get_term_meta( $category->term_id, 'collection_icon_id', true );
                                    $thumb_id = ( ! empty( $collection_attachment_id ) ? $collection_attachment_id : $post->ID );
                                ?>
                                <a href="<?php echo get_term_link( $category->slug, $taxonomy ); ?>"><?php sell_media_item_icon( $thumb_id, apply_filters( 'sell_media_thumbnail', 'medium' ) ); ?></a>
                                <span class="item-overlay">
                                    <div class="collection-details">
                                        <h3><a href="<?php echo get_term_link( $category->slug, $taxonomy ); ?>"><?php echo $category->name; ?></a></h3>
                                        <span class="collection-count"><span class="count"><?php if ( $category->count ) echo $category->count; else echo sell_media_get_cat_post_count( $term->term_id ); ?></span><?php _e( ' images in ', 'sell_media' ); ?><span class="collection"><?php echo $category->name; ?></span><?php _e(' collection', 'sell_media'); ?></span>
                                        <span class="collection-price"><?php _e( 'Starting at', 'sell_media' ); ?> <span class="price"><?php echo sell_media_get_currency_symbol(); ?><?php echo sell_media_item_min_price( $post->ID ); ?></span></span>
                                    </div>
                                </span>
                            </div>
                        </div>
                        <?php endwhile;
                    }
                    // Reset Post Data
                    wp_reset_postdata();

                else : ?>

                    <?php if ( have_posts() ) : ?>
                        <?php rewind_posts(); ?>
                        <?php $i = 0; ?>
                        <?php while ( have_posts() ) : the_post(); $i++; ?>
                            <div class="sell-media-grid<?php if ( $i %3 == 0 ) echo ' end'; ?>">
                                <div class="item-inner">
                                    <a href="<?php the_permalink(); ?>"><?php sell_media_item_icon( $post->ID, apply_filters( 'sell_media_thumbnail', 'medium' ) ); ?></a>
                                    <span class="item-overlay">
                                        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                        <?php do_action( 'sell_media_item_overlay' ); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                        <?php sell_media_pagination_filter(); ?>
                    <?php else : ?>
                        <p><?php _e( 'Nothing Found', 'sell_media' ); ?></p>
                    <?php endif; $i = 0; ?>
                <?php endif; ?>
            </div><!-- .sell-media-grid-container -->
        </div><!-- #content -->
    </div><!-- #sell_media-single .sell_media -->
<?php do_action( 'sell_media_before_footer' ); ?>
<?php get_footer(); ?>
