<?php

/**
 * All Sell Media admin notices should be placed in this function
 */
function sell_media_admin_messages() {

    $screen = get_current_screen();

    if ( $screen->id == 'edit-sell_media_item' || $screen->id == 'sell_media_item' ||  $screen->id == 'sell_media_item_page_sell_media_plugin_options' || $screen->id == 'sell_media_item_page_sell_media_add_bulk' ) {

        // New
        $settings = sell_media_get_plugin_options();

        $notices = array();

        /**
         * test mode
         */
        if ( isset( $settings->test_mode ) && $settings->test_mode == 1 ){
            $notices[] = array(
                'slug' => 'test-mode',
                'message' => 'Your site is currently in <a href="' . admin_url('edit.php?post_type=sell_media_item&page=sell_media_plugin_options') . '">test mode</a>.'
                );
        }

        /**
         * checkout
         */
        if ( isset( $settings->checkout_page ) && $settings->checkout_page == 1 || empty( $settings->checkout_page ) ){
            $notices[] = array(
                'slug' => 'checkout-page',
                'message' => 'Please create a checkout page using the <code>[sell_media_checkout]</code> shortcode and assign it in your <a href="'.admin_url('edit.php?post_type=sell_media_item&page=sell_media_plugin_options').'">settings</a>.'
                );
        }

        /**
         * thanks
         */
        if ( isset( $settings->thanks_page ) && $settings->thanks_page == 1 || empty( $settings->thanks_page ) ){
            $notices[] = array(
                'slug' => 'thanks-page',
                'message' => 'Please create a thanks page using the <code>[sell_media_thanks]</code> shortcode and assign it in your <a href="'.admin_url('edit.php?post_type=sell_media_item&page=sell_media_plugin_options').'">settings</a>.'
                );
        }

        /**
         * PayPal email
         */
        if ( empty( $settings->paypal_email ) ){
            $notices[] = array(
                'slug' => 'paypal-email',
                'message' => 'Please set a PayPal email in your <a href="'.admin_url('edit.php?post_type=sell_media_item&page=sell_media_plugin_options&tab=sell_media_payment_settings').'">payment settings</a>.'
                );
        }

        /**
         * price group
         */
        if ( empty( $settings->default_price_group ) ){
            $notices[] = array(
                'slug' => 'price-group',
                'message' => 'Without a <a href="'.admin_url('edit.php?post_type=sell_media_item&page=sell_media_plugin_options&tab=sell_media_size_settings&term_parent=new_term').'">default price group</a> set you will need to manually set a price group per item.'
                );
        }

        foreach( $notices as $notice ){
            add_settings_error( 'sell-media-notices', 'sell-media-notice-' . $notice['slug'], $notice['message'], 'updated' );
        }


        settings_errors( 'sell-media-notices' );
    }
}
add_action( 'admin_notices', 'sell_media_admin_messages' );