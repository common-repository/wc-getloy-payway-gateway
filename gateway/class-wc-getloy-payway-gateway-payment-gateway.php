<?php

class Wc_Getloy_Payway_Gateway_Payment_Gateway extends WC_Payment_Gateway {

    /**
     * Instance of GetLoy connector
     *
     * @var Wc_Getloy_Payway_Gateway_Getloy_Connector   $getloy_connector  Instance of GetLoy connector
     */
    protected $getloy_connector;
         
    /**
     * Constructor for the gateway.
     *
     * @return void
     */
    public function __construct() {
    
        $plugin = Wc_Getloy_Payway_Gateway::instance();
        $this->id                 = 'getloy_payway';
        $this->has_fields         = false;
        $this->pluginIdentifier   = sprintf('%s v%s', $plugin->get_plugin_name(), $plugin->get_version());

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        $this->enabled                  = $this->get_option('enabled'); 
        $this->title                    = $this->get_option('title');
        $this->description              = $this->get_option('description');
        $this->method_title             = __( 'PayWay (powered by GetLoy)', 'wc-getloy-payway-gateway' );
        $this->method_description       = __( 'Accept international credit card payments to your Cambodian bank account through GetLoy using PayWay by ABA Bank.', 'wc-getloy-payway-gateway' );
        $this->merchant_id              = $this->get_option('merchant_id');
        $this->testmode                 = $this->get_option('testmode');
        $this->test_api_key             = $this->get_option('test_api_key');
        $this->production_api_key       = $this->get_option('production_api_key');
        $this->getloy_token             = $this->get_option('getloy_token');
        $this->transaction_id_prefix    = $this->get_option('transaction_id_prefix');
        $this->transaction_id_suffix    = $this->get_option('transaction_id_suffix');
        $this->config_error             = ! $this->check_config(false);

        if (! $this->config_error) {
            $this->getloy_connector = new Wc_Getloy_Payway_Gateway_Getloy_Connector(
                $this->getloy_token,
                $this->merchant_id, 
                $this->testmode == 'yes' ? $this->test_api_key : $this->production_api_key,
                $this->pluginIdentifier, 
                $this->testmode == 'yes'
            );
        }
    }

    /**
     * Get gateway icons
     *
     * @return string HTML code for including the icons
     */
     public function get_icon() {
		$icon  = sprintf(
            '<img src="%s" alt="Visa" width="42px" />', 
            WC_HTTPS::force_https_url( plugins_url( '../public/images/visa.svg', __FILE__ ) ) 
        ) . sprintf(
            '<img src="%s" alt="MasterCard" width="42px" />', 
            WC_HTTPS::force_https_url( plugins_url( '../public/images/mastercard.svg', __FILE__ ) ) 
        );

		return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );
    } 
    
    /**
     * Initialize Gateway Settings Form Fields
     *
     * @return void
     */
    public function init_form_fields() {
        
        $this->form_fields = apply_filters( 'wc_getloy_payway_gateway_form_fields', array(
            'enabled' => array(
                'title'       => __( 'Enable/Disable', 'wc-getloy-payway-gateway' ),
                'label'       => __( 'Enable PayWay (powered by GetLoy)', 'wc-getloy-payway-gateway' ),
                'type'        => 'checkbox',
                'description' => '',
                'default'     => 'no',
            ),
            'title' => array(
                'title'       => __( 'Title', 'woocommerce' ),
                'type'        => 'text',
                'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
                'default'     => __( 'Debit / Credit Card', 'wc-getloy-payway-gateway' ),
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => __( 'Description', 'woocommerce' ),
                'type'        => 'text',
                'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce' ),
                'default'     => __( '', 'wc-getloy-payway-gateway' ),
                'desc_tip'    => true,
            ),
            'getloy_token' => array(
                'title'       => __( 'GetLoy merchant token', 'wc-getloy-payway-gateway' ),
                'type'        => 'text',
                'description' => __( 'You will receive this token from GetLoy after setting up your account.', 'wc-getloy-payway-gateway' ),
                'default'     => '',
                'desc_tip'    => true,
            ),
            'merchant_id' => array(
                'title'       => __( 'PayWay merchant ID', 'wc-getloy-payway-gateway' ),
                'type'        => 'text',
                'description' => __( 'You will receive your merchant ID by email from ABA Bank. It is the same for test and production mode.', 'wc-getloy-payway-gateway' ),
                'default'     => '',
                'desc_tip'    => true,
            ),
            'testmode' => array(
                'title'       => __( 'Test mode', 'wc-getloy-payway-gateway' ),
                'label'       => __( 'Enable Test Mode', 'wc-getloy-payway-gateway' ),
                'type'        => 'checkbox',
                'description' => __( 'Place the payment gateway in test mode (no actual payments will be made).', 'wc-getloy-payway-gateway' ),
                'default'     => 'yes',
                'desc_tip'    => false,
            ),
            'test_api_key' => array(
                'title'       => __( 'PayWay test API key', 'wc-getloy-payway-gateway' ),
                'type'        => 'text',
                'description' => __( 'You will receive this key from ABA Bank with your test account credentials.', 'wc-getloy-payway-gateway' ),
                'default'     => '',
                'desc_tip'    => true,
            ),
            'production_api_key' => array(
                'title'       => __( 'PayWay production API key', 'wc-getloy-payway-gateway' ),
                'type'        => 'text',
                'description' => __( 'You will receive this key from ABA Bank after completing the tests.', 'wc-getloy-payway-gateway' ),
                'default'     => '',
                'desc_tip'    => true,
            ),
            'advanced' => array(
                'title'       => __( 'Advanced settings', 'woocommerce' ),
                'type'        => 'title',
                'description' => 'Control the way payments are displayed in PayWay.',
            ), 
            'transaction_id_prefix' => array(
                'title'       => __( 'Transaction ID prefix', 'wc-getloy-payway-gateway' ),
                'type'        => 'text',
                'description' => __( 'This text will be prepended to the WooCommerce order number to create the PayWay order ID. This makes it easy to see in the PayWay order management where the payments came from.', 'wc-getloy-payway-gateway' ),
                'default'     => 'WC-',
            ),
            'transaction_id_suffix' => array(
                'title'       => __( 'Transaction ID suffix', 'wc-getloy-payway-gateway' ),
                'type'        => 'text',
                'description' => __( 'This text will be appended to the WooCommerce order number to create the PayWay order ID.', 'wc-getloy-payway-gateway' ),
                'default'     => '',
            ),
        ) );
        
    }

    /**
     * Process the payment and return the result
     *
     * @access public
     * @param int $order_id
     * @return array
     */
    public function process_payment( $order_id ) {
        
        if ($this->config_error) {
            $this->add_notice(__(
                'The PayWay payment gateway is not configured properly. Please ask the site administrator to complete the configuration.', 
                'wc-getloy-payway-gateway'
                ), 
                'error'
            );
            return false;
        } 

        $order_id = absint( $order_id );
        $order = wc_get_order( $order_id );
        if ( ! $order || $order->get_id() !== $order_id) {
            $this->add_notice( __( 
                'Sorry, this order is invalid and cannot be paid for.', 
                'woocommerce' 
                ),
                'error'
            );
            return;
        }

        return array(
            'result'   => 'success',
            'redirect' => $order->get_checkout_payment_url( true )
        );    

    }

    /**
     * Display the PayWay payment form on the payment page
     *
     * @access public
     * @param int $order_id
     * @return array
     */
    public function receipt_page( $order_id ) {

        if ($this->config_error) {
            $this->add_notice(__(
                'The PayWay payment gateway is not configured properly. Please ask the site administrator to complete the configuration.', 
                'wc-getloy-payway-gateway'
                ), 
                'error'
            );
            return;
        } 
        
        $order_id = absint( $order_id );
        $order = wc_get_order( $order_id );
        if ( ! $order || $order->get_id() !== $order_id) {
            $this->add_notice( __( 
                'Sorry, this order is invalid and cannot be paid for.', 
                'woocommerce' 
                ),
                'error'
            );
            return;
        }
        $this->order = $order;

        $payment_status = get_post_meta( $order_id, '_wc_getloy_payway_gateway_payment_status', true);

        if ( $payment_status && $payment_status !== 'ongoing') {
            $this->add_notice(__(
                'Payment not possible - the payment may have timed out or failed. ' . 
                'Please place a new order instead.', 
                'wc-getloy-payway-gateway'
                ), 
                'error'
            );
            error_log(
                sprintf(
                    '%s (receipt_page): Payment not processed because of payment status "%s" (order status "%s").',
                    $this->id,
                    $payment_status,
                    $order->get_status()
                )
            );
            return;
        }

        $transaction_id = $this->generateTransactionId( $order->get_order_number() );
        update_post_meta( $order_id, '_wc_getloy_payway_gateway_transaction_id', $transaction_id);
        update_post_meta( $order_id, '_wc_getloy_payway_gateway_payment_status', 'ongoing');

        echo '<div class="getloy"></div>';

    }

    /**
     * Add JS code to start GetLoy
     *
     * @access public
     * @param int $order_id
     * @return array
     */
    public function receipt_page_footer( ) {


        if ($this->config_error) {
            return;
        } 
        
        $order = $this->order;
        if ( ! $order ) {
            return;
        }

        $transaction_id = get_post_meta( $order->get_id(), '_wc_getloy_payway_gateway_transaction_id', true);

        $payway_params = $this->getloy_connector->generateCreateTransactionParams(
            $transaction_id, 
            $order->get_total(),
            $order->get_currency(),
            get_rest_url(null, sprintf('/wc-getloy-payway-gateway/v1/payments/%s/status', $transaction_id)),
            $this->transformOrderItems($order),
            $order->get_billing_first_name(),
            $order->get_billing_last_name(),
            $order->get_billing_phone(),
            $order->get_billing_email()
        );

        echo <<< EOD
<script>
!function(g,e,t,l,o,y){g.GetLoyPayments=t;g[t]||(g[t]=function(){
(g[t].q=g[t].q||[]).push(arguments)});g[t].l=+new Date;o=e.createElement(l);
y=e.getElementsByTagName(l)[0];o.src='https://some.getloy.com/';
y.parentNode.insertBefore(o,y)}(window,document,'gl','script');
EOD;
        printf("gl('payload', %s);" . PHP_EOL, json_encode($payway_params));
        printf("gl('success_callback', function(){window.location='%s';});" . PHP_EOL, $order->get_checkout_order_received_url());
        echo <<< EOD
</script>
EOD;

    }

    /**
     * Transform the items of a WooCommerce order to the format required for the gateway
     *
     * @access public
     * @param WC_Order $order   The order whose items are to be transformed
     * @return array    Array of Wc_Getloy_Payway_Gateway_Getloy_Transaction_Item items
     */
    public function transformOrderItems( WC_Order $order ) {
        
        $wc_items = $order->get_items();
        $gl_items = [];
        
        foreach ($wc_items as $wc_item) {
            $amount = round( ( $wc_item['line_subtotal'] + $wc_item['line_subtotal_tax'] ) / $wc_item['qty'] , 2 );
            $gl_items[] = new Wc_Getloy_Payway_Gateway_Getloy_Transaction_Item(
                $wc_item['name'],
                (int) $wc_item['quantity'],
                $amount
            );
        }

        return $gl_items;
    }
        
    /**
     * Handle a callback from GetLoy
     *
     * @access public
     * @return void
     */
    public function status_update_callback( WP_REST_Request $request ) {
        if (!$this->config_error) {            

            if (! $request ) {
                error_log(
                    sprintf(
                        '%s (status_update_callback): No request passed to handler',
                        $this->id
                    )
                );
                
                return [
                    'status'    => 'error',
                    'message'   => 'no payload'
                ];
            }

            $transaction_id = $request->get_param('tid');
            $status = $request->get_param('status');
            $auth_hash = $request->get_param('auth_hash');

            if ( ! $this->getloy_connector->validate_callback_hash($transaction_id, $status, $auth_hash) ) {
                error_log(
                    sprintf(
                        '%s (status_update_callback): Received invalid callback: %s',
                        $this->id,
                        json_encode($request->get_params())
                    )
                );
                
                return [
                    'status'    => 'error',
                    'message'   => 'invalid request'
                ];
            }

            $order_id = $this->lookupOrderId($transaction_id);
            $order = wc_get_order( $order_id );
            $payment_status = get_post_meta( $order_id, '_wc_getloy_payway_gateway_payment_status', true);
    
            if ( $status === 'successful' && $payment_status === 'ongoing' ) {

                update_post_meta( $order_id, '_wc_getloy_payway_gateway_payment_status', 'complete');
                $order->payment_complete();
                $order->add_order_note( sprintf( __( 'PayWay payment received - transaction ID: %s', 'wc-getloy-payway-gateway' ), $transaction_id ) );

            } elseif ( $status === 'timed_out' && $payment_status === 'ongoing' ) {

                update_post_meta( $order_id, '_wc_getloy_payway_gateway_payment_status', 'timeout');
                $order->update_status('failed', __( 'Payment timed out', 'wc-getloy-payway-gateway' ));

            } else {

                error_log(
                    sprintf(
                        '%s (status_update_callback): Cannot process callback for order %d: Status is "%s", but payment status is "%s".',
                        $this->id,
                        $order_id,
                        $status,
                        $payment_status
                    )
                );

                return [
                    'status'    => 'error',
                    'message'   => 'invalid transaction status'
                ];
                
            }

            return [
                'status'    => 'success',
                'message'   => 'transaction updated'
            ];
        }
    }

    protected function lookupOrderId( $transaction_id) {
        global $wpdb;

        $query = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wc_getloy_payway_gateway_transaction_id' and meta_value = %s";
        $order_id = $wpdb->get_var( $wpdb->prepare($query, $transaction_id) );
        
        return $order_id;

    }

    /**
     * Generate transaction ID to be sent to GetLoy
     *
	 * @access public
     * @param int $order_id The WooCommerce order ID.
     * @return string The GetLoy transaction ID
     */
    protected function generateTransactionId( $order_id ) {

        $transaction_id = ($this->transaction_id_prefix ?: '')
            . $order_id
            . ($this->transaction_id_suffix ?: '');
       
        return $transaction_id;

    }

	/**
	 * Check if payment method configuration is complete and display warnings otherwise
	 *
     * @access public
     * @param bool $output_error Output HTML code for displaying error messages in the WordPress admin interface? Default false
	 * @return bool True if the configuration is valid
	 */
    public function check_config( $output_error = true) {
		if ( $this->enabled == 'no' ) {
			return true;
        }
        $config_error = false;

        if ( $output_error ) {
            echo '<div class="notice notice-error wc-getloy-payway-gateway"><p>' . sprintf(
                __( 'The PayWay for WooCommerce Gateway is no longer maintained. Please <a href="%s">install the GetLoy payment gateway for WooCommerce</a> instead.', 'wc-getloy-payway-gateway' ),
                admin_url( 'plugin-install.php?s=wc-getloy-gateway+wallet&tab=search&type=term' )
            ) . '</p></div>';
        }

        // PHP Version.
		if ( version_compare( phpversion(), '5.3', '<' ) ) {
			if ($output_error) {
                echo '<div class="notice notice-error wc-getloy-payway-gateway"><p>' . sprintf( __( 'PayWay gateway error: This plugin requires PHP 5.3 and above. You are using version %s.', 'wc-getloy-payway-gateway' ), phpversion() ) . '</p></div>';
            }
            return false;
        } 

        $plugin_admin_config_link = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=getloy_payway' );

        // Check required fields.
        if ( ! $this->merchant_id && $this->testmode == 'yes' && ! $this->test_api_key && 
            ! $this->production_api_key && ! $this->getloy_token ) {
            // no configuration done at all - display general message
			if ($output_error) {
                echo '<div class="notice notice-error wc-getloy-payway-gateway"><p>' . 
                    __( sprintf( 
                            'Please <a href="%s">click here</a> to configure the PayWay payment gateway in order to be able to use it.',
                            $plugin_admin_config_link
                        ),
                        'wc-getloy-payway-gateway' 
                    ) . '</p></div>';
            }
            $config_error = true;
        } else {
            if ( ! $this->merchant_id ) {
                if ($output_error) {
                    echo '<div class="notice notice-error wc-getloy-payway-gateway"><p>' . 
                        __( sprintf( 
                                'PayWay gateway configuration error: Please <a href="%s">click here</a> to enter your PayWay merchant ID.', 
                                $plugin_admin_config_link
                            ),
                            'wc-getloy-payway-gateway' 
                        ) . '</p></div>';
                }
                $config_error = true;
            }
    
            if ( $this->testmode == 'yes' && ! $this->test_api_key ) {
                if ($output_error) {
                    echo '<div class="notice notice-error wc-getloy-payway-gateway"><p>' . 
                        __( sprintf( 
                                'PayWay gateway configuration error: Please <a href="%s">click here</a> to enter your PayWay test API key.', 
                                $plugin_admin_config_link
                            ),
                            'wc-getloy-payway-gateway' 
                        ) . '</p></div>';
                }
                $config_error = true;
            }
    
            if ( $this->testmode != 'yes' && ! $this->production_api_key ) {
                if ($output_error) {
                    echo '<div class="notice notice-error wc-getloy-payway-gateway"><p>' . 
                        __( sprintf( 
                                'PayWay gateway configuration error: Please <a href="%s">click here</a> to enter your PayWay production API key.', 
                                $plugin_admin_config_link
                            ),
                            'wc-getloy-payway-gateway' 
                        ) . '</p></div>';
                }
                $config_error = true;
            }
    
            if ( ! $this->getloy_token ) {
                if ($output_error) {
                    echo '<div class="notice notice-error wc-getloy-payway-gateway"><p>' . 
                        __( sprintf( 
                                'PayWay gateway configuration error: Please <a href="%s">click here</a> to enter your GetLoy account token. ' . 
                                'If you don\'t have a GetLoy account yet, please <a href="%s" target="_blank">click here to sign up</a>.', 
                                $plugin_admin_config_link,
                                'https://getloy.com/signup'
                            ),
                            'wc-getloy-payway-gateway' 
                        ) . '</p></div>';
                }
                $config_error = true;
            }
    
            // warn about test payments
            if ( $this->testmode == 'yes' ) {
                if ($output_error) {
                    echo '<div class="notice notice-info wc-getloy-payway-gateway"><p>' . 
                        __( 
                            "PayWay payment gateway is in test mode, no real payments are processed!", 
                            'wc-getloy-payway-gateway' 
                        ) . '</p></div>';
                }
            }
        }

        // warn about non-SSL page
		if ( ( get_option( 'woocommerce_force_ssl_checkout' ) === 'no' ) && ! class_exists( 'WordPressHTTPS' ) ) {
            if ($output_error) {
                echo '<div class="notice notice-warning wc-getloy-payway-gateway"><p>' . 
                    sprintf( __( 'You are not enforcing HTTPS for checkout. ' . 
                                'While the PayWay payment gateway remains secure, users may feel insecure due to the missing confirmation in the browser address bar. ' . 
                                'Please <a href="%s">enforce SSL</a> and ensure your server has a valid SSL certificate!', 
                            'wc-getloy-payway-gateway' 
                        ), 
                        admin_url( 'admin.php?page=wc-settings&tab=checkout' ) 
                    ) . '</p></div>';
            }
        }

        return !$config_error;

    }

    /**
     * Filter list of gateways available for checkout - hide GetLoy gateway if 
     * the configuration is incomplete, and show a warning
	 *
     * @since    1.0.0
     * @param array $available_gateways List of gateways to show as options during checkout
     * @return array                    List of gateways
	 * @access   public
	 */
	 public function filter_gateways( array $available_gateways ) {
        
        if (! isset($available_gateways[$this->id]) || $this->check_config(false) ) {
            return $available_gateways;
        }

        unset($available_gateways[$this->id]);
        $this->add_notice(__(
            'The PayWay payment gateway is not configured properly. Please ask the site administrator to complete the configuration.', 
            'wc-getloy-payway-gateway'
            ), 
            'error'
        );

        return $available_gateways;

    }
    
    /**
     * Add a notice to be displayed to the user. Skip duplicate notices.
	 *
     * @since    1.0.0
	 * @access   public
     * @param string    $message    Notice message
     * @param string    $type       Notice type
     * @return void
	 */
    protected function add_notice( $message, $type ) {
        foreach (wc_get_notices($type) as $notice) {
            if ($notice === $message) {
                return;
            }
        }
        wc_add_notice($message, $type);

    }

 }
