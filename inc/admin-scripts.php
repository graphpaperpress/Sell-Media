<?php

/**
 * Admin scripts
 *
 * @package     Sell Media
 * @subpackage  Functions/Install
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8.5
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Admin Scripts
 *
 * Enqueues all necessary scripts in the WP Admin to run Sell Media
 *
 * @since 1.8.5
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function sell_media_admin_scripts( $hook ) {

    if ( sell_media_is_sell_media_post_type_page() || 'post.php' == $hook || 'post-new.php' == $hook ) {
        wp_enqueue_style( 'sell_media-admin', SELL_MEDIA_PLUGIN_URL . 'css/sell_media-admin.css', array( 'thickbox' ), SELL_MEDIA_VERSION );
        wp_enqueue_script( 'sell_media-admin-items', SELL_MEDIA_PLUGIN_URL . 'js/admin-items.js', array( 'jquery' ), SELL_MEDIA_VERSION );

        if ( sell_media_is_license_page() || sell_media_is_license_term_page() ) {
            wp_enqueue_script( 'sell_media-admin', SELL_MEDIA_PLUGIN_URL . 'js/sell_media-admin.js', array( 'jquery', 'jquery-ui-sortable' ), SELL_MEDIA_VERSION );
            wp_enqueue_script( 'jquery-ui-slider' );
        }
    }

    if ( sell_media_is_reports_page() ) {
        wp_enqueue_script( 'google_charts', 'https://www.google.com/jsapi', array( 'jquery' ), SELL_MEDIA_VERSION );
    }
    
    do_action( 'sell_media_admin_scripts_hook' );
}
add_action( 'admin_enqueue_scripts', 'sell_media_admin_scripts' );