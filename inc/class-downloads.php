<?php

/**
 * Downloads Class
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

Class SellMediaDownload {

    public function __construct(){
        add_action( 'init', array( &$this, 'download') , 100 );
    }


    /**
     * Set the file headers and force the download of a given file
     *
     * @return void
     */
    public function download(){

        if ( isset( $_GET['download'] ) && isset( $_GET['payment_id'] ) ) {

            $transaction_id = urldecode( $_GET['download'] );
            $payment_id     = urldecode( $_GET['payment_id'] );
            $product_id     = urldecode( $_GET['product_id'] );
            // Old download links might not have attachment_id set.
            // This means they were purchased before we added support
            // for multiple attachments. So, we just grab the first
            // attachment_id saved in post meta.
            $attachment_id  = ( ! empty( $_GET['attachment_id'] ) ) ? urldecode( $_GET['attachment_id'] ) : sell_media_get_attachment_id( $product_id );
            $size_id        = ( ! empty( $_GET['size_id'] ) ) ? urldecode( $_GET['size_id'] ) : null;

            $verified = $this->verify( $transaction_id, $payment_id );

            if ( $verified ) {

                // Filepath to package or attachment
                $requested_file = ( sell_media_is_package( $product_id ) ) ? sell_media_get_package_filepath( $product_id ) : get_attached_file( $attachment_id );

                if ( ! file_exists( $requested_file ) ) {
                    wp_die( __( 'The original high resolution file doesn\'t exist here: %1$s', 'sell_media' ), $requested_file );
                    exit();
                }

                $file_type = wp_check_filetype( $requested_file );

                if ( ! ini_get( 'safe_mode' ) ){
                    set_time_limit( 0 );
                }

                if ( function_exists( 'get_magic_quotes_runtime' ) && get_magic_quotes_runtime() ) {
                    set_magic_quotes_runtime(0);
                }

                if ( function_exists( 'apache_setenv' ) ) @apache_setenv('no-gzip', 1);
                @ini_set( 'zlib.output_compression', 'Off' );

                nocache_headers();
                header( "Robots: none" );
                header( "Content-Type: " . $file_type['type'] . "" );
                header( "Content-Description: File Transfer" );
                header( "Content-Disposition: attachment; filename=\"" . basename( $requested_file ) . "\"" );
                header( "Content-Transfer-Encoding: binary" );

                // If package, download it from correct packages path ($requested_file)
                if ( sell_media_is_package( $product_id ) ) {
                    $this->download_file( $requested_file );
                }
                // If image, generate the image sizes purchased and create a download
                elseif ( wp_attachment_is_image( $attachment_id ) ){
                    $this->download_image( $product_id, $attachment_id, $size_id );
                }
                // Otherwise, just deliver the download
                else {
                    // Get the original uploaded file in the sell_media dir
                    $file_path = Sell_Media()->products->get_protected_file( $product_id, $attachment_id );
                    $this->download_file( $file_path );
                }
                do_action( 'sell_media_after_successful_download', $product_id );
                exit();
            } else {
                do_action( 'sell_media_before_failed_download', $product_id, $attachment_id );
                wp_die( __( 'You do not have permission to download this file', 'sell_media'), __( 'Purchase Verification Failed', 'sell_media' ) );
            }
            exit;
        }

        // Rend purchase receipt?
        if ( isset( $_GET['resend_email'] ) && isset( $_GET['payment_id'] ) ){
            $payment_id = $_GET['payment_id'];
            $payment_email = get_meta_key( $payment_id, 'email' );

            Sell_Media()->payments->email_receipt( $payment_id, $payment_email );
        }
    }


    /**
     * Verifies a download purchase by checking if the post status is set to 'publish' for a
     * given purchase key;
     *
     * @param $download (string) The download hash
     * @return boolean
     */
    public function verify( $transaction_id=null, $payment_id=null ) {
        if ( $transaction_id == Sell_Media()->payments->get_meta_key( $payment_id, 'transaction_id' ) ){
            return true;
        }
    }


    /**
     * Resize and download an image to the specified dimensions
     * http://codex.wordpress.org/Class_Reference/WP_Image_Editor
     *
     * Returns the new image file path
     *
     * @since 1.8.5
     * @param $product_id
     * @param $attachment_id
     * @param $size_id
     * @return resized image file path
     */
    public function download_image( $product_id=null, $attachment_id=null, $size_id=null ) {
        $file_path = Sell_Media()->products->get_protected_file( $product_id, $attachment_id );
        $img = wp_get_image_editor( $file_path );
        if ( ! is_wp_error( $img ) ) {
            $width = sell_media_get_term_meta( $size_id, 'width', true );
            $height = sell_media_get_term_meta( $size_id, 'height', true );
            if ( $width || $height ) {
                if ( $width >= $height ) {
                    $max = $width;
                } else {
                    $max = $height;
                }
                $img->resize( $max, $max, false );
                $img->set_quality( 100 );
            }
            $img->stream();
        }
    }


    /**
     * Download helper for large files without changing PHP.INI
     * See https://github.com/EllisLab/CodeIgniter/wiki/Download-helper-for-large-files
     *
     * @access   public
     * @param    string  $file      The file
     * @param    boolean $retbytes  Return the bytes of file
     * @return   bool|string        If string, $status || $cnt
     */
    public function download_file( $file=null, $retbytes=true ) {

        $chunksize = 1024 * 1024;
        $buffer    = '';
        $cnt       = 0;
        $handle    = @fopen( $file, 'r' );

        if ( $size = @filesize( $file ) ) {
            header("Content-Length: " . $size );
        }

        if ( false === $handle ) {
            return false;
        }

        while ( ! @feof( $handle ) ) {
            $buffer = @fread( $handle, $chunksize );
            echo $buffer;

            if ( $retbytes ) {
                $cnt += strlen( $buffer );
            }
        }

        $status = @fclose( $handle );

        if ( $retbytes && $status ) {
            return $cnt;
        }

        return $status;
    }

}
new SellMediaDownload;