<?php

/**
 * Defines the data structure for storing transaction items
 *
 * @link       https://geekho.asia
 * @since      1.0.0
 *
 * @package    Wc_Getloy_Payway_Gateway
 * @subpackage Wc_Getloy_Payway_Gateway/gateway/includes
 */

 /**
 * The class for encapsulating Getloy transaction items
 *
 * @since      1.0.0
 * @package    Wc_Getloy
 * @subpackage Wc_Getloy_Payway_Gateway/gateway/includes
 * @author     Jan Hagelauer, Geekho (Cambodia)
 */
 class Wc_Getloy_Payway_Gateway_Getloy_Transaction_Item {
	/**
	 * Item properties.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array 	$properties	Associative array containing the item properties.
	 */
 	protected $properties = [];

 	public function __construct( $name, $quantity, $price) {
 		$this->properties['description'] 	= (string) $name;
 		$this->properties['quantity']		= (int) $quantity;
    	$this->properties['unit_price']		= (float) $price;
    	$this->properties['total_price']	= (float) $price*$quantity;
 	}

 	/**
 	 * Return the item properties as an associative array.
 	 *
 	 * @return array The item properties.
 	 */
 	public function getProperties() {
 		return $this->properties;
 	}
 }
