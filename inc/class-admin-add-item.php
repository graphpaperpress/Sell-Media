<?php
/**
 * Add New Items Admin Page
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sell Media add item tabs.
 */
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

	/**
	 * Register new metaboxes.
	 *
	 * @return void
	 */
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

	/**
	 * Main container call back.
	 *
	 * @return void
	 */
	public function main_container() {
		global $post;
		wp_enqueue_script( 'jquery-ui-tabs' );
		include sprintf( '%s/themes/admin-add-item-main-container.php', untrailingslashit( plugin_dir_path( dirname( __FILE__ ) ) ) );
	}

	/**
	 * Tabs for add item page.
	 *
	 * @return array Tabs array.
	 */
	public function get_tabs() {
		$tabs['file_upload'] = array(
			'tab_label' => __( 'File Upload', 'sell_media' ),
			'content_title' => __( 'File Upload', 'sell_media' ),
			'content_callback' => array( $this, 'file_upload_callback' ),
		);

		$tabs['description'] = array(
			'tab_label' => __( 'Description', 'sell_media' ),
			'content_title' => __( 'Description', 'sell_media' ),
			'content_callback' => array( $this, 'description_callback' ),
		);

		$tabs['settings'] = array(
			'tab_label' => __( 'Settings', 'sell_media' ),
			'content_title' => __( 'Settings', 'sell_media' ),
			'content_callback' => array( $this, 'settings_callback' ),
		);

		$tabs['stats'] = array(
			'tab_label' => __( 'Stats', 'sell_media' ),
			'content_title' => __( 'Stats', 'sell_media' ),
			'content_callback' => array( $this, 'stats_callback' ),
		);

		$tabs['advanced'] = array(
			'tab_label' => __( 'Advanced', 'sell_media' ),
			'content_title' => __( 'Advanced Options', 'sell_media' ),
			'content_callback' => array( $this, 'advanced_options_callback' ),
		);

		return apply_filters( 'sell_media_admin_new_item_tabs', $tabs );
	}

	/**
	 * File upload tab content.
	 *
	 * @param  object $post Post object.
	 * @return void
	 */
	function file_upload_callback( $post ) {
		sell_media_files_meta_box( $post );
	}

	/**
	 * Description tab content.
	 *
	 * @param  object $post Post object.
	 * @return void
	 */
	function description_callback( $post ) {
		sell_media_editor( $post );
	}

	/**
	 * Setting tab content.
	 *
	 * @param  object $post Post object.
	 * @return void
	 */
	function settings_callback( $post ) {
		sell_media_options_meta_box( $post );
	}

	/**
	 * Stat tab content.
	 *
	 * @param  object $post Post object.
	 * @return void
	 */
	function stats_callback( $post ) {
		sell_media_stats_meta_box( $post );
	}

	/**
	 * Advance options tab content.
	 *
	 * @param  object $post Post object.
	 * @return void
	 */
	function advanced_options_callback( $post ) {

		echo '<div id="sell-media-advanced-options-container">';

			echo '<div id="sell-media-tax-collections" class="sell-media-tax-wrap">';
				printf( '<h3 class="tax-title">%s</h3>', esc_html__( 'Collections', 'sell_media' ) );
				printf( '<p class="tax-description desc">%s</p>', esc_html__( 'Collections', 'sell_media' ) );
				post_categories_meta_box( $post, array( 'args' => array( 'taxonomy' => 'collection' ) ) );
			echo '</div>';

			echo '<div id="sell-media-tax-licenses" class="sell-media-tax-wrap">';
				printf( '<h3 class="tax-title">%s</h3>', esc_html__( 'Licenses', 'sell_media' ) );
				post_categories_meta_box( $post, array( 'args' => array( 'taxonomy' => 'licenses' ) ) );
			echo '</div>';

			echo '<div id="sell-media-tax-creators" class="sell-media-tax-wrap">';
				printf( '<h3 class="tax-title">%s</h3>', esc_html__( 'Creaters', 'sell_media' ) );
				post_tags_meta_box( $post, array( 'args' => array( 'taxonomy' => 'creator' ) ) );
			echo '</div>';

		echo '</div>';
	}
}
