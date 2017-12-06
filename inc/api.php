<?php
/**
 * API
 */

/**
 * Shortcodes
 */
$pages = sell_media_get_pages_array();
foreach ( $pages as $page ) {
	add_shortcode( 'sell_media_' . $page, function() {
		echo '<div id="sell-media-app"></div>';
	} );
}

/**
 * Make permalinks a relative path
 * @param  [type] $url [description]
 * @return [type]      [description]
 */
function sell_media_get_relative_permalink( $id ) {
	return str_replace( home_url(), '', get_permalink( $id ) );
}
