<?php
/**
 * Layout Assistance
 *
 * @package   SellMedia
 * @author    Thad Allender <support@graphpaperpress.com>
 * @license   GPL-2.0+
 * @link      http://graphpaperpress.com
 * @copyright 2014 Graph Paper Press
 */

/**
 * Plugin class for standardizing archive and search layouts for Sell Media in themes.
 *
 * @package SellMediaLayouts
 * @author  Thad Allender <support@graphpaperpress.com>
 */
class SellMediaLayouts {

	/**
	 *
	 * Settings
	 *
	 *
	 * Retrieves the settings for Sell Media.
	 *
	 * @since    0.0.1
	 *
	 * @var      string
	 */
	private $settings = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     0.0.1
	 */
	public function __construct() {

		// Settings
		$this->settings = sell_media_get_plugin_options();

		// Post class filter
		add_filter( 'post_class', array( $this, 'post_class' ) );

		// Body class filter
		add_filter( 'body_class', array( $this, 'body_class' ) );

		// Menu class filter
		add_filter( 'nav_menu_css_class', array( $this, 'nav_menu_css_class' ), 10, 2 );
	}

	/**
	 * Post class filter.
	 * Adds a new post class so we can style individual grids
	 *
	 * @since    0.0.1
	 *
	 * @return    html
	 */
	public function post_class( $classes ) {
		global $post;
		if ( is_post_type_archive( 'sell_media_item' ) ) {
			$classes[] = apply_filters( 'sell_media_grid_item_class', 'sell-media-grid-item', $post->ID );
		}

		foreach ( ( get_the_category( $post->ID ) ) as $category ) {
			$classes[] = $category->category_nicename;
		}
		return $classes;
	}

	/**
	 * Body class filter
	 * Add body classes to assist layouts
	 *
	 * @since    0.0.1
	 *
	 * @return    html
	 */
	public function body_class( $classes ) {
		global $post;

		// Theme
		$theme     = wp_get_theme();
		$classes[] = 'theme-' . sanitize_title_with_dashes( $theme->get( 'Name' ) );

		// Layout
		if ( isset( $this->settings->layout ) ) {
			$classes[] = $this->settings->layout;
		}

		// Style
		if ( isset( $this->settings->style ) ) {
			$classes[] = 'sell-media-style-' . $this->settings->style;
		}

		if ( empty( $post ) ) {
			return $classes;
		}

		// Pages assigned with shortcode
		$pages = sell_media_get_pages_array();
		foreach ( $pages as $page ) {
			$setting = $page . '_page';
			if ( isset( $this->settings->$setting ) && $post->ID === $this->settings->$setting ) {
				$classes[] = 'sell-media-page';
				$classes[] = 'sell-media-' . str_replace( '_', '-', $setting );
			}
		}

		// Shortcodes
		$shortcodes = array( 'sell_media_thanks', 'sell_media_searchform', 'sell_media_item', 'sell_media_all_items', 'sell_media_checkout', 'sell_media_download_list', 'sell_media_price_group', 'sell_media_list_all_collections', 'sell_media_login_form' );
		foreach ( $shortcodes as $shortcode ) {
			if ( isset( $post->post_content ) && has_shortcode( $post->post_content, $shortcode ) ) {
				$classes[] = 'sell-media-page';
			}
		}

		// All Sell Media pages
		if ( ! empty( $post->ID ) && 'sell_media_item' === get_post_type( $post->ID ) ) {
			$classes[] = 'sell-media-page';
		}

		// Gallery
		if ( is_singular( 'sell_media_item' ) && sell_media_has_multiple_attachments( $post->ID ) ) {
			$classes[] = 'sell-media-gallery-page';
		}

		return $classes;
	}

	/**
	 * Menu class filter
	 * Add classes to menu items
	 *
	 * @since    0.0.1
	 *
	 * @return    html
	 */
	public function nav_menu_css_class( $classes, $item ) {

		if ( 'page' === $item->object ) {
			if ( isset( $this->settings->lightbox_page ) && $this->settings->lightbox_page === $item->object_id ) {
				$classes[] = 'lightbox-menu';
			}
			if ( isset( $this->settings->checkout_page ) && $this->settings->checkout_page === $item->object_id ) {
				if ( in_array( 'total', $item->classes, true ) ) {
					$classes[] = 'checkout-total';
				} else {
					$classes[] = 'checkout-qty';
				}
			}
		}

		return $classes;
	}

}
