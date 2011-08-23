<?php
require_once dirname(__FILE__).'/Api/Connection.php';
require_once dirname(__FILE__).'/Api/Resources.php';

/**
 * BigCommerce API wrapper.
 */
class BigCommerce_Api
{

	static private $api_path = '/api/v2';
	static private $store_url;
	static private $username;
	static private $api_key;
	static private $connection;
	static private $resource;

	/**
	 * Configure the API client with the required credentials.
	 */
	public static function configure($store_url, $username, $api_key)
	{
		self::$username  = $username;
		self::$api_key = $api_key;
		self::$store_url = rtrim($store_url, '/');
		self::$api_path = self::$store_url . self::$api_path;
	}

	/**
	 * Configure the API client to throw exceptions when HTTP errors occur.
	 *
	 * Note that network faults will always cause an exception to be thrown.
	 */
	public static function failOnError($option=true)
	{
		self::connection()->failOnError($option);
	}

	/**
	 * Return XML strings from the API instead of building objects.
	 */
	public static function useXml()
	{
		self::connection()->useXml();
	}

	/**
	 * Switch SSL certificate verification on requests.
	 */
	public static function verifyPeer($option=false)
	{
		self::connection()->verifyPeer($option);
	}

	/**
	 * Connect to the internet through a proxy server.
	 *
	 * @param string $host host server
	 * @param string $port port
	 */
	public static function useProxy($host, $port=false)
	{
		self::connection()->useProxy($host, $port);
	}

	/**
	 * Get error message returned from the last API request if
	 * failOnError is false (default).
	 *
	 * @return string
	 */
	public static function getLastError()
	{
		return self::connection()->getLastError();
	}

	/**
	 * Get an instance of the HTTP connection object. Initializes
	 * the connection if it is not already active.
	 *
	 * @return BigCommerce_Api_Connection
	 */
	private static function connection()
	{
		if (!self::$connection) {
		 	self::$connection = new BigCommerce_Api_Connection();
			self::$connection->authenticate(self::$username, self::$api_key);
		}

		return self::$connection;
	}

	/**
	 * Get a collection result from the specified endpoint.
	 *
	 * @param string $path api endpoint
	 * @param string $resource resource class to map individual items
	 * @param array $fields additional key=>value properties to apply to the object
	 * @return mixed array|string mapped collection or XML string if useXml is true
	 */
	public static function getCollection($path, $resource='Resource')
	{
		$response = self::connection()->get(self::$api_path . $path);

		return self::mapCollection($resource, $response);
	}

	/**
	 * Get a resource entity from the specified endpoint.
	 *
	 * @param string $path api endpoint
	 * @param string $resource resource class to map individual items
	 * @return mixed BigCommerce_ApiResource|string resource object or XML string if useXml is true
	 */
	public static function getResource($path, $resource='Resource')
	{
		$response = self::connection()->get(self::$api_path . $path);

		return self::mapResource($resource, $response);
	}

	/**
	 * Get a count value from the specified endpoint.
	 *
	 * @param string $path api endpoint
	 * @return mixed int|string count value or XML string if useXml is true
	 */
	public static function getCount($path)
	{
		$response = self::connection()->get(self::$api_path . $path);

		if ($response == false || is_string($response)) return $response;

		return $response->count;
	}

	/**
	 * Send a post request to create a resource on the specified collection.
	 *
	 * @param string $path api endpoint
	 * @param mixed $object object or XML string to create
	 */
	public static function createResource($path, $object)
	{
		if (is_array($object)) $object = (object)$object;

		return self::connection()->post(self::$api_path . $path, $object);
	}

	/**
	 * Send a put request to update the specified resource.
	 *
	 * @param string $path api endpoint
	 * @param mixed $object object or XML string to update
	 */
	public static function updateResource($path, $object)
	{
		if (is_array($object)) $object = (object)$object;

		return self::connection()->put(self::$api_path . $path, $object);
	}

	/**
	 * Internal method to wrap items in a collection to resource classes.
	 *
	 * @param string $resource name of the resource class
	 * @param array $object object collection
	 * @return array
	 */
	private static function mapCollection($resource, $object)
	{
		if ($object == false || is_string($object)) return $object;

		self::$resource = $resource;

		return array_map(array('self', 'mapCollectionObject'), $object);
	}

	/**
	 * Callback for mapping collection objects resource classes.
	 *
	 * @param stdClass $object
	 * @return BigCommerce_Api_Resource
	 */
	private static function mapCollectionObject($object)
	{
		$class = 'BigCommerce_Api_' . self::$resource;

		return new $class($object);
	}

	/**
	 * Map a single object to a resource class.
	 *
	 * @param string $resource name of the resource class
	 * @param stdClass $object
	 * @return BigCommerce_Api_Resource
	 */
	private static function mapResource($resource, $object)
	{
		if ($object == false || is_string($object)) return $object;

		$class = 'BigCommerce_Api_' . $resource;

		return new $class($object);
	}

	/**
	 * Map object representing a count to an integer value.
	 *
	 * @param stdClass $object
	 * @return int
	 */
	private static function mapCount($object)
	{
		if ($object == false || is_string($object)) return $object;

		return $object->count;
	}

	/**
	 * Pings the time endpoint to test the connection to a store.
	 *
	 * @return DateTime
	 */
	public static function getTime()
	{
		$response = self::connection()->get(self::$api_path . '/time');

		if ($response == false || is_string($response)) return $response;

		return new DateTime("@{$response->time}");
	}

	/**
	 * Returns the default collection of products.
	 *
	 * @return mixed array|string list of products or XML string if useXml is true
	 */
	public static function getProducts()
	{
		return self::getCollection('/products', 'Product');
	}

	/**
	 * Returns the total number of products in the collection.
	 *
	 * @return mixed int|string number of products or XML string if useXml is true
	 */
	public static function getProductsCount()
	{
		return self::getCount('/products/count');
	}

	/**
	 * Returns a single product resource by the given id.
	 *
	 * @param int $id product id
	 * @return BigCommerce_Api_Product|string
	 */
	public static function getProduct($id)
	{
		return self::getResource('/products/' . $id, 'Product');
	}

	/**
	 * Update the given product.
	 *
	 * @param int $id product id
	 * @param mixed $object fields to update
	 */
	public static function updateProduct($id, $object)
	{
		return self::updateResource('/products/' . $id, $object);
	}

	/**
	 * Return the collection of options.
	 *
	 * @param int $id option id
	 * @return array
	 */
	public static function getOptions()
	{
		return self::getCollection('/options', 'Option');
	}

	/**
	 * Return a single option by given id.
	 *
	 * @param int $id option id
	 * @return BigCommerce_Api_Option
	 */
	public static function getOption($id)
	{
		return self::getResource('/options/' . $id, 'Option');
	}

	/**
	 * Return a single value for an option.
	 *
	 * @param int $option_id option id
	 * @param int $id value id
	 * @return BigCommerce_Api_OptionValue
	 */
	public static function getOptionValue($option_id, $id)
	{
		return self::getResource('/options/' . $option_id . '/values/' . $id, 'OptionValue');
	}

	/**
	 * Return the collection of all option values.
	 *
	 * @return array
	 */
	public static function getOptionValues()
	{
		return self::getCollection('/options/values', 'OptionValue');
	}

	/**
	 *
	 */
	public static function getCategories()
	{
		return self::getCollection('/categories', 'Category');
	}

	/**
	 *
	 */
	public static function getCategoriesCount()
	{
		return self::getCount('/categories/count');
	}

	/**
	 *
	 */
	public static function getCategory($id)
	{
		return self::getResource('/categories/' . $id, 'Category');
	}

	/**
	 *
	 */
	public static function createCategory($object)
	{
		return self::createResource('/categories', $object);
	}

	/**
	 *
	 */
	public static function updateCategory($id, $object)
	{
		return self::updateResource('/categories/' . $id, $object);
	}

	/**
	 *
	 */
	public static function getBrands()
	{
		return self::getCollection('/brands', 'Brand');
	}

	/**
	 *
	 */
	public static function getBrandsCount()
	{
		return self::getCount('/brands/count');
	}

	/**
	 *
	 */
	public static function getBrand($id)
	{
		return self::getResource('/brands/' . $id, 'Brand');
	}

	/**
	 *
	 */
	public static function createBrand($object)
	{
		return self::createResource('/brands', $object);
	}

	/**
	 *
	 */
	public static function updateBrand($id, $object)
	{
		return self::updateResource('/brands/' . $id, $object);
	}

	/**
	 *
	 */
	public static function getOrders()
	{
		return self::getCollection('/orders', 'Order');
	}

	/**
	 *
	 */
	public static function getOrdersCount()
	{
		return self::getCount('/orders/count');
	}

	/**
	 *
	 */
	public static function getOrder($id)
	{
		return self::getResource('/orders/' . $id, 'Order');
	}

	/**
	 *
	 */
	public static function getCustomers()
	{
		return self::getCollection('/customers', 'Customer');
	}

	/**
	 *
	 */
	public static function getCustomersCount()
	{
		return self::getCount('/customers/count');
	}

	/**
	 *
	 */
	public static function getCustomer($id)
	{
		return self::getResource('/customers/' . $id, 'Customer');
	}

	/**
	 *
	 */
	public static function getOptionSets()
	{
		return self::getCollection('/optionsets', 'OptionSet');
	}

	/**
	 *
	 */
	public static function getOptionSetsCount()
	{
		return self::getCount('/optionsets/count');
	}

	/**
	 *
	 */
	public static function getOptionSet($id)
	{
		return self::getResource('/optionsets/' . $id, 'OptionSet');
	}

	/**
	 *
	 */
	public static function getOrderStatuses()
	{
		return self::getCollection('/orderstatuses', 'OrderStatus');
	}
}