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
    add_submenu_page( null, __('Add Bulk', 'sell_media'), __('Add Bulk', 'sell_media'),  'upload_files', 'sell_media_add_bulk', 'sell_media_add_bulk_callback_fn' );
    add_submenu_page( null, __('Add Package', 'sell_media'), __('Add Package', 'sell_media'),  'upload_files', 'sell_media_add_package', 'sell_media_add_package_callback_fn' );
    add_submenu_page( 'edit.php?post_type=sell_media_item', __('Reports', 'sell_media'), __('Reports', 'sell_media'),  $permission, 'sell_media_reports', 'sell_media_reports_callback_fn' );
    add_submenu_page( 'edit.php?post_type=sell_media_item', __('Extensions', 'sell_media'), __('Extensions', 'sell_media'),  $permission, 'sell_media_extensions', 'sell_media_extensions_callback_fn' );
    remove_submenu_page( 'edit.php?post_type=sell_media_item', 'edit-tags.php?taxonomy=price-group&amp;post_type=sell_media_item' );
    remove_submenu_page( 'edit.php?post_type=sell_media_item', 'edit-tags.php?taxonomy=keywords&amp;post_type=sell_media_item' );
    remove_submenu_page( 'edit.php?post_type=sell_media_item', 'edit-tags.php?taxonomy=creator&amp;post_type=sell_media_item' );

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
    // echo '<pre>' . print_r( $submenu, true ) . '</pre>';

    $arr = array();
    $required_order = array(
        '1'     => 'All Products',
        '2'     => 'Add New',
        '3'     => 'Collections',
        '4'     => 'Licenses',
        '5'     => 'Payments',
        '6'     => 'Reports',
        '7'     => 'Extensions',
        '99'    => 'Settings'
    );

    $j = 1;
    if( ! empty ( $submenu['edit.php?post_type=sell_media_item'] ) ) {
        foreach ( $submenu['edit.php?post_type=sell_media_item'] as $key => $value ) {
            if ( array_search( $value[0], $required_order ) ) {
                $i = array_search( $value[0], $required_order );
            } else {
                $i = array_search ( 'Settings', $required_order ) - $j;
                $j++;
            }
            $arr[$i] = $submenu['edit.php?post_type=sell_media_item'][$key];

        }
        ksort($arr);
        $submenu['edit.php?post_type=sell_media_item'] = $arr;
    }
    return $menu_ord;
}
add_filter( 'custom_menu_order', 'sell_media_submenu_order' );