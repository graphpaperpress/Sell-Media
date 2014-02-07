<?php

/*
 * SimpleCart js
 */
function sell_media_simpleCart_js(){

	$settings = sell_media_get_plugin_options(); ?>

	<script type="text/javascript">
		simpleCart({
			cartStyle: "table",
			checkout: {
				type: "PayPal",
				email: "<?php echo $settings->paypal_email; ?>"
			},
			currency: "<?php echo $settings->currency; ?>"
			
		});

		// callback beforeCheckout
		simpleCart.bind( 'beforeCheckout' , function( data ){
			// create pending post
			// console.log(data);
			// exit();
		});

	</script>

<?php }
add_action( 'wp_head', 'sell_media_simpleCart_js' );

/*
 * SimpleCart Checkout Shortcode
 */
function sell_media_simpleCart_checkout_shortcode(){

	echo '<div class="simpleCart_items"></div>';
	echo '<a href="javascript:;" class="simpleCart_checkout">'. __( 'Checkout', 'sell_media' ) . '</a>';

}
add_shortcode( 'sell_media_simpleCart_checkout', 'sell_media_simpleCart_checkout_shortcode' );