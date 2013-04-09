<?php

/**
 * Template for Cart dialog
 */

/**
 * If this attachment has a price use it, if not
 * fall back on the default price set in the
 * plugin settings
 */

$attachment_id = get_post_meta( $_POST['product_id'], '_sell_media_attachment_id', true );
?>
<div class="main-container">
    <span class="close">&times;</span>
    <div class="content">
        <div class="cart-target-tmp" style="display: none;"></div>
        <div class="product-target-tmp">
            <div class="left">
                <div class="image-container clearfix">
                    <?php sell_media_item_icon( $attachment_id, 'medium' ); ?>
                    <p><strong><?php print get_the_title( $_POST['product_id'] ); ?></strong></p>
                </div>
            </div>
            <div class="right">
                <?php sell_media_item_form(); ?>
            </div>
            <div class="sell-media-credit"><?php sell_media_plugin_credit(); ?></div>
        </div>
    </div>
</div>