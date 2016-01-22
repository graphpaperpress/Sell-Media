<?php
/**
 * Ajax Functions
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function sell_media_add_to_cart(){

	// Initialize cart
	$cart = new SellMediaClass();

	if( !empty( $_POST ) && isset( $_POST['add_to_cart'] ) ){
		
		$qty = 1;
		$attr = array();

		if( isset( $_POST['item_license'] ) && '' != $_POST['item_license'] )
			$attr['item_license'] = sanitize_text_field( $_POST['item_license'] );

		if( isset( $_POST['item_pgroup'] ) && '' != $_POST['item_pgroup'] )
			$attr['item_pgroup'] = sanitize_text_field( $_POST['item_pgroup'] );

		if( isset( $_POST['item_name'] ) && '' != $_POST['item_name'] )
			$attr['item_name'] = sanitize_text_field( $_POST['item_name'] );

		if( isset( $_POST['item_type'] ) && '' != $_POST['item_type'] )
			$attr['item_type'] = sanitize_text_field( $_POST['item_type'] );

		if( isset( $_POST['item_size'] ) && '' != $_POST['item_size'] )
			$attr['item_size'] = sanitize_text_field( $_POST['item_size'] );

		if( isset( $_POST['item_usage'] ) && '' != $_POST['item_usage'] )
			$attr['item_usage'] = sanitize_text_field( $_POST['item_usage'] );

		if( isset( $_POST['item_attachment'] ) && '' != $_POST['item_attachment'] )
			$attr['item_attachment'] = esc_url( sanitize_text_field( $_POST['item_attachment'] ) );

		if( '' != $item_number ){
			$cart->add( $item_number, $products[$product_id]['price'], $qty, $attr );
		}
	}
	exit;
}

add_action( 'wp_ajax_sm_add_to_cart', 'sell_media_add_to_cart' );
add_action( 'wp_ajax_nopriv_sm_add_to_cart', 'sell_media_add_to_cart' );