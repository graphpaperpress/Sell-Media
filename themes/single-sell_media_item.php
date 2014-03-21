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
			<?php sell_media_item_icon( $post->ID, 'large' ); ?>
			<div class="sell-media-content-text">
				<?php the_content(); ?>
			</div>
		</div>

		<div class="sell-media-meta">
			<p class="sell-media-single-button"><?php sell_media_item_buy_button( $post->ID, 'button', __( 'Purchase' ) ); ?></p>
			<h1 class="entry-title"><?php the_title(); ?></h1>
			<ul>
				<li class="filename"><span class="title"><?php _e( 'File ID', 'sell_media' ); ?>:</span> <?php echo get_the_id(); ?></li>
				<li class="filetype"><span class="title"><?php _e( 'File Type', 'sell_media' ); ?>:</span> <?php echo sell_media_get_filetype( $post->ID ); ?></li>

				<?php if ( true == wp_get_post_terms( $post->ID, 'collection' ) ) { ?>
					<li class="collections"><span class="title"><?php _e( 'Collections', 'sell_media' ); ?>:</span> <?php sell_media_collections( $post->ID ); ?></li>
				<?php } ?>
				<?php if ( true == wp_get_post_terms( $post->ID, 'keywords' ) ) {?>
					<li class="keywords"><span class="title"><?php _e( 'Keywords', 'sell_media' ); ?>:</span>
					<?php $product_terms = wp_get_object_terms( $post->ID, 'keywords' );
			        if ( !empty( $product_terms ) ) {
			            if ( ! is_wp_error( $product_terms ) ) {
			                foreach ( $product_terms as $term ) {
			                    echo '<a href="' . get_term_link( $term->slug, 'keywords' ) . '">' . $term->name . '</a> ';
			                }
			            }
			        }?>
					</li>
				<?php } ?>

				<?php do_action('sell_media_additional_list_items'); ?>

			</ul>
		</div><!-- .sell-media-meta -->

		<div class="sell-media-postmeta">
			<p class="sell-media-credit"><?php sell_media_plugin_credit(); ?></p>
			<div class="sell-media-prev-next">
				<?php previous_post_link('<span class="prev">&laquo; %link</span>', '%title', true, '', 'collection' ); ?>
				<?php next_post_link('<span class="next">%link &raquo;</span>', '%title', true, '', 'collection'); ?>
			</div>
			<?php edit_post_link('edit', '<p>', '</p>'); ?>
		</div><!-- .sell-media-postmeta -->

	<?php endwhile; ?>
	<?php do_action( 'sell_media_single_bottom_hook' ); ?>
	</div><!-- #content -->
</div><!-- #sell_media-single .sell_media -->

<?php get_footer(); ?>
