<?php

/**
 * Lists all downloads using sell_media_thanks shortcode.
 * Added to Thanks page so buyers can download directly from Page after successful purchase.
 *
 * @return string
 * @since 0.1
 */
function sell_media_list_downloads_shortcode( $purchase_key=null, $email=null ) {

    $purchase_key = $_GET['purchase_key'];
    $email = $_GET['email'];
    $message = null;

    $args = array(
        'post_type' => 'sell_media_payment',
        'post_status' => 'publish',
        'meta_query' => array(
            'relation' => 'AND',
                array(
                    'key' => '_sell_media_payment_purchase_key',
                    'value' => $purchase_key
                )
            )
        );

    $payments = new WP_Query( $args );
    foreach( $payments->posts as $payment ) {
        $payment_meta = get_post_meta( $payment->ID, '_sell_media_payment_meta', true );
        $downloads = maybe_unserialize( $payment_meta['products'] );
    }

    if ( empty( $downloads ) ) {
        $message .= __( 'Your purchase is pending. This happens if you paid with an eCheck, if you opened a new account or if there is a problem with the checkout system. Please contact the seller if you have questions about this purchase: ') ;
        $message .= get_option( 'sell_media_paypal_email' );
        } else {
        foreach( $downloads as $download ){
            $image_attributes = wp_get_attachment_image_src( $download['AttachmentID'], 'medium', false );
            $download = site_url() . '/?download=' . $purchase_key . '&email=' . $email . '&id=' . $download['AttachmentID'];
            $message .= '<div class="sell-media-aligncenter">';
            $message .= '<a href="' . $download . '"><img src="' . $image_attributes[0] . '" width="' . $image_attributes[1] . '" height="' . $image_attributes[2] . '" class="sell-media-aligncenter" /></a>';
            $message .= '<strong><a href="' . $download . '" class="sell-media-buy-button">' . __( 'Download File', 'sell_media' ) . '</a></strong>';
            $message .= '</div>';
        }
      }

    print '<p class="sell-media-thanks-message">' . $message . '</p>';
}
add_shortcode( 'sell_media_thanks', 'sell_media_list_downloads_shortcode' );

/**
 * Search form shortcode [sell_media_searchform]
 *
 * @since 0.1
 */
function sell_media_search_shortcode( $atts, $content = null ) { ?>
    <div id="sell-media-search" class="sell-media">
        <form name="sell-media-search" action="<?php echo home_url(); ?>/" method="get">
            <span class="sell-media-search-field">
                <label for="keywords"><?php _e('Keywords', 'sell_media'); ?></label>
                <input name="post_type" type="hidden" value="sell_media_item" />
                <input type="text" class="sell-media-search-input" name="keywords" />
            </span>
            <span class="sell-media-search-field">
                <a href="javascript://" class="sell-media-advanced-search"><?php _e('+ Advanced Search', 'sell_media'); ?></a>
            </span>
            <div class="sell-media-advanced-search-fields">
                <span class="sell-media-search-field">
                    <label for="city"><?php _e('City', 'sell_media'); ?></label>
                    <input type="text" class="sell-media-search-input" name="city" />
                </span>
                <span class="sell-media-search-field">
                    <label for="state"><?php _e('State', 'sell_media'); ?></label>
                    <input type="text" class="sell-media-search-input" name="state" />
                </span>
                <span class="sell-media-search-field">
                    <label for="collection"><?php _e('Collection', 'sell_media'); ?></label>
                    <input type="text" class="sell-media-search-input" name="collection" />
                </span>
            </div>
            <input type="submit" class="sell-media-buy-button" value="<?php _e('Search', 'sell_media'); ?>" />
        </form>
    </div>
<?php }
add_shortcode('sell_media_searchform', 'sell_media_search_shortcode');

/**
 * Checkout shortcode [sell_media_checkout]
 *
 * @since 0.1
 */
function sell_media_cart_shortcode($atts, $content = null) {

    $i = 0;

    if ( isset( $_SESSION['cart']['items'] ) )
        $items = $_SESSION['cart']['items'];

    if ( $_POST ) {

        // Creat User
        $user = array();
        $user['first_name'] = $_POST['first_name'];
        $user['last_name'] = $_POST['last_name'];
        $user['email'] = $_POST['email'];

        // construct the payment title
        if ( isset( $user['first_name'] ) || isset( $user['last_name'] ) ) {
            $payment_title = $user['first_name'] . ' ' . $user['last_name'];
        } else {
            $payment_title = $user['user_email'];
        }

        $data = array(
            'post_title' => $payment_title,
            'post_status' => 'pending',
            'post_type' => 'sell_media_payment',
            'post_date' => date('Y-m-d H:i:s')
            );

        $payment_id = wp_insert_post( $data );

        // Create Purchase
        if ( $payment_id ) {

            $purchase_key = strtolower( md5( uniqid() ) ); // random key

            if ( isset( $_SERVER['HTTP_X_FORWARD_FOR'] ) ) {
                $ip = $_SERVER['HTTP_X_FORWARD_FOR'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }

            $purchase = array(
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'products' => maybe_serialize( $items ),
                'email' => $user['email'],
                'date' => date( 'Y-m-d H:i:s' ),
                'purchase_key' => $purchase_key,
                'payment_id' => $payment_id
                );

            $amount = 0;
            foreach ( $items as $item ){
                $amount = $amount + $item['CalculatedPrice'];
            }

            // record the payment details
            update_post_meta( $payment_id, '_sell_media_payment_meta', $purchase );
            update_post_meta( $payment_id, '_sell_media_payment_user_email', $purchase['email'] );
            update_post_meta( $payment_id, '_sell_media_payment_first_name', $user['first_name'] );
            update_post_meta( $payment_id, '_sell_media_payment_last_name', $user['last_name'] );
            update_post_meta( $payment_id, '_sell_media_payment_user_ip', $ip );
            update_post_meta( $payment_id, '_sell_media_payment_purchase_key', $purchase['purchase_key'] );
            update_post_meta( $payment_id, '_sell_media_payment_amount', $amount );

            // Record the user
            $data = array(
                'user_login' => $purchase['email'],
                'user_pass' => wp_generate_password( $length=12, $include_standard_special_chars=false ),
                'user_email' => $purchase['email'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'role' => 'sell_media_customer'
                );

            wp_insert_user( $data );

            sell_media_process_paypal_purchase( $purchase );
        }
    }?>
<div id="sell-media-checkout" class="sell-media">
    <?php if ( empty( $items ) ) : ?>
         <p><?php _e('You have no items in your cart.', 'wmpc'); ?></p>
    <?php else : ?>
        <table id="sell-media-checkout-table">
            <thead>
                <tr class="sell-media-header">
                    <th class="sell-media-header-details"><h3><?php _e('Item', 'sell_media'); ?></h3></th>
                    <th class="sell-media-header-price"><h3><?php _e('Price', 'sell_media'); ?></h3></th>
                    <th class="sell-media-header-remove"><h3><?php _e('Remove', 'sell_media'); ?></h3></th>
                </tr>
            </thead>
            <tbody class="sell-media-product-list">
                <?php $price = null; foreach( $items as $item_id => $item ) : ?>
                    <?php
                    if ( $item['CalculatedPrice'] > 0 )
                        $price = $item['CalculatedPrice'];
                    else
                        $price = 0;
                    ?>
                    <tr>
                        <td class="product-details">
                            <a href="<?php print get_permalink( $item['ProductID'] ); ?>"><?php print wp_get_attachment_image( $item['AttachmentID'], array(75,0) ); ?></a>
                            <h5><a href="<?php print get_permalink( $item['ProductID'] ); ?>"><?php print get_the_title( $item['ProductID'] ); ?></a></h5>
                        </td>
                        <td class="product-price">
                            <span class="currency-symbol"><?php print sell_media_get_currency_symbol(); ?></span><span class="item-price-target"><?php print $price; ?></span>
                        </td>
                        <td class="remove-item">
                            <span class="remove-item-handle" data-item_id="<?php print $item_id; ?>" checked="checked" name="<?php print $item['AttachmentID']; ?>" value="" ><?php _e('Remove', 'sell_media'); ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="subtotal-container">
            <strong><?php _e( 'Subtotal ' , 'sell_media' ) ?><span class="total green"><?php print sell_media_get_currency_symbol(); ?><span class="price-target"></span></span></strong>
        </div>
        <form action="" method="post" id="sell_media_checkout_form">
            <?php if ( ! is_user_logged_in() ) : ?>
                <p><?php _e( 'Create an account to complete your purchase. Already have an account', 'sell_media' ); ?>? <a href="<?php echo wp_login_url( get_permalink() ); ?>" title="Login"><?php _e( 'Login', 'sell_media' ); ?></a></p>
                <p>
                <label><?php _e( 'First Name', 'sell_media' ); ?></label><sup class="sell-media-req">&#42;</sup><br />
                <input type="text" class="" id="sell_media_first_name_field" name="first_name" />
                <span id="firstname-error" class="error" style="display:none;"><?php _e( 'First name cannot be empty', 'sell_media' ); ?></span>
                </p>
                <p>
                <label><?php _e( 'Last Name', 'sell_media' ); ?></label><sup class="sell-media-req">&#42;</sup><br />
                <input type="text" class="" id="sell_media_last_name_field" name="last_name" />
                <span id="lastname-error" class="error" style="display:none;"><?php _e( 'Last name cannot be empty', 'sell_media' ); ?></span>
                </p>
                <p>
                <label><?php _e( 'Email', 'sell_media' ); ?></label><sup class="sell-media-req">&#42;</sup><br />
                <input type="email" class="" id="sell_media_email_field" name="email" />
                <span id="email-error" class="error" style="display:none;"><?php _e( 'Email doesn\'t appear valid', 'sell_media' ); ?></span>
                </p>
            <?php else : ?>
                <?php $current_user = wp_get_current_user(); ?>
                <input type="hidden" id="sell_media_first_name_field" name="first_name" value="<?php print $current_user->user_firstname; ?>" />
                <input type="hidden" id="sell_media_last_name_field" name="last_name" value="<?php print $current_user->user_lastname; ?>" />
                <input type="hidden" id="sell_media_email_field" name="email" value="<?php print $current_user->user_email; ?>" />
            <?php endif; ?>
            <div class="button-container">
                <input type="submit" class="sell-media-buy-button sell-media-buy-button-success sell-media-buy-button-checkout" value="<?php _e('Checkout', 'sell_media'); ?>" />
            </div>
        </form>
    <?php endif; ?>
</div>
<?php }
add_shortcode('sell_media_checkout', 'sell_media_cart_shortcode');

/**
 * Adds the 'sell_media' short code to the editor. [sell_media_buy_button]
 *
 * @author Zane M. Kolnik
 * @since 0.1
 */
function sell_media_item_shortcode( $atts ) {

    extract( shortcode_atts( array(
        'style' => 'default',
        'color' => 'blue',
        'id' => 'none',
        'text' => 'BUY',
        'size' => 'medium',
        'align' => 'center'
        ), $atts )
    );

    $thumb_id = null;
    $caption = null;

    $thumb_id = get_post_thumbnail_id( $id );
    $image = wp_get_attachment_image_src( $thumb_id, $size );

    $image = '<a href="' . get_permalink( $id ) . '"><img src="' . $image[0] . '" alt="' . sell_media_image_caption( $id ) . '" title=" ' . sell_media_image_caption( $id ) . ' " class="sell-media-aligncenter" /></a>';
    $button = '<a href="#" data-sell_media-product-id="' . esc_attr( $id ) . '" data-sell_media-thumb-id="' . esc_attr( $thumb_id ) . '" class="sell-media-cart-trigger sell-media-buy-' . esc_attr( $style ) . '">' . $text . '</a>';

    return '<div class="sell-media-item-container sell-media-align' . $align . ' ">' . $image . $button . '</div>';
}
add_shortcode('sell_media_item', 'sell_media_item_shortcode');