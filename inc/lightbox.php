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
    wp_enqueue_script( 'sell_media_lightbox', SELL_MEDIA_PLUGIN_URL . 'js/sell_media_lightbox.js', array( 'jquery' ), SELL_MEDIA_VERSION );
    $html = '<div id="sell-media-lightbox-content" class="sell-media">' . __( "Loading...", "sell_media" ) . '</div>';
    return $html;
}
add_shortcode( 'sell_media_lightbox', 'sell_media_lightbox_shortcode' );


/**
 * Ajax callback to list items in lightbox
 */
function sell_media_lightbox_generator() {
    $html = null;
    $lightbox_ids = json_decode( $_POST['lightbox_ids'] );
    if ( ! empty( $lightbox_ids ) ) {
        $args = array(
            'posts_per_page' => -1,
            'post_type' => 'sell_media_item',
            'post__in' => $lightbox_ids
        );
        $i = 0;
        $thumbSize = ( has_image_size('sell_media_item' ) ) ? 'sell_media_item' : 'medium';
        $posts = New WP_Query( $args );
        if ( $posts->posts ) {

            $html .= '<div class="sell-media-grid-container">';

            foreach( $posts->posts as $post ) {
                $i++;
                $html .= sell_media_content_loop( $post->ID, $i );
            }

            $html .= '</div>';

        }
        echo $html;
        $i = 0;
    } else {
        _e( 'Nothing saved in lightbox.', 'sell_media' );
    }
    die;
}
add_action( 'wp_ajax_sell_media_lightbox', 'sell_media_lightbox_generator' );
add_action( 'wp_ajax_nopriv_sell_media_lightbox', 'sell_media_lightbox_generator' );