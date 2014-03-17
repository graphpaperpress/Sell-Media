<?php


/**
 * Print the buy button
 *
 * @access      public
 * @since       0.1
 * @return      html
 */
function sell_media_item_buy_button( $post_id=null, $button=null, $text=null, $echo=true ) {

    $thumb_id = get_post_thumbnail_id( $post_id );
    $text = apply_filters('sell_media_purchase_text', $text, $post_id );
    $html = '<a href="javascript:void(0)" data-sell_media-product-id="' . esc_attr( $post_id ) . '" data-sell_media-thumb-id="' . esc_attr( $thumb_id ) . '" class="sell-media-cart-trigger sell-media-' . $button . '">' . $text . '</a>';

    if ( $echo ) print $html; else return $html;
}


/**
 * Determines the image source for a product
 * @return (string) url to product image or feature image
 */
function sell_media_item_image_src( $post_id=null ) {

    $attachment_id = get_post_meta( $_POST['product_id'], '_sell_media_attachment_id', true );
    $image = wp_get_attachment_image_src( $attachment_id, 'medium' );
    $featured_image_id = get_post_thumbnail_id( $_POST['product_id'] );
    $featured_image = wp_get_attachment_image_src( $featured_image_id, 'medium' );

    if ( $image[0] )
        $image = $image[0];
    else
        $image = $featured_image[0];

    return $image;
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

    if ( ! empty( $_POST['attachment_size'] ) )
        $size = $_POST['attachment_size'];

    $mime_type = get_post_mime_type( $attachment_id );
    $image_height = $image_width = null;
    $post_id = get_post_meta( $attachment_id, '_sell_media_for_sale_product_id', true );
    $image_title = get_the_title( $post_id );
    $_thumbnail_id = get_post_thumbnail_id( $post_id );

    /**
     * Since we always want to return the actual image associated with this item for sale
     * on the edit/add new item page. We check the global $pagenow variable, vs. adding
     * conditionals through out the code.
     */
    global $pagenow;
    global $post_type;
    if ( ! empty( $_thumbnail_id )
        && $post_type == 'sell_media_item'
        && $pagenow != 'post.php'
        || ! empty( $_thumbnail_id ) ){
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
            if ( $image ){
                $image_src = $image[0];
                $image_height = $image[2];
                $image_width = $image[1];
            } else {
                $image_src = wp_mime_type_icon( 'video/mpeg' );
            }
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

    $icon =  '<img src="' . $image_src . '" class="sell_media_image wp-post-image" title="' . $image_title . '" alt="' . $image_title . '" data-sell_media_medium_url="' . $medium_url . '" data-sell_media_item_id="' . $post_id . '" height="' . $image_height . '" width="' . $image_width . '" style="max-width:100%;height:auto;"/>';

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


/**
 * Optionally prints the plugin credit
 * Off by default in compliance with WordPress best practices
 * http://wordpress.org/extend/plugins/about/guidelines/
 *
 * @since 1.2.6
 * @author Thad Allender
 */
function sell_media_plugin_credit() {
    $settings = sell_media_get_plugin_options();

    if ( true == $settings->plugin_credit ) {
        printf( '%s <a href="http://graphpaperpress.com/plugins/sell-media/" title="Sell Media WordPress plugin">Sell Media</a>', __( 'Shopping cart by ', 'sell_media' ) );
    }
}


/**
 * Gets the except of a post by post id
 *
 * @since 1.8.5
 * @author Thad Allender
 */
function sell_media_get_excerpt( $post_id, $excerpt_length = 140, $trailing_character = '&nbsp;&hellip;' ) {
    $the_post = get_post( $post_id );
    $the_excerpt = strip_tags( strip_shortcodes( $the_post->post_excerpt ) );

    if ( empty( $the_excerpt ) )
      $the_excerpt = strip_tags( strip_shortcodes( $the_post->post_content ) );

    $words = explode( ' ', $the_excerpt, $excerpt_length + 1 );

    if( count( $words ) > $excerpt_length )
      $words = array_slice( $words, 0, $excerpt_length );

    $the_excerpt = implode( ' ', $words ) . $trailing_character;
    return $the_excerpt;
}