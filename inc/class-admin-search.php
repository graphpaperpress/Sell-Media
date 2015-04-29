<?php

/**
 * Admin Search
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

Class SellMediaAdminSearch {


    /**
     * Constructor
     */
    public function __construct(){

        add_filter( 'posts_where', array( &$this, 'posts_where' ) );

    }

    /**
     * Filter posts_where to search by ids.
     * Has ability to search one or many.
     * Separate ids with a comma.
     *
     * @since 1.0
     * @return array ids
     */
    public function posts_where( $where ) {

        if ( is_admin() && is_search() ) {
            $s = $_GET['s'];
            if ( ! empty( $s ) ) {
                global $wpdb;
                if ( is_numeric( $s ) ) {
                    $where = str_replace( '(' . $wpdb->posts . '.post_title LIKE', '(' . $wpdb->posts . '.ID = ' . $s . ') OR (' . $wpdb->posts . '.post_title LIKE', $where );
                } elseif( preg_match( "/^(\d+)(,\s*\d+)*\$/", $s ) ) { // a string of post ids
                    $where = str_replace( '(' . $wpdb->posts . '.post_title LIKE', '(' . $wpdb->posts . '.ID in (' . $s . ')) OR (' . $wpdb->posts . '.post_title LIKE', $where );
                }
            }
        }
        return $where;
    }

}