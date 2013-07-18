<?php

/**
 * Add a meta box for bulk tabs
 *
 * @author Thad Allender
 * @since 1.0.9
 */
function sell_media_add_bulk_tabs_meta_box( $post_type ){
    if ( 'sell_media_item' == $post_type )
        add_action( 'edit_form_after_title', 'sell_media_add_bulk_tabs' );
}
add_action( 'add_meta_boxes', 'sell_media_add_bulk_tabs_meta_box' );


/**
 * Build bulk tabs
 *
 * @author Thad Allender
 * @since 1.0.9
 */
function sell_media_add_bulk_tabs(){
    $screen = get_current_screen();
    $single_active = null;
    $bulk_active = null;
    if ( 'sell_media_item' == $screen->id )
        $single_active = " nav-tab-active";
    if ( 'sell_media_item_page_sell_media_add_bulk' == $screen->id ) {
        $bulk_active = " nav-tab-active";
        screen_icon();
        echo '<h2>' . __( 'Sell Media', 'sell_media' ) . '</h2>';
    }
    echo '<h2 id="sell-media-bulk-tabs" class="nav-tab-wrapper">';
    echo '<a href="' . admin_url( 'post-new.php?post_type=sell_media_item' ) . '" class="nav-tab' . $single_active . '">' . __( 'Add Single', 'sell_media' ) . '</a>';
    echo '<a href="' . admin_url( 'edit.php?post_type=sell_media_item&page=sell_media_add_bulk' ) . '" class="nav-tab' . $bulk_active . '" >' . __( 'Add Bulk', 'sell_media' ) . '</a>';
    echo '</h2>';
}

/**
 * Build bulk page callback function
 * Called from add_subpage on main sell-media.php file
 *
 * @author Thad Allender
 * @since 1.0.9
 */
function sell_media_add_bulk_callback_fn(){
    wp_enqueue_media(); ?>
    <div class="wrap">
        <?php sell_media_add_bulk_tabs(); ?>
        <div class="tool-box add-bulk">
            <p><?php _e( 'Bulk uploading is a server-intensive process. If you are using cheap, shared web hosting, uploading items in small batches will ensure all items uploaded are published correctly. All bulk uploads will inherit the default prices and licenses. You can modify the prices and licenses of each item after doing the bulk upload. ', 'sell_media' ); ?></p>
            <p class="uploader"><a class="sell-media-upload-trigger-multiple button" id="_sell_media_button" value="Upload"><?php _e( 'Upload', 'sell_media'); ?></a></p>
            <div class="sell-media-ajax-loader" style="display:none;"><?php _e('Loading items...', 'sell_media'); ?></div>
            <div class="sell-media-bulk-list"><a href="<?php print admin_url( 'edit.php?post_type=sell_media_item' ); ?>"></a></div>
            <div class="sell-media-bulk-controls">
                <form action="#" method="POST" id="sell_media_bulk_upload_form">
                    <?php wp_nonce_field('sell_media_bulk_update_collection','security'); ?>

                    <?php _e('Choose a Price Group','sell_media'); ?>:
                    <select name="price_group" value="price_group" id="sell_media_price_group_select">
                        <option value="" data-price="0"><?php _e( 'None', 'sell_media' ); ?></option>
                        <?php foreach( get_terms('price-group',array('hide_empty'=>false, 'parent'=>0)) as $term ) : ?>
                            <option value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
                        <?php endforeach; ?>
                    </select>

                    <?php _e('Choose a License','sell_media'); ?>:
                    <select name="licenses" value="collection" id="sell_media_licenses_select">
                        <option value="" data-price="0"><?php _e( 'None', 'sell_media' ); ?></option>
                        <?php sell_media_build_options( array( 'taxonomy' => 'licenses', 'type'=>'select' ) ); ?>
                    </select>

                    <?php _e('Choose a Collection','sell_media'); ?>:
                    <select name="collection" value="collection" id="sell_media_collection_select">
                        <option value="" data-price="0"><?php _e( 'None', 'sell_media' ); ?></option>
                        <?php sell_media_build_options( array( 'taxonomy' => 'collection', 'type'=>'select' ) ); ?>
                    </select>

                    <?php do_action('sell_media_bulk_uploader_additional_fields'); ?>
                    <p><input type="submit" id="sell_media_bulk_upload_save_button" class="button-primary" value="<?php _e('Save', 'sell_media'); ?>" /></p>
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
 * @param $_PSOT['term_id'] The term id
 * @since 1.2.9
 */
function sell_media_bulk_update_collection(){

    check_ajax_referer('sell_media_bulk_update_collection', 'security');
    $size_settings = get_option('sell_media_size_settings');
    $post_ids = explode(',', $_POST['post_ids'] );
    foreach( $post_ids as $post_id ){

        wp_set_post_terms( $post_id, $_POST['collection'], 'collection', true );
        wp_set_post_terms( $post_id, $_POST['licenses'], 'licenses', true );

        if ( ! empty( $_POST['price_group'] ) ){
            $childs = get_term_children( $_POST['price_group'], 'price-group' );
            $childs[] = $_POST['price_group'];
            wp_set_post_terms( $post_id, $childs, 'price-group' );
        } else {
            update_post_meta( $post_id, 'sell_media_price', $size_settings['default_price'] );
        }
        do_action( 'sell_media_bulk_uploader_additional_fields_meta', $post_id, $_POST );
    }
    die();
}
add_action( 'wp_ajax_sell_media_bulk_update_collection', 'sell_media_bulk_update_collection' );