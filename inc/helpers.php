<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Template Redirect
 * @since 1.0.4
 */
function sell_media_template_redirect( $original_template ){

    $post_type = get_query_var('post_type');
    $sell_media_taxonomies = get_object_taxonomies( 'sell_media_item' );

    if ( $post_type == '' )
       $post_type = 'sell_media_item';

    $default_templates = array(
        'single'  => plugin_dir_path( dirname( __FILE__ ) ) . 'themes/single-sell_media_item.php',
        'archive' => plugin_dir_path( dirname( __FILE__ ) ) . 'themes/archive-sell_media_item.php'
        );

    $custom_templates = array(
        'single'   => locate_template( 'single-sell_media_item.php' ),
        'archive'  => locate_template( 'archive-sell_media_item.php' ),
        'taxonomy' => locate_template( 'taxonomy-' . get_query_var('taxonomy') . '.php' )
        );

    /**
     * Single
     */
    if ( is_single() && get_query_var('post_type') == 'sell_media_item' ) {
        $template = ( file_exists( $custom_templates['single'] ) ) ? $custom_templates['single'] : $default_templates['single'];
    }

    /**
     * Archive -- Check if this is an archive page AND post type is sell media
     */
    elseif ( is_post_type_archive( $post_type ) && $post_type == 'sell_media_item' ) {
        $template = ( file_exists( $custom_templates['archive'] ) ) ? $custom_templates['archive'] : $default_templates['archive'];
    }

    /**
     * Taxonomies
     */
    elseif ( is_tax() && in_array( get_query_var('taxonomy'), $sell_media_taxonomies ) ) {
        // check if taxonomy template file exists in active theme
        if ( file_exists( $custom_templates['taxonomy'] ) ) {
            $template = $custom_templates['taxonomy'];
        // cehck if archive template file exists in active theme
        } elseif ( file_exists( $custom_templates['archive'] ) ) {
            $template = $custom_templates['archive'];
        // otherwise, use the archive-sell_media_item.php template in plugin
        } else {
            $template = $default_templates['archive'];
        }
    }

    else {
        $template = $original_template;
    }

    return $template;
}
add_action( 'template_include', 'sell_media_template_redirect', 6 );


/**
 * Loads a template from a specified path
 *
 * @package Ajax
 * @uses load_template()
 * @since 0.1
 */

function sell_media_load_template() {

    if ( $overridden_template = locate_template( 'cart.php' ) ) {
        load_template( apply_filters( 'sell_media_cart_template', $overridden_template ) );
    } else {
        load_template( apply_filters( 'sell_media_cart_template', SELL_MEDIA_PLUGIN_DIR . '/themes/cart.php' ) );
    }
    die();
}
add_action( 'wp_ajax_nopriv_sell_media_load_template', 'sell_media_load_template' );
add_action( 'wp_ajax_sell_media_load_template', 'sell_media_load_template' );


/**
 * Redirect admins to the WP dashboard and other users Sell Media Dashboard
 *
 * @package Sell Media
 * @since 1.4.6
 */
function sell_media_redirect_login_dashboard( $redirect_to, $request, $user ) {
    global $user;
    if ( isset( $user->roles ) && is_array( $user->roles ) ){
        if ( in_array( "sell_media_customer", $user->roles ) ){
            return site_url('dashboard');
        } else {
            return admin_url();
        }
    }
}
add_filter( 'login_redirect', 'sell_media_redirect_login_dashboard', 10, 3 );


/**
 * Builds html select field
 *
 * @since 0.1
 */
function sell_media_build_options( $taxonomy=null ) {

    if ( is_array( $taxonomy ) )
        extract( $taxonomy );

    if ( !isset( $label ) )
        $label = $taxonomy;

    // @todo need to merge
    $defaults = array(
        'value' => 'term_id'
    );

    // white list
    if ( empty( $prepend ) )
        $prepend = null;

    if ( empty( $current_term ) )
        $current_term = null;

    extract( $defaults );

    /** All Terms */
    $args = array(
        'orderby' => 'name',
        'hide_empty' => false
         );

    $terms = null;

    if ( isset( $post_id ) ) {
        $terms = wp_get_post_terms( $post_id, $taxonomy );
    } else {
        $terms = get_terms( $taxonomy, $args );
    }

    ?>
    <?php if ( $terms ) : ?>
        <?php do_action('sell_media_build_options_before'); ?>
        <?php foreach( $terms as $term ) : ?>
            <?php $price = str_replace( '%', '', sell_media_get_term_meta( $term->term_id, 'markup', true ) ); ?>
            <option
                value="<?php echo $prepend; ?><?php echo $term->$value; ?>"
                class="taxonomy-<?php echo $taxonomy; ?> term-<?php echo $term->slug; ?> <?php echo $taxonomy; ?>-<?php echo $term->term_id; ?>"
                data-value="<?php echo $term->slug; ?>"
                data-taxonomy="<?php echo $taxonomy; ?>"
                data-name="<?php echo $term->name; ?>"
                data-price="<?php echo $price; ?>"
                id="<?php echo $taxonomy; ?>-<?php echo $term->slug; ?>"
                title="<?php echo $term->description; ?>"
                name="<?php echo $taxonomy; ?>"
                >
            <?php echo $term->name; ?>
        </option>
        <?php endforeach; ?>
        </optgroup>
        <?php do_action('sell_media_build_options_after'); ?>
    <?php endif; ?>
<?php }


/**
 * Builds html input field (radio or checkbox)
 *
 * @since 0.1
 */
function sell_media_build_input( $taxonomy=null ) {

    if ( is_array( $taxonomy ) )
        extract( $taxonomy );

    if ( !isset( $label ) )
        $label = $taxonomy;

    // @todo need to merge
    $defaults = array(
        'value' => 'term_id'
    );

    // white list
    if ( empty( $prepend ) )
        $prepend = null;

    if ( empty( $current_term ) )
        $current_term = null;

    extract( $defaults );

    /** All Terms */
    $args = array(
        'orderby' => 'name',
        'hide_empty' => false
         );

    $terms = null;

    if ( isset( $post_id ) ) {
        $terms = wp_get_post_terms( $post_id, $taxonomy );
    } else {
        $terms = get_terms( $taxonomy, $args );
    }

    ?>
    <?php if ( $terms ) : ?>
        <?php do_action('sell_media_build_input_before'); ?>
        <?php foreach( $terms as $term ) : ?>
            <?php $price = sell_media_get_term_meta( $term->term_id, 'markup', true); ?>
            <input
                value="<?php echo $prepend; ?><?php echo $term->$value; ?>"
                class="taxonomy-<?php echo $taxonomy; ?> term-<?php echo $term->slug; ?> <?php echo $taxonomy; ?>-<?php echo $term->term_id; ?>"
                data-value="<?php echo $term->slug; ?>"
                data-taxonomy="<?php echo $taxonomy; ?>"
                data-name="<?php echo $term->name; ?>"
                data-price="<?php echo $price; ?>"
                id="<?php echo $taxonomy; ?>-<?php echo $term->slug; ?>"
                name="<?php echo $taxonomy; ?>"
                type="<?php echo $type; ?>"
                />
            <?php echo $term->name; ?> <?php if ( $price ) : ?>+<?php echo $price; ?>%<?php endif; ?><br />
        <?php endforeach; ?>
        <?php do_action('sell_media_build_input_after'); ?>
    <?php endif; ?>
<?php }


/**
 * Parse the iptc info and retrieve the given value.
 *
 * @since 0.1
 */
function sell_media_iptc_parser( $value=null, $image=null ){

    $size = getimagesize( $image, $info );

    if ( ! isset( $info['APP13'] ) )
        return;

    $iptc = iptcparse( $info['APP13'] );

    switch( $value ){
        case 'keywords':
            if ( isset( $iptc['2#025'] ) )
                return $iptc['2#025'];

        case 'city':
            if ( isset( $iptc['2#090'] ) )
                return $iptc['2#090'];

        case 'region':
            if ( isset( $iptc['2#095'] ) )
                return $iptc['2#095'];

        case 'country':
            if ( isset( $iptc['2#101'] ) )
                return $iptc['2#101'];

        default:
            return false;
    }
}


/**
 * Update/Saves iptc info as term. Does not check for valid iptc keys!
 *
 * @param $key 'string', see list of values in sell_media_iptc_parser();
 * @param $values the value that is lifted from sell_media_iptc_parser();
 * @param $post_id, duh, the post_id, NOT the attachment_id
 * @since 0.1
 */
function sell_media_iptc_save( $keys=null, $values=null, $post_id=null ){
    if ( is_null( $keys ) )
        return false;

    foreach( $values as $value ){
        $result = wp_set_post_terms( $post_id, $value, $keys, true );
    }
    return;
}


/**
 * Determine if we're on a Sell Media page in the admin
 *
 * @since 0.1
 */
function sell_media_is_sell_media_post_type_page(){

    if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'sell_media_item' )
        return true;
    else
        return false;
}


/**
 * Determine if the license page is being displayed on the admin
 *
 * @since 0.1
 */
function sell_media_is_license_page(){
    if ( isset( $_GET['action'] )
        && $_GET['action'] == 'edit'
        && isset( $_GET['taxonomy'] )
        && $_GET['taxonomy'] == 'licenses' ) {
        return true;
    } else {
        return false;
    }
}


/**
 * Determine if the license term page is being displayed on the admin
 *
 * @since 0.1
 */
function sell_media_is_license_term_page(){

    if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'sell_media_item' && isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] == 'licenses' )
        return true;
    else
        return false;
}


/**
 * Get Currency
 *
 * @since 0.1
 **/
function sell_media_get_currency() {
    $settings = sell_media_get_plugin_options();
    return apply_filters( 'sell_media_currency', empty( $settings->currency ) ? null : $settings->currency );
}


/**
 * Build currency values
 *
 * @since 0.1
 **/
function sell_media_get_currency_symbol( $currency = '' ) {
    if ( ! $currency ) $currency = sell_media_get_currency();
    $currency_symbol = '';
    switch ($currency) :
        case 'BRL' : $currency_symbol = 'R&#36;'; break; // in Brazil the correct is R$ 0.00,00
        case 'AUD' :
        case 'CAD' :
        case 'MXN' :
        case 'NZD' :
        case 'HKD' :
        case 'SGD' :
        case 'USD' : $currency_symbol = '&#36;'; break;
        case 'EUR' : $currency_symbol = '&euro;'; break;
        case 'RMB' :
        case 'JPY' : $currency_symbol = '&yen;'; break;
        case 'TRY' : $currency_symbol = 'TL'; break;
        case 'NOK' : $currency_symbol = 'kr'; break;
        case 'ZAR' : $currency_symbol = 'R'; break;
        case 'CZK' : $currency_symbol = '&#75;&#269;'; break;
        case 'MYR' : $currency_symbol = 'RM'; break;
        case 'DKK' :
        case 'HUF' :
        case 'ILS' :
        case 'PHP' :
        case 'PLN' :
        case 'SEK' :
        case 'CHF' :
        case 'TWD' :
        case 'THB' : $currency_symbol = $currency; break;
        case 'GBP' : $currency_symbol = '&pound;'; break;
        default    : $currency_symbol = '&#36;'; break;
    endswitch;
    return apply_filters( 'sell_media_currency_symbol', $currency_symbol, $currency );
}


/**
 * Returns the test mode option
 *
 * @since 0.1
 */
function sell_media_test_mode(){
    $settings = sell_media_get_plugin_options();
    return $settings->test_mode;
}


/**
 * Change order by on frontend
 *
 * @since 0.1
 * @return void
 */
function sell_media_order_by( $orderby_statement ) {

    $settings = sell_media_get_plugin_options();

    if ( ! empty( $settings->order_by ) && is_archive() ||
         ! empty( $settings->order_by ) && is_tax() ){
        global $wpdb;
        switch( $settings->order_by ){
            case 'title-asc' :
                $order_by = "{$wpdb->prefix}posts.post_title ASC";
                break;
            case 'title-desc' :
                $order_by = "{$wpdb->prefix}posts.post_title DESC";
                break;
            case 'date-asc' :
                $order_by = "{$wpdb->prefix}posts.post_date ASC";
                break;
            case 'date-desc' :
                $order_by = "{$wpdb->prefix}posts.post_date DESC";
                break;
        }
    } else {
        $order_by = $orderby_statement;
    }
    return $order_by;
}
if ( ! is_admin() )
    add_filter( 'posts_orderby', 'sell_media_order_by' );


/**
 * Returns the attachment ID file size
 *
 * @param $attachment_id ID of the attachment
 * @return string
 * @since 1.6.9
 */
function sell_media_get_size( $attachment_id=null ){

    $file_path = get_attached_file( $attachment_id );
    $bytes = filesize( $file_path );
    $s = array( 'b', 'Kb', 'Mb', 'Gb' );
    $e = floor( log( $bytes )/log( 1024 ) );
    return sprintf( '%.2f ' . $s[$e], ( $bytes/pow( 1024, floor( $e ) ) ) );
}


/**
 * Update the sales stats
 *
 * @since 0.1
 */
function sell_media_update_sales_stats( $product_id=null, $license_id=null, $price=null ){

    $prev = maybe_unserialize( get_post_meta( $product_id, 'sell_media_sales_stats', true ) );

    $new[ $license_id ]['count'] = $prev[ $license_id ]['count'] + 1;
    $new[ $license_id ]['total'] = $prev[ $license_id ]['total'] + $price;
    $sales_stats_s = serialize( $new );

    return update_post_meta( $product_id, 'sell_media_sales_stats', $sales_stats_s );
}


/**
 * Echos the pagination for Archive pages.
 *
 * @since 1.0.1
 */
function sell_media_pagination_filter(){

    global $wp_query;

    $big = 999999999; // need an unlikely integer

    $params = array(
        'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
        'format' => '?paged=%#%',
        'current' => max( 1, get_query_var('paged') ),
        'total' => $wp_query->max_num_pages // note sometimes max_num_pages needs to be sent over
        );

    $params = apply_filters( 'sell_media_pagination', $params );

    $links = paginate_links( $params );

    print '<div class="sell-media-pagination-container">' . $links . '</div>';
}


/**
 * Determine if the payment reports page is being displayed on the admin
 *
 * @since 1.2
 */
function sell_media_is_reports_page(){

    if ( 'post_type=sell_media_item&page=sell_media_reports' == $_SERVER['QUERY_STRING'] )
        return true;
    else
        return false;
}

/**
 * Get Plugin data
 *
 * @since 1.2
 */
function sell_media_plugin_data( $field=null ){
    $plugin_data = get_plugin_data( SELL_MEDIA_PLUGIN_FILE, $markup = true, $translate = true );
    return $plugin_data[$field];
}


/**
 * Build select fields
 *
 * @since 1.2
 */
function sell_media_build_select( $items=array(), $args=array() ){
    extract( $args );

    if ( $required ){
        $required = " required ";
    } else {
        $required = false;
        $required_html = false;
    }

    if ( ! $title ){
        $title = false;
    }

    if ( empty( $name ) )
        $name = null;

    if ( empty( $current ) )
        $current = null;
    ?>
    <select id="<?php print $name; ?>" class="sell_media_form_control" name="<?php print $name; ?>" <?php print $required; ?>>
        <option></option>
        <?php foreach( $items as $key => $value ) : ?>
            <option value="<?php print $key; ?>" <?php selected( $key, $current ); ?>><?php print $value; ?></option>
        <?php endforeach; ?>
    </select>
<?php }


/**
 * Return either the the custom price group or the default price group from settings
 * Used for showing price groups on cart popup
 *
 * @param $post_id, $taxonomy
 * @return $price_groups (object)
 */

function sell_media_get_price_groups( $post_id = NULL, $taxonomy = NULL ){

    // first, check price group set on the item
    $price_groups_custom = wp_get_post_terms( $post_id, $taxonomy );

    foreach( $price_groups_custom as $price_group ){
        if ( $price_group->parent == 0 ){
            $parent_price_group = $price_group->term_id;
        }
    }

    // if the item doesn't have a price group set, use the default from settings
    if ( empty( $price_groups_custom ) ){

        $settings = sell_media_get_plugin_options();

        if ( $taxonomy == 'reprints-price-group'){
            $price_group_id = $settings->reprints_default_price_group;
        } else {
            $price_group_id = $settings->default_price_group;
        }

        $default_price_group_obj = get_term( $price_group_id, $taxonomy );

        if ( is_null( $default_price_group_obj ) || is_wp_error( $default_price_group_obj ) )
            return;

        $parent_price_group = $default_price_group_obj->term_id;
    }

    $args = array(
        'type' => 'sell_media_item',
        'hide_empty' => false,
        'parent' => $parent_price_group,
        'taxonomy' => $taxonomy
        );

    $price_groups = get_categories( $args );

    return $price_groups;

}

/**
 * Retrieve the absolute path to the file upload directory without the trailing slash
 *
 * @since  1.8.5
 * @return string $path Absolute path to the sell_media upload directory
 */
function sell_media_get_upload_dir() {
    $wp_upload_dir = wp_upload_dir();
    wp_mkdir_p( $wp_upload_dir['basedir'] . '/sell_media' );
    $path = $wp_upload_dir['basedir'] . '/sell_media';

    return apply_filters( 'sell_media_get_upload_dir', $path );
}

/**
 * Retrieve the absolute path to the packages file upload directory without the trailing slash
 *
 * @since  1.8.5
 * @return string $path Absolute path to the sell_media/packages upload directory
 */
function sell_media_get_packages_upload_dir() {
    $wp_upload_dir = wp_upload_dir();
    wp_mkdir_p( $wp_upload_dir['basedir'] . '/sell_media/packages' );
    $path = $wp_upload_dir['basedir'] . '/sell_media/packages';

    return apply_filters( 'sell_media_get_packages_upload_dir', $path );
}


/**
 * Retrieve the url to the file upload directory without the trailing slash
 *
 * @since  1.8.5
 * @return string $url url to the sell_media upload directory
 */
function sell_media_get_upload_dir_url() {
    $wp_upload_dir = wp_upload_dir();
    $url = $wp_upload_dir['baseurl'] . '/sell_media';

    return apply_filters( 'sell_media_get_upload_dir_url', $url );
}

/**
 * Get File Extension
 *
 * Returns the file extension of a filename.
 *
 * @since 1.8.5
 * @param unknown $str File name
 * @return mixed File extension
 */
function sell_media_get_file_extension( $str ) {
    $parts = explode( '.', $str );
    return end( $parts );
}


/**
 * Get full system path to the originally uploaded file
 *
 * Returns the full system path to the file
 *
 * @since 1.8.5
 * @param product id ( post_id )
 * @return file path
 */
function sell_media_get_original_protected_file( $product_id=null ){

    // All uploads are saved to this meta field
    // Single uploads are saved like /2014/14/file.zip
    // Packages are saved like /packages/file.zip
    $attached_file = get_post_meta( $product_id, '_sell_media_attached_file', true );
    // Check if this item is a package and change the file location
    $is_package = get_post_meta( $product_id, '_sell_media_is_package', true );
    if ( $is_package ) {
        $file = sell_media_get_packages_upload_dir() . '/' . $attached_file;
    } else {
        $file = sell_media_get_upload_dir() . '/' . $attached_file;
    }

    if ( file_exists( $file ) )
        return $file;
    else
        return false;
}


/**
 * Resize an image to the specified dimensions
 * http://codex.wordpress.org/Class_Reference/WP_Image_Editor
 *
 * Returns the new image file path
 *
 * @since 1.8.5
 * @param file path
 * @param width
 * @param width
 * @return resized image file path
 */
function sell_media_resize_original_image( $product_id=null, $width=null, $height=null ){
    $file_path = sell_media_get_original_protected_file( $product_id );
    $img = wp_get_image_editor( $file_path );
    if ( ! is_wp_error( $img ) ) {
        // resize if height and width supplied
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
 * Is this an image?
 *
 * @since 1.0
 * @param string $file
 * @return bool (true/false)
 */
function sell_media_is_image( $file ) {
    $ext = sell_media_get_file_extension( $file );

    switch ( strtolower( $ext ) ) {
        case 'jpg';
            $return = true;
            break;
        case 'png';
            $return = true;
            break;
        case 'gif';
            $return = true;
            break;
        case 'tif';
            $return = true;
        case 'tiff';
            $return = true;
        case 'psd';
            $return = true;
        default:
            $return = false;
            break;
    }

    return (bool) apply_filters( 'sell_media_is_image_filter', $return, $file );
}