<?php

/**
 * Lightbox Functions
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

/**
 * Adds the 'sell_media_lightbox' short code to the editor. [sell_media_lightbox]
 *
 * @since 1.9.3
 */
function sell_media_lightbox_shortcode() {

    $html  = '<div id="sell-media-lightbox-content" class="sell-media">';
    $html .= '<div class="sell-media-grid-container">';
    $html .= sell_media_lightbox_query();
    $html .= '</div>';
    $html .= '</div>';

    return $html;
}
add_shortcode( 'sell_media_lightbox', 'sell_media_lightbox_shortcode' );


/**
 * Query lightbox items
 */
function sell_media_lightbox_query() {
    $html = '';

    // Check if items in lightbox
    if ( isset( $_COOKIE['sell_media_lightbox'] ) ) {

        // Decode the lightbox array of IDs since they're encoded
        $ids = json_decode( stripslashes( $_COOKIE['sell_media_lightbox'] ), true );

        // Setup query args
        $args = array(
            'posts_per_page' => -1,
            'post_type' => 'sell_media_item',
            'post__in' => (array) $ids
        );

        $i = 0;
        $posts = new WP_Query( $args );

        if ( $posts->posts ) {

            foreach( $posts->posts as $post ) {
                $i++;
                $html .= sell_media_content_loop( $post->ID, $i );
            }

        }
        $i = 0;

    } else {
        $html = __( 'Nothing saved in lightbox.', 'sell_media' );
    }

    return $html;

    //die;
}
add_action( 'wp_ajax_sell_media_lightbox', 'sell_media_lightbox_query' );
add_action( 'wp_ajax_nopriv_sell_media_lightbox', 'sell_media_lightbox_query' );

/**
 * Update lightbox
 */
function sell_media_update_lightbox(){

    // id is sent over in ajax request
    if ( isset( $_POST['id'] ) ) {
        $id = $_POST['id'];

        // check if cookie already exists
        if ( isset( $_COOKIE['sell_media_lightbox'] ) ) {
            $ids = json_decode( stripslashes( $_COOKIE['sell_media_lightbox'] ), true );

            // make sure the id isn't already saved in lightbox
            if ( ! in_array( $id, $ids ) ) {
                $ids[] = $id;
                $cookie = $ids;
            }
        // cookie doesn't already exist, so set cookie to the id
        } else {
            $cookie = array( $id );
        }

        // set cookie
        if ( $cookie )
            setcookie( 'sell_media_lightbox', json_encode( $cookie ), time()+3600*24*365,'/' );

        // generate the response
        $response = json_encode(
            array(
                'post_id' => $id
            )
        );

        // JSON header
        header( 'Content-type: application/json' );
        echo $response;
        die();
    }

}
add_action( 'wp_ajax_sell_media_update_lightbox', 'sell_media_update_lightbox' );
add_action( 'wp_ajax_nopriv_sell_media_update_lightbox', 'sell_media_update_lightbox' );
