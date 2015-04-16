<?php

/**
 * Products Class
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

Class SellMediaProducts {

    private $settings;

    public function __construct(){
        $this->settings = sell_media_get_plugin_options();
    }

    /**
     * Verify prices of products
     *
     * @return $prices (array)
     */
    public function verify_the_price( $product_id=null, $price_id=null ){

        // price group price
        $price_group_price = sell_media_get_term_meta( $price_id, 'price', true );
        $custom_price = get_post_meta( $product_id, 'sell_media_price', true );
        // check that the price_id exists and that the price meta is set
        if ( ! empty( $price_group_price ) ) {
            $price = $price_group_price;
        }
        // finally, set the price to the custom price
        elseif ( ! empty( $custom_price ) ) {
            $price = get_post_meta( $product_id, 'sell_media_price', true );
        } else {
            // set the default price from settings
            $settings = sell_media_get_plugin_options();
            $price = $settings->default_price;
        }
        return $price;
    }


    /**
     * Get all prices assigned to a product
     *
     * @return $prices (array)
     */
    public function get_prices( $post_id=null, $attachment_id=null, $taxonomy='price-group' ){
        $i = 0;

        if ( $this->settings->hide_original_price !== 'yes' ){
            $original_size = Sell_Media()->images->get_original_image_size( $attachment_id );
            $prices[$i]['id'] = 'original';
            $prices[$i]['name'] = __( 'Original', 'sell_media' );
            $prices[$i]['description'] = __( 'The original high resolution source file', 'sell_media' );
            $prices[$i]['price'] = $this->get_price( $post_id, 'original' );
            $prices[$i]['width'] = $original_size['original']['width'];
            $prices[$i]['height'] = $original_size['original']['height'];
        }

        if ( $this->has_image_attachments( $post_id ) ) {
            // check assigned price group. We're assuming there is just one.
            $term_parent = wp_get_post_terms( $post_id, $taxonomy );

            // if no assigned price group, get default from settings
            if ( empty( $term_parent ) || is_wp_error( $term_parent ) ){
                $default = $this->settings->default_price_group;
                $terms = get_terms( $taxonomy, array( 'hide_empty' => false, 'parent' => $default, 'orderby' => 'id' ) );
            } else {
                $terms = get_terms( $taxonomy, array( 'hide_empty' => false, 'parent' => $term_parent[0]->term_id, 'orderby' => 'id' ) );
            }

            // loop over child terms
            foreach( $terms as $term ){
                if ( ! empty( $term->term_id ) ){
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
    public function get_price( $product_id=null, $attachment_id=null, $price_id=null, $formatted=false, $taxonomy='price-group' ){

        $final_price = false;

        // Use custom price of item
        $original_price = get_post_meta( $product_id, 'sell_media_price', true );

        if ( ! empty( $original_price ) && ! empty( $price_id ) && $price_id == 'original' ){
            $final_price = $original_price;
        }

        // Use price group price
        elseif ( ! empty( $price_id ) &&  wp_attachment_is_image( $attachment_id ) && $price_id != 'original' ) {
            foreach( $this->get_prices( $product_id, $taxonomy ) as $price ){
                if ( $price_id == $price['id'] ){
                    $final_price = $price['price'];
                }
            }
        }

        // Use original price from settings
        else {
            $final_price = $this->settings->default_price;
        }

        $final_price = sprintf( '%0.2f', $final_price );
        $final_price = ( $formatted ) ? sell_media_get_currency_symbol() . $final_price : $final_price;

        return $final_price;
    }

    /**
     * Get the original price of an item
     *
     * @param $post_id (int) The post id
     * @return string (int)
     */
    public function get_original_price( $post_id=null ){

        $price = sprintf( '%0.2f', ( float ) get_post_meta( $post_id, 'sell_media_price', true ) );
        if ( $price )
            return $price;
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
        if ( is_array( $lowest_price ) ) {
            return min( $lowest_price );
        } else {
            return $lowest_price;
        }
    }

    /**
     * Check if item has an assigned price group
     *
     * @param $post_id (int) product id of post type "sell_media_item"
     * @return (bool) true/false
     */

    public function has_price_group( $post_id=null ){

        $taxonomies = array( 'price-group', 'reprints-price-group' );

        foreach ( $taxonomies as $taxonomy ) {
            if ( has_term( '', $taxonomy, $post_id ) ) {
                return true;
            }
        }
    }


    /**
     * Checks if the post has image attachments
     *
     * @param $attachment_id ID of the attachment
     * @param $mimetype an array of mimetypes
     * @return boolean true/false
     * @since 1.6.9
     */
    public function has_image_attachments( $post_id=null ){
        $attachment_ids = sell_media_get_attachments( $post_id );
        if ( $attachment_ids ) foreach ( $attachment_ids as $attachment_id ) {
            if ( wp_attachment_is_image( $attachment_id ) ) {
                return true;
            }
        }
    }


    /**
     * Get the protected image from the server
     *
     * @param (int) $post_id The post id to a sell media item
     * @param (int) $attachment_id The attachment id
     * @return Returns the path to a protected image
     */
    public function get_protected_file( $post_id=null, $attachment_id=null ){

        /**
         * When we upload items into Sell Media, we move the original file
         * into the protected wp-content/uploads/sell_media/* directory
         * and copy all of the intermediate image sizes into the regular
         * wp-content/uploads/* directory. Using get_attached_file()
         * will not return the original, high resolution file. It will return
         * only the largest publicly accessible (derived from Settings -> Media).
         * So, we now need to build the path protected sell_media directory.
         * Example (public): /var/www/wp.local/wp-content/uploads/2015/04/mansion.jpeg
         * Example (protected): /var/www/wp.local/wp-content/uploads/sell_media/2015/04/mansion.jpeg
         */

        $wp_upload_dir = wp_upload_dir();
        $protected_path = $wp_upload_dir['basedir'] . '/sell_media';

        // Full system file path to the public low resolution version.
        $unprotected_file_path = get_attached_file( $attachment_id );

        // Check if this item is a package and change the file location
        if ( sell_media_is_package( $post_id ) ) {
            $file = sell_media_get_package_filepath( $post_id );
        } else {
            $file = str_replace( $wp_upload_dir['basedir'], $protected_path, $unprotected_file_path );
        }

        return apply_filters( 'sell_media_get_original_protected_file', $file );
    }

    /**
     * Determine the markup amount.
     *
     * @param $post_id (int) Post ID for a sell media item
     * @param $price_id (int) The term_id for a price of the price-group taxonomy
     * @param $license_id (int) The term_id for a price of the licenses taxonomy
     *
     * @return (float) returns the full markup amount without currency symbol
     */
    public function markup_amount( $post_id=null, $price_id=null, $license_id=null ){

        $license_obj = get_term_by('id', $license_id, 'licenses');

        if ( empty( $license_obj ) ) {
            $markup_amount = 0;
        } else {
            $price = $this->verify_the_price( $post_id, $price_id );
            $markup_percent = str_replace( "%", "", sell_media_get_term_meta( $license_obj->term_id, 'markup', true ) );
            $markup_amount = ( $markup_percent / 100 ) * $price;
        }

        return $markup_amount;
    }

    /**
     * Determine if the item is a package
     *
     * @param $post_id (int) Post ID
     * @return true/false (boolean)
     */
    public function is_package( $post_id=null ){

        if ( get_post_meta( $post_id, '_sell_media_is_package', true ) )
            return true;
    }

}