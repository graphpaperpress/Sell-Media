<?php

/*
Plugin Name: Sell Media
Plugin URI: http://graphpaperpress.com/plugins/sell-media
Description: A plugin for selling digital downloads and reprints.
Version: 1.4.2
Author: Graph Paper Press
Author URI: http://graphpaperpress.com
Author Email: support@graphpaperpress.com
License: GPL
*/

define( 'SELL_MEDIA_VERSION', '1.4.2' );
define( 'SELL_MEDIA_PLUGIN_FILE', plugin_dir_path(__FILE__) . 'sell-media.php' );

include( dirname(__FILE__) . '/inc/cart.php' );
include( dirname(__FILE__) . '/inc/downloads.php' );
include( dirname(__FILE__) . '/inc/helpers.php');
include( dirname(__FILE__) . '/inc/gateways/paypal.php' );
include( dirname(__FILE__) . '/inc/shortcodes.php' );
include( dirname(__FILE__) . '/inc/template-tags.php' );
include( dirname(__FILE__) . '/inc/term-meta.php' );
include( dirname(__FILE__) . '/inc/widgets.php' );

if ( is_admin() ) {
    include( dirname(__FILE__) . '/inc/admin-bulk.php' );
    include( dirname(__FILE__) . '/inc/admin-attachments.php' );
    include( dirname(__FILE__) . '/inc/admin-items.php' );
    include( dirname(__FILE__) . '/inc/admin-extensions.php' );
    include( dirname(__FILE__) . '/inc/admin-mime-types.php' );
    include( dirname(__FILE__) . '/inc/admin-payments.php' );
    include( dirname(__FILE__) . '/inc/admin-settings.php' );
}


/**
 * Screen Icon for Sell Media
 * Better place for this?
 */
function sell_media_screen_icon() {
    global $post_type;
    if ( ! empty( $_GET['post_type'] ) && 'sell_media_item' == $_GET['post_type'] || 'sell_media_item' == $post_type ) :
        print '<style type="text/css">#icon-edit { background:transparent url("' . plugin_dir_url( __FILE__ ) . '/images/sell_media_icon.png") no-repeat; }</style>';
    endif;
}
add_action( 'admin_head', 'sell_media_screen_icon' );


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
        if ( $version <= '1.0.4' ){
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

    /**
     * Add all menus under Sell Media. Settings are added on admin-settings.php
     *
     * @since 1.0
     */
    public function adminMenus(){

        $permission = 'manage_options';

        add_submenu_page( 'edit.php?post_type=sell_media_item', __('Add Bulk', 'sell_media'), __('Add Bulk', 'sell_media'),  $permission, 'sell_media_add_bulk', 'sell_media_add_bulk_callback_fn' );
        add_submenu_page( 'edit.php?post_type=sell_media_item', __('Payments', 'sell_media'), __('Payments', 'sell_media'),  $permission, 'sell_media_payments', 'sell_media_payments_callback_fn' );
        add_submenu_page( 'edit.php?post_type=sell_media_item', __('Reports', 'sell_media'), __('Reports', 'sell_media'),  $permission, 'sell_media_reports', 'sell_media_reports_callback_fn' );
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
            'name' => _x( 'Licenses', 'sell_media' ),
            'singular_name' => _x( 'License', 'sell_media' ),
            'search_items' => _x( 'Search Licenses', 'sell_media' ),
            'popular_items' => _x( 'Popular Licenses', 'sell_media' ),
            'all_items' => _x( 'All Licenses', 'sell_media' ),
            'parent_item' => _x( 'Parent License', 'sell_media' ),
            'parent_item_colon' => _x( 'Parent License:', 'sell_media' ),
            'edit_item' => _x( 'Edit License', 'sell_media' ),
            'update_item' => _x( 'Update License', 'sell_media' ),
            'add_new_item' => _x( 'Add New License', 'sell_media' ),
            'new_item_name' => _x( 'New License', 'sell_media' ),
            'separate_items_with_commas' => _x( 'Separate licenses with commas', 'sell_media' ),
            'add_or_remove_items' => _x( 'Add or remove Licenses', 'sell_media' ),
            'choose_from_most_used' => _x( 'Choose from most used Licenses', 'sell_media' ),
            'menu_name' => _x( 'Licenses', 'sell_media' ),
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
            'name' => _x( 'Keywords', 'sell_media' ),
            'singular_name' => _x( 'Keyword', 'sell_media' ),
            'search_items' => _x( 'Search Keywords', 'sell_media' ),
            'popular_items' => _x( 'Popular Keywords', 'sell_media' ),
            'all_items' => _x( 'All Keywords', 'sell_media' ),
            'parent_item' => _x( 'Parent Keyword', 'sell_media' ),
            'parent_item_colon' => _x( 'Parent Keyword:', 'sell_media' ),
            'edit_item' => _x( 'Edit Keyword', 'sell_media' ),
            'update_item' => _x( 'Update Keyword', 'sell_media' ),
            'add_new_item' => _x( 'Add New Keyword', 'sell_media' ),
            'new_item_name' => _x( 'New Keyword', 'sell_media' ),
            'separate_items_with_commas' => _x( 'Separate keywords with commas', 'sell_media' ),
            'add_or_remove_items' => _x( 'Add or remove Keywords', 'sell_media' ),
            'choose_from_most_used' => _x( 'Choose from most used Keywords', 'sell_media' ),
            'menu_name' => _x( 'Keywords', 'sell_media' ),
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
            'name' => _x( 'City', 'sell_media' ),
            'singular_name' => _x( 'Keyword', 'sell_media' ),
            'search_items' => _x( 'Search City', 'sell_media' ),
            'popular_items' => _x( 'Popular City', 'sell_media' ),
            'all_items' => _x( 'All City', 'sell_media' ),
            'parent_item' => _x( 'Parent Keyword', 'sell_media' ),
            'parent_item_colon' => _x( 'Parent Keyword:', 'sell_media' ),
            'edit_item' => _x( 'Edit Keyword', 'sell_media' ),
            'update_item' => _x( 'Update Keyword', 'sell_media' ),
            'add_new_item' => _x( 'Add New Keyword', 'sell_media' ),
            'new_item_name' => _x( 'New Keyword', 'sell_media' ),
            'separate_items_with_commas' => _x( 'Separate city with commas', 'sell_media' ),
            'add_or_remove_items' => _x( 'Add or remove City', 'sell_media' ),
            'choose_from_most_used' => _x( 'Choose from most used City', 'sell_media' ),
            'menu_name' => _x( 'City', 'sell_media' ),
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
            'name' => _x( 'State', 'sell_media' ),
            'singular_name' => _x( 'Keyword', 'sell_media' ),
            'search_items' => _x( 'Search State', 'sell_media' ),
            'popular_items' => _x( 'Popular State', 'sell_media' ),
            'all_items' => _x( 'All State', 'sell_media' ),
            'parent_item' => _x( 'Parent Keyword', 'sell_media' ),
            'parent_item_colon' => _x( 'Parent Keyword:', 'sell_media' ),
            'edit_item' => _x( 'Edit Keyword', 'sell_media' ),
            'update_item' => _x( 'Update Keyword', 'sell_media' ),
            'add_new_item' => _x( 'Add New Keyword', 'sell_media' ),
            'new_item_name' => _x( 'New Keyword', 'sell_media' ),
            'separate_items_with_commas' => _x( 'Separate state with commas', 'sell_media' ),
            'add_or_remove_items' => _x( 'Add or remove State', 'sell_media' ),
            'choose_from_most_used' => _x( 'Choose from most used State', 'sell_media' ),
            'menu_name' => _x( 'State', 'sell_media' ),
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
            'name' => _x( 'Creator', 'sell_media' ),
            'singular_name' => _x( 'Keyword', 'sell_media' ),
            'search_items' => _x( 'Search Creator', 'sell_media' ),
            'popular_items' => _x( 'Popular Creator', 'sell_media' ),
            'all_items' => _x( 'All Creator', 'sell_media' ),
            'parent_item' => _x( 'Parent Keyword', 'sell_media' ),
            'parent_item_colon' => _x( 'Parent Keyword:', 'sell_media' ),
            'edit_item' => _x( 'Edit Keyword', 'sell_media' ),
            'update_item' => _x( 'Update Keyword', 'sell_media' ),
            'add_new_item' => _x( 'Add New Keyword', 'sell_media' ),
            'new_item_name' => _x( 'New Keyword', 'sell_media' ),
            'separate_items_with_commas' => _x( 'Separate creator with commas', 'sell_media' ),
            'add_or_remove_items' => _x( 'Add or remove Creator', 'sell_media' ),
            'choose_from_most_used' => _x( 'Choose from most used Creator', 'sell_media' ),
            'menu_name' => _x( 'Creator', 'sell_media' ),
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
            'name' => _x( 'Collections', 'sell_media' ),
            'singular_name' => _x( 'Collection', 'sell_media' ),
            'search_items' => _x( 'Search Collection', 'sell_media' ),
            'popular_items' => _x( 'Popular Collection', 'sell_media' ),
            'all_items' => _x( 'All Collections', 'sell_media' ),
            'parent_item' => _x( 'Parent Collection', 'sell_media' ),
            'parent_item_colon' => _x( 'Parent Collection:', 'sell_media' ),
            'edit_item' => _x( 'Edit Collection', 'sell_media' ),
            'update_item' => _x( 'Update Collection', 'sell_media' ),
            'add_new_item' => _x( 'Add New Collection', 'sell_media' ),
            'new_item_name' => _x( 'New Collection', 'sell_media' ),
            'separate_items_with_commas' => _x( 'Separate collection with commas', 'sell_media' ),
            'add_or_remove_items' => _x( 'Add or remove Collection', 'sell_media' ),
            'choose_from_most_used' => _x( 'Choose from most used Collection', 'sell_media' ),
            'menu_name' => _x( 'Collections', 'sell_media' ),
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
            'name' => _x( 'Sell Media Items', 'sell_media' ),
            'singular_name' => _x( 'Sell Media Item', 'sell_media' ),
            'all_items' => _x( 'All Items', 'sell_media' ),
            'add_new' => _x( 'Add New', 'sell_media' ),
            'add_new_item' => _x( 'Sell Media', 'sell_media' ),
            'edit_item' => _x( 'Edit Item', 'sell_media' ),
            'new_item' => _x( 'New Item', 'sell_media' ),
            'view_item' => _x( 'View Item', 'sell_media' ),
            'search_items' => _x( 'Search Sell Media Items', 'sell_media' ),
            'not_found' => _x( 'No items found', 'sell_media' ),
            'not_found_in_trash' => _x( 'No items found in Trash', 'sell_media' ),
            'parent_item_colon' => _x( 'Parent Item:', 'sell_media' ),
            'menu_name' => _x( 'Sell Media', 'sell_media' ),
        );

        $general_settings = get_option( 'sell_media_general_settings' );

        $args = array(
            'labels' => $labels,
            'hierarchical' => true,
            'supports' => array( 'title', 'thumbnail' ),
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
            'rewrite' => array (
                'slug' => empty( $general_settings['post_type_slug'] ) ? 'items' : $general_settings['post_type_slug'],
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
            'name' => _x( 'Payments', 'sell_media' ),
            'singular_name' => _x( 'Payment', 'sell_media' ),
            'add_new' => _x( 'Add New', 'sell_media' ),
            'add_new_item' => _x( 'Add New Payment', 'sell_media' ),
            'edit_item' => _x( 'Edit Payment', 'sell_media' ),
            'new_item' => _x( 'New Payment', 'sell_media' ),
            'view_item' => _x( 'View Payment', 'sell_media' ),
            'search_items' => _x( 'Search Payments', 'sell_media' ),
            'not_found' => _x( 'No payments found', 'sell_media' ),
            'not_found_in_trash' => _x( 'No payments found in Trash', 'sell_media' ),
            'parent_item_colon' => _x( 'Parent Payment:', 'sell_media' ),
            'menu_name' => _x( 'Payments', 'sell_media' ),
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
     * Registers and enqueues stylesheets for the administration panel
     * and the public facing site.
     */
    private function enqueueScripts() {

        global $pagenow;

        /**
         * For easier enqueueing
         */
        wp_register_script( 'sell_media-admin-uploader', plugin_dir_url( __FILE__ ) . 'js/sell_media-admin-uploader.js', array( 'jquery', 'media-upload' ) );

        /**
         * For Sell All Uploads checkbox on media uploader
         */
        function sell_media_upload_popup_scripts() {
            wp_enqueue_script( 'sell_media-admin-uploader' );
        }
        add_action( 'admin_head-media-upload-popup', 'sell_media_upload_popup_scripts' );


        if ( $pagenow == 'media-new.php' ) {
            wp_enqueue_script( 'sell_media-admin-uploader' );
        }
        if ( is_admin() && ( sell_media_is_sell_media_post_type_page() || $pagenow == 'post.php' || $pagenow == 'post-new.php' ) ) {
            wp_enqueue_style( 'sell_media-admin', plugin_dir_url( __FILE__ ) . 'css/sell_media-admin.css', array( 'thickbox' ) );

            wp_enqueue_script( 'sell_media-admin-items', plugin_dir_url( __FILE__ ) . 'js/admin-items.js', array( 'jquery' ) );

            if ( sell_media_is_license_page() || sell_media_is_license_term_page() ) {
                wp_enqueue_script( 'sell_media-admin', plugin_dir_url( __FILE__ ) . 'js/sell_media-admin.js', array( 'jquery', 'jquery-ui-sortable' ) );
                wp_enqueue_script( 'jquery-ui-slider' );
            }
        } if ( !is_admin() ) {
            wp_enqueue_script( 'sell_media', plugin_dir_url( __FILE__ ) . 'js/sell_media.js', array( 'jquery' ) );
            wp_enqueue_style( 'sell_media', plugin_dir_url( __FILE__ ) . 'css/sell_media.css' );
        }
        if ( sell_media_is_reports_page() )
            wp_enqueue_script( 'google_charts', 'https://www.google.com/jsapi', array( 'jquery' ) );
    }

} // end class


$a = new SellMedia();