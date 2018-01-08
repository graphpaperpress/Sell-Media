<?php

/**
 * Mime Types
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Allowed Mime Types
 *
 * @since 1.0
 *
 * @param array $mimes A list of all the existing MIME types
 * @return array A list of all the new MIME types appended
 */
function sell_media_allowed_mime_types( $mimes = array() ) {
	$mimes['zip']  = 'application/zip';
	$mimes['epub'] = 'application/epub+zip';
	$mimes['mobi'] = 'application/x-mobipocket-ebook';
	$mimes['m4r']  = 'audio/aac';
	$mimes['aif']  = 'audio/x-aiff';
	$mimes['aiff'] = 'audio/aiff';
	$mimes['psd']  = 'image/photoshop';
	$mimes['apk']  = 'application/vnd.android.package-archive';
	$mimes['msi']  = 'application/x-ole-storage';

	return $mimes;
}
add_filter( 'upload_mimes', 'sell_media_allowed_mime_types' );
