<?php
/**
 * Theme support.
 *
 * @since   1.0.0
 * @package SellMedia
 */

defined( 'ABSPATH' ) || exit;

/**
 * SM_Theme_Support.
 */
class SM_Theme_Support {

	public function __construct() {
	}
	/**
	 * Theme init.
	 */
	public static function init() {
		SM_Theme_Support::theme_support_includes();
	}

	/**
	 * Include classes for theme support.
	 */
	public static function theme_support_includes() {
		if ( SM_Theme_Support::sm_is_active_theme( array( 'twentytwenty', 'twentynineteen', 'twentyseventeen', 'twentysixteen', 'twentyfifteen', 'twentyfourteen', 'twentythirteen', 'twentyeleven', 'twentytwelve', 'twentyten' ) ) ) {
			switch ( get_template() ) {
				case 'twentyten':
					include_once SELL_MEDIA_PLUGIN_DIR . '/themes/theme-support/class-sm-twenty-ten.php';
					break;
				case 'twentyeleven':
					include_once SELL_MEDIA_PLUGIN_DIR . '/themes/theme-support/class-sm-twenty-eleven.php';
					break;
				case 'twentytwelve':
					include_once SELL_MEDIA_PLUGIN_DIR . '/themes/theme-support/class-sm-twenty-twelve.php';
					break;
				case 'twentythirteen':
					include_once SELL_MEDIA_PLUGIN_DIR . '/themes/theme-support/class-sm-twenty-thirteen.php';
					break;
				case 'twentyfourteen':
					include_once SELL_MEDIA_PLUGIN_DIR . '/themes/theme-support/class-sm-twenty-fourteen.php';
					break;
				case 'twentyfifteen':
					include_once SELL_MEDIA_PLUGIN_DIR . '/themes/theme-support/class-sm-twenty-fifteen.php';
					break;
				case 'twentysixteen':
					include_once SELL_MEDIA_PLUGIN_DIR . '/themes/theme-support/class-sm-twenty-sixteen.php';
					break;
				case 'twentyseventeen':
					include_once SELL_MEDIA_PLUGIN_DIR . '/themes/theme-support/class-sm-twenty-seventeen.php';
					break;
				case 'twentynineteen':
					include_once SELL_MEDIA_PLUGIN_DIR . '/themes/theme-support/class-sm-twenty-nineteen.php';
					break;
				case 'twentytwenty':
					include_once SELL_MEDIA_PLUGIN_DIR . '/themes/theme-support/class-sm-twenty-twenty.php';
					break;
			}
		}
	}

	public static function sm_is_active_theme( $theme ) {
   		return is_array( $theme ) ? in_array( get_template(), $theme, true ) : get_template() === $theme;
    }
}

$sm_theme_support = SM_Theme_Support::init();