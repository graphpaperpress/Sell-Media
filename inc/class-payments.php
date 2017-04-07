<?php

/**
 * Payments Class
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SellMediaPayments {


	public function __construct() {
	}


	/**
	* Get meta associated with a payment
	*
	* @param $post_id (int) The post_id for a post of post type "sell_media_payment"
	*
	* @return Array
	*/
	public function get_meta( $post_id = null ) {
		$meta = get_post_meta( $post_id, '_sell_media_payment_meta', true );
		if ( ! empty( $meta ) ) {
			return $unserilaized_meta = maybe_unserialize( $meta );
		}
	}

	/**
	* Get specific key data associated with a payment
	*
	* @param $post_id (int) The post_id for a post of post type "sell_media_payment"
	* @param $key = first_name, last_name, email, gateway, transaction_id, products, total
	*
	* @return Array
	*/
	public function get_meta_key( $post_id = null, $key = null ) {
		$meta = $this->get_meta( $post_id );
		if ( is_array( $meta ) && array_key_exists( $key, $meta ) ) {
			return $meta[ $key ];
		}
	}

	/**
	* Loop over products in payment meta
	*
	* @param $post_id (int) The post_id for a post of post type "sell_media_payment"
	*
	* @return Array
	*/
	public function get_products( $post_id = null ) {
		$meta = $this->get_meta( $post_id );
		if ( is_array( $meta ) && array_key_exists( 'products', $meta ) ) {
			return maybe_unserialize( $meta['products'] );
		}
	}

	/**
	* Get specific product from a purchase
	*
	* @param $post_id (int) The post_id for a post of post type "sell_media_payment"
	*
	* @return Array
	*/
	public function get_product_size( $post_id = null, $product_id = null, $attachment_id = null, $size_id = null, $type = null ) {
		$products = $this->get_products( $post_id );
		if ( $products ) foreach ( $products as $product ) {
			if ( $product_id == $product['id'] && $attachment_id == $product['attachment'] && $size_id == $product['size']['id'] && $type == $product['type'] ) {
				return $product['size']['id'];
			}
		}
	}

	/**
	* Get specific product type from a purchase
	*
	* @param $post_id (int) The post_id for a post of post type "sell_media_payment"
	* @param $product_id The product_id contained in the purchase
	* @param $type The type of product to check for (download, print)
	*
	* @return Array
	*/
	public function is_product_type( $post_id = null, $product_id = null, $type = null ) {
		$products = $this->get_products( $post_id );
		if ( $products ) foreach ( $products as $product ) {
			if ( $product_id == $product['id'] ) {
				if ( array_key_exists( 'type', $product ) && $type == $product['type'] ) {
					return true;
				}
			}
		}
	}


	/**
	* Loop over products in payment meta and see if products contain a specific types
	*
	* @param $post_id (int) The post_id for a post of post type "sell_media_payment"
	* @param $type = download, print (reprints extension)
	*
	* @return (bool) true/false
	*/
	public function products_include_type( $post_id = null, $type = null ) {
		$products = $this->get_products( $post_id );
		$types = array();
		if ( $products ) foreach ( $products as $product ) {
			$types[] = $this->get_meta_key( $post_id, 'type' );
		}
		if ( in_array( $type, $types ) ) {
			return true;
		}
	}

	/**
	* Get an array of products in the cart and determine if specific types exist
	*
	* @param $products (array) and array of products to test
	* @param $type = download, print (reprints extension)
	*
	* @return (bool) true/false
	*/
	public function has_type( $products = null, $type = null ) {
		$types = array();
		foreach ( $products as $product ) {
			$types[] = $this->get_meta_key( $post_id, 'type' );
		}
		if ( in_array( $type, $types ) ) {
			return true;
		}
	}

	/**
	* Get $post_id by querying a specific meta key value
	*
	* @param $key (string) The key to check
	* @param $value = (string) The value to check for
	*
	* @return (int) $post_id
	*/
	public function get_id( $post_type = null, $key = null, $value = null ) {
		$args = array(
		'post_type' => $post_type,
		'post_status' => 'publish',
		'meta_query' => array(
			'relation' => 'AND',
				array(
					'key' => $key,
					'value' => $value,
				)
			)
		);

		$payment_query = new WP_Query( $args );
		if ( $payment_query->have_posts() ) {
			while ( $payment_query->have_posts() ) : $payment_query->the_post();
				$post_id = get_the_ID();
			endwhile;
			return $post_id;
		}
	}

	/**
	* Get $post_id by matching transaction meta key value from serialized post meta
	*
	* @param $key (string) The key to check
	* @param $value = (string) The value to check for
	*
	* @return (int) $post_id
	*/
	public function get_id_from_tx( $transaction_id ) {
		$args = array(
			'post_type' => 'sell_media_payment',
			'post_status' => 'publish',
			'meta_key' => '_sell_media_payment_meta',
			'posts_per_page' => -1,
		);

		$payment_query = new WP_Query( $args );
		if ( $payment_query->have_posts() ) {
			while ( $payment_query->have_posts() ) : $payment_query->the_post();
				if ( $transaction_id == $this->get_meta_key( get_the_ID(), 'transaction_id' ) ) {
					return get_the_ID();
				}
			endwhile;
		}
	}

	/**
	* Get total sales for each item
	*
	* @param $post_id (string) The post_id to check
	*
	* @return (int) $total
	*/
	public function get_item_sales( $post_id ) {

		$total = ( float ) 0;
		$count = 0;
		$payments = get_transient( 'sell_media_total_revenue_' . $post_id );

		if ( false === $payments || '' === $payments ) {

			$args = array(
				'mode' => 'live',
				'post_type' => 'sell_media_payment',
				'post_status' => 'publish',
				'posts_per_page' => -1,
			);
			set_transient( 'sell_media_total_revenue_' . $post_id, $payments, 1800 );
		}

		$payments = get_posts( $args );
		if ( $payments ) foreach ( $payments as $payment ) {
			$products = $this->get_products( $payment->ID );
			if ( $products ) foreach ( $products as $payment_products ) {
				if ( isset( $payment_products['id'] ) && $payment_products['id'] == $post_id ) {
					$subtotal = $payment_products['total'];
					$total += $subtotal;
					$count++;
				}
			}
		}

		return $count . ' ' .  __( 'sales totaling', 'sell_media' ) . ' ' . sell_media_get_currency_symbol() . number_format( ( float ) $total, 2, '.', '' );
	}


	/**
	* Get all payments of a user
	*
	* @param $email (string) The email to check
	*
	* @return (array) $purchases
	*/
	public function get_user_payments( $email ) {

		$purchases = array();

		$args = array(
			'post_type' => 'sell_media_payment',
			'post_status' => 'publish',
			'meta_key' => '_sell_media_payment_meta',
			'posts_per_page' => -1,
		);

		$payment_query = new WP_Query( $args );
		if ( $payment_query->have_posts() ) {
			while ( $payment_query->have_posts() ) : $payment_query->the_post();
				if ( $email == $this->get_meta_key( get_the_ID(), 'email' ) ) {
					$purchases[] = get_the_ID();
				}
			endwhile;
		}
		wp_reset_postdata();
		return $purchases;
	}


	/**
	* Get buyer first and last name for payment
	*
	* @param $post_id (string) The payment to check
	*
	* @return (string) $name
	*/
	public function get_buyer_name( $post_id = null ) {

		$first_name = $this->get_meta_key( $post_id, 'first_name' );
		$last_name = $this->get_meta_key( $post_id, 'last_name' );

		return $first_name . ' ' . $last_name;
	}


	/**
	* Get shipping address for payment
	*
	* @param $post_id (string) The payment to check
	*
	* @return (string) $address
	*/
	public function get_buyer_address( $post_id = null ) {

		$keys = array( 'address_street', 'address_city', 'address_state', 'address_country_code', 'address_zip' );
		$values = null;
		$items = count( $keys );
		$i = 0;

		foreach ( $keys as $key ) {
			$sep = null;
			if ( ++$i != $items ) $sep = ', ';
				$values .= $this->get_meta_key( $post_id, $key ) . $sep;
		}

		return $values;
	}

	/**
	 * Get the total discount
	 * @return $total
	 */
	public function get_discount_total( $post_id = null ) {

		$discount_id = $this->get_meta_key( $post_id, 'discount' );
		$discount_code_meta = unserialize( get_post_meta( $discount_id, 'sell_media_discount_code_details', true ) );
		$subtotal = 0;

		$products = $this->get_products( $post_id );
		if ( $products ) foreach ( $products as $k => $v ) {
			$product_total = $v['total'];
			$subtotal += $product_total;
		}

		// apply the discount based on type (percent or flat)
		if ( 'percent' == $discount_code_meta['type'] ) {
			$percentage = $discount_code_meta['amount'] / 100;
			$total = $subtotal * $percentage;
		} else {
			$total = $subtotal - $discount_code_meta['amount'];
		}

		return number_format( $total, 2, '.', '' );
	}


	/**
	* Get all payments made at store, ever
	*
	* @param $email (string) The email to check
	*
	* @return (array) $purchases
	*/
	public function get_total_payments( $post_status = null ) {

		$total = ( float ) 0;
		$payments = get_transient( 'sell_media_total_revenue_' . $post_status );

		if ( false === $payments || '' === $payments ) {

			$args = array(
				'mode' => 'live',
				'post_type' => 'sell_media_payment',
				'post_status' => $post_status,
				'meta_key' => '_sell_media_payment_meta',
				'posts_per_page' => -1
			);
			set_transient( 'sell_media_total_revenue_' . $post_status, $payments, 1800 );
		}

		$payments = get_posts( $args );

		if ( $payments ) foreach ( $payments as $payment ) {
			$subtotal = $this->get_meta_key( $payment->ID, 'total' );
			$total += $subtotal;
		}

		return number_format( (float) $total, 2, '.', '' );
	}


	/**
	 *  Function to print out total payments by date
	 *
	 * @access public
	 * @since 1.2
	 * @return html
	 */
	public function get_payments_by_date( $day = null, $month_num, $year ) {

		$args = array(
			'post_type' => 'sell_media_payment',
			'posts_per_page' => -1,
			'year' => $year,
			'monthnum' => $month_num,
			'post_status' => 'publish',
		);
		if ( ! empty( $day ) )
			$args['day'] = $day;

		$payments = get_posts( $args );

		$total = 0;
		if ( $payments ) foreach ( $payments as $payment ) {
			$subtotal = $this->get_meta_key( $payment->ID, 'total' );
			$total += $subtotal;
		}

		return number_format( (float) $total, 2, '.', '' );
	}


	/**
	* Loop over products in payment meta and format them
	*
	* @param $post_id (int) The post_id for a post of post type "sell_media_payment"
	*
	* @return html
	*/
	public function get_payment_products_formatted( $post_id = null, $inline_css = false ) {
		$products = $this->get_products( $post_id );
		$tax = $this->get_meta_key( $post_id, $key = 'tax' );
		$shipping = $this->get_meta_key( $post_id, $key = 'shipping' );
		$discount = $this->get_meta_key( $post_id, $key = 'discount' );
		$total = $this->get_meta_key( $post_id, $key = 'total' );
		$css = ( $inline_css ) ? 'border-bottom: 1px solid #ccc; padding: 0.5rem; text-align: left;' : '';
		$style = apply_filters( 'sell_media_products_table_style', $css );

		if ( $products ) {

			$html = null;
			$html .= '<table class="sell-media-products sell-media-products-payment-' . $post_id . '" border="0" width="100%" style="border-collapse:collapse;font-size: 10px;">';
			$html .= '<thead>';
			$html .= '<tr>';
			$html .= '<th style="' . $style . '  font-weight: bold;">' . __( 'Product', 'sell_media' ) . '</th>';
			$html .= '<th style="' . $style . '  font-weight: bold;">' . __( 'Size', 'sell_media' ) . '</th>';
			$html .= '<th style="' . $style . '  font-weight: bold;">' . __( 'License', 'sell_media' ) . '</th>';
			$html .= '<th class="text-center" style="' . $style . ' text-align: center; font-weight: bold;">' . __( 'Qty', 'sell_media' ) . '</th>';
			$html .= '<th class="sell-media-product-subtotal" style="' . $style . ' text-align: right; font-weight: bold;">' . __( 'Subtotal', 'sell_media' ) . '</th>';
			$html .= '</tr>';
			$html .= '</thead>';
			$html .= '<tbody>';

			foreach ( $products as $product ) {

			// Old purchase links didn't have attachment_id set
			// So we derive the attachment_id from the product's post_meta
			$product['attachment'] = ( ! empty( $product['attachment'] ) ) ? $product['attachment'] : sell_media_get_attachment_id( $product['id'] );
			// If license description exists, show it.
			$product['license']['desc'] = ( term_description( $product['license']['id'], 'licenses' ) ) ? '<p><span class="license_desc">' . term_description( $product['license']['id'], 'licenses' ) . '</span></p>' : '';

				if ( ! empty( $product['id'] ) ) {

					$html .= '<tr class="sell-media-product sell-media-product-' . $product['id'] . '">';
					$html .= '<td class="sell-media-product-id" style="' . $style . '">';
					$filename = wp_get_attachment_image_src( $product['attachment'], 'full' );
					$filename = basename( $filename[0] );
					if ( isset ( $product['id'] ) && ! is_array( $product['id'] ) ) {
						$html .= '<div class="sell-media-product-attr sell-media-product-attr-id">' .  $product['id'] . ' — ' . $product['name'] . '</div>';
						$html .= '<div class="sell-media-product-attr sell-media-product-attr-img" style="max-width: 100px; height: auto; overflow: hidden;"><a href="' . $this->get_download_link( $post_id, $product['id'], $product['attachment'], $product['size']['id'] ) . '">' . sell_media_item_icon( $product['attachment'], 'thumbnail', false ) . '</a></div>';
						if ( 'download' == $product['type'] ) {
							$html .= '<div class="sell-media-product-attr sell-media-product-attr-download"><a href="' . $this->get_download_link( $post_id, $product['id'], $product['attachment'], $product['size']['id'] ) . '" class="text-center" style="color: #444444; text-decoration: none; font-weight: bold;">' . __( 'Download', 'sell_media' ) . '</a></div>';
						} elseif ( 'print' == $product['type'] ) {
							$html .= apply_filters( 'sell_media_product_delivery_text', 'Your print will be mailed to you shortly.' );
						}
					}
					$html .= '</td>';
					$html .= '<td class="sell-media-product-size" style="' . $style . '">';
					if ( isset( $product['size']['name'] ) && ! is_array( $product['size']['name'] ) ) {
						$html .= '<div class="sell-media-product-attr sell-media-product-attr-size-name">' . __( 'Size ID', 'sell_media' ) . ': ' . $product['size']['name'] . '</div>';
						$product_width = get_term_meta( (int) $product['size']['id'], 'width', true );
						if ( $product_width ) {
							$html .= '<div class="sell-media-product-attr sell-media-product-attr-width">' . __( 'Max Width', 'sell_media' ) . ': ' . $product_width . 'px</div>';
						}
						$product_height = get_term_meta( (int) $product['size']['id'], 'height', true );
						if ( $product_height ) {
							$html .= '<div class="sell-media-product-attr sell-media-product-attr-height">' . __( 'Max Height', 'sell_media' ) . ': ' . $product_height . 'px</div>';
						}
					}
					$html .= '</td>';
					$html .= '<td class="sell-media-product-license" style="' . $style . '">';
					if ( isset( $product['license']['name'] ) && ! is_array( $product['license']['name'] ) ) {
						$html .= '<div class="sell-media-product-attr sell-media-product-attr-license">' . $product['license']['name'] . $product['license']['desc'] . '</div>';
					}
					$html .= '</td>';
					$html .= '<td class="sell-media-product-qty text-center" style="' . $style . ' text-align: center;">';
					if ( isset( $product['qty'] ) && ! is_array( $product['qty'] ) ) {
						$html .= '<div class="sell-media-product-attr sell-media-product-attr-qty">' . $product['qty'] . '</div>';
					}
					$html .= '</td>';
					$html .= '<td class="sell-media-product-total" style="' . $style . ' text-align: right;">';
					if ( isset( $product['total'] ) && ! is_array( $product['total'] ) ) {
						$html .= '<div class="sell-media-product-attr sell-media-product-attr-total">' . sell_media_get_currency_symbol() . sprintf( '%0.2f', $product['total'] ) . '</div>';
					}
					$html .= '</td>';
					$html .= '</tr>';
				}
			}
			$html .= '</tbody>';
			$html .= '<tfoot>';
			$html .= '<tr>';
			$html .= '<td>&nbsp;</td>';
			$html .= '<td>&nbsp;</td>';
			$html .= '<td>&nbsp;</td>';
			$html .= '<td>&nbsp;</td>';
			$html .= '<td class="sell-media-products-grandtotal" style="padding: 0.5rem; text-align: right;">';
			if ( $discount ) {
				$html .= '<p>' . __( 'DISCOUNT', 'sell_media' ) . ': -' . sell_media_get_currency_symbol() . $this->get_discount_total( $post_id ) . '</p>';
			}
			if ( $tax ) {
				$html .= '<p>' . __( 'TAX', 'sell_media' ) . ': ' . sell_media_get_currency_symbol() . number_format( $tax, 2, '.', ',' ) . '</p>';
			}
			if ( $shipping ) {
				$html .= '<p>' . __( 'SHIPPING', 'sell_media' ) . ': ' . sell_media_get_currency_symbol() . number_format( $shipping, 2, '.', ',' ) . '</p>';
			}
			do_action( 'sell_media_above_products_formatted_table_total', $post_id );
			$html .= '<strong style="border-bottom: 3px solid #ccc; padding-bottom: 5px;">' . __( 'TOTAL', 'sell_media' ) . ': ' . sell_media_get_currency_symbol() . number_format( $this->get_meta_key( $post_id, $key = 'total' ), 2, '.', ',' ) . '</strong>';
			$html .= '</td>';
			$html .= '</tr>';
			$html .= '</table>';
			do_action( 'sell_media_below_products_formatted_table', $post_id );
			return $html;
		}
	}


	/**
	 * Retrieve PayPal IPN $_POST, format into _sell_media_payment_data
	 * There is a lot of useless data in PayPal's IPN
	 * This standardizes the way we store payment data
	 *
	 * @param (int)$post_id The post id to a payment
	 * @return Returns meta id on success false on failure
	 */
	public function paypal_copy_args( $post_id = null ) {

		$paypal_args = maybe_unserialize( get_post_meta( $post_id, '_paypal_args', true ) );
		$tmp = array();

		$keys = array(
			'email' => 'payer_email',
			'first_name' => 'first_name',
			'last_name' => 'last_name',
			'address_street' => 'address_street',
			'address_city' => 'address_city',
			'address_state' => 'address_state',
			'address_country_code' => 'address_country_code',
			'address_zip' => 'address_zip',
			'total' => 'mc_gross',
			'shipping' => 'mc_handling',
			//'handling' => 'mc_handling',
			'tax' => 'tax',
			'number_products' => 'num_cart_items',
			'transaction_id' => 'txn_id',
			'gateway' => 'PayPal',
			'discount' => 'custom',
		);


		foreach ( $keys as $k => $v ) {

			// Assign our contact info
			if ( array_key_exists( $v, $paypal_args ) ) {
				$tmp[ $k ] = $paypal_args[ $v ];
			// Assign the products
			} else {
				for ( $i=1; $i <= $paypal_args['num_cart_items']; $i++ ) {
					$tmp_products = array(
						'name' => $paypal_args[ 'item_name' . $i ],
						'id' => $paypal_args[ 'item_number' . $i ],
						'attachment' => $paypal_args[ 'option_selection7_' . $i ],
						'type' => $paypal_args[ 'option_selection1_' . $i ],
						'size' => array(
							'name' => $paypal_args[ 'option_selection4_' . $i ],
							'id' => $paypal_args[ 'option_selection3_' . $i ],
							'amount' => $paypal_args[ 'mc_gross_' . $i ],
							'description' => null,
							),
						'license' => array(
							'name' => $paypal_args[ 'option_selection5_' . $i ],
							'id' => empty( $paypal_args[ 'option_selection6_' . $i ] ) ? null : $paypal_args[ 'option_selection6_' . $i ],
							'description' => null,
							'markup' => empty( $paypal_args[ 'option_selection6_' . $i ] ) ? null : str_replace( '%', '', get_term_meta( $paypal_args[ 'option_selection6_' . $i ], 'markup', true ) ),
							),
						'qty' => $paypal_args[ 'quantity' . $i ],
						'total' => $paypal_args[ 'mc_gross_' . $i ],
						'shipping' => $paypal_args[ 'mc_shipping' . $i ],
						'handling' => $paypal_args[ 'mc_handling' . $i ],
						'tax' => $paypal_args[ 'tax' . $i ],
					);
					$tmp['products'][] = $tmp_products;
				}
			}
		}
		return update_post_meta( $post_id, '_sell_media_payment_meta', $tmp );
	}


	/**
	 * Retrieves the total for a payment
	 *
	 * @param $post_id (int) The post_id for a post of post type "sell_media_payment"
	 *
	 * @return (string) Total with formated currency symbol
	 */
	public function total( $post_id = null ) {
		return sell_media_get_currency_symbol() . sprintf( '%0.2f', $this->get_meta_key( $post_id, 'total' ) );
	}


	/**
	 * Used to build out an HTML table for a single payment containing all items for that payment
	 *
	 * @param $post_id (int) The post_id for a post of post type "sell_media_payment"
	 *
	 * @return $html (string) An html table containing the item, size, price, qty, and other usefulness
	 */
	public function payment_table( $post_id = null ) {

		$html = null;
		$html .= '<table class="wp-list-table widefat" cellspacing="0">';
		$html .= '<thead>
				<tr>
					<th scope="col">' . __( 'Item', 'sell_media' ) . '</th>
					<th>' . __( 'Size', 'sell_media' ) . '</th>
					<th>' . __( 'Price', 'sell_media' ) . '</th>
					<th>' . __( 'Quantity', 'sell_media' ) . '</th>
					<th>' . __( 'License', 'sell_media' ) . '</th>
					<th>' . apply_filters( 'sell_media_download_link_label', 'Download Link' ) . '</th>
				</tr>
			</thead>';
		$html .= '<tbody>';

		$products = $this->get_products( $post_id );

		if ( $products ) {
			foreach ( $this->get_products( $post_id ) as $product ) {

				$product['attachment']      = ( ! empty( $product['attachment'] ) ) ? $product['attachment'] : null;
				$product['size']['name']    = ( ! empty( $product['size']['name'] ) ) ? $product['size']['name'] : null;
				$product['size']['id']      = ( ! empty( $product['size']['id'] ) ) ? $product['size']['id'] : null;
				$product['size']['amount']  = ( ! empty( $product['size']['amount'] ) ) ? $product['size']['amount'] : null;
				$product['license']['name'] = ( ! empty( $product['license']['name'] ) ) ? $product['license']['name'] : null;

				$image_id = ( $product['attachment'] ) ? $product['attachment'] : $product['id'];

				$html .= '<tr class="" valign="top">';
				$html .= '<td class="media-icon">';
				$html .= '<a href="' . get_edit_post_link( $product['id'] ) . '">' . sell_media_item_icon( $image_id, 'medium', false ) . '</a></td>';
				$html .= '<td>' . $product['size']['name'] . '</td>';
				$html .= '<td>' . sell_media_get_currency_symbol() . $product['size']['amount'] . '</td>';
				$html .= '<td>' . $product['qty'] . '</td>';
				$html .= '<td>' . $product['license']['name'] . '</td>';
				if ( ! empty( $product['type'] ) && 'print' == $product['type'] ) {
					$html .= '<td class="title column-title">Sold a print</td>';
				} else {
					$html .= '<td class="title column-title"><input type="text" value="' . $this->get_download_link( $post_id, $product['id'], $product['attachment'], $product['size']['id'] ) . '" /></td>';
				}
				$html .= '</tr>';
			}

		// get legacy (pre 1.8) purchase data
		} else {

			$payment_meta = get_post_meta( $post_id, '_sell_media_payment_meta' );
			if ( ! $payment_meta ) return;

			$products_legacy = isset( $payment_meta['products'] )? maybe_unserialize( $payment_meta['products'] ) : array();

			if ( $products_legacy ) foreach ( $products as $product ) {

				$html .= '<tr class="" valign="top">';
				$html .= '<td class="media-icon">';
				$html .= '<a href="' . get_edit_post_link( $product['id'] ) . '">' . sell_media_item_icon( $product['id'], 'medium', false ) . '</a></td>';
				$html .= '<td>' . $product['price']['name'] . '</td>';
				$html .= '<td>' . sell_media_get_currency_symbol() . $product['price']['amount'] . '</td>';
				$html .= '<td>' . $product['qty'] . '</td>';
				$html .= '<td>' . $product['license']['name'] . '</td>';
				$html .= '<td class="title column-title"><input type="text" value="' . $this->get_download_link( $post_id, $product['id'], $product['attachment'], $product['size']['id'] ) . '" /></td>';
				$html .= '</tr>';
			} // foreach
		} // if legacy

		$html .= '</tbody>';
		$html .= '</table>';

		return $html;
	}


	/**
	 * Central location to derive the payment status
	 *
	 * @param $post_id (int) The post_id for a post of post type "sell_media_payment"
	 *
	 * @return Formated payment status
	 */
	static public function status( $post_id = null ) {
		$status = get_post_status( $post_id );
		if ( $status == 'publish' )
			$status = __( 'Paid','sell_media' );

		return apply_filters( 'sell_media_payment_status_filter', ucfirst( $status ), $post_id );
	}


	/**
	 * Gets the download link url
	 *
	 * @param  int $payment_id
	 * @param  int $product_id
	 * @param  int $attachment_id
	 * @return url
	 */
	public function get_download_link( $payment_id = null, $product_id = null, $attachment_id = null, $size_id = null ) {

		$link = site_url() . '?' . http_build_query( array(
			'download' => $this->get_meta_key( $payment_id, 'transaction_id' ),
			'payment_id' => $payment_id,
			'product_id' => $product_id,
			'attachment_id' => $attachment_id,
			'size_id' => $size_id,
		) );

		return $link;
	}


	/**
	 * Build the email to be sent to the user and send the email
	 * containing download links for PUBLISHED items only
	 *
	 * @since 0.1
	 */
	public function email_receipt( $payment_id = null, $email = null ) {

		$settings = sell_media_get_plugin_options();
		$products = $this->get_payment_products_formatted( $payment_id, $inline_css = true );

		$message['from_name'] = get_bloginfo( 'name' );
		$message['from_email'] = $settings->from_email;

		// send admins and buyers different email subject and body
		if ( $email == $message['from_email'] ) {

			$message['subject'] = __( 'New sale notification', 'sell_media' );
			$message['body']  = apply_filters( 'sell_media_email_admin_receipt_message_intro', '<p style="margin: 10px 0;">Congrats! You just made a sale!</p>' );
			$message['body'] .= '<p style="margin: 10px 0;">' . __( 'Customer', 'sell_media' ) . ': ' . $this->get_buyer_name( $payment_id ) . '</p>';
			$message['body'] .= '<p style="margin: 10px 0;">' . __( 'Address', 'sell_media' ) . ': ' . $this->get_buyer_address( $payment_id ) . '</p>';
			$message['body'] .= '<p style="margin: 10px 0;">' . __( 'Email', 'sell_media' ) . ': ' . $this->get_meta_key( $payment_id, 'email' ) . '</p>';
			$message['body'] .= apply_filters( 'sell_media_email_admin_receipt_message', '<p style="margin: 10px 0;">An email containing download links has just been sent to your customer, so no further action is required. Here are the details that the customer received:</p>' );
			$message['body'] .= $products;

		} else {

			$message['subject'] = $settings->success_email_subject;
			$message['body'] = $settings->success_email_body;
			$tags = array(
				'{first_name}'      => $this->get_meta_key( $payment_id, 'first_name' ),
				'{last_name}'       => $this->get_meta_key( $payment_id, 'last_name' ),
				'{email}'           => $this->get_meta_key( $payment_id, 'email' ),
				'{download_links}'  => empty( $products ) ? null : $products,
				'{payment_id}'      => $payment_id,
			);
			$message['body'] = str_replace( array_keys( $tags ), $tags, nl2br( $message['body'] ) );

		}

		$message['headers'] = "From: " . stripslashes_deep( html_entity_decode( $message['from_name'], ENT_COMPAT, 'UTF-8' ) ) . " <{$message['from_email']}>\r\n";
		$message['headers'] .= "Reply-To: ". $message['from_email'] . "\r\n";
		$message['headers'] .= "MIME-Version: 1.0\r\n";
		$message['headers'] .= "Content-Type: text/html; charset=utf-8\r\n";

		/**
		 * Check if we have additional test emails, if so we concatenate them
		 */
		if ( ! empty( $settings->paypal_additional_test_email ) ) {
			$email = $email . ', ' . $settings->paypal_additional_test_email;
		}

		// Send the email to buyer
		$r = wp_mail( $email, $message['subject'], html_entity_decode( $message['body'] ), $message['headers'] );

		return ( $r ) ? "Sent to: {$email}" : "Failed to send to: {$email}";
	}

}
new SellMediaPayments;
