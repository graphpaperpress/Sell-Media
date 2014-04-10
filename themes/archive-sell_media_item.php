<?php
/**
 * The template for displaying Archive pages.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package Sell Media
 * @since 0.1
 */
get_header(); global $wp_query; ?>
    <div id="sell-media-archive" class="sell-media">
        <div id="content" role="main">
            <header class="entry-header">
                <h1 class="entry-title">
                    <?php $taxonomy = get_query_var( 'taxonomy' ); ?>
                    <?php if ( $taxonomy && ! empty( $wp_query->queried_object->name ) ) : ?>
                        <?php echo ucfirst( $taxonomy ); ?>: <?php echo ucfirst( $wp_query->queried_object->name ); ?>
                    <?php elseif ( is_post_type_archive( 'sell_media_item' ) ) : ?>
                        <?php $obj = get_post_type_object( 'sell_media_item' ); echo ucfirst( $obj->rewrite['slug'] ); ?>
                    <?php else : ?>
                        <?php _e( 'Archive', 'sell_media' ); ?>
                    <?php endif; ?>
                </h1>
            </header>

            <div class="sell-media-grid-container">
                <?php if ( have_posts() ) : ?>
                    <?php rewind_posts(); ?>
                    <?php $i = 0; ?>
                    <?php while ( have_posts() ) : the_post(); $i++; ?>
                        <div class="sell-media-grid<?php if ( $i %3 == 0 ) echo ' end'; ?>">
                            <div class="item-inner">
                                <a href="<?php the_permalink(); ?>"><?php sell_media_item_icon( $post->ID, apply_filters( 'sell_media_thumbnail', 'medium' ) ); ?></a>
                                <span class="item-overlay">
                                    <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                    <?php sell_media_item_buy_button( $post->ID, 'text', __( 'Purchase' ) ); ?>
                                    <?php do_action( 'sell_media_item_overlay' ); ?>
                                </span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    <?php sell_media_pagination_filter(); ?>
                <?php else : ?>
                    <p><?php _e( 'Nothing Found', 'sell_media' ); ?></p>
                <?php endif; $i = 0; ?>
            </div><!-- .sell-media-grid-container -->
        </div><!-- #content -->
    </div><!-- #sell_media-single .sell_media -->
<?php do_action( 'sell_media_before_footer' ); ?>
<?php get_footer(); ?>