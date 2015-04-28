<?php

namespace Bigcommerce\Api;

use \Exception as Exception;

/**
 * Bigcommerce API Client.
 */
class Client
{
    /**
     * Full Store URL to connect to
     *
     * @var string
     */
    static private $store_url;

    /**
     * Username to connect to the store API with
     *
     * @var string
     */
    static private $username;

    /**
     * API key
     *
     * @var string
     */
    static private $api_key;

    /**
     * Connection instance
     *
     * @var Connection
     */
    static private $connection;

    /**
     * Resource class name
     *
     * @var string
     */
    static private $resource;

    /**
     * API path prefix to be added to store URL for requests
     *
     * @var string
     */
    static private $path_prefix = '/api/v2';

    /**
     * Full URL path to the configured store API.
     *
     * @var string
     */
    static public $api_path;

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
     * @throws \Exception
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

        self::$username = $settings['username'];
        self::$api_key = $settings['api_key'];
        self::$store_url = rtrim($settings['store_url'], '/');
        self::$api_path = self::$store_url . self::$path_prefix;
        self::$connection = false;
    }

    /**
     * Configure the API client to throw exceptions when HTTP errors occur.
     *
     * Note that network faults will always cause an exception to be thrown.
     */
    public static function failOnError($option = true)
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
     * Return JSON objects from the API instead of XML Strings.
     * This is the default behavior.
     */
    public static function useJson()
    {
        self::connection()->useXml(false);
    }

    /**
     * Switch SSL certificate verification on requests.
     */
    public static function verifyPeer($option = false)
    {
        self::connection()->verifyPeer($option);
    }

    /**
     * Connect to the internet through a proxy server.
     *
     * @param string $host host server
     * @param bool $port port
     */
    public static function useProxy($host, $port = false)
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
     * @return Connection
     */
    private static function connection()
    {
        if (!self::$connection) {
            self::$connection = new Connection();
            self::$connection->authenticate(self::$username, self::$api_key);
        }

        return self::$connection;
    }

    /**
     * Convenience method to return instance of the connection
     *
     * @return Connection
     */
    public static function getConnection()
    {
        return self::connection();
    }

    /**
     * Set the HTTP connection object. DANGER: This can screw up your Client!
     *
     * @param Connection $connection The connection to use
     */
    public static function setConnection(Connection $connection = null)
    {
        self::$connection = $connection;
    }

    /**
     * Get a collection result from the specified endpoint.
     *
     * @param string $path api endpoint
     * @param string $resource resource class to map individual items
     * @return mixed array|string mapped collection or XML string if useXml is true
     */
    public static function getCollection($path, $resource = 'Resource')
    {
        $response = self::connection()->get(self::$api_path . $path);

        return self::mapCollection($resource, $response);
    }

    /**
     * Get a resource entity from the specified endpoint.
     *
     * @param string $path api endpoint
     * @param string $resource resource class to map individual items
     * @return mixed Resource|string resource object or XML string if useXml is true
     */
    public static function getResource($path, $resource = 'Resource')
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
     * @return hash|bool|mixed
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
     * @return hash|bool|mixed
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
     * @return hash|bool|mixed
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

        $baseResource = __NAMESPACE__ . '\\' . $resource;
        self::$resource = (class_exists($baseResource)) ? $baseResource : 'Bigcommerce\\Api\\Resources\\' . $resource;

        return array_map(array('self', 'mapCollectionObject'), $object);
    }

    /**
     * Callback for mapping collection objects resource classes.
     *
     * @param stdClass $object
     * @return Resource
     */
    private static function mapCollectionObject($object)
    {
        $class = self::$resource;

        return new $class($object);
    }

    /**
     * Map a single object to a resource class.
     *
     * @param string $resource name of the resource class
     * @param stdClass $object
     * @return Resource
     */
    private static function mapResource($resource, $object)
    {
        if ($object == false || is_string($object)) return $object;

        $baseResource = __NAMESPACE__ . '\\' . $resource;
        $class = (class_exists($baseResource)) ? $baseResource : 'Bigcommerce\\Api\\Resources\\' . $resource;

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

        return new \DateTime("@{$response->time}");
    }

    /**
     * Returns the default collection of products.
     *
     * @param array $filter
     * @return mixed array|string list of products or XML string if useXml is true
     */
    public static function getProducts($filter = array())
    {
        $filter = Filter::create($filter);
        return self::getCollection('/products' . $filter->toQuery(), 'Product');
    }

    /**
     * Gets collection of images for a product.
     *
     * @param int $id product id
     * @return mixed array|string list of products or XML string if useXml is true
     */
    public static function getProductImages($id)
    {
        return self::getCollection('/products/' . $id . '/images/', 'ProductImage');
    }

    /**
     * Gets collection of custom fields for a product.
     *
     * @param int $id product ID
     * @return mixed array|string list of products or XML string if useXml is true
     */
    public static function getProductCustomFields($id)
    {
        return self::getCollection('/products/' . $id . '/customfields/', 'ProductCustomField');
    }

    /**
     * Returns a single custom field by given id
     * @param  int $product_id product id
     * @param  int $id custom field id
     * @return ProductCustomField|bool Returns ProductCustomField if exists, false if not exists
     */
    public static function getProductCustomField($product_id, $id)
    {
        return self::getResource('/products/' . $product_id . '/customfields/' . $id, 'ProductCustomField');
    }

    /**
     * Create a new custom field for a given product.
     *
     * @param int $product_id product id
     * @param mixed $object fields to create
     * @return Object Object with `id`, `product_id`, `name` and `text` keys
     */
    public static function createProductCustomField($product_id, $object)
    {
        return self::createResource('/products/' . $product_id . '/customfields', $object);
    }

    /**
     * Gets collection of reviews for a product.
     *
     * @param $id
     * @return mixed
     */
    public static function getProductReviews($id)
    {
        return self::getCollection('/products/' . $id . '/reviews/', 'ProductReview');
    }

    /**
     * Update the given custom field.
     *
     * @param int $product_id product id
     * @param int $id custom field id
     * @param mixed $object custom field to update
     * @return hash|bool|mixed
     */
    public static function updateProductCustomField($product_id, $id, $object)
    {
        return self::updateResource('/products/' . $product_id . '/customfields/' . $id, $object);
    }

    /**
     * Delete the given custom field.
     *
     * @param int $product_id product id
     * @param int $id custom field id
     * @return hash|bool|mixed
     */
    public static function deleteProductCustomField($product_id, $id)
    {
        return self::deleteResource('/products/' . $product_id . '/customfields/' . $id);
    }

    /**
     * Returns the total number of products in the collection.
     *
     * @param array $filter
     * @return mixed int|string number of products or XML string if useXml is true
     */
    public static function getProductsCount($filter = array())
    {
        $filter = Filter::create($filter);
        return self::getCount('/products/count' . $filter->toQuery());
    }
    
    /**
     * Checks if a product resource is valid for counting
     *
     * @param array $resource
     * @return bool if product resource is valid for counting
     */
    public static function validProductResourceCount( $resource='' )
    {
        return in_array( $resource, array('images','videos','rules','skus','custom_fields','configurable_fields') );
    }
    
    /**
     * Returns count of all product resources of a specific type
     *
     * @param array $resource
     * @return int number of resources of specific type or zero if an invalid type
     */
    public static function getProductResourceCount($resource)
    {
        if( self::validProductResourceCount( $resource ) ) {
            return Client::getCount("/products/{$resource}/count");
        } return 0;
    }
    
    /**
     * Returns count of all product images
     *
     * @return int number of resources of images type
     */
    public static function getProductImagesCount() {
        return static::getProductResourceCount('images');
    }
    
    /**
     * Returns count of all product videos
     *
     * @return int number of resources of videos type
     */
    public static function getProductVideosCount() {
        return static::getProductResourceCount('videos');
    }
    
    /**
     * Returns count of all product rules
     *
     * @return int number of resources of rules type
     */
    public static function getProductRulesCount() {
        return static::getProductResourceCount('rules');
    }
    
    /**
     * Returns count of all product skus
     *
     * @return int number of resources of skus type
     */
    public static function getProductSKUSCount() {
        return static::getProductResourceCount('skus');
    }
    
    /**
     * Returns count of all product custom fields
     *
     * @return int number of resources of custom fields type
     */
    public static function getProductCustomFieldsCount() {
        return static::getProductResourceCount('custom_fields');
    }
    
    /**
     * Returns count of all product configurable fields
     *
     * @return int number of resources of configurable fields type
     */
    public static function getProductConfigurableFieldsCount() {
        return static::getProductResourceCount('configurable_fields');
    }

    /**
     * Returns a single product resource by the given id.
     *
     * @param int $id product id
     * @return Product|string
     */
    public static function getProduct($id)
    {
        return self::getResource('/products/' . $id, 'Product');
    }

    /**
     * Create a new product.
     *
     * @param mixed $object fields to create
     * @return hash|bool|mixed
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
     * @return hash|bool|mixed
     */
    public static function updateProduct($id, $object)
    {
        return self::updateResource('/products/' . $id, $object);
    }

    /**
     * Delete the given product.
     *
     * @param int $id product id
     * @return hash|bool|mixed
     */
    public static function deleteProduct($id)
    {
        return self::deleteResource('/products/' . $id);
    }

    /**
     * Delete all products.
     *
     * @return hash|bool|mixed
     */
    public static function deleteAllProducts()
    {
        return self::deleteResource('/products');
    }

    /**
     * Return the collection of options.
     *
     * @param array $filter
     * @return array
     */
    public static function getOptions($filter = array())
    {
        $filter = Filter::create($filter);
        return self::getCollection('/options' . $filter->toQuery(), 'Option');
    }

    /**
     * Create Options
     * @param $object
     * @return hash|bool|mixed
     */
    public static function createOptions($object)
    {
        return self::createResource('/options', $object);
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
     * @return Option
     */
    public static function getOption($id)
    {
        return self::getResource('/options/' . $id, 'Option');
    }


    /**
     * Delete the given option.
     *
     * @param int $id option id
     * @return hash|bool|mixed
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
     * @return OptionValue
     */
    public static function getOptionValue($option_id, $id)
    {
        return self::getResource('/options/' . $option_id . '/values/' . $id, 'OptionValue');
    }

    /**
     * Return the collection of all option values.
     *
     * @param array $filter
     * @return array
     */
    public static function getOptionValues($filter = array())
    {
        $filter = Filter::create($filter);
        return self::getCollection('/options/values' . $filter->toQuery(), 'OptionValue');
    }

    /**
     * The collection of categories.
     *
     * @param array $filter
     * @return array
     */
    public static function getCategories($filter = array())
    {
        $filter = Filter::create($filter);
        return self::getCollection('/categories' . $filter->toQuery(), 'Category');
    }

    /**
     * The number of categories in the collection.
     *
     * @param array $filter
     * @return int
     */
    public static function getCategoriesCount($filter = array())
    {
        $filter = Filter::create($filter);
        return self::getCount('/categories/count' . $filter->toQuery());
    }

    /**
     * A single category by given id.
     *
     * @param int $id category id
     * @return Category
     */
    public static function getCategory($id)
    {
        return self::getResource('/categories/' . $id, 'Category');
    }

    /**
     * Create a new category from the given data.
     *
     * @param mixed $object
     * @return hash|bool|mixed
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
     * @return hash|bool|mixed
     */
    public static function updateCategory($id, $object)
    {
        return self::updateResource('/categories/' . $id, $object);
    }

    /**
     * Delete the given category.
     *
     * @param int $id category id
     * @return hash|bool|mixed
     */
    public static function deleteCategory($id)
    {
        return self::deleteResource('/categories/' . $id);
    }

    /**
     * Delete all categories.
     *
     * @return hash|bool|mixed
     */
    public static function deleteAllCategories()
    {
        return self::deleteResource('/categories');
    }

    /**
     * The collection of brands.
     *
     * @param array $filter
     * @return array
     */
    public static function getBrands($filter = array())
    {
        $filter = Filter::create($filter);
        return self::getCollection('/brands' . $filter->toQuery(), 'Brand');
    }

    /**
     * The total number of brands in the collection.
     *
     * @param array $filter
     * @return int
     */
    public static function getBrandsCount($filter = array())
    {
        $filter = Filter::create($filter);
        return self::getCount('/brands/count' . $filter->toQuery());
    }

    /**
     * A single brand by given id.
     *
     * @param int $id brand id
     * @return Brand
     */
    public static function getBrand($id)
    {
        return self::getResource('/brands/' . $id, 'Brand');
    }

    /**
     * Create a new brand from the given data.
     *
     * @param mixed $object
     * @return hash|bool|mixed
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
     * @return hash|bool|mixed
     */
    public static function updateBrand($id, $object)
    {
        return self::updateResource('/brands/' . $id, $object);
    }

    /**
     * Delete the given brand.
     *
     * @param int $id brand id
     * @return hash|bool|mixed
     */
    public static function deleteBrand($id)
    {
        return self::deleteResource('/brands/' . $id);
    }

    /**
     * Delete all brands.
     *
     * @return hash|bool|mixed
     */
    public static function deleteAllBrands()
    {
        return self::deleteResource('/brands');
    }

    /**
     * The collection of orders.
     *
     * @param array $filter
     * @return array
     */
    public static function getOrders($filter = array())
    {
        $filter = Filter::create($filter);
        return self::getCollection('/orders' . $filter->toQuery(), 'Order');
    }

    /**
     * The number of orders in the collection.
     *
     * @param array $filter
     * @return int
     */
    public static function getOrdersCount($filter = array())
    {
        $filter = Filter::create($filter);
        return self::getCount('/orders/count' . $filter->toQuery());
    }

    /**
     * A single order.
     *
     * @param int $id order id
     * @return Order
     */
    public static function getOrder($id)
    {
        return self::getResource('/orders/' . $id, 'Order');
    }

    /**
     * @param $orderID
     * @return mixed
     */
    public static function getOrderProducts($orderID)
    {
        return self::getCollection('/orders/' . $orderID . '/products', 'OrderProduct');
    }

    /**
     * The total number of order products in the collection.
     *
     * @param $orderID
     * @param array $filter
     * @return mixed
     */
    public static function getOrderProductsCount($orderID, $filter = array())
    {
        $filter = Filter::create($filter);
        return self::getCount('/orders/' . $orderID . '/products/count' . $filter->toQuery());
    }

    /**
     * Delete the given order (unlike in the Control Panel, this will permanently
     * delete the order).
     *
     * @param int $id order id
     * @return hash|bool|mixed
     */
    public static function deleteOrder($id)
    {
        return self::deleteResource('/orders/' . $id);
    }

    /**
     * Delete all orders.
     *
     * @return hash|bool|mixed
     */
    public static function deleteAllOrders()
    {
        return self::deleteResource('/orders');
    }

    /**
     * Create an order
     *
     * @param $object
     * @return hash|bool|mixed
     */
    public static function createOrder($object)
    {
        return self::createResource('/orders', $object);
    }

    /**
     * Update the given order.
     *
     * @param int $id order id
     * @param mixed $object fields to update
     * @return hash|bool|mixed
     */
    public static function updateOrder($id, $object)
    {
        return self::updateResource('/orders/' . $id, $object);
    }

    /**
     * The list of customers.
     *
     * @param array $filter
     * @return array
     */
    public static function getCustomers($filter = array())
    {
        $filter = Filter::create($filter);
        return self::getCollection('/customers' . $filter->toQuery(), 'Customer');
    }

    /**
     * The total number of customers in the collection.
     *
     * @param array $filter
     * @return int
     */
    public static function getCustomersCount($filter = array())
    {
        $filter = Filter::create($filter);
        return self::getCount('/customers/count' . $filter->toQuery());
    }

    /**
     * Bulk delete customers.
     *
     * @param array $filter
     * @return array
     */
    public static function deleteCustomers($filter = array())
    {
        $filter = Filter::create($filter);
        return self::deleteResource('/customers' . $filter->toQuery());
    }

    /**
     * A single customer by given id.
     *
     * @param int $id customer id
     * @return Customer
     */
    public static function getCustomer($id)
    {
        return self::getResource('/customers/' . $id, 'Customer');
    }

    /**
     * Create a new customer from the given data.
     *
     * @param mixed $object
     * @return hash|bool|mixed
     */
    public static function createCustomer($object)
    {
        return self::createResource('/customers', $object);
    }

    /**
     * Update the given customer.
     *
     * @param int $id customer id
     * @param mixed $object
     * @return hash|bool|mixed
     */
    public static function updateCustomer($id, $object)
    {
        return self::updateResource('/customers/' . $id, $object);
    }

    /**
     * Delete the given customer.
     *
     * @param int $id customer id
     * @return hash|bool|mixed
     */
    public static function deleteCustomer($id)
    {
        return self::deleteResource('/customers/' . $id);
    }

    /**
     * A list of addresses belonging to the given customer.
     *
     * @param int $id customer id
     * @return array
     */
    public static function getCustomerAddresses($id)
    {
        return self::getCollection('/customers/' . $id . '/addresses', 'Address');
    }

    /**
     * Returns the collection of option sets.
     *
     * @param array $filter
     * @return array
     */
    public static function getOptionSets($filter = array())
    {
        $filter = Filter::create($filter);
        return self::getCollection('/optionsets' . $filter->toQuery(), 'OptionSet');
    }

    /**
     * Create Optionsets
     *
     * @param $object
     * @return hash|bool|mixed
     */
    public static function createOptionsets($object)
    {
        return self::createResource('/optionsets', $object);
    }

    /**
     * Create Optionset Options
     *
     * @param $object
     * @param $id
     * @return hash|bool|mixed
     */
    public static function createOptionsets_Options($object, $id)
    {
        return self::createResource('/optionsets/' . $id . '/options', $object);
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
     * @return OptionSet
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
    public static function getOrderStatus($id)
    {
        return self::getResource('/order_statuses/' . $id, 'OrderStatus');
    }

    /**
     * Status codes used to represent the state of an order.
     *
     * @return array
     */
    public static function getOrderStatuses()
    {
        return self::getCollection('/order_statuses', 'OrderStatus');
    }

    /**
     * Get collection of product skus
     *
     * @param array $filter
     * @return mixed
     */
    public static function getSkus($filter = array())
    {
        $filter = Filter::create($filter);
        return self::getCollection('/products/skus' . $filter->toQuery(), 'Sku');
    }

    /**
     * Create sku
     *
     * @param $object
     * @return hash|bool|mixed
     */
    public static function createSku($object)
    {
        return self::createResource('/product/skus', $object);
    }

    /**
     * Update sku
     *
     * @param $id
     * @param $object
     * @return hash|bool|mixed
     */
    public static function updateSku($id, $object)
    {
        return self::updateResource('/product/skus/' . $id, $object);
    }

    /**
     * Get a single coupon by given id.
     *
     * @param int $id customer id
     * @return Coupon
     */
    public static function getCoupon($id)
    {
        return self::getResource('/coupons/' . $id, 'Coupon');
    }

    /**
     * Get coupons
     *
     * @param array $filter
     * @return mixed
     */
    public static function getCoupons($filter = array())
    {
        $filter = Filter::create($filter);
        return self::getCollection('/coupons' . $filter->toQuery(), 'Coupon');
    }

    /**
     * Create coupon
     *
     * @param $object
     * @return hash|bool|mixed
     */
    public static function createCoupon($object)
    {
        return self::createResource('/coupons', $object);
    }

    /**
     * Update coupon
     *
     * @param $id
     * @param $object
     * @return hash|bool|mixed
     */
    public static function updateCoupon($id, $object)
    {
        return self::updateResource('/coupons/' . $id, $object);
    }

    /**
     * Delete the given coupon.
     *
     * @param int $id coupon id
     * @return hash|bool|mixed
     */
    public static function deleteCoupon($id)
    {
        return self::deleteResource('/coupons/' . $id);
    }

    /**
     * Delete all Coupons.
     *
     * @return hash|bool|mixed
     */
    public static function deleteAllCoupons()
    {
        return self::deleteResource('/coupons');
    }

    /**
     * Return the number of coupons
     *
     * @return int
     */
    public static function getCouponsCount()
    {
        return self::getCount('/coupons/count');
    }

    /**
     * The request logs with usage history statistics.
     */
    public static function getRequestLogs()
    {
        return self::getCollection('/requestlogs', 'RequestLog');
    }

    public static function getStore()
    {
        $response = self::connection()->get(self::$api_path . '/store');
        return $response;
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

    /**
     * Get a single shipment by given id.
     *
     * @param $orderID
     * @param $shipmentID
     * @return mixed
     */
    public static function getShipment($orderID, $shipmentID)
    {
        return self::getResource('/orders/' . $orderID . '/shipments/' . $shipmentID, 'Shipment');
    }

    /**
     * Get shipments for a given order
     *
     * @param $orderID
     * @param array $filter
     * @return mixed
     */
    public static function getShipments($orderID, $filter = array())
    {
        $filter = Filter::create($filter);
        return self::getCollection('/orders/' . $orderID . '/shipments' . $filter->toQuery(), 'Shipment');
    }

    /**
     * Create shipment
     *
     * @param $orderID
     * @param $object
     * @return hash|bool|mixed
     */
    public static function createShipment($orderID, $object)
    {
        return self::createResource('/orders/' . $orderID . '/shipments', $object);
    }

    /**
     * Update shipment
     *
     * @param $orderID
     * @param $shipmentID
     * @param $object
     * @return hash|bool|mixed
     */
    public static function updateShipment($orderID, $shipmentID, $object)
    {
        return self::updateResource('/orders/' . $orderID . '/shipments/' . $shipmentID, $object);
    }

    /**
     * Delete the given shipment.
     *
     * @param $orderID
     * @param $shipmentID
     * @return hash|bool|mixed
     */
    public static function deleteShipment($orderID, $shipmentID)
    {
        return self::deleteResource('/orders/' . $orderID . '/shipments/' . $shipmentID);
    }

    /**
     * Delete all Shipments for the given order.
     *
     * @param $orderID
     * @return hash|bool|mixed
     */
    public static function deleteAllShipmentsForOrder($orderID)
    {
        return self::deleteResource('/orders/' . $orderID . '/shipments');
    }

    /**
     * Get a single order shipping address by given order and order shipping address id.
     *
     * @param $orderID
     * @param $orderShippingAddressID
     * @return mixed
     */
    public static function getOrderShippingAddress($orderID, $orderShippingAddressID)
    {
        return self::getResource('/orders/' . $orderID . '/shipping_addresses/' . $orderShippingAddressID, 'Address');
    }

    /**
     * Get order shipping addresses for a given order
     *
     * @param $orderID
     * @param array $filter
     * @return mixed
     */
    public static function getOrderShippingAddresses($orderID, $filter = array())
    {
        $filter = Filter::create($filter);
        return self::getCollection('/orders/' . $orderID . '/shipping_addresses' . $filter->toQuery(), 'Address');
    }
}
