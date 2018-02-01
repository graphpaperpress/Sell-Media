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
	$currency = empty( $settings->currency ) ? 'USD' : $settings->currency;
	$test_mode = empty( $settings->test_mode ) ? false : $settings->test_mode;
	$post_type_data = get_post_type_object( 'sell_media_item' );
	$post_type_slug = $post_type_data->rewrite['slug'];

	wp_enqueue_script( 'sell_media', SELL_MEDIA_PLUGIN_URL . 'dist/js/sell_media.js', array( 'jquery' ), SELL_MEDIA_VERSION, true );
	wp_enqueue_style( 'sell_media', SELL_MEDIA_PLUGIN_URL . 'dist/css/sell_media.css', array( 'dashicons' ), SELL_MEDIA_VERSION );

	// Masonry
	if ( is_customize_preview() || ( isset( $settings->thumbnail_layout ) && 'sell-media-masonry' === $settings->thumbnail_layout ) ) {
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

	$localize_script_args = array(
		'ajaxurl' => esc_url( admin_url( 'admin-ajax.php' ) ),
		'pluginurl' => esc_url( SELL_MEDIA_PLUGIN_URL . 'sell-media.php' ),
		'site_name' => esc_html( get_bloginfo( 'name' ) ),
		'site_url' => esc_url( site_url() ),
		'posts_per_page' => get_option( 'posts_per_page', 9 ),
		'archive_path' => esc_attr( $post_type_slug ),
		'checkout_path' => get_post_field( 'post_name', $settings->checkout_page ),
		'thanks_path' => get_post_field( 'post_name', $settings->thanks_page ),
		'dashboard_path' => get_post_field( 'post_name', $settings->dashboard_page ),
		'login_path' => get_post_field( 'post_name', $settings->login_page ),
		'search_path' => get_post_field( 'post_name', $settings->search_page ),
		'lightbox_path' => get_post_field( 'post_name', $settings->lightbox_page ),
		'currency' => $currency,
		'currency_symbol' => sell_media_get_currency_symbol( $currency ),
		'dashboard_page' => empty( $settings->dashboard_page ) ? '' : esc_url( get_permalink( $settings->dashboard_page ) ),
		'error' => array(
			'email_exists' => esc_html__( 'Sorry that email already exists or is invalid', 'sell_media' ),
			),
		'sandbox' => ( '1' === $test_mode ) ? true : false,
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
			'product' => esc_html__( 'Product', 'sell_media' ),
			'type' => esc_html__( 'Type', 'sell_media' ),
			'description' => esc_html__( 'Description', 'sell_media' ),
			'size' => esc_html__( 'Size', 'sell_media' ),
			'license' => esc_html__( 'License', 'sell_media' ),
			'price' => esc_html__( 'Price', 'sell_media' ),
			'qty' => esc_html__( 'Qty', 'sell_media' ),
			'sub_total' => esc_html__( 'Subtotal', 'sell_media' ),
			'usage_fee' => esc_html__( 'Usage Fee', 'sell_media' ),
			'apply' => esc_html__( 'Apply', 'sell_media' ),
			'shipping' => esc_html__( 'Shipping', 'sell_media' ),
			'tax' => esc_html__( 'Tax', 'sell_media' ),
			'total' => esc_html__( 'Total', 'sell_media' ),
			'add_to_cart' => esc_html__( 'Add to cart', 'sell_media' ),
			'added_to_cart' => esc_html__( 'Added!', 'sell_media' ),
			'view_cart' => esc_html__( 'View cart', 'sell_media' ),
			'choose' => esc_html__( 'Choose', 'sell_media' ),
			'prev' => esc_html__( 'Previous', 'sell_media' ),
			'next' => esc_html__( 'Next', 'sell_media' ),
			'empty' => esc_html__( 'Your cart is empty', 'sell_media' ),
			'visit' => esc_html__( 'Visit the store', 'sell_media' ),
			'continue_shopping' => esc_html__( 'Continue shopping', 'sell_media' )
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
		'quick_view_label' => esc_html__( 'Quick View', 'sell_media' ),
		'search_labels' => array(
			'search' => esc_html__( 'Search', 'sell_media' ),
			'no_results' => esc_html__( 'Sorry, no results for', 'sell_media' ),
			'we_found' => esc_html__( 'We found', 'sell_media' ),
			'results_for' => esc_html__( 'results for', 'sell_media' ),
			'all' => esc_html__( 'All', 'sell_media' ),
			'open_filters' => esc_html__( 'Open Filters', 'sell_media' ),
			'close_filters' => esc_html__( 'Close Filters', 'sell_media' ),
			'colors' => esc_html__( 'Colors', 'sell_media' ),
			'sort' => esc_html__( 'Sort By', 'sell_media' ),
			'popular' => esc_html__( 'Popularity', 'sell_media' ),
			'date' => esc_html__( 'Date', 'sell_media' ),
			'name' => esc_html__( 'Name', 'sell_media' ),
			'orientation' => esc_html__( 'Orientation', 'sell_media' ),
			'horizontal' => esc_html__( 'Horizontal', 'sell_media' ),
			'vertical' => esc_html__( 'Vertical', 'sell_media' ),
			'panoramic' => esc_html__( 'Panoramic', 'sell_media' ),
			'collections' => esc_html__( 'Collections', 'sell_media' ),
			'download' => esc_html__( 'Download', 'sell_media' ),
			'help' => esc_html__( 'Help', 'sell_media' ),
			'search_tips' => esc_html__( 'Search Tips', 'sell_media' ),
			'tips' => array(
				0 => esc_html__( 'Separate keywords with a comma', 'sell_media' ),
				1 => esc_html__( 'Use fewer keywords for more results', 'sell_media' ),
				2 => esc_html__( 'Use negative keywords (like -cars) to exclude cars from search results', 'sell_media' ),
			),
			'back' => esc_html__( 'Back to search', 'sell_media' ),
		),
		'lightbox_labels' => array(
			'add_all' => esc_html__( 'Add all to cart', 'sell_media' ),
			'remove_all' => esc_html__( 'Remove all', 'sell_media' ),
			'empty'      => esc_html__( 'Your lightbox is empty', 'sell_media' ),
			'save'       => esc_html__( 'Save to Lightbox', 'sell_media' ),
			'saved'      => esc_html__( 'Saved!', 'sell_media' ),
			'view'       => esc_html__( 'View your Lightbox', 'sell_media' ),
			'remove'     => esc_html__( 'Remove from Lightbox', 'sell_media' ),
			'lightbox'   => esc_html__( 'Lightbox', 'sell_media' ),
		),
		'layout' => empty( $settings->layout ) ? 'sell-media-single-one-col' : $settings->layout,
		'thumbnail_crop' => empty( $settings->thumbnail_crop ) ? 'medium' : $settings->thumbnail_crop,
		'thumbnail_layout' => empty( $settings->thumbnail_layout ) ? 'sell-media-three-col' : $settings->thumbnail_layout,
		'title' => empty( $settings->titles ) ? 0 : $settings->titles,
		'breadcrumbs' => empty( $settings->breadcrumbs ) ? 0 : $settings->breadcrumbs,
		'quick_view' => empty( $settings->quick_view ) ? 0 : $settings->quick_view,
		'quick_view_style' => empty( $settings->quick_view_style ) ? 0 : $settings->quick_view_style,
		'file_info' => empty( $settings->file_info ) ? 0 : $settings->file_info,
		'search_relation' => empty( $settings->search_relation ) ? 'or' : $settings->search_relation,
		'terms_and_conditions' => empty( $settings->terms_and_conditions ) ? '' : $settings->terms_and_conditions,
		'plugin_credit' => empty( $settings->plugin ) ? 0 : $settings->plugin_credit,
		//'currencies' => sell_media_currencies(),
		'licensing_enabled' => sell_media_licensing_enabled(),
		'licensing_markup_taxonomies' => sell_media_get_license_markup_taxonomies(),
		'nonce' => wp_create_nonce( 'wp_rest' ),
	);

	wp_localize_script( 'sell_media', 'sell_media', apply_filters( 'sell_media_localize_script', $localize_script_args ) );

	do_action( 'sell_media_scripts_hook' );
}
add_action( 'wp_enqueue_scripts', 'sell_media_scripts', 15 );
