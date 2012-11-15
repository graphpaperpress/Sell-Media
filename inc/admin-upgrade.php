<?php

/**
 * General Settings
 */
$sell_media_general_settings = array();
$sell_media_general_settings['test_mode'] = get_option( 'sell_media_test_mode' );
$sell_media_general_settings['checkout_page'] = get_option( 'sell_media_cart_page' );
$sell_media_general_settings['thanks_page'] = get_option( 'sell_media_thanks_page' );

/**
 * Update new settings with old values
 */
update_option('sell_media_general_settings', $sell_media_general_settings );

/**
 * Delete old "general settings"
 */
delete_option( 'sell_media_test_mode' );
delete_option( 'sell_media_cart_page' );
delete_option( 'sell_media_thanks_page' );

/**
 * Payment Settings
 */
$sell_media_payment_settings = array();
$sell_media_payment_settings['paypal_email'] = get_option( 'sell_media_paypal_email' );
$sell_media_payment_settings['currency'] = get_option( 'sell_media_currency' );
$sell_media_payment_settings['default_price'] = get_option( 'sell_media_original_price' );

/**
 * Update new settings with old values
 */
update_option('sell_media_payment_settings', $sell_media_payment_settings );

/**
 * Delete old "general settings"
 */
delete_option( 'sell_media_paypal_email' );
delete_option( 'sell_media_currency' );
delete_option( 'sell_media_original_price' );

/**
 * Email Settings
 */
$sell_media_email_settings = array();
$sell_media_email_settings['from_name'] = get_option( 'sell_media_from_email' );
$sell_media_email_settings['from_email'] = get_option( 'sell_media_from_name' );
$sell_media_email_settings['success_email_subject'] = get_option( 'sell_media_success_email_body' );
$sell_media_email_settings['success_email_body'] = get_option( 'sell_media_success_email_subject' );

/**
 * Update new settings with old values
 */
update_option('sell_media_email_settings', $sell_media_email_settings );

/**
 * Delete old "general settings"
 */
delete_option( 'sell_media_from_email' );
delete_option( 'sell_media_from_name' );
delete_option( 'sell_media_success_email_body' );
delete_option( 'sell_media_success_email_subject' );