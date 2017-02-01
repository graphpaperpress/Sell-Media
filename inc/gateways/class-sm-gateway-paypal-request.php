<?php
/**
 * Paypal payment request
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
* Paypal payment request.
*/
class SM_Gateway_Paypal_Request {

	function __construct() {

		add_action( 'sell_media_above_checkout_button', array( $this, 'form' ) );
		add_action( 'init', array( $this, 'process' ) );

	}

	function form(){
	?>
	<form id="sell_media_payment_gateway" style="margin: 20px 0;display:none;" method="post">
		<?php do_action( 'sell_media_payment_gateway_fields' ); ?>
		<label for="paypal"><input type="radio" name="gateway" id="paypal" value="paypal" checked><?php _e( 'PayPal', 'sell_media' ); ?></label>
	</form>
	<?php
	}

	function process(){

        // Before payment process action.
        do_action( 'sell_media_before_payment_process' );

		// Check if paypal is selected.
		if( !isset( $_POST['gateway'] ) || 'paypal' !== $_POST['gateway'] ){
			return;
		}

		$args = $this->get_args();

		$redirect_uri = esc_url( home_url( '/' ) );

		if( $args ){
	        $paypal_args = http_build_query( $args, '', '&' );
	        $redirect_uri = esc_url( sell_media_get_paypal_redirect() ) . '?' . $paypal_args;
		}

        wp_redirect( $redirect_uri );

        exit;
	}

	private function get_args(){
        global $sm_cart;

		// Get settings.
        $settings = sell_media_get_plugin_options();

        // Check if paypal email is set.
        if( !isset( $settings->paypal_email ) || '' === $settings->paypal_email ){
        	return false;
        }

        $paypal_email = sanitize_email( $settings->paypal_email );
        $subtotal = apply_filters( 'sell_media_paypal_subtotal', $sm_cart->getSubtotal( false ) );
        $item_args = $this->get_item_args();

        if( !$item_args ){
        	return false;
        }

	    $args['cmd'] = "_cart";
        $args['upload']        = "1";
        $args['currency_code'] = sanitize_text_field( $settings->currency );
        $args['business']      = sanitize_email( $paypal_email );
        $args['bn']            = "GraphPaperPress_SP";
        $args['rm']            = "2";
        $args['tax_cart']      = ( isset( $settings->tax ) && !empty( $settings->tax ) && ( 'exclusive' == $settings->tax_display || empty( $settings->tax_display ) ) )? number_format( $subtotal * $settings->tax_rate, 2 ) : 0;

        if( isset( $settings->shipping ) )
            $shipping = $settings->shipping;
        else
            $shipping = 0;

        $args['charset']       = get_bloginfo( 'charset' );
        $args['cbt']           = get_bloginfo( 'name' );
        $args['return'] = esc_url( get_permalink( $settings->thanks_page ) );
        $args['cancel'] = empty( $settings->checkout_page ) ? null : esc_url( get_permalink( $settings->checkout_page ) );
        $args['custom'] = 0;
        $args['handling']   = apply_filters( 'sell_media_payment_gateway_handling', number_format( 0, 2 ) );
        $args['handling_cart'] = apply_filters( 'sell_media_payment_gateway_handling_cart', number_format( 0, 2 ) );
        $args['no_shipping'] = apply_filters( 'sell_media_shipping', 0 );
        $args['notify_url'] = esc_url( add_query_arg( 'sell_media-listener', 'IPN', home_url( 'index.php' ) ) );

        return apply_filters( 'sell_media_paypal_args', array_merge(
			$args,
			$item_args
		) );
	}

	private function get_item_args(){
		global $sm_cart;
		$cart_items = $sm_cart->getItems();
		if( empty( $cart_items ) )
			return false;

		$index = 1;
        foreach ( $cart_items as $key => $item ) {
            $args['item_name_' . $index ]   = $item['item_name'];
            $args['quantity_' . $index ]   = $item['qty'];
            $args['amount_' . $index ]   = apply_filters( 'sell_media_payment_gateway_item_price', number_format( $item['price'], 2 ), $item['price'] );
            $args['item_number_'.$index]   = $item['item_id'];
            $args["on0_" . $index] = 'type';
            $args["on1_" . $index] = 'image';
            $args["on2_" . $index] = 'pgroup';
            $args["on3_" . $index] = 'size';
            $args["on4_" . $index] = 'usage';
            $args["on5_" . $index] = 'license';
            $args["on6_" . $index] = 'attachment';
            $args['option_index_0' ] = "7";

            $args["os0_" . $index] = $item['item_type'];
            $args["os1_" . $index] = $item['item_image'];
            $args["os2_" . $index] = $item['item_pgroup'];
            $args["os3_" . $index] = $item['item_size'];
            $args["os4_" . $index] = $item['item_usage'];
            $args["os5_" . $index] = $item['item_license'];
            $args["os6_" . $index] = $item['item_attachment'];
            $index++;
        }

        return $args;

	}
}

new SM_Gateway_Paypal_Request();
