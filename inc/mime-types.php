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
 * @param array $existing_mimes A list of all the existing MIME types
 * @return array A list of all the new MIME types appended
 */
function sell_media_allowed_mime_types( $existing_mimes = array() ) {
    $existing_mimes['zip']  = 'application/zip';
    $existing_mimes['epub'] = 'application/epub+zip';
    $existing_mimes['mobi'] = 'application/x-mobipocket-ebook';
    $existing_mimes['m4r']  = 'audio/aac';
    $existing_mimes['aif']  = 'audio/x-aiff';
    $existing_mimes['aiff'] = 'audio/aiff';
    $existing_mimes['psd']  = 'image/photoshop';
    $existing_mimes['apk']  = 'application/vnd.android.package-archive';
    $existing_mimes['msi']  = 'application/x-ole-storage';

    return $existing_mimes;
}
add_filter( 'upload_mimes', 'sell_media_allowed_mime_types' );