<?php

/**
 * Template for Cart dialog
 */

/**
 * If this attachment has a price use it, if not
 * fall back on the default price set in the
 * plugin settings
 */
$settings = sell_media_get_plugin_options();
$post_id = $_POST['product_id'];
$attachment_id = $_POST['attachment_id'];
$location = isset( $_POST['location'] ) ? $_POST['location'] : '' ;

// check if is package
$is_package = Sell_Media()->products->is_package( $post_id );
// check if has assigned price group
$has_price_group = Sell_Media()->products->has_price_group( $post_id );
// assign price
$price = ( $has_price_group ) ? 0 : Sell_Media()->products->get_original_price( $post_id );
// assign licenses
$licenses = wp_get_post_terms( $post_id, 'licenses' );
// featured image id
$image_id = ( sell_media_has_multiple_attachments( $post_id ) ) ? $attachment_id : $post_id;

?>
<div class="quick-view-container">

    <div class="quick-view-image">
        <figure><?php sell_media_item_icon( $image_id, 'large' ); ?></figure>
    </div>
    
    <div class="quick-view-content">
        <div class="quick-view-content-inner">
            <span class="close">&times;</span>
            <h2><a href="<?php echo get_permalink( $post_id ); ?>" <?php echo sell_media_link_attributes( $post_id ); ?>><?php echo get_the_title( $post_id ); ?><?php if ( sell_media_has_multiple_attachments( $post_id ) ) echo ', ' . $attachment_id; ?></a></h2>

            <div class="quick-view-fields">

                <form id="sell-media-cart-items" class="hide">
                    <input class="item_number" name="item_number" type="text" value="<?php echo $post_id; ?>" />
                    <input class="item_name" name="item_name" type="text" value="<?php echo get_the_title( $post_id ); ?><?php if ( sell_media_has_multiple_attachments( $post_id ) ) echo ', ' . $attachment_id; ?>" />
                    <input class="item_type" name="item_type" type="text" value="<?php if ( $is_package ) echo 'download'; else echo apply_filters( 'sell_media_set_product_type', 'download' ); ?>" />
                    <input class="item_image" name="item_image" type="text" value="<?php echo sell_media_item_image_src( $post_id, $attachment_id ); ?>" />
                    <input class="item_pgroup" name="item_pgroup" type="text" value="<?php if ( ! $has_price_group ) echo 'original'; ?>" />
                    <input class="item_size" name="item_size" type="text" value="<?php if ( ! $has_price_group ) echo 'Original'; ?>" />
                    <input class="item_usage" name="item_usage" type="text" value="No license" />
                    <input class="item_license" name="item_license" type="text" value="0" />
                    <input class="item_attachment" name="item_attachment" type="text" value="<?php echo $attachment_id; ?>" />
                </form>

                <?php do_action( 'sell_media_above_item_form' ); ?>
                <?php do_action( 'sell_media_cart_above_size' ); ?>

                <?php if ( $is_package ) : ?>
                    <p class="sell-media-package-excerpt"><?php echo sell_media_get_excerpt( $post_id ); ?></p>
                    <p class="sell-media-package-excerpt-link sell-media-aligncenter"><a href="<?php echo get_permalink( $post_id ); ?>"><?php _e( 'Learn more', 'sell_media' ); ?></a></p>
                <?php endif; ?>

                <div id="sell_media_download_wrapper" class="quick-view-size-fields">
                    <?php if ( ! $is_package && $has_price_group ) : ?>
                        <fieldset id="sell_media_download_size_fieldset" class="quick-view-fieldset quick-view-size-fieldset">
                            <legend><?php echo apply_filters( 'sell_media_download_size_text', __( 'Size', 'sell_media' ) ); ?></legend>
                            <select id="sell_media_item_size" class="sum" required>
                                <option selected="selected" value="" data-id="" data-size="" data-price="0" data-qty="0">-- <?php _e( 'Select a size', 'sell_media'); ?> --</option>
                                <?php
                                    $prices = Sell_Media()->products->get_prices( $post_id, $attachment_id );
                                    if ( $prices ) foreach ( $prices as $k => $v ) {
                                        if ( wp_attachment_is_image( $attachment_id ) ) {
                                            $name = $v['name'] . ' (' . $v['width'] . ' x ' . $v['height'] . ')';
                                            $dimensions = $v['width'] . ' x ' . $v['height'];
                                        } else {
                                            $name = $v['name'];
                                            $dimensions = 'Original';
                                        }
                                        $download_sizes = Sell_Media()->images->get_downloadable_size( $post_id, $attachment_id, null, true );
                                        if ( array_key_exists( $v['id'], $download_sizes['available'] ) || "original" == $v['id'] ) {
                                            echo '<option value="' . $name . '" data-id="' . $v['id'] . '" data-price="' . number_format( $v['price'], 2, '.', '') . '" data-qty="1" data-size="' . $dimensions . '">' . $name  . ': ' . sell_media_get_currency_symbol() . sprintf( '%0.2f', $v['price'] ) . '</option>';
                                        }
                                    }
                                ?>
                            </select>
                        </fieldset>
                    <?php else : ?>
                        <input id="sell_media_item_base_price" type="hidden" value="<?php echo $price; ?>" data-price="<?php echo $price; ?>" data-id="original" data-size="original" />
                    <?php endif; ?>

                    <?php do_action( 'sell_media_cart_below_size' ); ?>
                    <?php do_action( 'sell_media_cart_above_licenses' ); ?>

                    <?php if ( count( $licenses ) > 0 ) : ?>
                        <fieldset id="sell_media_download_license_fieldset" class="quick-view-fieldset quick-view-license-fieldset">
                            <legend><?php echo apply_filters( 'sell_media_download_license_text', __( 'License', 'sell_media' ) ); ?> <span class="sell-media-tooltip license-info" data-tooltip="<?php _e( 'Select a license that most closely describes the intended use of this item. Additional license details will be displayed here after selecting a license.', 'sell_media' ); ?>">(?)</span></legend>
                            <select id="sell_media_item_license" class="sum" required>
                                <option selected="selected" value="" data-id="" data-price="0" title="<?php _e( 'Select a license that most closely describes the intended use of this item. Additional license details will be displayed here after selecting a license.', 'sell_media' ); ?>">-- <?php _e( 'Select a license', 'sell_media'); ?> --</option>
                                <?php sell_media_build_options( array( 'post_id' => $post_id, 'taxonomy' => 'licenses', 'type'=>'select' ) ); ?>
                            </select>
                        </fieldset>
                    <?php endif; ?>
                </div>

                <?php do_action( 'sell_media_cart_below_licenses' ); ?>

            </div>

            <div class="total-container group">
                <strong><?php _e( 'Total', 'sell_media' ); ?>:</strong> <span class="price-container"><?php echo sell_media_get_currency_symbol(); ?><span id="total" class="item_price" data-price=<?php echo $price; ?>><?php echo $price; ?></span></span>
            </div>

            <div class="button-container group">
                <p id="sell-media-add-to-cart"><button class="item_add sell-media-button" <?php if ( ! $is_package && $has_price_group ) echo 'disabled'; ?>><?php _e( 'Add to cart', 'sell_media' ); ?></button></p>
                <p id="sell-media-add-to-lightbox"><?php echo sell_media_lightbox_link( $post_id, $attachment_id ); ?></p>
            </div>

            <?php sell_media_plugin_credit(); ?>

        </div><!-- .quick-view-content-inner -->

    </div><!-- .quick-view-content -->

    <?php do_action( 'sell_media_after_cart_content', $post_id, $attachment_id, $location ); ?>

</div><!-- .quick-view-container -->