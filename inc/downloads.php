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
     * @param $image (array) containing the height and width of the image
     * @param $location (string) The full server path to the image
     *
     * @return Full path to the download file in the tmp/ folder
     */
    public function generate_download_size( $size, $location=null ){

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
     * @todo in this logic sell_media_image_sizes();
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
     *
     * @return void
     */
    public function force_download( $location=null, $delete_tmp=false ){
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
        header("Pragma: no-cache");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Robots: none");
        header("Content-Type: {$ctype}");
        header("Content-Disposition: attachment; filename={$pathinfo['basename']};");
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

        $d = New Sell_Media_Download;
        $verified = $d->verify_download_link( $download );


        if ( $verified ) {


            /**
             * Get the full pat to the file that will be downloaded in the sell media dir
             */
            $wp_upload_dir = wp_upload_dir();
            $attachment_id = get_post_meta( $_GET['id'], '_sell_media_attachment_id', true );
            $location = $wp_upload_dir['basedir'] . '/sell_media/' . get_post_meta( $attachment_id, '_sell_media_attached_file', true );


            /**
             * Check if this download is an image, if it is we generate the download size
             */
            $mime_type = wp_check_filetype( $location );
            if ( in_array( $mime_type['type'], array( 'image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/tiff' ) ) ){

                /**
                 * For images other than the original we need to add this fix
                 */
                $attached_file = get_post_meta( $attachment_id, $term_id, true );
                if ( empty( $attached_file ) ){
                    $attached_file = get_post_meta( $attachment_id, '_wp_attached_file', true );
                }


                if ( $term_id == 'sell_media_original_file' ){
                    $file_download = $location;
                    $delete_tmp = false;
                } else {
                    $new_image = array(
                        'height' => sell_media_get_term_meta( $term_id, 'height', true ),
                        'width' => sell_media_get_term_meta( $term_id, 'width', true )
                    );

                    // $valid = $d->validate_download_size( $new_image, $location );
                    // var_dump($valid);

                    $file_download = $d->generate_download_size( $new_image, $location, true );
                    $delete_tmp = true;
                }
            } else {
                $file_download = $location;
                $delete_tmp = false;
            }

            $d->force_download( $file_download, $delete_tmp );

            exit;

        } else {
            wp_die(__('You do not have permission to download this file', 'sell_media'), __('Purchase Verification Failed', 'sell_media'));
        }
        exit;
    }
}
add_action( 'init', 'sell_media_process_download', 100 );



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
    $i = 0;

    $download_links = sell_media_build_download_link( $payment_id, $email );
    $count = count( $download_links );

    foreach( $download_links as $download ){
        $links .= '<a href="' . $download['url'] . '">' . get_the_title( $download['item_id'] ) .'</a>';
        $comma = ( $i == $count - 1 ) ? null : ', ';
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

    /**
     * Check if we have additional test emails, if so we concatinate them
     */
    $additonal_emails = get_option( 'sell_media_payment_settings' );
    if ( ! empty( $additonal_emails['paypal_additional_test_email'] ) ){
        $email = ', ' . $additonal_emails['paypal_additional_test_email'];
    }

    return wp_mail( $email, $subject, $body, $headers);
}
