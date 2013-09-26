<?php

/**
 * Lists all downloads using sell_media_thanks shortcode.
 * Added to Thanks page so buyers can download directly from Page after successful purchase.
 *
 * @return string
 * @since 0.1
 */
function sell_media_list_downloads_shortcode( $purchase_key=null, $email=null ) {

    if ( isset( $_GET['purchase_key'] ) && ! empty( $_GET['purchase_key'] ) ){
        $purchase_key = $_GET['purchase_key'];
    }

    if ( isset( $_GET['email'] ) && ! empty( $_GET['email'] ) ){
       	$email = $_GET['email'];
    }

    if ( ! empty( $purchase_key ) && ! empty( $email ) ){

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
            $payment_settings = get_option( 'sell_media_payment_settings' );
            $message .= __( 'Your purchase is pending. This happens if you paid with an eCheck, if you opened a new account or if there is a problem with the checkout system. Please contact the seller if you have questions about this purchase: ') ;
            $message .= $payment_settings['paypal_email'];
        } else {

            $payment_id = sell_media_get_payment_id_by( 'email', $email );
            $links = sell_media_build_download_link( $payment_id, $email );

            foreach( $links as $link ){

               	$image_attributes = wp_get_attachment_image_src( get_post_meta( $link['item_id'], '_sell_media_attachment_id', true ), 'medium', false );

                $message .= '<div class="sell-media-aligncenter">';
                $message .= '<a href="' . $link['url']. '">';
                $message .= '<img src="' . $image_attributes[0] . '" width="' . $image_attributes[1] . '" height="' . $image_attributes[2] . '" class="sell-media-aligncenter" />';
                $message .= '</a>';
                $message .= '<strong><a href="' . $link['url'] . '" class="sell-media-buy-button">' . __( 'Download File', 'sell_media' ) . '</a></strong>';
                $message .= '</div>';
            }
        }
    }
    return '<p class="sell-media-thanks-message">' . $message . '</p>';
}
add_shortcode( 'sell_media_thanks', 'sell_media_list_downloads_shortcode' );

/**
 * Search form shortcode [sell_media_searchform]
 *
 * @since 0.1
 */
function sell_media_search_shortcode( $atts, $content = null ) {
    return get_search_form();
}
add_shortcode('sell_media_searchform', 'sell_media_search_shortcode');

/**
 * Checkout shortcode [sell_media_checkout]
 *
 * @since 0.1
 */
function sell_media_checkout_shortcode($atts, $content = null) {

    $general_settings = get_option( 'sell_media_general_settings' );
    $i = 0;

    if ( isset( $_SESSION['cart']['items'] ) )
        $items = $_SESSION['cart']['items'];

    if ( $_POST ){

        // Check if the qty thats in the cart has changed
        // foreach( $_POST['sell_media_item_qty'] as $k => $v ){
        //     if ( is_array( $_SESSION['cart']['items'][ $k ]['price_id'] ) ){
        //         if ( $_SESSION['cart']['items'][ $k ]['price_id']['quantity'] != $v ){
        //             print "new qty: {$k} {$v}\n";
        //             $_SESSION['cart']['items'][ $k ]['price_id']['quantity'] = $v;
        //         }
        //     }
        // }

        // Create User
        $user = array();
        $user['first_name'] = $_POST['first_name'];
        $user['last_name'] = $_POST['last_name'];
        $user['email'] = $_POST['email'];

        // $user['user_id'] =

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
                'payment_id' => $payment_id,
                'CalculatedPrice' => $_SESSION['cart']['total']
                );

            // $amount = 0;
            // $quantity = 0;
            // $cart = New Sell_Media_Cart;
            // foreach ( $items as $item ){
            //     $price = $cart->item_price( $item['item_id'], $item['price_id'] );
            //     $qty = is_array( $item['price_id'] ) ? $item['price_id']['quantity'] : 1;
            //     $amount = $amount + $price * $qty;
            //     $quantity = $quantity + $qty;
            // }
            // echo '<pre>';
            // print_r( $_SESSION );
            // echo '</pre>';

            // $_SESSION['cart']['amount'] = $amount;
            // $_SESSION['cart']['qty'] = $quantity;

            // echo '<pre>';
            // print_r( $_SESSION );
            // echo '</pre>';
            // die();


            /**
             * Compare count in cart with count in post
             * update cart count as needed
             */
            $cart = New Sell_Media_Cart;
            foreach( $_POST['sell_media_item_qty'] as $k => $v ){
                if ( $_SESSION['cart']['items'][ $k ] != $v ){
                    $cart->update_item( $k, 'qty', $v );
                }
            }

            // record the payment details
            update_post_meta( $payment_id, '_sell_media_payment_meta', $purchase );
            update_post_meta( $payment_id, '_sell_media_payment_user_email', $purchase['email'] );
            update_post_meta( $payment_id, '_sell_media_payment_first_name', $user['first_name'] );
            update_post_meta( $payment_id, '_sell_media_payment_last_name', $user['last_name'] );
            update_post_meta( $payment_id, '_sell_media_payment_user_ip', $ip );
            update_post_meta( $payment_id, '_sell_media_payment_purchase_key', $purchase['purchase_key'] );
            update_post_meta( $payment_id, '_sell_media_payment_amount', $_SESSION['cart']['total'] );
            update_post_meta( $payment_id, '_sell_media_payment_quantity', $_SESSION['cart']['qty'] );

            global $current_user;
            do_action( 'sell_media_before_checkout', $purchase );

            // Record the user
            if ( ! $current_user->ID ){
                $password = wp_generate_password( $length=12, $include_standard_special_chars=false );
                $data = array(
                    'user_login' => $purchase['email'],
                    'user_pass' => $password,
                    'user_email' => $purchase['email'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'role' => 'sell_media_customer'
                    );

                $user_id = wp_insert_user( $data );
                $admin['name'] = get_bloginfo('name');
                $admin['email'] = get_option('admin_email');
                $subject = 'Welcome!';
                $body = 'Your username is: ' . $purchase['email'] . "<br />" . 'Your password is: ' . $password;
                $header = "From: " . stripslashes_deep( html_entity_decode( $admin['name'], ENT_COMPAT, 'UTF-8' ) ) . " <{$admin['email']}>\r\n";
                $header .= "Reply-To: ". $purchase['email'] . "\r\n";
                $header .= "MIME-Version: 1.0\r\n";
                $header .= "Content-Type: text/html; charset=utf-8\r\n";
                wp_mail( $purchase['email'], $subject, $body, $header );

            } else {
                $user_id = $current_user->ID;
            }
            update_post_meta( $payment_id, '_sell_media_user_id', $user_id );

            if ( empty( $general_settings['customer_notification'] ) ){
                $notice = false;
            } else {
                $notice = $general_settings['customer_notification'];
            }

            if ( ! is_wp_error( $user_id ) && $notice ){
                wp_new_user_notification( $user_id, $password );
            }

            if ( ! is_wp_error( $user_id ) ){
                do_action('sell_media_after_user_created');
            }

            // Get the new post meta
            $payment_meta = get_post_meta( $payment_id, '_sell_media_payment_meta', true );

            // Get the user by Email then assign their ID into the
            // payments meta array
            $user = get_user_by( 'email', $user['email'] );
            $payment_meta['user_id'] = $user->ID;

            // Upate the _sell_media_payment_meta with the User ID
            update_post_meta( $payment_id, '_sell_media_payment_meta', $payment_meta );

            sell_media_process_paypal_purchase( $purchase, $payment_id );
        }
    }

    ob_start();
    ?>
    <div id="sell-media-checkout" class="sell-media">
        <?php if ( empty( $items ) ) : ?>
             <p><?php _e('You have no items in your cart. ', 'sell_media'); ?><a href="<?php print get_post_type_archive_link('sell_media_item'); ?>"><?php _e('Continue shopping', 'sell_media'); ?></a>.</p>
        <?php else : ?>
            <form action="" method="post" id="sell_media_checkout_form" class="sell-media-form">
            <?php wp_nonce_field('check_email','sell_media_cart_nonce'); ?>
            <table id="sell-media-checkout-table">
                <thead>
                    <tr class="sell-media-header">
                        <th class="sell-media-header-details"><h3><?php _e('Item', 'sell_media'); ?></h3></th>
                        <th class="sell-media-header-quantity"><h3><?php _e('Quantity', 'sell_media'); ?></h3></th>
                        <th class="sell-media-header-price"><h3><?php _e('Price', 'sell_media'); ?></h3></th>
                    </tr>
                </thead>
                <tfoot>
                    <tr class="product-checkout-row">
                        <td colspan="3">
                            <div class="sell-media-subtotal">
                                <div class="sell-media-title"><h3><?php _e( 'Total' , 'sell_media' ) ?></h3></div>
                                <table id="sell-media-subtotal-table">
                                    <tr>
                                        <th scope="row"><?php _e('Subtotal','sell_media'); ?></th>
                                        <td>
                                            <span class="currency"><?php print sell_media_get_currency_symbol(); ?></span><span class="subtotal-target"></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <?php _e('Shipping &amp; Handling','sell_media'); ?>
                                        </th>
                                        <td>
                                            <span class="currency"><?php print sell_media_get_currency_symbol(); ?></span><span class="shipping-target"><?php print apply_filters('sell_media_shipping_rate', "0.00" ); ?></span>
                                        </td>
                                    <tr>
                                        <th scope="row">
                                            <?php _e('Total','sell_media'); ?>
                                        </th>
                                        <td>
                                            <span class="final-total green">
                                                <span class="currency"><?php print sell_media_get_currency_symbol(); ?></span><span class="total-target"></span>
                                            </span>
                                         </td>
                                    </tr>
                                </table>
                            </div>
                                <?php do_action('sell_media_above_registration_form'); ?>
                                <?php if ( ! is_user_logged_in() ) : ?>
                                    <h3 class="checkout-title"><?php _e( 'Create Account', 'sell_media' ); ?></h3>
                                    <p><?php _e( 'Create an account to complete your purchase. Already have an account', 'sell_media' ); ?>? <a href="<?php echo get_permalink( $general_settings['login_page'] ); ?>" title="Login"><?php _e( 'Login', 'sell_media' ); ?></a></p>
                                    <p>
                                    <label><?php _e( 'First Name', 'sell_media' ); ?></label>
                                    <input type="text" class="" id="sell_media_first_name_field" name="first_name" data-required="true" required />
                                    </p>
                                    <p>
                                    <label><?php _e( 'Last Name', 'sell_media' ); ?></label>
                                    <input type="text" class="" id="sell_media_last_name_field" name="last_name" data-required="true" required />
                                    </p>
                                    <p>
                                    <label><?php _e( 'Email', 'sell_media' ); ?></label>
                                    <input type="email" class="" id="sell_media_email_field" name="email" data-required="true" required />
                                    </p>
                                    <?php do_action('sell_media_below_registration_form'); ?>
                                <?php else : ?>
                                    <?php $current_user = wp_get_current_user(); ?>
                                    <input type="hidden" id="sell_media_first_name_field" name="first_name" value="<?php print $current_user->user_firstname; ?>" />
                                    <input type="hidden" id="sell_media_last_name_field" name="last_name" value="<?php print $current_user->user_lastname; ?>" />
                                    <input type="hidden" id="sell_media_email_field" name="email" value="<?php print $current_user->user_email; ?>" />
                                    <?php do_action('sell_media_below_registration_form'); ?>
                                <?php endif; ?>
                                <?php if ( current_user_can( 'activate_plugins' ) ) : ?>
                                        <p class="desc"><?php _e('You are logged in as an Admin and cannot purchase this item from yourself.', 'sell_media' ); ?></p>
                                <?php else : ?>
                                    <?php
                                        if ( ! empty ( $general_settings['terms_and_conditions'] ) ) :
                                    ?>
                                        <div id="termsdiv">
                                            <input type="checkbox" name="termsandconditions" id="sell_media_terms_cb" data-required="true" value="" required/>
                                            <span class="termnotice">
                                                <a href="#" id="agree_terms_and_conditions">
                                                <?php echo apply_filters( 'sell_media_filter_terms_conditions', 'I agree to the terms and conditions' ); ?>
                                                </a>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="button-container">
                                        <input type="submit" class="sell-media-buy-button-success sell-media-buy-button-checkout" value="<?php _e('Complete Purchase', 'sell_media'); ?>" />
                                        <span class="inline"><em><?php _e( 'or', 'sell_media' ); ?></em> <a href="<?php echo get_post_type_archive_link('sell_media_item'); ?>"><?php _e('Continue Shopping','sell_media'); ?></a></span>
                                        <p class="desc"><?php _e('You will be redirected to Paypal to complete your purchase.', 'sell_media' ); ?></p>
                                    </div>
                                <?php endif; ?>
                                <p class="sell-media-credit"><?php sell_media_plugin_credit(); ?></p>
                        </td>
                    </tr>
                </tfoot>
                <tbody class="sell-media-product-list">
                    <?php
                    $cart = New Sell_Media_Cart;
                    foreach( $items as $item_id => $item ) : ?>
                        <?php

                        // Derive the license name
                        if ( empty( $item['license']['id'] ) ){
                            $license = __('None','sell_media');
                            $license_id = false;
                            $price = $item['price']['amount'];
                            $markup_amount = 0;
                        } else {
                            $license_obj = get_term_by('id', $item['license']['id'], 'licenses' );
                            $license = $license_obj->name;
                            $license_id = $item['license']['id'];
                            $price = $cart->item_markup_total( $item['id'], $item['price']['id'], $license_id );
                            $markup_amount = $cart->item_markup_amount( $item['id'], $item['price']['id'], $license_id );
                        }

                        $size_name = $cart->item_size( $item['price']['id'] );
                        $total     = $item['qty'] * $price;
                        ?>

                        <tr>
                            <td class="product-details">
                                <a href="<?php print get_permalink( $item['id'] ); ?>"><?php sell_media_item_icon( get_post_meta( $item['id'], '_sell_media_attachment_id', true ), array(75,0) ); ?></a>
                                <div class="sell-media-table-meta">
                                    <a href="<?php print get_permalink( $item['id'] ); ?>"><?php print get_the_title( $item['id'] ); ?></a>
                                    <div class="sell-media-license"><?php _e('License','sell_media'); ?>: <?php print $license; ?></div>
                                    <div class="sell-media-size"><?php _e('Size','sell_media');?>: <?php print $size_name; ?></div>
                                    <?php if ( $item['price']['description'] ) : ?><div class="sell-media-size"><?php _e('Description','sell_media');?>: <?php print $item['price']['description']; ?></div><?php endif; ?>
                                </div>
                                <?php do_action('sell_media_below_product_cart_title', $item, $item['id'], $item['price']['id']); ?>
                                <?php if ( !empty( $item['License'] ) ) : ?>
                                    <?php $tmp_term = get_term_by( 'id', $item['License'], $item['taxonomy'] ); ?>
                                    <?php if ( $tmp_term ) : ?>
                                        <div class="sell-media-table-meta"><?php _e( 'License', 'sell_media' ); ?>: <?php print $tmp_term->name; ?></div>
                                        <p><?php print $tmp_term->description; ?></p>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td class="product-quantity">
                                <input
                                name="sell_media_item_qty[<?php echo $item_id; ?>]"
                                type="text"
                                id="quantity-<?php print $item_id; ?>"
                                value="<?php echo $item['qty']; ?>"
                                class="small-text sell-media-quantity"
                                data-id="<?php print $item_id; ?>"
                                data-price="<?php print $price; ?>"
                                data-markup="<?php print $markup_amount; ?>"
                                />
                            </td>
                            <td class="product-price">
                                <span class="currency-symbol"><?php print sell_media_get_currency_symbol(); ?></span>
                                <span class="item-price-target" id="sub-total-target-<?php print $item_id; ?>"><?php print sprintf( "%0.2f", $total ); ?></span>
                                <br />
                                <span class="remove-item-handle" data-item_id="<?php print $item_id; ?>"><?php _e('Remove', 'sell_media'); ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                        <tr>
                            <td></td>
                            <td></td>
                            <td><a href="#" class="remove-all-handle"><?php _e('Remove All', 'sell_media'); ?></a></td>
                        </tr>
                </tbody>
            </table>
            </form>
        <?php endif; ?>
    </div>
    <?php return ob_get_clean();
}
add_shortcode('sell_media_checkout', 'sell_media_checkout_shortcode');

/**
 * Adds the 'sell_media' short code to the editor. [sell_media_item]
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

    $caption = null;
    $thumb_id = get_post_meta( $id, '_sell_media_attachment_id', true );
    $image = wp_get_attachment_image_src( $thumb_id, $size );
    $text = apply_filters('sell_media_purchase_text', __( $text,'sell_media' ), $id );
    if ( $image ) {
        $image = '<img src="' . $image[0] . '" alt="' . sell_media_image_caption( $id ) . '" title=" ' . sell_media_image_caption( $id ) . ' " class="sell-media-aligncenter" />';
    } else {
        sell_media_item_icon( get_post_thumbnail_id( $id ), $size );
    }

    $button = '<a href="#" data-sell_media-product-id="' . esc_attr( $id ) . '" data-sell_media-thumb-id="' . esc_attr( $thumb_id ) . '" class="sell-media-cart-trigger sell-media-buy-' . esc_attr( $style ) . '">' . $text . '</a>';

    return '<div class="sell-media-item-container sell-media-align' . $align . ' "><a href="' . get_permalink( $id ) . '">' . $image . '</a>' . $button . '</div>';
}
add_shortcode('sell_media_item', 'sell_media_item_shortcode');


/**
 * Adds template to display all items for sale.
 *
 * @author Zane M. Kolnik
 * @since 1.0.4
 */
function sell_media_all_items_shortcode( $atts ){

    extract( shortcode_atts( array(
        'collection' => null,
		'show' => -1
        ), $atts )
    );

    $args = array(
        'posts_per_page' => -1,
        'post_type' => 'sell_media_item'
        );

    if ( $collection ){
		$args = array(
				'posts_per_page' => $show,
				'taxonomy' => 'collection',
				'field' => 'slug',
				'term' => $collection
				);

    }

    $posts = New WP_Query( $args );
    ob_start(); ?>
    <div id="sell-media-shortcode-all" class="sell-media">
        <div class="sell-media-short-code-all">
            <div class="sell-media-grid-container">
                <?php $i = 0; ?>
                <?php foreach( $posts->posts as $post ) : $i++; ?>
                    <?php if ( $i %3 == 0) $end = ' end'; else $end = null; ?>
                    <div class="sell-media-grid<?php echo $end; ?>">
                        <a href="<?php print get_permalink( $post->ID ); ?>"><?php sell_media_item_icon( get_post_meta( $post->ID, '_sell_media_attachment_id', true ) ); ?></a>
                        <h3 class="sell-media-shortcode-all-item-title"><a href="<?php print get_permalink( $post->ID ); ?>"><?php print get_the_title( $post->ID ); ?></a></h3>
                        <?php sell_media_item_buy_button( $post->ID, 'text', 'Purchase' ); ?>
                    </div>
                <?php endforeach; ?>
                <?php sell_media_pagination_filter(); ?>
            </div><!-- .sell-media-grid-container -->
        </div><!-- .sell-media-short-code-all -->
    </div><!-- #sell-media-shortcode-all .sell_media -->
    <?php return ob_get_clean();
}
add_shortcode('sell_media_all_items', 'sell_media_all_items_shortcode');


/**
 * Shows a list of everything user has downloaded.
 * Adds the 'sell_media_download_list' short code to the editor. [sell_media_download_list]
 *
 * @since 1.0.4
 */
function sell_media_download_shortcode( $atts ) {
	if ( is_user_logged_in() ) {
            global $current_user;
            global $wpdb;
	    get_currentuserinfo();

	    $payment_lists = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value LIKE %s order by post_id DESC", '_sell_media_payment_user_email', $current_user->user_email ), ARRAY_A );
            $payment_obj = New SellMediaPayments;
            $html = null;

            foreach( $payment_lists as $payment ){
                $payment_meta = get_post_meta( $payment['post_id'], '_sell_media_payment_meta', true );
                $html .= '<ul class="payment-meta">';
                $html .= '<li><strong>'.__('Date', 'sell_media').'</strong> ' . $payment_meta['date'] . '</li>';
                $html .= '<li><strong>'.__('Payment ID', 'sell_media').'</strong> ' . $payment_meta['payment_id'] . '</li>';
                $html .= '<li><strong>'.__('Status', 'sell_media').'</strong> ' . $payment_obj->status( $payment['post_id'] ) . '</li>';
                $html .= '</ul>';
                $html .= $payment_obj->payment_table( $payment['post_id'], true );
            }

            return '<div id="purchase-history">'.$html.'</div>';

	} else {
            do_shortcode( '[sell_media_login_form]' );
	}
}
add_shortcode('sell_media_download_list', 'sell_media_download_shortcode');


/**
 * Displays all the price groups in a table
 *
 * @since 1.5.1
 */
function sell_media_price_group_shortcode(){
    ob_start(); ?>
    <table class="">
        <tbody>
        <?php foreach( get_terms('price-group', array( 'hide_empty' => false, 'parent' => 0 ) ) as $parent ) : ?>
            <tr>
                <th colspan="4"><?php echo $parent->name; ?></th>
            </tr>
            <tr class="sell-media-price-group-parent sell-media-price-group-parent-<?php echo $parent->name; ?>" id="sell-media-price-group-parent-<?php echo $parent->term_id; ?>">
                <th><?php _e('Description','sell_media'); ?></th>
                <th><?php _e('width (px)','sell_media'); ?></th>
                <th><?php _e('height (px)','sell_media'); ?></th>
                <th><?php _e('price','sell_media'); ?>(<span class="currency-symbol"><?php echo sell_media_get_currency_symbol(); ?></span>)</th>
            </tr>
            <?php $i=0; foreach( get_terms( 'price-group', array( 'hide_empty' => false, 'child_of' => $parent->term_id ) ) as $term ): ?>
                <tr class="sell-media-price-group-row-<?php echo ($i++%2==1) ? 'odd' : 'even'; ?> sell-media-price-group-child-<?php echo $term->name; ?>" id="sell-media-price-group-child-<?php echo $term->term_id; ?>">
                    <td>
                        <span class="sell-media-price-group-name"><?php echo $term->name; ?></span>
                    </td>
                    <td>
                        <span class="sell-media-price-group-width"><?php echo sell_media_get_term_meta( $term->term_id, 'width', true ); ?></span>
                    </td>
                    <td>
                        <span class="sell-media-price-group-height"><?php echo sell_media_get_term_meta( $term->term_id, 'height', true ); ?></span>
                    </td>
                    <td>
                        <span class="sell-media-price-group-height">
                            <span class="currency-symbol"><?php echo sell_media_get_currency_symbol(); ?></span>
                            <?php echo sprintf( '%0.2f', sell_media_get_term_meta( $term->term_id, 'price', true ) ); ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php return ob_get_clean();
}
add_shortcode('sell_media_price_group', 'sell_media_price_group_shortcode');


/**
 * Displays all collections.
 * Adds the 'sell_media_list_all_collections' short code to the editor. [sell_media_list_all_collections]
 *
 * @since 1.5.3
 */
function sell_media_list_all_collections_shortcode( $atts ) {

	extract( shortcode_atts( array(
		'details' => 'false',
        'thumbs' => 'true'
        ), $atts )
    );

	if ( 'false' == $thumbs ) {

		$html = null;
		$html .= '<div class="sell-media-collections-shortcode">';

		$taxonomy = 'collection';
		$term_ids = array();
		foreach( get_terms( $taxonomy ) as $term_obj ){
		    $password = sell_media_get_term_meta( $term_obj->term_id, 'collection_password', true );
		    if ( $password ) $term_ids[] = $term_obj->term_id;
		}

		$args = array(
		    'orderby' => 'name',
			'hide_empty' => true,
			'parent' => 0,
			'exclude' => $term_ids
		);

		$terms = get_terms( $taxonomy, $args );

		if ( empty( $terms ) )
			return;

		$html .= '<ul class="sell-media-collections-shortcode-list">';
		foreach( $terms as $term ) :
			$html .= '<li class="sell-media-collections-shortcode-list-item">';
			$html .= '<a href="'. get_term_link( $term->slug, $taxonomy ) .'" class="sell-media-collections-shortcode-list-item-link">' . $term->name . '</a>';
			$html .= '</li>';
		endforeach;
		$html .= '</ul>';
		$html .= '</div>';
		return $html;

	} else {

		$html = null;
		$html .= '<div class="sell-media-collections-shortcode sell-media">';

		$sell_media_size_settings = get_option( 'sell_media_size_settings');

		$taxonomy = 'collection';
		$term_ids = array();
		foreach( get_terms( $taxonomy ) as $term_obj ){
		    $password = sell_media_get_term_meta( $term_obj->term_id, 'collection_password', true );
		    if ( $password ) $term_ids[] = $term_obj->term_id;
		}

		$args = array(
		    'orderby' => 'name',
			'hide_empty' => true,
			'parent' => 0,
			'exclude' => $term_ids
		);

		$terms = get_terms( $taxonomy, $args );

		if ( empty( $terms ) )
			return;

		foreach( $terms as $term ) :
			$args = array(
					'post_status' => 'publish',
					'taxonomy' => 'collection',
					'field' => 'slug',
					'term' => $term->slug,
					'tax_query' => array(
						array(
							'taxonomy' => 'collection',
							'field' => 'id',
							'terms' => $term_ids,
							'operator' => 'NOT IN'
							)
						)
					);
			$posts = New WP_Query( $args );
			$post_count = $posts->found_posts;

			if ( $post_count != 0 ) : ?>
				<?php
				$html .= '<div class="sell-media-grid sell-media-grid-collection third">';
					$args = array(
							'posts_per_page' => 1,
							'taxonomy' => 'collection',
							'field' => 'slug',
							'term' => $term->slug
							);

					$posts = New WP_Query( $args );
					?>

					<?php foreach( $posts->posts as $post ) : ?>

						<?php
						//Get Post Attachment ID
						$sell_media_attachment_id = get_post_meta( $post->ID, '_sell_media_attachment_id', true );
						if ( $sell_media_attachment_id ){
							$attachment_id = $sell_media_attachment_id;
						} else {
							$attachment_id = get_post_thumbnail_id( $post->ID );
						}

						$html .= '<a href="'. get_term_link( $term->slug, $taxonomy ) .'" class="sell-media-collections-shortcode-item-link">';
						$collection_attachment_id = sell_media_get_term_meta( $term->term_id, 'collection_icon_id', true );
							if ( ! empty ( $collection_attachment_id ) ) {
								$html .= wp_get_attachment_image( $collection_attachment_id, 'sell_media_item' );
							} else {
								$html .= sell_media_item_icon( $attachment_id, 'sell_media_item', false );
							}
						$html .= '</a>';
					endforeach;

					$html .= '<div class="sell-media-collections-shortcode-item-title"><a href="'. get_term_link( $term->slug, $taxonomy ) .'">' . $term->name . '</a></div>';
					if ( 'true' == $details ) {
						$html .= '<div class="sell-media-collections-shortcode-item-details">';
						$html .= '<span class="sell-media-collections-shortcode-item-count">';
						$html .= '<span class="count">' . $post_count . '</span>' .  __( ' images in ', 'sell_media' ) . '<span class="collection">' . $term->name . '</span>' . __(' collection', 'sell_media');
						$html .= '</span>';
						$html .= '<span class="sell-media-collections-shortcode-item-price">';
						$html .=  __( 'Starting at ', 'sell_media' ) . '<span class="price">' . sell_media_get_currency_symbol() . $sell_media_size_settings['default_price'] . '</span>';
						$html .= '</span>';
						$html .= '</div>';
					}
					$html .= '</div>';

			endif;
		endforeach;
		$html .= '</div>';

		return $html;

	}

}
add_shortcode('sell_media_list_all_collections', 'sell_media_list_all_collections_shortcode');


/**
 * Custom login form
 *
 * @since 1.5.5
 */
function sell_media_login_form_shortcode(){

    $general_settings = get_option( 'sell_media_general_settings' );

    if ( is_user_logged_in() ) {

        return sprintf( __( 'You are logged in. %1$s or %2$s.', 'sell_media'), '<a href="' . get_permalink( $general_settings['checkout_page'] ) . '">Checkout now</a>', '<a href="' . get_post_type_archive_link( 'sell_media_item' ) . '">continue shopping</a>' );

    } else {
        if( isset( $_GET['login'] ) && "failed" == $_GET['login'] ) {
            echo "<span class='error'>".__("Login Failed", "sell_media")."</span>";
        }

        $args = array(
            'redirect' => site_url( $_SERVER['REQUEST_URI'] )
        );

        wp_login_form( $args );

    }

}
add_shortcode( 'sell_media_login_form', 'sell_media_login_form_shortcode' );

/**
 * Redirect the failed login to the same page
 *
 * @since 1.6
 */
add_action( 'wp_login_failed', 'my_front_end_login_fail' );  // hook failed login

function my_front_end_login_fail( $username ) {
   $referrer = $_SERVER['HTTP_REFERER'];  // where did the post submission come from?
   // if there's a valid referrer, and it's not the default log-in screen
   if ( !empty($referrer) && !strstr($referrer,'wp-login') && !strstr($referrer,'wp-admin') ) {
        $redirect = add_query_arg( array( 'login' => 'failed' ), $referrer );
      wp_redirect( $redirect );
      exit;
   }
}



/**
 * Add to wp_footer
 * terms markup
 *
 * @return string
 * @since 1.6
 */
function sell_media_terms_footer(){
    $general_settings = get_option( 'sell_media_general_settings' );
    if ( ! empty ( $general_settings['terms_and_conditions'] ) ) { ?>
    <div id="terms-and-conditions-dialog" style="display: none;">
        <span class="close">&times;</span>
        <?php echo stripslashes_deep( nl2br( $general_settings['terms_and_conditions'] ) ); ?>
    </div>
<?php } }
add_action( 'wp_footer', 'sell_media_terms_footer' );
