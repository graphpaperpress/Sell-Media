<?php
/**
 * Twenty Twelve
 *
 * @class   SM_Twenty_Twelve
 * @since   1.0.0
 * @package SellMedia
 */

defined( 'ABSPATH' ) || exit;

/**
 * SM_Twenty_Twelve
 */
class SM_Twenty_Twelve {

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
		echo '<div id="primary" class="site-content"><div id="content" role="main" class="twentytwelve">';
	}

	/**
	 * Close wrappers.
	 */
	public static function sm_output_content_wrapper_end() {
		echo '</div></div>';
	}
}

SM_Twenty_Twelve::init();
