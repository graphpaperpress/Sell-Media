<?php

/**
 * All Sell Media admin notices should be placed in this function
 */
function sell_media_admin_messages() {

    $general = get_option('sell_media_general_settings');
    $payment = get_option('sell_media_payment_settings');
    $size = get_option('sell_media_size_settings');

    $notices = array();

    /**
     * test mode
     */
    if ( $general['test_mode'] == 1 ){
        $notices[] = array(
            'slug' => 'test-mode',
            'message' => 'Your site is currently in <a href="">test mode</a>.'
            );
    }

    /**
     * checkout
     */
    if ( $general['checkout_page'] == 1 || empty( $general )){
        $notices[] = array(
            'slug' => 'checkout-page',
            'message' => 'Please create a checkout page using the <code>[sell_media_checkout]</code> shortcode and assign it in your <a href="'.admin_url('edit.php?post_type=sell_media_item&page=sell_media_plugin_options').'">settings</a>'
            );
    }

    /**
     * thanks
     */
    if ( $general['thanks_page'] == 1 || empty( $general )){
        $notices[] = array(
            'slug' => 'thanks-page',
            'message' => 'Please create a thanks page using the <code>[sell_media_thanks]</code> shortcode and assign it in your <a href="'.admin_url('edit.php?post_type=sell_media_item&page=sell_media_plugin_options').'">settings</a>'
            );
    }

    /**
     * paypal email
     */
    if ( empty( $payment['paypal_email'] ) ){
        $notices[] = array(
            'slug' => 'paypal-email',
            'message' => 'Please set a paypal email in your <a href="'.admin_url('edit.php?post_type=sell_media_item&page=sell_media_plugin_options&tab=sell_media_payment_settings').'">payment settings</a>'
            );
    }

    /**
     * price group
     */
    if ( empty( $size['default_price_group'] ) ){
        $notices[] = array(
            'slug' => 'price-group',
            'message' => 'Without a <a href="'.admin_url('edit.php?post_type=sell_media_item&page=sell_media_plugin_options&tab=sell_media_size_settings&term_parent=new_term').'">default price group</a> set you will need to manually set a price group per item.'
            );
    }

    /**
     * Available size
     */
    if ( isset( $_GET['action'] ) && $_GET['action'] == 'edit' ){
        global $post_type;

        if ( $post_type == 'sell_media_item' ){

            global $post;
            $download_sizes = sell_media_get_downloadable_size( $post->ID, null, true );

            if ( ! empty( $download_sizes['unavailable'] ) ){
                $og_size = sell_media_original_image_size( $post->ID, $echo=false );
                $message = null;
                foreach( $download_sizes['unavailable'] as $unavailable ){
                    $message .= 'This image will not be available in the size ' . $unavailable['name'] . ' (' . $unavailable['width'] . ' x ' . $unavailable['width'] . ') ';
                }
                $message .= ', your original file is ' . $og_size['original']['width'] . ' x ' . $og_size['original']['height'] . '.';

                $notices[] = array(
                    'slug' => 'download-sizes',
                    'message' => $message
                    );
            }
        }
    }

    foreach( $notices as $notice ){
        add_settings_error( 'sell-media-notices', 'sell-media-notice-' . $notice['slug'], $notice['message'], 'updated' );
    }

    settings_errors( 'sell-media-notices' );
}
add_action( 'admin_notices', 'sell_media_admin_messages' );