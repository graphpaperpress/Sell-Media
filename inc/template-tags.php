<?php

/**
 * Print attached image
 *
 * @access      public
 * @since       0.1
 * @return      html
 */
function sell_media_image( $post_id=null ) {

    $thumb_id = get_post_thumbnail_id( $post_id );
    $attachment = get_post( $thumb_id );
    $title = $attachment->post_title;
    $size = 'large';
    $image = wp_get_attachment_image_src( $thumb_id, $size );
    $image = '<img src="' . $image[0] . '" alt="' .  $title . '" title="' .  $title . '" class="wp-post-image" />';

    print $image;

}


/**
 * Print attached image caption
 *
 * @access      public
 * @since       0.1
 * @return      string
 */
function sell_media_image_caption( $post_id=null ) {

    $thumb_id = get_post_thumbnail_id( $post_id );
    $attachment = get_post( $thumb_id );
    $title = $attachment->post_title;

    return $title;

}


/**
 * Print attached image size
 *
 * @access      public
 * @since       0.1
 * @return      html
 */
function sell_media_get_image_size( $post_id=null ) {

    $thumb_id = get_post_thumbnail_id( $post_id );
    $meta = get_post_meta( intval( $thumb_id ), '_wp_attachment_metadata' , true );

    if ( $meta['width'] && $meta['height'] )
        $size = $meta['width'] . 'x' . $meta['height'] . ' pixels';
    else
        $size = false;

    return $size;
}


/**
 * Print attached image filename
 *
 * @access      public
 * @since       0.1
 * @return      html
 */
function sell_media_image_filename( $post_id=null ) {

    $thumb_id = get_post_thumbnail_id( $post_id );
    $filename = basename( get_attached_file( $thumb_id ) );

    if ( $filename )
        print $filename;

}


/**
 * Print attached image keywords
 *
 * @access      public
 * @since       0.1
 * @return      html
 */
function sell_media_image_keywords( $post_id=null ) {

    $product_terms = wp_get_object_terms( $post_id, 'keywords' );
    if ( !empty( $product_terms ) ) {
        if ( !is_wp_error( $product_terms ) ) {
            foreach ( $product_terms as $term ) {
                echo '<a href="' . get_term_link( $term->slug, 'keywords' ) . '">' . $term->name . '</a> ';
            }
        }
    }
}


/**
 * Print the buy button
 *
 * @access      public
 * @since       0.1
 * @return      html
 */
function sell_media_item_buy_button( $post_id=null, $button=null, $text=null ) {

    $thumb_id = get_post_thumbnail_id( $post_id );
    $html = '<a href="javascript:void(0)" data-sell_media-product-id="' . esc_attr( $post_id ) . '" data-sell_media-thumb-id="' . esc_attr( $thumb_id ) . '" class="sell-media-cart-trigger sell-media-buy-' . $button . '">' . $text . '</a>';

    print $html;

}


/**
 * Check if item is part of a taxonomy
 *
 * @access      public
 * @since       0.1
 * @return      boolean
 */
function sell_media_item_has_taxonomy_terms( $post_id=null, $taxonomy=null ) {

    $terms = wp_get_post_terms( $post_id, $taxonomy );

    if ( empty ( $terms ) )
        return false;
    else
        return true;

}


function sell_media_item_size( $post_id=null ){

    $mime_type = get_post_mime_type( get_post_thumbnail_id( $post_id ) );
    $size = false;

    switch( $mime_type ){
        case 'image/jpeg':
        case 'image/png':
        case 'image/gif':
            $size = sell_media_get_image_size( $post_id );
            break;
        case 'video/mpeg':
        case 'video/mp4':
        case 'video/quicktime':
        case 'application/octet-stream':
            return;
        case 'text/csv':
        case 'text/plain':
        case 'text/xml':
        case 'text/document':
        case 'application/pdf':
            return;
    }

    return $size;
}