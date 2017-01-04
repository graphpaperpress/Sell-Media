<?php
/**
 * Admin Notices
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SellMediaAdminAddItem {

	/**
	 * Sell Media setting.
	 *
	 * @var object
	 */
	private $settings;

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->settings = sell_media_get_plugin_options();

		add_action( 'add_meta_boxes', array( $this, 'register_metaboxes' ), 999 );
	}

	public function register_metaboxes() {
		add_meta_box( 'sell-media-main-container', __( 'My Meta Box', 'sell_media' ), array( $this, 'main_container' ), 'sell_media_item', 'normal', 'high' );
		remove_meta_box( 'files_meta_box', 'sell_media_item', 'normal' );
		remove_meta_box( 'stats_meta_box', 'sell_media_item', 'normal' );
		remove_meta_box( 'options_meta_box', 'sell_media_item', 'normal' );
		remove_meta_box( 'licensesdiv', 'sell_media_item', 'side' );
		remove_meta_box( 'collectiondiv', 'sell_media_item', 'side' );
		remove_meta_box( 'postimagediv', 'sell_media_item', 'side' );
		remove_meta_box( 'authordiv', 'sell_media_item', 'normal' );
		remove_action( 'edit_form_advanced', 'sell_media_editor' );
	}

	public function main_container() {
		global $post;
		wp_enqueue_script( 'jquery-ui-tabs' );
		include sprintf( '%s/themes/admin-add-item-main-container.php', untrailingslashit( plugin_dir_path( dirname(__FILE__) ) ) );
	}
}
