<?php

/**
 * Downloads Class
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

Class SellMediaDownload {

	public function __construct(){
		add_action( 'init', array( &$this, 'download') , 100 );
	}


	/**
	 * Set the file headers and force the download of a given file
	 *
	 * @return void
	 */
	public function download(){
		if ( isset( $_GET['download'] ) && isset( $_GET['payment_id'] ) && ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce($_GET['_wpnonce'], 'download_media') ) ) {

			$transaction_id = (isset($_GET['download'])) ? sanitize_text_field(urldecode( $_GET['download'] )) : '';
			$payment_id     = (isset($_GET['payment_id'])) ? intval(urldecode( $_GET['payment_id'] )) : '';
			$product_id     = (isset($_GET['product_id'])) ? intval(urldecode( $_GET['product_id'] )) : '';
			
			// Old download links might not have attachment_id set.
			// This means they were purchased before we added support
			// for multiple attachments. So, we just grab the first
			// attachment_id saved in post meta.
			$attachment_id  = ( ! empty( $_GET['attachment_id'] ) ) ? intval(urldecode( $_GET['attachment_id'] )) : sell_media_get_attachment_id( $product_id );
			$size_id        = ( ! empty( $_GET['size_id'] ) ) ? sanitize_text_field(urldecode( $_GET['size_id'] )) : null;

			$verified = apply_filters( 'sell_media_verify_download', $this->verify( $transaction_id, $payment_id, $product_id, $attachment_id, $size_id ), $product_id );

			if ( $verified ) {

				$file = Sell_Media()->products->get_protected_file( $product_id, $attachment_id );
				$file_exists = file_exists( $file );				
				$file_exists = $this->is_file_url_valid( $file );

				$file_type = wp_check_filetype( $file );

				if ( ! ini_get( 'safe_mode' ) ){
					set_time_limit( 0 );
				}

				/**
				 * File Download
				 */
				if ( function_exists( 'apache_setenv' ) ) @apache_setenv('no-gzip', 1);
				@ini_set( 'zlib.output_compression', 'Off' );
				$file_url = strtok($file, '?');
				nocache_headers();
				header( "Robots: none" );
				header( "Content-Type: " . $file_type['type'] . "" );
				header( "Content-Description: File Transfer" );
				header( "Content-Disposition: attachment; filename=\"" . basename( $file_url ) . "\"" );
				header( "Content-Transfer-Encoding: binary" );

				// If image, generate the image sizes purchased and create a download
				if ( wp_attachment_is_image( $attachment_id ) ){
					// Don't resize images if original size is purchased.
					// Also helps retain metadata, since WP strips image metadata during resize
					// https://github.com/graphpaperpress/Sell-Media/issues/740
					// https://core.trac.wordpress.org/ticket/11877
					if ( 'original' == $size_id ) {
						$this->download_file( $file );
					} else {
						$this->download_image( $product_id, $attachment_id, $size_id );
					}
				}
				// Otherwise, just deliver the download
				else {
					$this->download_file( $file );
				}
				do_action( 'sell_media_after_successful_download', $product_id );
				exit();
			} else {
				do_action( 'sell_media_before_failed_download', $product_id, $attachment_id );
				wp_die( __( 'You do not have permission to download this file', 'sell_media'), __( 'Purchase Verification Failed', 'sell_media' ) );
			}
			exit;
		}

		// Rend purchase receipt?
		if ( isset( $_GET['resend_email'] ) && isset( $_GET['payment_id'] ) ){
			$payment_id = intval($_GET['payment_id']);
			$payment_email = get_meta_key( $payment_id, 'email' );

			Sell_Media()->payments->email_receipt( $payment_id, $payment_email );
		}
	}

	/**
	 * Check if file url is valid.
	 *
	 * @param  string  $url URL of file.
	 */
	function is_file_url_valid( $url ) {
		
		// Time out in seconds
		$timeout = apply_filters("sell_media_media_download_timeout", 5);
		
		// Get media information
		$media_info = wp_remote_get($url, $timeout);
		
		// check file is valid or not
		if (is_wp_error($media_info) || empty($media_info['response']) || $media_info['response']['code'] != '200') {
			return false;
		} else {
			return true;
		}
		return false;
	}

	/**
	 * Verifies a download purchase by checking if the post status is set to 'publish' for a
	 * given purchase key;
	 *
	 * @param $download (string) The download hash
	 * @return boolean
	 */
	public function verify( $transaction_id=null, $payment_id=null, $product_id = null, $attachment_id = null, $size_id = null) {
		if ( $transaction_id == Sell_Media()->payments->get_meta_key( $payment_id, 'transaction_id' ) || 'manual' == $transaction_id ) {
			if ( 'manual' === $transaction_id ) {
				if ( ! is_user_logged_in() || ! current_user_can('manage_options') )
					return false;
			}
			
			$products = Sell_Media()->payments->get_products( $payment_id );

			foreach ( $products as $key => $product ) {

				if ( $product['attachment'] == $attachment_id && 'download' === $product['type'] && $size_id == $product['size']['id'] ) {
					return true;
				}				
			}

			return false;
		} else {
			return false;
		}
	}


	/**
	 * Resize and download an image to the specified dimensions
	 * http://codex.wordpress.org/Class_Reference/WP_Image_Editor
	 *
	 * Returns the new image file path
	 *
	 * @since 1.8.5
	 * @param $product_id
	 * @param $attachment_id
	 * @param $size_id
	 * @return resized image file path
	 */
	public function download_image( $product_id=null, $attachment_id=null, $size_id=null ) {
		$file_path = Sell_Media()->products->get_protected_file( $product_id, $attachment_id );
		$img = wp_get_image_editor( $file_path );
		if ( ! is_wp_error( $img ) ) {
			$width = get_term_meta( $size_id, 'width', true );
			$height = get_term_meta( $size_id, 'height', true );
			if ( $width || $height ) {
				if ( $width >= $height ) {
					$max = $width;
				} else {
					$max = $height;
				}
				$img->resize( $max, $max, false );
				$img->set_quality( 100 );
			}
			$img->stream();
		}
	}


	/**
	 * Download helper for large files without changing PHP.INI
	 */
	public function download_file( $file ) {

		$stream_out = @fopen( 'php://output', 'w' );
		$stream_in = @fopen( $file, 'r' );
		stream_copy_to_stream($stream_in, $stream_out);
		fclose($stream_in);
		fclose($stream_out);

		exit;
	}

}
new SellMediaDownload;