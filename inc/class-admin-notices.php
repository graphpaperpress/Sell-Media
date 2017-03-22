<?php

/**
 * Admin Notices
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

Class SellMediaAdminNotices {

    private $settings;

    /**
     * Constructor
     */
    public function __construct(){

        $this->settings = sell_media_get_plugin_options();

        add_action( 'admin_notices', array( &$this, 'admin_notices' ) );
        add_action( 'set_site_transient_update_plugins', array( &$this, 'delete_transients' ) );

    }

    /**
     * Admin notices
     *
     * @since 1.0
     * @return array admin notices
     */
    public function admin_notices() {

        $screen = get_current_screen();

        if ( $screen->id == 'edit-sell_media_item' || $screen->id == 'sell_media_item' ||  $screen->id == 'sell_media_item_page_sell_media_plugin_options' ) {

            $notices = array();

            /**
             * Test mode
             */
            if ( isset( $this->settings->test_mode ) && $this->settings->test_mode == 1 ){
                $notices[] = array(
                    'slug' => 'test-mode',
                    'message' => sprintf( __( 'Your site is currently in <a href="%1$s">test mode</a>.', 'sell_media' ), esc_url( admin_url( 'edit.php?post_type=sell_media_item&page=sell_media_plugin_options&tab=sell_media_general_settings' ) ) )
                );
            }

            /**
             * PayPal email
             */
            if ( empty( $this->settings->paypal_email ) ){
                $notices[] = array(
                    'slug' => 'paypal-email',
                    'message' => sprintf( __( 'Please set a PayPal email in your <a href="%1$s">payment settings</a>.', 'sell_media' ), esc_url( admin_url( 'edit.php?post_type=sell_media_item&page=sell_media_plugin_options&tab=sell_media_payment_settings' ) ) )
                );
            }

            /**
             * Updates availalble
             */
            if ( $this->get_updates() ) {
                $notices[] = array(
                    'slug' => 'sell-media-updates',
                    'message' => sprintf( __( 'Important updates are now available for %1$s. <a href="%2$s" target="_blank">Learn more</a>.', 'sell_media' ), implode( ', ', $this->get_updates() ), 'http://graphpaperpress.com/docs/sell-media/#updates' )
                );
            }

            $notices = apply_filters( 'sell_media_admin_notices', $notices );

            foreach( $notices as $notice ){
                add_settings_error( 'sell-media-notices', 'sell-media-notice-' . $notice['slug'], $notice['message'], 'updated' );
            }

            settings_errors( 'sell-media-notices' );
        }
    }


    /**
     * Get latest versions of all extensions every day
     * and store them in a transient.
     *
     * @since 2.0.3
     * @return array newest plugins from transient cache
     */
    public function get_newest_plugins() {

        // get data from transient if it's set
        if ( false === ( $cache = get_transient( 'sell_media_get_newest_plugins' ) ) ) {
            $json = wp_remote_get( 'http://demo.graphpaperpress.com/wp-content/plugins/gpp-deployment/products.json', array( 'sslverify' => false ) );
            if ( ! is_wp_error( $json ) ) {
                if ( isset( $json['body'] ) && strlen( $json['body'] ) > 0 ) {
                    $cache = wp_remote_retrieve_body( $json );
                    set_transient( 'sell_media_get_newest_plugins', $cache, 3600 );
                }
            }
        }
        return maybe_unserialize( $cache );
    }

    /**
     * Get all installed Sell Media plugin versions and store them in a transient.
     *
     * @since 2.0.3
     * @return array installed plugins from transient cache
     */
    public function get_installed_plugins() {

        // get data from transient if it's set
        if ( false === ( $cache = get_transient( 'sell_media_get_installed_plugins' ) ) ) {
            $plugins = get_plugins();
            $cache = array();

            if ( $plugins ) foreach ( array_keys( $plugins ) as $plugin ) {
                if ( preg_match( '/sell-media-(.*)$/', $plugin, $matches ) ) {
                    $cache[$plugin] = $plugins[$plugin]['Version'];
                }
            }
            set_transient( 'sell_media_get_installed_plugins', $cache, 3600 );
        }
        return maybe_unserialize( $cache );
    }

    /**
     * Compare latest versions available with user's installed versions
     *
     * @since 2.0.3
     * @return array plugins have updates available
     */
    private function compare_versions() {

        $installed_plugins = $this->get_installed_plugins();
        $newest_plugins = $this->get_newest_plugins();
        $update_plugins = array();

        foreach ( $installed_plugins as $key => $value ) {
            // if the plugin exists in available update cache and the installed version is outdated, add to updates array
            if ( ! empty( $newest_plugins[$key] ) && $value < $newest_plugins[$key] ) {
                $update_plugins[] = $key;
            }
        }

        return $update_plugins;

    }

    /**
     * Return the names of available theme updates.
     * Derive plugin nice names since get_plugins is expensive.
     *
     * @since 2.0.3
     * @return array plugin names with available updates
     */
    private function get_updates() {

        $plugins = $this->compare_versions();
        $plugin_names = array();

        if ( $plugins ) foreach ( $plugins as $plugin ) {
            $dir = substr( $plugin, 0, strpos( $plugin, '/' ) );
            $plugin_names[]= ucwords( str_replace( '-', ' ', $dir ) );
        }

        return $plugin_names;

    }

    /**
     * Delete transients if plugins are updated
     *
     * @since 2.0.3
     * @return void
     */
    public function delete_transients(){
        delete_transient( 'sell_media_get_installed_plugins' );
        delete_transient( 'sell_media_get_newest_plugins' );
    }
}