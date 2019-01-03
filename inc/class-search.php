<?php

/**
 * Search Class
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SellMediaSearch {

	private $settings;

	/**
	 * Init
	 */
	public function __construct() {

		// Restrict in admin area.
		if ( is_admin() ) {
			return;
		}

		$this->settings = sell_media_get_plugin_options();

		// Add a media search form shortcode
		add_shortcode( 'sell_media_search', array( $this, 'form' ) );

		// Legacy add a media search form shortcode
		add_shortcode( 'sell_media_searchform', array( $this, 'form' ) );

		// Add custom search query vars
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
	}


	/**
	 * Add custom search query vars
	 *
	 * @since 2.4.2
	 */
	function add_query_vars( $vars ) {
		$vars[] = 'search_query';
		$vars[] = 'search_file_type';
		return $vars;
	}


	/**
	 * Search form
	 *
	 * @since 1.8.7
	 */
	public function form( $url = null, $used = null ) {

		$settings = sell_media_get_plugin_options();
		$html = '';

		// Show a message to admins if they don't have search page set in settings.
		if ( current_user_can( 'administrator' ) && empty( $settings->search_page ) ) {
			$html .= esc_html__( 'For search to work, you must assign your Search Page in Sell Media -> Settings.', 'sell_media' );
			return $html;
		}

		// Get the search term(s)
		$search_term = htmlspecialchars(trim(stripslashes(strip_tags(get_query_var( 'search_query' )))));

		// Get the file type
		$search_file_type = get_query_var( 'search_file_type' );

		// only use this method if it hasn't already been used on the page
		static $used;
		if ( ! isset( $used ) ) {
			$used = true;

			$html .= '<div class="sell-media-search">';
			$html .= '<form role="search" method="get" id="sell-media-search-form" class="sell-media-search-form" action="' . esc_url( get_permalink( $settings->search_page ) ) . '">';
			$html .= '<div class="sell-media-search-inner cf">';

			// Visible search options wrapper
			$html .= '<div id="sell-media-search-visible" class="sell-media-search-visible cf">';

			// Input field
			$html .= '<div id="sell-media-search-query" class="sell-media-search-field sell-media-search-query">';
			$html .= '<input type="text" value="' . $search_term . '" name="search_query" id="sell-media-search-text" class="sell-media-search-text" placeholder="' . apply_filters( 'sell_media_search_placeholder', sprintf( __( 'Search for %1$s (comma separated)', 'sell_media' ), empty( $settings->post_type_slug ) ? 'keywords' : $settings->post_type_slug ) ) . '"/>';
			$html .= '</div>';

			// Submit button
			$html .= '<div id="sell-media-search-submit" class="sell-media-search-field sell-media-search-submit">';
			$html .= '<input type="submit" id="sell-media-search-submit-button" class="sell-media-search-submit-button" value="' . apply_filters( 'sell_media_search_button', __( 'Search', 'sell_media' ) ) . '" />';
			$html .= '</div>';

			$html .= '</div>';

			// Hidden search options wrapper
			$html .= '<div id="sell-media-search-hidden" class="sell-media-search-hidden cf">';

			// File type field
			$html .= '<div id="sell-media-search-file-type" class="sell-media-search-field sell-media-search-file-type">';
			$html .= '<label for="search_file_type">' . esc_html__( 'File Type', 'sell_media' ) . '</label>';
			$html .= '<select name="search_file_type">';
			$html .= '<option value="">' . esc_html__( 'All', 'sell_media' ) . '</option>';
			$mimes = array( 'image', 'video', 'audio' );
			foreach ( $mimes as $mime ) {
				$selected = ( $search_file_type === $mime ) ? 'selected' : '';
				$html .= '<option value="' . $mime . '" ' . $selected . '>';
				$html .= ucfirst( $mime );
				$html .= '</option>';
			}

			$html .= '</select>';
			$html .= '</div>';

			// Hidden search options wrapper
			$html .= '</div>';

			$html .= '</div>';
			$html .= '</form>';
			$html .= '</div>';

		}

		// only run the query on the actual search results page.
		if ( is_page( $settings->search_page ) && in_the_loop() ) {

			// Find comma-separated search terms and format into an array
			$search_term_cleaned = preg_replace( '/\s*,\s*/', ',', $search_term );
			$search_terms = str_getcsv( $search_term_cleaned, ',' );

			// Exclude negative keywords in search query like "-cow"
			$negative_search_terms = '';
			$negative_search_terms = preg_grep( '/\B-[^\B]+/', $search_terms );
			$negative_search_terms = preg_replace( '/[-]/', '', $negative_search_terms );

			// now remove negative search terms from search terms
			$search_terms = array_diff( $search_terms, $negative_search_terms );
			$search_terms = array_filter( $search_terms );

			// Get the file/mimetype
			$mime_type = $this->get_mimetype( $search_file_type );

			// Current pagination
			$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

			if ( ! empty( $settings->search_relation ) && 'and' === $settings->search_relation ) {
				$tax_array = array();
				foreach ( $search_terms as $s ) {
					$array = array(
						'taxonomy' => 'keywords',
						'field'    => 'name',
						'terms'    => $s,
					);
					$tax_array[] = $array;
				}
				foreach ( $negative_search_terms as $n ) {
					$array = array(
						'taxonomy' => 'keywords',
						'field'    => 'name',
						'terms'    => array( $n ),
						'operator' => 'NOT IN'
					);
					$tax_array[] = $array;
				}

				$tax_query = array(
					'relation' => 'AND',
					$tax_array
				);
			} else {
				// Add original full keyword to the search terms array
				// This ensures that multiple word keyword search works
				$one_big_keyword = str_replace( ',', ' ', $search_term );
				$search_terms[] .= $one_big_keyword;
				$tax_query = array(
					array(
						'taxonomy' => 'keywords',
						'field'    => 'name',
						'terms'    => $search_terms,
					)
				);
			}

			// The Query
			$args = array(
				'post_type' => 'attachment',
				'paged'		=> $paged,
				'post_status' => array( 'publish', 'inherit' ),
				'post_mime_type' => $mime_type,
				'post_parent__in' => sell_media_ids(),
				'tax_query' => $tax_query
			);
			$args = apply_filters( 'sell_media_search_args', $args );
			$search_query = new WP_Query( $args );
			$i = 0;

			// The Loop
			if ( $search_query->have_posts() ) {

				$html .= '<p class="sell-media-search-results-text">' . sprintf( esc_html__( 'We found %1$s results for "%2$s."', 'sell_media' ), $search_query->found_posts, $search_term ) . '</p>';

				// hook for related keywords, etc.
				$html .= sell_media_format_related_search_results( $search_terms );

				//$html .= $this->search_help();

				$html .= '<div id="sell-media-search-results" class="sell-media">';
				$html .= '<div class="' . apply_filters( 'sell_media_grid_item_container_class', 'sell-media-grid-item-container' ) . '">';

				while ( $search_query->have_posts() ) {
					$search_query->the_post();

					$post_id = get_the_ID();
					$parent_id = get_post_meta( $post_id, $key = '_sell_media_for_sale_product_id', true );

					$html .= '<div id="sell-media-' . $post_id . '" class="' . apply_filters( 'sell_media_grid_item_class', 'sell-media-grid-item', $parent_id ) . ' sell-media-grid-single-item">';
					$html .= '<a href="' . esc_url( get_permalink() ) . '" ' . sell_media_link_attributes( $post_id ) . ' class="sell-media-item">';

					if ( ! empty( $settings->titles ) ) {
						$html .= '<h2 class="entry-title">' . get_the_title() . '</h2>';
					}

					if ( wp_get_attachment_image( $post_id, apply_filters( 'sell_media_thumbnail', 'medium' ) ) ) {

						$html .= wp_get_attachment_image( $post_id, apply_filters( 'sell_media_thumbnail', 'medium' ) );
					} else {
						$html .= sell_media_item_icon( $parent_id, apply_filters( 'sell_media_thumbnail', 'medium' ), false );
					}
					$html .= '<div class="sell-media-quick-view" data-product-id="' . esc_attr( $parent_id ) . '" data-attachment-id="' . esc_attr( $post_id ) . '">' . apply_filters( 'sell_media_quick_view_text', __( 'Quick View', 'sell_media' ), $parent_id, $post_id ) . '</div>';
					$html .= '</a>';
					$html .= '</div>';
				}
				$html .= '</div>';
				$html .= '</div>';
				$html .= sell_media_pagination_filter( $search_query->max_num_pages );
				$text = esc_html__( 'Explore more from our store', 'sell_media' );
				$html .= '<p class="sell-media-search-results-text">' . $text . '</p>';
				$html .= do_shortcode( '[sell_media_filters]' );

			} else {

				if ( $search_terms ) {
					$text = sprintf( __( 'Sorry, no results for "%1$s."', 'sell_media' ), $search_term );
					$html .= $this->search_help();
				} else {
					$html .= $this->search_help();
				}
				$html .= '<p class="sell-media-search-results-text">' . $text . '</p>';
				$html .= do_shortcode( '[sell_media_filters]' );
			}

			/* Restore original Post Data */
			wp_reset_postdata();
			$i = 0;

		} // end search results page check

		return apply_filters( 'sell_media_search_results', $html );
	}

	public function search_help() {

		$html  = '<div class="sell-media-search-help">';
		$html .= '<h6>' . esc_html__( 'Search Tips', 'sell_media' ) . '</h6>';
		$html .= '<ul>';
		$html .= '<li>' . esc_html__( 'Separate keywords with a comma.', 'sell_media' ) . '</li>';
		$html .= '<li>' . esc_html__( 'Use fewer keywords to expand search results.', 'sell_media' ) . '</li>';
		$html .= '<li>' . esc_html__( 'Use negative keywords (like -dogs) to exclude dogs from search results.', 'sell_media' ) . '</li>';
		$html .= '</ul>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Get the select value of the filetype field and conver it into a WP mimtype for WP_Query
	 *
	 * @param  string 		The filetype (image, video, audio)
	 * @return array 		The WP mimetype format for each filetype
	 */
	private function get_mimetype( $filetype ) {
		if ( 'image' === $filetype ) {
			$mime = array( 'image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/tiff', 'image/x-icon' );
		} elseif ( 'video' === $filetype ) {
			$mime = array( 'video/x-ms-asf', 'video/x-ms-wmv', 'video/x-ms-wmx', 'video/x-ms-wm', 'video/avi', 'video/divx', 'video/x-flv', 'video/quicktime', 'video/mpeg', 'video/mp4', 'video/ogg', 'video/webm', 'video/x-matroska' );
		} elseif ( 'audio' === $filetype ) {
			$mime = array( 'audio/mpeg', 'audio/x-realaudio', 'audio/wav', 'audio/ogg', 'audio/midi', 'audio/x-ms-wma', 'audio/x-ms-wax', 'audio/x-matroska' );
		} else {
			$mime = '';
		}

		return $mime;
	}



}
