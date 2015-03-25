<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

Class SellMediaSearch {

    private $query_instance;

    /**
     * Init
     */
    public function __construct(){
        //add_filter( 'posts_join', array( &$this, 'search_join' ) );
        //add_action( 'pre_get_posts', array( &$this, 'search_query' ) );
        //add_filter( 'posts_request', array( &$this, 'se_distinct' ) );

        add_filter( 'posts_join', array( &$this, 'se_terms_join' ) );
        add_filter( 'posts_search', array( &$this, 'se_search_where' ), 10, 2 );
        add_filter( 'posts_request', array( &$this, 'se_distinct' ) );
    }



    //join for searching tags
    function se_terms_join( $join ) {
        global $wpdb;

        if ( ! empty( $this->query_instance->query_vars['s'] ) ) {

            // if we're searching custom taxonomies
            $all_taxonomies = get_taxonomies();
            $filter_taxonomies = array( 'post_tag', 'category', 'nav_menu', 'link_category' );

            foreach ( $all_taxonomies as $taxonomy ) {
                if ( in_array( $taxonomy, $filter_taxonomies ) )
                    continue;
                $on[] = "ttax.taxonomy = '" . addslashes( $taxonomy )."'";

            }
            // build our final string
            $on = ' ( ' . implode( ' OR ', $on ) . ' ) ';
            $join .= " LEFT JOIN $wpdb->term_relationships AS trel ON ($wpdb->posts.ID = trel.object_id) LEFT JOIN $wpdb->term_taxonomy AS ttax ON ( " . $on . " AND trel.term_taxonomy_id = ttax.term_taxonomy_id) LEFT JOIN $wpdb->terms AS tter ON (ttax.term_id = tter.term_id) ";
        }
        return $join;
    }


    // creates the list of search keywords from the 's' parameters.
    function se_get_search_terms() {
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

    // add where clause to the search query
    function se_search_where( $where, $wp_query ) {

        $this->query_instance = &$wp_query;
        global $wpdb;

        $searchQuery = $this->se_search_default();

        $searchQuery .= $this->se_build_search_categories();

        if ( $searchQuery != '' ) {
            $where = preg_replace( '#\(\(\(.*?\)\)\)#', '(('.$searchQuery.'))', $where );

        }

        return $where;
    }



    // search for terms in default locations like title and content
    // replacing the old search terms seems to be the best way to
    // avoid issue with multiple terms
    function se_search_default(){

        global $wpdb;

        $not_exact = empty($this->query_instance->query_vars['exact']);
        $search_sql_query = '';
        $seperator = '';
        $terms = $this->se_get_search_terms();

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


    // create the search categories query
    function se_build_search_categories() {
        global $wpdb;
        $vars = $this->query_instance->query_vars;

        $s = $vars['s'];
        $search_terms = $this->se_get_search_terms();
        $exact = isset( $vars['exact'] ) ? $vars['exact'] : '';
        $search = '';

        if ( !empty( $search_terms ) ) {
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
            if ( !empty( $searchSlug ) )
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
            if ( !empty( $searchDesc ) )
                $search = $search." OR ({$searchDesc}) ";
        }
        return $search;
    }

    //Duplicate posts fix
    function se_distinct( $query ) {
        global $wpdb;
        if ( !empty( $this->query_instance->query_vars['s'] ) ) {
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