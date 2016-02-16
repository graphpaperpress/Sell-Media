<?php

/**
 * Tax migrate Class
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

Class SellMediaTaxMetaMigrate {

    /**
     * Class constructer
     */
    public function __construct(){
        
    }

    /**
     * Run migration process
     */
    public function run(){
        $version = get_option( 'sell_media_version' );

        if ( $version && $version > SELL_MEDIA_VERSION )
            return;

        $metas = $this->get_all_meta();

        if( !empty( $metas ) ){
            foreach ($metas as $key => $meta) {
                update_term_meta ( (int) $meta->taxonomy_id, $meta->meta_key, $meta->meta_value );
            }
        }

        $this->delete_meta_table();
    }

    /**
     * Get all old metas
     */
    private function get_all_meta(){
        global $wpdb;
        $sql = 'SELECT * FROM `'.$wpdb->prefix.'taxonomymeta`';
        $metas = $wpdb->get_results( $sql );
        return $metas;
    }

    /**
     * Delete old meta table
     */
    private function delete_meta_table(){
        global $wpdb;
        $sql = 'DROP TABLE `'.$wpdb->prefix.'taxonomymeta`';
        return $wpdb->query( $sql );
    }

}