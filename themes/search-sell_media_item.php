<?php

status_header('200 OK');

/**
 * The template for displaying Search pages in Sell Media.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package Sell Media
 * @since 0.1
 */

get_header();

$search_query = sell_media_search_query();

?>
	<div id="sell-media-archive" class="sell-media">
		<div id="content" role="main">
			<?php echo do_shortcode( '[sell_media_searchform]' ); ?>
			<?php if ( ! empty( $search_query ) && $search_query->posts ) : $i=0; ?>
				<header class="entry-header">
					<h1 class="entry-title">
						<?php if ( ! empty( $search_terms['s'] ) ) : ?><?php printf( __( 'Search Results for: %s', 'sell_media' ), '<span>'.$search_terms['s'].'</span>' ); ?><?php endif; ?>
					</h1>
				</header>
				<div class="sell-media-grid-container">
					<?php foreach( $search_query->posts as $post ) : setup_postdata( $post ); $i++; ?>
						<?php $end = ( $i %3 == 0 ) ? ' end' : null; ?>
						<div class="sell-media-grid<?php echo $end; ?>">
							<a href="<?php the_permalink(); ?>"><?php sell_media_item_icon( get_post_meta( $post->ID, '_sell_media_attachment_id', true ) ); ?></a>
							<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<?php sell_media_item_buy_button( $post->ID, 'text', 'Purchase' ); ?>
						</div>
					<?php endforeach; remove_filter( 'posts_where', 'sell_media_search_where' ); ?>
					<?php sell_media_pagination_filter(); ?>
				</div>
			<?php else : ?>
				<p><?php _e( 'Nothing Found', 'sell_media' ); ?></p>
                <?php //echo do_shortcode( '[sell_media_searchform]' ); ?>
            <?php endif; $i = 0; ?>

		</div><!-- #content -->
	</div><!-- #sell_media-single .sell_media -->
<?php get_footer(); ?>