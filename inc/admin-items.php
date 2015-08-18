<?php

/**
 * Admin Items
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

/**
 * Add meta boxes on Add New screen for Sell Media
 *
 * @author Thad Allender
 * @since 0.1
 */
function sell_media_meta_boxes( $post_type ) {
    // Files
    add_meta_box(
        'files_meta_box', // $id
        'Files', // $title
        'sell_media_files_meta_box', // $callback
        'sell_media_item', // $page
        'normal', // $context
        'high' // $priority
    );
    // Options
    add_meta_box(
        'options_meta_box', // $id
        'Options', // $title
        'sell_media_options_meta_box', // $callback
        'sell_media_item', // $page
        'normal', // $context
        'high' // $priority
    );
    // Stats
    add_meta_box(
        'stats_meta_box', // $id
        'Stats', // $title
        'sell_media_stats_meta_box', // $callback
        'sell_media_item', // $page
        'normal', // $context
        'high' // $priority
    );
    do_action( 'sell_media_additional_meta_boxes' );
}
add_action( 'add_meta_boxes', 'sell_media_meta_boxes' );


/**
 * Files meta box
 */
function sell_media_files_meta_box( $post ) {
    wp_nonce_field( '_sell_media_meta_box_nonce', 'sell_media_meta_box_nonce' );
    do_action( 'sell_media_before_files_meta_box', $post ); ?>

    <div id="sell-media-upload-field" class="sell-media-field">
        <p><input type="button" class="sell-media-upload-button button" data-id="<?php echo $post->ID; ?>" value="<?php _e( 'Upload', 'sell_media' ); ?>" /></p>
        <p class="description"><?php _e( 'Upload one file to create a single product or many files to create a gallery.', 'sell_media' ); ?></p>
        <p class="description"><a href="#" class="sell-media-upload-options"><span class="dashicons dashicons-arrow-down"></span> <?php _e( 'Importing Options', 'sell_media' ); ?></a></p>
    </div>

    <div id="sell-media-upload-show-options" class="sell-media-upload-show-options" style="display:none;">
        <h4><?php _e( 'Importing', 'sell_media' ); ?></h4>
        <p class="description"><?php printf( __( 'Quickly import folders of images using this option. Use FTP or <a href="%1$s" target="_blank">export directly from Lightroom</a> and place new folders into the server path listed below. Then, select the folder below to import into WordPress.', 'sell_media' ), 'http://graphpaperpress.com/docs/sell-media/#add-bulk' ); ?></p>
        <p class="description"><strong><?php _e( 'Server Path', 'sell_media' ); ?>:</strong> <?php echo sell_media_get_import_dir(); ?></p>
        <select id="sell-media-upload-bulk-selector" value="">
            <option value=""><?php _e( 'Select a folder', 'sell_media' ); ?></option>
            <?php
            $directories = sell_media_get_directories();
            if ( $directories ) { foreach ( $directories as $directory ) : ?>
                <option value="<?php echo basename( $directory ); ?>"><?php echo basename( $directory ); ?></option>
            <?php endforeach; }; ?>
        </select>
        <button id="sell-media-upload-bulk-processor" type="button" class="button button-large" data-default-text="<?php _e( 'Add more files', 'sell_media' ); ?>" data-uploading-text="<?php _e( 'Importing files...', 'sell_media' ); ?>" disabled><?php _e( 'Add files', 'sell_media' ); ?></button><br /><br />
        <?php do_action( 'sell_media_after_files_show_options_meta_box', $post ); ?>
    </div>

    <?php if ( Sell_Media()->products->is_package( $post->ID ) ) : ?>
        <div id="sell-media-packages" class="sell-media-field">
            <h4><?php _e( 'Packages', 'sell_media' ); ?></h4>
            <p><?php _e( 'This feature was retired in version 2.0 because product galleries can now be created. Your old package file will still be available for sale and is listed below.', 'sell_media' ); ?></p>
            <p><strong><?php echo get_post_meta( $post->ID, '_sell_media_attached_file', true ); ?></strong></p>
        </div>
    <?php endif; ?>

    <ul class="attachments sell-media-upload-list">
        <?php
            $attachment_ids = sell_media_get_attachments( $post->ID );
            if ( $attachment_ids ) { foreach ( $attachment_ids as $attachment_id ) {
                echo sell_media_list_uploads( $attachment_id );
            }
}
        ?>
    </ul>

    <?php do_action( 'sell_media_after_files_meta_box', $post ); ?>
    <!-- This hidden field holds all attached file ids -->
    <input type="hidden" name="_sell_media_attachment_id" id="sell-media-attachment-id" class="sell-media-attachment-id" value="<?php echo ( ! empty( $attachment_ids ) ) ? implode( ',', $attachment_ids ) : ''; ?>"/>
<?php }

/**
 * Options meta box
 */
function sell_media_options_meta_box( $post ) {

    $settings = sell_media_get_plugin_options();
    $price = ( get_post_meta( $post->ID, 'sell_media_price', true ) ) ? get_post_meta( $post->ID, 'sell_media_price', true ) : sprintf( '%0.2f', $settings->default_price );
    do_action( 'sell_media_before_options_meta_box', $post ); ?>

    <div id="sell-media-price-field" class="sell-media-field">
        <label for="sell-media-price"><?php _e( 'Price', 'sell_media' ); ?> (<?php echo sell_media_get_currency_symbol(); ?>)</label>
        <input name="sell_media_price" id="sell-media-price" class="small-text" type="number" step="0.01" min="0" placeholder="<?php echo $price; ?>" value="<?php echo $price; ?>" />
        <?php if ( sell_media_has_multiple_attachments( $post->ID ) ) { ?>
            <span class="desc"><?php _e( 'The price of each original, high-resolution file.', 'sell_media' ); ?></span>
        <?php } ?>
    </div>

    <div id="sell-media-price-group-field" class="sell-media-field">
        <label for="sell-media-price-group"><?php _e( 'Price group for downloads', 'sell_media' ); ?></label>
        <?php
            $args = array(
                'show_option_none' => __( 'None', 'sell_media' ),
                'option_none_value' => 0,
                'name' => 'sell_media_price_group',
                'id' => 'sell-media-price-group',
                'class' => 'sell-media-price-group',
                'taxonomy' => 'price-group',
                'hierarchical' => true,
                'depth' => 1,
                'hide_empty' => false,
                'selected' => sell_media_get_item_price_group( $post->ID, 'price-group' )
            );
            wp_dropdown_categories( $args );
        ?>
        <span class="desc"><?php printf( __( '<a href="%1$s">Create new price group</a>', 'sell_media' ), admin_url() . 'edit.php?post_type=sell_media_item&page=sell_media_plugin_options&tab=sell_media_size_settings' ); ?></span>
    </div>

    <?php do_action( 'sell_media_after_options_meta_box', $post->ID );

}

/**
 * Stats meta box
 */
function sell_media_stats_meta_box( $post ) { ?>

    <?php do_action( 'sell_media_before_stats_meta_box', $post ); ?>

    <div id="sell-media-stats" class="sell-media-field">
        <ul>
            <li><strong><?php _e( 'Views', 'sell_media' ); ?>:</strong> <?php echo sell_media_get_post_views( $post->ID ); ?></li>
            <li><strong><?php _e( 'Sales', 'sell_media' ); ?>:</strong> <?php echo Sell_Media()->payments->get_item_sales( $post->ID ); ?></li>
        </ul>
    </div>

    <?php do_action( 'sell_media_after_stats_meta_box', $post );
}

/**
 * Editor meta box
 */
function sell_media_editor() {
    global $post_type;
    if ( $post_type != 'sell_media_item' ) { return; }
    global $post;
    wp_editor( $post->post_content, 'post_content', array( 'sell_media_editor' => 'post_content' ) );
}
add_action( 'edit_form_advanced', 'sell_media_editor' );


/**
 * Saves post meta data.
 *
 * This function will verify permissions, save the
 * post meta data, along with handling file uploads.
 * The file upload is processed, a new post is created,
 * the attachment meta data is also updated.
 */
function sell_media_save_custom_meta( $post_id ) {

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
    if ( ! isset( $_POST['sell_media_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['sell_media_meta_box_nonce'], '_sell_media_meta_box_nonce' ) ) { return; }
    if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

    // Prevents an infinite loop
    // See: http://codex.wordpress.org/Function_Reference/wp_update_post
    if ( wp_is_post_revision( $post_id ) ) { return; }
    remove_action( 'save_post', 'sell_media_save_custom_meta' );

    // loop through sell media fields and save the data
    $fields = sell_media_meta_box_fields();

    foreach ( $fields as $field ) {

        if ( isset( $_POST[ $field ] ) ) {

            // price groups fields
            if ( $field == 'sell_media_price_group' ) {

                if ( isset( $_POST['sell_media_price_group'] ) ) {
                    wp_set_post_terms( $post_id, $_POST['sell_media_price_group'], 'price-group' );
                }

            // print price groups fields
            } elseif ( $field == 'sell_media_print_price_group' ) {

                if ( isset( $_POST['sell_media_print_price_group'] ) ) {
                    wp_set_post_terms( $post_id, $_POST['sell_media_print_price_group'], 'reprints-price-group' );
                }

            // post meta fields
            } else {

                $old = get_post_meta( $post_id, $field, true );
                $new = $_POST[ $field ];

                if ( $new && $new != $old ) {

                    // Sanitize price
                    if ( $field == 'sell_media_price' ) {
                        $new = sprintf( '%0.2f', ( float ) $new );
                    }

                    // new meta and it's different than old saved value, so update it
                    update_post_meta( $post_id, $field, $new );

                    // Loop over attachment ids and move files into protected directory
                    if ( $field == '_sell_media_attachment_id' ) {
                        $attachment_ids = explode( ',', $_POST[ $field ] );
                        if ( $attachment_ids ) { foreach ( $attachment_ids as $attachment_id ) {
                            sell_media_move_file( $attachment_id );
                        }
}
                    }

                // new meta is empty, so delete it
                } elseif ( '' == $new && $old ) {

                    delete_post_meta( $post_id, $field, $old );

                }
}
        // Checkbox field isn't set, so delete the meta
        } else {

            delete_post_meta( $post_id, $field );

        }
    } // end foreach

    // Save the post content
    global $post_type;
    if ( ! empty( $_POST['sell_media_editor'] ) && $post_type == 'sell_media_item' ) {
        $new_content = $_POST['sell_media_editor'];
        $old_content = get_post_field( 'post_content', $post_id );
        if ( ! wp_is_post_revision( $post_id ) && $old_content != $new_content ) {
            $args = array(
                'ID' => $post_id,
                'post_content' => $new_content
            );
            // unhook this function so it doesn't loop infinitely
            remove_action( 'save_post', 'sell_media_save_custom_meta' );
            // update the post, which calls save_post again
            wp_update_post( $args );
            // re-hook this function
            add_action( 'save_post', 'sell_media_save_custom_meta' );
        }
    }
    do_action( 'sell_media_extra_meta_save', $post_id );
}
add_action( 'save_post', 'sell_media_save_custom_meta' );

/**
 * Upload callback
 */
function sell_media_upload_callback(){
    $html = '';
    check_ajax_referer( '_sell_media_meta_box_nonce', 'security' );

    // Display thumbnails after upload/selection
    if ( $_POST['attachments'] ) { foreach ( $_POST['attachments'] as $attachment ) {
        $html .= sell_media_list_uploads( $attachment['id'] );
    }
}
    echo $html;
    die();
}
add_action( 'wp_ajax_sell_media_upload_callback', 'sell_media_upload_callback' );


/**
 * Bulk upload callback
 */
function sell_media_upload_bulk_callback(){

    check_ajax_referer( '_sell_media_meta_box_nonce', 'security' );

    @ini_set( 'max_execution_time', '300' );

    if ( isset( $_POST['dir'] ) ) {

        $path = sell_media_get_import_dir() . '/' . $_POST['dir'] . '/';

        if ( file_exists( $path ) ) {

            $files = glob( $path . '*.*' );

            $html = '';

            if ( $files ) { foreach ( $files as $file ) {

                /**
                 * The WP function media_handle_sideload requires a files array ($_FILES).
                 * Since we want to keep the original file, we copy the original file
                 * and create a tmp file, which we can then unlink (delete)
                 */
                $tmp = $file . '.tmp';
                copy( $file, $tmp );

                $file_array = array(
                    'name' => basename( $file ),
                    'tmp_name' => $tmp
                );

                $attachment_id = media_handle_sideload( $file_array, $_POST['id'] );

                // remove temp file
                @unlink( $file_array['tmp_name'] );

                if ( is_wp_error( $attachment_id ) ) {
                    $html .= '<li class="attachment">' . sprintf( __( 'Sorry, %1$s could\'t be added.', 'sell_media' ), basename( $file ) ) . '</li>';
                } else {
                    $html .= sell_media_list_uploads( $attachment_id );
                }
}
}

            echo $html;
        }
}
    die();
}
add_action( 'wp_ajax_sell_media_upload_bulk_callback', 'sell_media_upload_bulk_callback' );

/**
 * Returns the default meta box fields
 *
 * @since 2.0.1
 * @author Thad Allender
 */
function sell_media_meta_box_fields() {

    $fields = array(
        '_sell_media_attachment_id',
        'sell_media_price',
        'sell_media_price_group'
    );

    return apply_filters( 'sell_media_meta_box_fields', $fields );
}


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
        unset( $columns['cb'] );
    }

    // Our next column header is the 'icon', we use this,
    // to ensure that our head has the class 'column-icon'
    if ( ! isset( $columns_local['icon'] ) ) {
        $columns_local['icon'] = 'Item'; }

    // We have to unset default columns to "move" them
    if ( isset( $columns['title'] ) ) {
        $columns_local['title'] = $columns['title'];
        unset( $columns['title'] );
    }

    if ( ! isset( $columns_local['sell_media_price'] ) ) {
        $columns_local['sell_media_price'] = 'Price'; }

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
    switch ( $column ) {
        case 'icon':
            $html = '<a href="' . site_url() . '/wp-admin/post.php?post=' . $post_id . '&action=edit">';
            $html .= sell_media_item_icon( $post_id, 'thumbnail', false );
            $html .= '</a>';
            echo $html;
            break;
        case 'sell_media_price':
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
        foreach ( $stats_array as $license_id => $stats ) {
            $term_obj = get_term( $license_id, 'licenses' );
            $i++;
            if ( $i == $count ) {
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

    if ( $post_type != 'sell_media_item' ) { return; }

    /**
     * Get the attachment/thumbnail file so we can replace the "original", i.e.
     * lower quality "original" with the file in the protected area.
     */
    $attached_file = get_post_meta( $postid, '_sell_media_attached_file', true );
    if ( empty( $attachment_id ) ) {
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
        if ( $pos !== false ) {
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
    if ( 'edit-sell_media_payment' == $screen->id ) {
        if ( isset( $_GET['trashed'] ) &&  intval( $_GET['trashed'] ) > 0 ) {
            $redirect = esc_url_raw( add_query_arg( array( 'post_type' => 'sell_media_item', 'page' => 'sell_media_payments' ), admin_url() . 'edit.php' ) );
            wp_redirect( $redirect );
            exit();
        }
    }
}
add_action( 'load-edit.php', 'sell_media_trash_payment_redirect' );