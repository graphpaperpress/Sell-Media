<?php

/**
 * Lists all downloads using sell_media_thanks shortcode.
 * Added to Thanks page so buyers can download directly from Page after successful purchase.
 *
 * @return string
 * @since 0.1
 */
function sell_media_list_downloads_shortcode( $purchase_key=null, $email=null ) {
    if ( empty( $purchase_key ) ){
        $message = __( "Please provide a purchase key.", "sell_media" );
    } else {
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
            $payment_settings = get_option( 'sell_media_payment_settings' );
            $message .= __( 'Your purchase is pending. This happens if you paid with an eCheck, if you opened a new account or if there is a problem with the checkout system. Please contact the seller if you have questions about this purchase: ') ;
            $message .= $payment_settings['paypal_email'];
        }
        else {
            foreach( $downloads as $download ){
                $image_attributes = wp_get_attachment_image_src( $download['AttachmentID'], 'medium', false );
                $download = site_url() . '/?download=' . $purchase_key . '&email=' . $email . '&id=' . $download['AttachmentID'];
                $message .= '<div class="sell-media-aligncenter">';
                $message .= '<a href="' . $download . '"><img src="' . $image_attributes[0] . '" width="' . $image_attributes[1] . '" height="' . $image_attributes[2] . '" class="sell-media-aligncenter" /></a>';
                $message .= '<strong><a href="' . $download . '" class="sell-media-buy-button">' . __( 'Download File', 'sell_media' ) . '</a></strong>';
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
    ob_start(); ?>
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
    <?php return ob_get_clean();
}
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

            $general_settings = get_option( 'sell_media_general_settings' );
            if ( empty( $general_settings['customer_notification'] ) ){
                $notice = false;
            } else {
                $notice = $general_settings['customer_notification'];
            }

            if ( ! is_wp_error( $user_id ) && $notice ){
                wp_new_user_notification( $user_id, $password );
            }

            // Get the new post meta
            $payment_meta = get_post_meta( $payment_id, '_sell_media_payment_meta', true );

            // Get the user by Email then assign their ID into the
            // payments meta array
            $user = get_user_by( 'email', $user['email'] );
            $payment_meta['user_id'] = $user->ID;

            // Upate the _sell_media_payment_meta with the User ID
            update_post_meta( $payment_id, '_sell_media_payment_meta', $payment_meta );

            sell_media_process_paypal_purchase( $purchase );
        }
    }
    ob_start(); ?>
    <div id="sell-media-checkout" class="sell-media">
        <?php if ( empty( $items ) ) : ?>
             <p><?php _e('You have no items in your cart. ', 'sell_media'); ?><a href="<?php print get_post_type_archive_link('sell_media_item'); ?>"><?php _e('Continue shopping', 'sell_media'); ?></a>.</p>
        <?php else : ?>
            <table id="sell-media-checkout-table">
                <thead>
                    <tr class="sell-media-header">
                        <th class="sell-media-header-details"><h3><?php _e('Item', 'sell_media'); ?></h3></th>
                        <th class="sell-media-header-price"><h3><?php _e('Price', 'sell_media'); ?></h3></th>
                    </tr>
                </thead>
                <tfoot>
                    <tr class="product-checkout-row">
                        <td colspan="2">
                            <h4><?php _e( 'Subtotal' , 'sell_media' ) ?></h4>
                            <strong><span class="total green"><?php print sell_media_get_currency_symbol(); ?><span class="price-target"></span></span></strong>
                            <form action="" method="post" id="sell_media_checkout_form">
                                <?php do_action('sell_media_above_registration_form'); ?>
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
                                    <span id="email-error" class="error" style="display:none;"><?php _e( 'Email isn\'t valid', 'sell_media' ); ?></span>
                                    </p>
                                <?php else : ?>
                                        <?php $current_user = wp_get_current_user(); ?>
                                        <input type="hidden" id="sell_media_first_name_field" name="first_name" value="<?php print $current_user->user_firstname; ?>" />
                                        <input type="hidden" id="sell_media_last_name_field" name="last_name" value="<?php print $current_user->user_lastname; ?>" />
                                        <input type="hidden" id="sell_media_email_field" name="email" value="<?php print $current_user->user_email; ?>" />
                                    <?php do_action('sell_media_below_registration_form'); ?>
                                <?php endif; ?>
                                <?php if ( current_user_can( 'activate_plugins' ) ) : ?>
                                        <?php _e('You are logged in as an Admin and cannot purchase this item from yourself.', 'sell_media' ); ?>
                                <?php else : ?>
                                    <div class="button-container">
                                        <input type="submit" class="sell-media-buy-button sell-media-buy-button-success sell-media-buy-button-checkout" value="<?php _e('Checkout', 'sell_media'); ?>" />
                                    </div>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                </tfoot>
                <tbody class="sell-media-product-list">
                    <?php $price = null; foreach( $items as $item_id => $item ) : ?>
                        <?php if ( $item['CalculatedPrice'] > 0 ) $price = $item['CalculatedPrice']; else $price = 0; ?>
                        <tr>
                            <td class="product-details">
                                <a href="<?php print get_permalink( $item['ProductID'] ); ?>"><?php sell_media_item_icon( $item['AttachmentID'], array(75,0) ); ?></a>
                                <h6 class="sell-media-table-meta"><a href="<?php print get_permalink( $item['ProductID'] ); ?>"><?php print get_the_title( $item['ProductID'] ); ?></a></h6>
                                <?php if ( !empty( $item['License'] ) ) : ?>
                                    <?php $tmp_term = get_term_by( 'id', $item['License'], $item['taxonomy'] ); ?>
                                    <?php if ( $tmp_term ) : ?>
                                        <h6 class="sell-media-table-meta"><?php _e( 'License:', 'sell_media' ); ?> <?php print $tmp_term->name; ?></h6>
                                        <p><?php print $tmp_term->description; ?></p>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td class="product-price">
                                <span class="currency-symbol"><?php print sell_media_get_currency_symbol(); ?></span><span class="item-price-target"><?php print $price; ?></span> <br /><span class="remove-item-handle" data-item_id="<?php print $item_id; ?>" checked="checked" name="<?php print $item['AttachmentID']; ?>" value="" ><?php _e('Remove', 'sell_media'); ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php return ob_get_clean();
}
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
        'collection' => null
        ), $atts )
    );

    $args = array(
        'posts_per_page' => -1,
        'post_type' => 'sell_media_item'
        );

    if ( $collection ){
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'collection',
                'field' => 'slug',
                'terms' => $collection
                )
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
                        <h3><a href="<?php print get_permalink( $post->ID ); ?>"><?php print get_the_title( $post->ID ); ?></a></h3>
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
 * Adds the 'sell_media_download_list' short code to the editor. [sell_media_download_list]
 *
 * @since 1.0.4
 */
function sell_media_download_shortcode( $atts ) {
	if ( is_user_logged_in() ) {
		global $current_user, $wpdb;
		get_currentuserinfo();
		$payment_lists = $wpdb->get_results( "SELECT * FROM $wpdb->postmeta WHERE meta_key = '_sell_media_payment_meta'", ARRAY_A );
		$html = null;
        foreach( $payment_lists as $key=>$value ) {
			$details = unserialize($value[ 'meta_value' ]);
			if( ! empty( $details['email'] ) && $current_user->user_email == $details[ 'email' ] ){
				$product_details = unserialize( $details[ 'products' ] );
				foreach( $product_details as $product_detail ) {
					$html .= '<div class="download_lists">';
					$html .= wp_get_attachment_image( $product_detail[ 'AttachmentID' ] );
					$html .= '<span class="download_details">';
					$html .= 'Product = <a href="' . get_permalink( $product_detail['ProductID'] ) . '">' . get_the_title( $product_detail[ 'ProductID' ] ) . '</a><br />';
					$html .= "Price = $".$product_detail[ 'CalculatedPrice' ]."<br />";
					$html .= '</span>';
					$html .= '</div>';
				}
			}
		}
	} else {
		$html .=  __( "You must be logged in to view the download lists!", "sell_media" );
	}
    return $html;
}
add_shortcode('sell_media_download_list', 'sell_media_download_shortcode');