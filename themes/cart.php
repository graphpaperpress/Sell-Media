<?php

/**
 * Template for Cart dialog
 */

/**
 * If this attachment has a price use it, if not
 * fall back on the default price set in the
 * plugin settings
 */
$payment_settings = get_option( 'sell_media_payment_settings' );
$general_settings = get_option( 'sell_media_general_settings' );
$tmp_price = get_post_meta( $_POST['product_id'], 'sell_media_price', true );
$licenses = wp_get_post_terms( $_POST['product_id'], 'licenses' );
$attachment_id = get_post_meta( $_POST['product_id'], '_sell_media_attachment_id', true );

if ( $licenses ) {
    $term_id = $licenses[0]->term_id;
} else {
    $term_id = null;
}

if ( empty( $tmp_price ) ) {
    $price = $payment_settings['default_price'];
} else {
    $price = $tmp_price;
}
?>
<div class="main-container">
    <span class="close">&times;</span>
    <div class="content">
        <div class="cart-target-tmp" style="display: none;"></div>
        <div class="product-target-tmp">
            <?php do_action( 'sell_media_above_cart' ); ?>
            <div class="left">
                <div class="image-container clearfix">
                    <?php sell_media_item_icon( $attachment_id, 'medium' ); ?>
                    <p><strong><?php print get_the_title( $_POST['product_id'] ); ?></strong></p>
                </div>
            </div>
            <div class="right">
                <form action="javascript://" method="POST" id="sell_media_cart_form">
                    <input type="hidden" name="AttachmentID" value="<?php print $attachment_id; ?>" />
                    <input type="hidden" name="ProductID" value="<?php print $_POST['product_id']; ?>" />
                    <input type="hidden" name="CalculatedPrice" class="price-target" value="<?php sell_media_item_price( $_POST['product_id'], $currency=false); ?>" data-price="<?php sell_media_item_price( $_POST['product_id'], $currency=false); ?>" />
                    <?php if ( count( $licenses ) > 1 ) : ?>
                        <fieldset>
                            <legend><?php _e( 'License', 'sell_media' ); ?></legend>
                            <select name="License" value="License" id="sell_media_license_select">
                                <option value="" data-price="0">-- <?php _e( 'Select a License' ); ?> --</option>
                                <?php sell_media_build_options( array( 'post_id' => $_POST['product_id'], 'taxonomy' => 'licenses', 'type'=>'select' ) ); ?>
                            </select>
                        </fieldset>
                    <?php else : ?>
                        <?php if ( ! empty( $term_id ) ) : ?>
                            <input id="sell_media_single_price" type="hidden" name="License" value="<?php print $term_id; ?>" data-price="<?php sell_media_item_price( $_POST['product_id'], $currency=false); ?>" />
                            <?php _e( 'License', 'sell_media'); ?>: <?php print $licenses[0]->name; ?>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php do_action( 'sell_media_cart_below_licenses' ); ?>
                    <?php
                    $wp_upload_dir = wp_upload_dir();
                    $mime_type = wp_check_filetype( $wp_upload_dir['basedir'] . SellMedia::upload_dir . '/' . get_post_meta( $_POST['product_id'], '_sell_media_attached_file', true ) );
                    if ( in_array( $mime_type['type'], array( 'image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/tiff' ) ) ): ?>
                        <fieldset>
                            <legend><?php _e('Size', 'sell_media'); ?></legend>
                            <select id="sell_media_size_select">
                                <option value="<?php sell_media_item_price( $_POST['product_id'], false ); ?>"><?php _e( 'Original', 'sell_media' ); ?>: <?php sell_media_item_price( $_POST['product_id'] ); ?></option>
                                <?php if (get_post_meta( $_POST['product_id'], 'sell_media_small_file', true )) : ?><option value="<?php sell_media_item_price( $_POST['product_id'], false, 'small' ); ?>"><?php _e( 'Small', 'sell_media' ); ?>: <?php sell_media_item_price( $_POST['product_id'], true, 'small' ); ?></option><?php endif; ?>
                                <?php if (get_post_meta( $_POST['product_id'], 'sell_media_medium_file', true )) : ?><option value="<?php sell_media_item_price( $_POST['product_id'], false, 'medium' ); ?>"><?php _e( 'Medium', 'sell_media' ); ?>: <?php sell_media_item_price( $_POST['product_id'], true, 'medium' ); ?></option><?php endif; ?>
                                <?php if (get_post_meta( $_POST['product_id'], 'sell_media_large_file', true )) : ?><option value="<?php sell_media_item_price( $_POST['product_id'], false, 'large' ); ?>"><?php _e( 'Large', 'sell_media' ); ?>: <?php sell_media_item_price( $_POST['product_id'], true, 'large' ); ?></option><?php endif; ?>
                            </select>
                        </fieldset>
                    <?php else : ?>
                        <input type="hidden" id="sell_media_price" value="<?php print $price; ?>" />
                    <?php endif; ?>

                    <div class="total-container group">
                        <div class="left">
                            <strong><?php _e( 'Total' ); ?></strong>
                        </div>
                        <div class="right">
                            <span class="price-container"><?php print sell_media_get_currency_symbol(); ?><span id="price_target" class="price-target"><?php sell_media_item_price( $_POST['product_id'], $currency=false); ?></span></span>
                        </div>
                    </div>
                    <div class="button-container group">
                        <div class="left">
                            <?php if ( empty( $_SESSION['cart']['items']) ) : ?>
                                <span class="cart empty"><?php _e( 'Cart', 'sell_media' ); ?> (0)</span>
                            <?php else: ?>
                                <span class="cart full"><a href="<?php print get_permalink( $general_settings['checkout_page'] ); ?>" class="cart-handle"><?php _e( 'Cart', 'sell_media' ); ?> (<span class="count-container"><span class="count-target"></span></span>)</a></span>
                            <?php endif; ?>
                            <a href="<?php print get_permalink( $general_settings['checkout_page'] ); ?>" class="cart-handle" style="display: none;"><?php _e( 'Cart', 'sell_media' ); ?></a>
                        </div>
                        <div class="right">
                            <input type="submit" value="<?php _e( 'Add to Cart' ); ?>" class="sell-media-buy-button" />
                        </div>
                    </div>
                </form>
            </div>
            <?php do_action( 'sell_media_below_cart' ); ?>
        </div>
    </div>
</div>