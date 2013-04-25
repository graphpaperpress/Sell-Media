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
function sell_media_admin_items_init(){
    global $sell_media_item_meta_fields;
    $prefix = 'sell_media';
    $payment_settings = get_option( 'sell_media_size_settings' );
    $default_price = $payment_settings['default_price'];

    $size_settings = get_option('sell_media_size_settings');
    if ( ! empty( $_GET['post'] ) ) {
        $post_id = $_GET['post'];
    } elseif( ! empty( $_POST['post_ID'] ) ) {
        $post_id = $_POST['post_ID'];
    }else {
        $post_id = null;
    }


    $sell_media_item_meta_fields = array(
        array(
            'label' => __( 'File', 'sell_media' ),
            'desc'  => __( 'A description for the field.', 'sell_media' ),
            'id'    => $prefix . '_file',
            'type'  => 'file'
        ),
        array(
            'label' => __( 'Original File Price', 'sell_media' ),
            'desc'  => '', // this needs validation
            'id'    => $prefix . '_price',
            'type'  => 'price',
            'std'   => $default_price,
            'value' => get_post_meta( $post_id, $prefix . '_price', true )
        )
    );

    /**
     * @todo Quick fix to prevent this from firing on AJAX request
     */
    if ( ! is_null( $post_id ) ){
        $sizes = sell_media_image_sizes( $post_id, false );
        if ( $sizes ){
            foreach( $sizes as $k => $v ){
                $sell_media_item_meta_fields[] = array(
                    'label' => __( ucfirst( $k ), 'sell_media' ) . ' <span class="description">' . $size_settings[ $k . '_size_width'] . ' x ' . $size_settings[ $k . '_size_height'] . '</span>',
                    'desc'  => '',
                    'id'    => $prefix . '_price_' . $k,
                    'type'  => 'price',
                    'std'   => $size_settings[ $k . '_size_price'],
                    'value' => get_post_meta( $post_id, $prefix . '_price_' . $k, true )
                );
            }
        }
    }

    $sell_media_item_meta_fields = apply_filters( 'sell_media_additional_item_meta', $sell_media_item_meta_fields, $post_id );

    $sell_media_item_meta_fields[] = array(
            'label' => __( 'Shortcode', 'sell_media' ),
            'desc'  => __( 'The permalink for this item is displayed below the title above. The archive page showing all items for sale can be viewed <a href="' . get_post_type_archive_link( 'sell_media_item' ) . '">here</a>. You can optionally use shortcode to display this specific item on other Posts or Pages. Options include: text="purchase | buy" style="button | text" size="thumbnail | medium | large" align="left | center | right"', 'sell_media' ),
            'id'    => $prefix . '_shortcode',
            'type'  => 'html'
        );
    do_action('sell_media_extra_meta_fields', 'sell_media_item_meta_fields');
}
add_action('admin_init', 'sell_media_admin_items_init');


/**
 * Sell Media Editor
 *
 * @author Zane Matthew
 * @since 0.1
 */
function sell_media_editor() {

    global $post_type;

    if ( $post_type != "sell_media_item" ) return;

    global $post;
    wp_editor( stripslashes_deep( get_post_field( 'post_content', $post->ID ) ), 'sell_media_editor' );
}
add_action( 'edit_form_advanced', 'sell_media_editor' );


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
                    if ( $field['std'] )
                        $default = $field['std'];

                    echo '<input type="text" name="' . $field['id'].'" id="' . $field['id'] . '" placeholder="'. __( $default, 'sell_media' ) .'" value="' . wp_filter_nohtml_kses( $field['value'] ) . '" size="2"/><br /><span class="description">' . __( $field['desc'], 'sell_media' ) . '</span>';

                break;

                // price
                case 'price':
                    if ( $field['std'] )
                        $default = $field['std'];

                    echo '<span class="description">' . sell_media_get_currency_symbol() . '</span> <input type="number" step=".1" min="0" class="small-text" name="' . $field['id'].'" id="' . $field['id'] . '" placeholder="'. __( $default, 'sell_media' ) .'" value="' . wp_filter_nohtml_kses( $field['value'] ) . '" /><br /><span class="description">' . __( $field['desc'], 'sell_media' ) . '</span>';

                break;

                // textarea
                case 'textarea':
                    echo '<textarea name="' . $field['id'] . '" id="' . $field['id'] . '" cols="60" rows="4">' . __( $default, 'sell_media' ) . '</textarea>
                        <br /><span class="description">' . __( $field['desc'], 'sell_media' ) . '</span>';
                break;

                // checkbox
                case 'checkbox':
                    echo '<input type="checkbox" name="' . $field['id'] . '" id="' . $field['id'] . '" ' . checked( $field['value'], "on", false ) . '/>
                        <label for="' . $field['id'] . '">' . __( $field['desc'], 'sell_media' ) . '</label>';
                break;

                // select
                case 'select':
                    echo '<select name="'.$field['id'].'" id="'.$field['id'].'">';
                    foreach ($field['options'] as $option) {
                        echo '<option', $meta == $option['value'] ? ' selected="selected"' : '', ' value="'.$option['value'].'">' . __( $option['label'], 'sell_media' ) . '</option>';
                    }
                    echo '</select><br /><span class="description">'.__( $field['desc'], 'sell_media' ).'</span>';
                break;

                // image
                case 'image':
                    $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'medium' );
                    echo '<span class="custom_default_image" style="display:none">' . $image[0] . '</span>';
                    if ($meta) { $image = wp_get_attachment_image_src($meta, 'medium'); $image = $image[0]; }
                    echo    '<input name="' . $field['id'] . '" type="hidden" class="custom_upload_image" value="' . __( $meta, 'sell_media' ) . '" />
                    <img src="' . $image[0] . '" class="custom_preview_image" alt="" /><br />
                    <input class="custom_upload_image_button button" type="button" value="' . __( 'Choose Image', 'sell_media' ) . '" />
                    <small> <a href="#" class="custom_clear_image_button">'.__('Remove Image','sell_media').'</a></small>
                    <br clear="all" /><span class="description">' . __( $field['desc'], 'sell_media' ) . '</span>';
                break;

                // File
                case 'file':

                    $sell_media_attachment_id = get_post_meta( $post->ID, '_sell_media_attachment_id', true );
                    $attachment_id = ( $sell_media_attachment_id ) ? $sell_media_attachment_id : get_post_thumbnail_id( $post->ID );
                    $src_attribute = wp_get_attachment_url( $attachment_id );
                    $url = ( $src_attribute ) ? $src_attribute : null;
                    $attached_file = get_post_meta( $post->ID, '_sell_media_attached_file', true );

                    print '<input type="hidden" name="sell_media_selected_file_id" id="sell_media_selected_file_id" />';
                    print '<input type="text" name="_sell_media_attached_file_url" id="_sell_media_attached_file_url" class="sell-media-item-url field-has-button" value="' . $url . '" size="30" />';
                    print '<input type="hidden" name="_sell_media_attached_file" id="_sell_media_attached_file" class="sell-media-item-url field-has-button" value="' . $attached_file . '" size="30" />';
                    print '<a class="sell-media-upload-trigger button"id="_sell_media_button" value="Upload">' . __('Upload', 'sell_media') . '</a><br class="clear"/>';
                    print '<div class="sell-media-upload-trigger">';
                    print '<div class="sell-media-temp-target">' . sell_media_item_icon( $attachment_id, 'thumbnail', false ) . '</div>';
                    print '</div>';

                    break;

                // text
                case 'html':
                    $text = apply_filters( 'sell_media_purchase_text', __('Purchase') );
                    echo '<p><code>[sell_media_item id="' . $post->ID . '" text="' . $text . '" style="button" size="medium"]</code></p>
                    <p id="' . $field['id'] . '"><span class="description">' . __( $field['desc'], 'sell_media' ) . '</span></p>';
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

    $_thumbnail_id = get_post_thumbnail_id( $post_id );
    $_sell_media_attachment_id = get_post_meta( $post_id, '_sell_media_attachment_id', true );

    // If the selected file id was updated then we have
    // a new attachment.
    if ( empty( $_POST['sell_media_selected_file_id'] ) ){

        /**
         * Retroactive: If we have no $_sell_media_attachment_id we use the
         * old reference, $_thumbnail_id as the $attachment_id. Thus updating
         * the _sell_media_attachment_id to be the value of the _thumbnail_id.
         */
        if ( empty( $_sell_media_attachment_id ) ){
            $attachment_id = $_thumbnail_id;
        } else {
            $attachment_id = $_sell_media_attachment_id;
        }
        $attached_file = $_POST['_sell_media_attached_file'];
    } else {

        $attachment_id = $_POST['sell_media_selected_file_id'];
        $attached_file = get_post_meta( $attachment_id, '_wp_attached_file', true );

        // Check if this is a new upload
        $wp_upload_dir = wp_upload_dir();
        if ( ! file_exists( $wp_upload_dir['basedir'] . SellMedia::upload_dir . '/' . $attached_file ) ){

            $mime_type = wp_check_filetype( $wp_upload_dir['basedir'] . SellMedia::upload_dir . '/' . $attached_file );
            $image_mimes = array(
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/bmp',
                'image/tiff'
                );

            // Image mime type support
            if ( in_array( $mime_type['type'], $image_mimes ) ){
                sell_media_move_image_from_attachment( $attachment_id );
            } else {
                sell_media_default_move( $attached_file );
            }
        }
    }

    // Now, update the post meta to associate the new image with the post
    update_post_meta( $post_id, '_sell_media_attached_file', $attached_file );
    update_post_meta( $post_id, '_sell_media_attachment_id', $attachment_id );

    update_post_meta( $attachment_id, '_sell_media_for_sale_product_id', $post_id );
    update_post_meta( $attachment_id, '_sell_media_attached_file', $attached_file );

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

        if ( ! wp_is_post_revision( $post_id ) && $old_content != $new_content ){

            $args = array(
                    'ID' => $post_id,
                    'post_content' => $new_content
                    );

            // unhook this function so it doesn't loop infinitely
            remove_action('save_post', 'sell_media_save_custom_meta');
            // update the post, which calls save_post again
            wp_update_post( $args );

            // re-hook this function
            add_action('save_post', 'sell_media_save_custom_meta');

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
            $sell_media_attachment_id = get_post_meta( $post_id, '_sell_media_attachment_id', true );
            if ( $sell_media_attachment_id ){
                $attachment_id = $sell_media_attachment_id;
            } else {
                $attachment_id = get_post_thumbnail_id( $post_id );
            }
            $html ='<a href="' . site_url() . '/wp-admin/post.php?post=' . $post_id . '&action=edit">';
            $html .= sell_media_item_icon( $attachment_id, 'thumbnail' );
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
function sell_media_before_delete_post( $postid, $attachment_id=null ){

    $post_type = get_post_type( $postid );

    if ( $post_type != 'sell_media_item' ) return;

    /**
     * Get the attachment/thumbnail file so we can replace the "original", i.e.
     * lower quality "original" with the file in the proctedted area.
     */
    $attached_file = get_post_meta( $postid, '_sell_media_attached_file', true );
    if ( empty( $attachment_id ) ){
        $attachment_id = get_post_meta( $postid, '_sell_media_attachment_id', true );
    } else {
        delete_post_meta( $attachment_id, '_sell_media_for_sale_product_id' );
    }


    delete_post_meta( $attachment_id, '_sell_media_for_sale_product_id' );

    $wp_upload_dir = wp_upload_dir();
    $attached_file_path = $wp_upload_dir['basedir'] . SellMedia::upload_dir . '/' . $attached_file;

    // Delete the file stored in sell_media
    if ( file_exists( $attached_file_path ) ) {

        /**
         * Due to how WordPress handles attachments that are NOT
         * images we check if the "_wp_attached_file" is in fact
         * stored in the sell_media/ directory, i.e. there's only
         * "one" copy of the attachment.
         */
        $pos = strpos( $attached_file, 'sell_media/' );
        if ( $pos !== false ){
            $attached_file = str_replace( 'sell_media/', '', $attached_file );
        }

        // Copy our "original" back
        @copy( $attached_file_path, $wp_upload_dir['basedir'] . '/' . $attached_file );
        @unlink( $attached_file_path );

    }
    return;
}
add_action( 'before_delete_post', 'sell_media_before_delete_post' );


/**
 * Handles bulk uploading via ajax
 */
function sell_media_uploader_multiple(){

    $post = array();

    foreach( $_POST['attachments'] as $attachment ){

        $product_id = get_post_meta( $attachment['id'], '_sell_media_for_sale_product_id', true );

        $post['ID'] = $attachment['id'];
        $post['post_title'] = $attachment['title'];
        $post['post_content'] = null;
        $post['attachment_url'] = $attachment['url'];

        sell_media_attachment_field_sell_save( $post, $attachment['sell']="1" );
    }

    $html = '<ul class="attachments sell-media-bulk-list">';
    foreach( $_POST['attachments'] as $attachment ){
        $product_id = get_post_meta( $attachment['id'], '_sell_media_for_sale_product_id', true );
        $html .= '<li class="attachment sell-media-bulk-list-item" data-post_id="' . $product_id . '">';
        $html .= '<a href="' . admin_url('post.php?post=' . $product_id . '&action=edit') . '" class="sell-media-bulk-list-item-img">';
        $html .= wp_get_attachment_image( $attachment['id'], 'thumbnail' );
        $html .= '</a>';
        $html .= '<a href="' . admin_url('post.php?post=' . $product_id . '&action=edit') . '" class="sell-media-bulk-list-item-edit">' . __( 'Edit', 'sell_media' ) . '</a>';
        $html .= '</li>';
    }
    $html .= '</ul>';
    print $html;
    die();
}
add_action( 'wp_ajax_sell_media_uploader_multiple', 'sell_media_uploader_multiple' );