<?php

/**
 * Scripts
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Scripts
 *
 * Enqueues all necessary scripts in the WP Admin to run Sell Media
 *
 * @since 1.8.5
 * @return void
 */
function sell_media_scripts( $hook ) {

    $settings = sell_media_get_plugin_options();

    // enqueue
    wp_enqueue_script( 'sell_media_jquery_cookie', SELL_MEDIA_PLUGIN_URL . 'js/jquery.cookie.js', array( 'jquery'), SELL_MEDIA_VERSION );
    wp_enqueue_script( 'sell_media', SELL_MEDIA_PLUGIN_URL . 'js/sell_media.js', array( 'jquery', 'sell_media_jquery_cookie' ), SELL_MEDIA_VERSION );
    wp_enqueue_script( 'sellMediaCart', SELL_MEDIA_PLUGIN_URL . 'js/sell_media_cart.js', array( 'jquery' ), SELL_MEDIA_VERSION );
    wp_enqueue_style( 'sell_media', SELL_MEDIA_PLUGIN_URL . 'css/sell_media.css', array( 'dashicons' ), SELL_MEDIA_VERSION );

    if ( isset( $settings->style ) && '' != $settings->style ) {
        wp_enqueue_style( 'sell-media-style', SELL_MEDIA_PLUGIN_URL . 'css/sell_media-' . $settings->style . '.css' );
    } else {
        wp_enqueue_style( 'sell-media-style', SELL_MEDIA_PLUGIN_URL . 'css/sell_media-light.css' );
    }

    wp_localize_script( 'sell_media', 'sell_media', array(
        'ajaxurl' => esc_url( admin_url( 'admin-ajax.php' ) ),
        'pluginurl' => esc_url( SELL_MEDIA_PLUGIN_URL . 'sell-media.php' ),
        'site_name' => esc_html( get_bloginfo( 'name' ) ),
        'site_url' => esc_url( site_url() ),
        'checkout_url' => empty( $settings->checkout_page ) ? null : esc_url( get_permalink( $settings->checkout_page ) ),
        'currency_symbol' => $settings->currency,
        'dashboard_page' => esc_url( get_permalink( $settings->dashboard_page ) ),
        'error' => array(
            'email_exists' => __( 'Sorry that email already exists or is invalid', 'sell_media' )
            ),
        'sandbox' => ( $settings->test_mode == 1 ) ? true : false,
        'paypal_email' => ( empty( $settings->paypal_email ) ) ? null : $settings->paypal_email,
        'thanks_page' => esc_url( get_permalink( $settings->thanks_page ) ),
        'listener_url' => esc_url( add_query_arg( 'sell_media-listener', 'IPN', home_url( 'index.php' ) ) ),
        'added_to_cart' => sprintf(
            "%s! <a href='" . esc_url( get_permalink( $settings->checkout_page ) ) . "' class='cart'>%s</a>!",
            __( 'Added', 'sell_media' ),
            __( 'Checkout now','sell_media' ) ),
        'cart_labels' => array(
            'name' => __( 'Name', 'sell_media' ),
            'size' => __( 'Size', 'sell_media' ),
            'license' => __( 'License', 'sell_media' ),
            'price' => __( 'Price', 'sell_media' ),
            'qty' => __( 'Qty', 'sell_media' ),
            'sub_total' => __( 'Subtotal', 'sell_media' )
            ),
        'cart_style' => apply_filters( 'sell_media_cart_style', 'table' ),
        'tax' => ( empty( $settings->tax ) ) ? 0 : $settings->tax_rate,
        'shipping' => apply_filters( 'sell_media_shipping', 0 ), // should PayPal force buyers add address
        'cart_error' => __( 'There was an error loading the cart data. Please contact the site owner.', 'sell_media' ),
        'checkout_text' => __( 'Checkout Now', 'sell_media' ),
        'checkout_wait_text' => __( 'Please wait...', 'sell_media' ),
        'remove_text' => __( 'Remove from Lightbox', 'sell_media' ),
        'save_text' => __( 'Save to Lightbox', 'sell_media' ),
        'currencies' => sell_media_currencies()
    ) );

    do_action( 'sell_media_scripts_hook' );
}
add_action( 'wp_enqueue_scripts', 'sell_media_scripts' );
