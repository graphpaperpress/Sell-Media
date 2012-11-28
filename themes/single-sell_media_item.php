<?php
/**
 * The Template for displaying all single sell media items.
 *
 * @package Sell Media
 * @since 0.1
 */
get_header(); ?>

<div id="sell-media-single" class="sell-media">
	<div id="content" role="main">

	<?php while ( have_posts() ) : the_post(); ?>

		<div class="sell-media-content">
			<?php sell_media_item_icon( get_post_thumbnail_id( $post->ID ), 'large' ); ?>
			<div><?php the_content(); ?></div>
		</div>

		<div class="sell-media-meta">
			<h1 class="entry-title"><?php the_title(); ?></h1>
			<ul>
				<li class="filename"><span class="title"><?php _e( 'Filename', 'sell_media' ); ?>:</span> <?php sell_media_image_filename( $post->ID); ?></li>

				<?php if( sell_media_item_size( $post->ID ) ) : ?>
					<li class="size">
						<span class="title"><?php _e( 'Size', 'sell_media' ); ?>:</span>
						<?php print sell_media_item_size( $post->ID); ?>
					</li>
				<?php endif; ?>

				<?php if ( true == sell_media_item_has_taxonomy_terms( $post->ID, 'collection' ) ) { ?>
					<li class="collections"><span class="title"><?php _e( 'Collections', 'sell_media' ); ?>:</span> <?php sell_media_collections( $post->ID ); ?></li>
				<?php } ?>
				<?php if ( true == sell_media_item_has_taxonomy_terms( $post->ID, 'keywords' ) ) { ?>
					<li class="keywords"><span class="title"><?php _e( 'Keywords', 'sell_media' ); ?>:</span> <?php sell_media_image_keywords( $post->ID ); ?></li>
				<?php } ?>
				<li class="price"><span class="title"><?php _e( 'Price', 'sell_media' ); ?>:</span> <?php sell_media_item_price( $post->ID ); ?></li>
			</ul>
			<?php sell_media_item_buy_button( $post->ID, 'button', 'Purchase' ); ?>
		</div><!-- .sell-media-meta -->

	<?php endwhile; ?>

	</div><!-- #content -->
</div><!-- #sell_media-single .sell_media -->

<?php get_footer(); ?>