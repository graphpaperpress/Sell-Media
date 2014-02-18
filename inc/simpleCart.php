<?php

/*
 * simpleCart js
 */
function sell_media_cart_js(){

	$settings = sell_media_get_plugin_options(); ?>

	<script type="text/javascript">
	jQuery(document).ready(function($){

		function createPayment( data ){

			$.ajax({
				url: sell_media.ajaxurl,
				data: {
					'action': 'sell_media_ajax_create_payment',
					'cart_data' : data
				},
				success:function( data ) {
					// This outputs the result of the ajax request
					console.log( data );
				},
				error: function( errorThrown ){
					console.log( errorThrown );
				}
			});
		}

		simpleCart({
			cartStyle: "table",
			checkout: {
				sandbox: <?php if ( $settings->test_mode == 1 ) echo 'true'; else echo 'false'; ?>,
				type: "PayPal",
				email: "<?php echo $settings->paypal_email; ?>"
			},
			cartColumns: [
				{ view: "image", attr: "image", label: false },
				{ attr: "name", label: "Name" },
				{ attr: "size", label: "Size" },
				{ attr: "usage", label: "Usage License" },
				{ attr: "price", label: "Price", view: "currency" },
				{ view: "decrement", label: false, text: "-" },
				{ attr: "quantity", label: "Qty" },
				{ view: "increment", label: false, text: "+" },
				{ attr: "total", label: "SubTotal", view: "currency" },
				{ view: "remove", text: "Remove", label: false }
			],
			currency: "<?php echo $settings->currency; ?>",
			success: "<?php echo get_permalink( $settings->thanks_page ); ?>",
			cancel: "<?php echo get_permalink( $settings->checkout_page ); ?>",
			notify: "<?php echo site_url( '?sell_media-listener=IPN' ); ?>",
			shipping: 0 // 0 prompt & optional, 1 no prompt, 2 prompt & required

		});

		// callback beforeCheckout
		simpleCart.bind( 'beforeCheckout' , function( data ){
			// validate items and price sent to cart
			// optionally create new draft post (getting rid of this)
			// createPayment(data);
			console.log(data);
			//exit();
		});
	});
	</script>

<?php }
add_action( 'wp_head', 'sell_media_cart_js' );


/*
 * Callback to create a new pending payment
 */
function sell_media_ajax_create_payment(){

	if ( isset($_REQUEST) ) {
		$cart_data = $_REQUEST['cart_data'];

		$data = array(
			'post_title' => $cart_data[0],
			'post_status' => 'pending',
			'post_type' => 'sell_media_payment',
			'post_date' => date('Y-m-d H:i:s')
		);

		$payment_id = wp_insert_post( $data );

		die();
	}
}
add_action( 'wp_ajax_sell_media_ajax_create_payment', 'sell_media_ajax_create_payment' );

/*
 * Checkout Shortcode
 */
function sell_media_checkout_shortcode( $atts ){

	do_action( 'sell_media_checkout_before_cart' );
	$html = '<div class="simpleCart_items"></div>';
	$html .= '<div class="sell-media-totals group">';
	$html .= '<div class="subtotal"><span class="sell-media-itemize">' . __( 'Subtotal', 'sell_media' ) . ':</span> <span class="simpleCart_total"></span></div>';
	$html .= '<div class="tax"><span class="sell-media-itemize">' . __( 'Tax', 'sell_media' ) . ':</span> <span class="simpleCart_tax"></span></div>';
	$html .= '<div class="shipping"><span class="sell-media-itemize">' . __( 'Shipping', 'sell_media' ) . ':</span> <span class="simpleCart_shipping"></span></div>';
	$html .= '<div class="total sell-media-bold"><span class="sell-media-itemize">'  . __( 'Total', 'sell_media' ) . ':</span> <span class="simpleCart_grandTotal"></span></div>';
	$html .= '</div>';
	do_action( 'sell_media_checkout_registration_fields' );
	do_action( 'sell_media_checkout_after_registration_fields' );
	$html .= '<div class="sell-media-checkout-button group">';
	$html .= '<a href="javascript:;" class="simpleCart_checkout sell-media-button">'. __( 'Checkout', 'sell_media' ) . '</a>';
	do_action( 'sell_media_checkout_after_checkout_button' );
	$html .= '</div>';

	return $html;

}
add_shortcode( 'sell_media_checkout', 'sell_media_checkout_shortcode' );

/*
 * Postmeta data array key value retrieval helper
 *
 * Example: sell_media_get_post_meta_args( $post_id=28, $metakey='_paypal_args', $args=array( 'amount', 'business', 'item_name', 'item_number' ) );
 */
function sell_media_get_post_meta_args( $post_id=null, $metakey=null, $args=null ){
	$meta = get_post_meta( $post_id, $metakey, true );
	$array = maybe_unserialize( $meta );
	$payment_data = null;
	foreach ( $args as $k => $v ) {
		if ( array_key_exists( $v, $array ) ) {
			$payment_data[$k] = $array[$v];
		}
	}
	return $payment_data;
}

/*
 * Get products from payment
 * $product_arg = item_name, item_number, quantity
 */
function sell_media_get_products( $post_id=null, $metakey='_paypal_args', $product_arg='item_number' ){
	$meta = get_post_meta( $post_id, $metakey, true );
	$array = maybe_unserialize( $meta );
	// num_cart_items for _cart transactions only
	if ( array_key_exists( 'num_cart_items', $array ) ) {
		for ( $i = 1; $i <= $array['num_cart_items']; $i++ ) {
			$product = $array[$product_arg . $i];
			if ( $i > 1 && $product_arg != 'quantity' ) {
				$product .= ' ';
			}
		}
		return $product;
	// legacy: num_cart_items doesn't exist when using _xclick, so just return the product id from 'custom'
	} else {
		$product = $array['custom'];
		return $product;
	}
}

/*
 * Get products from payment
 * $product_arg = item_name, item_number, quantity
 */
function sell_media_get_paypal_args( $post_id=null, $metakey='_paypal_args', $product_arg='item_number' ){
	$meta = get_post_meta( $post_id, $metakey, true );
	$array = maybe_unserialize( $meta );
	// num_cart_items for _cart transactions only
	if ( array_key_exists( 'num_cart_items', $array ) ) {
		for ( $i = 1; $i <= $array['num_cart_items']; $i++ ) {
			$product = $array[$product_arg . $i];
			if ( $i > 1 && $product_arg != 'quantity' ) {
				$product .= ' ';
			}
		}
		return $product;
	// legacy: num_cart_items doesn't exist when using _xclick, so just return the product id from 'custom'
	} else {
		$product = $array['custom'];
		return $product;
	}
}