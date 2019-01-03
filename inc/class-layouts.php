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

		// Grid item container class
		add_filter( 'sell_media_grid_item_container_class', array( $this, 'grid_container_class' ), 10, 1 );

		// Grid item class
		add_filter( 'sell_media_grid_item_class', array( $this, 'grid_class' ), 10, 3 );

		// Before the content
		add_filter( 'the_content', array( $this, 'before_content' ) );

		// After the content
		add_filter( 'the_content', array( $this, 'after_content' ) );

		// Remove prepended attachment from attachment template in core WP
		add_filter( 'prepend_attachment', array( $this, 'remove_prepend_attachment' ) );

		// Content loop
		add_filter( 'sell_media_content_loop',  array( $this, 'content_loop' ), 10, 3 );

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
		if ( isset( $this->settings->layout ) ) {
			$classes[] = $this->settings->layout;
		}

		// Gallery
		if ( is_singular( 'sell_media_item' ) && sell_media_has_multiple_attachments( $post->ID ) ) {
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

		if ( 'sell-media-horizontal-masonry' === $this->settings->thumbnail_layout ) {
			$class = 'horizontal-masonry-columns';
			// $class = 'sell-media-grid-item-horizontal-masonry-container';
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
	public function grid_class( $class = '', $post_id = '', $args = '' ) {
		if ( isset( $args['context'] ) && 'widget' == $args['context'] ) {
			return $class . ' sell-media-three-col';
		}

		if ( ! empty( $this->settings->thumbnail_layout ) ) {
			return $class . ' ' . $this->settings->thumbnail_layout;
		}
	}

	/**
	 * Before the content on sell media and attachment pages
	 */
	public function before_content( $content ) {

		global $post;
		$post_id = $post->ID;


		// show on single sell media pages
		if ( is_singular( 'sell_media_item' ) || sell_media_attachment( $post_id ) || sell_media_is_search() ) {

			// bail if it's password protected item
			if ( post_password_required( $post ) || ( isset( $post->post_parent ) && post_password_required( $post->post_parent ) ) ) {
				return $content;
			}

			$has_multiple_attachments = sell_media_has_multiple_attachments( $post_id );
			$wrap = ( ! $has_multiple_attachments || 'attachment' === get_post_type( $post_id ) ) ? true : false;
			$new_content = '';

			// only wrap content if a single image/media is being viewed
			if ( $wrap ) {
				$new_content .= '<div class="sell-media-content">';
			}

			$new_content .= sell_media_breadcrumbs();
			if ( ! sell_media_is_search() ) {
				$new_content .= sell_media_get_media();
			}
			$new_content .= $content;

			// only wrap content if a single image/media is being viewed
			if ( $wrap ) {
				$new_content .= '</div>';
			}

			$content = $new_content;

			// set the post views, used for popular query
			sell_media_set_post_views( $post_id );
		}

		return apply_filters( 'sell_media_content', $content );

	}

	/**
	 * After content filter
	 *
	 * Append buy button and add action to append more stuff (lightbox, keywords, etc)
	 *
	 * @since 1.9.2
	 * @param int $post_id Item ID
	 * @return void
	 */
	public function after_content( $content ) {

		global $post;
		$post_id = $post->ID;

		if ( post_password_required( $post ) || ( isset( $post->post_parent ) && post_password_required( $post->post_parent ) ) ) {
			return $content;
		}

		// only show on single sell media and attachment pages
		if ( is_main_query() && is_singular( 'sell_media_item' ) && ! sell_media_has_multiple_attachments( $post_id ) || sell_media_attachment( $post_id ) ) {

			if ( is_singular( 'attachment' ) ) {
				$attachment_id = $post_id;
				$post_id = get_post_meta( $post_id, '_sell_media_for_sale_product_id', true );
			} else {
				$attachment_id = sell_media_get_attachment_id( $post_id );
			}

			ob_start();
			$enable_ecommerce = apply_filters( 'sell_media_enable_ecommerce', true, $post_id, $attachment_id );
			if ( $enable_ecommerce ) {
				echo '<div class="sell-media-meta">';
				do_action( 'sell_media_above_buy_button', $post_id, $attachment_id );
				do_action( 'sell_media_add_to_cart_fields', $post_id, $attachment_id );
				do_action( 'sell_media_below_buy_button', $post_id, $attachment_id );
				echo '</div>';
			}

			echo do_action( 'sell_media_below_content', $post_id, $attachment_id );

			$content .= ob_get_contents();
			ob_end_clean();
		}

		return $content;
	}

	/**
	 * Remove the prepended attachment from WordPress core attachment template
	 * since we're already appending it via the_content filter
	 * @return
	 */
	function remove_prepend_attachment( $p ) {

		global $post;

		if ( sell_media_attachment( $post->ID ) ) {
			$p = '';
		}

		return $p;
	}

	/**
	 * Main content loop used in all themes
	 * @return string html
	 */
	function content_loop( $post_id, $i, $args = array() ) {

		$original_id = $post_id;
		if ( post_password_required( $original_id ) && sell_media_is_search() ) {
			return;
		}
		if ( 'attachment' === get_post_type( $post_id ) ) {
			$attachment_id = $post_id; // always and attachment
			$post_id = get_post_meta( $attachment_id, $key = '_sell_media_for_sale_product_id', true ); // always a sell_media_item
		} else {
			$attachment_id = sell_media_get_attachment_id( $post_id ); // always an attachment
		}

		$class = apply_filters( 'sell_media_grid_item_class', 'sell-media-grid-item', $post_id, $args );
		if ( ! sell_media_has_multiple_attachments( $post_id ) ) {
			$class .= ' sell-media-grid-single-item';
		}
		$custom_style = '';
		if ( 'sell-media-horizontal-masonry' === $this->settings->thumbnail_layout ) {
			$class = 'horizontal-masonry-column overlay-container ';
						
			$image_data     = wp_get_attachment_image_src( $attachment_id, 'medium' );
			$image_width    = $image_data[1];
			$image_height   = $image_data[2];
			$width          = $image_width * 250 / $image_height;
			$padding_bottom = $image_height / $image_width * 100;

			$html  = '<div id="sell-media-' . $original_id . '" class="' . $class . '" style="width:' . $width . 'px; flex-grow:' . $width . '; " >';
		} else {
		
			$html  = '<div id="sell-media-' . $original_id . '" class="' . $class . '">';
		}
		
		$html .= '<a href="' . esc_url( get_permalink( $original_id ) ) . '" ' . sell_media_link_attributes( $original_id ) . ' class="sell-media-item">';
		if ( 'sell-media-horizontal-masonry' === $this->settings->thumbnail_layout ) {
			$html .= '<i style="padding-bottom:' . $padding_bottom . '%;" ></i>';
		}
		// Show titles?
		if ( isset( $this->settings->titles ) && 0 != $this->settings->titles && is_main_query() ) {
			$html .= '<h2 class="sell-media-entry-title">' . get_the_title( $original_id ) . '</h2>';
		}

		$html .= sell_media_item_icon( $original_id, apply_filters( 'sell_media_thumbnail', 'medium' ), false );

		$enable_ecommerce = apply_filters( 'sell_media_enable_ecommerce', true, $post_id, $attachment_id );

		// Show quick view?
		if ( isset( $this->settings->quick_view ) && 0 != $this->settings->quick_view && is_main_query() ) {
			if ( sell_media_has_multiple_attachments( $post_id ) && ( is_tax( array( 'collection' ) ) || is_post_type_archive( 'sell_media_item' ) ) ) {
				$html .= '<div class="sell-media-view-gallery">' . apply_filters( 'sell_media_view_gallery_text', __( 'View Gallery', 'sell_media' ) ) . '</div>';
			} elseif ( $enable_ecommerce ) {
				$html .= '<div class="sell-media-quick-view" data-product-id="' . esc_attr( $post_id ) . '" data-attachment-id="' . esc_attr( $attachment_id ) . '">' . apply_filters( 'sell_media_quick_view_text', __( 'Quick View', 'sell_media' ), $post_id, $attachment_id ) . '</div>';
			}
		}
		$html .= '</a>';
		$html .= '</div>';

		return apply_filters( 'sell_media_filter_content_loop', $html, $original_id, $i );
	}

}
