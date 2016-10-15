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

Class SellMediaSearch {

	private $query_instance;

	private $settings;
	/**
	 * Init
	 */
	public function __construct(){

		// Restrict in admin area.
		if ( is_admin() ) {
			return;
		}

		add_action( 'pre_get_posts', array( &$this, 'set_query_param' ) );
	}

	/**
	 * Set flag for search.
	 * @param object $query Query object.
	 */
	function set_query_param( $query ){
		if ( ! isset( $query->query['search_type'] ) ) {
			return $query;
		}

		$query->set( 'search_type', 'sell_media_search' );
	}

	/**
	 * Search form
	 *
	 * @since 1.8.7
	 */
	public function form( $url=null, $used=null ) {

		$settings = sell_media_get_plugin_options();

		$html = '';

		// only use this method if it hasn't already been used on the page
		static $used;
		if ( ! isset( $used ) ) {
			$used = true;

			$query = ( get_search_query() ) ? get_search_query() : '';

			$html .= '<div class="sell-media-search">';
			$html .= '<form role="search" method="get" id="sell-media-search-form" class="sell-media-search-form" action="' . site_url() . '">';
			$html .= '<div class="sell-media-search-inner cf">';

			// Visible search options wrapper
			$html .= '<div id="sell-media-search-visible" class="sell-media-search-visible cf">';

			// Input field
			$html .= '<div id="sell-media-search-query" class="sell-media-search-field sell-media-search-query">';
			$html .= '<input type="text" value="' . $query . '" name="s" id="sell-media-search-text" class="sell-media-search-text" placeholder="' . apply_filters( 'sell_media_search_placeholder', sprintf( __( 'Search for %1$s', 'sell_media' ), empty( $settings->post_type_slug ) ? 'items' : $settings->post_type_slug ) ) . '"/>';
			$html .= '</div>';

			// Submit button
			$html .= '<div id="sell-media-search-submit" class="sell-media-search-field sell-media-search-submit">';
			$html .= '<input type="hidden" name="post_type" value="attachment" />';
			$html .= '<input type="submit" id="sell-media-search-submit-button" class="sell-media-search-submit-button" value="' . apply_filters( 'sell_media_search_button', __( 'Search', 'sell_media' ) ) . '" />';
			$html .= '</div>';

			$html .= '</div>';

			// Hidden search options wrapper
			$html .= '<div id="sell-media-search-hidden" class="sell-media-search-hidden cf">';

			// Exact match field
			$html .= '<div id="sell-media-search-exact-match" class="sell-media-search-field sell-media-search-exact-match">';
			$html .= '<label for="sentence" id="sell-media-search-exact-match-desc" class="sell-media-search-exact-match-desc sell-media-tooltip" data-tooltip="Check to limit search results to exact phrase matches. Without exact phrase match checked, a search for \'New York Yankees\' would return results containing any of the three words \'New\', \'York\' and \'Yankees\'.">' . __( 'Exact phrase match (?)', 'sell_media' ) . '</label>';
			$html .= '<input type="checkbox" value="1" name="sentence" id="sentence" />';
			$html .= '</div>';

			// Collection field
			$html .= '<div id="sell-media-search-collection" class="sell-media-search-field sell-media-search-collection">';
			$html .= '<label for="collection">' . __( 'Collection', 'sell_media' ) . '</label>';
			$html .= '<select name="collection">';
			$html .= '<option value="">' . esc_attr( __( 'All', 'sell_media' ) ) . '</option>';

			$categories = get_categories( 'taxonomy=collection' );
			foreach ( $categories as $category ) {
				$html .= '<option value="' . $category->category_nicename . '">';
				$html .= $category->cat_name;
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

		$search_terms = ( get_search_query() ) ? get_search_query() : '';
		$search_terms = str_getcsv( $search_terms, ' ' );

		// The Query
		$args = array(
			'post_type' => 'attachment',
			'post_mime_type' => 'image',
			'post_status' => array( 'publish', 'inherit' ),
			'tax_query' => array(
				array(
					'taxonomy' => 'keywords',
					'field'    => 'name',
					'terms'    => $search_terms
				)
			)
		);
		$search_query = new WP_Query( $args );
		$i = 0;

		$html .= '<div id="sell-media-archive" class="sell-media">';
		$html .= '<div class="' . apply_filters( 'sell_media_grid_item_container_class', 'sell-media-grid-item-container' ) . '">';

		// The Loop
		if ( $search_query->have_posts() ) {
			while ( $search_query->have_posts() ) {
				$search_query->the_post();
				$parent_post_type = get_post_type( wp_get_post_parent_id( get_the_ID() ) );
				if ( $parent_post_type === 'sell_media_item' ) {
					$i++;
					$html .= apply_filters( 'sell_media_content_loop', get_the_ID(), $i );
				}
			}
		}

		$html .= '</div>';
		$html .= '</div>';

		/* Restore original Post Data */
		wp_reset_postdata();
		$i = 0;
		
		return $html;
	}

}
