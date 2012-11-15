<?php

/**
 * Add a meta box for item pricing
 *
 * @author Thad Allender
 * @since 0.1
 */
function sell_media_add_price_meta_box() {
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
add_action('add_meta_boxes', 'sell_media_add_price_meta_box');


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
        'label'  => 'Item',
        'desc'  => 'A description for the field.',
        'id'    => $prefix . '_file',
        'type'  => 'file'
    ),
    array(
        'label'=> 'Description',
        'desc'  => 'A brief description for the item.',
        'id'    => $prefix . '_description',
        'type'  => 'textarea'
    ),
    array(
        'label'=> 'Price',
        'desc'  => 'Leave off the ' . sell_media_get_currency_symbol() . '. Numbers only.', // this needs validation
        'id'    => $prefix . '_price',
        'type'  => 'text',
        'std'   => $default_price
    ),
    array(
        'label'=> 'Shortcode',
        'desc'  => 'Copy and paste this shortcode to show the image and buy button anywhere on your site, including Posts, Pages and in Widgets. You can change specific parameters in the shortcode to change the button design, the button text and the image size. Options include: text="purchase | buy" style="button | text" size="thumbnail | medium | large" align="left | center | right"', // this needs validation
        'id'    => $prefix . '_shortcode',
        'type'  => 'html'
    )
);
do_action('sell_media_extra_meta_fields', 'sell_media_item_meta_fields');


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
    echo '<table class="form-table">';
    foreach ($my_fields as $field) {
            $default = get_post_meta( $post->ID, $field['id'], true );

            // if ( ! isset( $default ) ) {
            //     $default = $field['std'];
            // } else {
            //     $default = null;
            // }

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
                    sell_media_item_icon( $attachment_id );
                    echo  '<br clear="all" /><input type="file" name="' . $field['id'] . '" /><br clear="all" /><span class="description">' . $field['desc'] . '';
                    break;

                // text
                case 'html':
                    echo '<p id="' . $field['id'] . '"><span class="description">' . $field['desc'] . '</span></p>
                    <p><code>[sell_media_item id="' . $post->ID . '" text="Purchase" style="button" size="medium"]</code></p>';
                break;

                // repeatable
                case 'repeatable':
                    echo '<a class="repeatable-add button" href="#">+</a>
                            <ul id="' . $field['id'] . '-repeatable" class="custom_repeatable ui-sortable">';
                    $i = 0;
                    if ($meta) {
                        foreach($meta as $row) {
                            echo '<li><span class="sort hndle">|||</span>
                                        <input type="text" name="' . $field['id'] . '[' . $i . ']" id="' . $field['id'] . '" value="' . $row . '" size="30" />
                                        <a class="repeatable-remove button" href="#">-</a></li>';
                            $i++;
                        }
                    } else {
                        echo '<li><span class="sort hndle">|||</span>
                                    <input type="text" name="' . $field['id'] . '[' . $i . ']" id="' . $field['id'] . '" value="" size="30" />
                                    <a class="repeatable-remove button" href="#">-</a></li>';
                    }
                    echo '</ul>
                        <span class="description">' . $field['desc'] . '</span>';
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

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE || empty( $_FILES ) )
        return $post_id;

    if ( 'page' == $_POST['post_type'] ) {
        if ( ! current_user_can('edit_page', $post_id) ) {
            return $post_id;
        } elseif ( ! current_user_can('edit_post', $post_id) ) {
            return $post_id;
        }
    }

    $wp_upload_dir = wp_upload_dir();

    // Build our destination path, note the Y/m
    $destination_file = $wp_upload_dir['basedir'] . SellMedia::upload_dir . '/' . date('Y') . '/' . date('m') . '/';

    // This is used to check for year/month/ folder
    // If we don't have one we'll let WordPress create it.
    if ( ! is_dir( $destination_file ) ){
        wp_mkdir_p( $destination_file );
    }

    // Move our uploaded file to our Sell Media upload folder
    $did_move = move_uploaded_file( $_FILES['sell_media_file']['tmp_name'], $destination_file . $_FILES['sell_media_file']['name'] );
    $moved_file =  $destination_file . $_FILES['sell_media_file']['name'];

    // Insert our uploaded file from the Sell Media upload dir into
    // WordPress as an attachment
    if ( $did_move ){

        // Resize our original to a lower "quality" and then copy it
        // to our wp uploads directory so other plugins/themes can use it.
        do_action( 'sell_media_before_upload' );

        $mime_type = wp_check_filetype( $moved_file );

        $image_mimes = array(
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/bmp',
            'image/tiff'
            );

        if ( in_array( $mime_type['type'], $image_mimes ) ){
            $destination_file = sell_media_move_image_from_meta( $moved_file, $_FILES );
        }

        // Additional processes dependent on mime types will
        // be here.

        $current_user = wp_get_current_user();
        $attachment = array(
            'post_mime_type' => $_FILES['sell_media_file']['type'],
            'guid' => $wp_upload_dir['baseurl'] . '/' . _wp_relative_upload_path( $destination_file ),
            'post_title' => $_FILES['sell_media_file']['name'],
            'post_content' => '',
            'post_author' => $current_user->ID,
            'post_status' => 'inherit',
            'post_date' => date( 'Y-m-d H:i:s' ),
            'post_date_gmt' => date( 'Y-m-d H:i:s' )
        );

        // Run the wp_insert_attachment function.
        // This adds the file to the media library and generates the thumbnails.
        // If you wanted to attch this image to a post,
        // you could pass the post id as a third param and it'd magically happen.
        $attach_id = wp_insert_attachment( $attachment, $destination_file );

        require_once(ABSPATH . "wp-admin" . '/includes/image.php');
        $attach_data = wp_generate_attachment_metadata( $attach_id, $destination_file );
        wp_update_attachment_metadata( $attach_id, $attach_data );

        // Now, update the post meta to associate the new image with the post
        update_post_meta( $post_id, '_wp_attached_file', $attach_id );
        update_post_meta( $post_id, '_thumbnail_id', $attach_id );
        update_post_meta( $attach_id, '_sell_media_for_sale_product_id', $post_id );
        update_post_meta( $attach_id, '_sell_media_for_sale', 1 );
    }

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
}
add_action('save_post', 'sell_media_save_custom_meta');


/**
 * Adds the enctype to our form on edit/add post page so our form
 * can upload media. This function called by the 'init_sell_media' method
 * Only used on Sell Media Add New page
 *
 * @since 0.1
 */
function sell_media_enctype( ) {
    global $post_type;
        if ( is_admin() && $post_type == 'sell_media_item' ) {
            echo ' enctype="multipart/form-data"';
        }
}
add_action( 'post_edit_form_tag' , 'sell_media_enctype' );


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
    die('here');
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