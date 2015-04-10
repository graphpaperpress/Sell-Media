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

    if ( $screen->id == 'edit-sell_media_item' || $screen->id == 'sell_media_item' ||  $screen->id == 'sell_media_item_page_sell_media_plugin_options' || $screen->id == 'sell_media_item_page_sell_media_add_bulk' || $screen->id == 'sell_media_item_page_sell_media_add_package' ) {

        $settings = sell_media_get_plugin_options();

        $notices = array();

        /**
         * test mode
         */
        if ( isset( $settings->test_mode ) && $settings->test_mode == 1 ){
            $notices[] = array(
                'slug' => 'test-mode',
                'message' => 'Your site is currently in <a href="' . admin_url( 'edit.php?post_type=sell_media_item&page=sell_media_plugin_options' ) . '">test mode</a>.'
                );
        }

        /**
         * checkout
         */
        if ( isset( $settings->checkout_page ) && $settings->checkout_page == 1 || empty( $settings->checkout_page ) ){
            $notices[] = array(
                'slug' => 'checkout-page',
                'message' => 'Please create a checkout page using the <code>[sell_media_checkout]</code> shortcode and assign it in your <a href="' . admin_url( 'edit.php?post_type=sell_media_item&page=sell_media_plugin_options' ) . '">settings</a>.'
                );
        }

        /**
         * thanks
         */
        if ( isset( $settings->thanks_page ) && $settings->thanks_page == 1 || empty( $settings->thanks_page ) ){
            $notices[] = array(
                'slug' => 'thanks-page',
                'message' => 'Please create a thanks page using the <code>[sell_media_thanks]</code> shortcode and assign it in your <a href="' . admin_url( 'edit.php?post_type=sell_media_item&page=sell_media_plugin_options' ) . '">settings</a>.'
                );
        }

        /**
         * PayPal email
         */
        if ( empty( $settings->paypal_email ) ){
            $notices[] = array(
                'slug' => 'paypal-email',
                'message' => 'Please set a PayPal email in your <a href="' . admin_url( 'edit.php?post_type=sell_media_item&page=sell_media_plugin_options&tab=sell_media_payment_settings' ) . '">payment settings</a>.'
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
                $is_package = get_post_meta( $post->ID, '_sell_media_is_package', true );
                if ( ! $is_package ) {

                    if ( Sell_Media()->products->mimetype_is_image( get_post_meta( $post->ID, '_sell_media_attachment_id', true ) ) ){

                        $images_obj = Sell_Media()->images;

                        $download_sizes = $images_obj->get_downloadable_size( $post->ID, null, true );

                        if ( ! empty( $download_sizes['unavailable'] ) ){

                            $og_size = $images_obj->get_original_image_size( $post->ID );

                            if ( sell_media_has_multiple_attachments( $post->ID ) ) {

                                $message = __( 'Some of these images are smaller than the size(s) that you are selling, so these sizes won\'t be available for sale.', 'sell_media' );

                            } else {

                                $message = sprintf( __( 'This image (%1$s x %2$s) is smaller than the size(s) that you are selling, so these sizes won\'t be available for sale. <br />', 'sell_media' ), $og_size['original']['width'], $og_size['original']['height'] );
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