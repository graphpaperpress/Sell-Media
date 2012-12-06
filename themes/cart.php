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
                    <?php sell_media_item_icon( $_POST['attachment_id'], 'medium' ); ?>
                    <p><strong><?php print get_the_title( $_POST['product_id'] ); ?></strong></p>
                </div>
            </div>
            <div class="right">
                <form action="javascript://" method="POST" id="sell_media_cart_form">
                    <input type="hidden" name="AttachmentID" value="<?php print $_POST['attachment_id']; ?>" />
                    <input type="hidden" name="ProductID" value="<?php print $_POST['product_id']; ?>" />
                    <input type="hidden" name="CalculatedPrice" class="price-target" value="<?php sell_media_item_price( $_POST['product_id'], $currency=false); ?>" data-price="<?php sell_media_item_price( $_POST['product_id'], $currency=false); ?>" />
                    <input type="hidden" name="Size" id="sell_media_size_select" value="<?php print $price; ?>" />

                    <?php if ( count( wp_get_post_terms( $_POST['product_id'], 'licenses' ) ) > 1 ) : ?>
                        <fieldset>
                            <legend><?php _e( 'License' ); ?></legend>
                            <select name="License" value="License" id="sell_media_license_select">
                                <option value="" data-price="0">-- <?php _e( 'Select a License' ); ?> --</option>
                                <?php sell_media_build_options( array( 'post_id' => $_POST['product_id'], 'taxonomy' => 'licenses', 'type'=>'select' ) ); ?>
                            </select>
                        </fieldset>
                    <?php endif; ?>

                    <?php $tmp = wp_get_post_terms( $_POST['product_id'], 'licenses' ); if ( $tmp ) { $tmp[0]->name; } else { $tmp = null; } ?>
                    <?php if ( is_null( $tmp ) ) : ?>
                        <input id="sell_media_single_price" type="hidden" name="License" value="<?php print $tmp[0]->term_id; ?>" data-price="<?php sell_media_item_price( $_POST['product_id'], $currency=false); ?>" />
                    <?php endif; ?>

                    <?php do_action( 'sell_media_cart_below_licenses' ); ?>

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