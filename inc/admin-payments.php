<?php

/**
 * Admin Payments
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

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
		$paypal_args = __(get_post_meta( $post->ID, '_paypal_args', true ));
		$stripe_args = __(get_post_meta( $post->ID, '_stripe_args', true ));
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
 * Remove the Publish metabox from Payments post type
 */
function sell_media_remove_publish_box() {
	remove_meta_box( 'submitdiv', 'sell_media_payment', 'side' );
}
add_action( 'admin_menu', 'sell_media_remove_publish_box' );

/**
 * Our callback for the payment meta fields, this prints out
 * the html form on the edit payment page
 *
 * @access public
 * @since 0.1
 * @return html
 */
function sell_media_payment_purchase_details( $post ){

	$payments = Sell_Media()->payments;

	_e('<div class="sell-media-admin-payments">','sell_media');
	_e('<input type="hidden" name="sell_media_custom_meta_box_nonce" value="' . wp_create_nonce( basename( __FILE__ ) ) . '" />','sell_media');

	printf(
		'<ul>
		<li>%s: ' . $payments->get_meta_key( $post->ID, 'first_name' ) . ' ' . $payments->get_meta_key( $post->ID, 'last_name' ) . ' ' . '</li>
		<li>%s: ' . $payments->get_meta_key( $post->ID, 'email' ) . ' ' . '</li>
		<li>%s: ' . $payments->total( $post->ID ) . ' ' . '</li>
		</ul>',
		__( 'Name', 'sell_media' ),
		__( 'Email', 'sell_media' ),
		__( 'Total', 'sell_media' )
	);

	do_action( 'sell_media_below_payment_contact_details', $post->ID );

	_e($payments->payment_table( $post->ID ),'sell_media');

	do_action( 'sell_media_additional_customer_meta', $post->ID );

	_e('</div>','sell_media');

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

	$args = Sell_Media()->payments->get_meta( $post->ID );

	?>

	<p><?php _e( 'This is the additional payment data stored with the purchase.', 'sell_media'); ?></p>
	<table class="wp-list-table widefat" cellspacing="0">
		<tbody>
			<?php if ( $args ) : foreach( $args as $k => $v ) : ?>
				<?php if ( ! is_array( $v ) ) : ?>
					<tr>
						<td><?php _e(ucwords( str_replace('_', ' ', $k ) ),'sell_media'); ?></td><td><?php _e($v,'sell_media'); ?></td>
					</tr>
				<?php else : ?>
					<?php $i = 0; ?>
					<?php foreach( $v as $name => $value ) : $i++ ?>
						<?php if ( ! is_array( $name ) ) : ?>
							<tr>
								<td><?php _e( 'Product', 'sell_media' ); ?> <?php _e($i); ?></td>
								<td>
									<ul>
										<?php if ( $value['name'] ) : ?>
											<li><?php _e( 'Name', 'sell_media' ); ?>: <?php esc_attr_e($value['name']); ?></li>
										<?php endif; ?>
										<?php if ( $value['id'] ) : ?>
											<li><?php _e( 'ID', 'sell_media' ); ?>: <a href="<?php _e(admin_url()); ?>post.php?post=<?php _e($value['id']); ?>&amp;action=edit"><?php esc_attr_e($value['id']); ?></a></li>
										<?php endif; ?>
										<?php if ( $value['type'] ) : ?>
											<li><?php _e( 'Type', 'sell_media' ); ?>: <?php esc_attr_e($value['type']); ?></li>
										<?php endif; ?>
										<?php if ( $value['size']['name'] ) : ?>
											<li><?php _e( 'Size', 'sell_media' ); ?>: <?php esc_attr_e($value['size']['name']); ?></li>
										<?php endif; ?>
										<?php if ( $value['license']['name'] ) : ?>
											<li><?php _e( 'License', 'sell_media' ); ?>: <?php esc_attr_e($value['license']['name']); ?></li>
										<?php endif; ?>
										<?php if ( $value['qty'] ) : ?>
											<li><?php _e( 'Qty', 'sell_media' ); ?>: <?php esc_attr_e($value['qty']); ?></li>
										<?php endif; ?>
										<?php if ( $value['total'] ) : ?>
											<li><?php _e( 'Subtotal', 'sell_media' ); ?>: <?php _e(sell_media_get_currency_symbol()); ?><?php _e(number_format( $value['total'], 2, '.', ',' )); ?></li>
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

	$paypal_args = __(get_post_meta( $post->ID, '_paypal_args', true ));
	$stripe_args = __(get_post_meta( $post->ID, '_stripe_args', true ));
	if ( $paypal_args ) {
		$arguments = $paypal_args;
		$gateway = __( 'PayPal', 'sell_media' );
	} else {
		$arguments = $stripe_args;
		$gateway = __( 'Stripe', 'sell_media' );
	}

    do_action('sell_media_payment_before_gateway_details', $post);

	_e('<p>' . __( 'This is the data that was sent from ', 'sell_media' ) . $gateway . __( ' at time of purchase.', 'sell_media' ) . '</p>');
	_e('<ul>','sell_media');
	if ( $arguments ) foreach ( $arguments as $k => $v ) {
		_e('<li><strong>' . $k . ':</strong> ' . ( ( is_array( $v) || is_object( $v ) ) ? serialize( $v ) : $v ) . '</li>');
	}
	_e('</ul>','sell_media');

	do_action('sell_media_payment_after_gateway_details', $post);
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
		<h2><?php _e( 'Earnings Report', 'sell_media' ); ?></h2>
		<div class="tool-box total-revenue">
			<h3 class="title"><?php _e( 'Total Earnings To Date:', 'sell_media' ); ?>&nbsp;<strong><?php print sell_media_get_currency_symbol(); ?><?php print sell_media_total_revenue( $post_status='publish' ); ?></strong></h3>
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
						$day_time   = strtotime( "-" . esc_attr($num_of_days - $i) . "days", time() );
						$day        = date( 'd', $day_time ) + 1;
						$month      = date( 'n', $day_time ) + 1;
						$year       = date( 'Y', $day_time );
						?>
						['<?php _e(date( "n/d", mktime( 0, 0, 0, $month, $day, $year ) ),'sell_media'); ?>',
						<?php _e(sell_media_get_sales_by_date( $day, $month, $year ),'sell_media'); ?>,
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
		<?php _e(ob_get_clean(),'sell_media'); ?>


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
						['<?php _e(sell_media_month_num_to_name($i) . ' ' . date("Y"),'sell_media'); ?>', <?php _e(sell_media_get_sales_by_date(null, $i, date('Y'),'sell_media') ); ?>,
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
		<?php _e(ob_get_clean(),'sell_media'); ?>


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
						['<?php _e($i,'sell_media'); ?>',
						<?php _e(sell_media_get_sales_by_date(null, null, $i ),'sell_media'); ?>,
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
		<?php _e(ob_get_clean(),'sell_media'); ?>

	</div>
<?php }


/**
 * Total Earnings
 *
 * @since 0.1
*/

function sell_media_total_revenue( $post_status=null ) {
	return Sell_Media()->payments->get_total_payments( $post_status );
}


/**
 *  Function to print out total payments by date
 *
 * @access public
 * @since 1.2
 * @return html
 */
function sell_media_get_sales_by_date( $day = null, $month_num, $year ) {
	return Sell_Media()->payments->get_payments_by_date( $day, $month_num, $year );
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

/**
 * Filter column headers names on the edit media table.
 *
 * @since 0.1
 */
function sell_media_payment_header( $columns ){

	$columns_local = array();

	// Allows to "move the checkbox" to be first
	if ( isset( $columns['cb'] ) ) {
		$columns_local['cb'] = $columns['cb'];
		unset($columns['cb']);
	}

	unset( $columns['title'] );

	// Our next column header is the 'id', we use this,
	// to ensure that our head has the class 'column-id'
	if ( ! isset( $columns_local['id'] ) )
		$columns_local['id'] = __("Payment ID", "sell_media");

	if ( ! isset( $columns_local['products'] ) )
		$columns_local['products'] = __("Products", "sell_media");

	if ( ! isset( $columns_local['customer'] ) )
		$columns_local['customer'] = __("Customer", "sell_media");

	if ( ! isset( $columns_local['total'] ) )
		$columns_local['total'] = __("Total", "sell_media");

	return array_merge( $columns_local, $columns );
}
add_filter( 'manage_edit-sell_media_payment_columns', 'sell_media_payment_header' );


/**
 * Filter custom column content on the edit media table.
 *
 * @since 0.1
 */
function sell_media_payment_content( $column, $post_id ){
	switch( $column ) {
		case "id":
			$html = '<a href="' . site_url() . '/wp-admin/post.php?post=' . $post_id . '&action=edit">';
			$html .= $post_id;
			$html .= '</a>';
			_e($html, 'sell_media');
			break;
		case "products":
			$products = Sell_Media()->payments->get_products( $post_id );
			if ( $products ) foreach ( $products as $product ) {
				$type = ( ! empty( $product['type'] ) ) ? ' (' . $product['type'] . ') ' : '';
				_e(apply_filters( 'sell_media_payment_products_column', $product['name'] . $type, $post_id ) . '<br />','sell_media');
			}
			break;
		case "customer":
			_e(Sell_Media()->payments->get_buyer_name( $post_id ),'sell_media');
			break;
		case "total":
			_e(sell_media_get_currency_symbol() . number_format( Sell_Media()->payments->get_meta_key( $post_id, 'total' ), 2, '.', '' ),'sell_media');
			break;
		default:
			break;
	}
}
add_filter( 'manage_sell_media_payment_posts_custom_column', 'sell_media_payment_content', 10, 2 );