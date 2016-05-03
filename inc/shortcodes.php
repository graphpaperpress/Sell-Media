<?php

/**
 * Shortcodes
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Lists all downloads using sell_media_thanks shortcode.
 * Added to Thanks page so buyers can download directly from Page after successful purchase.
 *
 * @return string
 * @since 0.1
 */
function sell_media_thanks_shortcode( $tx=null ) {

    do_action( 'sell_media_thanks_hook' );

    if ( ! empty( $_GET['tx'] ) ) {
        $tx = $_GET['tx'];
        $gateway = 'PayPal';
    } elseif ( ! empty( $_POST['stripeToken'] ) ) {
        $tx = $_POST['stripeToken'];
        $gateway = 'Stripe';
    } else {
        $tx = null;
    }

    if ( $tx ) {
        $post_id = Sell_Media()->payments->get_id_from_tx( $tx );
        $html  = null;
        $html .='<p class="sell-media-thanks-message">';
        if ( $post_id ) {
            $html .= Sell_Media()->payments->get_payment_products_formatted( $post_id );
        } else {
            $html .= __( 'We\'ve received your payment and are processing your order. <a href="" class="reload">Refresh this page</a> to check your order status. If you continue to see this message, please contact us.', 'sell_media' );
            // wp_mail( get_option( 'admin_email' ), __( 'Unable to retrieve transaction ID', 'sell_media' ), sprintf( __( 'We have some good news and bad news. First the good news: Somebody just purchased from your store! The bad news: Your website was unable to retrieve transaction ID from %1$s. This is typically easy to fix. Please see these tips for resolving this issue: %2$s' ), $gateway, 'https://github.com/graphpaperpress/Sell-Media/issues/670#issuecomment-89428248' ) );
        }
        
        // Clear cart item.
        global $sm_cart;
        @$sm_cart->clear();

        $html .= '</p>';
        return apply_filters( 'sell_media_thanks_filter_below', $html );
    }

}
add_shortcode( 'sell_media_thanks', 'sell_media_thanks_shortcode' );


/**
 * Search form shortcode [sell_media_searchform]
 *
 * @since 0.1
 */
function sell_media_search_shortcode( $atts, $content = null ) {

    $html = null;
    $html .= Sell_Media()->search->form();

    return $html;

}
add_shortcode( 'sell_media_searchform', 'sell_media_search_shortcode' );
add_shortcode( 'sell_media_search', 'sell_media_search_shortcode' );



/**
 * Adds the 'sell_media' short code to the editor. [sell_media_item]
 *
 * @since 0.1
 */
function sell_media_item_shortcode( $atts ) {
    $html = "";
    extract( shortcode_atts( array(
        'style' => 'default',
        'color' => 'blue',
        'id' => '',
        'attachment' => '',
        'text' => 'BUY',
        'size' => 'medium',
        'align' => 'center'
        ), $atts )
    );

    $image = sell_media_item_icon( $id, $size, false );
    $text = apply_filters('sell_media_purchase_text', $text, $id );
    $button = sell_media_item_buy_button( $id, $attachment, 'button', $text, false );
    
    if ( sell_media_has_multiple_attachments( $id ) ) {
        $html = sell_media_gallery( $id );
    } else {
        $html = '<div class="sell-media-item-container sell-media-align' . $align . ' "><a href="' . get_permalink( $id ) . '">' . $image . '</a><br />' . $button . '</div>';
    }

    return $html;
}
add_shortcode( 'sell_media_item', 'sell_media_item_shortcode' );


/**
 * Adds template to display all items for sale.
 *
 * @since 1.0.4
 */
function sell_media_all_items_shortcode( $atts ){
    $html = "";
    $settings = sell_media_get_plugin_options();

    global $paged;
    if ( get_query_var('paged') ) {
        $paged = get_query_var('paged');
    } elseif ( get_query_var('page') ) {
        $paged = get_query_var('page');
    } else {
        $paged = 1;
    }

    extract( shortcode_atts( array(
        'collection' => '',
        'columns' => '3',
        'show' => get_option( 'posts_per_page' )
        ), $atts )
    );
    //$class = ( $columns ) ? 'sell-media-grid-item sell-media-grid-item-' . $columns : 'sell-media-grid-item';

    $args = array(
        'posts_per_page' => $show,
        'post_type' => 'sell_media_item',
        'paged' => $paged
    );

    if ( $collection ){
        $args = array(
            'posts_per_page' => $show,
            'taxonomy' => 'collection',
            'field' => 'slug',
            'term' => $collection,
            'orderby' => $settings->order_by,
            'paged' => $paged
        );
    }

    $wp_query = null;
    $wp_query = new WP_Query();
    $wp_query->query( $args );
    $i = 0;

    if ( $wp_query->have_posts() ) :

        $html = '<div class="sell-media">';
        $html .= '<div class="' . apply_filters( 'sell_media_grid_item_container_class', 'sell-media-grid-item-container' ) . '">';

        while ( $wp_query->have_posts() ) : $wp_query->the_post(); $i++;
            $html .= apply_filters( 'sell_media_content_loop', get_the_id(), $i );
        endwhile; wp_reset_query(); $i = 0;

        $html .= '</div><!-- .sell-media-grid-item-container -->';
        if ( ! is_front_page() && is_main_query() )
            $html .= sell_media_pagination_filter( $wp_query->max_num_pages );
        $html .= '</div><!-- #sell-media-shortcode-all .sell_media -->';

    endif;
    wp_reset_postdata();

    return $html;
}
add_shortcode( 'sell_media_all_items', 'sell_media_all_items_shortcode' );


/**
 * The checkout page
 *
 * @since 2.0
 */
function sell_media_checkout_shortcode(){
    $settings = sell_media_get_plugin_options();
    ob_start(); ?>
    <?php do_action( 'sell_media_checkout_before_cart' ); ?>
    <?php 
    global $sm_cart;
    $cart_items = $sm_cart->getItems();
    if ( ! empty( $cart_items ) ) :
    ?>
    <div id="sell-media-checkout-cart">
        <ul class="sell-media-cart-items">
            <?php 
            $cart_index = 0;
            foreach( $cart_items as $key => $item ): ?>
                <li class="item row-<?php echo $cart_index; ?>" id="<?php echo $key; ?>" data-type="<?php echo $item['item_type']; ?>" data-price="<?php echo $item['price']; ?>">
                    <div class="item-image">
                        <?php if ( ! empty( $item['item_image'] ) ) : ?>
                            <img src="<?php echo esc_url( $item['item_image'] ); ?>">
                        <?php endif; ?>
                    </div>
                    <div class="item-details">
                        <div class="item-name">
                        <?php if ( ! empty( $item['item_name'] ) ) : ?>
                            <?php echo esc_attr( $item['item_name'] ); ?>
                        <?php endif; ?>
                        </div>
                        <div class="item-size">
                        <?php if ( ! empty( $item['item_size'] ) ) : ?>
                            <?php echo $item['item_size']; ?>
                        <?php endif; ?>
                        </div>
                        <div class="item-license">
                        <?php if ( ! empty( $item['item_usage'] ) ) : ?>
                            <?php echo $item['item_usage']; ?>
                        <?php endif; ?>
                        </div>
                    </div>
                    <div class="item-qty-total">
                        <div class="item-decrement">
                            <span class="sell-media-cart-decrement dashicons dashicons-minus"></span>
                        </div>
                        <div class="item-quantity">
                            <span class="count">
                                <?php if ( ! empty( $item['qty'] ) ) : ?>
                                    <?php echo $item['qty']; ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="item-increment">
                            <span class="sell-media-cart-increment dashicons dashicons-plus"></span>
                        </div>
                        <div class="item-total">
                            <?php echo sell_media_get_currency_symbol( $settings->currency ) . number_format( $item['price'] * $item['qty'], 2 ); ?>
                        </div>
                    </div>
                </li>
            <?php 
            $cart_index++;
            endforeach; ?> 
        </ul>

        <?php do_action( 'sell_media_checkout_after_cart' ); ?>
        
        <div class="sell-media-totals group">
            <div id="sell-media-totals-table" class="sell-media-totals-table cf">
                <div class="subtotal cf">
                    <div class="sell-media-key"><?php _e( 'Subtotal', 'sell_media' ); ?>:</div>
                    <div class="sell-media-value"><span class="sell-media-cart-total"></span></div>
                </div>
                <?php do_action( 'sell_media_checkout_registration_fields' ); ?>
                <div class="tax cf">
                    <div class="sell-media-key"><?php _e( 'Tax', 'sell_media' ); ?><span class="quiet"><?php if ( ! empty( $settings->tax ) ) echo ' (' . round( ( float ) $settings->tax_rate * 100 ) . '&#37)'; ?></span>:</div>
                    <div class="sell-media-value"><span class="sell-media-cart-tax"></span></div>
                </div>
                <div class="shipping cf">
                    <div class="sell-media-key"><?php _e( 'Shipping', 'sell_media' ); ?>:</div>
                    <div class="sell-media-value"><span class="sell-media-cart-shipping"></span></div>
                </div>
                <?php do_action( 'sell_media_checkout_before_grand_total' ); ?>
                <div class="total cf">
                    <div class="sell-media-key"><?php _e( 'Total', 'sell_media' ); ?>:</div>
                    <div class="sell-media-value"><span class="sell-media-cart-grand-total"></span></div>
                </div>
            </div>
            
            <?php do_action( 'sell_media_checkout_after_registration_fields' ); ?>
            
            <div class="sell-media-checkout-button group">
                <?php do_action( 'sell_media_above_checkout_button' ); ?>
                <p><a href="javascript:void(0)" class="sell-media-cart-checkout sell-media-button"><?php _e( 'Checkout Now', 'sell_media' ); ?></a></p>
                <p id="sell-media-continue-shopping">
                    <?php
                    $html  = __( 'or', 'sell_media' );
                    $html .= ' <a href="' . get_post_type_archive_link( 'sell_media_item' ) . '">';
                    $html .= __( 'continue shopping &raquo;', 'sell_media' );
                    $html .= '</a>';
                    echo apply_filters( 'sell_media_or_continue_shopping', $html );
                    ?>
                </p>
                <?php
                $settings = sell_media_get_plugin_options();
                if ( ! empty ( $settings->terms_and_conditions ) ) : ?>
                    <p id="sell-media-tos" class="text-center small quiet"><?php echo apply_filters( 'sell_media_tos_label', __( 'By clicking "Checkout Now", you are agreeing to our <a href="javascript:void(0);" class="sell-media-empty-dialog-trigger">terms of service</a>.', 'sell_media' ) ); ?></p>
                <?php endif; ?>
            </div><!-- .sell-media-checkout-button -->

        </div><!-- .sell-media-totals -->

        <?php do_action( 'sell_media_below_registration_form' ); ?>

    </div><!-- #sell-media-checkout-cart -->
    
    <?php endif; ?>

    <p id="sell-media-empty-cart-message" class="<?php echo ( !empty( $cart_items ) ) ? 'hide' : ''?>">
        <?php echo apply_filters( 'sell_media_continue_shopping', sprintf( __( 'Your cart is empty. %s', 'sell_media'), '<a href="' . get_post_type_archive_link( 'sell_media_item' ) . '">Continue shopping &raquo;</a>' ) ); ?>
    </p>

    <?php wp_nonce_field( 'validate_cart', 'cart_nonce_security' ); ?>
    <?php return ob_get_clean();
}
add_shortcode( 'sell_media_checkout', 'sell_media_checkout_shortcode' );

/**
 * Shows a list of everything user has downloaded.
 * Adds the 'sell_media_download_list' short code to the editor. [sell_media_download_list]
 *
 * @since 1.0.4
 */
function sell_media_download_shortcode( $atts ) {

    $html = do_shortcode( '[sell_media_login_form]' );

    if ( is_user_logged_in() ) {
        global $current_user;
        get_currentuserinfo();

        $purchases = Sell_Media()->payments->get_user_payments( $current_user->user_email );

        $html .= '<h2>';
        $html .= __( 'Your Purchase History', 'sell_media' );
        $html .= '</h2>';

        if ( $purchases ) foreach ( $purchases as $purchase ) {
            $html .= '<div class="sell-media-purchase">';
            $html .= '<p>';
            $html .= '<strong>' . __( 'Purchase ID', 'sell_media' ) . ': ' . $purchase . '</strong>';
            $html .= '<br /><span class="date">' . get_the_time( 'F j, Y', $purchase ) . '</span>';
            $html .= '</p>';
            $html .= Sell_Media()->payments->get_payment_products_formatted( $purchase );
            $html .= '</div>';
        }
    }
    
    return $html;
}
add_shortcode( 'sell_media_download_list', 'sell_media_download_shortcode' );
add_shortcode( 'sell_media_dashboard', 'sell_media_download_shortcode' );

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
            <?php $i=0; foreach( get_terms( 'price-group', array( 'hide_empty' => false, 'child_of' => $parent->term_id, 'orderby' => 'id' ) ) as $term ): ?>
                <tr class="sell-media-price-group-row-<?php echo ($i++%2==1) ? 'odd' : 'even'; ?> sell-media-price-group-child-<?php echo $term->name; ?>" id="sell-media-price-group-child-<?php echo $term->term_id; ?>">
                    <td>
                        <span class="sell-media-price-group-name"><?php echo $term->name; ?></span>
                    </td>
                    <td>
                        <span class="sell-media-price-group-width"><?php echo get_term_meta( $term->term_id, 'width', true ); ?></span>
                    </td>
                    <td>
                        <span class="sell-media-price-group-height"><?php echo get_term_meta( $term->term_id, 'height', true ); ?></span>
                    </td>
                    <td>
                        <span class="sell-media-price-group-height">
                            <span class="currency-symbol"><?php echo sell_media_get_currency_symbol(); ?></span>
                            <?php echo sprintf( '%0.2f', get_term_meta( $term->term_id, 'price', true ) ); ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php return ob_get_clean();
}
add_shortcode( 'sell_media_price_group', 'sell_media_price_group_shortcode' );


/**
 * Displays all collections.
 * Adds the 'sell_media_list_all_collections' short code to the editor. [sell_media_list_all_collections]
 *
 * @since 1.5.3
 */
function sell_media_list_all_collections_shortcode( $atts ) {

    extract( shortcode_atts( array(
        'details' => 'true',
        'thumbs' => 'true'
        ), $atts )
    );

    if ( 'false' == $thumbs ) {

        $html = null;
        $html .= '<div class="sell-media-collections-shortcode">';

        $taxonomy = 'collection';
        $term_ids = array();
        foreach( get_terms( $taxonomy ) as $term_obj ){
            $password = get_term_meta( $term_obj->term_id, 'collection_password', true );
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
        $html .= '<div class="' . apply_filters( 'sell_media_grid_item_container_class', 'sell-media-grid-item-container' ) . '">';

        $taxonomy = 'collection';
        $term_ids = array();
        foreach( get_terms( $taxonomy ) as $term_obj ){
            $password = get_term_meta( $term_obj->term_id, 'collection_password', true );
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

            if ( $post_count != 0 ) :

                $html .= '<div class="' . apply_filters( 'sell_media_grid_item_class', 'sell-media-grid-item', NULL ) . '">';
                $html .= '<div class="sell-media-item-wrap sell-media-collection">';
                    $args = array(
                            'posts_per_page' => 1,
                            'taxonomy' => 'collection',
                            'field' => 'slug',
                            'term' => $term->slug
                            );

                    $posts = New WP_Query( $args );

                    foreach( $posts->posts as $post ) :

                        $html .= '<a href="'. get_term_link( $term->slug, $taxonomy ) .'" class="collection">';
                        $collection_attachment_id = get_term_meta( $term->term_id, 'collection_icon_id', true );
                        $html .= sell_media_item_icon( $post->ID, apply_filters( 'sell_media_thumbnail', 'medium', false ), false );
                        if ( 'true' == $details ) {
                            $settings = sell_media_get_plugin_options();
                            $html .= '<div class="sell-media-item-details">';
                            $html .= '<div class="sell-media-collection-details">';
                            $html .= '<span class="sell-media-collection-count">';
                            $html .= '<span class="count">' . $post_count . '</span>' .  __( ' images in ', 'sell_media' ) . '<span class="collection">' . $term->name . '</span>' . __(' collection', 'sell_media');
                            $html .= '</span>';
                            $html .= '<span class="sell-media-collection-price">';
                            $html .=  __( 'Starting at ', 'sell_media' ) . '<span class="price">' . sell_media_get_currency_symbol() . $settings->default_price . '</span>';
                            $html .= '</span>';
                            $html .= '</div>';
                            $html .= '</div>';
                            $html .= '<h3>' . $term->name . '</h3>';
                        }
                        $html .= '</a>';
                        endforeach;
                    $html .= '</div>';
                    $html .= '</div>';

            endif;
        endforeach;
        $html .= '</div>';
        $html .= '</div>';

        return $html;

    }

}
add_shortcode( 'sell_media_list_all_collections', 'sell_media_list_all_collections_shortcode' );


/**
 * Custom login form
 *
 * @since 1.5.5
 */
function sell_media_login_form_shortcode(){

    $settings = sell_media_get_plugin_options();

    if ( is_user_logged_in() ) {

        return '<p class="sell-media-login-out">' . sprintf( __( 'You are logged in. %1$s or %2$s.', 'sell_media'), '<a href="' . apply_filters( 'sell_media_logout_redirect_url', wp_logout_url( site_url() ) ) . '">' . __( 'Logout', 'sell_media' ) . '</a>', '<a href="' . get_post_type_archive_link( 'sell_media_item' ) . '">' . __( 'continue shopping', 'sell_media' ) . '</a>' ) . '</p>';

    } else {
        if ( isset( $_GET['login'] ) && "failed" == $_GET['login'] ) {
            echo '<span class="error">' . __( 'Login failed', 'sell_media' ) . '</span>';
        }

        $args = array(
            'redirect' => get_permalink( $settings->checkout_page ),
            'label_username' => __( 'Username', 'sell_media' ),
            'label_password' => __( 'Password', 'sell_media' ),
            'label_remember' => __( 'Remember Me', 'sell_media' ),
            'label_log_in' => __( 'Log In', 'sell_media' )        );

        wp_login_form( $args );

        echo '<a href="' . wp_lostpassword_url( get_permalink() ) . '" title="' . __( 'Forgot Password', 'sell_media' ) . '">' . __( 'Forgot Password', 'sell_media' ) . '</a>';

    }

}
add_shortcode( 'sell_media_login_form', 'sell_media_login_form_shortcode' );
add_shortcode( 'sell_media_login', 'sell_media_login_form_shortcode' );

/**
 * Ajax item filter shortcode.
 * @param  mixed $atts Attributes for shortcode.
 * @return string      Shortcode output
 *
 * @since 2.1.4 
 */
function sell_media_ajax_filter( $atts ){

    $atts = shortcode_atts( array(
        'filters' => 'all',
    ), $atts );

    $filter_tabs = $choosen_tabs = array(
            array( 
                'title' => __( 'Newest', 'sell_media' ),
                'slug' => 'newest',
                'icon' => 'dashicons dashicons-clock'
            ),
            array( 
                'title' => __( 'Most Popular', 'sell_media' ),
                'slug' => 'most-popular',
                'icon' => 'dashicons dashicons-chart-bar'
            ),
            array( 
                'title' => __( 'Collections', 'sell_media' ),
                'slug'   => 'collections',
                'icon' => 'dashicons dashicons-screenoptions'
            ),
            array( 
                'title' => __( 'Keywords', 'sell_media' ),
                'slug' => 'keywords',
                'icon' => 'dashicons dashicons-tag'
            )
        );

    if( 'all' !== $atts['filters'] ){
        $choosen_tab_ids = explode( ',', $atts['filters'] );
        $choosen_tabs = array();
        if( !empty( $choosen_tab_ids ) ){
            foreach ($choosen_tab_ids as $key => $tab_id) {
                $_tab_id = trim( $tab_id ) - 1;
                if( isset( $filter_tabs[$_tab_id] ) ){
                    $choosen_tabs[] =  $filter_tabs[$_tab_id];                    
                }
            }
        }
    }

    if( empty( $choosen_tabs ) ){
        return false;
    }

    $output = '<div id="sell-media-ajax-filter-container">';
        $output .= '<ul class="sell-media-ajax-filter-tabs">';
        $have_keywords = false;
        $have_collections = false;
        $first_tab = false;
        $first_term = false;
        foreach ($choosen_tabs as $tab_key => $tab) {
            $tab_item_class = 'sell-media-ajax-filter-tab-item' . ( ( 0 == $tab_key )? ' selected-tab' : '' );
            $output .= '<li><a href="javascript:void(0);" id="' . $tab['slug'] . '" class="' . $tab_item_class . '"><span class="'.$tab['icon'].'"></span>' . $tab['title'] . '</a></li>';

            if( 0 == $tab_key ){
                $first_tab = $tab['slug'];
            }

            if( 'keywords' == $tab['slug'] ){
                $have_keywords = true;
            }
            if( 'keywords' == $tab['slug'] ){
                $have_collections = true;
            }
        }
        $output .= '</ul>';

        if( $have_keywords ){
            $keywords = get_terms( 'keywords' );
            if( !empty( $keywords ) ){
                $keywords_terms_class = 'sell-media-ajax-filter-terms sell-media-ajax-filter-keyword-terms';
                if( 'keywords' !== $first_tab ){
                    $keywords_terms_class .= ' hide';
                }
                $output .= '<div class="'.$keywords_terms_class.'">';
                    $output .= '<div class="drop-down-close-button"></div>';
                    $output .= '<ul class="current-term-group">';
                    $term_pagination = false;
                    foreach ($keywords as $key_index => $keyword) {
                        $keywords_class = 'sell-media-filter-keyword-term';
                        if( 'keywords' == $first_tab && 0 == $key_index ){
                            $first_term = $keyword->term_id;
                            $keywords_class .= ' selected-term';
                        }

                        $output .= '<li><a href="javascript:void(0);" id="' . $keyword->slug . '" data-termid="'.$keyword->term_id.'" class="'.$keywords_class.'">' . $keyword->name . '</a></li>';

                        if( 0 != $key_index && $key_index%15 == 0 ){
                            $output .= '</ul><!--end1-->';
                            $output .= '<ul class="hide">';
                            $term_pagination = true;
                        } 
                    }                
                    $output .= '</ul><!--end All-->';
                    if( $term_pagination ){
                        $output .= '<div class="term-pagination">';
                        $output .= '<span class="prev" style="display:none;"> &laquo; '. __('Prev', 'sell_media' ) . '</span>';
                        $output .= '<span class="next">'. __('Next', 'sell_media' ) . ' &raquo;</span>';
                        $output .= '</div>';
                    }
                $output .= '</div>';
            }
        }

        if( $have_collections ){
            $collections = get_terms( 'collection' );
            if( !empty( $collections ) ){
                $collection_terms_class = 'sell-media-ajax-filter-terms sell-media-ajax-filter-collection-terms';
                if( 'collections' !== $first_tab ){
                    $collection_terms_class .= ' hide';
                }
                $output .= '<div class="'.$collection_terms_class.'">';
                    $output .= '<div class="drop-down-close-button"></div>';
                $output .= '<ul class="current-term-group">';
                $term_pagination = false;
                foreach ($collections as $col_index => $collection) {
                    $collection_class = 'sell-media-filter-collection-term';
                    if( 'collections' == $first_tab && 0 == $col_index ){
                        $first_term = $collection->term_id;
                        $collection_class .= ' selected-term';
                    }
                    $output .= '<li><a href="javascript:void(0);" id="' . $collection->slug . '" data-termid="'.$collection->term_id.'" class="'.$collection_class.'">' . $collection->name . '</a></li>';

                    if( 0 != $col_index && $col_index%15 == 0 ){
                        $output .= '</ul><!--end1-->';
                        $output .= '<ul class="hide">';
                        $term_pagination = true;
                    } 
                }                
                $output .= '</ul><!--end All-->';
                if( $term_pagination ){
                    $output .= '<div class="term-pagination">';
                    $output .= '<span class="prev" style="display:none;"> &laquo; '. __('Prev', 'sell_media' ) . '</span>';
                    $output .= '<span class="next">'. __('Next', 'sell_media' ) . ' &raquo;</span>';
                    $output .= '</div>';
                }
                $output .= '</div>';
            }
        }

        $output .= '<div class="sell-media-ajax-filter-result">';
            $output .= '<div id="sell-media-archive" class="sell-media">';
                $output .= '    <div id="sell-media-ajax-filter-content" role="main">';
                    $pram = array( 'tab' => $first_tab, 'term' => $first_term );
                    $response = sell_media_ajax_filter_search( $pram, false );
                    $output .= $response['content'];
                    $output .= $response['load_more'];
                $output .= '</div>';
            $output .= '</div>';
        $output .= '</div>';
    $output .= '</div>';
    return $output;
}
add_shortcode( 'sell_media_filters', 'sell_media_ajax_filter' );
add_shortcode( 'sell_media_filter', 'sell_media_ajax_filter' );