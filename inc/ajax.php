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
	if( empty( $_POST ) )
		return false;

	$args['post_type'] = "sell_media_item";
	$args['post_status'] = "publish";

	if( 'newest' == $_POST['tab'] ){
		$args['order'] = 'DESC';
		$args['orderby'] = 'date';
	}
	else if( 'most-popular' == $_POST['tab'] ){
		$args['order'] = 'DESC';
		$args['meta_key'] = '_sell_media_post_views_count';
		$args['orderby'] = 'meta_value_num';
	}
	else if( 'most-popular' == $_POST['tab'] ){
		$args['order'] = 'DESC';
		$args['meta_key'] = '_sell_media_post_views_count';
		$args['orderby'] = 'meta_value_num';
	}
	else if( 'keywords' == $_POST['tab'] ){
		$args['tax_query'] = array(
							array(
								'taxonomy'     => 'keywords',
								'field'    => 'id',
								'terms'   => absint( $_POST['term']),
							),
						);
	}
	else if( 'collections' == $_POST['tab'] ){
		$args['tax_query'] = array(
							array(
								'taxonomy'     => 'collection',
								'field'    => 'id',
								'terms'   => absint( $_POST['term']),
							),
						);
	}
	$content = "";
	$search_query = new WP_Query( $args );

	$content .= '<div id="sell-media-archive" class="sell-media">';
	$content .= '    <div id="content" role="main">';

	if( $search_query->have_posts() ):
		$i = 0;
		$content .= '<div class="' . apply_filters( 'sell_media_grid_item_container_class', 'sell-media-grid-item-container' ) . '">';

		while( $search_query->have_posts() ):

			$search_query->the_post();
			$i++;
			$content .= apply_filters( 'sell_media_content_loop', get_the_ID(), $i );

		endwhile;

		$content .= '</div><!-- .sell-media-grid-item-container -->';
		$content .= sell_media_pagination_filter( $search_query->max_num_pages );

		wp_reset_postdata();

	else:
			$content .= '<h2>' . __( 'Nothing Found', 'sell_media' ) . '</h2>';
			$content .= '<p>' . __( 'Sorry, but we couldn\'t find anything that matches your search query.', 'sell_media' ) . '</p>';
	endif;

	$content .= '    </div>';
	$content .= '</div>';

	echo $content;
	die;
}
add_action( 'wp_ajax_sell_media_ajax_filter', 'sell_media_ajax_filter_search' );
add_action( 'wp_ajax_nopriv_sell_media_ajax_filter', 'sell_media_ajax_filter_search' );