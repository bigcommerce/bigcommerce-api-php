<?php

require 'bigcommerce.php';

use Bigcommerce\Api\Client as Bigcommerce;

error_reporting(E_ALL);
ini_set('display_errors', '1');


Bigcommerce::configure(array(
'store_url' => 'https://yourstore.bigcommerce.com',
'username' => 'admin',
'api_key' => 'keyhere'
));



Bigcommerce::setCipher('RC4-SHA');
Bigcommerce::verifyPeer(false);
Bigcommerce::failOnError(true);


//Get all orders created after
//Fri, 16 Nov 2012 21:43:32 +0000
$filter = array('min_date_created' => 'Tue, 20 Nov 2012 00:00:00 +0000');
$orders = Bigcommerce::getOrders($filter);
print_r($orders);

//filter products
$filter = array('name' => 'ABC Blocks');
$products = Bigcommerce_Api::getProducts($filter);
print_r($products);


//filter products by skus
$filter = array('sku' => 'abc-blocks-1-wood');
$skus = Bigcommerce::getSkus($filter);
print_r($skus);

//get a particular order
$order = Bigcommerce::getOrder(112);
print_r($order);

//update a category
$fields = array('name'=>'Optimus Prime');
print_r(Bigcommerce::updateCategory(11, $fields));

//create a new category
$createFields = array('name'=>'Some random 1');
print_r(Bigcommerce::createCategory($createFields));


//create new order
$createFields = array('customer_id'=>0, 'date_created' => 'Tue, 20 Nov 2012 00:00:00 +0000','status_id'=>1,'billing_address' => array( "first_name"=> "Trisha", "last_name"=> "McLaughlin", "company"=> "", "street_1"=> "12345 W Anderson Ln", "street_2"=> "", "city"=> "Austin", "state"=> "Texas", "zip"=> "78757", "country"=> "United States", "country_iso2"=> "US", "phone"=> "", "email"=> "elsie@example.com" ), "shipping_addresses" => array(), "external_source" => "POS", "products" => array() );
print_r(Bigcommerce::createOrder($createFields));

//if you want to use xml
// Bigcommerce::useXml();
// $orders_xml = Bigcommerce::getOrders();
// $orders = simplexml_load_string($orders_xml);
// foreach($orders as $order) {
//     echo $order->id;
//     echo "\n";
//     echo $order->currency_code;
// }


?>