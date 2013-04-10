<?php
/**
 * Add items to $_SESSION for shopping cart via $_POST
 *
 * @since 0.1
 * @todo Update all price_id to be size_id
 * @todo Update all price id (array) to be part of item array
 */
function sell_media_add_items(){

    check_ajax_referer('sell_media_add_items', 'sell_media_nonce');

    // Get current cart if any if not set $cart to be an empty array
    $cart = isset( $_SESSION['cart']['items'] ) ? $_SESSION['cart']['items'] : array();

    // Get any additional items
    $to_add = array();
    $to_add = apply_filters('sell_media_additional_items', $to_add);

    // If we don't have additional items we use whats in $_POST
    if ( empty( $to_add ) ){

        if ( empty( $_POST['price_id'] ) ) die();

        $items[] = array(
            'item_id' => (int)$_POST['ProductID'],
            'price_id' => $_POST['price_id'],
            'license_id' => $_POST['License']
        );
        $items = array_merge( $cart, $items );
    } else {
        // We have additional items and merge the current cart with the new items to add
        $items = array_merge( $cart, $to_add );
    }

    // Update our session with the new items
    $_SESSION['cart']['items'] = $items;
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

    $item_index = $_POST['item_id'];

    unset( $_SESSION['cart']['items'][$item_index] );

    if ( empty( $_SESSION['cart']['items'] ) ) {
        print '<p>' . __('You have no items in your cart. ', 'sell_media') . '<a href="'. get_post_type_archive_link('sell_media_item') .'">' . __('Continue shopping', 'sell_media') .'</a>.</p>';
    }

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
function sell_media_head(){?>
    <script type="text/javascript">
    var ajaxurl = "<?php print admin_url("admin-ajax.php"); ?>";
    var pluginurl = "<?php print plugin_dir_url( dirname( __FILE__ ) ); ?>";
    var checkouturl = "<?php $options = get_option('sell_media_general_settings'); $page_id = $options['checkout_page']; print get_permalink( $page_id ); ?>";
    </script>
<?php }
add_action( 'wp_head', 'sell_media_head' );