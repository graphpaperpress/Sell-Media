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
<div class="main-container sellMediaCart_shelfItem">
    <span class="close">&times;</span>
    <div class="content">
        <header>
            <figure><?php sell_media_item_icon( $image_id ); ?></figure>
            <figcaption><?php echo get_the_title( $post_id ); ?><?php if ( sell_media_has_multiple_attachments( $post_id ) ) echo ', ' . $attachment_id; ?></figcaption>
        </header>
        <section>
            <?php
                /*
                 * Set required cart items to be passed to gateway
                 * Any element tagged with item_* inside .sellMediaCart_shelfItem will be passed as an option/value key pair to PayPal
                 * If you want to show these items on the checkout page, edit cartColumns[] in sell_media.js
                 * We use input type="text" because using type="hidden" is unreliable
                 * The order in which these appear control the option names passed to PayPal
                 * So we must set all required tags high
                 * Otherwise, our server-side price validation fails
                 * The item_pgroup class is shared between downloads and reprints, which use different price group taxonomies
                 * Check if terms exist in these price group taxonomies to determine verified prices
                 * Some values below are set via javascript (select options on change event)
                 * .item_usage (license name to be shown on cartColumns) and .item_license are set inline with their select boxes
                 */
            ?>
            <form id="sell-media-cart-items" class="hide">
                <input class="item_number" type="text" value="<?php echo $post_id; ?>" />
                <input class="item_name" type="text" value="<?php echo get_the_title( $post_id ); ?><?php if ( sell_media_has_multiple_attachments( $post_id ) ) echo ', ' . $attachment_id; ?>" />
                <input class="item_type" type="text" value="<?php if ( $is_package ) echo 'download'; else echo apply_filters( 'sell_media_set_product_type', 'download' ); ?>" />
                <input class="item_image" type="text" value="<?php echo sell_media_item_image_src( $post_id, $attachment_id ); ?>" />
                <input class="item_pgroup" type="text" value="<?php if ( ! $has_price_group ) echo 'original'; ?>" />
                <input class="item_size" type="text" value="<?php if ( ! $has_price_group ) echo 'Original'; ?>" />
                <input class="item_usage" type="text" value="No license" />
                <input class="item_license" type="text" value="0" />
                <input class="item_attachment" type="text" value="<?php echo $attachment_id; ?>" />
            </form>

            <?php do_action( 'sell_media_above_item_form' ); ?>
            <?php do_action( 'sell_media_cart_above_size' ); ?>

            <?php if ( $is_package ) : ?>
                <p class="sell-media-package-excerpt"><?php echo sell_media_get_excerpt( $post_id ); ?></p>
                <p class="sell-media-package-excerpt-link sell-media-aligncenter"><a href="<?php echo get_permalink( $post_id ); ?>"><?php _e( 'Learn more', 'sell_media' ); ?></a></p>
            <?php endif; ?>

            <div id="sell_media_download_wrapper">
                <?php if ( ! $is_package && $has_price_group ) : ?>
                    <fieldset id="sell_media_download_size_fieldset">
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
                    <fieldset id="sell_media_download_license_fieldset">
                        <legend><?php echo apply_filters( 'sell_media_download_license_text', __( 'License', 'sell_media' ) ); ?> <span id="license_desc" class="license_desc sell-media-tooltip" data-tooltip="<?php _e( 'Select a license that most closely describes the intended use of this item. Additional license details will be displayed here after selecting a license.', 'sell_media' ); ?>"> <?php _e( '(see details)', 'sell_media' ); ?></span></legend>
                        <select id="sell_media_item_license" class="sum" required>
                            <option selected="selected" value="" data-id="" data-price="0" title="<?php _e( 'Select a license that most closely describes the intended use of this item. Additional license details will be displayed here after selecting a license.', 'sell_media' ); ?>">-- <?php _e( 'Select a license', 'sell_media'); ?> --</option>
                            <?php sell_media_build_options( array( 'post_id' => $post_id, 'taxonomy' => 'licenses', 'type'=>'select' ) ); ?>
                        </select>
                    </fieldset>
                <?php endif; ?>
            </div>

            <?php do_action( 'sell_media_cart_below_licenses' ); ?>

            <div class="total-container group">
                <strong><?php _e( 'Total', 'sell_media' ); ?>:</strong> <span class="price-container"><?php echo sell_media_get_currency_symbol(); ?><span id="total" class="item_price" data-price=<?php echo $price; ?>><?php echo $price; ?></span></span>
            </div>

            <div class="button-container group">
                <p id="sell-media-add-to-cart"><button class="item_add sell-media-button" <?php if ( ! $is_package && $has_price_group ) echo 'disabled'; ?>><?php _e( 'Add to cart', 'sell_media' ); ?></button></p>
            </div>

            <footer><?php sell_media_plugin_credit(); ?></footer>

        </section>
    </div>
</div>