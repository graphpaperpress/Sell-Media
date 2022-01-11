<?php

/**
 * Enqueues block editor styles and scripts.
 * 
 * Fires on `enqueue_block_editor_assets` hook.
 */
function sellmedia_editor_scripts() {

    $settings = sell_media_get_plugin_options();
    $checkout_page = empty( $settings->checkout_page ) ? '' : $settings->checkout_page;
    $test_mode = empty( $settings->test_mode ) ? false : $settings->test_mode;

	$asset_file = include( SELL_MEDIA_PLUGIN_DIR . 'gutenberg/build/index.asset.php');
    
    if(isset($asset_file['dependencies'][3])) {
        unset($asset_file['dependencies'][3]);
    }
	wp_enqueue_script(
        'sell_media_all_items_script',
        SELL_MEDIA_PLUGIN_URL . 'gutenberg/build/index.js',
        $asset_file['dependencies'],
        $asset_file['version']
    );

    // Scripts for masonary layout
    wp_enqueue_script(
        'sell_media_all_items_macy',
         SELL_MEDIA_PLUGIN_URL . 'js/macy.min.js',
        array('jquery'),
        $asset_file['version']
    ); 

    wp_enqueue_script( 'sell_media_jquery_cookie', SELL_MEDIA_PLUGIN_URL . 'js/jquery.cookie.js', array( 'jquery' ), SELL_MEDIA_VERSION );

    // Scripts for sell media js
    wp_enqueue_script(
        'sell_media_main_js',
         SELL_MEDIA_PLUGIN_URL . 'js/sell_media.js',
        array('jquery','sell_media_all_items_macy'),
        $asset_file['version']
    );

    wp_localize_script( 'sell_media_main_js', 'sell_media', array(
        'ajaxurl' => esc_url( admin_url( 'admin-ajax.php' ) ),
        'pluginurl' => esc_url( SELL_MEDIA_PLUGIN_URL . 'sell-media.php' ),
        'site_name' => __( get_bloginfo( 'name' ) ,'sell_media'),
        'site_url' => esc_url( site_url() ),
        'checkout_url' => esc_url( get_permalink( $checkout_page ) ),
        'currency_symbol' => empty( $settings->currency ) ? 'USD' : $settings->currency,
        'dashboard_page' => empty( $settings->dashboard_page ) ? '' : esc_url( get_permalink( $settings->dashboard_page ) ),
        'error' => array(
            'email_exists' => __( 'Sorry that email already exists or is invalid', 'sell_media' ),
            ),
        'sandbox' => ( 1 === $test_mode ) ? true : false,
        'paypal_email' => empty( $settings->paypal_email ) ? null : $settings->paypal_email,
        'thanks_page' => empty( $settings->thanks_page ) ? '' : esc_url( get_permalink( $settings->thanks_page ) ),
        'listener_url' => esc_url( add_query_arg( 'sell_media-listener', 'IPN', home_url( 'index.php' ) ) ),
        'added_to_cart' => sprintf(
            "%s! <a href='" . esc_url( get_permalink( $checkout_page ) ) . "' class='cart'>%s</a>!",
            __( 'Added', 'sell_media' ),
            __( 'Checkout now', 'sell_media' ) ),
        'cart_labels' => array(
            'name' => __( 'Name', 'sell_media' ),
            'size' => __( 'Size', 'sell_media' ),
            'license' => __( 'License', 'sell_media' ),
            'price' => __( 'Price', 'sell_media' ),
            'qty' => __( 'Qty', 'sell_media' ),
            'sub_total' => __( 'Subtotal', 'sell_media' ),
            ),
        'cart_style' => apply_filters( 'sell_media_cart_style', 'table' ),
        'tax' => empty( $settings->tax ) ? 0 : $settings->tax_rate,
        'tax_display' => empty( $settings->tax_display ) ? 'exclusive' : $settings->tax_display,
        'shipping' => apply_filters( 'sell_media_shipping', 0 ), // should PayPal force buyers add address
        'cart_error' => __( 'There was an error loading the cart data. Please contact the site owner.', 'sell_media' ),
        'checkout_text' => __( 'Checkout Now', 'sell_media' ),
        'checkout_wait_text' => __( 'Please wait...', 'sell_media' ),
        'remove_text' => __( 'Remove from Lightbox', 'sell_media' ),
        'save_text' => __( 'Save to Lightbox', 'sell_media' ),
        'currencies' => sell_media_currencies(),
        'thumbnail_layout' => $settings->thumbnail_layout
    ) );


    // Include style
    wp_enqueue_style(
        'sell_media_all_items_style',
        SELL_MEDIA_PLUGIN_URL . 'css/sell_media.css',
        array(),
        $asset_file['version']
    );

     // Include custom style for sell media items block
    wp_enqueue_style(
        'sell_media_items_block_style',
        SELL_MEDIA_PLUGIN_URL . 'gutenberg/css/editor-index.css',
        array(),
        $asset_file['version']
    );

    // Scripts for slider layout
    wp_enqueue_script(
        'sell_media_recent_items_tiny_slider',
         SELL_MEDIA_PLUGIN_URL . 'gutenberg/js/tiny-slider.js',
         array(),
        $asset_file['version']
    );           

    // Include style for slider
    wp_enqueue_style(
        'sell_media_recent_items_style',
        SELL_MEDIA_PLUGIN_URL . 'gutenberg/css/tiny-slider.css',
        array(),
        $asset_file['version']
    );

}
// Hook scripts function into block editor hook.
add_action( 'enqueue_block_editor_assets', 'sellmedia_editor_scripts' );


function sell_media_frontend_styles() {
    // Scripts for masonary layout
    wp_enqueue_style( 'sell_media_search_form_frontend', SELL_MEDIA_PLUGIN_URL . 'gutenberg/css/frontend-index.css', array() ); 
}

/* Enqueue Script */
add_action( 'wp_enqueue_scripts', 'sell_media_frontend_styles');