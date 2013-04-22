<?php

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
function sell_media_move_image_from_attachment( $attachment_id=null ){

    $original_file = get_attached_file( $attachment_id );

    // Extract IPTC meta info from the uploaded image.
    $city = sell_media_iptc_parser( 'city', $original_file );
    $state = sell_media_iptc_parser( 'state', $original_file );
    $creator = sell_media_iptc_parser( 'creator', $original_file );
    $keywords = sell_media_iptc_parser( 'keywords', $original_file );

    global $post;
    $product_id = empty( $post->ID ) ? get_post_meta( $attachment_id, '_sell_media_for_sale_product_id', true ) : $post->ID;

    // Save iptc info as taxonomies
    if ( ! empty( $product_id ) ) {
        if ( $city )
            sell_media_iptc_save( 'city', $city, $product_id );

        if ( $state )
            sell_media_iptc_save( 'state', $state, $product_id );

        if ( $creator )
            sell_media_iptc_save( 'creator', $creator, $product_id );

        if ( $keywords )
            sell_media_iptc_save( 'keywords', $keywords, $product_id );
    }


    // Assign the FULL PATH to our destination file.
    $wp_upload_dir = wp_upload_dir();

    $destination_file = $wp_upload_dir['basedir'] . SellMedia::upload_dir . $wp_upload_dir['subdir'] . '/' . basename( $original_file );
    $destination_dir  = $wp_upload_dir['basedir'] . SellMedia::upload_dir . $wp_upload_dir['subdir'] . '/';


    // Check if the destinatin directory exists, i.e.
    // wp-content/uploads/sell_media/YYYY/MM if not we create it.
    if ( ! file_exists( dirname( $destination_file ) ) ){
        wp_mkdir_p( dirname( $destination_file ) );
    }


    /**
     * Resize original file down to the largest size set in the Media Settings
     *
     * Determine which version of WP we are using.
     * Would rather check if the correct function exists
     * but the function 'image_make_intermediate_size' uses other
     * functions that are in trunk and not in 3.4
     */
    global $wp_version;
    if ( version_compare( $wp_version, '3.5', '>=' ) ){


        /**
         * Resize the "original" to our largest size set in the Media Settings.
         *
         * This creates a file named filename-[width]x[height].jpg
         * From here the "original" file is still in our uploads dir, its needed to create
         * the additional image sizes. Once we're done making the additional sizes, we rename
         * the filename-[width]x[height].jpg to filename.jpg, thus having a resized "original"
         * file.
         */
        $image_new_size = image_make_intermediate_size( $original_file, get_option('large_size_w'), get_option('large_size_h'), false );


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

            /**
             * Move our originally upload file into the protected area
             */
            copy( $original_file, $destination_file );
            if ( ! $keep_original ) unlink( $original_file );

            /**
             * We rename our resize original file i.e., "filename-[width]x[height].jpg" located in our uploads directory
             * to "filename.jpg"
             */
            $new_path_source = dirname( $original_file ) . '/' . basename( $resized_image );
            $new_path_destination = $original_file;
            copy( $new_path_source, $new_path_destination );
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