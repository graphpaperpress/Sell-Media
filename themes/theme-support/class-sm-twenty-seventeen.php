<?php
/**
 * Twenty Seventeen
 *
 * @since   1.0.0
 * @package SellMedia
 */

defined( 'ABSPATH' ) || exit;

/**
 * SM_Twenty_Seventeen
 */
class SM_Twenty_Seventeen {

	/**
	 * Theme init.
	 */
	public static function init() {

		add_action( 'sell_media_above_archive_content', array( __CLASS__, 'sm_output_content_wrapper' ), 10 );
		add_action( 'sell_media_below_archive_content', array( __CLASS__, 'sm_output_content_wrapper_end' ), 10 );
	}

	/**
	 * Open the Twenty Seventeen wrapper.
	 */
	public static function sm_output_content_wrapper() {
		echo '<div class="wrap">';
		echo '<div id="primary" class="content-area twentyseventeen">';
		echo '<main id="main" class="site-main" role="main">';
	}

	/**
	 * Close the Twenty Seventeen wrapper.
	 */
	public static function sm_output_content_wrapper_end() {
		echo '</main>';
		echo '</div>';
		get_sidebar();
		echo '</div>';
	}
}

SM_Twenty_Seventeen::init();