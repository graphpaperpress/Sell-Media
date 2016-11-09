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
