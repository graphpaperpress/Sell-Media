<?php

/**
 * Process the image that was uploaded from a meta field. Creates new image
 * sizes, moves the original image to the proctected area, and also saves IPTC
 * data if any as taxonomies/terms.
 *
 * @param $moved_file The file we are referencing
 * @param $file_name The name of the file.
 * @since 1.0.1
 * @return $destination_file The location to the new file
 */
function sell_media_move_image_from_meta( $selected_file=null ){

    $wp_upload_dir = wp_upload_dir();

    // Build our destination path, note the Y/m
    // This is used to check for year/month/ folder
    // If we don't have one we'll let WordPress create it.
    $destination_dir = $wp_upload_dir['basedir'] . SellMedia::upload_dir . '/' . date('Y') . '/' . date('m') . '/';
    if ( ! is_dir( $destination_dir ) ){
        wp_mkdir_p( $destination_dir );
    }

    $destination_file = $wp_upload_dir['basedir'] . SellMedia::upload_dir . '/' . date('Y') . '/' . date('m') . '/' . basename( $selected_file );

    // move original into protected area
    rename( $selected_file, $destination_file );

    // Resize original and move it into the uploads/ dir
    // and also rename it to the original
    $image_new_size = image_make_intermediate_size( $moved_file, get_option('large_size_w'), get_option('large_size_h'), $crop=false );
    rename( $image_new_size, $wp_upload_dir['path'] . '/' . basename( $selected_file ) );

    // Get iptc info
    $city = sell_media_iptc_parser( 'city', $destination_file );
    $state = sell_media_iptc_parser( 'state', $destination_file );
    $creator = sell_media_iptc_parser( 'creator', $destination_file );
    $keywords = sell_media_iptc_parser( 'keywords', $destination_file );

    // If we have iptc info save it
    if ( $city ) sell_media_iptc_save( 'city', $city, $post_id );
    if ( $state ) sell_media_iptc_save( 'state', $state, $post_id );
    if ( $creator ) sell_media_iptc_save( 'creator', $creator, $post_id );
    if ( $keywords ) sell_media_iptc_save( 'keywords', $keywords, $post_id );

    return $destination_file;
}


/**
 * Parse IPTC info and move the uploaded file into the proctected area
 *
 * In order to "protect" our uploaded file, we resize the original
 * file down to the largest WordPress size set in Media Settings.
 * Then we take the uploaded file and move it to the "proteced area".
 * Last, we copy (rename) our resized uploaded file to be the original
 * file.
 *
 * @param $attached_file As WordPress see's it in *postmeta table
 * "_wp_attached_file", i.e., YYYY/MM/file-name.ext
 * @since 1.0.1
 */
function sell_media_move_image_from_attachment( $attached_file=null, $attachment_id=null ){

    // Since our attached file does not contain the full path.
    // We build it using the wp_upload_dir function.
    $wp_upload_dir = wp_upload_dir();
    $original_file = $wp_upload_dir['basedir'] . '/' . $attached_file;

    // Extract IPTC meta info from the uploaded image.
    $city = sell_media_iptc_parser( 'city', $original_file );
    $state = sell_media_iptc_parser( 'state', $original_file );
    $creator = sell_media_iptc_parser( 'creator', $original_file );
    $keywords = sell_media_iptc_parser( 'keywords', $original_file );

    // Save iptc info as taxonomies
    if ( ! empty( $attachment_id ) ) {
        if ( $city )
            sell_media_iptc_save( 'city', $city, $attachment_id );

        if ( $state )
            sell_media_iptc_save( 'state', $state, $attachment_id );

        if ( $creator )
            sell_media_iptc_save( 'creator', $creator, $attachment_id );

        if ( $keywords )
            sell_media_iptc_save( 'keywords', $keywords, $attachment_id );
    }

    // Assign the FULL PATH to our destination file.
    $destination_file = $wp_upload_dir['basedir'] . SellMedia::upload_dir . '/' . $attached_file;

    // Check if the destinatin directory exists, i.e.
    // wp-content/uploads/sell_media/YYYY/MM if not we create it.
    if ( ! file_exists( dirname( $destination_file ) ) ){
        wp_mkdir_p( dirname( $destination_file ) );
    }

    // Resize original file down to the largest size set in the Media Settings
    //
    // Determine which version of WP we are using.
    // Would rather check if the correct function exists
    // but the function 'image_make_intermediate_size' uses other
    // functions that are in trunk and not in 3.4
    global $wp_version;
    if ( version_compare( $wp_version, '3.5', '>=' ) ){

        $image_new_size = image_make_intermediate_size( $original_file, get_option('large_size_w'), get_option('large_size_h'), $crop = false );

        /**
         * If for some reason the image resize fails we just fall back to the original image.
         * Example, the image the user is trying to sell is smaller than our "max width".
         */
        if ( empty( $image_new_size ) ){
            $resized_image = $original_file;
            $keep_original = true;
        } else {
            $keep_original = false;
            $resized_image = $wp_upload_dir['path'] . '/' . $image_new_size['file'];
        }

        if ( ! file_exists( $destination_file ) ){

            // Copy original to our protected area
            copy( $original_file, $destination_file );
            if ( ! $keep_original ) unlink( $original_file );

            // Copy (rename) our resized image to the original
            copy( $resized_image, $wp_upload_dir['path'] . '/' . basename( $destination_file ) );
        }

    } else {
        $resized_image = image_resize( $original_file, get_option('large_size_w'), get_option('large_size_h'), false, null, $wp_upload_dir['path'], 90 );
        if ( ! file_exists( $destination_file ) ){
            // Copy original to our protected area
            @copy( $original_file, $destination_file );

            // Copy (rename) our resized image to the original
            @copy( $resized_image, dirname( $resized_image ) . '/' . basename( $original_file ) );
        }
    }
}


/**
 * Moves and uploaded file from the uploads dir into the "protected"
 * Sell Media dir, note the original file is deleted.
 *
 * @param $original_file Full path of the file with the file.
 * @since 1.0.1
 */
function sell_media_default_move( $original_file=null ){

    $dir = wp_upload_dir();
    $original_file_path = $dir['basedir'] . '/' . $original_file;
    $destination_file = $dir['basedir'] . SellMedia::upload_dir . '/' . $original_file;

    if ( file_exists( $original_file_path ) ){
        // Check if the destinatin dir is exists, i.e.
        // sell_media/YYYY/MM if not we create it first
        $destination_dir = dirname( $destination_file );

        if ( ! file_exists( $destination_dir ) ){
            wp_mkdir_p( dirname( $destination_dir ) );
        }

        // Copy original to our protected area
        @copy( $original_file_path, $destination_file );
        @unlink( $original_file_path );
    }
}