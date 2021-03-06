<?php
/**
 * Twenty Nineteen support.
 *
 * @since   1.0.0
 * @package SellMedia
 */

defined( 'ABSPATH' ) || exit;

/**
 * SM_Twenty_Nineteen
 */
class SM_Twenty_Nineteen {

	/**
	 * Theme init.
	 */
	public static function init() {

		add_action( 'sell_media_above_archive_content', array( __CLASS__, 'sm_output_content_wrapper' ), 10 );
		add_action( 'sell_media_below_archive_content', array( __CLASS__, 'sm_output_content_wrapper_end' ), 10 );
	}

	/**
	 * Open the Twenty Nineteen wrapper.
	 */
	public static function sm_output_content_wrapper() {
		echo '<section id="primary" class="content-area">';
		echo '<main id="main" class="site-main">';
	}

	/**
	 * Close the Twenty Nineteen wrapper.
	 */
	public static function sm_output_content_wrapper_end() {
		echo '</main>';
		echo '</section>';
	}

}

SM_Twenty_Nineteen::init();
