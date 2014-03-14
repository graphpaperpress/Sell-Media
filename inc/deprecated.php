<?php

/**
 * Deprecated functions
 */
function sell_media_item_has_taxonomy_terms( $post_id=null, $taxonomy=null ) {
    return false;
}
function sell_media_country_list( $current=null, $req=false ){
    return false;
}
function sell_media_countries_list(){
    return false;
}
function sell_media_us_states_list( $current=null, $req=false ){
    return false;
}
function sell_media_item_min_price( $post_id=null, $echo=true, $key='price' ){

    $p = new SellMediaProducts;
    $price = $p->get_lowest_price( $post_id );

    if ( $echo ){
        echo $price;
    } else {
        return $price;
    }
}