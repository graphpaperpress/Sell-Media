<?php

/**
 * Add a meta box for item pricing
 *
 * @author Thad Allender
 * @since 0.1
 */
function sell_media_add_price_meta_box( $post_type ) {
    add_meta_box(
                'product_meta_box', // $id
                'Item Details', // $title
                'sell_media_show_custom_meta_box', // $callback
                'sell_media_item', // $page
                'normal', // $context
                'high'); // $priority

    add_meta_box(
                'sales_stats_meta_box', // $id
                'Sales Stats', // $title
                'sell_media_sales_stats', // $callback
                'sell_media_item', // $page
                'side', // $context
                'high'); // $priority
}
add_action( 'add_meta_boxes', 'sell_media_add_price_meta_box' );

/**
 * Field array for meta boxes
 *
 * @author Thad Allender
 * @since 0.1
 */
global $sell_media_item_meta_fields;
$prefix = 'sell_media';
$payment_settings = get_option( 'sell_media_payment_settings' );
$default_price = $payment_settings['default_price'];
$sell_media_item_meta_fields = array(
    array(
        'label'  => 'File',
        'desc'  => 'A description for the field.',
        'id'    => $prefix . '_file',
        'type'  => 'file'
    ),
    array(
        'label'=> 'Price',
        'desc'  => 'Numbers only.', // this needs validation
        'id'    => $prefix . '_price',
        'type'  => 'text',
        'std'   => $default_price
    ),
    array(
        'label'=> 'Small <span class="description">10x10</span>',
        'desc'  => 'Numbers only.', // this needs validation
        'id'    => $prefix . '_price',
        'type'  => 'text',
        'std'   => $default_price
    ),
    array(
        'label'=> 'Medium <span class="description">10x10</span>',
        'desc'  => 'Numbers only.', // this needs validation
        'id'    => $prefix . '_price',
        'type'  => 'text',
        'std'   => $default_price
    ),
    array(
        'label'=> 'Large <span class="description">10x10</span>',
        'desc'  => 'Numbers only.', // this needs validation
        'id'    => $prefix . '_price',
        'type'  => 'text',
        'std'   => $default_price
    ),
    array(
        'label'=> 'Shortcode',
        'desc'  => 'Copy and paste this shortcode to show the file and buy button anywhere on your site. Options include: text="purchase | buy" style="button | text" size="thumbnail | medium | large" align="left | center | right"', // this needs validation
        'id'    => $prefix . '_shortcode',
        'type'  => 'html'
    )
);
do_action('sell_media_extra_meta_fields', 'sell_media_item_meta_fields');


add_action( 'edit_form_advanced', 'sell_media_editor' );
function sell_media_editor() {

    global $post_type;

    if ( $post_type != "sell_media_item" ) return;

    global $post;
    wp_editor( stripslashes_deep( get_post_field( 'post_content', $post->ID ) ), 'sell_media_editor' );
}


/**
 * Field builder for meta boxes
 *
 * @author Thad Allender
 * @since 0.1
 */
function sell_media_show_custom_meta_box( $fields=null ) {

    global $post;

    // Since the first param coming into this functions is
    // ALWAYS the global $post which is an OBJECT we check it.
    // If it is an ARRAY we assume its new settings.
    if ( is_array( $fields ) ){
        $my_fields = $fields;
    } else {
        global $sell_media_item_meta_fields;
        $my_fields = $sell_media_item_meta_fields;
    }

    // Use nonce for verification
    echo '<input type="hidden" name="sell_media_custom_meta_box_nonce" value="' . wp_create_nonce( basename( __FILE__ ) ) . '" />';

    // Begin the field table and loop
    echo '<table class="form-table sell-media-item-table">';
    foreach ($my_fields as $field) {
            $default = get_post_meta( $post->ID, $field['id'], true );

            // begin a table row with
            echo '<tr>
            <th><label for="' . $field['id'] . '">' . $field['label'] . '</label></th>
            <td>';

            $meta = null; // I have to find out what "meta" was used for, just setting it to null

            switch($field['type']) {

                // text
                case 'text':
                    $tmp_price = get_post_meta( $post->ID, 'sell_media_price', true );
                    if ( $field['id'] == 'sell_media_price' && empty( $tmp_price ) ){
                        $payment_settings = get_option('sell_media_payment_settings');
                        $default = $payment_settings['default_price'];
                    }
                    echo '<input type="text" name="' . $field['id'].'" id="' . $field['id'] . '" value="' . $default . '" size="30" />
                        <br /><span class="description">' . $field['desc'] . '</span>';
                break;

                // textarea
                case 'textarea':
                    echo '<textarea name="' . $field['id'] . '" id="' . $field['id'] . '" cols="60" rows="4">' . $default . '</textarea>
                        <br /><span class="description">' . $field['desc'] . '</span>';
                break;

                // checkbox
                case 'checkbox':
                    echo '<input type="checkbox" name="' . $field['id'] . '" id="' . $field['id'] . '" ',$meta ? ' checked="checked"' : '','/>
                        <label for="' . $field['id'] . '">' . $field['desc'] . '</label>';
                break;

                // select
                case 'select':
                    echo '<select name="'.$field['id'].'" id="'.$field['id'].'">';
                    foreach ($field['options'] as $option) {
                        echo '<option', $meta == $option['value'] ? ' selected="selected"' : '', ' value="'.$option['value'].'">'.$option['label'].'</option>';
                    }
                    echo '</select><br /><span class="description">'.$field['desc'].'</span>';
                break;

                // image
                case 'image':
                    $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'medium' );
                    echo '<span class="custom_default_image" style="display:none">' . $image[0] . '</span>';
                    if ($meta) { $image = wp_get_attachment_image_src($meta, 'medium'); $image = $image[0]; }
                    echo    '<input name="' . $field['id'] . '" type="hidden" class="custom_upload_image" value="' . $meta . '" />
                    <img src="' . $image[0] . '" class="custom_preview_image" alt="" /><br />
                    <input class="custom_upload_image_button button" type="button" value="Choose Image" />
                    <small> <a href="#" class="custom_clear_image_button">Remove Image</a></small>
                    <br clear="all" /><span class="description">' . $field['desc'] . '';
                break;

                // File
                case 'file':
                    $attachment_id = get_post_thumbnail_id( $post->ID );
                    print '<input type="hidden" name="sell_media_selected_file_id" id="sell_media_selected_file_id" />';
                    print '<input type="text" name="_sell_media_file" id="_sell_media_file" class="field-has-button" value="'.get_post_meta($post->ID,'_sell_media_file', true).'" size="30" />';
                    print '<div class="sell-media-upload-trigger">';
                    if ( empty( $attachment_id ) ){
                        print '<a class="sell-media-upload-trigger button"id="_sell_media_button" value="Upload">'.__('Upload or Select Image', 'sell_media').'</a><br class="clear"/>';
                        print '<img src="" class="sell-media-image" />';
                    } else {
                        sell_media_item_icon( $attachment_id );
                    }
                    print '</div>';


                    break;

                // text
                case 'html':
                    echo '<p><code>[sell_media_item id="' . $post->ID . '" text="Purchase" style="button" size="medium"]</code></p>
                    <p id="' . $field['id'] . '"><span class="description">' . $field['desc'] . '</span></p>';
                break;
            } //end switch
            echo '</td></tr>';
    } // end foreach
    echo '</table>'; // end table
}


/**
 * Saves post meta data.
 *
 * This function will verify permissions, save the
 * post meta data, along with handling file uploads.
 * The file upload is processed, a new post is created,
 * the attachment meta data is also updated. Part of
 * this can (and should) be broken our into a re-usable method.
 *
 * @uses wp_verify_nonce()
 * @uses current_user_can()
 * @uses wp_get_current_user()
 * @uses wp_handle_upload()
 * @uses wp_check_filetype()
 * @uses wp_upload_dir()
 * @uses _wp_relative_upload_path()
 * @uses wp_insert_attachment()
 * @uses wp_generate_attachment_metadata()
 * @uses wp_update_attachment_metadata()
 * @uses update_post_meta()
 *
 */
function sell_media_save_custom_meta( $post_id ) {

    global $sell_media_item_meta_fields;
    do_action('sell_media_extra_meta_save');

    if ( isset( $_POST['sell_media_custom_meta_box_nonce'] ) )
        $nonce = $_POST['sell_media_custom_meta_box_nonce'];
    else
        return;

    if ( ! wp_verify_nonce( $nonce, basename(__FILE__) ) )
        return $post_id;

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        return $post_id;

    if ( 'page' == $_POST['post_type'] ) {
        if ( ! current_user_can('edit_page', $post_id) ) {
            return $post_id;
        } elseif ( ! current_user_can('edit_post', $post_id) ) {
            return $post_id;
        }
    }

    // If the selected file id was updated then we have
    // a new attachment.
    if ( empty( $_POST['sell_media_selected_file_id'] ) ){
        $selected_file = get_post_meta( $post_id, '_sell_media_file', true );
        $attachment_id = get_post_meta( $post_id, '_thumbnail_id', true );
    } else {
        $wp_upload_dir = wp_upload_dir();
        $attachment_id = $_POST['sell_media_selected_file_id'];

        $attached_file = get_post_meta( $attachment_id, '_wp_attached_file', true );
        $selected_file = $wp_upload_dir['basedir'] . '/' . $attached_file;
        $proteced_file = $wp_upload_dir['basedir'] . SellMedia::upload_dir . '/' . $attached_file;

        // Check if this is a new upload

        if ( ! file_exists( $proteced_file ) ){

            $mime_type = wp_check_filetype( $selected_file );
            $image_mimes = array(
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/bmp',
                'image/tiff'
                );

            // Image mime type support
            if ( in_array( $mime_type['type'], $image_mimes ) ){
                sell_media_move_image_from_attachment( $attached_file, $post_id );
            } else {
                sell_media_default_move( $attached_file );
            }
        }
    }

    // Now, update the post meta to associate the new image with the post
    update_post_meta( $post_id, '_wp_attached_file', $attachment_id );
    update_post_meta( $post_id, '_thumbnail_id', $attachment_id );
    update_post_meta( $post_id, '_sell_media_file', $proteced_file );

    update_post_meta( $attachment_id, '_sell_media_for_sale_product_id', $post_id );
    update_post_meta( $attachment_id, '_sell_media_for_sale', 1 );
    update_post_meta( $attachment_id, '_sell_media_file', $proteced_file );

    // loop through fields and save the data
    foreach ($sell_media_item_meta_fields as $field) {

        if ( isset( $_POST[ $field['id'] ] ) ){
            $old = get_post_meta( $post_id, $field['id'], true );
            $new = $_POST[ $field['id'] ];

            if ( $new && $new != $old ) {

                if ( $field['id'] == 'sell_media_price' ){
                    $new = (float)$new;
                }

                update_post_meta( $post_id, $field['id'], $new );
            } elseif ('' == $new && $old) {
                delete_post_meta( $post_id, $field['id'], $old );
            }
        }
    } // end foreach

    // Save the post content
    global $post_type;
    if ( ! empty( $_POST['sell_media_editor'] ) && $post_type == 'sell_media_item' ){

        $new_content = $_POST['sell_media_editor'];
        $old_content = get_post_field( 'post_content', $post_id );

        if ( $old_content != $new_content ){
            global $wpdb;
            $new_content = $_POST['sell_media_editor'];
            $query = "UPDATE {$wpdb->prefix}posts SET post_content = %s WHERE ID LIKE %d;";
            $wpdb->query( $wpdb->prepare( $query, $new_content, $post_id ) );
        }
    }
}
add_action('save_post', 'sell_media_save_custom_meta');


/**
 * Filter column headers names on the edit media table.
 *
 * @since 0.1
 */
function sell_media_item_header( $columns ){

    $columns_local = array();

    // Allows to "move the checkbox" to be first
    if ( isset( $columns['cb'] ) ) {
        $columns_local['cb'] = $columns['cb'];
        unset($columns['cb']);
    }

    // Our next column header is the 'icon', we use this,
    // to ensure that our head has the class 'column-icon'
    if ( ! isset( $columns_local['icon'] ) )
        $columns_local['icon'] = "Item";

    // We have to unset default columns to "move" them
    if ( isset( $columns['title'] ) ) {
        $columns_local['title'] = $columns['title'];
        unset($columns['title']);
    }

    if ( ! isset( $columns_local['sell_media_price'] ) )
        $columns_local['sell_media_price'] = "Price";

    if ( ! isset( $columns_local['sell_media_license'] ) )
        $columns_local['sell_media_license'] = "License";

    return array_merge( $columns_local, $columns );
}
add_filter( 'manage_edit-sell_media_item_columns', 'sell_media_item_header' );


/**
 * Filter custom column content on the edit media table.
 *
 * @since 0.1
 */
function sell_media_item_content( $column, $post_id ){
    switch( $column ) {
        case "icon":
            // $html ='<a href="'.site_url().'/wp-admin/media.php?attachment_id='.get_post_thumbnail_id( $post_id ).'&action=edit">';
            $html ='<a href="' . site_url() . '/wp-admin/post.php?post=' . $post_id . '&action=edit">';
            $html .= sell_media_item_icon( get_post_thumbnail_id( $post_id ), 'thumbnail' );
            $html .= '</a>';
            print $html;
            break;
        case "sell_media_price":
            sell_media_item_price( $post_id );
            break;
        case "sell_media_license":
            $terms = wp_get_post_terms( $post_id, 'licenses' );
            $count = count( $terms );
            $i = 0;
            foreach( $terms as $term ){
                print '<a href="' . site_url() . '/wp-admin/edit.php?licenses=' . $term->slug . '&post_type=sell_media_item">' . $term->name . '</a>';
                if ( $count - 1 > $i ) print ', ';
                $i++;
            }
            break;
        default:
            break;
    }
}
add_filter( 'manage_pages_custom_column', 'sell_media_item_content', 10, 2 );


/**
 * Move attachments of deleted sell media items back to default uploads dir
 *
 * @since 0.1
 */
function sell_media_move_image_from_attachment_back( $postid ){

    // Read our meta data from the original post
    $aid = get_post_thumbnail_id( $postid );
    $meta = wp_get_attachment_metadata( $aid );

    // Build paths to the original file and the destination
    $dir = wp_upload_dir();
    $destination_file = $dir['basedir'] . '/' . $meta['file'];

    $original_file = $dir['basedir'] . SellMedia::upload_dir . '/' . $meta['file'];

    @copy( $original_file, $destination_file );
    return;
}
// add_action( 'before_delete_post', 'sell_media_move_image_from_attachment_back' );


/**
 * Prints transaction sales, shown in admin
 *
 * @since 0.1
 * @return string
 */
function sell_media_sales_stats(){
    global $post_id;

    $stats_array = maybe_unserialize( get_post_meta( $post_id, 'sell_media_sales_stats', true ) );

    if ( $stats_array ) {
        $i = 0;
        $count = count( $stats_array );
        foreach( $stats_array as $license_id => $stats ) {
            $term_obj = get_term( $license_id, 'licenses' );
            $i++;
            if ( $i == $count ){
                $last_class = 'misc-pub-section-last';
            } else {
                $last_class = null;
            }
            ?>
            <div class="misc-pub-section <?php print $last_class; ?>"><?php print $term_obj->name; ?> <?php print $stats['count']; ?> <strong><?php print sell_media_get_currency_symbol() . $stats['total']; ?></strong></div>
        <?php }
    } else {
        _e( 'No sales so far.', 'sell_media' );
    }
}


/**
 * Deletes the uploaded file in sell_media/ when the
 * trash bin is emptied.
 *
 * @since 1.0.4
 */
function sell_media_before_delete_post( $postid ){

    global $post_type;

    if ( $post_type != 'sell_media_item' ) return;

    $file = get_post_meta( $postid, '_sell_media_file', true );

    /**
     * Get the attachment/thumbnail file so we can replace the "original", i.e.
     * lower quality "original" with the file in the proctedted area.
     */
    $attached_file = get_post_meta( get_post_meta( $postid, '_thumbnail_id', true ), '_wp_attached_file', true );

    // Delete the file stored in sell_media
    if ( file_exists( $file ) ) {

        /**
         * Due to how WordPress handles attachments that are NOT
         * images we check if the "_wp_attached_file" is in fact
         * stored in the sell_media/ directory, i.e. there's only
         * "one" copy of the attachment.
         */
        $pos = strpos( $attached_file, 'sell_media/' );
        $dir = wp_upload_dir();
        if ( $pos !== false ){
            $attached_file = str_replace( 'sell_media/', '', $attached_file );
        }

        @copy( $file, $dir['basedir'] . '/' . $attached_file );
        @unlink( $file );
    }
}
add_action( 'before_delete_post', 'sell_media_before_delete_post' );

function sell_media_uploader_multiple(){
//print_r( $_POST );
    $wp_upload_dir = wp_upload_dir();
    $post = array();
    foreach( $_POST['attachments'] as $attachment ){

        $attached_file = get_post_meta( $attachment['id'], '_wp_attached_file', true );
        $selected_file = $wp_upload_dir['basedir'] . '/' . $attached_file;
        $proteced_file = $wp_upload_dir['basedir'] . SellMedia::upload_dir . '/' . $attached_file;

        $post['ID'] = $attachment['id'];
        $post['post_title'] = $attachment['title'];
        $post['post_content'] = null;
        $post['attachment_url'] = $attachment['url'];

        sell_media_attachment_field_sell_save( $post, $attachment['sell']="on");
    }
    $html = null;
    $html .= '<ul class="attachments">';
    foreach( $_POST['attachments'] as $attachment ){
        $product_id = get_post_meta( $attachment['id'], '_sell_media_for_sale_product_id', true );
        $html .= '<li class="attachment">';
        $html .= '<a href="' . admin_url('post.php?post=' . $product_id . '&action=edit') . '">';
        $html .= wp_get_attachment_image( $attachment['id'], 'thumbnail' );
        $html .= '</a>';
        $html .= '</li>';
    }
    $html .= '</ul>';
    print $html;
    die();
}
add_action( 'wp_ajax_sell_media_uploader_multiple', 'sell_media_uploader_multiple' );