<?php

/**
 * Build package page callback function
 * Called from add_subpage on main sell-media.php file
 *
 * @author Thad Allender
 * @since 1.0.9
 */
function sell_media_add_package_callback_fn(){
    wp_enqueue_media();
    $settings = sell_media_get_plugin_options();
    $packages_dir = sell_media_get_packages_upload_dir();
    // make the package directory if it doesn't already exist
    if ( ! is_dir( $packages_dir ) ) {
        mkdir( $packages_dir );
    }
    ?>
    <div class="wrap">
        <?php sell_media_add_tabs(); ?>
        <div class="tool-box add-package">
            <p><?php _e( 'Packages are groups of large files that you want to sell as one product (Example: You photograph an event and want to sell all images in one package). To sell a package, you must:', 'sell_media' ); ?></p>
            <p>
                <ol>
                    <li><?php _e( 'ZIP up the folder full of files on your computer.', 'sell_media' ); ?></li>
                    <li><?php printf( __( 'Using FTP, upload the ZIP file to your website into the %1$s folder.','sell_media' ), $packages_dir ); ?></li>
                    <li><?php _e( 'Select the package filename from the list below.', 'sell_media' ); ?></li>
                </ol>
            </ul>

            <div class="sell-media-package-controls">
                <form action="#" method="POST" id="sell_media_package_upload_form">
                    <?php wp_nonce_field( 'sell_media_package_update', 'security' ); ?>
                    <table class="form-table sell-media-item-table">
                        <tbody>
                            <tr>
                                <th><?php _e( 'Choose a package', 'sell_media' ); ?>:</th>
                            </tr>
                            <tr>
                                <td>
                                    <?php
                                        $files = glob( $packages_dir . '/' . '*.{zip,gz}', GLOB_BRACE );
                                        $html = '<select name="file" value="" id="sell_media_package_select">';
                                        $html .= '<option value="">' . __( 'Select a package', 'sell_media' ) . '</option>';
                                        if ( $files ) foreach( $files as $file ) {
                                            $html .= '<option value="' . basename( $file ) . '">' . basename( $file ) . '</option>';
                                        }
                                        $html .= '</select>';
                                        echo $html;
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th><?php _e( 'Name','sell_media' ); ?>:</th>
                            </tr>
                            <tr>
                                <td>
                                    <input name="name" value="" type="text" id="sell_media_package_name" />
                                </td>
                            </tr>
                            <tr>
                                <th><?php _e( 'Price','sell_media' ); ?> (<?php echo sell_media_get_currency_symbol(); ?>):</th>
                            </tr>
                            <tr>
                                <td>
                                    <input name="price" value="" type="number" step="0.01" min="0" class="small-text" id="sell_media_package_price" placeholder="10.00" />
                                </td>
                            </tr>
                            <tr>
                                <th><?php _e( 'Collection (optional)', 'sell_media' ); ?>:</th>
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
                    <?php do_action('sell_media_package_uploader_additional_fields'); ?>
                    <p><input type="submit" id="sell_media_package_upload_save_button" class="button-primary" value="<?php _e( 'Save', 'sell_media' ); ?>" /></p>
                    <p id="sell_media_package_edit_link" style="display:none;"><a href="<?php echo admin_url(); ?>"><?php _e( 'Edit Package', 'sell_media' ); ?></a></p>
                </form>
            </div>
            <?php do_action( 'sell_media_package_below_uploader' ); ?>
        </div>
    </div>
<?php }


/**
 * Adds a package to a series of posts
 *
 * @author Thad Allender
 * @param $_POST['file'] the package file
 * @param $_POST['price'] the price
 * @param $_POST['collection'] the collection
 * @since 1.8.3
 */
function sell_media_package_update(){

    check_ajax_referer( 'sell_media_package_update', 'security' );

    $new_post = array(
        'post_title' => $_POST['name'],
        'post_status' => 'publish',
        'post_date' => date( 'Y-m-d H:i:s' ),
        'post_type' => 'sell_media_item'
    );
    $post_id = wp_insert_post( $new_post );

    update_post_meta( $post_id, '_sell_media_is_package', true );
    update_post_meta( $post_id, '_sell_media_attached_file', $_POST['file'] );
    update_post_meta( $post_id, 'sell_media_price', $_POST['price'] );
    wp_set_post_terms( $post_id, $_POST['collection'], 'collection', true );

    do_action( 'sell_media_package_uploader_additional_fields_meta', $post_id, $_POST );

    echo $post_id;
    
    die();
}
add_action( 'wp_ajax_sell_media_package_update', 'sell_media_package_update' );