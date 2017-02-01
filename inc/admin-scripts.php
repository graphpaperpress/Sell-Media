<?php

/**
 * Admin Scripts
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Admin Scripts
 *
 * Enqueues all necessary scripts in the WP Admin to run Sell Media
 *
 * @since 1.8.5
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function sell_media_admin_scripts( $hook ) {

	if ( sell_media_is_sell_media_post_type_page() || 'post.php' == $hook || 'post-new.php' == $hook ) {
		wp_enqueue_style( 'sell_media-admin', SELL_MEDIA_PLUGIN_URL . 'css/sell_media-admin.css', array( 'thickbox' ), SELL_MEDIA_VERSION );
		wp_enqueue_script( 'sell_media-admin-items', SELL_MEDIA_PLUGIN_URL . 'js/admin-items.js', array( 'jquery', 'inline-edit-post' ), SELL_MEDIA_VERSION );

		wp_register_script( 'sell_media-admin-media-uploader', SELL_MEDIA_PLUGIN_URL . 'js/admin-media-uploader.js', array( 'jquery', 'plupload-handlers' ), SELL_MEDIA_VERSION );

		$translation_array = array(
			'ajax' => admin_url( 'admin-ajax.php' ),
			'drag_drop_nonce' => wp_create_nonce( 'sell-media-drag-drop-nonce' )
		);
		wp_localize_script( 'sell_media-admin-media-uploader', 'sell_media_drag_drop_uploader', $translation_array );
		wp_enqueue_script( 'sell_media-admin-media-uploader' );

		if ( sell_media_is_license_page() || sell_media_is_license_term_page() ) {
			wp_enqueue_script( 'jquery-ui-slider' );
		}
	}

	if ( sell_media_is_reports_page() ) {
		wp_enqueue_script( 'google_charts', 'https://www.google.com/jsapi', array( 'jquery' ), SELL_MEDIA_VERSION );
	}

	do_action( 'sell_media_admin_scripts_hook' );
}
add_action( 'admin_enqueue_scripts', 'sell_media_admin_scripts' );
