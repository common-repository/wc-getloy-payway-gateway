<?php

/**
 * Definition of the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site.
 *
 * @link       https://geekho.asia
 * @since      1.0.0
 *
 * @package    Wc_Getloy_Payway_Gateway
 * @subpackage Wc_Getloy_Payway_Gateway/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization and public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wc_Getloy_Payway_Gateway
 * @subpackage Wc_Getloy_Payway_Gateway/includes
 * @author     Geekho (Cambodia) <payment@geekho.asia>
 */
class Wc_Getloy_Payway_Gateway {

	/**
	 * Single instance of the plugin class (to implement the singleton pattern).
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var Wc_Getloy_Payway_Gateway $instance Single instance of the plugin class
	 */
	protected static $instance;

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wc_Getloy_Payway_Gateway_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Instance of the payment gateway class.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wc_Getloy_Payway_Gateway_Payment_Gateway    $gateway_instance    The instance of the gateway.
	 */
	 protected $gateway_instance;

	 /**
	 * Return single instance of this class
	 *
	 * @since    1.0.0
	 * @access   private
	 * @return	 Wc_Getloy_Payway_Gateway $instance Single instance of the plugin class
	 */
	public static function instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;

	}

	 /**
	 * Define the core functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function __construct() {

		$this->get_plugin_name();
		$this->version = '1.0.7';

		$this->load_dependencies();
		$this->set_locale();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wc-getloy-payway-gateway-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wc-getloy-payway-gateway-i18n.php';

		/**
		 * The class responsible for defining all functions for communicating with GetLoy.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'gateway/includes/class-wc-getloy-payway-gateway-getloy-connector.php';
		 
		$this->loader = new Wc_Getloy_Payway_Gateway_Loader();

		/**
		 * The class responsible for defining all actions of the WooCommerce payment 
		 * gateway backend.
		 */
		$this->loader->add_action( 'plugins_loaded', $this, 'init_gateway' );

		$this->loader->add_filter( 'woocommerce_payment_gateways', $this, 'add_gateway' );
		
		$this->loader->add_action( 'woocommerce_receipt_getloy_payway', $this, 'gateway_receipt_page');

		$this->loader->add_filter('woocommerce_available_payment_gateways', $this, 'filter_gateways');

		$this->loader->add_action( 'rest_api_init', $this, 'register_rest_routes' );

		if ( is_admin() ) {
			$this->loader->add_filter( 'plugin_action_links_' . $this->plugin_name . '/wc-getloy-payway-gateway.php', $this, 'plugin_add_action_link' );

			$this->loader->add_action( 'woocommerce_update_options_payment_gateways_getloy_payway', $this, 'gateway_process_admin_options' );

			$this->loader->add_action( 'admin_notices', $this, 'gateway_check_config' );
		}

	}

	/**
	 * Load the class responsible for defining all actions of the WooCommerce payment 
	 * gateway backend.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	 public function init_gateway() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'gateway/class-wc-getloy-payway-gateway-payment-gateway.php';
		
		$this->gateway_instance = new Wc_Getloy_Payway_Gateway_Payment_Gateway();

	}

	/**
	 * Register the payment gateway with WooCommerce
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function add_gateway( $gateways ) {

		$gateways[] = 'Wc_Getloy_Payway_Gateway_Payment_Gateway';
		return $gateways;

	}

	/**
	 * Process admin options for gateway
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function gateway_process_admin_options() {
		
		$this->gateway_instance->process_admin_options();
	
	}
	
	/**
	 * Receipt page hook for gateway
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function gateway_receipt_page( $order_id ) {
		
		$this->gateway_instance->receipt_page( $order_id );
		add_action( 'wp_print_footer_scripts', [$this->gateway_instance, 'receipt_page_footer'] );

	}
		
	/**
	 * Thankyou page hook for gateway
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	// public function gateway_thankyou_page( $order_id ) {
		
	// 	$this->gateway_instance->thankyou_page( $order_id );

	// }
		
	/**
	 * Configuration check hook for gateway
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	 public function gateway_check_config() {
		
		$this->gateway_instance->check_config();

	}
		
	/**
	 * Filter list of gateways available for checkout
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	 public function filter_gateways( array $available_gateways ) {
		
		return $this->gateway_instance->filter_gateways( $available_gateways );

	}

	/**
	 * Register API endpoint for GetLoy callback
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function register_rest_routes() {
		register_rest_route( 'wc-getloy-payway-gateway/v1', '/payments/(?P<tid>[^/?&\s]+)/status', array(
			'methods' => 'POST',
			'callback' => [$this, 'handle_status_update_callback'],
		) );
	}

	/**
	 * Payment status update callback hook for gateway
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param WP_REST_Request	$request	The request sent to the REST endpoint
	 *
	 * @return WP_REST_Response The response
	 */
	 public function handle_status_update_callback( WP_REST_Request $request ) {
		
		return $this->gateway_instance->status_update_callback( $request );
	
	}
		
	/**
	 * Payment status check callback hook for gateway
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param WP_REST_Request	$request	The request sent to the REST endpoint
	 *
	 * @return WP_REST_Response The response
	 */
	 public function handle_status_check_callback( WP_REST_Request $request ) {
		
		return $this->gateway_instance->status_check_callback( $request );
	
	}

	/**
	 * Plugin action link (link to configuration page)
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function plugin_add_action_link( $links ) {

		$plugin_links = [
			'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=getloy_payway' ) . '">' . __( 'Settings', 'wc-getloy-payway-gateway' ) . '</a>'
		];
		return array_merge( $plugin_links, $links );

	}
				
	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wc_Getloy_Payway_Gateway_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wc_Getloy_Payway_Gateway_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		if (! isset($this->plugin_name)) {
			// extract the name of the second parent folder from the current script's path
			if (preg_match('/^.+[\\\\\/]([^\\\\\/]+)(?:[\\\\\/][^\\\\\/]+)[\\\\\/]?$/', dirname(__FILE__), $match)) {
				$this->plugin_name = $match[1];
			} else {
				// malformed path
				$this->plugin_name = 'wc-getloy-payway-gateway';
			}
		}

		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Wc_Getloy_Payway_Gateway_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
