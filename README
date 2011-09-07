BigCommerce REST API V2
=======================

PHP package for connecting to the BigCommerce REST API.

To find out more, visit the official documentation website:
http://developer.bigcommerce.com/

Requirements
------------

To connect to the API, you need the following credentials:

- Secure URL pointing to a BigCommerce store
- Username of an authorized admin user of the store
- API key for the user

To generate an API key, go to Control Panel > Users > Edit User and make sure
the 'Enable the XML API?' is ticked.

Configuration
-------------

Download the required PHP code for the BigCommerce REST API client and copy it
to your PHP include path, or use the following command to install the package
directly:

 $ sudo pear channel-discover http://bigcommerce.lib.pear.somewhere
 $ sudo pear install bigcommerce/api-v2-client

To use the API client in your PHP code, require the package from your include
path and provide the required credentials as follows:

require_once 'BigCommerce/Api2.php';

$store_url = "http://store.url/"
$username  = "admin"
$api_key   = "api_key"

BigCommerce_Api::configure($store_url, $username, $api_key);

Connecting to the store
-----------------------

To test that your configuration was correct and you can successfully connect to
the store, ping the getTime method which will return a DateTime object
representing the current timestamp of the store if successful or false if
unsuccessful:

$ping = BigCommerce_Api::getTime();

if ($ping) {
	echo $ping->format('H:i:s');
}

Accessing collections and resources (GET)
-----------------------------------------

To list all the resources in a collection:

$products = BigCommerce_Api::getProducts();

foreach($products as $product) {
	echo $product->name;
	echo $product->price;
}

To access a single resource and its connected sub-resources:

$product = BigCommerce_Api::getProduct(11);

echo $product->name;
echo $product->price;

To view the total count of resources in a collection:

$count = BigCommerce_Api::getProductsCount();

Updating existing resources (PUT)
---------------------------------

To update a single resource:

$product = BigCommerce_Api::getProduct(11);

$product->name = "MacBook Air";
$product->price = 99.95;
$product->update();

You can also update a resource by passing an array or stdClass object of fields
you want to change to the global update method:

$fields = array(
	"name"  => "MacBook Air",
	"price" => 999.95
);

BigCommerce_Api::updateProduct(11, $fields);

Creating new resources (POST)
-----------------------------

Some resources support creation of new items by posting to the collection. This
can be done by passing an array or stdClass object representing the new
resource to the global create method:

$fields = array(
	"name" => "Apple"
);

BigCommerce_Api::createBrand($fields);

You can also create a resource by making a new instance of the resource class
and calling the create method once you have set the fields you want to save:

$brand = new BigCommerce_Api_Brand();

$brand->name = "Apple";
$brand->create();

Deleting resources and collections (DELETE)
-------------------------------------------

To delete a single resource you can call the delete method on the resource object:

$options = BigCommerce_Api::getOptionSet(22);
$options->delete();

You can also delete resources by calling the global method:

BigCommerce_Api::deleteOptionSet(22);

Some resources support deletion of the entire collection. You can use the
deleteAll methods to do this:

BigCommerce_Api::deleteAllOptionSets();

Using The XML API
-----------------

By default, the API client handles requests and responses by converting between
JSON strings and their PHP object representations. If you need to work with XML
you can switch the API into XML mode with the useXml method:

BigCommerce_Api::useXml();

This will configure the API client to use XML for all subsequent requests. Note
that the client does not convert XML to PHP objects. In XML mode, all object
parameters to API create and update methods must be passed as strings
containing valid XML, and all responses from collection and resource methods
(including the ping, and count methods) will return XML strings instead of PHP
objects. An example transaction using XML would look like:

BigCommerce_Api::useXml();

$xml = "<?xml version="1.0" encoding="UTF-8"?>
		<brand>
		 	<name>Apple</name>
		 	<search_keywords>computers laptops</search_keywords>
		</brand>";

$result = BigCommerce_Api::createBrand($xml);

Handling Errors And Timeouts
----------------------------

For whatever reason, the HTTP requests at the heart of the API may not always
succeed.

Every method will return false if an error occurred, and you should always
check for this before acting on the results of the method call.

In some cases, you may also need to check the reason why the request failed.
This would most often be when you tried to save some data that did not validate
correctly.

$count = BigCommerce_Api::getProductsCount();

if ($count) {
	echo $count;
} else {
	$error = BigCommerce_Api::getLastError();
	echo $error->code;
	echo $error->message;
}

Returning false on errors, and using error objects to provide context is good
for writing quick scripts but is not the most robust solution for larger and
more long-term applications.

An alternative approach to error handling is to configure the API client to
throw exceptions when errors occur. Bear in mind, that if you do this, you will
need to catch and handle the exception in code yourself. The exception throwing
behavior of the client is controlled using the failOnError method:

BigCommerce_Api::failOnError();

try {
	$orders = BigCommerce_Api::getOrders();

} catch(BigCommerce_Api_Error $error) {
	echo $error->getCode();
	echo $error->getMessage();
}

The exceptions thrown are subclasses of BigCommerce_Api_Error, representing
client errors and server errors. The API documentation for response codes
contains a list of all the possible error conditions the client may encounter.

Verifying SSL certificates
--------------------------

By default, the client will attempt to verify the SSL certificate used by the
BigCommerce store. In cases where this is undesirable, or where an unsigned
certificate is being used, you can turn off this behavior using the verifyPeer
switch, which will disable certificate checking on all subsequent requests:

BigCommerce_Api::verifyPeer(false);

Connecting through a proxy server
---------------------------------

In cases where you need to connect to the API through a proxy server, you may
need to configure the client to recognize this. Provide the URL of the proxy
server and (optionally) a port to the useProxy method:

$proxy = "http://proxy.example.com";
$port = "81";

BigCommerce_Api::useProxy($proxy, $port);

