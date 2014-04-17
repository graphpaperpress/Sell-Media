<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Lists all downloads using sell_media_thanks shortcode.
 * Added to Thanks page so buyers can download directly from Page after successful purchase.
 *
 * @return string
 * @since 0.1
 */
function sell_media_list_downloads_shortcode( $tx=null ) {

    do_action( 'sell_media_thanks_hook' );

    $tx = null;

    if ( isset( $_GET['tx'] ) && ! empty( $_GET['tx'] ) ) {
        $tx = $_GET['tx'];
    } elseif ( isset( $_POST['stripeToken'] ) && ! empty( $_POST['stripeToken'] ) ) {
        $tx = $_POST['stripeToken'];
    } else {
        $tx = null;
    }

    if ( $tx ) {
        $post_id = Sell_Media()->payments->get_id_from_tx( $transaction_id=$tx );
        $html = null;
        $html = Sell_Media()->payments->get_payment_products_formatted( $post_id );
        $html .= '<script>sellMediaCart.empty();</script>';
        return '<p class="sell-media-thanks-message">' . $html . '</p>';
        do_action( 'sell_media_thanks_hook_below' );
    } else {
        return false;
    }

}
add_shortcode( 'sell_media_thanks', 'sell_media_list_downloads_shortcode' );


/**
 * Search form shortcode [sell_media_searchform]
 *
 * @since 0.1
 */
function sell_media_search_shortcode( $atts, $content = null ) {

    $html = null;
    $html .= Sell_Media()->search->form();
    $html .= Sell_Media()->search->results();

    return $html;

}
add_shortcode( 'sell_media_searchform', 'sell_media_search_shortcode' );




/**
 * Adds the 'sell_media' short code to the editor. [sell_media_item]
 *
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

    $image = sell_media_item_icon( $id, 'medium', false );

    $button = '<a href="#" data-sell_media-product-id="' . esc_attr( $id ) . '" data-sell_media-thumb-id="' . esc_attr( $id ) . '" class="sell-media-cart-trigger sell-media-' . esc_attr( $style ) . '">' . $text . '</a>';

    return '<div class="sell-media-item-container sell-media-align' . $align . ' "><a href="' . get_permalink( $id ) . '">' . $image . '</a>' . $button . '</div>';
}
add_shortcode( 'sell_media_item', 'sell_media_item_shortcode' );


/**
 * Adds template to display all items for sale.
 *
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
    <div class="sell-media">
        <div class="sell-media-grid-container">
            <?php $i = 0; ?>
            <?php foreach( $posts->posts as $post ) : $i++; ?>
                <div class="sell-media-grid<?php if ( $i %3 == 0 ) echo ' end'; ?>">
                    <div class="item-inner">
                        <a href="<?php echo get_permalink( $post->ID ); ?>"><?php sell_media_item_icon( $post->ID, apply_filters( 'sell_media_thumbnail', 'medium' ) ); ?></a>
                        <span class="item-overlay">
                            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <?php sell_media_item_buy_button( $post->ID, 'text', __( 'Purchase' ) ); ?>
                            <?php do_action( 'sell_media_item_overlay' ); ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php sell_media_pagination_filter(); ?>
        </div><!-- .sell-media-grid-container -->
    </div><!-- #sell-media-shortcode-all .sell_media -->
    <?php return ob_get_clean();
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
    <div id="sell-media-checkout-cart" style="display:none;">
        <div class="sellMediaCart_items"></div>
        <?php do_action( 'sell_media_checkout_after_cart' ); ?>
        <div class="sell-media-totals group">
            <table id="sell-media-totals-table" class="sell-media-totals-table">
                <tr class="subtotal">
                    <td class="sell-media-key"><?php _e( 'Subtotal', 'sell_media' ); ?>:</td>
                    <td class="sell-media-value"><span class="sellMediaCart_total"></span></td>
                </tr>
                <?php do_action( 'sell_media_checkout_registration_fields' ); ?>
                <tr class="tax">
                    <td class="sell-media-key"><?php _e( 'Tax', 'sell_media' ); ?><span class="quiet"><?php if ( ! empty( $settings->tax ) ) echo ' (' . round( ( float ) $settings->tax_rate * 100 ) . '&#37)'; ?></span>:</td>
                    <td class="sell-media-value"><span class="sellMediaCart_tax"></span></td>
                </tr>
                <tr class="shipping">
                    <td class="sell-media-key"><?php _e( 'Shipping', 'sell_media' ); ?>:</td>
                    <td class="sell-media-value"><span class="sellMediaCart_shipping"></span></td>
                </tr>
                <tr class="total sell-media-bold">
                    <td class="sell-media-key"><?php _e( 'Total', 'sell_media' ); ?>:</td>
                    <td class="sell-media-value"><span class="sellMediaCart_grandTotal"></span></td>
                </tr>
            </table>
            <?php do_action( 'sell_media_checkout_after_registration_fields' ); ?>
            <div class="sell-media-checkout-button group">
                <?php do_action( 'sell_media_above_checkout_button' ); ?>
                <p><a href="javascript:void(0)" class="sellMediaCart_checkout sell-media-button"><?php _e( 'Checkout Now', 'sell_media' ); ?></a></p>
                <p id="sell-media-continue-shopping" class="text-center">
                    <?php echo apply_filters( 'sell_media_or_continue_shopping', sprintf( __( 'or %s', 'sell_media'), '<a href="' . get_post_type_archive_link( 'sell_media_item' ) . '">continue shopping &raquo;</a>' ) ); ?>
                </p>
                <?php
                $settings = sell_media_get_plugin_options();
                if ( ! empty ( $settings->terms_and_conditions ) ) : ?>
                    <p id="sell-media-tos" class="text-center small quiet"><?php echo apply_filters( 'sell_media_tos_label', 'By clicking "Checkout Now", you are agreeing to our <a href="javascript:void(0);" class="sell-media-empty-dialog-trigger">terms of service</a>.' ); ?></p>
                <?php endif; ?>
            </div>
        </div><!-- .sell-media-totals -->
        <?php do_action( 'sell_media_below_registration_form' ); ?>
    </div><!-- #sell-media-checkout-cart -->
    <p id="sell-media-empty-cart-message" style="display:none;">
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
    if ( is_user_logged_in() ) {
        global $current_user;
        get_currentuserinfo();

        $purchases = Sell_Media()->payments->get_user_payments( $current_user->user_email );

        $html = null;
        $html = '<h2>';
        $html .= __( 'Your Purchase History', 'sell_media' );
        $html .= '</h2>';

        if ( $purchases ) foreach ( $purchases as $purchase ) {
            $html .= '<div class="sell-media-purchase">';
            $html .= '<p>';
            $html .= '<strong>' . __( 'Purchase ID', 'sell_media' ) . ': ' . $purchase . '</strong>';
            $html .= '<br /><span class="date">' . get_the_time( 'M N, Y', $purchase ) . '</span>';
            $html .= '</p>';
            $html .= Sell_Media()->payments->get_payment_products_formatted( $purchase );
            $html .= '</div>';
        }

        return '<div id="purchase-history">' . $html . '</div>';

    } else {
            do_shortcode( '[sell_media_login_form]' );
    }
}
add_shortcode( 'sell_media_download_list', 'sell_media_download_shortcode' );


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
add_shortcode( 'sell_media_price_group', 'sell_media_price_group_shortcode' );


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
        $html .= '<div class="sell-media-grid-container">';

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

            if ( $post_count != 0 ) :

                $html .= '<div class="sell-media-grid third">';
                $html .= '<div class="item-inner sell-media-collection">';
                    $args = array(
                            'posts_per_page' => 1,
                            'taxonomy' => 'collection',
                            'field' => 'slug',
                            'term' => $term->slug
                            );

                    $posts = New WP_Query( $args );

                    foreach( $posts->posts as $post ) :

                        $html .= '<a href="'. get_term_link( $term->slug, $taxonomy ) .'" class="collection">';
                        $collection_attachment_id = sell_media_get_term_meta( $term->term_id, 'collection_icon_id', true );
                        $html .= sell_media_item_icon( $post->ID, apply_filters( 'sell_media_thumbnail', 'medium', false ) );
                        if ( 'true' == $details ) {
                            $settings = sell_media_get_plugin_options();
                            $html .= '<div class="item-overlay">';
                            $html .= '<div class="collection-details">';
                            $html .= '<span class="collection-count">';
                            $html .= '<span class="count">' . $post_count . '</span>' .  __( ' images in ', 'sell_media' ) . '<span class="collection">' . $term->name . '</span>' . __(' collection', 'sell_media');
                            $html .= '</span>';
                            $html .= '<span class="collection-price">';
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

        return sprintf( __( 'You are logged in. %1$s or %2$s.', 'sell_media'), '<a href="' . get_permalink( $settings->checkout_page ) . '">Checkout now</a>', '<a href="' . get_post_type_archive_link( 'sell_media_item' ) . '">continue shopping</a>' );

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