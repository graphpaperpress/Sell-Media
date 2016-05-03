<?php
/**
 * The template for displaying Sell Media Taxonomy Archive pages.
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

    <?php do_action( 'sell_media_above_archive_content' ); ?>

    <div id="sell-media-archive" class="sell-media">
        <div id="content" role="main">

            <?php do_action( 'sell_media_above_archive_header_content' ); ?>

            <header class="sell-media-page-header">
                <h1 class="sell-media-page-title">
                    <?php
                        if ( is_search() ) {
                            printf( __( 'Search results for: %1$s', 'sell_media' ), get_search_query() );
                        } elseif ( is_post_type_archive( 'sell_media_item' ) ) {
                            $obj = get_post_type_object( 'sell_media_item' );
                            echo esc_attr( ucfirst( $obj->rewrite['slug'] ) );
                        } else {
                            the_archive_title();
                        }
                    ?>
                </h1>
                <?php
                    $term_description = term_description();
                    if ( ! empty( $term_description ) ) {
                        echo '<h2 class="collection_description>">' . $term_description . '</h2>';
                    }
                ?>
                <?php echo do_shortcode( '[sell_media_searchform]' ); ?>
            </header>

            <?php do_action( 'sell_media_below_archive_header_content' ); ?>

            <div class="<?php echo apply_filters( 'sell_media_grid_item_container_class', 'sell-media-grid-item-container' ); ?>">

            <?php
            // check if this term has child terms, if so, show terms
            $term_ID = $wp_query->get_queried_object_id();
            $children = get_term_children( $term_ID, 'collection' );

            if ( $children ) :

                $taxonomy_name = 'collection';
                $i = 0;
                $args = array(
                    'orderby' => 'name',
                    'hide_empty' => false,
                    'parent' => $term_ID
                );

                $terms = get_terms( $taxonomy_name, $args );

                // count number of child terms
                $c = 0;

                if ( ! is_wp_error( $terms ) ) {
                    foreach ( $terms as $child ) {
                        $c++;
                        $args = array(
                            'post_status' => 'publish',
                            'taxonomy' => 'collection',
                            'field' => 'slug',
                            'term' => $child->slug
                        );
                        $posts = New WP_Query( $args );
                        $post_count = 0;
                        $post_count = $posts->found_posts;

                        if ( $post_count != 0 ) : $i++; ?>

                            <div class="<?php echo apply_filters( 'sell_media_grid_item_class', 'sell-media-grid-item', NULL ); ?>">
                                <div class="sell-media-item-wrap sell-media-collection">
                                    <a href="<?php echo get_term_link( $child ); ?>" class="collection">

                                        <div class="sell-media-item-details">
                                            <div class="sell-media-collection-details">
                                                <h3 class="collection-title"><?php echo $child->name; ?></h3>
                                                <span class="sell-media-collection-count"><span class="count"><?php echo $post_count; ?></span><?php _e( ' images in ', 'sell_media' ); ?><span class="collection"><?php echo $child->name; ?></span><?php _e(' collection', 'sell_media'); ?></span>
                                                <span class="sell-media-collection-price"><?php _e( 'Starting at', 'sell_media' ); ?> <span class="price"><?php echo sell_media_get_currency_symbol(); ?><?php echo $settings->default_price; ?></span></span>
                                            </div>
                                        </div>
                                        <?php
                                        $args = array(
                                            'posts_per_page' => 1,
                                            'taxonomy' => 'collection',
                                            'field' => 'slug',
                                            'term' => $child->slug
                                        );
                                        $posts = New WP_Query( $args );
                                        ?>

                                        <?php foreach( $posts->posts as $post ) : ?>
                                            <?php
                                                $collection_attachment_id = get_term_meta( $child->term_id, 'collection_icon_id', true );
                                                if ( ! empty ( $collection_attachment_id ) ) {
                                                    echo wp_get_attachment_image( $collection_attachment_id, 'sell_media_item' );
                                                } else {
                                                    sell_media_item_icon( $post->ID, 'sell_media_item' );
                                                }
                                            ?>
                                        <?php endforeach; ?>
                                    </a>
                                </div><!-- .sell-media-item-wrap -->
                            </div>
                        <?php endif; ?><!-- loop over term children -->
                    <?php } ?><!-- show child terms check -->
                 <?php } ?><!-- not a WP error check -->

            <?php else : ?>

                <?php if ( have_posts() ) : ?>
                    <?php rewind_posts(); ?>
                    <?php $i = 0; ?>
                    <?php while ( have_posts() ) : the_post(); $i++; ?>
                        <?php echo apply_filters( 'sell_media_content_loop', $post->ID, $i ); ?>
                    <?php endwhile; ?>
                <?php else : ?>
                    <h2><?php _e( 'Nothing Found', 'sell_media' ); ?></h2>
                    <p><?php _e( 'Sorry, but we couldn\'t find anything that matches your search query.', 'sell_media' ); ?>
                    <?php echo do_shortcode( '[sell_media_searchform]' ); ?>
                <?php endif; $i = 0; ?>

            <?php endif; ?><!-- show child terms check -->

            </div><!-- .sell-media-grid-item-container -->
            <?php 
            if( !$children ){
                echo sell_media_pagination_filter( $wp_query->max_num_pages ); 
            }
            ?>
        </div><!-- #content -->
    </div><!-- #sell-media-archive .sell-media -->

<?php do_action( 'sell_media_below_archive_content' ); ?>
<?php do_action( 'sell_media_before_footer' ); ?>
<?php get_footer(); ?>