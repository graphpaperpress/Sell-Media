<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

Class SellMediaSearch {

    /**
     * Init
     */
    public function __construct(){
        //add_action( 'pre_get_posts', array( &$this, 'get_orientation' ) );
        $this->includes();
    }

    /**
     * Include WP Advanced Search class files
     */
    private function includes(){
        require_once SELL_MEDIA_PLUGIN_DIR . '/inc/search/wpas.php';
    }


    /**
     * Filters search results based on aspect ratio
     *
     * @param $posts (int)
     * @param $orientation (string) any|landscape|portrait
     *
     * @return Array of post IDs that are either landscape or portrait
     */
    public function get_orientation( $query ){

        if ( ! is_admin() && ! empty( $_GET['wpas'] ) && ( $_GET['orientation'] == 'landscape' || $_GET['orientation'] == 'portrait' ) ) {

            $orientation = get_query_var( 'orientation' );
            $post_ids = Sell_Media()->images->get_posts_by_orientation( $orientation );
            $query->set( 'post__in', $post_ids );
            
        }
    }


}