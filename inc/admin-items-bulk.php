<?php

/**
 * Build bulk page callback function
 * Called from add_subpage on main sell-media.php file
 *
 * @author Thad Allender
 * @since 1.0.9
 */
function sell_media_add_bulk_callback_fn(){
    wp_enqueue_media();
    $settings = sell_media_get_plugin_options(); ?>
    <div class="wrap">
        <?php sell_media_add_tabs(); ?>
        <div class="tool-box add-bulk">
            <p><?php _e( 'Add Bulk allows you to add many single items at once. Please note: Bulk uploading is a server-intensive process. The capability of this feature is dependent on the performance of your server. Upload in small batches to ensure all items uploaded are published correctly. You can modify the prices and licenses of each item after doing the bulk upload.', 'sell_media' ); ?></p>
            <p class="uploader"><a class="sell-media-upload-trigger-multiple button" id="_sell_media_button"><?php _e( 'Upload', 'sell_media'); ?></a></p>
            <div class="sell-media-ajax-loader" style="display:none;"><?php _e( 'Loading items...', 'sell_media' ); ?></div>
            <div class="sell-media-bulk-list"><a href="<?php echo admin_url( 'edit.php?post_type=sell_media_item' ); ?>"></a></div>
            <div class="sell-media-bulk-controls">
                <form action="#" method="POST" id="sell_media_bulk_upload_form">
                    <?php wp_nonce_field( 'sell_media_bulk_update_collection', 'security' ); ?>
                    <table class="form-table sell-media-item-table">
                        <tbody>
                            <tr>
                                <th><?php _e('Choose a Price Group','sell_media'); ?>:</th>
                            </tr>
                            <tr>
                                <td>
                                    <select name="price_group" value="price_group" id="sell_media_price_group_select">
                                        <option value="" data-price="0"><?php _e( 'None', 'sell_media' ); ?></option>
                                        <?php foreach( get_terms('price-group',array('hide_empty'=>false, 'parent'=>0)) as $term ) : ?>
                                            <option value="<?php echo $term->term_id; ?>" <?php selected( $settings->default_price_group, $term->term_id ); ?>><?php echo $term->name; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><?php _e('Choose a License','sell_media'); ?>:</th>
                            </tr>
                            <tr>
                                <td>
                                    <select name="licenses" value="collection" id="sell_media_licenses_select">
                                        <option value="" data-price="0"><?php _e( 'None', 'sell_media' ); ?></option>
                                        <?php sell_media_build_options( array( 'taxonomy' => 'licenses', 'type'=>'select' ) ); ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><?php _e('Choose a Collection','sell_media'); ?>:</th>
                            </tr>
                            <tr>
                                <td>
                                    <select name="collection" value="collection" id="sell_media_collection_select">
                                        <option value="" data-price="0"><?php _e( 'None', 'sell_media' ); ?></option>
                                        <?php sell_media_build_options( array( 'taxonomy' => 'collection', 'type'=>'select' ) ); ?>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <?php do_action('sell_media_bulk_uploader_additional_fields'); ?>
                    <p><input type="submit" id="sell_media_bulk_upload_save_button" class="button-primary" value="<?php _e( 'Save', 'sell_media' ); ?>" /></p>
                </form>
            </div>
            <?php do_action( 'sell_media_bulk_below_uploader' ); ?>
        </div>
    </div>
<?php }


/**
 * Adds a collection to a series of posts
 *
 * @author Zane Matthew
 * @param $_POST['post_ids'] array of post ids
 * @param $_POST['term_id'] The term id
 * @since 1.2.9
 */
function sell_media_bulk_update_collection(){

    check_ajax_referer( 'sell_media_bulk_update_collection', 'security' );
    $settings = sell_media_get_plugin_options();
    $post_ids = explode(',', $_POST['post_ids'] );

    foreach( $post_ids as $post_id ){

        wp_set_post_terms( $post_id, $_POST['collection'], 'collection', true );
        wp_set_post_terms( $post_id, $_POST['licenses'], 'licenses', true );

        if ( ! empty( $_POST['price_group'] ) ){
            wp_set_post_terms( $post_id, $_POST['price_group'], 'price-group' );
        }
        update_post_meta( $post_id, 'sell_media_price', $settings->default_price );
        do_action( 'sell_media_bulk_uploader_additional_fields_meta', $post_id, $_POST );
    }
    die();
}
add_action( 'wp_ajax_sell_media_bulk_update_collection', 'sell_media_bulk_update_collection' );


/**
 * Handles bulk uploading via ajax
 */
function sell_media_uploader_multiple(){

    foreach( $_POST['attachments'] as $attachment ){

        $product_args = array(
            'post_title' => $attachment['title'],
            'post_content' => $attachment['description'],
            'post_status' => 'publish',
            'post_type' => 'sell_media_item',
            'post_date_gmt' => date( 'Y-m-d H:i:s' )
        );

        $product_id = wp_insert_post( $product_args );

        if ( is_wp_error( $product_id ) ) return;

        /**
         * From here we can asusme that the image has been upload
         * and a post type has been created. We update post meta
         * read some meta data from the image, build paths, save
         * iptc data, set default license and finally copy the
         * image to our products directory.
         */

        update_post_meta( $product_id, '_sell_media_attachment_id', $attachment['id'] );
        update_post_meta( $product_id, '_sell_media_attached_file', date('Y') . '/' . date('m') . '/' . basename( $attachment['url'] ) );
        update_post_meta( $attachment['id'], '_sell_media_for_sale_product_id', $product_id );

        // Read our meta data from the original post
        sell_media_set_default_terms( $product_id );

        $attached_file = get_post_meta( $attachment['id'], '_wp_attached_file', true );

        if ( Sell_Media()->products->mimetype_is_image( $attachment['id'] ) ){
            Sell_Media()->images->move_image_from_attachment( $attachment['id'] );
        } else {
            sell_media_default_move( $attached_file );
        }
    }

    // Display thumbnails with edit link after upload/selection
    $html = '<ul class="attachments sell-media-bulk-list">';
    foreach( $_POST['attachments'] as $attachment ){
        $product_id = get_post_meta( $attachment['id'], '_sell_media_for_sale_product_id', true );
        $html .= '<li class="attachment sell-media-bulk-list-item" data-post_id="' . $product_id . '">';
        $html .= '<a href="' . admin_url('post.php?post=' . $product_id . '&action=edit') . '" class="sell-media-bulk-list-item-img">';
        $html .= wp_get_attachment_image( $attachment['id'], array( 75,75 ) );
        $html .= '</a>';
        $html .= '<a href="' . admin_url( 'post.php?post=' . $product_id . '&action=edit' ) . '" class="sell-media-bulk-list-item-edit">' . __( 'Edit', 'sell_media' ) . '</a>';
        $html .= '</li>';
    }
    $html .= '</ul>';
    echo $html;

    die();
}
add_action( 'wp_ajax_sell_media_uploader_multiple', 'sell_media_uploader_multiple' );