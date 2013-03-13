<?php

require_once 'Bigcommerce/Api.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');


Bigcommerce_Api::configure(array(
'store_url' => 'https://store-bwvr466.mybigcommerce.com',
'username' => 'saranyan',
'api_key' => 'd7948f71684475c6b6d8b29c36ae37cd'
));
Bigcommerce_Api::setCipher('RC4-SHA');
Bigcommerce_Api::verifyPeer(false);

// $product = array('name' => 'ABC Blocks', 'type' => 'physical', 'price' => '19.99', 'weight' => 2.3, 'categories' => array(34), 'availability' => 'available');
// echo Bigcommerce_Api::createProduct($product);

$product = array('name' => 'ABC Blocks', 'type' => 'physical', 'price' => '9.99', 'weight' => 2.3, 'categories' => array(34), 'availability' => 'available');
Bigcommerce_Api::updateProduct(46, $product);

?>

