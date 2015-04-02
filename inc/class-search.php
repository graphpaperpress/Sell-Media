<?php

/**
 * Search Class
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

Class SellMediaSearch {

    private $query_instance;

    /**
     * Init
     */
    public function __construct(){
        add_filter( 'posts_join', array( &$this, 'terms_join' ) );
        add_filter( 'posts_search', array( &$this, 'search_where' ), 10, 2 );
        add_filter( 'posts_request', array( &$this, 'distinct' ) );
    }


    /**
     * Join for searching tags
     *
     * @since 1.8.7
     */
    public function terms_join( $join ) {
        global $wpdb;

        if ( ! empty( $this->query_instance->query_vars['s'] ) ) {

            // searching custom taxonomies
            $taxonomies = get_object_taxonomies( 'sell_media_item' );

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
     * Creates the list of search keywords from the 's' parameters
     *
     * @since 1.8.7
     */
    public function get_search_terms() {
        global $wpdb;
        $s = isset( $this->query_instance->query_vars['s'] ) ? $this->query_instance->query_vars['s'] : '';
        $sentence = isset( $this->query_instance->query_vars['sentence'] ) ? $this->query_instance->query_vars['sentence'] : false;
        $search_terms = array();

        if ( !empty( $s ) ) {
            // added slashes screw with quote grouping when done early, so done later
            $s = stripslashes( $s );
            if ( $sentence ) {
                $search_terms = array( $s );
            } else {
                preg_match_all( '/".*?("|$)|((?<=[\\s",+])|^)[^\\s",+]+/', $s, $matches );
                $search_terms = array_map( create_function( '$a', 'return trim($a, "\\"\'\\n\\r ");' ), $matches[0] );
            }
        }
        return $search_terms;
    }


    /**
     * Add where clause to the search query
     *
     * @since 1.8.7
     */
    public function search_where( $where, $wp_query ) {

        $this->query_instance = &$wp_query;
        global $wpdb;

        $searchQuery = $this->search_default();

        $searchQuery .= $this->build_search_categories();

        if ( $searchQuery != '' ) {
            $where = preg_replace( '#\(\(\(.*?\)\)\)#', '(('.$searchQuery.'))', $where );

        }
        return $where;
    }


    /**
     * Search for terms in default locations like title and content
     * replacing the old search terms seems to be the best way to
     * avoid issue with multiple terms
     *
     * @since 1.8.7
     */
    public function search_default(){
        global $wpdb;

        $not_exact = empty($this->query_instance->query_vars['exact']);
        $search_sql_query = '';
        $seperator = '';
        $terms = $this->get_search_terms();

        // if it's not a sentance add other terms
        $search_sql_query .= '(';
        foreach ( $terms as $term ) {
            $search_sql_query .= $seperator;

            $esc_term = esc_sql($term);
            if ($not_exact) {
                $esc_term = "%$esc_term%";
            }

            $like_title = "($wpdb->posts.post_title LIKE '$esc_term')";
            $like_post = "($wpdb->posts.post_content LIKE '$esc_term')";

            $search_sql_query .= "($like_title OR $like_post)";

            $seperator = ' AND ';
        }

        $search_sql_query .= ')';
        return $search_sql_query;
    }


    /**
     * Create the search categories query
     *
     * @since 1.8.7
     */
    public function build_search_categories() {
        global $wpdb;
        $vars = $this->query_instance->query_vars;

        $s = $vars['s'];
        $search_terms = $this->get_search_terms();
        $exact = isset( $vars['exact'] ) ? $vars['exact'] : '';
        $search = '';

        if ( ! empty( $search_terms ) ) {
            // Building search query for categories slug.
            $n = ( $exact ) ? '' : '%';
            $searchand = '';
            $searchSlug = '';
            foreach ( $search_terms as $term ) {
                $term = addslashes_gpc( $term );
                $searchSlug .= "{$searchand}(tter.slug LIKE '{$n}".sanitize_title_with_dashes( $term )."{$n}')";
                $searchand = ' AND ';
            }
            if ( count( $search_terms ) > 1 && $search_terms[0] != $s ) {
                $searchSlug = "($searchSlug) OR (tter.slug LIKE '{$n}".sanitize_title_with_dashes( $s )."{$n}')";
            }
            if ( ! empty( $searchSlug ) )
                $search = " OR ({$searchSlug}) ";

            // Building search query for categories description.
            $searchand = '';
            $searchDesc = '';
            foreach ( $search_terms as $term ) {
                $term = addslashes_gpc( $term );
                $searchDesc .= "{$searchand}(ttax.description LIKE '{$n}{$term}{$n}')";
                $searchand = ' AND ';
            }
            $sentence_term = esc_sql( $s );
            if ( count( $search_terms ) > 1 && $search_terms[0] != $sentence_term ) {
                $searchDesc = "($searchDesc) OR (ttax.description LIKE '{$n}{$sentence_term}{$n}')";
            }
            if ( ! empty( $searchDesc ) )
                $search = $search." OR ({$searchDesc}) ";
        }
        return $search;
    }


    /**
     * Duplicate posts fix
     *
     * @since 1.8.7
     */
    public function distinct( $query ) {
        global $wpdb;
        if ( ! empty( $this->query_instance->query_vars['s'] ) ) {
            if ( strstr( $query, 'DISTINCT' ) ) {}
            else {
                $query = str_replace( 'SELECT', 'SELECT DISTINCT', $query );
            }
        }
        return $query;
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

        $settings = sell_media_get_plugin_options();

        // only use this method if it hasn't already been used on the page
        static $used;
        if ( ! isset( $used ) ) {
            $used = true;

            $query = ( get_search_query() ) ? get_search_query() : '';

            $html = '';
            $html .= '<div class="sell-media-search">';
            $html .= '<form role="search" method="get" id="sell-media-search-form" class="sell-media-search-form" action="' . site_url() . '">';
            $html .= '<div class="sell-media-search-inner cf">';

            // Visible search options wrapper
            $html .= '<div id="sell-media-search-visible" class="sell-media-search-visible cf">';

            // Input field
            $html .= '<div id="sell-media-search-query" class="sell-media-search-field sell-media-search-query">';
            $html .= '<input type="text" value="' . $query . '" name="s" id="sell-media-search-text" class="sell-media-search-text" placeholder="' . apply_filters( 'sell_media_search_placeholder', sprintf( __( 'Search for %1$s', 'sell_media' ), empty( $settings->post_type_slug ) ? 'items' : $settings->post_type_slug ) ) . '"/>';
            $html .= '</div>';

            // Submit button
            $html .= '<div id="sell-media-search-submit" class="sell-media-search-field sell-media-search-submit">';
            $html .= '<input type="hidden" name="post_type" value="sell_media_item" />';
            $html .= '<input type="submit" id="sell-media-search-submit-button" class="sell-media-search-submit-button" value="' . apply_filters( 'sell_media_search_button', __( 'Search', 'sell_media' ) ) . '" />';
            $html .= '</div>';

            $html .= '</div>';

            // Hidden search options wrapper
            $html .= '<div id="sell-media-search-hidden" class="sell-media-search-hidden cf">';

            // Exact match field
            $html .= '<div id="sell-media-search-exact-match" class="sell-media-search-field sell-media-search-exact-match">';
            $html .= '<label for="sentence" id="sell-media-search-exact-match-desc" class="sell-media-search-exact-match-desc sell-media-tooltip" data-tooltip="Check to limit search results to exact phrase matches. Without exact phrase match checked, a search for \'New York Yankees\' would return results containing any of the three words \'New\', \'York\' and \'Yankees\'.">' . __( 'Exact phrase match (?)', 'sell_media' ) . '</label>';
            $html .= '<input type="checkbox" value="1" name="sentence" id="sentence" />';
            $html .= '</div>';

            // Collection field
            $html .= '<div id="sell-media-search-collection" class="sell-media-search-field sell-media-search-collection">';
            $html .= '<label for="collection">' . __( 'Collection', 'sell_media' ) . '</label>';
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

            // Hidden search options wrapper
            $html .= '</div>';

            // Close button
            $html .= '<a href="javascript:void(0);" class="sell-media-search-close">&times;</a>';

            $html .= '</div>';
            $html .= '</form>';
            $html .= '</div>';

            echo apply_filters( 'sell_media_searchform_filter', $html );
        }
    }

}
