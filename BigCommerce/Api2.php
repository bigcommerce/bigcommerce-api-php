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
	static private $resource;

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

	private static function makeResource($resource, $object)
	{
		$class = 'BigCommerce_Api2_' . $resource;

		return new $class($object);
	}

	private static function mapCollection($resource, $object)
	{
		if ($object == false || is_string($object)) return $object;

		self::$resource = $resource;

		return array_map(array('self', 'mapCollectionObject'), $object);
	}

	private static function mapCollectionObject($object)
	{
		return self::makeResource(self::$resource, $object);
	}

	private static function mapResource($resource, $object)
	{
		if ($object == false || is_string($object)) return $object;

		return self::makeResource($object, $resource);
	}

	private static function mapCount($object) {
		if ($object == false || is_string($object)) return $object;

		return $object->count;
	}

	public static function ping()
	{
		$response = self::connection()->get(self::$api_path . '/time');

		if ($response == false || is_string($response)) return $response;

		return new DateTime("@{$response->time}");
	}

	public static function getProducts()
	{
		$response = self::connection()->get(self::$api_path . '/products');

		return self::mapCollection('Product', $response);
	}

	public static function getProductsCount()
	{
		$response = self::connection()->get(self::$api_path . '/products/count');

		return self::mapCount($response);
	}

	public static function getProduct()
	{
		$response = self::connection()->get(self::$api_path . '/time');

		return self::mapResource('Product', $response);
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