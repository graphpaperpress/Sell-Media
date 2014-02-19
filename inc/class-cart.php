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