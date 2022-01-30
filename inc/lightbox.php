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
function sell_media_lightbox_link( $post_id = null, $attachment_id = null ) {

	$settings = sell_media_get_plugin_options();
	// Disables lightbox link
	if ( empty( $settings->lightbox_page ) ) {
		return false;
	}

	$item                   = array();
	$item['post_id']        = $post_id;
	$item['attachment_id']  = ( ! empty( $attachment_id ) ) ? $attachment_id : sell_media_get_attachment_id( $post_id );

	$html = '<a href="javascript:void(0);" title="' . sell_media_get_lightbox_text( $item ) . '" id="lightbox-' . $post_id . '" class="sell-media-add-to-lightbox" data-id="' . $post_id . '" data-attachment-id="' . $item['attachment_id'] . '">' . sell_media_get_lightbox_text( $item ) . '</a>';

	// display lightbox notice on single posts
	if ( is_single() ) {

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
	$text = ( sell_media_get_lightbox_state( $item ) ) ? __( 'Remove from lightbox', 'sell_media' ) : __( 'Save to Lightbox', 'sell_media' );
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
		$items = json_decode( stripslashes( sanitize_text_field( $_COOKIE['sell_media_lightbox'] ) ), true );
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
	ob_start();
	?>
	<div id="sell-media-lightbox-content" class="sell-media">
	<?php

	do_action( 'sell_media_bofore_lightbox_item_container' );

	?>
		<div id="sell-media-grid-item-container" class="<?php echo esc_attr( apply_filters( 'sell_media_grid_item_container_class', 'sell-media-grid-item-container' ) ); ?>">
		<?php
			echo wp_kses( sell_media_lightbox_query(), array(
				'a' => array('href' => true, 'title' => true, 'id' => true, 'class' => true, 'data-*' => true),
				'div' => array('class' => true, 'id' => true, 'data-*' => true),
			));
		?>
		</div>
	<?php

	do_action( 'sell_media_after_lightbox_item_container' );

	?>
	</div>
	<?php

	$html = ob_get_contents();
	ob_end_clean();
	return apply_filters( 'sell_media_lightbox', $html );
}
add_shortcode( 'sell_media_lightbox', 'sell_media_lightbox_shortcode' );

/**
 * Added html to remove lightbox items before lightbox container.
 */
function sell_media_lightbox_remove_items() {
	if ( ! empty( $_COOKIE['sell_media_lightbox'] ) ) {
	    ?>
        <p class="empty-lightbox" title="<?php echo esc_attr__( 'Remove all from lightbox', 'sell_media' ); ?>"
            data-empty-text="<?php echo esc_attr__( 'Your lightbox is empty.', 'sell_media' ); ?>"><?php echo esc_html__( 'Remove All', 'sell_media' ); ?></p>
	    <?php
    }
}

add_action( 'sell_media_bofore_lightbox_item_container', 'sell_media_lightbox_remove_items' );

/**
 * Query lightbox items
 */
function sell_media_lightbox_query() {
	$html = '';

	// Decode the lightbox array of IDs since they're encoded
	if ( isset( $_COOKIE['sell_media_lightbox'] ) ) {
		$items = json_decode( sanitize_key( $_COOKIE['sell_media_lightbox'] ), true );
	}
	
	// Check if items in lightbox
	if ( isset( $items ) ) {

		array_walk($items, function(&$value, &$key) {
			$value['post_id'] = intval($value['post_id']);
			$value['attachment_id'] = intval($value['attachment_id']);
		});
		
		$i = 0;

		// loop over items from 'sell_media_lightbox' cookie
		foreach ( $items as $item ) {

			// Old cookies were stored as simple array of ids
			// New cookies are stored as a multidimensional array of ids
			// so that we can support attachments (galleries)
			$post_id        = ( ! empty( $item['post_id'] ) ) ? intval($item['post_id']) : intval($item);
			$attachment_id  = ( ! empty( $item['attachment_id'] ) ) ? intval($item['attachment_id']) : intval(sell_media_get_attachment_id( $post_id ));

			$i++;
			$class = apply_filters( 'sell_media_grid_item_class', 'sell-media-grid-item', $post_id );

			$html .= '<div id="sell-media-' . $attachment_id . '" class="sell-media-grid-single-item ' . $class . '">';
			$html .= '<a href="' . esc_url( get_permalink( $attachment_id ) ) . '" class="sell-media-item">';
			$mime_type = get_post_mime_type( $attachment_id );
			// if selling video or audio, show the post_id thumbnail
			if ( SellMediaAudioVideo::is_video_item( $post_id ) || SellMediaAudioVideo::is_audio_item( $post_id ) || 'application/pdf' === $mime_type || 'application/zip' === $mime_type ) {
				$html .= sell_media_item_icon( $post_id, apply_filters( 'sell_media_thumbnail', 'medium' ), false );
			} else {
				$html .= sell_media_item_icon( $attachment_id, apply_filters( 'sell_media_thumbnail', 'medium' ), false );
			}
			$html .= '<div class="sell-media-quick-view" data-product-id="' . esc_attr( $post_id ) . '" data-attachment-id="' . esc_attr( $attachment_id ) . '">' . apply_filters( 'sell_media_quick_view_text', __( 'Quick View', 'sell_media' ), $post_id, $attachment_id ) . '</div>';
			$html .= '</a>';
			$html .= sell_media_lightbox_link( $post_id, $attachment_id );
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
function sell_media_update_lightbox() {

	$_send_data = array();
	if (!isset($_POST['_nonce']) || !wp_verify_nonce($_POST['_nonce'], 'sell_media_ajax-nonce')) {
		$_send_data['status'] = false;
		wp_send_json($_send_data);
		die();
	}
	// id is sent over in ajax request
	if ( isset( $_POST['post_id'] ) && isset( $_POST['attachment_id'] ) ) {
		
		// build lightbox item array
		$item = array(
			'post_id'       => (isset($_POST['post_id']) && '' != sanitize_text_field($_POST['post_id'])) ? intval($_POST['post_id']) : 0,
			'attachment_id' => (isset($_POST['attachment_id']) && '' != sanitize_text_field($_POST['attachment_id'])) ? intval($_POST['attachment_id']) : 0,
		);
		
		// check if cookie already exists
		if ( isset( $_COOKIE['sell_media_lightbox'] ) ) {
			$items = json_decode( stripslashes( sanitize_text_field( $_COOKIE['sell_media_lightbox'] ) ), true );
		
			array_walk($items, function(&$value, &$key) {
				$value['post_id'] = intval($value['post_id']);
				$value['attachment_id'] = intval($value['attachment_id']);
			});
			
			// if not in lightbox, add it and change to say remove
			if ( ! in_array( $item, $items ) ) {

				$items[] = $item;
				$text = __( 'Remove from lightbox', 'sell_media' );
			// it is in lightbox, remove it
			} else {
				$remove = array_search( $item, $items );
				unset( $items[$remove] );
				$text = __( 'Save to Lightbox', 'sell_media' );
			}
			$cookie = $items;
		// cookie doesn't already exist, so set cookie to the id
		} else {
			$cookie = array( $item );
			$text = __( 'Remove from lightbox', 'sell_media' );
		}

		// allow text to be filtered
		$text = apply_filters( 'sell_media_get_lightbox_text', $text, $item );

		// set cookie
		if ( $cookie ) {
			setcookie( 'sell_media_lightbox', wp_json_encode( $cookie ), time() + 3600 * 24 * 365, '/' );
		} else {
			setcookie( 'sell_media_lightbox', '', time() + 3600 * 24 * 365, '/' );
		}

		// generate the response
		$response = array(
                        'post_ids' => $cookie,
                        'post_id' => $item['post_id'],
                        'count' => count( $cookie ),
                        'text' => $text,
                    );

		wp_send_json($response, 200);
	}

}
add_action( 'wp_ajax_sell_media_update_lightbox', 'sell_media_update_lightbox' );
add_action( 'wp_ajax_nopriv_sell_media_update_lightbox', 'sell_media_update_lightbox' );