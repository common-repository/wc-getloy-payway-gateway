<?php

require_once 'class-wc-getloy-payway-gateway-getloy-transaction-item.php';

/**
 * Defines the connector for the Getloy API
 *
 * @link       https://geekho.asia
 * @since      1.0.0
 *
 * @package    Wc_Getloy_Payway_Gateway
 * @subpackage Wc_Getloy_Payway_Gateway/gateway/includes
 */

 /**
 * The Getloy API connector class.
 *
 * @since      1.0.0
 * @package    Wc_Getloy
 * @subpackage Wc_Getloy_Payway_Gateway/gateway/includes
 * @author     Jan Hagelauer, Geekho (Cambodia)
 */
 class Wc_Getloy_Payway_Gateway_Getloy_Connector {

	/**
	 * List of allowed transaction status values.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array 	$transactionStatusList	List of transaction status values.
	 */
	 const TRANSACTION_STATUS_LIST = [
		'successful',
		'timed_out',
		'failed'
	 ];

	 /**
	 * GetLoy account token.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string 	$getloyToken	Getloy account token.
	 */
 	protected $getloyToken;

	 /**
	 * PayWay merchant ID.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string 	$merchantId	PayWay merchant ID.
	 */
 	protected $merchantId;

	/**
	 * PayWay merchant key.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $merchantKey	The PayWay merchant key used for generating hashes.
	 */
 	protected $merchantKey;

	/**
	 * URL to call upon successful completion of the transaction.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $merchantKey	The URL to call upon successful completion of the transaction.
	 */
 	protected $callbackUrl;

	/**
	 * Activate test mode.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      bool    $testMode	Activate test mode.
	 */
 	protected $testMode;
	
	/**
	 * String identifying the type and version of the requestor (e.g. "wc-getloy-payway-gateway v1.0.0").
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      bool    $requestOrigin	Identifier for the type and version of the requestor.
	 */
	protected $requestOrigin;

	 /**
 	 * Create new PayWay transaction.
 	 *
 	 * @param string	$getloy_token 	GetLoy account token
 	 * @param string	$merchant_id 	PayWay merchant ID
 	 * @param string	$merchant_id 	PayWay merchant key
	 * @param string	$request_origin	String identifying the type and version of the requestor
 	 * @param bool		$test_mode 		Activate test mode (optional, default false)
	 *
 	 * @return array List of parameters to be passed to PayWay to initialize the new transaction
 	 */
	public function __construct($getloy_token, $merchant_id, $merchant_key, 
	 	$request_origin, $test_mode = false) {
		
		$this->getloyToken		= $getloy_token;
		$this->merchantId		= $merchant_id;
 		$this->merchantKey		= $merchant_key;
		$this->testMode 		= $test_mode;
		$this->requestOrigin	= $request_origin;

 	}

 	/**
 	 * Generate HMAC SHA512 hash value of the provided string.
 	 *
 	 * @param string $string String value to generate the hash for.
 	 *
 	 * @return string Hash value
 	 */
	protected function hash($data, $key) {
 		$hash = hash_hmac('sha512', $data, $key);
 		return $hash;
 	}

 	/**
 	 * Validate the parameters for a new PayWay transaction. Throws an exception if the parameters are invalid.
 	 *
 	 * @param string	$transactionId 	Unique identifier string for the transaction.
 	 * @param float		$amount 		Amount to be transferred.
	 * @param string	$currency 		Transaction currency (3 character ISO 4217 currency code).
	 * @param string	$callback_url 	URL to call upon successful completion of the transaction
 	 * @param array		$items 			Array of items ordered (optional).
 	 * @param string	$firstName 		Payer's first name (optional).
 	 * @param string	$lastName		Payer's last name (optional).
 	 * @param string	$phone 			Payer's phone number (optional).
 	 * @param string	$email 			Payer's email address (optional).
	 *
 	 * @return bool True if parameters are valid.
 	 */
	 public function validateCreateTransactionParams($transactionId, $amount, $currency, $callback_url, 
	 	$items, $firstName, $lastName, $phone, $email) {

 	 	return $this->validateTransactionId($transactionId)
		  && $this->validateAmount($amount)
		  && $this->validateCurrency($currency);
		}

 	/**
 	 * Validate the parameters for checking the status of a PayWay transaction. Throws an exception if the parameters are invalid.
 	 *
 	 * @param string 	$transactionId 	Unique identifier string for the transaction.
 	 *
 	 * @return bool True if parameters are valid.
 	 */
	protected function validateCheckTransactionParams($transactionId) {
 		return $this->validateTransactionId($transactionId);
 	}

 	/**
 	 * Validate transaction ID. Throws an exception if the value is invalid.
 	 *
 	 * @param string	$transactionId 	Unique identifier string for the transaction.
 	 *
 	 * @return bool True if the value is valid.
 	 */
	protected function validateTransactionId($transactionId) {
 	 	if (count($transactionId) >= 20) {
 			throw new Exception(__('Transaction ID must be less than 20 characters long.', 'wc-getloy-payway-gateway'));
 	 	}
 	 	return true;
 	}

 	/**
 	 * Validate transaction amount. Throws an exception if the value is invalid.
 	 *
 	 * @param float $amount 		Amount to be transferred.
 	 *
 	 * @return bool True if the value is valid.
 	 */
	protected function validateAmount($amount) {
		if ($amount <= 0) {
		   throw new Exception(__('Transaction amount must be greater than 0.', 'wc-getloy-payway-gateway'));
		}
		return true;
    }

 	/**
 	 * Validate transaction currency. Throws an exception if the value is invalid.
 	 * Validate transaction currency. Throws an exception if the value is invalid.
 	 *
 	 * @param string $currency 		Transaction currency.
 	 *
 	 * @return bool True if the currency is valid.
 	 */
	protected function validateCurrency($currency) {
		if ($currency !== 'USD') {
		   throw new Exception(__('PayWay only supports transactions in US Dollars.', 'wc-getloy-payway-gateway'));
		}
		return true;
    }

 	/**
 	 * Transform an array of GetLoy transactions to an array.
 	 *
 	 * @param Wc_Getloy_Payway_Gateway_Getloy_Transaction_Item[]	$items 	Array of ordered items.
 	 *
 	 * @return array Transaction list as array
 	 */
 	protected function itemsToArray($items) {
		$itemArray = [];

 	 	foreach ($items as $item) {
 	 		if (is_object($item) && get_class($item) != 'Wc_Getloy_Payway_Gateway_Getloy_Transaction_Item') {
 	 			throw new Exception(sprintf(__('Invalid transaction item: Object of type Wc_Getloy_Payway_Gateway_Getloy_Transaction_Item expected, but got %s.', 'wc-getloy-payway-gateway'),
 	 				is_object($item) ? get_class($item) : gettype($item)
 				));
 	 		}
 	 		$itemArray[] = $item->getProperties();
 	 	}

 	 	return $itemArray;
	 }
	 
 	/**
 	 * Transform an array of GetLoy transactions to a base64 encoded string.
 	 *
 	 * @param Wc_Getloy_Payway_Gateway_Getloy_Transaction_Item[]	$items 	Array of ordered items.
 	 *
 	 * @return array Transaction list as array
 	 */
	protected function itemsToBase64String($items) {
		$itemArray = [];
		
		foreach ($items as $item) {
			if (is_object($item) && get_class($item) != 'Wc_Getloy_Payway_Gateway_Getloy_Transaction_Item') {
				throw new Exception(sprintf(__('Invalid transaction item: Object of type Wc_Getloy_Payway_Gateway_Getloy_Transaction_Item expected, but got %s.', 'wc-getloy-payway-gateway'),
					is_object($item) ? get_class($item) : gettype($item)
				));
			}
			$properties = $item->getProperties();
			$itemArray[] = [
				'name' => $properties['description'],
				'quantity' => $properties['quantity'],
				'price' => $properties['unit_price']
			];
		}

		return base64_encode(mb_convert_encoding(json_encode($itemArray), 'UTF-8'));
	}

 	/**
 	 * Create new GetLoy transaction.
 	 *
 	 * @param string	$transactionId 	Unique identifier string for the transaction.
	 * @param float		$amount 		Amount to be transferred.
	 * @param string	$currency 		Transaction currency (3 character ISO 4217 currency code).
	 * @param string	$callback_url 	URL to call upon successful completion of the transaction
 	 * @param array		$items 			Array of items ordered (optional).
 	 * @param string	$firstName 		Payer's first name (optional).
 	 * @param string	$lastName		Payer's last name (optional).
 	 * @param string	$phone 			Payer's phone number (optional).
 	 * @param string	$email 			Payer's email address (optional).
	 *
 	 * @return array List of parameters to be passed to PayWay to initialize the new transaction
 	 */
	 public function generateCreateTransactionParams($transactionId, $amount, $currency, $callback_url,
	 	array $items = [], $firstName = '', $lastName = '', $phone = '', $email = '') {

 	 	$this->validateCreateTransactionParams($transactionId, $amount, $currency, $callback_url, $items,
 	 		$firstName, $lastName, $phone, $email);

 	 	$amount = (float) $amount;

		$itemsArray = $this->itemsToArray($items);
		$itemsEncoded = $this->itemsToBase64String($items);

 	 	$initHash 			= $this->hash($this->merchantId . $transactionId . $amount . $itemsEncoded, $this->merchantKey);
		$statusHash 		= $this->hash($this->merchantId . $transactionId, $this->merchantKey);
		$getloyMerchantHash	= $this->hash($this->getloyToken, $this->getloyToken);
		$getloyAuthHash 	= $this->hash($this->getloyToken . $transactionId . $amount, $this->getloyToken); 
		
 	 	$params = [
			'tid' 				=> $transactionId,
			'merchant_hash'		=> $getloyMerchantHash,
			'auth_hash' 		=> $getloyAuthHash,
			'callback' 			=> $callback_url,
			'test_mode' 		=> $this->testMode,
			'request_origin'	=> $this->requestOrigin,
			'payee' => [
				'first_name' 	=> $firstName,
				'last_name' 	=> $lastName,
				'email_address' => $email,
				'mobile_number'	=> $phone,
			],
			'order' => [
				'total_amount' 	=> (float) $amount,
				'currency'		=> $currency,
				'order_items'	=> $itemsArray,
			],
			'payment_provider' => [
				'init_token'	=> $initHash,
				'status_token'	=> $statusHash
			]
		];
		  
 	 	$params = array_filter($params, function ($v) {
			  return !empty($v);
			}
		);

	    return $params;
	}
	 
 	/**
 	 * Validate authentication hash from GetLoy.
 	 *
 	 * @param string	$transactionId 	Unique identifier string for the transaction.
 	 * @param string	$status 		Transaction status.
 	 * @param string	$auth_hash 		Authentication hash for the transaction
	 *
 	 * @return bool Validation result
 	 */
	public function validate_callback_hash($transactionId, $status, $auth_hash) {
		if (! in_array($status, self::TRANSACTION_STATUS_LIST)) {
			return false;
		}

		$hash = $this->hash($this->getloyToken . $transactionId . $status, $this->getloyToken);
		return $hash == $auth_hash;
	}
}
