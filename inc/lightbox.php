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
function sell_media_lightbox_link( $post_id=null, $attachment_id=null ) {

    $item                   = array();
    $item['post_id']        = $post_id;
    $item['attachment_id']  = ( ! empty( $attachment_id ) ) ? $attachment_id : sell_media_get_attachment_id( $post_id );

    $html = '<a href="javascript:void(0);" title="' . sell_media_get_lightbox_text( $item ) . '" id="lightbox-' . $post_id . '" class="add-to-lightbox" data-id="' . $post_id . '" data-attachment-id="' . $item['attachment_id'] . '">' . sell_media_get_lightbox_text( $item ) . '</a>';

    // display lightbox notice on single posts
    if ( is_single() ) {
        $settings = sell_media_get_plugin_options();

        // show a link if lightbox page is assigned in settings
        $link = ( ! empty( $settings->lightbox_page ) ) ? '<a href="' . get_the_permalink( $settings->lightbox_page ) . '" title="' . __( 'Go to lightbox', 'sell_media' ) . '">' . __( 'lightbox', 'sell_media' ) . '</a>' : __( 'lightbox', 'sell_media' );
        // set css class based on item status
        $class = ( sell_media_get_lightbox_state( $item ) ) ? 'in-lightbox' : 'not-in-lightbox';

        // lightbox notice
        $html .= '<div class="lightbox-notice ' . $class . '">';
        $html .= '<p>' . sprintf( __( 'This item was saved to your %1$s.', 'sell_media' ), $link ) . '</p>';
        $html .= '</div>';
    }
    return apply_filters( 'sell_media_lightbox_link', $html, $post_id );
}

/**
 * Lightbox text
 */
function sell_media_get_lightbox_text( $item ) {
    $text = ( sell_media_get_lightbox_state( $item ) ) ? __( 'Remove', 'sell_media' ) : __( 'Save', 'sell_media' );
    return apply_filters( 'sell_media_get_lightbox_text', $text, $item );
}

/**
 * Lightbox state
 *
 * @var $post_id
 * @return bool
 */
function sell_media_get_lightbox_state( $item ) {

    // default state
    $state = false;

    // check if cookie already exists
    if ( isset( $_COOKIE['sell_media_lightbox'] ) ) {
        $items = json_decode( stripslashes( $_COOKIE['sell_media_lightbox'] ), true );
        // if id is in lightbox, return true
        if ( in_array( $item, $items ) ) {
            $state = true;
        }
    }

    return $state;
}

/**
 * Adds the 'sell_media_lightbox' short code to the editor. [sell_media_lightbox]
 *
 * @since 1.9.2
 */
function sell_media_lightbox_shortcode() {

    $html  = '<div id="sell-media-lightbox-content" class="sell-media">';
    if ( ! empty( $_COOKIE['sell_media_lightbox'] ) ) {
         $html .= '<p class="empty-lightbox" data-empty-text="' . __( 'Your lightbox is empty.', 'sell_media' ) . '">' . __( 'Remove all from lightbox', 'sell_media' ) . '</p>';
    }
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
    if ( isset( $_COOKIE['sell_media_lightbox'] ) )
        $items = json_decode( stripslashes( $_COOKIE['sell_media_lightbox'] ), true );

    // Check if items in lightbox
    if ( isset( $items ) ) {

        $i = 0;

        // loop over items from 'sell_media_lightbox' cookie
        foreach ( $items as $item ) {

            // Old cookies were stored as simple array of ids
            // New cookies are stored as a multidimensional array of ids
            // so that we can support attachments (galleries)
            $post_id        = ( ! empty( $item['post_id'] ) ) ? $item['post_id'] : $item;
            $attachment_id  = ( ! empty( $item['attachment_id'] ) ) ? $item['attachment_id'] : sell_media_get_attachment_id( $post_id );
            $permalink      = ( ! empty( $item['attachment_id'] ) ) ? add_query_arg( 'id', $attachment_id, get_permalink( $post_id ) ) : get_permalink( $attachment_id );

            $i++;
            $class = ( $i %3 == 0 ) ? ' end' : '';

            $html .= '<div id="sell-media-' . $attachment_id . '" class="sell-media-grid' . $class . '">';
            $html .= '<div class="item-inner">';
            $html .= '<a href="' . esc_url( $permalink ) . '">' . sell_media_item_icon( $attachment_id, apply_filters( 'sell_media_thumbnail', 'medium' ), false ) . '</a>';
            $html .= '<span class="item-overlay">';
            $html .= '<h3><a href="' . esc_url( $permalink ) . '">' . get_the_title( $post_id ) . '</a></h3>';
            $html .= sell_media_item_buy_button( $post_id, $attachment_id, 'text', __( 'Buy' ), false );
            $html .= sell_media_lightbox_link( $post_id, $attachment_id );
            $html .= '</span>';
            $html .= '</div>';
            $html .= '</div>';
        }

    } else {

        $html .= __( 'Your lightbox is empty.', 'sell_media' );

    }

    return $html;
}

/**
 * Update lightbox
 */
function sell_media_update_lightbox(){

    // id is sent over in ajax request
    if ( isset( $_POST['post_id'] ) && isset( $_POST['attachment_id'] ) ) {

        // build lightbox item array
        $item = array(
            'post_id'       => $_POST['post_id'],
            'attachment_id' => $_POST['attachment_id']
        );

        // check if cookie already exists
        if ( isset( $_COOKIE['sell_media_lightbox'] ) ) {
            $items = json_decode( stripslashes( $_COOKIE['sell_media_lightbox'] ), true );

            // if not in lightbox, add it and change to say remove
            if ( ! in_array( $item, $items ) ) {

                $items[] = $item;
                $text = __( 'Remove', 'sell_media' );
            // it is in lightbox, remove it
            } else {
                $remove = array_search( $item, $items );
                unset( $items[$remove] );
                $text = __( 'Save', 'sell_media' );
            }
            $cookie = $items;
        // cookie doesn't already exist, so set cookie to the id
        } else {
            $cookie = array( $item );
            $text = __( 'Remove', 'sell_media' );
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
                'post_id' => $item['post_id'],
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