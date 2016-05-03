<?php

/**
 * Template for Cart dialog
 */

$post_id        = $_POST['product_id'];
$attachment_id  = $_POST['attachment_id'];
$location       = isset( $_POST['location'] ) ? $_POST['location'] : '';
$image_id       = ( sell_media_has_multiple_attachments( $post_id ) ) ? $attachment_id : $post_id;

ob_start();
?>
<div class="sell-media-quick-view-container">
    <?php if( !post_password_required( $post_id ) ): ?>
    <div class="sell-media-quick-view-image">
        <?php echo apply_filters( 'sell_media_quick_view_post_thumbnail', sell_media_item_icon( $image_id, 'large', false ), $post_id ); ?>
    </div>
    
    <div class="sell-media-quick-view-content">
        <div class="sell-media-quick-view-content-inner">

            <h6><a href="<?php echo get_permalink( $post_id ); ?>" <?php echo sell_media_link_attributes( $post_id ); ?>><?php echo get_the_title( $post_id ); ?><?php if ( sell_media_has_multiple_attachments( $post_id ) ) echo ', ' . $attachment_id; ?></a></h6>
            <?php do_action( 'sell_media_add_to_cart_fields', $post_id, $attachment_id ); ?>
            <?php sell_media_plugin_credit(); ?>

        </div><!-- .sell-media-quick-view-content-inner -->
    </div><!-- .sell-media-quick-view-content -->
    <?php else: ?>
        <p>
            <?php printf( __( 'This item is password protected. %s Click here to enter password. %s', 'sell_media' ), '<a href="'.esc_url( get_permalink( $post_id ) ).'">', '</a>' ); ?>
        </p>
    <?php endif; ?>
    <?php do_action( 'sell_media_after_cart_content', $post_id, $attachment_id, $location ); ?>

</div><!-- .sell-media-quick-view-container -->
<?php
$cart_markup = ob_get_contents();
ob_end_clean();

echo apply_filters( 'sell_media_cart_output', $cart_markup, $post_id, $attachment_id, $location );