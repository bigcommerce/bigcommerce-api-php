<?php
namespace Bigcommerce\Api;

class Connection
{
    private $curl;
    private $headers = array();
    private $responseHeaders = array();
    private $responseStatusLine;
    private $responseBody;
    private $failOnError = false;
    private $followLocation = false;
    private $maxRedirects = 20;
    private $redirectsFollowed = 0;
    private $lastError = false;
    private $errorCode;
    private $useXml = false;
    public function __construct()
    {
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_HEADERFUNCTION, array($this, 'parseHeader'));
        curl_setopt($this->curl, CURLOPT_WRITEFUNCTION, array($this, 'parseBody'));
        $this->setCipher('rsa_rc4_128_sha');
        if (!ini_get('open_basedir')) {
            curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
        } else {
            $this->followLocation = true;
        }
    }
    public function useXml($option = true)
    {
        $this->useXml = $option;
    }
    public function failOnError($option = true)
    {
        $this->failOnError = $option;
    }
    public function authenticate($username, $password)
    {
        curl_setopt($this->curl, CURLOPT_USERPWD, "{$username}:{$password}");
    }
    public function setTimeout($timeout)
    {
        curl_setopt($this->curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, $timeout);
    }
    public function useProxy($server, $port = false)
    {
        curl_setopt($this->curl, CURLOPT_PROXY, $server);
        if ($port) {
            curl_setopt($this->curl, CURLOPT_PROXYPORT, $port);
        }
    }
    public function verifyPeer($option = false)
    {
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, $option);
    }
    public function setCipher($cipher = 'rsa_rc4_128_sha')
    {
        curl_setopt($this->curl, CURLOPT_SSL_CIPHER_LIST, $cipher);
    }
    public function addHeader($header, $value)
    {
        $this->headers[$header] = "{$header}: {$value}";
    }
    private function getContentType()
    {
        return $this->useXml ? 'application/xml' : 'application/json';
    }
    private function initializeRequest()
    {
        $this->isComplete = false;
        $this->responseBody = '';
        $this->responseHeaders = array();
        $this->lastError = false;
        $this->addHeader('Accept', $this->getContentType());
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->headers);
    }
    private function handleResponse()
    {
        if (curl_errno($this->curl)) {
            throw new NetworkError(curl_error($this->curl), curl_errno($this->curl));
        }
        $body = $this->useXml ? $this->getBody() : json_decode($this->getBody());
        $status = $this->getStatus();
        if ($status >= 400 && $status <= 499) {
            if ($this->failOnError) {
                throw new ClientError($body, $status);
            } else {
                $this->lastError = $body;
                return false;
            }
        } elseif ($status >= 500 && $status <= 599) {
            if ($this->failOnError) {
                throw new ServerError($body, $status);
            } else {
                $this->lastError = $body;
                return false;
            }
        }
        if ($this->followLocation) {
            $this->followRedirectPath();
        }
        return $body;
    }
    public function getLastError()
    {
        return $this->lastError;
    }
    private function followRedirectPath()
    {
        $this->redirectsFollowed++;
        if ($this->getStatus() == 301 || $this->getStatus() == 302) {
            if ($this->redirectsFollowed < $this->maxRedirects) {
                $location = $this->getHeader('Location');
                $forwardTo = parse_url($location);
                if (isset($forwardTo['scheme']) && isset($forwardTo['host'])) {
                    $url = $location;
                } else {
                    $forwardFrom = parse_url(curl_getinfo($this->curl, CURLINFO_EFFECTIVE_URL));
                    $url = $forwardFrom['scheme'] . '://' . $forwardFrom['host'] . $location;
                }
                $this->get($url);
            } else {
                $errorString = 'Too many redirects when trying to follow location.';
                throw new NetworkError($errorString, CURLE_TOO_MANY_REDIRECTS);
            }
        } else {
            $this->redirectsFollowed = 0;
        }
    }
    public function get($url, $query = false)
    {
        $this->initializeRequest();
        if (is_array($query)) {
            $url .= '?' . http_build_query($query);
        }
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_HTTPGET, true);
        curl_exec($this->curl);
        return $this->handleResponse();
    }
    public function post($url, $body)
    {
        $this->addHeader('Content-Type', $this->getContentType());
        if (!is_string($body)) {
            $body = json_encode($body);
        }
        $this->initializeRequest();
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_POST, true);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $body);
        curl_exec($this->curl);
        return $this->handleResponse();
    }
    public function head($url)
    {
        $this->initializeRequest();
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'HEAD');
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_NOBODY, true);
        curl_exec($this->curl);
        return $this->handleResponse();
    }
    public function put($url, $body)
    {
        $this->addHeader('Content-Type', $this->getContentType());
        if (!is_string($body)) {
            $body = json_encode($body);
        }
        $this->initializeRequest();
        $handle = tmpfile();
        fwrite($handle, $body);
        fseek($handle, 0);
        curl_setopt($this->curl, CURLOPT_INFILE, $handle);
        curl_setopt($this->curl, CURLOPT_INFILESIZE, strlen($body));
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_PUT, true);
        curl_exec($this->curl);
        return $this->handleResponse();
    }
    public function delete($url)
    {
        $this->initializeRequest();
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_exec($this->curl);
        return $this->handleResponse();
    }
    private function parseBody($curl, $body)
    {
        $this->responseBody .= $body;
        return strlen($body);
    }
    private function parseHeader($curl, $headers)
    {
        if (!$this->responseStatusLine && strpos($headers, 'HTTP/') === 0) {
            $this->responseStatusLine = $headers;
        } else {
            $parts = explode(': ', $headers);
            if (isset($parts[1])) {
                $this->responseHeaders[$parts[0]] = trim($parts[1]);
            }
        }
        return strlen($headers);
    }
    public function getStatus()
    {
        return curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
    }
    public function getStatusMessage()
    {
        return $this->responseStatusLine;
    }
    public function getBody()
    {
        return $this->responseBody;
    }
    public function getHeader($header)
    {
        if (array_key_exists($header, $this->responseHeaders)) {
            return $this->responseHeaders[$header];
        }
    }
    public function getHeaders()
    {
        return $this->responseHeaders;
    }
    public function __destruct()
    {
        curl_close($this->curl);
    }
}
namespace Bigcommerce\Api;

class Error extends \Exception
{
    public function __construct($message, $code)
    {
        if (is_array($message)) {
            $message = $message[0]->message;
        }
        parent::__construct($message, $code);
    }
}
namespace Bigcommerce\Api;

class ClientError extends Error
{
    public function __toString()
    {
        return "Client Error ({$this->code}): " . $this->message;
    }
}
namespace Bigcommerce\Api;

class ServerError extends Error
{
    
}
namespace Bigcommerce\Api;

class NetworkError extends Error
{
    
}
namespace Bigcommerce\Api;

use Exception;
class Client
{
    private static $store_url;
    private static $username;
    private static $api_key;
    private static $connection;
    private static $resource;
    private static $path_prefix = '/api/v2';
    public static $api_path;
    public static function configure($settings)
    {
        if (!isset($settings['store_url'])) {
            throw new Exception('\'store_url\' must be provided');
        }
        if (!isset($settings['username'])) {
            throw new Exception('\'username\' must be provided');
        }
        if (!isset($settings['api_key'])) {
            throw new Exception('\'api_key\' must be provided');
        }
        self::$username = $settings['username'];
        self::$api_key = $settings['api_key'];
        self::$store_url = rtrim($settings['store_url'], '/');
        self::$api_path = self::$store_url . self::$path_prefix;
        self::$connection = false;
    }
    public static function failOnError($option = true)
    {
        self::connection()->failOnError($option);
    }
    public static function useXml()
    {
        self::connection()->useXml();
    }
    public static function verifyPeer($option = false)
    {
        self::connection()->verifyPeer($option);
    }
    public static function setCipher($cipher = 'rsa_rc4_128_sha')
    {
        self::connection()->setCipher($cipher);
    }
    public static function useProxy($host, $port = false)
    {
        self::connection()->useProxy($host, $port);
    }
    public static function getLastError()
    {
        return self::connection()->getLastError();
    }
    private static function connection()
    {
        if (!self::$connection) {
            self::$connection = new Connection();
            self::$connection->authenticate(self::$username, self::$api_key);
        }
        return self::$connection;
    }
    public static function getCollection($path, $resource = 'Resource')
    {
        $response = self::connection()->get(self::$api_path . $path);
        return self::mapCollection($resource, $response);
    }
    public static function getResource($path, $resource = 'Resource')
    {
        $response = self::connection()->get(self::$api_path . $path);
        return self::mapResource($resource, $response);
    }
    public static function getCount($path)
    {
        $response = self::connection()->get(self::$api_path . $path);
        if ($response == false || is_string($response)) {
            return $response;
        }
        return $response->count;
    }
    public static function createResource($path, $object)
    {
        if (is_array($object)) {
            $object = (object) $object;
        }
        return self::connection()->post(self::$api_path . $path, $object);
    }
    public static function updateResource($path, $object)
    {
        if (is_array($object)) {
            $object = (object) $object;
        }
        return self::connection()->put(self::$api_path . $path, $object);
    }
    public static function deleteResource($path)
    {
        return self::connection()->delete(self::$api_path . $path);
    }
    private static function mapCollection($resource, $object)
    {
        if ($object == false || is_string($object)) {
            return $object;
        }
        $baseResource = __NAMESPACE__ . '\\' . $resource;
        self::$resource = class_exists($baseResource) ? $baseResource : 'Bigcommerce\\Api\\Resources\\' . $resource;
        return array_map(array('self', 'mapCollectionObject'), $object);
    }
    private static function mapCollectionObject($object)
    {
        $class = self::$resource;
        return new $class($object);
    }
    private static function mapResource($resource, $object)
    {
        if ($object == false || is_string($object)) {
            return $object;
        }
        $baseResource = __NAMESPACE__ . '\\' . $resource;
        $class = class_exists($baseResource) ? $baseResource : 'Bigcommerce\\Api\\Resources\\' . $resource;
        return new $class($object);
    }
    private static function mapCount($object)
    {
        if ($object == false || is_string($object)) {
            return $object;
        }
        return $object->count;
    }
    public static function getTime()
    {
        $response = self::connection()->get(self::$api_path . '/time');
        if ($response == false || is_string($response)) {
            return $response;
        }
        return new \DateTime("@{$response->time}");
    }
    public static function getProducts($filter = false)
    {
        $filter = Filter::create($filter);
        return self::getCollection('/products' . $filter->toQuery(), 'Product');
    }
    public static function getProductImages($id)
    {
        return self::getResource('/products/' . $id . '/images/', 'ProductImage');
    }
    public static function getProductCustomFields($id)
    {
        return self::getCollection('/products/' . $id . '/customfields/', 'ProductCustomField');
    }
    public static function getProductCustomField($product_id, $id)
    {
        return self::getResource('/products/' . $product_id . '/customfields/' . $id, 'ProductCustomField');
    }
    public static function createProductCustomField($product_id, $object)
    {
        return self::createResource('/products/' . $product_id . '/customfields', $object);
    }
    public static function updateProductCustomField($product_id, $id, $object)
    {
        return self::updateResource('/products/' . $product_id . '/customfields/' . $id, $object);
    }
    public static function deleteProductCustomField($product_id, $id)
    {
        return self::deleteResource('/products/' . $product_id . '/customfields/' . $id);
    }
    public static function getProductsCount($filter = false)
    {
        $filter = Filter::create($filter);
        return self::getCount('/products/count' . $filter->toQuery());
    }
    public static function getProduct($id)
    {
        return self::getResource('/products/' . $id, 'Product');
    }
    public static function createProduct($object)
    {
        return self::createResource('/products', $object);
    }
    public static function updateProduct($id, $object)
    {
        return self::updateResource('/products/' . $id, $object);
    }
    public static function deleteProduct($id)
    {
        return self::deleteResource('/products/' . $id);
    }
    public static function getOptions($filter = false)
    {
        $filter = Filter::create($filter);
        return self::getCollection('/options' . $filter->toQuery(), 'Option');
    }
    public static function createOptions($object)
    {
        return self::createResource('/options', $object);
    }
    public static function getOptionsCount()
    {
        return self::getCount('/options/count');
    }
    public static function getOption($id)
    {
        return self::getResource('/options/' . $id, 'Option');
    }
    public static function deleteOption($id)
    {
        return self::deleteResource('/options/' . $id);
    }
    public static function getOptionValue($option_id, $id)
    {
        return self::getResource('/options/' . $option_id . '/values/' . $id, 'OptionValue');
    }
    public static function getOptionValues($filter = false)
    {
        $filter = Filter::create($filter);
        return self::getCollection('/options/values' . $filter->toQuery(), 'OptionValue');
    }
    public static function getCategories($filter = false)
    {
        $filter = Filter::create($filter);
        return self::getCollection('/categories' . $filter->toQuery(), 'Category');
    }
    public static function getCategoriesCount($filter = false)
    {
        $filter = Filter::create($filter);
        return self::getCount('/categories/count' . $filter->toQuery());
    }
    public static function getCategory($id)
    {
        return self::getResource('/categories/' . $id, 'Category');
    }
    public static function createCategory($object)
    {
        return self::createResource('/categories/', $object);
    }
    public static function updateCategory($id, $object)
    {
        return self::updateResource('/categories/' . $id, $object);
    }
    public static function deleteCategory($id)
    {
        return self::deleteResource('/categories/' . $id);
    }
    public static function getBrands($filter = false)
    {
        $filter = Filter::create($filter);
        return self::getCollection('/brands' . $filter->toQuery(), 'Brand');
    }
    public static function getBrandsCount($filter = false)
    {
        $filter = Filter::create($filter);
        return self::getCount('/brands/count' . $filter->toQuery());
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
    public static function deleteBrand($id)
    {
        return self::deleteResource('/brands/' . $id);
    }
    public static function getOrders($filter = false)
    {
        $filter = Filter::create($filter);
        return self::getCollection('/orders' . $filter->toQuery(), 'Order');
    }
    public static function getOrdersCount()
    {
        return self::getCount('/orders/count');
    }
    public static function getOrder($id)
    {
        return self::getResource('/orders/' . $id, 'Order');
    }
    public static function deleteOrder($id)
    {
        return self::deleteResource('/orders/' . $id);
    }
    public static function createOrder($object)
    {
        return self::createResource('/orders', $object);
    }
    public static function getCustomers($filter = false)
    {
        $filter = Filter::create($filter);
        return self::getCollection('/customers' . $filter->toQuery(), 'Customer');
    }
    public static function getCustomersCount($filter = false)
    {
        $filter = Filter::create($filter);
        return self::getCount('/customers/count' . $filter->toQuery());
    }
    public static function deleteCustomers($filter = false)
    {
        $filter = Filter::create($filter);
        return self::deleteResource('/customers' . $filter->toQuery());
    }
    public static function getCustomer($id)
    {
        return self::getResource('/customers/' . $id, 'Customer');
    }
    public static function createCustomer($object)
    {
        return self::createResource('/customers', $object);
    }
    public static function updateCustomer($id, $object)
    {
        return self::updateResource('/customers/' . $id, $object);
    }
    public static function deleteCustomer($id)
    {
        return self::deleteResource('/customers/' . $id);
    }
    public static function getCustomerAddresses($id)
    {
        return self::getCollection('/customers/' . $id . '/addresses', 'Address');
    }
    public static function getOptionSets($filter = false)
    {
        $filter = Filter::create($filter);
        return self::getCollection('/optionsets' . $filter->toQuery(), 'OptionSet');
    }
    public static function createOptionsets($object)
    {
        return self::createResource('/optionsets', $object);
    }
    public static function createOptionsets_Options($object, $id)
    {
        return self::createResource('/optionsets/' . $id . '/options', $object);
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
    public static function getSkus($filter = false)
    {
        $filter = Filter::create($filter);
        return self::getCollection('/products/skus' . $filter->toQuery(), 'Sku');
    }
    public static function createSku($object)
    {
        return self::createResource('/product/skus', $object);
    }
    public static function updateSku($id, $object)
    {
        return self::updateResource('/product/skus' . $id, $object);
    }
    public static function getCoupons($filter = false)
    {
        $filter = Filter::create($filter);
        return self::getCollection('/coupons' . $filter->toQuery(), 'Sku');
    }
    public static function createCoupon($object)
    {
        return self::createResource('/coupons', $object);
    }
    public static function updateCoupon($id, $object)
    {
        return self::updateResource('/coupons/' . $id, $object);
    }
    public static function getRequestLogs()
    {
        return self::getCollection('/requestlogs');
    }
    public static function getStore()
    {
        $response = self::connection()->get(self::$api_path . '/store');
        return $response;
    }
    public static function getRequestsRemaining()
    {
        $limit = self::connection()->getHeader('X-BC-ApiLimit-Remaining');
        if (!$limit) {
            $result = self::getTime();
            if (!$result) {
                return false;
            }
            $limit = self::connection()->getHeader('X-BC-ApiLimit-Remaining');
        }
        return intval($limit);
    }
}
namespace Bigcommerce\Api;

class Filter
{
    private $parameters;
    public static function create($filter = false)
    {
        if ($filter instanceof self) {
            return $filter;
        }
        if (is_int($filter)) {
            $filter = array('page' => $filter);
        }
        return new self($filter);
    }
    public function __construct($filter = array())
    {
        $this->parameters = $filter ? $filter : array();
    }
    public function __set($parameter, $value)
    {
        $this->parameters[$parameter] = $value;
    }
    public function toQuery()
    {
        $query = http_build_query($this->parameters);
        return $query ? '?' . $query : '';
    }
}
namespace Bigcommerce\Api;

class Resource
{
    protected $fields;
    protected $id;
    protected $ignoreOnCreate = array();
    protected $ignoreOnUpdate = array();
    protected $ignoreIfZero = array();
    public function __construct($object = false)
    {
        if (is_array($object)) {
            $object = isset($object[0]) ? $object[0] : false;
        }
        $this->fields = $object ? $object : new \stdClass();
        $this->id = $object && isset($object->id) ? $object->id : 0;
    }
    public function __get($field)
    {
        if (method_exists($this, $field) && isset($this->fields->{$field})) {
            return $this->{$field}();
        }
        return isset($this->fields->{$field}) ? $this->fields->{$field} : null;
    }
    public function __set($field, $value)
    {
        $this->fields->{$field} = $value;
    }
    public function __isset($field)
    {
        return isset($this->fields->{$field});
    }
    public function getCreateFields()
    {
        $resource = $this->fields;
        foreach ($this->ignoreOnCreate as $field) {
            if (isset($resource->{$field})) {
                unset($resource->{$field});
            }
        }
        return $resource;
    }
    public function getUpdateFields()
    {
        $resource = $this->fields;
        foreach ($this->ignoreOnUpdate as $field) {
            if (isset($resource->{$field})) {
                unset($resource->{$field});
            }
        }
        foreach ($resource as $field => $value) {
            if ($this->isIgnoredField($field, $value)) {
                unset($resource->{$field});
            }
        }
        return $resource;
    }
    private function isIgnoredField($field, $value)
    {
        if ($value === null) {
            return true;
        }
        if (strpos($field, 'date') !== FALSE && $value === '') {
            return true;
        }
        if (in_array($field, $this->ignoreIfZero) && $value === 0) {
            return true;
        }
        return false;
    }
}
namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;
class Address extends Resource
{
    
}
namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;
class Brand extends Resource
{
    protected $ignoreOnCreate = array('id');
    protected $ignoreOnUpdate = array('id');
    public function create()
    {
        return Client::createBrand($this->getCreateFields());
    }
    public function update()
    {
        return Client::updateBrand($this->id, $this->getUpdateFields());
    }
}
namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;
class Category extends Resource
{
    protected $ignoreOnCreate = array('id', 'parent_category_list');
    protected $ignoreOnUpdate = array('id', 'parent_category_list');
    public function create()
    {
        return Client::createCategory($this->getCreateFields());
    }
    public function update()
    {
        return Client::updateCategory($this->id, $this->getUpdateFields());
    }
}
namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;
class Coupon extends Resource
{
    protected $ignoreOnCreate = array('id', 'num_uses');
    protected $ignoreOnUpdate = array('id', 'num_uses');
    public function create()
    {
        return Client::createCoupon($this->getCreateFields());
    }
    public function update()
    {
        return Client::updateCoupon($this->id, $this->getUpdateFields());
    }
}
namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;
class Customer extends Resource
{
    protected $ignoreOnCreate = array('id');
    protected $ignoreOnUpdate = array('id');
    public function addresses()
    {
        return Client::getCollection($this->fields->addresses->resource, 'Address');
    }
    public function create()
    {
        return Client::createCustomer($this->getCreateFields());
    }
    public function update()
    {
        return Client::updateCustomer($this->id, $this->getUpdateFields());
    }
    public function delete()
    {
        return Client::deleteCustomer($this->id);
    }
}
namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;
class DiscountRule extends Resource
{
    
}
namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;
class Option extends Resource
{
    public function values()
    {
        return Client::getCollection($this->fields->values->resource, 'OptionValue');
    }
}
namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;
class OptionSet extends Resource
{
    protected $ignoreOnCreate = array('id');
    protected $ignoreOnUpdate = array('id');
    public function options()
    {
        return Client::getCollection($this->fields->options->resource, 'OptionSetOption');
    }
    public function create()
    {
        return Client::createResource('/optionsets', $this->getCreateFields());
    }
    public function update()
    {
        Client::updateResource('/optionsets/' . $this->id, $this->getUpdateFields());
    }
}
namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;
class OptionSetOption extends Resource
{
    protected $ignoreOnCreate = array('id', 'option_set_id');
    protected $ignoreOnUpdate = array('id', 'option_set_id', 'option_id');
    public function option()
    {
        return Client::getCollection($this->fields->option->resource);
    }
    public function create()
    {
        return Client::createResource('/optionsets/options', $this->getCreateFields());
    }
    public function update()
    {
        Client::updateResource('/optionsets/options/' . $this->id, $this->getUpdateFields());
    }
}
namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;
class OptionValue extends Resource
{
    protected $ignoreOnCreate = array('id', 'option_id');
    protected $ignoreOnUpdate = array('id', 'option_id');
    public function option()
    {
        return self::getResource('/options/' . $this->option_id, 'Option');
    }
    public function create()
    {
        return Client::createResource('/options/' . $this->option_id . '/values', $this->getCreateFields());
    }
    public function update()
    {
        Client::updateResource('/options/' . $this->option_id . '/values/' . $this->id, $this->getUpdateFields());
    }
}
namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;
class Order extends Resource
{
    public function shipments()
    {
        return Client::getCollection('/orders/' . $this->id . '/shipments', 'Shipment');
    }
    public function products()
    {
        return Client::getCollection($this->fields->products->resource, 'OrderProduct');
    }
    public function shipping_addresses()
    {
        return Client::getCollection($this->fields->shipping_addresses->resource, 'Address');
    }
    public function coupons()
    {
        return Client::getCollection($this->fields->coupons->resource, 'Coupon');
    }
    public function update()
    {
        $order = new \stdClass();
        $order->status_id = $this->status_id;
        $order->is_deleted = $this->is_deleted;
        Client::updateResource('/orders/' . $this->id, $order);
    }
}
namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;
class OrderProduct extends Resource
{
    
}
namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;
class OrderStatus extends Resource
{
    
}
namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;
class Product extends Resource
{
    protected $ignoreOnCreate = array('date_created', 'date_modified');
    protected $ignoreOnUpdate = array('id', 'rating_total', 'rating_count', 'date_created', 'date_modified', 'date_last_imported', 'number_sold', 'brand', 'images', 'discount_rules', 'configurable_fields', 'custom_fields', 'videos', 'skus', 'rules', 'option_set', 'options', 'tax_class');
    protected $ignoreIfZero = array('tax_class_id');
    public function brand()
    {
        return Client::getResource($this->fields->brand->resource, 'Brand');
    }
    public function images()
    {
        return Client::getCollection($this->fields->images->resource, 'ProductImage');
    }
    public function skus()
    {
        return Client::getCollection($this->fields->skus->resource, 'Sku');
    }
    public function rules()
    {
        return Client::getCollection($this->fields->rules->resource, 'Rule');
    }
    public function videos()
    {
        return Client::getCollection($this->fields->videos->resource, 'ProductVideo');
    }
    public function custom_fields()
    {
        return Client::getCollection($this->fields->custom_fields->resource, 'ProductCustomField');
    }
    public function configurable_fields()
    {
        return Client::getCollection($this->fields->configurable_fields->resource, 'ProductConfigurableField');
    }
    public function discount_rules()
    {
        return Client::getCollection($this->fields->discount_rules->resource, 'DiscountRule');
    }
    public function option_set()
    {
        return Client::getResource($this->fields->option_set->resource, 'OptionSet');
    }
    public function options()
    {
        return Client::getCollection('/products/' . $this->id . '/options', 'ProductOption');
    }
    public function create()
    {
        return Client::createProduct($this->getCreateFields());
    }
    public function update()
    {
        return Client::updateProduct($this->id, $this->getUpdateFields());
    }
    public function delete()
    {
        return Client::deleteProduct($this->id);
    }
    public function tax_class()
    {
        return Client::getResource($this->fields->tax_class->resource, 'TaxClass');
    }
}
namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;
class ProductConfigurableField extends Resource
{
    
}
namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;
class ProductCustomField extends Resource
{
    protected $ignoreOnCreate = array('id', 'product_id');
    protected $ignoreOnUpdate = array('id', 'product_id');
    public function create()
    {
        return Client::createResource('/products/' . $this->product_id . '/customfields', $this->getCreateFields());
    }
    public function update()
    {
        Client::updateResource('/products/' . $this->product_id . '/customfields/' . $this->id, $this->getUpdateFields());
    }
    public function delete()
    {
        Client::deleteResource('/products/' . $this->product_id . '/customfields/' . $this->id);
    }
}
namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;
class ProductImage extends Resource
{
    protected $ignoreOnCreate = array('id', 'date_created', 'product_id');
    protected $ignoreOnUpdate = array('id', 'date_created', 'product_id');
    public function create()
    {
        return Client::createResource('/products/' . $this->product_id . '/images', $this->getCreateFields());
    }
    public function update()
    {
        Client::updateResource('/products/' . $this->product_id . '/images/' . $this->id, $this->getUpdateFields());
    }
}
namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;
class ProductOption extends Resource
{
    public function option()
    {
        return self::getResource('/options/' . $this->option_id, 'Option');
    }
}
namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;
class ProductVideo extends Resource
{
    
}
namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;
class RequestLog extends Resource
{
    
}
namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;
class Rule extends Resource
{
    protected $ignoreOnCreate = array('id', 'product_id');
    protected $ignoreOnUpdate = array('id', 'product_id');
    public function conditions()
    {
        $conditions = Client::getCollection($this->fields->conditions->resource, 'RuleCondition');
        foreach ($conditions as $condition) {
            $condition->product_id = $this->product_id;
        }
        return $conditions;
    }
    public function create()
    {
        return Client::createResource('/products/' . $this->product_id . '/rules', $this->getCreateFields());
    }
    public function update()
    {
        Client::updateResource('/products/' . $this->product_id . '/rules/' . $this->id, $this->getUpdateFields());
    }
}
namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;
class RuleCondition extends Resource
{
    protected $ignoreOnCreate = array('id');
    protected $ignoreOnUpdate = array('id', 'rule_id');
    public $product_id;
    public function create()
    {
        return Client::createResource('/products/' . $this->product_id . '/rules/' . $this->rule_id . '/conditions', $this->getCreateFields());
    }
    public function update()
    {
        Client::updateResource('/products/' . $this->product_id . '/rules/' . $this->rule_id . '/conditions/' . $this->id, $this->getUpdateFields());
    }
}
namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;
class Shipment extends Resource
{
    protected $ignoreOnCreate = array('id', 'order_id', 'date_created', 'customer_id', 'shipping_method');
    protected $ignoreOnUpdate = array('id', 'order_id', 'date_created', 'customer_id', 'shipping_method', 'items');
    public function create()
    {
        return Client::createResource('/orders/' . $this->order_id . '/shipments', $this->getCreateFields());
    }
    public function update()
    {
        return Client::createResource('/orders/' . $this->order_id . '/shipments' . $this->id, $this->getCreateFields());
    }
}
namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;
class Sku extends Resource
{
    protected $ignoreOnCreate = array('product_id');
    protected $ignoreOnUpdate = array('id', 'product_id');
    public function options()
    {
        $options = Client::getCollection($this->fields->options->resource, 'SkuOption');
        foreach ($options as $option) {
            $option->product_id = $this->product_id;
        }
        return $options;
    }
    public function create()
    {
        return Client::createResource('/products/' . $this->product_id . '/skus', $this->getCreateFields());
    }
    public function update()
    {
        Client::updateResource('/products/' . $this->product_id . '/skus/' . $this->id, $this->getUpdateFields());
    }
}
namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;
class SkuOption extends Resource
{
    protected $ignoreOnCreate = array('id');
    protected $ignoreOnUpdate = array('id', 'sku_id');
    public $product_id;
    public function create()
    {
        return Client::createResource('/products/' . $this->product_id . '/skus/' . $this->sku_id . '/options', $this->getCreateFields());
    }
    public function update()
    {
        Client::updateResource('/products/' . $this->product_id . '/skus/' . $this->sku_id . '/options/' . $this->id, $this->getUpdateFields());
    }
}