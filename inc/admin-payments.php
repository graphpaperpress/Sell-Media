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
        'sell_media_payment_render_contact',
        'sell_media_payment'
    );
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
function sell_media_payment_render_contact( $post ){
    print '<div class="sell-media-admin-payments">';
    print '<input type="hidden" name="sell_media_custom_meta_box_nonce" value="' . wp_create_nonce( basename( __FILE__ ) ) . '" />';

    if ( get_userdata( get_post_meta( $post->ID, '_sell_media_user_id', true ) ) ){
        $edit_link = '<a href="' . get_edit_user_link( get_post_meta( $post->ID, '_sell_media_user_id', true ) ) . '">Edit</a>';
    } else {
        $edit_link = null;
    }

    $contact = array(
            'first_name' => get_post_meta( $post->ID, '_sell_media_payment_first_name', true ),
            'last_name' => get_post_meta( $post->ID, '_sell_media_payment_last_name', true ),
            'user_edit_link' => $edit_link,
            'email' => get_post_meta( $post->ID, '_sell_media_payment_user_email', true )
        );

    printf( '%s %s %s %s',
        '<p>Name: ' . $contact['first_name'],
        $contact['last_name'],
        $contact['user_edit_link'],
        '<br />Email: <a href="mailto:' . $contact['email'] . '">' . $contact['email'] . '</a></p>'
        );

    $links = sell_media_build_download_link( $post->ID, get_post_meta( $post->ID, "_sell_media_payment_user_email", true ) );

    print '<table class="wp-list-table widefat" cellspacing="0">';
    print '<thead>
            <tr>
                <th scope="col">' . __('Item','sell_media') . '</th>
                <th>' . __('Price','sell_media') . '</th>
                <th>' . __('License','sell_media') . '</th>
                <th>' . __('Download Link','sell_media') . '</th>
            </tr>
        </thead>';
    print '<tbody>';
    foreach( $links as $link ){

        switch( $link['price_id'] ){
            case 'sell_media_small_file':
                $price_id = 'small';
                break;
            case 'sell_media_medium_file':
                $price_id = 'medium';
                break;
            case 'sell_media_large_file':
                $price_id = 'large';
                break;
            case 'sell_media_original_file':
                $price_id = "";
                break;
            default:
                $price_id = null;
                break;
        }

        if ( empty( $link['license_id'] ) ){
            $license = null;
        } else {
            $license = get_term( $link['license_id'], 'licenses' );
            $license = $license->name;
        }

        print '<tr class="" valign="top">';
        print '<td class="media-icon">' . $link['thumbnail'] . '</td>';
        print '<td>' . sell_media_item_price( $link['item_id'], true, $price_id, false ) . '</td>';
        print '<td>' . $license . '</td>';
        print '<td class="title column-title"><input type="text" value="' . $link['url'] . '" /></td>';
        print '</tr>';
    }
    print '</tbody>';
    print '</table>';

    do_action( 'sell_media_additional_customer_meta', $post );

    print '</div>';
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
        <?php screen_icon(); ?>
        <h2><?php _e( 'Payments', 'sell_media' ); ?></h2>
        <div class="tool-box total-revenue">
            <h3 class="title"><?php _e( 'Total Earnings To Date:', 'sell_media' ); ?>&nbsp;<strong><?php print sell_media_get_currency_symbol(); ?><?php print sell_media_total_revenue( $post_status='publish' ); ?></strong></h3>
            <p><?php _e( 'Below is a breakdown of each transaction. Payments marked as "Pending" mean the buyer checked out, but abandoned payment.', 'sell_media' ); ?></p>
            <p><?php printf( __( 'Want to increase your sales? <a href="%s" class="button secondary" target="_blank">Download Extensions for Sell Media</a>', 'sell_media' ), sell_media_plugin_data( $field='AuthorURI' ) . '/downloads/category/extensions/' ); ?></p>
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
                    <td><a href="<?php print site_url() . '/wp-admin/post.php?post='.$payment->ID.'&action=edit'; ?>"><?php echo $payment->ID; ?></a></td>
                    <td>
                        <?php if ( ! empty( $payment_meta['first_name'] ) ) echo $payment_meta['first_name']; ?>
                        <?php if ( ! empty( $payment_meta['last_name'] ) ) echo $payment_meta['last_name']; ?>
                    </td>
                    <td>
                    <?php
                        $payment_meta_array = get_post_meta( $payment->ID, '_sell_media_payment_meta', true );
                        if ( $payment_meta_array ){
                            $products_meta_array = unserialize( $payment_meta_array['products'] );

                            if ( ! $products_meta_array ) continue;
                            $count = count( $products_meta_array );
                            $i = 0;

                            foreach( $products_meta_array as $product ){
                                $comma = ( $count - 1) == $i ? null : ", ";
                                print '<a href="' . get_edit_post_link( $product['item_id'] ) . '">'. get_the_title( $product['item_id'] ) . "</a>" . $comma;
                                if ( isset( $product['License'] ) ){
                                    $license = get_term_by( 'id', $product['License'], 'licenses' );
                                    if ( $license ) print ' &ndash; <em>'.$license->name . '</em><br />';
                                }
                                $i++;
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
 * Total Earnings
 *
 * @since 0.1
*/

function sell_media_total_revenue( $post_status=null ) {
    $total = ( float ) 0;
    $payments = get_transient( 'sell_media_total_revenue_' . $post_status );
    if ( false === $payments || '' === $payments ) {
        $args = array(
            'mode' => 'live',
            'post_type' => 'sell_media_payment',
            'posts_per_page' => -1,
            'post_status' => $post_status,
            'meta_key' => '_sell_media_payment_amount'
        );
        set_transient( 'sell_media_total_revenue_' . $post_status, $payments, 1800 );
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
            <p><?php printf( __( 'Below is a breakdown of earnings per day, month and year. Want to increase your sales? <a href="%s" class="button secondary" target="_blank">Download Extensions for Sell Media</a>', 'sell_media' ), sell_media_plugin_data( $field='AuthorURI' ) . '/downloads/category/extensions/' ); ?></p>
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
 *  Function to print out total payments by date
 *
 * @access public
 * @since 1.2
 * @return html
 */
function sell_media_get_sales_by_date( $day = null, $month_num, $year ) {
    $args = array(
        'post_type' => 'sell_media_payment',
        'posts_per_page' => -1,
        'year' => $year,
        'monthnum' => $month_num,
        'post_status' => 'publish'
    );
    if( ! empty( $day ) )
        $args['day'] = $day;

    $sales = get_posts( $args );
    $total = 0;
    if( $sales ) {
        foreach ( $sales as $sale ) {
            $payment_amount = get_post_meta( $sale->ID, '_sell_media_payment_amount', true );
            $total = $total + $payment_amount;
        }
    }
    return $total;
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
