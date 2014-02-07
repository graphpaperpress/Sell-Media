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
$sizes_array = sell_media_image_sizes( $_POST['product_id'], false );
$licenses = wp_get_post_terms( $_POST['product_id'], 'licenses' );
if ( $licenses ) {
	$term_id = $licenses[0]->term_id;
} else {
	$term_id = null;
}
?>
<div class="main-container simpleCart_shelfItem">
	<?php do_action( 'sell_media_dialog_scripts' ); ?>
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
                <select id="sell_media_item_size" class="item_size">
                	<option selected="selected" value="" data-price="0" data-qty="0">-- <?php _e( 'Select a size', 'sell_media'); ?> --</option>
                    <?php if ( ! empty( $sizes_array ) ) : foreach( $sizes_array as $k => $v ) : ?>
                        <option value="<?php echo $k; ?>" data-price="<?php echo $v['price']; ?>" data-qty="1"><?php echo $v['name']; ?> (<?php echo $v['width'] . ' x ' . $v['height']; ?>): <?php echo sell_media_get_currency_symbol() . sprintf( '%0.2f', $v['price'] ); ?></option>
                    <?php endforeach; endif; ?>
                    <?php if ( $settings->hide_original_price !== 'yes' ) : ?>
                        <option value="sell_media_original_file" data-price="<?php sell_media_item_price( $_POST['product_id'], false ); ?>" data-qty="1">
                        <?php _e( 'Original', 'sell_media' ); ?>
                        (<?php print sell_media_original_image_size( $_POST['product_id'] ); ?>):
                        <?php sell_media_item_price( $_POST['product_id'] ); ?>
                    </option>
                <?php endif; ?>
                </select>
            </fieldset>
			<?php //do_action( 'sell_media_cart_below_size' ); ?>
			<?php //do_action( 'sell_media_cart_above_licenses' ); ?>
			<?php if ( count( $licenses ) > 1 ) : ?>
				<fieldset>
					<legend><?php _e( 'License', 'sell_media' ); ?></legend>
					<select name="License" value="License" id="sell_media_license_select" <?php if ( ! empty( $terms ) ) : ?>disabled<?php endif; ?>>
						<option value="" data-price="0" title="Select a license to learn more about each license.">-- <?php _e( 'Select a license', 'sell_media'); ?> --</option>
						<?php sell_media_build_options( array( 'post_id' => $_POST['product_id'], 'taxonomy' => 'licenses', 'type'=>'select' ) ); ?>
					</select>
					<div class="license_desc sell-media-tooltip" data-tooltip="<?php _e( 'Select a license to learn more about each license.', 'sell_media' ); ?>"> <?php _e( 'View Details', 'sell_media' ); ?></div>
				</fieldset>
			<?php else : ?>
				<?php if ( ! empty( $term_id ) ) : ?>
					<input id="sell_media_single_price" type="hidden" name="License" value="<?php print $term_id; ?>" data-price="<?php sell_media_item_price( $_POST['product_id'], $currency=false); ?>" />
					<input type="hidden" value="<?php print str_replace('%', '', sell_media_get_term_meta( $licenses[0]->term_id, 'markup', true ) ); ?>" id="sell_media_single_license_markup" />
					<div class="license_text"><?php _e( 'License', 'sell_media'); ?>: <?php print $licenses[0]->name; ?></div>
					<?php if ( ! empty( $licenses[0]->description ) ) : ?>
						<div class="license_desc sell-media-tooltip" data-tooltip="<?php print esc_attr( $licenses[0]->description ); ?>"><?php _e( 'View Details', 'sell_media' ); ?></div>
					<?php endif; ?>
				<?php endif; ?>
			<?php endif; ?>
			<?php //do_action( 'sell_media_cart_below_licenses' ); ?>
            <input type="hidden" value="" class="item_Quantity">
            <div class="total-container group">
				<strong><?php _e( 'Total', 'sell_media' ); ?>:</strong> <span class="price-container">$<span class="item_price">0</span></span>
			</div>
			<div class="button-container group">
				<div class="left">
					<p><a class="item_add sell-media-buy-button" href="javascript:;"><?php _e( 'Add to cart', 'sell_media' ); ?></a></p>
					<span class="added"></span>
				</div>
				<div class="right">
					<p>Currently in your cart</p>
					<p class="simpleCart_quantity"></span> items - <span class="simpleCart_total"></p>
					<p><a href="<?php echo get_permalink( $settings->checkout_page ); ?>" class="cart"><?php _e( 'View cart', 'sell_media' ); ?></a></p>
				</div>
			</div>
        </div>
        <div class="sell-media-credit"><?php sell_media_plugin_credit(); ?></div>
    </div>
</div>
<script>
jQuery(document).ready(function($){
    $('.item_size').change(function(){
        var item_price = $(this).children(':selected').data('price');
        var item_qty = $(this).children(':selected').data('qty');
        $('.item_price').text(item_price);
        $('.item_Quantity').text(item_qty);
    });
	simpleCart.bind('afterAdd', function(item){
		console.log(item);
		$('.added').text( item.name + " has been added to your cart!" );
		alert( item.name + " has been added to the cart!");
		exit();
	});

});
</script>