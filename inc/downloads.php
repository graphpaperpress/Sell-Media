<?php
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
        $price_id = $_GET['price_id'];
        $verified = sell_media_verify_download_link( $download );

        if ( $verified ) {


            /**
             * Get the file name from the attachment ID
             * and build the path to the attachment
             */
            $wp_upload_dir = wp_upload_dir();
            $attachment_id = get_post_meta( $_GET['id'], '_sell_media_attachment_id', true );
            $path = $wp_upload_dir['basedir'] . '/sell_media/';
            $full_file_path = $path . get_post_meta( $attachment_id, '_wp_attached_file', true );


            /**
             * Check if this download is an image, if it is we generate the download size (if its not the original).
             */
            $mime_type = wp_check_filetype( $full_file_path );
            if ( in_array( $mime_type['type'], array( 'image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/tiff' ) ) ){


                /**
                 * Due to the inconsistencies of the small/medium/large meta keys we need to add this fix
                 * @todo Resize fixes
                 */
                if ( $price_id == 'sell_media_original_file' ){
                    $price_id = '_sell_media_attached_file';
                }


                /**
                 * For images other than the original we need to add this fix
                 */
                $attached_file = get_post_meta( $attachment_id, $price_id, true );
                if ( empty( $attached_file ) ){
                    $attached_file = get_post_meta( $attachment_id, '_wp_attached_file', true );
                }


                /**
                 * We need to pair up keys like this "sell_media_small_file" with values like this "small"
                 * @todo Resize fixes
                 */
                if ( in_array( $price_id, array('sell_media_small_file','sell_media_medium_file','sell_media_large_file') ) ) {
                    $delete_tmp = true;
                    switch( $price_id ){
                        case 'sell_media_small_file':
                            $k = 'small';
                            break;
                        case 'sell_media_medium_file':
                            $k = 'medium';
                            break;
                        case 'sell_media_large_file':
                            $k = 'large';
                            break;
                    }


                    /**
                     * Determine the download height and width
                     */
                    $download_sizes = sell_media_image_sizes( $_GET['id'], false );
                    $new_image = array(
                        'height' => $download_sizes[ $k ]['height'],
                        'width' => $download_sizes[ $k ]['width']
                        );

                    list( $current_image['width'], $current_image['height'] ) = getimagesize( $full_file_path );

                    if ( $new_image['height'] >= $current_image['height'] || $new_image['width'] >= $current_image['width'] ) {
                        wp_die("This image is not available in the resolution of {$new_image['width']}x{$new_image['height']}");
                    }


                    /**
                     * Create the new image from our download sizes. Write the file (at 100% quality) out to disk,
                     * creating the folder tmp/ if its not already created.
                     */
                    $image_p = imagecreatetruecolor( $new_image['width'], $new_image['height'] );
                    $image = imagecreatefromjpeg( $full_file_path );
                    imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_image['width'], $new_image['height'], $current_image['width'], $current_image['height']);

                    $destination_file = $path . 'tmp/' . basename( $full_file_path );
                    if ( ! file_exists( $destination_file ) ){
                        wp_mkdir_p( dirname( $destination_file ) );
                    }

                    imagejpeg( $image_p, $destination_file, 100 );
                    $full_file_path = $destination_file;
                } else {
                    $delete_tmp = false;
                }
            }


            /**
             * Determine the file extension and then force downloading of the file
             */
            $pathinfo = pathinfo( $full_file_path );
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

            $size = filesize( $full_file_path );
            header("Pragma: no-cache");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Robots: none");
            header("Content-Type: {$ctype}");
            header("Content-Disposition: attachment; filename={$pathinfo['basename']};");
            header("Content-Length: {$size}");

            $download_result = file_get_contents( $full_file_path );
            print $download_result;


            /**
             * If this is a generated download size from our tmp/ directory we delete
             * the file after download.
             */
            if ( $download_result !== true && $delete_tmp === true )
                unlink( $full_file_path );

            exit;
        } else {
            wp_die(__('You do not have permission to download this file', 'sell_media'), __('Purchase Verification Failed', 'sell_media'));
        }
        exit;
    }
}
add_action( 'init', 'sell_media_process_download', 100 );


/**
 * Verifies a download purchase by checking if the post status is set to 'publish' for a
 * given purchase key;
 *
 * @access public
 * @since 0.1
 * @return boolean
 */
function sell_media_verify_download_link( $download ) {

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
 * Build the email to be sent to the user and send the email
 *
 * @since 0.1
 */
function sell_media_email_purchase_receipt( $purchase_key=null, $email=null, $payment_id=null ) {

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
    foreach( $payments->posts as $payment ) {
        $payment_meta = get_post_meta( $payment->ID, '_sell_media_payment_meta', true );
        $payment_id = $payment->ID;
        $downloads = maybe_unserialize( $payment_meta['products'] );
    }

    $from_name = get_bloginfo('name');
    $from_email = get_option('admin_email');
    $email_settings = get_option( 'sell_media_email_settings' );
    $subject = $email_settings['success_email_subject'];
    $body = $email_settings['success_email_body'];

    $links = null;
    $count = count( $download_links );
    $i = 0;

    $download_links = sell_media_build_download_link( $payment_id, $email );

    foreach( $download_links as $download ){
        $links .= '<a href="' . $download['url'] . '">' . get_the_title( $download['item_id'] ) .'</a>';
        $comma = ( $i == $count ) ? null : ', ';
        $links .= $comma;
        $i++;
    }

    $tags = array(
        '{first_name}'  => get_post_meta( $payment->ID, '_sell_media_payment_first_name', true ),
        '{last_name}'   => get_post_meta( $payment->ID, '_sell_media_payment_last_name', true ),
        '{payer_email}' => get_post_meta( $payment->ID, '_sell_media_payment_user_email',  true ),
        '{download_links}' => empty( $links ) ? null : $links
    );

    $body = str_replace( array_keys( $tags ), $tags, nl2br( $body ) );

    $headers = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
    $headers .= "Reply-To: ". $from_email . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=utf-8\r\n";

    return wp_mail( $email, $subject, $body, $headers);
}
