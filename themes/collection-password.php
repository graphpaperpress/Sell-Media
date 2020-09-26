<?php
/**
 * The Template for displaying password form for collections.
 *
 * @package Sell Media
 * @since 0.1
 */
get_header(); ?>

<div id="sell-media-collection-password" class="sell-media">
	<div id="content" role="main">
  	<form action="" method="POST">
  	     <p><?php _e( 'This collection is password protected.', 'sell_media' ); ?>
  	     <input type="text" value="" name="collection_password" />
  	     <input type="submit" value="<?php _e( 'Submit', 'sell_media' ); ?>" name="submit" />
  	     </p>
  	</form>
	</div><!-- #content -->
</div><!-- #sell_media-single .sell_media -->

<?php get_footer(); ?>
