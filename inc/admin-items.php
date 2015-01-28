<?php

/**
 * Admin Items
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

/**
 * Add a meta box for bulk & package tabs
 *
 * @author Thad Allender
 * @since 1.0.9
 */
function sell_media_add_tabs_meta_box( $post_type ){
    if ( 'sell_media_item' == $post_type )
        add_action( 'edit_form_after_title', 'sell_media_add_tabs' );
}
add_action( 'add_meta_boxes', 'sell_media_add_tabs_meta_box' );


/**
 * Build bulk tabs
 *
 * @author Thad Allender
 * @since 1.0.9
 */
function sell_media_add_tabs(){
    $screen = get_current_screen();
    $single_active = null;
    $bulk_active = null;
    $package_active = null;
    if ( 'sell_media_item' == $screen->id ) {
        $single_active = " nav-tab-active";
    }
    if ( 'sell_media_item_page_sell_media_add_bulk' == $screen->id ) {
        $bulk_active = " nav-tab-active";
        screen_icon();
        echo '<h2>' . __( 'Sell Media', 'sell_media' ) . '</h2>';
    }
    if ( 'sell_media_item_page_sell_media_add_package' == $screen->id ) {
        $package_active = " nav-tab-active";
        screen_icon();
        echo '<h2>' . __( 'Sell Media', 'sell_media' ) . '</h2>';
    }



    echo '<h2 id="sell-media-tabs" class="nav-tab-wrapper">';
    echo '<a href="' . admin_url( 'post-new.php?post_type=sell_media_item' ) . '" class="nav-tab' . $single_active . '">' . __( 'Add Single', 'sell_media' ) . '</a>';
    echo '<a href="' . admin_url( 'edit.php?post_type=sell_media_item&page=sell_media_add_bulk' ) . '" class="nav-tab' . $bulk_active . '" >' . __( 'Add Bulk', 'sell_media' ) . '</a>';
    echo '<a href="' . admin_url( 'edit.php?post_type=sell_media_item&page=sell_media_add_package' ) . '" class="nav-tab' . $package_active . '" >' . __( 'Add Package', 'sell_media' ) . '</a>';
    echo '</h2>';
}


/**
 * Add a meta box for item pricing
 *
 * @author Thad Allender
 * @since 0.1
 */
function sell_media_add_price_meta_box( $post_type ) {
    add_meta_box(
                'product_meta_box', // $id
                'Details', // $title
                'sell_media_details_meta_box', // $callback
                'sell_media_item', // $page
                'normal', // $context
                'high'); // $priority

    // add_meta_box(
    //             'sales_stats_meta_box', // $id
    //             'Sales Stats', // $title
    //             'sell_media_sales_stats', // $callback
    //             'sell_media_item', // $page
    //             'side', // $context
    //             'high'); // $priority
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

    $settings = sell_media_get_plugin_options();

    if ( ! empty( $_GET['post'] ) ) {
        $post_id = $_GET['post'];
    } elseif( ! empty( $_POST['post_ID'] ) ) {
        $post_id = $_POST['post_ID'];
    }else {
        $post_id = null;
    }

    // Don't show price groups if item is a package
    $is_package = get_post_meta( $post_id, '_sell_media_is_package', true );
    if ( $is_package ) {

        $sell_media_item_meta_fields = array(
            array(
                'label' => __( 'Package File', 'sell_media' ),
                'desc'  => '',
                'id'    => $prefix . '_file',
                'type'  => 'package'
            ),
            array(
                'label' => __( 'Price', 'sell_media' ) . ' (' . sell_media_get_currency_symbol() . ')',
                'desc'  => '',
                'id'    => $prefix . '_price',
                'type'  => 'price',
                'std'   => sprintf( "%0.2f", $settings->default_price ),
                'value' => get_post_meta( $post_id, $prefix . '_price', true )
            )
        );

    } else {

        $sell_media_item_meta_fields = array(
            array(
                'label' => __( 'File', 'sell_media' ),
                'desc'  => __( 'A description for the field.', 'sell_media' ),
                'id'    => $prefix . '_file',
                'type'  => 'file'
            ),
            array(
                'label' => __( 'Price', 'sell_media' ) . ' (' . sell_media_get_currency_symbol() . ')',
                'desc'  => '',
                'id'    => $prefix . '_price',
                'type'  => 'price',
                'std'   => sprintf( "%0.2f", $settings->default_price ),
                'value' => get_post_meta( $post_id, $prefix . '_price', true )
            ),
            array(
                'label' => __( 'Price Group', 'sell_media' ),
                'desc'  => __( 'If you want to sell additional image sizes, select a Price Group or <a href="' . admin_url() . 'edit.php?post_type=sell_media_item&page=sell_media_plugin_options&tab=sell_media_size_settings">create a new one</a>.', 'sell_media' ),
                'id'    => $prefix . '_price_group',
                'type'  => 'price_group'
            )
        );

    }


    $sell_media_item_meta_fields = apply_filters( 'sell_media_additional_item_meta', $sell_media_item_meta_fields, $post_id );

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
    //wp_editor( stripslashes_deep( get_post_field( 'post_content', $post->ID ) ), 'sell_media_editor' );
    wp_editor( $post->post_content, 'post_content', array( 'sell_media_editor' => 'post_content' ) );
}
add_action( 'edit_form_advanced', 'sell_media_editor' );


/**
 * Field builder for meta boxes
 *
 * @author Thad Allender
 * @since 0.1
 */
function sell_media_details_meta_box( $fields=null ) {

    global $post;


    // Since the first param coming into this functions is
    // ALWAYS the global $post which is an OBJECT we check it.
    // If it is an ARRAY we assume its new settings.
    if ( is_array( $fields ) ){
        $my_fields = $fields;
    } else {
        global $sell_media_item_meta_fields;
        $my_fields
        = $sell_media_item_meta_fields;
    }

    // Use nonce for verification
    echo '<input type="hidden" name="sell_media_custom_meta_box_nonce" value="' . wp_create_nonce( basename( __FILE__ ) ) . '" />';

    $is_package = get_post_meta( $post->ID, '_sell_media_is_package', true );
    if ( $is_package )
        echo '<p>' . __( 'This product is a package. Show customers what is included in this package by adding some images (preferably a gallery of images) to the Post Editor below. The Featured Image will be used to represent this package on archive pages.', 'sell_media' );

    // Begin the field table and loop
    echo '<table class="form-table sell-media-item-table">';
    foreach ( $my_fields as $field ) {
            $default = get_post_meta( $post->ID, $field['id'], true );

            // begin a table row with
            echo '<tr>
            <th><label for="' . $field['id'] . '">' . $field['label'] . '</label></th>
            <td>';

            $meta = null; // I have to find out what "meta" was used for, just setting it to null

            switch( $field['type'] ) {

                // text
                case 'text':
                    if ( $field['std'] )
                        $default = $field['std'];
                    echo '<input type="text" name="' . $field['id'].'" id="' . $field['id'] . '" placeholder="'. $default .'" value="' . wp_filter_nohtml_kses( $field['value'] ) . '" size="2"/><br /><span class="description">' . $field['desc'] . '</span>';
                break;

                // price
                case 'price':
                    if ( $field['std'] )
                        $default = $field['std'];

                    if ( '' != $field['value'] ) {
                        $price_value = wp_filter_nohtml_kses( $field['value'] );
                    } else {
                        $price_value = $default;
                    }
                    echo '<input type="number" step="0.01" min="0" class="small-text" name="' . $field['id'].'" id="' . $field['id'] . '" placeholder="'. $default .'" value="' . $price_value . '" /><br /><span class="description">' . $field['desc'] . '</span>';
                break;

                // textarea
                case 'textarea':
                    echo '<textarea name="' . $field['id'] . '" id="' . $field['id'] . '" cols="60" rows="4">' . $default . '</textarea>
                        <br /><span class="description">' . $field['desc'] . '</span>';
                break;

                // checkbox
                case 'checkbox':
                    echo '<input type="checkbox" name="' . $field['id'] . '" id="' . $field['id'] . '" ' . checked( $field['value'], "on", false ) . '/>
                        <label for="' . $field['id'] . '">' . $field['desc'] . '</label>';
                break;

                // select
                case 'select':
                    echo '<select name="'.$field['id'].'" id="'.$field['id'].'">';
                    foreach ($field['options'] as $option) {
                        echo '<option', $meta == $option['value'] ? ' selected="selected"' : '', ' value="'. $option['value'] .'">' . $option['label'] . '</option>';
                    }
                    echo '</select><br /><span class="description">' . $field['desc'] .'</span>';
                break;

                // image
                case 'image':
                    $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'medium' );
                    echo '<span class="custom_default_image" style="display:none">' . $image[0] . '</span>';
                    if ($meta) { $image = wp_get_attachment_image_src( $meta, 'medium' ); $image = $image[0]; }
                    echo    '<input name="' . $field['id'] . '" type="hidden" class="custom_upload_image" value="' . $meta . '" />
                    <img src="' . $image[0] . '" class="custom_preview_image" alt="" /><br />
                    <input class="custom_upload_image_button button" type="button" value="' . __( 'Choose Image', 'sell_media' ) . '" />
                    <small> <a href="#" class="custom_clear_image_button">' . __(' Remove Image', 'sell_media' ) . '</a></small>
                    <br clear="all" /><span class="description">' . $field['desc'] . '</span>';
                break;

                // File
                case 'file':

                    $attachment_id = get_post_meta( $post->ID, '_sell_media_attachment_id', true );
                    $attached_file = get_post_meta( $post->ID, '_sell_media_attached_file', true );
                    $hide = empty( $attachment_id ) ? 'style="display: none";' : null;

                    echo '<input type="hidden" name="sell_media_selected_file_id" class="sell_media_selected_file_id" />';
                    echo '<input type="text" name="_sell_media_attached_file" class="sell_media_attached_file sell-media-item-url field-has-button" value="' . $attached_file . '" size="30" />';
                    echo '<a class="sell-media-upload-trigger button">' . __( 'Upload', 'sell_media' ) . '</a><br class="clear"/>';

                    echo '<div class="sell-media-upload-trigger">';
                    echo '<div class="sell-media-item-thumbnail" ' . $hide . '>' . sell_media_item_icon( $post->ID, 'thumbnail', false ) . '</div>';
                    echo '</div>';
                    break;

                // text
                case 'html':
                    echo '<p id="' . $field['id'] . '"><span class="description">' . $field['desc'] . '</span></p>';
                    break;

                case 'price_group':
                    /**
                     * get our current term id for the parent only
                     */
                    $parent_id = false;
                    $settings = sell_media_get_plugin_options();
                    foreach( wp_get_post_terms( $post->ID, 'price-group' ) as $terms ){
                        if ( $terms->parent == 0 )
                            $parent_id = $terms->term_id;
                    }
                    if( false == $parent_id ) {
                        $parent_id = $settings->default_price_group;
                    }
                    ?>
                    <select name="_sell_media_price_group">
                        <option value="0"><?php _e("Select a price group"); ?></option>
                        <?php foreach( get_terms( 'price-group', array('hide_empty'=>false,'parent'=>0) ) as $term ) : ?>
                            <option <?php selected( $parent_id, $term->term_id ); ?> value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <br /><span class="description"><?php _e( $field['desc'], 'sell_media' ); ?></span>
                    <?php
                    break;

                case 'package':

                    $packages_dir = sell_media_get_packages_upload_dir();
                    $files = glob( $packages_dir . '/' . '*.{zip,gz}', GLOB_BRACE );
                    $saved = get_post_meta( $post->ID, '_sell_media_attached_file', true ); ?>

                    <select name="_sell_media_attached_file" id="_sell_media_attached_file" value="">
                        <option value=""><?php _e( 'Select a package', 'sell_media' ); ?></option>
                        <?php if ( $files ) foreach( $files as $file ) : ?>
                            <option <?php selected( $saved, basename( $file ) ); ?> value="<?php echo basename( $file ); ?>"><?php echo basename( $file ); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <?php
                    break;
                break;
            } //end switch
            echo '</td></tr>';
    } // end foreach
    echo '</table>'; // end table
    do_action('sell_media_additional_item_meta_section');
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

    $attachment_id = get_post_meta( $post_id, '_sell_media_attachment_id', true );

    // If the selected file id exists, then this is a new upload
    if ( empty( $_POST['sell_media_selected_file_id'] ) ){

        $attached_file = $_POST['_sell_media_attached_file'];

    } else {

        $attachment_id = $_POST['sell_media_selected_file_id'];
        $attached_file = get_post_meta( $attachment_id, '_wp_attached_file', true );

        // Check if this is a new upload
        if ( ! file_exists( sell_media_get_upload_dir() . '/' . $attached_file ) ){

            // Image mime type support
            if ( Sell_Media()->products->mimetype_is_image( $attachment_id ) ){
                Sell_Media()->images->move_image_from_attachment( $attachment_id );
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
                    $new = sprintf( '%0.2f', (float)$new );
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

    if ( "" != $_POST['_sell_media_price_group'] ) {
        wp_set_post_terms( $post_id, $_POST['_sell_media_price_group'], 'price-group' );
    }

}
add_action( 'save_post', 'sell_media_save_custom_meta' );


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

    return array_merge( $columns_local, $columns );
}
add_filter( 'manage_edit-sell_media_item_columns', 'sell_media_item_header' );


/**
 * Make column headers sortable
 *
 * @since 0.1
 */
function sell_media_sortable_column( $columns ) {
    $columns['sell_media_price'] = 'sell_media_price';
    $columns['author'] = 'author';
    return $columns;
}
add_filter( 'manage_edit-sell_media_item_sortable_columns', 'sell_media_sortable_column' );


/**
 * Sort the column headers
 *
 * @since 0.1
 */
function sell_media_column_orderby( $vars ) {
    if ( isset( $vars['orderby'] ) && 'sell_media_price' == $vars['orderby'] ) {
        $vars = array_merge( $vars, array(
            'meta_key' => 'sell_media_price',
            'orderby' => 'meta_value_num'
        ) );
    }
    if ( isset( $vars['orderby'] ) && 'author' == $vars['orderby'] ) {
        $vars = array_merge( $vars, array(
            'orderby' => 'author'
        ) );
    }
    return $vars;
}
add_filter( 'request', 'sell_media_column_orderby' );


/**
 * Filter custom column content on the edit media table.
 *
 * @since 0.1
 */
function sell_media_item_content( $column, $post_id ){
    switch( $column ) {
        case "icon":
            $html ='<a href="' . site_url() . '/wp-admin/post.php?post=' . $post_id . '&action=edit">';
            $html .= sell_media_item_icon( $post_id, 'thumbnail', false );
            $html .= '</a>';
            echo $html;
            break;
        case "sell_media_price":
            $price = get_post_meta( $post_id, 'sell_media_price', true );
            $settings = sell_media_get_plugin_options();
            if ( $price ) {
                echo sell_media_get_currency_symbol() . number_format( $price, 2, '.', '' );
            } elseif ( $settings->default_price ) {
                echo sell_media_get_currency_symbol() . number_format( $settings->default_price, 2, '.', '' );
            } else {
                echo __( 'No price set', 'sell_media' );
            }
            break;
        default:
            break;
    }
}
add_filter( 'manage_pages_custom_column', 'sell_media_item_content', 10, 2 );


/**
 * Echoes transaction sales, shown in admin
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
            <div class="misc-pub-section <?php echo $last_class; ?>"><?php echo $term_obj->name; ?> <?php echo $stats['count']; ?> <strong><?php echo sell_media_get_currency_symbol() . $stats['total']; ?></strong></div>
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
     * lower quality "original" with the file in the protected area.
     */
    $attached_file = get_post_meta( $postid, '_sell_media_attached_file', true );
    if ( empty( $attachment_id ) ){
        $attachment_id = get_post_meta( $postid, '_sell_media_attachment_id', true );
    } else {
        delete_post_meta( $attachment_id, '_sell_media_for_sale_product_id' );
    }


    delete_post_meta( $attachment_id, '_sell_media_for_sale_product_id' );

    $attached_file_path = sell_media_get_upload_dir() . '/' . $attached_file;

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
 * Redirect to custom url after move to trash in payments
 *
 * @since 1.6
 */
function sell_media_trash_payment_redirect() {
    $screen = get_current_screen();
    if( 'edit-sell_media_payment' == $screen->id ) {
        if( isset( $_GET['trashed'] ) &&  intval( $_GET['trashed']) > 0 ) {
            $redirect = add_query_arg( array( 'post_type' => 'sell_media_item', 'page'=>'sell_media_payments' ), admin_url() . "edit.php" );
            wp_redirect($redirect);
            exit();
        }
    }
}
add_action( 'load-edit.php', 'sell_media_trash_payment_redirect' );