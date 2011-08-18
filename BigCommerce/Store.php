<?php
require_once dirname(__FILE__).'/Api2/Connection.php';
require_once dirname(__FILE__).'/Api2/Resources.php';

class BigCommerce_Store
{

	static private $api_path = '/api/v2';
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

	public static function getCollection($path, $resource='Resource')
	{
		$response = self::connection()->get(self::$api_path . $path);

		return self::mapCollection($resource, $response);
	}

	public static function getResource($path, $resource='Resource')
	{
		$response = self::connection()->get(self::$api_path . $path);

		return self::mapResource($resource, $response);
	}

	public static function getCount($path)
	{
		$response = self::connection()->get(self::$api_path . $path);

		if ($response == false || is_string($response)) return $response;

		return $response->count;
	}

	public static function createResource($path, $object)
	{
		if (is_array($object)) $object = (object)$object;

		return self::connection()->post(self::$api_path . $path, $object);
	}

	public static function updateResource($path, $object)
	{
		if (is_array($object)) $object = (object)$object;

		return self::connection()->put(self::$api_path . $path, $object);
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

	public static function getTime()
	{
		$response = self::connection()->get(self::$api_path . '/time');

		if ($response == false || is_string($response)) return $response;

		return new DateTime("@{$response->time}");
	}

	public static function getProducts()
	{
		return self::getCollection('/products', 'Product');
	}

	public static function getProductsCount()
	{
		return self::getCount('/products/count');
	}

	public static function getProduct($id)
	{
		return self::getResource('/products/' . $id, 'Product');
	}

	public static function updateProduct($id, $object)
	{
		return self::updateResource('/products/' . $id, $object);
	}

	public static function getCategories()
	{
		return self::getCollection('/categories', 'Category');
	}

	public static function getCategoriesCount()
	{
		return self::getCount('/categories/count');
	}

	public static function getCategory($id)
	{
		return self::getResource('/categories/' . $id, 'Category');
	}

	public static function createCategory($object)
	{
		return self::createResource('/categories', $object);
	}

	public static function updateCategory($id, $object)
	{
		return self::updateResource('/categories/' . $id, $object);
	}

	public static function getBrands()
	{
		return self::getCollection('/brands', 'Brand');
	}

	public static function getBrandsCount()
	{
		return self::getCount('/brands/count');
	}

	public static function getBrand($id)
	{
		return self::getResource('/brands/' . $id, 'Brand');
	}

	public static function createBrand($object)
	{
		return self::createResource('/brands', $object);
	}

	public static function updateBrand($id, $object)
	{
		return self::updateResource('/brands/' . $id, $object);
	}

	public static function getOrders()
	{
		return self::getCollection('/orders', 'Order');
	}

	public static function getOrdersCount()
	{
		return self::getCount('/orders/count');
	}

	public static function getOrder($id)
	{
		return self::getResource('/orders/' . $id, 'Order');
	}

	public static function getCustomers()
	{
		return self::getCollection('/customers', 'Customer');
	}

	public static function getCustomersCount()
	{
		return self::getCount('/customers/count');
	}

	public static function getCustomer($id)
	{
		return self::getResource('/customers/' . $id, 'Customer');
	}

	public static function getOptionSets()
	{
		return self::getCollection('/optionsets', 'OptionSet');
	}

	public static function getOptionSetsCount()
	{
		return self::getCount('/optionsets/count');
	}

	public static function getOptionSet($id)
	{
		return self::getResource('/optionsets/' . $id, 'OptionSet');
	}

	public static function getOrderStatuses()
	{
		return self::getCollection('/orderstatuses', 'OrderStatus');
	}
}