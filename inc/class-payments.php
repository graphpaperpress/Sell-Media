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
		$meta = $this->get_meta( $post_id );
		if ( $meta) {
			if ( array_key_exists( 'products', $meta ) ) {
				return maybe_unserialize( $meta['products'] );
			}
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
			$html .= '<td class="sell-media-product-id">';
			if ( isset ( $product['id'] ) && ! is_array( $product['id'] ) ) $html .= $product['id'];
			$html .= '</td>';
			$html .= '<td class="sell-media-product-name">';
			if ( isset ( $product['name'] ) && ! is_array( $product['name'] ) ) $html .= $product['name'];
			$html .= '</td>';
			$html .= '<td class="sell-media-product-license">';
			if ( isset ( $product['license'] ) && ! is_array( $product['license'] ) ) $html .= $product['license'];
			$html .= '</td>';
			$html .= '<td class="sell-media-product-price">';
			if ( isset ( $product['price'] ) && ! is_array( $product['price'] ) ) $html .= $product['price'];
			$html .= '</td>';
			$html .= '<td class="sell-media-product-qty">';
			if ( isset ( $product['qty'] ) && ! is_array( $product['qty'] ) ) $html .= $product['qty'];
			$html .= '</td>';
			$html .= '<td class="sell-media-product-total">';
			if ( isset ( $product['total'] ) && ! is_array( $product['total'] ) ) $html .= $product['total'];
			$html .= '</td>';
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


    /**
     * Retrieve PayPal IPN $_POST, format into _sell_media_payment_data
     * There is a lot of useless data in PayPal's IPN
     * This standardizes the way we store payment data
     *
     * @param (int)$post_id The post id to a payment
     * @return Returns meta id on success false on failure
     */
    public function paypal_copy_args( $post_id=null ){

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
            'shipping' => 'mc_shipping',
            'handling' => 'mc_handling',
            'tax' => 'tax',
            'number_products' => 'num_cart_items',
            'transaction_id' => 'txn_id',
            'gateway' => 'PayPal'
        );

        $paypal_args = maybe_unserialize( get_post_meta( $post_id, '_paypal_args', true ) );
        $tmp = array();

        foreach( $keys as $k => $v ){

            // Assign our contact info
            if ( array_key_exists( $v, $paypal_args ) ){
                $tmp[ $k ] = $paypal_args[ $v ];
            } else {
                for ( $i=1; $i <= $paypal_args['num_cart_items']; $i++ ) {
                    echo sell_media_get_term_meta( $paypal_args[ 'option_selection4_' . $i ], 'markup', true );
                    $tmp_products = array(
                        'name' => $paypal_args[ 'item_name' . $i ],
                        'id' => $paypal_args[ 'item_number' . $i ],
                        'size' => array(
                            'name' => $paypal_args[ 'option_selection2_' . $i ],
                            'id' => $paypal_args[ 'option_selection3_' . $i ],
                            'amount' => $paypal_args[ 'mc_gross_' . $i ],
                            'description' => null
                            ),
                        'license' => array(
                            'name' => $paypal_args[ 'option_selection5_' . $i ],
                            'id' => empty( $paypal_args[ 'option_selection4_' . $i ] ) ? null : $paypal_args[ 'option_selection4_' . $i ],
                            'description' => null,
                            'markup' => empty( $paypal_args[ 'option_selection4_' . $i ] ) ? null : str_replace( '%', '', sell_media_get_term_meta( $paypal_args[ 'option_selection4_' . $i ], 'markup', true ) )
                            ),
                        'qty' => $paypal_args[ 'quantity' . $i ],
                        'total' => null,
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
    public function total( $post_id=null ){
        return sell_media_get_currency_symbol() . sprintf( '%0.2f', $this->get_meta_key( $post_id, 'total' ) );
    }


    /**
     * Used to build out an HTML table for a single payment containing ALL items for that payment
     *
     * @param $post_id (int) The post_id for a post of post type "sell_media_payment"
     *
     * @return $html (string) An html table containing the item, size, price, qty, and other usefulness
     */
    public function payment_table( $post_id=null ){

        $html = null;
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

        foreach( $this->get_products( $post_id ) as $product ){
            $html .= '<tr class="" valign="top">';
            $html .= '<td class="media-icon"><a href="' . get_edit_post_link( $product['id'] ) . '">' . wp_get_attachment_image( get_post_meta( $product['id'], '_sell_media_attachment_id', true ) ) . '</a></td>';
            $html .= '<td>' . $product['size']['name'] . '</td>';
            $html .= '<td>' . sell_media_get_currency_symbol() . $product['size']['amount'] . '</td>';
            $html .= '<td>' . $product['qty'] . '</td>';
            $html .= '<td>' . $product['license']['name'] . '</td>';
            $html .= '<td class="title column-title"><input type="text" value="' . $this->get_download_link( $post_id, $product['id'] ) . '" /></td>';
            $html .= '</tr>';
        }
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
    static public function status( $post_id=null ){
        $status = get_post_status( $post_id );
        if ( $status == 'publish' )
            $status = __( 'Paid','sell_media' );

        return apply_filters( 'sell_media_payment_status_filter', ucfirst( $status ), $post_id );
    }


    public function get_download_link( $payment_id=null, $product_id=null ){

        $products = $this->get_products( $payment_id );

        $tmp_links = array();

        foreach( $products as $product ){
            $tmp_links[ $product['id'] ] = site_url() . '?' . http_build_query( array(
                'download' => $this->get_meta_key( $payment_id, 'transaction_id' ),
                'payment_id' => $payment_id
            ) );
        }

        if ( ! empty( $product_id ) && ! empty( $tmp_links[ $product_id ] ) ){
            $link = $tmp_links[ $product_id ] . '&product_id=' . $product_id;
        } else {
            $link = $tmp_links;
        }

        return $link;
    }
}
