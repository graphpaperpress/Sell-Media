<?php

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
function sell_media_process_paypal_purchase( $purchase_data, $payment_id ) {

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


    // Lets save all the info being sent to Paypal at time of purchase
    update_post_meta( $payment_id, '_paypal_args', $paypal_args );

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
 * the ones Paypal is sending back to us.
 * This is the Pink Lilly of the whole operation.
 */
function sell_media_process_paypal_ipn() {
    
    /*
    Since this script is executed on the back end between the PayPal server and this
    script, you will want to log errors to a file or email. Do not try to use echo
    or print--it will not work!

    Here I am turning on PHP error logging to a file called "ipn_errors.log". Make
    sure your web server has permissions to write to that file. In a production
    environment it is better to have that log file outside of the web root.
    */
    ini_set('log_errors', true);
    ini_set('error_log', dirname(__FILE__).'/ipn_errors.log');


    // instantiate the IpnListener class
    include( dirname(__FILE__) . '/php-paypal-ipn/ipnlistener.php');
    $listener = new IpnListener();


    /*
    When you are testing your IPN script you should be using a PayPal "Sandbox"
    account: https://developer.paypal.com
    When you are ready to go live change use_sandbox to false.
    */    
    $general_settings = get_option('sell_media_general_settings');
    $listener->use_sandbox = ( $general_settings['test_mode'] ) ? true : false;


    /*
    By default the IpnListener object is going  going to post the data back to PayPal
    using cURL over a secure SSL connection. This is the recommended way to post
    the data back, however, some people may have connections problems using this
    method.

    To post over standard HTTP connection, use:
    $listener->use_ssl = false;

    To post using the fsockopen() function rather than cURL, use:
    $listener->use_curl = false;
    */

    /*
    The processIpn() method will encode the POST variables sent by PayPal and then
    POST them back to the PayPal server. An exception will be thrown if there is
    a fatal error (cannot connect, your server is not configured properly, etc.).
    Use a try/catch block to catch these fatal errors and log to the ipn_errors.log
    file we setup at the top of this file.

    The processIpn() method will send the raw data on 'php://input' to PayPal. You
    can optionally pass the data to processIpn() yourself:
    $verified = $listener->processIpn($my_post_data);
    */
    try {
        $listener->requirePostMethod();
        $verified = $listener->processIpn();
    } catch (Exception $e) {
        error_log($e->getMessage());
        exit(0);
    }


    /** 
     * The processIpn() method returned true if the IPN was "VERIFIED" and false if it
     * was "INVALID".
     */
    if ($verified) {
        
        $message = null;
            
        /**
         * Verify the mc_gross amount
         *          
         * Check the purchase price from $_POST against the arguments that are saved during 
         * time of purchase.
         */
        $paypal_args = get_post_meta( $_POST['custom'], '_paypal_args', true );
        if ( ! empty( $_POST['mc_gross'] ) && $_POST['mc_gross'] != $paypal_args['mc_gross'] ){
            $message .= "\nPayment does NOT match\n";            
        }

        
        /**
         * Verify seller Paypal email with Paypal email in settings
         *
         * Check if the seller email that was processed by the IPN matches what is saved as 
         * the seller email in our DB
         */
        $payment_settings = get_option( 'sell_media_payment_settings' );
        if ( $_POST['receiver_email'] != $payment_settings['paypal_email'] ){
            $message .= "\nEmail seller email does not match email in settings\n";            
        }


        /**
         * Check if this payment was already processed
         * 
         * Paypals transaction id (txn_id) is stored in the database, we check
         * that against the txn_id returned.
         */
        $txn_id = get_post_meta( $_POST['custom'], 'txn_id', true );
        if ( empty( $txn_id ) ){
            update_post_meta( $_POST['custom'], 'txn_id', $_POST['txn_id'] );
        } else {
            $message .= "\nThis payment was already processed\n";
        }

           
        // Check currency buyer paid with matches what seller allows


        /**
         * Verify the payment is set to "Completed".
         *
         * For a completed payment we update the payment status to publish, send the 
         * download email and empty the cart.         
         */
        if ( ! empty( $_POST['payment_status'] ) && $_POST['payment_status'] == 'Completed' ){
            
            $payment = array(
                'ID' => $_POST['custom'],
                'post_status' => 'publish'
                );
            
            wp_update_post( $payment );
            
            $message .= "\nSuccess! Updated payment status to: published\n";
            $message .= "Payment status is set to: {$_POST['payment_status']}\n\n";
            $message .= "Sending payment id: {$_POST['custom']}\n";
            $message .= "To email: {$_POST['email']}\n";
            $message .= "Purchase receipt: {$_POST['item_number']}\n";
            
            $email_status = sell_media_email_purchase_receipt( $_POST['item_number'], $_POST['email'], $_POST['custom'] );
            $message .= "Email sent status: {$email_status}\n";

            $payment_meta_array = get_post_meta( $_POST['custom'], '_sell_media_payment_meta', true );
            $products_meta_array = unserialize( $payment_meta_array['products'] );
            do_action( 'sell_media_after_successful_payment', $products_meta_array, $_POST['custom'] );
            
            $cart = sell_media_empty_cart();
            $message .= "Emptied cart: {$cart}\n";

        } else {            
            $message .= "\nPayment status not set to Completed\n";            
        }


        /**
         * Check if this is the test mode
         *
         * If this is the test mode we email the IPN text report.
         * note about and box http://stackoverflow.com/questions/4298117/paypal-ipn-always-return-payment-status-pending-on-sandbox
         */
        if ( $general_settings['test_mode'] == true ){
            $message .= "\nTest Mode\n";
            $email = array(
                'to' => get_option('admin_email'),
                'subject' => 'Verified IPN',
                'message' =>  $message . "\n" . $listener->getTextReport()
                );
            wp_mail( $email['to'], $email['subject'], $email['message'] );
        }

    } else {
        /**
         * An Invalid IPN *may* be caused by a fraudulent transaction attempt. It's
         * a good idea to have a developer or sys admin manually investigate any
         * invalid IPN.
         */
        wp_mail( get_option('admin_email'), 'Invalid IPN', $listener->getTextReport());
    }
}
add_action( 'sell_media_verify_paypal_ipn', 'sell_media_process_paypal_ipn' );
