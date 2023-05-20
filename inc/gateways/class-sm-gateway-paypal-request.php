<?php
/**
 * PayPal payment request
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require __DIR__ . '/php-paypal-sdk/vendor/autoload.php';

use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersAuthorizeRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;

/**
* PayPal payment request.
*/
class SM_Gateway_PayPal_Request {

    public $_paypal_main = '';

    public $settings = '';

    function __construct() {

        // Get settings.
        $this->settings = sell_media_get_plugin_options();

        add_action( 'sell_media_above_checkout_button', array( $this, 'form' ) );
        add_action( 'wp_ajax_paypal_process', array( $this, 'sell_media_process' ) );
        add_action( 'wp_ajax_nopriv_paypal_process', array( $this, 'sell_media_process' ) );
        add_action( 'sell_media_scripts_hook', array($this, 'sell_media_paypal_scripts'));

        add_action( 'init', array($this, 'sell_media_paypal_manage_order'));
        add_filter( 'sell_media_thanks', array( $this, 'thanks_page' ), 10, 1 );
    }

    /*
     * Register scripts for PayPal request
     * */
    public function sell_media_paypal_scripts() {

        wp_localize_script( 'sell_media', 'sell_media_paypal_obj', array(
                'paypal_nonce' => wp_create_nonce( 'sell_media_paypal_nonce' )
            )
        );
    }

    /**
     * Add payment form in checkout page
     */
    function form(){
        ?>
        <form id="sell_media_payment_gateway" style="margin: 20px 0;" method="post">
            <?php
            
                do_action( 'sell_media_payment_gateway_fields' );
                /*
                 * Check PayPal key is exist
                 * */
                $secret_key_exist = SellMediaPayPal::keys( 'secret_key' );

                /*
                 * Add classes so user can overwrite default button style of theme
                 * */
                $classes = "sell-media-cart-checkout-button sell-media-button";
                $classes .= apply_filters( 'sell_media_checkout_button_classes', $classes );

                if (!empty($secret_key_exist)) {
                    ?>
                    <button type="button" class="<?php echo esc_attr( $classes ); ?>" id="pay_via_paypal_purchase"><?php esc_html_e( 'Pay via PayPal', 'sell_media' ); ?></button>
                    <?php
                }
                
                do_action( 'sell_media_payment_gateway_fields_after' );
            ?>
        </form>
        <?php
    }

    /**
     * Sell Media PayPal order
     */
    public function sell_media_process(){

        // Before payment process action.
        do_action( 'sell_media_before_payment_process' );

        if ( ! isset($_POST['_nonce']) || ! wp_verify_nonce($_POST['_nonce'], 'sell_media_paypal_nonce')) {
            $_send_data['status'] = false;
            wp_send_json($_send_data);
            die();
        }

        // Check if PayPal is selected.
        if( !isset( $_POST['gateway'] ) || 'paypal' !== sanitize_text_field( $_POST['gateway'] ) ){
            $_send_data['status'] = false;
            wp_send_json($_send_data);
            die();
        }

        /*
         * Check PayPal key is exist
         * */
        $secret_key_exist = SellMediaPayPal::keys( 'secret_key' );
        if (empty($secret_key_exist)) {
            $_send_data['status'] = false;
            wp_send_json($_send_data);
            die();
        }
        $sell_media_cart_discount = 0;
        if ( isset( $_POST['discount'] ) ) {
            $sell_media_cart_discount = sanitize_text_field( wp_unslash( $_POST['discount'] ) );
        }

        /*
         * Creating order
         * */
        $order = $this->createOrder();

        /*
         * Store order id return order id
         * */
        $orderId = "";
        $redirect_uri = esc_url( home_url( '/' ) );
        if ($order->statusCode == 201) {

            // Get paypal order id
            $orderId = $order->result->id;

            // Temporary save PayPal order id
            set_transient( 'sell_media_paypal_order_id', $orderId, 3600 );

            // Fetch order approve URL
            foreach ($order->result->links as $_link) {

                // Return PayPal order approve link
                if (isset($_link->rel) && $_link->rel === 'approve') {
                    $redirect_uri = (isset($_link->href) && !empty($_link->href)) ? $_link->href : esc_url( home_url( '/' ) );
                    $_send_data['redirect_uri'] = $redirect_uri;
                    $_send_data['status'] = true;
                }
            }
        } else {
            $_send_data['status'] = false;
        }

        $_send_data = apply_filters('sell_media_paypal_order_process', $_send_data, $order);
        // Send data to js response
        wp_send_json($_send_data);
        die();
    }

    /**
     * Setting up the JSON request body for creating the Order with complete request body. The Intent in the
     * request body should be set as "AUTHORIZE" for authorize intent flow.
     *
     */
    private function buildRequestBody() {

        $_discount_id = 0;
        if ( isset( $_POST['discount'] ) ) {
            $_discount_id = sanitize_text_field($_POST['discount']);
        }
        global $sm_cart;
        $_return_url = apply_filters('sell_media_paypal_return_url', empty( $this->settings->thanks_page ) ? site_url() : esc_url_raw( add_query_arg( array( '_nonce' => wp_create_nonce( 'sell_media_paypal_order_complete_nonce' ) ), get_permalink( $this->settings->thanks_page ) ) ));
        $_cancel_url = apply_filters('sell_media_paypal_return_cancel', empty( $this->settings->checkout_page ) ? site_url() : esc_url( get_permalink( $this->settings->checkout_page ) ));

        $_sub_total = apply_filters( 'sell_media_paypal_subtotal', $sm_cart->getSubtotal( false ) );
        $tax_shipping_array = $this->get_shipping_tax_from_cart( $_discount_id );
        $taxAmount = (isset($tax_shipping_array['tax_amount']) && $tax_shipping_array['tax_amount'] > 0) ? $tax_shipping_array['tax_amount'] : 0;
        $shippingAmount = (isset($tax_shipping_array['shipping_amount']) && $tax_shipping_array['shipping_amount'] > 0) ? $tax_shipping_array['shipping_amount'] : 0;
        $discount = (function_exists('sell_media_get_cart_discount')) ? sell_media_get_cart_discount($_discount_id) : 0;
        $total = ($_sub_total - $discount) + ($shippingAmount + $taxAmount);
        $totalPrice = number_format( (float) $total, 2, '.', '' );

        /*
         * Built request body
         * */
        $_body = array(
                    'intent' => 'CAPTURE',
                    'application_context' => array(
                            'return_url' => $_return_url,
                            'cancel_url' => $_cancel_url,
                            'brand_name' => get_bloginfo('name'),
                            'user_action' => esc_attr__('PAY_NOW', 'sell_media'),
                    ),
                );
        $_body['purchase_units'][] = array(
                                        'amount'        => array(
                                                'currency_code' => sanitize_text_field( $this->settings->currency ),
                                                'value' => $this->number_format($totalPrice),
                                                'breakdown' => array(
                                                        'item_total' => array(
                                                                'currency_code' => sanitize_text_field( $this->settings->currency ),
                                                                'value' => $this->number_format($sm_cart->getSubtotal( false )),
                                                            ),
                                                        'shipping' => array(
                                                                'currency_code' => sanitize_text_field( $this->settings->currency ),
                                                                'value' => $this->number_format($shippingAmount),
                                                            ),
                                                        'handling' => array(
                                                                'currency_code' => sanitize_text_field( $this->settings->currency ),
                                                                'value' => apply_filters( 'sell_media_payment_gateway_handling', number_format( 0, 2 ) ),
                                                            ),
                                                        'tax_total' => array(
                                                                'currency_code' => sanitize_text_field( $this->settings->currency ),
                                                                'value' => apply_filters('sell_media_cart_tax', $this->number_format($taxAmount)),
                                                            ),
                                                        'discount' => array(
                                                                'currency_code' => sanitize_text_field( $this->settings->currency ),
                                                                'value' => $discount,
                                                            ),
                                                ),
                                        ),
                                    );

        return $_body;
    }

    /**
     * Format Number
     * @param int or float $_num
     * @return float
     */
    public function number_format($_num = 0) {
        return number_format($_num, 2);
    }

    /**
     * This is the sample function which can be used to create an order. It uses the
     * JSON body returned by buildRequestBody() to create an new Order.
     * @param boolean $debug for debug code
     */
    public function createOrder($debug = false) {

        $request = new OrdersCreateRequest();
        $request->headers["prefer"] = "return=representation";
        /*
         * Build response body for PayPal
         * */
        $request->body = $this->buildRequestBody();
        $client = SellMediaPayPal::client();
        $response = $client->execute($request);

        if ($debug) {

            echo esc_html("Create order Status Code: {$response->statusCode}\n");
            echo esc_html("Status: {$response->result->status}\n");
            echo esc_html("Order ID: {$response->result->id}\n");
            echo esc_html("Intent: {$response->result->intent}\n");
            echo esc_html("Links:\n");
            foreach($response->result->links as $link)
            {
                echo esc_html("\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n");
            }

            echo esc_html("Gross Amount: {$response->result->purchase_units[0]->amount->currency_code} {$response->result->purchase_units[0]->amount->value}\n");

            // To toggle printing the whole response body comment/uncomment below line
            wp_send_json($response->result, 200, JSON_PRETTY_PRINT);
        }

        return $response;
    }

    /**
     * Add shipping only if purchase contains prints
     * @method get_shipping_tax_from_cart.
     * @param int $discount_id
     * @return array shipping & tax.
     */
    public function get_shipping_tax_from_cart( $discount_id ) {

        $tax_amount = 0;
        global $sm_cart;
        // Convert our cart to an array and remove the key "SCI-1, SCI-2, etc."
        $items = $sm_cart->getItems();
        $cart = array();

        $total_print_qty = 0;
        $total_print_amount = 0;
        $print_ship_flag = 0;
        foreach ( $items as $c ) {
            $cart[] = $c;
            if ( 'print' == $c['item_type'] ) {
                $print_ship_flag = 1;
                $total_print_qty += (isset($c['qty'])) ? intval($c['qty']) : 1;
                $total_print_amount += ($c['price'] * intval($c['qty']));
            }
        }

        // Verify total
        $total = $this->verify_total( $discount_id, $cart );
        $tax_amount = $this->get_the_tax_from_cart( $total );
        if ( 1 == $print_ship_flag ) {
            switch ( $this->settings->reprints_shipping ) {
                case 'shippingFlatRate':
                    $shipping_amount = $this->settings->reprints_shipping_flat_rate;
                    break;
                case 'shippingQuantityRate':
                    $shipping_amount = $total_print_qty * $this->settings->reprints_shipping_quantity_rate;
                    break;
                case 'shippingTotalRate':
                    $shipping_amount = round( $total_print_amount * $this->settings->reprints_shipping_total_rate, 2 );
                    break;
                default:
                    $shipping_amount = 0;
                    break;
            }
        } else {
            $shipping_amount = 0;
        }

        $response = array(
            'tax_amount' => $tax_amount,
            'shipping_amount' => $shipping_amount,
        );        
        return apply_filters( 'sell_media_calculate_tax_shipping', $response );
    }

    /**
     * Get the Tax from the cart.
     *
     * @method get_the_tax_from_cart.
     * @param int $total.
     * @return float $tax_amount.
     */
    public function get_the_tax_from_cart( $total ) {

        $tax_amount = 0;
        if (
            isset( $this->settings->tax[0] ) &&
            'yes' == $this->settings->tax[0] &&
            ( empty( $this->settings->tax_display ) ||
                'exclusive' == $this->settings->tax_display )
        ) {
            $tax_amount = ( $this->settings->tax_rate * $total );
        }
        return $tax_amount;
    }

    /**
     * Verify the product totals
     *
     * @param  $products
     * @return $total
     */
    public function verify_total( $discount_id, $products = null ) {

        $total = 0;
        $p = new SellMediaProducts();

        foreach ( $products as $product ) {

            $product_id = intval($product['item_id']);
            $license_id = empty( $product['item_license'] ) ? null : $product['item_license'];
            $price_id = empty( $product['item_pgroup'] ) ? null : $product['item_pgroup'];

            // this is a download with an assigned license, so add license markup
            if ( '' !== $license_id ) {
                $price = $p->verify_the_price( $product_id, $price_id );
                $markup = $p->markup_amount( $product_id, $price_id, $license_id );
                $amount = $price + $markup;
            } else {
                // this is either a download without a license or a print, so just verify the price
                $amount = $p->verify_the_price( $product_id, $price_id );
            }

            // support for quantities
            if ( $product['qty'] > 1 ) {
                $amount = $amount * $product['qty'];
            }

            // Apply discount
            $amount = apply_filters( 'sell_media_price_filter', $amount, $discount_id, $product['qty'] );

            $total += $amount;
        }

        return number_format( (float) $total, 2, '.', '' );
    }

    /**
     * Get the total amount of the product.
     *
     * @param $product
     * @param $discount_id
     * @return
     * @since 1.0.7
     */
    public function get_product_amount_after_discount( $product, $discount_id ) {

        $p = new SellMediaProducts();
        if(empty($product)) {
            return 0;
        }
        $product_id = intval($product['item_id']);
        $license_id = empty( $product['item_license'] ) ? null : $product['item_license'];
        $price_id   = empty( $product['item_pgroup'] ) ? null : $product['item_pgroup'];

        // this is a download with an assigned license, so add license markup
        if ( '' !== $license_id ) {
            $price  = $p->verify_the_price( $product_id, $price_id );
            $markup = $p->markup_amount( $product_id, $price_id, $license_id );
            $amount = $price + $markup;
        } else {
            // this is either a download without a license or a print, so just verify the price
            $amount = $p->verify_the_price( $product_id, $price_id );
        }
        $amount = apply_filters( 'sell_media_price_filter', $amount, $discount_id, intval($product['qty']) );
        return $amount;
    }

    /**
     * This function can be used to perform authorization on the approved order.
     * Valid Approved order id should be passed as an argument.
     */
    public function authorizeOrder($orderId, $debug=false) {

        $request = new OrdersAuthorizeRequest($orderId);
        $request->body = "{}";

        $client = SellMediaPayPal::client();
        $response = $client->execute($request);
        if ($debug) {

            echo esc_html("Status Code: {$response->statusCode}\n");
            echo esc_html("Status: {$response->result->status}\n");
            echo esc_html("Order ID: {$response->result->id}\n");
            echo esc_html("Authorization ID: {$response->result->purchase_units[0]->payments->authorizations[0]->id}\n");
            echo esc_html("Links:\n");
            foreach($response->result->links as $link)
            {
                echo esc_html("\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n");
            }
            echo esc_html("Authorization Links:\n");
            foreach($response->result->purchase_units[0]->payments->authorizations[0]->links as $link)
            {
                echo esc_html("\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n");
            }
            // To toggle printing the whole response body comment/uncomment below line
            wp_send_json($response->result, 200, JSON_PRETTY_PRINT);
        }
        return $response;
    }

    /**
     * This function can be used to capture an order payment by passing the approved
     * order id as argument.
     *
     * @param string $orderId
     * @param boolean $debug
     * @returns object $response PayPal captured order object
     */
    public function captureOrder($orderId, $debug=false) {

        $request = new OrdersCaptureRequest($orderId);
        $client = SellMediaPayPal::client();
        $response = $client->execute($request);
        if ($debug) {
            echo esc_html("Status Code: {$response->statusCode}\n");
            echo esc_html("Status: {$response->result->status}\n");
            echo esc_html("Order ID: {$response->result->id}\n");
            echo esc_html("Links:\n");
            foreach($response->result->links as $link)
            {
                echo esc_html("\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n");
            }
            echo esc_html("Capture Ids:\n");
            foreach($response->result->purchase_units as $purchase_unit) {

                foreach($purchase_unit->payments->captures as $capture) {
                    echo esc_html("\t{$capture->id}");
                }
            }
            // To toggle printing the whole response body comment/uncomment below line
            wp_send_json($response->result, 200, JSON_PRETTY_PRINT);
        }

        return $response;
    }

    /**
     * This function can be used to retrieve an order by passing order Id as argument.
     * @param string $orderId PayPal order id
     * @param boolean $debug if you want to debug code
     */
    public function getOrder($orderId, $debug=false) {

        $client = SellMediaPayPal::client();
        $response = $client->execute(new OrdersGetRequest($orderId));
        /**
         * Enable below line to show complete response as JSON.
         */
        if ($debug) {
            echo esc_html("Get Order Status Code: {$response->statusCode}\n");
            echo esc_html("Status: {$response->result->status}\n");
            echo esc_html("Order ID: {$response->result->id}\n");
            echo esc_html("Intent: {$response->result->intent}\n");
            echo esc_html("Links:\n");
            foreach($response->result->links as $link)
            {
                echo esc_html("\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n");
            }

            echo esc_html("Gross Amount: {$response->result->purchase_units[0]->amount->currency_code} {$response->result->purchase_units[0]->amount->value}\n");

            // To toggle printing the whole response body comment/uncomment below line
            wp_send_json($response->result, 200, JSON_PRETTY_PRINT);
        }
        return $response;
    }

    /**
     * This function can be used to save approved order information.
     */
    public function sell_media_paypal_manage_order() {

        if(isset($_GET['PayerID']) && !empty($_GET['PayerID']) && isset($_GET['token']) && !empty($_GET['token']) && isset($_GET['_nonce']) && wp_verify_nonce($_GET['_nonce'], 'sell_media_paypal_order_complete_nonce')) {

            // Get PayPal order id from transient
            $_paypal_order_id = get_transient( 'sell_media_paypal_order_id' );

            // Delete PayPal Order id from transient
            delete_transient('sell_media_paypal_order_id');

            // Authorize order
            if ($_paypal_order_id) {

                // Capture Approved order
                $_paypal_order_capture_response = $this->captureOrder($_paypal_order_id);

                // Check captured order status
                if ($_paypal_order_capture_response->statusCode == 201) {
                    $_capture_id = $_paypal_order_capture_response->result->purchase_units[0]->payments->captures[0]->id;
                    $this->save_payment($_capture_id, $_paypal_order_id);
                }
            }
        }
    }

    /**
     * Save captured order
     * @param string $_capture_id PayPal Order captured id
     * @param string $_paypal_order_id PayPal Order id
     */
    public function save_payment($_capture_id, $_paypal_order_id) {

        // Get captured order
        $_paypal_get_order = $this->getOrder($_paypal_order_id);

        if($_paypal_get_order->statusCode == 200) {

            $amount = $_paypal_get_order->result->purchase_units[0]->amount->value;
            $_currency = $_paypal_get_order->result->purchase_units[0]->amount->currency_code;
            $total_qty = 0;
            global $sm_cart;
            // Convert our cart to an array and remove the key "SCI-1, SCI-2, etc."
            $items = $sm_cart->getItems();
            foreach ( $items as $c ) {
                $total_qty += $c['qty'];
            }

            if (!empty($_paypal_get_order->result->purchase_units[0]->items)) {
                foreach ( $_paypal_get_order->result->purchase_units[0]->items as $key => $item ) {
                    $total_qty += $item->quantity;
                }
            }
            $_billing_details = (isset($_paypal_get_order->result->payer)) ? $_paypal_get_order->result->payer : '';
            $_shipping = (isset($_paypal_get_order->result->purchase_units[0]->shipping)) ? $_paypal_get_order->result->purchase_units[0]->shipping : '';
            $_shipping_total = (isset($_paypal_get_order->result->purchase_units[0]->amount->breakdown->shipping->value)) ? $_paypal_get_order->result->purchase_units[0]->amount->breakdown->shipping->value : '';
            $_tax_total = (isset($_paypal_get_order->result->purchase_units[0]->amount->breakdown->tax_total->value)) ? $_paypal_get_order->result->purchase_units[0]->amount->breakdown->tax_total->value : 0;
            $_discount_total = isset( $_paypal_get_order->result->purchase_units[0]->amount->breakdown->discount->value ) ? $_paypal_get_order->result->purchase_units[0]->amount->breakdown->discount->value : 0;
            $_payer_name = esc_attr($_billing_details->name->given_name) .' '. esc_attr($_billing_details->name->surname);
            $payment_id = wp_insert_post(
                array(
                    'post_title'  => sanitize_text_field($_billing_details->email_address),
                    'post_status' => 'publish',
                    'post_type'   => 'sell_media_payment',
                )
            );

            if ( is_wp_error( $payment_id ) ) {
                $status = false;
                $message = $payment_id->get_error_message();
            } else {

                $_address_street = $_city = $_state = $_gpp_postal_code = $_country_code = '';

                // take address details from billing details if not exist then it will take from shipping details
                if (isset($_billing_details->address->address_line_1)) {

                    $_address_street = sanitize_text_field($_billing_details->address->address_line_1);
                    $_city = sanitize_text_field($_billing_details->address->admin_area_2);
                    $_state = sanitize_text_field($_billing_details->address->admin_area_1);
                    $_gpp_postal_code = sanitize_text_field($_billing_details->address->postal_code);
                    $_country_code = sanitize_text_field($_billing_details->address->country_code);

                } else if(isset($_paypal_get_order->result->purchase_units[0]->shipping)){

                    $_address_street = sanitize_text_field($_shipping->address->address_line_1);
                    $_city = sanitize_text_field($_shipping->address->admin_area_2);
                    $_state = sanitize_text_field($_shipping->address->admin_area_1);
                    $_gpp_postal_code = sanitize_text_field($_shipping->address->postal_code);
                    $_country_code = sanitize_text_field($_shipping->address->country_code);

                }

                $tmp = array(
                    'email'                => $_billing_details->email_address,
                    'first_name'           => $_payer_name,
                    'address_street'       => $_address_street,
                    'address_city'         => $_city,
                    'address_state'        => $_state,
                    'address_country_code' => $_country_code,
                    'address_zip'          => $_gpp_postal_code,
                    'transaction_id'       => $_paypal_order_id,
                    'payer_id'             => $_billing_details->payer_id,
                    'order_status'         => $_paypal_get_order->result->status,
                    'number_products'      => $total_qty,
                    'gateway'              => 'paypal',
                    'total'                => $this->number_format( $amount ),
                );
                if($_shipping_total) {
                    $tmp['shipping'] = $_shipping_total;
                }
                if($_tax_total) {
                    $tmp['tax'] = $_tax_total;
                }
                if($_shipping_total) {
                    $tmp['discount'] = $_discount_total;
                }
            }
            update_post_meta( $payment_id, '_paypal_args', $tmp );
            update_post_meta( $payment_id, '_sell_media_payment_meta', $tmp );
            update_post_meta( $payment_id, 'parameters', $_paypal_get_order );
            update_post_meta( $payment_id, 'transaction_id',$_paypal_order_id );
            update_post_meta( $payment_id, 'payment_capture_id',$_capture_id );
            update_post_meta( $payment_id, 'payment_currency_code',$_currency );
            update_post_meta( $payment_id, 'payment_billing_details', $_billing_details );
            update_post_meta( $payment_id, 'payment_shipping_details', $_shipping );
            update_post_meta( $payment_id, 'paypal_order_token', (isset($_GET['token'])) ? sanitize_text_field( $_GET['token'] ) : '' );

            // Create new customer if not exist
            $this->create_customer( $_billing_details );

            if ( ! empty( $payment_id ) ) {

                // Save placed order payment details
                $this->save_payment_details( $payment_id );

                // Send mail to admin and customer
                $this->send_emails( $payment_id );
            }

            do_action( 'sell_media_after_successful_payment', $payment_id );
        }
    }

    /**
     * Display purchased products on the thanks page.
     * @method thanks_page
     * @param string $html Thank you page HTML
     * @return string HTML content
     */
    public function thanks_page( $html ) {

        if ( isset( $_GET['token'] ) && isset( $_GET['PayerID'] )) {
            //paypal_order_id
            $token = sanitize_text_field( wp_unslash( $_GET['token'] ) );
            try {
                $args = array(
                    'post_type' => 'sell_media_payment',
                    'meta_query' => array(
                        array(
                            'key' => 'paypal_order_token',
                            'value' => $token,
                            'compare' => '=',
                        )
                    )
                );

                $payment_id = null;
                $payment_query = new WP_Query( $args );

                while ( $payment_query->have_posts() ) :
                    $payment_query->the_post();
                    $payment_id = get_the_ID();
                endwhile;

                $html ='<div class="sell-media-thanks-message">';

                if ( ! empty( $payment_id ) ) {
                    $html .= '<p><strong>' . sprintf( esc_attr__( 'Your order is complete.', 'sell_media' ), $payment_id ) . '</strong></p>';
                    $html .= Sell_Media()->payments->get_payment_products_formatted( $payment_id );
                    $meta = get_post_meta($payment_id, '_sell_media_payment_meta', true);
                    if ( $meta ) {
                        $html .= '<div class="sell-media-order-details">';
                        $html .= '<p><strong>Your details</strong></p>';
                        $html .= '<small class="order_id">Order ID: ' . $payment_id . '</small><br/>';
                        foreach ( $meta as $key => $value ) {
                            if ('products' !== $key) {
                                $html .= '<small class="'. $key .'">' . ucwords( str_replace( '_', ' ', $key ) ) . ': ' . $value . '</small><br/>';
                            }
                        }
                        $html .= '</div>';
                    }
                } else {
                    $refresh_url = isset( $this->settings->thanks_page ) ? get_permalink( $this->settings->thanks_page ) : home_url( );
                    $html .= sprintf( esc_attr__( 'We\'ve received your payment and are processing your order. <a href="%s" class="reload">Refresh this page</a> to check your order status. If you continue to see this message, please contact us.', 'sell_media' ), esc_url( add_query_arg(
                                array(
                                    'token' => (isset($_GET['token'])) ? sanitize_text_field($_GET['token']) : '',
                                    'PayerID' => (isset($_GET['PayerID'])) ? sanitize_text_field($_GET['PayerID']) : '',
                                ),
                                $refresh_url
                            )
                        )
                    );
                }

                global $sm_cart;
                @$sm_cart->clear();
                $html .= '</div>';
                $html =  apply_filters( 'sell_media_thanks_filter_below', $html, $payment_id );

            } catch ( Exception $e ) {
                error_log($e->getMessage());
                $html =  apply_filters( 'sell_media_thanks_filter_below', $e->getMessage() );
            }

        }

        return $html;
    }


    /**
     * Update the payment products array.
     *
     * @method save_payment_details
     * @param int $payment_id Post id of the payment.
     * @param array $payment_details_array details of the payment.
     * @return string
     * @since 1.0.7
     */
    public function save_payment_details( $payment_id, $payment_details_array = array() ) {

        global $sm_cart;
        $products = $sm_cart->getItems();

        if ( empty( $products ) )
            return false;

        $markups = Sell_Media()->tax_markup->markup_taxonomies();
        if ( empty( $payment_details_array ) ) {
            $payment_details_array = get_post_meta( $payment_id, '_sell_media_payment_meta', true );
        } else {
            $payment_details_array = array();
        }
        $p = new SellMediaProducts();
        foreach ( $products as $product ) {

            if( $product['item_type'] == 'print_on_demand' ) {
                $license_desc = null;
                $license_name = null;
                $amount = $product['price'];
            } else if ( empty( $product['item_license'] ) || (int) $product['item_license'] < 1) {
                $license_desc = null;
                $license_name = null;
                $amount = $p->verify_the_price( esc_attr($product['item_id']), esc_attr($product['item_pgroup']) );
            } else {
                $term_obj = get_term_by( 'id', $product['item_license'], 'licenses' );
                $license_desc = empty( $term_obj ) ? null : $term_obj->description;
                $license_name = empty( $term_obj ) ? null : $term_obj->name;
                $amount = $p->verify_the_price( esc_attr($product['item_id']), esc_attr($product['item_pgroup']) ) + $p->markup_amount( esc_attr($product['item_id']), esc_attr($product['item_pgroup']), esc_attr($product['item_license']) );
            }
            if ( $product['qty'] > 1 ) {
                $total = $amount * intval($product['qty']);
            } else {
                $total = $amount;
            }
            // Old purchase links didn't have attachment_id set
            // So we derive the attachment_id from the product's post_meta
            $product['attachment'] = ( ! empty( $product['item_attachment'] ) ) ? $product['item_attachment'] : sell_media_get_attachment_id( $product['item_id'] );

            $tmp_products = array(
                'name'        => get_the_title( $product['item_id'] ),
                'id'          => intval($product['item_id']),
                'attachment'  => (isset($product['attachment'])) ? $product['attachment'] : '',
                'type'        => (isset($product['item_type'])) ? $product['item_type'] : '',
                'size'        =>
                    array(
                        'name'        => isset( $product['item_size'] ) ? esc_attr($product['item_size']) : '',
                        'id'          => (isset($product['item_pgroup'])) ? esc_attr($product['item_pgroup']) : '',
                        'amount'      => $amount,
                        'description' => null,
                    ),
                'license'     =>
                    array(
                        'name'        => $license_name,
                        'id'          => (isset($product['item_license']) && !empty( $product['item_license'] )) ? $product['item_license'] : '',
                        'description' => $license_desc,
                        'markup'      => (isset($product['item_license']) && !empty( $product['item_license'] )) ? str_replace( '%', '', get_term_meta( $product['item_license'], 'markup', true ) ) : '',
                    ),
                'qty'      => intval($product['qty']),
                'total'    => $total,
            );
            if ( isset( $product['shipping_amount'] ) && !empty( $product['shipping_amount'] ) ) {
                $tmp_products['shipping'] = $product['shipping_amount'];
            }
            if( $product['item_type'] == 'print_on_demand' && isset( $product['item_sku'] ) ) {
                $tmp_products['sku'] = $product['item_sku'];
            }
            $payment_details_array['products'][] = $tmp_products;
        }
        update_post_meta( $payment_id, '_sell_media_payment_meta', $payment_details_array );
    }

    /**
     * Create a new customer
     *
     * @param  array $_billing_details pass customer details
     */
    public function create_customer( $_billing_details ) {
        $customer = new SellMediaCustomer();
        if(!empty($_billing_details->name->given_name)) {
            $_payer_name = $_billing_details->name->given_name .' '. $_billing_details->name->surname;
            $customer->insert( $_billing_details->email_address, $_payer_name );
        }
    }

    /**
     * Send emails to buyer and admin
     *
     * @param  int $payment_id Post id of the payment.
     */
    public function send_emails( $payment_id ) {

        $buyer_email = Sell_Media()->payments->get_meta_key( $payment_id, 'email' );
        $admin_email = get_option( 'admin_email' );

        Sell_Media()->payments->email_receipt( $payment_id, $buyer_email );
        Sell_Media()->payments->email_receipt( $payment_id, $admin_email );
    }
}

new SM_Gateway_PayPal_Request();
