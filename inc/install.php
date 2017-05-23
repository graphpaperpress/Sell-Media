<?php

/**
 * Install Functions
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Install
 *
 * Runs on plugin install by setting up the post types, custom taxonomies,
 * flushing rewrite rules to initiate the new 'sell_media_item' slug and also
 * creates the plugin and populates the settings fields for those plugin
 * pages.
 *
 * @since 1.8.5
 * @global $wpdb
 * @global $wp_version
 * @return void
 */
function sell_media_install() {

	$version = get_option( 'sell_media_version' );
	$settings = get_option( 'sell_media_options' );

	// Bail if saved version is higher than plugin version
	if ( $version && $version > SELL_MEDIA_VERSION ) {
		return;
	}

	// Check compatible version.
	if ( ! sell_media_compatible_version() ) {
		deactivate_plugins( plugin_basename( SELL_MEDIA_PLUGIN_FILE ) );
		wp_die( esc_html__( 'Sell Media requires WordPress 4.4 or higher!', 'sell_media' ) );
	}

	// Add default settings if sell media options is not set.
	if ( false === $settings || empty( $settings ) ) {
		sell_media_register_default_settings();
	}

	// Register Custom Post Types
	sell_media_register_post_types();

	// Register Taxonomies
	sell_media_register_taxonomies();

	// Autocreate Pages
	sell_media_autocreate_pages();

	// Flush the permalinks
	flush_rewrite_rules();

	// Install protected folder for uploading files and prevent hotlinking
	$downloads_url = sell_media_get_upload_dir();

	if ( wp_mkdir_p( $downloads_url ) && ! file_exists( $downloads_url . '/.htaccess' ) ) {
		if ( $file_handle = @fopen( $downloads_url . '/.htaccess', 'w' ) ) {
			fwrite( $file_handle, 'deny from all' );
			fclose( $file_handle );
		}
	}

	// Add a new Customer role
	add_role( 'sell_media_customer', 'Customer', array( 'read' => true ) );

	// This is a new install so add the defaults to the options table
	if ( empty( $version ) ) {
		$defaults = sell_media_get_plugin_option_defaults();
		update_option( 'sell_media_options', $defaults );
		// A version number exists, so run upgrades.
	} else {
		do_action( 'sell_media_run_upgrades', $version );
	}

	if ( $version < SELL_MEDIA_VERSION ) {
		// Restrict ipn log files.
		$htaccess_file = ABSPATH . ".htaccess";
		$file_content = "\n\n# BEGIN Sell Media\n";
		$file_content .= '<FilesMatch "ipn_errors\.log|ipn_success\.log">' . "\n";
		$file_content .= "\t Require all denied\n";
		$file_content .= "</FilesMatch>\n";
		$file_content .= "# END Sell Media\n";
		file_put_contents( $htaccess_file, $file_content, FILE_APPEND | LOCK_EX );
	}

	// Update the version number
	update_option( 'sell_media_version', SELL_MEDIA_VERSION );

}
register_activation_hook( SELL_MEDIA_PLUGIN_FILE, 'sell_media_install' );

/**
 * Check WordPress version and disable if incompatible
 * @return void
 */
function sell_media_check_version() {
	if ( ! sell_media_compatible_version() ) {
		if ( is_plugin_active( plugin_basename( SELL_MEDIA_PLUGIN_FILE ) ) ) {
			deactivate_plugins( plugin_basename( SELL_MEDIA_PLUGIN_FILE ) );
			add_action( 'admin_notices', 'sell_media_disabled_notice' );
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
		}
	}
}
add_action( 'admin_init', 'sell_media_check_version' );

/**
 * Plugin disable notice.
 * @return string Disable notice.
 */
function sell_media_disabled_notice() {
	echo '<div class="update-nag">' . esc_html__( 'Sell Media requires WordPress 4.4 or higher!', 'sell_media' ) . '</div>';
}

/**
 * Check WordPress version.
 * @return boolean
 */
function sell_media_compatible_version() {
	if ( version_compare( $GLOBALS['wp_version'], '4.4', '<' ) ) {
		 return false;
	}

	// Add sanity checks for other version requirements here.
	return true;
}

/**
 * Register Custom Post Types
 * @since 1.8.5
 */
function sell_media_register_post_types() {

	$settings = sell_media_get_plugin_options();

	/**
	 * Register Item Custom Post Type
	 */
	$item_labels = array(
		'name' => __( 'Sell Media', 'sell_media' ),
		'singular_name' => __( 'Sell Media', 'sell_media' ),
		'all_items' => __( 'All Products', 'sell_media' ),
		'add_new' => __( 'Add New', 'sell_media' ),
		'add_new_item' => __( 'Sell Media', 'sell_media' ),
		'edit_item' => __( 'Edit Product', 'sell_media' ),
		'new_item' => __( 'New Product', 'sell_media' ),
		'view_item' => __( 'View Product', 'sell_media' ),
		'search_items' => __( 'Search Products', 'sell_media' ),
		'not_found' => __( 'No products found', 'sell_media' ),
		'not_found_in_trash' => __( 'No products found in Trash', 'sell_media' ),
		'parent_item_colon' => __( 'Parent Product:', 'sell_media' ),
		'menu_name' => __( 'Sell Media', 'sell_media' ),
	);

	$item_args = array(
		'labels' => $item_labels,
		'hierarchical' => false,
		'supports' => array( 'title', 'thumbnail', 'author', 'custom-fields' ),
		'taxonomies' => array( 'licenses', 'keywords', 'city', 'state', 'creator', 'collection' ),
		'public' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'menu_position' => apply_filters( 'sell_media_filter_menu_position', 10 ),
		'menu_icon' => 'dashicons-cart',
		'show_in_nav_menus' => true,
		'publicly_queryable' => apply_filters( 'sell_media_filter_publicly_queryable', true ),
		'exclude_from_search' => apply_filters( 'sell_media_filter_exclude_from_search', false ),
		'has_archive' => apply_filters( 'sell_media_filter_has_archive', true ),
		'query_var' => true,
		'can_export' => true,
		'rewrite' => array(
			'slug' => empty( $settings->post_type_slug ) ? _x( 'items', 'URL slug', 'sell_media' ) : $settings->post_type_slug,
			'feeds' => true,
		),
		'capability_type' => 'post',
	);

	register_post_type( 'sell_media_item', apply_filters( 'sell_media_filter_post_type_registration_args', $item_args ) );

	/**
	 * Register Payment Custom Post Type
	 */
	$payment_labels = array(
		'name' => __( 'Payments', 'sell_media' ),
		'singular_name' => __( 'Payment', 'sell_media' ),
		'add_new' => __( 'Add New', 'sell_media' ),
		'add_new_item' => __( 'Add New Payment', 'sell_media' ),
		'edit_item' => __( 'Edit Payment', 'sell_media' ),
		'new_item' => __( 'New Payment', 'sell_media' ),
		'view_item' => __( 'View Payment', 'sell_media' ),
		'search_items' => __( 'Search Payments', 'sell_media' ),
		'not_found' => __( 'No payments found', 'sell_media' ),
		'not_found_in_trash' => __( 'No payments found in Trash', 'sell_media' ),
		'parent_item_colon' => __( 'Parent Payment:', 'sell_media' ),
		'menu_name' => __( 'Payments', 'sell_media' ),
	);

	$payment_args = array(
		'labels' => $payment_labels,
		'hierarchical' => false,
		'supports' => array( 'title' ),
		'public' => false,
		'show_ui' => true,
		'show_in_menu' => 'edit.php?post_type=sell_media_item',
		'publicly_queryable' => false,
		'has_archive' => false,
		'query_var' => true,
		'rewrite' => false,
		'capability_type' => 'post',
		'capabilities' => array(
			'create_posts' => false, // Removes support for the "Add New" function
			'edit_post' => 'update_core',
			'read_post' => 'update_core',
			'delete_post' => 'update_core',
			'edit_posts' => 'update_core',
			'edit_others_posts' => 'update_core',
			'publish_posts' => 'update_core',
			'read_private_posts' => 'update_core',
		),
		'map_meta_cap' => true, // Allow users to edit/remove existing payments
	);

	register_post_type( 'sell_media_payment', apply_filters( 'sell_media_payment_filter_post_type_registration_args', $payment_args ) );

}
add_action( 'init', 'sell_media_register_post_types', 1 );


/**
 * Create new image sizes
 *
 * Setting the last parameter to "true" hard-crops the images to an exact dimension
 * (SQUARE: 600px wide, by 600px tall)
 */
function sell_media_after_setup_theme() {
	add_image_size( 'sell_media_square', 600, 600, true );
}
add_action( 'after_setup_theme', 'sell_media_after_setup_theme' );

/**
 * Register Custom Taxonomies
 * @since 1.8.5
 */
function sell_media_register_taxonomies() {

	$settings = sell_media_get_plugin_options();
	$admin_columns = empty( $settings->admin_columns ) ? null : $settings->admin_columns;

	/**
	 * Register Price Group
	 */
	$price_group_labels = array(
		'name' => __( 'Price Groups', 'sell_media' ),
		'singular_name' => __( 'Price Groups', 'sell_media' ),
		'search_items' => __( 'Search Price Groups', 'sell_media' ),
		'popular_items' => __( 'Popular Price Groups', 'sell_media' ),
		'all_items' => __( 'All Price Groups', 'sell_media' ),
		'parent_item' => __( 'Parent Price Groups', 'sell_media' ),
		'parent_item_colon' => __( 'Parent Price Groups:', 'sell_media' ),
		'edit_item' => __( 'Edit Price Group', 'sell_media' ),
		'update_item' => __( 'Update Price Group', 'sell_media' ),
		'add_new_item' => __( 'Add New Price Group', 'sell_media' ),
		'new_item_name' => __( 'New Price Group', 'sell_media' ),
		'separate_items_with_commas' => __( 'Separate Price Groups with commas', 'sell_media' ),
		'add_or_remove_items' => __( 'Add or remove Price Groups', 'sell_media' ),
		'choose_from_most_used' => __( 'Choose from most used Price Groups', 'sell_media' ),
		'menu_name' => __( 'Price Groups', 'sell_media' ),
	);

	$price_group_args = array(
		'labels' => $price_group_labels,
		'public' => true,
		'show_in_nav_menus' => true,
		'show_tagcloud' => true,
		'show_admin_column' => true,
		'show_ui' => false,
		'hierarchical' => true,
		'rewrite' => true,
		'query_var' => true,
	);

	register_taxonomy( 'price-group', array( 'sell_media_item' ), apply_filters( 'sell_media_price_group_tax_registration_args', $price_group_args ) );

	/**
	 * Register Collection
	 */
	$collection_labels = array(
		'name' => __( 'Collections', 'sell_media' ),
		'singular_name' => __( 'Collection', 'sell_media' ),
		'search_items' => __( 'Search Collection', 'sell_media' ),
		'popular_items' => __( 'Popular Collection', 'sell_media' ),
		'all_items' => __( 'All Collections', 'sell_media' ),
		'parent_item' => __( 'Parent Collection', 'sell_media' ),
		'parent_item_colon' => __( 'Parent Collection:', 'sell_media' ),
		'edit_item' => __( 'Edit Collection', 'sell_media' ),
		'update_item' => __( 'Update Collection', 'sell_media' ),
		'add_new_item' => __( 'Add New Collection', 'sell_media' ),
		'new_item_name' => __( 'New Collection', 'sell_media' ),
		'separate_items_with_commas' => __( 'Separate collection with commas', 'sell_media' ),
		'add_or_remove_items' => __( 'Add or remove Collection', 'sell_media' ),
		'choose_from_most_used' => __( 'Choose from most used Collection', 'sell_media' ),
		'menu_name' => __( 'Collections', 'sell_media' ),
	);

	$collection_args = array(
		'labels' => $collection_labels,
		'public' => true,
		'show_in_nav_menus' => true,
		'show_admin_column' => ( ! empty( $admin_columns ) && in_array( 'show_collection', $admin_columns ) ) ? true : false,
		'show_ui' => true,
		'show_tagcloud' => true,
		'hierarchical' => true,
		'rewrite' => array( 'hierarchical' => true ),
		'query_var' => true,
	);

	register_taxonomy( 'collection', array( 'sell_media_item' ), apply_filters( 'sell_media_collection_tax_registration_args', $collection_args ) );

	/**
	 * Register Licenses
	 */
	$licenses_labels = array(
		'name' => __( 'Licenses', 'sell_media' ),
		'singular_name' => __( 'License', 'sell_media' ),
		'search_items' => __( 'Search Licenses', 'sell_media' ),
		'popular_items' => __( 'Popular Licenses', 'sell_media' ),
		'all_items' => __( 'All Licenses', 'sell_media' ),
		'parent_item' => __( 'Parent License', 'sell_media' ),
		'parent_item_colon' => __( 'Parent License:', 'sell_media' ),
		'edit_item' => __( 'Edit License', 'sell_media' ),
		'update_item' => __( 'Update License', 'sell_media' ),
		'add_new_item' => __( 'Add New License', 'sell_media' ),
		'new_item_name' => __( 'New License', 'sell_media' ),
		'separate_items_with_commas' => __( 'Separate licenses with commas', 'sell_media' ),
		'add_or_remove_items' => __( 'Add or remove Licenses', 'sell_media' ),
		'choose_from_most_used' => __( 'Choose from most used Licenses', 'sell_media' ),
		'menu_name' => __( 'Licenses', 'sell_media' ),
	);

	$licenses_args = array(
		'labels' => $licenses_labels,
		'public' => true,
		'show_in_nav_menus' => true,
		'show_admin_column' => ( ! empty( $admin_columns ) && in_array( 'show_license', $admin_columns ) ) ? true : false,
		'show_ui' => true,
		'show_tagcloud' => true,
		'hierarchical' => true,
		'rewrite' => true,
		'query_var' => true,
	);

	register_taxonomy( 'licenses', array( 'sell_media_item' ), apply_filters( 'sell_media_licenses_tax_registration_args', $licenses_args ) );

	/**
	 * Register Keywords
	 */
	$keywords_labels = array(
		'name' => __( 'Keywords', 'sell_media' ),
		'singular_name' => __( 'Keyword', 'sell_media' ),
		'search_items' => __( 'Search Keywords', 'sell_media' ),
		'popular_items' => __( 'Popular Keywords', 'sell_media' ),
		'all_items' => __( 'All Keywords', 'sell_media' ),
		'parent_item' => __( 'Parent Keyword', 'sell_media' ),
		'parent_item_colon' => __( 'Parent Keyword:', 'sell_media' ),
		'edit_item' => __( 'Edit Keyword', 'sell_media' ),
		'update_item' => __( 'Update Keyword', 'sell_media' ),
		'add_new_item' => __( 'Add New Keyword', 'sell_media' ),
		'new_item_name' => __( 'New Keyword', 'sell_media' ),
		'separate_items_with_commas' => __( 'Separate keywords with commas', 'sell_media' ),
		'add_or_remove_items' => __( 'Add or remove Keywords', 'sell_media' ),
		'choose_from_most_used' => __( 'Choose from most used Keywords', 'sell_media' ),
		'menu_name' => __( 'Keywords', 'sell_media' ),
	);

	$keywords_args = array(
		'labels' => $keywords_labels,
		'public' => true,
		'show_in_nav_menus' => true,
		'show_admin_column' => true,
		'show_ui' => true,
		'show_tagcloud' => true,
		'hierarchical' => false,
		'rewrite' => true,
		'query_var' => true,
	);

	register_taxonomy( 'keywords', array( 'attachment' ), apply_filters( 'sell_media_keyword_tax_registration_args', $keywords_args ) );

	/**
	 * Register Creator
	 */
	$creator_labels = array(
		'name' => __( 'Creators', 'sell_media' ),
		'singular_name' => __( 'Creator', 'sell_media' ),
		'search_items' => __( 'Search Creator', 'sell_media' ),
		'popular_items' => __( 'Popular Creator', 'sell_media' ),
		'all_items' => __( 'All Creator', 'sell_media' ),
		'parent_item' => __( 'Parent Creator', 'sell_media' ),
		'parent_item_colon' => __( 'Parent Creator:', 'sell_media' ),
		'edit_item' => __( 'Edit Creator', 'sell_media' ),
		'update_item' => __( 'Update Creator', 'sell_media' ),
		'add_new_item' => __( 'Add New Creator', 'sell_media' ),
		'new_item_name' => __( 'New Creator', 'sell_media' ),
		'separate_items_with_commas' => __( 'Separate creator with commas', 'sell_media' ),
		'add_or_remove_items' => __( 'Add or remove Creator', 'sell_media' ),
		'choose_from_most_used' => __( 'Choose from most used Creator', 'sell_media' ),
		'menu_name' => __( 'Creators', 'sell_media' ),
	);

	$creator_args = array(
		'labels' => $creator_labels,
		'public' => true,
		'show_in_nav_menus' => true,
		'show_admin_column' => ( ! empty( $admin_columns ) && in_array( 'show_creators', $admin_columns ) ) ? true : false,
		'show_tagcloud' => true,
		'hierarchical' => true,
		'rewrite' => true,
		'query_var' => true,
	);

	register_taxonomy( 'creator', array( 'sell_media_item', 'attachment' ), apply_filters( 'sell_media_creator_tax_registration_args', $creator_args ) );

	/**
	 * Register City
	*/
	$city_labels = array(
		'name' => __( 'City', 'sell_media' ),
		'singular_name' => __( 'Keyword', 'sell_media' ),
		'search_items' => __( 'Search City', 'sell_media' ),
		'popular_items' => __( 'Popular City', 'sell_media' ),
		'all_items' => __( 'All City', 'sell_media' ),
		'parent_item' => __( 'Parent Keyword', 'sell_media' ),
		'parent_item_colon' => __( 'Parent Keyword:', 'sell_media' ),
		'edit_item' => __( 'Edit Keyword', 'sell_media' ),
		'update_item' => __( 'Update Keyword', 'sell_media' ),
		'add_new_item' => __( 'Add New Keyword', 'sell_media' ),
		'new_item_name' => __( 'New Keyword', 'sell_media' ),
		'separate_items_with_commas' => __( 'Separate city with commas', 'sell_media' ),
		'add_or_remove_items' => __( 'Add or remove City', 'sell_media' ),
		'choose_from_most_used' => __( 'Choose from most used City', 'sell_media' ),
		'menu_name' => __( 'City', 'sell_media' ),
	);

	$city_args = array(
		'labels' => $city_labels,
		'public' => true,
		'show_in_nav_menus' => true,
		'show_ui' => false,
		'show_tagcloud' => true,
		'hierarchical' => false,
		'rewrite' => true,
		'query_var' => true,
	);

	register_taxonomy( 'city', array( 'sell_media_item', 'attachment' ), apply_filters( 'sell_media_city_tax_registration_args', $city_args ) );

	/**
	 * Register State
	*/
	$state_labels = array(
		'name' => __( 'State', 'sell_media' ),
		'singular_name' => __( 'Keyword', 'sell_media' ),
		'search_items' => __( 'Search State', 'sell_media' ),
		'popular_items' => __( 'Popular State', 'sell_media' ),
		'all_items' => __( 'All State', 'sell_media' ),
		'parent_item' => __( 'Parent Keyword', 'sell_media' ),
		'parent_item_colon' => __( 'Parent Keyword:', 'sell_media' ),
		'edit_item' => __( 'Edit Keyword', 'sell_media' ),
		'update_item' => __( 'Update Keyword', 'sell_media' ),
		'add_new_item' => __( 'Add New Keyword', 'sell_media' ),
		'new_item_name' => __( 'New Keyword', 'sell_media' ),
		'separate_items_with_commas' => __( 'Separate state with commas', 'sell_media' ),
		'add_or_remove_items' => __( 'Add or remove State', 'sell_media' ),
		'choose_from_most_used' => __( 'Choose from most used State', 'sell_media' ),
		'menu_name' => __( 'State', 'sell_media' ),
	);

	$state_args = array(
		'labels' => $state_labels,
		'public' => true,
		'show_in_nav_menus' => true,
		'show_ui' => false,
		'show_tagcloud' => true,
		'hierarchical' => false,
		'rewrite' => true,
		'query_var' => true,
	);

	register_taxonomy( 'state', array( 'sell_media_item', 'attachment' ), apply_filters( 'sell_media_state_tax_registration_args', $state_args ) );

}
add_action( 'init', 'sell_media_register_taxonomies', 1 );


/**
 * Add default options on plugin activation
 */
function sell_media_register_default_settings() {

	if ( false === ( $options = get_transient( 'sell_media_options' ) ) ) {

		$defaults = array(
			'test_mode' => 1,
			'customer_notification' => 1,
			'style' => 'light',
			'layout' => 'sell-media-single-one-col',
			'thumbnail_crop' => 'medium',
			'thumbnail_layout' => 'sell-media-three-col',
			'titles' => 0,
			'breadcrumbs' => 1,
			'quick_view' => 1,
			'file_info' => 0,
			'plugin_credit' => 0,
			'post_type_slug' => '',
			'order_by' => 'date-desc',
			'terms_and_conditions' => '',
			'admin_columns' => '',
			'default_price' => '1',
			'hide_original_price' => 'no',
			'default_price_group' => '',
			'price_group' => '',
			'paypal_email' => '',
			'currency' => 'USD',
			'paypal_additional_test_email' => '',
			'tax' => '',
			'tax_rate' => '',
			'from_name' => get_option( 'blogname' ),
			'from_email' => get_option( 'admin_email' ),
			'success_email_subject' => 'Your Purchase',
			'success_email_body' => "Hi {first_name} {last_name},\n\nThanks for purchasing from my site. Here are your download links:\n\n{download_links}\n\nThanks!",
			'misc' => '',
		);

		$options = wp_parse_args( update_option( 'sell_media_options', $defaults ) );
	}
}


/**
 * Create required pages if they don't already exist and are saved in options
 */
function sell_media_autocreate_pages() {

	$settings = sell_media_get_plugin_options();
	$settings = (array) $settings;
	$pages = sell_media_get_pages_array();

	foreach ( $pages as $page ) {
		$setting = $page . '_page';
		$shortcode = '[sell_media_' . $page . ']';
		$title = 'Sell Media ' . ucfirst( $page );

		// Check if this page already exists, with shortcode
		$existing_page = get_page_by_title( $title );
		if ( ! empty( $existing_page ) && 'page' === $existing_page->post_type && has_shortcode( $existing_page->post_content, 'sell_media_' . $page ) ) {
			$settings[ $setting ] = $existing_page->ID;
		} else {
			// If the page doesn't exist, create it
			$new_page = array(
				'post_title'    => $title,
				'post_content'  => $shortcode,
				'post_status'   => 'publish',
				'post_type'     => 'page',
			);

			$post_id = wp_insert_post( $new_page );

			if ( $post_id ) {
				$settings[ $setting ] = $post_id;
			}
		}
	}

	// update the option if it already exists
	if ( get_option( 'sell_media_options' ) !== false ) {
		update_option( 'sell_media_options', $settings );
		// otherwise, we need to create the option
	} else {
		add_option( 'sell_media_options', $settings );
	}

}
