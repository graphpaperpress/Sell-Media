<?php

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