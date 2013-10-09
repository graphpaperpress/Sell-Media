<?php

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
 * Empty the user's cart.
 *
 * @since 0.1
 */
function sell_media_empty_cart(){
    if ( ! isset( $_SESSION ) ) session_start();
    $_SESSION['cart']['items'] = null;
    unset( $_SESSION['cart']['items'] );
    unset( $_SESSION['cart'] );
    return ( empty( $_SESSION['cart']['items'] ) ) ? 'Yes' : 'No';
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
