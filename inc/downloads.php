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

            // Get the file name from the attachment ID
            $wp_upload_dir = wp_upload_dir();
            $attachment_id = get_post_meta( $_GET['id'], '_sell_media_attachment_id', true );

            /**
             * Due to the inconsistencies of the small/medium/large meta keys we need to add this fix
             * @todo Resize fixes
             */
            if ( $price_id == 'sell_meida_original_file' ){
                $price_id = '_sell_media_attached_file';
            }

            $attached_file = get_post_meta( $attachment_id, $price_id, true );
            $ds = null;
            if ( empty( $attached_file ) ){
                $attached_file = get_post_meta( $attachment_id, '_wp_attached_file', true );
                $ds = '/';
            }
            /** End inconsistencies fix */


            /** Build the (full) system path to the download file */
            $file_path = $wp_upload_dir['basedir'] . $ds . $attached_file;

            $pathinfo = pathinfo( $file_path );

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

            $full_file_path = $wp_upload_dir['basedir'] . '/sell_media' . $ds . $attached_file;
            $size = filesize( $full_file_path );

            header("Pragma: no-cache");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Robots: none");
            header("Content-Type: {$ctype}");
            header("Content-Disposition: attachment; filename={$pathinfo['basename']};");
            header("Content-Length: {$size}");

            print file_get_contents( $full_file_path );

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
        $downloads = maybe_unserialize( $payment_meta['products'] );
    }

    $from_name = get_bloginfo('name');
    $from_email = get_option('admin_email');
    $email_settings = get_option( 'sell_media_email_settings' );
    $subject = $email_settings['success_email_subject'];
    $body = $email_settings['success_email_body'];

    $links = null;
    $count = count( $downloads );
    $i = 0;

    foreach( $downloads as $download ){
        $i++;
        $link = site_url() . '/?download=' . $purchase_key . '&email=' . $email . '&id=' . $download['AttachmentID'];
        $links .= '<a href="' . $link . '">' . get_the_title( $download['ProductID'] ) .'</a>';
        if ( $i != $count ){
            $links .= ', ';
        }
    }

    $tags = array(
        '{first_name}'  => get_post_meta( $payment->ID, '_sell_media_payment_first_name', true ),
        '{last_name}'   => get_post_meta( $payment->ID, '_sell_media_payment_last_name', true ),
        '{payer_email}' => get_post_meta( $payment->ID, '_sell_media_payment_user_email',  true ),
        '{download_links}' => $links
    );

    $body = str_replace( array_keys( $tags ), $tags, nl2br( $body ) );

    $headers = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
    $headers .= "Reply-To: ". $from_email . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=utf-8\r\n";

    wp_mail( $email, $subject, $body, $headers);
}