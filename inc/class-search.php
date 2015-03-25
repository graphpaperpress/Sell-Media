<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

Class SellMediaSearch {


    /**
     * Init
     */
    public function __construct(){
        add_filter( 'posts_join', array( &$this, 'search_join' ) );
        add_action( 'pre_get_posts', array( &$this, 'search_query' ) );
    }

    /**
     * Search join
     *
     *
     * @since 2.0
     */
    public function search_join( $join ) {
        global $wpdb;

        if ( ! is_admin() && ! empty( get_search_query() ) && isset( $_GET['post_type'] ) && 'sell_media_item' == $_GET['post_type'] ) {

            // if we're searching custom taxonomies
            $taxonomies = get_object_taxonomies( 'sell_media_item' );;

            foreach ( $taxonomies as $taxonomy ) {
                $on[] = "ttax.taxonomy = '" . addslashes( $taxonomy )."'";
            }
            // build our final string
            $on = ' ( ' . implode( ' OR ', $on ) . ' ) ';
            $join .= " LEFT JOIN $wpdb->term_relationships AS trel ON ($wpdb->posts.ID = trel.object_id) LEFT JOIN $wpdb->term_taxonomy AS ttax ON ( " . $on . " AND trel.term_taxonomy_id = ttax.term_taxonomy_id) LEFT JOIN $wpdb->terms AS tter ON (ttax.term_id = tter.term_id) ";
        }
        return $join;
    }

    /**
     * Search query
     *
     * @since 1.8.7
     */
    public function search_query( $query ) {

        if ( ! $query->is_search )
            return $query;

        if ( $query->get( 'post_type' ) && 'sell_media_item' == $query->get( 'post_type' ) ) {

            /**
             * Exclude password protected collections from search query
             */
            foreach( get_terms('collection') as $term_obj ){
                $password = sell_media_get_term_meta( $term_obj->term_id, 'collection_password', true );
                if ( $password ) $exclude_term_ids[] = $term_obj->term_id;
            }

            if ( ! empty( $exclude_term_ids ) ){
                $collection_querys = array(
                    'relation' => 'AND',
                    array(
                        'taxonomy' => 'collection',
                        'field' => 'id',
                        'terms' => $exclude_term_ids,
                        'operator' => 'NOT IN'
                    )
                );
                $query->set( 'tax_query', $collections_query );
            }

            return $query;

        }

    }


    /**
     * Search form
     *
     * @since 1.8.7
     */
    public function form( $url=null, $used=null ){

        // only use this method if it hasn't already been used on the page
        static $used;
        if ( ! isset( $used ) ) {
            $used = true;

            $html = '';
            $html .= '<div class="sell-media-search cf">';
            $html .= '<form role="search" method="get" id="sell-media-searchform" action="' . site_url() . '">';
            $html .= '<div class="sell-media-search-inner">';

            $html .= '<div class="sell-media-search-field">';
            $html .= '<label for="s">' . __( 'Search for', 'sell_media' ) . ':</label>';
            $html .= '<input type="text" value="" name="s" id="s" />';
            $html .= '</div>';

            $html .= '<div class="sell-media-search-inner">';
            $html .= '<label for="s">' . __( 'Exact phrase match', 'sell_media' ) . ':</label>';
            $html .= '<input type="checkbox" value="1" name="sentence" id="sentence" />';
            $html .= '</div>';

            $html .= '<div class="sell-media-search-inner">';
            $html .= '<label for="collection">' . __( 'Collection', 'sell_media' ) . ':</label>';
            $html .= '<select name="collection">';
            $html .= '<option value="">' . esc_attr( __( 'All' ) ) . '</option>';

            $categories = get_categories( 'taxonomy=collection' );
            foreach ( $categories as $category ) {
                $html .= '<option value="' . $category->category_nicename . '">';
                $html .= $category->cat_name;
                $html .= '</option>';
            }
            $html .= '</select>';
            $html .= '</div>';

            $html .= '<input type="hidden" name="post_type" value="sell_media_item" />';
            $html .= '<input type="submit" id="searchsubmit" value="' . __( 'Search', 'sell_media' ) . '" />';
            $html .= '</div>';
            $html .= '</form>';
            $html .= '</div>';

            echo apply_filters( 'sell_media_searchform_filter', $html );
        }
    }

}