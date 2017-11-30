<?php

/**
 * Scripts
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
	$checkout_page = empty( $settings->checkout_page ) ? '' : $settings->checkout_page;
	$test_mode = empty( $settings->test_mode ) ? false : $settings->test_mode;

	wp_enqueue_script( 'sell_media', SELL_MEDIA_PLUGIN_URL . 'dist/js/sell_media.min.js', array( 'jquery' ), SELL_MEDIA_VERSION );
	wp_enqueue_style( 'sell_media', SELL_MEDIA_PLUGIN_URL . 'dist/css/sell_media.min.css', array( 'dashicons' ), SELL_MEDIA_VERSION );

	// Masonry
	if ( is_customize_preview() || ( isset( $settings->thumbnail_layout ) && 'sell-media-masonry' === $settings->thumbnail_layout ) ) {
		wp_enqueue_script( 'sell_media_masonry', SELL_MEDIA_PLUGIN_URL . 'js/macy.min.js', array( 'jquery' ), SELL_MEDIA_VERSION, true );
		wp_add_inline_script( 'sell_media_masonry', '

			var galleries = document.querySelectorAll(".sell-media-grid-item-masonry-container");
			var macyInstances = [];
			var macyOptions = {
				trueOrder: false,
				waitForImages: true,
				margin: 10,
				columns: 4,
				breakAt: {
					1200: 4,
					940: 3,
					520: 1
				}
			};

			for (var i = 0; i < galleries.length; i++) {
				var newId = "sell-media-instance-" + i;
				galleries[i].id = newId;
				macyOptions.container = "#" + newId;
				macyInstances.push(Macy(macyOptions));
			}'
		);
	}

	wp_localize_script( 'sell_media', 'sell_media', array(
		'ajaxurl' => esc_url( admin_url( 'admin-ajax.php' ) ),
		'pluginurl' => esc_url( SELL_MEDIA_PLUGIN_URL . 'sell-media.php' ),
		'site_name' => esc_html( get_bloginfo( 'name' ) ),
		'site_url' => esc_url( site_url() ),
		'checkout_url' => esc_url( get_permalink( $checkout_page ) ),
		'currency_symbol' => empty( $settings->currency ) ? 'USD' : $settings->currency,
		'dashboard_page' => empty( $settings->dashboard_page ) ? '' : esc_url( get_permalink( $settings->dashboard_page ) ),
		'error' => array(
			'email_exists' => esc_html__( 'Sorry that email already exists or is invalid', 'sell_media' ),
			),
		'sandbox' => ( 1 === $test_mode ) ? true : false,
		'paypal_email' => empty( $settings->paypal_email ) ? null : $settings->paypal_email,
		'thanks_page' => empty( $settings->thanks_page ) ? '' : esc_url( get_permalink( $settings->thanks_page ) ),
		'listener_url' => esc_url( add_query_arg( 'sell_media-listener', 'IPN', home_url( 'index.php' ) ) ),
		'added_to_cart' => sprintf(
			"%s! <a href='" . esc_url( get_permalink( $checkout_page ) ) . "' class='cart'>%s</a>!",
			esc_html__( 'Added', 'sell_media' ),
			esc_html__( 'Checkout now', 'sell_media' )
		),
		'cart_labels' => array(
			'name' => esc_html__( 'Name', 'sell_media' ),
			'size' => esc_html__( 'Size', 'sell_media' ),
			'license' => esc_html__( 'License', 'sell_media' ),
			'price' => esc_html__( 'Price', 'sell_media' ),
			'qty' => esc_html__( 'Qty', 'sell_media' ),
			'sub_total' => esc_html__( 'Subtotal', 'sell_media' ),
			),
		'cart_style' => apply_filters( 'sell_media_cart_style', 'table' ),
		'tax' => empty( $settings->tax ) ? 0 : $settings->tax_rate,
		'tax_display' => empty( $settings->tax_display ) ? 'exclusive' : $settings->tax_display,
		'shipping' => apply_filters( 'sell_media_shipping', 0 ), // should PayPal force buyers add address
		'cart_error' => esc_html__( 'There was an error loading the cart data. Please contact the site owner.', 'sell_media' ),
		'checkout_text' => esc_html__( 'Checkout Now', 'sell_media' ),
		'checkout_wait_text' => esc_html__( 'Please wait...', 'sell_media' ),
		'remove_text' => esc_html__( 'Remove from Lightbox', 'sell_media' ),
		'save_text' => esc_html__( 'Save to Lightbox', 'sell_media' ),
		'text_lightbox_remove_all' => esc_html__( 'Remove all', 'sell_media' ),
		'text_lightbox_empty' => esc_html__( 'Your lightbox is empty.', 'sell_media' ),
		'search_labels' => array(
			'search' => esc_html__( 'Search', 'sell_media' ),
			'no_results' => esc_html__( 'No results', 'sell_media' ),
		),
		'lightbox_labels' => array(
			'remove_all' 	=> esc_html__( 'Remove all', 'sell_media' ),
			'empty' 		=> esc_html__( 'Your lightbox is empty', 'sell_media' ),
			'save'			=> esc_html__( 'Save to Lightbox', 'sell_media' ),
			'remove' 		=> esc_html__( 'Remove from Lightbox', 'sell_media' ),
		),
		'currencies' => sell_media_currencies(),
	) );

	do_action( 'sell_media_scripts_hook' );
}
add_action( 'wp_enqueue_scripts', 'sell_media_scripts', 15 );
