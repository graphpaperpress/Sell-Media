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

	echo '<div class="sell-media-admin-payments">';
	echo '<input type="hidden" name="sell_media_custom_meta_box_nonce" value="' . wp_create_nonce( basename( __FILE__ ) ) . '" />';

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

	echo $payments->payment_table( $post->ID );

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

	$args = Sell_Media()->payments->get_meta( $post->ID );

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
	echo '<p>' . __( 'This is the data that was sent from ', 'sell_media' ) . $gateway . __( ' at time of purchase.', 'sell_media' ) . '</p>';
	echo '<ul>';
	if ( $arguments ) foreach ( $arguments as $k => $v ) {
		echo '<li><strong>' . $k . ':</strong> ' . ( ( is_array( $v) || is_object( $v ) ) ? serialize( $v ) : $v ) . '</li>';
	}
	echo '</ul>';
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
		$columns_local['id'] = "Payment ID";

	if ( ! isset( $columns_local['products'] ) )
		$columns_local['products'] = "Products";

	if ( ! isset( $columns_local['customer'] ) )
		$columns_local['customer'] = "Customer";

	if ( ! isset( $columns_local['total'] ) )
		$columns_local['total'] = "Total";

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
			echo $html;
			break;
		case "products":
			$products = Sell_Media()->payments->get_products( $post_id );
			if ( $products ) foreach ( $products as $product ) {
				$type = ( ! empty( $product['type'] ) ) ? ' (' . $product['type'] . ') ' : '';
				echo apply_filters( 'sell_media_payment_products_column', $product['name'] . $type, $post_id ) . '<br />';
			}
			break;
		case "customer":
			echo Sell_Media()->payments->get_buyer_name( $post_id );
			break;
		case "total":
			echo sell_media_get_currency_symbol() . number_format( Sell_Media()->payments->get_meta_key( $post_id, 'total' ), 2, '.', '' );
			break;
		default:
			break;
	}
}
add_filter( 'manage_sell_media_payment_posts_custom_column', 'sell_media_payment_content', 10, 2 );