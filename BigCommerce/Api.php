<?php
require_once dirname(__FILE__).'/Api/Connection.php';
require_once dirname(__FILE__).'/Api/Resources.php';
require_once dirname(__FILE__).'/Api/Filter.php';

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
	 *
	 * Requires a settings array to be passed in with the following keys:
	 *
	 * - store_url
	 * - username
	 * - api_key
	 *
	 * @param array $settings
	 */
	public static function configure(array $settings)
	{
		if (!isset($settings['store_url'])) {
			throw new Exception("'store_url' must be provided");
		}

		if (!isset($settings['username'])) {
			throw new Exception("'username' must be provided");
		}

		if (!isset($settings['api_key'])) {
			throw new Exception("'api_key' must be provided");
		}

		self::$username  = $settings['username'];
		self::$api_key 	 = $settings['api_key'];
		self::$store_url = rtrim($settings['store_url'], '/');
		self::$api_path  = self::$store_url . self::$api_path;
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
	 * Send a delete request to remove the specified resource.
	 *
	 * @param string $path api endpoint
	 */
	public static function deleteResource($path)
	{
		return self::connection()->delete(self::$api_path . $path);
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
	 * @param array $filter
	 * @return mixed array|string list of products or XML string if useXml is true
	 */
	public static function getProducts($filter=false)
	{
		$filter = BigCommerce_Api_Filter::create($filter);
		return self::getCollection('/products' . $filter->toQuery(), 'Product');
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
	 * Create a new product.
	 *
	 * @param mixed $object fields to create
	 */
	public static function createProduct($object)
	{
		return self::createResource('/products', $object);
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
	 * Delete the given product.
	 *
	 * @param int $id product id
	 */
	public static function deleteProduct($id)
	{
		return self::deleteResource('/products/' . $id);
	}

	/**
	 * Return the collection of options.
	 *
	 * @param array $filter
	 * @return array
	 */
	public static function getOptions($filter=false)
	{
		$filter = BigCommerce_Api_Filter::create($filter);
		return self::getCollection('/options' . $filter->toQuery(), 'Option');
	}

	/**
	 * Return the number of options in the collection
	 *
	 * @return int
	 */
	public static function getOptionsCount()
	{
		return self::getCount('/options/count');
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
	 * Delete the given option.
	 *
	 * @param int $id option id
	 */
	public static function deleteOption($id)
	{
		return self::deleteResource('/options/' . $id);
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
	 * @param mixed $filter
	 * @return array
	 */
	public static function getOptionValues($filter=false)
	{
		$filter = BigCommerce_Api_Filter::create($filter);
		return self::getCollection('/options/values' . $filter->toQuery(), 'OptionValue');
	}

	/**
	 * The collection of categories.
	 *
	 * @param mixed $filter
	 * @return array
	 */
	public static function getCategories($filter=false)
	{
		$filter = BigCommerce_Api_Filter::create($filter);
		return self::getCollection('/categories' . $filter->toQuery(), 'Category');
	}

	/**
	 * The number of categories in the collection.
	 *
	 * @return int
	 */
	public static function getCategoriesCount()
	{
		return self::getCount('/categories/count');
	}

	/**
	 * A single category by given id.
	 *
	 * @param int $id category id
	 * @return BigCommerce_Api_Category
	 */
	public static function getCategory($id)
	{
		return self::getResource('/categories/' . $id, 'Category');
	}

	/**
	 * Create a new category from the given data.
	 *
	 * @param mixed $object
	 */
	public static function createCategory($object)
	{
		return self::createResource('/categories', $object);
	}

	/**
	 * Update the given category.
	 *
	 * @param int $id category id
	 * @param mixed $object
	 */
	public static function updateCategory($id, $object)
	{
		return self::updateResource('/categories/' . $id, $object);
	}

	/**
	 * Delete the given category.
	 *
	 * @param int $id category id
	 */
	public static function deleteCategory($id)
	{
		return self::deleteResource('/categories/' . $id);
	}

	/**
	 * The collection of brands.
	 *
	 * @param mixed $filter
	 * @return array
	 */
	public static function getBrands($filter=false)
	{
		$filter = BigCommerce_Api_Filter::create($filter);
		return self::getCollection('/brands' . $filter->toQuery(), 'Brand');
	}

	/**
	 * The total number of brands in the collection.
	 *
	 * @return int
	 */
	public static function getBrandsCount()
	{
		return self::getCount('/brands/count');
	}

	/**
	 * A single brand by given id.
	 *
	 * @param int $id brand id
	 * @return BigCommerce_Api_Brand
	 */
	public static function getBrand($id)
	{
		return self::getResource('/brands/' . $id, 'Brand');
	}

	/**
	 * Create a new brand from the given data.
	 *
	 * @param mixed $object
	 */
	public static function createBrand($object)
	{
		return self::createResource('/brands', $object);
	}

	/**
	 * Update the given brand.
	 *
	 * @param int $id brand id
	 * @param mixed $object
	 */
	public static function updateBrand($id, $object)
	{
		return self::updateResource('/brands/' . $id, $object);
	}

	/**
	 * Delete the given brand.
	 *
	 * @param int $id brand id
	 */
	public static function deleteBrand($id)
	{
		return self::deleteResource('/brands/' . $id);
	}

	/**
	 * The collection of orders.
	 *
	 * @param mixed $filter
	 * @return array
	 */
	public static function getOrders($filter=false)
	{
		$filter = BigCommerce_Api_Filter::create($filter);
		return self::getCollection('/orders' . $filter->toQuery(), 'Order');
	}

	/**
	 * The number of orders in the collection.
	 *
	 * @return int
	 */
	public static function getOrdersCount()
	{
		return self::getCount('/orders/count');
	}

	/**
	 * A single order.
	 *
	 * @param int $id order id
	 * @return BigCommerce_Api_Order
	 */
	public static function getOrder($id)
	{
		return self::getResource('/orders/' . $id, 'Order');
	}

	/**
	 * Delete the given order (unlike in the Control Panel, this will permanently
	 * delete the order).
	 *
	 * @param int $id order id
	 */
	public static function deleteOrder($id)
	{
		return self::deleteResource('/orders/' . $id);
	}


	/**
	 * The list of customers.
	 *
	 * @param mixed $filter
	 * @return array
	 */
	public static function getCustomers($filter=false)
	{
		$filter = BigCommerce_Api_Filter::create($filter);
		return self::getCollection('/customers' . $filter->toQuery(), 'Customer');
	}

	/**
	 * The total number of customers in the collection.
	 *
	 * @return int
	 */
	public static function getCustomersCount()
	{
		return self::getCount('/customers/count');
	}

	/**
	 * A single customer by given id.
	 *
	 * @param int $id customer id
	 * @return BigCommerce_Api_Customer
	 */
	public static function getCustomer($id)
	{
		return self::getResource('/customers/' . $id, 'Customer');
	}

	/**
	 * A list of addresses belonging to the given customer.
	 *
	 * @param int $id customer id
	 * @return array
	 */
	public static function getAddresses($id)
	{
		return self::getCollection('/customer/' . $id . '/addresses', 'Address');
	}

	/**
	 * Returns the collection of option sets.
	 *
	 * @param array $filter
	 * @return array
	 */
	public static function getOptionSets($filter=false)
	{
		$filter = BigCommerce_Api_Filter::create($filter);
		return self::getCollection('/optionsets' . $filter->toQuery(), 'OptionSet');
	}

	/**
	 * Returns the total number of option sets in the collection.
	 *
	 * @return int
	 */
	public static function getOptionSetsCount()
	{
		return self::getCount('/optionsets/count');
	}

	/**
	 * A single option set by given id.
	 *
	 * @param int $id option set id
	 * @return BigCommerce_Api_OptionSet
	 */
	public static function getOptionSet($id)
	{
		return self::getResource('/optionsets/' . $id, 'OptionSet');
	}

	/**
	 * Status codes used to represent the state of an order.
	 *
	 * @return array
	 */
	public static function getOrderStatuses()
	{
		return self::getCollection('/orderstatuses', 'OrderStatus');
	}

	/**
	 * The request logs with usage history statistics.
	 */
	public static function getRequestLogs()
	{
		return self::getCollection('/requestlogs');
	}

	/**
	 * The number of requests remaining at the current time. Based on the
	 * last request that was fetched within the current script. If no
	 * requests have been made, pings the time endpoint to get the value.
	 *
	 * @return int
	 */
	public static function getRequestsRemaining()
	{
		$limit = self::connection()->getHeader('X-BC-ApiLimit-Remaining');

		if (!$limit) {
			$result = self::getTime();

			if (!$result) return false;

			$limit = self::connection()->getHeader('X-BC-ApiLimit-Remaining');
		}

		return intval($limit);
	}

}