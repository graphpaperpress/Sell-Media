<?php

/**
 * Template for Cart dialog
 */

$post_id        = (isset($_POST['product_id'])) ? absint( $_POST['product_id'] ) : '';
$attachment_id  = (isset($_POST['attachment_id'])) ? absint( $_POST['attachment_id'] ) : '';
$location       = isset( $_POST['location'] ) ? sanitize_text_field( $_POST['location'] ) : '';
$image_id       = ( sell_media_has_multiple_attachments( $post_id ) ) ? $attachment_id : $post_id;

ob_start();
?>
<div class="sell-media-quick-view-container">
	<?php if ( ! post_password_required( $post_id ) ) : ?>
	<div class="sell-media-quick-view-image">
		<?php
			$mime_type = get_post_mime_type( $attachment_id );	
			// if selling video or audio, show the post_id thumbnail
			if ( SellMediaAudioVideo::is_video_item( $post_id ) || SellMediaAudioVideo::is_audio_item( $post_id ) || 'application/pdf' === $mime_type || 'application/zip' === $mime_type ) {
				$image = sell_media_item_icon( $post_id, 'full', false );
			} else {
				$image = sell_media_item_icon( $attachment_id, 'full', false );
			}
		?>
		<?php echo wp_kses(apply_filters( 'sell_media_quick_view_post_thumbnail', $image, $post_id ), array(
		        'div' => array('class' => true, 'id' => true, 'data-*' => true),
		        'span' => array('class' => true, 'id' => true, 'data-*' => true),
		        'a' => array('class' => true, 'id' => true, 'data-*' => true, 'href' => true, 'target' => true),
		        'img' => array('class' => true, 'id' => true, 'data-*' => true, 'src' => true, 'height' => true, 'width' => true),
        ) ); ?>
	</div>
	
	<div class="sell-media-quick-view-content">
		<div class="sell-media-quick-view-content-inner">

			<h6><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" <?php 
			_e(sell_media_link_attributes( $post_id ),'sell_media'); ?>><?php echo esc_html( get_the_title( $post_id ) ); ?><?php if ( sell_media_has_multiple_attachments( esc_attr( $post_id ) ) ) { esc_html_e(', ' . esc_attr( $attachment_id ),'sell_media'); } ?></a></h6>
			<?php do_action( 'sell_media_add_to_cart_fields', $post_id, $attachment_id ); ?>
			<?php sell_media_plugin_credit(); ?>

		</div><!-- .sell-media-quick-view-content-inner -->
	</div><!-- .sell-media-quick-view-content -->
	<?php else : ?>
		<p class="sell-media-item-password-protected">
			<?php echo wp_kses( sprintf( __( 'This item is password protected. %s Click here to enter password. %s', 'sell_media' ), '<a href="' . esc_url( get_permalink( $post_id ) ) .'">', '</a>' ), ['a' => ['href' => true, 'target' => true]] ); ?>
		</p>
	<?php endif; ?>
	<?php do_action( 'sell_media_after_cart_content', $post_id, $attachment_id, $location ); ?>

</div><!-- .sell-media-quick-view-container -->
<?php
$cart_markup = ob_get_contents();
ob_end_clean();

echo wp_kses( apply_filters( 'sell_media_cart_output', $cart_markup, $post_id, $attachment_id, $location ), array(
	'div' => array('class' => true, 'id' => true, 'data-*' => true),
	'span' => array('class' => true, 'id' => true, 'data-*' => true),
	'a' => array('class' => true, 'id' => true, 'data-*' => true, 'href' => true, 'target' => true),
	'img' => array('class' => true, 'id' => true, 'data-*' => true, 'src' => true, 'height' => true, 'width' => true),
) );