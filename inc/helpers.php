<?php
/**
 * Template Redirect
 * @since 1.0.4
 */
function sell_media_template_redirect(){
    $post_type = get_query_var('post_type');
    $sell_media_taxonomies = get_object_taxonomies( 'sell_media_item' );

    if ( $post_type == '' )
       $post_type = 'sell_media_item';

    $custom_templates = array(
        'single'  => plugin_dir_path( dirname( __FILE__ ) ) . 'themes/single-sell_media_item.php',
        'archive' => plugin_dir_path( dirname( __FILE__ ) ) . 'themes/archive-sell_media_item.php'
        );

    $default_templates = array(
        'single'   => locate_template( 'single-sell_media_item.php' ),
        'archive'  => locate_template( 'archive-sell_media_item.php' ),
        'taxonomy' => locate_template( 'taxonomy-' . get_query_var('taxonomy') . '.php' )
        );

    if ( is_single() && get_query_var('post_type') == 'sell_media_item' ) {

        /**
         * Single
         */
        if ( file_exists( $default_templates['single'] ) ) return;
        load_template( $custom_templates['single'] );
        exit;
    }

    /**
     * Archive -- Check if this is an archive page AND post type is sell media
     * OR is this a search request and is our post type sell media. If so we
     * load the the archive template.
     */
    elseif ( is_post_type_archive( $post_type ) && $post_type == 'sell_media_item' || isset( $_GET['s'] ) && ! empty( $_GET['post_type'] ) && $_GET['post_type'] == 'sell_media_item' ) {
        if ( file_exists( $default_templates['archive'] ) ) return;
        load_template( $custom_templates['archive'] );
        exit;
    } elseif ( is_tax() && in_array( get_query_var('taxonomy'), $sell_media_taxonomies ) ) {
        /**
         * Taxonomies
         */
        if ( file_exists( $default_templates['taxonomy'] ) ) return;
        load_template( $custom_templates['archive'] );
        exit;
    }

}
add_action( 'template_redirect', 'sell_media_template_redirect',6 );


/**
 * Loads a template from a specificed path
 *
 * @package Ajax
 * @uses load_template()
 * @since 0.1
 */
function sell_media_load_template() {
    load_template( dirname( plugin_dir_path( __FILE__ ) ) . '/themes/cart.php' );
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
    // Is there a user?
    if ( ! empty( $user->roles ) && is_array( $user->roles ) ) {
        // Is it an administrator?
        if ( in_array( 'administrator', $user->roles ) ){
            return admin_url();
        } else {
        // Send to dashboard page
            $options = get_option('sell_media_general_settings');
            $page_id = $options['dashboard_page'];
            return get_permalink( $page_id );
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
 * Parse the iptc info and retrive the given value.
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
    $payment_settings = get_option( 'sell_media_payment_settings' );
    return apply_filters( 'sell_media_currency', $payment_settings['currency'] );
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
    $general_settings = get_option( 'sell_media_general_settings' );
    return $general_settings['test_mode'];
}


/**
 * Use this to get the payment id, i.e. $post_id for a $post_type
 * of 'sell_mediapayment'.
 *
 * @param $name = _sell_media_payment_purchase_key | _sell_media_payment_user_email
 * @param $value = $purchase_key
 * @return full post object
 * @since 0.1
 */
function sell_media_get_payment_id_by( $key=null, $value=null ){
    switch( $value ) {
        case '_sell_media_payment_purchase_key':
            $value = '_sell_media_payment_purchase_key';
            break;
        case '_sell_media_payment_user_email':
            $value = '_sell_media_payment_user_email';
            break;
        default:
            break;
    }

    global $wpdb;
    $query = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '%s' AND meta_value = '%s'";
    $payment_id = $wpdb->get_results( $wpdb->prepare( $query, $key, $value ) );

    if ( is_null( $payment_id ) ){
        return fasle;
    } else {
        return $payment_id;
    }
}


/**
 * Determine if a payment is approved.
 *
 * Checks post status for a post type of 'sell_media_payment'
 * @param $payment_id
 * @return bool
 * @since 0.1
 */
function sell_media_is_payment_approved( $payment_id=null ){

    $status = get_post_status( $payment_id );

    if ( $status == 'publish' ){
        return true;
    } else {
        wp_die("Payment ID: {$payment_id} is still pending message?");
    }
}


/**
 * wp query on post meta $post_id = $product_id, unserizlie and return array of sell_media_item IDs
 *
 * @note this does NOT determine if the payment is approved!
 * @since 0.1
 */
function sell_media_get_cusotmer_products( $payment_id=null ){
    $payment_meta = get_post_meta( $payment_id, '_sell_media_payment_meta', true );

    return maybe_unserialize( $payment_meta['products'] );
}


/**
 * Builds download link url
 *
 * @since 0.1
 */
function sell_media_build_download_link( $payment_id=null, $customer_email=null ){
    $payment_meta = get_post_meta( $payment_id, '_sell_media_payment_meta', true );
    $products = maybe_unserialize( $payment_meta['products'] );

    $downloads = array();
    foreach( $products as $product ){
        if ( ! is_array( $product['price_id'] ) ) {
            $downloads[] = $product;
        }
    }

    $tmp_links = null;
    $links = null;

    foreach( $downloads as $download ) {
        if ( ! empty( $download['item_id'] ) ){
            $tmp_links['item_id'] = $download['item_id'];
            $tmp_links['price_id'] = $download['price_id'];
            $tmp_links['license_id'] = $download['license_id'];
            $tmp_links['thumbnail'] = sell_media_item_icon( get_post_meta( $download['item_id'], '_sell_media_attachment_id', true ), 'thumbnail', false );
            $tmp_links['url'] = site_url() . '?download=' . $payment_meta['purchase_key'] . '&email=' . $customer_email . '&id=' . $download['item_id'] . '&price_id=' . $download['price_id'];
            $tmp_links['payment_id'] = $payment_id;
        }
        $links[] = $tmp_links;
    }
    return $links;
}


/**
 * Retrives the purchase title from the purchase key
 * serialized array.
 *
 * @since 0.1
 */
function sell_media_purchase_info( $product_id=null ){

    $purchase = get_post_meta( $product_id, '_sell_media_payment_meta', true );
    $products = maybe_unserialize( $purchase['products'] );

    $purchase = array();
    foreach( $products as $product ){

        $tmp_term = get_term_by( 'id', $product['License'], 'licenses' );
        $tmp['title'] = get_the_title( $product['ProductID'] );
        $tmp['license'] = $tmp_term->name;
        $tmp['price'] = $product['CalculatedPrice'];

        $purchase[] = $tmp;
    }

    return $purchase;
}


/**
 * Get PHP Arg Seaparator Ouput
 *
 * @since 0.1
 */
function sell_media_get_php_arg_separator_output() {
    return ini_get('arg_separator.output');
}


/**
 * Change Downloads Upload Dir
 *
 * Hooks the sell_media_set_upload_dir filter when appropiate.
 *
 * @access private
 * @since 0.1
 * @return void
 */
function sell_media_change_downloads_upload_dir() {
    global $pagenow;

    if ( ! empty( $_POST['post_id'] ) && ( 'async-upload.php' == $pagenow || 'media-upload.php' == $pagenow ) ) {
        if ( 'sell_mediaproduct' == get_post_type( $_REQUEST['post_id'] ) ) {
            add_filter( 'upload_dir', 'sell_media_set_upload_dir' );
        }
    }
}
add_action('admin_init', 'sell_media_change_downloads_upload_dir', 999);


/**
 * Prints Upload Dir for use in moving attachments into products dir
 *
 * Sets the upload dir to /sell_media.
 *
 * @access private
 * @since 0.1
 * @return path
 */
function sell_media_get_upload_dir() {
    $upload = wp_upload_dir();
    $upload['subdir'] = SellMedia::upload_dir . $upload['subdir'];
    $upload['path'] = $upload['basedir'] . $upload['subdir'];
    $upload['url']  = $upload['baseurl'] . $upload['subdir'];
    return $upload;
}


/**
 * Prints a semantic list of Collections, with "Collection" as the
 * title, the term slug is used for additinonal styling of each li
 * and a sell_media-last class is used for the last item in the list.
 *
 * @since 0.1
 */
function sell_media_collections(){

    global $post;

    $taxonomy = 'collection';

    $terms = wp_get_post_terms( $post->ID, $taxonomy );

    if ( empty( $terms ) )
        return;

    $html = null;
    $count = count( $terms );
    $x = 0;

    foreach( $terms as $term ) {

        ( $x == ( $count - 1 ) ) ? $last = 'sell_media-last' : $last = null;

        $html .= '<a href="' . get_term_link( $term->slug, $taxonomy ) . '" title="' . $term->description . '">';
        $html .= $term->name;
        $html .= '</a> ';
        $x++;
    }

    do_action( 'sell_media_collections_before' );
    print $html;
    do_action( 'sell_media_collections_after' );
}


/**
 * Given an attachment ID prints the URI to the image.
 *
 * @since 0.1
 */
function sell_media_attachment_link( $attachment_id=null ){
    print get_attachment_link( $attachment_id );
}


/**
 * Print the link to a product based on the attachment ID
 *
 * @since 0.1
 */
function sell_media_item_link_by_attachment( $attachment_id=null ){
    $product_id = get_post_meta( $attachment_id, '_sell_media_for_sale_product_id', true );
    print get_permalink( $product_id );
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
 * Admin Sell Media Icon
 *
 * Echo the CSS for the Sell Media Item post type icon. This is lame.
 *
 * @since 0.1
 * @return void
*/

function sell_media_admin_menu_icon() {
    global $post_type;
    $icon_url = plugin_dir_url( dirname( __FILE__ ) ) . 'images/menu_icons.png';
    ?>
    <style type="text/css" media="screen">
        #adminmenu #menu-posts-sell_media_item div.wp-menu-image { background: transparent url( "<?php echo $icon_url; ?>" ) no-repeat 7px -26px; }
        #adminmenu #menu-posts-sell_media_item:hover div.wp-menu-image,
        #adminmenu #menu-posts-sell_media_item.wp-has-current-submenu div.wp-menu-image { background: transparent url( "<?php echo $icon_url; ?>" ) no-repeat 7px 5px; }
    </style>
    <?php
}
add_action( 'admin_head', 'sell_media_admin_menu_icon' );


/**
 * Sell Media Enqueue Styles
 *
 * Enqueue the Sell Media style chosen on the settings page.
 *
 * @since 1.2.6
 * @return void
*/
function sell_media_enqueue_styles(){
    $settings = get_option( 'sell_media_general_settings' );
    if ( isset( $settings['style'] ) && '' != $settings['style'] )
        wp_enqueue_style( 'sell-media-style', plugin_dir_url( dirname( __FILE__ ) ) . 'css/sell_media-' . $settings['style'] . '.css' );
    else
        wp_enqueue_style( 'sell-media-style', plugin_dir_url( dirname( __FILE__ ) ) . 'css/sell_media-light.css' );
}
add_action( 'wp_enqueue_scripts', 'sell_media_enqueue_styles' );


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
        'total' => $wp_query->max_num_pages
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
 * Updates the payment status
 *
 * @access public
 * @since 0.1
 * @return null
 */
function sell_media_update_payment_status($payment_id, $new_status = 'publish') {

    if ( $new_status == 'completed' || $new_status == 'complete' ) {
        $new_status = 'publish';
    }

    $payment = get_post($payment_id);

    $old_status = $payment->post_status;

    do_action( 'sell_media_before_update_payment_status', $payment_id, $new_status, $old_status );
    $id = wp_update_post( array( 'ID' => $payment_id, 'post_status' => $new_status ) );
    do_action( 'sell_media_after_update_payment_status', $payment_id, $new_status, $old_status );
    return $id;
}

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
    <fieldset class="sell-media-state-container">
        <select name="<?php print $name; ?>" <?php print $required; ?>>
            <option></option>
            <?php foreach( $items as $key => $value ) : ?>
                <option value="<?php print $key; ?>" <?php selected( $key, $current ); ?>><?php print $value; ?></option>
            <?php endforeach; ?>
        </select>
    </fieldset>
<?php }

function sell_media_state_province_list( $current=null ){
    $items = array(
        "AL" => "Alabama",
        "AK" => "Alaska",
        "AB" => "Alberta",
        "AS" => "American Samoa",
        "AZ" => "Arizona",
        "AR" => "Arkansas",
        "AE" => "Armed Forces Europe",
        "AA" => "Armed Forces Americas",
        "AC" => "Armed Forces Canada",
        "AM" => "Armed Forces Middle East",
        "AP" => "Armed Forces Pacific",
        "BC" => "British Columbia",
        "CA" => "California",
        "CO" => "Colorado",
        "CT" => "Connecticut",
        "DE" => "Delaware",
        "DC" => "District of Columbia",
        "FM" => "Federated States of Micronesia",
        "FL" => "Florida",
        "FP" => "Fleet Post Office",
        "GA" => "Georgia",
        "GU" => "Guam",
        "HI" => "Hawaii",
        "ID" => "Idaho",
        "IL" => "Illinois",
        "IN" => "Indiana",
        "IA" => "Iowa",
        "KS" => "Kansas",
        "KY" => "Kentucky",
        "LA" => "Louisiana",
        "ME" => "Maine",
        "MB" => "Manitoba",
        "MH" => "Marshall Islands",
        "MD" => "Maryland",
        "MA" => "Massachusetts",
        "MI" => "Michigan",
        "MN" => "Minnesota",
        "MS" => "Mississippi",
        "MO" => "Missouri",
        "MT" => "Montana",
        "NE" => "Nebraska",
        "NV" => "Nevada",
        "NB" => "New Brunswick",
        "NH" => "New Hampshire",
        "NJ" => "New Jersey",
        "NM" => "New Mexico",
        "NY" => "New York",
        "NL" => "Newfoundland and Labrador",
        "NC" => "North Carolina",
        "ND" => "North Dakota",
        "MP" => "Northern Mariana Islands",
        "NT" => "Northwest Territory",
        "NS" => "Nova Scotia",
        "NU" => "Nunavut",
        "OH" => "Ohio",
        "OK" => "Oklahoma",
        "ON" => "Ontario",
        "OR" => "Oregon",
        "PW" => "Palau",
        "PA" => "Pennsylvania",
        "PE" => "Prince Edward Island",
        "PR" => "Puerto Rico",
        "QC" => "Quebec",
        "RI" => "Rhode Island",
        "SK" => "Saskatchewan",
        "SC" => "South Carolina",
        "SD" => "South Dakota",
        "TN" => "Tennessee",
        "TX" => "Texas",
        "UT" => "Utah",
        "VT" => "Vermont",
        "VI" => "Virgin Islands",
        "VA" => "Virginia",
        "WA" => "Washington",
        "WV" => "West Virginia",
        "WI" => "Wisconsin",
        "WY" => "Wyoming",
        "YK" => "Yukon"
        );
    sell_media_build_select( $items, array( 'name' => 'sell_media_reprints_sf_state', 'required' => true, 'title' => 'State/Provience', 'current' => $current ) );
}

function sell_media_country_list( $current=null ){
    $items = array(
        "US" => "United States",
        "AF" => "Afghanistan",
        "AX" => "Åland Islands",
        "AL" => "Albania",
        "DZ" => "Algeria",
        "AS" => "American Samoa",
        "AD" => "Andorra",
        "AO" => "Angola",
        "AI" => "Anguilla",
        "AQ" => "Antarctica",
        "AG" => "Antigua and Barbuda",
        "AR" => "Argentina",
        "AM" => "Armenia",
        "AW" => "Aruba",
        "AU" => "Australia",
        "AT" => "Austria",
        "AZ" => "Azerbaijan, Republic of",
        "BS" => "Bahamas",
        "BH" => "Bahrain",
        "BD" => "Bangladesh",
        "BB" => "Barbados",
        "BY" => "Belarus",
        "BE" => "Belgium",
        "BZ" => "Belize",
        "BJ" => "Benin",
        "BM" => "Bermuda",
        "BT" => "Bhutan",
        "BO" => "Bolivia, Plurinational State of",
        "BQ" => "Bonaire, Sint Eustatius and Saba",
        "BA" => "Bosnia and Herzegovina",
        "BW" => "Botswana",
        "BV" => "Bouvet Island",
        "BR" => "Brazil",
        "IO" => "British Indian Ocean Territory",
        "BN" => "Brunei Darussalam",
        "BG" => "Bulgaria",
        "BF" => "Burkina Faso",
        "BI" => "Burundi",
        "KH" => "Cambodia",
        "CM" => "Cameroon",
        "CA" => "Canada",
        "CV" => "Cape Verde",
        "KY" => "Cayman Islands",
        "CF" => "Central African Republic",
        "TD" => "Chad",
        "CL" => "Chile",
        "CN" => "China",
        "CX" => "Christmas Island",
        "CC" => "Cocos (Keeling) Islands",
        "CO" => "Colombia",
        "KM" => "Comoros",
        "CG" => "Congo",
        "CD" => "Congo, the Democratic Republic of the",
        "CK" => "Cook Islands",
        "CR" => "Costa Rica",
        "CI" => "Côte d'Ivoire",
        "HR" => "Croatia",
        "CU" => "Cuba",
        "CW" => "Curaçao",
        "CY" => "Cyprus",
        "CZ" => "Czech Republic",
        "DK" => "Denmark",
        "DJ" => "Djibouti",
        "DM" => "Dominica",
        "DO" => "Dominican Republic",
        "EC" => "Ecuador",
        "EG" => "Egypt",
        "SV" => "El Salvador",
        "GQ" => "Equatorial Guinea",
        "ER" => "Eritrea",
        "EE" => "Estonia",
        "ET" => "Ethiopia",
        "FK" => "Falkland Islands (Malvinas)",
        "FO" => "Faroe Islands",
        "FJ" => "Fiji",
        "FI" => "Finland",
        "FR" => "France",
        "GF" => "French Guiana",
        "PF" => "French Polynesia",
        "TF" => "French Southern Territories",
        "GA" => "Gabon",
        "GM" => "Gambia",
        "GE" => "Georgia",
        "DE" => "Germany",
        "GH" => "Ghana",
        "GI" => "Gibraltar",
        "GR" => "Greece",
        "GL" => "Greenland",
        "GD" => "Grenada",
        "GP" => "Guadeloupe",
        "GU" => "Guam",
        "GT" => "Guatemala",
        "GG" => "Guernsey",
        "GN" => "Guinea",
        "GW" => "Guinea-Bissau",
        "GY" => "Guyana",
        "HT" => "Haiti",
        "HM" => "Heard Island and McDonald Islands",
        "VA" => "Holy See (Vatican City State)",
        "HN" => "Honduras",
        "HK" => "Hong Kong",
        "HU" => "Hungary",
        "IS" => "Iceland",
        "IN" => "India",
        "ID" => "Indonesia",
        "IR" => "Iran, Islamic Republic of",
        "IQ" => "Iraq",
        "IE" => "Ireland",
        "IM" => "Isle of Man",
        "IL" => "Israel",
        "IT" => "Italy",
        "JM" => "Jamaica",
        "JP" => "Japan",
        "JE" => "Jersey",
        "JO" => "Jordan",
        "KZ" => "Kazakhstan",
        "KE" => "Kenya",
        "KI" => "Kiribati",
        "KP" => "Korea, Democratic People's Republic of",
        "KR" => "Korea, Republic of",
        "KW" => "Kuwait",
        "KG" => "Kyrgyzstan",
        "LA" => "Lao People's Democratic Republic",
        "LV" => "Latvia",
        "LB" => "Lebanon",
        "LS" => "Lesotho",
        "LR" => "Liberia",
        "LY" => "Libyan Arab Jamahiriya",
        "LI" => "Liechtenstein",
        "LT" => "Lithuania",
        "LU" => "Luxembourg",
        "MO" => "Macao",
        "MK" => "Macedonia, the former Yugoslav Republic of",
        "MG" => "Madagascar",
        "MW" => "Malawi",
        "MY" => "Malaysia",
        "MV" => "Maldives",
        "ML" => "Mali",
        "MT" => "Malta",
        "MH" => "Marshall Islands",
        "MQ" => "Martinique",
        "MR" => "Mauritania",
        "MU" => "Mauritius",
        "YT" => "Mayotte",
        "MX" => "Mexico",
        "FM" => "Micronesia, Federated States of",
        "MD" => "Moldova, Republic of",
        "MC" => "Monaco",
        "MN" => "Mongolia",
        "ME" => "Montenegro",
        "MS" => "Montserrat",
        "MA" => "Morocco",
        "MZ" => "Mozambique",
        "MM" => "Myanmar",
        "NA" => "Namibia",
        "NR" => "Nauru",
        "NP" => "Nepal",
        "NL" => "Netherlands",
        "NC" => "New Caledonia",
        "NZ" => "New Zealand",
        "NI" => "Nicaragua",
        "NE" => "Niger",
        "NG" => "Nigeria",
        "NU" => "Niue",
        "NF" => "Norfolk Island",
        "MP" => "Northern Mariana Islands",
        "NO" => "Norway",
        "OM" => "Oman",
        "PK" => "Pakistan",
        "PW" => "Palau",
        "PS" => "Palestinian Territory, Occupied",
        "PA" => "Panama",
        "PG" => "Papua New Guinea",
        "PY" => "Paraguay",
        "PE" => "Peru",
        "PH" => "Philippines",
        "PN" => "Pitcairn",
        "PL" => "Poland",
        "PT" => "Portugal",
        "PR" => "Puerto Rico",
        "QA" => "Qatar",
        "RE" => "Réunion",
        "RO" => "Romania",
        "RU" => "Russian Federation",
        "RW" => "Rwanda",
        "BL" => "Saint Barthélemy",
        "SH" => "Saint Helena, Ascension and Tristan da Cunha",
        "KN" => "Saint Kitts and Nevis",
        "LC" => "Saint Lucia",
        "MF" => "Saint Martin (French part)",
        "PM" => "Saint Pierre and Miquelon",
        "VC" => "Saint Vincent and the Grenadines",
        "WS" => "Samoa",
        "SM" => "San Marino",
        "ST" => "Sao Tome and Principe",
        "SA" => "Saudi Arabia",
        "SN" => "Senegal",
        "RS" => "Serbia",
        "SC" => "Seychelles",
        "SL" => "Sierra Leone",
        "SG" => "Singapore",
        "SX" => "Sint Maarten (Dutch part)",
        "SK" => "Slovakia",
        "SI" => "Slovenia",
        "SB" => "Solomon Islands",
        "SO" => "Somalia",
        "ZA" => "South Africa",
        "GS" => "South Georgia and the South Sandwich Islands",
        "SS" => "South Sudan",
        "ES" => "Spain",
        "LK" => "Sri Lanka",
        "SD" => "Sudan",
        "SR" => "Suriname",
        "SJ" => "Svalbard and Jan Mayen",
        "SZ" => "Swaziland",
        "SE" => "Sweden",
        "CH" => "Switzerland",
        "SY" => "Syrian Arab Republic",
        "TW" => "Taiwan, Province of China",
        "TJ" => "Tajikistan",
        "TZ" => "Tanzania, United Republic of",
        "TH" => "Thailand",
        "TL" => "Timor-Leste",
        "TG" => "Togo",
        "TK" => "Tokelau",
        "TO" => "Tonga",
        "TT" => "Trinidad and Tobago",
        "TN" => "Tunisia",
        "TR" => "Turkey",
        "TM" => "Turkmenistan",
        "TC" => "Turks and Caicos Islands",
        "TV" => "Tuvalu",
        "UG" => "Uganda",
        "UA" => "Ukraine",
        "AE" => "United Arab Emirates",
        "GB" => "United Kingdom",
        "UM" => "United States Minor Outlying Islands",
        "UY" => "Uruguay",
        "UZ" => "Uzbekistan",
        "VU" => "Vanuatu",
        "VE" => "Venezuela, Bolivarian Republic of",
        "VN" => "Viet Nam",
        "VG" => "Virgin Islands, British",
        "VI" => "Virgin Islands, U.S.",
        "WF" => "Wallis and Futuna",
        "EH" => "Western Sahara",
        "YE" => "Yemen",
        "ZM" => "Zambia",
        "ZW" => "Zimbabwe"
        );
    sell_media_build_select( $items, array( 'name' => 'sell_media_country', 'required' => true, 'title' => 'Country', 'current' => $current ) );
}
