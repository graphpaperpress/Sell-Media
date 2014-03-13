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
     * @param $post_id (int) The post to a sell media item post type
     * @param $term_id (int) The term id for a term from the price-group taxonomy
     * @param $size_not_available (bool) If true returns and array of unavailable sizes
     *
     * @return Array of downloadable sizes or single size if $term_id is present
     */
    public function get_downloadable_size( $post_id=null, $term_id=null, $size_not_available=false ){

        $null = null;
        $download_sizes = array();

        /**
         * Loop over price groups checking for children,
         * compare the width and height assigned to a price group
         * with the width and height of the current image. Remove
         * sizes that are not downloadable.
         */
        $size_groups = sell_media_get_price_groups( $post_id, 'price-group' );
        if ( ! empty( $size_groups ) ){

            $image = $this->get_original_image_size( $post_id );

            foreach( $size_groups as $size ){

                /**
                 * Check for children only
                 */
                if ( $size->parent > 0 ){

                    /**
                     * Retrieve the height and width for our price group
                     */
                    $pg_width = sell_media_get_term_meta( $size->term_id, 'width', true );
                    $pg_height = sell_media_get_term_meta( $size->term_id, 'height', true );

                    /**
                     * Build our array to be returned, the downloadable width and height
                     * are calculated later and added to this array
                     */
                    $download_sizes[ $size->term_id ] = array(
                        'name' => $size->name
                        );


                    /**
                     * Calculate dimensions and coordinates for a resized image that fits
                     * within a specified width and height. If $crop is true, the largest
                     * matching central portion of the image will be cropped out and resized
                     * to the required size.
                     *
                     * Note we need to pass in $null due to what image_resize_dimensions() returns
                     */
                    list(
                        $null,
                        $null,
                        $null,
                        $null,
                        $download_sizes[ $size->term_id ]['width'],
                        $download_sizes[ $size->term_id ]['height']
                        ) = image_resize_dimensions(
                            $image['original']['width'],
                            $image['original']['height'],
                            $pg_width,
                            $pg_height,
                            $crop=false
                            );

                    /**
                     * If no width/height can be determined we remove it from our array of
                     * available download sizes.
                     */
                    if ( empty( $download_sizes[ $size->term_id ]['width'] ) ) {
                        $unavailable_size[ $size->term_id ] = array(
                            'name' => $download_sizes[ $size->term_id ]['name'],
                            'height' => $pg_height,
                            'width' => $pg_width
                            );
                        unset( $download_sizes[ $size->term_id ] );
                    }


                    /**
                     * Check for portraits and if the available download size is larger than
                     * the original we remove it.
                     */
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


                    /**
                     * Compare the original image size with our array of images sizes from
                     * Price Groups array, removing items that are not available.
                     */
                    if ( $image['original']['height'] > $image['original']['width']
                        && isset( $download_sizes[ $size->term_id ] )
                        && $download_sizes[ $size->term_id ]['height'] < $smallest_height ){
                            $unavailable_size[ $size->term_id ] = array(
                                'name' => $download_sizes[ $size->term_id ]['name'],
                                'price' => $download_sizes[ $size->term_id ]['price'],
                                'height' => $pg_height,
                                'width' => $pg_width
                                );
                            unset( $download_sizes[ $price->term_id ] );
                    }
                }
            }
        }

        // Returns an array of available and unavailable sizes
        if ( $size_not_available ){
            $sizes = array(
                'available' => $download_sizes,
                'unavailable' => empty( $unavailable_size ) ? null : $unavailable_size
                );
        }

        // return all available sizes
        elseif ( empty( $term_id ) ) {
            $sizes = $download_sizes;
        }

        // return available size for a given product
        else {
            // Since we no longer check if the image sold is available in the download sizes
            // we allow the buyer to download the original image if the size they purchased
            // is larger than the original image i.e., they can purchase a size they can never
            // download.
            //
            // Hence if they paid for the original, OR they paid for a larger image than
            // available they get the original image.
            $sizes = empty( $download_sizes[ $term_id ] ) ? 'original' : $download_sizes[ $term_id ];
        }

        return $sizes;
    }
}