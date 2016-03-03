<?php
/**
 * Ajax Functions
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add item to cart.
 */
function sell_media_add_to_cart(){
	global $sm_cart;

	// Check if item number is there.
	if( !empty( $_POST ) && isset( $_POST['item_number'] ) ){
		
		$qty = 1;
		$attrs = array();
		$item_number = absint($_POST['item_number']);

		if( isset( $_POST['item_name'] ) && '' != $_POST['item_name'] ){
			$attrs['item_name'] = sanitize_text_field( $_POST['item_name'] );
		}

		if( isset( $_POST['item_type'] ) && '' != $_POST['item_type'] ){
			$attrs['item_type'] = sanitize_text_field( $_POST['item_type'] );
		}

		if( isset( $_POST['item_image'] ) && '' != $_POST['item_image'] ){
			$attrs['item_image'] = sanitize_text_field( $_POST['item_image'] );
		}

		if( isset( $_POST['item_pgroup'] ) && '' != $_POST['item_pgroup'] ){
			$attrs['item_pgroup'] = sanitize_text_field( $_POST['item_pgroup'] );
		}

		if( isset( $_POST['item_size'] ) && '' != $_POST['item_size'] ){
			$attrs['item_size'] = sanitize_text_field( $_POST['item_size'] );
		}
		
		if( isset( $_POST['item_usage'] ) && '' != $_POST['item_usage'] ){
			$attrs['item_usage'] = sanitize_text_field( $_POST['item_usage'] );
		}

		if( isset( $_POST['item_license'] ) && '' != $_POST['item_license'] ){
			$attrs['item_license'] = sanitize_text_field( $_POST['item_license'] );
		}

		if( isset( $_POST['item_attachment'] ) && '' != $_POST['item_attachment'] ){
			$attrs['item_attachment'] = absint( sanitize_text_field( $_POST['item_attachment'] ) );
		}

		$attrs = apply_filters( 'sell_media_cart_item_attrs', $attrs );

		if( '' != $item_number ){
			$price = floatval( $_GET['price'] );

			// Add item to session.
			echo $sm_cart->add( $item_number, $price, $qty, $attrs );
		}
		else{
			echo '0';
		}	

	}
	else{
		echo '0';
	}
	exit;
}

add_action( 'wp_ajax_sm_add_to_cart', 'sell_media_add_to_cart' );
add_action( 'wp_ajax_nopriv_sm_add_to_cart', 'sell_media_add_to_cart' );

/**
 * Update cart item.
 */
function sell_media_update_cart(){
	global $sm_cart;

	// Check if cart item id is there.
	if( !empty( $_POST ) && isset( $_POST['cart_item_id'] ) ){
		
		$qty = intval($_POST['qty']);
		$cart_item_id = sanitize_text_field( $_POST['cart_item_id'] );

		// Check if cart item id is empty.
		if( '' != $cart_item_id ){

			// Update cart item.
			echo $sm_cart->update( $cart_item_id, $qty );
		}
		else{
			echo '0';
		}	

	}
	else{
		echo '0';
	}
	exit;
}

add_action( 'wp_ajax_sm_update_cart', 'sell_media_update_cart' );
add_action( 'wp_ajax_nopriv_sm_update_cart', 'sell_media_update_cart' );

/**
 * Count cart qty and subtotal for the menu ajax
 */
function sell_media_cart_menu(){
	global $sm_cart;
	$data = array();
	$data['qty'] = $sm_cart->getQty();
	$data['subtotal'] = $sm_cart->getSubtotal();

	wp_send_json( $data );
}
add_action( 'wp_ajax_sell_media_cart_menu', 'sell_media_cart_menu' );
add_action( 'wp_ajax_nopriv_sell_media_cart_menu', 'sell_media_cart_menu' );


/**
 * Ajax filter search function.
 */
function sell_media_ajax_filter_search(){
	print_pre( $_POST );
	die;
}
add_action( 'wp_ajax_sell_media_ajax_filter', 'sell_media_ajax_filter_search' );
add_action( 'wp_ajax_nopriv_sell_media_ajax_filter', 'sell_media_ajax_filter_search' );