<?php


require_once plugin_dir_path( __FILE__ ) . 'log.php';


/**
 * Retrieve the correct Paypal Redirect based on http/s
 * and "live" or "test" mode, i.e., sandbox.
 *
 * @return Paypal URI
 */
function sell_media_get_paypal_redirect( $ssl_check=false ) {

    if ( is_ssl() || ! $ssl_check ) {
        $protocol = 'https://';
    } else {
        $protocol = 'http://';
    }

    if ( sell_media_test_mode() ) {
        $paypal_uri = $protocol . 'www.sandbox.paypal.com/cgi-bin/webscr';
    } else {
        $paypal_uri = $protocol . 'www.paypal.com/cgi-bin/webscr';
    }

    return $paypal_uri;
}


/**
 * Passes the Customers Product to Paypal via a redirect.
 * more info here: https://cms.paypal.com/mx/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_html_Appx_websitestandard_htmlvariables#id08A6HH00W2J
 *
 * @param $purchase_data array containing the following:
 * array(
 *     'first_name'   => $user['first_name'],
 *     'last_name'    => $user['last_name'],
 *     'products'     => maybe_serialize( $items ),
 *     'email'        => $user['email'],
 *     'date'         => date( 'Y-m-d H:i:s' ),
 *     'purchase_key' => $purchase_key,
 *     'payment_id'   => $payment_id
 * );
 */
function sell_media_process_paypal_purchase( $purchase_data, $my_post ) {

    $general_settings = get_option( 'sell_media_general_settings' );
    $payment_settings = get_option( 'sell_media_payment_settings' );
    $listener_url = trailingslashit( home_url() ).'?sell_media-listener=IPN';

    $args = array(
        'purchase_key' => $purchase_data['purchase_key'],
        'email' => $purchase_data['email']
    );

    $return_url = add_query_arg( $args, get_permalink( $general_settings['thanks_page'] ) );
    $paypal_redirect = trailingslashit( sell_media_get_paypal_redirect() ) . '?';

    $price = $_SESSION['cart']['amount'];

    $paypal_args = array(
        'cmd'            => '_xclick',
        'amount'         => $price,
        'business'       => $payment_settings['paypal_email'],
        'email'          => $purchase_data['email'],
        'no_shipping'    => '0', // 0 (defualt) prompt for address, not required, 1 no prompt, 2 prompt & required
        'no_note'        => '1',
        'currency_code'  => $payment_settings['currency'],
        'charset'        => get_bloginfo( 'charset' ),
        'rm'             => '2',
        'return'         => $return_url,
        'notify_url'     => $listener_url,
        'mc_currency'    => $payment_settings['currency'],
        'mc_gross'       => $price,
        'payment_status' => '',
        'item_name'      => __( 'Purchase from ', 'sell_media' ) . get_bloginfo( 'name' ),
        'item_number'    => $purchase_data['purchase_key'],
        'custom'         => $purchase_data['payment_id'] // post id, i.e., payment id
    );


    // Add additional args;
    $paypal_args = apply_filters('sell_media_before_paypal_args', $paypal_args );
    $paypal_redirect .= http_build_query( $paypal_args );

    print '<script type="text/javascript">window.location ="' . $paypal_redirect . '"</script>';
    exit;
}
add_action( 'sell_media_gateway_paypal', 'sell_media_process_paypal_purchase' );


/**
 * Listen for a $_GET request from our PayPal IPN.
 * This would also do the "set-up" for an "alternate purchase verification"
 */
function sell_media_listen_for_paypal_ipn() {
    if ( isset( $_GET['sell_media-listener'] )
        && $_GET['sell_media-listener'] == 'IPN'
        || isset( $_GET['test'] )
        && $_GET['test'] == true ) {
        do_action( 'sell_media_verify_paypal_ipn' );
    }
}
add_action( 'init', 'sell_media_listen_for_paypal_ipn' );


/**
 * When a payment is made Paypal will send us a response and this funciton is
 * called. From here we will confirm arguments that we sent to Paypal which
 * the ones Paypal is sending back to us. If they match we countinue if not we
 * stop execution, logging errors to log.txt along the way.
 *
 * @todo Add an option in the wp admin to turn this off/on as aposed to using
 * sell_media_test_mode() to check.
 *
 * This is the Pink Lilly of the whole operation.
 */
function sell_media_process_paypal_ipn() {

    $payment_settings = get_option( 'sell_media_payment_settings' );

    /**
     * If test mode is enabled lets start our log file
     */
    if ( sell_media_test_mode() ){
        $log_file = plugin_dir_path( __FILE__ ) . 'log.txt';
        $file_handle = fopen( $log_file, 'a');
    }

    if ( isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] != 'POST' || is_admin() ) {
        if ( sell_media_test_mode() ){
            start_log_txt( $file_handle );
            write_log_txt( $file_handle, "REQUEST_METHOD is NOT $_POST OR this IS an admin request.\nExecution stopped.\n" );
            end_log_txt( $file_handle );
        }
        return;
    }

    // set initial post data to false
    $post_data = false;

    if ( ini_get( 'allow_url_fopen' ) ) {
        $post_data = file_get_contents( 'php://input' );
    } else {
        ini_set('post_max_size', '12M');
    }

    // start the encoded data collection with notification command
    $encoded_data = 'cmd=_notify-validate';

    // get current arg separator
    $arg_separator = sell_media_get_php_arg_separator_output();

    // verify there is a post_data
    if ( $post_data || strlen( $post_data ) > 0 ) {
        $encoded_data .= $arg_separator.$post_data;
    } else {
        if ( empty( $_POST ) ) {
            return;
        } else {
            foreach ( $_POST as $key => $value ) {
                $encoded_data .= $arg_separator."$key=" . urlencode( $value ); // encode the value and append the data
            }
        }
    }

    parse_str( $encoded_data, $encoded_data_array );

    // get the PayPal redirect uri
    $paypal_redirect = sell_media_get_paypal_redirect( true );

    $remote_post_vars = array(
        'method' => 'POST',
        'timeout' => 45,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => array(),
        'body' => $encoded_data_array
    );

    // get response
    $api_response = wp_remote_post( sell_media_get_paypal_redirect(), $remote_post_vars );

    if ( is_wp_error( $api_response) ){
        if ( sell_media_test_mode() ){
            start_log_txt( $file_handle );
            write_log_txt( $file_handle, "Execution Stopped! is_wp_error" );
            end_log_txt( $file_handle );
        }
        return;
    }

    parse_str( $post_data, $post_data_array );

    if ( ! is_array( $encoded_data_array ) && ! empty( $encoded_data_array ) ){
        if ( sell_media_test_mode() ){
            start_log_txt( $file_handle );
            write_log_txt( $file_handle, "Execution Stopped! encoded_data_array" );
            end_log_txt( $file_handle );
        }
        return;
    }

    $payment_id     = $encoded_data_array['custom']; // This is our post id (payment id)
    $purchase_key   = $encoded_data_array['item_number']; // This is our purchase key
    $payment_status = $encoded_data_array['payment_status'];
    $currency_code  = strtolower( $encoded_data_array['mc_currency'] );

    if ( sell_media_test_mode() ){
        $tmp_encoded_data_array_o = print_r( $encoded_data_array, true );
        write_log_txt( $file_handle, "PayPal Encoded Data:\n{$tmp_encoded_data_array_o}\n\n" );
    }

    $payment_meta_array = get_post_meta( $payment_id, '_sell_media_payment_meta', true );

    if ( empty( $payment_meta_array ) ){
        $log_data .= "No payment meta found for payment_id: {$payment_id}.\n";

        start_log_txt( $file_handle );
        write_log_txt( $file_handle, $log_data );

        return;
    }

    $products_meta_array = unserialize( $payment_meta_array['products'] );
    $payment_amount = get_post_meta( $payment_id, '_sell_media_payment_amount', true );

    if ( sell_media_test_mode() ){
        $tmp_payment_meta = print_r( $payment_meta_array, true );
        $tmp_products_meta_array_o = print_r( $products_meta_array, true );

        $log_data .= "Payment Meta:\n{$tmp_payment_meta}\n\n";
        $log_data .= "Payment Array:\n{$tmp_products_meta_array_o}\n\n";
        $log_data .= "Payment Amount:\n{$payment_amount}\n\n";

        start_log_txt( $file_handle );
        write_log_txt( $file_handle, $log_data );
    }

    // verify details
    if ( $currency_code != strtolower( $payment_settings['currency'] ) ) {
        $currency_setting = $payment_settings['currency'];
        if ( sell_media_test_mode() ){
            $log_data .= "Currency code does not match!\n{$currency_code}, {strtolower( $currency_setting )}\nExecution stopped!\n\n";
            write_log_txt( $file_handle, $log_data );
            end_log_txt( $file_handle );
        }
        return;
    }

    if ( $encoded_data_array['mc_gross'] != $payment_amount ) {
        if ( sell_media_test_mode() ){
            $log_data .= "Payment does not match!\n  Amount attempted to charge: {$encoded_data_array['mc_gross']}\n  Amount customer paid: {$payment_amount}\nExecution stopped!\n\n";
            $tmp_encoded_data_array_o = print_r( $encoded_data_array, true );
            $log_data .= "\nPayPal Encoded Data:\n{$tmp_encoded_data_array_o}\n\n";
            write_log_txt( $file_handle, $log_data );
            end_log_txt( $file_handle );
        }
        return;
    }

    if ( $purchase_key != $payment_meta_array['purchase_key'] ) {
        if ( sell_media_test_mode() ){
            $log_data .= "Purchase key does not match!";
            $log_data .= "You gave me: {$purchase_key} (item_number), but the Purchase key I found is: ";
            $log_data .= "{$payment_meta_array['purchase_key']}\nExecution stopped!\n\n";
            write_log_txt( $file_handle, $log_data );
            end_log_txt( $file_handle );
        }
        return;
    }

    if ( isset( $encoded_data_array['txn_type'] ) && $encoded_data_array['txn_type'] == 'web_accept' ) {
        if ( strtolower( $payment_status ) == 'completed' ) {

            // foreach( $products_meta_array as $products ){
            //     sell_media_update_sales_stats( $products['ProductID'], $products['License'], $products['CalculatedPrice'] );
            // }

            $status = sell_media_update_payment_status( $payment_id, 'publish' );

            if ( sell_media_test_mode() ){
                if ( $status != 0 ){
                    $log_data .= "Updated  Payment ID: {$status} to publish!\n";
                } else {
                    $log_data .= "Did NOT update Payment ID: {$status} to publish!\n";
                }
                write_log_txt( $file_handle, $log_data );
                end_log_txt( $file_handle );
            }

            sell_media_email_purchase_receipt( $purchase_key, $payment_meta_array['email'], $payment_id );

            do_action( 'sell_media_after_successful_payment', $products_meta_array, $payment_id );
            sell_media_empty_cart();

        }
    }

}
add_action( 'sell_media_verify_paypal_ipn', 'sell_media_process_paypal_ipn' );
