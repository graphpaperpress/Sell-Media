<?php

Class SellMediaDownload {

    private $tmp_dir;

    public function __construct(){
        $wp_upload_dir = wp_upload_dir();
        $this->tmp_dir = $wp_upload_dir['basedir'] . '/sell_media/tmp/';

        add_action( 'init', array( &$this, 'process_download') , 100 );

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
     * Compares the request download size against the actual size of the original image.
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
    public function force_download( $location=null, $filename=null, $delete_tmp=false ){
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
    public function verify_download_link( $transaction_id=null, $payment_id=null ) {
        $payments_obj = new SellMediaPayments;

        if ( $transaction_id == $payments_obj->get_meta_key( $payment_id, 'transaction_id' ) ){
            $status = true;
        } else {
            $status = false;
        }

        return $status;
    }


    /**
     * Retrieve the license(s) for a given item associated with a purchase/download
     *
     * @param $purchase_key (string) The purchase key to retrive the license from
     * @todo license should be stored at time of purchase with?
     *
     * @return Array of license false on failure
     */
    public function get_license( $purchase_key=null ){

        $products = $this->get_purchases( $payment_id );

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


    /**
     * Retrieve the full path to the protected file
     *
     * @param (int)$product_id The id of the Sell Media item
     * @return (mixed) Full file path to the original item in the protected directory, false if the file does not exists
     */
    public function protected_file_path( $product_id=null ){

        $attachment_id = get_post_meta( $product_id, '_sell_media_attachment_id', true );
        $wp_upload_dir = wp_upload_dir();
        $file_attached_path = get_post_meta( $attachment_id, '_sell_media_attached_file', true );
        $file_path = $wp_upload_dir['basedir'] . '/sell_media/' . $file_attached_path;

        return file_exists( $file_path ) ? $file_path : false;
    }


    /**
     * Used to download a generic file
     */
    public function download_file( $download_file=null ){
        $this->force_download( $download_file );
        exit;
    }


    /**
     * Downloads the correct size that was purchased.
     *
     * @param (int)$payment_id The payment ID for a purchase
     * @param (int)$product_id The product ID from a given payment
     */
    public function download_image( $payment_id=null, $product_id=null ){

        $attachment_id = get_post_meta( $product_id, '_sell_media_attachment_id', true );

        $payments_obj = new SellMediaPayments;
        $products = $payments_obj->get_products( $payment_id );

        // test value
        // @todo size id needs to be passed in
        // $products[0]['size']['id'] = 704;

        // determine size customer purchased for this item from this payment
        foreach( $products as $product ){
            $size_id = $product['size']['id'];
        }

        if ( $size_id == 'original' ){

            $this->download_file( $this->protected_file_path( $product_id ) );

        } else {

            $confirmed_size = sell_media_get_downloadable_size( $product_id, $size_id );

            if ( empty( $confirmed_size['width'] ) || empty( $confirmed_size['height'] ) )
                exit;

            $new_image = array(
                'height' => $confirmed_size['height'],
                'width'  => $confirmed_size['width']
            );

            $file_download = $this->create_download_size( $new_image, $this->protected_file_path( $product_id ) , true );

            // Create unique name based on the file width, height and license
            $file_name_info = pathinfo( basename( $file_download ) );
            $size = '-' . $new_image['width'] . 'x' . $new_image['height'];

            // @todo derive license
            $license = null;
            $filename = $file_name_info['filename'] . $size . $license . '.' . $file_name_info['extension'];

            $this->force_download( $file_download, $filename );
        }
    }


    /**
     * Handles the file download process.
     *
     * @access private
     * @since 1.0
     * @return void
     */
    public function process_download() {

        if ( isset( $_GET['download'] ) && isset( $_GET['payment_id'] ) ) {

            $transaction_id = urldecode( $_GET['download'] );
            $payment_id = urldecode( $_GET['payment_id'] );
            $product_id = urldecode( $_GET['product_id'] );

            $verified = $this->verify_download_link( $transaction_id, $payment_id );

            if ( $verified ) {

                $download_file = $this->protected_file_path( $product_id );
                $mime_type = wp_check_filetype( $download_file );


                if ( in_array( $mime_type['type'], array( 'image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/tiff' ) ) ){
                    $this->download_image( $payment_id, $product_id );
                } else {
                    $this->download_file( $download_file );
                }

            } else {
                wp_die(__('You do not have permission to download this file', 'sell_media'), __('Purchase Verification Failed', 'sell_media'));
            }
            exit;
        }

        if ( isset( $_GET['resend_email'] ) && isset( $_GET['resend_email'] ) && isset( $_GET['payment_id'] ) ){
            $purchase_key = get_post_meta( $_GET['payment_id'], '_sell_media_payment_purchase_key', true );
            $payment_email = get_post_meta( $_GET['payment_id'], '_sell_media_payment_user_email', true );

            $payment_obj = new SellMediaPayments;
            $payment_obj->email_receipt( $purchase_key, $payment_email, $payment_id );
        }
    }
}
new SellMediaDownload;