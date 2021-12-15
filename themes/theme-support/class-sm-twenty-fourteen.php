<?php
/**
 * Twenty Fourteen
 *
 * @class   SM_Twenty_Fourteen
 * @since   1.0.0
 * @package SellMedia
 */

defined( 'ABSPATH' ) || exit;

/**
 * SM_Twenty_Fourteen
 */
class SM_Twenty_Fourteen {

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
		_e('<div id="primary" class="content-area"><div id="content" role="main" class="site-content twentyfourteen">','sell_media');
	}

	/**
	 * Close wrappers.
	 */
	public static function sm_output_content_wrapper_end() {
		_e('</div></div>','sell_media');
		get_sidebar( 'content' );
	}
}

SM_Twenty_Fourteen::init();
