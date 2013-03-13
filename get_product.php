<?php

require 'vendor/autoload.php';
use Bigcommerce\Api\Client as Bigcommerce;

error_reporting(E_ALL);
ini_set('display_errors', '1');


Bigcommerce::configure(array(
'store_url' => 'https://store-bwvr466.mybigcommerce.com',
'username' => 'saranyan',
'api_key' => 'd7948f71684475c6b6d8b29c36ae37cd'
));
Bigcommerce::setCipher('RC4-SHA');
Bigcommerce::verifyPeer(false);

// $filter = array('name' => 'ABC Blocks');
// $products = Bigcommerce_Api::getProducts($filter);
// print_r($products);

// $filter = array('brand_id' => 1);
// $products = Bigcommerce::getProducts($filter);
// print_r($products);


$filter = array('sku' => 'abc-blocks-1-wood');
$skus = Bigcommerce::getSkus($filter);
print_r($skus);


?>