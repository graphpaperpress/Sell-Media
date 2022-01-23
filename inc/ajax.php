<?php
/**
 * Ajax Functions
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add item to cart.
 */
function sell_media_add_to_cart() {
	global $sm_cart;

	if( !isset( $_POST['_wpnonce'] ) || isset( $_POST['_wpnonce'] ) && !wp_verify_nonce( $_POST['_wpnonce'], 'sell_media_add_cart_action') ) {
		echo esc_html( '0' );
		exit;
	}
	
	// Check if item number is there.
	if ( ! empty( $_POST ) && isset( $_POST['item_number'] ) ) {
		
		$qty = 1;
		$attrs = array();
		$item_number = absint( $_POST['item_number'] );

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
			$attrs['item_attachment'] = absint( $_POST['item_attachment'] );
		}

		$attrs = apply_filters( 'sell_media_cart_item_attrs', $attrs );

		if( '' != $item_number ){
			$price = floatval( $_GET['price'] );

			do_action( 'sell_media_before_add_to_cart', $item_number, $price, $qty, $attrs );

			// Add item to session.
			echo esc_html( $sm_cart->add( $item_number, $price, $qty, $attrs ) );
		}
		else{
			echo esc_html( '0' );
		}

	}
	else{
		echo esc_html( '0' );
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
			echo esc_html( $sm_cart->update( $cart_item_id, $qty ) );
		}
		else{
			echo esc_html( '0' );
		}

	}
	else{
		echo esc_html( '0' );
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
 *
 * @param  array   $param Parameters.
 * @param  boolean $output_the_value_or_return Output the value or return.
 *
 * @return array         Content and load button.
 */
function sell_media_ajax_filter_search( $param = array(), $output_the_value_or_return = true ){

	// Check if parameters are empty.
	if( empty( $param ) ){
		return false;
	}

	// Next pagination.
	$paged = ( isset( $_POST['paged'] ) and 1 <= absint( $_POST['paged'] ) ) ? absint( $_POST['paged'] )+1 : 1;

	// Arguments for query.
	$args['post_type'] = "sell_media_item";
	$args['post_status'] = "publish";
	$args['paged'] = $paged;

	// Check if tab is set.
	if ( ! isset( $_POST['tab'] ) ) {
		$_POST['tab'] = 'newest';
	}

	if( 'newest' == $_POST['tab'] ){
		$args['order'] = 'DESC';
		$args['orderby'] = 'date';
	}
	else if( 'most-popular' == $_POST['tab'] ){
		$args['order'] = 'DESC';
		$args['meta_key'] = '_sell_media_post_views_count';
		$args['orderby'] = 'meta_value_num';
	}
	else if( 'keywords' == $_POST['tab'] ){
		$args['post_type'] = 'attachment';
		$args['post_status'] = array( 'publish', 'inherit' );
		$args['post_parent__in'] = sell_media_ids();
		$args['tax_query'] = array(
							array(
								'taxonomy' => 'keywords',
								'field'    => 'id',
								'terms'    => absint( $_POST['term']),
							),
						);
	}
	else if( 'collections' == $_POST['tab'] ){
		$args['tax_query'] = array(
							array(
								'taxonomy' => 'collection',
								'field'    => 'id',
								'terms'    => absint( $_POST['term']),
							),
						);
	}

	$content = "";
	$search_query = new WP_Query( $args );

	if( $search_query->have_posts() ):
		$i = 0;

		// If its first result show container.
		if( $paged == 1 ){
			$content .= '<div class="sell_media_ajax_filter_items_container ' . apply_filters( 'sell_media_grid_item_container_class', 'sell-media-grid-item-container' ) . '">';
		}

		while ( $search_query->have_posts() ) :

			$search_query->the_post();
			$i++;
			$content .= apply_filters( 'sell_media_content_loop', get_the_ID(), $i );

		endwhile;

		// If its first result end container.
		if ( $paged == 1 ) {
			$content .= '</div><!-- .sell-media-grid-item-container -->';
		}


		$load_more = '';
		// If result is at end hide load button.
		if( $paged != $search_query->max_num_pages ){
			$classes = "";
            $classes .= apply_filters( 'sell_media_loadmore_button_classes', $classes );
			$load_more = '<div class="load-more-button"><a href="javascript:void(0);" class="' . $classes . '" data-currentpage="' . $paged . '">' . __( 'Load more', 'sell_media' ) . '</a></div>';
		}

		wp_reset_postdata();

	else:
		// No result found message.
		$content .= '<h2>' . __( 'Nothing Found', 'sell_media' ) . '</h2>';
		$content .= '<p>' . __( 'Sorry, but we couldn\'t find anything that matches your search query.', 'sell_media' ) . '</p>';
		// Nothing more to load
		$load_more = '';
	endif;

	// Final response.
	$response = array( 'content' => $content, 'load_more' => $load_more );

	if ( ! $output_the_value_or_return ){
		return $response;
	}

	wp_send_json( $response );
}

// Add ajax callback.
add_action( 'wp_ajax_sell_media_ajax_filter', 'sell_media_ajax_filter_search' );
add_action( 'wp_ajax_nopriv_sell_media_ajax_filter', 'sell_media_ajax_filter_search' );

/**
 * Ajax add to cart button.
 * @param  int $id            Item id.
 * @param  int $attachment_id Attachment id.
 * @param  string $type          Type of item.
 * @return string                Add to cart button.
 */
function sell_media_ajax_add_to_cart_button( $id = NULL, $attachment_id = NULL, $type = 'download' ){

	if( isset( $_POST['id'] ) ){
		$id = absint( $_POST['id'] );
	}

	if( isset( $_POST['id'] ) ){
		$attachment_id = absint( $_POST['attachment_id'] );
	}

	if( isset( $_POST['type'] ) ){
		$type = sanitize_text_field( $_POST['type'] );
	}

	sell_media_item_add_to_cart_button( $id, $attachment_id, null, null, true, $type );
	exit;
}

// Add ajax callback.
add_action( 'wp_ajax_sell_media_ajax_add_to_cart_button', 'sell_media_ajax_add_to_cart_button' );
add_action( 'wp_ajax_nopriv_sell_media_ajax_add_to_cart_button', 'sell_media_ajax_add_to_cart_button' );