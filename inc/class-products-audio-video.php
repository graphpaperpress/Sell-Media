<?php

/**
 * Product Videos Class
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class SellMediaAudioVideo extends SellMediaProducts {

    function __construct(){
        add_filter( 'sell_media_quick_view_post_thumbnail', array( $this, 'quick_view_thumbnail' ), 10, 2 );

        add_filter( 'sell_media_grid_item_class', array( $this, 'add_class' ), 10, 2 );

        add_action( 'sell_media_after_options_meta_box', array( $this, 'add_meta_fields' ), 11 );

        add_action( 'sell_media_extra_meta_save', array( $this, 'save_meta_fields' ) );
        add_action( 'wp_ajax_check_attachment_is_audio_video', array( $this, 'check_attachment_is_audio_video' ) );

        add_filter( 'sell_media_quick_view_text', array( $this, 'preview_text' ), 10, 3 );

        add_filter( 'wp_get_attachment_url', array( $this, 'change_attachment_url' ), 9, 2 );
        add_action( 'init', array( $this, 'read_protected_file' ) );
        add_action( 'sell_media_before_content', array( $this, 'before_content' ) );
    }

    /**
     * Replace image with video.
     * @param  string $html    Post thumbnail.
     * @param  int $post_id Id of the post.
     * @return string          Updated video or image.
     */
    function quick_view_thumbnail( $html, $post_id ){
        $preview_url =  $this->get_preview( $post_id );
        if ( $preview_url ) {
            $html  = '<div class="sell-media-iframe-container">';
            $html .= $preview_url;
            $html .= '</div>';
        }

        return $html;
    }

    /**
     * Get Video/ audio preview.
     * @param  int $post_id ID of post.
     * @return string          Embed video/ audio.
     */
    function get_preview( $post_id ){
        if( self::is_video_item( $post_id ) || self::is_audio_item( $post_id ) ){

            $url = get_post_meta( $post_id, 'sell_media_embed_link', true );
            if ( '' != $url ) {
                return wp_oembed_get( esc_url( $url ), array( 'width' => 600 ) );
            }

        }

        return false;
    }

    /**
     * Add meta fields.
     * @param int $post_id ID of post.
     */
    function add_meta_fields( $post_id ){
        $embed_url = get_post_meta( $post_id, 'sell_media_embed_link', true );
        ?>
        <div id="sell-media-embed-link-field" class="sell-media-field" style="display:none;">
            <label for="sell-media-embed-link"><?php _e( 'Preview URL', 'sell_media' ); ?></label>
            <input name="sell_media_embed_link" id="sell-media-embed-link" class="" type="text" placeholder="" value="<?php echo esc_url( $embed_url ); ?>" />
        </div>
        <?php
    }

    /**
     * Save meta fields.
     * @param  int $post_id ID of post.
     * @return void          
     */
    function save_meta_fields( $post_id ){

        if( isset( $_POST['sell_media_embed_link'] ) ){
            update_post_meta( $post_id, 'sell_media_embed_link', esc_url_raw( $_POST['sell_media_embed_link'] ) );
        } 
    }

    /**
     * Check if attachment is audio or video.
     */
    function check_attachment_is_audio_video(){
        if( !is_admin() ){
            echo 'false';
            exit;
        }

        $attachment_id = absint( $_POST['attachment_id'] );

        $is_audio = self::is_attachment_audio( $attachment_id );
        $is_video = self::is_attachment_video( $attachment_id );

        if( $is_video || $is_audio ){
            echo 'true';
            exit;
        }

        echo "false";
        exit;
    }
    /**
     * Get first video from the post content.
     * @param  int $post_id Id of the post.
     * @return mixed          First video embed code or false.
     */
    function get_first_embed_media( $post_id ) {

        $post = get_post( $post_id );
        if ( $post && $post->post_content ) {
            $content = do_shortcode( apply_filters( 'the_content', $post->post_content ) );
            $videos =  get_media_embedded_in_content( $content ) ;
            if ( ! empty( $videos ) ) {
                return $videos[0];
            }
        }

        return false;
    }
    
    /**
     * Check if item is video type or not.
     * @param  int  $post_id ID of post.
     * @return boolean          True if type is video.
     */
    public static function is_video_item( $post_id ){
        $attachment_ids = sell_media_get_attachments ( $post_id );
        if( !empty( $attachment_ids ) ){
            foreach ($attachment_ids as $key => $attachment_id) {
                return self::is_attachment_video( $attachment_id );
            }
        }
    }

    /**
     * Check if attachment is video.
     * @param  int  $attachment_id ID of attachment.
     * @return boolean                True if is video.
     */
    public static function is_attachment_video( $attachment_id ){
        $type = get_post_mime_type($attachment_id);
        switch ($type) {
            case 'video/x-ms-asf' :
            case 'video/x-ms-wmv' :
            case 'video/x-ms-wmx' :
            case 'video/x-ms-wm' :
            case 'video/avi' :
            case 'video/divx' :
            case 'video/x-flv' :
            case 'video/quicktime' :
            case 'video/mpeg' :
            case 'video/mp4' :
            case 'video/ogg' :
            case 'video/webm' :
            case 'video/x-matroska' :
              return true; break;
            default:
              return false;
        }
    }

    /**
     * Check if item is audio type or not.
     * @param  int  $post_id ID of post.
     * @return boolean          True if type is audio.
     */
    public static function is_audio_item( $post_id ){
        $attachment_ids = sell_media_get_attachments ( $post_id );
        if( !empty( $attachment_ids ) ){
            foreach ($attachment_ids as $key => $attachment_id) {
                return self::is_attachment_audio( $attachment_id );
            }
        }
    }

    /**
     * Check if attachment is audio.
     * @param  int  $attachment_id ID of attachment.
     * @return boolean                True if is audio.
     */
    public static function is_attachment_audio( $attachment_id ){
        $type = get_post_mime_type($attachment_id);
        switch ($type) {
            case 'audio/mpeg' :
            case 'audio/x-realaudio' :
            case 'audio/wav' :
            case 'audio/ogg' :
            case 'audio/midi' :
            case 'audio/x-ms-wma' :
            case 'audio/x-ms-wax' :
            case 'audio/x-matroska' :
              return true; break;
            default:
              return false;
        }
    }

    /**
     * Add video class.
     * @param string $classes Class for the item.
     */
    function add_class( $classes, $post_id ){
        if( is_null( $post_id ) ){
            return $classes;
        }
        
        if( self::is_video_item( $post_id ) ){
            return $classes . ' sell-media-grid-single-video-item';
        }

        if( self::is_audio_item( $post_id ) ){
            return $classes . ' sell-media-grid-single-audio-item';
        }

        return $classes;
    }

    /**
     * Replace quick view text with preview.
     * @param  string $text          Quick view text.
     * @param  int $post_id       ID of post.
     * @param  int $attachment_id Attachment id.
     * @return string                Modified string.
     */
    function preview_text( $text, $post_id, $attachment_id ){
        if( self::is_video_item( $post_id ) || self::is_audio_item( $post_id ) )
            return __( 'Preview', 'sell_media' );
        return $text;
    }

    /**
     * Function to give custom url to protected files.
     * URL is visible only to the admins who can manage options.
     * @param  string $url           Default attachment url.
     * @param  int $attachment_id    ID of attachment.
     * @return string                Modified url.
     */
    function change_attachment_url( $url, $attachment_id ){
        // Check if user who can manage options is logged in admin section. 
        if( !is_admin() || !is_user_logged_in() || !current_user_can( 'manage_options' ) ){
            return $url;
        }

        if ( self::is_attachment_video( $attachment_id ) || self::is_attachment_audio( $attachment_id ) ){

            $upload_dir = wp_upload_dir();
            $file_path = str_replace( $upload_dir['baseurl'], '', $url );
            $file = $upload_dir['basedir'] . $file_path;

            // Check if file exits in default uploads folder.
            if( !file_exists( $file ) ){
                $url =  htmlspecialchars_decode( wp_nonce_url( home_url( '/?sell_media_id='.$attachment_id ), 'sell_media_attachment_nonce_action', 'sell_media_attachment_nonce'  ));
            }
        }


        return $url;
    }

    /**
     * Read protected file and render it in browser.
     */
    function read_protected_file(){
        if( isset( $_GET['sell_media_id'] ) &&  '' != $_GET['sell_media_id'] && is_user_logged_in() && isset( $_GET['sell_media_attachment_nonce'] ) && current_user_can( 'manage_options' ) ){

            // Verfiy nonce.
            if ( !wp_verify_nonce($_GET['sell_media_attachment_nonce'], 'sell_media_attachment_nonce_action')) {
                return;
            }

            $attachment_id = absint( $_GET['sell_media_id'] );
            $upload_dir = wp_upload_dir();
            $unprotected_file = get_attached_file( $attachment_id );
            $file_path = str_replace( $upload_dir['basedir'], '', $unprotected_file );
            $file = $upload_dir['basedir'] . '/sell_media'. $file_path;
            
            // Check if attachment is video or audio.
            if ( self::is_attachment_video( $attachment_id ) || self::is_attachment_audio( $attachment_id ) ){

                // Check if file exits.
                if ( ! file_exists( $file ) ) {
                    wp_die( __( 'The original high resolution file doesn\'t exist here: %1$s', 'sell_media' ), $file );
                    exit();
                }

                $file_type = wp_check_filetype( $file );

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
                header("Content-Disposition: inline;");
                header("Content-Transfer-Encoding: binary\n");
                header('Connection: close');

                if ( !wp_attachment_is_image( $attachment_id ) ){                    
                    Sell_Media()->download->download_file( $file );
                }
                exit;
            }

        }
    }

    function before_content( $post_id ) {
        $preview_url = $this->get_preview( $post_id );
        if ( $preview_url ){
            echo '<div class="sell-media-iframe-container">';
            echo $preview_url;
            echo '</div>';
        }
    }
}

new SellMediaAudioVideo();