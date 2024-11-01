<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://geekho.asia
 * @since      1.0.0
 *
 * @package    Wc_Getloy_Payway_Gateway
 * @subpackage Wc_Getloy_Payway_Gateway/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Wc_Getloy_Payway_Gateway
 * @subpackage Wc_Getloy_Payway_Gateway/includes
 * @author     Geekho (Cambodia) <payment@geekho.asia>
 */
class Wc_Getloy_Payway_Gateway_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'wc-getloy-payway-gateway',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}


}
