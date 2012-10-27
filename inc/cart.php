<?php
/**
 * Add items to $_SESSION for shopping cart via $_POST
 *
 * @since 0.1
 */
function sell_media_add_items(){

    unset( $_POST['action'] );

    $tmp_items = array();

    foreach( $_POST as $k => $v ){
        $tmp_items[$k] = $v;
    }

    $_SESSION['cart']['items'][] = $tmp_items;

    $tmp_total = 0;
    foreach( $_SESSION['cart']['items'] as $value ){
        $tmp_total += $value['CalculatedPrice'];
    }

    $total = $tmp_total;
    $_SESSION['cart']['totalPrice'] = $total;

    die();
}
add_action( 'wp_ajax_nopriv_sell_media_add_items', 'sell_media_add_items' );
add_action( 'wp_ajax_sell_media_add_items', 'sell_media_add_items' );


/**
 * Retrive and print out the cart count
 *
 * @since 0.1
 */
function sell_media_count_cart(){

    if ( isset( $_SESSION['cart']['items'] ) )
        $count = count( $_SESSION['cart']['items'] );
    else
        $count = 0;

    print $count;
    die();
}
add_action( 'wp_ajax_nopriv_sell_media_count_cart', 'sell_media_count_cart' );
add_action( 'wp_ajax_sell_media_count_cart', 'sell_media_count_cart' );


/**
 * Removes an item from the users cart
 *
 * @since 0.1
 */
function sell_media_remove_item() {
    $item_id = $_POST['item_id'];
    $_SESSION['cart']['totalPrice'] = $_SESSION['cart']['totalPrice'] - $_SESSION['cart']['items'][$item_id]['CalculatedPrice'];

    unset( $_SESSION['cart']['items'][$item_id] );
    if (  empty( $item_id ) ) print "0";
    die();
}
add_action( 'wp_ajax_nopriv_sell_media_remove_item', 'sell_media_remove_item' );
add_action( 'wp_ajax_sell_media_remove_item', 'sell_media_remove_item' );


/**
 * Empty the user's cart.
 *
 * @since 0.1
 */
function sell_media_empty_cart(){
    unset( $_SESSION['cart'] );
}


/**
 * Creates a string of Product titles to be used as the summary for a purchase
 *
 * @since 0.1
 * @return $summary, string
 */
function sell_media_get_purchase_summary($purchase_data, $email = true) {

    $products = maybe_unserialize( $purchase_data['products'] );
    $summary = null;

    if ( $email ) {
        $summary .= $purchase_data['email'] . ' - ';
    }

    $count = count( $products );
    $i = 0;
    foreach( $products as $product ) {
        $summary .= get_the_title( $product['ProductID'] );
        $i++;

        if ( $count > $i )
            $summary .= ', ';

    }

    $summary = substr($summary, 0, -2);

    return $summary;
}


/**
 * Add to wp_footer
 * Cart markup
 *
 * @return string
 * @since 0.1
 */
function sell_media_footer(){ ?>
    <div class="sell-media-cart-dialog" style="display:none">
        <div class="sell-media-cart-dialog-target"><h2><?php _e( 'Loading', 'sell_media' ); ?>...</h2></div>
    </div>
<?php }
add_action( 'wp_footer', 'sell_media_footer' );


/**
 * Add to wp_head
 * Cart AJAX
 *
 * @return string
 * @since 0.1
 */
function sell_media_head(){
    print '<script type="text/javascript"> var ajaxurl = "'. admin_url("admin-ajax.php") .'"; var _pluginurl="'. plugin_dir_url( __FILE__ ) .'";</script>';
}
add_action( 'wp_head', 'sell_media_head' );