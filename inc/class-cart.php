<?php

/**
 * This class acts as a wrapper for all methods related to manipulating
 * the cart, i.e. getting prices, markup values, etc.
 *
 * @author Zane M. Kolnik
 */
Class SellMediaCart {

    public function __construct(){

        add_action( 'wp_ajax_nopriv_sell_media_check_email', array( &$this, 'check_email' ) );
        add_action( 'wp_ajax_sell_media_check_email', array( &$this, 'check_email' ) );

        add_shortcode( 'sell_media_checkout', array( &$this, 'checkout_shortcode' ) );
    }


    /**
     * Derive price of an item via post meta
     * else derive from the price group
     * else use the default price from settings
     *
     * @param $post_id (int) The post_id to a sell_media_item
     * @param $price_id (int) One of the following: 'term_id' (of price-group taxonomy), 'sell_media_original_file', 'default_price'
     *
     * @return formated price without the currency symbol
     */
    public function item_price( $post_id=null, $price_id=null ){

        $settings = sell_media_get_plugin_options();
        $custom_price = get_post_meta( $post_id, 'sell_media_price', true );

        if ( $price_id == 'sell_media_original_file' && ! empty( $custom_price ) ){
            $item_price = $custom_price;
        } elseif ( $price_id == 'sell_media_original_file' && empty( $custom_price ) ){
            $item_price = $settings->default_price;
        } elseif ( $price_id == 'default_price' && empty( $custom_price ) ){
            $item_price = $settings->default_price;
        } elseif ( $price_id == 'default_price' && ! empty( $custom_price ) ) {
            $item_price = $custom_price;
        } else {
            $item_price = sell_media_get_term_meta( $price_id, 'price', true );
        }

        $filtered_price = apply_filters( 'sell_media_filtered_price', $price_id );
        if ( $filtered_price != $price_id ){
            $item_price = $filtered_price;
        }

        return sprintf('%0.2f', $item_price);
    }


    /**
     * Determine the price of an item using the item price AND markup value
     *
     * @param $post_id (int) Post ID of sell media item
     * @param $price_id (int) Term ID of price-group taxonomy
     * @param $license_id (int) Term ID of licenses taxonomy
     *
     * @return price of an item including the markup amount with no currency symbol
     */
    public function item_markup_total( $post_id=null, $price_id=null, $license_id=null ){
        return sprintf('%0.2f', $this->item_markup_amount( $post_id, $price_id, $license_id ) + $this->item_price( $post_id, $price_id ) );
    }


    /**
     * Determine the markup amount.
     *
     * @param $post_id (int) Post ID for a sell media item
     * @param $price_id (int) The term_id for a price of the price-group taxonomy
     * @param $license_id (int) The term_id for a price of the licenses taxonomy
     *
     * @return (float) returns the full markup amount without currency symbol
     */
    public function item_markup_amount( $post_id=null, $price_id=null, $license_id=null ){

        $price = $this->item_price( $post_id, $price_id );

        if ( empty( $license_id ) ){
            $markup_amount = 0;
        } else {
            $markup_percent = str_replace( "%", "", sell_media_get_term_meta( $license_id, 'markup', true ) );
            $markup_amount = ( $markup_percent / 100 ) * $price;
        }

        return $markup_amount;
    }


    /**
     * Determine the size name of an item based
     *
     * @param $term_id (int) The term_id for a term from the price-group taxonomy
     * @todo isn't this already done? it should be part of the item class
     * @return $size (string) The text to be displayed as the size of the item
     */
    public function item_size( $term_id=null ){
        if ( $term_id == 'sell_media_original_file' ){
            $size = __('Original','sell_media');
        } elseif( $term_id == 'default_price' ) {
            $size = __('N/A','sell_media');
        } else {
            $size_obj = get_term_by('id', $term_id, 'price-group' );
            // if for some reason this term does not exists
            if ( $size_obj )
                $size = $size_obj->name;
            else
                $size = null;
        }
        return apply_filters( 'sell_media_cart_size', $size, $term_id );
    }


    /**
     * Determine the total cost for all items in the users current shopping
     * cart.
     *
     * @param $items (array) An array of items
     *     @option integer "id" Post ID
     *     @option string "name" Post name
     *     @option array "price"
     *          @option "id" Taxonomy (price group) ID
     *          @option "name" name of item
     *          @option "description" Description of item
     *          @option "amount" Price of item, note including markup
     *     @option integer "qty" The quantity
     *     @option float "total" The total dollar amount
     *     @option array "license"
     *          @option "id" Taxonomy ID
     *          @option "name" Name of the taxonomy
     *          @option "markup" The markup as a %
     *
     * @return $amount (string) The updated amount the customer is charged
     */
    public function get_subtotal( $items=array() ){
        $amount = 0;

        if ( ! empty( $items ) ){
            foreach ( $items as $item ){
                $qty = ( empty( $item['qty'] ) ) ? 1 : $item['qty'];
                if ( empty( $item['license'] ) ){
                    $price = $item['price']['amount'] * $qty;
                } else {
                    $price = $this->item_markup_total( $item['id'], $item['price']['id'], $item['license']['id'] ) * $qty;
                }
                $amount += $price;
            }
        }
        return apply_filters( 'sell_media_subtotal', sprintf( "%0.2f", max( $amount, 0 ) ) );
    }


    /**
     * Determines the quantity for all items in the customers cart
     *
     * @param $items (array) An array of items to total
     * @return $quantity (string) The total number of items in the users cart
     */
    public function get_quantity( $items=array() ){
        $quantity = 0;
        if ( ! empty( $items ) ){
            foreach ( $items as $item ){
                $quantity = $quantity + $item['qty'];
            }
        }
        return $quantity;
    }

    /**
     * Takes the current shopping cart and stores it in the database
     * at time of purchase
     *
     */
    public function receipt( $items=array() ){

        // $full_cart = array();
        $tmp = array();

        // $full_cart = array(
        //     'payment_id'
        //     'total'
        //     'quantity'
        //     'shipping_handling' => 'here'
        //     );

        foreach( $items as $item ){

            if ( empty( $item['license_id'] ) ){
                $license_id = null;
                $name = null;
                $description = null;
                $markup_amount = null;
            } else {
                $tmp_term = get_term_by( 'id', $item['license_id'], 'licenses' );
                $license_id = $item['license_id'];
                $name = $tmp_term->name;
                $description = $tmp_term->description;
                $markup_amount = sell_media_get_term_meta( $license_id, 'markup', true );
            }

            $tmp[] = array(
                'id' => $item['item_id'],
                'name' => get_post_field( 'post_title', $item['item_id'] ),
                'license' => array(
                        'id' => $license_id,
                        'name' => $name,
                        'description' => $description,
                        'markup_amount' => $markup_amount
                        )
                );

            $full_cart['items'] = $tmp;
        }


        echo '<pre>';
        echo '<hr />';
        print_r( $full_cart );
        echo '</pre>';
    }


    /**
     * Check if an email already exists.
     *
     * @param $_POST['email']
     * @uses wp_send_json_error()
     * @uses wp_send_json_success()
     * @package AJAX
     * @return JSON Object
     */
    public function check_email(){
        check_ajax_referer('check_email', 'security');
        $response = array();
        if ( email_exists( $_POST['email'] ) ){
            $response = array(
                'message' => __("Email exists or is invalid", 'sell_media'),
                'status' => 1
                );
        } else {
            $response = array(
                'message' => __("Email does not exists",'sell_media'),
                'status' => 0
                );
        }
        wp_send_json( $response );
    }


    public function checkout_shortcode(){

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

}
// Later make this a singleton or better don't use one
new SellMediaCart;