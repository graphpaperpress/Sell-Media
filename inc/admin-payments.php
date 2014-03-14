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
 * Add meta boxes to the edit payment page
 *
 * @access public
 * @since 0.1
 * @return null
 */
function sell_media_add_payment_meta_boxes(){

    add_meta_box(
        'meta_field',
        __( 'Purchase Details', 'sell_media' ),
        'sell_media_payment_purchase_details',
        'sell_media_payment'
    );

    add_meta_box(
        'meta_field_additional',
        __( 'Additional Purchase Details', 'sell_media' ),
        'sell_media_payment_additional_purchase_details',
        'sell_media_payment'
    );

    $screen = get_current_screen();

    if ( $screen->id == 'sell_media_payment' ) {
        global $post;
        $paypal_args = get_post_meta( $post->ID, '_paypal_args', true );
        $stripe_args = get_post_meta( $post->ID, '_stripe_args', true );
        if ( ! empty( $paypal_args ) || ! empty( $stripe_args ) ){
            add_meta_box(
                'meta_field_details',
                __( 'Payment Gateway Details', 'sell_media' ),
                'sell_media_payment_gateway_details',
                'sell_media_payment'
            );
        }
        add_action( 'sell_media_payment_gatway_metabox', $post->id );
    }
}
add_action( 'add_meta_boxes', 'sell_media_add_payment_meta_boxes' );


/**
 * Our callback for the payment meta fields, this prints out
 * the html form on the edit payment page
 *
 * @access public
 * @since 0.1
 * @return html
 */
function sell_media_payment_purchase_details( $post ){

    $payment_obj = new SellMediaPayments;

    echo '<div class="sell-media-admin-payments">';
    echo '<input type="hidden" name="sell_media_custom_meta_box_nonce" value="' . wp_create_nonce( basename( __FILE__ ) ) . '" />';

    printf(
        '<ul>
        <li>%s: ' . $payment_obj->get_meta_key( $post->ID, 'first_name' ) . ' ' . $payment_obj->get_meta_key( $post->ID, 'last_name' ) . ' ' . '</li>
        <li>%s: ' . $payment_obj->get_meta_key( $post->ID, 'email' ) . ' ' . '</li>
        <li>%s: ' . $payment_obj->total( $post->ID ) . ' ' . '</li>
        </ul>',
        __( 'Name', 'sell_media' ),
        __( 'Email', 'sell_media' ),
        __( 'Total', 'sell_media' )
    );

    do_action( 'sell_media_below_payment_contact_details', $post->ID );

    echo $payment_obj->payment_table( $post->ID );

    do_action( 'sell_media_additional_customer_meta', $post->ID );

    echo '</div>';

}

/**
 * Our callback for the additional payment meta fields, this prints out
 * all of the _sell_media_payment_meta info
 *
 * @access public
 * @since 0.1
 * @return html
 */
function sell_media_payment_additional_purchase_details( $post ){

    $p = new SellMediaPayments;
    $args = $p->get_meta( $post->ID );

    ?>
    
    <p><?php _e( 'This is the additional payment data stored with the purchase.', 'sell_media'); ?></p>
    <table class="wp-list-table widefat" cellspacing="0">
        <tbody>
            <?php if ( $args ) : foreach( $args as $k => $v ) : ?>
                <?php if ( ! is_array( $v ) ) : ?>
                    <tr>
                        <td><?php echo ucwords( str_replace('_', ' ', $k ) ); ?></td><td><?php echo $v; ?></td>
                    </tr>
                <?php else : ?>
                    <?php $i = 0; ?>
                    <?php foreach( $v as $name => $value ) : $i++ ?>
                        <?php if ( ! is_array( $name ) ) : ?>
                            <tr>
                                <td><?php _e( 'Product', 'sell_media' ); ?> <?php echo $i; ?></td>
                                <td>
                                    <ul>
                                        <?php if ( $value['name'] ) : ?>
                                            <li><?php _e( 'Name', 'sell_media' ); ?>: <?php echo $value['name']; ?></li>
                                        <?php endif; ?>
                                        <?php if ( $value['id'] ) : ?>
                                            <li><?php _e( 'ID', 'sell_media' ); ?>: <a href="<?php echo admin_url(); ?>post.php?post=<?php echo $value['id']; ?>&amp;action=edit"><?php echo $value['id']; ?></a></li>
                                        <?php endif; ?>
                                        <?php if ( $value['type'] ) : ?>
                                            <li><?php _e( 'Type', 'sell_media' ); ?>: <?php echo $value['type']; ?></li>
                                        <?php endif; ?>
                                        <?php if ( $value['size']['name'] ) : ?>
                                            <li><?php _e( 'Size', 'sell_media' ); ?>: <?php echo $value['size']['name']; ?></li>
                                        <?php endif; ?>
                                        <?php if ( $value['license']['name'] ) : ?>
                                            <li><?php _e( 'License', 'sell_media' ); ?>: <?php echo $value['license']['name']; ?></li>
                                        <?php endif; ?>
                                        <?php if ( $value['qty'] ) : ?>
                                            <li><?php _e( 'Qty', 'sell_media' ); ?>: <?php echo $value['qty']; ?></li>
                                        <?php endif; ?>
                                        <?php if ( $value['total'] ) : ?>
                                            <li><?php _e( 'Subtotal', 'sell_media' ); ?>: <?php echo sell_media_get_currency_symbol(); ?><?php echo number_format( $value['total'], 2, '.', ',' ); ?></li>
                                        <?php endif; ?>
                                    </ul>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach; else : ?>
                <tr>
                    <td><?php _e( 'This payment has no additional payment details', 'sell_media' ); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php
}


/**
 * Our callback for the additional payment meta fields, this prints out
 * all of the _paypal_args or _stripe_args metadata info
 *
 * @access public
 * @since 0.1
 * @return html
 */
function sell_media_payment_gateway_details( $post ){

    $paypal_args = get_post_meta( $post->ID, '_paypal_args', true );
    $stripe_args = get_post_meta( $post->ID, '_stripe_args', true );
    if ( $paypal_args ) {
        $arguments = $paypal_args;
        $gateway = __( 'PayPal', 'sell_media' );
    } else {
        $arguments = $stripe_args;
        $gateway = __( 'Stripe', 'sell_media' );
    }
    echo '<p>' . __( 'This is the data that was sent from ', 'sell_media' ) . $gateway . __( ' at time of purchase. Use this for debugging, if needed.', 'sell_media' ) . '</p>';
    echo '<pre style="overflow:hidden">';
    print_r( $arguments );
    echo '</pre>';
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

    $current_page = admin_url( 'edit.php?post_type=download&page=sell_media_payments' ); ?>
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
        <?php screen_icon(); ?>
        <h2><?php _e( 'Payments', 'sell_media' ); ?></h2>
        <div class="tool-box total-revenue">
            <h3 class="title"><?php _e( 'Total Earnings To Date:', 'sell_media' ); ?>&nbsp;<strong><?php print sell_media_get_currency_symbol(); ?><?php print sell_media_total_revenue( $post_status='publish' ); ?></strong></h3>
            <p><?php _e( "Below is a breakdown of each transaction. Payments marked as &quot;Pending&quot; mean the buyer checked out, but abandoned payment.", 'sell_media' ); ?></p>
            <p><?php printf( '%s <a href="' . sell_media_plugin_data( $field='PluginURI' ) . '" class="button secondary" target="_blank">%s</a>', __( 'Want to increase your sales?', 'sell_media' ), __( 'Download new Sell Media extensions','sell_media') ); ?></p>
            <?php do_action( 'sell_media_payments_below_total_earning' ); ?>
        </div>
        <div class="clear"></div>

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
            <?php submit_button( __('Show', 'sell_media'), 'secondary', 'show', $wrap=false ); ?>
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
                    <td><a href="<?php print admin_url() . 'post.php?post=' . $payment->ID . '&action=edit'; ?>"><?php echo $payment->ID; ?></a></td>
                    <td>
                        <?php if ( ! empty( $payment_meta['first_name'] ) ) echo $payment_meta['first_name']; ?>
                        <?php if ( ! empty( $payment_meta['last_name'] ) ) echo $payment_meta['last_name']; ?>
                    </td>
                    <td>
                        <?php
                            $p = new SellMediaPayments;
                            $products = $p->get_products( $payment->ID );
                            $i = 0;
                            $count = count( $products );
                            if ( $products ) foreach ( $products as $product ) {
                                echo '<a href="' . get_edit_post_link( $product['id'] ) . '">' . $product['name'] . '</a>';
                                echo $i++ != $count - 1 ? ', ' : null;
                            }
                        ?>
                    </td>
                    <td><?php echo $p->total( $payment->ID ); ?></td>
                    <td><?php echo date('M d, Y', strtotime($payment->post_date)); ?></td>
                    <td><?php echo $p->status( $payment->ID ); ?></td>
                </tr>
            <?php endforeach; ?>
            <?php else : ?>
                <p><?php _e( 'There are currently no pending payments', 'sell_media' ); ?>.</p>
            <?php endif; ?>
        </table>

        <div class="tablenav">
            <div class="total-revenue">
                <p><?php _e( 'Total Pending:', 'sell_media' ); ?>&nbsp;<strong><?php print sell_media_get_currency_symbol(); ?><?php print sell_media_total_revenue( $post_status='pending' ); ?></strong></p>
                <p><?php _e( 'Total Earnings:', 'sell_media' ); ?>&nbsp;<strong><?php print sell_media_get_currency_symbol(); ?><?php print sell_media_total_revenue( $post_status='publish' ); ?></strong></p>
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
 * Callback function to print out the payments report page
 *
 * @access public
 * @since 1.2
 * @return html
 */
function sell_media_reports_callback_fn(){

    $current_page = admin_url('edit.php?post_type=download&page=sell_media_reports');?>
    <div class="wrap">
        <?php screen_icon(); ?>
        <h2><?php _e( 'Earnings Report', 'sell_media' ); ?></h2>
        <div class="tool-box total-revenue">
            <h3 class="title"><?php _e( 'Total Earnings To Date:', 'sell_media' ); ?>&nbsp;<strong><?php print sell_media_get_currency_symbol(); ?><?php print sell_media_total_revenue( $post_status='publish' ); ?></strong></h3>
            <p><?php printf('%s <a href="' . sell_media_plugin_data( $field='PluginURI' ) . '" class="button secondary" target="_blank">%s</a>', __( 'Below is a breakdown of earnings per day, month and year. Want to increase your sales?', 'sell_media' ), __( 'Download new Sell Media extensions', 'sell_media' ) ); ?></p>
            <?php do_action( 'sell_media_payments_below_total_earning' ); ?>
        </div>

        <div class="clear"></div>

        <?php ob_start(); ?>
        <script type="text/javascript">
            google.load("visualization", "1", {packages:["corechart"]});
            // Sales Chart
            google.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = new google.visualization.DataTable();
                data.addColumn('string', '<?php _e("Day", "sell_media"); ?>');
                data.addColumn('number', '<?php _e("Earnings", "sell_media"); ?>');
                data.addRows([
                    <?php
                    $num_of_days = apply_filters( 'sell_media_earnings_per_day_days', 30 ); // show payments for the last 30 days
                    $i = $num_of_days;
                    while( $i > 1 ) :
                        $day_time   = strtotime( '-' . $num_of_days - $i . ' days', time() );
                        $day        = date( 'd', $day_time ) + 1;
                        $month      = date( 'n', $day_time ) + 1;
                        $year       = date( 'Y', $day_time );
                        ?>
                        ['<?php echo date( "n/d", mktime( 0, 0, 0, $month, $day, $year ) ); ?>',
                        <?php echo sell_media_get_sales_by_date( $day, $month, $year ); ?>,
                        ],
                        <?php $i--;
                    endwhile;
                    ?>
                ]);

                var options = {
                    title: "<?php _e('Earnings per day', 'sell_media'); ?>",
                    fontSize: "12"
                };

                var chart = new google.visualization.ColumnChart(document.getElementById('daily_earnings_chart_div'));
                chart.draw(data, options);
            }
        </script>
        <div id="daily_earnings_chart_div" class="earnings_chart"></div>
        <?php echo ob_get_clean(); ?>


        <?php ob_start(); ?>
        <script type="text/javascript">
            google.load("visualization", "1", {packages:["corechart"]});
            // Sales Chart
            google.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = new google.visualization.DataTable();
                data.addColumn('string', '<?php _e("Month", "sell_media"); ?>');
                data.addColumn('number', '<?php _e("Earnings", "sell_media"); ?>');
                data.addRows([
                    <?php
                    $i = 1;
                    while($i <= 12) : ?>
                        ['<?php echo sell_media_month_num_to_name($i) . ' ' . date("Y"); ?>', <?php echo sell_media_get_sales_by_date(null, $i, date('Y') ); ?>,
                        ],
                    <?php
                    $i++;
                    endwhile;
                    ?>
                ]);
                var options = {
                    title: "<?php _e('Earnings per month', 'sell_media'); ?>",
                    fontSize: "12"
                };

                var chart = new google.visualization.ColumnChart(document.getElementById('monthly_earnings_chart_div'));
                chart.draw(data, options);
            }
        </script>
        <div id="monthly_earnings_chart_div" class="earnings_chart"></div>
        <?php echo ob_get_clean(); ?>


        <?php ob_start(); ?>
        <script type="text/javascript">
            google.load("visualization", "1", {packages:["corechart"]});
            // Sales Chart
            google.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = new google.visualization.DataTable();
                data.addColumn('string', '<?php _e("Year", "sell_media"); ?>');
                data.addColumn('number', '<?php _e("Earnings", "sell_media"); ?>');
                data.addRows([
                    <?php
                    $current = date('Y');
                    $i = $current - 12;
                    while($current >= $i) : ?>
                        ['<?php echo $i; ?>',
                        <?php echo sell_media_get_sales_by_date(null, null, $i ); ?>,
                        ],
                        <?php
                        $i++;
                    endwhile;
                    ?>
                ]);

                var options = {
                    title: "<?php _e('Earnings per year', 'sell_media'); ?>",
                    fontSize: "12"
                };

                var chart = new google.visualization.ColumnChart(document.getElementById('annual_earnings_chart_div'));
                chart.draw(data, options);
            }
        </script>
        <div id="annual_earnings_chart_div" class="earnings_chart"></div>
        <?php echo ob_get_clean(); ?>

    </div>
<?php }


/**
 * Total Earnings
 *
 * @since 0.1
*/

function sell_media_total_revenue( $post_status=null ) {
    $p = new SellMediaPayments;
    return $p->get_total_payments( $post_status );
}


/**
 *  Function to print out total payments by date
 *
 * @access public
 * @since 1.2
 * @return html
 */
function sell_media_get_sales_by_date( $day = null, $month_num, $year ) {
    $p = new SellMediaPayments;
    return $p->get_payments_by_date( $day, $month_num, $year );
}

/**
 *  Function to get month name by month digit number
 *
 * @access public
 * @since 1.2
 * @return html
 */
function sell_media_month_num_to_name( $n ) {
    $timestamp = mktime( 0, 0, 0, $n, 1, 2005 );

    return date( "M", $timestamp );
}
