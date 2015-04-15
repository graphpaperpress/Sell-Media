<?php

/**
 * Admin Notices
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

/**
 * All Sell Media admin notices should be placed in this function
 */
function sell_media_admin_messages() {

    $screen = get_current_screen();

    if ( $screen->id == 'edit-sell_media_item' || $screen->id == 'sell_media_item' ||  $screen->id == 'sell_media_item_page_sell_media_plugin_options' ) {

        $settings = sell_media_get_plugin_options();

        $notices = array();

        /**
         * test mode
         */
        if ( isset( $settings->test_mode ) && $settings->test_mode == 1 ){
            $notices[] = array(
                'slug' => 'test-mode',
                'message' => sprintf( __( 'Your site is currently in <a href="%1$s">test mode</a>.', 'sell_media' ), esc_url( admin_url( 'edit.php?post_type=sell_media_item&page=sell_media_plugin_options ' ) ) )
            );
        }

        /**
         * checkout
         */
        if ( isset( $settings->checkout_page ) && $settings->checkout_page == 1 || empty( $settings->checkout_page ) ){
            $notices[] = array(
                'slug' => 'checkout-page',
                'message' => sprintf( __( 'Please create a checkout page using the <code>[sell_media_checkout]</code> shortcode and assign it in your <a href="%1$s">settings</a>.', 'sell_media' ), esc_url( admin_url( 'edit.php?post_type=sell_media_item&page=sell_media_plugin_options' ) ) )
            );
        }

        /**
         * thanks
         */
        if ( isset( $settings->thanks_page ) && $settings->thanks_page == 1 || empty( $settings->thanks_page ) ){
            $notices[] = array(
                'slug' => 'thanks-page',
                'message' => sprintf( __( 'Please create a thanks page using the <code>[sell_media_thanks]</code> shortcode and assign it in your <a href="%1$s">settings</a>.', 'sell_media' ), esc_url( admin_url( 'edit.php?post_type=sell_media_item&page=sell_media_plugin_options' ) ) )
            );
        }

        /**
         * PayPal email
         */
        if ( empty( $settings->paypal_email ) ){
            $notices[] = array(
                'slug' => 'paypal-email',
                'message' => sprintf( __( 'Please set a PayPal email in your <a href="%1$s">payment settings</a>.', 'sell_media' ), esc_url( admin_url( 'edit.php?post_type=sell_media_item&page=sell_media_plugin_options&tab=sell_media_payment_settings' ) ) )
            );
        }

        /**
         * Updates available for extensions
         */
        $plugins = get_plugins();
        $update_plugins = array();

        foreach ( array_keys( $plugins ) as $plugin ) {
            if ( preg_match( '/sell-media-(.*)$/', $plugin, $matches ) ) {
                if ( $plugins[$plugin]['Version'] < 2 ) {
                    $update_plugins[] = $plugins[$plugin]['Name'];
                }
            }
        }

        if ( ! empty( $update_plugins ) ) {
            $notices[] = array(
                'slug' => 'sell-media-updates',
                'message' => sprintf( __( 'Important updates are now available for %1$s. <a href="%2$s" target="_blank">Learn more</a>.', 'sell_media' ), implode( ', ', $update_plugins ), 'http://graphpaperpress.com/docs/sell-media/#updates' )
            );
        }

        /**
         * Available size
         */
        if ( isset( $_GET['action'] ) && $_GET['action'] == 'edit' && isset( $_GET['post'] ) ){
            global $post_type;

            if ( $post_type == 'sell_media_item' ){

                global $post;

                // don't show notice on packages
                if ( ! Sell_Media()->products->is_package( $post->ID ) ) {

                    if ( Sell_Media()->products->has_image_attachments( $post->ID ) ) {

                        $images_obj = Sell_Media()->images;

                        $download_sizes = $images_obj->get_downloadable_size( $post->ID, null, true );

                        if ( ! empty( $download_sizes['unavailable'] ) ){

                            $og_size = $images_obj->get_original_image_size( $post->ID );

                            if ( sell_media_has_multiple_attachments( $post->ID ) ) {

                                $message = __( 'Some of these images are smaller than the size(s) that you are selling, so these sizes won\'t be available for sale. Either upload higher resolution images, or decrease the sizes that you\'re selling.', 'sell_media' );

                            } else {

                                $message = sprintf( __( 'This image (%1$s x %2$s) is smaller than the size(s) that you are selling, so these sizes won\'t be available for sale. Either upload higher resolution images, or decrease the sizes that you\'re selling.<br />', 'sell_media' ), $og_size['original']['width'], $og_size['original']['height'] );
                                foreach( $download_sizes['unavailable'] as $unavailable ){
                                    $message .= $unavailable['name'] . ' (' . $unavailable['width'] . ' x ' . $unavailable['height'] . ')<br />';
                                }

                            }

                            $notices[] = array(
                                'slug' => 'download-sizes',
                                'message' => $message
                            );

                        }
                    }
                }
            }
        }

        foreach( $notices as $notice ){
            add_settings_error( 'sell-media-notices', 'sell-media-notice-' . $notice['slug'], $notice['message'], 'updated' );
        }

        settings_errors( 'sell-media-notices' );
    }
}
add_action( 'admin_notices', 'sell_media_admin_messages' );