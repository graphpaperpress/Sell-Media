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

		// Loop start action
		add_action( 'loop_start', array( $this, 'loop_start' ) );

		// Loop end action
		add_action( 'loop_start', array( $this, 'loop_start' ) );

		// Post class filter
		add_filter( 'post_class', array( $this, 'post_class' ) );

		// Body class filter
		add_filter( 'body_class', array( $this, 'body_class' ) );

		// Menu class filter
		add_filter( 'nav_menu_css_class', array( $this, 'nav_menu_css_class' ), 10, 2 );

		// Grid item container class
		add_filter( 'sell_media_grid_item_container_class', array( $this, 'grid_container_class' ), 10, 1 );

		// Grid item class
		add_filter( 'sell_media_grid_item_class', array( $this, 'grid_class' ), 10, 1 );

	}

	/**
	 * Loop start action hook.
	 * Adds markup before looping over posts so we can
	 * add a wrapper div for creating grids layouts.
	 *
	 * @since    0.0.1
	 *
	 * @return    html
	 */
	public function loop_start() {
		$class = apply_filters( 'sell_media_grid_item_container_class', 'sell-media-grid-item-container' );
		echo '<div class="' . esc_attr( $class ) . '">';
	}

	/**
	 * Loop start action hook.
	 * Adds markup before looping over posts so we can
	 * add a wrapper div for creating grids layouts.
	 *
	 * @since    0.0.1
	 *
	 * @return    html
	 */
	public function loop_end() {
		echo '</div>';
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
		if ( is_post_type_archive( 'sell_media_item' )
			|| ( is_search() && $_GET['post_type'] && 'attachment' === $_GET['post_type'] ) ) {
			$classes[] = apply_filters( 'sell_media_grid_item_class', 'sell-media-grid-item', null );
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

		if ( empty( $post ) ) {
			return;
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

		// Layout
		if ( isset( $settings->layout ) ) {
			$classes[] = $settings->layout;
		}

		// Gallery
		if ( sell_media_is_gallery_page() ) {
			$classes[] = 'sell-media-gallery-page';
		}

		// Theme
		$theme = wp_get_theme();
		$classes[] = 'theme-' . sanitize_title_with_dashes( $theme->get( 'Name' ) );

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
			if ( $this->settings->lightbox_page === $item->object_id ) {
				$classes[] = 'lightbox-menu';
			}
			if ( $this->settings->checkout_page === $item->object_id ) {
				if ( in_array( 'total', $item->classes, true ) ) {
					$classes[] = 'checkout-total';
				} else {
					$classes[] = 'checkout-qty';
				}
			}
		}

		return $classes;
	}

	/**
	 * Filter the item container class
	 * Needed to create the masonry layout
	 *
	 * @since  2.1.3
	 * @return string css class
	 */
	public function grid_container_class() {
		$class = 'sell-media-grid-item-container';
		if ( 'sell-media-masonry' === $this->settings->thumbnail_layout ) {
			$class = 'sell-media-grid-item-masonry-container';
		}
		return $class;
	}

	/**
	 * Filter the grid item class
	 * Creates a 1, 2, 3, 4, 5 column or masonry layout
	 *
	 * @since  2.1.3
	 * @return string css class
	 */
	function grid_class( $class ) {
		if ( ! empty( $this->settings->thumbnail_layout ) ) {
			return $class . ' ' . $this->settings->thumbnail_layout;
		}
	}

}
