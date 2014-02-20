<?php

class SellMediaImages extends SellMediaProducts {

    /**
     * Parse IPTC info and move the uploaded file into the protected area
     *
     * In order to "protect" our uploaded file, we resize the original
     * file down to the largest WordPress size set in Media Settings.
     * Then we take the uploaded file and move it to the "protected area".
     * Last, we copy (rename) our resized uploaded file to be the original
     * file.
     *
     * @param $attached_file As WordPress sees it in *postmeta table
     * "_wp_attached_file", i.e., YYYY/MM/file-name.ext
     * @since 1.0.1
     */
    public function move_image_from_attachment( $attachment_id=null ){

        $original_file = get_attached_file( $attachment_id );

        // Extract IPTC meta info from the uploaded image.
        $city = sell_media_iptc_parser( 'city', $original_file );
        $state = sell_media_iptc_parser( 'state', $original_file );
        $creator = sell_media_iptc_parser( 'creator', $original_file );
        $keywords = sell_media_iptc_parser( 'keywords', $original_file );

        global $post;
        $product_id = empty( $post->ID ) ? get_post_meta( $attachment_id, '_sell_media_for_sale_product_id', true ) : $post->ID;

        // Save IPTC info as taxonomies
        if ( ! empty( $product_id ) ) {
            if ( $city )
                sell_media_iptc_save( 'city', $city, $product_id );

            if ( $state )
                sell_media_iptc_save( 'state', $state, $product_id );

            if ( $creator )
                sell_media_iptc_save( 'creator', $creator, $product_id );

            if ( $keywords )
                sell_media_iptc_save( 'keywords', $keywords, $product_id );
        }


        // Assign the FULL PATH to our destination file.
        $wp_upload_dir = wp_upload_dir();

        $destination_file = $wp_upload_dir['basedir'] . SellMedia::upload_dir . $wp_upload_dir['subdir'] . '/' . basename( $original_file );
        $destination_dir  = $wp_upload_dir['basedir'] . SellMedia::upload_dir . $wp_upload_dir['subdir'] . '/';


        // Check if the destination directory exists, i.e.
        // wp-content/uploads/sell_media/YYYY/MM if not we create it.
        if ( ! file_exists( dirname( $destination_file ) ) ){
            wp_mkdir_p( dirname( $destination_file ) );
        }


        /**
         * Resize original file down to the largest size set in the Media Settings
         *
         * Determine which version of WP we are using.
         * Would rather check if the correct function exists
         * but the function 'image_make_intermediate_size' uses other
         * functions that are in trunk and not in 3.4
         */
        global $wp_version;
        if ( version_compare( $wp_version, '3.5', '>=' ) ){


            /**
             * Resize the "original" to our largest size set in the Media Settings.
             *
             * This creates a file named filename-[width]x[height].jpg
             * From here the "original" file is still in our uploads dir, its needed to create
             * the additional image sizes. Once we're done making the additional sizes, we rename
             * the filename-[width]x[height].jpg to filename.jpg, thus having a resized "original"
             * file.
             */
            $image_new_size = image_make_intermediate_size( $original_file, get_option('large_size_w'), get_option('large_size_h'), false );


            /**
             * If for some reason the image resize fails we just fall back to the original image.
             * Example, the image the user is trying to sell is smaller than our "max width".
             */
            if ( empty( $image_new_size ) ){
                $resized_image = $original_file;
                $keep_original = true;
            } else {
                $keep_original = false;
                $resized_image = $wp_upload_dir['path'] . '/' . $image_new_size['file'];
            }


            if ( ! file_exists( $destination_file ) ){

                /**
                 * Move our originally upload file into the protected area
                 */
                copy( $original_file, $destination_file );
                if ( ! $keep_original ) unlink( $original_file );

                /**
                 * We rename our resize original file i.e., "filename-[width]x[height].jpg" located in our uploads directory
                 * to "filename.jpg"
                 */
                $new_path_source = dirname( $original_file ) . '/' . basename( $resized_image );
                $new_path_destination = $original_file;
                copy( $new_path_source, $new_path_destination );
            }

        } else {

            $resized_image = image_resize( $original_file, get_option('large_size_w'), get_option('large_size_h'), false, null, $wp_upload_dir['path'], 90 );
            if ( ! file_exists( $destination_file ) ){
                // Copy original to our protected area
                @copy( $original_file, $destination_file );

                // Copy (rename) our resized image to the original
                @copy( $resized_image, dirname( $resized_image ) . '/' . basename( $original_file ) );
            }
        }
    }


    /**
    * Prints the original image resolution
    *
    * @param (int)$post_id The post_id to the sell media item
    * @since 1.2.4
    * @author Zane Matthew
    */
    public function get_original_image_size( $post_id=null ){
        // check if attachment is an image
        $attachment_id = get_post_meta( $post_id, '_sell_media_attachment_id', true );
        if ( $this->mimetype_is_image( $attachment_id ) ) {
            $original_size = wp_get_attachment_image_src( $attachment_id, 'full' );
            return array(
                'original'=> array(
                    'height' => $original_size[2],
                    'width' => $original_size[1]
                )
            );
        } else {
            return false;
        }
    }


    /**
     * Determines the available download sizes based on the current image width/height.
     * Note not ALL images are available in ALL download sizes.
     *
     * @since 1.2.4
     * @author Zane Matthew
     *
     * @return Prints an li or returns an array of available download sizes
     */
    public function image_sizes( $post_id=null, $echo=true ){

        $attachment_id = get_post_meta( $post_id, '_sell_media_attachment_id', true );

        if ( $this->mimetype_is_image( $attachment_id ) ){
            $download_sizes = $this->get_downloadable_size( $post_id );

            if ( $echo ){
                $html = null;
                foreach( $download_sizes as $k => $v ){
                    $html .= '<li class="price">here';
                    $html .= '<span class="title"> '.$download_sizes[ $k ]['name'].' (' . $download_sizes[ $k ]['width'] . ' x ' . $download_sizes[ $k ]['height'] . '): </span>';
                    $html .= sell_media_get_currency_symbol() . sprintf( '%0.2f', $download_sizes[ $k ]['price'] );
                    $html .= '</li>';
                }

                $settings = sell_media_get_plugin_options();
                if ( $settings->hide_original_price !== 'yes' ){

                    $original_size = $this->get_original_image_size( $post_id );

                    $html .= '<li class="price">';
                    $html .= '<span class="title">'.__( 'Original', 'sell_media' ) . ' (' . $original_size['original']['width'] . ' x ' . $original_size['original']['height'] . ')' . '</span>: ';
                    $html .= sell_media_item_price( $post_id, true, null, false );
                    $html .= '</li>';
                }

                print $html;
            } else {
                return $download_sizes;
            }
        } else {
            echo sell_media_item_price( $post_id, true, null, false );
        }
    }


    /**
     * @param $post_id (int) The post to a sell media item post type
     * @param $term_id (int) The term id for a term from the price-group taxonomy
     *
     * @return Array of downloadable sizes or single size if $term_id is present
     */
    public function get_downloadable_size( $post_id=null, $term_id=null, $size_not_available=null ){
        $attached_file = get_post_meta( $post_id, '_sell_media_attached_file', true );
        $wp_upload_dir = wp_upload_dir();
        $attached_path_file  = $wp_upload_dir['basedir'] . SellMedia::upload_dir . '/' . $attached_file;
        $parent_price_group = null;

        // Fix for legacy code?
        $attached_file_fix = file_exists( $attached_path_file );

        if ( ! $attached_file_fix ){
            @list( $broken, $url, $attached_file ) = explode( 'uploads/', $attached_path_file );
            $attached_path_file = $wp_upload_dir['basedir'] . SellMedia::upload_dir . '/' . $attached_file;
        }

        list( $orig_w, $orig_h, $type, $attr ) = @getimagesize( $attached_path_file );

        $null = null;
        $original = $download_sizes = array();
        list( $original['url'], $original['width'], $original['height'] ) = wp_get_attachment_image_src( get_post_meta( $post_id, '_sell_media_attachment_id', true ), 'full' );

        /**
         * Loop over price groups checking for children,
         * compare the width and height assigned to a price group
         * with the width and height of the current image. Remove
         * sizes that are not downloadable.
         */
        $price_groups = sell_media_get_price_groups( $post_id = $post_id, $taxonomy = 'price-group' );
        if ( ! empty( $price_groups ) ){
            foreach( $price_groups as $price ){

                /**
                 * Check for children only
                 */
                if ( $price->parent > 0 ){

                    /**
                     * Retrieve the height and width for our price group
                     */
                    $pg_width = sell_media_get_term_meta( $price->term_id, 'width', true );
                    $pg_height = sell_media_get_term_meta( $price->term_id, 'height', true );

                    $settings = sell_media_get_plugin_options();
                    $custom_price = get_post_meta( $post_id, 'sell_media_price', true );

                    /**
                     * Determine the price
                     */
                    if ( $price->term_id == 'sell_media_original_file' && ! empty( $custom_price ) ){
                        $item_price = $custom_price;
                    } elseif ( $price->term_id == 'sell_media_original_file' && empty( $custom_price ) ){
                        $item_price = $settings->default_price;
                    } elseif ( $price->term_id == 'default_price' && empty( $custom_price ) ){
                        $item_price = $settings->default_price;
                    } elseif ( $price->term_id == 'default_price' && ! empty( $custom_price ) ) {
                        $item_price = $custom_price;
                    } else {
                        $item_price = sell_media_get_term_meta( $price->term_id, 'price', true );
                    }

                    $filtered_price = apply_filters( 'sell_media_filtered_price', $price->term_id );
                    if ( $filtered_price != $price->term_id ){
                        $item_price = $filtered_price;
                    }


                    /**
                     * Build our array to be returned, the downloadable width and height
                     * are calculated later and added to this array
                     */
                    $download_sizes[ $price->term_id ] = array(
                        'name' => $price->name,
                        'price' => sprintf('%0.2f', $item_price)
                        );

                    /**
                     * Calculate dimensions and coordinates for a resized image that fits
                     * within a specified width and height. If $crop is true, the largest
                     * matching central portion of the image will be cropped out and resized
                     * to the required size.
                     */
                    list( $null, $null, $null, $null, $download_sizes[ $price->term_id ]['width'], $download_sizes[ $price->term_id ]['height'] ) = image_resize_dimensions( $orig_w, $orig_h, $pg_width, $pg_height, $crop=false );

                    /**
                     * If no width/height can be determined we remove it from our array of
                     * available download sizes.
                     */
                    if ( empty( $download_sizes[ $price->term_id ]['width'] ) ) {

                        $unavailable_size[ $price->term_id ] = array(
                            'name' => $download_sizes[ $price->term_id ]['name'],
                            'price' => $download_sizes[ $price->term_id ]['price'],
                            'height' => $pg_height,
                            'width' => $pg_width
                            );

                        unset( $download_sizes[ $price->term_id ] );
                    }

                    /**
                     * Check for portraits and if the available download size is larger than
                     * the original we remove it.
                     */
                    //////////
                    $terms = wp_get_post_terms( $post_id, 'price-group' );
                    $heights[] = '';
                    if ( ! empty( $terms ) ){
                        foreach( $terms as $term ){
                            if ( $term->parent != 0 ){
                                $height = sell_media_get_term_meta( $term->term_id, 'height', true );
                                $heights[] = $height;
                            }
                        }
                    }
                    $smallest_height = min( $heights );
                    //////////

                    if ( $original['height'] > $original['width']
                        && isset( $download_sizes[ $price->term_id ] )
                        && $download_sizes[ $price->term_id ]['height'] <  $smallest_height ){

                            $unavailable_size[ $price->term_id ] = array(
                                'name' => $download_sizes[ $price->term_id ]['name'],
                                'price' => $download_sizes[ $price->term_id ]['price'],
                                'height' => $pg_height,
                                'width' => $pg_width
                                );

                            unset( $download_sizes[ $price->term_id ] );
                    }
                }
            }
        }

        if ( ! empty( $size_not_available ) ){
            $sizes = array(
                'available' => $download_sizes,
                'unavailable' => empty( $unavailable_size ) ? null : $unavailable_size
                );
        } elseif ( empty( $term_id ) ) {
            $sizes = $download_sizes;
        } else {
            $sizes = $download_sizes[ $term_id ];
        }

        return $sizes;
    }
}