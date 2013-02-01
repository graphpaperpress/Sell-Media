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
function sell_media_image_filename( $post_id=null, $echo=true ) {

    $thumb_id = get_post_thumbnail_id( $post_id );
    $filename = basename( get_attached_file( $thumb_id ) );

    if ( $echo )
        print $filename;
    else
        return $filename;
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
function sell_media_item_buy_button( $post_id=null, $button=null, $text=null, $echo=true ) {

    $thumb_id = get_post_thumbnail_id( $post_id );
    $html = '<a href="javascript:void(0)" data-sell_media-product-id="' . esc_attr( $post_id ) . '" data-sell_media-thumb-id="' . esc_attr( $thumb_id ) . '" class="sell-media-cart-trigger sell-media-buy-' . $button . '">' . $text . '</a>';

    if ( $echo ) print $html; else return $html;
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


/**
 * Returns item size
 *
 * @since 0.1
 * @return string
 */
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


/**
 * Retrives and prints the price of an item
 *
 * @since 0.1
 * @return string
 */
function sell_media_item_price( $post_id=null, $currency=true, $size=null ){

    $original_price = get_post_meta( $post_id, 'sell_media_price', true );

    /**
     * If this Item has a default price we use it, i.e., "original price".
     * Else we fall back on the price that is set in settings.
     */
    if ( $original_price ){
        $price = $original_price;
    } else {
        $size_settings = get_option('sell_media_size_settings');
        if ( ! empty( $size_settings['default_price'] ) ){
            $price = $size_settings['default_price'];
        }
    }

    /**
     * If we have a size we get the size from the post meta
     */
    if ( ! empty( $size ) ){
        $price = get_post_meta( $post_id, 'sell_media_price_' . $size, true );
    }

    if ( $currency ){
        $price = sell_media_get_currency_symbol() . $price;
    }

    print $price;
}


/**
 * Determines the default icon used for an Attachment. If an
 * image mime type is detected than the attachment image is used.
 */
function sell_media_item_icon( $attachment_id=null, $size='medium', $echo=true ){

    if ( ! empty( $_POST['attachment_id'] ) ){
        $attachment_id = $_POST['attachment_id'];
    }

    if ( empty( $attachment_id ) )
        return;

    $mime_type = get_post_mime_type( $attachment_id );
    $image_height = null;
    $image_width = null;
    $sell_media_item_id = get_post_meta( $attachment_id, '_sell_media_for_sale_product_id', true );
    $image_title = get_the_title( $sell_media_item_id );
    $_thumbnail_id = get_post_meta( $sell_media_item_id, '_thumbnail_id', true );

    /**
     * Since we always want to return the actual image associated with this item for sale
     * on the edit/add new item page. We check the global $pagenow variable, vs. adding
     * coniditionals through out the code.
     */
    global $pagenow;
    global $post_type;
    if ( ! empty( $_thumbnail_id ) && $post_type == 'sell_media_item' && $pagenow != 'post.php' ){
        $attachment_id = $_thumbnail_id;
    }

    $image = wp_get_attachment_image_src( $attachment_id, $size );

    switch( $mime_type ){
        case 'image/jpeg':
        case 'image/png':
        case 'image/gif':
            $image_src = $image[0];
            $image_height = $image[2];
            $image_width = $image[1];
            break;
        case 'video/mpeg':
        case 'video/mp4':
        case 'video/quicktime':
        case 'application/octet-stream':
            $mime_type = 'video/mpeg';
            break;
        case 'text/csv':
        case 'text/plain':
        case 'text/xml':
        case 'text/document':
        case 'application/pdf':
            if ( $image ){
                $image_src = $image[0];
                $image_height = $image[2];
                $image_width = $image[1];
            } else {
                $image_src = wp_mime_type_icon( 'text/document' );
            }
            break;
        case 'application/zip':
            if ( $image ){
                $image_src = $image[0];
                $image_height = $image[2];
                $image_width = $image[1];
            } else {
                $image_src = includes_url() . 'images/crystal/archive.png';
            }
            break;
        default:
            if ( $image ){
                $image_src = $image[0];
                $image_height = $image[2];
                $image_width = $image[1];
            } else {
                $image_src = wp_mime_type_icon( $mime_type );
            }
    }

    $medium_url = wp_get_attachment_image_src( $attachment_id, 'medium' );
    if ( $medium_url )
        $medium_url = $medium_url[0];
    else
        $medium_url = null;

    $icon =  '<img src="' . $image_src . '" class="sell_media_image wp-post-image icon" title="' . $image_title . '" alt="' . $image_title . '" data-sell_media_medium_url="' . $medium_url . '" data-sell_media_item_id="' . $sell_media_item_id . '" height="' . $image_height . '" width="' . $image_width . '" style="max-width:100%;height:auto;"/>';

    if ( $echo )
        print $icon;
    else
        return $icon;

    /**
     * If attachment ID is set via $_POST we are doing ajax. So we
     * must die.
     */
    if ( ! empty( $_POST['action'] ) && $_POST['action'] == 'sell_media_item_icon' ) die();
}
add_action( 'wp_ajax_sell_media_item_icon', 'sell_media_item_icon' );