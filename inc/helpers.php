<?php

/**
 * Helper Functions
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Template Redirect
 * @since 1.0.4
 */
function sell_media_template_redirect( $original_template ){

    if ( ! sell_media_theme_support() )
        return $original_template;

    $sell_media_taxonomies = get_object_taxonomies( 'sell_media_item' );

    /**
     * Archive -- Check if this is an archive page AND post type is sell media
     */
    if ( is_post_type_archive( 'sell_media_item' ) || is_tax( $sell_media_taxonomies ) ) {
        $template = plugin_dir_path( dirname( __FILE__ ) ) . 'themes/archive.php';
    } else {
        $template = $original_template;
    }

    return $template;
}
add_filter( 'template_include', 'sell_media_template_redirect', 6 );

/**
 * Get search form
 *
 * @param  $form
 * @return $form
 */
function sell_media_get_search_form( $form ){
    // Change the default WP search form if is Sell Media search
    if ( is_search() && 'sell_media_item' == get_query_var( 'post_type' ) ) {
        $form = Sell_Media()->search->form();
    }
    return $form;
}
add_filter( 'get_search_form', 'sell_media_get_search_form' );

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
        if ( in_array( 'sell_media_customer', $user->roles ) ){
            return site_url('dashboard');
        } else {
            return admin_url();
        }
    }
}
add_filter( 'login_redirect', 'sell_media_redirect_login_dashboard', 10, 3 );

/**
 * Add specific CSS classes to the body_class
 *
 * @since 1.9.2
 */
function sell_media_body_class( $classes ) {
    global $post;
    $settings = sell_media_get_plugin_options();

    // Layout is set
    if ( isset( $settings->layout ) )
        $classes[] = $settings->layout;

    // Gallery
    if ( sell_media_is_gallery_page() )
        $classes[] = 'sell-media-gallery-page';

    return $classes;
}
add_filter( 'body_class', 'sell_media_body_class' );

/**
 * Adds a custom query var for gallery links
 *
 * @param  $vars Existing query vars
 * @return $vars Updated query vars
 * @since 2.0.1
 */
function sell_media_add_query_vars_filter( $vars ){
    $vars[] = 'id';
    return $vars;
}
add_filter( 'query_vars', 'sell_media_add_query_vars_filter' );

/**
 * Checks if on sell media gallery page
 *
 * @return boolean true/false
 * @since 2.0.1
 */
function sell_media_is_gallery_page(){
    global $post;

    if ( ! $post )
        return false;

    if ( $post->ID && sell_media_has_multiple_attachments( $post->ID ) && get_query_var( 'id' ) == false )
        return true;
}

/**
 * Add custom class to nav menu items
 */
function sell_media_nav_menu_css_class( $classes, $item ){
    $settings = sell_media_get_plugin_options();

    if ( $item->object == 'page' ){
        if ( $item->object_id == $settings->lightbox_page ) {
            $classes[] = 'lightbox-menu';
        }
        if ( $item->object_id == $settings->checkout_page ){
            if ( in_array( 'total', $item->classes ) ) {
                $classes[] = 'checkout-total';
            } else {
                $classes[] = 'checkout-qty';
            }
        }
    }

    return $classes;
}
add_filter( 'nav_menu_css_class', 'sell_media_nav_menu_css_class', 10, 2 );

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
        'orderby' => 'id',
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
        'orderby' => 'id',
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
 * Get Attachments
 *
 * Get attachment ids from post meta.
 * This function checks for both and returns a WP_Post object
 *
 * @param $post_id
 * @return WP_Post object
 * @since 2.0.1
 */
function sell_media_get_attachments( $post_id ) {
    $meta = get_post_meta( $post_id, '_sell_media_attachment_id', true );
    return ( ! empty ( $meta ) ) ? explode( ',', $meta ) : false;
}


/**
 * Get Attachment
 *
 * If the item has multiple attachments,
 * set the attachment_id to the query variable.
 * Otherwise, get the attachments and assign
 * the first as the $attachment_id.
 *
 * @param int $post_id
 * @return int $attachment_id
 * @since 2.0.1
 */
function sell_media_get_attachment_id( $post_id=null ) {

    if ( sell_media_has_multiple_attachments( $post_id ) ) {
        $attachment_id = get_query_var( 'id' );
    } else {
        $attachments = sell_media_get_attachments( $post_id );
        $attachment_id = $attachments[0];
    }

    return $attachment_id;
}

/**
 * Check if item has multiple attachments
 */
function sell_media_has_multiple_attachments( $post_id ) {

    $attachments = sell_media_get_attachments( $post_id );
    $count = count( $attachments );

    if ( $count > 1 ) {
        return true;
    }
}


/**
 * Determines if a post, identified by the specified ID, exist
 * within the WordPress database.
 *
 * @param    int    $id    The ID of the post to check
 * @return   bool          True if the post exists; otherwise, false.
 * @since    2.0.1
 */
function sell_media_post_exists( $id ) {
    return is_string( get_post_status( $id ) );
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
function sell_media_get_filesize( $post_id=null, $attachment_id=null ){

    $file_path = Sell_Media()->products->get_protected_file( $post_id, $attachment_id );

    if ( file_exists( $file_path ) ) {

        $bytes = filesize( $file_path );
        $s = array( 'b', 'Kb', 'Mb', 'Gb' );
        $e = floor( log( $bytes )/log( 1024 ) );

        return sprintf( '%.2f ' . $s[$e], ( $bytes/pow( 1024, floor( $e ) ) ) );
    }
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
function sell_media_pagination_filter( $max_pages = '' ) {

    global $wp_query;
    $max_num_pages = ( '' != $max_pages ) ? $max_pages : $wp_query->max_num_pages;

    $big = 999999999; // need an unlikely integer

    $params = array(
        //'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
        'format' => '?paged=%#%',
        'current' => max( 1, get_query_var('paged') ),
        'total' => $max_num_pages // note sometimes max_num_pages needs to be sent over
    );

    return '<div class="sell-media-pagination-container">' . paginate_links( $params ) . '</div>';
}
add_filter( 'sell_media_pagination_filter', 'sell_media_pagination_filter', 10, 1 );


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
        'taxonomy' => $taxonomy,
        'orderby' => 'id'
        );

    $price_groups = get_categories( $args );

    return $price_groups;

}

/**
 * Get the assigned price group
 *
 * @param $post_id, $taxonomy
 * @since 2.0.1
 * @return integer $price_group_id
 */
function sell_media_get_item_price_group( $post_id, $taxonomy ) {
    $settings = sell_media_get_plugin_options();
    $terms = get_the_terms( $post_id, $taxonomy );
    if ( $terms && ! is_wp_error( $terms ) ) foreach ( $terms as $term ) {
        if ( $term->parent == 0 ){
            $price_group_id = $term->term_id;
        }
    } elseif ( $taxonomy == 'reprints-price-group' ) {
        $price_group_id = $settings->reprints_default_price_group;
    } elseif ( $taxonomy == 'price-group' ) {
        $price_group_id = $settings->default_price_group;
    } else {
        $price_group_id = 0;
    }

    return $price_group_id;
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
 * Retrieve the absolute path to the import directory without the trailing slash
 *
 * @since  2.0.1
 * @return string $path Absolute path to the sell_media/import directory
 */
function sell_media_get_import_dir() {
    $wp_upload_dir = wp_upload_dir();
    wp_mkdir_p( $wp_upload_dir['basedir'] . '/sell_media/import' );
    $path = $wp_upload_dir['basedir'] . '/sell_media/import';

    return apply_filters( 'sell_media_get_import_dir', $path );
}


/**
 * Get directories
 *
 * @since 2.0.1
 * @param $dir (packages or import)
 * @return array (directories)
 */
function sell_media_get_directories( $dir=null ) {

    $directories = '';
    $path = ( $dir == 'packages' ) ? sell_media_get_packages_upload_dir() : sell_media_get_import_dir();

    foreach ( glob( $path . "/*", GLOB_ONLYDIR ) as $directory ) {
        $directories[] = $directory;
    }
    return $directories;
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
 * Disable cache on Checkout and Thanks pages
 *
 * @since 2.0.2
 * @return void
 */
function sell_media_nocache(){

    if ( is_admin() )
        return;

    if ( false === ( $page_uris = get_transient( 'sell_media_cache_excluded_uris' ) ) ) {
        $settings       = sell_media_get_plugin_options();
        $checkout_page  = $settings->checkout_page;
        $thanks_page    = $settings->thanks_page;

        if ( empty( $checkout_page ) || empty( $thanks_page ) )
            return;

        $page_uris   = array();
        // Exclude IPN listener
        $page_uris[] = '?sell_media-listener=IPN';
        // Exclude default permalinks for pages
        $page_uris[] = '?page_id=' . $checkout_page;
        $page_uris[] = '?page_id=' . $thanks_page;
        // Exclude nice permalinks for pages
        $checkout_page  = get_post( $checkout_page );
        $thanks_page    = get_post( $thanks_page );
        if ( ! is_null( $checkout_page ) )
            $page_uris[] = '/' . $checkout_page->post_name;
        if ( ! is_null( $thanks_page ) )
            $page_uris[] = '/' . $thanks_page->post_name;
        set_transient( 'sell_media_cache_excluded_uris', $page_uris );
    }

    if ( is_array( $page_uris ) ) {
        foreach( $page_uris as $uri ) {
            if ( strstr( $_SERVER['REQUEST_URI'], $uri ) ) {
                if ( ! defined( 'DONOTCACHEPAGE' ) )
                    define( 'DONOTCACHEPAGE', 'true' );
                nocache_headers();
                break;
            }
        }
    }
    delete_transient( 'sell_media_cache_excluded_uris' );
}
add_action( 'init', 'sell_media_nocache', 0 );