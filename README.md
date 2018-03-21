Bigcommerce API Client
======================

PHP client for connecting to the Bigcommerce V2 REST API.

To find out more, visit the official documentation website:
http://developer.bigcommerce.com/

[![Build Status](https://travis-ci.org/bigcommerce/bigcommerce-api-php.png?branch=master)](https://travis-ci.org/bigcommerce/bigcommerce-api-php)
[![Coverage Status](https://coveralls.io/repos/bigcommerce/bigcommerce-api-php/badge.png?branch=master)](https://coveralls.io/r/bigcommerce/bigcommerce-api-php?branch=master)
[![Dependency Status](https://www.versioneye.com/package/php--bigcommerce--api/badge.png)](https://www.versioneye.com/package/php--bigcommerce--api)

[![Latest Stable Version](https://poser.pugx.org/bigcommerce/api/v/stable.png)](https://packagist.org/packages/bigcommerce/api)
[![Total Downloads](https://poser.pugx.org/bigcommerce/api/downloads.png)](https://packagist.org/packages/bigcommerce/api)

Requirements
------------

- PHP 7.0 or greater
- cUrl extension enabled

**To connect to the API with basic auth you need the following:**

- Secure URL pointing to a Bigcommerce store
- Username of an authorized admin user of the store
- API key for the user

To generate an API key, go to Control Panel > Users > Edit User and make sure
the 'Enable the XML API?' is ticked.

**To connect to the API with OAuth you will need the following:**

- client_id
- auth_token
- store_hash

Installation
------------

Use the following Composer command to install the
API client from [the Bigcommerce vendor on Packagist](https://packagist.org/packages/bigcommerce/api):

~~~shell
 $ composer require bigcommerce/api
 $ composer update
~~~

You can also install composer for your specific project by running the following in the library folder.

~~~shell
 $ curl -sS https://getcomposer.org/installer | php
 $ php composer.phar install
 $ composer install
~~~

Namespace
---------

All the examples below assume the `Bigcommerce\Api\Client` class is imported
into the scope with the following namespace declaration:

~~~php
use Bigcommerce\Api\Client as Bigcommerce;
~~~

Configuration
-------------

To use the API client in your PHP code, ensure that you can access `Bigcommerce\Api`
in your autoload path (using Composer’s `vendor/autoload.php` hook is recommended).

Provide your credentials to the static configuration hook to prepare the API client
for connecting to a store on the Bigcommerce platform:

### Basic Auth
~~~php
Bigcommerce::configure(array(
	'store_url' => 'https://store.mybigcommerce.com',
	'username'	=> 'admin',
	'api_key'	=> 'd81aada4xc34xx3e18f0xxxx7f36ca'
));
~~~

### OAuth

In order to obtain the auth_token you would consume `Bigcommerce::getAuthToken` method 

~~~php

$object = new \stdClass();
$object->client_id = 'xxxxxx';
$object->client_secret = 'xxxxx;
$object->redirect_uri = 'https://app.com/redirect';
$object->code = $request->get('code');
$object->context = $request->get('context');
$object->scope = $request->get('scope');

$authTokenResponse = Bigcommerce::getAuthToken($object);

Bigcommerce::configure(array(
    'client_id' => 'xxxxxxxx',
    'auth_token' => $authTokenResponse->access_token,
    'store_hash' => 'xxxxxxx'
));

~~~

Connecting to the store
-----------------------

To test that your configuration was correct and you can successfully connect to
the store, ping the getTime method which will return a DateTime object
representing the current timestamp of the store if successful or false if
unsuccessful:

~~~php
$ping = Bigcommerce::getTime();

if ($ping) echo $ping->format('H:i:s');
~~~

Accessing collections and resources (GET)
-----------------------------------------

To list all the resources in a collection:

~~~php
$products = Bigcommerce::getProducts();

foreach ($products as $product) {
	echo $product->name;
	echo $product->price;
}
~~~

To access a single resource and its connected sub-resources:

~~~php
$product = Bigcommerce::getProduct(11);

echo $product->name;
echo $product->price;
~~~

To view the total count of resources in a collection:

~~~php
$count = Bigcommerce::getProductsCount();

echo $count;
~~~
Paging and Filtering
--------------------

All the default collection methods support paging, by passing
the page number to the method as an integer:

~~~php
$products = Bigcommerce::getProducts(3);
~~~
If you require more specific numbering and paging, you can explicitly specify
a limit parameter:

~~~php
$filter = array("page" => 3, "limit" => 30);

$products = Bigcommerce::getProducts($filter);
~~~

To filter a collection, you can also pass parameters to filter by as key-value
pairs:

~~~php
$filter = array("is_featured" => true);

$featured = Bigcommerce::getProducts($filter);
~~~
See the API documentation for each resource for a list of supported filter
parameters.

Updating existing resources (PUT)
---------------------------------

To update a single resource:

~~~php
$product = Bigcommerce::getProduct(11);

$product->name = "MacBook Air";
$product->price = 99.95;
$product->update();
~~~

You can also update a resource by passing an array or stdClass object of fields
you want to change to the global update method:

~~~php
$fields = array(
	"name"  => "MacBook Air",
	"price" => 999.95
);

Bigcommerce::updateProduct(11, $fields);
~~~

Creating new resources (POST)
-----------------------------

Some resources support creation of new items by posting to the collection. This
can be done by passing an array or stdClass object representing the new
resource to the global create method:

~~~php
$fields = array(
	"name" => "Apple"
);

Bigcommerce::createBrand($fields);
~~~

You can also create a resource by making a new instance of the resource class
and calling the create method once you have set the fields you want to save:

~~~php
$brand = new Bigcommerce\Api\Resources\Brand();

$brand->name = "Apple";
$brand->create();
~~~

Deleting resources and collections (DELETE)
-------------------------------------------

To delete a single resource you can call the delete method on the resource object:

~~~php
$category = Bigcommerce::getCategory(22);
$category->delete();
~~~

You can also delete resources by calling the global wrapper method:

~~~php
Bigcommerce::deleteCategory(22);
~~~

Some resources support deletion of the entire collection. You can use the
deleteAll methods to do this:

~~~php
Bigcommerce::deleteAllOptionSets();
~~~

Using The XML API
-----------------

By default, the API client handles requests and responses by converting between
JSON strings and their PHP object representations. If you need to work with XML
you can switch the API into XML mode with the useXml method:

~~~php
Bigcommerce::useXml();
~~~

This will configure the API client to use XML for all subsequent requests. Note
that the client does not convert XML to PHP objects. In XML mode, all object
parameters to API create and update methods must be passed as strings
containing valid XML, and all responses from collection and resource methods
(including the ping, and count methods) will return XML strings instead of PHP
objects. An example transaction using XML would look like:

~~~php
Bigcommerce::useXml();

$xml = "<?xml version="1.0" encoding="UTF-8"?>
		<brand>
		 	<name>Apple</name>
		 	<search_keywords>computers laptops</search_keywords>
		</brand>";

$result = Bigcommerce::createBrand($xml);
~~~

Handling Errors And Timeouts
----------------------------

For whatever reason, the HTTP requests at the heart of the API may not always
succeed.

Every method will return false if an error occurred, and you should always
check for this before acting on the results of the method call.

In some cases, you may also need to check the reason why the request failed.
This would most often be when you tried to save some data that did not validate
correctly.

~~~php
$orders = Bigcommerce::getOrders();

if (!$orders) {
	$error = Bigcommerce::getLastError();
	echo $error->code;
	echo $error->message;
}
~~~

Returning false on errors, and using error objects to provide context is good
for writing quick scripts but is not the most robust solution for larger and
more long-term applications.

An alternative approach to error handling is to configure the API client to
throw exceptions when errors occur. Bear in mind, that if you do this, you will
need to catch and handle the exception in code yourself. The exception throwing
behavior of the client is controlled using the failOnError method:

~~~php
Bigcommerce::failOnError();

try {
	$orders = Bigcommerce::getOrders();

} catch(Bigcommerce\Api\Error $error) {
	echo $error->getCode();
	echo $error->getMessage();
}
~~~

The exceptions thrown are subclasses of Error, representing
client errors and server errors. The API documentation for response codes
contains a list of all the possible error conditions the client may encounter.


Verifying SSL certificates
--------------------------

By default, the client will attempt to verify the SSL certificate used by the
Bigcommerce store. In cases where this is undesirable, or where an unsigned
certificate is being used, you can turn off this behavior using the verifyPeer
switch, which will disable certificate checking on all subsequent requests:

~~~php
Bigcommerce::verifyPeer(false);
~~~

Connecting through a proxy server
---------------------------------

In cases where you need to connect to the API through a proxy server, you may
need to configure the client to recognize this. Provide the URL of the proxy
server and (optionally) a port to the useProxy method:

~~~php
Bigcommerce::useProxy("http://proxy.example.com", 81);
~~~
