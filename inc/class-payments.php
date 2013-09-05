<?php

Class SellMediaPayments {

    /**
     * Retrives the total for a payment
     *
     * @param $post_id (int) The post_id for a post of post type "sell_media_payment"
     *
     * @return (string) Total with formated currency symbol
     */
    public function total( $post_id=null ){
        return sell_media_get_currency_symbol() . sprintf( '%0.2f', get_post_meta( $post_id, '_sell_media_payment_amount', true ) );
    }


    /**
     * Retrive the customer contact info associated with a payment
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

        $info = sprintf( '<p>%s: '.$contact['first_name'] . ' ' . $contact['last_name'] . '<br />
            %s: <a href="mailto:' . $contact['email'] . '">' . $contact['email'] . '</a><br />
            %s: '.$this->total( $post_id ).'</p>',
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
     *
     * @return $html (string) An html table containing the item, size, price, qty, and other usefulness
     */
    public function payment_table( $post_id=null ){

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

                    $html .= '<tr class="" valign="top">';
                    $html .= '<td class="media-icon">' . $link['thumbnail'] . '</td>';
                    $html .= '<td>'.$cart->item_size( $link['price_id'] ) . apply_filters('sell_media_payment_meta', $post_id, $link['price_id'] ) . '</td>';
                    $html .= '<td>' . sell_media_get_currency_symbol() . $price . '</td>';
                    $html .= '<td>' . $qty . '</td>';
                    $html .= '<td>' . $license . '</td>';
                    $html .= '<td class="title column-title"><input type="text" value="' . $link['url'] . '" /></td>';
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
    public function status( $post_id=null ){

        $status = get_post_status( $post_id );

        if ( $status == 'publish' )
            $status = __('Paid','sell_media');

        return ucfirst( $status );
    }
}