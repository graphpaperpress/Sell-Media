<?php

/**
 * Install Function
 *
 * @package     SellMedia
 * @subpackage  Functions/Install
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8.5
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

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

    if ( $version && $version > SELL_MEDIA_VERSION )
        return;

    // Register Custom Post Types
    sell_media_register_post_types();

    // Register Taxonomies
    sell_media_register_taxonomies();

    // Flush the permalinks
    flush_rewrite_rules();

    // Don't forget registration hook is called
    // BEFORE! taxonomies are registered! therefore
    // these terms and taxonomies are NOT derived from our object!
    $settings = sell_media_get_plugin_options();
    $admin_columns = empty( $settings->admin_columns ) ? null : $settings->admin_columns;

    // Install new table for term meta
    $taxonomy_metadata = new SellMediaTaxonomyMetadata;
    $taxonomy_metadata->activate();
    $taxonomy = 'licenses';

    // Add Personal and Commerical default license terms
    $r_personal = wp_insert_term( 'Personal', $taxonomy, array( 'slug' => 'personal' ) );
    $r_commercial = wp_insert_term( 'Commercial', $taxonomy, array( 'slug' => 'commercial' ) );

    // Install protected folder for uploading files and prevent hotlinking
    $downloads_url = sell_media_get_upload_dir();

    if ( wp_mkdir_p( $downloads_url ) && ! file_exists( $downloads_url.'/.htaccess' ) ) {
      if ( $file_handle = @fopen( $downloads_url . '/.htaccess', 'w' ) ) {
        fwrite( $file_handle, 'deny from all' );
        fclose( $file_handle );
      }
    }

    // Add a new Customer role
    add_role( 'sell_media_customer', 'Customer', array( 'read' => true ) );

    // This is a new install so add the defaults to the options table
    if ( empty( $version ) ){
        $defaults = sell_media_get_plugin_option_defaults();
        update_option( sell_media_get_current_plugin_id() . "_options" , $defaults );
    // A version number exists, so run upgrades
    } else {
        require_once SELL_MEDIA_PLUGIN_DIR . '/inc/admin-upgrade.php';
    }

    // Update the version number
    update_option( 'sell_media_version', SELL_MEDIA_VERSION );

}
register_activation_hook( SELL_MEDIA_PLUGIN_FILE, 'sell_media_install' );


/**
 * Register Custom Post Types
 * @since 1.8.5
 */
function sell_media_register_post_types(){

    $settings = sell_media_get_plugin_options();

    /**
     * Register Item Custom Post Type
     */
    $item_labels = array(
        'name' => __( 'Sell Media', '', 'sell_media' ),
        'singular_name' => __( 'Sell Media', '', 'sell_media' ),
        'all_items' => __( 'All Products', '', 'sell_media' ),
        'add_new' => __( 'Add New', '', 'sell_media' ),
        'add_new_item' => __( 'Sell Media', '', 'sell_media' ),
        'edit_item' => __( 'Edit Product', '', 'sell_media' ),
        'new_item' => __( 'New Product', '', 'sell_media' ),
        'view_item' => __( 'View Product', '', 'sell_media' ),
        'search_items' => __( 'Search Products', '', 'sell_media' ),
        'not_found' => __( 'No products found', '', 'sell_media' ),
        'not_found_in_trash' => __( 'No products found in Trash', '', 'sell_media' ),
        'parent_item_colon' => __( 'Parent Product:', '', 'sell_media' ),
        'menu_name' => __( 'Sell Media', '', 'sell_media' ),
    );

    $item_args = array(
        'labels' => $item_labels,
        'hierarchical' => true,
        'supports' => array( 'title', 'thumbnail', 'author' ),
        'taxonomies' => array( 'licenses', 'keywords', 'city', 'state', 'creator', 'collection' ),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 10,
        'menu_icon' => 'dashicons-cart',
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => array (
            'slug' => empty( $settings->post_type_slug ) ? 'items' : $settings->post_type_slug,
            'feeds' => true ),
        'capability_type' => 'post'
    );

    register_post_type( 'sell_media_item', $item_args );

    /**
     * Register Payment Custom Post Type
     */
    $payment_labels = array(
        'name' => __( 'Payments', '', 'sell_media' ),
        'singular_name' => __( 'Payment', '', 'sell_media' ),
        'add_new' => __( 'Add New', '', 'sell_media' ),
        'add_new_item' => __( 'Add New Payment', '', 'sell_media' ),
        'edit_item' => __( 'Edit Payment', '', 'sell_media' ),
        'new_item' => __( 'New Payment', '', 'sell_media' ),
        'view_item' => __( 'View Payment', '', 'sell_media' ),
        'search_items' => __( 'Search Payments', '', 'sell_media' ),
        'not_found' => __( 'No payments found', '', 'sell_media' ),
        'not_found_in_trash' => __( 'No payments found in Trash', '', 'sell_media' ),
        'parent_item_colon' => __( 'Parent Payment:', '', 'sell_media' ),
        'menu_name' => __( 'Payments', '', 'sell_media' ),
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
        ),
        'map_meta_cap' => true // Allow users to edit/remove existing payments
    );

    register_post_type( 'sell_media_payment', $payment_args );

}
add_action( 'init', 'sell_media_register_post_types', 1 );

/**
 * Register Custom Taxonomies
 * @since 1.8.5
 */
function sell_media_register_taxonomies(){

    $settings = sell_media_get_plugin_options();
    $admin_columns = empty( $settings->admin_columns ) ? null : $settings->admin_columns;

    /**
     * Register Price Group
     */
    $price_group_labels = array(
        'name' => __( 'Price Groups', '', 'sell_media' ),
        'singular_name' => __( 'Price Groups', '', 'sell_media' ),
        'search_items' => __( 'Search Price Groups', '', 'sell_media' ),
        'popular_items' => __( 'Popular Price Groups', '', 'sell_media' ),
        'all_items' => __( 'All Price Groups', '', 'sell_media' ),
        'parent_item' => __( 'Parent Price Groups', '', 'sell_media' ),
        'parent_item_colon' => __( 'Parent Price Groups:', '', 'sell_media' ),
        'edit_item' => __( 'Edit Price Group', '', 'sell_media' ),
        'update_item' => __( 'Update Price Group', '', 'sell_media' ),
        'add_new_item' => __( 'Add New Price Group', '', 'sell_media' ),
        'new_item_name' => __( 'New Price Group', '', 'sell_media' ),
        'separate_items_with_commas' => __( 'Separate Price Groups with commas', '', 'sell_media' ),
        'add_or_remove_items' => __( 'Add or remove Price Groups', '', 'sell_media' ),
        'choose_from_most_used' => __( 'Choose from most used Price Groups', '', 'sell_media' ),
        'menu_name' => __( 'Price Groups', '', 'sell_media' ),
    );

    $price_group_args = array(
        'labels' => $price_group_labels,
        'public' => true,
        'show_in_nav_menus' => true,
        'show_tagcloud' => true,
        'show_admin_column' => true,
        'hierarchical' => true,
        'rewrite' => true,
        'query_var' => true
    );

    register_taxonomy( 'price-group', array( 'sell_media_item' ), $price_group_args );

    /**
     * Register Collection
     */
    $collection_labels = array(
        'name' => __( 'Product Collections', '', 'sell_media' ),
        'singular_name' => __( 'Collection', '', 'sell_media' ),
        'search_items' => __( 'Search Collection', '', 'sell_media' ),
        'popular_items' => __( 'Popular Collection', '', 'sell_media' ),
        'all_items' => __( 'All Collections', '', 'sell_media' ),
        'parent_item' => __( 'Parent Collection', '', 'sell_media' ),
        'parent_item_colon' => __( 'Parent Collection:', '', 'sell_media' ),
        'edit_item' => __( 'Edit Collection', '', 'sell_media' ),
        'update_item' => __( 'Update Collection', '', 'sell_media' ),
        'add_new_item' => __( 'Add New Collection', '', 'sell_media' ),
        'new_item_name' => __( 'New Collection', '', 'sell_media' ),
        'separate_items_with_commas' => __( 'Separate collection with commas', '', 'sell_media' ),
        'add_or_remove_items' => __( 'Add or remove Collection', '', 'sell_media' ),
        'choose_from_most_used' => __( 'Choose from most used Collection', '', 'sell_media' ),
        'menu_name' => __( 'Collections', '', 'sell_media' ),
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
        'query_var' => true
    );

    register_taxonomy( 'collection', array( 'sell_media_item' ), $collection_args );

    /**
     * Register Licenses
     */
    $licenses_labels = array(
        'name' => __( 'Licenses', '', 'sell_media' ),
        'singular_name' => __( 'License', '', 'sell_media' ),
        'search_items' => __( 'Search Licenses', '', 'sell_media' ),
        'popular_items' => __( 'Popular Licenses', '', 'sell_media' ),
        'all_items' => __( 'All Licenses', '', 'sell_media' ),
        'parent_item' => __( 'Parent License', '', 'sell_media' ),
        'parent_item_colon' => __( 'Parent License:', '', 'sell_media' ),
        'edit_item' => __( 'Edit License', '', 'sell_media' ),
        'update_item' => __( 'Update License', '', 'sell_media' ),
        'add_new_item' => __( 'Add New License', '', 'sell_media' ),
        'new_item_name' => __( 'New License', '', 'sell_media' ),
        'separate_items_with_commas' => __( 'Separate licenses with commas', '', 'sell_media' ),
        'add_or_remove_items' => __( 'Add or remove Licenses', '', 'sell_media' ),
        'choose_from_most_used' => __( 'Choose from most used Licenses', '', 'sell_media' ),
        'menu_name' => __( 'Licenses', '', 'sell_media' ),
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
        'query_var' => true
    );

    register_taxonomy( 'licenses', array( 'sell_media_item' ), $licenses_args );

    /**
     * Register Keywords
     */
    $keywords_labels = array(
        'name' => __( 'Keywords', '', 'sell_media' ),
        'singular_name' => __( 'Keyword', '', 'sell_media' ),
        'search_items' => __( 'Search Keywords', '', 'sell_media' ),
        'popular_items' => __( 'Popular Keywords', '', 'sell_media' ),
        'all_items' => __( 'All Keywords', '', 'sell_media' ),
        'parent_item' => __( 'Parent Keyword', '', 'sell_media' ),
        'parent_item_colon' => __( 'Parent Keyword:', '', 'sell_media' ),
        'edit_item' => __( 'Edit Keyword', '', 'sell_media' ),
        'update_item' => __( 'Update Keyword', '', 'sell_media' ),
        'add_new_item' => __( 'Add New Keyword', '', 'sell_media' ),
        'new_item_name' => __( 'New Keyword', '', 'sell_media' ),
        'separate_items_with_commas' => __( 'Separate keywords with commas', '', 'sell_media' ),
        'add_or_remove_items' => __( 'Add or remove Keywords', '', 'sell_media' ),
        'choose_from_most_used' => __( 'Choose from most used Keywords', '', 'sell_media' ),
        'menu_name' => __( 'Keywords', '', 'sell_media' ),
    );

    $keywords_args = array(
        'labels' => $keywords_labels,
        'public' => true,
        'show_in_nav_menus' => true,
        'show_admin_column' => ( ! empty( $admin_columns ) && in_array( 'show_keywords', $admin_columns ) ) ? true : false,
        'show_ui' => true,
        'show_tagcloud' => true,
        'hierarchical' => false,
        'rewrite' => true,
        'query_var' => true
    );

    register_taxonomy( 'keywords', array( 'sell_media_item' ), $keywords_args );

    /**
     * Register Creator
     */
    $creator_labels = array(
        'name' => __( 'Creators', '', 'sell_media' ),
        'singular_name' => __( 'Creator', '', 'sell_media' ),
        'search_items' => __( 'Search Creator', '', 'sell_media' ),
        'popular_items' => __( 'Popular Creator', '', 'sell_media' ),
        'all_items' => __( 'All Creator', '', 'sell_media' ),
        'parent_item' => __( 'Parent Creator', '', 'sell_media' ),
        'parent_item_colon' => __( 'Parent Creator:', '', 'sell_media' ),
        'edit_item' => __( 'Edit Creator', '', 'sell_media' ),
        'update_item' => __( 'Update Creator', '', 'sell_media' ),
        'add_new_item' => __( 'Add New Creator', '', 'sell_media' ),
        'new_item_name' => __( 'New Creator', '', 'sell_media' ),
        'separate_items_with_commas' => __( 'Separate creator with commas', '', 'sell_media' ),
        'add_or_remove_items' => __( 'Add or remove Creator', '', 'sell_media' ),
        'choose_from_most_used' => __( 'Choose from most used Creator', '', 'sell_media' ),
        'menu_name' => __( 'Creators', '', 'sell_media' ),
    );

    $creator_args = array(
        'labels' => $creator_labels,
        'public' => true,
        'show_in_nav_menus' => true,
        'show_admin_column' => ( ! empty( $admin_columns ) && in_array( 'show_creators', $admin_columns ) ) ? true : false,
        'show_tagcloud' => true,
        'hierarchical' => false,
        'rewrite' => true,
        'query_var' => true
    );

    register_taxonomy( 'creator', array('sell_media_item'), $creator_args );

    /**
     * Register City
    */
    $city_labels = array(
        'name' => __( 'City', '', 'sell_media' ),
        'singular_name' => __( 'Keyword', '', 'sell_media' ),
        'search_items' => __( 'Search City', '', 'sell_media' ),
        'popular_items' => __( 'Popular City', '', 'sell_media' ),
        'all_items' => __( 'All City', '', 'sell_media' ),
        'parent_item' => __( 'Parent Keyword', '', 'sell_media' ),
        'parent_item_colon' => __( 'Parent Keyword:', '', 'sell_media' ),
        'edit_item' => __( 'Edit Keyword', '', 'sell_media' ),
        'update_item' => __( 'Update Keyword', '', 'sell_media' ),
        'add_new_item' => __( 'Add New Keyword', '', 'sell_media' ),
        'new_item_name' => __( 'New Keyword', '', 'sell_media' ),
        'separate_items_with_commas' => __( 'Separate city with commas', '', 'sell_media' ),
        'add_or_remove_items' => __( 'Add or remove City', '', 'sell_media' ),
        'choose_from_most_used' => __( 'Choose from most used City', '', 'sell_media' ),
        'menu_name' => __( 'City', '', 'sell_media' ),
    );

    $city_args = array(
        'labels' => $city_labels,
        'public' => true,
        'show_in_nav_menus' => true,
        'show_ui' => false,
        'show_tagcloud' => true,
        'hierarchical' => false,
        'rewrite' => true,
        'query_var' => true
    );

    register_taxonomy( 'city', array( 'sell_media_item' ), $city_args );

    /**
     * Register State
    */
    $state_labels = array(
        'name' => __( 'State', '', 'sell_media' ),
        'singular_name' => __( 'Keyword', '', 'sell_media' ),
        'search_items' => __( 'Search State', '', 'sell_media' ),
        'popular_items' => __( 'Popular State', '', 'sell_media' ),
        'all_items' => __( 'All State', '', 'sell_media' ),
        'parent_item' => __( 'Parent Keyword', '', 'sell_media' ),
        'parent_item_colon' => __( 'Parent Keyword:', '', 'sell_media' ),
        'edit_item' => __( 'Edit Keyword', '', 'sell_media' ),
        'update_item' => __( 'Update Keyword', '', 'sell_media' ),
        'add_new_item' => __( 'Add New Keyword', '', 'sell_media' ),
        'new_item_name' => __( 'New Keyword', '', 'sell_media' ),
        'separate_items_with_commas' => __( 'Separate state with commas', '', 'sell_media' ),
        'add_or_remove_items' => __( 'Add or remove State', '', 'sell_media' ),
        'choose_from_most_used' => __( 'Choose from most used State', '', 'sell_media' ),
        'menu_name' => __( 'State', '', 'sell_media' ),
    );

    $state_args = array(
        'labels' => $state_labels,
        'public' => true,
        'show_in_nav_menus' => true,
        'show_ui' => false,
        'show_tagcloud' => true,
        'hierarchical' => false,
        'rewrite' => true,
        'query_var' => true
    );

    register_taxonomy( 'state', array( 'sell_media_item' ), $state_args );

}
add_action( 'init', 'sell_media_register_taxonomies', 1 );