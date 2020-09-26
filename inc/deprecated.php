<?php

/**
 * Deprecated Functions
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Deprecated functions
 */
function sell_media_item_has_taxonomy_terms( $post_id = null, $taxonomy = null ) {
	return false;
}

function sell_media_country_list( $current = null, $req = false ) {
	return false;
}

function sell_media_countries_list() {
	return false;
}

function sell_media_us_states_list( $current = null, $req = false ) {
	return false;
}

function sell_media_collections() {
	sell_media_get_taxonomy_terms( 'collection' );
}

/**
 * Sell Media content loop.
 *
 * @deprecated 2.2.6 Use sell_media_content_loop filter.
 */
function sell_media_content_loop( $post_id, $index ) {
	_deprecated_function( __FUNCTION__, '2.2.6', 'sell_media_content_loop filter' );
	return apply_filters( 'sell_media_content_loop', $post_id, $index );
}

/**
 * Sell Media nav style UI.
 *
 * @deprecated 2.3 Use Sell_Media_Price_Listings.
 */
class SellMediaNavStyleUI {

	/**
	 * Constructor.
	 */
	function __construct() {
		return false;
	}

	/**
	 * Magic method.
	 *
	 * @param string $name  name.
	 * @param string $value Value.
	 */
	function __set( $name, $value ) {
		return false;
	}

	/**
	 * Magic methods.
	 *
	 * @param  string $name Name.
	 */
	function __get( $name ) {
		return false;
	}

	/**
	 * Admin scripts.
	 */
	function admin_scripts() {
		return false;
	}

	/**
	 * Save term.
	 */
	function save_term() {
		return false;
	}

	/**
	 * Delete term.
	 */
	function delete_term() {
		return false;
	}

	/**
	 * Add term.
	 */
	function add_term() {
		return false;
	}
	/**
	 * Setting UI.
	 */
	function setting_ui() {
		return false;
	}
}
