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
 * Order the post type by date and descending order in admin
 *
 * @param $wp_query
 * @since 1.8.5
 */
function sell_media_post_type_admin_order( $wp_query ) {
    
    if ( is_admin() ) {

        $post_type = $wp_query->query['post_type'];

        if ( $post_type == 'sell_media_item' && empty( $_GET['orderby'] ) ) {
            $wp_query->set( 'orderby', 'date' );
            $wp_query->set( 'order', 'DESC' );
        }

    }
}
add_filter ( 'pre_get_posts', 'sell_media_post_type_admin_order' );