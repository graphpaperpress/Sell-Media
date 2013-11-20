<?php

Class Sell_Media_Download {

    private $tmp_dir;


    public function __construct(){
        $wp_upload_dir = wp_upload_dir();
        $this->tmp_dir = $wp_upload_dir['basedir'] . '/sell_media/tmp/';
    }


    /**
     * Create our Download image and save it to the tmp/ folder in sell media
     *
     * @param $size (array) containing the height and width of the image
     * @param $location (string) The full server path to the image
     *
     * @return Full path to the download file in the tmp/ folder
     */
    public function create_download_size( $size, $location=null ){

        $image_p = imagecreatetruecolor( $size['width'], $size['height'] );
        $image = imagecreatefromjpeg( $location );

        list( $current_image['width'], $current_image['height'] ) = getimagesize( $location );
        imagecopyresampled($image_p, $image, 0, 0, 0, 0, $size['width'], $size['height'], $current_image['width'], $current_image['height']);

        $destination_file = $this->tmp_dir . basename( $location );

        if ( ! file_exists( $destination_file ) ){
            wp_mkdir_p( dirname( $destination_file ) );
        }

        imagejpeg( $image_p, $destination_file, 100 );

        return $destination_file;
    }


    /**
     * Determine if we can generate a download size for this request image
     * Compares the requeste download size against the actual size of the original image.
     *
     * @param $size (array) 'width' and 'height' of requested size
     * @param $location (string) full file path on the server of the image
     *
     *
     * @return bool True if the download image size can be generated, false if it can't
     */
    public function validate_download_size( $size=array(), $location=null ){
        list( $current_image['width'], $current_image['height'] ) = getimagesize( $location );
        return ( $size['height'] >= $current_image['height'] || $size['width'] >= $current_image['width'] ) ? false : true;
    }


    /**
     * Set the file headers and force the download of a given file
     *
     * @param $location (string) The file path on the server
     * @param $delete (bool) Either delete the tmp file or not
     * @param $filename (string) Override the downloaded file name, default is derived from $location
     *
     * @return void
     */
    public function force_download( $location=null, $delete_tmp=false, $filename=null ){
        $pathinfo = pathinfo( $location );
        switch( $pathinfo['extension'] ) {
            case "gif":  $ctype = "image/gif"; break;
            case "png":  $ctype = "image/png"; break;
            case "jpeg": $ctype = "image/jpg"; break;
            case "jpg":  $ctype = "image/jpg"; break;
            case "pdf":  $ctype = "application/pdf"; break;
            case "zip":  $ctype = "application/octet-stream"; break;
            case "doc":  $ctype = "application/msword"; break;
            case "docx":  $ctype = "application/msword"; break;
            case "xls":  $ctype = "application/vnd.ms-excel"; break;
            case "ppt":  $ctype = "application/vnd.ms-powerpoint"; break;
            default:     $ctype = "application/force-download";
        }

        if ( ! ini_get( 'safe_mode' ) ){
            set_time_limit( 0 );
        }

        if ( function_exists('get_magic_quotes_runtime') && get_magic_quotes_runtime() ) {
            set_magic_quotes_runtime(0);
        }

        $size = filesize( $location );
        if ( empty( $filename ) )
            $filename = $pathinfo['basename'];

        header("Pragma: no-cache");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Robots: none");
        header("Content-Type: {$ctype}");
        header("Content-Disposition: attachment; filename={$filename};");
        header("Content-Length: {$size}");

        $tmp_file = file_get_contents( $location );
        echo $tmp_file;
        if ( $tmp_file !== true && $delete_tmp === true )
            unlink( $location );
    }


    /**
     * Verifies a download purchase by checking if the post status is set to 'publish' for a
     * given purchase key;
     *
     * @param $download (string) The download hash
     * @return boolean
     */
    public function verify_download_link( $download=null ) {

        /**
         * @note We use a subquery since this function is ran before register_post_type, hence we
         * cannot run WP_Query
         */
        global $wpdb;
        $query = $wpdb->prepare("SELECT ID FROM {$wpdb->prefix}posts WHERE `post_status` LIKE 'publish' AND ID =
        (SELECT post_id FROM {$wpdb->prefix}postmeta
        WHERE meta_key LIKE '_sell_media_payment_purchase_key'
        AND meta_value LIKE '%s');", $download );

        return $wpdb->get_results( $query ) ? true : false;
    }


    /**
     * Retrive the license(s) for a given item associated with a purchase/download
     *
     * @param $purchase_key (string) The purchase key to retrive the license from
     * @todo license should be stored at time of purchase with?
     *
     * @return Array of license false on failure
     */
    public function get_license( $purchase_key=null ){

        $products = $this->get_purchases( $purchase_key );
        $licenses = array();
        $tmp = array();
        foreach( $products as $product ){
            if ( ! empty( $product['license'] ) ){
                $tmp_term = get_term_by( 'id', $product['license']['id'], 'licenses' );
                if ( ! empty( $tmp_term ) ){
                    $tmp = array(
                        'id' => $product['license']['id'],
                        'name' => $tmp_term->name,
                        'description' => $tmp_term->description
                        );
                    $licenses[] = $tmp;
                }
            }
        }

        return empty( $licenses ) ? false : $licenses;
    }


    /**
     * Returns an array of purchases associated with a purchase key
     */
    public function get_purchases( $purchase_key=null ){

        // Run our payments query
        $args = array(
            'post_type' => 'sell_media_payment',
            'post_status' => 'publish',
            'meta_query' => array(
                'relation' => 'AND',
                    array(
                        'key' => '_sell_media_payment_purchase_key',
                        'value' => $purchase_key
                    )
                )
            );

        $payments = new WP_Query( $args );

        // Payment is still pending we just leave
        if ( $payments->post_count == 0 ){
            $products = false;
        } else {
            foreach( $payments->posts as $payment ) {
                $payment_meta = get_post_meta( $payment->ID, '_sell_media_payment_meta', true );
                $payment_id = $payment->ID;
                $products = maybe_unserialize( $payment_meta['products'] );
            }
        }

        return $products;
    }

}

/**
 * Handles the file download process.
 *
 * @access private
 * @since 1.0
 * @return void
 */
function sell_media_process_download() {

    if ( isset( $_GET['download'] ) && isset( $_GET['email'] ) ) {

        $download = urldecode($_GET['download']);
        $term_id = $_GET['price_id'];
        $item_id = $_GET['id'];

        $d = New Sell_Media_Download;
        $verified = $d->verify_download_link( $download );

        if ( $verified ) {

            /**
             * Get the full pat to the file that will be downloaded in the sell media dir
             */
            $wp_upload_dir = wp_upload_dir();
            $attachment_id = get_post_meta( $item_id, '_sell_media_attachment_id', true );

            /**
             * This is legacy code for older attached files
             */
            if ( $tmp = get_post_meta( $attachment_id, '_sell_media_attached_file', true ) ){
                $_attached_file = $tmp;
            } else {
                $_attached_file = get_post_meta( $attachment_id, '_wp_attached_file', true );
            }

            $location = $wp_upload_dir['basedir'] . '/sell_media/' . $_attached_file;

            /**
             * Check if this download is an image, if it is we generate the download size
             */
            $mime_type = wp_check_filetype( $location );
            $size = null;
            $license = null;

            if ( in_array( $mime_type['type'], array( 'image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/tiff' ) ) ){

                /**
                 * For images other than the original we need to add this fix
                 */
                $attached_file = get_post_meta( $attachment_id, $term_id, true );
                if ( empty( $attached_file ) ){
                    $attached_file = get_post_meta( $attachment_id, '_wp_attached_file', true );
                }


                /**
                 * Get license ID by $item_id and $download
                 */
                global $wpdb;
                $query = $wpdb->prepare("SELECT ID FROM {$wpdb->prefix}posts WHERE `post_status` LIKE 'publish' AND ID =
                (SELECT post_id FROM {$wpdb->prefix}postmeta
                WHERE meta_key LIKE '_sell_media_payment_purchase_key'
                AND meta_value LIKE '%s');", $download );

                $r = $wpdb->get_results( $query );
                $payment_id = $r[0]->ID;

                $products_s = get_post_meta( $payment_id, '_sell_media_payment_meta', true );
                $products = unserialize( $products_s['products'] );
                $license_id = null;
                $tmp_license_id = null;

                foreach( $products as $product ){
                    $tmp_item_id = isset( $product['item_id'] ) ? $product['item_id'] : $product['id'];

                    if ( isset( $product['license_id'] ) && ! empty( $product['license_id'] ) ){
                        $tmp_license_id = $product['license_id'];
                    }

                    if ( isset( $product['license']['id'] ) && ! empty( $product['license']['id'] ) ){
                        $tmp_license_id = $product['license']['id'];
                    }

                    if ( $item_id == $tmp_item_id ){
                        $license_id = $tmp_license_id;
                    }
                }

                $license_obj = get_term( $license_id, 'licenses' );
                if ( is_wp_error( $license_obj ) ){
                    $license = null;
                } else {
                    $license = '-' . $license_obj->slug;
                }
                // End get license


                if ( $term_id == 'sell_media_original_file' ){

                    $file_download = $location;
                    $delete_tmp = false;

                    list( $new_image['width'], $new_image['height'] ) = getimagesize( $file_download );

                } else {
                    $confirmed_size = sell_media_get_downloadable_size( $item_id, $term_id );

                    // We've come such a long way just to leave...
                    // Leave if this is a reprint purchase

                    if ( empty( $confirmed_size['width'] ) || empty( $confirmed_size['height'] ) )
                        exit;

                    $new_image = array(
                        'height' => $confirmed_size['height'],
                        'width'  => $confirmed_size['width']
                    );

                    $file_download = $d->create_download_size( $new_image, $location, true );
                    $delete_tmp = true;

                }
                $size = '-' . $new_image['width'] . 'x' . $new_image['height'];
            } else {

                $file_download = $location;
                $delete_tmp = false;
            }

            // Create unique name based on the file width, height and license
            $file_name_info = pathinfo( basename( $file_download ) );
            $filename = $file_name_info['filename'] . $size . $license . '.' . $file_name_info['extension'];

            $d->force_download( $file_download, $delete_tmp, $filename );
            exit;

        } else {
            wp_die(__('You do not have permission to download this file', 'sell_media'), __('Purchase Verification Failed', 'sell_media'));
        }
        exit;
    }

    if ( isset( $_GET['resend_email'] ) && isset( $_GET['resend_email'] ) && isset( $_GET['payment_id'] ) ){
        $purchase_key = get_post_meta( $_GET['payment_id'], '_sell_media_payment_purchase_key', true );
        $payment_email = get_post_meta( $_GET['payment_id'], '_sell_media_payment_user_email', true );

        sell_media_email_purchase_receipt( $purchase_key, $payment_email, $payment_id );
    }
}
add_action( 'init', 'sell_media_process_download', 100 );


function sell_media_string_attachment( $phpmailer ){

    global $_purchase_key;

    /**
     * Since this function is ran before licenses are init'd
     * we need to manually load them
     */
    $t = New SellMedia;
    $t->registerLicenses();

    $download_obj = New Sell_Media_Download;
    $licenses = $download_obj->get_license( $_purchase_key );

    $str_attachments = array();
    $tmp = array();
    foreach( $licenses as $licenses ){
        $tmp = array(
            'data' => $licenses['description'],
            'file_name' => 'license-' . strtolower( sanitize_file_name( $licenses['name'] ) ) . '.txt',
            'encoding' => 'base64',
            'type' => 'application/octet-stream',
        );
        $str_attachments[] = $tmp;
    }

    foreach( $str_attachments as $attachment ){
        $phpmailer->AddStringAttachment( $attachment['data'], $attachment['file_name'], $attachment['encoding'], $attachment['type'] );
    }

    $_purchase_key = null;

}


/**
 * Build the email to be sent to the user and send the email
 * containing download links for PUBLISHED items only
 *
 * @since 0.1
 */
function sell_media_email_purchase_receipt( $purchase_key=null, $email=null, $payment_id=null ) {

    $download_obj = New Sell_Media_Download;
    $products = $download_obj->get_purchases( $purchase_key );

    $message['from_name'] = get_bloginfo('name');
    $message['from_email'] = get_option('admin_email');

    $settings = sell_media_get_plugin_options();
    $message['subject'] = $settings->success_email_subject;
    $message['body'] = $settings->success_email_body;

    // Build the download links markup
    $links = null;
    $i = 0;

    $download_links = sell_media_build_download_link( $payment_id, $email );
    $count = count( $download_links );
    foreach( $download_links as $download ){

        // Derive price id for legacy items
        if ( empty( $products[ $i ]['price']['id'] ) ) {
            $tmp_price_id = isset( $products[ $i ] ) ? $products[ $i ]['price_id'] : null;
        } else {
            $tmp_price_id = $products[ $i ]['price']['id'];
        }

        if ( $tmp_price_id = 'sell_media_original_file' || get_term_by('id', $tmp_price_id, 'price-group') ){
            // Only add download links if this item is a DOWNLOAD!
            $links .= '<a href="' . $download['url'] . '">' . get_the_title( $download['item_id'] ) .'</a>';
            $comma = ( $i == $count - 1 ) ? null : ', ';
            $links .= $comma;
            $i++;
        } else {
            // echo 'reprint';
        }
    }
    //

    $tags = array(
        '{first_name}'  => get_post_meta( $payment_id, '_sell_media_payment_first_name', true ),
        '{last_name}'   => get_post_meta( $payment_id, '_sell_media_payment_last_name', true ),
        '{payer_email}' => get_post_meta( $payment_id, '_sell_media_payment_user_email',  true ),
        '{download_links}' => empty( $links ) ? null : $links
    );

    $message['body'] = str_replace( array_keys( $tags ), $tags, nl2br( $message['body'] ) );

    $message['headers'] = "From: " . stripslashes_deep( html_entity_decode( $message['from_name'], ENT_COMPAT, 'UTF-8' ) ) . " <{$message['from_email']}>\r\n";
    $message['headers'] .= "Reply-To: ". $message['from_email'] . "\r\n";
    $message['headers'] .= "MIME-Version: 1.0\r\n";
    $message['headers'] .= "Content-Type: text/html; charset=utf-8\r\n";

    /**
     * Check if we have additional test emails, if so we concatenate them
     */
    if ( ! empty( $settings->paypal_additional_test_email ) ){
        $email = $email . ', ' . $settings->paypal_additional_test_email;
    }

    // Call the mail object to add license if we have any
    global $_purchase_key;
    $_purchase_key = $purchase_key;
    add_action('phpmailer_init', 'sell_media_string_attachment');

    // Send the email
    $r = wp_mail( $email, $message['subject'], $message['body'], $message['headers'] );

    return ( $r ) ? "Sent to: {$email}" : "Failed to send to: {$email}";
}
