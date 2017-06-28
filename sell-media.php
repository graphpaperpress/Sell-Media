<?php
/**
 * Plugin Name: Sell Media
 * Plugin URI: http://graphpaperpress.com/plugins/sell-media/
 * Description: A plugin for selling photos, prints and other downloads.
 * Version: 2.3.5
 * Author: Graph Paper Press
 * Author URI: http://graphpaperpress.com
 * Author Email: support@graphpaperpress.com
 * Text Domain: sell_media
 * Domain Path: languages
 * License: GPL2
 *
 * Copyright 2015 GRAPH PAPER PRESS (email: support@graphpaperpress.com)
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License or license.txt for more details.
 *
 * @package SellMedia
 * @category Core
 * @author Thad Allender
 * @version 2.3.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SellMedia' ) ) :

	/**
	 * Main SellMedia Class (singleton).
	 *
	 * @since 1.8.5
	 */
	final class SellMedia {

		/**
		 * The single instance of the class.
		 *
		 * @var SellMedia There's only one instance
		 * @since 1.8.5
		 */
		private static $instance;

		/**
		 * Sell_Media Customer Object.
		 *
		 * @var object
		 * @since 1.8.5
		 */
		public $customer;

		/**
		 * Sell_Media Download Object.
		 *
		 * @var object
		 * @since 1.8.5
		 */
		public $download;

		/**
		 * Sell_Media Images Object.
		 *
		 * @var object
		 * @since 1.8.5
		 */
		public $images;

		/**
		 * Sell_Media Payments Object.
		 *
		 * @var object
		 * @since 1.8.5
		 */
		public $payments;

		/**
		 * Sell_Media Products Object.
		 *
		 * @var object
		 * @since 1.8.5
		 */
		public $products;

		/**
		 * Sell_Media Search Object.
		 *
		 * @var object
		 * @since 1.8.5
		 */
		public $search;


		/**
		 * Main SellMedia Instance.
		 *
		 * Ensures that only one instance of SellMedia exists in memory.
		 *
		 * @since 1.8.5
		 * @static
		 * @staticvar array $instance
		 * @uses SellMedia::constants() Setup the constants needed
		 * @uses SellMedia::includes() Include the required files
		 * @uses SellMedia::textdomain() Load textdomain for translation
		 * @see SellMedia()
		 * @return The one, the only SellMedia
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof SellMedia ) ) {
				self::$instance = new SellMedia;
				self::$instance->constants();
				self::$instance->includes();
				self::$instance->textdomain();
				self::$instance->session        = new SellMediaSession();
				self::$instance->customer       = new SellMediaCustomer();
				self::$instance->download       = new SellMediaDownload();
				self::$instance->images         = new SellMediaImages();
				self::$instance->layouts        = new SellMediaLayouts();
				self::$instance->mail         	= new SellMediaMail();
				self::$instance->payments       = new SellMediaPayments();
				self::$instance->products       = new SellMediaProducts();
				self::$instance->queries        = new SellMediaQueries();
				self::$instance->search         = new SellMediaSearch();
				self::$instance->upgrades 		= new SellMediaUpgrades();

				if ( self::$instance->is_request( 'admin' ) ) {
					self::$instance->notices        = new SellMediaAdminNotices();
					self::$instance->admin_search   = new SellMediaAdminSearch();
					self::$instance->price_listings = new Sell_Media_Price_Listings();
					self::$instance->admin_add_item = new SellMediaAdminAddItem();
				}

				// Set cart global variable.
				if ( self::$instance->is_request( 'frontend' ) ) {
					$GLOBALS['sm_cart'] = new SellMediaCart();
				}
			}
			return self::$instance;
		}

		/**
		 * Throw error on object clone.
		 *
		 * @since 1.8.5
		 * @access protected
		 * @return void
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'sell_media' ), '1.8.5' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @since 1.8.5
		 * @access protected
		 * @return void
		 */
		public function __wakeup() {
			// Unserializing instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'sell_media' ), '1.8.5' );
		}

		/**
		 * Constants for the plugin.
		 *
		 * @access private
		 * @since 1.8.5
		 * @return void
		 */
		private function constants() {

			// Plugin version.
			if ( ! defined( 'SELL_MEDIA_VERSION' ) ) {
				define( 'SELL_MEDIA_VERSION', '2.3.5' );
			}

			// Plugin Folder Path.
			if ( ! defined( 'SELL_MEDIA_PLUGIN_DIR' ) ) {
				define( 'SELL_MEDIA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			}

			// Plugin Folder URL.
			if ( ! defined( 'SELL_MEDIA_PLUGIN_URL' ) ) {
				define( 'SELL_MEDIA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			}

			// Plugin Root File.
			if ( ! defined( 'SELL_MEDIA_PLUGIN_FILE' ) ) {
				define( 'SELL_MEDIA_PLUGIN_FILE', __FILE__ );
			}

		}

		/**
		 * What type of request is this?
		 * string $type ajax, frontend or admin.
		 *
		 * @return bool
		 */
		private function is_request( $type ) {
			switch ( $type ) {
				case 'admin' :
					return is_admin();
				case 'ajax' :
					return defined( 'DOING_AJAX' );
				case 'cron' :
					return defined( 'DOING_CRON' );
				case 'frontend' :
					return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
			}
		}

		/**
		 * Include required files.
		 *
		 * @access private
		 * @since 1.8.5
		 * @return void
		 */
		private function includes() {

			require_once SELL_MEDIA_PLUGIN_DIR . '/inc/class-session.php';
			require_once SELL_MEDIA_PLUGIN_DIR . '/inc/class-customer.php';
			require_once SELL_MEDIA_PLUGIN_DIR . '/inc/class-downloads.php';
			require_once SELL_MEDIA_PLUGIN_DIR . '/inc/class-layouts.php';
			require_once SELL_MEDIA_PLUGIN_DIR . '/inc/class-mail.php';
			require_once SELL_MEDIA_PLUGIN_DIR . '/inc/class-payments.php';
			require_once SELL_MEDIA_PLUGIN_DIR . '/inc/class-products.php';
			require_once SELL_MEDIA_PLUGIN_DIR . '/inc/class-products-images.php';
			require_once SELL_MEDIA_PLUGIN_DIR . '/inc/class-products-audio-video.php';
			require_once SELL_MEDIA_PLUGIN_DIR . '/inc/class-queries.php';
			require_once SELL_MEDIA_PLUGIN_DIR . '/inc/class-search.php';
			require_once SELL_MEDIA_PLUGIN_DIR . '/inc/collections.php';
			require_once SELL_MEDIA_PLUGIN_DIR . '/inc/emails.php';
			require_once SELL_MEDIA_PLUGIN_DIR . '/inc/deprecated.php';
			require_once SELL_MEDIA_PLUGIN_DIR . '/inc/fields.php';
			require_once SELL_MEDIA_PLUGIN_DIR . '/inc/helpers.php';
			require_once SELL_MEDIA_PLUGIN_DIR . '/inc/gateways/paypal.php';
			require_once SELL_MEDIA_PLUGIN_DIR . '/inc/gateways/class-sm-gateway-paypal-request.php';
			require_once SELL_MEDIA_PLUGIN_DIR . '/inc/mime-types.php';
			require_once SELL_MEDIA_PLUGIN_DIR . '/inc/scripts.php';
			require_once SELL_MEDIA_PLUGIN_DIR . '/inc/shortcodes.php';
			require_once SELL_MEDIA_PLUGIN_DIR . '/inc/template-tags.php';
			require_once SELL_MEDIA_PLUGIN_DIR . '/inc/term-meta.php';
			require_once SELL_MEDIA_PLUGIN_DIR . '/inc/widgets.php';
			require_once SELL_MEDIA_PLUGIN_DIR . '/inc/settings/settings.php';
			require_once SELL_MEDIA_PLUGIN_DIR . '/inc/settings.php';
			require_once SELL_MEDIA_PLUGIN_DIR . '/inc/lightbox.php';
			require_once SELL_MEDIA_PLUGIN_DIR . '/inc/class-tax-meta-migrate.php';
			require_once SELL_MEDIA_PLUGIN_DIR . '/inc/class-updater.php';

			// Load files if is front end.
			if ( self::$instance->is_request( 'frontend' ) ) {

				require_once SELL_MEDIA_PLUGIN_DIR . '/inc/class-cart.php';
				require_once SELL_MEDIA_PLUGIN_DIR . '/inc/ajax.php';

			}

			if ( self::$instance->is_request( 'admin' ) ) {

				require_once SELL_MEDIA_PLUGIN_DIR . '/inc/admin-helpers.php';
				require_once SELL_MEDIA_PLUGIN_DIR . '/inc/admin-items.php';
				require_once SELL_MEDIA_PLUGIN_DIR . '/inc/admin-menu.php';
				require_once SELL_MEDIA_PLUGIN_DIR . '/inc/admin-payments.php';
				require_once SELL_MEDIA_PLUGIN_DIR . '/inc/admin-scripts.php';
				require_once SELL_MEDIA_PLUGIN_DIR . '/inc/admin-system-info.php';
				require_once SELL_MEDIA_PLUGIN_DIR . '/inc/class-admin-notices.php';
				require_once SELL_MEDIA_PLUGIN_DIR . '/inc/class-admin-search.php';
				require_once SELL_MEDIA_PLUGIN_DIR . '/inc/class-price-listings.php';
				require_once SELL_MEDIA_PLUGIN_DIR . '/inc/class-price-listings-tabs.php';
				require_once SELL_MEDIA_PLUGIN_DIR . '/inc/class-admin-add-item.php';

			}

			require_once SELL_MEDIA_PLUGIN_DIR . '/inc/class-upgrades.php';
			require_once SELL_MEDIA_PLUGIN_DIR . '/inc/install.php';

		}

		/**
		 * Loads the plugin language files.
		 *
		 * @access public
		 * @since 1.8.5
		 * @return void
		 */
		public function textdomain() {

			// Get text domain.
			$domain = 'sell_media';

			// The "plugin_locale" filter is also used in load_plugin_textdomain().
			$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

			// Create path to custom language file.
			$custom_mo = WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo';

			if ( file_exists( $custom_mo ) ) {
				load_textdomain( $domain, $custom_mo );
			} else {
				load_plugin_textdomain( $domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
			}
		}
	} // End SellMedia class.

endif; // End if class_exists check.

/**
 * The main function that returns the one and only SellMedia instance.
 * Use this function to access classes and methods.
 * Example: <?php $sell_media = Sell_Media(); ?>
 *
 * @since 1.8.5
 * @return object The one true SellMedia instance.
 */
function Sell_Media() {
	return SellMedia::instance();
}

// Start Sell Media.
Sell_Media();
