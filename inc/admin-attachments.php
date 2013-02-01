<?php

/**
 * Filter: Add Sell option to media uploader
 *
 * @param $form_fields array, fields to include in attachment form
 * @param $post object, attachment record in database
 * @return $form_fields, modified form fields
 * @author Thad Allender
 * @uses add_filter( 'attachment_fields_to_edit', 'sell_media_attachment_fields_to_edit', 10, 2 );
 * @since 0.1
 */
function sell_media_attachment_fields_to_edit( $form_fields, $post ) {

    $image_meta_a = wp_get_attachment_metadata( $post->ID );
    $upload_url_a = wp_upload_dir();

    if ( empty( $image_meta_a ) ){
        $att_arr = explode( 'uploads/', $post->guid );
        $file = $att_arr[1];
    } else {
        $file = $image_meta_a['file'];
    }
    $dir = $upload_url_a['baseurl'];

    $sell = (bool) get_post_meta($post->ID, '_sell_media_for_sale', true);

    $form_fields['sell'] = array(
        'label' => __( 'Sell This', 'sell_media' ),
        'input' => 'html',
        'html' => '<input type="checkbox" class="sell-this-checkox" id="attachments-'.$post->ID.'-sell" name="attachments['.$post->ID.'][sell]" value="1"'.($sell ? ' checked="checked"' : '').' />',
        'value' => $sell
        // 'helps' => __('If you select yes, this image will be added as a Product entry. You can modify the price and available licenses on the Products -> Edit Products tab. By default, the newly created Product will inherit the prices and licenses that you chose on the settings page.'), 'sell_media'
    );

    return $form_fields;
}
add_filter( 'attachment_fields_to_edit', 'sell_media_attachment_fields_to_edit', 10, 2 );


/**
 * Delete a product post type based on the ID
 *
 * @since 0.1
 */
function sell_media_delete_item( $post_id=null ){
    $product_id = get_post_meta( $post_id, '_sell_media_for_sale_product_id', true );

    $image_mimes = array(
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/bmp',
        'image/tiff'
        );

    $wp_upload_dir = wp_upload_dir();
    $attached_file_path = $wp_upload_dir['basedir'] . SellMedia::upload_dir . '/' . get_post_meta( $product_id, '_sell_media_attached_file', true );

    $mime_type = wp_check_filetype( $attached_file_path );

    /**
     * For all items that are NOT "photographs" we move the
     * original file back into "uploads/". Note we do NOT
     * want to delete the original, that is handled when the
     * user empties the trash bin.
     */
    if ( ! in_array( $mime_type, $image_mimes ) ){
        $destination_file = $wp_upload_dir['basedir'] . '/' . get_post_meta( $post_id, '_wp_attached_file', true );
        @copy( $attached_file_path, $destination_file );
    }

    delete_post_meta( $post_id, '_sell_media_attached_file' );
    delete_post_meta( $post_id, '_sell_media_for_sale_product_id' );
    delete_post_meta( $post_id, '_sell_media_for_sale' );

    wp_trash_post( $product_id );
}


/**
 * Filter: Save/Edit value of "Sell This?" selection in media uploader
 *
 * @param $post array, the post data for database
 * @param $attachment array, attachment fields from $_POST form
 * @return $post array, modified post data
 * @author Thad Allender
 * @uses add_filter( 'attachment_fields_to_save', 'sell_media_attachment_field_sell_save', 10, 2 );
 * @todo make wp_delete_post work
 * @todo add conditional for save vs. edit
 * @since 0.1
 */
function sell_media_attachment_field_sell_save( $post, $attachment ) {

    $for_sale = get_post_meta( $post['ID'], '_sell_media_for_sale', true );

    if ( empty( $attachment['sell'] ) && $for_sale == "1" ){
        /**
         * Item was once marked for sale and is no longer being sold
         */
        sell_media_delete_item( $post['ID'] );
        return $post;

    } elseif ( ! empty( $attachment['sell'] ) && $attachment['sell'] == "1" ) {
        /**
         * Attachment is now marked for sale
         */

        $product = array(
            'post_title' => $post['post_title'],
            'post_content' => $post['post_content'],
            'post_status' => 'publish',
            'post_type' => 'sell_media_item',
            'post_date_gmt' => date('Y-m-d H:i:s')
        );

        $product_id = wp_insert_post( $product );

        if ( is_wp_error( $product_id ) ) {
            return;
        }

        /**
         * From here we can asusme that the image has been upload
         * and a post type has been created. We update post meta
         * read some meta data from the image, build paths, save
         * iptc data, set default license and finally copy the
         * image to our products directory.
         */

        update_post_meta( $product_id, '_sell_media_attachment_id', $post['ID'] );
        update_post_meta( $product_id, 'sell_media_description', $post['post_content'] );
        update_post_meta( $product_id, '_sell_media_attached_file', date('Y') . '/' . date('m') . '/' . basename( $post['attachment_url'] ) );

        update_post_meta( $post['ID'], '_sell_media_for_sale', true );
        update_post_meta( $post['ID'], '_sell_media_for_sale_product_id', $product_id );

        // Read our meta data from the original post
        sell_media_set_default_terms( $product_id );

        // Build paths to the original file and the destination
        $dir = wp_upload_dir();

        $attached_file = get_post_meta( $post['ID'], '_wp_attached_file', true );
        $file_name = basename( $attached_file );

        $original_file = $dir['path'] . '/' . $file_name;

        $mime_type = wp_check_filetype( $original_file );

        $image_mimes = array(
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/bmp',
            'image/tiff'
            );

        if ( in_array( $mime_type['type'], $image_mimes ) ){
            sell_media_move_image_from_attachment( $attached_file, $product_id );
        } else {
            sell_media_default_move( $attached_file );
        }
        return $post;

    } else {

        /**
         * Attachment is not marked for sale and never was,
         * treat this as a regular update and just return the $post data.
         */
        return $post;

    }
}
add_filter( 'attachment_fields_to_save', 'sell_media_attachment_field_sell_save', 10, 2 );