<?php

/**
 * Lightbox Functions
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

/**
 * Lightbox link
 *
 * @param  int $post_id
 * @return html
 * @since 1.9.2
 */
function sell_media_lightbox_link( $post_id ) {
    $html = '<a href="javascript:void(0);" title="' . sell_media_get_lightbox_text( $post_id ) . '" id="lightbox-' . $post_id . '" class="add-to-lightbox" data-id="' . $post_id . '">' . sell_media_get_lightbox_text( $post_id ) . '</a>';
    return apply_filters( 'sell_media_lightbox_link', $html, $post_id );
}

/**
 * Adds the 'sell_media_lightbox' short code to the editor. [sell_media_lightbox]
 *
 * @since 1.9.2
 */
function sell_media_lightbox_shortcode() {

    $html  = '<div id="sell-media-lightbox-content" class="sell-media">';
    $html .= '<div id="sell-media-grid-container" class="sell-media-grid-container">';
    $html .= sell_media_lightbox_query();
    $html .= '</div>';
    $html .= '</div>';

    return apply_filters( 'sell_media_lightbox', $html );
}
add_shortcode( 'sell_media_lightbox', 'sell_media_lightbox_shortcode' );


/**
 * Query lightbox items
 */
function sell_media_lightbox_query() {
    $html = '';

    // Decode the lightbox array of IDs since they're encoded
    $ids = json_decode( stripslashes( $_COOKIE['sell_media_lightbox'] ), true );

    // Check if items in lightbox
    if ( isset( $ids ) ) {

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
                $html .= apply_filters( 'sell_media_content_loop', $post->ID, $i );
            }

        }
        $i = 0;

    } else {

        $html = __( 'Nothing saved in lightbox.', 'sell_media' );

    }

    return $html;
}

/**
 * Lightbox state
 *
 * @var $post_id
 * @return bool
 */
function sell_media_get_lightbox_state( $post_id ) {

    // default state
    $state = false;

    // check if cookie already exists
    if ( isset( $_COOKIE['sell_media_lightbox'] ) ) {
        $ids = json_decode( stripslashes( $_COOKIE['sell_media_lightbox'] ), true );

        // if id is in lightbox, return true
        if ( in_array( $post_id, $ids ) ) {
            $state = true;
        }
    }

    return $state;
}

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

            // if not in lightbox, add it and change to say remove
            if ( ! in_array( $id, $ids ) ) {
                $ids[] = $id;
                $text = __( 'Remove from lightbox', 'sell_media' );
            // it is in lightbox, remove it
            } else {
                $remove = array_search( $id, $ids );
                unset( $ids[$remove] );
                $text = __( 'Add to lightbox', 'sell_media' );
            }
            $cookie = $ids;
        // cookie doesn't already exist, so set cookie to the id
        } else {
            $cookie = array( $id );
            $text = __( 'Remove from lightbox', 'sell_media' );
        }

        // set cookie
        if ( $cookie ) {
            setcookie( 'sell_media_lightbox', json_encode( $cookie ), time()+3600*24*365,'/' );
        } else {
            setcookie( 'sell_media_lightbox', '', time()+3600*24*365,'/' );
        }

        // generate the response
        $response = json_encode(
            array(
                'post_ids' => $cookie,
                'post_id' => $id,
                'count' => count( $cookie ),
                'text' => $text
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

/**
 * Lightbox text
 */
function sell_media_get_lightbox_text( $post_id ) {

    if ( sell_media_get_lightbox_state( $post_id) ) {
        $text = __( 'Remove from lightbox', 'sell_media' );
    } else {
        $text = __( 'Add to lightbox', 'sell_media' );
    }

    return apply_filters( 'sell_media_get_lightbox_text', $text, $post_id );
}