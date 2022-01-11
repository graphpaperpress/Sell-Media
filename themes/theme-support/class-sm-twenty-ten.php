<?php
/**
 * Twenty Ten
 *
 * @since   1.0.0
 * @package SellMedia
 */

defined( 'ABSPATH' ) || exit;

/**
 * SM_Twenty_Ten
 */
class SM_Twenty_Ten {

	/**
	 * Theme init.
	 */
	public static function init() {

		add_action( 'sell_media_above_archive_content', array( __CLASS__, 'sm_output_content_wrapper' ), 10 );
		add_action( 'sell_media_below_archive_content', array( __CLASS__, 'sm_output_content_wrapper_end' ), 10 );
	}

	/**
	 * Open wrappers.
	 */
	public static function sm_output_content_wrapper() {
		_e('<div id="container"><div id="content" role="main">','sell_media');
	}

	/**
	 * Close wrappers.
	 */
	public static function sm_output_content_wrapper_end() {
		_e('</div></div>','sell_media');
	}
}

SM_Twenty_Ten::init();
