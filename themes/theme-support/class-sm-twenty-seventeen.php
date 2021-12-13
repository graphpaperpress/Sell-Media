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
		_e('<div class="wrap">','sell_media');
		_e('<div id="primary" class="content-area twentyseventeen">','sell_media');
		_e('<main id="main" class="site-main" role="main">','sell_media');
	}

	/**
	 * Close the Twenty Seventeen wrapper.
	 */
	public static function sm_output_content_wrapper_end() {
		_e('</main>','sell_media');
		_e('</div>','sell_media');
		get_sidebar();
		_e('</div>','sell_media');
	}
}

SM_Twenty_Seventeen::init();