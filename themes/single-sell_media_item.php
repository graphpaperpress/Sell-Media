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
			<?php sell_media_item_icon( get_post_meta( $post->ID, '_sell_media_attachment_id', true ), 'large' ); ?>
			<div><?php the_content(); ?></div>
			<p class="sell-media-credit"><?php sell_media_plugin_credit(); ?></p>
		</div>

		<div class="sell-media-meta">
			<h1 class="entry-title"><?php the_title(); ?></h1>
			<ul>
				<li class="filename"><span class="title"><?php _e( 'File ID', 'sell_media' ); ?>:</span> <?php echo get_the_id(); ?></li>

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
				<?php
				$wp_upload_dir = wp_upload_dir();
				$mime_type = wp_check_filetype( $wp_upload_dir['basedir'] . SellMedia::upload_dir . '/' . get_post_meta( $post->ID, '_sell_media_attached_file', true ) );
				if ( in_array( $mime_type['type'], array( 'image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/tiff' ) ) ): ?>
					<?php sell_media_image_sizes( $post->ID ); ?>
				<?php endif; ?>
				<li class="price">
					<span class="title"><?php _e( 'Original', 'sell_media' ); ?>
						<?php if ( in_array( $mime_type['type'], array( 'image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/tiff' ) ) ): ?>
							(<?php sell_media_original_image_size( $post->ID ); ?>):
						<?php endif ?>
					</span>
					<?php sell_media_item_price( $post->ID ); ?>
				</li>
			</ul>
			<?php sell_media_item_buy_button( $post->ID, 'button', 'Purchase' ); ?>
		</div><!-- .sell-media-meta -->

	<?php endwhile; ?>
	<?php do_action( 'sell_media_single_bottom_hook' ); ?>
	</div><!-- #content -->
</div><!-- #sell_media-single .sell_media -->

<?php get_footer(); ?>
