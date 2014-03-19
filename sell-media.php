<?php

/**
 * Plugin Name: Sell Media
 * Plugin URI: http://graphpaperpress.com/plugins/sell-media/
 * Description: A plugin for selling digital downloads and reprints.
 * Version: 1.8.4
 * Author: Graph Paper Press
 * Author URI: http://graphpaperpress.com
 * Author Email: support@graphpaperpress.com
 * Text Domain: sell_media
 * Domain Path: languages
 *
 * Sell Media is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Sell Media is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Sell Media. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package SellMedia
 * @category Core
 * @author Thad Allender
 * @version 1.8.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'SellMedia' ) ) :

/**
 * Main SellMedia Class (singleton)
 *
 * @since 1.8.5
 */
final class SellMedia {

    /**
     * @var SellMedia There's only one instance
     * @since 1.8.5
     */
    private static $instance;

    /**
     * sell_media Customer Object
     *
     * @var object
     * @since 1.8.5
     */
    public $customer;

    /**
     * sell_media Download Object
     *
     * @var object
     * @since 1.8.5
     */
    public $download;

    /**
     * sell_media Images Object
     *
     * @var object
     * @since 1.8.5
     */
    public $images;

    /**
     * sell_media Payments Object
     *
     * @var object
     * @since 1.8.5
     */
    public $payments;

    /**
     * sell_media Products Object
     *
     * @var object
     * @since 1.8.5
     */
    public $products;

    /**
     * sell_media Search Object
     *
     * @var object
     * @since 1.8.5
     */
    public $search;


    /**
     * Main SellMedia Instance
     *
     * Ensures that only one instance of SellMedia exists in memory.
     *
     * @since 1.8.5
     * @static
     * @staticvar array $instance
     * @uses SellMedia::constants() Setup the constants needed
     * @uses SellMedia::includes() Include the required files
     * @see SellMedia()
     * @return The one, the only SellMedia
     */
    
    public static function instance() {
        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof SellMedia ) ) {
            self::$instance = new SellMedia;
            self::$instance->constants();
            self::$instance->includes();
            //self::$instance->flush();
            self::$instance->textdomain();
            self::$instance->customer       = new SellMediaCustomer();
            self::$instance->download       = new SellMediaDownload();
            self::$instance->images         = new SellMediaImages();
            self::$instance->payments       = new SellMediaPayments();
            self::$instance->products       = new SellMediaProducts();
            self::$instance->search         = new SellMediaSearch();
        }
        return self::$instance;
    }

    /**
     * Throw error on object clone
     *
     * The whole idea of the singleton design pattern is that there is a single
     * object therefore, we don't want the object to be cloned.
     *
     * @since 1.8.5
     * @access protected
     * @return void
     */
    public function __clone() {
        // Cloning instances of the class is forbidden
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'sell_media' ), '1.8.5' );
    }

    /**
     * Disable unserializing of the class
     *
     * @since 1.8.5
     * @access protected
     * @return void
     */
    public function __wakeup() {
        // Unserializing instances of the class is forbidden
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'sell_media' ), '1.8.5' );
    }

    /**
     * Constants
     *
     * @access private
     * @since 1.8.5
     * @return void
     */
    private function constants() {
        
        // Plugin version
        if ( ! defined( 'SELL_MEDIA_VERSION' ) ) {
            define( 'SELL_MEDIA_VERSION', '1.8.5' );
        }

        // Plugin Folder Path
        if ( ! defined( 'SELL_MEDIA_PLUGIN_DIR' ) ) {
            define( 'SELL_MEDIA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
        }

        // Plugin Folder URL
        if ( ! defined( 'SELL_MEDIA_PLUGIN_URL' ) ) {
            define( 'SELL_MEDIA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
        }

        // Plugin Root File
        if ( ! defined( 'SELL_MEDIA_PLUGIN_FILE' ) ) {
            define( 'SELL_MEDIA_PLUGIN_FILE', __FILE__ );
        }

    }

    /**
     * Include required files
     *
     * @access private
     * @since 1.8.5
     * @return void
     */
    private function includes() {

        require_once SELL_MEDIA_PLUGIN_DIR . '/settings/settings.php';
        require_once SELL_MEDIA_PLUGIN_DIR . 'sell-media-settings.php';
        require_once SELL_MEDIA_PLUGIN_DIR . '/inc/class-customer.php';
        require_once SELL_MEDIA_PLUGIN_DIR . '/inc/class-downloads.php';
        require_once SELL_MEDIA_PLUGIN_DIR . '/inc/class-payments.php';
        require_once SELL_MEDIA_PLUGIN_DIR . '/inc/class-products.php';
        require_once SELL_MEDIA_PLUGIN_DIR . '/inc/class-products-images.php';
        require_once SELL_MEDIA_PLUGIN_DIR . '/inc/class-search.php';
        require_once SELL_MEDIA_PLUGIN_DIR . '/inc/collections.php';
        require_once SELL_MEDIA_PLUGIN_DIR . '/inc/deprecated.php';
        require_once SELL_MEDIA_PLUGIN_DIR . '/inc/helpers.php';
        require_once SELL_MEDIA_PLUGIN_DIR . '/inc/gateways/paypal.php';
        require_once SELL_MEDIA_PLUGIN_DIR . '/inc/scripts.php';
        require_once SELL_MEDIA_PLUGIN_DIR . '/inc/shortcodes.php';
        require_once SELL_MEDIA_PLUGIN_DIR . '/inc/template-tags.php';
        require_once SELL_MEDIA_PLUGIN_DIR . '/inc/term-meta.php';
        require_once SELL_MEDIA_PLUGIN_DIR . '/inc/widgets.php';

        if ( is_admin() ) {
            
            require_once SELL_MEDIA_PLUGIN_DIR . '/inc/admin-items.php';
            require_once SELL_MEDIA_PLUGIN_DIR . '/inc/admin-items-bulk.php';
            //require_once SELL_MEDIA_PLUGIN_DIR . '/inc/admin-items-package.php';
            require_once SELL_MEDIA_PLUGIN_DIR . '/inc/admin-extensions.php';
            require_once SELL_MEDIA_PLUGIN_DIR . '/inc/admin-menu.php';
            require_once SELL_MEDIA_PLUGIN_DIR . '/inc/admin-notices.php';
            require_once SELL_MEDIA_PLUGIN_DIR . '/inc/admin-payments.php';
            require_once SELL_MEDIA_PLUGIN_DIR . '/inc/admin-price-groups.php';
            require_once SELL_MEDIA_PLUGIN_DIR . '/inc/admin-scripts.php';
            
        }

        require_once SELL_MEDIA_PLUGIN_DIR . '/inc/install.php';

    }

    /**
     * Flush permalinks every time plugin version number is updated
     * Do not generate any output here!
     *
     * @since 1.8.5
     */
    public function flush(){

        $version = get_option( 'sell_media_version' );

        if ( $version < SELL_MEDIA_VERSION ) {
            global $wp_rewrite;
            $wp_rewrite->flush_rules();
        }
    }

    /**
     * Loads the plugin language files
     *
     * @access public
     * @since 1.8.5
     * @return void
     */
    public function textdomain() {
        load_plugin_textdomain( 'sell_media', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

} // end SellMedia class

endif; // End if class_exists check

/**
 * The main function that returns the one and only SellMedia instance
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $sell_media = Sell_Media(); ?>
 *
 * @since 1.8.5
 * @return object The one true SellMedia instance
 */
function Sell_Media() {
    return SellMedia::instance();
}

// Start Sell Media
Sell_Media();