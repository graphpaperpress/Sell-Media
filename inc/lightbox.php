<?php

/**
 * Lightbox functions
 * @package package Sell Media
 * @since Sell Media 1.9.3
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
    if( ! empty( $lightbox_ids ) ) {
    	$args = array(
    			'posts_per_page' => -1,
    			'post_type' => 'sell_media_item',
    			'post__in' => $lightbox_ids
        );
		$posts = New WP_Query( $args );
		if ( $posts->posts ) {
            $html .= '<div class="sell-media-grid-container">';
            $thumbSize = (has_image_size('sell_media_item')) ? 'sell_media_item' : 'medium';
            foreach( $posts->posts as $post ) {
                $html .= '<div class="sell-media-grid">';
    				    $html .= '<div class="item-inner">';
    				    $html .= '<a href="'. get_permalink( $post->ID ) . '" class="lightbox-id" data-id="' . $post->ID . '">' . sell_media_item_icon( $post->ID, $thumbSize, false ) . '</a>';
    				    $html .= '<span class="item-overlay">';
                $html .= '<h3><a href="' . get_permalink( $post->ID ) . '">' . get_the_title( $post->ID ) . '</a></h3>';
                $html .= '<a href="javascript:void(0);" data-id="' . $post->ID . '" class="remove-lightbox">' . __( 'Remove', 'sell_media' ) . '</a>';
                $html .= sell_media_item_buy_button( $post->ID, 'text', __( 'Buy' ), false );
                //$html .= do_action( 'sell_media_item_overlay' );
                $html .= '</span>';
                $html .= '</div>';
    				    $html .= '</div>';
    				}
    				$html .= '</div>';
        }
		echo $html;
    } else {
        _e( 'No items', 'sell_media' );
    }
    die;
}
add_action( 'wp_ajax_sell_media_lightbox', 'sell_media_lightbox_generator' );
add_action( 'wp_ajax_nopriv_sell_media_lightbox', 'sell_media_lightbox_generator' );
