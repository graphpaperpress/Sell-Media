<?php

Class Sell_Media_Payments {

	/**
	* Get meta associated with a payment
	*
	* @param $post_id (int) The post_id for a post of post type "sell_media_payment"
	*
	* @return Array
	*/
	public function get_meta( $post_id=null ){
		$meta = get_post_meta( $post_id, '_sell_media_payment_meta', true );
		if ( ! empty ( $meta ) ){
			return $unserilaized_meta = maybe_unserialize( $meta );
		} else {
			return false;
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
	public function get_meta_key( $post_id=null, $key=null ){
		$meta = $this->get_meta( $post_id );
		if ( array_key_exists( $key, $meta ) ) {
			return $meta[$key];
		} else {
			return false;
		}
	}

	/**
	* Loop over products in payment meta
	*
	* @param $post_id (int) The post_id for a post of post type "sell_media_payment"
	* @param $key = product_id, product_name, product_price, product_license, product_qty, product_subtotal
	*
	* @return Array
	*/
	public function get_products( $post_id=null ){
		$key = 'products';
		$meta = $this->get_meta( $post_id );
		if ( array_key_exists( $key, $meta ) ) {
			$products = maybe_unserialize( $meta[$key] );
			foreach ( $products as $product ) {
				$products_array[] = $product;
			}
			return $products_array;
		} else {
			return false;
		}
	}

	/**
	* Loop over products in payment meta and format them
	*
	* @param $post_id (int) The post_id for a post of post type "sell_media_payment"
	* @param $key = product_id, product_name, product_price, product_license, product_qty, product_subtotal
	*
	* @return html
	*/
	public function get_products_formatted( $post_id=null ){
		$products = $this->get_products( $post_id );
		$html = null;
		$html .= '<table class="sell-media-products sell-media-products-payment-' . $post_id . '">';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th>' . __( 'ID', 'sell_media' ) . '</th>';
		$html .= '<th>' . __( 'Name', 'sell_media' ) . '</th>';
		$html .= '<th>' . __( 'License', 'sell_media' ) . '</th>';
		$html .= '<th>' . __( 'Price', 'sell_media' ) . '</th>';
		$html .= '<th>' . __( 'Qty', 'sell_media' ) . '</th>';
		$html .= '<th class="sell-media-product-subtotal">' . __( 'Subtotal', 'sell_media' ) . '</th>';
		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody>';
		foreach ( $products as $product ) {
			$html .= '<tr class="sell-media-product sell-media-product-' . $product['id'] . '">';
			$items = array( 'id', 'name', 'license', 'price', 'qty', 'total' );
			foreach ( $items as $item ){
				$html .= '<td class="sell-media-product-' . $item . '">';
				if ( isset ( $product[$item] ) && ! is_array( $product[$item] ) ){
					$html .= $product[$item];
				}
				$html .= '</td>';
			}

			$html .= '</tr>';
		}
		$html .= '</tbody>';
		$html .= '<tfoot>';
		$html .= '<tr>';
		$html .= '<td>&nbsp;</td>';
		$html .= '<td>&nbsp;</td>';
		$html .= '<td>&nbsp;</td>';
		$html .= '<td>&nbsp;</td>';
		$html .= '<td>&nbsp;</td>';
		$html .= '<td class="sell-media-products-grandtotal">' . __( 'Total', 'sell_media' ) . ': ' . sell_media_get_currency_symbol() . $this->get_meta_key( $post_id, $key='total' ) . '</td>';
		$html .= '</tr>';
		$html .= '</table>';
		return $html;
	}

	/*
	 * Retrieve Paypal IPN $_POST, format into _sell_media_payment_data
	 * There is a lot of useless data in Paypal's IPN
	 * This standardizes the way we store payment data
	 */
	public function paypal_copy_args( $post_id=null, $metakey=null ){
		$args = array(
			'email' => 'payer_email',
			'first_name' => 'first_name',
			'last_name' => 'last_name',
			'address_street' => 'address_street',
			'address_city' => 'address_city',
			'address_state' => 'address_state',
			'address_country_code' => 'address_country_code',
			'address_zip' => 'address_zip',
			'total' => 'mc_gross',
			'shipping' => 'mc_shipping',
			'handling' => 'mc_handling',
			'tax' => 'tax',
			'number_products' => 'num_cart_items'
		);
		$meta = get_post_meta( $post_id, $metakey, true );
		$array = maybe_unserialize( $meta );
		$payment_data = null;
		foreach ( $args as $k => $v ) {
			if ( array_key_exists( $v, $array ) ) {
				if ( $k == 'num_cart_items' ) {
					for ( $i = 1; $i <= $v; $i++ ) {
						$payment_data['name'] = 'item_name' . $i;
						$payment_data['id'] = 'item_number' . $i;
						$payment_data['size'] = 'option_selection2_' . $i;
						$payment_data['license'] = 'option_selection4_' . $i;
						$payment_data['qty'] = 'quantity' . $i;
						$payment_data['price'] = 'mc_gross_' . $i;
						$payment_data['shipping'] = 'mc_shipping' . $i;
						$payment_data['handling'] = 'mc_handling' . $i;
						$payment_data['tax'] = 'tax' . $i;
					}
				}
				$payment_data[$k] = $array[$v];
			}
		}
		return $payment_data;
	}


    /**
     * Retrieves the total for a payment
     *
     * @param $post_id (int) The post_id for a post of post type "sell_media_payment"
     *
     * @return (string) Total with formated currency symbol
     */
    static public function total( $post_id=null ){
        return sell_media_get_currency_symbol() . sprintf( '%0.2f', get_post_meta( $post_id, '_sell_media_payment_amount', true ) );
    }


    /**
     * Retrieve the customer contact info associated with a payment
     *
     * @param $post_id (int) The post_id for a post of post type "sell_media_payment"
     * @todo This should not include the total
     * @return
     */
    public function get_contact_info( $post_id=null ){
        if ( get_userdata( get_post_meta( $post_id, '_sell_media_user_id', true ) ) ){
            $edit_link = '<a href="' . get_edit_user_link( get_post_meta( $post_id, '_sell_media_user_id', true ) ) . '">Edit</a>';
        } else {
            $edit_link = null;
        }

        $contact = array(
                'first_name' => get_post_meta( $post_id, '_sell_media_payment_first_name', true ),
                'last_name' => get_post_meta( $post_id, '_sell_media_payment_last_name', true ),
                'user_edit_link' => $edit_link,
                'email' => get_post_meta( $post_id, '_sell_media_payment_user_email', true )
            );

        $info = sprintf(
            '<ul>
            <li>%s: '.$contact['first_name'] . ' ' . $contact['last_name'] . '</li>
            <li>%s: <a href="mailto:' . $contact['email'] . '">' . $contact['email'] . '</a></li>
            <li>%s: '.$this->total( $post_id ).'</li></ul>',
            __( 'Name', 'sell_media' ),
            __( 'Email', 'sell_media' ),
            __( 'Total', 'sell_media' )
            );
        return $info;
    }


    /**
     * Used to build out an HTML table for a single payment containing ALL items for that payment
     *
     * @param $post_id (int) The post_id for a post of post type "sell_media_payment"
     * @param $link (bool) Use a html hyper link or an input field to display the link
     *
     * @return $html (string) An html table containing the item, size, price, qty, and other usefulness
     */
    public function payment_table( $post_id=null, $download_link=null ){

        $links = sell_media_build_download_link( $post_id, get_post_meta( $post_id, "_sell_media_payment_user_email", true ) );
        $payment_meta = get_post_meta( $post_id, '_sell_media_payment_meta', true );
        $products = array_values( unserialize( $payment_meta['products'] ) );

        $html = null;
        if ( ! empty( $links ) ){
            $html .= '<table class="wp-list-table widefat" cellspacing="0">';
            $html .= '<thead>
                    <tr>
                        <th scope="col">' . __('Item','sell_media') . '</th>
                        <th>' . __('Size','sell_media') . '</th>
                        <th>' . __('Price','sell_media') . '</th>
                        <th>' . __('Quantity','sell_media') . '</th>
                        <th>' . __('License','sell_media') . '</th>
                        <th>' . __('Download Link','sell_media') . '</th>
                    </tr>
                </thead>';
            $html .= '<tbody>';
            $cart = New Sell_Media_Cart;
            $i = 0;

            foreach( $links as $link ){

                if ( empty( $link['qty'] ) ){
                    if ( empty( $link['license_id'] ) ){
                        $license = __('None','sell_media');
                    } else {
                        $license = get_term( $link['license_id'], 'licenses' );
                        $license = $license->name;
                    }

                    /**
                     * If we have no qty the default is 1
                     * i.e., its a download
                     */
                    if ( isset( $products[$i]['qty'] ) ){
                        $qty = $products[$i]['qty'];
                    } else {
                        $qty = 1;
                    }

                    /**
                     * Derive price from the products array
                     * or use the legacy $item_id
                     */
                    if ( isset( $products[$i]['price'] ) ){
                        $price = $products[$i]['price']['amount'];
                    } else {
                        $price = $cart->item_markup_total( $link['item_id'], $link['price_id'], $link['license_id'] );
                    }


                    if ( empty( $link['url'] ) ){
                        $tmp_download = __('N/A','sell_media');
                    } elseif ( empty( $download_link ) ){
                        $tmp_download = '<input type="text" value="' . $link['url'] . '" />';
                    } else {
                        $tmp_download = '<a href="'.$link['url'].'" target="_blank">' . get_post_field('post_title', $link['item_id']) . '</a>';
                    }


                    $html .= '<tr class="" valign="top">';
                    $html .= '<td class="media-icon"><a href="'.get_edit_post_link($link['item_id']).'">' . $link['thumbnail'] . '</a></td>';
                    $html .= '<td>'.$cart->item_size( $link['price_id'] ) . apply_filters('sell_media_payment_meta', $post_id, $link['price_id'] ) . '</td>';
                    $html .= '<td>' . sell_media_get_currency_symbol() . $price . '</td>';
                    $html .= '<td>' . $qty . '</td>';
                    $html .= '<td>' . $license . '</td>';
                    $html .= '<td class="title column-title">'.$tmp_download.'</td>';
                    $html .= '</tr>';

                }
                $i++;
            }
            $html .= '</tbody>';
            $html .= '</table>';
        }
        return $html;
    }


    /**
     * Central location to derive the payment status
     *
     * @param $post_id (int) The post_id for a post of post type "sell_media_payment"
     *
     * @return Formated payment status
     */
    static public function status( $post_id=null ){
        $status = get_post_status( $post_id );
        if ( $status == 'publish' )
            $status = __( 'Paid','sell_media' );

        return apply_filters( 'sell_media_payment_status_filter', ucfirst( $status ), $post_id );
    }

}
