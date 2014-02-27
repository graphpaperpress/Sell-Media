<?php

/**
 * Template for Cart dialog
 */

/**
 * If this attachment has a price use it, if not
 * fall back on the default price set in the
 * plugin settings
 */
wp_enqueue_script( 'simpleCart', plugin_dir_url( __FILE__ ) . 'js/simpleCart.min.js', array( 'jquery' ), SELL_MEDIA_VERSION );
$settings = sell_media_get_plugin_options();
$attachment_id = get_post_meta( $_POST['product_id'], '_sell_media_attachment_id', true );
$licenses = wp_get_post_terms( $_POST['product_id'], 'licenses' );
if ( $licenses ) {
	$term_id = $licenses[0]->term_id;
} else {
	$term_id = null;
}
?>
<div class="main-container simpleCart_shelfItem">
    <span class="close">&times;</span>
    <div class="content">
        <div class="left">
            <div class="image-container clearfix">
                <?php sell_media_item_icon( $attachment_id, 'medium' ); ?>
                <h3 class="item_name"><?php print get_the_title( $_POST['product_id'] ); ?></h3>
            </div>
        </div>
        <div class="right">
        	<?php //do_action( 'sell_media_above_item_form' ); ?>
            <?php //do_action( 'sell_media_cart_above_size' ); ?>
            <fieldset>
            	<legend><?php _e( 'Size', 'sell_media' ); ?></legend>
                <select id="sell_media_item_size" class="sum item_size">
                	<option selected="selected" value="" data-price="0" data-qty="0">-- <?php _e( 'Select a size', 'sell_media'); ?> --</option>
                    <?php
                        $p = new SellMediaProducts;
                        $prices = $p->get_prices( $_POST['product_id'] );
                        if ( $prices ) foreach ( $prices as $k => $v ) {
                            if ( $p->mimetype_is_image( get_post_meta( $_POST['product_id'], '_sell_media_attachment_id', true ) ) ){
                                $name = $v['name'] . ' (' . $v['width'] . ' x ' . $v['height'] . ')';
                            } else {
                                $name = $v['name'];
                            }
                            echo '<option value="' . $name . '" data-id="' . $v['id'] . '" data-price="' . $v['price'] . '" data-qty="1" data-size="' . $v['width'] . ' x ' . $v['height'] . '">' . $name  . ': ' . sell_media_get_currency_symbol() . sprintf( '%0.2f', $v['price'] ) . '</option>';
                        }
                    ?>
                </select>
                <span class="item_pgroup hide"></span>
            </fieldset>
			<?php //do_action( 'sell_media_cart_below_size' ); ?>
			<?php //do_action( 'sell_media_cart_above_licenses' ); ?>
			<?php if ( count( $licenses ) > 1 ) : ?>
				<fieldset>
					<legend><?php _e( 'License', 'sell_media' ); ?></legend>
					<select id="sell_media_item_license" class="sum item_license" disabled>
						<option value="" data-price="0" title="Select a license to learn more about each license.">-- <?php _e( 'Select a license', 'sell_media'); ?> --</option>
						<?php sell_media_build_options( array( 'post_id' => $_POST['product_id'], 'taxonomy' => 'licenses', 'type'=>'select' ) ); ?>
					</select>
					<span class="item_usage hide"></span>
					<div class="license_desc sell-media-tooltip" data-tooltip="<?php _e( 'Select a license to learn more about each license.', 'sell_media' ); ?>"> <?php _e( 'View Details', 'sell_media' ); ?></div>
				</fieldset>
			<?php else : ?>
                <input type="text" value="0" class="item_license hide" />
                <input type="text" value="0" class="item_usage hide" />
				<?php if ( ! empty( $term_id ) ) : ?>
					<div id="sell_media_item_license" data-id="<?php print $term_id; ?>" data-value="<?php print $licenses[0]->slug; ?>" data-taxonomy="licenses" data-name="<?php print $licenses[0]->name; ?>" data-price="<?php print str_replace('%', '', sell_media_get_term_meta( $licenses[0]->term_id, 'markup', true ) ); ?>">
						<?php _e( 'License', 'sell_media'); ?>: <span class="item_usage"><?php print $licenses[0]->name; ?></span>
					</div>
					<?php if ( ! empty( $licenses[0]->description ) ) : ?>
						<div class="license_desc sell-media-tooltip" data-tooltip="<?php print esc_attr( $licenses[0]->description ); ?>"><?php _e( 'View Details', 'sell_media' ); ?></div>
					<?php endif; ?>
                <?php endif; ?>
			<?php endif; ?>
			<?php //do_action( 'sell_media_cart_below_licenses' ); ?>
			<span class="item_number hide"><?php echo $_POST['product_id']; ?></span>
            <div class="total-container group">
				<strong><?php _e( 'Total', 'sell_media' ); ?>:</strong> <span class="price-container">$<span id="total" class="item_price">0</span></span>
			</div>
			<div class="button-container group">
				<p id="sell-media-add-to-cart"><button class="item_add sell-media-button" disabled><?php _e( 'Add to cart', 'sell_media' ); ?></button></p>
			</div>
        </div>
        <div class="sell-media-credit"><?php sell_media_plugin_credit(); ?></div>
    </div>
</div>