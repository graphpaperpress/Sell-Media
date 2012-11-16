<?php

/*
Plugin Name: Sell Media
Plugin URI: http://graphpaperpress.com/plugins/sell-media
Description: A plugin for selling digital downloads and reprints.
Version: 1.0.2
Author: Graph Paper Press
Author URI: http://graphpaperpress.com
Author Email: support@graphpaperpress.com
License: GPL
*/

define( 'SELL_MEDIA_VERSION', '1.0.2' );

include( dirname(__FILE__) . '/inc/cart.php' );
include( dirname(__FILE__) . '/inc/downloads.php' );
include( dirname(__FILE__) . '/inc/helpers.php');
include( dirname(__FILE__) . '/inc/gateways/paypal.php' );
include( dirname(__FILE__) . '/inc/shortcodes.php' );
include( dirname(__FILE__) . '/inc/template-tags.php' );
include( dirname(__FILE__) . '/inc/term-meta.php' );

if ( is_admin() ) {
    include( dirname(__FILE__) . '/inc/admin-attachments.php' );
    include( dirname(__FILE__) . '/inc/admin-items.php' );
    include( dirname(__FILE__) . '/inc/admin-extensions.php' );
    include( dirname(__FILE__) . '/inc/admin-mime-types.php' );
    include( dirname(__FILE__) . '/inc/admin-payments.php' );
    include( dirname(__FILE__) . '/inc/admin-settings.php' );
}


/**
 * Start our PHP session for shopping cart
 *
 * @since 0.1
 */
if ( ! session_start() ) session_start();


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
        add_action( 'admin_menu', array( &$this, 'adminMenus' ) );

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

        update_option( 'sell_media_version', SELL_MEDIA_VERSION );

        // Dont forget registration hook is called
        // BEFORE! taxonomies are regsitered! therefore
        // these terms and taxonomies are NOT derived from our object!
        $this->registerLicenses();

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
            wp_insert_term( $term, $taxonomy, array( 'slug' => strtolower(str_replace( ' ', '-', $term ) ), 'parent' => $personal['term_id'] ) );
        }

        /**
         * Commercial
         *
         * Get our Parent term and add child terms
         */
        $commercial = term_exists( 'Commercial', $taxonomy );
        $commercial_terms = array( 'Annual Reports', 'Billboards', 'Brochures', 'Print Advertising', 'Product Advertising', 'Product Packaging', 'Public Relations', 'Web Advertising', 'Website' );
        foreach ( $commercial_terms as $term ) {
            wp_insert_term( $term, $taxonomy, array( 'slug' => strtolower(str_replace( ' ', '-', $term ) ), 'parent' => $commercial['term_id'] ) );
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

        $this->init();
        flush_rewrite_rules();


        // Update script to new settings
        if ( $version <= '1.0.1' ){
            include( dirname(__FILE__) . '/inc/admin-upgrade.php' );
        }


    } // end install();


    /**
     * Runs when the plugin is initialized
     */
    public function init() {

        $this->registerLicenses();
        $this->registerKeywords();
        $this->registerCity();
        $this->registerState();
        $this->registerCreator();
        $this->registerCollection();
        $this->registerItem();
        $this->registerPayment();
        $this->enqueueScripts();

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

    public function adminMenus(){

        $permission = 'manage_options';

        //add_submenu_page( 'edit.php?post_type=sell_media_item', __('Settings', 'sell_media'), __('Settings', 'sell_media'),  $permission, 'sell_media_settings', array( SellMediaSettings, 'plugin_options_tabs' ) );
        add_submenu_page( 'edit.php?post_type=sell_media_item', __('Payments', 'sell_media'), __('Payments', 'sell_media'),  $permission, 'sell_media_payments', 'sell_media_payments_callback_fn' );
        add_submenu_page( 'edit.php?post_type=sell_media_item', __('Extensions', 'sell_media'), __('Extensions', 'sell_media'),  $permission, 'sell_media_extensions', 'sell_media_extensions_callback_fn' );

        do_action( 'sell_media_menu_hook' );
    }


    /**
     * Register License Taxonomy
     *
     * @author Thad Allender
     */
    public function registerLicenses() {
        $labels = array(
            'name' => _x( 'Licenses', 'licenses' ),
            'singular_name' => _x( 'License', 'licenses' ),
            'search_items' => _x( 'Search Licenses', 'licenses' ),
            'popular_items' => _x( 'Popular Licenses', 'licenses' ),
            'all_items' => _x( 'All Licenses', 'licenses' ),
            'parent_item' => _x( 'Parent License', 'licenses' ),
            'parent_item_colon' => _x( 'Parent License:', 'licenses' ),
            'edit_item' => _x( 'Edit License', 'licenses' ),
            'update_item' => _x( 'Update License', 'licenses' ),
            'add_new_item' => _x( 'Add New License', 'licenses' ),
            'new_item_name' => _x( 'New License', 'licenses' ),
            'separate_items_with_commas' => _x( 'Separate licenses with commas', 'licenses' ),
            'add_or_remove_items' => _x( 'Add or remove Licenses', 'licenses' ),
            'choose_from_most_used' => _x( 'Choose from most used Licenses', 'licenses' ),
            'menu_name' => _x( 'Licenses', 'licenses' ),
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'show_in_nav_menus' => true,
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
    public function registerKeywords() {
        $labels = array(
            'name' => _x( 'Keywords', 'keywords' ),
            'singular_name' => _x( 'Keyword', 'keywords' ),
            'search_items' => _x( 'Search Keywords', 'keywords' ),
            'popular_items' => _x( 'Popular Keywords', 'keywords' ),
            'all_items' => _x( 'All Keywords', 'keywords' ),
            'parent_item' => _x( 'Parent Keyword', 'keywords' ),
            'parent_item_colon' => _x( 'Parent Keyword:', 'keywords' ),
            'edit_item' => _x( 'Edit Keyword', 'keywords' ),
            'update_item' => _x( 'Update Keyword', 'keywords' ),
            'add_new_item' => _x( 'Add New Keyword', 'keywords' ),
            'new_item_name' => _x( 'New Keyword', 'keywords' ),
            'separate_items_with_commas' => _x( 'Separate keywords with commas', 'keywords' ),
            'add_or_remove_items' => _x( 'Add or remove Keywords', 'keywords' ),
            'choose_from_most_used' => _x( 'Choose from most used Keywords', 'keywords' ),
            'menu_name' => _x( 'Keywords', 'keywords' ),
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'show_in_nav_menus' => true,
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
            'name' => _x( 'City', 'city' ),
            'singular_name' => _x( 'Keyword', 'city' ),
            'search_items' => _x( 'Search City', 'city' ),
            'popular_items' => _x( 'Popular City', 'city' ),
            'all_items' => _x( 'All City', 'city' ),
            'parent_item' => _x( 'Parent Keyword', 'city' ),
            'parent_item_colon' => _x( 'Parent Keyword:', 'city' ),
            'edit_item' => _x( 'Edit Keyword', 'city' ),
            'update_item' => _x( 'Update Keyword', 'city' ),
            'add_new_item' => _x( 'Add New Keyword', 'city' ),
            'new_item_name' => _x( 'New Keyword', 'city' ),
            'separate_items_with_commas' => _x( 'Separate city with commas', 'city' ),
            'add_or_remove_items' => _x( 'Add or remove City', 'city' ),
            'choose_from_most_used' => _x( 'Choose from most used City', 'city' ),
            'menu_name' => _x( 'City', 'city' ),
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
            'name' => _x( 'State', 'state' ),
            'singular_name' => _x( 'Keyword', 'state' ),
            'search_items' => _x( 'Search State', 'state' ),
            'popular_items' => _x( 'Popular State', 'state' ),
            'all_items' => _x( 'All State', 'state' ),
            'parent_item' => _x( 'Parent Keyword', 'state' ),
            'parent_item_colon' => _x( 'Parent Keyword:', 'state' ),
            'edit_item' => _x( 'Edit Keyword', 'state' ),
            'update_item' => _x( 'Update Keyword', 'state' ),
            'add_new_item' => _x( 'Add New Keyword', 'state' ),
            'new_item_name' => _x( 'New Keyword', 'state' ),
            'separate_items_with_commas' => _x( 'Separate state with commas', 'state' ),
            'add_or_remove_items' => _x( 'Add or remove State', 'state' ),
            'choose_from_most_used' => _x( 'Choose from most used State', 'state' ),
            'menu_name' => _x( 'State', 'state' ),
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
    public function registerCreator() {
        $labels = array(
            'name' => _x( 'Creator', 'creator' ),
            'singular_name' => _x( 'Keyword', 'creator' ),
            'search_items' => _x( 'Search Creator', 'creator' ),
            'popular_items' => _x( 'Popular Creator', 'creator' ),
            'all_items' => _x( 'All Creator', 'creator' ),
            'parent_item' => _x( 'Parent Keyword', 'creator' ),
            'parent_item_colon' => _x( 'Parent Keyword:', 'creator' ),
            'edit_item' => _x( 'Edit Keyword', 'creator' ),
            'update_item' => _x( 'Update Keyword', 'creator' ),
            'add_new_item' => _x( 'Add New Keyword', 'creator' ),
            'new_item_name' => _x( 'New Keyword', 'creator' ),
            'separate_items_with_commas' => _x( 'Separate creator with commas', 'creator' ),
            'add_or_remove_items' => _x( 'Add or remove Creator', 'creator' ),
            'choose_from_most_used' => _x( 'Choose from most used Creator', 'creator' ),
            'menu_name' => _x( 'Creator', 'creator' ),
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

        register_taxonomy( 'creator', array('sell_media_item'), $args );
    }


    /**
     * Register Collection Taxonomy
     *
     * @author Thad Allender
     */
    public function registerCollection() {
        $labels = array(
            'name' => _x( 'Collections', 'collection' ),
            'singular_name' => _x( 'Collection', 'collection' ),
            'search_items' => _x( 'Search Collection', 'collection' ),
            'popular_items' => _x( 'Popular Collection', 'collection' ),
            'all_items' => _x( 'All Collections', 'collection' ),
            'parent_item' => _x( 'Parent Collection', 'collection' ),
            'parent_item_colon' => _x( 'Parent Collection:', 'collection' ),
            'edit_item' => _x( 'Edit Collection', 'collection' ),
            'update_item' => _x( 'Update Collection', 'collection' ),
            'add_new_item' => _x( 'Add New Collection', 'collection' ),
            'new_item_name' => _x( 'New Collection', 'collection' ),
            'separate_items_with_commas' => _x( 'Separate collection with commas', 'collection' ),
            'add_or_remove_items' => _x( 'Add or remove Collection', 'collection' ),
            'choose_from_most_used' => _x( 'Choose from most used Collection', 'collection' ),
            'menu_name' => _x( 'Collections', 'collection' ),
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'show_in_nav_menus' => true,
            'show_ui' => true,
            'show_tagcloud' => true,
            'hierarchical' => true,
            'rewrite' => true,
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
            'name' => _x( 'Items', 'sell_media_item' ),
            'singular_name' => _x( 'Item', 'sell_media_item' ),
            'all_items' => _x( 'All Items', 'sell_media_item' ),
            'add_new' => _x( 'Add New', 'sell_media_item' ),
            'add_new_item' => _x( 'Add New Item', 'sell_media_item' ),
            'edit_item' => _x( 'Edit Item', 'sell_media_item' ),
            'new_item' => _x( 'New Item', 'sell_media_item' ),
            'view_item' => _x( 'View Item', 'sell_media_item' ),
            'search_items' => _x( 'Search Items', 'sell_media_item' ),
            'not_found' => _x( 'No items found', 'sell_media_item' ),
            'not_found_in_trash' => _x( 'No items found in Trash', 'sell_media_item' ),
            'parent_item_colon' => _x( 'Parent Item:', 'sell_media_item' ),
            'menu_name' => _x( 'Sell Media', 'sell_media_item' ),
        );

        $args = array(
            'labels' => $labels,
            'hierarchical' => true,
            'supports' => array( 'title' ),
            'taxonomies' => array( 'licenses', 'keywords', 'city', 'state', 'creator', 'collection' ),
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 10,
            'show_in_nav_menus' => true,
            'publicly_queryable' => true,
            'exclude_from_search' => false,
            'has_archive' => true,
            'query_var' => true,
            'can_export' => true,
            'rewrite' => array ( 'slug' => 'items' ),
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
            'name' => _x( 'Payments', 'sell_media_payment' ),
            'singular_name' => _x( 'Payment', 'sell_media_payment' ),
            'add_new' => _x( 'Add New', 'sell_media_payment' ),
            'add_new_item' => _x( 'Add New Payment', 'sell_media_payment' ),
            'edit_item' => _x( 'Edit Payment', 'sell_media_payment' ),
            'new_item' => _x( 'New Payment', 'sell_media_payment' ),
            'view_item' => _x( 'View Payment', 'sell_media_payment' ),
            'search_items' => _x( 'Search Payments', 'sell_media_payment' ),
            'not_found' => _x( 'No payments found', 'sell_media_payment' ),
            'not_found_in_trash' => _x( 'No payments found in Trash', 'sell_media_payment' ),
            'parent_item_colon' => _x( 'Parent Payment:', 'sell_media_payment' ),
            'menu_name' => _x( 'Payments', 'sell_media_payment' ),
        );

        $args = array(
            'labels' => $labels,
            'hierarchical' => false,
            'supports' => array( 'title' ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'publicly_queryable' => true,
            'has_archive' => false,
            'query_var' => true,
            'rewrite' => false,
            'capability_type' => 'post'
        );

        register_post_type( 'sell_media_payment', $args );
    }


    /**
     * Registers and enqueues stylesheets for the administration panel and the
     * public facing site.
     */
    private function enqueueScripts() {

        if ( is_admin() && sell_media_is_sell_media_post_type_page() ) {
            wp_enqueue_style( 'sell_media-admin', plugin_dir_url( __FILE__ ) . 'css/sell_media-admin.css', array( 'thickbox' ) );
            if ( sell_media_is_license_page() || sell_media_is_license_term_page() ) {
                wp_enqueue_script( 'sell_media-admin', plugin_dir_url( __FILE__ ) . 'js/sell_media-admin.js', array( 'jquery', 'jquery-ui-sortable' ) );
                wp_enqueue_script( 'jquery-ui-slider' );
            }
        } if ( !is_admin() ) {
            wp_enqueue_script( 'sell_media', plugin_dir_url( __FILE__ ) . 'js/sell_media.js', array( 'jquery' ) );
            wp_enqueue_style( 'sell_media', plugin_dir_url( __FILE__ ) . 'css/sell_media.css' );
        }
    }

} // end class

$a = new SellMedia();