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

	private static function mapCollection($resource, $object)
	{
		if ($object == false || is_string($object)) return $object;

		self::$resource = $resource;

		return array_map(array('self', 'mapCollectionObject'), $object);
	}

	private static function mapCollectionObject($object)
	{
		$class = 'BigCommerce_Api2_' . self::$resource;

		return new $class($object);
	}

	private static function mapResource($resource, $object)
	{
		if ($object == false || is_string($object)) return $object;

		$class = 'BigCommerce_Api2_' . $resource;

		return new $class($object);
	}

	private static function mapCount($object)
	{
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

	public static function getProduct($id)
	{
		$response = self::connection()->get(self::$api_path . '/products/' . $id);

		return self::mapResource('Product', $response);
	}

	public static function updateProduct()
	{

	}

	public static function getCategories()
	{
		$response = self::connection()->get(self::$api_path . '/categories');

		return self::mapCollection('Category', $response);
	}

	public static function getCategoriesCount()
	{
		$response = self::connection()->get(self::$api_path . '/categories/count');

		return self::mapCount($response);
	}

	public static function getCategory($id)
	{
		$response = self::connection()->get(self::$api_path . '/categories/' . $id);

		return self::mapResource('Category', $response);
	}

	public static function getBrands()
	{
		$response = self::connection()->get(self::$api_path . '/brands');

		return self::mapCollection('Brand', $response);
	}

	public static function getBrandsCount()
	{
		$response = self::connection()->get(self::$api_path . '/brands/count');

		return self::mapCount($response);
	}

	public static function getBrand($id)
	{
		$response = self::connection()->get(self::$api_path . '/brands/' . $id);

		return self::mapResource('Brand', $response);
	}

	public static function createBrand($object)
	{
		if (is_array($object)) $object = (object)$object;

		return self::connection()->post(self::$api_path . '/brands', $object);
	}

	public static function updateBrand($id, $object)
	{
		if (is_array($object)) $object = (object)$object;

		return self::connection()->put(self::$api_path . '/brands/' . $id, $object);
	}

	public static function getOrders()
	{
		$response = self::connection()->get(self::$api_path . '/orders');

		return self::mapCollection('Order', $response);
	}

	public static function getOrdersCount()
	{
		$response = self::connection()->get(self::$api_path . '/orders/count');

		return self::mapCount($response);
	}

	public static function getOrder($id)
	{
		$response = self::connection()->get(self::$api_path . '/orders/' . $id);

		return self::mapResource('Order', $response);
	}

	public static function getCustomers()
	{
		$response = self::connection()->get(self::$api_path . '/customers');

		return self::mapCollection('Customer', $response);
	}

	public static function getCustomersCount()
	{
		$response = self::connection()->get(self::$api_path . '/customers/count');

		return self::mapCount($response);
	}

	public static function getCustomer($id)
	{
		$response = self::connection()->get(self::$api_path . '/customers/' . $id);

		return self::mapResource('Customer', $response);
	}

	public static function getOptionSets()
	{
		$response = self::connection()->get(self::$api_path . '/optionsets');

		return self::mapCollection('OptionSet', $response);
	}

	public static function getOptionSetsCount()
	{
		$response = self::connection()->get(self::$api_path . '/optionsets/count');

		return self::mapCount($response);
	}

	public static function getOptionSet($id)
	{
		$response = self::connection()->get(self::$api_path . '/optionsets/' . $id);

		return self::mapResource('OptionSet', $response);
	}
}