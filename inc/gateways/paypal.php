<?php

/**
 * Retrieve the correct PayPal Redirect based on http/s
 * and "live" or "test" mode, i.e., sandbox.
 *
 * @return PayPal URI
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
 * When a payment is made PayPal will send us a response and this function is
 * called. From here we will confirm arguments that we sent to PayPal which
 * the ones PayPal is sending back to us.
 * This is the Pink Lilly of the whole operation.
 */
function sell_media_process_paypal_ipn() {

    /**
     * Instantiate the IPNListener class
     */
    include( dirname( __FILE__ ) . '/php-paypal-ipn/IPNListener.php' );
    $listener = new IPNListener();

    /**
     * Set to PayPal sandbox or live mode
     */
    $settings = sell_media_get_plugin_options();
    $listener->use_sandbox = ( $settings->test_mode ) ? true : false;

    /**
     * Check if IPN was successfully processed
     */
    if ( $verified = $listener->processIpn( ) ) {

        /**
         * Log successful purchases
         */
        $transactionData = $listener->getPostData(); // POST data array
        file_put_contents( 'ipn_success.log', print_r( $transactionData, true ) . PHP_EOL, LOCK_EX | FILE_APPEND );

        $message = null;

        /**
         * Verify seller PayPal email with PayPal email in settings
         *
         * Check if the seller email that was processed by the IPN matches what is saved as
         * the seller email in our DB
         */
        $settings = sell_media_get_plugin_options();
        if ( $_POST['receiver_email'] != $settings->paypal_email ){
            $message .= "\nEmail seller email does not match email in settings\n";
        }

        /**
         * Verify currency
         *
         * Check if the currency that was processed by the IPN matches what is saved as
         * the currency setting
         */
        $settings = sell_media_get_plugin_options();
        if ( $_POST['mc_currency'] != $settings->currency ){
            $message .= "\nCurrency does not match those assigned in settings\n";
        }

        /**
         * Check if this payment was already processed
         *
         * PayPal transaction id (txn_id) is stored in the database, we check
         * that against the txn_id returned.
         */
        $txn_id = get_post_meta( $_POST['custom'], 'txn_id', true );
        if ( empty( $txn_id ) ){
            update_post_meta( $_POST['custom'], 'txn_id', $_POST['txn_id'] );
        } else {
            $message .= "\nThis payment was already processed\n";
        }

        /**
         * Verify the payment is set to "Completed".
         *
         * Create a new payment, send customer an email and empty the cart
         */
        if ( ! empty( $_POST['payment_status'] ) && $_POST['payment_status'] == 'Completed' ) {

            // Return if this IPN doesn't contain a Sell Media item
            if ( empty( $_POST['option_selection1_1'] ) && ( $_POST['option_selection1_1'] != 'print' || $_POST['option_selection1_1'] != 'download' ) )
                return;

            $data = array(
                'post_title'    => $_POST['payer_email'],
                'post_status'   => 'publish',
                'post_type'     => 'sell_media_payment'
            );

            $payment_id = wp_insert_post( $data );
            $payments = Sell_Media()->payments;

            if ( $payment_id ) {

                update_post_meta( $payment_id, '_paypal_args', $_POST );

                // record the PayPal payment details
                $payments->paypal_copy_args( $payment_id );

                // create new user, auto log them in, email them registration
                Sell_Media()->customer->insert( $_POST['payer_email'], $_POST['first_name'], $_POST['last_name'] );

                $message .= "\nSuccess! Your purchase has been completed.\n";
                $message .= "Your transaction number is: {$_POST['txn_id']}\n";
                $message .= "To email: {$_POST['payer_email']}\n";

                // Send email to buyer and admin
                $email_status = $payments->email_receipt( $payment_id, $_POST['payer_email'] );
                $admin_email_status = $payments->email_receipt( $payment_id, $settings->from_email );

                $message .= "{$email_status}\n";
                $message .= "{$admin_email_status}\n";

                do_action( 'sell_media_after_successful_payment', $payment_id );

            }

        } else {

            $message .= "\nPayment status not set to Completed\n";

        }

        /**
         * Check if this is the test mode
         *
         * If this is the test mode we email the IPN text report.
         * note about and box http://stackoverflow.com/questions/4298117/paypal-ipn-always-return-payment-status-pending-on-sandbox
         */
        if ( $settings->test_mode == true ){

            $message .= "\nTest Mode\n";
            $email = array(
                'to' => $settings->from_email,
                'subject' => 'Verified IPN',
                'message' =>  $message . "\n" . $listener->getTextReport()
                );

            wp_mail( $email['to'], $email['subject'], $email['message'] );

        }

    } else {

        /**
         * Log errors
         */
        $errors = $listener->getErrors();
        file_put_contents( 'ipn_errors.log', print_r( $errors, true ) . PHP_EOL, LOCK_EX | FILE_APPEND );

        /**
         * An Invalid IPN *may* be caused by a fraudulent transaction attempt. It's
         * a good idea to have a developer or sys admin manually investigate any
         * invalid IPN.
         */
        wp_mail( $settings->from_email, 'Invalid IPN', $listener->getTextReport() );

    }
}
add_action( 'sell_media_verify_paypal_ipn', 'sell_media_process_paypal_ipn' );
