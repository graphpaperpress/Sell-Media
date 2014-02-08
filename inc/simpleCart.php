<?php

/*
 * simpleCart js
 */
function sell_media_cart_js(){

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
add_action( 'wp_head', 'sell_media_cart_js' );
add_action( 'sell_media_cart_js', 'sell_media_cart_js' );

/*
 * Checkout Shortcode
 */
function sell_media_checkout_shortcode( $atts ){

	$html = '<div class="simpleCart_items"></div>';
	$html .= '<div class="subtotal">' . __( 'Subtotal', 'sell_media' ) . ': <span class="simpleCart_total"></span></div>';
	$html .= '<div class="tax">' . __( 'Tax', 'sell_media' ) . ': <span class="simpleCart_tax"></span></div>';
	$html .= '<div class="shipping">' . __( 'Shipping', 'sell_media' ) . ': <span class="simpleCart_shipping"></span></div>';
	$html .= '<div class="total">'  . __( 'Total', 'sell_media' ) . ': <span class="simpleCart_grandTotal"></span></div>';
	do_action( 'sell_media_checkout_after_total' );
	$html .= '<form action="' . site_url( 'wp-login.php?action=register', 'login_post') . '" class="user_new" id="user_new" method="post">';
	$html .= '<input id="user_login" name="user_login" size="30" type="text" placeholder="'. __( 'Username', 'sell_media' ) . '">';
	$html .= '<input id="user_email" name="user_email" size="30" type="text" placeholder="'. __( 'Email address', 'sell_media' ) . '">';
	do_action('register_form');
	$html .= '</form>';
	$html .= '<p>' . __( 'A password will be emailed to you.', 'sell_media' ) . '</p>';
    $html .= '<p>' . __( 'Already a user?', 'sell_media' ) . '<a href="' . site_url( 'wp-login.php', 'login_post') . '">'. __( 'Sign in', 'sell_media' ) . '</a>!</p>';
	do_action( 'sell_media_checkout_after_registration_fields' );
	$html .= '<a href="javascript:;" class="simpleCart_checkout">'. __( 'Checkout', 'sell_media' ) . '</a>';
	do_action( 'sell_media_checkout_after_checkout_button' );

	return $html;

}
add_shortcode( 'sell_media_checkout', 'sell_media_checkout_shortcode' );