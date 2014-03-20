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
$attachment_id = get_post_meta( $_POST['product_id'], '_sell_media_attachment_id', true );
$image = sell_media_item_image_src( $_POST['product_id'] );
if ( empty( $image ) ) {
    $mime_type = get_post_mime_type( $attachment_id );
    $image = wp_mime_type_icon( $mime_type );
}
$is_package = get_post_meta( $_POST['product_id'], '_sell_media_is_package', true );
$licenses = wp_get_post_terms( $_POST['product_id'], 'licenses' );
if ( $licenses ) {
	$term_id = $licenses[0]->term_id;
} else {
	$term_id = null;
}
?>
<div class="main-container sellMediaCart_shelfItem">
    <span class="close">&times;</span>
    <div class="content">
        <header>
            <figure><?php sell_media_item_icon( $attachment_id, 'medium' ); ?></figure>
            <figcaption><?php print get_the_title( $_POST['product_id'] ); ?></figcaption>
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
            <?php if ( $is_package ) : ?>
                <div id="sell-media-cart-items" class="hide">
                    <input class="item_number" type="text" value="<?php echo $_POST['product_id']; ?>" />
                    <input class="item_name" type="text" value="<?php print get_the_title( $_POST['product_id'] ); ?>" />
                    <input class="item_type" type="text" value="download" />
                    <input class="item_image" type="text" value="<?php echo $image; ?>" />
                    <input class="item_pgroup" type="text" value="original" />
                    <input class="item_size" type="text" value="" />
                    <input class="item_usage" type="text" value="" />
                    <input class="item_license" type="text" value="" />
                </div>
                <p class="sell-media-package-excerpt"><?php echo sell_media_get_excerpt( $_POST['product_id'] ); ?></p>
                <?php if ( ! is_singular( 'sell_media_item') ) : ?>
                    <p class="sell-media-package-excerpt-link sell-media-aligncenter"><a href="<?php echo get_permalink( $_POST['product_id'] ); ?>"><?php _e( 'Learn more', 'sell_media' ); ?></a></p>
                <?php endif; ?>
                <div class="total-container group">
                    <strong><?php _e( 'Total', 'sell_media' ); ?>:</strong> <span class="price-container">$<span id="total" class="item_price"><?php echo get_post_meta( $_POST['product_id'], 'sell_media_price', true ); ?></span></span>
                </div>
                <div class="button-container group">
                    <p id="sell-media-add-to-cart"><button class="item_add sell-media-button"><?php _e( 'Add to cart', 'sell_media' ); ?></button></p>
                </div>
        	<?php else : ?>
                <div id="sell-media-cart-items" class="hide">
                    <input class="item_number" type="text" value="<?php echo $_POST['product_id']; ?>" />
                    <input class="item_name" type="text" value="<?php print get_the_title( $_POST['product_id'] ); ?>" />
                    <input class="item_type" type="text" value="<?php echo apply_filters( 'sell_media_set_product_type', 'download' ); ?>" />
                    <input class="item_image" type="text" value="<?php echo $image; ?>" />
                    <input class="item_pgroup" type="text" value="" />
                    <input class="item_size" type="text" value="" />
                    <input class="item_usage" type="text" value="" />
                    <input class="item_license" type="text" value="" />
                </div>
                <?php do_action( 'sell_media_above_item_form' ); ?>
                <?php do_action( 'sell_media_cart_above_size' ); ?>
                <div id="sell_media_download_wrapper">
                    <fieldset id="sell_media_download_size_fieldset">
                    	<legend><?php echo apply_filters( 'sell_media_download_size_text', 'Size' ); ?></legend>
                        <select id="sell_media_item_size" class="sum">
                        	<option selected="selected" value="" data-price="0" data-qty="0">-- <?php _e( 'Select a size', 'sell_media'); ?> --</option>
                            <?php
                                $prices = Sell_Media()->products->get_prices( $_POST['product_id'] );
                                if ( $prices ) foreach ( $prices as $k => $v ) {
                                    if ( Sell_Media()->products->mimetype_is_image( get_post_meta( $_POST['product_id'], '_sell_media_attachment_id', true ) ) ){
                                        $name = $v['name'] . ' (' . $v['width'] . ' x ' . $v['height'] . ')';
                                        $dimensions = $v['width'] . ' x ' . $v['height'];
                                    } else {
                                        $name = $v['name'];
                                        $dimensions = '';
                                    }
                                    echo '<option value="' . $name . '" data-id="' . $v['id'] . '" data-price="' . $v['price'] . '" data-qty="1" data-size="' . $dimensions . '">' . $name  . ': ' . sell_media_get_currency_symbol() . sprintf( '%0.2f', $v['price'] ) . '</option>';
                                }
                            ?>
                        </select>
                    </fieldset>
        			<?php do_action( 'sell_media_cart_below_size' ); ?>
        			<?php do_action( 'sell_media_cart_above_licenses' ); ?>
        			<?php if ( count( $licenses ) > 1 ) : ?>
        				<fieldset id="sell_media_download_license_fieldset">
        					<legend><?php echo apply_filters( 'sell_media_download_license_text', 'License' ); ?> <span id="license_desc" class="license_desc sell-media-tooltip" data-tooltip="<?php _e( 'Select a license that most closely describes the intended use of this item. Additional license details will be displayed here after selecting a license.', 'sell_media' ); ?>"> <?php _e( '(see details)', 'sell_media' ); ?></span></legend>
        					<select id="sell_media_item_license" class="sum" disabled>
        						<option value="" data-price="0" title="<?php _e( 'Select a license that most closely describes the intended use of this item. Additional license details will be displayed here after selecting a license.', 'sell_media' ); ?>">-- <?php _e( 'Select a license', 'sell_media'); ?> --</option>
        						<?php sell_media_build_options( array( 'post_id' => $_POST['product_id'], 'taxonomy' => 'licenses', 'type'=>'select' ) ); ?>
        					</select>
                        </fieldset>
        			<?php elseif ( ! empty( $term_id ) ) : ?>
                        <fieldset id="sell_media_download_license_fieldset">
        					<div id="sell_media_item_license" data-id="<?php print $term_id; ?>" data-value="<?php print $licenses[0]->slug; ?>" data-taxonomy="licenses" data-name="<?php print $licenses[0]->name; ?>" data-price="<?php print str_replace('%', '', sell_media_get_term_meta( $licenses[0]->term_id, 'markup', true ) ); ?>">
        						<?php $markup = sell_media_get_term_meta( $licenses[0]->term_id, 'markup', true ); ?>
                                <?php echo apply_filters( 'sell_media_download_license_text', 'License' ); ?>: <?php print $licenses[0]->name; ?><?php if ( $markup ) : ?> (<?php $markup =print str_replace('%', '', sell_media_get_term_meta( $licenses[0]->term_id, 'markup', true ) ); ?>% markup) <?php endif; ?>
        					</div>
                        </fieldset>
        			<?php else : ?>
                        <?php // no license ?>
                    <?php endif; ?>
                </div>
    			<?php do_action( 'sell_media_cart_below_licenses' ); ?>
                <div class="total-container group">
    				<strong><?php _e( 'Total', 'sell_media' ); ?>:</strong> <span class="price-container">$<span id="total" class="item_price">0</span></span>
    			</div>
                <div class="button-container group">
                    <p id="sell-media-add-to-cart"><button class="item_add sell-media-button" disabled><?php _e( 'Add to cart', 'sell_media' ); ?></button></p>
                </div>
            <?php endif; ?>
            <footer><?php sell_media_plugin_credit(); ?></footer>
        </section>
    </div>
</div>