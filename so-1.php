<?php

require_once 'Bigcommerce/Api.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');

Bigcommerce_Api::configure(array(
'store_url' => 'https://store-bwvr466.mybigcommerce.com',
'username' => 'demo',
'api_key' => 'df38dd10e9665a3cfa667817d78ec91ee9384bc3'
));
Bigcommerce_Api::setCipher('RC4-SHA');
Bigcommerce_Api::verifyPeer(false);

// $orders = Bigcommerce_Api::getOrders();
// foreach($orders as $order) {
//     echo $order->name;
//     echo $order->price;
// }
$shipment = Bigcommerce_Api::getCollection('/orders/100/shipments/1', 'Shipment');

$Orders = BigCommerce_Api::getOrder(101);
$order_shipments = Bigcommerce_Api::getCollection('/orders/'.$Orders->id. '/shipments/'. 1, 'Shipment');


// $products = Bigcommerce_Api::getProducts();

// foreach($products as $product) {
//     echo $product->name;
//     echo $product->price;
// }

// $ping = Bigcommerce_Api::getTime();
// if ($ping) 
// {
// echo $ping->format('H:i:s');
// }

?>