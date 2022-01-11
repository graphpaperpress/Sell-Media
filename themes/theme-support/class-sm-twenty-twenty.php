<?php
/**
 * Twenty Nineteen support.
 *
 * @since   1.0.0
 * @package SellMedia
 */

defined( 'ABSPATH' ) || exit;

/**
 * SM_Twenty_Twenty.
 */
class SM_Twenty_Twenty {

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
		_e('<section id="primary" class="content-area">','sell_media');
		_e('<div class="entry-content">','sell_media');
	}

	/**
	 * Close the Twenty Nineteen wrapper.
	 */
	public static function sm_output_content_wrapper_end() {
		_e('</div>','sell_media');
		_e('</section>','sell_media');
	}
}

SM_Twenty_Twenty::init();
