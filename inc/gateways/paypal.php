<?php
require __DIR__ . '/php-paypal-sdk/vendor/autoload.php';
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Payments\CapturesRefundRequest;

// Creating an environment
/**
 * Our PayPal Checkout class
 */
class SellMediaPayPal {

    /**
     * Sell Media setting.
     *
     * @var object
     */
    private $settings;

    public function __construct() {

        add_filter( 'sell_media_admin_notices', array( &$this, 'sell_media_admin_notices' ) );
        add_action( 'sell_media_payment_after_gateway_details', array($this, 'sell_media_refund_payment_html') );
        add_action( 'sell_media_admin_scripts_hook', array($this, 'sell_media_admin_scripts_hook'));

        // This action work only logged-in users only
        add_action( 'wp_ajax_sell_media_paypal_order_refund', array($this, 'sell_media_paypal_refund_order'));

    }

    /*
     * Register scripts for PayPal payment gateway
     * */
    public function sell_media_admin_scripts_hook() {
        $translation_array = array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'paypal_refund_nonce' => wp_create_nonce( 'sell-media-paypal-payment-refund' ),
            'paypal_refund_label' => esc_attr__('Order refund ID: ', 'sell_media')
        );
        wp_localize_script( 'sell_media-admin-items', 'sell_media_paypal', $translation_array );
    }

    /*
     *  Register admin notice.
     * */
    public function sell_media_admin_notices($notices) {

        global $current_screen;
        if ( 'sell_media_item_page_sell_media_plugin_options' !== $current_screen->id ) {
            return $notices;
        }

        // Check for curl
        if ( ! function_exists( 'curl_version' ) ) {
            $message = __( 'PayPal not supported, please enable <code>cURL</code>.', 'sell_media' );
        }

        /**
         * Empty PayPal API Key
         */
        $secret_key = self::keys( 'secret_key' );
        if ( empty( $secret_key ) ) {
            $notices[] = array(
                'slug' => 'paypal-api-key',
                'message' => sprintf( __( 'Please add valid <a href="%1$s">PayPal API keys</a>.', 'sell_media' ), esc_url( admin_url( 'edit.php?post_type=sell_media_item&page=sell_media_plugin_options&tab=sell_media_payment_settings' ) ) ),
                'type' => 'error',
            );
        }

        return $notices;
    }

    /**
     * Checks if the site is in test mode and returns the correct
     * keys as needed
     *
     * @param $key (string) secret_key | client_id
     * @return Returns either the test or live key based on the general setting "test_mode"
     */
    public static function keys( $key = null ) {
        $settings = sell_media_get_plugin_options();
        $keys = array(
            'secret_key' => $settings->test_mode ? $settings->paypal_test_client_secret_key : $settings->paypal_live_client_secret_key,
            'client_id'  => $settings->test_mode ? $settings->paypal_test_client_id : $settings->paypal_live_client_id
        );

        return $keys[ $key ];
    }

    /**
     * Returns PayPal HTTP client instance with environment which has access
     * credentials context. This can be used invoke PayPal API's provided the
     * credentials have the access to do so.
     */
    public static function client() {
        return new PayPalHttpClient(self::environment());
    }

    /**
     * Setting up and Returns PayPal SDK environment with PayPal Access credentials.
     * For demo purpose, we are using SandboxEnvironment. In production this will be
     * ProductionEnvironment.
     */
    public static function environment() {

        $clientId = self::keys('client_id');
        $clientSecret = self::keys('secret_key');
        $settings = sell_media_get_plugin_options();
        if($settings->test_mode) {
            return new SandboxEnvironment($clientId, $clientSecret);
        } else {
            return new ProductionEnvironment($clientId, $clientSecret);
        }
    }

    /*
     * PayPal refund form
     * */
    public function sell_media_refund_payment_html( $_payment_obj ) {

        $payment_id = $_payment_obj->ID;
        $_order_data        = get_post_meta( $payment_id, '_sell_media_payment_meta', true);
        $_currency_code     = get_post_meta( $payment_id, 'payment_currency_code', true);
        $_order_total_paid  = (isset($_order_data['total']) && !empty($_order_data['total'])) ? $_order_data['total'] : 0;
        $_order_total_paid  = apply_filters('sell_media_paypal_order_total', $_order_total_paid, $payment_id);
        $_order_refund_id   = get_post_meta($payment_id, 'sell_media_paypal_payment_refund_id', true);

        // check current order placed by PayPal Or not
        if (isset($_order_data['gateway']) && $_order_data['gateway'] == 'paypal') {
            $transaction_id = (isset($_order_data['transaction_id'])) ? $_order_data['transaction_id'] : '';
            ?>
            <ul class="paypal-image-refund-wrapper">
                <?php do_action('sell_media_before_refund_form'); ?>
                <?php if(!$_order_refund_id) { ?>
                    <li>
                        <label for="paypal-order-amount"><?php esc_html_e('Enter amount which you want to refund.','sell_media'); ?></label>
                        <input type="number" id="paypal-order-amount" class="paypal-order-amount" value="<?php echo esc_attr( $_order_total_paid ); ?>" min="0" />
                    </li>
                    <li class="paypal-order-refund-action">
                        <button type="button" id="paypal_payment_refund_btn" class="button button-primary button-large" data-transaction_id="<?php echo esc_attr( $transaction_id ); ?>" ><?php 
                        esc_attr_e('Refund Now', 'sell_media'); ?></button>
                        <input type="hidden" id="paypal_payment_id" value="<?php echo esc_attr( $payment_id ); ?>"/>
                        <input type="hidden" id="paypal_payment_currency_code" value="<?php echo esc_attr( $_currency_code ); ?>"/>
                    </li>
                <?php } else { ?>
                    <li class="order-refund-msg">
                        <strong><?php esc_html_e('Order refund ID: ', 'sell_media'); ?></strong> <?php echo esc_html( $_order_refund_id ); ?>
                    </li>
                <?php } ?>
                <?php do_action('sell_media_after_refund_form'); ?>
            </ul>
            <?php
        }
    }

    /**
     * This function can be used to preform refund on the captured order.
     * @param $captureId (string) PayPal order captured ID
     */
    public function sell_media_paypal_refund_order($captureId) {

        $_result = array();
        $_result['status'] = false;
        $_result['message'] = apply_filters('sell_media_order_refund_fail_message', esc_attr__('Order refund process fail, Please try again', 'sell_media'));
        if(isset($_POST['transaction_id']) && '' != sanitize_text_field($_POST['transaction_id']) && isset($_POST['_nonce']) && wp_verify_nonce($_POST['_nonce'], 'sell-media-paypal-payment-refund')) {

            $_payment_id        = (isset($_POST['payment_id']) && '' != sanitize_text_field($_POST['payment_id'])) ? sanitize_text_field($_POST['payment_id']) : 0;
            $_payment_obj       = get_post_meta($_payment_id, '_sell_media_payment_meta', true);
            $_refund_amount     = (isset($_POST['refund_amount']) && '' != sanitize_text_field($_POST['refund_amount'])) ? sanitize_text_field($_POST['refund_amount']) : sanitize_text_field($_payment_obj['total']);
            $_currency_code     = get_post_meta($_payment_id, 'payment_currency_code', true);
            $_capture_id        = get_post_meta($_payment_id, 'payment_capture_id', true);

            $request = new CapturesRefundRequest($_capture_id);
            $request->body = self::buildRequestBody($_refund_amount, $_currency_code);
            $client = self::client();
            $response = $client->execute($request);
            if($response->result->status == 'COMPLETED') {
                update_post_meta($_payment_id, 'sell_media_paypal_payment_refund_response', $response);
                update_post_meta($_payment_id, 'sell_media_paypal_payment_refund_id', $response->result->id);
                $_result['status'] = true;
                $_result['refund_id'] = $response->result->id;
                $_message = sprintf(
                                __('Order success fully refunded. <strong>Refund ID: %s</strong>','sell_media'),
                                $response->result->id
                            );
                $_result['message'] = apply_filters('sell_media_order_refund_success_message', $_message);
            } else if ($response->statusCode < 200 || $response->statusCode > 300) {
                $_result['message'] = $response->result->details[0]->description;
            }
        }
        wp_send_json($_result);
    }

    /**
     * Function to build a refund refund request body.
     * @param $_amount (Float)
     * @param $_currency_code (String)
     * @return array
     */
    public static function buildRequestBody($_amount, $_currency_code) {

        //parameters
        return array(
            'amount' =>
                array(
                    'value' => $_amount,
                    'currency_code' => $_currency_code
                )
        );
    }
}

$paypal = new SellMediaPayPal();