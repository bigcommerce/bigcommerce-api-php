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

To generate an OAuth API token, [follow this guide.](https://support.bigcommerce.com/s/article/Store-API-Accounts)

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

V3 Update - *NEW
---------
This update is on the development with `Backward Compatibility` and can be easily customised on future version releases. Feel free to add more features and create issues.

`configureBasicAuth` is Completely removed now you can only configure using `auth_token, client_id and store_hash`

Now you can set the version on Configuration and can be overridden anywhere in the code.
~~~php
Bigcommerce::configure(array(
    'client_id' => 'xxxxxxxxxxxxxxxxxxxxxx',
    'auth_token' => 'xxxxxxxxxxxxxxxxxxxxxxx',
    'store_hash' => 'xxxxxxxxx',
    'version' => 'v3' //optional By Default set as 'v2'
));

//If you don't want to set version by default, you can always set it in the method.

$brands = Bigcommerce::getBrands([],"v3");

foreach($brands as $brand){
    echo $brand->name."\n";
}
~~~
As of now, Only `Carts, Wishlists and Catlalog\brands support 'v3'` other APIs are still in development will be added here once it is completed, Meanwhile `You can still use 'v2' features without any issues`.

Set 'v3' by default if you're only using 'v3' APIs

'v3' methods has `$version` parameter which can be used if you didn't set version 'v3' as default version.

##Carts(V3)

you can do almost all the functions in cart.

**Get Cart by Cart Id**: `getCart($id, $version = null);`
* $id = String Cart Id
* $version = (Optional) String "v2", "v3", ..  
~~~php
Bigcommerce::configure(array(
    'client_id' => 'xxxxxxxxxxxxxxxxxxxxxx',
    'auth_token' => 'xxxxxxxxxxxxxxxxxxxxxxx',
    'store_hash' => 'xxxxxxxxx',
    'version' => 'v3'
));
$cart = Bigcommerce::getCart();

echo $cart->id;

//for this documentation, I'll use the above example
//$version variable available for only methods that use 'v3', you can use older functions as it was without '$version' variable


//or

Bigcommerce::configure(array(
    'client_id' => 'xxxxxxxxxxxxxxxxxxxxxx',
    'auth_token' => 'xxxxxxxxxxxxxxxxxxxxxxx',
    'store_hash' => 'xxxxxxxxx'
));

$cart = Bigcommerce::getCart("v3");

echo $cart->id;
~~~
**Create Cart**: `createCart($object, $version = null);`
* $object = Array | Object 
* $version = (Optional) String "v2", "v3", ..
~~~php
$cart = array(
    "customer_id" => 1,
    "line_items" => array(
        array(
            "quantity" => 1,
            "product_id" => 1,
            "variant_id" => 2
        )
    )
);

Bigcommerce::createCart($cart); //or Bigcommerce::createCart($cart,"v3");

//or

$cart = new Bigcommerce\Api\Resources\Cart();
$cart->customer_id = 1;
$cart->line_items = array(
    array(
        "quantity" => 1,
        "product_id" => 1,
        "variant_id" => 2
    )
);
$cart->create(); // CartObject->create($version = 'v3'); $version (Optional)
~~~
**Update Cart**: `updateCartCustomerId($cart_id, $customer_id, $version = null);`

Note: Only `Customer Id` can be updated by update cart api
* $cart_id = String Cart Id
* $customer_id = Int Customer Id
* $version = (Optional) String "v2", "v3", ..
 ~~~php
Bigcommerce::updateCartCustomerId("xxxxxxxxx",1);
 
 //or
 
 $cart = Bigcommerce::getCart("xxxxxxxxxxx");
 $cart->update(41) // CartObject->update($customer_id, $version = 'v3'); $version (Optional)
 ~~~
**Delete Cart**: `deleteCart($cart_id, $version = null);`
* $cart_id = String Cart Id
~~~php
Bigcommerce::deleteCart("xxxxxxxxx",1);
 
 //or
 
 $cart = Bigcommerce::getCart("xxxxxxxxxxx");
 $cart->delete() // CartObject->delete($version = 'v3'); $version (Optional)
 ~~~

**Add Cart Items**: `createCartLineItems($id, $object, $filter = array(), $version = null);`
* $id = String Cart Id
* $object = Array|Object 
* $filter = (Optional) Array Example ['include'=>'redirect_urls']
~~~php
$items = array(
    "line_items" => array(
        array(
            "quantity" => 1,
            "product_id" => 1,
            "variant_id" => 2
        ),
        array(
            "quantity" => 1,
            "product_id" => 2,
            "variant_id" => 3
        )
    )
);

Bigcommerce::createCartLineItems("xxxxxxxxx",$items);
 
 //or
 
 $cart = Bigcommerce::getCart("xxxxxxxxxxx");
 $cart->addItems($items) // CartObject->addItems($items, $filter = array(), $version = 'v3'); $filter, $version (Optional)
 ~~~

**Update Cart Item**: `updateCartLineItem($cart_id, $line_item_id, $object, $filter = array(), $version = null);`
* $cart_id = String Cart Id
* $line_item_id = String Line Item Id
* $object = Array|Object 
* $filter = (Optional) Array Example ['include'=>'redirect_urls']
~~~php
$item = array(
    "line_items" => array(
        "quantity" => 1,
        "product_id" => 1,
        "variant_id" => 2
    )
);

Bigcommerce::updateCartLineItem("xxxxxxxxx","xxxxxxxxx",$item);
 ~~~

**Delete Cart Item**: `deleteCartLineItem($cart_id, $line_item_id, $version = null);`
* $cart_id = String Cart Id
* $line_item_id = String Line Item Id
~~~php
Bigcommerce::deleteCartLineItem("xxxxxxxxx","xxxxxxxxx");
 ~~~

##Brands (V2 and V3)
you can use both 'v2' and 'v3' in Brands and I'm trying to do the same for all new versions.

**Get All Brands**: `getBrands($filter = array(), $version = null);`
* $filter = Array filter options refer Bigcommerce documentation for more.
* $version = (Optional) String "v2", "v3", ..  
~~~php
Bigcommerce::configure(array(
    'client_id' => 'xxxxxxxxxxxxxxxxxxxxxx',
    'auth_token' => 'xxxxxxxxxxxxxxxxxxxxxxx',
    'store_hash' => 'xxxxxxxxx'
));
// By default version will be 'v2'
// API url will be https://api.bigcommerce.com/stores/{store_hash}/v2/brands
$brands = Bigcommerce::getBrands();

//or

Bigcommerce::configure(array(
    'client_id' => 'xxxxxxxxxxxxxxxxxxxxxx',
    'auth_token' => 'xxxxxxxxxxxxxxxxxxxxxxx',
    'store_hash' => 'xxxxxxxxx',
    'version' => 'v3' \\ Optional
));

// API url will be https://api.bigcommerce.com/stores/{store_hash}/v3/catalog/brands
$brands = Bigcommerce::getBrands([],"v3");
~~~
**Get Brand by Brand Id:** `getBrand($id, $version = null);`
* $id = Int Brand Id.
* $version = (Optional) String "v2", "v3", ..  
~~~php
$brand = Bigcommerce::getBrand(1);
//or
$brand = Bigcommerce::getBrand(1,"v3");

echo $brand->name;
~~~
**Create Brand:** `createBrand($object, $version = null);`
* $object = Array|Object API Payload.
* $version = (Optional) String "v2", "v3", ..  
~~~php
$brand = array(
    "name" => "test"
);
$brand = Bigcommerce::createBrand($brand,'v3');
//or
$brand = new Bigcommerce\Api\Resources\Brand();
$brand->name = "test";
$brand->create(); // BrandObject->create($version = null); $version (Optional)
~~~
**Update Brand:** `createBrand($id, $object, $version = null);`
* $id = Int Brand Id.
* $object = Array|Object
* $version = (Optional) String "v2", "v3", ..  
~~~php
$brand = array(
    "name" => "test"
);
$brand = Bigcommerce::updateBrand(1, $brand, 'v3');
//or
$brand = Bigcommerce::getBrand(1);
$brand->name = "test";
$brand->update(); // BrandObject->update($version = null); $version (Optional)
~~~
**Delete Brand:** `deleteBrand($id, $version = null);`
* $id = Int Brand Id.
* $version = (Optional) String "v2", "v3", ..  
~~~php
Bigcommerce::deleteBrand(1);
//or
$brand = Bigcommerce::getBrand(1);
$brand->delete(); // BrandObject->delete($version = null); $version (Optional)
~~~
**Delete All Brand:** `deleteAllBrands($version = null);`
* $version = (Optional) String "v2", "v3", ..  
~~~php
Bigcommerce::deleteAllBrands();
~~~
**Get All Brand Meta Fields (Only on 'v3'):** `getBrandMetafields($id, $filter = array(), $version = null);`
* $id = Int Brand Id
* $filter = (Optional) Array|Object
* $version = (Optional) String "v2", "v3", ..  
~~~php
Bigcommerce::getBrandMetafields(1, array(), 'v3');
~~~
**Get Brand Meta Field by Id (Only on 'v3'):** `getBrandMetafield($brand_id, $metafield_id, $filter = array(), $version = null);`
* $brand_id = Int Brand Id
* $metafield_id = Int Brand Meta Field Id
* $filter = (Optional) Array|Object
* $version = (Optional) String "v2", "v3", ..  
~~~php
Bigcommerce::getBrandMetafield(1, 1, array(), 'v3');
~~~
**Create Brand Meta Field (Only on 'v3'):** `createBrandMetafield($id, $object, $version = null);`
* $id = Int Brand Id
* $object = Array|Object
* $version = (Optional) String "v2", "v3", ..  
~~~php
$metaField = array(
    "permission_set" => "app_only",
    "namespace" => "App Namespace",
    "key" => "location_id",
    "value" => "Shelf 3, Bin 5",
);

Bigcommerce::createBrandMetafield(1, $metaField, 'v3');
~~~

**Update Brand Meta Field (Only on 'v3'):** `updateBrandMetafield($brand_id, $metafield_id, $object, $version = null);`
* $brand_id = Int Brand Id
* $metafield_id = Int Brand Meta Field Id
* $object = Array|Object
* $version = (Optional) String "v2", "v3", ..  
~~~php
$metaField = array(
    "permission_set" => "app_only",
    "namespace" => "App Namespace",
    "key" => "location_id",
    "value" => "Shelf 3",
);

Bigcommerce::updateBrandMetafield(1, 1, $metaField, 'v3');
~~~

**Delete Brand Meta Field (Only on 'v3'):** `updateBrandMetafield($brand_id, $metafield_id, $version = null);`
* $brand_id = Int Brand Id
* $metafield_id = Int Brand Meta Field Id
* $version = (Optional) String "v2", "v3", ..  
~~~php
Bigcommerce::deleteBrandMetafield(1, 1, 'v3');
~~~

That's all for now. I'll update for other APIs Continuously. **Feel free to Pull and Merge for other APIs**

I'll publish this repo on composer for easy Installation

**You can use all the features and Methods Below**

Configuration
-------------

To use the API client in your PHP code, ensure that you can access `Bigcommerce\Api`
in your autoload path (using Composer’s `vendor/autoload.php` hook is recommended).

Provide your credentials to the static configuration hook to prepare the API client
for connecting to a store on the Bigcommerce platform:

### OAuth

In order to obtain the auth_token you would consume `Bigcommerce::getAuthToken` method during an installation of a single-click app. Alternatively, if you already have a token, skip to `Bigcommerce::configure` and provide your credentials.

~~~php

$object = new \stdClass();
$object->client_id = 'xxxxxx';
$object->client_secret = 'xxxxx';
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

### Basic Auth (deprecated)
**Update - Totally Removed**
~~~php
Bigcommerce::configure(array(
	'store_url' => 'https://store.mybigcommerce.com',
	'username'	=> 'admin',
	'api_key'	=> 'd81aada4xc34xx3e18f0xxxx7f36ca'
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
