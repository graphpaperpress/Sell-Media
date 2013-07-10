<?php
/**
 * The template for displaying Search pages in Sell Media.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package Sell Media
 * @since 0.1
 */
get_header(); ?>
	<div id="sell-media-archive" class="sell-media">
		<div id="content" role="main">

			<?php if ( have_posts() ) : ?>

			<header class="entry-header">
				<h1 class="entry-title"><?php printf( __( 'Search Results for: %s', 'sell_media' ), '<span>' . $_GET['keyword'] . '</span>' ); ?></h1>
			</header>

			<div class="sell-media-grid-container">
				<?php $i = 0; ?>
				<?php while ( have_posts() ) : the_post(); $i++; ?>
					<?php
						if ( $i %3 == 0)
							$end = ' end';
						else
							$end = null;
					?>
					<div class="sell-media-grid<?php echo $end; ?>">
						<a href="<?php the_permalink(); ?>"><?php sell_media_item_icon( get_post_meta( $post->ID, '_sell_media_attachment_id', true ) ); ?></a>
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<?php sell_media_item_buy_button( $post->ID, 'text', 'Purchase' ); ?>
					</div>

				<?php endwhile; wp_reset_postdata(); ?>
    			
				<?php sell_media_pagination_filter(); ?>
			</div><!-- .sell-media-grid-container -->
			<?php else : ?>
				<p><?php _e( 'Nothing Found', 'sell_media' ); ?></p>
				<?php echo do_shortcode( '[sell_media_searchform]' ); ?>
			<?php endif; $i = 0; ?>
		</div><!-- #content -->
	</div><!-- #sell_media-single .sell_media -->
<?php get_footer(); ?>