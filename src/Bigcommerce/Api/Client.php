<?php

namespace Bigcommerce\Api;

use \Exception as Exception;
use Firebase\JWT\JWT;

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
    private static $store_url;

    /**
     * Username to connect to the store API with
     *
     * @var string
     */
    private static $username;

    /**
     * API key
     *
     * @var string
     */
    private static $api_key;

    /**
     * Connection instance
     *
     * @var Connection
     */
    private static $connection;

    /**
     * Resource class name
     *
     * @var string
     */
    private static $resource;

    /**
     * version name
     *
     * @var string
     */
    public static $version;

    /**
     * API path prefix to be added to store URL for requests
     *
     * @var string
     * @deprecated
     */
    private static $path_prefix = '/api/v2';

    /**
     * Full URL path to the configured store API.
     *
     * @var string
     */
    public static $api_path;
    private static $client_id;
    private static $store_hash;
    private static $auth_token;
    private static $client_secret;
    private static $stores_prefix = '/stores/%s/';
    private static $api_url = 'https://api.bigcommerce.com';
    private static $login_url = 'https://login.bigcommerce.com';
    private static $available_versions = array(null, "v2", "v3");

    /**
     * Configure the API client with the required settings to access
     * the API for a store.
     *
     * Accepts OAuth and (for now!) Basic Auth credentials
     *
     * configure using Basic Auth credentials doesn't support anymore
     *
     * @param array $settings
     * @throws Exception
     */
    public static function configure($settings)
    {
        if (isset($settings['client_id'])) {
            self::configureOAuth($settings);
        } else {
            throw new Exception("'client_id' must be provided");
        }
    }

    /**
     * Configure the API client with the required OAuth credentials.
     *
     * Requires a settings array to be passed in with the following keys:
     *
     * - client_id
     * - auth_token
     * - store_hash
     *
     * @param array $settings
     * @throws Exception
     */
    public static function configureOAuth($settings)
    {
        if (!isset($settings['auth_token'])) {
            throw new Exception("'auth_token' must be provided");
        }

        if (!isset($settings['store_hash'])) {
            throw new Exception("'store_hash' must be provided");
        }

        self::$client_id = $settings['client_id'];
        self::$auth_token = $settings['auth_token'];
        self::$store_hash = $settings['store_hash'];

        self::$client_secret = isset($settings['client_secret']) ? $settings['client_secret'] : null;

        if (isset($settings['version'])) {
            if (in_array($settings['version'], self::$available_versions)) {
                self::$version = (!is_null($settings['version']))? $settings['version']:'v2';
            } else {
                throw new Exception("'version' not available");
            }
        }

        self::$version = isset($settings['version']) ? $settings['version'] : "v2";

        self::$api_path = self::$api_url . sprintf(self::$stores_prefix, self::$store_hash);
        self::$connection = false;
    }

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
     * @throws Exception
     * @deprecated
     */
    public static function configureBasicAuth(array $settings)
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
     *
     * @param bool $option sets the value of this flag
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
     *
     * @param bool $option sets the value of this flag
     */
    public static function verifyPeer($option = false)
    {
        self::connection()->verifyPeer($option);
    }

    /**
     * Connect to the internet through a proxy server.
     *
     * @param string $host host server
     * @param int|bool $port port number to use, or false
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
            self::$connection->authenticateOauth(self::$client_id, self::$auth_token);
//            if (self::$client_id) {
//                self::$connection->authenticateOauth(self::$client_id, self::$auth_token);
//            } else {
//                self::$connection->authenticateBasic(self::$username, self::$api_key);
//            }
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
     * @param null $version
     * @return mixed array|string mapped collection or XML string if useXml is true
     * @throws Exception
     */
    public static function getCollection($path, $resource = 'Resource', $version = null)
    {
        $temp_version = (!is_null($version) and in_array($version, self::$available_versions))?$version:self::$version;
        $path = ($resource !== "Resource")?self::getUrl($resource, $temp_version).$path : $path;
        $response = self::connection()->get(self::$api_path .$temp_version. $path);
        if (isset($response->data)) {
            return self::mapCollection($resource, $response->data);
        } else {
            return self::mapCollection($resource, $response);
        }
    }

    /**
     * Get a resource entity from the specified endpoint.
     *
     * @param string $path api endpoint
     * @param string $resource resource class to map individual items
     * @param null $version
     * @return mixed Resource|string resource object or XML string if useXml is true
     * @throws Exception
     */
    public static function getResource($path, $resource = 'Resource', $version = null)
    {
        $temp_version = (!is_null($version) and in_array($version, self::$available_versions))?$version:self::$version;
        $path = ($resource !== "Resource")?self::getUrl($resource, $temp_version).$path : $path;

        $response = self::connection()->get(self::$api_path .$temp_version. $path);

        if (isset($response->data)) {
            return self::mapResource($resource, $response->data);
        } else {
            return self::mapResource($resource, $response);
        }
    }

    /**
     * Get url from Resource Class if exist.
     *
     * @param string $resource resource class to map individual items
     * @param $version
     * @return mixed Resource|string resource object or XML string if useXml is true
     * @throws Exception
     */

    public static function getUrl($resource, $version)
    {
        $baseResource = __NAMESPACE__ . '\\' . $resource;
        $resource_namespace = (class_exists($baseResource)) ? $baseResource : 'Bigcommerce\\Api\\Resources\\' . $resource;
        $object = new $resource_namespace();
        if (isset($object->urls)) {
            if (array_key_exists($version, $object->urls)) {
                return $object->urls[$version];
            } else {
                throw new Exception($version." not available for this resource");
            }
        } else {
            return "";
        }
    }

    /**
     * Get a count value from the specified endpoint.
     *
     * @param string $path api endpoint
     * @param string $resource
     * @param null $version
     * @return mixed int|string count value or XML string if useXml is true
     * @throws Exception
     */
    public static function getCount($path, $resource = "Resource", $version = null)
    {
        $temp_version = (!is_null($version) and in_array($version, self::$available_versions))?$version:self::$version;
        $path = ($resource !== "Resource")?self::getUrl($resource, $temp_version).$path : $path;

        $response = self::connection()->get(self::$api_path .$temp_version. $path);

        if (self::$version == "v2") {
            if ($response == false || is_string($response)) {
                return $response;
            }

            return $response->count;
        } else {
            return $response->meta->pagination->total;
        }
    }

    /**
     * Send a post request to create a resource on the specified collection.
     *
     * @param string $path api endpoint
     * @param mixed $object object or XML string to create
     * @param string $resource
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function createResource($path, $object, $resource = "Resource", $version = null)
    {
        $temp_version = (!is_null($version) and in_array($version, self::$available_versions))?$version:self::$version;
        if (is_array($object)) {
            $object = (object)$object;
        }
        $path = ($resource !== "Resource")?self::getUrl($resource, $temp_version).$path : $path;
        return self::connection()->post(self::$api_path .$temp_version. $path, $object);
    }

    /**
     * Send a put request to update the specified resource.
     *
     * @param string $path api endpoint
     * @param mixed $object object or XML string to update
     * @param string $resource
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function updateResource($path, $object, $resource = "Resource", $version = null)
    {
        $temp_version = (!is_null($version) and in_array($version, self::$available_versions))?$version:self::$version;
        if (is_array($object)) {
            $object = (object)$object;
        }
        $path = ($resource !== "Resource")?self::getUrl($resource, $temp_version).$path : $path;
        return self::connection()->put(self::$api_path .$temp_version. $path, $object);
    }

    /**
     * Send a delete request to remove the specified resource.
     *
     * @param string $path api endpoint
     * @param string $resource
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function deleteResource($path, $resource = "Resource", $version = null)
    {
        $temp_version = (!is_null($version) and in_array($version, self::$available_versions))?$version:self::$version;
        $path = ($resource !== "Resource")?self::getUrl($resource, $temp_version).$path : $path;
        return self::connection()->delete(self::$api_path .$temp_version. $path);
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
        if ($object == false || is_string($object)) {
            return $object;
        }

        $baseResource = __NAMESPACE__ . '\\' . $resource;
        self::$resource = (class_exists($baseResource)) ? $baseResource : 'Bigcommerce\\Api\\Resources\\' . $resource;

        return array_map(array('self', 'mapCollectionObject'), $object);
    }

    /**
     * Callback for mapping collection objects resource classes.
     *
     * @param \stdClass $object
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
     * @param \stdClass $object
     * @return Resource
     */
    private static function mapResource($resource, $object)
    {
        if ($object == false || is_string($object)) {
            return $object;
        }

        $baseResource = __NAMESPACE__ . '\\' . $resource;
        $class = (class_exists($baseResource)) ? $baseResource : 'Bigcommerce\\Api\\Resources\\' . $resource;
        return new $class($object);
    }

    /**
     * Map object representing a count to an integer value.
     *
     * @param \stdClass $object
     * @return int
     */
    private static function mapCount($object)
    {
        if ($object == false || is_string($object)) {
            return $object;
        }

        return $object->count;
    }

    /**
     * Swaps a temporary access code for a long expiry auth token.
     *
     * @param \stdClass|array $object
     * @return \stdClass
     */
    public static function getAuthToken($object)
    {
        $context = array_merge(array('grant_type' => 'authorization_code'), (array)$object);
        $connection = new Connection();

        return $connection->post(self::$login_url . '/oauth2/token', $context);
    }

    /**
     * @param int $id
     * @param string $redirectUrl
     * @param string $requestIp
     * @return string
     */
    public static function getCustomerLoginToken($id, $redirectUrl = '', $requestIp = '')
    {
        if (empty(self::$client_secret)) {
            throw new Exception('Cannot sign customer login tokens without a client secret');
        }

        $payload = array(
            'iss' => self::$client_id,
            'iat' => time(),
            'jti' => bin2hex(random_bytes(32)),
            'operation' => 'customer_login',
            'store_hash' => self::$store_hash,
            'customer_id' => $id
        );

        if (!empty($redirectUrl)) {
            $payload['redirect_to'] = $redirectUrl;
        }

        if (!empty($requestIp)) {
            $payload['request_ip'] = $requestIp;
        }

        return JWT::encode($payload, self::$client_secret, 'HS256');
    }

    /**
     * Pings the time endpoint to test the connection to a store.
     *
     * @return \DateTime
     */
    public static function getTime()
    {
        $response = self::connection()->get(self::$api_path . '/time');

        if ($response == false || is_string($response)) {
            return $response;
        }

        return new \DateTime("@{$response->time}");
    }

    /**
     * Returns the default collection of products.
     *
     * @param array $filter
     * @param null $version
     * @return mixed array|string list of products or XML string if useXml is true
     * @throws Exception
     */
    public static function getProducts($filter = array(), $version = null)
    {
        $filter = Filter::create($filter);
        return self::getCollection('' . $filter->toQuery(), 'Product', $version);
    }

    /**
     * Gets collection of images for a product.
     *
     * @param int $id product id
     * @param array $filter
     * @param null $version
     * @return mixed array|string list of products or XML string if useXml is true
     * @throws Exception
     */
    public static function getProductImages($id, $filter = array(), $version = null)
    {
        $filter = Filter::create($filter);
        return self::getCollection('/' . $id . '/images'.$filter->toQuery(), 'ProductImage', $version);
    }

    /**
     * Gets collection of custom fields for a product.
     *
     * @param int $id product ID
     * @param null $version
     * @return array|string list of products or XML string if useXml is true
     * @throws Exception
     */
    public static function getProductCustomFields($id, $version = null)
    {
        $temp_version = (is_null($version))?self::$version:$version;
        if ($temp_version === "v2") {
            return self::getCollection('/' . $id . '/custom_fields', 'ProductCustomField', $version);
        } else {
            return self::getCollection('/' . $id . '/custom-fields', 'ProductCustomField', $version);
        }
    }

    /**
     * Returns a single custom field by given id
     * @param int $product_id product id
     * @param int $id custom field id
     * @param null $version
     * @return Resources\ProductCustomField|bool Returns ProductCustomField if exists, false if not exists
     * @throws Exception
     */
    public static function getProductCustomField($product_id, $id, $version = null)
    {
        $temp_version = (is_null($version))?self::$version:$version;
        if ($temp_version === "v2") {
            return self::getResource('/' . $product_id . '/custom_fields/' . $id, 'ProductCustomField', $version);
        } else {
            return self::getResource('/' . $product_id . '/custom-fields/' . $id, 'ProductCustomField', $version);
        }
    }

    /**
     * Create a new custom field for a given product.
     *
     * @param int $product_id product id
     * @param mixed $object fields to create
     * @param $version
     * @return Object Object with `id`, `product_id`, `name` and `text` keys
     * @throws Exception
     */
    public static function createProductCustomField($product_id, $object, $version)
    {
        $temp_version = (is_null($version))?self::$version:$version;
        if ($temp_version === "v2") {
            return self::createResource('/' . $product_id . '/custom_fields', $object, 'ProductCustomField', $version);
        } else {
            return self::createResource('/' . $product_id . '/custom-fields', $object, 'ProductCustomField', $version);
        }
    }

    /**
     * Gets collection of reviews for a product.
     *
     * @param $id
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function getProductReviews($id, $version = null)
    {
        return self::getCollection('/' . $id . '/reviews/', 'ProductReview', $version);
    }

    /**
     * Update the given custom field.
     *
     * @param int $product_id product id
     * @param int $id custom field id
     * @param mixed $object custom field to update
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function updateProductCustomField($product_id, $id, $object, $version = null)
    {
        $temp_version = (is_null($version))?self::$version:$version;
        if ($temp_version === "v2") {
            return self::updateResource('/' . $product_id . '/custom_fields/' . $id, $object, 'ProductCustomField', $version);
        } else {
            return self::updateResource('/' . $product_id . '/custom-fields/' . $id, $object, 'ProductCustomField', $version);
        }
    }

    /**
     * Delete the given custom field.
     *
     * @param int $product_id product id
     * @param int $id custom field id
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function deleteProductCustomField($product_id, $id, $version = null)
    {
        $temp_version = (is_null($version))?self::$version:$version;
        if ($temp_version === "v2") {
            return self::deleteResource('/' . $product_id . '/custom_fields/' . $id, 'ProductCustomField', $version);
        } else {
            return self::deleteResource('/' . $product_id . '/custom-fields/' . $id, 'ProductCustomField', $version);
        }
    }

    /**
     * Returns the total number of products in the collection.
     *
     * @param array $filter
     * @return int|string number of products or XML string if useXml is true
     */
    public static function getProductsCount($filter = array())
    {
        $filter = Filter::create($filter);
        return self::getCount('/products/count' . $filter->toQuery());
    }

    /**
     * Returns a single product resource by the given id.
     *
     * @param int $id product id
     * @param null $version
     * @return Resources\Product|string
     * @throws Exception
     */
    public static function getProduct($id, $version = null)
    {
        if (in_array($version, self::$available_versions)) {
            return self::getResource('/' . $id, 'Product', $version);
        } else {
            throw new Exception("'version' not available");
        }
    }

    /**
     * Create a new product.
     *
     * @param mixed $object fields to create
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function createProduct($object, $version = null)
    {
        return self::createResource('', $object, 'Product', $version);
    }

    /**
     * Update the given product.
     *
     * @param int $id product id
     * @param mixed $object fields to update
     * @param $version
     * @return mixed
     * @throws Exception
     */
    public static function updateProduct($id, $object, $version = null)
    {
        return self::updateResource('/' . $id, $object, 'Product', $version);
    }

    /**
     * Update the product Batch.
     *
     * @param int $id product id
     * @param mixed $object fields to update
     * @param $version
     * @return mixed
     * @throws Exception
     */
    public static function updateProducts($object, $version = null)
    {
        return self::updateResource('', $object, 'Product', $version);
    }

    /**
     * Delete the given product.
     *
     * @param int $id product id
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function deleteProduct($id, $version = null)
    {
        return self::deleteResource('/' . $id, 'Product', $version);
    }

    /**
     * Delete All products.
     *
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function deleteProducts($version = null)
    {
        return self::deleteResource('', 'Product', $version);
    }

    /**
     * Delete all products.
     *
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function deleteAllProducts($version = null)
    {
        return self::deleteResource('', 'Product', $version);
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
     * @return mixed
     */
    public static function createOption($object)
    {
        return self::createResource('/options', $object);
    }

    /**
     * Update the given option.
     *
     * @param int $id category id
     * @param mixed $object
     * @return mixed
     */
    public static function updateOption($id, $object)
    {
        return self::updateResource('/options/' . $id, $object);
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
     * @return Resources\Option
     */
    public static function getOption($id)
    {
        return self::getResource('/options/' . $id, 'Option');
    }

    /**
     * Delete the given option.
     *
     * @param int $id option id
     * @return mixed
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
     * @return Resources\OptionValue
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
     * @throws Exception
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
     * @return Resources\Category
     * @throws Exception
     */
    public static function getCategory($id)
    {
        return self::getResource('/categories/' . $id, 'Category');
    }

    /**
     * Create a new category from the given data.
     *
     * @param mixed $object
     * @return mixed
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
     * @return mixed
     */
    public static function updateCategory($id, $object)
    {
        return self::updateResource('/categories/' . $id, $object);
    }

    /**
     * Delete the given category.
     *
     * @param int $id category id
     * @return mixed
     */
    public static function deleteCategory($id)
    {
        return self::deleteResource('/categories/' . $id);
    }

    /**
     * Delete all categories.
     *
     * @return mixed
     */
    public static function deleteAllCategories()
    {
        return self::deleteResource('/categories');
    }

    /**
     * The collection of brands.
     *
     * @param array $filter
     * @param string $version
     * @return array
     * @throws Exception
     */
    public static function getBrands($filter = array(), $version = null)
    {
        $filter = Filter::create($filter);
        if (in_array($version, self::$available_versions)) {
            return self::getCollection($filter->toQuery(), 'Brand', $version);
        } else {
            throw new Exception("'version' not available");
        }
    }

    /**
     * The collection of Brand Metafields.
     *
     * @param int $id Brand Id
     * @param array $filter
     * @param string $version
     * @return array
     * @throws Exception
     */
    public static function getBrandMetafields($id, $filter = array(), $version = null)
    {
        $filter = Filter::create($filter);
        if (in_array($version, self::$available_versions)) {
            return self::getCollection('/'.$id.'/metafields'.$filter->toQuery(), 'Brand', $version);
        } else {
            throw new Exception("'version' not available");
        }
    }

    /**
     * The total number of brands in the collection.
     *
     * @param array $filter
     * @param null $version
     * @return int
     * @throws Exception
     */
    public static function getBrandsCount($filter = array(), $version = null)
    {
        $filter = Filter::create($filter);
        if (in_array($version, self::$available_versions)) {
            $path = ($version != "v3" and self::$version != "v3")?'/count':'';
            return self::getCount($path . $filter->toQuery(), "Brand", $version);
        } else {
            throw new Exception("'version' not available");
        }
    }

    /**
     * A single brand by given id.
     *
     * @param int $id brand id
     * @param string $version
     * @return Resources\Brand
     * @throws Exception
     */
    public static function getBrand($id, $version = null)
    {
        if (in_array($version, self::$available_versions)) {
            return self::getResource("/".$id, 'Brand', $version);
        } else {
            throw new Exception("'version' not available");
        }
    }

    /**
     * A single brand Metafield by given brnad id and Metafield id.
     *
     * @param $brand_id
     * @param $metafield_id
     * @param array $filter
     * @param string $version
     * @return Resources\Brand
     * @throws Exception
     */
    public static function getBrandMetafield($brand_id, $metafield_id, $filter = array(), $version = null)
    {
        $filter = Filter::create($filter);
        if (in_array($version, self::$available_versions)) {
            return self::getResource("/".$brand_id.'/metafields/'.$metafield_id.$filter->toQuery(), 'Brand', $version);
        } else {
            throw new Exception("'version' not available");
        }
    }

    /**
     * Create a new brand from the given data.
     *
     * @param mixed $object
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function createBrand($object, $version = null)
    {
        if (in_array($version, self::$available_versions)) {
            return self::createResource('', $object, "Brand", $version);
        } else {
            throw new Exception("'version' not available");
        }
    }

    /**
     * Create a new brand from the given data.
     *
     * @param $id
     * @param mixed $object
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function createBrandMetafield($id, $object, $version = null)
    {
        if (in_array($version, self::$available_versions)) {
            return self::createResource('/'.$id.'/metafields', $object, "Brand", $version);
        } else {
            throw new Exception("'version' not available");
        }
    }

    /**
     * Update the given brand.
     *
     * @param int $id brand id
     * @param mixed $object
     * @param null|string $version
     * @return mixed
     * @throws Exception
     */
    public static function updateBrand($id, $object, $version = null)
    {
        if (in_array($version, self::$available_versions)) {
            return self::updateResource('/' . $id, $object, "Brand", $version);
        } else {
            throw new Exception("'version' not available");
        }
    }

    /**
     * Update single brand Metafield by given brand id and Metafield id.
     *
     * @param int $brand_id
     * @param int $metafield_id
     * @param array|object $object
     * @param string $version
     * @return Resources\Brand
     * @throws Exception
     */
    public static function updateBrandMetafield($brand_id, $metafield_id, $object, $version = null)
    {
        if (in_array($version, self::$available_versions)) {
            return self::updateResource("/".$brand_id.'/metafields/'.$metafield_id, $object, 'Brand', $version);
        } else {
            throw new Exception("'version' not available");
        }
    }

    /**
     * Delete the given brand.
     *
     * @param int $id brand id
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function deleteBrand($id, $version = null)
    {
        if (in_array($version, self::$available_versions)) {
            return self::deleteResource('/' . $id, "Brand", $version);
        } else {
            throw new Exception("'version' not available");
        }
    }

    /**
     * Delete single brand Metafield by given brand id and Metafield id.
     *
     * @param int $brand_id
     * @param int $metafield_id
     * @param string $version
     * @return Resources\Brand
     * @throws Exception
     */
    public static function deleteBrandMetafield($brand_id, $metafield_id, $version = null)
    {
        if (in_array($version, self::$available_versions)) {
            return self::deleteResource("/".$brand_id.'/metafields/'.$metafield_id, 'Brand', $version);
        } else {
            throw new Exception("'version' not available");
        }
    }

    /**
     * Delete all brands.
     *
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function deleteAllBrands($version = null)
    {
        if (in_array(self::$version, self::$available_versions)) {
            return self::deleteResource('', 'Brand', $version);
        } else {
            throw new Exception("'version' not available");
        }
    }

    /**
     * Get a cart from cart id.
     *
     * @param $id
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function getCart($id, $version = null)
    {
        if (in_array($version, self::$available_versions)) {
            return self::getResource("/".$id, 'Cart', $version);
        } else {
            throw new Exception("'version' not available");
        }
    }

    /**
     * Create a new cart from the given data.
     *
     * @param mixed $object
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function createCart($object, $version = null)
    {
        if (in_array($version, self::$available_versions)) {
            return self::createResource('', $object, "Cart", $version);
        } else {
            throw new Exception("'version' not available");
        }
    }

    /**
     * Create a Line items for existing cart from the given data.
     *
     * @param $id
     * @param mixed $object
     * @param array $filter
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function createCartLineItems($id, $object, $filter = array(), $version = null)
    {
        $filter = Filter::create($filter);
        if (in_array($version, self::$available_versions)) {
            return self::createResource('/'.$id.'/items'.$filter->toQuery(), $object, "Cart", $version);
        } else {
            throw new Exception("'version' not available");
        }
    }

    /**
     * update cart customer id.
     *
     * @param $cart_id
     * @param $customer_id
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function updateCartCustomerId($cart_id, $customer_id, $version = null)
    {
        if (in_array($version, self::$available_versions)) {
            return self::updateResource("/".$cart_id, array("customer_id"=>$customer_id), 'Cart', $version);
        } else {
            throw new Exception("'version' not available");
        }
    }

    /**
     * Update a Line items for existing cart from the given data.
     *
     * @param $cart_id
     * @param $line_item_id
     * @param array|object $object
     * @param array $filter
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function updateCartLineItem($cart_id, $line_item_id, $object, $filter = array(), $version = null)
    {
        $filter = Filter::create($filter);
        if (in_array($version, self::$available_versions)) {
            return self::updateResource('/'.$cart_id.'/items/'.$line_item_id.$filter->toQuery(), $object, "Cart", $version);
        } else {
            throw new Exception("'version' not available");
        }
    }

    /**
     * Delete a Cart by Cart Id
     *
     * @param $id
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function deleteCart($id, $version = null)
    {
        if (in_array($version, self::$available_versions)) {
            return self::deleteResource('/'.$id, "Cart", $version);
        } else {
            throw new Exception("'version' not available");
        }
    }

    /**
     * Delete a Cart Line Item by Cart Id and Line Item Id
     *
     * @param $cart_id
     * @param $line_item_id
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function deleteCartLineItem($cart_id, $line_item_id, $version = null)
    {
        if (in_array($version, self::$available_versions)) {
            return self::deleteResource('/'.$cart_id.'/items/'.$line_item_id, "Cart", $version);
        } else {
            throw new Exception("'version' not available");
        }
    }

    /**
     * Get All Wishlists.
     *
     * @param array $filter
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function getWishlists($filter = array(), $version = null)
    {
        $filter = Filter::create($filter);
        if (in_array($version, self::$available_versions)) {
            return self::getCollection($filter->toQuery(), 'Wishlist', $version);
        } else {
            throw new Exception("'version' not available");
        }
    }

    /**
     * Get Wishlist by Wishlist Id.
     *
     * @param $id
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function getWishlist($id, $version = null)
    {
        if (in_array($version, self::$available_versions)) {
            return self::getResource("/".$id, 'Wishlist', $version);
        } else {
            throw new Exception("'version' not available");
        }
    }

    /**
     * Get Wishlist by Wishlist Id.
     *
     * @param array|object $object
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function createWishlist($object, $version = null)
    {
        if (in_array($version, self::$available_versions)) {
            return self::createResource("", $object, 'Wishlist', $version);
        } else {
            throw new Exception("'version' not available");
        }
    }

    /**
     * Get Wishlist by Wishlist Id.
     *
     * @param $id
     * @param array|object $object
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function createWishlistItems($id, $object, $version = null)
    {
        if (in_array($version, self::$available_versions)) {
            return self::createResource("/".$id."/items", $object, 'Wishlist', $version);
        } else {
            throw new Exception("'version' not available");
        }
    }

    /**
     * Get Wishlist by Wishlist Id.
     *
     * @param $id
     * @param array|object $object
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function updateWishlist($id, $object, $version = null)
    {
        if (in_array($version, self::$available_versions)) {
            return self::updateResource("/".$id, $object, 'Wishlist', $version);
        } else {
            throw new Exception("'version' not available");
        }
    }

    /**
     * Get Wishlist by Wishlist Id.
     *
     * @param $id
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function deleteWishlist($id, $version = null)
    {
        if (in_array($version, self::$available_versions)) {
            return self::deleteResource("/".$id, 'Wishlist', $version);
        } else {
            throw new Exception("'version' not available");
        }
    }

    /**
     * Get Wishlist by Wishlist Id.
     *
     * @param $wishlist_id
     * @param $item_id
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function deleteWishlistItem($wishlist_id, $item_id, $version = null)
    {
        if (in_array($version, self::$available_versions)) {
            return self::deleteResource("/".$wishlist_id."/items/".$item_id, 'Wishlist', $version);
        } else {
            throw new Exception("'version' not available");
        }
    }

    /**
     * The collection of orders.
     *
     * @param array $filter
     * @return array
     * @throws Exception
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
     * The order count grouped by order status
     *
     * @param array $filter
     * @return Resources\OrderStatus
     * @throws Exception
     */
    public static function getOrderStatusesWithCounts($filter = array())
    {
        $filter = Filter::create($filter);
        $resource = self::getResource('/orders/count' . $filter->toQuery(), "OrderStatus");
        return $resource->statuses;
    }

    /**
     * A single order.
     *
     * @param int $id order id
     * @return Resources\Order
     * @throws Exception
     */
    public static function getOrder($id)
    {
        return self::getResource('/orders/' . $id, 'Order');
    }

    /**
     * @param $orderID
     * @return mixed
     * @throws Exception
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
     * @return mixed
     * @throws Exception
     */
    public static function deleteOrder($id)
    {
        return self::deleteResource('/orders/' . $id);
    }

    /**
     * Delete all orders.
     *
     * @return mixed
     */
    public static function deleteAllOrders()
    {
        return self::deleteResource('/orders');
    }

    /**
     * Create an order
     *
     * @param $object
     * @return mixed
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
     * @return mixed
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
     * @throws Exception
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
     * @return Resources\Customer
     */
    public static function getCustomer($id)
    {
        return self::getResource('/customers/' . $id, 'Customer');
    }

    /**
     * Create a new customer from the given data.
     *
     * @param mixed $object
     * @return mixed
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
     * @return mixed
     */
    public static function updateCustomer($id, $object)
    {
        return self::updateResource('/customers/' . $id, $object);
    }

    /**
     * Delete the given customer.
     *
     * @param int $id customer id
     * @return mixed
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
     * @return mixed
     */
    public static function createOptionSet($object)
    {
        return self::createResource('/optionsets', $object);
    }

    /**
     * Create Option Set Options
     *
     * @param $object
     * @param $id
     * @return mixed
     */
    public static function createOptionSetOption($object, $id)
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
     * @return Resources\OptionSet
     */
    public static function getOptionSet($id)
    {
        return self::getResource('/optionsets/' . $id, 'OptionSet');
    }

    /**
     * Update the given option set.
     *
     * @param int $id option set id
     * @param mixed $object
     * @return mixed
     */
    public static function updateOptionSet($id, $object)
    {
        return self::updateResource('/optionsets/' . $id, $object);
    }

    /**
     * Delete the given option set.
     *
     * @param int $id option id
     * @return mixed
     */
    public static function deleteOptionSet($id)
    {
        Client::deleteResource('/optionsets/' . $id);
    }

    /**
     * Status code used to represent the state of an order.
     *
     * @param int $id order status id
     *
     * @return mixed
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
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function getSkus($filter = array(), $version = null)
    {
        $filter = Filter::create($filter);
        return self::getCollection('/skus' . $filter->toQuery(), 'Sku', $version);
    }

    /**
     * Get collection of product skus
     *
     * @param $id
     * @param array $filter
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function getProductSkus($id, $filter = array(), $version = null)
    {
        $filter = Filter::create($filter);
        return self::getCollection('/'.$id.'/skus' . $filter->toQuery(), 'Sku', $version);
    }

    /**
     * Create a SKU
     *
     * @param $productId
     * @param $object
     * @return mixed
     */
    public static function createSku($productId, $object)
    {
        return self::createResource('/products/' . $productId . '/skus', $object);
    }

    /**
     * Update sku
     *
     * @param $id
     * @param $object
     * @return mixed
     */
    public static function updateSku($id, $object)
    {
        return self::updateResource('/products/skus/' . $id, $object);
    }

    /**
     * Returns the total number of SKUs in the collection.
     *
     * @return int
     */
    public static function getSkusCount()
    {
        return self::getCount('/products/skus/count');
    }

    /**
     * Returns the googleproductsearch mapping for a product.
     *
     * @return Resources\ProductGoogleProductSearch
     */
    public static function getGoogleProductSearch($productId)
    {
        return self::getResource('/products/' . $productId . '/googleproductsearch', 'ProductGoogleProductSearch');
    }

    /**
     * Get a single coupon by given id.
     *
     * @param int $id customer id
     * @return Resources\Coupon
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
     * @return mixed
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
     * @return mixed
     */
    public static function updateCoupon($id, $object)
    {
        return self::updateResource('/coupons/' . $id, $object);
    }

    /**
     * Delete the given coupon.
     *
     * @param int $id coupon id
     * @return mixed
     */
    public static function deleteCoupon($id)
    {
        return self::deleteResource('/coupons/' . $id);
    }

    /**
     * Delete all Coupons.
     *
     * @return mixed
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
        $limit = self::connection()->getHeader('X-Rate-Limit-Requests-Left');

        if (!$limit) {
            $result = self::getTime();

            if (!$result) {
                return false;
            }

            $limit = self::connection()->getHeader('X-Rate-Limit-Requests-Left');
        }

        return (int)$limit;
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
     * @return mixed
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
     * @return mixed
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
     * @return mixed
     */
    public static function deleteShipment($orderID, $shipmentID)
    {
        return self::deleteResource('/orders/' . $orderID . '/shipments/' . $shipmentID);
    }

    /**
     * Delete all Shipments for the given order.
     *
     * @param $orderID
     * @return mixed
     */
    public static function deleteAllShipmentsForOrder($orderID)
    {
        return self::deleteResource('/orders/' . $orderID . '/shipments');
    }

    /**
     * Get order coupons for a given order
     *
     * @param $orderID
     * @param array $filter
     * @return mixed
     */
    public static function getOrderCoupons($orderID, $filter = array())
    {
        $filter = Filter::create($filter);
        return self::getCollection('/orders/' . $orderID . '/coupons' . $filter->toQuery(), 'OrderCoupons');
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

    /**
     * Create a new currency.
     *
     * @param mixed $object fields to create
     * @return mixed
     */
    public static function createCurrency($object)
    {
        return self::createResource('/currencies', $object);
    }

    /**
     * Returns a single currency resource by the given id.
     *
     * @param int $id currency id
     * @return Resources\Currency|string
     */
    public static function getCurrency($id)
    {
        return self::getResource('/currencies/' . $id, 'Currency');
    }

    /**
     * Update the given currency.
     *
     * @param int $id currency id
     * @param mixed $object fields to update
     * @return mixed
     */
    public static function updateCurrency($id, $object)
    {
        return self::updateResource('/currencies/' . $id, $object);
    }

    /**
     * Delete the given currency.
     *
     * @param int $id currency id
     * @return mixed
     */
    public static function deleteCurrency($id)
    {
        return self::deleteResource('/currencies/' . $id);
    }

    /**
     * Returns the default collection of currencies.
     *
     * @param array $filter
     * @return mixed array|string list of currencies or XML string if useXml is true
     */
    public static function getCurrencies($filter = array())
    {
        $filter = Filter::create($filter);
        return self::getCollection('/currencies' . $filter->toQuery(), 'Currency');
    }

    /**
     * Create a new product image.
     *
     * @param string $productId
     * @param mixed $object
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function createProductImage($productId, $object, $version = null)
    {
        return self::createResource('/' . $productId . '/images', $object, 'ProductImage', $version);
    }

    /**
     * Update a product image.
     *
     * @param string $productId
     * @param string $imageId
     * @param mixed $object
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function updateProductImage($productId, $imageId, $object, $version = null)
    {
        return self::updateResource('/' . $productId . '/images/' . $imageId, $object, 'ProductImage', $version);
    }

    /**
     * Returns a product image resource by the given product id.
     *
     * @param int $productId
     * @param int $imageId
     * @param null $version
     * @return Resources\ProductImage|string
     * @throws Exception
     */
    public static function getProductImage($productId, $imageId, $version = null)
    {
        return self::getResource('/' . $productId . '/images/' . $imageId, 'ProductImage', $version);
    }

    /**
     * Delete the given product image.
     *
     * @param int $productId
     * @param int $imageId
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function deleteProductImage($productId, $imageId, $version = null)
    {
        return self::deleteResource('/' . $productId . '/images/' . $imageId, 'ProductImage', $version);
    }

    /**
     * Returns a product videos resource by the given product id.
     *
     * @param $id
     * @param null $version
     * @return Resources\ProductImage|string
     * @throws Exception
     */
    public static function getProductVideos($id, $filter = array(), $version = null)
    {
        $filter = Filter::create($filter);
        return self::getCollection('/' . $id . '/videos'.$filter->toQuery(), 'ProductVideo', $version);
    }

    /**
     * Create a product videos resource by the given product id.
     *
     * @param $id
     * @param $object
     * @param null $version
     * @return Resources\ProductImage|string
     * @throws Exception
     */
    public static function createProductVideo($id, $object, $version = null)
    {
        return self::createResource('/' . $id . '/videos', $object, 'ProductVideo', $version);
    }

    /**
     * Returns a product videos resource by the given product id.
     *
     * @param $product_id
     * @param $id
     * @param null $version
     * @return Resources\ProductImage|string
     * @throws Exception
     */
    public static function getProductVideo($product_id, $id, $version = null)
    {
        return self::getResource('/' . $product_id . '/videos/'.$id, 'ProductVideo', $version);
    }

    /**
     * Update a product videos resource by the given product id.
     *
     * @param $product_id
     * @param $id
     * @param $object
     * @param null $version
     * @return Resources\ProductImage|string
     * @throws Exception
     */
    public static function updateProductVideo($product_id, $id, $object, $version = null)
    {
        return self::updateResource('/' . $product_id . '/videos/'.$id, $object, 'ProductVideo', $version);
    }

    /**
     * Delete a product videos resource by the given product id.
     *
     * @param $product_id
     * @param $id
     * @param null $version
     * @return Resources\ProductImage|string
     * @throws Exception
     */
    public static function deleteProductVideo($product_id, $id, $version = null)
    {
        return self::deleteResource('/' . $product_id . '/videos/'.$id, 'ProductVideo', $version);
    }

    /**
     * Returns a product bulk pricing rules resource by the given product id.
     *
     * @param $id
     * @param array $filter
     * @param string $version
     * @return Resources\ProductBulkPricingRule|string
     * @throws Exception
     */
    public static function getProductBulkPricingRules($id, $filter = array(), $version = "v3")
    {
        $filter = Filter::create($filter);
        $temp_object =  self::getCollection('/' . $id . '/bulk-pricing-rules'.$filter->toQuery(), 'ProductBulkPricingRule', $version);
        if (gettype($temp_object) == "object") {
            foreach ($temp_object as $obj) {
                $obj->product_id = $id;
            }
        }
        return $temp_object;
    }

    /**
     * Returns a product bulk pricing rule resource by the given product id.
     *
     * @param $product_id
     * @param $id
     * @param array $filter
     * @param string $version
     * @return Resources\ProductBulkPricingRule|string
     * @throws Exception
     */
    public static function getProductBulkPricingRule($product_id, $id, $filter = array(), $version = "v3")
    {
        $filter = Filter::create($filter);
        $temp_object = self::getResource('/' . $product_id . '/bulk-pricing-rules/'.$id.$filter->toQuery(), 'ProductBulkPricingRule', $version);
        if (gettype($temp_object) == "object") {
            $temp_object->product_id = $product_id;
        }
        return $temp_object;
    }

    /**
     * Create a Bulk Pricing Rule resource by the given product id.
     *
     * @param $id
     * @param $object
     * @param array $filter
     * @param null $version
     * @return Resources\ProductImage|string
     * @throws Exception
     */
    public static function createProductBulkPricingRule($id, $object, $filter = array(), $version = "v3")
    {
        $filter = Filter::create($filter);
        return self::createResource('/' . $id . '/bulk-pricing-rules'.$filter->toQuery(), $object, 'ProductBulkPricingRule', $version);
    }

    /**
     * Update a Bulk Pricing Rule resource by the given product id.
     *
     * @param $product_id
     * @param $id
     * @param $object
     * @param string $version
     * @return Resources\ProductImage|string
     * @throws Exception
     */
    public static function updateProductBulkPricingRule($product_id, $id, $object, $version = "v3")
    {
        return self::updateResource('/' . $product_id . '/bulk-pricing-rules/'.$id, $object, 'ProductBulkPricingRule', $version);
    }

    /**
     * Update a Bulk Pricing Rule resource by the given product id.
     *
     * @param $product_id
     * @param $id
     * @param string $version
     * @return Resources\ProductImage|string
     * @throws Exception
     */
    public static function deleteProductBulkPricingRule($product_id, $id, $version = "v3")
    {
        return self::deleteResource('/' . $product_id . '/bulk-pricing-rules/'.$id, 'ProductBulkPricingRule', $version);
    }

    /**
     * Returns a product bulk pricing rules resource by the given product id.
     *
     * @param $id
     * @param array $filter
     * @param string $version
     * @return Resources\ProductBulkPricingRule|string
     * @throws Exception
     */
    public static function getProductComplexRules($id, $filter = array(), $version = "v3")
    {
        $filter = Filter::create($filter);
        return self::getCollection('/' . $id . '/complex-rules'.$filter->toQuery(), 'ProductComplexRule', $version);
    }

    /**
     * Returns a product bulk pricing rule resource by the given product id.
     *
     * @param $product_id
     * @param $id
     * @param array $filter
     * @param string $version
     * @return Resources\ProductBulkPricingRule|string
     * @throws Exception
     */
    public static function getProductComplexRule($product_id, $id, $filter = array(), $version = "v3")
    {
        $filter = Filter::create($filter);
        return self::getResource('/' . $product_id . '/complex-rules/'.$id.$filter->toQuery(), 'ProductComplexRule', $version);
    }

    /**
     * Create a Complex Rule resource by the given product id.
     *
     * @param $id
     * @param $object
     * @param array $filter
     * @param string $version
     * @return Resources\ProductImage|string
     * @throws Exception
     */
    public static function createProductComplexRule($id, $object, $version = "v3")
    {
        return self::createResource('/' . $id . '/complex-rules', $object, 'ProductComplexRule', $version);
    }

    /**
     * Update a Complex Rule resource by the given product id.
     *
     * @param $product_id
     * @param $id
     * @param $object
     * @param string $version
     * @return Resources\ProductImage|string
     * @throws Exception
     */
    public static function updateProductComplexRule($product_id, $id, $object, $version = "v3")
    {
        return self::updateResource('/' . $product_id . '/complex-rules/'.$id, $object, 'ProductComplexRule', $version);
    }

    /**
     * Update a Complex Rule resource by the given product id.
     *
     * @param $product_id
     * @param $id
     * @param string $version
     * @return Resources\ProductImage|string
     * @throws Exception
     */
    public static function deleteProductComplexRule($product_id, $id, $version = "v3")
    {
        return self::deleteResource('/' . $product_id . '/complex-rules/'.$id, 'ProductComplexRule', $version);
    }

    /**
     * Returns a product variants resource by the given product id.
     *
     * @param $id
     * @param array $filter
     * @param string $version
     * @return Resources\ProductBulkPricingRule|string
     * @throws Exception
     */
    public static function getProductVariants($id, $filter = array(), $version = "v3")
    {
        $filter = Filter::create($filter);
        return self::getCollection('/' . $id . '/variants'.$filter->toQuery(), 'ProductVariant', $version);
    }

    /**
     * Returns a product variant resource by the given product id.
     *
     * @param $product_id
     * @param $id
     * @param array $filter
     * @param string $version
     * @return Resources\ProductBulkPricingRule|string
     * @throws Exception
     */
    public static function getProductVariant($product_id, $id, $filter = array(), $version = "v3")
    {
        $filter = Filter::create($filter);
        return self::getResource('/' . $product_id . '/variants/'.$id.$filter->toQuery(), 'ProductVariant', $version);
    }

    /**
     * Returns a product variant resource by the given product id.
     *
     * @param $product_id
     * @param $object
     * @param string $version
     * @return Resources\ProductBulkPricingRule|string
     * @throws Exception
     */
    public static function createProductVariant($product_id, $object, $version = "v3")
    {
        return self::createResource('/' . $product_id . '/variants', $object, 'ProductVariant', $version);
    }

    /**
     * Returns a product variant resource by the given product id.
     *
     * @param $product_id
     * @param $id
     * @param $image_url
     * @param string $version
     * @return Resources\ProductBulkPricingRule|string
     * @throws Exception
     */
    public static function createProductVariantImage($product_id, $id, $image_url, $version = "v3")
    {
        return self::createResource('/' . $product_id . '/variants/'.$id.'/image', array("image_url" => $image_url), 'ProductVariant', $version);
    }

    /**
     * Update a product variant resource by the given product id.
     *
     * @param $product_id
     * @param $id
     * @param $object
     * @param string $version
     * @return Resources\ProductBulkPricingRule|string
     * @throws Exception
     */
    public static function updateProductVariant($product_id, $id, $object, $version = "v3")
    {
        return self::updateResource('/' . $product_id . '/variants/'.$id, $object, 'ProductVariant', $version);
    }

    /**
     * Delete a product variant resource by the given product id.
     *
     * @param $product_id
     * @param $id
     * @param string $version
     * @return Resources\ProductBulkPricingRule|string
     * @throws Exception
     */
    public static function deleteProductVariant($product_id, $id, $version = "v3")
    {
        return self::deleteResource('/' . $product_id . '/variants/'.$id, 'ProductVariant', $version);
    }

    /**
     * Returns a product variants resource by the given product id.
     *
     * @param $product_id
     * @param $variant_id
     * @param array $filter
     * @param string $version
     * @return Resources\ProductBulkPricingRule|string
     * @throws Exception
     */
    public static function getProductVariantMetafields($product_id, $variant_id, $filter = array(), $version = "v3")
    {
        $filter = Filter::create($filter);
        $temp_object = self::getCollection('/' . $product_id . '/variants/'.$variant_id.'/metafields'.$filter->toQuery(), 'ProductVariantMetafield', $version);
        if (gettype($temp_object) == "object") {
            foreach ($temp_object as $obj) {
                $obj->product_id = $product_id;
            }
        }
        return $temp_object;
    }

    /**
     * Returns a product variant resource by the given product id.
     *
     * @param $product_id
     * @param $id
     * @param array $filter
     * @param string $version
     * @return Resources\ProductBulkPricingRule|string
     * @throws Exception
     */
    public static function getProductVariantMetafield($product_id, $variant_id, $id, $filter = array(), $version = "v3")
    {
        $filter = Filter::create($filter);
        $temp_object = self::getResource('/' . $product_id . '/variants/'.$variant_id.'/metafields/'.$id.$filter->toQuery(), 'ProductVariantMetafield', $version);
        if (gettype($temp_object) == "object") {
            $temp_object->product_id = $product_id;
        }
        return $temp_object;
    }

    /**
     * Returns a product variant resource by the given product id.
     *
     * @param $product_id
     * @param $variant_id
     * @param $object
     * @param string $version
     * @return Resources\ProductBulkPricingRule|string
     * @throws Exception
     */
    public static function createProductVariantMetafield($product_id, $variant_id, $object, $version = "v3")
    {
        return self::createResource('/' . $product_id . '/variants/'.$variant_id.'/metafields', $object, 'ProductVariantMetafield', $version);
    }

    /**
     * Returns a product variant resource by the given product id.
     *
     * @param $product_id
     * @param $variant_id
     * @param $id
     * @param $object
     * @param string $version
     * @return Resources\ProductBulkPricingRule|string
     * @throws Exception
     */
    public static function updateProductVariantMetafield($product_id, $variant_id, $id, $object, $version = "v3")
    {
        return self::updateResource('/' . $product_id . '/variants/'.$variant_id.'/metafields/'.$id, $object, 'ProductVariantMetafield', $version);
    }

    /**
     * Returns a product variant resource by the given product id.
     *
     * @param $product_id
     * @param $variant_id
     * @param $id
     * @param string $version
     * @return Resources\ProductBulkPricingRule|string
     * @throws Exception
     */
    public static function deleteProductVariantMetafield($product_id, $variant_id, $id, $version = "v3")
    {
        return self::deleteResource('/' . $product_id . '/variants/'.$variant_id.'/metafields/'.$id, 'ProductVariantMetafield', $version);
    }

    /**
     * Get all content pages
     *
     * @return mixed
     */
    public static function getPages()
    {
        return self::getCollection('/pages', 'Page');
    }

    /**
     * Get single content pages
     *
     * @param int $pageId
     * @return mixed
     */
    public static function getPage($pageId)
    {
        return self::getResource('/pages/' . $pageId, 'Page');
    }

    /**
     * Create a new content pages
     *
     * @param $object
     * @return mixed
     */
    public static function createPage($object)
    {
        return self::createResource('/pages', $object);
    }

    /**
     * Update an existing content page
     *
     * @param int $pageId
     * @param $object
     * @return mixed
     */
    public static function updatePage($pageId, $object)
    {
        return self::updateResource('/pages/' . $pageId, $object);
    }

    /**
     * Delete an existing content page
     *
     * @param int $pageId
     * @return mixed
     */
    public static function deletePage($pageId)
    {
        return self::deleteResource('/pages/' . $pageId);
    }

    /**
     * Create a Gift Certificate
     *
     * @param array $object
     * @return mixed
     */
    public static function createGiftCertificate($object)
    {
        return self::createResource('/gift_certificates', $object);
    }

    /**
     * Get a Gift Certificate
     *
     * @param int $giftCertificateId
     * @return mixed
     */
    public static function getGiftCertificate($giftCertificateId)
    {
        return self::getResource('/gift_certificates/' . $giftCertificateId);
    }

    /**
     * Return the collection of all gift certificates.
     *
     * @param array $filter
     * @return mixed
     */
    public static function getGiftCertificates($filter = array())
    {
        $filter = Filter::create($filter);
        return self::getCollection('/gift_certificates' . $filter->toQuery());
    }

    /**
     * Update a Gift Certificate
     *
     * @param int $giftCertificateId
     * @param array $object
     * @return mixed
     */
    public static function updateGiftCertificate($giftCertificateId, $object)
    {
        return self::updateResource('/gift_certificates/' . $giftCertificateId, $object);
    }

    /**
     * Delete a Gift Certificate
     *
     * @param int $giftCertificateId
     * @return mixed
     */
    public static function deleteGiftCertificate($giftCertificateId)
    {
        return self::deleteResource('/gift_certificates/' . $giftCertificateId);
    }

    /**
     * Delete all Gift Certificates
     *
     * @return mixed
     */
    public static function deleteAllGiftCertificates()
    {
        return self::deleteResource('/gift_certificates');
    }

    /**
     * Create Product Review
     *
     * @param int $productId
     * @param array $object
     * @return mixed
     */
    public static function createProductReview($productId, $object)
    {
        return self::createResource('/products/' . $productId . '/reviews', $object);
    }

    /**
     * Create Product Bulk Discount rules
     *
     * @param string $productId
     * @param array $object
     * @return mixed
     */
    public static function createProductBulkPricingRules($productId, $object)
    {
        return self::createResource('/products/' . $productId . '/discount_rules', $object);
    }

    /**
     * Create a Marketing Banner
     *
     * @param array $object
     * @return mixed
     */
    public static function createMarketingBanner($object)
    {
        return self::createResource('/banners', $object);
    }

    /**
     * Get all Marketing Banners
     *
     * @return mixed
     */
    public static function getMarketingBanners()
    {
        return self::getCollection('/banners');
    }

    /**
     * Delete all Marketing Banners
     *
     * @return mixed
     */
    public static function deleteAllMarketingBanners()
    {
        return self::deleteResource('/banners');
    }

    /**
     * Delete a specific Marketing Banner
     *
     * @param int $bannerID
     * @return mixed
     */
    public static function deleteMarketingBanner($bannerID)
    {
        return self::deleteResource('/banners/' . $bannerID);
    }

    /**
     * Update an existing banner
     *
     * @param int $bannerID
     * @param array $object
     * @return mixed
     */
    public static function updateMarketingBanner($bannerID, $object)
    {
        return self::updateResource('/banners/' . $bannerID, $object);
    }

    /**
     * Add a address to the customer's address book.
     *
     * @param int $customerID
     * @param array $object
     * @return mixed
     */
    public static function createCustomerAddress($customerID, $object)
    {
        return self::createResource('/customers/' . $customerID . '/addresses', $object);
    }

    /**
     * Create a product rule
     *
     * @param int $productID
     * @param array $object
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function createProductRule($productID, $object, $version = "v2")
    {
        return self::createResource('/' . $productID . '/rules', $object, 'Rule', $version);
    }

    /**
     * Create a customer group.
     *
     * @param array $object
     * @return mixed
     */
    public static function createCustomerGroup($object)
    {
        return self::createResource('/customer_groups', $object);
    }

    /**
     * Get list of customer groups
     *
     * @return mixed
     */
    public static function getCustomerGroups()
    {
        return self::getCollection('/customer_groups');
    }

    /**
     * Delete a customer group
     *
     * @param int $customerGroupId
     * @return mixed
     */
    public static function deleteCustomerGroup($customerGroupId)
    {
        return self::deleteResource('/customer_groups/' . $customerGroupId);
    }

    /**
     * Delete all customers
     *
     * @return mixed
     */
    public static function deleteAllCustomers()
    {
        return self::deleteResource('/customers');
    }

    /**
     * Delete all options
     *
     * @return mixed
     */
    public static function deleteAllOptions()
    {
        return self::deleteResource('/options');
    }

    /**
     * Return the collection of all option values for a given option.
     *
     * @param $id
     * @param array $filter
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function getProductOptions($id, $filter = array(), $version = null)
    {
        $filter = Filter::create($filter);
        return self::getCollection('/' . $id . '/options'.$filter->toQuery(), 'ProductOption', $version);
    }

    /**
     * Return the collection of all option values for a given option.
     *
     * @param $product_id
     * @param $id
     * @param array $filter
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function getProductOption($product_id, $id, $filter = array(), $version = null)
    {
        $filter = Filter::create($filter);
        return self::getResource('/' . $product_id . '/options/' . $id.$filter->toQuery(), 'ProductOption', $version);
    }

    /**
     * Return the collection of all option values for a given option.
     *
     * @param $product_id
     * @param $id
     * @param $object
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function createProductOption($product_id, $id, $object, $version = null)
    {
        return self::createResource('/' . $product_id . '/options/' . $id, $object, 'ProductOption', $version);
    }

    /**
     * Return the collection of all option values for a given option.
     *
     * @param $product_id
     * @param $id
     * @param $object
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function updateProductOption($product_id, $id, $object, $version = null)
    {
        return self::updateResource('/' . $product_id . '/options/' . $id, $object, 'ProductOption', $version);
    }

    /**
     * Return the collection of all option values for a given option.
     *
     * @param $product_id
     * @param $id
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function deleteProductOption($product_id, $id, $version = null)
    {
        return self::deleteResource('/' . $product_id . '/options/' . $id, 'ProductOption', $version);
    }

    /**
     * Return the collection of all option values for a given option.
     *
     * @param $product_id
     * @param $option_id
     * @param array $filter
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function getProductOptionValues($product_id, $option_id, $filter = array(), $version = null)
    {
        $filter = Filter::create($filter);
        $temp_object = self::getCollection('/' . $product_id . '/options/'.$option_id.'/values'.$filter->toQuery(), 'ProductOptionValue', $version);
        if (gettype($temp_object) == "object") {
            foreach ($temp_object as $obj) {
                $obj->product_id = $product_id;
                $obj->option_id = $option_id;
            }
        }
        return $temp_object;
    }

    /**
     * Return the collection of all option values for a given option.
     *
     * @param $product_id
     * @param $option_id
     * @param $id
     * @param array $filter
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function getProductOptionValue($product_id, $option_id, $id, $filter = array(), $version = null)
    {
        $filter = Filter::create($filter);
        $temp_object = self::getResource('/' . $product_id . '/options/'.$option_id.'/values/'.$id.$filter->toQuery(), 'ProductOptionValue', $version);
        if (gettype($temp_object) == "object") {
            $temp_object->product_id = $product_id;
            $temp_object->option_id = $option_id;
        }
        return $temp_object;
    }

    /**
     * Return the collection of all option values for a given option.
     *
     * @param $product_id
     * @param $id
     * @param $object
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function createProductOptionValue($product_id, $option_id, $object, $version = null)
    {
        return self::createResource('/' . $product_id . '/options/' . $option_id.'/values', $object, 'ProductOptionValue', $version);
    }

    /**
     * Return the collection of all option values for a given option.
     *
     * @param $product_id
     * @param $option_id
     * @param $id
     * @param $object
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function updateProductOptionValue($product_id, $option_id, $id, $object, $version = null)
    {
        return self::updateResource('/' . $product_id . '/options/' . $option_id.'/values/'.$id, $object, 'ProductOptionValue', $version);
    }

    /**
     * Return the collection of all option values for a given option.
     *
     * @param $product_id
     * @param $option_id
     * @param $id
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function deleteProductOptionValue($product_id, $option_id, $id, $version = null)
    {
        return self::deleteResource('/' . $product_id . '/options/' . $option_id.'/values/'.$id, 'ProductOptionValue', $version);
    }

    /**
     * Return the collection of all option values for a given option.
     *
     * @param int $productId
     * @param int $productRuleId
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function getProductRule($productId, $productRuleId, $version = "v2")
    {
        return self::getResource('/' . $productId . '/rules/' . $productRuleId, 'Rule', $version);
    }

    /**
     * Return the collection of all option values for a given option.
     *
     * @param int $productId
     * @param null $version
     * @return mixed
     * @throws Exception
     */
    public static function getProductRules($productId, $version = "v2")
    {
        return self::getResource('/' . $productId . '/rules', 'Rule', $version);
    }

    /**
     * Return the option value object that was created.
     *
     * @param int $optionId
     * @param array $object
     * @return mixed
     */
    public static function createOptionValue($optionId, $object)
    {
        return self::createResource('/options/' . $optionId . '/values', $object);
    }

    /**
     * Delete all option sets that were created.
     *
     * @return mixed
     */
    public static function deleteAllOptionSets()
    {
        return self::deleteResource('/optionsets');
    }

    /**
     * Return the option value object that was updated.
     *
     * @param int $optionId
     * @param int $optionValueId
     * @param array $object
     * @return mixed
     */
    public static function updateOptionValue($optionId, $optionValueId, $object)
    {
        return self::updateResource(
            '/options/' . $optionId . '/values/' . $optionValueId,
            $object
        );
    }

    /**
     * Returns all webhooks.
     *
     * @return mixed Resource|string resource object or XML string if useXml is true
     */
    public static function listWebhooks()
    {
        return self::getCollection('/hooks');
    }

    /**
     * Returns data for a specific web-hook.
     *
     * @param int $id
     * @return mixed Resource|string resource object or XML string if useXml is true
     */
    public static function getWebhook($id)
    {
        return self::getResource('/hooks/' . $id);
    }

    /**
     * Creates a web-hook.
     *
     * @param mixed $object object or XML string to create
     * @return mixed
     */
    public static function createWebhook($object)
    {
        return self::createResource('/hooks', $object);
    }

    /**
     * Updates the given webhook.
     *
     * @param int $id
     * @param mixed $object object or XML string to create
     * @return mixed
     */
    public static function updateWebhook($id, $object)
    {
        return self::updateResource('/hooks/' . $id, $object);
    }

    /**
     * Delete the given webhook.
     *
     * @param int $id
     * @return mixed
     */
    public static function deleteWebhook($id)
    {
        return self::deleteResource('/hooks/' . $id);
    }

    /**
     * Return a collection of shipping-zones
     *
     * @return mixed
     */
    public static function getShippingZones()
    {
        return self::getCollection('/shipping/zones', 'ShippingZone');
    }

    /**
     * Return a shipping-zone by id
     *
     * @param int $id shipping-zone id
     * @return mixed
     */
    public static function getShippingZone($id)
    {
        return self::getResource('/shipping/zones/' . $id, 'ShippingZone');
    }


    /**
     * Delete the given shipping-zone
     *
     * @param int $id shipping-zone id
     * @return mixed
     */
    public static function deleteShippingZone($id)
    {
        return self::deleteResource('/shipping/zones/' . $id);
    }

    /**
     * Return a shipping-method by id
     *
     * @param $zoneId
     * @param $methodId
     * @return mixed
     */
    public static function getShippingMethod($zoneId, $methodId)
    {
        return self::getResource('/shipping/zones/'. $zoneId . '/methods/'. $methodId, 'ShippingMethod');
    }

    /**
     * Return a collection of shipping-methods
     *
     * @param $zoneId
     * @return mixed
     */
    public static function getShippingMethods($zoneId)
    {
        return self::getCollection('/shipping/zones/' . $zoneId . '/methods', 'ShippingMethod');
    }


    /**
     * Delete the given shipping-method by id
     *
     * @param $zoneId
     * @param $methodId
     * @return mixed
     */
    public static function deleteShippingMethod($zoneId, $methodId)
    {
        return self::deleteResource('/shipping/zones/'. $zoneId . '/methods/'. $methodId);
    }

    /**
     * Get collection of product skus by Product
     *
     * @param $productId
     * @param array $filter
     * @param string $version
     * @return mixed
     * @throws Exception
     */
    public static function getSkusByProduct($productId, $filter = array(), $version = "v2")
    {
        $filter = Filter::create($filter);
        return self::getCollection('/'.$productId.'/skus' . $filter->toQuery(), 'Sku', $version);
    }
}
