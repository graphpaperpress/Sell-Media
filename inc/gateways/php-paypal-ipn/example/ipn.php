<?php
/**
 *  PHP-PayPal-IPN Example
 *
 *  This shows a basic example of how to use the IPNListener() PHP class to
 *  implement a PayPal Instant Payment Notification (IPN) listener script.
 *
 *  This package is available at GitHub:
 *  https://github.com/WadeShuler/PHP-PayPal-IPN/
 *
 *  @package    PHP-PayPal-IPN
 *  @link       https://github.com/WadeShuler/PHP-PayPal-IPN
 *  @forked     https://github.com/Quixotix/PHP-PayPal-IPN
 *  @author     Wade Shuler
 *  @copyright  Copyright (c) 2015, Wade Shuler
 *  @license    http://choosealicense.com/licenses/gpl-2.0/
 *  @version    2.2.0
 */

// TODO: I hate 'ini_set', fix this later
ini_set('log_errors', true);
ini_set('error_log', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ipn_errors.log');

// include the IPNListener Class
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'IPNListener.php');

$listener = new IPNListener();      // NOTICE new upper-casing of the class name
$listener->use_sandbox = true;      // Only needed for testing (sandbox), else omit or set false

// NOTICE this is no longer in a try-catch.
// The try-catch is now inside processIpn itself.
if ($verified = $listener->processIpn())
{

    // Valid IPN
    /*
        1. Check that $_POST['payment_status'] is "Completed"
        2. Check that $_POST['txn_id'] has not been previously processed
        3. Check that $_POST['receiver_email'] is your Primary PayPal email
        4. Check that $_POST['payment_amount'] and $_POST['payment_currency'] are correct
    */
    $transactionRawData = $listener->getRawPostData();      // raw data from PHP input stream
    $transactionData = $listener->getPostData();            // POST data array
    file_put_contents('ipn_success.log', print_r($transactionData, true) . PHP_EOL, LOCK_EX | FILE_APPEND);

} else {

    // Invalid IPN
    $errors = $listener->getErrors();
    file_put_contents('ipn_errors.log', print_r($errors, true) . PHP_EOL, LOCK_EX | FILE_APPEND);

}
