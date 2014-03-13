<?php

Class SellMediaPayments {


    public function __construct(){
        add_action('wp_ajax_nopriv_sell_media_simple_cart', array( &$this, 'sell_media_simple_cart') );
        add_action('wp_ajax_sell_media_simple_cart', array( &$this, 'sell_media_simple_cart') );
    }


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
		if ( is_array( $meta ) && array_key_exists( $key, $meta ) ) {
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
		if ( is_array( $meta ) && array_key_exists( 'products', $meta ) ) {
			return maybe_unserialize( $meta['products'] );
		} else {
			return false;
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
    public function products_include_type( $post_id=null, $type=null ){
        $products = $this->get_products( $post_id );
        $types = array();
        foreach ( $products as $product ) {
            $types[] = $this->get_meta_key( $post_id, 'type' );
        }
        if ( in_array( $type, $types ) ) {
            return true;
        } else {
            return false;
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
    public function get_id( $post_type=null, $key=null, $value=null ){
        $args = array(
        'post_type' => $post_type,
        'post_status' => 'publish',
        'meta_query' => array(
            'relation' => 'AND',
                array(
                    'key' => $key,
                    'value' => $value
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
    public function get_id_from_tx( $transaction_id ){
        $args = array(
            'post_type' => 'sell_media_payment',
            'post_status' => 'publish',
            'meta_key' => '_sell_media_payment_meta',
            'posts_per_page' => -1
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
    * Get all payments of a user
    *
    * @param $email (string) The email to check
    *
    * @return (array) $purchases
    */
    public function get_user_payments( $email ){

        $purchases = array();

        $args = array(
            'post_type' => 'sell_media_payment',
            'post_status' => 'publish',
            'meta_key' => '_sell_media_payment_meta',
            'posts_per_page' => -1
        );

        $payment_query = new WP_Query( $args );
        if ( $payment_query->have_posts() ) {
            while ( $payment_query->have_posts() ) : $payment_query->the_post();
                if ( $email == $this->get_meta_key( get_the_ID(), 'email' ) ) {
                    $purchases[] = get_the_ID();
                }
            endwhile;
        }

        return $purchases;
    }


    /**
    * Get buyer first and last name for payment
    *
    * @param $post_id (string) The payment to check
    *
    * @return (string) $name
    */
    public function get_buyer_name( $post_id=null ){

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
    public function get_buyer_address( $post_id=null ){

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
    * Get all payments made at store, ever
    *
    * @param $email (string) The email to check
    *
    * @return (array) $purchases
    */
    public function get_total_payments( $post_status=null ){

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

        return number_format( ( float ) $total, 2, '.', '' );
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
            'post_status' => 'publish'
        );
        if ( ! empty( $day ) )
            $args['day'] = $day;

        $payments = get_posts( $args );

        $total = 0;
        if ( $payments ) foreach ( $payments as $payment ) {
            $subtotal = $this->get_meta_key( $payment->ID, 'total' );
            $total += $subtotal;
        }

        return $total;
    }



 	/**
	* Loop over products in payment meta and format them
	*
	* @param $post_id (int) The post_id for a post of post type "sell_media_payment"
	* @param $key = product_id, product_name, product_price, product_license, product_qty, product_subtotal
	*
	* @return html
	*/
	public function get_payment_products_formatted( $post_id=null ){
		$products = $this->get_products( $post_id );

        if ( $products ) {
    		$html = null;
    		$html .= '<table class="sell-media-products sell-media-products-payment-' . $post_id . '" border="0" cellpadding="0" cellspacing="0" width="100%">';
    		$html .= '<thead>';
    		$html .= '<tr>';
    		$html .= '<th>' . __( 'ID', 'sell_media' ) . '</th>';
    		$html .= '<th>' . __( 'Name', 'sell_media' ) . '</th>';
            $html .= '<th>' . __( 'Size', 'sell_media' ) . '</th>';
    		$html .= '<th>' . __( 'License', 'sell_media' ) . '</th>';
    		$html .= '<th class="text-center">' . __( 'Qty', 'sell_media' ) . '</th>';
            $html .= '<th class="text-center">' . __( 'Download Link', 'sell_media' ) . '</td>';
    		$html .= '<th class="sell-media-product-subtotal">' . __( 'Subtotal', 'sell_media' ) . '</th>';
    		$html .= '</tr>';
    		$html .= '</thead>';
    		$html .= '<tbody>';
    		foreach ( $products as $product ) {
                if ( ! empty( $product['id'] ) ){
    			$html .= '<tr class="sell-media-product sell-media-product-' . $product['id'] . '">';
    			$items = array( 'id', 'name', 'license', 'price', 'qty', 'total' );
    			$html .= '<td class="sell-media-product-id">';
    			if ( isset ( $product['id'] ) && ! is_array( $product['id'] ) ) $html .= $product['id'];
    			$html .= '</td>';
    			$html .= '<td class="sell-media-product-name">';
    			if ( isset ( $product['name'] ) && ! is_array( $product['name'] ) ) $html .= $product['name'];
    			$html .= '</td>';
                $html .= '<td class="sell-media-product-size">';
                if ( isset ( $product['size']['name'] ) && ! is_array( $product['size']['name'] ) ) $html .= $product['size']['name'];
                $html .= '</td>';
    			$html .= '<td class="sell-media-product-license">';
    			if ( isset ( $product['license']['name'] ) && ! is_array( $product['license']['name'] ) ) $html .= $product['license']['name'] . '<span class="license_desc">' . term_description( $product['license']['id'], 'licenses' ) . '</span>';
    			$html .= '</td>';
    			$html .= '<td class="sell-media-product-qty text-center">';
    			if ( isset ( $product['qty'] ) && ! is_array( $product['qty'] ) ) $html .= $product['qty'];
    			$html .= '</td>';
                $html .= '<td class="sell-media-product-download text-center">';
                if ( isset ( $product['license']['name'] ) && ! is_array( $product['license']['name'] ) ) $html .= '<a href="' . $this->get_download_link( $post_id, $product['id'] ) . '">' . __( 'Download', 'sell_media' ) . '</a></td>';
    			$html .= '</td>';
                $html .= '<td class="sell-media-product-total">';
    			if ( isset ( $product['total'] ) && ! is_array( $product['total'] ) )
                    $html .= sell_media_get_currency_symbol() . sprintf( "%0.2f", $product['total'] );
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
    		$html .= '<td>&nbsp;</td>';
            $html .= '<td>&nbsp;</td>';
    		$html .= '<td class="sell-media-products-grandtotal">' . __( 'Total', 'sell_media' ) . ': ' . sell_media_get_currency_symbol() . $this->get_meta_key( $post_id, $key='total' ) . '</td>';
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
                    $tmp_products = array(
                        'name' => $paypal_args[ 'item_name' . $i ],
                        'id' => $paypal_args[ 'item_number' . $i ],
                        'type' => $paypal_args[ 'option_selection1_' . $i ],
                        'size' => array(
                            'name' => $paypal_args[ 'option_selection4_' . $i ],
                            'id' => $paypal_args[ 'option_selection3_' . $i ],
                            'amount' => $paypal_args[ 'mc_gross_' . $i ],
                            'description' => null
                            ),
                        'license' => array(
                            'name' => $paypal_args[ 'option_selection5_' . $i ],
                            'id' => empty( $paypal_args[ 'option_selection6_' . $i ] ) ? null : $paypal_args[ 'option_selection6_' . $i ],
                            'description' => null,
                            'markup' => empty( $paypal_args[ 'option_selection6_' . $i ] ) ? null : str_replace( '%', '', sell_media_get_term_meta( $paypal_args[ 'option_selection6_' . $i ], 'markup', true ) )
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
                    <th>' . apply_filters( 'sell_media_download_link_label', 'Download Link' ) . '</th>
                </tr>
            </thead>';
        $html .= '<tbody>';

        foreach( $this->get_products( $post_id ) as $product ){
            $html .= '<tr class="" valign="top">';
            $html .= '<td class="media-icon">';
            $html .= '<a href="' . get_edit_post_link( $product['id'] ) . '">' . sell_media_item_icon( get_post_meta( $product['id'], '_sell_media_attachment_id', true ), 'medium', false) . '</a></td>';
            $html .= '<td>' . $product['size']['name'] . '</td>';
            $html .= '<td>' . sell_media_get_currency_symbol() . $product['size']['amount'] . '</td>';
            $html .= '<td>' . $product['qty'] . '</td>';
            $html .= '<td>' . $product['license']['name'] . '</td>';
            if ( ! empty( $product['type'] ) && 'print' == $product['type'] ){
                $html .= '<td class="title column-title">Sold a print</td>';
            } else {
                $html .= '<td class="title column-title"><input type="text" value="' . $this->get_download_link( $post_id, $product['id'] ) . '" /></td>';
            }
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

        if ( $products ) foreach( $products as $product ){
            $tmp_links[ $product['id'] ] = site_url() . '?' . http_build_query( array(
                'download' => $this->get_meta_key( $payment_id, 'transaction_id' ),
                'payment_id' => $payment_id,
                'product_id' => $product['id']
            ) );
        }

        if ( ! empty( $product_id ) && ! empty( $tmp_links[ $product_id ] ) ){
            $link = $tmp_links[ $product_id ];
        } else {
            $link = $tmp_links;
        }

        return $link;
    }


    /**
     * Build the email to be sent to the user and send the email
     * containing download links for PUBLISHED items only
     *
     * @since 0.1
     */
    public function email_receipt( $payment_id=null, $email=null ) {

        $message['from_name'] = get_bloginfo( 'name' );
        $message['from_email'] = get_option( 'admin_email' );

        $settings = sell_media_get_plugin_options();

        // send admins and buyers different email subject and body
        if ( get_option( 'admin_email' ) == $email ) {
            $message['subject'] = __( 'New sale notification', 'sell_media' );
            $message['body'] = apply_filters( 'sell_media_email_admin_receipt_message', 'Congrats! You just made a sale. An email containing download links has just been sent to your customer, so no further action is required. Here are the details:' );
        } else {
            $message['subject'] = $settings->success_email_subject;
            $message['body'] = $settings->success_email_body;
        }

        $payments_table = $this->get_payment_products_formatted( $payment_id );

        $tags = array(
            '{first_name}'      => $this->get_meta_key( $payment_id, 'first_name' ),
            '{last_name}'       => $this->get_meta_key( $payment_id, 'last_name' ),
            '{email}'           => $this->get_meta_key( $payment_id, 'email' ),
            '{download_links}'  => empty( $payments_table ) ? null : $payments_table
        );

        $message['body'] = str_replace( array_keys( $tags ), $tags, nl2br( $message['body'] ) );

        $message['headers'] = "From: " . stripslashes_deep( html_entity_decode( $message['from_name'], ENT_COMPAT, 'UTF-8' ) ) . " <{$message['from_email']}>\r\n";
        $message['headers'] .= "Reply-To: ". $message['from_email'] . "\r\n";
        $message['headers'] .= "MIME-Version: 1.0\r\n";
        $message['headers'] .= "Content-Type: text/html; charset=utf-8\r\n";

        /**
         * Check if we have additional test emails, if so we concatenate them
         */
        if ( ! empty( $settings->paypal_additional_test_email ) ){
            $email = $email . ', ' . $settings->paypal_additional_test_email;
        }

        // Send the email to buyer
        $r = wp_mail( $email, $message['subject'], $message['body'], $message['headers'] );

        return ( $r ) ? "Sent to: {$email}" : "Failed to send to: {$email}";
    }


    /**
     * Determine the price of all items in the cart that is being sent during checkout and set it.
     */
    public function sell_media_verify_callback(){

        check_ajax_referer( 'validate_cart', 'security' );

        $settings = sell_media_get_plugin_options();

        // Our PayPal settings
        $args = array(
            'currency_code' => $settings->currency,
            'business'      => $settings->paypal_email,
            'return'        => get_permalink( $settings->thanks_page ),
            'notify_url'    => site_url( '?sell_media-listener=IPN' )
            // 'tax_cart' => 0.00,
            // 'handling_cart' => 0.00
        );

        $cart = $_POST['cart'];

        // Count the number of keys that match the pattern "item_number_"
        $cart_count = count( preg_grep( '/^item_number_/', array_keys( $cart ) ) );

        $p = new SellMediaProducts;
        $verified = array();
        for( $i=0; $i <= $cart_count; $i++ ) {

            $product_id = $cart[ 'item_number_' . $i ];
            // $qty = $cart[ 'quantity_' . $i ];
            $type = $cart[ 'os0_' . $i ];
            $price_id = $cart[ 'os2_' . $i ];
            $license_id = $cart[ 'os5_' . $i ];

            // set price taxonomy if product is download or reprint
            if ( 'download' == $type )
                $taxonomy = 'price-group';
            else
                $taxonomy = 'reprints-price-group';

            // download with assigned license
            if ( ! empty( $license_id ) || $license_id != "undefined" ) {
                $cart['amount_' . $i ] = $p->markup_amount(
                    $product_id,
                    $price_id,
                    $license_id
                    ) + $p->get_price( $product_id, $price_id );
            } else {
                // download or print without assigned license
                $cart['amount_' . $i ] = $p->get_price( $product_id, $price_id, false, $taxonomy );
            }

        }

        $verified_cart = array_merge( $cart, $verified, $args );

        wp_send_json( array( 'cart' => $verified_cart ) );
    }
}
new SellMediaPayments;