<?php

/**
 * Admin menu
 *
 * @package     Sell Media
 * @subpackage  Functions/Install
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8.5
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Admin menu
 *
 * Add our menus to WP Admin to access Sell Media
 *
 * @since 1.8.5
 * @return void
 */
function sell_media_admin_menu() {

    $permission = 'manage_options';

    add_submenu_page( 'edit.php?post_type=sell_media_item', __('Add Bulk', 'sell_media'), __('Add Bulk', 'sell_media'),  'upload_files', 'sell_media_add_bulk', 'sell_media_add_bulk_callback_fn' );
    add_submenu_page( 'edit.php?post_type=sell_media_item', __('Add Package', 'sell_media'), __('Add Package', 'sell_media'),  'upload_files', 'sell_media_add_package', 'sell_media_add_package_callback_fn' );
    add_submenu_page( 'edit.php?post_type=sell_media_item', __('Payments', 'sell_media'), __('Payments', 'sell_media'),  $permission, 'sell_media_payments', 'sell_media_payments_callback_fn' );
    add_submenu_page( 'edit.php?post_type=sell_media_item', __('Reports', 'sell_media'), __('Reports', 'sell_media'),  $permission, 'sell_media_reports', 'sell_media_reports_callback_fn' );
    add_submenu_page( 'edit.php?post_type=sell_media_item', __('Extensions', 'sell_media'), __('Extensions', 'sell_media'),  $permission, 'sell_media_extensions', 'sell_media_extensions_callback_fn' );
    remove_submenu_page( 'edit.php?post_type=sell_media_item', 'edit-tags.php?taxonomy=price-group&amp;post_type=sell_media_item' );

    do_action( 'sell_media_menu_hook' );
}
add_action( 'admin_menu', 'sell_media_admin_menu' );

/**
 * Admin menu order
 *
 * Sort our menus to WP Admin to access Sell Media
 *
 * @since 1.8.5
 * @return void
 */
function sell_media_submenu_order( $menu_ord ) {
    global $submenu;

    // Enable the next line to see all menu orders
    //echo '<pre>' . print_r( $submenu, true ) . '</pre>';

    $arr = array();
    $arr[] = $submenu['edit.php?post_type=sell_media_item'][5];
    $arr[] = $submenu['edit.php?post_type=sell_media_item'][10];
    $arr[] = $submenu['edit.php?post_type=sell_media_item'][21];
    $arr[] = $submenu['edit.php?post_type=sell_media_item'][22];
    $arr[] = $submenu['edit.php?post_type=sell_media_item'][16];
    $arr[] = $submenu['edit.php?post_type=sell_media_item'][17];
    $arr[] = $submenu['edit.php?post_type=sell_media_item'][18];
    $arr[] = $submenu['edit.php?post_type=sell_media_item'][19];
    $arr[] = $submenu['edit.php?post_type=sell_media_item'][23];
    $arr[] = $submenu['edit.php?post_type=sell_media_item'][24];
    $arr[] = $submenu['edit.php?post_type=sell_media_item'][25];
    $arr[] = $submenu['edit.php?post_type=sell_media_item'][26];
    $submenu['edit.php?post_type=sell_media_item'] = $arr;

    return $menu_ord;
}
add_filter( 'custom_menu_order', 'sell_media_submenu_order' );