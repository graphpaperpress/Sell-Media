<?php

/**
 * Process the image that was uploaded from a meta field. Creates new image
 * sizes, moves the original image to the proctected area, and also saves IPTC
 * data if any as taxonomies/terms.
 *
 * @param $moved_file The file we are referencing
 * @param $_FILES The Global PHP $_FILES array.
 *
 * @return $destination_file The location to the new file
 */
function sell_media_item_image_meta_fields( $moved_file=null, $_FILES=null ){

    // Would rather check if the correct function exists
    // but the function 'image_make_intermediate_size' uses other
    // functions that are in trunk and not in 3.4
    if ( get_bloginfo('version') >= '3.5' ) {
        $image_new_size = image_make_intermediate_size( $moved_file, get_option('large_size_h'), get_option('large_size_w'), $crop=false );
        $resized_image = dirname( $destination ) . '/' . date('m') . '/' . $image_new_size['file'];
    } else {
        $resized_image = image_resize( $moved_file, get_option('large_size_w'), get_option('large_size_h'), false, null, $wp_upload_dir['path'], 90 );
    }

    $wp_upload_dir = wp_upload_dir();
    $destination_file = $wp_upload_dir['path'] . '/' . $_FILES['sell_media_file']['name'];
    do_action( 'sell_media_after_upload' );

    // image_resize() creates the file with the prefixed file size, we
    // don't want this. So we copy the resized image to the same location
    // just without the prefixed image resolution.

    // We still don't have an original, so we copy the resized image to.
    @copy( $resized_image, $destination_file );

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