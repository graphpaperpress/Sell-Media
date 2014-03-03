<?php

Class SellMediaProducts {

    private $settings;

    public function __construct(){
        $this->settings = sell_media_get_plugin_options();
    }


    /**
     * Get all prices assigned to a product
     *
     * @return $prices (array)
     */
    public function get_prices( $post_id=null ){
        $i = 0;

        if ( $this->settings->hide_original_price !== 'yes' ){
            $images_obj = new SellMediaImages;
            $original_size = $images_obj->get_original_image_size( $post_id, false );
            $prices[$i]['id'] = 'original';
            $prices[$i]['name'] = __( 'Original', 'sell_media' );
            $prices[$i]['description'] = __( 'The original high resolution source file', 'sell_media' );
            $prices[$i]['price'] = $this->get_price( $post_id, 'original' );
            $prices[$i]['width'] = $original_size['original']['width'];
            $prices[$i]['height'] = $original_size['original']['height'];
        }

        if ( $this->mimetype_is_image( get_post_meta( $post_id, '_sell_media_attachment_id', true ) ) ){
            // check assigned price group. We're assuming there is just one.
            $term_parent = wp_get_post_terms( $post_id, 'price-group' );

            // if no assigned price group, get default from settings
            if ( empty( $term_parent ) ){
                $default = $this->settings->default_price_group;
                $terms = get_terms( 'price-group', array( 'hide_empty' => false, 'parent' => $default ) );
            } else {
                $terms = get_terms( 'price-group', array( 'hide_empty' => false, 'parent' => $term_parent[0]->term_id ) );
            }

            // loop over child terms
            foreach( $terms as $term ){
                if ( $term->parent != 0 ){
                    $i++;
                    $prices[$i]['id'] = $term->term_id;
                    $prices[$i]['name'] = $term->name;
                    $prices[$i]['description'] = $term->description;
                    $prices[$i]['price'] = sell_media_get_term_meta( $term->term_id, 'price', true );
                    $prices[$i]['width'] = sell_media_get_term_meta( $term->term_id, 'width', true );
                    $prices[$i]['height'] = sell_media_get_term_meta( $term->term_id, 'height', true );
                }
            }
        }

        return $prices;
    }



    /**
     * Determine the price for a single item based on the product_id and size_id
     *
     * @param (int)$product_id
     * @param (int)$price_id
     *
     * @return price on success false on failure
     */
    public function get_price( $product_id=null, $price_id=null, $formatted=false ){

        $final_price = false;

        // If this item has a price set use that
        $original_price = get_post_meta( $product_id, 'sell_media_price', true );

        if ( ! empty( $original_price )  ){
            echo "product id: {$product_id} price id: {$price_id}";
            $final_price = $original_price;
        }

        elseif ( ! empty( $price_id ) && $this->mimetype_is_image( get_post_meta( $product_id, '_sell_media_attachment_id', true ) )
            && $price_id != 'original' ){
            foreach( $this->get_prices( $product_id ) as $price ){
                if ( $price_id == $price['id'] ){
                    $final_price = $price['price'];
                }
            }
        }

        // Use default from setting
        else {
            $final_price = $this->settings->default_price;
        }

        $final_price = ( $formatted ) ? sell_media_get_currency_symbol() . sprintf( '%0.2f', $final_price ) : $final_price;

        return $final_price;
    }



    /**
     * Retrieves the lowest price available of an item from the price groups
     *
     * @param $post_id (int) The post_id, must be a post type of "sell_media_item"
     * @return string
     */
    public function get_lowest_price( $post_id=null ){
        $prices = $this->get_prices( $post_id );
        if ( $prices ) foreach ( $prices as $price ){
            $lowest_price[] = $price['price'];
        }

        return min( $lowest_price );
    }


    /**
     * Checks if the attachment ID is an image mime type
     *
     * @param $attachment_id ID of the attachment
     * @param $mimetype an array of mimetypes
     * @return boolean true/false
     * @since 1.6.9
     */
    public function mimetype_is_image( $post_id=null, $mimetypes=array( 'image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/tiff', 'image/icon' ) ){
        $attachment_mimetype = get_post_mime_type( $post_id );
        if ( in_array( $attachment_mimetype, $mimetypes ) ) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Get the protected image from the server
     *
     * @param (int) $post_id The post id to a sell media item
     * @return Returns the path to a protected image
     */
    public function protected_file( $post_id=null ){
        $attached_file = get_post_meta( $post_id, '_sell_media_attached_file', true );
        $wp_upload_dir = wp_upload_dir();
        $attached_path_file = $wp_upload_dir['basedir'] . SellMedia::upload_dir . '/' . $attached_file;
        return ( file_exists( $attached_file ) ) ? $attached_file : false;
    }
}