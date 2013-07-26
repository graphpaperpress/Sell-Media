<?php

Class Sell_Media_Search {

    public $keyword_ids;


    public function __construct(){
        $this->keyword_ids = $this->get_ids('keywords');
        $this->collection_ids = $this->get_ids('collection');
    }


    /**
     * Filter the $_GET parameters trim white space, remove empty values,
     * remove html and replace un-wanted text
     *
     * @return An array of variables that are safe to use from $_GET
     */
    public function clean_get(){

        $clean_get = array();
        foreach( $_GET as $k => $v ){
            if ( ! empty( $v ) ){
                $clean_get[ str_replace('sell_media_', '', $k) ] = trim( wp_filter_nohtml_kses( $v ) );
            }
        }

        return $clean_get;
    }


    /**
     * Retrive the ID from the get method
     *
     * @param $taxonomy (string) The taxonomy to search for
     *
     * @return If we have the given key the value will be returned if not return false
     */
    private function get_ids( $taxonomy ){
        $clean_get = $this->clean_get();
        return isset( $clean_get[ $taxonomy ] ) ? $clean_get[ $taxonomy ] : false;
    }

}