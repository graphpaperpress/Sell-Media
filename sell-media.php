<?php

/*
Plugin Name: Sell Media
Plugin URI: http://graphpaperpress.com/plugins/sell-media/
Description: A plugin for selling digital downloads and reprints.
Version: 1.8.2
Author: Graph Paper Press
Author URI: http://graphpaperpress.com
Author Email: support@graphpaperpress.com
License: GPL
*/

define( 'SELL_MEDIA_VERSION', '1.8.2' );
define( 'SELL_MEDIA_PLUGIN_FILE', plugin_dir_path(__FILE__) . 'sell-media.php' );

include( dirname(__FILE__) . '/inc/class-customer.php' );
include( dirname(__FILE__) . '/inc/class-downloads.php' );
include( dirname(__FILE__) . '/inc/helpers.php');
include( dirname(__FILE__) . '/inc/gateways/paypal.php' );
include( dirname(__FILE__) . '/inc/shortcodes.php' );
include( dirname(__FILE__) . '/inc/template-tags.php' );
include( dirname(__FILE__) . '/inc/class-search.php' );
include( dirname(__FILE__) . '/inc/class-payments.php' );
include( dirname(__FILE__) . '/inc/class-products.php' );
include( dirname(__FILE__) . '/inc/class-products-images.php' );

include( dirname(__FILE__) . '/inc/term-meta.php' );
include( dirname(__FILE__) . '/inc/widgets.php' );
include_once( dirname(__FILE__) . '/settings/settings.php');
include( dirname(__FILE__) . '/inc/deprecated.php' );

if ( is_admin() ) {
    include( dirname(__FILE__) . '/inc/admin-attachments.php' );
    include( dirname(__FILE__) . '/inc/admin-bulk.php' );
    include( dirname(__FILE__) . '/inc/admin-items.php' );
    include( dirname(__FILE__) . '/inc/admin-extensions.php' );
    include( dirname(__FILE__) . '/inc/admin-payments.php' );
    include( dirname(__FILE__) . '/inc/admin-price-groups.php' );
    include( dirname(__FILE__) . '/inc/admin-notices.php' );
}


/**
 * Sell Media class for activation, init, install, deactivation
 *
 * @since 0.1
 */
class SellMedia {

    // Ret our protected file upload path
    const upload_dir = '/sell_media';

    function __construct() {
        // Register an activation hook for the plugin
        register_activation_hook( __FILE__, array( &$this, 'install' ) );
        add_action( 'init', array( &$this, 'init' ) );
        add_action( 'admin_init', array( &$this, 'initAdmin' ) );
        add_action( 'admin_menu', array( &$this, 'adminMenus' ), 999 );
        add_action( 'admin_enqueue_scripts', array( &$this, 'adminScripts' ) );
        add_action( 'wp_enqueue_scripts', array( &$this, 'publicScripts' ) );
        add_action( 'pre_get_posts', array( &$this, 'collection_password_check' ) );
        add_action( 'wp_footer', array( &$this, 'footer' ) );
        add_action( 'parse_query', array( &$this, 'search_warning_surpression' ) );
        add_action( 'plugins_loaded', array( &$this, 'textdomain' ) );

        if ( ! is_admin() ){
            add_filter( 'posts_orderby', array( &$this, 'order_by') );
        }
    }


    /**
     * Runs when the plugin is activated
     * Do not generate any output here!
     *
     * @since 0.1
     */
    public function install() {

        $version = get_option( 'sell_media_version' );

        if ( $version && $version > SELL_MEDIA_VERSION )
            return;

        // Don't forget registration hook is called
        // BEFORE! taxonomies are registered! therefore
        // these terms and taxonomies are NOT derived from our object!
        $settings = sell_media_get_plugin_options();
        $admin_columns = empty( $settings->admin_columns ) ? null : $settings->admin_columns;
        $this->registerLicenses( $admin_columns );

        // Install new table for term meta
        $taxonomy_metadata = new SELL_MEDIA_Taxonomy_Metadata;
        $taxonomy_metadata->activate();
        $taxonomy = 'licenses';

        /**
         * Add our Parent terms
         *
         * Parent -- book, magazine, newspaper, website
         * Commercial -- annual reports, billboards, brochures, print advertising, product packaging, public
         * relations, web advertising, websites
         */
        $r_personal = wp_insert_term( 'Personal', $taxonomy, array( 'slug' => 'personal' ) );
        $r_commercial = wp_insert_term( 'Commercial', $taxonomy, array( 'slug' => 'commercial' ) );

        /**
         * Personal
         *
         * Get our Parent term and add child terms
         */
        $personal = term_exists( 'Personal', $taxonomy );
        $personal_terms = array( 'Book', 'Magazine', 'Newspaper', 'Website' );
        foreach ( $personal_terms as $term ) {
            if ( ! term_exists( $term, $taxonomy ) ){
                wp_insert_term( $term, $taxonomy, array( 'slug' => strtolower(str_replace( ' ', '-', $term ) ), 'parent' => $personal['term_id'] ) );
            }
        }

        /**
         * Commercial
         *
         * Get our Parent term and add child terms
         */
        $commercial = term_exists( 'Commercial', $taxonomy );
        $commercial_terms = array( 'Annual Reports', 'Billboards', 'Brochures', 'Print Advertising', 'Product Advertising', 'Product Packaging', 'Public Relations', 'Web Advertising', 'Website' );
        foreach ( $commercial_terms as $term ) {
            if ( ! term_exists( $term, $taxonomy ) ){
                wp_insert_term( $term, $taxonomy, array( 'slug' => strtolower(str_replace( ' ', '-', $term ) ), 'parent' => $commercial['term_id'] ) );
            }
        }

        wp_update_term( $commercial['term_id'], $taxonomy, array( 'description' => '' ) );

        // Install folder for uploading files and prevent hotlinking
        $upload_dir =  wp_upload_dir();
        $downloads_url = $upload_dir['basedir'] . '/' . self::upload_dir;

        if ( wp_mkdir_p( $downloads_url ) && ! file_exists( $downloads_url.'/.htaccess' ) ) {
          if ( $file_handle = @fopen( $downloads_url . '/.htaccess', 'w' ) ) {
            fwrite($file_handle, 'deny from all');
            fclose($file_handle);
          }
        }

        add_role( 'sell_media_customer', 'Customer', array( 'read' => true ) );


        // This is a new install so add the defaults to the options table
        if ( empty( $version ) ){
            include_once(plugin_dir_path(__FILE__).'settings/settings.php');
            include_once(plugin_dir_path( __FILE__ ).'sell-media-settings.php');

            $defaults = sell_media_get_plugin_option_defaults();
            update_option( sell_media_get_current_plugin_id() . "_options" , $defaults );
        } else {
            // Update script to new settings
            include( dirname(__FILE__) . '/inc/admin-upgrade.php' );
        }


        $this->init();
        flush_rewrite_rules();

        update_option( 'sell_media_version', SELL_MEDIA_VERSION );

    } // end install();


    /**
     * Runs when the plugin is initialized
     */
    public function init() {

        $settings = sell_media_get_plugin_options();
        $admin_columns = empty( $settings->admin_columns ) ? null : $settings->admin_columns;

        $this->registerCollection( $admin_columns );
        $this->registerLicenses( $admin_columns );
        $this->registerCreator( $admin_columns );
        $this->registerCity( $admin_columns );
        $this->registerKeywords( $admin_columns );
        $this->registerState( $admin_columns );
        $this->registerItem();
        $this->registerPayment();
        $this->registerPriceGroup();

        include_once( plugin_dir_path( __FILE__ ) . 'sell-media-settings.php' );
    }


    /**
     * Flush permalinks every time plugin version number is updated
     * Do not generate any output here!
     *
     * @since 0.1
     */
    public function initAdmin(){

        $version = get_option( 'sell_media_version' );

        if ( $version < SELL_MEDIA_VERSION ) {
            global $wp_rewrite;
            $wp_rewrite->flush_rules();
        }
    }


    /**
     * Add all menus under Sell Media.
     *
     * @since 1.0
     */
    public function adminMenus(){

        $permission = 'manage_options';

        add_submenu_page( 'edit.php?post_type=sell_media_item', __('Add Bulk', 'sell_media'), __('Add Bulk', 'sell_media'),  'upload_files', 'sell_media_add_bulk', 'sell_media_add_bulk_callback_fn' );
        add_submenu_page( 'edit.php?post_type=sell_media_item', __('Payments', 'sell_media'), __('Payments', 'sell_media'),  $permission, 'sell_media_payments', 'sell_media_payments_callback_fn' );
        add_submenu_page( 'edit.php?post_type=sell_media_item', __('Reports', 'sell_media'), __('Reports', 'sell_media'),  $permission, 'sell_media_reports', 'sell_media_reports_callback_fn' );
        add_submenu_page( 'edit.php?post_type=sell_media_item', __('Extensions', 'sell_media'), __('Extensions', 'sell_media'),  $permission, 'sell_media_extensions', 'sell_media_extensions_callback_fn' );
        remove_submenu_page( 'edit.php?post_type=sell_media_item', 'edit-tags.php?taxonomy=price-group&amp;post_type=sell_media_item' );

        do_action( 'sell_media_menu_hook' );
    }


    /**
     * Register License Taxonomy
     *
     * @author Thad Allender
     */
    public function registerLicenses($admin_columns=null) {
        $labels = array(
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

        $args = array(
            'labels' => $labels,
            'public' => true,
            'show_in_nav_menus' => true,
            'show_admin_column' => ( ! empty( $admin_columns ) && in_array('show_license', $admin_columns) ) ? true : false,
            'show_ui' => true,
            'show_tagcloud' => true,
            'hierarchical' => true,
            'rewrite' => true,
            'query_var' => true
        );

        register_taxonomy( 'licenses', array('sell_media_item'), $args );
    }


    /**
     * Register Keywords Taxonomy
     *
     * @author Thad Allender
     */
    public function registerKeywords($admin_columns=null) {
        $labels = array(
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

        $args = array(
            'labels' => $labels,
            'public' => true,
            'show_in_nav_menus' => true,
            'show_admin_column' => ( ! empty( $admin_columns ) && in_array('show_keywords', $admin_columns) ) ? true : false,
            'show_ui' => true,
            'show_tagcloud' => true,
            'hierarchical' => false,
            'rewrite' => true,
            'query_var' => true
        );

        register_taxonomy( 'keywords', array('sell_media_item'), $args );
    }


    /**
     * Register City Taxonomy
     *
     * @author Thad Allender
     */
    public function registerCity() {
        $labels = array(
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

        $args = array(
            'labels' => $labels,
            'public' => true,
            'show_in_nav_menus' => true,
            'show_ui' => false,
            'show_tagcloud' => true,
            'hierarchical' => false,
            'rewrite' => true,
            'query_var' => true
        );

        register_taxonomy( 'city', array('sell_media_item'), $args );
    }


    /**
     * Register State Taxonomy
     *
     * @author Thad Allender
     */
    public function registerState() {
        $labels = array(
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

        $args = array(
            'labels' => $labels,
            'public' => true,
            'show_in_nav_menus' => true,
            'show_ui' => false,
            'show_tagcloud' => true,
            'hierarchical' => false,
            'rewrite' => true,
            'query_var' => true
        );

        register_taxonomy( 'state', array('sell_media_item'), $args );
    }


    /**
     * Register Creator Taxonomy
     *
     * @author Thad Allender
     */
    public function registerCreator($admin_columns=null) {
        $labels = array(
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

        $args = array(
            'labels' => $labels,
            'public' => true,
            'show_in_nav_menus' => true,
            'show_admin_column' => ( ! empty( $admin_columns ) && in_array('show_creators', $admin_columns) ) ? true : false,
            'show_tagcloud' => true,
            'hierarchical' => false,
            'rewrite' => true,
            'query_var' => true
        );

        register_taxonomy( 'creator', array('sell_media_item'), $args );
    }


    /**
     * Register Collection Taxonomy
     *
     * @author Thad Allender
     */
    public function registerCollection($admin_columns=null) {
        $labels = array(
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

        $args = array(
            'labels' => $labels,
            'public' => true,
            'show_in_nav_menus' => true,
            'show_admin_column' => ( ! empty( $admin_columns ) && in_array('show_collection', $admin_columns) ) ? true : false,
            'show_ui' => true,
            'show_tagcloud' => true,
            'hierarchical' => true,
            'rewrite' => array('hierarchical' => true ),
            'query_var' => true
        );

        register_taxonomy( 'collection', array( 'sell_media_item' ), $args );
    }


    /**
     * Register Item Custom Post Type
     *
     * @author Thad Allender
     */
    public function registerItem() {

        $labels = array(
            'name' => __( 'Sell Media', '', 'sell_media' ),
            'singular_name' => __( 'Sell Media', '', 'sell_media' ),
            'all_items' => __( 'All Products', '', 'sell_media' ),
            'add_new' => __( 'Add Product', '', 'sell_media' ),
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

        $settings = sell_media_get_plugin_options();

        $args = array(
            'labels' => $labels,
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

        register_post_type( 'sell_media_item', $args );
    }


    /**
     * Register Payment Custom Post Type
     *
     * @author Thad Allender
     */
    public function registerPayment() {

        $labels = array(
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

        $args = array(
            'labels' => $labels,
            'hierarchical' => false,
            'supports' => array( 'title' ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'publicly_queryable' => false,
            'has_archive' => false,
            'query_var' => true,
            'rewrite' => false,
            'capability_type' => 'post'
        );

        register_post_type( 'sell_media_payment', $args );
    }


    public function registerPriceGroup() {
        $labels = array(
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

        $args = array(
            'labels' => $labels,
            'public' => true,

            'show_in_nav_menus' => true,
            'show_tagcloud' => true,
            'show_admin_column' => true,

            'hierarchical' => true,
            'rewrite' => true,
            'query_var' => true
        );

        register_taxonomy( 'price-group', array('sell_media_item'), $args );
    }

    /**
     * Admin scripts
     */
    public function adminScripts( $hook ) {

        if ( sell_media_is_sell_media_post_type_page() || 'post.php' == $hook || 'post-new.php' == $hook ) {
            wp_enqueue_style( 'sell_media-admin', plugin_dir_url( __FILE__ ) . 'css/sell_media-admin.css', array( 'thickbox' ), SELL_MEDIA_VERSION );
            wp_enqueue_script( 'sell_media-admin-items', plugin_dir_url( __FILE__ ) . 'js/admin-items.js', array( 'jquery' ), SELL_MEDIA_VERSION );

            if ( sell_media_is_license_page() || sell_media_is_license_term_page() ) {
                wp_enqueue_script( 'sell_media-admin', plugin_dir_url( __FILE__ ) . 'js/sell_media-admin.js', array( 'jquery', 'jquery-ui-sortable' ), SELL_MEDIA_VERSION );
                wp_enqueue_script( 'jquery-ui-slider' );
            }
        }

        if ( sell_media_is_reports_page() ) {
            wp_enqueue_script( 'google_charts', 'https://www.google.com/jsapi', array( 'jquery' ), SELL_MEDIA_VERSION );
        }
    }

    /**
     * Public scripts
     */
    public function publicScripts( $hook ) {

        wp_enqueue_script( 'sell_media', plugin_dir_url( __FILE__ ) . 'js/sell_media.js', array( 'jquery' ), SELL_MEDIA_VERSION );
        wp_enqueue_script( 'sellMediaCart', plugin_dir_url( __FILE__ ) . 'js/sell_media_cart.js', array( 'jquery' ), SELL_MEDIA_VERSION );
        wp_enqueue_style( 'sell_media', plugin_dir_url( __FILE__ ) . 'css/sell_media.css', null, SELL_MEDIA_VERSION );
        wp_enqueue_style( 'sell_media-widgets-style', plugin_dir_url( __FILE__ ) . 'css/sell_media_widgets.css', null, SELL_MEDIA_VERSION );

        if ( isset( $settings->style ) && '' != $settings->style ) {
            wp_enqueue_style( 'sell-media-style', plugin_dir_url( __FILE__ ) . 'css/sell_media-' . $settings->style . '.css' );
        } else {
            wp_enqueue_style( 'sell-media-style', plugin_dir_url( __FILE__ ) . 'css/sell_media-light.css' );
        }

        $settings = sell_media_get_plugin_options();

        wp_localize_script( 'sell_media', 'sell_media', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'pluginurl' => plugin_dir_url( dirname( __FILE__ ) ),
            'site_name' => get_bloginfo( 'name' ),
            'checkout_url' => empty( $settings->checkout_page ) ? null : get_permalink( $settings->checkout_page ),
            'currency_symbol' => $settings->currency,
            'error' => array(
                'email_exists' => __('Sorry that email already exists or is invalid', 'sell_media')
                ),
            'sandbox' => ( $settings->test_mode == 1 ) ? 'true' : 'false',
            'paypal_email' => ( empty( $settings->paypal_email ) ) ? null : $settings->paypal_email,
            // set this in stripe extension? and make use testing or live key
            'stripe_public_key' => ( empty( $settings->stripe_test_publishable_key ) ) ? null : $settings->stripe_test_publishable_key,
            'thanks_page' => get_permalink( $settings->thanks_page ),
            'listener_url' => site_url( '?sell_media-listener=IPN' ),
            'added_to_cart' => sprintf(
                "%s! <a href='" . get_permalink( $settings->checkout_page ) . "' class='cart'>%s</a>!",
                __( 'Added', 'sell_media' ),
                __( 'Checkout now','sell_media' ) ),
            'cart_labels' => array(
                'name' => __( 'Name', 'sell_media' ),
                'size' => __( 'Size', 'sell_media' ),
                'license' => __( 'License', 'sell_media' ),
                'price' => __( 'Price', 'sell_media' ),
                'qty' => __( 'Qty', 'sell_media' ),
                'sub_total' => __( 'Subtotal', 'sell_media' )
                ),
            'cart_style' => apply_filters( 'sell_media_cart_style', 'table' ),
            'tax' => ( empty( $settings->tax ) ) ? 0 : $settings->tax_rate,
            'shipping' => apply_filters( 'sell_media_shipping', 0 ), // should PayPal force buyers add address
            'cart_error' => __( 'There was an error loading the cart data. Please contact the site owner.', 'sell_media' ),
            'checkout_text' => __( 'Checkout Now', 'sell_media' ),
            'checkout_wait_text' => __( 'Please wait...', 'sell_media' )
        ) );
    }


    public function collection_password_check( $query ){

        if ( is_admin() ) return $query;
        if ( ! $query->is_main_query() ) return $query;

        // JetPack Infinite Scroll fix
        if ( class_exists( 'Jetpack' ) && Jetpack::is_module_active( 'infinite-scroll' ) )
            return $query;

        if ( ! empty( $_GET['sell_media_advanced_search_flag'] ) ) return;

        /**
         * Check if "collections" is present in query vars
         */
        if ( ! empty( $query->query_vars['collection'] ) ){
            $term_obj = get_term_by( 'slug', $query->query_vars['collection'], 'collection' );
            if ( $term_obj ){
                $term_id = $term_obj->term_id;
            }
        }


        /**
         * Check if this is a single sell_media_item page
         * note is_singular('sell_media_item') does not work here
         */
        else if ( is_single() && ! empty( $query->query['post_type'] )
            && $query->query['post_type'] == 'sell_media_item'
            && ! empty( $query->query['sell_media_item'] ) ){
            global $wpdb;

            /**
             * build an array of terms that are password protected
             */
            foreach( get_terms('collection') as $term_obj ){
                $password = sell_media_get_term_meta( $term_obj->term_id, 'collection_password', true );
                if ( $password ) $exclude_term_ids[] = $term_obj->term_id;
            }


            /**
             * Apparently none of our globals are set and the post_id is not in $query
             * so we run this query to get our post_id
             */
            $post_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE `post_name` LIKE '{$query->query['sell_media_item']}' AND post_type LIKE 'sell_media_item';");


            /**
             * Determine if this post has the given term and the term has a password
             * if it does we set our term_id to the password protected term
             */
            if ( ! empty( $exclude_term_ids ) ){
                foreach( $exclude_term_ids as $t ){
                    if ( has_term( $t, 'collection', $post_id ) && sell_media_get_term_meta( $t, 'collection_password', true ) ){
                        $term_id = $t;
                        $message = __( 'This item is password protected', 'sell_media' );
                    }
                }
            }
        }


        /**
         * Filter out posts that are in password protected collections from our archive pages
         * We need to check additional post_type since this will pass as true for nav_menu_item
         */
        else if ( is_post_type_archive('sell_media_item')
            && ! empty( $query->query['post_type'] ) && $query->query['post_type'] == 'sell_media_item'
            || is_home()
            || is_tax()
            || is_page()
            || is_single()
            ){

            /**
             * build an array of terms that are password protected
             */
            foreach( get_terms('collection') as $term_obj ){
                $password = sell_media_get_term_meta( $term_obj->term_id, 'collection_password', true );
                if ( $password ) $exclude_term_ids[] = $term_obj->term_id;
            }


            if ( ! empty( $exclude_term_ids ) ){
                // echo 'exclude these ids: ';
                $tax_query = array(
                         'relation' => 'AND',
                         array(
                             'taxonomy' => 'collection',
                             'field' => 'id',
                             'terms' => $exclude_term_ids,
                             'operator' => 'NOT IN'
                             )
                         );
            }

            $search = New SellMediaSearch;
            if ( $search->keyword_ids ){
                $tax_query[] = array(
                    'taxonomy' => 'keywords',
                    'field' => 'id',
                    'terms' => $search->keyword_ids,
                    'operator' => 'IN'
                );
            }

            if ( $search->collection_ids ){
                $tax_query[] = array(
                    'taxonomy' => 'collection',
                    'field' => 'id',
                    'terms' => $search->collection_ids,
                    'operator' => 'IN'
                );
            }

            if ( isset( $tax_query ) )
                $query->set( 'tax_query', $tax_query );

        }


        /**
         * Just set our term_id to null.
         */
        else {
            $term_id = null;
        }

        /**
         * If we have a term ID check if this term is password protected
         */
        if ( ! empty( $term_id ) ) {

            /**
             * get the password for the collection
             */
            $password = sell_media_get_term_meta( $term_id, 'collection_password', true );
            if ( empty( $password ) ){
                $child_term = get_term( $term_id, 'collection' );
                $parent_term = get_term( $child_term->parent, 'collection' );
                if ( ! empty( $parent_term->term_id ) )
                    $password = sell_media_get_term_meta( $parent_term->term_id, 'collection_password', true );
                else
                    $password = null;
            }

            if ( ! isset( $_SESSION ) ) session_start();

            /**
             * Since we do not have a "logout link" and can't rely on
             * "garbage collection", we end our session after 30 minutes.
             */
            if ( isset( $_SESSION['sell_media']['recent_activity'] ) &&
                ( time() - $_SESSION['sell_media']['recent_activity'] > ( 30 * 60 ) ) ) {
                session_destroy();
                session_unset();
            }
            $_SESSION['sell_media']['recent_activity'] = time(); // the start of the session.


            if ( ! empty( $password ) ) {
                if ( ! empty( $_POST['collection_password'] ) && $_POST['collection_password'] == $password
                    || ! empty( $_SESSION['sell_media']['collection_password'][$term_id] )
                    || ! empty( $_SESSION['sell_media']['collection_password'][$term_id] )
                    && $_SESSION['sell_media']['collection_password'][$term_id] == $password ) {

                    if ( empty( $_SESSION['sell_media']['collection_password'][$term_id] ) )
                        $_SESSION['sell_media']['collection_password'][$term_id] = $_POST['collection_password'];

                    return $query;
                } else {
                    $custom = locate_template( 'collection-password.php' );
                    if ( empty( $custom ) ){
                        load_template( plugin_dir_path( __FILE__ ) . 'themes/collection-password.php');
                        exit();
                    } else {
                        load_template( $custom );
                    }
                }
            }
        } else {
            return $query;
        }
    }


    public function order_by( $orderby_statement ) {

        $settings = sell_media_get_plugin_options();

        if ( ! empty( $settings->order_by ) && is_archive() ||
             ! empty( $settings->order_by ) && is_tax() ){
            global $wpdb;
            switch( $settings->order_by ){
                case 'title-asc' :
                    $order_by = "{$wpdb->prefix}posts.post_title ASC";
                    break;
                case 'title-desc' :
                    $order_by = "{$wpdb->prefix}posts.post_title DESC";
                    break;
                case 'date-asc' :
                    $order_by = "{$wpdb->prefix}posts.post_date ASC";
                    break;
                case 'date-desc' :
                    $order_by = "{$wpdb->prefix}posts.post_date DESC";
                    break;
            }
        } else {
            $order_by = $orderby_statement;
        }
        return $order_by;
    }

    /*
     * Put the cart in the footer
     */
    public function footer(){
        $settings = sell_media_get_plugin_options();
        if ( ! is_page( $settings->checkout_page ) || ! is_page( $settings->login_page ) || ! is_page( $settings->dashboard_page ) ) : ?>

            <div id="sell-media-dialog-box" class="sell-media-dialog-box" style="display:none">
                <div id="sell-media-dialog-box-target"></div>
            </div>
            <div id="sell-media-dialog-overlay" class="sell-media-dialog-overlay" style="display:none"></div>

        <?php endif;
    }

    /**
     * Adjust wp_query for when search is submitted error no longer shows in "general-template.php"
     * detail here: http://wordpress.stackexchange.com/questions/71157/undefined-property-stdclasslabels-in-general-template-php-post-type-archive
     * @author Zane Matthew
     * @since 1.2.3
     */
    public function search_warning_surpression( $wp_query ){
        if ( $wp_query->is_post_type_archive && $wp_query->is_tax )
            $wp_query->is_post_type_archive = false;
    }

    /**
     * Make plugin translatable
     * @since 1.8
     */
    public function textdomain(){
        load_plugin_textdomain( 'sell_media', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

} // end class

$a = new SellMedia();