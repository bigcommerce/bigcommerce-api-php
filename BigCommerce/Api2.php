<?php
require_once dirname(__FILE__).'/Api2/Connection.php';
require_once dirname(__FILE__).'/Api2/Resources.php';

class BigCommerce_Api2
{

	private $api_path = '/api/v1';
	private $store_url;
	private $username;
	private $api_key
	
	public static function configure($store_url, $username, $api_key)
	{
		self::$store_url = ($store_url[0] == '/') ? substr($store_url, 1) : $store_url;
		self::$username  = $username;
		self::$api_key = $api_key;
	}
	
	public static function failOnError($option=true)
	{
		self::getConnection()->failOnError($option);
	}
	
	public static function useXml()
	{
		self::getConnection()->useXml();
	}
	
	private static function getConnection()
	{
		if (!self::$connection) {
		 	self::$connection = new BigCommerce_Api2_Connection();
			self::$connection->authenticate(self::$username, self::$api_key);
		}
		return self::$connection;
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