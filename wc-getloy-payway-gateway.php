<?php

/**
 * Payment gateway for WooCommerce to accept payments via ABA Bank's PayWay (powered by GetLoy)
 *
 * @link       	      https://getloy.com
 * @since             1.0.0
 * @package           Wc_Getloy_Payway_Gateway
 *
 * @wordpress-plugin
 * Plugin Name:       PayWay for WooCommerce (powered by GetLoy)
 * Plugin URI:        https://getloy.com/wc-getloy-payway
 * Description:       Accept international credit card payments to your Cambodian bank account through GetLoy using PayWay by ABA Bank.
 * Version:           1.0.7
 * Author:            Geekho (Cambodia)
 * Author URI:        https://geekho.asia
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wc-getloy-payway-gateway
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Make sure WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}

/**
 * The core plugin class that is used to define internationalization,
 * and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wc-getloy-payway-gateway.php';

/**
 * Begin execution of the plugin.
 *
 * @since    1.0.0
 */
if (! function_exists('run_wc_getloy_payway_gateway')) {
	function run_wc_getloy_payway_gateway() {
		
			$plugin = Wc_Getloy_Payway_Gateway::instance();
			$plugin->run();
		
		}
}
run_wc_getloy_payway_gateway();
