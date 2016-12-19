<?php

/**
 * Admin Helper Functions
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Checks if the attached file is an image
 * and runs functions that resizes and moves
 * high resolution files into protected dir.
 * If attachment isn't an image, it just moves it.
 * Original files are deleted.
 *
 * @param  [integer] $attachment_id [The attachment id]
 * @return [null]
 */
function sell_media_move_file( $attachment_id ) {
	
	do_action( 'sell_media_before_move_file', $attachment_id );

	if ( wp_attachment_is_image( $attachment_id ) ) {
		Sell_Media()->images->move_image_from_attachment( $attachment_id );
	} else {
		$attached_file = get_attached_file( $attachment_id );
		sell_media_default_move( $attached_file );
	}

	do_action( 'sell_media_after_move_file', $attachment_id );
}

/**
 * Moves and uploaded file from the uploads dir into the "protected"
 * sell_media dir. Note the original file is deleted.
 *
 * @param $original_file Full path of the file with the file.
 * @since 1.0.1
 */
function sell_media_default_move( $original_file=null ){

	$dir = wp_upload_dir();
	$protected_dir = $dir['basedir'] . '/sell_media';
	$destination_file_path = str_replace( $dir['basedir'], $protected_dir, $original_file );

	if ( file_exists( $original_file ) ){

		if ( ! file_exists( dirname( $destination_file_path ) ) ){
			wp_mkdir_p( dirname( $destination_file_path ) );
		}

		// Copy original to our protected area
		@copy( $original_file, $destination_file_path );
		@unlink( $original_file );
	}
}


/**
 * Order the post type by date and descending order in admin
 *
 * @param $wp_query
 * @since 1.8.5
 */
function sell_media_post_type_admin_order( $wp_query ) {

	if ( is_admin() ) {

		$post_type = $wp_query->query['post_type'];

		if ( $post_type == 'sell_media_item' && empty( $_GET['orderby'] ) ) {
			$wp_query->set( 'orderby', 'date' );
			$wp_query->set( 'order', 'DESC' );
		}

	}
}
//add_filter ( 'pre_get_posts', 'sell_media_post_type_admin_order' );

/**
 * List file uploads on add/edit item page
 *
 * This same markup is used in multiple places
 * So let's make it into a reusable function
 */
function sell_media_list_uploads( $attachment_id ) {

	if ( ! wp_get_attachment_url( $attachment_id ) )
		return;

	$html  = '<li class="attachment sell-media-attachment" data-post_id="' . $attachment_id . '">';
	$html .= '<a href="' . admin_url( 'post.php?post=' . $attachment_id . '&action=edit' ) . '" class="sell-media-edit dashicons dashicons-edit" data-id="' . $attachment_id . '" target="_blank"></a>';
	$html .= '<span class="sell-media-delete dashicons dashicons-no" data-id="' . $attachment_id . '"></span>';
	$html .= wp_get_attachment_image( $attachment_id, 'medium', true );
	$html .= '</li>';

	return apply_filters( 'sell_media_list_uploads', $html, $attachment_id );
}
