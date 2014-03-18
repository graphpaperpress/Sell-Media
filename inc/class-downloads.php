<?php

Class SellMediaDownload {

    private $tmp_dir;

    public function __construct(){
        $wp_upload_dir = wp_upload_dir();
        $this->tmp_dir = $wp_upload_dir['basedir'] . '/sell_media/tmp/';

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
            $payment_id = urldecode( $_GET['payment_id'] );
            $product_id = urldecode( $_GET['product_id'] );

            $verified = $this->verify_download_link( $transaction_id, $payment_id );

            if ( $verified ) {

                $requested_file = get_post_meta( $product_id, '_sell_media_attached_file', true );
                $file_extension = sell_media_get_file_extension( $requested_file );
                $ctype = $this->ctype( $file_extension );


                if ( ! ini_get( 'safe_mode' ) ){
                    set_time_limit( 0 );
                }

                if ( function_exists('get_magic_quotes_runtime') && get_magic_quotes_runtime() ) {
                    set_magic_quotes_runtime(0);
                }

                nocache_headers();
                header("Robots: none");
                header("Content-Type: " . $ctype . "");
                header("Content-Description: File Transfer");
                header("Content-Disposition: attachment; filename=\"" . basename( $requested_file ) . "\"");
                header("Content-Transfer-Encoding: binary");

                // If this download is an image, generate the image sizes purchased and create a download
                $product_obj = new SellMediaProducts;
                if ( $product_obj->mimetype_is_image( get_post_meta( $product_id, '_sell_media_attachment_id', true ) ) ){
                    $this->download_image( $payment_id, $product_id );
                // Otherwise, just deliver the download
                } else {
                    $this->deliver_download( $requested_file );
                }

            } else {
                wp_die( __( 'You do not have permission to download this file', 'sell_media'), __( 'Purchase Verification Failed', 'sell_media' ) );
            }
            exit;
        }

        // Rend purchase receipt?
        if ( isset( $_GET['resend_email'] ) && isset( $_GET['payment_id'] ) ){
            $payment_obj = new SellMediaPayments;
            $payment_id = $_GET['payment_id'];
            $payment_email = get_meta_key( $payment_id, 'email' );

            $payment_obj->email_receipt( $payment_id, $payment_email );
        }
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
     * Downloads the correct size that was purchased.
     *
     * @param (int) $payment_id The payment ID for a purchase
     * @param (int) $product_id The product ID from a given payment
     */
    public function download_image( $payment_id=null, $product_id=null ){

        $attachment_id = get_post_meta( $product_id, '_sell_media_attachment_id', true );

        $payments_obj = new SellMediaPayments;
        $products = $payments_obj->get_products( $payment_id );

        $image_obj = new SellMediaImages;

        // determine size customer purchased for this item from this payment
        foreach( $products as $product ){

            if ( $product_id == $product['id'] ){

                // Since we no longer check if the image sold is available in the download sizes
                // we allow the buyer to download the original image if the size they purchased
                // is larger than the original image i.e., they can purchase a size they can never
                // download.
                //
                // Hence if they paid for the original, OR they paid for a larger image than
                // available they get the original image.
                $confirmed_size = $image_obj->get_downloadable_size( $product_id, $product['size']['id'] );

                // If this the customer purchased the original size, just download it and
                // set the filename to null
                if ( $confirmed_size == 'original' || $product['size']['id'] === 'original' ){
                    $filename = null;
                    $file_download = $this->protected_file_path( $product_id );
                }

                // If this is not an original size we generated the file download
                // along with adding the download size to the file name
                else {

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
                }
            }
        }

        $this->force_download( $file_download, $filename );

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
     * Retrieve the full path to the protected file
     *
     * @param (int)$product_id The id of the Sell Media item
     * @return (mixed) Full file path to the original item in the protected directory, false if the file does not exists
     */
    public function protected_file_path( $product_id=null ){

        $wp_upload_dir = wp_upload_dir();
        $file_attached_path = get_post_meta( $product_id, '_sell_media_attached_file', true );
        $file_path = $wp_upload_dir['basedir'] . '/sell_media/' . $file_attached_path;

        if ( file_exists( $file_path ) ) {
            $file = $file_path;
        } else {
            $file = $file_attached_path;
        }

        return $file;
    }


    /**
     * Deliver the download file
     *
     * If enabled, the file is symlinked to better support large file downloads
     *
     * @param $file (url to download)
     * @return void
     */
    public function deliver_download( $file = '' ) {

        /*
         * If symlinks are enabled, a link to the file will be created
         * This symlink is used to hide the true location of the file, even when the file URL is revealed
         * The symlink is deleted after it is used
         */

        if ( function_exists( 'symlink' ) ) {

            // Generate a symbolic link
            $ext       = sell_media_get_file_extension( $file );
            $parts     = explode( '.', $file );
            $name      = basename( $parts[0] );
            $md5       = md5( $file );
            $file_name = $name . '_' . substr( $md5, 0, -15 ) . '.' . $ext;
            $path      = sell_media_get_symlink_dir() . '/' . $file_name;
            $url       = sell_media_get_symlink_url() . '/' . $file_name;

            // Set a transient to ensure this symlink is not deleted before it can be used
            set_transient( md5( $file_name ), '1', 30 );

            // Schedule deletion of the symlink
            if ( ! wp_next_scheduled( 'sell_media_cleanup_file_symlinks' ) )
                wp_schedule_single_event( current_time( 'timestamp' )+60, 'sell_media_cleanup_file_symlinks' );

            // Make sure the symlink doesn't already exist before we create it
            if ( ! file_exists( $path ) )
                $link = symlink( $file, $path );
            else
                $link = true;

            if ( $link ) {
                // Send the browser to the file
                header( 'Location: ' . $url );
            } else {
                $this->chunked( $file );
            }

        } else {

            // Read the file and deliver it in chunks
            $this->chunked( $file );

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
    public function chunked( $file, $retbytes = true ) {

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

    /**
     * Get the file content type
     *
     * @access   public
     * @param    string    file extension
     * @return   string
     */
    public function ctype( $extension ) {
        switch( $extension ):
            case 'ac'       : $ctype = "application/pkix-attr-cert"; break;
            case 'adp'      : $ctype = "audio/adpcm"; break;
            case 'ai'       : $ctype = "application/postscript"; break;
            case 'aif'      : $ctype = "audio/x-aiff"; break;
            case 'aifc'     : $ctype = "audio/x-aiff"; break;
            case 'aiff'     : $ctype = "audio/x-aiff"; break;
            case 'air'      : $ctype = "application/vnd.adobe.air-application-installer-package+zip"; break;
            case 'apk'      : $ctype = "application/vnd.android.package-archive"; break;
            case 'asc'      : $ctype = "application/pgp-signature"; break;
            case 'atom'     : $ctype = "application/atom+xml"; break;
            case 'atomcat'  : $ctype = "application/atomcat+xml"; break;
            case 'atomsvc'  : $ctype = "application/atomsvc+xml"; break;
            case 'au'       : $ctype = "audio/basic"; break;
            case 'aw'       : $ctype = "application/applixware"; break;
            case 'avi'      : $ctype = "video/x-msvideo"; break;
            case 'bcpio'    : $ctype = "application/x-bcpio"; break;
            case 'bin'      : $ctype = "application/octet-stream"; break;
            case 'bmp'      : $ctype = "image/bmp"; break;
            case 'boz'      : $ctype = "application/x-bzip2"; break;
            case 'bpk'      : $ctype = "application/octet-stream"; break;
            case 'bz'       : $ctype = "application/x-bzip"; break;
            case 'bz2'      : $ctype = "application/x-bzip2"; break;
            case 'ccxml'    : $ctype = "application/ccxml+xml"; break;
            case 'cdmia'    : $ctype = "application/cdmi-capability"; break;
            case 'cdmic'    : $ctype = "application/cdmi-container"; break;
            case 'cdmid'    : $ctype = "application/cdmi-domain"; break;
            case 'cdmio'    : $ctype = "application/cdmi-object"; break;
            case 'cdmiq'    : $ctype = "application/cdmi-queue"; break;
            case 'cdf'      : $ctype = "application/x-netcdf"; break;
            case 'cer'      : $ctype = "application/pkix-cert"; break;
            case 'cgm'      : $ctype = "image/cgm"; break;
            case 'class'    : $ctype = "application/octet-stream"; break;
            case 'cpio'     : $ctype = "application/x-cpio"; break;
            case 'cpt'      : $ctype = "application/mac-compactpro"; break;
            case 'crl'      : $ctype = "application/pkix-crl"; break;
            case 'csh'      : $ctype = "application/x-csh"; break;
            case 'css'      : $ctype = "text/css"; break;
            case 'cu'       : $ctype = "application/cu-seeme"; break;
            case 'davmount' : $ctype = "application/davmount+xml"; break;
            case 'dbk'      : $ctype = "application/docbook+xml"; break;
            case 'dcr'      : $ctype = "application/x-director"; break;
            case 'deploy'   : $ctype = "application/octet-stream"; break;
            case 'dif'      : $ctype = "video/x-dv"; break;
            case 'dir'      : $ctype = "application/x-director"; break;
            case 'dist'     : $ctype = "application/octet-stream"; break;
            case 'distz'    : $ctype = "application/octet-stream"; break;
            case 'djv'      : $ctype = "image/vnd.djvu"; break;
            case 'djvu'     : $ctype = "image/vnd.djvu"; break;
            case 'dll'      : $ctype = "application/octet-stream"; break;
            case 'dmg'      : $ctype = "application/octet-stream"; break;
            case 'dms'      : $ctype = "application/octet-stream"; break;
            case 'doc'      : $ctype = "application/msword"; break;
            case 'docx'     : $ctype = "application/vnd.openxmlformats-officedocument.wordprocessingml.document"; break;
            case 'dotx'     : $ctype = "application/vnd.openxmlformats-officedocument.wordprocessingml.template"; break;
            case 'dssc'     : $ctype = "application/dssc+der"; break;
            case 'dtd'      : $ctype = "application/xml-dtd"; break;
            case 'dump'     : $ctype = "application/octet-stream"; break;
            case 'dv'       : $ctype = "video/x-dv"; break;
            case 'dvi'      : $ctype = "application/x-dvi"; break;
            case 'dxr'      : $ctype = "application/x-director"; break;
            case 'ecma'     : $ctype = "application/ecmascript"; break;
            case 'elc'      : $ctype = "application/octet-stream"; break;
            case 'emma'     : $ctype = "application/emma+xml"; break;
            case 'eps'      : $ctype = "application/postscript"; break;
            case 'epub'     : $ctype = "application/epub+zip"; break;
            case 'etx'      : $ctype = "text/x-setext"; break;
            case 'exe'      : $ctype = "application/octet-stream"; break;
            case 'exi'      : $ctype = "application/exi"; break;
            case 'ez'       : $ctype = "application/andrew-inset"; break;
            case 'f4v'      : $ctype = "video/x-f4v"; break;
            case 'fli'      : $ctype = "video/x-fli"; break;
            case 'flv'      : $ctype = "video/x-flv"; break;
            case 'gif'      : $ctype = "image/gif"; break;
            case 'gml'      : $ctype = "application/srgs"; break;
            case 'gpx'      : $ctype = "application/gml+xml"; break;
            case 'gram'     : $ctype = "application/gpx+xml"; break;
            case 'grxml'    : $ctype = "application/srgs+xml"; break;
            case 'gtar'     : $ctype = "application/x-gtar"; break;
            case 'gxf'      : $ctype = "application/gxf"; break;
            case 'hdf'      : $ctype = "application/x-hdf"; break;
            case 'hqx'      : $ctype = "application/mac-binhex40"; break;
            case 'htm'      : $ctype = "text/html"; break;
            case 'html'     : $ctype = "text/html"; break;
            case 'ice'      : $ctype = "x-conference/x-cooltalk"; break;
            case 'ico'      : $ctype = "image/x-icon"; break;
            case 'ics'      : $ctype = "text/calendar"; break;
            case 'ief'      : $ctype = "image/ief"; break;
            case 'ifb'      : $ctype = "text/calendar"; break;
            case 'iges'     : $ctype = "model/iges"; break;
            case 'igs'      : $ctype = "model/iges"; break;
            case 'ink'      : $ctype = "application/inkml+xml"; break;
            case 'inkml'    : $ctype = "application/inkml+xml"; break;
            case 'ipfix'    : $ctype = "application/ipfix"; break;
            case 'jar'      : $ctype = "application/java-archive"; break;
            case 'jnlp'     : $ctype = "application/x-java-jnlp-file"; break;
            case 'jp2'      : $ctype = "image/jp2"; break;
            case 'jpe'      : $ctype = "image/jpeg"; break;
            case 'jpeg'     : $ctype = "image/jpeg"; break;
            case 'jpg'      : $ctype = "image/jpeg"; break;
            case 'js'       : $ctype = "application/javascript"; break;
            case 'json'     : $ctype = "application/json"; break;
            case 'jsonml'   : $ctype = "application/jsonml+json"; break;
            case 'kar'      : $ctype = "audio/midi"; break;
            case 'latex'    : $ctype = "application/x-latex"; break;
            case 'lha'      : $ctype = "application/octet-stream"; break;
            case 'lrf'      : $ctype = "application/octet-stream"; break;
            case 'lzh'      : $ctype = "application/octet-stream"; break;
            case 'lostxml'  : $ctype = "application/lost+xml"; break;
            case 'm3u'      : $ctype = "audio/x-mpegurl"; break;
            case 'm4a'      : $ctype = "audio/mp4a-latm"; break;
            case 'm4b'      : $ctype = "audio/mp4a-latm"; break;
            case 'm4p'      : $ctype = "audio/mp4a-latm"; break;
            case 'm4u'      : $ctype = "video/vnd.mpegurl"; break;
            case 'm4v'      : $ctype = "video/x-m4v"; break;
            case 'm21'      : $ctype = "application/mp21"; break;
            case 'ma'       : $ctype = "application/mathematica"; break;
            case 'mac'      : $ctype = "image/x-macpaint"; break;
            case 'mads'     : $ctype = "application/mads+xml"; break;
            case 'man'      : $ctype = "application/x-troff-man"; break;
            case 'mar'      : $ctype = "application/octet-stream"; break;
            case 'mathml'   : $ctype = "application/mathml+xml"; break;
            case 'mbox'     : $ctype = "application/mbox"; break;
            case 'me'       : $ctype = "application/x-troff-me"; break;
            case 'mesh'     : $ctype = "model/mesh"; break;
            case 'metalink' : $ctype = "application/metalink+xml"; break;
            case 'meta4'    : $ctype = "application/metalink4+xml"; break;
            case 'mets'     : $ctype = "application/mets+xml"; break;
            case 'mid'      : $ctype = "audio/midi"; break;
            case 'midi'     : $ctype = "audio/midi"; break;
            case 'mif'      : $ctype = "application/vnd.mif"; break;
            case 'mods'     : $ctype = "application/mods+xml"; break;
            case 'mov'      : $ctype = "video/quicktime"; break;
            case 'movie'    : $ctype = "video/x-sgi-movie"; break;
            case 'm1v'      : $ctype = "video/mpeg"; break;
            case 'm2v'      : $ctype = "video/mpeg"; break;
            case 'mp2'      : $ctype = "audio/mpeg"; break;
            case 'mp2a'     : $ctype = "audio/mpeg"; break;
            case 'mp21'     : $ctype = "application/mp21"; break;
            case 'mp3'      : $ctype = "audio/mpeg"; break;
            case 'mp3a'     : $ctype = "audio/mpeg"; break;
            case 'mp4'      : $ctype = "video/mp4"; break;
            case 'mp4s'     : $ctype = "application/mp4"; break;
            case 'mpe'      : $ctype = "video/mpeg"; break;
            case 'mpeg'     : $ctype = "video/mpeg"; break;
            case 'mpg'      : $ctype = "video/mpeg"; break;
            case 'mpg4'     : $ctype = "video/mpeg"; break;
            case 'mpga'     : $ctype = "audio/mpeg"; break;
            case 'mrc'      : $ctype = "application/marc"; break;
            case 'mrcx'     : $ctype = "application/marcxml+xml"; break;
            case 'ms'       : $ctype = "application/x-troff-ms"; break;
            case 'mscml'    : $ctype = "application/mediaservercontrol+xml"; break;
            case 'msh'      : $ctype = "model/mesh"; break;
            case 'mxf'      : $ctype = "application/mxf"; break;
            case 'mxu'      : $ctype = "video/vnd.mpegurl"; break;
            case 'nc'       : $ctype = "application/x-netcdf"; break;
            case 'oda'      : $ctype = "application/oda"; break;
            case 'oga'      : $ctype = "application/ogg"; break;
            case 'ogg'      : $ctype = "application/ogg"; break;
            case 'ogx'      : $ctype = "application/ogg"; break;
            case 'omdoc'    : $ctype = "application/omdoc+xml"; break;
            case 'onetoc'   : $ctype = "application/onenote"; break;
            case 'onetoc2'  : $ctype = "application/onenote"; break;
            case 'onetmp'   : $ctype = "application/onenote"; break;
            case 'onepkg'   : $ctype = "application/onenote"; break;
            case 'opf'      : $ctype = "application/oebps-package+xml"; break;
            case 'oxps'     : $ctype = "application/oxps"; break;
            case 'p7c'      : $ctype = "application/pkcs7-mime"; break;
            case 'p7m'      : $ctype = "application/pkcs7-mime"; break;
            case 'p7s'      : $ctype = "application/pkcs7-signature"; break;
            case 'p8'       : $ctype = "application/pkcs8"; break;
            case 'p10'      : $ctype = "application/pkcs10"; break;
            case 'pbm'      : $ctype = "image/x-portable-bitmap"; break;
            case 'pct'      : $ctype = "image/pict"; break;
            case 'pdb'      : $ctype = "chemical/x-pdb"; break;
            case 'pdf'      : $ctype = "application/pdf"; break;
            case 'pki'      : $ctype = "application/pkixcmp"; break;
            case 'pkipath'  : $ctype = "application/pkix-pkipath"; break;
            case 'pfr'      : $ctype = "application/font-tdpfr"; break;
            case 'pgm'      : $ctype = "image/x-portable-graymap"; break;
            case 'pgn'      : $ctype = "application/x-chess-pgn"; break;
            case 'pgp'      : $ctype = "application/pgp-encrypted"; break;
            case 'pic'      : $ctype = "image/pict"; break;
            case 'pict'     : $ctype = "image/pict"; break;
            case 'pkg'      : $ctype = "application/octet-stream"; break;
            case 'png'      : $ctype = "image/png"; break;
            case 'pnm'      : $ctype = "image/x-portable-anymap"; break;
            case 'pnt'      : $ctype = "image/x-macpaint"; break;
            case 'pntg'     : $ctype = "image/x-macpaint"; break;
            case 'pot'      : $ctype = "application/vnd.ms-powerpoint"; break;
            case 'potx'     : $ctype = "application/vnd.openxmlformats-officedocument.presentationml.template"; break;
            case 'ppm'      : $ctype = "image/x-portable-pixmap"; break;
            case 'pps'      : $ctype = "application/vnd.ms-powerpoint"; break;
            case 'ppsx'     : $ctype = "application/vnd.openxmlformats-officedocument.presentationml.slideshow"; break;
            case 'ppt'      : $ctype = "application/vnd.ms-powerpoint"; break;
            case 'pptx'     : $ctype = "application/vnd.openxmlformats-officedocument.presentationml.presentation"; break;
            case 'prf'      : $ctype = "application/pics-rules"; break;
            case 'ps'       : $ctype = "application/postscript"; break;
            case 'psd'      : $ctype = "image/photoshop"; break;
            case 'qt'       : $ctype = "video/quicktime"; break;
            case 'qti'      : $ctype = "image/x-quicktime"; break;
            case 'qtif'     : $ctype = "image/x-quicktime"; break;
            case 'ra'       : $ctype = "audio/x-pn-realaudio"; break;
            case 'ram'      : $ctype = "audio/x-pn-realaudio"; break;
            case 'ras'      : $ctype = "image/x-cmu-raster"; break;
            case 'rdf'      : $ctype = "application/rdf+xml"; break;
            case 'rgb'      : $ctype = "image/x-rgb"; break;
            case 'rm'       : $ctype = "application/vnd.rn-realmedia"; break;
            case 'rmi'      : $ctype = "audio/midi"; break;
            case 'roff'     : $ctype = "application/x-troff"; break;
            case 'rss'      : $ctype = "application/rss+xml"; break;
            case 'rtf'      : $ctype = "text/rtf"; break;
            case 'rtx'      : $ctype = "text/richtext"; break;
            case 'sgm'      : $ctype = "text/sgml"; break;
            case 'sgml'     : $ctype = "text/sgml"; break;
            case 'sh'       : $ctype = "application/x-sh"; break;
            case 'shar'     : $ctype = "application/x-shar"; break;
            case 'sig'      : $ctype = "application/pgp-signature"; break;
            case 'silo'     : $ctype = "model/mesh"; break;
            case 'sit'      : $ctype = "application/x-stuffit"; break;
            case 'skd'      : $ctype = "application/x-koan"; break;
            case 'skm'      : $ctype = "application/x-koan"; break;
            case 'skp'      : $ctype = "application/x-koan"; break;
            case 'skt'      : $ctype = "application/x-koan"; break;
            case 'sldx'     : $ctype = "application/vnd.openxmlformats-officedocument.presentationml.slide"; break;
            case 'smi'      : $ctype = "application/smil"; break;
            case 'smil'     : $ctype = "application/smil"; break;
            case 'snd'      : $ctype = "audio/basic"; break;
            case 'so'       : $ctype = "application/octet-stream"; break;
            case 'spl'      : $ctype = "application/x-futuresplash"; break;
            case 'spx'      : $ctype = "audio/ogg"; break;
            case 'src'      : $ctype = "application/x-wais-source"; break;
            case 'stk'      : $ctype = "application/hyperstudio"; break;
            case 'sv4cpio'  : $ctype = "application/x-sv4cpio"; break;
            case 'sv4crc'   : $ctype = "application/x-sv4crc"; break;
            case 'svg'      : $ctype = "image/svg+xml"; break;
            case 'swf'      : $ctype = "application/x-shockwave-flash"; break;
            case 't'        : $ctype = "application/x-troff"; break;
            case 'tar'      : $ctype = "application/x-tar"; break;
            case 'tcl'      : $ctype = "application/x-tcl"; break;
            case 'tex'      : $ctype = "application/x-tex"; break;
            case 'texi'     : $ctype = "application/x-texinfo"; break;
            case 'texinfo'  : $ctype = "application/x-texinfo"; break;
            case 'tif'      : $ctype = "image/tiff"; break;
            case 'tiff'     : $ctype = "image/tiff"; break;
            case 'torrent'  : $ctype = "application/x-bittorrent"; break;
            case 'tr'       : $ctype = "application/x-troff"; break;
            case 'tsv'      : $ctype = "text/tab-separated-values"; break;
            case 'txt'      : $ctype = "text/plain"; break;
            case 'ustar'    : $ctype = "application/x-ustar"; break;
            case 'vcd'      : $ctype = "application/x-cdlink"; break;
            case 'vrml'     : $ctype = "model/vrml"; break;
            case 'vsd'      : $ctype = "application/vnd.visio"; break;
            case 'vss'      : $ctype = "application/vnd.visio"; break;
            case 'vst'      : $ctype = "application/vnd.visio"; break;
            case 'vsw'      : $ctype = "application/vnd.visio"; break;
            case 'vxml'     : $ctype = "application/voicexml+xml"; break;
            case 'wav'      : $ctype = "audio/x-wav"; break;
            case 'wbmp'     : $ctype = "image/vnd.wap.wbmp"; break;
            case 'wbmxl'    : $ctype = "application/vnd.wap.wbxml"; break;
            case 'wm'       : $ctype = "video/x-ms-wm"; break;
            case 'wml'      : $ctype = "text/vnd.wap.wml"; break;
            case 'wmlc'     : $ctype = "application/vnd.wap.wmlc"; break;
            case 'wmls'     : $ctype = "text/vnd.wap.wmlscript"; break;
            case 'wmlsc'    : $ctype = "application/vnd.wap.wmlscriptc"; break;
            case 'wmv'      : $ctype = "video/x-ms-wmv"; break;
            case 'wmx'      : $ctype = "video/x-ms-wmx"; break;
            case 'wrl'      : $ctype = "model/vrml"; break;
            case 'xbm'      : $ctype = "image/x-xbitmap"; break;
            case 'xdssc'    : $ctype = "application/dssc+xml"; break;
            case 'xer'      : $ctype = "application/patch-ops-error+xml"; break;
            case 'xht'      : $ctype = "application/xhtml+xml"; break;
            case 'xhtml'    : $ctype = "application/xhtml+xml"; break;
            case 'xla'      : $ctype = "application/vnd.ms-excel"; break;
            case 'xlam'     : $ctype = "application/vnd.ms-excel.addin.macroEnabled.12"; break;
            case 'xlc'      : $ctype = "application/vnd.ms-excel"; break;
            case 'xlm'      : $ctype = "application/vnd.ms-excel"; break;
            case 'xls'      : $ctype = "application/vnd.ms-excel"; break;
            case 'xlsx'     : $ctype = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"; break;
            case 'xlsb'     : $ctype = "application/vnd.ms-excel.sheet.binary.macroEnabled.12"; break;
            case 'xlt'      : $ctype = "application/vnd.ms-excel"; break;
            case 'xltx'     : $ctype = "application/vnd.openxmlformats-officedocument.spreadsheetml.template"; break;
            case 'xlw'      : $ctype = "application/vnd.ms-excel"; break;
            case 'xml'      : $ctype = "application/xml"; break;
            case 'xpm'      : $ctype = "image/x-xpixmap"; break;
            case 'xsl'      : $ctype = "application/xml"; break;
            case 'xslt'     : $ctype = "application/xslt+xml"; break;
            case 'xul'      : $ctype = "application/vnd.mozilla.xul+xml"; break;
            case 'xwd'      : $ctype = "image/x-xwindowdump"; break;
            case 'xyz'      : $ctype = "chemical/x-xyz"; break;
            case 'zip'      : $ctype = "application/zip"; break;
            default         : $ctype = "application/force-download";
        endswitch;

        if ( wp_is_mobile() ) {
            $ctype = 'application/octet-stream';
        }

        return apply_filters( 'sell_media_file_ctype', $ctype );
    }
}
new SellMediaDownload;