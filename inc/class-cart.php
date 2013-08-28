<?php

/**
 * This class acts as a wrapper for all methods related to manipulating
 * the cart, i.e. getting prices, markup values, etc.
 *
 * @author Zane M. Kolnik
 */
Class Sell_Media_Cart {


    public function __construct(){
        add_action( 'wp_ajax_nopriv_add_items', array( &$this, 'add_items' ) );
        add_action( 'wp_ajax_add_items', array( &$this, 'add_items' ) );

        add_action( 'wp_ajax_nopriv_remove_item', array( &$this, 'remove_item' ) );
        add_action( 'wp_ajax_remove_item', array( &$this, 'remove_item' ) );

        add_action( 'wp_ajax_nopriv_sell_media_check_email', array( &$this, 'check_email' ) );
        add_action( 'wp_ajax_sell_media_check_email', array( &$this, 'check_email' ) );
    }


    /**
     * Derive price of an item via post meta
     * else derive from the price group
     * else use the default price from settings
     *
     * @param $post_id (int) The post_id to a sell_media_item
     * @param $price_id (int) One of the following: 'term_id' (of price-group taxonomy), 'sell_media_original_file', 'default_price'
     *
     * @return formated price without the currency symbolc
     */
    public function item_price( $post_id=null, $price_id=null ){

        $default_price_array = get_option('sell_media_size_settings');
        $custom_price = get_post_meta( $post_id, 'sell_media_price', true );

        if ( $price_id == 'sell_media_original_file' && ! empty( $custom_price ) ){
            $item_price = $custom_price;
        } elseif ( $price_id == 'sell_media_original_file' && empty( $custom_price ) ){
            $item_price = $default_price_array['default_price'];
        } elseif ( $price_id == 'default_price' && empty( $custom_price ) ){
            $item_price = $default_price_array['default_price'];
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
     *
     * @return $amount (string) The updated amount the customer is charged
     */
    public function get_total( $items=array() ){
        $amount = 0;
        if ( ! empty( $items ) ){
            foreach ( $items as $item ){
                if ( empty( $item['license'] ) ){
                    $price = $item['price']['amount'] * $item['qty'];
                } else {
                    $price = $this->item_markup_total( $item['id'], $item['price']['id'], $item['license']['id'] );
                }
                $qty = 1;
                $amount = $amount + $price * $qty;
            }
        }
        return sprintf( "%0.2f", max( $amount, 0 ) );
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
     * Add items to $_SESSION for shopping cart via $_POST
     *
     * @since 0.1
     * @todo Update all price_id to be size_id
     * @todo Update all price id (array) to be part of item array
     * @todo move this into the cart class
     */
    public function add_items(){

        check_ajax_referer('add_items', 'sell_media_nonce');

        // Get current cart if any if not set $cart to be an empty array
        $cart = isset( $_SESSION['cart']['items'] ) ? $_SESSION['cart']['items'] : array();

        // Get any additional items
        $to_add = array();
        $to_add = apply_filters('sell_media_additional_items', $to_add);


        // If we don't have additional items we use whats in $_POST
        if ( empty( $to_add ) ){

            if ( empty( $_POST['price_id'] ) ) die();

            // Determine price group, id, name, description
            if ( $_POST['price_id'] == 'sell_media_original_file' ){
                $price_name = __('Original File');
                $description = null;
            } else {
                $price_group_obj = get_term_by( 'id', $_POST['price_id'], 'price-group' );
                $price_name = $price_group_obj->name;
                $width = sell_media_get_term_meta( $price_group_obj->term_id, 'width', true );
                $height = sell_media_get_term_meta( $price_group_obj->term_id, 'height', true );
                if ( ! empty( $width ) && ! empty( $height ) ){
                    $description = apply_filters( 'sell_media_cart_price_description', $width . ' x ' . $height );
                }
            }


            // Determine license id, name, description
            if ( empty( $_POST['License'] ) ){
                $markup = null;
                $license_name = null;
                $license_id = null;
            } else {
                $license_obj = get_term_by( 'id', $_POST['License'], 'licenses' );
                $license_id = $license_obj->term_id;
                $license_name = $license_obj->name;
                $markup = sell_media_get_term_meta( $license_obj->term_id, 'markup', true );
            }


            $items[] = array(
                'id' => (int)$_POST['ProductID'],
                'name' => get_post_field('post_title', (int)$_POST['ProductID'] ),
                'price' => array(
                    'id' => $_POST['price_id'],
                    'name' => $price_name,
                    'description' => $description,
                    'amount' => $this->item_price( $_POST['ProductID'], $_POST['price_id'] )
                ),
                'license' => array(
                    'id' => $license_id,
                    'name' => $license_name,
                    'markup' => $markup
                    ),
                'qty' => 1,
                'total' => $this->item_markup_total( $_POST['ProductID'], $_POST['price_id'], $license_id )
                );

            $items = array_merge( $cart, $items );
        } else {
            // We have additional items and merge the current cart with the new items to add
            $items = array_merge( $cart, $to_add );
        }

        // Update our session with the new items
        $_SESSION['cart']['items'] = $items;

        // Update the total and the quantity
        $_SESSION['cart']['currency'] = sell_media_get_currency_symbol();
        $_SESSION['cart']['total'] = $this->get_total( $_SESSION['cart']['items'] );
        $_SESSION['cart']['qty'] = $this->get_quantity( $_SESSION['cart']['items'] );

        die();
    }


    /**
     * Removes an item from the users cart, updates the quantity and total in session
     *
     * @since 0.1
     * @param Derives the post_id from $_POST['item_id']
     * @return null
     */
    public function remove_item() {

        $item_index = $_POST['item_id'];

        $_SESSION['cart']['total'] = $this->get_total( $_SESSION['cart']['items'] ) - $_SESSION['cart']['items'][ $item_index ]['total'];
        $_SESSION['cart']['qty'] = $this->get_quantity( $_SESSION['cart']['items'] ) - $_SESSION['cart']['items'][ $item_index ]['qty'];

        unset( $_SESSION['cart']['items'][$item_index] );

        if ( empty( $_SESSION['cart']['items'] ) ) {
            print '<p>' . __('You have no items in your cart. ', 'sell_media') . '<a href="'. get_post_type_archive_link('sell_media_item') .'">' . __('Continue shopping', 'sell_media') .'</a>.</p>';
        }

        die();
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
        if ( !is_user_logged_in() ) {
            email_exists( $_POST['email'] ) ? wp_send_json_error() : wp_send_json_success();
        } else {
            wp_send_json_success();
        }
        die();
    }
}
// Later make this a singleton or better don't use one
New Sell_Media_Cart;