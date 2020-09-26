<?php
/**
 * Queries Class
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SellMediaQueries {

	/**
	 * Constructor
	 */
	public function __construct() {

		add_action( 'parse_query', array( $this, 'parse_query' ) );

	}

	/**
	 * Show attachments on taxonomy archives
	 * By default, WordPress doesn't show attachments on archives.
	 * @return object the $wp_query object
	 */
	public function parse_query() {

		global $wp_query;

		// When inside a custom taxonomy archive include attachments
		if ( is_tax( 'keywords' ) or is_tax( 'creator' ) ) {
			$wp_query->query_vars['post_type'] = array( 'attachment' );
			$wp_query->query_vars['post_status'] = array( null );

			return $wp_query;
		}
	}
}
