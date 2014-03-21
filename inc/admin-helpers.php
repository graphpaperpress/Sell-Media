<?php

/**
 * Admin Helper Functions
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Moves and uploaded file from the uploads dir into the "protected"
 * sell_media dir. Note the original file is deleted.
 *
 * @param $original_file Full path of the file with the file.
 * @since 1.0.1
 */
function sell_media_default_move( $original_file=null ){

    $dir = wp_upload_dir();
    $original_file_path = $dir['basedir'] . '/' . $original_file;
    $destination_file_path = sell_media_get_upload_dir() . '/' . $original_file;

    if ( file_exists( $original_file_path ) ){

        if ( ! file_exists( dirname( $destination_file_path ) ) ){
            wp_mkdir_p( dirname( $destination_file_path ) );
        }

        // Copy original to our protected area
        @copy( $original_file_path, $destination_file_path );
        @unlink( $original_file_path );
    }
}


/**
 * Change Downloads Upload Dir
 *
 * Hooks the sell_media_set_upload_dir filter when appropriate.
 *
 * @access private
 * @since 1.0
 * @return void
 */
function sell_media_change_downloads_upload_dir() {
    global $pagenow;

    if ( ! empty( $_REQUEST['post_id'] ) && ( 'async-upload.php' == $pagenow || 'media-upload.php' == $pagenow ) ) {
        if ( 'sell_media_item' == get_post_type( $_REQUEST['post_id'] ) ) {
            add_filter( 'upload_dir', 'sell_media_set_upload_dir' );
        }
    }
}
//add_action( 'admin_init', 'sell_media_change_downloads_upload_dir', 999 );

/**
 * Set the sell_media upload directory
 *
 * @since 1.0
 * @return array Upload directory information
 */
function sell_media_set_upload_dir( $upload ) {

    // Override the year / month being based on the post publication date, if year/month organization is enabled
    if ( get_option( 'uploads_use_yearmonth_folders' ) ) {
        // Generate the yearly and monthly directories
        $time = current_time( 'mysql' );
        $y = substr( $time, 0, 4 );
        $m = substr( $time, 5, 2 );
        $upload['subdir'] = "/$y/$m";
    }

    $upload['subdir'] = '/sell_media' . $upload['subdir'];
    $upload['path'] = $upload['basedir'] . $upload['subdir'];
    $upload['url'] = $upload['baseurl'] . $upload['subdir'];
    return $upload;
}