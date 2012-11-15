<?php

/**
 * This file handles redirecting of our templates to our given views
 * dir and anything else.
 *
 * Check if the themer has made a theme file in their
 * theme dir, if not load our default.
 *
 * @uses template_redirect http://codex.wordpress.org/Plugin_API/Action_Reference/template_redirect
 * @since 0.1
 */
function sell_media_redirect(){
    global $wp_query;

    /**
     * This checks if we are on a multi-site install and if so
     * we just set the index to 2.
     * Example cars/ is our network, i.e. http://site.com/cars/make/
     * therefore make/ is our $post_type
     */
    $url_index = 1;
    if ( is_multisite() ) {
        if ( ! SUBDOMAIN_INSTALL ) {
            $url_index = 2;
        }
    }

    if ( isset( $wp_query->query_vars['taxonomy'] ) ) {
        $queried_tax = $wp_query->query_vars['taxonomy'];
    } else {
        $queried_tax = null;
    }

    if ( get_query_var('post_type') ) {

        /**
         * Use our query var
         */
        $post_type = get_query_var('post_type');

    } else {

        /**
         * Use the current post_type
         *
         * If not we explode the url on / and use the
         * first index. Which maps to our controller
         * (file).
         */
        global $post;

        if ( $post ) {
            $post_type = $post->post_type;
        }
    }

    /**
     * If we cant determine a post type just leave
     */
    if ( empty( $post_type ) )
        return;

    /**
     * Derive the post type object, from here we derive the
     * list of taxonoimes.
     */
    $cpt_obj = get_post_types( array( 'name' => $post_type ), 'objects' );

    if ( $cpt_obj ){
        $taxonomies = $cpt_obj[ $post_type ]->taxonomies;
    }

    $template = array(
        'post_type' => $post_type,
        'single'    => plugin_dir_path( dirname( __FILE__ ) ) . 'themes/single-' . $post_type . '.php',
        'archive'   => plugin_dir_path( dirname( __FILE__ ) ) . 'themes/archive-' . $post_type . '.php',
        'search'    => plugin_dir_path( dirname( __FILE__ ) ) . 'themes/search-' . $post_type . '.php',
        'taxonomy'  => plugin_dir_path( dirname( __FILE__ ) ) . 'themes/taxonomy-' . $post_type . '.php',
        'default'   => plugin_dir_path( dirname( __FILE__ ) ) . 'themes/archive-' . $post_type . '.php',
        );

    $theme_dir = get_stylesheet_directory() . '/';
    $theme_files = array(
        'single' => $theme_dir . 'single-' . $post_type . '.php',
        'archive' => $theme_dir . 'archive-' . $post_type . '.php',
        'taxonomy' => $theme_dir . 'taxonomy-'
        );

    /**
     * If this is a single template, and the post type is
     * our custom post type.
     */
    if ( is_single() && get_query_var('post_type') == $post_type ) {
        if ( file_exists( $theme_files['single'] ) ) {
            load_template( $theme_files['single']  );
            exit();
        } elseif ( file_exists( $template['single'] ) ) {
            load_template( $template['single'] );
            exit();
        } else {
            return;
        }
    }

    /**
     * Check if this is a taxonomy, if so try loading
     * a template for EACH term in that taxonomy.
     * i.e., taxonomy-$term.php
     */
    elseif ( is_tax() ) {
        if ( ! empty( $taxonomies ) ){
            foreach( $taxonomies as $tax ){
                if ( $queried_tax == $tax ) {
                    if ( file_exists( $theme_files['archive'] . $tax . '.php' ) ) {
                        load_template( $theme_files['archive'] . $tax . '.php' );
                        exit;
                    } else {
                        load_template( $template['archive'] );
                        exit;
                    }
                }
            }
        }
    }

    /**
     * Check if the post type archive for our custom
     * post type is being displayed, i.e.
     * site.com/$post_type/
     */
    elseif ( is_post_type_archive( $post_type ) ) {
        if ( file_exists( $theme_files['archive'] ) ) {
            load_template( $theme_files['archive'] );
            exit();
        } elseif( file_exists( $template['archive'] )) {
            load_template( $template['archive'] );
            exit();
        } else {
            return;
        }
    }

    elseif ( is_search() ) {
        if ( file_exists( $template['search'] ) ) {
            load_template( $template['search'] );
            exit;
        }
    }

    else {
        if ( file_exists( $template['single'] ) ) {
            load_template( $template['single'] );
            exit;
        }
    }
}
add_action( 'template_redirect', 'sell_media_redirect',6 );


/**
 * Loads a template from a specificed path
 *
 * @package Ajax
 * @uses load_template()
 * @since 0.1
 */
function sell_media_load_template() {

    $path = dirname( plugin_dir_path( __FILE__ ) );

    $template = $path . '/themes/' . $_POST['template'];

    load_template( $template );
    die();
}
add_action( 'wp_ajax_nopriv_sell_media_load_template', 'sell_media_load_template' );
add_action( 'wp_ajax_sell_media_load_template', 'sell_media_load_template' );


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
            <?php $price = str_replace( '%', '', get_term_meta( $term->term_id, 'markup', true ) ); ?>
            <option
                value="<?php echo $prepend; ?><?php echo $term->$value; ?>"
                class="taxonomy-<?php echo $taxonomy; ?> term-<?php echo $term->slug; ?> <?php echo $taxonomy; ?>-<?php echo $term->term_id; ?>"
                data-value="<?php echo $term->slug; ?>"
                data-taxonomy="<?php echo $taxonomy; ?>"
                data-name="<?php echo $term->name; ?>"
                data-price="<?php echo $price; ?>"
                id="<?php echo $taxonomy; ?>-<?php echo $term->slug; ?>"
                name="<?php echo $taxonomy; ?>"
                >
            <?php echo $term->name; ?> <?php if ( $price ) : ?>+<?php echo $price; ?>%<?php endif; ?>
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
            <?php $price = get_term_meta( $term->term_id, 'markup', true); ?>
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
    $query = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '{$key}' AND meta_value = '{$value}'";
    $payment_id = $wpdb->get_var( $wpdb->prepare( $query ) );

    if ( is_null( $payment_id ) ){
        wp_die("Invalid {$value} for {$key}");
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
function sell_media_build_download_link( $attachment_id=null ){
    return site_url() . '/' . sell_media_get_attachment_file( $attachment_id, false );
}


/**
 * Returns the file name with extension or the file name and
 * upload year/month/file.ext.
 *
 * @since 0.1
 */
function sell_media_get_attachment_file( $attachment_id=null, $dir=true ){
    $boo = wp_get_attachment_metadata( $attachment_id );

    if ( $dir )
        return array_pop( explode( '/', $boo['file'] ) );
    else
        return $boo['file'];
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
 * Returns the file extension for a give file name.
 * @since 0.1
 */
function sell_media_get_file_extension( $str ){
   $parts = explode('.', $str);
   return end( $parts );
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

        //$html .= '<li class="sell_media-' . $term->slug . ' ' .$last.'">';
        $html .= '<a href="' . get_term_link( $term->slug, $taxonomy ) . '" title="' . $term->description . '">';
        $html .= $term->name;
        $html .= '</a>';
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