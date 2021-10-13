<?php
/**
 * Twenty Sixteen
 *
 * @since   1.0.0
 * @package SellMedia
 */

defined( 'ABSPATH' ) || exit;

/**
 * SM_Twenty_Sixteen
 */
class SM_Twenty_Sixteen {

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
		echo '<div id="primary" class="content-area twentysixteen"><main id="main" class="site-main" role="main">';
	}

	/**
	 * Close wrappers.
	 */
	public static function sm_output_content_wrapper_end() {
		echo '</main></div>';
	}
}

SM_Twenty_Sixteen::init();
