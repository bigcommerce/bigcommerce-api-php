<?php
require_once dirname(__FILE__).'/Api2/Connection.php';
require_once dirname(__FILE__).'/Api2/Resources.php';

class BigCommerce_Api2
{

	static private $api_path = '/api/v1';
	static private $store_url;
	static private $username;
	static private $api_key;
	static private $connection;

	public static function configure($store_url, $username, $api_key)
	{
		self::$username  = $username;
		self::$api_key = $api_key;
		self::$store_url = ($store_url[0] == '/') ? substr($store_url, 1) : $store_url;
		self::$api_path = self::$store_url . self::$api_path;
	}

	public static function failOnError($option=true)
	{
		self::connection()->failOnError($option);
	}

	public static function useXml()
	{
		self::connection()->useXml();
	}

	public static function verifyPeer($option=false)
	{
		self::connection()->verifyPeer($option);
	}

	public static function useProxy($server, $port=false)
	{
		self::connection()->useProxy($server, $port);
	}

	public static function getLastError()
	{
		return self::connection()->getLastError();
	}

	private static function connection()
	{
		if (!self::$connection) {
		 	self::$connection = new BigCommerce_Api2_Connection();
			self::$connection->authenticate(self::$username, self::$api_key);
		}
		return self::$connection;
	}

	public static function ping()
	{
		$timestamp = self::connection()->get(self::$api_path . '/ping');
		return $timestamp;
	}

	public static function getProducts()
	{

	}

	public static function getProductsCount()
	{

	}

	public static function getProduct()
	{

	}

	public static function updateProduct()
	{

	}

	public static function getCategories()
	{

	}

	public static function getCategoriesCount()
	{

	}

	public static function getCategory()
	{

	}

	public static function getBrands()
	{

	}

	public static function getBrandsCount()
	{

	}

}