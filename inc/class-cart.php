<?php

/**
 * This class acts as a wrapper for all methods related to manipulating
 * the cart, i.e. getting prices, markup values, etc.
 *
 * @author Zane M. Kolnik
 */
Class Sell_Media_Cart {

    /**
     * Derive price of an item via post meta
     * else derive from the price group
     * else use the default price from settings
     *
     * @param $post_id (int) The post_id to a sell_media_item
     * @param $price_id (int) One of the following: 'term_id' (of price-group taxonomy), 'sell_media_original_file', 'default_price'
     *
     * @return formated price without the currency symbolc
     */
    public function item_price( $post_id=null, $price_id=null ){

        $default_price_array = get_option('sell_media_size_settings');
        $custom_price = get_post_meta( $post_id, 'sell_media_price', true );

        if ( $price_id == 'sell_media_original_file' && ! empty( $custom_price ) ){
            $item_price = $custom_price;
        } elseif ( $price_id == 'sell_media_original_file' && empty( $custom_price ) ){
            $item_price = $default_price_array['default_price'];
        } elseif ( $price_id == 'default_price' && empty( $custom_price ) ){
            $item_price = $default_price_array['default_price'];
        } elseif ( $price_id == 'default_price' && ! empty( $custom_price ) ) {
            $item_price = $custom_price;
        } else {
            $item_price = sell_media_get_term_meta( $price_id, 'price', true );
        }

        $filtered_price = apply_filters( 'sell_media_filtered_price', $price_id );
        if ( $filtered_price != $price_id ){
            $item_price = $filtered_price;
        }

        return sprintf('%0.2f', $item_price);

    }


    /**
     * Determine the price of an item using the item price and markup value
     *
     * @param $post_id (int) Post ID of sell media item
     * @param $price_id (int) Term ID of price-group taxonomy
     * @param $license_id (int) Term ID of licenses taxonomy
     *
     * @return price of an item including the markup amount with no currency symbol
     */
    public function item_markup_total( $post_id=null, $price_id=null, $license_id=null ){
        return sprintf('%0.2f', $this->item_markup_amount( $post_id, $price_id, $license_id ) + $this->item_price( $post_id, $price_id ) );
    }


    /**
     * Determine the price of an item PLUS the markup
     *
     * @param $post_id (int) Post ID for a sell media item
     * @param $price_id (int) The term_id for a price of the price-group taxonomy
     * @param $license_id (int) The term_id for a price of the licenses taxonomy
     *
     * @return (float) returns the full markup amount without currency symbol
     */
    public function item_markup_amount( $post_id=null, $price_id=null, $license_id=null ){

        $price = $this->item_price( $post_id, $price_id );

        if ( empty( $license_id ) ){
            $markup_amount = 0;
        } else {
            $markup_percent = str_replace( "%", "", sell_media_get_term_meta( $license_id, 'markup', true ) );
            $markup_amount = ( $markup_percent / 100 ) * $price;
        }

        return $markup_amount;
    }


    /**
     * Determine the size name of an item based
     *
     * @param $term_id (int) The term_id for a term from the price-group taxonomy
     *
     * @return $size (string) The text to be displayed as the size of the item
     */
    public function item_size( $term_id=null ){
        if ( $term_id == 'sell_media_original_file' ){
            $size = __('Original','sell_media');
        } elseif( $term_id == 'default_price' ) {
            $size = __('N/A','sell_media');
        } else {
            $size_obj = get_term_by('id', $term_id, 'price-group' );
            // if for some reason this term does not exists
            if ( $size_obj )
                $size = $size_obj->name;
            else
                $size = null;
        }
        return $size;
    }
}