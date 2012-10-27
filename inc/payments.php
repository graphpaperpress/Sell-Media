<?php

/**
 * Determine if payment is completed
 *
 * @access      public
 * @since       0.1
 * @return      boolean
 */
function sell_media_is_payment_complete($payment_id) {
    $payment = get_post($payment_id);
    if ( $payment )
        if ( $payment->post_status == 'publish' )
            return true;
    return false;
}


/**
 * Updates the payment status
 *
 * @access public
 * @since 0.1
 * @return null
 */
function sell_media_update_payment_status($payment_id, $new_status = 'publish') {

    if ( $new_status == 'completed' || $new_status == 'complete' ) {
        $new_status = 'publish';
    }

    $payment = get_post($payment_id);

    $old_status = $payment->post_status;

    do_action( 'sell_media_before_update_payment_status', $payment_id, $new_status, $old_status );
    wp_update_post( array( 'ID' => $payment_id, 'post_status' => $new_status ) );
    do_action( 'sell_media_after_update_payment_status', $payment_id, $new_status, $old_status );
}


/**
 * Add meta boxes to the edit payment page
 *
 * @access public
 * @since 0.1
 * @return null
 */
function sell_media_add_payment_meta_boxes(){
    add_meta_box(
        'meta_field',
        __( 'Customer Info', 'sell_media' ),
        'sell_media_payment_render_contact',
        'sell_media_payment'
    );
}
add_action( 'add_meta_boxes', 'sell_media_add_payment_meta_boxes' );


/**
 * Our call back for the payment meta fields, this prints out
 * the html form on the edit payment page
 *
 * @access public
 * @since 0.1
 * @return html
 */
function sell_media_payment_render_contact( $post ){
    print '<input type="hidden" name="sell_media_custom_meta_box_nonce" value="' . wp_create_nonce( basename( __FILE__ ) ) . '" />';

    $tmp = get_post_custom_values( "_sell_media_payment_first_name", $post->ID );
    print '<p>' . __( 'First Name' ) . ': <input type="text" name="_sell_media_payment_first_name" value="' . $tmp[0] . '" /></p>';

    $tmp = get_post_custom_values( "_sell_media_payment_last_name", $post->ID );
    print '<p>' . __( 'Last Name' ) . ': <input type="text" name="_sell_media_payment_last_name" value="' . $tmp[0] . '" /></p>';

    $tmp = get_post_custom_values( "_sell_media_payment_user_email", $post->ID );
    print '<p>' . __( 'Email' ) . ': <input type="text" name="_sell_media_payment_user_email" value="' . $tmp[0] . '" /></p>';
}


/**
 * Hook to save payment meta when the post is saved
 *
 * @access public
 * @since 0.1
 * @return null
 */
function sell_media_save_contact( $post_id ){
    if ( isset( $_POST['sell_media_custom_meta_box_nonce'] ) )
        $nonce = $_POST['sell_media_custom_meta_box_nonce'];
    else
        return;

    if ( ! wp_verify_nonce( $nonce, basename(__FILE__) ) )
        return $post_id;

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        return $post_id;

    if ( !empty($_POST['sell_media_payment_first_name']))
        update_post_meta( $post_id, 'sell_media_payment_first_name', $_POST['sell_media_payment_first_name'] );

    if ( !empty($_POST['sell_media_payment_last_name']))
        update_post_meta( $post_id, 'sell_media_payment_last_name', $_POST['sell_media_payment_last_name'] );

    if ( !empty($_POST['sell_media_payment_email']))
        update_post_meta( $post_id, 'sell_media_payment_email', $_POST['sell_media_payment_email'] );

    return;
}
add_action( 'save_post', 'sell_media_save_contact' );


/**
 * Callback function to print out the payment page
 *
 * @access public
 * @since 0.1
 * @return html
 */
function sell_media_payments_callback_fn(){

    $current_page = admin_url('edit.php?post_type=download&page=sell_media_payments');?>
    <div class="wrap">
        <?php
        if (isset($_GET['p'])) $page = $_GET['p']; else $page = 1;
        $per_page = 20;
        if(isset($_GET['show']) && $_GET['show'] > 0) {
            $per_page = intval($_GET['show']);
        }
        $total_pages = 1;
        $offset = $per_page * ($page-1);

        $mode = isset($_GET['mode']) ? $_GET['mode'] : 'live';
        if(sell_media_test_mode() && !isset($_GET['mode'])) $mode = 'test';

        $orderby        = isset( $_GET['orderby'] ) ? $_GET['orderby'] : 'ID';
        $order          = isset( $_GET['order'] ) ? $_GET['order'] : 'DESC';
        $order_inverse  = $order == 'DESC' ? 'ASC' : 'DESC';
        $order_class    = strtolower($order_inverse);
        $user           = isset( $_GET['user'] ) ? $_GET['user'] : null;
        $status         = isset( $_GET['status'] ) ? $_GET['status'] : 'any';
        $meta_key       = isset( $_GET['meta_key'] ) ? $_GET['meta_key'] : null;

        $payment_args = array(
            'mode'     => $mode,
            'number'   => $per_page,
            'post_type' => 'sell_media_payment',
            'posts_per_page' => $per_page,
            'post_status' => $status,
            'offset' => $offset,
            'order' => $order,
            'orderby' => $orderby,
            'meta_key' => $meta_key
        );

        $payment_count  = wp_count_posts('sell_media_payment');
        $payments = get_posts( $payment_args );

        $total_count = $payment_count->publish + $payment_count->pending + $payment_count->trash;

        switch( $status ) {
            case 'publish':
                $current_count = $payment_count->publish;
                break;
            case 'pending':
                $current_count = $payment_count->pending;
                break;
            case 'any':
                $current_count = $total_count;
                break;
        }

        $total_pages = ceil( $current_count / $per_page );
     ?>
        <h2><?php _e( 'Payments', 'sell_media' ); ?></h2>
        <?php if ( $payments ) : ?>
        <form id="payments-filter" action="<?php echo admin_url('edit.php'); ?>" method="get" style="float: right; margin-bottom: 5px;">
            <input type="hidden" name="page" value="sell_media_payments"/>
            <input type="hidden" name="post_type" value="sell_media_item"/>
            <?php if(isset( $_GET['user'] ) ) { ?>
                <input type="hidden" name="user" value="<?php echo $_GET['user']; ?>"/>
            <?php } ?>
            <?php if(isset( $_GET['status'] ) ) { ?>
                <input type="hidden" name="status" value="<?php echo $_GET['status']; ?>"/>
            <?php } ?>
            <label for="sell_media_show"><?php _e('Payments per page', 'sell_media'); ?></label>
            <input type="text" class="regular-text" style="width:30px;" id="sell_media_show" name="show" value="<?php echo isset($_GET['show']) ? $_GET['show'] : ''; ?>"/>
            <input type="submit" class="button-secondary" value="<?php _e( 'Show', 'sell_media' ); ?>"/>
        </form>
        <table class="wp-list-table widefat fixed posts">
            <thead>
                <tr>
                    <th style="width: 50px;" class="manage-column column-title sortable <?php echo $order_class; echo $orderby == 'ID' ? ' sorted' : ''; ?>">
                        <a href="<?php echo add_query_arg( array( 'orderby' => 'ID', 'order' => $order_inverse ) ); ?>" title="<?php _e( 'ID', 'sell_media' ); ?>"><span><?php _e( 'ID', 'sell_media' ); ?></span> <span class="sorting-indicator"></span></a>
                    </th>
                    <th style="width: 100px;"><?php _e( 'Customer', 'sell_media' ); ?></th>
                    <th style="width: 300px;"><?php _e( 'Items', 'sell_media' ); ?></th>
                    <th><?php _e( 'Amount', 'sell_media' ); ?></th>
                    <th class="manage-column column-title sortable <?php echo $order_class; echo $orderby == 'Date' ? ' sorted' : ''; ?>">
                        <a href="<?php echo add_query_arg( array( 'orderby' => 'post_date', 'order' => $order_inverse ) ); ?>" title="<?php _e( 'Date', 'sell_media' ); ?>"><span><?php _e( 'Date', 'sell_media' ); ?></span> <span class="sorting-indicator"></span></a>
                    </th>
                    <th class="manage-column column-title sortable <?php echo $order_class; echo $orderby == 'Status' ? ' sorted' : ''; ?>">
                        <a href="<?php echo add_query_arg( array( 'orderby' => 'post_status', 'order' => $order_inverse ) ); ?>" title="<?php _e( 'Status', 'sell_media' ); ?>"><span><?php _e( 'Status', 'sell_media' ); ?></span> <span class="sorting-indicator"></span></a>
                    </th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th><?php _e( 'ID', 'sell_media' ); ?></th>
                    <th><?php _e( 'Customer', 'sell_media' ); ?></th>
                    <th><?php _e( 'Items', 'sell_media' ); ?></th>
                    <th><?php _e( 'Amount', 'sell_media' ); ?></th>
                    <th><?php _e( 'Date', 'sell_media' ); ?></th>
                    <th><?php _e( 'Status', 'sell_media' ); ?></th>
                </tr>
            </tfoot>
            <?php foreach( $payments as $payment ) : ?>
                <?php $payment_meta = get_post_meta($payment->ID, '_sell_media_payment_meta', true); ?>
                <tr>
                    <td><a href="<?php print site_url() . '/wp-admin/post.php?post='.$payment->ID.'&action=edit'; ?>"><?php echo $payment->ID; ?></a></td><td>
                        <?php if ( ! empty( $payment_meta['first_name'] ) ) echo $payment_meta['first_name']; ?>
                        <?php if ( ! empty( $payment_meta['last_name'] ) ) echo $payment_meta['last_name']; ?>
                    </td>
                    <td>
                    <?php
                        $payment_meta_array = get_post_meta( $payment->ID, '_sell_media_payment_meta', true );
                        if ( $payment_meta_array ){
                            $products_meta_array = unserialize( $payment_meta_array['products'] );

                            foreach( $products_meta_array as $product ){
                                print get_the_title( $product['ProductID'] );
                                if ( isset( $product['License'] ) ){
                                    $license = get_term_by( 'id', $product['License'], 'licenses' );
                                    if ( $license ) print ' &ndash; <em>'.$license->name . '</em><br />';
                                }
                            }
                        }
                    ?>
                    </td>
                    <td><?php if (get_post_meta( $payment->ID, '_sell_media_payment_amount', true )) print sell_media_get_currency_symbol() . get_post_meta( $payment->ID, '_sell_media_payment_amount', true ); ?></td>
                    <td><?php echo date('M d, Y', strtotime($payment->post_date)); ?></td>
                    <td><?php if ( $payment->post_status == 'publish' ) print 'paid'; else print $payment->post_status; ?></td>
                </tr>
            <?php endforeach; ?>
            <?php else : ?>
                <p><?php _e( 'There are currently no pending payments', 'sell_media' ); ?>.</p>
            <?php endif; ?>
        </table>

        <div class="tablenav">
            <div class="total-revenue">
                <p><?php _e( 'Total Revenue:', 'sell_media' ); ?>&nbsp;<strong><?php print sell_media_get_currency_symbol(); ?><?php print sell_media_total_revenue(); ?></strong></p>
                <?php do_action( 'sell_media_payments_below_total_earning' ); ?>
            </div>
            <?php if ($total_pages > 1) : ?>
                <div class="tablenav-pages alignright">
                    <?php

                        $query_string = $_SERVER['QUERY_STRING'];

                        $base = 'edit.php?' . remove_query_arg('p', $query_string) . '%_%';

                        echo paginate_links( array(
                            'base' => $base,
                            'format' => '&p=%#%',
                            'prev_text' => '&laquo; ' . __('Previous', 'sell_media'),
                            'next_text' => __('Next', 'sell_media') . ' &raquo;',
                            'total' => $total_pages,
                            'current' => $page,
                            'end_size' => 1,
                            'mid_size' => 5,
                        ));
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php }


/**
 * Total Earnings
 *
 * @since 0.1
*/

function sell_media_total_revenue() {
    $total = ( float ) 0;
    $payments = get_transient( 'sell_media_total_revenue' );
    if ( false === $payments || '' === $payments ) {
        $args = array(
            'mode' => 'live',
            'post_type' => 'sell_media_payment',
            'posts_per_page' => -1,
            'post_status' => 'pending',
            'meta_key' => '_sell_media_payment_amount'
        );
        set_transient( 'sell_media_total_revenue', $payments, 1800 );
    }
    $payments = get_posts( $args );
    if ( $payments ) {
        foreach( $payments as $payment ) {
            $subtotal = get_post_meta( $payment->ID, '_sell_media_payment_amount', true );
            $total += $subtotal;
        }
    }
    return number_format( ( float ) $total, 2, '.', '' );
}