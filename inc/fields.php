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
			<input class="item_number" name="item_number" type="text" value="<?php echo esc_attr(absint( $post_id )); ?>" />
			<input class="item_name" name="item_name" type="text" value="<?php echo esc_attr( get_the_title( $post_id ) ); ?><?php if ( sell_media_has_multiple_attachments( $post_id ) ) { esc_html_e(', ' . $attachment_id,'sell_media'); } ?>" />
			<input class="item_type" name="item_type" type="text" value="<?php echo esc_attr($type); ?>" />
			<input class="item_image" name="item_image" type="text" value="<?php echo esc_url(sell_media_item_image_src( $post_id, $attachment_id )); ?>" />
			<input class="item_pgroup" name="item_pgroup" type="text" value="<?php if ( ! $has_price_group ) esc_attr_e('original','sell_media'); ?>" />
			<input class="item_size" name="item_size" type="text" value="<?php if ( ! $has_price_group ) esc_attr_e( 'Original', 'sell_media' ); ?>" />
			<input class="item_usage" name="item_usage" type="text" value="<?php esc_attr_e( 'No license', 'sell_media' ); ?>" />
			<input class="item_license" name="item_license" type="text" value="0" />
			<input class="item_attachment" name="item_attachment" type="text" value="<?php echo esc_attr( $attachment_id ); ?>" />
			<?php wp_nonce_field('sell_media_add_cart_action'); ?>
			<?php do_action( 'sell_media_cart_add_markup_inputs' ); ?>
		</form>

		<?php do_action( 'sell_media_above_item_form' ); ?>
		<?php do_action( 'sell_media_cart_above_size' ); ?>

		<?php if ( $is_package ) : ?>
			<p class="sell-media-package-excerpt"><?php echo esc_html( sell_media_get_excerpt( $post_id ) ); ?></p>
			<p class="sell-media-package-excerpt-link sell-media-aligncenter"><a href="<?php echo esc_url(get_permalink( $post_id )); ?>"><?php esc_html_e( 'Learn more', 'sell_media' ); ?></a></p>
		<?php endif; ?>

		<div id="sell_media_download_wrapper" class="sell-media-add-to-cart-download-fields">
			<?php if ( ! $is_package && $has_price_group ) : ?>
				<fieldset id="sell_media_download_size_fieldset" class="sell-media-add-to-cart-fieldset sell-media-add-to-cart-download-fieldset">					
					<span class="sell-media-select-box sell-media-select-small">
						<select id="sell_media_item_size" class="sum sell-media-select" required>
							<option selected="selected" value="" data-id="" data-size="" data-price="0" data-qty="0"><?php esc_html_e( 'Select a size', 'sell_media'); ?></option>
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
										?>
                                        <option value="<?php echo esc_attr($name); ?>"
                                                data-id="<?php echo esc_attr($v['id']); ?>"
                                                data-price="<?php echo esc_attr( number_format( $v['price'], 2, '.', '') ); ?>"
                                                data-qty="1"
                                                data-size="<?php echo esc_attr($dimensions); ?>">
                                                <?php echo esc_html($name  . ': ' . sell_media_get_currency_symbol() . sprintf( '%0.2f', $v['price'] ) ); ?></option>
                                        <?php
									}
								}
							?>
						</select>
					</span>
				</fieldset>
			<?php else : ?>
				<input id="sell_media_item_base_price" type="hidden" value="<?php echo esc_attr( $price ); ?>" data-price="<?php echo esc_attr( $price ); ?>" data-id="original" data-size="original" />
			<?php endif; ?>

			<?php do_action( 'sell_media_cart_below_size' ); ?>
			<?php do_action( 'sell_media_cart_above_licenses' ); ?>

		</div>

		<?php do_action( 'sell_media_cart_below_licenses' ); ?>

		<div class="total-container cf">
			<strong><?php esc_html_e( 'Total', 'sell_media' ); ?>:</strong> <span class="price-container"><?php echo esc_html( sell_media_get_currency_symbol() ); ?><span id="total" class="item_price" data-price=<?php echo esc_attr( $price ); ?>><?php echo esc_html( $price ); ?></span></span>
		</div>

		<div class="button-container cf">
			<p id="sell-media-add-to-cart"><?php sell_media_item_add_to_cart_button( $post_id, $attachment_id, null, null, true, $type ); ?></p>
			<p id="sell-media-add-to-lightbox"><?php echo wp_kses(sell_media_lightbox_link( $post_id, $attachment_id), array(
			        'a' => array('href' => true, 'title' => true, 'id' => true, 'class' => true, 'data-*' => true),
			        'div' => array('class' => true),
			        'p' => array('class' => true),
                )); ?></p>
		</div>

	</div><!-- .sell-media-add-to-cart-fields -->

<?php
}
add_action( 'sell_media_add_to_cart_fields', 'sell_media_add_to_cart_fields', 10, 2 );