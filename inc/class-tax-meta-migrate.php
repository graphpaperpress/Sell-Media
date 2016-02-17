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
        $version = sell_media_version();

        if ( $version && $version > SELL_MEDIA_VERSION )
            return;

        $metas = $this->get_all_meta();
        $drop_table = true;

        if( !empty( $metas ) ){
            foreach ($metas as $key => $meta) {
                $result = update_term_meta ( (int) $meta->taxonomy_id, $meta->meta_key, $meta->meta_value );

                if( is_wp_error( $result ) || false === $result ){
                    $drop_table = false;
                }
            }
        }

        if( $drop_table ){
            $this->delete_meta_table();
        }
    }

    /**
     * Get old tax metas.
     * @return object Old tax metas
     */
    private function get_all_meta(){
        global $wpdb;
        $sql = 'SELECT * FROM `'.$wpdb->prefix.'taxonomymeta`';
        $metas = $wpdb->get_results( $sql );
        return $metas;
    }

    /**
     * Delete old meta table.
     * @return mixed Query output.
     */
    private function delete_meta_table(){
        global $wpdb;
        $sql = 'DROP TABLE `'.$wpdb->prefix.'taxonomymeta`';
        return $wpdb->query( $sql );
    }

}