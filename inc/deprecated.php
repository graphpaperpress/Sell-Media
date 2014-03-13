<?php

/**
 * Deprecated functions
 */
function sell_media_item_min_price( $post_id=null, $echo=true, $key='price' ){

    $p = new SellMediaProducts;
    $price = $p->get_lowest_price( $post_id );

    if ( $echo ){
        echo $price;
    } else {
        return $price;
    }
}

/**
 * Deprecated functions
 */
function sell_media_item_has_taxonomy_terms( $post_id=null, $taxonomy=null ) {
    return false;
}