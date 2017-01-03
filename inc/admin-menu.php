<?php

/**
 * Admin Menu
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
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
	add_submenu_page( 'edit.php?post_type=sell_media_item', __('Reports', 'sell_media'), __('Reports', 'sell_media'),  $permission, 'sell_media_reports', 'sell_media_reports_callback_fn' );
	add_submenu_page( 'edit.php?post_type=sell_media_item', __('Upgrades', 'sell_media'), __('Upgrades', 'sell_media'),  $permission, 'sell_media_upgrades', 'sell_media_upgrades_callback_fn' );
	remove_submenu_page( 'edit.php?post_type=sell_media_item', 'edit-tags.php?taxonomy=price-group&amp;post_type=sell_media_item' );
	add_submenu_page( null, __('System Info', 'sell_media'), __('System Info', 'sell_media'),  $permission, 'sell_media_system_info', 'sell_media_system_info_callback_fn' );
	remove_submenu_page( 'edit.php?post_type=sell_media_item', 'edit.php?post_type=sell_media_item&amp;page=sell_media_system_info' );
	remove_submenu_page( 'edit.php?post_type=sell_media_item', 'edit-tags.php?taxonomy=keywords&amp;post_type=sell_media_item' );
	remove_submenu_page( 'edit.php?post_type=sell_media_item', 'edit-tags.php?taxonomy=creator&amp;post_type=sell_media_item' );
	remove_submenu_page( 'upload.php', 'edit-tags.php?taxonomy=creator&amp;post_type=attachment' );

	do_action( 'sell_media_menu_hook' );
}
add_action( 'admin_menu', 'sell_media_admin_menu', 999 );

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
		'5'     => 'Pricelists',
		'6'     => 'Payments',
		'7'     => 'Reports',
		'97'    => 'Settings',
		'99'    => 'Upgrades'
	);

	$j = 1;
	if ( ! empty ( $submenu['edit.php?post_type=sell_media_item'] ) ) {
		foreach ( $submenu['edit.php?post_type=sell_media_item'] as $key => $value ) {
			if ( array_search( $value[0], $required_order ) ) {
				$i = array_search( $value[0], $required_order );
			} else {
				$i = array_search ( 'Settings', $required_order ) - $j;
				$j++;
			}
			$arr[$i] = $submenu['edit.php?post_type=sell_media_item'][$key];

		}
		ksort( $arr );
		$submenu['edit.php?post_type=sell_media_item'] = $arr;
	}
	return $menu_ord;
}
add_filter( 'custom_menu_order', 'sell_media_submenu_order' );

/**
 * Upgrades admin menu page
 * @return url
 */
function sell_media_admin_init_upgrades(){

	if ( isset( $_GET['page'] ) && $_GET['page'] === 'sell_media_upgrades' ) {

		$plugin_data = get_plugin_data( plugin_dir_path( __DIR__ ) . 'sell-media.php', false );
		wp_redirect( $plugin_data['AuthorURI'] . '/downloads/category/extensions/#utm_source=wp-admin&utm_medium=banner&utm_campaign=sell-media-menu-link', 301 );
		exit();

	}
}
add_action( 'admin_init', 'sell_media_admin_init_upgrades' );
