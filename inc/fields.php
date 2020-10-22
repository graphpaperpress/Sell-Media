<?php
/**
 * Add to cart fields
 */

function sell_media_add_to_cart_fields( $post_id = null, $attachment_id = null ) {

	// Check if is package.
	$is_package = Sell_Media()->products->is_package( $post_id );
	// check if has assigned price group
	$has_price_group = Sell_Media()->products->has_price_group( $post_id );
	// assign price
	$price = ( $has_price_group ) ? 0 : Sell_Media()->products->get_original_price( $post_id );
	// assign licenses
	$licenses = wp_get_post_terms( $post_id, 'licenses' );
	//get original image size
	$original_image =  Sell_Media()->images->get_original_image_size( $post_id, $attachment_id );
	// assign type
	$type = ( $is_package ) ? 'download' : apply_filters( 'sell_media_set_product_type', 'download' ); ?>

	<div class="sell-media-add-to-cart-fields">

		<form id="sell-media-cart-items" class="hide">
			<input class="item_number" name="item_number" type="text" value="<?php echo absint( $post_id ); ?>" />
			<input class="item_name" name="item_name" type="text" value="<?php the_title_attribute( 'post=' . $post_id ); ?><?php if ( sell_media_has_multiple_attachments( $post_id ) ) echo ', ' . $attachment_id; ?>" />
			<input class="item_type" name="item_type" type="text" value="<?php echo esc_attr( $type ); ?>" />
			<input class="item_image" name="item_image" type="text" value="<?php echo sell_media_item_image_src( $post_id, $attachment_id ); ?>" />
			<input class="item_pgroup" name="item_pgroup" type="text" value="<?php if ( ! $has_price_group ) echo 'original'; ?>" />
			<input class="item_size" name="item_size" type="text" value="<?php if ( ! $has_price_group ) _e( 'Original', 'sell_media' ); ?>" />
			<input class="item_usage" name="item_usage" type="text" value="<?php _e( 'No license', 'sell_media' ); ?>" />
			<input class="item_license" name="item_license" type="text" value="0" />
			<input class="item_attachment" name="item_attachment" type="text" value="<?php echo $attachment_id; ?>" />
			<?php do_action( 'sell_media_cart_add_markup_inputs' ); ?>
		</form>

		<?php do_action( 'sell_media_above_item_form' ); ?>
		<?php do_action( 'sell_media_cart_above_size' ); ?>

		<?php if ( $is_package ) : ?>
			<p class="sell-media-package-excerpt"><?php echo sell_media_get_excerpt( $post_id ); ?></p>
			<p class="sell-media-package-excerpt-link sell-media-aligncenter"><a href="<?php echo get_permalink( $post_id ); ?>"><?php _e( 'Learn more', 'sell_media' ); ?></a></p>
		<?php endif; ?>

		<div id="sell_media_download_wrapper" class="sell-media-add-to-cart-download-fields">
			<?php if ( ! $is_package && $has_price_group ) : ?>
				<fieldset id="sell_media_download_size_fieldset" class="sell-media-add-to-cart-fieldset sell-media-add-to-cart-download-fieldset">
					<label for="sell_media_item_size"><?php echo apply_filters( 'sell_media_download_size_text', __( 'Size', 'sell_media' ) ); ?></label>
					<span class="sell-media-select-box sell-media-select-small">
						<select id="sell_media_item_size" class="sum sell-media-select" required>
							<option selected="selected" value="" data-id="" data-size="" data-price="0" data-qty="0"><?php _e( 'Select a size', 'sell_media'); ?></option>
							<?php
								$prices = Sell_Media()->products->get_prices( $post_id, $attachment_id );
								if ( $prices ) foreach ( $prices as $k => $v ) {
									if ( wp_attachment_is_image( $attachment_id ) ) {

										if ( $v['width'] || $v['height'] ) {
											if ( $v['width'] >= $v['height'] ) {
												$max = $v['width'];
											} else {
												$max = $v['height'];
											}
										list( $new_w, $new_h ) = wp_constrain_dimensions( $original_image['original']['width'], $original_image['original']['height'], $max, $max );
							            }

										$name = $v['name'] . ' (' . $new_w . ' x ' . $new_h . ')';
										$dimensions = $new_w . ' x ' . $new_h;
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
					</span>
				</fieldset>
			<?php else : ?>
				<input id="sell_media_item_base_price" type="hidden" value="<?php echo $price; ?>" data-price="<?php echo $price; ?>" data-id="original" data-size="original" />
			<?php endif; ?>

			<?php do_action( 'sell_media_cart_below_size' ); ?>
			<?php do_action( 'sell_media_cart_above_licenses' ); ?>

		</div>

		<?php do_action( 'sell_media_cart_below_licenses' ); ?>

		<div class="total-container cf">
			<strong><?php _e( 'Total', 'sell_media' ); ?>:</strong> <span class="price-container"><?php echo sell_media_get_currency_symbol(); ?><span id="total" class="item_price" data-price=<?php echo $price; ?>><?php echo $price; ?></span></span>
		</div>

		<div class="button-container cf">
			<p id="sell-media-add-to-cart"><?php echo sell_media_item_add_to_cart_button( $post_id, $attachment_id, null, null, true, $type ); ?></p>
			<p id="sell-media-add-to-lightbox"><?php echo sell_media_lightbox_link( $post_id, $attachment_id ); ?></p>
		</div>

	</div><!-- .sell-media-add-to-cart-fields -->

<?php
}
add_action( 'sell_media_add_to_cart_fields', 'sell_media_add_to_cart_fields', 10, 2 );
